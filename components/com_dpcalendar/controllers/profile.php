<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.controller');

class DPCalendarControllerProfile extends JControllerLegacy
{

	public function change ()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$input = JFactory::getApplication()->input;
		$deselected = $input->getString('deselected');
		if (! empty($deselected))
		{
			$this->getModel()->change('remove', $deselected, $input->getString('action'));
		}
		else
		{
			$this->getModel()->change('add', $input->getString('selected'), $input->getString('action'));
		}
		JFactory::getApplication()->close();
	}

	public function getModel ($name = 'profile', $prefix = 'DPCalendarModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
