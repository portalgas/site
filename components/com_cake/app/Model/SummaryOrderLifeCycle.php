<?php
App::uses('AppModel', 'Model');

class SummaryOrderLifeCycle extends AppModel {

	public $useTable = 'summary_orders';
	public $name = 'SummaryOrder'; 
	public $alias = 'SummaryOrder'; 

	/*
	 * saldato_a ENUM('CASSIERE','TESORIERE')
	 */
	public function callbackToOrder($user, $orderResult, $options=[], $debug=false) {
	
		$esito = [];

		if(empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
				
		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);
				
		if(isset($options['saldato_a']))
			$saldato_a = $options['saldato_a'];
		else 
			$saldato_a = $this->_getSaldatoA($orderResult);
		
		self::l("SummaryOrderLifeCycle::callbackToOrder order_id ".$orderResult['Order']['id']." state_code ".$orderResult['Order']['state_code']." saldato_a ".$saldato_a, $debug);
	
		switch($orderResult['Order']['state_code']) {
			case 'CREATE-INCOMPLETE':
			case 'OPEN-NEXT':
			case 'OPEN':
			case 'RI-OPEN-VALIDATE':
			case 'PROCESSED-BEFORE-DELIVERY':
			break;
			case 'PROCESSED-POST-DELIVERY':
			case 'INCOMING-ORDER':  // merce arrivata
				$SummaryOrder->delete_to_order($user, $orderResult['Order']['id']);	// qui il cassiere lo puo' mandare indietro			
			break;
			case 'PROCESSED-ON-DELIVERY':  // in carico al Cassiere
				$SummaryOrder->populate_to_order($user, $orderResult['Order']['id'], 0);				
			break;
			/*
			 * tesoriere
			 */				
			case 'WAIT-PROCESSED-TESORIERE':
				$SummaryOrder->delete_to_order($user, $orderResult['Order']['id']);	// qui il tesoriere lo puo' mandare indietro				
			break;
			case 'PROCESSED-TESORIERE':	// In carico al tesoriere			
				/*
				 * se l'ordine arriva in questo stato perche' cancellato da richiesta di pagamento 
				 * cancello i pagamenti del TESORIERE (SummaryOrder.saldato_a = TESORIERE) gia' fatti $SummaryOrderLifeCycle->changeRequestPayment('DELETE');
				 */		
				$opts['saldato_a'] = 'TESORIERE';
				$this->changeRequestPayment($user, $orderResult['Order']['id'], $operation='DELETE', $opts);
			
				 $SummaryOrder->populate_to_order($user, $orderResult['Order']['id'], 0);
			break;				
			case 'TO-REQUEST-PAYMENT':	

			break;
			case 'TO-PAYMENT': // Associato ad una richiesta di pagamento
				$SummaryOrder->populate_to_order($user, $orderResult['Order']['id'], 0);			
			break;
			case 'USER-PAID':					
				// da TO-PAYMENT a USER-PAID per in pagamenti POST
				// da PROCESSED-ON-DELIVERY a USER-PAID  per in pagamenti ON
			break;
			case 'SUPPLIER-PAID':
			break;
			case 'WAIT-REQUEST-PAYMENT-CLOSE':
			break;
			case 'CLOSE':
				/*
				 * 	popolo SummaryOrder
				 *  setto SummaryOrder.importo_pagato = SummaryOrder.importo
				 *	calcolo Order.tot_importo 
				 *	setto Order.tesoriere_stato_pay
				 */		 
				$SummaryOrder->populate_to_order($user, $orderResult['Order']['id'], 0);	

				$sql = "UPDATE `".Configure::read('DB.prefix')."summary_orders` SET 
						importo_pagato = importo,
						saldato_a = '".$saldato_a."'
						modified = '".date('Y-m-d H:i:s')."'
					WHERE
						importo_pagato != importo 
						and organization_id = ".(int)$user->organization['Organization']['id']."
						and order_id = ".$orderResult['Order']['id'];
				self::d($sql, $debug);
				$updateResults = $this->query($sql);
			break;
			default:
				self::x("SummaryOrderLifeCycle::callbackToOrder Order.state_code non previsto [".$orderResult['Order']['state_code']."]");
			break;			
		}
			
		return $esito; 
	}

