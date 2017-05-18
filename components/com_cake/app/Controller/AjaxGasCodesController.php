<?php

App::uses('AppController', 'Controller');

class AjaxGasCodesController extends AppController {

    public $components = array('ActionsDesOrder');

    public function beforeFilter() {
        $this->ctrlHttpReferer();

        parent::beforeFilter();

        /*
         * ctrl ACL - per tutti gli admin_box_ che l'utente sia referente
         */
        if ($this->utilsCommons->string_starts_with($this->action, 'admin_box_')) {

            if ($this->isSuperReferente() || $this->isTesoriereGeneric() || $this->isCassiereGeneric() || $this->isReferentDesAllGas()) {
                
            } else {
                if (!empty($this->order_id)) {

                    App::import('Model', 'Order');
                    $Order = new Order;
                    if (!$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
                        $this->Session->setFlash(__('msg_not_permission'));
                        $this->myRedirect(Configure::read('routes_msg_stop'));
                    }
                }
            }
        }
        /* ctrl ACL */
    }

    public function admin_box_orders() {
        $this->__box_orders($this->delivery_id, $this->order_id);
        $this->render('admin_box_orders');
    }

    public function admin_box_orders_history() {
        $this->__box_orders($this->delivery_id, $this->order_id);
        $this->render('admin_box_orders_history');
    }

    /*
     * se arrivo da Orders/admin_index.ctp $order_id e' valorizzato
     */

