<?php
App::uses('AppController', 'Controller');

class BookmarksArticlesController extends AppController {
   
   public function beforeFilter() {
   		parent::beforeFilter();
		
		if($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
			$this->Session->setFlash(__('msg_not_permission_guest'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
   }

   public function admin_index() {
   
	   	App::import('Model', 'User');
	   	$User = new User;
	   	
	   	$FilterBookmarksArticleUserId = null;
	   	$FilterBookmarksArticleSupplierOrganizationId = null;
	   	$FilterBookmarksArticleAllArticles = 'N';
	   	
	   	$options =  array();
	   	$options['conditions'] = array('BookmarksArticle.organization_id' => (int)$this->user->organization['Organization']['id']);
	   	  	
	   	/*
	   	 * fields Article
	   	*/
	   	if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'UserId')) {
	   		$FilterBookmarksArticleUserId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'UserId');
	   		$options['conditions'] += array('BookmarksArticle.user_id' => $FilterBookmarksArticleUserId);
	   	}
	
	   	if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'SupplierOrganizationId')) {
	   		$FilterBookmarksArticleSupplierOrganizationId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'SupplierOrganizationId');
	   		$options['conditions'] += array('BookmarksArticle.supplier_organization_id' => $FilterBookmarksArticleSupplierOrganizationId);
	   	}
	   	
	   	/*
	   	 * filtro se vedere tutti gli articoli di un produttore
	   	 * o solo quellli in Bookmarks
	   	 * devo aver scelto un produttore e un utente
	   	 */
   		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'AllArticles')) {
	   		$FilterBookmarksArticleAllArticles = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'AllArticles');
	   		
	   		if(empty($FilterBookmarksArticleUserId) || empty($FilterBookmarksArticleSupplierOrganizationId))
	   			$FilterBookmarksArticleAllArticles = 'N';
	   	}
	   	
	   	$options['order'] = array('SuppliersOrganization.name ASC', Configure::read('orderUser'));
	   	$options['recursive'] = 1;
	   	
	   	/*
	   	 * se non ho filtrato per utente o produttore non effettuo la ricerca
	   	 */
	   	$results = array();
	   	if(!empty($FilterBookmarksArticleUserId) || !empty($FilterBookmarksArticleSupplierOrganizationId)) {
	   		
	   		/*
	   		 * solo gli articoli in Bookmarks
	   		 */
	   		if($FilterBookmarksArticleAllArticles=='N')
	   			$results = $this->BookmarksArticle->find('all', $options); 
			else {
				if(!empty($FilterBookmarksArticleUserId) && !empty($FilterBookmarksArticleSupplierOrganizationId)) {
					/*
					 * tutti gli articoli di un produttore
					 * elenco articoli del produttore come da Article::getArticlesDataAnagr()
					*/
					App::import('Model', 'Article');
					$Article = new Article;
					
					$belongsTo = array(
							'className' => 'BookmarksArticle',
							'foreignKey' => '',
							'conditions' => array('BookmarksArticle.organization_id = Article.organization_id and BookmarksArticle.article_id = Article.id and BookmarksArticle.user_id = '.$FilterBookmarksArticleUserId.' and BookmarksArticle.supplier_organization_id = '.$FilterBookmarksArticleSupplierOrganizationId),
							'fields' => '',
							'order' => '');
					
					$Article->bindModel(array('belongsTo' => array('BookmarksArticle' => $belongsTo)));
					
					$Article->unbindModel(array('hasOne' => array('ArticlesOrder')));
					$Article->unbindModel(array('hasMany' => array('ArticlesOrder')));
					$Article->unbindModel(array('hasAndBelongsToMany' => array('Order')));
					
					if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='N')
						$Article->unbindModel(array('belongsTo' => array('CategoriesArticle')));
					
					$Article->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = '.$this->user->organization['Organization']['id'];
					$Article->hasMany['ArticlesArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
					$Article->hasAndBelongsToMany['ArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
					
					
					$options = array();
					$options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
													'Article.stato' => 'Y',
													'Article.supplier_organization_id' => $FilterBookmarksArticleSupplierOrganizationId);
					$options['recursive'] = 1;
					
					$options['fields'] = array('BookmarksArticle.id,BookmarksArticle.user_id,BookmarksArticle.qta,Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.stato,Article.created,Article.modified,SuppliersOrganization.id,SuppliersOrganization.name,CategoriesArticle.name,ArticlesArticlesType.article_type_id');
					$options['group'] = array('BookmarksArticle.id,BookmarksArticle.user_id,BookmarksArticle.qta,Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.stato,Article.created,Article.modified,SuppliersOrganization.id,SuppliersOrganization.name,CategoriesArticle.name');
					$options['order'] = array('Article.name');
					$results = $Article->find('all', $options);		

					/*
					 * associo per ogni articolo lo User (e' sempre lo stesso perche' passato dal filtro)
					 */
					$options =  array();
					$options['conditions'] = array('User.organization_id'=>(int)$this->user->organization['Organization']['id'],
												   'User.id'=> $FilterBookmarksArticleUserId);
					$options['fields'] = array('id','name');
					$options['recursive'] = -1;
					$users = $User->find('first', $options);
					
					foreach ($results as $numResult => $result) {
						$results[$numResult]['User'] = $users['User'];
					}	
					
				} // end if(!empty($FilterBookmarksArticleSupplierOrganizationId))
			}
	   		
	   		$this->set('results', $results);
	   		
	   		/*
		   	echo "<pre>";
		   	print_r($options);
		   	print_r($results);
		   	echo "</pre>";
		   	*/
	   	}
	   		   	
	   	$this->set('results', $results);
	   	   	
	   	$this->set('FilterBookmarksArticleUserId', $FilterBookmarksArticleUserId);
	   	$this->set('FilterBookmarksArticleSupplierOrganizationId', $FilterBookmarksArticleSupplierOrganizationId);
	   	$this->set('FilterBookmarksArticleAllArticles', $FilterBookmarksArticleAllArticles);
	   	
	   	/* filtro */
		$options =  array();
	   	$options['conditions'] = array('User.organization_id'=>(int)$this->user->organization['Organization']['id'],
										'User.block'=> 0);
	   	$options['fields'] = array('id','name');
	   	$options['recursive'] = -1;
	   	$options['order'] = Configure::read('orderUser');
		$users = $User->find('list', $options);
		$this->set('users', $users);
		
	   	$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
	   	
	   	$allArticles = array('Y' => "Mostra tutti gli articoli", 'N' => "Mostra solo quelli tra i preferiti");
	   	$this->set('allArticles', $allArticles);
   }
   
 	public function index() {
		
		App::import('Model', 'Supplier');
	
		$options =  array();
		$options['conditions'] = array('BookmarksArticle.organization_id' => (int)$this->user->organization['Organization']['id']);
		$options['order'] = array('SuppliersOrganization.name ASC');
		$options['recursive'] = 1;
		$results = $this->BookmarksArticle->find('all', $options);
		
		/*
		 * Suppliers per l'immagine
		 */		
		foreach ($results as $numResult => $result) {

			$Supplier = new Supplier;
			
			$options = array();
			$options['conditions'] = array('Supplier.id' => $result['SuppliersOrganization']['supplier_id']);
			$options['fields'] = array('Supplier.img1');
			$options['recursive'] = -1;
			$SupplierResults = $Supplier->find('first', $options);
			if(!empty($SupplierResults))
				$results[$numResult]['Supplier']['img1'] = $SupplierResults['Supplier']['img1'];
		}
		
		$this->set('results', $results);
		
		// parametro passato da storeroom_to_user.ctp
		if(isset($_REQUEST['esito']) && $_REQUEST['esito']=='OK')
			$this->Session->setFlash(__('bookmarksArticles has been saved'));
		
		$FilterBookmarksArticleSupplierId = null;
		$SqlLimit = 20;
		
		/* filtro */
		$this->set('FilterBookmarksArticleSupplierId', $FilterBookmarksArticleSupplierId);
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => (int)$this->user->organization['Organization']['id'],
				'SuppliersOrganization.stato' => 'Y');
		$options['order'] = array('SuppliersOrganization.name');
		$options['recursive'] = -1;
		$suppliersOrganizations = $SuppliersOrganization->find('list', $options);
		
		$this->set(compact('suppliersOrganizations'));
		
		$this->layout = 'default_front_end';
	}  
	
	/*
	 * elenco produttori da scegliere
	 */
	public function add() {
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => (int)$this->user->organization['Organization']['id'],
										'SuppliersOrganization.stato' => 'Y');
		$options['order'] = array('SuppliersOrganization.name');
		$options['recursive'] = -1;
		$suppliersOrganizations = $SuppliersOrganization->find('list', $options);
		
		$this->set(compact('suppliersOrganizations'));
		
		$this->layout = 'ajax';
	}	
	
	/*
	 * estraggo elenco degli articoli di un produttore
	 * eventuali bookmarks dell'utente
	 */
	public function index_articles($supplier_organization_id) {

		/*
		 * elenco articoli del produttore come da Article::getArticlesDataAnagr()
		 */
		App::import('Model', 'Article');
		$Article = new Article;
		
		$belongsTo = array(
				'className' => 'BookmarksArticle',
				'foreignKey' => '',
				'conditions' => array('BookmarksArticle.organization_id = Article.organization_id and BookmarksArticle.article_id = Article.id'),
				'fields' => '',
				'order' => '');
		
		$Article->bindModel(array('belongsTo' => array('BookmarksArticle' => $belongsTo)));
		
		$Article->unbindModel(array('hasOne' => array('ArticlesOrder')));
		$Article->unbindModel(array('hasMany' => array('ArticlesOrder')));
		$Article->unbindModel(array('hasAndBelongsToMany' => array('Order')));

		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='N')
			$Article->unbindModel(array('belongsTo' => array('CategoriesArticle')));
		
		$Article->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = '.$this->user->organization['Organization']['id'];
		$Article->hasMany['ArticlesArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
		$Article->hasAndBelongsToMany['ArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
		
		
		$options = array();
		$options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
										'Article.stato' => 'Y',
										'Article.supplier_organization_id' => $supplier_organization_id);
		$options['recursive'] = 1;
		
		$options['fields'] = array('BookmarksArticle.id,BookmarksArticle.user_id,BookmarksArticle.qta,Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.stato,Article.created,Article.modified,SuppliersOrganization.id,SuppliersOrganization.name,CategoriesArticle.name,ArticlesArticlesType.article_type_id');
		$options['group'] = array('BookmarksArticle.id,BookmarksArticle.user_id,BookmarksArticle.qta,Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.stato,Article.created,Article.modified,SuppliersOrganization.id,SuppliersOrganization.name,CategoriesArticle.name');
		$options['order'] = array('Article.name');
		$results = $Article->find('all', $options);
		
		$this->set('results', $results);
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		$this->layout = 'ajax';
	}
	
	/*
	 * Call ajax per submit
	 * $rowId = Article.id _ numResult (numero della riga)
	 */
	public function admin_managementCartSimple($rowId, $user_id, $supplier_organization_id, $article_organization_id, $article_id, $qta) {
		$this->__managementCartSimple($rowId, $user_id, $supplier_organization_id, $article_organization_id, $article_id, $qta);
	}
	
	public function managementCartSimple($rowId, $supplier_organization_id, $article_organization_id, $article_id, $qta) {
		
		$user_id = $this->user->get('id');
		$this->__managementCartSimple($rowId, $user_id, $supplier_organization_id, $article_organization_id, $article_id, $qta);
	}
	
	public function __managementCartSimple($rowId, $user_id, $supplier_organization_id, $article_organization_id, $article_id, $qta) {

	    if(empty($user_id) || empty($article_organization_id) || empty($article_id) || empty($supplier_organization_id)) {
    		$this->Session->setFlash(__('msg_error_params'));
    		$this->myRedirect(Configure::read('routes_msg_exclamation'));
    	}
		
    	/*
		 * ctrl se INSERT o UPDATE
		 */
		$options =  array();
		$options['conditions'] = array('BookmarksArticle.organization_id' => (int)$this->user->organization['Organization']['id'],
										'BookmarksArticle.user_id' => $user_id,
										'BookmarksArticle.article_organization_id' => $article_organization_id,
										'BookmarksArticle.article_id' => $article_id,
										'BookmarksArticle.supplier_organization_id' => $supplier_organization_id,
		);
		$options['recursive'] = -1;
		$results = $this->BookmarksArticle->find('first', $options);
		if(!empty($results))
			$data['BookmarksArticle']['id'] = $results['BookmarksArticle']['id'];

		/*
		 * delete
		 */
		if($qta==0) {
			$this->BookmarksArticle->id = $results['BookmarksArticle']['id'];
			if ($this->BookmarksArticle->delete())
				$resultsJS = 'managementCartBookmarks(\'%s\',\'DELETE\', null);';
			else 
				$resultsJS = 'managementCartBookmarks(\'%s\',\'NO\', null);';
		}
		else {
			$data['BookmarksArticle']['organization_id'] = $this->user->organization['Organization']['id'];
			$data['BookmarksArticle']['user_id'] = $this->user->id;
			$data['BookmarksArticle']['supplier_organization_id'] = $supplier_organization_id;
			$data['BookmarksArticle']['article_organization_id'] = $article_organization_id;
			$data['BookmarksArticle']['article_id'] = $article_id;
			$data['BookmarksArticle']['qta'] = $qta;
			
			$this->BookmarksArticle->create();
			if ($this->BookmarksArticle->save($data)) {
				$resultsJS = 'managementCartBookmarks(\'%s\',\'OK\', null);';
			} else {
				$resultsJS = 'managementCartBookmarks(\'%s\',\'NO\', null);';
			}
		}
		
		/*
		 * gestione JavaScript
		* */
		$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId).'</script>';
		$this->set('resultsJS',$resultsJS);
		
		/*
		 * ricarico riga
		 */
		App::import('Model', 'Article');
		$Article = new Article;
		
		$belongsTo = array(
							'className' => 'BookmarksArticle',
							'foreignKey' => '',
							'conditions' => array('BookmarksArticle.organization_id = Article.organization_id and BookmarksArticle.article_id = Article.id  and BookmarksArticle.user_id = '.$user_id),
							'fields' => '',
							'order' => '');
		
		$Article->bindModel(array('belongsTo' => array('BookmarksArticle' => $belongsTo)));
		
		$Article->unbindModel(array('hasOne' => array('ArticlesOrder')));
		$Article->unbindModel(array('hasMany' => array('ArticlesOrder')));
		$Article->unbindModel(array('hasAndBelongsToMany' => array('Order')));
		
		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='N')
			$Article->unbindModel(array('belongsTo' => array('CategoriesArticle')));
		
		$Article->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = '.$this->user->organization['Organization']['id'];
		$Article->hasMany['ArticlesArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
		$Article->hasAndBelongsToMany['ArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
		
		
		$options = array();
		$options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
										'Article.id' => $article_id,
										'Article.stato' => 'Y',
										'Article.supplier_organization_id' => $supplier_organization_id);
		$options['recursive'] = 1;
		
		$options['fields'] = array('BookmarksArticle.id,BookmarksArticle.user_id,BookmarksArticle.qta,Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.stato,Article.created,Article.modified,SuppliersOrganization.id,SuppliersOrganization.name,CategoriesArticle.name,ArticlesArticlesType.article_type_id');
		$options['group'] = array('BookmarksArticle.id,BookmarksArticle.user_id,BookmarksArticle.qta,Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.stato,Article.created,Article.modified,SuppliersOrganization.id,SuppliersOrganization.name,CategoriesArticle.name');
		$results = $Article->find('first', $options);
	
		$this->set('results', $results);
				
		/*
		 * numero della riga
		 */
		list($articles_id, $numResult) = explode('_', $rowId);
		$this->set('numResult', $numResult);
		
		$this->layout = 'ajax';
		$this->render('/Layouts/AjaxGas/rowecomm_frontend_bookmarks');
	}
}