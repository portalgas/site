<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
$params = $this->params;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<link rel='stylesheet' type='text/css' href='<?php echo JURI::base()?>components/com_dpcalendar/libraries/fullcalendar/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='<?php echo JURI::base()?>components/com_dpcalendar/views/calendar/tmpl/dpcalendar.css' />
<link rel='stylesheet' type='text/css' href='<?php echo JURI::base()?>components/com_dpcalendar/libraries/jquery/themes/bootstrap/jquery-ui.custom.css' />

<style type='text/css'>
body {
	text-align: center;
	font-size: 14px;
	font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
	-webkit-print-color-adjust:exact;
}
#dpcalendar_component, #dpcalendar_component_map, #dpcalendar_view_list {
	width: 900px;
	margin: 0 auto;
}
#dpcalendar_component{
	margin-bottom: 10px;
}
</style>

<script type='text/javascript' src='<?php echo JURI::base()?>components/com_dpcalendar/libraries/jquery/jquery.min.js'></script>
<script type='text/javascript' src='<?php echo JURI::base()?>components/com_dpcalendar/libraries/jquery/dpcalendar/dpNoConflict.js'></script>
<script type='text/javascript' src='<?php echo JURI::base()?>components/com_dpcalendar/views/calendar/tmpl/dpcalendar.js'></script>
<script type='text/javascript' src='<?php echo JURI::base()?>components/com_dpcalendar/libraries/fullcalendar/fullcalendar.min.js'></script>
<script type='text/javascript' src='<?php echo JURI::base()?>components/com_dpcalendar/libraries/jquery/ui/jquery-ui.custom.min.js'></script>

<?php if ($params->get('show_map', 1) == 1)
{?>
<script type='text/javascript' src='<?php echo (JBrowser::getInstance()->isSSLConnection() ? "https" : "http")?>://maps.googleapis.com/maps/api/js?sensor=true&language=<?php echo DPCalendarHelper::getGoogleLanguage()?>'></script>
<script type='text/javascript' src='<?php echo (JBrowser::getInstance()->isSSLConnection() ? "https" : "http")?>://j.maxmind.com/app/geoip.js'></script>
<?php
}
$calsSources = "		eventSources: [\n";
foreach ($this->selectedCalendars as $calendar)
{
	$value = html_entity_decode(JRoute::_('index.php?option=com_dpcalendar&view=events&limit=0&format=raw&ids=' . $calendar . '&my=' . $params->get('show_my_only_calendar', '0') . '&Itemid=' . JRequest::getVar('Itemid', 0)));
	$calsSources .= "				'" . $value . "',\n";
}
$calsSources = trim($calsSources, ",\n");
$calsSources .= "	],\n";

$defaultView = $params->get('defaultView', 'month');
if ($params->get('defaultView', 'month') == 'week')
{
	$defaultView = 'agendaWeek';
}
else if ($params->get('defaultView', 'month') == 'day')
{
	$defaultView = 'agendaDay';
}
$daysLong = "[";
$daysShort = "[";
$daysMin = "[";
$monthsLong = "[";
$monthsShort = "[";
for ($i = 0; $i < 7; $i++)
{
	$daysLong .= "'" . htmlspecialchars(DPCalendarHelper::dayToString($i, false), ENT_QUOTES) . "'";
	$daysShort .= "'" . htmlspecialchars(DPCalendarHelper::dayToString($i, true), ENT_QUOTES) . "'";
	$daysMin .= "'" . htmlspecialchars(mb_substr(DPCalendarHelper::dayToString($i, true), 0, 2), ENT_QUOTES) . "'";
	if ($i < 6)
	{
		$daysLong .= ",";
		$daysShort .= ",";
		$daysMin .= ",";
	}
}
for ($i = 1; $i <= 12; $i++)
{
	$monthsLong .= "'" . htmlspecialchars(DPCalendarHelper::monthToString($i, false), ENT_QUOTES) . "'";
	$monthsShort .= "'" . htmlspecialchars(DPCalendarHelper::monthToString($i, true), ENT_QUOTES) . "'";
	if ($i < 12)
	{
		$monthsLong .= ",";
		$monthsShort .= ",";
	}
}
$daysLong .= "]";
$daysShort .= "]";
$daysMin .= "]";
$monthsLong .= "]";
$monthsShort .= "]";

