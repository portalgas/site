<?php
App::uses('AppModel', 'Model');


class SummaryOrder extends AppModel {
  
	/* 
	 *  estrae tutti i summarOrder di un ordine
	 */	 
	public function select_to_order($user, $order_id, $user_id=0, $debug=false) {
	
		// $debug = true;
		
		$sql = "SELECT 
					SummaryOrder.*,
					User.id, User.name, User.username, User.email   
				FROM 
					".Configure::read('DB.prefix')."summary_orders as SummaryOrder, 
					".Configure::read('DB.portalPrefix')."users as User 
				WHERE
					SummaryOrder.organization_id = ".(int)$user->organization['Organization']['id']." 
					AND User.organization_id = ".(int)$user->organization['Organization']['id']." 
					AND SummaryOrder.order_id = ".(int)$order_id."
					AND SummaryOrder.user_id = User.id ";
		if($user_id>0) $sql .= " AND User.id = ".$user_id;
		$sql .= " ORDER BY SummaryOrder.user_id";
		self::d($sql, $debug) ;
		try {
			$results = $this->query($sql);
			
			if($user_id>0 && !empty($results))
				$results = current($results);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
		return $results;
	}

	/*
	 *  calcolare il totale degli importi di un ordine
	*/
	public function select_totale_importo_to_order($user, $order_id) {
		
		$options = [];
		$options['conditions'] = ['SummaryOrder.organization_id' => $user->organization['Organization']['id'],
								  'SummaryOrder.order_id' => $order_id];
		$options['fields'] = ['sum(SummaryOrder.importo) as totale_importo'];
		$options['recursive'] = -1;
		
		$results = $this->find('first', $options);
		$results = current($results);
		if(empty($results['totale_importo'])) 
			$results = 0;
		else 
			$results = $results['totale_importo'];
						
		self::l("SummaryOrder::select_totale_importo_to_order order_id ".$order_id." totale_importo ".$results);
		
		return $results;
	}

	/* 
	 * non cancello SummaryOrder gia' pagati SummaryOrder.saldato_a CASSIERE / TESORIERE
	 * se payToDelivery = POST il cassiere gestisce SummaryPayment e da li viene settato SummaryOrder.saldato_a
	 */
	public function delete_to_order($user, $order_id, $debug = false) {
		
		switch($user->organization['Template']['payToDelivery']) {
			case "ON":
			case "ON-POST":
			case "POST":
				$options = [];
				$options['conditions'] = ['SummaryOrder.organization_id' => $user->organization['Organization']['id'],
										  'SummaryOrder.order_id' => $order_id,
										  'SummaryOrder.saldato_a is null'];				
				$results = $this->deleteAll($options['conditions'], false);
			break;
		}

		self::l("SummaryOrder::delete_to_order order_id order_id ".$order_id." con saldato_a is null ".$sql);
		
		return true;
	}
		
	/*
	 * se user_id valorizzato popolo i dati aggregati dello specifico user 
	 *		per ex SummaryOrder::admin_ajax_summary_orders_ricalcola()
	 *
	 * non cancello SummaryOrder gia' pagati SummaryOrder.saldato_a CASSIERE / TESORIERE => non li carico nuovamente
	 * se payToDelivery = POST il cassiere gestisce SummaryPayment e da li viene settato SummaryOrder.saldato_a
	 */
	public function populate_to_order($user, $order_id, $user_id=0, $debug = false) {

		self::l("SummaryOrder::populate_to_order order_id ".$order_id);
		
		try {
			/*
			 * ctrl che l'ordine sia in stato valido per creare i dati aggragati
			 */
			App::import('Model', 'Order');
			$Order = new Order;
			
			$options = [];
			$options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'],
									  'Order.id' => $order_id];
			$options['recursive'] = -1;
			$options['fields'] = ['delivery_id', 'typeGest', 'hasTrasport', 'trasport', 'hasCostMore', 'cost_more', 'hasCostLess', 'cost_less', 'state_code'];
			$results = $Order->find('first', $options);
			
			/*
			 * estraggo eventuale spesa di trasporto
			 */
			if($results['Order']['hasTrasport']=='Y' && $results['Order']['trasport']>0) {

				self::l("SummaryOrder::populate_to_order order_id ".$order_id." Ordine ha le spese di traporto ".$results['Order']['trasport']);
				
				App::import('Model', 'SummaryOrderTrasport');
				$SummaryOrderTrasport = new SummaryOrderTrasport;
				
				$resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($user, $order_id, $user_id);				
			}
			else {
				self::l("SummaryOrder::populate_to_order order_id ".$order_id." Ordine NON ha le spese di traporto ".$results['Order']['trasport']);
			}
			
			/*
			 * estraggo eventuale costo aggiuntivo
			*/
			if($results['Order']['hasCostMore']=='Y' && $results['Order']['cost_more']>0) {
			
				self::l("SummaryOrder::populate_to_order order_id ".$order_id." Ordine ha costo aggiuntivo ".$results['Order']['cost_more']);				
				
				App::import('Model', 'SummaryOrderCostMore');
				$SummaryOrderCostMore = new SummaryOrderCostMore;
			
				$resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($user, $order_id, $user_id);
			}
			else {
				self::l("SummaryOrder::populate_to_order order_id ".$order_id." Ordine NON ha costo aggiuntivo ".$results['Order']['cost_more']);			
			}

			/*
			 * estraggo eventuale sconto
			*/
			if($results['Order']['hasCostLess']=='Y' && $results['Order']['cost_less']>0) {
					
				self::l("SummaryOrder::populate_to_order order_id ".$order_id." Ordine ha uno sconto ".$results['Order']['cost_less']);			
				
				App::import('Model', 'SummaryOrderCostLess');
				$SummaryOrderCostLess = new SummaryOrderCostLess;
					
				$resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($user, $order_id, $user_id);
			}
			else {
				self::l("SummaryOrder::populate_to_order order_id ".$order_id." Ordine NON ha uno sconto ".$results['Order']['cost_less']);
			}
			
			/*
			 * estraggo eventuali dati aggregati
			 * se non ci sono li estraggo tutti gli acquisti in base all'ordine
			 */
			if($results['Order']['typeGest']=='AGGREGATE') {
				
				self::l("SummaryOrder::populate_to_order order_id ".$order_id." Ordine ha i dati aggregati ".$results['Order']['typeGest']);
				
				App::import('Model', 'SummaryOrderAggregate');
				$SummaryOrderAggregate = new SummaryOrderAggregate;
				
				$summaryOrderAggregateResults = $SummaryOrderAggregate->select_to_order($user, $order_id, $user_id);				
			}
			else {
				self::l("SummaryOrder::populate_to_order order_id ".$order_id." Ordine NON ha i dati aggregati ".$results['Order']['typeGest']);
			}
				
				
			/*
			 * estraggo tutti gli acquisti in base all'ordine
			* ottienti dati Article, ArticlesOrder, Cart
			* */
			App::import('Model', 'ArticlesOrder');
			$ArticlesOrder = new ArticlesOrder;
			$conditions = ['Order.id' => $order_id,
							'Order.organization_id' => $user->organization['Organization']['id'],
							'Cart.deleteToReferent' => 'N'];
			if($user_id>0)
				$conditions += array('Cart.user_id' => $user_id);
			$orderBy = ['User' => 'User.id'];
			$articlesOrders = $ArticlesOrder->getArticoliAcquistatiDaUtenteInOrdine($user, $conditions, $orderBy);
			
			if(!empty($articlesOrders)) {
			
				$summaryCarts = [];
				$user_id_old=0;
				$importo=0;
				$i=0;
				foreach($articlesOrders as $articlesOrder) {
			
					self::l("SummaryOrder::populate_to_order $i) tratto l'utente ".$articlesOrder['User']['name'].' ('.$articlesOrder['Cart']['user_id'].") (precedente $user_id_old)", $debug);
			
					if($user_id_old>0 && $articlesOrder['Cart']['user_id']!=$user_id_old) {
						self::l("SummaryOrder::populate_to_order $i) utente diverso => memorizzo in ", $debug);
			
						$summaryCarts[$i]['user_id'] = $user_id_old;
						$summaryCarts[$i]['importo'] = $importo;
						$importo = 0;
				
						self::l($summaryCarts[$i], $debug);
			
						$i++;
					}
			
					/*
					 * importo, somma di tutti gli importi di un utente
					 */
					if($articlesOrder['Cart']['importo_forzato']>0)
						$importo += $articlesOrder['Cart']['importo_forzato'];
					else
						if($articlesOrder['Cart']['qta_forzato']>0)
						$importo += ($articlesOrder['ArticlesOrder']['prezzo'] * $articlesOrder['Cart']['qta_forzato']);
					else
						$importo += ($articlesOrder['ArticlesOrder']['prezzo'] * $articlesOrder['Cart']['qta']);  
			
					self::l("SummaryOrder::populate_to_order $i)     importo dell'utente ".$articlesOrder['Cart']['user_id'].": ".$importo, $debug);
		
					$user_id_old = $articlesOrder['Cart']['user_id'];
				} // end foreach($articlesOrders as $articlesOrder)
				
				$summaryCarts[$i]['user_id'] = $articlesOrder['Cart']['user_id'];
				$summaryCarts[$i]['importo'] = $importo;
			
				self::l($summaryCarts[$i], $debug);
			
				foreach($summaryCarts as $summaryCart) {
					
					$summaryOrderData = [];
					
					/*
					 * ctrl se esiste gia' un SummaryOrder per l'utente 
					 */
					$options = [];
					$options['conditions'] = ['SummaryOrder.organization_id' => $user->organization['Organization']['id'],
											  'SummaryOrder.order_id' => $order_id,
											  'SummaryOrder.user_id' => $summaryCart['user_id']];
					$options['recursive'] = -1;
					$ctrlSummaryOrderResults = $this->find('first', $options);
					if(empty($ctrlSummaryOrderResults)) { 

						self::l("SummaryOrder::populate_to_order order_id ".$order_id." salvo per lo user_id ".$summaryCart['user_id']." importo ".$summaryCart['importo'].' non ancora SALDATO a CASSIERE / TESORIERE', $debug);
					
						$summaryOrderData['SummaryOrder']['organization_id'] = $user->organization['Organization']['id'];
						$summaryOrderData['SummaryOrder']['delivery_id'] = $results['Order']['delivery_id'];
						$summaryOrderData['SummaryOrder']['order_id'] = $order_id;
						$summaryOrderData['SummaryOrder']['user_id'] = $summaryCart['user_id'];
						$summaryOrderData['SummaryOrder']['importo'] = $summaryCart['importo'];
						$summaryOrderData['SummaryOrder']['importo_pagato'] = '0.00';
						$summaryOrderData['SummaryOrder']['modalita'] = 'DEFINED';
						$summaryOrderData['SummaryOrder']['saldato_a'] = null;
						
						/*
						 * aggiungo eventuali dati aggregati
						*/
						if($results['Order']['typeGest']=='AGGREGATE') {		
							foreach($summaryOrderAggregateResults as $numSummaryOrderAggregate => $summaryOrderAggregateResult) {
								if($summaryOrderAggregateResult['SummaryOrderAggregate']['user_id']==$summaryOrderData['SummaryOrder']['user_id']) {
									
									self::l("SummaryOrder.importo ".$summaryOrderData['SummaryOrder']['importo'].' - sovrascrivo con i dati aggregati '.$summaryOrderAggregateResult['SummaryOrderAggregate']['importo'], $debug);
									
									$summaryOrderData['SummaryOrder']['importo'] = $summaryOrderAggregateResult['SummaryOrderAggregate']['importo'];
									break;
								}
							}
						}
					
						/*
						 * aggiungo eventuale spesa di trasporto
						*/
						if($results['Order']['hasTrasport']=='Y' && $results['Order']['trasport']>0) {					
							foreach($resultsSummaryOrderTrasport as $numSummaryOrderTrasport => $resultSummaryOrderTrasport) {
								if($resultSummaryOrderTrasport['SummaryOrderTrasport']['user_id']==$summaryOrderData['SummaryOrder']['user_id']) {
									
									self::l("SummaryOrder.importo ".$summaryOrderData['SummaryOrder']['importo'].' - dopo la spesa di trasporto '.($summaryOrderData['SummaryOrder']['importo'] + $resultSummaryOrderTrasport['SummaryOrderTrasport']['importo_trasport']), $debug);
									
									$summaryOrderData['SummaryOrder']['importo'] = ($summaryOrderData['SummaryOrder']['importo'] + $resultSummaryOrderTrasport['SummaryOrderTrasport']['importo_trasport']);
									break;
								}
							}
						} 

						/*
						 * aggiungo eventuale costo aggiuntivo
						*/
						if($results['Order']['hasCostMore']=='Y' && $results['Order']['cost_more']>0) {
							foreach($resultsSummaryOrderCostMore as $numSummaryOrderCostMore => $resultSummaryOrderCostMore) {
								if($resultSummaryOrderCostMore['SummaryOrderCostMore']['user_id']==$summaryOrderData['SummaryOrder']['user_id']) {
						
									self::l("SummaryOrder.importo ".$summaryOrderData['SummaryOrder']['importo'].' - dopo il costo aggiuntivo '.($summaryOrderData['SummaryOrder']['importo'] + $resultSummaryOrderCostMore['SummaryOrderCostMore']['importo_cost_more']), $debug);
						
									$summaryOrderData['SummaryOrder']['importo'] = ($summaryOrderData['SummaryOrder']['importo'] + $resultSummaryOrderCostMore['SummaryOrderCostMore']['importo_cost_more']);
									break;
								}
							}
						} 
							
						/*
						 * aggiungo eventuale sconto
						*/
						if($results['Order']['hasCostLess']=='Y' && $results['Order']['cost_less']>0) {
							foreach($resultsSummaryOrderCostLess as $numSummaryOrderCostLess => $resultSummaryOrderCostLess) {
								if($resultSummaryOrderCostLess['SummaryOrderCostLess']['user_id']==$summaryOrderData['SummaryOrder']['user_id']) {
										
									self::l("SummaryOrder.importo ".$summaryOrderData['SummaryOrder']['importo'].' - dopo lo sconto '.($summaryOrderData['SummaryOrder']['importo'] + $resultSummaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less']), $debug);
										
									$summaryOrderData['SummaryOrder']['importo'] = ($summaryOrderData['SummaryOrder']['importo'] + ($resultSummaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less']));
									break;
								}
							}
						}
					
						self::l($summaryOrderData, $debug);
										
						$this->create();
						$this->save($summaryOrderData);
					} // end if(empty($ctrlSummaryOrderResults)) 
					else {
						self::l("SummaryOrder::populate_to_order order_id ".$order_id." lo user_id ".$ctrlSummaryOrderResults['SummaryOrder']['user_id']." ha gia saldato l'importo ".$ctrlSummaryOrderResults['SummaryOrder']['importo']." a ".$ctrlSummaryOrderResults['SummaryOrder']['saldato_a']);
					}
				}	// end loop
				
			} // if(!empty($articlesOrders)) 
				
			//if($debug) exit;
		}
		catch (Exception $e) {
			CakeLog::write('error',$options);
			CakeLog::write('error',$e);
			return false;
		}	

		return true;
	}

	/*
	 * 	ricalcola i dati aggregati (SummaryOrder) di uno user per un dato ordine
	 *  	se lo user ha gia' dati aggregati 
	 * Non + utilizzato perche' 
	 *		se ho gia' saldato il record e' bloccato
	 *		SummaryOrder viene ricalcolato ogni volta che e' l'ordine inviato a Cassiere / Tesoriere 
	 */
	public function ricalcolaPerSingoloUtente($user, $order_id, $user_id, $debug=false) {
	
		return true;
		
		
		// $debug=true;
	
		 App::import('Model', 'Order');
		 $Order = new Order;
				
		try {	
			/*
			 * ctrl se ci sono gia' dati aggregati per l'ordine (non per lo user perche' potebbe essere nuovo!)
			 */
			$summaryOrdersResults = $this->select_to_order($user, $order_id);
			if(!empty($summaryOrdersResults)) {
			
				$sql = "DELETE FROM ".Configure::read('DB.prefix')."summary_orders WHERE
							organization_id = ".(int)$user->organization['Organization']['id']."
							AND order_id = ".(int)$order_id."
							AND user_id = ".(int)$user_id;
				self::l('SummaryOrder::ricalcolaPerSingoloUtente() CANCELLO dati aggregati dello user '.$sql);
				$result = $this->query($sql);		


				$this->populate_to_order($user, $order_id, $user_id);
			
				/*
				 * ricalcolo totale importo dell'ordine
				 */
				 $tot_importo = $Order->getTotImporto($user, $order_id);
				
				$sql = "UPDATE
					`".Configure::read('DB.prefix')."orders`
				SET 
					tot_importo = ".$tot_importo." ,  
					modified = '".date('Y-m-d H:i:s')."'
				WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					and id = ".(int)$order_id; 
				CakeLog::write('debug','AGGIORNO order.tot_importo '.$sql);
				$result = $this->query($sql);
				
			} // if(!empty($summaryOrdersResults))	
			else {
				CakeLog::write('debug','SummaryOrder::ricalcolaPerSingoloUtente() per lo user '.$user_id.' e order_id '.$order_id.' non ci sono dati aggregati (SummaryOrders)');
			}			
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}	
	}  
	
	public $validate = array(
		'user_id' => array(
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
	);
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
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