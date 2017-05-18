<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.view');

class DPCalendarViewEvent extends JViewLegacy
{

	public function display ($tpl = null)
	{
		DPCalendarHelperIcal::createIcalFromEvents(array(
				$this->get('Item')
		), true);
	}
}
