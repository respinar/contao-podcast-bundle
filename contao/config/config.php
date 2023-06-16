<?php

/*
 * This file is part of Contao Simple Podcast.
 *
 * (c) Hamid Abbaszadeh 2023 <abbaszadeh.h@gmail.com>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/respinar/contao-podcast-bundle
 */

use Respinar\PodcastBundle\Model\ChannelModel;
use Respinar\PodcastBundle\Model\EpisodeModel;

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['podcasts'] = array(
    'tables' => array('tl_podcast_channel', 'tl_podcast_feed', 'tl_podcast_episode')
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_podcast_channel'] = ChannelModel::class;
$GLOBALS['TL_MODELS']['tl_podcast_episode'] = EpisodeModel::class;
