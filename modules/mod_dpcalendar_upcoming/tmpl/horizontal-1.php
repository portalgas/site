<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'modules/mod_dpcalendar_upcoming/tmpl/horizontal-1.css');

ob_start();
?>

{{#events}}{{#header}}
<p style="clear: both;">
    <strong>{{header}}</strong>
</p>
{{/header}}
<div class="dp-upcoming-event-width">
<div>
  <div itemprop="event" itemscope itemtype="http://schema.org/Event">
    <p style="clear: both;" />
    <div class="dp-upcoming-calendar">
        <div class="dp-upcoming-calendar-background"
            style="background-color: #{{color}}"></div>
        <div class="dp-upcoming-text-month">{{month}}</div>
        <div class="dp-upcoming-text-day" style="color: #{{color}}">{{day}}</div>
    </div>
    <div itemprop="startDate" content="{{startDateIso}}">
        {{date}}<br />
        <a href="/{{{backLink}}}" itemprop="url"><span itemprop="name">{{title}}</span></a>
        <meta itemprop="location" content="{{#location}}{{full}} {{/location}}" />
    <p style="clear: both;" />
    </div>
  </div>
</div>
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
