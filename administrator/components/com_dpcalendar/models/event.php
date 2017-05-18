<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.modeladmin');

class DPCalendarModelEvent extends JModelAdmin
{

	protected $text_prefix = 'COM_DPCALENDAR';

	private $eventHandler = null;

	public function __construct ($config = array())
	{
		parent::__construct($config);
		$dispatcher = JDispatcher::getInstance();
		$this->eventHandler = new EventHandler($dispatcher, $this);
	}

	protected function canDelete ($record)
	{
		if (! empty($record->id))
		{
			if ($record->state != - 2)
			{
				return false;
			}
			$user = JFactory::getUser();
			$calendar = DPCalendarHelper::getCalendar($record->catid);

			if ($calendar->canDelete || ($calendar->canEditOwn && $record->created_by == JFactory::getUser()->id))
			{
				return true;
			}
			else
			{
				return parent::canDelete($record);
			}
		}
	}

	protected function canEditState ($record)
	{
		$user = JFactory::getUser();

		if (! empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_dpcalendar.category.' . (int) $record->catid);
		}
		else
		{
			return parent::canEditState('com_dpcalendar');
		}
	}

	public function getTable ($type = 'Event', $prefix = 'DPCalendarTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm ($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_dpcalendar.event', 'event', array(
				'control' => 'jform',
				'load_data' => $loadData
		));
		if (empty($form))
		{
			return false;
		}
		$eventId = $this->getState('event.id', 0);

		// Determine correct permissions to check.
		if ($eventId)
		{
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		$item = $this->getItem();

		// Modify the form based on access controls.
		if (! $this->canEditState($item))
		{
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');

			// Disable fields while saving
			$form->setFieldAttribute('state', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
		}

		$form->setFieldAttribute('start_date', 'all_day', $item->all_day);
		$form->setFieldAttribute('end_date', 'all_day', $item->all_day);

		if (DPCalendarHelper::isFree())
		{
			// Disable fields for display.
			$form->setFieldAttribute('rrule', 'disabled', 'true');
			$form->setFieldAttribute('capacity', 'disabled', 'true');
			$form->setFieldAttribute('capacity_used', 'disabled', 'true');
			$form->setFieldAttribute('price', 'disabled', 'true');
			$form->setFieldAttribute('ordertext', 'disabled', 'true');
			$form->setFieldAttribute('canceltext', 'disabled', 'true');
			$form->setFieldAttribute('plugintype', 'disabled', 'true');

			// Disable fields while saving.
			$form->setFieldAttribute('rrule', 'filter', 'unset');
			$form->setFieldAttribute('capacity', 'filter', 'unset');
			$form->setFieldAttribute('capacity_used', 'filter', 'unset');
			$form->setFieldAttribute('price', 'filter', 'unset');
			$form->setFieldAttribute('ordertext', 'filter', 'unset');
			$form->setFieldAttribute('canceltext', 'filter', 'unset');
			$form->setFieldAttribute('plugintype', 'filter', 'unset');
		}

		return $form;
	}

	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_dpcalendar.edit.event.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
			$eventId = $this->getState('event.id');

			if (! DPCalendarHelper::isFree())
			{
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
				$attendData = JModelLegacy::getInstance('Attendee', 'DPCalendarModel')->findItem($eventId);
				$attendData->check();
				$this->setState('attend_id', $attendData->id);

				$data->set('name', $attendData->name);
				$data->set('email', $attendData->email);
				$data->set('telephone', $attendData->telephone);
				$data->set('remind_time', $attendData->remind_time);
				$data->set('remind_type', $attendData->remind_type);
				$data->set('user_id', $attendData->user_id);
				$data->set('attend_date', $attendData->attend_date);
				$data->set('event_id', $eventId);
			}
			// Prime some default values.
			if ($eventId == 0)
			{
				$app = JFactory::getApplication();
				$data->set('catid', JRequest::getCmd('catid', $app->getUserState('com_dpcalendar.events.filter.category_id')));
			}
		}

		if (is_array($data))
		{
			$data = new JObject($data);
		}

		if (! $data->get('id'))
		{
			$data->set('capacity', 0);
		}

