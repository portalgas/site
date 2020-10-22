<?php
App::uses('AppModel', 'Model');

class StatoElaborazione extends AppModel {

	public $useTable = false;

	public function orders($user, $debug=false, $order_id) {

        try {
            App::import('Model', 'OrderLifeCycle');
			$OrderLifeCycle = new OrderLifeCycle;

	        App::import('Model', 'SummaryOrderLifeCycle');
	        $SummaryOrderLifeCycle = new SummaryOrderLifeCycle;

            App::import('Model', 'Order');
            $Order = new Order;
		
			$p = Configure::read('DB.prefix');
			if (!empty($order_id))
                $filter_order_id = " AND `Order`.id=".$order_id;
			else 
				$filter_order_id = "";
		
			/*
			 * `Order`.data_inizio > CURDATE() "; // data_inizio successiva ad oggi
			 * `Order`.data_inizio <= CURDATE()"; // data_inizio precedente o uguale ad oggi 
			 */
			$sqls['from_all_to_CREATE-INCOMPLETE']['descri'] = "Porto gli ordini senza articoli associati (ArticlesOrder) in CREATE-INCOMPLETE";
			$sqls['from_all_to_CREATE-INCOMPLETE']['sql'] = "SELECT `Order`.* FROM ".$p."orders `Order` LEFT JOIN ".$p."articles_orders ArticlesOrder ON(ArticlesOrder.order_id=`Order`.id and `ArticlesOrder`.organization_id=`Order`.organization_id) WHERE `Order`.organization_id=%s AND `Order`.state_code NOT IN ('CREATE-INCOMPLETE','CLOSE') AND ArticlesOrder.article_id IS NULL AND ArticlesOrder.order_id IS NULL %s GROUP BY `Order`.id";
            $sqls['from_all_to_CREATE-INCOMPLETE']['state_code_next'] = 'CREATE-INCOMPLETE';
			
			$sqls['from_CREATE-INCOMPLETE_to_OPEN-NEXT_o_OPEN']['descri'] = "Porto gli ordini con articoli associati (ArticlesOrder) da CREATE-INCOMPLETE a OPEN-NEXT o OPEN (ArtciclesOrders::add)";
			$sqls['from_CREATE-INCOMPLETE_to_OPEN-NEXT_o_OPEN']['sql'] = "SELECT `Order`.* FROM ".$p."orders `Order`,".$p."articles_orders ArticlesOrder WHERE `Order`.organization_id=%s AND ArticlesOrder.organization_id=`Order`.organization_id AND ArticlesOrder.order_id=`Order`.id AND `Order`.state_code='CREATE-INCOMPLETE' %s GROUP BY `Order`.id";
            $sqls['from_CREATE-INCOMPLETE_to_OPEN-NEXT_o_OPEN']['state_code_next'] = '';

			$sqls['from_all_to_OPEN-NEXT']['descri'] = "Porto gli ordini a OPEN-NEXT per quelli che devono ancora aprirsi";
			$sqls['from_all_to_OPEN-NEXT']['sql'] = "SELECT `Order`.* FROM ".$p."orders as `Order` WHERE `Order`.organization_id=%s AND `Order`.state_code NOT IN ('CREATE-INCOMPLETE','OPEN-NEXT','CLOSE') AND `Order`.data_inizio > CURDATE() %s";
            $sqls['from_all_to_OPEN-NEXT']['state_code_next'] = 'OPEN-NEXT';			

			$sqls['from_OPEN-NEXT_to_OPEN']['descri'] = "Porto gli ordini da OPEN-NEXT a OPEN: estraggo gli ordini che si aprono oggi (o dovrebbero essere gia' aperti!)";
			$sqls['from_OPEN-NEXT_to_OPEN']['sql'] = "SELECT `Order`.* FROM ".$p."orders as `Order` WHERE `Order`.organization_id=%s AND `Order`.state_code='OPEN-NEXT' AND `Order`.data_inizio <= CURDATE() %s";
            $sqls['from_OPEN-NEXT_to_OPEN']['state_code_next'] = 'OPEN';

			$sqls['from_OPEN_to_PROCESSED-BEFORE-DELIVERY']['descri'] = "Porto gli ordini da OPEN a PROCESSED-BEFORE-DELIVERY: estraggo gli ordini chiusi con le consegne ancora aperte";
			$sqls['from_OPEN_to_PROCESSED-BEFORE-DELIVERY']['sql'] = "SELECT `Order`.* FROM ".$p."deliveries Delivery,".$p."orders `Order` WHERE Delivery.organization_id=%s and `Order`.organization_id=Delivery.organization_id and Delivery.stato_elaborazione = 'OPEN' and `Order`.delivery_id = Delivery.id and `Order`.state_code='OPEN' and DATE(Delivery.data) >= CURDATE() and `Order`.data_fine < CURDATE() %s";
            $sqls['from_OPEN_to_PROCESSED-BEFORE-DELIVERY']['state_code_next'] = 'PROCESSED-BEFORE-DELIVERY';
			
			/* 
			 * non dovrebbe mai capitare
             */			 
			$sqls['from_OPEN_to_PROCESSED-BEFORE-DELIVERY_DELIVERY_CLOSE']['descri'] = "Porto gli ordini da OPEN a PROCESSED-BEFORE-DELIVERY: estraggo gli ordini chiusi con le consegne chiuse (non dovrebbe mai capitare!)";
			$sqls['from_OPEN_to_PROCESSED-BEFORE-DELIVERY_DELIVERY_CLOSE']['sql'] = "SELECT `Order`.* FROM ".$p."deliveries Delivery,".$p."orders `Order` WHERE Delivery.organization_id=%s and `Order`.organization_id=Delivery.organization_id and Delivery.stato_elaborazione = 'OPEN' and `Order`.delivery_id = Delivery.id and `Order`.state_code='OPEN' and DATE(Delivery.data) < CURDATE() and `Order`.data_fine < CURDATE() %s";
            $sqls['from_OPEN_to_PROCESSED-BEFORE-DELIVERY_DELIVERY_CLOSE']['state_code_next'] = 'PROCESSED-BEFORE-DELIVERY';
			
			$sqls['return_RI-OPEN-VALIDATE']['descri'] = "Porto gli ordini a RI-OPEN-VALIDATE se la data di riapertura è futura";
			$sqls['return_RI-OPEN-VALIDATE']['sql'] = "SELECT `Order`.* FROM ".$p."deliveries Delivery,".$p."orders `Order` WHERE Delivery.organization_id=%s and `Order`.organization_id=Delivery.organization_id and Delivery.stato_elaborazione = 'OPEN' and `Order`.delivery_id = Delivery.id and `Order`.state_code != 'RI-OPEN-VALIDATE' and DATE(Delivery.data) >= CURDATE() and `Order`.data_fine_validation >= CURDATE() %s";
            $sqls['return_RI-OPEN-VALIDATE']['state_code_next'] = 'RI-OPEN-VALIDATE';

			$sqls['from_RI-OPEN-VALIDATE_to_PROCESSED-BEFORE-DELIVERY']['descri'] = "Porto gli ordini da RI-OPEN-VALIDATE a PROCESSED-BEFORE-DELIVERY: estraggo gli ordini chiusi con le consegne ancora aperte";
			$sqls['from_RI-OPEN-VALIDATE_to_PROCESSED-BEFORE-DELIVERY']['sql'] = "SELECT `Order`.* FROM ".$p."deliveries Delivery,".$p."orders `Order` WHERE Delivery.organization_id=%s and `Order`.organization_id=Delivery.organization_id and Delivery.stato_elaborazione = 'OPEN' and `Order`.delivery_id = Delivery.id and `Order`.state_code = 'RI-OPEN-VALIDATE' and DATE(Delivery.data) >= CURDATE() and `Order`.data_fine_validation < CURDATE() %s";
            $sqls['from_RI-OPEN-VALIDATE_to_PROCESSED-BEFORE-DELIVERY']['state_code_next'] = 'PROCESSED-BEFORE-DELIVERY';

			$sqls['from_PROCESSED-BEFORE-DELIVERY_to_OPEN']['descri'] = "Porto gli ordini da PROCESSED-BEFORE-DELIVERY a OPEN: estraggo gli ordini in carico al referente prima delle consegne che devono riaprirsi";
			$sqls['from_PROCESSED-BEFORE-DELIVERY_to_OPEN']['sql'] = "SELECT `Order`.* FROM ".$p."deliveries Delivery,".$p."orders `Order` WHERE Delivery.organization_id=%s and `Order`.organization_id=Delivery.organization_id and Delivery.stato_elaborazione = 'OPEN' and `Order`.delivery_id = Delivery.id and `Order`.state_code = 'PROCESSED-BEFORE-DELIVERY' and DATE(Delivery.data) >= CURDATE() and `Order`.data_fine >= CURDATE() %s";
            $sqls['from_PROCESSED-BEFORE-DELIVERY_to_OPEN']['state_code_next'] = 'OPEN';
			
            if ($user->organization['Template']['payToDelivery'] == 'POST') {
				 /*  
				  * solo per Organization.payToDelivery=='POST'
				  *  	orders da PROCESSED-BEFORE-DELIVERY a PROCESSED-POST-DELIVERY
				  *  
				  *  per ON o ON-POST e' un azione del referente 
				  *  	orders da PROCESSED-BEFORE-DELIVERY => INCOMING-ORDER (merce arrivata) => PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna)
				 */
				$sqls['from_PROCESSED-BEFORE-DELIVERY_to_PROCESSED-POST-DELIVERY']['descri'] = "Porto gli ordini da PROCESSED-BEFORE-DELIVERY a PROCESSED-POST-DELIVERY: estraggo gli ordini con le consegne chiuse";
				$sqls['from_PROCESSED-BEFORE-DELIVERY_to_PROCESSED-POST-DELIVERY']['sql'] = "SELECT `Order`.* FROM ".$p."deliveries Delivery,".$p."orders `Order` WHERE Delivery.organization_id=%s and `Order`.organization_id=Delivery.organization_id and Delivery.stato_elaborazione = 'OPEN' and `Order`.delivery_id = Delivery.id and `Order`.state_code = 'PROCESSED-BEFORE-DELIVERY' and DATE(Delivery.data) <= CURDATE() and `Order`.data_fine < CURDATE() %s";
				$sqls['from_PROCESSED-BEFORE-DELIVERY_to_PROCESSED-POST-DELIVERY']['state_code_next'] = 'PROCESSED-POST-DELIVERY';
				
				$sqls['from_PROCESSED-POST-DELIVERY_to_PROCESSED-BEFORE-DELIVERY']['descri'] = "Porto gli ordini da PROCESSED-POST-DELIVERY a PROCESSED-BEFORE-DELIVERY: la consegna da Chiusa e' stata riaperta (OPEN)";
				$sqls['from_PROCESSED-POST-DELIVERY_to_PROCESSED-BEFORE-DELIVERY']['sql'] = "SELECT `Order`.* FROM ".$p."deliveries Delivery,".$p."orders `Order` WHERE Delivery.organization_id=%s and `Order`.organization_id=Delivery.organization_id and Delivery.stato_elaborazione = 'OPEN' and `Order`.delivery_id = Delivery.id and `Order`.state_code = 'PROCESSED-POST-DELIVERY' and DATE(Delivery.data) > CURDATE() %s";
				$sqls['from_PROCESSED-POST-DELIVERY_to_PROCESSED-BEFORE-DELIVERY']['state_code_next'] = 'PROCESSED-BEFORE-DELIVERY';			
			}
							
			
			foreach($sqls as $key => $sql) {

				$query = sprintf($sql['sql'], $user->organization['Organization']['id'], $filter_order_id);

				self::d([date("d/m/Y")." - ".date("H:i:s")." StatoElaborazione::orders ".$sql['descri'], $query], $debug);
				
				$results = $Order->query($query);
				if(!empty($results))  {
					
					self::d("StatoElaborazione::orders Aggiornati: ".count($results), $debug);
					
					foreach ($results as $result) {
						
						switch($key) {
							case "from_CREATE-INCOMPLETE_to_OPEN-NEXT_o_OPEN":
								if ($result['Order']['data_inizio'] > date("Y-m-d"))
									$state_code_next = 'OPEN-NEXT';
								else
									$state_code_next = 'OPEN';							
							break;
							case "from_PROCESSED-POST-DELIVERY_to_PROCESSED-BEFORE-DELIVERY":
								if ($result['Order']['data_fine'] > date("Y-m-d"))
									$state_code_next = 'PROCESSED-BEFORE-DELIVERY';
								else
									$state_code_next = 'OPEN';							
							break;
							default:
								$state_code_next = $sql['state_code_next'];
							break;
						}

				
						$OrderLifeCycle->stateCodeUpdate($user, $result, $state_code_next);
					}
				}
			
			}

            if($user->organization['Template']['payToDelivery']=='ON' || $user->organization['Template']['payToDelivery']=='ON-POST') {
             
					self::d("StatoElaborazione::orders Porto gli ordini in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna), pagati (con SummaryOrder.saldato_a != null) a Order.state_code => stato successivo", $debug);

			        $options = [];
			        $options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'],
											  'Order.state_code' => 'PROCESSED-ON-DELIVERY'];
			        if ($order_id != 0) 
			            $options['conditions'] += ['Order.id' => $order_id];
	
			        $options['recursive'] = -1;
			        $results = $Order->find('all', $options);
			        if (!empty($results)) {
			            foreach ($results as $result) {
			
							self::d("StatoElaborazione::orders Tratto l'ordine " . $result['Order']['id'] . " da PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna)", $debug);
							
							if($SummaryOrderLifeCycle->isSummaryOrderAllSaldato($user, $result, $debug)) { // ctrl se e' stato saldato da tutti i gasisti
								
								$state_code_next = $OrderLifeCycle->stateCodeAfter($user, $result, 'PROCESSED-ON-DELIVERY');
								
								$OrderLifeCycle->stateCodeUpdate($user, $result, $state_code_next);
							}
						} // end foreach ($results as $result)
					}
					else {
			            self::d("StatoElaborazione::orders Nessun ordine in stato PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) con tutti gli utenti che hanno pagato (SummaryOrder.saldato_a != null) da portare Order.state_code => stato successivo", $debug);			
					}
            } // end if($user->organization['Template']['payToDelivery']=='ON' || $user->organization['Template']['payToDelivery']=='ON-POST')
			  
            /* 
             * 	orders da TO-PAYMENT (Associato ad una richiesta di pagamento) 
             *  a stato successivo (dipende da template $OrderLifeCycle->stateCodeAfter() )
             *	USER-PAID / SUPPLIER-PAID  / CLOSE
			 */
             if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
             
	              self::d("StatoElaborazione::orders Porto gli ordini in TO-PAYMENT (Associato ad una richiesta di pagamento), pagati (con SummaryOrder.saldato_a != null) a Order.state_code => stato successivo", $debug);

			        $options = [];
			        $options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'],
											  'Order.state_code' => 'TO-PAYMENT'];
			        if ($order_id != 0) 
			            $options['conditions'] += ['Order.id' => $order_id];
	
