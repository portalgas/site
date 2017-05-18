<?php
// no direct access
defined('_JEXEC') or die;

$debug = false;

require_once dirname(__FILE__).'/helper.php';
require_once JPATH_SITE . '/components/com_cake/app/Lib/UtilsModules.php';

$j_content_id = getJContentId();
if($debug) echo '<br />'.$j_content_id;

if($j_content_id!=0) {
	
	$app = JFactory::getApplication();	$params = $app->getTemplate(true)->params;

	$supplier = modGasSupplierHelper::getItem($j_content_id, $debug);
	
	if($debug)  {
		echo "<pre>";
		print_r($supplier);
		echo "</pre>";
	}
	
	if (!empty($supplier)) 
		require JModuleHelper::getLayoutPath('mod_gas_supplier_gmaps', $params->get('layout', 'default'));
}
