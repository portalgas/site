<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.modelform');
JLoader::import('components.com_dpcalendar.tables.event', JPATH_ADMINISTRATOR);

class DPCalendarModelEvent extends JModelForm
{

	protected $view_item = 'contact';

	protected $_item = null;

	protected $_context = 'com_dpcalendar.event';

	protected function populateState ()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getVar('id');
		$this->setState('event.id', $pk);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = JFactory::getUser();
		if ((! $user->authorise('core.edit.state', 'com_dpcalendar')) && (! $user->authorise('core.edit', 'com_dpcalendar')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_dpcalendar.event', 'event', array(
				'control' => 'jform',
				'load_data' => true
		));
		if (empty($form))
		{
			return false;
		}

		$id = $this->getState('event.id');
		$params = $this->getState('params');
		$event = $this->_item[$id];
		$params->merge($event->params);

		return $form;
	}

	protected function loadFormData ()
	{
		$data = (array) JFactory::getApplication()->getUserState('com_dpcalendar.event.data', array());
		return $data;
	}

	public function &getItem ($pk = null)
	{
		$pk = (! empty($pk)) ? $pk : $this->getState('event.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (! isset($this->_item[$pk]))
		{
			if (! empty($pk) && ! is_numeric($pk))
			{
				$event = null;
				JPluginHelper::importPlugin('dpcalendar');
				$tmp = JDispatcher::getInstance()->trigger('onEventFetch', array(
						$pk
				));
				if (! empty($tmp))
				{
					$this->_item[$pk] = $tmp[0];
				}
				else
				{
					$this->_item[$pk] = false;
				}
			}
			else
			{
				try
				{
					$db = $this->getDbo();
					$query = $db->getQuery(true);
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					// Sqlsrv changes
					$case_when = ' CASE WHEN ';
					$case_when .= $query->charLength('a.alias');
					$case_when .= ' THEN ';
					$a_id = $query->castAsChar('a.id');
					$case_when .= $query->concatenate(array(
							$a_id,
							'a.alias'
					), ':');
					$case_when .= ' ELSE ';
					$case_when .= $a_id . ' END as slug';

					$case_when1 = ' CASE WHEN ';
					$case_when1 .= $query->charLength('c.alias');
					$case_when1 .= ' THEN ';
					$c_id = $query->castAsChar('c.id');
					$case_when1 .= $query->concatenate(array(
							$c_id,
							'c.alias'
					), ':');
					$case_when1 .= ' ELSE ';
					$case_when1 .= $c_id . ' END as catslug';

					$query->select($this->getState('item.select', 'a.*') . ',' . $case_when . ',' . $case_when1);
					$query->from('#__dpcalendar_events AS a');

					// Join on category table.
					$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access');
					$query->join('LEFT', '#__categories AS c on c.id = a.catid');

					// Join over the categories to get parent category titles
					$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias');
					$query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

					$query->select('u.name AS author');
					$query->join('LEFT', '#__users AS u on u.id = a.created_by');

					$query->where('a.id = ' . (int) $pk);

					// Filter by start and end dates.
					$nullDate = $db->Quote($db->getNullDate());
					$nowDate = $db->Quote(JFactory::getDate()->toSql());

					// Filter by published state.
					$published = $this->getState('filter.published');
					$archived = $this->getState('filter.archived');
					if (is_numeric($published))
					{
						$query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
						$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
						$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
					}

					// Implement View Level Access
					if (! $user->authorise('core.admin'))
					{
						$query->where('a.access IN (' . implode(',', $groups) . ')');
					}

					$db->setQuery($query);

					$data = $db->loadObject('DPCalendarTableEvent');

					if ($error = $db->getErrorMsg())
					{
						throw new JException($error);
					}

					if (empty($data))
					{
						throw new JException(JText::_('COM_DPCALENDAR_ERROR_EVENT_NOT_FOUND'), 404);
					}

					// Check for published state if filter set.
					if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived)))
					{
						JError::raiseError(404, JText::_('COM_DPCALENDAR_ERROR_EVENT_NOT_FOUND'));
					}

					if (! DPCalendarHelper::isFree())
					{
						JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models', 'DPCalendarModel');
						$attendeeModel = JModelLegacy::getInstance('Attendees', 'DPCalendarModel');
						$attendeeModel->getState();

						$eventIds = array(
								$data->id
						);
						if ($data->original_id > 0)
						{
							$eventIds[] = $data->original_id;
						}
						$attendeeModel->setState('filter.event_id', $eventIds);
						$attendeeModel->setState('list.limit', 10000);
						$data->attendees = $attendeeModel->getItems();
					}

					$locationQuery = $db->getQuery(true);
					$locationQuery->select('a.*');
					$locationQuery->from('#__dpcalendar_locations AS a');

					$locationQuery->join('RIGHT',
							'#__dpcalendar_events_location AS rel on rel.event_id = ' . (int) $pk . ' and rel.location_id = a.id');
					$locationQuery->where('state = 1');
					$db->setQuery($locationQuery);
					$data->locations = $db->loadObjectList();

					// Implement View Level Access
					if (! $user->authorise('core.admin') && ! in_array($data->access_content, $user->getAuthorisedViewLevels()))
					{
						$data->title = JText::_('COM_DPCALENDAR_EVENT_BUSY');
						$data->location = '';
						$data->locations = null;
						$data->url = '';
						$data->description = '';
					}

					// Convert parameter fields to objects.
					$registry = new JRegistry();
					$registry->loadString($data->params);
					$data->params = clone $this->getState('params');
					$data->params->merge($registry);

					$registry = new JRegistry();
					$registry->loadString($data->metadata);
					$data->metadata = $registry;

					// Technically guest could edit an article, but lets not
					// check that to improve performance a little.
					if (! $user->get('guest'))
					{
						$userId = $user->get('id');
						$asset = 'com_dpcalendar.event.' . $data->id;

						// Check general edit permission first.
						if ($user->authorise('core.edit', $asset))
						{
							$data->params->set('access-edit', true);
						}
						if ($user->authorise('core.delete', $asset))
						{
							$data->params->set('access-delete', true);
						}
						else if (! empty($userId) && $user->authorise('core.edit.own', $asset))
						{
							// Check for a valid user and that they are the
							// owner.
							if ($userId == $data->created_by)
							{
								$data->params->set('access-edit', true);
							}
						}
					}

					// Compute access permissions.
					if ($access = $this->getState('filter.access'))
					{
						// If the access filter has been set, we already know
						// this user can view.
						$data->params->set('access-view', true);
					}
					else
					{
						// If no access filter is set, the layout takes some
						// responsibility for display of limited information.

						if ($data->catid == 0 || $data->category_access === null)
						{
							$data->params->set('access-view', in_array($data->access, $groups));
						}
						else
						{
							$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
						}
					}

					$this->_item[$pk] = $data;
				}
				catch (JException $e)
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	public function hit ($id = null)
	{
		if (empty($id))
		{
			$id = $this->getState('event.id');
		}

		if (! is_numeric($id))
		{
			return 0;
		}

		$event = $this->getTable('Event', 'DPCalendarTable');
		return $event->hit($id);
	}
}
