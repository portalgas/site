<?php
// no direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__).'/helper.php';

$user = JFactory::getUser();

$app = JFactory::getApplication();
$params = $app->getTemplate(true)->params;
$organization_id = $params->get('organizationId');

// echo '<h1>mod_gas_organization_choice: organization_id '.$organization_id.' preso dal template</h1>';

$neo_portalgas_url = $app->getCfg('NeoPortalgasUrl');

$list = modGasOrganizationChoiceHelper::getList($params);
if (!empty($list)) {
	require JModuleHelper::getLayoutPath('mod_gas_organization_choice', $params->get('layout', 'default'));
}