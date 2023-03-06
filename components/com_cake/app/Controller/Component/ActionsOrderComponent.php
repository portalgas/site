<?php 
App::uses('Component', 'Controller');

class ActionsOrderComponent extends Component {

    public $components = array('ActionsDesOrder');

	private $isToValidate = false;
	private $isCartToStoreroom = false;
    private $Controller = null;

    public function initialize(Controller $controller) 
    {
		$this->Controller = $controller;
    }
	
	/*
	 * ctrl che si abbiamo i permessi per accedere all'url
	 * 		Organization.template_id
	 * 		Order.state_code (OPEN, PROCESSED-BEFORE-DELIVERY ...)
	 * 		User.group_id    (referente, cassiere, tesoriere)
	 * 		Controllor (Order)
	 * 		Action     (edit)
	 */
	public function isACL($user, $group_id, $order_id, $controller, $action, $debug=false) {

		$controllerLog = $this->Controller;
	
		$controller = ucfirst(strtolower($controller));  
		$action = strtolower($action);
		$RoutingPrefixes = Configure::read('Routing.prefixes');
		$prefix = $RoutingPrefixes[0];  // admin
		
		if($this->__string_starts_with($action, $prefix))
			$action = substr($action, strlen($prefix)+1, strlen($action));
		
		$controllerLog::l('ActionsOrderComponent->isACL', $debug);
		$controllerLog::l('group_id '.$group_id, $debug);
		$controllerLog::l('order_id '.$order_id, $debug);
		$controllerLog::l('controller '.$controller, $debug);
		$controllerLog::l("Ctrl se ".$controller."::".$action." e' tra gli url profilati (per template, group_id, order.state_code)", $debug);
			
		/*
		 * ctrl che action chiamata sia tra quelle profilate
		 */
		App::import('Model', 'TemplatesOrdersStatesOrdersAction');
		$TemplatesOrdersStatesOrdersAction = new TemplatesOrdersStatesOrdersAction;
		
		$TemplatesOrdersStatesOrdersAction->unbindModel(['belongsTo' => ['UserGroup']]);
		
		$options = [];
		$options['conditions'] = ['TemplatesOrdersStatesOrdersAction.template_id' => $user->organization['Organization']['template_id'],
									'OrdersAction.controller' => $controller,
									'OrdersAction.action' => $action];
		
		$options['recursive'] = 0;
		$results = $TemplatesOrdersStatesOrdersAction->find('count', $options);
		
		$controllerLog::l($options, $debug);
		
		/*
		 * l'action non e' tra quelle profilate
		 */
		if($results==0) {
			$controllerLog::l("l'action non e' tra quelle profilate", $debug);
			return true;
		}
		else {
			$controllerLog::l("l'action e' tra quelle profilate, compare $results volte, allora ctrl con Order.state_code e group_id", $debug);
		}
		
		
		/*
		 * dati ordine
		*/
		App::import('Model', 'Order');
		$Order = new Order;
		
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'], 'Order.id' => $order_id];
		$options['fields'] = ['Order.organization_id', 'Order.id', 'Order.delivery_id', 'Order.supplier_organization_id', 'Order.prod_gas_promotion_id', 
							   'Order.state_code', 'Order.order_type_id',  
							   'Order.hasTrasport', 'Order.hasCostMore', 'Order.hasCostLess', 'Order.typeGest'];
		$options['recursive'] = -1;
		$orderResults = $Order->find('first', $options);

		$controllerLog::l($orderResults, $debug);
		
		if(empty($orderResults)) {
			$controllerLog::l("Ordine non trovato", $debug);
			return false;
		}
		
		$options = [];
		$options['conditions'] = ['TemplatesOrdersStatesOrdersAction.template_id' => $user->organization['Organization']['template_id'],
									'TemplatesOrdersStatesOrdersAction.state_code' => $orderResults['Order']['state_code'],
									'TemplatesOrdersStatesOrdersAction.group_id' => $group_id,
									'OrdersAction.controller' => $controller,
									'OrdersAction.action' => $action];
		
		$options['recursive'] = 0;
		$results = $TemplatesOrdersStatesOrdersAction->find('all', $options);
		
		$controllerLog::l([$options, $results], $debug);
		
		/*
		 * ctrl per ogni action OrdersAction.permission e OrdersAction.permission_or
		*/
		$orderActions = $this->_ctrlACLOrdersAction($user, $orderResults, $results, $debug);
		
		if(empty($orderActions))
			return false;
		else 
			return true;
		
	}
	
