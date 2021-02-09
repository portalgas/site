<?php 
App::uses('Component', 'Controller');

class ActionsProdGasPromotionsComponent extends Component {

    private $Controller = null;
    private $_template_id = 1;

    public function initialize(Controller $controller) 
    {
		$this->Controller = $controller;
    }
	
	/*
	 * restituisce gli ProdGasPromotion.state_code (WORKING, TRASMISSION-TO_GAS ...) dato un 
	* 		template_id ora fisso a 1
	* 		User.group_id    (prod_gas_manager)
	*
	* per il menu'
	*/
	public function getProdGasPromotionStates($user, $group_id, $debug=false) {
		
		$controllerLog = $this->Controller;
		
		App::import('Model', 'TemplatesProdGasPromotionsState');
		$TemplatesProdGasPromotionsState = new TemplatesProdGasPromotionsState;
		
		$options = [];
		$options['conditions'] = [
				'TemplatesProdGasPromotionsState.template_id' => $this->_template_id, // $user->organization['Organization']['template_id'],
				'TemplatesProdGasPromotionsState.group_id' => $group_id];
		
		$options['order'] = ['TemplatesProdGasPromotionsState.sort'];
		$options['recursive'] = 0;
		$results = $TemplatesProdGasPromotionsState->find('all', $options);

		$controllerLog::d($result, $debug);
		
		return $results;
	}
	
	/*
	 * estrae gli stati della promozione per la legenda profilata e per gli stati del ProdGasPromotion.sotto_menu
     *
     * $type='GAS'        promozioni ai G.A.S.
     * $type='GAS-USERS'  promozioni ai singoli utenti
     */
	public function getProdGasPromotionStatesToLegenda($user, $group_id, $type='GAS', $debug=false) {
		
		$controllerLog = $this->Controller;
		
		switch (strtoupper($type)) {
			case 'GAS':
				App::import('Model', 'TemplatesProdGasPromotionsState');
				$TemplatesProdGasPromotionsState = new TemplatesProdGasPromotionsState;	
			break;
			case 'GAS-USERS':
				App::import('Model', 'TemplatesProdGasPromotionsGasUsersState');
				$TemplatesProdGasPromotionsState = new TemplatesProdGasPromotionsGasUsersState;	
			break;
			
			default:
				die("getProdGasPromotionStatesToLegenda type non previsto [$type]");
				break;
		}
		
		$options = [];
		$options['conditions'] = ['template_id' => $this->_template_id, // $user->organization['Organization']['template_id'],
									'group_id' => $group_id,
									'flag_menu' => 'Y'];
		
		$options['order'] = ['sort'];
		$options['recursive'] = -1;
		$prodGasPromotionStates = $TemplatesProdGasPromotionsState->find('all', $options);
		
		$controllerLog::d([$options, $prodGasPromotionStates], $debug);

		return $prodGasPromotionStates;
	}
}
?>