			        $options['recursive'] = -1;
			        $results = $Order->find('all', $options);
			        if (!empty($results)) {
			            foreach ($results as $result) {
			
							self::d("StatoElaborazione::orders Tratto l'ordine " . $result['Order']['id'] . " da TO-PAYMENT (Associato ad una richiesta di pagamento)", $debug);
							
							if($SummaryOrderLifeCycle->isSummaryOrderAllSaldato($user, $result, $debug)) { // ctrl se e' stato saldato da tutti i gasisti
								
								$state_code_next = $OrderLifeCycle->stateCodeAfter($user, $result, 'TO-PAYMENT');
			
								$OrderLifeCycle->stateCodeUpdate($user, $result, $state_code_next);
							}
						} // end foreach ($results as $result)
					}
					else {
			            self::d("StatoElaborazione::orders Nessun ordine in stato TO-PAYMENT (Associato ad una richiesta di pagamento) con tutti gli utenti che hanno pagato (SummaryOrder.saldato_a != null) da portare Order.state_code => stato successivo", $debug);			
					}
             } // end if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST')
			  
			
			/*
			 * Order.state_code = CLOSE
			 */
			if($user->organization['Organization']['orderUserPaid'] == 'Y') {
			
              self::d("StatoElaborazione::orders Porto gli ordini in CLOSE", $debug);

		        $options = [];
		        $options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'],
										  'Order.state_code' => 'USER-PAID'];
		        if ($order_id != 0) 
		            $options['conditions'] += ['Order.id' => $order_id];

		        $options['recursive'] = -1;
		        $results = $Order->find('all', $options);
		        if (!empty($results)) {
		            foreach ($results as $result) {
		
						self::d("StatoElaborazione::orders Tratto l'ordine " . $result['Order']['id'] . " da USER-PAID (Da saldare da parte dei gasisti)", $debug);
						
						if($SummaryOrderLifeCycle->isSummaryOrderAllSaldato($user, $result, $debug)) { // ctrl se e' stato saldato da tutti i gasisti
						
							if($user->organization['Organization']['orderSupplierPaid'] == 'N') {
								$OrderLifeCycle->stateCodeUpdate($user, $result, 'CLOSE');
							}
							else {
								if($OrderLifeCycle->isPaidSupplier($user, $result))
									$OrderLifeCycle->stateCodeUpdate($user, $result, 'CLOSE');
								else 
									$OrderLifeCycle->stateCodeUpdate($user, $result, 'SUPPLIER-PAID');
							}
						}
						
					} // end foreach ($results as $result)
				}
				else {
		            self::d("StatoElaborazione::orders Nessun ordine in stato USER-PAID (Da saldare da parte dei gasisti)", $debug);			
				}
			} // end if($user->organization['Organization']['orderUserPaid'] == 'Y') 
			
			if($user->organization['Organization']['orderSupplierPaid'] == 'Y') {
			
		        $options = [];
		        $options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'],
										  'Order.state_code' => 'SUPPLIER-PAID'];
		        if ($order_id != 0) 
		            $options['conditions'] += ['Order.id' => $order_id];

		        $options['recursive'] = -1;
		        $results = $Order->find('all', $options);
		        if (!empty($results)) {
		            foreach ($results as $result) {
		
						self::d("StatoElaborazione::orders Tratto l'ordine " . $result['Order']['id'] . " da SUPPLIER-PAID (Pagare al produttore)", $debug);
						
						if($OrderLifeCycle->isPaidSupplier($user, $result)) {
							$OrderLifeCycle->stateCodeUpdate($user, $result, 'CLOSE');
						}
						
					} // end foreach ($results as $result)
				}
				else {
		            self::d("StatoElaborazione::orders Nessun ordine in stato SUPPLIER-PAID (Pagare al produttore)", $debug);			
				}
			} // end if($user->organization['Organization']['orderSupplierPaid'] == 'Y') 
			
        } catch (Exception $e) {
            self::d('UtilsCrons::ordersStatoElaborazione() => StatoElaborazione::orders()'.$sql.' '.$e, $debug);
        }		
	}
	
	public function requestPayment($user, $debug=false, $request_payment_id=0)  {

		$debug=false;

		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;						
	
		switch ($user->organization['Template']['payToDelivery']) {
			case 'ON':
				return;
			break;
			case 'POST':
			case 'ON-POST':
				App::import('Model', 'RequestPayment');
				$RequestPayment = new RequestPayment;
				
				App::import('Model', 'SummaryPayment');
				$SummaryPayment = new SummaryPayment;
				
				App::import('Model', 'RequestPaymentsOrder');
				$RequestPaymentsOrder = new RequestPaymentsOrder;

				/*
				 * cron: estraggo i summary_payments associati alla richiesta di pagamento con importo_richiesto = importo_pagato
				 */
				self::d(date("d/m/Y") . " - " . date("H:i:s") . " StatoElaborazione::requestPayment Porto le richiesta di pagamento con tutti i summary_payments.stato = SOSPESO o PAGATO a RequestPayment.stato_elaborazione = CLOSE", $debug);

				/*
				 * estraggo tutti gli summary_payments di request_payment
				 */
				$results = []; 
				
				$options = [];
				$options['conditions'] = ['RequestPayment.organization_id' => (int)$user->organization['Organization']['id'],
										  'RequestPayment.stato_elaborazione' => 'OPEN'];
				if (!empty($request_payment_id))
					$options['conditions'] += ['RequestPayment.id' => $request_payment_id];
				$options['recursive'] = -1;
				$requestPaymentResults = $RequestPayment->find('all', $options);
				self::d([$options,$requestPaymentResults], $debug);

				foreach($requestPaymentResults as $requestPaymentResult) {					
					
					$all_summary_order_importi_uguali = true;
					
					$options = [];
					$options['conditions'] = ['SummaryPayment.organization_id' => (int)$user->organization['Organization']['id'],
											  'SummaryPayment.request_payment_id' => $requestPaymentResult['RequestPayment']['id']];
					$options['recursive'] = 0;	
					$SummaryPaymentResults = $SummaryPayment->find('all', $options);
					// self::d($options, $debug);	
					// self::d($SummaryPaymentResults, $debug);
					if(!empty($SummaryPaymentResults)) 
					foreach ($SummaryPaymentResults as $SummaryPaymentResult) {
							
						self::d("request_payment_id ".$SummaryPaymentResult['SummaryPayment']['request_payment_id']." user_id ".$SummaryPaymentResult['SummaryPayment']['user_id']." all_summary_order_importi_uguali [".$all_summary_order_importi_uguali."]", $debug);
							
						if(!$SummaryPayment->isPaid($user, $SummaryPaymentResult, $debug)) {
							$all_summary_order_importi_uguali = false;
							break;
						}
					}
					
					if ($all_summary_order_importi_uguali) {
						$this->_requestPaymentClose($user, $requestPaymentResult['RequestPayment']['id'], $debug);
						
						$RequestPaymentsOrder->setOrdersStateCodeByRequestPaymentId($user, $requestPaymentResult['RequestPayment']['id'], '', $debug); // lo stato successivo lo calcola OrderLifeCycle::stateCodeAfter
					}					
					
				} // loop foreach($requestPaymentResults as $requestPaymentResult) 
				
				/*
				 * cancello le richieste di pagamento senza ordini associati perche' gia' in statistiche
				 */
				$options = [];
				$options['conditions'] = ['RequestPayment.organization_id' => (int)$user->organization['Organization']['id'],
										  'RequestPayment.stato_elaborazione' => 'CLOSE'];
				if (!empty($request_payment_id))
					$options['conditions'] += ['RequestPayment.id' => $request_payment_id];
				$options['recursive'] = -1;
				$requestPaymentResults = $RequestPayment->find('all', $options);
				self::d([$options,$requestPaymentResults], $debug);
				foreach($requestPaymentResults as $requestPaymentResult) {					
					
					$options = [];
					$options['conditions'] = ['RequestPaymentsOrder.organization_id' => (int)$user->organization['Organization']['id'],
											  'RequestPaymentsOrder.request_payment_id' => $requestPaymentResult['RequestPayment']['id']];
					$options['recursive'] = -1;	
					$requestPaymentsOrderResults = $RequestPaymentsOrder->find('all', $options);
					self::d([$options,$requestPaymentsOrderResults], $debug);
				
					if(empty($requestPaymentsOrderResults)) {
						$RequestPayment->id = $requestPaymentResult['RequestPayment']['id'];
						
						if ($RequestPayment->delete())
							self::d("DELETE RequestPayment " . $requestPaymentResult['RequestPayment']['id'] . " OK", $debug);    
						else
							self::d("DELETE RequestPayment " . $requestPaymentResult['RequestPayment']['id'] . " ERRORE", $debug);
					} // if(empty($requestPaymentsOrderResults))
								
					
				} // loop foreach($requestPaymentResults as $requestPaymentResult)				
			break;
			default:
				self::x("StatoElaborazione::requestPayment Template.payToDelivery [".$user->organization['Template']['payToDelivery']."] non valido!");
				return;
			break;		
		}
		
		
	}
	
	private function _requestPaymentClose($user, $request_payment_id, $debug) {
		
		App::import('Model', 'RequestPayment');
		$RequestPayment = new RequestPayment;
		
		$options = [];
		$options['conditions'] = ['RequestPayment.organization_id' => $user->organization['Organization']['id'],
							      'RequestPayment.id' => $request_payment_id];
		$options['recursive'] = -1;
		$requestPaymentResults = $RequestPayment->find('first', $options);	

		$requestPaymentResults['RequestPayment']['stato_elaborazione'] = 'CLOSE';
		self::d($requestPaymentResults, $debug);
		$RequestPayment->create();
		if(!$RequestPayment->save($requestPaymentResults))
			return false;
		else
			return true;			
	}
}