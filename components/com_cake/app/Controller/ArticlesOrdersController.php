<?php

App::uses('AppController', 'Controller');

/**
 * ArticlesOrders Controller
 *
 * @property ArticlesOrder $ArticlesOrder
 */
class ArticlesOrdersController extends AppController {

    public $components = array('RequestHandler', 'ActionsDesOrder');
    private $order;

    public function beforeFilter() {
        parent::beforeFilter();

        App::import('Model', 'Order');
        $Order = new Order;

        /* ctrl ACL */
        $actionWithPermission = array('admin_add', 'admin_index');
        if (in_array($this->action, $actionWithPermission)) {

            if ($this->isSuperReferente()) {
                
            } else {
                if (empty($this->order_id) || !$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
                    $this->Session->setFlash(__('msg_not_permission'));
                    $this->myRedirect(Configure::read('routes_msg_stop'));
                }
            }
        }
        /* ctrl ACL */


        /*
         * ctrl che la consegna e l'ordine siano visibili in backoffice
         */
        if ($this->action != 'admin_order_choice') {
            $options = array();
            $options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
                'Order.id' => $this->order_id);
            $options['recursive'] = 0;
            $results = $Order->find('first', $options);
            if ($results['Delivery']['isVisibleBackOffice'] == 'N') {
                $this->Session->setFlash(__('msg_delivery_not_visible_backoffice'));
                $this->myRedirect(Configure::read('routes_msg_stop'));
            }
            if ($results['Order']['isVisibleBackOffice'] == 'N') {
                $this->Session->setFlash(__('msg_order_not_visible_backoffice'));
                $this->myRedirect(Configure::read('routes_msg_stop'));
            }

            $this->order = $results;
            $this->set('order', $this->order);
        } // if ($this->action != 'admin_order_choice') 
        else {
            if (!$this->isUserPermissionArticlesOrder($this->user)) {
                $this->Session->setFlash(__('msg_not_organization_config'));
                if (!$debug)
                    $this->myRedirect(Configure::read('routes_msg_stop'));
            }
        }
    }

    /*
     * richiamato dopo la creazione di un ordine 
     * 		se Organization.hasArticlesOrder=='N'
     * 		o da admin_easy_order
     *
     * aggiorno orders.state_code = 'OPEN-NEXT' o 'OPEN'
     */
    public function admin_add_hidden() {

        /*
         * cancello eventuali doppioni
         * */
        $this->ArticlesOrder->delete($this->user->organization['Organization']['id'], $this->order_id);

        $msg = '';

        /*
         * estraggo gli articoli associati al fornitore
         */
        App::import('Model', 'Article');
        $Article = new Article;
        $results = $Article->getBySupplierOrganizationArticleInArticlesOrder($this->user, $this->order['Order']['supplier_organization_id']);
        foreach ($results as $result) {
            $row['ArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
            $row['ArticlesOrder']['article_organization_id'] = $this->user->organization['Organization']['id'];
            $row['ArticlesOrder']['article_id'] = $result['Article']['id'];
            $row['ArticlesOrder']['order_id'] = $this->order_id;
            $row['ArticlesOrder']['name'] = $result['Article']['name'];
            $row['ArticlesOrder']['prezzo'] = $result['Article']['prezzo'];
            $row['ArticlesOrder']['qta_cart'] = 0;
            $row['ArticlesOrder']['pezzi_confezione'] = $result['Article']['pezzi_confezione'];
            $row['ArticlesOrder']['qta_minima'] = $result['Article']['qta_minima'];
            $row['ArticlesOrder']['qta_massima'] = $result['Article']['qta_massima'];
            $row['ArticlesOrder']['qta_minima_order'] = $result['Article']['qta_minima_order'];
            $row['ArticlesOrder']['qta_massima_order'] = $result['Article']['qta_massima_order'];
            $row['ArticlesOrder']['qta_multipli'] = $result['Article']['qta_multipli'];
            $row['ArticlesOrder']['flag_bookmarks'] = 'N';
            if ($this->user->organization['Organization']['hasFieldArticleAlertToQta'] == 'N')
                $row['ArticlesOrder']['alert_to_qta'] = 0;
            else
                $row['ArticlesOrder']['alert_to_qta'] = $result['Article']['alert_to_qta'];
            $row['ArticlesOrder']['stato'] = 'Y';

            /*
             * richiamo la validazione
             */
            $this->ArticlesOrder->set($row);
            if (!$this->ArticlesOrder->validates()) {
                $errors = $this->ArticlesOrder->validationErrors;
                $tmp = '';
                $flatErrors = Set::flatten($errors);
                if (count($errors) > 0) {
                    $tmp = '';
                    foreach ($flatErrors as $key => $value)
                        $tmp .= $value . ' - ';
                }
                $msg .= "Articolo non associato all'ordine: dati non validi, $tmp<br />";
            } else {
                $this->ArticlesOrder->create();
                if (!$this->ArticlesOrder->save($row)) {
                    $msg .= "<br />articolo " . $result['Article']['id'] . " in errore!";
                }
            }
        } // end foreach

        if (!empty($msg))
            $this->Session->setFlash(__('The articles order could not be saved. Please, try again.') . $msg);
        else {
            /*
             * aggiorno lo stato dell'ordine
             * 	da OPEN-NEXT o OPEN
             * */
            $utilsCrons = new UtilsCrons(new View(null));
            $utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $this->order_id);

            /*
             * setto la tipologia Draw (SIMPLE o COMPLETE)
             */
            App::import('Model', 'Order');
            $Order = new Order;
            $Order->updateTypeDraw($this->user, $this->order_id);

            $this->Session->setFlash(__('The articles order has been saved'));
        }

        $this->myRedirect(Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id=' . $this->delivery_id . '&order_id=' . $this->order_id);
    }

    /*
     *   richiamato dopo la creazione di un ordine se Organization.hasArticlesOrder=='Y'
     *   aggiorno orders.state_code = 'OPEN-NEXT' o 'OPEN'
     *
     * 	action_post = action_articles_orders_current gestione normale
     *  action_post = action_articles_orders_previuos associo articoli ordine precedente
     */

    public function admin_add($order_id = 0, $des_order_id = 0) {

        $debug = false;

        $previousResults = $this->__getPreviousArticlesOrder($this->user, $this->order['Order']);
        $this->set('previousResults', $previousResults);
        $this->set('des_order_id', $des_order_id);

        if ($this->request->is('post') || $this->request->is('put')) {

            $msg = "";
            /*
             * cancello eventuali doppioni
             * */
            $this->ArticlesOrder->delete($this->user->organization['Organization']['id'], $this->order_id);

            $des_order_id = $this->request->data['ArticlesOrder']['des_order_id'];
            $action_post = $this->request->data['ArticlesOrder']['action_post'];
            if ($action_post == 'action_articles_orders_previuos')
                $this->request->data = $this->__ridefinedDataToPreviousArticlesOrder($previousResults);

            $article_id_selected = $this->request->data['ArticlesOrder']['article_id_selected'];
            $arr_article_id_selected = explode(',', $article_id_selected);

            foreach ($this->request->data['Article'] as $key => $data) {

                $article_id = $key;

                if (isset($article_id) && in_array($article_id, $arr_article_id_selected)) {
                
					/*
                	 * get Article.name
                	 */
	                App::import('Model', 'Article');
	                $Article = new Article;
                            
                    $options = array();
                    $options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'], 
                     								'Article.id' => $article_id);
                    $options['fields'] = array('Article.name');
                    $options['recursive'] = -1;
                      
                    $articleResults = $Article->find('first', $options);
                    $row['ArticlesOrder']['name'] = $articleResults['Article']['name'];
                    
                    
                                    
                    $row['ArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
                    $row['ArticlesOrder']['article_organization_id'] = $this->user->organization['Organization']['id'];
                    $row['ArticlesOrder']['article_id'] = $article_id;
                    $row['ArticlesOrder']['order_id'] = $this->order_id;
                    $row['ArticlesOrder']['prezzo'] = $data['ArticlesOrderPrezzo'];
                    $row['ArticlesOrder']['qta_cart'] = 0;
                    $row['ArticlesOrder']['pezzi_confezione'] = $data['ArticlesOrderPezziConfezione'];
                    $row['ArticlesOrder']['qta_minima'] = $data['ArticlesOrderQtaMinima'];
                    $row['ArticlesOrder']['qta_massima'] = $data['ArticlesOrderQtaMassima'];
                    $row['ArticlesOrder']['qta_minima_order'] = $data['ArticlesOrderQtaMinimaOrder'];
                    $row['ArticlesOrder']['qta_massima_order'] = $data['ArticlesOrderQtaMassimaOrder'];
                    $row['ArticlesOrder']['qta_multipli'] = $data['ArticlesOrderQtaMultipli'];
                    $row['ArticlesOrder']['flag_bookmarks'] = 'N';
                    if ($this->user->organization['Organization']['hasFieldArticleAlertToQta'] == 'N')
                        $row['ArticlesOrder']['alert_to_qta'] = 0;
                    else
                        $row['ArticlesOrder']['alert_to_qta'] = $data['ArticlesOrderAlertToQta'];
                    $row['ArticlesOrder']['stato'] = 'Y';

                    if ($debug) {
                        echo "<pre>";
                        print_r($row);
                        echo "</pre>";
                    }

                    /*
                     * richiamo la validazione
                     */
                    $this->ArticlesOrder->set($row);
                    if (!$this->ArticlesOrder->validates()) {

                        $errors = $this->ArticlesOrder->validationErrors;
                        $tmp = '';
                        $flatErrors = Set::flatten($errors);
                        if (count($errors) > 0) {
                            $tmp = '';
                            foreach ($flatErrors as $key => $value)
                                $tmp .= $value . ' - ';
                        }
                        $msg .= "Articolo non inserito: dati non validi, $tmp<br />";
                        $this->Session->setFlash($msg);
                    } else {

                        $this->ArticlesOrder->create();
                        if (!$this->ArticlesOrder->save($row)) {
                            $msg .= "<br />articolo " . $article_id . " in errore!";
                        }
                    }
                } // end if(isset($article_id) && in_array($article_id, $arr_article_id_selected))
            } // end foreach 

            if (!empty($msg))
                $this->Session->setFlash(__('The articles order could not be saved. Please, try again.'));
            else {
                /*
                 * aggiorno lo stato dell'ordine 
                 * 	da OPEN-NEXT o OPEN
                 * */
                $utilsCrons = new UtilsCrons(new View(null));
                $utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $this->order_id);

                /*
                 * setto la tipologia Draw (SIMPLE o COMPLETE)
                 */
                App::import('Model', 'Order');
                $Order = new Order;
                $Order->updateTypeDraw($this->user, $this->order_id);

                if ($this->user->organization['Organization']['hasDes'] == 'Y' && !empty($des_order_id)) {

                    $isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);

                    App::import('Model', 'DesOrder');
                    $DesOrder = new DesOrder;
                    $DesOrder->insertOrUpdateArticlesOrderAllOrganizations($this->user, $des_order_id, $this->order_id, null, $isTitolareDesSupplier, $debug);

                    $this->Session->setFlash(__('The articles order has been saved'));
                    $url = Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id=' . $this->delivery_id . '&order_id=' . $this->order_id . '&des_order_id=' . $des_order_id;
                } else {
                    $this->Session->setFlash(__('The articles order has been saved'));
                    $url = Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id=' . $this->delivery_id . '&order_id=' . $this->order_id;
                }

                if (!$debug)
                    $this->myRedirect($url);
            }
        } // end if ($this->request->is('post')) 

        /*
         * estraggo gli articoli associati al fornitore
         */
        App::import('Model', 'Article');
        $Article = new Article;
        $results = $Article->getBySupplierOrganizationArticleInArticlesOrder($this->user, $this->order['Order']['supplier_organization_id']);
        $this->set('results', $results);

        /*
         * dati DesOrder
         */
        if ($this->user->organization['Organization']['hasDes'] == 'Y' && !empty($des_order_id)) {

            App::import('Model', 'DesOrder');
            $DesOrder = new DesOrder();

            $desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
            /*
              echo "<pre>";
              print_r($desOrdersResults);
              echo "</pre>";
             */
            $this->set(compact('desOrdersResults'));
        }
    }

    /*
     * elenco degli articoli associati all'ordine DES dove il referente non e' titolare
     */

    public function admin_index_only_read_des() {

        $debug = false;

        if (empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }


        $des_order_id = 0;
        if (!$this->user->organization['Organization']['hasDes'] == 'Y') {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        App::import('Model', 'DesOrdersOrganization');
        $DesOrdersOrganization = new DesOrdersOrganization();

        $desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
        if (!empty($desOrdersOrganizationResults)) {

            $des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];

            App::import('Model', 'DesOrder');
            $DesOrder = new DesOrder();

            $desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
            /*
              echo "<pre>";
              print_r($desOrdersResults);
              echo "</pre>";
             */
            $this->set(compact('desOrdersResults'));
        }
        $this->set(compact('des_order_id'));

        /*
         * articoli associati all'ordine
         */
        $this->ArticlesOrder->unbindModel(array('belongsTo' => array('Cart', 'Order')));
        $options = array();
        $options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
            'ArticlesOrder.order_id' => $this->order_id,
            'Article.stato' => 'Y');
        $options['order'] = 'Article.name';
        $options['recursive'] = 0;
        $results = $this->ArticlesOrder->find('all', $options);
        if ($debug) {
            echo "<pre>ArticlesOrders::admin_index_only_read_des \n ";
            print_r($options);
            print_r($results);
            echo "</pre>";
        }

        /*
         * se vuoto, ctrl se copiarli dal titolare
         */
        if (empty($results)) {
            $isTitolareDesSupplier = false;

            App::import('Model', 'DesOrder');
            $DesOrder = new DesOrder;
            $DesOrder->insertOrUpdateArticlesOrderAllOrganizations($this->user, $des_order_id, $this->order_id, null, $isTitolareDesSupplier, $debug);

            /*
             * aggiorno lo stato dell'ordine
             * 	da OPEN-NEXT o OPEN o CLOSE a eventualmente CREATE-INCOMPLETE
             * */
            $utilsCrons = new UtilsCrons(new View(null));
            $utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $this->order_id);

            /*
             * rileggo i dati
             */
            $this->ArticlesOrder->unbindModel(array('belongsTo' => array('Cart', 'Order')));
            $options = array();
            $options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
                'ArticlesOrder.order_id' => $this->order_id,
                'Article.stato' => 'Y');
            $options['order'] = 'Article.name';
            $options['recursive'] = 0;
            $results = $this->ArticlesOrder->find('all', $options);
        }

        /*
         *  ctrl eventuali acquisti gia' fatti del proprio GAS
         */
        App::import('Model', 'Cart');
        foreach ($results as $numResult => $result) {
            $Cart = new Cart();

            $options = array();
            $options['conditions'] = array('Cart.organization_id' => $this->user->organization['Organization']['id'],
                'Cart.order_id' => $result['ArticlesOrder']['order_id'],
                'Cart.article_organization_id' => $result['ArticlesOrder']['article_organization_id'],
                'Cart.article_id' => $result['ArticlesOrder']['article_id'],
                'Cart.deleteToReferent' => 'N'
            );
            $options['order'] = '';
            $options['recursive'] = -1;
            $cartResults = $Cart->find('all', $options);

            $results[$numResult]['Cart'] = $cartResults;
        }
        $this->set('results', $results);
    }

	/*  
	 * articoli gestiti dal produttore SuppliersOrganization.owner_articles =='SUPPLIER'
	 */ 
    public function admn_add_prod_gas($order_id) {

        $debug = false;

        if (empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

	}	

    /*
     * elenco degli articoli associati all'ordine
     */

    public function admin_index() {

        $debug = false;

        if (empty($this->order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * D.E.S.
         */
        $des_order_id = 0;
        if ($this->user->organization['Organization']['hasDes'] == 'Y') {

            App::import('Model', 'DesOrdersOrganization');
            $DesOrdersOrganization = new DesOrdersOrganization();

            $desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
            if (!empty($desOrdersOrganizationResults)) {

                $des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];

                /*
                 * ctrl ACL, pagina visibile solo dal titolare
                 */
                $isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);
                if (!$isTitolareDesSupplier) {
                    $this->Session->setFlash(__('msg_not_permission'));
                    $this->myRedirect(Configure::read('routes_msg_stop'));
                }

                App::import('Model', 'DesOrder');
                $DesOrder = new DesOrder();
                $desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id, $debug);

                /*
                 * ctrl eventuali occorrenze di SummaryDesOrder
                 */
                App::import('Model', 'SummaryDesOrder');
                $SummaryDesOrder = new SummaryDesOrder;
                $summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id, $this->user->organization['Organization']['id']);
                /*
                  echo "<pre>";
                  print_r($summaryDesOrderResults);
                  echo "</pre>";
                 */

                $this->set(compact('desOrdersResults', 'summaryDesOrderResults'));
            }
        } // DES
        $this->set(compact('des_order_id'));

        if ($this->request->is('post') || $this->request->is('put')) {

            App::import('Model', 'DesOrder');

            $msg = "";

            /*
             * articoli da aggiungere in ArticlesOrder
             * */
            $article_id_selected = $this->request->data['ArticlesOrder']['article_id_selected'];
            if (!empty($article_id_selected)) {
                $arr_article_id_selected = explode(',', $article_id_selected);

                foreach ($this->request->data['Article'] as $key => $data) {
                    $article_id = $key;

                    if (isset($article_id) && in_array($article_id, $arr_article_id_selected)) {
                    
                    	/*
                    	 * get Article.name
                    	 */
		                App::import('Model', 'Article');
		                $Article = new Article;
                                
                        $options = array();
                        $options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'], 
                         								'Article.id' => $article_id);
                        $options['fields'] = array('Article.name');
                        $options['recursive'] = -1;
                          
                        $articleResults = $Article->find('first', $options);
                        $row['ArticlesOrder']['name'] = $articleResults['Article']['name'];
                        
                        
                        $row['ArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
                        $row['ArticlesOrder']['order_id'] = $this->order_id;
                        $row['ArticlesOrder']['article_organization_id'] = $this->user->organization['Organization']['id'];
                        $row['ArticlesOrder']['article_id'] = $article_id;
                        $row['ArticlesOrder']['qta_cart'] = "0";
                        $row['ArticlesOrder']['prezzo'] = $data['ArticlesOrderPrezzo'];
                        $row['ArticlesOrder']['pezzi_confezione'] = $data['ArticlesOrderPezziConfezione'];
                        $row['ArticlesOrder']['qta_minima'] = $data['ArticlesOrderQtaMinima'];
                        $row['ArticlesOrder']['qta_massima'] = $data['ArticlesOrderQtaMassima'];
                        $row['ArticlesOrder']['qta_minima_order'] = $data['ArticlesOrderQtaMinimaOrder'];
                        $row['ArticlesOrder']['qta_massima_order'] = $data['ArticlesOrderQtaMassimaOrder'];
                        $row['ArticlesOrder']['qta_multipli'] = $data['ArticlesOrderQtaMultipli'];
                        $row['ArticlesOrder']['flag_bookmarks'] = 'N';
                        if ($this->user->organization['Organization']['hasFieldArticleAlertToQta'] == 'N')
                            $row['ArticlesOrder']['alert_to_qta'] = 0;
                        else
                            $row['ArticlesOrder']['alert_to_qta'] = $data['ArticlesOrderAlertToQta'];
                        $row['ArticlesOrder']['stato'] = 'Y';

                        /*
                         * richiamo la validazione
                         */
                        $this->ArticlesOrder->set($row);
                        if (!$this->ArticlesOrder->validates()) {
                            $errors = $this->ArticlesOrder->validationErrors;
                            break;
                        }

                        $this->ArticlesOrder->create();
                        if (!$this->ArticlesOrder->save($row)) {
                            $msg .= "<br />Articolo (" . $article_id . ") non associato all'ordine!";
                        }

                        /*
                         *  D E S
                         */
                        if ($this->user->organization['Organization']['hasDes'] == 'Y' && !empty($des_order_id)) {

                            $isTitolareDesSupplier = true;
                            $articles_orders_key = array('organization_id' => $row['ArticlesOrder']['organization_id'],
                                'order_id' => $row['ArticlesOrder']['order_id'],
                                'article_organization_id' => $row['ArticlesOrder']['article_organization_id'],
                                'article_id' => $row['ArticlesOrder']['article_id']);
                            $DesOrder = new DesOrder;
                            $DesOrder->insertOrUpdateArticlesOrderAllOrganizations($this->user, $des_order_id, $this->order_id, $articles_orders_key, $isTitolareDesSupplier, $debug);
                        }
                    }
                } // end foreach
            } // if(!empty($article_id_selected))


            /*
             * ArticlesOrder da cancellare
             * 	=> cancello tutti gli eventuali acquisti (Carts)
             * */
            $article_order_key_selected = $this->request->data['ArticlesOrder']['article_order_key_selected'];
            if (!empty($article_order_key_selected)) {
                $arr_article_order_key_selected = explode(',', $article_order_key_selected);

                App::import('Model', 'Cart');

                $article_ids = '';
                foreach ($arr_article_order_key_selected as $article_order_key) {

                    list($order_id, $article_id) = explode('_', $article_order_key);
                    $article_ids .= $article_id . ',';

                    if (!$this->ArticlesOrder->exists($this->user->organization['Organization']['id'], $order_id, $this->user->organization['Organization']['id'], $article_id)) {
                        $this->Session->setFlash(__('msg_error_params'));
                        $this->myRedirect(Configure::read('routes_msg_exclamation'));
                    }

                    if (!$this->ArticlesOrder->delete($this->user->organization['Organization']['id'], $order_id, $this->user->organization['Organization']['id'], $article_id)) {
                        $msg .= "<br />Articolo associato all'ordine ($order_id $article_id) non cancellato!";
                    }

                    /*
                     * cancello tutti gli eventuali acquisti (Carts), lo esegue gia' articles_orders_Trigger
                     * */
                    $Cart = new Cart;
                    $Cart->delete($this->user->organization['Organization']['id'], $order_id, $this->user->organization['Organization']['id'], $article_id);

                    /*
                     *  D E S
                     */
                    if ($this->user->organization['Organization']['hasDes'] == 'Y' && !empty($des_order_id)) {

                        $articles_orders_key = array('organization_id' => $this->user->organization['Organization']['id'],
                            'order_id' => $order_id,
                            'article_organization_id' => $this->user->organization['Organization']['id'],
                            'article_id' => $article_id);
                        $DesOrder = new DesOrder;
                        $DesOrder->deleteArticlesOrderAllOrganizations($this->user, $des_order_id, $articles_orders_key, $debug);
                    }
                } // end foreach

                /*
                 * ricalcolo SummaryOrders se esiste,
                 * degli articoli cancellati, cerco gli user_id e aggiorno
                 */
                if (!empty($article_ids)) {

                    App::import('Model', 'SummaryOrder');

                    $article_ids = substr($article_ids, 0, strlen($article_ids) - 1);
                    $sql = "SELECT user_id 
                            FROM " . Configure::read('DB.prefix') . "carts as Cart 
                            WHERE 
                                    Cart.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                                    and Cart.order_id = " . $order_id . " 
                                    and Cart.article_id in (" . $article_ids . ") 
                                    and Cart.deleteToReferent = 'N'
                            GROUP BY Cart.user_id 
                            ORDER BY Cart.user_id ";
                    if ($debug)
                        echo '<br />ArticlesOrder::index - cerco eventauli user_id per ricalcolo SummaryOrders se esiste ' . $sql;
                    try {
                        $usersSummaryOrdersResults = $this->ArticlesOrder->query($sql);

                        if (!empty($usersSummaryOrdersResults))
                            foreach ($usersSummaryOrdersResults as $usersSummaryOrdersResult) {
                                $user_id = $usersSummaryOrdersResult['Cart']['user_id'];
                                if ($debug)
                                    echo '<br />ArticlesOrder::index Tratto lo user ' . $user_id;

                                $SummaryOrder = new SummaryOrder;
                                $SummaryOrder->ricalcolaPerSingoloUtente($this->user, $order_id, $user_id, $debug);
                            }
                    } catch (Exception $e) {
                        CakeLog::write('error', $sql);
                        CakeLog::write('error', $e);
                    }
                } // if(!empty($article_ids))
            }  // end if(!empty($article_order_key_selected))

            if (!empty($msg))
                $this->Session->setFlash($msg);
            else
                $this->Session->setFlash(__('The articles order has been saved'));

            /*
             * aggiorno lo stato dell'ordine
             * 	da OPEN-NEXT o OPEN o CLOSE a eventualmente CREATE-INCOMPLETE
             * */
            $utilsCrons = new UtilsCrons(new View(null));
            $utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $this->order_id);
        } // end if ($this->request->is('post') || $this->request->is('put'))

        /*
         * articoli gia' associati all'ordine
         */
        $this->ArticlesOrder->unbindModel(array('belongsTo' => array('Cart', 'Order')));
        $options = array();
        $options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
            'ArticlesOrder.order_id' => $this->order_id,
            'Article.stato' => 'Y'
            /*
             * prendo anche quelli  "Presente tra gli articoli da ordinare"  a NO per gli articoli gia associati
             * 'Article.flag_presente_articlesorders' => 'Y'
             */
            );
        $options['order'] = 'Article.name';
        $options['recursive'] = 0;
        $results = $this->ArticlesOrder->find('all', $options);

        /*
         *  ctrl eventuali acquisti gia' fatti
         */
        App::import('Model', 'Cart');
        if($des_order_id==0) {
            foreach ($results as $numResult => $result) {
                $Cart = new Cart();
                $options = array();
                $options['conditions'] = array('Cart.organization_id' => $this->user->organization['Organization']['id'],
                                                'Cart.order_id' => $result['ArticlesOrder']['order_id'],
                                                'Cart.article_organization_id' => $result['ArticlesOrder']['article_organization_id'],
                                                'Cart.article_id' => $result['ArticlesOrder']['article_id'],
                                                'Cart.deleteToReferent' => 'N',
                );

                $options['order'] = '';
                $options['recursive'] = -1;
                $cartResults = $Cart->find('all', $options);
                /*
                echo "<pre>";
                print_r($options['conditions']);
                print_r($cartResults);
                echo "</pre>";
                */
                $results[$numResult]['Cart'] = $cartResults;
            }            
        }
         else {
            /*
            * se DES ctrl acquisti di TUTTI i GAS 
            */
			$options = array();
			$options['conditions'] = array('DesOrdersOrganization.des_id' => $this->user->des_id,
										   'DesOrdersOrganization.des_order_id' => $des_order_id);
			$options['recursive'] = -1;								
			$desOrdersOrganizationResults = $DesOrdersOrganization->find('all', $options);
			
			/*
			echo "<pre>DesOrdersOrganization::getDesOrdersOrganization() \n";
			print_r($options);
			print_r($desOrdersOrganizationResults);
			echo "</pre>";
			*/         
           
            foreach ($results as $numResult => $result) {
            
            	foreach($desOrdersOrganizationResults as $desOrdersOrganizationResult) {
            	
	                $Cart = new Cart();
	                $options = array();
	                $options['conditions'] = array('Cart.organization_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['organization_id'],
	                                                'Cart.order_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['order_id'],
	                                                'Cart.article_organization_id' => $result['ArticlesOrder']['article_organization_id'],
	                                                'Cart.article_id' => $result['ArticlesOrder']['article_id'],
	                                                'Cart.deleteToReferent' => 'N',
	                );
	
	                $options['order'] = '';
	                $options['recursive'] = -1;
	                $cartResults = $Cart->find('all', $options);
	                /*
	                echo "<pre>";
	                print_r($options['conditions']);
	                print_r($cartResults);
	                echo "</pre>";
	                */
	                /*
	                 * sovrascrivo i Cart di ogni GAS nel caso di + di 1 occorrenza
	                 * ma mi serve solo sapere se c'e' almeno un caso
	                 */
	                 if(!isset($results[$numResult]['Cart']) || empty($results[$numResult]['Cart']))
		                $results[$numResult]['Cart'] = $cartResults;
	                
	            } // loop DesOrdersOrganizationResult
            }  // loop Cart             
        }

        $this->set('results', $results);

        /*
         * * articoli ancora da associare
         */
        App::import('Model', 'Article');
        $Article = new Article();

        $Article->unbindModel(array('belongsTo' => array('SuppliersOrganization', 'CategoriesArticle')));

        $Article->unbindModel(array('hasOne' => array('ArticlesOrder')));
        $Article->unbindModel(array('hasMany' => array('ArticlesOrder')));
        $Article->unbindModel(array('hasAndBelongsToMany' => array('Order')));

        $Article->unbindModel(array('hasOne' => array('ArticlesArticlesType')));
        $Article->unbindModel(array('hasMany' => array('ArticlesArticlesType')));
        $Article->unbindModel(array('hasAndBelongsToMany' => array('ArticlesType')));

        $article_id_da_escludere = '';
        foreach ($results as $result)
            $article_id_da_escludere .= $result['Article']['id'] . ',';

        $article_id_da_escludere = substr($article_id_da_escludere, 0, strlen($article_id_da_escludere) - 1);

        $options = array();
        $options['conditions'] = array('Article.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Article.supplier_organization_id' => $this->order['SuppliersOrganization']['id'],
            'Article.stato' => 'Y',
            'Article.flag_presente_articlesorders' => 'Y');
			if(!empty($article_id_da_escludere))							   
				$options['conditions'] += array("NOT" => array( "Article.id" => split(',', $article_id_da_escludere)));

        $options['recursive'] = 0;
        $options['order'] = 'Article.name';
        $articles = $Article->find('all', $options);
        $this->set('articles', $articles);
    }

    /*
     * modifico i dati di un articolo associato all'ordine
     */

    public function admin_edit($order_id = 0, $article_organization_id = 0, $article_id = 0) {

        $debug = false;

        if (empty($order_id) || empty($article_organization_id) || empty($article_id)) {
            /*
             * dopo il submit passano come campi hidden
             */
            $order_id = $this->request->data['ArticlesOrder']['order_id'];
            $article_organization_id = $this->request->data['ArticlesOrder']['article_organization_id'];
            $article_id = $this->request->data['ArticlesOrder']['article_id'];
        }

        if (empty($order_id) || empty($article_organization_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if (!$this->ArticlesOrder->exists($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * DES
         */
        $des_order_id = 0;
        if ($this->user->organization['Organization']['hasDes'] == 'Y') {

            App::import('Model', 'DesOrdersOrganization');
            $DesOrdersOrganization = new DesOrdersOrganization();

            $desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
            if (!empty($desOrdersOrganizationResults)) {

                $des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];

                /*
                 * ctrl ACL
                 */
                $isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);
                if (!$isTitolareDesSupplier) {
                    $this->Session->setFlash(__('msg_not_permission'));
                    $this->myRedirect(Configure::read('routes_msg_stop'));
                }

                App::import('Model', 'DesOrder');
                $DesOrder = new DesOrder();

                $desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
                /*
                  echo "<pre>";
                  print_r($desOrdersResults);
                  echo "</pre>";
                 */
                $this->set(compact('desOrdersResults'));
            }
        } // DES
        $this->set(compact('des_order_id'));

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->request->data['ArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];

            /*
             * richiamo la validazione
             */
            $this->ArticlesOrder->set($this->request->data);
            if (!$this->ArticlesOrder->validates()) {

                $errors = $this->ArticlesOrder->validationErrors;
                $tmp = '';
                $flatErrors = Set::flatten($errors);
                if (count($errors) > 0) {
                    $tmp = '';
                    foreach ($flatErrors as $key => $value)
                        $tmp .= $value . ' - ';
                }
                $msg .= "Articolo non aggiornato: dati non validi, $tmp<br />";
                $this->Session->setFlash($msg);
            } else {

                $this->ArticlesOrder->create();
                if ($this->ArticlesOrder->save($this->request->data)) {
                    $this->Session->setFlash(__('The articles order edit single has been saved'));

                    /*
                     *  D E S
                     */
                    if ($this->user->organization['Organization']['hasDes'] == 'Y' && !empty($des_order_id)) {

                        $isTitolareDesSupplier = true;
                        $articles_orders_key = array('organization_id' => $this->request->data['ArticlesOrder']['organization_id'],
                            'order_id' => $order_id,
                            'article_organization_id' => $article_organization_id,
                            'article_id' => $article_id);
                        $DesOrder = new DesOrder;
                        $DesOrder->insertOrUpdateArticlesOrderAllOrganizations($this->user, $des_order_id, $this->order_id, $articles_orders_key, $isTitolareDesSupplier, $debug);
                    }

                    /*
                     * aggiorno ArticlesOrder.qta_cart e ArticlesOrder.qta_massima_order
                     */
                    $this->ArticlesOrder->aggiornaQtaCart_StatoQtaMax($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id);

                    if (!$debug)
                        $this->myRedirect(array('action' => 'index'));
                } else
                    $this->Session->setFlash(__('The article could not be saved. Please, try again.'));
            } // end if(!$this->ArticlesOrder->validates()) 
        } // if ($this->request->is('post') || $this->request->is('put')) 
        else {
            /*
             * aggiorno ArticlesOrder.qta_cart e ArticlesOrder.qta_massima_order
             */
            $this->ArticlesOrder->aggiornaQtaCart_StatoQtaMax($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id);
        }

        $options = array();
        $options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
            'ArticlesOrder.order_id' => $order_id,
            'ArticlesOrder.article_organization_id' => $article_organization_id,
            'ArticlesOrder.article_id' => $article_id);
        $options['recursive'] = -1;
        $this->request->data = $this->ArticlesOrder->find('first', $options);

        /*
         * Order
         * */
        App::import('Model', 'Order');
        $Order = new Order;

        $options = array();
        $options['conditions'] = array('Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.isVisibleBackOffice' => 'Y',
            'Order.id' => $order_id);
        $order = $Order->find('first', $options);

        $stato = ClassRegistry::init('ArticlesOrder')->enumOptions('stato');
        unset($stato['QTAMAXORDER']);
        $this->set(compact('stato'));

        $this->set(compact('order'));

        /*
         * dettaglio articolo
         */
        App::import('Model', 'Article');
        $Article = new Article;
        if (!$Article->exists($article_organization_id, $article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $options = array();
        $options['conditions'] = array('Article.id' => $article_id);
        $article = $Article->getArticlesDataAnagr($this->user, $options);
        $this->set('article', $article);

        /*
         * ctrl referentTesoriere
         */
        $this->set('isReferenteTesoriere', $this->isReferentTesoriere());
    }

    /*
     * filtro per Order per gestire gli articoli associati
     * 
     * $article_id valorizzato se arrivo da Article::admin_context_article_edit 
     * */

    public function admin_order_choice($article_id = 0) {

        $debug = false;

        $order_valido = true;

        /*
         * recupero dati
         * */
        if (isset($this->request->data['ArticlesOrder']['delivery_id']))
            $this->delivery_id = $this->request->data['ArticlesOrder']['delivery_id'];
        else
            $this->delivery_id = 0;
        if (isset($this->request->data['ArticlesOrder']['order_id']))
            $this->order_id = $this->request->data['ArticlesOrder']['order_id'];
        else
            $this->order_id = 0;

        if ($debug) {
            echo '<br />delivery_id ' . $this->delivery_id;
            echo '<br />order_id ' . $this->order_id;
        }
        $this->set('delivery_id', $this->delivery_id);
        $this->set('order_id', $this->order_id);

        if (!empty($this->delivery_id) && !empty($this->order_id)) {

            /*
             * cookies e session
             * */
            setcookie('delivery_id', $this->delivery_id, time() + 86400 * 365 * 1, Configure::read('App.server'));  // (86400 secs per day for 1 years)
            $this->Session->write('delivery_id', $this->delivery_id);

            setcookie('order_id', $this->order_id, time() + 86400 * 365 * 1, Configure::read('App.server'));
            $this->Session->write('order_id', $this->order_id);


            App::import('Model', 'Order');
            $Order = new Order;

            $Order->id = $this->order_id;
            if (!$Order->exists($this->user->organization['Organization']['id'])) {
                $this->Session->setFlash(__('msg_error_params'));
                $this->myRedirect(Configure::read('routes_msg_exclamation'));
            }

            $results = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
            $this->set('results', $results);

            if ($results['Order']['state_code'] == 'OPEN-NEXT' ||
                    $results['Order']['state_code'] == 'OPEN' ||
                    $results['Order']['state_code'] == 'RI-OPEN-VALIDATE' ||
                    $results['Order']['state_code'] == 'PROCESSED-BEFORE-DELIVERY' ||
                    $results['Order']['state_code'] == 'PROCESSED-POST-DELIVERY' ||
                    $results['Order']['state_code'] == 'PROCESSED-ON-DELIVERY' ||
                    $results['Order']['state_code'] == 'INCOMING-ORDER') {
                $order_valido = true;
            } else
                $order_valido = false;

            if ($order_valido) {
                unset($_REQUEST['_method']); // se no passava a index (_method, order_id)
                $this->myRedirect(array('controller' => 'ArticlesOrders', 'action' => 'index', 'id' => $this->order_id));
            }
        } // end if(!empty($this->delivery_id) && !empty($this->order_id))

        /*
         * se article_id e' valorizzato cerco se trovo almeno un ordine legato all'articolo
         */
        $order_id = 0;
        $delivery_id = 0;
        $conditions = array('Article.id' => $article_id);
        $resultsCtrl = $this->ArticlesOrder->getArticlesOrdersInOrder($this->user, $conditions);
        if (!empty($resultsCtrl)) {
            // prendo il primo order_id (potrei avere + ordini associati all'articolo)
            $order_id = $this->order_id = $resultsCtrl[0]['ArticlesOrder']['order_id'];

            App::import('Model', 'Order');
            $Order = new Order;

            $options = array();
            $options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
                'Order.id' => $this->order_id);
            $options['recursive'] = -1;
            $options['fields'] = array('delivery_id');
            $results = $Order->find('first', $options);
            $delivery_id = $this->delivery_id = $results['Order']['delivery_id'];
        }


        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $conditions = array('Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Delivery.isVisibleBackOffice' => 'Y',
            'Delivery.sys' => 'N',
            'Delivery.stato_elaborazione' => 'OPEN');
        if (!empty($delivery_id))
            $conditions += array('Delivery.id' => $this->delivery_id);
        $deliveries = $Delivery->find('list', array('fields' => array('id', 'luogoData'), 'conditions' => $conditions, 'order' => 'data ASC', 'recursive' => -1));
        if (empty($deliveries)) {
            $this->Session->setFlash(__('NotFoundDeliveries'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $this->set(compact('deliveries'));


        $ACLsuppliersIdsOrganization = $this->user->get('ACLsuppliersIdsOrganization');

        App::import('Model', 'Order');
        $Order = new Order;

        $conditions = array('Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.delivery_id' => $this->delivery_id,
            'Order.isVisibleBackOffice' => 'Y',
            'Order.supplier_organization_id IN (' . $ACLsuppliersIdsOrganization . ')',
        ); // 'DATE(Order.data_fine) >= CURDATE()'
        if (!empty($order_id))
            $conditions += array('Order.id' => $this->order_id);
        $results = $Order->find('all', array('conditions' => $conditions, 'order' => 'Order.data_inizio ASC', 'recursive' => 1));
        $orders = array();
        if (!empty($results))
            foreach ($results as $result) {

                if ($result['Order']['data_fine_validation'] != '0000-00-00')
                    $data_fine = $result['Order']['data_fine_validation_'];
                else
                    $data_fine = $result['Order']['data_fine_'];

                $orders[$result['Order']['id']] = $result['SuppliersOrganization']['name'] . ' - dal ' . $result['Order']['data_inizio_'] . ' al ' . $data_fine;
            }
        $this->set(compact('orders'));

        $this->set('order_valido', $order_valido);
    }

    /*
     *  estraggo gli articoli dell'ordine precedente per presentarli in admin_add
     */

    private function __getPreviousArticlesOrder($user, $order) {

        $previousResults = array();

        App::import('Model', 'Order');
        $Order = new Order;

        $options = array();
        $options['conditions'] = array('Delivery.organization_id' => (int) $user->organization['Organization']['id'],
            'Delivery.isVisibleBackOffice' => 'Y',
            'DATE(Delivery.data) < CURDATE()',
            'Order.isVisibleBackOffice' => 'Y',
            'Order.supplier_organization_id' => $order['supplier_organization_id']);
        //$options['fields'] = array('Delivery.id', 'Delivery.luogoData');
        $options['order'] = array('Delivery.data DESC');
        $results = $Order->find('first', $options);

        /*
          echo "<pre>";
          print_r($options);
          print_r($deliveries);
          echo "</pre>";
         */

        /*
         * c'e' un ordine precedente, estraggo gli articoli
         */
        if (!empty($results)) {
            App::import('Model', 'ArticlesOrder');
            $ArticlesOrder = new ArticlesOrder;

            $order_id = $results['Order']['id'];

            $conditions = array('Order.id' => (int) $order_id);
            $previousResults = $ArticlesOrder->getArticlesOrdersInOrder($this->user, $conditions);
        }
        /*
          echo "<pre>";
          print_r($previousResults);
          echo "</pre>";
         */

        return $previousResults;
    }

    /*
     *  all'ordine associo gli articoli dell'ordine precedente
     *  ridefinisco $this->request->data in 
     *  [ArticlesOrder] => Array ([article_id_selected] => id, id)
     * 	[Article] => Array ([article_id] => Array(
     *              [ArticlesOrderPrezzo] => 1,00
     *              [ArticlesOrderPezziConfezione] => 1
     *              [ArticlesOrderQtaMinima] => 1
     *              [ArticlesOrderQtaMassima] => 0
     *              [ArticlesOrderQtaMultipli] => 1
     *              [ArticlesOrderQtaMinimaOrder] => 0
     *              [ArticlesOrderQtaMassimaOrder] => 0)	 
     */

    private function __ridefinedDataToPreviousArticlesOrder($previousResults) {

        $data = array();
        $article_id_selected = '';
        foreach ($previousResults as $previousResult) {

            if ($previousResult['Article']['stato'] == 'Y') {

                $data['Article'][$previousResult['Article']['id']] = array(
                    'ArticlesOrderPrezzo' => $previousResult['ArticlesOrder']['prezzo'],
                    'ArticlesOrderPezziConfezione' => $previousResult['ArticlesOrder']['pezzi_confezione'],
                    'ArticlesOrderQtaMinima' => $previousResult['ArticlesOrder']['qta_minima'],
                    'ArticlesOrderQtaMassima' => $previousResult['ArticlesOrder']['qta_massima'],
                    'ArticlesOrderQtaMultipli' => $previousResult['ArticlesOrder']['qta_multipli'],
                    'ArticlesOrderQtaMinimaOrder' => $previousResult['ArticlesOrder']['qta_minima_order'],
                    'ArticlesOrderQtaMassimaOrder' => $previousResult['ArticlesOrder']['qta_massima_order'],
                );

                $article_id_selected .= $previousResult['Article']['id'] . ',';
            }
        }

        if (!empty($article_id_selected))
            $article_id_selected = substr($article_id_selected, 0, strlen($article_id_selected) - 1);

        $data['ArticlesOrder'] = array('article_id_selected' => $article_id_selected);

        /*
          echo "<pre>";
          print_r($data);
          echo "</pre>";
         */

        return $data;
    }

}
