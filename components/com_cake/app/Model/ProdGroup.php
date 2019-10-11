<?php
App::uses('AppModel', 'Model');


class ProdGroup extends AppModel {

	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'name' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
	);

	public $hasMany = array(
		'ProdDelivery' => array(
			'className' => 'ProdDelivery',
			'foreignKey' => 'prod_group_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'ProdUsersGroup' => array(
			'className' => 'ProdUsersGroup',
			'foreignKey' => 'prod_group_id',
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