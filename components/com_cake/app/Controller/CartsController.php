<?php
App::uses('AppController', 'Controller');

class CartsController extends AppController {

   public $components = ['RequestHandler', 'ActionsDesOrder'];
   
   public function beforeFilter() {
   		parent::beforeFilter();
 
   		$actionWithPermission = ['admin_managementCartsOne', 'admin_managementCartsGroupByUsers', 'admin_validationCarts', 'admin_trasport'];
   		if (in_array($this->action, $actionWithPermission)) {
	   		/*
	   		 * ctrl che la consegna sia visibile in backoffice
	   		*/
	   		if(!empty($this->delivery_id)) {
	   			
	   			App::import('Model', 'Delivery');
	   			$Delivery = new Delivery;
	   			$results = $Delivery->read($this->delivery_id, $this->user->organization['Organization']['id']);
	   			if($results['Delivery']['isVisibleBackOffice']=='N') {
	   				$this->Session->setFlash(__('msg_delivery_not_visible_backoffice'));
	   				$this->myRedirect(Configure::read('routes_msg_stop'));
	   			}  			
	   		}
	
	   		/*
	   		 * ctrl che l'ordine sia visibile in backoffice
	   		*/
	   		if(!empty($this->order_id)) {
	   			
	   			App::import('Model', 'Order');
	   			$Order = new Order;
	   			$results = $Order->read($this->order_id, $this->user->organization['Organization']['id']);
	   			if($results['Order']['isVisibleBackOffice']=='N') {
	   				$this->Session->setFlash(__('msg_order_not_visible_backoffice'));
	   				$this->myRedirect(Configure::read('routes_msg_stop'));
	   			}   			
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

	   	$conditions = ['Cart.user_id' => (int)$user_id];
	   	 
	   	// in AjaxGasCart setto con = valore created e modified
	   	$orderBy = ['CartPreview' => 'Cart.date DESC'];
	   	
	   	$results = $ArticlesOrder->getArticoliDellUtenteInOrdine($this->user, $conditions, $orderBy, Configure::read('CartLimitPreview'));
	   	
	   	$this->set('results', $results);
	   
	   	$this->layout = 'ajax';
   }
   
   /*
    * se arrivo da Orders/admin_index.ctp $delivery_id, $order_id sono valorizzati
    * */
   public function admin_managementCartsOne() {
   	
   		$debug = false;
   	
	   	if(empty($this->order_id) || empty($this->delivery_id)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	   	
        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);		
		$des_order_id = $desResults['des_order_id'];
		$this->set('des_order_id',$des_order_id);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		/*
		 * ctrl ACL, pagina visibile solo dal titolare
		 */				
		if(!empty($des_order_id) && !$this->ActionsDesOrder->isAclReferente($desResults['DesOrder']['state_code'])) { 
			$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
			$this->myRedirect(['controller' => 'Orders', 'action' => 'home', $this->order_id]);
		}
				
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

	   $options =[];
	   $options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
	   $this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	   
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
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);		
		$des_order_id = $desResults['des_order_id'];
		$this->set('des_order_id',$des_order_id);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		/*
		 * ctrl ACL, pagina visibile solo dal titolare
		 */				
		if(!empty($des_order_id) && !$this->ActionsDesOrder->isAclReferente($desResults['DesOrder']['state_code'])) { 
			$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
			$this->myRedirect(['controller' => 'Orders', 'action' => 'home', $this->order_id]);
		}
			   	 
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
		   
	   	$options = [];
		$options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
		$this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
   
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
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);		
		$des_order_id = $desResults['des_order_id'];
		$this->set('des_order_id',$des_order_id);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		/*
		 * ctrl ACL, pagina visibile solo dal titolare
		 */				
		if(!empty($des_order_id) && !$this->ActionsDesOrder->isAclReferente($desResults['DesOrder']['state_code'])) { 
			$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
			$this->myRedirect(['controller' => 'Orders', 'action' => 'home', $this->order_id]);
		}
			   	 
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
	
	   		$options = [];
	   		$options['fields'] = ['SUM(CartsSplit.importo_forzato) AS totImportoForzato', 'CartsSplit.organization_id', 'CartsSplit.order_id', 'CartsSplit.article_id', 'CartsSplit.article_organization_id', 'CartsSplit.user_id'];
	   		$options['conditions'] = ['CartsSplit.organization_id' => $this->user->organization['Organization']['id'],
	   										'CartsSplit.order_id' => $this->order_id];
	   		$options['group'] = ['CartsSplit.organization_id', 'CartsSplit.order_id', 'CartsSplit.article_id', 'CartsSplit.article_organization_id', 'CartsSplit.user_id'];
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
	   	
	   	$options = [];
		$options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
		$this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
   
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
   }
    
