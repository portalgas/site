<?php
App::uses('AppController', 'Controller');

class ProdGasSuppliersController extends AppController {
														
	public function beforeFilter() {
		parent::beforeFilter();
		
		/* ctrl ACL */
		if($this->user->organization['Organization']['type']!='PRODGAS') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	
		/* ctrl ACL */	
	}
	
	public function admin_index() { 
				
		$debug = false;

		// precedente versione $results = $this->ProdGasSupplier->getOrganizationsAssociate($this->user, 0, $debug);
		
		App::import('Model', 'ProdGasSuppliersImport');
		$ProdGasSuppliersImport = new ProdGasSuppliersImport;
		
		$results = $ProdGasSuppliersImport->getProdGasSuppliers($this->user, $this->user->organization['Organization']['id'], 0, [], $debug);
		
		if($debug) debug($results);

		$this->set(compact('results'));
	}
	
	/*
	 * estraggo tutti gli ordini di un GAS
	 */
	public function admin_index_orders($organization_id=0) { 
	
		$debug = false;
		
        if (empty($organization_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		// ACL 
		$organizationResults = $this->ProdGasSupplier->getOrganizationAssociate($this->user, $organization_id, 0, $debug);
		if($organizationResults['SuppliersOrganization']['can_view_orders']!='Y' && $organizationResults['SuppliersOrganization']['can_view_orders_users']!='Y') {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		
		// ACL
		
		/*
		 * get elenco Article del GAS ed eventuali ArticlesOrder: se c'e' non posso cancellarlo
		 */	
		App::import('Model', 'Order');
		$Order = new Order;

		$Order->unbindModel(array('belongsTo' => array('SuppliersOrganization',)));
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' =>  $organization_id,
								   'Delivery.isVisibleBackOffice'=>'Y',
								   'Delivery.stato_elaborazione'=>'OPEN',
								   'Order.organization_id' => $organization_id,
								   'Order.supplier_organization_id' => $organizationResults['SuppliersOrganization']['id']];
		$options['order'] = ['Delivery.data asc, Delivery.id, Order.data_inizio asc'];
		$options['recursive'] = 1;
		$results = $Order->find('all', $options);	
		$this->set(compact('results'));
		
		self::d($results, false);
	}	
}