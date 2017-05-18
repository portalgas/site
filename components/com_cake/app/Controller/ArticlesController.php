<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'ImageTool');

class ArticlesController extends AppController {
															
	public $helpers = array('Javascript', 'Tabs', 'Image');
	private $context;
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		/* ctrl ACL */
		if (in_array($this->action, array('admin_edit', 'admin_delete'))) {

			if($this->isSuperReferente()) {
			
			}
			else {
				$id = $this->request->pass['id'];
				$article = $this->Article->read($this->user->organization['Organization']['id'], 'supplier_organization_id', $id);
				$supplier_organization_id = $article['Article']['supplier_organization_id'];
				
				if(!$this->isReferentGeneric() || !in_array($supplier_organization_id,explode(",",$this->user->get('ACLsuppliersIdsOrganization')))) {
					$this->Session->setFlash(__('msg_not_permission'));
					$this->myRedirect(Configure::read('routes_msg_stop'));
				}
			}	
		}	
		/* ctrl ACL */	

		if(Cache::read('articlesTypes')===false) {
			App::import('Model', 'ArticlesType');			$ArticlesType = new ArticlesType;
			$ArticlesTypeResults = $ArticlesType->prepareArray($ArticlesType->getArticlesTypes());
			Cache::write('articlesTypes',$ArticlesTypeResults);
		}
		else
			$ArticlesTypeResults = Cache::read('articlesTypes');	
		$this->set('ArticlesTypeResults',$ArticlesTypeResults);
	}

	public function admin_context_order_index() {
		$results = $this->__set_context_order($this->order_id);
		$supplier_organization_id = $results['Order']['supplier_organization_id'];
		$this->__admin_index('order', $supplier_organization_id);
	}
	
	public function admin_context_articles_index() {
		if($this->user->organization['Organization']['type']=='PROD') {
			$supplier_organization_id = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			$this->Session->write(Configure::read('Filter.prefix').$this->modelClass.'SupplierId', $supplier_organization_id);
		}
		else 
			$supplier_organization_id = null;
		$this->__admin_index('articles', $supplier_organization_id);
	}

	/*
	 * elenco articoli con gestione del flag_presente_articlesorders
	 */	
	public function admin_index_flag_presente_articlesorders() {

		if ($this->request->is('post') || $this->request->is('put')) {
			/*
			echo "<pre>";
			print_r($this->request->data);
			echo "<pre>";
			*/
			$articles_in_articlesorders = $this->request->data['Article']['articles_in_articlesorders'];
			if(!empty($articles_in_articlesorders)) {
				$arrs = split(",", $articles_in_articlesorders);				
				foreach($arrs as $arr) {
					list($article_id, $flag_presente_articlesorders) = explode("-", $arr);
					
					try {
						$sql = "UPDATE ".Configure::read('DB.prefix')."articles 
								SET flag_presente_articlesorders = '".$flag_presente_articlesorders."'
								WHERE organization_id = ".(int)$this->user->organization['Organization']['id']."
									and id = $article_id ";
					 	// echo '<br />Article::index '.$sql;
						$results = $this->Article->query($sql);
					}
					catch (Exception $e) {
						CakeLog::write('error',$sql);
						CakeLog::write('error',$e);
					}
					
				}
			}
		} // end POST
	
		$this->__admin_index('articles', $supplier_organization_id);
	}
	
	private function __admin_index($context, $FilterArticleSupplierId) { 
		$conditions = $this->__admin_index_sql_conditions($context, $FilterArticleSupplierId);
		
		$this->__admin_index_filter_object();
		
		if($context=='articles') 
			$SqlLimit = 25;		else if($context=='order') 			$SqlLimit = 25;
				
		/*
		 * parametri da passare eventualmente a admin_edit
		 */
		$sort = '';
		$direction = '';
		$page = 0;
		if (!empty($this->request->params['named']['sort'])) 
			$sort = $this->request->params['named']['sort'];
		else 
			unset($this->request->params['named']['sort']);
		
		if (!empty($this->request->params['named']['direction'])) 
			$direction = $this->request->params['named']['direction'];
		if (!empty($this->request->params['named']['page'])) 
			$page = $this->request->params['named']['page'];		$this->set('sort', $sort);		$this->set('direction', $direction);		$this->set('page', $page);
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'ArticleTypeIds_hidden')) 
			$fields = array('Article.id,Article.organization_id,Article.supplier_organization_id,Article.supplier_id,Article.prod_gas_article_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.img1,Article.stato,Article.created,Article.modified,Article.flag_presente_articlesorders,SuppliersOrganization.id,SuppliersOrganization.name,SuppliersOrganization.owner_articles,CategoriesArticle.name,ArticlesArticlesType.article_type_id');
		else
			$fields = array('Article.id,Article.organization_id,Article.supplier_organization_id,Article.supplier_id,Article.prod_gas_article_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.img1,Article.stato,Article.created,Article.modified,Article.flag_presente_articlesorders,SuppliersOrganization.id,SuppliersOrganization.name,SuppliersOrganization.owner_articles,CategoriesArticle.name');
	    $this->paginate = array('conditions' => $conditions,
					    		'fields' => $fields,
					    		'group' => 'Article.id,Article.organization_id,Article.supplier_organization_id,Article.supplier_id,Article.prod_gas_article_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.img1,Article.stato,Article.created,Article.modified,Article.flag_presente_articlesorders,SuppliersOrganization.id,SuppliersOrganization.name,SuppliersOrganization.owner_articles,CategoriesArticle.name',
								'order' => 'SuppliersOrganization.name, Article.name','recursive' => 1,'limit' => $SqlLimit);
	    $results = $this->paginate('Article');
	    /*
	    echo "<pre>";
	    print_r($results);
	    echo "</pre>";
	    */
	    $this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
	}

	public function admin_context_articles_index_quick($FilterArticleSupplierId=0) {
		
		$debug = false;
		
		/*		 * ctrl configurazione Organization		*/
		if(!$this->isUserPermissionArticlesOrder($this->user)) {			$this->Session->setFlash(__('msg_not_organization_config'));			$this->myRedirect(Configure::read('routes_msg_stop'));		}
		
		if($this->user->organization['Organization']['type']=='PROD')
			$FilterArticleSupplierId = $this->user->organization['Organization']['prodSupplierOrganizationId'];
		
		$results = array();		$FilterArticleName = null;		$SqlLimit = 1000;		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			if($this->user->organization['Organization']['type']=='PROD')
				$FilterArticleSupplierId = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			else if(isset($this->request->data['ArticlesOrder']['FilterArticleSupplierId']))
				$FilterArticleSupplierId = $this->request->data['ArticlesOrder']['FilterArticleSupplierId'];
			
			if(isset($this->request->data['ArticlesOrder']['FilterArticleName']))				$FilterArticleName = $this->request->data['ArticlesOrder']['FilterArticleName'];				
			if(isset($this->request->data['ArticlesOrder']['article_id_selected']) && !empty($this->request->data['ArticlesOrder']['article_id_selected'])) {
				$array_article_id = explode(',',$this->request->data['ArticlesOrder']['article_id_selected']);
				$msg = '';
				foreach ($array_article_id as $id) {
					
					$options = array();
					$options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
												   'Article.id' => $id);
					$options['recursive'] = -1;
					$articleResults = $this->Article->find('first', $options);	
					if (empty($articleResults)) 						$msg .= 'Errore articolo id '.$id.'<br />';
					else {
						
						$name = $articleResults['Article']['name'];
						
						/*
						  * ctrl gli eventuali acquisti gia' effettuati, se true non posso cancellarlo
						 */
						if($this->Article->isArticleInCart($this->user, $this->user->organization['Organization']['id'], $id)) 
							$msg .= 'Articolo "'.$name.'" non può essere cancellato perchè associato ad alcuni ordini.<br />';				
						else {
							if(!$this->Article->delete($this->user->organization['Organization']['id'], $id))
								$msg .= 'Errore cancellazione articolo "'.$name.'".<br />';
							else
								$msg .= 'Articolo "'.$name.'" cancellato definitivamente.<br />';							
						}

					}					
				}
				$this->Session->setFlash($msg);
			}	
		}  // end if ($this->request->is('post') || $this->request->is('put'))						$conditions = array();		$conditions[] = array('SuppliersOrganization.organization_id'=>(int)$this->user->organization['Organization']['id']);				if(!$this->isSuperReferente()) 			$conditions[] = array('SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');		
		if(!empty($FilterArticleSupplierId)) {
			$conditions[] = array('Article.supplier_organization_id' => $FilterArticleSupplierId);
			if(!empty($this->request->params['pass']['FilterArticleName'])) {				$FilterArticleName = $this->request->params['pass']['FilterArticleName'];				$conditions[] = array('Article.name LIKE '=>'%'.addslashes($FilterArticleName).'%');			}
			
			$this->Article->unbindModel(array('hasOne' => array('ArticlesOrder', 'ArticlesArticlesType')));
			$this->Article->unbindModel(array('hasMany' => array('ArticlesOrder', 'ArticlesArticlesType')));
			$this->Article->unbindModel(array('hasAndBelongsToMany' => array('Order', 'ArticlesType')));
						$this->paginate = array('conditions' => $conditions,
									'order' => 'SuppliersOrganization.name, Article.name',
									'recursive' => 0,
									'limit' => $SqlLimit);			$results = $this->paginate('Article');
			/*
			echo "<pre>";
			print_r($results);
			echo "</pre>";
			*/		}
				/*
		 * get elenco produttori filtrati
		*/
		if($this->user->organization['Organization']['type']=='GAS') {
			if($this->isSuperReferente()) {
				App::import('Model', 'SuppliersOrganization');
				$SuppliersOrganization = new SuppliersOrganization;
				
				$options = array();
				$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
											   'SuppliersOrganization.stato' => 'Y');
				$options['recursive'] = -1;
				$options['order'] = array('SuppliersOrganization.name');
				$ACLsuppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
				$this->set('ACLsuppliersOrganization', $ACLsuppliersOrganizationResults);
			}
			else 
				$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());		}
					/* filtro */		$this->set('FilterArticleSupplierId', $FilterArticleSupplierId);		$this->set('FilterArticleName', $FilterArticleName);			$this->set('results', $results);		$this->set('SqlLimit', $SqlLimit);	}	
	/*
	 * gestisci le categorie degli articoli
	 */
	public function admin_gest_categories($FilterArticleSupplierId=0) {
	
		$debug = false;
		
		/*
		 * ctrl configurazione Organization
		*/
		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='N') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
	
		if($this->user->organization['Organization']['type']=='PROD')
			$FilterArticleSupplierId = $this->user->organization['Organization']['prodSupplierOrganizationId'];
	
		$results = array();
		$FilterArticleName = null;
		$SqlLimit = 1000;
	
		if ($this->request->is('post') || $this->request->is('put')) {
				
			if($debug) {
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";
			}
			
			if($this->user->organization['Organization']['type']=='PROD')
				$FilterArticleSupplierId = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			else if(isset($this->request->data['Article']['FilterArticleSupplierId']))
				$FilterArticleSupplierId = $this->request->data['Article']['FilterArticleSupplierId'];
				
			if(isset($this->request->data['Article']['FilterArticleName']))
				$FilterArticleName = $this->request->data['Article']['FilterArticleName'];
	
			if(isset($this->request->data['Article']['article_id_selected']) && !empty($this->request->data['Article']['article_id_selected'])) {
				$array_article_id = explode(',',$this->request->data['Article']['article_id_selected']);
				$msg = '';
				foreach ($array_article_id as $id) {
					if (!$this->Article->exists($this->user->organization['Organization']['id'], $id))
						$msg .= '<br />Errore articolo id '.$id;
					else {
						$category_article_id = $this->request->data['Article']['category_article_id'];
						
						try {
							$sql = "UPDATE ".Configure::read('DB.prefix')."articles
						   			SET
						   				category_article_id = $category_article_id,
						   				modified = '".date('Y-m-d H:i:s')."'
						   		    WHERE
						   				organization_id = ".$this->user->organization['Organization']['id']."
						   		    AND id = ".$id;
							if($debug) echo '<br />'.$sql;
							$results = $this->Article->query($sql);
						}
						catch (Exception $e) {
							CakeLog::write('error',$sql);
							CakeLog::write('error',$e);
						}						
					}
				} // end foreach ($array_article_id as $id)
					
				if(empty($msg)) $msg = "Le categorie degli articoli sono state aggiornate";
				$this->Session->setFlash($msg);
			}
		}  // end if ($this->request->is('post') || $this->request->is('put'))
	
		$conditions = array();
		$conditions[] = array('SuppliersOrganization.organization_id'=>(int)$this->user->organization['Organization']['id']);
	
		if(!$this->isSuperReferente())
			$conditions[] = array('SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');
	
		if(!empty($FilterArticleSupplierId)) {
	
			$conditions[] = array('Article.supplier_organization_id' => $FilterArticleSupplierId);
			if(!empty($this->request->params['pass']['FilterArticleName'])) {
				$FilterArticleName = $this->request->params['pass']['FilterArticleName'];
				$conditions[] = array('Article.name LIKE '=>'%'.addslashes($FilterArticleName).'%');
			}
				
			$this->Article->unbindModel(array('hasOne' => array('ArticlesOrder', 'ArticlesArticlesType')));
			$this->Article->unbindModel(array('hasMany' => array('ArticlesOrder', 'ArticlesArticlesType')));
			$this->Article->unbindModel(array('hasAndBelongsToMany' => array('Order', 'ArticlesType')));
				
			$this->paginate = array('conditions' => $conditions,
					'order' => 'SuppliersOrganization.name, CategoriesArticle.name, Article.name',
					'recursive' => 0,
					'limit' => $SqlLimit);
			$results = $this->paginate('Article');
		}
	
		/*
		 * get elenco produttori filtrati
		*/
		if($this->user->organization['Organization']['type']=='GAS') {
			if($this->isSuperReferente()) {
				App::import('Model', 'SuppliersOrganization');
				$SuppliersOrganization = new SuppliersOrganization;
	
				$options = array();
				$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
						'SuppliersOrganization.stato' => 'Y');
				$options['recursive'] = -1;
				$options['order'] = array('SuppliersOrganization.name');
				$ACLsuppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
				$this->set('ACLsuppliersOrganization', $ACLsuppliersOrganizationResults);
			}
			else
				$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
		}

		/*
		 * get elenco categorie
		*/
		$conditionsCategoriesArticle = array('organization_id' => $this->user->organization['Organization']['id']);
		$categories = $this->Article->CategoriesArticle->generateTreeList($conditionsCategoriesArticle, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('categories'));
		
		/* filtro */
		$this->set('FilterArticleSupplierId', $FilterArticleSupplierId);
		$this->set('FilterArticleName', $FilterArticleName);
	
		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
	}
	
	function admin_index_edit_prices_default() {
		
		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			if($debug) {
				echo "<pre>";
			//	print_r($this->request->data);
				echo "</pre>";
			}
			
			$msg = '';
			if(isset($this->request->data['Article']['prezzo'])) {
				foreach ($this->request->data['Article']['prezzo'] as $key => $value) {
					$article_id = $key;
					
					if($debug) echo '<br />tratto article '.$article_id.' con valore '.$value;
					
					if($value != $this->request->data['Article']['prezzo_old'][$article_id]) {
	
						if (!$this->Article->exists($this->user->organization['Organization']['id'], $article_id)) {
							$msg .= "<br />articolo ".$article_id." non esiste!";
						}
						
						$options = array();
						$options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
													  'Article.id' => $article_id);
						$options['recursive'] = -1;							  
						$row = $this->Article->find('first', $options);
						
						$row['Article']['prezzo'] = $value;
				
						if($debug) {
							echo "<pre>SAVE \n";
							print_r($row);
							echo "</pre>";
						}
				
						$this->Article->create();
						if (!$this->Article->save($row)) {
							$msg .= "<br />articolo ".$article_id." errore!";
						}
						else {
							
							/*
							 * se non ho attivato il modulo hasArticlesOrder 
							 * aggiorno sempre gli articoli associati agli ordini (con stato_elaborazione != CLOSE) 
							 */ 
							if(!$this->isUserPermissionArticlesOrder($this->user)) 
								$this->request->data['Article']['updateArticlesOrder']='Y';
							
							/*
							 * aggiorno gli articoli associati agli ordini
							 */ 
							if($this->request->data['Article']['updateArticlesOrder']=='Y') {
								
								/*								 * gestione della sincronizzazione dell'articolo associato all'ordine								*/							
								if($this->isUserPermissionArticlesOrder($this->user)) // se ho il modulo attivato devo modificarlo a mano									if(!$this->Article->syncronizeArticlesOrder($this->user, $article_id,'UPDATE', $debug))										$msg .= __('The articles order syncronize could not be saved. Please, try again.');							} // end if($this->request->data['Article']['updateArticlesOrder']=='Y')
						}
						
					}
				} // loop articles
			}
			else {
				$msg = "Nessun articolo presente da poter aggiornare";
			}
			
			if(!empty($msg))
				$this->Session->setFlash($msg);
			else
				$this->Session->setFlash(__('The price to articles order has been saved'));
			
		} // end if ($this->request->is('post') || $this->request->is('put')) 
		
		$this->__index_edit_prices();
	}

	function admin_index_edit_prices_percentuale() {
		$this->__index_edit_prices();
		
		$stato = ClassRegistry::init('Article')->enumOptions('stato');
		$stato['ALL'] = 'Tutti';
		$this->set(compact('stato'));
	}
	
	private function __index_edit_prices() {
		
		$SqlLimit = 2000;
		$FilterArticleSupplierId = null;
		if(isset($this->request->data['Article']['FilterArticleSupplierId'])) // recupero il produttore filtrato
			$FilterArticleSupplierId = $this->request->data['Article']['FilterArticleSupplierId'];
			
		$conditions = array();
		$conditions[] = array('SuppliersOrganization.organization_id'=>(int)$this->user->organization['Organization']['id']);
	
		if($this->isSuperReferente()) {
			$conditions[] = array('SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');
		}
	
		/* recupero dati */
		if (!empty($this->request->params['pass']['FilterArticleSupplierId'])) {
			$FilterArticleSupplierId = $this->request->params['pass']['FilterArticleSupplierId'];
			$conditions[] = array('Article.supplier_organization_id'=>$FilterArticleSupplierId);
		}
					
		/* filtro */
		$this->set('FilterArticleSupplierId', $FilterArticleSupplierId);
		
		$results = array();
		if (!empty($this->request->params['pass']['FilterArticleSupplierId'])) {
			
			$this->Article->unbindModel(array('hasOne' => array('ArticlesOrder', 'ArticlesArticlesType')));			$this->Article->unbindModel(array('hasMany' => array('ArticlesOrder', 'ArticlesArticlesType')));			$this->Article->unbindModel(array('hasAndBelongsToMany' => array('Order', 'ArticlesType')));							$this->paginate = array('conditions' => $conditions,									'order' => 'SuppliersOrganization.name, Article.name',									'recursive' => 0,									'limit' => $SqlLimit);			$results = $this->paginate('Article');				
		}
		$this->set('results', $results);

		
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.stato' => 'Y');
			$options['recursive'] = -1;
			$options['order'] = array('SuppliersOrganization.name');
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else 
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
		
		if($this->action=='default')
			$this->render('admin_index_edit_prices');
		else
		if($this->action=='percentuale')
			$this->render('admin_index_edit_prices_percentuale');
	}
	
	public function admin_context_order_add() {
		$results = $this->__set_context_order($this->order_id);
		$supplier_organization_id = $results['Order']['supplier_organization_id'];		$this->__admin_add('order', $supplier_organization_id);	}		public function admin_context_articles_add() {		$this->__admin_add('articles');	}	
	/*
	 * se aggiungo un articolo richiamo syncronizeArticlesOrder
	 *	ctrl se il produttore e' associato ad ordine
	 * 		se si => lo aggiungo in automatico
	 *  	se e' ordine DES no
	 */ 
	private function __admin_add($context, $supplier_organization_id=0) {
		
		if ($this->request->is('post') || $this->request->is('put')) {	

			$msg = "";	
			$this->request->data['Article']['organization_id'] = $this->user->organization['Organization']['id'];

			if($this->user->organization['Organization']['type']=='PROD')
				$this->request->data['Article']['supplier_organization_id'] = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			
			if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='N')
				$this->request->data['Article']['category_article_id'] = 0;
			else 
			if(empty($this->request->data['Article']['category_article_id'])) $this->request->data['Article']['category_article_id'] = 0;				
			if($this->user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
				$this->request->data['Article']['alert_to_qta'] = 0;
			
			$this->request->data['Article']['flag_presente_articlesorders'] = 'Y';
			
			$this->request->data['Article']['id'] = $this->Article->getMaxIdOrganizationId($this->user->organization['Organization']['id']);
			
			/*
			 * richiamo la validazione 
			 */
			$this->Article->set($this->request->data);			if(!$this->Article->validates()) {
			
					$errors = $this->Article->validationErrors;
					$tmp = '';
					$flatErrors = Set::flatten($errors);
					if(count($errors) > 0) { 
						$tmp = '';
						foreach($flatErrors as $key => $value) 
							$tmp .= $value.' - ';
					}
					$msg .= "Articolo non inserito: dati non validi, $tmp<br />";
					$this->Session->setFlash($msg);
			}
			else {
				$this->Article->create();
				if($this->Article->save($this->request->data)) {
				
					$id = $this->request->data['Article']['id'];
					$msg = __('The article has been saved');
						
					/*					 * Articles Type 					*/
					if(!$this->Article->articlesTypesSave($this->request->data))
						$msg .= '<br />'.__('The articlesType could not be saved. Please, try again.');
				
					$this->Article->syncronizeArticleTypeBio($this->user, $this->request->data['Article']['id']);
				
					/*
					 * gestione della sincronizzazione dell'articolo associato all'ordine
					*/
					if($this->isUserPermissionArticlesOrder($this->user)) // se ho il modulo attivato devo modificarlo a mano
						if(!$this->Article->syncronizeArticlesOrder($this->user, $this->request->data['Article']['id'],'INSERT'))
							$msg .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');
			
				
					/*
				 	* IMG1 upload
					*/
					if(!empty($this->request->data['Document']['img1']['name'])) {
						$esito_upload = $this->__upload_img($id);
						if($esito_upload===true)
							$msg .= "<br />e l'immagine caricata";
						else
							$msg .= "<br />ma l'immagine non è caricata per un errore:<br/>$esito_upload";
					}
				
					$this->Session->setFlash($msg);
					
					$this->myRedirect(array('action' => $this->request->data['Article']['action_post'],'supplier_organization_id' => $this->request->data['Article']['supplier_organization_id']));  // context_$context_index, add
				} else {
					$this->Session->setFlash(__('The article could not be saved. Please, try again.'));
				}
			}  // end if(!$this->Article->validates()) 
		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		/*
		 * recupero il produttore se ho "salvato + continua ad inserire"
		 */
		if($this->user->organization['Organization']['type']=='GAS') {
			if(isset($this->request->pass['supplier_organization_id'])) 
				$supplier_organization_id = $this->request->pass['supplier_organization_id'];
			$this->set('supplier_organization_id',$supplier_organization_id);
		}

		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y')  {
			$conditions = array();
			$conditions = array('organization_id' => $this->user->organization['Organization']['id']);
			$categories = $this->Article->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
			$this->set(compact('categories'));		}
		
		$um = ClassRegistry::init('Article')->enumOptions('um');
		$this->set(compact('um'));
		$stato = ClassRegistry::init('Article')->enumOptions('stato');
		$this->set(compact('stato'));

		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.stato' => 'Y');
			$options['recursive'] = -1;
			$options['order'] = array('SuppliersOrganization.name');
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else 
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
	}

	public function admin_context_order_edit($id = null) {
		$this->__set_context_order($this->order_id);		$this->__admin_edit('order', $id);	}		public function admin_context_articles_edit($id = null) {		$this->__admin_edit('articles', $id);	}	
	/*
	 * $sort					 passati dalla ricerca da admin_index  sort:value
	 * $direction				 passati dalla ricerca da admin_index  direction:asc
	 * $page					 passati dalla ricerca da admin_index  page:2
	 */
	private function __admin_edit($context, $id) {
		
		if (!$this->Article->exists($this->user->organization['Organization']['id'], $id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		/*
		 * messaggio per Article.stato
		 * ctrl se articolo associato ad eventuali ArticlesOrder
		 * 		se empty($resultsAssociateArticlesOrder) non e' associato
		 *
		 * se acquistato NON posso cambiare lo stato in N (non lo vedrei + in tutti gli ordini e rich di pagamento)
		*/
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
			
		$conditions = array('Article.id' => $id);
		$resultsAssociateArticlesOrder = $ArticlesOrder->getArticlesOrdersInOrder($this->user ,$conditions);
		$this->set('resultsAssociateArticlesOrder', $resultsAssociateArticlesOrder);

		/*
 		 * ctrl gli eventuali acquisti gia' effettuati
		 */
		$isArticleInCart = $this->Article->isArticleInCart($this->user, $this->user->organization['Organization']['id'], $id);
		$this->set('isArticleInCart', $isArticleInCart);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			$msg = "";
			
			/*
			 * Article.state prima del salvataggio
			 */
			$options = array();
			$options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],										   'Article.id' => $id);			$options['recursive'] = -1;			$options['fields'] = array('stato', 'img1');			$resultsOld = $this->Article->find('first', $options);
						
			
			$this->request->data['Article']['organization_id'] = $this->user->organization['Organization']['id'];
			
			if($this->user->organization['Organization']['type']=='PROD')
				$this->request->data['Article']['supplier_organization_id'] = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			
			if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='N')				$this->request->data['Article']['category_article_id'] = 0;			else			if(empty($this->request->data['Article']['category_article_id'])) $this->request->data['Article']['category_article_id'] = 0;
			
			if($this->user->organization['Organization']['hasFieldArticleAlertToQta']=='N')				$this->request->data['Article']['alert_to_qta'] = 0;
					
				
			/*			 * richiamo la validazione			*/			$this->Article->set($this->request->data);			if(!$this->Article->validates()) {
			
					$errors = $this->Article->validationErrors;
					$tmp = '';
					$flatErrors = Set::flatten($errors);
					if(count($errors) > 0) { 
						$tmp = '';
						foreach($flatErrors as $key => $value) 
							$tmp .= $value.' - ';
					}
					$msg .= "Articolo non inserito: dati non validi, $tmp<br />";
					$this->Session->setFlash($msg);											}			else {
				/*
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";	
				*/				if($this->Article->save($this->request->data)) {
				
					$msg = __('The article has been saved');
				
					/*					 * Articles Type					*/					// if(!empty($this->request->data['Article']['article_type_id_hidden']))					if(!$this->Article->articlesTypesSave($this->request->data))						$msg .= '<br />'.__('The articlesType could not be saved. Please, try again.');					
					$this->Article->syncronizeArticleTypeBio($this->user, $this->request->data['Article']['id']);
					
					/*					 * gestione della sincronizzazione dell'articolo associato all'ordine					*/
					if($this->isUserPermissionArticlesOrder($this->user)) // se ho il modulo attivato devo modificarlo a mano						if(!$this->Article->syncronizeArticlesOrder($this->user, $this->request->data['Article']['id'],'UPDATE'))							$msg .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');				
					/*
					 * se lo stato = N
					 * 		- cancello l'associazione con gli ordini (trigger cancella gli acquisti effettuati)
					 */
					if($resultsOld['Article']['stato']=='Y' && $this->request->data['Article']['stato']=='N') {
						//if(!$this->Article->syncronizeArticlesOrder($this->user, $this->request->data['Article']['id'],'DELETE'))
						//	$msg .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');
					}
					else
					if($resultsOld['Article']['stato']=='N' && $this->request->data['Article']['stato']=='Y') {
						if($this->isUserPermissionArticlesOrder($this->user)) // l'utente gestisce l'associazione degli articoli con l'ordine
							if(!$this->Article->syncronizeArticlesOrder($this->user, $this->request->data['Article']['id'],'INSERT'))
								$msg .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');
					}	

					/*
					 * IMG1 delete
					 */
					if($this->request->data['Article']['file1_delete'] == 'Y') {
						$esito_delete = $this->__delete_img($id, $resultsOld['Article']['img1'], false);
						if($esito_delete)
							$msg .= "<br />e l'immagine cancellata";
						else
							$msg .= '<br />'.$esito_delete;						
					}
					
						
					/*
					 * IMG1 upload
					*/
					if(!empty($this->request->data['Document']['img1']['name'])) {
						$esito_upload = $this->__upload_img($id);
						if($esito_upload===true)
							$msg .= "<br />e l'immagine caricata";
						else
							$msg .= "<br />ma l'immagine non è caricata per un errore:<br />$esito_upload";
					}
										
					$this->Session->setFlash($msg);
						
					$filterParams = '';					$filterParams .= '&sort:'.$this->request->data['Article']['sort'];
					$filterParams .= '&direction:'.$this->request->data['Article']['direction'];
					$filterParams .= '&page:'.$this->request->data['Article']['page'];
					$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_'.$context.'_index'.$filterParams);  
				} else {
					$this->Session->setFlash(__('The article could not be saved. Please, try again.'));
				}
			} // end if(!$this->Article->validates())	
		} // end if ($this->request->is('post') || $this->request->is('put'))

	   /*
	    * ottendo i dati anagrafici degli Articoli 
	    * 	Article, SupplierOrganization, CategoriesArticle, ArticlesType
	    */
		$options = array();
		$options['conditions'] = array('Article.id' => $id);
		$this->request->data = $this->Article->getArticlesDataAnagr($this->user, $options);
		
		if(!empty($this->request->data['Article']['img1']) && 
		   file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$this->request->data['Article']['organization_id'].DS.$this->request->data['Article']['img1'])) {
			
			$file1 = new File(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$this->request->data['Article']['organization_id'].DS.$this->request->data['Article']['img1']);
			$this->set('file1', $file1);
		}
		
		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y')  {
			$conditions = array();
			$conditions = array('organization_id' => $this->user->organization['Organization']['id']);			$categories = $this->Article->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
			$this->set(compact('categories'));		}
				
		$um = ClassRegistry::init('Article')->enumOptions('um');
		$this->set(compact('um'));
		
		if($isArticleInCart)	
			$stato = array('Y' => 'Si');
		else
			$stato = ClassRegistry::init('Article')->enumOptions('stato');
		$this->set(compact('stato'));	
		
		$flag_presente_articlesorders = ClassRegistry::init('Article')->enumOptions('flag_presente_articlesorders');
		$this->set(compact('flag_presente_articlesorders'));	
		
		
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.stato' => 'Y');
			$options['recursive'] = -1;
			$options['order'] = array('SuppliersOrganization.name');
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else 
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
		
		/*
		 * parametri di ricerca da ripassare a admin_index
		 */ 
		$sort = '';		$direction = '';		$page = 0;		if (!empty($this->request->params['named']['sort']))			$sort = $this->request->params['named']['sort'];		if (!empty($this->request->params['named']['direction']))			$direction = $this->request->params['named']['direction'];		if (!empty($this->request->params['named']['page']))			$page = $this->request->params['named']['page'];		$this->set('sort', $sort);		$this->set('direction', $direction);		$this->set('page', $page);
	}

	public function admin_context_articles_view($id = null) {
		if (!$this->Article->exists($this->user->organization['Organization']['id'], $id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

	   /*
	    * ottendo i dati anagrafici degli Articoli 
	    * 	Article, SupplierOrganization, CategoriesArticle, ArticlesType
	    */
		$options = array();
		$options['conditions'] = array('Article.id' => $id);
		$this->request->data = $this->Article->getArticlesDataAnagr($this->user, $options);
		
		/*
		 * parametri di ricerca da ripassare a admin_index
		 */ 
		$sort = '';
		$direction = '';
		$page = 0;
		if (!empty($this->request->params['named']['sort']))
			$sort = $this->request->params['named']['sort'];
		if (!empty($this->request->params['named']['direction']))
			$direction = $this->request->params['named']['direction'];
		if (!empty($this->request->params['named']['page']))
			$page = $this->request->params['named']['page'];
		$this->set('sort', $sort);
		$this->set('direction', $direction);
		$this->set('page', $page);
	}
	
	public function admin_context_order_copy($context, $id) {
		$results = $this->__set_context_order($this->order_id);
		$supplier_organization_id = $results['Order']['supplier_organization_id'];
		$this->__admin_copy('order', $id);
		
		$this->Session->setFlash(__('The delivery could not be copied. Please, try again.'));
		$this->myRedirect(array('action' => 'context_order_index'));
	}
	
	public function admin_context_articles_copy($id) {
		$this->__admin_copy('articles',$id);
		
		$this->Session->setFlash(__(Configure::read('sys_function_not_implement')));
		$this->myRedirect(array('action' => 'context_articles_index'));
	}
	
	private function __admin_copy($context, $id=0) {
	
		$debug = false;
		$url = "";
		
		if (!$this->Article->exists($this->user->organization['Organization']['id'], $id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		/*
		 * parametri di ricerca da ripassare a admin_index
		*/
		$sort = '';
		$direction = '';
		$page = 0;
		if (!empty($this->request->params['named']['sort']))
			$sort = $this->request->params['named']['sort'];
		if (!empty($this->request->params['named']['direction']))
			$direction = $this->request->params['named']['direction'];
		if (!empty($this->request->params['named']['page']))
			$page = $this->request->params['named']['page'];
		$this->set('sort', $sort);
		$this->set('direction', $direction);
		$this->set('page', $page);
		
		$results = $this->Article->copy_prepare($this->user, $id, $debug);
		$results = $this->Article->copy_img($this->user, $this->user->organization['Organization']['id'], $results, $debug);
		$results = $this->Article->copy_article_type($this->user, $results, $debug);

		if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";
		}
	
		$this->Article->create();
		if ($this->Article->save($results['Article'], array('validate' => false))) {
				
			$this->Session->setFlash(__('The article has been copied'));

			if($context=='order')
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_order_edit&id='.$results['Article']['id'];
			else
			if($context=='articles')
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_articles_edit&id='.$results['Article']['id'];			
		}
		else {
			$this->Session->setFlash(__('The article could not be copied. Please, try again.'));

			if($context=='order')
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_order_index';
			else
			if($context=='articles')
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_articles_index';
				
		}

		if($debug) {
			echo '<br />url '.$url;
			exit;
		}
		$this->myRedirect($url);
	}
	
	public function admin_context_order_delete($id = null) {		$this->__set_context_order($this->order_id);		$this->__admin_delete('order', $id);	}		public function admin_context_articles_delete($id = null) {		$this->__admin_delete('articles', $id);	}
	
   /*
    * articles_Trigger
	*	* $sort					 passati dalla ricerca da admin_index  sort:value	* $direction		     passati dalla ricerca da admin_index  direction:asc	* $page					 passati dalla ricerca da admin_index  page:2	*/	private function __admin_delete($context, $id) {	
		$debug = false;
		
		if (!$this->Article->exists($this->user->organization['Organization']['id'], $id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		/*
 		 * ctrl gli eventuali acquisti gia' effettuati, se true non posso cancellarlo
		 */
		$isArticleInCart = $this->Article->isArticleInCart($this->user, $this->user->organization['Organization']['id'], $id);
		$this->set('isArticleInCart', $isArticleInCart);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$filterParams = '';
			$filterParams .= '&sort:'.$this->request->data['Article']['sort'];
			$filterParams .= '&direction:'.$this->request->data['Article']['direction'];
			$filterParams .= '&page:'.$this->request->data['Article']['page'];
			
			if($isArticleInCart) 
				$this->Session->setFlash(__('IsArticleInCart'));
			else {
			
				/*
				 * Article prima del salvataggio
				*/
				$options = array();
				$options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
											  'Article.id' => $id);
				$options['recursive'] = -1;
				$options['fields'] = array('img1');
				$resultsOld = $this->Article->find('first', $options);

				/*
				 * gestione della sincronizzazione dell'articolo associato all'ordine
				 * prima di delete se no non ho + l'articolo
				*/
				if($this->isUserPermissionArticlesOrder($this->user)) // se ho il modulo attivato devo modificarlo a mano
					if(!$this->Article->syncronizeArticlesOrder($this->user, $id,'DELETE', $debug))
						$msg .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');
				
				if ($this->Article->delete($this->user->organization['Organization']['id'], $id)) {
					
					$msg = __('Delete Article');
					
					/*
					 * IMG1 delete
					*/
					if(!empty($resultsOld['Article']['img1'])) {
						$esito_delete = $this->__delete_img($id, $resultsOld['Article']['img1'], false);
						if($esito_delete)
							$msg .= "<br />e l'immagine cancellata";
						else
							$msg .= '<br />'.$esito_delete;
					}

					$this->Session->setFlash($msg);
				}	
				else
					$this->Session->setFlash(__('Article was not deleted'));
			} // end if($isArticleInCart)
				
			if(!$debug) $this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_'.$context.'_index'.$filterParams);					}
		
		/*		 * estraggo articolo		*/
		$options = array();
		$options['conditions'] = array('Article.id' => $id);
		$this->request->data = $this->Article->getArticlesDataAnagr($this->user, $options);
		
		/*
		 * img1 
		 */
		if(!empty($this->request->data['Article']['img1']) &&
		file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$this->request->data['Article']['organization_id'].DS.$this->request->data['Article']['img1'])) {
				
			$file1 = new File(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$this->request->data['Article']['organization_id'].DS.$this->request->data['Article']['img1']);
			$this->set('file1', $file1);
		}
		
		/*
		 * estraggo gli ordini associati all'articolo
		 */
		$this->Article->ArticlesOrder->unbindModel(array('belongsTo' => array('Article', 'Cart')));
		$options = array();
		$options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],										'ArticlesOrder.article_id' => $id);		$options['recursive'] = 0;		$results = $this->Article->ArticlesOrder->find('all', $options);
		
		App::import('Model', 'Delivery');
		
		foreach ($results as $numResult => $result) {
			
			/*
			 * consegna
			 */
			$Delivery = new Delivery;
			$deliveryResults = $Delivery->read($this->user->organization['Organization']['id'], null, $result['Order']['delivery_id']);
			$results[$numResult]['Delivery'] = $deliveryResults['Delivery'];
		}
		$this->set(compact('results'));
		
		/*		 * parametri di ricerca da ripassare a admin_index		*/		$sort = '';		$direction = '';		$page = 0;		if (!empty($this->request->params['named']['sort']))			$sort = $this->request->params['named']['sort'];		if (!empty($this->request->params['named']['direction']))			$direction = $this->request->params['named']['direction'];		if (!empty($this->request->params['named']['page']))			$page = $this->request->params['named']['page'];		$this->set('sort', $sort);		$this->set('direction', $direction);		$this->set('page', $page);		
	}
	
	private function __set_context_order($order_id) {
		
		App::import('Model', 'Order');		$Order = new Order;		
		$Order->id = $order_id;		if (!$Order->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}
		
		$results = $Order->read($this->user->organization['Organization']['id'], null, $order_id);		
		return $results;
	}

	/*	 * crea sql per l'elenco articoli	*/	private function __admin_index_sql_conditions($context, $FilterArticleSupplierId) {			$FilterArticleCategoryArticleId = null;		$FilterArticleName = null;		$FilterArticleOrderId = 0;		$FilterArticleArticleIds = null;		$FilterArticleStato = 'Y';
		$FilterArticleFlagPresenteArticlesorders = 'ALL';		$FilterArticleUm = null;				$conditions = array();			/* recupero dati dalla Session gestita in appController::beforeFilter */
		
		/*
		 * fields Article
		 */		if($context=='articles') {			if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'SupplierId')) {				$FilterArticleSupplierId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'SupplierId');				$conditions[] = array('Article.supplier_organization_id' => $FilterArticleSupplierId);			}		}		else		if($context=='order')			$conditions[] = array('Article.supplier_organization_id' => $FilterArticleSupplierId);						if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'CategoryArticleId')) {			$FilterArticleCategoryArticleId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'CategoryArticleId');			$conditions[] = array('Article.category_article_id' => $FilterArticleCategoryArticleId);		}		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Name')) {			$FilterArticleName = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Name');			$conditions[] = array('Article.name LIKE '=>'%'.$FilterArticleName.'%');		}
		
		/*		 * solo per il context=article		* per context=order Article.stato sempre a Y (se un articolo legato ad un ordine modifica lo stato a N viene cancellato dagli ordini)		*/		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Stato')) {			$FilterArticleStato = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Stato');			if($FilterArticleStato!='ALL')				$conditions[] = array('Article.stato' => $FilterArticleStato);		}		else {			if(!empty($FilterArticleStato))  // cosi' di default e' Y				$conditions[] = array('Article.stato' => $FilterArticleStato);		}
		
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'FlagPresenteArticlesorders')) {
			$FilterArticleFlagPresenteArticlesorders = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'FlagPresenteArticlesorders');
			if($FilterArticleFlagPresenteArticlesorders!='ALL')
				$conditions[] = array('Article.flag_presente_articlesorders' => $FilterArticleFlagPresenteArticlesorders);
		}
		else {
			if(!empty($FilterArticleFlagPresenteArticlesorders) && $FilterArticleFlagPresenteArticlesorders!='ALL')  
				$conditions[] = array('Article.flag_presente_articlesorders' => $FilterArticleFlagPresenteArticlesorders);
		}
		
				if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Um')) {			$FilterArticleUm = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Um');			$conditions[] = array('Article.um' => $FilterArticleUm);		}			/*		 * fields articleType  $hasAndBelongsToMany, $hasOne, 		*/		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'ArticleTypeIds_hidden')) {			$FilterArticleArticleIds = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'ArticleTypeIds_hidden');				$conditions[] = array('ArticlesArticlesType.article_type_id IN ('.$FilterArticleArticleIds.')');
		}

		$this->Article->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = '.$this->user->organization['Organization']['id'];
		$this->Article->hasMany['ArticlesArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
		$this->Article->hasAndBelongsToMany['ArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
				
		/*
		 * fields articleOrder  $hasAndBelongsToMany, $hasOne, 
		*/
		if($context=='articles') {			if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'OrderId')) {				$FilterArticleOrderId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'OrderId');				$conditions[] = array('ArticlesOrder.order_id' => $FilterArticleOrderId);
				
			$this->Article->hasOne['ArticlesOrder']['conditions'] = 'ArticlesOrder.article_organization_id = Article.organization_id and Article.organization_id = '. $this->user->organization['Organization']['id'].' and ArticlesOrder.order_id = '.$FilterArticleOrderId;
			$this->Article->hasMany['ArticlesOrder']['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
																		  'ArticlesOrder.order_id' => $FilterArticleOrderId);
			$this->Article->hasAndBelongsToMany['Order']['conditions'] = array('Order.organization_id' => 'ArticlesOrder.organization_id',
																			   'Order.organization_id' => $this->user->organization['Organization']['id'],
																				'ArticlesOrder.order_id' => $FilterArticleOrderId);			}	
			else {
				$this->Article->unbindModel(array('hasOne' => array('ArticlesOrder')));				$this->Article->unbindModel(array('hasMany' => array('ArticlesOrder')));				$this->Article->unbindModel(array('hasAndBelongsToMany' => array('Order')));			
			}		}		else		if($context=='order') {
			$this->Article->hasOne['ArticlesOrder']['conditions'] = 'ArticlesOrder.article_organization_id = Article.organization_id and Article.organization_id = '. $this->user->organization['Organization']['id'].' and ArticlesOrder.order_id ='.$this->order_id;
			$this->Article->hasMany['ArticlesOrder']['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
																		  'ArticlesOrder.order_id' => $this->order_id);
			$this->Article->hasAndBelongsToMany['Order']['conditions'] = array('Order.organization_id' => 'ArticlesOrder.organization_id',
																			   'Order.organization_id' => $this->user->organization['Organization']['id'],
																				'ArticlesOrder.order_id' => $this->order_id);			$conditions[] = array('ArticlesOrder.order_id' => $this->order_id);
		}
		
		/*		 * ctrl se non e' ancora stata effettuata una ricerca		* */		if(empty($conditions))			$this->set('iniCallPage', true);		else			$this->set('iniCallPage', false);			/*		 * conditions obbligatorie		*/		$conditions[] = array('Article.organization_id'=>(int)$this->user->organization['Organization']['id']);		$conditions[] = array('SuppliersOrganization.organization_id'=>(int)$this->user->organization['Organization']['id']);			if(!$this->isSuperReferente())			$conditions[] = array('SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');	
		/*		echo "<pre>";		print_r($conditions);		echo "</pre>";		*/

		$this->set('FilterArticleSupplierId', $FilterArticleSupplierId);		$this->set('FilterArticleCategoryArticleId', $FilterArticleCategoryArticleId);		$this->set('FilterArticleName', $FilterArticleName);		$this->set('FilterArticleOrderId', $FilterArticleOrderId);		$this->set('FilterArticleArticleIds', $FilterArticleArticleIds);
		$this->set('FilterArticleStato', $FilterArticleStato);
		$this->set('FilterArticleFlagPresenteArticlesorders', $FilterArticleFlagPresenteArticlesorders);		$this->set('FilterArticleUm', $FilterArticleUm);			return $conditions;	}		/*	 * crea i filtri l'elenco articoli	*/	private function __admin_index_filter_object() {
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
				
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
											'SuppliersOrganization.stato' => 'Y');
			$options['recursive'] = -1;
			$options['order'] = array('SuppliersOrganization.name');
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());				/*		 * get elenco ordini filtrati		*/		App::import('Model', 'Order');
		$Order = new Order;
		
		$conditionsOrder = array('Order.organization_id' => (int)$this->user->organization['Organization']['id'],								 'Order.isVisibleBackOffice'=> 'Y');		if(!$this->isSuperReferente())			$conditionsOrder += array('Order.supplier_organization_id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');			$results = $Order->find('all',array('conditions' => $conditionsOrder,				'order' => 'Delivery.data ASC, Order.data_inizio ASC','recursive'=>1));		$orders = array();		if(!empty($results)) 			foreach ($results as $result) {
				if($result['Delivery']['sys']=='N')
					$label = $result['Delivery']['luogoData'];
				else 
					$label = $result['Delivery']['luogo'];
				
				if($result['Order']['data_fine_validation']!='0000-00-00')
					$data_fine = $result['Order']['data_fine_validation_'];
				else
					$data_fine = $result['Order']['data_fine_'];
								$orders[$result['Order']['id']] = $result['Delivery']['luogo'].' '.$result['SuppliersOrganization']['name'].' - dal '.$result['Order']['data_inizio_'].' al '.$data_fine;
			}			$this->set(compact('orders'));			/*		 * get elenco categorie		*/
		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y')  {			$conditionsCategoriesArticle = array('organization_id' => $this->user->organization['Organization']['id']);			$categories = $this->Article->CategoriesArticle->generateTreeList($conditionsCategoriesArticle, null, null, '&nbsp;&nbsp;&nbsp;');
			$this->set(compact('categories'));		}
			
		$um = ClassRegistry::init('Article')->enumOptions('um');
		$flag_presente_articlesorders = array('Y' => __('Y'), 'N' => __('No'), 'ALL' => __('ALL'));
		$this->set(compact('um', 'flag_presente_articlesorders'));
		
		
		/*
		 * solo per il context=article
		 * per context=order Article.stato sempre a Y (se un articolo legato ad un ordine modifica lo stato a N viene cancellato dagli ordini)
		 */		$stato = array('Y' => __('StatoY'), 'N' => __('StatoN'), 'ALL' => __('ALL'));		$this->set(compact('stato'));	}
	
	private function __delete_img($article_id, $img1, $debug=false) {

		$img_path = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$this->user->organization['Organization']['id'].DS;
		
		if($debug) {
			echo "<br >img1 $img1";
			echo "<br >img_path $img_path";
		}
		
		$esito = true;
		
		$file1 = new File($img_path.$img1, false, 0777);
		if($debug) {
			echo "<pre>";
			print_r($file1);
			echo "</pre>";
		}
		
		if(!$file1->delete()) 
			$esito = "<br />File $img1 non cancellato";
		
		
		/*
		 * update database
		*/
		$sql = "UPDATE
					".Configure::read('DB.prefix')."articles
				SET img1 = ''
				WHERE
					organization_id = ".$this->user->organization['Organization']['id']."
					AND id = ".$article_id;
		if($debug) echo "<br >sql $sql";
		try {
			$this->Article->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}		
		
		if($debug) exit;
		
		return $esito;
	}
	
	private function __upload_img($article_id, $debug=false) {
		
		if($debug) {
			echo "<pre>";
			print_r($this->request->data['Document']);
			echo "</pre>";
		}
		
		$esito = true;
		
		/*
		 * 	$img1 = array(
		 		* 		'name' => 'immagine.jpg',
		 		* 		'type' => 'image/jpeg',
		 		* 		'tmp_name' => /tmp/phpsNYCIB',
		 		* 		'error' => 0,
		 		*		'size' => 41737,
		 		* 	);
		*
		* UPLOAD_ERR_OK (0): Non vi sono errori, l’upload e' stato eseguito con successo;
		* UPLOAD_ERR_INI_SIZE (1): Il file inviato eccede le dimensioni specificate nel parametro upload_max_filesize di php.ini;
		* UPLOAD_ERR_FORM_SIZE (2): Il file inviato eccede le dimensioni specificate nel parametro MAX_FILE_SIZE del form;
		* UPLOAD_ERR_PARTIAL (3): Upload eseguito parzialmente;
		* UPLOAD_ERR_NO_FILE (4): Nessun file e' stato inviato;
		* UPLOAD_ERR_NO_TMP_DIR (6): Mancanza della cartella temporanea;
		*/
		$img1 = $this->request->data['Document']['img1'];
		if($debug) {
			echo "<pre>img1 ";
			print_r($img1);
			echo "</pre>";
		}
		
		if($img1['error'] == UPLOAD_ERR_OK && is_uploaded_file($img1['tmp_name']))	{
	
			$path_upload = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$this->request->data['Article']['organization_id'].DS;

			/*
			 * ctrl exstension / content type
			*/
			$ext = strtolower(pathinfo($img1['name'], PATHINFO_EXTENSION));
			
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$type = finfo_file ($finfo, $img1['tmp_name']);
			finfo_close($finfo);
					
       		if(!in_array($ext, Configure::read('App.web.img.upload.extension')) || !in_array($type, Configure::read('ContentType.img'))) {
       			$esito = "Estensione .$ext non valida: si possono caricare file con la seguente estensione ";
       			foreach ( Configure::read('App.web.img.upload.extension') as $estensione) 
       				$esito .= '.'.$estensione.'&nbsp;';	
       			
       			if($debug) {
       				echo "<br />ext ".$ext;
       				echo "<br />type ".$type;       				
       				echo "<br />esito ".$esito;
       				exit;
       			}
       			return $esito;
			}

			$fileNewName = $article_id.'.'.$ext;
			if($debug) {
				echo "<br />path_upload ".$path_upload;
				echo "<br />ext ".$ext;
				echo "<br />fileNewName ".$fileNewName;
			}
			
			if(move_uploaded_file($img1['tmp_name'], $path_upload.$fileNewName)) {
				
				$info = getimagesize($path_upload.$fileNewName);
				$width = $info[0];
				$height = $info[1];
				if($debug) {
					echo "<pre>";
					print_r($info);
					echo "</pre>";
				}
						
					
				/*
				 * ridimensiona img
				 */
				if($width > Configure::read('App.web.img.upload.width.article')) {
					$status = ImageTool::resize(array(
							'input' => $path_upload.$fileNewName,
							'output' => $path_upload.$fileNewName,
							'width' => Configure::read('App.web.img.upload.width.article'),
							'height' => ''
					));
					
					if($debug) echo "<br />ridimensiono ".$status;
				}
										
				/*
				 * update database
				 */
				$sql = "UPDATE
							".Configure::read('DB.prefix')."articles
						SET img1 = '".$fileNewName."' 
						WHERE
							organization_id = ".$this->user->organization['Organization']['id']."
							AND id = ".$article_id;
				try {
					$this->Article->query($sql);
				}
				catch (Exception $e) {
					CakeLog::write('error',$sql);
					CakeLog::write('error',$e);
				}
			}	
			else
				$esito = $img1['error'];
		}
		else
			$esito = $img1['error'];
		
		if($debug) {
			echo "<br />esito ".$esito;
			exit;
		}
		return $esito;
	}
	
}