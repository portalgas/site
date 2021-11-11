<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/*
 * find se in uso il componente com_cake
 */
$componentIsComCake = false;
if(strpos($_SERVER['QUERY_STRING'], '&') == true) {
	$args = explode('&',  $_SERVER['QUERY_STRING']);
	foreach($args as $arg) {
		list($key, $value) = explode('=', $arg);
		
		if(isset($key) && $key == 'option') {
			if(isset($value) && $value == 'com_cake') {
				$componentIsComCake = true;
				break;
			}
		}
		
	}
}

if($componentIsComCake)
	require JModuleHelper::getLayoutPath('mod_menu', 'default_enabled_com_cake');
else
	require JModuleHelper::getLayoutPath('mod_menu', $enabled ? 'default_enabled' : 'default_disabled');

$menu->renderMenu('menu', $enabled ? '' : 'disabled');
