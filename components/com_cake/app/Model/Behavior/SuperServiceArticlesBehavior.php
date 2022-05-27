<?php 
class SuperServiceArticlesBehavior extends ModelBehavior {

	public function setup(Model $Model, $settings = []) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = [
					'option1_key' => 'option1_default_value',
					'option2_key' => 'option2_default_value',
					'option3_key' => 'option3_default_value',
			];
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], (array)$settings);
	}

	/*
	 * articoli associati ad un ordine
	 * Order.owner_articles REFERENT / DES / SUPPLIER
	 * Order.owner_organization_id
	 * Order.owner_supplier_organization_id
	 *
	 * return ArticlesOrder / Article
	 */
	public function getArticlesByOrderId(Model $Model, $user, $orderResult, $opts=[], $debug=false) {
	
		$esito = [];

		if(empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		App::import('Model', 'Order');
		$Order = new Order;
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$ArticlesOrder->unbindModel(['belongsTo' => ['Order', 'Cart']]);

		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);
	
		$Model::d("ServiceArticlesBehavior::getArticlesByOrderId order_id [".$orderResult['Order']['id']."] organization_id ".$user->organization['Organization']['id'], $debug);
		$Model::d("ServiceArticlesBehavior::getArticlesByOrderId order_id [".$orderResult['Order']['id']."] Order.supplier_organization_id ".$orderResult['Order']['supplier_organization_id'], $debug);
		$Model::d("ServiceArticlesBehavior::getArticlesByOrderId order_id [".$orderResult['Order']['id']."] Order.owner_articles ".$orderResult['Order']['owner_articles'], $debug);
		$Model::d("ServiceArticlesBehavior::getArticlesByOrderId order_id [".$orderResult['Order']['id']."] Order.owner_organization_id ".$orderResult['Order']['owner_organization_id'], $debug);
		$Model::d("ServiceArticlesBehavior::getArticlesByOrderId order_id [".$orderResult['Order']['id']."] Order.owner_supplier_organization_id ".$orderResult['Order']['owner_supplier_organization_id'], $debug);
			
		$options = [];
		$options['conditions'] = ['Article.organization_id' => $orderResult['Order']['owner_organization_id'],
				  'Article.supplier_organization_id' => $orderResult['Order']['owner_supplier_organization_id'],
				  'ArticlesOrder.organization_id' => $orderResult['Order']['organization_id'],
				  'ArticlesOrder.order_id' => $orderResult['Order']['id']];
											  
		if(isset($opts['conditions']))
			$options['conditions'] = array_merge($options['conditions'], $opts['conditions']);	  
		if(isset($opts['order']))
			$options['order'] = $opts['order'];
		else
			$options['order'] = ['Article.name' => 'asc'];	
	   	$options['recursive'] = 0;
		$Model::d($options, $debug);

		if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
			$results = $ArticlesOrder->find('first', $options);
		else
			$results = $ArticlesOrder->find('all', $options);
			
		$Model::d($results, $debug);
		
	   	return $results;
	}	

	/*
	 * articoli associati ad un produttore
	 * SuppliersOrganization.owner_articles REFERENT / DES / SUPPLIER
	 * SuppliersOrganization.owner_organization_id
	 * SuppliersOrganization.owner_supplier_organization_id
	 */	
	public function getArticlesBySupplierOrganizationId(Model $Model, $user, $suppliersOrganizationResult, $opts=[], $debug=false) {

		$esito = [];

		if(empty($suppliersOrganizationResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		App::import('Model', 'SuppliersOrganizationOwnerArticles');
		$SuppliersOrganization = new SuppliersOrganization;
		
		App::import('Model', 'Article');
		$Article = new Article;
					
	  	App::import('Model', 'ArticlesArticlesType');
		$ArticlesArticlesType = new ArticlesArticlesType;

		// $Article->unbindModel(['belongsTo' => ['CategoriesArticle']]);
		$Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
						
		if(!is_array($suppliersOrganizationResult))
			$suppliersOrganizationResult = $Model->_getSuppliersOrganizationById($user, $suppliersOrganizationResult, $debug);
	
		$Model::d("ServiceArticlesBehavior::getArticlesBySupplierOrganizationId supplier_organization_id [".$suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['id']."] organization_id ".$user->organization['Organization']['id'], $debug);
		$Model::d("ServiceArticlesBehavior::getArticlesBySupplierOrganizationId supplier_organization_id [".$suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['id']."] SuppliersOrganization.owner_articles ".$suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['owner_articles'], $debug);
		$Model::d("ServiceArticlesBehavior::getArticlesBySupplierOrganizationId supplier_organization_id [".$suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['id']."] SuppliersOrganization.owner_organization_id ".$suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['owner_organization_id'], $debug);
		$Model::d("ServiceArticlesBehavior::getArticlesBySupplierOrganizationId supplier_organization_id [".$suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['id']."] SuppliersOrganization.owner_supplier_organization_id ".$suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['owner_supplier_organization_id'], $debug);
			
		$options = [];
		$options['conditions'] = [
			'SuppliersOrganization.id' => $suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['owner_supplier_organization_id'],
			'SuppliersOrganization.organization_id' => $suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['owner_organization_id'],
			'Article.organization_id' => $suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['owner_organization_id'],
			'Article.supplier_organization_id' => $suppliersOrganizationResult['SuppliersOrganizationOwnerArticles']['owner_supplier_organization_id']];
		$Model::d($opts, $debug);								  
		if(isset($opts['conditions']))
			$options['conditions'] = array_merge($options['conditions'], $opts['conditions']);	  
		if(isset($opts['order']))
			$options['order'] = $opts['order'];
		else
			$options['order'] = ['Article.name'];	
	   	$options['recursive'] = 1;
		$Model::d($options, $debug);

		if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
			$results = $Article->find('first', $options);
		else
			$results = $Article->find('all', $options);
		
		if(!empty($results))	
		foreach($results as $numResult => $result) {
			$articlesTypeResults = $ArticlesArticlesType->getArticlesArticlesTypes($user, $result['Article']['organization_id'], $result['Article']['id']);
			if(!empty($articlesTypeResults))
				$results[$numResult]['ArticlesType'] = $articlesTypeResults;
		}	
		// $Model::d($results, $debug);

	   	return $results;
	}	
	
	/*
	 * trasforma il results ottenuto da find('first') in results ottenuto da find('all') => $results[0]
	 */
	protected function _arrayConvertingToFindAll($results) {
		
		$results2 = []; 
		array_push($results2, $results);
		
		return $results2;		
	}
	
	/*
	 * trasforma il results ottenuto da find('all') in results ottenuto da find('first') => $results
	 */
	protected function _arrayConvertingToFindFirst($results) {
		
		return current($results);
	}
}	
?>