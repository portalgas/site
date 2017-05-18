<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_categories
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

abstract class modGasSupplierHelper
{
	public static function getItem($j_content_id, $debug=false)
	{
		$db = JFactory::getDbo();
		$rows = array();
		
		$sql = "SELECT 
					Supplier.name, Supplier.descrizione, 
					Supplier.indirizzo, Supplier.localita, Supplier.cap, Supplier.provincia, 
					Supplier.telefono, Supplier.telefono2, Supplier.fax, Supplier.mail, Supplier.www     
		//$rows = $db->loadObjectList();
		return $rows;
	}
}