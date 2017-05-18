<?php
App::uses('AppModel', 'Model');

class DesSupplier extends AppModel {
	
	public function getOrganizationIdTitolare($user, $des_order_id, $debug = false) {

		$own_organiation_id = 0;
		
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();
	    $DesOrdersOrganization->unbindModel(array('belongsTo' => array('Organization', 'De', 'Order')));
	    
	    $options = array();
		$options['conditions'] = array('DesOrdersOrganization.organization_id' => $user->organization['Organization']['id'],
								   	   'DesOrdersOrganization.des_order_id' => (int)$des_order_id);
		if(!empty($user->des_id))
			$options['conditions'] += array('DesOrdersOrganization.des_id' => $user->des_id);	    								   	   
		$options['recursive'] = 2;
		$results = $DesOrdersOrganization->find('first', $options);
		
 	    if($debug) {
			echo "<pre>DesSupplier->getOrganizationIdTitolare()  \n";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}
		
		if(!empty($results)) {
			$own_organiation_id = $results['DesOrder']['DesSupplier']['own_organization_id'];	
		}
		
		if($debug) {
			echo "<br />DesSupplier->getOrganizationIdTitolare(): $own_organiation_id  \n";
		}
				
	    return $own_organiation_id;
	}

	/*
	 * ctrl se il GAS e' superReferenteTitolareDes
	 * se non lo e' ctrl che lo sia qualcun'altro, se no potrebbe diventarlo
	 */
	public function isOrganizationTitolare($user, $des_supplier_id, $debug = false) {

		/*
		 * ctrl se ancora nessuno e' superReferenteTitolareDes DesSupplier.own_organization_id = 0
	 	*/	
	   $options = array();
	   $options['conditions'] = array('DesSupplier.des_id' => $user->des_id,
		    						  'DesSupplier.own_organization_id' => 0,
	    							   'DesSupplier.id' => (int)$des_supplier_id);
	   $total = $this->find('count', $options);
		if($debug) { 
			echo "<pre>DesSupplier->isOrganizationTitolare() ";
			print_r($options);
			echo "</pre>";
			echo '<br />totali '.$total.' se 1 non c\'e\' ancora un titolare';
	    }
	    	   
	   if($total==1)
	   		return true;
	   	
	   	/*
	   	 * qualcuno e' superReferenteTitolareDes, ctrl che sia il mio GAS
	   	 */
	    $options = array();
	    $options['conditions'] = array('DesSupplier.des_id' => $user->des_id,
	    							   'DesSupplier.own_organization_id' => (int)$user->organization['Organization']['id'],
									   'DesSupplier.id' => (int)$des_supplier_id);
	    $total = $this->find('count', $options);
		if($debug) { 
			echo "<pre>";
			print_r($options);
			echo "</pre>";
			echo '<br />isOrganizationTitolare '.$total;
	    }
	    
	 	if($total==1)
			return true;
		else
			return false;
	}

	/*
	 * ctrl per il produttore qual'è il GAS titolare
	 */
	public function aggiornaOwnOrganizationId($user, $des_supplier_id, $debug = false) {

		App::import('Model', 'DesSuppliersReferent');
		$DesSuppliersReferent = new DesSuppliersReferent;
		
	    $options = array();
	    $options['conditions'] = array('DesSuppliersReferent.des_id' => $user->des_id,
		    						  'DesSuppliersReferent.des_supplier_id' => $des_supplier_id,
	    							  'DesSuppliersReferent.group_id' => Configure::read('group_id_titolare_des_supplier'));
	    $options['fields'] = array('DesSuppliersReferent.organization_id');							  
	    $options['recursive'] = -1;							  
	    $desSuppliersReferentResults = $DesSuppliersReferent->find('first', $options);
	    
		if($debug) { 
			echo "<pre>DesSupplier->aggiornaOwnOrganizationId() \n ";
			print_r($options);
			print_r($desSuppliersReferentResults);
			echo "</pre>";
	    }

		if(empty($desSuppliersReferentResults))
			$own_organization_id = 0;
		else
			$own_organization_id = $desSuppliersReferentResults['DesSuppliersReferent']['organization_id'];
		
		try {
			$sql = "UPDATE
						`".Configure::read('DB.prefix')."des_suppliers`
					SET
						own_organization_id = ".$own_organization_id."
					WHERE
					  des_id = ".$user->des_id." 
					  and id = ".(int)$des_supplier_id;
			if($debug) echo $sql;
			$result = $this->query($sql);
		
		} catch (Exception $e) {
			echo '<br />DesSupplier->aggiornaOwnOrganizationId()<br />'.$e;
		}
		
		if($debug) exit;
	}	
	
