<?php
/**
 * Element: AkeebaSubs
 * Displays a multiselectbox of available Akeeba Subsriptons levels
 *
 * @package         NoNumber Framework
 * @version         13.9.1
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2013 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once JPATH_PLUGINS . '/system/nnframework/helpers/functions.php';
require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
require_once JPATH_PLUGINS . '/system/nnframework/helpers/text.php';

class JFormFieldNN_AkeebaSubs extends JFormField
{
	public $type = 'AkeebaSubs';

	protected function getInput()
	{
		if (!NNFrameworkFunctions::extensionInstalled('akeebasubs')) {
			return '<fieldset class="radio"><label class="nn_label nn_label_error">' . JText::_('ERROR') . ': ' . JText::sprintf('NN_FILES_NOT_FOUND', JText::_('NN_AKEEBASUBS')) . '</label></fieldset>';
		}

		$this->params = $this->element->attributes();

		$group = $this->def('group', 'categories');

		$this->db = JFactory::getDBO();
		$tables = $this->db->getTableList();
		if (!in_array($this->db->getPrefix() . 'akeebasubs_' . $group, $tables)) {
			return '<fieldset class="radio"><label class="nn_label nn_label_error">' . JText::_('ERROR') . ': ' . JText::sprintf('NN_TABLE_NOT_FOUND', JText::_('NN_AKEEBASUBS')) . '</label></fieldset>';
		}

		$this->params = $this->element->attributes();

		$parameters = NNParameters::getInstance();
		$params = $parameters->getPluginParams('nnframework');
		$this->max_list_count = $params->max_list_count;

		if (!is_array($this->value)) {
			$this->value = explode(',', $this->value);
		}

		$options = $this->{'get' . $group}();

		$size = (int) $this->def('size');
		$multiple = $this->def('multiple');

		require_once JPATH_PLUGINS . '/system/nnframework/helpers/html.php';
		return nnHtml::selectlist($options, $this->name, $this->value, $this->id, $size, $multiple);
	}

	function getLevels()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(*)')
			->from('#__akeebasubs_levels AS l')
			->where('l.enabled > -1');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count) {
			return -1;
		}

		$query->clear()
			->select('l.akeebasubs_level_id as id, l.title AS name, l.enabled as published')
			->from('#__akeebasubs_levels AS l')
			->where('l.enabled > -1')
			->order('l.title, l.akeebasubs_level_id');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		// assemble items to the array
		$options = array();
		foreach ($list as $item) {
			$item->name = NNText::prepareSelectItem($item->name, $item->published);
			$options[] = JHtml::_('select.option', $item->id, $item->name, 'value', 'text', 0);
		}

		return $options;
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}
