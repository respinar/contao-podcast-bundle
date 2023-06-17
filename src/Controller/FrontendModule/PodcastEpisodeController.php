<?php

declare(strict_types=1);

namespace Respinar\PodcastBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Contao\Input;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Respinar\PodcastBundle\Controller\Podcast;

use Respinar\PodcastBundle\Model\EpisodeModel;
use Respinar\PodcastBundle\Model\ChannelModel;

use Respinar\ContaoPodcastBundle;

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

        return $template->getResponse();
    }
}
