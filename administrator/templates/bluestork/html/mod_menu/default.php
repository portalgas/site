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
define('group_id_user_manager_des', 77);
define('group_id_user_flag_privacy', 78);


/*
 * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
* 		gestisce anche il pagamento del suo produttore
*/
define('group_id_referent_tesoriere',23);

// tesoriere (pagamento ai fornitori)
define('group_id_tesoriere',11);
define('group_id_storeroom',9);
define('group_id_user',2); 

/*
 * altre tipologie di organization GAS PRODGAS PACT
 */
define('prod_gas_supplier_manager',62); // prodGasSupplier
define('group_pact_supplier_manager',84);        // manager pact 

// calendar events gasEvents
define('group_id_events',65);

// gas groups
define('group_id_gas_groups_manager_groups', 122);
define('group_id_gas_groups_manager_consegne',120);
define('group_id_gas_groups_manager_orders', 121);
define('group_id_gas_groups_manager_parent_orders', 123);

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
					k_organizations.id, k_organizations.name, k_organizations.type, k_organizations.paramsConfig, k_organizations.paramsFields,
					k_templates.orderUserPaid, k_templates.orderSupplierPaid, k_templates.payToDelivery 
				FROM
		    		k_organizations LEFT JOIN k_templates ON (k_templates.id = k_organizations.template_id)
				WHERE 
					k_organizations.id = ".$user->organization['Organization']['id'];
		$db->setQuery($sql);
		$results = $db->loadAssoc();	
		$organization_id = $results['id'];
		$organization_name = $results['name'];
		$organization_type = $results['type'];  // GAS, PRODGAS, PROD
	
		$paramsConfig = json_decode($results['paramsConfig'], true);
		$paramsFields = json_decode($results['paramsFields'], true);
		/*
		echo "<pre>";
		print_r($paramsConfig);
		echo "</pre>";
		*/
		/*
		 * produttori
		 */
		$hasPromotionGas = $paramsConfig['hasPromotionGas'];
		$hasPromotionGasUsers = $paramsConfig['hasPromotionGasUsers'];
				
		$hasArticlesGdxp = $paramsConfig['hasArticlesGdxp'];
		$hasBookmarsArticles = $paramsConfig['hasBookmarsArticles'];
		$hasDocuments = $paramsConfig['hasDocuments'];
		$hasArticlesOrder = $paramsConfig['hasArticlesOrder'];
		$hasDes = $paramsConfig['hasDes'];
		$hasDesUserManager = $paramsConfig['hasDesUserManager'];
		$hasStoreroom = $paramsConfig['hasStoreroom'];
		$hasStoreroomFrontEnd = $paramsConfig['hasStoreroomFrontEnd'];
		$hasUserFlagPrivacy = $paramsConfig['hasUserFlagPrivacy'];
		$hasUserRegistrationExpire = $paramsConfig['hasUserRegistrationExpire'];
		$hasGasGroups = $paramsConfig['hasGasGroups'];
		$hasCashFilterSupplier = $paramsConfig['hasCashFilterSupplier'];
		$hasFieldArticleCategoryId = $paramsFields['hasFieldArticleCategoryId'];

		// 'BEFORE', 'ON', 'POST', 'ON-POST' ora lo prendo da template
		// $payToDelivery = $paramsConfig['payToDelivery'];
		$payToDelivery = $results['payToDelivery'];
		
		// gas che gestisce il saldo dei gasisti
		$orderUserPaid = $results['orderUserPaid'];
		
		// gas che gestisce il pagamento dei gasisti
		$orderSupplierPaid = $results['orderSupplierPaid'];
	
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
	else {
		$organization_id = 0;
		$organization_name = '';
		$organization_type = '';
		$hasBookmarsArticles = 'N';
		$hasArticlesOrder = 'N';
		$payToDelivery = 'ON';
		$orderUserPaid = 'N';
		$orderSupplierPaid = 'N';
		$hasDes = 'N';
		$hasDesUserManager = 'N';
		$hasStoreroom = 'N';
		$hasStoreroomFrontEnd = 'N';
		$hasUserFlagPrivacy = 'N';
		$hasUserRegistrationExpire = 'N';
		$hasGasGroups = 'N';
		$hasFieldArticleCategoryId = 'N';
		$hasRolesCassiere = 'N';
		$hasRolesReferentTesoriere = 'N';
		$hasRolesTesoriere = 'N';
		$hasRolesStoreroom = 'N';
		$hasCashFilterSupplier = 'N';
		$hasPromotionGasUsers = 'N';
		$hasPromotionGas = 'N';
	}
	
	$_menus = [];
	$_menus_definitivo = [];

	switch($organization_type) {
        case 'GAS':
            require (dirname(__FILE__).'/default_enabled_com_cake_gas.php');
        break;
        case 'SOCIALMARKET':
			require (dirname(__FILE__).'/default_enabled_com_cake_socialmarket.php');
		break;
		case 'PRODGAS':
			require (dirname(__FILE__).'/default_enabled_com_cake_prod_gas_supplier.php');
		break;
		case 'PROD': // non utilizzato
			require (dirname(__FILE__).'/default_enabled_com_cake_prod.php');
		break;
		
	}	
	
	if(in_array(group_id_root,$user->getAuthorisedGroups())) {
		require (dirname(__FILE__).'/default_enabled_com_cake_root.php');
		
		$_menus_definitivo = array_merge($_menus_root, $_menus);
	}
	else {
		$_menus_definitivo = $_menus;
	}
		
	/*	
	echo "<pre>";
	print_r($_menus_definitivo);
	echo "</pre>";	
	*/
		
		
	/*
	 * H T M L
	 */
	echo '<div id="box-submenu">';
	echo '<nav class="navbar navbar-default">'; // style="background-color:#000000;"
	echo '<div class="container-fluid">';
	echo '<div class="navbar-header">';
	echo '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false">';
	echo '<span class="sr-only">Toggle navigation</span>';
	echo '<span class="icon-bar"></span>';
	echo '<span class="icon-bar"></span>';
	echo '<span class="icon-bar"></span>';
	echo '</button>';
	echo '<a class="navbar-brand" href="index.php?option=com_cake&amp;controller=Pages&amp;action=home"><img src="/images/cake/loghi/0/120x120.jpg" alt="PortAlGas" /></a>';
	echo '</div>';
	
	echo '<div class="collapse navbar-collapse" id="navbar">';
	echo '<ul class="nav navbar-nav">';
	 
	foreach($_menus_definitivo as $numResults => $_menu) {
	
		if(isset($_menu['separator'])) {
			echo '<li role="separator" class="divider"></li>';
		}
		else {	
			
			if(isset($_menu['target']))
				$target = $_menu['target'];
			else
				$target = '';
		
			if($_menu['level']==0) {
				echo '<li class="dropdown">';
				echo '	<a href="'.$_menu['url'].'" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$_menu['label'].' <span class="caret"></span></a>';
				echo '	<ul class="dropdown-menu">';
			}
			
			if($_menu['level']==1) {
				
				// $tmp .= 'Current level '.$_menu['level'].' '.$_menu['label'].' - successivo '.$_menus_definitivo[$numResults-1]['level'].' '.$_menus_definitivo[$numResults+1]['label'].'<br />';
				
				if(isset($_menus_definitivo[$numResults+1]['level']) && $_menus_definitivo[$numResults+1]['level']==2) {
					echo '<li class="dropdown-submenu">';
					echo '	<a href="'.$_menu['url'].'" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$_menu['label'].'</a>';
					echo '		<ul class="dropdown-menu">';
				}	
				else
					echo '<li><a href="'.$_menu['url'].'" target="'.$target.'">'.$_menu['label'].'</a></li>';
				
				if(isset($_menus_definitivo[$numResults+1]['level']) && $_menus_definitivo[$numResults+1]['level']==0) {
					echo '	</ul>';
					echo '</li>';
				}			
			}
			
			if($_menu['level']==2) {
				
				if(isset($_menus_definitivo[$numResults+1]['level']) && $_menus_definitivo[$numResults+1]['level']==3) {
					echo '<li class="dropdown-submenu">';
					echo '	<a href="'.$_menu['url'].'" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$_menu['label'].'</a>';
					echo '		<ul class="dropdown-menu">';
				}	
				else
					echo '<li><a href="'.$_menu['url'].'" target="'.$target.'">'.$_menu['label'].'</a></li>';
				
				if(isset($_menus_definitivo[$numResults+1]['level']) && $_menus_definitivo[$numResults+1]['level']==1) {
					echo '	</ul>';
					echo '</li>';
				}	
				else
				if(isset($_menus_definitivo[$numResults+1]['level']) && $_menus_definitivo[$numResults+1]['level']==0) {
					echo '	</ul>';
					echo '</li>';
					echo '	</ul>';
					echo '</li>';
				}			
			}
			
			if($_menu['level']==3) {
				echo '<li><a href="'.$_menu['url'].'" target="'.$target.'">'.$_menu['label'].'</a></li>';
				
				if(isset($_menus_definitivo[$numResults+1]['level']) && $_menus_definitivo[$numResults+1]['level']==2) {
					echo '	</ul>';
					echo '</li>';
				}	
				else
				if(isset($_menus_definitivo[$numResults+1]['level']) && $_menus_definitivo[$numResults+1]['level']==1) {
					echo '	</ul>';
					echo '</li>';
					echo '	</ul>';
					echo '</li>';
				}
				else
				if(isset($_menus_definitivo[$numResults+1]['level']) && $_menus_definitivo[$numResults+1]['level']==0) {
					echo '	</ul>';
					echo '</li>';
					echo '	</ul>';
					echo '</li>';
					echo '	</ul>';
					echo '</li>';
				}			
			}
		}
	}
	if(!empty($_menus_definitivo)) {
		echo '	</ul>';
		echo '</li>';
	
		echo '	</ul>';
		echo '</li>';
	}	
	
	echo '</ul>';	
	echo '<ul class="nav navbar-nav navbar-right">';
	
	echo '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">';
	echo '<i class="fa fa-lg fa-user-circle text-primary" aria-hidden="true"></i></a>';
	echo '<ul class="dropdown-menu">';
	echo '<li>';
	echo '<a href="index.php?option=com_admin&task=profile.edit&id='.$user->get('id').'">Il mio profilo</a>';
	echo '</li>';
	echo '</ul>';
	echo '</li>';
	
	echo '<li><a href="'.JURI::root().'" target="_blank" class="text-primary">Sito</a></li>';
	$task = JRequest::getCmd('task');
	if ($task == 'edit' || $task == 'editA' || JRequest::getInt('hidemainmenu')) {
		$logoutLink = '';
	} else {
		$logoutLink = JRoute::_('index.php?option=com_login&task=logout&'. JSession::getFormToken() .'=1');
	}
	$hideLinks	= JRequest::getBool('hidemainmenu');
	echo '<li>' .($hideLinks ? '' : '<a href="'.$logoutLink.'"><i class="fa fa-lg fa-power-off text-danger"></i> ').($hideLinks ? '' : '</a>').'</li>';
	echo ' </ul>';
	echo '</div>';
	echo '</div>';
	echo '</nav>';
	echo '</div>';	
}
else
	require JModuleHelper::getLayoutPath('mod_menu', $enabled ? 'default_enabled' : 'default_disabled');

$menu->renderMenu('menu', $enabled ? '' : 'disabled');
