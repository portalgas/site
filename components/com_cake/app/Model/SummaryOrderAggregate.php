<?php
App::uses('AppModel', 'Model');


class SummaryOrderAggregate extends AppModel {
  
	/* 
	 *  estrae tutti i summarOrder di un ordine
	 */	 
	public function select_to_order($user, $order_id, $user_id=0) {
	
		$debug = false;
		
		$sql = "SELECT 
					SummaryOrderAggregate.*,
					User.id, User.name, User.username, User.email   
				FROM 
					".Configure::read('DB.prefix')."summary_order_aggregates as SummaryOrderAggregate, 
					".Configure::read('DB.portalPrefix')."users as User 
				WHERE
					SummaryOrderAggregate.organization_id = ".(int)$user->organization['Organization']['id']." 
					AND User.organization_id = ".(int)$user->organization['Organization']['id']." 
					AND SummaryOrderAggregate.order_id = ".(int)$order_id."
					AND SummaryOrderAggregate.user_id = User.id ";
		if($user_id>0) $sql .= " AND User.id = ".$user_id;
		$sql .= " ORDER BY SummaryOrderAggregate.user_id";
		// if($debug) CakeLog::write('debug','SummaryOrderAggregate::select_to_order() '.$sql);
		try {
			$results = $this->query($sql);
		
			if($user_id>0)
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
	public function select_totale_importo_to_order($user, $order_id, $debug=false) {
		
		$options = [];
		$options['conditions'] = ['SummaryOrderAggregate.organization_id' => $user->organization['Organization']['id'],
								  'SummaryOrderAggregate.order_id' => $order_id];
		$options['fields'] = ['sum(SummaryOrderAggregate.importo) as totale_importo'];
		$options['recursive'] = -1;
		
		$results = $this->find('first', $options);
		$results = current($results);
		if(empty($results['totale_importo'])) 
			$results = 0;
		else 
			$results = $results['totale_importo'];
						
		self::l("SummaryOrderAggregate::select_totale_importo_to_order order_id ".$order_id." totale_importo ".$results);
		
		return $results;
	}

	/* 
	 * se payToDelivery = ON / ON-POST non cancello SummaryOrderAggregate gia' pagati
	 */
	public function delete_to_order($user, $order_id, $debug = false) {
		
		switch($user->organization['Template']['payToDelivery']) {
			case "ON":
			case "ON-POST":
			case "POST":
				$options = [];
				$options['conditions'] = ['SummaryOrderAggregate.organization_id' => $user->organization['Organization']['id'],
										  'SummaryOrderAggregate.order_id' => $order_id];				
				$this->deleteAll($options['conditions'], false);
			break;
		}
	}
	
	/*
	 * se user_id valorizzato popolo i dati aggregati dello specifico user 
	 *		per ex SummaryOrderAggregate::admin_ajax_summary_orders_ricalcola()
	 *
	 * se payToDelivery = ON / ON-POST non ho cancellato SummaryOrderAggregate gia' pagati => non li carico nuovamente
	 */
	public function populate_to_order($user, $order_id, $user_id=0, $debug = false) {

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
			if($user_id>0)
				$conditions += array('Cart.user_id' => $user_id);
			$orderBy = array('User' => 'User.id');
			$articlesOrders = $ArticlesOrder->getArticoliAcquistatiDaUtenteInOrdine($user, $conditions, $orderBy);
		
			if(!empty($articlesOrders)) {
			
				$summaryCarts = [];
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
			
			
				$data = [];
				foreach($summaryCarts as $summaryCart) {
					
					/*
					 * ctrl se esiste gia' un SummaryOrderAggregate per l'utente
					 */
					$options = [];
					$options['conditions'] = ['SummaryOrderAggregate.organization_id' => $user->organization['Organization']['id'],
											  'SummaryOrderAggregate.order_id' => $order_id,
											  'SummaryOrderAggregate.user_id' => $summaryCart['user_id']];
					$options['recursive'] = -1;
					$ctrlSummaryOrderAggregateResults = $this->find('first', $options);
					if(empty($ctrlSummaryOrderAggregateResults)) { 
					
						$data['SummaryOrderAggregate']['organization_id'] = $user->organization['Organization']['id'];
						$data['SummaryOrderAggregate']['order_id'] = $order_id;
						$data['SummaryOrderAggregate']['user_id'] = $summaryCart['user_id'];
						$data['SummaryOrderAggregate']['importo'] = $summaryCart['importo'];
						
						if($debug)  {
							echo "<pre>salvo il record ";
							print_r($data);
							echo "</pre>";
						}
							
						$msg_errors = $this->getMessageErrorsToValidate($this, $data);
						if(!empty($msg_errors)) {
							self::x($msg_errors);
						}
						else {
							$this->create();
							$this->save($data);
						}
						
					} // end if(empty($ctrlSummaryOrderAggregateResults))  
				}	// end loop
				
			} // if(!empty($articlesOrders)) 
				
			//if($debug) exit;
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
	
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = SummaryOrderAggregate.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => 'Order.organization_id = SummaryOrderAggregate.organization_id',
			'fields' => '',
			'order' => ''
		)
	);
	
	public function beforeSave($options = []) {
		if(!empty($this->data['SummaryOrderAggregate']['importo'])) {
			$this->data['SummaryOrderAggregate']['importo'] =  $this->importoToDatabase($this->data['SummaryOrderAggregate']['importo']);
		}
		return true;
	}
		
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if(isset($val['SummaryOrderAggregate']['importo'])) {
					$results[$key]['SummaryOrderAggregate']['importo_'] = number_format($val['SummaryOrderAggregate']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryOrderAggregate']['importo_e'] = $results[$key]['SummaryOrderAggregate']['importo_'].' &euro;';
				}
			}
		}
		return $results;
	}	
}