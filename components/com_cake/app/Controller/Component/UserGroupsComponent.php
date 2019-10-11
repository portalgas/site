<?php 
App::uses('Component', 'Controller');

class UserGroupsComponent extends Component {

	private $debug = false;
	
    private $Controller = null;

    public function initialize(Controller $controller) 
    {
		$this->Controller = $controller;
    }
	
	public function getUserGroups($user) {

		$debug = false;
		$controllerLog = $this->Controller;
		
		$userGroups = [];

		/*
		 * gruppo non legato all'organization, se non sono Root in AppController lo elimino
		 */
		$userGroups[Configure::read('group_id_root_supplier')]['id'] = Configure::read('group_id_root_supplier');
		$userGroups[Configure::read('group_id_root_supplier')]['name'] = __('UserGroupsRootSupplier');
		$userGroups[Configure::read('group_id_root_supplier')]['descri'] = __('HasUserGroupsRootSupplier');
		$userGroups[Configure::read('group_id_root_supplier')]['join'] = '';
		$userGroups[Configure::read('group_id_root_supplier')]['type'] = 'GAS';
		
		$userGroups[Configure::read('group_id_manager')]['id'] = Configure::read('group_id_manager');
		$userGroups[Configure::read('group_id_manager')]['name'] = 'Manager';
		$userGroups[Configure::read('group_id_manager')]['descri'] = __('HasUserGroupsManager');
		$userGroups[Configure::read('group_id_manager')]['join'] = '';
		$userGroups[Configure::read('group_id_manager')]['type'] = 'GAS';
	
		if(isset($user->organization['Organization']) && 
		        ($user->organization['Organization']['hasStoreroom']=='Y' ||
				 $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') &&
				($user->organization['Organization']['hasUserGroupsStoreroom']=='Y')) {
			$userGroups[Configure::read('group_id_storeroom')]['id'] = Configure::read('group_id_storeroom');
			$userGroups[Configure::read('group_id_storeroom')]['name'] = __('UserGroupsStoreroom');
			$userGroups[Configure::read('group_id_storeroom')]['descri'] = __('HasUserGroupsStoreroom');
			$userGroups[Configure::read('group_id_storeroom')]['join'] = '';
			$userGroups[Configure::read('group_id_storeroom')]['type'] = 'GAS';
		}
		 
		if(isset($user->organization['Organization']) && $user->organization['Organization']['hasUserGroupsTesoriere']=='Y') {
			$userGroups[Configure::read('group_id_tesoriere')]['id'] = Configure::read('group_id_tesoriere');
			$userGroups[Configure::read('group_id_tesoriere')]['name'] = __('UserGroupsTesoriere');
			$userGroups[Configure::read('group_id_tesoriere')]['descri'] = __('HasUserGroupsTesoriere');
			$userGroups[Configure::read('group_id_tesoriere')]['join'] = '';
			$userGroups[Configure::read('group_id_tesoriere')]['type'] = 'GAS';
		}
		
		$userGroups[Configure::read('group_id_manager_delivery')]['id'] = Configure::read('group_id_manager_delivery');
		$userGroups[Configure::read('group_id_manager_delivery')]['name'] = __('UserGroupsManagerDelivery');
		$userGroups[Configure::read('group_id_manager_delivery')]['descri'] = __('HasUserGroupsManagerDelivery');
		$userGroups[Configure::read('group_id_manager_delivery')]['join'] = '';
		$userGroups[Configure::read('group_id_manager_delivery')]['type'] = 'GAS';
		
		$userGroups[Configure::read('group_id_referent')]['id'] = Configure::read('group_id_referent');
		$userGroups[Configure::read('group_id_referent')]['name'] = __('UserGroupsReferent');
		$userGroups[Configure::read('group_id_referent')]['descri'] = __('HasUserGroupsReferent');
		$userGroups[Configure::read('group_id_referent')]['join'] = 'Supplier';
		$userGroups[Configure::read('group_id_referent')]['type'] = 'GAS';
		
		$userGroups[Configure::read('group_id_super_referent')]['id'] = Configure::read('group_id_super_referent');
		$userGroups[Configure::read('group_id_super_referent')]['name'] = __('UserGroupsSuperReferent');
		$userGroups[Configure::read('group_id_super_referent')]['descri'] = __('HasUserGroupsSuperReferent');
		$userGroups[Configure::read('group_id_super_referent')]['join'] = '';
		$userGroups[Configure::read('group_id_super_referent')]['type'] = 'GAS';
		
		$userGroups[Configure::read('group_id_generic')]['id'] = Configure::read('group_id_generic');
		$userGroups[Configure::read('group_id_generic')]['name'] = __('UserGroupsGeneric');
		$userGroups[Configure::read('group_id_generic')]['descri'] = __('HasUserGroupsGeneric');
		$userGroups[Configure::read('group_id_generic')]['join'] = '';
		$userGroups[Configure::read('group_id_generic')]['type'] = 'GAS';
		
		/*
		 * gestore calendar.events
		 */
		$userGroups[Configure::read('group_id_events')]['id'] = Configure::read('group_id_events');
		$userGroups[Configure::read('group_id_events')]['name'] = __('UserGroupsEvents');
		$userGroups[Configure::read('group_id_events')]['descri'] = __('HasUserGroupsEvents');
		$userGroups[Configure::read('group_id_events')]['join'] = '';
		$userGroups[Configure::read('group_id_events')]['type'] = 'GAS';
		
		
		/*
		 * referente cassa (pagamento degli utenti alla consegna)
		 */ 
		if(isset($user->organization['Organization']) && $user->organization['Template']['payToDelivery']=='ON') {
			if($user->organization['Organization']['hasUserGroupsCassiere']=='Y') {
				$userGroups[Configure::read('group_id_cassiere')]['id'] = Configure::read('group_id_cassiere');
				$userGroups[Configure::read('group_id_cassiere')]['name'] = __('UserGroupsCassiere');
				$userGroups[Configure::read('group_id_cassiere')]['descri'] = __('HasUserGroupsCassiere');
				$userGroups[Configure::read('group_id_cassiere')]['join'] = '';
				$userGroups[Configure::read('group_id_cassiere')]['type'] = 'GAS';
				
				$userGroups[Configure::read('group_id_referent_cassiere')]['id'] = Configure::read('group_id_referent_cassiere');
				$userGroups[Configure::read('group_id_referent_cassiere')]['name'] = __('UserGroupsReferentCassiere');
				$userGroups[Configure::read('group_id_referent_cassiere')]['descri'] = __('HasUserGroupsReferentCassiere');
				$userGroups[Configure::read('group_id_referent_cassiere')]['join'] = '';
				$userGroups[Configure::read('group_id_referent_cassiere')]['type'] = 'GAS';
			}
		}
		
		/*
		 * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
		 * 		gestisce anche il pagamento del suo produttore
		 */ 
		if(isset($user->organization['Organization']) && $user->organization['Template']['payToDelivery']=='POST') {
			if($user->organization['Organization']['hasUserGroupsCassiere']=='Y') {
				$userGroups[Configure::read('group_id_cassiere')]['id'] = Configure::read('group_id_cassiere');
				$userGroups[Configure::read('group_id_cassiere')]['name'] = __('UserGroupsCassiere');
				$userGroups[Configure::read('group_id_cassiere')]['descri'] = __('HasUserGroupsCassiere');
				$userGroups[Configure::read('group_id_cassiere')]['join'] = '';
				$userGroups[Configure::read('group_id_cassiere')]['type'] = 'GAS';
				
				$userGroups[Configure::read('group_id_referent_cassiere')]['id'] = Configure::read('group_id_referent_cassiere');
				$userGroups[Configure::read('group_id_referent_cassiere')]['name'] = __('UserGroupsReferentCassiere');
				$userGroups[Configure::read('group_id_referent_cassiere')]['descri'] = __('HasUserGroupsReferentCassiere');
				$userGroups[Configure::read('group_id_referent_cassiere')]['join'] = '';
				$userGroups[Configure::read('group_id_referent_cassiere')]['type'] = 'GAS';				
			}
			
			$userGroups[Configure::read('group_id_referent_tesoriere')]['id'] = Configure::read('group_id_referent_tesoriere');
			$userGroups[Configure::read('group_id_referent_tesoriere')]['name'] = __('UserGroupsReferentTesoriere');
			$userGroups[Configure::read('group_id_referent_tesoriere')]['descri'] = __('HasUserGroupsReferentTesoriere');
			$userGroups[Configure::read('group_id_referent_tesoriere')]['join'] = 'Supplier';
			$userGroups[Configure::read('group_id_referent_tesoriere')]['type'] = 'GAS';
		}
		

		if(isset($user->organization['Organization']) && $user->organization['Template']['payToDelivery']=='ON-POST') {
			if($user->organization['Organization']['hasUserGroupsCassiere']=='Y') {
				$userGroups[Configure::read('group_id_cassiere')]['id'] = Configure::read('group_id_cassiere');
				$userGroups[Configure::read('group_id_cassiere')]['name'] = __('UserGroupsCassiere');
				$userGroups[Configure::read('group_id_cassiere')]['descri'] = __('HasUserGroupsCassiere');
				$userGroups[Configure::read('group_id_cassiere')]['join'] = '';
				$userGroups[Configure::read('group_id_cassiere')]['type'] = 'GAS';
				
				$userGroups[Configure::read('group_id_referent_cassiere')]['id'] = Configure::read('group_id_referent_cassiere');
				$userGroups[Configure::read('group_id_referent_cassiere')]['name'] = __('UserGroupsReferentCassiere');
				$userGroups[Configure::read('group_id_referent_cassiere')]['descri'] = __('HasUserGroupsReferentCassiere');
				$userGroups[Configure::read('group_id_referent_cassiere')]['join'] = '';
				$userGroups[Configure::read('group_id_referent_cassiere')]['type'] = 'GAS';					
			}			
			if($user->organization['Organization']['hasUserGroupsReferentTesoriere']=='Y') {
				$userGroups[Configure::read('group_id_referent_tesoriere')]['id'] = Configure::read('group_id_referent_tesoriere');
				$userGroups[Configure::read('group_id_referent_tesoriere')]['name'] = __('UserGroupsReferentTesoriere');
				$userGroups[Configure::read('group_id_referent_tesoriere')]['descri'] = __('HasUserGroupsReferentTesoriere');
				$userGroups[Configure::read('group_id_referent_tesoriere')]['join'] = 'Supplier';
				$userGroups[Configure::read('group_id_referent_tesoriere')]['type'] = 'GAS';
			}
		}
		
   		if(isset($user->organization['Organization']) && $user->organization['Organization']['hasDes']=='Y') {		
			$userGroups[Configure::read('group_id_manager_des')]['id'] = Configure::read('group_id_manager_des');
			$userGroups[Configure::read('group_id_manager_des')]['name'] =  __('UserGroupsManagerDes');
			$userGroups[Configure::read('group_id_manager_des')]['descri'] = __('HasUserGroupsManagerDes');
			$userGroups[Configure::read('group_id_manager_des')]['join'] = '';
			$userGroups[Configure::read('group_id_manager_des')]['type'] = 'DES';

			$userGroups[Configure::read('group_id_titolare_des_supplier')]['id'] = Configure::read('group_id_titolare_des_supplier');
			$userGroups[Configure::read('group_id_titolare_des_supplier')]['name'] = __('UserGroupsReferentTitolareDes');
			$userGroups[Configure::read('group_id_titolare_des_supplier')]['descri'] = __('HasUserGroupsReferentTitolareDes');
			$userGroups[Configure::read('group_id_titolare_des_supplier')]['join'] = 'DesSupplier';
			$userGroups[Configure::read('group_id_titolare_des_supplier')]['type'] = 'DES';
		
			$userGroups[Configure::read('group_id_des_supplier_all_gas')]['id'] = Configure::read('group_id_des_supplier_all_gas');
			$userGroups[Configure::read('group_id_des_supplier_all_gas')]['name'] = __('UserGroupsReferentDesAllGas');
			$userGroups[Configure::read('group_id_des_supplier_all_gas')]['descri'] = __('HasUserGroupsReferentDesAllGas');
			$userGroups[Configure::read('group_id_des_supplier_all_gas')]['join'] = 'DesSupplier';
			$userGroups[Configure::read('group_id_des_supplier_all_gas')]['type'] = 'DES';
		
			$userGroups[Configure::read('group_id_super_referent_des')]['id'] = Configure::read('group_id_super_referent_des');
			$userGroups[Configure::read('group_id_super_referent_des')]['name'] = __('UserGroupsSuperReferentDes');
			$userGroups[Configure::read('group_id_super_referent_des')]['descri'] = __('HasUserGroupsSuperReferentDes');
			$userGroups[Configure::read('group_id_super_referent_des')]['join'] = '';
			$userGroups[Configure::read('group_id_super_referent_des')]['type'] = 'DES';

			$userGroups[Configure::read('group_id_referent_des')]['id'] = Configure::read('group_id_referent_des');
			$userGroups[Configure::read('group_id_referent_des')]['name'] = __('UserGroupsReferentDes');
			$userGroups[Configure::read('group_id_referent_des')]['descri'] = __('HasUserGroupsReferentDes');
			$userGroups[Configure::read('group_id_referent_des')]['join'] = 'DesSupplier';
			$userGroups[Configure::read('group_id_referent_des')]['type'] = 'DES';

			if(isset($user->organization['Organization']) && $user->organization['Organization']['hasUserFlagPrivacy'] && $user->organization['Organization']['hasUserFlagPrivacy']=='Y') {
				$userGroups[Configure::read('group_id_user_flag_privacy')]['id'] = Configure::read('group_id_user_flag_privacy');
				$userGroups[Configure::read('group_id_user_flag_privacy')]['name'] = __('UserGroupsUserFlagPrivacy');
				$userGroups[Configure::read('group_id_user_flag_privacy')]['descri'] = __('HasUserGroupsUserFlagPrivacy');
				$userGroups[Configure::read('group_id_user_flag_privacy')]['join'] = '';
				$userGroups[Configure::read('group_id_user_flag_privacy')]['type'] = 'GAS';				
			}

			if(isset($user->organization['Organization']) && $user->organization['Organization']['hasDesUserManager']=='Y') {
				$userGroups[Configure::read('group_id_user_manager_des')]['id'] = Configure::read('group_id_user_manager_des');
				$userGroups[Configure::read('group_id_user_manager_des')]['name'] = __('UserGroupsUserManagerDes');
				$userGroups[Configure::read('group_id_user_manager_des')]['descri'] = __('HasUserGroupsUserManagerDes');
				$userGroups[Configure::read('group_id_user_manager_des')]['join'] = '';
				$userGroups[Configure::read('group_id_user_manager_des')]['type'] = 'DES';				
			}
			
   		}		

		$controllerLog::d($userGroups, $debug);
		
		return $userGroups;
	}
}
?>