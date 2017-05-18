<?php
App::uses('AppModel', 'Model');

class ProdGasPromotionsOrganizationsManager extends AppModel {

    public $useTable = 'prod_gas_promotions_organizations';

	/*
	 * il managerGas accetta la promozione:
	 * si importa il produttore (da Supplier a SuppliersOrganization)
	 */
	public function importProdGasSupplier($user, $supplier_id, $prod_gas_promotion_id, $debug=false) {
		
		$continua = true;
		$supplier_organization_id = 0;
		
		if(empty($supplier_id) || empty($prod_gas_promotion_id)) {
			if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Passaggio parametri errato: supplier_id '.$supplier_id.' prod_gas_promotion_id '.$prod_gas_promotion_id;
			return false;
		}
		
		/*
		 * ctrl se il GAS ha il produttore associato
		 */
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization','CategoriesSupplier')));
		$SuppliersOrganization->unbindModel(array('hasMany' => array('Article','Order','SuppliersOrganizationsReferent')));

		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
									   'Supplier.id' => $supplier_id,
									   'Supplier.stato' => 'Y'); // non prendo Supplier.stato = 'T' or Supplier.stato = 'PG'
		$options['recursive'] = 1;
		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		if($debug) {
			echo "<pre>ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Dati produttore associato al GAS \n";
			print_r($suppliersOrganizationResults);
			echo "</pre>";
		}		
		if(!empty($suppliersOrganizationResults)) {
			/*
			 * il produttore c'e' ma era disabilitato
			 */
			if($suppliersOrganizationResults['SuppliersOrganization']['stato']!='Y') {
				$sql = "UPDATE ".Configure::read('DB.prefix')."suppliers_organizations SET stato='Y' WHERE id=".$suppliersOrganizationResults['SuppliersOrganization']['id']." AND organization_id=".$user->organization['Organization']['id'];
				if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasSupplier() '.$sql;
				try {
					$this->query($sql);
				}
				catch (Exception $e) {
					CakeLog::write('error',$sql);
					CakeLog::write('error',$e);
					return false;
				}				
			}
			
			$supplier_organization_id = $suppliersOrganizationResults['SuppliersOrganization']['id'];
			
			if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Produttore ESISTE gia ('.$supplier_organization_id.') => non lo creo';
		}
		else {
			/*
			 * import produttore
			 */
			App::import('Model', 'Supplier');
			$Supplier = new Supplier;
			
			$options = array();
			$options['conditions'] = array('Supplier.id' => $supplier_id);
			$options['recursive'] = -1;
			$supplierResults = $Supplier->find('first', $options);
			if(empty($supplierResults)) {
			if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Dati produttore NON trovato!';
				return false;			
			}	
			
			else {
				$data=array();
				$data['SuppliersOrganization']['organization_id'] = $user->organization['Organization']['id'];
				$data['SuppliersOrganization']['supplier_id'] = $supplier_id;
				$data['SuppliersOrganization']['name'] = $supplierResults['Supplier']['name'];
				$data['SuppliersOrganization']['category_supplier_id'] = $supplierResults['Supplier']['category_supplier_id'];
				$data['SuppliersOrganization']['stato'] = 'Y';
				$data['SuppliersOrganization']['mail_order_open'] = 'Y';
				$data['SuppliersOrganization']['mail_order_close'] = 'Y';
				$SuppliersOrganization->create();
				
				if($debug) {
					echo "<pre>ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Dati produttore da SAVE \n";
					print_r($data);
					echo "</pre>";
				}
				
				if(!$SuppliersOrganization->save($data)) {
					if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Produttore NON salvato!';
					return false;	
				}	
				
				$supplier_organization_id = $SuppliersOrganization->getLastInsertId();
			}	 
		}

