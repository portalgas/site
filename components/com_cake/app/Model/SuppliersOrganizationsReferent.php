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
		if(!in_array($supplier_organization_id,explode(",", $user->get('ACLsuppliersIdsOrganization')))) 
			return false;
		else
			return true;
	}

	/*

		App::import('Model', 'Supplier');

			$Supplier = new Supplier;		
			
			$options['conditions'] = array('Supplier.id' => $result['SuppliersOrganizationsReferent']['supplier_organization_id']);
			$results[$i]['Supplier'] = $resultsSuppliers['Supplier'];
		}

		return $results;
	/*
		$options = array();
									   'SuppliersOrganizationsReferent.user_id' => $user_id);
		$options['fields'] = array('supplier_organization_id');
		$options['order_by'] = array('id');
		$results = $this->find('all', $options);
		
		/*
		 * converto results in una stringa 1, 3, 4, 56
		 */
		if(!empty($results)) {
				$tmp .= $result['SuppliersOrganizationsReferent']['supplier_organization_id'].',';
			$results = substr($tmp, 0, (strlen($tmp)-1));
	/*
	 * estraggo Referenti di un produttore con i soli dati name, email, telefono, type
	 * 
	 *  $modalita = CRON se metodo richiamato da UtilsCrons::mailReferentiQtaMax() perche' esclude jimport()
	 */
	public function getReferentsCompact($user, $conditions, $orderBy=null, $modalita='') {
		
		$results = array();
		
		if(empty($orderBy)) $orderBy = Configure::read('orderUser');
		
		// in profile.phone elimino i ""
		$sql = "SELECT 
					User.organization_id, User.id, User.username, User.name, User.email, 
					SuppliersOrganizationsReferent.type, SuppliersOrganizationsReferent.group_id, 
					SuppliersOrganization.name, SuppliersOrganization.frequenza  
				FROM 
					".Configure::read('DB.portalPrefix')."users User,
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
		// echo '<br />'.$sql;

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
				}
								
				/*
				 * ruolo
				*/
				$UserGroup = new UserGroup;
					
				$options = array();
				$options['conditions'] = array('UserGroup.id' => $result['SuppliersOrganizationsReferent']['group_id']);
				$options['recursive'] = -1;
				$userGroupResults = $UserGroup->find('first', $options);				
				$group_name = $userGroupResults['UserGroup']['title'];
				
				$results[$numResult]['SuppliersOrganizationsReferent']['UserGroups']['name'] = $group_name;
				$results[$numResult]['SuppliersOrganizationsReferent']['UserGroups']['descri'] = $this->userGroups[$userGroupResults['UserGroup']['id']]['descri'];
			}
		catch (Exception $e) {

		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		
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
			$options = array();
			$options['conditions'] = array('SuppliersOrganizationsReferent.organization_id' => $user->organization['Organization']['id'],
										   'SuppliersOrganizationsReferent.user_id' => $user_id,
										   'SuppliersOrganizationsReferent.group_id' => $group_id);
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
	
				if($debug) {
					echo "<pre>";
					print_r($errors);
					echo "</pre>";
				}
	
				$continue = false;
			}
				
			$this->create();
			if($debug) {
				echo "<pre>";
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
		'supplier_organization_id' => array(
				'rule' => array('naturalNumber', false),
		),
		'user_id' => array(
				'rule' => array('naturalNumber', false),
				'message' => 'Scegli il produttore da associare al produttore',
		),
		'type' => array( // non funge il msg di errore perche' radio custom!
				'rule'    => array('inList', array('REFERENTE', 'COREFERENTE')),
	);

	public $belongsTo = array(
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = SuppliersOrganizationsReferent.organization_id',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = SuppliersOrganizationsReferent.organization_id',
			'fields' => '',
			'order' => ''
		)
	);
}