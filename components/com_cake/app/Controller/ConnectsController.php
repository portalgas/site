<?php
App::uses('AppController', 'Controller');

class ConnectsController extends AppController {
   
    public $components = ['CryptDecrypt'];
		
    public function beforeFilter() {
   		parent::beforeFilter();

		if(empty($this->user) || empty($this->user->id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
    }

    public function index() {
    	$scope = 'FE';
   		$this->_index($scope);
   	}

    public function admin_index() {
    	$scope = 'BO';
   		$this->_index($scope);
   	}

   /*
    * da OLD => NEO
    *	(NEO => OLD /api/connect => Rests::connect())
    *
    * da neo.joomla25Salts::index()
    *	creo u (user_salt) e passo scope (FE / BO) c_to (controller destinazione) / a_to (action destinazione)
	* richiamo https://www.portalgas.it/api/connect?u={salt}=&c_to=Pages&a_to=home
	* rimappa in Rests::connect()
	*	unserialize(user_salt), crea Session e redirect pg destinazione
    *
    * localhost nginx non gestisce .htaccess  
	* 	non passa da api/connect ma direttamente Rests::connect
    */
   public function _index($scope = 'FE') {
   		
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
		   	$c_to = Configure::read('Neo.portalgas.controller'); 
	   	$a_to = $this->request->pass['a_to'];
	   	if(empty($a_to))
			$a_to = Configure::read('Neo.portalgas.action'); 

		/*
		 * parametri aggiuntivi
		 */
		$q = '';
		$pass = $this->request->pass;
		unset($pass['c_to']);
		unset($pass['a_to']);
		if(!empty($pass)) {
			// debug($pass);
			foreach ($pass as $key => $value) {
				$q .= $key.'='.$value.'&';
			}
			$q = substr($q, 0, strlen($q)-1);
		}

		// https://neo.portalgas.it/api/joomla25Salt/login
		$url = Configure::read('Neo.portalgas.url').Configure::read('Neo.portalgas.pagelogin').'?u='.$user_salt.'&scope='.$scope.'&c_to='.$c_to.'&a_to='.$a_to;

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