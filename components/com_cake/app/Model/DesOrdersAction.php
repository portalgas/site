<?php
App::uses('AppModel', 'Model');


class DesOrdersAction extends AppModel {

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

}