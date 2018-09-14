<?php
App::uses('AppController', 'Controller');

class OrderLifeCyclesController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();
	}
	
	/*
	 * pagamento dei gasisti
	 * SummaryOrder.saldato_a is not null
	 */
	public function admin_summary_order($order_id) {
		
		$debug = false;
		
		if(empty($order_id)) { 
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
				
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
								  'Order.id' => $order_id];
		$options['recursive'] =  1;
		$results = $this->OrderLifeCycle->find('first', $options);
		if(empty($results)) { 
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$resultsPaidUsers = $this->OrderLifeCycle->getPaidUsers($this->user, $results, $debug);
		 
		$results['SummaryOrder']['SummaryOrderPaid'] = $resultsPaidUsers['summaryOrderPaid'];
		$results['SummaryOrder']['SummaryOrderNotPaid'] = $resultsPaidUsers['summaryOrderNotPaid'];
			
		self::d($results,$debug);
	
	    $can_state_code_to_close = $this->OrderLifeCycle->canStateCodeToClose($this->user, $results, $debug);
		$results['Order']['can_state_code_to_close'] = $can_state_code_to_close;
		
		switch($this->user->organization['Template']['payToDelivery']) {
			case "POST":			
				$results['RequestPayment'] = $this->_getRequestPaymentsOrder($this->user, $order_id);
			break;
			case "ON":
			
			break;
			case "ON-POST":
				$results['RequestPayment'] = $this->_getRequestPaymentsOrder($this->user, $order_id);			
			break;
		}
		
		$this->set('results', $results);
	}
	
	/*
	 * pagamento al produttore
	 * SummaryOrder.saldato_a is not null
	 */
	public function admin_pay_suppliers($order_id) {
		
		if(empty($order_id)) { 
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
				
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
								  'Order.id' => $order_id
								  ];
		$options['recursive'] =  1;
		$results = $this->OrderLifeCycle->find('first', $options);
		if(empty($results)) { 
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$results += $this->OrderLifeCycle->getPaidSupplier($this->user, $results);
		
		$this->set('results', $results);
	}
	
	private function _getRequestPaymentsOrder($user, $order_id, $debug=false) {
		
		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;

		$options = [];
		$options['conditions'] = ['RequestPaymentsOrder.organization_id' => $user->organization['Organization']['id'],
								  'RequestPaymentsOrder.order_id' => $order_id];
		$options['recursive'] =  0;	
		$RequestPaymentsOrder->unbindModel(['belongsTo' => ['Order']]);
		$requestPaymentsOrderResults = $RequestPaymentsOrder->find('first', $options);

		return $requestPaymentsOrderResults;
	}		
}