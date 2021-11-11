<?php
App::uses('AppModel', 'Model');
	
class TemplatesOrdersStatesOrdersAction extends AppModel {

	public $validate = array(
		'template_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
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
		'order_action_id' => array(
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
		'OrdersAction' => array(
			'className' => 'OrdersAction',
			'foreignKey' => 'order_action_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Template' => array(
			'className' => 'Template',
			'foreignKey' => 'template_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)		
	);
}