<?php
App::uses('AppModel', 'Model');

class GasGroup extends AppModel {	
	
	public $tablePrefix = false;

	public function getsById($user, $organization_id, $gas_group_id) {

		  $options = [];
		  $options['conditions'] = ['GasGroup.organization_id' => $organization_id,
								  'GasGroup.id' => $gas_group_id];
		  $options['recursive'] = -1;
  
		  $results = $this->find('first', $options);
	
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