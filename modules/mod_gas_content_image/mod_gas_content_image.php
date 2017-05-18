<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_random_image
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

require_once JPATH_SITE . '/components/com_cake/app/Lib/UtilsModules.php';
$content_id = getJContentId();
// echo '<br />'.$content_id;if($content_id!=0) {

	$link	= $params->get('link');
	
	$folder	= modGasContentImageHelper::getFolder($params);
	$images	= modGasContentImageHelper::getImages($params, $folder, $content_id);

	if (!count($images)) {
		echo JText::_('MOD_GAS_CONTENT_IMAGE_NO_IMAGES');
		return;
	}
	
	$image = modGasContentImageHelper::getContentImage($params, $images);
	$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
	require JModuleHelper::getLayoutPath('mod_gas_content_image', $params->get('layout', 'default'));
}