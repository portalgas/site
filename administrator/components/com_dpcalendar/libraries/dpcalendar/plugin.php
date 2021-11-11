<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.plugin.plugin');

JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR);
JLoader::import('components.com_dpcalendar.helpers.ical', JPATH_ADMINISTRATOR);

JLoader::import('components.com_dpcalendar.tables.event', JPATH_ADMINISTRATOR);
JLoader::import('components.com_dpcalendar.tables.location', JPATH_ADMINISTRATOR);

JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'tables');

/**
 * This is the base class for the DPCalendar plugins.
 */
abstract class DPCalendarPlugin extends JPlugin
{

	protected $identifier = null;

	public function __construct (&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function fetchEvent ($eventId, $calendarId)
	{
		$eventId = urldecode($eventId);
		$pos = strrpos($eventId, '_');
		if ($pos === false)
		{
			return null;
		}
		$s = substr($eventId, $pos + 1);
		if ($s == 0)
		{
			$uid = substr($eventId, 0, $pos);
			JLoader::import('components.com_dpcalendar.libraries.caldav.vendor.autoload', JPATH_ADMINISTRATOR);

			$content = $this->getContent($calendarId, DPCalendarHelper::getDate('2000-01-01'), null, new JRegistry());
			if (is_array($content))
			{
				$content = implode(PHP_EOL, $content);
			}

			$cal = Sabre\VObject\Reader::read($content);

			foreach ($cal->VEVENT as $event)
			{
				if ((string) $event->UID != $uid)
				{
					continue;
				}
				return $this->createEventFromIcal($event, $calendarId, array(
						(string) $event->UID => $event
				));
			}
		}
		$start = null;
		if (strlen($s) == 8)
		{
			$start = JFactory::getDate(substr($s, 0, 4) . '-' . substr($s, 4, 2) . '-' . substr($s, 6, 2) . ' 00:00');
		}
		else
		{
			$start = JFactory::getDate(
					substr($s, 0, 4) . '-' . substr($s, 4, 2) . '-' . substr($s, 6, 2) . ' ' . substr($s, 8, 2) . ':' . substr($s, 10, 2));
		}

		$end = clone $start;
		$end->modify('+1 day');

		$tmpEvent = $this->createEvent($eventId, $calendarId);
		foreach ($this->fetchEvents($calendarId, $start, $end, new JRegistry()) as $event)
		{
			if ($event->id == $tmpEvent->id)
			{
				return $event;
			}
		}
		return null;
	}

	/**
	 * The options can have the following parameters:
	 * - filter: Select only events which match the filter
	 * - limit: The amount of events which should be returned
	 * - expand: If recurring events should be expanded
	 * - location: The event must be around this location based on the givn
	 * radius
	 * - radius: Comes into action when a location is set. Defines how close the
	 * events need to be.
	 * - length_type: The length type in kilometers or miles
	 *
	 * @param string $content
	 * @param JDate $startDate
	 * @param JDate $endDate
	 * @param JRegistry $options
	 * @return array
	 */
	public function fetchEvents ($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$s = $startDate;
		if ($s)
		{
			$s = clone $startDate;
		}
		$e = $endDate;
		if ($e)
		{
			$e = clone $endDate;
		}
		$content = $this->getContent($calendarId, $s, $e, $options);
		if (empty($content))
		{
			return array();
		}
		if (is_array($content))
		{
			$content = implode(PHP_EOL, $content);
		}

		if (empty($options))
		{
			$options = new JRegistry();
		}

		JLoader::import('components.com_dpcalendar.libraries.caldav.vendor.autoload', JPATH_ADMINISTRATOR);
		$cal = null;

		try
		{
			$cal = Sabre\VObject\Reader::read($content);
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
			return array();
		}

		if ($startDate == null)
		{
			$startDate = DPCalendarHelper::getDate();
		}
		if ($endDate == null)
		{
			$endDate = DPCalendarHelper::getDate();
			$endDate->modify('+5 year');
		}
		$data = $cal->VEVENT;
		if (empty($data))
		{
			return array();
		}

		$originals = array();
		foreach ($cal->VEVENT as $tmp)
		{
			$originals[] = clone $tmp;
		}

		try
		{
			if ($options->get('expand', true))
			{
				$cal->expand($startDate, $endDate);
			}
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
			return array();
		}

		$data = $cal->VEVENT;
		if (empty($data))
		{
			return array();
		}

		$tmp = array();
		foreach ($data as $event)
		{
			$tmp[] = $event;
		}
		$data = $tmp;

		$events = array();
		$filter = strtolower($options->get('filter', null));
		$limit = $options->get('limit', null);
		$order = strtolower($options->get('order', 'asc'));

		// Location filtering support
		$location = $options->get('location');
		$locationFilterData = new stdClass();
		$radius = $options->get('radius');
		if ($options->get('length_type') == 'm')
		{
			$radius = $radius * 0.62137119;
		}
		if (! empty($location))
		{
			$locationFilterData->latitude = null;
			$locationFilterData->longitude = null;
			if (strpos($location, 'latitude=') !== false && strpos($location, 'longitude=') !== false)
			{
				list ($latitude, $longitude) = explode(';', $location);
				$locationFilterData->latitude = str_replace('latitude=', '', $latitude);
				$locationFilterData->longitude = str_replace('longitude=', '', $longitude);
			}
			else
			{
				$locationFilterData = DPCalendarHelperLocation::get($location);
			}
		}

		$dbCal = $this->getDbCal($calendarId);
		foreach ($data as $event)
		{
			if (! empty($filter))
			{
				$posSummary = strpos(strtolower($event->SUMMARY), $filter);
				$posDescription = strpos(strtolower($event->DESCRIPTION), $filter);
				$posLocation = strpos(strtolower($event->LOCATION), $filter);
				if ($posSummary === false && $posDescription === false && $posLocation === false)
				{
					continue;
				}
			}

			$tmpEvent = $this->createEventFromIcal($event, $calendarId, $originals);
			$tmpEvent->access_content = $dbCal->access_content;

			if (! empty($location))
			{
				$within = false;
				foreach ($tmpEvent->locations as $loc)
				{
					if (! DPCalendarHelperLocation::within($loc, $locationFilterData->latitude, $locationFilterData->longitude, $radius))
					{
						continue;
					}
					$within = true;
					break;
				}
				if (! $within)
				{
					continue;
				}
			}
			$events[] = $tmpEvent;
		}

		usort($events,
				function  ($event1, $event2) use( $order)
				{
					$first = $event1;
					$second = $event2;
					if (strtolower($order) == 'desc')
					{
						$first = $event2;
						$second = $event1;
					}

					return strcmp($first->start_date, $second->start_date);
				});

		if (! empty($limit) && count($events) >= $limit)
		{
			$events = array_slice($events, 0, $limit);
		}

		return $events;
	}

	protected function fetchCalendars ($calendarIds = null)
	{
		JLoader::import('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'models', 'DPCalendarModel');

		$model = JModelLegacy::getInstance('Extcalendars', 'DPCalendarModel', array(
				'ignore_request' => true
		));
		$model->getState();
		$model->setState('filter.plugin', str_replace('dpcalendar_', '', $this->_name));
		$model->setState('filter.state', 1);
		$model->setState('list.limit', - 1);
		$model->setState('list.ordering', 'a.ordering');

		$user = JFactory::getUser();
		$calendars = array();
		foreach ($model->getItems() as $calendar)
		{
			if (! empty($calendarIds) && ! in_array($calendar->id, $calendarIds))
			{
				continue;
			}

			$cal = $this->createCalendar($calendar->id, $calendar->title, $calendar->description, $calendar->color);
			$cal->params = $calendar->params;
			$cal->access_content = $calendar->access_content;

			$action = $calendar->params->get('action-create', 'false');
			$cal->canCreate = $user->authorise('core.create', 'com_dpcalendar.extcalendar.' . $calendar->id) &&
					 ($action == 'true' || $action === true || $action == 1);
			$action = $calendar->params->get('action-edit', 'false');
			$cal->canEdit = $user->authorise('core.edit', 'com_dpcalendar.extcalendar.' . $calendar->id) &&
					 ($action == 'true' || $action === true || $action == 1);
			$action = $calendar->params->get('action-delete', 'false');
			$cal->canDelete = $user->authorise('core.delete', 'com_dpcalendar.extcalendar.' . $calendar->id) &&
					 ($action == 'true' || $action === true || $action == 1);
			$calendars[] = $cal;
		}
		return $calendars;
	}

	protected function getContent ($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar))
		{
			return '';
		}
		$content = DPCalendarHelper::fetchContent(str_replace('webcal://', 'https://', $calendar->params->get('uri')));

		if ($content instanceof Exception)
		{
			$this->log($content->getMessage());
			return '';
		}

		$content = str_replace("BEGIN:VCALENDAR\r\n", '', $content);
		$content = str_replace("BEGIN:VCALENDAR\n", '', $content);
		$content = str_replace("\r\nEND:VCALENDAR", '', $content);
		$content = str_replace("\nEND:VCALENDAR", '', $content);

		return "BEGIN:VCALENDAR\n" . $content . "\nEND:VCALENDAR";
	}