	/*
	 * richiamato a cambiamento di una requestPayment
	 * 	DELETE quando cancello associazione ordine con richiesta di pagamento
	 */
	public function changeRequestPayment($user, $orderResult, $operation='', $opts=[], $debug=false) {
	
		$esito = [];

		if(empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		App::import('Model', 'Order');
		$Order = new Order;
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
			
		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);
	
		self::l("SummaryOrderLifeCycle::changeRequestPayment order_id ".$orderResult['Order']['id']." operation [".$operation."]", $debug);
		
		switch($operation) {
			/* 
			 * cancello SummaryOrder gia' pagati SummaryOrder.saldato_a CASSIERE o TESORIERE 
			 * utilizzato in RequestPayments::admin_delete()  RequestPayments::admin_delete() 
			 */			
			case 'DELETE':
		
				if(isset($opts['saldato_a']))
					$saldato_a = $opts['saldato_a'];
				else 
					$saldato_a = 'TESORIERE';
			
				switch($user->organization['Template']['payToDelivery']) {
					case "ON":
					
					break;
					case "ON-POST":
					case "POST":
						$options = [];
						$options['conditions'] = ['SummaryOrder.organization_id' => $user->organization['Organization']['id'],
												  'SummaryOrder.order_id' => $orderResult['Order']['id'],
												  'SummaryOrder.saldato_a' => $saldato_a];
						self::l($options, $debug);						  
						$summaryOrderResults = $this->deleteAll($options['conditions'], false);
						if(!$summaryOrderResults)  {
							CakeLog::write('error',"Errore: non cancellati i SummaryOrder.order_id ".$orderResult['Order']['id']." SummaryOrder.saldato_a $saldato_a");	
						}
						
					break;
					default:
						self::x("SummaryOrderLifeCycle::changeRequestPayment payToDelivery non previsto [".$user->organization['Template']['payToDelivery']."]");
					break;
				}
			break;
			default:
				self::x("SummaryOrderLifeCycle::changeRequestPayment operation non previsto [".$operation."]");
			break;
		}
			
		return $esito; 
	}
	
	/* 
	 * call Doc::admin_cassiere_delivery_docs_export()
	 * aggiorno il SummaryOrder di un gasista, se tutti i gasisti dell'ordine hanno saldato => Order.state_code successivo
	 */
	public function saveToUser($user, $data, $debug) {
		
		$order_id = $data['SummaryOrder']['order_id'];
		$user_id = $data['SummaryOrder']['user_id'];
		if(empty($order_id) || empty($user_id))
			return false;
	
        App::import('Model', 'OrderLifeCycle');
        $OrderLifeCycle = new OrderLifeCycle;
		
		self::l("SummaryOrderLifeCycle::saveToUser order_id ".$order_id." user_id ".$user_id. " ".print_r($data, true), $debug);
	
		$msg_errors = $this->getMessageErrorsToValidate($this, $data);
		if(!empty($msg_errors)) {
			self::l("SummaryOrderLifeCycle::saveToUser order_id ".$order_id." user_id ".$user_id." ERROR validazione", $debug);				
			self::l($msg_errors, $debug);				

			return false;
		}
			
		$this->create();
		if(!$this->save($data)) 
			return false;

		/*
		 * se tutti i gasisti hanno sladato aggiorno stato dlel'ordine
		 */
		$state_code_next = $OrderLifeCycle->stateCodeAfter($user, $order_id, 'PROCESSED-ON-DELIVERY', $debug);
		
		$OrderLifeCycle->stateCodeUpdate($user, $order_id, $state_code_next, [], $debug);	
		
		return true;
	} 
	
	/*
	 * ctrl che tutti i gassiti abbiano saldato a cassiere / tesoriere => Order.stato successivo in base al template
	 */
	public function isSummaryOrderAllSaldato($user, $orderResult, $debug=false) {
	
		$debug=false; // function richiamata da Cron che ha il debug a true
	
		$esito = [];

		if(empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	
				
		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);
				
		self::l("SummaryOrderLifeCycle::isSummaryOrderAllSaldato order_id ".$orderResult['Order']['id'], $debug);

		/*
		 * prima ctrl che sia pololata
		 */
        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;
		 
		$summaryOrderResults = $SummaryOrder->select_to_order($user, $orderResult['Order']['id']);
		if(empty($summaryOrderResults)) {
			self::l("SummaryOrderLifeCycle::isSummaryOrderAllSaldato order_id ".$orderResult['Order']['id']." SummaryOrder non popolato!", $debug);			
			
			return false;
		}
		
        $this->unbindModel(['belongsTo' => ['User', 'Delivery']]);

        $options = [];
        $options['conditions'] = ['SummaryOrder.organization_id' => $user->organization['Organization']['id'],
								'Order.organization_id' => $user->organization['Organization']['id'],
								'SummaryOrder.order_id' => $orderResult['Order']['id'],
								'Order.id' => $orderResult['Order']['id'],
								'SummaryOrder.saldato_a' => null];
        $summaryOrderResults = $this->find('all', $options);
		// self::l($options, $debug);
		// self::l($summaryOrderResults, $debug);
        // self::l(["SummaryOrderLifeCycle::isSummaryOrderAllSaldato order_id ".$orderResult['Order']['id'], $options], $debug);
		if(empty($summaryOrderResults)) {
			self::l("SummaryOrderLifeCycle::isSummaryOrderAllSaldato order_id ".$orderResult['Order']['id']." tutti hanno saldato", $debug);			
			
			return true;
		}
		else {
			self::l("SummaryOrderLifeCycle::isSummaryOrderAllSaldato order_id ".$orderResult['Order']['id']." NON tutti hanno saldato", $debug);				
			
			return false;
		}
	}
		
