<?php
App::uses('AppModel', 'Model');

class PactSupplierImport extends AppModel {

    public $useTable = 'suppliers';
	
	/*
	 * estrae i dati di un GAS legato al produttore (PactSuppliers)
	 */
	public function getGas($user, $organization_id, $debug=false) {
		
		$results = [];
		
		$PactSuppliersResults =  $this->getPactSuppliers($user, $user->organization['Organization']['id'], $organization_id, [], $debug); 
		if(!empty($PactSuppliersResults)) {
			$results = current($PactSuppliersResults['Supplier']['Organization']);
		}
		self::d($results, $debug);
		
		return $results;
	}
	
	/*
	 * estrae tutti i produttori
	 * se prod_gas_organization_id estraggo solo il produttore 
	 * se gas_organization_id estraggo solo il GAS associato al produttore
	 * filtersOwnerArticles SUPPLIER / REFERENT / DES
	 */
	public function getPactSuppliers($user, $prod_gas_organization_id=0, $gas_organization_id=0, $filters=[], $debug=false) {
	
		App::import('Model', 'Organization');
		$Organization = new Organization;
		
		App::import('Model', 'PactSupplier');
		$PactSupplier = new PactSupplier;

		App::import('Model', 'User');
		$User = new User;
		
		$options = [];
		$options['conditions'] = ['Organization.type' => 'PACT'];
		if(!empty($prod_gas_organization_id))
			$options['conditions'] += ['Organization.id' => $prod_gas_organization_id];
		$options['order'] = ['Organization.name'];
		$options['recursive'] = -1;
		
		$organizationResults = $Organization->find('all', $options);
		self::d("totale Organization.type PRODGAS ".count($organizationResults),$debug);
		if(count($organizationResults)==0) {
			self::d($options['conditions'],$debug);
		}
		
		foreach($organizationResults as $numResult => $organizationResult) {
			
			$organization_id = $organizationResult['Organization']['id'];

			$pactSupplierResults = $PactSupplier->getOrganizationSupplier($user, $organization_id, $filters, false);	
			if(!empty($pactSupplierResults)) {

				/*
				 * estraggo solo il GAS associato al produttore
				 */
				if(!empty($gas_organization_id)) {
					foreach($pactSupplierResults['Organization'] as $numResult2 => $pactSupplierResult) {
						
						self::d($pactSupplierResult['Organization']['id'].' '.$gas_organization_id,$debug);
						
						if($pactSupplierResult['Organization']['id'] != $gas_organization_id)
							unset($pactSupplierResults['Organization'][$numResult2]);
					}
									
				} // if(!empty($gas_organization_id))
			
				$organizationResults[$numResult]['Supplier'] = $pactSupplierResults;
			}
			
			/*
			 * estraggo account e gruppi del produttore
			 */
			$tmp_user->organization['Organization']['id'] = $organization_id;
			$conditions['UserGroupMap.group_id IN'] = "(".Configure::read('group_id_super_referent').",".Configure::read('prod_gas_supplier_manager').")"; 
			$userResults = $User->getUsers($tmp_user, $conditions);
			if(!empty($userResults))
				$organizationResults[$numResult]['User'] = $userResults;
				
		} // end foreach($organizationResults as $numResult => $organizationResult)

		if(!empty($prod_gas_organization_id))
			$organizationResults = current($organizationResults);
		
		self::d($organizationResults,$debug);
		
		return $organizationResults; 
	}
	
	/*
	 * dato un produttore, estrae il listino di tutti i GAS per confronto
	 * utilizzata da root e dal produttore
	 */
	public function getOrganizationsArticles($user, $supplier_id, $debug=false) {

		/*
		 * estrae gli articoli del produttore
		 */
		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;			
	
		$options = [];
		$options['conditions'] = ['ProdGasArticle.supplier_id' => $supplier_id];
		$options['order'] = ['ProdGasArticle.name'];
		$options['recursive'] = 0;
		
		$prodGasArticleResults = $ProdGasArticle->find('all', $options);	
		
		/*
		echo "<pre>";
		print_r($prodGasArticleResults);
		echo "</pre>";
		*/
				
		/*
		 * estrae i GAS del produttore, ctrl se sono abilitati [owner_articles] => SUPPLIER
		 */
		$PactSuppliersResults = $this->getPactSuppliers($user, $supplier_id, $debug);
		/*
		echo "<pre>";
		print_r($PactSuppliersResults);
		echo "</pre>";
		*/
		
		$organizations_owner_articles = [];
		$suppliers_organization_ids = [];	
		foreach($PactSuppliersResults as $PactSuppliersResult) {
			foreach($PactSuppliersResult['SuppliersOrganization'] as $suppliersOrganization) {
				array_push($suppliers_organization_ids, $suppliersOrganization['SuppliersOrganization']['id']);

				$organizations_owner_articles[$suppliersOrganization['Organization']['id']] = $suppliersOrganization['SuppliersOrganization']['owner_articles'];
			}
		}
		
		/*
		 * bind Article + Organization
		 */
		App::import('Model', 'Article');
		$Article = new Article;			
	
		$belongsTo = array(
				'className' => 'Organization',
				'foreignKey' => '',
				'conditions' => array('Organization.id = Article.organization_id'),
				'fields' => '',
				'order' => '');
		
		$Article->bindModel(array('belongsTo' => array('Organization' => $belongsTo)));
		
		$Article->unbindModel(['belongsTo' => ['SuppliersOrganization', 'CategoriesArticle']]);
		$Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(array('hasAndBelongsToMany' => array('Order', 'ArticlesArticlesType')));

		$options = [];
		if(count($suppliers_organization_ids)==1)
			$options['conditions'] = ['Article.supplier_organization_id' => $suppliers_organization_ids];
		else
			$options['conditions'] = ['Article.supplier_organization_id IN ' => $suppliers_organization_ids];
		$options['order'] = ['Article.name'];
		$options['recursive'] = 0;	
	
		$gasResults = $Article->find('all', $options);	

		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($gasResults);
			echo "</pre>";		
		}
	
