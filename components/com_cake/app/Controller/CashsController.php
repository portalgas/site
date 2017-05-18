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

        $conditions = array('Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
                // 'User.block' => 0  lo metto nel model se no non mi prende quelli con user_id = 0
        );

        $this->Cash->recursive = 1;
        $this->paginate = array('limit' => 250, 'conditions' => $conditions, 'order' => Configure::read('orderUser'));
        $results = $this->paginate('Cash');
        $this->set(compact('results'));

       /*  
       echo "<pre>";
   	   print_r($results);
       echo "</pre>";
       */        
    }

    public function admin_index_quick() {

        App::import('Model', 'User');
        $User = new User;

        $options = array();
        $options['conditions'] = array('User.organization_id' => $this->user->organization['Organization']['id'],
            'User.block' => 0);

        $options['recursive'] = -1;
        $options['order'] = Configure::read('orderUser');
        $results = $User->find('all', $options);

        foreach ($results as $numResult => $result) {

            $options = array();
            $options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
                'Cash.user_id' => $result['User']['id']);
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

        $user_id = $this->request->data['user_id'];
        $value = $this->request->data['value'];

        /*
         *   ctrl se insert / update 
         */
        $options = array();
        $options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
            							'Cash.user_id' => $user_id);

        $options['recursive'] = -1;
        $results = $this->Cash->find('first', $options);
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
        /*
          echo "<pre>";
          print_r($data);
          echo "</pre>";
         */
        $this->Cash->create();
        $this->Cash->save($data);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }

	/*
	 * con la nota non inserisco in $CashesHistory->previousCashSave($this->user, $results['Cash']['id']);
	 */ 
    public function admin_index_quick_update_nota() {

        $user_id = $this->request->data['user_id'];
        $value = $this->request->data['value'];

        /*
         *   ctrl se insert / update 
         */
        $options = array();
        $options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
            							'Cash.user_id' => $user_id);

        $options['recursive'] = -1;
        $results = $this->Cash->find('first', $options);
        if (!empty($results)) {
            /*
             * UPDATE
             */
            $data['Cash']['id'] = $results['Cash']['id'];	        	            
		}
		
        $data['Cash']['organization_id'] = $this->user->organization['Organization']['id'];
        $data['Cash']['user_id'] = $user_id;
        $data['Cash']['nota'] = $value; // '".addslashes($nota)."' 
        /*
          echo "<pre>";
          print_r($data);
          echo "</pre>";
         */
        $this->Cash->create();
        $this->Cash->save($data);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }

    public function admin_add() {

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
                $this->myRedirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
            }
        } // if ($this->request->is('post') || $this->request->is('put'))

        /*
         * estraggo tutti gli utenti senza associazione ad una voce di spesa 
         * != 0 per escludere la voce di spesa generica
         */
        $options = array();
        $options['conditions'] = array('Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
                                        'Cash.user_id != ' => 0);
        $options['fields'] = array('user_id');
        $options['recursive'] = 1;
        $options['order'] = Configure::read('orderUser');
        $results = $this->Cash->find('all', $options);

        /*
          echo "<pre>";
          print_r($options);
          print_r($results);
          echo "</pre>";
         */

        $user_ids = '';
        foreach ($results as $result) {
            $user_ids .= $result['Cash']['user_id'] . ',';
        }

        $options = array();
        $options['conditions'] = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
            'User.block' => 0);

        if (!empty($user_ids)) {
            $user_ids = substr($user_ids, 0, (strlen($user_ids) - 1));
            $options['conditions'] += array('User.id NOT IN (' . $user_ids . ')');
        }

        $options['fields'] = array('id', 'name');
        $options['recursive'] = -1;
        $options['order'] = Configure::read('orderUser');
        $users = $this->Cash->User->find('list', $options);

        $this->set('users', $users);

        $results = $this->Cash->get_totale_cash($this->user);
        $totale_importo = $results['totale_importo'];
        $this->set('totale_importo', $totale_importo);
    }

	/*
	 * Cash precedente lo salvo in CashesHistories
	 */
    public function admin_edit($id = null) {

        $this->Cash->id = $id;
        if (!$this->Cash->exists()) {
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
                $this->myRedirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
            }
        } // edn if ($this->request->is('post') || $this->request->is('put'))

        $options = array();
        $options['conditions'] = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
            'User.block' => 0);
        $options['fields'] = array('id', 'name');
        $options['recursive'] = -1;
        $options['order'] = Configure::read('orderUser');
        $users = $this->Cash->User->find('list', $options);

        $this->set('users', $users);

		/*
		 * dati Cash
		 */
        $options = array();
        $options['conditions'] = array('Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
							            'Cash.id' => $id);
        $this->request->data = $this->Cash->find('first', $options);

        $results = $this->Cash->get_totale_cash($this->user);
        $totale_importo = $results['totale_importo'];
        $this->set('totale_importo', $totale_importo);

        /*
         * anagrafica dello user
         */
        App::import('Model', 'User');
        $User = new User;

        $options = array();
        $options['conditions'] = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
            'User.id' => $this->request->data['Cash']['user_id']);
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
        if (!$this->Cash->exists()) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }


        if ($this->Cash->delete())
            $this->Session->setFlash(__('Delete Cash'));
        else
            $this->Session->setFlash(__('Cash was not deleted'));
        $this->myRedirect(array('action' => 'index'));
    }

}