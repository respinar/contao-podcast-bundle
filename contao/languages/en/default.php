<?php

declare(strict_types=1);

/*
 * This file is part of Contao Simple Podcast.
 *
 * (c) Hamid Peywasti 2023 <hamid@respinar.com>
 *
 * @license MIT
 */

use Respinar\PodcastBundle\Controller\ContentElement\PodcastController;
use Respinar\PodcastBundle\Controller\FrontendModule\PodcastChannelController;
use Respinar\PodcastBundle\Controller\FrontendModule\PodcastEpisodeController;

/**
 * Backend modules
 */
$GLOBALS['TL_LANG']['MOD']['podcasts'] = ['Podcasts', 'Manage Podcasts'];

/**
 * Content element
 */
$GLOBALS['TL_LANG']['CTE'][PodcastController::TYPE] = ['Podcast', 'Add an Episode of podcast'];

/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['podcast_modules'] = 'Podacsts';
$GLOBALS['TL_LANG']['FMD'][PodcastEpisodeController::TYPE] = ['Episode reader', 'Show deatil of an Episode'];
$GLOBALS['TL_LANG']['FMD'][PodcastChannelController::TYPE] = ['Channel list', 'Show Episodes of a Channel'];

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['notExist'] = 'There is a problem!';
$GLOBALS['TL_LANG']['MSC']['accessError'] = 'You do not have access to this channel!';
$GLOBALS['TL_LANG']['MSC']['emptyChannel'] = 'There is no episode in this channel!';

/**
 * Errors
 */
//$GLOBALS['TL_LANG']['ERR'][''] = '';
