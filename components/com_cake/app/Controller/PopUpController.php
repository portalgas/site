<?php
App::uses('AppController', 'Controller');

class PopUpController extends AppController {
	
	public $components = array('Cookie');
	
	public $helpers = array('App',
							'Html',
							'Form',
							'Time',
							'Ajax',
							'Tabs');

    public function beforeFilter() {
    	//$this->ctrlHttpReferer();
    	 
    	parent::beforeFilter();
    }
    
	public function delivery_info() {
		$this->layout = 'ajax';
	}
	
	public function order_mail_open_testo($order_id) {
		if($order_id==0) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	 

		App::import('Model', 'Order');
		$Order = new Order;
						
		$options = array();
		$options['conditions'] = array('Order.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Order.id' => (int)$order_id);
		$options['fields'] = array('Order.mail_open_testo');
		$options['recursive'] = -1;
		$results = $Order->find('first', $options);
		$this->set('results', $results);
				
		$this->layout = 'ajax';
	}

	/*
	 * popup per conflitto tra i moduli
	 */
	public function admin_summary_order_just_populate($orderHasTrasport='N', $orderHasCostMore='N', $orderHasCostLess='N') {
		$cookie_name = 'summary_order_just_populate';
		
		$this->set('order_id', $this->order_id);
		$this->set('cookie_name', $cookie_name);
		
		$this->set('orderHasTrasport', $orderHasTrasport);
    	$this->set('orderHasCostMore', $orderHasCostMore);
    	$this->set('orderHasCostLess', $orderHasCostLess);
		
		$this->layout = 'ajax';
	}

	/*
	 * popup per conflitto tra i moduli
	 */
	public function admin_order_change_qta($orderHasTrasport='N', $orderHasCostMore='N', $orderHasCostLess='N') {
		$cookie_name = 'order_change_qta';
		
		$this->set('order_id', $this->order_id);
		$this->set('cookie_name', $cookie_name);
		
		$this->set('orderHasTrasport', $orderHasTrasport);
    	$this->set('orderHasCostMore', $orderHasCostMore);
    	$this->set('orderHasCostLess', $orderHasCostLess);
		
		$this->layout = 'ajax';
	}
	
	/*
	 * popup per conflitto tra i moduli
	 */	
	public function admin_order_change_carts_one($orderHasTrasport='N', $orderHasCostMore='N', $orderHasCostLess='N'){
		$cookie_name = 'order_change_carts_one';
		
		$this->set('order_id', $this->order_id);
		$this->set('cookie_name', $cookie_name);
		
		$this->set('orderHasTrasport', $orderHasTrasport);
    	$this->set('orderHasCostMore', $orderHasCostMore);
    	$this->set('orderHasCostLess', $orderHasCostLess);
		
		$this->layout = 'ajax';
	}

	/*
	 * popup per conflitto tra i moduli
	 */	
	public function admin_summary_order_change($orderHasTrasport='N', $orderHasCostMore='N', $orderHasCostLess='N') {
		$cookie_name = 'summary_order_change';
		
		$this->set('order_id', $this->order_id);
		$this->set('cookie_name', $cookie_name);
		
		$this->set('orderHasTrasport', $orderHasTrasport);
    	$this->set('orderHasCostMore', $orderHasCostMore);
    	$this->set('orderHasCostLess', $orderHasCostLess);
		
		$this->layout = 'ajax';
	}
	
	/*
	 * popup per il Cassiere perche' co sono consegne scadute ancora da chiudere
	 */
	public function admin_alert_cassiere_deliveries_to_close() {		
		$this->layout = 'ajax';
	}
	
	/*
	 * popup per ordine importi aggregati, aggiorna dati
	 *  $state_code=='PROCESSED-BEFORE-DELIVERY' 
     *  $state_code=='PROCESSED-POST-DELIVERY'  
	 *  $state_code=='INCOMING-ORDER'
	 */	
	public function admin_order_importi_aggregati_aggiorna($order_id=0) {
		$this-> __admin_order_importi_aggregati($order_id); 	
	}	 

	/*
	 * popup per ordine importi aggregati, ritorna al referente
	 *  $state_code=='WAIT-PROCESSED-TESORIERE'
	 */		
	public function admin_order_importi_aggregati_return_tesoriere($order_id=0) {
		$this-> __admin_order_importi_aggregati($order_id);	
	}
	 
	/*
	 * popup per ordine importi aggregati, ritorna al referente
	 *  $state_code=='WAIT-PROCESSED-TESORIERE'
	 */		
	public function admin_order_importi_aggregati_return_cassiere($order_id=0) {
		$this-> __admin_order_importi_aggregati($order_id);	
	}
	
	private function __admin_order_importi_aggregati($order_id) {
		if($order_id==0) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	 

		App::import('Model', 'Order');
		$Order = new Order;
						
		$options = array();
		$options['conditions'] = array('Order.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Order.id' => (int)$order_id);
		$options['recursive'] = -1;
		$results = $Order->find('first', $options);
		$this->set('results', $results);
		
		$this->layout = 'ajax';	
	}
}