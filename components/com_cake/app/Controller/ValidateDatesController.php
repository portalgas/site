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

		$options = [];
		$options['conditions'] = array('RequestPayment.organization_id' => $this->user->organization['Organization']['id']);
        $options['fields'] = array('RequestPayment.id', 'RequestPayment.num');
		$options['order'] = array('RequestPayment.num');
        $options['recursive'] = -1;
		$results = $RequestPayment->find('all', $options);
        $newResults = [];
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

		App::import('Model', 'Organization');
		$Organization = new Organization;
		
		$options = [];
		$options['conditions'] = array('Delivery.organization_id' => $this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'Delivery.sys'=> 'N');	
		$options['fields'] = array('id', 'luogoData');
		$options['order'] = array('Delivery.data DESC');
		$options['recursive'] = -1;
		$results = $Delivery->find('list', $options);
		$this->set('deliveries', $results);
		
		$organizationPayToDelivery = $Organization->getPayToDelivery($this->user->organization['Template']['payToDelivery']);
		$this->set('organizationPayToDelivery', $organizationPayToDelivery);
	}
		
	function admin_ajax_index_request_payments($request_payment_id=0) {
	
		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;
		
		App::import('Model', 'Order');
		
		$options = [];
		$options['conditions'] = array('RequestPaymentsOrder.organization_id' => $this->user->organization['Organization']['id'],
										'RequestPaymentsOrder.request_payment_id' => $request_payment_id);
		$options['recursive'] = -1;
		$results = $RequestPaymentsOrder->find('all', $options);
		
		$newResults = [];
		foreach ($results as $numResult => $result) {
		
			$order_id = $result['RequestPaymentsOrder']['order_id'];

			$options = [];
			$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
											'Order.id' => $order_id,
											'Order.isVisibleBackOffice'=> 'Y');
			$Order = new Order;											
			$orderResults = $Order->find('first', $options);

			$newResults[$numResult] = $orderResults;
			$newResults[$numResult] = $this->_getSummaryOrderImporto($orderResults);
		} // foreach ($results as $numResult => $result)
			
		/*
		echo "<pre>";
		print_r($orderResults);
		echo "</pre>";
		*/
		$this->set('results', $newResults);
		
		$this->layout = 'ajax';
		$this->render('admin_ajax_results');
	}	
	
	function admin_ajax_index_deliveries($delivery_id=0) {
	
		App::import('Model', 'Order');
		$Order = new Order;		
		
		App::import('Model', 'Cart');
		$Cart = new Cart;	
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;		
		
		$SummaryOrder->unbindModel(['belongsTo' => ['Delivery', 'Order']]);
		
		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;
		
		$RequestPaymentsOrder->unbindModel(['belongsTo' => ['Order']]);
		
		$options = [];
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

			$orderResults[$numResult] = $this->_getSummaryOrderImporto($result);

			$conditions = []; 
			$conditions['Order.id'] = $order_id;
			$orderResults[$numResult]['Cart']['totImporto'] = $Cart->getTotImporto($this->user, $conditions);
			$orderResults[$numResult]['Cart']['totImporto_e'] = number_format($orderResults[$numResult]['Cart']['totImporto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			
			/*
			 * summaryOrder
			 */
			$options = [];
			$options['conditions'] = array('SummaryOrder.organization_id' => $this->user->organization['Organization']['id'],
											'SummaryOrder.delivery_id' => $delivery_id,
											'SummaryOrder.order_id'=> $order_id);
			$options['recursive'] = 1;
			$options['order'] = array('SummaryOrder.user_id ASC'); 
			$summaryOrderResults = $SummaryOrder->find('all', $options);
		
			$orderResults[$numResult]['SummaryOrder'] = $summaryOrderResults;
		
			/*
			 *
			 */
			$requestPaymentsOrderResults = []; 
			if($this->user->organization['Template']['payToDelivery']=='POST' || $this->user->organization['Template']['payToDelivery']=='ON-POST') {
				$options = [];
				$options['conditions'] = ['RequestPaymentsOrder.organization_id' => $this->user->organization['Organization']['id'],
										  'RequestPaymentsOrder.delivery_id' => $delivery_id,
										  'RequestPaymentsOrder.order_id', $order_id];
				$options['recursive'] = 0;
				$requestPaymentsOrderResults = $RequestPaymentsOrder->find('first', $options);	
			}
			$orderResults[$numResult]['RequestPayment'] = $requestPaymentsOrderResults;
			
		} // foreach ($results as $numResult => $result)
			
		/*
		echo "<pre>";
		print_r($orderResults);
		echo "</pre>";
		*/
		$this->set('results', $orderResults);
		
		$this->set('isRoot', $this->isRoot());
		$this->set('isManager', $this->isManager());
		
		$this->layout = 'ajax';
		$this->render('admin_ajax_results');
	}

	private function _getSummaryOrderImporto($orderResults) {
		
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
		self::d($sql, false);
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