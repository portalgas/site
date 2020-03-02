<?php
App::uses('AppModel', 'Model');

class RequestPaymentsOrder extends AppModel {

	/*
	* call RequestPayment::edit()
 	*
	 * Template.payToDelivery = POST / ON-POST 
     * Template.payToDelivery = ON => mai, ha gli stati (PROCESSED-ON-DELIVERY, SUPPLIER-PAID)
     *
	 * gestisco Order.state_code successivo degli ordini associati ad una richiesta di pagamento
	 *
	 * ordini di una rich di pagamento
	 *  - TO-PAYMENT (Associato ad una richiesta di pagamento)
	 *  - USER-PAID (Da saldare da parte dei gasisti) => se Y => WAIT-REQUEST-PAYMENT-CLOSE 
	 *  - WAIT-REQUEST-PAYMENT-CLOSE => se tutti calcolo se SUPPLIER-PAID o CLOSE
	 */
	public function setOrdersStateCodeByRequestPaymentId($user, $request_payment_id, $state_code_next='', $debug=false) {

		// $debug=true;
		
		$requestPaymentsOrderTot = 0;

		self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - Ctrl gli ordini della rich  ".$request_payment_id, $debug);

		App::import('Model', 'RequestPayment');
		$RequestPayment = new RequestPayment;

		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;
		
		App::import('Model', 'SummaryOrderLifeCycle');
		$SummaryOrderLifeCycle = new SummaryOrderLifeCycle;
		
		/*
		 * dati richiesta pagamento
		 */
		$options = [];
		$options['conditions'] = ['RequestPayment.organization_id' => $user->organization['Organization']['id'],
							      'RequestPayment.id' => $request_payment_id];
		$options['recursive'] = -1;
		$requestPaymentResults = $RequestPayment->find('first', $options);
		if(empty($requestPaymentResults))
			self::xx('RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId - request_payment_id ['.$request_payment_id.'] empty');
			
		self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - RequestPayment.stato_elaborazione  ".$requestPaymentResults['RequestPayment']['stato_elaborazione']." id [".$requestPaymentResults['RequestPayment']['id']."]", $debug);
		
		switch ($requestPaymentResults['RequestPayment']['stato_elaborazione']) {
			case 'WAIT': // in lavorazione
				$state_code_next='TO-PAYMENT';  // Associato ad una richiesta di pagamento
				self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - Orders.state_code_next  ".$state_code_next, $debug);
			break;
			case 'OPEN': // Aperta per richiedere il pagamento
				$state_code_next=''; // lo calcolo x ogni Order
				self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - Orders.state_code_next => lo calcolo ", $debug);
			break;
			case 'CLOSE': // Chiusa
				$state_code_next=''; // lo calcolo x ogni Order
				self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - Orders.state_code_next => lo calcolo ", $debug);
			break;	
		}
		
		$options = [];
		$options['conditions'] = ['RequestPaymentsOrder.organization_id' => $user->organization['Organization']['id'],
							      'RequestPaymentsOrder.request_payment_id' => $request_payment_id];
		$options['recursive'] = 0;
		$requestPaymentsOrderResults = $this->find('all', $options);
		
		if(!empty($requestPaymentsOrderResults)) {
			
			$requestPaymentsOrderTot = count($requestPaymentsOrderResults);
			
			foreach($requestPaymentsOrderResults as $numResult => $requestPaymentsOrderResult) {

				self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - ".($numResult+1)."/".$requestPaymentsOrderTot." tratto Order.id ".$requestPaymentsOrderResult['Order']['id']." Order.state_code ".$requestPaymentsOrderResult['Order']['state_code'], $debug);
				
				if(!empty($state_code_next)) {
					$OrderLifeCycle->stateCodeUpdate($user, $requestPaymentsOrderResult, $state_code_next, null, $debug);					
				}
				else {
					switch ($requestPaymentsOrderResult['Order']['state_code']) {
						case 'TO-PAYMENT':
						case 'USER-PAID': // Da saldare da parte dei gasisti
							
							// USER-PAID / WAIT-REQUEST-PAYMENT-CLOSE 
							$state_code_next_single_order = $OrderLifeCycle->stateCodeAfter($user, $requestPaymentsOrderResult, 'USER-PAID', $debug);
							self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - tratto Order.id ".$requestPaymentsOrderResult['Order']['id']." Order.state_code_next_single_order ".$state_code_next_single_order, $debug);
				
							$OrderLifeCycle->stateCodeUpdate($user, $requestPaymentsOrderResult, $state_code_next_single_order, null, $debug);					
						break;
						case 'WAIT-REQUEST-PAYMENT-CLOSE':							
							$state_code_next_single_order = $OrderLifeCycle->stateCodeAfter($user, $requestPaymentsOrderResult, 'WAIT-REQUEST-PAYMENT-CLOSE', $debug);
							self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - tratto Order.id ".$requestPaymentsOrderResult['Order']['id']." Order.state_code_next_single_order ".$state_code_next_single_order, $debug);
							
							$OrderLifeCycle->stateCodeUpdate($user, $requestPaymentsOrderResult, $state_code_next_single_order, null, $debug);	
						break; 
						case 'SUPPLIER-PAID':
						break; 
						case 'CLOSE':
						break; 
						default:
							self::x("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId Order.state_code [".$requestPaymentsOrderResult['Order']['state_code']."] order_id ".$requestPaymentsOrderResult['Order']['id']);
						break;
					}
				} // end if(!empty($state_code_next)) 					
			} // end loop
		}
		
		/*
		 * ctrl se tutti gli Orders.state = WAIT-REQUEST-PAYMENT-CLOSE
		 */
		$options = [];
		$options['conditions'] = ['RequestPaymentsOrder.organization_id' => $user->organization['Organization']['id'],
							      'RequestPaymentsOrder.request_payment_id' => $request_payment_id,
								  'Order.organization_id' => $user->organization['Organization']['id'],
							      'Order.state_code' => 'WAIT-REQUEST-PAYMENT-CLOSE'];
		$options['recursive'] = 0;
		$requestPaymentsOrderCount = $this->find('count', $options);
		
		if($requestPaymentsOrderCount==$requestPaymentsOrderTot) {
			self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - tutti gli ordini della rich  ".$request_payment_id." hanno state_code WAIT-REQUEST-PAYMENT-CLOSE => calcolo stato successivo", $debug);

			foreach($requestPaymentsOrderResults as $requestPaymentsOrderResult) {

				// SUPPLIER-PAID / CLOSE 
				$state_code_next_single_order = $OrderLifeCycle->stateCodeAfter($user, $requestPaymentsOrderResult, 'WAIT-REQUEST-PAYMENT-CLOSE-ALL', $debug);
				self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - tratto Order.id ".$requestPaymentsOrderResult['Order']['id']." Order.state_code_next_single_order ".$state_code_next_single_order, $debug);
				
				$OrderLifeCycle->stateCodeUpdate($user, $requestPaymentsOrderResult, $state_code_next_single_order, null, $debug);				
			}			
		}
		else {
			if($requestPaymentsOrderCount>0)
				self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - della rich  ".$request_payment_id." ".$requestPaymentsOrderCount." ordini su ".$requestPaymentsOrderTot." hanno state_code WAIT-REQUEST-PAYMENT-CLOSE => stato invariato", $debug);
			else
				self::d("RequestPaymentsOrder::setOrdersStateCodeByRequestPaymentId() - della rich  ".$request_payment_id." nessun ordine su ".$requestPaymentsOrderTot." hanno state_code WAIT-REQUEST-PAYMENT-CLOSE => stato invariato", $debug);
		}
		// exit;					
		
		return true;
	}
	
/*
	 * per ogni ordine associato ad una richiesta di pagamento
	 * riporto ordinie a 'PROCESSED-TESORIERE' (In carico al tesoriere) => popolo summary_orders
	 * $SummaryOrderLifeCycle->changeRequestPayment($this->user, $order_id, $operation='DELETE', $opts); cancello i pagamenti gia' fatti del tesoriere SummaryOrder.saldato_a = TESORIERE
	 */
	public function setOrdersDeleteByRequestPaymentId($user, $request_payment_id, $opts=[], $debug=false) {
		
		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;
		
		$options = [];
		$options['conditions'] = ['RequestPaymentsOrder.organization_id' => $user->organization['Organization']['id'],
							      'RequestPaymentsOrder.request_payment_id' => $request_payment_id];
		$options['recursive'] = -1;
		$requestPaymentsOrderResults = $this->find('all', $options);
		if(!empty($requestPaymentsOrderResults)) {
			foreach($requestPaymentsOrderResults as $requestPaymentsOrderResult) {
				$order_id = $requestPaymentsOrderResult['RequestPaymentsOrder']['order_id'];
				
				/*
				 * riporto ordinie a 'PROCESSED-TESORIERE' (In carico al tesoriere) => popolo summary_orders
				 * $SummaryPayment->delete_order() aggiorno il totale in SummaryPayment, se il gasista aveva solo quell'ordine SummaryPayment.stato = DAPAGARE
				 * $SummaryOrderLifeCycle->changeRequestPayment($this->user, $order_id, $operation='DELETE', $opts); cancello i pagamenti gia' fatti del tesoriere SummaryOrder.saldato_a = TESORIERE
				 */		
				$OrderLifeCycle->stateCodeUpdate($user, $order_id, 'PROCESSED-TESORIERE');
			}
		} // if(!empty($requestPaymentsOrderResults))
	
		return true;
	}
	
