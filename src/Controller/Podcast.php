<?php

declare(strict_types=1);

namespace Respinar\PodcastBundle\Controller;

use Contao\System;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Environment;
use Contao\Config;
use Contao\ContentModel;
use Contao\Controller;
use Contao\FilesModel;
use Contao\CoreBundle\Util\LocaleUtil;
use Contao\File;

class Podcast {

    /**
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();

    static public function parseEpisode ($objEpisode, $objPodcast, $model, $blnAddArchive=false) {

		/** @var PageModel $objPage */
		global $objPage;

        $objTemplate = new FrontendTemplate($model->podcast_template);

		$objTemplate->setData($objEpisode->row());

		$objTemplate->link = Podcast::generateEpisodeUrl($objEpisode, $objPodcast, $blnAddArchive);

		if ($objEpisode->coverSRC)
		{
			//$imgSize = $objArticle->size ?: null;

			// Override the default image size
			if ($model->imgSize)
			{
				$size = StringUtil::deserialize($model->imgSize);

				if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]) || ($size[2][0] ?? null) === '_')
				{
					$imgSize = $model->imgSize;
				}
			}

			$figure = System::getContainer()
				->get('contao.image.studio')
				->createFigureBuilder()
				->setSize($imgSize)
				->from($objEpisode->coverSRC)
                ->buildIfResourceExists();
		}

        if (null !== $figure)
		{
			$figure->applyLegacyTemplateData($objTemplate);
		}

		if ($objEpisode->podcastSRC)
		{
			$source = StringUtil::deserialize($objEpisode->podcastSRC);

			$objFileModel = FilesModel::findByUuid($objEpisode->podcastSRC);

			// Convert the language to a locale (see #5678)
			$strLanguage = LocaleUtil::formatAsLocale($objPage->language);
			//$strCaption = $this->playerCaption;

			/** @var FilesModel $objFileModel */
			$objMeta = $objFileModel->getMetadata($strLanguage);
			$strTitle = null;

			if (null !== $objMeta)
			{
				$strTitle = $objMeta->getTitle();

				if (empty($strCaption))
				{
					$strCaption = $objMeta->getCaption();
				}
			}

			$objFile = new File($objFileModel->path);
			$objFile->title = StringUtil::specialchars($strTitle ?: $objFile->name);
			$objFile->title = StringUtil::specialchars($objFile->name);

			$objTemplate->file = $objFile;


		}

        return $objTemplate->parse();
    }


    static public function parseEpisodes ($objEpisodes, $objPodcast, $model, $blnAddArchive=false) {

        $arrEpisodes = array();

        foreach($objEpisodes as $objEpisode){
            $arrEpisodes[] = Podcast::parseEpisode($objEpisode, $objPodcast, $model, $blnAddArchive);
        }
        return $arrEpisodes;
    }


    static public function generateEpisodeUrl ($objItem, $objPodcast, $blnAddArchive=false, $blnAbsolute=false) {
        $strCacheKey = 'id_' . $objItem->id . ($blnAbsolute ? '_absolute' : '');

		// Load the URL from cache
		if (isset(self::$arrUrlCache[$strCacheKey]))
		{
			return self::$arrUrlCache[$strCacheKey];
		}

		// Initialize the cache
		self::$arrUrlCache[$strCacheKey] = null;

		// Link to the default page
		if (self::$arrUrlCache[$strCacheKey] === null)
		{
			$objPage = PageModel::findByPk($objPodcast->jumpTo);

			if (!$objPage instanceof PageModel)
			{
				self::$arrUrlCache[$strCacheKey] = StringUtil::ampersand(Environment::get('request'));
			}
			else
			{
				$params = (Config::get('useAutoItem') ? '/' : '/items/') . ($objItem->alias ?: $objItem->id);

				self::$arrUrlCache[$strCacheKey] = StringUtil::ampersand($blnAbsolute ? $objPage->getAbsoluteUrl($params) : $objPage->getFrontendUrl($params));
			}
		}

		return self::$arrUrlCache[$strCacheKey];
    }

}