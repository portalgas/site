<?php
App::uses('AppModel', 'Model');


class CashesUser extends AppModel {
    
    /*
     * somma di quanto un gasista ha acquistato e non ancora saldato
	 * - tutti gli acquisti di ordini non associati a summary_orders (ordini aperti, prima della consegna etc)
	 * - tutti gli acquisti di ordini associati a summary_orders con saldato_a IS NULL (non ancora saldato)
	 *
	 * escludo gli acquisti effettuati da produttori in supplier_organization_cash_excludeds
     */
    public function getTotImportoAcquistato($user, $user_id, $debug=false) {

		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;
		
		$stateCodeUsersCash = $OrderLifeCycle->getStateCodeUsersCash($user);
		$stateCodeUsersCash = "'".implode("','", $stateCodeUsersCash)."'";
		
    	$tot_importo = '0.00';
		$zero = floatval(0);
		
		/*
		 * escludo gli acquisti effettuati da produttori in supplier_organization_cash_excludeds
	     */		
        $supplierOrganizationCashExcludedResults = $this->getSupplierOrganizationCashExcludedIds($user, $user->organization['Organization']['id'], $debug);
        $sql_supplier_organization_cash_excluded = '';
		if(!empty($supplierOrganizationCashExcludedResults)) {
			foreach($supplierOrganizationCashExcludedResults as $supplierOrganizationCashExcludedResult) {
				$sql_supplier_organization_cash_excluded .= $supplierOrganizationCashExcludedResult.',';
			}
			if(!empty($sql_supplier_organization_cash_excluded))
				$sql_supplier_organization_cash_excluded = substr($sql_supplier_organization_cash_excluded, 0, (strlen($sql_supplier_organization_cash_excluded)-1));

				$sql_supplier_organization_cash_excluded = ' AND `Order`.supplier_organization_id NOT IN ('.$sql_supplier_organization_cash_excluded.')';
		} // end if(!empty($supplierOrganizationCashExcludedResults))
		if($debug) debug($sql_supplier_organization_cash_excluded);

		$sql = "SELECT
					ArticlesOrder.prezzo, Cart.qta_forzato, Cart.qta, Cart.importo_forzato
				FROM
					".Configure::read('DB.prefix')."articles_orders as ArticlesOrder, ".Configure::read('DB.prefix')."orders as `Order`,
					".Configure::read('DB.prefix')."carts as Cart
					 LEFT JOIN ".Configure::read('DB.prefix')."summary_orders as SummaryOrder ON 
					(SummaryOrder.organization_id = ".(int)$user->organization['Organization']['id']." and SummaryOrder.user_id = Cart.user_id and SummaryOrder.order_id = Cart.order_id and SummaryOrder.saldato_a is null)
				WHERE
					ArticlesOrder.organization_id = ".(int)$user->organization['Organization']['id']."
				    and `Order`.organization_id = ".(int)$user->organization['Organization']['id']."
				    and Cart.organization_id = ".(int)$user->organization['Organization']['id']."
				    and Cart.user_id = $user_id
				    and Cart.order_id = `Order`.id  
				    and Cart.article_organization_id = ArticlesOrder.article_organization_id
				    and Cart.article_id = ArticlesOrder.article_id  
				    and ArticlesOrder.order_id = `Order`.id  
				    and Cart.deleteToReferent = 'N' 
				    and `Order`.isVisibleBackOffice = 'Y'
					and `Order`.state_code not in ($stateCodeUsersCash)";
		if(!empty($sql_supplier_organization_cash_excluded))
			$sql .= $sql_supplier_organization_cash_excluded;
		if($debug) debug($sql); 
		$results = $this->query($sql);

		foreach($results as $numResult => $result) {

			$prezzo = floatval($result['ArticlesOrder']['prezzo']);
			$qta_forzato = floatval($result['Cart']['qta_forzato']);
			
			if($qta_forzato > $zero) {
				$qta = $qta_forzato;
			}
			else {
				$qta = floatval($result['Cart']['qta']);
			}

			$importo_forzato = floatval($result['Cart']['importo_forzato']);
				
			if($importo_forzato==$zero) {
				if($qta_forzato>$zero) 
					$importo = ($qta_forzato * $prezzo);
				else {
					$importo = (floatval($result['Cart']['qta']) * $prezzo);
				}
			}
			else {
				$importo = $importo_forzato;
			}
			
			$tot_importo = ($tot_importo + $importo);
			if($debug) debug('CashesUser::getTotImportoAcquistato - tot_importo '.$tot_importo);
		} // end foreach($results as $numResult => $result)
				
		if($debug) debug('CashesUser::getTotImportoAcquistato - RESULTS '.$tot_importo);

		return floatval($tot_importo);
    }

