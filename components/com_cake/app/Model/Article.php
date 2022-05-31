<?php
App::uses('AppModel', 'Model');
App::import('Model', 'ArticleMultiKey');

class Article extends ArticleMultiKey {

   public $name = 'Article';
   
   /*
    * filtro per Order.owner_articles owner_organization_id owner_supplier_organization_id => estraggo TUTTI quelli del GAS  (REFERENT / SUPPLIER / DES)
	*
    * ottendo i dati anagrafici degli Articoli 
    * 	Article, SupplierOrganization, CategoriesArticle, ArticlesType
   */   
   public function getByOrder($user, $orderResult, $opts=[], $debug=false) {
	  	
		$esito = [];

		if(empty($orderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		if(!is_array($orderResult))
			$orderResult = $this->_getOrderById($user, $orderResult, $debug);
		
	  	App::import('Model', 'ArticlesArticlesType');
		$ArticlesArticlesType = new ArticlesArticlesType;

		// $this->unbindModel(['belongsTo' => ['CategoriesArticle']]);
		$this->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$this->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$this->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
				
		$options = [];
		$options['conditions'] = ['Article.organization_id' => $orderResult['Order']['owner_organization_id'],
								  'Article.supplier_organization_id' => $orderResult['Order']['owner_supplier_organization_id']];
											  
		if(!empty($opts))
			$options['conditions'] = array_merge($options['conditions'], $opts);
	   	$options['recursive'] = 1;
	   	$options['order'] = ['Article.name'];
		self::d('Article::getByOrder()', $debug);
		self::d($options, $debug);

		if (array_key_exists('Article.id', $opts))
			$results = $this->find('first', $options);
		else
			$results = $this->find('all', $options);
		
		if(!empty($results))	
		foreach($results as $numResult => $result) {
			$articlesTypeResults = $ArticlesArticlesType->getArticlesArticlesTypes($user, $result['Article']['organization_id'], $result['Article']['id']);
			if(!empty($articlesTypeResults))
				$results[$numResult]['ArticlesType'] = $articlesTypeResults;
		}	
		self::d($results, $debug);

	   	return $results;
   }
      
	/*
	 * articoli che si possono ordinare, del produttore ProdGasSupplier
	 *	Article.organization_id          => Order.owner_organization_id
	 *	Article.supplier_organization_id => Order.owner_supplier_organization_id
	 */   
   public function getBySupplierArticleInArticlesOrder($user, $owner_organization_id, $owner_supplier_organization_id, $article_id_da_escludere=[]) {

		$opts=[];
		$opts=['Article.stato' => 'Y',
			   'Article.flag_presente_articlesorders' => 'Y'];
        if(!empty($article_id_da_escludere))
			$opts += ["NOT" => [ "Article.id" => explode(',', $article_id_da_escludere)]];			   
		
		$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $owner_organization_id]);

		$results = $this->getByOrder($tmp_user, $owner_supplier_organization_id, $opts, $debug);
	   	
	   	return $results;
   } 
   
   /*
    * filtro per SuppliersOrganization.id => estraggo TUTTI quelli del GAS  (REFERENT / SUPPLIER / DES)
	*
    * ottendo i dati anagrafici degli Articoli 
    * 	Article, SupplierOrganization, CategoriesArticle, ArticlesType
   */   
   public function getBySupplierOrganization($user, $supplier_organization_id, $opts=[], $debug=false) {

	  	App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $supplier_organization_id];
		$options['fields'] = ['SuppliersOrganization.owner_articles', 'SuppliersOrganization.owner_organization_id', 'SuppliersOrganization.owner_supplier_organization_id'];
		$options['recursive'] = -1;
		self::d($options, $debug);
		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
	  	
	  	App::import('Model', 'ArticlesArticlesType');
		$ArticlesArticlesType = new ArticlesArticlesType;

		// $this->unbindModel(['belongsTo' => ['CategoriesArticle']]);
		$this->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$this->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$this->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
				
		$options = [];
		$options['conditions'] = ['Article.organization_id' => $suppliersOrganizationResults['SuppliersOrganization']['owner_organization_id'],
								  'Article.supplier_organization_id' => $suppliersOrganizationResults['SuppliersOrganization']['owner_supplier_organization_id'],
								  'SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $supplier_organization_id];
											  
		if(!empty($opts))
			$options['conditions'] = array_merge($options['conditions'], $opts);
	   	$options['recursive'] = 1;
	   	$options['order'] = ['Article.name'];
		self::d('Article::getByOrder()', $debug);
		self::d($options['conditions'], $debug);

		if (array_key_exists('Article.id', $opts))
			$results = $this->find('first', $options);
		else
			$results = $this->find('all', $options);
		
		if(!empty($results))	
		foreach($results as $numResult => $result) {
			$articlesTypeResults = $ArticlesArticlesType->getArticlesArticlesTypes($user, $result['Article']['organization_id'], $result['Article']['id']);
			if(!empty($articlesTypeResults))
				$results[$numResult]['ArticlesType'] = $articlesTypeResults;
		}	
		self::d($results, $debug);

	   	return $results;
   }
  
  
   /*
    * filtro per Article.organization_id => estraggo SOLO quelli del GAS (REFERENT) escludo SUPPLIER / DES
	*
    * ottendo i dati anagrafici degli Articoli 
    * 	Article, SupplierOrganization, CategoriesArticle, ArticlesType
   */
   public function getArticlesDataAnagr($user, $options, $debug=false) {
   	
   	$this->unbindModel(['hasOne' => ['ArticlesOrder']]);
   	$this->unbindModel(['hasMany' => ['ArticlesOrder']]);
   	$this->unbindModel(['hasAndBelongsToMany' => ['Order']]);
   	
   	if (!array_key_exists('Article.organization_id', $options['conditions']))
		$this->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = '.$user->organization['Organization']['id'];
	else
		$this->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = '.$options['conditions']['Article.organization_id'];
   	$this->hasMany['ArticlesArticlesType']['conditions'] = ['ArticlesArticlesType.organization_id' => $user->organization['Organization']['id']];
   	$this->hasAndBelongsToMany['ArticlesType']['conditions'] = ['ArticlesArticlesType.organization_id' => $user->organization['Organization']['id']];
   	
   	if($user->organization['Organization']['hasFieldArticleCategoryId']=='N')
   		$this->unbindModel(['belongsTo' => ['CategoriesArticle']]);
   		
	if (!array_key_exists('Article.organization_id', $options['conditions']))	
		$options['conditions'] += ['Article.organization_id' => $user->organization['Organization']['id']];
									
   	$options['recursive'] = 1;
	self::d($options, $debug);

   	if(array_key_exists('Article.id', $options['conditions'])) 
 	  	$results = $this->find('first', $options);
   	else {
   		$options['fields'] = ['Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.stato,Article.flag_presente_articlesorders,Article.created,Article.modified,SuppliersOrganization.id,SuppliersOrganization.owner_supplier_organization_id,SuppliersOrganization.name,CategoriesArticle.name,ArticlesArticlesType.organization_id,ArticlesArticlesType.article_id,ArticlesArticlesType.article_type_id'];
   		$options['group']  = ['Article.id,Article.organization_id,Article.supplier_organization_id'];
   		$results = $this->find('all', $options);
   	}
	self::d($results, $debug);
   	$this->set(compact('results'));
   	   
   	return $results;   
   }
   
