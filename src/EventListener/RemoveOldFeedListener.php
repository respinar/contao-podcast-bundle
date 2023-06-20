<?php

declare(strict_types=1);

namespace Respinar\PodcastBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

use Respinar\PodcastBundle\Model\ChannelModel;

#[AsHook('removeOldFeeds')]
class RemoveOldFeedListener
{
    public function __invoke(): array
    {
        $arrFeeds = array();
		$objFeeds = ChannelModel::findAll();

		if ($objFeeds !== null)
		{
			while ($objFeeds->next())
			{
				$arrFeeds[] = $objFeeds->feedAlias ?: 'podcast' . $objFeeds->id;
			}
		}

		return $arrFeeds;
    }
}
