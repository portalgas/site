<?php
App::uses('AppController', 'Controller');

class ArticlesOrdersController extends AppController {

    public $components = ['RequestHandler', 'ActionsDesOrder'];
    private $order;

    public function beforeFilter() {
        parent::beforeFilter();

        App::import('Model', 'Order');
        $Order = new Order;

		if(isset($this->request->data['ArticlesOrder']['order_id']))
			$order_id = $this->request->data['ArticlesOrder']['order_id'];
		else				
		if(isset($this->request->data['order_id']))
			$order_id = $this->request->data['order_id'];
		else				
		if(isset($this->request->params['pass']['order_id']))
			$order_id = $this->request->params['pass']['order_id'];

		/* ctrl ACL */
        $actionWithPermission = ['admin_add', 'admin_index'];
        if (in_array($this->action, $actionWithPermission)) {

            if ($this->isSuperReferente()) {
                
            } else {
                if (empty($order_id) || !$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $order_id)) {
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

            $results = $Order->_getOrderById($this->user, $order_id);
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

		$debug = false;
	
		$msg = '';
        
		/*
         * cancello eventuali doppioni
         * */
        $this->ArticlesOrder->delete($this->user->organization['Organization']['id'], $this->order_id);

        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order['Order']['id'], $debug);
		if(!empty($desResults['des_order_id'])) {
			$des_order_id = $desResults['des_order_id'];
			$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
		}
		
	    self::d('Order.owner_articles '.$this->order['Order']['owner_articles'], $debug); 
		$results = [];
        switch ($this->order['Order']['owner_articles']) {
            case 'DES':
                /*
                 * se DES non puo' essere titolare, il titolare prende il listino articolo da REFERENT o SUPPLIER
                 * prendo il listino da titolare => Articles / ArticlesOrders
                 */
                if(!empty($desResults['des_order_id'])) {
                    $titolareOrderResult = $this->ActionsDesOrder->getOrderTitolare($this->user, $desResults, $debug);
                    $results = $this->ArticlesOrder->getArticlesByOrder($this->user, $titolareOrderResult, [], $debug);	// articoli del titolare da associare
                }
                break;
            case 'REFERENT':
            case 'SUPPLIER':
            case 'PACT':
                /*
                 * prima volta ricerco partendo da SupplierOrganization => Articles
                 * SuppliersOrganizationOwnerArticles creato in AppModel::_getOrderById() chi gestisce il listino
                 */
                $suppliersOrganization['SuppliersOrganization'] = $this->order['SuppliersOrganizationOwnerArticles'];
                // debug($suppliersOrganization['SuppliersOrganization']);
                $results = $this->ArticlesOrder->getArticlesBySuppliersOrganization($this->user, $suppliersOrganization, [], $debug);	// articoli da associare
                break;
            default:
                self::x(__('msg_error_supplier_organization_owner_articles'));
                break;
        }
        self::d($results, $debug);

        foreach ($results as $result) {
			
			$data = [];
            $data['ArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
            $data['ArticlesOrder']['order_id'] = $this->order_id;
            $data['ArticlesOrder']['article_organization_id'] = $result['Article']['organization_id'];
            $data['ArticlesOrder']['article_id'] = $result['Article']['id'];
            $data['ArticlesOrder']['name'] = $result['Article']['name'];
            $data['ArticlesOrder']['prezzo'] = $result['Article']['prezzo'];
            $data['ArticlesOrder']['qta_cart'] = 0;
            $data['ArticlesOrder']['pezzi_confezione'] = $result['Article']['pezzi_confezione'];
            $data['ArticlesOrder']['qta_minima'] = $result['Article']['qta_minima'];
            $data['ArticlesOrder']['qta_massima'] = $result['Article']['qta_massima'];
            $data['ArticlesOrder']['qta_minima_order'] = $result['Article']['qta_minima_order'];
            $data['ArticlesOrder']['qta_massima_order'] = $result['Article']['qta_massima_order'];
            $data['ArticlesOrder']['qta_multipli'] = $result['Article']['qta_multipli'];
            $data['ArticlesOrder']['flag_bookmarks'] = 'N';
            if ($this->user->organization['Organization']['hasFieldArticleAlertToQta'] == 'N')
                $data['ArticlesOrder']['alert_to_qta'] = 0;
            else
                $data['ArticlesOrder']['alert_to_qta'] = $result['Article']['alert_to_qta'];
            $data['ArticlesOrder']['stato'] = 'Y';

            /*
             * richiamo la validazione
             */
            // $this->ArticlesOrder->set($data); il prezzo 1.00 non viene validato perche' si aspetta 1,00
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
                if (!$this->ArticlesOrder->save($data)) {
                    $msg .= "<br />Articolo ".$result['Article']['name']." [".$result['Article']['id']."] non salvato per errore di sistema!";
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

            /* 
             * msg in fromOrderAddToArticlesOrderAdd
             * $this->Session->setFlash(__('The articles order has been saved'));
             */
        }

        $this->myRedirect(Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id=' . $this->delivery_id . '&order_id=' . $this->order_id);
    }

    /*
	 *   arrivo da RoutingComponent 
	 * 
     *   richiamato dopo la creazione di un ordine se Organization.hasArticlesOrder=='Y'
     *   aggiorno orders.state_code = 'OPEN-NEXT' o 'OPEN'
     *
     * 	action_post = action_articles_orders_current gestione normale
     *  action_post = action_articles_orders_previuos associo articoli ordine precedente
	 */
    public function admin_add($delivery_id=0, $order_id=0, $des_order_id=0, $sort='Article.name asc', $filter_name='') {

        $debug = false;
		
		if(isset($this->request->data['ArticlesOrder']['order_id']))
			$order_id = $this->request->data['ArticlesOrder']['order_id'];
		else	
		if(isset($this->request->params['pass']['order_id']))
			$order_id = $this->request->params['pass']['order_id'];
		
		App::import('Model', 'Article');
		$Article = new Article;
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'ArticlesOrderLifeCycle');
		$ArticlesOrderLifeCycle = new ArticlesOrderLifeCycle;

        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order['Order']['id'], $debug);
		if(!empty($desResults['des_order_id'])) {
			$des_order_id = $desResults['des_order_id'];
			$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
			$this->set('des_order_id',$des_order_id);
			$this->set('isTitolareDesSupplier', $isTitolareDesSupplier);
			$this->set('desOrdersResults', $desResults['desOrdersResults']);
			$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
			$canEdit = $ArticlesOrderLifeCycle->canEdit($this->user, $this->order, $isTitolareDesSupplier, $debug);
		}
		else
        	$canEdit = $ArticlesOrderLifeCycle->canEdit($this->user, $this->order, false, $debug);
        $this->set(compact('canEdit'));
       
		if($this->order['Order']['owner_articles']=='REFERENT' || $this->order['Order']['owner_articles']=='SUPPLIER') {
			$previousResults = $this->_getPreviousArticlesOrder($this->user, $this->order['Order']);
			$this->set(compact('previousResults'));
		}

        if ($this->request->is('post') || $this->request->is('put')) {

			self::d($this->request->data, $debug);

            $msg = "";
            /*
             * cancello eventuali doppioni
             */
            $this->ArticlesOrder->delete($this->user->organization['Organization']['id'], $order_id);

            $des_order_id = $this->request->data['ArticlesOrder']['des_order_id'];
            $action_post = $this->request->data['ArticlesOrder']['action_post'];
		    if ($action_post == 'action_articles_orders_previuos')
                $this->request->data = $this->_ridefinedDataToPreviousArticlesOrder($previousResults);

            $article_id_selected = $this->request->data['ArticlesOrder']['article_id_selected'];
            $arr_article_id_selected = explode(',', $article_id_selected);
			
			if(isset($this->request->data['Article']))
            foreach ($this->request->data['Article'] as $article_id => $article) {

                if (isset($article_id) && in_array($article_id, $arr_article_id_selected)) {
                
					$data = [];
				
					self::d('Tratto articolo ['.$article_id.']', $debug);
					self::d($article, $debug);

					/*
                	 * get Article.name
                	 */
                	$opts = []; 
					$opts['conditions'] = ['Article.organization_id' => $article['article_organization_id'], 
					         			   'Article.id' => $article_id];
                    $opts['recursive'] = -1;
					$articleResults = $Article->find('first', $opts);
					if(empty($articleResults)) {
						self::d($opts);
						self::x('Non trovato il nome dell articolo');
					}
					
					$data['ArticlesOrder']['name'] = $articleResults['Article']['name'];
					self::d($articleResults, $debug); 

                    $data['ArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
					$data['ArticlesOrder']['order_id'] = $order_id;
				
					/*
					 * dati dell owner_ dell'articolo REFERENT / SUPPLIER / DES
					 */
					$data['ArticlesOrder']['article_organization_id'] = $article['article_organization_id'];
					$data['ArticlesOrder']['article_id'] = $article_id;
		
					if(isset($article['ArticlesOrderPrezzo']))	
						$data['ArticlesOrder']['prezzo'] = $article['ArticlesOrderPrezzo'];
					else
						$data['ArticlesOrder']['prezzo'] = $articleResults['Article']['prezzo_'];
						
					if(isset($article['ArticlesOrderPezziConfezione']))	
						$data['ArticlesOrder']['pezzi_confezione'] = $article['ArticlesOrderPezziConfezione'];
					else
						$data['ArticlesOrder']['pezzi_confezione'] = $articleResults['Article']['pezzi_confezione'];
					if(isset($article['ArticlesOrderQtaMinima']))	
						$data['ArticlesOrder']['qta_minima'] = $article['ArticlesOrderQtaMinima'];
					else
						$data['ArticlesOrder']['qta_minima'] = $articleResults['Article']['qta_minima'];
					if(isset($article['ArticlesOrderQtaMassima']))	
						$data['ArticlesOrder']['qta_massima'] = $article['ArticlesOrderQtaMassima'];
					else
						$data['ArticlesOrder']['qta_massima'] = $articleResults['Article']['qta_massima'];
					if(isset($article['ArticlesOrderQtaMinimaOrder']))	
						$data['ArticlesOrder']['qta_minima_order'] = $article['ArticlesOrderQtaMinimaOrder'];
					else
						$data['ArticlesOrder']['qta_minima_order'] = $articleResults['Article']['qta_minima_order'];
					if(isset($article['ArticlesOrderQtaMassimaOrder']))	
						$data['ArticlesOrder']['qta_massima_order'] = $article['ArticlesOrderQtaMassimaOrder'];
					else
						$data['ArticlesOrder']['qta_massima_order'] = $articleResults['Article']['qta_massima_order'];
					if(isset($article['ArticlesOrderQtaMultipli']))	
						$data['ArticlesOrder']['qta_multipli'] = $article['ArticlesOrderQtaMultipli'];
					else
						$data['ArticlesOrder']['qta_multipli'] = $articleResults['Article']['qta_multipli'];
						
					$data['ArticlesOrder']['alert_to_qta'] = 0;	
					if(isset($data['alert_to_qta']))	
						$data['ArticlesOrder']['alert_to_qta'] = $article['ArticlesOrderAlertToQta'];
					else
						$data['ArticlesOrder']['alert_to_qta'] = $articleResults['Article']['alert_to_qta'];
					
					$data['ArticlesOrder']['send_mail'] = 'N';
					$data['ArticlesOrder']['qta_cart'] = '0';
					$data['ArticlesOrder']['flag_bookmarks'] = 'N';
					$data['ArticlesOrder']['stato'] = 'Y';

					self::d('ArticlesOrder da salvare', $debug);
					self::d($data, $debug);

                    /*
                     * richiamo la validazione
                     */
                    $this->ArticlesOrder->set($data);
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
                        if (!$this->ArticlesOrder->save($data)) {
                            $msg .= "<br />Articolo ".$articleResults['Article']['name']." [".$article_id."] non salvato per errore di sistema!";
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
                $utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $order_id);

                /*
                 * setto la tipologia Draw (SIMPLE o COMPLETE)
                 */
                App::import('Model', 'Order');
                $Order = new Order;
                $Order->updateTypeDraw($this->user, $order_id);

                if ($this->user->organization['Organization']['hasDes'] == 'Y' && !empty($des_order_id)) {
					/*
					 * non +, il GAS puo' scegliere quali articoli associare
                    App::import('Model', 'DesOrder');
                    $DesOrder = new DesOrder;
                    $DesOrder->insertOrUpdateArticlesOrderAllOrganizations($this->user, $des_order_id, $order_id, null, $isTitolareDesSupplier, $debug);
					*/
					
                    $this->Session->setFlash(__('The articles order has been saved'));
                    $url = Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id=' . $this->delivery_id . '&order_id=' . $this->order_id . '&des_order_id=' . $des_order_id;
                } else {
                    $this->Session->setFlash(__('The articles order has been saved'));
                    $url = Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id=' . $this->delivery_id . '&order_id=' . $order_id;
                }

                if (!$debug)
                    $this->myRedirect($url);
            }
        } // end if ($this->request->is('post')) 

	    self::d('Order.owner_articles '.$this->order['Order']['owner_articles'], $debug); 
		$results = [];		
		$opt = []; 
		$opt['order'] = [$sort];
		if(!empty($filter_name))
			$opt['conditions'] = ['Article.name LIKE ' => '%'.$filter_name.'%'];

		switch ($this->order['Order']['owner_articles']) {
            case 'DES':
                /*
                 * se DES non puo' essere titolare, il titolare prende il listino articolo da REFERENT o SUPPLIER
                 * prendo il listino da titolare => Articles / ArticlesOrders
                 */
                if(!empty($desResults['des_order_id'])) {
                    $titolareOrderResult = $this->ActionsDesOrder->getOrderTitolare($this->user, $desResults, $debug);
                    $results = $this->ArticlesOrder->getArticlesByOrder($this->user, $titolareOrderResult, $opt, $debug);	// articoli del titolare da associare
                }
            break;
            case 'REFERENT':
            case 'SUPPLIER':
            case 'PACT':
                /*
                 * prima volta ricerco partendo da SupplierOrganization => Articles
                 * SuppliersOrganizationOwnerArticles creato in AppModel::_getOrderById() chi gestisce il listino
                 */
                $suppliersOrganization['SuppliersOrganization'] = $this->order['SuppliersOrganizationOwnerArticles'];
                // debug($suppliersOrganization['SuppliersOrganization']);
                $results = $this->ArticlesOrder->getArticlesBySuppliersOrganization($this->user, $suppliersOrganization, $opt, $debug);	// articoli da associare
			break;
			default:
				self::x(__('msg_error_supplier_organization_owner_articles'));
			break;
		}
		self::d($results, $debug);  
		$this->set(compact('results'));
		
		$sorts = [];
		if($this->user->organization['Organization']['hasFieldArticleCodice']=='Y') {
			$sorts['Article.codice asc'] = __('Code').' '.__('OrderAsc');
			$sorts['Article.codice desc'] = __('Code').' '.__('OrderDesc');
		}
		$sorts['Article.name asc'] = __('Name').' '.__('OrderAsc');
		$sorts['Article.name desc'] = __('Name').' '.__('OrderDesc');
 
		$this->set(compact('sort', 'sorts', 'filter_name'));
    }

    /*
     * Organization.type = PRODGAS elenco degli articoli associati all'ordine
	 * 
	 * per ora e' disabilitato da ProgGasOrder::index
	 */
    public function admin_prodgas_index($organization_id, $order_id) {
		
    	$debug = false;
    
        if (empty($organization_id) || empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        		
		// ACL 
		App::import('Model', 'ProdGasSuppliersImport');
		$ProdGasSuppliersImport = new ProdGasSuppliersImport;

		// precedente versione $organizationResults = $ProdGasSupplier->getOrganizationAssociate($this->user, $organization_id, 0, $debug);
		$organizationResults = $ProdGasSuppliersImport->getProdGasSuppliers($this->user, $this->user->organization['Organization']['id'], $organization_id, [], $debug);
		
		$currentOrganization = $organizationResults['Supplier']['Organization'];
		$currentOrganization = current($currentOrganization);
		self::d($currentOrganization, $debug);

		if($currentOrganization['SuppliersOrganization']['owner_articles']!='SUPPLIER' || $currentOrganization['SuppliersOrganization']['owner_organization_id'] != $this->user->organization['Organization']['id']) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}				
		$this->set(compact('currentOrganization'));
		// ACL
		
		App::import('Model', 'Order');
		$Order = new Order;
		
        $options = [];
        $options['conditions'] = ['Order.organization_id' => $organization_id, 'Order.id' => $order_id];
        $options['recursive'] = 0;
        $results = $Order->find('first', $options);

        $this->order = $results;
        $this->set('order', $this->order);

		$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organization_id, 'type' => 'PRODGAS']);

        $this->_index($tmp_user, $organization_id, $delivery_id, $order_id, $debug);	
        
        $this->set('organization_id', $organization_id);	
	}

    /*
     * Organization.type = GAS elenco degli articoli associati all'ordine
     */
    public function admin_index() {
    
	    $debug = false;
        
		$this->set('organization_id', $this->user->organization['Organization']['id']);
		
        $this->_index($this->user, $this->user->organization['Organization']['id'], $this->delivery_id, $this->order_id, $debug);
	}

    public function _index($user, $organization_id, $delivery_id, $order_id, $debug = false) {

        if (empty($order_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        
		App::import('Model', 'Article');
		$Article = new Article;
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'ArticlesOrderLifeCycle');
		$ArticlesOrderLifeCycle = new ArticlesOrderLifeCycle;
		
		App::import('Model', 'DesOrder');
		$DesOrder = new DesOrder;

		App::import('Model', 'Order');
		$Order = new Order;
				
        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($user, $this->order['Order']['id'], $debug);
		$des_order_id = $desResults['des_order_id'];
		$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
		$this->set('des_order_id',$des_order_id);
		$this->set('isTitolareDesSupplier', $isTitolareDesSupplier);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
				
		$canEdit = $ArticlesOrderLifeCycle->canEdit($user, $this->order, $isTitolareDesSupplier, $debug);
		$this->set('canEdit', $canEdit);

        if ($this->request->is('post') || $this->request->is('put')) {

			self::d($this->request->data, $debug);

            $msg = "";

            /*
             * articoli da aggiungere in ArticlesOrder
             */
            $article_id_selected = $this->request->data['ArticlesOrder']['article_id_selected'];
            if (!empty($article_id_selected)) {
				
				self::d('article_id che ho selezionato '.$article_id_selected, $debug);
				
                $arr_article_id_selected = explode(',', $article_id_selected);

                foreach ($this->request->data['Article'] as $article_id => $article) {
					
					self::d('Tratto article_id '.$article_id, $debug);
					
					if (isset($article_id) && in_array($article_id, $arr_article_id_selected)) {
                    	
						$data = [];
						
						/*
						 * get Article, 
						 */
						$opts = ['Article.organization_id' => $article['article_organization_id'], 
						         'Article.id' => $article_id];
						$articleResults = $Article->getByOrder($user, $this->order, $opts, $debug);
						$data['ArticlesOrder']['name'] = $articleResults['Article']['name'];
						self::d($articleResults, $debug); 
										  
						$data['ArticlesOrder']['organization_id'] = $organization_id;
						$data['ArticlesOrder']['order_id'] = $order_id;

						/*
						 * dati dell owner_ dell'articolo REFERENT / SUPPLIER / DES
						 */
						$data['ArticlesOrder']['article_organization_id'] = $article['article_organization_id'];
						$data['ArticlesOrder']['article_id'] = $article_id;
					
                        if(isset($article['ArticlesOrderPrezzo']))	
							$data['ArticlesOrder']['prezzo'] = $article['ArticlesOrderPrezzo'];
						else
							$data['ArticlesOrder']['prezzo'] = $articleResults['Article']['prezzo_'];
							
                        if(isset($article['ArticlesOrderPezziConfezione']))	
							$data['ArticlesOrder']['pezzi_confezione'] = $article['ArticlesOrderPezziConfezione'];
						else
							$data['ArticlesOrder']['pezzi_confezione'] = $articleResults['Article']['pezzi_confezione'];
                        if(isset($article['ArticlesOrderQtaMinima']))	
							$data['ArticlesOrder']['qta_minima'] = $article['ArticlesOrderQtaMinima'];
						else
							$data['ArticlesOrder']['qta_minima'] = $articleResults['Article']['qta_minima'];
                        if(isset($article['ArticlesOrderQtaMassima']))	
							$data['ArticlesOrder']['qta_massima'] = $article['ArticlesOrderQtaMassima'];
						else
							$data['ArticlesOrder']['qta_massima'] = $articleResults['Article']['qta_massima'];
                        if(isset($article['ArticlesOrderQtaMinimaOrder']))	
							$data['ArticlesOrder']['qta_minima_order'] = $article['ArticlesOrderQtaMinimaOrder'];
						else
							$data['ArticlesOrder']['qta_minima_order'] = $articleResults['Article']['qta_minima_order'];
                        if(isset($article['ArticlesOrderQtaMassimaOrder']))	
							$data['ArticlesOrder']['qta_massima_order'] = $article['ArticlesOrderQtaMassimaOrder'];
						else
							$data['ArticlesOrder']['qta_massima_order'] = $articleResults['Article']['qta_massima_order'];
                        if(isset($article['ArticlesOrderQtaMultipli']))	
							$data['ArticlesOrder']['qta_multipli'] = $article['ArticlesOrderQtaMultipli'];
						else
							$data['ArticlesOrder']['qta_multipli'] = $articleResults['Article']['qta_multipli'];
							
						$data['ArticlesOrder']['alert_to_qta'] = 0;	
						if(isset($article['alert_to_qta']))	
							$data['ArticlesOrder']['alert_to_qta'] = $article['ArticlesOrderAlertToQta'];
						else
							$data['ArticlesOrder']['alert_to_qta'] = $articleResults['Article']['alert_to_qta'];

						$data['ArticlesOrder']['send_mail'] = 'N';
						$data['ArticlesOrder']['qta_cart'] = "0";
						$data['ArticlesOrder']['flag_bookmarks'] = 'N';
						$data['ArticlesOrder']['stato'] = 'Y';

						if($debug) debug($data);	
					
                        /*
                         * richiamo la validazione
                         */
                        $this->ArticlesOrder->set($data);
                        if (!$this->ArticlesOrder->validates()) {
                            $errors = $this->ArticlesOrder->validationErrors;
                            break;
                        }

                        $this->ArticlesOrder->create();
                        if (!$this->ArticlesOrder->save($data)) {
                            $msg .= "<br />Articolo (" .$data['ArticlesOrder']['name']. ") non associato all'ordine!";
                        }

                        /*
                         *  D E S, se isTitolareDesSupplier propago agli altri GAS
                         */
                        if ($user->organization['Organization']['hasDes'] == 'Y' && $isTitolareDesSupplier) {

                            $articles_orders_key = ['organization_id' => $data['ArticlesOrder']['organization_id'],
													'order_id' => $data['ArticlesOrder']['order_id'],
													'article_organization_id' => $data['ArticlesOrder']['article_organization_id'],
													'article_id' => $data['ArticlesOrder']['article_id']];
                            $DesOrder->insertOrUpdateArticlesOrderAllOrganizations($user, $this->order['Order']['des_order_id'], $order_id, $articles_orders_key, $isTitolareDesSupplier, $debug);
                        }
                    }
                } // end foreach
            } // if(!empty($article_id_selected))


            /*
             * ArticlesOrder da cancellare
             * 		order NO DES => cancello tutti gli eventuali acquisti (Carts)
             * 		order DES 
			 *			titolare => cancello tutti gli eventuali acquisti (Carts) di TUTTI i GAS
			 *			NO titolare => cancello tutti gli eventuali acquisti (Carts) del proprio i GAS
             */
            $article_order_key_selected = $this->request->data['ArticlesOrder']['article_order_key_selected'];
            if (!empty($article_order_key_selected)) {
                $arr_article_order_key_selected = explode(',', $article_order_key_selected);

                foreach ($arr_article_order_key_selected as $article_order_key) {

                    list($order_id, $article_organization_id, $article_id) = explode('_', $article_order_key);

					$msg .= $this->ArticlesOrder->delete_and_carts($user, $order_id, $article_organization_id, $article_id, $this->order['Order']['des_order_id'], $isTitolareDesSupplier, $debug);
															
                } // end foreach
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
            $utilsCrons->ordersStatoElaborazione($organization_id, (Configure::read('developer.mode')) ? true : false, $order_id);
        
        	/*
        	 * se CREATE-INCOMPLETE vado in home
        	 */
			$options = [];
			$options['conditions'] = ['Order.organization_id' => $organization_id, 'Order.id' => $order_id];
			$options['recursive'] = -1;
			$orderCtrlResults = $Order->find('first', $options);
			if($orderCtrlResults['Order']['state_code']=='CREATE-INCOMPLETE') {
					$this->Session->setFlash(__('CREATE-INCOMPLETE-descri'));
					$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$orderCtrlResults['Order']['delivery_id'].'&order_id='.$order_id;
					$this->myRedirect($url);
			 }
			 
        } // end if ($this->request->is('post') || $this->request->is('put'))

		self::d('Order.owner_articles '.$this->order['Order']['owner_articles'], $debug);

        $results = [];
        $articles = [];
		switch ($this->order['Order']['owner_articles']) {
            case 'DES':
                /*
                 * se DES non puo' essere titolare, il titolare prende il listino articolo da REFERENT o SUPPLIER
                 * prendo il listino da titolare => Articles / ArticlesOrders
                 */
                if(!empty($desResults['des_order_id'])) {
                    $results = $this->ArticlesOrder->getArticlesByOrder($user, $this->order, [], $debug);	// articoli del titolare da associare

                    $titolareOrderResult = $this->ActionsDesOrder->getOrderTitolare($user, $desResults, $debug);
                    $articles = $this->ArticlesOrder->getArticlesByDesOrder_Ordinabili($user, $titolareOrderResult, $this->order, [], $debug);	// articoli da associare
                }
            break;
			case 'REFERENT':
            case 'SUPPLIER':
            case 'PACT':
				self::d('Ordine NON DES', $debug);
				$results = $this->ArticlesOrder->getArticlesByOrder_ConAcquisti($user, $this->order, [], $debug);  // articoli gia associati
				$articles = $this->ArticlesOrder->getArticlesBySupplierOrganization_Ordinabili($user, $this->order, [], $debug);	// articoli da associare
			break;
			default:
				self::x(__('msg_error_supplier_organization_owner_articles').' ['.$this->order['Order']['owner_articles'].']');
			break;				
		}
		$this->set(compact('results', 'articles'));
	
	    $this->render('/ArticlesOrders/admin_index');    
    }


    public function admin_prodgas_edit($organization_id, $order_id, $article_organization_id, $article_id) {
    
    	$debug = false;
    
        if (empty($organization_id) || empty($order_id) || empty($article_organization_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

		// ACL 
		App::import('Model', 'ProdGasSuppliersImport');
		$ProdGasSuppliersImport = new ProdGasSuppliersImport;

		// precedente versione $organizationResults = $ProdGasSupplier->getOrganizationAssociate($this->user, $organization_id, 0, $debug);
		$organizationResults = $ProdGasSuppliersImport->getProdGasSuppliers($this->user, $this->user->organization['Organization']['id'], $organization_id, [], $debug);
		
		$currentOrganization = $organizationResults['Supplier']['Organization'];
		$currentOrganization = current($currentOrganization);
		self::d($currentOrganization, $debug);

		if($currentOrganization['SuppliersOrganization']['owner_articles']!='SUPPLIER' || $currentOrganization['SuppliersOrganization']['owner_organization_id'] != $this->user->organization['Organization']['id']) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}				
		$this->set(compact('currentOrganization'));
		// ACL
		
		App::import('Model', 'Order');
		$Order = new Order;
		
        $options = [];
        $options['conditions'] = ['Order.organization_id' => $organization_id,
								  'Order.id' => $order_id];
        $options['recursive'] = 0;
        $results = $Order->find('first', $options);

        $this->order = $results;
        $this->set('order', $this->order);

		$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organization_id, 'type' => 'PRODGAS']);
		
		$this->set(compact('organization_id'));
		
        $this->_edit($tmp_user, $organization_id, $order_id, $article_organization_id, $article_id, $debug);	
	}

    public function admin_edit($order_id = 0, $article_organization_id = 0, $article_id = 0) {
    
	    $debug = false;
                
        $this->set('organization_id', $this->user->organization['Organization']['id']);	
		
        $this->_edit($this->user, $this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id, $debug);        
	}
	
    /*
     * modifico i dati di un articolo associato all'ordine
     */
    public function _edit($user, $organization_id, $order_id=0, $article_organization_id=0, $article_id=0, $debug=false) {

        App::import('Model', 'ArticlesOrderLifeCycle');
		$ArticlesOrderLifeCycle = new ArticlesOrderLifeCycle;
		
        if (empty($order_id) || empty($article_organization_id) || empty($article_id)) {
            /*
             * dopo il submit passano come campi hidden
             */
            $order_id = $this->request->data['ArticlesOrder']['order_id'];
            $article_organization_id = $this->request->data['ArticlesOrder']['article_organization_id'];
            $article_id = $this->request->data['ArticlesOrder']['article_id'];
        }

        if (empty($organization_id) || empty($order_id) || empty($article_organization_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if (!$this->ArticlesOrder->exists($organization_id, $order_id, $article_organization_id, $article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($user, $this->order['Order']['id'], $debug);
		$des_order_id = $desResults['des_order_id'];
		$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
		$this->set('des_order_id',$des_order_id);
		$this->set('isTitolareDesSupplier', $isTitolareDesSupplier);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		
		$canEdit = $ArticlesOrderLifeCycle->canEdit($user, $this->order, $isTitolareDesSupplier, $debug);
		$this->set('canEdit', $canEdit);
		/*
		 * il ctrl non lo faccio + perche' alcuni campi sono modificabili
		if(!$canEdit) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));	
		}
		*/
		
        if ($this->request->is('post') || $this->request->is('put')) {

            $this->request->data['ArticlesOrder']['organization_id'] = $organization_id;

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
                $msg = "Articolo non aggiornato: dati non validi, $tmp<br />";
                $this->Session->setFlash($msg);
            } else {

                $this->ArticlesOrder->create();
                if ($this->ArticlesOrder->save($this->request->data)) {
                    $this->Session->setFlash(__('The articles order edit single has been saved'));

                    /*
                     *  D E S
                     */
                    if ($this->user->organization['Organization']['hasDes'] == 'Y' && $isTitolareDesSupplier) {

                        $articles_orders_key = ['organization_id' => $this->request->data['ArticlesOrder']['organization_id'],
												'order_id' => $order_id,
												'article_organization_id' => $article_organization_id,
												'article_id' => $article_id];
                        $DesOrder = new DesOrder;
                        $DesOrder->insertOrUpdateArticlesOrderAllOrganizations($user, $des_order_id, $order_id, $articles_orders_key, $isTitolareDesSupplier, $debug);
                    }

                    /*
                     * aggiorno ArticlesOrder.qta_cart e ArticlesOrder.qta_massima_order
                     */
                    $this->ArticlesOrder->aggiornaQtaCart_StatoQtaMax($organization_id, $order_id, $article_organization_id, $article_id);

                    if (!$debug) {
				 		switch($user->organization['Organization']['type']) {
							case 'PRODGAS':
        		                $this->myRedirect(['controller' => 'ArticlesOrders','action' => 'prodgas_index', 'organization_id' => $organization_id, 'order_id' => $order_id]);
							break;
							case 'GAS':
                            case 'SOCIALMARKET':
		                        $this->myRedirect(['controller' => 'ArticlesOrders','action' => 'index', 'order_id' => $order_id]);
							break;
							case 'PROD':
							break;  
						}                 
                    }
                } else
                    $this->Session->setFlash(__('The article could not be saved. Please, try again.'));
            } // end if(!$this->ArticlesOrder->validates()) 
        } // if ($this->request->is('post') || $this->request->is('put')) 
        else {
            /*
             * aggiorno ArticlesOrder.qta_cart e ArticlesOrder.qta_massima_order
             */
            $this->ArticlesOrder->aggiornaQtaCart_StatoQtaMax($organization_id, $order_id, $article_organization_id, $article_id);
        }

        $options = [];
        $options['conditions'] = ['ArticlesOrder.organization_id' => $organization_id,
								'ArticlesOrder.order_id' => $order_id,
								'ArticlesOrder.article_organization_id' => $article_organization_id,
								'ArticlesOrder.article_id' => $article_id];
        $options['recursive'] = -1;
        $this->request->data = $this->ArticlesOrder->find('first', $options);

        /*
         * Order
         * */
        App::import('Model', 'Order');
        $Order = new Order;

        $options = [];
        $options['conditions'] = ['Order.organization_id' => $organization_id,
								'Order.isVisibleBackOffice' => 'Y',
								'Order.id' => $order_id];
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

        $options = [];
        $options['conditions'] = ['Article.id' => $article_id, 'Article.organization_id' => $article_organization_id];
        $article = $Article->getArticlesDataAnagr($this->user, $options);
        $this->set('article', $article);

        /*
         * ctrl referentTesoriere
         */
        $this->set('isReferenteTesoriere', $this->isReferentTesoriere());
        
        $this->render('/ArticlesOrders/admin_edit');
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

        self::d('delivery_id '.$this->delivery_id, $debug);
        self::d('order_id '.$this->order_id, $debug);
        
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
            if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
                $this->Session->setFlash(__('msg_error_params'));
                $this->myRedirect(Configure::read('routes_msg_exclamation'));
            }

            $results = $Order->read($this->order_id, $this->user->organization['Organization']['id']);
            $this->set(compact('results'));

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
                $this->myRedirect(['controller' => 'ArticlesOrders', 'action' => 'index', 'order_id' => $this->order_id]);
            }
        } // end if(!empty($this->delivery_id) && !empty($this->order_id))

        /*
         * se article_id e' valorizzato cerco se trovo almeno un ordine legato all'articolo
         */
        $order_id = 0;
        $delivery_id = 0;
        $conditions = ['Article.id' => $article_id];
        $resultsCtrl = $this->ArticlesOrder->getArticlesOrdersInOrder($this->user, $conditions);
        if (!empty($resultsCtrl)) {
            // prendo il primo order_id (potrei avere + ordini associati all'articolo)
            $order_id = $this->order_id = $resultsCtrl[0]['ArticlesOrder']['order_id'];

            App::import('Model', 'Order');
            $Order = new Order;

            $options = [];
            $options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
            $options['recursive'] = -1;
            $options['fields'] = ['Order.delivery_id'];
            $results = $Order->find('first', $options);
            $delivery_id = $this->delivery_id = $results['Order']['delivery_id'];
        }


        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

		$options = [];
        $options['conditions'] = ['Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
								  'Delivery.sys' => 'Y'];
		$options['fields'] = ['Delivery.id', 'Delivery.luogo'];
		$options['recursive'] = -1;		
        $deliverySysY = $Delivery->find('list', $options);
		
		$options = [];
        $options['conditions'] = ['Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
								 'Delivery.isVisibleBackOffice' => 'Y',
								 'Delivery.sys' => 'N', 
                                 'Delivery.type'=> 'GAS', // GAS-GROUP
								 'Delivery.stato_elaborazione' => 'OPEN'];
        if (!empty($delivery_id))
            $options['conditions']  += ['Delivery.id' => $this->delivery_id];
		$options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$options['order'] = ['Delivery.data ASC'];
		$options['recursive'] = -1;		
        $deliverySysN = $Delivery->find('list', $options);

		$deliveries = [];
		$deliveries += $deliverySysY;
		$deliveries += $deliverySysN;
		self::d($deliveries);
        if (empty($deliveries)) {
            $this->Session->setFlash(__('NotFoundDeliveries'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        $this->set(compact('deliveries'));

        $ACLsuppliersIdsOrganization = $this->user->get('ACLsuppliersIdsOrganization');

        App::import('Model', 'Order');
        $Order = new Order;

		$options = [];
        $options['conditions'] = ['Order.organization_id' => (int) $this->user->organization['Organization']['id'],
								'Order.delivery_id' => $this->delivery_id,
								'Order.isVisibleBackOffice' => 'Y',
								'Order.supplier_organization_id IN (' . $ACLsuppliersIdsOrganization . ')',
							]; // 'DATE(Order.data_fine) >= CURDATE()'
		if (!empty($order_id))
            $options['conditions'] += ['Order.id' => $this->order_id];
		$options['order'] = ['Order.data_inizio ASC'];
		$options['recursive'] = 1;
        $results = $Order->find('all', $options);
        $orders = [];
        if (!empty($results))
            foreach ($results as $result) {

                if ($result['Order']['data_fine_validation'] != Configure::read('DB.field.date.empty'))
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
    private function _getPreviousArticlesOrder($user, $order, $debug=false) {

        $previousResults = [];

		if(!empty($order)) {
			App::import('Model', 'Order');
			$Order = new Order;

			$options = [];
			$options['conditions'] = ['Delivery.organization_id' => (int) $user->organization['Organization']['id'],
									'Delivery.isVisibleBackOffice' => 'Y',
					                'DATE(Delivery.data) < CURDATE()',
									'Order.isVisibleBackOffice' => 'Y',
									'Order.supplier_organization_id' => $order['supplier_organization_id']];
			$options['order'] = ['Delivery.data DESC'];
			$results = $Order->find('first', $options);
			if($debug) debug($options);
            if($debug) debug($results);

			/*
			 * c'e' un ordine precedente, estraggo gli articoli non escludendoli dal precedente
			 */
			if (!empty($results)) {
                $order = $Order->_getOrderById($user, $results['Order']['id'], $debug=false);  // nella struttura ho SuppliersOrganizationOwnerArticles
				$opts = ['force' => 'NOT_EXCLUDE_ARTICLESORDERS'];
				$previousResults = $this->ArticlesOrder->getArticlesByOrder_Ordinabili($user, $order, $opts);
			}			
		}
        if($debug) debug($previousResults);
		
        return $previousResults;
    }

    /*
     *  all'ordine associo gli articoli dell'ordine precedente
     *  ridefinisco $this->request->data in 
     *  [ArticlesOrder] => [[article_id_selected] => id, id)
     * 	[Article] => [[article_id] => Array(
     *              [ArticlesOrderPrezzo] => 1,00
     *              [ArticlesOrderPezziConfezione] => 1
     *              [ArticlesOrderQtaMinima] => 1
     *              [ArticlesOrderQtaMassima] => 0
     *              [ArticlesOrderQtaMultipli] => 1
     *              [ArticlesOrderQtaMinimaOrder] => 0
     *              [ArticlesOrderQtaMassimaOrder] => 0)	 
     */
    private function _ridefinedDataToPreviousArticlesOrder($previousResults) {

        $data = [];
        $article_id_selected = '';
        foreach ($previousResults as $previousResult) {

            if ($previousResult['Article']['stato'] == 'Y') {

                $data['Article'][$previousResult['Article']['id']] = [
                    /*
                     * prendo l'importo dall'articolo cosi' se e' stato aggiornato
                     */
					'ArticlesOrderPrezzo' => $previousResult['Article']['prezzo_'],
					'ArticlesOrderPezziConfezione' => $previousResult['ArticlesOrder']['pezzi_confezione'],
					'ArticlesOrderQtaMinima' => $previousResult['ArticlesOrder']['qta_minima'],
					'ArticlesOrderQtaMassima' => $previousResult['ArticlesOrder']['qta_massima'],
					'ArticlesOrderQtaMultipli' => $previousResult['ArticlesOrder']['qta_multipli'],
					'ArticlesOrderQtaMinimaOrder' => $previousResult['ArticlesOrder']['qta_minima_order'],
					'ArticlesOrderQtaMassimaOrder' => $previousResult['ArticlesOrder']['qta_massima_order'],
					'article_organization_id' => $previousResult['ArticlesOrder']['article_organization_id']
				];

                $article_id_selected .= $previousResult['Article']['id'] . ',';
            }
        }

        if (!empty($article_id_selected))
            $article_id_selected = substr($article_id_selected, 0, strlen($article_id_selected) - 1);

        $data['ArticlesOrder'] = ['article_id_selected' => $article_id_selected];

		self::d($data, false);
		
        return $data;
    }
}