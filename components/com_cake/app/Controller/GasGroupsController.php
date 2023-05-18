<?php
App::uses('AppController', 'Controller');

class GasGroupsController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();

		/* ctrl ACL */
	   	if(!isset($this->user->organization['Organization']['hasGasGroups']) || 
			$this->user->organization['Organization']['hasGasGroups']=='N') {
			if(!$this->isManager()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}		
		/* ctrl ACL */
	}

	public function admin_choice($dest_controller=null, $dest_action=null) {

		$gasGroups = [];
		$gas_group_id = [];
        if ($this->request->is('post') || $this->request->is('put')) {
			if(isset($this->request->data['OrganizationsCash']['gas_group_id']) && !empty($this->request->data['OrganizationsCash']['gas_group_id'])) {
				$gas_group_id = $this->request->data['OrganizationsCash']['gas_group_id'];
				if ($gas_group_id != '' && $gas_group_id != null) {
					setcookie('gas_group_id', $gas_group_id, time() + 86400 * 365 * 1, Configure::read('App.server'));  // (86400 secs per day for 1 years)
					$this->Session->write('gas_group_id', $gas_group_id);
					
					$dest_controller = $this->request->data['OrganizationsCash']['dest_controller'];
					$dest_action = $this->request->data['OrganizationsCash']['dest_action'];
					if(!empty($dest_controller) && !empty($dest_controller))
						$this->myRedirect(['controller' => $dest_controller, 'action' => $dest_action]);		
				}
			} 
		} // end if ($this->request->is('post') || $this->request->is('put'))

		App::import('Model', 'GasGroup');
		$GasGroup = new GasGroup;	
		$gasGroups = $GasGroup->getsByUserList($this->user, $this->user->organization['Organization']['id'], $this->user->id);
		if(empty($gasGroups)) {
			$this->Session->setFlash(__('msg_gas_groups_not_found'));
			$this->myRedirect(Configure::read('routes_msg_stop'));					
		}
		if(count($gasGroups)==1) 
			$gas_group_id = array_key_first($gasGroups);

		$this->set(compact('gasGroups', 'gas_group_id'));
		$this->set(compact('dest_controller', 'dest_action'));

	}
}