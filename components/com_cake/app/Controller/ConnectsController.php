<?php
App::uses('AppController', 'Controller');

class ConnectsController extends AppController {
   
    public $components = ['CryptDecrypt'];
		
   public function beforeFilter() {
   		parent::beforeFilter();
		
		if(empty($this->user)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
   }

   public function admin_index() {
   		
		$username = $this->user->username;
		$organization_id = $this->user->organization['Organization']['id'];
		
		$user = ['username' => $username, 'organization_id' => $organization_id];
		// debug($user);
		$user = serialize($user);
		
		$user_salt = $this->CryptDecrypt->encrypt($user);
		// debug($user_salt);
		
		// $user = $this->CryptDecrypt->decrypt($user_salt);
		// debug($user);
	   	
		$url = 'http://neo.portalgas.it/admin/api/token/login?u='.$user_salt;
		debug($url);
	   	
		// $this->redirect($url);
				
		// header("Location: $url");
					
		exit;
	}
}