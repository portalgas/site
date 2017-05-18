<?php
App::uses('AppModel', 'Model');
/**
 * RequestPaymentsGeneric Model
 *
 * @property Organization $Organization
 * @property RequestPayment $RequestPayment
 */
class RequestPaymentsGeneric extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'request_payment_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
	);

	public $belongsTo = array(
		'RequestPayment' => array(
			'className' => 'RequestPayment',
			'foreignKey' => 'request_payment_id',
			'conditions' => 'RequestPayment.organization_id = RequestPaymentsGeneric.organization_id',
			'fields' => '',
			'order' => ''
		)
	);
	
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if(isset($val['RequestPaymentsGeneric']['importo'])) {
					$results[$key]['RequestPaymentsGeneric']['importo_'] = number_format($val['RequestPaymentsGeneric']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['RequestPaymentsGeneric']['importo_e'] = $results[$key]['RequestPaymentsGeneric']['importo_'];
				}
			}
		}
		return $results;
	}
	
	public function beforeSave($options = array()) {
		if(!empty($this->data['RequestPaymentsGeneric']['importo'])) {
			$this->data['RequestPaymentsGeneric']['importo'] =  $this->importoToDatabase($this->data['RequestPaymentsGeneric']['importo']);
		}
	
		return true;
	}
}