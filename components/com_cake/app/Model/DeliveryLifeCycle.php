<?php
App::uses('AppModel', 'Model');

class DeliveryLifeCycle extends AppModel {

	public $useTable = 'deliveries';
	public $name = 'Delivery'; 
	public $alias = 'Delivery'; 
		
	public $hasMany = [
			'Order' => [
					'className' => 'Order',
					'foreignKey' => 'delivery_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			]
	];

	/*
	 *  elimino le consegne 
	 *		- scadute DATE(Delivery.data) < CURDATE()
	 *		- senza ordini associati
	 */	
	public function deleteExpiredWithoutAssociations($user, $delivery_id=0, $debug=false) {
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => $user->organization['Organization']['id'],
								 'Delivery.sys' => 'N',
								 'Delivery.isVisibleFrontEnd' => 'Y',
								 'Delivery.isVisibleFrontEnd' => 'Y',
								 'DATE(Delivery.data) < CURDATE()'];
		if (!empty($delivery_id))
			$options['conditions'] += ['Delivery.id' => $delivery_id];
		if ($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y')
			$options['conditions'] +=  ['OR' => ['Delivery.isToStoreroom' => 'Y',
													'Delivery.isToStoreroomPay' => 'Y'],
													['Delivery.isToStoreroom' => 'N']
										  ];
		$options['fields'] = ['Delivery.id'];
		$options['recursive'] = -1;
		//self::d($options['conditions'], $debug);
		$deliveryResults = $this->find('all', $options);
		self::d("Estratte ".count($deliveryResults)." consegne SCADUTE ed eventualmente pagate alla dispensa => controllo se SENZA Ordini (passate in statistiche)", $debug);
	
		foreach ($deliveryResults as $deliveryResult) {
			
			$options = [];
			$options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'],
									  'Order.delivery_id' => $deliveryResult['Delivery']['id']];
			$options['recursive'] = -1;
			$orderResults = $Order->find('count', $options);

			if($orderResults==0) { 
				$this->id = $deliveryResult['Delivery']['id'];
				self::d("CANCELLO la consegna ".$deliveryResult['Delivery']['id']." con ".$orderResults." ordini ed eventualmente pagate alla dispensa", $debug);			
				$this->delete();
			}
			else 
				self::d("NON CANCELLO la consegna ".$deliveryResult['Delivery']['id']." con ".$orderResults." ordini o eventualmente da pagate alla dispensa", $debug);	
		} // end loops Delivery
    }				
    
