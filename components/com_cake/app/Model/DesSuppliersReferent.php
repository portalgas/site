<?php
App::uses('AppModel', 'Model');
App::import('Model', 'DesSuppliersReferentMultiKey');

class DesSuppliersReferent extends DesSuppliersReferentMultiKey {

	/*
	 *  dato un organization_id estrae i ruoli passati
	 * roles = Configure::read('group_id_manager_des'),Configure::read('group_id_super_referent_des'),Configure::read('group_id_referent_des'),Configure::read('group_id_titolare_des_supplier')
 	 */
	 public function getUsersRoles($user, $organization_id, $roles=[], $des_supplier_id=0, $debug=false) {
			
			// $debug=true;

	 		$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organization_id]);
			
			if($debug) debug("DesSuppliersReferent::getUsersRoles()");
			if($debug) debug($roles);
	 			 	
	 		$results = [];
	 		
	 		App::import('Model', 'User');
	 		App::import('Model', 'DesSuppliersReferent');
	 		
	 		/*
	 		 * estraggo MANAGER-DES
	 		 */
	 		if(in_array(Configure::read('group_id_manager_des'), $roles)) {
				$User = new User;
				
				$conditions = ['UserGroup.id' => Configure::read('group_id_manager_des')];
				$usersResults = $User->getUsers($tmp_user, $conditions);
				foreach($usersResults as $usersResult) {
					$results[$usersResult['User']['id']]['User'] = $usersResult['User'];
					if(isset($results[$usersResult['User']['id']]['User']['Group']))	
						array_push($results[$usersResult['User']['id']]['User']['Group'], Configure::read('group_id_manager_des'));
					else
						$results[$usersResult['User']['id']]['User']['Group'] = array(Configure::read('group_id_manager_des'));
				}
	 		}
	 		
	 		/*
	 		 * estraggo SUPER-REFERENTI-DES
	 		 */
	 		if(in_array(Configure::read('group_id_super_referent_des'), $roles)) {
				$User = new User;
				
				$conditions = array('UserGroup.id' => Configure::read('group_id_super_referent_des'));
				$usersResults = $User->getUsers($tmp_user, $conditions);
				foreach($usersResults as $usersResult) {					
					if(!isset($results[$usersResult['User']['id']])) {
						$results[$usersResult['User']['id']]['User'] = $usersResult['User'];
						$results[$usersResult['User']['id']]['User']['Group'] = [Configure::read('group_id_super_referent_des')]; 
					}
					else 
						array_push($results[$usersResult['User']['id']]['User']['Group'], Configure::read('group_id_super_referent_des'));														
				}
	 		}
	 		
	 		/*
	 		 * estraggo REFERENT-DES di un des_supplier 
	 		 */
	 		if(!empty($des_supplier_id) && in_array(Configure::read('group_id_referent_des'), $roles)) {
	 		
				$DesSuppliersReferent = new DesSuppliersReferent;
				$DesSuppliersReferent->unbindModel(['belongsTo' => ['De', 'DesSupplier']]);

				$options = [];
				$options['conditions'] = ['DesSuppliersReferent.des_id' => $user->des_id,
										   'DesSuppliersReferent.organization_id' => $tmp_user->organization['Organization']['id'],
										   'DesSuppliersReferent.des_supplier_id' => $des_supplier_id,
										   'DesSuppliersReferent.group_id' => Configure::read('group_id_referent_des')];
				$options['recursive'] = 1;
				$options['order_by'] = ['User.name'];
				$desSuppliersReferentResults = $DesSuppliersReferent->find('all', $options);
				
		 		if($debug) debug("DesSuppliersReferent::getUsersRoles() => group_id_referent_des");
		 		if($debug) debug($options);
		 		if($debug) debug($desSuppliersReferentResults);
					
				foreach($desSuppliersReferentResults as $desSuppliersReferentResult) {		

					if(!isset($results[$desSuppliersReferentResult['User']['id']])) {
						$results[$desSuppliersReferentResult['User']['id']]['User'] = $desSuppliersReferentResult['User'];
						$results[$desSuppliersReferentResult['User']['id']]['User']['Group'] = [Configure::read('group_id_referent_des')];
					}
					else 
						array_push($results[$desSuppliersReferentResult['User']['id']]['User']['Group'], Configure::read('group_id_referent_des'));	
				}	
			}	
						 		
	 		/*
	 		 * estraggo TITOTALE-DES di un des_supplier 
	 		 */
	 		if(!empty($des_supplier_id) && in_array(Configure::read('group_id_titolare_des_supplier'), $roles)) {
	 		
				$DesSuppliersReferent = new DesSuppliersReferent;
				$DesSuppliersReferent->unbindModel(['belongsTo' => ['De', 'DesSupplier']]);

				$options = [];
				$options['conditions'] = ['DesSuppliersReferent.des_id' => $user->des_id,
										   'DesSuppliersReferent.organization_id' => $tmp_user->organization['Organization']['id'],
										   'DesSuppliersReferent.des_supplier_id' => $des_supplier_id,
										   'DesSuppliersReferent.group_id' => Configure::read('group_id_titolare_des_supplier')];
				$options['recursive'] = 1;
				$options['order_by'] = ['User.name'];
				$desSuppliersReferentResults = $DesSuppliersReferent->find('all', $options);
				
		 		if($debug) debug("DesSuppliersReferent::getUsersRoles() => group_id_titolare_des_supplier");
		 		if($debug) debug($options);
		 		if($debug) debug($desSuppliersReferentResults);
		 		
				foreach($desSuppliersReferentResults as $desSuppliersReferentResult) {					
					if(!isset($results[$desSuppliersReferentResult['User']['id']])) {
						$results[$desSuppliersReferentResult['User']['id']]['User'] = $desSuppliersReferentResult['User'];
						$results[$desSuppliersReferentResult['User']['id']]['User']['Group'] = array(Configure::read('group_id_titolare_des_supplier')); 
					}
					else 
						array_push($results[$desSuppliersReferentResult['User']['id']]['User']['Group'], Configure::read('group_id_titolare_des_supplier'));													
				}
			}	

	 		/*
	 		 * estraggo REFERENT-DES-ALL_GAS di un group_id_des_supplier_all_gas 
	 		 */
	 		if(!empty($des_supplier_id) && in_array(Configure::read('group_id_des_supplier_all_gas'), $roles)) {
	 		
				$DesSuppliersReferent = new DesSuppliersReferent;
				$DesSuppliersReferent->unbindModel(array('belongsTo' => array('De', 'DesSupplier')));

				$options = [];
				$options['conditions'] = ['DesSuppliersReferent.des_id' => $user->des_id,
										   'DesSuppliersReferent.organization_id' => $tmp_user->organization['Organization']['id'],
										   'DesSuppliersReferent.des_supplier_id' => $des_supplier_id,
										   'DesSuppliersReferent.group_id' => Configure::read('group_id_des_supplier_all_gas')];
				$options['recursive'] = 1;
				$options['order_by'] = ['User.name'];
				$desSuppliersReferentResults = $DesSuppliersReferent->find('all', $options);
				
		 		if($debug) debug("DesSuppliersReferent::getUsersRoles() => group_id_des_supplier_all_gas");
		 		if($debug) debug($options);
				if($debug) debug($desSuppliersReferentResults);
		 		
				foreach($desSuppliersReferentResults as $desSuppliersReferentResult) {					
					if(!isset($results[$desSuppliersReferentResult['User']['id']])) {
						$results[$desSuppliersReferentResult['User']['id']]['User'] = $desSuppliersReferentResult['User'];
						$results[$desSuppliersReferentResult['User']['id']]['User']['Group'] = [Configure::read('group_id_des_supplier_all_gas')]; 
					}
					else 
						array_push($results[$desSuppliersReferentResult['User']['id']]['User']['Group'], Configure::read('group_id_des_supplier_all_gas'));													
				}
			}	
	 		  		 
 			if($debug) debug("DesSuppliersReferent::getUsersRoles()");
 			if($debug) debug($results);
	 		
	 		return $results;
	 }
	 
	/*
	 * estrae tutti i produttori del titolare del produttore
	 *	 solo lui puo' aprire un DesOder
	 */
	public function getDesSuppliersTitolare($user, $debug=false) {

		$ACLDesSuppliersResults = [];
		
		App::import('Model', 'DesSuppliersReferent');
		$DesSuppliersReferent = new DesSuppliersReferent;

		App::import('Model', 'Supplier');

		$DesSuppliersReferent->unbindModel(['belongsTo' => ['De', 'User']]);
		
		$options = [];
		$options['conditions'] = ['DesSuppliersReferent.des_id' => $user->des_id,
								   'DesSuppliersReferent.organization_id' => $user->organization['Organization']['id'],
								   'DesSuppliersReferent.user_id' => $user->get('id'),
								   'DesSuppliersReferent.group_id' => Configure::read('group_id_titolare_des_supplier'),
								   'DesSupplier.des_id' => $user->des_id,
								   'DesSupplier.own_organization_id' => $user->organization['Organization']['id']];
		$options['recursive'] = 1;
		$ACLDesSuppliersResults = $DesSuppliersReferent->find('all', $options);

		// debug($options['conditions']);
		$i=0;
		foreach($ACLDesSuppliersResults as $ACLDesSuppliersResult) {
			
			$supplier_id = $ACLDesSuppliersResult['DesSupplier']['supplier_id'];
			
			$Supplier = new Supplier;
					
			$options = [];
			$options['conditions'] = ['Supplier.id' => $supplier_id,
									  "(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')"];
			$options['order'] = ['Supplier.name'];
			$options['recursive'] = -1;
			$suppliersResults = $Supplier->find('first', $options);		
			if(!empty($suppliersResults)) {
				$ACLDesSuppliersResults[$i]['Supplier'] = $suppliersResults['Supplier'];
				$i++; 
			}
			
		}
		
		if($debug) {
			echo "<pre>DesSuppliersReferent::getDesSuppliersTitolare() ";
			print_r($ACLDesSuppliersResults);
			echo "</pre>";		
		}
		
		return $ACLDesSuppliersResults;
	}
		 
	/*
	 * ctrl se l'utente e' referente del produttore del DES
	 * 		in ACLsuppliersIdsDes ho gli des_ids del solo DES scelto 
	*/
	public function aclReferenteDesSupplier($user, $des_supplier_id) {
		if(!in_array($des_supplier_id,explode(",", $user->get('ACLsuppliersIdsDes')))) 
			return false;
		else
			return true;
	}
	
	/*
	 * ottengo gli Ids dei desProduttori del referente
	* 	1, 3, 4, 56
	*/
	public function getDesSupplierIdsByReferent($user, $user_id, $debug=false) {
	
		$options = [];
		$options['conditions'] = ['DesSuppliersReferent.des_id' => $user->des_id,
								   'DesSuppliersReferent.organization_id' => $user->organization['Organization']['id'],
								   'DesSuppliersReferent.user_id' => $user_id,
								   "DesSuppliersReferent.group_id IN (".Configure::read('group_id_referent_des').",".Configure::read('group_id_titolare_des_supplier').")"];
		$options['recursive'] = -1;
		$options['fields'] = ['DesSuppliersReferent.des_supplier_id'];
		$options['order_by'] = ['DesSuppliersReferent.id'];
		$results = $this->find('all', $options);
		
		if($debug) {
			echo "<pre>getDesSupplierIdsByReferent ";
			print_r($results);
			echo "</pre>";
		}
		
		/*
		 * converto results in una stringa 1, 3, 4, 56
		 */
		if(!empty($results)) {
			$tmp = "";
			foreach ($results as $result) 
				$tmp .= $result['DesSuppliersReferent']['des_supplier_id'].',';
			$results = substr($tmp, 0, (strlen($tmp)-1));
		}
		else
			$results = 0;

	if($debug) 
		echo "<pre>getDesSupplierIdsByReferent ".$results;
			
		return $results;		
	}
	
	/*
	 * estraggo Referenti D.E.S. di un produttore con i soli dati name, email, telefono
	 */
	public function getReferentsDesCompact($conditions, $orderBy=null, $debug=false) {
		
		$results = [];
		
		if(empty($orderBy)) $orderBy = Configure::read('orderUser');
		
		// in profile.phone elimino i ""
		$sql = "SELECT 
					User.organization_id, User.id, User.username, User.name, User.email, 
					DesSuppliersReferent.group_id, 
					Supplier.name, Supplier.img1,
					Organization.id, Organization.name, Organization.img1   
				FROM 
					".Configure::read('DB.portalPrefix')."users User,
					".Configure::read('DB.prefix')."suppliers Supplier, 
					".Configure::read('DB.prefix')."des_suppliers_referents DesSuppliersReferent,
					".Configure::read('DB.prefix')."des_suppliers DesSupplier,
					".Configure::read('DB.prefix')."organizations Organization
				WHERE 
				    DesSupplier.id = DesSuppliersReferent.des_supplier_id
					and DesSupplier.supplier_id = Supplier.id 
					and DesSuppliersReferent.user_id = User.id 
					and User.organization_id = Organization.id ";
		/*
		 * filtro per i solo referenti del proprio GAS => referenteDes
		 * NON filtro per i solo referenti del proprio GAS => referenteTitolareDes  
		 */
		if(isset($conditions['DesSuppliersReferent.organization_id'])) $sql .= " and DesSuppliersReferent.organization_id = ".$conditions['DesSuppliersReferent.organization_id'];
		
		if(isset($conditions['DesSupplier.des_id'])) $sql .= " and DesSupplier.des_id = ".$conditions['DesSupplier.des_id'];
		if(isset($conditions['DesSupplier.supplier_id'])) $sql .= " and DesSupplier.supplier_id = ".$conditions['DesSupplier.supplier_id']; 			
		if(isset($conditions['DesSupplier.own_organization_id'])) $sql .= " and DesSupplier.own_organization_id = ".$conditions['DesSupplier.own_organization_id']; 			
		if(isset($conditions['User.block'])) $sql .= " and User.block = ".$conditions['User.block'];  // 0 attivo
		if(isset($conditions['DesSupplier.id'])) $sql .= " and DesSupplier.id = ".$conditions['DesSupplier.id'];  // filtro per produttore
		if(isset($conditions['DesSuppliersReferent.group_id'])) $sql .= " and DesSuppliersReferent.group_id = ".$conditions['DesSuppliersReferent.group_id'];  // filtro per gruppo
		$sql .= " ORDER BY ".$orderBy;
		self::d($sql, $debug);
		try {
			$results = $this->query($sql);

			App::import('Model', 'UserGroup');
				
			jimport( 'joomla.user.helper' );
			
			foreach($results as $numResult => $result) {
				
				/*
				 * userprofile
				*/
				if($modalita != 'CRON') {
					$result['User']['id'];
					$userTmp = JFactory::getUser($result['User']['id']);
					$userProfile = JUserHelper::getProfile($userTmp->id);
					$results[$numResult]['Profile'] = $userProfile->profile;
				}
								
				/*
				 * ruolo
				*/
				$UserGroup = new UserGroup;
					
				$options = [];
				$options['conditions'] = ['UserGroup.id' => $result['DesSuppliersReferent']['group_id']];
				$options['recursive'] = -1;
				$userGroupResults = $UserGroup->find('first', $options);				
				$group_name = $userGroupResults['UserGroup']['title'];
				
				$results[$numResult]['DesSuppliersReferent']['UserGroups']['name'] = $group_name;
				$results[$numResult]['DesSuppliersReferent']['UserGroups']['descri'] = $this->userGroups[$userGroupResults['UserGroup']['id']]['descri'];
			}
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}

		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		
		return $results;
	}
	
	/*
	 * $data['DesSuppliersReferent']['des_supplier_id']
	*  $data['DesSuppliersReferent']['user_id']
	*  $data['DesSuppliersReferent']['group_id']
	*/
	public function insert($user, $data, $debug) {
	
		$continue = true;
	
		$user_id = $data['DesSuppliersReferent']['user_id'];
		$group_id = $data['DesSuppliersReferent']['group_id'];
		$des_supplier_id = $data['DesSuppliersReferent']['des_supplier_id'];
	
		/*
		 * ctrl che esisti gia' l'associazione utente / produttore
		*/
		if(!$this->exists($user->des_id, $user->organization['Organization']['id'], $user_id, $group_id, $des_supplier_id)) {
	
			/*
			 *  ctrl se e' gia' referent,
			*  se NO lo e' associo lo joomla.users al gruppo referenti in joomla.user_usergroup_map
			*/
			$options = [];
			$options['conditions'] = ['DesSuppliersReferent.organization_id' => $user->organization['Organization']['id'],
										   'DesSuppliersReferent.user_id' => $user_id,
										   'DesSuppliersReferent.group_id' => $group_id];
			$totRows = $this->find('count', $options);
			if($debug) {
				echo '<h3>Ctrl in DesSuppliersReferent se associalo al gruppo Joomla con id '.$group_id.'</h3>';
				echo '<h4>Eseguo la query</h4>';
				debug($options);
				echo "<h3>Risultato $totRows (se 0 lo inserisco nel gruppo di joomla)</h3>";
			}
			
			if($totRows==0) {
				App::import('Model', 'User');
				$User = new User;
	
				$User->joomlaBatchUser($group_id, $user_id, 'add', $debug);
			}	

			$data['DesSuppliersReferent']['organization_id'] = $user->organization['Organization']['id'];
			$data['DesSuppliersReferent']['des_id'] = $user->des_id;
	
			/*
			 * richiamo la validazione
			*/
			$this->set($data);
			if(!$this->validates()) {
				$errors = $this->validationErrors;
	
				if($debug) {
					echo "<pre>";
					print_r($errors);
					echo "</pre>";
				}
	
				$continue = false;
			}
				
			$this->create();
			if($debug) {
				echo "<pre>DesSuppliersReferent->save() ";
				print_r($data);
				echo "</pre>";
			}
			if ($this->save($data))
				$continue = true;
			else
				$continue = false;
		}
	
		return $continue;
	}
	
	public $validate = array(
		'des_id' => array(
				'rule' => array('naturalNumber', false),
				'message' => 'Scegli il D.E.S.',
		),
		'des_supplier_id' => array(
				'rule' => array('naturalNumber', false),
				'message' => 'Scegli il produttore da associare all\'utente',
		),
		'user_id' => array(
				'rule' => array('naturalNumber', false),
				'message' => 'Scegli il produttore da associare al produttore',
		),
		'group_id' => array(
				'rule' => array('naturalNumber', false),
				'message' => 'Scegli il gruppo da associare all\'utente',
		),
		'organization_id' => array(
				'rule' => array('naturalNumber', false),
				'message' => 'Indica il GAS dell\'utente',
		)
	);

	public $belongsTo = array(
		'De' => array(
			'className' => 'De',
			'foreignKey' => 'des_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'DesSupplier' => array(
			'className' => 'DesSupplier',
			'foreignKey' => 'des_supplier_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = DesSuppliersReferent.organization_id',
			'fields' => '',
			'order' => ''
		)
	);
}