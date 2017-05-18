<?php
App::uses('AppModel', 'Model');

class SummaryOrderTrasport extends AppModel {
  
	/* 
	 *  estrae tutti i summarOrderTrasport di un ordine
	 */
	public function select_to_order($user, $order_id, $user_id=0, $debug=false) {
		$sql = "SELECT 
					SummaryOrderTrasport.*,
					User.id, User.name, User.username, User.email   
				FROM 
					".Configure::read('DB.prefix')."summary_order_trasports as SummaryOrderTrasport, 
					".Configure::read('DB.portalPrefix')."users as User 
				WHERE
					SummaryOrderTrasport.organization_id = ".(int)$user->organization['Organization']['id']." 
					and User.organization_id = ".(int)$user->organization['Organization']['id']." 
					and SummaryOrderTrasport.order_id = ".(int)$order_id."
					and SummaryOrderTrasport.user_id = User.id ";
		if($user_id>0) $sql .= " and User.id = ".$user_id;
		 $sql .= " ORDER BY SummaryOrderTrasport.user_id";
		if($debug) echo '<br />'.$sql;
		try {
			$result = $this->query($sql);
		}		catch (Exception $e) {			CakeLog::write('error',$sql);			CakeLog::write('error',$e);		}
		return $result;
	}

	/*
	 *  calcolare il totale degli importi di un ordine
	*/
	public function select_totale_importo_to_order($user, $order_id, $debug=false) {
		$sql = "SELECT
					sum(importo) as totale_importo
				FROM
					".Configure::read('DB.prefix')."summary_order_trasports as SummaryOrderTrasport 
						WHERE
						organization_id = ".(int)$user->organization['Organization']['id']."
						and order_id = ".(int)$order_id."
				ORDER BY user_id";
		if($debug) echo '<br />'.$sql;
		try {			$result = current($this->query($sql));		}		catch (Exception $e) {			CakeLog::write('error',$sql);			CakeLog::write('error',$e);		}		
		return $result;
	}

