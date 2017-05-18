<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.controller');

class DPCalendarControllerIcal extends JControllerLegacy
{

	public function download ()
	{
		$calendars = array(
				JRequest::getCmd('id')
		);
		$calendar = DPCalendarHelper::getCalendar(JRequest::getCmd('id'));
		if (method_exists($calendar, 'getChildren'))
		{
			$childrens = $calendar->getChildren();
			if ($childrens)
			{
				foreach ($childrens as $c)
				{
					$calendars[] = $c->id;
				}
			}
		}
		DPCalendarHelperIcal::createIcalFromCalendar($calendars, true);
		return true;
	}
}
