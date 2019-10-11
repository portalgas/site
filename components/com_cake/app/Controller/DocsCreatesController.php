<?php
App::uses('AppController', 'Controller');

class DocsCreatesController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();

		/* ctrl ACL */
		if(!$this->isManager() && !$this->isTesoriere() && !$this->isCassiere()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		/* ctrl ACL */
	}

    public function admin_index() {
	    $this->paginate = ['conditions' => ['DocsCreate.organization_id' => $this->user->organization['Organization']['id']],
							'recursive' => 1,
							'limit' => 100,
							'order' => ['DocsCreate.created' => 'desc']];
	    $results = $this->paginate('DocsCreate');
		$this->set('results', $results);
		
		App::import('Model', 'DocsCreateUser');
		$DocsCreateUser = new DocsCreateUser;		
		$num_last = $DocsCreateUser->getLastNum($this->user, date('Y'));
		$this->set('num_last', $num_last);
	}
	
	public function admin_add() {
	
		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['DocsCreate']['organization_id'] = $this->user->organization['Organization']['id'];
			$this->request->data['DocsCreate']['txt_data'] = $this->request->data['DocsCreate']['txt_data_db'];
			
			$this->DocsCreate->create();
			if ($this->DocsCreate->save($this->request->data)) {
				
				$doc_id = $this->DocsCreate->getLastInsertId();
				
				/*
				 * inserisco gli Users associati al documento
				 */
				if(!empty($this->request->data['DocsCreate']['user_ids'])) {
					
					App::import('Model', 'DocsCreateUser');
					$DocsCreateUser  = new DocsCreateUser ;
					
					$DocsCreateUser ->insert($this->user, $doc_id, $this->request->data['DocsCreate']['user_ids'], $debug);
				}  
		
				$this->Session->setFlash(__('The docsCreates has been saved', true));
				if(!$debug) $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The docsCreates could not be saved. Please, try again.', true));
			}
		} // end if ($this->request->is('post') || $this->request->is('put')) 
			
		
		App::import('Model', 'User');
		$User = new User;

		$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user')];
		$users = $User->getUsersList($this->user, $conditions, Configure::read('orderUser'), ['name', 'email']);
		$this->set('users',$users);
		
		$stato = ClassRegistry::init('DocsCreate')->enumOptions('stato');
		$this->set(compact('stato'));		
	}
	
	public function admin_edit($doc_id=0) {
	
		$debug = false;
		
		if (empty($doc_id)) 
			$doc_id = $this->request->data['DocsCreate']['doc_id'];
		
		if (empty($doc_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$options = [];
		$options['conditions'] = ['DocsCreate.organization_id' => $this->user->organization['Organization']['id'],
								  'DocsCreate.id' => $doc_id];
		$options['recursive'] = 1;
		$results = $this->DocsCreate->find('first', $options);
		
		self::d($results, $debug);
		
		if(empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if($results['DocsCreate']['mail_send_data']!=Configure::read('DB.field.datetime.empty')) {
			$this->Session->setFlash("Non si può modificare il documento perchè è stata già inviata la mail agli utenti"); // ho creato per ogni utente il num progressivo
			$this->myRedirect(Configure::read('routes_msg_exclamation'));			
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['DocsCreate']['organization_id'] = $this->user->organization['Organization']['id'];
			$this->request->data['DocsCreate']['id'] = $this->request->data['DocsCreate']['doc_id'];
			$this->request->data['DocsCreate']['txt_data'] = $this->request->data['DocsCreate']['txt_data_db'];
	
			$this->DocsCreate->create();
			if ($this->DocsCreate->save($this->request->data)) {
				
				/*
				 * inserisco gli Users associati al documento
				 */
				if(!empty($this->request->data['DocsCreate']['user_ids'])) {
					
					App::import('Model', 'DocsCreateUser');
					$DocsCreateUser  = new DocsCreateUser ;
					
					$DocsCreateUser ->insert($this->user, $doc_id, $this->request->data['DocsCreate']['user_ids'], $debug);
				}  
						
				$this->Session->setFlash(__('The docsCreates has been saved', true));
				if(!$debug) $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The docsCreates could not be saved. Please, try again.', true));
			}
		} // end if ($this->request->is('post') || $this->request->is('put')) 
			
		
		App::import('Model', 'User');
		$User = new User;
		
		/*
		 * users gia' associati
		 */
		$usersResults = [];
		$users_ids = '';
		if(!empty($results['DocsCreateUser']))
		foreach($results['DocsCreateUser'] as $result){
			$users_ids .= $result['user_id'].',';
			
			$options = [];
			$options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
									  'User.id' => $result['user_id']];
			$options['recursive'] = -1;
			$tmpResults = $User->find('first', $options);
		
			$usersResults[$result['user_id']] = $tmpResults['User']['name'].' '.$tmpResults['User']['email']; 
		}
		$this->set('usersResults',$usersResults);
		
		/*
		 * user da associare
		 */		
		$conditions = [];
		if(!empty($users_ids)) {
			$users_ids = substr($users_ids, 0, (strlen($users_ids)-1));
			$conditions += ["User.id NOT IN" => "('$users_ids')"];
		}
		
		$conditions += ['UserGroupMap.group_id' => Configure::read('group_id_user')];
		$users = $User->getUsersList($this->user, $conditions, Configure::read('orderUser'), ['name', 'email']);
		$this->set(compact('users'));
		
		$stato = ClassRegistry::init('DocsCreate')->enumOptions('stato');
		$this->set(compact('stato'));
		$this->set(compact('doc_id'));
		
		$this->request->data = $results;		
	}
	
	/*
	 * stampa tutti i doc degli users
	 */
	public function admin_pdf_print_all($doc_id=0) {
	
		$debug = false;
		
		if (empty($doc_id)) 
			$doc_id = $this->request->data['DocsCreate']['doc_id'];
		
		if (empty($doc_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$options = [];
		$options['conditions'] = ['DocsCreate.organization_id' => $this->user->organization['Organization']['id'],
								  'DocsCreate.id' => $doc_id];
		$options['recursive'] = 1;
		$results = $this->DocsCreate->find('first', $options);
		
		self::d($results, $debug);
		
		if(empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		App::import('Model', 'User');
		$User = new User;
		
		/*
		 * users gia' associati
		 */
		$usersResults = [];
		$users_ids = '';
		if(!empty($results['DocsCreateUser']))
		foreach($results['DocsCreateUser'] as $result){
			$users_ids .= $result['user_id'].',';
			
			$options = [];
			$options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
									  'User.id' => $result['user_id']];
			$options['recursive'] = -1;
			$tmpResults = $User->find('first', $options);
		
			$usersResults[$result['user_id']] = $tmpResults['User']['name'].' '.$tmpResults['User']['email']; 
		}
		$this->set('usersResults',$usersResults);
		
		$this->set(compact('doc_id'));
		
		$this->request->data = $results;		
	}
							
	public function admin_mail($doc_id=0) {
	
		$debug = false;
		
		if (empty($doc_id)) 
			$doc_id = $this->request->data['DocsCreate']['doc_id'];
		
		if (empty($doc_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$options = [];
		$options['conditions'] = ['DocsCreate.organization_id' => $this->user->organization['Organization']['id'],
								  'DocsCreate.id' => $doc_id];
		$options['recursive'] = 1;
		$results = $this->DocsCreate->find('first', $options);
		
		self::d($results, $debug);
		
		if(empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if(empty($results['DocsCreateUser'])) {		
			$this->Session->setFlash("Per il documento che desideri inviare non sono stati scelti destinatari!");			
			$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DocsCreates&action=index');			
		}
			
		if($results['DocsCreate']['mail_send_data']!=Configure::read('DB.field.datetime.empty')) {
			$this->Session->setFlash("La mail agli utenti è già stata inviata"); // ho creato per ogni utente il num progressivo
			$this->myRedirect(Configure::read('routes_msg_exclamation'));			
		}
		
		App::import('Model', 'User');
		$User = new User;
				
		App::import('Model', 'DocsCreateUser');
		$DocsCreateUser = new DocsCreateUser;

		App::import('Model', 'Mail');
		$Mail = new Mail;
		
		$Email = $Mail->getMailSystem($this->user);

		$users = [];
		$user_ids = '';
		foreach($results['DocsCreateUser'] as $result)
			$user_ids .= $result['user_id'].',';
		if(!empty($user_ids)) 
			$user_ids = substr($user_ids, 0, strlen($user_ids)-1);
			
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$body_mail = $this->request->data['DocsCreate']['body_mail'];
			
			self::d($body_mail,$debug);
			
			$Email->subject($this->request->data['DocsCreate']['subject']);						
			$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);

			$tot_ok=0;
			$tot_ko=0;
			$msg_ok='';
			$msg_no='';
			foreach($results['DocsCreateUser'] as $result) {
						
				$options = [];
				$options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
										  'User.id' => $result['user_id']];
				$options['recursive'] = -1;
				$usersResults = $User->find('first', $options);				
					
				$mail = $usersResults['User']['email'];
				$name = $usersResults['User']['name'];						
				
				$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
				$Mail->send($Email, $mail, $body_mail, $debug);

				/*
				 * aggiorno il numero di documento 
				 */
				$options = [];
				$options['conditions'] = ['DocsCreateUser.organization_id' => $this->user->organization['Organization']['id'],
										  'DocsCreateUser.id' => $result['id']];
				$options['recursive'] = -1;
				$docsCreateUserResults = $DocsCreateUser->find('first', $options);
		
				$docsCreateUserResults['DocsCreateUser']['year'] = date('Y');
				$docsCreateUserResults['DocsCreateUser']['num'] = $DocsCreateUser->getLastNum($this->user, date('Y'));
				$DocsCreateUser->create();
				$DocsCreateUser->save($docsCreateUserResults);				 
			}

			/*
			 * aggiorno la data di invio mail, cosi' blocco il doc perche' ho settato i numeri progressivi in DocsCreateUser
			 */
			$results['DocsCreate']['mail_send_data'] = date('Y-m-d H:i:s');
			$this->DocsCreate->create();
			$this->DocsCreate->save($results);

			
			/*
			 * messaggio
			 */
			if(!empty($msg_ok)) $msg_ok = 'La mail è stata inviata a<br />'.$msg_ok.'<br/>Totale: '.$tot_ok; 
			if(!empty($msg_no)) $msg_no = '<hr />La mail NON è stata inviata a<br />'.$msg_no.'<br/>Totale: '.$tot_no; 
			$msg = $msg_ok.$msg_no;	
			$this->Session->setFlash($msg);
	
			if(!$debug) $this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DocsCreates&action=index');
		}
						
		$this->set('body_header', 'Salve Mario Rossi,');
			
		$this->set('body_footer', sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www'])));
		
		$body_mail = '';	
		$body_mail .= 'Da oggi potrai trovare un documento denominato <b>'.$results['DocsCreate']['name'].'</b><br /><br />';
		$body_mail .= 'Dopo esseri <b>autenticato</b> su <a target="_blank" href="http://www.portalgas.it">portalgas.it</a><br />';

		$j_seo = $this->user->organization['Organization']['j_seo'];
		$url = 'http://www.portalgas.it/home-' . $j_seo . '/stampe-' . $j_seo . '#user-docs';
		$body_mail .= ' clicca su <a target="_blank" href="' . $url . '">'.$url.'</a> per scaricare il documento';
							
		$this->set('body_mail',$body_mail);			
		
		
		$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'),
							'UserGroupMap.user_id IN' => '('.$user_ids.')');

		$users = $User->getUsersList($this->user, $conditions, Configure::read('orderUser'), array('name', 'email'));
		$this->set('users',$users);
		
		$stato = ClassRegistry::init('DocsCreate')->enumOptions('stato');
		$this->set(compact('results', 'doc_id'));		
	}
	
	public function admin_pdf_preview($format='notmpl') {
			
		if ($this->request->is('post') || $this->request->is('put')) {			
			$data = [];
			$data['num'] = 'xxx';
			$data['year'] = date('Y');				
			$data['name'] = $this->request->data['DocsCreate']['name'];
			$data['txt_testo'] = $this->request->data['DocsCreate']['txt_testo'];
			if(!empty($results['DocsCreate']['txt_data']) && $results['DocsCreate']['txt_data']!=Configure::read('DB.field.date.empty'))
				$data['txt_data'] = $this->request->data['DocsCreate']['txt_data']['day'].'/'.$this->request->data['DocsCreate']['txt_data']['month'].'/'.$this->request->data['DocsCreate']['txt_data']['year'];
			else
				$data['txt_data'] = date('d/m/Y');
			
			$this->_pdf($this->user, $data, $this->user->id);
		}
	
		$this->layout = 'pdf';			
		$this->render('/DocsCreates/admin_pdf');
	}	

	public function pdf_create($doc_id, $format='notmpl') {

		if (empty($doc_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$user_id = $this->user->id;
	
		if (empty($user_id)) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
		
		$this->_pdf_create($doc_id, $user_id);
			
		$this->layout = 'pdf';			
		$this->render('/DocsCreates/admin_pdf');		
	}
	
	public function admin_pdf_create($doc_id, $user_id=0, $format='notmpl') {

		if (empty($doc_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		if (empty($user_id)) 
			$user_id = $this->user->id;
		
		$this->_pdf_create($doc_id, $user_id);
		
		$this->layout = 'pdf';			
		$this->render('/DocsCreates/admin_pdf');		
	}
	
	private function _pdf_create($doc_id, $user_id, $format='notmpl') {
		
		App::import('Model', 'DocsCreateUser');
		$DocsCreateUser = new DocsCreateUser;
		
		$options = [];
		$options['conditions'] = ['DocsCreate.organization_id' => $this->user->organization['Organization']['id'],
								  'DocsCreate.id' => $doc_id,
								  'DocsCreateUser.doc_id' => $doc_id,
								  'DocsCreateUser.user_id' => $user_id];
		$options['recursive'] = 1;
		$results = $DocsCreateUser->find('first', $options);

		if(!empty($results)) {	
			$data = [];
			$data['num'] = $results['DocsCreateUser']['num'];
			$data['year'] = $results['DocsCreateUser']['year'];
			$data['name'] = $results['DocsCreate']['name'];
			$data['txt_testo'] = $results['DocsCreate']['txt_testo'];
			if(!empty($results['DocsCreate']['txt_data']) && $results['DocsCreate']['txt_data']!=Configure::read('DB.field.date.empty')) {
				list($aaaa, $mm, $gg) = explode("-", $results['DocsCreate']['txt_data']);
				$data['txt_data'] = $gg . '-' . $mm . '-' . $aaaa;
			}
			else
				$data['txt_data'] = date('d/m/Y');
			
			$this->_pdf($this->user, $data, $user_id);
		}		
	}	
	
	private function _pdf($user, $data, $user_id) {
		
		Configure::write('debug', 0);

		App::import('Model', 'Organization');
		$Organization = new Organization;
		
		App::import('Model', 'User');
		$User = new User;

		$options = [];
		$options['conditions'] = array('Organization.id' => $user->organization['Organization']['id']);
		$options['recursive'] = -1;
	
		$organizationResults = $Organization->find('first', $options);
		$this->set('organizationResults', $organizationResults);
	
		$conditions = [];
		$conditions['User.id'] = 'User.id = '.$user_id;
		$userResults = $User->getUsersComplete($this->user, $conditions);
		$this->set('userResults', $userResults[0]);
		
		$this->set('num', $data['num']);
		$this->set('year', $data['year']);
		$this->set('name', $data['name']);
		$this->set('txt_testo', $data['txt_testo']);
		$this->set('txt_data', $data['txt_data']);
		
		if(empty($data['name']))
			$data['name'] = 'documento';

		$fileData['fileTitle'] = $data['name'];
		$fileData['fileName'] = strtolower(str_replace(" ","_", $data['num'].'/'.$data['year'].' '.$data['name']));
		$this->set('fileData', $fileData);
		
	}

	public function admin_delete($doc_id) {

		$this->DocsCreate->id = $doc_id;
		if ($this->DocsCreate->delete())
			$this->Session->setFlash(__('Delete DocsCreate'));
		else
			$this->Session->setFlash(__('DocsCreate was not deleted'));

		$this->myRedirect(['action' => 'index']);
    }
}