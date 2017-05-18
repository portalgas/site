<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'bootstrap' => true, 'fullcalendar' => true, 'dpcalendar' => true));

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'modules/mod_dpcalendar_mini/tmpl/dpcalendar.css');

$color = $params->get('event_color', '135CAE');
$cssClass = "dpcal-module_event_dpcal_" . $module->id;
$document->addStyleDeclaration("." . $cssClass . ",." . $cssClass . " a, ." . $cssClass . " div{background-color:transparent; !important; border-color: #" . $color . "} .fc-header-center{vertical-align: middle !important;} #dpcalendar_module_" . $module->id . " .fc-state-default span, #dpcalendar_module_" . $module->id . " .ui-state-default{padding:0px !important;}");

$user = JFactory::getUser();
$canAdd = DPCalendarHelper::canCreateEvent();

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

$calCode .= "dpjQuery('#dpcal-create-" . $module->id . "').click(function(){
    dpjQuery('#editEventForm" . $module->id . "').submit();
});
dpjQuery('.dpcal-cancel-" . $module->id . "').click(function(){
    dpjQuery('#editEventForm" . $module->id . "').toggle();
    dpjQuery('#editEventForm" . $module->id . " #jform_title').val('');
    return false;
});
dpjQuery('#dpcal-edit-" . $module->id . "').click(function(){
    dpjQuery('#editEventForm" . $module->id . " #task').val('');
    dpjQuery('#editEventForm" . $module->id . "').submit();
});

dpjQuery('body').click(function(e) {
    var form = dpjQuery('#editEventForm" . $module->id . "');

    if (form.has(e.target).length === 0 && !dpjQuery('#ui-datepicker-div').is(':visible') && !dpjQuery(e.target).hasClass('ui-timepicker-selected')) {
        form.hide();
    }
});";
$calCode .= "   dpjQuery('#dpcalendar_module_" . $module->id . "').fullCalendar({\n";
$calCode .= "		events: '" . html_entity_decode(JRoute::_('index.php?option=com_dpcalendar&view=events&limit=0&compact=' .
		$params->get('compact_events', 1) . '&format=raw&ids=' . implode(',', $params->get('ids', array('root'))))) . "',\n";
$calCode .= "       header: {\n";
$calCode .= "				left: 'prev,next ',\n";
$calCode .= "				center: 'title',\n";
$calCode .= "				right: ''\n";
$calCode .= "		},\n";
$calCode .= "		defaultView: 'month',\n";
$calCode .= "		eventClick: function(event, jsEvent, view) {\n";

if ($params->get('show_event_as_popup', 2) == 1)
{
	$calCode .= "		        if (dpjQuery(window).width() < 600) {window.location = dpEncode(event.url); return false;}\n";
	$calCode .= "		        dpjQuery('#dpc-event-view-" . $module->id . "').on('show', function () {\n";
	$calCode .= "		            var url = new Url(event.url);\n";
	$calCode .= "		            url.query.tmpl = 'component';\n";
	$calCode .= "		            dpjQuery('#dpc-event-view-" . $module->id . " iframe').attr('src', url.toString());\n";
	$calCode .= "		        });\n";
	$calCode .= "		        dpjQuery('#dpc-event-view-" . $module->id . "').on('hide', function () {\n";

	if (DPCalendarHelper::isJoomlaVersion('3'))
	{
		$calCode .= "		           if(dpjQuery('#dpc-event-view-" . $module->id . " iframe').contents().find('#system-message').children().length > 0){dpjQuery('#dpcalendar_module_" . $module->id . "').fullCalendar('refetchEvents');}\n";
	}
	if (DPCalendarHelper::isJoomlaVersion('2.5'))
	{
		$calCode .= "		           if(dpjQuery('#dpc-event-view-" . $module->id . " iframe').contents().find('#system-message-container').children().length > 0){dpjQuery('#dpcalendar_module_" . $module->id . "').fullCalendar('refetchEvents');}\n";
	}
	$calCode .= "		            dpjQuery('#dpc-event-view-" . $module->id . " iframe').removeAttr('src');\n";
	$calCode .= "		        });\n";
	$calCode .= "		        dpjQuery('#dpc-event-view-" . $module->id . "').modal();\n";
	$calCode .= "		        return false;\n";
}
else
{
	$calCode .= "		        window.location = dpEncode(event.url); return false;\n";
}
$calCode .= "		},\n";

$calCode .= "		dayClick: function(date, allDay, jsEvent, view) {\n";

if ($canAdd)
{
	$calCode .= "    jsEvent.stopPropagation();\n";
	$calCode .= "    dpjQuery('#jform_start_date').datepicker('setDate', date);\n";
	$calCode .= "    dpjQuery('#jform_start_date_time').timepicker('setTime', date);\n";
	$calCode .= "    dpjQuery('#jform_end_date').datepicker('setDate', date);\n";
	$calCode .= "    date.setHours(date.getHours()+1);\n";
	$calCode .= "    dpjQuery('#jform_end_date_time').timepicker('setTime', date);\n";
	$calCode .= "    var p = dpjQuery('#dpcalendar_module_" . $module->id . "').parents().filter(function() {\n";
	$calCode .= "    	var parent = dpjQuery(this);\n";
	$calCode .= "    	return parent.is('body') || parent.css('position') == 'relative';\n";
	$calCode .= "    }).slice(0,1).offset();\n";

	if ($params->get('event_edit_popup', 1) == 1)
	{
		$calCode .= "    dpjQuery('#editEventForm" . $module->id . "').css({top: jsEvent.pageY-p.top, left: jsEvent.pageX-160-p.left}).show();\n";
	}
	else
	{
		$calCode .= "    dpjQuery('#editEventForm" . $module->id . " #task').val('');\n";
		$calCode .= "    dpjQuery('#editEventForm" . $module->id . "').submit();\n";
	}
	$calCode .= "    dpjQuery('#jform_title').focus();\n";
}
$calCode .= "		},\n";

