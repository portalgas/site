<?php
App::uses('AppController', 'Controller');

class DocsController extends AppController {

    public $helpers = ['Tabs'];
    public $components = ['ActionsDesOrder'];

    public function beforeFilter() {
        parent::beforeFilter();

        $actionWithPermission =  ['admin_referentDocsExport', 'admin_referentDocsExportHistory', 'admin_tesoriereDocsExport', 'admin_cassiere_docs_export', 'admin_cassiere_delivery_docs_export'];
        if (in_array($this->action, $actionWithPermission)) {
            /*
             * ctrl che la consegna sia visibile in backoffice
             */
            if (!empty($this->delivery_id)) {

                App::import('Model', 'Delivery');
                $Delivery = new Delivery;
                $results = $Delivery->read($this->user->organization['Organization']['id'], null, $this->delivery_id);
                if (!empty($results) && $results['Delivery']['isVisibleBackOffice'] == 'N') {
                    $this->Session->setFlash(__('msg_delivery_not_visible_backoffice'));
                    $this->myRedirect(Configure::read('routes_msg_stop'));
                }
            }

            /*
             * ctrl che l'ordine sia visibile in backoffice
             */
            if (!empty($this->order_id)) {

                App::import('Model', 'Order');
                $Order = new Order;
                $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
                if (!empty($results) && $results['Order']['isVisibleBackOffice'] == 'N') {
                    $this->Session->setFlash(__('msg_order_not_visible_backoffice'));
                    $this->myRedirect(Configure::read('routes_msg_stop'));
                }
            }
        } // end if (in_array($this->action, $actionWithPermission))		
    }

    /*
     * se arrivo da Orders/admin_index.ctp $delivery_id, $order_id sono valorizzati
     * */

