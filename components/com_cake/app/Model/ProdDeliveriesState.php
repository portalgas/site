<?php
App::uses('AppModel', 'Model');
/**
 * ProdDeliveriesState Model
 *
 */
class ProdDeliveriesState extends AppModel {

	public function getProdDeliveriesState() {

		$results = array();
		
		$options = array();
		$options['conditions'] = array('ProdDeliveriesState.flag_produttore' => 'Y');
		$options['order'] = array('sort');
		$options['recursive'] = -1;

			/*echo "<pre>";
		
		return $results;
	}
	
	public $hasMany = array(
		'ProdDelivery' => array(
				'className' => 'ProdDelivery',
				'foreignKey' => 'prod_delivery_state_id',
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