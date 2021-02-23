<?php
App::uses('AppModel', 'Model');

class ProdGasPromotionsOrganizationsManager extends AppModel {

    public $useTable = 'prod_gas_promotions_organizations';
 
	/*
	 * promozioni da associare ad un ordine di GAS state_code = WAITING
	 */
    public function getWaitingPromotions($user, $rules=[], $debug=false) {
    
		$options = [];
		$options['conditions'] = ['ProdGasPromotionsOrganizationsManager.organization_id' => $user->organization['Organization']['id'],
								   'ProdGasPromotionsOrganizationsManager.state_code' => 'WAITING',
								   'ProdGasPromotion.state_code' => 'PRODGASPROMOTION-GAS-TRASMISSION-TO-GAS',
								   'ProdGasPromotion.type' =>  'GAS-USERS',
								   'DATE(ProdGasPromotion.data_inizio) <= CURDATE() AND DATE(ProdGasPromotion.data_fine) >= CURDATE()'];

		$this->unbindModel(['belongsTo' => ['Organization']]);
		$options['recursive'] = 1;	
		$results = $this->find('all', $options);
		self::d([$options, $results], $debug);	

			
		App::import('Model', 'ProdGasArticlesPromotion');
		$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
		$ProdGasArticlesPromotion->unbindModel(['belongsTo' => ['ProdGasPromotion']]);

		App::import('Model', 'Organization');
		$Organization = new Organization;
		
		$Organization->unbindModel(['belongsTo' => ['Template']]);
		
		$belongsTo = [
				'className' => 'Supplier',
				'foreignKey' => '',
				'conditions' => 'Supplier.owner_organization_id = Organization.id',
				'fields' => '',
				'order' => ''];
		
		$Organization->bindModel(['belongsTo' => ['Supplier' => $belongsTo]]);

		/*
		 * per ogni promozione estraggo Organization del produttore, ProdGasArticle
 		 */		
		foreach($results as $numResult => $result) {
			
			$options = [];
			$options['conditions'] = ['Organization.id' => $result['ProdGasPromotion']['organization_id']];
			$options['recursive'] = 0;
			$organizationResults = $Organization->find('first', $options);
			self::d($organizationResults, $debug);	
			$results[$numResult]['ProdGasSupplier'] = $organizationResults;
			
			$options = [];
			$options['conditions'] = ['ProdGasArticlesPromotion.prod_gas_promotion_id' => $result['ProdGasPromotion']['id']];
			$options['recursive'] = 1;
			$prodGasArticlesPromotionResults = $ProdGasArticlesPromotion->find('all', $options);
			if(!empty($prodGasArticlesPromotionResults)) {
				$results[$numResult]['Article'] = $prodGasArticlesPromotionResults;
			}
			
			/*
			 * ctrl ACL
			 */
			$results[$numResult]['Acl'] = $this->isAclToManagement($user, $results[$numResult]['ProdGasSupplier']['Supplier']['id'], $rules, $debug);				
		}
		
		self::d([$options, $results], $debug);
		
		return $results;	    
    }
    
	/*
	 * promozioni associate ad un ordine di GAS state_code = OPEN
	 */
    public function getOpenPromotions($user, $rules=[], $debug=false) {
		
		$options = [];
		$options['conditions'] = ['ProdGasPromotionsOrganizationsManager.organization_id' => $user->organization['Organization']['id'],
								   'ProdGasPromotionsOrganizationsManager.state_code' => 'OPEN',
								   'ProdGasPromotion.state_code != ' => 'PRODGASPROMOTION-GAS-WORKING',
								   'ProdGasPromotion.type' =>  'GAS-USERS'];

		$this->unbindModel(['belongsTo' => ['Organization']]);
		$options['recursive'] = 1;
		$results = $this->find('all', $options);
		
		/*
		 * per ogni ordine estraggo Delivery , SuppliersOrganization
 		 */
		App::import('Model', 'Delivery');
		App::import('Model', 'SuppliersOrganization'); 
		foreach($results as $numResult => $result) {
			if(isset($result['Order'])) {
					
				$order_id = $result['Order']['id'];
				$delivery_id = $result['Order']['delivery_id'];
				$supplier_organization_id = $result['Order']['supplier_organization_id'];
				
				$Delivery = new Delivery;
				
				$options = [];
				$options['conditions'] = ['Delivery.organization_id' => $user->organization['Organization']['id'], 'Delivery.id' => $delivery_id];
				$options['recursive'] = -1;
				$deliveryResults = $Delivery->find('first', $options);
				if(!empty($deliveryResults)) {
					$results[$numResult]['Delivery'] = $deliveryResults['Delivery'];
				}
				
				$SuppliersOrganization = new SuppliersOrganization;
				
				$options = [];
				$options['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
											'SuppliersOrganization.id' => $supplier_organization_id];
				$options['recursive'] = -1;
				$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
				if(!empty($suppliersOrganizationResults)) {
					$results[$numResult]['SuppliersOrganization'] = $suppliersOrganizationResults['SuppliersOrganization'];
				}
			}
		} 
		
		self::d($results, $debug);

		return $results;		
	}
	
