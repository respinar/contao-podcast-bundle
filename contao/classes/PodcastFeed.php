<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Respinar\PodcastBundle;

use Contao\Frontend;
use Contao\System;
use Contao\Input;
use Contao\StringUtil;
use Contao\Config;
use Contao\Environment;
use Contao\UserModel;
use Contao\FilesModel;
use Contao\Date;
use Contao\File;
use Contao\Feed;
use Contao\FeedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

use Respinar\PodcastBundle\Model\ChannelModel;
use Respinar\PodcastBundle\Model\EpisodeModel;

/**
 * Provide methods regarding calendars.
 */
class PodcastFeed extends Frontend
{
	/**
	 * Current events
	 * @var array
	 */
	protected $arrEvents = array();

	/**
	 * Page cache array
	 * @var array
	 */
	private static $arrPageCache = array();

	/**
	 * Update a particular RSS feed
	 *
	 * @param integer $intId
	 */
	public function generateFeed($intId)
	{
		$objPodcast = ChannelModel::findByPk($intId);

		if ($objPodcast === null)
		{
			return;
		}

		$objPodcast->feedName = $objPodcast->alias ?: 'podcast' . $objPodcast->id;

		// Delete XML file
		if (Input::get('act') == 'delete')
		{
			$webDir = StringUtil::stripRootDir(System::getContainer()->getParameter('contao.web_dir'));

			$this->import(Files::class, 'Files');
			$this->Files->delete($webDir . '/share/' . $objPodcast->feedName . '.xml');
		}

		// Update XML file
		else
		{
			$this->generateFiles($objPodcast->row());

			System::getContainer()->get('monolog.logger.contao.cron')->info('Generated calendar feed "' . $objPodcast->feedName . '.xml"');
		}
	}

	/**
	 * Delete old files and generate all feeds
	 */
	public function generateFeeds()
	{
		$this->import(Automator::class, 'Automator');
		$this->Automator->purgeXmlFiles();

		$objPodcast = ChannelModel::findAll();

		if ($objPodcast !== null)
		{
			while ($objPodcast->next())
			{
				$objPodcast->feedName = $objPodcast->alias ?: 'podcast' . $objPodcast->id;
				$this->generateFiles($objPodcast->row());

				System::getContainer()->get('monolog.logger.contao.cron')->info('Generated calendar feed "' . $objPodcast->feedName . '.xml"');
			}
		}
	}

	/**
	 * Generate all feeds including a certain calendar
	 *
	 * @param integer $intId
	 */
	public function generateFeedsByCalendar($intId)
	{
		// $objFeed = CalendarFeedModel::findByCalendar($intId);

		// if ($objFeed !== null)
		// {
		// 	while ($objFeed->next())
		// 	{
		// 		$objFeed->feedName = $objFeed->alias ?: 'calendar' . $objFeed->id;

		// 		// Update the XML file
		// 		$this->generateFiles($objFeed->row());

		// 		System::getContainer()->get('monolog.logger.contao.cron')->info('Generated calendar feed "' . $objFeed->feedName . '.xml"');
		// 	}
		// }
	}

