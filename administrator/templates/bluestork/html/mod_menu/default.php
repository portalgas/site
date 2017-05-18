<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;


/* j_usergroups , in menu sono ripetuti */
define('group_id_root',8);
define('group_id_root_supplier',24);

define('group_id_manager',10);
define('group_id_manager_delivery',20);
define('group_id_generic',60);

define('group_id_referent',18);
define('group_id_super_referent',19);

// referente cassa (pagamento degli utenti alla consegna)
define('group_id_cassiere',21);
define('group_id_referent_cassiere',41);

define('group_id_manager_des',36);
define('group_id_referent_des',38);
define('group_id_super_referent_des',37);
define('group_id_titolare_des_supplier',39);
define('group_id_des_supplier_all_gas', 51);

/*
 * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
* 		gestisce anche il pagamento del suo produttore
*/
define('group_id_referent_tesoriere',23);

// tesoriere (pagamento ai fornitori)
define('group_id_tesoriere',11);
define('group_id_storeroom',9);
define('group_id_user',2); 

// prodGasSupplier
define('prod_gas_supplier_manager',62);

// calendar events gasEvents
define('group_id_events',65);


/*
 * find se in uso il componente com_cake
 */
$componentIsComCake = false;
if(strpos($_SERVER['QUERY_STRING'], '&') == true) {
	$args = explode('&',  $_SERVER['QUERY_STRING']);
	foreach($args as $arg) {
		list($key, $value) = explode('=', $arg);
		
		if(isset($key) && $key == 'option') {
			if(isset($value) && $value == 'com_cake') {
				$componentIsComCake = true;
				break;
			}
		}
		
	}
}

if($componentIsComCake) {

	/*
	 * dati organization per profilare
	*/
	$user = JFactory::getUser();
	/*
	echo "<pre>";
	print_r($user);
	echo "</pre>";
	*/
	if(isset($user->organization['Organization'])) {
		$db = JFactory::getDbo();
		$sql = "SELECT
					id, name, type, paramsConfig, paramsFields
				FROM
		    		k_organizations
				WHERE id = ".$user->organization['Organization']['id'];
		$db->setQuery($sql);
		$results = $db->loadAssoc();
		$gasId = $results['id'];
		$gasName = $results['name'];
		$type = $results['type'];  // GAS, PROD
	
		$paramsConfig = json_decode($results['paramsConfig'], true);
		$paramsFields = json_decode($results['paramsFields'], true);
		$hasBookmarsArticles = $paramsConfig['hasBookmarsArticles'];
		$hasArticlesOrder = $paramsConfig['hasArticlesOrder'];
		$hasDes = $paramsConfig['hasDes'];
		$hasStoreroom = $paramsConfig['hasStoreroom'];
		$hasStoreroomFrontEnd = $paramsConfig['hasStoreroomFrontEnd'];
		$hasFieldArticleCategoryId = $paramsFields['hasFieldArticleCategoryId'];
	
		// 'BEFORE', 'ON', 'POST', 'ON-POST'
		$payToDelivery = $paramsConfig['payToDelivery'];
	
		/*
		 * ruoli
		* di default gasManager, gasManagerDelivery, gasReferente, gasSuperReferente, utenti
		*/
		// referente cassa (pagamento degli utenti alla consegna)
		$hasRolesCassiere = $paramsFields['hasRolesCassiere'];
	
		/*
		 * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
		 * 		gestisce anche il pagamento del suo produttore
		 */
		$hasRolesReferentTesoriere = $paramsFields['hasRolesReferentTesoriere'];
	
		// tesoriere per pagamento fornitori
		$hasRolesTesoriere = $paramsFields['hasRolesTesoriere'];
	
		// dispensa
		$hasRolesStoreroom = $paramsFields['hasRolesStoreroom'];
	}
	else
	if(isset($user->supplier['Supplier'])) {
		$db = JFactory::getDbo();
		$sql = "SELECT
					id, name
				FROM
		    		k_suppliers
				WHERE id = ".$user->supplier['Supplier']['id'];
		$db->setQuery($sql);
		$results = $db->loadAssoc();
		$gasId = $results['id'];
		$gasName = $results['name'];		
	}		
	else {
		$gasId = 0;
		$gasName = '';
		$hasBookmarsArticles = 'N';
		$hasArticlesOrder = 'N';
		$payToDelivery = 'ON';
		$hasDes = 'N';
		$hasStoreroom = 'N';
		$hasStoreroomFrontEnd = 'N';
		$hasFieldArticleCategoryId = 'N';
		$hasRolesCassiere = 'N';
		$hasRolesReferentTesoriere = 'N';
		$hasRolesTesoriere = 'N';
		$hasRolesStoreroom = 'N';
	}
	
	if(isset($user->supplier['Supplier'])) 
		require JModuleHelper::getLayoutPath('mod_menu', 'default_enabled_com_cake_prod_gas_supplier');
	else {
		if(in_array(group_id_root,$user->getAuthorisedGroups())) 
			require JModuleHelper::getLayoutPath('mod_menu', 'default_enabled_com_cake_root');
		
		if($type=='GAS')
			require JModuleHelper::getLayoutPath('mod_menu', 'default_enabled_com_cake_gas');
		else 
		if($type=='PROD')
			require JModuleHelper::getLayoutPath('mod_menu', 'default_enabled_com_cake_prod');
	}
}
else
	require JModuleHelper::getLayoutPath('mod_menu', $enabled ? 'default_enabled' : 'default_disabled');

$menu->renderMenu('menu', $enabled ? '' : 'disabled');
