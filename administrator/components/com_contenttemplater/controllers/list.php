<?php
/**
 * List Controller
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

jimport('joomla.application.component.controlleradmin');

/**
 * List Controller
 */
class ContentTemplaterControllerList extends JControllerAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 */
	protected $text_prefix = 'NN';

	/**
	 * Method to update a record.
	 */
	public function activate()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$ids = JFactory::getApplication()->input->get('cid', array(), 'array');
		$name = JFactory::getApplication()->input->getString('name');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_REDIRECT_NO_ITEM_SELECTED'));
		} else {
			// Get the model.
			$model = $this->getModel();

			JArrayHelper::toInteger($ids);

			// Remove the list.
			if (!$model->activate($ids, $name)) {
				JError::raiseWarning(500, $model->getError());
			} else {
				$this->setMessage(JText::plural('NN_N_LINKS_UPDATED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_contenttemplater&view=list');
	}

	/**
	 * Proxy for getModel.
	 */
	public function getModel($name = 'Item', $prefix = 'ContentTemplaterModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Import Method
	 * Set layout to import
	 */
	function import()
	{
		$file = JRequest::getVar('file', '', 'files');

		if (!empty($file)) {
			if (isset($file['name'])) {
				// Get the model.
				$model = $this->getModel('List');
				$model_item = $this->getModel('Item');
				$model->import($model_item);
			} else {
				$msg = JText::_('CT_PLEASE_CHOOSE_A_VALID_FILE');
				$this->setRedirect('index.php?option=com_contenttemplater&view=list&layout=import', $msg);
			}
		} else {
			$this->setRedirect('index.php?option=com_contenttemplater&view=list&layout=import');
		}
	}

	/**
	 * Export Method
	 * Export the selected items specified by id
	 */
	function export()
	{
		$ids = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get the model.
		$model = $this->getModel('List');

		$model->export($ids);
	}

	/**
	 * Copy Method
	 * Copy all items specified by array cid
	 * and set Redirection to the list of items
	 */
	function copy()
	{
		$ids = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get the model.
		$model = $this->getModel('List');
		$model_item = $this->getModel('Item');

		$model->copy($ids, $model_item);
	}
}
