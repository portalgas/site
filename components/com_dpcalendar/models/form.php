<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.models.event', JPATH_ADMINISTRATOR);
JLoader::import('components.com_dpcalendar.tables.event', JPATH_ADMINISTRATOR);

class DPCalendarModelForm extends DPCalendarModelEvent
{

	public function getReturnPage ()
	{
		return base64_encode($this->getState('return_page'));
	}

	protected function populateState ()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = JRequest::getVar('e_id');
		$this->setState('event.id', $pk);

		// Add compatibility variable for default naming conventions.
		$this->setState('form.id', $pk);

		$categoryId = JRequest::getVar('catid');
		$this->setState('event.catid', $categoryId);

		$return = JRequest::getVar('return', null, 'default', 'base64');

		if (! JUri::isInternal(base64_decode($return)))
		{
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		if ($app->isSite())
		{
			$this->setState('params', $app->getParams());
		}
		else
		{
			$this->setState('params', JComponentHelper::getParams('com_dpcalendar'));
		}

		$this->setState('layout', JRequest::getCmd('layout'));
	}

	protected function preprocessForm (JForm $form, $data, $group = 'content')
	{
		$return = parent::preprocessForm($form, $data, $group);
		$form->setFieldAttribute('user_id', 'type', 'hidden');
		return $return;
	}
}
