<?php
App::uses('AppModel', 'Model');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::import('Vendor', 'xtcpdf');

class Statistic extends AppModel {

    public $useTable = false;
	private $debug_insert = true; // false NON inserisce
	private $debug_create_pdf = true; // false NON crea pdf => non + utilizzato 
	private $debug_sql_orders_limit = 0; // 0 prende tutto

    /*
     * cron::archiveStatistics()
     * 	tratto Order.stato_code = CLOSE 
     * 		 dati archiviati in Statistiche
     * 		 cancellazione Carrello / Ordini / Consegne se non ha + ordini
     */
    public function archive($user, $debug = false) {

        $organization_id = $user->organization['Organization']['id'];

        try {
            App::import('Model', 'Order');

            App::import('Model', 'ArticlesOrder');
            $ArticlesOrder = new ArticlesOrder;

			self::d("Statistic::archive Estraggo tutti gli Order.stato_code = CLOSE con Order.data_state_code_close + ".$user->organization['Organization']['ggArchiveStatics']." gg passati", $debug);
			self::d("Statistic::archive -> dati archiviati in Statistiche", $debug);
			self::d("Statistic::archive -> cancellazione Carrello / Ordini / Consegne se non ha + ordini", $debug);
			
            $orderResults = $this->_getOrdersArchive($user, $organization_id, $debug);

            if (!empty($orderResults))
                foreach ($orderResults as $numResult => $orderResult) {
                    
                    self::d("Tratto consegna " . $orderResult['Delivery']['id'], $debug);

                    /*
					 * non creo + il pdf 
					if($this->debug_create_pdf)
						$this->_createPdfUserDelivery($user, $orderResult['Delivery'], $debug);
					*/
                    $this->_statDeliveryInsert($user, $orderResult['Delivery'], $debug);

					$this->_statOrderInsert($user, $orderResult, $debug);

					$conditions = [];
					$conditions = ['Order.id' => $orderResult['Order']['id'],
									'ArticlesOrder.order_id' => $orderResult['Order']['id'],
									'Cart.deleteToReferent' => 'N'];
					$orderBy['ArticlesOrder'] = ['ArticlesOrder.organization_id','ArticlesOrder.order_id','ArticlesOrder.article_organization_id','ArticlesOrder.article_id'];
					$articlesOrders = $ArticlesOrder->getArticoliAcquistatiDaUtenteInOrdine($user, $conditions, $orderBy);

					$organization_id_old = 0;
					$order_id_old = 0;
					$article_organization_id_old = 0;
					$article_id_old = 0;
					foreach ($articlesOrders as $numArticlesOrder => $articlesOrder) {

						if ($organization_id_old != $articlesOrder['ArticlesOrder']['organization_id'] ||
								$order_id_old != $articlesOrder['ArticlesOrder']['order_id'] ||
								$article_organization_id_old != $articlesOrder['ArticlesOrder']['article_organization_id'] ||
								$article_id_old != $articlesOrder['ArticlesOrder']['article_id'])
							$this->_statArticlesOrderInsert($user, $articlesOrder['Article'], $articlesOrder['ArticlesOrder'], $debug);

						$this->_statCartInsert($user, $articlesOrder['Cart'], $articlesOrder['ArticlesOrder'], $debug);

						$organization_id_old = $articlesOrder['ArticlesOrder']['organization_id'];
						$order_id_old = $articlesOrder['ArticlesOrder']['order_id'];
						$article_organization_id_old = $articlesOrder['ArticlesOrder']['article_organization_id'];
						$article_id_old = $articlesOrder['ArticlesOrder']['article_id'];
					} // loop ArticlesOrders

					$this->_deleteOrder($user, $orderResult['Order'], $debug);

                 $this->_deleteDelivery($user, $orderResult['Delivery'], $debug);
				 
                } // loop Orders
        } catch (Exception $e) {
			self::d($e, $debug);
        }
    }

    /*
     * estraggo gli Order CLOSE con DATE(Order.data_state_code_close) <= (CURDATE()- INTERVAL ' .$user->organization['Organization']['ggArchiveStatics']. ' DAY 
     * escludo gli ordini titolari di gruppo
     */
    private function _getOrdersArchive($user, $organization_id, $debug) {

        App::import('Model', 'Order');
        $Order = new Order;

        $options = [];
        $options['conditions'] = ['Order.organization_id' => (int) $organization_id,
                                  'Order.state_code' => 'CLOSE',
                                  'Order.order_type_id != ' => Configure::read('Order.type.gas_parent_groups'),
								  'DATE(Order.data_state_code_close) <= CURDATE() - INTERVAL '.$user->organization['Organization']['ggArchiveStatics'].' DAY '
                                 ];
        $options['recursive'] = 0;
		if($this->debug_sql_orders_limit > 0)
			$options['limit'] = $this->debug_sql_orders_limit;
        $options['order'] = ['Order.id'];
        $results = $Order->find('all', $options);

		self::d($options['conditions'], $debug);
		self::d("Statistic::_getOrdersArchive trovati " . count($results) . " ordini", $debug);

        if(count($results)==0)
            return $results;

        /*
         * controllo se gli ordini sono associati ad una richiesta di pagamento
         */
        switch ($user->organization['Template']['payToDelivery']) {
            case 'ON':
                break;
            case 'POST':
            case 'ON-POST':
                App::import('Model', 'RequestPaymentsOrder');
                $RequestPaymentsOrder = new RequestPaymentsOrder;

                foreach($results as $numResult => $result) {
                    $options = [];
                    $options['conditions'] = ['RequestPaymentsOrder.organization_id' => $user->organization['Organization']['id'],
                        'RequestPaymentsOrder.order_id' => $result['Order']['id']];
                    $options['recursive'] = 1;
                    $requestPaymentsOrderResults = $RequestPaymentsOrder->find('all', $options);
                    if(!empty($requestPaymentsOrderResults)) {
                        // $requestPaymentsOrderResults['RequestPayment']['stato_elaborazione']!='CLOSE'
                        // se ho associato un rich di pagamento non posso cancellare l'ordine
                        // quando la richiesta di pagamento viene chiusa, la richiesta di pagamento sara' eliminata
                        self::d("Statistic::_getOrdersArchive l'ordine " . $result['Order']['id']. " e' associato ad una richiesta di pagamento, non posso eliminalo", $debug);
                        unset($results[$numResult]);
                    }
                }
                break;
        }

        return $results;
    }
	
