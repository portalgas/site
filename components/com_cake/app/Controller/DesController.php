<?php

App::uses('AppController', 'Controller');

/**
 * Des Controller
 *
 * @property De $De
 * @property PaginatorComponent $Paginator
 */
class DesController extends AppController {

    public $components = array('Paginator');

    public function beforeFilter() {
        parent::beforeFilter();

		if ($this->user->organization['Organization']['hasDes'] == 'N') {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
    }

    public function admin_index() {
		
        App::import('Model', 'DesOrganization');
        $DesOrganization = new DesOrganization;

        $options = [];
        $options['conditions'] = ['De.id' => $this->user->des_id];
        $options['recursive'] = -1;
        $desResults = $this->De->find('first', $options);

        /*
         * tutti i GAS del DES
         */
        $options = [];
        $options['conditions'] = array('DesOrganization.des_id' => $this->user->des_id);
        $options['order'] = array('Organization.name' => 'asc');
        $options['recursive'] = 1;
        $desOrganizationsResults = $DesOrganization->find('all', $options);

        self::d($desOrganizationsResults,false);
		
        $this->set(compact('desResults', 'desOrganizationsResults'));
    }
}
