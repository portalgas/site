<?php
/**
 * Element: Group Level
 * Displays a select box of backend group levels
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

require_once JPATH_PLUGINS . '/system/nnframework/helpers/text.php';

class JFormFieldNN_GroupLevel extends JFormField
{
	public $type = 'GroupLevel';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$root = $this->def('root', 'USERS');
		$size = (int) $this->def('size');
		$multiple = $this->def('multiple');
		$show_all = $this->def('show_all');

		$attribs = 'class="inputbox"';

		$groups = $this->getUserGroups();
		$options = array();
		if ($show_all) {
			$option = new stdClass;
			$option->value = -1;
			$option->text = '- ' . JText::_('JALL') . ' -';
			$option->disable = '';
			$options[] = $option;
		}

		foreach ($groups as $group) {
			$option = new stdClass;
			$option->value = $group->value;
			$option->text = $group->text;

			$repeat = $show_all ? $group->level + 1 : $group->level;
			$option->text = str_repeat('- ', $repeat) . $option->text;
			$option->text = NNText::prepareSelectItem($option->text);

			$option->disable = '';
			$options[] = $option;
		}

		require_once JPATH_PLUGINS . '/system/nnframework/helpers/html.php';
		return nnHtml::selectlist($options, $this->name, $this->value, $this->id, $size, $multiple, $attribs);
	}

	protected function getUserGroups()
	{
		$db = JFactory::getDBO();

		// Get the user groups from the database.
		$query = $db->getQuery(true)
			->select('a.id as value, a.title as text, a.parent_id AS parent')
			->from('#__usergroups AS a')
			->select('COUNT(DISTINCT b.id) AS level')
			->join('LEFT', '#__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->group('a.id')
			->order('a.lft ASC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}