	/*
	 * ctrl che lo user sia superReferent o referente del produttore 
	 */
	public function isAclToManagement($user, $supplier_id, $rules=[], $debug=false) {

		$acl = false;
		$suppliersOrganizationsReferents = [];

		self::d($rules, $debug);	

		if(!isset($rules['isReferente']))
			$isReferente = false;
		else 
			$isReferente = $rules['isReferente'];
			
		if(!isset($rules['isSuperReferente']))
			$isSuperReferente = false;
		else 
			$isSuperReferente = $rules['isSuperReferente'];
		
		/*	
		if(!isset($rules['isManager']))
			$isManager = false;
		else 
			$isManager = $rules['isManager'];
		*/

		if (!$isSuperReferente || $isReferente) {
			App::import('Model', 'SuppliersOrganizationsReferent');
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
			 
			$suppliersOrganizationsReferents = $SuppliersOrganizationsReferent->getSuppliersOrganizationByReferent($user, $user->id);
			self::d($suppliersOrganizationsReferents, $debug);	
		}
		
		if($isSuperReferente)
			$acl = true;
		else 
		if(!$isReferente) {
			foreach ($suppliersOrganizationsReferents as $suppliersOrganizationsReferent) {
				if($supplier_id==$suppliersOrganizationsReferent['Supplier']['id']) {	
					$acl = true;
					break;
				}
			}	
		}
		
		return $acl;		
	}
	
	/*
	 * estrare i superReferent e referente del produttore di ogni GAS
	 * se $organization_id solo del GAS 
	 */	
	public function getReferents($user, $prod_gas_promotion_id, $organization_id=0, $debug = false) {
	
		$results = [];

        App::import('Model', 'ProdGasPromotion');
        $ProdGasPromotion = new ProdGasPromotion;

		$promotionResults = $ProdGasPromotion->getProdGasPromotion($user, $prod_gas_promotion_id, $organization_id, $debug);

		if(!empty($promotionResults['Organization']))
		foreach($promotionResults['ProdGasPromotionsOrganization'] as $promotionResult) {
	
			$organization_id = $promotionResult['ProdGasPromotionsOrganization']['id']; 

	 		$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organization_id]);

	 		/*
	 		 * estraggo users group_id_super_referent 
	 		 */
	        App::import('Model', 'User');
	        $User = new User;

			$conditions = [];
			$conditions = ['UserGroup.group_id' => Configure::read('group_id_super_referent')];
			// debug($conditions);
			$superReferentUsersResults = $User->getUsersComplete($tmp_user, $conditions,  Configure::read('orderUser'), $debug);
			if($debug) debug($superReferentUsersResults);
			if(!empty($superReferentUsersResults)) {
				foreach($superReferentUsersResults as $superReferentUsersResult) {
					$results[$superReferentUsersResult['User']['id']]['User'] = $superReferentUsersResult['User'];
					$results[$superReferentUsersResult['User']['id']]['UserProfile'] = $superReferentUsersResult['Profile'];
				}
			}

	 		/*
	 		 * estraggo users group_id_referent 
	 		 */

			/*
			 * suppliers_organization_id del GAS per ricercare i referente ai quali inviare la mail  
			 */ 
			$suppliers_organization_id = 0;
			foreach($promotionResults['Organization'] as $numResult => $organization) {
				if($organization['Organization']['id']==$organization_id) {
					$suppliers_organization_id = $organization['SuppliersOrganization']['id']; 
					unset($promotionResults['Organization'][$numResult]);
					break;
				}
			}	
			
