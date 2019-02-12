<?php
App::uses('AppController', 'Controller');

class SummaryOrderAggregatesController extends AppController {

   public $components = array('RequestHandler', 'ActionsDesOrder');
   
   public function beforeFilter() {
   		parent::beforeFilter();
 
   		$actionWithPermission = array('admin_managementCartsGroupByUsers');   		if (in_array($this->action, $actionWithPermission)) {
	   		/*	   		 * ctrl che la consegna sia visibile in backoffice	   		*/	   		if(!empty($this->delivery_id)) {
	   				   			App::import('Model', 'Delivery');	   			$Delivery = new Delivery;	   			$results = $Delivery->read($this->user->organization['Organization']['id'], null, $this->delivery_id);
	   			if($results['Delivery']['isVisibleBackOffice']=='N') {	   				$this->Session->setFlash(__('msg_delivery_not_visible_backoffice'));	   				$this->myRedirect(Configure::read('routes_msg_stop'));	   			}  				   		}
	
	   		/*	   		 * ctrl che l'ordine sia visibile in backoffice	   		*/
	   		if(!empty($this->order_id)) {
	   				   			App::import('Model', 'Order');	   			$Order = new Order;	   			$results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);	   			if($results['Order']['isVisibleBackOffice']=='N') {	   				$this->Session->setFlash(__('msg_order_not_visible_backoffice'));	   				$this->myRedirect(Configure::read('routes_msg_stop'));	   			}   			
	   		}
   		} // end if (in_array($this->action, $actionWithPermission)) 
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
			$this->myRedirect(array('controller' => 'Orders', 'action' => 'home', $this->order_id));
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
		$options['conditions'] = array('Delivery.stato_elaborazione' => 'OPEN');
		$this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
   
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
		
