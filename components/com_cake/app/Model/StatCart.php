<?php
App::uses('AppModel', 'Model');

class StatCart extends AppModel {
	
	public $belongsTo =[
			'User' =>[
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => 'User.organization_id = StatCart.organization_id',
					'fields' => '',
					'order' => ''
			],
			'StatArticlesOrder' =>[
					'className' => 'StatArticlesOrder',
					'foreignKey' => '',
					'conditions' => 'StatArticlesOrder.organization_id = StatCart.organization_id AND StatArticlesOrder.stat_order_id = StatCart.stat_order_id AND StatArticlesOrder.article_id = StatCart.article_id and StatArticlesOrder.article_organization_id = StatCart.article_organization_id',
					'fields' => '',
					'order' => ''
			],
			'StatOrder' => array(
					'className' => 'StatOrder',
					'foreignKey' => 'stat_order_id',
					'conditions' => 'StatOrder.organization_id = StatCart.organization_id',
					'fields' => '',
					'order' => ''
			),			
	];	
}