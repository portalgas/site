<?php
App::uses('AppController', 'Controller');

/*
 * options = array(
* 				  'orders'=>true, 		 ORDINI
* 				  'storerooms' => true, DISPENSA
* 				  'summaryOrders' => true, per Tesoriere
*
* 				  'articoliEventualiAcquistiNoFilterInOrdine'=>true   estraggo tutti gli articoli acquistati in base all'ordine ed EVENTUALI Cart di un utente
* 				  'articlesOrdersInOrder'=>true              estraggo tutti gli articoli in base all'ordine
* 				  'articoliDellUtenteInOrdine'=>true,     estraggo SOLO gli articoli acquistati da un utente in base all'ordine
* 
* 				  'suppliers'=>true, 'referents'=>true);
 */

class DeliveriesController extends AppController {

    public $components = ['RequestHandler', 'Users'];
    public $helpers = ['Html', 'Javascript', 'Ajax', 'Tabs', 'RowEcomm']; 

    public function beforeFilter() {
        parent::beforeFilter();

        /* ctrl ACL */
        if (in_array($this->action, ['admin_index', 'admin_index_history', 'admin_edit', 'admin_add', 'admin_delete'])) {
            if (!$this->isManager() && !$this->isManagerDelivery()) {
                $this->Session->setFlash(__('msg_not_permission'));
                $this->myRedirect(Configure::read('routes_msg_stop'));
            }
        }
        
		if($this->user->organization['Organization']['type']!='GAS') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	        
        /* ctrl ACL */

		if(!$this->Users->hasUserFlagPrivacy($this->user, $this->action)) {
			$this->Session->setFlash(__('msg_user_flag_privacy'));
			$this->myRedirect(Configure::read('routes_msg_user_flag_privacy'));			
		}
		
		self::d("------- _COOKIE --------", $debug);
		self::d($_COOKIE, $debug);
		self::d("------- _COOKIE --------", $debug);
		$this->set('hasUserRegistrationExpire', 'Y');
		if(!$this->Users->hasUserRegistrationExpire($this->user, $this->action)) {
			// $this->Session->setFlash(__('msg_user_registration_expire'));
			// $this->myRedirect(Configure::read('routes_msg_user_registration_expire'));
			
			if(!isset($_COOKIE[Configure::read('Cookies.user.registration.expire')]) || empty($_COOKIE[Configure::read('Cookies.user.registration.expire')]))
				$this->set('hasUserRegistrationExpire', 'N');
		}
		
	
        /*
         * ctrl configurazione Organization
         */
        if ($this->user->organization['Organization']['hasVisibility'] == 'Y')
            $this->set('organizationHasVisibility', 'Y');
        else
            $this->set('organizationHasVisibility', 'N');
    }