    public function deliveriesToClose($user, $delivery_id=0, $debug) {

        self::d(date("d/m/Y") . " - " . date("H:i:s") . " Porto le consegne a Delivery.stato_elaborazione = CLOSE con tutti gli ordini in stato_elaborazione = CLOSE", $debug);
		if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST')
			self::d("e RequestPayment.stato_elaborazione = CLOSE", $debug);
		if($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y')
			self::d("e isToStoreroomPay = Y", $debug);

        try {			
            /*
             * estraggo tutte le consegne aperte e quanti ordini ha associati
             */
            $sql = "SELECT
						Delivery.id, count(`Order`.id) as tot_order
				   FROM
						 " . Configure::read('DB.prefix') . "deliveries Delivery, 
						 " . Configure::read('DB.prefix') . "orders `Order` 
				   WHERE
						Delivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND `Order`.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND Delivery.stato_elaborazione = 'OPEN' 
						AND Delivery.sys = 'N' 
						AND `Order`.delivery_id = Delivery.id 
						and `Order`.isVisibleFrontEnd = 'Y'  and Delivery.isVisibleFrontEnd = 'Y' ";
            if ($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y')
                $sql .= " AND (Delivery.isToStoreroom = 'Y' && Delivery.isToStoreroomPay = 'Y' || Delivery.isToStoreroom = 'N') ";
            if (!empty($delivery_id))
                $sql .= " AND Delivery.id = " . (int) $delivery_id;
            $sql .= " GROUP BY Delivery.id 
					  ORDER BY Delivery.id ";
            self::d($sql, false);
            $results = $this->query($sql);
            self::d("Estratte " . count($results) . " consegne OPEN", $debug);

            /*
             * ciclo tutte le consegne e ctrl che abbiamo tutti gli ordini 
             *		- con state_code = CLOSE
             * 		- RequestPayment.stato_elaborazione = CLOSE 
             */
            foreach ($results as $result) {

                $sql = "SELECT
						count(`Order`.id) as tot_order_close 
				   FROM
						 " . Configure::read('DB.prefix') . "deliveries Delivery,
						 " . Configure::read('DB.prefix') . "orders `Order`
				   WHERE
						Delivery.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND `Order`.organization_id = " . (int) $user->organization['Organization']['id'] . "
						AND Delivery.id = " . $result['Delivery']['id'] . "
						AND `Order`.delivery_id = Delivery.id
						AND `Order`.isVisibleFrontEnd = 'Y'  and Delivery.isVisibleFrontEnd = 'Y' 
						AND `Order`.state_code = 'CLOSE' ";
                self::d($sql, false);
                $ordersResults = current($this->query($sql));

                self::d("Per la consegna " . $result['Delivery']['id'] . " estratti " . $ordersResults[0]['tot_order_close'] . " ordini CLOSE su un totale " . $result[0]['tot_order'], $debug);				
				if ($ordersResults[0]['tot_order_close'] == $result[0]['tot_order']) 
					self::d("=> potrei chiudere la consegna", $debug);
				else
					self::d(" => non potrei chiudere la consegna", $debug);

				/*
				 * ctrl che le richieste di pagamento siano CLOSE
				 */
				if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
					$sql = "SELECT RequestPayment.id, RequestPayment.num, RequestPayment.stato_elaborazione FROM 
								" . Configure::read('DB.prefix') . "request_payments_orders as RequestPaymentsOrder, 
								" . Configure::read('DB.prefix') . "request_payments RequestPayment  
							WHERE
							RequestPaymentsOrder.organization_id = " . (int) $user->organization['Organization']['id'] . " 
							AND RequestPaymentsOrder.organization_id = RequestPayment.organization_id
							AND RequestPaymentsOrder.request_payment_id = RequestPayment.id
							AND RequestPayment.stato_elaborazione != 'CLOSE'
							AND RequestPaymentsOrder.delivery_id = " . $result['Delivery']['id'];	
			                self::d($sql, false);
			                $requestPaymentResults = $this->query($sql);							
							if(empty($requestPaymentClose) || count($requestPaymentResults)==0) {
			                	$requestPaymentClose=true;
								self::d("Nessun ordine e' legata ad una RICHIESTA DI PAGAMENTO chiusa => potrei chiudere la consegna", $debug);
			                }
							else {
 			                	$requestPaymentClose=false;
								self::d("Alcuni ordini sono legati ad una RICHIESTA DI PAGAMENTO non chiusa => non potrei chiudere la consegna", $debug);
							}			
				}
				else
					$requestPaymentClose=true;
				
				
                /*
                 * per una consegna
                 * 	il totale degli ordini e' = al totale degli ordini chiudi 
                 */
                if ($ordersResults[0]['tot_order_close'] == $result[0]['tot_order'] && $requestPaymentClose) {
                    $sql = "UPDATE `" . Configure::read('DB.prefix') . "deliveries`
						   SET
								stato_elaborazione = 'CLOSE',
								modified = '" . date('Y-m-d H:i:s') . "'
						   WHERE
						   		organization_id = " . (int) $user->organization['Organization']['id'] . "
						   		and id = " . $result['Delivery']['id'];
                    self::d($sql, $debug);
                    $this->query($sql);

                    self::d("	per la consegna " . $result['Delivery']['id'] . " aggiorno lo stato a CLOSE ", $debug);
                }
                else
                self::d("	per la consegna " . $result['Delivery']['id'] . " NON aggiorno lo stato a CLOSE", $debug);
            } // end foreach
	
 
        } catch (Exception $e) {
            self::d('DeliveryLifeCycle::deliveriesToClose()<br />' . $e, $debug);
        }
    }
    
   public function deliveriesToOpen($user, $delivery_id=0, $debug=false) {

        self::d(date("d/m/Y") . " - " . date("H:i:s") . " Porto le consegne a Delivery.stato_elaborazione = OPEN se almeno un ordine non e' in stato_elaborazione = CLOSE", $debug);
		if($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y')
			self::d("e isToStoreroomPay = N", $debug);
			
		App::import('Model', 'Order');
		$Order = new Order;
			
        /*
         * estraggo tutte le consegne CLOSE
         */
         $options = [];
         $options['conditions'] = ['Delivery.organization_id' => $user->organization['Organization']['id'],
					               'Delivery.stato_elaborazione' => 'CLOSE', 
					               'Delivery.sys' => 'N', 
					               'Delivery.isVisibleFrontEnd' => 'Y'];
        if ($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y')
        	$options['conditions'] += ['OR' => ['Delivery.isToStoreroom' => 'N',
        									   ['Delivery.isToStoreroomPay' => 'N', 'Delivery.isToStoreroom' => 'Y']]];
        if (!empty($delivery_id))
        	$options['conditions'] += ['Delivery.id' => $delivery_id];
        $options['recursive'] = -1;
        $deliveryResults = $this->find('all', $options);
        self::d($deliveryResults, $debug);
        
        foreach($deliveryResults as $deliveryResult) {

	        /*
	         * estraggo ordini non CLOSE
	         */
	         $options = [];
	         $options['conditions'] = ['Order.organization_id' => $deliveryResult['Delivery']['organization_id'],
						               'Order.delivery_id' => $deliveryResult['Delivery']['id'],
						               'Order.state_code !=' => 'CLOSE'];
	        $options['recursive'] = -1;
	        $orderCount = $Order->find('count', $options);
			self::d($orderCount, $debug);
        	if($orderCount>0) {
        		self::d(date("d/m/Y") . " - " . date("H:i:s") . " riapro la consegne a Delivery.stato_elaborazione = OPEN perche' trovati $orderCount ordini Order.state_code != CLOSE ", $debug);
        		
        		$deliveryResult['Delivery']['stato_elaborazione'] = 'OPEN';
        		
        		self::d($deliveryResult, $debug);
        		
				/*
				 * richiamo la validazione 
				 */
				$msg_errors = $this->getMessageErrorsToValidate($this, $deliveryResult);
				if(!empty($msg_errors)) {
					self::d($deliveryResult, $debug);
					self::d($msg_errors, $debug);
				}
				else {
					$this->create();
					if(!$this->save($deliveryResult)) {
						self::l($deliveryResult, $debug);
						self::l($msg_errors, $debug);
						
						return false;
					}
				}    		
        	}
        }            

		return true;
   }    				
}