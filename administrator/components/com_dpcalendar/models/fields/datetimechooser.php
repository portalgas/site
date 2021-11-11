<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldDatetimechooser extends JFormField
{

	protected $type = 'Datetimechooser';

	public function getInput ()
	{
		JHtml::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'helpers' . DS . 'html');

		$options = array();
		$options['class'] = $this->element['class'];
		$options['onchange'] = $this->element['onchange'];
		$options['allDay'] = $this->element['all_day'] == '1';
		$options['dateFormat'] = $this->element['format'];
		$options['timeFormat'] = $this->element['formatTime'];
		$options['formated'] = $this->element['formated'];
		return JHtml::_('datetime.render', $this->value, $this->id, $this->name, $options);
	}
}
