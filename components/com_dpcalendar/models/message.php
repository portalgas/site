<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.modelform');
JLoader::import('components.com_dpcalendar.tables.event', JPATH_ADMINISTRATOR);

class DPCalendarModelMessage extends JModelLegacy
{

	protected $view_item = 'messages';

	protected $_item = null;

	protected $_context = 'com_dpcalendar.messages';

	public function __construct ($config = array())
	{
		parent::__construct($config);

		$this->populateState();
	}

	protected function populateState ()
	{
		$app = JFactory::getApplication('site');

		$return = $app->input->get('return', null, 'default', 'base64');

		if (! JUri::isInternal(base64_decode($return)))
		{
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));

		// Load state from the request.
		$pk = $app->input->getInt('a_id', 0);
		$this->setState('attendee.id', $pk);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
	}

	public function getItem ($pk = null)
	{
		$pk = (! empty($pk)) ? $pk : $this->getState('attendee.id');

		if (! $pk)
		{
			return null;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($this->getState('item.select', 'a.*'));
		$query->from('#__dpcalendar_attendees AS a');
		$query->select($this->getState('item.select', 'e.title, e.ordertext, e.canceltext, e.orderurl, e.cancelurl'));
		$query->join('LEFT', '#__dpcalendar_events AS e on e.id = a.event_id');

		$query->select('u.name AS author');
		$query->join('LEFT', '#__users AS u on u.id = a.user_id');

		$query->where('a.id = ' . (int) $pk);

		$db->setQuery($query);
		$data = $db->loadObject();

		// Convert parameter fields to objects.
		$data->params = clone $this->getState('params');

		$this->_item[$pk] = $data;

		return $this->_item[$pk];
	}
}
