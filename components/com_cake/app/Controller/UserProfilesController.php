<?php
App::uses('AppController', 'Controller');

class UserProfilesController extends AppController {

	private $organization_id;
	
    public function beforeFilter() {
        parent::beforeFilter();

		$isManager = $this->isManager();
		$isManagerUserDes = $this->isManagerUserDes();
		
		$this->set(compact('isManager', 'isManagerUserDes'));

        /* ctrl ACL */		
        if (!$isManager && !$isManagerUserDes) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

		$organization_id = $this->request->params['pass']['organization_id'];
		self::d($this->request->params['pass']['organization_id']);
		$this->organization_id = $this->aclOrganizationIdinUso($organization_id);
		if($this->organization_id===false) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));		
		}		

		if (!$isManagerUserDes && ($this->user->organization['Organization']['id'] != $this->organization_id)) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */        
    }

	/* 
	 * se $organization_id!=0  => isManagerUserDes organization_id del suo DES
	 * se $organization_id=0   => isManager solo su organization_id
	 *
	 * passato un campo (UserProfile.hasUserFlagPrivacy) inverte il valore Y => N
	 */
    public function admin_inverseValue($organization_id=0, $user_id, $field, $format='notmpl') {

		$debug = false;

		$value = $this->UserProfile->getValue($this->user, $user_id, $field, 'N', $debug);
		self::d('UserProfiles::inverseValue field '.$field.' '.$value, $debug);	

		switch ($value) {
			case 'Y':
				$value = 'N';
			break;
			case 'N':
				$value = 'Y';
			break;
			default:
				$value = 'N';
			break;
		}
		self::d('UserProfiles::inverseValue field '.$field.' '.$value, $debug);

		$esito = $this->UserProfile->setValue($this->user, $user_id, $field, $value, $debug);
	
        $this->set('content_for_layout', $esito);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
   }
   
   public function admin_userFlagPrivacyN($organization_id=0, $action_back_controller='Users', $action_back_action='index') {
   
    	$debug = false;
        
        App::import('Model', 'User');
        $User = new User;
           
        $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $this->organization_id]);
           
 		$userResults = $User->getAllUsers($tmp_user);

		if(!empty($userResults)) {
			foreach($userResults as $userResult) {
				
				self::d($userResult);
				
				$esito = $this->UserProfile->setValue($this->user, $userResult['User']['id'], 'hasUserFlagPrivacy', 'N', $debug);
			}

            $this->Session->setFlash(__('The userProfile.FlagPrivacyN has been saved'));
		}
		else {
            $this->Session->setFlash(__('msg_search_not_result_users'));
		}
    
        $this->myRedirect(['controller' => $action_back_controller, 'action' => $action_back_action]);
   }
   
   public function admin_userRegistrationExpireN($organization_id=0, $action_back_controller='Users', $action_back_action='index') {
   
    	$debug = false;
        
        App::import('Model', 'User');
        $User = new User;
           
        $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $this->organization_id]);
 		$userResults = $User->getAllUsers($tmp->user);
		
		if(!empty($userResults)) {
			foreach($userResults as $userResult) {
				
				self::d($userResult);
				
				$esito = $this->UserProfile->setValue($this->user, $userResult['User']['id'], 'hasUserRegistrationExpire', 'N', $debug);
			}

            $this->Session->setFlash(__('The userProfile.RegistrationExpireN has been saved'));
		}
		else {
            $this->Session->setFlash(__('msg_search_not_result_users'));
		}
		
        $this->myRedirect(['controller' => $action_back_controller, 'action' => $action_back_action]);
   }
}