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
	
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
							      'SuppliersOrganization.id' => $supplier_organization_id];
		$options['recursive'] = -1;
		//$options['fields'] = ['supplier_id'];
		$results = $this->find('first', $options);
		
		return $results;
    }
	
	/*
	 * se $user->organization['Organization']['hasCashFilterSupplier'] = 'Y' 
	 *		Ha la gestione del prepagato solo per alcuni produttori 
	 *	ctrl in supplier_organization_cash_excludeds se e' un produttore escluso dal prepagato
	 */	
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
		if(isset($conditions['SuppliersOrganization.stato'])) $sql .= " AND SuppliersOrganization.stato = '".$conditions['SuppliersOrganization.stato']."'";
		if(isset($conditions['SuppliersOrganization.supplier_id'])) $sql .= " AND SuppliersOrganization.supplier_id = ".$conditions['SuppliersOrganization.supplier_id'];
			$sql .= ' ORDER BY '.$order;

		self::d($sql, false);
		try {
			$results = $this->query($sql);
		
			/*
			 * ctrl in supplier_organization_cash_excludeds se e' un produttore escluso dal prepagato 
			 */
			if(isset($user->organization['Organization']['hasCashFilterSupplier']) && $user->organization['Organization']['hasCashFilterSupplier']=='Y') {

		        App::import('Model', 'SupplierOrganizationCashExcluded');
		        $SupplierOrganizationCashExcluded = new SupplierOrganizationCashExcluded;		
		        $results[0]['SuppliersOrganization']['isSupplierOrganizationCashExcluded'] = $SupplierOrganizationCashExcluded->isSupplierOrganizationCashExcluded($user, $results[0]['SuppliersOrganization']['id']);	
				 
			}
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}		
		
		return $results;
	}
		
	/*
	 * dato un SuppliersOrganization estraggo i possibili OwnerArticles SUPPLIER / REFERENT / DES
	 */
	public function getOwnerArticles($user, $suppliersOrganizationResults, $debug=false) {
		
		$supplier_owner_articles = ClassRegistry::init('SuppliersOrganization')->enumOptions('owner_articles');
		
		if(!is_array($suppliersOrganizationResults))
			$suppliersOrganizationResults = $this->_getSuppliersOrganizationResultsById($user, $suppliersOrganizationResults, $debug);

		/*
		 * ctrl se il produttore e' Organization.type = 'PRODGAS'
		 */
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;
		
		$supplierResults = $ProdGasSupplier->getBySupplierId($user, $suppliersOrganizationResults['SuppliersOrganization']['supplier_id'], $debug);
		 		
		/*
		 * ctrl se GAS e' DES
		 */
		$desSupplierResults = [];
		$isGasTitolare = false;
		
		if($user->organization['Organization']['hasDes'] == 'Y') { 
			App::import('Model', 'DesOrganization');
			$DesOrganization = new DesOrganization;
			
			App::import('Model', 'DesSupplier');
			$DesSupplier = new DesSupplier;	
	
			$options = [];
			$options['conditions'] = ['DesOrganization.organization_id' => $user->organization['Organization']['id']];
			$options['recursive'] = -1;
			$options['fields'] = ['DesOrganization.des_id'];
			$desOrganizationResults = $DesOrganization->find('all', $options);
			self::d($desOrganizationResults);
			
			if(!empty($desOrganizationResults)) {
				$des_ids = [];
				foreach($desOrganizationResults as $desOrganizationResult)
					array_push($des_ids, $desOrganizationResult['DesOrganization']['des_id']);
	
				// self::d($des_ids, $debug);
				/*
				 * ctrl se produttote DES
				 */			
				$options = [];
				$options['conditions'] = ['DesSupplier.des_id' => $des_ids,
										  'DesSupplier.supplier_id' => $suppliersOrganizationResults['SuppliersOrganization']['supplier_id']];
				$options['recursive'] = -1;
				$desSupplierResults = $DesSupplier->find('first', $options);
				self::d($options, $debug);
				self::d($desSupplierResults, $debug);
	
				if(!empty($desSupplierResults)) {
					/*
					 * ctrl se il GAS e' titolare
					 */
					if($desSupplierResults['DesSupplier']['own_organization_id']==$user->organization['Organization']['id'])
						$isGasTitolare = true;
					else
						$isGasTitolare = false;
					
				}			
			} // if(!empty($desOrganizationResults))
		} // if($user->organization['Organization']['hasDes'] == 'Y')
				
		if(empty($desSupplierResults)) {
			unset($supplier_owner_articles['DES']);			
		}
		else {
			if($isGasTitolare)
				unset($supplier_owner_articles['DES']);
		}

		switch ($suppliersOrganizationResults['SuppliersOrganization']['owner_articles']) {
			case 'SUPPLIER':
				if(empty($desSupplierResults))
					unset($supplier_owner_articles['DES']);			
			break;
			case 'REFERENT':
				if(empty($supplierResults))
					unset($supplier_owner_articles['SUPPLIER']);	
			break;
			case 'DES':
				if(empty($supplierResults))
					unset($supplier_owner_articles['SUPPLIER']);
			break;
		}
		
		self::d($supplier_owner_articles, $debug);
		
		return $supplier_owner_articles;
	}	

	private function _getSuppliersOrganizationResultsById($user, $suppliersOrganizationResults, $debug) {
	
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
							      'SuppliersOrganization.id' => $suppliersOrganizationResults];
		$options['recursive'] = 1;
		$results = $this->find('first', $options);
		
		return $results;		
	}
	
	/*
	 * get elenco dei suppliersOrganization
	 * 	1, 3, 4, 56
	 */
	public function getSuppliersOrganizationIds($user) {

		$options = [];
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

		$debug = false;
				
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
								  'SuppliersOrganization.id' => $supplier_organization_id];
		$options['fields'] = ['SuppliersOrganization.owner_articles', 'SuppliersOrganization.owner_organization_id', 'SuppliersOrganization.owner_supplier_organization_id'];
		$options['recursive'] = -1;
		self::d($options, $debug);
		$suppliersOrganizationResults = $this->find('first', $options);
			
		App::import('Model', 'Article');
		$Article = new Article;
		$Article->unbindModel(['belongsTo' => ['CategoriesArticle']]);
		$Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$Article->unbindModel(['hasMany' => ['ArticlesArticlesType', 'ArticlesOrder']]);
			
		$options = [];
		$options['conditions'] = ['Article.stato' => 'Y',
								  'Article.flag_presente_articlesorders' => 'Y',
								  'Article.organization_id' => $suppliersOrganizationResults['SuppliersOrganization']['owner_organization_id'],
								  'Article.supplier_organization_id' => $suppliersOrganizationResults['SuppliersOrganization']['owner_supplier_organization_id']];
		$options['recursive'] = 0;						  
		$articleCount = $Article->find('count', $options);
		self::d($options['conditions'], $debug);
		self::d($articleCount, $debug);
				
		return $articleCount;
	} 		
	
	/*
	 * $opts['conditions'] = ['Article.stato' => 'Y'];
	 */
	public function getTotArticlesAttivi($user, $supplier_organization_id, $opts=[], $debug=false) {
	
		$articlesResults = $this->getArticlesBySupplierOrganizationId($user, $supplier_organization_id, $opts, $debug);
		$articlesCountResults = count($articlesResults);
		self::d($articlesResults, $debug);	
		self::d($articlesCountResults, $debug);	

		return $articlesCountResults;		
	} 
	
	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
			),
		),
		'supplier_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
			),
		),
		'category_supplier_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
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