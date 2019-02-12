<?php
App::uses('AppModel', 'Model');

class ProdGasPromotion extends AppModel {

    public $name = 'ProdGasPromotion';

	/*
	 * ctrl se puo' gestire le promozioni
	 * 	Supplier.can_promotions - come produttore
	 *  SuppliersOrganizaton.can_promotions - per il dato gas
	 */ 
	public function canPromotions($user, $organization_id=0, $debug=false) {
	
		$canPromotions = false;
		
		/*
		 * ctrl a livello di produttore 
		 *
		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
		
		$options = [];
		$options['conditions'] = ['Supplier.id' => $user->organization['Supplier']['Supplier']['id']];
		$options['fields'] = ['Supplier.can_promotions'];
		$options['recursive'] = -1;
		$supplierResults = $Supplier->find('first', $options);
		self::d($options, $debug);
		self::d($supplierResults, $debug);
		if(empty($supplierResults))
			return false;
		 */
		 
		self::d($user->organization['Supplier'], $debug);
		if($user->organization['Supplier']['Supplier']['can_promotions']!='Y') 
			return false;
					
		/*
		 * ctrl a livello di GAS 
		 */
		 if(!empty($organization_id)) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.supplier_id' => $user->organization['Supplier']['Supplier']['id'], 
									  'SuppliersOrganization.organization_id' => $organization_id];
			$options['fields'] = ['SuppliersOrganization.can_promotions'];
			$options['recursive'] = -1;
			$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
			self::d($options, $debug);
			self::d($suppliersOrganizationResults, $debug);			
			if(empty($suppliersOrganizationResults))
				return false;
				
