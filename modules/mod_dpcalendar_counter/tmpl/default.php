<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary();

ob_start();
?>
{{#events}}
<span class="countdown_row">{y<}<span class="countdown_section"><span
		class="countdown_amount">{yn}</span><br />{yl}</span>{y>}{o<}<span
	class="countdown_section"><span class="countdown_amount">{on}</span><br />{ol}</span>{o>}{w<}<span
	class="countdown_section"><span class="countdown_amount">{wn}</span><br />{wl}</span>{w>}{d<}<span
	class="countdown_section"><span class="countdown_amount">{dn}</span><br />{dl}</span>{d>}{h<}<span
	class="countdown_section"><span class="countdown_amount">{hn}</span><br />{hl}</span>{h>}{m<}<span
	class="countdown_section"><span class="countdown_amount">{mn}</span><br />{ml}</span>{m>}{s<}<span
	class="countdown_section"><span class="countdown_amount">{sn}</span><br />{sl}</span>{s>}
	<div style="clear: both">
		<p>
			<a href="{{{backLink}}}">{{title}}</a><br />{{{description}}}
		</p>
	</div></span>
{{/events}}{{^events}}{{emptyText}}{{/events}}

<?php
$output = ob_get_contents();
ob_end_clean();

$data = array();
$targetDate = 0;
$title = '';
if ($item != null)
{
	$data[] = $item;
	$d = DPCalendarHelper::getDate($item->start_date, $item->all_day);
	$targetDate = $d->format('Y', true) . "," . ($d->format('m', true) - 1) . "," . $d->format('d', true) . "," . $d->format('H', true) . "," .
			 $d->format('i', true) . ",0";
	$title = $item->title;
}

$tmp = clone JComponentHelper::getParams('com_dpcalendar');
$tmp->set('event_date_format', $params->get('date_format', 'm.d.Y'));
$tmp->set('event_time_format', $params->get('time_format', 'g:i a'));
$tmp->set('description_length', $params->get('description_length', $tmp->get('description_length')));

$layout = preg_replace('#\r|\n#', '', DPCalendarHelper::renderEvents($data, $output, $tmp));

$output = $params->get('output_now',
		'{{#events}}<p>Event happening now:<br/>{{date}}<br/><a href="{{{backLink}}}">{{title}}</a>{{#maplink}}<br/>Join us at [<a href="{{{maplink}}}" target="_blank">map</a>]{{/maplink}}</p>{{/events}}{{^events}}{{emptyText}}{{/events}}');
$expiryText = preg_replace('#\r|\n#', "", DPCalendarHelper::renderEvents($data, $output, $tmp));

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'components/com_dpcalendar/libraries/jquery/ext/jquery.countdown.min.js');
$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/libraries/jquery/ext/jquery.countdown.css');

$targetId = "dpcountdown-" . $module->id;

$labels = array(
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_YEARS'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_MONTHS'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_WEEKS'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_DAYS'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_HOURS'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_MINUTES'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_SECONDS')
);
$labels1 = array(
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_YEAR'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_MONTH'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_WEEK'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_DAY'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_HOUR'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_MINUTE'),
		JText::_('MOD_DPCALENDAR_COUNTER_LABEL_SECOND')
);

$code = "// <![CDATA[ \n";
$code .= "	dpjQuery(document).ready(function() {\n";
$code .= "	var targetDate = new Date(" . $targetDate . ");\n";
$code .= "	dpjQuery('#" . $targetId . "').countdown({until: targetDate, \n";
$code .= "				       description: '" . str_replace('\'', '\\\'', $title) . "', \n";
$code .= " 				       layout: '" . str_replace('\'', '\\\'', $layout) . "', \n";
$code .= " 				       labels: ['" . implode("','", $labels) . "'], \n";
$code .= " 				       labels1: ['" . implode("','", $labels1) . "'], \n";
$code .= "				       format: 'dHMS'});\n";
$code .= "});\n";
$code .= "// ]]>\n";
$document->addScriptDeclaration($code);
?>
<div class="dpcalendar_next">
	<div id="<?php echo $targetId;?>" class="countdown">
		<?php echo JText::_("MOD_DPCALENDAR_COUNTER_JSERR");?>
	</div>
</div>
