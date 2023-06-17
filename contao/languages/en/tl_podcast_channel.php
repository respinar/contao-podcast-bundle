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

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_podcast_channel']['title_legend'] = "Channel Title";
$GLOBALS['TL_LANG']['tl_podcast_channel']['config_legend'] = "Config";
$GLOBALS['TL_LANG']['tl_podcast_channel']['author_legend'] = "Ower and Author";
$GLOBALS['TL_LANG']['tl_podcast_channel']['feed_legend'] = "Feed settings";
$GLOBALS['TL_LANG']['tl_podcast_channel']['detail_legend'] = "Channel Conver and Description";
$GLOBALS['TL_LANG']['tl_podcast_channel']['protected_legend'] = "Access protection";

/**
* Global operations
*/
$GLOBALS['TL_LANG']['tl_podcast_channel']['new'] = ["New Channel", "Create a new Podcast Channel"];

/**
 * Operations
 */
$GLOBALS['TL_LANG']['tl_podcast_channel']['edit'] = "Datensatz mit ID: %s bearbeiten";
$GLOBALS['TL_LANG']['tl_podcast_channel']['copy'] = "Datensatz mit ID: %s kopieren";
$GLOBALS['TL_LANG']['tl_podcast_channel']['delete'] = "Datensatz mit ID: %s löschen";
$GLOBALS['TL_LANG']['tl_podcast_channel']['show'] = "Datensatz mit ID: %s ansehen";

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_podcast_channel']['title'] = ["Channel Title", "Please enter a podcast channel title."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['jumpTo'] = ["Redirect page", "Please choose the news reader page to which visitors will be redirected when clicking a podcast item."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['owner'] = ["Owenr", "Please select an owner."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['author'] = ["Author", "Please select an author"];
$GLOBALS['TL_LANG']['tl_podcast_channel']['feed'] = ["Podcast Feed", "Create a Podcast Feed in share folder."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['feedAlias'] = ["Feed alias", "Here you can enter a unique filename (without extension). The XML feed file will be auto-generated in the share directory of your Contao installation, e.g. as share/name.xml."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['format'] = ["Feed format", "Please choose a feed format."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['language'] = ["Feed language", "Please enter the feed language according to the ISO-639 standard (e.g. en or en-us)."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['feedBase'] = ["Base URL", "Please enter the base URL with protocol (e.g. http://)."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['maxItems'] = ["Maximum number of items", "Here you can limit the number of feed items. Set to 0 to export all."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['imgSize'] = ["Image size", "Here you can set the image dimensions and the resize mode."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['coverSRC'] = ["Channel Cover", "Please select a file or folder from the files directory."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['description'] = ["Channel Description", "Here you can enter a short description of the podcast feed."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['protected'] = ["Protect channel", "Show podcast episodes to certain member groups only."];
$GLOBALS['TL_LANG']['tl_podcast_channel']['group'] = ["Allowed member groups", "These groups will be able to see the podcast episodes in this archive."];


/**
 * References
 */
//$GLOBALS['TL_LANG']['tl_podcast_channel']['firstoption'] = "Erste Option";


/**
 * Buttons
 */
//$GLOBALS['TL_LANG']['tl_podcast_channel']['customButton'] = "Custom Routine starten";
