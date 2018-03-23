<?php
App::uses('AppModel', 'Model');

class CashesUser extends AppModel {
    
    /*
     * somma di quanto un gasista ha acquistato per le consegne ancora aperte
     */
    public function getTotImportoAcquistato($user, $user_id, $debug=false) {
    
    	$tot_importo = '0.00';
		$zero = floatval(0);

		App::import('Model', 'Order');
		$Order = new Order;
    	
    	$Order->unbindModel(array('belongsTo' => array('SuppliersOrganization')));
    	
		App::import('Model', 'Cart');
		$Cart = new Cart;
    
        $Cart->unbindModel(array('belongsTo' => array('User', 'Article', 'Order')));
    
    	$options = [];
		$options['conditions'] = ['Cart.organization_id' => $user->organization['Organization']['id'],
								  'Cart.user_id' => $user_id,
								  'Cart.deleteToReferent' => 'N'];
	    $options['recursive'] = 1;
		$results = $Cart->find('all', $options);
		if($debug) {
			echo "<pre>CashesUser::getTotImportoAcquistato - CART \n ";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}
			
		foreach($results as $numResult => $result) {

			/*
			 * escludo le consegne chiuse
			 */
		   	$options = [];
			$options['conditions'] = ['Delivery.organization_id' => $user->organization['Organization']['id'],
									  'Delivery.isVisibleFrontEnd' => 'Y',
									  'Delivery.stato_elaborazione' => 'OPEN',
									 // 'DATE(Delivery.data) >=' => 'CURDATE()',
									  'Order.id' => $result['Cart']['order_id']
									  ];
		    $options['recursive'] = 0;
			$orderResults = $Order->find('first', $options);
			if($debug) {
				echo "<pre>CashesUser::getTotImportoAcquistato - ORDERS con consegne ancora aperte \n ";
				print_r($options);
				print_r($orderResults);
				echo "</pre>";
			}
			
			if(!empty($orderResults)) {
			
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
				if($debug) {
					echo "<pre>CashesUser::getTotImportoAcquistato - tot_importo \n ";
					print_r($tot_importo);
					echo "</pre>";
				}				
			} // end if(!empty($orderResults)) 
						
		} // end loop
		
		if($debug) {
			echo "<pre>CashesUser::getTotImportoAcquistato - RESULTS \n ";
			print_r($tot_importo);
			echo "</pre>";
		}

	
		return floatval($tot_importo);
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
	 * dato un acquisto ctrl se lo user puo' acquistarlo
	 */
    public function ctrlLimitCart($user, $qta_prima_modifica, $qta, $prezzo, $debug=false) {

		$results = []; 	
		$results = $this->getUserData($user);

		if($debug)
			echo '<br />organization_cash_limit '.$results['organization_cash_limit'];
		
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
			echo "<pre>";
			echo '<br />user_cash '.$results['user_cash'];
			echo '<br />user_tot_importo_acquistato '.$results['user_tot_importo_acquistato'];
			echo '<br />cart_importo (qta_diff * prezzo) '.' ('.$qta_diff.' * '.$prezzo.') '.$cart_importo;
			echo "</pre>";
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
		
			if($debug)
				echo '<br />user_limit_type '.$results['user_limit_type'];
		
			if($results['user_limit_type']=='LIMIT-CASH') {
			
				$delta = ($results['user_cash'] - ($results['user_tot_importo_acquistato'] + $cart_importo));
				
				if($debug)
					echo '<br />ctrlLimitCart => delta '.$delta;

				if($delta < $zero)
					return false;
				else			
					return true;
			}
			
			if($results['user_limit_type']=='LIMIT-CASH-AFTER') {

				if($debug)
					echo '<br />user_limit_after '.$results['user_limit_after'];

				$delta = (($results['user_cash'] + $results['user_limit_after'] ) - ($results['user_tot_importo_acquistato'] + $cart_importo));
				
				if($debug)
					echo '<br />delta '.$delta;
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
					if($debug)
						echo '<br >FALSE delta < zero ';
					return false;	
				}	
				else {
					if($debug)
						echo '<br >TRUE delta > zero ';		
					return true;
				}		
			}
			
			$delta = ($results['user_cash'] - ($results['user_tot_importo_acquistato'] + $cart_importo)); 
			if($delta < $zero)
				return false;
		}		

		if($debug)
			echo '<br />ctrlLimitCart OK ';

