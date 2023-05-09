<?php
App::uses('AppModel', 'Model');

class GasGroupUser extends AppModel {	
	
	public $tablePrefix = false;

	public function getsListUserByGasGroupId($user, $organization_id, $gas_group_id) {

		$results = [];
		$users = $this->getsUserByGasGroupId($user, $organization_id, $gas_group_id);
		if(!empty($users)) {
			foreach($users as $user) {
				$results[$user['User']['id']] = $user['User']['name'];
			}
		}
		
	    return $results;		
	}

	public function getsUserByGasGroupId($user, $organization_id, $gas_group_id) {
		
		$options = [];
		$options['conditions'] = ['GasGroupUser.organization_id' => $organization_id,
							'GasGroupUser.gas_group_id' => $gas_group_id];
		$this->unbindModel(['belongsTo' => ['Organization']]);
		$options['recursive'] = 0;
		$results = $this->find('all', $options);
		
		return $results;
	}

	public function getsByUser($user, $organization_id, $user_id) {

		if($user->organization['Organization']['hasGasGroups']=='N')
		  return [];

		$options = [];
		$options['conditions'] = ['GasGroup.organization_id' => $organization_id,
								'GasGroup.user_id' => $user_id];
		$options['recursive'] = -1;

		$results = $this->find('all', $options);
	
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