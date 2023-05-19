<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'ImageTool');

class ArticlesController extends AppController {
				
	public $components = ['Paginator'];
	public $helpers = ['Javascript', 'Tabs', 'Image'];
	private $context;
	private $article_organization_id = 0;
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		/*
		 * non lo passo nelle firma del metodo perche' non viene aggiunto nel action del form
		 */
		if(isset($this->request->data['Article']['article_organization_id'])) 
			$this->article_organization_id = $this->request->data['Article']['article_organization_id']; 
		else
		if(isset($this->request->pass['article_organization_id'])) 
			$this->article_organization_id = $this->request->pass['article_organization_id']; 
		else
			$this->article_organization_id = $this->user->organization['Organization']['id'];
		
		/* ctrl ACL */
		if (in_array($this->action, ['admin_edit', 'admin_delete', 'admin_inverseValue'])) {

			if($this->isSuperReferente()) {
			
			}
			else {
				$id = $this->request->pass['id'];
				$article = $this->Article->read($this->article_organization_id, 'supplier_organization_id', $id);
				$supplier_organization_id = $article['Article']['supplier_organization_id'];
				
				if(!$this->isReferentGeneric() || !in_array($supplier_organization_id, explode(",",$this->user->get('ACLsuppliersIdsOrganization')))) {
					$this->Session->setFlash(__('msg_not_permission'));
					$this->myRedirect(Configure::read('routes_msg_stop'));
				}
			}	
		}	
		/* ctrl ACL */	

