<?php 
class ActionsOrderComponent extends Component {

    public $components = array('ActionsDesOrder');

	private $isToValidate = false;
	private $isCartToStoreroom = false;

	/*
	 * ctrl che si abbiamo i permessi per accedere all'url
	 * 		Organization.template_id
	 * 		Order.state_code (OPEN, PROCESSED-BEFORE-DELIVERY ...)
	 * 		User.group_id    (referente, cassiere, tesoriere)
	 * 		Controllor (Order)
	 * 		Action     (edit)
	 */
	public function isACL($user, $group_id, $order_id, $controller, $action, $debug=false) {

		$controller = ucfirst(strtolower($controller));  
		$action = strtolower($action);
		$RoutingPrefixes = Configure::read('Routing.prefixes');
		$prefix = $RoutingPrefixes[0];  // admin
		
		if($this->__string_starts_with($action, $prefix))
			$action = substr($action, strlen($prefix)+1, strlen($action));
		
		if($debug) {
			echo '<h3>ActionsOrderComponent->isACL()</h3>';
			echo '<br />group_id '.$group_id;
			echo '<br />order_id '.$order_id;
			echo '<br />controller '.$controller;
			echo '<br />action '.$action;
			echo '<h3>Ctrl se '.$controller.'::'.$action.' e\' tra gli url profilati (per template, group_id, order.state_code)</h3>';
		}
		
		/*
		 * ctrl che action chiamata sia tra quelle profilate
		 */
		App::import('Model', 'TemplatesOrdersStatesOrdersAction');
		$TemplatesOrdersStatesOrdersAction = new TemplatesOrdersStatesOrdersAction;
		
		$TemplatesOrdersStatesOrdersAction->unbindModel(array('belongsTo' => array('UserGroup')));
		
		$options = array();
		$options['conditions'] = array('TemplatesOrdersStatesOrdersAction.template_id' => $user->organization['Organization']['template_id'],
										'OrdersAction.controller' => $controller,
										'OrdersAction.action' => $action
		);
		
		$options['recursive'] = 0;
		$results = $TemplatesOrdersStatesOrdersAction->find('count', $options);
		
		if($debug) {
			echo "<pre>";
			print_r($options);
			echo "</pre>";
		}
		
		/*
		 * l'action non e' tra quelle profilate
		 */
		if($results==0) {
			if($debug) echo "<br />l'action non e' tra quelle profilate";
			return true;
		}
		else {
			if($debug) echo "<br />l'action e' tra quelle profilate, compare $results volte, allora ctrl con Order.state_code e group_id";
		}
		
		
		/*
		 * dati ordine
		*/
		App::import('Model', 'Order');
		$Order = new Order;
		
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
										'Order.id' => $order_id);
		$options['fields'] = array('organization_id', 'id', 'delivery_id', 'prod_gas_promotion_id', 'state_code', 'hasTrasport', 'hasCostMore', 'hasCostLess', 'typeGest');
		$options['recursive'] = -1;
		$orderResults = $Order->find('first', $options);
		
		if($debug) {
			echo "<pre>";
			print_r($orderResults);
			echo "</pre>";
		}
		
		if(empty($orderResults)) {
			if($debug) echo "<br />Ordine non trovato";
			return false;
		}
		
		$options = array();
		$options['conditions'] = array('TemplatesOrdersStatesOrdersAction.template_id' => $user->organization['Organization']['template_id'],
										'TemplatesOrdersStatesOrdersAction.state_code' => $orderResults['Order']['state_code'],
										'TemplatesOrdersStatesOrdersAction.group_id' => $group_id,
										'OrdersAction.controller' => $controller,
										'OrdersAction.action' => $action
		);
		
