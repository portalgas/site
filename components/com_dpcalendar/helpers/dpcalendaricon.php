<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.helper');

class JHtmlDPCalendaricon
{

	public static function attend ($event)
	{
		if (! DPCalendarHelper::openForAttending($event))
		{
			return '';
		}

		$title = JText::_('COM_DPCALENDAR_VIEW_ATTENDEE_ADD_TEXT');
		$text = JText::_('COM_DPCALENDAR_ATTEND');
		$icon = 'icon-plus';
		foreach ($event->attendees as $attendee)
		{
			if (JFactory::getUser()->id > 0 && JFactory::getUser()->id == $attendee->user_id)
			{
				switch ($attendee->state)
				{
					case 1:
						$text = JText::_('COM_DPCALENDAR_ATTENDING');
						$title = JText::_('COM_DPCALENDAR_VIEW_ATTENDEE_EDIT_TEXT');
						$icon = 'icon-edit';
						break;
					case 3:
						$text = JText::_('COM_DPCALENDAR_ATTENDING_NEEDS_PAYMENT');
						$title = JText::_('COM_DPCALENDAR_VIEW_ATTENDEE_EDIT_TEXT');
						$icon = 'icon-edit';
						break;
					case 4:
						$text = JText::_('COM_DPCALENDAR_ATTENDING_ON_HOLD');
						$title = JText::_('COM_DPCALENDAR_VIEW_ATTENDEE_EDIT_TEXT');
						$icon = 'icon-edit';
						break;
				}
				break;
			}
		}

		$text = '<i class="' . $icon . '"></i> ' . $text;
		$button = JHtml::_('link', DPCalendarHelper::getAttendRoute($event), $text);
		$output = '<span class="hasTip btn btn-small" title="' . $title . '">' . $button . '</span>';
		return $output;
	}

	public static function create ($event, $params)
	{
		$uri = JFactory::getURI();

		$url = JRoute::_(DPCalendarHelper::getFormRoute(0, $uri));
		$text = '<i class="icon-plus"></i> ' . JText::_('JNEW');
		$button = JHtml::_('link', $url, $text);
		$output = '<span class="hasTip btn btn-small" title="' . JText::_('COM_DPCALENDAR_VIEW_FORM_SUBMIT_EVENT') . '">' . $button . '</span>';
		return $output;
	}

	public static function edit ($event, $params, $attribs = array())
	{
		$user = JFactory::getUser();
		$uri = JFactory::getURI();

		if ($params && $params->get('popup'))
		{
			return;
		}

		if ($event->state < 0)
		{
			return;
		}

		JHtml::_('behavior.tooltip');
		$url = DPCalendarHelper::getFormRoute($event->id, $uri);
		$text = '<i class="icon-edit"></i> ' . JText::_('JGLOBAL_EDIT');

		if ($event->state == 0)
		{
			$overlib = JText::_('JUNPUBLISHED');
		}
		else
		{
			$overlib = JText::_('JPUBLISHED');
		}

		$date = JHtml::_('date', $event->created);
		$author = $event->created_by_alias ? $event->created_by_alias : $event->author;

		$overlib .= '&lt;br /&gt;';
		$overlib .= $date;
		$overlib .= '&lt;br /&gt;';
		$overlib .= htmlspecialchars($author, ENT_COMPAT, 'UTF-8');

		$button = JHtml::_('link', JRoute::_($url), $text);

		$output = '<span class="hasTip btn btn-small" title="' . JText::_('COM_DPCALENDAR_VIEW_FORM_BUTTON_EDIT_EVENT') . ' :: ' . $overlib . '">' .
				 $button . '</span>';

		return $output;
	}

	public static function delete ($event, $params, $attribs = array())
	{
		JHtml::_('behavior.tooltip');
		$text = '<i class="icon-delete icon-remove"></i> ' . JText::_('COM_DPCALENDAR_DELETE');

		if ($event->state == 0)
		{
			$overlib = JText::_('JUNPUBLISHED');
		}
		else
		{
			$overlib = JText::_('JPUBLISHED');
		}

		$date = JHtml::_('date', $event->created);
		$author = $event->created_by_alias ? $event->created_by_alias : $event->author;

		$overlib .= '&lt;br /&gt;';
		$overlib .= $date;
		$overlib .= '&lt;br /&gt;';
		$overlib .= htmlspecialchars($author, ENT_COMPAT, 'UTF-8');

		$return = JFactory::getURI();
		if (JRequest::getCmd('view', null) == 'event')
		{
			$return->setVar('layout', 'empty');
		}

		$link = 'index.php?option=com_dpcalendar&task=event.delete&e_id=' . $event->id . '&tmpl=' . JRequest::getWord('tmpl') . '&return=' .
				 base64_encode($return);
		$button = JHtml::_('link', JRoute::_($link), $text);

		$output = '<span class="hasTip btn btn-small" title="' . JText::_('COM_DPCALENDAR_VIEW_FORM_BUTTON_DELETE_EVENT') . ' :: ' . $overlib . '">' .
				 $button . '</span>';

		return $output;
	}
}
