<?php

declare(strict_types=1);

/*
 * This file is part of Contao Simple Podcast.
 *
 * (c) Hamid Peywasti 2024 <hamid@respinar.com>
 *
 * @license MIT
 */

use Contao\Backend;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\StringUtil;
use Contao\BackendUser;
use Contao\CoreBundle\Util\LocaleUtil;

/**
 * Table tl_podcast_channel
 */
$GLOBALS['TL_DCA']['tl_podcast_channel'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'c_table' => ['tl_podcast_episode'],
        'enableVersioning' => true,
        'switchToEdit' => true,
        'markAsCopy' => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary'
			]
		],
	],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['title'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit'
		],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
		],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			]
		],
        'operations' => [
            'edit' => [
                'href' => 'table=tl_podcast_episode',
                'icon' => 'edit.svg'
			],
            'editheader' => [
                'href' => 'act=edit',
                'icon' => 'header.svg'
			],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg'
			],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
			],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
                'attributes' => 'style="margin-right:3px"'
			],
		]
	],

    // Palettes
    'palettes' => [
		'__selector__' => ['feed', 'protected'],
		'default' => '{title_legend},title;{config_legend},overviewPage,jumpTo;{author_legend},owner,author;{feed_legend},feed;{detail_legend},coverSRC,description;{protected_legend:hide},protected;'
	],

	// Subpalettes
	'subpalettes' => [
		'protected' => 'groups',
		'feed' => 'feedAlias,format,language,feedBase,maxItems,imgSize',
		//'allowComments' => 'notify,sortOrder,perPage,moderate,bbcode,requireLogin,disableCaptcha'
	],
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
		],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
		],
        'title' => [
            'exclude' => true,
			'search' => true,
			'inputType' => 'text',
			'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
			'sql' => "varchar(255) NOT NULL default ''"
		],
		'feed' => [
			'exclude' => true,
			'toggle' => true,
			'filter' => true,
			'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
			'inputType' => 'checkbox',
			'eval' => ['doNotCopy' => true, 'submitOnChange' => true, 'tl_class' => 'w50 m12'],
			'sql' => "char(1) NOT NULL default ''"
		],
		'feedAlias' => [
			'exclude' => true,
			'search' => true,
			'inputType' => 'text',
			'eval' => ['mandatory' => true, 'rgxp' => 'alias', 'doNotCopy' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr'],
			// 'save_callback' => array
			// (
			// 	['tl_podcast_episode', 'generateAlias')
			// ),
			'sql' => "varchar(255) BINARY NOT NULL default ''"
		],
		'feedBase' => [
			'exclude' => true,
			'search' => true,
			'inputType' => 'text',
			'eval' => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 2048, 'dcaPicker' => true, 'tl_class' => 'w50'],
			'sql' => "varchar(2048) NOT NULL default ''"
		],
		'format' => [
			'exclude' => true,
			'filter' => true,
			'inputType' => 'select',
			'options' => ['rss' => 'RSS 2.0', 'atom' => 'Atom'],
			'eval' => ['tl_class' => 'w50'],
			'sql' => "varchar(32) NOT NULL default 'rss'"
		],
		'maxItems' => [
			'exclude' => true,
			'inputType' => 'text',
			'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50'],
			'sql' => "smallint(5) unsigned NOT NULL default 25"
		],
		'imgSize' => [
			'exclude' => true,
			'inputType' => 'imageSize',
			'reference' => &$GLOBALS['TL_LANG']['MSC'],
			'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
			'options_callback' => ['contao.listener.image_size_options', '__invoke'],
			'sql' => "varchar(255) NOT NULL default ''"
		],
		'owner' => [
			'default' => BackendUser::getInstance()->id,
			'exclude' => true,
			'search' => true,
			'filter' => true,
			'sorting' => true,
			'flag' => DataContainer::SORT_ASC,
			'inputType' => 'select',
			'foreignKey' => 'tl_user.name',
			'eval' => ['doNotCopy' => true, 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
			'sql' => "int(10) unsigned NOT NULL default 0",
			'relation' => ['type' => 'hasOne', 'load' => 'lazy']
			],
		'author' => [
			'default' => BackendUser::getInstance()->id,
			'exclude' => true,
			'search' => true,
			'filter' => true,
			'sorting' => true,
			'flag' => DataContainer::SORT_ASC,
			'inputType' => 'select',
			'foreignKey' => 'tl_user.name',
			'eval' => ['doNotCopy' => true, 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
			'sql' => "int(10) unsigned NOT NULL default 0",
			'relation' => ['type' => 'hasOne', 'load' => 'lazy']
			],
		'language' => [
			'exclude' => true,
			'search' => true,
			'inputType' => 'text',
			'eval' => ['mandatory' => true, 'maxlength'=>64, 'nospace' => true, 'decodeEntities' => true, 'doNotCopy' => true, 'tl_class' => 'w50'],
			'sql' => "varchar(64) NOT NULL default ''",
			'save_callback' => [
				static function ($value) {
					// Make sure there is at least a basic language
					if (!preg_match('/^[a-z]{2,}/i', $value))
					{
						throw new RuntimeException($GLOBALS['TL_LANG']['ERR']['language']);
					}

					return LocaleUtil::canonicalize($value);
				}
			]
		],
		'description' => [
			'exclude' => true,
			'search' => true,
			'inputType' => 'textarea',
			'eval' => ['style' => 'height:60px', 'decodeEntities' => true, 'tl_class' => 'clr'],
			'sql' => "text NULL"
		],
		'coverSRC' => [
			'exclude' => true,
			'inputType' => 'fileTree',
			'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'mandatory' => true],
			'sql' => "binary(16) NULL"
		],
		'overviewPage' => [
			'exclude' => true,
			'inputType' => 'pageTree',
			'foreignKey' => 'tl_page.title',
			'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
			'sql' => "int(10) unsigned NOT NULL default 0",
			'relation' => ['type' => 'hasOne', 'load' => 'lazy']
		],
        'jumpTo' => [
			'exclude' => true,
			'inputType' => 'pageTree',
			'foreignKey' => 'tl_page.title',
			'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
			'sql' => "int(10) unsigned NOT NULL default 0",
			'relation' => ['type' => 'hasOne', 'load' => 'lazy']
		],
		'protected' => [
			'exclude' => true,
			'filter' => true,
			'inputType' => 'checkbox',
			'eval' => ['submitOnChange' => true],
			'sql' => "char(1) NOT NULL default ''"
		],
		'groups' => [
			'exclude' => true,
			'inputType' => 'checkbox',
			'foreignKey' => 'tl_member_group.name',
			'eval' => ['mandatory' => true, 'multiple' => true],
			'sql' => "blob NULL",
			'relation' => ['type' => 'hasMany', 'load' => 'lazy']
		],
		'allowComments' => [
			'exclude' => true,
			'filter' => true,
			'inputType' => 'checkbox',
			'eval' => ['submitOnChange' => true],
			'sql' => "char(1) NOT NULL default ''"
		],
		'notify' => [
			'exclude' => true,
			'inputType' => 'select',
			'options' => ['notify_admin', 'notify_author', 'notify_both'],
			'eval' => ['tl_class' => 'w50'],
			'reference' => &$GLOBALS['TL_LANG']['tl_podcast_channel'],
			'sql' => "varchar(16) NOT NULL default 'notify_admin'"
		],
		'sortOrder' => [
			'exclude' => true,
			'inputType' => 'select',
			'options' => ['ascending', 'descending'],
			'reference' => &$GLOBALS['TL_LANG']['MSC'],
			'eval' => ['tl_class' => 'w50 clr'],
			'sql' => "varchar(32) NOT NULL default 'ascending'"
		],
		'perPage' => [
			'exclude' => true,
			'inputType' => 'text',
			'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50'],
			'sql' => "smallint(5) unsigned NOT NULL default 0"
		],
		'moderate' => [
			'exclude' => true,
			'inputType' => 'checkbox',
			'eval' => ['tl_class' => 'w50'],
			'sql' => "char(1) NOT NULL default ''"
		],
		'bbcode' => [
			'exclude' => true,
			'inputType' => 'checkbox',
			'eval' => ['tl_class' => 'w50'],
			'sql' => "char(1) NOT NULL default ''"
		],
		'requireLogin' => [
			'exclude' => true,
			'inputType' => 'checkbox',
			'eval' => ['tl_class' => 'w50'],
			'sql' => "char(1) NOT NULL default ''"
		],
		'disableCaptcha' => [
			'exclude' => true,
			'inputType' => 'checkbox',
			'eval' => ['tl_class' => 'w50'],
			'sql' => "char(1) NOT NULL default ''"
		]
	]
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_podcast_channel extends Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct() {
		parent::__construct();
		$this->import(BackendUser::class, 'User');
	}

	public function manageFeeds($href, $label, $title, $class, $attributes)	{
		return '<a href="' . $this->addToUrl($href) . '" class="' . $class . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . $label . '</a> ';
	}

}