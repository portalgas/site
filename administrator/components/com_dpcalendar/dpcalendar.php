<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR);

if (! JFactory::getUser()->authorise('core.manage', 'com_dpcalendar'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::import('joomla.application.component.controller');

$path = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'dpcalendar.xml';
if (file_exists($path))
{
	$manifest = simplexml_load_file($path);
	JRequest::setVar('DPCALENDAR_VERSION', $manifest->version);
}
else
{
	JRequest::setVar('DPCALENDAR_VERSION', '');
}

if (version_compare(PHP_VERSION, '5.3.0') < 0)
{
	JError::raiseWarning(0,
			'You have PHP version ' . PHP_VERSION . ' installed. This version is end of life and contains some security wholes!!
					 		Please upgrade your PHP version to at least 5.3.x. DPCalendar can not run on this version.');
	return;
}

$controller = JControllerLegacy::getInstance('DPCalendar');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
