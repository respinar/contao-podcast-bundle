<?php

declare(strict_types=1);

/*
 * This file is part of Contao Simple Podcast.
 *
 * (c) Hamid Peywasti 2024 <hamid@respinar.com>
 *
 * @license MIT
 */

namespace Respinar\PodcastBundle\Model;

use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

class EpisodeModel extends Model
{
    protected static $strTable = 'tl_podcast_episode';

	/**
	 * Find a published episode from one or more podcast channel by its ID or alias
	 *
	 * @param mixed $varId      The numeric ID or alias name
	 * @param array $arrPids    An array of parent IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return EpisodeModel|null The model or null if there are no episode
	 */
	public static function findPublishedByParentAndIdOrAlias($varId, $arrPids, array $arrOptions=array())
	{
		if (empty($arrPids) || !\is_array($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = !preg_match('/^[1-9]\d*$/', $varId) ? array("BINARY $t.alias=?") : array("$t.id=?");
		$arrColumns[] = "$t.pid IN(" . implode(',', array_map('\intval', $arrPids)) . ")";

		if (!static::isPreviewMode($arrOptions))
		{
			$time = Date::floorToMinute();
			$arrColumns[] = "$t.published=1 AND ($t.start='' OR $t.start<=$time) AND ($t.stop='' OR $t.stop>$time)";
		}

		return static::findOneBy($arrColumns, array($varId), $arrOptions);
	}

	/**
	 * Find published episodes with the default redirect target by their parent ID
	 *
	 * @param integer $intPid     The podcast channel ID
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Collection<EpisodeModel>|EpisodeModel[]|null A collection of models or null if there are no episode
	 */
	public static function findPublishedDefaultByPid($intPid, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = array("$t.pid=?");

		if (!static::isPreviewMode($arrOptions))
		{
			$time = Date::floorToMinute();
			$arrColumns[] = "$t.published=1 AND ($t.start='' OR $t.start<=$time) AND ($t.stop='' OR $t.stop>$time)";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.date DESC";
		}

		return static::findBy($arrColumns, array($intPid), $arrOptions);
	}

	/**
	 * Count published product items by their parent ID
	 *
	 * @param array   $arrPids     An array of product catalogs IDs
	 * @param boolean $blnFeatured If true, return only featured product, if false, return only unfeatured product
	 * @param array   $arrOptions  An optional options array
	 *
	 * @return integer The number of product items
	 */
	public static function countPublishedByPid($pid, $blnFeatured=null, array $arrOptions=array())
	{
		if (empty($pid))
		{
			return 0;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid = $pid");

		if ($blnFeatured === true)
		{
			$arrColumns[] = "$t.featured=1";
		}
		elseif ($blnFeatured === false)
		{
			$arrColumns[] = "$t.featured=''";
		}

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		return static::countBy($arrColumns, null, $arrOptions);
	}

	/**
	 * Find published product items by their parent ID
	 *
	 * @param array   $arrPids     An array of product catalogs IDs
	 * @param boolean $blnFeatured If true, return only featured product, if false, return only unfeatured product
	 * @param integer $intLimit    An optional limit
	 * @param integer $intOffset   An optional offset
	 * @param array   $arrOptions  An optional options array
	 *
	 * @return \Model\Collection|null A collection of models or null if there are no product
	 */
	public static function findPublishedByPid($pid, $blnFeatured=null, $intLimit=0, $intOffset=0, array $arrOptions=array())
	{
		if (empty($pid))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid = $pid");

		if ($blnFeatured === true)
		{
			$arrColumns[] = "$t.featured=1";
		}
		elseif ($blnFeatured === false)
		{
			$arrColumns[] = "$t.featured=''";
		}

		// Never return unpublished elements in the back end, so they don't end up in the RSS feed
		if (!BE_USER_LOGGED_IN || TL_MODE == 'BE')
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order']  = "$t.date DESC";
		}

		$arrOptions['limit']  = $intLimit;
		$arrOptions['offset'] = $intOffset;

		return static::findBy($arrColumns, null, $arrOptions);
	}

}
