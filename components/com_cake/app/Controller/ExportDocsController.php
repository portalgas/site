<?php

App::uses('AppController', 'Controller');

class ExportDocsController extends AppController {

    public $components = array('RequestHandler', 'ActionsDesOrder'); // Include the RequestHandler, it makes sure the proper layout and views files are used
    public $helpers = array('App',
        'Html',
        'Form',
        'Time',
        'Ajax',
        'ExportDocs',
        'PhpExcel');
    private $tmp_user;

    public function beforeFilter() {

        // $this->ctrlHttpReferer($considera_IE='N');

        parent::beforeFilter();

        /* ctrl ACL */
        if (in_array($this->action, array('admin_exportToReferent', 'admin_exportToArticlesWeight'))) {
            $delivery_id = $this->request->pass['delivery_id'];
            $order_id = $this->request->pass['order_id'];

            if (empty($delivery_id) || empty($order_id)) {
                $this->Session->setFlash(__('msg_error_params'));
                $this->myRedirect(Configure::read('routes_msg_exclamation'));
            }


			/*
			 * trovo Order.organization_id del GAS
			 */
			App::import('Model', 'Order');
			$Order = new Order();

			$options = array();
			$options['conditions'] = array('Order.id' => $order_id,
				'Order.delivery_id' => $delivery_id);
			$options['recursive'] = -1;
			$options['fields'] = array('Order.organization_id');
			$orderResults = $Order->find('first', $options);

			$organization_id = $orderResults[Order]['organization_id'];
			
            /* ctrl ACL 
             * i tesoriere e cassiere sono abilitati 
             * ctrl ACL */
            $is_order_des = false;
            if ($this->user->organization['Organization']['hasDes'] == 'Y' && 
				$this->isReferentDesAllGas() && $this->user->get('des_id') != 0 && 
				$organization_id!=$this->user->organization['Organization']['id']) {

                /*
                 * ctr se l'ordine è DES
                 */
                App::import('Model', 'DesOrdersOrganization');
                $DesOrdersOrganization = new DesOrdersOrganization();

                $options = array();
                $options['conditions'] = array('DesOrdersOrganization.des_id' => $this->user->get('des_id'),
                    'DesOrdersOrganization.order_id' => $order_id,
                    'DesOrdersOrganization.organization_id' => $organization_id);
                $options['recursive'] = -1;
                $desOrdersOrganizationResults = $DesOrdersOrganization->find('first', $options);
                if ($debug) {
                    echo "<pre> is OrderDES\n";
                    print_r($options);
                    print_r($desOrdersOrganizationResults);
                    echo "</pre>";
                }

                if (!empty($desOrdersOrganizationResults)) {
                    $organization_id = $desOrdersOrganizationResults['DesOrdersOrganization']['organization_id'];

                    if (!$this->ActionsDesOrder->isReferentDesAllGasDesOrder($this->user, $organization_id, $order_id)) {
                        $this->Session->setFlash(__('msg_not_organization_config'));
                        $this->myRedirect(Configure::read('routes_msg_stop'));
                    }

                    $this->tmp_user->organization['Organization']['id'] = $organization_id;
                    $is_order_des = true;
                }
            }

            if (!$is_order_des) {
                if ($this->isSuperReferente() || $this->isCassiereGeneric() || $this->isTesoriere()) {
                    $this->tmp_user->organization['Organization']['id'] = $this->user->organization['Organization']['id'];
                } else {
                    App::import('Model', 'Order');
                    $Order = new Order;

                    if (!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $order_id)) {
                        $this->Session->setFlash(__('msg_not_permission'));
                        $this->myRedirect(Configure::read('routes_msg_stop'));
                    }

                    $this->tmp_user->organization['Organization']['id'] = $this->user->organization['Organization']['id'];
                }
            }

            Configure::write('debug', 0);
        }
    }

	/*
	 * stampe gli ordini del Produttore abilitato
	 */
	public function admin_exportProdGasSupplierToReferent($organization_id=0, $delivery_id = 0, $order_id = 0, $doc_options = null, $doc_formato = null, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null, $g = null, $h = null, $i = null) {

		$this->tmp_user->organization['Organization']['id'] = $organization_id;
		$this->admin_exportToReferent($delivery_id, $order_id, $doc_options, $doc_formato, $a, $b, $c, $d, $e, $f, $g, $h, $i);
	}
		
    /*
     * $doc_options = to-users, to-users-label, to-users-all-modify, to-articles, to-articles-monitoring, to-articles-details
     * parametri di Setting
     * 		se  $doc_options=to-users-all-modify    $a = trasport
     * 		se  $doc_options=to-users            $a = user_phone, $b = user_email, $c = user_address, $d = totale_per_utente, $e = trasportAndCost, $f = user_avatar, $g = dettaglio_per_utente, $h = note, $i = delete_to_referent
     * 		se  $doc_options=to-users-label      $a = user_phone, $b = user_email, $c = user_address, $d = trasportAndCost, $e = user_avatar, $f = delete_to_referent
     * 		se  $doc_options=to-articles         $a = trasportAndCost, $b = codice, $c = pezzi_confezione
     * 		se  $doc_options=to-articles-details $a = acquistato_il, $b = article_img, $c = trasportAndCost, $d = totale_per_articolo, $e = codice
     *      se  $doc_options=to-articles-monitoring
     *
     * $doc_formato = PREVIEW, PDF, CSV, EXCEL
     */

    public function admin_exportToReferent($delivery_id = 0, $order_id = 0, $doc_options = null, $doc_formato = null, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null, $g = null, $h = null, $i = null) {

        /*
         * log
        if ($doc_options == 'to-articles-details') {
            $str_log = "";
            $str_log .= "org " . $this->user->organization['Organization']['id'] . "\n";
            $str_log .= "user_id " . $this->user->id . "\n";
            $str_log .= "delivery_id " . $delivery_id . "\n";
            $str_log .= "order_id " . $order_id . "\n";
            $str_log .= "doc_options " . $doc_options . "\n";
            $str_log .= "doc_formato " . $doc_formato . "\n";
            CakeLog::write('info', $str_log);
        }
         */
		 
        $debug = false;

        if ($debug) {
            echo "<br />doc_options " . $doc_options;
            echo "<br />a " . $a;
            echo "<br />b " . $b;
            echo "<br />c " . $c;
            echo "<br />d " . $d;
            echo "<br />e " . $e;
            echo "<br />f " . $f;
            echo "<br />g " . $g;
            echo "<br />h " . $h;
            echo "<br />i " . $i;
        }

        $this->ctrlHttpReferer();

        if ($doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
            'articlesOrdersInOrderAndCartsAllUsers' => true, // estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
            'suppliers' => true, 'referents' => true);

		/*
		 * CONDITIONS
		 */
        $conditions = array('Delivery' => array('Delivery.id' => (int) $delivery_id,
			                'Delivery.isVisibleBackOffice' => 'Y'),
				            'Order' => array('Order.isVisibleBackOffice' => 'Y',
			                'Order.id' => (int) $order_id));
			                
        if ($doc_options == 'to-users-all-modify' && ($doc_formato == 'PREVIEW' || $doc_formato == 'PDF')) {
         
        }
        else if ($doc_options == 'to-users' || $doc_options == 'to-users-label') {
           
           if ($doc_options == 'to-users') {
           		if($i=='N')
	           	   $conditions += array('Cart' => array('Cart.stato' => 'Y',  'Cart.deleteToReferent' => 'N'));
 	            else
	           	   $conditions += array('Cart' => array('Cart.stato' => 'Y'));
           }
		   else
           if ($doc_options == 'to-users-label') {
           		if($f=='N')
	           	   $conditions += array('Cart' => array('Cart.stato' => 'Y',  'Cart.deleteToReferent' => 'N'));
 	            else
	           	   $conditions += array('Cart' => array('Cart.stato' => 'Y'));
           }

           
        } else
            $conditions += array('Cart' => array('Cart.stato' => 'Y',  'Cart.deleteToReferent' => 'N'));

		/*
		 * ORDER BY
		 */
        if ($doc_options == 'to-users' || $doc_options == 'to-users-label' || $doc_options == 'to-users-all-modify')
            $orderBy = array('User' => Configure::read('orderUser') . ', Article.name, Article.id');
        else
        if ($doc_options == 'to-articles' || $doc_options == 'to-articles-monitoring')
            $orderBy = array('Article' => 'Article.name, Article.id, ' . Configure::read('orderUser'));
        else
        if ($doc_options == 'to-articles-details') {
            if ($a == 'Y') // acquistato_il
                $orderBy = array('Article' => 'Article.name, Article.id, Cart.created, ' . Configure::read('orderUser'));
            else
                $orderBy = array('Article' => 'Article.name, Article.id, ' . Configure::read('orderUser'));
        }
        $results = $Delivery->getDataWithoutTabs($this->tmp_user, $conditions, $options, $orderBy);

        /*
         * ctrl eventuali
         * 		- totali impostati dal referente (SummaryOrder) in Carts::managementCartsGroupByUsers 
         * 		- spese di trasporto  (SummaryOrderTrasport)
         * 		- costi aggiuntivi  (SummaryOrderCostMore)
         * 		- sconti  (SummaryOrderCostLess)
         */
        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;

        if ($doc_options == 'to-users' || $doc_options == 'to-users-label' || $doc_options == 'to-users-all-modify') {

            /*
             * dati dell'ordine
             */
            $hasTrasport = $results['Delivery'][0]['Order'][0]['Order']['hasTrasport']; /* trasporto */
            $trasport = $results['Delivery'][0]['Order'][0]['Order']['trasport'];
            $hasCostMore = $results['Delivery'][0]['Order'][0]['Order']['hasCostMore']; /* spesa aggiuntiva */
            $cost_more = $results['Delivery'][0]['Order'][0]['Order']['cost_more'];
            $hasCostLess = $results['Delivery'][0]['Order'][0]['Order']['hasCostLess'];  /* sconto */
            $cost_less = $results['Delivery'][0]['Order'][0]['Order']['cost_less'];
            $typeGest = $results['Delivery'][0]['Order'][0]['Order']['typeGest'];   /* AGGREGATE / SPLIT */

            $resultsSummaryOrder = array();
            $resultsSummaryOrderTrasport = array();
            $resultsSummaryOrderCostMore = array();
            $resultsSummaryOrderCostLess = array();

            if ($hasTrasport == 'Y') {
                App::import('Model', 'SummaryOrderTrasport');
                $SummaryOrderTrasport = new SummaryOrderTrasport;

                $resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($this->tmp_user, $order_id);
            }
            if ($hasCostMore == 'Y') {
                App::import('Model', 'SummaryOrderCostMore');
                $SummaryOrderCostMore = new SummaryOrderCostMore;

                $resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($this->tmp_user, $order_id);
            }
            if ($hasCostLess == 'Y') {
                App::import('Model', 'SummaryOrderCostLess');
                $SummaryOrderCostLess = new SummaryOrderCostLess;

                $resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($this->tmp_user, $order_id);
            }

            $resultsSummaryOrder = $SummaryOrder->select_to_order($this->tmp_user, $order_id); // se l'ordine e' ancora aperto e' vuoto

            $results = $this->ExportDoc->getCartCompliteOrder($order_id, $results, $resultsSummaryOrder, $resultsSummaryOrderTrasport, $resultsSummaryOrderCostMore, $resultsSummaryOrderCostLess);
        } else
        if ($doc_options == 'to-articles' || $doc_options == 'to-articles-details' || $doc_options == 'to-articles-monitoring') {
            $resultsSummaryOrder = $SummaryOrder->select_totale_importo_to_order($this->tmp_user, $order_id);
            $this->set('resultsSummaryOrder', $resultsSummaryOrder);
        }
        $this->set('results', $results);

        $params = array('delivery_id' => $delivery_id, 'order_id' => $order_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->tmp_user, $doc_options, $params, $user_target = 'REFERENTE'));
        $this->set('organization', $this->tmp_user->organization);

        if ($doc_options == 'to-articles-monitoring') {

            App::import('Model', 'Order');
            $Order = new Order;

            /*
             * ctrl se l'ordine dev'essere validato (ArticlesOrder.pezzi_confezione > 1) per la gestione dei colli
             */
            if ($Order->isOrderToValidate($this->tmp_user, $order_id))
                $orderToValidate = true;
            else
                $orderToValidate = false;

            /*
             * ctrl se l'ordine ha settato delle quantita' massime > 0
             */
            if ($Order->isOrderToQtaMassima($this->tmp_user, $order_id))
                $orderToQtaMassima = true;
            else
                $orderToQtaMassima = false;

            /*
             * ctrl se l'ordine ha settato delle quantita' minime sugli acquisti di tutto l'ordine > 0
             */
            if ($Order->isOrderToQtaMinimaOrder($this->tmp_user, $order_id))
                $orderToQtaMinimaOrder = true;
            else
                $orderToQtaMinimaOrder = false;

            $this->set('orderToValidate', $orderToValidate);
            $this->set('orderToQtaMinimaOrder', $orderToQtaMinimaOrder);
            $this->set('orderToQtaMassima', $orderToQtaMassima);
        } // end if($doc_options=='to-articles-monitoring') 

        /*
         * setting 
         */
        switch ($doc_options) {
            case 'to-users-all-modify':
                $this->set('trasportAndCost', $a);
                break;
            case 'to-users':
                $this->set('user_phone', $a);
                $this->set('user_email', $b);
                $this->set('user_address', $c);
                $this->set('totale_per_utente', $d);
                $this->set('trasportAndCost', $e);
                $this->set('user_avatar', $f);
                $this->set('dettaglio_per_utente', $g);
                $this->set('note', $h);
                break;
            case 'to-users-label':
                $this->set('user_phone', $a);
                $this->set('user_email', $b);
                $this->set('user_address', $c);
                $this->set('trasportAndCost', $d);
                $this->set('user_avatar', $e);
                break;
            case 'to-articles':
                $this->set('trasportAndCost', $a);
                $this->set('codice', $b);
                $this->set('pezzi_confezione1', $c);
                break;
            case 'to-articles-details':
                $this->set('acquistato_il', $a);
                $this->set('article_img', $b);
                $this->set('trasportAndCost', $c);
                $this->set('totale_per_articolo', $d);
                $this->set('codice', $e);
                break;
        }

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'to-users') {
                    if ($g == 'Y') /* dettaglio_per_utente */
                        $this->render('referent_to_users');
                    else
                        $this->render('referent_to_users_no_details');
                }
                else
                if ($doc_options == 'to-users-label')
                    $this->render('referent_to_users_label');
                else
                if ($doc_options == 'to-users-all-modify')
                    $this->render('referent_to_users_all_modify');
                else
                if ($doc_options == 'to-articles')
                    $this->render('referent_to_articles');
                else
                if ($doc_options == 'to-articles-monitoring')
                    $this->render('referent_to_articles_monitoring');
                else
                if ($doc_options == 'to-articles-details')
                    $this->render('referent_to_articles_details');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'to-users') {
                    if ($g == 'Y') /* dettaglio_per_utente */
                        $this->render('referent_to_users');
                    else
                        $this->render('referent_to_users_no_details');
                }
                else
                if ($doc_options == 'to-users-label')
                    $this->render('referent_to_users_label');
                else
                if ($doc_options == 'to-users-all-modify')
                    $this->render('referent_to_users_all_modify');
                else
                if ($doc_options == 'to-articles')
                    $this->render('referent_to_articles');
                else
                if ($doc_options == 'to-articles-monitoring')
                    $this->render('referent_to_articles_monitoring');
                else
                if ($doc_options == 'to-articles-details')
                    $this->render('referent_to_articles_details');
                break;
            case 'CSV':
                $this->layout = 'csv';
                if ($doc_options == 'to-users') {
                    if ($g == 'Y') /* dettaglio_per_utente */
                        $this->render('referent_to_users_csv');
                    else
                        $this->render('referent_to_users_no_details_csv');
                }
                else
                if ($doc_options == 'to-users-label')
                    $this->render('referent_to_users_label_csv');
                else
                if ($doc_options == 'to-users-all-modify')
                    $this->render('referent_to_users_all_modify_csv');
                else
                if ($doc_options == 'to-articles')
                    $this->render('referent_to_articles_csv');
                else
                if ($doc_options == 'to-articles-monitoring')
                    $this->render('referent_to_articles_monitoring_csv');
                else
                if ($doc_options == 'to-articles-details')
                    $this->render('referent_to_articles_details_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'to-users') {
                    if ($g == 'Y') /* dettaglio_per_utente */
                        $this->render('referent_to_users_excel');
                    else
                        $this->render('referent_to_users_no_details_excel');
                }
                else
                if ($doc_options == 'to-users-label')
                    $this->render('referent_to_users_label_excel');
                else
                if ($doc_options == 'to-users-all-modify')
                    $this->render('referent_to_users_excel');
                else
                if ($doc_options == 'to-articles')
                    $this->render('referent_to_articles_excel');
                else
                if ($doc_options == 'to-articles-monitoring')
                    $this->render('referent_to_articles_monitoring_excel');
                else
                if ($doc_options == 'to-articles-details')
                    $this->render('referent_to_articles_details_excel');
                break;
        }
    }

    /*
     * stampa l'intera consegna, utilizzata dal cassiere / dal manager / superReferente 
     * $doc_options = to-delivery-cassiere-users-all
     * ordinato per utente / produttore
     */

    public function admin_exportToCassiereAllDelivery($delivery_id = 0, $doc_options = null, $doc_formato = null) {

        if ($delivery_id == 0 || $doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $users = array();
        // $this->ctrlHttpReferer();

        Configure::write('debug', 0);

        App::import('Model', 'User');
        $User = new User;

        $conditions = array();
        $conditions = array('Delivery.id' => $delivery_id);

		/*
		 * stampa disponibile tra tutte le stampe
		 */
        if ($this->isCassiere() || $this->isManager() || $this->isSuperReferente())
            $users = $User->getUserWithCartByDelivery($this->user, $conditions, '', '', $debug);
        else
        if ($this->isReferentCassiere())
            $users = $User->getUserWithCartByDeliveryACLReferent($this->user, $conditions, '', $debug);

        $newResults = array();
        if (!empty($users))
            foreach ($users as $numResult => $user) {

                $user_id = $user['User']['id'];

                $results = $this->__getExportToCassiereSingleUser($this->user, $delivery_id, $user_id);

                $newResults[$numResult] = $results;
            }
        /*
          echo "<pre>";
          print_r($newResults);
          echo "</pre>";
         */
        $this->set('results', $newResults);

        /*
         * filtri stampa
         */
        $this->set('user_phone', 'Y');
        $this->set('user_email', 'N');
        $this->set('user_address', 'Y');
        $this->set('trasportAndCost', 'Y');
        $this->set('user_avatar', 'N');

        $params = array('delivery_id' => $delivery_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params, $user_target = 'REFERENTE'));
        $this->set('organization', $this->user->organization);
        $this->set('user_id', $user_id);

        if ($doc_options == 'to-delivery-cassiere-users-compact-all') {
            /*
             * D E L I V E R Y
             */
            App::import('Model', 'Delivery');
            $Delivery = new Delivery;

            $options = array();
            $options['conditions'] = array('Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
                'Delivery.id' => (int) $delivery_id);
            $options['recursive'] = -1;
            $resultDelivery = $Delivery->find('first', $options);
            $this->set('resultDelivery', $resultDelivery);
        }

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'to-delivery-cassiere-users-all')
                    $this->render('cassiere_delivery_users_all');
                else
                if ($doc_options == 'to-delivery-cassiere-users-compact-all')
                    $this->render('cassiere_delivery_users_compact_all');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'to-delivery-cassiere-users-all')
                    $this->render('cassiere_delivery_users_all');
                else
                if ($doc_options == 'to-delivery-cassiere-users-compact-all')
                    $this->render('cassiere_delivery_users_compact_all');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'to-delivery-cassiere-users-all')
                    $this->render('cassiere_delivery_users_all_excel');
                elseif ($doc_options == 'to-delivery-cassiere-users-compact-all')
                    $this->render('cassiere_delivery_users_compact_all_excel');
                break;
        }
    }

    /*
     * stampa consegna splittata per user
     * $doc_options = to-delivery-cassiere-users-all-split / to-delivery-cassiere-user-one
     * $doc_formato = PREVIEW, PDF
     */

    public function admin_exportToCassiere($delivery_id = 0, $user_id = 0, $doc_options = null, $doc_formato = null) {

        if ($delivery_id == 0 || $doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $this->ctrlHttpReferer();

        Configure::write('debug', 0);

        $results = $this->__getExportToCassiereSingleUser($this->user, $delivery_id, $user_id);
        $this->set('results', $results);

        /*
         * filtri stampa
         */
        $this->set('user_phone', 'Y');
        $this->set('user_email', 'N');
        $this->set('user_address', 'Y');
        $this->set('trasportAndCost', 'Y');
        $this->set('user_avatar', 'N');

        $params = array('delivery_id' => $delivery_id, 'user_id' => $user_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params, $user_target = 'REFERENTE'));
        $this->set('organization', $this->user->organization);
        $this->set('user_id', $user_id);

        /*
         * dati organization per dati pagamento
         */
        App::import('Model', 'Organization');
        $Organization = new Organization;

        $options = array();
        $options['conditions'] = array('Organization.id' => (int) $this->user->organization['Organization']['id']);
        $options['recursive'] = -1;
        $organizationResults = $Organization->find('first', $options);
        $this->set('organizationResults', $organizationResults);

        /*
         *  eventuale importo POS
         */
        if ($this->user->organization['Organization']['hasFieldPaymentPos'] == 'Y') {

            App::import('Model', 'SummaryDeliveriesPos');
            $SummaryDeliveriesPos = new SummaryDeliveriesPos;

            $summaryDeliveriesPosResults = $SummaryDeliveriesPos->findPaymentPos($this->user, $delivery_id, $user_id);
            if (!empty($summaryDeliveriesPosResults))
                $this->set('summaryDeliveriesPosResults', $summaryDeliveriesPosResults);
        }

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'to-delivery-cassiere-users-all-split' || $doc_options == 'to-delivery-cassiere-user-one')
                    $this->render('cassiere_delivery_user');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'to-delivery-cassiere-users-all-split' || $doc_options == 'to-delivery-cassiere-user-one')
                    $this->render('cassiere_delivery_user');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'to-delivery-cassiere-users-all-split' || $doc_options == 'to-delivery-cassiere-user-one')
                    $this->render('cassiere_delivery_user_excel');
                break;
        }
    }

    /*
     * situazione di tutti gli ordini in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) di una consegna
     */

    public function admin_exportToCassiereListOrders($delivery_id = 0, $doc_options = null, $doc_formato = null) {

        if ($delivery_id == 0 || $doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $this->ctrlHttpReferer();

        Configure::write('debug', 0);

        App::import('Model', 'Cassiere');
        $Cassiere = new Cassiere;

        $results = $Cassiere->lists_orders_processed_on_delivery($this->user, $delivery_id);

        /*
         * calcolo totali
         */
        if ($doc_options == 'to-lists-suppliers-cassiere') {

            /*
             * D E L I V E R Y
             */
            App::import('Model', 'Delivery');
            $Delivery = new Delivery;

            $options = arraY();
            $options['conditions'] = array('Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
                'Delivery.id' => (int) $delivery_id);
            $options['recursive'] = -1;
            $resultDelivery = $Delivery->find('first', $options);
            $this->set('resultDelivery', $resultDelivery);

            $newResults = array();
            if (!empty($results['Order'])) {

                $tot_importo_delivery = 0;
                $tot_importo_pagato_delivery = 0;
                $tot_differenza_delivery = 0;
                foreach ($results['Order'] as $numResult => $order) {

                    $newResults[$numResult]['SuppliersOrganization']['name'] = $order['SuppliersOrganization']['name'];
                    $newResults[$numResult]['Order']['data_inizio'] = $order['data_inizio'];
                    $newResults[$numResult]['Order']['data_fine'] = $order['data_fine'];
                    $newResults[$numResult]['Order']['tot_importo'] = $order['tot_importo'];
                    $newResults[$numResult]['Order']['totUserToTesoriere'] = $order['totUserToTesoriere'];

                    $tot_importo = 0;
                    $tot_importo_pagato = 0;
                    $tot_differenza = 0;
                    $tot_importo_cash = 0;
                    if (!empty($order['SummaryOrder']))
                        foreach ($order['SummaryOrder'] as $numResult2 => $result) {

                            $importo = number_format($result['SummaryOrder']['importo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                            $importo_pagato = number_format($result['SummaryOrder']['importo_pagato'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));

                            $differenza = (-1 * ($result['SummaryOrder']['importo'] - $result['SummaryOrder']['importo_pagato']));
                            $differenza = number_format($differenza, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));

                            if (isset($result['Cash']['importo']))
                                $importo_cash = number_format($result['Cash']['importo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                            else
                                $importo_cash = '0,00';

                            $tot_importo += $result['SummaryOrder']['importo'];
                            $tot_importo_pagato += $result['SummaryOrder']['importo_pagato'];
                            $tot_differenza += (-1 * ($result['SummaryOrder']['importo'] - $result['SummaryOrder']['importo_pagato']));

                            $tot_importo_delivery += $result['SummaryOrder']['importo'];
                            $tot_importo_pagato_delivery += $result['SummaryOrder']['importo_pagato'];
                            $tot_differenza_delivery += (-1 * ($result['SummaryOrder']['importo'] - $result['SummaryOrder']['importo_pagato']));

                            $tot_importo_cash += $result['Cash']['importo'];
                        } // loop summaryOrders

                        /*
                         * totale ordine
                         */
                    $tot_importo = number_format($tot_importo, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                    $tot_importo_pagato = number_format($tot_importo_pagato, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                    $tot_differenza = number_format($tot_differenza, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));

                    $newResults[$numResult]['Order']['tot_importo'] = $tot_importo;
                    $newResults[$numResult]['Order']['tot_importo_pagato'] = $tot_importo_pagato;
                    $newResults[$numResult]['Order']['tot_differenza'] = $tot_differenza;
                } // loop orders

                /*
                 * totale consegna
                 */
                $tot_importo_delivery = number_format($tot_importo_delivery, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                $tot_importo_pagato_delivery = number_format($tot_importo_pagato_delivery, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                $tot_differenza_delivery = number_format($tot_differenza_delivery, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));

                $deliveryResults = array();
                $deliveryResults['tot_importo_delivery'] = $tot_importo_delivery;
                $deliveryResults['tot_importo_pagato_delivery'] = $tot_importo_pagato_delivery;
                $deliveryResults['tot_differenza_delivery'] = $tot_differenza_delivery;
                $this->set('deliveryResults', $deliveryResults);
            } // end if $results

            $results = $newResults;
        } // end if($doc_options=='to-lists-suppliers-cassiere')

        $this->set('results', $results);
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */

        $params = array('delivery_id' => $delivery_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params));
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'to-lists-orders-cassiere')
                    $this->render('cassiere_lists_orders');
                else
                if ($doc_options == 'to-lists-suppliers-cassiere')
                    $this->render('cassiere_lists_suppliers');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'to-lists-orders-cassiere')
                    $this->render('cassiere_lists_orders');
                else
                if ($doc_options == 'to-lists-suppliers-cassiere')
                    $this->render('cassiere_lists_suppliers');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'to-lists-orders-cassiere')
                    $this->render('cassiere_lists_orders_excel');
                else
                if ($doc_options == 'to-lists-suppliers-cassiere')
                    $this->render('cassiere_lists_suppliers_excel');
                break;
        }
    }

    /*
     * situazione di tutti gli ordini in
     * 		PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) 
     * 		WAIT-PROCESSED-TESORIERE, PROCESSED-TESORIERE, TO-PAYMENT
     * 		CLOSE 	 
     */

    public function admin_exportToCassiereListOrdersAll($delivery_id = 0, $doc_options = null, $doc_formato = null) {

        if ($delivery_id == 0 || $doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $this->ctrlHttpReferer();

        Configure::write('debug', 0);

        App::import('Model', 'Cassiere');
        $Cassiere = new Cassiere;

        $results = $Cassiere->lists_orders_all($this->user, $delivery_id);
        $this->set('results', $results);

        $params = array('delivery_id' => $delivery_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params));
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'to-lists-orders-cassiere')
                    $this->render('cassiere_lists_orders');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'to-lists-orders-cassiere')
                    $this->render('cassiere_lists_orders');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'to-lists-orders-cassiere')
                    $this->render('cassiere_lists_orders_excel');
                break;
        }
    }

    /*
     * situazione di tutti gli ordini in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) di una consegna
     */

    public function admin_exportToCassiereListUsersDelivery($delivery_id = 0, $doc_options = null, $doc_formato = null) {

        if ($delivery_id == 0 || $doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $this->ctrlHttpReferer();

        Configure::write('debug', 0);

        App::import('Model', 'Cassiere');
        $Cassiere = new Cassiere;

        $results = $Cassiere->lists_users_delivery_processed_on_delivery($this->user, $delivery_id);
        $this->set('results', $results);

        $params = array('delivery_id' => $delivery_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params));
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'to-list-users-delivery-cassiere')
                    $this->render('cassiere_lists_users_delivery');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'to-list-users-delivery-cassiere')
                    $this->render('cassiere_lists_users_delivery');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'to-list-users-delivery-cassiere')
                    $this->render('cassiere_lists_users_delivery_excel');
                break;
        }
    }

    /*
     * situazione di tutti gli ordini in
     * 		PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) 
     * 		WAIT-PROCESSED-TESORIERE, PROCESSED-TESORIERE, TO-PAYMENT
     * 		CLOSE 	 
     */

    public function admin_exportToCassiereListUsersDeliveryAll($delivery_id = 0, $doc_options = null, $doc_formato = null) {

        if ($delivery_id == 0 || $doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $this->ctrlHttpReferer();

        Configure::write('debug', 0);

        App::import('Model', 'Cassiere');
        $Cassiere = new Cassiere;

        $results = $Cassiere->lists_users_delivery_all($this->user, $delivery_id);
        $this->set('results', $results);

        $params = array('delivery_id' => $delivery_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params));
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'to-list-users-delivery-cassiere')
                    $this->render('cassiere_lists_users_delivery');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'to-list-users-delivery-cassiere')
                    $this->render('cassiere_lists_users_delivery');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'to-list-users-delivery-cassiere')
                    $this->render('cassiere_lists_users_delivery_excel');
                break;
        }
    }

    private function __getExportToCassiereSingleUser($user, $delivery_id, $user_id) {

        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
            'articlesOrdersInOrderAndCartsAllUsers' => true, // estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
            'suppliers' => true, 'referents' => false);

        $conditions = array('Delivery' => array('Delivery.id' => (int) $delivery_id,
                'Delivery.isVisibleBackOffice' => 'Y'),
            'Order' => array('Order.isVisibleBackOffice' => 'Y'));

        if ($user_id > 0)
            $conditions += array('Cart' => array('Cart.stato' => 'Y', 'Cart.deleteToReferent' => 'N', 'Cart.user_id' => $user_id));
        else
            $conditions += array('Cart' => array('Cart.stato' => 'Y', 'Cart.deleteToReferent' => 'N'));

        $orderBy = array('User' => Configure::read('orderUser') . ', Article.name, Article.id');

        $results = $Delivery->getDataWithoutTabs($user, $conditions, $options, $orderBy);

        /*
          echo "<pre>";
          print_r($conditions);
          print_r($results);
          echo "</pre>";
         */

        /*
         * ctrl eventuali
         * 		- totali impostati dal referente (SummaryOrder) in Carts::managementCartsGroupByUsers 
         * 		- spese di trasporto  (SummaryOrderTrasport)
         * 		- costi aggiuntivi  (SummaryOrderCostMore)
         * 		- sconti  (SummaryOrderCostLess)
         */
        $deliveryResults = $results;
        unset($deliveryResults['Delivery'][$numDelivery]['Order']);

        foreach ($results['Delivery'] as $numDelivery => $result['Delivery']) {
            foreach ($result['Delivery']['Order'] as $numOrder => $order) {

                $order_id = $order['Order']['id'];

                /*
                 * dati dell'ordine
                 */
                $hasTrasport = $order['Order']['hasTrasport']; /* trasporto */
                $trasport = $order['Order']['trasport'];
                $hasCostMore = $order['Order']['hasCostMore']; /* spesa aggiuntiva */
                $cost_more = $order['Order']['cost_more'];
                $hasCostLess = $order['Order']['hasCostLess'];  /* sconto */
                $cost_less = $order['Order']['cost_less'];
                $typeGest = $order['Order']['typeGest'];   /* AGGREGATE / SPLIT */

                $resultsSummaryOrder = array();
                $resultsSummaryOrderTrasport = array();
                $resultsSummaryOrderCostMore = array();
                $resultsSummaryOrderCostLess = array();

                if ($hasTrasport == 'Y') {
                    App::import('Model', 'SummaryOrderTrasport');
                    $SummaryOrderTrasport = new SummaryOrderTrasport;

                    $resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($user, $order_id, $user_id);
                }
                if ($hasCostMore == 'Y') {
                    App::import('Model', 'SummaryOrderCostMore');
                    $SummaryOrderCostMore = new SummaryOrderCostMore;

                    $resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($user, $order_id, $user_id);
                }
                if ($hasCostLess == 'Y') {
                    App::import('Model', 'SummaryOrderCostLess');
                    $SummaryOrderCostLess = new SummaryOrderCostLess;

                    $resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($user, $order_id, $user_id);
                }

                $resultsSummaryOrder = $SummaryOrder->select_to_order($user, $order_id, $user_id); // se l'ordine e' ancora aperto e' vuoto				
                $results = $this->ExportDoc->getCartCompliteOrder($order_id, $results, $resultsSummaryOrder, $resultsSummaryOrderTrasport, $resultsSummaryOrderCostMore, $resultsSummaryOrderCostLess, $debug);
            }  // ciclo orders

            /*
             * pagamento POS
             */
            if ($user->organization['Organization']['hasFieldPaymentPos'] == 'Y') {

                App::import('Model', 'SummaryDeliveriesPos');
                $SummaryDeliveriesPos = new SummaryDeliveriesPos;

                $summaryDeliveriesPosResults = $SummaryDeliveriesPos->findPaymentPos($user, $delivery_id, $user_id);
                if (!empty($summaryDeliveriesPosResults))
                    $results['Delivery'][$numDelivery]['SummaryDeliveriesPos'][$user_id] = $summaryDeliveriesPosResults['SummaryDeliveriesPos'];
            }
        } // ciclo deliveries

        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
          exit;
         */

        return $results;
    }

    /*
     * richiamata dal Tesoriere o ReferenteTesoriere
     * 
     * $doc_options    to-supplier  to-users, to-users-all-modify
     * $doc_formato = PREVIEW, PDF, CSV
     */

    public function admin_exportToTesoriere($delivery_id = 0, $order_id_selected = null, $doc_options = null, $doc_formato = null) {

        $this->ctrlHttpReferer();

        /* ctrl ACL */
        $acl_continue = false;
        if ($this->isSuperReferente())
            $acl_continue = true;
        else
        if ($this->isTesoriereGeneric())
            $acl_continue = true;
        else {
            $order_id = $order_id_selected;

            /*
             * ctrl referentTesoriere
             */
            if ($this->isReferentTesoriere())
                $isReferenteTesoriere = true;
            else
                $isReferenteTesoriere = false;

            if ($isReferenteTesoriere)
                $acl_continue = true;
        }

        if (!$acl_continue) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */

        Configure::write('debug', 0);

        if (empty($delivery_id) || $order_id_selected == null || $doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * D E L I V E R Y
         */
        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = array('Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Delivery.isVisibleBackOffice' => 'Y',
            'Delivery.stato_elaborazione' => 'OPEN',
            'Delivery.id' => (int) $delivery_id);

        $resultDelivery = $Delivery->find('first', array('conditions' => $conditions, 'recursive' => -1));
        $this->set('resultDelivery', $resultDelivery);

        /*
         * S U M M A R Y - O R D E R S 
         */
        if ($doc_options == 'to-supplier') {
            $sql = "select 
						SummaryOrder.order_id, 
						`Order`.data_inizio, `Order`.data_fine, 
						sum(importo) as totImporto, SupplierOrganization.name
					FROM 
						" . Configure::read('DB.prefix') . "summary_orders as SummaryOrder,
						" . Configure::read('DB.prefix') . "orders as `Order`,
						" . Configure::read('DB.prefix') . "suppliers_organizations as SupplierOrganization
					WHERE 
						SummaryOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						and `Order`.organization_id = " . (int) $this->user->organization['Organization']['id'] . "  
						and SupplierOrganization.organization_id = " . (int) $this->user->organization['Organization']['id'] . "  
						and SummaryOrder.order_id in (" . $order_id_selected . ")
						and `Order`.id = SummaryOrder.order_id
						and `Order`.supplier_organization_id = SupplierOrganization.id
					GROUP BY 
						SummaryOrder.order_id, `Order`.data_inizio, `Order`.data_fine 
					ORDER BY
						 SupplierOrganization.name";
            //echo '<br />'.$sql;
            $results = $Delivery->query($sql);
            $this->set('results', $results);
        } else
        if ($doc_options == 'to-users') {
            $sql = "SELECT 
						SummaryOrder.user_id, importo,
						User.name 
					FROM 
						" . Configure::read('DB.prefix') . "summary_orders as SummaryOrder, 
						" . Configure::read('DB.portalPrefix') . "users as User 
					WHERE
						SummaryOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						and User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "  
						and SummaryOrder.order_id in (" . $order_id_selected . ")
						and SummaryOrder.user_id = User.id
						and User.block = 0   		
					GROUP BY
						User.name
					ORDER BY " . Configure::read('orderUser');
            //echo '<br />'.$sql;
            $results = $Delivery->query($sql);
            $this->set('results', $results);
        }


        $params = array('delivery_id' => $delivery_id, 'order_id' => $order_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params, $user_target = 'TESORIERE'));
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'to-supplier')
                    $this->render('tesoriere_to_supplier');
                else
                if ($doc_options == 'to-users')
                    $this->render('tesoriere_to_users');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'to-supplier')
                    $this->render('tesoriere_to_supplier');
                else
                if ($doc_options == 'to-users')
                    $this->render('tesoriere_to_users');
                break;
            case 'CSV':
                $this->layout = 'csv';
                if ($doc_options == 'to-supplier')
                    $this->render('tesoriere_to_supplier_csv');
                else
                if ($doc_options == 'to-users')
                    $this->render('tesoriere_to_users_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'to-supplier')
                    $this->render('tesoriere_to_supplier_excel');
                else
                if ($doc_options == 'to-users')
                    $this->render('tesoriere_to_users_excel');
                break;
        }
    }

    public function admin_tesoriere_request_payment($request_payment_id = 0, $doc_formato = null) {

        $this->ctrlHttpReferer();

        /* ctrl ACL */
        if ($this->user->organization['Organization']['payToDelivery'] != 'POST' &&
                $this->user->organization['Organization']['payToDelivery'] == 'ON-POST' &&
                !$this->isTesoriereGeneric()) {

            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */

        Configure::write('debug', 0);

        if (empty($request_payment_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * D E L I V E R Y
         */
        App::import('Model', 'RequestPayment');
        $RequestPayment = new RequestPayment;

        /*
         * estraggo i dettagli di una richiesta di pagamento
         * 	- ordini associati
         *  - voci di spesa generica
         *  - dispensa
         */
        $conditions = array();
        $results = $RequestPayment->getAllDetails($this->user, $request_payment_id, $conditions);

        /*
         * dati cassa per l'utente
         */
        App::import('Model', 'Cash');
        foreach ($results['SummaryPayment'] as $numResult => $result) {
            $Cash = new Cash;

            $options = array();
            $options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
                'Cash.user_id' => $result['User']['id']);
            $options['recursive'] = -1;
            $cashResults = $Cash->find('first', $options);
            if (empty($cashResults)) {
                $cashResults['Cash']['importo'] = '0.00';
                $cashResults['Cash']['importo_'] = '0,00';
                $cashResults['Cash']['importo_e'] = '0,00 &euro;';
            }
            /*
              echo "<pre>";
              print_r($options);
              print_r($cashResults);
              echo "</pre>";
             */
            $results['SummaryPayment'][$numResult]['Cash'] = $cashResults['Cash'];
        }
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
        $this->set('results', $results);


        $params = array('request_payment_num' => $results['RequestPayment']['num']);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, 'to-tesoriere-request-payment', $params, $user_target = 'TESORIERE'));

        switch ($doc_formato) {
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('tesoriere_request_payment_excel');
                break;
        }
    }

    /*
     * stampa il carrello dell'utente passato
     */

    public function admin_userOtherRequestPayment($request_payment_id, $user_id, $doc_formato) {

        if ($this->user->organization['Organization']['payToDelivery'] != 'POST' && $this->user->organization['Organization']['payToDelivery'] != 'ON-POST') {
            $this->Session->setFlash(__('msg_not_organization_config'));
            if (!$debug)
                $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        if (!$this->isRoot() && !$this->isManager() && !$this->isTesoriereGeneric()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__userRequestPayment($request_payment_id, $user_id, $doc_formato);
    }

    public function admin_userRequestPayment($request_payment_id, $doc_formato) {
        $user_id = $this->user->get('id');
        $this->__userRequestPayment($request_payment_id, $user_id, $doc_formato);
    }

    public function userRequestPayment($request_payment_id, $doc_formato) {
        $user_id = $this->user->get('id');
        $this->__userRequestPayment($request_payment_id, $user_id, $doc_formato);
    }

    private function __userRequestPayment($request_payment_id, $user_id = 0, $doc_formato) {

        App::import('Model', 'RequestPayment');
        $RequestPayment = new RequestPayment;

        if ($user_id == 0 || $request_payment_id == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * num della richiesta pagamento
         */
        $options = array();
        $options['conditions'] = array('RequestPayment.organization_id' => (int) $this->user->organization['Organization']['id'],
            'RequestPayment.id' => $request_payment_id);
        $options['recursive'] = -1;
        $options['fields'] = array('num');
        $results = $RequestPayment->find('first', $options);
        $request_payment_num = $results['RequestPayment']['num'];
        $this->set('request_payment_num', $request_payment_num);

        /*
         * dati (Orders, Generics, Storeroom) della richiesta pagamento
         */
        $results = array();
        $results = $RequestPayment->userRequestPayment($this->user, $user_id, $request_payment_id, $doc_formato);
        $this->set('results', $results);

        $params = array('user_id' => $user_id, 'request_payment_num' => $request_payment_num);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'user_request_payment', $params, null));
        $this->set('organization', $this->user->organization);

        /*
         * dati organization per dati pagamento
         */
        App::import('Model', 'Organization');
        $Organization = new Organization;

        $options = array();
        $options['conditions'] = array('Organization.id' => (int) $this->user->organization['Organization']['id']);
        $options['recursive'] = -1;
        $organizationResults = $Organization->find('first', $options);
        $this->set('organizationResults', $organizationResults);

        /*
         * dati user per intestazione dati pagamento
         */
        App::import('Model', 'User');
        $User = new User;

        $options = array();
        $options['conditions'] = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
            'User.id' => $user_id);
        $options['recursive'] = -1;
        $userResults = $User->find('first', $options);
        $this->set('userResults', $userResults);


        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                $this->render('user_request_payment');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('user_request_payment');
                break;
        }
    }

    /*
     * stampa il carrello dell'utente passato
     */

    public function admin_userOtherCart($delivery_id, $user_id, $doc_formato) {

        if (!$this->isRoot() && !$this->isManager()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__userCart($delivery_id, $user_id, $doc_formato);
    }

    /*
     * stampa il carrello dell'utente in Sessione
     * back-office
     */

    public function admin_userCart($delivery_id, $doc_formato) {

        $user_id = $this->user->get('id');
        if ($user_id == 0) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__userCart($delivery_id, $user_id, $doc_formato);
    }

    /*
     * stampa il carrello dell'utente in Sessione
     * front-end
     */

    public function userCart($delivery_id, $doc_formato) {

        $user_id = $this->user->get('id');
        if ($user_id == 0) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__userCart($delivery_id, $user_id, $doc_formato);
    }

    private function __userCart($delivery_id, $user_id, $doc_formato) {

        if (empty($delivery_id) || empty($user_id) || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Cart');
        $Cart = new Cart;

        $results = $Cart->getUserCart($this->user, $user_id, $delivery_id);
        $this->set('results', $results);

        /*
         * loops Orders, if Order.state_code = PROCESSED-ON-DELIVERY (in carico al cassiere) faccio vedere le modifiche
         */
        $resultsWithModifies = array();
        if ($this->user->organization['Organization']['payToDelivery'] == 'ON' ||
                $this->user->organization['Organization']['payToDelivery'] == 'ON-POST') {

            if (isset($results['Delivery']))
                foreach ($results['Delivery'] as $numDelivery => $result['Delivery']) {

                    if ($result['Delivery']['totOrders'] > 0 && $result['Delivery']['totArticlesOrder'] > 0) {

                        foreach ($result['Delivery']['Order'] as $numOrder => $order) {

							/*
							 * aggiunge ad un ordine le eventuali 
							 *  SummaryOrder 
							 *  SummaryOrderTrapsort spese di trasporto
							 *  SummaryOrderMore spese generiche
							 *  SummaryOrderLess sconti
							 */
                            if ($order['Order']['state_code'] == 'PROCESSED-POST-DELIVERY' ||  //  In carico al referente dopo la consegna
								$order['Order']['state_code'] == 'PROCESSED-ON-DELIVERY' ||  //  in carico al cassiere
								$order['Order']['state_code'] == 'INCOMING-ORDER' ||  // In carico al referente con la merce arrivata
								$order['Order']['state_code'] == 'WAIT-PROCESSED-TESORIERE' || 
								$order['Order']['state_code'] == 'PROCESSED-TESORIERE' || 
								$order['Order']['state_code'] == 'TO-PAYMENT' || 
								$order['Order']['state_code'] == 'CLOSE') {

                                $resultsSummaryOrderVarius = $Cart->addSummaryOrder($this->user, $user_id, $order);

                                $resultsWithModifies[$order['Order']['id']]['SummaryOrder'] = $resultsSummaryOrderVarius['SummaryOrder'];
                                $resultsWithModifies[$order['Order']['id']]['SummaryOrderTrasport'] = $resultsSummaryOrderVarius['SummaryOrderTrasport'];
                                $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostMore'] = $resultsSummaryOrderVarius['SummaryOrderCostMore'];
                                $resultsWithModifies[$order['Order']['id']]['SummaryOrderCostLess'] = $resultsSummaryOrderVarius['SummaryOrderCostLess'];

                                // $results = $this->ExportDoc->getCartCompliteOrder($order_id, $results, $resultsSummaryOrder, $resultsSummaryOrderTrasport, $resultsSummaryOrderCostMore, $resultsSummaryOrderCostLess, $debug);					
                            }  // if($result['Order']['state_code']=='PROCESSED-ON-DELIVERY')
                        } // loops Orders
                    }
                } // loops Deliveries	   
        }
        /*
          echo "<pre>";
          print_r($resultsWithModifies);
          echo "</pre>";
         */
        $this->set('resultsWithModifies', $resultsWithModifies);

        $this->set('storeroomResults', $Cart->getUserCartStoreroom($this->user, $user_id, $delivery_id));

        $params = array('delivery_id' => $delivery_id, 'user_id' => $this->user->get('id'));
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'user_cart', $params, null));
        $this->set('organization', $this->user->organization);

        /*
         * D I S T A N C E
         */
        jimport('joomla.user.helper');
        $userProfile = JUserHelper::getProfile($this->user->get('id'));

        $numDistance = 0;
        $arrayDistances = array();
        if (!empty($userProfile->profile['lat']) && $userProfile->profile['lat'] != Configure::read('LatLngNotFound') && !empty($userProfile->profile['lng']) && $userProfile->profile['lng'] != Configure::read('LatLngNotFound')) {

            $userLat = $userProfile->profile['lat'];
            $userLng = $userProfile->profile['lng'];

            if (isset($results['Delivery']))
                foreach ($results['Delivery'] as $numDelivery => $result['Delivery']) {
                    if ($result['Delivery']['totOrders'] > 0 && $result['Delivery']['totArticlesOrder'] > 0) {
                        foreach ($result['Delivery']['Order'] as $numOrder => $order) {
                            if (isset($order['ArticlesOrder'])) { // cosi' escludo gli ordini senza acquisti			

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
                            }
                        }
                    }
                } // end foreach
        } // end if lng
        $this->set('arrayDistances', $arrayDistances);



        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                $this->render('user_cart');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('user_cart');
                break;
            /* case 'CSV':
              $this->layout = 'csv';
              $this->render('user_cart_csv');
              break;
              case 'EXCEL':
              $this->layout = 'excel';
              $this->render('user_cart_excel');
              break;
             */
        }
    }

    public function admin_articlesSupplierOrganization($supplier_organization_id, $filterType = 'Y', $filterCategory = 'Y', $filterNota = 'Y', $filterIngredienti = 'Y', $doc_formato = 'PDF') {
        $this->__articlesSupplierOrganization($supplier_organization_id, $filterType, $filterCategory, $filterNota, $filterIngredienti, $doc_formato);
    }

    public function articlesSupplierOrganization($supplier_organization_id, $filterType = 'Y', $filterCategory = 'Y', $filterNota = 'Y', $filterIngredienti = 'Y', $doc_formato = 'PDF') {

        $user_id = $this->user->get('id');
        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__articlesSupplierOrganization($supplier_organization_id, $filterType, $filterCategory, $filterNota, $filterIngredienti, $doc_formato);
    }

    public function admin_articlesOrders($order_id, $filterType = 'Y', $filterCategory = 'Y', $filterNota = 'Y', $filterIngredienti = 'Y', $doc_formato) {
        $this->__articlesOrders($order_id, $filterType, $filterCategory, $filterNota, $filterIngredienti, $doc_formato);
    }

    public function articlesOrders($order_id, $filterType = 'Y', $filterCategory = 'Y', $filterNota = 'Y', $filterIngredienti = 'Y', $doc_formato) {

        $user_id = $this->user->get('id');
        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__articlesOrders($order_id, $filterType, $filterCategory, $filterNota, $filterIngredienti, $doc_formato);
    }

    /*
     * se lo user e' referente del produttore ho anche gli articoli a stato N
     */
    private function __articlesSupplierOrganization($supplier_organization_id, $filterType, $filterCategory, $filterNota, $filterIngredienti, $doc_formato) {

        if ($supplier_organization_id == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * dati produttore
         */
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $conditions = array('SuppliersOrganization.id' => $supplier_organization_id);
        $supplier = current($SuppliersOrganization->getSuppliersOrganization($this->user, $conditions));
        $this->set('supplier', $supplier);

        /*
         * dati anagrafici articoli 
         * 	   Article, SupplierOrganization, CategoriesArticle, ArticlesType
         */
        App::import('Model', 'Article');
        $Article = new Article;

        $options = array();
        $options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
                                        'Article.supplier_organization_id' => $supplier_organization_id);

        /*
         * se lo user e' referente del produttore ho anche gli articoli a stato N
         */
        $isReferenteSupplierOrganization = false;
        if ($this->isReferentGeneric()) {
            App::import('Model', 'Order');
            $Order = new Order;

            if (!$this->isSuperReferente() && !$Order->aclReferenteSupplierOrganization($this->user, $supplier_organization_id)) {
                $options['conditions'] += array('Article.stato' => 'Y');
                $isReferenteSupplierOrganization = false;
            } else
                $isReferenteSupplierOrganization = true;
        }

        $this->set('isReferenteSupplierOrganization', $isReferenteSupplierOrganization);

        $results = $Article->getArticlesDataAnagr($this->user, $options);
        $this->set('results', $results);

        $params = array('supplier_organization_id' => $supplier_organization_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'articles_supplier_organization', $params, null));
        $this->set('organization', $this->user->organization);

        $this->set('filterType', $filterType);
        $this->set('filterCategory', $filterCategory);
        $this->set('filterNota', $filterNota);
        $this->set('filterIngredienti', $filterIngredienti);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                $this->render('articles_supplier_organization');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('articles_supplier_organization');
                break;
            case 'CSV':
                $this->layout = 'csv';
                $this->render('articles_supplier_organization_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('articles_supplier_organization_excel');
                break;
        }
    }

    public function admin_articlesSupplierDes($des_id, $des_supplier_id, $filterType = 'Y', $filterCategory = 'Y', $filterNota = 'Y', $filterIngredienti = 'Y', $doc_formato = 'PDF') {
        $this->__articlesSupplierDes($des_id, $des_supplier_id, $filterType, $filterCategory, $filterNota, $filterIngredienti, $doc_formato);
    }

    public function articlesSupplierDes($des_id, $des_supplier_id, $filterType = 'Y', $filterCategory = 'Y', $filterNota = 'Y', $filterIngredienti = 'Y', $doc_formato = 'PDF') {

        $user_id = $this->user->get('id');
        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__articlesSupplierDes($des_id, $des_supplier_id, $filterType, $filterCategory, $filterNota, $filterIngredienti, $doc_formato);
    }
    
    private function __articlesSupplierDes($des_id, $des_supplier_id, $filterType, $filterCategory, $filterNota, $filterIngredienti, $doc_formato) {

        if ($des_id == null || $des_supplier_id == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->user->organization['Organization']['hasDes']=='N') {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        
        /*
         * estraggo il GAS titolare
         */
        App::import('Model', 'DesSupplier');
        $DesSupplier = new DesSupplier;
        $DesSupplier->unbindModel(array('hasMany' => array('DesOrder')));
        $DesSupplier->unbindModel(array('belongsTo' => array()));

        $options = [];
        $options['conditions'] = array('DesSupplier.des_id' => $des_id,
                                          'DesSupplier.id' => $des_supplier_id);
        $options['recursive'] = 1;
        $desSuppliersResults = $DesSupplier->find('first', $options);
        
        $own_organization_id = $desSuppliersResults['OwnOrganization']['id'];
        $tmp_user->organization['Organization']['id'] = $own_organization_id;
        $supplier_id = $desSuppliersResults['DesSupplier']['supplier_id'];
                        
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;
        
        $options = [];
        $options['conditions'] = array('SuppliersOrganization.organization_id' => $own_organization_id,
                                          'SuppliersOrganization.supplier_id' => $supplier_id);
        $options['recursive'] = 1;
        $suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);

        $supplier_organization_id = $suppliersOrganizationResults['SuppliersOrganization']['id'];

        /*
         * dati produttore
         */
        /*
         * dati produttore
         */
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $conditions = array('SuppliersOrganization.id' => $supplier_organization_id);
        $supplier = current($SuppliersOrganization->getSuppliersOrganization($tmp_user, $conditions));
        $this->set('supplier', $supplier);
        
        
        /*
         * dati anagrafici articoli 
         * 	   Article, SupplierOrganization, CategoriesArticle, ArticlesType
         */
        App::import('Model', 'Article');
        $Article = new Article;

        $this->set('isReferenteSupplierOrganization', 'N');
        
        $options = array();
        $options['conditions'] = array('Article.organization_id' => $own_organization_id,
                                        'Article.supplier_organization_id' => $supplier_organization_id,
                                        'Article.stato' => 'Y');

        $results = $Article->getArticlesDataAnagr($tmp_user, $options);
        $this->set('results', $results);

        $params = array('supplier_organization_id' => $supplier_organization_id);
        $this->set('fileData', $this->utilsCommons->getFileData($tmp_user, $doc_options = 'articles_supplier_organization', $params, null));
        $this->set('organization', $this->user->organization);

        $this->set('filterType', $filterType);
        $this->set('filterCategory', $filterCategory);
        $this->set('filterNota', $filterNota);
        $this->set('filterIngredienti', $filterIngredienti);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                $this->render('articles_supplier_organization');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('articles_supplier_organization');
                break;
            case 'CSV':
                $this->layout = 'csv';
                $this->render('articles_supplier_organization_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('articles_supplier_organization_excel');
                break;
        }
    }
    
    /*
     * se lo user e' referente del produttore ho anche gli articoli a stato N
     */

    private function __articlesOrders($order_id, $filterType, $filterCategory, $filterNota, $filterIngredienti, $doc_formato) {

        if ($order_id == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * dati ordini associati all'ordine
         */
        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;

        App::import('Model', 'Article');

        $conditions = array('Order.id' => (int) $order_id);
        $results = $ArticlesOrder->getArticlesOrdersInOrder($this->user, $conditions);
        if (!empty($results)) {

            /*
             * aggiungo l'anagrafica (ArticlesType, CategoriesArticle, SuppliersOrganization)
             */
            $newResults = array();
            foreach ($results as $numResults => $result) {
                $id = $result['Article']['id'];

                $options = array();
                $options['conditions'] = array('Article.id' => $id);

                $Article = new Article;

				/*
				 * ridefinisco $this->user->organization['Organization']['id'] 
				 * perche' se e' DES potrebbero essere articoli di un'altor GAS 
				 */
				$tmp_user = new stdClass();
				$tmp_user->organization['Organization']['id'] = $result['Article']['organization_id'];
                $tmp_user->organization['Organization']['hasFieldArticleCategoryId'] = $this->user->organization['Organization']['hasFieldArticleCategoryId'];
                $articlesResults = $Article->getArticlesDataAnagr($tmp_user, $options);

                $newResults[$numResults] = $articlesResults;
                $newResults[$numResults]['ArticlesOrder'] = $result['ArticlesOrder'];
            }
        }
        $this->set('results', $newResults);

        /*
         *  dati ordine
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $options = array();
        $options['conditions'] = array('Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.id' => $order_id);
        $options['recursive'] = 1;
        $orderResults = $Order->find('first', $options);
        if ($orderResults['Delivery']['sys'] == 'N')
            $DeliveryLabel = $orderResults['Delivery']['luogoData'];
        else
            $DeliveryLabel = $orderResults['Delivery']['luogo'];
        $this->set('DeliveryLabel', $DeliveryLabel);

        /*
         * dati produttore
         */
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $conditions = array('SuppliersOrganization.id' => $orderResults['SuppliersOrganization']['id']);
        $supplier = current($SuppliersOrganization->getSuppliersOrganization($this->user, $conditions));
        $this->set('supplier', $supplier);

        $params = array('SuppliersOrganizationName' => $orderResults['SuppliersOrganization']['name'], 'DeliveryLabel' => $DeliveryLabel);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'articles_orders', $params, null));
        $this->set('organization', $this->user->organization);

        $this->set('filterType', $filterType);
        $this->set('filterCategory', $filterCategory);
        $this->set('filterNota', $filterNota);
        $this->set('filterIngredienti', $filterIngredienti);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                $this->render('articles_supplier_organization');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('articles_supplier_organization');
                break;
            case 'CSV':
                $this->layout = 'csv';
                $this->render('articles_supplier_organization_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('articles_supplier_organization_excel');
                break;
        }
    }

    public function usersData($userGroupIds, $filterUsersImg = 'N', $doc_formato) {

        $debug = false;

        $user_id = $this->user->get('id');
        if ($user_id == 0) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__usersData($userGroupIds, $doc_formato, $debug);

        $this->set('filterUsersImg', $filterUsersImg);
        $this->set('userGroupIds', $userGroupIds);
        $this->set('isRoot', $this->isRoot());

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('users_data');
                break;
            case 'CSV':
                $this->layout = 'csv';
                $this->render('users_data_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('users_data_excel');
                break;
        }
    }

    public function admin_usersData($userGroupIds, $filterUsersImg = 'N', $doc_formato) {

        $debug = false;

        $this->__usersData($userGroupIds, $doc_formato, $debug);

        $this->set('filterUsersImg', $filterUsersImg);
        $this->set('userGroupIds', $userGroupIds);
        $this->set('isRoot', $this->isRoot());

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('users_data');
                break;
            case 'CSV':
                $this->layout = 'csv';
                $this->render('users_data_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('users_data_excel');
                break;
        }
    }

    private function __usersData($userGroupIds, $doc_formato, $debug) {

        if ($doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'User');
        $User = new User;

        $conditions = array();

        /*
         * filtro per userGroups
         */
        if (!empty($userGroupIds)) {
            $userGroupIds = explode(',', $userGroupIds);

            if ($debug) {
                echo "<pre>";
                print_r($userGroupIds);
                echo "</pre>";
            }

            /*
             * ciclo per gli userGroups passati per evitare che vengano passati group_id strano 
             */
            $tmp = '';
            foreach ($userGroupIds as $userGroupId) {
                if ($userGroupId == Configure::read('group_id_user'))
                    $tmp .= Configure::read('group_id_user') . ',';
                else
                if ($userGroupId == Configure::read('group_id_manager'))
                    $tmp .= Configure::read('group_id_manager') . ',';
                else
                if ($userGroupId == Configure::read('group_id_manager_delivery'))
                    $tmp .= Configure::read('group_id_manager_delivery') . ',';
                else
                if ($userGroupId == Configure::read('group_id_cassiere'))
                    $tmp .= Configure::read('group_id_cassiere') . ',';
                else
                if ($userGroupId == Configure::read('group_id_tesoriere'))
                    $tmp .= Configure::read('group_id_tesoriere') . ',';
                else
                if ($userGroupId == Configure::read('group_id_super_referent'))
                    $tmp .= Configure::read('group_id_super_referent') . ',';
                else
                if ($userGroupId == Configure::read('group_id_referent'))
                    $tmp .= Configure::read('group_id_referent') . ',';
                else
                if ($userGroupId == Configure::read('group_id_generic'))
                    $tmp .= Configure::read('group_id_generic') . ',';
            } // end foreach($userGroupIds as $userGroupId)

            $tmp = substr($tmp, 0, (strlen($tmp) - 1));

            $conditions += array('UserGroup.group_id' => $tmp);
        }
        else {
            
        } // end if($userGroupIds!='ALL')

        $results = $User->getUsersComplete($this->user, $conditions);
        $this->set('results', $results);

        if ($debug) {
            echo "<pre>";
            print_r($conditions);
            print_r($results);
            echo "</pre>";

            exit;
        }

        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'users_data', null, null));
        $this->set('organization', $this->user->organization);
    }

    public function admin_usersDateData($FilterUserSort = 'NAME', $doc_formato) {

        $debug = false;

        App::import('Model', 'User');
        $User = new User;
        
		$conditions['User.block'] = "User.block IN ('0','1')";
        
        if($FilterUserSort=='NAME')
        	$FilterUserSort = Configure::read('orderUser');
        else 
        if($FilterUserSort=='REGISTERDATA')
        	$FilterUserSort = 'User.registerDate';
                        
        $results = $User->getUsersComplete($this->user, $conditions, $FilterUserSort, false);
        $this->set('results', $results);
 
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'users_data', null, null));
        $this->set('organization', $this->user->organization);        
        
        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('users_date');  // mai utilizzato
                break;
            case 'CSV':
                $this->layout = 'csv';
                $this->render('users_date_csv');   // mai utilizzato
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('users_date_excel');
                break;
        }
    }

    public function referentsData($filterOrder = 'SUPPLIERS', $doc_formato) {

        $debug = false;

        $user_id = $this->user->get('id');
        if ($user_id == 0) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__referentsData($filterOrder, $doc_formato, $debug);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('referents_data');
                break;
            case 'CSV':
                $this->layout = 'csv';
                $this->render('referents_data_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('referents_data_excel');
                break;
        }
    }

    public function admin_referentsData($filterOrder = 'SUPPLIERS', $doc_formato) {

        $debug = false;

        $user_id = $this->user->get('id');
        if ($user_id == 0) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__referentsData($filterOrder, $doc_formato, $debug);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('referents_data');
                break;
            case 'CSV':
                $this->layout = 'csv';
                $this->render('referents_data_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('referents_data_excel');
                break;
        }
    }

    private function __referentsData($filterOrder, $doc_formato, $debug) {

        if ($doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * ottengo referenti del produttore
         */
        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

        $conditions = array('User.block' => 0);
        if ($filterOrder == 'USERS')
            $orderBy = Configure::read('orderUser');
        else
        if ($filterOrder == 'SUPPLIERS')
            $orderBy = 'SuppliersOrganization.name';
        $results = $SuppliersOrganizationsReferent->getReferentsCompact($this->user, $conditions, $orderBy);
        $this->set('results', $results);

        if ($debug) {
            echo "<pre>";
            print_r($conditions);
            print_r($results);
            echo "</pre>";

            exit;
        }

        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'referents_data', null, null));
        $this->set('organization', $this->user->organization);
    }

    public function usersDelivery($delivery_id, $doc_formato) {

        $user_id = $this->user->get('id');
        if ($user_id == 0) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        $this->__usersDelivery($delivery_id, $doc_formato);
    }

    public function admin_usersDelivery($delivery_id, $doc_formato) {
        $this->__usersDelivery($delivery_id, $doc_formato);
    }

    /*
     *  doc_options
     * 		des-referent-to-supplier            $a = codice
     * 		des-referent-to-supplier-details    $a = codice
     * 		des-referent-to-supplier-split-org  $a = codice
     * 		des-referent-to-supplier-monitoring  $a = codice
     *      des-referent-to-supplier-split-org-monitoring  $a = codice
     */

    public function admin_exportToDes($des_order_id, $doc_options = null, $doc_formato = null, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null, $g = null, $h = null, $format = 'notmpl') {

        /* ctrl ACL */
        if ($this->user->organization['Organization']['hasDes'] == 'N' || !$this->isDes()) {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */

        // $debug = true;

        if ($debug) {
            echo "<br />doc_options " . $doc_options;
            echo "<br />a " . $a;
            echo "<br />b " . $b;
            echo "<br />c " . $c;
            echo "<br />d " . $d;
            echo "<br />e " . $e;
            echo "<br />f " . $f;
            echo "<br />g " . $g;
            echo "<br />h " . $h;
        }

        App::import('Model', 'De');
        $De = new De;

        /*
         *  tutti i dati del DesOrder
         */
        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder;

        $desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
        $this->set('desOrdersResults', $desOrdersResults);


        if ($doc_options == 'des-referent-to-supplier-split-org' || $doc_options == 'des-referent-to-supplier-split-org-monitoring') {

            $newResults = array();

            if (!empty($desOrdersResults['DesOrdersOrganizations']))
                foreach ($desOrdersResults['DesOrdersOrganizations'] as $numResult => $resultDesOrdersOrganization) {

                    $newResults[$numResult] = $resultDesOrdersOrganization;

                    $orderBy = array('Organization.name', 'Article.name ASC', 'Article.id ASC');
                    $conditions = array('Cart.order_id' => $resultDesOrdersOrganization['DesOrdersOrganization']['order_id'],
										'Cart.stato' => 'Y',
										'Cart.deleteToReferent' => 'N');
                    $newResults[$numResult]['Cart'] = $De->getArticoliAcquistatiDaUtenteInDesOrdine($this->user, $conditions, $orderBy);
                } // loop DesOrdersOrganizations

                /*
                  echo "<pre>";
                  print_r($newResults);
                  echo "</pre>";
                 */

            $this->set('results', $newResults);
        } else {
            $order_ids = '';
            foreach ($desOrdersResults['DesOrdersOrganizations'] as $numResult => $resultDesOrdersOrganization)
                $order_ids .= $resultDesOrdersOrganization['DesOrdersOrganization']['order_id'] . ',';

            $order_ids = substr($order_ids, 0, strlen($order_ids) - 1);

            $conditions = array('Cart.order_id' => $order_ids,
								'Cart.stato' => 'Y',
								'Cart.deleteToReferent' => 'N');
            switch ($doc_options) {
                case 'des-referent-to-supplier':
                case 'des-referent-to-supplier-monitoring':
                    $orderBy = array('Article.name ASC', 'Article.id ASC');
                    break;
                case 'des-referent-to-supplier-details':
                    $orderBy = array('Article.name ASC', 'Article.id ASC', 'Organization.name');
                    break;
            }

            $results = $De->getArticoliAcquistatiDaUtenteInDesOrdine($this->user, $conditions, $orderBy);

            /*
             * aggrego i dati per articolo/organization
             */
            if ($doc_options == 'des-referent-to-supplier-details')
                $results = $De->getAggregateArticoliOrganization($this->user, $results);
            else
            if ($doc_options == 'des-referent-to-supplier-monitoring')
                $results = $De->getAggregateArticoli($this->user, $results);

            $this->set('results', $results);
        }

        /*
         * monitoring
         */
        if ($doc_options == 'des-referent-to-supplier-monitoring' || $doc_options == 'des-referent-to-supplier-split-org-monitoring') {

            /*
              echo "<pre>";
              print_r($desOrdersResults);
              echo "</pre>";
             */            
            /*
             * rispetto all'ordine del titolare ctrl se il suo ordine 
             * 		dev'essere validato (ArticlesOrder.pezzi_confezione > 1) per la gestione dei colli
             */
            $order_id = 0;
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
        } // end if ($doc_options == 'des-referent-to-supplier-monitoring' || $doc_options == 'des-referent-to-supplier-split-org-monitoring')
        
        
        $params = array('des_order_id' => $des_order_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params, $user_target = 'REFERENTE'));
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'des-referent-to-supplier') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier');
                } else
                if ($doc_options == 'des-referent-to-supplier-monitoring') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_monitoring');
                } else
                if ($doc_options == 'des-referent-to-supplier-details') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_details');
                } else
                if ($doc_options == 'des-referent-to-supplier-split-org') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_split_org');
                } else
                if ($doc_options == 'des-referent-to-supplier-split-org-monitoring') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_split_org_monitoring');
                }
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'des-referent-to-supplier') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier');
                } else
                if ($doc_options == 'des-referent-to-supplier-monitoring') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_monitoring');
                } else
                if ($doc_options == 'des-referent-to-supplier-details') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_details');
                } else
                if ($doc_options == 'des-referent-to-supplier-split-org') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_split_org');
                } else
                if ($doc_options == 'des-referent-to-supplier-split-org-monitoring') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_split_org_monitoring');
                }
                break;
            case 'CSV':
                $this->layout = 'csv';
                if ($doc_options == 'des-referent-to-supplier-monitoring') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_monitoring_csv');
                } else
                if ($doc_options == 'des-referent-to-supplier') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_csv');
                } else
                if ($doc_options == 'des-referent-to-supplier-details') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_details_csv');
                } else
                if ($doc_options == 'des-referent-to-supplier-split-org') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_split_org_csv');
                } else
                if ($doc_options == 'des-referent-to-supplier-split-org-monitoring') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_split_org_monitoring_csv');
                }
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'des-referent-to-supplier') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_excel');
                } else
                if ($doc_options == 'des-referent-to-supplier-monitoring') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_monitoring_excel');
                } else
                if ($doc_options == 'des-referent-to-supplier-details') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_details_excel');
                } else
                if ($doc_options == 'des-referent-to-supplier-split-org') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_split_org_excel');
                } else
                if ($doc_options == 'des-referent-to-supplier-split-org-monitoring') {
                    $this->set('showCodice', $a);
                    $this->render('des_referent_to_supplier_split_org_monitoring_excel');
                }
                break;
        }
    }

    /*
     * estratto tutti i pagamenti POS SummaryDeliveriesPos di un anno
     * $doc_options=to-cassiere-pos
     */

    public function admin_exportToCassiereImportoPos($year_pos, $doc_options = null, $doc_formato = null) {

        /* ctrl ACL */
        if ($this->user->organization['Organization']['hasFieldPaymentPos'] == 'N' || !$this->isCassiere()) {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */

        App::import('Model', 'SummaryDeliveriesPos');
        $SummaryDeliveriesPos = new SummaryDeliveriesPos;

        $options = array();
        $options['conditions'] = array('SummaryDeliveriesPos.organization_id' => $this->user->organization['Organization']['id'],
            'DATE_FORMAT(SummaryDeliveriesPos.created, "%Y")' => (int) $year_pos);
        $options['recursive'] = 1;
        $options['order'] = array('User.name', 'Delivery.data');
        $summaryDeliveriesPosResults = $SummaryDeliveriesPos->find('all', $options);

        foreach ($summaryDeliveriesPosResults as $numResult => $summaryDeliveriesPosResult) {
            /*
             * ctrl se delivery in StatDelivery 
             */
            if (!empty($summaryDeliveriesPosResult['StatDelivery']['id'])) {
                $summaryDeliveriesPosResult[$numResult]['Delivery'] = $summaryDeliveriesPosResult['StatDelivery'];
            }

            unset($summaryDeliveriesPosResult[$numResult]['StatDelivery']);
        }

        /*
          echo "<pre>";
          print_r($summaryDeliveriesPosResults);
          echo "</pre>";
         */

        $this->set('results', $summaryDeliveriesPosResults);

        $params = array('year_pos' => $year_pos);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options, $params));
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                if ($doc_options == 'to-cassiere-pos')
                    $this->render('cassiere_pos');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                if ($doc_options == 'to-cassiere-pos')
                    $this->render('cassiere_pos');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'to-cassiere-pos')
                    $this->render('cassiere_pos_excel');
                break;
        }
    }

    public function admin_suppliersOrganizations($doc_formato = null) {
        $this->__admin_suppliersOrganizations($doc_formato);
    }

    public function suppliersOrganizations($doc_formato = null) {
        $this->__admin_suppliersOrganizations($doc_formato);
    }

    private function __admin_suppliersOrganizations($doc_formato) {

        /*
         * dati produttore
         */
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization')));

        $options = array();
        $options['conditions'] = array('SuppliersOrganization.organization_id' => (int) $this->user->organization['Organization']['id']);
        /*
          if(!$this->isRoot() && !$this->isManager() && !$this->isSuperReferente())
          $options['conditions'] += array('SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');
         */
        $options['order'] = array('SuppliersOrganization.name');
        $options['recursive'] = 0;
        $results = $SuppliersOrganization->find('all', $options);

        foreach ($results as $numResult => $result) {

            /*
             * SuppliersOrganizationsReferent 
             */
            $options = array();
            $options['conditions'] = array('SuppliersOrganizationsReferent.supplier_organization_id' => $result['SuppliersOrganization']['id'],
                'SuppliersOrganizationsReferent.organization_id' => (int) $this->user->organization['Organization']['id']);
            $suppliersOrganizationsReferents = $SuppliersOrganization->SuppliersOrganizationsReferent->find('all', $options);
            if (!empty($suppliersOrganizationsReferents)) {
                foreach ($suppliersOrganizationsReferents as $numResult2 => $suppliersOrganizationsReferent) {
                    $results[$numResult]['SuppliersOrganizationsReferent'][$numResult2]['User'] = $suppliersOrganizationsReferent['User'];
                    $results[$numResult]['SuppliersOrganizationsReferent'][$numResult2]['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent['SuppliersOrganizationsReferent'];
                }
            } else
                $results[$numResult]['SuppliersOrganizationsReferent'] = null;

            /*
             * totale articoli
             */
            $results[$numResult]['Articles'] = $SuppliersOrganization->getTotArticlesAttivi($this->user, $result['SuppliersOrganization']['id']);
        }
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
        $this->set('results', $results);

        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'to_suppliers_organizations', $params, null));
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                $this->render('suppliers_organizations');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('suppliers_organizations');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('suppliers_organizations_excel');
                break;
        }
    }

    private function __usersDelivery($delivery_id, $doc_formato) {

        $debug = false;

        if ($delivery_id == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'User');
        $User = new User;

        $conditions = array('Delivery.id' => $delivery_id);
        $results = $User->getUserWithCartByDelivery($this->user, $conditions);

        /*
         *  per ogni user estraggo la qta
         */
        foreach ($results as $numResults => $result) {

            $user_id = $result['User']['id'];

            $sql = "SELECT 
						Cart.qta, Cart.qta_forzato 
					FROM 
						" . Configure::read('DB.prefix') . "deliveries as Delivery,
						" . Configure::read('DB.prefix') . "orders as `Order`,
						" . Configure::read('DB.prefix') . "carts as Cart
					WHERE 
						Delivery.organization_id = " . $this->user->organization['Organization']['id'] . "
						AND `Order`.organization_id = " . $this->user->organization['Organization']['id'] . "
						AND Cart.organization_id = " . $this->user->organization['Organization']['id'] . "
						AND Cart.deleteToReferent = 'N' 
						AND Cart.stato = 'Y' 
						AND `Order`.isVisibleFrontEnd = 'Y' 
						AND `Order`.isVisibleBackOffice = 'Y' 
						AND Delivery.id = Order.delivery_id
						AND Cart.order_id = Order.id
						AND Cart.user_id = $user_id 
						AND Delivery.id = $delivery_id ";
            if ($debug)
                echo '<br />' . $sql;
            $cartResults = $this->ExportDoc->query($sql);

            $tot_user_qta = 0;
            foreach ($cartResults as $cartResult) {
                if ($cartResult['Cart']['qta_forzato'] == 0)
                    $tot_user_qta += $cartResult['Cart']['qta'];
                else
                    $tot_user_qta += $cartResult['Cart']['qta_forzato'];
            }

            $results[$numResults]['User']['cart_qta_tot'] = $tot_user_qta;
        } // foreach $users	

        $this->set('results', $results);

        $params = array('delivery_id' => $delivery_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'users_data_delivery', $params, null));
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('users_delivery');
                break;
            case 'CSV':
                $this->layout = 'csv';
                $this->render('users_delivery_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('users_delivery_excel');
                break;
        }
    }

    public function admin_cashsData($doc_formato) {

        $debug = false;

        $user_id = $this->user->get('id');
        if ($user_id == 0) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        App::import('Model', 'User');
        $User = new User;

        App::import('Model', 'Cash');
        $Cash = new Cash;

        $options = array();
        $options['conditions'] = array('User.organization_id' => $this->user->organization['Organization']['id'],
            'User.block' => 0);

        $options['recursive'] = -1;
        $options['order'] = Configure::read('orderUser');
        $results = $User->find('all', $options);

        foreach ($results as $numResult => $result) {

            $options = array();
            $options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
                'Cash.user_id' => $result['User']['id']);
            $userResults = $Cash->find('first', $options);
            if (!empty($userResults))
                $results[$numResult]['Cash'] = $userResults['Cash'];
            else {
                $results[$numResult]['Cash']['importo'] = '0.00';
                $results[$numResult]['Cash']['importo_'] = '0,00';
                $results[$numResult]['Cash']['importo_e'] = '0,00 &euro;';
                $results[$numResult]['Cash']['nota'] = '';
            }
        }

        $this->set(compact('results'));

        $fileData['fileTitle'] = "Cassa";
        $fileData['fileName'] = "cassa";

        $this->set('fileData', $fileData);
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('cashs_data');
                break;
            case 'CSV': // mai utilizzato
                $this->layout = 'csv';
                $this->render('cashs_data_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('cashs_data_excel');
                break;
        }
    }

    public function admin_cashsHistoryData($doc_formato) {

        $debug = false;

        $user_id = $this->user->get('id');
        if ($user_id == 0) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        App::import('Model', 'User');
        $User = new User;

        App::import('Model', 'Cash');
        $Cash = new Cash;

        App::import('Model', 'CashesHistory');
        
        $options = array();
        $options['conditions'] = array('User.organization_id' => $this->user->organization['Organization']['id'],
                                       'User.block' => 0);

        $options['recursive'] = -1;
        $options['order'] = Configure::read('orderUser');
        $results = $User->find('all', $options);

        foreach ($results as $numResult => $result) {

            $options = array();
            $options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
                'Cash.user_id' => $result['User']['id']);
            $userResults = $Cash->find('first', $options);
            if (!empty($userResults))
                $results[$numResult]['Cash'] = $userResults['Cash'];
            else {
                $results[$numResult]['Cash']['importo'] = '0.00';
                $results[$numResult]['Cash']['importo_'] = '0,00';
                $results[$numResult]['Cash']['importo_e'] = '0,00 &euro;';
                $results[$numResult]['Cash']['nota'] = '';
            }
            
            /*
             * storico di cassa
             */
            $CashesHistory = new CashesHistory;
            
        
            $options = array();
            $options['conditions'] = array('CashesHistory.organization_id' => $this->user->organization['Organization']['id'],
                                           'CashesHistory.user_id' => $result['User']['id']);

            $options['recursive'] = -1;
            $options['order'] = array('CashesHistory.created asc');
            $cashesHistoryResults = $CashesHistory->find('all', $options);

            if(!empty($cashesHistoryResults))
                $results[$numResult]['CashesHistory'] = $cashesHistoryResults;
        }

        $this->set(compact('results'));

        $fileData['fileTitle'] = "Cassa con storico";
        $fileData['fileName'] = "cassa_con_storico";

        $this->set('fileData', $fileData);
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';  // mai utilizzato
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('cashs_history_data');
                break;
            case 'CSV': // mai utilizzato
                $this->layout = 'csv';
                $this->render('cashs_history_data_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('cashs_history_data_excel');
                break;
        }
    }
    
    public function admin_exportToArticlesWeight($delivery_id = 0, $order_id = 0, $doc_options = null, $doc_formato = null) {

        $this->ctrlHttpReferer();

        Configure::write('debug', 0);

        if ($doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;

        $conditions = array('Order.id' => $order_id,
            'Order.organization_id' => $this->tmp_user->organization['Organization']['id'],
			'Cart.stato' => 'Y',
            'Cart.deleteToReferent' => 'N');
        $orderBy = array('Article' => 'Article.id');
        $results = $ArticlesOrder->getArticoliAcquistatiDaUtenteInOrdine($this->tmp_user, $conditions, $orderBy);
        /*
          echo "<pre>";
          print_r($conditions);
          print_r($results);
          echo "</pre>";
         */
        // 	'PZ', 'GR', 'HG', 'KG', 'ML', 'DL', 'LT'
        $newResults = array();
        $pesoResults = array();
        $peso_gr_totale = 0;
        $peso_ml_totale = 0;
        $peso_pz_totale = 0;

        if (!empty($results))
            foreach ($results as $result) {

                $qta_article_gr = 0;
                $qta_article_ml = 0;
                $qta_article_pz = 0;

                $um = $result['Article']['um'];

                /*
                 * qta ordinata
                 */
                if (!empty($result['Cart']['qta_forzato']))
                    $qta = $result['Cart']['qta_forzato'];
                else
                    $qta = $result['Cart']['qta'];

                if ($debug)
                    echo "<br />------------------------";
                if ($debug)
                    echo "<br />quantita ordinata " . $qta . " sull'articolo " . $result['ArticlesOrder']['name'] . ' (' . $result['Article']['id'] . ')';
                if ($debug)
                    echo "<br />unita misura dell'articolo " . $um;

                /*
                 * porto tutto al grammo, millilitro
                 */
                if ($um == 'KG')
                    $qta_article_gr = ($result['Article']['qta'] * 1000);
                else
                if ($um == 'HG')
                    $qta_article_gr = ($result['Article']['qta'] * 100);
                else
                if ($um == 'GR')
                    $qta_article_gr = $result['Article']['qta'];
                else
                if ($um == 'LT')
                    $qta_article_ml = ($result['Article']['qta'] * 1000);
                else
                if ($um == 'DL')
                    $qta_article_ml = ($result['Article']['qta'] * 100);
                else
                if ($um == 'ML')
                    $qta_article_ml = $result['Article']['qta'];
                else
                if ($um == 'PZ')
                    $qta_article_pz = $result['Article']['qta'];

                /*
                 *  incremento i pesi
                 */
                if ($qta_article_gr > 0) {
                    $peso_gr_totale += ($qta * $qta_article_gr);
                    $newResults[$result['Article']['id']]['Peso']['kg'] += ($qta * $qta_article_gr);
                } else
                if ($qta_article_ml > 0) {
                    $peso_ml_totale += ($qta * $qta_article_ml);
                    $newResults[$result['Article']['id']]['Peso']['lt'] += ($qta * $qta_article_ml);
                } else
                if ($qta_article_pz > 0) {
                    $peso_pz_totale += ($qta * $qta_article_pz);
                    $newResults[$result['Article']['id']]['Peso']['pz'] += ($qta * $qta_article_pz);
                }

                if ($debug)
                    echo "<br />peso incrementato in GR " . $peso_gr_totale;
                if ($debug)
                    echo "<br />peso incrementato in ML " . $peso_ml_totale;
                if ($debug)
                    echo "<br />peso incrementato in PZ " . $peso_pz_totale;


                $newResults[$result['Article']['id']]['Article']['organization_id'] = $result['Article']['organization_id'];
                $newResults[$result['Article']['id']]['Article']['id'] = $result['Article']['id'];
				/*
				 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
				 */					
				if(!empty($result['ArticlesOrder']['name']))
					$newResults[$result['Article']['id']]['ArticlesOrder']['name'] = $result['ArticlesOrder']['name'];
				else
					$newResults[$result['Article']['id']]['ArticlesOrder']['name'] = $result['Article']['name'];
                $newResults[$result['Article']['id']]['Article']['codice'] = $result['Article']['codice'];
                $newResults[$result['Article']['id']]['Article']['bio'] = $result['Article']['bio'];
                $newResults[$result['Article']['id']]['Article']['img1'] = $result['Article']['img1'];
                $newResults[$result['Article']['id']]['Article']['qta'] = $result['Article']['qta'];
                $newResults[$result['Article']['id']]['Article']['um'] = $result['Article']['um'];
                $newResults[$result['Article']['id']]['Article']['um_riferimento'] = $result['Article']['um_riferimento'];
                $newResults[$result['Article']['id']]['ArticlesOrder']['prezzo'] = $result['ArticlesOrder']['prezzo'];
                $newResults[$result['Article']['id']]['ArticlesOrder']['prezzo_'] = $result['ArticlesOrder']['prezzo_'];
                $newResults[$result['Article']['id']]['ArticlesOrder']['prezzo_e'] = $result['ArticlesOrder']['prezzo_e'];
                $newResults[$result['Article']['id']]['ArticlesOrder']['qta_cart'] = $result['ArticlesOrder']['qta_cart'];
            } // end loop ArticlesOrder
        if ($peso_gr_totale > 0)
            $peso_kg = ($peso_gr_totale / 1000);
        else
            $peso_kg = $peso_gr_totale;
        $pesoResults['peso_kg'] = $peso_kg;

        if ($peso_ml_totale > 0)
            $peso_lt = ($peso_ml_totale / 1000);
        else
            $peso_lt = $peso_ml_totale;
        $pesoResults['peso_lt'] = $peso_lt;

        $pesoResults['peso_pz'] = $peso_pz_totale;

        $this->set('results', $newResults);
        $this->set('pesoResults', $pesoResults);

        $fileData['fileTitle'] = "Articoli monitoraggio peso";
        $fileData['fileName'] = "articoli_monitoraggio_peso";

        $this->set('fileData', $fileData);
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
                $this->render('referent_to_articles_weight');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('referent_to_articles_weight');
                break;
            case 'CSV': // mai utilizzato
                $this->layout = 'csv';
                $this->render('referent_to_articles_weight_csv');
                break;
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('referent_to_articles_weight_excel');
                break;
        }
    }

    public function admin_organizationsPayment($year, $doc_formato = null) {

        App::import('Model', 'Organization');
        $Organization = new Organization;

        App::import('Model', 'OrganizationsPay');
        $OrganizationsPay = new OrganizationsPay;

        $options = array();
        $options['order'] = array('Organization.name');
        $options['recursive'] = -1;

        $results = $Organization->find('all', $options);


        $resultsNew = array();
        foreach ($results as $numResult => $result) {
            $organization_id = $result['Organization']['id'];

            $tot_users = $OrganizationsPay->totUsers($organization_id);

            /*
			 * tolgo info@nomegas.portalgas.it
			 * eventuale dispensa@nomegas.portalgas.it
			 */
			$paramsConfig = json_decode($result['Organization']['paramsConfig'], true); 
			if($paramsConfig['hasStoreroom']=='Y') 
				$users_default = 2;
			else
				$users_default = 1;
			$tot_users = ($tot_users - $users_default);
			
            $tot_orders = $OrganizationsPay->totOrders($organization_id, $year);

            $tot_suppliers_organizations = $OrganizationsPay->totSuppliersOrganizations($organization_id);

            $tot_articles = $OrganizationsPay->totArticlesOrganizations($organization_id);

            $resultsNew[$numResult] = $result;
            $resultsNew[$numResult]['OrganizationsPay']['id'] = 0;
            $resultsNew[$numResult]['OrganizationsPay']['year'] = $year;
            $resultsNew[$numResult]['OrganizationsPay']['tot_users'] = $tot_users;
            $resultsNew[$numResult]['OrganizationsPay']['tot_orders'] = $tot_orders;
            $resultsNew[$numResult]['OrganizationsPay']['tot_suppliers_organizations'] = $tot_suppliers_organizations;
            $resultsNew[$numResult]['OrganizationsPay']['tot_articles'] = $tot_articles;
            
            /*
             * verifica se ho pagato
             */
		    $options = array();
	        $options['conditions'] = array('OrganizationsPay.organization_id' => $organization_id, 
	        							   'OrganizationsPay.year' => $year);
	        $options['fields'] = array('OrganizationsPay.importo', 'OrganizationsPay.data_pay', 'OrganizationsPay.beneficiario_pay', 'OrganizationsPay.type_pay');
	        $options['recursive'] = -1;
	        $organizationsPayResults = $OrganizationsPay->find('first', $options);
             
            $resultsNew[$numResult]['OrganizationsPay']['beneficiario_pay'] = $organizationsPayResults['OrganizationsPay']['beneficiario_pay'];
            $resultsNew[$numResult]['OrganizationsPay']['type_pay'] = $organizationsPayResults['OrganizationsPay']['type_pay'];
            $resultsNew[$numResult]['OrganizationsPay']['data_pay'] = $organizationsPayResults['OrganizationsPay']['data_pay'];
            $resultsNew[$numResult]['OrganizationsPay']['importo_pagato'] = $organizationsPayResults['OrganizationsPay']['importo'];

            if (!empty($result['Organization']['paramsPay'])) {
                $paramsPay = json_decode($result['Organization']['paramsPay'], true);
                $resultsNew[$numResult]['Organization'] += $paramsPay;
            }
        }
        $this->set('results', $resultsNew);

        $fileData['fileTitle'] = "Organizzazioni dati pagamento $year";
        $fileData['fileName'] = "organizzazioni_dati_pagamento_$year";

        $this->set('fileData', $fileData);
        $this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'EXCEL':
                $this->layout = 'excel';
                $this->render('organizations_payment_excel');
                break;
        }
    }

}
