<?php
App::uses('AppController', 'Controller');

class SocialmarketOrganizationsController extends AppController {
		
	private $str_log = '';
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		if(!$this->isRoot()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
	}

	public function admin_index() { 

		$debug = false;

		$results = $this->SocialmarketOrganization->getSuppliers($this->user, $debug);

		$this->set(compact('results'));		
	}
}