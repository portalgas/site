<?php
App::uses('AppController', 'Controller');

class DesArticlesSyncronizesController extends AppController {
														
    public function beforeFilter() {
        parent::beforeFilter();

		if ($this->user->organization['Organization']['hasDes'] == 'N') {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
		
		if(empty($this->user->des_id)) {
            $this->Session->setFlash(__('msg_des_choice'));
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Des&action=index';
			$this->myRedirect($url);
        }
    }
	
	public function admin_intro() { 
	
		$debug = false;
			
		App::import('Model', 'DesSupplier');
		$DesSupplier = new DesSupplier;	
			
		if ($this->request->is('post')) {
			$organization_id = $this->request->data['DesArticlesSyncronize']['organization_id'];
			$des_supplier_id = $this->request->data['DesArticlesSyncronize']['des_supplier_id'];

			/*
			 *  get Supplier
			 */
			if($des_supplier_id>0) {
				$options = [];
				$options['conditions'] = ['DesSupplier.des_id' => $this->user->des_id,
											'DesSupplier.id' => $des_supplier_id];
				$options['fields'] = ['DesSupplier.supplier_id'];
				$options['recursive'] = -1;
				$desSupplierResults = $DesSupplier->find('first', $options);
				$supplier_id = $desSupplierResults['DesSupplier']['supplier_id'];
			
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesArticlesSyncronizes&action=index&organization_id='.$organization_id.'&supplier_id='.$supplier_id;
				if($debug) 
					self::d($url, $debug);
				else
					$this->myRedirect($url);				
			}			
		}
		
        /*
         * tutti i GAS del DES
         */
        App::import('Model', 'DesOrganization');
        $DesOrganization = new DesOrganization;
				
        $options = [];
        $options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id, 
								   'DesOrganization.organization_id != ' => $this->user->organization['Organization']['id']];
        $options['order'] = ['Organization.name' => 'asc'];
        $options['recursive'] = 1;
        $desOrganizationsResults = $DesOrganization->find('all', $options);
		$desOrganizations = [];
		foreach($desOrganizationsResults as $desOrganizationsResult) {
			$desOrganizations[$desOrganizationsResult['Organization']['id']] = $desOrganizationsResult['Organization']['name'];
		}
    	 /*
		*  elenco Supplier profilati
		*		ReferenteDes
		*		SuperReferenteDes
		*/
		$ACLSuppliersResults = [];
		if($this->isManagerDes() || $this->isSuperReferenteDes())
			$ACLSuppliersResults = $DesSupplier->getListDesSuppliers($this->user);
		else
			$ACLSuppliersResults = $this->getACLsuppliersIdsDes();
		$this->set('ACLdesSuppliers',$ACLSuppliersResults);
        $this->set(compact('desOrganizations'));
	}
	