		$this->ctrlModuleConflicts($this->user, $this->order_id, 'managementCartsGroupByUsers', $debug);
   }   

    /*
     * solo se $Order->getOrderPermissionToEditReferente() posso procedere
     */
    public function admin_box_summary_order_aggregates_options() {

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
        if ($Order->getOrderPermissionToEditReferente($results['Order'])) {
            /*
             * ctrl eventuali occorrenze di SummaryOrderAggregate
             */
            App::import('Model', 'SummaryOrderAggregate');
            $SummaryOrderAggregate = new SummaryOrderAggregate;
            $summaryOrderAggregateResults = $SummaryOrderAggregate->select_to_order($this->user, $this->order_id);
            $this->set(compact('summaryOrderAggregateResults', $summaryOrderAggregateResults));
        }

        $this->set(compact('results', $results));

        $this->layout = 'ajax';
    }
	
    /*
     * richiamata da
     * Carts::managementCartsGroupByUsers dove gli passo un solo order_id e $summaryOrderAggregatesOptions='options-delete-...'
     */
    public function admin_box_summary_order_aggregates($order_id_selected, $summaryOrderAggregatesOptions = 'options-delete-no') {

		$debug = false;
		
        if (empty($this->delivery_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * Tesoriere::admin_orders_in_processing_summary_orders posso avere + order_id
         */
        $array_order_id = explode(',', $order_id_selected);
        
        App::import('Model', 'Cart');
        $Cart = new Cart;

        /*
         * cancello occorrenze di SummaryOrderAggregates, se il referente vuole rigenerarle
         */
        if ($summaryOrderAggregatesOptions == 'options-delete-yes') {
            $this->SummaryOrderAggregate->delete_to_order($this->user, $array_order_id[0]);

            $this->SummaryOrderAggregate->populate_to_order($this->user, $array_order_id[0], 0);

            $this->set('summary_orders_regenerated', true);
        } else {
            /*
             * ctrl eventuali occorrenze di SummaryOrder, se non ci sono lo popolo
             */
            foreach ($array_order_id as $order_id) {
                $results = $this->SummaryOrderAggregate->select_to_order($this->user, $order_id);
                if (empty($results))
                    $this->SummaryOrderAggregate->populate_to_order($this->user, $order_id, 0);
            }
        }


        /*
         *  stesso codice di SummaryOrders::__populate_to_view
         *  
         * ricarico il div con l'elenco dei summaryOrders
         */
        App::import('Model', 'Delivery');
        $Delivery = new Delivery;
        
        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $this->delivery_id),
				'Order' => array('Order.isVisibleBackOffice' => 'Y',
                'Order.id IN (' . $order_id_selected . ')'),
				'Cart' => array('Cart.deleteToReferent' => 'N'));

        $orderBy = array('User' => 'User.name');

        $options = array('orders' => false, 'storerooms' => false, 'summaryOrders' => false, 'summaryOrderAggregates' => true,
            'articlesOrdersInOrder' => true,
            'suppliers' => true, 'referents' => true);

        $results = $Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);
       
        /*
         * per ogni user estraggo il totale degli acquisti originale
         * ctrl se ha gia' saldato al cassiere / tesoriere SummaryOrder.saldato_a
         */
        foreach($results as $numResult => $result) {
			if($results['Delivery']['totOrders'] > 0) {
				foreach($results['Delivery'][0]['Order'] as $numOrder => $order) {
					if(isset($order['SummaryOrderAggregate']))
					foreach($order['SummaryOrderAggregate'] as $numResult2 => $summaryOrderAggregate) {
						
						$conditions = []; 
						$conditions['Cart.user_id'] = $summaryOrderAggregate['User']['id'];
						$conditions['Order.id'] = $summaryOrderAggregate['SummaryOrderAggregate']['order_id'];
						$totImporto = $Cart->getTotImporto($this->user, $conditions, $debug);
						
						$summaryOrderResults = $SummaryOrder->select_to_order($this->user, $summaryOrderAggregate['SummaryOrderAggregate']['order_id'], $summaryOrderAggregate['User']['id']);
						if(!empty($summaryOrderResults) && $summaryOrderResults['SummaryOrder']['saldato_a']!=null)
							$results['Delivery'][0]['Order'][$numOrder]['SummaryOrderAggregate'][$numResult2]['SummaryOrder']['saldato_a'] = $summaryOrderResults['SummaryOrder']['saldato_a'];
						else
							$results['Delivery'][0]['Order'][$numOrder]['SummaryOrderAggregate'][$numResult2]['SummaryOrder']['saldato_a'] = null;
						
						$results['Delivery'][0]['Order'][$numOrder]['SummaryOrderAggregate'][$numResult2]['User']['totImporto'] = $totImporto;
						$results['Delivery'][0]['Order'][$numOrder]['SummaryOrderAggregate'][$numResult2]['User']['totImporto_e'] = number_format($totImporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			
						self::d($summaryOrderAggregate,false);
				
					} // end foreach 						
				} // end foreach 
			}					        
        } // end foreach 
   
        $this->set('results', $results);

        App::import('Model', 'User');
        $User = new User;

        $conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
        $users = $User->getUsersList($this->user, $conditions);
        $this->set('users', $users);

        $this->layout = 'ajax';
    }

	public function admin_setImporto($row_id, $id, $importo=0) {
		if($row_id==null || $id==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$esito = false;
	
		$this->SummaryOrderAggregate->id = $id;
		if (!$this->SummaryOrderAggregate->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$data['SummaryOrderAggregate']['importo'] = $this->importoToDatabase($importo);
		if ($this->SummaryOrderAggregate->save($data))
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
	
	public function admin_delete($order_ids, $id = null) {

		if($id==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * delete
		 */
		$this->SummaryOrderAggregate->id = $id;
		if (!$this->SummaryOrderAggregate->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		if (!$this->SummaryOrderAggregate->delete())
			$this->Session->setFlash(__('Summary order was not deleted'));
		else
			$this->Session->setFlash(__('Delete Summary order'));
 
		$this->_populate_to_view($order_ids);
		
		$this->layout = 'ajax';
		$this->render('/SummaryOrderAggregates/admin_box_summary_order_aggregates');
	}
	
	public function admin_add($order_ids, $order_id_to_add, $user_id, $importo) {
	
		if($order_id_to_add==null || $user_id==null || $importo==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * ctrl se non esiste gia' un occorrenza in SummaryOrderAggregate
		 */
		$options = [];
		$options['conditions'] = ['SummaryOrderAggregate.organization_id' => (int)$this->user->organization['Organization']['id'],
									'SummaryOrderAggregate.order_id' => $order_id_to_add,
									'SummaryOrderAggregate.user_id'=> $user_id];
        $options['recursive'] = 0;									
		$ctrlResults = $this->SummaryOrderAggregate->find('first', $options);
		if(!empty($ctrlResults))
			$this->Session->setFlash(__('The summary order just exist'));
		else {
			/*
			 * add
			*/
			$data = [];
			$data['SummaryOrderAggregate']['organization_id'] = $this->user->organization['Organization']['id'];
			$data['SummaryOrderAggregate']['order_id'] = $order_id_to_add;
			$data['SummaryOrderAggregate']['user_id'] = $user_id;
			$data['SummaryOrderAggregate']['importo'] = $importo;
			
			$this->SummaryOrderAggregate->create();
			if ($this->SummaryOrderAggregate->save($data)) {
				$this->Session->setFlash(__('The summary order has been saved'));
			} else {
				$this->Session->setFlash(__('The summary order could not be saved. Please, try again.'));
			}
		}
				
		$this->_populate_to_view($order_ids);
		
		// nascondo il div summary-orders-options perche' se no ho troppi box-message 
		$this->set('hide_summary_orders_options',true);
		
		$this->layout = 'ajax';
		$this->render('/SummaryOrderAggregates/admin_box_summary_order_aggregates');
	}
	
	/*
	 *  stesso codice di Ajax::admin_box_summary_orders 
	 */
	private function _populate_to_view($order_ids) {
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
	
		$options = array('orders' => false, 'storerooms' => false, 'summaryOrders' => false, 'summaryOrderAggregates' => true,
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