		$i=0;
		$results = [];
		if(!empty($prodGasArticleResults)) 
		foreach($prodGasArticleResults as $prodGasArticleResult) {
			
			$results[$i]['Organization']['id'] = 0;
			$results[$i]['Organization']['name'] = "";
			$results[$i]['Organization']['img1'] = "";
			$results[$i]['Article']['supplier_id'] = $prodGasArticleResult['ProdGasArticle']['supplier_id'];
			$results[$i]['Article']['id'] = $prodGasArticleResult['ProdGasArticle']['id'];
			$results[$i]['Article']['supplier_id'] = $prodGasArticleResult['ProdGasArticle']['supplier_id'];
			$results[$i]['Article']['prod_gas_article_id'] = $prodGasArticleResult['ProdGasArticle']['id'];
			$results[$i]['Article']['name'] = $prodGasArticleResult['ProdGasArticle']['name'];
			$results[$i]['Article']['codice'] = $prodGasArticleResult['ProdGasArticle']['codice'];
			$results[$i]['Article']['prezzo'] = $prodGasArticleResult['ProdGasArticle']['prezzo'];
			$results[$i]['Article']['prezzo_'] = $prodGasArticleResult['ProdGasArticle']['prezzo_'];
			$results[$i]['Article']['prezzo_e'] = $prodGasArticleResult['ProdGasArticle']['prezzo_e'];
			$results[$i]['Article']['qta'] = $prodGasArticleResult['ProdGasArticle']['qta'];
			$results[$i]['Article']['um'] = $prodGasArticleResult['ProdGasArticle']['um'];
			$results[$i]['Article']['img1'] = $prodGasArticleResult['ProdGasArticle']['img1'];
			$results[$i]['SuppliersOrganization']['owner_articles'] = 'SUPPLIER';
			$i++; 
			
			if(!empty($gasResults)) 
			foreach($gasResults as $numResult => $gasResult) {
				if(trim(strtoupper($prodGasArticleResult['ProdGasArticle']['name'])) == trim(strtoupper($gasResult['Article']['name']))) {

					$results[$i]['Organization']['id'] = $gasResult['Article']['organization_id'];
					$results[$i]['Organization']['name'] = $gasResult['Organization']['name'];
					$results[$i]['Organization']['img1'] = $gasResult['Organization']['img1'];
					$results[$i]['Article']['supplier_id'] = 0;
					$results[$i]['Article']['id'] = $gasResult['Article']['id'];
					$results[$i]['Article']['supplier_id'] = $gasResult['Article']['supplier_id'];
					$results[$i]['Article']['prod_gas_article_id'] = $gasResult['Article']['prod_gas_article_id'];
					$results[$i]['Article']['name'] = $gasResult['Article']['name'];
					$results[$i]['Article']['codice'] = $gasResult['Article']['codice'];
					$results[$i]['Article']['prezzo'] = $gasResult['Article']['prezzo'];
					$results[$i]['Article']['prezzo_'] = $gasResult['Article']['prezzo_'];
					$results[$i]['Article']['prezzo_e'] = $gasResult['Article']['prezzo_e'];
					$results[$i]['Article']['qta'] = $gasResult['Article']['qta'];
					$results[$i]['Article']['um'] = $gasResult['Article']['um'];
					$results[$i]['Article']['img1'] = $gasResult['Article']['img1'];
					$results[$i]['SuppliersOrganization']['owner_articles'] = $organizations_owner_articles[$gasResult['Organization']['id']];
					$i++; 
					
					unset($gasResults[$numResult]);				
				} 
			}
		} // loop articles produttore
		
		/*
		 * aggiungo tutti gli articoli trovati solo per i GAS e non per il produttore
		 */
		if(!empty($gasResults)) 
		foreach($gasResults as $numResult => $gasResult) {
			$results[$i]['Organization']['id'] = $gasResult['Article']['organization_id'];
			$results[$i]['Organization']['name'] = $gasResult['Organization']['name'];
			$results[$i]['Organization']['img1'] = $gasResult['Organization']['img1'];
			$results[$i]['Article']['supplier_id'] = 0;
			$results[$i]['Article']['id'] = $gasResult['Article']['id'];
			$results[$i]['Article']['supplier_id'] = $gasResult['Article']['supplier_id'];
			$results[$i]['Article']['prod_gas_article_id'] = $gasResult['Article']['prod_gas_article_id'];
			$results[$i]['Article']['name'] = $gasResult['Article']['name'];
			$results[$i]['Article']['codice'] = $gasResult['Article']['codice'];
			$results[$i]['Article']['prezzo'] = $gasResult['Article']['prezzo'];
			$results[$i]['Article']['prezzo_'] = $gasResult['Article']['prezzo_'];
			$results[$i]['Article']['prezzo_e'] = $gasResult['Article']['prezzo_e'];
			$results[$i]['Article']['qta'] = $gasResult['Article']['qta'];
			$results[$i]['Article']['um'] = $gasResult['Article']['um'];
			$results[$i]['Article']['img1'] = $gasResult['Article']['img1'];
			$results[$i]['SuppliersOrganization']['owner_articles'] = $organizations_owner_articles[$gasResult['Organization']['id']];
			$i++; 
		}		 
		
		if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";		
		}
		
		return $results;

	} 

	public $hasMany = [
		'SuppliersOrganization' => [
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		]
	];
}