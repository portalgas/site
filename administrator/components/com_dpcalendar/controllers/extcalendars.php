<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.controlleradmin');

class DPCalendarControllerExtcalendars extends JControllerAdmin
{

	protected $text_prefix = 'COM_DPCALENDAR_EXTCALENDAR';

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->input = JFactory::getApplication()->input;
	}

	public function getModel ($name = 'Extcalendar', $prefix = 'DPCalendarModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function import ()
	{
		$this->setRedirect(
				'index.php?option=com_dpcalendar&view=extcalendars&layout=import&dpplugin=' . $this->input->getCmd('dpplugin') . '&tmpl=' .
						 $this->input->getCmd('tmpl'));
		return true;
	}

	public function delete ()
	{
		$return = parent::delete();

		$redirect = $this->redirect;
		$tmp = $this->input->get('dpplugin');
		if ($tmp)
		{
			$redirect .= '&dpplugin=' . $tmp;
		}
		$tmp = $this->input->get('tmpl');
		if ($tmp)
		{
			$redirect .= '&tmpl=' . $tmp;
		}
		$this->setRedirect($redirect);
		return $return;
	}

	public function publish ()
	{
		$return = parent::publish();

		$redirect = $this->redirect;
		$tmp = $this->input->get('dpplugin');
		if ($tmp)
		{
			$redirect .= '&dpplugin=' . $tmp;
		}
		$tmp = $this->input->get('tmpl');
		if ($tmp)
		{
			$redirect .= '&tmpl=' . $tmp;
		}
		$this->setRedirect($redirect);
		return $return;
	}
}
