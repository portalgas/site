<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarHelperIcal
{

	public static function createIcalFromCalendar ($calendarId, $asDownload = false)
	{
		JModelLegacy::addIncludePath(JPATH_SITE . DS . 'components' . DS . 'com_dpcalendar' . DS . 'models');
		$model = JModelLegacy::getInstance('Events', 'DPCalendarModel');
		$model->getState();
		$model->setState('category.id', $calendarId);
		$model->setState('category.recursive', false);
		$model->setState('list.limit', 100000);
		$model->setState('list.start-date', '0');
		$model->setState('list.ordering', 'start_date');

		$model->setState('filter.expand', false);

		// In some cases we need to increase the memory limit as we try to fetch
		// all events, then uncomment the following line.
		// ini_set('memory_limit', '512M');

		$items = $model->getItems();

		if (! is_array($items))
		{
			$items = array();
		}

		return self::createIcalFromEvents($items, $asDownload);
	}

	public static function createIcalFromEvents ($events, $asDownload = false)
	{
		$text = array();
		$text[] = 'BEGIN:VCALENDAR';
		$text[] = 'VERSION:2.0';
		$text[] = 'PRODID:DPCALENDAR';
		$text[] = 'CALSCALE:GREGORIAN';

		$calendars = array();
		foreach ($events as $event)
		{
			if (key_exists($event->catid, $calendars))
			{
				continue;
			}
			if (! empty($event->category_title))
			{
				$calendars[$event->catid] = $event->category_title;
			}
			else
			{
				$calendars[$event->catid] = DPCalendarHelper::getCalendar($event->catid)->title;
			}
		}
		// $text[] = 'X-WR-CALNAME:'.implode('; ', $calendars);
		$text[] = 'X-WR-TIMEZONE:UTC';

		$now = DPCalendarHelper::getDate()->format('Ymd\THis\Z');
		foreach ($events as $event)
		{
			$text[] = 'BEGIN:VEVENT';
			if ($event->all_day == 1)
			{
				$text[] = 'DTSTART;VALUE=DATE:' . DPCalendarHelper::getDate($event->start_date, $event->all_day)->format('Ymd');
				$end = DPCalendarHelper::getDate($event->end_date, $event->all_day);
				$end->modify('+1 day');
				$text[] = 'DTEND;VALUE=DATE:' . $end->format('Ymd');
			}
			else
			{
				$text[] = 'DTSTART:' . DPCalendarHelper::getDate($event->start_date, $event->all_day)->format('Ymd\THis\Z');
				$text[] = 'DTEND:' . DPCalendarHelper::getDate($event->end_date, $event->all_day)->format('Ymd\THis\Z');
			}

			if (! empty($event->rrule))
			{
				$text[] = 'RRULE:' . $event->rrule;
			}

			if (isset($event->uid))
			{
				$text[] = 'UID:' . $event->uid;
			}
			else if (! empty($event->original_id) && $event->original_id != - 1)
			{
				$text[] = 'UID:' . md5($event->original_id . '_DPCalendar');
			}
			else if (! empty($event->id))
			{
				$text[] = 'UID:' . md5($event->id . '_DPCalendar');
			}
			else
			{
				$text[] = 'UID:' . md5(uniqid() . '_DPCalendar');
			}

			if (! empty($event->original_id) && $event->original_id != - 1)
			{
				if (strlen($event->recurrence_id) <= 8)
				{
					$text[] = 'RECURRENCE-ID;VALUE=DATE:' . $event->recurrence_id;
				}
				else
				{
					$text[] = 'RECURRENCE-ID:' . $event->recurrence_id;
				}
			}

			$text[] = 'SUMMARY:' . $event->title;
			$text[] = 'DTSTAMP:' . DPCalendarHelper::getDate($event->created)->format('Ymd\THis\Z');
			$text[] = 'DESCRIPTION:' . JFilterInput::getInstance()->clean(preg_replace('/\r\n?/', "\N", $event->description));
			$text[] = 'X-ALT-DESC;FMTTYPE=text/html:' . preg_replace('/\r\n?/', "", $event->description);

			if (isset($event->locations) && ! empty($event->locations))
			{
				$text[] = 'LOCATION:' . DPCalendarHelperLocation::format($event->locations);
				if (! empty($event->locations[0]->latitude) && ! empty($event->locations[0]->longitude))
				{
					$text[] = 'GEO:' . $event->locations[0]->latitude . ';' . $event->locations[0]->longitude;
				}
			}

			$text[] = 'X-ACCESS:' . $event->access;
			$text[] = 'X-MODIFIED:' . DPCalendarHelper::getDate($event->modified)->format('Ymd\THis\Z');
			$text[] = 'X-HITS:' . $event->hits;
			$text[] = 'X-URL:' . $event->url;
			$text[] = 'X-COLOR:' . $event->color;

			$text[] = 'END:VEVENT';
		}
		$text[] = 'END:VCALENDAR';

		if ($asDownload)
		{
			header('Content-Type: text/calendar; charset=utf-8');
			header('Content-disposition: attachment; filename="' . DPCalendarHelper::getDate()->format('YmdHis') . '.ics"');

			echo implode(PHP_EOL, $text);
			JFactory::getApplication()->close();
		}
		else
		{
			return implode(PHP_EOL, $text);
		}
	}
}
