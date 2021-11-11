<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_categories
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

abstract class modGasOrganizationChoiceHelper
{
	public static function getList(&$params)
	{
		$db = JFactory::getDbo();
		$rows = [];
		
		$sql = "SELECT 
					Organization.id, Organization.name, Organization.j_seo, 
					Organization.localita, Organization.provincia   
				FROM
					k_organizations Organization
				WHERE
					Organization.stato = 'Y' and Organization.type = 'GAS' 
				ORDER BY Organization.name";
		//echo '<br />'.$sql;
		$db->setQuery($sql);
		if ($db->query())
			$rows = $db->loadObjectList();
		//$rows = $db->loadObjectList();
		//$rows = $db->loadResult();
		//$rows = $db->loadAssoc();
		
		return $rows;
	}
}