		$options['recursive'] = 0;
		$results = $TemplatesOrdersStatesOrdersAction->find('all', $options);
		
		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}
		
		/*
		 * ctrl per ogni action OrdersAction.permission e OrdersAction.permission_or
		*/
		$orderActions = $this->__ctrlACLOrdersAction($user, $orderResults, $results, $debug);
		
		if(empty($orderActions))
			return false;
		else 
			return true;
		
	}
	
	public function getGroupIdToReferente($user, $debug=false) {
		$group_id = 0;
	
		if(in_array(Configure::read('group_id_referent'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_referent');
		else
		if(in_array(Configure::read('group_id_super_referent'), $user->getAuthorisedGroups())) $group_id = Configure::read('group_id_super_referent');
	
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
		
		$orderState=array();
		
		App::import('Model', 'TemplatesOrdersState');
		$TemplatesOrdersState = new TemplatesOrdersState;
		
		$options = array();
		$options['conditions'] = array('TemplatesOrdersState.template_id' => $user->organization['Organization']['template_id'],
									   'TemplatesOrdersState.group_id' => $group_id);
		
		$options['order'] = array('TemplatesOrdersState.sort');
		$options['recursive'] = 0;
		$results = $TemplatesOrdersState->find('all', $options);

		if($debug) {
			echo "<pre>ActionsOrderComponent->getOrderStates";
			print_r($results);
			echo "</pre>";
		}
		
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

		$orderActions=array();

		$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';
		$RoutingPrefixes = Configure::read('Routing.prefixes');
		
		/*
		 * dati ordine
		 */
		App::import('Model', 'Order');
		$Order = new Order;
				
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
									   'Order.id' => $order_id);
		$options['recursive'] = -1;		
		$orderResults = $Order->find('first', $options);

		if($debug) {
			echo "<pre>";
			print_r($orderResults);
			echo "</pre>";			
		}
		
		if(empty($orderResults)) {
			if($debug) echo "<br />Ordine non trovato";
			return $orderActions;
		}

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
		
		$options = array();
		$options['conditions'] = array('TemplatesOrdersStatesOrdersAction.template_id' => $user->organization['Organization']['template_id'],
				                       'TemplatesOrdersStatesOrdersAction.state_code' => $orderResults['Order']['state_code'],
									   'TemplatesOrdersStatesOrdersAction.group_id' => $group_id,
									   'OrdersAction.flag_menu' => 'Y',
		);
		
		$options['order'] = array('TemplatesOrdersStatesOrdersAction.sort');
		$options['recursive'] = 0;
		$results = $TemplatesOrdersStatesOrdersAction->find('all', $options);
	
		/*
		 * ctrl per ogni action OrdersAction.permission e OrdersAction.permission_or
		 */
		$orderActions = $this->__ctrlACLOrdersAction($user, $orderResults, $results, $debug); 

		return $orderActions;
	}	

	/*
	 * ctrl per ogni action OrdersAction.permission e OrdersAction.permission_or
	*/
	private function __ctrlACLOrdersAction($user, $orderResults, $results, $debug) {
		
		//$debug = true;
		
		/*
		 * controlli Custom
		*/
		$this->isToValidate = $this->__orderToValidate($user, $orderResults);
		
		$this->isCartToStoreroom = $this->__orderIsCartToStoreroom($user, $orderResults);
		
		$orderActions = array();
		$i=0;
		foreach ($results as $numResult => $result) {
				
			$orderActionOk = true;
			
			if($debug) echo '<br />Controllo permission per '.$result['OrdersAction']['controller'].' '.$result['OrdersAction']['action'].' '.$result['OrdersAction']['query_string'];
				
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
						if($debug) echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;permission '.$method_name.'('.$value_da_verificare.') esito '.$esito;
						
					}
						
					if(!$esito) {
						$orderActionOk = false;
						// break; no perche' mi blocca il foreach superiore
					}
				}
				
				if($debug) {
					if($orderActionOk)
						echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action OK';
					else
						echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action NOT OK!';
				}
				
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
						if($debug) echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;permission_OR '.$method_name.'('.$value_da_verificare.') esito '.$esito;						
					}
	
					if(!$esito) {
						$orderActionOR_Ok = false;
						// break; no perche' mi blocca il foreach superiore
					}
				}
	
				if($debug) {
					if($orderActionOR_Ok)
						echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action OK';
					else
						echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action NOT OK!';
				}	

				/*
				 * se nel controllo OrdersAction.permission era true, e' valido anche qui perche' sono in OR
				*/				
				if($orderActionOk || $orderActionOR_Ok) 
					$orderActionOk = true;
				
			} // if(!empty($result['OrdersAction']['permission_or']))



			if($orderActionOk) {
	
				$orderActions[$i]['OrdersAction'] = $result['OrdersAction'];
				$orderActions[$i]['OrdersAction']['url'] = $urlBase.'controller='.$result['OrdersAction']['controller'].'&action='.$result['OrdersAction']['action'].'&delivery_id='.$orderResults['Order']['delivery_id'].'&order_id='.$orderResults['Order']['id'];
	
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
	
		if($debug) {
			echo '<h3>Risultato delle Actions dell\'ordine</h3>';
			echo "<pre> ";
			print_r($orderActions);
			echo "</pre>";
		}
	
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
	
		if($user->organization['Organization']['payToDelivery']==$value_da_verificare)
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
	
		$options = array();
		$options['conditions'] = array(// 'DesOrdersOrganization.des_id' => $user->des_id,  potrebbe non averlo valorizzato
										'DesOrdersOrganization.organization_id' => $results['Order']['organization_id'],
										'DesOrdersOrganization.order_id' => $results['Order']['id']);
		$options['recursive'] = -1;								
		$desOrdersOrganizationResults = $DesOrdersOrganization->find('first', $options);
		/*
		echo "<pre>ActionsOrderComponent->orderHasDes() ";
		print_r($desOrdersOrganizationResults);
		echo "</pre>";
		*/
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

		$options = array();
		$options['conditions'] = array(// 'DesOrdersOrganization.des_id' => $user->des_id,  potrebbe non averlo valorizzato
										'DesOrdersOrganization.organization_id' => $results['Order']['organization_id'],
										'DesOrdersOrganization.order_id' => $results['Order']['id']);
		$options['recursive'] = 1;								
		$desOrdersOrganizationResults = $DesOrdersOrganization->find('first', $options);
		/*
		echo "<pre>";
		print_r($options);
		print_r($desOrdersOrganizationResults);
		echo "</pre>";
		*/
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
	 *  D.E.S.
	 *  dal DesOrdersOrganization estraggo des_supplier_id
	*/
	private function __isTitolareDesSupplier($user, $results) {
		
		$isTitolareDesSupplier = false;
		
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();

		$options = array();
		$options['conditions'] = array(// 'DesOrdersOrganization.des_id' => $user->des_id,  potrebbe non averlo valorizzato
										'DesOrdersOrganization.organization_id' => $results['Order']['organization_id'],
										'DesOrdersOrganization.order_id' => $results['Order']['id']);
		$options['recursive'] = -1;								
		$desOrdersOrganizationResults = $DesOrdersOrganization->find('first', $options);

		if(!empty($desOrdersOrganizationResults)) {
			$des_id = $desOrdersOrganizationResults['DesOrder']['des_id'];
			$user->des_id = $des_id;
			
			if($this->ActionsDesOrder->isTitolareDesSupplier($user, $desOrdersOrganizationResults))
				$isTitolareDesSupplier = true;	
		}
		
		return $isTitolareDesSupplier;
	}
	
	/*
	 * gestione dei colli (pezzi_confezione)
	 */
	private function __orderToValidate($user, $results) {
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
	*  se Order.state_code == PROCESSED-POST-DELIVERY
	*  li copio dal carrello alla dispensa
	*/
	private function __orderIsCartToStoreroom($user, $results) {

		$isCartToStoreroom = false;
		if($user->organization['Organization']['hasStoreroom']=='Y' && $results['Order']['state_code']=='PROCESSED-POST-DELIVERY') {
			App::import('Model', 'Storeroom');
			$Storeroom = new Storeroom;
			
			$storeroomResults = $Storeroom->getCartsToStoreroom($user, $results['Order']['id']);
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
		
		$orderState=array();
		$orderState2=array();
		
		App::import('Model', 'TemplatesOrdersState');
		$TemplatesOrdersState = new TemplatesOrdersState;
		
		$options = array();
		$options['conditions'] = array('TemplatesOrdersState.template_id' => $user->organization['Organization']['template_id'],
									   'TemplatesOrdersState.group_id' => $group_id,
									   'TemplatesOrdersState.flag_menu' => 'Y'
		);
		
		$options['order'] = array('TemplatesOrdersState.sort');
		$options['recursive'] = -1;
		$orderStates = $TemplatesOrdersState->find('all', $options);
				
		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($orderStates);
			echo "</pre>";
		}

		return $orderStates;
	}
	
	/*
	 * creo degli OrderActions di raggruppamento per controller (Orders, Carts, etc)
	 * 
	 * per Order::home e Order_home_simple
	*/
	public function getRaggruppamentoOrderActions($orderActions, $debug) {
		
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
		
		$raggruppamentoOrderActions = array();

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
				
		if($debug) {
			echo "<pre>";
			print_r($raggruppamentoOrderActions);
			echo "</pre>";		
		}
	
		return $raggruppamentoOrderActions;
	}
}
?>