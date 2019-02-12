<?php
App::uses('AppModel', 'Model');


class ProdGasMonitoringArticle extends AppModel {

    public $useTable = 'prod_gas_articles';
   
	public $hasMany = array(
		'ProdGasArticlesPromotion' => array(
				'className' => 'ProdGasArticlesPromotion',
				'foreignKey' => 'prod_gas_article_id',
				'dependent' => false,
				'conditions' =>  '',
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