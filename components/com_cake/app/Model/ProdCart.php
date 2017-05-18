<?php
App::uses('AppModel', 'Model');
App::import('Model', 'ProdCartMultiKey');

class ProdCart extends ProdCartMultiKey {

	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'article_organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'article_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'qta' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		)
	);

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = ProdCart.organization_id',
			'fields' => '',
			'order' => ''
		),
		'ProdDelivery' => array(
				'className' => 'ProdDelivery',
				'foreignKey' => 'prod_delivery_id',
				'conditions' => 'ProdDelivery.organization_id = ProdCart.organization_id',
				'fields' => '',
				'order' => ''
		),			
		'Article' => array(
			'className' => 'Article',
			'foreignKey' => 'article_id',
			'conditions' => 'Article.organization_id = ProdCart.article_organization_id',
			'fields' => '',
			'order' => ''
		),
		'ProdDeliveriesArticle' => array(
				'className' => 'ProdDeliveriesArticle',
				'foreignKey' => '',
				'conditions' => 'ProdDeliveriesArticle.organization_id = ProdCart.organization_id AND ProdDeliveriesArticle.prod_delivery_id = ProdCart.prod_delivery_id AND ProdDeliveriesArticle.article_organization_id = ProdCart.article_organization_id AND ProdDeliveriesArticle.article_id = ProdCart.article_id',
				'fields' => '',
				'order' => ''
		),
	);
}
