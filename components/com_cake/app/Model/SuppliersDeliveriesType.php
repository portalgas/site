<?php
App::uses('AppModel', 'Model');


class SuppliersDeliveriesType extends AppModel {
	
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => ['notBlank']
			),
		),
		'sort' => array(
				'numeric' => array(
						'rule' => ['numeric']
				),
		),
		'delivery_type_id' => array(
				'numeric' => array(
						'rule' => ['numeric']
				),
		),
	);
	
	public $hasMany = array(
		'Supplier' => array(
			'className' => 'Supplier',
			'foreignKey' => 'delivery_type_id',
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
}
?>