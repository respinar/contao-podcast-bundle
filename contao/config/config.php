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

use Respinar\PodcastBundle\Model\PodcastModel;
use Respinar\PodcastBundle\Model\EpisodeModel;

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['podcast_chanel'] = array(
    'tables' => array('tl_podcast','tl_podcast_episode')
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_podcast'] = PodcastModel::class;
$GLOBALS['TL_MODELS']['tl_podcast_episode'] = EpisodeModel::class;
