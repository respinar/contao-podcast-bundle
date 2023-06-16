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

use Contao\Controller;

use Respinar\PodcastBundle\Controller\FrontendModule\PodcastListController;
use Respinar\PodcastBundle\Controller\FrontendModule\PodcastEpisodeController;

/**
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes'][PodcastListController::TYPE] = '
    {title_legend},name,headline,type;
    {config_legend},podcast,numberOfItems,podcast_featured,podcast_order,skipFirst,perPage;
    {template_legend:hide},podcast_metaFields,podcast_template,customTpl;
    {image_legend:hide},imgSize;
    {protected_legend:hide},protected;
    {expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][PodcastEpisodeController::TYPE] = '
    {title_legend},name,headline,type;
    {config_legend},podcast,overviewPage,customLabel;
    {template_legend:hide},podcast_metaFields,podcast_template,customTpl;
    {image_legend:hide},imgSize;
    {protected_legend:hide},protected;
    {expert_legend:hide},guests,cssID';



// Add fields to tl_module
$GLOBALS['TL_DCA']['tl_module']['fields']['podcast'] = array
(
	'exclude'                 => true,
	'inputType'               => 'checkbox',
    'foreignKey'              => 'tl_podcast.title',
	//'options_callback'        => array('tl_module_news', 'getNewsArchives'),
	'eval'                    => array('multiple'=>true, 'foreignTable' => 'tl_podcast', 'mandatory'=>true),
	'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['podcast_featured'] = array
(
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('all_items', 'featured', 'unfeatured', 'featured_first'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('tl_class'=>'w50 clr'),
	'sql'                     => "varchar(16) COLLATE ascii_bin NOT NULL default 'all_items'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['podcast_metaFields'] = array
(
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => array('date', 'author', 'comments'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('multiple'=>true),
	'sql'                     => "varchar(255) COLLATE ascii_bin NOT NULL default 'a:2:{i:0;s:4:\"date\";i:1;s:6:\"author\";}'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['podcast_template'] = array
(
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback' => static function ()
	{
		return Controller::getTemplateGroup('podcast_');
	},
	'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
	'sql'                     => "varchar(64) COLLATE ascii_bin NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['podcast_format'] = array
(
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('news_day', 'news_month', 'news_year'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('tl_class'=>'w50 clr', 'submitOnChange'=>true),
	'sql'                     => "varchar(32) COLLATE ascii_bin NOT NULL default 'news_month'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['podcast_startDay'] = array
(
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array(0, 1, 2, 3, 4, 5, 6),
	'reference'               => &$GLOBALS['TL_LANG']['DAYS'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "smallint(5) unsigned NOT NULL default 0"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['podcast_order'] = array
(
	'exclude'                 => true,
	'inputType'               => 'select',
	//'options_callback'        => array('tl_module_news', 'getSortingOptions'),
    'options'                 => array('order_date_asc', 'order_date_desc'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(32) COLLATE ascii_bin NOT NULL default 'order_date_desc'"
);