$calCode = "// <![CDATA[ \n";
$calCode .= "dpjQuery(document).ready(function(){\n";
$calCode .= "	var today = new Date();\n";
$calCode .= "	var tmpYear = today.getFullYear();\n";
$calCode .= "	var tmpMonth = today.getMonth();\n";
$calCode .= "	var tmpDay = today.getDate();\n";
$calCode .= "	var tmpView = '" . $defaultView . "';\n";
$calCode .= "	var vars = window.location.hash.replace(/&amp;/gi, \"&\").split(\"&\");\n";
$calCode .= "	for ( var i = 0; i < vars.length; i++ ){\n";
$calCode .= "		if(vars[i].match(\"^#year\"))tmpYear = vars[i].substring(6);\n";
$calCode .= "		if(vars[i].match(\"^month\"))tmpMonth = vars[i].substring(6)-1;\n";
$calCode .= "		if(vars[i].match(\"^day\"))tmpDay = vars[i].substring(4);\n";
$calCode .= "		if(vars[i].match(\"^view\"))tmpView = vars[i].substring(5);\n";
$calCode .= "	}\n";
$calCode .= "	dpjQuery('#dpcalendar_component').fullCalendar({\n";
$calCode .= "		header: {\n";
$calCode .= "			left: '',\n";
$calCode .= "			center: 'title',\n";
$calCode .= "			right: ''\n";
$calCode .= "		},\n";
$calCode .= "		year: tmpYear,\n";
$calCode .= "		month: tmpMonth,\n";
$calCode .= "		date: tmpDay,\n";
$calCode .= "		defaultView: tmpView,\n";
$calCode .= "		weekMode: 'liquid',\n";
$calCode .= "		theme: false,\n";
$calCode .= "		weekends: " . ($params->get('weekend', 1) == 1 ? 'true' : 'false') . ",\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "			month: '" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_month', 'F Y')) . "',\n";
$calCode .= "			week: \"" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_week', "M j[ Y]{ '&#8212;'[ M] j o}")) . "\",\n";
$calCode .= "			day: '" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_day', 'l, M j, Y')) . "',\n";
$calCode .= "			list: '" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_list', 'M j Y')) . "'},\n";
$calCode .= "		firstDay: " . $params->get('weekstart', 0) . ",\n";
$calCode .= "		firstHour: " . $params->get('first_hour', 6) . ",\n";
$calCode .= "		maxTime: " . $params->get('max_time', 24) . ",\n";
$calCode .= "		minTime: " . $params->get('min_time', 0) . ",\n";
$calCode .= "		weekNumbers: " . ($params->get('weeknumbers', 1) == 1 ? 'true' : 'false') . ",\n";
$calCode .= "		monthNames: " . $monthsLong . ",\n";
$calCode .= "		monthNamesShort: " . $monthsShort . ",\n";
$calCode .= "		dayNames: " . $daysLong . ",\n";
$calCode .= "		dayNamesShort: " . $daysShort . ",\n";
if ($params->get('calendar_height', 0) > 0)
{
	$calCode .= "		contentHeight: " . $params->get('calendar_height', 0) . ",\n";
}
$calCode .= "		dayNamesShort: " . $daysShort . ",\n";
$calCode .= "		startParam: 'date-start',\n";
$calCode .= "		endParam: 'date-end',\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "			month: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_month', 'g:i a{ - g:i a}')) . "',\n";
$calCode .= "			week: \"" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_week', "g:i a{ - g:i a}")) . "\",\n";
$calCode .= "			day: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_day', 'g:i a{ - g:i a}')) . "',\n";
$calCode .= "			list: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_list', 'g:i a{ - g:i a}')) . "'},\n";
$calCode .= "		columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "		axisFormat: '" . DPFullcalendar::convertFromPHPDate($params->get('axisformat', 'g:i a')) . "',\n";
$calCode .= "		allDayText: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_ALL_DAY'), ENT_QUOTES) . "',\n";
$calCode .= "			buttonText: {\n";
$calCode .= "			today:    '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY'), ENT_QUOTES) . "',\n";
$calCode .= "			month:    '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_MONTH'), ENT_QUOTES) . "',\n";
$calCode .= "			week:     '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_WEEK'), ENT_QUOTES) . "',\n";
$calCode .= "			day:      '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_DAY'), ENT_QUOTES) . "',\n";
$calCode .= "			list:     '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_LIST'), ENT_QUOTES) . "'\n";
$calCode .= "		},\n";
$calCode .= "		listSections: 'smart',\n";
$calCode .= "		listRange: 30,\n";
$calCode .= "		listPage: 30,\n";
$calCode .= "		listTexts: {
						until: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_UNTIL'), ENT_QUOTES) . "',
						past: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_PAST'), ENT_QUOTES) . "',
						today: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TODAY'), ENT_QUOTES) . "',
						tomorrow: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TOMORROW'), ENT_QUOTES) . "',
						thisWeek: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_WEEK'), ENT_QUOTES) . "',
						nextWeek: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_WEEK'), ENT_QUOTES) . "',
						thisMonth: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_MONTH'), ENT_QUOTES) . "',
						nextMonth: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_MONTH'), ENT_QUOTES) . "',
						future: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_FUTURE'), ENT_QUOTES) . "',
						week: '" . htmlspecialchars(JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_WEEK'), ENT_QUOTES) . "'
					},\n";
