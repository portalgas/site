<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar');

$document = JFactory::getDocument();
$document->setMimeEncoding('application/json');

$data = array();
foreach ($this->items as $event)
{
	$description = DPCalendarHelper::renderEvents(array(
			$event
	),
			'{{#events}}{{title}}<br>{{date}}<br>{{{description}}}<hr id="dp-popup-window-divider"/>{{#canAttend}}<a href="{{attendLink}}">' .
					 (isset($event->attending) && $event->attending !== null ? JText::_('COM_DPCALENDAR_ATTENDING') : JText::_('COM_DPCALENDAR_ATTEND')) .
					 '</a> {{/canAttend}}{{#canEdit}}<a href="{{editLink}}">' . JText::_('JACTION_EDIT') .
					 '</a> {{/canEdit}}{{#canDelete}}<a href="{{deleteLink}}">' . JText::_('JACTION_DELETE') . '</a>{{/canDelete}}{{/events}}',
					$this->params);

	$locations = array();
	if (! empty($event->locations))
	{
		foreach ($event->locations as $location)
		{
			$locations[] = array(
					'location' => DPCalendarHelperLocation::format($location),
					'latitude' => $location->latitude,
					'longitude' => $location->longitude
			);
		}
	}
	$data[] = array(
			'id' => $event->id,
			'title' => $this->compactMode == 0 ? htmlspecialchars_decode($event->title) : utf8_encode(chr(160)),
			'start' => DPCalendarHelper::getDate($event->start_date, $event->all_day)->format('c', true),
			'end' => DPCalendarHelper::getDate($event->end_date, $event->all_day)->format('c', true),
			'url' => DPCalendarHelper::getEventRoute($event->id, $event->catid),
			'editable' => JFactory::getUser()->authorise('core.edit', 'com_dpcalendar.category.' . $event->catid),
			'color' => '#' . $event->color,
			'allDay' => $this->compactMode == 0 ? (bool) $event->all_day : true,
			'description' => $description,
			'location' => $locations
	);
}
ob_clean();
echo json_encode($data);
JFactory::getApplication()->close();
