<?php
App::uses('AppController', 'Controller');
/**
 * SuppliersOrganizationsReferents Controller
 *
 * @property SuppliersOrganizationsReferent $SuppliersOrganizationsReferent
 */
class SuppliersOrganizationsReferentsController extends AppController {
	 
	private $types;
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		/*
		 * elenco di tutti i gruppi dell'organization userGroupsComponent
		*/
		$this->set('userGroups',$this->userGroups);
		
		/*
		 * REFERENTE, COREFERENTE lato front-end viene evidenziata la differenza
		*/
		$this->types = ClassRegistry::init('SuppliersOrganizationsReferent')->enumOptions('type');	
		$this->set('types', $this->types);
	}

	/*
	 * elenco di tutti gli users associati al ruolo
	*/
	public function admin_index() {
		
		$debug = false;
		
		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
		
		/*
		 * non lo passo dal metodo perche' quando ho i filtri di ricerca lo sovrascrive
		 */
		$group_id = $this->request->params['pass']['group_id'];
		if($debug) echo '<br />group_id - join '.$group_id.' '.$this->userGroups[$group_id]['join'];
		
		if (empty($group_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			if(!$debug) $this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$FilterSuppliersOrganizationsReferentId = null;
		$FilterSuppliersOrganizationsReferentUserId = null;
		$FilterSuppliersOrganizationsReferentUserName=null;
		
		$resultsFound = '';
		$results = array();
		$SqlLimit = 20;
		
		$conditions = array('SuppliersOrganizationsReferent.organization_id' => (int)$this->user->organization['Organization']['id'],
							'SuppliersOrganizationsReferent.group_id' => $group_id);
		
		/* recupero dati dalla Session gestita in appController::beforeFilter */ 
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Id')) 
			$FilterSuppliersOrganizationsReferentId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Id');
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'UserId')) 
			$FilterSuppliersOrganizationsReferentUserId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'UserId');
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'UserName')) 			$FilterSuppliersOrganizationsReferentUserName = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'UserName');		
		/*
		 *  Supplier.ids di cui lo user e' referente
		 */
		if($this->userGroups[$group_id]['join'] == 'Supplier') 
			$arrayACLsuppliersIdsOrganization = explode(",", $this->user->get('ACLsuppliersIdsOrganization'));

		if($debug) {
			echo "<pre> arrayACLsuppliersIdsOrganization () Supplier.ids di cui lo user e' referente ";
			print_r($arrayACLsuppliersIdsOrganization);
			echo "</pre>";
		}
		
		if (!empty($FilterSuppliersOrganizationsReferentId) || !empty($FilterSuppliersOrganizationsReferentUserId) || !empty($FilterSuppliersOrganizationsReferentUserName)) {
			
			if (!empty($FilterSuppliersOrganizationsReferentId)) {
				
				if($debug) echo "<br />FilterSuppliersOrganizationsReferentId (ctrl se e' tra quelli di cui e' referente) ".$FilterSuppliersOrganizationsReferentId;
				
				/*
				 * ctrl che il produttore scelto sia tra quelli possibili per l'utente
				*/
				if(!in_array($FilterSuppliersOrganizationsReferentId, $arrayACLsuppliersIdsOrganization)) {
					$this->Session->setFlash(__('msg_not_permission'));
					if(!$debug) $this->myRedirect(Configure::read('routes_msg_stop'));
				}					
				
				
				$conditions += array('SuppliersOrganizationsReferent.supplier_organization_id' => $FilterSuppliersOrganizationsReferentId);
			}	
				
			if (!empty($FilterSuppliersOrganizationsReferentUserId)) 
				$conditions += array('SuppliersOrganizationsReferent.user_id' => $FilterSuppliersOrganizationsReferentUserId);
			
			if (!empty($FilterSuppliersOrganizationsReferentUserName)) 				$conditions[] = array('User.name LIKE '=>'%'.$FilterSuppliersOrganizationsReferentUserName.'%');		}	

		$this->SuppliersOrganizationsReferent->recursive = 0;
		$this->paginate = array('conditions' => $conditions,'order'=>'SuppliersOrganization.name');
		$results = $this->paginate('SuppliersOrganizationsReferent');
		
		if(empty($results))
			$resultsFound = 'N';
		else {
			$resultsFound = 'Y';
		
			/*
			 * posso creare l'associazione con l'utente solo i produttori di cui sono referente
			*/
			foreach ($results as $numResult => $result) {
					
				if(($this->isSuperReferente() || 
						in_array($result['SuppliersOrganizationsReferent']['supplier_organization_id'], $arrayACLsuppliersIdsOrganization)))
					$results[$numResult]['SuppliersOrganizationsReferent']['IsReferente'] = 'Y';
				else
					$results[$numResult]['SuppliersOrganizationsReferent']['IsReferente'] = 'N';
				
				/*
				 * Suppliers per l'immagine
				* */
				$options = array();
				$options['conditions'] = array('Supplier.id' => $result['SuppliersOrganization']['supplier_id']);
				$options['fields'] = array('Supplier.img1');
				$options['recursive'] = -1;
				$SupplierResults = $Supplier->find('first', $options);
				if(!empty($SupplierResults))
					$results[$numResult]['Supplier']['img1'] = $SupplierResults['Supplier']['img1'];	

				if($debug) {
					echo '<br />'.$result['Supplier']['name'].' ('.$result['Supplier']['id'].')';
					echo ' - IsReferente. '.$results[$numResult]['SuppliersOrganizationsReferent']['IsReferente'];
				}			
			}
		}
		$this->set('results', $results);
		
		/*
		*  elenco Supplier profilati
		*		Referente
		*		SuperReferente
		*/		
		$resultsACLsuppliersOrganization = array();
		if($this->userGroups[$group_id]['join'] == 'Supplier') {
			if($this->isSuperReferente()) 
				$resultsACLsuppliersOrganization = $Supplier->getListSuppliers($this->user, false);
			else
				$resultsACLsuppliersOrganization = $this->getACLsuppliersOrganization();
		}
		$this->set('ACLsuppliersOrganization', $resultsACLsuppliersOrganization);
		
		/*
		 * elenco utenti
		 */
		App::import('Model', 'User');
		$User = new User;
		
		$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
		$users = $User->getUsersList($this->user, $conditions);
		$this->set('users',$users);
		
		
		/* filtro */
		$this->set('FilterSuppliersOrganizationsReferentId', $FilterSuppliersOrganizationsReferentId);
		$this->set('FilterSuppliersOrganizationsReferentUserId', $FilterSuppliersOrganizationsReferentUserId);
		$this->set('FilterSuppliersOrganizationsReferentUserName', $FilterSuppliersOrganizationsReferentUserName);		
		$this->set('resultsFound', $resultsFound);
		$this->set('SqlLimit', $SqlLimit);
		$this->set('group_id', $group_id);
	}

	/*
	 * se arrivo da admin_index supplier_organization_id e' valorizzato
	 */
	public function admin_edit($supplier_organization_id=0, $group_id=0) {
		
		$debug = false;
		$msg = "";
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			if($debug) {
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";
			}

			$supplier_organization_id = $this->request->data['SuppliersOrganizationsReferent']['supplier_organization_id'];
			$group_id = $this->request->data['SuppliersOrganizationsReferent']['group_id'];
			
			$data = array();
			$data['SuppliersOrganizationsReferent']['supplier_organization_id'] = $supplier_organization_id;
				
			/*
			 * ciclo referent_user_ids-$type
			 */
			foreach ($this->types as $type => $value) {
				if(!empty($this->request->data['referent_user_ids-'.$type])) {
				
					$data['SuppliersOrganizationsReferent']['type'] = $type;
					$data['SuppliersOrganizationsReferent']['group_id'] = $group_id;
					
					$arr_referenti = explode(',', $this->request->data['referent_user_ids-'.$type]);
					foreach ($arr_referenti as $user_id) {
						$data['SuppliersOrganizationsReferent']['user_id'] = $user_id;
						$this->SuppliersOrganizationsReferent->insert($this->user, $data, $debug);
					}

					/*
					 * gestione delete
					*/
					try {
							
						$sql = "DELETE FROM ".Configure::read('DB.prefix')."suppliers_organizations_referents
									WHERE
								organization_id = ".(int)$this->user->organization['Organization']['id']."
								AND supplier_organization_id = $supplier_organization_id
								AND group_id = $group_id
								AND type = '".$type."'
								AND user_id NOT IN (".$this->request->data['referent_user_ids-'.$type].")";
						if($debug) echo '<br />'.$sql;
						$result = $this->SuppliersOrganizationsReferent->query($sql);
					}
					catch (Exception $e) {
						CakeLog::write('error',$sql);
						CakeLog::write('error',$e);
					}
				} 	
				else {
					/*
					 * se empty li cancello tutti
					 */
					/*
					 * gestione delete
					*/
					try {
							
						$sql = "DELETE FROM ".Configure::read('DB.prefix')."suppliers_organizations_referents
									WHERE
								organization_id = ".(int)$this->user->organization['Organization']['id']."
													AND supplier_organization_id = $supplier_organization_id
													AND group_id = $group_id
													AND type = '".$type."'";
						if($debug) echo '<br />'.$sql;
						$result = $this->SuppliersOrganizationsReferent->query($sql);
					}
					catch (Exception $e) {
						CakeLog::write('error',$sql);
						CakeLog::write('error',$e);
					}					
				} // end if(!empty($this->request->data['referent_user_ids-'.$type]))
			}  // end foreach ($this->types as $type => $value) 

			$this->Session->setFlash(__('The supplier organization referent has been saved'));
		
		} // if ($this->request->is('post') || $this->request->is('put'))
		
		/*
		*  elenco Supplier profilati
		*		Referente
		*		SuperReferente
		*/
		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
		
		$resultsACLsuppliersOrganization = array();
		if($this->userGroups[$group_id]['join'] == 'Supplier') {
			if($this->isSuperReferente()) 
				$resultsACLsuppliersOrganization = $Supplier->getListSuppliers($this->user);
			else
				$resultsACLsuppliersOrganization = $this->getACLsuppliersOrganization();
		}
		$this->set('supplierOrganizations',$resultsACLsuppliersOrganization);
		
		$this->set('supplier_organization_id', $supplier_organization_id);
		$this->set('group_id', $group_id);
	}
	
	/*
	 * se $supplier_organization_id = 0 , sto creando un nuovo produttore SuppliersOrganization::add_new
	 * $SuppliersOrganizationsReferentType REFERENTE, COREFERENTE
	 */
	public function admin_ajax_box_users($supplier_organization_id, $group_id, $SuppliersOrganizationsReferentType='REFERENTE') {
		
		$debug = false;
		
		$referents = array();
		$referenti_ids = '';
		if(!empty($supplier_organization_id)) {
			/*
			 * estraggo i referenti
			*/
			$conditions = array('User.block' => 0,
								'SuppliersOrganization.id' => $supplier_organization_id,
								'SuppliersOrganizationsReferent.group_id' => $group_id,
								'SuppliersOrganizationsReferent.type' => $SuppliersOrganizationsReferentType);
			$suppliersOrganizationsReferent = $this->SuppliersOrganizationsReferent->getReferentsCompact($this->user, $conditions);
			
			if($debug) {
				echo "<pre>";
				print_r($conditions);
				print_r($suppliersOrganizationsReferent);
				echo "</pre>";
			}
			
			/*
			 * ids dei referenti per escluderli dalla lista degli utenti
			*/
			foreach ($suppliersOrganizationsReferent as $ref) {
				$referenti_ids = $referenti_ids.$ref['User']['id'].',';
				$referents[$ref['User']['id']] = $ref['User']['name']; 
			}
		}
		$this->set('referents', $referents);
				
		App::import('Model', 'User');
		$User = new User;
		
		if(!empty($referenti_ids)) {
			$referenti_ids = substr($referenti_ids, 0, (strlen($referenti_ids)-1));
			$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'),
								'UserGroupMap.user_id NOT IN' => '('.$referenti_ids.')');
		}
		else
			$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
			
		$users = $User->getUsersList($this->user, $conditions);
		$this->set(compact('users'));	

		$this->set('SuppliersOrganizationsReferentType', $SuppliersOrganizationsReferentType);
		
		$this->layout = 'ajax';
	}
	
	/*	 * key = $_organization_id, $user_id, $supplier_organization_id	*/
	public function admin_delete($user_id=0, $supplier_organization_id=0, $group_id=0, $type) {
		$msg = "";
		
		if(!$this->SuppliersOrganizationsReferent->exists($this->user->organization['Organization']['id'], $user_id, $group_id, $supplier_organization_id, $type)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		$msg .= __('Delete Supplier organization referent');		
		/*
		 *  ctrl se e' gia' referent,
		*  se NO lo e' associo lo joomla.users al gruppo referenti in joomla.user_usergroup_map
		*/
		$options['conditions'] = array('SuppliersOrganizationsReferent.organization_id' => $this->user->organization['Organization']['id'],									   'SuppliersOrganizationsReferent.user_id' => $user_id,
									   'SuppliersOrganizationsReferent.group_id' => $group_id);
		$totRows = $this->SuppliersOrganizationsReferent->find('count', $options);
		if($totRows==1) {
			App::import('Model', 'User');
			$User = new User;
			
			$User->joomlaBatchUser($group_id, $user_id, 'del');
			$msg .= "<br />e cancellato dal gruppo Referenti";
			
		}
		else 
			$msg .= "<br />ma non cancellato dal gruppo Referenti perchè associato ad altri produttori.";
		
		if ($this->SuppliersOrganizationsReferent->delete($this->user->organization['Organization']['id'], $user_id, $group_id, $supplier_organization_id, $type)) {
			
			// cancello eventuale sessione con il filtro per utente se no la ricerca e' sempre vuota
			if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'UserId')) {
				$this->Session->delete(Configure::read('Filter.prefix').$this->modelClass.'UserId');
				$this->Session->delete(Configure::read('Filter.prefix').$this->modelClass.'UserName');
			}
				
			$this->Session->setFlash($msg);
			$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=SuppliersOrganizationsReferents&action=index&group_id='.$group_id);
		}
		$this->Session->setFlash(__('Supplier organization referent was not deleted'));
		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=SuppliersOrganizationsReferents&action=index&group_id='.$group_id);	
	}	
	

}