   /*
    * Organization.type = 'GAS'
    * ottendo i dati anagrafici di un Articolo 
    * 		Article, ArticlesOrder 
    * 
    * call AjaxGasCart!!!!
   */
   public function getArticleDataAnagrArticlesOrder($user, $article_organization_id, $article_id, $order_id) {
   	 
		$this->unbindModel(['hasOne' => ['ArticlesArticlesType']]);
   		$this->unbindModel(['hasMany' => ['ArticlesArticlesType']]);
   	 	$this->unbindModel(['hasAndBelongsToMany' => ['ArticlesType']]);
   	 	
   	 	$this->hasOne['ArticlesOrder']['conditions'] = 'ArticlesOrder.article_organization_id = Article.organization_id and Article.organization_id = '. $article_organization_id.' and ArticlesOrder.order_id ='.$order_id;
   	 	$this->hasMany['ArticlesOrder']['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
   	 														'ArticlesOrder.article_organization_id' => $article_organization_id,
   	 														'ArticlesOrder.article_id' => $article_id,
   	 														'ArticlesOrder.order_id' => $order_id];
   	 	$this->hasAndBelongsToMany['Order']['conditions'] = ['Order.organization_id' => 'ArticlesOrder.organization_id',
															'Order.organization_id' => $user->organization['Organization']['id'],
															'ArticlesOrder.order_id' => $order_id];
   	 	
   		$options['conditions'] = ['Article.organization_id' => $article_organization_id,
   										'Article.id' => $article_id,
   										'ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
   										'ArticlesOrder.article_organization_id' => $article_organization_id,
   										'ArticlesOrder.order_id' => $order_id];
   	 
	   	$options['recursive'] = 1;
	   	$results = $this->find('first', $options);
	   	
	   	/*
	   	 * pulisco i dati
	   	 */
	   	$results['Order'] = $results['Order'][0];
	   	unset($results['Order'][0]);
	   	
	   	$this->set(compact('results'));
	   
	   	return $results;
   }
   
