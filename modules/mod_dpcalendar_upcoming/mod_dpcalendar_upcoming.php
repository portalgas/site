<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR);
if (! class_exists('DPCalendarHelper'))
{
	return;
}

JLoader::import('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_SITE . DS . 'components' . DS . 'com_dpcalendar' . DS . 'models', 'DPCalendarModel');

$model = JModelLegacy::getInstance('Calendar', 'DPCalendarModel');
$model->getState();
$model->setState('filter.parentIds', $params->get('ids', array(
		'root'
)));

$startDate = DPCalendarHelper::getDate(trim($params->get('start_date', '')));

// Round to the last quater
$startDate->sub(new DateInterval("PT" . $startDate->format("s") . "S"));
$startDate->sub(new DateInterval("PT" . ($startDate->format("i") % 15) . "M"));

$startDate = $startDate->format('U');

$endDate = trim($params->get('end_date', ''));
if ($endDate)
{
	$endDate = DPCalendarHelper::getDate($endDate)->format('U');
}
else
{
	$endDate = null;
}

$ids = array();
foreach ($model->getItems() as $calendar)
{
	$ids[] = $calendar->id;
}

$model = JModelLegacy::getInstance('Events', 'DPCalendarModel', array(
		'ignore_request' => true
));
$model->getState();
$model->setState('list.limit', $params->get('max_events', 5));
$model->setState('list.direction', $params->get('order', 'asc'));
$model->setState('category.id', $ids);
$model->setState('category.recursive', true);
$model->setState('filter.search', $params->get('filter', ''));
$model->setState('filter.ongoing', $params->get('ongoing', 0));
$model->setState('filter.expand', true);
$model->setState('filter.state', 1);
$model->setState('filter.language', JFactory::getLanguage());
$model->setState('list.start-date', $startDate);
$model->setState('list.end-date', $endDate);

$events = $model->getItems();

require JModuleHelper::getLayoutPath('mod_dpcalendar_upcoming', $params->get('layout', 'default'));
