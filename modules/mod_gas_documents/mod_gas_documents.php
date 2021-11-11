<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_random_image
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$user = JFactory::getUser();
// echo "<pre>"; print_r($user); echo "</pre>";
if(isset($user->organization) && !empty($user->organization)) {

	$app = JFactory::getApplication();
	$params = $app->getTemplate(true)->params;
	$organization_id = $params->get('organizationId');  // ottengo organization_id come parametro del template
	// echo "<pre>getTemplate(organizationId) \n"; print_r($organization_id); echo "</pre>";
	
	if($user->organization['Organization']['id']==$organization_id) {  // utente sul proprio GAS
   
		$organization_id = $user->organization['Organization']['id'];
		$db_documents	 = modGasDocumentsHelper::getDataBaseDocuments($organization_id);
		// echo "<pre>"; print_r($db_documents); echo "</pre>"; 

		$documents	     = modGasDocumentsHelper::getDocuments($db_documents);

		require JModuleHelper::getLayoutPath('mod_gas_documents', $params->get('layout', 'default'));
	}
}