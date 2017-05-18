<?php
/**
 * Helper
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

/**
 * Component Helper
 */
class ContentTemplaterHelper
{
	public static $extension = 'com_contenttemplater';

	/**
	 * Configure the Itembar.
	 *
	 * @param    string    The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		// No submenu for this component.
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return    JObject
	 */
	public static function getActions()
	{
		$user = JFactory::getUser();
		$result = new JObject;
		$assetName = 'com_contenttemplater';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @return    string            The HTML code for the select tag
	 */
	public static function publishedOptions()
	{
		// Build the active state filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '*', 'JALL');
		$options[] = JHtml::_('select.option', '1', 'JENABLED');
		$options[] = JHtml::_('select.option', '0', 'JDISABLED');
		$options[] = JHtml::_('select.option', '-2', 'JTRASHED');

		return $options;
	}

	/**
	 * Determines if the plugin for Content Templater to work is enabled.
	 *
	 * @return    boolean
	 */
	public static function isEnabled()
	{
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT `enabled`' .
				' FROM #__extensions' .
				' WHERE `folder` = ' . $db->quote('system') .
				'  AND `element` = ' . $db->quote('contenttemplater')
		);
		$result = ( boolean ) $db->loadResult();
		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
		}
		return $result;
	}
}
