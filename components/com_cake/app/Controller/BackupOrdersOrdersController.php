<?php
App::uses('AppController', 'Controller');

class BackupOrdersOrdersController extends AppController {
   
   public function beforeFilter() {
   		parent::beforeFilter();
   		
   		/* ctrl ACL */	   		 
		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
   }
       
   public function admin_index() {

		$debug = false;
		
	   	App::import('Model', 'Supplier');
	   	
	   	App::import('Model', 'BackupOrdersDesOrdersOrganization');

		App::import('Model', 'RequestPayment');
		$RequestPayment = new RequestPayment;
		
		$SqlLimit = 75;
		$conditions = ['BackupOrdersOrder.organization_id' => $this->user->organization['Organization']['id']];
		$order = 'Delivery.data asc, Delivery.id, BackupOrdersOrder.data_inizio asc';

		$this->BackupOrdersOrder->recursive = 0;

		self::d($conditions, $debug);
		self::d($order, $debug);
		
	    $this->paginate = ['conditions' => $conditions, 'order' => $order, 'limit' => $SqlLimit];
		$results = $this->paginate('BackupOrdersOrder');
		foreach($results as $numResult => $result) {
	
			/*
			 * Suppliers per l'immagine
			 * */
			$Supplier = new Supplier;
			
			$options = [];
			$options['conditions'] = ['Supplier.id' => $result['SuppliersOrganization']['supplier_id']];
			$options['fields'] = ['Supplier.img1'];
			$options['recursive'] = -1;
			$SupplierResults = $Supplier->find('first', $options);
			if(!empty($SupplierResults))
				$results[$numResult]['Supplier']['img1'] = $SupplierResults['Supplier']['img1'];
			
			/*
			 * DES
			 */		
			if($this->user->organization['Organization']['hasDes']=='Y') {		
	
				$BackupOrdersDesOrdersOrganization = new BackupOrdersDesOrdersOrganization();
				
				$options = [];
				$options['conditions'] = ['BackupOrdersDesOrdersOrganization.order_id' => $result['BackupOrdersOrder']['id'],
										'BackupOrdersDesOrdersOrganization.organization_id' => $this->user->organization['Organization']['id']];
				$options['recursive'] = -1;
				$backupOrdersDesOrdersOrganizationResults = $BackupOrdersDesOrdersOrganization->find('first', $options);
				$results[$numResult]['BackupOrdersDesOrdersOrganization'] = $backupOrdersDesOrdersOrganizationResults['BackupOrdersDesOrdersOrganization'];
				// debug($options);
				// debug($backupOrdersDesOrdersOrganizationResults);
			} // DES
			 			 
			 /*
			  * recupero richiesta di pagamento 
			  */ 
			$results[$numResult]['BackupOrdersOrder']['request_payment_num'] = '';
			$results[$numResult]['BackupOrdersOrder']['request_payment_id'] = '';
			if($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery']=='ON-POST') {
				$results[$numResult]['BackupOrdersOrder']['request_payment_num'] = $RequestPayment->getRequestPaymentNumByOrderId($this->user, $result['BackupOrdersOrder']['id']);
				$results[$numResult]['BackupOrdersOrder']['request_payment_id'] = $RequestPayment->getRequestPaymentIdByOrderId($this->user, $result['BackupOrdersOrder']['id']);
			} 
			  
		} // loop Orders
 
		$this->set(compact('results'));
		$this->set(compact('SqlLimit'));
		
		/*
		 * ctrl se ho i permessi per modificare le consegne
		 */
		if(!$this->isManager() && !$this->isManagerDelivery())
			$delivery_link_permission = false;
		else
			$delivery_link_permission = true;
		$this->set('delivery_link_permission', $delivery_link_permission);
		
		/*
		 * per ogni ordine CLOSE ctrl se richiesto il pagamento
		 */
		 $this->set('isRoot', $this->isRoot());
		 $this->set('isTesoriereGeneric', $this->isTesoriereGeneric());
	}
	
   public function admin_resume($order_id) {

		if (empty($order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$debug = false;
	
		/*
		 * ripristina il backup di Order / ArticlesOrder / Cart
		 */
		App::import('Model', 'BackupOrder');
		$BackupOrder = new BackupOrder();				
		$results = $BackupOrder->resumeData($this->user, $order_id, $debug);	
		if($results===false) 
			$this->Session->setFlash("Non esiste piu' la consegna!");
		else {
			$BackupOrder->deleteData($this->user, $order_id, $debug);		
			$this->Session->setFlash(__('Order resume'));
		}
		
		$this->myRedirect(['action' => 'index']);
   }
}