<?php

declare(strict_types=1);

namespace Respinar\ContaoPodcastBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Respinar\ContaoPodcastBundle\Controller\Podcast;

use Respinar\ContaoPodcastBundle\Model\PodcastEpisodeModel;
use Respinar\ContaoPodcastBundle\Model\PodcastModel;

#[AsContentElement(category: "podcasts")]
class PodcastEpisodeController extends AbstractContentElementController
{

    public const TYPE = 'podcast_episode';

    
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        $objEpisode = PodcastEpisodeModel::findOneByID($model->podcast_episode);

        $objPodcast = PodcastModel::findByIdOrAlias($objEpisode->pid);

        $model->imgSize = $model->size;
      
        $template->episode = Podcast::parseEpisode($objEpisode, $objPodcast, $model);        

        return $template->getResponse();
    }
}