    public function tabs() {

        /*
         * setto organization_id preso dal template
         */
        $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $this->user->get('org_id')]);

        $conditions = ['Delivery' => ['Delivery.isVisibleFrontEnd' => 'Y',
									'Delivery.stato_elaborazione' => 'OPEN',
									'Delivery.sys' => 'N',
									'DATE(Delivery.data) >= CURDATE() - INTERVAL ' . Configure::read('GGinMenoPerEstrarreDeliveriesInTabs') . ' DAY']];

        $results = $this->Delivery->getTabsToDeliveriesData($tmp_user, $conditions['Delivery']);

        /*
         * ctrl se ci sono ordini con la consegna ancora da definire (Delivery.sys = Y)
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $ordersResults = $Order->getOrdersDeliverySys($tmp_user);

        if (count($ordersResults) > 0) {
            $tmpDeliveryData['Delivery']['data'] = Configure::read('DeliveryToDefinedDate');
            $results[count($results)] = $tmpDeliveryData;
        }

        $this->set(compact('results'));
        $this->layout = 'default_front_end';
    }

    /*
     * Consegne, cliccando sul Tab visualizzo gli ordini delle consegne
     */

    public function tabsAjaxDeliveries($deliveryData) {

        /*
         * setto organization_id preso dal template
         */
        $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $this->user->get('org_id')]);

        if (empty($deliveryData)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if (!empty($this->user->id) && $this->user->get('org_id') == $this->user->organization['Organization']['id'])
            $options = array('articlesOrders' => true, 'carts' => true);
        else
            $options = array('articlesOrders' => false, 'carts' => false);

        $options += array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
            'suppliers' => true, 'referents' => true);

        if (!empty($this->user->id) && $this->user->get('org_id') == $this->user->organization['Organization']['id'])
            $options += ['articoliEventualiAcquistiNoFilterInOrdine' => true];  // estraggo tutti gli articoli acquistati in base all'ordine ed EVENTUALI Cart di un utente
        else
            $options += ['articlesOrdersInOrder' => false];  // NON estraggo gli articoli dell'ordine

        $conditions = array('Delivery' => array('Delivery.isVisibleFrontEnd' => 'Y',
                'Delivery.stato_elaborazione' => 'OPEN',
                'Delivery.data' => $deliveryData),
            'Order' => array('Order.state_code != ' => 'CREATE-INCOMPLETE'),
            'SuppliersOrganization' => array('SuppliersOrganization.stato' => 'Y'));
        if (!empty($this->user->id) && $this->user->get('org_id') == $this->user->organization['Organization']['id'])
            $conditions += array('Cart' => array('Cart.user_id' => (int) $this->user->id,
                    'Cart.deleteToReferent' => 'N'));

        $orderBy = array('Order' => 'Order.data_fine');
        $results = $this->Delivery->getDataTabs($tmp_user, $conditions, $options, $orderBy);

        /*
         * ctrl configurazione Organization  
         */
        $storeroomResults = [];
        if ($this->user->organization['Organization']['hasStoreroom'] == 'Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y') {

            /*
             * Consegne per la dispensa
			$conditions = []; 
            $conditions = array('Delivery.data' => $deliveryData,
                'Delivery.organization_id = ' . (int) $tmp_user->organization['Organization']['id'],
                'Delivery.isToStoreroom' => 'Y',
                'Delivery.isVisibleFrontEnd' => 'Y',
                'Delivery.stato_elaborazione' => 'OPEN');
            $storeroomResults = $this->Delivery->find('all', array('fields' => array('Delivery.id', 'data'),
                'conditions' => $conditions,
                'order' => 'data ASC',
                'recursive' => -1));
			*/
			$delivery_id = 0;
			if(!empty($results))	
				foreach($results['Tab'] as $numTabs => $tab) 
					foreach($tab['Delivery'] as $numDelivery => $delivery) 
						$delivery_id = $delivery['id'];
			
			if($delivery_id>0) {
				App::import('Model', 'Storeroom');
				$Storeroom = new Storeroom;
				
				$conditions = [];
				$conditions['Delivery.id'] = $delivery_id;
				$conditions['User.id'] = $this->user->id;
				$storeroomResults = $Storeroom->getArticlesToStoreroom($this->user, $conditions);

			} // end if($delivery_id>0)
		}
        $this->set(compact('results'));
        $this->set('delivery_id', $delivery_id);
        $this->set('storeroomResults', $storeroomResults);

        /*
         *  calcolo il totale importo delle consegna
         *  se Order.state_code =='PROCESSED-ON-DELIVERY' prendo anche le spese trasporto, sconti...
         */
		$tmp_user_id = $this->user->id;
		
        App::import('Model', 'Cart');
        $Cart = new Cart;

		App::import('Model', 'SummaryOrderPlu');
        $SummaryOrderPlu = new SummaryOrderPlu;

		$tot_importo_delivery = 0;

        if (!empty($this->user->id) && $this->user->get('org_id') == $this->user->organization['Organization']['id'] && !empty($results))
            foreach ($results['Tab'] as $numTabs => $tab)
                foreach ($tab['Delivery'] as $numDelivery => $delivery)
                    if ($delivery['totOrders'] > 0) {
                        foreach ($delivery['Order'] as $order) {

                            $tot_importo_order = 0;
                            if (isset($order['ArticlesOrder']))
                                foreach ($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
                                    if (!empty($order['Cart'][$numArticlesOrder])) {

                                        /*
                                         * gestione importi
                                         * */
                                        if ($order['Cart'][$numArticlesOrder]['importo_forzato'] == 0) {
                                            if ($order['Cart'][$numArticlesOrder]['qta_forzato'] > 0)
                                                $importo = ($order['Cart'][$numArticlesOrder]['qta_forzato'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
                                            else {
                                                $importo = ($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
                                            }
                                        } else {
                                            $importo = $order['Cart'][$numArticlesOrder]['importo_forzato'];
                                        }

                                        $tot_importo_order += $importo;
                                    }
                                } // loop ArticlesOrder

                                /*
                                 * ctrl eventuali spese aggiuntive
                                 */
                            if ($order['Order']['state_code'] == 'PROCESSED-ON-DELIVERY') {

								$resultsSummaryOrderPlus = $SummaryOrderPlu->addSummaryOrder($this->user, $order, $tmp_user_id);

                                $resultsWithModifies[$order['Order']['id']]['SummaryOrder'] = $resultsSummaryOrderPlus['SummaryOrder'];
                                $resultsWithModifies[$order['Order']['id']]['SummaryOrderTrasport'] = $resultsSummaryOrderPlus['SummaryOrderTrasport'];
                                $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostMore'] = $resultsSummaryOrderPlus['SummaryOrderCostMore'];
                                $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostLess'] = $resultsSummaryOrderPlus['SummaryOrderCostLess'];

                                if (isset($resultsSummaryOrderPlus['SummaryOrderTrasport'][0]))
                                    $importo_trasport = $resultsSummaryOrderPlus['SummaryOrderTrasport'][0]['SummaryOrderTrasport']['importo_trasport'];

                                if (isset($resultsSummaryOrderPlus['SummaryOrderCostMore'][0]))
                                    $importo_cost_more = $resultsSummaryOrderPlus['SummaryOrderCostMore'][0]['SummaryOrderCostMore']['importo_cost_more'];

                                if (isset($resultsSummaryOrderPlus['SummaryOrderCostLess'][0]))
                                    $importo_cost_less = $resultsSummaryOrderPlus['SummaryOrderCostLess'][0]['SummaryOrderCostLess']['importo_cost_less'];

                                if (isset($resultsSummaryOrderPlus['SummaryOrder'][0])) {

                                    $importo = $resultsSummaryOrderPlus['SummaryOrder'][0]['SummaryOrder']['importo'];

                                    if ($importo_trasport == 0 && $importo_cost_less == 0 && $importo_cost_more == 0)
                                        $tot_importo_order = $importo;  // sovrascrivo con i dati aggregati
                                    else
                                        $tot_importo_order = ($tot_importo_order + $importo_trasport + $importo_cost_more + $importo_cost_less);
                                }
                            }  // if($result['Order']['state_code']=='PROCESSED-ON-DELIVERY')	

                            $tot_importo_delivery += $tot_importo_order;
                        } // loop orders			
                    }

        $this->set('tot_importo_delivery', $tot_importo_delivery);

        $this->layout = 'ajax';
    }

    /*
     * visualizzo i tab   ordini per consegna / ordini per produttore 
     */

    public function tabsEcomm() {

		$debug = false;
        
		/*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

		App::import('Model', 'ProdGasPromotionsOrganizationsManager');
		$ProdGasPromotionsOrganizationsManager = new ProdGasPromotionsOrganizationsManager;
					
		$prodGasPromotionsOrganizationsResults = $ProdGasPromotionsOrganizationsManager->getOpenPromotions($this->user, $debug);
		$this->set(compact('prodGasPromotionsOrganizationsResults'));
		
        $this->layout = 'default_front_end';
    }

    /*
     * visualizzo il tab   ordini per consegna
     */
    public function tabsEcommTabOrdersDelivery($delivery_date = null) {

        /*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $conditions = ['Delivery' => ['Delivery.isVisibleFrontEnd' => 'Y',
									'Delivery.stato_elaborazione' => 'OPEN',
									'Delivery.sys' => 'N',
									'DATE(Delivery.data) >= CURDATE()']];

        $results = $this->Delivery->getTabsToDeliveriesData($this->user, $conditions['Delivery']);

        /*
         * ctrl se ci sono ordini con la consegna ancora da definire (Delivery.sys = Y)
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $ordersResults = $Order->getOrdersDeliverySys($this->user);

        if (count($ordersResults) > 0) {
            $tmp['Delivery']['data'] = Configure::read('DeliveryToDefinedDate');
            $results[count($results)] = $tmp;
        }
        $this->set(compact('results'));

        $this->layout = 'default_front_end';
    }

    /*
     * visualizzo il tab  promozioni
     */
    public function tabsEcommTabProdGasPromotions($delivery_date = null) {

        /*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $conditions = ['Delivery' => ['Delivery.isVisibleFrontEnd' => 'Y',
									'Delivery.stato_elaborazione' => 'OPEN',
									'Delivery.sys' => 'N',
									'DATE(Delivery.data) >= CURDATE()']];

        $results = $this->Delivery->getTabsToDeliveriesGasProdPromotionsData($this->user, $conditions['Delivery']);
		self::d($results); 
        $this->set(compact('results'));

        $this->layout = 'default_front_end';
    }	
	
    /*
     * visualizzo il tab   ordini per produttore
     * elenco di tutti gli ordini aperti di un GAS per il tab "ordini per produttore" 
     */

    public function tabsEcommTabAllOrders() {

        /*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        App::import('Model', 'Order');
        $Order = new Order;

        $options = [];
        $options['conditions'] = ['Delivery.organization_id' => $this->user->organization['Organization']['id'],
									'Delivery.isVisibleFrontEnd' => 'Y',
									'Delivery.stato_elaborazione' => 'OPEN',
									'DATE(Delivery.data) >= CURDATE()',
									'Order.organization_id' => $this->user->organization['Organization']['id'],
									'Order.isVisibleBackOffice' => 'Y',
									'Order.state_code' => 'OPEN'];
        $options['order'] = ['SuppliersOrganization.name'];
        $allOrdersResults = $Order->find('all', $options);
		
        $results = [];
        if (!empty($allOrdersResults))
            foreach ($allOrdersResults as $numResult => $allOrdersResult) {
                if ($allOrdersResult['Delivery']['sys'] == 'N')
                    $delivery = $allOrdersResult['Delivery']['luogoData'];
                else
                    $delivery = Configure::read('DeliveryToDefinedLabel');

                $results[$numResult]['id'] = $allOrdersResult['Delivery']['id'] . '-' . $allOrdersResult['Order']['id'];
                $results[$numResult]['SuppliersOrganization']['name'] = $allOrdersResult['SuppliersOrganization']['name'];
                $results[$numResult]['Delivery']['id'] = $allOrdersResult['Delivery']['id'];
                $results[$numResult]['Delivery']['name'] = $delivery;
                $results[$numResult]['Order']['data_fine'] = $allOrdersResult['Order']['data_fine'];
                $results[$numResult]['Order']['type_draw'] = $allOrdersResult['Order']['type_draw'];
            }
        
		self::d($results, false);
			
        $this->set(compact('results'));

        /*
         * ottengo l'elenco dei produttori con ordini da validare i colli (pezzi_confezione)
         * */
        $options = [];
        $options['conditions'] = ['Delivery.organization_id' => $this->user->organization['Organization']['id'],
									'Delivery.isVisibleFrontEnd' => 'Y',
									'Delivery.stato_elaborazione' => 'OPEN',
									'DATE(Delivery.data) >= CURDATE()',
									'Order.organization_id' => $this->user->organization['Organization']['id'],
									'Order.isVisibleBackOffice' => 'Y',
									'Order.state_code' => 'RI-OPEN-VALIDATE'];
        $options['order'] = ['Order.data_fine'];
        $allOrdersResults = $Order->find('all', $options);

        $ordersToValidateResults = [];
        if (!empty($allOrdersResults)) {
            foreach ($allOrdersResults as $numResult => $allOrdersResult) {
                if ($allOrdersResult['Delivery']['sys'] == 'N')
                    $delivery = $allOrdersResult['Delivery']['luogoData'];
                else
                    $delivery = Configure::read('DeliveryToDefinedLabel');

                $ordersToValidateResults[$numResult]['id'] = $allOrdersResult['Delivery']['id'] . '-' . $allOrdersResult['Order']['id'];
                $ordersToValidateResults[$numResult]['SuppliersOrganization']['name'] = $allOrdersResult['SuppliersOrganization']['name'];
                $ordersToValidateResults[$numResult]['Delivery']['id'] = $allOrdersResult['Delivery']['id'];
                $ordersToValidateResults[$numResult]['Delivery']['name'] = $delivery;
                $ordersToValidateResults[$numResult]['Order']['data_fine_validation'] = $allOrdersResult['Order']['data_fine_validation'];
                $ordersToValidateResults[$numResult]['Order']['type_draw'] = $allOrdersResult['Order']['type_draw'];
            }
		}
		$this->set('ordersToValidateResults', $ordersToValidateResults);

        $this->layout = 'default_front_end';
    }

    public function tabsAjaxEcommDeliveries($deliveryData) {

        $this->ctrlHttpReferer();

        /*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        if (empty($deliveryData)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $conditions = ['Delivery.isVisibleFrontEnd' => 'Y',
						'Delivery.stato_elaborazione' => 'OPEN',
						'DATE(Delivery.data) >= CURDATE()',
						'Delivery.organization_id' => $this->user->organization['Organization']['id'],
						'Delivery.data' => $deliveryData];

        $orderBy = 'data ASC';
        $results = $this->Delivery->find('all', ['conditions' => $conditions,
												'order' => $orderBy,
												'recursive' => -1]);
        /*
         * ottengo l'elenco dei produttori
         * */
        foreach ($results as $numResult => $result) {
            $sql = "SELECT 
						SuppliersOrganization.id, SuppliersOrganization.name, 
						Supplier.descrizione,
						`Order`.id, `Order`.data_inizio, `Order`.data_fine, `Order`.type_draw 
					FROM 
						" . Configure::read('DB.prefix') . "deliveries Delivery,
						" . Configure::read('DB.prefix') . "orders `Order`,
						" . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,
						" . Configure::read('DB.prefix') . "suppliers Supplier
					WHERE 
						Delivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND `Order`.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND SuppliersOrganization.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND Delivery.id = `Order`.delivery_id
						AND Delivery.isVisibleFrontEnd = 'Y'
						AND `Order`.isVisibleBackOffice = 'Y'
						AND `Order`.state_code = 'OPEN'  
						AND `Order`.supplier_organization_id = SuppliersOrganization.id
						AND SuppliersOrganization.supplier_id = Supplier.id				 
						AND SuppliersOrganization.stato = 'Y'
						AND (Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')	
						AND Delivery.id = " . $result['Delivery']['id'] . "  				
					ORDER BY `Order`.data_fine";
            self::d($sql, false);
            $suppliersOrganizationResults = $this->Delivery->query($sql);

            $results[$numResult]['Delivery']['Order'] = $suppliersOrganizationResults;
        }

        /*
         * ottengo l'elenco dei produttori con ordini da validare i colli (pezzi_confezione)
         * */
        foreach ($results as $numResult => $result) {
            $sql = "SELECT
						SuppliersOrganization.id, SuppliersOrganization.name,
						Supplier.descrizione,
						`Order`.id, `Order`.data_inizio, `Order`.data_fine, `Order`.data_fine_validation, `Order`.type_draw 
					FROM
						" . Configure::read('DB.prefix') . "deliveries Delivery,
						" . Configure::read('DB.prefix') . "orders `Order`,
						" . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,
						" . Configure::read('DB.prefix') . "suppliers Supplier
					WHERE
						Delivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND `Order`.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND SuppliersOrganization.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND Delivery.id = `Order`.delivery_id
						AND Delivery.isVisibleFrontEnd = 'Y'
						AND `Order`.isVisibleBackOffice = 'Y'
						AND `Order`.state_code = 'RI-OPEN-VALIDATE' 
						AND (`Order`.data_fine_validation != '" . Configure::read('DB.field.date.empty') . "' && DATE(`Order`.data_fine_validation) >= CURDATE()) 
						AND `Order`.supplier_organization_id = SuppliersOrganization.id
						AND SuppliersOrganization.supplier_id = Supplier.id
						AND SuppliersOrganization.stato = 'Y'
						AND (Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')
						AND Delivery.id = " . $result['Delivery']['id'] . "
					ORDER BY `Order`.data_fine";
            self::d($sql, false);
            $suppliersOrganizationResults = $this->Delivery->query($sql);

            $results[$numResult]['Delivery']['OrderToValidate'] = $suppliersOrganizationResults;
        }
		self::d($results);
        $this->set(compact('results'));
        $this->layout = 'ajax';
    }

	public function tabsAjaxEcommProdGasPromotionsDeliveries($deliveryData) {

        $this->ctrlHttpReferer();

        /*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        if (empty($deliveryData)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $conditions = ['Delivery.isVisibleFrontEnd' => 'Y',
						'Delivery.stato_elaborazione' => 'OPEN',
						'DATE(Delivery.data) >= CURDATE()',
						'Delivery.organization_id' => $this->user->organization['Organization']['id'],
						'Delivery.data' => $deliveryData];

        $orderBy = 'data ASC';
        $results = $this->Delivery->find('all', ['conditions' => $conditions,
												'order' => $orderBy,
												'recursive' => -1]);
        /*
         * ottengo l'elenco dei produttori
         * */
        foreach ($results as $numResult => $result) {
            $sql = "SELECT 
						SuppliersOrganization.id, SuppliersOrganization.name, 
						Supplier.descrizione,
						`Order`.id, `Order`.data_inizio, `Order`.data_fine, `Order`.type_draw 
					FROM 
						" . Configure::read('DB.prefix') . "deliveries Delivery,
						" . Configure::read('DB.prefix') . "orders `Order`,
						" . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,
						" . Configure::read('DB.prefix') . "suppliers Supplier
					WHERE 
						Delivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND `Order`.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND SuppliersOrganization.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND Delivery.id = `Order`.delivery_id
						AND Delivery.isVisibleFrontEnd = 'Y'
						AND `Order`.isVisibleBackOffice = 'Y'
						AND `Order`.state_code = 'OPEN' 

						AND `Order`.prod_gas_promotion_id != 0 
						
						AND `Order`.supplier_organization_id = SuppliersOrganization.id
						AND SuppliersOrganization.supplier_id = Supplier.id				 
						AND SuppliersOrganization.stato = 'Y'
						AND (Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')	
						AND Delivery.id = " . $result['Delivery']['id'] . "  				
					ORDER BY `Order`.data_fine";
            self::d($sql, false);
            $suppliersOrganizationResults = $this->Delivery->query($sql);

            $results[$numResult]['Delivery']['Order'] = $suppliersOrganizationResults;
        }
		self::d($results);
        $this->set(compact('results'));
        $this->layout = 'ajax';	 
	}
	
    public function tabsAjaxEcommArticlesOrder($delivery_id, $order_id, $filterArticleName = null, $filterArticleArticleTypeIds = null, $type_draw = null) {
        $this->ctrlHttpReferer();

        /*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        if (empty($delivery_id) || empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $results = [];
        /*
         * dati dell'ordine
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $options = [];
        $options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
									'Order.id' => $order_id,
									'Order.delivery_id' => $delivery_id,
									'Order.state_code' => 'OPEN'];
        $options['recursive'] = 0;
        // $Order->unbindModel(array('belongsTo' => array('Delivery')));
        $order = $Order->find('first', $options);
        
		$owner_articles = $order['Order']['owner_articles'];
		$owner_organization_id = $order['Order']['owner_organization_id'];
		$owner_supplier_organization_id = $order['Order']['owner_supplier_organization_id'];
        $article_organization_id = $order['Order']['owner_organization_id'];
        
        $this->set('order', $order);

        if (!empty($order)) {

			/*
			 * dati promozione
			 */
			$supplier_id = 0;
			$prod_gas_promotion_id = $order['Order']['prod_gas_promotion_id']; 
			$this->set(compact('supplier_id', $prod_gas_promotion_id));	 
			if($prod_gas_promotion_id!=0) {		 
				App::import('Model', 'ProdGasPromotion');
				$ProdGasPromotion = new ProdGasPromotion;
				 
				$promotionResults = $ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id, $this->user->organization['Organization']['id'], $debug);
				self::d($promotionResults, $debug);
				$this->set(compact('promotionResults'));
			}
			
            /*
             * dati del produttore img e distance
             */
            App::import('Model', 'Supplier');
            $Supplier = new Supplier;

            $options = [];
            $options['conditions'] = ['Supplier.id' => $order['SuppliersOrganization']['supplier_id']];
            $options['recursive'] = -1;
            $supplier = $Supplier->find('first', $options);
            $this->set('supplier', $supplier);

            jimport('joomla.user.helper');
            $userProfile = JUserHelper::getProfile($this->user->get('id'));

            $numDistance = 0;
            $arrayDistances = [];
            if (!empty($userProfile->profile['lat']) && $userProfile->profile['lat'] != Configure::read('LatLngNotFound') && !empty($userProfile->profile['lng']) && $userProfile->profile['lng'] != Configure::read('LatLngNotFound')) {

                $userLat = $userProfile->profile['lat'];
                $userLng = $userProfile->profile['lng'];

                /*
                 *  crea l'array DISTANCE
                 */
                $supplierLat = $supplier['Supplier']['lat'];
                $supplierLng = $supplier['Supplier']['lng'];

                if (!empty($supplierLat) && $supplierLat != Configure::read('LatLngNotFound') && !empty($supplierLng) && $supplierLng != Configure::read('LatLngNotFound')) {

                    $arrayDistances[$numDistance]['supplierName'] = $supplier['Supplier']['name'];

                    $address = "";
                    $address = $supplier['Supplier']['localita'];
                    //if(!empty($supplier['Supplier']['cap']))
                    //	$address .= ' ('.$supplier['Supplier']['cap'].')';
                    $arrayDistances[$numDistance]['supplierLocalita'] = $address;

                    $distance = $this->distance($userLat, $userLng, $supplierLat, $supplierLng, "K");
                    $distance = round($distance, 2);

                    $arrayDistances[$numDistance]['distance'] = $distance;
                    $percentuale = 100;
                    if ($distance < Configure::read('LatLngDistanceAbsolute')) {
                        $percentuale = $distance * 100 / Configure::read('LatLngDistanceAbsolute');
                        $percentuale = round($percentuale);
                    }
                    $arrayDistances[$numDistance]['percentuale'] = $percentuale;
                }
            }
            $this->set('arrayDistances', $arrayDistances);





			/*
			 * type_draw SIMPLE / COMPLETE / PROMOTION
			 * se non viene passato prendo quello dell'ordine Order.type_draw
			 */
			self::d($order['Order'], $debug); 
			if (empty($type_draw))
			 	$type_draw = $order['Order']['type_draw'];
			$this->set('type_draw', $type_draw);
			// echo '<br />type_draw '.$type_draw;

            App::import('Model', 'ArticlesOrder');
            $ArticlesOrder = new ArticlesOrder;

            $options = [];
            $options['conditions'] = ['Cart.user_id' => $this->user->id,
						              'Cart.deleteToReferent' => 'N'];
            if (!empty($filterArticleName))
                $options['conditions'] += ['Article.name' => $filterArticleName];
            if (!empty($filterArticleArticleTypeIds))
                $options['conditions'] += ['ArticleArticleTypeId.article_type_id' => $filterArticleArticleTypeIds];

            $options['order'] = 'Article.name';
			if($prod_gas_promotion_id==0) 
				$results = $ArticlesOrder->getArticoliEventualiAcquistiInOrdine($this->user, $order, $options);
			else
				$results = $ArticlesOrder->getArticoliEventualiAcquistiInOrdinePromotion($this->user, $order, $prod_gas_promotion_id, $options);
				
			self::d($results, $debug);
			 	
        } // end if(!empty($order)) 	
        $this->set(compact('results'));

        $this->set('FilterArticleName', $filterArticleName);
        $this->set('FilterArticleArticleTypeIds', $filterArticleArticleTypeIds);

        if (Cache::read('articlesTypes') === false) {
            App::import('Model', 'ArticlesType');
            $ArticlesType = new ArticlesType;
            $ArticlesTypeResults = $ArticlesType->prepareArray($ArticlesType->getArticlesTypes());
            Cache::write('articlesTypes', $ArticlesTypeResults);
        } else
            $ArticlesTypeResults = Cache::read('articlesTypes');
        $this->set('ArticlesTypeResults', $ArticlesTypeResults);

        $this->layout = 'ajax';
    }

    /*
     * gestisco gli ordini da validare i colli (pezzi_confezione)
     * Order.state_code = PROCESSED-BEFORE-DELIVERY
     */

    public function tabsAjaxEcommCartsValidation($delivery_id, $order_id) {
        $this->ctrlHttpReferer();

        /*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        if (empty($delivery_id) || empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * dati dell'ordine
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $options = [];
        $options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
					            'Order.id' => $order_id,
					            'Order.delivery_id' => $delivery_id,
					            'Order.state_code' => 'RI-OPEN-VALIDATE'];
        $options['recursive'] = 0;
        $order = $Order->find('first', $options);
        
		$owner_articles = $order['Order']['owner_articles'];
		$owner_organization_id = $order['Order']['owner_organization_id'];
		$owner_supplier_organization_id = $order['Order']['owner_supplier_organization_id'];
        $article_organization_id = $order['Order']['owner_organization_id'];
        
        $this->set('order', $order);

        if (!empty($order)) {
            /*
             * * estraggo gli acquisti da validate (ArticlesOrder.pezzi_confezione > 1)
             */
            App::import('Model', 'Cart');
            $Cart = new Cart;
            $results = $Cart->getCartToValidateFrontEnd($this->user, $delivery_id, $order_id, $article_organization_id);
            $this->set(compact('results'));
        }

        $this->layout = 'ajax';
    }

    /*
     * carrello dell'utente
     * dispensa dell'utente
     */

    public function tabsUserCart($format = 'html') {

        /*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        // parametro passato da storeroom_to_user.ctp
        if (isset($_REQUEST['esito']) && $_REQUEST['esito'] == 'OK')
            $this->Session->setFlash(__('The cart has been saved'));

		$results = [];
		
		/*
		 * sql
		 */
        $sqlDelivery = "SELECT
					Delivery.data 
				FROM
					" . Configure::read('DB.prefix') . "deliveries Delivery,
					" . Configure::read('DB.prefix') . "orders `Order`,
					" . Configure::read('DB.prefix') . "articles_orders ArticlesOrder,
					" . Configure::read('DB.prefix') . "carts Cart,
					" . Configure::read('DB.portalPrefix') . "users User
				WHERE
					Delivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
					AND `Order`.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
					AND ArticlesOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
					AND Cart.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
					AND User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
					AND Delivery.id = `Order`.delivery_id
				    AND Delivery.isVisibleFrontEnd = 'Y'
					AND `Order`.isVisibleBackOffice = 'Y'
					AND ArticlesOrder.order_id = `Order`.id
					AND ArticlesOrder.order_id = Cart.order_id
					AND ArticlesOrder.article_id = Cart.article_id
					AND Cart.user_id = User.id
					AND Cart.deleteToReferent = 'N'
					AND ArticlesOrder.stato != 'N'
					AND Cart.user_id = " . $this->user->id . "
					AND User.id = " . $this->user->id . "
					AND Delivery.data >= CURDATE() - INTERVAL " . Configure::read('GGinMenoPerEstrarreDeliveriesCartInTabs') . " DAY 
				GROUP BY Delivery.data
				ORDER BY Delivery.data DESC";
        // AND Delivery.stato_elaborazione = 'OPEN'
		if ($this->user->organization['Organization']['hasStoreroom'] == 'Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y') {

			$sql = "(".$sqlDelivery.") UNION (";
			$sql .= "SELECT
						Delivery.data 
					FROM
						" . Configure::read('DB.prefix') . "deliveries Delivery, 
						" . Configure::read('DB.prefix') . "storerooms Storeroom,
						" . Configure::read('DB.portalPrefix') . "users User
					WHERE
						Delivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND Storeroom.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND Delivery.isVisibleFrontEnd = 'Y'
						AND Delivery.isToStoreroom = 'Y'
						AND Storeroom.user_id = User.id
						AND Storeroom.user_id = " . $this->user->id . "
						AND User.id = " . $this->user->id . "
						AND Delivery.data >= CURDATE() - INTERVAL " . Configure::read('GGinMenoPerEstrarreDeliveriesCartInTabs') . " DAY 
					GROUP BY Delivery.data
					ORDER BY Delivery.data DESC";
			$sql .= ") ORDER BY data DESC";
		}
		else
			$sql = $sqlDelivery;
			
        self::d($sql, false);
        $results = $this->Delivery->query($sql);

        /*
         * rimappo per D I S P E N S A
         */
		if ($this->user->organization['Organization']['hasStoreroom'] == 'Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y') {
			$newResults = [];
			foreach($results as $numResults => $result) {
				$newResults[$numResults]['Delivery']['data']=$result[0]['data'];
			}
			
			$results = [];
			$results = $newResults;
		}			

		self::d($results, false);
		
		$this->set(compact('results'));

		
        $this->layout = 'default_front_end';
    }

    /*
     * carrello dell'utente in sola lettura
     * dispensa dell'utente in sola lettura
     * 
     * dal link della mail /preview-carrello?E=3456434&O=451&R=fHqbzWjOK6GaWezgE4mycHsphSPsE9HhincbgjTmDjY=&format=html
     * 	E = random, non serve a niente
     *  O = (tolgo i primi 2 numeri e poi organization_id) organization_id
     *  R = username crittografata User->getUsernameCrypted()
     *  D = (tolgo i primi 2 numeri e poi delivery_id) delivery_id
     *  org_id serve per mod_gas_organization_choice
     */

    public function tabsUserCartPreview($E = 0, $O = 0, $R = 0, $D = 0, $org_id = 0, $format = 'html') {

        $debug = false;

        /*
         * redirect al carrello se gia' loggato
         */
        if (!empty($this->user->id) && !empty($this->user->organization_id)) {

            App::import('Model', 'Organization');
            $Organization = new Organization;

            $options = [];
            $options['conditions'] = array('Organization.id' => (int) $this->user->organization_id);
            $options['recursive'] = array('Organization.j_seo');
            $options['recursive'] = -1;

            $results = $Organization->find('first', $options);
            $j_seo = $results['Organization']['j_seo'];
            $url = '/home-' . $j_seo . '/carrello-' . $j_seo;

            $this->myRedirect($url);
        } // end if(!empty($this->user->id) && !empty($this->user->organization_id))

        $userPreview = $this->_getUserPreview($E, $O, $R, $D, $debug);

        $organization_id = $userPreview->organization_id;
        $user_id = $userPreview->user_id;
        $delivery_id = $userPreview->delivery_id;

        /*
         * consegna
         * la leggo sempre nel caso non ho acquisti
         */
        $options = [];
        $options['conditions'] = array('Delivery.organization_id' => $this->user->organization['Organization']['id'],
            'Delivery.isVisibleFrontEnd' => 'Y',
            'Delivery.stato_elaborazione' => 'OPEN',
            'Delivery.id' => $delivery_id);
        $options['recursive'] = -1;
        $deliveryResults = $this->Delivery->find('first', $options);
        if (empty($deliveryResults)) {
            $this->myRedirect(Configure::read('routes_msg_frontend_cart_preview'));
        }
        $this->set('deliveryResults', $deliveryResults);

        /*
         * carrello
         */
        $conditions = array('Delivery' => array('Delivery.isVisibleFrontEnd' => 'Y',
                'Delivery.stato_elaborazione' => 'OPEN',
                'Delivery.id' => $delivery_id),
            'Cart' => array('Cart.user_id' => (int) $user_id,
                'Cart.deleteToReferent' => 'N'),
            'Order' => array("Order.state_code != 'CREATE-INCOMPLETE'"),
            'SuppliersOrganization' => array('SuppliersOrganization.stato' => 'Y'));

        $options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
            'articoliDellUtenteInOrdine' => true, // estraggo SOLO gli articoli acquistati da un utente in base all'ordine
            'suppliers' => true, 'referents' => false);

        $results = $this->Delivery->getDataTabs($userPreview, $conditions, $options);
        $this->set(compact('results'));

        /*
         * loops Orders, if Order.state_code = PROCESSED-ON-DELIVERY (in carico al cassiere) / CLOSE  faccio vedere le modifiche
         */
        $resultsWithModifies = [];
        /*
          ora per tutti
          if($this->user->organization['Template']['payToDelivery']=='ON' ||
          $this->user->organization['Template']['payToDelivery']=='ON-POST') {
         */
        App::import('Model', 'Cart');
        $Cart = new Cart;
		
        App::import('Model', 'SummaryOrderLifeCycle');
        $SummaryOrderLifeCycle = new SummaryOrderLifeCycle;
		
        App::import('Model', 'SummaryOrderPlu');
        $SummaryOrderPlu = new SummaryOrderPlu;
		
        if (isset($results['Tab']))
            foreach ($results['Tab'] as $numTabs => $tab) {
                foreach ($tab['Delivery'] as $numDelivery => $delivery) {
                    if ($delivery['totOrders'] > 0 && $delivery['totArticlesOrder'] > 0) {
                        if (isset($delivery['Order'])) {
                            $order_id_old = 0;
                            foreach ($delivery['Order'] as $numOrder => $order) {

                                // echo "<br />".$order['Order']['state_code'];

								/*
								 * aggiunge ad un ordine le eventuali 
								 *  SummaryOrder 
								 *  SummaryOrderTrapsort spese di trasporto
								 *  SummaryOrderMore spese generiche
								 *  SummaryOrderLess sconti
								 */
								if($SummaryOrderLifeCycle->canAddSummaryOrder($this->user, $order['Order']['state_code'])) {
									                                 
										$resultsSummaryOrderPlus = $SummaryOrderPlu->addSummaryOrder($this->user, $order, $user_id);
	
	                                    $resultsWithModifies[$order['Order']['id']]['SummaryOrder'] = $resultsSummaryOrderPlus['SummaryOrder'];
	                                    $resultsWithModifies[$order['Order']['id']]['SummaryOrderTrasport'] = $resultsSummaryOrderPlus['SummaryOrderTrasport'];
	                                    $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostMore'] = $resultsSummaryOrderPlus['SummaryOrderCostMore'];
	                                    $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostLess'] = $resultsSummaryOrderPlus['SummaryOrderCostLess'];
	
	                                    // $results = $this->ExportDoc->getCartCompliteOrder($order_id, $results, $resultsSummaryOrderAggregate, $resultsSummaryOrderTrasport, $resultsSummaryOrderCostMore, $resultsSummaryOrderCostLess, $debug);					
                                }  
                            } // loop Order
                        } // end if(isset($delivery['Order']))
                    } // end if(($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0)
                } // loop Delivery
            } // loop Tab
            
// }

        $this->set('resultsWithModifies', $resultsWithModifies);		
		
        /*
         * D I S P E N S A
         */
        $storeroomResults = [];
        if ($userPreview->organization['Organization']['hasStoreroom'] == 'Y' && $userPreview->organization['Organization']['hasStoreroomFrontEnd'] == 'Y') {

			$options = [];
            $options = array('orders' => false, 'storerooms' => true, 'summaryOrders' => false,
                'suppliers' => true, 'referents' => false);

			$conditions = [];
            $conditions = array('Delivery' => array('Delivery.isVisibleFrontEnd' => 'Y',
                    'Delivery.stato_elaborazione' => 'OPEN',
                    'DATE(Delivery.data) >= CURDATE()',
                    'Delivery.data' => $deliveryData),
                'Storeroom' => array('Storeroom.user_id' => (int) $user_id),
                'SuppliersOrganization' => array('SuppliersOrganization.stato' => 'Y'));
            $orderBy = null;
            $storeroomResults = $this->Delivery->getDataTabs($userPreview, $conditions, $options, $orderBy);
        }
        $this->set('storeroomResults', $storeroomResults);

        $this->layout = 'default_front_end';
    }

    public function tabsAjaxUserCartDeliveries($deliveryData) {

        $this->ctrlHttpReferer();

        /*
         * in AppController setto $this->user
         * $this->user->organization_id                    = organization dell'utente (in table.User)
         * $this->user->organization['Organization']['id'] = organization che si sta navigando (dal templates ho il parametro organization_id e organizationSEO che metto in $user->set('org_id',$organization_id))
         */
        if ($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        if (empty($deliveryData)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        /*
         * carrello
         */
		$conditions = []; 
        $conditions = ['Delivery' => ['Delivery.isVisibleFrontEnd' => 'Y',
                //  'Delivery.stato_elaborzione'=> 'OPEN',
                'DATE(Delivery.data) >= CURDATE() - INTERVAL ' . Configure::read('GGinMenoPerEstrarreDeliveriesCartInTabs') . ' DAY ',
                'Delivery.data' => $deliveryData],
            'Cart' => ['Cart.user_id' => (int) $this->user->id,
                'Cart.deleteToReferent' => 'N'],
            'Order' => ['Order.state_code != ' => 'CREATE-INCOMPLETE'],
            'SuppliersOrganization' => ['SuppliersOrganization.stato' => 'Y']];
	
	    $options = []; 
        $options = ['orders' => true, 'storerooms' => false, 'summaryOrders' => false,
            'articoliDellUtenteInOrdine' => true, // estraggo SOLO gli articoli acquistati da un utente in base all'ordine
            'suppliers' => true, 'referents' => false];

        $results = $this->Delivery->getDataTabs($this->user, $conditions, $options);
        $this->set(compact('results'));

        $resultsWithModifies = [];
        /*
          ora per tutti
          if($this->user->organization['Template']['payToDelivery']=='ON' ||
          $this->user->organization['Template']['payToDelivery']=='ON-POST') {
         */
		$tmp_user_id = $this->user->id;
		
        App::import('Model', 'Cart');
        $Cart = new Cart;
		
        App::import('Model', 'SummaryOrderLifeCycle');
        $SummaryOrderLifeCycle = new SummaryOrderLifeCycle;
		
        App::import('Model', 'SummaryOrderPlu');
        $SummaryOrderPlu = new SummaryOrderPlu;
		
        if (isset($results['Tab']))
            foreach ($results['Tab'] as $numTabs => $tab) {
                foreach ($tab['Delivery'] as $numDelivery => $delivery) {
                    if ($delivery['totOrders'] > 0 && $delivery['totArticlesOrder'] > 0) {
                        if (isset($delivery['Order'])) {
                            $order_id_old = 0;
                            foreach ($delivery['Order'] as $numOrder => $order) {

                                // echo "<br />".$order['Order']['state_code'];

								/*
								 * aggiunge ad un ordine le eventuali 
								 *  SummaryOrder 
								 *  SummaryOrderTrapsort spese di trasporto
								 *  SummaryOrderMore spese generiche
								 *  SummaryOrderLess sconti
								 */
								if($SummaryOrderLifeCycle->canAddSummaryOrder($this->user, $order['Order']['state_code'])) {
									
									$resultsSummaryOrderPlus = $SummaryOrderPlu->addSummaryOrder($this->user, $order, $tmp_user_id);
									
                                    $resultsWithModifies[$order['Order']['id']]['SummaryOrder'] = $resultsSummaryOrderPlus['SummaryOrder'];
                                    $resultsWithModifies[$order['Order']['id']]['SummaryOrderTrasport'] = $resultsSummaryOrderPlus['SummaryOrderTrasport'];
                                    $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostMore'] = $resultsSummaryOrderPlus['SummaryOrderCostMore'];
                                    $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostLess'] = $resultsSummaryOrderPlus['SummaryOrderCostLess'];

                                    // $results = $this->ExportDoc->getCartCompliteOrder($order_id, $results, $resultsSummaryOrderAggregate, $resultsSummaryOrderTrasport, $resultsSummaryOrderCostMore, $resultsSummaryOrderCostLess, $debug);					
                                }  // if($result['Order']['state_code']=='PROCESSED-ON-DELIVERY' || $order['Order']['state_code']=='CLOSE') 
                            } // loop Order
                        } // end if(isset($delivery['Order']))
                    } // end if(($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0)
                } // loop Delivery
            } // loop Tab
            
// }

        $this->set('resultsWithModifies', $resultsWithModifies);

        /*
         * D I S P E N S A
         */
        $storeroomResults = [];
        if ($this->user->organization['Organization']['hasStoreroom'] == 'Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y') {

			$options = []; 
            $options = array('orders' => false, 'storerooms' => true, 'summaryOrders' => false,
                'suppliers' => true, 'referents' => false);

			$conditions = []; 
            $conditions = array('Delivery' => array('Delivery.isVisibleFrontEnd' => 'Y',
                    'Delivery.stato_elaborazione' => 'OPEN',
                    'DATE(Delivery.data) >= CURDATE()',
                    'Delivery.data' => $deliveryData),
                'Storeroom' => array('Storeroom.user_id' => (int) $this->user->id),
                'SuppliersOrganization' => array('SuppliersOrganization.stato' => 'Y'));
            $orderBy = null;
            $storeroomResults = $this->Delivery->getDataTabs($this->user, $conditions, $options, $orderBy);
        }
        $this->set('storeroomResults', $storeroomResults);


        /*
         * D I S T A N C E
         */
        jimport('joomla.user.helper');
        $userProfile = JUserHelper::getProfile($this->user->get('id'));

        $numDistance = 0;
        $arrayDistances = [];
        if (!empty($userProfile->profile['lat']) && $userProfile->profile['lat'] != Configure::read('LatLngNotFound') && !empty($userProfile->profile['lng']) && $userProfile->profile['lng'] != Configure::read('LatLngNotFound')) {

            $userLat = $userProfile->profile['lat'];
            $userLng = $userProfile->profile['lng'];

            if (isset($results['Tab']))
                foreach ($results['Tab'] as $numTabs => $tab) {
                    foreach ($tab['Delivery'] as $numDelivery => $delivery) {
                        if ($delivery['totOrders'] > 0 && $delivery['totArticlesOrder'] > 0) {
                            if (isset($delivery['Order'])) {
                                $order_id_old = 0;
                                foreach ($delivery['Order'] as $numOrder => $order) {
                                    if (isset($order['ArticlesOrder'])) { // cosi' escludo gli ordini senza acquisti
                                        foreach ($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
                                            if ($order['Order']['id'] != $order_id_old) {

                                                /*
                                                 *  crea l'array DISTANCE
                                                 */
                                                $supplierLat = $order['SuppliersOrganization']['lat'];
                                                $supplierLng = $order['SuppliersOrganization']['lng'];

                                                if (!empty($supplierLat) && $supplierLat != Configure::read('LatLngNotFound') && !empty($supplierLng) && $supplierLng != Configure::read('LatLngNotFound')) {

                                                    $arrayDistances[$numDistance]['supplierName'] = $order['SuppliersOrganization']['name'];

                                                    $address = "";
                                                    $address = $order['SuppliersOrganization']['localita'];
                                                    //if(!empty($order['SuppliersOrganization']['cap']))
                                                    //	$address .= ' ('.$order['SuppliersOrganization']['cap'].')';
                                                    $arrayDistances[$numDistance]['supplierLocalita'] = $address;

                                                    $distance = $this->distance($userLat, $userLng, $supplierLat, $supplierLng, "K");
                                                    $distance = round($distance, 2);

                                                    $arrayDistances[$numDistance]['distance'] = $distance;
                                                    $percentuale = 100;
                                                    if ($distance < Configure::read('LatLngDistanceAbsolute')) {
                                                        $percentuale = $distance * 100 / Configure::read('LatLngDistanceAbsolute');
                                                        $percentuale = round($percentuale);
                                                    }
                                                    $arrayDistances[$numDistance]['percentuale'] = $percentuale;

                                                    $numDistance++;
                                                }
                                                /*
                                                 *  crea l'array DISTANCE
                                                 */

                                                $order_id_old = $order['Order']['id'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } // end foreach
        } // end if lng
        $this->set('arrayDistances', $arrayDistances);


        $this->layout = 'ajax';
    }

    public function admin_index() {
			
        /*
         * aggiorno lo stato delle consegne
         * ctrl se quelle chiuse hanno tutti gli ordini CLOSE
		 * per le consegne chiuse, copio gli eventuali Acquisti dal Carrello con l'utente DISPENSA (Cart) alla DISPENSA (Storeroom)
         * */
		App::import('Model', 'DeliveryLifeCycle');
		$DeliveryLifeCycle = new DeliveryLifeCycle;
		
		$DeliveryLifeCycle->deliveriesToOpen($this->user);	
			
        $utilsCrons = new UtilsCrons(new View(null));
        if (Configure::read('developer.mode'))
            echo "<pre>";
        $utilsCrons->deliveriesStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
        if($this->user->organization['Organization']['hasStoreroom']=='Y') 
				$utilsCrons->articlesFromCartToStoreroom($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
        if (Configure::read('developer.mode'))
            echo "</pre>";

        $this->_ctrl_data_visibility();

        /*
         * escludo quella da definire Delivery.sys = Y 
         */
		$conditions = []; 
        $conditions = ['Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
			            'Delivery.stato_elaborazione' => 'OPEN',
			            'Delivery.sys' => 'N'];
        $SqlLimit = 20;

        $this->Delivery->recursive = -1;
        $this->paginate = ['conditions' => $conditions, 'order' => ['Delivery.data' => 'asc'], 'limit' => $SqlLimit];
        $results = $this->paginate('Delivery');
        
        $this->set(compact('results'));
        $this->set('SqlLimit', $SqlLimit);
    }

    public function admin_index_history() {

		$conditions = [];
        $conditions = ['Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
			            'Delivery.stato_elaborazione' => 'CLOSE',
			            'Delivery.sys' => 'N'];
        $SqlLimit = 20;

        $this->Delivery->recursive = -1;
        $this->paginate = ['conditions' => $conditions, 'order' => ['Delivery.data' => 'asc'], 'limit' => $SqlLimit];
        $results = $this->paginate('Delivery');

        $this->set(compact('results'));
        $this->set('SqlLimit', $SqlLimit);
    }

    public function admin_view() {

        $this->_ctrl_data_visibility();

        /*
         * escludo quella da definire Delivery.sys = Y 
         */
		$conditions = []; 
        $conditions = ['Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
			            'Delivery.stato_elaborazione' => 'OPEN',
			            'Delivery.sys' => 'N'];
        $SqlLimit = 20;

        $this->Delivery->recursive = -1;
        $this->paginate = ['conditions' => $conditions, 'order' => ['Delivery.data' => 'asc'], 'limit' => $SqlLimit];
        $results = $this->paginate('Delivery');

        $this->set(compact('results'));
        $this->set('SqlLimit', $SqlLimit);
    }

    public function admin_add() {

        /*
         * setting fields
         */
        if ($this->request->is('post') || $this->request->is('put')) {
            $orario_da = $this->request->data['Delivery']['orario_da']['hour'] . ':' . $this->request->data['Delivery']['orario_da']['min'];
            $orario_a = $this->request->data['Delivery']['orario_a']['hour'] . ':' . $this->request->data['Delivery']['orario_a']['min'];
            $data = $this->request->data['Delivery']['data'];
            $data_db = $this->request->data['Delivery']['data_db'];
            $nota_evidenza = $this->request->data['Delivery']['nota_evidenza'];
            $isToStoreroom = $this->request->data['Delivery']['isToStoreroom'];
            $isToStoreroomPay = 'N';
            $isVisibleFrontEnd = $this->request->data['Delivery']['isVisibleFrontEnd'];
            $isVisibleBackOffice = $this->request->data['Delivery']['isVisibleBackOffice'];
        } else {
            $orario_da = '10:30';
            $orario_a = '11:30';
            $data = '';
            $data_db = '';
            $nota_evidenza = '';
            $isToStoreroom = 'N';
            $isToStoreroomPay = 'N';
            $isVisibleFrontEnd = 'Y';
            $isVisibleBackOffice = 'Y';
        }
        $this->set('orario_da', $orario_da);
        $this->set('orario_a', $orario_a);
        $this->set('data', $data);
        $this->set('data_db', $data_db);
        $this->set('nota_evidenzaDefault', $nota_evidenza);
        $this->set('isToStoreroomDefault', $isToStoreroom);
        $this->set('isVisibleFrontEndDefault', $isVisibleFrontEnd);
        $this->set('isVisibleBackOfficeDefault', $isVisibleBackOffice);

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->request->data['Delivery']['organization_id'] = $this->user->organization['Organization']['id'];
            $this->request->data['Delivery']['data'] = $this->request->data['Delivery']['data_db'];
            $this->request->data['Delivery']['stato_elaborazione'] = 'OPEN';
            $this->request->data['Delivery']['isToStoreroomPay'] = 'N';

            if ($this->user->organization['Organization']['hasStoreroom'] == 'N' || $this->user->organization['Organization']['hasStoreroomFrontEnd'] == 'N') {
                $this->request->data['Delivery']['isToStoreroom'] = 'N';
                $this->request->data['Delivery']['isToStoreroomPay'] = 'N';
            }

            if ($this->user->organization['Organization']['hasVisibility'] == 'N') {
                $this->request->data['Delivery']['isVisibleFrontEnd'] = 'Y';
                $this->request->data['Delivery']['isVisibleBackOffice'] = 'Y';
            }

			$msg_errors = $this->Delivery->getMessageErrorsToValidate($this->Delivery, $this->request->data);
			if(!empty($msg_errors)) {
				$this->Session->setFlash(__('The delivery could not be saved. Please, try again.').'<br />'.$msg_errors);
			}
			else {
				self::d($this->request->data);
	            $this->Delivery->create();
	            if ($this->Delivery->save($this->request->data)) {
	                $this->Session->setFlash(__('The delivery has been saved'));

	                /*
	                 * creo l'evento su gcalendar solo se sono su portalgas.it (no test / next)
	                 * */
	                if (Configure::read('App.root') == '/var/www/portalgas') {
	                    $utilsCrons = new UtilsCrons(new View(null));
	                    if (Configure::read('developer.mode'))
	                        echo "<pre>";
	                    $utilsCrons->gcalendarUsersDeliveryInsert($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
	                    if (Configure::read('developer.mode'))
	                        echo "</pre>";
	                }
	
	                $this->delivery_id = $this->Delivery->getLastInsertId();		
	                $this->myRedirect(Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Deliveries&action=index&delivery_id=' . $this->delivery_id);
	              }
	              else
	              	$this->Session->setFlash(__('The delivery could not be saved. Please, try again.')); 
            } 
        }

        $nota_evidenza = ClassRegistry::init('Delivery')->enumOptions('nota_evidenza');
        $isVisibleFrontEnd = ClassRegistry::init('Order')->enumOptions('isVisibleFrontEnd');
        $isVisibleBackOffice = ClassRegistry::init('Order')->enumOptions('isVisibleBackOffice');
        $this->set(compact('nota_evidenza', 'isVisibleFrontEnd', 'isVisibleBackOffice'));

        /*
         * ctrl configurazione Organization
         */
        if ($this->user->organization['Organization']['hasStoreroom'] == 'Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y') {
            $isToStoreroom = ClassRegistry::init('Delivery')->enumOptions('isToStoreroom');
            $this->set(compact('isToStoreroom'));
        }
    }

    public function admin_edit() {

        $this->Delivery->id = $this->delivery_id;
        if (!$this->Delivery->exists($this->Delivery->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * setting fields
         */
        if ($this->request->is('post') || $this->request->is('put')) {
            $nota_evidenza = $this->request->data['Delivery']['nota_evidenza'];
        } else {
            $nota_evidenza = '';
        }
        $this->set('nota_evidenzaDefault', $nota_evidenza);


        if ($this->request->is('post') || $this->request->is('put')) {

            $this->request->data['Delivery']['organization_id'] = $this->user->organization['Organization']['id'];
            $this->request->data['Delivery']['data'] = $this->request->data['Delivery']['data_db'];

            if ($this->user->organization['Organization']['hasStoreroom'] == 'N' || $this->user->organization['Organization']['hasStoreroomFrontEnd'] == 'N') {
                $this->request->data['Delivery']['isToStoreroom'] = 'N';
                $this->request->data['Delivery']['isToStoreroomPay'] = 'N';
            }
            if ($this->user->organization['Organization']['hasVisibility'] == 'N') {
                $this->request->data['Delivery']['isVisibleFrontEnd'] = 'Y';
                $this->request->data['Delivery']['isVisibleBackOffice'] = 'Y';
            }

            if ($this->request->data['Delivery']['isToStoreroom'] == 'N')
                $this->request->data['Delivery']['isToStoreroomPay'] = 'N';

            /*
             * ctrl se la consegna era isToStoreroom e ora non +
             */
            if ($this->user->organization['Organization']['hasStoreroom'] == 'Y') {
                if ($this->request->data['Delivery']['isToStoreroom_old'] == 'Y' && $this->request->data['Delivery']['isToStoreroom'] == 'N') {
                    App::import('Model', 'Storeroom');
                    $Storeroom = new Storeroom;

                    $Storeroom->riportaArticoliAcquistatiInDispensa($this->user, $this->delivery_id);
                }
            }

			$msg_errors = $this->Delivery->getMessageErrorsToValidate($this->Delivery, $this->request->data);
			if(!empty($msg_errors)) {
				$this->Session->setFlash(__('The delivery could not be saved. Please, try again.').'<br />'.$msg_errors);
			}
			else {
			
	            $this->Delivery->create();
	            if ($this->Delivery->save($this->request->data)) {
	
	                /*
	                 * aggiorno lo stato degli ordini
	                 * */
	                $utilsCrons = new UtilsCrons(new View(null));
	                $utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
	
	                $this->Session->setFlash(__('The delivery has been saved'));
	                $this->myRedirect(['action' => 'index']);
	            } else
	                $this->Session->setFlash(__('The delivery could not be saved. Please, try again.'));
	        }
        }

        $options = [];
        $options['conditions'] = array('Delivery.id' => $this->delivery_id,
            'Delivery.organization_id' => $this->user->organization['Organization']['id'],
            'Delivery.sys' => 'N'
        );
        $options['recursive'] = -1;
        $this->request->data = $this->Delivery->find('first', $options);
        if (empty($this->request->data)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $nota_evidenza = ClassRegistry::init('Delivery')->enumOptions('nota_evidenza');
        $isVisibleFrontEnd = ClassRegistry::init('Order')->enumOptions('isVisibleFrontEnd');
        $isVisibleBackOffice = ClassRegistry::init('Order')->enumOptions('isVisibleBackOffice');

        $this->set(compact('nota_evidenza', 'isVisibleFrontEnd', 'isVisibleBackOffice', 'stato_elaborazione'));

        if ($this->user->organization['Organization']['hasStoreroom'] == 'Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y') {

            $isToStoreroom = ClassRegistry::init('Delivery')->enumOptions('isToStoreroom');
            $isToStoreroomPay = ClassRegistry::init('Delivery')->enumOptions('isToStoreroomPay');

            $this->set(compact('isToStoreroom', 'isToStoreroomPay'));
        }
    }

    /*
     * deliveries_Trigger
     * 		orders 
     * 		storerooms 
     * 		summary_orders 
     */

    public function admin_delete() {

        $this->Delivery->id = $this->delivery_id;
        if (!$this->Delivery->exists($this->Delivery->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {

            /*
             * riporto gli eventuali articoli acquistati in dispensa 
             */
            if ($this->user->organization['Organization']['hasStoreroom'] == 'Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd'] == 'Y') {
                App::import('Model', 'Storeroom');
                $Storeroom = new Storeroom;

                $Storeroom->riportaArticoliAcquistatiInDispensa($this->user, $this->delivery_id);
            }

            if ($this->Delivery->delete())
                $this->Session->setFlash(__('Delete Delivery'));
            else
                $this->Session->setFlash(__('Delivery was not deleted'));

            setcookie('delivery_id', '', time() - 42000, Configure::read('App.server'));
            $this->Session->delete('delivery_id');

            $this->myRedirect(['action' => 'index']);
        }

        $options = [];
        $options['conditions'] = ['Delivery.organization_id' => $this->user->organization['Organization']['id'],
									'Delivery.id' => $this->delivery_id,
									'Delivery.sys' => 'N'];
        $options['recursive'] = 1;
        $results = $this->Delivery->find('first', $options);
        if (empty($results)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * dispensa associata alla consegna
         */
        App::import('Model', 'Storeroom');
        $Storeroom = new Storeroom;

		$options = [];
		$options['conditions'] = ['Storeroom.organization_id' => (int) $this->user->organization['Organization']['id'],
									'Storeroom.delivery_id' => (int) $this->delivery_id,
									'Storeroom.stato' => 'Y'];
		$options['recursive'] = -1;
		
        $totStorerooms = $Storeroom->find('count', $options);
        $results['totStorerooms'] = $totStorerooms;

        $this->set(compact('results'));
    }

    public function admin_copy() {
        $this->Delivery->id = $this->delivery_id;
        if (!$this->Delivery->exists($this->Delivery->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $options = [];
        $options['conditions'] = array('Delivery.organization_id' => $this->user->organization['Organization']['id'],
            'Delivery.id' => $this->delivery_id,
            'Delivery.sys' => 'N'
        );
        $options['recursive'] = 1;
        $results = $this->Delivery->find('first', $options);
        if (empty($results)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $results['Delivery']['id'] = null;
        if ($this->user->organization['Organization']['hasVisibility'] == 'Y') {
            $results['Delivery']['isVisibleFrontEnd'] = 'N';
            $results['Delivery']['isVisibleBackOffice'] = 'N';
            $results['Delivery']['isToStoreroomPay'] = 'N';
            $results['Delivery']['stato_elaborazione'] = 'OPEN';
        } else {
            $results['Delivery']['isVisibleFrontEnd'] = 'Y';
            $results['Delivery']['isVisibleBackOffice'] = 'Y';
            $results['Delivery']['isToStoreroomPay'] = 'N';
            $results['Delivery']['stato_elaborazione'] = 'OPEN';
        }

        $this->Delivery->create();
        if ($this->Delivery->save($results['Delivery'], array('validate' => false))) {
            $this->Session->setFlash(__('The delivery has been copied'));
            $this->delivery_id = $this->Delivery->getLastInsertID();
            $this->myRedirect(Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Deliveries&action=edit&delivery_id=' . $this->delivery_id);
        } else {
            $this->Session->setFlash(__('The delivery could not be copied. Please, try again.'));
            $this->myRedirect(['action' => 'index']);
        }
    }

    public function admin_calendar_view() {

        $this->Delivery->id = $this->delivery_id;
        if (!$this->Delivery->exists($this->Delivery->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $urlBase = Configure::read('App.server') . '/administrator/index.php?option=com_cake&';

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $this->delivery_id),
            'Order' => array('Order.state_code != ' => 'CREATE-INCOMPLETE'),
            'SuppliersOrganization' => array('SuppliersOrganization.stato' => 'Y'));

        $orderBy = array('Order' => 'Order.data_inizio asc');

        $options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
            'suppliers' => true, 'referents' => true);

        $options += array('articlesOrdersInOrder' => false);  // NON estraggo gli articoli dell'ordine

        $results = $this->Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);

        if (!$this->isSuperReferente()) {
            App::import('Model', 'Order');
            $Order = new Order;
        }

        $tmp = "";
        if (isset($results['Delivery'][0]['Order']))
            foreach ($results['Delivery'][0]['Order'] as $result) {

                /* ctrl ACL 
                 * per ogni ordine per gestire il link alla home dell'ordine
                 * */
                if (!$this->isSuperReferente()) {
                    if (!$Order->aclReferenteSupplierOrganization($this->user, $result['Order']['id']))
                        $orderAcl = false;
                    else
                        $orderAcl = true;
                } else
                    $orderAcl = true;
                /* ctrl ACL */


                $data_inizio = explode("-", $result['Order']['data_inizio']);
                $data_fine = explode("-", $result['Order']['data_fine']);

                if ($data_inizio[1][0] == '0')
                    $data_inizio_mm = $data_inizio[1][1]; // elimino l'eventuale 0 iniziale
                else
                    $data_inizio_mm = $data_inizio[1];
                $data_inizio_mm = ($data_inizio_mm - 1);

                if ($data_inizio[2][0] == '0')
                    $data_inizio_gg = $data_inizio[2][1];
                else
                    $data_inizio_gg = $data_inizio[2];
                $data_inizio_gg = ($data_inizio_gg - 1);

                if ($data_fine[1][0] == '0')
                    $data_fine_mm = $data_fine[1][1];
                else
                    $data_fine_mm = $data_fine[1];
                $data_fine_mm = ($data_fine_mm - 1);

                if ($data_fine[2][0] == '0')
                    $data_fine_gg = $data_fine[2][1];
                else
                    $data_fine_gg = $data_fine[1];
                $data_fine_gg = ($data_fine_gg - 1);

                $tmp .= "\r\n";
                $tmp .= "{";
                $tmp .= "\r\n";
                $tmp .= "title: \"" . $this->prepareJs($result['SuppliersOrganization']['name']);
                if (!empty($result['SuppliersOrganization']['descrizione']))
                    $tmp .= '/' . $this->prepareJs($result['SuppliersOrganization']['descrizione']);
                $tmp .= "\",";
                $tmp .= "\r\n";
                $tmp .= "start: new Date(" . $data_inizio[0] . "," . $data_inizio_mm . "," . $data_inizio_gg . "),";
                $tmp .= "\r\n";
                $tmp .= "end: new Date(" . $data_fine[0] . "," . $data_fine_mm . "," . $data_fine_gg . "),";
                $tmp .= "\r\n";
                $tmp .= "allDay: true,";  // se false indica i gg
                $tmp .= "\r\n";
                if ($orderAcl)
                    $tmp .= "url: '" . $urlBase . "controller=Orders&action=home&delivery_id=" . $result['Order']['delivery_id'] . "&order_id=" . $result['Order']['id'] . "'";
                $tmp .= "\r\n";
                $tmp .= "},";
            } // end foreach

        $this->set(compact('results'));
        $this->set('jsCalendar', $tmp);
    }

    private function prepareJs($str) {

        $str = str_replace("'", "\'", $str);
        $str = str_replace("<br />", " ", $str);
        $str = str_replace("<br/>", " ", $str);
        $str = str_replace("<br>", " ", $str);
        $str = str_replace("\r", " ", $str);
        $str = str_replace("\n", " ", $str);

        return $str;
    }

    /*
     * call front-end da delivery/tabs con ajax
     */

    public function calendar_view($delivery_id) {

        $this->Delivery->id = $delivery_id;
        if (!$this->Delivery->exists($this->Delivery->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $delivery_id),
            'Order' => array('Order.state_code != ' => 'CREATE-INCOMPLETE'),
            'SuppliersOrganization' => array('SuppliersOrganization.stato' => 'Y'));

        $orderBy = array('Order' => 'Order.data_inizio asc, Order.data_fine asc');

        $options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
            'suppliers' => true, 'referents' => true);

        $options += array('articlesOrdersInOrder' => false);  // NON estraggo gli articoli dell'ordine

        $results = $this->Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);

        $tmp = "";
        if (isset($results['Delivery'][0]['Order']))
            foreach ($results['Delivery'][0]['Order'] as $numResult => $result) {

                if ((($numResult + 1) % 4) == 0)
                    $eventColorNum = '0';
                else
                if ((($numResult + 1) % 3) == 0)
                    $eventColorNum = '1';
                else
                if ((($numResult + 1) % 2) == 0)
                    $eventColorNum = '2';
                else
                    $eventColorNum = '3';

                $SuppliersOrganizationName = $this->_pulisciStringa($result['SuppliersOrganization']['name']);
                $SuppliersOrganizationDescrizione = $this->_pulisciStringa($result['SuppliersOrganization']['descrizione']);

                $data_inizio = explode("-", $result['Order']['data_inizio']);
                $data_fine = explode("-", $result['Order']['data_fine']);

                if ($data_inizio[1][0] == '0')
                    $data_inizio_mm = $data_inizio[1][1]; // elimino l'eventuale 0 iniziale
                else
                    $data_inizio_mm = $data_inizio[1];
                $data_inizio_mm = ($data_inizio_mm - 1);

                if ($data_inizio[2][0] == '0')
                    $data_inizio_gg = $data_inizio[2][1];
                else
                    $data_inizio_gg = $data_inizio[2];
                $data_inizio_gg = ($data_inizio_gg);

                if ($data_fine[1][0] == '0')
                    $data_fine_mm = $data_fine[1][1];
                else
                    $data_fine_mm = $data_fine[1];
                $data_fine_mm = ($data_fine_mm - 1);

                if ($data_fine[2][0] == '0')
                    $data_fine_gg = $data_fine[2][1];
                else
                    $data_fine_gg = $data_fine[2];
                $data_fine_gg = ($data_fine_gg);

                $tmp .= "\r\n";
                $tmp .= "{";
                $tmp .= "\r\n";
                $tmp .= "title: \"" . $this->prepareJs($SuppliersOrganizationName);
                if (!empty($SuppliersOrganizationDescrizione))
                    $tmp .= '/' . $this->prepareJs($SuppliersOrganizationDescrizione);
                $tmp .= "\",";
                $tmp .= "\r\n";
                $tmp .= "start: new Date(" . $data_inizio[0] . "," . $data_inizio_mm . "," . $data_inizio_gg . "),";
                $tmp .= "\r\n";
                $tmp .= "end: new Date(" . $data_fine[0] . "," . $data_fine_mm . "," . $data_fine_gg . "),";
                $tmp .= "\r\n";
                $tmp .= "allDay: true,";  // se false indica i gg
                $tmp .= "\r\n";
                $tmp .= "className: 'eventColor" . $eventColorNum . "',";
                $tmp .= "\r\n";
                $tmp .= "},";
            } // end foreach

        $this->set(compact('results'));
        $this->set('jsCalendar', $tmp);

        $this->layout = 'ajax';
    }

    private function _pulisciStringa($str) {

        if (!empty($str)) {
            $str = str_replace("'", "\'", $str);
        }

        return $str;
    }

    /*
     * setto la visibility delle consegne a Y se l'organizzazione ha organizationHasVisibility = 'N'
     */

    private function _ctrl_data_visibility() {

        if ($this->user->organization['Organization']['hasVisibility'] == 'N') {
            $sql = "UPDATE " . Configure::read('DB.prefix') . "deliveries
   						SET isVisibleFrontEnd = 'Y', isVisibleBackOffice = 'Y'
						WHERE organization_id = " . (int) $this->user->organization['Organization']['id'];
            $result = $this->Delivery->query($sql);
        }
    }

    private function _getUserPreview($E, $O, $R, $D, $debug = false) {

        if ($debug) {
            debug('E => ' . $E);
            debug('O => organization_id (tolgo i primi 2 numeri e poi organization_id) ' . $O);
            debug('R => username ' . $R);
            debug('D => delivery_id (tolgo i primi 2 numeri e poi delivery_id) ' . $D);
        }

        if (empty($E) || empty($O) || empty($R) || empty($D)) {
            if ($debug) {
                debug('ERROR empty(E) || empty(O) || empty(R) || empty($D) ');
                exit;
            } else
                $this->myRedirect(Configure::read('routes_msg_frontend_cart_preview'));
        }

        App::import('Model', 'User');
        $User = new User;

        $organization_id = substr($O, 2, strlen($O));
        $username = $User->getUsernameToUsernameCrypted($R);
        $delivery_id = substr($D, 2, strlen($D));

        if ($debug) {
            echo '<br />organization_id ' . $organization_id;
            echo '<br />username ' . $username;
            echo '<br />delivery_id ' . $delivery_id;
        }

        if (empty($username) || empty($organization_id) || !is_numeric($organization_id) || empty($delivery_id) || !is_numeric($delivery_id)) {
            if ($debug) {
                echo '<br />ERROR empty(username) || empty(organization_id) || !is_numeric(organization_id) || empty($delivery_id) || !is_numeric($delivery_id) ';
                exit;
            } else
                $this->myRedirect(Configure::read('routes_msg_frontend_cart_preview'));
        }

        $options = [];
        $options['conditions'] = ['User.organization_id' => $organization_id,
            'User.username' => $username,
            'User.block' => 0];
        $options['fields'] = ['id', 'organization_id'];
        $options['recursive'] = -1;
        $results = $User->find('first', $options);

		self::d($results, $debug);
		
        if (empty($results)) {
            if ($debug) {
                echo '<br />ERROR empty(results) ';
                exit;
            } else
                $this->myRedirect(Configure::read('routes_msg_frontend_cart_preview'));
        }

        App::import('Model', 'Organization');
        $Organization = new Organization;

        $conditions = ['Organization.id' => (int) $organization_id];
        $organization = $Organization->find('first', ['conditions' => $conditions, 'recursive' => -1]);

        $userPreview = new UserPreview();
        $userPreview->user_id = $results['User']['id'];
        $userPreview->delivery_id = $delivery_id;
        $userPreview->organization_id = $results['User']['organization_id'];
        $userPreview->organization = $organization;

		self::d($userPreview, $debug);

        $this->set('E', $E);
        $this->set('O', $O);
        $this->set('R', $R);
        $this->set('D', $D);

        return $userPreview;
    }

}

class UserPreview {

    public $organization;

}
