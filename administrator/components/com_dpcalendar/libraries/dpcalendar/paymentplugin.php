<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.plugin.plugin');
JLoader::register('DPCalendarModelAttendee', JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models/attendee.php');

abstract class DPCalendarPaymentPlugin extends JPlugin
{

	public function __construct (&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function onDPPaymentStart ($plugin)
	{
		if ($plugin == '0')
		{
			$plugin = $this->_name;
		}
		if ($plugin != $this->_name)
		{
			return null;
		}

		DPCalendarHelper::loadLibrary(array(
				'jquery' => true
		));
		JHtml::_('behavior.tooltip');

		$start = new stdClass();
		$start->return = base64_encode(JUri::base() . 'index.php?option=com_dpcalendar&view=attendee&layout=pay&tmpl=component&type=' . $this->_name);
		$document = JFactory::getDocument();
		$document->addScriptDeclaration(
				"dpjQuery(document).ready(function() {
				dpjQuery('#" . $this->_name . "').on('click', function(){
					dpjQuery('input[name=\"return\"]').val('$start->return');
					Joomla.submitbutton('attendee.apply');
				});
			});");
		$start->type = $this->_name;

		$name = strtoupper(str_replace('dpcalendar_', '', $this->_name));
		$start->button = '<button type="button" class="btn btn-primary hasTooltip" id="' . $this->_name . '" title="' .
				 JText::_('PLG_DPCALENDAR_' . $name . '_PAY_BUTTON_DESC') . '"/><i class="icon-ok"></i> ' .
				 $this->params->get('title', JText::_('PLG_DPCALENDAR_' . $name . '_PAY_BUTTON')) . '</button>';
		return $start;
	}

	public function onDPPaymentNew ($paymentmethod, $attendee)
	{
		JPlugin::loadLanguage();
		if ($paymentmethod != $this->_name && $paymentmethod != '0')
		{
			return false;
		}

		$event = JModelLegacy::getInstance('Event', 'DPCalendarModel')->getItem($attendee->event_id);
		$rootURL = rtrim(JURI::base(), '/');
		$subpathURL = JURI::base(true);
		if (! empty($subpathURL) && ($subpathURL != '/'))
		{
			$rootURL = substr($rootURL, 0, - 1 * strlen($subpathURL));
		}

		@ob_start();
		include JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/form.php';
		$html = @ob_get_clean();
		return $html;
	}

	public function onDPPaymentStatement ($attendee)
	{
		if ($attendee == null || $attendee->processor != $this->_name)
		{
			return;
		}
		$return = new stdClass();
		$return->status = true;
		$return->statement = $this->params->get('payment_statement');
		$return->type = $this->_name;
		return $return;
	}

	protected function updateRecord ($data)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/tables');
		$attendee = JTable::getInstance('Attendee', 'DPCalendarTable');

		$attendee->load($data['id']);

		$data['processor'] = $this->_name;

		if ($attendee)
		{
			$dataOld = (array) $attendee;
		}
		else
		{
			$dataOld = array();
		}
		$data = array_merge($dataOld, $data);

		return $attendee->save($data);
	}

	protected function log ($data, $isValid)
	{
		$config = JFactory::getConfig();
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$logpath = $config->get('log_path');
		}
		else
		{
			$logpath = $config->getValue('log_path');
		}

		$logFilenameBase = $logpath . '/plg_' . strtolower($this->_name);

		$logFile = $logFilenameBase . '.php';
		JLoader::import('joomla.filesystem.file');
		if (! JFile::exists($logFile))
		{
			$dummy = "<?php die(); ?>\n";
			JFile::write($logFile, $dummy);
		}
		else
		{
			if (@filesize($logFile) > 1048756)
			{
				$altLog = $logFilenameBase . '-1.php';
				if (JFile::exists($altLog))
				{
					JFile::delete($altLog);
				}
				JFile::copy($logFile, $altLog);
				JFile::delete($logFile);
				$dummy = "<?php die(); ?>\n";
				JFile::write($logFile, $dummy);
			}
		}
		$logData = file_get_contents($logFile);
		if ($logData === false)
		{
			$logData = '';
		}
		$logData .= "\n" . str_repeat('-', 80);
		$pluginName = strtoupper($this->_name);
		$logData .= $isValid ? 'VALID ' . $pluginName . ' IPN' : 'INVALID ' . $pluginName . ' IPN *** FRAUD ATTEMPT OR INVALID NOTIFICATION ***';
		$logData .= "\nDate/time : " . gmdate('Y-m-d H:i:s') . " GMT\n\n";
		foreach ($data as $key => $value)
		{
			$logData .= '  ' . str_pad($key, 30, ' ') . $value . "\n";
		}
		$logData .= "\n";
		JFile::write($logFile, $logData);
	}
}
