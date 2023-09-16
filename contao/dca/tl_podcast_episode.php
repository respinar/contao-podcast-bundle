<?php

declare(strict_types=1);

/*
 * This file is part of Contao Simple Podcast.
 *
 * (c) Hamid Peywasti 2023 <hamid@respinar.com>
 *
 * @license MIT
 */

use Contao\Backend;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Contao\BackendUser;
use Contao\Config;
use Contao\System;

use Respinar\PodcastBundle\Model\ChannelModel;

/**
 * Table tl_podcast_episode
 */
$GLOBALS['TL_DCA']['tl_podcast_episode'] = array(
    'config'      => array(
        'dataContainer'    => DC_Table::class,
        'ptable'           => 'tl_podcast_channel',
        'enableVersioning' => true,
        'switchToEdit'     => true,
        'markAsCopy'       => 'headline',
        'sql'              => array(
            'keys' => array(
                'id' => 'primary',
				'alias' => 'index',
				'pid,published,featured,start,stop' => 'index'
            )
        ),
    ),
    'list'        => array(
        'sorting'           => array(
            'mode'        => DataContainer::MODE_PARENT,
            'fields'      => array('date'),
            'flag'        => DataContainer::SORT_INITIAL_LETTER_ASC,
            'headerFields'=> array('title', 'jumpTo', 'author', 'feed', 'feedAlias'),

            'panelLayout' => 'filter;sort,search,limit'
        ),
        'label'             => array(
            'fields' => array('date', 'episodeNumber', 'title'),
            'format' => '<span style="color:#999;padding-left:3px">[%s]</span> episode: %s - %s',
        ),
        'global_operations' => array(
            'all' => array(
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations'        => array(
            'edit'   => array(
                'href'  => 'act=edit',
                'icon'  => 'edit.svg'
            ),
            'copy'   => array(
                'href'  => 'act=copy',
                'icon'  => 'copy.svg'
            ),
            'delete' => array(
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
            ),
			'toggle' => array
			(
				'href'                => 'act=toggle&amp;field=published',
				'icon'                => 'visible.svg',
				'showInHeader'        => true
			),
			'feature' => array
			(
				'href'                => 'act=toggle&amp;field=featured',
				'icon'                => 'featured.svg',
			),
            'show'   => array(
                'href'       => 'act=show',
                'icon'       => 'show.svg',
                'attributes' => 'style="margin-right:3px"'
            ),
        )
    ),

    // Palettes
    'palettes' => array
	(
		//'__selector__'      => array('addImage'),
		'default'           => '
			{title_legend},title,featured,alias,episodeNumber;
			{date_legend},date,author;
			{podcast_legend},podcastSRC;
			{image_legend},coverSRC;
			{meta_legend},pageTitle,duration,description;
			{teaser_legend},subheadline,teaser;
			{expert_legend:hide},cssClass;
			{publish_legend},published,start,stop'
	),

	// Subpalettes
	'subpalettes' => array
	(
		//'addImage'                   => 'singleSRC',
	),
    'fields'      => array(
        'id'             => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
		(
			'foreignKey'              => 'tl_podcast_channel.title',
			'sql'                     => "int(10) unsigned NOT NULL default 0",
			'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
		),
        'tstamp'         => array(
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'title'          => array(
            'exclude'   => true,
			'search'    => true,
			'inputType' => 'text',
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
			'eval'      => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'featured' => array
		(
			'exclude'                 => true,
			'toggle'                  => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'alias' => array
		(
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'alias', 'doNotCopy'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'save_callback' => array
			(
				array('tl_podcast_episode', 'generateAlias')
			),
			'sql'                     => "varchar(255) BINARY NOT NULL default ''"
		),
		'author' => array
		(
			'default'                 => BackendUser::getInstance()->id,
			'exclude'                 => true,
			'search'                  => true,
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => DataContainer::SORT_ASC,
			'inputType'               => 'select',
			'foreignKey'              => 'tl_user.name',
			'eval'                    => array('doNotCopy'=>true, 'chosen'=>true, 'mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NOT NULL default 0",
			'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
		),
		'date' => array
		(
			'default'                 => time(),
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => DataContainer::SORT_MONTH_DESC,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			// 'load_callback' => array
			// (
			// 	array('tl_news', 'loadDate')
			// ),
			'sql'                     => "int(10) unsigned NOT NULL default 0"
		),
        'episodeNumber' => array
		(
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'number', 'mandatory'=>true, 'doNotCopy'=>true, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NULL"
		),
		'pageTitle' => array
		(
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'description' => array
		(
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('style'=>'height:60px', 'decodeEntities'=>true, 'tl_class'=>'clr'),
			'sql'                     => "text NULL"
		),
		'duration' => array
		(
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'number', 'mandatory'=>true, 'doNotCopy'=>true, 'tl_class'=>'w50'),
			'sql'                     => "int(5) unsigned NULL"
		),
		'subheadline' => array
		(
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'long'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'teaser' => array
		(
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr'),
			'sql'                     => "text NULL"
		),
		'coverSRC' => array
		(
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'filesOnly'=>true, 'extensions'=>'%contao.image.valid_extensions%', 'mandatory'=>true),
			'sql'                     => "binary(16) NULL"
		),
		'podcastSRC' => array
		(
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('multiple'=>false, 'fieldType'=>'radio', 'filesOnly'=>true, 'isDownloads'=>true, 'extensions'=>'mp3, m4a, ogg', 'mandatory'=>true),
			'sql'                     => "binary(16) NULL"
		),
		'cssClass' => array
		(
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'noComments' => array
		(
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'published' => array
		(
			'exclude'                 => true,
			'toggle'                  => true,
			'filter'                  => true,
			'flag'                    => DataContainer::SORT_INITIAL_LETTER_ASC,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'start' => array
		(
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		),
		'stop' => array
		(
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		)

    )
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @property News $News
 */
class tl_podcast_episode extends Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import(BackendUser::class, 'User');
	}

	/**
	 * Auto-generate the news alias if it has not been set yet
	 *
	 * @param mixed         $varValue
	 * @param DataContainer $dc
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$aliasExists = function (string $alias) use ($dc): bool
		{
			return $this->Database->prepare("SELECT id FROM tl_podcast_episode WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
		};

		// Generate alias if there is none
		if (!$varValue)
		{
			$varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->title, ChannelModel::findByPk($dc->activeRecord->pid)->jumpTo, $aliasExists);
		}
		elseif (preg_match('/^[1-9]\d*$/', $varValue))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
		}
		elseif ($aliasExists($varValue))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
		}

		return $varValue;
	}
}