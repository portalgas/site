<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JHtmlDateTime
{

	public static function render ($dateValue, $id, $name, $options = array())
	{
		DPCalendarHelper::loadLibrary(array(
				'jquery' => true,
				'datepicker' => true
		));

		$document = JFactory::getDocument();
		$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/jquery/timepicker/jquery.timepicker.min.js');
		$document->addStyleSheet(JURI::root() . 'components/com_dpcalendar/libraries/jquery/timepicker/jquery.timepicker.css');

		$dateFormat = DPCalendarHelper::getComponentParameter('event_date_format', 'm.d.Y');
		if (isset($options['dateFormat']) && ! empty($options['dateFormat']))
		{
			$dateFormat = $options['dateFormat'];
		}

		$timeFormat = DPCalendarHelper::getComponentParameter('event_time_format', 'g:i a');
		if (isset($options['timeFormat']) && ! empty($options['timeFormat']))
		{
			$timeFormat = $options['timeFormat'];
		}

		if (! isset($options['allDay']))
		{
			$options['allDay'] = false;
		}

		// Handle the special case for "now".
		$date = null;
		if (strtoupper($dateValue) == 'NOW')
		{
			$date = DPCalendarHelper::getDate();
			$date->setTime($date->format('H', true), 0, 0);
		}
		else if (strtoupper($dateValue) == '+1 HOUR' || strtoupper($dateValue) == '+2 MONTH')
		{
			$date = DPCalendarHelper::getDate();
			$date->setTime($date->format('H', true), 0, 0);
			$date->modify($dateValue);
		}
		else if (isset($options['formated']) && ! empty($options['formated']))
		{
			$date = DPCalendarHelper::getDateFromString($dateValue, null, $options['allDay'], $dateFormat, $timeFormat);
		}
		else
		{
			$date = DPCalendarHelper::getDate($dateValue, $options['allDay']);
		}

		// Transform the date string.
		$dateString = $date->format($dateFormat, true);
		$timeString = $date->format($timeFormat, true);
		if ($options['allDay'])
		{
			$dateString = $date->format($dateFormat, false);
			$timeString = $date->format($timeFormat, false);
		}

		$daysLong = "[";
		$daysShort = "[";
		$daysMin = "[";
		$monthsLong = "[";
		$monthsShort = "[";
		for ($i = 0; $i < 7; $i ++)
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
		for ($i = 1; $i <= 12; $i ++)
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

		$calCode = "dpjQuery(document).ready(function(){\n";
		$calCode .= "	dpjQuery('#" . $id . "').datepicker({\n";
		$calCode .= "		dateFormat: '" . self::dateStringToDatepickerFormat($dateFormat) . "',\n";
		$calCode .= "		changeYear: true, \n";
		$calCode .= "		dayNames: " . $daysLong . ",\n";
		$calCode .= "		dayNamesShort: " . $daysShort . ",\n";
		$calCode .= "		dayNamesMin: " . $daysMin . ",\n";
		$calCode .= "		monthNames: " . $monthsLong . ",\n";
		$calCode .= "		monthNamesShort: " . $monthsShort . ",\n";
		$calCode .= "		firstDay: " . DPCalendarHelper::getComponentParameter('weekstart', 0) . "\n";
		$calCode .= "	});\n";
		$calCode .= "	dpjQuery('#" . $id . "_time').timepicker({'timeFormat': '" . $timeFormat . "'});\n";
		$calCode .= "});\n";
		JFactory::getDocument()->addScriptDeclaration($calCode);

		$onchange = isset($options['onchange']) && ! empty($options['onchange']) ? ' onchange="' . $options['onchange'] . '"' : '';

		if (! isset($options['class']) || empty($options['class']))
		{
			$options['class'] = 'input-small';
		}

		$buffer = '';

		$type = 'text';
		if (isset($options['button']) && $options['button'])
		{
			$type = 'hidden';
		}

		$timeName = $name;
		if (strpos($timeName, ']') !== false)
		{
			$timeName = str_replace(']', '_time]', $name);
		}
		else
		{
			$timeName .= '_time';
		}
		$buffer .= '<input type="' . $type . '" class="' . $options['class'] . '" value="' . $dateString . '" name="' . $name . '" id="' . $id .
				 '" size="15" maxlength="10" ' . $onchange . '/>';
		$buffer .= '&nbsp;<input type="text" class="time ' . $options['class'] . '" value="' . $timeString . '" size="8" name="' . $timeName . '" id="' .
				 $id . '_time" ' . ($options['allDay'] == '1' ? 'style="display:none"' : '') . '/>';
		if (isset($options['button']) && $options['button'])
		{
			$buffer .= '<button class="btn" type="button" onclick="dpjQuery(\'#' . $id . '\').datepicker(\'show\');">';
			$buffer .= '<i class="icon-calendar"></i>';
			$buffer .= '</button>';
		}
		return $buffer;
	}

	public static function dateStringToDatepickerFormat ($dateString)
	{
		$pattern = array(
				'd',
				'j',
				'l',
				'z',
				'F',
				'M',
				'n',
				'm',
				'Y',
				'y'
		);
		$replace = array(
				'dd',
				'd',
				'DD',
				'o',
				'MM',
				'M',
				'm',
				'mm',
				'yy',
				'y'
		);
		foreach ($pattern as &$p)
		{
			$p = '/' . $p . '/';
		}
		return preg_replace($pattern, $replace, $dateString);
	}
}
