<?php
App::uses('AppController', 'Controller');

class OrganizationsCashsController extends AppController {
	
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
	}

	public function admin_index() {

		$debug = false;

		App::import('Model', 'CashesUser');
		$CashesUser = new CashesUser;
				
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
					
					$data = [];
					
					$options = [];
					$options['conditions'] = ['CashesUser.organization_id' => $this->user->organization['Organization']['id'],
											'CashesUser.user_id' => $user_id];
					$options['recursive'] = -1;
					$cashesUserResults = $CashesUser->find('first', $options);
					if(!empty($cashesUserResults))
						$data['CashesUser']['id'] = $cashesUserResults['CashesUser']['id'];
						
					$data['CashesUser']['organization_id'] = $this->user->organization['Organization']['id'];
					$data['CashesUser']['user_id'] = $user_id;
					$data['CashesUser']['limit_type'] = $limit_type;
					$data['CashesUser']['limit_after'] = $this->request->data['OrganizationsCash']['limit_after'][$user_id];
					
					self::d("OrganizationsCashsController", $debug);
					self::d($data, $debug);
					
					$CashesUser->create();
					if ($CashesUser->save($data)) {
						$esito = true;
					}
					else {
						$esito = false;
					}
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
	
	public function admin_ctrl() {

		$debug = false;

		App::import('Model', 'CashesUser');
		$CashesUser = new CashesUser;
		
		App::import('Model', 'Cash');
		$Cash = new Cash;
				
		$options = [];
		$options['conditions'] = ['OrganizationsCash.id' => $this->user->organization['Organization']['id']];
		$options['recursive'] = 1;
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
						 
						 /*
						  * totale cassa per l'utente
						  */
						 $user_cash = $Cash->get_totale_cash_to_user($this->user, $user['id']);
		
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
					 } 
				 } // loop CashsUser
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
		
		self::d($results, $debug);		
			
		App::import('Model', 'Organization');
		$Organization = new Organization;
			
        $this->set('results', $results);
		$this->set('cashLimits', $cashLimits);	
		
		$this->set('isCassiere', $this->isCassiereGeneric());        
	}
}