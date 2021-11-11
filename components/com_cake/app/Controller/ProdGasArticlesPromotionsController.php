<?php
App::uses('AppController', 'Controller');

class ProdGasArticlesPromotionsController extends AppController {

    public $components = array('RequestHandler');
    private $order;

    public function beforeFilter() {
        parent::beforeFilter();

		/* ctrl ACL */
		if($this->user->organization['Organization']['type']!='PRODGAS') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	
		/* ctrl ACL */
    }
}