		return $data;
	}

	protected function preprocessForm (JForm $form, $data, $group = 'content')
	{
		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models/forms');
		$form->loadFile('attendee', false);
		$form->setFieldAttribute('event_id', 'required', false);
		$form->setFieldAttribute('email', 'required', false);

		return parent::preprocessForm($form, $data, $group);
	}

	public function getItem ($pk = null)
	{
		$pk = (! empty($pk)) ? $pk : $this->getState($this->getName() . '.id');
		$item = null;
		if (! empty($pk) && ! is_numeric($pk))
		{
			JPluginHelper::importPlugin('dpcalendar');
			$tmp = JDispatcher::getInstance()->trigger('onEventFetch', array(
					$pk
			));
			if (! empty($tmp))
			{
				$item = $tmp[0];
			}
		}
		else
		{
			$item = parent::getItem($pk);
			if ($item != null)
			{
				// Convert the params field to an array.
				$registry = new JRegistry();
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();

				if ($item->id > 0)
				{
					$this->_db->setQuery('select location_id from #__dpcalendar_events_location where event_id = ' . (int) $item->id);
					$locations = $this->_db->loadObjectList();
					if (! empty($locations))
					{
						$item->location_ids = array();
						foreach ($locations as $location)
						{
							$item->location_ids[] = $location->location_id;
						}
					}
				}
			}
		}

		return $item;
	}

	public function save ($data)
	{
		$locationIds = array();
		if (isset($data['location_ids']))
		{
			$locationIds = $data['location_ids'];
			unset($data['location_ids']);
		}

		$deleteCondition = '';
		if (isset($data['id']))
		{
			$this->getDbo()->setQuery('select id from #__dpcalendar_events where original_id = ' . (int) $data['id']);
			$rows = $this->getDbo()->loadObjectList();
			foreach ($rows as $oldEvent)
			{
				$deleteCondition .= $oldEvent->id . ',';
			}
		}
		$this->setState('dpcalendar.event.deletecondition', $deleteCondition);
		$this->setState('dpcalendar.event.locationids', $locationIds);
		$this->setState('dpcalendar.event.data', $data);

		if ($data['all_day'] == 1 && ! isset($data['date_range_correct']))
		{
			$data['start_date'] = DPCalendarHelper::getDate($data['start_date'])->toSql(true);
			$data['end_date'] = DPCalendarHelper::getDate($data['end_date'])->toSql(true);
		}

		// Alter the title for save as copy
		if (JRequest::getVar('task') == 'save2copy')
		{
			list ($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
		}

		return parent::save($data);
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

		if (! isset($table->state) && $this->canEditState($table))
		{
			$table->state = 1;
		}
	}

	public function batch ($commands, $pks, $contexts)
	{
		$result = parent::batch($commands, $pks, $contexts);

		if (! empty($commands['color_id']))
		{
			$user = JFactory::getUser();
			$table = $this->getTable();
			foreach ($pks as $pk)
			{
				if ($user->authorise('core.edit', $contexts[$pk]))
				{
					$table->reset();
					$table->load($pk);
					$table->color = $commands['color_id'];

					if (! $table->store())
					{
						$this->setError($table->getError());
						return false;
					}
				}
				else
				{
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
					return false;
				}
			}

			$this->cleanCache();
			return true;
		}
		return $result;
	}

	public function featured ($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			$this->setError(JText::_('COM_DPCALENDAR_NO_ITEM_SELECTED'));
			return false;
		}

		try
		{
			$db = $this->getDbo();

			$db->setQuery('UPDATE #__dpcalendar_events' . ' SET featured = ' . (int) $value . ' WHERE id IN (' . implode(',', $pks) . ')');
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		return true;
	}

	public function detach ()
	{
		JDispatcher::getInstance()->detach($this->eventHandler);
	}
}

class EventHandler extends JEvent
{

	private $model = null;

	public function __construct (&$subject, $model)
	{
		parent::__construct($subject);

		$this->model = $model;
	}

	public function onContentBeforeSave ($context, $event, $isNew)
	{
		if ($context != 'com_dpcalendar.event' && $context != 'com_dpcalendar.form')
		{
			return;
		}

		JPluginHelper::importPlugin('dpcalendar');
		if ($isNew)
		{
			return JDispatcher::getInstance()->trigger('onEventBeforeCreate', array(
					&$event
			));
		}
		else
		{
			return JDispatcher::getInstance()->trigger('onEventBeforeSave', array(
					&$event
			));
		}
	}

