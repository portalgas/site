<?php
// no direct access
defined('_JEXEC') or die;

echo ' ';

require_once JPATH_SITE . '/components/com_cake/app/Lib/UtilsModules.php';

$j_content_id = getJContentId();
// echo '<br />'.$j_content_id;
if($j_content_id!=0) {
	
	$app = JFactory::getApplication();

	require JModuleHelper::getLayoutPath('mod_gas_supplier_details', $params->get('layout', 'default'));
}