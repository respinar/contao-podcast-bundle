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
use Contao\PageModel;
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
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();

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
		$objFeed = ChannelModel::findByPk($intId);

		if ($objFeed === null)
		{
			return;
		}

		$objFeed->feedName = $objFeed->alias ?: 'podcast' . $objFeed->id;

		// Delete XML file
		if (Input::get('act') == 'delete')
		{
			$this->import(Files::class, 'Files');
			$this->Files->delete($objFeed->feedName . '.xml');
		}

		// Update XML file
		else
		{
			$this->generateFiles($objFeed->row());

			System::getContainer()->get('monolog.logger.contao.cron')->info('Generated podcast feed "' . $objFeed->feedName . '.xml"');
		}
	}

	/**
	 * Delete old files and generate all feeds
	 */
	public function generateFeeds()
	{
		$this->import(Automator::class, 'Automator');
		$this->Automator->purgeXmlFiles();

		$objFeed = ChannelModel::findAll();

		if ($objFeed !== null)
		{
			while ($objFeed->next())
			{
				$objFeed->feedName = $objFeed->alias ?: 'podcast' . $objFeed->id;
				$this->generateFiles($objFeed->row());

				System::getContainer()->get('monolog.logger.contao.cron')->info('Generated podcast feed "' . $objFeed->feedName . '.xml"');
			}
		}
	}

	/**
	 * Generate an XML files and save them to the root directory
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

		// Get the items
		if ($arrFeed['maxItems'] > 0)
		{
			$objEpisodes = EpisodeModel::findPublishedByPids($arrPodcasts, null, $arrFeed['maxItems']);
		}
		else
		{
			$objEpisodes = EpisodeModel::findPublishedByPids($arrPodcasts);
		}

		$container = System::getContainer();

		// Parse the items
		if ($objEpisodes !== null)
		{
			$arrUrls = array();

			/** @var RequestStack $requestStack */
			$requestStack = $container->get('request_stack');
			$currentRequest = $requestStack->getCurrentRequest();

			$time = time();
			$origObjPage = $GLOBALS['objPage'] ?? null;

			foreach ($objEpisodes as $objEpisode)
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

				// Override the global page object (#2946)
				$GLOBALS['objPage'] = $objParent;

				// Get the jumpTo URL
				if (!isset($arrUrls[$jumpTo]))
				{
					$arrUrls[$jumpTo] = $objParent->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s');
				}

				$strUrl = $arrUrls[$jumpTo];

				$objItem = new FeedItem();
				$objItem->title = $objEpisode->headline;
				$objItem->link = $this->getLink($objEpisode, $strUrl);
				$objItem->published = $objEpisode->date;

				// Push a new request to the request stack (#3856)
				$request = $this->createSubRequest($objItem->link, $currentRequest);
				$request->attributes->set('_scope', 'frontend');
				$requestStack->push($request);

				/** @var UserModel $objAuthor */
				if (($objAuthor = $objEpisode->getRelated('author')) instanceof UserModel)
				{
					$objItem->author = $objAuthor->name;
				}

				// Prepare the description

				$strDescription = $objEpisode->teaser ?? '';

				$strDescription = $container->get('contao.insert_tag.parser')->replaceInline($strDescription);
				$objItem->description = $this->convertRelativeUrls($strDescription, $strLink);

				// Add the article image as enclosure
				if ($objEpisode->coverSRC)
				{
					$objFile = FilesModel::findByUuid($objEpisode->coverSRC);

					if ($objFile !== null)
					{
						$objItem->addEnclosure($objFile->path, $strLink, 'media:content', $arrFeed['imgSize']);
					}
				}

				// Enclosures
				if ($objEpisode->podcastSRC)
				{
					$arrEnclosure = StringUtil::deserialize($objEpisode->podcastSRC, true);

					if (\is_array($arrEnclosure))
					{
						$objFile = FilesModel::findMultipleByUuids($arrEnclosure);

						if ($objFile !== null)
						{
							while ($objFile->next())
							{
								$objItem->addEnclosure($objFile->path, $strLink);
							}
						}
					}
				}

				$objFeed->addItem($objItem);

				$requestStack->pop();
			}

			$GLOBALS['objPage'] = $origObjPage;
		}

		$webDir = StringUtil::stripRootDir($container->getParameter('contao.web_dir'));

		// Create the file
		File::putContent($webDir . '/share/' . $strFile . '.xml', $container->get('contao.insert_tag.parser')->replaceInline($objFeed->$strType()));
	}

	/**
	 * Return the link of a news article
	 *
	 * @param NewsModel $objItem
	 * @param string    $strUrl
	 * @param string    $strBase
	 *
	 * @return string
	 */
	protected function getLink($objItem, $strUrl, $strBase='')
	{

		if (($objTarget = $objItem->getRelated('jumpTo')) instanceof PageModel)
		{
			/** @var PageModel $objTarget */
			return $objTarget->getAbsoluteUrl();
		}

		// Backwards compatibility (see #8329)
		if ($strBase && !preg_match('#^https?://#', $strUrl))
		{
			$strUrl = $strBase . $strUrl;
		}

		// Link to the default page
		return sprintf(preg_replace('/%(?!s)/', '%%', $strUrl), ($objItem->alias ?: $objItem->id));
	}

	/**
	 * Return the names of the existing feeds so they are not removed
	 *
	 * @return array
	 */
	public function purgeOldFeeds()
	{
		$arrFeeds = array();
		$objFeeds = ChannelModel::findAll();

		if ($objFeeds !== null)
		{
			while ($objFeeds->next())
			{
				$arrFeeds[] = $objFeeds->alias ?: 'podcast' . $objFeeds->id;
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

class_alias(PodcastFeed::class, 'PodcastFeed');
