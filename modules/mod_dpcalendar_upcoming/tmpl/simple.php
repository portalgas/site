<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'modules/mod_dpcalendar_upcoming/tmpl/simple.css');

ob_start();
?>

{{#events}}{{#header}}
<p class="dp-upcoming-header" style="clear: both;">
	<strong>{{startDate}}</strong>
</p>
{{/header}}
<div class="dp-upcoming-group" style="border-color:#{{color}}"itemprop="event" itemscope itemtype="http://schema.org/Event">
	<div style="clear: both;" /></div>
	<p itemprop="startDate" content="{{startDateIso}}">
      {{#startTime}}{{startTime}} {{dateSeparator}} {{endTime}}<br />{{/startTime}}
		<a href="{{{backLink}}}" itemprop="url"><span itemprop="name">{{title}}</span></a>
	</p>
	<meta itemprop="location" content="{{#location}}{{full}} {{/location}}" />
	<p style="clear: both;" />
</div>
{{/events}}
{{^events}}
{{emptyText}}
{{/events}}

<?php
$output = ob_get_contents();
ob_end_clean();

$tmp = clone JComponentHelper::getParams('com_dpcalendar');
$tmp->set('event_date_format', $params->get('date_format', $tmp->get('event_date_format')));
$tmp->set('event_time_format', $params->get('time_format', $tmp->get('event_time_format')));
$tmp->set('grouping', $params->get('output_grouping', ''));

echo DPCalendarHelper::renderEvents($events, $output, $tmp);
