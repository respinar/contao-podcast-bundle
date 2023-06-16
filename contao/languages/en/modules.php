<?php

declare(strict_types=1);

/*
 * This file is part of Contao Simple Podcast.
 *
 * (c) Hamid Abbaszadeh 2023 <abbaszadeh.h@gmail.com>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/respinar/contao-podcast-bundle
 */

use Respinar\PodcastBundle\Controller\FrontendModule\PodcastListController;

/**
 * Backend modules
 */
$GLOBALS['TL_LANG']['MOD']['podcast_chanel'] = ['Podcasts', 'Manage Podcasts'];

/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['podcast_modules'] = 'Podacsts';
$GLOBALS['TL_LANG']['FMD'][PodcastListController::TYPE] = ['Podcast List', 'Show list of podcasts'];

