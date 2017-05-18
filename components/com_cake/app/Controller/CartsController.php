<?php
App::uses('AppController', 'Controller');

class CartsController extends AppController {

   public $components = array('RequestHandler', 'ActionsDesOrder');
   
   public function beforeFilter() {
   		parent::beforeFilter();
 
   		$actionWithPermission = array('admin_managementCartsOne', 'admin_managementCartsGroupByUsers', 'admin_validationCarts', 'admin_trasport');   		if (in_array($this->action, $actionWithPermission)) {
	   		/*	   		 * ctrl che la consegna sia visibile in backoffice	   		*/	   		if(!empty($this->delivery_id)) {
	   				   			App::import('Model', 'Delivery');	   			$Delivery = new Delivery;	   			$results = $Delivery->read($this->user->organization['Organization']['id'], null, $this->delivery_id);
	   			if($results['Delivery']['isVisibleBackOffice']=='N') {	   				$this->Session->setFlash(__('msg_delivery_not_visible_backoffice'));	   				$this->myRedirect(Configure::read('routes_msg_stop'));	   			}  				   		}
	
	   		/*	   		 * ctrl che l'ordine sia visibile in backoffice	   		*/
	   		if(!empty($this->order_id)) {
	   				   			App::import('Model', 'Order');	   			$Order = new Order;	   			$results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);	   			if($results['Order']['isVisibleBackOffice']=='N') {	   				$this->Session->setFlash(__('msg_order_not_visible_backoffice'));	   				$this->myRedirect(Configure::read('routes_msg_stop'));	   			}   			
	   		}
   		} // end if (in_array($this->action, $actionWithPermission)) 
   }
   
   /*
    * il campo Cart.date si aggiorna in automatico ON UPDATE CURRENT_TIMESTAMP
   */
   public function cart_to_user_preview() {
   	
   		$this->ctrlHttpReferer();
   	
	   	$user_id = $this->user->get('id');
	   	if(empty($user_id)) {
	   		$this->Session->setFlash(__('msg_not_permission_guest'));
	   		$this->myRedirect(Configure::read('routes_msg_stop'));
	   	}
	      		   	 
	   	App::import('Model', 'ArticlesOrder');
	   	$ArticlesOrder = new ArticlesOrder;

	   	$conditions = array('Cart.user_id' => (int)$user_id);
	   	 
	   	// in AjaxGasCart setto con = valore created e modified
	   	$orderBy = array('CartPreview' => 'Cart.date DESC');
	   	
	   	$results = $ArticlesOrder->getArticoliDellUtenteInOrdine($this->user, $conditions, $orderBy, Configure::read('CartLimitPreview'));
	   	
	   	$this->set('results', $results);
	   
	   	$this->layout = 'ajax';
   }
   
   /*
    * se arrivo da Orders/admin_index.ctp $delivery_id, $order_id sono valorizzati
    * */
   public function admin_managementCartsOne() {
   	
   		$debug = false;
   	
	   	if(empty($this->order_id) || empty($this->delivery_id)) {	   		$this->Session->setFlash(__('msg_error_params'));	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));	   	}
	   	
	   	/*
	   	 * DES
	   	 */
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
				
				App::import('Model', 'DesOrder');
				$DesOrder = new DesOrder();
				
				$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
				if(!$this->ActionsDesOrder->isAclReferente($desOrdersResults['DesOrder']['state_code'])) {
					$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
					$this->myRedirect(array('controller' => 'Orders', 'action' => 'home', $this->order_id));				
				}
				
				/*
				echo "<pre>";
				print_r($desOrdersResults);
				echo "</pre>";	
				*/
				
				/*
				 * ctrl eventuali occorrenze di SummaryDesOrder
				*/
				App::import('Model', 'SummaryDesOrder');
				$SummaryDesOrder = new SummaryDesOrder;
				$summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id, $this->user->organization['Organization']['id']);
				/*
				echo "<pre>";
				print_r($summaryDesOrderResults);
				echo "</pre>";	
				*/
	
				$this->set(compact('desOrdersResults', 'summaryDesOrderResults'));
			}
		} // DES
		$this->set(compact('des_order_id'));
			   	
	   	App::import('Model', 'Order');
	   	$Order = new Order;
	   	
   	   	/* ctrl ACL */
	   	if($this->isSuperReferente()) {
	   			
	   	}
	   	else {	
		   	if(!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
		   		$this->Session->setFlash(__('msg_not_permission'));
		   		$this->myRedirect(Configure::read('routes_msg_stop'));
		   	}
	   }

	   $options =array();
	   $options['conditions'] = array ('Delivery.stato_elaborazione' => 'OPEN');
	   $this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	   
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
		
		$this->ctrlModuleConflicts($this->user, $this->order_id, 'managementCartsOne', $debug);
   }
                   
   public function admin_managementCartsGroupByUsers() {
   
   		$debug = false;
   	
	   	if(empty($this->order_id) || empty($this->delivery_id)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	   	 
	   	/*
	   	 * DES
	   	 */
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
				
				App::import('Model', 'DesOrder');
				$DesOrder = new DesOrder();
				
				$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
				if(!$this->ActionsDesOrder->isAclReferente($desOrdersResults['DesOrder']['state_code'])) {
					$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
					$this->myRedirect(array('controller' => 'Orders', 'action' => 'home', $this->order_id));				
				}
				
				/*
				echo "<pre>";
				print_r($desOrdersResults);
				echo "</pre>";	
				*/
				
				/*
				 * ctrl eventuali occorrenze di SummaryDesOrder
				*/
				App::import('Model', 'SummaryDesOrder');
				$SummaryDesOrder = new SummaryDesOrder;
				$summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id, $this->user->organization['Organization']['id']);

				$this->set(compact('desOrdersResults', 'summaryDesOrderResults'));
			}
		} // DES
		$this->set(compact('des_order_id'));
			   	 
	   	/* ctrl ACL */
	   	if($this->isSuperReferente()) {
				
		}
		else {
	   		App::import('Model', 'Order');
	   		$Order = new Order;
	   		if(!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
	   			$this->Session->setFlash(__('msg_not_permission'));
	   			$this->myRedirect(Configure::read('routes_msg_stop'));
	   		}
	   	}
		   
	   	$options = array();
		$options['conditions'] = array('Delivery.stato_elaborazione' => 'OPEN');
		$this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
   
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
		
		$this->ctrlModuleConflicts($this->user, $this->order_id, 'managementCartsGroupByUsers', $debug);
   }

   public function admin_managementCartsSplit() {
   
	   	if(empty($this->order_id) || empty($this->delivery_id)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	   	 
	   	/*
	   	 * DES
	   	 */
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
				
				App::import('Model', 'DesOrder');
				$DesOrder = new DesOrder();
				
				$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
				if(!$this->ActionsDesOrder->isAclReferente($desOrdersResults['DesOrder']['state_code'])) {
					$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
					$this->myRedirect(array('controller' => 'Orders', 'action' => 'home', $this->order_id));				
				}
				
				/*
				echo "<pre>";
				print_r($desOrdersResults);
				echo "</pre>";	
				*/
				
				/*
				 * ctrl eventuali occorrenze di SummaryDesOrder
				*/
				App::import('Model', 'SummaryDesOrder');
				$SummaryDesOrder = new SummaryDesOrder;
				$summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id, $this->user->organization['Organization']['id']);

				$this->set(compact('desOrdersResults', 'summaryDesOrderResults'));
			}
		} // DES
		$this->set(compact('des_order_id'));
			   	 
	   	/* ctrl ACL */
	   	if($this->isSuperReferente()) {
	   			
	   	}
	   	else {	
	   		App::import('Model', 'Order');
	   		$Order = new Order;
	   		if(!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
	   			$this->Session->setFlash(__('msg_not_permission'));
	   			$this->myRedirect(Configure::read('routes_msg_stop'));
	   		}
	   	}
	
	   	if ($this->request->is('post') || $this->request->is('put')) {
	   		
	   		App::import('Model', 'CartsSplit');
	   		$CartsSplit = new CartsSplit;
	
	   		$options = array();
	   		$options['fields'] = array('SUM(CartsSplit.importo_forzato) AS totImportoForzato', 'CartsSplit.organization_id', 'CartsSplit.order_id', 'CartsSplit.article_id', 'CartsSplit.article_organization_id', 'CartsSplit.user_id');
	   		$options['conditions'] = array('CartsSplit.organization_id' => $this->user->organization['Organization']['id'],
	   										'CartsSplit.order_id' => $this->order_id);
	   		$options['group'] = array('CartsSplit.organization_id', 'CartsSplit.order_id', 'CartsSplit.article_id', 'CartsSplit.article_organization_id', 'CartsSplit.user_id');
	   		$options['recursive'] = -1;
	   		$results = $CartsSplit->find('all', $options);   		 
	   		foreach ($results as $result) {
	   			
	   			$importo_forzato = $result[0]['totImportoForzato'];
	   			
	   			$sql = "UPDATE
						".Configure::read('DB.prefix')."carts
					SET
						importo_forzato = ".$importo_forzato."
					WHERE
						organization_id = ".$this->user->organization['Organization']['id']."
						AND order_id = ".$result['CartsSplit']['order_id']."
						AND article_organization_id = ".$result['CartsSplit']['article_organization_id']."
						AND article_id = ".$result['CartsSplit']['article_id']."
						AND user_id = ".$result['CartsSplit']['user_id'];
	   			//echo '<br/>'.$sql;
	   			try {
	   				$CartsSplit->query($sql);
	   				$esito = true;
	   			}
	   			catch (Exception $e) {
	   				CakeLog::write('error',$sql);
	   				CakeLog::write('error',$e);
	   				$esito = false;
	   			}  			
	   		} 
	   		
	  		$this->Session->setFlash(__('The carts split has been saved'));
	  		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
	  		
	   	}  // end if ($this->request->is('post') || $this->request->is('put'))
	   	
	   	$options = array();
		$options['conditions'] = array ('Delivery.stato_elaborazione' => 'OPEN');
		$this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
   
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
   }
    
   /*
    * se arrivo da Orders/admin_index.ctp $delivery_id, $order_id sono valorizzati
   * */
   public function admin_validationCarts() {

   		$debug = false;
   	
   		if(empty($this->order_id) || empty($this->delivery_id)) {	   		$this->Session->setFlash(__('msg_error_params'));	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));	   	}

	   	/*
	   	 * DES
	   	 */
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
				
				App::import('Model', 'DesOrder');
				$DesOrder = new DesOrder();
				
				$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
				if(!$this->ActionsDesOrder->isAclReferente($desOrdersResults['DesOrder']['state_code'])) {
					$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
					$this->myRedirect(array('controller' => 'Orders', 'action' => 'home', $this->order_id));				
				}
				
				/*
				echo "<pre>";
				print_r($desOrdersResults);
				echo "</pre>";	
				*/
				
				/*
				 * ctrl eventuali occorrenze di SummaryDesOrder
				*/
				App::import('Model', 'SummaryDesOrder');
				$SummaryDesOrder = new SummaryDesOrder;
				$summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id, $this->user->organization['Organization']['id']);

				$this->set(compact('desOrdersResults', 'summaryDesOrderResults'));
			}
		} // DES
		$this->set(compact('des_order_id'));
		
	   	/* ctrl ACL */
	   	if($this->isSuperReferente()) {
	   	
	   	}
	   	else {
	  		App::import('Model', 'Order');
		   	$Order = new Order;
		   	if(!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
		   		$this->Session->setFlash(__('msg_not_permission'));
		   		$this->myRedirect(Configure::read('routes_msg_stop'));
		   	}
	   	}

	   	/*
	   	 * aggiorno ArticlesOrder.qta_cart e ArticlesOrder.stato 
	   	 * perche' in admin_validationCartsEdit lo farebbe nel loop ma non funziona
	   	 */
	   	App::import('Model', 'ArticlesOrder');
	   	$ArticlesOrder = new ArticlesOrder;
	   	
	   	$options = array();
	   	$options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
	   									'ArticlesOrder.order_id' => $this->order_id);
	   	$options['recursive'] = -1;
	   	$results = $ArticlesOrder->find('all', $options);
	   	
	   	foreach ($results as $result) {
	   			
	   		$organization_id = $result['ArticlesOrder']['organization_id'];
	   		$order_id = $result['ArticlesOrder']['order_id'];
	   		$article_organization_id = $result['ArticlesOrder']['article_organization_id'];
	   		$article_id = $result['ArticlesOrder']['article_id'];
	   		
	   		$ArticlesOrder->aggiornaQtaCart_StatoQtaMax($organization_id, $order_id, $article_organization_id, $article_id, $debug);
	   	}
	   	
	   	/*
	   	 * associo gli articoli mancanti per il collo alla dispensa
	   	 */
   	   	if ($this->request->is('post') || $this->request->is('put')) {

   	   		if($debug) {
   	   			echo "<pre>";
   	   			print_r($this->request->data);
   	   			echo "</pre>";
   	   		}
   	   		
   	   		if($this->user->organization['Organization']['hasStoreroom']=='Y') {
   	   			/*   	   			 * ctrl se storeroom esiste   	   			* */   	   			App::import('Model', 'Storeroom');   	   			$Storeroom = new Storeroom;   	   			$storeroomUser = $Storeroom->getStoreroomUser($this->user);   	   			if(empty($storeroomUser)) {   	   				$this->Session->setFlash(__('StoreroomNotFound'));   	   				$this->myRedirect(Configure::read('routes_msg_exclamation'));   	   			} 
	   	   		App::import('Model', 'Cart');
	   	   		$Cart = new Cart;
				
	   	   		App::import('Model', 'AjaxGasCart');
	   	   		$AjaxGasCart = new AjaxGasCart;
	
	   	   		$article_order_id_selected = $this->request->data['article_order_id_selected'];
	   	   		$arr_article_order_id_selected = explode(',',$article_order_id_selected);
	   	   		
	   	   		if($debug) {
	   	   			echo "<pre>arr_article_order_id_selected ";
	   	   			print_r($arr_article_order_id_selected);
	   	   			echo "</pre>";
	   	   		}
	   	   		
	   	   		$user_id = $storeroomUser['User']['id'];
	   	   		/*
	   	   		 * $key = $result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id']
	   	   		 */
	   	   		foreach($this->request->data['Cart'] as $key => $value) {
	   	   			if(in_array($key, $arr_article_order_id_selected)) {
	   	   				
	   	   				$qta = current($value);
	   	   				list($order_id, $article_organization_id, $article_id) = explode('_', $key);
	   	   				
	   	   				if($debug) {
	   	   					echo '<br />user_id (dispensa) '.$user_id;
	   	   					echo '<br />order_id '.$order_id;
	   	   					echo '<br />article_id '.$article_id;
	   	   					echo '<br />article_organization_id '.$article_organization_id;
	   	   					echo '<br />qta '.$qta; 					
	   	   				}

						/*
						 * ctrl se l'articolo e' gia' nel carrello
						 */
						$options = array();
						$options['conditions'] = array('Cart.organization_id' => $this->user->organization['Organization']['id'],
														'Cart.order_id' => $order_id,
														'Cart.article_organization_id' => $article_organization_id,
														'Cart.article_id' => $article_id,
														'Cart.user_id' => $user_id);
						$options['recursive'] = -1;
						$cartResults = $Cart->find('first', $options);
						/*
						echo "<pre>";
						print_r($options);
						print_r($cartResults);
						echo "</pre>";
						*/						
						if(!empty($cartResults)) {
							if($debug) echo "<br />Articolo gia acquistato, aggiorno la QTA da ".$cartResults['Cart']['qta_forzato']." a ".($cartResults['Cart']['qta_forzato'] + $qta);
							$qta = ($cartResults['Cart']['qta_forzato'] + $qta);
						}
						else {
							if($debug) echo "<br />Articolo MAI acquistato, QTA ".$qta;
						}
						
	   	   				/*
	   	   				 * forzare_validazione=true non esegue la validazione (qta_minima, qta_massima_order)
	   	   				*/
	   	   				if($debug) echo "<pre>";
	   	   				$resultsJS = $AjaxGasCart->managementCart($this->user, $order_id, $article_organization_id, $article_id, $user_id, $qta, $backOffice=true, $forzare_validazione=true);
	   	   				if($debug) {
	   	   					echo "<pre>";
	   	   					print_r($resultsJS);
	   	   					echo "</pre>";
	   	   				}
	   	   					   	   				
	   	   				/*	   	   				 * gestione JavaScript	   	   				* */	   	   				$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId=0).'</script>';	   	   				$this->set('resultsJS',$resultsJS);	   	   				
	   	   			}  	   			
	   	   		}

		   		$this->Session->setFlash(__('The articles order has been saved to storeroom'));
				/*
				 * bugs, faccio un redirect su se stesso perche' se no ricarica la pg con i dati vecchi da AjaxGasCodes::box_validation_carts
				 */
				 $this->myRedirect(array('action' => 'validationCarts'));
   	   		} // end if($this->user->organization['Organization']['hasStoreroom']=='Y') 
	   	}  // end if ($this->request->is('post') || $this->request->is('put')) 
	   	
	   	$options = array();
	    $options['conditions'] = array ('Delivery.stato_elaborazione' => 'OPEN');
	    $this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	   		   	
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
   }

   /*
    * ArticlesOrder.key = $organization_id, $order_id, $article_organization_id, $article_id
    */
   public function admin_validation_carts_edit($delivery_id=0, $order_id=0, $article_organization_id=0, $article_id=0) {

   		$debug = false;
   		
	   	if ($this->request->is('post') || $this->request->is('put')) {
	   		
	   		if($debug) {
	   			echo "<pre>";
	   			print_r($this->request->data);
	   			echo "</pre>";
	   		}

	   		App::import('Model', 'AjaxGasCart');	   		$AjaxGasCart = new AjaxGasCart;
	   		
	   		App::import('Model', 'SummaryOrder');
	   		
	   		$utilsCrons = new UtilsCrons(new View(null));
	   		
	   		
			/*
			 * Cart.key = $order_id_$article_organization_id_$article_id_$user_id
			 */
	   		$tot_modificati=0;
	   		foreach($this->request->data['Cart'] as $key => $data) {
	   			 
	   			if(!empty($key) && strpos($key,'_') == true) { // escludo i campi hidden
	   			
		   			list($order_id,$article_organization_id,$article_id,$user_id) = explode('_',$key);
		   			
		   			if(!empty($order_id) && !empty($article_organization_id) && !empty($article_id) && !empty($user_id)) { // escludo altri campi (ex order_id o un nuovo user)

		   				$dataSource = $this->Cart->getDataSource();
		   				$dataSource->begin();
		   				 
						if(!$this->Cart->exists($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id, $user_id)) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));		
						}
						
						$qta = $data['qta'];
			   			$qta_prima_modifica = $data['qta_prima_modifica'];
			   			
			   			if($qta != $qta_prima_modifica) {
			   				
			   				if($debug) echo '<br />Tratto carrello dello user '.$user_id.' con qta '.$qta.' - articleOrder.order_id '.$order_id.' articleOrder.article_organization_id '.$article_organization_id.' articleOrder.article_id '.$article_id;
		
			   				/*
			   				 * forzare_validazione=true non esegue la validazione (qta_minima, qta_massima_order) 
			   				 */
			   				if($debug) echo "<pre>";
							$resultsJS = $AjaxGasCart->managementCart($this->user, $order_id, $article_organization_id, $article_id, $user_id, $qta, $backOffice=true, $forzare_validazione=true);
							if($debug) $resultsJS."</pre>";
							
							/*							 * gestione JavaScript							* */							$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId=0).'</script>';							$this->set('resultsJS',$resultsJS);
							
							$tot_modificati++;	

							/*
							 * ricalcolo SummaryOrders se esiste
							 */
							$SummaryOrder = new SummaryOrder;
							$SummaryOrder->ricalcolaPerSingoloUtente($this->user, $order_id, $user_id);
						}

						$dataSource->commit();
						
		   			} // end if(!empty($order_id) && !empty($article_organization_id) && !empty($article_id) && !empty($user_id)) 
	   			}
	   		} // end foreach($this->request->data['Cart'] as $key => $data)

	 
	   		 /*
	   		 * nuovo utente
	   		 */
	   		if(!empty($this->request->data['Cart']['user_id'])) {

	   			$order_id = $this->request->data['Cart']['order_id'];
	   			$article_organization_id = $this->request->data['Cart']['article_organization_id'];
	   			$article_id = $this->request->data['Cart']['article_id'];
	   			
	   			$rowId = $order_id.'_'.$article_organization_id.'_'.$article_id.'_0';
	   			echo $rowId;
	   			$qta = $this->request->data['Cart'][$rowId]['qta'];
	   			$user_id = $this->request->data['Cart']['user_id'];
	   			
	   			if($debug) echo '<br />Tratto carrello dello user NUOVO '.$user_id.' con qta '.$qta.' - articleOrder.order_id '.$order_id.' articleOrder.article_organization_id '.$article_organization_id.' articleOrder.article_id '.$article_id;
	   			if($qta>0) {
	   				/*
	   				 * forzare_validazione=true non esegue la validazione (qta_minima, qta_massima_order)
	   				*/
		   			if($debug) echo "<pre>";
		   			$resultsJS = $AjaxGasCart->managementCart($this->user, $order_id, $article_organization_id, $article_id, $user_id, $qta, $backOffice=true, $forzare_validazione=true);
		   			if($debug) echo $resultsJS."</pre>";
		   				
		   			/*
		   			 * gestione JavaScript
		   			* */
		   			$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId=0).'</script>';
		   			$this->set('resultsJS',$resultsJS);
		   				
		   			$tot_modificati++;
	   			}
	   		}
	   			
	   		if($tot_modificati>0) 
	   			$this->Session->setFlash(__('Validation Carts Edit has been saved'));
	   		else 
	   			$this->Session->setFlash(__('Validation Carts Edit no change'));  			
	   		
	   		if(!$debug) $this->myRedirect(array('action' => 'validationCarts'));
	   	} // end if ($this->request->is('post') || $this->request->is('put')) 
	   	else {
	   		if(empty($delivery_id) || empty($order_id) || empty($article_organization_id) || empty($article_id)) {	   			$this->Session->setFlash(__('msg_error_params'));	   			$this->myRedirect(Configure::read('routes_msg_exclamation'));	   		}
	   	}
	   			
		/*		 * estraggo i dati dell'articolo		 */		$results = $this->Cart->getCartToValidate($this->user, $delivery_id, $order_id, $article_organization_id, $article_id);
		$this->set('articlesOrdesResults',current($results));
	 		/*		 * stessa chiamata Ajax::admin_view_articles_order_carts simile		*/		
		App::import('Model', 'Cart');		$Cart = new Cart();			
		$options = array();		$options['conditions'] = array('Cart.organization_id' => $this->user->organization['Organization']['id'],				'Cart.order_id' => $order_id,
				'Cart.article_organization_id' => $article_organization_id,
				'Cart.article_id' => $article_id,
				'Cart.deleteToReferent' => 'N'		);		$options['recursive'] = 1;		$options['order'] = array(Configure::read('orderUser'));		$results = $Cart->find('all', $options);		$this->set('results', $results);
		
	   	/*	   	 * tutti i gruppi escluso gli users che hanno gia' effettuato acquisti	   	*/	   	App::import('Model', 'User');	   	$User = new User;	   	
		$users = array();
		if(!empty($results)) {
			$user_ids_da_escludere = '';
			foreach($results as $result) 
				$user_ids_da_escludere .= $result['Cart']['user_id'].',';
			if(!empty($user_ids_da_escludere)) {
				$user_ids_da_escludere = substr($user_ids_da_escludere, 0, (strlen($user_ids_da_escludere)-1));
					
				$conditions = array();
				$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'),
									'UserGroupMap.user_id NOT IN' => '('.$user_ids_da_escludere.')');				$users = $User->getUsersList($this->user, $conditions);
			}
		}		$this->set('users',$users);	   	
   }
	
   public function admin_view($id = null) {
	   	$this->Cart->id = $id;
	   	if (!$this->Cart->exists($this->user->organization['Organization']['id'])) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	   	$this->set('cart', $this->Cart->read($this->user->organization['Organization']['id'], null, $id));
   }
   
	/*
	  * SummaryOrder empty
	  *		Trasport importo
	  *			1) Inserisci importo
	  *				SummaryOrderTrasport empty (anche se gia vuoto)
	  *				SummaryOrderTrasport.importo: somma acquisti Cart
	  *				SummaryOrderTrasport.importo_trasport: 0
	  *				Order.trasport: importo
	  *				Order.trasport_type: empty
	  *			2) Aggiorna importo
	  *				SummaryOrderTrasport empty
	  *				SummaryOrderTrasport.importo: somma acquisti Cart
	  *				SummaryOrderTrasport.importo_trasport: 0
	  *				Order.trasport: importo
	  *				Order.trasport_type: empty
	  *			3) Elimina importo
	  *				SummaryOrderTrasport empty
	  *				Order.trasport: empty
	  *				Order.trasport_type: empty
	  *
	  *		Trasport type 'QTA','WEIGHT','USERS'
	  *
	  *		Trasport Submit i singoli importi su ogni utente
	  *				SummaryOrderTrasport not empty
	  *				SummaryOrderTrasport.importo_trasport: importo_trasport
	  *				Order.trasport_type: type
	  *
	  * SummaryOrder NOT empty
	  *		Trasport importo 
	  *			1) Inserisci importo
	  *				SummaryOrderTrasport empty (anche se gia vuoto)
	  *				SummaryOrderTrasport.importo: somma acquisti Cart + summaryOrder
	  *				SummaryOrderTrasport.importo_trasport: 0
	  *				Order.trasport: importo
	  *				Order.trasport_type: empty 
	  *			2) Aggiorna importo
	  *				se Order.typeGest = 'AGGREGATE': SummaryOrder delete
	  *				SummaryOrder sottraggo SummaryOrderTrasport.importo_trasport
	  *				SummaryOrderTrasport empty
	  *				SummaryOrderTrasport.importo: somma acquisti Cart + summaryOrder
	  *				SummaryOrderTrasport.importo_trasport: 0
	  *				Order.trasport: importo
	  *				Order.trasport_type: empty
	  *			3) Elimina importo
	  *				se Order.typeGest = 'AGGREGATE': SummaryOrder delete
	  *				SummaryOrder sottraggo SummaryOrderTrasport.importo_trasport
	  *				SummaryOrderTrasport empty
	  *				Order.trasport: empty
	  *				Order.trasport_type: empty
	  *
	  *		Trasport type 'QTA','WEIGHT','USERS'
	  *
	  *		Trasport Submit i singoli importi su ogni utente
	  *				SummaryOrderTrasport not empty
	  *				SummaryOrder.importo = (SummaryOrder.importo - SummaryOrderTrasport.importo_trasport[OLD] + importo_trasport[NEW]) => 
	  *				SummaryOrderTrasport.importo_trasport: importo_trasport
	  *				Order.trasport_type: type
	  *
	  *  actionSubmit = submitImportoInsert   inserisce importo del trasporto
	  *  actionSubmit = submitImportoUpdate   aggiorna importo del trasporto
	  *  actionSubmit = submitImportoDelete   elimina importo del trasporto
	  *  actionSubmit = submitElabora		  salva per ogni utente la % di trasporto e aggiorna SummaryOrder
	 */
    public function admin_trasport() {
   
   		$debug = false;
   		
	   	if(empty($this->order_id) || empty($this->delivery_id)) {	   		$this->Session->setFlash(__('msg_error_params'));	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));	   	}
	 
	   	/*
	   	 * DES
	   	 */
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
				
				App::import('Model', 'DesOrder');
				$DesOrder = new DesOrder();
				
				$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
				if(!$this->ActionsDesOrder->isAclReferente($desOrdersResults['DesOrder']['state_code'])) {
					$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
					$this->myRedirect(array('controller' => 'Orders', 'action' => 'home', $this->order_id));				
				}
				
				/*
				echo "<pre>";
				print_r($desOrdersResults);
				echo "</pre>";	
				*/
				
				/*
				 * ctrl eventuali occorrenze di SummaryDesOrder
				*/
				App::import('Model', 'SummaryDesOrder');
				$SummaryDesOrder = new SummaryDesOrder;
				$summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id, $this->user->organization['Organization']['id']);

				$this->set(compact('desOrdersResults', 'summaryDesOrderResults'));
			}
		} // DES
		$this->set(compact('des_order_id'));
			   	
	   	/*
	   	 * ctrl configurazione Organization
	   	*/
	   	if($this->user->organization['Organization']['hasTrasport']=='N') {
	   		$this->Session->setFlash(__('msg_not_organization_config'));
	   		$this->myRedirect(Configure::read('routes_msg_stop'));
	   	}
	   	 
	   	App::import('Model', 'Order');
	   	$Order = new Order;

		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.id' => $this->order_id);
		$options['recursive'] = -1;
		$orderResults = $Order->find('first', $options);
	   	if (empty($orderResults)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}   	 
	   	   	
	   	/* ctrl ACL */
	   	if($this->isSuperReferente()) {
	   		 
	   	}
	   	else { 
	   		if(!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
	   			$this->Session->setFlash(__('msg_not_permission'));
	   			$this->myRedirect(Configure::read('routes_msg_stop'));
	   		}
	   	}
   	    
  	 	if ($this->request->is('post') || $this->request->is('put')) {

  	 		if(isset($this->request['data']['SummaryOrderTrasport']['actionSubmit']) && 
	   		   !empty($this->request['data']['SummaryOrderTrasport']['actionSubmit'])) {

  	 			App::import('Model', 'SummaryOrder');
  	 			$SummaryOrder = new SummaryOrder;
  	 			
	   			App::import('Model', 'SummaryOrderTrasport');
	   			$SummaryOrderTrasport = new SummaryOrderTrasport;
	   			
				$trasport = $this->request['data']['trasport'];
					
				/*
				 *  estraggo eventuali aggregazioni SummaryOrder
				*/
				$resultsSummaryOrder = $SummaryOrder->select_to_order($this->user, $this->order_id);
				/*
				echo "<pre>";
				print_r($resultsSummaryOrder);
				echo "</pre>"; 
				*/
				if($debug) {
					if(empty($resultsSummaryOrder)) 
						echo '<br />Non ci sono occorrenze in SummaryOrder (dati aggregati e importo = importo + trasporto)';
					else 
						echo '<br />Trovate occorrenze in SummaryOrder (dati aggregati e importo = importo + trasporto)';
				}
				
				 /*
				  *  actionSubmit = submitImportoInsert   inserisce importo del trasporto
				  *  actionSubmit = submitImportoUpdate   aggiorna importo del trasporto
				  *  actionSubmit = submitImportoDelete   elimina importo del trasporto
				  *  actionSubmit = submitElabora		  salva per ogni utente la % di trasporto e aggiorna SummaryOrder
				 */
				try {
								switch ($this->request['data']['SummaryOrderTrasport']['actionSubmit']) {
	   				case 'submitImportoInsert':	/* inserisce importo del trasporto */
	   					
	   					/*
	   					 * ripulisco SummaryOrderTrasport anche se gia' vuoto
	   					 */
	   					$SummaryOrderTrasport->delete_to_order($this->user, $this->order_id, $debug);

	   					/*
	   					 * aggiorno SummaryOrderTrasport
						 * 		importo_trasport = 0 (dettaglio per ogni utente)
						 * 		importo = 0 (somma acquisti Cart + summaryOrder)
	   					*/
	   					$SummaryOrderTrasport->populate_to_order($this->user, $this->order_id, $debug);
	   					
	   					/*
	   					 * aggiorno Order
	   					*/	   					
	   					$sql ="UPDATE `".Configure::read('DB.prefix')."orders`
				   			   SET
									trasport = ".$this->importoToDatabase($trasport).",
									trasport_type = null,
	   								modified = '".date('Y-m-d H:i:s')."'
				 			  WHERE
			   						organization_id = ".(int)$this->user->organization['Organization']['id']."
			   						and id = ".$this->order_id;
	   					if($debug) echo '<br />'.$sql;
	   					$this->Cart->query($sql);
	   					
	   					$this->Session->setFlash(__('Insert Trasport'));	   					
	   				break;
	   				case 'submitImportoUpdate': /* aggiorna importo del trasporto */
	   					
	   					/*
	   					 * SummaryOrder.importo sottraggo SummaryOrderTrasport.importo_trasport
	   					 * 		se non avevo ancora aggiunto il trasporto in SummaryOrder, SummaryOrderTrasport.importo_trasport sara' = 0
	   					 * dopo con submitElabora a SummaryOrder.importo aggiungero' SummaryOrderTrasport.importo_trasport
	   					 */
	   					if(!empty($resultsSummaryOrder)) {
	   						$SummaryOrder->delete_trasport($this->user, $this->order_id, $debug);
						
							/*
							 * ricreo SummaryOrder perche' possono esserci state modifiche
							 */
							if($orderResults['Order']['typeGest']!='AGGREGATE') {
								$SummaryOrder->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrderTrasport->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrder->populate_to_order($this->user, $this->order_id, $debug);	
							}	
						}
						
				
	   					/*
	   					 * ripulisco SummaryOrderTrasport anche se gia' vuoto
	   					*/
	   					$SummaryOrderTrasport->delete_to_order($this->user, $this->order_id, $debug);
	   						   					
	   					/*
	   					 * aggiorno SummaryOrderTrasport
	   					* 		importo_trasport = 0 (dettaglio per ogni utente)
	   					* 		importo = 0 (somma acquisti Cart + summaryOrder)
	   					*/
	   					$SummaryOrderTrasport->populate_to_order($this->user, $this->order_id, $debug);
	   						
	   					/*
	   					 * aggiorno Order
	   					*/
	   					$sql ="UPDATE `".Configure::read('DB.prefix')."orders`
				   			   SET
									trasport = ".$this->importoToDatabase($trasport).",
									trasport_type = null,
	   								modified = '".date('Y-m-d H:i:s')."'
				 			  WHERE
			   						organization_id = ".(int)$this->user->organization['Organization']['id']."
			   						and id = ".$this->order_id;
	   					if($debug) echo '<br />'.$sql;
	   					$this->Cart->query($sql);
	   						   					
	   					/*
						 * tolgo in SummaryOrder l'importo del trasporto perche' e' la somma di sum(Cart.importo) + SummaryOrderTrasport.importo_trasport
						 */
						$SummaryOrder->delete_trasport($this->user, $this->order_id, $debug);

	   					$this->Session->setFlash(__('Insert Trasport'));
	  				break;
	   				case 'submitImportoDelete': /* elimina importo del trasporto */
	   					
	   					/*
	   					 * SummaryOrder.importo sottraggo SummaryOrderTrasport.importo_trasport
	   					* 		se non avevo ancora aggiunto il trasporto in SummaryOrder, SummaryOrderTrasport.importo_trasport sara' = 0
	   					* dopo con submitElabora a SummaryOrder.importo aggiungero' SummaryOrderTrasport.importo_trasport
	   					*/
	   					if(!empty($resultsSummaryOrder)) {
	   						$SummaryOrder->delete_trasport($this->user, $this->order_id, $debug);
						
							/*
							 * ricreo SummaryOrder perche' possono esserci state modifiche
							 */
							if($orderResults['Order']['typeGest']!='AGGREGATE') {
								$SummaryOrder->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrderTrasport->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrder->populate_to_order($this->user, $this->order_id, $debug);	
							}	
						}	
	   					
	   					/*
	   					 * ripulisco SummaryOrderTrasport anche se gia' vuoto
	   					*/
	   					$SummaryOrderTrasport->delete_to_order($this->user, $this->order_id, $debug);
	   					
	   					/*
	   					 * ripulisco Order
	   					*/
	   					$SummaryOrderTrasport->delete_trasport_to_order($this->user, $this->order_id, $debug);
	   					
	   					$this->Session->setFlash(__('Delete Trasport'));
	   				break;
	   				case 'submitElabora': /* salva per ogni utente la % di trasporto e aggiorna SummaryOrder */
	   					
	   					/*
	   					 * popolo SummaryOrderTrasport
	   					 * 		ho SummaryOrderTrasport.importo_trasport = 0 (dettaglio di ogni utente) => dopo lo popolo con i campi del form 
	   					*/
	   					$resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($this->user, $this->order_id, $debug);
	   					if(empty($resultsSummaryOrderTrasport))
	   						$SummaryOrderTrasport->populate_to_order($this->user, $this->order_id, $debug);
	   					
	   					/*
	   					 * aggiorno Order
	   					 */	   					$trasport_type_db = null;	   					switch ($this->request['data']['trasport-options']) {	   						case "options-qta":	   							$trasport_type_db = 'QTA';	   							break;	   						case "options-weight":	   							$trasport_type_db = 'WEIGHT';	   							break;	   						case "options-users":	   							$trasport_type_db = 'USERS';	   							break;	   					}	   						   					$sql ="UPDATE 
	   								`".Configure::read('DB.prefix')."orders`	   							SET	   								trasport = ".$this->importoToDatabase($trasport).",
	   								trasport_type = '$trasport_type_db',	   								modified = '".date('Y-m-d H:i:s')."'	   							WHERE	   								organization_id = ".(int)$this->user->organization['Organization']['id']."	   								and id = ".$this->order_id;	   					if($debug) echo '<br />'.$sql;	   					$this->Cart->query($sql);
	   					
	   					/*
	   					 * valorizzo SummaryOrderTrasport.importo_trasport (dettaglio di ogni utente)
	   					 * aggiorno SummaryOrder.importo = SummaryOrder.importo - SummaryOrderTrasport.importo_trasport-OLD + SummaryOrderTrasport.importo_trasport-NEW
	   					 */
	   					if(isset($this->request['data']['Data']))
	   					foreach($this->request['data']['Data'] as $key => $value) {
	   						$user_id = $key;
							$importo_trasport = $this->importoToDatabase($value);

							/*
							 * aggiorno SummaryOrder togliendo l'eventuale vecchio importo e aggiungendo il nuovo
							*/
							if(!empty($resultsSummaryOrder)) {
								
								$resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($this->user, $this->order_id, $debug);
								foreach($resultsSummaryOrderTrasport as $numSummaryOrderTrasport => $summaryOrderTrasport) {
									
									if($summaryOrderTrasport['SummaryOrderTrasport']['user_id'] == $user_id) {
										
										$importo_trasport_old = $summaryOrderTrasport['SummaryOrderTrasport']['importo_trasport'];

										$sql = "UPDATE ".Configure::read('DB.prefix')."summary_orders
												SET
													importo = (importo + $importo_trasport - $importo_trasport_old)
												WHERE
													organization_id = ".(int)$this->user->organization['Organization']['id']."
													AND user_id = ".(int)$user_id."
													AND order_id = ".(int)$this->order_id;
										if($debug) echo '<br />'.$sql;
										$result = $this->Cart->query($sql);
										
										unset($summaryOrderTrasport[$numSummaryOrderTrasport]);
									}
								}
							} // end if(!empty($resultsSummaryOrder))
									
			   				/*
			   				 * aggiorno SummaryOrderTrasport.importo_trasport
			   				*/																
							$sql = "UPDATE
								".Configure::read('DB.prefix')."summary_order_trasports
							SET
								importo_trasport = '$importo_trasport',
								modified = '".date('Y-m-d H:i:s')."'
							WHERE
								organization_id = ".(int)$this->user->organization['Organization']['id']."
								and order_id = ".(int)$this->order_id." 
								and user_id = ".(int)$user_id;
							if($debug) echo '<br />'.$sql;
							$result = $SummaryOrderTrasport->query($sql);
								
							$this->Session->setFlash(__('Trasport has been saved'));
						}	
	   				break;
	   					
	   			} // end swicth
	   			
				}catch (Exception $e) {
	   				CakeLog::write('error',$sql);
	   				CakeLog::write('error',$e);
	   				if($debug) echo '<br />'.$e;
	   			}
	   		}  // end if(isset($this->request['data']['SummaryOrderTrasport']['actionSubmitImporto']) && ...	   
	    } // if ($this->request->is('post') || $this->request->is('put')) 
	    
	    $options =array();
	    $options['conditions'] = array ('Delivery.stato_elaborazione' => 'OPEN');
	    $this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	    
	    /*
	     * legenda profilata
	    */
    	$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
	    $orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
	    $this->set('orderStatesToLegenda', $orderStatesToLegenda);
   }
   
   public function admin_cost_more() {
   	 
   	$debug = false;
   	 
   	if(empty($this->order_id) || empty($this->delivery_id)) {
   		$this->Session->setFlash(__('msg_error_params'));
   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
   	}
   	    
	   	/*
	   	 * DES
	   	 */
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
				
				App::import('Model', 'DesOrder');
				$DesOrder = new DesOrder();
				
				$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
				if(!$this->ActionsDesOrder->isAclReferente($desOrdersResults['DesOrder']['state_code'])) {
					$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
					$this->myRedirect(array('controller' => 'Orders', 'action' => 'home', $this->order_id));				
				}
				
				/*
				echo "<pre>";
				print_r($desOrdersResults);
				echo "</pre>";	
				*/
				
				/*
				 * ctrl eventuali occorrenze di SummaryDesOrder
				*/
				App::import('Model', 'SummaryDesOrder');
				$SummaryDesOrder = new SummaryDesOrder;
				$summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id, $this->user->organization['Organization']['id']);

				$this->set(compact('desOrdersResults', 'summaryDesOrderResults'));
			}
		} // DES
		$this->set(compact('des_order_id'));
		   	    
   	App::import('Model', 'Order');
   	$Order = new Order;
   
	$options = array();
	$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
									'Order.id' => $this->order_id);
	$options['recursive'] = -1;
	$orderResults = $Order->find('first', $options);
   	if (empty($orderResults)) {
   		$this->Session->setFlash(__('msg_error_params'));
   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
   	}  
   		
   	/* ctrl ACL */
   	if($this->isSuperReferente()) {
   		 
   	}
   	else {
   		if(!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
   			$this->Session->setFlash(__('msg_not_permission'));
   			$this->myRedirect(Configure::read('routes_msg_stop'));
   		}
   	}
   
   	if ($this->request->is('post') || $this->request->is('put')) {
   
   		if(isset($this->request['data']['SummaryOrderCostMore']['actionSubmit']) &&
   		!empty($this->request['data']['SummaryOrderCostMore']['actionSubmit'])) {
   
   			App::import('Model', 'SummaryOrder');
   			$SummaryOrder = new SummaryOrder;
   			 
   			App::import('Model', 'SummaryOrderCostMore');
   			$SummaryOrderCostMore = new SummaryOrderCostMore;
   	   
   			$cost_more = $this->request['data']['cost_more'];
   
   			/*
   			 *  estraggo eventuali aggragazioni SummaryOrder
   			*/
   			$resultsSummaryOrder = $SummaryOrder->select_to_order($this->user, $this->order_id);
   			if($debug) {
   				if(empty($resultsSummaryOrder))
   					echo '<br />Non ci sono occorrenze in SummaryOrder (dati aggregati e importo = importo + cost_more)';
   				else
   					echo '<br />Trovate occorrenze in SummaryOrder (dati aggregati e importo = importo + cost_more)';
   			}
   
   			/*
   			 *  actionSubmit = submitImportoInsert   inserisce importo del cost_more
   			*  actionSubmit = submitImportoUpdate   aggiorna importo del cost_more
   			*  actionSubmit = submitImportoDelete   elimina importo del cost_more
   			*  actionSubmit = submitElabora		  salva per ogni utente la % di cost_more e aggiorna SummaryOrder
   			*/
   			try {
   
   				switch ($this->request['data']['SummaryOrderCostMore']['actionSubmit']) {
   					case 'submitImportoInsert':	/* inserisce importo del cost_more */
   							
   						/*
   						 * ripulisco SummaryOrderCostMore anche se gia' vuoto
   						 */
   						$SummaryOrderCostMore->delete_to_order($this->user, $this->order_id, $debug);
   
   						/*
   						 * aggiorno SummaryOrderCostMore
   						* 		importo_cost_more = 0 (dettaglio per ogni utente)
   						* 		importo = 0 (somma acquisti Cart + summaryOrder)
   						*/
   						$SummaryOrderCostMore->populate_to_order($this->user, $this->order_id, $debug);
   							
   						/*
   						 * aggiorno Order
   						*/
   						$sql ="UPDATE `".Configure::read('DB.prefix')."orders`
				   			   SET
									cost_more = ".$this->importoToDatabase($cost_more).",
									cost_more_type = null,
	   								modified = '".date('Y-m-d H:i:s')."'
				 			  WHERE
			   						organization_id = ".(int)$this->user->organization['Organization']['id']."
			   						and id = ".$this->order_id;
   						if($debug) echo '<br />'.$sql;
   						$this->Cart->query($sql);
   							
   						$this->Session->setFlash(__('Insert CostMore'));
   						break;
   					case 'submitImportoUpdate': /* aggiorna importo del cost_more */
   							
   						/*
   						 * SummaryOrder.importo sottraggo SummaryOrderCostMore.importo_cost_more
   						 * 		se non avevo ancora aggiunto il cost_more in SummaryOrder, SummaryOrderCostMore.importo_cost_more sara' = 0
   						 * dopo con submitElabora a SummaryOrder.importo aggiungero' SummaryOrderCostMore.importo_cost_more
   						 */
   						if(!empty($resultsSummaryOrder)) {
   							$SummaryOrder->delete_cost_more($this->user, $this->order_id, $debug);
   							
						
							/*
							 * ricreo SummaryOrder perche' possono esserci state modifiche
							 */
							if($orderResults['Order']['typeGest']!='AGGREGATE') {
								$SummaryOrder->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrderCostMore->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrder->populate_to_order($this->user, $this->order_id, $debug);	
							}	   							
   						}
   
   						/*
   						 * ripulisco SummaryOrderCostMore anche se gia' vuoto
   						*/
   						$SummaryOrderCostMore->delete_to_order($this->user, $this->order_id, $debug);
   
   						/*
   						 * aggiorno SummaryOrderCostMore
   						* 		importo_cost_more = 0 (dettaglio per ogni utente)
   						* 		importo = 0 (somma acquisti Cart + summaryOrder)
   						*/
   						$SummaryOrderCostMore->populate_to_order($this->user, $this->order_id, $debug);
   
   						/*
   						 * aggiorno Order
   						*/
   						$sql ="UPDATE `".Configure::read('DB.prefix')."orders`
				   			   SET
									cost_more = ".$this->importoToDatabase($cost_more).",
									cost_more_type = null,
	   								modified = '".date('Y-m-d H:i:s')."'
				 			  WHERE
			   						organization_id = ".(int)$this->user->organization['Organization']['id']."
			   						and id = ".$this->order_id;
   						if($debug) echo '<br />'.$sql;
   						$this->Cart->query($sql);
   
   						/*
   						 * tolgo in SummaryOrder l'importo del cost_more perche' e' la somma di sum(Cart.importo) + SummaryOrderCostMore.importo_cost_more
   						*/
   						$SummaryOrder->delete_cost_more($this->user, $this->order_id, $debug);
   
   						$this->Session->setFlash(__('Insert CostMore'));
   						break;
   					case 'submitImportoDelete': /* elimina importo del cost_more */
   							
   						/*
   						 * SummaryOrder.importo sottraggo SummaryOrderCostMore.importo_cost_more
   						 * 		se non avevo ancora aggiunto il cost_more in SummaryOrder, SummaryOrderCostMore.importo_cost_more sara' = 0
   						 * dopo con submitElabora a SummaryOrder.importo aggiungero' SummaryOrderCostMore.importo_cost_more
   						 */
   						if(!empty($resultsSummaryOrder)) {
   							$SummaryOrder->delete_cost_more($this->user, $this->order_id, $debug);
   							
							/*
							 * ricreo SummaryOrder perche' possono esserci state modifiche
							 */
							if($orderResults['Order']['typeGest']!='AGGREGATE') {
								$SummaryOrder->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrderCostMore->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrder->populate_to_order($this->user, $this->order_id, $debug);	
							}	   							
   						}
   							
   						/*
   						 * ripulisco SummaryOrderCostMore anche se gia' vuoto
   						*/
   						$SummaryOrderCostMore->delete_to_order($this->user, $this->order_id, $debug);
   							
   						/*
   						 * ripulisco Order
   						*/
   						$SummaryOrderCostMore->delete_cost_more_to_order($this->user, $this->order_id, $debug);
   							
   						$this->Session->setFlash(__('Delete CostMore'));
   						break;
   					case 'submitElabora': /* salva per ogni utente la % di cost_more e aggiorna SummaryOrder */
   							
   						/*
   						 * popolo SummaryOrderCostMore
   						 * 		ho SummaryOrderCostMore.importo_cost_more = 0 (dettaglio di ogni utente) => dopo lo popolo con i campi del form
   						 */
   						$resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($this->user, $this->order_id, $debug);
   						if(empty($resultsSummaryOrderCostMore))
   							$SummaryOrderCostMore->populate_to_order($this->user, $this->order_id, $debug);
   							
   						/*
   						 * aggiorno Order
   						*/
   						$cost_more_type_db = null;
   						switch ($this->request['data']['cost-more-options']) {
   							case "options-qta":
   								$cost_more_type_db = 'QTA';
   								break;
   							case "options-weight":
   								$cost_more_type_db = 'WEIGHT';
   								break;
   							case "options-users":
   								$cost_more_type_db = 'USERS';
   								break;
   						}
   							
   						$sql ="UPDATE
	   								`".Configure::read('DB.prefix')."orders`
	   							SET
	   								cost_more = ".$this->importoToDatabase($cost_more).",
   	   								cost_more_type = '$cost_more_type_db',
   	   								modified = '".date('Y-m-d H:i:s')."'
	   							WHERE
	   								organization_id = ".(int)$this->user->organization['Organization']['id']."
	   								and id = ".$this->order_id;
   						if($debug) echo '<br />'.$sql;
   						$this->Cart->query($sql);
   							
   						/*
   						 * valorizzo SummaryOrderCostMore.importo_cost_more (dettaglio di ogni utente)
   						* aggiorno SummaryOrder.importo = SummaryOrder.importo - SummaryOrderCostMore.importo_cost_more-OLD + SummaryOrderCostMore.importo_cost_more-NEW
   						*/
   						foreach($this->request['data']['Data'] as $key => $value) {
   							$user_id = $key;
   							$importo_cost_more = $this->importoToDatabase($value);
   
   							/*
   							 * aggiorno SummaryOrder togliendo l'eventuale vecchio importo e aggiungendo il nuovo
   							*/
   							if(!empty($resultsSummaryOrder)) {
   
   								$resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($this->user, $this->order_id, $debug);
   								foreach($resultsSummaryOrderCostMore as $numSummaryOrderCostMore => $summaryOrderCostMore) {
   										
   									if($summaryOrderCostMore['SummaryOrderCostMore']['user_id'] == $user_id) {
   
   										$importo_cost_more_old = $summaryOrderCostMore['SummaryOrderCostMore']['importo_cost_more'];
   
   										$sql = "UPDATE ".Configure::read('DB.prefix')."summary_orders
   												SET
   													importo = (importo + $importo_cost_more - $importo_cost_more_old)
   												WHERE
   													organization_id = ".(int)$this->user->organization['Organization']['id']."
   												AND user_id = ".(int)$user_id."
   												AND order_id = ".(int)$this->order_id;
   										if($debug) echo '<br />'.$sql;
   										$result = $this->Cart->query($sql);
   
   										unset($summaryOrderCostMore[$numSummaryOrderCostMore]);
   									}
   								}
   							} // end if(!empty($resultsSummaryOrder))
   										
   							/*
   							* aggiorno SummaryOrderCostMore.importo_cost_more
   							*/
   							$sql = "UPDATE
   										".Configure::read('DB.prefix')."summary_order_cost_mores
   									SET
   										importo_cost_more = '$importo_cost_more',
   										modified = '".date('Y-m-d H:i:s')."'
   									WHERE
   										organization_id = ".(int)$this->user->organization['Organization']['id']."
   										AND order_id = ".(int)$this->order_id."
   										AND user_id = ".(int)$user_id;
   							if($debug) echo '<br />'.$sql;
   							$result = $SummaryOrderCostMore->query($sql);
   
   							$this->Session->setFlash(__('CostMore has been saved'));
   						}
   						break;
   							
   				} // end swicth
   				 
   			}catch (Exception $e) {
   				CakeLog::write('error',$sql);
   				CakeLog::write('error',$e);
   				if($debug) echo '<br />'.$e;
   			}
   		}  // end if(isset($this->request['data']['SummaryOrderCostMore']['actionSubmitImporto']) && ...
   	} // if ($this->request->is('post') || $this->request->is('put'))
   	 
   	$options =array();
   	$options['conditions'] = array ('Delivery.stato_elaborazione' => 'OPEN');
   	$this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
   	 
   	/*
   	 * legenda profilata
   	*/
   	$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
   	$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
   	$this->set('orderStatesToLegenda', $orderStatesToLegenda);
   }

   public function admin_cost_less() {
   	 
   	$debug = false;
   	 
   	if(empty($this->order_id) || empty($this->delivery_id)) {
   		$this->Session->setFlash(__('msg_error_params'));
   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
   	}
  
	   	/*
	   	 * DES
	   	 */
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
				
				App::import('Model', 'DesOrder');
				$DesOrder = new DesOrder();
				
				$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
				if(!$this->ActionsDesOrder->isAclReferente($desOrdersResults['DesOrder']['state_code'])) {
					$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
					$this->myRedirect(array('controller' => 'Orders', 'action' => 'home', $this->order_id));				
				}
				
				/*
				echo "<pre>";
				print_r($desOrdersResults);
				echo "</pre>";	
				*/
				
				/*
				 * ctrl eventuali occorrenze di SummaryDesOrder
				*/
				App::import('Model', 'SummaryDesOrder');
				$SummaryDesOrder = new SummaryDesOrder;
				$summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id, $this->user->organization['Organization']['id']);

				$this->set(compact('desOrdersResults', 'summaryDesOrderResults'));
			}
		} // DES
		$this->set(compact('des_order_id'));
	
	App::import('Model', 'Order');
	$Order = new Order;
	   
	$options = array();
	$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
									'Order.id' => $this->order_id);
	$options['recursive'] = -1;
	$orderResults = $Order->find('first', $options);
   	if (empty($orderResults)) {
   		$this->Session->setFlash(__('msg_error_params'));
   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
   	}  
   	 
   	/* ctrl ACL */
   	if($this->isSuperReferente()) {
   
   	}
   	else {
   		if(!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
   			$this->Session->setFlash(__('msg_not_permission'));
   			$this->myRedirect(Configure::read('routes_msg_stop'));
   		}
   	}
   	 
   	if ($this->request->is('post') || $this->request->is('put')) {
   		 
   		if(isset($this->request['data']['SummaryOrderCostLess']['actionSubmit']) &&
   		!empty($this->request['data']['SummaryOrderCostLess']['actionSubmit'])) {
   			 
   			App::import('Model', 'SummaryOrder');
   			$SummaryOrder = new SummaryOrder;
   				
   			App::import('Model', 'SummaryOrderCostLess');
   			$SummaryOrderCostLess = new SummaryOrderCostLess;
   				
   			$cost_less = $this->request['data']['cost_less'];
   			 
   			/*
   			 *  estraggo eventuali aggragazioni SummaryOrder
   			*/
   			$resultsSummaryOrder = $SummaryOrder->select_to_order($this->user, $this->order_id);
   			if($debug) {
   				if(empty($resultsSummaryOrder))
   					echo '<br />Non ci sono occorrenze in SummaryOrder (dati aggregati e importo = importo + cost_less)';
   				else
   					echo '<br />Trovate occorrenze in SummaryOrder (dati aggregati e importo = importo + cost_less)';
   			}
   			 
   			/*
   			 *  actionSubmit = submitImportoInsert   inserisce importo del cost_less
   			*  actionSubmit = submitImportoUpdate   aggiorna importo del cost_less
   			*  actionSubmit = submitImportoDelete   elimina importo del cost_less
   			*  actionSubmit = submitElabora		  salva per ogni utente la % di cost_less e aggiorna SummaryOrder
   			*/
   			try {
   				switch ($this->request['data']['SummaryOrderCostLess']['actionSubmit']) {
   					case 'submitImportoInsert':	/* inserisce importo del cost_less */
   
   						/*
   						 * ripulisco SummaryOrderCostLess anche se gia' vuoto
   						 */
   						$SummaryOrderCostLess->delete_to_order($this->user, $this->order_id, $debug);
   						 
   						/*
   						 * aggiorno SummaryOrderCostLess
   						* 		importo_cost_less = 0 (dettaglio per ogni utente)
   						* 		importo = 0 (somma acquisti Cart + summaryOrder)
   						*/
   						$SummaryOrderCostLess->populate_to_order($this->user, $this->order_id, $debug);
   
   						/*
   						 * aggiorno Order
   						*/
   						$sql ="UPDATE `".Configure::read('DB.prefix')."orders`
				   			   SET
									cost_less = ".$this->importoToDatabase($cost_less).",
									cost_less_type = null,
	   								modified = '".date('Y-m-d H:i:s')."'
				 			  WHERE
			   						organization_id = ".(int)$this->user->organization['Organization']['id']."
			   						and id = ".$this->order_id;
   						if($debug) echo '<br />'.$sql;
   						$this->Cart->query($sql);
   
   						$this->Session->setFlash(__('Insert CostLess'));
   						break;
   					case 'submitImportoUpdate': /* aggiorna importo del cost_less */
   
   						/*
   						 * SummaryOrder.importo sottraggo SummaryOrderCostLess.importo_cost_less
   						 * 		se non avevo ancora aggiunto il cost_less in SummaryOrder, SummaryOrderCostLess.importo_cost_less sara' = 0
   						 * dopo con submitElabora a SummaryOrder.importo aggiungero' SummaryOrderCostLess.importo_cost_less
   						 */
   						if(!empty($resultsSummaryOrder)) {
   							$SummaryOrder->delete_cost_less($this->user, $this->order_id, $debug);
   							
							/*
							 * ricreo SummaryOrder perche' possono esserci state modifiche
							 */
							if($orderResults['Order']['typeGest']!='AGGREGATE') {
								$SummaryOrder->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrderCostLess->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrder->populate_to_order($this->user, $this->order_id, $debug);	
							}
						}	   							
   						 
   						/*
   						 * ripulisco SummaryOrderCostLess anche se gia' vuoto
   						*/
   						$SummaryOrderCostLess->delete_to_order($this->user, $this->order_id, $debug);
   						 
   						/*
   						 * aggiorno SummaryOrderCostLess
   						* 		importo_cost_less = 0 (dettaglio per ogni utente)
   						* 		importo = 0 (somma acquisti Cart + summaryOrder)
   						*/
   						$SummaryOrderCostLess->populate_to_order($this->user, $this->order_id, $debug);
   						 
   						/*
   						 * aggiorno Order
   						*/
   						$sql ="UPDATE `".Configure::read('DB.prefix')."orders`
				   			   SET
									cost_less = ".$this->importoToDatabase($cost_less).",
									cost_less_type = null,
	   								modified = '".date('Y-m-d H:i:s')."'
				 			  WHERE
			   						organization_id = ".(int)$this->user->organization['Organization']['id']."
			   						and id = ".$this->order_id;
   						if($debug) echo '<br />'.$sql;
   						$this->Cart->query($sql);
   						 
   						/*
   						 * tolgo in SummaryOrder l'importo del cost_less perche' e' la somma di sum(Cart.importo) + SummaryOrderCostLess.importo_cost_less
   						*/
   						$SummaryOrder->delete_cost_less($this->user, $this->order_id, $debug);
   						 
   						$this->Session->setFlash(__('Insert CostLess'));
   						break;
   					case 'submitImportoDelete': /* elimina importo del cost_less */
   						
   						/*
   						 * SummaryOrder.importo sottraggo SummaryOrderCostLess.importo_cost_less
   						 * 		se non avevo ancora aggiunto il cost_less in SummaryOrder, SummaryOrderCostLess.importo_cost_less sara' = 0
   						 * dopo con submitElabora a SummaryOrder.importo aggiungero' SummaryOrderCostLess.importo_cost_less
   						 */
   						if(!empty($resultsSummaryOrder)) {
   							$SummaryOrder->delete_cost_less($this->user, $this->order_id, $debug);
   
							/*
							 * ricreo SummaryOrder perche' possono esserci state modifiche
							 */
							if($orderResults['Order']['typeGest']!='AGGREGATE') {
								$SummaryOrder->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrderCostLess->delete_to_order($this->user, $this->order_id, $debug);
								$SummaryOrder->populate_to_order($this->user, $this->order_id, $debug);	
							}	   							
   
   						}
   						
   						/*
   						 * ripulisco SummaryOrderCostLess anche se gia' vuoto
   						*/
   						$SummaryOrderCostLess->delete_to_order($this->user, $this->order_id, $debug);
   
   						/*
   						 * ripulisco Order
   						*/
   						$SummaryOrderCostLess->delete_cost_less_to_order($this->user, $this->order_id, $debug);
   
   						$this->Session->setFlash(__('Delete CostLess'));
   						break;
   					case 'submitElabora': /* salva per ogni utente la % di cost_less e aggiorna SummaryOrder */
   
   						/*
   						 * popolo SummaryOrderCostLess
   						 * 		ho SummaryOrderCostLess.importo_cost_less = 0 (dettaglio di ogni utente) => dopo lo popolo con i campi del form
   						 */
   						$resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($this->user, $this->order_id, $debug);
   						if(empty($resultsSummaryOrderCostLess))
   							$SummaryOrderCostLess->populate_to_order($this->user, $this->order_id, $debug);
   
   						/*
   						 * aggiorno Order
   						*/
   						$cost_less_type_db = null;
   						switch ($this->request['data']['cost-less-options']) {
   							case "options-qta":
   								$cost_less_type_db = 'QTA';
   								break;
   							case "options-weight":
   								$cost_less_type_db = 'WEIGHT';
   								break;
   							case "options-users":
   								$cost_less_type_db = 'USERS';
   								break;
   						}
   
   						$sql ="UPDATE
	   								`".Configure::read('DB.prefix')."orders`
	   							SET
	   								cost_less = ".$this->importoToDatabase($cost_less).",
   	   								cost_less_type = '$cost_less_type_db',
   	   								modified = '".date('Y-m-d H:i:s')."'
	   							WHERE
	   								organization_id = ".(int)$this->user->organization['Organization']['id']."
   	   								and id = ".$this->order_id;
   	   								if($debug) echo '<br />'.$sql;
   	   								$this->Cart->query($sql);
   
   	   					/*
   	   					 * valorizzo SummaryOrderCostLess.importo_cost_less (dettaglio di ogni utente)
   	   					 * aggiorno SummaryOrder.importo = SummaryOrder.importo - SummaryOrderCostLess.importo_cost_less-OLD + SummaryOrderCostLess.importo_cost_less-NEW
   	   					 */
   	   					foreach($this->request['data']['Data'] as $key => $value) {
   	   						$user_id = $key;
   	   						$importo_cost_less = $this->importoToDatabase($value);
   	   									 
   	   						/*
   	   						 * aggiorno SummaryOrder togliendo l'eventuale vecchio importo e aggiungendo il nuovo
   	   						 */
   	   						if(!empty($resultsSummaryOrder)) {
   	   										 
   	   							$resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($this->user, $this->order_id, $debug);
   	   							foreach($resultsSummaryOrderCostLess as $numSummaryOrderCostLess => $summaryOrderCostLess) {
   	   												
   	   								if($summaryOrderCostLess['SummaryOrderCostLess']['user_id'] == $user_id) {
   	   												 
   	   									$importo_cost_less_old = $summaryOrderCostLess['SummaryOrderCostLess']['importo_cost_less'];
   	   												 
   	   									$sql = "UPDATE ".Configure::read('DB.prefix')."summary_orders
   	   											SET
   	   												importo = (importo + $importo_cost_less - $importo_cost_less_old)
   	   											WHERE
   	   												organization_id = ".(int)$this->user->organization['Organization']['id']."
   													AND user_id = ".(int)$user_id."
   													AND order_id = ".(int)$this->order_id;
   	   									if($debug) echo '<br />'.$sql;
   	   									$result = $this->Cart->query($sql);
   	   												 
   	   									unset($summaryOrderCostLess[$numSummaryOrderCostLess]);
   	   								}
   	   							}
   	   						} // end if(!empty($resultsSummaryOrder))
   	   											
   	   						/*
   	   						 * aggiorno SummaryOrderCostLess.importo_cost_less
   							*/
   	   						$sql = "UPDATE
   	   									".Configure::read('DB.prefix')."summary_order_cost_lesses
   	   								SET
   	   									importo_cost_less = '$importo_cost_less',
   										modified = '".date('Y-m-d H:i:s')."'
      								WHERE
      									organization_id = ".(int)$this->user->organization['Organization']['id']."
   										AND order_id = ".(int)$this->order_id."
      									AND user_id = ".(int)$user_id;
      						if($debug) echo '<br />'.$sql;
      						$result = $SummaryOrderCostLess->query($sql);
  
      						$this->Session->setFlash(__('CostMore has been saved'));
   						}
   				break;
   
  	 		} // end swicth
   
   		}catch (Exception $e) {
   			CakeLog::write('error',$sql);
   			CakeLog::write('error',$e);
   			if($debug) echo '<br />'.$e;
   		}
   	}  // end if(isset($this->request['data']['SummaryOrderCostLess']['actionSubmitImporto']) && ...
   } // if ($this->request->is('post') || $this->request->is('put'))
      												 
	$options =array();
    $options['conditions'] = array ('Delivery.stato_elaborazione' => 'OPEN');
    $this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
      												 
 	/*
    * legenda profilata
    */
    $group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
    $orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
    $this->set('orderStatesToLegenda', $orderStatesToLegenda);
  }    
}