    public function admin_referentDocsExport() {

        if (empty($this->delivery_id) || empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Order');
        $Order = new Order;

        /* ctrl ACL */
        if ($this->isSuperReferente()) {
            
        } else {
            if (!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
                $this->Session->setFlash(__('msg_not_permission'));
                $this->myRedirect(Configure::read('routes_msg_stop'));
            }
        }

        $options = [];
        $options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
        $this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
    }

    /*
     * isReferentDesAllGasDesSupplier: lo user e' referente del produttore: potra' visualizzare gli ordini dei GAS
     * */

    public function admin_referentDesAllGasDocsExport($des_order_id, $organization_id, $delivery_id, $order_id) {

        if (empty($des_order_id) || empty($delivery_id) || empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /* ctrl ACL */
        if (!$this->isReferentDesAllGas()) {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        if (!$this->ActionsDesOrder->isReferentDesAllGasDesOrder($this->user, $organization_id, $order_id)) {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $options = [];
        $options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];

        $tmp_user->organization['Organization']['id'] = $organization_id;
        $this->_boxOrder($tmp_user, $delivery_id, $order_id, $options);

        $this->set('des_order_id', $des_order_id);
        $this->set('organization_id', $organization_id);        
		$this->set('delivery_id', $delivery_id);
        $this->set('order_id', $order_id);
    }

	
    /*
     * il produttore: potra' visualizzare gli ordini dei GAS associati
     * */
    public function admin_prodGasSupplierDocsExport($organization_id, $delivery_id, $order_id) {

		$debug=false;
	
        if (empty($organization_id) || empty($delivery_id) || empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

		// ACL 
		
		/*
		 * estraggo il filtersOwnerArticles SUPPLIER / REFERENT / DES
		 */		
		App::import('Model', 'Order');
		$Order = new Order;

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $organization_id,
								  'Order.delivery_id' => $delivery_id,
								  'Order.id' => $order_id];
		$options['fields'] = ['SuppliersOrganization.owner_articles'];
		$options['recursive'] = 1;
		$Order->unbindModel(['belongsTo' => ['Delivery']]);
		$orderResults = $Order->find('first', $options);
		$owner_articles = $orderResults['SuppliersOrganization']['owner_articles'];
		self::d($orderResults, $debug);

		App::import('Model', 'ProdGasSuppliersImport');
		$ProdGasSuppliersImport = new ProdGasSuppliersImport;		 
		 
		 
		// precedente versione $organizationResults = $ProdGasSupplier->getOrganizationAssociate($this->user, $organization_id, 0, $debug);
		$organizationResults = $ProdGasSuppliersImport->getProdGasSuppliers($this->user, $this->user->organization['Organization']['id'], $organization_id, [$owner_articles], $debug);
		
		$currentOrganization = $organizationResults['Supplier']['Organization'];
		$currentOrganization = current($currentOrganization);
		self::d($currentOrganization, $debug);
		
		if($currentOrganization['SuppliersOrganization']['can_view_orders']!='Y' && $currentOrganization['SuppliersOrganization']['can_view_orders_users']!='Y') {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		// ACL
		
        $options = [];
        $options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];

        $tmp_user->organization['Organization']['id'] = $organization_id;
        $this->_boxOrder($tmp_user, $delivery_id, $order_id, $options);

        $this->set('can_view_orders', $currentOrganization['SuppliersOrganization']['can_view_orders']);
        $this->set('can_view_orders_users', $currentOrganization['SuppliersOrganization']['can_view_orders_users']);
		$this->set('organization_id', $organization_id);
		$this->set('delivery_id', $delivery_id);
        $this->set('order_id', $order_id);
    }
	
    /*
     * se arrivo da Orders/admin_index_history.ctp $delivery_id, $order_id sono valorizzati
     * 'Doc.stato_elaborazione' => 'CLOSE'
     * */

    public function admin_referentDocsExportHistory() {

        if (empty($this->delivery_id) || empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /* ctrl ACL */
        if ($this->isSuperReferente()) {
            
        } else {
            App::import('Model', 'Order');
            $Order = new Order;
            if (!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
                $this->Session->setFlash(__('msg_not_permission'));
                $this->myRedirect(Configure::read('routes_msg_stop'));
            }
        }

        $options = [];
        //$options['conditions'] = ['Delivery.stato_elaborazione' => 'CLOSE'];
        $options['conditions'] = ['Order.state_code' => 'CLOSE'];
        $this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);

        $this->render('/Docs/admin_referent_docs_export');
    }

    /*
     * se lo richiamo dal menu laterale delivery_id e' valorizzato
     */

    public function admin_tesoriereDocsExport() {

        /* ctrl ACL */
        if (!$this->isTesoriereGeneric()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

		$options = [];
        $options['conditions'] = ['Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
									'Delivery.isVisibleBackOffice' => 'Y',
									'Delivery.sys' => 'N',
									'Delivery.stato_elaborazione' => 'OPEN'];
		$options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$options['order'] = ['Delivery.data' => 'asc'];
		$options['recursive'] = -1;
        $deliveries = $Delivery->find('list', $options);
        if (empty($deliveries)) {
            $this->Session->setFlash(__('NotFoundDeliveries'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $this->set(compact('deliveries'));


        $this->set('order_state_code_checked', 'PROCESSED-TESORIERE');
    }

    public function admin_cassiere_docs_export() {
        if (!$this->isCassiereGeneric()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        App::import('Model', 'Cassiere');
        $Cassiere = new Cassiere;

        $deliveries = $Cassiere->get_cassiere_deliveries($this->user, $this->isCassiere(), $this->isReferentCassiere());
        $this->set(compact('deliveries'));
    }

    /*
     * elenco delle consegne con ordini Order.state_code == 'PROCESSED-ON-DELIVERY' (in carico al Cassiere)
     * 
     * dopo Sumbit,
     *  per quell'utente pago tutti gli ordini passati, 
     *  se $tot_importo_da_pagare < o > differenza in cassa
     */

    public function admin_cassiere_delivery_docs_export() {

        $debug = false;
        $debug_save = false; // se true NON salva
		
		App::import('Model', 'SummaryOrderLifeCycle');
		$SummaryOrderLifeCycle = new SummaryOrderLifeCycle;
		
		/*
		if($this->user->organization['Organization']['id']==33) { // pastamadristiromani
			$debug = true;
			$debug_save = true; // se true NON salva			
		}
		*/
		
		self::d($this->request->data,$debug);
		
        $delivery_id = 0;
        $user_id = 0;

        /*
         * salvo i pagamenti di un utente
         */
        if (!empty($this->request->data['Cassiere'])) {

            $delivery_id = $this->request->data['Cassiere']['delivery_id'];
            $user_id = $this->request->data['user_id'];

            /*
             * importo che avrebbe dovuto pagare
             */
            $tot_importo_dovuto = $this->request->data['tot_importo_dovuto'];
            
            /*
             * importo che paga realmente
             */
            $tot_importo_da_pagare = $this->request->data['Cassiere']['tot_importo_da_pagare'];

            $tot_importo_cassa = $this->request->data['tot_importo_cassa'];
            $cash_options = $this->request->data['cash_options'];
            $cash_text = $this->request->data['cash_text'];
            $differenza_in_cassa = $this->request->data['differenza_in_cassa'];

            unset($this->request->data['Cassiere']['delivery_id']);
            unset($this->request->data['user_id']);
            unset($this->request->data['Cassiere']['tot_importo_da_pagare']);
            unset($this->request->data['tot_importo_cassa']);
            unset($this->request->data['cash_options']);
            unset($this->request->data['cash_text']);
            unset($this->request->data['differenza_in_cassa']);



            /*
             *  ciclo per ogni ordine nello stato "in carico al cassiere"
             *  valorizzo SummaryOrder come pagato
             */
            foreach ($this->request->data['Cassiere'] as $key => $data) {

                $summary_order_id = $key;

                $order_id = $data['order_id'];
                $importo = $data['importo'];
                $modalita = $data['modalita'];
                if (empty($modalita))
                    $modalita = 'CONTANTI';

                $data = [];
                $data['SummaryOrder']['id'] = $summary_order_id;
                $data['SummaryOrder']['organization_id'] = $this->user->organization['Organization']['id'];
                $data['SummaryOrder']['order_id'] = $order_id;
                $data['SummaryOrder']['delivery_id'] = $delivery_id;
                $data['SummaryOrder']['user_id'] = $user_id;
                $data['SummaryOrder']['importo_pagato'] = $importo;
                $data['SummaryOrder']['modalita'] = $modalita;
                $data['SummaryOrder']['saldato_a'] = 'CASSIERE';

				if(!$debug_save) {
	                /*
	                 * aggiorno lo stato dell'ordine: se tutti hanno pagato (SummaryOrder.saldato_a) Order.state_code a state_code successivo
					 */				
					if(!$SummaryOrderLifeCycle->saveToUser($this->user, $data, $debug))
						$this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
					else
						$this->Session->setFlash(__('The cash has been saved'));
				}

                /*
                 *  salvo importo POS
                 */
                if ($this->user->organization['Organization']['hasFieldPaymentPos'] == 'Y') {
                    if ($modalita == 'BANCOMAT' && $data['paymentPos'] != '0,00') {
                        $paymentPos = $data['paymentPos'];

                        App::import('Model', 'SummaryDeliveriesPos');
                        $SummaryDeliveriesPos = new SummaryDeliveriesPos;

                        if ($SummaryDeliveriesPos->saveToDelivery($this->user, $delivery_id, $user_id, $this->importoToDatabase($paymentPos), $debug))
                            $this->Session->setFlash(__('The SummaryDeliveriesPos has been saved'));
                        else
                            $this->Session->setFlash(__('The SummaryDeliveriesPos could not be saved. Please, try again.'));
                    } // end if($modalita=='BANCOMAT' && $paymentPos!='0,00') 					
                } // end if($this->user->organization['Organization']['hasFieldPaymentPos']=='Y') 

            } // end foreach($this->request->data['Cassiere'] as $key => $data)

            /*
             * CASSA 
             * se differenza_in_cassa == 0  DELETE 
             * se differenza_in_cassa != 0  UPDATE/INSERT 
             */
			$diff = ($tot_importo_dovuto - $tot_importo_da_pagare);
            if($diff==0) 
            	$creoMovimentoDiCassa = false;
            else	
           		$creoMovimentoDiCassa = true;

            $tot_importo_da_pagare = $this->importoToDatabase($tot_importo_da_pagare);
            
		    self::d('cash_options ['.$cash_options.'] se Y gestisco la CASSA, se no salto', $debug);
        
            if ($cash_options == 'Y') {

                App::import('Model', 'Cash');
                $Cash = new Cash;

                self::d('creoMovimentoDiCassa se  tot_importo_dovuto '.$tot_importo_dovuto.' != tot_importo_da_pagare '.$tot_importo_da_pagare.' =>  UPDATE/INSERT', $debug);
				
				if($creoMovimentoDiCassa || ($tot_importo_cassa != $differenza_in_cassa)) {
		             				
                    /*
                     *  aggiorno
                     */
                    $row = [];
                    $row['Cash']['organization_id'] = $this->user->organization['Organization']['id'];
                    $row['Cash']['user_id'] = $user_id;
                    $row['Cash']['importo'] = $this->importoToDatabase($differenza_in_cassa);
                    $row['Cash']['nota'] = $cash_text;

                    /*
                     *   ctrl se insert / update 
                     */
                    $options = [];
                    $options['conditions'] = ['Cash.organization_id' => $this->user->organization['Organization']['id'],
                                              'Cash.user_id' => $user_id];
                    $options['recursive'] = -1;
                    $ctrlCashResults = $Cash->find('first', $options);
                    if (!empty($ctrlCashResults)) {
                    	/*
                    	 * UPDATE
                    	 */
                        $row['Cash']['id'] = $ctrlCashResults['Cash']['id'];

						/*
						 * dati Cash precedenti in CashesHistory
						 */
						App::import('Model', 'CashesHistory');
				        $CashesHistory = new CashesHistory;
						
						$CashesHistory->previousCashSave($this->user, $ctrlCashResults['Cash']['id']);
				        	
					}
					
					self::d($row,$debug);

                    $Cash->create();
                    if(!$debug_save) {
                    if ($Cash->save($row)) 
                        $this->Session->setFlash(__('The cash has been saved'));
                    else 
                        $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
                    }
               } // end if ($differenza_in_cassa != '0,00')
				/*     
				 * DELETE, non cancello + perche' ho CashesHistory
                } else {

                    if ($debug)
                        echo '<br />differenza_in_cassa (' . $differenza_in_cassa . ') == 0,00  DELETE';

                    //  delete
                     
                    $options = [];
                    $options['conditions'] = ['Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
                                                'Cash.user_id' => $user_id];
                    $options['fields'] = ['Cash.id'];
                    $options['recursive'] = -1;
                    $results = $Cash->find('first', $options);

                    if (!empty($results)) {
                        if(!$debug_save) {
                            if ($Cash->delete($results['Cash']['id'])) 
                                $this->Session->setFlash(__('The cash has been saved'));
                            else 
                                $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
                        }
                    }
                }
 				*/
            } // end if($cash_options=='Y') 
        }  // end post

        if (!$this->isCassiereGeneric()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        App::import('Model', 'Cassiere');
        $Cassiere = new Cassiere;

        $deliveries = $Cassiere->get_cassiere_deliveries($this->user, $this->isCassiere(), $this->isReferentCassiere());
        
		self::d($deliveries,$debug);
        
		/*
		 * dispensa
		*/
		if($this->user->organization['Organization']['hasStoreroom']=='Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {

			App::import('Model', 'Storeroom');
			$Storeroom = new Storeroom;
		
			$storeroomDeliveries = $Storeroom->deliveriesToRequestPayment($this->user);
			
			self::d($storeroomDeliveries,$debug);
		
			if(!empty($storeroomDeliveries)) {
				foreach($storeroomDeliveries as $key => $label) {
					if (!array_key_exists($key, $deliveries))
						$deliveries[$key] = $label;
				}
			} 
		}

        $this->set(compact('deliveries'));

        $this->set(compact('delivery_id', 'user_id'));
    }

    public function admin_des_referent_docs_export($des_order_id) {

        $debug = false;

		if(!isset($this->user->des_id) || empty($this->user->des_id)) { 
            $this->Session->setFlash(__('msg_des_not_selected'));
			$this->myRedirect(['controller' => 'Des', 'action' => 'index']);
		}
		
        if (empty($des_order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         *  tutti i dati del DesOrder
         */
        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder();

        $desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
        
		$this->set(compact('desOrdersResults'));
        $this->set('desDesOrderResults', $desDesOrderResults);
        $this->set('des_order_id', $des_order_id);

        /*
         * rispetto all'ordine del titolare ctrl se il suo ordine 
         * 		dev'essere validato (ArticlesOrder.pezzi_confezione > 1) per la gestione dei colli
         */
        $order_id = 0;
		if(isset($desOrdersResults['DesOrdersOrganizations']))
        foreach ($desOrdersResults['DesOrdersOrganizations'] as $desOrdersOrganization) {
            /*
             * estraggo l'order_id del titolare
             */
            if ($desOrdersOrganization['DesOrdersOrganization']['organization_id'] == $this->user->organization['Organization']['id'])
                $order_id = $desOrdersOrganization['DesOrdersOrganization']['order_id'];
        }

        $toQtaMassima = false;
        $toQtaMinimaOrder = false;
        $isToValidate = false;

        if ($order_id > 0) {
            App::import('Model', 'Order');
            $Order = new Order;

            /*
             * ctrl se l'ordine dev'essere validato (ArticlesOrder.pezzi_confezione > 1) per la gestione dei colli
             */
            if ($Order->isOrderToValidate($this->user, $order_id)) {
                if ($debug)
                    echo '<br />OrderId '.$order_id.' toValidate (ArticlesOrder.pezzi_confezione > 1) = Y ';
                $isToValidate = true;
            }
            else {
                if ($debug)
                    echo '<br />OrderId '.$order_id.' toValidate (ArticlesOrder.pezzi_confezione > 1) = N ';
                $isToValidate = false;
            }

            /*
             * ctrl se l'ordine ha settato delle quantita' massime > 0
             */
            if ($Order->isOrderToQtaMassima($this->user, $order_id)) {
                if ($debug)
                    echo '<br />OrderId '.$order_id.' ToQtaMassima (ArticlesOrder.qta_massima_order > 1) = Y ';
                $toQtaMassima = true;
            }
            else {
                if ($debug)
                    echo '<br />OrderId '.$order_id.' ToQtaMassima (ArticlesOrder.qta_massima_order > 1) = N ';
                $toQtaMassima = false;
            }

            /*
             * ctrl se l'ordine ha settato delle quantita' minime sugli acquisti di tutto l'ordine > 0
             * (come in PageController::index per ExportDocs/referent_to_articles_monitoring.ctp)
             */
            if ($Order->isOrderToQtaMinimaOrder($this->user, $order_id)) {
                if ($debug)
                    echo '<br />OrderId '.$order_id.' isOrderToQtaMinimaOrder (ArticlesOrder.qta_minima_order > 1) = Y ';
                $toQtaMinimaOrder = true;
            }
            else {
                if ($debug)
                    echo '<br />OrderId '.$order_id.' ToQtaMinimaOrder (ArticlesOrder.qta_minima_order > 1) = N ';
                $toQtaMinimaOrder = false;
            }
        }

        $this->set('toQtaMassima', $toQtaMassima);
        $this->set('toQtaMinimaOrder', $toQtaMinimaOrder);
        $this->set('isToValidate', $isToValidate);

        $this->render('/Docs/admin_des_referent_docs_export');
    }

}
