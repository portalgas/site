<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

DPCalendarHelper::loadLibrary(array('jquery' => true, 'maps' => true, 'bootstrap' => true, 'dpcalendar' => true));

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/default.css');
$document->addScript(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/event.js');

if (JRequest::getCmd('tmpl', '') == 'component')
{
	$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/none-responsive.css');
}

ob_start();
?>

{{#events}}
<div id="dpcal-event-container" class="dp-container" itemprop="event" itemscope itemtype="http://schema.org/Event">
{{#pluginsBefore}} {{{.}}} {{/pluginsBefore}}
{{#attendButton}}<div class="pull-left event-button">{{{attendButton}}}</div>{{/attendButton}}
{{#canEdit}}<div class="pull-left event-button">{{{editButton}}}</div>{{/canEdit}}
{{#canDelete}}<div class="pull-left event-button">{{{deleteButton}}}</div>{{/canDelete}}
<div class="pull-left event-button">{{{shareTwitter}}}</div>
<div class="pull-left event-button">{{{shareLike}}}</div>
<div class="pull-left event-button">{{{shareGoogle}}}</div>
<div class="pull-left event-button">{{{shareLinkedin}}}</div>
<div class="clearfix"></div>
<h2>{{eventLabel}}</h2>
<div class="row-fluid">
	<div class="span7">
		<div class="row-fluid" id="dp-event-title">
			<div class="span3 event-label">{{titleLabel}}: </div>
			<div class="span9 event-content" itemprop="name">{{title}}</div>
		</div>
		<div class="row-fluid" id="dp-event-calendar">
			<div class="span3 event-label">{{calendarNameLabel}}: </div>
			<div class="span9 event-content">
				{{#calendarLink}}<a href="{{calendarLink}}" target="_parent">{{calendarName}}</a>{{/calendarLink}}
				{{^calendarLink}}{{calendarName}}{{/calendarLink}}
			</div>
		</div>
		<div class="row-fluid" id="dp-event-date">
			<div class="span3 event-label">{{dateLabel}}: </div>
			<div class="span9 event-content" itemprop="startDate" content="{{startDateIso}}">{{date}}</div>
		</div>
		<div class="row-fluid" id="dp-event-location">
			<div class="span3 event-label">{{locationLabel}}: </div>
			<div class="span9 event-content" itemprop="location" content="{{#location}}{{full}} {{/location}}">{{#location}}
				<div class="dp-location" data-latitude="{{latitude}}" data-longitude="{{longitude}}" data-title="{{title}}">
					<a href="http://maps.google.com/?q={{full}}" target="_blank">{{title}}</a>
				</div>
				<br/>{{/location}}
			</div>
		</div>
		<div class="row-fluid" id="dp-event-url">
			<div class="span3 event-label">{{urlLabel}}: </div>
			<div class="span9 event-content"><a href="{{url}}" target="_blank">{{url}}</a></div>
		</div>
		<div class="row-fluid" id="dp-event-author">
			<div class="span3 event-label">{{authorLabel}}: </div>
			<div class="span9 event-content" itemprop="performer">{{author}}<br/>{{{avatar}}}</div>
		</div>
		<div class="row-fluid" id="dp-event-capacity">
			<div class="span3 event-label">{{capacityLabel}}: </div>
			<div class="span9 event-content">{{capacity}}</div>
		</div>
		<div class="row-fluid">
			<div class="span3 event-label" id="dp-event-attendee">{{attendeesLabel}}: </div>
			<div class="span9 event-content" itemprop="performer">
			{{#attendees}}<span class="label">{{name}}</span> {{/attendees}}</div>
		</div>
		<div class="row-fluid" id="dp-event-copy-google">
			<div class="span3 event-label">{{copyLabel}}: </div>
			<div class="span9 event-content"><a target="_blank" href="{{copyGoogleUrl}}">{{copyGoogleLabel}}</a></div>
		</div>
		<div class="row-fluid" id="dp-event-coppy-outlook">
			<div class="span3 event-label"></div>
			<div class="span9 event-content"><a target="_blank" href="{{copyOutlookUrl}}">{{copyOutlookLabel}}</a></div>
		</div>
	</div>
	<div class="span5" id="dp-event-map"><div id="dp-event-details-map" class="pull-right dpcalendar-fixed-map" data-zoom="4"></div></div>
</div>
{{#description}}
<h2>{{descriptionLabel}}</h2>
<div itemprop="description">
{{{description}}}
</div>
{{/description}}
{{#pluginsAfter}} {{{.}}} {{/pluginsAfter}}
{{#shareComment}}
<h2>{{commentsLabel}}</h2>
{{{shareComment}}}
{{/shareComment}}
</div>
{{/events}}
{{^events}}
{{emptyText}}
{{/events}}

<?php
$output = ob_get_contents();
ob_end_clean();

$params = $this->item->params;

$variables = array();
$variables['shareTwitter'] = JHtml::_('share.twitter', $params);
$variables['shareLike'] = JHtml::_('share.like', $params);
$variables['shareGoogle'] = JHtml::_('share.google', $params);
$variables['shareComment'] = JHtml::_('share.comment', $params, $this->item);
$variables['shareLinkedin'] = JHtml::_('share.linkedin', $params);

$variables['editButton'] = JHtml::_('dpcalendaricon.edit', $this->item, $params);
$variables['deleteButton'] = JHtml::_('dpcalendaricon.delete', $this->item, $params);
$variables['attendButton'] = JHtml::_('dpcalendaricon.attend', $this->item);

JPluginHelper::importPlugin('dpcalendar');
$dispatcher = JDispatcher::getInstance();
$variables['pluginsBefore'] = $dispatcher->trigger('onEventBeforeDisplay', array(&$this->item,  &$content));
$variables['pluginsAfter'] = $dispatcher->trigger('onEventAfterDisplay', array(&$this->item,  &$content));

JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . '/components/com_dpcalendar');

echo DPCalendarHelper::renderEvents(array($this->item), $output, JFactory::getApplication()->getParams(), $variables);