    private function _statDeliveryInsert($user, $delivery, $debug) {

		App::import('Model', 'StatDelivery');
		$StatDelivery = new StatDelivery;

		/*
		 * ctrl se esiste gia'
		 */ 
		$options = [];
		$options['conditions'] = ['StatDelivery.organization_id' => $delivery['organization_id'],
								   'StatDelivery.id' => $delivery['id']];
		$options['recursive'] = -1;
		$statDeliveryResults = $StatDelivery->find('first', $options);
		if(empty($statDeliveryResults)) {	
			
			self::d("Statitic ::_statDeliveryInsert => NON esiste => la creo ", $debug);
			
			$dataDelivery = [];
			$dataDelivery['StatDelivery']['id'] = $delivery['id'];
			$dataDelivery['StatDelivery']['organization_id'] = $delivery['organization_id'];
			$dataDelivery['StatDelivery']['luogo'] = $delivery['luogo'];
			$dataDelivery['StatDelivery']['data'] = $delivery['data'];
			
			if($this->debug_insert) {				 
				if (!$StatDelivery->save($dataDelivery)) {
					self::d("Statitic ::_statDeliveryInsert ", $debug);
					self::d($dataDelivery, $debug);
					return;
				} else {
					self::d("Statitic ::_statDeliveryInsert INSERT StatDelivery " . $delivery['id'] . " data " . $dataDelivery['StatDelivery']['data'] . " " . $dataDelivery['StatDelivery']['luogo'], $debug);
				}
			}
			else {
				self::d("Statitic ::_statDeliveryInsert INSERT StatDelivery " . $delivery['id'] . " data " . $dataDelivery['StatDelivery']['data'] . " " . $dataDelivery['StatDelivery']['luogo'], $debug);
			}
		}
		else {
			self::d($statDeliveryResults, $debug);
			self::d("Statitic ::_statDeliveryInsert => esiste gia => NON la creo ", $debug);
		}
    }