	/**
	 * Generate an XML file and save it to the root directory
	 *
	 * @param array $arrFeed
	 */
	protected function generateFiles($arrFeed)
	{
		$arrPodcasts = StringUtil::deserialize($arrFeed['podcasts']);

		if (empty($arrPodcasts) || !\is_array($arrPodcasts))
		{
			return;
		}

		$strType = ($arrFeed['format'] == 'atom') ? 'generateAtom' : 'generateRss';
		$strLink = $arrFeed['feedBase'] ?: Environment::get('base');
		$strFile = $arrFeed['feedName'];

		$objFeed = new Feed($strFile);
		$objFeed->link = $strLink;
		$objFeed->title = $arrFeed['title'];
		$objFeed->description = $arrFeed['description'];
		$objFeed->language = $arrFeed['language'];
		$objFeed->published = $arrFeed['tstamp'];

		$arrUrls = array();
		$this->arrEvents = array();
		$time = time();

		// Get the upcoming events
		$objEpisode = EpisodeModel::findByPids($arrPodcasts, $arrFeed['maxItems']);

		// Parse the items
		if ($objEpisode !== null)
		{
			while ($objEpisode->next())
			{
				// Never add unpublished elements to the RSS feeds
				if (!$objEpisode->published || ($objEpisode->start && $objEpisode->start > $time) || ($objEpisode->stop && $objEpisode->stop <= $time))
				{
					continue;
				}

				$jumpTo = $objEpisode->getRelated('pid')->jumpTo;

				// No jumpTo page set (see #4784)
				if (!$jumpTo)
				{
					continue;
				}

				$objParent = $this->getPageWithDetails($jumpTo);

				// A jumpTo page is set but does no longer exist (see #5781)
				if ($objParent === null)
				{
					continue;
				}

				// Get the jumpTo URL
				if (!isset($arrUrls[$jumpTo]))
				{
					$arrUrls[$jumpTo] = $objParent->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/events/%s');
				}

				$strUrl = $arrUrls[$jumpTo];
				$this->addEvent($objEpisode, $objEpisode->startTime, $objEpisode->endTime, $strUrl);

			}
		}

		$count = 0;
		ksort($this->arrEvents);

		$container = System::getContainer();

		/** @var RequestStack $requestStack */
		$requestStack = System::getContainer()->get('request_stack');
		$currentRequest = $requestStack->getCurrentRequest();

		$origObjPage = $GLOBALS['objPage'] ?? null;

		// Add the feed items
		foreach ($this->arrEvents as $days)
		{
			foreach ($days as $events)
			{
				foreach ($events as $event)
				{
					if ($arrFeed['maxItems'] > 0 && $count++ >= $arrFeed['maxItems'])
					{
						break 3;
					}

					// Override the global page object (#2946)
					$GLOBALS['objPage'] = $this->getPageWithDetails(CalendarModel::findByPk($event['pid'])->jumpTo);

					// Push a new request to the request stack (#3856)
					$request = $this->createSubRequest($event['link'], $currentRequest);
					$request->attributes->set('_scope', 'frontend');
					$requestStack->push($request);

					$objItem = new FeedItem();
					$objItem->title = $event['title'];
					$objItem->link = $event['link'];
					$objItem->published = $event['tstamp'];
					$objItem->begin = $event['startTime'];
					$objItem->end = $event['endTime'];

					if ($event['isRepeated'] ?? null)
					{
						$objItem->guid = $event['link'] . '#' . date('Y-m-d', $event['startTime']);
					}

					if (($objAuthor = UserModel::findById($event['author'])) !== null)
					{
						$objItem->author = $objAuthor->name;
					}

					// Prepare the description
					if ($arrFeed['source'] == 'source_text')
					{
						$strDescription = '';
						$objElement = ContentModel::findPublishedByPidAndTable($event['id'], 'tl_calendar_events');

						if ($objElement !== null)
						{
							// Overwrite the request (see #7756)
							$strRequest = Environment::get('request');
							Environment::set('request', $objItem->link);

							while ($objElement->next())
							{
								$strDescription .= $this->getContentElement($objElement->current());
							}

							Environment::set('request', $strRequest);
						}
					}
					else
					{
						$strDescription = $event['teaser'] ?? '';
					}

					$strDescription = System::getContainer()->get('contao.insert_tag.parser')->replaceInline($strDescription);
					$objItem->description = $this->convertRelativeUrls($strDescription, $strLink);

					if (\is_array($event['media:content']))
					{
						foreach ($event['media:content'] as $enclosure)
						{
							$objItem->addEnclosure($enclosure, $strLink, 'media:content', $arrFeed['imgSize']);
						}
					}

					if (\is_array($event['enclosure']))
					{
						foreach ($event['enclosure'] as $enclosure)
						{
							$objItem->addEnclosure($enclosure, $strLink);
						}
					}

					$objFeed->addItem($objItem);

					$requestStack->pop();
				}
			}
		}

		$GLOBALS['objPage'] = $origObjPage;

		$webDir = StringUtil::stripRootDir($container->getParameter('contao.web_dir'));

		// Create the file
		File::putContent($webDir . '/share/' . $strFile . '.xml', System::getContainer()->get('contao.insert_tag.parser')->replaceInline($objFeed->$strType()));
	}



