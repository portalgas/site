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
	   	
	   	/*
	   	 * land page, controller / action
	   	 */
	   	$c_to = Configure::read('Neo.portalgas.controller'); // 'admin/cashes'
		$a_to = Configure::read('Neo.portalgas.action');     // 'supplierOrganizationFilter'; 

		// http://neo.portalgas.it/api/token/login?u=
		$url = Configure::read('Neo.portalgas.url').Configure::read('Neo.portalgas.pagelogin').'?u='.$user_salt.'&c_to='.$c_to.'&a_to='.$a_to;
		// debug($url);
	   	
		// $this->redirect($url);
				
		header("Location: $url");
		exit;
	}
}