    private function _statOrderInsert($user, $orderResults, $debug) {

        try {
            /*
             * dati produttore
             */
            App::import('Model', 'SuppliersOrganization');
            $SuppliersOrganization = new SuppliersOrganization;

            $SuppliersOrganization->unbindModel(['hasMany' => ['Article', 'Order', 'SuppliersOrganizationsReferent']]);
            $SuppliersOrganization->unBindModel(['belongsTo' => ['Organization', 'CategoriesSupplier']]);

            $options = [];
            $options['conditions'] = ['SuppliersOrganization.organization_id' => (int) $orderResults['Order']['organization_id'],
									  'SuppliersOrganization.id' => $orderResults['Order']['supplier_organization_id']];
            $options['order'] = ['SuppliersOrganization.name', 'Supplier.id', 'Supplier.img1'];
            $options['recursive'] = 1;
            $suppliersOrganizationsResults = $SuppliersOrganization->find('first', $options);

            $supplier_img1 = '';
            if (!empty($suppliersOrganizationsResults)) {
                $supplier_organization_name = $suppliersOrganizationsResults['SuppliersOrganization']['name'];
                $supplier_img1 = $suppliersOrganizationsResults['Supplier']['img1'];
            }

            App::import('Model', 'Order');
            $Order = new Order;

            App::import('Model', 'StatOrder');
            $StatOrder = new StatOrder;

            $dataOrder = [];
            $dataOrder['StatOrder']['id'] = $orderResults['Order']['id'];
            $dataOrder['StatOrder']['organization_id'] = $orderResults['Order']['organization_id'];
            $dataOrder['StatOrder']['supplier_organization_id'] = $orderResults['Order']['supplier_organization_id'];
            $dataOrder['StatOrder']['supplier_organization_name'] = $supplier_organization_name;
            $dataOrder['StatOrder']['supplier_img1'] = $supplier_img1;
            $dataOrder['StatOrder']['stat_delivery_id'] = $orderResults['Order']['delivery_id'];
            $dataOrder['StatOrder']['data_inizio'] = $orderResults['Order']['data_inizio'];
            $dataOrder['StatOrder']['data_fine'] = $orderResults['Order']['data_fine'];
			$dataOrder['StatOrder']['stat_delivery_year'] = substr($orderResults['Delivery']['data'], 0, 4);

            /*
             * dati fattura
             */
            $dataOrder['StatOrder']['tesoriere_fattura_importo'] = $orderResults['Order']['tesoriere_fattura_importo'];
            $dataOrder['StatOrder']['tesoriere_doc1'] = $orderResults['Order']['tesoriere_doc1'];
            $dataOrder['StatOrder']['tesoriere_data_pay'] = $orderResults['Order']['tesoriere_data_pay'];
            $dataOrder['StatOrder']['tesoriere_importo_pay'] = $orderResults['Order']['tesoriere_importo_pay'];

			/*
			 * dati richiesta di pagamento
			 */
			$dataOrder['StatOrder']['request_payment_num'] = ''; 
			switch($user->organization['Template']['payToDelivery']) {
				case "POST":	
				case "ON-POST":		
					App::import('Model', 'RequestPaymentsOrder');
					$RequestPaymentsOrder = new RequestPaymentsOrder;

					$options = [];
					$options['conditions'] = ['RequestPaymentsOrder.organization_id' => $user->organization['Organization']['id'],
											  'RequestPaymentsOrder.order_id' => $orderResults['Order']['id']];
					$options['recursive'] =  0;	
					$RequestPaymentsOrder->unbindModel(['belongsTo' => ['Order']]);
					$requestPaymentsOrderResults = $RequestPaymentsOrder->find('first', $options);
					
					self::d($requestPaymentsOrderResults, $debug);
					
					if(!empty($requestPaymentsOrderResults)) {
						$dataOrder['StatOrder']['request_payment_num'] = $requestPaymentsOrderResults['RequestPayment']['num'];
					}
				break;
				case "ON":
				
				break;
			}
						
            /*
             * calcolo il totale degli importi degli acquisti dell'ordine
             */
            if (empty($orderResults['Order']['tot_importo']) || $orderResults['Order']['tot_importo'] == '0.00')
                $importo_totale = $Order->getTotImporto($user, $orderResults['Order']['id']);
            else
                $importo_totale = $orderResults['Order']['tot_importo'];
            $dataOrder['StatOrder']['importo'] = $importo_totale;

            self::d($dataOrder, $debug);
   
			if($this->debug_insert) {	
				if (!$StatOrder->save($dataOrder)) {
					self::d("ERRORE StatOrder::save()", $debug);
					return;
				} else
                    $stat_order_id = $StatOrder->getLastInsertId();    
                    self::d("INSERT StatOrder " . $orderResults['Order']['id'] . " - data ini " . $orderResults['Order']['data_inizio'] . " e data fine " . $orderResults['Order']['data_fine'] . ", produttore " . $supplier_organization_name . " (" . $orderResults['Order']['supplier_organization_id'] . ") con importo totale " . $dataOrder['StatOrder']['importo'] . ", fattura " . $dataOrder['StatOrder']['tesoriere_doc1'].' stat_order_id '.$stat_order_id, $debug);

                    /* 
                    * aggiorno movimento di cassa, da order_id a stat_order_id
                    */          
                    App::import('Model', 'Movement');
                    $Movement = new Movement;	
                    $Movement->update($user, $user->organization['Organization']['id'], $orderResults['Order']['id'], $stat_order_id, $debug);
			}
			else {
					self::d("INSERT StatOrder " . $orderResults['Order']['id'] . " - data ini " . $orderResults['Order']['data_inizio'] . " e data fine " . $orderResults['Order']['data_fine'] . ", produttore " . $supplier_organization_name . " (" . $orderResults['Order']['supplier_organization_id'] . ") con importo totale " . $dataOrder['StatOrder']['importo'] . ", fattura " . $dataOrder['StatOrder']['tesoriere_doc1'], $debug);				
			}
        } catch (Exception $e) {
            self::d($e, $debug);
        }
    }

    private function _statArticlesOrderInsert($user, $article, $articlesOrder, $debug) {

        try {
            App::import('Model', 'StatArticlesOrder');
            $StatArticlesOrder = new StatArticlesOrder;

			/*
			 * prima d inserire faccio un ulteriore controllo 
			 */
			$options = [];
			$options['conditions'] = ['StatArticlesOrder.organization_id' => $articlesOrder['organization_id'],
									'StatArticlesOrder.stat_order_id' => $articlesOrder['order_id'],
									'StatArticlesOrder.article_organization_id' => $articlesOrder['article_organization_id'],
									'StatArticlesOrder.article_id' => $articlesOrder['article_id']];
			$options['recursive'] = -1;
			$statArticlesOrderCount = $StatArticlesOrder->find('count', $options);

			if($statArticlesOrderCount==0) {
				$dataArticlesOrder = [];
				$dataArticlesOrder['StatArticlesOrder']['organization_id'] = $articlesOrder['organization_id'];
				$dataArticlesOrder['StatArticlesOrder']['article_organization_id'] = $articlesOrder['article_organization_id'];
				$dataArticlesOrder['StatArticlesOrder']['article_id'] = $articlesOrder['article_id'];
				$dataArticlesOrder['StatArticlesOrder']['stat_order_id'] = $articlesOrder['order_id'];
				$dataArticlesOrder['StatArticlesOrder']['prezzo'] = $articlesOrder['prezzo'];
				$dataArticlesOrder['StatArticlesOrder']['name'] = $articlesOrder['name'];
				$dataArticlesOrder['StatArticlesOrder']['codice'] = $article['codice'];
				$dataArticlesOrder['StatArticlesOrder']['qta'] = $article['qta'];
				if (!empty($article['um']))
					$dataArticlesOrder['StatArticlesOrder']['um'] = $article['um'];
				else
					$dataArticlesOrder['StatArticlesOrder']['um'] = 'PZ';
				if (!empty($article['um_riferimento']))
					$dataArticlesOrder['StatArticlesOrder']['um_riferimento'] = $article['um_riferimento'];
				else
					$dataArticlesOrder['StatArticlesOrder']['um_riferimento'] = 'PZ';

				self::d($dataArticlesOrder, $debug);
				
				if($this->debug_insert) {
					if (!$StatArticlesOrder->save($dataArticlesOrder)) {
						self::d("ERRORE StatArticlesOrder::save()", $debug);
						return;
					} else
						self::d("INSERT StatArticlesOrder, order_id ".$articlesOrder['order_id']." - article_id ".$articlesOrder['article_id'], $debug);
				}
				else {
					self::d("INSERT StatArticlesOrder, order_id ".$articlesOrder['order_id']." - article_id ".$articlesOrder['article_id'], $debug);
				}
			}
			else {
				self::d("NO INSERT StatArticlesOrder ESISTE GIA, order_id ".$articlesOrder['order_id']." - article_id ".$articlesOrder['article_id'], $debug);				
			}
        } catch (Exception $e) {
            self::d($e, $debug);
        }
    }

