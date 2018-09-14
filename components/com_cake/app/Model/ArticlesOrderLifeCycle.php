<?php
App::uses('AppModel', 'Model');
App::uses('CakeTime', 'Utility');

class ArticlesOrderLifeCycle extends AppModel {

	public $useTable = 'articles_orders';
	public $name = 'ArticlesOrder'; 
	public $alias = 'ArticlesOrder'; 
	
    public $belongsTo = [
        'Article' => [
            'className' => 'Article',
            'foreignKey' => 'article_id',
            'conditions' => 'Article.organization_id = ArticlesOrder.article_organization_id',
            'fields' => '',
            'order' => ''
        ],
        'Order' => [
            'className' => 'Order',
            'foreignKey' => 'order_id',
            'conditions' => 'Order.organization_id = ArticlesOrder.organization_id',
            'fields' => '',
            'order' => ''
        ],
        'Cart' => [
            'className' => 'Cart',
            'foreignKey' => '',
            'conditions' => 'Cart.organization_id = ArticlesOrder.organization_id AND Cart.order_id = ArticlesOrder.order_id AND Cart.article_organization_id = ArticlesOrder.article_organization_id AND Cart.article_id = ArticlesOrder.article_id',
            'fields' => '',
            'order' => '',
        ],
    ];
	
	/*
	 * ACL modifica articoli associati all'ordine
	 * 	edit
	 *		titolare DES del produttore ($isTitolareDesSupplier)
	 *		referente gestore del listino articoli - SuppliersOrganization.owner_articles == 'REFERENT'
	 *      produttore $currentOrganization['SuppliersOrganization']['owner_articles'] = SUPPLIER
	 * 	only read
	 *		NON titolare DES del produttore (!$isTitolareDesSupplier)
	 *		produttore gestore del listino articoli - SuppliersOrganization.owner_articles == 'SUPPLIER'
	 *		DES gestore del listino articoli, ma NON e' un ordine DES - SuppliersOrganization.owner_articles == 'DES'
	 *      produttore $currentOrganization['SuppliersOrganization']['owner_articles'] != SUPPLIER
	 */	
	public function canEdit($user, $orderResult, $isTitolareDesSupplier=false, $debug=false) {

		// $debug=true;
		
		$results = false;

		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);
		self::d($orderResult, $debug);
		
		$des_order_id = $orderResult['Order']['des_order_id'];
		$owner_articles = $orderResult['SuppliersOrganization']['owner_articles'];
	
		switch($user->organization['Organization']['type']) {
			case 'PRODGAS':
				if($owner_articles=='SUPPLIER') 
					$results = true;
				else 
					$results = false;
			break;
			case 'GAS':
				if($isTitolareDesSupplier || $owner_articles=='REFERENT')
					$results = true;
				else
				if(!$isTitolareDesSupplier || $owner_articles=='SUPPLIER' || $owner_articles=='DES')
					$results = false;
				else
					self::x("ArticlesOrderLifeCycle::canEdit order_id (".$orderResult['Order']['id'].") => Caso non previsto!");
		
				self::d("ArticlesOrderLifeCycle::canEdit order_id (".$orderResult['Order']['id'].") owner_articles ".$owner_articles);
				self::d("ArticlesOrderLifeCycle::canEdit order_id (".$orderResult['Order']['id'].") isTitolareDesSupplier ".$isTitolareDesSupplier);
				self::d("ArticlesOrderLifeCycle::canEdit order_id (".$orderResult['Order']['id'].") ".$results);
			
			break;
			case 'PROD':			
			break;
		}
						
		return $results;    
	}
    
}