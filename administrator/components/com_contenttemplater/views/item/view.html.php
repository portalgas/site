<?php
/**
 * Item View
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
 * Item View
 */
class ContentTemplaterViewItem extends JView
{
	protected $item;
	protected $form;
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

		$this->form = $this->get('Form');
		$this->item = $this->_models['item']->getItem(null, 1);
		$this->state = $this->get('State');
		$this->config = $this->parameters->getComponentParams('contenttemplater', $this->state->params);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->id == 0);
		$canDo = ContentTemplaterHelper::getActions();

		JFactory::getApplication()->input->set('hidemainmenu', true);

		// Set document title
		JFactory::getDocument()->setTitle(JText::_('CONTENT_TEMPLATER') . ' : ' . JText::_('NN_ITEM'));
		// Set ToolBar title
		JToolbarHelper::title(JText::_('NN_ITEM'), 'contenttemplater.png');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit')) {
			JToolbarHelper::apply('item.apply');
			JToolbarHelper::save('item.save');
		}

		if ($canDo->get('core.edit') && $canDo->get('core.create')) {
			JToolbarHelper::save2new('item.save2new');
		}
		if (!$isNew && $canDo->get('core.create')) {
			JToolbarHelper::save2copy('item.save2copy');
		}

		if (empty($this->item->id)) {
			JToolbarHelper::cancel('item.cancel');
		} else {
			JToolbarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
		}

		$bar = JToolBar::getInstance('toolbar');
		JToolbarHelper::divider();
		$bar->appendButton('Popup', 'help', 'JHELP', 'http://www.nonumber.nl/contenttemplater?ml=1', 'window.getSize().x-100', 'window.getSize().y-100');
	}

	protected function render(&$form, $name = '', $title = '')
	{
		$items = array();
		foreach ($form->getFieldset($name) as $field) {
			$items[] = $field->label . $field->input;
		}
		if (empty ($items)) {
			return '';
		}

		$html = array();
		if ($title) {
			$html[] = '<fieldset class="adminform">';
			$html[] = '<legend>' . $title . '</legend>';
		} else {
			$html[] = '<fieldset class="panelform">';
		}
		$html[] = '<ul class="adminformlist"><li>' . implode('</li><li>', $items) . '</li></ul>';
		$html[] = '</fieldset>';

		return implode('', $html);
	}
}
