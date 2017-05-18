<?php
App::uses('AppModel', 'Model');
App::uses('CakeTime', 'Utility');

class Cassiere extends AppModel {

	public $useTable = false;

	/*
	 * le consegne con Delivery.stato_elaborazione = OPEN
	 * e gli ordini associati in 
	 *		Order.stato = CLOSE
	 * 		Order.tesoriere_stato_pay = Y (se $user->organization['Organization']['hasUserGroupsTesoriere']=='Y')
	 * possono essere chiusi
	 *
	 * se $cron=true 'DATE(Delivery.data) <= CURDATE() - INTERVAL '.Configure::read('GGAlertCassiereDeliveriesToClose').' DAY');
	 */
	public function getDeliveriesToClose($user, $cron=false, $debug=true) {

		$newResults = array();
	
		/*
		 *  se payToDelivery = POST non ho la cassa
		 */
		if($user->organization['Organization']['payToDelivery']!='ON' && $user->organization['Organization']['payToDelivery']!='ON-POST') 
			return $newResults;
		
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'Order');
		
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$user->organization['Organization']['id'],
									   'Delivery.isVisibleBackOffice' => 'Y',
									   'Delivery.stato_elaborazione' => 'OPEN',
									   'Delivery.sys' => 'N');
		if($cron)
			$options['conditions'] += array('DATE(Delivery.data) <= CURDATE() - INTERVAL '.Configure::read('GGAlertCassiereDeliveriesToClose').' DAY');
		else
			$options['conditions'] += array('DATE(Delivery.data) < CURDATE()');
		
		$options['order'] = 'data ASC';
		$options['recursive'] = -1;
		$results = $Delivery->find('all', $options);
		
		if($debug) {
			echo "<pre>Consegne ancora OPEN  ";
			echo print_r($options);
			echo count($results);
			echo "</pre>";
		}
		
		if(!empty($results))
		foreach($results as $numResult => $result) {

			$Order = new Order;

			/*
			 * ordini totali della consegna
			 */
			$options = array();
			$options['conditions'] = array('Order.organization_id' => (int)$user->organization['Organization']['id'],
										   'Order.delivery_id' => $result['Delivery']['id']);
			$options['recursive'] = -1;
			$totOrders = $Order->find('count', $options);
		
			if($debug) 
				echo "<br />Totale ordini della consegna ".$result['Delivery']['luogoData'].': '.$totOrders;
			
			/*
			 * ordini chiusi della consegna
			 *
			 * il cron::ordersIncomingOnDeliveryToClose() li porta da PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) a CLOSE
			 * 		se tutti gli utenti hanno pagato SummaryOrder.importo = SummaryOrder.importo_pagato
			 */			
			$options = array();
			$options['conditions'] = array('Order.organization_id' => (int)$user->organization['Organization']['id'],
										   'Order.delivery_id' => $result['Delivery']['id'],
										   'Order.state_code' => 'CLOSE');
										   
			/*
			 * se ho il tesoriere devo gestire il pagamento
			*/			
			if($user->organization['Organization']['hasUserGroupsTesoriere']=='Y')
				$options['conditions'] += array('Order.tesoriere_stato_pay' => 'Y');
			
			$options['recursive'] = -1;
			if($debug) {
				echo "<pre>";
				print_r($options);
				echo "</pre>";
			}			
			
			$totOrdersClose = $Order->find('count', $options);

			if($debug) 
				echo "<br />Totale ordini CHIUSI della consegna ".$result['Delivery']['luogoData'].': '.$totOrdersClose;
			
			if($totOrders==$totOrdersClose) 
				$newResults[$numResult] = $result;	
		}
		
		return $newResults;
	}
	
	/*
	 * cambio lo stato della DELIVERY
	 *   Cassiere::admin_edit_stato_elaborazione
	 *   Cron::deliveriesCassiereClose
	 */	
	public function deliveryStatoClose($user, $delivery_id) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$sql = "UPDATE
					".Configure::read('DB.prefix')."deliveries 
				SET
					stato_elaborazione = 'CLOSE',
					modified = '".date('Y-m-d H:i:s')."'
				WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					and id = ".(int)$delivery_id;
		// echo '<br />'.$sql;
		$result = $Delivery->query($sql);

		/*
		 * cambio lo stato degli ORDERS
		*/
		$sql = "UPDATE
					".Configure::read('DB.prefix')."orders
				SET
					state_code = 'CLOSE',
					modified = '".date('Y-m-d H:i:s')."'
				WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					and delivery_id = ".(int)$delivery_id;
		// echo '<br />'.$sql;
		$result = $Delivery->query($sql);		
	}	
	
	/*
	 * estraggo tutte le consegne con ordini in stato Order.state_code == 'PROCESSED-ON-DELIVERY' In carico al cassiere durante la consegna
	 */
	public function get_cassiere_deliveries($user, $isCassiere, $isReferentCassiere, $debug=false) {
		
		$deliveries = array();

		if($isCassiere) {

			$sql = "SELECT
						Delivery.id, Delivery.luogo, Delivery.data 
					FROM
						".Configure::read('DB.prefix')."orders `Order`,
						".Configure::read('DB.prefix')."deliveries Delivery 
					WHERE
						`Order`.organization_id = ".(int)$user->organization['Organization']['id']."
						and Delivery.organization_id = ".(int)$user->organization['Organization']['id']."
						and Delivery.isVisibleBackOffice = 'Y' 
						and Delivery.stato_elaborazione = 'OPEN' 
						and Delivery.sys = 'N' 
						and `Order`.state_code = 'PROCESSED-ON-DELIVERY' 					
						and `Order`.delivery_id = Delivery.id 
						ORDER BY Delivery.data ASC";
		}
		else
		if($isReferentCassiere)	{
	 
			$sql = "SELECT
						Delivery.id, Delivery.luogo, Delivery.data 
					FROM
						".Configure::read('DB.prefix')."suppliers_organizations SuppliersOrganization,
						".Configure::read('DB.prefix')."orders `Order`,
						".Configure::read('DB.prefix')."deliveries Delivery 
					WHERE
						 SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
						and `Order`.organization_id = ".(int)$user->organization['Organization']['id']."
						and Delivery.organization_id = ".(int)$user->organization['Organization']['id']."
						and Delivery.isVisibleBackOffice = 'Y' 
						and Delivery.stato_elaborazione = 'OPEN' 
						and Delivery.sys = 'N' 
						and `Order`.state_code = 'PROCESSED-ON-DELIVERY' 					
						and `Order`.delivery_id = Delivery.id 
						and `Order`.supplier_organization_id = SuppliersOrganization.id 
						and SuppliersOrganization.id IN (".$user->get('ACLsuppliersIdsOrganization').")
						ORDER BY Delivery.data ASC";				
		}	
		
		if($debug) echo '<br />'.$sql; 
		$results = $this->query($sql);	
			
		if(!empty($results))
                foreach ($results as $result) {
                    // $DeliveryData = date('d', strtotime($result['Delivery']['data'])) . '/' . date('n', strtotime($result['Delivery']['data'])) . '/' . date('Y', strtotime($result['Delivery']['data']));
                    $DeliveryData = CakeTime::format($result['Delivery']['data'], "%A %e %B %Y");
                    $deliveries[$result['Delivery']['id']] = $DeliveryData . ' - ' . $result['Delivery']['luogo'];
                }

		return $deliveries;
	}	

	/*
	 * situazione di tutti gli ordini in CLOSE di una consegna
	 */
	public function lists_orders_close($user, $delivery_id, $debug=false) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
			
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
														'Order.isVisibleBackOffice != ' => 'N',
														'Order.state_code' => 'CLOSE');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options = array();
		$options['conditions'] = array('Delivery.id' => $delivery_id,
										'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
										'Delivery.sys'=> 'N',
										'Delivery.isVisibleBackOffice' => 'Y');
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$results = $this->__lists_orders($user, $results, $debug);
		return $results;
	}

	/*
	 * situazione di tutti gli ordini in 
	 *		PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) 
	 *		WAIT-PROCESSED-TESORIERE, PROCESSED-TESORIERE, TO-PAYMENT
	 *		CLOSE 
	 di una consegna
	 */
	public function lists_orders_all($user, $delivery_id, $debug=false) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
			
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
														'Order.isVisibleBackOffice != ' => 'N',
														'(Order.state_code = \'PROCESSED-ON-DELIVERY\' OR Order.state_code = \'WAIT-PROCESSED-TESORIERE\' OR Order.state_code = \'PROCESSED-TESORIERE\' OR Order.state_code = \'TO-PAYMENT\' OR Order.state_code = \'CLOSE\')');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options = array();
		$options['conditions'] = array('Delivery.id' => $delivery_id,
										'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
										'Delivery.sys'=> 'N',
										'Delivery.isVisibleBackOffice' => 'Y');
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$results = $this->__lists_orders($user, $results, $debug);
		return $results;
	}
	
	/*
	 * situazione di tutti gli ordini in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) di una consegna
	 */
	public function lists_orders_processed_on_delivery($user, $delivery_id, $debug=false) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
														'Order.isVisibleBackOffice != ' => 'N',
														'Order.state_code' => 'PROCESSED-ON-DELIVERY');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options = array();
		$options['conditions'] = array('Delivery.id' => $delivery_id,
										'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
										'Delivery.sys'=> 'N',
										'Delivery.isVisibleBackOffice' => 'Y');
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$results = $this->__lists_orders($user, $results, $debug);
		return $results;
	}
	
	private function __lists_orders($user, $results, $debug) {

		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'Cash');
		
		App::import('Model', 'SummaryOrder');
	
		App::import('Model', 'SuppliersOrganization');
		App::import('Model', 'SuppliersOrganizationsReferent');
		
		$newResults = array();
		
		$numOrderNewResults=0;
		foreach ($results['Order'] as $numOrder => $order) {
	
			/*
			 *  se tutti i SummaryOrder sono stati pagati lo escludo, non e' da passare al Tesoriere
			 */
			$SummaryOrder = new SummaryOrder;
			
			$options = array();
			$options['conditions'] = array('SummaryOrder.organization_id' => (int)$user->organization['Organization']['id'],
								'SummaryOrder.order_id' => $order['id'],
								'SummaryOrder.importo_pagato' => '0.00',
								'SummaryOrder.modalita' => 'DEFINED');
			$totUserToTesoriere = $SummaryOrder->find('count', $options);
			
			$newResults['Order'][$numOrderNewResults] = $results['Order'][$numOrder];
			$newResults['Order'][$numOrderNewResults]['totUserToTesoriere'] = $totUserToTesoriere;			
			/*
			echo '<pre>Cassiere::lists_orders_processed_on_delivery() ';
			print_r($options['conditions']);
			echo '</pre>';
			echo "<br />totUserToTesoriere ".$totUserToTesoriere;
			*/

			/*
			 *  tutti i SummaryOrder e users
			 */
			$SummaryOrder->unbindModel(array('belongsTo' => array('Order','Delivery')));
			
			$options = array();
			$options['conditions'] = array('SummaryOrder.organization_id' => (int)$user->organization['Organization']['id'],
											'SummaryOrder.order_id' => $order['id']);
			$options['recursive'] = 1;
			$options['order'] = array('SummaryOrder.user_id');
			$summaryOrderResults = $SummaryOrder->find('all', $options);	
			$newResults['Order'][$numOrderNewResults]['SummaryOrder'] = $summaryOrderResults;
			 
			/*
			 *  cash
			 */
			 if(!empty($summaryOrderResults)) {
				 foreach($summaryOrderResults as $numResultSummaryOrder => $summaryOrderResult) {
					$options = array();
					$options['conditions'] = array('Cash.organization_id' => $user->organization['Organization']['id'],
												   'Cash.user_id'=> $summaryOrderResult['SummaryOrder']['user_id']);
					$Cash = new Cash;
					$cashResults = $Cash->find('first', $options);
					$newResults['Order'][$numOrderNewResults]['SummaryOrder'][$numResultSummaryOrder]['Cash'] = $cashResults['Cash'];					
				 }
			 }
			
			/*
			 * Referents
			*/
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
						
			$conditions = array('User.block' => 0,
								'SuppliersOrganization.id' => $order['supplier_organization_id']);
			$suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions);
			
			if(!empty($suppliersOrganizationsReferent))
				$newResults['Order'][$numOrderNewResults]['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent;			
			
			/*
			 * Suppliers
			* */
			$SuppliersOrganization = new SuppliersOrganization;
			$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization', 'CategoriesSupplier')));
			$SuppliersOrganization->unbindModel(array('hasMany' => array('Article', 'Order', 'SuppliersOrganizationsReferent')));
			
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
										   'SuppliersOrganization.id' => $order['supplier_organization_id']);
			$options['recursive'] = 1;
			$SuppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
			if(!empty($SuppliersOrganizationResults)) {
				$newResults['Order'][$numOrderNewResults]['Supplier'] = $SuppliersOrganizationResults['Supplier'];
				$newResults['Order'][$numOrderNewResults]['SuppliersOrganization'] = $SuppliersOrganizationResults['SuppliersOrganization'];
			}
				
			$numOrderNewResults++;
			
		} // end  foreach ($results['Order'] as $numOrder => $order)
	
		return $newResults;	
	}
	

	/*
	 * situazione di tutti gli users di una consegna in 
	 *		PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) 
	 *		WAIT-PROCESSED-TESORIERE, PROCESSED-TESORIERE, TO-PAYMENT
	 *		CLOSE 
	 di una consegna
	 */
	public function lists_users_delivery_all($user, $delivery_id, $debug=false) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
			
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
														'Order.isVisibleBackOffice != ' => 'N',
														'(Order.state_code = \'PROCESSED-ON-DELIVERY\' OR Order.state_code = \'WAIT-PROCESSED-TESORIERE\' OR Order.state_code = \'PROCESSED-TESORIERE\' OR Order.state_code = \'TO-PAYMENT\' OR Order.state_code = \'CLOSE\')');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options = array();
		$options['conditions'] = array('Delivery.id' => $delivery_id,
										'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
										'Delivery.sys'=> 'N',
										'Delivery.isVisibleBackOffice' => 'Y');
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$results = $this->__lists_users_delivery($user, $delivery_id, $results, $debug);
		return $results;
	}
	
	/*
	 * situazione di tutti users di una consegna in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) di una consegna
	 */
	public function lists_users_delivery_processed_on_delivery($user, $delivery_id, $debug=false) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
														'Order.isVisibleBackOffice != ' => 'N',
														'Order.state_code' => 'PROCESSED-ON-DELIVERY');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options = array();
		$options['conditions'] = array('Delivery.id' => $delivery_id,
										'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
										'Delivery.sys'=> 'N',
										'Delivery.isVisibleBackOffice' => 'Y');
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$results = $this->__lists_users_delivery($user, $delivery_id, $results, $debug);
		return $results;
	}

	private function __lists_users_delivery($user, $delivery_id, $results, $debug) {

		$order_ids='';
		$newResults = array();
		 
		foreach ($results['Order'] as $numOrder => $order) 
			$order_ids .= $order['Order']['id'].',';
		if(!empty($order_ids)) {
			$order_ids = substr($order_ids, 0, strlen($order_ids)-1);
			
			$orderBy = Configure::read('orderUser');
	
			$sql = "SELECT 
						User.id, User.name, User.username, User.email,
						SUM(SummaryOrder.importo) as tot_importo, SUM(SummaryOrder.importo_pagato) as tot_importo_pagato,
						Cash.importo, Cash.nota  
					FROM 
						".Configure::read('DB.portalPrefix')."users User LEFT JOIN ".Configure::read('DB.prefix')."cashes Cash  
						ON (Cash.user_id = User.id AND Cash.organization_id = ".(int)$user->organization['Organization']['id']."), 
						".Configure::read('DB.prefix')."summary_orders SummaryOrder 
					WHERE 
						User.organization_id = ".(int)$user->organization['Organization']['id']." 
						AND SummaryOrder.organization_id = ".(int)$user->organization['Organization']['id']." 
						AND SummaryOrder.user_id = User.id
						AND User.block = 0 ";  // 0 attivo
			$sql .= " GROUP BY User.id, User.name, User.username, User.email, Cash.importo, Cash.nota    
					  ORDER BY ".$orderBy;
			if($debug) echo '<br />'.$sql;					
			$newResults = $this->query($sql);
		}
		
		/*
		 * pagamento POS
		 */
		if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
			
			App::import('Model', 'SummaryDeliveriesPos');
			
			foreach($newResults as $numResult => $newResult) {
			
				$user_id = $newResult['User']['id'];
				
				$SummaryDeliveriesPos = new SummaryDeliveriesPos;
				
				$summaryDeliveriesPosResults = $SummaryDeliveriesPos->findPaymentPos($user, $delivery_id, $user_id);
				if(!empty($summaryDeliveriesPosResults))
					$newResults[$numResult]['SummaryDeliveriesPos'] = $summaryDeliveriesPosResults['SummaryDeliveriesPos'];
			
			}
		}		
		/*
		echo "<pre>";
		print_r($newResults);
		echo "</pre>";
		*/
		return $newResults;
	}
			
}