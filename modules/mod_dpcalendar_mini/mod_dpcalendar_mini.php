<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR);
if (! class_exists('DPCalendarHelper'))
{
	return;
}

JLoader::import('components.com_dpcalendar.libraries.fullcalendar.fullcalendar', JPATH_SITE);

require JModuleHelper::getLayoutPath('mod_dpcalendar_mini', $params->get('layout', 'default'));
