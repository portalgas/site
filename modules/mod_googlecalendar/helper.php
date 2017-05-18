<?php
/**
 * @copyright	Copyright (C) 2011 Jan Maat. All rights reserved.
 * @license		GNU/GPL, see http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class modGoogleCalendarHelper
{
	public static function getCode($params)
	{
		$mainframe = JFactory::getApplication();
		$code = $params->get('code');	
		return $code;
	}
}
