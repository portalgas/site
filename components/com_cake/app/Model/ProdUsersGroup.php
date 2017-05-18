<?php
App::uses('AppModel', 'Model');

class ProdUsersGroup extends AppModel {

	/*
	 * estrae la LISTA utenti ancora da associare
	 * */
	public function getUsersListToAssocite($user, $prod_group_id, $debug=false) {
		
		$results = $this->__getUsersAssocite($user, $prod_group_id, $debug);
		
		$resultsNew = array();
		foreach ($results as $result) 
			$resultsNew[$result['User']['id']] = $this->getUserLabel($result);
		
	
		
		return $resultsNew;
	}	
	
	private function __getUsersAssocite($user, $prod_group_id, $debug) {
	
		App::import('Model', 'User');
		$User = new User;
	
		/*
		 * estraggo gli utenti gia' associati
		*/
		$options['conditions'] = array('ProdUsersGroup.organization_id' => $user->organization['Organization']['id'],
									   'ProdUsersGroup.prod_group_id' => $prod_group_id);
		$options['recursive'] = -1;
		$options['fields'] = 'ProdUsersGroup.user_id';
		$options['order'] =  'ProdUsersGroup.user_id';
		$results = $this->find('all', $options);
	
		if($debug) {
			echo "<pre>Utenti gia' associati ";
			//print_r($users);
			echo '<br />trovati '.count($results);
			echo "</pre>";
		}
	
		/*
		 *  estraggo gli user_id per escluderli dopo
		*/
		$user_ids = "";
		if(!empty($results)) {
			foreach ($results as $result)
				$user_ids .= $result['ProdUsersGroup']['user_id'].',';
			$user_ids = substr($user_ids, 0, (strlen($user_ids)-1));
		}
	
		if($debug)  echo '<br />'.$user_ids;
	
		/*
		 * estraggo tutti gli utenti da associare
		*/
		$sql = "SELECT
					User.id, User.name, User.username, User.email
				FROM
					".Configure::read('DB.portalPrefix')."users User,
					".Configure::read('DB.portalPrefix')."user_usergroup_map UserGroup,
					".Configure::read('DB.portalPrefix')."usergroups AS `Group`
				WHERE
					User.organization_id = ".(int)$user->organization['Organization']['id']."
					AND UserGroup.user_id = User.id
					AND UserGroup.group_id = `Group`.id 
					AND User.block = 0 
					AND UserGroup.group_id = ".Configure::read('group_id_user')." ";
		if(!empty($user_ids)) $sql .= "AND ('User.id not IN ('.$user_ids.')') ";
		$sql .= " ORDER BY ".Configure::read('orderUser');
		// echo '<br />'.$sql;
		try {
			$users = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
		
		if($debug) {
			echo "<pre>Utenti da associare ";
			print_r($options);
			print_r($users);
			echo '<br />trovati '.count($users);
			echo "</pre>";
		}
		
		return $users;
	}
	
	public function getUserLabel($result) {
	
		$userTmp = JFactory::getUser($result['User']['id']);
		$userProfile = JUserHelper::getProfile($userTmp->id);
		
		$tmp = "";
		if(!empty($userProfile->profile['codice'])) $tmp .= $userProfile->profile['codice'].' ';
		$tmp .= $result['User']['name'].' - ';
		if(!empty($userProfile->profile['address'])) $tmp .= $userProfile->profile['address'].' ';
		if(!empty($userProfile->profile['city'])) $tmp .= $userProfile->profile['city'].' ';
	
		return $tmp;	
	}
	
	public function getMaxSort($user, $prod_group_id) {
		
		$maxSort = 0;
		 
		$options['fields'] = array('MAX(sort)+1 AS maxSort');
		$options['conditions'] = array('organization_id' => $user->organization['Organization']['id'],
									  'prod_group_id' => $prod_group_id);
		$options['recursive'] = -1;
		$results = $this->find('first', $options);
		if(!empty($results)) {
			$results = current($results);
			$maxSort = $results['maxSort'];
			if(empty($maxSort)) $maxSort = 0;
		}
		
		return $maxSort;		
	}
	
	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'prod_group_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'sort' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'ProdGroup' => array(
			'className' => 'ProdGroup',
			'foreignKey' => 'prod_group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}