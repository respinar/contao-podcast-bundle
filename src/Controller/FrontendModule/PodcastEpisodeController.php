<?php

declare(strict_types=1);

namespace Respinar\ContaoPodcastBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Contao\Input;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Respinar\ContaoPodcastBundle\Controller\Podcast;

use Respinar\ContaoPodcastBundle\Model\PodcastEpisodeModel;
use Respinar\ContaoPodcastBundle\Model\PodcastModel;

use Respinar\ContaoPodcastBundle;

#[AsFrontendModule(category: "podcasts")]
class PodcastEpisodeController extends AbstractFrontendModuleController
{
    public const TYPE = 'podcast_episode';

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {        

        // Set the item from the auto_item parameter
		if (!isset($_GET['items']) && $GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item']))
		{
			Input::setGet('items', Input::get('auto_item'));
		}
         
        $objEpisode = PodcastEpisodeModel::findOneByAlias(Input::get('items'));

        $objPodcast = PodcastModel::findByIdOrAlias($objEpisode->pid);
      
        $template->episode = Podcast::parseEpisode($objEpisode, $objPodcast, $model);        

        return $template->getResponse();
    }
}