	/*
	 * gestisco solo referente perche' ha i medesimi permessi
	 */	
	public function getGroupIdToReferente($user, $debug=false) {
		$group_id = 0;
	
		/*
		if(in_array(Configure::read('group_id_referent'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_referent');
		else
		if(in_array(Configure::read('group_id_super_referent'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_super_referent');
		*/ 
		if(in_array(Configure::read('group_id_referent'), $user->getAuthorisedGroups()) || 
		in_array(Configure::read('group_id_super_referent'), $user->getAuthorisedGroups()))
			$group_id = Configure::read('group_id_referent');

		return $group_id;
	}

	/*
	 * pagamento alla consegna
	 */
	public function getGroupIdToCassiere($user, $debug=false) {
		$group_id = 0;
	
		if(in_array(Configure::read('group_id_cassiere'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_cassiere');
	
		return $group_id;
	}

	/*
	 * referente che gestisce anche il pagamento del suo produttore 
	 */
	public function getGroupIdToReferenteTesoriere($user, $debug=false) {
		$group_id = 0;
	
		if(in_array(Configure::read('group_id_referent_tesoriere'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_referent_tesoriere');
	
		return $group_id;
	}
	
	public function getGroupIdToTesoriere($user, $debug=false) {
		$group_id = 0;
		
		if(in_array(Configure::read('group_id_tesoriere'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_tesoriere');
		
		return $group_id;		
	}
	
	/*
	 * restituisce gli Order.state_code (OPEN, PROCESSED-BEFORE-DELIVERY ...) dato un 
	* 		Organization.template_id
	* 		User.group_id    (referente, cassiere, tesoriere)
	*
	* per il menu'
	*/
	public function getOrderStates($user, $group_id, $debug=false) {
		
		$controllerLog = $this->Controller;
		
		$orderState=[];
		
		App::import('Model', 'TemplatesOrdersState');
		$TemplatesOrdersState = new TemplatesOrdersState;
		
		$options = [];
		$options['conditions'] = array('TemplatesOrdersState.template_id' => $user->organization['Organization']['template_id'],
									   'TemplatesOrdersState.group_id' => $group_id);
		
		$options['order'] = array('TemplatesOrdersState.sort');
		$options['recursive'] = 0;
		$results = $TemplatesOrdersState->find('all', $options);

		$controllerLog::l([$options, $results], $debug);
		
		return $results;
	}
	
	/*
	 * dato un Ordine restituisce le possibili Action per il menu (flag_menu = Y) in base 
	 * 		Organization.template_id
	 * 		User.group_id    (referente, cassiere, tesoriere)
	 * 		Order.state_code (OPEN, PROCESSED-BEFORE-DELIVERY ...)
	 * 
	 * return 	
	 * 			$result['OrdersAction'] 
	 *			$result['OrdersAction']['url'] = link gia' composto
	*/	
	public function getOrderActionsToMenu($user, $group_id, $order_id, $debug=false) {

		$controllerLog = $this->Controller;
		$controllerLog::l('ActionsOrderComponent::getOrderActionsToMenu', $debug);
	
		$orderActions=[];

		$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';
		$RoutingPrefixes = Configure::read('Routing.prefixes');
		
		/*
		 * dati ordine
		 */
		App::import('Model', 'Order');
		$Order = new Order;
				
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'], 'Order.id' => $order_id];
		$options['recursive'] = -1;		
		$orderResults = $Order->find('first', $options);

		$controllerLog::l($orderResults, $debug);
		
		if(empty($orderResults)) {
			$controllerLog::l("Ordine non trovato", $debug);
			return $orderActions;
		}

        $orderResults['Order']['tot_importo'] = $Order->getTotImporto($user, $order_id, $debug);

		/*
		 * home order di default
		 */
	    $orderActions[0]['OrdersAction']['id'] = '0';
	    $orderActions[0]['OrdersAction']['controller'] = 'Orders';
	    $orderActions[0]['OrdersAction']['action'] = 'home';
        $orderActions[0]['OrdersAction']['label'] = 'Order home';
        $orderActions[0]['OrdersAction']['label'] = __('Order home').'<br /> - <small>'.__('Importo_totale').' '.number_format($orderResults['Order']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' €</small>';
        $orderActions[0]['OrdersAction']['label_more'] = '';
	    $orderActions[0]['OrdersAction']['css_class'] = 'actionWorkflow';
	    $orderActions[0]['OrdersAction']['img'] = '';
	    $orderActions[0]['OrdersAction']['url'] = 'controller=Orders&action=home&delivery_id='.$orderResults['Order']['delivery_id'].'&order_id='.$order_id;
                		 
		 /*
		  * per i TEST
		$orderResults['Order']['state_code'] = 'PROCESSED-TESORIERE';
		$orderResults['Order']['state_code'] = 'WAIT-PROCESSED-TESORIERE';
		*/
		
		/*
		 * actions possibili in base a
		 * 		Organization.template_id
		 * 		User.group_id
		 * 		Order.state_code
		*/		
		App::import('Model', 'TemplatesOrdersStatesOrdersAction');
		$TemplatesOrdersStatesOrdersAction = new TemplatesOrdersStatesOrdersAction;
		
		$options = [];
		$options['conditions'] = ['TemplatesOrdersStatesOrdersAction.template_id' => $user->organization['Organization']['template_id'],
								   'TemplatesOrdersStatesOrdersAction.state_code' => $orderResults['Order']['state_code'],
								   'TemplatesOrdersStatesOrdersAction.group_id' => $group_id,
								   'OrdersAction.flag_menu' => 'Y'];
		
		$options['order'] = ['TemplatesOrdersStatesOrdersAction.sort'];
		$options['recursive'] = 0;
		$results = $TemplatesOrdersStatesOrdersAction->find('all', $options);
		// $controllerLog::l($results, $debug);
		
		/*
		 * ctrl per ogni action OrdersAction.permission e OrdersAction.permission_or
		 */
		$orderActions += $this->_ctrlACLOrdersAction($user, $orderResults, $results, $debug);

		return $orderActions;
	}	

	/*
	 * ctrl per ogni action OrdersAction.permission e OrdersAction.permission_or
	*/
	private function _ctrlACLOrdersAction($user, $orderResults, $results, $debug) {
		
		$controllerLog = $this->Controller;
		
		//$debug = true;

		/*
		 * controlli Custom
		*/
		$this->isToValidate = $this->_orderToValidate($user, $orderResults);
		
		$this->isCartToStoreroom = $this->_orderIsCartToStoreroom($user, $orderResults);
		
		$orderActions = [];
		$i=1; // parto da 1 perche' orderActions[0] e' Home Order
		foreach ($results as $numResult => $result) {
				
			$orderActionOk = true;
			
			$controllerLog::l('Controllo permission per '.$result['OrdersAction']['controller'].' '.$result['OrdersAction']['action'].' '.$result['OrdersAction']['query_string'], $debug);
				
			/*
			 * PERMISSION
			 * 		sono stati soddisfatti tutti i criteri per accedere alla risorsa => faccio vedere l'url
			 */			
			if(!empty($result['OrdersAction']['permission'])) {
				$permission = json_decode($result['OrdersAction']['permission'], true);

				$esito = false;
				$orderActionOk = true;
				foreach ($permission as $method_name => $value_da_verificare) {
					if($orderActionOk) {
						$esito = $this->{$method_name}($user, $orderResults, $value_da_verificare);
						$controllerLog::l('     permission '.$method_name.'('.$value_da_verificare.') esito '.$esito, $debug);
						
					}
						
					if(!$esito) {
						$orderActionOk = false;
						// break; no perche' mi blocca il foreach superiore
					}
				}
				
				if($orderActionOk)
					$controllerLog::l('     Action OK', $debug);
				else
					$controllerLog::l('     Action NOT OK!', $debug);
				
			} // end if(!empty($result['OrdersAction']['permission']))
				
			/*
			 * PERMISSION_OR
			 */			
			if(!empty($result['OrdersAction']['permission_or'])) {
				$permission_or = json_decode($result['OrdersAction']['permission_or'], true);

				$esito = false;
				$orderActionOR_Ok = true;
				foreach ($permission_or as $method_name => $value_da_verificare) {
					if($orderActionOR_Ok) {
						$esito = $this->{$method_name}($user, $orderResults, $value_da_verificare);
						$controllerLog::l('     permission_OR '.$method_name.'('.$value_da_verificare.') esito '.$esito, $debug);						
					}
	
					if(!$esito) {
						$orderActionOR_Ok = false;
						// break; no perche' mi blocca il foreach superiore
					}
				}
	
				if($orderActionOR_Ok)
					$controllerLog::l('     Action OK', $debug);
				else
					$controllerLog::l('     Action NOT OK!', $debug);

				/*
				 * se nel controllo OrdersAction.permission era true, e' valido anche qui perche' sono in OR
				*/				
				if($orderActionOk || $orderActionOR_Ok) 
					$orderActionOk = true;
				
			} // if(!empty($result['OrdersAction']['permission_or']))

			if($orderActionOk) {
				
				$orderActions[$i]['OrdersAction'] = $result['OrdersAction'];
				$orderActions[$i]['OrdersAction']['url'] = 'controller='.$result['OrdersAction']['controller'].'&action='.$result['OrdersAction']['action'].'&delivery_id='.$orderResults['Order']['delivery_id'].'&order_id='.$orderResults['Order']['id'];
				if(!empty($result['OrdersAction']['neo_url'])) {
					$neo_url = $result['OrdersAction']['neo_url'];
					$neo_url = str_replace('{order_type_id}', $orderResults['Order']['order_type_id'], $neo_url);
					$neo_url = str_replace('{delivery_id}', $orderResults['Order']['delivery_id'], $neo_url);
					$neo_url = str_replace('{order_id}', $orderResults['Order']['id'], $neo_url);
					$neo_url = str_replace('{parent_id}', $orderResults['Order']['parent_id'], $neo_url);
	
					$orderActions[$i]['OrdersAction']['neo_url'] = Configure::read('Neo.portalgas.url').$neo_url;
				}

				if(!empty($result['OrdersAction']['query_string'])) {
						
					switch ($result['OrdersAction']['query_string']) {
						case 'FilterArticleOrderId':
							$orderActions[$i]['OrdersAction']['url'] .= '&FilterArticleOrderId='.$orderResults['Order']['id'];
							break;
					}
				}
				$i++;
			}
	
		}

		$controllerLog::l($orderActions, $debug);
	
		return $orderActions;
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
	
	private function orgHasTemplateOrderForceClose($user, $results, $value_da_verificare) {
		$esito = false;
		
		if($user->organization['Template']['orderForceClose']==$value_da_verificare)
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

	private function orgHasOrdersGdxp($user, $results, $value_da_verificare) {
		$esito = false;
		
		if($user->organization['Organization']['hasOrdersGdxp']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
		
		return $esito;			
	}

	private function orgHasGasGroups($user, $results, $value_da_verificare) {
		$esito = false;
		
		if($user->organization['Organization']['hasGasGroups']==$value_da_verificare)
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
		$options['conditions'] = array(// 'DesOrdersOrganization.des_id' => $user->des_id,  potrebbe non averlo valorizzato
										'DesOrdersOrganization.organization_id' => $results['Order']['organization_id'],
										'DesOrdersOrganization.order_id' => $results['Order']['id']);
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
	
		if($results['Order']['hasTrasport']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}


	private function orderHasCostMore($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($results['Order']['hasCostMore']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}
	
	private function orderHasCostLess($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($results['Order']['hasCostLess']==$value_da_verificare)
			$esito = true;
		else
			$esito = false;
	
		return $esito;
	}
	
	private function orderTypeGest($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($results['Order']['typeGest']==$value_da_verificare)
			return true;
		else
			return false;
	}
	
	/*
	 * Controlli su Custom
	*/
	private function isTitolareDesSupplier($user, $results, $value_da_verificare) {
	
		$isTitolareDesSupplier = "N";
		
		/*
		 * estraggo des_supplier_id per sapere se e' titolare
		 */
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();
		$DesOrdersOrganization->unbindModel(array('belongsTo' => array('Organization', 'De', 'Order')));

		$options = [];
		$options['conditions'] = array(// 'DesOrdersOrganization.des_id' => $user->des_id,  potrebbe non averlo valorizzato
										'DesOrdersOrganization.organization_id' => $results['Order']['organization_id'],
										'DesOrdersOrganization.order_id' => $results['Order']['id']);
		$options['recursive'] = 1;								
		$desOrdersOrganizationResults = $DesOrdersOrganization->find('first', $options);
		
		if(!empty($desOrdersOrganizationResults)) {
			$des_id = $desOrdersOrganizationResults['DesOrder']['des_id'];
			$user->des_id = $des_id;
			
			if($this->ActionsDesOrder->isTitolareDesSupplier($user, $desOrdersOrganizationResults, $value_da_verificare))
				$isTitolareDesSupplier = "Y";	
		}
	
		if($isTitolareDesSupplier==$value_da_verificare)
			return true;
		else
			return false;
	}
	
	private function isToValidate($user, $results, $value_da_verificare) {
		$esito = false;
	
		if($this->isToValidate==$value_da_verificare)
			return true;
		else
			return false;
	}
	
	private function isCartToStoreroom($user, $results, $value_da_verificare) {
		$esito = false;
		
		if($this->isCartToStoreroom==$value_da_verificare)
			return true;
		else 
			return false;
	}
	
	/*
	 * controllo la tipologia di ordine
		Configure::write('Order.type.gas', 1);
		Configure::write('Order.type.des', 2);
		Configure::write('Order.type.des_titolare', 3);
		Configure::write('Order.type.promotion', 4);
		Configure::write('Order.type.pact_pre', 5); 
		Configure::write('Order.type.pact', 6);  
		Configure::write('Order.type.supplier', 7);
		Configure::write('Order.type.promotion_gas_users', 8);
		Configure::write('Order.type.socialmarket', 9);
		Configure::write('Order.type.gas_groups', 10);
		Configure::write('Order.type.gas_parent_groups', 11);
	*/
	private function isOrderTypes($user, $results, $value_da_verificare) {
		$esito = false;

		$order_type_ids = explode(',', $value_da_verificare);
		if(in_array($results['Order']['order_type_id'], $order_type_ids))
			$esito = true;
		else
			$esito = false;			
			
		return $esito;
	}

	private function isProdGasPromotion($user, $results, $value_da_verificare) {
		$esito = false;
		$isProdGasPromotion = "N";

		if($results['Order']['prod_gas_promotion_id']>0)
			$isProdGasPromotion = "Y";

		// echo '<br />prod_gas_promotion_id '.$results['Order']['prod_gas_promotion_id'].' - value_da_verificare '.$value_da_verificare;
		
		if($value_da_verificare==$isProdGasPromotion)
				$esito = true;
			else
				$esito = false;			
			
		return $esito;
	}
		
	/*
     * SUPPLIER / REFERENT / DES
	 */	 
	private function articlesOwner($user, $results, $value_da_verificare) {
		$esito = false;
		$articlesOwner = 'REFERENT';

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
				                   'SuppliersOrganization.id' => $results['Order']['supplier_organization_id']];
		$options['field'] = ['SuppliersOrganization.owner_articles'];
		$options['recursive'] = -1;
		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		if(!empty($suppliersOrganizationResults)) {
			// REFERENT o REFERENT-TMP
			$articlesOwner = $suppliersOrganizationResults['SuppliersOrganization']['owner_articles'];
			if($articlesOwner=='REFERENT-TMP')
				$articlesOwner = 'REFERENT';
		}
		
		if($value_da_verificare==$articlesOwner)
				$esito = true;
			else
				$esito = false;			
			
		return $esito;
	}
	
	/*
	 *  D.E.S.
	 *  dal DesOrdersOrganization estraggo des_supplier_id
	*/
	private function _isTitolareDesSupplier($user, $results) {
		
		$isTitolareDesSupplier = false;
		
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();

		$options = [];
		$options['conditions'] = [// 'DesOrdersOrganization.des_id' => $user->des_id,  potrebbe non averlo valorizzato
								'DesOrdersOrganization.organization_id' => $results['Order']['organization_id'],
								'DesOrdersOrganization.order_id' => $results['Order']['id']];
		$options['recursive'] = -1;								
		$desOrdersOrganizationResults = $DesOrdersOrganization->find('first', $options);

		if(!empty($desOrdersOrganizationResults)) {
			$des_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_id'];
			$user->des_id = $des_id;
			
			if($this->ActionsDesOrder->isTitolareDesSupplier($user, $desOrdersOrganizationResults))
				$isTitolareDesSupplier = true;	
		}
		
		return $isTitolareDesSupplier;
	}
	
	/*
	 * gestione dei colli (pezzi_confezione)
	 */
	private function _orderToValidate($user, $results) {
		App::import('Model', 'Order');
		$Order = new Order;
			
		if($Order->isOrderToValidate($user, $results['Order']['id']))
			$isToValidate = true;
		else
			$isToValidate = false;

		return $isToValidate;
	}
	
	/*
	 *  Storeroom, cerco eventuali articoli nel carrello dell'utente Dispensa,
	*
	*  se Order.state_code == PROCESSED-POST-DELIVERY / INCOMING-ORDER (merce arrivata) / PROCESSED-ON-DELIVERY 
	*  li copio dal carrello alla dispensa con cron
	*/
	private function _orderIsCartToStoreroom($user, $results, $debug=false) {

		$controllerLog = $this->Controller;
	
		$isCartToStoreroom = false;
		if($user->organization['Organization']['hasStoreroom']=='Y' && 
			($results['Order']['state_code']=='PROCESSED-POST-DELIVERY' || 
			$results['Order']['state_code']=='INCOMING-ORDER' || 
			$results['Order']['state_code']=='PROCESSED-ON-DELIVERY')) {
				
			App::import('Model', 'Storeroom');
			$Storeroom = new Storeroom;
			
			$storeroomResults = $Storeroom->getCartsToStoreroom($user, $results['Order']['id'], $debug);
			
			$controllerLog::l($storeroomResults, $debug);
		
			if(count($storeroomResults)>0) $isCartToStoreroom = true;
		}

		return $isCartToStoreroom;
	}
	
	public function __string_starts_with($string, $search)
	{
		return (strncmp($string, $search, strlen($search)) == 0);
	}
	
	/*
	 * estrae gli stati dell'ordine per la legenda profilata e per gli stati del Order.sotto_menu
	 * e aggiunge gli stati del Tesoriere
	 */
	public function getOrderStatesToLegenda($user, $group_id, $debug=false) {
		
		$controllerLog = $this->Controller;
		
		$orderState=[];
		$orderState2=[];
		
		App::import('Model', 'TemplatesOrdersState');
		$TemplatesOrdersState = new TemplatesOrdersState;
		
		$options = [];
		$options['conditions'] = ['TemplatesOrdersState.template_id' => $user->organization['Organization']['template_id'],
								   'TemplatesOrdersState.group_id' => $group_id,
								   'TemplatesOrdersState.flag_menu' => 'Y'];
		
		$options['order'] = ['TemplatesOrdersState.sort'];
		$options['recursive'] = -1;
		$orderStates = $TemplatesOrdersState->find('all', $options);
		
		$controllerLog::l([$options, $orderStates], $debug);
		
		return $orderStates;
	}
	
	/*
	 * creo degli OrderActions di raggruppamento per controller (Orders, Carts, etc)
	 * 
	 * per Order::home e Order_home_simple
	*/
	public function getRaggruppamentoOrderActions($orderActions, $debug) {
		
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
		
		$raggruppamentoOrderActions = [];

		if(count($orderActions)==1)
			return $raggruppamentoOrderActions;
		
		$controller_old='';
		$tot_figli=0;
		$i=0;
		foreach($orderActions as $orderAction) {
		
			if($debug) echo '<br />'.$controller_old.' '.$orderAction['OrdersAction']['controller'];
		
			if(empty($controller_old) || $controller_old==$orderAction['OrdersAction']['controller']) {
				$tot_figli++;
		
				if($debug) echo '<br />A) Per il controller '.$orderAction['OrdersAction']['controller'].' finora trovati '.$tot_figli.' figli';
			}
			else {
				$raggruppamentoOrderActions[$i]['controller'] = $controller_old;
				$raggruppamentoOrderActions[$i]['tot_figli'] = $tot_figli;
				if(isset($raggruppamentoDefault[$controller_old])) {
					$raggruppamentoOrderActions[$i]['label'] = $raggruppamentoDefault[$controller_old]['label'];
					$raggruppamentoOrderActions[$i]['img'] = $raggruppamentoDefault[$controller_old]['img'];
				}
				else {
					$raggruppamentoOrderActions[$i]['label'] = '';
					$raggruppamentoOrderActions[$i]['img'] = '';					
				}
				
				$i++;
				$tot_figli = 1;
				
				if($debug) echo '<br />B) Per il controller '.$orderAction['OrdersAction']['controller'].' finora trovati '.$tot_figli.' figli';
			}
		
			$controller_old = $orderAction['OrdersAction']['controller'];
		} // foreach($orderActions as $orderAction)
			
		$raggruppamentoOrderActions[$i]['controller'] = $controller_old;
		$raggruppamentoOrderActions[$i]['tot_figli'] = $tot_figli;
		if(isset($raggruppamentoDefault[$controller_old])) {
			$raggruppamentoOrderActions[$i]['label'] = $raggruppamentoDefault[$controller_old]['label'];
			$raggruppamentoOrderActions[$i]['img'] = $raggruppamentoDefault[$controller_old]['img'];
		}
		else {
			$raggruppamentoOrderActions[$i]['label'] = '';
			$raggruppamentoOrderActions[$i]['img'] = '';
		}
				
		$controllerLog::l($raggruppamentoOrderActions, $debug);
			
		return $raggruppamentoOrderActions;
	}
}
?>