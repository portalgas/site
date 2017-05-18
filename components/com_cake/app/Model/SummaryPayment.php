<?php
App::uses('AppModel', 'Model');

class SummaryPayment extends AppModel {
  	
/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = SummaryPayment.organization_id',
			'fields' => '',
			'order' => ''
		),
		'RequestPayment' => array(
			'className' => 'RequestPayment',
			'foreignKey' => 'request_payment_id',
			'conditions' => 'RequestPayment.organization_id = SummaryPayment.organization_id',
			'fields' => '',
			'order' => ''
		)
	);	
	
	public function afterFind($results, $primary = false) {			foreach ($results as $key => $val) {			if(!empty($val)) {			
				if(isset($val['SummaryPayment']['importo_dovuto'])) {
					$results[$key]['SummaryPayment']['importo_dovuto_'] = number_format($val['SummaryPayment']['importo_dovuto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));					$results[$key]['SummaryPayment']['importo_dovuto_e'] = $results[$key]['SummaryPayment']['importo_dovuto_'].' &euro;';				}
				if(isset($val['SummaryPayment']['importo_richiesto'])) {
					$results[$key]['SummaryPayment']['importo_richiesto_'] = number_format($val['SummaryPayment']['importo_richiesto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryPayment']['importo_richiesto_e'] = $results[$key]['SummaryPayment']['importo_richiesto_'].' &euro;';
				}
				if(isset($val['SummaryPayment']['importo_pagato'])) {
					$results[$key]['SummaryPayment']['importo_pagato_'] = number_format($val['SummaryPayment']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryPayment']['importo_pagato_e'] = $results[$key]['SummaryPayment']['importo_pagato_'].' &euro;';
				}			}				
		}		return $results;	}
	
	public function beforeSave($options = array()) {		
		if(!empty($this->data['SummaryPayment']['importo_dovuto'])) {
			$this->data['SummaryPayment']['importo_dovuto'] =  $this->importoToDatabase($this->data['SummaryPayment']['importo_dovuto']);
		}	
		if(!empty($this->data['SummaryPayment']['importo_richiesto'])) {
			$this->data['SummaryPayment']['importo_richiesto'] =  $this->importoToDatabase($this->data['SummaryPayment']['importo_richiesto']);
		}
		if(!empty($this->data['SummaryPayment']['importo_pagato'])) {
			$this->data['SummaryPayment']['importo_pagato'] =  $this->importoToDatabase($this->data['SummaryPayment']['importo_pagato']);
		}		return true;	}	
}