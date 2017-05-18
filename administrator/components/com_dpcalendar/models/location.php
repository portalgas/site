<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.modeladmin');

class DPCalendarModelLocation extends JModelAdmin
{

	protected $text_prefix = 'COM_DPCALENDAR_LOCATION';

	protected function canDelete ($record)
	{
		if (! empty($record->id))
		{
			if ($record->state != - 2)
			{
				return;
			}

			return parent::canDelete($record);
		}
	}

	public function getTable ($type = 'Location', $prefix = 'DPCalendarTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm ($data = array(), $loadData = true)
	{
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_dpcalendar.location', 'location', array(
				'control' => 'jform',
				'load_data' => $loadData
		));
		if (empty($form))
		{
			return false;
		}

		// Modify the form based on access controls.
		if (! $this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');

			// Disable fields while saving.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
		}

		return $form;
	}

	protected function loadFormData ()
	{
		$data = JFactory::getApplication()->getUserState('com_dpcalendar.edit.location.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	protected function prepareTable ($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias = JApplication::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = JApplication::stringURLSafe($table->title);
		}

		if (empty($table->latitude) && empty($table->longitude))
		{
			$latLong = DPCalendarHelperLocation::get(DPCalendarHelperLocation::format($table));
			$table->latitude = $latLong->latitude;
			$table->longitude = $latLong->longitude;
		}

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__dpcalendar_locations');
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
			else
			{
				// Set the values
				$table->modified = $date->toSql();
				$table->modified_by = $user->get('id');
			}

			// Increment the content version number.
			$table->version ++;
		}

		if (! isset($table->state) && $this->canEditState($table))
		{
			$table->state = 1;
		}
	}
}
