<?php

declare(strict_types=1);

/*
 * This file is part of Contao Simple Podcast.
 *
 * (c) Hamid Peywasti 2023 <hamid@respinar.com>
 *
 * @license MIT
 */

use Respinar\PodcastBundle\Controller\FrontendModule\PodcastChannelController;

/**
 * Backend modules
 */
$GLOBALS['TL_LANG']['MOD']['podcasts'] = ['Podcasts', 'Manage Podcasts'];

/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['podcast_modules'] = 'Podacsts';
$GLOBALS['TL_LANG']['FMD'][PodcastChannelController::TYPE] = ['Podcast Channel', 'Show podcasts of a Channel'];