$calCode .= $calsSources;
$calCode .= "		viewRender: function(view) {\n";
$calCode .= "			var d = dpjQuery('#dpcalendar_component').fullCalendar('getDate');\n";
$calCode .= "			var newHash = 'year='+d.getFullYear()+'&month='+(d.getMonth()+1)+'&day='+d.getDate()+'&view='+view.name;\n";
$calCode .= "			if(window.location.hash.replace(/&amp;/gi, \"&\") != newHash)\n";
$calCode .= "			window.location.hash = newHash;\n";
if ($params->get('show_map', 1) == 1)
{
	$calCode .= "			if(dpcalendarMapMarkers != null){for (var i = 0; i < dpcalendarMapMarkers.length; i++ ) { dpcalendarMapMarkers[i].setMap(null);}}\n";
	$calCode .= "			dpcalendarMapMarkers = [];\n";
}
$calCode .= "		},\n";
$calCode .= "		eventRender: function(event, element) {\n";
if ($params->get('show_map', 1) == 1)
{
	$calCode .= "			dpjQuery.each(event.location, function(i, loc) {\n";
	$calCode .= "				if(loc.location == undefined || loc.location == '' || loc.location == null) return;\n";
	$calCode .= "				var l = new google.maps.LatLng(loc.latitude, loc.longitude);\n";
	$calCode .= "				var marker = new google.maps.Marker({position: l, map: dpcalendarMap, title: loc.location});\n";
 	$calCode .= "				dpcalendarMapMarkers.push(marker);\n";
 	$calCode .= "				var infowindow = new google.maps.InfoWindow({content: event.description});\n";
 	$calCode .= "				google.maps.event.addListener(marker, 'click', function() {infowindow.open(dpcalendarMap, marker);});\n";
 	$calCode .= "				dpcalendarMapBounds.extend(l); dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());\n";
	$calCode .= "			});\n";
}
$calCode .= "		},\n";
$calCode .= "		eventClick: function(event, jsEvent, view) {\n";
$calCode .= "		        return false;\n";
$calCode .= "		},\n";

