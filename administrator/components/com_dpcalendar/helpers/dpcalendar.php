<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

JLoader::import('joomla.application.component.helper');
JLoader::import('joomla.application.categories');
JLoader::import('joomla.environment.browser');
JLoader::import('joomla.filesystem.file');

JLoader::register('DPCalendarHelperIcal', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'helpers' . DS . 'ical.php');
JLoader::register('DPCalendarHelperLocation', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'helpers' . DS . 'location.php');

JLoader::register('DPCalendarHelperPayment', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'helpers' . DS . 'payment.php');

if (! class_exists('Mustache'))
{
	JLoader::register('Mustache',
			JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'libraries' . DS . 'mustache' . DS . 'Mustache.php');
}

class DPCalendarHelper
{

	private static $lookup;

	private static $calendars = array();

	public static function getCalendar ($id)
	{
		if (isset(self::$calendars[$id]))
		{
			return self::$calendars[$id];
		}
		$calendar = null;
		if (is_numeric($id) || $id == 'root')
		{
			$calendar = JCategories::getInstance('DPCalendar')->get($id);
			if ($calendar == null)
			{
				return null;
			}
			$user = JFactory::getUser();

			$reg = new JRegistry($calendar->params);
			$calendar->color = $reg->get('color', '3366CC');
			$calendar->external = false;
			$calendar->system = 'joomla';

			$calendar->canCreate = $user->authorise('core.create', 'com_dpcalendar.category.' . $calendar->id);
			$calendar->canEdit = $user->authorise('core.edit', 'com_dpcalendar.category.' . $calendar->id);
			$calendar->canEditOwn = $user->authorise('core.edit.own', 'com_dpcalendar.category.' . $calendar->id);
			$calendar->canDelete = $user->authorise('core.delete', 'com_dpcalendar.category.' . $calendar->id);
			$calendar->canAttend = $user->authorise('dpcalendar.attend', 'com_dpcalendar.category.' . $calendar->id);

			$userId = $user->get('id');

			if (! empty($userId) && $user->authorise('core.edit.own', 'com_dpcalendar.category.' . $calendar->id))
			{
				if ($userId == $calendar->created_user_id)
				{
					$calendar->canEdit = true;
				}
			}
		}
		else
		{
			$tmp = array();
			JPluginHelper::importPlugin('dpcalendar');
			$tmp = JDispatcher::getInstance()->trigger('onCalendarsFetch', array(
					$id
			));
			if (! empty($tmp))
			{
				foreach ($tmp as $calendars)
				{
					foreach ($calendars as $fetchedCalendar)
					{
						$calendar = $fetchedCalendar;
					}
				}
			}
		}

		self::$calendars[$id] = $calendar;

		return $calendar;
	}