			if($suppliersOrganizationResults['SuppliersOrganization']['can_promotions']=='N')
				return false;		 
		 }		

		 return true;
	}
	
	/*
	 * estrae tutti i dati di una promozione
	 * ProdGasPromotion / Supplier 
	 *
	 * organization_id filtra per la promozione per il GAS passato
	 */
	public function getProdGasPromotion($user, $prod_gas_promotion_id, $organization_id=0, $debug=false) {
	
		$results = [];
		
		/*
		 * dati promozione 
		 */
		$options = [];
		$options['conditions'] = ['ProdGasPromotion.id' => $prod_gas_promotion_id];
		$options['recursive'] = -1;
		$results = $this->find('first', $options);
		self::d($results, $debug);
		
		/*
		 * dati produttore 
		 */
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;
		
		$supplierResults = $ProdGasSupplier->getOrganizationSupplier($user, $results['ProdGasPromotion']['organization_id']);
		self::d($supplierResults, $debug); 

		$results += $supplierResults;
		
		/* 
		 * ProdGasPromotionsOrganization per spese trasporto, costi aggiuntivi + Order
		 */
		App::import('Model', 'ProdGasPromotionsOrganization');
		$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;
		
		$ProdGasPromotionsOrganization->unbindModel(['belongsTo' => ['Order', 'Organization']]);
	
		$options = [];
		$options['conditions'] = ['ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id];
		$options['recursive'] = -1;							   
		if(!empty($organization_id)) {
			$options['conditions'] += ['ProdGasPromotionsOrganization.organization_id' => $organization_id];
			$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('first', $options);
			
			$results['ProdGasPromotionsOrganization'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization'];
		}
		else {
			$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('all', $options);
			$results['ProdGasPromotionsOrganization'] = $prodGasPromotionsOrganizationResults;
		}
		
		self::d($prodGasPromotionsOrganizationResults, $debug); 	

		/* 
		 * articoli i promozione
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
		
		$results['ProdGasArticlesPromotion'] = $prodGasArticlesPromotionResults;
		 
		return $results;
	}
		 
	/*
	 * estrae tutti i GAS di un produttore
	 * con $prod_gas_promotion_id estraggo i GAS inseriti nella promozione (ProdGasPromotionsOrganization)
	 */
	public function getOrganizationsAssociate($user, $prod_gas_promotion_id=0, $debug=false) {

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$SuppliersOrganization->unbindModel(['belongsTo' => ['Supplier', 'CategoriesSupplier']]);
		$SuppliersOrganization->unbindModel(['hasMany' => ['Article', 'Order', 'SuppliersOrganizationsReferent']]);
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.supplier_id' => $user->organization['Supplier']['Supplier']['id'],
								  'SuppliersOrganization.stato' => 'Y'];
		$options['order'] = ['SuppliersOrganization.name'];
		$options['recursive'] = 1;
		$results = $SuppliersOrganization->find('all', $options);
		
		/* 
		 * ProdGasPromotionsOrganization per spese trasporto, costi aggiuntivi + Order
		 */
		if($prod_gas_promotion_id>0) {
			App::import('Model', 'ProdGasPromotionsOrganization');

			foreach($results as $numResult => $result) {
				$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;

				$options = [];
				$options['conditions'] = ['ProdGasPromotionsOrganization.supplier_id' => $user->organization['Supplier']['Supplier']['id'],
										  'ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id,
										  'ProdGasPromotionsOrganization.organization_id' => $result['SuppliersOrganization']['organization_id']];
				$options['recursive'] = -1;
				$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('first', $options);

				self::d($prodGasPromotionsOrganizationResults, $debug);	
				
				if(!empty($prodGasPromotionsOrganizationResults)) 
					$results[$numResult]['ProdGasPromotionsOrganization'] = $prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization'];
			}			


		}
		
		self::d($results, $debug);	
		
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
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.supplier_id' => $user->organization['Supplier']['Supplier']['id'],
								   'SuppliersOrganization.stato' => 'Y'];
		$options['fields'] = ['SuppliersOrganization.organization_id'];
		$options['order'] = ['SuppliersOrganization.id'];
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
				
				$options = [];
				$options['conditions'] = ['Organization.id NOT IN ("'.$organization_ids .'")',
										  'Organization.type' => 'GAS'];
				$options['order'] = ['Organization.name'];
				$options['recursive'] = -1;
				$results = $Organization->find('all', $options);
				
			} // if(!empty($organization_ids))
			
		} // end if(!empty($results))
		
		self::d($results, $debug);					
		
		return $results;	
	}

	public function settingStateCode($user, $prod_gas_promotion_id, $next_state, $debug=false) {

		if(empty($prod_gas_promotion_id) || empty($next_state))
			return false;
		
		$sql = "UPDATE 
					".Configure::read('DB.prefix')."prod_gas_promotions
				SET
					state_code = '".$next_state."',
					modified = '".date('Y-m-d H:i:s')."'
				WHERE
				    organization_id = ".$user->organization['Organization']['id']." and id = ".(int)$prod_gas_promotion_id;
		self::d($sql, $debug);
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			self::d($sql, $debug);
			self::d($e, $debug);

			return false;
		}
		return true;
	}
	
	public $hasMany = [
		'ProdGasPromotionsOrganization' => [
				'className' => 'ProdGasPromotionsOrganization',
				'foreignKey' => 'prod_gas_promotion_id',
				'dependent' => false,
				'conditions' =>  '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'exclusive' => '',
				'finderQuery' => '',
				'counterQuery' => ''
		],
		'ProdGasArticlesPromotion' => [
				'className' => 'ProdGasArticlesPromotion',
				'foreignKey' => 'prod_gas_promotion_id',
				'dependent' => false,
				'conditions' => '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'exclusive' => '',
				'finderQuery' => '',
				'counterQuery' => ''
		]
	];
	
	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {

			if(!empty($val)) {
				if (isset($val['ProdGasPromotion']['data_inizio'])) {
					$results[$key]['ProdGasPromotion']['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['ProdGasPromotion']['data_inizio']);
					$results[$key]['ProdGasPromotion']['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['ProdGasPromotion']['data_fine']);
					
					$results[$key]['ProdGasPromotion']['data_inizio_'] = date('d',strtotime($val['ProdGasPromotion']['data_inizio'])).'/'.date('n',strtotime($val['ProdGasPromotion']['data_inizio'])).'/'.date('Y',strtotime($val['ProdGasPromotion']['data_inizio']));
					$results[$key]['ProdGasPromotion']['data_fine_'] = date('d',strtotime($val['ProdGasPromotion']['data_fine'])).'/'.date('n',strtotime($val['ProdGasPromotion']['data_fine'])).'/'.date('Y',strtotime($val['ProdGasPromotion']['data_fine']));					
					
					$results[$key]['ProdGasPromotion']['prod_gas_promotion_data_fine'] = $val['ProdGasPromotion']['data_fine'];  // rinomino perche' quando creo un ordine ho Order.data_fine
				}
				else 
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				 */
				if (isset($val['data_inizio'])) {
					$results[$key]['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['data_inizio']);
					$results[$key]['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['data_fine']);
												
					$results[$key]['data_inizio_'] = date('d',strtotime($val['data_inizio'])).'/'.date('n',strtotime($val['data_inizio'])).'/'.date('Y',strtotime($val['data_inizio']));
					$results[$key]['data_fine_'] = date('d',strtotime($val['data_fine'])).'/'.date('n',strtotime($val['data_fine'])).'/'.date('Y',strtotime($val['data_fine']));
					
					$results[$key]['prod_gas_promotion_data_fine'] = $val['data_fine']; // rinomino perche' quando creo un ordine ho Order.data_fine
				}

								
				if (isset($val['ProdGasPromotion']['importo_originale'])) {
					$results[$key]['ProdGasPromotion']['importo_originale_'] = number_format($val['ProdGasPromotion']['importo_originale'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasPromotion']['importo_originale_e'] = $results[$key]['ProdGasPromotion']['importo_originale_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['importo_originale'])) {
					$results[$key]['importo_originale_'] = number_format($val['importo_originale'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['importo_originale_e'] = $results[$key]['importo_originale_'].' &euro;';
				}
				
				if (isset($val['ProdGasPromotion']['importo_scontato'])) {
					$results[$key]['ProdGasPromotion']['importo_scontato_'] = number_format($val['ProdGasPromotion']['importo_scontato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasPromotion']['importo_scontato_e'] = $results[$key]['ProdGasPromotion']['importo_scontato_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['importo_scontato'])) {
					$results[$key]['importo_scontato_'] = number_format($val['importo_scontato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['importo_scontato_e'] = $results[$key]['importo_scontato_'].' &euro;';
				}				
			}				
		}
		
		return $results;
	}

	public function beforeValidate($options = []) {
		 
		if (!empty($this->data['ProdGasPromotion']['data_inizio']))
			$this->data['ProdGasPromotion']['data_inizio'] = $this->data['ProdGasPromotion']['data_inizio_db'];

		if (!empty($this->data['ProdGasPromotion']['data_fine']))
			$this->data['ProdGasPromotion']['data_fine'] = $this->data['ProdGasPromotion']['data_fine_db'];
		
		return true;
	}
		
	public function beforeSave($options = []) {
		if (!empty($this->data['ProdGasPromotion']['data_inizio'])) 
	    	$this->data['ProdGasPromotion']['data_inizio'] = $this->data['ProdGasPromotion']['data_inizio_db'];

		if (!empty($this->data['ProdGasPromotion']['data_fine']))
			$this->data['ProdGasPromotion']['data_fine'] = $this->data['ProdGasPromotion']['data_fine_db'];

		if (!empty($this->data['ProdGasPromotion']['importo_originale']))
			$this->data['ProdGasPromotion']['importo_originale'] = $this->importoToDatabase($this->data['ProdGasPromotion']['importo_originale']);

		if (!empty($this->data['ProdGasPromotion']['importo_scontato']))
			$this->data['ProdGasPromotion']['importo_scontato'] = $this->importoToDatabase($this->data['ProdGasPromotion']['importo_scontato']);
		
	    return true;
	}	
}