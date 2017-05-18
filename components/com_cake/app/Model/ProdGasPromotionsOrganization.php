<?php
App::uses('AppModel', 'Model');

class ProdGasPromotionsOrganization extends AppModel {
	
	public $validate = array(
		'supplier_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
	);

	public $belongsTo = array(
		'ProdGasPromotion' => array(
			'className' => 'ProdGasPromotion',
			'foreignKey' => 'prod_gas_promotion_id',
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
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => 'Order.organization_id = ProdGasPromotionsOrganization.organization_id',
			'fields' => '',
			'order' => ''
		),
	);
	
	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {
			if(!empty($val)) {				
		
				if (isset($val['ProdGasPromotionsOrganization']['trasport'])) {
					$results[$key]['ProdGasPromotionsOrganization']['trasport_'] = number_format($val['ProdGasPromotionsOrganization']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasPromotionsOrganization']['trasport_e'] = $results[$key]['ProdGasPromotionsOrganization']['trasport_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['trasport'])) {
					$results[$key]['trasport_'] = number_format($val['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['trasport_e'] = $results[$key]['trasport_'].' &euro;';
				}
		
				if (isset($val['ProdGasPromotionsOrganization']['cost_more'])) {
					$results[$key]['ProdGasPromotionsOrganization']['cost_more_'] = number_format($val['ProdGasPromotionsOrganization']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasPromotionsOrganization']['cost_more_e'] = $results[$key]['ProdGasPromotionsOrganization']['cost_more_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['cost_more'])) {
					$results[$key]['cost_more_'] = number_format($val['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['cost_more_e'] = $results[$key]['cost_more_'].' &euro;';
				}
			}
		}
		return $results;
	}	
	
	public function beforeSave($options = array()) {
		if (!empty($this->data['ProdGasPromotionsOrganization']['cost_more']))
			$this->data['ProdGasPromotionsOrganization']['cost_more'] = $this->importoToDatabase($this->data['ProdGasPromotionsOrganization']['cost_more']);

		if (!empty($this->data['ProdGasPromotionsOrganization']['trasport']))
			$this->data['ProdGasPromotionsOrganization']['trasport'] = $this->importoToDatabase($this->data['ProdGasPromotionsOrganization']['trasport']);
		
	    return true;
	}
	
}