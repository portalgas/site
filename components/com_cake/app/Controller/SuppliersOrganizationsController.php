<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class SuppliersOrganizationsController extends AppController {

	public function beforeFilter() {
		 parent::beforeFilter();
		 
		 /* ctrl ACL */
		 if (in_array($this->action, array('admin_edit', 'admin_delete'))) {
		 	
		 	if($this->isSuperReferente()) {
		 			
		 	}
		 	else {			 	
		 		$supplier_organization_id = $this->request->pass['id'];
		 		
		 		/*
		 		 * ctrl se l'utente e' referente del produttore
		 		*/
		 		App::import('Model', 'SuppliersOrganizationsReferent');
		 		$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
		 		
		 		if(!$this->isReferentGeneric() && !$SuppliersOrganizationsReferent->aclReferenteSupplierOrganization($this->user, $supplier_organization_id)) {
					$this->Session->setFlash(__('msg_not_permission'));
					$this->myRedirect(Configure::read('routes_msg_stop'));	
		 		}
		 	}
		 }
		 /* ctrl ACL */
	}

	public function admin_index() {

		$FilterSuppliersOrganizationId=null;
		$FilterSuppliersOrganizationCategoryId=null;
		$FilterSuppliersOrganizationStato = 'ALL';
		$SqlLimit = 20;

		$conditions[] = array('SuppliersOrganization.organization_id' => (int)$this->user->organization['Organization']['id']); 

		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
				
			$options = [];
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
											'SuppliersOrganization.stato' => 'Y');
			$options['recursive'] = -1;
			$options['order'] = array('SuppliersOrganization.name');
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else {
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
			$conditions[] = ['SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')'];
		}
		
		/* recupero dati dalla Session gestita in appController::beforeFilter */
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Id')) {
			$FilterSuppliersOrganizationId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Id');
			if(!empty($FilterSuppliersOrganizationId)) $conditions[] = array('SuppliersOrganization.id' => $FilterSuppliersOrganizationId);
		}

		if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
			if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'CategoryId')) {
				$FilterSuppliersOrganizationCategoryId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'CategoryId');
				$conditions[] = array('SuppliersOrganization.category_supplier_id'=>$FilterSuppliersOrganizationCategoryId);
			}
		}
		
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Stato')) {
			$FilterSuppliersOrganizationStato = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Stato');
			if($FilterSuppliersOrganizationStato!='ALL')
				$conditions[] = array('SuppliersOrganization.stato' => $FilterSuppliersOrganizationStato);
		}
		else {
			if(!empty($FilterSuppliersOrganizationStato) && $FilterSuppliersOrganizationStato!='ALL')  // cosi' di default e' ALL
				$conditions[] = array('SuppliersOrganization.stato' => $FilterSuppliersOrganizationStato);
		}
		
		/* filtro */
		$this->set('FilterSuppliersOrganizationId', $FilterSuppliersOrganizationId);
		$this->set('FilterSuppliersOrganizationCategoryId', $FilterSuppliersOrganizationCategoryId);
		$this->set('FilterSuppliersOrganizationStato', $FilterSuppliersOrganizationStato);
		
		App::import('Model', 'CategoriesSupplier');
		$CategoriesSupplier = new CategoriesSupplier;

		$options = [];
		$options['order'] = array('CategoriesSupplier.name');
		$categories = $CategoriesSupplier->find('list', $options);
		$this->set(compact('categories'));	

		$stato = ['Y' => __('StatoY'), 'N' => __('StatoN'), 'ALL' => __('ALL')];
		$this->set(compact('stato'));
		
		
		/*
		echo "<pre>";
		print_r($conditions);
		echo "</pre>";
		*/
		
		$this->SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization')));
		$this->SuppliersOrganization->recursive = 0; 
        $this->paginate = array('conditions' => array($conditions),'order'=>'SuppliersOrganization.name','limit' => $SqlLimit);
		$results = $this->paginate('SuppliersOrganization');
		/*
		echo "<pre>";
		print_r($conditions);
		echo "</pre>";
		*/
		foreach ($results as $i  => $result) {

			/* 
			 * SuppliersOrganizationsReferent 
			 */
			$conditions = array('SuppliersOrganizationsReferent.supplier_organization_id'=>$result['SuppliersOrganization']['id'],
								'SuppliersOrganizationsReferent.organization_id' => (int)$this->user->organization['Organization']['id']);
			$suppliersOrganizationsReferents = $this->SuppliersOrganization->SuppliersOrganizationsReferent->find('all',array('conditions' => $conditions));
			if(!empty($suppliersOrganizationsReferents)) {
				foreach ($suppliersOrganizationsReferents as $ii  => $suppliersOrganizationsReferent) {
					$results[$i]['SuppliersOrganizationsReferent'][$ii]['User'] = $suppliersOrganizationsReferent['User'];
					$results[$i]['SuppliersOrganizationsReferent'][$ii]['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent['SuppliersOrganizationsReferent'];
				}
			}
			else 
				$results[$i]['SuppliersOrganizationsReferent'] = null;
			
			/*
			 * totale articoli
			*/				
			$results[$i]['Articles'] = $this->SuppliersOrganization->getTotArticlesAttivi($this->user, $result['SuppliersOrganization']['id']);
		}

		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
		$this->set('isSuperReferente',$this->isSuperReferente());
		$this->set('isRoot',$this->isRoot()); // per accedere alla modifica dell'articolo
		
		/*
		 * parametri da passare eventualmente a admin_edit
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

	/*
	 * elenco dei Suppliers disponibili, non ancora associati 
	 * $duplicateSuppliersId se arrivo da SuppliersOrganization::add e ho inserito un dato duplicato
	 * can_promotions redita quelli del GAS
	 * x root, se arrivo da ProdGasSuppliersImports::index $search e' il nome del produttore
	 */
	public function admin_add_index($search='') {
		
		$debug = false;

		$search = str_replace('Azienda ', '', $search);
		$search = str_replace('azienda', '', $search);
		$search = str_replace('Az. ', '', $search);
		$search = str_replace('Agricola ', '', $search);
		$search = str_replace('agricola ', '', $search);
		$search = str_replace('Agr. ', '', $search);
		$search = str_replace('Ag. ', '', $search);
		$this->set(compact('search'));
		self::d($search, $debug);
		
		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
			
		if ($this->request->is('post') || $this->request->is('put')) {
		
			self::d($this->request->data,$debug);

			$supplier_id = $this->request->data['supplier_id'];
			$Supplier->id = $supplier_id;
			if (!$Supplier->exists()) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
			
			$options = [];
			$options['conditions'] = ['Supplier.id' => $supplier_id];			
			$options['recursive'] = -1;
			$results = $Supplier->find('first', $options);
			
			self::d($results, $debug);

			$data = [];
			$data['SuppliersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
			$data['SuppliersOrganization']['supplier_id'] = $results['Supplier']['id'];
			$data['SuppliersOrganization']['name'] = $results['Supplier']['name'];
			$data['SuppliersOrganization']['category_supplier_id'] = $results['Supplier']['category_supplier_id'];
			
			/*
			 * eredita quelli del produttore
			 */ 
			$data['SuppliersOrganization']['can_promotions'] = $results['Supplier']['can_promotions'];
			
			/*
			 * il produttore importata a i valori di defautl
			 */ 
			$data['SuppliersOrganization']['frequenza'] = '';
			$data['SuppliersOrganization']['can_view_orders'] = Configure::read('SupplierDefaultCanViewOrders');
			$data['SuppliersOrganization']['can_view_orders_users'] = Configure::read('SupplierDefaultCanViewOrdersUsers');
			$data['SuppliersOrganization']['mail_order_open'] = Configure::read('SupplierDefaultMailOrderOpen');
			$data['SuppliersOrganization']['mail_order_close'] = Configure::read('SupplierDefaultMailOrderClose');
			$data['SuppliersOrganization']['stato'] = 'Y';
			
			if($results['Supplier']['owner_organization_id']==0) {
				$data['SuppliersOrganization']['owner_articles'] = 'REFERENT';
				/*
				 * dopo il salvataggio recupero SupplierOrganization.id e aggiorno
				 */
				$data['SuppliersOrganization']['owner_supplier_organization_id'] = 0;
				$data['SuppliersOrganization']['owner_organization_id'] = 0;
			}
			else {
				$data['SuppliersOrganization']['owner_articles'] = 'SUPPLIER';
				
				$data['SuppliersOrganization']['owner_supplier_organization_id'] = 0;
				$data['SuppliersOrganization']['owner_organization_id'] = $results['Supplier']['owner_organization_id'];
				
				/* 
				 * aggiorno SuppliersOrganization con chi gestisce il listino articoli (ora lui) owner_... 
				 */
				$options = [];
				$options['conditions'] = ['SuppliersOrganization.organization_id' => $results['Supplier']['owner_organization_id'],
										   'SuppliersOrganization.supplier_id' => $results['Supplier']['id']];
				$options['recursive'] = -1;
				$suppliersOrganizationResults = $this->SuppliersOrganization->find('first', $options);	
				if(empty($suppliersOrganizationResults)) {
					
					// self::x("SuppliersOrganizations::add_index produttore non completo!");
					 /*
					  * l'organization del PRODUTTORE non ha associato il produttore (SuppliersOrganization vuoto) => lo creo
					  */
					$dataProd = $data;
					$dataProd['SuppliersOrganization']['owner_articles'] = 'REFERENT';
					$dataProd['SuppliersOrganization']['organization_id'] = $results['Supplier']['owner_organization_id'];
					$dataProd['SuppliersOrganization']['supplier_id'] = $results['Supplier']['id'];
					
					$this->SuppliersOrganization->create();
					if ($this->SuppliersOrganization->save($dataProd)) {
						$supplier_organization_id = $this->SuppliersOrganization->id;
						
						self::d("Inserito SuppliersOrganization.id [$supplier_organization_id] associato all'organization del PRODUTTORE", $debug);
						
						$dataProd['SuppliersOrganization']['owner_supplier_organization_id'] = $supplier_organization_id;
						$data['SuppliersOrganization']['owner_supplier_organization_id'] = $supplier_organization_id;	
						
						if (!$this->SuppliersOrganization->save($dataProd)) {
							$continua = false;
						}
					}
					else 
						$continua = false;
				}
				else
					$data['SuppliersOrganization']['owner_supplier_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['id'];			
			}	

			$this->SuppliersOrganization->create();
			
			self::d('Dati produttore SLAVE', $debug);
			self::d($data, $debug); 

			if ($this->SuppliersOrganization->save($data)) {
				
				/*
				 * dont'work: restituisce supplier_id
				* $supplier_organization_id = $this->SuppliersOrganization->getLastInsertId();
				*/
				$supplier_organization_id = $this->SuppliersOrganization->id;
				
				self::d("Inserito SuppliersOrganization.id [$supplier_organization_id]", $debug);
				
				if($data['SuppliersOrganization']['owner_supplier_organization_id'] == 0 && $data['SuppliersOrganization']['owner_organization_id'] == 0) {
					/* 
					 * aggiorno SuppliersOrganization con chi gestisce il listino articoli (ora lui) owner_... 
					 */
					 $options = [];
					 $options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
					 						   'SuppliersOrganization.id' => $supplier_organization_id];
					 $options['recursive'] = -1;
					 $suppliersOrganizationResults = $this->SuppliersOrganization->find('first', $options);	
					
					$suppliersOrganizationResults['SuppliersOrganization']['owner_supplier_organization_id'] = $supplier_organization_id;
					$suppliersOrganizationResults['SuppliersOrganization']['owner_organization_id'] = $this->user->organization['Organization']['id'];
					self::d($suppliersOrganizationResults, $debug);
					
					if (!$this->SuppliersOrganization->save($suppliersOrganizationResults)) {
						$this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
						$continua = false;
					} 					
				} // end if($data['SuppliersOrganization']['owner_supplier_organization_id'] == 0 && $data['SuppliersOrganization']['owner_organization_id'] == 0) 
				
				self::d("supplier_articles ".$this->request->data['SuppliersOrganization']['supplier_articles']." se Y importo anche gli articoli del produttore", $debug);
								
				/*
				 * importo anche gli articoli del produttore
				 */
				$organization_id = $this->request->data['organization_id'];
	 
				if($this->request->data['SuppliersOrganization']['supplier_articles']=='Y' && !empty($organization_id)) {
					
					App::import('Model', 'SuppliersOrganization');
					$SuppliersOrganization = new SuppliersOrganization;
					
					$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization', 'Supplier', 'CategoriesSupplier')));
					$SuppliersOrganization->unbindModel(array('hasMany' => array('Order', 'SuppliersOrganizationsReferent')));
						
					$options = [];
					$options['conditions'] = ['SuppliersOrganization.organization_id' => $organization_id,
											  'SuppliersOrganization.supplier_id' => $supplier_id];
					$options['recursive'] = 1;
					$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
					
					App::import('Model', 'Article');
					
					if(isset($suppliersOrganizationResults['Article']))
					foreach ($suppliersOrganizationResults['Article'] as $numResult  => $article) {
					
						$debugArticleCopy = false;
					
						/*
						 * non gli passo organization_id dell'utente ma dell'organization da cui copio i prodotti 
						 * cosi' estraggo l'articolo master
						 */
						$user->organization['Organization']['id'] = $suppliersOrganizationResults['SuppliersOrganization']['organization_id'];
						$id = $article['id'];
						
						$Article = new Article;
						
						$articleCopy = [];
						$articleCopy = $Article->copy_prepare($user, $id, $organization_id, $debugArticleCopy);
						$articleCopy['Article']['id'] = $Article->getMaxIdOrganizationId($this->user->organization['Organization']['id']);
						$articleCopy['Article']['organization_id'] = $this->user->organization['Organization']['id'];
						$articleCopy['Article']['supplier_organization_id'] = $supplier_organization_id;
						
						$articleCopy = $Article->copy_img($this->user, $user->organization['Organization']['id'], $articleCopy, $debugArticleCopy);
						$articleCopy = $Article->copy_article_type($this->user, $articleCopy, $debugArticleCopy);
						
						self::d('articolo SLAVE (nuovo dalla copia del MASTER)', $debug);
						self::d($articleCopy, $debug);
						
						$Article->create();
						$Article->save($articleCopy['Article'], ['validate' => false]);	
					}	
					
					$this->Session->setFlash(__('The supplier organization and articles has been saved'));
				}
				else
					$this->Session->setFlash(__('The supplier organization has been saved'));
			
				/*
				 * se sono un referente, lo user diventa referente se no dopo non posso accedergli
				 */
				if(!$this->isSuperReferente()) {
					App::import('Model', 'SuppliersOrganizationsReferent');
					$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
					
					$data = [];
					$data['SuppliersOrganizationsReferent']['supplier_organization_id'] = $supplier_organization_id;
					$data['SuppliersOrganizationsReferent']['type'] = 'REFERENTE';
					$data['SuppliersOrganizationsReferent']['user_id'] = $this->user->get('id');
					$data['SuppliersOrganizationsReferent']['group_id'] = Configure::read('group_id_referent');
					$SuppliersOrganizationsReferent->insert($this->user, $data, $debug);
				}
				
				$this->reloadUserParams();
				
				if(!$debug) $this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=SuppliersOrganizations&action=edit&id='.$supplier_organization_id);
			} else {
				$this->Session->setFlash(__('The supplier organization could not be saved. Please, try again.'));
				$this->myRedirect(array('controller' => 'SuppliersOrganization', 'action' => 'add_index'));
			}
						
		} // end if ($this->request->is('post') || $this->request->is('put')) 
		
		$FilterSuppliersOrganizationName=null;
		$FilterSuppliersOrganizationCategoryId=null;
		$FilterSuppliersOrganizationProvincia=null;
		$FilterSuppliersOrganizationCap=null;

		/* 
		 * estraggo tutti i produttori non ancora associati 
		 * escludo i PG perche' sono pagine personali del GAS
		 */
		$sql = "SELECT 
					Supplier.*, CategorySupplier.name 
				FROM 
					".Configure::read('DB.prefix')."suppliers as Supplier 
					LEFT JOIN ".Configure::read('DB.prefix')."categories_suppliers as CategorySupplier ON CategorySupplier.id = Supplier.category_supplier_id 
				WHERE 
					(Supplier.stato = 'Y' or Supplier.stato = 'T')
					and Supplier.id NOT IN (
						select 
							s.id 
						FROM 
							".Configure::read('DB.prefix')."suppliers s, 
							".Configure::read('DB.prefix')."suppliers_organizations o 
						WHERE s.id = o.supplier_id
							AND o.organization_id = ".(int)$this->user->organization['Organization']['id'].") ";
		
		/*
		 * se arrivo da SuppliersOrganization::add e ho inserito un dato duplicato
		 */
		if(isset($this->request->params['pass']['duplicateSuppliersId'])) {
			$duplicateSuppliersId = $this->request->params['pass']['duplicateSuppliersId'];
			$sql .= " AND Supplier.id = ".$duplicateSuppliersId;
		}

		/*
		 * non li prendo dalla Session->check se no quando li inserisco me li tiene in memoria e la lista risulta vuota
		 * if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'CategoryId')) {
		*/
		
		// if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Name')) {
			$FilterSuppliersOrganizationName = $this->request->params['pass'][Configure::read('Filter.prefix').$this->modelClass.'Name'];
			if(!empty($FilterSuppliersOrganizationName)) $sql .= " AND Supplier.name LIKE '%".addslashes($FilterSuppliersOrganizationName)."%' ";
			
		// if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'CategoryId')) {
			$FilterSuppliersOrganizationCategoryId = $this->request->params['pass'][Configure::read('Filter.prefix').$this->modelClass.'CategoryId'];
			if(!empty($FilterSuppliersOrganizationCategoryId)) $sql .= " AND Supplier.category_supplier_id = $FilterSuppliersOrganizationCategoryId ";
		
			$FilterSuppliersOrganizationProvincia = $this->request->params['pass'][Configure::read('Filter.prefix').$this->modelClass.'Provincia'];
			if(!empty($FilterSuppliersOrganizationProvincia)) $sql .= " AND Supplier.provincia = '".$FilterSuppliersOrganizationProvincia."' ";
			
			$FilterSuppliersOrganizationCap = $this->request->params['pass'][Configure::read('Filter.prefix').$this->modelClass.'Cap'];
			if(!empty($FilterSuppliersOrganizationCap)) $sql .= " AND Supplier.cap = '".$FilterSuppliersOrganizationCap."' ";
			
		$sql .= " ORDER BY Supplier.name";
		self::d($sql, $debug);
		$results = $this->SuppliersOrganization->query($sql);
		$this->set('results', $results);
		
		/* filtro */
		$this->set('FilterSuppliersOrganizationName', $FilterSuppliersOrganizationName);
		$this->set('FilterSuppliersOrganizationCategoryId', $FilterSuppliersOrganizationCategoryId);
		$this->set('FilterSuppliersOrganizationProvincia', $FilterSuppliersOrganizationProvincia);
		$this->set('FilterSuppliersOrganizationCap', $FilterSuppliersOrganizationCap);
		
		App::import('Model', 'CategoriesSupplier');
		$CategoriesSupplier = new CategoriesSupplier;

		$options = [];
		$options['order'] = array('CategoriesSupplier.name');
		$categories = $CategoriesSupplier->find('list', $options);
		
		$filterProvinciaResults = $Supplier->getListProvincia($this->user, $debug);
		$filterCapResults = $Supplier->getListCap($this->user, $debug);
		
		$this->set(compact('categories', 'filterProvinciaResults', 'filterCapResults'));
		
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

	/*
	 * produttore inserito dal referente
	 */
	public function admin_add_new() {
		
		$debug = false;
	
		/*
		 * REFERENTE, COREFERENTE lato front-end viene evidenziata la differenza
		*/
		$types = ClassRegistry::init('SuppliersOrganizationsReferent')->enumOptions('type');	
		$this->set('types', $types);
		
		if ($this->request->is('post') || $this->request->is('put')) {

			$continua = true;
			self::d($this->request->data, $debug);
			
			$sort = $this->request->data['Supplier']['sort'];
			$direction = $this->request->data['Supplier']['direction'];
			$page = $this->request->data['Supplier']['page'];
			
			/*
			 * inserisco in Supplier con stato = T
			 */
			App::import('Model', 'Supplier');
			$Supplier = new Supplier;
			
			/*
			 * fields custom
			 */
			$data['Supplier'] = $this->request->data['Supplier'];
			if(!empty($this->request->data['Supplier']['www']))
				$data['Supplier']['www'] = $this->traslateWww($this->request->data['Supplier']['www']);
			$data['Supplier']['j_content_id'] = 0;
			$data['Supplier']['lat'] ='';
			$data['Supplier']['lng'] = '';

			/*
			 * il produttore creato, se il GAS lo crea e abilita can_promotions, il Supplier erederdita 
			 */			
			$data['Supplier']['can_promotions'] = $this->request->data['Supplier']['prod_gas_supplier_can_promotions'];
			$data['Supplier']['owner_organization_id'] = 0; // id del Organization.type = PRODGAS
			
			/*
			 * il produttore creato a i valori di defautl
			 */ 			
			$data['Supplier']['stato'] = 'T';
			
			/*
			 * richiamo la validazione 
			 */
			$msg_errors = $Supplier->getMessageErrorsToValidate($Supplier, $data);
			if(!empty($msg_errors)) {
				self::d($data, $debug);
				self::d($msg_errors, $debug);
				$this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
				$continua = false;
			}

			if($continua) {
				$Supplier->create();
				self::d($this->request->data, $debug);
				if (!$Supplier->save($data)) {
					$this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
				$continua = false;
			} 
			
			if($continua) {
				$this->Session->setFlash(__('The supplier has been saved'));
				
				$supplier_id = $Supplier->getLastInsertId();
				self::d("Inserito Supplier.id [$supplier_id]", $debug);	
					
				/*
				 * inserisco in SuppliersOrgnization
				*/			
				$data = [];
				$data['SuppliersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
				$data['SuppliersOrganization']['supplier_id'] = $supplier_id;
				$data['SuppliersOrganization']['name'] = $this->request->data['Supplier']['name'];
				if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
					$data['SuppliersOrganization']['category_supplier_id'] = $this->request->data['Supplier']['category_supplier_id'];
				else
					$data['SuppliersOrganization']['category_supplier_id'] = 0;
				$data['SuppliersOrganization']['frequenza'] = $this->request->data['Supplier']['frequenza'];
				$data['SuppliersOrganization']['can_promotions'] = Configure::read('SupplierDefaultCanPromotions');
				$data['SuppliersOrganization']['stato'] = 'Y';
				$data['SuppliersOrganization']['mail_order_open'] = $this->request->data['Supplier']['mail_order_open'];
				$data['SuppliersOrganization']['mail_order_close'] = $this->request->data['Supplier']['mail_order_close'];
				/*
				 * ora i radio e' disabled
				 * $data['SuppliersOrganization']['owner_articles'] = $this->request->data['Supplier']['prod_gas_supplier_owner_articles'];
				 */
				$data['SuppliersOrganization']['owner_articles'] = 'REFERENT'; 
				$data['SuppliersOrganization']['can_view_orders'] = $this->request->data['Supplier']['prod_gas_supplier_can_view_orders'];
				$data['SuppliersOrganization']['can_view_orders_users'] = $this->request->data['Supplier']['prod_gas_supplier_can_view_orders_users'];
				$data['SuppliersOrganization']['can_promotions'] = $this->request->data['Supplier']['prod_gas_supplier_can_promotions'];
				
				/*
				 * dopo il salvataggio recupero SupplierOrganization.id e aggiorno
				 */
				$data['SuppliersOrganization']['owner_supplier_organization_id'] = 0;
				$data['SuppliersOrganization']['owner_organization_id'] = 0;
				
				/*
				 * richiamo la validazione 
				 */
				$msg_errors = $this->SuppliersOrganization->getMessageErrorsToValidate($Supplier, $data);
				if(!empty($msg_errors)) {
					self::d($data, $debug);
					self::d($msg_errors, $debug);
					$this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
					$continua = false;
				}	
			}
				
			if($continua) {			
				$this->SuppliersOrganization->create();
				self::d($data, $debug);
					
				if (!$this->SuppliersOrganization->save($data)) {
					$this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
					$continua = false;
				} 
			}
			
			if($continua) {
					$this->Session->setFlash(__('The supplier has been saved'));
				
					$supplier_organization_id = $this->SuppliersOrganization->getLastInsertId();
					self::d("Inserito SuppliersOrganization.id [$supplier_organization_id]", $debug);
					
					/* 
					 * aggiorno SuppliersOrganization con chi gestisce il listino articoli (ora lui) owner_... 
					 */
					 $options = [];
					 $options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
					 						   'SuppliersOrganization.id' => $supplier_organization_id];
					 $options['recursive'] = -1;
					 $suppliersOrganizationResults = $this->SuppliersOrganization->find('first', $options);	
					
					$suppliersOrganizationResults['SuppliersOrganization']['owner_supplier_organization_id'] = $supplier_organization_id;
					$suppliersOrganizationResults['SuppliersOrganization']['owner_organization_id'] = $this->user->organization['Organization']['id'];
					self::d($suppliersOrganizationResults, $debug);
					
					if (!$this->SuppliersOrganization->save($suppliersOrganizationResults)) {
						$this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
						$continua = false;
					} 					
				}
				
				if($continua) {						
					/*
					 * REFERENTE, COREFERENTE lato front-end viene evidenziata la differenza
					*/
					$types = ClassRegistry::init('SuppliersOrganizationsReferent')->enumOptions('type');
					
					App::import('Model', 'SuppliersOrganizationsReferent');
					$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
							
					foreach ($types as $type => $value) {
						
						self::d("$type => $value", $debug);

						if(!empty($this->request->data['referent_user_ids-'.$type])) {
					
							$data['SuppliersOrganizationsReferent']['type'] = $type;
					
							$arr_referenti = explode(',', $this->request->data['referent_user_ids-'.$type]);

							$data = [];
							$data['SuppliersOrganizationsReferent']['supplier_organization_id'] = $supplier_organization_id;
							$data['SuppliersOrganizationsReferent']['type'] = 'REFERENTE';
							$data['SuppliersOrganizationsReferent']['group_id'] = Configure::read('group_id_referent');
							foreach ($arr_referenti as $user_id) {
								$data['SuppliersOrganizationsReferent']['user_id'] = $user_id;
								$SuppliersOrganizationsReferent->insert($this->user, $data, $debug);
							}
						}
					} // foreach ($types as $type => $value) 

					$this->reloadUserParams();
					
					/*
					 * invio mail a Configure::read('group_id_root_supplier')
					 */
					App::import('Model', 'User');
					$User = new User;
		
					App::import('Model', 'Mail');
					$Mail = new Mail;
					
					$Email = $Mail->getMailSystem($this->user);
					
					$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_root_supplier'));
					$userResults = $User->getUsersNoOrganization($conditions);
										
					$subject_mail = "Inserito nuovo produttore";
					$body_mail  = "Inserito nuovo produttore per il G.A.S. <b>".$this->user->organization['Organization']['name'].'</b>'; 
					$body_mail .= " da parte del referente ".$this->user->name." - <a href=mailto:".$this->user->email.">".$this->user->email."</a>";
					$body_mail .= ' con la denominazione <b>'.$this->request->data['Supplier']['name'].'</b><br />'; 
					$body_mail .= "Ha lo stato <span style=color:red>Temporaneo</span>: controlla i dati per renderlo visibile a tutti gli altri G.A.S.";	
					
					if(!empty($this->request->data['Supplier']['www']))
						$body_mail .= '<br />Sito web: '.$this->traslateWww($this->request->data['Supplier']['www']);
					if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y' && !empty($this->request->data['Supplier']['category_supplier_id'])) {
							App::import('Model', 'CategoriesSupplier');
							$CategoriesSupplier = new CategoriesSupplier;

							$options = [];
							$options['conditions'] = array('CategoriesSupplier.id' => $this->request->data['Supplier']['category_supplier_id']);
							$options['fields'] = array('CategoriesSupplier.name');
							$options['order'] = array('CategoriesSupplier.name');
							$categoriesResults = $CategoriesSupplier->find('first', $options);
					
							$body_mail .= '<br />Categoria: '.$categoriesResults['CategoriesSupplier']['name'];
					}
					
					$Email->subject($subject_mail);
					if(!empty($this->user->organization['Organization']['www']))
						$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
					else
						$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);
											
					foreach ($userResults as $userResult)  {
						$name = $userResult['User']['name'];
						$mail = $userResult['User']['email'];
							
						if(!empty($mail)) {
							$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
							$Email->to($mail);
							
							$Mail->send($Email, $mail, $body_mail, $debug);							
						} // end if(!empty($mail))
					} // end foreach ($userResults as $userResult)
						
					$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=SuppliersOrganizationsJcontents&action=edit&supplier_organization_id='.$supplier_organization_id;	
					if(!$debug)
						$this->myRedirect($url);
					else
						self::d($url, $debug);	
				}
			}
		}

		App::import('Model', 'CategoriesSupplier');
		$CategoriesSupplier = new CategoriesSupplier;

		$options = [];
		$options['order'] = array('CategoriesSupplier.name');
		$categories = $CategoriesSupplier->find('list', $options);
		$this->set(compact('categories'));			

        App::import('Model', 'SuppliersDeliveriesType');
        $SuppliersDeliveriesType = new SuppliersDeliveriesType;

        $options = [];
        $options['order'] = array('SuppliersDeliveriesType.sort');
        $suppliersDeliveriesType = $SuppliersDeliveriesType->find('list', $options);
        $this->set(compact('suppliersDeliveriesType'));

		$mail_order_open = ClassRegistry::init('SuppliersOrganization')->enumOptions('mail_order_open');
		$mail_order_close = ClassRegistry::init('SuppliersOrganization')->enumOptions('mail_order_close');
		$prod_gas_supplier_owner_articles = ClassRegistry::init('SuppliersOrganization')->enumOptions('owner_articles');
		$prod_gas_supplier_can_view_orders = ClassRegistry::init('SuppliersOrganization')->enumOptions('can_view_orders');
		$prod_gas_supplier_can_view_orders_users = ClassRegistry::init('SuppliersOrganization')->enumOptions('can_view_orders_users');
		$prod_gas_supplier_can_promotions = ClassRegistry::init('SuppliersOrganization')->enumOptions('can_promotions');	
		$this->set(compact('stato', 'mail_order_open', 'mail_order_close', 'prod_gas_supplier_owner_articles', 'prod_gas_supplier_can_view_orders', 'prod_gas_supplier_can_view_orders_users', 'prod_gas_supplier_can_promotions'));
			
		/*
		 *  elenco users per gestione referenti
		 */
		App::import('Model', 'User');
		$User = new User;
		
		/*
		 * imposto gia' l'utente come referente
		 */
		$referents = [];
		$referents[$this->user->get('id')] = $this->user->get('name'); 
		
		$user_id = $this->user->get('id');
		$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'),
							'UserGroupMap.user_id NOT IN ('.$user_id.')');
		$users = $User->getUsersList($this->user, $conditions);
		$this->set(compact('users'));		
		
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
	
	/*
	 * modifico un produttore
	 * 	se Supplier.stato =  Temporaneo posso modificare i dati admin_edit_new_stato_t
	 *  se Supplier.stato != Temporaneo posso modificare solo alcuni dati admin_edit_new
	 */
	public function admin_edit($supplier_orgaqnization_id=0) {

		$debug = false;
		
		$this->SuppliersOrganization->id = $supplier_orgaqnization_id;
		if (!$this->SuppliersOrganization->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
	    App::import('Model', 'SuppliersDeliveriesType');
        $SuppliersDeliveriesType = new SuppliersDeliveriesType;
		
		/*
		 * dati produttore
		 */
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $supplier_orgaqnization_id];
		$options['recursive'] = 0;
			
		$this->SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization')));
		$results = $this->SuppliersOrganization->find('first', $options);
		
		/*
		 * distribuzione
		 */
        $options = [];
		$options['conditions'] = ['SuppliersDeliveriesType.id' => $results['Supplier']['delivery_type_id']];
		$options['recursive'] = -1;
        $suppliersDeliveriesTypesResults = $SuppliersDeliveriesType->find('first', $options);
		$results['SuppliersDeliveriesType'] = $suppliersDeliveriesTypesResults['SuppliersDeliveriesType'];
		
		$this->set(compact('results'));
		
		if ($this->request->is('post') || $this->request->is('put')) {

			$msg = "";
			$esito = true;
			
			self::d("this->request->data", $debug);
			self::d($this->request->data, $debug);
			
			/*
			 * Temporaneo / Page => posso modificare tutto
			 */ 
			switch($results['Supplier']['stato']) {
				case 'T':
				case 'PG':
				
					$data = [];
					$data['Supplier']['id'] = $results['SuppliersOrganization']['supplier_id'];
					if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
						$data['Supplier']['category_supplier_id'] = $this->request->data['SuppliersOrganization']['category_supplier_id'];
					else
						$data['Supplier']['category_supplier_id'] = 0;
					$data['Supplier']['name'] = $this->request->data['SuppliersOrganization']['name'];
					$data['Supplier']['nome'] = $this->request->data['SuppliersOrganization']['nome'];
					$data['Supplier']['cognome'] = $this->request->data['SuppliersOrganization']['cognome'];
					$data['Supplier']['descrizione'] = $this->request->data['SuppliersOrganization']['descrizione'];
					$data['Supplier']['indirizzo'] = $this->request->data['SuppliersOrganization']['indirizzo'];
					$data['Supplier']['localita'] = $this->request->data['SuppliersOrganization']['localita'];
					$data['Supplier']['cap'] = $this->request->data['SuppliersOrganization']['cap'];
					$data['Supplier']['provincia'] = $this->request->data['SuppliersOrganization']['provincia'];
					$data['Supplier']['lat'] = '';
					$data['Supplier']['lng'] = '';
					$data['Supplier']['telefono'] = $this->request->data['SuppliersOrganization']['telefono'];
					$data['Supplier']['telefono2'] = $this->request->data['SuppliersOrganization']['telefono2'];
					$data['Supplier']['fax'] = $this->request->data['SuppliersOrganization']['fax'];
					$data['Supplier']['mail'] = $this->request->data['SuppliersOrganization']['mail'];
					if(!empty($this->request->data['SuppliersOrganization']['www']))
						$data['Supplier']['www'] = $this->traslateWww($this->request->data['SuppliersOrganization']['www']);
					$data['Supplier']['nota'] = $this->request->data['SuppliersOrganization']['nota'];
					$data['Supplier']['cf'] = $this->request->data['SuppliersOrganization']['cf'];
					$data['Supplier']['piva'] = $this->request->data['SuppliersOrganization']['piva'];
					$data['Supplier']['conto'] = $this->request->data['SuppliersOrganization']['conto'];
					$data['Supplier']['delivery_type_id'] = $this->request->data['SuppliersOrganization']['delivery_type_id'];

					$data['Supplier']['j_content_id'] = $results['Supplier']['j_content_id'];
					$data['Supplier']['img1'] = $results['Supplier']['img1'];
					$data['Supplier']['stato'] = $results['Supplier']['stato'];
					
					/*
					 * il produttore creato ancora i Temporaneo, se il GAS lo crea e abilita can_promotions, il Supplier erederdita 
					 */					
					$data['Supplier']['can_promotions'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_promotions'];
					$data['Supplier']['owner_organization_id'] = 0; // id del Organization.type = PRODGAS
					
					self::d($data, $debug);
					
					App::import('Model', 'Supplier');
					$Supplier = new Supplier;
				
					$msg_errors = $Supplier->getMessageErrorsToValidate($Supplier, $data);
					if(!empty($msg_errors)) {
						self::d($msg_errors, $debug);
						$msg = __('The supplier could not be saved. Please, try again.').'<br />'.$msg_errors;
						$esito = false;
					}
					
					if($esito) {
						$Supplier->create();
						if (!$Supplier->save($data))  {
							$esito = false;
							$msg = __('The supplier could not be saved. Please, try again.');
						}
					}
				
					if($esito) {		
						$data = [];
						$data['SuppliersOrganization'] = $results['SuppliersOrganization'];
						$data['SuppliersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
						$data['SuppliersOrganization']['name'] = $this->request->data['SuppliersOrganization']['name'];
						$data['SuppliersOrganization']['frequenza'] = $this->request->data['SuppliersOrganization']['frequenza'];
						/*
						 * ora i radio e' disabled
						 * $data['SuppliersOrganization']['owner_articles'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_owner_articles'];
						 */
						$data['SuppliersOrganization']['can_view_orders'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_view_orders'];
						$data['SuppliersOrganization']['can_view_orders_users'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_view_orders_users'];					
						$data['SuppliersOrganization']['can_promotions'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_promotions'];
						if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
							$data['SuppliersOrganization']['category_supplier_id'] = $this->request->data['SuppliersOrganization']['category_supplier_id'];
						else
							$data['SuppliersOrganization']['category_supplier_id'] = 0;
						
						self::d($data, $debug);
							
						$msg_errors = $this->SuppliersOrganization->getMessageErrorsToValidate($this->SuppliersOrganization, $data);
						if(!empty($msg_errors)) {
							self::d($msg_errors, $debug);
							$msg = __('The supplier could not be saved. Please, try again.').'<br />'.$msg_errors;
							$esito = false;
						}
					
						if($esito) {					
							$this->SuppliersOrganization->create();
							if ($this->SuppliersOrganization->save($data))
								$esito = true;
							else {
								$esito = false;
								$msg = __('The supplier could not be saved. Please, try again.');
							}
						}
					}
				break;
				default: // Supplier.stato Y
				   /*
					* 	posso modificare solo FREQUENZA o lo stato (N, PG o T)
					*/	
					$data = [];
					$data['SuppliersOrganization'] = $results['SuppliersOrganization'];
					$data['SuppliersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
					$data['SuppliersOrganization']['frequenza'] = $this->request->data['SuppliersOrganization']['frequenza'];
					$data['SuppliersOrganization']['mail_order_open'] = $this->request->data['SuppliersOrganization']['mail_order_open'];
					$data['SuppliersOrganization']['mail_order_close'] = $this->request->data['SuppliersOrganization']['mail_order_close'];
					/*
					 * ora i radio e' disabled
					 * $data['SuppliersOrganization']['owner_articles'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_owner_articles'];
					 */
					$data['SuppliersOrganization']['can_view_orders'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_view_orders'];
					$data['SuppliersOrganization']['can_view_orders_users'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_view_orders_users'];				
					$data['SuppliersOrganization']['can_promotions'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_promotions'];
					if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
						$data['SuppliersOrganization']['category_supplier_id'] = $this->request->data['SuppliersOrganization']['category_supplier_id'];
					else
						$data['SuppliersOrganization']['category_supplier_id'] = 0;				
					$data['SuppliersOrganization']['stato'] = $this->request->data['SuppliersOrganization']['stato'];
					
					self::d($data, $debug);
												
					$this->SuppliersOrganization->create();
					if ($this->SuppliersOrganization->save($data)) 
						$esito = true;
					else
						$esito = false;
				break;
			} // switch($results['Supplier']['stato'])
			
			if($esito) {
				$this->Session->setFlash(__('The supplier has been saved'));
				if(!$debug) $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash($msg);
			}

		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		$stato = ClassRegistry::init('SuppliersOrganization')->enumOptions('stato');
		$mail_order_open = ClassRegistry::init('SuppliersOrganization')->enumOptions('mail_order_open');
		$mail_order_close = ClassRegistry::init('SuppliersOrganization')->enumOptions('mail_order_close');
		$prod_gas_supplier_owner_articles = ClassRegistry::init('SuppliersOrganization')->enumOptions('owner_articles');
		$prod_gas_supplier_can_view_orders = ClassRegistry::init('SuppliersOrganization')->enumOptions('can_view_orders');
		$prod_gas_supplier_can_view_orders_users = ClassRegistry::init('SuppliersOrganization')->enumOptions('can_view_orders_users');
		$prod_gas_supplier_can_promotions = ClassRegistry::init('SuppliersOrganization')->enumOptions('can_promotions');
		$this->set(compact('stato', 'mail_order_open', 'mail_order_close', 'prod_gas_supplier_owner_articles', 'prod_gas_supplier_can_view_orders', 'prod_gas_supplier_can_view_orders_users', 'prod_gas_supplier_can_promotions'));
		
		App::import('Model', 'CategoriesSupplier');
		$CategoriesSupplier = new CategoriesSupplier;

		$options = [];
		$options['order'] = array('CategoriesSupplier.name');
		$categories = $CategoriesSupplier->find('list', $options);
		$this->set(compact('categories'));			
		
        $options = [];
        $options['order'] = array('SuppliersDeliveriesType.sort');
        $suppliersDeliveriesType = $SuppliersDeliveriesType->find('list', $options);
        $this->set(compact('suppliersDeliveriesType'));
		
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
		 * totale articoli
		*/				
		$totArticlesAttivi = $this->SuppliersOrganization->getTotArticlesAttivi($this->user, $supplier_orgaqnization_id);
		$totArticlesAttivi = $totArticlesAttivi['totArticles'];
		$this->set(compact('totArticlesAttivi'));

		self::d([$results, 'totArticlesAttivi '.$totArticlesAttivi], $debug);
		
		/*
		 * se e' TEMPORANEO (non ancora validato da admin) o PG (pagina di un GAS) posso modificarlo
		*/
		if($results['Supplier']['stato']=='T' || $results['Supplier']['stato']=='PG') 
			$this->render('admin_edit_new_stato_t');
		else
			$this->render('admin_edit_new');
	}

	/*
	 * se e' solo il GAS ad utilizzarlo cancello anche Supplier
	 */
	public function admin_delete($id = null) {
		
		$debug=false;
		
		$this->SuppliersOrganization->id = $id;
  		if (!$this->SuppliersOrganization->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		$this->SuppliersOrganization->hasMany['Article']['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id']);
		$this->SuppliersOrganization->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id']);
		$this->SuppliersOrganization->hasMany['SuppliersOrganizationsReferent']['conditions'] = array('SuppliersOrganizationsReferent.organization_id' => $this->user->organization['Organization']['id']);
		
		$options = [];		
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $id];
		$options['recursive'] = 1;
		$results = $this->SuppliersOrganization->find('first', $options);
		self::d($results, $debug);
		
		$this->set(compact('results'));
		
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->SuppliersOrganization->delete()) { 
				$this->Session->setFlash(__('Delete Supplier Organization'));
			
				/*
				 * ctrl se il Supplier e' condiviso da altri GAS
				*/
				App::import('Model', 'Supplier');
				$Supplier = new Supplier;
						
				$options = [];		
				$options['conditions'] = ['SuppliersOrganization.organization_id != ' => $this->user->organization['Organization']['id'],
										  'SuppliersOrganization.supplier_id' => $results['SuppliersOrganization']['supplier_id']];
				$options['recursive'] = -1;
				$suppliersOrganizationCount = $this->SuppliersOrganization->find('count', $options);
				self::d("Cerco se il Supplier ".$results['SuppliersOrganization']['supplier_id']." e' condiviso da altri GAS", $debug);
				self::d($options['conditions'], $debug);
				self::d($suppliersOrganizationCount, $debug);
				
				if($suppliersOrganizationCount==0) {
				
					self::d("il Supplier NON e' condiviso da altri GAS => delete", $debug);
				
					/*
					 * il Supplier NON e' condiviso da altri GAS => delete
					 */
					App::import('Model', 'Supplier');
					$Supplier = new Supplier;
						
					$Supplier->id = $results['SuppliersOrganization']['supplier_id'];
					if(!$Supplier->delete()) {
					
					} 					
				} // end if($suppliersOrganizationCount==0)	
			}	   
			else
				$this->Session->setFlash(__('Supplier was not deleted'));
				
			if(!$debug)
				$this->myRedirect(['action' => 'index']);
		}
	}
}