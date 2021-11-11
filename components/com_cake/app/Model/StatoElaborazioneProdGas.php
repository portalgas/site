<?php
App::uses('AppModel', 'Model');

class StatoElaborazioneProdGas extends AppModel {

	public $useTable = false;

	/*
     * $type='GAS'        promozioni ai G.A.S.
     * $type='GAS-USERS'  promozioni ai singoli utenti
	 */
	public function promotions($user, $prod_gas_promotion_id=0, $debug=false) {

		$this->promotionsGas($user, $prod_gas_promotion_id, $debug); 

		$this->promotionsGasUsers($user, $prod_gas_promotion_id, $debug); 
	}

	/*
     * $type='GAS'        promozioni ai G.A.S.
     */
	public function promotionsGas($user, $prod_gas_promotion_id=0, $debug=false) {
        
        App::import('Model', 'ProdGasPromotion');
		$ProdGasPromotion = new ProdGasPromotion;
		
		self::d(date("d/m/Y")." - ".date("H:i:s")." StatoElaborazioneProdGas::promotions - estraggo le promoziponi type='GAS' scadute con state_code = PRODGASPROMOTION-GAS-TRASMISSION-TO-GAS", $debug);

		$options = [];
		$options['conditions'] = ['ProdGasPromotion.organization_id' => $user->organization['Organization']['id'],
									'ProdGasPromotion.state_code' => 'PRODGASPROMOTION-GAS-TRASMISSION-TO-GAS',
									'ProdGasPromotion.type' =>  'GAS',
									'DATE(ProdGasPromotion.data_fine) < CURDATE()'];
		if(!empty($prod_gas_promotion_id))
			$options['conditions'] += ['ProdGasPromotion.id' => $prod_gas_promotion_id];
		$options['recursive'] = -1;
		$results = $ProdGasPromotion->find('all', $options);
		self::d(date("d/m/Y")." - ".date("H:i:s")." trovati ".count($results)." promozioni", $debug);

		if(!empty($results)) 	
		foreach ($results as $result) {
			$result['ProdGasPromotion']['state_code'] = 'PRODGASPROMOTION-GAS-FINISH';

		    // self::d($result, $debug);
  			$ProdGasPromotion->set($result);
    		if(!$ProdGasPromotion->validates()) {
    			$errors = $this->validationErrors;
				self::d($errors, $debug);	
			}
			else {
				$ProdGasPromotion->create();
				if(!$ProdGasPromotion->save($result)) {
	    			$errors = $this->validationErrors;
					self::d($result, $debug);
					self::d($errors, $debug);
				}
				else {
					self::d("Aggiornata la promozione ".$result['id'], $debug);
					self::d($result, $debug);
				}			
			} 		

		} // end foreach ($results as $result)

		return true;
	}

	/*
     * $type='GAS-USERS'  promozioni ai singoli utenti
	 */
	public function promotionsGasUsers($user, $prod_gas_promotion_id=0, $debug=false) {
      
        App::import('Model', 'ProdGasPromotion');
		$ProdGasPromotion = new ProdGasPromotion;
		
		self::d(date("d/m/Y")." - ".date("H:i:s")." StatoElaborazioneProdGas::promotions - estraggo le promoziponi type='GAS-USERS' scadute con state_code = PRODGASPROMOTION-GAS-USERS-OPEN", $debug);

		$options = [];
		$options['conditions'] = ['ProdGasPromotion.organization_id' => $user->organization['Organization']['id'],
									'ProdGasPromotion.state_code' => 'PRODGASPROMOTION-GAS-USERS-OPEN',
									'ProdGasPromotion.type' =>  'GAS-USERS',
									'DATE(ProdGasPromotion.data_fine) < CURDATE()'];
		if(!empty($prod_gas_promotion_id))
			$options['conditions'] += ['ProdGasPromotion.id' => $prod_gas_promotion_id];
		$options['recursive'] = -1;
		$results = $ProdGasPromotion->find('all', $options);
		
		self::d(date("d/m/Y")." - ".date("H:i:s")." trovati ".count($results)." promozioni", $debug);

		if(!empty($results)) 	
		foreach ($results as $result) {
			$result['ProdGasPromotion']['state_code'] = 'PRODGASPROMOTION-GAS-USERS-CLOSE';

		    // self::d($result, $debug);
  			$ProdGasPromotion->set($result);
    		if(!$ProdGasPromotion->validates()) {
    			$errors = $this->validationErrors;
				self::d($errors, $debug);	
			}
			else {
				$ProdGasPromotion->create();
				if(!$ProdGasPromotion->save($result)) {
	    			$errors = $this->validationErrors;
					self::d($result, $debug);
					self::d($errors, $debug);
				}
				else {
					self::d("Aggiornata la promozione ".$result['id'], $debug);
					self::d($result, $debug);
				}			
			} 		

		} // end foreach ($results as $result)

		return true;
	}		
}