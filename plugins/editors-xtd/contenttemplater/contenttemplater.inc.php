<?php
/**
 * Main Component File
 * Used for the editor button (template xml)
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

// Load common functions
require_once JPATH_PLUGINS . '/system/nnframework/helpers/functions.php';

$user = JFactory::getUser();
if ($user->get('guest')) {
	JError::raiseError(403, JText::_("ALERTNOTAUTH"));
}

if (JFactory::getApplication()->isSite()) {
	$params = JComponentHelper::getParams('com_contenttemplater');
	if (!$params->get('enable_frontend', 1)) {
		JError::raiseError(403, JText::_("ALERTNOTAUTH"));
	}
}

$class = new plgButtonContentTemplaterData;
$class->render();
die;

class plgButtonContentTemplaterData
{
	function render()
	{
		header('Content-Type: text/html; charset=utf-8');

		$id = JFactory::getApplication()->input->getInt('id');

		if (!$id) {
			return;
		}

		$nocontent = JFactory::getApplication()->input->getInt('nocontent', 0);
		$unprotected = (JFactory::getUser()->authorise('core.manage', 'com_contenttemplater')) ? JFactory::getApplication()->input->getInt('unprotect') : 0;

		require_once JPATH_ADMINISTRATOR . '/components/com_contenttemplater/models/item.php';

		// Create a new class of classname and set the default task: display
		$model = new ContentTemplaterModelItem;
		$item = $model->getItem($id, 0, 1);

		$output = array();

		$ignore = array(
			'view_state',
			'id',
			'ordering',
			'name',
			'description',
			'ordering',
			'published',
			'checked_out',
			'checked_out_time',
			'show_url_field_sef',
			'show_url_field',
			'match_method',
			'show_assignments',
			'defaults'
		);

		foreach ($item as $key => $val) {
			if ($val != ''
				&& !isset($output[$key])
				&& !in_array($key, $ignore)
				&& strpos($key, '@') !== 0
				&& strpos($key, 'button_') !== 0
				&& strpos($key, 'load_') !== 0
				&& strpos($key, 'url_') !== 0
				&& strpos($key, 'assignto_') !== 0
			) {
				if ($key == 'content' && $nocontent) {
					continue;
				}
					$default = '';
					if (isset($item->defaults->$key)) {
						$default = $item->defaults->$key;
					}
					if ($val != $default) {
						if (strpos($key, 'jform_') === 0 && $val == -2) {
							$val = '';
						}
						list($key, $val) = $this->getStr($model, $key, $val, $default);
						$output[$key] = $val;
					}
			}
		}


		$str = implode("\n", $output);
		if (!$unprotected) {
			$str = base64_encode($str);
			$str = wordwrap($str, 80, "\n", 1);
		}
		echo $str;
	}

	function getStr(&$item, $key, $val, $default = '')
	{
		switch ($key) {
			case 'jform_access':
				$default = 1;
				break;
			case 'jform_categories_k2':
				$key = 'catid';
				$default = 0;
				break;
			case 'jform_categories_zoo':
				$key = 'categories';
				$default = '';
				break;
		}
		if (is_array($val)) {
			$val = implode(',', $val);
		}
		if ($key != 'content') {
			$val = html_entity_decode($val, ENT_QUOTES, 'UTF-8');
		}
		$item->replaceVars($val);

		return array($key, '[CT]' . $key . '[CT]' . $default . '[CT]' . $val . '[/CT]');
	}
}