	/**
	 * Dummy placeholder for plugins which do not support event editing.
	 *
	 * @param string $eventId
	 * @param string $calendarId
	 * @param array $data
	 *
	 * @return string false
	 */
	public function saveEvent ($eventId = null, $calendarId, array $data)
	{
		return false;
	}

	/**
	 * Dummy placeholder for plugins which do not support event deleteing.
	 *
	 * @param string $eventId
	 * @param string $calendarId
	 *
	 * @return boolean
	 */
	public function deleteEvent ($eventId = null, $calendarId)
	{
		return false;
	}

	/**
	 * Dummy placeholder for plugins which do not support event editing.
	 *
	 * @param string $eventId
	 * @param string $calendarId
	 */
	public function prepareForm ($eventId, $calendarId, $form, $data)
	{
	}

	public function onEventFetch ($eventId)
	{
		if (strpos($eventId, $this->identifier) !== 0)
		{
			return;
		}

		$params = $this->params;

		// Sometimes it changes the id
		$eventId = str_replace($this->identifier . ':', $this->identifier . '-', $eventId);
		$id = explode('-', str_replace($this->identifier . '-', '', $eventId), 2);
		if (count($id) < 2)
		{
			return;
		}

		$cache = JFactory::getCache('plg_' . $this->_name);
		$cache->setCaching($params->get('cache', 1) == '1');
		if ($params->get('cache', 1) == 2)
		{
			$conf = JFactory::getConfig();
			$cache->setCaching($conf->get('config.caching'));
		}
		$cache->setLifeTime($params->get('cache_time', 900));

		$event = $cache->call(array(
				$this,
				'fetchEvent'
		), $id[1], $id[0]);
		$cache->gc();
		return $event;
	}

