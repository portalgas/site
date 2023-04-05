<?php
App::uses('AppController', 'Controller');

class CashsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();

        /*
         * il ReferentCassiere non puo' accedere
         */
        if (!$this->isCassiere()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
    }

    public function admin_index() {

        App::import('Model', 'User');

        $conditions = ['Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
                // 'User.block' => 0  lo metto nel model se no non mi prende quelli con user_id = 0
        ];

        $SqlLimit = 500;
        $this->Cash->recursive = 1;
        $this->paginate = ['maxLimit' => $SqlLimit, 'limit' => $SqlLimit, 'conditions' => $conditions, 'order' => Configure::read('orderUser')];
        $results = $this->paginate('Cash');
        foreach ($results as $numResult => $result) {
   
            /*
             * user disabilitati
             */
            if(!isset($result['User']['id']) && !empty($result['Cash']['user_id'])) {
                $User = new User;   

                $options = [];
                $options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
                                          'User.id' => $result['Cash']['user_id'],
                                          'User.username NOT LIKE' => '%portalgas.it'];

                $options['recursive'] = -1;
                $userResults = $User->find('first', $options);  
                if(!empty($userResults)) {
                    $results[$numResult]['User']['name'] = $userResults['User']['name'];
                    $results[$numResult]['User']['email'] = $userResults['User']['email'];
                    $results[$numResult]['User']['block'] = $userResults['User']['block'];    
                }
                else 
                    unset($results[$numResult]);

            }
        }
        $this->set(compact('results'));

       self::d($results, false);
    }

    public function admin_index_quick() {

        App::import('Model', 'User');
        $User = new User;

        $options = [];
        $options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
								  'User.block' => 0];

        $options['recursive'] = -1;
        $options['order'] = Configure::read('orderUser');
        $results = $User->find('all', $options);

        foreach ($results as $numResult => $result) {

            $options = [];
            $options['conditions'] = ['Cash.organization_id' => $this->user->organization['Organization']['id'],
									  'Cash.user_id' => $result['User']['id']];
            $userResults = $this->Cash->find('first', $options);
            if (!empty($userResults))
                $results[$numResult]['Cash'] = $userResults['Cash'];
            else {
                $results[$numResult]['Cash']['importo'] = '0.00';
                $results[$numResult]['Cash']['importo_'] = '0,00';
                $results[$numResult]['Cash']['importo_e'] = '0,00 &euro;';
                $results[$numResult]['Cash']['nota'] = '';
            }
        }

        $this->set(compact('results'));
    }

    public function admin_index_quick_update() {

		$debug = false;
		
        $user_id = $this->request->data['user_id'];
        $value = $this->request->data['value'];

		if($debug) debug($this->request->data);
		
        /*
         *   ctrl se insert / update 
         */
        $options = [];
        $options['conditions'] = ['Cash.organization_id' => $this->user->organization['Organization']['id'],
            					  'Cash.user_id' => $user_id];
        $options['fields'] = ['Cash.id'];
        $options['recursive'] = -1;
        $results = $this->Cash->find('first', $options);
		if($debug) debug($options);
		if($debug) debug($results); 
        if (!empty($results)) {
            /*
             * UPDATE
             */
            $data['Cash']['id'] = $results['Cash']['id'];
            

			/*
			 * dati Cash precedenti in CashesHistory
			 */
			App::import('Model', 'CashesHistory');
	        $CashesHistory = new CashesHistory;
			
			$CashesHistory->previousCashSave($this->user, $results['Cash']['id']);
	        	            
		}
		
        $data['Cash']['organization_id'] = $this->user->organization['Organization']['id'];
        $data['Cash']['user_id'] = $user_id;
        $data['Cash']['importo'] = $this->importoToDatabase($value);
        //debug($data);exit;
		self::d($data, $debug);
		
		$msg_errors = $this->Cash->getMessageErrorsToValidate($this->Cash, $data);
		if(!empty($msg_errors)) {
			if($debug) debug($msg_errors);
		}
		else {			
			$this->Cash->create();
			$this->Cash->save($data);

			$this->layout = 'ajax';
			$this->render('/Layouts/ajax');
		}
    }

	/*
	 * con la nota non inserisco in $CashesHistory->previousCashSave($this->user, $results['Cash']['id']);
	 */ 
    public function admin_index_quick_update_nota() {

		$debug = false;
		
        $user_id = $this->request->data['user_id'];
        $value = $this->request->data['value'];
	
		if($debug) debug('admin_index_quick_update_nota');
		if($debug) debug($this->request->data);

        /*
         *   ctrl se insert / update 
         */
        $options = [];
        $options['conditions'] = ['Cash.organization_id' => $this->user->organization['Organization']['id'],
            					  'Cash.user_id' => $user_id];

        $options['recursive'] = -1;
        $results = $this->Cash->find('first', $options);
        if (!empty($results)) {
            /*
             * UPDATE
             */
            $data['Cash'] = $results['Cash'];	            
		}
		
        $data['Cash']['organization_id'] = $this->user->organization['Organization']['id'];
        $data['Cash']['user_id'] = $user_id;
        $data['Cash']['nota'] = $value; // '".addslashes($nota)."' 
        
		if($debug) debug($data);
		
        $this->Cash->create();
        $this->Cash->save($data);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }
	
	/*
	 * se if(!empty($user_id)) ha gia' lo user settato 
	 */
    public function admin_add($user_id=0) {

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->request->data['Cash']['organization_id'] = $this->user->organization['Organization']['id'];
            /*
             * voce di spesa generica
             */
            if (empty($this->request->data['Cash']['user_id']))
                $this->request->data['Cash']['user_id'] = 0;

            $this->Cash->create();
            if ($this->Cash->save($this->request->data)) {
                $this->Session->setFlash(__('The cash has been saved'));
                $this->myRedirect(['action' => 'index']);
            } else {
                $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
            }
        } // if ($this->request->is('post') || $this->request->is('put'))

        /*
         * estraggo tutti gli utenti senza associazione ad una voce di spesa 
         * != 0 per escludere la voce di spesa generica
         */
        $options = [];
        $options['conditions'] = ['Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
                                  'Cash.user_id != ' => 0];
        $options['fields'] = ['user_id'];
        $options['recursive'] = 1;
        $options['order'] = Configure::read('orderUser');
        $results = $this->Cash->find('all', $options);

        self::d([$options, $results], false);
		
        $user_ids = '';
        foreach ($results as $result) {
            $user_ids .= $result['Cash']['user_id'] . ',';
        }

        $options = [];
        $options['conditions'] = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
                                  'User.block' => 0,
                                  'User.username NOT LIKE' => '%portalgas.it'];

        if (!empty($user_ids)) {
            $user_ids = substr($user_ids, 0, (strlen($user_ids) - 1));
            $options['conditions'] += ['User.id NOT IN (' . $user_ids . ')'];
        }

        $options['fields'] = ['id', 'name'];
        $options['recursive'] = -1;
        $options['order'] = Configure::read('orderUser');
        $users = $this->Cash->User->find('list', $options);

        $this->set('users', $users);
        $this->set('user_id', $user_id);

        $results = $this->Cash->getTotaleCash($this->user);
        $totale_importo = $results['totale_importo'];
        $this->set('totale_importo', $totale_importo);
    }

    /*
     * modifica voce di cassa dell'utente 
     */
    public function admin_edit($id=0) {

        $debug = false;

        $options = [];
        $options['conditions'] = ['Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
                                  'Cash.id' => $id];
        $options['recursive'] = 1;
        $results = $this->Cash->find('first', $options);

        if($debug) debug($results);
        $this->set(compact('results'));

        $results = $this->Cash->getTotaleCash($this->user);
        $totale_importo = $results['totale_importo'];
        $this->set('totale_importo', $totale_importo);

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->request->data['Cash']['organization_id'] = $this->user->organization['Organization']['id'];
            /*
             * voce di spesa generica
             */
            if (empty($this->request->data['Cash']['user_id']))
                $this->request->data['Cash']['user_id'] = 0;
            // debug($this->request->data);
            $this->Cash->create();
            if ($this->Cash->save($this->request->data)) {
                $this->Session->setFlash(__('The cash has been saved'));
                $this->myRedirect(['action' => 'index']);
            } else {
                $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
            }
        } // if ($this->request->is('post') || $this->request->is('put'))
    }

    
    /*
     * modifica voce di cassa storica dell'utente , solo campo NOTE
     */
    public function admin_history_edit($id=0) {

        $debug = false;

        App::import('Model', 'CashesHistory');
    	$CashesHistory = new CashesHistory;

        $options = [];
        $options['conditions'] = ['CashesHistory.organization_id' => (int) $this->user->organization['Organization']['id'],
                                  'CashesHistory.id' => $id];
        $options['recursive'] = 2;
        $results = $CashesHistory->find('first', $options);
        /*debug($results);
        debug($options);exit;
        */
        if(empty($results)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if($debug) debug($results);
        $this->set(compact('results'));

        $results = $this->Cash->getTotaleCash($this->user);
        $totale_importo = $results['totale_importo'];
        $this->set('totale_importo', $totale_importo);

        if ($this->request->is('post') || $this->request->is('put')) {

            $options = [];
            $options['conditions'] = ['CashesHistory.organization_id' => (int) $this->user->organization['Organization']['id'],
                                      'CashesHistory.id' => $id];
            $options['recursive'] = -1;
            $results = $CashesHistory->find('first', $options);
            $results['CashesHistory']['nota'] = $this->request->data['CashesHistory']['nota'];
            if ($CashesHistory->save($results)) {
                $this->Session->setFlash(__('The cash has been saved'));
                $this->myRedirect(['action' => 'index']);
            } else {
                $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
            }
        } // if ($this->request->is('post') || $this->request->is('put'))
    }
    
    public function admin_edit_by_user_id($user_id=0) {

       if (empty($user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $options = [];
        $options['conditions'] = ['Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
            						   'Cash.user_id' => $user_id];
        $options['fields'] = ['id'];
        $options['recursive'] = -1;
        $cashResults = $this->Cash->find('first', $options);
        
		self::d([$options, $cashResults], false);
	
        if(empty($cashResults)) {
        	/*
        	 * non ho ancora creato un occorrenza 
        	 */ 
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Cashs&action=add&user_id='.$user_id;        	 
        }
        else {
			$cash_id = $cashResults['Cash']['id'];
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Cashs&action=add_user&id='.$cash_id;		
		}					
		$this->myRedirect($url);
	}
 
	/*
	 * Cash precedente lo salvo in CashesHistories
	 */
    public function admin_add_user($id = null) {

        $this->Cash->id = $id;
        if (!$this->Cash->exists($this->Cash->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

		 
        if ($this->request->is('post') || $this->request->is('put')) {

            $this->request->data['Cash']['organization_id'] = $this->user->organization['Organization']['id'];

			/*
			 * dati Cash precedenti in CashesHistory
			 */
			App::import('Model', 'CashesHistory');
	        $CashesHistory = new CashesHistory;
			
			$CashesHistory->previousCashSave($this->user, $this->request->data['Cash']['id']);
	        			 
	        			             
            $this->Cash->create();
            if ($this->Cash->save($this->request->data)) {
                $this->Session->setFlash(__('The cash has been saved'));
                $this->myRedirect(['action' => 'index']);
            } else {
                $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
            }
        } // edn if ($this->request->is('post') || $this->request->is('put'))

        $options = [];
        $options['conditions'] = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
                                    'User.block' => 0,
                                    'User.username NOT LIKE' => '%portalgas.it'];
        $options['fields'] = ['id', 'name'];
        $options['recursive'] = -1;
        $options['order'] = Configure::read('orderUser');
        $users = $this->Cash->User->find('list', $options);

        $this->set('users', $users);

		/*
		 * dati Cash
		 */
        $options = [];
        $options['conditions'] = ['Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
							            'Cash.id' => $id];
        $this->request->data = $this->Cash->find('first', $options);

        $results = $this->Cash->getTotaleCash($this->user);
        $totale_importo = $results['totale_importo'];
        $this->set('totale_importo', $totale_importo);

        /*
         * anagrafica dello user
         */
        App::import('Model', 'User');
        $User = new User;

        $options = [];
        $options['conditions'] = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
                                  'User.id' => $this->request->data['Cash']['user_id']];
        $options['recursive'] = -1;
        $utente = $User->find('first', $options);

        /*
         * userprofile
         */
        $utente_profile = JUserHelper::getProfile($this->request->data['Cash']['user_id']);
        $utente['Profile'] = $utente_profile->profile;
        $this->set(compact('utente'));
    }

    public function admin_delete($id = null) {

        $this->Cash->id = $id;
        if (!$this->Cash->exists($this->Cash->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }


        if ($this->Cash->delete())
            $this->Session->setFlash(__('Delete Cash'));
        else
            $this->Session->setFlash(__('Cash was not deleted'));
        $this->myRedirect(['action' => 'index']);
    }

}