	/*
	 * dettaglio degli ordini con acquisti 
	 *
	 * escludo gli acquisti effettuati da produttori in supplier_organization_cash_excludeds
	 */ 
    public function getTotImportoAcquistatoDetails($user, $user_id, $debug=false) {

		if($debug) debug('CashesUser::getTotImportoAcquistatoDeatils');

		/*
		 * escludo gli acquisti effettuati da produttori in supplier_organization_cash_excludeds
	     */		
        $supplierOrganizationCashExcludedResults = $this->getSupplierOrganizationCashExcludedIds($user, $user->organization['Organization']['id'], $debug);
        $sql_supplier_organization_cash_excluded = '';
		if(!empty($supplierOrganizationCashExcludedResults)) {
			foreach($supplierOrganizationCashExcludedResults as $supplierOrganizationCashExcludedResult) {
				$sql_supplier_organization_cash_excluded .= $supplierOrganizationCashExcludedResult.',';
			}
			if(!empty($sql_supplier_organization_cash_excluded))
				$sql_supplier_organization_cash_excluded = substr($sql_supplier_organization_cash_excluded, 0, (strlen($sql_supplier_organization_cash_excluded)-1));

				$sql_supplier_organization_cash_excluded = ' AND `Order`.supplier_organization_id NOT IN ('.$sql_supplier_organization_cash_excluded.')';
		} // end if(!empty($supplierOrganizationCashExcludedResults))
		if($debug) debug($sql_supplier_organization_cash_excluded);

		$sql = "SELECT
					`Order`.data_inizio, `Order`.data_fine, `Order`.data_fine_validation, `Order`.state_code, 
					Delivery.luogo, Delivery.data, SuppliersOrganization.name
				FROM
					".Configure::read('DB.prefix')."deliveries as Delivery,
					".Configure::read('DB.prefix')."suppliers_organizations as SuppliersOrganization,
					".Configure::read('DB.prefix')."orders as `Order`,
					".Configure::read('DB.prefix')."carts as Cart
					 LEFT JOIN ".Configure::read('DB.prefix')."summary_orders as SummaryOrder ON 
					(SummaryOrder.organization_id = ".(int)$user->organization['Organization']['id']." and SummaryOrder.user_id = Cart.user_id and SummaryOrder.order_id = Cart.order_id and SummaryOrder.saldato_a is null)
				WHERE
					`Order`.organization_id = ".(int)$user->organization['Organization']['id']."
				    and Delivery.organization_id = ".(int)$user->organization['Organization']['id']."
				    and SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
				    and Cart.organization_id = ".(int)$user->organization['Organization']['id']."
				    and Cart.user_id = ".$user_id."
				    and Cart.order_id = `Order`.id  
				    and Cart.deleteToReferent = 'N' 
				    and `Order`.isVisibleBackOffice = 'Y'  
				    and Delivery.id = `Order`.delivery_id   
				    and SuppliersOrganization.id = `Order`.supplier_organization_id ";
		if(!empty($sql_supplier_organization_cash_excluded))
			$sql .= $sql_supplier_organization_cash_excluded;				    
		$sql .= " GROUP BY `Order`.id 
				    ORDER BY Delivery.data asc, SuppliersOrganization.name";
		if($debug) debug($sql); 
		$results = $this->query($sql);
			
		if($debug) debug($results);

		return $results;
    }
        
    /* 
     * estrae i dati caricati alla login dell'utente in AppController
     */
    public function getUserData($user) {
		
		$results = [];
		
		/*
		 * configurazione Organization
		 */
		$results['organization_cash_limit'] = $user->get('organization_cash_limit');
		$results['organization_cash_limit_label'] = $user->get('organization_cash_limit_label');
		$results['organization_limit_cash_after'] = floatval($user->get('organization_limit_cash_after'));
		$results['organization_limit_cash_after_'] = $user->get('organization_limit_cash_after_');
		$results['organization_limit_cash_after_e'] = $user->get('organization_limit_cash_after_e');

	
		/*
		 * configurazione CashesUser
		 */					
		$results['user_limit_type'] = $user->get('user_limit_type');
		$results['user_limit_type_label'] = __('FE-'.$user->get('user_limit_type'));
		$results['user_limit_after'] = floatval($user->get('user_limit_after'));
		$results['user_limit_after_'] = $user->get('user_limit_after_');
		$results['user_limit_after_e'] = $user->get('user_limit_after_e');
			
		/*
		 * totale cassa
		 */
		$results['user_cash'] = floatval($user->get('user_cash'));
		$results['user_cash_'] = $user->get('user_cash_');
		$results['user_cash_e'] = $user->get('user_cash_e');
		
		return $results;
	}

