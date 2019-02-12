<?php
App::uses('AppController', 'Controller');

class DesUsersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();

        if ($this->user->organization['Organization']['hasDes'] == 'N' || $this->user->organization['Organization']['hasDesUserManager'] != 'Y') {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

		if(empty($this->user->des_id)) {
            $this->Session->setFlash(__('msg_des_choice'));
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Des&action=index';
			$this->myRedirect($url);
        }
		
        /* ctrl ACL */
        if (!$this->isManagerUserDes()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */
    }

    public function admin_index() {

        $debug = false;

        if((empty($this->user->organization['Organization']['hasUserFlagPrivacy']) || $this->user->organization['Organization']['hasUserFlagPrivacy'] == 'N') && 
		   (empty($this->user->organization['Organization']['hasUserRegistrationExpire']) || $this->user->organization['Organization']['hasUserRegistrationExpire'] == 'N')) {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
		
		$this->set('isManagerUserDes', $this->isManagerUserDes());

		$this->modelClass = 'User'; // setto User perche' codice filtro preso da User::index_flag_privacy

		App::import('Model', 'UserGroupMap');
	  	$UserGroupMap = new UserGroupMap();
		
        App::import('Model', 'De');
        $De = new De;
		
        App::import('Model', 'DesOrganization');
        $DesOrganization = new DesOrganization;
        
        App::import('Model', 'Organization');
        $Organization = new Organization;
		
        App::import('Model', 'User');
        $User = new User;
		
		App::import('Model', 'Cart');
		$Cart = new Cart;
				
        $options = [];
        $options['conditions'] = ['De.id' => $this->user->des_id];
        $options['recursive'] = -1;
        $desResults = $De->find('first', $options);
		
        /*
         * tutti i GAS del DES
         */
        $options = [];
        $options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id];
        $options['order'] = ['Organization.name' => 'asc'];
        $options['recursive'] = 1;
        $desOrganizationsResults = $DesOrganization->find('all', $options);

		if(!empty($desOrganizationsResults)) {
			
			/*
			 * filtri
			 */
			$FilterUserUsername = '';
			$FilterUserName = '';
			$FilterUserProfileCF = '';
			$FilterUserBlock = 'ALL';
			$FilterUserSort = Configure::read('orderUser');		
			$FilterUserHasUserFlagPrivacy = 'ALL';
			$FilterUserHasUserRegistrationExpire = 'ALL';
			
			/*
			 * conditions
			 */
			$conditions = [];

			/* recupero dati dalla Session gestita in appController::beforeFilter */
			if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Username')) {
				$FilterUserUsername = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Username');
				$conditions['User.username'] = "User.username LIKE '%" . $FilterUserUsername . "%'";
			}
			if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Name')) {
				$FilterUserName = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Name');
				$conditions['User.name'] = "User.name LIKE '%" . $FilterUserName . "%'";
			}
	        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'ProfileCF')) {
	            $FilterUserProfileCF = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'ProfileCF');
	            $conditions['UserProfile.CF'] = $FilterUserProfileCF;
	        } 			
			if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Block')) {
				$FilterUserBlock = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Block');
				if ($FilterUserBlock != 'ALL')
					$conditions['User.block'] = "User.block = $FilterUserBlock";  // 0 attivi / 1 disattivati
				else
					$conditions['User.block'] = "User.block IN ('0','1')";
			}
			else {
				$FilterUserBlock = 'ALL';
				$conditions['User.block'] = "User.block IN ('0','1')"; // di default li prende tutti
			}
			if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'HasUserFlagPrivacy')) {
				$FilterUserHasUserFlagPrivacy = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'HasUserFlagPrivacy');
				$conditions['UserProfile.UserFlagPrivacy'] = $FilterUserHasUserFlagPrivacy;
			}
			if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'HasUserRegistrationExpire')) {
				$FilterUserHasUserRegistrationExpire = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'HasUserRegistrationExpire');
				$conditions['UserProfile.UserRegistrationExpire'] = $FilterUserHasUserRegistrationExpire;
			}
			
			if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Sort')) 
				$FilterUserSort = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Sort');
			else 
				$FilterUserSort = Configure::read('orderUser');
			
			/* filtro */
			$this->set('FilterUserUsername', $FilterUserUsername);
			$this->set('FilterUserName', $FilterUserName);
			$this->set('FilterUserProfileCF', $FilterUserProfileCF);
			$this->set('FilterUserBlock', $FilterUserBlock);
			$this->set('FilterUserHasUserFlagPrivacy', $FilterUserHasUserFlagPrivacy);
			$this->set('FilterUserHasUserRegistrationExpire', $FilterUserHasUserRegistrationExpire);
			$this->set('FilterUserSort', $FilterUserSort);

			$block = ['ALL' => 'Tutti', '0' => 'Attivi', '1' => 'Disattivi'];
			$hasUserFlagPrivacys = ['ALL' => 'Tutti', 'Y' => __('Y'), 'N' => __('No')];
			$hasUserRegistrationExpires = ['ALL' => 'Tutti', 'Y' => __('Y'), 'N' => __('No')];
			$this->set(compact('block', 'hasUserFlagPrivacys', 'hasUserRegistrationExpires'));

			$sorts = [Configure::read('orderUser') => __('Name'), 
					  'User.registerDate' => __('registerDate')];
			$this->set('sorts', $sorts);
					
			self::d($conditions, $debug);
		
			foreach ($desOrganizationsResults as $numResult => $desOrganizationsResult) {    

		        $paramsConfig = json_decode($desOrganizationsResult['Organization']['paramsConfig'], true);
		        $paramsFields = json_decode($desOrganizationsResult['Organization']['paramsFields'], true);
		
		        $desOrganizationsResults[$numResult]['Organization'] += $paramsConfig;
		        $desOrganizationsResults[$numResult]['Organization'] += $paramsFields;
		        
		        $tmp->user->organization['Organization'] = $desOrganizationsResults[$numResult]['Organization'];
     			self::d($tmp->user->organization);
     			
				$userResults = $User->getUsersComplete($tmp->user, $conditions, $FilterUserSort, false);
				$desOrganizationsResults[$numResult]['User'] = $userResults;

			    if(isset($tmp->user->organization['Organization']['hasUserFlagPrivacy']) && $tmp->user->organization['Organization']['hasUserFlagPrivacy'] == 'Y') {
		        	
				  	$ctrlUserFlagPrivacys = $UserGroupMap->getUserFlagPrivacys($tmp->user);
			        $desOrganizationsResults[$numResult]['UserFlagPrivacy'] = $ctrlUserFlagPrivacys;
		        } 
		        
				foreach($userResults as $numResult2 => $result) {
					
					$cartResults = $Cart->getLastCartDateByUser($tmp->user, $result['User']['id'], $debug);
					$desOrganizationsResults[$numResult]['User'][$numResult2] += $cartResults;
				}
			}
		}
		        
        self::d($desOrganizationsResults,false);
		
        $this->set(compact('desResults', 'desOrganizationsResults'));
    }
}