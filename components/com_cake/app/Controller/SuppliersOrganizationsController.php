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
		
		$FilterSuppliersOrganizationName=null;
		$FilterSuppliersOrganizationCategoryId=null;
		$SqlLimit = 20;

		$conditions[] = array('SuppliersOrganization.organization_id = '.(int)$this->user->organization['Organization']['id']);

		if($this->isSuperReferente()) {
			/* recupero dati dalla Session gestita in appController::beforeFilter */
			if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Name')) {
				$FilterSuppliersOrganizationName = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Name');
				if(!empty($FilterSuppliersOrganizationName)) $conditions[] = array('SuppliersOrganization.name LIKE '=>'%'.$FilterSuppliersOrganizationName.'%');
			}

			if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
				if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'CategoryId')) {
					$FilterSuppliersOrganizationCategoryId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'CategoryId');
					$conditions[] = array('SuppliersOrganization.category_supplier_id'=>$FilterSuppliersOrganizationCategoryId);
				}
			}
							
			/* filtro */
			$this->set('FilterSuppliersOrganizationName', $FilterSuppliersOrganizationName);
			$this->set('FilterSuppliersOrganizationCategoryId', $FilterSuppliersOrganizationCategoryId);
			
			App::import('Model', 'CategoriesSupplier');
			$CategoriesSupplier = new CategoriesSupplier;
	
			$options = array();
			$options['order'] = array('CategoriesSupplier.name');
			$categories = $CategoriesSupplier->find('list', $options);
			$this->set(compact('categories'));			
		}
		else {
			$conditions[] = array('SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');
		}

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
	 */
	public function admin_add_index() {
		
		$debug = false;

		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
			
		if ($this->request->is('post') || $this->request->is('put')) {
			
			if($debug) {
				echo '<h2>->request->data </h2>';
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";
			}
			
			$supplier_id = $this->request->data['supplier_id'];
			$Supplier->id = $supplier_id;
			if (!$Supplier->exists()) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
			
			$options = array();
			$options['conditions'] = array('Supplier.id' => $supplier_id);			
			$options['recursive'] = -1;
			$results = $Supplier->find('first', $options);
			if($debug) {
				echo '<h2>Dati produttore MASTER</h2>';
				echo "<pre>";
				print_r($results);
				echo "</pre>";
			}
			
			$data = array();
			$data['SuppliersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
			$data['SuppliersOrganization']['supplier_id'] = $results['Supplier']['id'];
			$data['SuppliersOrganization']['name'] = $results['Supplier']['name'];
			$data['SuppliersOrganization']['category_supplier_id'] = $results['Supplier']['category_supplier_id'];
			$data['SuppliersOrganization']['stato'] = 'Y';
			$data['SuppliersOrganization']['mail_order_open'] = 'Y';
			$data['SuppliersOrganization']['mail_order_close'] = 'Y';
			$this->SuppliersOrganization->create();
			
			if($debug) {
				echo '<h2>Dati produttore SLAVE</h2>';
				echo "<pre>";
				print_r($data);
				echo "</pre>";
			}
			
			if ($this->SuppliersOrganization->save($data)) {
				
				/*
				 * dont'work: restituisce supplier_id
				* $supplier_organization_id = $this->SuppliersOrganization->getLastInsertId();
				*/
				$supplier_organization_id = $this->SuppliersOrganization->id;
				
				if($debug) echo "<br />supplier_articles ".$this->request->data['SuppliersOrganization']['supplier_articles'];
				
				/*
				 * importo anche gli articoli del produttore
				 */
				if($this->request->data['SuppliersOrganization']['supplier_articles']=='Y') {
					
					App::import('Model', 'SuppliersOrganization');
					$SuppliersOrganization = new SuppliersOrganization;
					
					$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization', 'Supplier', 'CategoriesSupplier')));
					$SuppliersOrganization->unbindModel(array('hasMany' => array('Order', 'SuppliersOrganizationsReferent')));
						
					$options = array();
					$options['conditions'] = array('SuppliersOrganization.organization_id !=' => $this->user->organization['Organization']['id'], /* non prendo quello dello user perche' devo ancora associarlo */
													'SuppliersOrganization.supplier_id' => $supplier_id);
					$options['recursive'] = 1;
					$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
					
					App::import('Model', 'Article');
					
					if(isset($suppliersOrganizationResults['Article']))
					foreach ($suppliersOrganizationResults['Article'] as $numResult  => $article) {
					
						/*
						 * non gli passo organization_id dell'utente ma dell'organization da cui copio i prodotti 
						 * cosi' estraggo l'articolo master
						 */
						$user->organization['Organization']['id'] = $suppliersOrganizationResults['SuppliersOrganization']['organization_id'];
						$id = $article['id'];
						
						$Article = new Article;
						
						$articleCopy = array();
						$articleCopy = $Article->copy_prepare($user, $id, $debug);
						$articleCopy['Article']['id'] = $Article->getMaxIdOrganizationId($this->user->organization['Organization']['id']);
						$articleCopy['Article']['organization_id'] = $this->user->organization['Organization']['id'];
						$articleCopy['Article']['supplier_organization_id'] = $supplier_organization_id;
						
						$articleCopy = $Article->copy_img($this->user, $user->organization['Organization']['id'], $articleCopy, $debug);
						$articleCopy = $Article->copy_article_type($this->user, $articleCopy, $debug);
						
						if($debug) {
							echo '<h1>articolo SLAVE (nuovo dalla copia del MASTER)</h1>';
							echo "<pre>";
							print_r($articleCopy);
							echo "</pre>";
						}
						
						$Article->create();
						$Article->save($articleCopy['Article'], array('validate' => false));	
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
					
					$data = array();
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
		if($debug) echo '<br />'.$sql;
		$results = $this->SuppliersOrganization->query($sql);
		$this->set('results', $results);
		
		/* filtro */
		$this->set('FilterSuppliersOrganizationName', $FilterSuppliersOrganizationName);
		$this->set('FilterSuppliersOrganizationCategoryId', $FilterSuppliersOrganizationCategoryId);
		$this->set('FilterSuppliersOrganizationProvincia', $FilterSuppliersOrganizationProvincia);
		$this->set('FilterSuppliersOrganizationCap', $FilterSuppliersOrganizationCap);
		
		App::import('Model', 'CategoriesSupplier');
		$CategoriesSupplier = new CategoriesSupplier;

		$options = array();
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

			if($debug) {
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";				
			}
			
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
			$data['Supplier']['stato'] = 'T';
			$Supplier->create();
			if($debug) {
				echo "<pre>";
				print_r($data);
				echo "</pre>";
			}
			if (!$Supplier->save($data)) {
				$this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
			} else {
				$this->Session->setFlash(__('The supplier has been saved'));
				
				$supplier_id = $Supplier->getLastInsertId();
					
				/*
				 * inserisco in SuppliersOrgnization
				*/			
				$data = array();
				$data['SuppliersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
				$data['SuppliersOrganization']['supplier_id'] = $supplier_id;
				$data['SuppliersOrganization']['name'] = $this->request->data['Supplier']['name'];
				if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
					$data['SuppliersOrganization']['category_supplier_id'] = $this->request->data['Supplier']['category_supplier_id'];
				else
					$data['SuppliersOrganization']['category_supplier_id'] = 0;
				$data['SuppliersOrganization']['frequenza'] = $this->request->data['Supplier']['frequenza'];
				$data['SuppliersOrganization']['stato'] = 'Y';
				$data['SuppliersOrganization']['mail_order_open'] = $this->request->data['Supplier']['mail_order_open'];
				$data['SuppliersOrganization']['mail_order_close'] = $this->request->data['Supplier']['mail_order_close'];
				$data['SuppliersOrganization']['owner_articles'] = $this->request->data['Supplier']['prod_gas_supplier_owner_articles'];
				$data['SuppliersOrganization']['can_view_orders'] = $this->request->data['Supplier']['prod_gas_supplier_can_view_orders'];
				$data['SuppliersOrganization']['can_view_orders_users'] = $this->request->data['Supplier']['prod_gas_supplier_can_view_orders_users'];
				
				$this->SuppliersOrganization->create();
				if($debug) {
					echo "<pre>";
					print_r($data);
					echo "</pre>";
				}
				if (!$this->SuppliersOrganization->save($data)) {
					$this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
				} else {
					$this->Session->setFlash(__('The supplier has been saved'));
				
					$supplier_organization_id = $this->SuppliersOrganization->getLastInsertId();
					
					/*
					 * REFERENTE, COREFERENTE lato front-end viene evidenziata la differenza
					*/
					$types = ClassRegistry::init('SuppliersOrganizationsReferent')->enumOptions('type');
					
					App::import('Model', 'SuppliersOrganizationsReferent');
					$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
							
					foreach ($types as $type => $value) {
						
						if($debug) echo "<br />$type => $value";

						if(!empty($this->request->data['referent_user_ids-'.$type])) {
					
							$data['SuppliersOrganizationsReferent']['type'] = $type;
					
							$arr_referenti = explode(',', $this->request->data['referent_user_ids-'.$type]);

							$data = array();
							$data['SuppliersOrganizationsReferent']['supplier_organization_id'] = $supplier_organization_id;
							$data['SuppliersOrganizationsReferent']['type'] = 'REFERENTE';
							$data['SuppliersOrganizationsReferent']['group_id'] = Configure::read('group_id_referent');
							foreach ($arr_referenti as $user_id) {
								$data['SuppliersOrganizationsReferent']['user_id'] = $user_id;
								$SuppliersOrganizationsReferent->insert($this->user, $data, $debug);
							}
						}
					}

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

							$options = array();
							$options['conditions'] = array('CategoriesSupplier.id' => $this->request->data['Supplier']['category_supplier_id']);
							$options['fields'] = array('CategoriesSupplier.name');
							$options['order'] = array('CategoriesSupplier.name');
							$categoriesResults = $CategoriesSupplier->find('first', $options);
					
							$body_mail .= '<br />Categoria: '.$categoriesResults['CategoriesSupplier']['name'];
					}
					
					$Email->subject($subject_mail);
					if(!empty($this->user->organization['Organization']['www']))
						$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))));
					else
						$Email->viewVars(array('body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))));
											
					foreach ($userResults as $userResult)  {
						$name = $userResult['User']['name'];
						$mail = $userResult['User']['email'];
							
						if(!empty($mail)) {
							$Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
							$Email->to($mail);
							
							$Mail->send($Email, $mail, $body_mail, $debug);							
						} // end if(!empty($mail))
					} // end foreach ($userResults as $userResult)
						
					$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=SuppliersOrganizationsJcontents&action=edit&supplier_organization_id='.$supplier_organization_id);
				}
			}
		}

		App::import('Model', 'CategoriesSupplier');
		$CategoriesSupplier = new CategoriesSupplier;

		$options = array();
		$options['order'] = array('CategoriesSupplier.name');
		$categories = $CategoriesSupplier->find('list', $options);
		$this->set(compact('categories'));			

		$mail_order_open = ClassRegistry::init('SuppliersOrganization')->enumOptions('mail_order_open');
		$mail_order_close = ClassRegistry::init('SuppliersOrganization')->enumOptions('mail_order_close');
		$prod_gas_supplier_owner_articles = ClassRegistry::init('SuppliersOrganization')->enumOptions('owner_articles');
		$prod_gas_supplier_can_view_orders = ClassRegistry::init('SuppliersOrganization')->enumOptions('can_view_orders');
		$prod_gas_supplier_can_view_orders_users = ClassRegistry::init('SuppliersOrganization')->enumOptions('can_view_orders_users');	
		$this->set(compact('stato', 'mail_order_open', 'mail_order_close', 'prod_gas_supplier_owner_articles', 'prod_gas_supplier_can_view_orders', 'prod_gas_supplier_can_view_orders_users'));
			
		/*
		 *  elenco users per gestione referenti
		 */
		App::import('Model', 'User');
		$User = new User;
		
		/*
		 * imposto gia' l'utente come referente
		 */
		$referents = array();
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
		
		/*
		 * dati produttore
		 */
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									'SuppliersOrganization.id' => $supplier_orgaqnization_id);
		$options['recursive'] = 0;
			
		$this->SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization')));
		$results = $this->SuppliersOrganization->find('first', $options);
		$this->set(compact('results'));
		
		if ($this->request->is('post') || $this->request->is('put')) {

			$msg = "";
			$esito = true;
			
			if($debug) {
				echo '<h2>this->request->data</h2>';
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";
			}
			
			/*
			 * Temporaneo / Page => posso modificare tutto
			 */ 
			if($results['Supplier']['stato']=='T' || $results['Supplier']['stato']=='PG') {
				
				$data = array();
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

				$data['Supplier']['j_content_id'] = $results['Supplier']['j_content_id'];
				$data['Supplier']['img1'] = $results['Supplier']['img1'];
				$data['Supplier']['stato'] = $results['Supplier']['stato'];
				
				if($debug) {
					echo '<h2>Supplier</h2>';
					echo "<pre>";
					print_r($data);
					echo "</pre>";
				}
				
				App::import('Model', 'Supplier');
				$Supplier = new Supplier;
				
				$Supplier->set($data);
				if(!$Supplier->validates()) {
				
						$errors = $Supplier->validationErrors;
						$tmp = '';
						$flatErrors = Set::flatten($errors);
						if(count($errors) > 0) { 
							$tmp = '';
							foreach($flatErrors as $key => $value) 
								$tmp .= __($key).' '.$value.' - ';
						}
						$msg .= "Produttore non inserito: dati non validi, $tmp<br />";
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
					$data = array();
					$data['SuppliersOrganization']['id'] = $results['SuppliersOrganization']['id'];
					$data['SuppliersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
					$data['SuppliersOrganization']['supplier_id'] = $results['SuppliersOrganization']['supplier_id'];
					$data['SuppliersOrganization']['name'] = $this->request->data['SuppliersOrganization']['name'];
					$data['SuppliersOrganization']['frequenza'] = $this->request->data['SuppliersOrganization']['frequenza'];
					$data['SuppliersOrganization']['stato'] = $results['SuppliersOrganization']['stato'];
					$data['SuppliersOrganization']['mail_order_open'] = $results['SuppliersOrganization']['mail_order_open'];
					$data['SuppliersOrganization']['mail_order_close'] = $results['SuppliersOrganization']['mail_order_close'];
					$data['SuppliersOrganization']['owner_articles'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_owner_articles'];
					$data['SuppliersOrganization']['can_view_orders'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_view_orders'];
					$data['SuppliersOrganization']['can_view_orders_users'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_view_orders_users'];					
					if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
						$data['SuppliersOrganization']['category_supplier_id'] = $this->request->data['SuppliersOrganization']['category_supplier_id'];
					else
						$data['SuppliersOrganization']['category_supplier_id'] = 0;
					
					if($debug) {
						echo '<h2>SuppliersOrganization</h2>';
						echo "<pre>";
						print_r($data);
						echo "</pre>";
					}
						
					$this->SuppliersOrganization->set($data);
					if(!$this->SuppliersOrganization->validates()) {
					
							$errors = $this->SuppliersOrganization->validationErrors;
							$tmp = '';
							$flatErrors = Set::flatten($errors);
							if(count($errors) > 0) { 
								$tmp = '';
								foreach($flatErrors as $key => $value) 
									$tmp .= __($key).' '.$value.' - ';
							}
							$msg .= "Produttore non inserito: dati non validi, $tmp<br />";
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
			}
			else {
			   /*
				* 	posso modificare solo FREQUENZA o lo stato (N, PG o T)
				*/	
				$data = array();
				$data['SuppliersOrganization']['id'] = $results['SuppliersOrganization']['id'];
				$data['SuppliersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
				$data['SuppliersOrganization']['frequenza'] = $this->request->data['SuppliersOrganization']['frequenza'];
				$data['SuppliersOrganization']['mail_order_open'] = $this->request->data['SuppliersOrganization']['mail_order_open'];
				$data['SuppliersOrganization']['mail_order_close'] = $this->request->data['SuppliersOrganization']['mail_order_close'];
				$data['SuppliersOrganization']['owner_articles'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_owner_articles'];
				$data['SuppliersOrganization']['can_view_orders'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_view_orders'];
				$data['SuppliersOrganization']['can_view_orders_users'] = $this->request->data['SuppliersOrganization']['prod_gas_supplier_can_view_orders_users'];				
				if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
					$data['SuppliersOrganization']['category_supplier_id'] = $this->request->data['SuppliersOrganization']['category_supplier_id'];
				else
					$data['SuppliersOrganization']['category_supplier_id'] = 0;				
				$data['SuppliersOrganization']['stato'] = $this->request->data['SuppliersOrganization']['stato'];
				
				if($debug) {
					echo '<h2>SuppliersOrganization</h2>';
					echo "<pre>";
					print_r($data);
					echo "</pre>";
				}
											
				$this->SuppliersOrganization->create();
				if ($this->SuppliersOrganization->save($data)) 
					$esito = true;
				else
					$esito = false;
			} // end if stato 
			
			if($esito) {
				$this->Session->setFlash(__('The supplier has been saved'));
				if(!$debug) $this->myRedirect(array('action' => 'index'));
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
		$this->set(compact('stato', 'mail_order_open', 'mail_order_close', 'prod_gas_supplier_owner_articles', 'prod_gas_supplier_can_view_orders', 'prod_gas_supplier_can_view_orders_users'));
		
		App::import('Model', 'CategoriesSupplier');
		$CategoriesSupplier = new CategoriesSupplier;

		$options = array();
		$options['order'] = array('CategoriesSupplier.name');
		$categories = $CategoriesSupplier->find('list', $options);
		$this->set(compact('categories'));			
		
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

		if($debug) {
			echo '<h2>SuppliersOrganization->find</h2>';
			echo "<pre>";
			print_r($results);
			echo "</pre>";				
			echo '<h3>totArticlesAttivi '.$totArticlesAttivi.'</h3>';
		}
				
		/*
		 * se e' TEMPORANEO (non ancora validato da admin) o PG (pagina di un GAS) posso modificarlo
		*/
		if($results['Supplier']['stato']=='T' || $results['Supplier']['stato']=='PG') 
			$this->render('admin_edit_new_stato_t');
		else
			$this->render('admin_edit_new');
	}

	/*
	 * suppliers_organizations_Trigger
	 *		suppliers_organizations_referents
	 *    articles
	 *				article_orders
	 *				storerooms
	 *    orders
	 *				summary_orders
	 *				articles_orders
	 *						carts
    */
	public function admin_delete($id = null) {
		
		$this->SuppliersOrganization->id = $id;
  		if (!$this->SuppliersOrganization->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->SuppliersOrganization->delete()) 
				$this->Session->setFlash(__('Delete Supplier Organization'));   
			else
				$this->Session->setFlash(__('Supplier was not deleted'));
			$this->myRedirect(array('action' => 'index'));
		}

		$this->SuppliersOrganization->hasMany['Article']['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id']);
		$this->SuppliersOrganization->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id']);
		$this->SuppliersOrganization->hasMany['SuppliersOrganizationsReferent']['conditions'] = array('SuppliersOrganizationsReferent.organization_id' => $this->user->organization['Organization']['id']);
				
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										'SuppliersOrganization.id' => $id);
		$options['recursive'] = 1;
		$results = $this->SuppliersOrganization->find('first', $options);
	
		$this->set(compact('results'));
	}
}