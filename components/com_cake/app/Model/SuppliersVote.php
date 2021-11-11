<?php
App::uses('AppModel', 'Model');

class SuppliersVote extends AppModel {

	public function getVotoOptions() {
	
		$results = [];
		$results[0] = __('vote_0');
		$results[1] = __('vote_1');
		$results[2] = __('vote_2');
		$results[3] = __('vote_3');
		$results[4] = __('vote_4');

		return $results;
	}
	
	public function getOrganizationsVoto($supplier_id, $debug=false) {
	
		$results = [];
		
		$options = [];
		$options['conditions'] = array('SuppliersVote.supplier_id' => $supplier_id);
        $options['recursive'] = 1;
		
		$this->unbindModel(array('belongsTo' => array('Supplier')));
		$this->unbindModel(array('hasMany' => array('SuppliersOrganization')));
		$results = $this->find('all', $options);      
		
		if($debug) {
			echo "<pre>SuppliersVote::getOrganizationsVoto() supplier_id $supplier_id\n ";
			print_r($results);
			echo "</pre>";
		}
		
		return $results;
	}
	
	public $validate = array(
		'nota' => array(
			'notempty' => array(
				'rule' => ['notBlank'],
			),
		),
		'supplier_id' => array(
				'numeric' => array(
						'rule' => ['numeric'],
				),
		),
		'organization_id' => array(
				'numeric' => array(
						'rule' => ['numeric'],
				),
		),
		'user_id' => array(
				'numeric' => array(
						'rule' => ['numeric'],
				),
		),
		'voto' => array(
				'numeric' => array(
						'rule' => ['numeric'],
				),
		),
	);

	public $belongsTo = array(
		'Supplier' => array(
			'className' => 'Supplier',
			'foreignKey' => 'supplier_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
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
		),
	);
	
	public $hasMany = array(
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
}