   /*
    * se arrivo da Orders/admin_index.ctp $delivery_id, $order_id sono valorizzati
	* gestione colli
    */
   public function admin_validationCarts() {

   		$debug = false;
   	
   		if(empty($this->order_id) || empty($this->delivery_id)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}

        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);		
		$des_order_id = $desResults['des_order_id'];
		$this->set('des_order_id',$des_order_id);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		/*
		 * ctrl ACL, pagina visibile solo dal titolare
		 */				
		if(!empty($des_order_id) && !$this->ActionsDesOrder->isAclReferente($desResults['DesOrder']['state_code'])) { 
			$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
			$this->myRedirect(['controller' => 'Orders', 'action' => 'home', $this->order_id]);
		}
		
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
	   	
	   	$options = [];
	   	$options['conditions'] = ['ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
	   									'ArticlesOrder.order_id' => $this->order_id];
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

   	   		self::d($this->request->data, $debug); 
   	   		
   	   		if($this->user->organization['Organization']['hasStoreroom']=='Y') {
   	   			/*
   	   			 * ctrl se storeroom esiste
   	   			* */
   	   			App::import('Model', 'Storeroom');
   	   			$Storeroom = new Storeroom;
   	   			$storeroomUser = $Storeroom->getStoreroomUser($this->user);
   	   			if(empty($storeroomUser)) {
   	   				$this->Session->setFlash(__('StoreroomNotFound'));
   	   				$this->myRedirect(Configure::read('routes_msg_exclamation'));
   	   			}
 
	   	   		App::import('Model', 'Cart');
	   	   		$Cart = new Cart;
				
	   	   		App::import('Model', 'AjaxGasCart');
	   	   		$AjaxGasCart = new AjaxGasCart;
	
	   	   		$article_order_id_selected = $this->request->data['article_order_id_selected'];
	   	   		$arr_article_order_id_selected = explode(',',$article_order_id_selected);
	   	   		
	   	   		self::d($arr_article_order_id_selected, $debug); 
	   	   		
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
						$options = [];
						$options['conditions'] = ['Cart.organization_id' => $this->user->organization['Organization']['id'],
													'Cart.order_id' => $order_id,
													'Cart.article_organization_id' => $article_organization_id,
													'Cart.article_id' => $article_id,
													'Cart.user_id' => $user_id];
						$options['recursive'] = -1;
						$cartResults = $Cart->find('first', $options);
						
						self::d([$options, $cartResults], $debug);
						
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
	   	   				
						self::d($resultsJS, $debug);
							   	   					   	   				
	   	   				/*
	   	   				 * gestione JavaScript
	   	   				* */
	   	   				$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId=0).'</script>';
	   	   				$this->set('resultsJS',$resultsJS);
	   	   				
	   	   			}  	   			
	   	   		}

		   		$this->Session->setFlash(__('The articles order has been saved to storeroom'));
				/*
				 * bugs, faccio un redirect su se stesso perche' se no ricarica la pg con i dati vecchi da AjaxGasCodes::box_validation_carts
				 */
				 $this->myRedirect(['controller' => 'Carts', 'action' => 'validationCarts']);
   	   		} // end if($this->user->organization['Organization']['hasStoreroom']=='Y') 
	   	}  // end if ($this->request->is('post') || $this->request->is('put')) 
	   	
	   	$options = [];
	    $options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
	    $this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	   		   	
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
   }

   /*
    * ArticlesOrder.key = $organization_id, $order_id, $article_organization_id, $article_id 
	* gestione colli
    */
   public function admin_validation_carts_edit($delivery_id=0, $order_id=0, $article_organization_id=0, $article_id=0) {

   		$debug = false;
   		
	   	if ($this->request->is('post') || $this->request->is('put')) {
	   		
			self::d($this->request->data, $debug); 
			
	   		App::import('Model', 'AjaxGasCart');
	   		$AjaxGasCart = new AjaxGasCart;
	   		
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
							
							/*
							 * gestione JavaScript
							* */
							$resultsJS = '<script type="text/javascript">'.sprintf($resultsJS,$rowId=0).'</script>';
							$this->set('resultsJS',$resultsJS);
							
							$tot_modificati++;	

							/*
							 * ricalcolo SummaryOrders se esiste, NON + utilizzato, function vuota
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
	   		
	   		if(!$debug) $this->myRedirect(['action' => 'validationCarts']);
	   	} // end if ($this->request->is('post') || $this->request->is('put')) 
	   	else {
	   		if(empty($delivery_id) || empty($order_id) || empty($article_organization_id) || empty($article_id)) {
	   			$this->Session->setFlash(__('msg_error_params'));
	   			$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   		}
	   	}
	   			
		/*
		 * estraggo i dati dell'articolo
		 */
		$results = $this->Cart->getCartToValidate($this->user, $delivery_id, $order_id, $article_organization_id, $article_id);
		$this->set('articlesOrdesResults',current($results));
	 
		/*
		 * stessa chiamata Ajax::admin_view_articles_order_carts simile
		*/		
		App::import('Model', 'Cart');
		$Cart = new Cart();
			
		$options = [];
		$options['conditions'] = ['Cart.organization_id' => $this->user->organization['Organization']['id'],
				'Cart.order_id' => $order_id,
				'Cart.article_organization_id' => $article_organization_id,
				'Cart.article_id' => $article_id,
				'Cart.deleteToReferent' => 'N'];
		$options['recursive'] = 1;
		$options['order'] = [Configure::read('orderUser')];
		$results = $Cart->find('all', $options);
		$this->set('results', $results);
		
	   	/*
	   	 * tutti i gruppi escluso gli users che hanno gia' effettuato acquisti
	   	*/
	   	App::import('Model', 'User');
	   	$User = new User;
	   	
		$users = [];
		if(!empty($results)) {
			$user_ids_da_escludere = '';
			foreach($results as $result) 
				$user_ids_da_escludere .= $result['Cart']['user_id'].',';
			if(!empty($user_ids_da_escludere)) {
				$user_ids_da_escludere = substr($user_ids_da_escludere, 0, (strlen($user_ids_da_escludere)-1));
					
				$conditions = [];
				$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user'),
							   ['NOT' => ['UserGroupMap.user_id' => $user_ids_da_escludere]]];
				$users = $User->getUsersList($this->user, $conditions);
			}
		}
		$this->set('users',$users);	   	
   }
	
   public function admin_view($id = null) {
	   	$this->Cart->id = $id;
	   	if (!$this->Cart->exists($this->user->organization['Organization']['id'])) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	   	$this->set('cart', $this->Cart->read($id, $this->user->organization['Organization']['id']));
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
		
   	if(empty($this->order_id) || empty($this->delivery_id)) {
   		$this->Session->setFlash(__('msg_error_params'));
   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
   	}
	 
        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);		
		$des_order_id = $desResults['des_order_id'];
		$this->set('des_order_id',$des_order_id);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		/*
		 * ctrl ACL, pagina visibile solo dal titolare
		 */				
		if(!empty($des_order_id) && !$this->ActionsDesOrder->isAclReferente($desResults['DesOrder']['state_code'])) { 
			$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
			$this->myRedirect(['controller' => 'Orders', 'action' => 'home', $this->order_id]);
		}
			   	
   	/*
   	 * ctrl configurazione Organization
   	*/
   	if($this->user->organization['Organization']['hasTrasport']=='N') {
   		$this->Session->setFlash(__('msg_not_organization_config'));
   		$this->myRedirect(Configure::read('routes_msg_stop'));
   	}
   	 
   	App::import('Model', 'Order');
   	$Order = new Order;

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
								  'Order.id' => $this->order_id];
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
				
  	 			App::import('Model', 'SummaryOrderPlu');
  	 			$SummaryOrderPlu = new SummaryOrderPlu;
  	 			
				$this->Session->setFlash($SummaryOrderPlu->mySave($this->user, 'SummaryOrderTrasport', $this->request, $debug));
					   			
	   		}  // end if(isset($this->request['data']['SummaryOrderTrasport']['actionSubmitImporto']) && ...	   
	    } // if ($this->request->is('post') || $this->request->is('put')) 
	    
	    $options =[];
	    $options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
	    $this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	    
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
	 * D.E.S.
	 */
	$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);		
	$des_order_id = $desResults['des_order_id'];
	$this->set('des_order_id',$des_order_id);
	$this->set('desOrdersResults', $desResults['desOrdersResults']);
	$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
	/*
	 * ctrl ACL, pagina visibile solo dal titolare
	 */				
	if(!empty($des_order_id) && !$this->ActionsDesOrder->isAclReferente($desResults['DesOrder']['state_code'])) { 
		$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
		$this->myRedirect(['controller' => 'Orders', 'action' => 'home', $this->order_id]);
	}
			
   	App::import('Model', 'Order');
   	$Order = new Order;
   
	$options = [];
	$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
							  'Order.id' => $this->order_id];
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
   
			App::import('Model', 'SummaryOrderPlu');
			$SummaryOrderPlu = new SummaryOrderPlu;
			
			$this->Session->setFlash($SummaryOrderPlu->mySave($this->user, 'SummaryOrderCostMore', $this->request, $debug));
				
   		}  // end if(isset($this->request['data']['SummaryOrderCostMore']['actionSubmitImporto']) && ...
   	} // if ($this->request->is('post') || $this->request->is('put'))
   	 
   	$options =[];
   	$options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
   	$this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
   	 
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
	 * D.E.S.
	 */
	$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);		
	$des_order_id = $desResults['des_order_id'];
	$this->set('des_order_id',$des_order_id);
	$this->set('desOrdersResults', $desResults['desOrdersResults']);
	$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
	/*
	 * ctrl ACL, pagina visibile solo dal titolare
	 */				
	if(!empty($des_order_id) && !$this->ActionsDesOrder->isAclReferente($desResults['DesOrder']['state_code'])) { 
		$this->Session->setFlash(__('msg_not_des_order_state_no_modify_order'));
		$this->myRedirect(['controller' => 'Orders', 'action' => 'home', $this->order_id]);
	}
	
	App::import('Model', 'Order');
	$Order = new Order;
	   
	$options = [];
	$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
							  'Order.id' => $this->order_id];
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
   			 
			App::import('Model', 'SummaryOrderPlu');
			$SummaryOrderPlu = new SummaryOrderPlu;
			
			$this->Session->setFlash($SummaryOrderPlu->mySave($this->user, 'SummaryOrderCostLess', $this->request, $debug));
			
   	}  // end if(isset($this->request['data']['SummaryOrderCostLess']['actionSubmitImporto']) && ...
   } // if ($this->request->is('post') || $this->request->is('put'))
      												 
	$options =[];
    $options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
    $this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
      												 
 	/*
    * legenda profilata
    */
    $group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
    $orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
    $this->set('orderStatesToLegenda', $orderStatesToLegenda);
  }    
}