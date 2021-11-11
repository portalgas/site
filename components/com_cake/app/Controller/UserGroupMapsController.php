<?php
App::uses('AppController', 'Controller');

class UserGroupMapsController extends AppController {

	public function beforeFilter() {
	   parent::beforeFilter();
	   
	   /* ctrl ACL */
	   if(!$this->isManager()) {
   			$this->Session->setFlash(__('msg_not_permission'));
   			$this->myRedirect(Configure::read('routes_msg_stop'));
   		}
	   /* ctrl ACL */
	   
	   /*
	    * elenco di tutti i gruppi dell'organization UserGroupsComponent escludendo i DES
	   */
		foreach ($this->userGroups as $group_id => $data) {
			if($data['type']=='DES')	
				unset($this->userGroups[$group_id]);
		}
	   
	   $this->set('userGroups',$this->userGroups);
	}

	public function admin_intro() {
		
		$debug = false;
		
		App::import('Model', 'UserGroupMap');
		$UserGroupMap = new UserGroupMap;
		
		$this->set('isManager',$this->isManager());
	
		/*
		 * totale utenti associati ad un ruolo
		*/
		foreach ($this->userGroups as $group_id => $data) {
			$this->userGroups[$group_id]['tot_users'] = $UserGroupMap->getTotUserByGroupId($this->user, $group_id, $debug);			
		}
			
		$this->set('userGroups', $this->userGroups);	
	}
	
	/*
	 * elenco di tutti gli users associati al ruolo
	*/
	public function admin_index($group_id) {
	
		if (empty($group_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		App::import('Model', 'User');
		$User = new User;
		
		$conditions = array('UserGroup.id' => $group_id);
		$results = $User->getUsers($this->user, $conditions);
				
		$this->set('results',$results);
		$this->set('group_id',$group_id);
		
		$this->set('add_user', $this->_canAddUser($group_id, $results));
	}
	
	/*
	 * aggiungo un utente al gruppo passato (UserGroups)
	*/
	public function admin_edit($group_id=null) {
		
		$debug = false;
		
		App::import('Model', 'User');
		$User = new User;
		
		if ($this->request->is('post') || $this->request->is('put')) {

			$group_id = $this->request->data['UserGroupMap']['group_id'];
			if($debug) echo '<br />group_id '.$group_id;
			
			$user_id = $this->request->data['UserGroupMap']['users'];
			if($debug) echo '<br />user_id '.$user_id;
			
			if(!empty($user_id)) {
				/*
				 * aggiungo gruppo joomla gasUserGroupMap se non ci appartiene gia'
				*/
				App::import('Model', 'User');
				$User = new User;
	
				if($debug) echo '<br />group_id '.$group_id;
				$User->joomlaBatchUser($group_id, $user_id, 'add', $debug);
	
				$this->Session->setFlash(__('The UserGroups has been saved').' '.$this->userGroups[$group_id]['name']);
			}
			else
				$this->Session->setFlash(__('The UserGroup could not be saved. Please, try again.').' '.$this->userGroups[$group_id]['name']);
	
			if(!$debug) $this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=UserGroupMaps&action=index&group_id='.$group_id);
		}  // end if ($this->request->is('post') || $this->request->is('put'))
			
		/*
		 * ids dei utenti gia' associti per escluderli dalla lista degli utenti
		*/
		$conditions = array('UserGroup.id' => $group_id);
		$usersUserGroups = $User->getUsers($this->user, $conditions);
		
		/*
		 * se group_id = Configure::write('group_id_storeroom',9) posso solo avere 1 user
		 */
	 
		if($group_id == Configure::read('group_id_storeroom') && !empty($usersUserGroups)) {
			$this->Session->setFlash("Lo user DISPENSA è già stato creato");	
			$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=UserGroupMaps&action=index&group_id='.$group_id);			
		}
		
		$user_ids = '';
		foreach ($usersUserGroups as $usersUserGroups) {
			$user_ids = $user_ids.$usersUserGroups['User']['id'].',';
		}

		if(!empty($user_ids)) {
			$user_ids = substr($user_ids, 0, (strlen($user_ids)-1));
			
			$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'),
								'UserGroupMap.user_id NOT IN' => '('.$user_ids.')');
		}
		else
			$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
			
		$users = $User->getUsersList($this->user, $conditions);
		$this->set(compact('users'));
		
		$this->set('group_id',$group_id);
	}	
	
	/*
	 * cancello un utente dal ruolo
	*/
	public function admin_delete($user_id, $group_id) {
	
		if(!empty($user_id)) {
				
			/*
			 * aggiungo gruppo joomla se non ci appartiene gia'
			*/
			App::import('Model', 'User');
			$User = new User;
	
			$User->joomlaBatchUser($group_id, $user_id, 'del');
	
			$this->Session->setFlash(__('Delete UserGroup').' '.$this->userGroups[$group_id][$name]);
		}
		else
			$this->Session->setFlash(__('UserGroup was not deleted'));
	
		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=UserGroupMaps&action=index&group_id='.$group_id);
	}
	
	/*
	 * se group_id = Configure::write('group_id_storeroom',9) posso solo avere 1 user
	 */
	private function _canAddUser($group_id, $results) {
		$add_user = true;
		if($group_id == Configure::read('group_id_storeroom')) {
			if(empty($results))
				$add_user = true;
			else
				$add_user = false;
		}
		
		return $add_user;		
	}
}