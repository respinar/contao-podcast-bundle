<?php

declare(strict_types=1);

/*
 * This file is part of Contao Simple Podcast.
 *
 * (c) Hamid Peywasti 2023 <hamid@respinar.com>
 *
 * @license MIT
 */

namespace Respinar\PodcastBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\Template;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Respinar\PodcastBundle\Controller\Podcast;

use Respinar\PodcastBundle\Model\EpisodeModel;
use Respinar\PodcastBundle\Model\ChannelModel;

#[AsContentElement(category: "podcasts")]
class PodcastEpisodeController extends AbstractContentElementController
{

    public const TYPE = 'podcast_episode';


    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        $page = $this->getPageModel();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request))
		{
			//return $template->getResponse();
		}

        $objEpisode = EpisodeModel::findOneByID($model->podcast_episode);

        $objPodcast = ChannelModel::findByIdOrAlias($objEpisode->pid);

        $model->imgSize = $model->size;

        $template->episode = Podcast::parseEpisode($objEpisode, $objPodcast, $model, $page);

        return $template->getResponse();
    }
}