		 return $supplier_organization_id;		 
	}

	/*
	 * il managerGas accetta la promozione:
	 * si importa articoli in promozioni (da ProdGasArticlesPromotion->ProdGasArticles a Articles)
	 */
	public function importProdGasArticles($user, $supplier_id, $prod_gas_promotion_id, $debug=false) {
		
		$continua = true;
		
		if(empty($supplier_id) || empty($prod_gas_promotion_id)) {
			if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasArticles() Passaggio parametri errato: supplier_id '.$supplier_id.' prod_gas_promotion_id '.$prod_gas_promotion_id;
			return false;
		}
		
		/*
		 * estratto il supplier_organization_id
		 */
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization','CategoriesSupplier')));
		$SuppliersOrganization->unbindModel(array('hasMany' => array('Article','Order','SuppliersOrganizationsReferent')));

		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
									   'Supplier.id' => $supplier_id,
									   'Supplier.stato' => 'Y'); // non prendo Supplier.stato = 'T' or Supplier.stato = 'PG'
		$options['recursive'] = 1;
		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		if($debug) {
			echo "<pre>ProdGasPromotionsOrganizationsManager::importProdGasArticles() Dati produttore associato al GAS \n";
			print_r($suppliersOrganizationResults);
			echo "</pre>";
		}		
		
		if(empty($suppliersOrganizationResults)) {
			if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasArticles() Dati produttore associato al GAS NON trovato!';
			return false;			
		}	
		
		$supplier_organization_id = $suppliersOrganizationResults['SuppliersOrganization']['id'];
		
		/*
		 * ProdGasArticlesPromotion, li importo per il GAS
		 */
		App::import('Model', 'Article');
		
		App::import('Model', 'ProdGasArticlesPromotion');
		$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;

		$ProdGasArticlesPromotion->unbindModel(array('belongsTo' => array('ProdGasPromotion')));
		
		$options = array();
		$options['conditions'] = array('ProdGasArticle.supplier_id' => $supplier_id,
									   'ProdGasArticle.stato' => 'Y',
									   'ProdGasArticlesPromotion.prod_gas_promotion_id' => $prod_gas_promotion_id);
		$options['recursive'] = 1;
		$prodGasArticlesPromotionResults = $ProdGasArticlesPromotion->find('all', $options);
		if($debug) {
			echo "<pre>ProdGasPromotionsOrganizationsManager::importProdGasArticles() Articoli in promozione \n";
			print_r($prodGasArticlesPromotionResults);
			echo "</pre>";
		}
		if(empty($prodGasArticlesPromotionResults)) {
			if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasArticles() Articoli in promozione NON trovati!';
			return false;			
		}	

		foreach($prodGasArticlesPromotionResults as $prodGasArticlesPromotionResult) {
			/*
			 * ctrl che l'prodGasArticle sia in Article del GAS
			 */
			$prod_gas_article_id = $prodGasArticlesPromotionResult['ProdGasArticle']['id'];
			
			$Article = new Article;
			$options = array();
			$options['conditions'] = array('Article.supplier_id' => $supplier_id,
										   'Article.prod_gas_article_id' => $prod_gas_article_id,
										   'Article.organization_id' => $user->organization['Organization']['id'],
										   'Article.supplier_organization_id' => $supplier_organization_id);
			$options['recursive'] = -1;
			$articleResults = $Article->find('first', $options);
			if($debug) {
				echo "<pre>ProdGasPromotionsOrganizationsManager::importProdGasArticles() Ricerco ARTICLE se gia associato al GAS \n";
				print_r($options['conditions']);
				print_r($articleResults);
				echo "</pre>";
			}  			
			if(empty($articleResults)) {
				/*
				 * articolo non presente, INSERT
				 */
				$data=array();
				$data['Article']['id'] = $Article->getMaxIdOrganizationId($user->organization['Organization']['id']);
				$data['Article']['organization_id'] = $user->organization['Organization']['id'];
				$data['Article']['supplier_organization_id'] = $supplier_organization_id;
				$data['Article']['supplier_id'] = $supplier_id;
				$data['Article']['prod_gas_article_id'] = $prod_gas_article_id;
				$data['Article']['name'] = $prodGasArticlesPromotionResult['ProdGasArticle']['name'];
				$data['Article']['codice'] = $prodGasArticlesPromotionResult['ProdGasArticle']['codice'];
				$data['Article']['nota'] = $prodGasArticlesPromotionResult['ProdGasArticle']['nota'];
				$data['Article']['ingredienti'] = $prodGasArticlesPromotionResult['ProdGasArticle']['ingredienti'];
				$data['Article']['prezzo'] = $prodGasArticlesPromotionResult['ProdGasArticle']['prezzo'];
				$data['Article']['qta'] = $prodGasArticlesPromotionResult['ProdGasArticle']['qta'];
				$data['Article']['um'] = $prodGasArticlesPromotionResult['ProdGasArticle']['um'];
				$data['Article']['um_riferimento'] = $prodGasArticlesPromotionResult['ProdGasArticle']['um_riferimento'];
				$data['Article']['pezzi_confezione'] = $prodGasArticlesPromotionResult['ProdGasArticle']['pezzi_confezione'];
				$data['Article']['bio'] = $prodGasArticlesPromotionResult['ProdGasArticle']['bio'];
				$data['Article']['img1'] = $prodGasArticlesPromotionResult['ProdGasArticle']['img1'];
				$data['Article']['qta_minima'] = 1;
				$data['Article']['qta_massima'] = 0;
				$data['Article']['qta_minima_order'] = 0;
				$data['Article']['qta_massima_order'] = 0;
				$data['Article']['qta_multipli'] = 1;
				$data['Article']['alert_to_qta'] = 0;
				$data['Article']['flag_presente_articlesorders'] = 'Y';
				$data['Article']['stato'] = 'Y';
  
				if($debug) {
					echo "<pre>ProdGasPromotionsOrganizationsManager::importProdGasArticles() Articoli INSERT al GAS \n";
					print_r($data);
					echo "</pre>";
				}  		
				
				/*
				 * richiamo la validazione 
				 */
				$Article->set($data);				
				if(!$Article->validates()) {
			
					$errors = $Article->validationErrors;
					$tmp = '';
					$flatErrors = Set::flatten($errors);
					if(count($errors) > 0) { 
						$tmp = '';
						foreach($flatErrors as $key => $value) 
							$tmp .= $value.' - ';
					}
					if($debug) echo "<br />Articolo non inserito: dati non validi, $tmp";
					$continua = false;
				}
				else {				
					$Article->create();
					if(!$Article->save($data)) {
						if($debug) echo "<br />Articolo non inserito: ".$prodGasArticlesPromotionResult['ProdGasArticle']['name'];
						$continua = false;
					}	
				}
			}
			else {
				if($debug) {
					echo "<br />ProdGasPromotionsOrganizationsManager::importProdGasArticles() Articolo ".$prodGasArticlesPromotionResult['ProdGasArticle']['name']." GIA INSERT al GAS \n";
				} 				
			}
			
			$qta = $prodGasArticlesPromotionResult['ProdGasArticlesPromotion']['qta'];
			$name = $prodGasArticlesPromotionResult['ProdGasArticle']['name'];
			
		} // loop prodGasArticlesPromotionResults
		
		return $continua;
	}

	/*
	 * il managerGas accetta la promozione:
	 * si importa articoli in promozioni in articoli in ordine (da ProdGasArticlesPromotion a ArticlesOrders)
	 * la ProdGasArticlesPromotion.qta diventera' ArticlesOrder.qta_minima_order e ArticlesOrder.qta_massima_order
	 */
	public function importProdGasArticlesPromotions($user, $supplier_id, $prod_gas_promotion_id, $order_id, $debug=false) {
		
		$continua = true;
		
		if(empty($supplier_id) || empty($prod_gas_promotion_id) || empty($order_id)) {
			if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasArticlesPromotions() Passaggio parametri errato: supplier_id '.$supplier_id.' prod_gas_promotion_id '.$prod_gas_promotion_id.' order_id '.$order_id;
			return false;
		}
		
		App::import('Model', 'ArticlesOrder');
		
		/*
		 * estratto gli articoli in promozione
		 * ProdGasArticlesPromotion.qta => ArticlesOrder.qta_minima_order e ArticlesOrder.qta_massima_order
		 */
		try {
			$sql = "SELECT Article.*, ProdGasArticlesPromotion.qta FROM 
					k_prod_gas_articles_promotions AS ProdGasArticlesPromotion,
					k_prod_gas_articles AS ProdGasArticle,
					k_articles AS Article 
				WHERE  
					ProdGasArticlesPromotion.supplier_id = ".$supplier_id." 
					and ProdGasArticlesPromotion.prod_gas_promotion_id = ".$prod_gas_promotion_id." 
					and ProdGasArticlesPromotion.supplier_id = ProdGasArticle.supplier_id 
					and ProdGasArticlesPromotion.prod_gas_article_id = ProdGasArticle.id
					and ProdGasArticle.stato = 'Y'
					and Article.organization_id = ".$user->organization['Organization']['id']." 
					and Article.supplier_id = ProdGasArticle.supplier_id 
					and Article.prod_gas_article_id = ProdGasArticle.id
					ORDER BY Article.id;";
				if($debug) echo '<br />ProdGasPromotionsOrganizationsManager '.$sql;
				$results = $this->query($sql);
				
				if(empty($results)){
					if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasArticlesPromotions() Articoli in promozione NON trovati!';
					return false;			
				}
		
				foreach($results as $result) {
					$data = array();
					$data['ArticlesOrder']['organization_id'] = $user->organization['Organization']['id'];
					$data['ArticlesOrder']['article_organization_id'] = $user->organization['Organization']['id'];
					$data['ArticlesOrder']['article_id'] = $result['Article']['id'];
					$data['ArticlesOrder']['order_id'] = $order_id;
					$data['ArticlesOrder']['prezzo'] = $result['Article']['prezzo'];
					$data['ArticlesOrder']['qta_cart'] = 0;
					$data['ArticlesOrder']['pezzi_confezione'] = $result['Article']['pezzi_confezione'];
					$data['ArticlesOrder']['qta_minima'] = $result['Article']['qta_minima'];
					$data['ArticlesOrder']['qta_massima'] = $result['Article']['qta_massima'];
					$data['ArticlesOrder']['qta_minima_order'] = $result['ProdGasArticlesPromotion']['qta'];
					$data['ArticlesOrder']['qta_massima_order'] = $result['ProdGasArticlesPromotion']['qta'];
					$data['ArticlesOrder']['qta_multipli'] = $result['Article']['qta_multipli'];
					$data['ArticlesOrder']['flag_bookmarks'] = 'N';
					$data['ArticlesOrder']['alert_to_qta'] = 0;
					$data['ArticlesOrder']['stato'] = 'Y';

					/*
					 * richiamo la validazione
					 */
					$ArticlesOrder = new ArticlesOrder; 
					$ArticlesOrder->set($data);
					if (!$ArticlesOrder->validates()) {
						$errors = $ArticlesOrder->validationErrors;
						$tmp = '';
						$flatErrors = Set::flatten($errors);
						if (count($errors) > 0) {
							$tmp = '';
							foreach ($flatErrors as $key => $value)
								$tmp .= $value . ' - ';
						}
						if($debug) echo "<br />Articolo non associato all'ordine: dati non validi, $tmp";
						$continua = false;
					} else {
						$ArticlesOrder->create();
						if (!$ArticlesOrder->save($data)) {
							if($debug) echo "<br />articolo " . $result['Article']['id'] . " in errore!";
							$continua = false;
						}
					}					
				}
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			$continua = false;
		}		
		
		return $continua;
	}
	
	public $validate = array(
		'delivery_id' => array(
			'rule' => array('naturalNumber', false),
			'message' => "Scegli la consegna da associare all'ordine",
		),
		'data_inizio_db' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data valida',
				'allowEmpty' => false
			),
			'dateMinore' => array(
				'rule'       =>  array('date_comparison', '<=', 'data_fine_db'),
				'message'    => 'La data di apertura non può essere posteriore della data di chiusura',
			),
			'dateToDelivery' => array(
				'rule'       =>  array('date_comparison_to_delivery','>'),
				'message'    => 'La data di apertura non può essere posteriore della data della consegna',
			),
		),
		'data_fine_db' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data valida',
				'allowEmpty' => false
			),
			'dateMaggiore' => array(
				'rule'       =>  array('date_comparison', '>=', 'data_inizio_db'),
				'message'    => 'La data di chiusura non può essere antecedente della data di apertura',
			),
			'dateToDelivery' => array(
				'rule'       =>  array('date_comparison_to_delivery','>'),
				'message'    => 'La data di chiusura non può essere posteriore o uguale della data della consegna',
			),
			'dateToProdGasPromotionDataFine' => array(
				'rule'       =>  array('date_comparison','<=', 'prod_gas_promotion_data_fine'),
				'message'    => 'La data di chiusura non può essere posteriore alla data di chiusura della promozione',
			)
		),
	);

	function date_comparison($field=array(), $operator, $field2) {
		foreach( $field as $key => $value1 ){
			$value2 = $this->data[$this->alias][$field2];
			
			if(empty($value2))
				return true;
			
			if (!Validation::comparison($value1, $operator, $value2))
				return false;
		}
		return true;
	}
	
	function date_comparison_to_delivery($field=array(), $operator) {
		foreach( $field as $key => $value ){
			if(isset($this->data[$this->alias]['delivery_id'])) { // capita se l'elenco delle consegne è vuoto
				$delivery_id = $this->data[$this->alias]['delivery_id'];
				$organization_id = $this->data[$this->alias]['organization_id'];
				 
				App::import('Model', 'Delivery');
				$Delivery = new Delivery;
			
				$Delivery->unbindModel(array('hasMany' => array('Order','Cart')));
				$delivery = $Delivery->read($organization_id, 'data', $delivery_id);
				$delivery_data = $delivery['Delivery']['data'];
			
				if (!Validation::comparison($delivery_data, $operator, $value))
					return false;
			}
			else
				return false;
		}
		return true;		
	}
	
	public $belongsTo = array(
		'ProdGasPromotion' => array(
			'className' => 'ProdGasPromotion',
			'foreignKey' => 'prod_gas_promotion_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Organization' => array(
			'className' => 'Organization',
			'foreignKey' => 'organization_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => 'Order.organization_id = ProdGasPromotionsOrganizationsManager.organization_id',
			'fields' => '',
			'order' => ''
		),
	);
	
	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {
			if(!empty($val)) {				
		
				if (isset($val['ProdGasPromotionsOrganizationsManager']['trasport'])) {
					$results[$key]['ProdGasPromotionsOrganizationsManager']['trasport_'] = number_format($val['ProdGasPromotionsOrganizationsManager']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasPromotionsOrganizationsManager']['trasport_e'] = $results[$key]['ProdGasPromotionsOrganizationsManager']['trasport_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['trasport'])) {
					$results[$key]['trasport_'] = number_format($val['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['trasport_e'] = $results[$key]['trasport_'].' &euro;';
				}
		
				if (isset($val['ProdGasPromotionsOrganizationsManager']['cost_more'])) {
					$results[$key]['ProdGasPromotionsOrganizationsManager']['cost_more_'] = number_format($val['ProdGasPromotionsOrganizationsManager']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasPromotionsOrganizationsManager']['cost_more_e'] = $results[$key]['ProdGasPromotionsOrganizationsManager']['cost_more_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['cost_more'])) {
					$results[$key]['cost_more_'] = number_format($val['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['cost_more_e'] = $results[$key]['cost_more_'].' &euro;';
				}				
			}
		}
		return $results;
	}	
}