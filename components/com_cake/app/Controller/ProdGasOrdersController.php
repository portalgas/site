<?php
App::uses('AppController', 'Controller');

class ProdGasOrdersController extends AppController {
														
	public function beforeFilter() {
		parent::beforeFilter();
		
		/* ctrl ACL */
		if(empty($this->user->supplier['Supplier'])) {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	
		/* ctrl ACL */	
	}
	
	/*
	 * estraggo tutti gli ordini di un GAS
	 */
	public function admin_index($organization_id=0) { 
	
		$debug = false;
		
        if (empty($organization_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		// ACL 
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;

		$organizationResults = $ProdGasSupplier->getOrganizationAssociate($this->user, $organization_id, 0, $debug);
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
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' =>  $organization_id,
									   'Delivery.isVisibleBackOffice'=>'Y',
									   'Delivery.stato_elaborazione'=>'OPEN',
									   'Order.organization_id' => $organization_id,
									   'Order.supplier_organization_id' => $organizationResults['SuppliersOrganization']['id']);
		$options['order'] = array('Delivery.data asc, Delivery.id, Order.data_inizio asc');
		$options['recursive'] = 1;
		$results = $Order->find('all', $options);	
		$this->set(compact('results'));
		/*
		echo "<pre>results \n";
		print_r($results);
		echo "</pre>";		
		*/
		
		/*
		 * dati GAS con il quale sto lavorando
		 */
		App::import('Model', 'Organization');
		$Organization = new Organization;
		
		$options = array();
		$options['conditions'] = array('Organization.id' => $organization_id);
		$organizations = $Organization->find('first', $options);
		$this->set(compact('organizations'));		 		
	}	
	
	public function admin_print($organization_id=0, $order_id=0) { 
	
		$debug = false;
		
        if (empty($organization_id) || empty($order_id) ) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		// ACL 
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;

		$organizationResults = $ProdGasSupplier->getOrganizationAssociate($this->user, $organization_id, 0, $debug);
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
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' =>  $organization_id,
									   'Delivery.isVisibleBackOffice'=>'Y',
									   'Delivery.stato_elaborazione'=>'OPEN',
									   'Order.organization_id' => $organization_id,
									   'Order.supplier_organization_id' => $organizationResults['SuppliersOrganization']['id']);
		$options['order'] = array('Delivery.data asc, Delivery.id, Order.data_inizio asc');
		$options['recursive'] = 1;
		$results = $Order->find('all', $options);	
		$this->set(compact('results'));
		/*
		echo "<pre>results \n";
		print_r($results);
		echo "</pre>";		
		*/
	}		
}