			if(!empty($suppliers_organization_id)) {
		
		 		$conditions = [];
		 		$conditions['SuppliersOrganization.id'] = $suppliers_organization_id;

		        App::import('Model', 'SuppliersOrganizationsReferent');
		        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent; 		
				$referentUsersResults = $SuppliersOrganizationsReferent->getReferentsCompact($tmp_user, $conditions);
				if($debug) debug($referentUsersResults);
				if(!empty($referentUsersResults)) {
					foreach($referentUsersResults as $referentUsersResult) {
						$results[$referentUsersResult['User']['id']]['User'] = $referentUsersResult['User'];
						$results[$referentUsersResult['User']['id']]['UserProfile'] = $referentUsersResult['Profile'];
					}
				}
			} // end if(!empty($suppliers_organization_id))
		} // loop organizations

		return $results;
	}	

	/*
	 * il referente o superReferente accetta la promozione:
	 * si importa il produttore (da Supplier a SuppliersOrganization)
	 */
	public function importProdGasSupplier($user, $prod_gas_promotion_id, $debug=false) {
		
		$continua = true;
		$supplier_organization_id = 0;
		
		if(empty($prod_gas_promotion_id)) {
			self::d('ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Passaggio parametri errato: '.$prod_gas_promotion_id, $debug);
			return false;
		}
		
		/*
		 * dati promozione 
		 */
		App::import('Model', 'ProdGasPromotion');
		$ProdGasPromotion = new ProdGasPromotion;
		
		$options = [];
		$options['conditions'] = ['ProdGasPromotion.id' => $prod_gas_promotion_id];
		$options['recursive'] = -1;
		$results = $ProdGasPromotion->find('first', $options);
		self::d([$options,$results], $debug);
		
		/*
		 * dati produttore 
		 */
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;
		
		$supplierResults = $ProdGasSupplier->getOrganizationSupplier($user, $results['ProdGasPromotion']['organization_id']);
		self::d($supplierResults, $debug);
		
		/*
		 * ctrl se il GAS ha il produttore associato
		 */
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization','CategoriesSupplier')));
		$SuppliersOrganization->unbindModel(array('hasMany' => array('Article','Order','SuppliersOrganizationsReferent')));

		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
								   'Supplier.id' => $supplierResults['SuppliersOrganization']['supplier_id'],
								   'Supplier.stato' => 'Y']; // non prendo Supplier.stato = 'T' or Supplier.stato = 'PG'
		$options['recursive'] = 1;
		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		self::d([$options,$supplierResults], $debug);
		
		self::d($suppliersOrganizationResults, $debug);

		if(!empty($suppliersOrganizationResults)) {
			/*
			 * il produttore c'e' ma era disabilitato
			 */
			if($suppliersOrganizationResults['SuppliersOrganization']['stato']!='Y') {
				$sql = "UPDATE ".Configure::read('DB.prefix')."suppliers_organizations SET stato='Y' WHERE id=".$suppliersOrganizationResults['SuppliersOrganization']['id']." AND organization_id=".$user->organization['Organization']['id'];
				if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasSupplier() '.$sql;
				try {
					$this->query($sql);
				}
				catch (Exception $e) {
					CakeLog::write('error',$sql);
					CakeLog::write('error',$e);
					return false;
				}				
			}
			
			$supplier_organization_id = $suppliersOrganizationResults['SuppliersOrganization']['id'];
			
			if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Produttore ESISTE gia ('.$supplier_organization_id.') => non lo creo';
		}
		else {
			/*
			 * import produttore
			 */
			App::import('Model', 'Supplier');
			$Supplier = new Supplier;
			
			$options = [];
			$options['conditions'] = ['Supplier.id' => $supplier_id];
			$options['recursive'] = -1;
			$supplierResults = $Supplier->find('first', $options);
			if(empty($supplierResults)) {
			if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Dati produttore NON trovato!';
				return false;			
			}	
			
			else {
				$data=[];
				$data['SuppliersOrganization']['organization_id'] = $user->organization['Organization']['id'];
				$data['SuppliersOrganization']['supplier_id'] = $supplier_id;
				$data['SuppliersOrganization']['name'] = $supplierResults['Supplier']['name'];
				$data['SuppliersOrganization']['category_supplier_id'] = $supplierResults['Supplier']['category_supplier_id'];
				$data['SuppliersOrganization']['stato'] = 'Y';
				$data['SuppliersOrganization']['mail_order_open'] = 'Y';
				$data['SuppliersOrganization']['mail_order_close'] = 'Y';
				$SuppliersOrganization->create();
				
				if($debug) {
					echo "<pre>ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Dati produttore da SAVE \n";
					print_r($data);
					echo "</pre>";
				}
				
				if(!$SuppliersOrganization->save($data)) {
					if($debug) echo '<br />ProdGasPromotionsOrganizationsManager::importProdGasSupplier() Produttore NON salvato!';
					return false;	
				}	
				
				$supplier_organization_id = $SuppliersOrganization->getLastInsertId();
			}	 
		}

		 return $supplier_organization_id;		 
	}

	/*
	 * il referente / superReferente accetta la promozione:
	 * si importa articoli in promozioni in articoli in ordine (da ProdGasArticlesPromotion a ArticlesOrders)
	 * la ProdGasArticlesPromotion.qta diventera' ArticlesOrder.qta_minima_order e ArticlesOrder.qta_massima_order
	 */
	public function importProdGasArticlesPromotions($user, $prod_gas_promotion_id, $order_id, $debug=false) {
		
		$continua = true;
		
		if(empty($prod_gas_promotion_id) || empty($order_id)) {
			self::d('ProdGasPromotionsOrganizationsManager::importProdGasArticlesPromotions() Passaggio parametri errato: supplier_id '.$supplier_id.' prod_gas_promotion_id '.$prod_gas_promotion_id.' order_id '.$order_id, $debug);
			return false;
		}
		
		/*
		 * dati promozione 
		 */
		App::import('Model', 'ProdGasPromotion');
		$ProdGasPromotion = new ProdGasPromotion;
		
		$options = [];
		$options['conditions'] = ['ProdGasPromotion.id' => $prod_gas_promotion_id];
		$options['recursive'] = -1;
		$results = $ProdGasPromotion->find('first', $options);
		self::d($results, $debug);
		
		/*
		 * dati produttore 
		 */
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;
		
		$supplierResults = $ProdGasSupplier->getOrganizationSupplier($user, $results['ProdGasPromotion']['organization_id']);
		
		App::import('Model', 'ArticlesOrder');
		
		/*
		 * estratto gli articoli in promozione
		 * ProdGasArticlesPromotion.qta => ArticlesOrder.qta_minima_order e ArticlesOrder.qta_massima_order
		 */
		App::import('Model', 'ProdGasArticlesPromotion');
		$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
		
		$ProdGasArticlesPromotion->unbindModel(['belongsTo' => ['ProdGasPromotion']]);
	
		$options = [];
		$options['conditions'] = ['ProdGasArticlesPromotion.prod_gas_promotion_id' => $prod_gas_promotion_id];
		$options['recursive'] = 0;
		$prodGasArticlesPromotionResults = $ProdGasArticlesPromotion->find('all', $options);
		self::d($options, $debug);
		self::d($prodGasArticlesPromotionResults, $debug); 	

		foreach($prodGasArticlesPromotionResults as $prodGasArticlesPromotionResult) {
			$data = [];
			$data['ArticlesOrder']['organization_id'] = $user->organization['Organization']['id'];
			$data['ArticlesOrder']['article_organization_id'] = $prodGasArticlesPromotionResult['Article']['organization_id'];
			$data['ArticlesOrder']['article_id'] = $prodGasArticlesPromotionResult['Article']['id'];
			$data['ArticlesOrder']['name'] = $prodGasArticlesPromotionResult['Article']['name'];
			$data['ArticlesOrder']['order_id'] = $order_id;
			$data['ArticlesOrder']['prezzo'] = $prodGasArticlesPromotionResult['ProdGasArticlesPromotion']['prezzo_unita_']; // prezzo originalre Article.prezzo;
			$data['ArticlesOrder']['qta_cart'] = 0;
			$data['ArticlesOrder']['pezzi_confezione'] = $prodGasArticlesPromotionResult['Article']['pezzi_confezione'];
			$data['ArticlesOrder']['qta_minima'] = $prodGasArticlesPromotionResult['Article']['qta_minima'];
			/*
			 * ProdGasArticlesPromotion.qta = quantita' dell'offerta 
			 */
			$data['ArticlesOrder']['qta_massima'] = $prodGasArticlesPromotionResult['ProdGasArticlesPromotion']['qta'];
			$data['ArticlesOrder']['qta_minima_order'] = $prodGasArticlesPromotionResult['ProdGasArticlesPromotion']['qta'];
			$data['ArticlesOrder']['qta_massima_order'] = $prodGasArticlesPromotionResult['ProdGasArticlesPromotion']['qta'];
			
			$data['ArticlesOrder']['qta_multipli'] = $prodGasArticlesPromotionResult['Article']['qta_multipli'];
			$data['ArticlesOrder']['flag_bookmarks'] = 'N';
			$data['ArticlesOrder']['alert_to_qta'] = 0;
			$data['ArticlesOrder']['stato'] = 'Y';

			/*
			 * richiamo la validazione
			 */
			$ArticlesOrder = new ArticlesOrder; 
			$ArticlesOrder->set($data);
			if (!$ArticlesOrder->validates()) {
				$errors = $ArticlesOrder->validationErrors;
				$tmp = '';
				$flatErrors = Set::flatten($errors);
				if (count($errors) > 0) {
					$tmp = '';
					foreach ($flatErrors as $key => $value)
						$tmp .= $value . ' - ';
				}
				self::d('Articolo non associato all\'ordine: dati non validi, '.$tmp, $debug);
				debug('Articolo non associato all\'ordine: dati non validi, '.$tmp);
				$continua = false;
			} else {
				$ArticlesOrder->create();
				if (!$ArticlesOrder->save($data)) {
					self::d('articolo '. $result['Article']['id'].' in errore!', $debug);
					$continua = false;
				}
			}					
		}
		
		return $continua;
	}
	
	public function getRejectNotes($user) {
		
		$results = [];
		
		$results["Promozione poco conveniente"] = "Promozione poco conveniente";
		$results["Articoli in promozione non interessanti"] = "Articoli in promozione non interessanti";
		$results["Ordinato dal produttore da poco"] = "Ordinato dal produttore da poco";
		$results["Altro..."] = "Altro...";
		
		return $results;
	}
	
	public $validate = array(
		'delivery_id' => array(
			'rule' => array('naturalNumber', false),
			'message' => "Scegli la consegna da associare all'ordine",
		),
		'data_inizio_db' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data valida',
				'allowEmpty' => false
			),
			'dateMinore' => array(
				'rule'       =>  array('date_comparison', '<=', 'data_fine_db'),
				'message'    => 'La data di apertura non può essere posteriore della data di chiusura',
			),
			'dateToDelivery' => array(
				'rule'       =>  array('date_comparison_to_delivery','>'),
				'message'    => 'La data di apertura non può essere posteriore della data della consegna',
			),
		),
		'data_fine_db' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data valida',
				'allowEmpty' => false
			),
			'dateMaggiore' => array(
				'rule'       =>  array('date_comparison', '>=', 'data_inizio_db'),
				'message'    => 'La data di chiusura non può essere antecedente della data di apertura',
			),
			'dateToDelivery' => array(
				'rule'       =>  array('date_comparison_to_delivery','>'),
				'message'    => 'La data di chiusura non può essere posteriore o uguale della data della consegna',
			),
			'dateToProdGasPromotionDataFine' => array(
				'rule'       =>  array('date_comparison','<=', 'prod_gas_promotion_data_fine'),
				'message'    => 'La data di chiusura non può essere posteriore alla data di chiusura della promozione',
			)
		),
	);

	function date_comparison($field=[], $operator, $field2) {
		foreach( $field as $key => $value1 ){
			$value2 = $this->data[$this->alias][$field2];
			
			if(empty($value2))
				return true;
			
			if (!Validation::comparison($value1, $operator, $value2))
				return false;
		}
		return true;
	}
	
	function date_comparison_to_delivery($field=[], $operator) {
		foreach( $field as $key => $value ){
			if(isset($this->data[$this->alias]['delivery_id'])) { // capita se l'elenco delle consegne è vuoto
				$delivery_id = $this->data[$this->alias]['delivery_id'];
				$organization_id = $this->data[$this->alias]['organization_id'];
				 
				App::import('Model', 'Delivery');
				$Delivery = new Delivery;
			
				$Delivery->unbindModel(array('hasMany' => array('Order','Cart')));
				$delivery = $Delivery->read($delivery_id, $organization_id, 'data');
				$delivery_data = $delivery['Delivery']['data'];
			
				if (!Validation::comparison($delivery_data, $operator, $value))
					return false;
			}
			else
				return false;
		}
		return true;		
	}
	
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
			'conditions' => 'Order.organization_id = ProdGasPromotionsOrganizationsManager.organization_id',
			'fields' => '',
			'order' => ''
		),
	);
	
	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {
			if(!empty($val)) {				
		
				if (isset($val['ProdGasPromotionsOrganizationsManager']['trasport'])) {
					$results[$key]['ProdGasPromotionsOrganizationsManager']['trasport_'] = number_format($val['ProdGasPromotionsOrganizationsManager']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasPromotionsOrganizationsManager']['trasport_e'] = $results[$key]['ProdGasPromotionsOrganizationsManager']['trasport_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['trasport'])) {
					$results[$key]['trasport_'] = number_format($val['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['trasport_e'] = $results[$key]['trasport_'].' &euro;';
				}
		
				if (isset($val['ProdGasPromotionsOrganizationsManager']['cost_more'])) {
					$results[$key]['ProdGasPromotionsOrganizationsManager']['cost_more_'] = number_format($val['ProdGasPromotionsOrganizationsManager']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasPromotionsOrganizationsManager']['cost_more_e'] = $results[$key]['ProdGasPromotionsOrganizationsManager']['cost_more_'].' &euro;';
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
}