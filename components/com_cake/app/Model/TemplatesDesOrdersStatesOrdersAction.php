<?php
App::uses('AppModel', 'Model');


class TemplatesDesOrdersStatesOrdersAction extends AppModel {

	public $validate = array(
		'template_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'group_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'state_code' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'des_order_action_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'sort' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
	);

	public $belongsTo = array(
		'UserGroup' => array(
			'className' => 'UserGroup',
			'foreignKey' => 'group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'DesOrdersAction' => array(
			'className' => 'DesOrdersAction',
			'foreignKey' => 'des_order_action_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)				
	);		
}