<?php
App::uses('AppController', 'Controller');

class GdxpsController extends AppController {
   
   public $components = ['Connects']; 

   public function beforeFilter() {
   		parent::beforeFilter();
   }

   public function admin_order_export($delivery_id, $order_id) {
   
		$debug = false;
		
		if($this->user->organization['Organization']['hasOrdersGdxp']!='Y') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
	
		App::import('Model', 'Order');
		$Order = new Order;

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
		$options['recursive'] = -1;
		$orderResults = $Order->find('first', $options);
		if($debug) debug($orderResults); 
		
		if (empty($orderResults)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		$params = ['order_type_id' => $orderResults['Order']['order_type_id'], 'order_id' => $this->order_id];
		$url = $this->Connects->createUrlBo('admin/gdxps', 'order_export', $params);
		if($debug) debug($url); 
		
		if(!$debug) $this->myRedirect($url);
	}
}