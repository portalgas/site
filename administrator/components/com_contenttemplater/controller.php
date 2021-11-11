<?php
/**
 * Controller
 *
 * @package         Content Templater
 * @version         4.6.3
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2013 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

/**
 * Master Display Controller
 */
class ContentTemplaterController extends JController
{
	/**
	 * @var        string    The default view.
	 */
	protected $default_view = 'list';

	/**
	 * Method to display a view
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/helper.php';

		// Load the submenu.
		ContentTemplaterHelper::addSubmenu(JFactory::getApplication()->input->get('view', 'list'));

		$view = JFactory::getApplication()->input->get('view', 'list');
		$layout = JFactory::getApplication()->input->get('layout', 'default');
		$id = JFactory::getApplication()->input->getInt('id');

		// redirect to list if view is invalid
		if ($view != 'list' && $view != 'item') {
			$this->setRedirect(JRoute::_('index.php?option=com_contenttemplater', false));
			return false;
		}

		// Check for edit form.
		if ($view == 'item' && $layout == 'edit' && !$this->checkEditId('com_contenttemplater.edit.item', $id)) {
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_contenttemplater', false));

			return false;
		}

		parent::display();
	}
}