	/*
	 *  calcolare il totale degli importi suddivisi per user: per Order.isOrderValidateToTrasmit
	 *		se ZERO, il referente ha impostato l'importo ma non l'ha suddiviso
	*/
	public function select_totale_importo_trasport($user, $order_id, $debug=false) {
		
		$result = 0;
		
		$sql = "SELECT
					sum(importo_trasport) as totale_importo
				FROM
					".Configure::read('DB.prefix')."summary_order_trasports as SummaryOrderTrasport 
						WHERE
						organization_id = ".(int)$user->organization['Organization']['id']."
						and order_id = ".(int)$order_id."
				ORDER BY user_id";
		if($debug) echo '<br />'.$sql;
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
		public function delete_to_order($user, $order_id, $debug=false) {		$sql = "DELETE				FROM					".Configure::read('DB.prefix')."summary_order_trasports				WHERE					organization_id = ".(int)$user->organization['Organization']['id']."					and order_id = ".(int)$order_id;		if($debug) echo '<br />'.$sql;
		try {			$result = $this->query($sql);		}		catch (Exception $e) {			CakeLog::write('error',$sql);			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;		}	}
		
	public function delete_trasport_to_order($user, $order_id, $debug=false) {
		
		$this->delete_to_order($user, $order_id, $debug);
		
		$sql = "UPDATE					".Configure::read('DB.prefix')."orders 
				SET 
					trasport = '0.00',
					trasport_type = null,
					modified = '".date('Y-m-d H:i:s')."'					WHERE					organization_id = ".(int)$user->organization['Organization']['id']."					and id = ".(int)$order_id;		if($debug) echo '<br />'.$sql;
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
	 *  per valorizzare SummaryOrderTrasport.importo estraggo 
	 * 		- tutti gli acquisti di un ordine
	 *      - eventuali aggregazioni SummaryOrder
	 */
	public function populate_to_order($user, $order_id, $debug= false) {
		
		try {
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
		
			/*
			 *  estraggo eventuali aggragazioni SummaryOrder
 			 */
			App::import('Model', 'SummaryOrder');
			$SummaryOrder = new SummaryOrder;
			
			$resultsSummaryOrder = $SummaryOrder->select_to_order($user, $order_id); 
			 
			if(!empty($articlesOrders)) {
			
				$summaryCarts = array();
				$user_id_old=0;
				$importo=0;
				$peso=0; // indica la somma di tutti i gr o lt degli articoli acquistati, serve per il calcolo del trasporto a peso, se 0 ci sono UM diverse gr, hg o kg
				$peso_valido=true; // si calcola il peso totale solo se i pesi sono a gr, hg o kg se no 0
				$i=0;
				foreach($articlesOrders as $articlesOrder) {
			
					if($debug) echo "<br />$i) tratto l'utente ".$articlesOrder['User']['name'].' ('.$articlesOrder['Cart']['user_id'].") (precedente $user_id_old)";
			
					if($user_id_old>0 && $articlesOrder['Cart']['user_id']!=$user_id_old) {
						if($debug) echo "<br />$i) utente diverso => memorizzo in ";
			
				 	   /*
					 	* per l'UTENTE trattato ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
					 	*/
						foreach($resultsSummaryOrder as $numSummaryOrder => $resultSummaryOrder) {
							if($resultSummaryOrder['SummaryOrder']['user_id']==$user_id_old) {
								$importo = $resultSummaryOrder['SummaryOrder']['importo'];
								if($debug) echo "<br />$i)     importo dell'utente ".$user_id_old." aggregato in SummaryOrder: $importo";
								break;
							}
						}
					
						$summaryCarts[$i]['user_id'] = $user_id_old;
						$summaryCarts[$i]['importo'] = $importo;
						$summaryCarts[$i]['peso'] = $peso;
						$importo = 0;
						$peso = 0;
			
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

				
					/*					 * peso, somma di tutti i gr o lt degli articoli acquistati					*/
					if(!empty($articlesOrder['Cart']['qta_forzato']))						$qta = $articlesOrder['Cart']['qta_forzato'];					else						$qta = $articlesOrder['Cart']['qta'];											/*					 * porto tutto al grammo					*/					if($articlesOrder['Article']['um']=='KG')						$qta_article = ($articlesOrder['Article']['qta'] * 1000);					else						if($articlesOrder['Article']['um']=='HG')						$qta_article = ($articlesOrder['Article']['qta'] * 100);					else						if($articlesOrder['Article']['um']=='GR')						$qta_article = $articlesOrder['Article']['qta'];					else						$peso_valido=false;											$peso += ($qta * $qta_article);
					if($debug) echo "<br />$i)     peso dell'utente ".$articlesOrder['Cart']['user_id'].": $peso";					
					$user_id_old = $articlesOrder['Cart']['user_id'];
				} // end foreach($articlesOrders as $articlesOrder)
				
				$summaryCarts[$i]['user_id'] = $articlesOrder['Cart']['user_id'];
				$summaryCarts[$i]['importo'] = $importo;
				$summaryCarts[$i]['peso'] = $peso;
			
			
				if($debug)  {
					echo "<br />$i) ultimo utente => memorizzo in ";
					echo "<pre>";
					print_r($summaryCarts[$i]);
					echo "</pre>";
				}
			
			
				$summaryOrderTrasportData = array();
				foreach($summaryCarts as $summaryCart) {
					$summaryOrderTrasportData['SummaryOrderTrasport']['organization_id'] = $user->organization['Organization']['id'];
					$summaryOrderTrasportData['SummaryOrderTrasport']['order_id'] = $order_id;
					$summaryOrderTrasportData['SummaryOrderTrasport']['user_id'] = $summaryCart['user_id'];
					$summaryOrderTrasportData['SummaryOrderTrasport']['importo'] = $summaryCart['importo'];
					$summaryOrderTrasportData['SummaryOrderTrasport']['peso'] = $summaryCart['peso'];
					
					if($debug)  {
						echo "<pre>salvo il record ";
						print_r($summaryOrderTrasportData);
						echo "</pre>";
				    }
										
					$this->create();
					$this->save($summaryOrderTrasportData);
				}	
				
				/*
				 * se anche un solo valore UM era diverso da GR, HG, KG non posso fare il calcolo trasporto a peso
				 */
				if(!$peso_valido) {
					$sql = "UPDATE							".Configure::read('DB.prefix')."summary_order_trasports						SET							peso = 0						WHERE							organization_id = ".(int)$user->organization['Organization']['id']."							and order_id = ".(int)$order_id;					$result = $this->query($sql);
					
					if($debug)  {						echo "<br />c'e' anche un solo valore UM era diverso da GR, HG, KG non posso fare il calcolo trasporto a peso ".$sql;					}				}				
			} // if(!empty($articlesOrders)) 
				
			if($debug) exit;
		}		catch (Exception $e) {			CakeLog::write('error',$sql);			CakeLog::write('error',$e);		}			
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
			'conditions' => 'User.organization_id = SummaryOrderTrasport.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = SummaryOrderTrasport.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => 'Order.organization_id = SummaryOrderTrasport.organization_id',
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
				if(isset($val['SummaryOrderTrasport']['importo'])) {
					$results[$key]['SummaryOrderTrasport']['importo_'] = number_format($val['SummaryOrderTrasport']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));					$results[$key]['SummaryOrderTrasport']['importo_e'] = $results[$key]['SummaryOrderTrasport']['importo_'].' &euro;';				}
				
				if(isset($val['SummaryOrderTrasport']['importo_trasport'])) {
					$results[$key]['SummaryOrderTrasport']['importo_trasport_'] = number_format($val['SummaryOrderTrasport']['importo_trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryOrderTrasport']['importo_trasport_e'] = $results[$key]['SummaryOrderTrasport']['importo_trasport_'].' &euro;';
				}				
			}
		}
		return $results;
	}	
}