	/* 
	 * ctrl se puo' aggiungere ad un ordine le eventuali 
	 *  SummaryOrder 
	 *  SummaryOrderTrapsort spese di trasporto
	 *  SummaryOrderMore spese generiche
	 *  SummaryOrderLess sconti
	 */		
	public function canAddSummaryOrder($user, $order_state_code) {
		
		if($order_state_code == 'PROCESSED-POST-DELIVERY' ||  //  In carico al referente dopo la consegna
			$order_state_code == 'PROCESSED-ON-DELIVERY' ||  //  in carico al cassiere
			$order_state_code == 'INCOMING-ORDER' ||  // In carico al referente con la merce arrivata
			$order_state_code == 'WAIT-PROCESSED-TESORIERE' || 
			$order_state_code == 'PROCESSED-TESORIERE' || 
			$order_state_code == 'TO-PAYMENT' || 
			$order_state_code == 'TO-REQUEST-PAYMENT' || 
			$order_state_code == 'USER-PAID' || 
			$order_state_code == 'SUPPLIER-PAID' || 
			$order_state_code == 'WAIT-REQUEST-PAYMENT-CLOSE' || 
			$order_state_code == 'CLOSE') 
			return true;
		else
			return false;
	}
	
	private function _getSaldatoA($orderResult) {
		
		$saldato_a = null;
		
		switch($orderResult['Order']['state_code']) {
			case 'CREATE-INCOMPLETE':
			case 'OPEN-NEXT':
			case 'OPEN':
			case 'RI-OPEN-VALIDATE':
			case 'PROCESSED-BEFORE-DELIVERY':
			case 'PROCESSED-POST-DELIVERY':
			case 'INCOMING-ORDER':  // merce arrivata
			break;
			case 'PROCESSED-ON-DELIVERY':  // in carico al Cassiere
				$saldato_a = 'CASSIERE';	
			break;
			case 'WAIT-PROCESSED-TESORIERE':
			case 'PROCESSED-TESORIERE':	
			case 'TO-REQUEST-PAYMENT':
			case 'TO-PAYMENT':
				$saldato_a = 'TESORIERE';
			break;
			case 'USER-PAID':					
			case 'SUPPLIER-PAID':
			case 'WAIT-REQUEST-PAYMENT-CLOSE':
			case 'CLOSE':
			break;
			default:
				self::x("SummaryOrderLifeCycle::_getSaldatoA Order.state_code non previsto [".$orderResult['Order']['state_code']."]");
			break;			
		}
			
		return $saldato_a; 
	}
	
	public $validate = array(
		'user_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'delivery_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'order_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
	);
	
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = SummaryOrder.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = SummaryOrder.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => 'Order.organization_id = SummaryOrder.organization_id',
			'fields' => '',
			'order' => ''
		)
	);
	
	/*
	 * il save lo faccio in populate_to_order() ed e' gia' corretto
	public function beforeSave($options = []) {
		if(!empty($this->data['SummaryOrder']['importo'])) {
			$this->data['SummaryOrder']['importo'] =  $this->importoToDatabase($this->data['SummaryOrder']['importo']);
		}
		return true;
	}*/
		
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if(isset($val['SummaryOrder']['importo'])) {
					$results[$key]['SummaryOrder']['importo_'] = number_format($val['SummaryOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryOrder']['importo_e'] = $results[$key]['SummaryOrder']['importo_'].' &euro;';
				}
				if(isset($val['SummaryOrder']['importo_pagato'])) {
					$results[$key]['SummaryOrder']['importo_pagato_'] = number_format($val['SummaryOrder']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryOrder']['importo_pagato_e'] = $results[$key]['SummaryOrder']['importo_pagato_'].' &euro;';
				}
			}
		}
		return $results;
	}
}