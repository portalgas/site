<?php

App::uses('AppController', 'Controller');

class DocsController extends AppController {

    public $helpers = array('Tabs');
    public $components = array('ActionsDesOrder');

    public function beforeFilter() {
        parent::beforeFilter();

        $actionWithPermission = array('admin_referentDocsExport', 'admin_referentDocsExportHistory', 'admin_tesoriereDocsExport', 'admin_cassiere_docs_export', 'admin_cassiere_delivery_docs_export');
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

        $options = array();
        $options['conditions'] = array('Delivery.stato_elaborazione' => 'OPEN');
        $this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
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

        $options = array();
        $options['conditions'] = array('Delivery.stato_elaborazione' => 'OPEN');

        $tmp_user->organization['Organization']['id'] = $organization_id;
        $this->__boxOrder($tmp_user, $delivery_id, $order_id, $options);

        $this->set('des_order_id', $des_order_id);
        $this->set('organization_id', $organization_id);        
		$this->set('delivery_id', $delivery_id);
        $this->set('order_id', $order_id);
    }

	
    /*
     * il produttore: potra' visualizzare gli ordini dei GAS associati
     * */
    public function admin_prodGasSupplierDocsExport($organization_id, $delivery_id, $order_id) {

        if (empty($organization_id) || empty($delivery_id) || empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

		// ACL 
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;

		$organizationResults = $ProdGasSupplier->getOrganizationAssociate($this->user, $organization_id, 0, $debug);
		if($organizationResults['SuppliersOrganization']['can_view_orders']!='Y' && $organizationResults['SuppliersOrganization']['can_view_orders_users']!='Y') {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		// ACL

        $options = array();
        $options['conditions'] = array('Delivery.stato_elaborazione' => 'OPEN');

        $tmp_user->organization['Organization']['id'] = $organization_id;
        $this->__boxOrder($tmp_user, $delivery_id, $order_id, $options);

        $this->set('can_view_orders', $organizationResults['SuppliersOrganization']['can_view_orders']);
        $this->set('can_view_orders_users', $organizationResults['SuppliersOrganization']['can_view_orders_users']);
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

        $options = array();
        //$options['conditions'] = array ('Delivery.stato_elaborazione' => 'CLOSE');
        $options['conditions'] = array('Order.state_code' => 'CLOSE');
        $this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);

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

        $conditions = array('Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Delivery.isVisibleBackOffice' => 'Y',
            'Delivery.sys' => 'N',
            'Delivery.stato_elaborazione' => 'OPEN');

        $deliveries = $Delivery->find('list', array('fields' => array('id', 'luogoData'), 'conditions' => $conditions, 'order' => 'data ASC', 'recursive' => -1));
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

        if ($debug) {
            echo "<pre>Docs::cassiere_delivery_docs_export \n ";
            print_r($this->request->data);
            echo "</pre>";
        }

        $delivery_id = 0;
        $user_id = 0;

        /*
         * salvo i pagamenti di un utente
         */
        if (!empty($this->request->data['Cassiere'])) {

            $delivery_id = $this->request->data['Cassiere']['delivery_id'];
            $user_id = $this->request->data['user_id'];

            // importo che dovrebbe pagare (senza considerare la cassa)
            $tot_importo_da_pagare_orig = $this->request->data['tot_importo_da_pagare_orig'];

            // importo che paga realmente
            $tot_importo_da_pagare = $this->request->data['Cassiere']['tot_importo_da_pagare'];

            $totale_importo_cash = $this->request->data['totale_importo_cash'];
            $cash_options = $this->request->data['cash_options'];
            $cash_text = $this->request->data['cash_text'];
            $differenza_in_cassa = $this->request->data['differenza_in_cassa'];

            unset($this->request->data['Cassiere']['delivery_id']);
            unset($this->request->data['user_id']);
            unset($this->request->data['tot_importo_da_pagare_orig']);
            unset($this->request->data['Cassiere']['tot_importo_da_pagare']);
            unset($this->request->data['totale_importo_cash']);
            unset($this->request->data['cash_options']);
            unset($this->request->data['cash_text']);
            unset($this->request->data['differenza_in_cassa']);

            App::import('Model', 'SummaryOrder');

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

                $row = array();
                $row['SummaryOrder']['id'] = $summary_order_id;
                $row['SummaryOrder']['organization_id'] = $this->user->organization['Organization']['id'];
                $row['SummaryOrder']['order_id'] = $order_id;
                $row['SummaryOrder']['delivery_id'] = $delivery_id;
                $row['SummaryOrder']['user_id'] = $user_id;
                $row['SummaryOrder']['importo_pagato'] = $importo;
                $row['SummaryOrder']['modalita'] = $modalita;

                if ($debug) {
                    echo "<pre>Docs::cassiere_delivery_docs_export() - SummaryOrder \n";
                    print_r($row);
                    echo "</pre>";
                }

                $SummaryOrder = new SummaryOrder;

                if(!$debug_save) {
                    $SummaryOrder->create();
                    if ($SummaryOrder->save($row)) 
                        $this->Session->setFlash(__('The cash has been saved'));
                    else 
                        $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
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


                /*
                 * aggiorno lo stato dell'ordine: se tutti hanno pagato Order.state_code = CLOSE 
                 * */
                if(!$debug_save) {
                    $utilsCrons = new UtilsCrons(new View(null));
                    if (Configure::read('developer.mode'))
                        echo "<pre>";
                    $utilsCrons->ordersIncomingOnDeliveryToClose($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $order_id);
                    if (Configure::read('developer.mode'))
                        echo "</pre>";
                }
            } // end foreach($this->request->data['Cassiere'] as $key => $data)

            /*
             * CASSA 
             * se differenza_in_cassa == 0  DELETE 
             * se differenza_in_cassa != 0  UPDATE/INSERT 
             */
            $tot_importo_da_pagare = $this->importoToDatabase($tot_importo_da_pagare);
            $differenza = ($tot_importo_da_pagare_orig - $tot_importo_da_pagare);
            if ($cash_options == 'Y') {

                App::import('Model', 'Cash');
                $Cash = new Cash;

                /*
                 * non cancello + perche' ho CashesHistory
                 * if ($differenza_in_cassa != '0,00') {
				*/
                    if ($debug)
                        echo '<br />differenza_in_cassa (' . $differenza_in_cassa . ') != 0,00  UPDATE/INSERT';

                    /*
                     *  aggiorno
                     */
                    $row = array();
                    $row['Cash']['organization_id'] = $this->user->organization['Organization']['id'];
                    $row['Cash']['user_id'] = $user_id;
                    $row['Cash']['importo'] = $this->importoToDatabase($differenza_in_cassa);
                    $row['Cash']['nota'] = $cash_text;

                    /*
                     *   ctrl se insert / update 
                     */
                    $options = array();
                    $options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
                                                    'Cash.user_id' => $user_id);
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
                    if ($debug) {
                        echo "<pre>Docs::cassiere_delivery_docs_export() - SALVO IN CASSA \n ";
                        print_r($row);
                        echo "</pre>";
                    }

                    $Cash->create();
                    if(!$debug_save) {
                    if ($Cash->save($row)) 
                        $this->Session->setFlash(__('The cash has been saved'));
                    else 
                        $this->Session->setFlash(__('The cash could not be saved. Please, try again.'));
                    }
/*     
 * DELETE, non cancllo + perche' ho CashesHistory               
                } else {

                    if ($debug)
                        echo '<br />differenza_in_cassa (' . $differenza_in_cassa . ') == 0,00  DELETE';

                    //  delete
                     
                    $options = array();
                    $options['conditions'] = array('Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
                                                'Cash.user_id' => $user_id);
                    $options['fields'] = array('id');
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
        $this->set(compact('deliveries'));

        $this->set(compact('delivery_id', 'user_id'));
    }

    public function admin_des_referent_docs_export($des_order_id) {

        $debug = false;

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
        /*
          echo "<pre>";
          print_r($desOrdersResults);
          echo "</pre>";
         */
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
