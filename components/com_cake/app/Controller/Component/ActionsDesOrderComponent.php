<?php 
App::uses('Component', 'Controller');

class ActionsDesOrderComponent extends Component {
    
	private $Controller = null;

    public function initialize(Controller $controller) 
    {
		$this->Controller = $controller;
    }	

	/*
	 * ordine del titolare 
	 * $desResults = getDesOrderData($user, $order_id, $debug=false)
	 */
	public function getOrderTitolare($user, $desResults, $debug=false) {
		$titolareOrderResult = [];
		
		foreach($desResults['desOrdersResults']['DesOrdersOrganizations'] as $desOrdersOrganization) {
			if($desOrdersOrganization['DesOrdersOrganization']['organization_id']==$desResults['desOrdersResults']['OwnOrganization']['id']) {

                App::import('Model', 'Order');
                $Order = new Order();

                $tmp_user = new \stdClass();
                $tmp_user->organization = [];
                $tmp_user->organization['Organization'] = ['id' => $desOrdersOrganization['Order']['organization_id']];
                $order = $Order->_getOrderById($tmp_user, $desOrdersOrganization['Order']['id']);
                $titolareOrderResult = $order;
				break;
			}
		}
		return $titolareOrderResult;
	}
	
	/*
	 * richiamato per ogni ordine per sapere se e' DES
	 */
	public function getDesOrderData($user, $order_id, $debug=false) {
		
		$controllerLog = $this->Controller;
		
		$results = [];
		
		$isTitolareDesSupplier = false;
		$des_order_id = 0;
		$results['des_order_id'] = 0;
		$results['desOrdersResults'] = [];
		$results['summaryDesOrderResults'] = [];
		
		if ($user->organization['Organization']['hasDes'] == 'Y') {

			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($user, $order_id, $debug);
			if (!empty($desOrdersOrganizationResults)) {

				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
				
				if (!empty($des_order_id)) {
					$isTitolareDesSupplier = $this->isTitolareDesSupplier($user, $des_order_id);
			
					App::import('Model', 'DesOrder');
					$DesOrder = new DesOrder();
					$desOrdersResults = $DesOrder->getDesOrder($user, $des_order_id, $debug);
				
					/*
					 * ctrl eventuali occorrenze di SummaryDesOrder
					 */
					App::import('Model', 'SummaryDesOrder');
					$SummaryDesOrder = new SummaryDesOrder;
					$summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($user, $des_order_id, $user->organization['Organization']['id']);
					
					$controllerLog::d($desOrdersResults, $debug);
					 
					$results['desOrdersResults'] = $desOrdersResults;
					$results['summaryDesOrderResults'] = $summaryDesOrderResults;
				}
			} // end if (!empty($desOrdersOrganizationResults))
					
		} // DES

		$results['isTitolareDesSupplier'] = $isTitolareDesSupplier;
		$results['des_order_id'] = $des_order_id;
		
		return $results;
	}
	
	/*
	 * ctrl se il referente puo' agire sul suo ordine
	 */
	public function isAclReferente($state_code) {
		
		if($state_code=='POST-TRASMISSION' || $state_code=='CLOSE')
			return false;
		else
			return true;
	}	
	
