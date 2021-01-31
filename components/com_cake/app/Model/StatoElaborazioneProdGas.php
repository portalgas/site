<?php
App::uses('AppModel', 'Model');

class StatoElaborazioneProdGas extends AppModel {

	public $useTable = false;

	public function promotions($user, $prod_gas_promotion_id=0, $debug=false) {

        App::import('Model', 'ProdGasPromotion');
		$ProdGasPromotion = new ProdGasPromotion;
		
		self::d(date("d/m/Y")." - ".date("H:i:s")." StatoElaborazioneProdGas::promotions - estraggo le promoziponi scadute con state_code = TRASMISSION-TO-GAS", $debug);

		$options = [];
		$options['conditions'] = ['ProdGasPromotion.organization_id' => $user->organization['Organization']['id'],
			'ProdGasPromotion.state_code' => 'TRASMISSION-TO-GAS',
			'DATE(ProdGasPromotion.data_fine) < CURDATE()'];
		if(!empty($prod_gas_promotion_id))
			$options['conditions'] += ['ProdGasPromotion.id' => $prod_gas_promotion_id];
		$options['recursive'] = -1;
		$results = $ProdGasPromotion->find('all', $options);
		self::d(date("d/m/Y")." - ".date("H:i:s")." trovati ".count($results)." promozioni", $debug);

		if(!empty($results)) 	
		foreach ($results as $result) {
			$result['ProdGasPromotion']['state_code'] = 'FINISH';

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