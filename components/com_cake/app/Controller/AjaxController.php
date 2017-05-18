<?php

App::uses('AppController', 'Controller');

class AjaxController extends AppController {

    public $components = array('ActionsDesOrder');
    
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

        if ($debug)
            echo '<br />request_payment_id ' . $request_payment_id;
        if ($debug)
            echo '<br />user_id ' . $user_id;

        if ($user_id == null || $request_payment_id == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'RequestPayment');
        $RequestPayment = new RequestPayment;

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

        $results = $RequestPayment->userRequestPayment($this->user, $user_id, $request_payment_id, $doc_formato, $debug);
        $this->set('results', $results);

        $params = array('request_payment_id' => $request_payment_id);
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'request_payment', $params, $user_target = 'TESORIERE'));
        $this->set('organization_id', $this->user->organization['Organization']['id']);

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

        if ($debug)
            echo '<br />user_id ' . $user_id;
        if ($user_id == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->user->organization['Organization']['payToDelivery'] == 'POST' || $this->user->organization['Organization']['payToDelivery'] == 'ON-POST') {

            App::import('Model', 'SummaryPayment');
            $SummaryPayment = new SummaryPayment;

            $options = array();
            $options['conditions'] = array('SummaryPayment.organization_id' => (int) $this->user->organization['Organization']['id'],
                'SummaryPayment.user_id' => $user_id);
            $options['recursive'] = 1;
            $options['order'] = array('RequestPayment.num');
            $results = $SummaryPayment->find('all', $options);
            $this->set('results', $results);
            /*
              echo "<pre>";
              print_r($results);
              echo "</pre>";
             */
        }

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

        $this->__view_deliveries($delivery_id);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_deliveries');
    }

    public function admin_view_deliveries_small($delivery_id = 0) {

        $this->__view_deliveries($delivery_id);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_deliveries_small');
    }

    private function __view_deliveries($delivery_id) {
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
        if (!$Delivery->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $deliveryResults = $Delivery->read($this->user->organization['Organization']['id'], null, $delivery_id);
        if ($deliveryResults['Delivery']['isVisibleBackOffice'] == 'Y')
            $this->set('deliveryResults', $deliveryResults['Delivery']);
        else
            $this->set('deliveryResults', null);

        /*
         * estraggo gli ordini associati alla consegna
         */
        App::import('Model', 'Order');
        $Order = new Order;

        $Order->unbindModel(array('belongsTo' => array('Delivery')));
        $options = array();
        $options['conditions'] = array('Order.delivery_id' => $delivery_id,
            'Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.isVisibleBackOffice' => 'Y',
            'Order.state_code !=' => 'CREATE-INCOMPLETE');
        $options['recursive'] = 0;
        $options['order'] = array('Order.data_inizio', 'Order.data_fine');
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

            $options = array();
            $options['conditions'] = array('Supplier.id' => $result['SuppliersOrganization']['supplier_id']);
            $options['fields'] = array('img1');
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

        $this->set('results', $results);
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
        $this->set('results', $results);

        $SuppliersOrganizationsReferent->getSuppliersOrganizationIdsByReferent($this->user, $user_id);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_suppliers_organizations_referents');
    }

    public function admin_view_orders($order_id) {

        if (empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Order');
        $Order = new Order;

        $Order->id = $order_id;
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * estraggo 
         * dati ordine 
         * gli articoli associati all'ordine
         */
        $Order->hasAndBelongsToMany['Article'] = array(
            'className' => 'Article',
            'joinTable' => 'articles_orders',
            'foreignKey' => 'order_id',
            'associationForeignKey' => 'article_id',
            'unique' => 'keepExisting',
            'conditions' => 'Article.organization_id = ArticlesOrder.article_organization_id AND Article.stato = \'Y\' AND ArticlesOrder.stato != \'N\' AND ArticlesOrder.organization_id = ' . $this->user->organization['Organization']['id'],
            'with' => 'ArticlesOrder');

        $options = array();
        $options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
            'Order.id' => $order_id);
        $options['recursive'] = 1;
        $results = $Order->find('first', $options);
        $this->set('results', $results);

        /*
         * creo il link articlesOrder/admin_index
         */
        if ($results['Delivery']['isVisibleBackOffice'] == 'N' || $results['Order']['isVisibleBackOffice'] == 'N') {
            $actionToEditOrder = array();
            $actionToEditArticle = array();
        } else {
            $actionToEditOrder = $this->actionToEditOrder($this->user, $results);
            $actionToEditArticle = $this->actionToEditArticle($this->user, $results);
        }
        $this->set('actionToEditOrder', $actionToEditOrder);
        $this->set('actionToEditArticle', $actionToEditArticle);

        /*
         * estraggo i referenti 
         */
        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

        $supplier_organization_id = $results['Order']['supplier_organization_id'];
        $conditions = array('User.block' => 0,
            'SuppliersOrganization.id' => $supplier_organization_id);
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
        if (!$SuppliersOrganization->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * ordini associati al produttore
         */
        $options = array();
        $options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
            'SuppliersOrganization.id' => $id);
        $options['order'] = array('Delivery.data', 'Order.data_inizio');
        $options['recursive'] = 1;
        $results = $SuppliersOrganization->Order->find('all', $options);

        $this->set('results', $results);
        $this->set('supplier_organization_id', $id);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_suppliers_organizations');
    }

    // call Supplier::add_index
    public function admin_view_suppliers($id = null) {
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

        $options = array();
        $options['conditions'] = array('Supplier.id' => $id);
        $options['recursive'] = 1;
        $results = $Supplier->find('first', $options);

        if (isset($results['SuppliersOrganization']))
            foreach ($results['SuppliersOrganization'] as $numResult => $suppliersOrganization) {

                /*
                 * dati  associati al produttore: Organization, Article, Referents
                 */
                $SuppliersOrganization = new SuppliersOrganization;
                $SuppliersOrganization->unbindModel(array('belongsTo' => array('Supplier', 'CategoriesSupplier')));
                $SuppliersOrganization->unbindModel(array('hasMany' => array('Order')));

                $options = array();
                $options['conditions'] = array('SuppliersOrganization.organization_id' => $suppliersOrganization['organization_id'],
							                    'SuppliersOrganization.id' => $suppliersOrganization['id'],
							                    'Organization.stato' => 'Y');
                $options['recursive'] = 1;
                $suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);

                /*
                 * per i test posso avere dati sporchi: articoli e produttori senza Organization
                 */
                if (empty($suppliersOrganizationResults))
                    unset($results['SuppliersOrganization'][$numResult]);
                else {
					
					$results['SuppliersOrganization'][$numResult] = $suppliersOrganizationResults;
					
					/*
					 * per ogni produttore faccio la media dei voti
					 */	
					$options = array();
					$options['conditions'] = array('SuppliersVote.organization_id' => $suppliersOrganization['organization_id'],
												   'SuppliersVote.supplier_id' => $results['Supplier']['id']);
					$options['recursive'] = -1;
					$SuppliersVote = new SuppliersVote;
					
					$suppliersVoteResults = $SuppliersVote->find('first', $options);
					$results['SuppliersOrganization'][$numResult]['SuppliersVote'] = $suppliersVoteResults['SuppliersVote'];					
				}
                    
            }
		
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
        $this->set('results', $results);
        $this->set('supplier_id', $id);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_suppliers');
    }

    public function admin_view_articles($article_id = 0) {
        $this->view_articles($article_id);
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

        $Article->unbindModel(array('hasOne' => array('ArticlesOrder', 'ArticlesArticlesType')));
        $Article->unbindModel(array('hasMany' => array('ArticlesOrder', 'ArticlesArticlesType')));
        $Article->unbindModel(array('hasAndBelongsToMany' => array('Order', 'ArticlesType')));

        $options = array();
        $options['conditions'] = array('Article.organization_id' => $article_organization_id,
            'Article.id' => $article_id);
        $options['recursive'] = -1;
        $options['fields'] = array('stato');
        $results = $Article->find('first', $options);
        $article_stato = $results['Article']['stato'];
        $this->set(compact('article_stato'));

        if ($article_stato == 'Y') {
            App::import('Model', 'Delivery');
            $Delivery = new Delivery;

            $options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
                'articlesOrdersInOrderAndCartsAllUsers' => true, // estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
                'suppliers' => false, 'referents' => false);

            $conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
                    'Delivery.stato_elaborazione' => 'OPEN'),
                'Cart' => array('Cart.deleteToReferent' => 'N'));

            if (!empty($order_id))
                $conditions += array('ArticlesOrder' => array('ArticlesOrder.order_id' => $order_id,
                        'ArticlesOrder.article_organization_id' => $article_organization_id,
                        'ArticlesOrder.article_id' => $article_id));
            else
                $conditions += array('ArticlesOrder' => array('ArticlesOrder.article_organization_id' => $article_organization_id,
                        'ArticlesOrder.article_id' => $article_id));

            $orderBy = array('User' => Configure::read('orderUser'));

            $results = $Delivery->getDataTabs($this->user, $conditions, $options, $orderBy);
            $this->set('results', $results);

            /*
             * ctrl configurazione Organization
             */
            $storeroomResults = array();
            $organization = $this->user->get('Organization');
            if ($this->user->organization['Organization']['hasStoreroom'] == 'Y') {

                /*
                 * Articolo in DISPENSA acquistato aggregati per delivery
                 * */
                App::import('Model', 'Storeroom');
                $Storeroom = new Storeroom;

                $conditions = array('Delivery.id' => '> 0', // se dispensa e' 0
                    'Articles.organization_id' => $article_organization_id,
                    'Article.id' => $article_id);
                $orderBy = array('Delivery' => 'Delivery.data,Delivery.id, ' . Configure::read('orderUser') . ', User.id');

                $storeroomDeliveryResults = $Storeroom->getArticlesToStoreroom($this->user, $conditions, $orderBy);

                /*
                 * Articolo in DISPENSA non ancora acquistato
                 * */
                App::import('Model', 'Storeroom');
                $Storeroom = new Storeroom;

                $conditions = array('Delivery.id' => '0', // se dispensa e' 0,
                    'Articles.organization_id' => $article_organization_id,
                    'Article.id' => $article_id);
                $orderBy = array('Delivery' => 'Delivery.data,Delivery.id, ' . Configure::read('orderUser') . ', User.id');

                $storeroomResults = $Storeroom->getArticlesToStoreroom($this->user, $conditions, $orderBy);
            } // if($this->user->organization['Organization']['hasStoreroom']=='Y')

            $this->set(compact('storeroomResults', 'storeroomDeliveryResults'));
        } // end if($article_stato=='Y')

        $this->layout = 'ajax';
        $this->render('/Ajax/view_article_carts');
    }

    /*
     * dettaglio di acquisti di un articolo in un preciso ordine e consegna
     * 		in validation_carts
     * ArticlesOrder.key = $order_id, $article_organization_id, $article_id
     */

    public function admin_view_articles_order_carts($key) {

        $debug = false;
        
        $results = array();

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
		
       /*
         *  ctrl eventuali acquisti gia' fatti
         */      
        if($des_order_id==0 || !$isReferentDesAllGasDesSupplier) {
	        $Cart = new Cart();
	
	        $options = array();
	        $options['conditions'] = array('Cart.organization_id' => $this->user->organization['Organization']['id'],
	        								'Cart.order_id' => $order_id,
	                                        'Cart.article_organization_id' => $article_organization_id,
	                                        'Cart.article_id' => $article_id,
	                                        'Cart.deleteToReferent' => 'N',
	        );
	        $options['recursive'] = 1;
	        $options['order'] = array(Configure::read('orderUser'));
	        $results = $Cart->find('all', $options);	                
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
			       
            $i=0;
        	foreach($desOrdersOrganizationResults as $desOrdersOrganizationResult) {
        	
                $Cart = new Cart();
                $options = array();
                $options['conditions'] = array('Cart.organization_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['organization_id'],
                                                'Cart.order_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['order_id'],
                                                'Cart.article_organization_id' => $article_organization_id,
                                                'Cart.article_id' => $article_id,
                                                'Cart.deleteToReferent' => 'N',
                );
	
                $options['order'] = '';
                $options['recursive'] = 1;
                $cartResults = $Cart->find('all', $options);
                /*
                echo "<pre>";
                print_r($options['conditions']);
                print_r($cartResults);
                echo "</pre>";
	            */    
	            if(!empty($cartResults)) {
	                $results[$i] = current($cartResults);
					$i++;
				}
				
            } // loop DesOrdersOrganizationResult
        }
            
        $this->set('results', $results);
        /*
        echo "<pre>";
        print_r($results);
        echo "</pre>";
        */
        $this->layout = 'ajax';
        $this->render('/Ajax/view_articles_order_carts');
    }

    /*
     * dettaglio di acquisti di un articolo in un preciso ordine e consegna
     * codice = a admin_view_articles_order_carts 
     * BackupArticlesOrder.key = $order_id, $article_organization_id, $article_id
     */

    public function admin_view_backup_articles_order_carts($key) {

        $results = array();

        if (empty($key) && strpos($key, '_') !== false) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        list($order_id, $article_organization_id, $article_id) = explode('_', $key);

        App::import('Model', 'BackupArticlesOrder');
        $BackupArticlesOrder = new BackupArticlesOrder();

        App::import('Model', 'BackupCart');
        $BackupCart = new BackupCart();

        $options = array();
        $options['conditions'] = array('BackupCart.organization_id' => $this->user->organization['Organization']['id'],
            'BackupCart.order_id' => $order_id,
            'BackupCart.article_organization_id' => $article_organization_id,
            'BackupCart.article_id' => $article_id,
            'BackupCart.deleteToReferent' => 'N',
        );
        $options['recursive'] = 1;
        $options['order'] = array(Configure::read('orderUser'));
        $results = $BackupCart->find('all', $options);
        $this->set('results', $results);

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
        
        $options = array();
        $options['conditions'] = array('CashesHistory.organization_id' => (int) $this->user->organization['Organization']['id'],
							            'CashesHistory.cash_id' => $cash_id);
		$options['order'] =	array('CashesHistory.id asc'); // per created no perche' e' sempre = 
		 $options['recursive'] = 0;
        $results = $CashesHistory->find('all', $options);
		if($debug) {
			echo "<pre>";
			print_r($results);
			echo "<pre>";
		}

		/*
		 * aggiungo ultimo movimento
		 */
        App::import('Model', 'Cash');
        $Cash = new Cash;
        
        $options = array();
        $options['conditions'] = array('Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
							            'Cash.id' => $cash_id);
		$options['recursive'] =	-1; 
        $cashResults = $Cash->find('first', $options);
		
		if(!empty($cashResults))	
			$results[(count($results))]['CashesHistory'] = $cashResults['Cash'];	

		
		/*
		 * calcolo dai saldi alle operazioni
		 */
        foreach($results as $numResult => $result) {
			if($numResult>0) {
				$importo_old = $results[$numResult-1]['CashesHistory']['importo'];
				$importo = $results[$numResult]['CashesHistory']['importo'];
				
				$operazione = (-1*($importo_old - $importo));
				$results[$numResult-1]['CashesHistory']['operazione'] = $operazione;
				$results[$numResult-1]['CashesHistory']['operazione_'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$results[$numResult-1]['CashesHistory']['operazione_e'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
				
				$results[$numResult-1]['CashesHistory']['nota'] = $results[$numResult-1]['CashesHistory']['nota'];
				$results[$numResult-1]['CashesHistory']['created'] = $results[$numResult-1]['CashesHistory']['created'];
			}	
		}
	
		$results[$numResult]['CashesHistory']['operazione'] = '';
		$results[$numResult]['CashesHistory']['operazione_'] = '';
		$results[$numResult]['CashesHistory']['operazione_e'] = '';		

		/*
		 * aggiungo la prima riga con partenza saldo a 0
		 * porto nota / modified all'occorrenza dell'array precedente
		 */
		$newResults = array(); 
		if(!empty($results)) { 		
			$newResults[0]['Cash']['importo'] = '0';
			$newResults[0]['Cash']['importo_'] = '0,00';
			$newResults[0]['Cash']['importo_e'] = '0,00 &euro;';
			$operazione = (-1*(0 - $results[0]['CashesHistory']['importo']));
			

			$newResults[0]['CashesHistory']['importo'] = '0';
			$newResults[0]['CashesHistory']['importo_'] = '0,00';
			$newResults[0]['CashesHistory']['importo_e'] = '0,00 &euro;';

			$newResults[0]['CashesHistory']['nota'] = $results[0]['CashesHistory']['nota'];
			$newResults[0]['CashesHistory']['modified'] = $results[0]['CashesHistory']['modified'];
				
			$newResults[0]['CashesHistory']['operazione'] = $operazione;
			$newResults[0]['CashesHistory']['operazione_'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$newResults[0]['CashesHistory']['operazione_e'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
		
			foreach($results as $numResult => $result) {
				$newResults[$numResult+1] = $result;
				
				if(isset($results[$numResult+1])) {
					$newResults[$numResult+1]['CashesHistory']['nota'] = $results[$numResult+1]['CashesHistory']['nota'];
					$newResults[$numResult+1]['CashesHistory']['modified'] = $results[$numResult+1]['CashesHistory']['modified'];
				}
				else {
					$newResults[$numResult+1]['CashesHistory']['nota'] = "";					
				}
			}
		}
	
		if($debug) {
			echo "<pre>";
			print_r($newResults);
			echo "<pre>";
		}
		
        $this->set('results', $newResults);
		 
        $this->layout = 'ajax';
        $this->render('/Ajax/admin_view_cashes_histories');
    }
	
	/*
	 * history Cash da FE, vado per cacs_id
	 */	
    public function view_cashes_histories() {

		$user_id = $this->user->id;

        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'CashesHistory');
        $CashesHistory = new CashesHistory;
        
        $options = array();
        $options['conditions'] = array('CashesHistory.organization_id' => (int) $this->user->organization['Organization']['id'],
							            'CashesHistory.user_id' => $user_id);
		$options['order'] =	array('CashesHistory.id asc'); // per created no perche' e' sempre = 
        $results = $CashesHistory->find('all', $options);
		
		/*
		 * aggiungo ultimo movimento
		 */
        App::import('Model', 'Cash');
        $Cash = new Cash;
        
        $options = array();
        $options['conditions'] = array('Cash.organization_id' => (int) $this->user->organization['Organization']['id'],
							            'Cash.user_id' => $user_id);
		$options['recursive'] =	-1; 
        $cashResults = $Cash->find('first', $options);
		
		if(!empty($cashResults))	
			$results[(count($results))]['CashesHistory'] = $cashResults['Cash'];	

		
		/*
		 * calcolo dai saldi alle operazioni
		 */
        foreach($results as $numResult => $result) {
			if($numResult>0) {
				$importo_old = $results[$numResult-1]['CashesHistory']['importo'];
				$importo = $results[$numResult]['CashesHistory']['importo'];
				
				$operazione = (-1*($importo_old - $importo));
				$results[$numResult-1]['CashesHistory']['operazione'] = $operazione;
				$results[$numResult-1]['CashesHistory']['operazione_'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$results[$numResult-1]['CashesHistory']['operazione_e'] = number_format($operazione,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
			}	
		}
	
		$results[$numResult]['CashesHistory']['operazione'] = '';
		$results[$numResult]['CashesHistory']['operazione_'] = '';
		$results[$numResult]['CashesHistory']['operazione_e'] = '';		
						
		/*
		  echo "<pre>";
		  print_r($results);
		  echo "</pre>";
		*/
        $this->set('results', $results);
		 
        $this->layout = 'ajax';
        $this->render('/Ajax/view_cashes_histories');
    }
    
    public function view_articles($article_id = 0) {
        if (empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'Article');
        $Article = new Article;
        if (!$Article->exists($this->user->organization['Organization']['id'], $article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $options = array();
        $options['conditions'] = array('Article.id' => $article_id);
        $results = $Article->getArticlesDataAnagr($this->user, $options);
        $this->set('results', $results);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_articles');
    }

    public function admin_view_articles_order($key, $evidenzia = '') {
        $this->__view_articles_order($key, $evidenzia);
    }

    /*
     * Organization.type = 'GAS'
     * dettaglio di un articolo associato ad un ordine
     */

    public function view_articles_order($key, $evidenzia = '') {
        $this->__view_articles_order($key, $evidenzia);
    }

    public function view_articles_order_no_img($key) {
        $this->__view_articles_order($key);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_articles_order_no_img');
    }

    /*
     * Organization.type = 'PROD'
     * dettaglio di un articolo associato ad una consegna
     */

    public function admin_view_prod_deliveries_articles($key, $evidenzia = '') {
        $this->__view_prod_deliveries_articles($key, $evidenzia);
    }

    public function view_prod_deliveries_articles($key, $evidenzia = '') {
        $this->__view_prod_deliveries_articles($key, $evidenzia);
    }

    public function view_prod_deliveries_articles_no_img($key) {
        $this->__view_prod_deliveries_articles($key);

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

        $options = array();
        $options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
            'Order.id' => $order_id);
        $options['recursive'] = -1;
        $results = $Order->find('first', $options);

        $delivery_id = $results['Order']['delivery_id'];

        $this->set('results', $results);
        $this->set('delivery_id', $delivery_id);
        $this->set('order_id', $order_id);

        $this->layout = 'ajax';
    }

    public function admin_view_orders_tesoriere($order_id) {

        App::import('Model', 'Order');
        $Order = new Order();

        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

        $options = array();
        $options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
            'Order.id' => $order_id);
        $options['recursive'] = 1;
        $results = $Order->find('first', $options);

        /*
         * Referents
         */
        $conditions = array('User.block' => 0,
            'SuppliersOrganization.id' => $results['Order']['supplier_organization_id']);
        $suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($this->user, $conditions);

        if (!empty($suppliersOrganizationsReferent))
            $results['Order']['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent;

        $this->set('results', $results);

        /*
         * supplier
         */
        App::import('Model', 'Supplier');
        $Supplier = new Supplier();

        $options = array();
        $options['conditions'] = array('Supplier.id' => $results['SuppliersOrganization']['supplier_id']);
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

        $ProdDeliveriesArticle->unbindModel(array('belongsTo' => array('ProdDelivery', 'ProdCart')));

        $options = array();
        $options['conditions'] = array('ProdDeliveriesArticle.organization_id' => $this->user->organization['Organization']['id'],
            'ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id,
            ''
        );
        $options['recursive'] = 1;
        $options['order'] = 'Article.name';
        $results = $ProdDeliveriesArticle->find('all', $options);
        $this->set('results', $results);

        $this->layout = 'ajax';
        $this->render('/Ajax/view_prod_deliveries');
    }

    /*
     * Organization.type = 'GAS'
     * dettaglio di un articolo associato ad un ordine
     * ArticlesOrder.key = $order_id_$article_organization_id_$article_id
     */

    private function __view_articles_order($key, $evidenzia = '') {

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
        $this->set('results', $results);

        /*
         * ArticlesType, qui lo calcolo runtime, se no e' il valore di Article.bio
         */
        if (!empty($results)) {
            App::import('Model', 'ArticlesArticlesType');
            $ArticlesArticlesType = new ArticlesArticlesType;
            $resultsArticlesTypes = $ArticlesArticlesType->getArticlesArticlesTypes($this->user, $results['Article']['id']);

            if (!empty($resultsArticlesTypes)) {
                foreach ($resultsArticlesTypes as $resultsArticlesType) {
                    $tmp[] = $resultsArticlesType['ArticlesType'];
                }
                $results['ArticlesType'] = $tmp;
            } else
                $results['ArticlesType'] = array();
        }

        $this->set('results', $results);
        $this->set('evidenzia', $evidenzia);

        /*
         * articolo non del GAS
         */
        if ($this->user->organization['Organization']['id'] != $article_organization_id) {
            App::import('Model', 'Organization');
            $Organization = new Organization;

            $options = array();
            $options['conditions'] = array('Organization.id' => (int) $article_organization_id);
            $options['recursive'] = -1;
            $organizationOtherResults = $Organization->find('first', $options);
            $this->set('organizationOtherResults', $organizationOtherResults);
        }

        $this->layout = 'ajax';
        $this->render('/Ajax/view_articles_order');
    }

    /*
     * Organization.type = 'PROD'
     * dettaglio di un articolo associato ad una consegna
     * ProdDeliveryArticle.key = $prod_delivery_id_$article_organization_id_$article_id
     */

    private function __view_prod_deliveries_articles($key, $evidenzia = '') {

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
        $this->set('results', $results);

        /*
         * ArticlesType, qui lo calcolo runtime, se no e' il valore di Article.bio
         */
        if (!empty($results)) {
            App::import('Model', 'ArticlesArticlesType');
            $ArticlesArticlesType = new ArticlesArticlesType;
            $resultsArticlesTypes = $ArticlesArticlesType->getArticlesArticlesTypes($this->user, $results['Article']['id']);

            if (!empty($resultsArticlesTypes)) {
                foreach ($resultsArticlesTypes as $resultsArticlesType) {
                    $tmp[] = $resultsArticlesType['ArticlesType'];
                }
                $results['ArticlesType'] = $tmp;
            } else
                $results['ArticlesType'] = array();
        }

        $this->set('results', $results);
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
        if (!$RequestPayment->exists($this->user->organization['Organization']['id'])) {
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
        $this->set('results', $results);

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

        $options = array();
        $options['conditions'] = array('OrdersAction.id' => $order_action_id);
        $results = $OrdersAction->find('first', $options);

        $this->set('results', $results);

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

        $option = array();
        $option['conditions'] = array('Supplier.j_content_id' => $j_content_id);
        $option['recursive'] = 1;
        $results = $Supplier->find('first', $option);

        /*
         * estraggo le organization
         */
        if (isset($results['SuppliersOrganization']))
            foreach ($results['SuppliersOrganization'] as $numResult => $result) {

                $organization_id = $result['organization_id'];

                $Organization = new Organization;

                $options = array();
                $options['conditions'] = array('Organization.id' => $organization_id);
                $options['recursive'] = -1;

                $organizationsResults = $Organization->find('first', $options);

                $results['Organization'][$numResult] = $organizationsResults['Organization'];

                unset($results['SuppliersOrganization'][$numResult]);
            }
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */

        $this->set('results', $results);

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

        $option = array();
        $option['conditions'] = array('Supplier.j_content_id' => $j_content_id);
        $option['recursive'] = 1;
        $results = $Supplier->find('first', $option);

        /*
         * estraggo gli articoli del primo GAS
		 * se non li trovo passo al successivo: se DES potrebbe non averli
         */
        if (isset($results['SuppliersOrganization'][0]['organization_id']) &&
                isset($results['SuppliersOrganization'][0]['id'])) {

			foreach($results['SuppliersOrganization'] as $suppliersOrganization) {
			
					$articlesResults = array();
					$organization_id = $suppliersOrganization['organization_id'];
					$supplier_organization_id = $suppliersOrganization['id'];

					$options = array();
					$options['conditions'] = array('Article.organization_id' => $organization_id,
													'Article.supplier_organization_id' => $supplier_organization_id,
													'Article.stato' => 'Y');
					$options['recursive'] = -1;

					$articlesResults = $Article->find('all', $options);
					if(!empty($articlesResults)) {
						$results['Article'] = $articlesResults;
						break;
					}
					
			} // foreach($results['SuppliersOrganization'] as $suppliersOrganization)
        }
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
        $this->set('results', $results);

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

        $SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization')));
        $SuppliersOrganization->unbindModel(array('hasMany' => array('Article')));

        $option = array();
        $option['conditions'] = array('SuppliersOrganization.organization_id' => $organization_id,
            'Supplier.j_content_id' => $j_content_id);
        $option['recursive'] = 2;
        $results = $SuppliersOrganization->find('first', $option);

        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */

        $this->set('results', $results);
        $this->set('organization_id', $organization_id);

        $this->layout = 'ajax';
        $this->render('/Ajax/modules_suppliers_organization_details');
    }

    public function admin_autoCompleteDeliveries_luogo($format = 'notmpl', $q) {

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $tmpResults = $Delivery->find('all', array('conditions' => array('Delivery.luogo LIKE' => '%' . $q . '%',
                'Delivery.organization_id' => (int) $this->user->organization['Organization']['id']),
            'fields' => 'DISTINCT luogo', 'recursive' => -1));

        $results = array();
        foreach ($tmpResults as $key => $value)
            foreach ($value as $key1 => $value1)
                foreach ($value1 as $key2 => $value2)
                    $results[] = $value2;

        $this->set('results', $results);

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_deliveries_luogo');
    }

    public function admin_autoCompleteUsers_username($format = 'notmpl', $q) {
        App::import('Model', 'User');
        $User = new User;

        $conditions = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
            /* 'User.block'=> 0, */
            'lower(User.username) LIKE' => '%' . strtolower(addslashes($q)) . '%');

        $this->set('results', $User->find('all', array('conditions' => $conditions, 'fields' => array('username'))));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_users_username');
    }

    public function admin_autoCompleteUsers_name($format = 'notmpl', $q) {
        App::import('Model', 'User');
        $User = new User;

        $conditions = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
            /* 'User.block'=> 0, */
            'lower(User.name) LIKE' => '%' . strtolower(addslashes($q)) . '%');

        $this->set('results', $User->find('all', array('conditions' => $conditions, 'fields' => array('name'))));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_users_name');
    }

    public function admin_autoCompleteRequestPayment_name($format = 'notmpl', $q) {
        App::import('Model', 'User');
        $User = new User;

        $conditions = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
            /* 'User.block'=> 0, */
            'lower(User.name) LIKE' => '%' . strtolower(addslashes($q)) . '%');

        $this->set('results', $User->find('all', array('conditions' => $conditions, 'fields' => array('name'))));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_users_name');
    }

    public function admin_autoCompleteContextArticlesArticles_name($format = 'notmpl', $q) {
        App::import('Model', 'Article');
        $Article = new Article;

        $options = array();
        $options['conditions'] = array('lower(Article.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
            'Article.organization_id' => $this->user->organization['Organization']['id'],
            'Article.supplier_organization_id IN (' . $this->user->get('ACLsuppliersIdsOrganization') . ')');
        $options['fields'] = array('name');
        $options['recursive'] = -1;
        $results = $Article->find('all', $options);

        $this->set('results', $results);
        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_article_name');
    }

    public function admin_autoCompleteContextOrderArticles_name($supplier_organization_id, $format = 'notmpl', $q) {
        App::import('Model', 'Article');
        $Article = new Article;


        $options = array();
        $options['conditions'] = array('lower(Article.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
            'Article.organization_id' => $this->user->organization['Organization']['id'],
            'Article.supplier_organization_id' => $supplier_organization_id);
        $options['fields'] = array('name');
        $options['recursive'] = -1;
        $results = $Article->find('all', $options);

        $this->set('results', $results);
        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_article_name');
    }

    /*
     * filtro di ricerca del ecomm front-end
     */

    public function autoCompleteArticlesName($supplier_organization_id, $format = 'notmpl', $q) {
        App::import('Model', 'Article');
        $Article = new Article;

        $options = array();
        $options['conditions'] = array('lower(Article.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
            'Article.supplier_organization_id' => $supplier_organization_id,
            'Article.stato' => 'Y');
        $options['fields'] = array('name');
        $options['recursive'] = -1;
        $results = $Article->find('all', $options);

        $this->set('results', $results);
        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_article_name');
    }

    public function admin_autoCompleteSuppliers_name($format = 'notmpl', $q) {
        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

        $options = array();
        $options['conditions'] = array('lower(Supplier.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
            'Supplier.stato != ' => 'N');
        $options['fields'] = array('name');
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

        $options = array();
        $options['conditions'] = array('lower(Supplier.name) LIKE' => '%' . strtolower(addslashes($q)) . '%');
        $options['fields'] = array('name');
        $options['recursive'] = -1;
        $this->set('results', $Supplier->find('all', $options));

        $this->layout = 'ajax';
        $this->render('/Ajax/autocomplete_suppliers_name');
    }

    public function admin_autoCompleteSuppliersOrganizations_name($format = 'notmpl', $q) {
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $this->set('results', $SuppliersOrganization->find('all', array('conditions' => array('lower(SuppliersOrganization.name) LIKE' => '%' . strtolower(addslashes($q)) . '%',
                        'SuppliersOrganization.organization_id' => (int) $this->user->organization['Organization']['id']),
                    'fields' => array('name'))));

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
    public function admin_suppliersOrganizationDetails($supplier_organization_id=0, $des_order_id=0, $format='notmpl') {

		$debug = false;
		
        $results = array();

        if (!empty($supplier_organization_id)) {
            App::import('Model', 'SuppliersOrganization');
            $SuppliersOrganization = new SuppliersOrganization;
            
            $SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization', 'CategoriesSupplier')));

            $options = array();
            $options['conditions'] = array('SuppliersOrganization.id' => $supplier_organization_id,
                'SuppliersOrganization.organization_id' => (int) $this->user->organization['Organization']['id']);
            $options['recursive'] = 0;
            $results = $SuppliersOrganization->find('first', $options);
			/*
              echo "<pre>";
              print_r($results);
              echo "</pre>";
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
		            
		            if($debug) echo '<br />Inizio ctrl se produttore DES';
		            
		            App::import('Model', 'DesSupplier');
		            $DesSupplier = new DesSupplier;

					/*
					 * non ho scelto il DES
					 */ 
					if(empty($this->user->des_id))	{
							
			            if($debug) echo '<br />Non ho ancora scelto il mio DES';
			            
						/*
						 * se e' associato ad un solo DES lo ricavo
						 */
			            App::import('Model', 'DesOrganization');
			            $DesOrganization = new DesOrganization;

			            $options = array();
						$options['conditions'] = array('DesOrganization.organization_id' => $this->user->organization['Organization']['id']); // non ho scelto il DES, ctrl solo se il suo GAS e' titolare
							   		                   					   		                   
			            $options['fields'] = array('DesOrganization.des_id');
			            $options['recursive'] = -1;
			            $desOrganizationResults = $DesOrganization->find('all', $options);
						
						if(count($desOrganizationResults)==1) {
							/*
							 * e' associato a 1 solo DES
							 * non capita + perche' in AppController cerco se ne ha solo 1
							 */								
							$this->user->des_id = $desOrganizationResults[0]['DesOrganization']['des_id'];
							if($debug) echo '<br />il GAS e\' associato ad un solo DES => ricavo des_id '.$this->user->des_id;
							
							$this->__addParamsDesJUser($user);
						}
						else {
							/*
							 * non ho scelto il DES ma e' associato a + DES, ctrl solo se il suo GAS e' titolare
							 */						
							$options = array();
			            	$options['conditions'] = array('DesSupplier.supplier_id' => $results['Supplier']['id'],
		            								       'DesSupplier.own_organization_id' => $this->user->organization['Organization']['id']);
				            $options['recursive'] = -1;
				            $desSupplierResults = $DesSupplier->find('first', $options);
							if(!empty($desSupplierResults)) {
								$isOwnGasTitolareDes = true;							
								if($debug) echo '<br />il GAS e\' associato ad + DES => NON posso ricavare des_id ma il suo GAS e\' titolare del produttore';
							}
							else {
								if($debug) echo '<br />il GAS e\' associato ad + DES => NON posso ricavare des_id ma il suo GAS NON e\' titolare del produttore';
							
							}
							
						}
					}
					
					
					if(!empty($this->user->des_id))	{
					
						if($debug) echo '<br />Ho già scelto il mio DES, des_id '.$this->user->des_id;
								
						/*
						 * ho scelto il DES
						 */ 					
			            $options = array();
			            $options['conditions'] = array('DesSupplier.supplier_id' => $results['Supplier']['id'],
			            						       'DesSupplier.des_id' => $this->user->des_id);			   		                   
			            $options['fields'] = array('DesSupplier.id');
			            $options['recursive'] = -1;
			            $desSupplierResults = $DesSupplier->find('first', $options);

						if(!empty($desSupplierResults)) {
						
							if($debug) echo '<br />Il produttore fa parte dei produttori DES';
						
							/*
							 * ctrl se lo user e' associato al produttore come REFERENTE DES o TITOLARE DES 
							 */
							App::import('Model', 'DesSuppliersReferent');
							$DesSuppliersReferent = new DesSuppliersReferent;
					
							$DesSuppliersReferent->unbindModel(array('belongsTo' => array('De', 'User')));
							
							$options = array();
							$options['conditions'] = array('DesSuppliersReferent.des_id' => $this->user->des_id,
														   'DesSuppliersReferent.organization_id' => $this->user->organization['Organization']['id'],
														   'DesSuppliersReferent.user_id' => $this->user->get('id'),
														   'DesSupplier.des_id' => $this->user->des_id,
														   'DesSupplier.id' => $desSupplierResults['DesSupplier']['id']);
							$options['recursive'] = 1;
							$ACLDesSuppliersResults = $DesSuppliersReferent->find('first', $options);
							
							if(!empty($ACLDesSuppliersResults)) {
								if($ACLDesSuppliersResults['DesSuppliersReferent']['group_id'] == Configure::read('group_id_titolare_des_supplier')) {
									$isTitolareDes = true;
									if($debug) echo '<br />sono TITOLARE DES del produttore';
								}
								else
								if($ACLDesSuppliersResults['DesSuppliersReferent']['group_id'] == Configure::read('group_id_referent_des')) {
									$isReferenteDes = true;
									if($debug) echo '<br />sono REFERENTE DES del produttore';
								}
							
								/*						   
					              echo "<pre>";
					              print_r($ACLDesSuppliersResults);
					              echo "</pre>";
					            */
					        } // end if(!empty($ACLDesSuppliersResults))
							else {
								/*
								 * ctrl se lo user e' SUPER-REFERENTE DES 
								 */		
								App::import('Model', 'UserGroupMap');
								$UserGroupMap = new UserGroupMap;
						
								$options = array();
								$options['conditions'] = array('UserGroupMap.user_id' => $this->user->get('id'),
															   'UserGroupMap.group_id' => Configure::read('group_id_super_referent_des'));
								$options['recursive'] = -1;
								$ACLDesSuppliersResults = $UserGroupMap->find('first', $options);
								if(!empty($ACLDesSuppliersResults)) {
									$isSuperReferenteDes = true;
									if($debug) echo '<br />sono SUPER-REFERENTE DES del produttore';									
								}
							}
						}  // end if(!empty($desSupplierResults))
						else {
							if($debug) echo '<br />Il produttore NON fa parte dei produttori DES';
						}
						
						
						$msgOrderDesLink = "<br /><a href=?option=com_cake&controller=DesOrders&action=index>Se devi gestire un <b>ordine condiviso</b> (D.E.S.) clicca qui</a>.<br />Altrimenti prosegui con l'ordine.";
						
						if($isOwnGasTitolareDes) {
							$msgOrderDes .= "Il tuo G.A.S. è titolare degli ordini D.E.S. del produttore";
							$msgOrderDes .= $msgOrderDesLink;
						}
						else
						if($isTitolareDes) {
							$msgOrderDes .= "Sei titolare degli ordini D.E.S. del produttore!";
							$msgOrderDes .= $msgOrderDesLink;
						}
						else 
						if($isReferenteDes) {
							$msgOrderDes .= "Sei il referente degli ordini D.E.S. del produttore";
							$msgOrderDes .= $msgOrderDesLink;
						}
						else 
						if($isSuperReferenteDes) {
							$msgOrderDes .= "Sei super-referente degli ordini D.E.S. del produttore";
							$msgOrderDes .= $msgOrderDesLink;
						}
						
						if($debug) {
					        echo '<br />isOwnGasTitolareDes '.$isOwnGasTitolareDes;
					        echo '<br />isTitolareDes '.$isTitolareDes;
					        echo '<br />isReferenteDes '.$isReferenteDes;
					        echo '<br />isSuperReferenteDes '.$isSuperReferenteDes;
					        echo '<br />msgOrderDes '.$msgOrderDes;
						}
												
					} // end if(empty($this->user->des_id))
             } // end if($this->user->organization['Organization']['hasDes']=='Y' && empty($des_order_id))
             
             $this->set(compact('isOwnGasTitolareDes', 'isTitolareDes', 'isReferenteDes', 'isSuperReferenteDes', 'msgOrderDes')); 
        }

        $this->set('results', $results);

        $this->layout = 'ajax';
        $this->render('/Ajax/admin_suppliers_organization_details');
    }

    public function admin_desSupplierDetails($des_supplier_id = 0, $format = 'notmpl') {

        $results = array();

        if (!empty($des_supplier_id)) {

            App::import('Model', 'DesSupplier');
            $DesSupplier = new DesSupplier;

            $DesSupplier->unbindModel(array('belongsTo' => array('De', 'OwnOrganization', 'DesOrder')));

            $options = array();
            $options['conditions'] = array('DesSupplier.des_id' => $this->user->des_id,
                'DesSupplier.own_organization_id' => $this->user->organization['Organization']['id'],
                'DesSupplier.id' => $des_supplier_id);
            $options['recursive'] = 0;
            $results = $DesSupplier->find('first', $options);

            /*
              echo "<pre>";
              print_r($results);
              echo "</pre>";
             */
        }

        $this->set('results', $results);

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
		
        $tableValide = array('articles', 'prod_gas_articles');
        $continue = true;
        $esito = 'NO';

        $this->log .= "\r\n------------------------------------------------------------------------------";
        $this->log .= "\r\n organization_id".$this->user->organization['Organization']['id'];
        $this->log .= "\r\n supplier_id".$this->user->supplier['Supplier']['id'];
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
						$field = " . $value . " 
						and id = " . (int) $id;
			if(!empty($this->user->supplier['Supplier'])) 
				$sql .= " and supplier_id = ".$this->user->supplier['Supplier']['id'];
			else
				$sql .= " and organization_id = ".$this->user->organization['Organization']['id'];		
            // echo '<br />'.$sql;exit;
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
		   					id = " . (int) $id;			
				if(!empty($this->user->supplier['Supplier'])) 
					$sql .= " and supplier_id = ".$this->user->supplier['Supplier']['id'];
				else
					$sql .= " and organization_id = ".$this->user->organization['Organization']['id'];
                // echo '<br />'.$sql;
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
		if($debug) {
			echo "<pre>";
			print_r($this->log);
			echo "</pre>";
		}

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
        if (!$Order->exists($this->user->organization['Organization']['id'])) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;
        $results = $SummaryOrder->select_to_order($this->user, $order_id);
        $this->set(compact('results'));

        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
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
}