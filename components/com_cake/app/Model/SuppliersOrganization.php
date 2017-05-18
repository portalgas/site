<?php
App::uses('AppModel', 'Model');

/**
 * DROP TRIGGER IF EXISTS `k_suppliers_organizations_Trigger`;
 * DELIMITER |
 * CREATE TRIGGER `k_suppliers_organizations_Trigger` AFTER DELETE ON `k_suppliers_organizations`
 *  FOR EACH ROW BEGIN
 * delete from k_suppliers_organizations_referents where supplier_organization_id = old.id and organization_id = old.organization_id;
 * delete from k_articles where supplier_organization_id = old.id and organization_id = old.organization_id;
 * delete from k_orders where supplier_organization_id = old.id and organization_id = old.organization_id;
 * END
 * |
 * DELIMITER ;
 */

class SuppliersOrganization extends AppModel {

	public $primaryKey = 'id';
	
	public function getSupplier($user, $supplier_organization_id) {
	
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
									   'SuppliersOrganization.id' => $supplier_organization_id);
		$options['recursive'] = -1;
		//$options['fields'] = array('supplier_id');
		$results = $this->find('first', $options);
		
		return $results;
    }
	
	public function getSuppliersOrganization($user, $conditions, $orderBy=null) {
	
		if(isset($orderBy['SuppliersOrganization'])) $order = $orderBy['SuppliersOrganization'];
		else $order = 'SuppliersOrganization.name ASC';
		
		$sql = "SELECT
				  	Supplier.*,
				  	SuppliersOrganization.id, SuppliersOrganization.name, SuppliersOrganization.frequenza,
                                        SuppliersOrganization.mail_order_open, SuppliersOrganization.mail_order_close,
					Content.id, Content.catid 
				FROM
					".Configure::read('DB.prefix')."suppliers_organizations AS SuppliersOrganization,
					".Configure::read('DB.prefix')."suppliers AS Supplier LEFT JOIN ".Configure::read('DB.portalPrefix')."content AS Content 
						ON (Content.id = Supplier.j_content_id)
				WHERE
					SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
					AND Supplier.id = SuppliersOrganization.supplier_id
					AND SuppliersOrganization.stato = 'Y'
					AND (Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG') ";
		if(isset($conditions['SuppliersOrganization.id'])) $sql .= " AND SuppliersOrganization.id = ".$conditions['SuppliersOrganization.id'];
		if(isset($conditions['SuppliersOrganization.supplier_id'])) $sql .= " AND SuppliersOrganization.supplier_id = ".$conditions['SuppliersOrganization.supplier_id'];
			$sql .= ' ORDER BY '.$order;

		// echo '<br />'.$sql;
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}		
		
		return $results;
	}
			
	/*
	 * get elenco dei suppliersOrganization
	 * 	1, 3, 4, 56
	 */
	public function getSuppliersOrganizationIds($user) {

		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
									   "(Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')");
		$options['recursive'] = 1;
		$options['fields'] = array('id');
		$options['order_by'] = array('id');
		
		$this->unBindModel(array('belongsTo' => array('Organization', 'CategoriesSupplier')));
		$results = $this->find('list', $options);

		/*
		 * converto results in una stringa 1, 3, 4, 56
		*/
		if(!empty($results)) {
			$tmp = "";
			foreach ($results as $key => $value)
				$tmp .= $value.',';
		
			$results = substr($tmp, 0, (strlen($tmp)-1));
		}
		else
			$results = 0;
							
		return $results;
	}
			
	/*
	 * articoli che si possono ordinare
	 */
	public function getTotArticlesPresentiInArticlesOrder($user, $supplier_organization_id) {

		$totArticles = array();
		$sql = "SELECT count(id) as totArticles 
				FROM ".Configure::read('DB.prefix')."articles
				WHERE
					stato = 'Y' 
					AND flag_presente_articlesorders = 'Y' 
					AND supplier_organization_id = ".$supplier_organization_id;
		/*
		 * non + il produttore puo' essere un ProdGas
		 *  AND organization_id = ".(int)$user->organization['Organization']['id']."
		 */	    
		//echo '<br />'.$sql;
		try {
			$totArticles = current($this->query($sql));
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
				
		return $totArticles[0];
	} 		
	
	public function getTotArticlesAttivi($user, $supplier_organization_id) {

		$totArticles = array();
		$sql = "SELECT count(id) as totArticles 
				FROM ".Configure::read('DB.prefix')."articles
				WHERE
					stato = 'Y'
					AND supplier_organization_id = ".$supplier_organization_id;
		/*
		 * non + il produttore puo' essere un ProdGas
		 *  AND organization_id = ".(int)$user->organization['Organization']['id']."
		 */	    
		//echo '<br />'.$sql;
		try {
			$totArticles = current($this->query($sql));
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
				
		return $totArticles[0];
	} 
	
	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'supplier_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'category_supplier_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

	public $belongsTo = array(
		'Organization' => array(
			'className' => 'Organization',
			'foreignKey' => 'organization_id',
			'conditions' => 'Organization.id = SuppliersOrganization.organization_id',
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
		'CategoriesSupplier' => array(
				'className' => 'CategoriesSupplier',
				'foreignKey' => 'category_supplier_id',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		)
	);

	public $hasMany = array(
		'Article' => array(
			'className' => 'Article',
			'foreignKey' => 'supplier_organization_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'supplier_organization_id',
			'dependent' => false,
			'conditions' => '', 
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'SuppliersOrganizationsReferent' => array(
			'className' => 'SuppliersOrganizationsReferent',
			'foreignKey' => 'supplier_organization_id',
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