	public function hasOrganizationSupplier($user, $organization_id, $des_supplier_id, $debug=false) {
		
	    $options = array();
	    $options['conditions'] = array('DesSupplier.des_id' => $user->des_id,
									    'DesSupplier.id' => $des_supplier_id);
	    $options['fields'] = array('DesSupplier.supplier_id');
	    $options['recursive'] = -1;
	    $results = $this->find('first', $options);
	
		if($debug) echo '<br />DesSupplier->hasOrganizationSupplier() - supplier_id '.$results['DesSupplier']['supplier_id'];	
	
   		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$tmp_user->organization['Organization']['id'] = $organization_id;
		$conditions = array('SuppliersOrganization.supplier_id' => $results['DesSupplier']['supplier_id']);
		$results = $SuppliersOrganization->getSuppliersOrganization($tmp_user, $conditions);
		
		if($debug) {
			echo "<pre>DesSupplier->hasOrganizationSupplier() - SuppliersOrganization ";
			print_r($results);
			echo "</pre>";
		}
						
		if(empty($results))
			return false;
		else	
			return true;
	}
	
	/*
	 * lista di tutti i DesSuppliers , per superReferenteDes
	 */
	public function getListDesSuppliers($user) {
	    
	    $options = array();
	    $options['conditions'] = array('DesSupplier.des_id' => $user->des_id,
	    								"(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')");
	    $options['fields'] = array('DesSupplier.id', 'Supplier.name');
	    $options['order'] = array('Supplier.name');
	    $options['recursive'] = 1;
	    $results = $this->find('list', $options);
		
		return $results;
	}
	
	/*
	 * get elenco dei desSupplier
	 * 	1, 3, 4, 56
	 */
	public function getDesSuppliersIds($user, $debug=false) {

		$options = array();
		$options['conditions'] = array('DesSupplier.des_id' => $user->des_id,
									   "(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')");
		$options['recursive'] = 1;
	    $options['fields'] = array('DesSupplier.id');
	    $options['order'] = array('DesSupplier.id');
		$this->unBindModel(array('belongsTo' => array('De')));
		$this->unBindModel(array('hasMany' => array('DesOrder')));
		
		$results = $this->find('all', $options);

		if($debug) {
			echo "<pre>getDesSuppliersIds ";
			print_r($results);
			echo "</pre>";
		}
				
		/*
		 * converto results in una stringa 1, 3, 4, 56
		*/
		if(!empty($results)) {
			$tmp = "";
			foreach ($results as $result) 
				$tmp .= $result['DesSupplier']['id'].',';
		
			$results = substr($tmp, 0, (strlen($tmp)-1));
		}
		else
			$results = 0;
			
		if($debug) echo '<br />'.$results;

		return $results;
	}
	
	public function getSuppliersOrganization($user, $des_supplier_id, $debug= false) {
	
		$options = array();
		$options['conditions'] = array('DesSupplier.des_id' => $user->des_id,
									   'DesSupplier.id' => $des_supplier_id);
		$options['recursive'] = -1;
		$options['fields'] = array('supplier_id');
		$results = $this->find('first', $options);
		
		if($debug) {
			echo "<pre>DesSupplier->getSuppliersOrganization ";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}
		
		$supplier_id = $results['DesSupplier']['supplier_id'];
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;		

		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => (int)$user->organization['Organization']['id'],
									   'SuppliersOrganization.supplier_id' => $supplier_id);
		$options['recursive'] = -1;		
		$results = $SuppliersOrganization->find('first', $options);
		if($debug) {
			echo "<pre>DesSupplier->getSuppliersOrganization ";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}
				
		return $results;
    }
    	
	public $validate = array(
		'des_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
		'own_organization_id' => array(
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
			'De' => array(
					'className' => 'De',
					'foreignKey' => 'des_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'Supplier' => array(
					'className' => 'Supplier',
					'foreignKey' => 'supplier_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'OwnOrganization' => array(
					'className' => 'Organization',
					'foreignKey' => 'own_organization_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			)			
	);	
	
	public $hasMany = array(
			'DesOrder' => array(
					'className' => 'DesOrder',
					'foreignKey' => 'des_supplier_id',
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