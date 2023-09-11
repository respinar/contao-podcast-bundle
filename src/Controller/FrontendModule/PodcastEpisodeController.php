<?php

declare(strict_types=1);

/*
 * This file is part of Contao Simple Podcast.
 *
 * (c) Hamid Peywasti 2023 <hamid@respinar.com>
 *
 * @license MIT
 */

namespace Respinar\PodcastBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;

use Respinar\PodcastBundle\Controller\Podcast;

use Respinar\PodcastBundle\Model\EpisodeModel;
use Respinar\PodcastBundle\Model\ChannelModel;

#[AsFrontendModule(category: "podcasts")]
class PodcastEpisodeController extends AbstractFrontendModuleController
{
    public const TYPE = 'podcast_episode';

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $page = $this->getPageModel();

        // Set the item from the auto_item parameter
		if (!isset($_GET['items']) && $GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item']))
		{
			Input::setGet('items', Input::get('auto_item'));
		}

        $objEpisode = EpisodeModel::findOneByAlias(Input::get('items'));

        $objPodcast = ChannelModel::findByIdOrAlias($objEpisode->pid);

        $template->episode = Podcast::parseEpisode($objEpisode, $objPodcast, $model, $page);

        // Page title and Description
        $responseContext = System::getContainer()->get('contao.routing.response_context_accessor')->getResponseContext();

		if ($responseContext && $responseContext->has(HtmlHeadBag::class))
		{

			/** @var HtmlHeadBag $htmlHeadBag */
			$htmlHeadBag = $responseContext->get(HtmlHeadBag::class);
			$htmlDecoder = System::getContainer()->get('contao.string.html_decoder');

			if ($objEpisode->pageTitle)
			{
				$htmlHeadBag->setTitle($objEpisode->pageTitle); // Already stored decoded
			}
			elseif ($objEpisode->title)
			{
				$htmlHeadBag->setTitle($objEpisode->title);
			}

			if ($objEpisode->description)
			{
				$htmlHeadBag->setMetaDescription($htmlDecoder->inputEncodedToPlainText($objEpisode->description));
			}

		}

        return $template->getResponse();
    }
}