    private function _statCartInsert($user, $cart, $articlesOrder, $debug) {

        try {
            App::import('Model', 'StatCart');
            $StatCart = new StatCart;

            $dataCart = [];
            $dataCart['StatCart']['organization_id'] = $cart['organization_id'];
            ;
            $dataCart['StatCart']['stat_order_id'] = $cart['order_id'];
            $dataCart['StatCart']['article_organization_id'] = $cart['article_organization_id'];
            $dataCart['StatCart']['article_id'] = $cart['article_id'];
            $dataCart['StatCart']['user_id'] = $cart['user_id'];

            /*
             * importo e qta
             */
            if ($cart['qta_forzato'] > 0) {
                $qta = $cart['qta_forzato'];
            } else
                $qta = $cart['qta'];

            if ($cart['importo_forzato'] == 0) {
                if ($cart['qta_forzato'] > 0)
                    $importo = ($cart['qta_forzato'] * $articlesOrder['prezzo']);
                else
                    $importo = ($cart['qta'] * $articlesOrder['prezzo']);
            } else
                $importo = $cart['importo_forzato'];

            $dataCart['StatCart']['qta'] = $qta;
            $dataCart['StatCart']['importo'] = $importo;

            self::d($dataCart, $debug);

			if($this->debug_insert) {
				if (!$StatCart->save($dataCart)) {
					self::d("ERRORE StatCart::save()", $debug);
					return;
				} else
					self::d("INSERT StatCart user " . $dataCart['StatCart']['user_id'] . " con articolo ID " . $cart['article_organization_id'] . "-" . $cart['article_id'], $debug);
			}
			else {
				self::d("INSERT StatCart user " . $dataCart['StatCart']['user_id'] . " con articolo ID " . $cart['article_organization_id'] . "-" . $cart['article_id'], $debug);
			}
        } catch (Exception $e) {
            self::d($e, $debug);
        }
    }

    /*
     * cancellazione Order
     * 		TRIGGER 
     * 			 k_summary_orders
     * 			 k_articles_orders
     * 				TRIGGER
     * 				k_carts
     * 			 k_request_payments_orders
     */

    private function _deleteOrder($user, $order, $debug) {

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $order['id'];
		if($this->debug_insert) {
			if ($Order->delete())
				self::d("DELETE Order " . $order['id'] . " OK", $debug);    
			else
				self::d("DELETE Order " . $order['id'] . " ERRORE", $debug);
		}
		else {
			self::d("DELETE Order " . $order['id'] . " DEBUG", $debug);
		}
        
        /*
         * ctrl se l'ordine e' di gruppo 
         * se si, cancello l'ordine titolar di gruppo se non ha + ordini di gruppo associati
         * */
        if($order['order_type_id']==Configure::read('Order.type.gas_groups')) {
            
            // cerco l'ordine titolare
            $options = [];
            $options['conditions'] = ['Order.organization_id' => $order['organization_id'],
                                        'Order.id' => $order['parent_id'],
                                        'Order.state_code' => 'CLOSE',
                                        'Order.order_type_id' => Configure::read('Order.type.gas_parent_groups')];
            $options['recursive'] = -1;
            $orderGroupParent = $Order->find('first', $options);  
            
            if(!empty($orderGroupParent)) {
                // ctrl che abbia non abbia + ordini associati
                $options = [];
                $options['conditions'] = ['Order.organization_id' => $orderGroupParent['Order']['organization_id'],
                                        'Order.parent_id' => $orderGroupParent['Order']['id'],
                                        'Order.order_type_id' => Configure::read('Order.type.gas_groups')];
                $options['recursive'] = -1;
                $orderGroupChild = $Order->find('first', $options);
                
                if(empty($orderGroupChild)) {

                    $Order->id = $orderGroupParent['Order']['id'];
                    if($this->debug_insert) {
                        if ($Order->delete())
                            self::d("DELETE Order parent " . $orderGroupParent['Order']['id'] . " OK", $debug);    
                        else
                            self::d("DELETE Order parent " . $orderGroupParent['Order']['id'] . " ERRORE", $debug);
                    }
                    else {
                        self::d("DELETE Order parent " . $orderGroupParent['Order']['id'] . " DEBUG", $debug);
                    }
                }
            }
        }
    }