    private function __box_orders($delivery_id, $order_id = 0) {
        if (empty($delivery_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $ACLsuppliersIdsOrganization = $this->user->get('ACLsuppliersIdsOrganization');

        App::import('Model', 'Order');
        $Order = new Order;

        $conditions = array('Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.delivery_id' => $delivery_id,
            'Order.isVisibleBackOffice' => 'Y');
        if (!$this->isSuperReferente())
            $conditions += array('Order.supplier_organization_id IN (' . $this->user->get('ACLsuppliersIdsOrganization') . ')');

        $results = $Order->find('all', array('conditions' => $conditions, 'order' => 'Order.data_inizio ASC', 'recursive' => 0));

        /*
         *  ctrl che order_id passato sia tra uno di quelli associati alle consegna 
         *  perche' se cambio dalla tendina la consegna mi rimande $this->order_id vecchio 
         */
        $order_id_associato_delivery = false;

        $orders = array();
        if (!empty($results)) {
            foreach ($results as $result) {

                if ($order_id == $result['Order']['id'])
                    $order_id_associato_delivery = true;

                if ($result['Order']['data_fine_validation'] != '0000-00-00')
                    $data_fine = $result['Order']['data_fine_validation_'];
                else
                    $data_fine = $result['Order']['data_fine_'];

                $orders[$result['Order']['id']] = $result['SuppliersOrganization']['name'] . ' - dal ' . $result['Order']['data_inizio_'] . ' al ' . $data_fine;
            }
        } else
            $order_id = 0; // lo setto a 0 cosi' il dettaglio dell'ordine non viene eseguito

        if (!$order_id_associato_delivery)
            $order_id = 0;
        $this->set('order_id', $order_id);

        $this->set(compact('orders'));

        $this->layout = 'ajax';
    }

    public function admin_box_order_details() {

        $debug = false;

        if (empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $conditions = array('Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.isVisibleBackOffice' => 'Y',
            'Order.id' => $this->order_id);
        $Order->unbindModel(array('belongsTo' => array('SuppliersOrganization', 'Delivery')));
        $results = $Order->find('first', array('conditions' => $conditions, 'recursive' => 0));

        $this->set('results', $results);

        /*
         * DES
         */
        $desOrdersOrganizationResults = [];
        if ($this->user->organization['Organization']['hasDes'] == 'Y') {

            App::import('Model', 'DesOrdersOrganization');
            $DesOrdersOrganization = new DesOrdersOrganization();

            $desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
        } // DES
        $this->set(compact('desOrdersOrganizationResults'));

        $this->layout = 'ajax';
    }

    /*
     * call_action: valore di $this->action
     * 		admin_managementCartsOne           se chiamato da controller=Carts&action=managementCartsOne
     * 		admin_managementCartsGroupByUsers  se chiamato da controller=Carts&action=managementCartsGroupByUsers
     * 		admin_referentDocsExport           se chiamato da controller=Docs&action=referentDocsExport
     * 		admin_referentDocsExportHistory    se chiamato da controller=Docs&action=referentDocsExportHistory
     * 		admin_cassiere_docs_export         se chiamato da controller=Docs&action=cassiere_docs_export
     * 		admin_validationCarts              se chiamato da controller=Carts&action=validationCarts
     * 		admin_summary_orders              se chiamato da controller=Referente&action=summary_orders
     */

    public function admin_box_order_permission($order_id = 0, $call_action) {
        if (empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if (empty($call_action))
            $call_action = $this->action;

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $Order->unbindModel(array('belongsTo' => array('SuppliersOrganization')));
        $conditions = array('Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.id' => $this->order_id);
        $results = $Order->find('first', array('conditions' => $conditions, 'recursive' => 1));

        /*
         * ctrl visibilita' in backOffice
         */
        if ($results['Delivery']['isVisibleBackOffice'] == 'N') {
            $this->Session->setFlash(__('msg_not_delivery_visible_backoffice'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        if ($results['Order']['isVisibleBackOffice'] == 'N') {
            $this->Session->setFlash(__('msg_not_order_visible_backoffice'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * permission per abilitazione modifica del carrello
         */
        $permissions = array('isReferentGeneric' => $this->isReferentGeneric(),
            'isTesoriereGeneric' => $this->isTesoriereGeneric(),
            'isCassiereGeneric' => $this->isCassiereGeneric());
        $this->set('permissions', $permissions);

        $this->set('results', $results);
        $this->set('call_action', $call_action);

        $this->layout = 'ajax';
    }

    /*
     * call Tesoriere::home
     */

    public function admin_box_users_delivery($delivery_id = 0) {

        if (empty($delivery_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            //$this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $results = $this->__users_delivery($delivery_id);

        $this->set('users', $results);
        $this->layout = 'ajax';
    }

    /*
     * call Cassiere::home
     * 
     * 	$delivery_id e $user_id sono valorizzati se ho Submit, li passo cosi' ricarico i dati dell'utente
     */

    public function admin_box_users_delivery_list($delivery_id = 0, $user_id = 0) {

        $debug = false;
        $results = array();

        if (empty($delivery_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->isCassiere())
            $results = $this->__users_delivery($delivery_id, $debug);
        else
        if ($this->isReferentCassiere())
            $results = $this->__users_delivery_acl_referent($delivery_id, $debug);

        $users = array();
        if (!empty($results)) {
            $users += array('ALL' => 'Tutti gli utenti che hanno effettuato acquisti');
            foreach ($results as $key => $results2)
                $users[$results2['User']['id']] = $results2['User']['name'];
        }

        $this->set(compact('user_id'));
        $this->set('users', $users);
        $this->layout = 'ajax';
        $this->render('/AjaxGasCodes/admin_box_users');
    }

    /*
     * list users che hanno effettuato acquisti in una consegna
     */

    public function __users_delivery($delivery_id, $debug = false) {

        App::import('Model', 'User');
        $User = new User;

        $conditions = array();
        $conditions = array('Delivery.id' => $delivery_id);
        $users = $User->getUserWithCartByDelivery($this->user, $conditions, '', '', $debug);

        return $users;
    }

    /*
     * list users che hanno effettuato acquisti in una consegna
     *  filtrando per gli ordini di cui lo user e' referente $this->user->get('ACLsuppliersIdsOrganization')
     * 		(per ex group_id_referent_cassiere)
     */

    public function __users_delivery_acl_referent($delivery_id, $debug = false) {

        App::import('Model', 'User');
        $User = new User;

        $conditions = array();
        $conditions = array('Delivery.id' => $delivery_id);
        $users = $User->getUserWithCartByDeliveryACLReferent($this->user, $conditions, '', $debug);

        return $users;
    }

    /*
     * elenco ordini di cui lo user scelto ha effettuato acquisti
     * call Tesoriere::home
     * 
     * se order_id valorizzato e' richiamato da un suo ordine
     */

    public function admin_box_orders_users_cart($delivery_id = 0, $user_id = 0, $order_id = 0) {

        /*
         * se order_id NON e' valorizzato ho gia' richiamato la view in ajax con l'elenco di tutti gli ordini
         * e la sto richamando per ricaricare gli acquisti: non devo ridisegnare l'intestazione della tabella
         */
        if ($order_id == 0)
            $this->set('drawListAllOrders', 'Y');
        else
            $this->set('drawListAllOrders', 'N');


        $results = array();

        if (empty($delivery_id) || empty($user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Order');

        App::import('Model', 'ArticlesOrder');

        /*
         * estraggo tutto gli ordini di una consegna
         */
        $Order = new Order;
        $Order->unbindModel(array('belongsTo' => array('Delivery')));

        $options = array();
        $options['conditions'] = array('Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.delivery_id' => $delivery_id,
            'Order.isVisibleBackOffice' => 'Y');
        if (!empty($order_id) && $order_id > 0)
            $options['conditions'] += array('Order.id' => (int) $order_id);
        $options['order'] = 'Order.data_inizio ASC';
        $options['recursive'] = 0;

        $orderResults = $Order->find('all', $options);

        /*
         * per ogni ordine ctrl che l'utente abbia effettuato acquisti
         */
        $i = 0;
        foreach ($orderResults as $numResult => $orderResult) {

            $conditions = array();
            $conditions = array('Cart.user_id' => $user_id,
                'Cart.order_id' => $orderResult['Order']['id']);

            $ArticlesOrder = new ArticlesOrder;
            $articlesOrderResults = $ArticlesOrder->getArticoliDellUtenteInOrdine($this->user, $conditions);
            if (!empty($articlesOrderResults)) {

                $results[$i] = $orderResult;
                $results[$i]['ArticlesOrder'] = $articlesOrderResults;

                $i++;
            }
        } // end foreach ($orderResults as $orderResult)

        $this->set(compact('results'));

        $this->set('delivery_id', $delivery_id);
        $this->set('user_id', $user_id);

        $this->layout = 'ajax';
    }

    /*
     * list users 
     * $reportOptions = 'report-users-all', tutti
     * 					'report-users-cart', che hanno effettuato acquisti in un ordine
     */

    public function admin_box_users($order_id = 0, $reportOptions) {

        if (empty($order_id) || $reportOptions == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'User');
        $User = new User;

        if ($reportOptions == 'report-users-all') {
            $conditions = array();
            $conditions = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
                'User.block' => 0);
            $users = $User->find('list', array('conditions' => $conditions, 'fields' => array('id', 'name'),
                'order' => Configure::read('orderUser'), 'recursive' => -1));
        } else
        if ($reportOptions == 'report-users-cart') {
            $conditions = array();
            $conditions = array('ArticlesOrder.order_id' => $order_id);
            $results = $User->getUserWithCartByOrder($this->user, $conditions);

            $users = array();
            $users += array('ALL' => 'Tutti gli utenti che hanno effettuato acquisti');
            foreach ($results as $key => $results2)
                $users[$results2['User']['id']] = $results2['User']['name'];
        }

        $this->set(compact('users'));

        $this->layout = 'ajax';
    }

    /*
     * richamato da 
     * 		page Cart::admin_managementCartsOne()           => ajax admin_box_users()    => call=managementCartsOne
     * 		page Doc::admin_cassiere_delivery_docs_export() => ajax admin_box_delivery() => call=cassiereDEliveryDocsExport
     */

    public function admin_box_user_anagrafica($user_id, $call = 'managementCartsOne') {

        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if (!is_numeric($user_id)) // ALL
            $utente = array();
        else {
            App::import('Model', 'User');
            $User = new User;

            $conditions = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
                'User.id' => $user_id);
            $utente = $User->find('first', array('conditions' => $conditions));

            /*
             * userprofile
             */
            $utente_profile = JUserHelper::getProfile($user_id);
            $utente['Profile'] = $utente_profile->profile;

            /* echo "<pre>";
              print_r($utente);
              echo "</pre>"; */
        }

        $this->set(compact('utente'));

        /*
         * call
         * 		managementCartsOne 			nella View richiama choiceUserAnagrafica()
         * 	    cassiereDEliveryDocsExport  nella View richiama choiceUserCash()
         */
        $this->set('call', $call);

        $this->layout = 'ajax';
    }

    /*
     * gestisco la cassa dell'utente per tutti gli ordini della consegna 
     * 	con Order.state_code = PROCESSED-ON-DELIVERY validati dal referente
     */

    public function admin_box_user_cash($delivery_id, $user_id) {

        $debug = false;

        if (empty($delivery_id) || empty($user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'ArticlesOrder');

        App::import('Model', 'Supplier');

        App::import('Model', 'SummaryOrder');

        /*
         * estraggo tutto gli ordini di una consegna
         */
        App::import('Model', 'Order');
        $Order = new Order;
        $Order->unbindModel(array('belongsTo' => array('Delivery')));

        $options = array();
        $options['conditions'] = array('Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.delivery_id' => $delivery_id,
            'Order.isVisibleBackOffice' => 'Y');
        $options['order'] = 'Order.data_inizio ASC';
        $options['recursive'] = 0;

        $orderResults = $Order->find('all', $options);

        if ($debug) {
            echo "<h2>Ordini della consegna $delivery_id: " . count($orderResults) . " totali</h2>";
        }

        /*
         * per ogni ordine ctrl che l'utente abbia effettuato acquisti
         */
        $results = array();
        $i = 0;
        foreach ($orderResults as $numResult => $orderResult) {

            if ($debug) {
                echo "<h2>Tratto ordine " . $orderResult['Order']['id'] . " con stato " . $orderResult['Order']['state_code'] . "</h2>";
            }

            $conditions = array();
            $conditions = array('Cart.user_id' => $user_id,
                'Cart.order_id' => $orderResult['Order']['id']);

            $ArticlesOrder = new ArticlesOrder;
            $articlesOrderResults = $ArticlesOrder->getArticoliDellUtenteInOrdine($this->user, $conditions);
            if (!empty($articlesOrderResults)) {

                $results[$i] = $orderResult;

                /*
                 * Suppliers per img
                 */
                $Supplier = new Supplier;

                $options = array();
                $options['conditions'] = array('Supplier.id' => $orderResult['SuppliersOrganization']['supplier_id']);
                $options['recursive'] = -1;
                $options['fields'] = array('Supplier.img1');
                $SupplierResults = $Supplier->find('first', $options);
                $results[$i]['Supplier'] = $SupplierResults['Supplier'];

                /*
                 * se ha effettuato acquisti verifica che possa pagare Order.state_code == 'PROCESSED-ON-DELIVERY'
                 */
                $SummaryOrder = new SummaryOrder;
                $SummaryOrder->unbindModel(array('belongsTo' => array('User', 'Delivery')));

                $options = array();
                $options['conditions'] = array('SummaryOrder.organization_id' => $this->user->organization['Organization']['id'],
                    'SummaryOrder.user_id' => $user_id,
                    'SummaryOrder.delivery_id' => $delivery_id,
                    'SummaryOrder.order_id' => $orderResult['Order']['id'],
                    'Order.state_code' => 'PROCESSED-ON-DELIVERY',
                    'Order.isVisibleBackOffice' => 'Y');
                $options['recursive'] = 1;
                $SummaryOrderResults = $SummaryOrder->find('first', $options);

                if ($debug) {
                    echo "<pre>";
                    print_r($options);
                    echo "</pre>";
                    echo '<h3>SummaryOrder ' . count($SummaryOrderResults) . '</h3>';
                }

                /*
                 * Anomalie!! dovrebbe gia' arrivare valorizzato
                 */
                if (empty($SummaryOrderResults) && $orderResult['Order']['state_code'] == 'PROCESSED-ON-DELIVERY') {

                    if ($debug)
                        echo '<h1>SummaryOrder ' . count($SummaryOrderResults) . ' anomalia!</h1>';

                    $SummaryOrder->populate_to_order($this->user, $orderResult['Order']['id'], $user_id, $debug);

                    /*
                     * rifaccio select
                     */
                    $options = array();
                    $options['conditions'] = array('SummaryOrder.organization_id' => $this->user->organization['Organization']['id'],
                        'SummaryOrder.user_id' => $user_id,
                        'SummaryOrder.delivery_id' => $delivery_id,
                        'SummaryOrder.order_id' => $orderResult['Order']['id'],
                        'Order.state_code' => 'PROCESSED-ON-DELIVERY',
                        'Order.isVisibleBackOffice' => 'Y');
                    $options['recursive'] = 1;
                    $SummaryOrderResults = $SummaryOrder->find('first', $options);

                    if ($debug) {
                        echo "<pre>";
                        print_r($options);
                        echo "</pre>";
                        echo '<h3>SummaryOrder ' . count($SummaryOrderResults) . '</h3>';
                    }
                }

                if (!empty($SummaryOrderResults)) {
                    $results[$i]['SummaryOrder'] = $SummaryOrderResults['SummaryOrder'];
                }

                $i++;
            } else {
                if ($debug) {
                    echo "Non ci sono acquisti per lo user $user_id";
                }
            }
        } // end foreach ($orderResults as $orderResult)

        /*
         *  eventuale importo POS
         */
        if ($this->user->organization['Organization']['hasFieldPaymentPos'] == 'Y') {

            App::import('Model', 'SummaryDeliveriesPos');
            $SummaryDeliveriesPos = new SummaryDeliveriesPos;

            $summaryDeliveriesPosResults = $SummaryDeliveriesPos->findPaymentPos($this->user, $delivery_id, $user_id, $debug);
            if (!empty($summaryDeliveriesPosResults))
                $this->set('summaryDeliveriesPosResults', $summaryDeliveriesPosResults);
        }

        if ($debug) {
            echo "<pre>------------------------";
            print_r($results);
            echo "</pre>";
        }
        $this->set('results', $results);

        /*
         * dati cassa per l'utente
         */
        App::import('Model', 'Cash');
        $Cash = new Cash;

        $options = array();
        $options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
            'Cash.user_id' => $user_id);
        $options['recursive'] = -1;
        $cashResults = $Cash->find('first', $options);
        if ($debug) {
            echo "<pre>Dati Cassa per lo user $user_id";
            print_r($cashResults);
            echo "</pre>";
        }
        $this->set('cashResults', $cashResults);

        $modalita = ClassRegistry::init('SummaryOrder')->enumOptions('modalita');
        if ($this->user->organization['Organization']['hasFieldPaymentPos'] == 'N')
            unset($modalita['BANCOMAT']);
        unset($modalita['DEFINED']);
        $this->set(compact('modalita'));

        $this->layout = 'ajax';
    }

    /*
     * $report_options
     * 				report-users-cart 					(Solo utenti con acquisti)
     * 				report-users-all 					(Tutti gli utenti)
     * 				report-articles-details				(Articoli aggregati con il dettaglio degli utenti)
     */

    public function admin_box_report_options() {

        // di default l'opzione (Solo utenti con acquisti)
        $this->set('report_options', 'report-users-cart');

        $this->layout = 'ajax';
    }

    /*
     * $articles-options
     * 				options-articles-cart 		(Solo articoli acquistati)
     * 				options-users-all  			(Tutti gli articoli)
     * 
     * se user_id == ALL disabilito l'opzione (Tutti gli articoli)
     */

    public function admin_box_articles_options($user_id) {

        // di default l'opzione (Solo articoli acquistati)
        $this->set('articles_options', 'options-articles-cart');
        if($user_id=='ALL')
	        $this->set('articles_sort', 'cart_date');
        else
        	$this->set('articles_sort', 'articles_users');
        $this->set('user_id', $user_id);

        $this->layout = 'ajax';
    }

    /*
     * $articles-options
     * 				options-articles-cart 		(Solo articoli acquistati)
     * 				options-users-all  			(Tutti gli articoli)
     * 
     * se user_id == ALL disabilito l'opzione (Tutti gli articoli)
     */

    public function admin_box_articles_options_pay_to_delivery($delivery_id, $order_id, $user_id) {

        // di default l'opzione (Solo articoli acquistati)
        $this->set('articles_options', 'options-articles-cart');

        $this->set('delivery_id', $delivery_id);
        $this->set('order_id', $order_id);
        $this->set('user_id', $user_id);

        $this->layout = 'ajax';
    }

    /*
     * solo se $Order->getOrderPermissionToEditReferente() posso procedere
     */

    public function admin_box_summary_orders_options() {

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
             * ctrl eventuali occorrenze di SummaryOrder
             */
            App::import('Model', 'SummaryOrder');
            $SummaryOrder = new SummaryOrder;
            $resultsSummaryOrder = $SummaryOrder->select_to_order($this->user, $this->order_id);
            $this->set(compact('resultsSummaryOrder', $resultsSummaryOrder));
        }

        $this->set(compact('results', $results));

        $this->layout = 'ajax';
    }

    public function admin_box_summary_des_orders_options($des_order_id) {

        /*
         * ctrl ACL
         */
        $isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);
        if (!$isTitolareDesSupplier) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder;

        $options = array();
        $options['conditions'] = array('DesOrder.des_id' => $this->user->des_id,
            'DesOrder.id' => $des_order_id
        );
        $options['fields'] = array('DesOrder.state_code');
        $options['recursive'] = -1;
        $results = $DesOrder->find('first', $options);
        $this->set(compact('results'));

        /*
         * ctrl eventuali occorrenze di SummaryDesOrder
         */
        App::import('Model', 'SummaryDesOrder');
        $SummaryDesOrder = new SummaryDesOrder;
        $resultsSummaryDesOrder = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id);
        $this->set(compact('resultsSummaryDesOrder'));

        $this->layout = 'ajax';
    }

    /*
     * solo se $results['Order']['state_code']=='PROCESSED-POST-DELIVERY'  || $results['Order']['state_code']=='INCOMING-ORDER' || $results['Order']['state_code']=='PROCESSED-ON-DELIVERY' posso procedere
     */

    public function admin_box_carts_splits_options() {

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
        if ($results['Order']['state_code'] == 'PROCESSED-POST-DELIVERY' || $results['Order']['state_code'] == 'INCOMING-ORDER' || $results['Order']['state_code'] == 'PROCESSED-ON-DELIVERY') {
            /*
             * ctrl eventuali occorrenze di CartsSplit
             */
            App::import('Model', 'CartsSplit');
            $CartsSplit = new CartsSplit;
            $resultsCartsSplit = $CartsSplit->select_to_order($this->user, $this->order_id);
            $this->set('resultsCartsSplit', $resultsCartsSplit);
        }

        $this->set(compact('results', $results));

        $this->layout = 'ajax';
    }

    /*
     * richiamata da
     * Carts::managementCartsSplits dove gli passo un solo order_id e $cartsSplitsOptions='options-delete-...'
     */

    public function admin_box_carts_splits($delivery_id, $order_id, $cartsSplitsOptions = 'options-delete-no') {
        $this->__box_carts_splits_read_only($delivery_id, $order_id, $cartsSplitsOptions);
        $this->layout = 'ajax';
    }

    public function admin_box_carts_splits_read_only($delivery_id, $order_id, $cartsSplitsOptions = 'options-delete-no') {
        $this->__box_carts_splits_read_only($delivery_id, $order_id, $cartsSplitsOptions);
        $this->layout = 'ajax';
    }

    public function __box_carts_splits_read_only($delivery_id, $order_id, $cartsSplitsOptions) {
        if (empty($this->delivery_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'CartsSplit');
        $CartsSplit = new CartsSplit;

        /*
         * cancello occorrenze di CartsSplit, se il referente vuole rigenerarle
         */
        if ($cartsSplitsOptions == 'options-delete-yes') {
            $CartsSplit->delete_to_order($this->user, $order_id);

            $CartsSplit->populate_to_order($this->user, $order_id);

            $this->set('carts_splits_regenerated', true);
        } else {
            /*
             * ctrl eventuali occorrenze di CartsSplit, se non ci sono lo popolo
             */
            $results = $CartsSplit->select_to_order($this->user, $order_id);
            if (empty($results))
                $CartsSplit->populate_to_order($this->user, $order_id);
        }

        $CartsSplit->unbindModel(array('belongsTo' => array('Order')));
        $options = array();
        $options['conditions'] = array('CartsSplit.organization_id' => $this->user->organization['Organization']['id'],
            'CartsSplit.order_id' => $order_id);
        $options['recursive'] = 1;
        $options['order'] = array(Configure::read('orderUser') . ',CartsSplit.user_id, CartsSplit.article_organization_id, CartsSplit.article_id, CartsSplit.num_split');
        $results = $CartsSplit->find('all', $options);

        $this->set('results', $results);
    }

    /*
     * solo se $results['Order']['state_code']=='PROCESSED-POST-DELIVERY' o 'INCOMING-ORDER' posso procedere
     * 
     * visualizzo il campo importo del trasporto con i tasti Salva importo, Aggiorna importo, Elimina importo gestiti in CartController::admin_trasport()
     */

    public function admin_box_trasport_importo() {

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
        $this->set(compact('results', $results));

        $this->layout = 'ajax';
    }

    /*
     * solo se $results['Order']['state_code']=='PROCESSED-POST-DELIVERY' o 'INCOMING-ORDER' posso procedere
     *
     * visualizzo il campo importo del costo aggiuntivo con i tasti Salva importo, Aggiorna importo, Elimina importo gestiti in CartController::admin_cost_more()
     */

    public function admin_box_cost_more_importo() {

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
        $this->set(compact('results', $results));

        $this->layout = 'ajax';
    }

    /*
     * solo se $results['Order']['state_code']=='PROCESSED-POST-DELIVERY' o 'INCOMING-ORDER' posso procedere
     *
     * visualizzo il campo importo del costo aggiuntivo con i tasti Salva importo, Aggiorna importo, Elimina importo gestiti in CartController::admin_cost_less()
     */

    public function admin_box_cost_less_importo() {

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
        $this->set(compact('results', $results));

        $this->layout = 'ajax';
    }

    /*
     * solo se $results['Order']['state_code']=='PROCESSED-POST-DELIVERY' o 'INCOMING-ORDER' posso procedere
     * 
     * visualizzo i 3 algoritmi di calcolo 'QTA','WEIGHT','USERS'
     */

    public function admin_box_trasport_options() {

        $debug = false;
        if ($debug)
            echo '<h2>admin_box_trasport_options()</h2>';

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
        $this->set(compact('results', $results));

        /*
         * creo le occorrenze in SummaryOrderTrasport, ma gia' popolato quando ho salvato l'importo del trasporto
         */
        App::import('Model', 'SummaryOrderTrasport');
        $SummaryOrderTrasport = new SummaryOrderTrasport;
        $results = $SummaryOrderTrasport->select_to_order($this->user, $this->order_id, 0, $debug);
        if (empty($results)) {

            if ($debug)
                echo '<h2>da SummaryOrderTrasport->select_to_order() nessun risultato => eseguo SummaryOrderTrasport->populate_to_order</h2>';

            $SummaryOrderTrasport->populate_to_order($this->user, $this->order_id, $debug);
        }
        else {
            if ($debug)
                echo '<h2>da SummaryOrderTrasport->select_to_order() VALORIZZATO => NON eseguo SummaryOrderTrasport->populate_to_order</h2>';
        }

        /*
         * ottengo il TOTALE del peso dell'ordine
         * se e' 0 (perche' un UM e' diverso da GR, HG, KG) l'opzione del trasporto a peseo "options-weight" non la faccio vedere
         */
        $sql = "SELECT
					sum(peso) as totaleOrdine
				FROM
					" . Configure::read('DB.prefix') . "summary_order_trasports as SummaryOrderTrasport
				WHERE
					SummaryOrderTrasport.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
					and SummaryOrderTrasport.order_id = " . (int) $this->order_id;
        if ($debug)
            echo '<br />' . $sql;
        $result = $Order->query($sql);
        $totaleOrdine = 0;
        if (!empty($result)) {
            $result = current($result);
            $totaleOrdine = $result[0]['totaleOrdine'];
        }
        if ($debug)
            echo '<br />totaleOrdine ' . $totaleOrdine;

        if ($totaleOrdine == 0)
            $this->set('options_weight', 'N');
        else
            $this->set('options_weight', 'Y');

        $this->layout = 'ajax';
    }

    /*
     * solo se $results['Order']['state_code']=='PROCESSED-POST-DELIVERY' o 'INCOMING-ORDER' posso procedere
     *
     * visualizzo i 3 algoritmi di calcolo 'QTA','WEIGHT','USERS'
     */

    public function admin_box_cost_more_options() {

        $debug = false;
        if ($debug)
            echo '<h2>admin_box_cost_more_options()</h2>';

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
        $this->set(compact('results', $results));

        /*
         * creo le occorrenze in SummaryOrderCostMore, ma gia' popolato quando ho salvato l'importo del costo aggiuntivo
         */
        App::import('Model', 'SummaryOrderCostMore');
        $SummaryOrderCostMore = new SummaryOrderCostMore;
        $results = $SummaryOrderCostMore->select_to_order($this->user, $this->order_id, 0, $debug);
        if (empty($results)) {

            if ($debug)
                echo '<h2>da SummaryOrderCostMore->select_to_order() nessun risultato => eseguo SummaryOrderCostMore->populate_to_order</h2>';

            $SummaryOrderCostMore->populate_to_order($this->user, $this->order_id, $debug);
        }
        else {
            if ($debug)
                echo '<h2>da SummaryOrderCostMore->select_to_order() VALORIZZATO => NON eseguo SummaryOrderCostMore->populate_to_order</h2>';
        }

        /*
         * ottengo il TOTALE del peso dell'ordine
         * se e' 0 (perche' un UM e' diverso da GR, HG, KG) l'opzione del cost_moreo a peseo "options-weight" non la faccio vedere
         */
        $sql = "SELECT
					sum(peso) as totaleOrdine
				FROM
					" . Configure::read('DB.prefix') . "summary_order_cost_mores as SummaryOrderCostMore
				WHERE
					SummaryOrderCostMore.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
					and SummaryOrderCostMore.order_id = " . (int) $this->order_id;
        if ($debug)
            echo '<br />' . $sql;
        $result = $Order->query($sql);
        $totaleOrdine = 0;
        if (!empty($result)) {
            $result = current($result);
            $totaleOrdine = $result[0]['totaleOrdine'];
        }
        if ($debug)
            echo '<br />totaleOrdine ' . $totaleOrdine;

        if ($totaleOrdine == 0)
            $this->set('options_weight', 'N');
        else
            $this->set('options_weight', 'Y');

        $this->layout = 'ajax';
    }

    /*
     * solo se $results['Order']['state_code']=='PROCESSED-POST-DELIVERY' o 'INCOMING-ORDER' posso procedere
     *
     * visualizzo i 3 algoritmi di calcolo 'QTA','WEIGHT','USERS'
     */

    public function admin_box_cost_less_options() {

        $debug = false;
        if ($debug)
            echo '<h2>admin_box_cost_less_options()</h2>';

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
        $this->set(compact('results', $results));

        /*
         * creo le occorrenze in SummaryOrderCostLess, ma gia' popolato quando ho salvato l'importo dello sconto
         */
        App::import('Model', 'SummaryOrderCostLess');
        $SummaryOrderCostLess = new SummaryOrderCostLess;
        $results = $SummaryOrderCostLess->select_to_order($this->user, $this->order_id, 0, $debug);
        if (empty($results)) {

            if ($debug)
                echo '<h2>da SummaryOrderCostLess->select_to_order() nessun risultato => eseguo SummaryOrderCostLess->populate_to_order</h2>';

            $SummaryOrderCostLess->populate_to_order($this->user, $this->order_id, $debug);
        }
        else {
            if ($debug)
                echo '<h2>da SummaryOrderCostLess->select_to_order() VALORIZZATO => NON eseguo SummaryOrderCostLess->populate_to_order</h2>';
        }

        /*
         * ottengo il TOTALE del peso dell'ordine
         * se e' 0 (perche' un UM e' diverso da GR, HG, KG) l'opzione del cost_moreo a peseo "options-weight" non la faccio vedere
         */
        $sql = "SELECT
					sum(peso) as totaleOrdine
				FROM
					" . Configure::read('DB.prefix') . "summary_order_cost_lesses as SummaryOrderCostLess
				WHERE
					SummaryOrderCostLess.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
					and SummaryOrderCostLess.order_id = " . (int) $this->order_id;
        if ($debug)
            echo '<br />' . $sql;
        $result = $Order->query($sql);
        $totaleOrdine = 0;
        if (!empty($result)) {
            $result = current($result);
            $totaleOrdine = $result[0]['totaleOrdine'];
        }
        if ($debug)
            echo '<br />totaleOrdine ' . $totaleOrdine;

        if ($totaleOrdine == 0)
            $this->set('options_weight', 'N');
        else
            $this->set('options_weight', 'Y');

        $this->layout = 'ajax';
    }

    public function admin_box_doc_options_referente() {
        
        /*
         * ctrl se ci sono ordini da validate per avvisare l'utente
         */
        $results = array();

        App::import('Model', 'Order');
        $Order = new Order;
        $options = array();
        $options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
                                       'Order.id' => $this->order_id);
        $options['recursive'] = -1;
        $results = $Order->find('first', $options);
        
        $this->__box_doc_options_referente($results);
        
        $this->layout = 'ajax';
    }
    
    public function admin_box_doc_options_referente_all_gas($des_order_id, $organization_id, $delivery_id, $order_id) {

        if (empty($des_order_id) || empty($organization_id) || empty($delivery_id) || empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        
        /*
         * ctrl se ci sono ordini da validate per avvisare l'utente
         */
        $results = array();

        App::import('Model', 'Order');
        $Order = new Order;
        
        $options = array();
        $options['conditions'] = array('Order.organization_id' => $organization_id,
                                       'Order.delivery_id' => $delivery_id,
                                       'Order.id' => $order_id,);
        $options['recursive'] = -1;
        $results = $Order->find('first', $options);
        
        $this->__box_doc_options_referente($results);
        
        $this->set('des_order_id', $des_order_id);
        $this->set('organization_id', $organization_id);
        
        $this->layout = 'ajax';
    }
	
    public function admin_box_doc_options_prod_gas_supplier($organization_id, $delivery_id, $order_id) {

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

        $this->set('can_view_orders', $organizationResults['SuppliersOrganization']['can_view_orders']);
        $this->set('can_view_orders_users', $organizationResults['SuppliersOrganization']['can_view_orders_users']);
		
        /*
         * ctrl se ci sono ordini da validate per avvisare l'utente
         */
        $results = array();

        App::import('Model', 'Order');
        $Order = new Order;
        
        $options = array();
        $options['conditions'] = array('Order.organization_id' => $organization_id,
                                       'Order.delivery_id' => $delivery_id,
                                       'Order.id' => $order_id,);
        $options['recursive'] = -1;
        $results = $Order->find('first', $options);
        
        $this->__box_doc_options_referente($results);
        
        $this->set('organization_id', $organization_id);
 		$this->set('delivery_id', $delivery_id);
        $this->set('order_id', $order_id);
		
        $this->layout = 'ajax';
    }
    
    private function __box_doc_options_referente($orderResults) {

        $debug = false;

        App::import('Model', 'Order');
        $Order = new Order;
        
        $tmp_user->organization['Organization']['id'] = $orderResults['Order']['organization_id'];
        
        /*
         * opzione: Visualizzo le spese aggiuntive o gli sconti
         */
        $this->set('hasTrasport', $orderResults['Order']['hasTrasport']);
        $this->set('trasport', $orderResults['Order']['trasport']);

        $this->set('hasCostMore', $orderResults['Order']['hasCostMore']);
        $this->set('cost_more', $orderResults['Order']['cost_more']);

        $this->set('hasCostLess', $orderResults['Order']['hasCostLess']);
        $this->set('cost_less', $orderResults['Order']['cost_less']);

        if ($debug) {
            echo '<br />Order.hasTrasport ' . $orderResults['Order']['hasTrasport'];
            echo '<br />Order.trasport ' . $orderResults['Order']['trasport'];
            echo '<br />Order.hasCostMore ' . $orderResults['Order']['hasCostMore'];
            echo '<br />Order.cost_more ' . $orderResults['Order']['cost_more'];
            echo '<br />Order.hasCostLess ' . $orderResults['Order']['hasCostLess'];
            echo '<br />Order.cost_less ' . $orderResults['Order']['cost_less'];
        }

        /*
         * ctrl se l'ordine dev'essere validato (ArticlesOrder.pezzi_confezione > 1) per la gestione dei colli
         * (come in PageController::index per ExportDocs/referent_to_articles_monitoring.ctp)
         */ 
       	if ($Order->isOrderToValidate($tmp_user, $orderResults['Order']['id']))
            $isToValidate = true;
        else
            $isToValidate = false;

		$results = array();
        if ($isToValidate) {
            if ($debug)
                echo '<br />Order toValidate (ArticlesOrder.pezzi_confezione > 1) = Y ';

            App::import('Model', 'Cart');
            $Cart = new Cart;
            $results = $Cart->getCartToValidate($tmp_user, $orderResults['Order']['delivery_id'], $orderResults['Order']['id']);
        }
        else
        if ($debug)
            echo '<br />Order toValidate (ArticlesOrder.pezzi_confezione > 1) = N ';
        $this->set('results', $results);

        /*
         * ctrl se visualizzare il report ExportDocs/referent_to_articles_monitoring.ctp
         * per i colli e la qta_massima_order
         */
        $options = array();
        $options['conditions'] = array('Order.organization_id' => $orderResults['Order']['organization_id'],
                                        'Order.id' => $orderResults['Order']['id'],
                                        '(Order.state_code = \'OPEN\' OR Order.state_code = \'RI-OPEN-VALIDATE\' OR Order.state_code = \'PROCESSED-BEFORE-DELIVERY\')');
        $options['recursive'] = -1;
        $order = $Order->find('first', $options);
		/*
		echo "<pre>";
		print_r($options);
		print_r($order);
		echo "</pre>";
        */
        if (!empty($order)) {
            /*
             * ctrl se l'ordine ha settato delle quantita' massime > 0
             * (come in PageController::index per ExportDocs/referent_to_articles_monitoring.ctp)
             */
            if ($Order->isOrderToQtaMassima($tmp_user, $orderResults['Order']['id'])) {
                if ($debug)
                    echo '<br />Order ToQtaMassima (ArticlesOrder.qta_massima_order > 1) = Y ';
                $toQtaMassima = true;
            }
            else {
                if ($debug)
                    echo '<br />Order ToQtaMassima (ArticlesOrder.qta_massima_order > 1) = N ';
                $toQtaMassima = false;
            }

            /*
             * ctrl se l'ordine ha settato delle quantita' minime sugli acquisti di tutto l'ordine > 0
             * (come in PageController::index per ExportDocs/referent_to_articles_monitoring.ctp)
             */
            if ($Order->isOrderToQtaMinimaOrder($tmp_user, $orderResults['Order']['id'])) {
                if ($debug)
                    echo '<br />Order isOrderToQtaMinimaOrder (ArticlesOrder.qta_minima_order > 1) = Y ';
                $toQtaMinimaOrder = true;
            }
            else {
                if ($debug)
                    echo '<br />Order ToQtaMinimaOrder (ArticlesOrder.qta_minima_order > 1) = N ';
                $toQtaMinimaOrder = false;
            }
        } 
        else
            $isToValidate = false;

        $this->set('toQtaMassima', $toQtaMassima);
        $this->set('toQtaMinimaOrder', $toQtaMinimaOrder);
        $this->set('isToValidate', $isToValidate);
    }

    /*
     *  $user_id = ALL o user_id 
     */

    public function admin_box_doc_options_cassiere($user_id = 0) {

        $this->set('user_id', $user_id);

        $debug = false;
        $this->layout = 'ajax';
    }

    public function admin_box_doc_print_referente($doc_options = null) {

        if ($doc_options == 'to-users-all-modify')
            $options = array('PDF' => 'Pdf', 'CSV' => 'Csv');
        else
        if ($doc_options == 'to-users-label' || $doc_options == 'to-articles-weight')
            $options = array('PDF' => 'Pdf', 'EXCEL' => 'Excel');
        else
            $options = array('PDF' => 'Pdf', 'CSV' => 'Csv', 'EXCEL' => 'Excel');

        $this->set('options', $options);

        $this->layout = 'ajax';
    }
    
    public function admin_box_doc_print_referente_all_gas($des_order_id, $organization_id, $delivery_id, $order_id, $doc_options = null) {

        if (empty($des_order_id) || empty($organization_id) || empty($delivery_id) || empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        
        if ($doc_options == 'to-users-all-modify')
            $options = array('PDF' => 'Pdf', 'CSV' => 'Csv');
        else
        if ($doc_options == 'to-users-label' || $doc_options == 'to-articles-weight')
            $options = array('PDF' => 'Pdf', 'EXCEL' => 'Excel');
        else
            $options = array('PDF' => 'Pdf', 'CSV' => 'Csv', 'EXCEL' => 'Excel');

        $this->set('options', $options);

        $this->set('des_order_id', $des_order_id);
        $this->set('organization_id', $organization_id);
        
        $this->layout = 'ajax';
    }

    public function admin_box_doc_print_prod_gas_supplier($organization_id, $delivery_id, $order_id, $doc_options = null) {

        if (empty($organization_id) || empty($delivery_id) || empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        
        if ($doc_options == 'to-users-all-modify')
            $options = array('PDF' => 'Pdf', 'CSV' => 'Csv');
        else
        if ($doc_options == 'to-users-label' || $doc_options == 'to-articles-weight')
            $options = array('PDF' => 'Pdf', 'EXCEL' => 'Excel');
        else
            $options = array('PDF' => 'Pdf', 'CSV' => 'Csv', 'EXCEL' => 'Excel');

        $this->set('options', $options);

        $this->set('organization_id', $organization_id);
        
        $this->layout = 'ajax';
    }

    public function admin_box_doc_print_cassiere($doc_options = null) {

        $options = array('PDF' => 'Pdf', 'EXCEL' => 'Excel');
        $this->set('options', $options);

        $this->layout = 'ajax';
    }

    /*
     * $articlesOptions
     * 		'options-users-cart' Solo articoli acquisti
     * 		'options-users-all'  Tutti gli articoli
     */

    public function admin_box_management_carts_users($delivery_id, $order_id, $user_id, $articlesOptions, $order_by) {

        if (empty($this->delivery_id) || empty($this->order_id) || $user_id == null || $articlesOptions == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $this->delivery_id),
            'Order' => array('Order.isVisibleBackOffice' => 'Y',
                'Order.id' => (int) $this->order_id),
            'Cart' => array('Cart.user_id' => (int) $user_id,
                'Cart.order_id' => (int) $this->order_id));

        if ($order_by == 'articles_asc')
            $orderBy = array('Article' => 'Article.name asc');
        else
        if ($order_by == 'articles_desc')
            $orderBy = array('Article' => 'Article.name desc');
		else
		if ($order_by == 'articles_users')
            $orderBy = array('Article' => 'Article.name asc, Article.id');
		
        $options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
            'suppliers' => true, 'referents' => false);

        if ($articlesOptions == 'options-users-cart')    // estraggo solo gli articoli acquistati
            $options += array('articoliDellUtenteInOrdine' => true);
        else
        if ($articlesOptions == 'options-users-all')    // estraggo tutti gli articoli con EVENTUALI acquisti
            $options += array('articoliEventualiAcquistiNoFilterInOrdine' => true);

        $results = $Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);

        $this->set(compact('results', 'user_id'));
        /*
         * permission per abilitazione modifica del carrello
         */
        $permissions = array('isReferentGeneric' => $this->isReferentGeneric(),
            'isTesoriereGeneric' => $this->isTesoriereGeneric());
        $this->set('permissions', $permissions);

        $this->layout = 'ajax';
    }

    /*
     * $articlesOptions
     * 		'options-users-cart' Solo articoli acquisti
     * 		'options-users-all'  Tutti gli articoli
     *
     *  $order_by 
     *		da options radio (Articoli e gasista / Gasista e articoli / Acquistato il)
     *			articles_users
     *			users_articles
     *			cart_date 
     *		da header colonna (Articolo / Utente)
     *  		users_asc     (tutti gli utenti con acquisti)
     *  		articles_asc  (Articoli aggregati con il dettaglio degli utenti)
     */

    public function admin_box_management_carts_articles_details($delivery_id, $order_id, $order_by) {
        
        if (empty($this->delivery_id) || empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $this->delivery_id),
            	'Order' => array('Order.isVisibleBackOffice' => 'Y',
                				 'Order.id' => (int) $this->order_id));
		
		/*
		 * S O R T
		 */
		switch ($order_by) {
		    case "articles_asc":
		        $orderBy = array('Article' => 'Article.name asc, Article.id, Cart.created');
		        break;
		    case "articles_desc":
		        $orderBy = array('Article' => 'Article.name desc, Article.id, Cart.created');
		        break;
		    case "users_asc":
		        $orderBy = array('User' => Configure::read('orderUser') . ' asc, Cart.created');
		        break;
		    case "users_desc":
		        $orderBy = array('User' => Configure::read('orderUser') . ' desc, Cart.created');
		        break;
		    case "articles_users":
		        $orderBy = array('ArticleUser' => 'Article.name asc, Article.id,'. Configure::read('orderUser') . ' asc');
		        break;
		    case "users_articles":
		        $orderBy = array('UserArticle' => Configure::read('orderUser') . ' asc, Article.name asc, Article.id');
		        break;
		    default: // "cart_date"
		        $orderBy = array('CartDate' => 'Cart.created, Article.name asc, Article.id');
		        break;
		}
   

        $options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
            'articlesOrdersInOrderAndCartsAllUsers' => true, // estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
            'suppliers' => true, 'referents' => true);

        $results = $Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);

        $this->set(compact('results', 'user_id'));
        /*
         * permission per abilitazione modifica del carrello
         */
        $permissions = array('isReferentGeneric' => $this->isReferentGeneric(),
            'isTesoriereGeneric' => $this->isTesoriereGeneric());
        $this->set('permissions', $permissions);

        $this->layout = 'ajax';
    }

    public function admin_box_validation_carts() {

        if (empty($this->delivery_id) || empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        /*
         * ctrl se storeroom esiste
         * */
        $isStoreroom = false;
        if ($this->user->organization['Organization']['hasStoreroom'] == 'Y') {

            App::import('Model', 'Storeroom');
            $Storeroom = new Storeroom;
            $storeroomUser = $Storeroom->getStoreroomUser($this->user);
            if (empty($storeroomUser))
                $isStoreroom = false;
            else {
                $isStoreroom = true;
                $this->set('storeroomUser', $storeroomUser);
            }
        }
        $this->set('isStoreroom', $isStoreroom);

        /*
         * dati ordine
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $options = array();
        $options['conditions'] = array('Order.organization_id' => (int) $this->user->organization['Organization']['id'],
										'Order.isVisibleBackOffice' => 'Y',
										'Order.id' => $this->order_id);
        $options['recursive'] = -1;
        $order = $Order->find('first', $options);
        $this->set('order', $order);

        /*
         * estraggo gli acquisti da validate (ArticlesOrder.pezzi_confezione > 1)
         */
        App::import('Model', 'Cart');
        $Cart = new Cart;
        $results = $Cart->getCartToValidate($this->user, $this->delivery_id, $this->order_id);
		/*
		echo "<pre>AjaxGasCode::admin_box_validation_carts() \r";
		print_r($results);
		echo "<pre>";
		*/
        $this->set('results', $results);

		$this->disableCache();
		
        $this->layout = 'ajax';
    }

    /*
     * richiamata da
     * Tesoriere::admin_orders_in_processing_summary_orders dove gli posso passare + order_id
     * Carts::managementCartsGroupByUsers dove gli passo un solo order_id e $summaryOrdersOptions='options-delete-...'
     */

    public function admin_box_summary_orders($delivery_id, $order_id_selected, $summaryOrdersOptions = 'options-delete-no') {

        if (empty($this->delivery_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * Tesoriere::admin_orders_in_processing_summary_orders posso avere + order_id
         */
        $array_order_id = explode(',', $order_id_selected);

        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;

        /*
         * cancello occorrenze di SummaryOrder, se il referente vuole rigenerarle
         */
        if ($summaryOrdersOptions == 'options-delete-yes') {
            $SummaryOrder->delete_to_order($this->user, $array_order_id[0]);

            $SummaryOrder->populate_to_order($this->user, $array_order_id[0], 0);

            $this->set('summary_orders_regenerated', true);
        } else {
            /*
             * ctrl eventuali occorrenze di SummaryOrder, se non ci sono lo popolo
             */
            foreach ($array_order_id as $order_id) {
                $results = $SummaryOrder->select_to_order($this->user, $order_id);
                if (empty($results))
                    $SummaryOrder->populate_to_order($this->user, $order_id, 0);
            }
        }

        /*
         *  stesso codice di SummaryOrders::__populate_to_view
         *  
         * ricarico il div con l'elenco dei summaryOrders
         */
        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $this->delivery_id),
            'Order' => array('Order.isVisibleBackOffice' => 'Y',
                'Order.id IN (' . $order_id_selected . ')'),
            'Cart' => array('Cart.deleteToReferent' => 'N'));

        $orderBy = array('User' => 'User.name');

        $options = array('orders' => false, 'storerooms' => false, 'summaryOrders' => true,
            'articlesOrdersInOrder' => true,
            'suppliers' => true, 'referents' => true);

        $results = $Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);
        $this->set('results', $results);

        App::import('Model', 'User');
        $User = new User;

        $conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
        $users = $User->getUsersList($this->user, $conditions);
        $this->set('users', $users);

        $this->layout = 'ajax';
    }

    public function admin_box_summary_orders_read_only() {

        if (empty($this->delivery_id) || empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;

        /*
         * ctrl eventuali occorrenze di SummaryOrder, se non ci sono lo popolo
         */
        $results = $SummaryOrder->select_to_order($this->user, $this->order_id);
        if (empty($results))
            $SummaryOrder->populate_to_order($this->user, $this->order_id, 0);



        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $this->delivery_id),
            'Order' => array('Order.isVisibleBackOffice' => 'Y',
                'Order.id' => $this->order_id),
            'Cart' => array('Cart.deleteToReferent' => 'N'));
        $orderBy = array('Article' => 'Article.name');

        $options = array('orders' => false, 'storerooms' => false, 'summaryOrders' => true,
            'articlesOrdersInOrder' => true,
            'suppliers' => true, 'referents' => true);

        $results = $Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);

        $this->set('results', $results);
        $this->layout = 'ajax';
    }

    public function admin_box_summary_des_orders($des_order_id, $summaryDesOrdersOptions = 'options-delete-no') {

        $debug = false;

        App::import('Model', 'SummaryDesOrder');
        $SummaryDesOrder = new SummaryDesOrder;

        /*
         * cancello occorrenze di SummaryOrder, se il referente vuole rigenerarle
         */
        if ($summaryDesOrdersOptions == 'options-delete-yes') {
            $SummaryDesOrder->delete_to_des_order($this->user, $des_order_id);

            $SummaryDesOrder->populate_to_des_order($this->user, $des_order_id);

            $this->set('summary_des_orders_regenerated', true);
        } else {
            /*
             * ctrl eventuali occorrenze di SummaryOrder, se non ci sono lo popolo
             */
            $results = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id);
            if (empty($results))
                $SummaryDesOrder->populate_to_des_order($this->user, $des_order_id);
        }

        /*
         * ricarico il div con l'elenco dei summaryDesOrders
         */
        $results = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id);
        
        /*
         * calcolo le %
         */
        $tot_importo_orig = 0;
        foreach($results as $result) 
            $tot_importo_orig += $result['SummaryDesOrder']['importo_orig'];
        
        if ($debug) echo "<br />AjaxGasCode::admin_box_summary_des_orders - tot_importo_orig ".$tot_importo_orig;
        
        foreach($results as $numResult => $result) {
            $results[$numResult]['SummaryDesOrder']['percentuale'] = number_format(($result['SummaryDesOrder']['importo_orig'] * 100 / $tot_importo_orig), 0);
            if ($debug) echo "<br />AjaxGasCode::admin_box_summary_des_orders - importo_orig ".$result['SummaryDesOrder']['importo_orig']." - % ".$results[$numResult]['SummaryDesOrder']['percentuale'];
        }
        
        $this->set('results', $results);

        if ($debug) {
            echo "<pre>AjaxGasCode::admin_box_summary_des_orders \n ";
            print_r($results);
            echo "</pre>";
        }

        $this->layout = 'ajax';
    }

    /*
     * $trasportOptions 
     * 		options-qta     Divido il trasporto in base al quantitativo acquistato
     * 		options-weight  Divido il trasporto in base al peso di ogni acquisto
     * 		options-users   Divido il trasporto per ogni utente
     * 
     * visualizzo i dettaglio dell'importo del trasporto per ogni utente in base all'algoritmo scelto
     */

    public function admin_box_trasport($delivery_id, $order_id, $trasportOptions) {

        $debug = false;
        if ($debug)
            echo '<h2>admin_box_trasport()</h2>';

        if (empty($this->delivery_id) || empty($this->order_id) || $trasportOptions == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * registro l'importo del trasporto in Order
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $this->order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $trasport_type_db = null;
        switch ($trasportOptions) {
            case "options-qta":
                $trasport_type_db = 'QTA';
                break;
            case "options-weight":
                $trasport_type_db = 'WEIGHT';
                break;
            case "options-users":
                $trasport_type_db = 'USERS';
                break;
        }

        /*
         * creo le occorrenze in SummaryOrderTrasport, ma gia' popolato quando ho salvato l'importo del trasporto
         */
        App::import('Model', 'SummaryOrderTrasport');
        $SummaryOrderTrasport = new SummaryOrderTrasport;
        $results = $SummaryOrderTrasport->select_to_order($this->user, $this->order_id, 0, $debug);
        if (empty($results)) {

            if ($debug)
                echo '<h2>da SummaryOrderTrasport->select_to_order() nessun risultato => eseguo SummaryOrderTrasport->populate_to_order</h2>';

            $SummaryOrderTrasport->populate_to_order($this->user, $this->order_id, $debug);
        }
        else {
            if ($debug)
                echo '<h2>da SummaryOrderTrasport->select_to_order() VALORIZZATO => NON eseguo SummaryOrderTrasport->populate_to_order</h2>';
        }

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $this->delivery_id),
            'Order' => array('Order.isVisibleBackOffice' => 'Y',
                'Order.id' => $this->order_id),
            'Cart' => array('Cart.deleteToReferent' => 'N'));

        $orderBy = array('User' => 'User.name');

        $options = array('orders' => false, 'storerooms' => false, 'summaryOrderTrasports' => true, 'summaryOrders' => false,
            'articlesOrdersInOrder' => true,
            'suppliers' => true, 'referents' => true);

        $results = $Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);

        switch ($trasportOptions) {
            case "options-qta":
                /*
                 * ottengo il TOTALE dell'IMPORTO dell'ordine
                 * 	totale importo utente : x = totale importo ordine : 100%
                 */
                $sql = "SELECT
							sum(importo) as totaleOrdine
						FROM
							" . Configure::read('DB.prefix') . "summary_order_trasports as SummaryOrderTrasport 
						WHERE
							SummaryOrderTrasport.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
							and SummaryOrderTrasport.order_id = " . (int) $this->order_id;
                if ($debug)
                    echo '<br />' . $sql;
                $result = $SummaryOrderTrasport->query($sql);
                $totaleOrdine = 0;
                if (!empty($result)) {
                    $result = current($result);
                    $totaleOrdine = $result[0]['totaleOrdine'];
                }
                if ($debug)
                    echo '<br />totaleOrdine ' . $totaleOrdine;
                break;
            case "options-weight":
                /*
                 * ottengo il TOTALE del peso dell'ordine
                 * 	totale peso utente : x = totale peso ordine : 100%
                 */
                $sql = "SELECT
							sum(peso) as totaleOrdine
						FROM
							" . Configure::read('DB.prefix') . "summary_order_trasports as SummaryOrderTrasport 
						WHERE
							SummaryOrderTrasport.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
							and SummaryOrderTrasport.order_id = " . (int) $this->order_id;
                if ($debug)
                    echo '<br />' . $sql;
                $result = $SummaryOrderTrasport->query($sql);
                $totaleOrdine = 0;
                if (!empty($result)) {
                    $result = current($result);
                    $totaleOrdine = $result[0]['totaleOrdine'];
                }
                if ($debug)
                    echo '<br />totaleOrdine ' . $totaleOrdine;
                break;
            case "options-users":
                /*
                 * ottengo il TOTALE degli UTENTI dell'ordine
                 * 	totale utente : x = totale ordine : 100%
                 */
                $sql = "SELECT
							count(user_id) as totaleUtenti
						FROM
							" . Configure::read('DB.prefix') . "summary_order_trasports as SummaryOrderTrasport 
						WHERE
							SummaryOrderTrasport.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
							and SummaryOrderTrasport.order_id = " . (int) $this->order_id;
                if ($debug)
                    echo '<br />' . $sql;
                $result = $SummaryOrderTrasport->query($sql);
                $totaleUtenti = 0;
                if (!empty($result)) {
                    $result = current($result);
                    $totaleUtenti = $result[0]['totaleUtenti'];
                }
                if ($debug)
                    echo '<br />totaleUtenti ' . $totaleUtenti;
                break;
        }

        if ($debug)
            echo '<h2>Calcolo trasporto</h2>';
        /*
         * calcolo trasporto
         */
        $trasport = 0;
        if ($results['Delivery']['totOrders'] > 0)
            foreach ($results['Delivery'][0]['Order'] as $numOrder => $order) {

                $trasport = $order['Order']['trasport'];
                $trasport_type = $order['Order']['trasport_type'];

                if (isset($order['SummaryOrderTrasport']))
                    foreach ($order['SummaryOrderTrasport'] as $numResult => $summaryOrder) {

                        /*
                         * dati gia' inseriti
                         */
                        if ($trasport_type == $trasport_type_db) {
                            $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderTrasport'][$numResult]['SummaryOrderTrasport']['trasporto_percentuale'] = 0;
                            $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderTrasport'][$numResult]['SummaryOrderTrasport']['trasporto_importo'] = $summaryOrder['SummaryOrderTrasport']['importo_trasport'];
                        } else {

                            /*
                             * dati nuovi
                             */
                            switch ($trasportOptions) {
                                case "options-qta":
                                    $percentualeRispettoAlTotale = round(($summaryOrder['SummaryOrderTrasport']['importo'] * 100 / $totaleOrdine), 2);
                                    $trasporto_importo = round(($trasport * $percentualeRispettoAlTotale / 100), 2);
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderTrasport'][$numResult]['SummaryOrderTrasport']['trasporto_percentuale'] = $percentualeRispettoAlTotale;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderTrasport'][$numResult]['SummaryOrderTrasport']['trasporto_importo'] = $trasporto_importo;
                                    break;
                                case "options-weight":
                                    $percentualeRispettoAlTotale = round(($summaryOrder['SummaryOrderTrasport']['peso'] * 100 / $totaleOrdine), 2);
                                    $trasporto_importo = round(($trasport * $percentualeRispettoAlTotale / 100), 2);
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderTrasport'][$numResult]['SummaryOrderTrasport']['trasporto_percentuale'] = $percentualeRispettoAlTotale;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderTrasport'][$numResult]['SummaryOrderTrasport']['trasporto_importo'] = $trasporto_importo;
                                    break;
                                case "options-users":
                                    $percentualeRispettoAlTotale = round((100 / $totaleUtenti), 2);
                                    $trasporto_importo = round(($trasport / $totaleUtenti), 2);
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderTrasport'][$numResult]['SummaryOrderTrasport']['trasporto_percentuale'] = $percentualeRispettoAlTotale;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderTrasport'][$numResult]['SummaryOrderTrasport']['trasporto_importo'] = $trasporto_importo;
                                    break;
                            } // end switch
                        }
                    }
            }

        if ($debug)
            echo '<br />trasport ' . $trasport;

        $this->set('trasportOptions', $trasportOptions);  // options-qta options-weight options-users
        $this->set('trasport', $trasport);  // importo del trasporto
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

    /*
     * $cost_moreOptions
     * 		options-qta     Divido il cost_more in base al quantitativo acquistato
     * 		options-weight  Divido il cost_more in base al peso di ogni acquisto
     * 		options-users   Divido il cost_more per ogni utente
     *
     * visualizzo i dettaglio dell'importo del cost_more per ogni utente in base all'algoritmo scelto
     */

    public function admin_box_cost_more($delivery_id, $order_id, $cost_moreOptions) {

        $debug = false;
        if ($debug)
            echo '<h2>admin_box_cost_more()</h2>';

        if (empty($delivery_id) || empty($order_id) || $cost_moreOptions == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * registro l'importo del cost_more in Order
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $cost_more_type_db = null;
        switch ($cost_moreOptions) {
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

        /*
         * creo le occorrenze in SummaryOrderCostMore, ma gia' popolato quando ho salvato l'importo del cost_more
         */
        App::import('Model', 'SummaryOrderCostMore');
        $SummaryOrderCostMore = new SummaryOrderCostMore;
        $results = $SummaryOrderCostMore->select_to_order($this->user, $order_id, 0, $debug);
        if (empty($results)) {

            if ($debug)
                echo '<h2>da SummaryOrderCostMore->select_to_order() nessun risultato => eseguo SummaryOrderCostMore->populate_to_order</h2>';

            $SummaryOrderCostMore->populate_to_order($this->user, $order_id, $debug);
        }
        else {
            if ($debug)
                echo '<h2>da SummaryOrderCostMore->select_to_order() VALORIZZATO => NON eseguo SummaryOrderCostMore->populate_to_order</h2>';
        }

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $delivery_id),
            'Order' => array('Order.isVisibleBackOffice' => 'Y',
                'Order.id' => $order_id),
            'Cart' => array('Cart.deleteToReferent' => 'N'));

        $orderBy = array('User' => 'User.name');

        $options = array('orders' => false, 'storerooms' => false, 'summaryOrderCostMores' => true, 'summaryOrders' => false,
            'articlesOrdersInOrder' => true,
            'suppliers' => true, 'referents' => true);

        $results = $Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);
        switch ($cost_moreOptions) {
            case "options-qta":
                /*
                 * ottengo il TOTALE dell'IMPORTO dell'ordine
                 * 	totale importo utente : x = totale importo ordine : 100%
                 */
                $sql = "SELECT
							sum(importo) as totaleOrdine
						FROM
							" . Configure::read('DB.prefix') . "summary_order_cost_mores as SummaryOrderCostMore
						WHERE
							SummaryOrderCostMore.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
							and SummaryOrderCostMore.order_id = " . (int) $order_id;
                if ($debug)
                    echo '<br />' . $sql;
                $result = $SummaryOrderCostMore->query($sql);
                $totaleOrdine = 0;
                if (!empty($result)) {
                    $result = current($result);
                    $totaleOrdine = $result[0]['totaleOrdine'];
                }
                if ($debug)
                    echo '<br />totaleOrdine ' . $totaleOrdine;
                break;
            case "options-weight":
                /*
                 * ottengo il TOTALE del peso dell'ordine
                 * 	totale peso utente : x = totale peso ordine : 100%
                 */
                $sql = "SELECT
							sum(peso) as totaleOrdine
						FROM
							" . Configure::read('DB.prefix') . "summary_order_cost_mores as SummaryOrderCostMore
						WHERE
							SummaryOrderCostMore.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
							and SummaryOrderCostMore.order_id = " . (int) $order_id;
                if ($debug)
                    echo '<br />' . $sql;
                $result = $SummaryOrderCostMore->query($sql);
                $totaleOrdine = 0;
                if (!empty($result)) {
                    $result = current($result);
                    $totaleOrdine = $result[0]['totaleOrdine'];
                }
                if ($debug)
                    echo '<br />totaleOrdine ' . $totaleOrdine;
                break;
            case "options-users":
                /*
                 * ottengo il TOTALE degli UTENTI dell'ordine
                 * 	totale utente : x = totale ordine : 100%
                 */
                $sql = "SELECT
							count(user_id) as totaleUtenti
						FROM
							" . Configure::read('DB.prefix') . "summary_order_cost_mores as SummaryOrderCostMore
						WHERE
							SummaryOrderCostMore.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
							and SummaryOrderCostMore.order_id = " . (int) $order_id;
                if ($debug)
                    echo '<br />' . $sql;
                $result = $SummaryOrderCostMore->query($sql);
                $totaleUtenti = 0;
                if (!empty($result)) {
                    $result = current($result);
                    $totaleUtenti = $result[0]['totaleUtenti'];
                }
                if ($debug)
                    echo '<br />totaleUtenti ' . $totaleUtenti;
                break;
        }

        if ($debug)
            echo '<h2>Calcolo cost_more</h2>';
        /*
         * calcolo cost_more
         */
        $cost_more = 0;
        if ($results['Delivery']['totOrders'] > 0)
            foreach ($results['Delivery'][0]['Order'] as $numOrder => $order) {

                $cost_more = $order['Order']['cost_more'];
                $cost_more_type = $order['Order']['cost_more_type'];

                if (isset($order['SummaryOrderCostMore']))
                    foreach ($order['SummaryOrderCostMore'] as $numResult => $summaryOrder) {

                        /*
                         * dati gia' inseriti
                         */
                        if ($cost_more_type == $cost_more_type_db) {
                            $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostMore'][$numResult]['SummaryOrderCostMore']['cost_more_percentuale'] = 0;
                            $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostMore'][$numResult]['SummaryOrderCostMore']['cost_more_importo'] = $summaryOrder['SummaryOrderCostMore']['importo_cost_more'];
                        } else {

                            /*
                             * dati nuovi
                             */
                            switch ($cost_moreOptions) {
                                case "options-qta":
                                    $percentualeRispettoAlTotale = round(($summaryOrder['SummaryOrderCostMore']['importo'] * 100 / $totaleOrdine), 2);
                                    $cost_more_importo = round(($cost_more * $percentualeRispettoAlTotale / 100), 2);
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostMore'][$numResult]['SummaryOrderCostMore']['cost_more_percentuale'] = $percentualeRispettoAlTotale;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostMore'][$numResult]['SummaryOrderCostMore']['cost_more_importo'] = $cost_more_importo;
                                    break;
                                case "options-weight":
                                    $percentualeRispettoAlTotale = round(($summaryOrder['SummaryOrderCostMore']['peso'] * 100 / $totaleOrdine), 2);
                                    $cost_more_importo = round(($cost_more * $percentualeRispettoAlTotale / 100), 2);
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostMore'][$numResult]['SummaryOrderCostMore']['cost_more_percentuale'] = $percentualeRispettoAlTotale;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostMore'][$numResult]['SummaryOrderCostMore']['cost_more_importo'] = $cost_more_importo;
                                    break;
                                case "options-users":
                                    $percentualeRispettoAlTotale = round((100 / $totaleUtenti), 2);
                                    $cost_more_importo = round(($cost_more / $totaleUtenti), 2);
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostMore'][$numResult]['SummaryOrderCostMore']['cost_more_percentuale'] = $percentualeRispettoAlTotale;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostMore'][$numResult]['SummaryOrderCostMore']['cost_more_importo'] = $cost_more_importo;
                                    break;
                            } // end switch
                        }
                    }
            }

        if ($debug)
            echo '<br />cost_more ' . $cost_more;

        $this->set('cost_moreOptions', $cost_moreOptions);  // options-qta options-weight options-users
        $this->set('cost_more', $cost_more);  // importo del cost_more
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

    /*
     * $cost_lessOptions
     * 		options-qta     Applica il cost_less in base al quantitativo acquistato
     * 		options-weight  Applica il cost_less in base al peso di ogni acquisto
     * 		options-users   Applica il cost_less per ogni utente
     *
     * visualizzo i dettaglio dell'importo del cost_less per ogni utente in base all'algoritmo scelto
     */

    public function admin_box_cost_less($delivery_id, $order_id, $cost_lessOptions) {

        $debug = false;
        if ($debug)
            echo '<h2>admin_box_cost_less()</h2>';

        if (empty($delivery_id) || empty($order_id) || $cost_lessOptions == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * registro l'importo del cost_less in Order
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $cost_less_type_db = null;
        switch ($cost_lessOptions) {
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

        /*
         * creo le occorrenze in SummaryOrderCostLess, ma gia' popolato quando ho salvato l'importo del cost_less
         */
        App::import('Model', 'SummaryOrderCostLess');
        $SummaryOrderCostLess = new SummaryOrderCostLess;
        $results = $SummaryOrderCostLess->select_to_order($this->user, $order_id, 0, $debug);
        if (empty($results)) {

            if ($debug)
                echo '<h2>da SummaryOrderCostLess->select_to_order() nessun risultato => eseguo SummaryOrderCostLess->populate_to_order</h2>';

            $SummaryOrderCostLess->populate_to_order($this->user, $order_id, $debug);
        }
        else {
            if ($debug)
                echo '<h2>da SummaryOrderCostLess->select_to_order() VALORIZZATO => NON eseguo SummaryOrderCostLess->populate_to_order</h2>';
        }

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                'Delivery.id' => (int) $delivery_id),
            'Order' => array('Order.isVisibleBackOffice' => 'Y',
                'Order.id' => $order_id),
            'Cart' => array('Cart.deleteToReferent' => 'N'));

        $orderBy = array('User' => 'User.name');

        $options = array('orders' => false, 'storerooms' => false, 'summaryOrderCostLess' => true, 'summaryOrders' => false,
            'articlesOrdersInOrder' => true,
            'suppliers' => true, 'referents' => true);

        $results = $Delivery->getDataWithoutTabs($this->user, $conditions, $options, $orderBy);

        switch ($cost_lessOptions) {
            case "options-qta":
                /*
                 * ottengo il TOTALE dell'IMPORTO dell'ordine
                 * 	totale importo utente : x = totale importo ordine : 100%
                 */
                $sql = "SELECT
							sum(importo) as totaleOrdine
						FROM
							" . Configure::read('DB.prefix') . "summary_order_cost_lesses as SummaryOrderCostLess
						WHERE
							SummaryOrderCostLess.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
							and SummaryOrderCostLess.order_id = " . (int) $order_id;
                if ($debug)
                    echo '<br />' . $sql;
                $result = $SummaryOrderCostLess->query($sql);
                $totaleOrdine = 0;
                if (!empty($result)) {
                    $result = current($result);
                    $totaleOrdine = $result[0]['totaleOrdine'];
                }
                if ($debug)
                    echo '<br />totaleOrdine ' . $totaleOrdine;
                break;
            case "options-weight":
                /*
                 * ottengo il TOTALE del peso dell'ordine
                 * 	totale peso utente : x = totale peso ordine : 100%
                 */
                $sql = "SELECT
							sum(peso) as totaleOrdine
						FROM
							" . Configure::read('DB.prefix') . "summary_order_cost_lesses as SummaryOrderCostLess
						WHERE
							SummaryOrderCostLess.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
							and SummaryOrderCostLess.order_id = " . (int) $order_id;
                if ($debug)
                    echo '<br />' . $sql;
                $result = $SummaryOrderCostLess->query($sql);
                $totaleOrdine = 0;
                if (!empty($result)) {
                    $result = current($result);
                    $totaleOrdine = $result[0]['totaleOrdine'];
                }
                if ($debug)
                    echo '<br />totaleOrdine ' . $totaleOrdine;
                break;
            case "options-users":
                /*
                 * ottengo il TOTALE degli UTENTI dell'ordine
                 * 	totale utente : x = totale ordine : 100%
                 */
                $sql = "SELECT
							count(user_id) as totaleUtenti
						FROM
							" . Configure::read('DB.prefix') . "summary_order_cost_lesses as SummaryOrderCostLess
						WHERE
							SummaryOrderCostLess.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
							and SummaryOrderCostLess.order_id = " . (int) $order_id;
                if ($debug)
                    echo '<br />' . $sql;
                $result = $SummaryOrderCostLess->query($sql);
                $totaleUtenti = 0;
                if (!empty($result)) {
                    $result = current($result);
                    $totaleUtenti = $result[0]['totaleUtenti'];
                }
                if ($debug)
                    echo '<br />totaleUtenti ' . $totaleUtenti;
                break;
        }

        if ($debug)
            echo '<h2>Calcolo cost_less: importo NEGATIVO perche SCONTO (differenza con cost_more e trasport)</h2>';
        /*
         * calcolo cost_less
         */
        $cost_less = 0;
        if ($results['Delivery']['totOrders'] > 0)
            foreach ($results['Delivery'][0]['Order'] as $numOrder => $order) {

                $cost_less = $order['Order']['cost_less'];
                $cost_less_type = $order['Order']['cost_less_type'];

                if (isset($order['SummaryOrderCostLess']))
                    foreach ($order['SummaryOrderCostLess'] as $numResult => $summaryOrder) {

                        /*
                         * dati gia' inseriti
                         */
                        if ($cost_less_type == $cost_less_type_db) {
                            $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostLess'][$numResult]['SummaryOrderCostLess']['cost_less_percentuale'] = 0;
                            $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostLess'][$numResult]['SummaryOrderCostLess']['cost_less_importo'] = $summaryOrder['SummaryOrderCostLess']['importo_cost_less'];
                        } else {

                            /*
                             * dati nuovi
                             * 
                             * (-1 * ... importo NEGATIVO perche SCONTO, differenza con cost_more e trasport 
                             */
                            switch ($cost_lessOptions) {
                                case "options-qta":
                                    $percentualeRispettoAlTotale = round(($summaryOrder['SummaryOrderCostLess']['importo'] * 100 / $totaleOrdine), 2);
                                    $cost_less_importo = round(($cost_less * $percentualeRispettoAlTotale / 100), 2);
                                    if ($debug)
                                        echo '<br /> cost_less_importo ' . $cost_less_importo;
                                    $cost_less_importo = (-1 * $cost_less_importo);
                                    if ($debug)
                                        echo ' - poi NEGATIVO cost_less_importo ' . $cost_less_importo;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostLess'][$numResult]['SummaryOrderCostLess']['cost_less_percentuale'] = $percentualeRispettoAlTotale;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostLess'][$numResult]['SummaryOrderCostLess']['cost_less_importo'] = $cost_less_importo;
                                    break;
                                case "options-weight":
                                    $percentualeRispettoAlTotale = round(($summaryOrder['SummaryOrderCostLess']['peso'] * 100 / $totaleOrdine), 2);
                                    $cost_less_importo = round(($cost_less * $percentualeRispettoAlTotale / 100), 2);
                                    if ($debug)
                                        echo '<br /> cost_less_importo ' . $cost_less_importo;
                                    $cost_less_importo = (-1 * $cost_less_importo);
                                    if ($debug)
                                        echo ' - poi NEGATIVO cost_less_importo ' . $cost_less_importo;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostLess'][$numResult]['SummaryOrderCostLess']['cost_less_percentuale'] = $percentualeRispettoAlTotale;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostLess'][$numResult]['SummaryOrderCostLess']['cost_less_importo'] = $cost_less_importo;
                                    break;
                                case "options-users":
                                    $percentualeRispettoAlTotale = round((100 / $totaleUtenti), 2);
                                    $cost_less_importo = round(($cost_less / $totaleUtenti), 2);
                                    if ($debug)
                                        echo '<br /> cost_less_importo ' . $cost_less_importo;
                                    $cost_less_importo = (-1 * $cost_less_importo);
                                    if ($debug)
                                        echo ' - poi NEGATIVO cost_less_importo ' . $cost_less_importo;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostLess'][$numResult]['SummaryOrderCostLess']['cost_less_percentuale'] = $percentualeRispettoAlTotale;
                                    $results['Delivery'][0]['Order'][$numOrder]['SummaryOrderCostLess'][$numResult]['SummaryOrderCostLess']['cost_less_importo'] = $cost_less_importo;
                                    break;
                            } // end switch
                        }
                    }
            }

        if ($debug)
            echo '<br />cost_less ' . $cost_less;

        $this->set('cost_lessOptions', $cost_lessOptions);  // options-qta options-weight options-users
        $this->set('cost_less', $cost_less);  // importo del cost_less
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

    public function admin_box_stat_orders($supplier_organization_id) {
        if (empty($supplier_organization_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'StatOrder');
        $StatOrder = new StatOrder;

        $conditions = array('StatOrder.organization_id' => (int) $this->user->organization['Organization']['id'],
            'StatOrder.supplier_organization_id' => $supplier_organization_id);
        $results = $StatOrder->find('all', array('conditions' => $conditions, 'order' => 'StatOrder.data_inizio ASC', 'recursive' => 1));

        $orders = array();
        if (!empty($results)) {
            foreach ($results as $result)
                $orders[$result['StatOrder']['id']] = $result['SuppliersOrganization']['name'] . ' - dal ' . $result['StatOrder']['data_inizio_'] . ' al ' . $result['StatOrder']['data_fine_'];
        } else
            $order_id = 0; // lo setto a 0 cosi' il dettaglio dell'ordine non viene eseguito

        $this->set(compact('orders'));

        $this->layout = 'ajax';
    }

    /*
     * key = $order_id_$article_organization_id_$article_id_$user_id
     */

    public function admin_setImportoForzato($row_id, $key, $importo_forzato = 0) {

        if (empty($row_id) || (empty($key) && strpos($key, '_') !== false)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($order_id, $article_organization_id, $article_id, $user_id) = explode('_', $key);

        App::import('Model', 'Cart');
        $Cart = new Cart();
        if (!$Cart->exists($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id, $user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $sql = "UPDATE
					" . Configure::read('DB.prefix') . "carts
				SET 
					importo_forzato = " . $this->importoToDatabase($importo_forzato) . " 
				WHERE
					organization_id = " . $this->user->organization['Organization']['id'] . "
					AND order_id = " . $order_id . "
					AND article_organization_id = " . $article_organization_id . "
					AND article_id = " . $article_id . "
					AND user_id = " . $user_id;
        //echo '<br/>'.$sql;
        try {
            $Cart->query($sql);
            $esito = true;
        } catch (Exception $e) {
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
            $esito = false;
        }

        if ($esito)
            $content_for_layout = '<script type="text/javascript">managementCart(\'' . $row_id . '\',\'OKIMPORTO\',null);</script>';
        else
            $content_for_layout = '<script type="text/javascript">managementCart(\'' . $row_id . '\',\'NO\',null);</script>';

        $this->set('content_for_layout', $content_for_layout);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }

    /*
     * key = $order_id_$article_organization_id_$article_id_$user_id
     */

    function admin_setNotaForzato($key) {

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($order_id, $article_organization_id, $article_id, $user_id) = explode('_', $key);

        App::import('Model', 'Cart');
        $Cart = new Cart();
        if (!$Cart->exists($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id, $user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {

            $sql = "UPDATE
					" . Configure::read('DB.prefix') . "carts
				SET
					nota = '" . addslashes($this->request->data['notaTextEcomm']) . "' 
				WHERE
					organization_id = " . $this->user->organization['Organization']['id'] . "
					AND order_id = " . $order_id . "
					AND article_organization_id = " . $article_organization_id . "
					AND article_id = " . $article_id . "
					AND user_id = " . $user_id;
            try {
                $Cart->query($sql);
            } catch (Exception $e) {
                CakeLog::write('error', $sql);
                CakeLog::write('error', $e);
            }
        }

        $content_for_layout = '';
        $this->set('content_for_layout', $content_for_layout);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }

    /*
     * Event ('#dialogmodal')open()
     * 	View/Docs/admin_management_cart.ctp
     * 		/Layouts/ajax.ctp 
     * 		modal in View/Docs/admin_management_cart.ctp
     *
     *  key = $order_id_$article_organization_id_$article_id_$user_id
     */

    function admin_getNotaForzato($key) {

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($order_id, $article_organization_id, $article_id, $user_id) = explode('_', $key);

        App::import('Model', 'Cart');
        $Cart = new Cart();
        if (!$Cart->exists($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id, $user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $options = array();
        $options['conditions'] = array('Cart.organization_id' => $this->user->organization['Organization']['id'],
            'Cart.order_id' => $order_id,
            'Cart.article_organization_id' => $article_organization_id,
            'Cart.article_id' => $article_id,
            'Cart.user_id' => $user_id);
        $options['recursive'] = -1;
        $options['fields'] = array('nota');
        $results = $Cart->find('first', $options);

        $nota = $results['Cart']['nota'];

        if (!empty($nota))
            $this->set('content_for_layout', $nota);
        else
            $this->set('content_for_layout', '');

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }

}
