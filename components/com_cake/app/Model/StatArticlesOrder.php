<?php
App::uses('AppModel', 'Model');

class StatArticlesOrder extends AppModel {

	public $belongsTo = array(
			'Article' => array(
					'className' => 'Article',
					'foreignKey' => 'article_id',
					'conditions' => 'Article.organization_id = StatArticlesOrder.organization_id',
					'fields' => '',
					'order' => ''
			),
			'StatOrder' => array(
					'className' => 'Order',
					'foreignKey' => 'stat_order_id',
					'conditions' => 'Order.organization_id = StatArticlesOrder.organization_id',
					'fields' => '',
					'order' => ''
			),
			'StatCart' => array(
					'className' => 'Cart',
					'foreignKey' => '',
					'conditions' => 'StatCart.organization_id = StatArticlesOrder.organization_id AND StatCart.order_id = StatArticlesOrder.order_id AND StatCart.article_id = StatArticlesOrder.article_id',
					'fields' => '',
					'order' => '',
			),
	);
	
	public function afterFind($results, $primary = true) {
	
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if (isset($val['StatArticlesOrder']['prezzo'])) {
					$results[$key]['StatArticlesOrder']['prezzo_'] = number_format($val['StatArticlesOrder']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['StatArticlesOrder']['prezzo_e'] = $results[$key]['StatArticlesOrder']['prezzo_'].' &euro;';
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
