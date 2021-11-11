<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$params = $this->params;

if ($params->get('show_page_heading', 1))
{ ?>
	<h1>
	<?php echo $this->escape($params->get('page_heading')); ?>
	</h1>
<?php
}

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
$document = JFactory::getDocument();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'bootstrap' => true, 'dpcalendar' => true, 'fullcalendar' => true, 'datepicker' => true));

if ($params->get('show_map', 1) == 1)
{
	DPCalendarHelper::loadLibrary(array('maps' => true));
}
$document->addScript(JURI::root() . 'components/com_dpcalendar/views/calendar/tmpl/dpcalendar.js');
$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/calendar/tmpl/dpcalendar.css');

$canAdd = DPCalendarHelper::canCreateEvent();

$calsSources = "		eventSources: [\n";
foreach ($this->selectedCalendars as $calendar)
{
	$value = html_entity_decode(JRoute::_('index.php?option=com_dpcalendar&view=events&format=raw&limit=0&ids=' .
			$calendar . '&my=' . $params->get('show_my_only_calendar', '0') . '&Itemid=' . JRequest::getInt('Itemid', 0)));
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
	$daysLong .= "'" . DPCalendarHelper::dayToString($i, false) . "'";
	$daysShort .= "'" . DPCalendarHelper::dayToString($i, true) . "'";
	$daysMin .= "'" . mb_substr(DPCalendarHelper::dayToString($i, true), 0, 2) . "'";
	if ($i < 6)
	{
		$daysLong .= ",";
		$daysShort .= ",";
		$daysMin .= ",";
	}
}
for ($i = 1; $i <= 12; $i++)
{
	$monthsLong .= "'" . DPCalendarHelper::monthToString($i, false) . "'";
	$monthsShort .= "'" . DPCalendarHelper::monthToString($i, true) . "'";
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
$calCode .= "	if (dpjQuery(document).width() < 500) {tmpView = 'list';}\n";
$calCode .= "	dpjQuery('#dpcalendar_component').fullCalendar({\n";
$calCode .= "		header: {\n";
$calCode .= "			left: 'prev,next ',\n";
$calCode .= "			center: 'title',\n";
$calCode .= "			right: 'month,agendaWeek,agendaDay,list'\n";
$calCode .= "		},\n";
$calCode .= "		year: tmpYear,\n";
$calCode .= "		month: tmpMonth,\n";
$calCode .= "		date: tmpDay,\n";
$calCode .= "		defaultView: tmpView,\n";
$calCode .= "		weekNumbers: " . ($params->get('week_numbers', 0) == 1 ? 'true' : 'false') . ",\n";
$calCode .= "		weekNumberTitle: '',\n";
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
$calCode .= "		weekMode: '" . $params->get('week_mode', 'fixed') . "',\n";
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
$calCode .= "		slotEventOverlap: " . ($params->get('overlap_events', 1) == 1 ? 'true' : 'false') . ",\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "			month: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_month', 'g:i a{ - g:i a}')) . "',\n";
$calCode .= "			week: \"" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_week', "g:i a{ - g:i a}")) . "\",\n";
$calCode .= "			day: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_day', 'g:i a{ - g:i a}')) . "',\n";
$calCode .= "			list: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_list', 'g:i a{ - g:i a}')) . "'},\n";
$calCode .= "		columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "		axisFormat: '" . DPFullcalendar::convertFromPHPDate($params->get('axisformat', 'g:i a')) . "',\n";
$calCode .= "		allDayText: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_ALL_DAY', true) . "',\n";
$calCode .= "			buttonText: {\n";
$calCode .= "			today:    '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY', true) . "',\n";
$calCode .= "			month:    '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_MONTH', true) . "',\n";
$calCode .= "			week:     '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_WEEK', true) . "',\n";
$calCode .= "			day:      '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_DAY', true) . "',\n";
$calCode .= "			list:     '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_LIST', true) . "'\n";
$calCode .= "		},\n";
$calCode .= "		listSections: 'smart',\n";
$calCode .= "		listRange: 30,\n";
$calCode .= "		listPage: 30,\n";
$calCode .= "		listTexts: {
						until: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_UNTIL', true) . "',
						past: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_PAST', true) . "',
						today: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TODAY', true) . "',
						tomorrow: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TOMORROW', true) . "',
						thisWeek: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_WEEK', true) . "',
						nextWeek: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_WEEK', true) . "',
						thisMonth: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_MONTH', true) . "',
						nextMonth: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_MONTH', true) . "',
						future: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_FUTURE', true) . "',
						week: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_WEEK', true) . "'
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
$calCode .= "			if (event.description){\n";
$calCode .= "				element.tooltip({html: true, title: dpEncode(event.description), delay: { show: 100, hide: event.description.indexOf('task=event.edit') > -1 || event.description.indexOf('task=event.delete') > -1 ? 1500 : 200 }, container: '#dpcalendar_component'});}\n";
if ($params->get('show_map', 1) == 1)
{
	$chartUrl = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
	if (JBrowser::getInstance()->isSSLConnection()) $chartUrl = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
	$calCode .= "			dpjQuery.each(event.location, function(i, loc) {\n";
	$calCode .= "				if(loc.location == undefined || loc.location == '' || loc.location == null) return;\n";
	$calCode .= "				var l = new google.maps.LatLng(loc.latitude, loc.longitude);\n";
	$calCode .= "				var pinImage = new google.maps.MarkerImage('" . $chartUrl . "' + event.color.replace('#', ''),\n";
    $calCode .= "				new google.maps.Size(21, 34),\n";
    $calCode .= "				new google.maps.Point(0,0),\n";
    $calCode .= "				new google.maps.Point(10, 34));\n";
	$calCode .= "				var marker = new google.maps.Marker({position: l, map: dpcalendarMap, title: loc.location, icon: pinImage});\n";
 	$calCode .= "				dpcalendarMapMarkers.push(marker);\n";
 	$calCode .= "				var infowindow = new google.maps.InfoWindow({content: event.description});\n";
 	$calCode .= "				google.maps.event.addListener(marker, 'click', function() {infowindow.open(dpcalendarMap, marker);});\n";
 	$calCode .= "				dpcalendarMapBounds.extend(l); dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());\n";
	$calCode .= "			});\n";
}
$calCode .= "		},\n";
$calCode .= "		eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view){\n";
$calCode .= "			dpjQuery('#dpcalendar_component_loading').show();dpjQuery(jsEvent.target).tooltip('hide');\n";
$calCode .= "			dpjQuery.ajax({\n";
$calCode .= "				type: 'POST',\n";
$calCode .= "				url: 'index.php?option=com_dpcalendar&task=event.move',\n";
$calCode .= "				data: {id: event.id, days:  dayDelta, minutes:  minuteDelta, allDay: allDay},\n";
$calCode .= "				success: function (data) {\n";
$calCode .= "					dpjQuery('#dpcalendar_component_loading').hide();\n";
$calCode .= "					var json = dpjQuery.parseJSON(data);\n";
$calCode .= "					if (json.data.url) event.url = json.data.url;\n";
$calCode .= "					Joomla.renderMessages(json.messages);\n";
$calCode .= "				}\n";
$calCode .= "			});\n";
$calCode .= "		},\n";
$calCode .= "		eventResize: function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view){\n";
$calCode .= "			dpjQuery('#dpcalendar_component_loading').show();dpjQuery(jsEvent.target).tooltip('hide');\n";
$calCode .= "			dpjQuery.ajax({\n";
$calCode .= "				type: 'POST',\n";
$calCode .= "				url: 'index.php?option=com_dpcalendar&task=event.move',\n";
$calCode .= "				data: {id: event.id, days:  dayDelta, minutes:  minuteDelta, allDay: false, onlyEnd: true},\n";
$calCode .= "				success: function (data) {\n";
$calCode .= "					dpjQuery('#dpcalendar_component_loading').hide();\n";
$calCode .= "					var json = dpjQuery.parseJSON(data);\n";
$calCode .= "					if (json.data.url) event.url = json.data.url;\n";
$calCode .= "					Joomla.renderMessages(json.messages);\n";
$calCode .= "				}\n";
$calCode .= "			});\n";
$calCode .= "		},\n";

$calCode .= "		eventClick: function(event, jsEvent, view) {\n";
if ($params->get('show_event_as_popup', 1) == 1)
{
 	$calCode .= "		        if (dpjQuery(window).width() < 600) {window.location = dpEncode(event.url); return false;}\n";
 	$calCode .= "		        dpjQuery('#dpc-event-view').on('show', function () {\n";
 	$calCode .= "		            var url = new Url(event.url);\n";
 	$calCode .= "		            url.query.tmpl = 'component';\n";
 	$calCode .= "		            dpjQuery('#dpc-event-view iframe').attr('src', url.toString());\n";
 	$calCode .= "		        });\n";
 	$calCode .= "		        dpjQuery('#dpc-event-view').on('hide', function () {\n";
	if (DPCalendarHelper::isJoomlaVersion('3'))
	{
		$calCode .= "		           if(dpjQuery('#dpc-event-view iframe').contents().find('#system-message').children().length > 0){dpjQuery('#dpcalendar_component').fullCalendar('refetchEvents');}\n";
	}
	if (DPCalendarHelper::isJoomlaVersion('2.5'))
	{
		$calCode .= "		           if(dpjQuery('#dpc-event-view iframe').contents().find('#system-message-container').children().length > 0){dpjQuery('#dpcalendar_component').fullCalendar('refetchEvents');}\n";
	}
 	$calCode .= "		            dpjQuery('#dpc-event-view iframe').removeAttr('src');\n";
 	$calCode .= "		        });\n";
	$calCode .= "		        dpjQuery('#dpc-event-view').modal();\n";
	$calCode .= "		        return false;\n";
} else {
	$calCode .= "		        window.location = dpEncode(event.url); return false;\n";
}
$calCode .= "		},\n";

$calCode .= "		dayClick: function(date, allDay, jsEvent, view) {\n";
if ($canAdd)
{
	$calCode .= "	 if (dpjQuery(window).width() < 600) {dpjQuery('#task').val(''); dpjQuery('#editEventForm').submit(); return false;}\n";
	$calCode .= "    jsEvent.stopPropagation();\n";
	$calCode .= "    if (view.name == 'month') date.setHours(8);\n";
	$calCode .= "    dpjQuery('#jform_start_date').datepicker('setDate', date);\n";
	$calCode .= "    dpjQuery('#jform_start_date_time').timepicker('setTime', date);\n";
	$calCode .= "    dpjQuery('#jform_end_date').datepicker('setDate', date);\n";
	$calCode .= "    date.setHours(date.getHours()+1);\n";
	$calCode .= "    dpjQuery('#jform_end_date_time').timepicker('setTime', date);\n";
	$calCode .= "    var p = dpjQuery('#dpcalendar_component').parents().filter(function() {\n";
	$calCode .= "    	var parent = dpjQuery(this);\n";
	$calCode .= "    	return parent.is('body') || parent.css('position') == 'relative';\n";
	$calCode .= "    }).slice(0,1).offset();\n";
	if ($params->get('event_edit_popup', 1) == 1)
	{
	   $calCode .= "    dpjQuery('#editEventForm').css({top: jsEvent.pageY-p.top, left: jsEvent.pageX-160-p.left}).show();\n";
	}
	else
	{
	    $calCode .= "    dpjQuery('#task').val('');\n";
	    $calCode .= "    dpjQuery('#editEventForm').submit();\n";
	}
	$calCode .= "    dpjQuery('#jform_title').focus();\n";
} else {
	$calCode .= "dpjQuery('#dpcalendar_component').fullCalendar('gotoDate', date).fullCalendar('changeView', 'agendaDay');\n";
}
$calCode .= "		},\n";

$calCode .= "		loading: function(bool) {\n";
$calCode .= "			if (bool) {\n";
$calCode .= "				dpjQuery('#dpcalendar_component_loading').show();\n";
$calCode .= "			}else{\n";
$calCode .= "				dpjQuery('#dpcalendar_component_loading').hide();\n";
$calCode .= "			}\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$class = 'fc';
$calCode .= "	var custom_buttons = '<span class=\"fc-button fc-button-datepicker " . $class . "-state-default " . $class . "-corner-left " . $class . "-corner-right\">'+\n";
$calCode .= "			'<span class=\"fc-button-inner\"><span class=\"fc-button-content\" id=\"dpcalendar_component_date_picker_button\">'+\n";
$calCode .= "			'<input type=\"hidden\" id=\"dpcalendar_component_date_picker\" value=\"\">'+\n";
$calCode .= "			'<i class=\"icon-calendar\" title=\"" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_SHOW_DATEPICKER') . "\"></i>'+\n";
$calCode .= "			'</span></span></span>';\n";
$calCode .= "		custom_buttons +='<span class=\"hidden-phone fc-button fc-button-print " . $class . "-state-default " . $class . "-corner-left " . $class . "-corner-right\">'+\n";
$calCode .= "			'<span class=\"fc-button-inner\"><span class=\"fc-button-content\" id=\"dpcalendar_component_print_button\">'+\n";
$calCode .= "			'<i class=\"icon-print\" title=\"" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_PRINT') . "\"></i>'+\n";
$calCode .= "			'</span></span></span>';\n";
$calCode .= "	dpjQuery('span.fc-header-space').after(custom_buttons);\n";
$calCode .= "	if (dpjQuery('table').disableSelection) dpjQuery('div.fc-button-today').closest('table.fc-header').disableSelection();\n";
$calCode .= "	dpjQuery(\"#dpcalendar_component_date_picker\").datepicker({\n";
$calCode .= "		dateFormat: 'dd-mm-yy',\n";
$calCode .= "		changeYear: true, \n";
$calCode .= "		dayNames: " . $daysLong . ",\n";
$calCode .= "		dayNamesShort: " . $daysShort . ",\n";
$calCode .= "		dayNamesMin: " . $daysMin . ",\n";
$calCode .= "		monthNames: " . $monthsLong . ",\n";
$calCode .= "		monthNamesShort: " . $monthsShort . ",\n";
$calCode .= "		firstDay: " . $params->get('weekstart', 0) . ",\n";
$calCode .= "		showButtonPanel: true,\n";
$calCode .= "		closeText: '" . JText::_('JCANCEL', true) . "',\n";
$calCode .= "		currentText: '" . JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY', true) . "',\n";
$calCode .= "		onSelect: function(dateText, inst) {\n";
$calCode .= "			var d = dpjQuery('#dpcalendar_component_date_picker').datepicker('getDate');\n";
$calCode .= "			var view = dpjQuery('#dpcalendar_component').fullCalendar('getView').name;\n";
$calCode .= "			dpjQuery('#dpcalendar_component').fullCalendar('gotoDate', d);\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$calCode .= "	dpjQuery(window).bind( 'hashchange', function(){\n";
$calCode .= "		var today = new Date();\n";
$calCode .= "		var tmpYear = today.getFullYear();\n";
$calCode .= "		var tmpMonth = today.getMonth();\n";
$calCode .= "		var tmpDay = today.getDate();\n";
$calCode .= "		var tmpView = '" . $defaultView . "';\n";
$calCode .= "		var vars = window.location.hash.replace(/&amp;/gi, \"&\").split(\"&\");\n";
$calCode .= "		for ( var i = 0; i < vars.length; i++ ){\n";
$calCode .= "			if(vars[i].match(\"^#year\"))tmpYear = vars[i].substring(6);\n";
$calCode .= "			if(vars[i].match(\"^month\"))tmpMonth = vars[i].substring(6)-1;\n";
$calCode .= "			if(vars[i].match(\"^day\"))tmpDay = vars[i].substring(4);\n";
$calCode .= "			if(vars[i].match(\"^view\"))tmpView = vars[i].substring(5);\n";
$calCode .= "		}\n";
$calCode .= "		var date = new Date(tmpYear, tmpMonth, tmpDay,0,0,0);\n";
$calCode .= "		var d = dpjQuery('#dpcalendar_component').fullCalendar('getDate');\n";
$calCode .= "		var view = dpjQuery('#dpcalendar_component').fullCalendar('getView');\n";
$calCode .= "		if(date.getFullYear() != d.getFullYear() || date.getMonth() != d.getMonth() || date.getDate() != d.getDate())\n";
$calCode .= "			dpjQuery('#dpcalendar_component').fullCalendar('gotoDate', date);\n";
$calCode .= "		if(view.name != tmpView)\n";
$calCode .= "			dpjQuery('#dpcalendar_component').fullCalendar('changeView', tmpView);\n";
$calCode .= "	});\n";
$calCode .= "	dpjQuery('.ui-widget-overlay').on('click', function() { dpjQuery('#dpcalendar-dialog').dialog('close'); });\n";
if ($params->get('show_selection', 1) == 1)
{
	$calCode .= "dpjQuery('#dpcalendar_view_list').hide();\n";
}
if ($params->get('show_map', 1) == 1)
{
	$calCode .= "			var lat = 0; var long = 0;\n";
	$calCode .= "			if( typeof geoip_latitude === 'function' ){ lat = geoip_latitude(); long = geoip_longitude(); }\n";
	$calCode .= "			var dpcalendarMap = new google.maps.Map(document.getElementById('dpcalendar_component_map'), {zoom: " . $params->get('map_zoom', 4) . ", mapTypeId: google.maps.MapTypeId.ROADMAP, center: new google.maps.LatLng(lat, long)});\n";
	$calCode .= "			var dpcalendarMapBounds = new google.maps.LatLngBounds();\n";
	$calCode .= "			var dpcalendarMapMarkers = [];\n";
}
$calCode .= "});\n";
$calCode .= "// ]]>\n";
$document->addScriptDeclaration($calCode);

?>
<div class="dp-container">
<div class="pull-left event-button"><?php echo JHtml::_('share.twitter', $params); ?></div>
<div class="pull-left event-button"><?php echo JHtml::_('share.like', $params); ?></div>
<div class="pull-left event-button"><?php echo JHtml::_('share.google', $params); ?></div>
<div class="pull-left event-button"><?php echo JHtml::_('share.linkedin', $params); ?></div>
<div class="clearfix"></div>

<?php
echo JHTML::_('content.prepare', $params->get('textbefore'));
if ($params->get('show_selection', 1) == 1 || $params->get('show_selection', 1) == 3)
{?>
<dl id="dpcalendar_view_list">
<?php foreach ($this->items as $calendar)
{
	$value = html_entity_decode(JRoute::_('index.php?option=com_dpcalendar&view=events&format=raw&limit=0&ids=' .
				$calendar->id . '&my=' . $params->get('show_my_only_calendar', '0') . '&Itemid=' . JRequest::getInt('Itemid', 0)));
	$checked = '';
	if (empty($this->selectedCalendars) || in_array($calendar->id, $this->selectedCalendars))
	{
		$checked = 'checked="checked"';
	}?>
	<dt>
		<label class="checkbox">
			<input type="checkbox" name="<?php echo $calendar->id?>" value="<?php echo $value . '" ' . $checked?> onclick="updateDPCalendarFrame(this)"/>
			<font color="<?php echo $calendar->color?>">
				<?php echo str_pad(' ' . $calendar->title, strlen(' ' . $calendar->title) + $calendar->level - 1, '-', STR_PAD_LEFT)?>
			</font>
			[ <a href="<?php echo DPCalendarHelper::getCalendarIcalRoute($calendar->id)?>">
				<?php echo JText::_('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_ICAL')?>
				</a> ]
		</label>
	</dt>
	<dd><?php echo $calendar->description?></dd>
<?php
}?>
</dl>
<?php
$image = JURI::base() . 'media/com_dpcalendar/images/site/down.png';
if ($params->get('show_selection', 1) == 3)
{
	$image = JURI::base() . 'media/com_dpcalendar/images/site/up.png';
}?>
<div style="text-align:center">
<img id="dpcalendar_view_toggle_status" src="<?php echo $image?>" alt="<?php echo JText::_('COM_DPCALENDAR_VIEW_CALENDAR_CALENDAR_LIST')?>" title="<?php echo JText::_('COM_DPCALENDAR_VIEW_CALENDAR_CALENDAR_LIST')?>"/>
</div>
<?php
}?>

<div id='dpcalendar_component_loading' style="text-align: center;<?php echo empty($this->items) ? 'visibility:hidden' : '';?>">
	<img src="<?php echo JURI::base()?>media/com_dpcalendar/images/site/ajax-loader.gif"  alt="loader" />
</div>
<div id="dpcalendar_component"></div>
<div id='dpcalendar_component_popup' style="visibility:hidden" ></div>
<?php if ($params->get('show_map', 1) == 1)
{?>
<div id="dpcalendar_component_map" style="width:<?php echo $params->get('map_width', '100%') . ";height:" . $params->get('map_height', '350px')?>"
	class="dpcalendar-fixed-map"></div>
<?php
}

echo JHTML::_('content.prepare', $params->get('textafter'));
echo JHtml::_('share.comment', $params);

$width = $params->get('popup_width', 0) ? 'width:' . $params->get('popup_width', 0) . 'px;':'';
$height = $params->get('popup_height', 500) ? 'height:' . $params->get('popup_height', 500) . 'px;':'';
?>

<div id="dpc-event-view" class="modal hide" tabindex="-1" role="dialog" aria-hidden="true"
	style="<?php echo $width . $height?>">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
  	<iframe style="width:99.6%;height:95%;border:none;"></iframe>
</div>
</div>

<?php
if (!$canAdd)
{
	return;
}

JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar');

$dateVar = JRequest::getVar('date', null);
$local = false;
if (strpos($dateVar, '00-00') != false)
{
	$dateVar = substr($dateVar, 0, 10) . DPCalendarHelper::getDate()->format(' H:i');
	$local = true;
}
$date = DPCalendarHelper::getDate($dateVar);
$date->setTime($date->format('H'), 0);

JLoader::import('joomla.form.form');
JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
$form = JForm::getInstance('com_dpcalendar.event', 'event', array('control' => 'jform'));
$form->setValue('start_date', null, $date->format('Y-m-d H:i:00', $local));
$date->modify('+1 hour');
$form->setValue('end_date', null, $date->format('Y-m-d H:i:00', $local));
$form->setFieldAttribute('title', 'class', 'input-medium');

$form->setFieldAttribute('start_date', 'format', $params->get('event_form_date_format', 'm.d.Y'));
$form->setFieldAttribute('start_date', 'formatTime', $params->get('event_form_time_format', 'g:i a'));
$form->setFieldAttribute('end_date', 'format', $params->get('event_form_date_format', 'm.d.Y'));
$form->setFieldAttribute('end_date', 'formatTime', $params->get('event_form_time_format', 'g:i a'));
?>
<form action="<?php echo JRoute::_(DPCalendarHelper::getFormRoute(0, JUri::getInstance()->toString())); ?>"
	method="post" name="adminForm" id="editEventForm" class="form-validate dp-container">
	<button class="close dpcal-cancel">&times;</button>
	<div class="span12 form-horizontal">
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel('start_date'); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput('start_date'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel('end_date'); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput('end_date'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel('title'); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput('title'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel('catid'); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput('catid'); ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" id="task" value="event.save"/>
	<input type="hidden" name="jform[capacity]" value="0"/>
	<input type="hidden" name="jform[all_day]" value="0"/>
	<input type="hidden" name="layout" id="layout" value="edit"/>
	<?php echo JHtml::_('form.token'); ?>
	<button id="dpcal-create" class="btn btn-mini btn-primary" type="button"><?php echo JText::_('COM_DPCALENDAR_VIEW_FORM_BUTTON_SUBMIT_EVENT');?></button>
	<button id="dpcal-edit" class="btn btn-mini" type="button"><?php echo JText::_('COM_DPCALENDAR_VIEW_FORM_BUTTON_EDIT_EVENT');?></button>
	<button class="btn btn-mini btn-danger dpcal-cancel" type="button"><?php echo JText::_('JCANCEL');?></button>
</form>