	public function onEventsFetch ($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options = null)
	{
		if (strpos($calendarId, $this->identifier) !== 0)
		{
			return;
		}

		$params = $this->params;

		$id = str_replace($this->identifier . '-', '', $calendarId);

		$cache = JFactory::getCache('plg_' . $this->_name);
		$cache->setCaching($params->get('cache', 1) == '1');
		if ($params->get('cache', 1) == 2)
		{
			$conf = JFactory::getConfig();
			$cache->setCaching($conf->get('config.caching'));
		}
		$cache->setLifeTime($params->get('cache_time', 900));

		if ($options == null)
		{
			$options = new JRegistry();
		}

		if ($startDate)
		{
			// If now we cache at least for the minute
			$startDate->setTime($startDate->format('H', true), $startDate->format('i'));
		}
		$events = $cache->call(array(
				$this,
				'fetchEvents'
		), $id, $startDate, $endDate, $options);
		$cache->gc();
		return $events;
	}

	public function onCalendarsFetch ($calendarIds = null, $type = null)
	{
		if (! empty($type) && $this->identifier != $type)
		{
			return;
		}

		$ids = array();
		if (! empty($calendarIds))
		{
			if (! is_array($calendarIds))
			{
				$calendarIds = array(
						$calendarIds
				);
			}
			foreach ($calendarIds as $calendarId)
			{
				if (strpos($calendarId, $this->identifier) === 0)
				{
					$ids[] = (int) str_replace($this->identifier . '-', '', $calendarId);
				}
			}
			if (empty($ids))
			{
				return;
			}
		}

		return $this->fetchCalendars($ids);
	}