	/*
	 * elenco ids produttori esclusi dalla gestione della cassa dell'utente
	 */ 
    public function getSupplierOrganizationCashExcludedIds($user, $organization_id, $debug=false) {

    	$results = [];

        if($user->organization['Organization']['hasCashFilterSupplier']=='N')
            return $results;

        App::import('Model', 'SupplierOrganizationCashExcluded');
        $SupplierOrganizationCashExcluded = new SupplierOrganizationCashExcluded;

        $options = [];
        $options['conditions'] = ['organization_id' => $organization_id];
        if($debug) debug($options);
        /*
         * recurvice 1 da errore!!!
         */
        $results = $SupplierOrganizationCashExcluded->find('all', $options);
        if(!empty($results)) {
        	$newResults = [];
        	foreach($results as $result) {
        		$newResults[] = $result['SupplierOrganizationCashExcluded']['supplier_organization_id'];
        	}

        	$results = $newResults;
        }
        if($debug) debug($results);
        return $results;
    } 

	/*
	 * ctrl se il produttore dell'ordine ha la gestione della cassa dell'utente
	 */ 
    public function isSupplierOrganizationCashExcluded($user, $organization_id, $supplier_organization_id, $debug=false) {

        if($user->organization['Organization']['hasCashFilterSupplier']=='N')
            return false;

        App::import('Model', 'SupplierOrganizationCashExcluded');
        $SupplierOrganizationCashExcluded = new SupplierOrganizationCashExcluded;

        $options = [];
        $options['conditions'] = ['organization_id' => $organization_id,
                                  'supplier_organization_id' => $supplier_organization_id];
        if($debug) debug($options);
        /*
         * recurvice 1 da errore!!!
         */        
        $supplierOrganizationCashExcludedResults = $SupplierOrganizationCashExcluded->find('first', $options);

        self::d($supplierOrganizationCashExcludedResults, $debug);
        if(empty($supplierOrganizationCashExcludedResults))
        	return false;
        else
        	return true;
    } 

	/* 
	 * dato un acquisto ctrl se lo user puo' acquistarlo
	 */
    public function ctrlLimitCart($user, $supplier_organization_id, $qta_prima_modifica, $qta, $prezzo, $debug=false) {

        if($this->isSupplierOrganizationCashExcluded($user, $user->organization['Organization']['id'], $supplier_organization_id, $debug))
        	return true;

		$results = []; 	
		$results = $this->getUserData($user);

		if($debug) debug('organization_cash_limit '.$results['organization_cash_limit']);
		
		if($results['organization_cash_limit']=='LIMIT-NO')
			return true;
			
		if($results['organization_cash_limit']=='LIMIT-CASH-USER' && $results['user_limit_type']=='LIMIT-NO')
			return true;
			
		/*
		 * quanto e' stato acquistato
		 * devo calcolare come qta solo da differenza, se avava acquistato 2 e aumenta a 4 => 4 - 2 
		 */
		$zero = floatval(0);
		$qta = floatval($qta);
		$qta_prima_modifica = floatval($qta_prima_modifica);
		if($qta > $qta_prima_modifica)
			$qta_diff = ($qta - $qta_prima_modifica);
		else 
			$qta_diff = ($qta);
	
		$prezzo = floatval($prezzo);
		$cart_importo = ($qta_diff * $prezzo);
						
		 /*
		  * totale importo acquisti
		  */
		$results['user_tot_importo_acquistato'] = $this->getTotImportoAcquistato($user, $user->id /*, $debug */);
		
		if($debug) {
			debug('user_cash '.$results['user_cash']);
			debug('user_tot_importo_acquistato '.$results['user_tot_importo_acquistato']);
			debug('cart_importo (qta_diff * prezzo) '.' ('.$qta_diff.' * '.$prezzo.') '.$cart_importo);
		}
				
		if($results['organization_cash_limit']=='LIMIT-CASH') {
			if(($results['user_cash'] - ($results['user_tot_importo_acquistato'] + $cart_importo)) < 0 )
				return false;
		}
			
		if($results['organization_cash_limit']=='LIMIT-CASH-AFTER') {
			if((($results['user_cash'] + $results['organization_limit_cash_after'] ) - ($results['user_tot_importo_acquistato'] + $cart_importo)) < 0 )
				return false;
			else			
				return true;
		}

		if($results['organization_cash_limit']=='LIMIT-CASH-USER') {
		
			if($debug) debug('user_limit_type '.$results['user_limit_type']);
		
			if($results['user_limit_type']=='LIMIT-CASH') {
			
				$delta = ($results['user_cash'] - ($results['user_tot_importo_acquistato'] + $cart_importo));
				
				if($debug) debug('ctrlLimitCart => delta '.$delta);

				if($delta < $zero)
					return false;
				else			
					return true;
			}
			
			if($results['user_limit_type']=='LIMIT-CASH-AFTER') {

				if($debug) debug('user_limit_after '.$results['user_limit_after']);

				$delta = (($results['user_cash'] + $results['user_limit_after'] ) - ($results['user_tot_importo_acquistato'] + $cart_importo));
				
				if($debug) debug('delta '.$delta);
				/*
				echo '<br >user_cash '.$results['user_cash'].' '.gettype($results['user_cash']);
				echo '<br >user_limit_after '.$results['user_limit_after'].' '.gettype($results['user_limit_after']);
				echo '<br >user_tot_importo_acquistato '.$results['user_tot_importo_acquistato'].' '.gettype($results['user_tot_importo_acquistato']);
				echo '<br >cart_importo '.$cart_importo.' '.gettype($cart_importo);
				
				echo '<br >delta '.$delta.' '.gettype($delta);
				echo '<br >qta '.$qta.' '.gettype($qta);
				echo '<br >prezzo '.$prezzo.' '.gettype($prezzo);
				echo '<br >zero '.$zero.' '.gettype($zero);
				*/
				if($delta < $zero) {
					if($debug) debug('FALSE delta < zero');
					return false;	
				}	
				else {
					if($debug) debug('TRUE delta > zero');		
					return true;
				}		
			}
			
			$delta = ($results['user_cash'] - ($results['user_tot_importo_acquistato'] + $cart_importo)); 
			if($delta < $zero)
				return false;
		}		

		if($debug) debug('ctrlLimitCart OK');

		return true;		
	}
	  
