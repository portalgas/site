<?php
App::uses('AppModel', 'Model');
App::import('Model', 'SuppliersOrganizationsReferentMultiKey');

/**
 * SuppliersOrganizationsReferent Model
 *
 * @property Supplier $Supplier
 * @property User $User
 */
class SuppliersOrganizationsReferent extends SuppliersOrganizationsReferentMultiKey {
	
	/*
	 * ctrl se l'utente e' referente del produttore
	*/
	public function aclReferenteSupplierOrganization($user, $supplier_organization_id) {
		if(!in_array($supplier_organization_id, explode(",", $user->get('ACLsuppliersIdsOrganization')))) 
			return false;
		else
			return true;
	}

	/*
	 * ottengo i produttori del referente
	*/
	public function getSuppliersOrganizationByReferent($user, $user_id) {
	
		$result = [];
	
		App::import('Model', 'SuppliersOrganizationsReferent');
		$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
	
		$options['conditions'] = ['SuppliersOrganizationsReferent.organization_id' => $user->organization['Organization']['id'],
								  'SuppliersOrganizationsReferent.user_id' => $user_id];
		$options['recursive'] = 1;
		$results = $SuppliersOrganizationsReferent->find('all', $options);

		App::import('Model', 'Supplier');
		foreach ($results as $i => $result) {

			$Supplier = new Supplier;		
			
			$options['conditions'] = ['Supplier.id' => $result['SuppliersOrganizationsReferent']['supplier_organization_id']];
			$options['recursive'] = -1;
			$resultsSuppliers = $Supplier->find('first', $options);
				
			$results[$i]['Supplier'] = $resultsSuppliers['Supplier'];
		}

		return $results;
	}
	
	/*
	 * ottengo gli Ids dei produttori del referente
	* 	1, 3, 4, 56
	*/
	public function getSuppliersOrganizationIdsByReferent($user, $user_id) {
	
		$options = [];
		$options['conditions'] = ['SuppliersOrganizationsReferent.organization_id' => $user->organization['Organization']['id'],
								  'SuppliersOrganizationsReferent.user_id' => $user_id];
		$options['recursive'] = -1;
		$options['fields'] = ['SuppliersOrganizationsReferent.supplier_organization_id'];
		$options['order_by'] = ['SuppliersOrganizationsReferent.id'];
		$results = $this->find('all', $options);
		
		/*
		 * converto results in una stringa 1, 3, 4, 56
		 */
		if(!empty($results)) {
			$tmp = "";
			foreach ($results as $result) 
				$tmp .= $result['SuppliersOrganizationsReferent']['supplier_organization_id'].',';
			$results = substr($tmp, 0, (strlen($tmp)-1));
		}
		else
			$results = 0;
		
		return $results;		
	}
	
	/*
	 * estraggo Referenti di un produttore con i soli dati name, email, telefono, type
	 * 
	 *  $modalita = CRON se metodo richiamato da 
	 * 		UtilsCrons::mailReferentiQtaMax() 
	 * 		UtilsCrons::mailUsersOrdersOpen()
	 * 	    UtilsCrons::mailUsersOrdersClose()
	 * perche' esclude jimport()
	 */
	public function getReferentsCompact($user, $conditions, $orderBy=null, $modalita='') {
		
		$results = [];
		
		if(empty($orderBy)) $orderBy = Configure::read('orderUser');

		$sql = "SELECT 
					User.organization_id, User.id, User.username, User.name, User.email, UserProfile.profile_value as email,  UserProfile2.profile_value as satispay,
					SuppliersOrganizationsReferent.type, SuppliersOrganizationsReferent.group_id, 
					SuppliersOrganization.name, SuppliersOrganization.frequenza  
				FROM 
					".Configure::read('DB.portalPrefix')."users User LEFT JOIN ".Configure::read('DB.portalPrefix')."user_profiles UserProfile ON 
					(UserProfile.user_id = User.id and UserProfile.profile_key = 'profile.email')
						LEFT JOIN ".Configure::read('DB.portalPrefix')."user_profiles UserProfile2 ON 
					(UserProfile2.user_id = User.id and UserProfile2.profile_key = 'profile.satispay'),  
					".Configure::read('DB.prefix')."suppliers_organizations_referents SuppliersOrganizationsReferent,
					".Configure::read('DB.prefix')."suppliers_organizations SuppliersOrganization
				WHERE 
					User.organization_id = ".(int)$user->organization['Organization']['id']." 
					and SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
					and SuppliersOrganizationsReferent.organization_id =  ".(int)$user->organization['Organization']['id']."
					and SuppliersOrganizationsReferent.user_id = User.id
					and SuppliersOrganizationsReferent.supplier_organization_id = SuppliersOrganization.id ";
		if(isset($conditions['User.block'])) $sql .= " and User.block = ".$conditions['User.block'];  // 0 attivo
		if(isset($conditions['SuppliersOrganization.id'])) $sql .= " and SuppliersOrganization.id = ".$conditions['SuppliersOrganization.id'];  // filtro per produttore
		if(isset($conditions['SuppliersOrganizationsReferent.group_id'])) $sql .= " and SuppliersOrganizationsReferent.group_id = ".$conditions['SuppliersOrganizationsReferent.group_id'];  // filtro per gruppo
		if(isset($conditions['SuppliersOrganizationsReferent.type'])) $sql .= " and SuppliersOrganizationsReferent.type = '".$conditions['SuppliersOrganizationsReferent.type']."'"; 
		$sql .= " ORDER BY ".$orderBy;
		self::d($sql, false);
		try {
			$results = $this->query($sql);

			App::import('Model', 'UserGroup');
				
			//if($modalita != 'CRON') jimport( 'joomla.user.helper' );
			if($modalita != 'CRON')
				require_once(Configure::read('App.root').'/libraries/joomla/user/helper.php');

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
				$options['conditions'] = ['UserGroup.id' => $result['SuppliersOrganizationsReferent']['group_id']];
				$options['recursive'] = -1;
				$userGroupResults = $UserGroup->find('first', $options);				
				$group_name = $userGroupResults['UserGroup']['title'];
				
				$results[$numResult]['SuppliersOrganizationsReferent']['UserGroups']['name'] = $group_name;
				$results[$numResult]['SuppliersOrganizationsReferent']['UserGroups']['descri'] = $this->userGroups[$userGroupResults['UserGroup']['id']]['descri'];
			}
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}

