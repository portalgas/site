<?php
App::uses('AppModel', 'Model');
App::uses('CakeEmail', 'Network/Email');
App::uses('CakeTime', 'Utility');

class MyAppModel extends AppModel {
        
	protected function _getMail($template='default') {
		$Email = new CakeEmail(Configure::read('EmailConfig'));
		$Email->helpers(array('Html', 'Text'));
		$Email->template($template);
		$Email->emailFormat('html');
		
		$Email->replyTo(Configure::read('Mail.no_reply_mail'), Configure::read('Mail.no_reply_name'));
		$Email->from(array(Configure::read('SOC.mail') => Configure::read('SOC.name')));
		$Email->sender(Configure::read('SOC.mail'), Configure::read('SOC.name'));
		
		$Email->viewVars(array('content_info' => $this->_getContentInfo()));
		
		return $Email;
	}
	
	protected function _getContentInfo() {
  		App::import('Model', 'Msg');
		$Msg = new Msg;	

		$results = $Msg->getRandomMsg();
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		if(!empty($results)) 
			$content_info = $results['Msg']['testo'];
		else
			$content_info = '';
		
		return $content_info;
	}

	protected function _organizationNameError($organization) {
		if($organization['Organization']['id']==10)
			$organization_name = "Colibrì";
		else
			$organization_name = $organization['Organization']['name'];
		
		return $organization_name;
	}
	
	/*
	 * stesso codice AppController, AppHelper
	*/
	protected function _traslateWww($str) {
			
    	if(strpos($str,'http://')===false && strpos($str,'https://')===false)
    		$str = 'http://'.$str;
			
		return $str;
	}
	
	protected function _getUsers($organization_id) {
            App::import('Model', 'User');
            $User = new User;

            $options = array();
            $options['conditions'] = array('User.organization_id'=>(int)$organization_id,
                                            'User.block'=> 0);
            $options['fields'] = array('User.id','User.name','User.email','User.username');
            $options['order'] = Configure::read('orderUser');
            $options['recursive'] = 0;

            $users = $User->find('all', $options);

            /*
            echo "<pre>";
            print_r($users;
            echo "</pre>";
            */

            echo "getUsers(): trovati ".count($users)." utenti\n";

            return $users;
	}
        
        protected function _getObjUserLocal($organization_id, $debug=false) {

			App::import('Model', 'Organization');
            $Organization = new Organization;

            $options = array();
            $options['conditions'] = array('Organization.id' => (int) $organization_id);
            $options['recursive'] = -1;
            $organization = $Organization->find('first', $options);

            $user = new UserLocal();
            $user->organization = $organization;

            $paramsConfig = json_decode($organization['Organization']['paramsConfig'], true);
            $paramsFields = json_decode($organization['Organization']['paramsFields'], true);

            $user->organization['Organization'] += $paramsConfig;
            $user->organization['Organization'] += $paramsFields;

			if($debug)
				echo "_getObjUserLocal() per il GAS ".$organization_id." \n";
			
            return $user;
        }        
}