	public function onEventBeforeDisplay (&$event, &$output)
	{
	}

	public function onEventAfterDisplay (&$event, &$output)
	{
	}

	public function onEventBeforeCreate (&$event)
	{
	}

	public function onEventAfterCreate (&$event)
	{
	}

	public function onEventBeforeSave (&$event)
	{
	}

	/**
	 * This function is called when an external event is going
	 * to be saved.
	 * This function is dependant when a calendar has canEdit or
	 * canCreate set to true.
	 *
	 * @param array $data
	 * @return boolean
	 */
	public function onEventSave (array $data)
	{
		if (strpos($data['catid'], $this->identifier) !== 0)
		{
			return false;
		}

		$calendarId = str_replace($this->identifier . '-', '', $data['catid']);

		$newEventId = false;
		if (! isset($data['id']) || empty($data['id']))
		{
			$newEventId = $this->saveEvent(null, $calendarId, $data);
		}
		else
		{
			$eventId = $data['id'];
			$eventId = str_replace($this->identifier . ':', $this->identifier . '-', $eventId);
			$id = explode('-', str_replace($this->identifier . '-', '', $eventId), 2);
			if (count($id) < 2)
			{
				return false;
			}

			$newEventId = $this->saveEvent($id[1], $id[0], $data);
		}
		if ($newEventId != false)
		{
			$cache = JFactory::getCache('plg_' . $this->_name);
			$cache->clean();
		}
		return $newEventId;
	}

	public function onEventAfterSave (&$event)
	{
	}

	public function onEventBeforeDelete ($event)
	{
	}

	/**
	 * This function is called when an external event is going
	 * to be deleted.
	 * This function is dependant when a calendar has canDelete
	 * set to true.
	 *
	 * @param string $eventId
	 * @return boolean
	 */
	public function onEventDelete ($eventId)
	{
		if (strpos($eventId, $this->identifier) !== 0)
		{
			return false;
		}

		$eventId = str_replace($this->identifier . ':', $this->identifier . '-', $eventId);
		$id = explode('-', str_replace($this->identifier . '-', '', $eventId), 2);
		if (count($id) < 2)
		{
			return false;
		}

		$success = $this->deleteEvent($id[1], $id[0]);
		if ($success != false)
		{
			$cache = JFactory::getCache('plg_' . $this->_name);
			$cache->clean();
		}
		return $success;
	}

	public function onEventAfterDelete ($event)
	{
	}

	public function onDPCalendarDoAction ($action, $pluginName, $data = null)
	{
		if (str_replace('dpcalendar_', '', $this->_name) != $pluginName)
		{
			return;
		}
		if (! method_exists($this, $action))
		{
			return;
		}
		return $this->$action($data);
	}

