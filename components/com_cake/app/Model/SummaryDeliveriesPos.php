<?php
App::uses('AppModel', 'Model');

class SummaryDeliveriesPos extends AppModel {
	
	/*
	 * salva la commissione POS di uno user per una determinata consegna
	 * se non e' gia' registrata.
	 */
	public function saveToDelivery($user, $delivery_id, $user_id, $paymentPos, $debug = false) {
		
		if($user->organization['Organization']['hasFieldPaymentPos']!='Y')
			return true; 
		
		if($paymentPos=='0.00')
			return true;
			
		$results = $this->findPaymentPos($user, $delivery_id, $user_id, $debug);
		
		/*
		 *  paymentPos gia' salvato sullo user e per la consegna
		 */
		if(!empty($results)) {			
			if($debug) echo "<br />SummaryDeliveriesPos::saveToDelivery() paymentPos gia' salvato => NON salvo";
			return true;
		}	
		else {
			if($debug) echo "<br />SummaryDeliveriesPos::saveToDelivery() paymentPos NON salvato => salvo";

			$row = array();
			$row['SummaryDeliveriesPos']['organization_id'] = $user->organization['Organization']['id'];
			$row['SummaryDeliveriesPos']['delivery_id'] = $delivery_id;
			$row['SummaryDeliveriesPos']['user_id'] = $user_id;
			$row['SummaryDeliveriesPos']['importo'] = $paymentPos;
			
			if($debug) {
				echo "<br />SummaryDeliveriesPos::saveToDelivery() dati da salvare";
				echo "<pre>";
				print_r($row);
				echo "</pre>";					
			}
			
			$this->create();
			if(!$this->save($row)) 
				return false;
		}			
			
		return true;
	}
	
	/* 
	 * cerca il pagamento POS di uno user su una consegna
	 */ 
	public function findPaymentPos($user, $delivery_id, $user_id, $debug = false) {

		$results = array();
		
		if($user->organization['Organization']['hasFieldPaymentPos']!='Y')
			return $results; 
		
		$options = array();
		$options['conditions'] = array('SummaryDeliveriesPos.organization_id' => $user->organization['Organization']['id'],
										'SummaryDeliveriesPos.user_id' => $user_id,
										'SummaryDeliveriesPos.delivery_id' => $delivery_id);
		$options['recursive'] = -1;
		$results = $this->find('first', $options);
		/*
		echo "<pre>SummaryDeliveriesPos::findPaymentPos() ";
		print_r($results);
		echo "</pre>";
		*/
		
		return $results;
	} 
	
	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'delivery_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
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
			'conditions' => 'User.organization_id = SummaryDeliveriesPos.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = SummaryDeliveriesPos.organization_id',
			'fields' => '',
			'order' => ''
		),
		'StatDelivery' => array(
			'className' => 'StatDelivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'StatDelivery.organization_id = SummaryDeliveriesPos.organization_id',
			'fields' => '',
			'order' => ''
		),
	);
		
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if(isset($val['SummaryDeliveriesPos']['importo'])) {
					$results[$key]['SummaryDeliveriesPos']['importo_'] = number_format($val['SummaryDeliveriesPos']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryDeliveriesPos']['importo_e'] = $results[$key]['SummaryDeliveriesPos']['importo_'].' &euro;';
				}
			}
		}
		return $results;
	}	
}