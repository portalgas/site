<?php
App::uses('AppController', 'Controller');

class DesOrganizationsController extends AppController {
  
   public function beforeFilter() {
   		parent::beforeFilter();
   		
		/* ctrl ACL */
   		if($this->user->organization['Organization']['hasDes']=='N' || !$this->isDes()) {
   			$this->Session->setFlash(__('msg_not_organization_config'));
   			$this->myRedirect(Configure::read('routes_msg_stop'));
   		}
		/* ctrl ACL */
		
  		$this->set('isManagerDes', $this->isManagerDes());
   		$this->set('isReferenteDes', $this->isReferenteDes());
   		$this->set('isSuperReferenteDes', $this->isSuperReferenteDes());
   		$this->set('isTitolareDesSupplier', $this->isTitolareDesSupplier());		
   }

	public function admin_choice() {

		if($this->request->is('post') || $this->request->is('put') &&
			(isset($this->request->data['DesOrganization']['des_id']) && !empty($this->request->data['DesOrganization']['des_id']))) {
			
				$this->Session->setFlash('D.E.S. scelto');
				$this->myRedirect(array('controller' => 'DesOrders', 'action' => 'index', 'admin' => true));
		}
		else {
			$options = [];
			$options['conditions'] = array('DesOrganization.organization_id' => $this->user->organization['Organization']['id']);
			$options['order'] = array('De.name');
			$results = $this->DesOrganization->find('all', $options);
			
			/*
			if(count($results)== 1) {
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesOrders&action=index&DesOrganization.des_id='.$results[0]['De']['id'];
				$this->myRedirect($url);
			}
			*/
			
			$newResults = [];
			foreach ($results as $result) 
				$newResults[$result['De']['id']] = $result['De']['name'];
				
			$this->set('desOrganizations', $newResults);
		}
	}
}