	/*
	 * sincronizzo gli articoli del gas passato
	 * $organization_id GAS al quale si desidera copiare il listino articoli
	 * la prima volta passo $des_supplier_id=0
	 * dopo action sincronizzazione gli passo $supplier_id
	 */
	public function admin_index($organization_id=0, $supplier_id=0) { 
	
		$debug = false;
		
		if (empty($organization_id) || empty($supplier_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		App::import('Model', 'Organization');
        $Organization = new Organization;
		
		App::import('Model', 'Article');
		$Article = new Article;
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		/*
		 * dati GAS scelto
		 */		
        $options = [];
        $options['conditions'] = ['Organization.id' => $organization_id];
        $options['recursive'] = -1;
        $organizationsResults = $Organization->find('first', $options);
		$this->set(compact('organizationsResults'));
	
		/*
		 *  get SuppliersOrganization del GAS da cui copiare
		 */
   		$options = [];
   		$options['conditions'] = ['SuppliersOrganization.organization_id' => $organization_id,
								'SuppliersOrganization.supplier_id' => $supplier_id,
								'SuppliersOrganization.stato' => 'Y'];
   		$options['recursive'] = 0;
   		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		$this->set(compact('suppliersOrganizationResults'));
		
		$supplier_organization_id = $suppliersOrganizationResults['SuppliersOrganization']['id'];
		if (empty($supplier_organization_id)) {
			$this->Session->setFlash("Il G.A.S. scelto non ha registrato il produttore nel suo archivio!");
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesArticlesSyncronizes&action=intro';
			$this->myRedirect($url);
		}
		
		/*
		 * get elenco Master Article 
		 */	
		$options = [];
		$options['conditions'] = ['Article.organization_id' => $organization_id,
								   'Article.supplier_organization_id' => $supplier_organization_id,
								   'Article.stato' => 'Y'];
		$options['order'] = ['Article.name'];
		$options['recursive'] = -1;
		$articlesMasters = $Article->find('all', $options);	
		$this->set(compact('articlesMasters'));

		/*
		 * importo tutto il listino
		 */
		if(isset($this->request->params['pass']['modalita']) && $this->request->params['pass']['modalita']=='IMPORT_ALL') {
			if(!empty($articlesMasters))
			foreach($articlesMasters as $articlesMaster) {
				self::d($articlesMaster);
				$master_organization_id = $articlesMaster['Article']['organization_id'];
				$master_article_id = $articlesMaster['Article']['id'];
				$category_article_id = 0;
				$esito = $this->DesArticlesSyncronize->syncronize_insert($this->user, $master_organization_id, $master_article_id, $supplier_id, $category_article_id, false);
				if($esito!==true) {
					self::dd($articlesMaster);
					self::dd($esito);exit;
				}
				
			} // end foreach($articlesMasters as $articlesMaster)
		}
	
		/*
		 * get elenco propri Articles 
		 */	
		$myArticlesResults = [];
		$mySuppliersOrganizationResults = [];
		
   		$options = [];
   		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.supplier_id' => $supplier_id];
   		$options['fields'] = ['SuppliersOrganization.id', 'SuppliersOrganization.supplier_id', 'SuppliersOrganization.name', 'SuppliersOrganization.stato', 'SuppliersOrganization.owner_articles'];
   		$options['recursive'] = -1;
		$SuppliersOrganization = new SuppliersOrganization;	
   		$mySuppliersOrganizationResults = $SuppliersOrganization->find('first', $options);		
		
		/*
		 * ACL chi gestisce il listino articoli
		 */
		switch ($mySuppliersOrganizationResults['SuppliersOrganization']['owner_articles']) {
			case 'REFERENT':
			case 'REFERENT-TMP':
				$msg = '';
			break;
			case 'SUPPLIER':
				$msg = "Il listino articolo del produttore scelto è gestito da '".__('ArticlesOwnerSUPPLIER')."', per poterlo gestire dev'essere '".__('ArticlesOwnerREFERENT')."'";
			break;
			case 'DES':
				$msg = "Il listino articolo del produttore scelto è gestito da '".__('ArticlesOwnerDES')."', per poterlo gestire dev'essere '".__('ArticlesOwnerREFERENT')."'";
			break;
			default:
				self::x(__('msg_error_supplier_organization_owner_articles'));
			break;
		}
		if(!empty($msg)) {
			$this->Session->setFlash($msg);
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesArticlesSyncronizes&action=intro';
			$this->myRedirect($url);
		}
		
		if($mySuppliersOrganizationResults['SuppliersOrganization']['stato']!='N') {
			$options = [];
			$options['conditions'] = ['Article.organization_id' => $this->user->organization['Organization']['id'],
									  'Article.supplier_organization_id' => $mySuppliersOrganizationResults['SuppliersOrganization']['id'],
									  'Article.stato' => 'Y'];
			$options['order'] = ['Article.name'];
			$options['recursive'] = -1;
			$Article = new Article;
			$myArticlesResults = $Article->find('all', $options);
			$myArticles = [];
			if(!empty($myArticlesResults)) {
				foreach($myArticlesResults as $myArticlesResult) {				
					$myArticles[$myArticlesResult['Article']['id']] = $myArticlesResult['Article']['name'].' '.$myArticlesResult['Article']['qta'].' '.$myArticlesResult['Article']['um'].' - '.$myArticlesResult['Article']['prezzo_e'];
				}				
			}
		}
		$this->set(compact('myArticles', 'mySuppliersOrganizationResults'));
		
		/*
		 * get elenco categorie se INSERT
		*/
		$Article = new Article;
		
		$conditionsCategoriesArticle = ['organization_id' => $organization_id];
		$categories = $Article->CategoriesArticle->generateTreeList($conditionsCategoriesArticle, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('categories'));

		$this->set(compact('organization_id', 'supplier_id'));
	}	
	
	public function admin_ctrl_article($master_organization_id, $supplier_id, $master_article_id, $article_id, $format=notmpl) {

		$debug = false;
		
		if (empty($master_organization_id) || empty($supplier_id) || empty($master_article_id) || empty($article_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		App::import('Model', 'Article');
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;

		/*
		 * Master articles 
		 */		
		$options = [];
		$options['conditions'] = ['Article.organization_id' => $master_organization_id,
								   'Article.id' => $master_article_id,
								   'Article.stato' => 'Y'];
		$options['recursive'] = -1;
		$Article = new Article;
		$masterResults = $Article->find('first', $options);
		
		/*
		 * MY articles 
		 */
   		$options = [];
   		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									'SuppliersOrganization.supplier_id' => $supplier_id];
   		$options['fields'] = ['SuppliersOrganization.id'];
   		$options['recursive'] = -1;
		$SuppliersOrganization = new SuppliersOrganization;	
   		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		
		$options = [];
		$options['conditions'] = ['Article.organization_id' => $this->user->organization['Organization']['id'],
								   'Article.supplier_organization_id' => $suppliersOrganizationResults['SuppliersOrganization']['id'],
								   'Article.id' => $article_id,
								   'Article.stato' => 'Y'];
		$options['recursive'] = -1;
		$Article = new Article;
		$results = $Article->find('first', $options);
		if(!empty($results)) {
			/*
			 * ctrl se l'articolo e' stato ordinato
			 */
			$options = [];
			$options['conditions'] = ['ArticlesOrder.organization_id' => $results['Article']['organization_id'],
									   'ArticlesOrder.article_id' => $results['Article']['id'],
									   'ArticlesOrder.stato != ' => 'N'];
			$options['recursive'] = -1;
			$articlesOrderResults = $ArticlesOrder->find('all', $options);	
			if(empty($articlesOrderResults))
				$results['Article']['ArticlesOrder'] = false;
			else
				$results['Article']['ArticlesOrder'] = false;

			/*
			 * ctrl che l'articolo sia acquistato
			 */
			$results['Article']['isArticleInCart'] = $Article->isArticleInCart($this->user, $results['Article']['organization_id'], $results['Article']['id']);
		}	 
		$this->set(compact('results', 'masterResults', 'master_organization_id', 'supplier_id', 'master_article_id'));
		
		$this->set('id_articles_confronto', uniqid());
		
		$this->layout = 'ajax';	
	}
	
	/*
	 * articolo del GAS master gia' presente nel GAS => UPDATE Article
	 * 	 	
	 * copia un articolo da Master.Article a My.Article
	 * copia l'img da da Master.Article a My.Article
	 */
	public function admin_syncronize_update($master_organization_id, $supplier_id, $master_article_id, $article_id) {

		$debug = false;
		
        if(empty($master_organization_id) || empty($master_article_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
	
		$esito = $this->DesArticlesSyncronize->syncronize_update($this->user, $master_organization_id, $master_article_id, $article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeUpdate has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
				
		$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesArticlesSyncronizes&action=index&organization_id='.$master_organization_id.'&supplier_id='.$supplier_id;

		if($debug) {
			self::d($url, $debug);
			exit;
		}
		$this->myRedirect($url);
	} 

	/*
	 * nuovo articolo del GAS Master => INSERT Article
	 * 	 
	 * copia un articolo da Master.Article a My.Article
	 * copia l'img da da MasterArticle a My.Article
	 */
	public function admin_syncronize_insert($master_organization_id, $supplier_id, $master_article_id, $category_article_id=0) {

		$debug = false;
		
        if(empty($master_organization_id) || empty($supplier_id) || empty($master_article_id) || empty($category_article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		$esito = $this->DesArticlesSyncronize->syncronize_insert($this->user, $master_organization_id, $master_article_id, $supplier_id, $category_article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeInsert has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		if($debug) 
			exit;
		
		$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesArticlesSyncronizes&action=index&organization_id='.$master_organization_id.'&supplier_id='.$supplier_id;
		$this->myRedirect($url);
	} 

	public function admin_syncronize_flag_presente_articlesorders($master_organization_id, $supplier_id, $article_id) {

		$debug = false;
		
        if(empty($master_organization_id) || empty($supplier_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		$esito = $this->DesArticlesSyncronize->syncronize_flag_presente_articlesorders($this->user, $article_id, $debug);
		if($esito===true) {
			$this->Session->setFlash(__('The prodGasArticleSyncronizeFlagPresenteArticlesorders has been saved', true));
		} else {
			$this->Session->setFlash($esito);
		}
		
		if($debug) 
			exit;
		
		$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesArticlesSyncronizes&action=index&organization_id='.$master_organization_id.'&supplier_id='.$supplier_id;
		$this->myRedirect($url);
	}
}