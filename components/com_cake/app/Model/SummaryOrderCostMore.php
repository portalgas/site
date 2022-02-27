<?php
App::uses('AppModel', 'Model');

class SummaryOrderCostMore extends AppModel {
  
	/* 
	 *  estrae tutti i summarOrderCostMore di un ordine
	 */
	public function select_to_order($user, $order_id, $user_id=0, $debug=false) {
		$sql = "SELECT 
					SummaryOrderCostMore.*,
					User.id, User.name, User.username, User.email   
				FROM 
					".Configure::read('DB.prefix')."summary_order_cost_mores as SummaryOrderCostMore, 
					".Configure::read('DB.portalPrefix')."users as User 
				WHERE
					SummaryOrderCostMore.organization_id = ".(int)$user->organization['Organization']['id']." 
					and User.organization_id = ".(int)$user->organization['Organization']['id']." 
					and SummaryOrderCostMore.order_id = ".(int)$order_id."
					and SummaryOrderCostMore.user_id = User.id ";
		if($user_id>0) $sql .= " and User.id = ".$user_id;
		 $sql .= " ORDER BY SummaryOrderCostMore.user_id";
		self::d($sql, $debug);
		try {
			$result = $this->query($sql);
			
			if($user_id>0 && !empty($result))
				$result = current($result);			
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
	public function select_totale_importo_to_order($user, $order_id, $debug=false) {
				
		$options = [];
		$options['conditions'] = ['SummaryOrderCostMore.organization_id' => $user->organization['Organization']['id'],
								  'SummaryOrderCostMore.order_id' => $order_id];
		$options['fields'] = ['sum(SummaryOrderCostMore.importo) as totale_importo'];
		$options['recursive'] = -1;
		
		$results = $this->find('first', $options);
		$results = current($results);
		if(empty($results['totale_importo'])) 
			$results = 0;
		else 
			$results = $results['totale_importo'];
						
		self::l("SummaryOrderCostMore::select_totale_importo_to_order order_id ".$order_id." totale_importo ".$results);
		
		return $results;
	}

	/*
	 *  calcolare il totale degli importi suddivisi per user: per OrderLifeCycle._isOrderValidateToTrasmit
	 *		se ZERO, il referente ha impostato l'importo ma non l'ha suddiviso
	*/
	public function select_totale_importo_cost_more($user, $order_id, $debug=false) {
		
		$result = 0;
		
		$sql = "SELECT
					sum(importo_cost_more) as totale_importo
				FROM
					".Configure::read('DB.prefix')."summary_order_cost_mores as SummaryOrderCostMore 
						WHERE
						organization_id = ".(int)$user->organization['Organization']['id']."
						and order_id = ".(int)$order_id."
				ORDER BY user_id";
		self::d($sql, $debug);
		try {
			$result = current($this->query($sql));
			if(isset($result[0]['totale_importo']))
				$result = $result[0]['totale_importo'];
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}		
		return $result;
	}
	
	/*
	 * escludo i gasisti che hanno gia' saldato (SummaryOrder.saldato_a != null)
	 */	
	public function delete_to_order($user, $order_id, $debug=false) {
		
		$user_da_esludere_ids = []; // escludo perche' son gia' saldati
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
				
		$options = [];
		$options['conditions'] = ['SummaryOrder.organization_id' => $user->organization['Organization']['id'],
								  'SummaryOrder.order_id' => $order_id,
								  'SummaryOrder.saldato_a != ' => null];
		$options['recursive'] = -1;
		$summaryOrderResults = $SummaryOrder->find('all', $options);
		self::d($summaryOrderResults, $debug);
	   	if (!empty($summaryOrderResults)) {
			foreach($summaryOrderResults as $summaryOrderResult) {
				array_push($user_da_esludere_ids, $summaryOrderResult['SummaryOrder']['user_id']);				
			}
	   	}   

		$options = [];
		$options['conditions'] = ['SummaryOrderCostMore.organization_id' => $user->organization['Organization']['id'],
								  'SummaryOrderCostMore.order_id' => $order_id];
		if(!empty($user_da_esludere_ids))
			$options['conditions'] += ['NOT' => ['SummaryOrderCostMore.user_id' => $user_da_esludere_ids]];
		self::d($options, $debug); 
		$this->unbindModel(['belongsTo' => ['Delivery']]);
		$this->deleteAll($options['conditions'], false);
	
		return true;
	}
		
	public function delete_importo_to_order($user, $order_id, $debug=false) {
		
		$this->delete_to_order($user, $order_id, $debug);
		
		$sql = "UPDATE
					".Configure::read('DB.prefix')."orders 
				SET 
					cost_more = '0.00',
					cost_more_type = null,
					modified = '".date('Y-m-d H:i:s')."'	
				WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					and id = ".(int)$order_id;
		self::d($sql, $debug);
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
	 *  per valorizzare SummaryOrderCostMore.importo estraggo 
	 * 		- tutti gli acquisti di un ordine
	 *      - eventuali aggregazioni SummaryOrderAggregate
	 */
	public function populate_to_order($user, $order_id, $debug= false) {
		
		try {
			App::import('Model', 'SummaryOrderAggregate');
			$SummaryOrderAggregate = new SummaryOrderAggregate;
					
			/*
			 * estraggo tutti gli acquisti in base all'ordine
			* ottienti dati Article, ArticlesOrder, Cart
			* */
			App::import('Model', 'ArticlesOrder');
			$ArticlesOrder = new ArticlesOrder;
			
			$conditions = array('Order.id' => $order_id,
								'Order.organization_id' => $user->organization['Organization']['id'],
								'Cart.deleteToReferent' => 'N');
			$orderBy = array('User' => 'User.id');
			$articlesOrders = $ArticlesOrder->getArticoliAcquistatiDaUtenteInOrdine($user, $conditions, $orderBy);
		 
			if(!empty($articlesOrders)) {
			
				$summaryCarts = [];
				$user_id_old=0;
				$importo=0;
				$peso=0; // indica la somma di tutti i gr o lt degli articoli acquistati, serve per il calcolo del cost_moreo a peso, se 0 ci sono UM diverse gr, hg o kg
				$peso_valido=true; // si calcola il peso totale solo se i pesi sono a gr, hg o kg se no 0
				$i=0;
				foreach($articlesOrders as $articlesOrder) {
			
					if($debug) echo "<br />$i) tratto l'utente ".$articlesOrder['User']['name'].' ('.$articlesOrder['Cart']['user_id'].") (precedente $user_id_old)";
			
					if($user_id_old>0 && $articlesOrder['Cart']['user_id']!=$user_id_old) {
						if($debug) echo "<br />$i) utente diverso => memorizzo in ";

					 	/*
					 	* per l'UTENTE trattato ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
					 	*/
						$summaryOrderAggregateResults = $SummaryOrderAggregate->select_to_order($user, $order_id, $user_id_old);
						self::d($summaryOrderAggregateResults, $debug);
						if(!empty($summaryOrderAggregateResults)) {
							$importo = $summaryOrderAggregateResults[0]['SummaryOrderAggregate']['importo'];
							if($debug) echo "<br />$i)     importo dell'utente ".$user_id_old." aggregato in SummaryOrderAggregate: $importo";
						}
								
						$summaryCarts[$i]['user_id'] = $user_id_old;
						$summaryCarts[$i]['importo'] = $importo;
						$summaryCarts[$i]['peso'] = $peso;
						$importo = 0;
						$peso = 0;
			
						self::d($summaryCarts[$i], $debug);
						
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
					/*
					 * peso, somma di tutti i gr o lt degli articoli acquistati
					*/
					if(!empty($articlesOrder['Cart']['qta_forzato']))
						$qta = $articlesOrder['Cart']['qta_forzato'];
					else
						$qta = $articlesOrder['Cart']['qta'];
						
					/*
					 * porto tutto al grammo
					*/
					if($articlesOrder['Article']['um']=='KG')
						$qta_article = ($articlesOrder['Article']['qta'] * 1000);
					else
						if($articlesOrder['Article']['um']=='HG')
						$qta_article = ($articlesOrder['Article']['qta'] * 100);
					else
						if($articlesOrder['Article']['um']=='GR')
						$qta_article = $articlesOrder['Article']['qta'];
					else
						$peso_valido=false;
						
					$peso += ($qta * $qta_article);
					if($debug) echo "<br />$i)     peso dell'utente ".$articlesOrder['Cart']['user_id'].": $peso";
					
					$user_id_old = $articlesOrder['Cart']['user_id'];
				} // end foreach($articlesOrders as $articlesOrder)

		 	   /*
			 	* ultimo user
			 	*
			 	* per l'UTENTE trattato ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
			 	*/
				$summaryOrderAggregateResults = $SummaryOrderAggregate->select_to_order($user, $order_id, $user_id_old);
				self::d($summaryOrderAggregateResults, $debug);
				if(!empty($summaryOrderAggregateResults)) {
					$importo = $summaryOrderAggregateResults[0]['SummaryOrderAggregate']['importo'];
					if($debug) echo "<br />$i)     importo dell'utente ".$user_id_old." aggregato in SummaryOrderAggregate: $importo";
				}
								
				$summaryCarts[$i]['user_id'] = $articlesOrder['Cart']['user_id'];
				$summaryCarts[$i]['importo'] = $importo;
				$summaryCarts[$i]['peso'] = $peso;
			
			
				if($debug)  {
					echo "<br />$i) ultimo utente => memorizzo in ";
					echo "<pre>";
					print_r($summaryCarts[$i]);
					echo "</pre>";
				}
			
			
				$summaryOrderCostMoreData = [];
				foreach($summaryCarts as $summaryCart) {
					
					/*
					 * ctrl se esiste gia' un SummaryOrderCostMore per l'utente
					 */
					$options = [];
					$options['conditions'] = ['SummaryOrderCostMore.organization_id' => $user->organization['Organization']['id'],
											  'SummaryOrderCostMore.order_id' => $order_id,
											  'SummaryOrderCostMore.user_id' => $summaryCart['user_id']];
					$options['recursive'] = -1;
					$ctrlSummaryOrderCostMoreResults = $this->find('first', $options);
					if(empty($ctrlSummaryOrderCostMoreResults)) {
						
						$summaryOrderCostMoreData['SummaryOrderCostMore']['organization_id'] = $user->organization['Organization']['id'];
						$summaryOrderCostMoreData['SummaryOrderCostMore']['order_id'] = $order_id;
						$summaryOrderCostMoreData['SummaryOrderCostMore']['user_id'] = $summaryCart['user_id'];
						$summaryOrderCostMoreData['SummaryOrderCostMore']['importo'] = $summaryCart['importo'];
						$summaryOrderCostMoreData['SummaryOrderCostMore']['peso'] = $summaryCart['peso'];
						
						if($debug)  {
							echo "<pre>salvo il record ";
							print_r($summaryOrderCostMoreData);
							echo "</pre>";
						}
											
						$this->create();
						$this->save($summaryOrderCostMoreData);
						
					} // end if(empty($ctrlSummaryOrderCostMoreResults))
				} // loop	
				
				/*
				 * se anche un solo valore UM era diverso da GR, HG, KG non posso fare il calcolo cost_moreo a peso
				 */
				if(!$peso_valido) {
					$sql = "UPDATE
							".Configure::read('DB.prefix')."summary_order_cost_mores
						SET
							peso = 0
						WHERE
							organization_id = ".(int)$user->organization['Organization']['id']."
							and order_id = ".(int)$order_id;
					$result = $this->query($sql);
					
					if($debug)  {
						echo "<br />c'e' anche un solo valore UM era diverso da GR, HG, KG non posso fare il calcolo cost_moreo a peso ".$sql;
					}
				}				
			} // if(!empty($articlesOrders)) 
				
			if($debug) exit;
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
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
			'conditions' => 'User.organization_id = SummaryOrderCostMore.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = SummaryOrderCostMore.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => 'Order.organization_id = SummaryOrderCostMore.organization_id',
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
				if(isset($val['SummaryOrderCostMore']['importo'])) {
					$results[$key]['SummaryOrderCostMore']['importo_'] = number_format($val['SummaryOrderCostMore']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryOrderCostMore']['importo_e'] = $results[$key]['SummaryOrderCostMore']['importo_'].' &euro;';
				}
				
				if(isset($val['SummaryOrderCostMore']['importo_cost_more'])) {
					$results[$key]['SummaryOrderCostMore']['importo_cost_more_'] = number_format($val['SummaryOrderCostMore']['importo_cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryOrderCostMore']['importo_cost_more_e'] = $results[$key]['SummaryOrderCostMore']['importo_cost_more_'].' &euro;';
				}				
			}
		}
		return $results;
	}	
}