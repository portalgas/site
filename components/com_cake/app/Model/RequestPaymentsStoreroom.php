<?php
App::uses('AppModel', 'Model');


class RequestPaymentsStoreroom extends AppModel {

	/*
	 * ctrl se non esiste gia' la consegna legata alla richiesta di pagamento
	 */
	public function exist($user, $organization_id, $request_payment_id, $delivery_id, $debug=false) {

		$options = [];
		$options['conditions'] = ['RequestPaymentsStoreroom.organization_id' => $organization_id,
		 						  'RequestPaymentsStoreroom.request_payment_id' => $request_payment_id,
								  'RequestPaymentsStoreroom.delivery_id' => $delivery_id];
		$options['recursive'] = -1;

		$results = $this->find('count', $options);
		// debug($options);
		// debug($results);
		if($results==0)
			return false;
		else
			return true;
	}

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'organization_id' => array(
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
		'request_payment_id' => array(
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
		'RequestPayment' => array(
			'className' => 'RequestPayment',
			'foreignKey' => 'request_payment_id',
			'conditions' => 'RequestPayment.organization_id = RequestPaymentsStoreroom.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = RequestPaymentsStoreroom.organization_id',
			'fields' => '',
			'order' => ''
		)
	);
}