    public function ctrlLimit($user, $organization_cashLimit, $organization_limitCashAfter=0, $cashesUser, $tot_importo_cash=0, $tot_importo_acquistato=0, $debug=false) {
		
		$results = [];

		if(isset($cashesUser['CashesUser']))
			$cashesUser = $cashesUser['CashesUser'];
		
		if($debug) {
			debug("organization_cashLimit ".$organization_cashLimit);
			debug("organization_limitCashAfter ".$organization_limitCashAfter);
			debug("tot_importo_cash ".$tot_importo_cash);
			debug("tot_importo_acquistato ".$tot_importo_acquistato);
			debug("cashesUser");
			debug($cashesUser);
		}

		 /*
		  * totale importo acquisti
		  */
		$user_tot_importo_acquistato = $this->getTotImportoAcquistato($user, $user->id);
		$user_tot_importo_acquistato_ = number_format($user_tot_importo_acquistato ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		if($user_tot_importo_acquistato==0)
			$results['fe_msg_tot_acquisti'] = 'Non hai ancora effettuato acquisti';
		else
			$results['fe_msg_tot_acquisti'] = 'Hai acquistato per '.$user_tot_importo_acquistato_.'&nbsp;&euro;';

		// 
     	switch($organization_cashLimit) {
    		case "LIMIT-NO":
    			$results['importo'] = 0; // (floatval($tot_importo_cash) - floatval($tot_importo_acquistato));
		    	$results['importo_'] = number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$results['importo_e']  = $results['importo_'] .'&nbsp;&euro;'; 

    			$results['stato'] = 'GREEN';
    			$results['fe_msg'] = 'Nessun limite per gli acquisti';
    			$results['fe_msg_tot_acquisti'] = '';

				$results['has_fido'] = false;
    		break;
    		case "LIMIT-CASH":
    			$results['importo'] = (floatval($tot_importo_cash) - floatval($tot_importo_acquistato));
		    	$results['importo_'] = number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$results['importo_e']  = $results['importo_'] .'&nbsp;&euro;'; 

    			if($results['importo']<0) {
	    			$results['stato'] = 'RED';
					$results['fe_msg'] = 'Hai esaurito il credito di cassa! ('.$results['importo_e'].')';
	    		}
				else
	    		if($results['importo']>0) {
	    			$results['stato'] = 'GREEN';
					$results['fe_msg'] = 'Puoi fare acquisti per '.$results['importo_e']; 
	    		}
				else
	    		if($results['importo']==0) {
	    			$results['stato'] = 'YELLOW';
					$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!'; 
				}

				$results['has_fido'] = false;
    		break;
    		case "LIMIT-CASH-AFTER":
    			$results['importo'] = (floatval($tot_importo_cash) - floatval($tot_importo_acquistato) + floatval($organization_limitCashAfter));
		    	$results['importo_'] = number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$results['importo_e']  = $results['importo_'] .'&nbsp;&euro;'; 

    			if($results['importo']<0) {
	    			$results['stato'] = 'RED';
					$results['fe_msg'] = 'Hai esaurito il credito di cassa! ('.$results['importo_e'].')';
	    		}
				else
	    		if($results['importo']>0) {
	    			$results['stato'] = 'GREEN';
					$results['fe_msg'] = 'Puoi fare acquisti per '.$results['importo_e']; 
	    		}
				else
	    		if($results['importo']==0) {
	    			$results['stato'] = 'YELLOW';
					$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!';
				}	

				$results['has_fido'] = true;
				$results['importo_fido'] = $results['importo'];
				$results['importo_fido_'] = $results['importo_'];
				$results['importo_fido_e'] = $results['importo_e'];
    		break;
    		case "LIMIT-CASH-USER":
			
				/*
				 * puo' essere se vuoto se si e' scelto "Limite per ogni gasista" ma poi non ho salvato
				 */
				if(empty($cashesUser))
					$cashesUser['limit_type'] = 'LIMIT-NO';
					
				/*
				 * singolo User
				 */
		    	switch($cashesUser['limit_type']) {
		    		case "LIMIT-NO":
		    			$results['importo'] = 0; // (floatval($tot_importo_cash) - floatval($tot_importo_acquistato));
				    	$results['importo_'] = number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$results['importo_e']  = $results['importo_'] .'&nbsp;&euro;'; 

		    			$results['stato'] = 'GREEN';
						$results['fe_msg'] = 'Nessun limite per gli acquisti';
    					$results['fe_msg_tot_acquisti'] = '';

						$results['has_fido'] = false;
		    		break;
		    		case "LIMIT-CASH":
		    			$results['importo'] = (floatval($tot_importo_cash) - floatval($tot_importo_acquistato));
				    	$results['importo_'] = number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$results['importo_e']  = $results['importo_'] .'&nbsp;&euro;'; 

		    			if($results['importo']<0) {
			    			$results['stato'] = 'RED';
						$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa! ('.$results['importo_e'].')';
			    		}
						else
			    		if($results['importo']>0) {
			    			$results['stato'] = 'GREEN';
							$results['fe_msg'] = 'Puoi fare acquisti per '.$results['importo_e']; 
			    		}
						else
			    		if($results['importo']==0) {
			    			$results['stato'] = 'YELLOW';
							$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!';
						}

						$results['has_fido'] = false;
		    		break;
		    		case "LIMIT-CASH-AFTER":
		    			$results['importo'] = (floatval($tot_importo_cash) - floatval($tot_importo_acquistato) + floatval($cashesUser['limit_after']));
				    	$results['importo_'] = number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						$results['importo_e']  = $results['importo_'] .'&nbsp;&euro;'; 

		    			if($results['importo']<0) {
			    			$results['stato'] = 'RED';
							$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa! ('.$results['importo_e'].')';
			    		}
			    		else
			    		if($results['importo']>0) {
			    			$results['stato'] = 'GREEN';
							$results['fe_msg'] = 'Puoi fare acquisti per '.$results['importo_e']; 
			    		}
			    		else
			    		if($results['importo']==0) {
			    			$results['stato'] = 'YELLOW'; 
							$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!'; 
			    		}  

						$results['has_fido'] = true;
						$results['importo_fido'] = $results['importo'];
						$results['importo_fido_'] = $results['importo_'];
						$results['importo_fido_e'] = $results['importo_e'];
		    		break;
		    	}				 
    		break;
    	}

		if($debug) debug($results);
				
    	return $results;
    } 
	
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'CashesUser.organization_id = User.organization_id',
			'fields' => '',
			'order' => ''
		)
	);	
	
	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {
			if(!empty($val)) {				
				if (isset($val['CashesUser']['limit_after'])) {
					$results[$key]['CashesUser']['limit_after_'] = number_format($val['CashesUser']['limit_after'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['CashesUser']['limit_after_e'] = $results[$key]['CashesUser']['limit_after_'].' &euro;';
				}				
			}
		}
		return $results;
	}
	
	public function beforeSave($options = []) {
		if (!empty($this->data['CashesUser']['limit_after']))
			$this->data['CashesUser']['limit_after'] = $this->importoToDatabase($this->data['CashesUser']['limit_after']);
	
		return true;
	}	
}