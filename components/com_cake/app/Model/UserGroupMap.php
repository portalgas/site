<?php
App::uses('AppModel', 'Model');

class UserGroupMap extends AppModel {

	public $actsAs = ['Containable'];
    public $virtualFields = ['id' => 'user_id'];
    
	public $useTable = 'user_usergroup_map'; 
	public $tablePrefix = 'j_';
	
	public function getTotUserByGroupId($user, $group_id, $debug=false) {
		
		$tot_users = 0;
		
		$sql = "SELECT count(User.id) as tot_users
			FROM
			".Configure::read('DB.portalPrefix')."user_usergroup_map m,
			".Configure::read('DB.portalPrefix')."usergroups g,
			".Configure::read('DB.portalPrefix')."users User
					WHERE
					m.user_id = User.id
					and m.group_id = g.id
					and m.group_id = ".$group_id."
					and User.block = 0
					and User.organization_id = ".(int)$user->organization['Organization']['id'];
		self::d($sql, $debug);
		try {
			$results = current($this->query($sql));
			$tot_users = $results[0]['tot_users'];
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
		
		return $tot_users;
	}
	
	/*
	 * elenco degli users che hanno il ruolo group_id_user_flag_privacy 
	 *	necessario solo 1
	 */
	public function getUserFlagPrivacys($user, $debug=false) {
	
		$results = '';

		$this->bindModel(['hasMany' => ['User' => ['className' => 'User', 'foreignKey' => 'id', 
												   'conditions' => 'User.organization_id = '.$user->organization['Organization']['id']]]]);
			
		$options = [];
		$options['conditions'] = ['UserGroupMap.group_id' => Configure::read('group_id_user_flag_privacy')];
		$options['recursive'] = 1;
		
		$userResults = $this->find('all', $options);
		foreach($userResults as $numResult => $userResult) {
			if(empty($userResult['User']))
				unset($userResults[$numResult]);
		}
		self::d([$options, $userResults], $debug);
		
		return $userResults;
	}

	public function getUserFlagPrivacy($user, $debug=false) {
	
		$userFlagPrivacy = [];
		
		$userFlagPrivacys = $this->getUserFlagPrivacys($user);
		
		if(!empty($userFlagPrivacys)) {
        	$userFlagPrivacy = current($userFlagPrivacys);
        	$userFlagPrivacy = $userFlagPrivacy['User'][0];
        }
        
        self::d($userFlagPrivacy, $debug);
        		
		return $userFlagPrivacy;
	}
		
	/*
	 * estrae gli user.id degli utenti associato al gruppo (GasGroups) 
	 * che l'utente come cassiere (isGasGroupsCassiere)
	 * gestisce
	 */
	public function getUserIdsByGasGroupsCassiere($user, $user_id) {
		
		$user_ids = [];
			
		$options = [];
		$options['conditions'] = ['id' => $user_id,
								'group_id' => Configure::read('group_id_gas_groups_id_cassiere')];
		$options['recursive'] = -1;
		$user_group_maps = $this->find('all', $options);
		if(!empty($user_group_maps)) {
			App::import('Model', 'GasGroupUser');
			$GasGroupUser = new GasGroupUser; 
			foreach($user_group_maps as $user_group_map) {
				$users = $GasGroupUser->getsUserByGasGroupId($user, $user->organization['Organization']['id'], $user_group_map['UserGroupMap']['gas_group_id']);
				if(!empty($users)) {
					foreach($users as $user) {
						$user_ids[$user['User']['id']] = $user['User']['id'];
					}
				}
			}
		} // end if(!empty($user_group_maps))
		// debug($user_ids); 
		return $user_ids;
	}

	public $hasMany = [
		'User' => [
			'className' => 'User',
			'foreignKey' => 'id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		]
	];
	
 	public $belongsTo = [
		'UserGroup' => [
			'className' => 'UserGroup',
			'foreignKey' => 'group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		]
	];	
}