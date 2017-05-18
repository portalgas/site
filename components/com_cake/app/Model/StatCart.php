<?php
App::uses('AppModel', 'Model');

class StatCart extends AppModel {
	
	public $belongsTo = array(
			'User' => array(
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => 'User.organization_id = StatCart.organization_id',
					'fields' => '',
					'order' => ''
			),
			'Article' => array(
					'className' => 'Article',
					'foreignKey' => 'article_id',
					'conditions' => 'Article.organization_id = StatCart.organization_id',
					'fields' => '',
					'order' => ''
			),
			'StatOrder' => array(
					'className' => 'Order',
					'foreignKey' => 'stat_order_id',
					'conditions' => 'StatCartOrder.organization_id = StatCart.organization_id',
					'fields' => '',
					'order' => ''
			),
			'StatArticlesOrder' => array(
					'className' => 'StatArticlesOrder',
					'foreignKey' => '',
					'conditions' => 'StatArticlesOrder.organization_id = StatCart.organization_id AND StatArticlesOrder.order_id = StatCart.order_id AND StatArticlesOrder.article_id = StatCart.article_id',
					'fields' => '',
					'order' => ''
			),
	);	
}