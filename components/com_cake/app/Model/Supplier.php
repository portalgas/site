<?php
App::uses('AppModel', 'Model');

/**
 * DROP TRIGGER IF EXISTS `k_suppliers_Trigger`;
 * DELIMITER |
 * CREATE TRIGGER `k_suppliers_Trigger` AFTER DELETE ON `k_suppliers`
 * FOR EACH ROW BEGIN
 * delete from k_suppliers_organizations where supplier_id = old.id;
 * END
 * |
 * DELIMITER ;
 */

class Supplier extends AppModel {

	public function ctrlSupplierDuplicate($user, $field, $value) {
		
		$msg = '';
		
		$options['conditions'] = array('Supplier.'.$field.' LIKE ' => '%'.$value.'%',
									   'Supplier.stato' => 'Y');
		$options['order'] = array('Supplier.name');
		$options['recursive'] = 1;
		
		$results = $this->find('all', $options);
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		if(count($results) < 5) {
			foreach($results as $result) {
				
				$msg .= "Esiste già un produttore denominato <b>".$result['Supplier']['name']."</b> con il campo <b>";
				switch($field) {
					case "cf":
						$msg .= "Codice fiscale";
					break;
					case "piva":
						$msg .= "Partita iva";
					break;
					case "mail":
						$msg .= "Mail";
					break;
					case "name":
						$msg .= "Nome";
					break;
					case "fax":
						$msg .= "Fax";
					break;
					case "telefono":
						$msg .= "Telefono";
					break;
					case "telefono2":
						$msg .= __("Telefono2");
					break;
					case "www":
						$msg .= __("Www");
					break;
					default:
						$msg .= $field;
					break;
				}
				$msg .= "</b> valorizzato a <b>".$value."</b><br />";
				$supplier_associato_al_proprio_gas=false;
			
				if(isset($result['SuppliersOrganization'])) {
					foreach($result['SuppliersOrganization'] as $suppliersOrganization) {
						// gia' del GAS
						if($suppliersOrganization['organization_id']==$user->organization['Organization']['id']) {
							$msg .= "<a href='/administrator/index.php?option=com_cake&controller=SuppliersOrganizations&action=edit&id=".$suppliersOrganization['id']."'>Clicca qui per visualizzarlo, fa parte del tuo <b>GAS</b></a><br />";
							$supplier_associato_al_proprio_gas=true;
						}						
					}
				}
				// non anocra associato, lo devo importare
				if(!$supplier_associato_al_proprio_gas) {
					$msg .= "<a href='/administrator/index.php?option=com_cake&controller=SuppliersOrganizations&action=add_index&duplicateSuppliersId=".$result['Supplier']['id']."'>Clicca qui per visualizzarlo ed <b>importarlo</b></a><br />";					
				}
				
				$msg .= "</br>";
			}
		}

		return $msg;
	}
	
	public function getListSuppliers($user, $debug=false) {
	
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
									   'SuppliersOrganization.stato' => 'Y',
									   "(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')");
		$options['recursive'] = 1;
		$options['order'] = array('SuppliersOrganization.name');
		$results = $SuppliersOrganization->find('list', $options);

		if($debug) {
			echo "<pre>Supplier::getListSuppliers() ";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}
		
		return $results;
	}
	
	/*
	 * elenco provincie dei produttori non ancora associati al GAS
	 */
	public function getListProvincia($user, $debug=false) {
	
		/*
		$options = array();
		$options['conditions'] = array('Supplier.stato' => 'Y', 'Supplier.provincia !=' => '');
		$options['recursive'] = -1;
		$options['fields'] = array('DISTINCT (Supplier.provincia) AS provincia ');
		$options['order'] = array('Supplier.provincia');
		$results = $this->find('all', $options);
		*/
		
		$sql = "SELECT 
					DISTINCT (Supplier.provincia) AS provincia 
				FROM 
					".Configure::read('DB.prefix')."suppliers as Supplier
				WHERE 
					(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')
					and Supplier.id NOT IN (
						select 
							s.id 
						FROM 
							".Configure::read('DB.prefix')."suppliers s, 
							".Configure::read('DB.prefix')."suppliers_organizations o 
						WHERE s.id = o.supplier_id
						and o.organization_id = ".(int)$user->organization['Organization']['id'].") 
				ORDER BY Supplier.provincia";
		// echo '<br />'.$sql;
		$results = $this->query($sql);				

		$newResults = array();
		foreach($results as $numResuls => $result) {
			$newResults[$result['Supplier']['provincia']] = $result['Supplier']['provincia'];
		}
		/*
		echo "<pre>";
		print_r($newResults);
		echo "</pre>";
		*/
		return $newResults;	
	}
	
	/*
	 * elenco CAP dei produttori non ancora associati al GAS
	 */
	public function getListCap($user, $debug=false) {
		
		/*
		$options = array();
		$options['conditions'] = array('Supplier.stato' => 'Y', 'Supplier.provincia !=' => '');
		$options['recursive'] = -1;
		$options['fields'] = array('DISTINCT (Supplier.cap) AS cap ');
		$options['order'] = array('Supplier.cap');
		$results = $this->find('all', $options);
		*/
		
		$sql = "SELECT 
					DISTINCT (Supplier.cap) AS cap 
				FROM 
					".Configure::read('DB.prefix')."suppliers as Supplier
				WHERE 
					(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')
					and Supplier.id NOT IN (
						select 
							s.id 
						FROM 
							".Configure::read('DB.prefix')."suppliers s, 
							".Configure::read('DB.prefix')."suppliers_organizations o 
						WHERE s.id = o.supplier_id
						and o.organization_id = ".(int)$user->organization['Organization']['id'].") 
				ORDER BY Supplier.cap";
		// echo '<br />'.$sql;
		$results = $this->query($sql);	
		
		$newResults = array();
		foreach($results as $numResuls => $result) {
			$newResults[$result['Supplier']['cap']] = $result['Supplier']['cap'];
		}
		/*
		echo "<pre>";
		print_r($newResults);
		echo "</pre>";
		*/
		return $newResults;		
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

	public $belongsTo = array(
		'CategoriesSupplier' => array(
			'className' => 'CategoriesSupplier',
			'foreignKey' => 'category_supplier_id',
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