    /*
     * cancellazione Delivery, se non ha + ordini associati ed e' stata eventualmente pagata alla consegna 
	 * Cron deliveriesStatoElaborazione => $DeliveryLifeCycle->deleteExpiredWithoutAssociations cancella le consegne senza + associazioni
     */

    private function _deleteDelivery($user, $delivery, $debug) {

        App::import('Model', 'DeliveryLifeCycle');
        $DeliveryLifeCycle = new DeliveryLifeCycle;

		$DeliveryLifeCycle->deleteExpiredWithoutAssociations($user, $delivery['id'], $debug);
    }

    private function _createPdfUserDelivery($user, $delivery, $debug) {

    	$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $delivery['organization_id']]);

        $delivery_id = $delivery['id'];
        $delivery_data = $delivery['data'];

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        App::import('Model', 'SummaryOrderPlu');
        $SummaryOrderPlu = new SummaryOrderPlu;

        App::import('Model', 'User');
        $User = new User;

        App::import('Model', 'PdfCart');
        App::import('Model', 'PdfCartsOrder');

        $conditions = ['Delivery.id' => $delivery_id];
        $orderBy = Configure::read('orderUser');
        $userResults = $User->getUserWithCartByDelivery($tmp_user, $conditions, $orderBy, 'CRON');

        self::d("_createPdfUserDelivery() per consegna " . $delivery_data . " (" . $delivery_id . "): trovati " . count($userResults) . " users che hanno effettuato acquisti", $debug);

        foreach ($userResults as $numResult => $userResult) {
            
            $tot_importo = 0;
            $user_id = $userResult['User']['id'];
            
			self::d("Tratto user " . $userResult['User']['name'] . " (" . $userResult['User']['id'] . ")", $debug);





            //$results = $Cart->getUserCart($tmp_user, $user_id, $delivery_id, $debug);
            $Delivery = new Delivery;

            $conditions = ['Delivery' => ['Delivery.isVisibleBackOffice' => 'Y',
										  'Delivery.id' => (int) $delivery_id],
						   'Cart' => ['Cart.user_id' => (int) $userResult['User']['id'],
									  'Cart.deleteToReferent' => 'N']];

            $options = ['orders' => true, 'storerooms' => false, 'summaryOrders' => false,
                'articoliDellUtenteInOrdine' => true, // estraggo SOLO gli articoli acquistati da un utente in base all'ordine
                'suppliers' => true, 'referents' => false];

            self::d("Cart::getUserCart()", $debug);
            self::d($conditions, $debug);
		
            $results = $Delivery->getDataWithoutTabs($user, $conditions, $options);






           self::d("\rTotale acquisti per lo user " . $userResult['User']['name'] . " (" . $userResult['User']['id'] . "): " . count($results), $debug);

            $resultsWithModifies = [];

            if (isset($results['Delivery']))
                foreach ($results['Delivery'] as $numDelivery => $result['Delivery']) {

                    if ($result['Delivery']['totOrders'] > 0 && $result['Delivery']['totArticlesOrder'] > 0) {

                        foreach ($result['Delivery']['Order'] as $numOrder => $order) {
                            $resultsSummaryOrderPlus = $SummaryOrderPlu->addSummaryOrder($this->user, $order, $user_id);

                            $resultsWithModifies[$order['Order']['id']]['SummaryOrder'] = $resultsSummaryOrderPlus['SummaryOrder'];
                            $resultsWithModifies[$order['Order']['id']]['SummaryOrderTrasport'] = $resultsSummaryOrderPlus['SummaryOrderTrasport'];
                            $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostMore'] = $resultsSummaryOrderPlus['SummaryOrderCostMore'];
                            $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostLess'] = $resultsSummaryOrderPlus['SummaryOrderCostLess'];
                        } // loops Orders
                    }
                } // loops Deliveries	   
                
            /*
             * view user_cart
             */
            $output = new XTCPDF($tmp_user->organization, PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $output->headerText = "Consegna del " . $this->_translateData($delivery_data);

            // add a page
            $output->AddPage();
            $css = $output->getCss();


            if (isset($results['Delivery'])) {
                foreach ($results['Delivery'] as $numDelivery => $result['Delivery']) {

                    $html = '<div class="h1Pdf">' . __('Delivery') . ' ';
                    if ($delivery['sys'] == 'N')
                        $html .= $delivery['luogoData'];
                    else
                        $html .= $delivery['luogo'];
                    $html .= '</div>';
                    $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

                    if ($result['Delivery']['totOrders'] > 0 && $result['Delivery']['totArticlesOrder'] > 0) {

                        $tot_importo = 0;  /* il totale di tutta la consegna */
                        $tot_importo_cost_less = 0;
                        $tot_importo_trasport = 0;
                        $tot_importo_cost_more = 0;

                        $array_suppliers_organizations = [];

                        foreach ($result['Delivery']['Order'] as $numOrder => $order) {

                            if (isset($order['ArticlesOrder'])) { // cosi' escludo gli ordini senza acquisti
                                $array_suppliers_organizations[$numOrder]['id'] = $order['SuppliersOrganization']['id'];
                                $array_suppliers_organizations[$numOrder]['name'] = $order['SuppliersOrganization']['name'];
                                $array_suppliers_organizations[$numOrder]['supplier_id'] = $order['SuppliersOrganization']['supplier_id'];
                                $array_suppliers_organizations[$numOrder]['supplier_img1'] = $order['SuppliersOrganization']['img1'];

                                $html = '<div class="h2Pdf">' . __('Supplier') . ' ' . $order['SuppliersOrganization']['name'] . ', ' . $order['SuppliersOrganization']['descrizione'] . '</div>';
                                $output->writeHTML($css . $html, $ln = false, $fill = false, $reseth = true, $cell = true, $align = '');

                                $html = '';
                                $html .= '	<table cellpadding="0" cellspacing="0">';
                                $html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
                                $html .= '		<tr>';
                                $html .= '			<th width="' . $output->getCELLWIDTH20() . '">' . __('N') . '</th>';
                                $html .= '			<th width="' . $output->getCELLWIDTH30() . '">' . __('Bio') . '</th>';
                                $html .= '			<th width="' . ($output->getCELLWIDTH300() + $output->getCELLWIDTH20()) . '">' . __('Name') . '</th>';
                                $html .= '			<th width="' . $output->getCELLWIDTH50() . '" style="text-align:center;">' . __('qta') . '</th>';
                                $html .= '			<th width="' . $output->getCELLWIDTH70() . '" style="text-align:center;">&nbsp;' . __('PrezzoUnita') . '</th>';
                                $html .= '			<th width="' . $output->getCELLWIDTH70() . '">' . __('Prezzo/UM') . '</th>';
                                $html .= '			<th width="' . $output->getCELLWIDTH70() . '" style="text-align:right;">' . __('Importo') . '</th>';
                                $html .= '	</tr>';
                                $html .= '	</thead><tbody>';



                                $tot_qta = 0;
                                $tot_importo_sub = 0;  /* il totale di un ordine */
                                foreach ($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {

                                    $name = $order['ArticlesOrder'][$numArticlesOrder]['name'] . ' ' . $this->_getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']);

                                    /*
                                     * gestione qta e importi
                                     * */
                                    if ($order['Cart'][$numArticlesOrder]['qta_forzato'] > 0) {
                                        $qta = $order['Cart'][$numArticlesOrder]['qta_forzato'];
                                        $qta_modificata = true;
                                    } else {
                                        $qta = $order['Cart'][$numArticlesOrder]['qta'];
                                        $qta_modificata = false;
                                    }
                                    $importo_modificato = false;
                                    if ($order['Cart'][$numArticlesOrder]['importo_forzato'] == 0) {
                                        if ($order['Cart'][$numArticlesOrder]['qta_forzato'] > 0)
                                            $importo = ($order['Cart'][$numArticlesOrder]['qta_forzato'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
                                        else {
                                            $importo = ($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
                                        }
                                    } else {
                                        $importo = $order['Cart'][$numArticlesOrder]['importo_forzato'];
                                        $importo_modificato = true;
                                    }

                                    $tot_qta += $qta;
                                    $tot_importo_sub += $importo;

                                    $importo = number_format($importo, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));

                                    $html .= '<tr>';
                                    $html .= '	<td width="' . $output->getCELLWIDTH20() . '">' . ($numArticlesOrder + 1) . '</td>';
                                    $html .= '	<td width="' . $output->getCELLWIDTH30() . '">';
                                    if ($order['Article'][$numArticlesOrder]['bio'] == 'Y')
                                        $html .= 'Bio';
                                    $html .= '</td>';
                                    $html .= '<td width="' . ($output->getCELLWIDTH300() + $output->getCELLWIDTH20()) . '">' . $name . '</td>';
                                    $html .= '<td width="' . $output->getCELLWIDTH50() . '" style="text-align:center;">' . $qta . '</td>';  // $this->App->traslateQtaImportoModificati($qta_modificata)
                                    $html .= '<td width="' . $output->getCELLWIDTH70() . '" style="text-align:center;">' . $order['ArticlesOrder'][$numArticlesOrder]['prezzo_e'] . '</td>';
                                    $html .= '<td width="' . $output->getCELLWIDTH70() . '">' . $this->utilsCommons->getArticlePrezzoUM($order['ArticlesOrder'][$numArticlesOrder]['prezzo'], $order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um'], $order['Article'][$numArticlesOrder]['um_riferimento']) . '</td>';
                                    $html .= '<td width="' . $output->getCELLWIDTH70() . '" style="text-align:right;">';
                                    $html .= $importo . '&nbsp;&euro;'; // $this->App->traslateQtaImportoModificati($importo_modificato);
                                    $html .= '</td>';
                                    $html .= '</tr>';
                                }  // end ciclo ArticlesOrder

                                $tot_importo += $tot_importo_sub;

                                $html .= '<tr>';
                                $html .= '	<th></th>';
                                $html .= '	<th colspan="2" style="text-align:right;">Quantit&agrave;&nbsp;totale&nbsp;</th>';
                                $html .= '	<th style="text-align:center;">&nbsp;' . $tot_qta . '</th>';
                                $html .= '	<th colspan="3" style="text-align:right;">Importo totale&nbsp;' . number_format($tot_importo_sub, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                                $html .= '</tr>';

                                /*
                                 * ctrl se ci sono variazioni in
                                 *
                                 * SummaryOrder
                                 * SummaryOrderTrasport
                                 * SummaryOrderCostMore
                                 * SummaryOrderCostLess
                                 *
                                 * $resultsWithModifies[order_id][SummaryOrder][0][SummaryOrder][importo] e' la somma di SummaryOrderTrasport + SummaryOrderCostMore + SummaryOrderCostLess
                                 */
                                if (array_key_exists($order['Order']['id'], $resultsWithModifies)) {

                                    $resultsWithModifiesOrder = $resultsWithModifies[$order['Order']['id']];
                                    /*
                                      echo "<pre>";
                                      print_r($resultsWithModifiesOrder);
                                      echo "</pre>";
                                     */
                                    if (isset($resultsWithModifiesOrder['SummaryOrderTrasport'][0])) {

                                        $importo_trasport = $resultsWithModifiesOrder['SummaryOrderTrasport'][0]['SummaryOrderTrasport']['importo_trasport'];
                                        // echo '<br />importo_trasport '.$importo_trasport;

                                        if ($importo_trasport > 0) {
                                            $html .= '<tr>';
                                            $html .= '	<th></th>';
                                            $html .= '	<th colspan="2" style="text-align:right;"></th>';
                                            $html .= '	<th style="text-align:center;"></th>';
                                            $html .= '	<th colspan="3" style="text-align:right;">Trasporto&nbsp;&nbsp;' . number_format($importo_trasport, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                                            $html .= '</tr>';
                                        }

                                        $tot_importo_trasport += $importo_trasport;
                                        // echo '<br />tot_importo_trasport '.$tot_importo_trasport;
                                    }
                                    if (isset($resultsWithModifiesOrder['SummaryOrderCostMore'][0])) {

                                        $importo_cost_more = $resultsWithModifiesOrder['SummaryOrderCostMore'][0]['SummaryOrderCostMore']['importo_cost_more'];

                                        if ($importo_cost_more > 0) {
                                            $html .= '<tr>';
                                            $html .= '	<th></th>';
                                            $html .= '	<th colspan="2" style="text-align:right;"></th>';
                                            $html .= '	<th style="text-align:center;"></th>';
                                            $html .= '	<th colspan="3" style="text-align:right;">Costo aggiuntivo&nbsp;&nbsp;' . number_format($importo_cost_more, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                                            $html .= '</tr>';
                                        }

                                        $tot_importo_cost_more += $importo_cost_more;
                                    }
                                    if (isset($resultsWithModifiesOrder['SummaryOrderCostLess'][0])) {

                                        $importo_cost_less = $resultsWithModifiesOrder['SummaryOrderCostLess'][0]['SummaryOrderCostLess']['importo_cost_less'];

                                        if ($importo_cost_less != 0) {
                                            $html .= '<tr>';
                                            $html .= '	<th></th>';
                                            $html .= '	<th colspan="2" style="text-align:right;"></th>';
                                            $html .= '	<th style="text-align:center;"></th>';
                                            $html .= '	<th colspan="3" style="text-align:right;">Sconto&nbsp;&nbsp;' . number_format($importo_cost_less, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                                            $html .= '</tr>';
                                        }

                                        $tot_importo_cost_less += $importo_cost_less;
                                    }

                                    if (isset($resultsWithModifiesOrder['SummaryOrder'][0])) {

                                        $importo = $resultsWithModifiesOrder['SummaryOrder'][0]['SummaryOrder']['importo'];
                                        // echo '<br />importo '.$importo;

                                        if ($importo > 0) {
                                            $html .= '<tr>';
                                            $html .= '	<th></th>';
                                            $html .= '	<th colspan="6" style="text-align:right;">';

                                            if ($importo_trasport == 0 && $importo_cost_less == 0 && $importo_cost_more == 0) {
                                                $html .= 'Totale dell\'ordine&nbsp;modificato&nbsp;dal&nbsp;referente&nbsp;';
                                                /*
                                                 * l'importo dell'ordine e' stato modificato con l'aggregazione dei dati, tolgo il vecchio e agginugo il nuovo
                                                 */
                                                $tot_importo = ($tot_importo - $tot_importo_sub + $importo);
                                            } else
                                                $html .= 'Totale dell\'ordine&nbsp;&nbsp;';
                                            $html .= number_format($importo, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                                            $html .= '</tr>';

                                            $array_suppliers_organizations[$numOrder]['order_importo'] = $importo;
                                        }
                                    }
                                    else {
                                        $array_suppliers_organizations[$numOrder]['order_importo'] = $tot_importo_sub;
                                    }
                                }

                                $html .= '</tbody></table>';
                                $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
                            }  // end if(isset($order['ArticlesOrder']))  	
                        }  // end foreach($result['Delivery']['Order'] as $numOrder => $order)


                        /*
                         * totale importo della consegna
                         */
                        $tot_importo = ($tot_importo + $tot_importo_trasport + ($tot_importo_cost_less) + $tot_importo_cost_more);

                        $html = '';
                        $html .= '	<table cellpadding="0" cellspacing="0">';
                        $html .= '	<tbody>';
                        $html .= '<tr>';
                        $html .= '	<td colspan="7"></td>';
                        $html .= '</tr>';

                        $html .= '<tr>';
                        $html .= '	<th colspan="7" style="text-align:right;">';
                        $html .= number_format($tot_importo, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;';
                        $html .= '</th>';
                        $html .= '</tr>';

                        $html .= '</tbody></table>';
                        $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
                    } else {

                        if ($storeroomResults['Delivery'][$numDelivery]['totStorerooms'] == 0) {
                            $html = '<div class="h4PdfNotFound">' . __('export_docs_not_found') . '</div>';
                            $output->writeHTMLCell(0, 0, 15, 40, $css . $html, $border = 0, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
                        }
                    }
                } // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 

                $html = '';
                $html = $output->getLegenda();
                $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

                // reset pointer to the last page
                $output->lastPage();

                $fileData['fileName'] = uniqid();

                /*
                 * scrivo fisicamente il file
                 */
                $path = $this->_directoryTargetUserPdf($delivery['organization_id'], $user_id, $debug);
                self::d("Scrivo il file $path" . DS . $fileData['fileName'] . '.pdf', $debug);

                $output->Output($path . DS . $fileData['fileName'] . '.pdf', 'F');
                $output = null;

                /*
                 * scrivo sul db
                 */
                $PdfCart = new PdfCart;
                $data = [];
                $data['PdfCart']['organization_id'] = $delivery['organization_id'];
                $data['PdfCart']['user_id'] = $user_id;
                $data['PdfCart']['uuid'] = $fileData['fileName'];
                $data['PdfCart']['name'] = $this->_translateData($delivery_data);
                $data['PdfCart']['delivery_id'] = $delivery['id'];
                $data['PdfCart']['delivery_data'] = $delivery_data;
                $data['PdfCart']['delivery_luogo'] = $delivery['luogo'];
                $data['PdfCart']['delivery_importo'] = $tot_importo;
                //self::d($data, $debug);

                if (!$PdfCart->save($data)) {
                    self::d("ERRORE PdfCart::save()", $debug);
					self::d($data, $debug);
                }
                $pdf_cart_id = $PdfCart->getLastInsertId();

                 if(!empty($array_suppliers_organizations))
                foreach ($array_suppliers_organizations as $array_suppliers_organization) {

                    $PdfCartsOrder = new PdfCartsOrder;
                    $data = [];
                    $data['PdfCartsOrder']['organization_id'] = $delivery['organization_id'];
                    $data['PdfCartsOrder']['user_id'] = $user_id;
                    $data['PdfCartsOrder']['pdf_cart_id'] = $pdf_cart_id;
                    $data['PdfCartsOrder']['supplier_id'] = $array_suppliers_organization['supplier_id'];
                    $data['PdfCartsOrder']['supplier_img1'] = $array_suppliers_organization['supplier_img1'];
                    $data['PdfCartsOrder']['supplier_organizations_id'] = $array_suppliers_organization['id'];
                    $data['PdfCartsOrder']['supplier_organizations_name'] = $array_suppliers_organization['name'];
                    $data['PdfCartsOrder']['order_importo'] = $array_suppliers_organization['order_importo'];
                    /*
                      echo "<pre>PdfCartsOrder \n";
                      print_r($data);
                      echo "</pre>";
                     */
                    if (!$PdfCartsOrder->save($data)) {
						self::d("ERRORE PdfCartsOrder::save()", $debug);
						self::d($data, $debug);
                    }
                }
                $array_suppliers_organizations = [];
            }

            /*
              if($numResult==2)
              exit;
             */
        } // loop user
    }

    private function _translateData($data) {
        if (!empty($data))
            $data = date('d', strtotime($data)) . '/' . date('m', strtotime($data)) . '/' . date('Y', strtotime($data));

        return $data;
    }

    /*
     * ctrl se la direcotry esiste, se no la creo
     */

    private function _directoryTargetUserPdf($organization_id, $user_id, $debug) {
        $path = Configure::read('App.root') . Configure::read('App.img.upload.pdf.carts') . DS . $organization_id . DS . $user_id;
        $dir = new Folder($path, true, 0755);

        return $path;
    }

    /*
     * code AppHelper
     */

    private function _getArticleConf($qta, $um) {

        /*
         * qta, da 1.00 a 1
         * 		da 0.75 a 0,75
         * */
        $qta = str_replace(".", ",", $qta);
        if (strpos($qta, ',') !== false) {
            $arrCtrlTwoZero = explode(",", $qta);
            if ($arrCtrlTwoZero[1] == '00')
                $qta = $arrCtrlTwoZero[0];
        }

        // $um = $this->traslateEnum($um);

        $tmp = "";
        $tmp .= $qta . '&nbsp;' . $um;

        return $tmp;
    }

    public function create_pdf_single_user($user, $delivery_id, $user_id, $debug = true) {

        //$delivery_id = '1273';
        //$user_id = '202';

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        App::import('Model', 'Cart');
        $Cart = new Cart;

        $conditions = ['Delivery' => ['Delivery.isVisibleBackOffice' => 'Y',
									  'Delivery.id' => (int) $delivery_id],
					   'Cart' => ['Cart.user_id' => (int) $user_id,
									'Cart.deleteToReferent' => 'N']];

        $options = ['orders' => true, 'storerooms' => false, 'summaryOrders' => false,
						'articoliDellUtenteInOrdine' => true, // estraggo SOLO gli articoli acquistati da un utente in base all'ordine
						'suppliers' => true, 'referents' => false];

        self::d("organization_id " . $user->organization['Organization']['id'], $debug);
        self::d("delivery_id " . $delivery_id, $debug);
        self::d("user_id " . $user_id, $debug);
	
		$results = $Delivery->getDataWithoutTabs($user, $conditions, $options);
        self::d($results, $debug);
    }
}