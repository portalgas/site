<?php
App::uses('AppModel', 'Model');

class GeoRegion extends AppModel {	
	
	public $name = 'GeoRegion';
	
	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
	);

	public $hasMany = array(
			'GeoProvince' => array(
					'className' => 'GeoProvince',
					'foreignKey' => 'id',
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