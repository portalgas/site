<?php
App::uses('AppModel', 'Model');

class StatSuppliersOrganization extends AppModel {

	public $hasMany = array(			'StatOrder' => array(					'className' => 'StatOrder',					'foreignKey' => 'stat_supplier_organization_id',					'dependent' => false,					'conditions' => '',					'fields' => '',					'order' => '',					'limit' => '',					'offset' => '',					'exclusive' => '',					'finderQuery' => '',					'counterQuery' => ''			)	);
}