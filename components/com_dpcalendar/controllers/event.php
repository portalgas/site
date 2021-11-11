<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.controllerform');

class DPCalendarControllerEvent extends JControllerForm
{

	protected $view_item = 'form';

	protected $view_list = 'calendar';

	protected $option = 'com_dpcalendar';

	public function add ()
	{
		if (! parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}

	protected function allowAdd ($data = array())
	{
		$calendar = DPCalendarHelper::getCalendar(JArrayHelper::getValue($data, 'catid', JRequest::getVar('id'), 'string'));
		$allow = null;
		if ($calendar)
		{
			$allow = $calendar->canCreate;
		}

		if ($allow === null)
		{
			return parent::allowAdd($data);
		}
		else
		{
			return $allow;
		}
	}

	protected function allowEdit ($data = array(), $key = 'id')
	{
		$recordId = isset($data[$key]) ? $data[$key] : 0;
		$event = null;

		if ($recordId)
		{
			$event = $this->getModel()->getItem($recordId);
		}

		if ($event != null)
		{
			$calendar = DPCalendarHelper::getCalendar($event->catid);
			return $calendar->canEdit || ($calendar->canEditOwn && $event->created_by == JFactory::getUser()->id);
		}
		else
		{
			return parent::allowEdit($data, $key);
		}
	}

	protected function allowDelete ($data = array(), $key = 'id')
	{
		$calendar = null;
		$event = null;
		if (isset($data['catid']))
		{
			$calendar = DPCalendarHelper::getCalendar($data['catid']);
		}
		if ($calendar == null)
		{
			$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
			$categoryId = 0;
			$event = $this->getModel()->getItem($recordId);
			$calendar = DPCalendarHelper::getCalendar($event->catid);
		}

		if ($calendar != null && $event != null)
		{
			return $calendar->canDelete || ($calendar->canEditOwn && $event->created_by == JFactory::getUser()->id);
		}
		else
		{
			return JFactory::getUser()->authorise('core.delete', $this->option);
		}
	}

	public function cancel ($key = 'e_id')
	{
		$recordId = JRequest::getVar($key);
		if (is_numeric($recordId))
		{
			parent::cancel($key);
		}
		$this->setRedirect($this->getReturnPage());
	}

	public function delete ($key = 'e_id')
	{
		$recordId = JRequest::getVar($key);

		if (! $this->allowDelete(array(
				$key => $recordId
		), $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

			return false;
		}

		if (! is_numeric($recordId))
		{
			JDispatcher::getInstance()->trigger('onEventDelete', array(
					$recordId
			));
		}
		else
		{
			$this->getModel()->publish($recordId, - 2);
			if (! $this->getModel()->delete($recordId))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
				$this->setMessage($this->getModel()
					->getError(), 'error');

				$this->setRedirect(
						JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

				return false;
			}
		}
		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage(), JText::_('COM_DPCALENDAR_DELETE_SUCCESS'));
		return true;
	}

	public function edit ($key = 'id', $urlVar = 'e_id')
	{
		$context = "$this->option.edit.$this->context";
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$recordId = (count($cid) ? $cid[0] : JRequest::getVar($urlVar));

		if (! $this->allowEdit(array(
				$key => $recordId
		), $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));
			return false;
		}
		if ($this->getModel()->getItem($recordId) != null && ! is_numeric($recordId))
		{
			$app = JFactory::getApplication();
			$values = (array) $app->getUserState($context . '.id');

			array_push($values, $recordId);
			$values = array_unique($values);
			$app->setUserState($context . '.id', $values);
			$app->setUserState($context . '.data', null);

			$this->setRedirect(
					JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $urlVar),
							false));
			return true;
		}

		return parent::edit($key, $urlVar);
	}

	public function getModel ($name = 'form', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	protected function getRedirectToItemAppend ($recordId = null, $urlVar = null)
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$itemId = JRequest::getInt('Itemid');
		$return = $this->getReturnPage();

		if ($itemId)
		{
			$append .= '&Itemid=' . $itemId;
		}

		if ($return)
		{
			$append .= '&return=' . base64_encode($return);
		}

		return $append;
	}

	protected function getReturnPage ()
	{
		$return = JRequest::getVar('return', null, 'default', 'base64');

		if (empty($return) || ! JUri::isInternal(base64_decode($return)))
		{
			return JURI::base();
		}
		else
		{
			return base64_decode($return);
		}
	}

	public function move ()
	{
		$data = array();
		$data['id'] = JRequest::getVar('id');
		$success = false;
		if (! $this->allowSave($data))
		{
			$this->getModel()->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
		}
		else
		{
			$event = $this->getModel()->getItem($data['id']);
			$data = JArrayHelper::fromObject($event);

			$start = DPCalendarHelper::getDate($event->start_date, $event->all_day);
			$end = DPCalendarHelper::getDate($event->end_date, $event->all_day);

			$days = JRequest::getVar('days') . ' day';
			if (strpos($days, '-') === false)
			{
				$days = '+' . $days;
			}
			$start->modify($days);
			$end->modify($days);

			$minutes = JRequest::getVar('minutes') . ' minute';
			if (strpos($minutes, '-') === false)
			{
				$minutes = '+' . $minutes;
			}
			if (JRequest::getVar('onlyEnd', 'false') == 'false')
			{
				$start->modify($minutes);
			}
			$end->modify($minutes);

			// If we were moved from a full day
			if ($event->all_day == 1 && JRequest::getVar('minutes') != '0')
			{
				$data['all_day'] = '0';
				$end->modify('+2 hour');
			}

			$data['start_date'] = $start->toSql();
			$data['end_date'] = $end->toSql();
			$data['date_range_correct'] = true;
			$data['all_day'] = JRequest::getVar('allDay') == 'true' ? '1' : '0';

			if (! is_numeric($data['catid']))
			{
				$tmp = JDispatcher::getInstance()->trigger('onEventSave', array(
						$data
				));
				foreach ($tmp as $newEventId)
				{
					if ($newEventId === false)
					{
						continue;
					}
					$data['id'] = $newEventId;
					$success = true;
				}
			}
			else
			{
				$success = $this->getModel()->save($data);
			}
		}

		if ($success)
		{
			DPCalendarHelper::sendMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'), false,
					array(
							'url' => DPCalendarHelper::getEventRoute($data['id'], $data['catid'])
					));
		}
		else
		{
			DPCalendarHelper::sendMessage($this->getModel()->getError(), true);
		}
	}

	public function save ($key = null, $urlVar = 'e_id')
	{
		if (DPCalendarHelper::isJoomlaVersion('3') && JRequest::getVar($urlVar))
		{
			$this->context = 'form';
		}

		$accessCaptcha = array_intersect(JComponentHelper::getParams('com_dpcalendar')->get('captcha_groups', array(
				1
		)), JAccess::getGroupsByUser(JFactory::getUser()->id, false));
		if (JPluginHelper::isEnabled('captcha') && $accessCaptcha)
		{
			JPluginHelper::importPlugin('captcha');
			$res = JEventDispatcher::getInstance()->trigger('onCheckAnswer', $this->input->get('recaptcha_response_field'));
			if (! $res[0])
			{
				JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar');
				$this->setRedirect(DPCalendarHelper::getFormRoute($this->input->get($urlVar), $this->getReturnPage()),
						JText::_('COM_DPCALENDAR_CAPTCHA_INVALID'), 'error');
				return false;
			}
		}

		$app = JFactory::getApplication();
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		if (empty($data['start_date_time']) && empty($data['end_date_time']))
		{
			$data['all_day'] = '1';
		}

		if (! key_exists('all_day', $data))
		{
			$data['all_day'] = 0;
		}

		$dateFormat = $app->getParams()->get('event_form_date_format', 'm.d.Y');
		$timeFormat = $app->getParams()->get('event_form_time_format', 'g:i a');

		$start = DPCalendarHelper::getDateFromString($data['start_date'], $data['start_date_time'], $data['all_day'] == '1', $dateFormat, $timeFormat);
		$data['start_date'] = $start->toSql(false);
		$end = DPCalendarHelper::getDateFromString($data['end_date'], $data['end_date_time'], $data['all_day'] == '1', $dateFormat, $timeFormat);
		if ($end->format('U') < $start->format('U'))
		{
			$end = clone $start;
			$end->modify('+30 min');
		}
		$data['end_date'] = $end->toSql(false);

		JRequest::setVar('jform', $data);
		$app->input->post->set('jform', $data);

		$result = false;
		if (! is_numeric($data['catid']))
		{
			JPluginHelper::importPlugin('dpcalendar');
			$data['id'] = JRequest::getVar($urlVar, null);
			$app->setUserState('com_dpcalendar.edit.event.data', $data);

			$model = $this->getModel();
			$form = $model->getForm($data, true);
			$validData = $model->validate($form, $data);

			if (isset($validData['all_day']) && $validData['all_day'] == 1)
			{
				$validData['start_date'] = DPCalendarHelper::getDate($validData['start_date'])->toSql(true);
				$validData['end_date'] = DPCalendarHelper::getDate($validData['end_date'])->toSql(true);
			}

			$tmp = JDispatcher::getInstance()->trigger('onEventSave', array(
					$validData
			));
			foreach ($tmp as $newEventId)
			{
				if ($newEventId === false)
				{
					continue;
				}

				$app->setUserState('dpcalendar.event.id', $newEventId);

				$result = true;
				$return = JRequest::getVar('return', null, 'default', 'base64');
				if (! empty($urlVar) && ! empty($return) && ! empty($data['id']))
				{
					$uri = base64_decode($return);
					$uri = str_replace($data['id'], $newEventId, $uri);
					JRequest::setVar('return', base64_encode($uri));
				}
			}
		}
		else
		{
			$result = parent::save($key, $urlVar);
		}
		// If ok, redirect to the return page.
		if ($result)
		{
			if ($this->getTask() == 'save')
			{
				$app->setUserState("$this->option.edit.$this->context" . '.data', null);
				$return = $this->getReturnPage();
				if ($return == JURI::base())
				{
					$return = DPCalendarHelper::getEventRoute($app->getUserState('dpcalendar.event.id'), $data['catid']);
				}
				$this->setRedirect($return);
			}
			if ($this->getTask() == 'apply')
			{
				$return = DPCalendarHelper::getFormRoute($app->getUserState('dpcalendar.event.id'), $this->getReturnPage());
				$this->setRedirect($return);
			}
			if ($this->getTask() == 'save2new')
			{
				$app->setUserState("$this->option.edit.$this->context" . '.data', null);
				$return = DPCalendarHelper::getFormRoute(0, $this->getReturnPage());
				$this->setRedirect($return);
			}
		}

		return $result;
	}
}
