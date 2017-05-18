<?php
App::uses('AppModel', 'Model');

class ProdGasSupplier extends AppModel {

    public $useTable = 'suppliers';
	
	/*
	 * dato un produttore estraggo il suppliers_organization del GAS
	 */
	public function getSuppliersOrganization($user, $organization_id, $debug=false) {

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;	

		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $organization_id,
									   'SuppliersOrganization.supplier_id' => $user->supplier['Supplier']['id'],
									   'SuppliersOrganization.stato' => 'Y');
		$options['recursive'] = -1;
		$results = $SuppliersOrganization->find('first', $options);

		if($debug) {
			echo "<pre>ProdGasSupplier::getSuppliersOrganization \n";
			print_r($options['conditions']);
			print_r($results);
			echo "</pre>";			
		}
		
		return $results;
	}
	
	/*
	 * estrae il SINGOLO GAS di un produttore
	 * con $prod_gas_promotion_id estraggo il GAS inseriti nella promozione (ProdGasPromotionsOrganization)
	 */
	public function getOrganizationAssociate($user, $organization_id, $prod_gas_promotion_id=0, $debug=false) {

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$SuppliersOrganization->unbindModel(array('belongsTo' => array('Supplier', 'CategoriesSupplier')));
		$SuppliersOrganization->unbindModel(array('hasMany' => array('Article', 'Order', 'SuppliersOrganizationsReferent')));
		
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $organization_id,
									   'SuppliersOrganization.supplier_id' => $user->supplier['Supplier']['id'],
									   'SuppliersOrganization.stato' => 'Y');
		$options['order'] = array('SuppliersOrganization.name');
		$options['recursive'] = 1;
		$results = $SuppliersOrganization->find('first', $options);
		
		/* 
		 * ProdGasPromotionsOrganization per spese trasporto, costi aggiuntivi + Order
		 */
		if($prod_gas_promotion_id>0 && !empty($results)) {
			App::import('Model', 'ProdGasPromotionsOrganization');


			$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;

			$options = array();
			$options['conditions'] = array('ProdGasPromotionsOrganization.supplier_id' => $user->supplier['Supplier']['id'],
										   'ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id,
											'ProdGasPromotionsOrganization.organization_id' => $result['SuppliersOrganization']['organization_id']);
			$options['recursive'] = -1;
			$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('first', $options);

			if($debug) {
				echo "<pre>ProdGasPromotion->getProdGasPromotion() dati del GAS \n";
				print_r($prodGasPromotionsOrganizationResults);
				echo "</pre>";
			}
			
			if(!empty($prodGasPromotionsOrganizationResults)) 
				$results['ProdGasPromotionsOrganization'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization'];


		}
		
		if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";	
		}							
		
		return $results;
	}
	
	/*
	 * estrae tutti i GAS di un produttore
	 * con $prod_gas_promotion_id estraggo i GAS inseriti nella promozione (ProdGasPromotionsOrganization)
	 */
	public function getOrganizationsAssociate($user, $prod_gas_promotion_id=0, $debug=false) {

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$SuppliersOrganization->unbindModel(array('belongsTo' => array('Supplier', 'CategoriesSupplier')));
		$SuppliersOrganization->unbindModel(array('hasMany' => array('Article', 'Order', 'SuppliersOrganizationsReferent')));
		
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.supplier_id' => $user->supplier['Supplier']['id'],
									   'SuppliersOrganization.stato' => 'Y');
		$options['order'] = array('SuppliersOrganization.name');
		$options['recursive'] = 1;
		$results = $SuppliersOrganization->find('all', $options);
		
		/* 
		 * ProdGasPromotionsOrganization per spese trasporto, costi aggiuntivi + Order
		 */
		if($prod_gas_promotion_id>0 && !empty($results)) {
			App::import('Model', 'ProdGasPromotionsOrganization');

			foreach($results as $numResult => $result) {
				$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;

				$options = array();
				$options['conditions'] = array('ProdGasPromotionsOrganization.supplier_id' => $user->supplier['Supplier']['id'],
											   'ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id,
												'ProdGasPromotionsOrganization.organization_id' => $result['SuppliersOrganization']['organization_id']);
				$options['recursive'] = -1;
				$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('first', $options);

				if($debug) {
					echo "<br /> Tratto ".$result['SuppliersOrganization']['name'].' ('.$result['SuppliersOrganization']['id'].') per il GAS '.$result['SuppliersOrganization']['organization_id'];
					echo "<pre>ProdGasPromotion->getProdGasPromotion() dati del GAS \n";
					print_r($options['conditions']);
					print_r($prodGasPromotionsOrganizationResults);
					echo "</pre>";
				}
				
				if(!empty($prodGasPromotionsOrganizationResults)) 
					$results[$numResult]['ProdGasPromotionsOrganization'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization'];
			}			


		}
		
		if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";	
		}							
		
		return $results;
	}
	
	/*
	 * estrae tutti i GAS non assocati di un produttore
	 */
	public function getOrganizationsNotAssociate($user, $debug=false) {
		
		$organization_ids = '';
		
		/*
		 * estraggo i organization_id dei GAS associati
		 */
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.supplier_id' => $user->supplier['Supplier']['id'],
									   'SuppliersOrganization.stato' => 'Y');
		$options['fields'] = array('SuppliersOrganization.organization_id');
		$options['order'] = array('SuppliersOrganization.id');
		$options['recursive'] = -1;
		$results = $SuppliersOrganization->find('all', $options);
		
		/*
		 * ids da escludere
		 */
		
		if(!empty($results)) {
			foreach($results as $result) {
				$organization_id = $result['SuppliersOrganization']['organization_id'];
				$organization_ids .= $organization_id.',';
			}
			
			if(!empty($organization_ids)) {
				$organization_ids = substr($organization_ids, 0, strlen($organization_ids)-1);

				/*
				 * estraggo i GAS non associati
				 */
				App::import('Model', 'Organization');
				$Organization = new Organization;
				
				$options = array();
				$options['conditions'] = array('Organization.type' => 'GAS');
				if(!empty($organization_ids))							   
					$options['conditions'] += array("NOT" => array( "Organization.id" => split(',', $organization_ids)));												
				$options['order'] = array('Organization.name');
				$options['recursive'] = -1;
				$results = $Organization->find('all', $options);
				
			} // if(!empty($organization_ids))
			
		} // end if(!empty($results))
		
		if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";	
		}							
		
		return $results;	
	}
	
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'category_supplier_id' => array(
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