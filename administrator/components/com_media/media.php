<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Access check.

$user = JFactory::getUser();
$asset = JRequest::getCmd('asset');
$author = JRequest::getCmd('author');

if (	!$user->authorise('core.manage', 'com_media')
	&&	(!$asset or (
			!$user->authorise('core.edit', $asset)
		&&	!$user->authorise('core.create', $asset)
		&& 	count($user->getAuthorisedCategories($asset, 'core.create')) == 0)
		&&	!($user->id==$author && $user->authorise('core.edit.own', $asset))))
{
	return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
}

$params = JComponentHelper::getParams('com_media');

// Load the admin HTML view
require_once JPATH_COMPONENT.'/helpers/media.php';

// Set the path definitions
$popup_upload = JRequest::getCmd('pop_up', null);
$path = "file_path";

$view = JRequest::getCmd('view');
if (substr(strtolower($view), 0, 6) == "images" || $popup_upload == 1) {
	$path = "image_path";
}

/*
 *  fractis ridefinisco il path in base all'amministratore
 */
$acl_path = '';
$userJoomla = JFactory::getUser();
$organization_id = $userJoomla->organization_id;
/*
 * non ho scelto l'organization
 */
if(!empty($organization_id)) {
	// $acl_path ='/organizations/'.$userJoomla->organization['Organization']['j_seo']; 
	$acl_path ='/organizations/'.$userJoomla->organization['Organization']['id']; 
}

//echo JPATH_ROOT.'/'.$params->get($path, 'images').$acl_path;
/*
 *  fractis ridefinisco il path in base all'amministratore
 */

define('COM_MEDIA_BASE',	JPATH_ROOT.'/'.$params->get($path, 'images').$acl_path);
define('COM_MEDIA_BASEURL', $image_path = JURI::root().$params->get($path, 'images').$acl_path);



// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Media');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();