<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('controllers.location', JPATH_COMPONENT_ADMINISTRATOR);

class DPCalendarControllerLocationForm extends DPCalendarControllerLocation
{

	public function __construct ($config = array())
	{
		JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar');

		JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'models');
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables');

		parent::__construct();
	}

	public function getModel ($name = 'Location', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
