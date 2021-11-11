<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.controlleradmin');

class DPCalendarControllerLocations extends JControllerAdmin
{

	protected $text_prefix = 'COM_DPCALENDAR_LOCATION';

	public function getModel ($name = 'Location', $prefix = 'DPCalendarModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function saveOrderAjax ()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	public function publish ()
	{
		$return = parent::publish();

		if (JRequest::getVar('ajax') != 0)
		{
			$text = JText::plural($this->text_prefix . '_N_ITEMS_TRASHED', count(JFactory::getApplication()->input->get('cid', array(), 'array')));
			if ($this->message == $text)
			{
				DPCalendarHelper::sendMessage($this->message, false);
			}
			else
			{
				DPCalendarHelper::sendMessage($this->message, true);
			}
		}
		return $return;
	}
}