	public function onContentPrepareForm ($form, $data)
	{
		if (! ($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		if (! in_array($form->getName(), array(
				'com_dpcalendar.event'
		)))
		{
			return true;
		}

		$eventId = JRequest::getVar('e_id');
		if (empty($eventId))
		{
			return true;
		}

		if (strpos($eventId, $this->identifier) !== 0)
		{
			return true;
		}

		$eventId = str_replace($this->identifier . ':', $this->identifier . '-', $eventId);
		$id = explode('-', str_replace($this->identifier . '-', '', $eventId), 2);
		if (count($id) < 2)
		{
			return true;
		}

		return $this->prepareForm($id[1], $id[0], $form, $data);
	}

	protected function createCalendar ($id, $title, $description, $color = '3366CC')
	{
		$calendar = new stdClass();
		$calendar->id = $this->identifier . '-' . $id;
		$calendar->title = $title;
		$calendar->description = $description;
		$calendar->plugin_name = $this->_name;
		$calendar->level = 1;
		$calendar->color = $color;
		$calendar->access = 1;
		$calendar->access_content = 1;
		$calendar->created_user_id = 0;
		$calendar->external = true;
		$calendar->system = $this->identifier;
		$calendar->canCreate = false;
		$calendar->canEdit = false;
		$calendar->canEditOwn = false;
		$calendar->canDelete = false;
		$calendar->canAttend = false;

		return $calendar;
	}

	/**
	 *
	 * @return DPCalendarTableEvent
	 */
	protected function createEvent ($id, $calendarId)
	{
		$event = JTable::getInstance('Event', 'DPCalendarTable');
		$event->id = $this->identifier . '-' . $calendarId . '-' . $id;
		$event->alias = $id;
		$event->catid = $this->identifier . '-' . $calendarId;
		$event->category_access = 1;
		$event->category_alias = $calendarId;
		$event->category_title = DPCalendarHelper::getCalendar($event->catid)->title;
		$event->parent_alias = '';
		$event->parent_id = 0;
		$event->original_id = 0;
		$event->title = '';
		$event->rrule = null;
		$event->recurrence_id = null;
		$event->start_date = '';
		$event->end_date = '';
		$event->all_day = false;
		$event->color = '';
		$event->url = '';
		$event->locations = array();
		$event->hits = 0;
		$event->capacity = 0;
		$event->capacity_used = 0;
		$event->description = '';
		$event->state = 1;
		$event->access = 1;
		$event->access_content = 1;
		$event->language = '*';
		$event->created = '';
		$event->created_by = 0;
		$event->created_by_alias = '';
		$event->modified = '';
		$event->modified_by = 0;
		$event->params = '';
		$event->metadesc = null;
		$event->metakey = null;
		$event->metadata = new JRegistry();
		$event->author = null;
		$event->clearDb(null);

		return $event;
	}

	/**
	 *
	 * @return DPCalendarTableEvent
	 */
	private function createEventFromIcal (Sabre\VObject\Component\VEvent $event, $calendarId, array $originals)
	{
		$allDay = ! $event->DTSTART->hasTime();
		$startDate = DPCalendarHelper::getDate($event->DTSTART->getDateTime()->format('U'), $allDay);

		$endDate = null;
		if ($event->DURATION != null)
		{
			$endDate = clone $startDate;
			$duration = Sabre\VObject\DateTimeParser::parseDuration($event->DURATION, true);
			$endDate->modify($duration);
		}
		else
		{
			if (! $event->DTEND)
			{
				$endDate = clone $startDate;
				$endDate->setTime(23, 59, 59);
			}
			else
			{
				$endDate = DPCalendarHelper::getDate($event->DTEND->getDateTime()->format('U'), $allDay);
				if ($allDay)
				{
					$endDate->modify('-1 day');
				}
			}
		}

		// Search for the original to get the rrule
		$original = null;
		foreach ($originals as $tmp)
		{
			if ((string) $tmp->UID == (string) $event->UID && $tmp->{'RECURRENCE-ID'} === null && $tmp->RRULE !== null)
			{
				$original = $tmp;

				if ($event->{'RECURRENCE-ID'} === null && (string) $event->DTSTART == (string) $original->DTSTART && $event->RRULE === null)
				{
					$event->add('RECURRENCE-ID', (string) $event->DTSTART);
					$event->{'RECURRENCE-ID'}->parameters = $event->DTSTART->parameters;
				}
				break;
			}
		}

		// Find the override in the originals
		foreach ($originals as $o)
		{
			if ($event->{'RECURRENCE-ID'} == (string) $o->DTSTART && (string) $o->UID == (string) $event->UID && $o->RRULE === null)
			{
				$event = $o;
			}
		}

		$id = 0;
		$recId = $event->{'RECURRENCE-ID'};
		if ($original !== null && $recId === null)
		{
			$id = $event->UID . '_0';
		}
		else
		{
			$id = $event->UID . '_' . ($allDay ? $startDate->format('Ymd') : $startDate->format('YmdHi'));
		}

		$tmpEvent = $this->createEvent($id, $calendarId);
		if (! empty($recId))
		{
			$tmpEvent->recurrence_id = (string) $recId;
		}
		$tmpEvent->start_date = $startDate->toSql();
		$tmpEvent->end_date = $endDate->toSql();

		$title = (string) $event->SUMMARY;
		$title = str_replace('\n', ' ', $title);
		$title = str_replace('\N', ' ', $title);
		$tmpEvent->title = $this->icalDecode($title);
		$tmpEvent->alias = JApplication::stringURLSafe($tmpEvent->title);
		$tmpEvent->description = $this->icalDecode((string) $event->DESCRIPTION);

		$description = (string) $event->{'X-ALT-DESC'};
		if (! empty($description))
		{
			$desc = $description;
			if (is_array($desc))
			{
				$desc = implode(' ', $desc);
			}
			$tmpEvent->description = $desc;
		}

		$author = (string) $event->ORGANIZER;
		if (! empty($author))
		{
			$tmpEvent->created_by_alias = str_replace('MAILTO:', '', $author);
		}
		$color = (string) $event->{'X-COLOR'};
		if (! empty($color))
		{
			$tmpEvent->color = $color;
		}
		$url = (string) $event->{'x-url'};
		if (! empty($url))
		{
			$tmpEvent->url = $url;
		}
		$alias = (string) $event->{'x-alias'};
		if (! empty($alias))
		{
			$tmpEvent->alias = $alias;
		}
		$language = (string) $event->{'x-language'};
		if (! empty($language))
		{
			$tmpEvent->language = $language;
		}

		$location = (string) $event->LOCATION;
		$locations = array();
		if (! empty($location))
		{
			$geo = (string) $event->GEO;
			if (! empty($geo) && strpos($geo, ';') !== false)
			{
				static $locationModel = null;
				if ($locationModel == null)
				{
					JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'models', 'DPCalendarModel');
					$locationModel = JModelLegacy::getInstance('Locations', 'DPCalendarModel', array(
							'ignore_request' => true
					));
					$locationModel->getState();
					$locationModel->setState('list.limit', 1);
				}
				list ($latitude, $longitude) = explode(';', $geo);
				$locationModel->setState('filter.latitude', $latitude);
				$locationModel->setState('filter.longitude', $longitude);

				$tmp = $locationModel->getItems();
				if (! empty($tmp))
				{
					$locations = $tmp;

					$tmpEvent->location_ids = array();
					foreach ($tmp as $dpLocation)
					{
						$tmpEvent->location_ids[] = $dpLocation->id;
					}
				}
				else
				{
					list ($latitude, $longitude) = explode(';', $geo);
					$locations[] = DPCalendarHelperLocation::get($latitude . ',' . $longitude);
				}
			}
			else
			{
				$locations[] = DPCalendarHelperLocation::get($this->icalDecode($location));
			}
		}
		$tmpEvent->locations = $locations;
		$tmpEvent->all_day = $allDay;

		if ($original !== null)
		{
			if ($recId !== null)
			{
				$tmpEvent->original_id = $this->identifier . '-' . $calendarId . '-' . $event->UID . '_0';
			}
			else
			{
				$tmpEvent->rrule = (string) $original->RRULE;
				$tmpEvent->original_id = - 1;
			}
		}

		return $tmpEvent;
	}

	protected function getDbCal ($calendarId)
	{
		$calendars = $this->fetchCalendars(array(
				$calendarId
		));
		if (empty($calendars))
		{
			return null;
		}

		return $calendars[0];
	}

	protected function icalDecode ($text)
	{
		$newText = str_replace('\n', '<br/>', $text);
		$newText = str_replace('\N', '<br/>', $newText);
		$newText = str_replace('\,', ',', $newText);
		$newText = str_replace('\;', ';', $newText);
		return $newText;
	}

	protected function icalEncode ($text)
	{
		$newText = str_replace(',', '\,', $text);
		$newText = str_replace(';', '\;', $newText);
		return $newText;
	}

	protected function replaceNl ($text, $replace = '')
	{
		return str_replace(array(
				"\r\n",
				"\r",
				"\n"
		), $replace, $text);
	}

	protected function log ($message)
	{
		JFactory::getApplication()->enqueueMessage((string) $message, 'warning');
	}
}
