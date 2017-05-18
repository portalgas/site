<?php
App::uses('AppModel', 'Model');

class CashesHistory extends AppModel {

	public $useTable = 'cashes_histories';
		
	public function previousCashSave($user, $cash_id, $debug=false) {
	
		App::import('Model', 'Cash');
	    $Cash = new Cash;
		
	    $options = array();
	    $options['conditions'] = array('Cash.organization_id' => (int) $user->organization['Organization']['id'],
								            'Cash.id' => $cash_id);
		$options['recursive'] = -1;
		$previuosCash = $this->Cash->find('first', $options);
		
		if($debug) {
		  echo "<pre>CashesHistory::previousCashSave() - cassa precedente da salvare in History \n ";
		  print_r($previuosCash);
		  echo "</pre>";
		}
        
        $cashesHistory['CashesHistory'] = $previuosCash['Cash'];
        $cashesHistory['CashesHistory']['id'] = null;
        $cashesHistory['CashesHistory']['cash_id'] = $cash_id;

		if($debug) {
		  echo "<pre>CashesHistory::previousCashSave() \n ";
		  print_r($cashesHistory);
		  echo "</pre>";
		}
		        
	    $this->create();
        if($this->save($cashesHistory))
        	return true;
       	else
            return false;
	}
		
	public $validate = array(
		'organization_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'cash_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'user_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
	);

	public $belongsTo = array(
		'Cash' => array(
			'className' => 'Cash',
			'foreignKey' => 'cash_id',
			'conditions' => 'CashesHistory.organization_id = Cash.organization_id ',
			'fields' => '',
			'order' => ''
		),		
	);	
	
	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {
			if(!empty($val)) {				
				if (isset($val['CashesHistory']['importo'])) {
					$results[$key]['CashesHistory']['importo_'] = number_format($val['CashesHistory']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['CashesHistory']['importo_e'] = $results[$key]['CashesHistory']['importo_'].' &euro;';
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
	
	public function beforeSave($options = array()) {
		if (!empty($this->data['CashesHistory']['importo']))
			$this->data['CashesHistory']['importo'] = $this->importoToDatabase($this->data['CashesHistory']['importo']);
	
		return true;
	}
}