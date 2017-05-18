<?php
App::uses('AppModel', 'Model');

class BackupArticlesOrder extends AppModel {

	public $belongsTo = array(
		'Article' => array(
			'className' => 'Article',
			'foreignKey' => 'article_id',
			'conditions' => 'Article.organization_id = BackupArticlesOrder.article_organization_id',
			'fields' => '',
			'order' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => 'Order.organization_id = BackupArticlesOrder.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Cart' => array(
			'className' => 'Cart',
			'foreignKey' => '',
			'conditions' => 'Cart.organization_id = BackupArticlesOrder.organization_id AND Cart.order_id = BackupArticlesOrder.order_id AND Cart.article_organization_id = BackupArticlesOrder.article_organization_id AND Cart.article_id = BackupArticlesOrder.article_id',
			'fields' => '',
			'order' => '',
		),			
	);

	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {
			if(!empty($val)) {				
				if (isset($val['BackupArticlesOrder']['prezzo'])) {
					$results[$key]['BackupArticlesOrder']['prezzo_'] = number_format($val['BackupArticlesOrder']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupArticlesOrder']['prezzo_e'] = $results[$key]['BackupArticlesOrder']['prezzo_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['prezzo'])) {
					$results[$key]['prezzo_'] = number_format($val['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['prezzo_e'] = $results[$key]['prezzo_'].' &euro;';
				}					
			}
		}
		return $results;
	}	
}