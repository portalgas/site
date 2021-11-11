<?php
App::uses('AppModel', 'Model');


class ProdDeliveriesState extends AppModel {

	public function getProdDeliveriesState() {

		$results = [];
		
		$options = [];
		$options['conditions'] = array('ProdDeliveriesState.flag_produttore' => 'Y');
		$options['order'] = array('sort');
		$options['recursive'] = -1;		try {			$results = $this->find('all', $options); 

			/*echo "<pre>";			 print_r($results);			echo "</pre>";*/							}		catch (Exception $e) {			CakeLog::write('error',$e);		}
		
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