<?php
App::uses('AppController', 'Controller');

class SummaryOrdersController extends AppController {

	private $order_state_exclude;
	
	public function beforeFilter() {
		// $this->ctrlHttpReferer();
		
		$this->order_state_exclude = " AND (`Order`.state_code != 'CREATE-INCOMPLETE' and 
											`Order`.state_code != 'OPEN' and 
											`Order`.state_code != 'OPEN-NEXT' and 
											`Order`.state_code != 'RI-OPEN-VALIDATE' and 
											`Order`.state_code != 'TO-PAYMENT' and 
											`Order`.state_code != 'CLOSE') ";
		
		parent::beforeFilter();		
	}
	
	/*
	 * estraggo tutte le consegne con ordini con dati aggregati (SummaryOrders)
	 */
	public function admin_orders_validate() {
	
		$debug = false;
		$deliveries = array();
		
		$sql = "SELECT Delivery.id, Delivery.data, Delivery.luogo, Delivery.sys   
				FROM 
					".Configure::read('DB.prefix')."deliveries as Delivery, 
					".Configure::read('DB.prefix')."orders as `Order`, 
					".Configure::read('DB.prefix')."summary_orders as SummaryOrder 
				WHERE
					Delivery.organization_id = ".(int)$this->user->organization['Organization']['id']."
					AND `Order`.organization_id = ".(int)$this->user->organization['Organization']['id']." 
					AND SummaryOrder.organization_id = ".(int)$this->user->organization['Organization']['id']." 
					AND Delivery.isVisibleFrontEnd = 'Y'
					AND Delivery.stato_elaborazione = 'OPEN'
					AND `Order`.isVisibleFrontEnd = 'Y' 
					AND Delivery.id = `Order`.delivery_id
					AND SummaryOrder.delivery_id = Delivery.id
					AND SummaryOrder.order_id = `Order`.id 
					 ".$this->order_state_exclude." ";
			if(!$this->isSuperReferente()) 
				$sql .= " AND `Order`.supplier_organization_id IN (".$this->user->get('ACLsuppliersIdsOrganization').") ";
		
			$sql .= " GROUP BY Delivery.id 
					 ORDER BY Delivery.data ASC";
		 	if($debug) echo '<br />'.$sql;
		try {
			$results = $this->SummaryOrder->query($sql);
			
			if(!empty($results)) {
				foreach($results as $result) {
					if($result['Delivery']['sys']=='N') {
						$DeliveryData = date('d',strtotime($result['Delivery']['data'])).'/'.date('n',strtotime($result['Delivery']['data'])).'/'.date('Y',strtotime($result['Delivery']['data']));
						$deliveries[$result['Delivery']['id']] = $DeliveryData.' - '.$result['Delivery']['luogo'];
					}
					else
						$deliveries[$result['Delivery']['id']] = Configure::read('DeliveryToDefinedLabel');
				}			
			}
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
		
		$this->set(compact('deliveries'));
	}

	/*
	 * estraggo tutti gli ordini con dati aggregati (SummaryOrders)
	 */	
	public function admin_ajax_orders_list_validate($delivery_id) {

   		$debug = false;
   		$orders = array();
   		
	   	if(empty($this->delivery_id)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}

		$sql = "SELECT `Order`.data_inizio, `Order`.data_fine, `Order`.id, 
					   SuppliersOrganization.name    
				FROM 
					".Configure::read('DB.prefix')."orders as `Order`, 
					".Configure::read('DB.prefix')."suppliers_organizations as SuppliersOrganization, 
					".Configure::read('DB.prefix')."summary_orders as SummaryOrder 
				WHERE
					`Order`.organization_id = ".(int)$this->user->organization['Organization']['id']." 
					AND SuppliersOrganization.organization_id = ".(int)$this->user->organization['Organization']['id']." 
					AND SummaryOrder.organization_id = ".(int)$this->user->organization['Organization']['id']." 
					AND `Order`.isVisibleFrontEnd = 'Y' 
					AND SuppliersOrganization.id = `Order`.supplier_organization_id
					AND SummaryOrder.delivery_id = Order.delivery_id
					AND SummaryOrder.order_id = `Order`.id 
					AND Order.delivery_id = $delivery_id 
					 ".$this->order_state_exclude." ";
			if(!$this->isSuperReferente()) 
				$sql .= " AND `Order`.supplier_organization_id IN (".$this->user->get('ACLsuppliersIdsOrganization').") ";			
			$sql .= " GROUP BY `Order`.id 
					 ORDER BY `Order`.data_inizio ASC, `Order`.data_fine ASC";
		 	if($debug) echo '<br />'.$sql;  	
		try {
			$results = $this->SummaryOrder->query($sql);
			
			if(!empty($results)) {
				foreach($results as $result) {
					$OrderDataInizio = date('d',strtotime($result['Order']['data_inizio'])).'/'.date('n',strtotime($result['Order']['data_inizio'])).'/'.date('Y',strtotime($result['Order']['data_inizio']));
					$OrderDataFine = date('d',strtotime($result['Order']['data_fine'])).'/'.date('n',strtotime($result['Order']['data_fine'])).'/'.date('Y',strtotime($result['Order']['data_fine']));
					$orders[$result['Order']['id']] = $result['SuppliersOrganization']['name'].' - da '.$OrderDataInizio.' a '.$OrderDataFine;
				}			
			}
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
			   	
		$this->set(compact('orders'));
		
		$this->layout = 'ajax';	
	}
	
	public function admin_ajax_summary_orders_list_validate($delivery_id, $order_id) {
		
   		$debug = false;
   	
	   	if(empty($order_id) || empty($delivery_id)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}

		App::import('Model', 'Order');
	   	$Order = new Order;
	   	
	   	if(!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $order_id)) {
	   		$this->Session->setFlash(__('msg_not_permission'));
	   		$this->myRedirect(Configure::read('routes_msg_stop'));
	   	}
	   			
		$debug = false;
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$conditions = array('Order.id' => $order_id,
							'ArticlesOrder.order_id' => $order_id);
		$orderBy = array('User' => Configure::read('orderUser').', Article.name, Article.id');
							
		$results = $ArticlesOrder->getArticoliAcquistatiDaUtenteInOrdine($this->user, $conditions, $orderBy);

		/*
		 * dati Order
		 */
		$options = array();
		$options['conditions'] = array('Order.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Order.id' => (int)$order_id);
		$options['recursive'] = -1;
		$orderResults = $Order->find('first', $options);

		$summaryOrdersResults = array();
		$summaryOrderTrasportResults = array();
		$summaryOrderCostMoreResults = array();
		$summaryOrderCostLessResults = array();
		
		if($orderResults['Order']['hasTrasport']=='Y') {
			App::import('Model', 'SummaryOrderTrasport');
			$SummaryOrderTrasport = new SummaryOrderTrasport;
				
			$summaryOrderTrasportResults = $SummaryOrderTrasport->select_to_order($this->user, $order_id);
		}
		if($orderResults['Order']['hasCostMore']=='Y') {
			App::import('Model', 'SummaryOrderCostMore');
			$SummaryOrderCostMore = new SummaryOrderCostMore;
				
			$summaryOrderCostMoreResults = $SummaryOrderCostMore->select_to_order($this->user, $order_id);
		}
		if($orderResults['Order']['hasCostLess']=='Y') {
			App::import('Model', 'SummaryOrderCostLess');
			$SummaryOrderCostLess = new SummaryOrderCostLess;
				
			$summaryOrderCostLessResults = $SummaryOrderCostLess->select_to_order($this->user, $order_id);
		}
			
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;

		$summaryOrdersResults = $SummaryOrder->select_to_order($this->user, $order_id);
		/*
		echo "<pre>";
		print_r($summaryOrdersResults);
		echo "</pre>";
		*/
		$this->set(compact('results', 'orderResults', 'summaryOrdersResults', 'summaryOrderTrasportResults', 'summaryOrderCostMoreResults', 'summaryOrderCostLessResults'));		
	
		$this->layout = 'ajax';	
	}
	
	/*
	 * $key = SummaryOrder_order_id-SummaryOrder_delivery_id-SummaryOrder_user_id
	 * 	ricalcola i dati aggregati (SummaryOrder) di uno user per un dato ordine 
	 */
	public function admin_ajax_summary_orders_ricalcola($key) {
  		
		$debug = false;  // se true scrive log
   	
		$this->ctrlHttpReferer();
	
	   	if(empty($key)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}	

		list($order_id, $delivery_id, $user_id) = explode('-', $key);
		
		$this->SummaryOrder->ricalcolaPerSingoloUtente($this->user, $order_id, $user_id, $debug);
						
		/*
		 * rileggo il nuovo importo aggregato
		 */
		$optinos = array();
		$options['conditions'] = array('SummaryOrder.organization_id' => $this->user->organization['Organization']['id'],
										'SummaryOrder.order_id' => $order_id,
										'SummaryOrder.delivery_id' => $delivery_id,
										'SummaryOrder.user_id' => $user_id);
		$options['recursive'] = -1;
		$options['fields'] = array('importo');
		$results = $this->SummaryOrder->find('first', $options);
		if($debug)  {
			if($debug) CakeLog::write('debug','NUOVI dai SummaryOrder');
			if($debug) CakeLog::write('debug', print_r($options, true));
			if($debug) CakeLog::write('debug', print_r($results, true));
		}
	
    	$this->set('content_for_layout', $results['SummaryOrder']['importo_']);
	
	    				
		$this->layout = 'ajax';	
		$this->render('/Layouts/ajax');			
	}
	
	/*
	 * da Referente Carts::managementCartsGroupByUsers  (1 solo ordine)
	 * 		Gestisci gli acquisti dell'ordine aggregati per utente
	 * da Tesoriere::ordersInProcessingSummaryOrders  (1 o + ordini)
	 * 		Gestione degli ordini in elaborazione
	*/
	public function admin_delete($delivery_id, $order_ids, $id = null) {

		if($id==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * delete
		 */
		$this->SummaryOrder->id = $id;
		if (!$this->SummaryOrder->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		if (!$this->SummaryOrder->delete())
			$this->Session->setFlash(__('Summary order was not deleted'));
		else
			$this->Session->setFlash(__('Delete Summary order'));
 
		$this->__populate_to_view($this->delivery_id,$order_ids);
		
		$this->layout = 'ajax';
		$this->render('/AjaxGasCodes/admin_box_summary_orders');
	}

	/*
	 * da Referente Carts::managementCartsGroupByUsers  (1 solo ordine)
	* 		Gestisci gli acquisti dell'ordine aggregati per utente
	* da Tesoriere::ordersInProcessingSummaryOrders  (1 o + ordini)
	* 		Gestione degli ordini in elaborazione
	* 
	*  $delivery_id, $order_ids per ripolorare la pagina con chiamata ajxa
	*  $order_id_to_add, $user_id, $importo campi per il db.SummaryOrder
	*/
	public function admin_add($delivery_id, $order_ids, $order_id_to_add, $user_id, $importo) {
	
		if($order_id_to_add==null || $user_id==null || $importo==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * ctrl se non esiste gia' un occorrenza in SummaryOrder
		 */
		$conditions = array('SummaryOrder.organization_id' => (int)$this->user->organization['Organization']['id'],
							'SummaryOrder.order_id' => $order_id_to_add,
							'SummaryOrder.user_id'=> $user_id);
		$ctrlResults = $this->SummaryOrder->find('first',array('conditions'=> $conditions,'recursive'=>0));
		if(!empty($ctrlResults))
			$this->Session->setFlash(__('The summary order just exist'));
		else {
			/*
			 * add
			*/
			$results['SummaryOrder']['organization_id'] = $this->user->organization['Organization']['id'];
			$results['SummaryOrder']['order_id'] = $order_id_to_add;
			$results['SummaryOrder']['user_id'] = $user_id;
			$results['SummaryOrder']['importo'] = $importo;
			$results['SummaryOrder']['importo_pagato'] = '0.00';
			$results['SummaryOrder']['modalita'] = 'DEFINED';
			
			$this->SummaryOrder->create();
			if ($this->SummaryOrder->save($results)) {
				$this->Session->setFlash(__('The summary order has been saved'));
			} else {
				$this->Session->setFlash(__('The summary order could not be saved. Please, try again.'));
			}
		}
				
		$this->__populate_to_view($this->delivery_id,$order_ids);
		
		// nascondo il div summary-orders-options perche' se no ho troppi box-message 
		$this->set('hide_summary_orders_options',true);
		
		$this->layout = 'ajax';
		$this->render('/AjaxGasCodes/admin_box_summary_orders');
	}
	
	public function admin_setImporto($row_id, $id, $importo=0) {
		if($row_id==null || $id==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$esito = false;
	
		$this->SummaryOrder->id = $id;
		if (!$this->SummaryOrder->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$data['SummaryOrder']['importo'] = $this->importoToDatabase($importo);
		if ($this->SummaryOrder->save($data))
			$esito = true;
		else
			$esito = false;
	
		if ($esito)
			$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'OKIMPORTO\','.$id.',null);</script>';
		else
			$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'NO\','.$id.',null);</script>';
			
		$this->set('content_for_layout',$content_for_layout);
	
		$this->layout = 'ajax';
		$this->render('/Layouts/ajax');
	}
	
	/*
	 *  stesso codice di Ajax::admin_box_summary_orders 
	 */
	private function __populate_to_view($delivery_id,$order_ids) {
		/*
		 * ricarico il div con l'elenco dei summaryOrders
		*/
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
												'Delivery.sys'=> 'N',
												'Delivery.id' => (int)$this->delivery_id),
							'Order' => array('Order.isVisibleBackOffice' => 'Y',
											 'Order.id IN ('.$order_ids.')'),
							'Cart' => array('Cart.deleteToReferent' => 'N'));
	
		$orderBy = array('User' => 'User.name');
	
		$options = array('orders' => false, 'storerooms' => false, 'summaryOrders' => true,
				'articlesOrdersInOrder' => true,
				'suppliers'=>true, 'referents'=>true);
	
		$results = $Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);
	
		$this->set('results', $results);
	
		App::import('Model', 'User');
		$User = new User;
	
		$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
		$users = $User->getUsersList($this->user, $conditions);
		$this->set('users',$users);
	}
}