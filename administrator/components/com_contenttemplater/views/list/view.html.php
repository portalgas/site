<?php
/**
 * List View
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

jimport('joomla.application.component.view');

/**
 * List View
 */
class ContentTemplaterViewList extends JView
{
	protected $enabled;
	protected $list;
	protected $pagination;
	protected $state;
	protected $config;
	protected $parameters;

	/**
	 * Display the view
	 *
	 */
	public function display($tpl = null)
	{
		require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
		$this->parameters = NNParameters::getInstance();

		$this->enabled = ContentTemplaterHelper::isEnabled();
		$this->list = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->config = $this->parameters->getComponentParams('contenttemplater', $this->state->params);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = ContentTemplaterHelper::getActions();

		$viewLayout = JFactory::getApplication()->input->get('layout', 'default');

		if ($viewLayout == 'import') {
			// Set document title
			JFactory::getDocument()->setTitle(JText::_('CONTENT_TEMPLATER') . ' : ' . JText::_('NN_IMPORT_ITEMS'));
			// Set ToolBar title
			JToolbarHelper::title(JText::_('NN_IMPORT_ITEMS'), 'contenttemplater.png');
			// Set toolbar items for the page
			JToolbarHelper::back();
		} else {
			// Set document title
			JFactory::getDocument()->setTitle(JText::_('CONTENT_TEMPLATER') . ' : ' . JText::_('NN_LIST'));
			// Set ToolBar title
			JToolbarHelper::title(JText::_('NN_LIST'), 'contenttemplater.png');
			// Set toolbar items for the page
			if ($canDo->get('core.create')) {
				JToolbarHelper::addNew('item.add');
				JToolbarHelper::custom('list.copy', 'copy.png', 'copy.png', 'JTOOLBAR_DUPLICATE', true);
			}
			if ($canDo->get('core.edit')) {
				JToolbarHelper::editList('item.edit');
			}
			if ($canDo->get('core.edit.state')) {
				if ($state->get('filter.state') != 2) {
					JToolbarHelper::divider();
					JToolbarHelper::publish('list.publish', 'JTOOLBAR_ENABLE', true);
					JToolbarHelper::unpublish('list.unpublish', 'JTOOLBAR_DISABLE', true);
				}
			}
			if ($canDo->get('core.create')) {
				JToolbarHelper::divider();
				JToolbarHelper::custom('list.export', 'export.png', 'export.png', 'NN_EXPORT');
				JToolbarHelper::custom('list.import', 'import.png', 'import.png', 'NN_IMPORT', false);
			}
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
				JToolbarHelper::divider();
				JToolbarHelper::deleteList('', 'list.delete', 'JTOOLBAR_EMPTY_TRASH');
			} else if ($canDo->get('core.edit.state')) {
				JToolbarHelper::divider();
				JToolbarHelper::trash('list.trash');
			}
			if ($canDo->get('core.admin')) {
				JToolbarHelper::divider();
				JToolbarHelper::preferences('com_contenttemplater');
			}

			$bar = JToolBar::getInstance('toolbar');
			JToolbarHelper::divider();
			$bar->appendButton('Popup', 'help', 'Help', 'http://www.nonumber.nl/contenttemplater?ml=1', 'window.getSize().x-100', 'window.getSize().y-100');
		}
	}

	function maxlen($string = '', $maxlen = 60)
	{
		if (JString::strlen($string) > $maxlen) {
			$string = JString::substr($string, 0, $maxlen - 3) . '...';
		}
		return $string;
	}
}
