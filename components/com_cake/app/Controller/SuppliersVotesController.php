<?php
App::uses('AppController', 'Controller');

class SuppliersVotesController extends AppController {

	public function beforeFilter() {
		 parent::beforeFilter();
		 
		 /* ctrl ACL */
		 if (in_array($this->action, array('admin_edit'))) {
		 	
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
		
		$FilterSuppliersVoteName=null;
		$FilterSuppliersVoteCategoryId=null;
		$SqlLimit = 20;

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$conditions[] = array('SuppliersOrganization.organization_id = '.(int)$this->user->organization['Organization']['id'],
							  "Supplier.stato IN ('Y', 'T')");

		if($this->isSuperReferente()) {
			/* recupero dati dalla Session gestita in appController::beforeFilter */
			if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Name')) {
				$FilterSuppliersVoteName = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Name');
				if(!empty($FilterSuppliersVoteName)) $conditions[] = array('SuppliersOrganization.name LIKE '=>'%'.$FilterSuppliersVoteName.'%');
			}

			if($this->user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') {
				if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'CategoryId')) {
					$FilterSuppliersVoteCategoryId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'CategoryId');
					$conditions[] = array('SuppliersOrganization.category_supplier_id'=>$FilterSuppliersVoteCategoryId);
				}
			}
							
			/* filtro */
			$this->set('FilterSuppliersVoteName', $FilterSuppliersVoteName);
			$this->set('FilterSuppliersVoteCategoryId', $FilterSuppliersVoteCategoryId);
			
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

		$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization')));
		$SuppliersOrganization->recursive = 0; 
        $this->paginate = array('conditions' => array($conditions), 'order' => 'SuppliersOrganization.name', 'limit' => $SqlLimit);
		$results = $this->paginate('SuppliersOrganization');
		/*
		echo "<pre>";
		print_r($conditions);
		echo "</pre>";
		*/
		foreach ($results as $numResult => $result) {

			/* 
			 * SuppliersVote del proprio GAS
			 */
			$options = array();
			$options['conditions'] = array('SuppliersVote.supplier_id' => $result['SuppliersOrganization']['supplier_id'],
										   'SuppliersVote.organization_id' => (int)$this->user->organization['Organization']['id']);
			$options['recursivo'] = 0;
			$this->SuppliersVote->unbindModel(array('belongsTo' => array('Organization', 'Supplier', 'CategoriesSupplier')));
			$this->SuppliersVote->unbindModel(array('hasMany' => array('SuppliersOrganization')));
			$suppliersVotesResults = $this->SuppliersVote->find('first', $options);
			if(!empty($suppliersVotesResults))   {
				$results[$numResult]['SuppliersVote'] = $suppliersVotesResults['SuppliersVote'];
				$results[$numResult]['User'] = $suppliersVotesResults['User'];
			}
			else {
				$results[$numResult]['SuppliersVote'] = '';
				$results[$numResult]['User'] = '';
			}
			
			/* 
			 * SuppliersVote di TUTTI i GAS
			 */			
			$suppliersVotesOrganizationsResults = $this->SuppliersVote->getOrganizationsVoto($result['SuppliersOrganization']['supplier_id']);
			if(!empty($suppliersVotesOrganizationsResults)) 
				$results[$numResult]['SuppliersVoteOrganization'] = $suppliersVotesOrganizationsResults;
			else
				$results[$numResult]['SuppliersVoteOrganization'] = '';			
		}
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
		$this->set('isSuperReferente',$this->isSuperReferente());
		
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

	public function admin_edit($supplier_orgaqnization_id=0) {

		$debug = false;

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		if (empty($supplier_orgaqnization_id) && isset($this->request->data['SuppliersVote']['supplier_organization_id'])) 
			$supplier_orgaqnization_id = $this->request->data['SuppliersVote']['supplier_organization_id'];
		
		if (!empty($supplier_orgaqnization_id)) {
			
			/*
			 * dati produttore
			 */		
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.id' => $supplier_orgaqnization_id);
			$options['recursive'] = -1;
			$suppliersOrganizationsResults = $SuppliersOrganization->find('first', $options);

			if(empty($suppliersOrganizationsResults)) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
			
			/*
			 * dati voto
			 */		
			App::import('Model', 'SuppliersVote');
			$SuppliersVote = new SuppliersVote;

			$options = array();
			$options['conditions'] = array('SuppliersVote.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersVote.supplier_id' => $suppliersOrganizationsResults['SuppliersOrganization']['supplier_id']);
			$options['recursive'] = -1;
			
			$suppliersVotesResults = $SuppliersVote->find('first', $options);
			
			$results = array_merge($suppliersOrganizationsResults, $suppliersVotesResults);		
			$this->set(compact('results'));
		
			$this->set('ACLsuppliersOrganization', '');	
			/*
			echo "<pre>";
			print_r($results);
			echo "</pre>";
			*/
		
			
			/* 
			 * SuppliersVote di TUTTI i GAS
			 */			
			$suppliersVotesOrganizationsResults = $this->SuppliersVote->getOrganizationsVoto($suppliersOrganizationsResults['SuppliersOrganization']['supplier_id']);	
			/*
			 * escludo proprio GAS
			 */
			foreach ($suppliersVotesOrganizationsResults as $numResult => $suppliersVoteOrganization) {
				// echo '<br />'.$suppliersVoteOrganization['SuppliersVote']['organization_id'].'  '.$this->user->organization['Organization']['id'];
				if($suppliersVoteOrganization['SuppliersVote']['organization_id']==$this->user->organization['Organization']['id']) 
					unset($suppliersVotesOrganizationsResults[$numResult]);
			}					
			$this->set(compact('suppliersVotesOrganizationsResults'));
		}
		else {
			/*
			 * produttore non passato => lista profilata dei produttori
			 */
			if($this->isSuperReferente()) {
				$options = array();
				$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
											   'SuppliersOrganization.stato' => 'Y');
				$options['order'] = array('SuppliersOrganization.name');
				$options['recursive'] = -1;
				$ACLsuppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
				$this->set('ACLsuppliersOrganization',$ACLsuppliersOrganizationResults);
			}
			else 
				$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());			 
		}
		

		if ($this->request->is('post') || $this->request->is('put')) {

			$esito = false;
		
			if($debug) {
				echo '<h2>this->request->data</h2>';
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";
			}
			
			$data = array();
			if(isset($suppliersVotesResults['SuppliersVote']['id']))
				$data['SuppliersVote']['id'] = $suppliersVotesResults['SuppliersVote']['id'];
			$data['SuppliersVote']['organization_id'] = $this->user->organization['Organization']['id'];
			$data['SuppliersVote']['user_id'] = $this->user->get('id');
			$data['SuppliersVote']['supplier_id'] = $suppliersOrganizationsResults['SuppliersOrganization']['supplier_id'];
			$data['SuppliersVote']['nota'] = $this->request->data['SuppliersVote']['nota'];
			$data['SuppliersVote']['voto'] = $this->request->data['SuppliersVote']['voto'];
			
			if($debug) {
				echo '<h2>SuppliersVote</h2>';
				echo "<pre>";
				print_r($data);
				echo "</pre>";
			}
										
			$this->SuppliersVote->create();
			if ($this->SuppliersVote->save($data)) 
				$esito = true;
			else
				$esito = false;

			
			if($esito) {
				$this->Session->setFlash(__('The suppliers vote has been saved'));
				if(!$debug) $this->myRedirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The suppliers vote could not be saved. Please, try again.'));
			}

		} 
		
		$votos = $this->SuppliersVote->getVotoOptions();
		$this->set(compact('votos'));
		
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
}