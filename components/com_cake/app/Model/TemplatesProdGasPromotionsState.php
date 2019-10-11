<?php
App::uses('AppModel', 'Model');


class TemplatesProdGasPromotionsState extends AppModel {
	
	public $validate = array(
		'template_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'state_code' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'group_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'action_controller' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'action_action' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
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
		)
	);
}