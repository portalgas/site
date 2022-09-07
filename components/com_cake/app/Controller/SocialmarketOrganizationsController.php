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

        App::import('Model', 'Organization');
        $Organization = new Organization;

        $options = [];
        $options['fields'] = ['id'];
        $options['conditions'] = ['type' => 'GAS', 'stato' => 'Y'];
        $options['recursive'] = -1;

        $organizationResults = $Organization->find('all', $options);
        $organization_ids = [];
        foreach ($organizationResults as $organizationResult) {
            array_push($organization_ids, $organizationResult['Organization']['id']);
        }

		$this->set(compact('results', 'organization_ids'));
	}
}