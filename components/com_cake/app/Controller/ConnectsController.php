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

   /*
    * da joomla25 a cakephp
    *
    * da cakephp a joomla25 
    * chiamando /api/connect?u={salt}&format=notmpl .htaccess
    * Rests::connect()
    */
   public function admin_index() {
   		
   		if(!isset($this->user->id) || empty($this->user->id))
   			return false;

		$user_id = $this->user->id;
		$user_organization_id =  $this->_getOrganizationById($this->user->id);
		$organization_id = $this->user->organization['Organization']['id']; // gas scelto o gas dello user
		
		$user = ['user_id' => $user_id, 'user_organization_id' => $user_organization_id, 'organization_id' => $organization_id];
		// debug($user);
		$user = serialize($user);
		
		$user_salt = $this->CryptDecrypt->encrypt($user);
		// debug($user_salt);
		
		// $user = $this->CryptDecrypt->decrypt($user_salt);
		// debug($user);
	   	
	   	/*
	   	 * land page, controller / action
	   	 */
	   	$c_to = $this->request->pass['c_to'];
	   	if(empty($c_to))
		   	$c_to = Configure::read('Neo.portalgas.controller'); // 'admin/cashes'
	   	$a_to = $this->request->pass['a_to'];
	   	if(empty($a_to))
			$a_to = Configure::read('Neo.portalgas.action');     // 'supplierOrganizationFilter'; 

		/*
		 * parametri aggiuntivi
		 */
		$q = '';
		unset($this->request->pass['c_to']);
		unset($this->request->pass['a_to']);
		if(!empty($this->request->pass)) {
			foreach ($this->request->pass as $key => $value) {
				$q = $key.'='.$value;
			}
		}

		// https://neo.portalgas.it/api/token/login?u=
		$url = Configure::read('Neo.portalgas.url').Configure::read('Neo.portalgas.pagelogin').'?u='.$user_salt.'&c_to='.$c_to.'&a_to='.$a_to;

		if(!empty($q))
			$url .= '&'.$q;

	 	// debug($url); 	
		// $this->redirect($url);
				
		header("Location: $url");
		exit;
	}

   /*
    * $this->user ha organization_id ma e' gestito a frontend
    * $this->user->organization['Organization'] e' l'organizzazione corrente
    */
   private function _getOrganizationById($user_id) {
		
		$organization_id = 0;

        App::import('Model', 'User');
        $User = new User;

		$options = [];
		$options['conditions'] = ['User.id' => $user_id];
		$options['fields'] = ['User.organization_id'];
		$options['recursive'] = -1;
		$usersResults = $User->find('first', $options);
		// debug($options);
		// debug($usersResults);
		if(!empty($usersResults))
			$organization_id = $usersResults['User']['organization_id'];

		return $organization_id;
   }	
}