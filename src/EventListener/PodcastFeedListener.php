<?php

declare(strict_types=1);

namespace Respinar\PodcastBundle\EventListener;

use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\CoreBundle\InsertTag\InsertTagParser;

use Contao\System;
use Contao\StringUtil;
use Contao\Environment;
use Contao\Feed;
use Contao\FeedItem;
use Contao\UserModel;
use Contao\FilesModel;
use Contao\File;
use Contao\Controller;

// use FeedIo\Feed;
// use FeedIo\Specification;
// use FeedIo\Feed\Item;
// use FeedIo\Feed\Item\Author;
// use FeedIo\Feed\Item\AuthorInterface;
// use FeedIo\Feed\Item\Media;
// use FeedIo\Feed\ItemInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

use Respinar\PodcastBundle\Model\ChannelModel;
use Respinar\PodcastBundle\Model\EpisodeModel;


use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('generateXmlFiles')]
class PodcastFeedListener
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ImageFactoryInterface $imageFactory,
        private readonly InsertTagParser $insertTags,
        //private readonly string $projectDir,
        private readonly EntityCacheTags $cacheTags,
    ) {
    }

    public function __invoke(): void
    {
        $objFeeds = ChannelModel::findAll();

		if ($objFeeds !== null)
		{
			foreach ($objFeeds as $objFeed)
			{
				$objFeed->feedName = $objFeeds->feedAlias ?: 'podcast' . $objFeeds->id;

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
		$podcast = StringUtil::deserialize($arrFeed['id']);

		if ($podcast)
		{
			//return;
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
			$objEpisodes = EpisodeModel::findBy('pid', $arrFeed['id']);//, null, $arrFeed['maxItems']);
		}
		else
		{
			$objEpisodes = EpisodeModel::findBy('pid', $arrFeed['id']); //findPublishedByPids($arrFeed['id']);
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

				$objItem = new FeedItem();
				$objItem->title = $objEpisode->title;
				$objItem->link = $this->getLink($objEpisode, $arrFeed['jumpTo'], '');
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
				$objItem->description = Controller::convertRelativeUrls($strDescription, $strLink);

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
					$arrEnclosure = [$objEpisode->podcastSRC];

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
		//$name = fgets(STDIN);
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
		// Link to the default page
		//return sprintf(preg_replace('/%(?!s)/', '%%', $strUrl), ($objItem->alias ?: $objItem->id));
        return '';
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
