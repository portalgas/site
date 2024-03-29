<?php
App::uses('AppModel', 'Model');
App::uses('CakeTime', 'Utility');

class Cassiere extends AppModel {

	public $useTable = false;
		
	/*
	 * estraggo tutte le consegne con ordini in stato Order.state_code == 'PROCESSED-ON-DELIVERY' In carico al cassiere durante la consegna
	 */
	public function get_cassiere_deliveries($user, $isCassiere, $isReferentCassiere, $debug=false) {
		
		$deliveries = [];

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
		
		self::d($sql, $debug); 
		$results = $this->query($sql);	
			
		if(isset($user->organization['Organization']['hasGasGroups']) && $user->organization['Organization']['hasGasGroups']=='Y') {
			App::import('Model', 'GasGroupDelivery');
			$GasGroupDelivery = new GasGroupDelivery;
		}

		if(!empty($results))
                foreach ($results as $result) {

					if(isset($user->organization['Organization']['hasGasGroups']) && $user->organization['Organization']['hasGasGroups']=='Y') {	
						$gasGroupDeliveryLabel = $GasGroupDelivery->getLabel($user, $user->organization['Organization']['id'], $result['Delivery']['id']);
						if($gasGroupDeliveryLabel!==false)
							$deliveries[$result['Delivery']['id']] = $gasGroupDeliveryLabel;
					}
					else {
						// $DeliveryData = date('d', strtotime($result['Delivery']['data'])) . '/' . date('n', strtotime($result['Delivery']['data'])) . '/' . date('Y', strtotime($result['Delivery']['data']));
						$DeliveryData = CakeTime::format($result['Delivery']['data'], "%A %e %B %Y");
						$deliveries[$result['Delivery']['id']] = $result['Delivery']['luogo'].' - '.$DeliveryData;
					}
                }

		return $deliveries;
	}	

	/*
	 * situazione di tutti gli ordini in CLOSE di una consegna
	 */
	public function lists_orders_close($user, $delivery_id, $debug=false) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
			
