<?php
App::uses('AppController', 'Controller');

class DesSuppliersReferentsController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();
		
	    /* ctrl ACL */
   		if($this->user->organization['Organization']['hasDes']=='N' || !$this->isDes()) {
   			$this->Session->setFlash(__('msg_not_organization_config'));
   			$this->myRedirect(Configure::read('routes_msg_stop'));
   		}
		/* ctrl ACL */
		
		if(empty($this->user->des_id)) {
            $this->Session->setFlash(__('msg_des_choice'));
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Des&action=index';
			$this->myRedirect($url);
        }
		
		/*
		 * elenco di tutti i gruppi dell'organization userGroupsComponent
		*/
		$this->set('userGroups',$this->userGroups);
		
  		$this->set('isManagerDes', $this->isManagerDes());
   		$this->set('isReferenteDes', $this->isReferenteDes());
   		$this->set('isSuperReferenteDes', $this->isSuperReferenteDes());
   		$this->set('isTitolareDesSupplier', $this->isTitolareDesSupplier());		
	}

	/*
	 * elenco di tutti gli users associati al ruolo
	*/
	public function admin_index() {
		
		$debug = false;
		
		App::import('Model', 'DesSupplier');
		$DesSupplier = new DesSupplier;

		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
		
		App::import('Model', 'Organization');
		$Organization = new Organization;
		
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
		
		/*
		 * non lo passo dal metodo perche' quando ho i filtri di ricerca lo sovrascrive
		 */
		$group_id = $this->request->params['pass']['group_id'];
		self::d('group_id - join '.$group_id.' '.$this->userGroups[$group_id]['join'], $debug);
		if (empty($group_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			if(!$debug) $this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$FilterDesSuppliersReferentId = null;
		$FilterDesSuppliersReferentOrganizationId = null;
		
		$resultsFound = '';
		$results = [];
		
		$conditions = ['DesSuppliersReferent.des_id' => (int)$this->user->des_id,
					   'DesSuppliersReferent.group_id' => $group_id];
		
		/* recupero dati dalla Session gestita in appController::beforeFilter */ 
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Id')) 
			$FilterDesSuppliersReferentId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Id');
		
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'OrganizationId')) {
			$FilterDesSuppliersReferentOrganizationId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'OrganizationId');
			if($FilterDesSuppliersReferentOrganizationId!='ALL')
				$conditions += ['DesSuppliersReferent.organization_id' => (int)$this->user->organization['Organization']['id']]; // estraggo SOLO i referenti del mio GAS
			
			$this->set('FilterDesSuppliersReferentOrganizationIdDefault', $FilterDesSuppliersReferentOrganizationId);
		} 
		else
			$this->set('FilterDesSuppliersReferentOrganizationIdDefault', "ALL");
				
		/*
		 *  Supplier.ids di cui lo user e' referente
		 */	
		$arrayACLsuppliersIdsDes = []; 
		if($this->userGroups[$group_id]['join'] == 'DesSupplier' && !empty($ACLSuppliersResults)) {
			foreach($ACLSuppliersResults as $key => $ACLSuppliersResult)
				array_push($arrayACLsuppliersIdsDes, $key); 
		}

		self::d("arrayACLsuppliersIdsDes () Supplier.ids di cui lo user e' referente", $debug);
		self::d($arrayACLsuppliersIdsDes, $debug);

		if(!empty($FilterDesSuppliersReferentId)) {
			
			self::d("FilterDesSuppliersReferentId (ctrl se e' tra quelli di cui e' referente) ".$FilterDesSuppliersReferentId, $debug);
			
			/*
			 * ctrl che il produttore scelto sia tra quelli possibili per l'utente
			*/
			if(!in_array($FilterDesSuppliersReferentId, $arrayACLsuppliersIdsDes)) {
				$this->Session->setFlash(__('msg_not_permission'));
				if(!$debug) $this->myRedirect(Configure::read('routes_msg_stop'));
			}					
							
			$conditions += ['DesSuppliersReferent.des_supplier_id' => $FilterDesSuppliersReferentId];		}	

		$options = [];
		$options['conditions'] = $conditions;
		$options['recursive'] = 1;
		$results = $this->DesSuppliersReferent->find('all', $options);

		if(empty($results))
			$resultsFound = 'N';
		else {
			$resultsFound = 'Y';
		
			/*
			 * posso creare l'associazione con l'utente solo i produttori di cui sono referente
			*/
			foreach ($results as $numResult => $result) {

				/*
				 * bug, potrebbero non avere + il riferimento son DesSupplier
				 */
				if(empty($result['DesSupplier']['id']))
					unset($results[$numResult]);
				else {
					/*	
					echo '<br />des_supplier_id '.$result['DesSuppliersReferent']['des_supplier_id'];	
					echo "<pre>ho i permessi in ";
					print_r($arrayACLsuppliersIdsDes);
					echo "</pre>";
					*/
					
					/*
					 * solo se sono 
					 *   SuperReferenteDes o Referente del produttore
					 *   e il referente e' del mio GAS
					 */
					if($result['DesSuppliersReferent']['organization_id']==$this->user->organization['Organization']['id'] &&
						($this->isSuperReferenteDes() || 
						in_array($result['DesSuppliersReferent']['des_supplier_id'], $arrayACLsuppliersIdsDes)))
						$results[$numResult]['DesSuppliersReferent']['canUserDesGestRole'] = true;
					else
						$results[$numResult]['DesSuppliersReferent']['canUserDesGestRole'] = false;

					/*
					 * Organization
					 */
					$options = [];
					$options['conditions'] = ['Organization.id' => $result['User']['organization_id']];
					$options['recursive'] = -1;
					$organizationResults = $Organization->find('first', $options);
					
					$results[$numResult]['Organization'] = $organizationResults['Organization'];	

					/*
					 * Supplier
					 */
					$options = [];
					$options['conditions'] = ['Supplier.id' => $result['DesSupplier']['supplier_id']];
					$options['recursive'] = -1;
					$supplierResults = $Supplier->find('first', $options);
					$results[$numResult]['Supplier'] = $supplierResults['Supplier'];	

					self::d($result[$numResult]['Supplier']['name'].' ('.$result[$numResult]['Supplier']['id'].')', $debug);
					self::d(' - canUserDesGestRole. '.$results[$numResult]['DesSuppliersReferent']['canUserDesGestRole'], $debug);
				} // if(empty($result['DesSupplier']['id']))
			} // end loops
		}
		
		self::d($results, false);
		$this->set(compact('results'));
						
		/*
		 * elenco utenti
		 */
		App::import('Model', 'User');
		$User = new User;
		
		$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user')];
		$users = $User->getUsersList($this->user, $conditions);
		$this->set('users',$users);
		
		
		/* filtro */
		$desSuppliersReferentOrganizationId['ALL'] = "Tutti i GAS";
		$desSuppliersReferentOrganizationId['OWN'] = "Solo il mio GAS";
		$this->set('FilterDesSuppliersReferentId', $FilterDesSuppliersReferentId);
		$this->set('desSuppliersReferentOrganizationId', $desSuppliersReferentOrganizationId);		
		$this->set('resultsFound', $resultsFound);
		$this->set('group_id', $group_id);
	}

	/*
	 * se arrivo da admin_index des_supplier_id e' valorizzato
	 */
	public function admin_edit($des_supplier_id=0, $group_id=0) {
		
		$debug = false;

		App::import('Model', 'DesSupplier');
		$DesSupplier = new DesSupplier();
				
		$msg = "";
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			self::d($this->request->data, $debug);

			$des_supplier_id = $this->request->data['DesSuppliersReferent']['des_supplier_id'];
			$group_id = $this->request->data['DesSuppliersReferent']['group_id'];
			
			$data = [];
			$data['DesSuppliersReferent']['des_supplier_id'] = $des_supplier_id;
			$data['DesSuppliersReferent']['group_id'] = $group_id;
			
			if(!empty($this->request->data['referent_user_ids'])) {	
	
				$arr_referenti = explode(',', $this->request->data['referent_user_ids']);
				foreach ($arr_referenti as $user_id) {
					$data['DesSuppliersReferent']['user_id'] = $user_id;
					$this->DesSuppliersReferent->insert($this->user, $data, $debug);
				}

				/*
			 	 * gestione delete
			 	 */
				try {
							
					$sql = "DELETE FROM ".Configure::read('DB.prefix')."des_suppliers_referents
						WHERE
							des_id = ".(int)$this->user->des_id."
							AND organization_id = ".(int)$this->user->organization['Organization']['id']."
							AND des_supplier_id = $des_supplier_id
							AND group_id = $group_id
							AND user_id NOT IN (".$this->request->data['referent_user_ids'].")";
							self::d($sql, $debug);
							$result = $this->DesSuppliersReferent->query($sql);
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
				try {
							
					$sql = "DELETE FROM ".Configure::read('DB.prefix')."des_suppliers_referents
							WHERE
								des_id = ".(int)$this->user->des."
								AND organization_id = ".(int)$this->user->organization['Organization']['id']."
								AND des_supplier_id = $des_supplier_id
								AND group_id = $group_id ";
					self::d($sql, $debug);
					$result = $this->DesSuppliersReferent->query($sql);
				}
				catch (Exception $e) {
					CakeLog::write('error',$sql);
					CakeLog::write('error',$e);
				}					
			}
			
			/*
			 * se gestisco un ReferenteManagerDes, gestisco il GAS Manager own_organization_id
			 */
			if($group_id == Configure::read('group_id_titolare_des_supplier')) {
   				$DesSupplier->aggiornaOwnOrganizationId($this->user, $des_supplier_id, $debug);	
			}
					
			/*
			 * setto SupplierOrganization.owner_articles = DES o REFERENT per il produttore 
			 */		
			$suppliersOrganizationResults = $DesSupplier->getSuppliersOrganization($this->user, $des_supplier_id, $debug);
			if(!empty($suppliersOrganizationResults)) {
				$supplier_id = $suppliersOrganizationResults['Supplier']['id'];
				$DesSupplier->setSupplierOrganizationOwnerArticles($this->user, $supplier_id, $debug);			
			}
			
			$this->Session->setFlash(__('The supplier organization referent has been saved'));
		
		} // if ($this->request->is('post') || $this->request->is('put'))
		
		/*
	 	 *  elenco Supplier profilati
		 */
		$ACLSuppliersResults = [];
		if($this->isManagerDes() || $this->isSuperReferenteDes()) 
			$ACLSuppliersResults = $DesSupplier->getListDesSuppliers($this->user);
		else
			$ACLSuppliersResults = $this->getACLsuppliersIdsDes();
		$this->set('ACLdesSuppliers',$ACLSuppliersResults);
		self::d($ACLSuppliersResults, $debug);
		
		$this->set('des_supplier_id', $des_supplier_id);
		$this->set('group_id', $group_id);
	}
	
	/*
	 * se $des_supplier_id = 0 , sto creando un nuovo produttore 
	 */
	public function admin_ajax_box_users($des_supplier_id, $group_id) {
		
		$debug = false;
		
		if($debug) echo '<br />DesSuppliersReferentsController->admin_ajax_box_users() - des_supplier_id '.$des_supplier_id;

		$referents = [];
		$referenti_ids = '';
		if(!empty($des_supplier_id)) {
			/*
			 * estraggo i referenti
			*/
			$conditions = ['User.block' => 0,
							'User.organization_id' => $this->user->organization['Organization']['id'],
							'DesSupplier.des_id' => $this->user->des_id,
							'DesSupplier.id' => $des_supplier_id,
							'DesSuppliersReferent.organization_id' => $this->user->organization['Organization']['id'],
							'DesSuppliersReferent.group_id' => $group_id];
			$DesSuppliersReferent = $this->DesSuppliersReferent->getReferentsDesCompact($conditions, null, $debug);
			
			if($debug) {
				echo "<pre>DesSuppliersReferentsController->admin_ajax_box_users() - Referenti proprio GAS del DesSupplier \n ";
				print_r($conditions);
				print_r($DesSuppliersReferent);
				echo "</pre>";
			}
			
			/*
			 * ids dei referenti per escluderli dalla lista degli utenti
			*/
			if(!empty($DesSuppliersReferent))
				foreach ($DesSuppliersReferent as $ref) {
					$referenti_ids = $referenti_ids.$ref['User']['id'].',';
					$referents[$ref['User']['id']] = $ref['User']['name']; 
				}
		}
		$this->set('referents', $referents);
				
		/*
		 * estraggo gli utenti del proprio GAS
		 */
		App::import('Model', 'User');
		$User = new User;
		
		if(!empty($referenti_ids)) {
			$referenti_ids = substr($referenti_ids, 0, (strlen($referenti_ids)-1));
			$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user'),
						   'UserGroupMap.user_id NOT IN' => '('.$referenti_ids.')'];
		}
		else
			$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user')];
			
		$users = $User->getUsersList($this->user, $conditions);
		$this->set(compact('users'));	

		/*
		 * se gestisco i ReferentiTitolareDes puo' solo un GAS 
		 */
		$isOrganizationTitolare = false;
		$this->set(compact('isOrganizationTitolare'));
		if($group_id == Configure::read('group_id_titolare_des_supplier')) {
			
			App::import('Model', 'DesSupplier');
			$DesSupplier = new DesSupplier;		
			
		    $options = [];
			$options['conditions'] = ['DesSupplier.id' => $des_supplier_id,
									   'DesSupplier.des_id' => $this->user->des_id,
									   'DesSupplier.own_organization_id' => $this->user->organization['Organization']['id']];
			$options['recursive'] = -1;
			$totali = $DesSupplier->find('count', $options);
			
			if($debug) {
				echo "<pre>CTRL se il GAS e' gia' titolare \n";
				print_r($options);
				echo "</pre>";
				echo '<h1>'.$totali.'</h1>';
			}
							
			if($totali==0)
				$isOrganizationTitolare = false;
			else
				$isOrganizationTitolare = true;
			
			/* 
			 * se non ci sono ancora Referenti Titolari associati, il GAS lo diventera'
			 */
			if($isOrganizationTitolare) {
				if(empty($referents))
					$msg = "Il G.A.S. diventerà il titolare del produttore";
				else
					$msg = "Il G.A.S. è il titolare del produttore";
		
				$this->set(compact('msg'));
				$this->set(compact('isOrganizationTitolare'));	
			}			
			
			/*
			 * estraggo il GAS del titolare
			 */
			if(!$isOrganizationTitolare) {
			
			    $options = [];
				$options['conditions'] = ['DesSupplier.id' => $des_supplier_id,
										  'DesSupplier.des_id' => $this->user->des_id];
				$options['fields'] = ['DesSupplier.own_organization_id'];
				$options['recursive'] = -1;
				$desSupplierResults = $DesSupplier->find('first', $options);
				if($debug) {
					echo "<pre>GAS non e' titolare, estraggo eventale GAS titolare \n";
					print_r($options);
					print_r($desSupplierResults);
					echo "</pre>";
				}	
				$own_organization_id = $desSupplierResults['DesSupplier']['own_organization_id'];
				
				if($own_organization_id==0) {
					if($debug) echo '<h1>Nessun GAS e\' titolare</h1>';
					
					$isOrganizationTitolare = true;
					$msg = "Il G.A.S. diventerà il titolare del produttore";
					$this->set(compact('msg'));	
					$this->set(compact('isOrganizationTitolare'));					
				}
				else {
				
					App::import('Model', 'Organization');
					$Organization = new Organization;
					
					$options = [];
					$options['conditions'] = ['Organization.id'=>(int)$own_organization_id];
					$options['recursive'] = -1;
					$organizationTitolare = $Organization->find('first', $options);
					if($debug) {
						echo "<pre>dati del GAS titolare \n";
						print_r($options);
						print_r($organizationTitolare);
						echo "</pre>";
					}
					
					$this->set(compact('organizationTitolare'));	
					$this->render('/DesSuppliersReferents/admin_ajax_box_users_read');					
				}
				
			}
		}

		$this->layout = 'ajax';
	}
	
	/*	 * key = $_organization_id, $user_id, $des_supplier_id	*/
	public function admin_delete($user_id=0, $des_supplier_id=0, $group_id=0) {
		
		$debug = false;
		$msg = "";
		
		if(!$this->DesSuppliersReferent->exists($this->user->des_id, $this->user->organization['Organization']['id'], $user_id, $group_id, $des_supplier_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$msg .= __('Delete Supplier organization referent');		
		/*
		 *  ctrl se e' gia' referent,
		*  se NO lo e' associo lo joomla.users al gruppo referenti in joomla.user_usergroup_map
		*/
		$options['conditions'] = ['DesSuppliersReferent.des_id' => $this->user->des_id,
							   'DesSuppliersReferent.organization_id' => $this->user->organization['Organization']['id'],
							   'DesSuppliersReferent.user_id' => $user_id,
							   'DesSuppliersReferent.group_id' => $group_id];
		$totRows = $this->DesSuppliersReferent->find('count', $options);
		if($totRows==1) {
			App::import('Model', 'User');
			$User = new User;
			
			$User->joomlaBatchUser($group_id, $user_id, 'del');
			$msg .= "<br />e cancellato dal gruppo ";
			if($group_id==Configure::read('group_id_titolare_des_supplier'))
				$msg .= "Titolari del produttore del DES";
			else
				$msg .= "Referenti";
			
		}
		else 
			$msg .= "<br />ma non cancellato dal gruppo perchè associato ad altri produttori.";

		if ($this->DesSuppliersReferent->delete($this->user->des_id, $this->user->organization['Organization']['id'], $user_id, $group_id, $des_supplier_id)) {

			/*
			 * aggiorno il DesSupplier.own_organization_id
			 */
			App::import('Model', 'DesSupplier');
			$DesSupplier = new DesSupplier;
			$DesSupplier->aggiornaOwnOrganizationId($this->user, $des_supplier_id);
					
			/*
			 * setto SupplierOrganization.owner_articles = DES o REFERENT per il produttore 
			 */		
			 
			$suppliersOrganizationResults = $DesSupplier->getSuppliersOrganization($this->user, $des_supplier_id, $debug);
			if(!empty($suppliersOrganizationResults)) {
				$supplier_id = $suppliersOrganizationResults['Supplier']['id'];
				$DesSupplier->setSupplierOrganizationOwnerArticles($this->user, $supplier_id, $debug);			
			}

			// cancello eventuale sessione con il filtro per utente se no la ricerca e' sempre vuota
			if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'UserId')) {
				$this->Session->delete(Configure::read('Filter.prefix').$this->modelClass.'UserId');
				$this->Session->delete(Configure::read('Filter.prefix').$this->modelClass.'UserName');
			}
				
			$this->Session->setFlash($msg);
			$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesSuppliersReferents&action=index&group_id='.$group_id);
		}
		
		$this->Session->setFlash(__('Supplier organization referent was not deleted'));
		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesSuppliersReferents&action=index&group_id='.$group_id);	
	}	
	

}