<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'ImageTool');

class ValidateDatesController extends AppController {
	
    public function beforeFilter() {
    	 
    	parent::beforeFilter();
    	
    	/* ctrl ACL */
    	if(!$this->isRoot()) {
    		$this->Session->setFlash(__('msg_not_permission'));
    		$this->myRedirect(Configure::read('routes_msg_stop'));
    	}    	
    }

	public function admin_gcalendar_deliveries() {
		$utilsCrons = new UtilsCrons(new View(null));
		echo "<pre>";
		$utilsCrons->gcalendarUsersDeliveryInsert($this->user->organization['Organization']['id'], true);
		echo "</pre>";		
	}
	
	public function admin_index_request_payments() {
		
		App::import('Model', 'RequestPayment');
		$RequestPayment = new RequestPayment;

		$options = array();
		$options['conditions'] = array('RequestPayment.organization_id' => $this->user->organization['Organization']['id']);
        $options['fields'] = array('RequestPayment.id', 'RequestPayment.num');
		$options['order'] = array('RequestPayment.num');
        $options['recursive'] = -1;
		$results = $RequestPayment->find('all', $options);
        $newResults = array();
		foreach ($results as $result) 
			$newResults[$result['RequestPayment']['id']] = "Richiesta di pagamento num ".$result['RequestPayment']['num'];

        /*
        echo "<pre>";
		print_r($newResults);
		echo "</pre>";
	    */
            
		$this->set('results', $newResults);		
	}
		
	public function admin_index_deliveries() {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;

		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => $this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'Delivery.sys'=> 'N');	
		$options['fields'] = array('id', 'luogoData');
		$options['order'] = array('Delivery.data DESC');
		$options['recursive'] = -1;
		$results = $Delivery->find('list', $options);
		$this->set('deliveries', $results);
	}
		
	function admin_ajax_index_request_payments($request_payment_id=0) {
	
		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;
		
		App::import('Model', 'Order');
		
		$options = array();
		$options['conditions'] = array('RequestPaymentsOrder.organization_id' => $this->user->organization['Organization']['id'],
										'RequestPaymentsOrder.request_payment_id' => $request_payment_id);
		$options['recursive'] = -1;
		$results = $RequestPaymentsOrder->find('all', $options);
		
		$newResults = array();
		foreach ($results as $numResult => $result) {
		
			$order_id = $result['RequestPaymentsOrder']['order_id'];

			$options = array();
			$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
											'Order.id' => $order_id,
											'Order.isVisibleBackOffice'=> 'Y');
			$Order = new Order;											
			$orderResults = $Order->find('first', $options);

			$newResults[$numResult] = $orderResults;
			$newResults[$numResult] = $this->__getSummaryOrderImporto($orderResults);
		} // foreach ($results as $numResult => $result)
			
		/*
		echo "<pre>";
		print_r($orderResults);
		echo "</pre>";
		*/
		$this->set('results', $newResults);
		
		$this->render('admin_ajax_results');
		$this->layout = 'ajax';
	}	
	
	function admin_ajax_index_deliveries($delivery_id=0) {
	
		App::import('Model', 'Order');
		$Order = new Order;		
		
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.delivery_id' => $delivery_id,
										'Order.isVisibleBackOffice'=> 'Y');
		$options['recursive'] = 1;
		$options['order'] = array('Order.data_inizio ASC'); 
		$orderResults = $Order->find('all', $options);
		/*	
		echo "<pre>";
		print_r($orderResults);
		echo "</pre>";
		*/
		foreach ($orderResults as $numResult => $result) {
		
			$order_id = $result['Order']['id'];

			$orderResults[$numResult] = $this->__getSummaryOrderImporto($result);
	
		} // foreach ($results as $numResult => $result)
			
		/*
		echo "<pre>";
		print_r($orderResults);
		echo "</pre>";
		*/
		$this->set('results', $orderResults);
		
		$this->set('isRoot', $this->isRoot());
		$this->set('isManager', $this->isManager());
		
		$this->render('admin_ajax_results');
		$this->layout = 'ajax';
	}

	private function __getSummaryOrderImporto($orderResults) {
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
		
		$tot_importo_rimborsate = 0;
		$sql = "SELECT
					sum(importo) as tot_importo_rimborsate
				FROM
					".Configure::read('DB.prefix')."summary_orders as SummaryOrder
				WHERE
					SummaryOrder.organization_id = ".(int)$this->user->organization['Organization']['id']."
					and SummaryOrder.order_id = ".(int)$orderResults['Order']['id'];
		// echo '<br />'.$sql;
		$tot_importo_rimborsate = current($SummaryOrder->query($sql));
		if(!empty($tot_importo_rimborsate[0]['tot_importo_rimborsate']))
			$orderResults['Order']['tot_importo_rimborsate'] = $tot_importo_rimborsate[0]['tot_importo_rimborsate']; 
		else
			$orderResults['Order']['tot_importo_rimborsate'] = 0;

			// echo "<br />Order.tot_importo_rimborsate ".$orderResults['Order']['tot_importo_rimborsate'];
			
			/*
			 * $orderResults['Order']['tot_importo_rimborsate'] e' zero so non e' ancora passato al tesoriere
			 */
			if($orderResults['Order']['tot_importo_rimborsate'] != '0' && $orderResults['Order']['tot_importo_rimborsate'] != '0.00') 
				$delta = ($orderResults['Order']['tot_importo'] - $orderResults['Order']['tot_importo_rimborsate']);
			else
				$delta = 0;
				
			$orderResults['Order']['delta'] = $delta;
		
		return $orderResults;
	}	
}