$calCode .= "		dayClick: function(date, allDay, jsEvent, view) {\n";
$calCode .= "		},\n";
$calCode .= "	});\n";
if ($params->get('show_selection', 1) == 1)
{
	$calCode .= "dpjQuery('#dpcalendar_view_list').hide();\n";
}
if ($params->get('show_map', 1) == 1)
{
	$calCode .= "			var dpcalendarMap = new google.maps.Map(document.getElementById('dpcalendar_component_map'), {zoom: " . $params->get('map_zoom', 4) . ", mapTypeId: google.maps.MapTypeId.ROADMAP, center: new google.maps.LatLng(geoip_latitude(), geoip_longitude())});\n";
	$calCode .= "			var dpcalendarMapBounds = new google.maps.LatLngBounds();\n";
	$calCode .= "			var dpcalendarMapMarkers = [];\n";
}
$calCode .= "});\n";
$calCode .= "// ]]>\n";

?>
<script type='text/javascript'><?php echo $calCode?></script>
</head>
<body>
<?php
if ($params->get('show_page_heading', 1))
{ ?>
	<h1>
	<?php echo $this->escape($params->get('page_heading')); ?>
	</h1>
<?php
}

echo JHTML::_('content.prepare', $params->get('textbefore'));
if ($params->get('show_selection', 1) == 1 || $params->get('show_selection', 1) == 3)
{
	$calendar_list = '<div id="dpcalendar_view_list"><table class="dpcalendar-table">';
	foreach ($this->items as $calendar)
	{
		$value = html_entity_decode(JRoute::_('index.php?option=com_dpcalendar&view=events&limit=0&format=raw&ids=' . $calendar->id . '&my=' . $params->get('show_my_only_calendar', '0') . '&Itemid=' . JRequest::getVar('Itemid', 0)));
		$checked = '';
		if (empty($this->selectedCalendars) || in_array($calendar->id, $this->selectedCalendars))
		{
			$checked = 'checked="checked"';
		}

		$calendar_list .= "<tr>\n";
		$calendar_list .= "<td><input type=\"checkbox\" name=\"" . $calendar->id . "\" value=\"" . $value . "\" " . $checked . " onclick=\"updateDPCalendarFrame(this)\"/></td>\n";
		$calendar_list .= "<td class=\"dp-list-row\"><font color=\"" . $calendar->color . "\">" . str_pad(' ' . $calendar->title, strlen(' ' . $calendar->title) + $calendar->level - 1, '-', STR_PAD_LEFT) . '</font> &nbsp;[ <a href="' . DPCalendarHelper::getCalendarIcalRoute($calendar->id) . '">' . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_ICAL') . '</a> ]</td><td>' . $calendar->description . "</td></tr>\n";
	}
	$calendar_list .= "</table></div>\n";
	echo $calendar_list;
	echo "<div align=\"center\" style=\"text-align:center\">\n";
	$image = JURI::base() . 'media/com_dpcalendar/images/site/down.png';
	if ($params->get('show_selection', 1) == 3)
	{
		$image = JURI::base() . 'media/com_dpcalendar/images/site/up.png';
	}
	echo "<img id=\"dpcalendar_view_toggle_status\" name=\"dpcalendar_view_toggle_status\" src=\"" . $image . "\" alt=\"" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_CALENDAR_LIST') . "\" title=\"" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_CALENDAR_LIST') . "\"/>\n";
	echo "</div>\n";
}

echo "<div id='dpcalendar_component'></div><div id='dpcalendar_component_popup' style=\"visibility:hidden\" ></div>";
if ($params->get('show_map', 1) == 1)
{
	echo "<div id='dpcalendar_component_map' style='height:" . $params->get('map_height', '350px') . "'></div>";
}

echo JHTML::_('content.prepare', $params->get('textafter'));
?>
</body>
</html>
