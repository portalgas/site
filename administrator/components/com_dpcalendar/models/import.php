<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.modellist');

class DPCalendarModelImport extends JModelLegacy
{

	public function import ()
	{
		JPluginHelper::importPlugin('dpcalendar');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_categories' . DS . 'models');
		JModelLegacy::addTablePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_categories' . DS . 'tables');

		JRequest::setVar('extension', 'com_dpcalendar');
		JFactory::getApplication()->input->post->set('extension', 'com_dpcalendar');

		$tmp = JDispatcher::getInstance()->trigger('onCalendarsFetch');
		$calendars = array();
		if (! empty($tmp))
		{
			foreach ($tmp as $tmpCalendars)
			{
				foreach ($tmpCalendars as $calendar)
				{
					$calendars[] = $calendar;
				}
			}
		}

		$calendarsToimport = JRequest::getVar('calendar', array());
		$existingCalendars = JModelLegacy::getInstance('Categories', 'CategoriesModel')->getItems();
		$start = DPCalendarHelper::getDate(JRequest::getCmd('filter_search_start', null));
		$end = DPCalendarHelper::getDate(JRequest::getCmd('filter_search_end', null));

		$msgs = array();
		foreach ($calendars as $cal)
		{
			if (! in_array($cal->id, $calendarsToimport))
			{
				continue;
			}

			$category = null;
			foreach ($existingCalendars as $exCal)
			{
				if ($exCal->title == $cal->title)
				{
					$category = $exCal;
					break;
				}
			}

			if ($category == null)
			{
				$data = array();
				$data['id'] = 0;
				$data['title'] = $cal->title;
				$data['description'] = $cal->description;
				$data['extension'] = 'com_dpcalendar';
				$data['parent_id'] = 1;
				$data['published'] = 1;
				$data['language'] = '*';

				$model = JModelLegacy::getInstance('Category', 'CategoriesModel');
				$model->save($data);
				$category = $model->getItem($model->getState('category.id'));
			}

			$tmp = JDispatcher::getInstance()->trigger('onEventsFetch',
					array(
							$cal->id,
							$start,
							$end,
							new JRegistry(array(
									'expand' => false
							))
					));

			$counter = 0;
			if (! empty($tmp))
			{
				foreach ($tmp as $events)
				{
					foreach ($events as $event)
					{
						$filter = strtolower(JRequest::getVar('filter_search', ''));
						if (! empty($filter) && strpos(
								strtolower($event->title . ' ' . $event->description . ' ' . $event->url . ' ' . $event->location), $filter) === false)
						{
							continue;
						}

						$eventData = (array) $event;

						if (isset($eventData['locations']) && $eventData['locations'])
						{
							foreach ($eventData['locations'] as $loc)
							{
								$model = JModelLegacy::getInstance('Locations', 'DPCalendarModel',
										array(
												'ignore_request' => true
										));
								$model->getState();
								$location = null;
								if ($loc->latitude && $loc->longitude)
								{
									$model->setState('list.limit', 1);
									$model->setState('filter.latitude', $loc->latitude);
									$model->setState('filter.longitude', $loc->longitude);

									$locations = $model->getItems();
									if (! empty($locations))
									{
										$location = reset($locations);
									}
								}

								if (! $location)
								{
									$model->setState('list.limit', 10000);
									$model->setState('filter.latitude', null);
									$model->setState('filter.longitude', null);

									$locations = $model->getItems();

									$locationString = DPCalendarHelperLocation::format($loc);
									foreach ($locations as $l)
									{
										if (DPCalendarHelperLocation::format($l) == $locationString)
										{
											$location = $l;
											break;
										}
									}
									if (! $location)
									{
										$loc->id = 0;
										$table = $this->getTable('Location');
										$table->bind((array) $loc);
										if ($table->store())
										{
											$loc->id = $table->id;
										}
										else
										{
											JError::raiseWarning(0, $table->getError());
										}

										$location = $loc;
									}
								}
								if ($location)
								{
									$eventData['location_ids'] = array(
											$location->id
									);
								}
							}
						}

						unset($eventData['id']);
						unset($eventData['locations']);
						$eventData['alias'] = ! empty($event->alias) ? $event->alias : JApplication::stringURLSafe($event->title);
						$eventData['catid'] = $category->id;

						JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dpcalendar/models', 'DPCalendarModel');
						$model = JModelLegacy::getInstance('Form', 'DPCalendarModel');
						$model->getState();
						if (! $model->save($eventData))
						{
							JError::raiseWarning(0, $model->getError());
						}
						else
						{
							$counter ++;
						}
						$model->detach();
					}
				}
			}
			$msgs[] = sprintf(JText::_('COM_DPCALENDAR_N_ITEMS_CREATED'), $counter, $cal->title);
		}
		$this->set('messages', $msgs);
	}

	public function getTable ($type = 'Location', $prefix = 'DPCalendarTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
}