		self::d($results, $debug);
		
		return $results;
	}

	/*
	 * $data['SuppliersOrganizationsReferent']['supplier_organization_id']
	 * $data['SuppliersOrganizationsReferent']['type'] = 'REFERENTE', 'COREFERENTE'
	*  $data['SuppliersOrganizationsReferent']['user_id']
	*  $data['SuppliersOrganizationsReferent']['group_id']
	*/
	public function insert($user, $data, $debug) {
	
		$continue = true;
	
		$user_id = $data['SuppliersOrganizationsReferent']['user_id'];
		$group_id = $data['SuppliersOrganizationsReferent']['group_id'];
		$supplier_organization_id = $data['SuppliersOrganizationsReferent']['supplier_organization_id'];
		$type = $data['SuppliersOrganizationsReferent']['type'];
	
		/*
		 * ctrl che esisti gia' l'associazione utente / produttore
		*/
		if(!$this->exists($user->organization['Organization']['id'], $user_id, $group_id, $supplier_organization_id, $type)) {
	
			/*
			 *  ctrl se e' gia' referent,
			*  se NO lo e' associo lo joomla.users al gruppo referenti in joomla.user_usergroup_map
			*/
			$options = [];
			$options['conditions'] = ['SuppliersOrganizationsReferent.organization_id' => $user->organization['Organization']['id'],
									   'SuppliersOrganizationsReferent.user_id' => $user_id,
									   'SuppliersOrganizationsReferent.group_id' => $group_id];
			$totRows = $this->find('count', $options);
			if($debug) {
				echo '<h3>Ctrl in SuppliersOrganizationsReferents se associalo al gruppo Joomla con id '.$group_id.'</h3>';
				echo '<h4>Eseguo la query</h4>';
				echo "<pre>";
				print_r($options);
				echo "</pre>";
				echo "<h3>Risultato $totRows (se 0 lo inserisco nel gruppo di joomla)</h3>";
			}
			
			if($totRows==0) {
				App::import('Model', 'User');
				$User = new User;
	
				$User->joomlaBatchUser($group_id, $user_id, 'add', $debug);
			}	

			$data['SuppliersOrganizationsReferent']['organization_id'] = $user->organization['Organization']['id'];
	
			/*
			 * richiamo la validazione
			*/
			$this->set($data);
			if(!$this->validates()) {
				$errors = $this->validationErrors;
	
				self::d($errors, $debug);
	
				$continue = false;
			}
				
			$this->create();
			self::d($data, $debug); 
			
			if ($this->save($data))
				$continue = true;
			else
				$continue = false;
		}
	
		return $continue;
	}
	
	public $validate = [
		'supplier_organization_id' => [
				'rule' => ['naturalNumber', false],
				'message' => 'Scegli il produttore da associare all\'utente',
		],
		'user_id' => [
				'rule' => ['naturalNumber', false],
				'message' => 'Scegli il produttore da associare al produttore',
		],
		'type' => [ // non funge il msg di errore perche' radio custom!
				'rule'    => ['inList', ['REFERENTE', 'COREFERENTE']],
				'message' => 'Indica la tipologia del referente',
		],
	];

	public $belongsTo = [
		'SuppliersOrganization' => [
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = SuppliersOrganizationsReferent.organization_id',
			'fields' => '',
			'order' => ''
		],
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = SuppliersOrganizationsReferent.organization_id',
			'fields' => '',
			'order' => ''
		]
	];
}