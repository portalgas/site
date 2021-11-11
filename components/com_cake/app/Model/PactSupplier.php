<?php
App::uses('AppModel', 'Model');

class PactSupplier extends AppModel {

    public $useTable = 'suppliers';
		
	/*
	 * dato un supplier_id estraggo l'eventuale PactSupplier
	 */
	public function getBySupplierId($user, $supplier_id, $debug=false) {
		 
		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
		
		$Supplier->unbindModel(['belongsTo' => ['CategoriesSupplier', 'SuppliersDeliveriesType', 'Organization']]);
		$Supplier->bindModel(['belongsTo' => ['Organization' => ['className' => 'Organization', 'foreignKey' => 'owner_organization_id']]]);
		
		$options = [];
		$options['conditions'] = ['Organization.type' => 'PACT', 
								  'Supplier.id' => $supplier_id];
		$options['recursive'] = 0;
		$supplierResults = $Supplier->find('first', $options);
		self::d($options, $debug);
		self::d($options, $debug);
		
		if(!empty($supplierResults)) {
			
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'], 
									  'SuppliersOrganization.supplier_id' => $supplierResults['Supplier']['id']];
			$options['recursive'] = -1;
			self::d($options, $debug);
			$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		
			$supplierResults += $suppliersOrganizationResults;
		}
		
		self::d($supplierResults, $debug);
		
		return $supplierResults;
	}			
				
	/*
	 * dato un Organization PACT estraggo il suo supplier
	 *
	 * filtersOwnerArticles SUPPLIER / REFERENT / DES non e' valorizzato prendo tutti i SuppliersOrganization.owner_articles => root
	 * il prodGas ha filtrato per $SuppliersOrganization.owner_articles = SUPPLIER  
	 */
	public function getOrganizationSupplier($user, $organization_id, $filters=[], $debug=false) {
				
		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		$SuppliersOrganization->unbindModel(['belongsTo' => ['CategoriesSupplier']]);
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $organization_id];
		$options['order'] = ['SuppliersOrganization.name'];
		$options['recursive'] = 0;
		
		$results = [];
		$results = $SuppliersOrganization->find('first', $options);
		self::d($options, $debug);
		self::d($results, $debug);
		 
		if(!empty($results)) {

			/*
			 * estraggo tutti i GAS che hanno il produttore
			 */
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.supplier_id' => $results['Supplier']['id'],
									  'SuppliersOrganization.organization_id != ' => $organization_id];
			if(isset($filters['ownerArticles']))
				$options['conditions'] += ['SuppliersOrganization.owner_articles' => $filters['ownerArticles']];
			if(isset($filters['organization_id']) && !empty($filters['organization_id'])) // estraggo solo il GAS corrente
				$options['conditions'] += ['SuppliersOrganization.organization_id' => $filters['organization_id']];
			$options['order'] = ['SuppliersOrganization.name'];
			$options['recursive'] = 0;

			$SuppliersOrganization->unbindModel(['belongsTo' => ['Supplier', 'CategoriesSupplier']]);
			$suppliersOrganizationResults = $SuppliersOrganization->find('all', $options);
			self::d($options, $debug);
			self::d($suppliersOrganizationResults, $debug);
						
			$results['Organization'] = $suppliersOrganizationResults;
				
			
		} // end if(!empty($results))
		
		self::d($results,$debug);
		
		return $results;
	}
	
	/*
	 * dato un produttore estraggo il suppliers_organization del GAS
	 */
	public function getSuppliersOrganization($user, $organization_id, $debug=false) {

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;	

		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $organization_id,
								   'SuppliersOrganization.supplier_id' => $user->organization['Supplier']['Supplier']['id'],
								   'SuppliersOrganization.stato' => 'Y'];
		$options['recursive'] = -1;
		$results = $SuppliersOrganization->find('first', $options);

		if($debug) {
			echo "<pre>PactSupplier::getSuppliersOrganization \n";
			print_r($options['conditions']);
			print_r($results);
			echo "</pre>";			
		}
		
		return $results;
	}

	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => ['notBlank']
			),
		),
		'category_supplier_id' => array(
				'numeric' => array(
						'rule' => ['numeric']
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