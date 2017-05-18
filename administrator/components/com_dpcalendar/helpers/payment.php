<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.helper');
JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'tables');

class DPCalendarHelperPayment
{

	/**
	 * Get Price to Attend the Event
	 *
	 * @param DPCalendarTableEvent $event
	 *        	Event Data
	 * @param Object $payment
	 *        	Payment Data
	 *
	 * @return Object Price for attending.
	 */
	public static function getPrice ($event, $attendee = null)
	{
		if (! $event)
		{
			return false;
		}
		if (@$attendee->state == 1)
		{
			$sprice = $attendee->price;
		}
		else
		{
			$sprice = $event->price;
		}

		$price = $sprice;
		$events = '';

		if ($event->original_id != 0)
		{
			$model = JModelLegacy::getInstance('Events', 'DPCalendarModel');
			if (! $model)
			{
				$model = JModelLegacy::getInstance('AdminEvents', 'DPCalendarModel');
			}
			$model->getState();
			if ($event->original_id > 0)
			{
				$model->setState('filter.children', $event->original_id);
				$model->setState('list.limit', 10000);
			}
			else
			{
				$model->setState('filter.children', $event->id);
				$model->setState('list.limit', 10000);
			}

			$series = $model->getItems();
			$price = 0.00;
			$i = 1;
			$count = count($series);
			foreach ($series as $event)
			{
				if ($i != $count && $i < $count)
				{
					$events = $events . $event->id . ',';
				}
				else
				{
					$events = $events . $event->id;
				}
				$price = $event->price + $price;
				$i ++;
			}
		}
		$return = new stdClass();
		$return->single = $sprice;
		$return->series = number_format($price, 2, '.', '');
		$return->tax_amount = '0.00';
		$return->events = $events;

		return $return;
	}

	public static function paymentRequired ($event)
	{
		if (empty($event))
		{
			return false;
		}
		return $event->price != '0.00' && ! empty($event->price);
	}
}
