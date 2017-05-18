<?php
App::uses('AppController', 'Controller');

class ProdGasArticlesSyncronizesController extends AppController {
														
	public function beforeFilter() {
		parent::beforeFilter();
		
		/* ctrl ACL */
		if(empty($this->user->supplier['Supplier'])) {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	

		$organization_id = $this->request->params['pass']['organization_id'];
        if (empty($organization_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;

		
		$organizationResults = $ProdGasSupplier->getOrganizationAssociate($this->user, $organization_id, 0);
		if($organizationResults['SuppliersOrganization']['owner_articles']!='SUPPLIER') {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		$this->set(compact('organization_id'));
		// ACL		
	}
	
	public function admin_index($organization_id=0) { 
	
		$debug = false;
		
		/*
		 * get elenco Article del GAS ed eventuali ArticlesOrder: se c'e' non posso cancellarlo
		 */	
		App::import('Model', 'Article');
		$Article = new Article;
		
		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;

		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		/*
		 * non funge non fa unbindModel ArticlesOrder e duplica i rows => $options['recursive'] = -1;
		 */
		$Article->unbindModel(array('belongsTo' => array('SuppliersOrganization', 'CategoriesArticle')),
		                      array('hasMany' => array('ArticlesOrder','ArticlesArticlesType')),
							  array('hasAndBelongsToMany' => array('ArticlesType','Order')),
							  array('hasOne' => array('ArticlesArticlesType','ArticlesOrder')));
		$Article->bindModel(array('belongsTo' => array('ProdGasArticle' => array(
														'className' => 'ProdGasArticle',
														'foreignKey' => 'prod_gas_article_id'))));														
		$options = array();
		$options['conditions'] = array('Article.organization_id' => $organization_id,
									   'Article.supplier_id' => $this->user->supplier['Supplier']['id']);
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
			
			$options = array();
			$options['conditions'] = array('ProdGasArticle.id' => $articlesResult['Article']['prod_gas_article_id']);
			$options['recursive'] = -1;
			$prodGasArticlesResults = $ProdGasArticle->find('first', $options);	

			$articlesResults[$numResult]['ProdGasArticle'] = $prodGasArticlesResults['ProdGasArticle'];
			
			
			/*
			 * ctrl se l'articolo e' stato ordinato
			 */
			$options = array();
			$options['conditions'] = array('ArticlesOrder.organization_id' => $articlesResult['Article']['organization_id'],
										   'ArticlesOrder.article_id' => $articlesResult['Article']['id'],
										   'ArticlesOrder.stato != ' => 'N');
			$options['recursive'] = -1;
			$articlesOrderResults = $ArticlesOrder->find('all', $options);	
			if(empty($articlesOrderResults))
				$results['Article']['ArticlesOrder'] = false;
			else
				$results['Article']['ArticlesOrder'] = false;
			
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
		$options = array();
		$options['conditions'] = array('ProdGasArticle.supplier_id' => $this->user->supplier['Supplier']['id'],
									   'ProdGasArticle.stato' => 'Y');
		if(!empty($prod_gas_article_ids))							   
			$options['conditions'] += array("NOT" => array( "ProdGasArticle.id" => split(',', $prod_gas_article_ids)));
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
		
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $organization_id,
									   'SuppliersOrganization.supplier_id' => $this->user->supplier['Supplier']['id']);
		$options['field'] = array('SuppliersOrganization.id');
		$options['recursive'] = -1;
		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		/*
		echo "<pre>";
		print_r($options);
		print_r($suppliersOrganizationResults);
		echo "</pre>";
		*/
		$options = array();
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
		$articlesToAssociateListResults = array();
		
		if(!empty($articlesToAssociateResults)) {
			
			$ProdGasArticle->unbindModel(array('hasMany' => array('ProdGasArticlesPromotion')));
			$options = array();
			$options['conditions'] = array('ProdGasArticle.supplier_id' => $this->user->supplier['Supplier']['id'],
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
		
		$options = array();
		$options['conditions'] = array('Organization.id' => $organization_id);
		$organizations = $Organization->find('first', $options);
		$this->set(compact('organizations'));		 
	}	
	
	/*
	 * articolo del produttore gia' presente nel GAS => UPDATE Article
	 * 	 	
	 * copia un articolo da ProdGasArticle a Article
	 * copia l'img da da ProdGasArticle a Article
	 */
	public function admin_syncronize_update($organization_id, $prod_gas_article_id, $category_article_id=0) {

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
		
		$this->myRedirect(array('action' => 'index'));
	} 

	/*
	 * nuovo articolo del produttore => INSERT Article
	 * 	 
	 * copia un articolo da ProdGasArticle a Article
	 * copia l'img da da ProdGasArticle a Article
	 */
	public function admin_syncronize_insert($organization_id, $prod_gas_article_id, $category_article_id=0) {

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
		
		$this->myRedirect(array('action' => 'index'));
	} 

	public function admin_syncronize_flag_presente_articlesorders($organization_id, $article_id) {

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
		
		$this->myRedirect(array('action' => 'index'));
	}
	
	/*
	 * articolo del produttore non + presente nel suo archivio => DELETE Article
	 *
	 * in ProdGasArticlesSyncronize->syncronize_delete ctrl che non sia acquistato
	 */
	public function admin_syncronize_delete($organization_id, $article_id) {

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
		
		$this->myRedirect(array('action' => 'index'));
	}
	
	public function admin_import_article_gas($organization_id, $prod_gas_article_id, $article_id) {

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
		
		$this->myRedirect(array('action' => 'index'));
	}	

}