	public function onContentAfterSave ($context, $event, $isNew)
	{
		if ($context != 'com_dpcalendar.event' && $context != 'com_dpcalendar.form')
		{
			return;
		}

		$id = (int) $event->id;

		if (JFactory::getApplication()->input->get('attend', 0) == 1 && DPCalendarHelper::openForAttending($event))
		{
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
			$model = JModelLegacy::getInstance('Attendee', 'DPCalendarModel');
			$data = $this->model->getState('dpcalendar.event.data');
			$data['event_id'] = $id;
			unset($data['id']);
			$model->save($data);
		}

		JFactory::getApplication()->setUserState('dpcalendar.event.id', $id);

		$deleteCondition = $this->model->getState('dpcalendar.event.deletecondition');
		$locationIds = $this->model->getState('dpcalendar.event.locationids');

		$db = JFactory::getDbo();
		$db->setQuery('select id from #__dpcalendar_events where id = ' . $id . ' or original_id = ' . $id);
		$rows = $db->loadObjectList();
		$values = '';
		foreach ($rows as $tmp)
		{
			$deleteCondition .= (int) $tmp->id . ',';

			foreach ($locationIds as $location)
			{
				$values .= '(' . (int) $tmp->id . ',' . (int) $location . '),';
			}
		}
		$values = trim($values, ',');
		$deleteCondition = trim($deleteCondition, ',');

		if (! $isNew)
		{
			$db->setQuery('delete from #__dpcalendar_events_location where event_id in (' . $deleteCondition . ')');
			$db->query();
		}
		if (! empty($values))
		{
			$db->setQuery('insert into #__dpcalendar_events_location (event_id, location_id) values ' . $values);
			$db->query();
		}

		$this->sendMail($isNew ? 'create' : 'edit', array(
				$event
		));

		JPluginHelper::importPlugin('dpcalendar');
		if ($isNew)
		{
			return JDispatcher::getInstance()->trigger('onEventAfterCreate', array(
					&$event
			));
		}
		else
		{
			return JDispatcher::getInstance()->trigger('onEventAfterSave', array(
					&$event
			));
		}
	}

	public function onContentBeforeDelete ($context, $event)
	{
		if ($context != 'com_dpcalendar.event' && $context != 'com_dpcalendar.form')
		{
			return;
		}

		JPluginHelper::importPlugin('dpcalendar');
		return JDispatcher::getInstance()->trigger('onEventBeforeDelete', array(
				$event
		));
	}

	public function onContentAfterDelete ($context, $event)
	{
		if ($context != 'com_dpcalendar.event' && $context != 'com_dpcalendar.form')
		{
			return;
		}

		$this->sendMail('delete', array(
				$event
		));

		JPluginHelper::importPlugin('dpcalendar');
		return JDispatcher::getInstance()->trigger('onEventAfterDelete', array(
				$event
		));
	}

	public function onContentChangeState ($context, $pks, $value)
	{
		if ($context != 'com_dpcalendar.event' && $context != 'com_dpcalendar.form')
		{
			return;
		}

		$events = array();

		$model = new DPCalendarModelEvent();
		foreach ($pks as $pk)
		{
			$event = $model->getItem($pk);
			$events[] = $event;

			JDispatcher::getInstance()->trigger('onEventAfterSave', array(
					$event
			));
		}

		$this->sendMail('edit', $events);
	}

	private function sendMail ($action, $events)
	{
		JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar');

		$subject = DPCalendarHelper::renderEvents($events, JText::_('COM_DPCALENDAR_NOTIFICATION_EVENT_SUBJECT'));

		$body = DPCalendarHelper::renderEvents($events, JText::_('COM_DPCALENDAR_NOTIFICATION_EVENT_' . strtoupper($action) . '_BODY'), null,
				array(
						'sitename' => JFactory::getConfig()->get('sitename'),
						'user' => JFactory::getUser()->name
				));

		DPCalendarHelper::sendMail($subject, $body, 'notification_groups_' . $action);
	}
}
