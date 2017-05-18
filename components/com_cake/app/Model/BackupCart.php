<?php
App::uses('AppModel', 'Model');

class BackupCart extends AppModel {

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = BackupCart.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Article' => array(
				'className' => 'Article',
				'foreignKey' => 'article_id',
				'conditions' => 'Article.organization_id = BackupCart.article_organization_id',
				'fields' => '',
				'order' => ''
		),
		'Order' => array(
				'className' => 'Order',
				'foreignKey' => 'order_id',
				'conditions' => 'Order.organization_id = BackupCart.organization_id',
				'fields' => '',
				'order' => ''
		),
		'BackupArticlesOrder' => array(
				'className' => 'BackupArticlesOrder',
				'foreignKey' => '', 
				'conditions' => 'BackupArticlesOrder.organization_id = BackupCart.organization_id AND BackupArticlesOrder.order_id = BackupCart.order_id AND BackupArticlesOrder.article_organization_id = BackupCart.article_organization_id AND BackupArticlesOrder.article_id = BackupCart.article_id',
				'fields' => '',
				'order' => ''
		),			
	);	
}