$height = $params->get('calendar_height', null);
if (!empty($height))
{
	$calCode .= "		contentHeight: " . $height . ",\n";
}
$calCode .= "		editable: false, theme: false,\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "		        month: '" . DPFullcalendar::convertFromPHPDate($params->get('titleformat_month', 'M Y')) . "'},\n";
$calCode .= "		firstDay: " . $params->get('weekstart', 0) . ",\n";
$calCode .= "		monthNames: " . $monthsLong . ",\n";
$calCode .= "		monthNamesShort: " . $monthsShort . ",\n";
$calCode .= "		dayNames: " . $daysLong . ",\n";
$calCode .= "		dayNamesShort: " . $daysShort . ",\n";
$calCode .= "		startParam: 'date-start',\n";
$calCode .= "		endParam: 'date-end',\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "		        month: '" . DPFullcalendar::convertFromPHPDate($params->get('timeformat_month', 'g:i a')) . "'},\n";
$calCode .= "		columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "		eventRender: function(event, element) {\n";
$calCode .= "			element.addClass('dpcal-module_event_dpcal_'+" . $module->id . ");\n";
$calCode .= "			if (event.description){\n";
$calCode .= "				element.tooltip({html: true, title: event.description, delay: { show: 100, hide: 500}, container: '#dpcalendar_module_" . $module->id . "'});}\n";
$calCode .= "		},\n";
$calCode .= "		loading: function(bool) {\n";
$calCode .= "			if (bool) {\n";
$calCode .= "				dpjQuery('#dpcalendar_module_" . $module->id . "_loading').show();\n";
$calCode .= "			}else{\n";
$calCode .= "				dpjQuery('#dpcalendar_module_" . $module->id . "_loading').hide();\n";
$calCode .= "			}\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$calCode .= "});\n";
$calCode .= "// ]]>\n";
$document->addScriptDeclaration($calCode);

$width = $params->get('popup_width', 0) ? 'width:' . $params->get('popup_width', 0) . 'px;':'';
$height = $params->get('popup_height', 500) ? 'height:' . $params->get('popup_height', 500) . 'px;':'';
?>
<div class="dp-container" data-id="<?php echo $module->id ?>">
	<div id="dpcalendar_module_<?php echo $module->id ?>_loading" style="text-align: center;">
		<img src="<?php echo JURI::base() ?>media/com_dpcalendar/images/site/ajax-loader.gif" alt="loader"/>
	</div>
	<div id="dpcalendar_module_<?php echo $module->id ?>"></div>
	<div id="dpcalendar_module_<?php echo $module->id ?>_popup" style="visibility:hidden"></div>
<div id="dpc-event-view-<?php echo $module->id ?>" class="modal hide" tabindex="-1" role="dialog" aria-hidden="true"
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
JFactory::getLanguage()->load('com_dpcalendar', JPATH_ROOT . DS . 'components' . DS . 'com_dpcalendar');

JLoader::import('joomla.form.form');
JForm::addFormPath(JPATH_ROOT . '/components/com_dpcalendar/models/forms');
JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models/fields');
$form = JForm::getInstance('com_dpcalendar.event', 'event', array('control' => 'jform'));
$form->setFieldAttribute('title', 'class', 'input-medium');

$form->setFieldAttribute('start_date', 'format', DPCalendarHelper::getComponentParameter('event_form_date_format', 'm.d.Y'));
$form->setFieldAttribute('start_date', 'formatTime', DPCalendarHelper::getComponentParameter('event_form_time_format', 'g:i a'));
$form->setFieldAttribute('end_date', 'format', DPCalendarHelper::getComponentParameter('event_form_date_format', 'm.d.Y'));
$form->setFieldAttribute('end_date', 'formatTime', DPCalendarHelper::getComponentParameter('event_form_time_format', 'g:i a'));
?>
<form action="<?php echo JRoute::_(DPCalendarHelper::getFormRoute(0, JUri::getInstance()->toString())); ?>" method="post"
      id="editEventForm<?php echo $module->id ?>" class="form-validate dp-container dpcalendar-mini-module-form">
	<button class="close dpcal-cancel-<?php echo $module->id ?>">&times;</button>
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
	<input type="hidden" name="jform[all_day]" value="0"/>
	<input type="hidden" name="layout" id="layout" value="edit"/>
	<?php echo JHtml::_('form.token'); ?>
	<button id="dpcal-create-<?php echo $module->id ?>" class="btn btn-mini btn-primary"
	        type="button"><?php echo JText::_('COM_DPCALENDAR_VIEW_FORM_BUTTON_SUBMIT_EVENT'); ?></button>
	<button id="dpcal-edit-<?php echo $module->id ?>" class="btn btn-mini"
	        type="button"><?php echo JText::_('COM_DPCALENDAR_VIEW_FORM_BUTTON_EDIT_EVENT'); ?></button>
	<button class="btn btn-mini btn-danger dpcal-cancel-<?php echo $module->id ?>" type="button"><?php echo JText::_('JCANCEL'); ?></button>
</form>