	/*
	 * estraggo SummaryPayment users che hanno gia' saldato per quella richiesta di pagamento
	 * estraggo SummaryOrder   users legati all'ordine
	 * match tra SummaryPayment / SummaryOrder = ctrl se ci sono users dell'ordine che hanno pagato la richiesta di pagamento 
	 * 	se SI => ctrl se avevano solo quell'ordine (SummaryOrder.importo_pagato = SummaryPayment.importo_pagato)
	 *					=> si SI procedo
	 *					=> si NO li blocco
	 * estraggo SummaryOrder users che hanno gia' saldato per quell'ordine => se si msg che SummaryOrders.saldato_a = TESORIERE verranno eliminati
	 */
	public  function ctrlDeleteOrders($user, $request_payments_order_id, $debug=false) {
        
		//$debug = true;
		
		$results = [] ;
		
		$user_ids_just_saldato_summary_payments = []; // alcuni gasisti hanon gia' saldato la rich di pagamento
		$user_ids_just_saldato_summary_orders = []; // avviso che cancellero' i summary_orders gia' sladato al TESORIERE
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
		$SummaryOrder->unbindModel(['belongsTo' => ['Order', 'Delivery']]);
		
		App::import('Model', 'SummaryPayment');
		$SummaryPayment = new SummaryPayment;
		$SummaryPayment->unbindModel(['belongsTo' => ['RequestPayment']]);
		
		/*
		 * estraggo RequestPaymentsOrder da cancellare
		 */
		$options =  [];
		$options['conditions'] = ['RequestPaymentsOrder.organization_id'=>(int)$user->organization['Organization']['id'],
								  'RequestPaymentsOrder.id'=> $request_payments_order_id];
		$options['recursive'] = -1;
		$results = $this->find('first', $options);
		$order_id = $results['RequestPaymentsOrder']['order_id'];
		
		self::d($results, $debug);

		/*
		 * estraggo SummaryPayment users che hanno gia' saldato per quella richiesta di pagamento
		 */	
		$options =  [];
		$options['conditions'] = ['SummaryPayment.organization_id'=>(int)$user->organization['Organization']['id'],
								  'SummaryPayment.request_payment_id' => $results['RequestPaymentsOrder']['request_payment_id'],
								  'SummaryPayment.stato' => 'PAGATO'];			   		   
		$options['recursive'] = 0;
		$summaryPaymentResults = $SummaryPayment->find('all', $options);
		self::d($summaryPaymentResults, $debug);
						
		/*
		 * estraggo SummaryOrder users legati all'ordine
		 */			
		$options =  [];
		$options['conditions'] = ['SummaryOrder.organization_id'=>(int)$user->organization['Organization']['id'],
								  'SummaryOrder.order_id' => $order_id];
		$options['order'] = Configure::read('orderUser');			   
        $options['recursive'] = 0;
        $summaryOrderResults = $SummaryOrder->find('all', $options);	
		self::d($summaryOrderResults, $debug);
	
		/*
		 * ctrl se ci sono users dell'ordine che hanno pagato la richiesta di pagamento => se SI li blocco 
		 */
		foreach($summaryPaymentResults as $summaryPaymentResult) {
			foreach($summaryOrderResults as $summaryOrderResult) {
							
				if($summaryPaymentResult['SummaryPayment']['user_id'] == $summaryOrderResult['SummaryOrder']['user_id']) {  
					
					self::d($summaryPaymentResult['SummaryPayment']['user_id'].' - '.$summaryOrderResult['SummaryOrder']['user_id'].' - SummaryPayment.importo_pagato '.$summaryPaymentResult['SummaryPayment']['importo_pagato'].' - SummaryOrder.importo_pagato '.$summaryOrderResult['SummaryOrder']['importo_pagato'], $debug);

					if($summaryOrderResult['SummaryOrder']['importo_pagato'] != $summaryPaymentResult['SummaryPayment']['importo_pagato']) { // ha + ordine saldati
						array_push($user_ids_just_saldato_summary_payments, $summaryPaymentResult);
					
						self::d($user_ids_just_saldato_summary_payments, $debug);
					}
				}
			}
		}
		self::d($user_ids_just_saldato_summary_payments, $debug);
			
		if(empty($user_ids_just_saldato_summary_payments)) {
	
			/*
			 * estraggo SummaryOrder users che hanno gia' saldato per quell'ordine => se si msg che SummaryOrders.saldato_a = TESORIERE verranno eliminati
			 */			
			$options =  [];
			$options['conditions'] = ['SummaryOrder.organization_id'=>(int)$user->organization['Organization']['id'],
									  'SummaryOrder.order_id' => $order_id,
									  'SummaryOrder.saldato_a' => 'TESORIERE'
									 ];
			$options['order'] = Configure::read('orderUser');
			$options['recursive'] = 0;
			$user_ids_just_saldato_summary_orders = $SummaryOrder->find('all', $options);	

			self::d($user_ids_just_saldato_summary_orders, $debug);				
			
		} // end if(!empty($user_ids_just_saldato_summary_payments)

		$results['user_ids_just_saldato_summary_payments'] = $user_ids_just_saldato_summary_payments;
		$results['user_ids_just_saldato_summary_orders'] = $user_ids_just_saldato_summary_orders;
		
		return $results;
	}
	
	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'delivery_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'order_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'request_payment_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $belongsTo = array(
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => 'Order.organization_id = RequestPaymentsOrder.organization_id',
			'fields' => '',
			'order' => ''
		),
		'RequestPayment' => array(
			'className' => 'RequestPayment',
			'foreignKey' => 'request_payment_id',
			'conditions' => 'RequestPayment.organization_id = RequestPaymentsOrder.organization_id',
			'fields' => '',
			'order' => ''
		)
	);
}