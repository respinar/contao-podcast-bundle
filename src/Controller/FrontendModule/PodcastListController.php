<?php

declare(strict_types=1);

namespace Respinar\ContaoPodcastBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Contao\Input;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Respinar\ContaoPodcastBundle\Controller\Podcast;

use Respinar\ContaoPodcastBundle\Model\PodcastEpisodeModel;
use Respinar\ContaoPodcastBundle\Model\PodcastModel;

use Respinar\ContaoPodcastBundle;

#[AsFrontendModule(category: "podcasts")]
class PodcastListController extends AbstractFrontendModuleController
{
    public const TYPE = 'podcast_list';

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
     
        $objPodcast = PodcastModel::findOneBy('id', StringUtil::deserialize($model->podcast)[0]);
                
        $objEpisodes = PodcastEpisodeModel::findBy('pid', $objPodcast->id);
      
        $template->arrEpisodes = Podcast::parseEpisodes($objEpisodes, $objPodcast, $model);    

        return $template->getResponse();
    }
}
