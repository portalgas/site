<?php
App::uses('AppModel', 'Model');


class ProdGasOrder extends AppModel {

    public $useTable = 'suppliers';
	
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => ['notBlank']
			),
		),
		'category_supplier_id' => array(
				'numeric' => array(
						'rule' => ['numeric']
				),
		),
	);
	
	public $hasMany = array(
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_id',
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