		$Delivery->hasMany['Order']['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'],
													'Order.isVisibleBackOffice != ' => 'N',
													'Order.state_code' => 'CLOSE'];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		$options = [];
		$options['conditions'] = ['Delivery.id' => $delivery_id,
								  'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
								  'Delivery.sys'=> 'N',
								  'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$results = $this->_lists_orders($user, $results, $debug);
		return $results;
	}

	/*
	 * situazione di tutti gli ordini in 
	 *		PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) 
	 *		WAIT-PROCESSED-TESORIERE, PROCESSED-TESORIERE, TO-PAYMENT, WAIT-REQUEST-PAYMENT-CLOSE
	 *		CLOSE 
	 di una consegna
	 */
	public function lists_orders_all($user, $delivery_id, $debug=false) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
			
		$Delivery->hasMany['Order']['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'],
													'Order.isVisibleBackOffice != ' => 'N',
													"Order.state_code IN ('PROCESSED-ON-DELIVERY','WAIT-PROCESSED-TESORIERE','PROCESSED-TESORIERE','TO-PAYMENT','WAIT-REQUEST-PAYMENT-CLOSE', 'CLOSE')"];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		$options = [];
		$options['conditions'] = ['Delivery.id' => $delivery_id,
								'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
								'Delivery.sys'=> 'N',
								'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$results = $this->_lists_orders($user, $results, $debug);
		return $results;
	}
	
	/*
	 * situazione di tutti gli ordini in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) di una consegna
	 */
	public function lists_orders_processed_on_delivery($user, $delivery_id, $debug=false) {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$Delivery->hasMany['Order']['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'],
													'Order.isVisibleBackOffice != ' => 'N',
													'Order.state_code' => 'PROCESSED-ON-DELIVERY'];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		$options = [];
		$options['conditions'] = ['Delivery.id' => $delivery_id,
									'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
									'Delivery.sys'=> 'N',
									'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		self::d($results, $debug);
		
		$results = $this->_lists_orders($user, $results, $debug);
		return $results;
	}
	
	private function _lists_orders($user, $results, $debug) {

		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'Cash');
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
		
		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;
		
		App::import('Model', 'SuppliersOrganization');
		App::import('Model', 'SuppliersOrganizationsReferent');
		
		$newResults = [];
			
		$numOrderNewResults=0;
		foreach ($results['Order'] as $numOrder => $order) {

			$newResults['Order'][$numOrderNewResults] = $results['Order'][$numOrder];
		
			/*
			 *  se tutti i SummaryOrder sono stati pagati lo escludo, non e' da passare al Tesoriere
			 * forzo Organization.orderUserPaid
			 */
			$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $user->organization['Organization']['id'], 'orderUserPaid' => 'Y']);

			$newResults['Order'][$numOrderNewResults]['PaidUsers'] = $OrderLifeCycle->getPaidUsers($tmp_user, $order['id'], $debug);
			
			/*
			 *  tutti i SummaryOrder e users
			 */
			$SummaryOrder->unbindModel(['belongsTo' => ['Order','Delivery']]);
			
			$options = [];
			$options['conditions'] = ['SummaryOrder.organization_id' => (int)$user->organization['Organization']['id'],
									  'SummaryOrder.order_id' => $order['id']];
			$options['recursive'] = 1;
			$options['order'] = ['SummaryOrder.user_id'];
			$summaryOrderResults = $SummaryOrder->find('all', $options);	
			$newResults['Order'][$numOrderNewResults]['SummaryOrder'] = $summaryOrderResults;
			 
			/*
			 *  cash
			 */
			 if(!empty($summaryOrderResults)) {
				 foreach($summaryOrderResults as $numResultSummaryOrder => $summaryOrderResult) {
					$options = [];
					$options['conditions'] = ['Cash.organization_id' => $user->organization['Organization']['id'],
											  'Cash.user_id'=> $summaryOrderResult['SummaryOrder']['user_id']];
					$Cash = new Cash;
					$cashResults = $Cash->find('first', $options);
					$newResults['Order'][$numOrderNewResults]['SummaryOrder'][$numResultSummaryOrder]['Cash'] = $cashResults['Cash'];					
				 }
			 }
			
			/*
			 * Referents
			*/
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
						
			$conditions = ['User.block' => 0,
							'SuppliersOrganization.id' => $order['supplier_organization_id']];
			$suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions);
			
			if(!empty($suppliersOrganizationsReferent))
				$newResults['Order'][$numOrderNewResults]['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent;			
			
			/*
			 * Suppliers
			* */
			$SuppliersOrganization = new SuppliersOrganization;
			$SuppliersOrganization->unbindModel(['belongsTo' => ['Organization', 'CategoriesSupplier']]);
			$SuppliersOrganization->unbindModel(['hasMany' => ['Article', 'Order', 'SuppliersOrganizationsReferent']]);
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
									   'SuppliersOrganization.id' => $order['supplier_organization_id']];
			$options['recursive'] = 1;
			$SuppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
			if(!empty($SuppliersOrganizationResults)) {
				$newResults['Order'][$numOrderNewResults]['Supplier'] = $SuppliersOrganizationResults['Supplier'];
				$newResults['Order'][$numOrderNewResults]['SuppliersOrganization'] = $SuppliersOrganizationResults['SuppliersOrganization'];
			}
				
			$numOrderNewResults++;
			
		} // end  foreach ($results['Order'] as $numOrder => $order)
		self::d($newResults, $debug);
		
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
			
		$Delivery->hasMany['Order']['conditions'] = [
			'Order.organization_id' => $user->organization['Organization']['id'],
			'Order.isVisibleBackOffice != ' => 'N',
			"(Order.state_code = 'PROCESSED-ON-DELIVERY' OR 
			  Order.state_code = 'WAIT-PROCESSED-TESORIERE' OR 
			  Order.state_code = 'PROCESSED-TESORIERE' OR 
			  Order.state_code = 'TO-PAYMENT' OR 
			  Order.state_code = 'CLOSE')"];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		
		$options = [];
		$options['conditions'] = [
				'Delivery.id' => $delivery_id,
				'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
				'Delivery.sys'=> 'N',
				'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		// self::dd($options, $debug);
		// self::dd($results, $debug);

		$results = $this->_lists_users_delivery($user, $delivery_id, $results, $debug);
		return $results;
	}
	
	/*
	 * situazione di tutti users di una consegna in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) di una consegna
	 */
	public function lists_users_delivery_processed_on_delivery($user, $delivery_id, $debug=false) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$Delivery->hasMany['Order']['conditions'] = [
			'Order.organization_id' => $user->organization['Organization']['id'],
			'Order.isVisibleBackOffice != ' => 'N',
			'Order.state_code' => 'PROCESSED-ON-DELIVERY'];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		$options = [];
		$options['conditions'] = [
			'Delivery.id' => $delivery_id,
			'Delivery.organization_id' => (int)$user->organization['Organization']['id'],
			'Delivery.sys'=> 'N',
							'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$results = $this->_lists_users_delivery($user, $delivery_id, $results, $debug);
		return $results;
	}

	private function _lists_users_delivery($user, $delivery_id, $results, $debug) {

		$order_ids = '';
		$newResults = [];

		foreach ($results['Order'] as $numOrder => $order) {
			if(isset($order['Order']['id']))
				$order_ids .= $order['Order']['id'].',';
			else
				$order_ids .= $order['id'].',';
		}

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
						AND SummaryOrder.order_id IN (".$order_ids.")
						AND User.block = 0 ";  // 0 attivo
			$sql .= " GROUP BY User.id, User.name, User.username, User.email, Cash.importo, Cash.nota    
					  ORDER BY ".$orderBy;
			self::d($sql, $debug);					
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
		self::d($sql, $debug);	

		return $newResults;
	}		
}