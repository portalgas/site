<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_categories
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the helper functions only once
require_once dirname(__FILE__).'/helper.php';

$list = modGasArticlesCategoriesHelper::getList($params);
if (!empty($list)) {
	
	$app = JFactory::getApplication();	$params = $app->getTemplate(true)->params;	$organization_id = $params->get('organizationId');  // ottengo organization_id come parametro del template	
	$listOrganization = array();
	if($organization_id > 0)
		$listOrganization = modGasArticlesCategoriesHelper::getCategoryOrganization($organization_id);
	
	$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
	$startLevel = reset($list)->getParent()->level;
	require JModuleHelper::getLayoutPath('mod_gas_articles_categories', $params->get('layout', 'default'));
}