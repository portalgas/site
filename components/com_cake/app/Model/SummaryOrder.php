<?php
App::uses('AppModel', 'Model');

class SummaryOrder extends AppModel {
  
	/* 
	 *  estrae tutti i summarOrder di un ordine
	 */
	public function select_to_order($user, $order_id, $user_id=0) {
	
		$debug = true;
		
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
		// if($debug) CakeLog::write('debug','SummaryOrder::select_to_order() '.$sql);
		try {
			$result = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
		return $result;
	}

	/*
	 *  calcolare il totale degli importi di un ordine
	*/
	public function select_totale_importo_to_order($user, $order_id) {
		$sql = "SELECT
					sum(importo) as totale_importo
				FROM
					".Configure::read('DB.prefix')."summary_orders as SummaryOrder
				WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					AND order_id = ".(int)$order_id."
				ORDER BY user_id";
		// echo '<br />'.$sql;
		try {
			$result = current($this->query($sql));
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}		
		return $result;
	}

	public function delete_to_order($user, $order_id, $debug = false) {
		$sql = "DELETE
				FROM
					".Configure::read('DB.prefix')."summary_orders
				WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					AND order_id = ".(int)$order_id;
		if($debug) echo '<br />'.$sql;
		try {
			$result = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}
	}
	
	/*
	 * tolgo l'importo del trasporto da SummaryOrder.importo perche' il referente ha  
	 *		modificato l'importo del trasporto => dopo aggiorna_trasporto
	 * 		cancellato l'importo del trasporto
	 */
	public function delete_trasport($user, $order_id, $debug = false) {
		
		/*
		 * estraggo gli importi del trasporto spalmati per ogni utente
		 */
		App::import('Model', 'SummaryOrderTrasport');
		$SummaryOrderTrasport = new SummaryOrderTrasport;
		
		$options = array();
		$options['conditions'] = array('SummaryOrderTrasport.organization_id' => $user->organization['Organization']['id'],
									   'SummaryOrderTrasport.order_id' => $order_id);
		$options['recursive'] = -1;
		$results = $SummaryOrderTrasport->find('all', $options);

		if(!empty($results))
			foreach($results as $result) {
				$user_id = $result['SummaryOrderTrasport']['user_id'];
				$importo_trasport_ = $result['SummaryOrderTrasport']['importo_trasport_'];
				
				if($importo_trasport_ != '0,00') {
					$sql = "UPDATE ".Configure::read('DB.prefix')."summary_orders
							SET
							importo = (importo - ".$this->importoToDatabase($importo_trasport_).")
						WHERE
							organization_id = ".(int)$user->organization['Organization']['id']."
							AND user_id = ".(int)$user_id."
							AND order_id = ".(int)$order_id;
					if($debug)  echo '<br />'.$sql;
					try {
						$result = $this->query($sql);
					}
					catch (Exception $e) {
						CakeLog::write('error',$sql);
						CakeLog::write('error',$e);
						if($debug) echo '<br />'.$e;
					}
				}
			}	
	}

	/*
	 * tolgo l'importo del costo aggiuntivo da SummaryOrder.importo perche' il referente ha
	*		modificato l'importo del costo aggiuntivo => dopo aggiorna_trasporto
	* 		cancellato l'importo del costo aggiuntivo
	*/
	public function delete_cost_more($user, $order_id, $debug = false) {
	
		/*
		 * estraggo gli importi del costo aggiuntivo spalmati per ogni utente
		*/
		App::import('Model', 'SummaryOrderCostMore');
		$SummaryOrderCostMore = new SummaryOrderCostMore;
	
		$options = array();
		$options['conditions'] = array('SummaryOrderCostMore.organization_id' => $user->organization['Organization']['id'],
				'SummaryOrderCostMore.order_id' => $order_id);
		$options['recursive'] = -1;
		$results = $SummaryOrderCostMore->find('all', $options);
	
		if(!empty($results))
		foreach($results as $result) {
			$user_id = $result['SummaryOrderCostMore']['user_id'];
			$importo_cost_more_ = $result['SummaryOrderCostMore']['importo_cost_more_'];
	
			if($importo_cost_more_ != '0,00') {
				$sql = "UPDATE ".Configure::read('DB.prefix')."summary_orders
						SET
							importo = (importo - ".$this->importoToDatabase($importo_cost_more_).")
						WHERE
							organization_id = ".(int)$user->organization['Organization']['id']."
							AND user_id = ".(int)$user_id."
							AND order_id = ".(int)$order_id;
				if($debug)  echo '<br />'.$sql;
				try {
					$result = $this->query($sql);
				}
				catch (Exception $e) {
					CakeLog::write('error',$sql);
					CakeLog::write('error',$e);
					if($debug) echo '<br />'.$e;
				}
			}
		}
	}

	/*
	 * tolgo l'importo dello sconto da SummaryOrder.importo perche' il referente ha
	*		modificato l'importo dello sconto => dopo aggiorna_trasporto
	* 		cancellato l'importo dello sconto
	*/
	public function delete_cost_less($user, $order_id, $debug = false) {
	
		/*
		 * estraggo gli importi dello sconto spalmati per ogni utente
		*/
		App::import('Model', 'SummaryOrderCostLess');
		$SummaryOrderCostLess = new SummaryOrderCostLess;
	
		$options = array();
		$options['conditions'] = array('SummaryOrderCostLess.organization_id' => $user->organization['Organization']['id'],
				'SummaryOrderCostLess.order_id' => $order_id);
		$options['recursive'] = -1;
		$results = $SummaryOrderCostLess->find('all', $options);
	
		if(!empty($results))
		foreach($results as $result) {
			$user_id = $result['SummaryOrderCostLess']['user_id'];
			$importo_cost_less_ = $result['SummaryOrderCostLess']['importo_cost_less_'];
	
			if($importo_cost_less_ != '0,00') {
				
				/*
				 * importo NEGATIVO perche SCONTO
				 */
				$importo_cost_less = (-1 * $importo_cost_less);
				$sql = "UPDATE ".Configure::read('DB.prefix')."summary_orders
						SET
							importo = (importo + ".$this->importoToDatabase($importo_cost_less_).")
						WHERE
							organization_id = ".(int)$user->organization['Organization']['id']."
							AND user_id = ".(int)$user_id."
							AND order_id = ".(int)$order_id;
				if($debug)  echo '<br />'.$sql;
				try {
					$result = $this->query($sql);
				}
				catch (Exception $e) {
					CakeLog::write('error',$sql);
					CakeLog::write('error',$e);
					if($debug) echo '<br />'.$e;
				}
			}
		}
	}
	
	/*
	 * se user_id valorizzato popolo i dati aggregati dello specifico user 
	 *		per ex SummaryOrder::admin_ajax_summary_orders_ricalcola()
	 */
	public function populate_to_order($user, $order_id, $user_id=0, $debug = false) {
	
		try {
			/*
			 * ctrl che l'ordine sia in stato valido per creare i dati aggragati
			 */
			App::import('Model', 'Order');
			$Order = new Order;
			
			$optinos = array();
			$options['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
											'Order.id' => $order_id);
			$options['recursive'] = -1;
			$options['fields'] = array('delivery_id', 'hasTrasport', 'trasport', 'hasCostMore', 'cost_more', 'hasCostLess', 'cost_less', 'state_code');
			$results = $Order->find('first', $options);
			
			/*
			 * estraggo eventuale spesa di trasporto
			 */
			if($results['Order']['hasTrasport']=='Y' && $results['Order']['trasport']>0) {
				
				if($debug) echo "<br />Ordine ha le spese di traporto ".$results['Order']['trasport'];
				App::import('Model', 'SummaryOrderTrasport');
				$SummaryOrderTrasport = new SummaryOrderTrasport;
				
				$resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($user, $order_id);				
			}
			else
				if($debug) echo "<br />Ordine NON ha le spese di traporto ".$results['Order']['trasport'];
				
			/*
			 * estraggo eventuale costo aggiuntivo
			*/
			if($results['Order']['hasCostMore']=='Y' && $results['Order']['cost_more']>0) {
			
				if($debug) echo "<br />Ordine ha costo aggiuntivo ".$results['Order']['cost_more'];
				App::import('Model', 'SummaryOrderCostMore');
				$SummaryOrderCostMore = new SummaryOrderCostMore;
			
				$resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($user, $order_id);
			}
			else
				if($debug) echo "<br />Ordine NON ha costo aggiuntivo ".$results['Order']['cost_more'];


			/*
			 * estraggo eventuale sconto
			*/
			if($results['Order']['hasCostLess']=='Y' && $results['Order']['cost_less']>0) {
					
				if($debug) echo "<br />Ordine ha uno sconto ".$results['Order']['cost_less'];
				App::import('Model', 'SummaryOrderCostLess');
				$SummaryOrderCostLess = new SummaryOrderCostLess;
					
				$resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($user, $order_id);
			}
			else
				if($debug) echo "<br />Ordine NON ha uno sconto ".$results['Order']['cost_less'];
								
			/*
			 * estraggo tutti gli acquisti in base all'ordine
			* ottienti dati Article, ArticlesOrder, Cart
			* */
			App::import('Model', 'ArticlesOrder');
			$ArticlesOrder = new ArticlesOrder;
			$conditions = array('Order.id' => $order_id,
								'Order.organization_id' => $user->organization['Organization']['id'],
								'Cart.deleteToReferent' => 'N');
			if($user_id>0)
				$conditions += array('Cart.user_id' => $user_id);
			$orderBy = array('User' => 'User.id');
			$articlesOrders = $ArticlesOrder->getArticoliAcquistatiDaUtenteInOrdine($user, $conditions, $orderBy);
		
			if(!empty($articlesOrders)) {
			
				$summaryCarts = array();
				$user_id_old=0;
				$importo=0;
				$i=0;
				foreach($articlesOrders as $articlesOrder) {
			
					if($debug) echo "<br />$i) tratto l'utente ".$articlesOrder['User']['name'].' ('.$articlesOrder['Cart']['user_id'].") (precedente $user_id_old)";
			
					if($user_id_old>0 && $articlesOrder['Cart']['user_id']!=$user_id_old) {
						if($debug) echo "<br />$i) utente diverso => memorizzo in ";
			
						$summaryCarts[$i]['user_id'] = $user_id_old;
						$summaryCarts[$i]['importo'] = $importo;
						$importo = 0;
				
						if($debug)  {
							echo "<pre>";
							print_r($summaryCarts[$i]);
							echo "</pre>";
						}
			
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
			
					if($debug) echo "<br />$i)     importo dell'utente ".$articlesOrder['Cart']['user_id'].": $importo";
		
					$user_id_old = $articlesOrder['Cart']['user_id'];
				} // end foreach($articlesOrders as $articlesOrder)
				
				$summaryCarts[$i]['user_id'] = $articlesOrder['Cart']['user_id'];
				$summaryCarts[$i]['importo'] = $importo;
			
			
				if($debug)  {
					echo "<br />$i) ultimo utente => memorizzo in ";
					echo "<pre>";
					print_r($summaryCarts[$i]);
					echo "</pre>";
				}
			
			
				$summaryOrderData = array();
				foreach($summaryCarts as $summaryCart) {
					$summaryOrderData['SummaryOrder']['organization_id'] = $user->organization['Organization']['id'];
					$summaryOrderData['SummaryOrder']['delivery_id'] = $results['Order']['delivery_id'];
					$summaryOrderData['SummaryOrder']['order_id'] = $order_id;
					$summaryOrderData['SummaryOrder']['user_id'] = $summaryCart['user_id'];
					$summaryOrderData['SummaryOrder']['importo'] = $summaryCart['importo'];
					$summaryOrderData['SummaryOrder']['importo_pagato'] = '0.00';
					$summaryOrderData['SummaryOrder']['modalita'] = 'DEFINED';
					
					/*
					 * aggiungo eventuale spesa di trasporto
					*/
					if($results['Order']['hasTrasport']=='Y' && $results['Order']['trasport']>0) {					
						foreach($resultsSummaryOrderTrasport as $numSummaryOrderTrasport => $resultSummaryOrderTrasport) {
							if($resultSummaryOrderTrasport['SummaryOrderTrasport']['user_id']==$summaryOrderData['SummaryOrder']['user_id']) {
								
								if($debug) echo "<br />SummaryOrder.importo ".$summaryOrderData['SummaryOrder']['importo'].' - dopo la spesa di trasporto '.($summaryOrderData['SummaryOrder']['importo'] + $resultSummaryOrderTrasport['SummaryOrderTrasport']['importo_trasport']);
								
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
					
								if($debug) echo "<br />SummaryOrder.importo ".$summaryOrderData['SummaryOrder']['importo'].' - dopo il costo aggiuntivo '.($summaryOrderData['SummaryOrder']['importo'] + $resultSummaryOrderCostMore['SummaryOrderCostMore']['importo_cost_more']);
					
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
									
								if($debug) echo "<br />SummaryOrder.importo ".$summaryOrderData['SummaryOrder']['importo'].' - dopo lo sconto '.($summaryOrderData['SummaryOrder']['importo'] + $resultSummaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less']);
									
								$summaryOrderData['SummaryOrder']['importo'] = ($summaryOrderData['SummaryOrder']['importo'] + ($resultSummaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less']));
								break;
							}
						}
					}

					
					if($debug)  {
						echo "<pre>salvo il record ";
						print_r($summaryOrderData);
						echo "</pre>";
				    }
									
					$this->create();
					$this->save($summaryOrderData);
				}	
				
			} // if(!empty($articlesOrders)) 
				
			//if($debug) exit;
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}			
	}

	/*
	 * 	ricalcola i dati aggregati (SummaryOrder) di uno user per un dato ordine
	 *  	se lo user ha gia' dati aggregati 
	 */
	public function ricalcolaPerSingoloUtente($user, $order_id, $user_id, $debug=false) {
	
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
				if($debug) CakeLog::write('debug','SummaryOrder::ricalcolaPerSingoloUtente() CANCELLO dati aggregati dello user '.$sql);
				$result = $this->query($sql);		


				$this->populate_to_order($user, $order_id, $user_id);
			
				/*
				 * ricalcolo totale importo dell'ordine
				 */
				 $tot_importo = $Order->getTotImporto($user, $order_id);
				 
				/* 
				 *  bugs float: i float li converte gia' con la virgola!  li riporto flaot
				 */
				if(strpos($tot_importo,',')!==false)  $tot_importo = str_replace(',','.',$tot_importo);					 
				
				$sql = "UPDATE
					`".Configure::read('DB.prefix')."orders`
				SET 
					tot_importo = ".$tot_importo." ,  
					modified = '".date('Y-m-d H:i:s')."'
				WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					and id = ".(int)$order_id;
				if($debug) CakeLog::write('debug','AGGIORNO order.tot_importo '.$sql);
				$result = $this->query($sql);
				
			} // if(!empty($summaryOrdersResults))	
			else {
				if($debug) CakeLog::write('debug','SummaryOrder::ricalcolaPerSingoloUtente() per lo user '.$user_id.' e order_id '.$order_id.' non ci sono dati aggregati (SummaryOrders)');
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
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'delivery_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'order_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
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
	public function beforeSave($options = array()) {
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