<?php
App::uses('AppController', 'Controller');

class AjaxController extends AppController {

    public $components = ['ActionsDesOrder'];
    
    public function beforeFilter() {
        $this->ctrlHttpReferer();

        parent::beforeFilter();
    }

    /*
     * key = $request_payment_id_$user_id
     */

    public function admin_view_request_payment_referent_to_users($key) {

        $debug = false;

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($request_payment_id, $user_id) = explode('_', $key);
        $doc_formato = 'PREVIEW';

        self::d('request_payment_id ' . $request_payment_id, $debug);
        self::d('user_id ' . $user_id, $debug);

        if ($user_id == null || $request_payment_id == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'RequestPayment');
        $RequestPayment = new RequestPayment;

        /*
         * num della richiesta pagamento
         */
        $options = [];
        $options['conditions'] = ['RequestPayment.organization_id' => (int) $this->user->organization['Organization']['id'],
								  'RequestPayment.id' => $request_payment_id];
        $options['recursive'] = -1;
        $options['fields'] = ['RequestPayment.num'];
        $results = $RequestPayment->find('first', $options);
        $request_payment_num = $results['RequestPayment']['num'];
        $this->set('request_payment_num', $request_payment_num);

        $results = $RequestPayment->userRequestPayment($this->user, $user_id, $request_payment_id, $doc_formato, $debug);
        $this->set(compact('results'));

        $params = ['request_payment_id' => $request_payment_id];
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'request_payment', $params, $user_target = 'TESORIERE'));
        $this->set('organization_id', $this->user->organization['Organization']['id']);

        /*
         * dati organization per dati pagamento
         */
        App::import('Model', 'Organization');
        $Organization = new Organization;

        $options = [];
        $options['conditions'] = ['Organization.id' => (int) $this->user->organization['Organization']['id']];
        $options['recursive'] = -1;
        $organizationResults = $Organization->find('first', $options);
        $this->set('organizationResults', $organizationResults);

        /*
         * dati user per intestazione dati pagamento
         */
        App::import('Model', 'User');
        $User = new User;

        $options = [];
        $options['conditions'] = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
								  'User.id' => $user_id];
        $options['recursive'] = -1;
        $userResults = $User->find('first', $options);
        $this->set('userResults', $userResults);

        $this->layout = 'ajax';
        $this->render('/ExportDocs/user_request_payment');
    }

    /*
     * id uno user estrae 
     * 		tutte le richieste di pagamento 
     * 		la cassa
     */

    public function admin_view_user_block($user_id) {

        $debug = false;

        self::d('user_id ' . $user_id, $debug);
		
        if ($user_id == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery'] == 'ON-POST') {

            App::import('Model', 'SummaryPayment');
            $SummaryPayment = new SummaryPayment;

            $options = [];
            $options['conditions'] = ['SummaryPayment.organization_id' => (int) $this->user->organization['Organization']['id'],
                'SummaryPayment.user_id' => $user_id];
            $options['recursive'] = 1;
            $options['order'] = ['RequestPayment.num'];
            $results = $SummaryPayment->find('all', $options);
            $this->set(compact('results'));
            
			self::d($results, false);
        }

        /*
         * dati cassa per l'utente
         */
        App::import('Model', 'Cash');
        $Cash = new Cash;

        $options = [];
        $options['conditions'] = ['Cash.organization_id' => $this->user->organization['Organization']['id'],
								  'Cash.user_id' => $user_id];
        $options['recursive'] = -1;
        $cashResults = $Cash->find('first', $options);
        $this->set('cashResults', $cashResults);



        $this->layout = 'ajax';
        $this->render('/Ajax/view_user_block');
    }

    /*
     * function call /View/model/admin_index.ctp 
     * chiamata ajax generata in js/indexRows.js
     * esempio /administrator/index.php?option=com_cake&controller=Ajax&action=view_users&id=646&format=notmpl
     * view /View/Ajax/view_articles.ctp
     */

    public function admin_view_deliveries($delivery_id = 0) {

        $this->_view_deliveries($delivery_id);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_deliveries');
    }

    public function admin_view_deliveries_small($delivery_id = 0) {

        $this->_view_deliveries($delivery_id);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_deliveries_small');
    }

    private function _view_deliveries($delivery_id) {
        if (empty($delivery_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * estraggo i dati della consegna
         */
        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $Delivery->id = $delivery_id;
        if (!$Delivery->exists($Delivery->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $deliveryResults = $Delivery->read($delivery_id, $this->user->organization['Organization']['id']);
        if ($deliveryResults['Delivery']['isVisibleBackOffice'] == 'Y')
            $this->set('deliveryResults', $deliveryResults['Delivery']);
        else
            $this->set('deliveryResults', null);

        /*
         * estraggo gli ordini associati alla consegna
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $Order->unbindModel(['belongsTo' => ['Delivery']]);
        $options = [];
        $options['conditions'] = ['Order.delivery_id' => $delivery_id,
								'Order.organization_id' => (int) $this->user->organization['Organization']['id'],
								'Order.isVisibleBackOffice' => 'Y',
								'Order.state_code !=' => 'CREATE-INCOMPLETE'];
        $options['recursive'] = 0;
        $options['order'] = ['Order.data_inizio', 'Order.data_fine'];
        $results = $Order->find('all', $options);

        /* ctrl ACL
         * per ogni ordine per gestire il link alla home dell'ordine
         */
        App::import('Model', 'Supplier');
        foreach ($results as $numResult => $result) {


            /*
             * Suppliers per l'immagine
             * */
            $Supplier = new Supplier;

            $options = [];
            $options['conditions'] = ['Supplier.id' => $result['SuppliersOrganization']['supplier_id']];
            $options['fields'] = ['Supplier.img1'];
            $options['recursive'] = -1;
            $SupplierResults = $Supplier->find('first', $options);
            if (!empty($SupplierResults))
                $results[$numResult]['Supplier']['img1'] = $SupplierResults['Supplier']['img1'];


            if (!$this->isSuperReferente()) {
                if (!$Order->aclReferenteSupplierOrganization($this->user, $result['Order']['id']))
                    $orderAcl = false;
                else
                    $orderAcl = true;
            } else
                $orderAcl = true;

            $results[$numResult]['Order']['acl'] = $orderAcl;
        }

        $this->set(compact('results'));
    }

    /*
     * ottengo i produttori del referente
     */

    public function admin_view_suppliers_organizations_referents($user_id = 0) {

        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
        $results = $SuppliersOrganizationsReferent->getSuppliersOrganizationByReferent($this->user, $user_id);
        $this->set(compact('results'));

        $SuppliersOrganizationsReferent->getSuppliersOrganizationIdsByReferent($this->user, $user_id);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_suppliers_organizations_referents');
    }

	/*
	 * call root TestLifeCycles::index
	 * dettaglio  di un ordine 
	 * k_summary_order: dati aggragati per gasista per un ordine
     * k_summary_payments: dati aggregati per gasista per richiesta di pagamento (può includere ordini + dispensa + voci di spesa)
	 */
    public function admin_view_summary_orders($order_id) {

        if (empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Order');
        $Order = new Order;
		
        App::import('Model', 'RequestPayment');
        $RequestPayment = new RequestPayment;
		
        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;
		$SummaryOrder->unbindModel(['belongsTo' => ['Order', 'Delivery']]);

        App::import('Model', 'SummaryPayment');
        $SummaryPayment = new SummaryPayment;
		$SummaryPayment->unbindModel(['belongsTo' => ['RequestPayment']]);
		
        App::import('Model', 'SummaryOrderTrasport');
        $SummaryOrderTrasport = new SummaryOrderTrasport;

        App::import('Model', 'SummaryOrderCostMore');
        $SummaryOrderCostMore = new SummaryOrderCostMore;
		
        App::import('Model', 'SummaryOrderCostLess');
        $SummaryOrderCostLess = new SummaryOrderCostLess;
		
        $Order->id = $order_id;
        if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $options = [];
        $options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $order_id];	   
        $options['recursive'] = -1;
        $orderResults = $Order->find('first', $options);
		self::d($orderResults, false);
		$this->set('orderResults', $orderResults);
		
        $options = [];
        $options['conditions'] = ['SummaryOrder.organization_id' => $this->user->organization['Organization']['id'],
								  'SummaryOrder.order_id' => $order_id];
		$options['order'] = Configure::read('orderUser');			   
        $options['recursive'] = 0;
        $results = $SummaryOrder->find('all', $options);
		
		/*
		 * estraggo i dettagli di una richiesta di pagamento
		 * 	- ordini associati
		 *  - voci di spesa generica
		 *  - dispensa
		 */
		$requestPaymentResults = [];
		if($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery']=='ON-POST') {
			switch ($orderResults['Order']['state_code']) {
				case 'TO-PAYMENT': 
				case 'USER-PAID': 
				case 'SUPPLIER-PAID': 
				case 'WAIT-REQUEST-PAYMENT-CLOSE': 
					$request_payment_id = $RequestPayment->getRequestPaymentIdByOrderId($this->user, $order_id);
					$requestPaymentResults = $RequestPayment->getAllDetails($this->user, $request_payment_id);
					self::d($requestPaymentResults, false);
				break;
			}
		} // end if($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery']=='ON-POST')
			
		$user_ids = [];
		foreach($results as $numResult => $result) {
			/*
			 * trasport
			 */
			if($orderResults['Order']['hasTrasport']=='Y') {
				$options = [];
				$options['conditions'] = ['SummaryOrderTrasport.organization_id' => $this->user->organization['Organization']['id'],
										   'SummaryOrderTrasport.order_id' => $result['SummaryOrder']['order_id'],
										   'SummaryOrderTrasport.user_id' => $result['SummaryOrder']['user_id']];
				$options['recursive'] = -1;
				$summaryOrderTrasportResults = $SummaryOrderTrasport->find('first', $options);
				self::d($summaryOrderTrasportResults, false);
				if(!empty($summaryOrderTrasportResults)) {
					$results[$numResult]['SummaryOrderTrasport'] = $summaryOrderTrasportResults['SummaryOrderTrasport'];
				}					
			}

			/*
			 * costMore
			 */			
			if($orderResults['Order']['hasCostMore']=='Y') {
				$options = [];
				$options['conditions'] = ['SummaryOrderCostMore.organization_id' => $this->user->organization['Organization']['id'],
										   'SummaryOrderCostMore.order_id' => $result['SummaryOrder']['order_id'],
										   'SummaryOrderCostMore.user_id' => $result['SummaryOrder']['user_id']];
				$options['recursive'] = -1;
				$summaryOrderCostMoreResults = $SummaryOrderCostMore->find('first', $options);
				self::d($summaryOrderCostMoreResults, false);
				if(!empty($summaryOrderCostMoreResults)) {
					$results[$numResult]['SummaryOrderCostMore'] = $summaryOrderCostMoreResults['SummaryOrderCostMore'];
				}					
			}

			/*
			 * costLess
			 */			
			if($orderResults['Order']['hasCostLess']=='Y') {
				$options = [];
				$options['conditions'] = ['SummaryOrderCostLess.organization_id' => $this->user->organization['Organization']['id'],
										   'SummaryOrderCostLess.order_id' => $result['SummaryOrder']['order_id'],
										   'SummaryOrderCostLess.user_id' => $result['SummaryOrder']['user_id']];
				$options['recursive'] = -1;
				$summaryOrderCostLessResults = $SummaryOrderCostLess->find('first', $options);
				self::d($summaryOrderCostLessResults, false);
				if(!empty($summaryOrderCostLessResults)) {
					$results[$numResult]['SummaryOrderCostLess'] = $summaryOrderCostLessResults['SummaryOrderCostLess'];
				}					
			}
			
			if($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery']=='ON-POST') {
				
				switch ($orderResults['Order']['state_code']) {
					case 'TO-PAYMENT': 
					case 'USER-PAID': 
					case 'SUPPLIER-PAID': 
					case 'WAIT-REQUEST-PAYMENT-CLOSE':
						$options = [];
						$options['conditions'] = ['SummaryPayment.organization_id' => $this->user->organization['Organization']['id'],
												   'SummaryPayment.user_id' => $result['SummaryOrder']['user_id'],
												   'SummaryPayment.request_payment_id' => $request_payment_id];
						$options['recursive'] = 0;
						$summaryPaymentResults = $SummaryPayment->find('first', $options);
						self::d($summaryPaymentResults, false);
						if(!empty($summaryPaymentResults)) {
							$results[$numResult]['SummaryPayment'] = $summaryPaymentResults;
						}
						
						array_push($user_ids, $result['SummaryOrder']['user_id']);
						
						 
						/*
						 * estraggo altri gasisti della RequestPayment
						 */
						if(!empty($user_ids)) {

							$options = [];	
							$options['conditions'] = ['SummaryPayment.organization_id' => $this->user->organization['Organization']['id'],
														   'SummaryPayment.request_payment_id' => $request_payment_id,
														   ['NOT' => ['SummaryPayment.user_id' => $user_ids]]]; 
							$options['recursive'] = 0;
							$summaryPaymentNotSummaryOrderResults = $SummaryPayment->find('all', $options);
							self::d($summaryPaymentNotSummaryOrderResults, false);
							if(!empty($summaryPaymentNotSummaryOrderResults)) {
								$results[$numResult]['SummaryPaymentNotSummaryOrderResults'] = $summaryPaymentNotSummaryOrderResults;
							}				 
						}
				}
			} // end if($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery']=='ON-POST')
				
		} // loop foreach($results as $numResult => $result) 
		
		self::d($results, false);
        $this->set(compact('results'));		
		$this->set('requestPaymentResults', $requestPaymentResults);
		
        $this->layout = 'ajax';
        $this->render('/Ajax/view_summary_orders');
	}
	
    public function admin_view_order_details($order_id) {

        if (empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $order_id;
        if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
        $options = [];
        $options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $order_id];
        $options['recursive'] = -1;
        $results = $Order->find('first', $options);
        $this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/view_order_details');
	}
		
    public function admin_view_orders($order_id) {

        if (empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'OrderLifeCycle');
        $OrderLifeCycle = new OrderLifeCycle;
		
        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $order_id;
        if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * estraggo 
         * dati ordine 
         * gli articoli associati all'ordine
         */
        $Order->hasAndBelongsToMany['Article'] = ['className' => 'Article',
												'joinTable' => 'articles_orders',
												'foreignKey' => 'order_id',
												'associationForeignKey' => 'article_id',
												'unique' => 'keepExisting',
												'conditions' => 'Article.organization_id = ArticlesOrder.article_organization_id AND Article.stato = \'Y\' AND ArticlesOrder.stato != \'N\' AND ArticlesOrder.organization_id = ' . $this->user->organization['Organization']['id'],
												'with' => 'ArticlesOrder'];

        $options = [];
        $options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $order_id];
        $options['recursive'] = 1;
        $results = $Order->find('first', $options);
        $this->set(compact('results'));

        /*
         * creo il link articlesOrder/admin_index
		 * No se e' una promozione (prod_gas_promotion_id)
         */
        if ($results['Delivery']['isVisibleBackOffice'] == 'N' || $results['Order']['isVisibleBackOffice'] == 'N' || $results['Order']['prod_gas_promotion_id']!=0) {
            $actionToEditOrder = [];
            $actionToEditArticle = [];
        } else {
            $actionToEditOrder = $OrderLifeCycle->actionToEditOrder($this->user, $results);
            $actionToEditArticle = $OrderLifeCycle->actionToEditArticle($this->user, $results);
        }
        $this->set('actionToEditOrder', $actionToEditOrder);
        $this->set('actionToEditArticle', $actionToEditArticle);

        /*
         * estraggo i referenti 
         */
        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

        $supplier_organization_id = $results['Order']['supplier_organization_id'];
        $conditions = ['User.block' => 0,
						'SuppliersOrganization.id' => $supplier_organization_id];
        $suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($this->user, $conditions);
        $this->set('suppliersOrganizationsReferent', $suppliersOrganizationsReferent);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_orders');
    }

    // call SuppliersOrganization::index
    public function admin_view_suppliers_organizations($id = null) {
        if (empty($id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $SuppliersOrganization->id = $id;
        if (!$SuppliersOrganization->exists($SuppliersOrganization->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * ordini associati al produttore
         */
        $options = [];
        $options['conditions'] =  ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								   'SuppliersOrganization.id' => $id];
        $options['order'] = ['Delivery.data', 'Order.data_inizio'];
        $options['recursive'] = 1;
        $results = $SuppliersOrganization->Order->find('all', $options);

        $this->set(compact('results'));
        $this->set('supplier_organization_id', $id);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_suppliers_organizations');
    }

    // call Supplier::add_index
	// call SuppliersOrganizations&action::add_index
    public function admin_view_suppliers($id = null) {
		
		$debug=false;
		
		$this->_view_suppliers($id, $debug);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_suppliers');
    }
	
    public function admin_view_supplier_details($id = null) {
		
		$debug=false;
		
		$this->_view_suppliers($id, $debug);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_supplier_details');
    }
	
   public function _view_suppliers($id = null, $debug=false) {
		
        if (empty($id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

		App::import('Model', 'SuppliersVote');

        App::import('Model', 'Organization');

        App::import('Model', 'SuppliersOrganization');

        App::import('Model', 'SuppliersOrganizationsReferent');
		
        $Supplier->id = $id;
        if (!$Supplier->exists()) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $options = [];
        $options['conditions'] = ['Supplier.id' => $id];
        $options['recursive'] = 1;
        $results = $Supplier->find('first', $options);
		self::d($results, $debug); 

        if (isset($results['SuppliersOrganization']))
            foreach ($results['SuppliersOrganization'] as $numResult => $suppliersOrganization) {

				$articleResults = [];
				
                /*
                 * dati  associati al produttore: Organization, Article, Referents
				 * se il produttore gestisce il listino (Supplier.owner_organization_id > 0) prendo i suoi articoli
                 */
                $SuppliersOrganization = new SuppliersOrganization;
                $SuppliersOrganization->unbindModel(['belongsTo' => ['Supplier', 'CategoriesSupplie']]);
                if($results['Supplier']['owner_organization_id']>0) {
					$SuppliersOrganization->unbindModel(['hasMany' => ['Article','Order']]);
				
					/*
					 * estraggo articoli del produttore
					 */
					if($numResult==0) {
	
						App::import('Model', 'Article');
						$Article = new Article;
		
						App::import('Model', 'ProdGasSupplier');
						$ProdGasSupplier = new ProdGasSupplier;
		
						$organization_id = $results['Supplier']['owner_organization_id'];
						$filters['ownerArticles'] = 'SUPPLIER';
						$prodGasSupplierResults = $ProdGasSupplier->getOrganizationSupplier($this->user, $organization_id, $filters, $debug);
						
						self::d($prodGasSupplierResults, $debug); 
						if(!empty($prodGasSupplierResults)) {
							$options = [];
							$options['conditions'] = ['Article.organization_id' => $prodGasSupplierResults['SuppliersOrganization']['organization_id'],
														'Article.supplier_organization_id' => $prodGasSupplierResults['SuppliersOrganization']['id'],
														'Article.stato' => 'Y'];
							$options['recursive'] = -1;
							$articleResults = $Article->find('all', $options);

							self::d($articleResults, $debug); 
						}
					}
				}
				else
					$SuppliersOrganization->unbindModel(['hasMany' => ['Order']]);
					
                $options = [];
                $options['conditions'] = ['SuppliersOrganization.organization_id' => $suppliersOrganization['organization_id'],
											'SuppliersOrganization.id' => $suppliersOrganization['id'],
											'Organization.stato' => 'Y',
											'Organization.type' => 'GAS'];
                $options['recursive'] = 1;
                $suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
				self::d($suppliersOrganizationResults, $debug); 
								
                /*
                 * per i test posso avere dati sporchi: articoli e produttori senza Organization
                 */
                if (empty($suppliersOrganizationResults))
                    unset($results['SuppliersOrganization'][$numResult]);
                else {			

					/*
					 * il listino e' del produttore, associo il suo listino
					 */
					if($results['Supplier']['owner_organization_id']>0 && $numResult==0) { 
						foreach($articleResults as $numResult2 => $articleResult)
						$suppliersOrganizationResults['Article'][] = $articleResult['Article'];
						
						self::d($suppliersOrganizationResults, $debug); 
					}
					
					$results['SuppliersOrganization'][$numResult] = $suppliersOrganizationResults;
				
					
					/*
					 * per ogni produttore faccio la media dei voti
					 */	
					$options = [];
					$options['conditions'] = ['SuppliersVote.organization_id' => $suppliersOrganization['organization_id'],
											   'SuppliersVote.supplier_id' => $results['Supplier']['id']];
					$options['recursive'] = -1;
					$SuppliersVote = new SuppliersVote;
					
					$suppliersVoteResults = $SuppliersVote->find('first', $options);
					$results['SuppliersOrganization'][$numResult]['SuppliersVote'] = $suppliersVoteResults['SuppliersVote'];					
				}
                    
            }
		
		self::d($results, $debug);
		
        $this->set(compact('results'));
        $this->set('supplier_id', $id);
    }	

    public function admin_view_articles($key) {
        $this->view_articles($key);
    }

    /*
     * dettaglio di acquisti di un articolo
     * 		ArticlesOrder.key = $order_id, $organization_id, $article_id
     */
    public function admin_view_article_carts($key) {

        if (empty($key)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        list($order_id, $article_organization_id, $article_id) = explode('_', $key);

        /*
         * ctrl che lo Article.stato = Y, se no non posso avere acquisti
         */
        App::import('Model', 'Article');
        $Article = new Article;

        $Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
        $Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
        $Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);

        $options = [];
        $options['conditions'] = ['Article.organization_id' => $article_organization_id, 'Article.id' => $article_id];
        $options['recursive'] = -1;
        $options['fields'] = ['Article.stato'];
        $results = $Article->find('first', $options);
        $article_stato = $results['Article']['stato'];
        $this->set(compact('article_stato'));

        if ($article_stato == 'Y') {
        
            App::import('Model', 'Delivery');
            $Delivery = new Delivery;

            $options = ['orders' => true, 'storerooms' => false, 'summaryOrders' => false,
						'articlesOrdersInOrderAndCartsAllUsers' => true, // estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
						'suppliers' => false, 'referents' => false];

            $conditions = ['Delivery' => ['Delivery.isVisibleBackOffice' => 'Y',
										 'Delivery.stato_elaborazione' => 'OPEN'],
							'Cart' => ['Cart.deleteToReferent' => 'N']];

            if (!empty($order_id))
                $conditions += ['ArticlesOrder' => ['ArticlesOrder.order_id' => $order_id,
													'ArticlesOrder.article_organization_id' => $article_organization_id,
													'ArticlesOrder.article_id' => $article_id]];
            else
                $conditions += ['ArticlesOrder' => ['ArticlesOrder.article_organization_id' => $article_organization_id,
								'ArticlesOrder.article_id' => $article_id]];

            $orderBy = ['User' => Configure::read('orderUser')];

            $results = $Delivery->getDataTabs($this->user, $conditions, $options, $orderBy);
            $this->set(compact('results'));

            /*
             * ctrl configurazione Organization
             */
            $storeroomResults = [];
            $organization = $this->user->get('Organization');
            if ($this->user->organization['Organization']['hasStoreroom'] == 'Y') {

                /*
                 * Articolo in DISPENSA acquistato aggregati per delivery
                 * */
                App::import('Model', 'Storeroom');
                $Storeroom = new Storeroom;

                $conditions = ['Delivery.id' => '> 0', // se dispensa e' 0
								'Articles.organization_id' => $article_organization_id,
								'Article.id' => $article_id];
                $orderBy = ['Delivery' => 'Delivery.data,Delivery.id, ' . Configure::read('orderUser') . ', User.id'];

                $storeroomDeliveryResults = $Storeroom->getArticlesToStoreroom($this->user, $conditions, $orderBy);

                /*
                 * Articolo in DISPENSA non ancora acquistato
                 * */
                App::import('Model', 'Storeroom');
                $Storeroom = new Storeroom;

                $conditions = ['Delivery.id' => '0', // se dispensa e' 0,
								'Articles.organization_id' => $article_organization_id,
								'Article.id' => $article_id];
                $orderBy = ['Delivery' => 'Delivery.data,Delivery.id, ' . Configure::read('orderUser') . ', User.id'];

                $storeroomResults = $Storeroom->getArticlesToStoreroom($this->user, $conditions, $orderBy);
            } // if($this->user->organization['Organization']['hasStoreroom']=='Y')

            $this->set(compact('storeroomResults', 'storeroomDeliveryResults'));
        
        } // end if($article_stato=='Y')

        $this->layout = 'ajax';
        $this->render('/Ajax/view_article_carts');
    }

    /*
     * dettaglio di acquisti di un articolo per tutti i GAS associati al ProdGas
     * 		ArticlesOrder.key = $order_id, $organization_id, $article_id
     */
    public function admin_view_prodgas_article_carts($key) {

		$debug = false;
		
        if (empty($key)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        list($order_id, $article_organization_id, $article_id) = explode('_', $key);

        /*
         * ctrl che lo Article.stato = Y, se no non posso avere acquisti
         */
        App::import('Model', 'Article');
        $Article = new Article;

        $Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
        $Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
        $Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);

        $options = [];
        $options['conditions'] = ['Article.organization_id' => $article_organization_id, 'Article.id' => $article_id];
        $options['recursive'] = -1;
        $options['fields'] = ['Article.stato'];
        $results = $Article->find('first', $options);
        $article_stato = $results['Article']['stato'];
        $this->set(compact('article_stato'));

        if ($article_stato == 'Y') {
        
	        App::import('Model', 'ProdGasArticle');
	        $ProdGasArticle = new ProdGasArticle;
			
			$articlesOrderResults = $ProdGasArticle->getGasOrganizationInCart($this->user, $article_organization_id, $article_id, $debug); 
			self::d($articlesOrderResults);

        } // end if($article_stato=='Y')

		$this->set('results', $articlesOrderResults);
			
        $this->layout = 'ajax';
        $this->render('/Ajax/view_prodgas_article_carts');
    }

    /*
     * dettaglio di acquisti di un articolo in un preciso ordine e consegna
     * 		in validation_carts
     * ArticlesOrder.key = $order_id, $article_organization_id, $article_id
     */
    public function admin_view_articles_order_carts($key) {

        $debug = false;
        
        $results = [];
        $desResults = [];
		$totAcquistiAltriGas = 0;

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($order_id, $article_organization_id, $article_id) = explode('_', $key);

        /*
         * D.E.S.
         */
        $isReferentDesAllGasDesSupplier = false;
        $des_order_id = 0;
        if ($this->user->organization['Organization']['hasDes'] == 'Y') {

            App::import('Model', 'DesOrdersOrganization');
            $DesOrdersOrganization = new DesOrdersOrganization();

            $desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $order_id, $debug);
            if (!empty($desOrdersOrganizationResults)) {

                $des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];

                /*
                 * ctrl ACL, gli acquisti degli altri GAS sono visibili solo al isReferentDesAllGasDesSupplier
                 */
                $isReferentDesAllGasDesSupplier = $this->ActionsDesOrder->isReferentDesAllGasDesSupplier($this->user, $des_order_id);
            }
        } // DES
        $this->set(compact('des_order_id', 'isReferentDesAllGasDesSupplier'));

        
        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder();
        if (!$ArticlesOrder->exists($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Cart');
		$Cart = new Cart();
		
        /*
         *  ctrl eventuali acquisti gia' fatti del proprio GAS
         */      
		$options = [];
		$options['conditions'] = ['Cart.organization_id' => $this->user->organization['Organization']['id'],
									'Cart.order_id' => $order_id,
									'Cart.article_organization_id' => $article_organization_id,
									'Cart.article_id' => $article_id,
									'Cart.deleteToReferent' => 'N'];
		$options['recursive'] = 1;
		$options['order'] = [Configure::read('orderUser')];
		$results = $Cart->find('all', $options);	                
			
		/*
		* se DES 
		*	se isReferentDesAllGasDesSupplier  => ctrl acquisti di TUTTI i GAS 
		*	se !isReferentDesAllGasDesSupplier => ctrl eventuali acquisti gia' fatti del proprio GAS + totla acquisti di TUTTI i GAS 
		*/
		
		if(!empty($des_order_id)) {
			/*
			 * estraggo acquisti di TUTTI i GAS
			 */
			$options = [];
			$options['conditions'] = ['DesOrdersOrganization.des_id' => $this->user->des_id,
									   'DesOrdersOrganization.des_order_id' => $des_order_id];
			$options['recursive'] = -1;								
			$desOrdersOrganizationResults = $DesOrdersOrganization->find('all', $options);
			
			self::d([$options, $desOrdersOrganizationResults], false);
				   
			$i=0;
			foreach($desOrdersOrganizationResults as $desOrdersOrganizationResult) {
			
				$Cart = new Cart();
				$options = [];
				$options['conditions'] = ['Cart.organization_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['organization_id'],
										'Cart.order_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['order_id'],
										'Cart.article_organization_id' => $article_organization_id,
										'Cart.article_id' => $article_id,
										'Cart.deleteToReferent' => 'N'];
	
				$options['order'] = '';
				$options['recursive'] = 1;
				$cartResults = $Cart->find('all', $options);
				
				self::d([$options['conditions'], $cartResults], false);
		   
				if(!empty($cartResults)) {
					$desResults[$i] = current($cartResults);
					$i++;
				}
				
			} // loop DesOrdersOrganizationResult
				
			if(!$isReferentDesAllGasDesSupplier) {  // se non ho ACL estraggo solo il totale
				$totAcquistiAltriGas = count($desResults);
			}
			else {
				$results = $desResults;
			}

        } // if(!empty($des_order_id))
         
		$this->set('totAcquistiAltriGas', $totAcquistiAltriGas);	 
        $this->set(compact('results'));
		self::d($results, false);
		
        $this->layout = 'ajax';
        $this->render('/Ajax/view_articles_order_carts');
    }

    /*
     * dettaglio di acquisti di un articolo in un preciso ordine e consegna
     * 		in validation_carts
     * ArticlesOrder.key = $organization_id $order_id, $article_organization_id, $article_id
     */
    public function admin_view_prodgas_articles_order_carts($key) {

        $debug = false;
        
        $results = [];
        $desResults = [];
		$totAcquistiAltriGas = 0;

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($organization_id, $order_id, $article_organization_id, $article_id) = explode('_', $key);

        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder();
        if (!$ArticlesOrder->exists($organization_id, $order_id, $article_organization_id, $article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Cart');
		$Cart = new Cart();
		
        /*
         *  ctrl eventuali acquisti gia' fatti del proprio GAS
         */      
		$options = [];
		$options['conditions'] = ['Cart.organization_id' => $organization_id,
									'Cart.order_id' => $order_id,
									'Cart.article_organization_id' => $article_organization_id,
									'Cart.article_id' => $article_id,
									'Cart.deleteToReferent' => 'N'];
		$options['recursive'] = 1;
		$options['order'] = [Configure::read('orderUser')];
		$results = $Cart->find('all', $options);	                
	 
        $this->set(compact('results'));
		self::d($results, false);
		
        $this->layout = 'ajax';
        $this->render('/Ajax/view_prodgas_articles_order_carts');
    }
	
    /*
     * dettaglio di acquisti di un articolo in un preciso ordine e consegna
     * codice = a admin_view_articles_order_carts 
     * BackupArticlesOrder.key = $order_id, $article_organization_id, $article_id
     */

    public function admin_view_backup_articles_order_carts($key) {

        $results = [];

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($order_id, $article_organization_id, $article_id) = explode('_', $key);

        App::import('Model', 'BackupArticlesOrder');
        $BackupArticlesOrder = new BackupArticlesOrder();

        App::import('Model', 'BackupCart');
        $BackupCart = new BackupCart();

        $options = [];
        $options['conditions'] = ['BackupCart.organization_id' => $this->user->organization['Organization']['id'],
								'BackupCart.order_id' => $order_id,
								'BackupCart.article_organization_id' => $article_organization_id,
								'BackupCart.article_id' => $article_id,
								'BackupCart.deleteToReferent' => 'N'];
        $options['recursive'] = 1;
        $options['order'] = [Configure::read('orderUser')];
        $results = $BackupCart->find('all', $options);
        $this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/view_backup_articles_order_carts');
    }

	/*
	 * history Cash da BO, vado per cash_id
	 */
    public function admin_view_cashes_histories($cash_id = 0) {

		$debug = false;
		
        if (empty($cash_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'CashesHistory');
        $CashesHistory = new CashesHistory;
        
        $options = [];
        $options['conditions'] = ['CashesHistory.organization_id' => (int) $this->user->organization['Organization']['id'],
							      'CashesHistory.cash_id' => $cash_id];
		$options['order'] =	['CashesHistory.id asc']; // per created no perche' e' sempre = 
		$options['recursive'] = 0;
        $results = $CashesHistory->find('all', $options);

		self::d($results, $debug);

		/*
		 * aggiungo ultimo movimento
		 */
        App::import('Model', 'Cash');
        $Cash = new Cash;
        
        $options = [];
        $options['conditions'] = ['Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
							      'Cash.id' => $cash_id];
		$options['recursive'] =	-1; 
        $cashResults = $Cash->find('first', $options);
		
		if(!empty($cashResults))	
			$results[(count($results))]['CashesHistory'] = $cashResults['Cash'];	

		$results = $CashesHistory->getListCashHistoryByUser($this->user, $results);
        $this->set(compact('results'));
		 
        $this->layout = 'ajax';
        $this->render('/Ajax/admin_view_cashes_histories');
    }
	
	/*
	 * history Cash da FE, vado per cash_id
	 */	
    public function view_cashes_histories() {

		$debug = false;
		
		$user_id = $this->user->id;

        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'CashesHistory');
        $CashesHistory = new CashesHistory;
        
        $options = [];
        $options['conditions'] = ['CashesHistory.organization_id' => (int) $this->user->organization['Organization']['id'],
							      'CashesHistory.user_id' => $user_id];
		$options['order'] =	['CashesHistory.id asc']; // per created no perche' e' sempre = 
        $results = $CashesHistory->find('all', $options);
		
		/*
		 * aggiungo ultimo movimento
		 */
        App::import('Model', 'Cash');
        $Cash = new Cash;
        
        $options = [];
        $options['conditions'] = ['Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
							      'Cash.user_id' => $user_id];
		$options['recursive'] =	-1; 
        $cashResults = $Cash->find('first', $options);
		
		if(!empty($cashResults))	
			$results[(count($results))]['CashesHistory'] = $cashResults['Cash'];	

		$results = $CashesHistory->getListCashHistoryByUser($this->user, $results);
        $this->set(compact('results'));
		 
        $this->layout = 'ajax';
        $this->render('/Ajax/view_cashes_histories');
    }
    
    public function view_articles($key) {
    
    	list($article_organization_id, $article_id) = explode('_', $key);
    
        if (empty($article_organization_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Article');
        $Article = new Article;
        if (!$Article->exists($article_organization_id, $article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $options = [];
        $options['conditions'] = ['Article.id' => $article_id, 'Article.organization_id' => $article_organization_id];
        $results = $Article->getArticlesDataAnagr($this->user, $options);
        $this->set(compact('results'));

		/*
		 * dati owner_articles listino REFERENT / DES / SUPPLIER 
		 */
		if($article_organization_id!=$this->user->organization['Organization']['id']) {
	        App::import('Model', 'Organization');
	        $Organization = new Organization;
	        
			$options = [];
			$options['conditions'] = ['Organization.id' => $article_organization_id];
			$options['recursive'] = -1;
			$organizationResults = $Organization->find('first', $options);	
			$this->set('organizationResults', $organizationResults);
		}
		 
        $this->layout = 'ajax';
        $this->render('/Ajax/view_articles');
    }

    public function admin_view_articles_order($key, $evidenzia = '') {
        $this->_view_articles_order($key, $evidenzia);
    }

    /*
     * Organization.type = 'GAS'
     * dettaglio di un articolo associato ad un ordine
     */

    public function view_articles_order($key, $evidenzia = '') {
        $this->_view_articles_order($key, $evidenzia);
    }

    public function view_articles_order_no_img($key) {
        $this->_view_articles_order($key);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_articles_order_no_img');
    }

    /*
     * Organization.type = 'PROD'
     * dettaglio di un articolo associato ad una consegna
     */

    public function admin_view_prod_deliveries_articles($key, $evidenzia = '') {
        $this->_view_prod_deliveries_articles($key, $evidenzia);
    }

    public function view_prod_deliveries_articles($key, $evidenzia = '') {
        $this->_view_prod_deliveries_articles($key, $evidenzia);
    }

    public function view_prod_deliveries_articles_no_img($key) {
        $this->_view_prod_deliveries_articles($key);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_prod_deliveries_articles_no_img');
    }

    /*
     * richiamo /ExportDocs/referent_to_users dal tesoriere:
     * nella View ho il richiamo ajax
     */

    public function admin_view_tesoriere_export_docs($order_id) {

        App::import('Model', 'Order');
        $Order = new Order();

        $options = [];
        $options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $order_id];
		$options['recursive'] = -1;
        $results = $Order->find('first', $options);

        $delivery_id = $results['Order']['delivery_id'];

        $this->set(compact('results'));
        $this->set('delivery_id', $delivery_id);
        $this->set('order_id', $order_id);

        $this->layout = 'ajax';
    }

    public function admin_view_orders_tesoriere($order_id) {

        App::import('Model', 'Order');
        $Order = new Order();

        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

        $options = [];
        $options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $order_id];
        $options['recursive'] = 1;
        $results = $Order->find('first', $options);

        /*
         * Referents
         */
        $conditions = ['User.block' => 0,
						'SuppliersOrganization.id' => $results['Order']['supplier_organization_id']];
        $suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($this->user, $conditions);

        if (!empty($suppliersOrganizationsReferent))
            $results['Order']['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent;

        $this->set(compact('results'));

        /*
         * supplier
         */
        App::import('Model', 'Supplier');
        $Supplier = new Supplier();

        $options = [];
        $options['conditions'] = ['Supplier.id' => $results['SuppliersOrganization']['supplier_id']];
        $options['recursive'] = -1;
        $supplier = $Supplier->find('first', $options);
        $this->set('supplier', $supplier);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_orders_tesoriere');
    }

    /*
     * key ProdDeliveriesArticle organization_id, prod_delivery_id, article_organization_id, article_id
     */

    public function admin_view_prod_deliveries($prod_delivery_id) {

        App::import('Model', 'ProdDeliveriesArticle');
        $ProdDeliveriesArticle = new ProdDeliveriesArticle();

        $ProdDeliveriesArticle->unbindModel(['belongsTo' => ['ProdDelivery', 'ProdCart']]);

        $options = [];
        $options['conditions'] = ['ProdDeliveriesArticle.organization_id' => $this->user->organization['Organization']['id'],
            'ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id];
        $options['recursive'] = 1;
        $options['order'] = ['Article.name'];
        $results = $ProdDeliveriesArticle->find('all', $options);
        $this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/view_prod_deliveries');
    }

    /*
     * Organization.type = 'GAS'
     * dettaglio di un articolo associato ad un ordine
     * ArticlesOrder.key = $order_id_$article_organization_id_$article_id
     */

    private function _view_articles_order($key, $evidenzia = '') {

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($order_id, $article_organization_id, $article_id) = explode('_', $key);
        if (empty($order_id) || empty($article_organization_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;
        if (!$ArticlesOrder->exists($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Article');
        $Article = new Article;
        $results = $Article->getArticleDataAnagrArticlesOrder($this->user, $article_organization_id, $article_id, $order_id);
        $this->set(compact('results'));

        /*
         * ArticlesType, qui lo calcolo runtime, se no e' il valore di Article.bio
         */
        if (!empty($results)) {
            App::import('Model', 'ArticlesArticlesType');
            $ArticlesArticlesType = new ArticlesArticlesType;
            $resultsArticlesTypes = $ArticlesArticlesType->getArticlesArticlesTypes($this->user, $results['Article']['organization_id'], $results['Article']['id']);

            if (!empty($resultsArticlesTypes)) {
                foreach ($resultsArticlesTypes as $resultsArticlesType) {
                    $tmp[] = $resultsArticlesType['ArticlesType'];
                }
                $results['ArticlesType'] = $tmp;
            } else
                $results['ArticlesType'] = [];
        }

        $this->set(compact('results'));
        $this->set('evidenzia', $evidenzia);

		/*
		 * dati owner_articles listino REFERENT / DES / SUPPLIER 
		 */
		if($article_organization_id!=$this->user->organization['Organization']['id']) {
	        App::import('Model', 'Organization');
	        $Organization = new Organization;
	        
			$options = [];
			$options['conditions'] = ['Organization.id' => $article_organization_id];
			$options['recursive'] = -1;
			$organizationResults = $Organization->find('first', $options);	
			$this->set('organizationResults', $organizationResults);
		}

        $this->layout = 'ajax';
        $this->render('/Ajax/view_articles_order');
    }

    /*
     * Organization.type = 'PROD'
     * dettaglio di un articolo associato ad una consegna
     * ProdDeliveryArticle.key = $prod_delivery_id_$article_organization_id_$article_id
     */

    private function _view_prod_deliveries_articles($key, $evidenzia = '') {

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($prod_delivery_id, $article_organization_id, $article_id) = explode('_', $key);
        if (empty($prod_delivery_id) || empty($article_organization_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'ProdDeliveriesArticle');
        $ProdDeliveriesArticle = new ProdDeliveriesArticle;
        if (!$ProdDeliveriesArticle->exists($this->user->organization['Organization']['id'], $prod_delivery_id, $article_organization_id, $article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Article');
        $Article = new Article;
        $results = $Article->getArticleDataAnagrProdDeliveriesArticle($this->user, $article_organization_id, $article_id, $prod_delivery_id);
        $this->set(compact('results'));

        /*
         * ArticlesType, qui lo calcolo runtime, se no e' il valore di Article.bio
         */
        if (!empty($results)) {
            App::import('Model', 'ArticlesArticlesType');
            $ArticlesArticlesType = new ArticlesArticlesType;
            $resultsArticlesTypes = $ArticlesArticlesType->getArticlesArticlesTypes($this->user, $results['Article']['organization_id'], $results['Article']['id']);

            if (!empty($resultsArticlesTypes)) {
                foreach ($resultsArticlesTypes as $resultsArticlesType) {
                    $tmp[] = $resultsArticlesType['ArticlesType'];
                }
                $results['ArticlesType'] = $tmp;
            } else
                $results['ArticlesType'] = [];
        }

        $this->set(compact('results'));
        $this->set('evidenzia', $evidenzia);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_prod_deliveries_articles');
    }

    public function admin_view_request_payment($request_payment_id = 0) {
        if (empty($request_payment_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'RequestPayment');
        $RequestPayment = new RequestPayment;

        $RequestPayment->id = $request_payment_id;
        if (!$RequestPayment->exists($RequestPayment->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * estraggo i dettagli di una richiesta di pagamento
         * 	- ordini associati
         *  - voci di spesa generica
         *  - dispensa
         */
        $results = $RequestPayment->getAllDetails($this->user, $request_payment_id);
        $this->set(compact('results'));

        $this->set('isSuperReferente', $this->isSuperReferente());

        $this->layout = 'ajax';
        $this->render('/Ajax/view_request_payment');
    }

    /*
     * key = TemplatesOrdersStatesOrdersAction.template_id-TemplatesOrdersStatesOrdersAction.state_code-OrdersAction.id-UserGroup.id
     */

    public function admin_view_orders_actions($key) {

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($template_id, $state_code, $order_action_id, $user_group_id) = explode('_', $key);

        App::import('Model', 'OrdersAction');
        $OrdersAction = new OrdersAction;

        $options = [];
        $options['conditions'] = ['OrdersAction.id' => $order_action_id];
        $results = $OrdersAction->find('first', $options);

        $this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/view_orders_actions');
    }

    /*
     * chiamato da organizationSeo = portale
     */

    public function modules_supplier_details($j_content_id = 0) {

        if (empty($j_content_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Organization');

        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

        $option = [];
        $option['conditions'] = ['Supplier.j_content_id' => $j_content_id];
        $option['recursive'] = 1;
        $results = $Supplier->find('first', $option);

        /*
         * estraggo le organization
         */
        if (isset($results['SuppliersOrganization']))
            foreach ($results['SuppliersOrganization'] as $numResult => $result) {

                $organization_id = $result['organization_id'];

                $Organization = new Organization;

                $options = [];
                $options['conditions'] = ['Organization.id' => $organization_id];
                $options['recursive'] = -1;
                $organizationsResults = $Organization->find('first', $options);

                $results['Organization'][$numResult] = $organizationsResults['Organization'];

                unset($results['SuppliersOrganization'][$numResult]);
            }
        
		self::d($results, false);
        
        $this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/modules_supplier_details');
    }

    public function modules_supplier_articles($j_content_id = 0) {

        if (empty($j_content_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

		App::import('Model', 'Supplier');
        $Supplier = new Supplier;

		App::import('Model', 'Article');
		$Article = new Article;

        $option = [];
        $option['conditions'] = ['Supplier.j_content_id' => $j_content_id];
        $option['recursive'] = 1;
        $results = $Supplier->find('first', $option);

        /*
         * estraggo gli articoli del primo GAS
		 * se non li trovo passo al successivo: se DES potrebbe non averli
         */
        if (isset($results['SuppliersOrganization'][0]['organization_id']) &&
                isset($results['SuppliersOrganization'][0]['id'])) {

			foreach($results['SuppliersOrganization'] as $suppliersOrganization) {
			
					$articlesResults = [];
					$organization_id = $suppliersOrganization['organization_id'];
					$supplier_organization_id = $suppliersOrganization['id'];

					$options = [];
					$options['conditions'] = ['Article.organization_id' => $organization_id,
											  'Article.supplier_organization_id' => $supplier_organization_id,
											  'Article.stato' => 'Y'];
					$options['recursive'] = -1;

					$articlesResults = $Article->find('all', $options);
					if(!empty($articlesResults)) {
						$results['Article'] = $articlesResults;
						break;
					}
					
			} // foreach($results['SuppliersOrganization'] as $suppliersOrganization)
        }
        
		self::d($results, false);
		
        $this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/modules_supplier_articles');
    }

    /*
     *  non utilizzata: la scheda del produttore non e' per singolo gas
     */

    public function modules_suppliers_organization_details($organization_id = 0, $j_content_id = 0) {
        if ($organization_id == 0 || empty($j_content_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $SuppliersOrganization->unbindModel(['belongsTo' =>  ['Organization']]);
        $SuppliersOrganization->unbindModel(['hasMany' => ['Article']]);

        $option = [];
        $option['conditions'] = ['SuppliersOrganization.organization_id' => $organization_id,
								 'Supplier.j_content_id' => $j_content_id];
        $option['recursive'] = 2;
        $results = $SuppliersOrganization->find('first', $option);

        self::d($results, false);
		
        $this->set(compact('results'));
        $this->set('organization_id', $organization_id);

        $this->layout = 'ajax';
        $this->render('/Ajax/modules_suppliers_organization_details');
    }

    public function admin_autoCompleteDeliveries_luogo($format = 'notmpl', $q) {

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

		$options = [];
		$options['conditions'] = ['Delivery.luogo LIKE' => '%' . $q . '%',
                				  'Delivery.organization_id' => (int) $this->user->organization['Organization']['id']];
		$options['fields'] = ['DISTINCT luogo'];
		$options['recursive'] = -1;
        $tmpResults = $Delivery->find('all', $options);

		self::l($tmpResults, false);
		
        $results = [];
        foreach ($tmpResults as $key => $value)
            foreach ($value as $key1 => $value1)
                foreach ($value1 as $key2 => $value2)
                    $results[] = $value2;

        $this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_deliveries_luogo');
    }

    public function admin_autoCompleteUsers_username($format = 'notmpl', $q) {
        App::import('Model', 'User');
        $User = new User;

        $conditions = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
            /* 'User.block'=> 0, */
            'lower(User.username) LIKE' => '%' . strtolower(addslashes($q)) . '%'];

        $this->set('results', $User->find('all', ['conditions' => $conditions, 'fields' => ['User.username']]));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_users_username');
    }

    public function admin_autoCompleteUsers_name($format = 'notmpl', $q) {
        App::import('Model', 'User');
        $User = new User;

        $conditions = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
            /* 'User.block'=> 0, */
            'lower(User.name) LIKE' => '%' . strtolower(addslashes($q)) . '%'];

        $this->set('results', $User->find('all', ['conditions' => $conditions, 'fields' => ['User.name']]));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_users_name');
    }

	/*
	 * DES 
	 */
    public function admin_autoCompleteDesUsers_username($format = 'notmpl', $q) {
		
		$results = [];
		
        /* ctrl ACL */
		if ($this->user->organization['Organization']['hasDes'] == 'N' || $this->user->organization['Organization']['hasDesUserManager'] != 'Y' || empty($this->user->des_id)) {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
		
        if (!$this->isManagerUserDes()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */
		
        App::import('Model', 'DesOrganization');
        $DesOrganization = new DesOrganization;
				
        App::import('Model', 'User');
        $User = new User;
		
        /*
         * tutti i GAS del DES
         */
        $options = [];
        $options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id];
        $options['recursive'] = 1;
        $desOrganizationsResults = $DesOrganization->find('all', $options);
		if(!empty($desOrganizationsResults)) {
			$organization_ids = [];
			foreach ($desOrganizationsResults as $desOrganizationsResult) {    
				array_push($organization_ids, $desOrganizationsResult['Organization']['id']);
			}
			
			$conditions = ['User.organization_id IN ' => $organization_ids,
							/* 'User.block'=> 0, */
							'lower(User.username) LIKE' => '%' . strtolower(addslashes($q)) . '%'];

			$results = $User->find('all', ['conditions' => $conditions, 'fields' => ['User.username']]);			
		}
		
        $this->set(compact('results'));
		$this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_users_username');
    }

	/*
	 * DES 
	 */
    public function admin_autoCompleteDesUsers_name($format = 'notmpl', $q) {		
        
		$results = [];
		
        /* ctrl ACL */
		if ($this->user->organization['Organization']['hasDes'] == 'N' || $this->user->organization['Organization']['hasDesUserManager'] != 'Y' || empty($this->user->des_id)) {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
		
        if (!$this->isManagerUserDes()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */
		
        App::import('Model', 'DesOrganization');
        $DesOrganization = new DesOrganization;
				
        App::import('Model', 'User');
        $User = new User;
		
        /*
         * tutti i GAS del DES
         */
        $options = [];
        $options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id];
        $options['recursive'] = 1;
        $desOrganizationsResults = $DesOrganization->find('all', $options);
		if(!empty($desOrganizationsResults)) {
			$organization_ids = [];
			foreach ($desOrganizationsResults as $desOrganizationsResult) {    
				array_push($organization_ids, $desOrganizationsResult['Organization']['id']);
			}
			
			$conditions = ['User.organization_id IN ' => $organization_ids,
							/* 'User.block'=> 0, */
							'lower(User.name) LIKE' => '%' . strtolower(addslashes($q)) . '%'];

			$results = $User->find('all', ['conditions' => $conditions, 'fields' => ['User.name']]);
		}
		
		$this->set(compact('results'));
        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_users_name');
    }
		
    public function admin_autoCompleteRequestPayment_name($format = 'notmpl', $q) {
        App::import('Model', 'User');
        $User = new User;

        $conditions = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
            /* 'User.block'=> 0, */
            'lower(User.name) LIKE' => '%' . strtolower(addslashes($q)) . '%'];

        $this->set('results', $User->find('all', ['conditions' => $conditions, 'fields' => ['User.name']]));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_users_name');
    }

    public function admin_autoCompleteContextArticlesArticles_name($format = 'notmpl', $q) {
        App::import('Model', 'Article');
        $Article = new Article;

        $options = [];
        $options['conditions'] = ['lower(Article.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
            'Article.organization_id' => $this->user->organization['Organization']['id'],
            'Article.supplier_organization_id IN (' . $this->user->get('ACLsuppliersIdsOrganization') . ')'];
        $options['fields'] = ['Article.name'];
        $options['recursive'] = -1;
        $results = $Article->find('all', $options);

        $this->set(compact('results'));
        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_article_name');
    }

    public function admin_autoCompleteContextOrderArticles_name($supplier_organization_id, $format = 'notmpl', $q) {
        App::import('Model', 'Article');
        $Article = new Article;


        $options = [];
        $options['conditions'] = ['lower(Article.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
            'Article.organization_id' => $this->user->organization['Organization']['id'],
            'Article.supplier_organization_id' => $supplier_organization_id];
        $options['fields'] = ['Article.name'];
        $options['recursive'] = -1;
        $results = $Article->find('all', $options);

        $this->set(compact('results'));
        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_article_name');
    }

    /*
     * filtro di ricerca del ecomm front-end
     */

    public function autoCompleteArticlesName($supplier_organization_id, $format = 'notmpl', $q) {
        App::import('Model', 'Article');
        $Article = new Article;

        $options = [];
        $options['conditions'] = ['lower(Article.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
            'Article.supplier_organization_id' => $supplier_organization_id,
            'Article.stato' => 'Y'];
        $options['fields'] = ['Article.name'];
        $options['recursive'] = -1;
        $results = $Article->find('all', $options);

        $this->set(compact('results'));
        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_article_name');
    }

    public function admin_autoCompleteSuppliers_name($format = 'notmpl', $q) {
        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

        $options = [];
        $options['conditions'] = ['lower(Supplier.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
								  'Supplier.stato != ' => 'N'];
        $options['fields'] = ['Supplier.name'];
        $options['recursive'] = -1;
        $this->set('results', $Supplier->find('all', $options));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_suppliers_name');
    }

    /*
     * prendo tutti i Supplier.stato = Y, T, N
     */

    public function admin_autoCompleteRootSuppliers_name($format = 'notmpl', $q) {
        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

        $options = [];
        $options['conditions'] = ['lower(Supplier.name) LIKE' => '%' . strtolower(addslashes($q)) . '%'];
        $options['fields'] = ['Supplier.name'];
        $options['recursive'] = -1;
        $this->set('results', $Supplier->find('all', $options));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_suppliers_name');
    }

    public function admin_autoCompleteSuppliersOrganizations_name($format = 'notmpl', $q) {
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $options = [];
        $options['conditions'] = ['lower(SuppliersOrganization.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
								  'SuppliersOrganization.organization_id' => (int) $this->user->organization['Organization']['id']];
		$options['fields'] = ['SuppliersOrganization.name'];
		$options['recursive'] = -1;		
		$results = $SuppliersOrganization->find('all', $options);
        $this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_suppliersOrganization_name');
    }
    
	/*
	 * da Order:add / Order:edit quando scelgo un produttore
	 * Supplier.img
	 * SuppliersOrganization.mail_order_open
	 * se Order ctrl se TitolareDes o ReferenteDes o SuperReferenteDes e popup di aprire OrderDes 
	 *
	 * des_order_id vale 9999 in Order:view / view_public cosi' salto il ctrl
	 */
    private function _suppliersOrganizationDetails($supplier_organization_id, $des_order_id, $debug = false) {
		
        $results = [];

        if (!empty($supplier_organization_id)) {
            App::import('Model', 'SuppliersOrganization');
            $SuppliersOrganization = new SuppliersOrganization;
            
            $SuppliersOrganization->unbindModel(['belongsTo' => ['Organization', 'CategoriesSupplier']]);

            $options = [];
            $options['conditions'] = ['SuppliersOrganization.id' => $supplier_organization_id,
                					  'SuppliersOrganization.organization_id' => (int) $this->user->organization['Organization']['id']];
            $options['recursive'] = 0;
            $results = $SuppliersOrganization->find('first', $options);
	
            self::d($results, false);
	
			/*
			 * owner_articles				  SUPPLIER / REFERENT / DES / PACT
		     * owner_organization_id          organization_id di chi gestisce il listino
		     * owner_supplier_organization_id supplier_organization_id di chi gestisce il listino
		     */
     			
			 /*
			  * ctrl se potrebbe essere un ordine DES
			  */
			 $isOwnGasTitolareDes = false;
			 $isTitolareDes = false;
			 $isReferenteDes = false;
			 $isSuperReferenteDes = false;
			 $msgOrderDes = "";
			 
             if($this->user->organization['Organization']['hasDes']=='Y' && empty($des_order_id)) {
		
					self::d('Inizio ctrl se produttore DES', $debug);
		            
		            App::import('Model', 'DesSupplier');
		            $DesSupplier = new DesSupplier;

					/*
					 * non ho scelto il DES
					 */ 
					if(empty($this->user->des_id))	{
							
						self::d('Non ho ancora scelto il mio DES', $debug);
			            
						/*
						 * se e' associato ad un solo DES lo ricavo
						 */
			            App::import('Model', 'DesOrganization');
			            $DesOrganization = new DesOrganization;

			            $options = [];
						$options['conditions'] = ['DesOrganization.organization_id' => $this->user->organization['Organization']['id']]; // non ho scelto il DES, ctrl solo se il suo GAS e' titolare
							   		                   					   		                   
			            $options['fields'] = ['DesOrganization.des_id'];
			            $options['recursive'] = -1;
			            $desOrganizationResults = $DesOrganization->find('all', $options);
						
						if(count($desOrganizationResults)==1) {
							/*
							 * e' associato a 1 solo DES
							 * non capita + perche' in AppController cerco se ne ha solo 1
							 */								
							$this->user->des_id = $desOrganizationResults[0]['DesOrganization']['des_id'];
							self::d('il GAS e\' associato ad un solo DES => ricavo des_id '.$this->user->des_id, $debug);
							
							$this->__addParamsDesJUser($user);
						}
						else {
							/*
							 * non ho scelto il DES ma e' associato a + DES, ctrl solo se il suo GAS e' titolare
							 */						
							$options = [];
			            	$options['conditions'] = ['DesSupplier.supplier_id' => $results['Supplier']['id'],
		            							      'DesSupplier.own_organization_id' => $this->user->organization['Organization']['id']];
				            $options['recursive'] = -1;
				            $desSupplierResults = $DesSupplier->find('first', $options);
							if(!empty($desSupplierResults)) {
								$isOwnGasTitolareDes = true;
								self::d('il GAS e\' associato ad + DES => NON posso ricavare des_id ma il suo GAS e\' titolare del produttore', $debug);
							}
							else {
								self::d('il GAS e\' associato ad + DES => NON posso ricavare des_id ma il suo GAS NON e\' titolare del produttore', $debug);
							
							}
							
						}
					}
					
					
					if(!empty($this->user->des_id))	{
					
						self::d('Ho già scelto il mio DES, des_id '.$this->user->des_id, $debug);
								
						/*
						 * ho scelto il DES
						 */ 					
			            $options = [];
			            $options['conditions'] = ['DesSupplier.supplier_id' => $results['Supplier']['id'],
			            						  'DesSupplier.des_id' => $this->user->des_id];			   		                   
			            $options['fields'] = ['DesSupplier.id'];
			            $options['recursive'] = -1;
			            $desSupplierResults = $DesSupplier->find('first', $options);

						if(!empty($desSupplierResults)) {
						
							self::d('Il produttore fa parte dei produttori DES', $debug);
						
							/*
							 * ctrl se lo user e' associato al produttore come REFERENTE DES o TITOLARE DES 
							 */
							App::import('Model', 'DesSuppliersReferent');
							$DesSuppliersReferent = new DesSuppliersReferent;
					
							$DesSuppliersReferent->unbindModel(['belongsTo' => ['De', 'User']]);
							
							$options = [];
							$options['conditions'] = ['DesSuppliersReferent.des_id' => $this->user->des_id,
													   'DesSuppliersReferent.organization_id' => $this->user->organization['Organization']['id'],
													   'DesSuppliersReferent.user_id' => $this->user->get('id'),
													   'DesSupplier.des_id' => $this->user->des_id,
													   'DesSupplier.id' => $desSupplierResults['DesSupplier']['id']];
							$options['recursive'] = 1;
							$ACLDesSuppliersResults = $DesSuppliersReferent->find('first', $options);
							
							if(!empty($ACLDesSuppliersResults)) {
								if($ACLDesSuppliersResults['DesSuppliersReferent']['group_id'] == Configure::read('group_id_titolare_des_supplier')) {
									$isTitolareDes = true;
									self::d('sono TITOLARE DES del produttore', $debug);
								}
								else
								if($ACLDesSuppliersResults['DesSuppliersReferent']['group_id'] == Configure::read('group_id_referent_des')) {
									$isReferenteDes = true;
									self::d('sono REFERENTE DES del produttore', $debug);
								}
							
								self::d($ACLDesSuppliersResults, false);
					            
					        } // end if(!empty($ACLDesSuppliersResults))
							else {
								/*
								 * ctrl se lo user e' SUPER-REFERENTE DES 
								 */		
								App::import('Model', 'UserGroupMap');
								$UserGroupMap = new UserGroupMap;
						
								$options = [];
								$options['conditions'] = ['UserGroupMap.user_id' => $this->user->get('id'),
														  'UserGroupMap.group_id' => Configure::read('group_id_super_referent_des')];
								$options['recursive'] = -1;
								$ACLDesSuppliersResults = $UserGroupMap->find('first', $options);
								if(!empty($ACLDesSuppliersResults)) {
									$isSuperReferenteDes = true;
									self::d('sono SUPER-REFERENTE DES del produttore', $debug);									
								}
							}
						}  // end if(!empty($desSupplierResults))
						else {
							self::d('Il produttore NON fa parte dei produttori DES', $debug);
						}
						
						$owner_articles = $results['SuppliersOrganization']['owner_articles']; // SUPPLIER / REFERENT / REFERENT-TMP / DES / PACT
						self::d($owner_articles, false);
	
						$msgOrderDesLink = '<p>Se desideri gestire l\'<b>ordine condiviso</b> (D.E.S.) <a class="btn btn-sm btn-info" href="?option=com_cake&controller=DesOrders&action=index">clicca qui</a></p>';
						$msgOrderDes .= '<div class="alert alert-danger" role="alert">Ordine D.E.S. o normale?</div>';
						
						if($isOwnGasTitolareDes) {
							$msgOrderDes .= "<p>Il tuo G.A.S. è titolare degli ordini D.E.S. del produttore</p>";
							$msgOrderDes .= $msgOrderDesLink;
						}
						else
						if($isTitolareDes) {
							$msgOrderDes .= "<p>Sei titolare degli ordini D.E.S. del produttore!</p>";
							$msgOrderDes .= $msgOrderDesLink;
						}
						else 
						if($isReferenteDes) {
							$msgOrderDes .= "<p>Sei il referente degli ordini D.E.S. del produttore</p>";
							$msgOrderDes .= $msgOrderDesLink;
						}
						else 
						if($isSuperReferenteDes) {
							$msgOrderDes .= "<p>Sei super-referente degli ordini D.E.S. del produttore</p>";
							$msgOrderDes .= $msgOrderDesLink;
						}
						
						/*
						 * msg se il GAS vuole utilizzare il proprio listino e non quello del titolare DES
						 */
						if($isReferenteDes || $isSuperReferenteDes && ($owner_articles=='DES')) {
							$msgOrderDes .= '<div class="alert alert-danger" role="alert">Quale listino articolo desideri utilizzare</div>';
							$msgOrderDes .= '<p>Sei desideri utilizzare <ul><li>il listino articoli associato al tuo G.A.S. e</li><li>non quello del G.A.S. titolare</li></ul></p>';
							$msgOrderDes .= '<p>configura la <b>Gestione del listino</b> del produttore in <b>referente del G.A.S.</b>, <a class="btn btn-sm btn-info" href="?option=com_cake&controller=SuppliersOrganizations&action=edit&id='.$results['SuppliersOrganization']['id'].'">clicca qui</a></p>';							
						}
						$msgOrderDes .= "<br /><br /><p class=\"alert alert-info\">Altrimenti prosegui con l'ordine.</p>";
						
				        self::d('isOwnGasTitolareDes '.$isOwnGasTitolareDes, $debug);
				        self::d('isTitolareDes '.$isTitolareDes, $debug);
				        self::d('isReferenteDes '.$isReferenteDes, $debug);
				        self::d('isSuperReferenteDes '.$isSuperReferenteDes, $debug);
				        self::d('msgOrderDes '.$msgOrderDes, $debug);
												
					} // end if(empty($this->user->des_id))
             } // end if($this->user->organization['Organization']['hasDes']=='Y' && empty($des_order_id))
             
             $this->set(compact('isOwnGasTitolareDes', 'isTitolareDes', 'isReferenteDes', 'isSuperReferenteDes', 'msgOrderDes')); 
        }
		
		return $results;
    }

	/*
	 * quando creo un ordine mi avvisa se l'ordine e' DES
	 */
    public function admin_suppliersOrganizationWithMsgDetails($supplier_organization_id=0, $des_order_id=0, $format='notmpl') {

		$results = $this->_suppliersOrganizationDetails($supplier_organization_id, $des_order_id, $debug = false);
        $this->set(compact('results'));
        $this->set('msgAlert', true);

        $this->layout = 'ajax';
        $this->render('/Ajax/admin_suppliers_organization_details');
    }
	
    public function admin_suppliersOrganizationDetails($supplier_organization_id=0, $des_order_id=0, $format='notmpl') {

		$results = $this->_suppliersOrganizationDetails($supplier_organization_id, $des_order_id, $debug = false);
        $this->set(compact('results'));
		$this->set('msgAlert', false);

        $this->layout = 'ajax';
        $this->render('/Ajax/admin_suppliers_organization_details');
    }

    public function admin_suppliersOrganizationOrderDetails($order_id, $des_order_id=0, $format='notmpl') {

        $results = [];

        App::import('Model', 'Order');
        $Order = new Order;

        $results = $Order->_getOrderById($this->user, $order_id);

        if(!empty($des_order_id)) {
            $results = $this->_suppliersOrganizationDetails($supplier_organization_id, $des_order_id, $debug = false);
            $this->set(compact('results'));
            $this->set('msgAlert', false);

            $this->layout = 'ajax';
            $this->render('/Ajax/admin_suppliers_organization_order_details');
        }
        else {
            $this->set(compact('results'));
            $this->set('msgAlert', false);

            $this->layout = 'ajax';
            $this->render('/Ajax/admin_suppliers_organization_order_details');
        }
    }

    public function admin_desSupplierDetails($des_supplier_id = 0, $format = 'notmpl') {

        $results = [];

        if (!empty($des_supplier_id)) {

            App::import('Model', 'DesSupplier');
            $DesSupplier = new DesSupplier;

            $DesSupplier->unbindModel(['belongsTo' => ['De', 'OwnOrganization', 'DesOrder']]);

            $options = [];
            $options['conditions'] = ['DesSupplier.des_id' => $this->user->des_id,
									'DesSupplier.own_organization_id' => $this->user->organization['Organization']['id'],
									'DesSupplier.id' => $des_supplier_id];
            $options['recursive'] = 0;
            $results = $DesSupplier->find('first', $options);

            self::d($results, false);
        }

        $this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/admin_suppliers_organization_details');
    }

    /*
     * call 
	 *		Articles::admin_index_quick
     * 		ProdGasArticles::admin_index_quick
     * idRow = id-field-table
     */
    public function admin_updateGeneric($idRow) {

		$debug = false;
		
        $tableValide = ['articles', 'prod_gas_articles'];
        $continue = true;
        $esito = 'NO';

        $this->log .= "\r\n------------------------------------------------------------------------------";
        $this->log .= "\r\n GAS organization_id".$this->user->organization['Organization']['id'];
        $this->log .= "\r\n PRODGAS organization_id".$this->user->organization['Supplier']['id'];
        $this->log .= "\r\n idRow $idRow";


        if (empty($idRow))
            $continue = false;

        if ($continue) {
            $rowIdElem = explode("-", $idRow);
            $id = $rowIdElem[0];
            $field = $rowIdElem[1];
            $table = $rowIdElem[2];

            $this->log .= "\r\n id $id";
            $this->log .= "\r\n field $field";
            $this->log .= "\r\n table $table";

            if (!is_numeric($id))
                $continue = false;

            if (!in_array($table, $tableValide))
                $continue = false;
        }

        if ($continue) {

            $value = $this->request->data['value'];
            $this->log .= "\r\n value (POST) $value";

            if (!empty($value)) {
                switch ($field) {
                    case "prezzo":
                        $value = $this->importoToDatabase($value);
                        $value = "'" . $value . "'";
                        break;
                    case "qta":
                        $value = $this->importoToDatabase($value);
                        $value = "'" . $value . "'";
                        break;
                    default:
                        $value = "'" . str_replace("'", "\'", $value) . "'";
                        break;
                }
            } else
                $value = "'" . $value . "'";

            $sql = "SELECT * 
					FROM " . Configure::read('DB.prefix') . $table . " 
					WHERE 
						BINARY $field = " . $value . " 
						and id = ".(int) $id." and organization_id = ".$this->user->organization['Organization']['id'];		

            // self::d($sql, false);
            $this->log .= "\r\n sql $sql";
            $results = $this->{$this->modelClass}->query($sql);
            $this->log .= "\r\n count(results) " . count($results);

            if (!empty($results))
                $esito = 'NOCHANGE';
            else {
                $sql = "UPDATE 
							" . Configure::read('DB.prefix') . $table . " 
						SET 
							$field = " . $value . ", 
							modified = '" . date('Y-m-d H:i:s') . "'
			   			WHERE
		   					id = ".(int)$id." and organization_id = ".$this->user->organization['Organization']['id'];
                // self::d($sql, false);
                $this->log .= "\r\n sql $sql";
                $results = $this->{$this->modelClass}->query($sql);

                $esito = 'OK';
            }
        }

        if ($esito == 'NOCHANGE')
            $content_for_layout = '';
        else
            $content_for_layout = "<script type=\"text/javascript\">managementCart('" . $id . "','" . $esito . "',0,null);</script>";

        $this->set('content_for_layout', $content_for_layout);

        $this->log .= "\r\n esito $esito";
        $this->log .= "\r\n  $content_for_layout";
        if (Configure::read('developer.mode'))
            CakeLog::write("debug", $this->log);
		self::d($this->log, $debug);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }

    public function admin_view_cassiere_orders($order_id) {

        if (empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $order_id;
        if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;
        $results = $SummaryOrder->select_to_order($this->user, $order_id);
        $this->set(compact('results'));
		
		self::d($results, false);
		
        $this->layout = 'ajax';
        $this->render('/Ajax/admin_view_cassiere_orders');
    }

	public function admin_view_details_users($des_supplier_id) {
		App::import('Controller', 'Mails');
		$Mails = new MailsController;
		
		/*
		 * gli risetto user perche' in MailsController $this vale MailsController
		 */		
		$Mails->user = $this->user; 		
		$Mails->admin_des_send_details_users($des_supplier_id);

		$this->set('results', $Mails->viewVars['results']);
		
		$this->set('userGroups', $this->userGroups);

			
		$this->layout = 'ajax';
		$this->render('/Mails/admin_des_send_details_users');
	}
	

	public function admin_ctrl_supplier_duplicate($field, $value) {

		App::import('Model', 'Supplier');
		$Supplier = new Supplier;		

		$msg = $Supplier->ctrlSupplierDuplicate($this->user, $field, $value);
		$this->set('content_for_layout', $msg);
			
		$this->layout = 'ajax';
		$this->render('/Layouts/ajax');
	}	
	
    public function admin_view_docs_creates($doc_id = 0) {
		
		if (empty($doc_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		App::import('Model', 'DocsCreateUser');
		$DocsCreateUser  = new DocsCreateUser ;
		
		$options = [];
		$options['conditions'] = ['DocsCreateUser.organization_id' => $this->user->organization['Organization']['id'],
								  'DocsCreateUser.doc_id' => $doc_id];
		$options['order'] = ['DocsCreateUser.num'];
		$options['recursive'] = 1;
		
		$results = $DocsCreateUser->find('all', $options);
		$this->set(compact('results'));

        $this->layout = 'ajax';
        $this->render('/Ajax/view_docs_creates');
    }	
	
	/*
	 * di un articolo in dispensa, estrae le eventuali prenotazioni 
	 */
    public function admin_view_storeroom_just_booked($storeroom_id = 0) {

		if (empty($storeroom_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		App::import('Model', 'Storeroom');
		$Storeroom = new Storeroom;
	            
		/*
		 * ctrl configurazione Organization
		 */
		if($this->user->organization['Organization']['hasStoreroom']=='N') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$storeroomUser = $Storeroom->getStoreroomUser($this->user);
		if(empty($storeroomUser)) {
			$this->Session->setFlash(__('StoreroomNotFound'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	
		
		$options = [];
		$options['conditions'] = ['Storeroom.organization_id' => $this->user->organization['Organization']['id'],
								  'Storeroom.id' => $storeroom_id];
		$options['recursive'] = -1;
		$storeroomResults = $Storeroom->find('first', $options);
		
		$results = $Storeroom->getArticlesJustBooked($this->user, $storeroomUser, $storeroomResults['Storeroom']['organization_id'], $storeroomResults['Storeroom']['article_id']);
		$this->set(compact('results'));
		
        $this->layout = 'ajax';
        $this->render('/Ajax/view_storeroom_just_booked');
    }
    
    /*
     *	dettaglio user di acquisti effettuati 
     */
    public function admin_view_orders_cashes_limit_users($user_id=0) {
		
		$debug=false;
		
		if (empty($user_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		App::import('Model', 'CashesUser');
		$CashesUser = new CashesUser;
		
		$results = $CashesUser->getTotImportoAcquistatoDetails($this->user, $user_id, $debug);
		$this->set(compact('results'));
		
		self::d($results, false);
		
        $this->layout = 'ajax';
        $this->render('/Ajax/view_orders_cashes_limit_users');
    }
}