	public static function addSubmenu ($vName = 'cpanel')
	{
		JSubMenuHelper::addEntry(JText::_('COM_DPCALENDAR_SUBMENU_CPANEL'), 'index.php?option=com_dpcalendar&view=cpanel', $vName == 'cpanel');
		JSubMenuHelper::addEntry(JText::_('COM_DPCALENDAR_SUBMENU_EVENTS'), 'index.php?option=com_dpcalendar&view=events', $vName == 'events');
		JSubMenuHelper::addEntry(JText::_('COM_DPCALENDAR_SUBMENU_CALENDARS'), 'index.php?option=com_categories&extension=com_dpcalendar',
				$vName == 'categories');
		JSubMenuHelper::addEntry(JText::_('COM_DPCALENDAR_SUBMENU_LOCATIONS'), 'index.php?option=com_dpcalendar&view=locations',
				$vName == 'locations');

		if (! self::isFree())
		{
			JSubMenuHelper::addEntry(JText::_('COM_DPCALENDAR_SUBMENU_ATTENDEES'), 'index.php?option=com_dpcalendar&view=attendees',
					$vName == 'attendees');
		}

		JSubMenuHelper::addEntry(JText::_('COM_DPCALENDAR_SUBMENU_TOOLS'), 'index.php?option=com_dpcalendar&view=tools', $vName == 'tools');
		JSubMenuHelper::addEntry(JText::_('COM_DPCALENDAR_SUBMENU_SUPPORT'), 'index.php?option=com_dpcalendar&view=support', $vName == 'support');
		if ($vName == 'categories')
		{
			JToolBarHelper::title(JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('com_dpcalendar')), 'dpcalendar-categories');
		}
	}

	public static function getActions ($categoryId = 0)
	{
		$user = JFactory::getUser();
		$result = new JObject();

		if (empty($categoryId))
		{
			$assetName = 'com_dpcalendar';
			$level = 'component';
		}
		else
		{
			$assetName = 'com_dpcalendar.category.' . (int) $categoryId;
			$level = 'category';
		}

		$actions = JAccess::getActions('com_dpcalendar', $level);

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	public static function getComponentParameter ($key, $defaultValue = null)
	{
		$params = JComponentHelper::getParams('com_dpcalendar');
		return $params->get($key, $defaultValue);
	}

	public static function getFrLanguage ()
	{
		$language = JFactory::getApplication()->getCfg('language');

		$user = JFactory::getUser();
		if ($user->get('id'))
		{
			$userLanguage = $user->getParam('language');
			if (! empty($userLanguage))
			{
				$language = $userLanguage;
			}
		}
		return $language;
	}

	public static function dayToString ($day, $abbr = false)
	{
		$date = new JDate();
		return addslashes($date->dayToString($day, $abbr));
	}

	public static function monthToString ($month, $abbr = false)
	{
		$date = new JDate();
		return addslashes($date->monthToString($month, $abbr));
	}

	public static function getDate ($date = null, $allDay = null, $tz = null)
	{
		$dateObj = JFactory::getDate($date, $tz);

		$timezone = JFactory::getApplication()->getCfg('offset');
		$user = JFactory::getUser();
		if ($user->get('id'))
		{
			$userTimezone = $user->getParam('timezone');
			if (! empty($userTimezone))
			{
				$timezone = $userTimezone;
			}
		}
		if (! $allDay)
		{
			$dateObj->setTimezone(new DateTimeZone($timezone));
		}
		return $dateObj;
	}

	public static function getDateFromString ($date, $time, $allDay, $dateFormat = null, $timeFormat = null)
	{
		$string = $date;
		if (! empty($time))
		{
			$string = $date . ($allDay ? '' : ' ' . $time);
		}

		$months = array(
				'JANUARY',
				'FEBRUARY',
				'MARCH',
				'APRIL',
				'MAY',
				'JUNE',
				'JULY',
				'AUGUST',
				'SEPTEMBER',
				'OCTOBER',
				'NOVEMBER',
				'DECEMBER'
		);
		$monthsShort = array(
				'JANUARY_SHORT',
				'FEBRUARY_SHORT',
				'MARCH_SHORT',
				'APRIL_SHORT',
				'MAY_SHORT',
				'JUNE_SHORT',
				'JULY_SHORT',
				'AUGUST_SHORT',
				'SEPTEMBER_SHORT',
				'OCTOBER_SHORT',
				'NOVEMBER_SHORT',
				'DECEMBER_SHORT'
		);
		$lang = JLanguage::getInstance('en-GB');
		foreach (array_merge($months, $monthsShort) as $month)
		{
			$string = str_replace(JText::_($month), $lang->_($month), $string);
		}

		if (empty($dateFormat))
		{
			$dateFormat = self::getComponentParameter('event_form_date_format', 'm.d.Y');
		}
		if (empty($timeFormat))
		{
			$timeFormat = self::getComponentParameter('event_form_time_format', 'g:i a');
		}

		$date = self::getDate(null, $allDay);
		$date = DateTime::createFromFormat($dateFormat . ($allDay ? '' : ' ' . $timeFormat), $string, $date->getTimezone());
		if ($date == null)
		{
			throw new Exception('Could not inteprete format: ' . ($dateFormat . ($allDay ? '' : ' ' . $timeFormat)) . ' for date string : ' . $string);
		}

		$date = self::getDate($date->format('U'), $allDay);

		return $date;
	}

	public static function renderEvents (array $events = null, $output, $params = null, $eventParams = array())
	{
		if ($events === null)
		{
			$events = array();
		}
		if ($params == null)
		{
			$params = JComponentHelper::getParams('com_dpcalendar');
		}

		JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar');

		$return = JFactory::getApplication()->input->getInt('Itemid', null);
		if (! empty($return))
		{
			$return = JRoute::_('index.php?Itemid=' . $return);
		}

		$user = JFactory::getUser();

		$lastHeading = '';

		$configuration = $eventParams;
		$configuration['events'] = array();
		$locationCache = array();
		foreach ($events as $event)
		{
			$variables = array();

			$calendar = self::getCalendar($event->catid);

			$variables['canEdit'] = $calendar->canEdit || ($calendar->canEditOwn && $event->created_by == $user->id);
			$variables['editLink'] = self::getFormRoute($event->id, $return);
			$variables['canDelete'] = $calendar->canDelete || ($calendar->canEditOwn && $event->created_by == $user->id);
			$variables['deleteLink'] = JRoute::_(
					'index.php?option=com_dpcalendar&task=event.delete&e_id=' . $event->id . '&return=' . base64_encode($return));

			$variables['canAttend'] = self::openForAttending($event);
			$variables['attendLink'] = self::getAttendRoute($event, $return);
			$variables['attending'] = isset($event->attending) ? (boolean) $event->attending : false;

			$variables['calendarLink'] = self::getCalendarRoute($event->catid);
			$variables['backLink'] = self::getEventRoute($event->id, $event->catid);
			$variables['backLinkFull'] = self::getEventRoute($event->id, $event->catid, true);

			// The date formats from http://php.net/date
			$dateformat = $params->get('event_date_format', 'm.d.Y');
			$timeformat = $params->get('event_time_format', 'g:i a');

			// These are the dates we'll display
			$startDate = self::getDate($event->start_date, $event->all_day)->format($dateformat, true);
			$startTime = self::getDate($event->start_date, $event->all_day)->format($timeformat, true);
			$endDate = self::getDate($event->end_date, $event->all_day)->format($dateformat, true);
			$endTime = self::getDate($event->end_date, $event->all_day)->format($timeformat, true);
			$dateSeparator = '-';

			$timeString = $startTime . ' ' . $startDate . ' ' . $dateSeparator . ' ' . $endTime . ' ' . $endDate;
			$copyDateTimeFormat = 'Ymd';

			if ($event->all_day)
			{
				if ($startDate == $endDate)
				{
					$timeString = $startDate;
					$dateSeparator = '';
					$endDate = '';
				}
				else
				{
					$timeString = $startDate . ' ' . $dateSeparator . ' ' . $endDate;
				}
				$startTime = '';
				$endTime = '';
			}
			else
			{
				if ($startDate == $endDate)
				{
					$timeString = $startDate . ' ' . $startTime . ' ' . $dateSeparator . ' ' . $endTime;
					$endDate = '';
				}
				$copyDateTimeFormat = 'Ymd\THis';
			}

			$variables['color'] = $event->color;
			if (empty($variables['color']) && $calendar != null)
			{
				$variables['color'] = $calendar->color;
			}

			$variables['calendarName'] = $calendar != null ? $calendar->title : $event->catid;
			$variables['title'] = $event->title;
			$variables['date'] = $timeString;
			$variables['startDate'] = $startDate;
			$variables['startDateIso'] = self::getDate($event->start_date, $event->all_day)->format('c');
			$variables['startTime'] = $startTime;
			$variables['endDate'] = $endDate;
			$variables['endDateIso'] = self::getDate($event->end_date, $event->all_day)->format('c');
			$variables['endTime'] = $endTime;
			$variables['dateSeparator'] = $dateSeparator;

			$variables['monthNr'] = self::getDate($event->start_date, $event->all_day)->format('m', true);
			$variables['year'] = self::getDate($event->start_date, $event->all_day)->format('Y', true);
			$variables['month'] = self::getDate($event->start_date, $event->all_day)->format('M', true);
			$variables['day'] = self::getDate($event->start_date, $event->all_day)->format('j', true);

			$location = '';
			if (isset($event->locations) && ! empty($event->locations))
			{
				$variables['location'] = $event->locations;
				foreach ($event->locations as $location)
				{
					if (key_exists($location->id, $locationCache))
					{
						$location = $locationCache[$location->id];
					}
					else
					{
						$tmp = DPCalendarHelperLocation::format($location);
						$location->full = $tmp;
						$locationCache[$location->id] = $tmp;
						$location = $tmp;
					}
				}
			}

			try
			{
				$variables['description'] = JHTML::_('content.prepare', $event->description);
			}
			catch (Exception $e)
			{
				$variables['description'] = $event->description;
			}
			if ($params->get('description_length', 0) > 0)
			{
				$variables['description'] = JHtml::_('string.truncate', $variables['description'], $params->get('description_length', 0));
			}

			$variables['url'] = $event->url;
			$variables['hits'] = $event->hits;

			$author = JFactory::getUser($event->created_by);
			$variables['author'] = $author->name;
			if (! empty($event->created_by_alias))
			{
				$variables['author'] = $event->created_by_alias;
			}
			$variables['avatar'] = self::getAvatar($author->id, $author->email, $params);

			$variables['capacity'] = $event->capacity == null ? JText::_('COM_DPCALENDAR_FIELD_CAPACITY_UNLIMITED') : $event->capacity;
			$variables['capacityUsed'] = $event->capacity_used;
			if (isset($event->attendees))
			{
				foreach ($event->attendees as $attendee)
				{
					if ($attendee->user_id < 1)
					{
						continue;
					}
					$attendee->avatar = self::getAvatar($attendee->id, $attendee->email, $params);
				}
				$variables['attendees'] = $event->attendees;
			}

			$end = self::getDate($event->end_date, $event->all_day);
			if ($event->all_day)
			{
				$end->modify('+1 day');
			}
			$variables['copyGoogleUrl'] = 'http://www.google.com/calendar/render?action=TEMPLATE&text=' . urlencode($event->title);
			$variables['copyGoogleUrl'] .= '&dates=' . self::getDate($event->start_date, $event->all_day)->format($copyDateTimeFormat, true) . '%2F' .
					 $end->format($copyDateTimeFormat, true);
			$variables['copyGoogleUrl'] .= '&location=' . urlencode($location);
			$variables['copyGoogleUrl'] .= '&details=' . urlencode(JHtml::_('string.truncate', $event->description, 200));
			$variables['copyGoogleUrl'] .= '&hl=' . self::getFrLanguage() . '&ctz=' .
					 self::getDate($event->start_date, $event->all_day)->getTimezone()->getName();
			$variables['copyGoogleUrl'] .= '&sf=true&output=xml';

			$variables['copyOutlookUrl'] = JRoute::_("index.php?option=com_dpcalendar&view=event&format=raw&id=" . $event->id);

			$groupHeading = self::getDate($event->start_date, $event->all_day)->format($params->get('grouping', ''), true);
			if ($groupHeading != $lastHeading)
			{
				$lastHeading = $groupHeading;
				$variables['header'] = $groupHeading;
			}

			$configuration['events'][] = $variables;
		}

		$configuration['canCreate'] = self::canCreateEvent();
		$configuration['createLink'] = self::getFormRoute(0, $return);

		$configuration['calendarNameLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_CALANDAR');
		$configuration['titleLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_TITLE');
		$configuration['dateLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DATE');
		$configuration['locationLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_LOCATION');
		$configuration['descriptionLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DESCRIPTION');
		$configuration['commentsLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_COMMENTS');
		$configuration['eventLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL');
		$configuration['authorLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_AUTHOR');
		$configuration['attendeesLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_ATTENDEES');
		$configuration['attendLabel'] = JText::_('COM_DPCALENDAR_ATTEND');
		$configuration['attendingLabel'] = JText::_('COM_DPCALENDAR_ATTENDING');
		$configuration['capacityLabel'] = JText::_('COM_DPCALENDAR_FIELD_CAPACITY_LABEL');
		$configuration['capacityUsedLabel'] = JText::_('COM_DPCALENDAR_FIELD_CAPACITY_USED_LABEL');
		$configuration['hitsLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_HITS');
		$configuration['urlLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_URL');
		$configuration['copyLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_COPY');
		$configuration['copyGoogleLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_COPY_GOOGLE');
		$configuration['copyOutlookLabel'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_COPY_OUTLOOK');
		$configuration['language'] = substr(self::getFrLanguage(), 0, 2);
		$configuration['editLabel'] = JText::_('JACTION_EDIT');
		$configuration['createLabel'] = JText::_('JACTION_CREATE');
		$configuration['deleteLabel'] = JText::_('JACTION_DELETE');

		$configuration['emptyText'] = JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_NO_EVENT_TEXT');

		try
		{
			$m = new Mustache();
			return $m->render($output, $configuration);
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

	public static function getAvatar ($userId, $email, $params)
	{
		$image = null;
		$avatarProvider = $params->get('avatar', 1);
		if ($avatarProvider == 2)
		{
			$size = $params->get('avatar_width', 0);
			if ($size == 0)
			{
				$size = $params->get('avatar_height', 0);
			}
			if ($size == 0)
			{
				$size = '';
			}
			else
			{
				$size = '?s=' . $size;
			}
			$image = (JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . '://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) .
					 $size;
		}

		$cbLoader = JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';
		$jomsocial = JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php';
		if ((($avatarProvider == 1 && ! JFile::exists($jomsocial)) || $avatarProvider == 4) && JFile::exists($cbLoader))
		{
			include_once $cbLoader;
			$cbUser = CBuser::getInstance($userId);
			if ($cbUser !== null)
			{
				$image = $cbUser->getField('avatar', null, 'csv');
			}
			if (empty($image))
			{
				$image = selectTemplate() . 'images/avatar/tnnophoto_n.png';
			}
		}
		if (($avatarProvider == 1 || $avatarProvider == 3) && JFile::exists($jomsocial))
		{
			include_once $jomsocial;
			$image = CFactory::getUser($userId)->getThumbAvatar();
		}
		if ($image != null)
		{
			$w = $params->get('avatar_width', 0);
			$h = $params->get('avatar_height', 0);
			if ($w != 0)
			{
				$w = 'width="' . $w . '"';
			}
			else if ($h == 0)
			{
				$w = 'width="80"';
			}
			else
			{
				$w = '';
			}
			if ($h != 0)
			{
				$h = 'height="' . $h . '"';
			}
			else
			{
				$h = '';
			}
			return '<img src="' . $image . '" ' . $w . ' ' . $h . '/>';
		}
		return '';
	}

	public static function getGoogleLanguage ()
	{
		$languages = array(
				'ar',
				'bg',
				'bn',
				'ca',
				'cs',
				'da',
				'de',
				'el',
				'en',
				'en-AU',
				'en-GB',
				'es',
				'eu',
				'fa',
				'fi',
				'fil',
				'fr',
				'gl',
				'gu',
				'hi',
				'hr',
				'hu',
				'id',
				'it',
				'iw',
				'ja',
				'kn',
				'ko',
				'lt',
				'lv',
				'ml',
				'mr',
				'nl',
				'nn',
				'no',
				'or',
				'pl',
				'pt',
				'pt-BR',
				'pt-PT',
				'rm',
				'ro',
				'ru',
				'sk',
				'sl',
				'sr',
				'sv',
				'tl',
				'ta',
				'te',
				'th',
				'tr',
				'uk',
				'vi',
				'zh-CN',
				'zh-TW'
		);
		$lang = self::getFrLanguage();
		if (! in_array($lang, $languages))
		{
			$lang = substr($lang, 0, strpos($lang, '-'));
		}
		if (! in_array($lang, $languages))
		{
			$lang = 'en';
		}
		return $lang;
	}

	public static function fetchContent ($uri)
	{
		if (empty($uri))
		{
			return '';
		}

		$content = '';
		try
		{
			$internal = ! filter_var($uri, FILTER_VALIDATE_URL);

			if ($internal && strpos($uri, '/') !== 0)
			{
				$uri = JPATH_ROOT . DS . $uri;
			}

			if ($internal)
			{
				JLoader::import('joomla.filesystem.folder');
				if (JFolder::exists($uri))
				{
					foreach (JFolder::files($uri, '\.ics', true, true) as $file)
					{
						$content .= JFile::read($file);
					}
				}
				else
				{
					$content = JFile::read($uri);
				}
			}
			else
			{
				$options = new JRegistry();
				foreach (array(
						'curl',
						'socket',
						'stream'
				) as $adapter)
				{
					$class = 'JHttpTransport' . ucfirst($adapter);
					$http = new JHttp($options, new $class($options));

					$u = JUri::getInstance($uri);
					$uri = $u->toString(array(
							'scheme',
							'user',
							'pass',
							'host',
							'port',
							'path'
					));
					if (self::isJoomlaVersion('2.5'))
					{
						$uri .= urlencode($u->toString(array(
								'query',
								'fragment'
						)));
					}
					else
					{
						$uri .= $u->toString(array(
								'query',
								'fragment'
						));
					}

					$content = $http->get($uri)->body;
					break;
				}
			}
		}
		catch (Exception $e)
		{
			return $e;
		}
		if (! empty($content))
		{
			return $content;
		}
		return '';
	}

	public static function getEventRoute ($id, $calId, $full = false, $autoRoute = true)
	{
		$needles = array(
				'event' => array(
						(int) $id
				)
		);
		$tmpl = '';
		if (JRequest::getWord('tmpl'))
		{
			$tmpl = '&tmpl=' . JRequest::getWord('tmpl');
		}

		// Create the link
		$link = ($full ? JUri::root() : '') . 'index.php?option=com_dpcalendar&view=event&id=' . $id . $tmpl;
		if ($calId > 0 || (! is_numeric($calId) && $calId != 'root'))
		{
			$needles['calendar'] = array(
					$calId
			);
			$needles['list'] = array(
					$calId
			);
		}

		if ($item = self::findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		else if ($item = self::findItem())
		{
			$link .= '&Itemid=' . $item;
		}

		if(!$autoRoute){
			return $link;
		}

		return JRoute::_($link, false);
	}

	public static function getFormRoute ($id, $return = null, $append = null)
	{
		if ($id)
		{
			$key = 'e_id';
			if (JFactory::getApplication()->isAdmin())
			{
				$key = 'id';
			}
			$link = 'index.php?option=com_dpcalendar&task=event.edit&' . $key . '=' . $id;
		}
		else
		{
			if (JFactory::getApplication()->isAdmin())
			{
				$link = 'index.php?option=com_dpcalendar&task=event.add&e_id=0';
			}
			else
			{
				$link = 'index.php?option=com_dpcalendar&view=form&layout=edit&e_id=0';
			}
		}

		$itemId = JFactory::getApplication()->input->get('Itemid', null);
		if (! empty($itemId))
		{
			$link .= '&Itemid=' . $itemId;
		}

		if (! empty($append))
		{
			$link .= '&' . $append;
		}
		if (JRequest::getWord('tmpl'))
		{
			$link .= '&tmpl=' . JRequest::getWord('tmpl');
		}
		if ($return)
		{
			$link .= '&return=' . base64_encode($return);
		}

		return $link;
	}

	public static function getAttendRoute ($event, $return = null)
	{
		if (empty($return))
		{
			$return = self::getEventRoute($event->id, $event->catid);
		}
		return JRoute::_(
				'index.php?option=com_dpcalendar&task=attendee.add&e_id=' . $event->id . '&tmpl=' . JRequest::getWord('tmpl') . '&return=' .
						 base64_encode($return));
	}

	public static function getCalendarIcalRoute ($calId)
	{
		return JRoute::_('index.php?option=com_dpcalendar&task=ical.download&id=' . $calId);
	}

	public static function getCalendarRoute ($calId)
	{
		if ($calId instanceof JCategoryNode)
		{
			$id = $calId->id;
			$calendar = $calId;
		}
		else
		{
			$id = (int) $calId;
			$calendar = self::getCalendar($id);
		}

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			$needles = array(
					'calendar' => array(
							$id
					)
			);

			if ($item = self::findItem($needles))
			{
				$link = 'index.php?Itemid=' . $item;
			}
			else
			{
				// Create the link
				$link = 'index.php?option=com_dpcalendar&view=calendar&id=' . $id;

				if ($calendar)
				{
					$calIds = array_reverse($calendar->getPath());
					$needles = array(
							'calendar' => $calIds,
							'list' => $calIds
					);

					if ($item = self::findItem($needles))
					{
						$link .= '&Itemid=' . $item;
					}
					else if ($item = self::findItem())
					{
						$link .= '&Itemid=' . $item;
					}
				}
			}
		}

		return $link;
	}

	public static function findItem ($needles = null)
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component = JComponentHelper::getComponent('com_dpcalendar');
			$items = $menus->getItems('component_id', $component->id);

			if ($items)
			{
				foreach ($items as $item)
				{
					if (isset($item->query) && isset($item->query['view']))
					{
						$view = $item->query['view'];

						if (! isset(self::$lookup[$view]))
						{
							self::$lookup[$view] = array();
						}

						$ids = $item->params->get('ids');
						if (! is_array($ids))
						{
							$ids = array(
									$ids
							);
						}

						foreach ($ids as $id)
						{
							$root = self::getCalendar($id);
							if ($root == null)
							{
								continue;
							}
							self::$lookup[$view][$id] = $item->id;
							if (! $root->external)
							{
								foreach ($root->getChildren(true) as $child)
								{
									self::$lookup[$view][$child->id] = $item->id;
								}
							}
						}
					}
				}
			}
		}
		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$view][$id]))
						{
							return self::$lookup[$view][$id];
						}
					}
				}
			}
		}
		else
		{
			$active = $menus->getActive();
			if ($active && $active->component == 'com_dpcalendar')
			{
				return $active->id;
			}
		}

		return null;
	}

	public static function doPluginAction ($plugin, $action, $data = null)
	{
		JPluginHelper::importPlugin('dpcalendar');

		$enabled = JPluginHelper::isEnabled('dpcalendar', 'dpcalendar_' . $plugin);
		if (! $enabled)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('folder AS type, element AS name, params')
				->from('#__extensions')
				->where('element = ' . $db->quote('dpcalendar_' . $plugin));
			$p = $db->setQuery($query)->loadObject();

			JLoader::import('dpcalendar.dpcalendar_' . $plugin . '.dpcalendar_' . $plugin, JPATH_PLUGINS);

			$className = 'Plg' . $p->type . $p->name;
			$dispatcher = JDispatcher::getInstance();
			$p = (array) $p;
			new $className($dispatcher, $p);
		}

		$result = JDispatcher::getInstance()->trigger('onDPCalendarDoAction', array(
				$action,
				$plugin
		));

		return $result;
	}

	public static function openForAttending ($event)
	{
		if (self::isFree())
		{
			return false;
		}
		if (self::getDate($event->start_date)->format('U') < self::getDate()->format('U'))
		{
			return false;
		}
		if ($event->capacity !== null && $event->capacity_used >= $event->capacity)
		{
			return false;
		}
		return self::getCalendar($event->catid)->canAttend;
	}

	public static function isJoomlaVersion ($version)
	{
		$j = new JVersion();
		return substr($j->RELEASE, 0, strlen($version)) == $version;
	}

	public static function canCreateEvent ()
	{
		$user = JFactory::getUser();
		$canAdd = $user->authorise('core.create', 'com_dpcalendar') || count($user->getAuthorisedCategories('com_dpcalendar', 'core.create'));

		if (! $canAdd)
		{
			JPluginHelper::importPlugin('dpcalendar');
			$tmp = JDispatcher::getInstance()->trigger('onCalendarsFetch');
			if (! empty($tmp))
			{
				foreach ($tmp as $tmpCalendars)
				{
					foreach ($tmpCalendars as $calendar)
					{
						if ($calendar->canCreate)
						{
							$canAdd = true;
							break;
						}
					}
				}
			}
		}
		return $canAdd;
	}

	public static function isFree ()
	{
		return ! JFile::exists(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/tables/attendee.php');
	}

	public static function loadLibrary ($libraries = array('jquery' => true))
	{
		if (JFactory::getDocument()->getType() != 'html')
		{
			return;
		}

		$document = JFactory::getDocument();
		if (self::isJoomlaVersion('2.5'))
		{
			if (isset($libraries['jquery']))
			{
				if (! JFactory::getApplication()->get('jquery', false))
				{
					JFactory::getApplication()->set('jquery', true);
					$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/jquery/jquery.min.js');
				}
				$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/jquery/jquery.migrate.min.js');
				$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/jquery/dpcalendar/dpNoConflict.js');
			}

			if (isset($libraries['bootstrap']))
			{
				$document->addStyleSheet(JURI::root() . 'components/com_dpcalendar/libraries/bootstrap/css/bootstrap.min.css');
				$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/bootstrap/js/bootstrap.min.js');
			}

			if (isset($libraries['chosen']))
			{
				$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/jquery/ext/jquery.chosen.min.js');
				$document->addStyleSheet(JURI::root() . 'components/com_dpcalendar/libraries/jquery/ext/jquery.chosen.css');
			}
		}
		else
		{
			if (isset($libraries['jquery']))
			{
				JHtml::_('jquery.framework');
				$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/jquery/dpcalendar/dpNoConflict.js');
			}

			if (isset($libraries['bootstrap']))
			{
				JHtmlBootstrap::framework();

				if (self::getComponentParameter('force_bootstrap', 0) == 1)
				{
					$document->addStyleSheet(JURI::root() . 'components/com_dpcalendar/libraries/bootstrap/css/bootstrap.min.css');
				}
			}

			if (isset($libraries['chosen']))
			{
				JHtml::_('formbehavior.chosen', 'select');
			}
		}

		if (isset($libraries['dpcalendar']))
		{
			$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/dpcalendar/dpcalendar.js');
			$document->addStyleSheet(JURI::root() . 'components/com_dpcalendar/libraries/dpcalendar/dpcalendar.css');
		}

		if (isset($libraries['datepicker']))
		{
			$document->addStyleSheet(JURI::root() . 'components/com_dpcalendar/libraries/jquery/themes/bootstrap/jquery-ui.custom.css');
			$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/jquery/ui/jquery-ui.custom.min.js');
		}

		if (isset($libraries['maps']))
		{
			if ($key = DPCalendarHelper::getComponentParameter('map_api_google_key', ''))
			{
				$key .= '&key=' . $key;
			}
			$document->addScript(
					(JBrowser::getInstance()->isSSLConnection() ? "https" : "http") .
							 '://maps.googleapis.com/maps/api/js?sensor=false&libraries=places&language=' . self::getGoogleLanguage() . $key);
			$document->addScript((JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . '://j.maxmind.com/app/geoip.js');
		}

		if (isset($libraries['fullcalendar']))
		{
			$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/fullcalendar/fullcalendar.min.js');
			$document->addStyleSheet(JURI::root() . 'components/com_dpcalendar/libraries/fullcalendar/fullcalendar.css');
		}
	}

	public static function sendMessage ($message, $error = false, array $data = array())
	{
		ob_clean();

		JLoader::import('components.com_languages.helpers.jsonresponse', JPATH_ADMINISTRATOR);
		if (! $error)
		{
			JFactory::getApplication()->enqueueMessage($message);
			echo new JJsonResponse($data);
		}
		else
		{
			JFactory::getApplication()->enqueueMessage($message, 'error');
			echo new JJsonResponse($data);
		}

		JFactory::getApplication()->close();
	}

	public static function sendMail ($subject, $message, $group)
	{
		$groups = self::getComponentParameter($group);
		if (empty($groups))
		{
			return;
		}
		if (! is_array($groups))
		{
			$groups = array(
					$groups
			);
		}

		foreach ($groups as $groupId)
		{
			$users = JFactory::getACL()->getUsersByGroup($groupId);
			foreach ($users as $user)
			{
				$u = JUser::getTable();
				if ($u->load($user))
				{
					$mailer = JFactory::getMailer();
					$mailer->setSubject($subject);
					$mailer->setBody($message);
					$mailer->IsHTML(true);
					$mailer->addRecipient($u->email);
					$mailer->Send();
				}
			}
		}
	}

	public static function parseHtml ($text)
	{
		$text = str_replace('\n', PHP_EOL, $text);

		// IE does not handle &apos; entity!
		$text = preg_replace('/&apos;/', '&#39;', $text);
		$section_html_pattern = '%# Rev:20100913_0900 github.com/jmrware/LinkifyURL
		# Section text into HTML <A> tags  and everything else.
		(                              # $1: Everything not HTML <A> tag.
		[^<]+(?:(?!<a\b)<[^<]*)*     # non A tag stuff starting with non-"<".
		|      (?:(?!<a\b)<[^<]*)+     # non A tag stuff starting with "<".
		)                              # End $1.
		| (                              # $2: HTML <A...>...</A> tag.
		<a\b[^>]*>                   # <A...> opening tag.
		[^<]*(?:(?!</a\b)<[^<]*)*    # A tag contents.
		</a\s*>                      # </A> closing tag.
		)                              # End $2:
		%ix';
		$text = preg_replace_callback($section_html_pattern, array(
				'DPCalendarHelper',
				'linkifyHtmlCallback'
		), $text);
		$text = nl2br($text);
		return $text;
	}

	public static function linkify ($text)
	{
		$url_pattern = '/# Rev:20100913_0900 github.com\/jmrware\/LinkifyURL
		# Match http & ftp URL that is not already linkified.
		# Alternative 1: URL delimited by (parentheses).
		(\()                     # $1  "(" start delimiter.
		((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $2: URL.
		(\))                     # $3: ")" end delimiter.
		| # Alternative 2: URL delimited by [square brackets].
		(\[)                     # $4: "[" start delimiter.
		((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $5: URL.
		(\])                     # $6: "]" end delimiter.
		| # Alternative 3: URL delimited by {curly braces}.
		(\{)                     # $7: "{" start delimiter.
		((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $8: URL.
		(\})                     # $9: "}" end delimiter.
		| # Alternative 4: URL delimited by <angle brackets>.
		(<|&(?:lt|\#60|\#x3c);)  # $10: "<" start delimiter (or HTML entity).
		((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $11: URL.
		(>|&(?:gt|\#62|\#x3e);)  # $12: ">" end delimiter (or HTML entity).
		| # Alternative 5: URL not delimited by (), [], {} or <>.
		(                        # $13: Prefix proving URL not already linked.
		(?: ^                  # Can be a beginning of line or string, or
		| [^=\s\'"\]]          # a non-"=", non-quote, non-"]", followed by
		) \s*[\'"]?            # optional whitespace and optional quote;
		| [^=\s]\s+              # or... a non-equals sign followed by whitespace.
		)                        # End $13. Non-prelinkified-proof prefix.
		( \b                     # $14: Other non-delimited URL.
		(?:ht|f)tps?:\/\/      # Required literal http, https, ftp or ftps prefix.
		[a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]+ # All URI chars except "&" (normal*).
		(?:                    # Either on a "&" or at the end of URI.
		(?!                  # Allow a "&" char only if not start of an...
		&(?:gt|\#0*62|\#x0*3e);                  # HTML ">" entity, or
		| &(?:amp|apos|quot|\#0*3[49]|\#x0*2[27]); # a [&\'"] entity if
		[.!&\',:?;]?        # followed by optional punctuation then
		(?:[^a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]|$)  # a non-URI char or EOS.
		) &                  # If neg-assertion true, match "&" (special).
		[a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]* # More non-& URI chars (normal*).
		)*                     # Unroll-the-loop (special normal*)*.
		[a-z0-9\-_~$()*+=\/#[\]@%]  # Last char can\'t be [.!&\',;:?]
		)                        # End $14. Other non-delimited URL.
		/imx';
		$url_replace = '$1$4$7$10$13<a href="$2$5$8$11$14">$2$5$8$11$14</a>$3$6$9$12';
		return preg_replace($url_pattern, $url_replace, $text);
	}

	public static function linkifyHtmlCallback ($matches)
	{
		if (isset($matches[2]))
		{
			return $matches[2];
		}
		return self::linkify($matches[1]);
	}

	public static function where ()
	{
		$e = new Exception();
		$trace = '<pre>' . $e->getTraceAsString() . '</pre>';

		echo $trace;
		return $trace;
	}
}
