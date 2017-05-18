<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_SITE);
JLoader::import('components.com_dpcalendar.helpers.plugin', JPATH_SITE);

class DPCalendarViewMessage extends JViewLegacy
{

	protected $payment;

	protected $item;

	protected $form;

	protected $return_page;

	public function display ($tpl = null)
	{
		JLoader::import('joomla.plugin.helper');
		JPluginHelper::importPlugin('dpcalendarpay');

		$app = JFactory::getApplication();

		$this->item = $this->get('Item');
		$this->return_page = $this->get('ReturnPage');
		if ($app->getUserState('payment_return'))
		{
			$this->return_page = base64_decode($app->getUserState('payment_return'));
		}

		switch ($this->getLayout())
		{
			case 'cancel':
				$field = 'cancelurl';
				if (isset($this->item->attendee_id))
				{
					// Remove old Attendee
					$payment = JModelLegacy::getInstance('Attendee', 'DPCalendarModel');
					$payment->delete($this->item->attendee_id);
				}
				break;

			case 'order':
			default:
				$field = 'orderurl';
				break;
		}

		// Do I have a custom redirect URL? Follow it instead of showing the
		// message
		if (@$this->item->$field)
		{
			$app->redirect($this->item->$field);
		}

		return parent::display($tpl);
	}
}
