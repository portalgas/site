<?php
/**
* @copyright	Copyright (C) 2012 Jan Maat. All rights reserved.
* @license		GNU/GPL, see http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$code = modGoogleCalendarHelper::getCode($params);
require(JModuleHelper::getLayoutPath('mod_googlecalendar'));