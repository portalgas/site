<?php
App::uses('AppModel', 'Model');

class Template extends AppModel {

	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => ['notBlank'],
			),
		)
	);	
				
	public $hasMany = array(
		'Organization' => array(
			'className' => 'Organization',
			'foreignKey' => 'template_id',
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
