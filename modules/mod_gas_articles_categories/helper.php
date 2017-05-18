<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_categories
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.'/components/com_content/helpers/route.php';
jimport('joomla.application.categories');

abstract class modGasArticlesCategoriesHelper
{
	public static function getList(&$params)
	{
		$categories = JCategories::getInstance('Content');
		$category = $categories->get($params->get('parent', 'root'));

		if ($category != null)
		{
			$items = $category->getChildren();
			if($params->get('count', 0) > 0 && count($items) > $params->get('count', 0))
			{
				$items = array_slice($items, 0, $params->get('count', 0));
			}				
			return $items;
		}
	}

	/*
	 * filtra per le categorie di un organization
	 */
	public static function getCategoryOrganization($organization_id) {
		
		$db = JFactory::getDbo();
		
		$sql = "SELECT JCategory.id				FROM					j_categories JCategory,					k_categories_suppliers CategoriesSupplier,					k_suppliers_organizations SuppliersOrganization				WHERE					SuppliersOrganization.organization_id = ".$organization_id."					AND SuppliersOrganization.category_supplier_id = CategoriesSupplier.id					AND CategoriesSupplier.j_category_id = JCategory.id					AND CategoriesSupplier.j_category_id > 0				GROUP BY JCategory.id				ORDER BY JCategory.id";		//echo '<br />'.$sql;		$db->setQuery($sql);		if ($db->query())			$rows = $db->loadObjectList();
		//$rows = $db->loadObjectList();		//$rows = $db->loadResult();		//$rows = $db->loadAssoc();			
		return $rows;
	}
}