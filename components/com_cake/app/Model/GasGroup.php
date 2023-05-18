<?php
App::uses('AppModel', 'Model');

class GasGroup extends AppModel {	
	
	public $tablePrefix = false;

	public function getsById($user, $organization_id, $gas_group_id) {

		  $options = [];
		  $options['conditions'] = ['GasGroup.organization_id' => $organization_id,
								  'GasGroup.id' => $gas_group_id,
								  'GasGroup.is_active' => true];
		  $options['recursive'] = -1;
  
		  $results = $this->find('first', $options);
	
	    return $results;		
	}

	/* 
	 * gruppi creati dall'utente
	 */
	public function getsByUser($user, $organization_id, $user_id) {

		if($user->organization['Organization']['hasGasGroups']=='N')
		  return [];

		$options = [];
		$options['conditions'] = ['GasGroup.organization_id' => $organization_id,
								'GasGroup.user_id' => $user_id,
								'GasGroup.is_active' => true];
		$options['order'] = ['GasGroup.name'];
		$options['recursive'] = -1;

		$results = $this->find('all', $options);
	
	    return $results;		
	}

	public function getsByUserList($user, $organization_id, $user_id) {

		$results = [];

		if($user->organization['Organization']['hasGasGroups']=='N')
		  return [];

		$gas_groups = $this->getsByUser($user, $organization_id, $user_id);
		if(!empty($gas_groups))
		foreach($gas_groups as $gas_group) {
			$results[$gas_group['GasGroup']['id']] = $gas_group['GasGroup']['name'];
		}
	
	    return $results;		
	}

	public function getsByIdsUser($user, $organization_id, $user_id) {

		if($user->organization['Organization']['hasGasGroups']=='N')
		  return [];

		$ids = [];

		$results = $this->getsByUser($user, $organization_id, $user_id);
		if(!empty($results))
		foreach($results as $result) {
			$ids[$result['GasGroup']['id']] = $result['GasGroup']['id'];
		}
	
	    return $ids;		
	}

	public $validate = array(
		'user_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),		
		'name' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
	);

	public $belongsTo = array(
		'Organization' => array(
				'className' => 'Organization',
				'foreignKey' => 'organization_id',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		),
		'User' => array(
				'className' => 'User',
				'foreignKey' => 'user_id',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		)
);
}