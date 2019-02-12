<?php
App::uses('AppController', 'Controller');

class ProdGasArticlesSyncronizesController extends AppController {
						
	private $userOrganization; // ottengo i dati del GAS, x es per sapere se Organization.hasDes
									
	public function beforeFilter() {
		parent::beforeFilter();
		
		/* ctrl ACL */
		if($this->user->organization['Organization']['type']!='PRODGAS') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	
		
		if(isset($this->request->params['pass']['organization_id']))
			$organization_id = $this->request->params['pass']['organization_id'];
		
		if (!empty($organization_id)) {
			App::import('Model', 'ProdGasSupplier');
			$ProdGasSupplier = new ProdGasSupplier;
		
			$organizationResults = $ProdGasSupplier->getOrganizationAssociate($this->user, $organization_id, 0);
			if($organizationResults['SuppliersOrganization']['owner_articles']!='SUPPLIER') {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));			
			}
			$this->set(compact('organization_id'));
			
			$this->userOrganization = $ProdGasSupplier->getUserOrganization($organization_id);			
		}
		// ACL		
	}
	
	/*
	 * type SIMPLE / ADVANCED
	 */
	public function admin_index() { 
	
		$debug = false;
		
		/*
		 * se il produttore non ha neppure un GAS owner_articles = 'SUPPLIER' lo blocco
		 */
		$permission_to_continue = false;
		
		if(isset($this->request->params['pass']['type']))
			$type = $this->request->params['pass']['type'];
		else
			$type='SIMPLE';
		
		if(isset($this->request->params['pass']['organization_id']))
			$organization_id = $this->request->params['pass']['organization_id'];
		else
			$organization_id=0;
	
		if(empty($organization_id)) {
			
			App::import('Model', 'ProdGasSupplier');
			$ProdGasSupplier = new ProdGasSupplier;
		
			$organizationsResults = $ProdGasSupplier->getOrganizationsArticlesSupplierList($this->user, $debug);
			if(empty($organizationsResults)) {
				$permission_to_continue = false;
			}
			else {
				if(count($organizationsResults)==1) 
					$organization_id = key($organizationsResults);
				else
					$organization_id = 0;
				$this->set('organization_id', $organization_id);
				$this->set(compact('organizationsResults'));
				
				$permission_to_continue = true;
			}
		
			$this->set(compact('permission_to_continue'));
		}
		else {
		
			$permission_to_continue = true; // in beforeFilter() ctrl che il GAS sia owner_articles = 'SUPPLIER'
			
			/*
			 * get elenco Article del GAS ed eventuali ArticlesOrder: se c'e' non posso cancellarlo
			 */	
			App::import('Model', 'Article');
			$Article = new Article;
			
			App::import('Model', 'ProdGasArticle');
			$ProdGasArticle = new ProdGasArticle;
			
			/*
			 * non funge non fa unbindModel ArticlesOrder e duplica i rows => $options['recursive'] = -1;
			 */
			$Article->unbindModel(['belongsTo' => ['SuppliersOrganization', 'CategoriesArticle']],
								  array('hasMany' => array('ArticlesOrder','ArticlesArticlesType')),
								  array('hasAndBelongsToMany' => array('ArticlesType','Order')),
								  array('hasOne' => array('ArticlesArticlesType','ArticlesOrder')));
			$Article->bindModel(array('belongsTo' => array('ProdGasArticle' => array(
															'className' => 'ProdGasArticle',
															'foreignKey' => 'prod_gas_article_id'))));														
			$options = [];
			$options['conditions'] = array('Article.organization_id' => $organization_id,
										   'Article.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']);
			$options['order'] = array('Article.name');
			$options['recursive'] = -1;
			$articlesResults = $Article->find('all', $options);	
			/*
			echo "<pre>";
			print_r($articlesResults);
			echo "</pre>";
			*/			
			$prod_gas_article_ids = '';
			$tmp_user->organization['Organization']['id'] =  $organization_id;
			foreach($articlesResults as $numResult => $articlesResult) {
				
				$options = [];
				$options['conditions'] = array('ProdGasArticle.id' => $articlesResult['Article']['prod_gas_article_id'],
											   'ProdGasArticle.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']);
				$options['recursive'] = -1;
				$prodGasArticlesResults = $ProdGasArticle->find('first', $options);	

				$articlesResults[$numResult]['ProdGasArticle'] = $prodGasArticlesResults['ProdGasArticle'];
							
				/*
				 * ctrl se l'articolo e' stato ordinato
				 */
				 $articlesResults[$numResult]['ArticlesOrder'] = $this->_isArticleInArticlesOrder($articlesResult['Article']['organization_id'], $articlesResult['Article']['id']);
				
				/*
				 * ctrl che l'articolo sia acquistato
				 */
				$articlesResults[$numResult]['isArticleInCart'] = $Article->isArticleInCart($tmp_user, $articlesResult['Article']['organization_id'], $articlesResult['Article']['id']);
				
				$prod_gas_article_ids .= $articlesResult['Article']['prod_gas_article_id'].',';
			}
			$this->set(compact('articlesResults'));
					
			if(!empty($prod_gas_article_ids))
				$prod_gas_article_ids = substr($prod_gas_article_ids, 0, strlen($prod_gas_article_ids)-1);
			

			/*
			 * get elenco ProdGasArticles non ancora associati
			 */	
			App::import('Model', 'ProdGasArticle');
			$ProdGasArticle = new ProdGasArticle;

			$ProdGasArticle->unbindModel(array('hasMany' => array('ProdGasArticlesPromotion')));
			$options = [];
			$options['conditions'] = array('ProdGasArticle.supplier_id' => $this->user->organization['Supplier']['Supplier']['id'],
										   'ProdGasArticle.stato' => 'Y');
			if(!empty($prod_gas_article_ids))							   
				$options['conditions'] += array("NOT" => array( "ProdGasArticle.id" => explode(',', $prod_gas_article_ids)));
			$options['order'] = array('ProdGasArticle.name');			
			$prodGasArticlesResults = $ProdGasArticle->find('all', $options);
			$this->set(compact('prodGasArticlesResults'));
			/*
			echo "<pre>prodGasArticlesResults \n";
			print_r($prodGasArticlesResults);
			echo "</pre>";
			*/		
			
			/*
			 * get elenco categorie
			*/
			App::import('Model', 'Article');
			$Article = new Article;
			
			$conditionsCategoriesArticle = array('organization_id' => $organization_id);
			$categories = $Article->CategoriesArticle->generateTreeList($conditionsCategoriesArticle, null, null, '&nbsp;&nbsp;&nbsp;');
			$this->set(compact('categories'));
			
			/*
			 * articoli del GAS non associati 
			 */
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = [];
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $organization_id,
										   'SuppliersOrganization.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']);
			$options['field'] = array('SuppliersOrganization.id');
			$options['recursive'] = -1;
			$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
			/*
			echo "<pre>";
			print_r($options);
			print_r($suppliersOrganizationResults);
			echo "</pre>";
			*/
			$options = [];
			$options['conditions'] = array('Article.organization_id' => $organization_id,
										   'Article.supplier_organization_id' => $suppliersOrganizationResults['SuppliersOrganization']['id'],
										   'Article.supplier_id' => 0,
										   'Article.prod_gas_article_id' => 0,
										   'Article.stato' => 'Y');
			$options['order'] = array('Article.name');
			$options['recursive'] = -1;
			$articlesToAssociateResults = $Article->find('all', $options);	
			/*
			echo "<pre>";
			print_r($options);
			print_r($articlesToAssociateResults);
			echo "</pre>";
			*/
			$articlesToAssociateListResults = [];
			
			if(!empty($articlesToAssociateResults)) {
				
				$ProdGasArticle->unbindModel(array('hasMany' => array('ProdGasArticlesPromotion')));
				$options = [];
				$options['conditions'] = array('ProdGasArticle.supplier_id' => $this->user->organization['Supplier']['Supplier']['id'],
											   'ProdGasArticle.stato' => 'Y');
				$options['order'] = array('ProdGasArticle.name');
				$options['recursive'] = -1;										   
				$prodGasArticlesAllResults = $ProdGasArticle->find('all', $options);
				$this->set(compact('prodGasArticlesAllResults'));
				/*
				echo "<pre>prodGasArticlesAllResults \n";
				print_r($prodGasArticlesAllResults);
				echo "</pre>";
				*/		
		
				foreach($articlesToAssociateResults as $articlesToAssociateResult) {
					$articlesToAssociateListResults[$articlesToAssociateResult['Article']['id']] = $articlesToAssociateResult['Article']['name'].' '.$articlesToAssociateResult['Article']['qta'].' '.$articlesToAssociateResult['Article']['um'].' '.$articlesToAssociateResult['Article']['prezzo_e']; 
				}
				$this->set(compact('articlesToAssociateListResults'));
				
			} // end if(!empty($articlesToAssociateResults))
			$this->set(compact('articlesToAssociateListResults'));
				
			/*
			 * dati GAS con il quale sto lavorando
			 */
			App::import('Model', 'Organization');
			$Organization = new Organization;
			
			$options = [];
			$options['conditions'] = array('Organization.id' => $organization_id);
			$organizations = $Organization->find('first', $options);
			$this->set(compact('organizations'));
		
		} // end if(empty($organization_id)) 
			
		$this->set(compact('type'));
		$this->set(compact('permission_to_continue'));
		$this->set(compact('organization_id')); 
		
		switch($type) {
			case "SIMPLE":
				$this->render('/ProdGasArticlesSyncronizes/admin_index');
			break;
			case "ADVANCED":
				$this->render('/ProdGasArticlesSyncronizes/admin_index_advanced');
			break;
		}		 
	}	

	/*
	 * 
	 */
	public function admin_index_articles_orders() { 
	
		$debug = false;
	
		$organizationsResults = [];
		$orderResults = [];	
		$organization_id = 0;
		$order_id = 0;
		if(isset($this->request->params['pass']['organization_id']))
			$organization_id = $this->request->params['pass']['organization_id'];
		else
			$organization_id=0;
		if(isset($this->request->params['pass']['order_id']))
			$order_id = $this->request->params['pass']['order_id'];
		else
			$order_id=0;

		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;
			
		/*
		 * se il produttore non ha neppure un GAS owner_articles = 'SUPPLIER' lo blocco
		 */
		$permission_to_continue = false;
	
		$organizationsResults = $ProdGasSupplier->getOrganizationsArticlesSupplierList($this->user, $debug);
		if($debug) {
			echo "<pre>getOrganizationsArticlesSupplierList \n ";
			print_r($organizationsResults);
			echo "</pre>";
		}
		if(empty($organizationsResults)) 
			$permission_to_continue = false;
		else 
			$permission_to_continue = true;
				
		if($permission_to_continue) { // in beforeFilter() ctrl che il GAS sia owner_articles = 'SUPPLIER'

			if(!empty($organization_id)) {
				$orderTmpResults = $ProdGasSupplier->getDeliveriesWhitOrders($this->user, $organization_id, 0, $debug);
				if($debug) {
					echo "<pre>getDeliveriesWhitOrders \n ";
					print_r($orderTmpResults);
					echo "</pre>";
				}
				if(!empty($orderTmpResults)) {
					foreach($orderTmpResults as $orderTmpResult) {
						$orderResults[$orderTmpResult['Order']['id']] = $orderTmpResult['Delivery']['luogoData'];
					}
				}
			}
		
			if(!empty($organization_id) && !empty($order_id)) {
			
				$tmp_user->organization['Organization']['id'] =  $organization_id;
			
				/*
				 * get elenco Article del GAS ed eventuali ArticlesOrder: se c'e' non posso cancellarlo
				 */	
				App::import('Model', 'ProdGasArticle');
				$ProdGasArticle = new ProdGasArticle;
				
				App::import('Model', 'Article');
				$Article = new Article;
				
				/*
				 * dati ordine
				 */
				App::import('Model', 'Order');
				$Order = new Order;
				
				$options = [];
				$options['conditions'] = array('Order.organization_id' => $organization_id,
											   'Order.id' => $order_id);
				$options['recursive'] = -1;
				$orderCurrentResults = $Order->find('first', $options);
								
				App::import('Model', 'ArticlesOrder');
				$ArticlesOrder = new ArticlesOrder;

				$conditions = ['Order.id' => (int) $order_id,
							   'Article.supplier_id' => (int) $this->user->organization['Supplier']['Supplier']['id'],
							  ];
				$articlesResults = $ArticlesOrder->getArticlesOrdersInOrder($tmp_user, $conditions);
			
				if($debug) {
					echo "<pre>getArticlesOrdersInOrder \n ";
					print_r($conditions);
					print_r($articlesResults);
					echo "</pre>";
				}
				$article_id_da_escludere = '';	
				foreach($articlesResults as $numResult => $articlesResult) {
	
					$options = [];
					$options['conditions'] = array('ProdGasArticle.id' => $articlesResult['Article']['prod_gas_article_id'],
												   'ProdGasArticle.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']);
					$options['recursive'] = -1;
					$prodGasArticlesResults = $ProdGasArticle->find('first', $options);	

					$articlesResults[$numResult]['ProdGasArticle'] = $prodGasArticlesResults['ProdGasArticle'];
								
					/*
					 * ctrl che l'articolo sia acquistato
					 */
					$articlesResults[$numResult]['isArticleInCart'] = $Article->isArticleInCart($tmp_user, $articlesResult['Article']['organization_id'], $articlesResult['Article']['id'], $order_id, $debug);
					
					$article_id_da_escludere .= $articlesResult['Article']['id'] . ',';	
				}
				$this->set(compact('articlesResults'));
		
		        /*
		         * * articoli del PRODUTTORE ancora da associare
		         */
				if(!empty($article_id_da_escludere)) 						   
					$article_id_da_escludere = substr($article_id_da_escludere, 0, strlen($article_id_da_escludere) - 1);
				
		        $articles = $Article->getBySupplierArticleInArticlesOrder($tmp_user, $orderCurrentResults['Order']['supplier_organization_id'], $this->user->organization['Supplier']['Supplier']['id'], $article_id_da_escludere);
				$this->set('articles', $articles);	
				/*
				echo "<pre>";
				print_r($articles);
				echo "</pre>";	
				*/
							
				/*
				 * dati GAS con il quale sto lavorando
				 */
				App::import('Model', 'Organization');
				$Organization = new Organization;
				
				$options = [];
				$options['conditions'] = array('Organization.id' => $organization_id);
				$organizations = $Organization->find('first', $options);
				$this->set(compact('organizations'));
			
			} // end if(empty($organization_id)) 
		} // end if($permission_to_continue)
			
		$this->set('organization_id', $organization_id);
		$this->set('order_id', $order_id);
		$this->set(compact('organizationsResults'));
		$this->set(compact('orderResults'));

		$this->set(compact('permission_to_continue'));
	}	
	
    private function _isArticleInArticlesOrder($organization_id, $article_id) {

		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;

		$options = [];
		$options['conditions'] = array('ArticlesOrder.organization_id' => $organization_id,
									   'ArticlesOrder.article_id' => $article_id,
									   'ArticlesOrder.stato != ' => 'N');
		$options['recursive'] = -1;
		$articlesOrderResults = $ArticlesOrder->find('all', $options);
		/*
		echo "<pre>";
		print_r($options);
		print_r($articlesOrderResults);
		echo "</pre>";				
		*/
		if(empty($articlesOrderResults))
			return false;
		else
			return true;
	}

	/*
	 * posso effettuare la syncronize_update se 
	 * 	prod_gas_article.id != 0 && article.flag_presente_articlesorders=='Y'
	 *
	 * category_article_id e' quello che Articolo originale, se lo cambio dal menu' a tendina non lo prende
	 */	
	public function admin_syncronize_update_ids($organization_id, $prod_gas_article_ids, $category_article_id, $type='SIMPLE') {
	
		$debug = false;
	
        if(empty($organization_id) || empty($prod_gas_article_ids) || empty($category_article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        	
		App::import('Model', 'Article');
		$Article = new Article;

		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;
	
		$msg = '';
		$prod_gas_article_ids = explode(",", $prod_gas_article_ids);
	
		foreach($prod_gas_article_ids as $prod_gas_article_id) {

			if($debug) {
				echo "<pre>prod_gas_article_id \n ";
				print_r($prod_gas_article_id);
				echo "</pre>";
			}
			
			/*
			 * ProdGasArticle
			 */		
			$options = [];
			$options['conditions'] = array('ProdGasArticle.id' => $prod_gas_article_id,
										   'ProdGasArticle.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']);
			$options['recursive'] = -1;
			$prodGasArticlesResults = $ProdGasArticle->find('first', $options);	
			if(empty($prodGasArticlesResults['ProdGasArticle']['id'])) {
			 	$command=false;
				$prodGasArticleExist = true;
				
				$msg .= "Articolo del produttore con id ".$prod_gas_article_id." non trovato!<br />";
			}
			else {
				$command=true;
				$prodGasArticleExist = true;
			} 
			
			if($command) {
				$esito = $this->ProdGasArticlesSyncronize->syncronize_update($this->user, $organization_id, $prod_gas_article_id, $category_article_id, $debug);
				if($esito!==true) 
					$msg .= $esito.'<br />';
			}
		} // loop foreach($prod_gas_article_is as $prod_gas_article_id)

		if(empty($msg))
			$msg = __('The prodGasArticleSyncronizeUpdate Ids has been saved', true);
		
		$this->Session->setFlash($msg);
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index', 'type' => $type));	
	}

	/*
	 * articolo del produttore gia' presente nel GAS => UPDATE Article
	 * 	 	
	 * copia un articolo da ProdGasArticle a Article
	 * copia l'img da da ProdGasArticle a Article
	 */
	public function admin_syncronize_update($organization_id, $prod_gas_article_id, $category_article_id=0, $type='SIMPLE') {

		$debug = false;
		
        if(empty($prod_gas_article_id) || empty($category_article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
	
		$esito = $this->ProdGasArticlesSyncronize->syncronize_update($this->user, $organization_id, $prod_gas_article_id, $category_article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeUpdate has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index', 'type' => $type));
	} 

	public function admin_syncronize_insert_ids($organization_id, $prod_gas_article_ids, $category_article_id=0, $type='SIMPLE') {

		$debug = false;
		
        if(empty($organization_id) || empty($prod_gas_article_ids) || empty($category_article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }	

		$msg = '';
		if(strpos($prod_gas_article_ids, ',')===false) {
		    $prod_gas_article_ids = [$prod_gas_article_ids];
		}
		else
			$prod_gas_article_ids = explode(",", $prod_gas_article_ids);

		foreach($prod_gas_article_ids as $prod_gas_article_id) {

			if($debug) {
				echo "<pre>prod_gas_article_id \n ";
				print_r($prod_gas_article_id);
				echo "</pre>";
			}	
		
			$esito = $this->ProdGasArticlesSyncronize->syncronize_insert($this->user, $organization_id, $prod_gas_article_id, $category_article_id, $debug);
			if($esito!==true) 
				$msg .= $esito.'<br />';
		} // loop foreach($prod_gas_article_is as $prod_gas_article_id)

		if(empty($msg))
			$msg = __('The prodGasArticleSyncronizeInsert Ids has been saved', true);
		
		$this->Session->setFlash($msg);
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index', 'type' => $type));	
	}

	/*
	 * nuovo articolo del produttore => INSERT Article
	 * 	 
	 * copia un articolo da ProdGasArticle a Article
	 * copia l'img da da ProdGasArticle a Article
	 */
	public function admin_syncronize_insert($organization_id, $prod_gas_article_id, $category_article_id=0, $type='SIMPLE') {

		$debug = false;
		
        if(empty($prod_gas_article_id) || empty($category_article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		$esito = $this->ProdGasArticlesSyncronize->syncronize_insert($this->user, $organization_id, $prod_gas_article_id, $category_article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeInsert has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index', 'type' => $type));
	} 

	/*
	 * posso effettuare la syncronize_flag_presente_articlesorders se 
	 * 	prod_gas_article.id != 0
	 *  isArticleInCart
	 */
	public function admin_syncronize_flag_presente_articlesorders_ids($organization_id, $article_ids, $type='SIMPLE') {
	
		$debug = false;
	
        if(empty($organization_id) || empty($article_ids)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
	
		App::import('Model', 'Article');
		$Article = new Article;

		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;
	
		$msg = '';
		$article_ids = explode(",", $article_ids);
		
		foreach($article_ids as $article_id) {

			if($debug) {
				echo "<pre>article_id \n ";
				print_r($article_id);
				echo "</pre>";
			}
			
			$command=false;
			$articleExist = false;
			$prodGasArticleExist = false;
			$isArticleInArticlesOrder = false;
			$isArticleInCart = false;
			
			/*
			 * Article
			 */		
			$options = [];
			$options['conditions'] = array('Article.id' => $article_id,
										   'Article.organization_id' => $organization_id,
										   'Article.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']);
			$options['recursive'] = -1;
			$articlesResults = $Article->find('first', $options);	
			if(empty($articlesResults)) {
				$command=false;
				$articleExist=false;
				
				$msg .= 'Articolo con id '.$article_id.' non trovato<br />';
			}
			else {
			
				$articleExist=true;
			
				/*
				 * ProdGasArticle
				 */		
				$options = [];
				$options['conditions'] = array('ProdGasArticle.id' => $articlesResults['Article']['prod_gas_article_id'],
											   'ProdGasArticle.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']);
				$options['recursive'] = -1;
				$prodGasArticlesResults = $ProdGasArticle->find('first', $options);	
				if(!empty($prodGasArticlesResults['ProdGasArticle']['id'])) 
					$prodGasArticleExist = true;
				else
					$prodGasArticleExist = false;
	
				
				/*
				 * ctrl se l'articolo e' stato ordinato, questo ctrl non serve
				 * $isArticleInArticlesOrder = $this->_isArticleInArticlesOrder($organization_id, $article_id);
				*/
				 
				/*
				 * ctrl che l'articolo sia acquistato
				 */
				$isArticleInCart = $Article->isArticleInCart($tmp_user, $organization_id, $article_id);
	
				if($debug) {
					echo "<pre>prodGasArticleExist \n ";
					print_r($prodGasArticleExist);
					echo "</pre>";
					echo "<pre>isArticleInCart \n ";
					print_r($isArticleInCart);
					echo "</pre>";
				}
							
				if($prodGasArticleExist) 
					$command=true;
				else 
				if(!$prodGasArticleExist && $isArticleInCart) 
					$command=true;
				else {
					$command=false;
					
					$msg .= "Articolo ".$articlesResults['Article']['name']." non più presente nell'archivio del produttore<br />";
				}
			} // end if(empty($articlesResults))  			
			
			if($command) {
				$esito = $this->ProdGasArticlesSyncronize->syncronize_flag_presente_articlesorders($this->user, $organization_id, $article_id, $debug);
				if($esito!==true) 
					$msg .= $esito.'<br />';
			}
		} // loop foreach($article_ids as $article_id)

		if(empty($msg))
			$msg = __('The prodGasArticleSyncronizeFlagPresenteArticlesorders Ids has been saved', true);
		
		$this->Session->setFlash($msg);
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index', 'type' => $type));		
	}
	
	public function admin_syncronize_flag_presente_articlesorders($organization_id, $article_id, $type='SIMPLE') {

		$debug = false;
		
        if(empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		$esito = $this->ProdGasArticlesSyncronize->syncronize_flag_presente_articlesorders($this->user, $organization_id, $article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeFlagPresenteArticlesorders has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index', 'type' => $type));
	}
	
	/*
	 * articolo del produttore non + presente nel suo archivio => DELETE Article
	 *
	 * in ProdGasArticlesSyncronize->syncronize_delete ctrl che non sia acquistato
	 */
	public function admin_syncronize_delete($organization_id, $article_id, $type='SIMPLE') {

		$debug = false;
		
        if(empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		$esito = $this->ProdGasArticlesSyncronize->syncronize_delete($this->user, $organization_id, $article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeDelete has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index', 'type' => $type));
	}
	
	public function admin_import_article_gas($organization_id, $prod_gas_article_id, $article_id, $type='SIMPLE') {

		$debug = false;
		
        if(empty($organization_id) || empty($prod_gas_article_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		$esito = $this->ProdGasArticlesSyncronize->import_article_gas($this->user, $organization_id, $prod_gas_article_id, $article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeImportArticleGas has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index', 'type' => $type));
	}	

	/* 
	 * articles - orders
	 */
	public function admin_syncronize_articles_orders_update_ids($organization_id, $order_id, $article_organization_ids, $article_ids, $prod_gas_article_ids) {
	
		$debug = false;
		
        if(empty($organization_id) || empty($order_id) || empty($article_organization_ids) || empty($article_ids) || empty($prod_gas_article_ids)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		$msg = '';
		$article_organization_ids = explode(",", $article_organization_ids);
		$article_ids = explode(",", $article_ids);
		$prod_gas_article_ids = explode(",", $prod_gas_article_ids);
	
		for($i=0; $i<count($prod_gas_article_ids); $i++) {

 			$article_organization_id = $article_organization_ids[$i];
 			$article_id = $article_ids[$i];
 			$prod_gas_article_id = $prod_gas_article_ids[$i];
 			
			if($debug) {
				echo "<pre>prod_gas_article_id \n ";
				print_r($prod_gas_article_ids);
				echo "</pre>";
			}
			
			$esito = $this->ProdGasArticlesSyncronize->syncronize_articles_orders_update($this->user, $organization_id, $order_id, $article_organization_id, $article_id, $prod_gas_article_id, $debug);
			if($esito!==true) 
				$msg .= $esito.'<br />';
		} // loop foreach($prod_gas_article_is as $prod_gas_article_id)

		if(empty($msg))
			$msg = __('The prodGasArticleSyncronizeArticlesOrdersUpdate Ids has been saved', true);
		
		$this->Session->setFlash($msg);
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index_articles_orders', 'organization_id' => $organization_id, 'order_id' => $order_id));			
	}
	
	public function admin_syncronize_articles_orders_update($organization_id, $order_id, $article_organization_id, $article_id, $prod_gas_article_id) {

		$debug = false;
		
        if(empty($organization_id) || empty($order_id) || empty($article_organization_id) || empty($article_id) || empty($prod_gas_article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
	
		$esito = $this->ProdGasArticlesSyncronize->syncronize_articles_orders_update($this->user, $organization_id, $order_id, $article_organization_id, $article_id, $prod_gas_article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeArticlesOrdersUpdate has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		//if($debug) 
		//	exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index_articles_orders', 'organization_id' => $organization_id, 'order_id' => $order_id));
	} 	

	public function admin_syncronize_articles_orders_insert_ids($organization_id, $order_id, $prod_gas_article_ids) {
	
		$debug = false;
		
        if(empty($organization_id) || empty($order_id) || empty($prod_gas_article_ids)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		$msg = '';
		$prod_gas_article_ids = explode(",", $prod_gas_article_ids);
	
		for($i=0; $i<count($prod_gas_article_ids); $i++) {

 			$prod_gas_article_id = $prod_gas_article_ids[$i];
 			
			if($debug) {
				echo "<pre>prod_gas_article_id \n ";
				print_r($prod_gas_article_ids);
				echo "</pre>";
			}
			
			$esito = $this->ProdGasArticlesSyncronize->syncronize_articles_orders_insert($this->user, $organization_id, $order_id, $prod_gas_article_id, $debug);
			if($esito!==true) 
				$msg .= $esito.'<br />';
		} // loop foreach($prod_gas_article_is as $prod_gas_article_id)

		if(empty($msg))
			$msg = __('The prodGasArticleSyncronizeArticlesOrdersInsert Ids has been saved', true);
		
		$this->Session->setFlash($msg);
		
		if($debug) 
			exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index_articles_orders', 'organization_id' => $organization_id, 'order_id' => $order_id));			
	}
	
	public function admin_syncronize_articles_orders_insert($organization_id, $order_id, $prod_gas_article_id) {

		$debug = false;
		
        if(empty($organization_id) || empty($order_id) || empty($prod_gas_article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		$esito = $this->ProdGasArticlesSyncronize->syncronize_articles_orders_insert($this->user, $organization_id, $order_id, $prod_gas_article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeArticlesOrdersInsert has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		//if($debug) 
		//	exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index_articles_orders', 'organization_id' => $organization_id, 'order_id' => $order_id));
	} 	
	
	public function admin_syncronize_articles_orders_delete($organization_id, $order_id, $article_organization_id, $article_id) {

		$debug = false;
		
        if(empty($organization_id) || empty($order_id) || empty($article_organization_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
	
	
        /*
         * D.E.S.
         */
        $des_order_id = 0;
        if ($this->userOrganization->organization['Organization']['hasDes'] == 'Y') {

            App::import('Model', 'DesOrdersOrganization');
            $DesOrdersOrganization = new DesOrdersOrganization();

            $desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->userOrganization, $order_id, $debug);
            if (!empty($desOrdersOrganizationResults)) 
                $des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
		}
	
		$esito = $this->ProdGasArticlesSyncronize->syncronize_articles_orders_delete($this->userOrganization, $organization_id, $order_id, $article_organization_id, $article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeArticlesOrdersDelete has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		//if($debug) 
		//	exit;
		
		$this->myRedirect(array('controller' => 'ProdGasArticlesSyncronizes', 'action' => 'index_articles_orders', 'organization_id' => $organization_id, 'order_id' => $order_id));
	}	
}