	/**
	 * Add an event to the array of active events
	 *
	 * @param CalendarEventsModel $objEvent
	 * @param integer             $intStart
	 * @param integer             $intEnd
	 * @param string              $strUrl
	 * @param string              $strBase
	 * @param boolean             $isRepeated
	 *
	 * @deprecated Deprecated since Contao 4.9, to be made private in Contao 5.0
	 */
	protected function addEvent($objEvent, $intStart, $intEnd, $strUrl, $strBase='', $isRepeated=false)
	{
		if (static::class !== self::class)
		{
			trigger_deprecation('contao/calendar-bundle', '4.9', 'Calling "%s()" from an extended class has been deprecated, it will be made private in Contao 5.0.', __METHOD__);
		}

		if ($intEnd < time())
		{
			return; // see #3917
		}

		$intKey = date('Ymd', $intStart);
		//$span = self::calculateSpan($intStart, $intEnd);
		$format = $objEvent->addTime ? 'datimFormat' : 'dateFormat';

		/** @var PageModel $objPage */
		global $objPage;

		if ($objPage instanceof PageModel)
		{
			$date = $objPage->$format;
			$dateFormat = $objPage->dateFormat;
			$timeFormat = $objPage->timeFormat;
		}
		else
		{
			// Called in the back end (see #4026)
			$date = Config::get($format);
			$dateFormat = Config::get('dateFormat');
			$timeFormat = Config::get('timeFormat');
		}

		// Add date
		if ($span > 0)
		{
			$title = Date::parse($date, $intStart) . $GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'] . Date::parse($date, $intEnd);
		}
		else
		{
			$title = Date::parse($dateFormat, $intStart) . ($objEvent->addTime ? ' (' . Date::parse($timeFormat, $intStart) . (($intStart < $intEnd) ? $GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'] . Date::parse($timeFormat, $intEnd) : '') . ')' : '');
		}

		// Add title and link
		$title .= ' ' . $objEvent->title;

		// Backwards compatibility (see #8329)
		if ($strBase && !preg_match('#^https?://#', $strUrl))
		{
			$strUrl = $strBase . $strUrl;
		}

		$link = '';

		switch ($objEvent->source)
		{
			case 'external':
				$link = $objEvent->url;
				break;

			case 'internal':
				if (($objTarget = $objEvent->getRelated('jumpTo')) instanceof PageModel)
				{
					/** @var PageModel $objTarget */
					$link = $objTarget->getAbsoluteUrl();
				}
				break;

			case 'article':
				if (($objEpisode = ArticleModel::findByPk($objEvent->articleId)) instanceof ArticleModel && ($objPid = $objEpisode->getRelated('pid')) instanceof PageModel)
				{
					/** @var PageModel $objPid */
					$link = StringUtil::ampersand($objPid->getAbsoluteUrl('/articles/' . ($objEpisode->alias ?: $objEpisode->id)));
				}
				break;

			default:
				$link = sprintf(preg_replace('/%(?!s)/', '%%', $strUrl), ($objEvent->alias ?: $objEvent->id));
				break;
		}

		// Store the whole row (see #5085)
		$arrEvent = $objEvent->row();

		// Override link and title
		$arrEvent['link'] = $link;
		$arrEvent['title'] = $title;

		// Set the current start and end date
		$arrEvent['startDate'] = $intStart;
		$arrEvent['endDate'] = $intEnd;
		$arrEvent['isRepeated'] = $isRepeated;

		// Reset the enclosures (see #5685)
		$arrEvent['enclosure'] = array();
		$arrEvent['media:content'] = array();

		// Add the article image as enclosure
		if ($objEvent->addImage)
		{
			$objFile = FilesModel::findByUuid($objEvent->singleSRC);

			if ($objFile !== null)
			{
				$arrEvent['media:content'][] = $objFile->path;
			}
		}

		// Enclosures
		if ($objEvent->addEnclosure)
		{
			$arrEnclosure = StringUtil::deserialize($objEvent->enclosure, true);

			if (\is_array($arrEnclosure))
			{
				$objFile = FilesModel::findMultipleByUuids($arrEnclosure);

				if ($objFile !== null)
				{
					while ($objFile->next())
					{
						$arrEvent['enclosure'][] = $objFile->path;
					}
				}
			}
		}

		$this->arrEvents[$intKey][$intStart][] = $arrEvent;
	}

	/**
	 * Return the names of the existing feeds so they are not removed
	 *
	 * @return array
	 */
	public function purgeOldFeeds()
	{
		$arrFeeds = array();
		$objFeeds = CalendarFeedModel::findAll();

		if ($objFeeds !== null)
		{
			while ($objFeeds->next())
			{
				$arrFeeds[] = $objFeeds->alias ?: 'calendar' . $objFeeds->id;
			}
		}

		return $arrFeeds;
	}

	/**
	 * Return the page object with loaded details for the given page ID
	 *
	 * @param  integer        $intPageId
	 * @return PageModel|null
	 */
	private function getPageWithDetails($intPageId)
	{
		if (!isset(self::$arrPageCache[$intPageId]))
		{
			self::$arrPageCache[$intPageId] = PageModel::findWithDetails($intPageId);
		}

		return self::$arrPageCache[$intPageId];
	}

	/**
	 * Creates a sub request for the given URI.
	 */
	private function createSubRequest(string $uri, Request $request = null): Request
	{
		$cookies = null !== $request ? $request->cookies->all() : array();
		$server = null !== $request ? $request->server->all() : array();

		unset($server['HTTP_IF_MODIFIED_SINCE'], $server['HTTP_IF_NONE_MATCH']);

		$subRequest = Request::create($uri, 'get', array(), $cookies, array(), $server);

		if (null !== $request)
		{
			if ($request->get('_format'))
			{
				$subRequest->attributes->set('_format', $request->get('_format'));
			}

			if ($request->getDefaultLocale() !== $request->getLocale())
			{
				$subRequest->setLocale($request->getLocale());
			}
		}

		// Always set a session (#3856)
		$subRequest->setSession(new Session(new MockArraySessionStorage()));

		return $subRequest;
	}
}

class_alias(Calendar::class, 'Calendar');