   /*
    *  Organization.type = 'PROD'
    *  ottendo i dati anagrafici di un Articolo
   * 		Article, ProdDeliveriesArticle
   *
   * call AjaxProdCart!!!!
   */
   public function getArticleDataAnagrProdDeliveriesArticle($user, $article_organization_id, $article_id, $prod_delivery_id) {

   	$this->unbindModel(['hasOne' => ['ArticlesArticlesType']]);
   	$this->unbindModel(['hasMany' => ['ArticlesArticlesType']]);
   	$this->unbindModel(['hasAndBelongsToMany' => ['ArticlesType']]);
   	
   	$this->hasOne['ProdDeliveriesArticle']['conditions'] = 'ProdDeliveriesArticle.organization_id = Article.organization_id and Article.organization_id = '. $user->organization['Organization']['id'].' and ProdDeliveriesArticle.prod_delivery_id ='.$prod_delivery_id;
   	$this->hasMany['ProdDeliveriesArticle']['conditions'] = ['ProdDeliveriesArticle.organization_id' => $user->organization['Organization']['id'],
													   			'ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id,
													   			'ProdDeliveriesArticle.article_organization_id' => $article_organization_id,
													   			'ProdDeliveriesArticle.article_id' => $article_id];
   	$this->hasAndBelongsToMany['ProdDelivery']['conditions'] = ['ProdDelivery.organization_id' => 'ProdDeliveriesArticle.organization_id',
												   			'ProdDelivery.organization_id' => $user->organization['Organization']['id'],
												   			'ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id];
   	
   	$options['conditions'] = ['Article.organization_id' => $user->organization['Organization']['id'],
							'ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id,
							'ProdDeliveriesArticle.article_organization_id' => $article_organization_id,
							'Article.id' => $article_id];
   	 
   	$options['recursive'] = 0;
   	$results = $this->find('first', $options);

   	/*
   	 * pulisco i dati
   	*/
   	$results['ProdDelivery'] = $results['ProdDelivery'][0];
   	unset($results['ProdDelivery'][0]);
   	 
   	$this->set(compact('results'));
   	
   	return $results;   	
   }
   
   /*
    *  ctrl se un articolo e' stato acquistato (INDIPENDENTEMENTE da un ORDINE se $order_id=0) => 
	*  	se true non posso Article.stato = N / Article DELETE
	*	per consistenza dati Orders, RequestPayments, prodGasArticleSyncronize
	*
	 * come ProdGasArticle::isArticleInCart()	
	*/
   public function isArticleInCart($user, $article_organization_id, $article_id, $order_id=0, $debug=false) {
		App::import('Model', 'Cart');
		$Cart = new Cart;
		
		$options = [];
		$options['conditions'] = ['Cart.organization_id' => $user->organization['Organization']['id'],
								   'Cart.article_organization_id' => $article_organization_id, 
								   'Cart.article_id' => $article_id];
		if(!empty($order_id)) 
			$options['conditions'] += ['Cart.order_id' => $order_id];
		$options['recursive'] = -1;
		$results = $Cart->find('count', $options);
		
		self::d("Article::isArticleInCart", $debug);
		self::d($options, $debug);
		self::d($results, $debug);
	
		if($results==0)
			return false;
		else
			return true;
   }
        
   /*
    * sincronizzo il campo Article.bio in base a isArticlesTypeBio()
    * 	se ho valorizzato ArticleType.bio o ArticleType.biodinamico
    * 
    * se article_id = 0 li eseguo tutti
    */
   public function syncronizeArticleTypeBio($user, $article_id=0, $debug=false) {
		
   		$isBio = false;
   	
	   	if(!empty($article_id)) {
	   		if (!$this->exists($user->organization['Organization']['id'], $article_id)) return;
	   		
	   		$this->_syncronizeArticleTypeBioExecute($user, $user->organization['Organization']['id'], $article_id, $debug);
	   	}
	   	else {
			$options = [];
			$options['conditions'] = ['Article.organization_id' => (int)$user->organization['Organization']['id']];
			$options['recursive'] = -1;							
	   		$options['order'] = ['Article.id'];
			self::d($options, $debug);
			
			$results = $this->find('all', $options);
			if($debug)
				echo "Totale articoli trovati ".count($results);
			foreach($results as $result) {
				$this->_syncronizeArticleTypeBioExecute($user, $result['Article']['organization_id'], $result['Article']['id'], $debug);
			}
	   	}
   }

   private function _syncronizeArticleTypeBioExecute($user, $organization_id, $article_id, $debug) {
	   	
	   	try {
		   	App::import('Model', 'ArticlesType');
		   	$ArticlesType = new ArticlesType;
		   	 
		   	App::import('Model', 'ArticlesArticlesType');
		   	$ArticlesArticlesType = new ArticlesArticlesType;
		   	
		   	if($debug) echo '<br />Tratto articolo id '.$article_id;
		   	
		   	/*
		   	 * estraggo tutti gli articleType di un articolo
		   	*/
		   	$resultsArticlesTypes = $ArticlesArticlesType->getArticlesArticlesTypes($user, $organization_id, $article_id);
		   	if(!empty($resultsArticlesTypes)) {
		   		foreach ($resultsArticlesTypes as $resultsArticlesType) {
		   			$tmp[] = $resultsArticlesType['ArticlesType'];
		   		}
		   		$resultsArticlesTypes['ArticlesType'] = $tmp;
		   	
		   		self::d($resultsArticlesTypes, $debug);
		   		 
		   		if($ArticlesType->isArticlesTypeBio($resultsArticlesTypes))
		   			$isBio = true;
		   		else
		   			$isBio = false;
		   	}
		   	else
		   		$isBio = false;
		   	
		   	if($isBio) $isBio = 'Y';
		   	else $isBio = 'N';
		   	
		   	/*
		   	 * aggiorno il campo Article.bio
		   	*/
		   	$sql = "UPDATE ".Configure::read('DB.prefix')."articles
					SET bio = '".$isBio."'
					WHERE organization_id = ".$user->organization['Organization']['id']." AND id = ".$article_id;
		   	self::d($sql, $debug);
		   	$results = $this->query($sql);
	   	}
	   	catch (Exception $e) {
	   		CakeLog::write('error',$sql);
	   		CakeLog::write('error',$e);
	   	}
   }
   
   /* 
    * ciclo per tutti gli articlesTypes
    * se articlesTypes.id esiste e non c'e' tra quelli passati => delete
    * se articlesTypes.id esiste e c'e' tra quelli passati => 
    * se articlesTypes.id non esiste e non c'e' tra quelli passati => 
    * se articlesTypes.id non esiste e c'e' tra quelli passati => insert
    */
   public function articlesTypesSave($results, $debug = false) {
   	
	 	self::d($results['Article'], $debug);		
	   		
	   	if(!empty($results['Article']['id'])) {

	   		try {	
				if(Cache::read('articlesTypes')===false) {
					App::import('Model', 'ArticlesType');
					$ArticlesType = new ArticlesType;
					$ArticlesTypeResults = $ArticlesType->prepareArray($ArticlesType->getArticlesTypes());
					Cache::write('articlesTypes',$ArticlesTypeResults);
				}
				else
					$ArticlesTypeResults = Cache::read('articlesTypes');	
		   			   			
	   			App::import('Model', 'ArticlesArticlesType');
	   			
	   			$article_id = $results['Article']['id'];
	   			$article_type_ids = explode(",", $results['Article']['article_type_id_hidden']);
	   			/*
	   			 * trasformo [0] => value in [value]=> value
	   			 */
	   			$tmp = [];
	   			foreach ($article_type_ids as $key => $value) {
	   				$tmp[$value] = $value;
	   			}
	   			$article_type_ids = $tmp;
	   			
	   			self::d('article_id '.$article_id, $debug);
	   			self::d('results[Article][article_type_id_hidden] '.$results['Article']['article_type_id_hidden'], $debug);
	   			self::d($article_type_ids, $debug);
	   			
	   			/*
	   			 * ciclo per tutti gli articlesType
	   			 * e li confronto con gli id passati
	   			 */
	   			foreach($ArticlesTypeResults as $key => $value) {
	   				
	   				self::d('---------------------------------------', $debug);
	   				/*
	   				 * resetto le variabili
	   				 */
	   				$data=[];
	   				$conditions=[];
	   				$ArticlesArticlesType = new ArticlesArticlesType;
	   				
	   				$article_type_id = $key;
	   				
	   				self::d('ciclo '.$article_type_id.' '.$value, $debug);
	   			
	   				if(in_array($article_type_id, $article_type_ids)) {
	
	   					self::d('match tra la key '.$article_type_id.' e gli id passati: ESISTE', $debug);
	   					
	   					/*
	   					 * ora e' passato, ctrl se non esiste gia', se Y => insert
	   					*/
	   					$conditions = ['ArticlesArticlesType.organization_id' => $results['Article']['organization_id'],
				   							'ArticlesArticlesType.article_type_id' => $article_type_id,
				   							'ArticlesArticlesType.article_id' => $article_id];
	   					$ArticlesArticlesTypeResults = $ArticlesArticlesType->find('first', ['conditions' => $conditions]);
	   					self::d($conditions, $debug);
	   					self::d($ArticlesArticlesTypeResults, $debug);
						
	   					if(empty($ArticlesArticlesTypeResults)) {
	   						self::d('INSERT', $debug);
	   						$data['ArticlesArticlesType']['organization_id'] = $results['Article']['organization_id'];
	   						$data['ArticlesArticlesType']['article_type_id'] = $article_type_id;
	   						$data['ArticlesArticlesType']['article_id'] = $article_id;
	   						self::d($data, $debug);
	   						
	   						$ArticlesArticlesType->save($data);
	   					}
	   					else 	
	   						self::d('nessun a operazione sul DB ', $debug);
	   					
	   				}
	   				else {
	   					self::d('match tra la key '.$article_type_id.' e gli id passati: NON ESISTE', $debug);
							   					
	   					/*
	   					 * ora non e' passato, ctrl se esiste gia', se Y => delete
	   					*/
	   					$conditions = ['ArticlesArticlesType.organization_id' => $results['Article']['organization_id'],
				   						'ArticlesArticlesType.article_type_id' => $article_type_id,
				   						'ArticlesArticlesType.article_id' => $article_id];
	   					$ArticlesArticlesTypeResults = $ArticlesArticlesType->find('first', ['conditions' => $conditions]);
	   					self::d($conditions, $debug);
						self::d($ArticlesArticlesTypeResults, $debug);
											
	   					if(!empty($ArticlesArticlesTypeResults)) {  
	   						self::d('DELETE ', $debug);
						   	$sql = "DELETE FROM ".Configure::read('DB.prefix')."articles_articles_types 
						   			WHERE 
						   				organization_id = ".(int)$ArticlesArticlesTypeResults['ArticlesArticlesType']['organization_id']." 
						   				AND article_id = ".(int)$ArticlesArticlesTypeResults['ArticlesArticlesType']['article_id']."
						   				AND article_type_id = ".(int)$ArticlesArticlesTypeResults['ArticlesArticlesType']['article_type_id'];
							$result = $this->query($sql);							
	   						self::d($data, $debug);
							
	   						$ArticlesArticlesType->delete();
	   					}
	   					else 
	   						self::d('nessun a operazione sul DB ', $debug);
	   				}   				
	   			} // foreach($ArticlesTypeResults as $key as $value) 
		   	}
		   	catch (Exception $e) {
		   		CakeLog::write('error',$sql);
		   		CakeLog::write('error',$e);
		   	}   
	   	} // end if(!empty($results['Article']['id']))
	   	else
	   		return false;
	   
   		return true;
   }
   	
   /*
    * se non ho attivato il modulo aggiornando l'articolo 
    * 		cerco gli eventuali ArticlesOrder, ctrl lo stato dell'ordine 
    * 	associati agli ordini (con stato_elaborazione != CLOSE)
	*
	* ctrl se il produttore e' associato ad ordine
	* 		se si => lo aggiungo in automatico
	*  	se e' ordine DES no
	*/ 
   public function syncronizeArticlesOrder($user, $article_organization_id, $article_id, $action, $debug=false) {

		$continue=true;
 	   		
   		self::d('Article::syncronizeArticlesOrder() - action '.$action.' article_id '.$article_id, $debug);
		
   		App::import('Model', 'ArticlesOrder');
  		$ArticlesOrder = new ArticlesOrder;
  			
   		App::import('Model', 'DesOrdersOrganization');
		   		
   		App::import('Model', 'OrderLifeCycle');
  		$OrderLifeCycle = new OrderLifeCycle;
		
		$stateCodeNotUpdateArticle = $OrderLifeCycle->getStateCodeNotUpdateArticleToSql($user);
					
   		/*
   		 * ottengo Article cosi' ho prezzo_
   		 */
		$options = []; 
   		$options['conditions'] = ['Article.organization_id' => $article_organization_id, 'Article.id' => $article_id];
   		$options['recursive'] = -1;
   		$article = $this->find('first', $options);
		self::d($article, $debug);
		
   		$results = [];
   		if($action!='INSERT') {
		
	   		/*
	   		 * estraggo gli eventuali ordini associati all'articolo
			 * non filtro per $user->organization['Organization']['id'] cosi' estraggo tutti gli articoli associati all'articolo anche x DES / PRODGAS
	   		* */
	   		$sql = "SELECT
						ArticlesOrder.* 
					FROM
						".Configure::read('DB.prefix')."articles_orders ArticlesOrder,
						".Configure::read('DB.prefix')."articles Article,
						".Configure::read('DB.prefix')."orders `Order`
	   				WHERE
						`Order`.organization_id =  ArticlesOrder.organization_id 
						and `Order`.id =  ArticlesOrder.order_id
	   					and ArticlesOrder.article_organization_id = Article.organization_id 
	   					and Article.id = ArticlesOrder.article_id
						and `Order`.state_code NOT IN (".$stateCodeNotUpdateArticle.") 
						and Article.organization_id = ".$article['Article']['organization_id']."  
						and Article.id = ".$article['Article']['id'];
	   	    self::d('Article::syncronizeArticlesOrder() - '.$sql, $debug);
	  		try {
	  			$results = $this->query($sql);
	  		}
	  		catch (Exception $e) {
	  			CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
	  		}
   		} // end if($action!='INSERT')
	
  		/*
  		 * se l'articolo non esiste ma Article.stato = Y da UPDATE -> INSERT
  		 * ricavando i diversi ordini del produttore
  		 */
  		if($action=='INSERT' || (count($results)==0 && $action=='UPDATE')) {
  			
  			$action = 'INSERT';
  			
			/*
			 * estraggo dati SuppliersOrganization
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;	

			$options = []; 
			$options['conditions'] = ['SuppliersOrganization.id' => $article['Article']['supplier_organization_id']];
			$options['fields'] = ['SuppliersOrganization.owner_supplier_organization_id', 'SuppliersOrganization.owner_organization_id'];
			$options['recursive'] = -1;
			$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
			self::d($suppliersOrganizationResults, $debug);  
			*/
									 
			/*
			 * estraggo eventuali Orders di quel produttore 
			 */
			App::import('Model', 'Order');
			$Order = new Order;	

			$options = []; 
			$options['conditions'] = ['Order.supplier_organization_id' => $article['Article']['supplier_organization_id'], 
								      'Order`.state_code NOT IN ('.$stateCodeNotUpdateArticle.')'];
			$options['recursive'] = -1;
			$results = $Order->find('all', $options);
			self::d($results, $debug);
			
  		}	// case INSERT
  			
	   	$row = []; 
	   	switch ($action) {
	   		case 'DELETE':
		   		/*
		   		 * delete, cancello articolo da eventuali ordini associati se NON ci sono acquisti
		   		 */
				App::import('Model', 'ArticlesOrder');
				$ArticlesOrder = new ArticlesOrder;
					
				$options = [];
				$options['conditions'] = ['ArticlesOrder.article_organization_id' => $article['Article']['organization_id'], 'ArticlesOrder.article_id' => $article['Article']['id']];
				$options['group'] = ['Order.id'];
				$options['recursive'] = 1;
				$articlesOrderResults = $ArticlesOrder->find('all', $options);
				
				if(!empty($articlesOrderResults))
				foreach($articlesOrderResults as $numResult => $articlesOrderResult) {

					$article_organization_id = $articlesOrderResult['ArticlesOrder']['article_organization_id'];
					$article_id = $articlesOrderResult['ArticlesOrder']['article_id'];

					/*
					 * ctrl gli eventuali acquisti gia' effettuati, se true non posso cancellarlo
					 */ 
					switch($user->organization['Organization']['type']) {
						case 'PROD':
						break;
						case 'PRODGAS':        
							App::import('Model', 'ProdGasArticle');
							$ProdGasArticle = new ProdGasArticle;
						
							$isArticleInCart = $ProdGasArticle->isArticleInCart($user, $article_organization_id, $article_id);
						break;
						case 'GAS':
							$isArticleInCart = $this->isArticleInCart($user, $article_organization_id, $article_id);
						break;
						default:
							self::x(__('msg_error_org_type').' ['.$user->organization['Organization']['type'].']');
						break;
					}
		
					if(!$isArticleInCart) {
						if($continue) {
							if (!$ArticlesOrder->delete($articlesOrderResult['ArticlesOrder']['organization_id'], $articlesOrderResult['ArticlesOrder']['order_id'], $articlesOrderResult['ArticlesOrder']['article_organization_id'], $articlesOrderResult['ArticlesOrder']['article_id']))
								$continue = false;
						}						
					}
				} // loop $articlesOrderResults 

   			break;
	  		case 'UPDATE':
		   		/*
		   		 * update, modificato un articolo
		   		*/
   				foreach($results as $result) {

					/*
					 * ctrl se DES, se Y non lo aggiungo
					 */		
					$isDesOrder = 'N';
					if($user->organization['Organization']['hasDes']=='Y') {		
			
						$DesOrdersOrganization = new DesOrdersOrganization();
						
						$options = [];
						$options['conditions'] = ['DesOrdersOrganization.order_id' => $result['ArticlesOrder']['order_id'],
												  'DesOrdersOrganization.organization_id' => $user->organization['Organization']['id']];
						$options['recursive'] = -1;
						$isDesOrder = $DesOrdersOrganization->find('count', $options);

						if($isDesOrder==0)
							$isDesOrder = 'N';
						else
							$isDesOrder = 'Y';	
					} // DES
			
	   				if($continue && $isDesOrder == 'N') {
	   					$row['ArticlesOrder']['organization_id'] = $result['ArticlesOrder']['organization_id'];
	   					$row['ArticlesOrder']['order_id'] = $result['ArticlesOrder']['order_id'];
		   				$row['ArticlesOrder']['article_organization_id'] = $article['Article']['organization_id'];
		   				$row['ArticlesOrder']['article_id'] = $article['Article']['id'];
		   				$row['ArticlesOrder']['name'] = $article['Article']['name'];
		   				$row['ArticlesOrder']['prezzo']     = $article['Article']['prezzo_'];  // in ArticlesOrder->save() importoToDatabase()
		   				$row['ArticlesOrder']['pezzi_confezione'] = $article['Article']['pezzi_confezione'];
		   				$row['ArticlesOrder']['qta_minima'] = $article['Article']['qta_minima'];
		   				$row['ArticlesOrder']['qta_massima'] = $article['Article']['qta_massima'];
		   				$row['ArticlesOrder']['qta_minima_order'] = $article['Article']['qta_minima_order'];
		   				$row['ArticlesOrder']['qta_massima_order'] = $article['Article']['qta_massima_order'];
		   				$row['ArticlesOrder']['qta_multipli'] = $article['Article']['qta_multipli'];
		   				$row['ArticlesOrder']['alert_to_qta'] = $article['Article']['alert_to_qta'];
		   				$ArticlesOrder->create();
		   				try {
		   					self::d("Article::syncronizeArticlesOrder() - SAVE", $debug);
		   					self::d($row, $debug);
		   					$ArticlesOrder->save($row);
		   				}
		   				catch (Exception $e) {
		   					$continue = false;
		   					CakeLog::write('error',$e);
		   				}			
		   			}
					else {
						self::d("Article associato all'ordine ".$result['ArticlesOrder']['order_id']." di tipo DesOrder => non aggiorno", $debug);						
					}
		 		} // end foreach
		 	break;
	  		case 'INSERT':
	    			
	  			foreach($results as $result) {
	  					
					/*
					 * ctrl se DES, se Y non lo aggiungo
					 */		
					$isDesOrder = 'N';
					if($user->organization['Organization']['hasDes']=='Y') {		
			
						$DesOrdersOrganization = new DesOrdersOrganization();
						
						$options = [];
						$options['conditions'] = ['DesOrdersOrganization.order_id' => $result['ArticlesOrder']['order_id'],
												   'DesOrdersOrganization.organization_id' => $user->organization['Organization']['id']];
						$options['recursive'] = -1;
						$isDesOrder = $DesOrdersOrganization->find('count', $options);
	
						if($isDesOrder==0)
							$isDesOrder = 'N';
						else
							$isDesOrder = 'Y';	
					} // DES
					
	  				if($continue && $isDesOrder == 'N') {
			  			$row['ArticlesOrder']['organization_id'] = $result['Order']['organization_id'];
			  			$row['ArticlesOrder']['order_id'] = $result['Order']['id'];
			  			$row['ArticlesOrder']['article_organization_id'] = $article['Article']['organization_id'];
			  			$row['ArticlesOrder']['article_id'] = $article['Article']['id'];
			  			$row['ArticlesOrder']['name'] = $article['Article']['name'];
			  			$row['ArticlesOrder']['prezzo']     = $article['Article']['prezzo_']; // in ArticlesOrder->save() importoToDatabase()
			  			$row['ArticlesOrder']['pezzi_confezione'] = $article['Article']['pezzi_confezione'];
			  			$row['ArticlesOrder']['qta_minima'] = $article['Article']['qta_minima'];
			  			$row['ArticlesOrder']['qta_massima'] = $article['Article']['qta_massima'];
			  			$row['ArticlesOrder']['qta_minima_order'] = $article['Article']['qta_minima_order'];
			  			$row['ArticlesOrder']['qta_massima_order'] = $article['Article']['qta_massima_order'];
			  			$row['ArticlesOrder']['qta_multipli'] = $article['Article']['qta_multipli'];
			  			$row['ArticlesOrder']['alert_to_qta'] = $article['Article']['alert_to_qta'];

			  			$ArticlesOrder->create();
	  					try {
		   					self::d("Article::syncronizeArticlesOrder() - SAVE", $debug);
		   					self::d($row, $debug);
		   					$ArticlesOrder->save($row);
		   				}
		   				catch (Exception $e) {
		   					$continue = false;
		   					CakeLog::write('error',$e);
		   				}		
			  		}
					else {
						self::d("Article associato all'ordine ".$result['ArticlesOrder']['order_id']." di tipo DesOrder => non inserisco", $debug);					
					}					
			  	} // end foreach			
	  		break;
   		} // end switch($action)

   		if($debug) exit;
   		
   		return $continue;
   }

   public function copy_prepare($user, $article_id, $article_organization_id, $debug=false) {

	   /*
	    * dati articolo precedente
	   */
	   $this->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = '.$user->organization['Organization']['id'];
	   $this->hasMany['ArticlesArticlesType']['conditions'] = ['ArticlesArticlesType.organization_id' => $user->organization['Organization']['id']];
	   $this->hasAndBelongsToMany['ArticlesType']['conditions'] = ['ArticlesArticlesType.organization_id' => $user->organization['Organization']['id']];
	   
	   $this->unbindModel(['hasOne' => ['ArticlesOrder']]);
	   $this->unbindModel(['hasMany' => ['ArticlesOrder']]);
	   $this->unbindModel(['hasAndBelongsToMany' => ['Order']]);
	   $this->unbindModel(['belongsTo' => ['SuppliersOrganization', 'CategoriesArticle']]);
	   
	   $options = [];
	   $options['conditions'] = ['Article.organization_id' => $article_organization_id,
	   						     'Article.id' => $article_id];
	   $options['recursive'] = 1;
	   $results = $this->find('first', $options);
	   
		self::d('articolo da copiare', $debug);
		self::d($options, $debug);
		self::d($results, $debug);
   
	   $results['Article']['id'] = $this->getMaxIdOrganizationId($user->organization['Organization']['id']);
	   	
	   //$results['Article']['stato'] = 'N';
	   //$results['Article']['name'] = $results['Article']['name'].' copia';

	   return $results;
   }

   /*
    * gestione immagine
    * $results['Article']
    */
   public function copy_img($user, $organization_id_master, $results, $debug=false) {

	   if(!empty($results['Article']['img1'])) {
	   		
		   	$pathDa = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$organization_id_master;
		   	$pathA = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$results['Article']['organization_id'];
		   	if($debug) echo '<br />Copy File DA $pathDa '.$pathDa.' file '.$results['Article']['img1'];
		   	if($debug) echo '<br />Copy File A $pathA '.$pathA.' file '.$results['Article']['img1'];
		   		
		   	$file = new File($pathDa . DS . $results['Article']['img1']);
		   	if ($file->exists()) {
		   		$ext = strtolower(pathinfo($results['Article']['img1'], PATHINFO_EXTENSION));
		   		$newFile = $results['Article']['id'].'.'.$ext;
		   		if($debug) echo '<br />ext '.$ext;
		   		if($debug) echo '<br />newFile '.$newFile;
		   		$file->copy($pathA . DS . $newFile);
		   
		   		$results['Article']['img1'] = $newFile;
		   	}
		   	else {
		   		if($debug) echo '<br />Nessuna copia del file '.$pathDa.DS.$results['Article']['img1'].' non esiste';
		   	}
	   }
	   else {
	   		if($debug) echo '<br />Nessuna copia del file, campi Article.img1 empty';
	   }
	   
	   return $results;
	}
	 
   /*
    * Articles Type
    * $results['Article']
    */
   public function copy_article_type($user, $results, $debug=false) {

	   $tmp = "";
	   if(!empty($results['ArticlesType'])) {
		   	foreach ($results['ArticlesType'] as $articleType)
		   		$tmp .= $articleType['id'].',';

		   	if(!empty($tmp))
		   		$tmp = substr($tmp, 0, strlen($tmp)-1);
	   		
		   	$results['Article']['article_type_id_hidden'] = $tmp;
		   	self::d('results[Article][article_type_id_hidden] '.$results['Article']['article_type_id_hidden'], $debug);
		   	$this->articlesTypesSave($results, $debug);
	   } // end if(!empty($results['ArticlesType']))
	   	   	
	   return $results;
   }
   
   function owner_articles($field=[]) {
   
   		$return = false;
   		$suppliersOrganizationResults = [];
   
		foreach( $field as $key => $value) {
			
			$supplier_organization_id = $value;

			/*
			 * estraggo dati SuppliersOrganization
			 */
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;	

			$options = []; 
			$options['conditions'] = ['SuppliersOrganization.id' => $supplier_organization_id,
									  'SuppliersOrganization.organization_id' => $this->data[$this->alias]['organization_id']];
			$options['recursive'] = -1;
			$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
			self::d($suppliersOrganizationResults); 
			
		}

		switch ($suppliersOrganizationResults['SuppliersOrganization']['owner_articles']) {
			case 'REFERENT':
			case 'REFERENT-TMP':
				$return = true;
			break;
			case 'SUPPLIER':
			case 'DES':
				$return = false;
			break;
			default:
				self::x(__('msg_error_supplier_organization_owner_articles'));
			break;
		}

		return $return;		
	}
	   
   public $validate = [
		'supplier_organization_id' => [
			'notempty' => [
				'rule' => ['naturalNumber', false],
				'message' => "Scegli il produttore da associare all'ordine"
			],
			'isValid' => [
				'rule' =>  ['owner_articles'],
				'message' => "Per il produttore scelto non puoi gestire gli articoli"
			],
		],
		'category_article_id' => [
			'notempty' => [
					'rule' => ['notBlank', false],
					'message' => 'Indica la quantità minima che si può acquistabile',
			],
		],		
		'name' => [
			'notempty' => [
				'rule' => ['notBlank'],
				'message' => "Indica il nome dell'articolo",
			],
		],
		'qta' => [
			'notempty' => [
				'rule' => ['notBlank'],
				'message' => "Indica la quantità dell'articolo",
			],
		],
		'prezzo' => [
			'rule' => ['decimal', 2],
			'message' => "Indica il prezzo dell'articolo con un valore numerico con 2 decimali (1,00)",
		],		
		'qta_minima' => [
			'notempty' => [
				'rule' => ['notBlank', false],
				'message' => 'Indica la quantità minima che un gasista può acquistare',
			],
			'numeric' => [
				'rule' => ['naturalNumber', false],
				'message' => "La quantità minima che un gasista può acquistare dev'essere indicata con un valore numerico maggiore di zero",
				'allowEmpty' => false,
			],
		],
		'qta_massima' => [
			'notempty' => [
				'rule' => ['notBlank', false],
				'message' => 'Indica la quantità massima che un gasista può acquistare',
			],
			'numeric' => [
					'rule' => ['numeric', false],
					'message' => "La quantità massima che un gasista può acquistare dev'essere indicata con un valore numerico",
					'allowEmpty' => true,
			],				
		],
		'qta_minima_order' => [
			'notempty' => [
					'rule' => ['notBlank', false],
					'message' => "Indica la quantità minima rispetto a tutti gli acquisti dell'ordine",
			],		
			'numeric' => [
				'rule' => ['numeric', false],
				'message' => "La quantità minima rispetto a tutti gli acquisti dell'ordine dev'essere indicata con un valore numerico",
				'allowEmpty' => true,
			],
		],
		'qta_massima_order' => [
			'notempty' => [
					'rule' => ['notBlank', false],
					'message' => "Indica la quantità massima rispetto a tutti gli acquisti dell'ordine",
			],		
			'numeric' => [
				'rule' => ['numeric', false],
				'message' => "La quantità massima rispetto a tutti gli acquisti dell'ordine dev'essere indicata con un valore numerico",
				'allowEmpty' => true,
			],
		],
		'pezzi_confezione' => [
			'notempty' => [
					'rule' => ['notBlank', false],
					'message' => 'Indica il numero di pezzi che può contenere una confezione',
			],
			'numeric' => [
					'rule' => ['naturalNumber', false],
					'message' => "Il numero di pezzi che può contenere una confezione dev'essere indicato con un valore numerico maggiore di zero",
					'allowEmpty' => false,
			],
		],
		'qta_multipli' => [
			'notempty' => [
					'rule' => ['notBlank', false],
					'message' => 'Indica la quantità minima che si può acquistabile',
			],		
			'numeric' => [
					'rule' => ['naturalNumber', false],
					'message' => "La quantità multipla dev'essere indicata con un valore numerico maggiore di zero",
					'allowEmpty' => true,
			],
		],
		'alert_to_qta' => [
			'numeric' => [
					'rule' => ['numeric', false],
					'message' => "Il valore che indica quando avvisare raggiunta la quantità di massima dev'essere indicato con un valore numerico",
					'allowEmpty' => true,
			],
		],				
	];

	public $belongsTo = [
		'SuppliersOrganization' => [
			'className' => 'SuppliersOrganization',
			'foreignKey' => '', // supplier_organization_id la key e' la conditions 
			'conditions' => 'SuppliersOrganization.owner_supplier_organization_id = Article.supplier_organization_id and SuppliersOrganization.owner_organization_id = Article.organization_id', 
			'fields' => '',
			'order' => ''
		],
        'CategoriesArticle' => [
            'className' => 'CategoriesArticle',
            'foreignKey' => 'category_article_id',
            'conditions' =>  'CategoriesArticle.organization_id = Article.organization_id',
            'fields' => '',
            'order' => '',
		]
	];

	public $hasMany = [
		'ArticlesArticlesType' => [
				'className' => 'ArticlesArticlesType',
				'foreignKey' => 'article_id',
				'dependent' => false,
				'conditions' =>  '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'exclusive' => '',
				'finderQuery' => '',
				'counterQuery' => ''
		],
		'ArticlesOrder' => [
				'className' => 'ArticlesOrder',
				'foreignKey' => 'article_id',
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

	public $hasAndBelongsToMany = [
		'ArticlesType' => [
				'className' => 'ArticlesType',
				'joinTable' => 'articles_articles_types',
				'foreignKey' => 'article_id',
				'associationForeignKey' => 'article_type_id',
				'unique' => 'keepExisting',
				'conditions' =>  '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'finderQuery' => '',
		],
		'Order' => [
			'className' => 'Order',
			'joinTable' => 'articles_orders',
			'foreignKey' => 'article_id',
			'associationForeignKey' => 'order_id',
			'unique' => 'keepExisting',
			'conditions' =>  ['Order.organization_id = ArticlesOrder.organization_id'],
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		]
	];
	
	public $hasOne = [
		'ArticlesArticlesType' => ['conditions' => ['ArticlesArticlesType.organization_id = Article.organization_id']],
		'ArticlesOrder' => ['conditions' => ['ArticlesOrder.article_organization_id = Article.organization_id']]
	];
	
	public function afterFind($results, $primary = false) {

		foreach ($results as $key => $val) {
			if(!empty($val)) {

				if(isset($val['Article']['prezzo'])) {
					$results[$key]['Article']['prezzo_'] = number_format($val['Article']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Article']['prezzo_e'] = $results[$key]['Article']['prezzo_'].' &euro;';
				}
				else
					/*
					 * se il find() arriva da $hasAndBelongsToMany
					*/
				 if(isset($val['prezzo'])) {
					$results[$key]['prezzo_'] = number_format($val['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['prezzo_e'] = $results[$key]['prezzo_'].' &euro;';
				}
				
				/*
				 * qta, da 1.00 a 1
				 * 		da 0.75 a 0,75  
				 * */
				if(isset($val['Article']['qta'])) {
					$qta = str_replace(".", ",", $val['Article']['qta']);
					$arrCtrlTwoZero = explode(",",$qta);
					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
					$results[$key]['Article']['qta_'] = $qta;
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/	
				if(isset($val['qta'])) {
					$qta = str_replace(".", ",", $val['qta']);
					$arrCtrlTwoZero = explode(",",$qta);
					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
					$results[$key]['qta_'] = $qta;
				}

				if(isset($val['ArticlesOrder'])) {

					if(isset($val['ArticlesOrder']['prezzo'])) {
						$results[$key]['ArticlesOrder']['prezzo_'] = number_format($val['ArticlesOrder']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$results[$key]['ArticlesOrder']['prezzo_e'] = $results[$key]['ArticlesOrder']['prezzo_'].' &euro;';
					}
					else
					if(isset($val['ArticlesOrder'][0]['prezzo'])) {
						$results[$key]['ArticlesOrder'][0]['prezzo_'] = number_format($val['ArticlesOrder'][0]['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$results[$key]['ArticlesOrder'][0]['prezzo_e'] = $results[$key]['ArticlesOrder'][0]['prezzo_'].' &euro;';
					}	
				}
				
				/*
				 * ACL
				 */
				if(isset($val['SuppliersOrganization']['owner_articles'])) {
					switch ($val['SuppliersOrganization']['owner_articles']) {
						case 'REFERENT':
						case 'REFERENT-TMP':
							$results[$key]['Article']['owner'] = true;
						break;
						case 'SUPPLIER':
						case 'DES':
							$results[$key]['Article']['owner'] = false;
						break;
						default:
							$results[$key]['Article']['owner'] = false;
						break;
					} 
				}				
			}
		}
		
		return $results;
	}	
}