	/*
	 * do precedenza al group_id_titolare_des_supplier
	 */
	public function getGroupIdToReferente($user, $debug=false) {
		$group_id = 0;
		
		if(in_array(Configure::read('group_id_titolare_des_supplier'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_titolare_des_supplier');
		else
		if(in_array(Configure::read('group_id_referent_des'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_referent_des');
		else
		if(in_array(Configure::read('group_id_super_referent_des'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_super_referent_des');
	
		return $group_id;
	}

	/*
	 * restituisce gli DesOrder.state_code (OPEN, POST-TRASMISSION ...) dato un 
	* 		template_id ora fisso a 1
	* 		User.group_id    (referente, cassiere, tesoriere)
	*
	* per il menu'
	*/
	public function getDesOrderStates($user, $group_id, $debug=false) {
		
		$controllerLog = $this->Controller;
		
		$orderState=[];
		
		App::import('Model', 'TemplatesDesOrdersState');
		$TemplatesDesOrdersState = new TemplatesDesOrdersState;
		
		$options = [];
		$options['conditions'] = ['TemplatesDesOrdersState.template_id' => 1, // $user->organization['Organization']['template_id'],
								  'TemplatesDesOrdersState.group_id' => $group_id];
		
		$options['order'] = ['TemplatesDesOrdersState.sort'];
		$options['recursive'] = 0;
		$results = $TemplatesDesOrdersState->find('all', $options);

		$controllerLog::l($results, $debug);
		
		return $results;
	}
	
	/*
	 * dato un Ordine restituisce le possibili Action per il menu (flag_menu = Y) in base 
	 * 		template_id ora fisso a 1
	 * 		User.group_id    (referente, cassiere, tesoriere)
	 * 		DesOrder.state_code (OPEN, POST-TRASMISSION ...)
	 * 
	 * return 	
	 * 			$result['DesOrdersAction'] 
	 *			$result['DesOrdersAction']['url'] = link gia' composto
	*/	
	public function getDesOrderActionsToMenu($user, $group_id, $des_order_id, $debug=false) {

		$controllerLog = $this->Controller;
	
		$controllerLog::l('ActionsDesOrder::getDesOrderActionsToMenu() ', $debug);
	
		$desOrderActions=[];

		$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';
		$RoutingPrefixes = Configure::read('Routing.prefixes');
		
		/*
		 * dati ordine
		 */
		App::import('Model', 'DesOrder');
		$DesOrder = new DesOrder;
				
		$options = [];
		$options['conditions'] = ['DesOrder.des_id' => $user->des_id,
								   'DesOrder.id' => $des_order_id];
		$options['recursive'] = -1;		
		$desOrderResults = $DesOrder->find('first', $options);

		$controllerLog::l($desOrderResults, $debug);
		
		if(empty($desOrderResults)) {
			$controllerLog::l("getDesOrderActionsToMenu: DesOrdine non trovato", $debug);
			return $desOrderActions;
		}

		 /*
		  * per i TEST
		$desOrderResults['DesOrder']['state_code'] = 'POST-TRASMISSION';
		$desOrderResults['DesOrder']['state_code'] = 'INCOMING-ORDER';
		*/
		
		/*
		 * actions possibili in base a
		 * 		template_id ora fisso a 1
		 * 		User.group_id
		 * 		Order.state_code
		*/		
		App::import('Model', 'TemplatesDesOrdersStatesOrdersAction');
		$TemplatesDesOrdersStatesOrdersAction = new TemplatesDesOrdersStatesOrdersAction;
		
		$options = [];
		$options['conditions'] = ['TemplatesDesOrdersStatesOrdersAction.template_id' => 1, // $user->organization['Organization']['template_id'],
								   'TemplatesDesOrdersStatesOrdersAction.state_code' => $desOrderResults['DesOrder']['state_code'],
								   'TemplatesDesOrdersStatesOrdersAction.group_id' => $group_id,
								   'DesOrdersAction.flag_menu' => 'Y',
		];
		
		$options['order'] = ['TemplatesDesOrdersStatesOrdersAction.sort'];
		$options['recursive'] = 0;
		$results = $TemplatesDesOrdersStatesOrdersAction->find('all', $options);
		
		$controllerLog::l($results, $debug);
			
		/*
		 * ctrl per ogni action OrdersAction.permission e OrdersAction.permission_or
		 */
		$desOrderActions = $this->_ctrlACLDesOrdersAction($user, $desOrderResults, $results, $debug); 

		return $desOrderActions;
	}	

	/*
	 * ctrl per ogni action DesOrdersAction.permission e DesOrdersAction.permission_or
	*/
	private function _ctrlACLDesOrdersAction($user, $desOrderResults, $results, $debug) {
		
		$controllerLog = $this->Controller;
		
		//$debug = true;
		
		$desOrderActions = [];
		$i=0;
		foreach ($results as $numResult => $result) {
				
			$desOrderActionOk = true;
			
			$controllerLog::l('Controllo permission per '.$result['DesOrdersAction']['controller'].' '.$result['DesOrdersAction']['action'].' '.$result['DesOrdersAction']['query_string'], $debug);
			
			/*
			 * PERMISSION
			 * 		sono stati soddisfatti tutti i criteri per accedere alla risorsa => faccio vedere l'url
			 */			
			if(!empty($result['DesOrdersAction']['permission'])) {
				$permission = json_decode($result['DesOrdersAction']['permission'], true);

				$esito = false;
				$desOrderActionOk = true;
				foreach ($permission as $method_name => $value_da_verificare) {
					if($desOrderActionOk) {
						$esito = $this->{$method_name}($user, $desOrderResults, $value_da_verificare);
						$controllerLog::l('     permission '.$method_name.'('.$value_da_verificare.') esito '.$esito, $debug);
					}
						
					if(!$esito) {
						$desOrderActionOk = false;
						// break; no perche' mi blocca il foreach superiore
					}
				}
				
				if($desOrderActionOk)
					$controllerLog::l('     Action OK', $debug);
				else
					$controllerLog::l('     Action NOT OK!', $debug);
				
			} // end if(!empty($result['DesOrdersAction']['permission']))
				
			/*
			 * PERMISSION_OR
			 */			
			if(!empty($result['DesOrdersAction']['permission_or'])) {
				$permission_or = json_decode($result['DesOrdersAction']['permission_or'], true);

				$esito = false;
				$desOrderActionOR_Ok = true;
				foreach ($permission_or as $method_name => $value_da_verificare) {
					if($desOrderActionOR_Ok) {
						$esito = $this->{$method_name}($user, $desOrderResults, $value_da_verificare);
						$controllerLog::l('     permission_OR '.$method_name.'('.$value_da_verificare.') esito '.$esito, $debug);				
					}
	
					if(!$esito) {
						$desOrderActionOR_Ok = false;
						// break; no perche' mi blocca il foreach superiore
					}
				}
	
				if($desOrderActionOR_Ok)
					$controllerLog::l('     Action OK', $debug);
				else
					$controllerLog::l('     Action NOT OK!', $debug);
				
				/*
				 * se nel controllo OrdersAction.permission era true, e' valido anche qui perche' sono in OR
				*/				
				if($desOrderActionOk || $desOrderActionOR_Ok) 
					$desOrderActionOk = true;
				
			} // if(!empty($result['DesOrdersAction']['permission_or']))



			if($desOrderActionOk) {
	
				$desOrderActions[$i]['DesOrdersAction'] = $result['DesOrdersAction'];
				$desOrderActions[$i]['DesOrdersAction']['url'] = $urlBase.'controller='.$result['DesOrdersAction']['controller'].'&action='.$result['DesOrdersAction']['action'].'&des_order_id='.$desOrderResults['DesOrder']['id'];
	
				if(!empty($result['DesOrdersAction']['query_string'])) {
						
					switch ($result['DesOrdersAction']['query_string']) {
						case 'FilterArticleOrderId':
							$desOrderActions[$i]['DesOrdersAction']['url'] .= '&FilterArticleOrderId='.$desOrderResults['DesOrder']['id'];
							break;
					}
				}
				$i++;
			}
				
	
	
		}
	
		$controllerLog::l($desOrderActions, $debug);
	
		return $desOrderActions;
	}
	
	/*
	 * metodi che controllano i permessi
	 * da OrdersAction.permission ho l'elenco dei metodi da controllare
	 */
	
	/*
	 * Controlli su Organization
	 */
	private function orgHasPayToDelivery($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($user->organization['Template']['payToDelivery']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}
	
	private function orgHasArticlesOrder($user, $results, $value_da_verificare) {
		$esito = false;
		
		if($user->organization['Organization']['hasArticlesOrder']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
		
		return $esito;
	}
	
	private function orgHasValidate($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($user->organization['Organization']['hasValidate']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}

	private function orgHasTrasport($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($user->organization['Organization']['hasTrasport']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}

	private function orgHasCostMore($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($user->organization['Organization']['hasCostMore']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}
	
	private function orgHasCostLess($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($user->organization['Organization']['hasCostLess']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}
	
	private function orgHasStoreroom($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($user->organization['Organization']['hasStoreroom']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}
	
	private function orgHasDes($user, $results, $value_da_verificare) {
		$esito = false;
		 
		if($user->organization['Organization']['hasDes']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
		
		return $esito;			
	}
	
	/*
	 * Controlli su User
	*/
	private function userHasArticlesOrder($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($user->organization['Organization']['hasArticlesOrder']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}
	
	/*
	 * Controlli su Ordine del e' un ordine condiviso 
	*/
	private function orderIsDes($user, $results, $value_da_verificare) {
		$esito = false;
		
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();
	
		$options = [];
		$options['conditions'] = array('DesOrdersOrganization.organization_id' => $user->organization['Organization']['id'],
									   'DesOrdersOrganization.des_order_id' => $results['DesOrder']['id']);
		if(!empty($user->des_id))
			$options['conditions'] += array('DesOrdersOrganization.des_id' => $user->des_id);
										
		$options['recursive'] = -1;								
		$desOrdersOrganizationResults = $DesOrdersOrganization->find('first', $options);

		if(empty($desOrdersOrganizationResults)) {
			if($value_da_verificare=='N')
				$esito = true;
			else
				$esito = false;
		}
		else {
			if($value_da_verificare=='Y')
				$esito = true;
			else
				$esito = false;		
		}

		return $esito;
	}

	private function orderHasTrasport($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($results['DesOrder']['hasTrasport']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}


	private function orderHasCostMore($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($results['DesOrder']['hasCostMore']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}
	
	private function orderHasCostLess($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($results['DesOrder']['hasCostLess']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}
	
	private function orderTypeGest($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($results['DesOrder']['typeGest']==$value_da_verificare)
			return true;
		else
			return false;
	}
	
	public function __string_starts_with($string, $search)
	{
		return (strncmp($string, $search, strlen($search)) == 0);
	}
	
	/*
	 * estrae gli stati dell'ordine per la legenda profilata e per gli stati del Order.sotto_menu
	 * e aggiunge gli stati del Tesoriere
	 */
	public function getDesOrderStatesToLegenda($user, $group_id, $debug=false) {
		
		$controllerLog = $this->Controller;
		
		$orderState=[];
		$orderState2=[];
		
		App::import('Model', 'TemplatesDesOrdersState');
		$TemplatesDesOrdersState = new TemplatesDesOrdersState;
		
		$options = [];
		$options['conditions'] = array('TemplatesDesOrdersState.template_id' => 1, // $user->organization['Organization']['template_id'],
									   'TemplatesDesOrdersState.group_id' => $group_id,
									   'TemplatesDesOrdersState.flag_menu' => 'Y'
		);
		
		$options['order'] = array('TemplatesDesOrdersState.sort');
		$options['recursive'] = -1;
		$desOrderStates = $TemplatesDesOrdersState->find('all', $options);

		$controllerLog::l([$options, $desOrderStates], $debug);		

		return $desOrderStates;
	}
	
	/*
	 * creo degli OrderActions di raggruppamento per controller (Orders, Carts, etc)
	 * 
	 * per Order::home e Order_home_simple
	*/
	public function getRaggruppamentoDesOrderActions($desOrderActions, $debug) {
		
		$controllerLog = $this->Controller;
		
		// $debug = true;
		
		$raggruppamentoDefault['Orders']['label'] = 'Edit Order';
		$raggruppamentoDefault['Orders']['img'] = 'modulo.jpg';
		$raggruppamentoDefault['ArticlesOrders']['label'] = 'Edit ArticlesOrder Short';
		$raggruppamentoDefault['ArticlesOrders']['img'] = 'legno-frutta-cassetta.jpg';
		$raggruppamentoDefault['Carts']['label'] = 'Gestisci gli acquisti';
		$raggruppamentoDefault['Carts']['img'] = 'legno-bancone.jpg';
		$raggruppamentoDefault['Docs']['label'] = 'Gestisci le stampe';
		$raggruppamentoDefault['Docs']['img'] = 'lista.jpg';
		$raggruppamentoDefault['Referente']['label'] = 'Gestisci la merce';
		$raggruppamentoDefault['Referente']['img'] = 'legno-frutta-cassetta.jpg';
		
		$raggruppamentoDesOrderActions = [];

		if(count($desOrderActions)==1)
			return $raggruppamentoDesOrderActions;
		
		$controller_old='';
		$tot_figli=0;
		$i=0;
		foreach($desOrderActions as $desOrderAction) {
		
			if($debug) echo '<br />'.$controller_old.' '.$desOrderAction['DesOrdersAction']['controller'];
		
			if(empty($controller_old) || $controller_old==$desOrderAction['DesOrdersAction']['controller']) {
				$tot_figli++;
		
				if($debug) echo '<br />A) Per il controller '.$desOrderAction['DesOrdersAction']['controller'].' finora trovati '.$tot_figli.' figli';
			}
			else {
				$raggruppamentoDesOrderActions[$i]['controller'] = $controller_old;
				$raggruppamentoDesOrderActions[$i]['tot_figli'] = $tot_figli;
				if(isset($raggruppamentoDefault[$controller_old])) {
					$raggruppamentoDesOrderActions[$i]['label'] = $raggruppamentoDefault[$controller_old]['label'];
					$raggruppamentoDesOrderActions[$i]['img'] = $raggruppamentoDefault[$controller_old]['img'];
				}
				else {
					$raggruppamentoDesOrderActions[$i]['label'] = '';
					$raggruppamentoDesOrderActions[$i]['img'] = '';					
				}
				
				$i++;
				$tot_figli = 1;
				
				if($debug) echo '<br />B) Per il controller '.$desOrderAction['DesOrdersAction']['controller'].' finora trovati '.$tot_figli.' figli';
			}
		
			$controller_old = $desOrderAction['DesOrdersAction']['controller'];
		} // foreach($desOrderActions as $desOrderAction)
			
		$raggruppamentoDesOrderActions[$i]['controller'] = $controller_old;
		$raggruppamentoDesOrderActions[$i]['tot_figli'] = $tot_figli;
		if(isset($raggruppamentoDefault[$controller_old])) {
			$raggruppamentoDesOrderActions[$i]['label'] = $raggruppamentoDefault[$controller_old]['label'];
			$raggruppamentoDesOrderActions[$i]['img'] = $raggruppamentoDefault[$controller_old]['img'];
		}
		else {
			$raggruppamentoDesOrderActions[$i]['label'] = '';
			$raggruppamentoDesOrderActions[$i]['img'] = '';
		}
		
		$controllerLog::l($raggruppamentoDesOrderActions, $debug);
			
		return $raggruppamentoDesOrderActions;
	}
	
	/*
	 * isTitolareDesSupplier: lo user e' titolare del produttore
	 * results = $results['DesOrder']['des_supplier_id']
	 * results = $des_order_id	
	 */
	public function isTitolareDesSupplier($user, $results, $value_da_verificare=true) {
	
		$controllerLog = $this->Controller;
	
		$debug = false;
		
		$esitoIsTitolareDesSupplier = false;
		
		if(!isset($results['DesOrder'])) {
		
			$des_order_id = $results;
			
			App::import('Model', 'DesOrder');
			$DesOrder = new DesOrder;
			
			$options = [];
			$options['conditions'] = ['DesOrder.des_id' => $user->des_id,
									  'DesOrder.id' => $des_order_id];
			$options['fields'] = ['DesOrder.des_supplier_id'];
			$options['recursive'] = -1;
			
			$controllerLog::l($results, $debug);

			$results = $DesOrder->find('first', $options);
			$controllerLog::d($results, $debug);
		}
	
		/*
		 * ctrl se lo user e' nel gruppo Configure::read('group_id_titolare_des_supplier')
		 */
		if($user->get('id') == 0 || !in_array(Configure::read('group_id_titolare_des_supplier'), $user->getAuthorisedGroups())) 
			 $esitoIsTitolareDesSupplier = false;
		 else {

			App::import('Model', 'DesSuppliersReferent');
			$DesSuppliersReferent = new DesSuppliersReferent;

			$DesSuppliersReferent->unbindModel(['belongsTo' => ['De', 'User']]);
			
			$options = [];
			$options['conditions'] = ['DesSuppliersReferent.des_id' => $user->des_id,
					'DesSuppliersReferent.organization_id' => $user->organization['Organization']['id'],
					'DesSuppliersReferent.user_id' => $user->get('id'),
					'DesSuppliersReferent.group_id' => Configure::read('group_id_titolare_des_supplier'),
					'DesSupplier.des_id' => $user->des_id,
					'DesSupplier.own_organization_id' => $user->organization['Organization']['id'],
					'DesSupplier.id' => $results['DesOrder']['des_supplier_id']];
			$options['recursive'] = 1;
			$totali = $DesSuppliersReferent->find('count', $options);

			$controllerLog::d($options, $debug);
			$controllerLog::l([$options, $totali], $debug);
			   		
			if($totali==0)
				$esitoIsTitolareDesSupplier = false;
			else 
				$esitoIsTitolareDesSupplier = true;
		}
		
		if($esitoIsTitolareDesSupplier==$value_da_verificare) {
			$controllerLog::d('isTitolareDesSupplier() esito SI - totali '.$totali, $debug);
			return true;
		}	
		else {
			$controllerLog::d('isTitolareDesSupplier() esito NO - totali '.$totali, $debug);
			return false;
		}	
	}	

	/*
	 * isReferentDesAllGasDesSupplier: lo user e' referente del produttore: potra' visualizzare gli ordini dei GAS
	 * results = $results['DesOrder']['des_supplier_id']
	 * results = $des_order_id	
	 */
	public function isReferentDesAllGasDesSupplier($user, $results, $value_da_verificare=true) {
	
		$controllerLog = $this->Controller;
	
		$debug = false;
		
		$esitoReferentDesAllGasDesOrder = false;
		
		if(!isset($results['DesOrder'])) {
		
			$des_order_id = $results;
			
			App::import('Model', 'DesOrder');
			$DesOrder = new DesOrder;
			
			$options = [];
			$options['conditions'] = ['DesOrder.des_id' => $user->des_id,
									   'DesOrder.id' => $des_order_id];
			$options['fields'] = ['DesOrder.des_supplier_id'];
			$options['recursive'] = -1;
			$controllerLog::l($results, $debug);

			$results = $DesOrder->find('first', $options);
		}
		
		/*
		 * ctrl se lo user e' nel gruppo Configure::read('group_id_des_supplier_all_gas')
		 */
		if($user->get('id') == 0 || !in_array(Configure::read('group_id_des_supplier_all_gas'), $user->getAuthorisedGroups())) 
			 $esitoReferentDesAllGasDesOrder = false;
		 else {

			App::import('Model', 'DesSuppliersReferent');
			$DesSuppliersReferent = new DesSuppliersReferent;

			$DesSuppliersReferent->unbindModel(['belongsTo' => ['De', 'User']]);
			
			$options = [];
			$options['conditions'] = ['DesSuppliersReferent.des_id' => $user->des_id,
									   'DesSuppliersReferent.organization_id' => $user->organization['Organization']['id'],
									   'DesSuppliersReferent.user_id' => $user->get('id'),
									   'DesSuppliersReferent.group_id' => Configure::read('group_id_des_supplier_all_gas'),
									   'DesSupplier.des_id' => $user->des_id,
									   'DesSupplier.own_organization_id' => $user->organization['Organization']['id'],
									   'DesSupplier.id' => $results['DesOrder']['des_supplier_id']];
			$options['recursive'] = 1;
			$totali = $DesSuppliersReferent->find('count', $options);

			$controllerLog::l([$options, $totali], $debug);
			   		
			if($totali==0)
				$esitoReferentDesAllGasDesOrder = false;
			else 
				$esitoReferentDesAllGasDesOrder = true;
		}
		
		if($esitoReferentDesAllGasDesOrder==$value_da_verificare) {
			$controllerLog::l('esito SI ', $debug); 
			return true;
		}	
		else {
			$controllerLog::l('esito NO ', $debug); 
			return false;
		}	
	}	

	/*
	 * isReferentDesAllGasDesSupplier: lo user e' referente del produttore: potra' visualizzare gli ordini dei GAS
	 * dato un ordine di un GAS ctrl se e' abilitato
	 * results = $des_order_id	
	 */
	public function isReferentDesAllGasDesOrder($user, $organization_id, $order_id) {
	
		$controllerLog = $this->Controller;
		
		$debug = false;
		
		$esitoReferentDesAllGasDesOrder = false;
		
		/*
		 * estraggo des_order_id
		 */
		
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization;
		
		$options = [];
		$options['conditions'] = ['DesOrdersOrganization.des_id' => $user->des_id,
								  'DesOrdersOrganization.organization_id' => $organization_id,
								  'DesOrdersOrganization.order_id' => $order_id];
		$options['fields'] = ['DesOrdersOrganization.des_order_id'];
		$options['recursive'] = -1;
		
		$controllerLog::l($results, $debug);

		$results = $DesOrdersOrganization->find('first', $options);
		
		$des_order_id = $results['DesOrdersOrganization']['des_order_id'];
		
		if($this->isReferentDesAllGasDesSupplier($user, $des_order_id))
			return true;
		else
			return false;	
	}	
				
	/*
	 * ctrl se e' gia' stato creato un ordine per il DesOrder
	 */
	 public function orderJustCreate($user, $results, $value_da_verificare) {
		
		$controllerLog = $this->Controller;
		
		$debug = false;
		$esitoIsOrderJustCreate = false;
		
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();
		
		$options = [];
		$options['conditions'] = ['DesOrdersOrganization.des_id' => $user->des_id,
								  'DesOrdersOrganization.des_order_id' => $results['DesOrder']['id'],
							      'DesOrdersOrganization.organization_id' => $user->organization['Organization']['id']];
		$totali = $DesOrdersOrganization->find('count', $options);
		
		$controllerLog::l([$options, $totali], $debug);
		
		if($totali==0)
			$esitoIsOrderJustCreate = "N";
		else 
			$esitoIsOrderJustCreate = "Y";
				 
		if($esitoIsOrderJustCreate==$value_da_verificare)
			return true;
		else
			return false;	 
	 } 
}
?>