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
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Respinar\PodcastBundle\Controller\Podcast;

use Respinar\PodcastBundle\Model\EpisodeModel;
use Respinar\PodcastBundle\Model\ChannelModel;

use Respinar\ContaoPodcastBundle;

#[AsFrontendModule(category: "podcasts")]
class PodcastChannelController extends AbstractFrontendModuleController
{
    public const TYPE = 'podcast_channel';

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $page = $this->getPageModel();

        $objChannel = ChannelModel::findOneBy('id', StringUtil::deserialize($model->podcast)[0]);

        $objEpisodes = EpisodeModel::findBy('pid', $objChannel->id);

        $template->arrEpisodes = Podcast::parseEpisodes($objEpisodes, $model, $page);

        return $template->getResponse();
    }
}
