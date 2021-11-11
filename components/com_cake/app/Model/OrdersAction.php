<?php
App::uses('AppModel', 'Model');


class OrdersAction extends AppModel {

	public $validate = array(
		'controller' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'action' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'permission' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'permission_or' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'query_string' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'label' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'css_class' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'img' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
	);
	
	public $hasMany = array(
		'TemplatesOrdersStatesOrdersAction' => array(
			'className' => 'TemplatesOrdersStatesOrdersAction',
			'foreignKey' => 'order_action_id',
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