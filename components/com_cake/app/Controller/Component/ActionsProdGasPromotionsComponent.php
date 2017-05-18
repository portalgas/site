<?php 
class ActionsProdGasPromotionsComponent extends Component {

	/*
	 * restituisce gli ProdGasPromotion.state_code (WORKING, TRASMISSION-TO_GAS ...) dato un 
	* 		template_id ora fisso a 1
	* 		User.group_id    (prod_gas_manager)
	*
	* per il menu'
	*/
	public function getProdGasPromotionStates($user, $group_id, $debug=false) {
		
		App::import('Model', 'TemplatesProdGasPromotionsState');
		$TemplatesProdGasPromotionsState = new TemplatesProdGasPromotionsState;
		
		$options = array();
		$options['conditions'] = array('TemplatesProdGasPromotionsState.template_id' => 1, // $user->organization['Organization']['template_id'],
									   'TemplatesProdGasPromotionsState.group_id' => $group_id);
		
		$options['DesOrder'] = array('TemplatesProdGasPromotionsState.sort');
		$options['recursive'] = 0;
		$results = $TemplatesProdGasPromotionsState->find('all', $options);

		if($debug) {
			echo "<pre>ActionsPromotionsComponent->getProdGasPromotionStates";
			print_r($results);
			echo "</pre>";
		}
		
		return $results;
	}
	
	/*
	 * estrae gli stati della promozione per la legenda profilata e per gli stati del ProdGasPromotion.sotto_menu
	 */
	public function getProdGasPromotionStatesToLegenda($user, $group_id, $debug=false) {
		
		App::import('Model', 'TemplatesProdGasPromotionsState');
		$TemplatesProdGasPromotionsState = new TemplatesProdGasPromotionsState;
		
		$options = array();
		$options['conditions'] = array('TemplatesProdGasPromotionsState.template_id' => 1, // $user->organization['Organization']['template_id'],
									   'TemplatesProdGasPromotionsState.group_id' => $group_id,
									   'TemplatesProdGasPromotionsState.flag_menu' => 'Y'
		);
		
		$options['order'] = array('TemplatesProdGasPromotionsState.sort');
		$options['recursive'] = -1;
		$prodGasPromotionStates = $TemplatesProdGasPromotionsState->find('all', $options);
				
		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($prodGasPromotionStates);
			echo "</pre>";
		}

		return $prodGasPromotionStates;
	}
}
?>