		return true;		
	}
	  
    public function ctrlLimit($user, $organization_cashLimit, $organization_limitCashAfter=0, $cashesUser, $tot_importo_cash=0, $tot_importo_acquistato=0, $debug=false) {
		
		$results = [];

		if(isset($cashesUser['CashesUser']))
			$cashesUser = $cashesUser['CashesUser'];
		
		if($debug) {
			echo "organization_cashLimit ".$organization_cashLimit."<br />";
			echo "organization_limitCashAfter ".$organization_limitCashAfter."<br />";
			echo "tot_importo_cash ".$tot_importo_cash."<br />";
			echo "tot_importo_acquistato ".$tot_importo_acquistato."<br />";
			echo "<pre>cashesUser \n ";
			print_r($cashesUser);
			echo "</pre>";			
		}
		// 
     	switch($organization_cashLimit) {
    		case "LIMIT-NO":
    			$results['importo'] = 0; // (floatval($tot_importo_cash) - floatval($tot_importo_acquistato));
    			$results['stato'] = 'GREEN';
    			$results['fe_msg'] = 'Nessun limiti per gli acquisti';
    		break;
    		case "LIMIT-CASH":
    			$results['importo'] = (floatval($tot_importo_cash) - floatval($tot_importo_acquistato));
    			if($results['importo']<0) {
	    			$results['stato'] = 'RED';
					$results['fe_msg'] = 'Hai esaurito il credito di cassa!';
	    		}
				else
	    		if($results['importo']>0) {
	    			$results['stato'] = 'GREEN';
					$results['fe_msg'] = 'Puoi fare acquisti per '.number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'; 
	    		}
				else
	    		if($results['importo']==0) {
	    			$results['stato'] = 'YELLOW';
					$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!'; 
				}
    		break;
    		case "LIMIT-CASH-AFTER":
    			$results['importo'] = (floatval($tot_importo_cash) - floatval($tot_importo_acquistato) + floatval($organization_limitCashAfter));
    			if($results['importo']<0) {
	    			$results['stato'] = 'RED';
					$results['fe_msg'] = 'Hai esaurito il credito di cassa!';
	    		}
				else
	    		if($results['importo']>0) {
	    			$results['stato'] = 'GREEN';
					$results['fe_msg'] = 'Puoi fare acquisti per '.number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'; 
	    		}
				else
	    		if($results['importo']==0) {
	    			$results['stato'] = 'YELLOW';
					$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!'; 
				}	
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
		    			$results['stato'] = 'GREEN';
						$results['fe_msg'] = 'Nessun limiti per gli acquisti';
		    		break;
		    		case "LIMIT-CASH":
		    			$results['importo'] = (floatval($tot_importo_cash) - floatval($tot_importo_acquistato));
		    			if($results['importo']<0) {
			    			$results['stato'] = 'RED';
					$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!';
			    		}
						else
			    		if($results['importo']>0) {
			    			$results['stato'] = 'GREEN';
							$results['fe_msg'] = 'Puoi fare acquisti per '.number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'; 
			    		}
						else
			    		if($results['importo']==0) {
			    			$results['stato'] = 'YELLOW';
					$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!';
						}
		    		break;
		    		case "LIMIT-CASH-AFTER":
		    			$results['importo'] = (floatval($tot_importo_cash) - floatval($tot_importo_acquistato) + floatval($cashesUser['limit_after']));
		    			if($results['importo']<0) {
			    			$results['stato'] = 'RED';
					$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!';
			    		}
			    		else
			    		if($results['importo']>0) {
			    			$results['stato'] = 'GREEN';
							$results['fe_msg'] = 'Puoi fare acquisti per '.number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'; 
			    		}
			    		else
			    		if($results['importo']==0) {
			    			$results['stato'] = 'YELLOW'; 
					$results['fe_msg'] = 'Hai esaurito il tuo credito in cassa!'; 
			    		}  		
		    		break;
		    	}				 
    		break;
    	}
    	
    	$results['importo_'] = number_format($results['importo'] ,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$results['importo_e']  = $results['importo_'] .'&nbsp;&euro;'; 

		if($debug) {
			echo "<pre>\n ";
			print_r($results);
			echo "</pre>";			
		}
				
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
	
	public function beforeSave($options = array()) {
		if (!empty($this->data['CashesUser']['limit_after']))
			$this->data['CashesUser']['limit_after'] = $this->importoToDatabase($this->data['CashesUser']['limit_after']);
	
		return true;
	}	
}