		if(Cache::read('articlesTypes')===false) {
			App::import('Model', 'ArticlesType');
			$ArticlesType = new ArticlesType;
			$ArticlesTypeResults = $ArticlesType->prepareArray($ArticlesType->getArticlesTypes());
			Cache::write('articlesTypes',$ArticlesTypeResults);
		}
		else
			$ArticlesTypeResults = Cache::read('articlesTypes');	
		$this->set('ArticlesTypeResults',$ArticlesTypeResults);
	}

	public function admin_context_order_index() {
		$results = $this->_set_context_order($this->order_id);
		$supplier_organization_id = $results['Order']['supplier_organization_id'];
		$this->_admin_index('order', $supplier_organization_id);
	}
	
	public function admin_context_articles_index() {
		
		switch($this->user->organization['Organization']['type']) {
			case 'PROD':
				$supplier_organization_id = $this->user->organization['Organization']['prodSupplierOrganizationId'];
				$this->Session->write(Configure::read('Filter.prefix').$this->modelClass.'SupplierId', $supplier_organization_id);
			break;
			case 'PRODGAS':
			case 'PACT':
				$supplier_organization_id = $this->user->organization['Supplier']['SuppliersOrganization']['id'];
				$this->Session->write(Configure::read('Filter.prefix').$this->modelClass.'SupplierId', $supplier_organization_id);
			break;
            case 'GAS':
            case 'SOCIALMARKET':
				$supplier_organization_id = null;
			break;
			default:
				self::x(__('msg_error_org_type').' ['.$this->user->organization['Organization']['type'].']');
			break;
		}

		$this->_admin_index('articles', $supplier_organization_id);
	}
	
	/*
	 * elenco articoli con gestione del flag_presente_articlesorders
	 */	
	public function admin_index_flag_presente_articlesorders() {

		if ($this->request->is('post') || $this->request->is('put')) {
			
			self::d($this->request->data, false);
			
			$articles_in_articlesorders = $this->request->data['Article']['articles_in_articlesorders'];
			if(!empty($articles_in_articlesorders)) {
				$arrs = explode(",", $articles_in_articlesorders); 				
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
	
		$this->_admin_index('articles', $supplier_organization_id);
	}
	
	private function _admin_index($context, $FilterArticleSupplierId, $debug=false) { 
				
		$ACLsuppliersIdsOrganization = $this->user->get('ACLsuppliersIdsOrganization');
		if(empty($ACLsuppliersIdsOrganization)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
			
		$conditions = $this->_admin_index_sql_conditions($this->user->organization['Organization']['id'], $context, $FilterArticleSupplierId);
		// debug($conditions);

		$this->_admin_index_filter_object();
		
		if($context=='articles') 
			$SqlLimit = 75;
		else if($context=='order') 
			$SqlLimit = 75;
				
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
		else
			$direction = 'ASC';
		if (!empty($this->request->params['named']['page'])) 
			$page = $this->request->params['named']['page'];
		
		$sorts = $this->_getSorts($sort, $direction);
	
		$this->set('sort', $sort);
		$this->set('direction', $direction);
		$this->set('page', $page);

		$orders = $this->_admin_index_sql_order($this->user->organization['Organization']['id']);

		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'ArticleTypeIds_hidden')) 
			$fields = ['Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.img1,Article.stato,Article.created,Article.modified,Article.flag_presente_articlesorders,SuppliersOrganization.id,SuppliersOrganization.owner_organization_id,SuppliersOrganization.owner_supplier_organization_id,SuppliersOrganization.name,SuppliersOrganization.owner_articles,CategoriesArticle.name,ArticlesArticlesType.article_type_id'];
		else
			$fields = ['Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.img1,Article.stato,Article.created,Article.modified,Article.flag_presente_articlesorders,SuppliersOrganization.id,SuppliersOrganization.owner_organization_id,SuppliersOrganization.owner_supplier_organization_id,SuppliersOrganization.name,SuppliersOrganization.owner_articles,CategoriesArticle.name'];

		$this->paginate = ['conditions' => $conditions,
					       'fields' => $fields,
					       'group' => 'Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.img1,Article.stato,Article.created,Article.modified,Article.flag_presente_articlesorders,SuppliersOrganization.id,SuppliersOrganization.owner_organization_id,SuppliersOrganization.owner_supplier_organization_id,SuppliersOrganization.name,SuppliersOrganization.owner_articles,CategoriesArticle.name',
						   'order' => $orders, 
						   'recursive' => 1, 
						   'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];
		$results = $this->paginate('Article');
	    // debug($conditions);
		// debug($results); 
		
	    /*
	     * se empty ctrl se non e' un produttore DES e 
	     *  cerco eventuali articoli del Gas titolare per visualizzarli in lettura
	     * solo se ho scelto un produttore
	     */
	    $isSupplierOrganizationDesTitolare = false;
	    $ownOrganizationResults= [];
	    if(empty($results) && $this->user->organization['Organization']['hasDes']=='Y') {
	    
			if($context=='articles') {
				if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'SupplierId')) {
					$FilterArticleSupplierId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'SupplierId');
				}
			}
			if($debug)
				echo "<br />FilterArticleSupplierId ".$FilterArticleSupplierId;
				
			if(!empty($FilterArticleSupplierId)) {
				App::import('Model', 'DesSupplier');
		  		$DesSupplier = new DesSupplier();
		    	
		    	$desSupplierResults = $DesSupplier->getDesSupplierTitolare($this->user, $FilterArticleSupplierId, $debug);

		    	$own_organization_id = $desSupplierResults['DesSupplier']['own_organization_id'];
		    	$FilterArticleSupplierId = $desSupplierResults['SuppliersOrganization']['owner_supplier_organization_id'];
		    	$ownOrganizationResults= $desSupplierResults;
		    	
			    if(!empty($own_organization_id) && !empty($FilterArticleSupplierId)) { 
			    	/*
			    	 * rifacco query con organization_id del Gas titolare
			    	 */
			    	$conditions = $this->_admin_index_sql_conditions($own_organization_id, $context, $FilterArticleSupplierId);
			    	
				    $this->paginate = ['conditions' => $conditions,
										'fields' => $fields,
										'group' => 'Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.img1,Article.stato,Article.created,Article.modified,Article.flag_presente_articlesorders,SuppliersOrganization.id,SuppliersOrganization.name,SuppliersOrganization.owner_articles,CategoriesArticle.name',
										'order' => ['SuppliersOrganization.name, Article.name'], 'recursive' => 1, 
										'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];
				    $results = $this->paginate('Article');

					$isSupplierOrganizationDesTitolare = true;
		    	}
		    	
		    }
	    } // end if(empty($results) && $this->user->organization['Organization']['hasDes']=='Y')

		self::d($results, false);
		
	    $this->set('isSupplierOrganizationDesTitolare', $isSupplierOrganizationDesTitolare);
	    $this->set('ownOrganizationResults', $ownOrganizationResults);
	    $this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
	}

	public function admin_context_articles_index_quick($FilterArticleSupplierId=0) {
		
		$debug = false;
		
		/*
		 * ctrl configurazione Organization
		*/
		if(!$this->isUserPermissionArticlesOrder($this->user)) {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		switch($this->user->organization['Organization']['type']) {
			case 'PROD':
				$FilterArticleSupplierId = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			break;
			case 'PRODGAS':        
				$FilterArticleSupplierId = $this->user->organization['Supplier']['SuppliersOrganization']['id'];
			break;
			case 'GAS':
            case 'SOCIALMARKET':
			break;
			default:
				self::x(__('msg_error_org_type').' ['.$this->user->organization['Organization']['type'].']');
			break;
		}		
		
		$results = [];
		$FilterArticleName = null;
		$FilterArticleCodice = null;
		$FilterArticleStato = 'Y';
		$SqlLimit = 1000;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			if($this->user->organization['Organization']['type']=='PROD')
				$FilterArticleSupplierId = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			else if(isset($this->request->data['ArticlesOrder']['FilterArticleSupplierId']))
				$FilterArticleSupplierId = $this->request->data['ArticlesOrder']['FilterArticleSupplierId'];

			if(isset($this->request->data['ArticlesOrder']['FilterArticleCodice']))
				$FilterArticleCodice = $this->request->data['ArticlesOrder']['FilterArticleCodice'];
				
			if(isset($this->request->data['ArticlesOrder']['FilterArticleName']))
				$FilterArticleName = $this->request->data['ArticlesOrder']['FilterArticleName'];
				
			if(isset($this->request->data['ArticlesOrder']['article_id_selected']) && !empty($this->request->data['ArticlesOrder']['article_id_selected'])) {
				$array_article_id = explode(',',$this->request->data['ArticlesOrder']['article_id_selected']);
				$msg = '';
				foreach ($array_article_id as $id) {
					
					$options = [];
					$options['conditions'] = ['Article.organization_id' => $this->user->organization['Organization']['id'],
											  'Article.id' => $id];
					$options['recursive'] = -1;
					$articleResults = $this->Article->find('first', $options);	
					if (empty($articleResults)) 
						$msg .= 'Errore articolo id '.$id.'<br />';
					else {
						
						$article_organization_id = $articleResults['Article']['organization_id'];
						$article_id = $articleResults['Article']['id'];
						$name = $articleResults['Article']['name'];
						 
						/*
						 * ctrl gli eventuali acquisti gia' effettuati, se true non posso cancellarlo
						 */
						switch($this->user->organization['Organization']['type']) {
							case 'PROD':
							break;
							case 'PRODGAS':        
								App::import('Model', 'ProdGasArticle');
								$ProdGasArticle = new ProdGasArticle;
							
								$isArticleInCart = $ProdGasArticle->isArticleInCart($this->user, $article_organization_id, $article_id);
							break;
							case 'GAS':
                            case 'SOCIALMARKET':
								$isArticleInCart = $this->Article->isArticleInCart($this->user, $article_organization_id, $article_id);
							break;
							default:
								self::x(__('msg_error_org_type').' ['.$this->user->organization['Organization']['type'].']');
							break;
						}
		
						if($isArticleInCart) 
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
		}  // end if ($this->request->is('post') || $this->request->is('put'))
				
		$conditions = [];
		$conditions[] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id']];

		if(!$this->isSuperReferente()) {
			if(strpos($this->user->get('ACLsuppliersIdsOrganization'), ",")===false)
				$conditions[] = ['SuppliersOrganization.id' => $this->user->get('ACLsuppliersIdsOrganization')];
			else
				$conditions[] = ['SuppliersOrganization.id IN ' => explode(",", $this->user->get('ACLsuppliersIdsOrganization'))];
		}
		self::d($this->user->get('ACLsuppliersIdsOrganization'), $debug);
		
		/*
		 * solo per il context=article
		* per context=order Article.stato sempre a Y (se un articolo legato ad un ordine modifica lo stato a N viene cancellato dagli ordini)
		*/
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Stato')) {
			$FilterArticleStato = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Stato');
			if($FilterArticleStato!='ALL')
				$conditions[] = ['Article.stato' => $FilterArticleStato];
		}
		else {
			if(!empty($FilterArticleStato))  // cosi' di default e' Y
				$conditions[] = ['Article.stato' => $FilterArticleStato];
		}

		if(!empty($FilterArticleSupplierId)) {
			$conditions[] = ['SuppliersOrganization.id' => $FilterArticleSupplierId];
			if(!empty($this->request->params['pass']['FilterArticleCodice'])) {
				$FilterArticleCodice = $this->request->params['pass']['FilterArticleCodice'];
				$conditions[] = ['Article.codice LIKE '=>'%'.addslashes($FilterArticleCodice).'%'];
			}			
			if(!empty($this->request->params['pass']['FilterArticleName'])) {
				$FilterArticleName = $this->request->params['pass']['FilterArticleName'];
				$conditions[] = ['Article.name LIKE '=>'%'.addslashes($FilterArticleName).'%'];
			}
			
			$this->Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
			$this->Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
			$this->Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
			
			self::d($conditions, $debug);

			$this->paginate = ['conditions' => $conditions,
								'order' => ['SuppliersOrganization.name' => 'asc', 'Article.name' => 'asc'],
								'recursive' => 0,
								'maxLimit' => $SqlLimit,
								'limit' => $SqlLimit];						
			$results = $this->paginate('Article');
		
			self::d($results, $debug);
		}
		
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
				
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									   'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = ['SuppliersOrganization.name'];
			$suppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$suppliersOrganizationResults);
		}
		else
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
		
		$stato = ['Y' => __('StatoY'), 'N' => __('StatoN'), 'ALL' => __('ALL')];
		$this->set(compact('stato'));

		/* filtro */
		$this->set('FilterArticleSupplierId', $FilterArticleSupplierId);
		$this->set('FilterArticleCodice', $FilterArticleCodice);
		$this->set('FilterArticleName', $FilterArticleName);
		$this->set('FilterArticleStato', $FilterArticleStato);
	
		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
	}
	
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
	
		switch($this->user->organization['Organization']['type']) {
			case 'PROD':
				$FilterArticleSupplierId = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			break;
			case 'PRODGAS':        
				$FilterArticleSupplierId = $this->user->organization['Supplier']['SuppliersOrganization']['id'];
			break;
			case 'GAS':
            case 'SOCIALMARKET':
			break;
			default:
				self::x(__('msg_error_org_type').' ['.$this->user->organization['Organization']['type'].']');
			break;
		}		
	
		$results = [];
		$FilterArticleCodice = null;
		$FilterArticleName = null;
		$SqlLimit = 1000;
	
		if ($this->request->is('post') || $this->request->is('put')) {
				
			self::d($this->request->data, $debug);
			
			if($this->user->organization['Organization']['type']=='PROD')
				$FilterArticleSupplierId = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			else if(isset($this->request->data['Article']['FilterArticleSupplierId']))
				$FilterArticleSupplierId = $this->request->data['Article']['FilterArticleSupplierId'];
				
			if(isset($this->request->data['Article']['FilterArticleCodice']))
				$FilterArticleCodice = $this->request->data['Article']['FilterArticleCodice'];
					
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
							self::d($sql, $debug);
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
	
		$conditions = [];
		$conditions[] = ['SuppliersOrganization.organization_id'=>(int)$this->user->organization['Organization']['id']];
	
		if(!$this->isSuperReferente()) {
			if(strpos($this->user->get('ACLsuppliersIdsOrganization'), ",")===false)
				$conditions[] = ['SuppliersOrganization.id' => $this->user->get('ACLsuppliersIdsOrganization')];
			else
				$conditions[] = ['SuppliersOrganization.id IN ' => explode(",", $this->user->get('ACLsuppliersIdsOrganization'))];
		}
		
		if(!empty($FilterArticleSupplierId)) {
	
			$conditions[] = ['SuppliersOrganization.id' => $FilterArticleSupplierId];
			if(!empty($this->request->params['pass']['FilterArticleCodice'])) {
				$FilterArticleCodice = $this->request->params['pass']['FilterArticleCodice'];
				$conditions[] = ['Article.codice LIKE '=>'%'.addslashes($FilterArticleCodice).'%'];
			}			
			if(!empty($this->request->params['pass']['FilterArticleName'])) {
				$FilterArticleName = $this->request->params['pass']['FilterArticleName'];
				$conditions[] = ['Article.name LIKE '=>'%'.addslashes($FilterArticleName).'%'];
			}
				
			$this->Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
			$this->Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
			$this->Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
				
			$this->paginate = ['conditions' => $conditions,
								'order' => ['SuppliersOrganization.name' => 'asc', 'CategoriesArticle.name' => 'asc', 'Article.name' => 'asc'],
								'recursive' => 0,
								'maxLimit' => $SqlLimit,
								'limit' => $SqlLimit];
			$results = $this->paginate('Article');
		}
	
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
				
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									   'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = ['SuppliersOrganization.name'];
			$suppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$suppliersOrganizationResults);
		}
		else
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());

		/*
		 * get elenco categorie
		*/
		$conditionsCategoriesArticle = ['organization_id' => $this->user->organization['Organization']['id']];
		$categories = $this->Article->CategoriesArticle->generateTreeList($conditionsCategoriesArticle, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('categories'));
		
		/* filtro */
		$this->set('FilterArticleSupplierId', $FilterArticleSupplierId);
		$this->set('FilterArticleCodice', $FilterArticleCodice);
		$this->set('FilterArticleName', $FilterArticleName);
	
		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
	}
	
	function admin_index_edit_prices_default() {
		
		$debug = false;
									
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->_index_edit_prices_post($this->request, $debug);
		} // end if ($this->request->is('post') || $this->request->is('put')) 
		
		$this->_index_edit_prices();
	}

	function admin_index_edit_prices_percentuale() {

        $debug=false;

        if ($this->request->is('post') || $this->request->is('put')) {
            $this->_index_edit_prices_post($this->request, $debug);
        } // end if ($this->request->is('post') || $this->request->is('put'))

		$this->_index_edit_prices();
		
		$stato = ClassRegistry::init('Article')->enumOptions('stato');
		$stato['ALL'] = 'Tutti';
		$this->set(compact('stato'));
	}

    private function _index_edit_prices_post($request, $debug=false) {

        if($debug) debug($request->data);

        App::import('Model', 'SiteLifeCyle');
        $SiteLifeCyle = new SiteLifeCyle;

        $msg = '';
        if(isset($request->data['Article']['prezzo'])) {
            foreach ($request->data['Article']['prezzo'] as $key => $value) {
                $article_id = $key;

                if($debug) debug('tratto article '.$article_id.' con valore '.$value);

                if(!empty($value) && $value!=='0,00' && $value!=='0.00' && $value != $request->data['Article']['prezzo_old'][$article_id]) {

                    if (!$this->Article->exists($this->user->organization['Organization']['id'], $article_id)) {
                        $msg .= "<br />articolo ".$article_id." non esiste!";
                    }

                    $options = [];
                    $options['conditions'] = ['Article.organization_id' => $this->user->organization['Organization']['id'], 'Article.id' => $article_id];
                    $options['recursive'] = -1;
                    $row = $this->Article->find('first', $options);

                    $row['Article']['prezzo'] = $value;

                    if($debug) debug($row);

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
                            $request->data['Article']['updateArticlesOrder']='Y';

                        /*
                         * aggiorno gli articoli associati agli ordini
                         */
                        if($request->data['Article']['updateArticlesOrder']=='Y') {

                            $options = [];
                            $options['isUserPermissionArticlesOrder'] = $this->isUserPermissionArticlesOrder($this->user);
                            $esito .= $SiteLifeCyle->changeArticle($this->user, $row, 'EDIT_PRICE', $options);
                            if(isset($esito['CODE']) && $esito['CODE']==200)
                                $msg .= $esito['MSG'];
                        } // end if($request->data['Article']['updateArticlesOrder']=='Y')
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
    }

	private function _index_edit_prices() {
		
		$SqlLimit = 2000;
		$FilterArticleSupplierId = null;
		if(isset($this->request->data['Article']['FilterArticleSupplierId'])) // recupero il produttore filtrato
			$FilterArticleSupplierId = $this->request->data['Article']['FilterArticleSupplierId'];
			
		$conditions = [];
		$conditions[] = ['SuppliersOrganization.organization_id' => (int)$this->user->organization['Organization']['id']];
	
		if(!$this->isSuperReferente()) {
			if(strpos($this->user->get('ACLsuppliersIdsOrganization'), ",")===false)
				$conditions[] = ['SuppliersOrganization.id' => $this->user->get('ACLsuppliersIdsOrganization')];
			else
				$conditions[] = ['SuppliersOrganization.id IN ' => explode(",", $this->user->get('ACLsuppliersIdsOrganization'))];
		}
			
		/* recupero dati */
		if (!empty($this->request->params['pass']['FilterArticleSupplierId'])) {
			$FilterArticleSupplierId = $this->request->params['pass']['FilterArticleSupplierId'];
			// $conditions[] = ['Article.supplier_organization_id' => $FilterArticleSupplierId];
			$conditions[] = ['SuppliersOrganization.id' => $FilterArticleSupplierId];
		}
					
		/* filtro */
		if(empty($FilterArticleSupplierId)) {
			$ACLsuppliersIdsOrganization = $this->user->get('ACLsuppliersIdsOrganization');
			if(!empty($ACLsuppliersOrganization) && count($ACLsuppliersOrganization)<1)
				$FilterArticleSupplierId = $this->user->get('ACLsuppliersIdsOrganization');
		}
		$this->set('FilterArticleSupplierId', $FilterArticleSupplierId);
		
		$results = [];
		if (!empty($this->request->params['pass']['FilterArticleSupplierId'])) {
									
			$this->Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
			$this->Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
			$this->Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
				
			$this->paginate = ['conditions' => $conditions,
								'order' => ['SuppliersOrganization.name' => 'asc', 'Article.name' => 'asc'],
								'recursive' => 0,
								'maxLimit' => $SqlLimit,
								'limit' => $SqlLimit];
			$results = $this->paginate('Article');				
		}
		$this->set('results', $results);

		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
				
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									   'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = ['SuppliersOrganization.name'];
			$suppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$suppliersOrganizationResults);
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
		$results = $this->_set_context_order($this->order_id);
		$supplier_organization_id = $results['Order']['supplier_organization_id'];
		$this->_admin_add('order', $supplier_organization_id);
	}
	
	public function admin_context_articles_add() {
		$this->_admin_add('articles');
	}
	
	/*
	 * se aggiungo un articolo richiamo syncronizeArticlesOrder
	 *	ctrl se il produttore e' associato ad ordine
	 * 		se si => lo aggiungo in automatico
	 *  	se e' ordine DES no
	 */ 
	private function _admin_add($context, $supplier_organization_id=0) {

		self::d($this->request->data);
		
		/*
		 * iva
		 */
		if(isset($this->request->data['Article']['iva']) && !empty($this->request->data['Article']['iva'])) {
			setcookie('iva', $this->request->data['Article']['iva'], time() + 86400 * 365 * 1, Configure::read('App.server'));  // (86400 secs per day for 1 years)
			$this->Session->write('iva', $this->request->data['Article']['iva']);
		}
			
		$iva = $this->Session->read('iva');	
		if(empty($iva))
			$iva = '22';

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
			
			$this->request->data['Article']['id'] = $this->Article->getMaxIdOrganizationId($this->user->organization['Organization']['id']);

			/*
			 * richiamo la validazione 
			 */
			$msg_errors = $this->Article->getMessageErrorsToValidate($this->Article, $this->request->data);
			if(!empty($msg_errors)) {
				$this->Session->setFlash(__('The article could not be saved. Please, try again.').'<br />'.$msg_errors);
			}
			else {
					self::d($this->request->data, $debug);
						 
					$this->Article->create();
					if($this->Article->save($this->request->data)) {
					
						$id = $this->request->data['Article']['id'];
						$msg = __('The article has been saved');
							
						App::import('Model', 'SiteLifeCyle');
						$SiteLifeCyle = new SiteLifeCyle;				
						
						$options = [];
						$options['isUserPermissionArticlesOrder'] = $this->isUserPermissionArticlesOrder($this->user);
						$esito .= $SiteLifeCyle->changeArticle($this->user, $this->request->data, 'ADD_AFTER_SAVE', $options);
						if(isset($esito['CODE']) && $esito['CODE']==200)
							$msg .= $esito['MSG'];
										
						/*
					 	* IMG1 upload
						*/
						if(!empty($this->request->data['Document']['img1']['name'])) {
							$esito_upload = $this->_upload_img($id, $this->user->organization['Organization']['id']);
							if($esito_upload===true)
								$msg .= "<br />e l'immagine caricata";
							else
								$msg .= "<br />ma l'immagine non è caricata per un errore:<br/>$esito_upload";
						}
					
						$this->Session->setFlash($msg);
						
						$this->myRedirect(array('action' => $this->request->data['Article']['action_post'],'supplier_organization_id' => $this->request->data['Article']['supplier_organization_id']));  // context_$context_index, add
					} else 
						$this->Session->setFlash(__('The article could not be saved. Please, try again.'));
			}  // end richiamo la validazione 
		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		/*
		 * recupero il produttore se ho "salvato + continua ad inserire"
		 */
		if($this->user->organization['Organization']['type']=='GAS' || $this->user->organization['Organization']['type']=='SOCIALMARKET') {
			if(isset($this->request->pass['supplier_organization_id'])) 
				$supplier_organization_id = $this->request->pass['supplier_organization_id'];
			$this->set('supplier_organization_id',$supplier_organization_id);
		}

		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y')  {
			$conditions = [];
			$conditions = ['organization_id' => $this->user->organization['Organization']['id']];
			$categories = $this->Article->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
			$this->set(compact('categories'));
		}
		
		$um = ClassRegistry::init('Article')->enumOptions('um');
		$this->set(compact('um'));
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
				
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									   'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = ['SuppliersOrganization.name'];
			$suppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$suppliersOrganizationResults);
		}
		else
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
		
		$this->set('ivas', Configure::read('ivas'));
		$this->set('iva', $iva);
	}

	public function admin_context_order_edit($id=0) {
		$this->_set_context_order($this->order_id);
		$this->_admin_edit('order', $id,  $this->user->organization['Organization']['id']); // article_organization_id e' quello del ORG perche' solo lui puo' modificare  
	}
	
	public function admin_context_articles_edit($id=0) {
		$this->_admin_edit('articles', $id, $this->user->organization['Organization']['id']); // article_organization_id e' quello del ORG perche' solo lui puo' modificare
	}
	
	/*
	 * $sort					 passati dalla ricerca da admin_index  sort:value
	 * $direction				 passati dalla ricerca da admin_index  direction:asc
	 * $page					 passati dalla ricerca da admin_index  page:2
	 */
	private function _admin_edit($context, $id, $article_organization_id) {
		
		$debug = false;
		
		if($debug) debug('article_id '.$id.' article_organization_id '.$article_organization_id);
		
		if (!$this->Article->exists($article_organization_id, $id)) {
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
			
		$conditions = ['Article.id' => $id, 'Article.organization_id' => $article_organization_id];
		$resultsAssociateArticlesOrder = $ArticlesOrder->getArticlesOrdersInOrder($this->user ,$conditions);
		$this->set('resultsAssociateArticlesOrder', $resultsAssociateArticlesOrder);

		/*
 		 * ctrl gli eventuali acquisti gia' effettuati, se true non posso cancellarlo
 		 * idem in delete()
		 */
		if($debug) debug('Organization.type '.$this->user->organization['Organization']['type']);
		switch($this->user->organization['Organization']['type']) {
			case 'PROD':
			break;
			case 'PRODGAS':        
		        App::import('Model', 'ProdGasArticle');
		        $ProdGasArticle = new ProdGasArticle;
	        
				$isArticleInCart = $ProdGasArticle->isArticleInCart($this->user, $article_organization_id, $id);
			break;
			case 'GAS':
            case 'SOCIALMARKET':
				$isArticleInCart = $this->Article->isArticleInCart($this->user, $article_organization_id, $id);
			break;
			default:
				self::x(__('msg_error_org_type').' ['.$this->user->organization['Organization']['type'].']');
			break;
		}
		$this->set('isArticleInCart', $isArticleInCart);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			$msg = "";
			
			/*
			 * Article.state prima del salvataggio
			 */
			$options = [];
			$options['conditions'] = ['Article.organization_id' => $article_organization_id,
									  'Article.id' => $id];
			$options['recursive'] = -1;
			$options['fields'] = ['stato', 'img1'];
			$resultsOld = $this->Article->find('first', $options);
						
			
			$this->request->data['Article']['organization_id'] = $this->user->organization['Organization']['id'];
			
			if($this->user->organization['Organization']['type']=='PROD')
				$this->request->data['Article']['supplier_organization_id'] = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			
			if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='N')
				$this->request->data['Article']['category_article_id'] = 0;
			else
			if(empty($this->request->data['Article']['category_article_id'])) $this->request->data['Article']['category_article_id'] = 0;
			
			if($this->user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
				$this->request->data['Article']['alert_to_qta'] = 0;
								
			/*
			 * richiamo la validazione
			*/
			$msg_errors = $this->Article->getMessageErrorsToValidate($this->Article, $this->request->data);
			if(!empty($msg_errors)) {
				$this->Session->setFlash(__('The article could not be saved. Please, try again.').'<br />'.$msg_errors);
			}
			else {			
						 
				$this->Article->create();
				if($this->Article->save($this->request->data)) {
				
					$msg = __('The article has been saved');
				
					App::import('Model', 'SiteLifeCyle');
					$SiteLifeCyle = new SiteLifeCyle;				
					
					$options = [];
					$options['isUserPermissionArticlesOrder'] = $this->isUserPermissionArticlesOrder($this->user);
					$esito .= $SiteLifeCyle->changeArticle($this->user, $this->request->data, 'EDIT_AFTER_SAVE', $options);
					if(isset($esito['CODE']) && $esito['CODE']==200)
						$msg .= $esito['MSG'];
					
					/*
					 * IMG1 delete
					 */
					if($this->request->data['Article']['file1_delete'] == 'Y') {
						$esito_delete = $this->_delete_img($id, $article_organization_id, $resultsOld['Article']['img1'], false);
						if($esito_delete)
							$msg .= "<br />e l'immagine cancellata";
						else
							$msg .= '<br />'.$esito_delete;						
					}
					
						
					/*
					 * IMG1 upload
					*/
					if(!empty($this->request->data['Document']['img1']['name'])) {
						$esito_upload = $this->_upload_img($id, $article_organization_id);
						if($esito_upload===true)
							$msg .= "<br />e l'immagine caricata";
						else
							$msg .= "<br />ma l'immagine non è caricata per un errore:<br />$esito_upload";
					}
										
					$this->Session->setFlash($msg);
						
					$filterParams = '';
					$filterParams .= '&sort:'.$this->request->data['Article']['sort'];
					$filterParams .= '&direction:'.$this->request->data['Article']['direction'];
					$filterParams .= '&page:'.$this->request->data['Article']['page'];
					$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_'.$context.'_index'.$filterParams.'#anchor_'.$article_organization_id.'_'.$id);  
				} else 
					$this->Session->setFlash(__('The article could not be saved. Please, try again.'));
				
			} // end richiamo la validazione	
		} // end if ($this->request->is('post') || $this->request->is('put'))

	   /*
	    * ottendo i dati anagrafici degli Articoli 
	    * 	Article, SupplierOrganization, CategoriesArticle, ArticlesType
	    */
		$options = [];
		$options['conditions'] = ['Article.id' => $id, 'Article.organization_id' => $article_organization_id];
		$this->request->data = $this->Article->getArticlesDataAnagr($this->user, $options);

		if(!empty($this->request->data['Article']['img1']) && 
		   file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$article_organization_id.DS.$this->request->data['Article']['img1'])) {
			
			$file1 = new File(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$article_organization_id.DS.$this->request->data['Article']['img1']);
			$this->set(compact('file1'));
		}
		
		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y')  {
			$conditions = [];
			$conditions = ['organization_id' => $this->user->organization['Organization']['id']];
			$categories = $this->Article->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
			$this->set(compact('categories'));
		}
				
		$um = ClassRegistry::init('Article')->enumOptions('um');
		$this->set(compact('um'));
		
		if($isArticleInCart)	
			$stato = ['Y' => 'Si'];
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
				
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									   'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = ['SuppliersOrganization.name'];
			$suppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$suppliersOrganizationResults);
		}
		else
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
		
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
		
		/*
		 * stati dell'ordine in cui non cambia l'importo agli ordini associati
		 */ 		
		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;		
		$stateCodeNotUpdateArticle = $OrderLifeCycle->getStateCodeNotUpdateArticleToSql($this->user);
		$this->set(compact('stateCodeNotUpdateArticle'));
	}

	/*
	 * articolo in sola lettura, puo' appartenere al altre organizzazioni
	 */
	public function admin_context_articles_view($id = null, $article_organization_id=0) {
	
		if (!$this->Article->exists($article_organization_id, $id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

	   /*
	    * ottendo i dati anagrafici degli Articoli 
	    * 	Article, SupplierOrganization, CategoriesArticle, ArticlesType
	    */
		$options = [];
		$options['conditions'] = ['Article.id' => $id, 'Article.organization_id' => $article_organization_id];
		$this->request->data = $this->Article->getArticlesDataAnagr($user, $options);
		
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
		
		/*
		 * dati owner_articles listino REFERENT / DES / SUPPLIER 
		 */
		if($this->request->data['Article']['organization_id']!=$this->user->organization['Organization']['id']) {
	        App::import('Model', 'Organization');
	        $Organization = new Organization;
	        
			$options = [];
			$options['conditions'] = ['Organization.id' => $this->request->data['Article']['organization_id']];
			$options['recursive'] = -1;
			$organizationResults = $Organization->find('first', $options);	
			$this->set('organizationResults', $organizationResults);
		}
		 		
	}
	
	public function admin_context_order_copy($context, $id) {
		$results = $this->_set_context_order($this->order_id);
		$supplier_organization_id = $results['Order']['supplier_organization_id'];
		$this->_admin_copy('order', $id, $this->user->organization['Organization']['id']); // article_organization_id e' quello del ORG perche' solo lui puo' modificare
		
		$this->Session->setFlash(__('The delivery could not be copied. Please, try again.'));
		$this->myRedirect(['action' => 'context_order_index']);
	}
	
	public function admin_context_articles_copy($id) {
		$this->_admin_copy('articles', $id, $this->user->organization['Organization']['id']); // article_organization_id e' quello del ORG perche' solo lui puo' modificare
        $this->myRedirect(['action' => 'context_articles_index']);
	}
	
	private function _admin_copy($context, $id=0, $article_organization_id=0) {
	
		$debug = false;
		$url = "";
		
		self::d([$article_organization_id, $id], $debug);
		
		if (!$this->Article->exists($article_organization_id, $id)) {
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
		
		$results = $this->Article->copy_prepare($this->user, $id, $article_organization_id, $debug);
		$results = $this->Article->copy_img($this->user, $article_organization_id, $results, $debug);
		$results = $this->Article->copy_article_type($this->user, $results, $debug);

		self::d($results, $debug);
	
		$this->Article->create();
		if ($this->Article->save($results['Article'], array('validate' => false))) {
				
			$this->Session->setFlash(__('The article has been copied'));

			if($context=='order')
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_order_edit&id='.$results['Article']['id'].'&article_organization_id='.$results['Article']['organization_id'];
			else
			if($context=='articles')
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_articles_edit&id='.$results['Article']['id'].'&article_organization_id='.$results['Article']['organization_id'];			
		}
		else {
			$this->Session->setFlash(__('The article could not be copied. Please, try again.'));

			if($context=='order')
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_order_index#anchor_'.$results['Article']['organization_id'].'_'.$results['Article']['id'];
			else
			if($context=='articles')
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_articles_index#anchor_'.$results['Article']['organization_id'].'_'.$results['Article']['id'];
				
		}

		self::d('url '.$url, $debug);
		if($debug) exit;

		$this->myRedirect($url);
	}
	
	public function admin_context_order_delete($id=0) {
		$this->_set_context_order($this->order_id);
		$this->_admin_delete('order', $id, $this->user->organization['Organization']['id']); // article_organization_id e' quello del ORG perche' solo lui puo' modificare
	}
	
	public function admin_context_articles_delete($id=0) {
		$this->_admin_delete('articles', $id, $this->user->organization['Organization']['id']); // article_organization_id e' quello del ORG perche' solo lui puo' modificare
	}
	
   /*
    * articles_Trigger
	*
	* $sort					 passati dalla ricerca da admin_index  sort:value
	* $direction		     passati dalla ricerca da admin_index  direction:asc
	* $page					 passati dalla ricerca da admin_index  page:2
	*/
	private function _admin_delete($context, $id, $article_organization_id) {
	
		$debug = false;
		
		if($debug) debug('article_id '.$id.' article_organization_id '.$article_organization_id);
		
		if (!$this->Article->exists($article_organization_id, $id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		/*
 		 * ctrl gli eventuali acquisti gia' effettuati, se true non posso cancellarlo
 		 * idem in edit() per Article.stato
		 */
		if($debug) debug('Organization.type '.$this->user->organization['Organization']['type']);
		switch($this->user->organization['Organization']['type']) {
			case 'PROD':
			break;
			case 'PRODGAS':        
		        App::import('Model', 'ProdGasArticle');
		        $ProdGasArticle = new ProdGasArticle;
	        
				$isArticleInCart = $ProdGasArticle->isArticleInCart($this->user, $article_organization_id, $id);
			break;
			case 'GAS':
				$isArticleInCart = $this->Article->isArticleInCart($this->user, $article_organization_id, $id);
			break;
			default:
				self::x(__('msg_error_org_type').' ['.$this->user->organization['Organization']['type'].']');
			break;
		}
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
				$this->Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
				$this->Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
				$this->Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);
			
				$options = [];
				$options['conditions'] = ['Article.organization_id' => $article_organization_id, 'Article.id' => $id];
				$options['recursive'] = 0;
				$resultsOld = $this->Article->find('first', $options);

				/*
				 * gestione della sincronizzazione dell'articolo associato all'ordine
				 * prima di delete se no non ho + l'articolo
				*/
				App::import('Model', 'SiteLifeCyle');
				$SiteLifeCyle = new SiteLifeCyle;	
 
				$options = [];
				$options['isUserPermissionArticlesOrder'] = $this->isUserPermissionArticlesOrder($this->user);
				$esito .= $SiteLifeCyle->changeArticle($this->user, $resultsOld, 'DELETE', $options);
				if(isset($esito['CODE']) && $esito['CODE']==200)
					$msg .= $esito['MSG'];
				
				if ($this->Article->delete($article_organization_id, $id)) {
					
					$msg = __('Delete Article');
					
					/*
					 * IMG1 delete
					*/
					if(!empty($resultsOld['Article']['img1'])) {
						$esito_delete = $this->_delete_img($id, $article_organization_id, $resultsOld['Article']['img1'], false);
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
				
			if(!$debug) $this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_'.$context.'_index'.$filterParams.'#anchor_'.$article_organization_id.'_'.$id);			
		}
		
		/*
		 * estraggo articolo
		*/
		$options = [];
		$options['conditions'] = ['Article.id' => $id, 'Article.organization_id' => $article_organization_id];
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
		switch($this->user->organization['Organization']['type']) {
			case 'PROD':
			break;
			case 'PRODGAS':        
		        $orderResults = $ProdGasArticle->getGasOrganizationInArticlesOrder($this->user, $article_organization_id, $id, $debug);
			break;
			case 'GAS':
				$this->Article->ArticlesOrder->unbindModel(array('belongsTo' => array('Article', 'Cart')));
				$options = [];
				$options['conditions'] = ['ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
										'ArticlesOrder.article_id' => $id,
										'ArticlesOrder.article_organization_id' => $article_organization_id];
				$options['recursive'] = 0;
				$orderResults = $this->Article->ArticlesOrder->find('all', $options);
		
				App::import('Model', 'Delivery');
				
				foreach ($orderResults as $numResult => $orderResult) {
					
					/*
					 * consegna
					 */
					$Delivery = new Delivery;
					$deliveryResults = $Delivery->read($result['Order']['delivery_id'], $this->user->organization['Organization']['id']);
					$orderResults[$numResult]['Delivery'] = $deliveryResults['Delivery'];
				}

			break;
			default:
				self::x(__('msg_error_org_type').' ['.$this->user->organization['Organization']['type'].']');
			break;
		}		
		self::d($orderResults); 
		$this->set(compact('orderResults'));
		
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
	
	private function _set_context_order($order_id) {
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$Order->id = $order_id;
		if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$results = $Order->read($order_id, $this->user->organization['Organization']['id']);
		
		return $results;
	}

    /*
     * $FilterArticleOrderBy valore per il select options
     * $orders valore per la query
    */
	private function _admin_index_sql_order($organization_id) {

		$orders = [];
		
		/*
		 * ctrl se c'e' il filtro per produttore		$FilterArticleSupplierId = '';
		if(!$this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'SupplierId')) {
			$orders = ['SuppliersOrganization.name ASC'];
		}
     	*/
        // $this->Session->delete(Configure::read('Filter.prefix').$this->modelClass.'OrderBy');

		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'OrderBy')) {
            list($field, $sort) = explode(' ',$this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'OrderBy') );
            $orders[$field] = $sort;
			$FilterArticleOrderBy = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'OrderBy');
		}
		else {
			$FilterArticleOrderBy = 'Article.name ASC';
            $orders['Article.name'] = 'ASC';
		}

		$this->set('FilterArticleOrderBy', $FilterArticleOrderBy);
		
		// debug('FilterArticleOrderBy '.$FilterArticleOrderBy);
		// debug($orders);

		return $orders;
	}

	/*
	 * crea sql per l'elenco articoli
	 * passo $organization_id perche' se non trovo risultati gli passo l'eventuale organization_id del Gas DES titolate
	*/
	private function _admin_index_sql_conditions($organization_id, $context, $FilterArticleSupplierId) {
	
		$FilterArticleCategoryArticleId = null;
		$FilterArticleCodice = null;
		$FilterArticleName = null;
		$FilterArticleOrderById = 0;
		$FilterArticleArticleIds = null;
		$FilterArticleStato = 'Y';
		$FilterArticleFlagPresenteArticlesorders = 'ALL';
		$FilterArticleUm = null;
	
		$conditions = [];
	
		/* recupero dati dalla Session gestita in appController::beforeFilter */
		
		/*
		 * fields Article
		 * se organization_id != $this->user->organization['Organization']['id'] prendo article Gas DES titolare
		 *		escludo quelli filtrati dal form
		 */
		 if($organization_id == $this->user->organization['Organization']['id']) {
			if($context=='articles') {
				if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'SupplierId')) {
					$FilterArticleSupplierId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'SupplierId');
					$conditions[] = ['SuppliersOrganization.id' => $FilterArticleSupplierId];
				}
			}
			else
			if($context=='order')
				$conditions[] = ['SuppliersOrganization.id' => $FilterArticleSupplierId];
		}
		else {
			$conditions[] = ['SuppliersOrganization.id' => $FilterArticleSupplierId];
		}
						
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'CategoryArticleId')) {
			$FilterArticleCategoryArticleId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'CategoryArticleId');
			$conditions[] = ['Article.category_article_id' => $FilterArticleCategoryArticleId];
		}
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Codice')) {
			$FilterArticleCodice = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Codice');
			$conditions[] = ['Article.codice LIKE '=>'%'.$FilterArticleCodice.'%'];
		}
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Name')) {
			$FilterArticleName = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Name');
			$conditions[] = ['Article.name LIKE '=>'%'.$FilterArticleName.'%'];
		}
		
		/*
		 * solo per il context=article
		* per context=order Article.stato sempre a Y (se un articolo legato ad un ordine modifica lo stato a N viene cancellato dagli ordini)
		*/
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Stato')) {
			$FilterArticleStato = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Stato');
			if($FilterArticleStato!='ALL')
				$conditions[] = ['Article.stato' => $FilterArticleStato];
		}
		else {
			if(!empty($FilterArticleStato))  // cosi' di default e' Y
				$conditions[] = ['Article.stato' => $FilterArticleStato];
		}
		
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'FlagPresenteArticlesorders')) {
			$FilterArticleFlagPresenteArticlesorders = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'FlagPresenteArticlesorders');
			if($FilterArticleFlagPresenteArticlesorders!='ALL')
				$conditions[] = ['Article.flag_presente_articlesorders' => $FilterArticleFlagPresenteArticlesorders];
		}
		else {
			if(!empty($FilterArticleFlagPresenteArticlesorders) && $FilterArticleFlagPresenteArticlesorders!='ALL')  
				$conditions[] = ['Article.flag_presente_articlesorders' => $FilterArticleFlagPresenteArticlesorders];
		}
		
		
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Um')) {
			$FilterArticleUm = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Um');
			$conditions[] = ['Article.um' => $FilterArticleUm];
		}
	
		/*
		 * fields articleType  $hasAndBelongsToMany, $hasOne, 
		*/
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'ArticleTypeIds_hidden')) {
			$FilterArticleArticleIds = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'ArticleTypeIds_hidden');	
			$conditions[] = ['ArticlesArticlesType.article_type_id IN ('.$FilterArticleArticleIds.')'];
		}

		$this->Article->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = '.$organization_id;
		$this->Article->hasMany['ArticlesArticlesType']['conditions'] = ['ArticlesArticlesType.organization_id' => $organization_id];
		$this->Article->hasAndBelongsToMany['ArticlesType']['conditions'] = ['ArticlesArticlesType.organization_id' => $organization_id];
				
		/*
		 * fields articleOrder  $hasAndBelongsToMany, $hasOne, 
		*/
		if($context=='articles') {
			if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'OrderId')) {
				$FilterArticleOrderById = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'OrderId');
				$conditions[] = ['ArticlesOrder.order_id' => $FilterArticleOrderById];
				
			$this->Article->hasOne['ArticlesOrder']['conditions'] = 'ArticlesOrder.article_organization_id = Article.organization_id and Article.organization_id = '. $organization_id.' and ArticlesOrder.order_id = '.$FilterArticleOrderById;
			$this->Article->hasMany['ArticlesOrder']['conditions'] = ['ArticlesOrder.organization_id' => $organization_id,
																      'ArticlesOrder.order_id' => $FilterArticleOrderById];
			$this->Article->hasAndBelongsToMany['Order']['conditions'] = ['Order.organization_id' => 'ArticlesOrder.organization_id',
																		  'Order.organization_id' => $organization_id,
																		  'ArticlesOrder.order_id' => $FilterArticleOrderById];
			}	
			else {
				$this->Article->unbindModel(['hasOne' => ['ArticlesOrder']]);
				$this->Article->unbindModel(['hasMany' => ['ArticlesOrder']]);
				$this->Article->unbindModel(['hasAndBelongsToMany' => ['Order']]);			
			}
		}
		else
		if($context=='order') {
			$this->Article->hasOne['ArticlesOrder']['conditions'] = 'ArticlesOrder.article_organization_id = Article.organization_id and Article.organization_id = '. $organization_id.' and ArticlesOrder.order_id ='.$this->order_id;
			$this->Article->hasMany['ArticlesOrder']['conditions'] = ['ArticlesOrder.organization_id' => $organization_id,
																		  'ArticlesOrder.order_id' => $this->order_id];
			$this->Article->hasAndBelongsToMany['Order']['conditions'] = ['Order.organization_id' => 'ArticlesOrder.organization_id',
																		  'Order.organization_id' => $organization_id,
																		  'ArticlesOrder.order_id' => $this->order_id];
			$conditions[] = ['ArticlesOrder.order_id' => $this->order_id];
		}
		
		/*
		 * ctrl se non e' ancora stata effettuata una ricerca
		* */
		if(empty($conditions))
			$this->set('iniCallPage', true);
		else
			$this->set('iniCallPage', false);
	
		/*
		 * conditions obbligatorie
		*/
		$conditions[] = ['SuppliersOrganization.organization_id' => $organization_id];
		if(!$this->isSuperReferente()) {
			if(strpos($this->user->get('ACLsuppliersIdsOrganization'), ",")===false)
				$conditions[] = ['SuppliersOrganization.id' => $this->user->get('ACLsuppliersIdsOrganization')];
			else
				$conditions[] = ['SuppliersOrganization.id IN ' => explode(",", $this->user->get('ACLsuppliersIdsOrganization'))];
		}
		self::d($this->user->get('ACLsuppliersIdsOrganization'), false);
		self::d($conditions, false);
		
		$this->set('FilterArticleSupplierId', $FilterArticleSupplierId);
		$this->set('FilterArticleCategoryArticleId', $FilterArticleCategoryArticleId);
		$this->set('FilterArticleCodice', $FilterArticleCodice);
		$this->set('FilterArticleName', $FilterArticleName);
		$this->set('FilterArticleOrderById', $FilterArticleOrderById);
		$this->set('FilterArticleArticleIds', $FilterArticleArticleIds);
		$this->set('FilterArticleStato', $FilterArticleStato);
		$this->set('FilterArticleFlagPresenteArticlesorders', $FilterArticleFlagPresenteArticlesorders);
		$this->set('FilterArticleUm', $FilterArticleUm);
	
		return $conditions;
	}
	
	/*
	 * crea i filtri l'elenco articoli
	*/
	private function _admin_index_filter_object() {
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
				
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									   'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = ['SuppliersOrganization.name'];
			$suppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$suppliersOrganizationResults);
		}
		else
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
	
		/*
		 * get elenco ordini filtrati
		*/
		App::import('Model', 'Order');
		$Order = new Order;
		
		$conditionsOrder = ['Order.organization_id' => (int)$this->user->organization['Organization']['id'],
								 'Order.isVisibleBackOffice'=> 'Y'];
		if(!$this->isSuperReferente())
			$conditionsOrder += ['Order.supplier_organization_id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')'];
	
		$results = $Order->find('all', ['conditions' => $conditionsOrder,
										'order' => ['Delivery.data' => 'asc', 'Order.data_inizio' => 'asc'], 'recursive' => 1]);
		$orders = [];
		if(!empty($results)) 
			foreach ($results as $result) {
				if($result['Delivery']['sys']=='N')
					$label = $result['Delivery']['luogoData'];
				else 
					$label = $result['Delivery']['luogo'];
				
				if($result['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
					$data_fine = $result['Order']['data_fine_validation_'];
				else
					$data_fine = $result['Order']['data_fine_'];
				
				$orders[$result['Order']['id']] = $result['Delivery']['luogo'].' '.$result['SuppliersOrganization']['name'].' - dal '.$result['Order']['data_inizio_'].' al '.$data_fine;
			}	
		$this->set(compact('orders'));
	
		/*
		 * get elenco categorie
		*/
		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y')  {
			$conditionsCategoriesArticle = ['organization_id' => $this->user->organization['Organization']['id']];
			$categories = $this->Article->CategoriesArticle->generateTreeList($conditionsCategoriesArticle, null, null, '&nbsp;&nbsp;&nbsp;');
			$this->set(compact('categories'));
		}
			
		$um = ClassRegistry::init('Article')->enumOptions('um');
		$flag_presente_articlesorders = ['Y' => __('Y'), 'N' => __('No'), 'ALL' => __('ALL')];
		$this->set(compact('um', 'flag_presente_articlesorders'));
		
		
		/*
		 * solo per il context=article
		 * per context=order Article.stato sempre a Y (se un articolo legato ad un ordine modifica lo stato a N viene cancellato dagli ordini)
		 */
		$stato = ['Y' => __('StatoY'), 'N' => __('StatoN'), 'ALL' => __('ALL')];
		$this->set(compact('stato'));

		$orderbys = ['Article.codice ASC' => __('Code').' ('.__('OrderAsc').')',
					 'Article.codice DESC' => __('Code').' ('.__('OrderDesc').')',
					 'Article.name ASC' => __('Name').' ('.__('OrderAsc').')',
					 'Article.name DESC' => __('Name').' ('.__('OrderDesc').')',
					 'CategoriesSupplier.name ASC' => __('Category').' ('.__('OrderAsc').')',
					 'CategoriesSupplier.name DESC' => __('Category').' ('.__('OrderDesc').')',
					 'Article.prezzo ASC' => __('Prezzo').' ('.__('OrderNumAsc').')',
					 'Article.prezzo DESC' => __('Prezzo').' ('.__('OrderNumDesc').')',
					];
		$this->set(compact('orderbys'));
	}
	
	private function _delete_img($article_id, $article_organization_id, $img1, $debug=false) {

		$img_path = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$this->user->organization['Organization']['id'].DS;
		
		self::d("img1 $img1", $debug);
		self::d("img_path $img_path", $debug);
	
		$esito = true;
		
		$file1 = new File($img_path.$img1, false, 0777);
		self::d($file1, $debug);
		
		if(!$file1->delete()) 
			$esito = "<br />File $img1 non cancellato";
		
		
		/*
		 * update database
		*/
		$sql = "UPDATE
					".Configure::read('DB.prefix')."articles
				SET img1 = ''
				WHERE
					organization_id = ".$article_organization_id."
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
	
	private function _upload_img($article_id, $article_organization_id, $debug=false) {
	
		self::d($this->request->data['Document'], $debug);
		
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
		self::d($img1, $debug);
		
		if($img1['error'] == UPLOAD_ERR_OK && is_uploaded_file($img1['tmp_name']))	{
	
			$path_upload = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$article_organization_id.DS;

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
			$fileNewName = uniqid($article_id.'-').'.'.$ext;
			self::d("path_upload ".$path_upload, $debug);
			self::d("ext ".$ext, $debug);
			self::d("fileNewName ".$fileNewName, $debug);
			
			if(move_uploaded_file($img1['tmp_name'], $path_upload.$fileNewName)) {
				
				$info = getimagesize($path_upload.$fileNewName);
				$width = $info[0];
				$height = $info[1];
				
				self::d($info, $debug);
					
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
					
					self::d("ridimensiono ".$status, $debug);
				}
										
				/*
				 * update database
				 */
				$sql = "UPDATE
							".Configure::read('DB.prefix')."articles
						SET img1 = '".$fileNewName."' 
						WHERE
							organization_id = ".$article_organization_id."
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
	
	/* 
	 * passato un campo stato / flag_presente_articlesorders inverte il valore Y => N
	 */
    public function admin_inverseValue($article_organization_id, $article_id, $field, $format='notmpl') {

		$debug = false;
		
        if (empty($article_organization_id) && empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $options = [];
        $options['conditions'] = ['Article.organization_id' => $article_organization_id,
								  'Article.id' => $article_id];
        $options['recursive'] = -1;
        $articleResults = $this->Article->find('first', $options);
		if(empty($articleResults)) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		
		self::d($field, $debug);
		self::d($articleResults, $debug);

		if(isset($articleResults['Article'][$field])) {

			self::d($articleResults['Article'][$field], $debug);

			switch ($articleResults['Article'][$field]) {
				case 'Y':
					$articleResults['Article'][$field] = 'N';
				break;
				case 'N':
					$articleResults['Article'][$field] = 'Y';
				break;
				default:
					$articleResults['Article'][$field] = 'N';
				break;
			}

			self::d($articleResults['Article'][$field], $debug);
			self::d($articleResults, $debug);
			
			$this->Article->create();
			if (!$this->Article->save($articleResults)) {
			}
		
		}

        $this->set('content_for_layout', '');

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
   } 

   private function _getSorts($sort, $direction='asc', $debug=false) {

   		$sorts = [];

		if($debug) debug('_getSorts '.$sort.' '.$direction);

		if(empty($sort))	
			$sorts = ['SuppliersOrganization.name' => $direction, 'Article.name' => $direction];
		else {
			switch (strtolower($sort)) {
				case 'supplier_id':
					$sorts = ['SuppliersOrganization.name' => $direction, 'Article.name' => $direction];
					break;
				case 'category':
					$sorts = ['CategoriesSupplier.name' => $direction, 'Article.name' => $direction];
				break;
				case 'codice':
					$sorts = ['Article.codice' => $direction];
				break;
				case 'name':
					$sorts = ['Article.name' => $direction];
				break;
				case 'package': // conf
					$sorts = ['Article.qta' => $direction];
				break;
				case 'prezzounita': 
					$sorts = ['Article.prezzo' => $direction];
				break;
				case 'prezzo/um': 
					$sorts = ['Article.prezzo' => $direction];
				break;
				case 'bio': 
					$sorts = ['Article.bio' => $direction];
				break;
				case 'stato': 
					$sorts = ['Article.stato' => $direction];
				break;
				default:
					$sorts = ['SuppliersOrganization.name' => $direction, 'Article.name' => $direction];
					break;
			}
		}

		if($debug) debug($sorts);

		return $sorts;
   } 	
}