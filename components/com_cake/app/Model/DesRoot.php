<?php
App::uses('AppModel', 'Model');


class DesRoot extends AppModel {
	
	public $useTable = 'des';
	
	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
	);


	public $hasMany = array(
			'DesOrganization' => array(
					'className' => 'DesOrganization',
					'foreignKey' => 'des_id',
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