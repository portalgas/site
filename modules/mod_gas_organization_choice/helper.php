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
		$rows = array();
		
		$sql = "SELECT 
					Organization.id, Organization.name, Organization.j_seo, 
					Organization.localita, Organization.provincia   
		//$rows = $db->loadObjectList();
		return $rows;
	}
}