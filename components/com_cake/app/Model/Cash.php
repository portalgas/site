<?php
App::uses('AppModel', 'Model');


class Cash extends AppModel {
	
	/*
	 *  calcolare il totale in cassa
	*/
	public function get_totale_cash($user) {
		
		$options = [];
		$options['fields'] = array('SUM(Cash.importo) AS totale_importo');
		$options['conditions'] = array('Cash.organization_id' => $user->organization['Organization']['id']);
		$options['recursive'] = -1;
		$results = current($this->find('first', $options));

		if(empty($results['totale_importo'])) $results['totale_importo'] = 0;
		
		return $results;
	}
	
	/*
	 *      calcolare il totale in cassa di un utente
	 *  	le voci di cassa generiche (user_id=0) possono avere + occorrenze
	 *  	le voci di cassa degli utenti hanno una sola occorrenza
	*/
	public function get_totale_cash_to_user($user, $user_id, $debug = false) {
	
		$options = [];
		if($user_id==0)
			$options['fields'] = array('SUM(Cash.importo) AS totale_importo');
		else 
			$options['fields'] = array('Cash.importo AS totale_importo');
		$options['conditions'] = array('Cash.organization_id' => $user->organization['Organization']['id'],
										'Cash.user_id' => $user_id
		);
		$options['recursive'] = -1;
		$results = current($this->find('first', $options));

		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}

		if(empty($results['totale_importo'])) $results = 0;
		else $results = $results['totale_importo'];
		
		return $results;
	}
	
	public $validate = array(
		'organization_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
	);

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = Cash.organization_id and User.block = 0 AND User.username NOT LIKE \'%.portalgas.it\' ',
			'fields' => '',
			'order' => ''
		),		
	);	
	
	public $hasMany = array(
		'CashesHistory' => array(
			'className' => 'CashesHistory',
			'foreignKey' => 'cash_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)		
	);
	
	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {
			if(!empty($val)) {				
				if (isset($val['Cash']['importo'])) {
					$results[$key]['Cash']['importo_'] = number_format($val['Cash']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Cash']['importo_e'] = $results[$key]['Cash']['importo_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['importo'])) {
					$results[$key]['importo_'] = number_format($val['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['importo_e'] = $results[$key]['importo_'].' &euro;';
				}					
			}
		}
		return $results;
	}
	
	public function beforeSave($options = []) {
		if (!empty($this->data['Cash']['importo']))
			$this->data['Cash']['importo'] = $this->importoToDatabase($this->data['Cash']['importo']);
	
		return true;
	}
}