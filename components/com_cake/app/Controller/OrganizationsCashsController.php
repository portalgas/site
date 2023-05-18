<?php
App::uses('AppController', 'Controller');

class OrganizationsCashsController extends AppController {
	
	private $_gas_group_id = 0;
	private $_gas_group_user_ids = []; // ids_user del gruppo  

	public function beforeFilter() {
		parent::beforeFilter();

		/* ctrl ACL */
	   	$actionWithPermission = ['admin_index'];
	   	if (in_array($this->action, $actionWithPermission)) {
			if(!$this->isManager()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}
		
		$actionWithPermission = ['admin_ctrl'];
	   	if (in_array($this->action, $actionWithPermission)) {
			if(!$this->isManager() && !$this->isCassiere()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}		
		/* ctrl ACL */

		/*
		 * GasGroup
		 * gas_group_id scelto e messo in session in GasGroupsController::admin_choice
		 */
		if(isset($this->user->organization['Organization']['hasGasGroups']) &&
			$this->user->organization['Organization']['hasGasGroups']=='Y') {
			$this->_gas_group_id = $this->Session->read('gas_group_id');

			App::import('Model', 'GasGroupUser');
			$GasGroupUser = new GasGroupUser;				
			$this->_gas_group_user_ids = $GasGroupUser->getsListUserByGasGroupId($this->user, $this->user->organization['Organization']['id'], $this->_gas_group_id);
			// debug($this->_gas_group_user_ids);
		}
	}

	public function admin_index() {

		$debug = false;
				
		$esito = true;
		if ($this->request->is('post') || $this->request->is('put')) {
		
			self::d("OrganizationsCashsController", $debug);
			self::d($this->request->data['OrganizationsCash'], $debug);

			$options = [];
			$options['conditions'] = ['OrganizationsCash.id' => $this->user->organization['Organization']['id']];
			$options['recursive'] = 1;
			$results = $this->OrganizationsCash->find('first', $options);
			$paramsConfig = json_decode($results['OrganizationsCash']['paramsConfig'], true);
		
			$paramsConfig['cashLimit'] = $this->request->data['OrganizationsCash']['cashLimit'];
			$paramsConfig['limitCashAfter'] = $this->request->data['OrganizationsCash']['limitCashAfter'];
			$results['OrganizationsCash']['paramsConfig'] = json_encode($paramsConfig);

			$this->OrganizationsCash->create();
			if ($this->OrganizationsCash->save($results)) {
				
				foreach($this->request->data['OrganizationsCash']['limit_type'] as $user_id => $limit_type) {
					
					$esito = $this->OrganizationsCash->popolaCashesUser($this->user, $user_id, $this->request->data['OrganizationsCash']['limit_after'][$user_id], $limit_type, $debug); 
				}
			} else {
				$esito = false;
			}

			if(!$esito)
				$this->Session->setFlash(__('The organizationsCash could not be saved. Please, try again.'));
			else
				$this->Session->setFlash(__('The organizationsCash has been saved'));


		} // POST

		$options = [];
		$options['conditions'] = ['OrganizationsCash.id' => $this->user->organization['Organization']['id']];
		$options['recursive'] = 1;

		/*
		* filtro per gli utenti associati al gruppo 
		*/
		if(isset($this->user->organization['Organization']['hasGasGroups']) &&
			$this->user->organization['Organization']['hasGasGroups']=='Y') {
				
			if(empty($this->_gas_group_user_ids))
				$ids = [0 => 0, 1 => 0]; // gruppo senza utenti => invaldo l'sql per non avere risultati
			else 
				$ids = array_keys($this->_gas_group_user_ids);
				
			$this->OrganizationsCash->bindModel(['hasMany' => 
				['User' => ['className' => 'User',
							'foreignKey' => 'organization_id',
							'conditions' => ['User.id IN ' => $ids],
							'order' => Configure::read('orderUser')]]
						]);
		}

		$results = $this->OrganizationsCash->find('first', $options);
		$paramsConfig = json_decode($results['OrganizationsCash']['paramsConfig'], true);		
		$results['OrganizationsCash'] += $paramsConfig;
		
		/*
	     * merge CashesUser e User
		 */
		 if(!empty($results['CashesUser'])) {
			 foreach($results['User'] as $numResult => $user) {
				 foreach($results['CashesUser'] as $numResult2 => $cashesUser) {
					 if($user['id']==$cashesUser['user_id']) {
						 $results['User'][$numResult]['limit_after'] = $cashesUser['limit_after'];
						 $results['User'][$numResult]['limit_after_'] = number_format($cashesUser['limit_after'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						 $results['User'][$numResult]['limit_after_e'] = $results['User'][$numResult]['limit_after_'].'&nbsp;&euro;';
						 $results['User'][$numResult]['limit_type'] = $cashesUser['limit_type'];
						 
						 unset($results['CashesUser'][$numResult2]);
					 } 
				 }
			 }
		 }
	
		self::d($results, $debug);
			
		$limit_type = ClassRegistry::init('CashesUser')->enumOptions('limit_type');
		$this->set(compact('limit_type'));
		
		App::import('Model', 'Organization');
		$Organization = new Organization;
		
		$cashLimits = $Organization->getCashLimit();
	
        $this->set('results', $results);
		$this->set('cashLimits', $cashLimits);	
	}
	
	/*
	 * ctrl se tutti gli user sono in CashesUser per gestire quelli inseriti dopo configurazione
	 */
	private function _ctrlCashesUser($user, $organization_id, $debug=false) {		

		App::import('Model', 'CashesUser');
		$CashesUser = new CashesUser;
		
		App::import('Model', 'Cash');
		$Cash = new Cash;
							
		$options = [];
		$options['conditions'] = ['OrganizationsCash.id' => $organization_id];
		$options['recursive'] = 1;
		$results = $this->OrganizationsCash->find('first', $options);
		
		$paramsConfig = json_decode($results['OrganizationsCash']['paramsConfig']);
		$limit_after = $paramsConfig->limitCashAfter;
		$limit_type = $paramsConfig->cashLimit;

		/*
		* filtro per gli utenti associati al gruppo 
		*/
		if(isset($this->user->organization['Organization']['hasGasGroups']) &&
			$this->user->organization['Organization']['hasGasGroups']=='Y') {
				
			if(empty($this->_gas_group_user_ids))
				$ids = [0 => 0, 1 => 0]; // gruppo senza utenti => invaldo l'sql per non avere risultati
			else 
				$ids = array_keys($this->_gas_group_user_ids);
				
			$this->OrganizationsCash->bindModel(['hasMany' => 
				['User' => ['className' => 'User',
							'foreignKey' => 'organization_id',
							'conditions' => ['User.id IN ' => $ids],
							'order' => Configure::read('orderUser')]]
						]);
		}

		if(!empty($results['CashesUser'])) {
			foreach($results['User'] as $utente) {

				/*
				 * se non lo trovo e' uno user creato dopo e inserisco in CashesUser
				 */ 
				$found_user = false;

				foreach($results['CashesUser'] as $numResult2 => $cashesUser) {
					if($utente['id']==$cashesUser['user_id']) {
					 	$found_user = true;
					 	unset($results['CashesUser'][$numResult2]);
					} // end if($user['id']==$cashesUser['user_id']) 
				 } // loop CashsUser

				 if(!$found_user) {
				 	// debug($utente['id']);
				 	$esito = $this->OrganizationsCash->popolaCashesUser($user, $utente['id'], $limit_after, $limit_type, $debug); 
				 } // end if(!$found_user)
			 } // loop User
		}

		return $esito;
 	}

	public function admin_ctrl() {

		$debug = false;

        $FilterOrganizationsCashUserId = 0;

        /* recupero dati dalla Session gestita in appController::beforeFilter */
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'UserId')) {
            $FilterOrganizationsCashUserId = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'UserId');
        }
        /* filtro */
        $this->set('FilterOrganizationsCashUserId', $FilterOrganizationsCashUserId);

		/*
		 * elenco utenti
		 */
		/*
		* filtro per gli utenti associati al gruppo 
		*/
		if(isset($this->user->organization['Organization']['hasGasGroups']) &&
			$this->user->organization['Organization']['hasGasGroups']=='Y') {
				$users = $this->_gas_group_user_ids;
		}
		else {
			App::import('Model', 'User');
			$User = new User;
			
			$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user')];
			$users = $User->getUsersList($this->user, $conditions);	
		}
		$this->set('users',$users);

		App::import('Model', 'CashesUser');
		$CashesUser = new CashesUser;
		
		App::import('Model', 'Cash');
		$Cash = new Cash;
				
		/*
		 * gestione user nuovi e non ancora inseriti in CashesUser
		 */
		$this->_ctrlCashesUser($this->user, $this->user->organization['Organization']['id'], $debug);
			
		$options = [];
		$options['conditions'] = ['OrganizationsCash.id' => $this->user->organization['Organization']['id']];
		if(!empty($FilterOrganizationsCashUserId)) {
			$hasManyCashesUser = ['className' => 'CashesUser',
								  'foreignKey' => 'organization_id',
                    			  'conditions' => 'user_id = '.$FilterOrganizationsCashUserId];
			$hasManyUser = ['className' => 'User',
								  'foreignKey' => 'organization_id',
                    			  'conditions' => 'id = '.$FilterOrganizationsCashUserId];
			$this->OrganizationsCash->bindModel(['hasMany' => 
				['CashesUser' => $hasManyCashesUser,
				 'User' => $hasManyUser]
			]);
		}
		$options['recursive'] = 1;
		$results = $this->OrganizationsCash->find('first', $options);
		// debug($results);
		$paramsConfig = json_decode($results['OrganizationsCash']['paramsConfig'], true);		
		$results['OrganizationsCash'] += $paramsConfig;
		
		/*
	     * merge CashesUser e User
		 */
		 if(!empty($results['CashesUser'])) {
			
			foreach($results['User'] as $numResult => $user) {

				/*
				 * se non lo trovo e' uno user creato dopo e inserisco in CashesUser
				 */ 
				$found_user = false;

				foreach($results['CashesUser'] as $numResult2 => $cashesUser) {
					if($user['id']==$cashesUser['user_id']) {

					 	$found_user = true;

						$results['User'][$numResult]['limit_after'] = $cashesUser['limit_after'];
						$results['User'][$numResult]['limit_after_'] = number_format($cashesUser['limit_after'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$results['User'][$numResult]['limit_after_e'] = $results['User'][$numResult]['limit_after_'].'&nbsp;&euro;';
						$results['User'][$numResult]['limit_type'] = $cashesUser['limit_type'];
						 
						/*
						 * totale cassa per l'utente
						 */
						$user_cash = $Cash->getTotaleCashToUser($this->user, $user['id']);
		
						$results['User'][$numResult]['user_cash'] = $user_cash;
					    $results['User'][$numResult]['user_cash_'] = number_format($user_cash ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$results['User'][$numResult]['user_cash_e'] = number_format($user_cash ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';

						/*
						 * totale importo acquisti
						 */
						$user_tot_importo_acquistato = $CashesUser->getTotImportoAcquistato($this->user, $user['id']);
					    $results['User'][$numResult]['user_tot_importo_acquistato'] = $user_tot_importo_acquistato;
						$results['User'][$numResult]['user_tot_importo_acquistato_'] = number_format($user_tot_importo_acquistato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$results['User'][$numResult]['user_tot_importo_acquistato_e'] = $results['User'][$numResult]['user_tot_importo_acquistato_'].'&nbsp;&euro;'; 
						 
						$results['User'][$numResult]['ctrl_limit'] = $CashesUser->ctrlLimit($this->user, $results['OrganizationsCash']['cashLimit'], $results['OrganizationsCash']['limitCashAfter'], $cashesUser, $user_cash, $user_tot_importo_acquistato, $debug);
						  
						unset($results['CashesUser'][$numResult2]);
					} // end if($user['id']==$cashesUser['user_id']) 
				 } // loop CashsUser

				 if(!$found_user) {
				 	// debug($user['id']);
				 } // end if(!$found_user)
			 } // loop User
		 }
		 else {
			 foreach($results['User'] as $numResult => $user) {

				 /*
				  * totale cassa
				  */
				 $results['User'][$numResult]['user_cash'] = $this->user->get('user_cash');
				 $results['User'][$numResult]['user_cash_'] = $this->user->get('user_cash_');
				 $results['User'][$numResult]['user_cash_e'] = $this->user->get('user_cash_e');
				 
				 /*
				  * totale importo acquisti
				  */
				 $user_tot_importo_acquistato = $CashesUser->getTotImportoAcquistato($this->user, $user['id']);
			     $results['User'][$numResult]['user_tot_importo_acquistato'] = $user_tot_importo_acquistato;
				 $results['User'][$numResult]['user_tot_importo_acquistato_'] = number_format($user_tot_importo_acquistato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				 $results['User'][$numResult]['user_tot_importo_acquistato_e'] = $results['User'][$numResult]['user_tot_importo_acquistato_'].'&nbsp;&euro;'; 
				 
				 $results['User'][$numResult]['ctrl_limit'] = $CashesUser->ctrlLimit($this->user, $results['OrganizationsCash']['cashLimit'], $results['OrganizationsCash']['limitCashAfter'], $cashesUser, $user_cash, $user_tot_importo_acquistato, $debug);
				
			 } // loop User
		 }
		
		if($debug) debug($results);		
			
		App::import('Model', 'Organization');
		$Organization = new Organization;
			
        $this->set('results', $results);
		$this->set('cashLimits', $cashLimits);	
		
		$this->set('isCassiere', $this->isCassiereGeneric());        
	}
}