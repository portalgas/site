<?php
App::uses('AppModel', 'Model');
App::import('Model', 'ArticlesOrderMultiKey');

/**
 * public function getArticlesOrdersInOrder($user, $conditions, $orderBy=null) 
 *        estraggo tutti gli articoli in base all'ordine
 * 
 * public function getArticoliEventualiAcquistiNoFilterInOrdine($user, $conditions, $orderBy=null)
 *        estraggo tutti gli articoli acquistati in base all'ordine ed EVENTUALI Cart di UN utente 
 *        $conditions['Cart.user_id']  obbligatorio
 * 
 * public function getArticoliDellUtenteInOrdine($user, $conditions, $orderBy=null)
 *        estraggo SOLO gli articoli acquistati da UN utente in base all'ordine
 *        $conditions['Cart.user_id'] || $conditions['User.id'] obbligatorio
 *
 * public function getArticoliAcquistatiDaUtenteInOrdine($user, $conditions, $orderBy=null)
 *        estraggo SOLO gli articoli acquistati da TUTTI gli utenti in base all'ordine 
 */
class ArticlesOrder extends ArticlesOrderMultiKey {
    
    /*
     * estraggo tutti gli articoli in base all'ordine
     * Ajax::admin_box_validation_carts() estraggo tutti ArticlesOrder con pezzi_confezione > 1 
     */
    public function getArticlesOrdersInOrder($user, $conditions, $orderBy = null, $debug = false) {
    
        /*
         * estraggo 
         *  Order.owner_articles
         *  Order.owner_organization_id 
         *  Order.owner_supplier_organization_id 
         * per filtrare gli articoli (REFERENT / SUPPLIER / DES)
         */
        $owner_articles = 'REFERENT';
        $owner_organization_id = 0;
        $owner_supplier_organization_id = 0;         
        if (array_key_exists('Order.id', $conditions)) {

            if(!is_array($orderResult))
                $orderResult = $this->_getOrderById($user, $conditions['Order.id'], $debug);
        
            $owner_articles = $orderResult['Order']['owner_articles'];
            $owner_organization_id = $orderResult['Order']['owner_organization_id'];
            $owner_supplier_organization_id = $orderResult['Order']['owner_supplier_organization_id'];
        }
            
        if (isset($orderBy['ArticlesOrder']))
            $order = $orderBy['ArticlesOrder'];
        else
        if (isset($orderBy['CartPreview']))
            $order = $orderBy['CartPreview'];
        else
        if (isset($orderBy['Article']))
            $order = $orderBy['Article'];
        else
            $order = 'Article.name ASC';

        /*
         * estraggo tutti gli articoli dell'ordine
         */
        $this->unbindModel(['belongsTo' => ['Cart', 'Order']]);
        
        $options = [];
        $options['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
                                  'ArticlesOrder.stato != ' => 'N', 
                                  'Article.stato' => 'Y'];
        if(!empty($owner_organization_id) && !empty($owner_supplier_organization_id))
            $options['conditions'] += ['Article.organization_id' => $owner_organization_id,
                                       'Article.supplier_organization_id' => $owner_supplier_organization_id];
                                  
        if (isset($conditions['ArticlesOrder.pezzi_confezione']))
            $options['conditions'] += ['ArticlesOrder.pezzi_confezione > ' => $conditions['ArticlesOrder.pezzi_confezione']];
        if (isset($conditions['ArticlesOrder.qta_massima']))
            $options['conditions'] += ['ArticlesOrder.qta_massima > ' => $conditions['ArticlesOrder.qta_massima']];
        if (isset($conditions['ArticlesOrder.qta_minima']))
            $options['conditions'] += ['ArticlesOrder.qta_minima > ' => $conditions['ArticlesOrder.qta_minima']];
        if (isset($conditions['ArticlesOrder.qta_massima_order']))
            $options['conditions'] += ['ArticlesOrder.qta_massima_order > ' => $conditions['ArticlesOrder.qta_massima_order']];
        if (isset($conditions['ArticlesOrder.qta_minima_order']))
            $options['conditions'] += ['ArticlesOrder.qta_minima_order > ' => $conditions['ArticlesOrder.qta_minima_order']];
        if (isset($conditions['Article.id']))
            $options['conditions'] += ['ArticlesOrder.article_id' => $conditions['Article.id']];
        if (isset($conditions['Article.article_organization_id']))
            $options['conditions'] += ['ArticlesOrder.article_organization_id' => $conditions['Article.article_organization_id']];
        if (isset($conditions['ArticlesOrder.id']))
            $options['conditions'] += ['ArticlesOrder.order_id' => $conditions['ArticlesOrder.order_id']];
        if (isset($conditions['Order.id']))
            $options['conditions'] += ['ArticlesOrder.order_id' => $conditions['Order.id']];
        $options['recursive'] = 1;
        $options['order'] = $order;
        $results = $this->find('all', $options);

        self::d($options, $debug);
        self::d($results, $debug);

        return $results;
    }

    /*
     * estraggo articoli associati ad un ordine ed eventuali acquisti
     * ArticlesOrders::admin_index()  
     */
    public function getArticlesOrdersInOrderAndCart($user, $order_id, $opts=[], $debug = false) {

        $results = [];
        
        /*
         * D.E.S.
         */
        if(isset($opts['isTitolareDesSupplier']))
            $isTitolareDesSupplier = $opts['isTitolareDesSupplier'];
        else 
            $isTitolareDesSupplier = false;  
        $des_order_id = 0;
        if ($user->organization['Organization']['hasDes'] == 'Y') {

            App::import('Model', 'DesOrdersOrganization');
            $DesOrdersOrganization = new DesOrdersOrganization();

            $desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($user, $order_id, $debug);
            if (!empty($desOrdersOrganizationResults)) 
                $des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
        }
        
        $this->unbindModel(['belongsTo' => ['Cart', 'Order']]);
        $options = [];
        $options['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
                                  'ArticlesOrder.order_id' => $order_id,
                                  'Article.stato' => 'Y'
                                /*
                                 * prendo anche quelli  "Presente tra gli articoli da ordinare"  a NO per gli articoli gia associati
                                 * 'Article.flag_presente_articlesorders' => 'Y'
                                 */
                                ];
        $options['order'] = ['Article.name'];
        $options['recursive'] = 0;
        $results = $this->find('all', $options);
        self::d($options['conditions'], $debug);
        self::d($results, $debug);
        
        /*
         *  ctrl eventuali acquisti gia' fatti
         */
        App::import('Model', 'Cart');
        
        /*
         * se titolare DES controllo gli acquisti di tutti
         */
        if($isTitolareDesSupplier) {
            /*
            * se DES ctrl acquisti di TUTTI i GAS 
            */
            $options = [];
            $options['conditions'] = ['DesOrdersOrganization.des_id' => $user->des_id,
                                      'DesOrdersOrganization.des_order_id' => $des_order_id];
            $options['recursive'] = -1;                             
            $desOrdersOrganizationResults = $DesOrdersOrganization->find('all', $options);
            
            self::d('DesOrdersOrganization::getDesOrdersOrganization()', $debug);
            self::d($options['conditions'], $debug);
            self::d($desOrdersOrganizationResults, $debug);
           
            foreach ($results as $numResult => $result) {
            
                foreach($desOrdersOrganizationResults as $desOrdersOrganizationResult) {
                
                    $Cart = new Cart();
                    $options = [];
                    $options['conditions'] = ['Cart.organization_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['organization_id'],
                                                'Cart.order_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['order_id'],
                                                'Cart.article_organization_id' => $result['ArticlesOrder']['article_organization_id'],
                                                'Cart.article_id' => $result['ArticlesOrder']['article_id'],
                                                'Cart.deleteToReferent' => 'N',
                    ];
    
                    $options['order'] = '';
                    $options['recursive'] = -1;
                    $cartResults = $Cart->find('all', $options);
                    self::d($options['conditions'], $debug);
                    self::d($cartResults, $debug);
                    
                    /*
                     * sovrascrivo i Cart di ogni GAS nel caso di + di 1 occorrenza
                     * ma mi serve solo sapere se c'e' almeno un caso
                     */
                     if(!isset($results[$numResult]['Cart']) || empty($results[$numResult]['Cart']))
                        $results[$numResult]['Cart'] = $cartResults;
                    
                } // loop DesOrdersOrganizationResult
            }  // loop Cart
        }
        else {
            foreach ($results as $numResult => $result) {
                $Cart = new Cart();
                
                $options = [];
                $options['conditions'] =  ['Cart.organization_id' => $user->organization['Organization']['id'],
                                            'Cart.order_id' => $result['ArticlesOrder']['order_id'],
                                            'Cart.article_organization_id' => $result['ArticlesOrder']['article_organization_id'],
                                            'Cart.article_id' => $result['ArticlesOrder']['article_id'],
                                            'Cart.deleteToReferent' => 'N',
                ];

                $options['order'] = '';
                $options['recursive'] = -1;
                $cartResults = $Cart->find('all', $options);
                self::d($options['conditions'], $debug);
                self::d($cartResults, $debug);

                $results[$numResult]['Cart'] = $cartResults;
            } // end loops            
        } // end if($isTitolareDesSupplier)
            
        return $results;
    }
        
    /*
     * estraggo tutti gli articoli acquistati in base all'ordine ed EVENTUALI acquisti (Cart) di un utente
     *      $conditions['Cart.user_id'] e $conditions['User.id'] necessario!
     *    
     *  stesso risultato di getArticoliEventualiAcquistiInOrdine ma NON gestisco i filtri (non gestivo ArticleType)
     *  
     *  Deliveries::tabs()      in front-end se loggati
     */
    public function getArticoliEventualiAcquistiNoFilterInOrdine($user, $conditions, $orderBy = null) {

        if (!isset($conditions['Cart.user_id']))
            self::x("Errore ArticleOrder::getArticlesOrdersInOrderAndAnyCartsByUserId conditions['Cart.user_id'] obbligatorio");

        if (isset($orderBy['ArticlesOrder']))
            $order = $orderBy['ArticlesOrder'];
        else
        if (isset($orderBy['CartPreview']))
            $order = $orderBy['CartPreview'];
        else
        if (isset($orderBy['Article']))
            $order = $orderBy['Article'];
        else
            $order = 'Article.name ASC';

        /*
         * estraggo tutti gli articoli acquistati dall'utente
         *      ho bindModel di Cart
         */
        $this->unbindModel(['belongsTo' => ['Order']]);
        $options['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'], 'ArticlesOrder.stato != ' => 'N', 'Article.stato' => 'Y'];
        if (isset($conditions['Order.id']))
            $options['conditions'] += ['ArticlesOrder.order_id' => $conditions['Order.id']];
        if (isset($conditions['Cart.user_id']))
            $options['conditions'] += ['Cart.user_id' => $conditions['Cart.user_id']];
        if (isset($conditions['Cart.deleteToReferent']))
            $options['conditions'] += ['Cart.deleteToReferent' => $conditions['Cart.deleteToReferent']];

        $options['recursive'] = 0;
        $options['order'] = $order;
        $results = $this->find('all', $options);

        /*
         *  estraggo gli article_id per escluderli dopo
         */
        $article_ids = "";
        if (!empty($results)) {
            foreach ($results as $result)
                $article_ids .= $result['ArticlesOrder']['article_id'] . ',';
            $article_ids = substr($article_ids, 0, (strlen($article_ids) - 1));
        }

        /*
         * estraggo tutti gli articoli dell'ordine
         *      faccio unbindModel di Cart
         */
        $this->unbindModel(['belongsTo' => ['Order', 'Cart']]);
        $options['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'], 'ArticlesOrder.stato != ' => 'N', 'Article.stato' => 'Y'];
        if (!empty($article_ids))
            $options['conditions'] += ['Article.id not IN (' . $article_ids . ')'];

        if (isset($conditions['Order.id']))
            $options['conditions'] += ['ArticlesOrder.order_id' => $conditions['Order.id']];

        $options['recursive'] = 0;
        $options['order'] = $order;
        $results2 = $this->find('all', $options);


        $results = array_merge($results, $results2);

        if ($order == 'Article.name asc')
            $results = Set::sort($results, '{n}.Article.name', 'asc');

        return $results;
    }

    /*
     * estraggo tutti gli articoli di un ordine ed EVENTUALI acquisti (Cart) di un utente
     * fitrando per ArticleType, Article.name, Article.category_id
     * 
     *  Deliveries::tabsEcomm() in front-end per l'ecommerce 
     * 
     * stesso risultato di getArticoliEventualiAcquistiNoFilterInOrdine ma gestisco i filtri (non gestivo ArticleType)
     * 
     * Delivery::tabs_ajax_ecomm_carts_validation() estraggo tutti ArticlesOrder con pezzi_confezione > 1 
     */
    public function getArticoliEventualiAcquistiInOrdine($user, $orderResult, $options=[], $debug = false) {

        if(!is_array($orderResult))
            $orderResult = $this->_getOrderById($user, $orderResult, $debug);
        
        $results = [];

        try {
            if (!isset($options['order']))
                $options['order'] = 'Article.name ASC';

            $sql = "SELECT 
                        ArticlesOrder.*,Article.*";
            if (isset($options['conditions']['Cart.user_id']))
                $sql .= ",Cart.* ";
            if (isset($options['conditions']['ArticleArticleTypeId.article_type_id']))
                $sql .= ",ArticlesArticlesType.article_type_id ";
            $sql .= "FROM " .
                    Configure::read('DB.prefix') . "articles AS Article, ";
            if (isset($options['conditions']['ArticleArticleTypeId.article_type_id']))
                $sql .= Configure::read('DB.prefix') . "articles_articles_types ArticlesArticlesType, ";
            $sql .= Configure::read('DB.prefix') . "articles_orders AS ArticlesOrder ";
            if (isset($options['conditions']['Cart.user_id'])) {
                $sql .= " LEFT JOIN " . Configure::read('DB.prefix') . "carts AS Cart ON " .
                        "(Cart.organization_id = ArticlesOrder.organization_id AND Cart.order_id = ArticlesOrder.order_id AND Cart.article_organization_id = ArticlesOrder.article_organization_id AND Cart.article_id = ArticlesOrder.article_id " .
                        "AND Cart.user_id = " . $options['conditions']['Cart.user_id'] . "
                        AND Cart.deleteToReferent = 'N')";
            }
            $sql .= "WHERE 
                        ArticlesOrder.organization_id = ".$user->organization['Organization']['id']." 
                        AND ArticlesOrder.article_organization_id = Article.organization_id
                        AND ArticlesOrder.article_id = Article.id                       
                        AND ArticlesOrder.order_id = ".$orderResult['Order']['id']." 
                        AND ArticlesOrder.stato != 'N' 
                        AND Article.stato = 'Y' 
                        AND Article.organization_id = ".$orderResult['Order']['owner_organization_id']." 
                        AND Article.supplier_organization_id = ".$orderResult['Order']['owner_supplier_organization_id']."";

            if (isset($conditions['ArticlesOrder.pezzi_confezione']))
                $options['conditions'] += ['ArticlesOrder.pezzi_confezione > ' => $conditions['ArticlesOrder.pezzi_confezione']];

            if (isset($options['conditions']['ArticleArticleTypeId.article_type_id']))
                $sql .= " AND ArticlesArticlesType.organization_id = ".$orderResult['Order']['owner_organization_id']." 
                          AND ArticlesArticlesType.article_type_id IN (" . $options['conditions']['ArticleArticleTypeId.article_type_id'] . ")
                          AND Article.id = ArticlesArticlesType.article_id ";

            if (isset($options['conditions']['Article.name']))
                $sql .= " AND lower(Article.name) LIKE '%" . strtolower(addslashes($options['conditions']['Article.name'])) . "%'";

            /*
             * filtro un solo ordine AjaxGasCartComtroller::__managementCart()
             */
            if (isset($options['conditions']['Article.id']))
                $sql .= " AND Article.id = " . $options['conditions']['Article.id'];

            // Organization.hasFieldArticleCategoryId
            if (isset($options['conditions']['Article.category_id']))
                $sql .= " AND Article.category_id = " . $options['conditions']['Article.category_id'];

            $sql .= " ORDER BY " . $options['order'];
            self::d('getArticoliEventualiAcquistiInOrdine '.$sql, $debug);
            $results = $this->query($sql);

            /*
             * applico metodi afterFind()
             */
            foreach ($results as $numResult => $result) {

                /*
                 * Article
                 */
                $results[$numResult]['Article']['prezzo_'] = number_format($result['Article']['prezzo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                $results[$numResult]['Article']['prezzo_e'] = $results[$numResult]['Article']['prezzo_'] . ' &euro;';

                $qta = str_replace(".", ",", $result['Article']['qta']);
                $arrCtrlTwoZero = explode(",", $qta);
                if ($arrCtrlTwoZero[1] == '00')
                    $qta = $arrCtrlTwoZero[0];
                $results[$numResult]['Article']['qta_'] = $qta;

                /*
                 * ArticlesOrder
                 */
                $results[$numResult]['ArticlesOrder']['prezzo_'] = number_format($result['ArticlesOrder']['prezzo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                $results[$numResult]['ArticlesOrder']['prezzo_e'] = $results[$numResult]['ArticlesOrder']['prezzo_'] . ' &euro;';

                /*
                 * Cart
                 */
            } // foreach ($results as $numResult => $result) 

            self::d($results, $debug);
            
        } catch (Exception $e) {
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }

        return $results;
    }

    /*
     * estraggo tutti gli articoli di un ordine ProdGasPromotion ed EVENTUALI acquisti (Cart) di un utente
     * fitrando per ArticleType, Article.name, Article.category_id
     * 
     *  Deliveries::tabsEcomm() in front-end per l'ecommerce 
     * 
     * stesso risultato di getArticoliEventualiAcquistiNoFilterInOrdine ma gestisco i filtri (non gestivo ArticleType) 
     */

    public function getArticoliEventualiAcquistiInOrdinePromotion($user, $orderResult, $prod_gas_promotion_id, $options, $debug = false) {

        if(!is_array($orderResult))
            $orderResult = $this->_getOrderById($user, $orderResult, $debug);
        
        $results = [];

        try {
            if (!isset($options['order']))
                $options['order'] = 'Article.name ASC';

            $sql = "SELECT 
                        ArticlesOrder.*,Article.*,ProdGasArticlesPromotion.*";
            if (isset($options['conditions']['Cart.user_id']))
                $sql .= ",Cart.* ";
            if (isset($options['conditions']['ArticleArticleTypeId.article_type_id']))
                $sql .= ",ArticlesArticlesType.article_type_id ";
            $sql .= "FROM 
                    " . Configure::read('DB.prefix') . "prod_gas_articles_promotions AS ProdGasArticlesPromotion, 
                    " . Configure::read('DB.prefix') . "articles AS Article, ";
            if (isset($options['conditions']['ArticleArticleTypeId.article_type_id']))
                $sql .= Configure::read('DB.prefix') . "articles_articles_types ArticlesArticlesType, ";
            $sql .= Configure::read('DB.prefix') . "articles_orders AS ArticlesOrder ";
            if (isset($options['conditions']['Cart.user_id'])) {
                $sql .= " LEFT JOIN " . Configure::read('DB.prefix') . "carts AS Cart ON " .
                        "(Cart.organization_id = ArticlesOrder.organization_id AND Cart.order_id = ArticlesOrder.order_id AND Cart.article_organization_id = ArticlesOrder.article_organization_id AND Cart.article_id = ArticlesOrder.article_id " .
                        "AND Cart.user_id = " . $options['conditions']['Cart.user_id'] . "
            AND Cart.deleteToReferent = 'N')";
            }
            $sql .= "WHERE 
                        ArticlesOrder.organization_id = ".$user->organization['Organization']['id']." 
                        AND ProdGasArticlesPromotion.organization_id = Article.organization_id
                        AND ProdGasArticlesPromotion.article_id = Article.id                        
                        AND Article.organization_id = ArticlesOrder.article_organization_id 
                        AND ArticlesOrder.article_id = Article.id 
                        AND Article.organization_id = ".$orderResult['Order']['owner_organization_id']." 
                        AND Article.supplier_organization_id = ".$orderResult['Order']['owner_supplier_organization_id']."                      
                        AND ArticlesOrder.stato != 'N' 
                        AND Article.stato = 'Y' 
                        AND ArticlesOrder.order_id = ".$orderResult['Order']['id']." 
                        AND ProdGasArticlesPromotion.prod_gas_promotion_id = ".$prod_gas_promotion_id;
                        
            if (isset($options['conditions']['ArticleArticleTypeId.article_type_id']))
                $sql .= " AND ArticlesArticlesType.organization_id = ".$orderResult['Order']['owner_organization_id']."
                        AND ArticlesArticlesType.article_type_id IN (".$options['conditions']['ArticleArticleTypeId.article_type_id'].")
                        AND Article.id = ArticlesArticlesType.article_id ";

            if (isset($options['conditions']['Article.name']))
                $sql .= " AND lower(Article.name) LIKE '%" . strtolower(addslashes($options['conditions']['Article.name'])) . "%'";

            /*
             * filtro un solo ordine AjaxGasCartComtroller::__managementCart()
             */
            if (isset($options['conditions']['Article.organization_id']))
                $sql .= " AND Article.organization_id = " . $options['conditions']['Article.organization_id'];
            if (isset($options['conditions']['Article.id']))
                $sql .= " AND Article.id = " . $options['conditions']['Article.id'];

            // Organization.hasFieldArticleCategoryId
            if (isset($options['conditions']['Article.category_id']))
                $sql .= " AND Article.category_id = " . $options['conditions']['Article.category_id'];

            $sql .= " ORDER BY " . $options['order'];
            self::d('getArticoliEventualiAcquistiInOrdinePromotion '.$sql, $debug);
            $results = $this->query($sql);

            /*
             * applico metodi afterFind()
             */
            foreach ($results as $numResult => $result) {

                /*
                 * Article
                 */
                $results[$numResult]['Article']['prezzo_'] = number_format($result['Article']['prezzo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                $results[$numResult]['Article']['prezzo_e'] = $results[$numResult]['Article']['prezzo_'] . ' &euro;';

                $qta = str_replace(".", ",", $result['Article']['qta']);
                $arrCtrlTwoZero = explode(",", $qta);
                if ($arrCtrlTwoZero[1] == '00')
                    $qta = $arrCtrlTwoZero[0];
                $results[$numResult]['Article']['qta_'] = $qta;

                /*
                 * ArticlesOrder
                 */
                $results[$numResult]['ArticlesOrder']['prezzo_'] = number_format($result['ArticlesOrder']['prezzo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                $results[$numResult]['ArticlesOrder']['prezzo_e'] = $results[$numResult]['ArticlesOrder']['prezzo_'] . ' &euro;';

                /*
                 * ProdGasArticlesPromotion
                 */
                $results[$numResult]['ProdGasArticlesPromotion']['prezzo_unita_'] = number_format($result['ProdGasArticlesPromotion']['prezzo_unita'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                $results[$numResult]['ProdGasArticlesPromotion']['prezzo_unita_e'] = $results[$numResult]['ProdGasArticlesPromotion']['prezzo_unita_'] . ' &euro;';

                $results[$numResult]['ProdGasArticlesPromotion']['importo_'] = number_format($result['ProdGasArticlesPromotion']['importo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                $results[$numResult]['ProdGasArticlesPromotion']['importo_e'] = $results[$numResult]['ProdGasArticlesPromotion']['importo_'] . ' &euro;';

                /*
                 * differenza_da_ordinare, la qta della promozione - quanto gia' acquistato
                 */

                $differenza_da_ordinare = ($result['ProdGasArticlesPromotion']['qta'] - $result['ArticlesOrder']['qta_cart']);
                $results[$numResult]['ProdGasArticlesPromotion']['differenza_da_ordinare'] = $differenza_da_ordinare;
            } // foreach ($results as $numResult => $result) 

            self::d($results, $debug);
            
        } catch (Exception $e) {
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }

        return $results;
    }

    /*
     * estraggo SOLO gli articoli acquistati da un utente in base all'ordine
     *      $conditions['Cart.user_id'] e $conditions['User.id'] necessario!
     *
     *  Deliveries::tabsUserCart()   carrello
     */

    public function getArticoliDellUtenteInOrdine($user, $conditions, $orderBy = null, $limit = null, $debug=false) {

        if ((!isset($conditions['Cart.user_id']) || empty($conditions['Cart.user_id'])) &&
                (!isset($conditions['User.id']) || empty($conditions['User.id']))) {

            self::d($conditions, $debug);           
            self::x("Errore ArticleOrder::getArticoliDellUtenteInOrdine conditions['Cart.user_id'] o conditions['User.id'] obbligatori");
        }
         
        if (isset($orderBy['ArticlesOrder']))
            $order = $orderBy['ArticlesOrder'];
        else
        if (isset($orderBy['CartPreview']))
            $order = $orderBy['CartPreview'];
        else
        if (isset($orderBy['Article']))
            $order = $orderBy['Article'];
        else
            $order = 'Article.name ASC';

        App::import('Model', 'Cart');
        $Cart = new Cart();

        $options['conditions'] = ['Cart.organization_id' => $user->organization['Organization']['id'],
                    'ArticlesOrder.stato != ' => 'N',
                    'Article.stato' => 'Y'];
        /*
         * solo per il CartPreview (box in front-end che compare dopo un acquisto)
         *      filtro per lo stato dell'ordine
         * per il Carrello mi filtra il Tab della Consegna 
         */
        if (isset($orderBy['CartPreview'])) {
            $options['conditions'] += ['(Order.state_code = \'OPEN\' OR Order.state_code = \'RI-OPEN-VALIDATE\' OR Order.state_code = \'PROCESSED-BEFORE-DELIVERY\')'];
        }

        if (isset($conditions['Order.id']))
            $options['conditions'] += ['Cart.order_id' => $conditions['Order.id']];
        if (isset($conditions['Cart.order_id']))
            $options['conditions'] += ['Cart.order_id' => $conditions['Cart.order_id']];
        if (isset($conditions['Cart.article_id']))
            $options['conditions'] += ['Cart.article_id' => $conditions['Cart.article_id']];
        if (isset($conditions['Cart.user_id']))
            $options['conditions'] += ['Cart.user_id' => $conditions['Cart.user_id']];
        if (isset($conditions['Cart.deleteToReferent']))
            $options['conditions'] += ['Cart.deleteToReferent' => $conditions['Cart.deleteToReferent']];
        if (isset($conditions['Cart.inStoreroom']))
            $options['conditions'] += ['Cart.inStoreroom' => $conditions['Cart.inStoreroom']];

        $options['recursive'] = 0;
        $options['order'] = $order;
        if (!empty($limit))
            $options['limit'] = $limit;

        $results = $Cart->find('all', $options);
        
        self::d($options, $debug);
        self::d($results, $debug);
        
        return $results;
    }

    /*
     * estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
     *
     *  Ajax::admin_view_articles() quando e chi ha acquistato un articolo
     *  ExportDocs::admin_exportToReferent() tutti gli articoli di un ordine aggregati per produttore
     *                                          tutti gli articoli di un ordine aggregati per utenti 
     */

    public function getArticoliAcquistatiDaUtenteInOrdine($user, $conditions, $orderBy = null) {

        if (isset($orderBy['ArticlesOrder']))
            $order = $orderBy['ArticlesOrder'];
        else
        if (isset($orderBy['CartPreview']))
            $order = $orderBy['CartPreview'];
        else
        if (isset($orderBy['Article']))
            $order = $orderBy['Article'];
        else
        if (isset($orderBy['User']))
            $order = $orderBy['User'];
        else
        if (isset($orderBy['ArticleUser']))
            $order = $orderBy['ArticleUser'];
        else
        if (isset($orderBy['UserArticle']))
            $order = $orderBy['UserArticle'];
        else
        if (isset($orderBy['CartDate']))
            $order = $orderBy['CartDate'];
        else
            $order = 'Article.name ASC, User.name ';

        App::import('Model', 'Cart');
        $Cart = new Cart();

        $Cart->unbindModel(['belongsTo' => ['Order']]);
        $options['conditions'] = ['Cart.organization_id' => $user->organization['Organization']['id'],
                                    'ArticlesOrder.stato != ' => 'N',
                                    'Article.stato' => 'Y'];
            
        if (isset($conditions['ArticlesOrder.order_id']))
            $options['conditions'] += ['Cart.order_id' => $conditions['ArticlesOrder.order_id']];
        if (isset($conditions['Order.id']))
            $options['conditions'] += ['Cart.order_id' => $conditions['Order.id']];
        if (isset($conditions['ArticlesOrder.article_id']))
            $options['conditions'] += ['Cart.article_id' => $conditions['ArticlesOrder.article_id']];
        if (isset($conditions['Article.id']))
            $options['conditions'] += ['Cart.article_id' => $conditions['Article.id']];
        if (isset($conditions['Cart.user_id']))
            $options['conditions'] += ['Cart.user_id' => $conditions['Cart.user_id']];
        if (isset($conditions['Cart.deleteToReferent']))
            $options['conditions'] += ['Cart.deleteToReferent' => $conditions['Cart.deleteToReferent']];
        if (isset($conditions['User.id']))
            $options['conditions'] += ['Cart.user_id' => $conditions['User.id']];

        $options['recursive'] = 0;
        $options['order'] = $order;

        $results = $Cart->find('all', $options);
        self::d($options);
        self::d($results);
        
        return $results;
    }

    /*
     * aggiorno la ArticlesOrder.qta_cart con Cart.qta + Cart.qta_forzato
     * ctrl se ArticlesOrder.qta_massima_order > 0, se SI controllo lo ArticlesOrder.stato
     * 
     * se Ordine e' DES 
     *      ArticlesOrder.qta_massima_order indica la somma delle ArticlesOrder.qta_cart di tutti i GAS dell'ordine DES
     *          cosi' a FE c'e' per tutti il blocco se raggiunta la qta_massima_order
     */
    public function aggiornaQtaCart_StatoQtaMax($organization_id, $order_id, $article_organization_id, $article_id, $debug = false) {

        try {
            self::d("Estraggo i totali sum(cart.qta) acquisti dall'UTENTE per ogni articolo associato ad un ordine", $debug);

            $options = [];
            $options['conditions'] = ['ArticlesOrder.organization_id' => $organization_id,
                                        'ArticlesOrder.order_id' => $order_id,
                                        'ArticlesOrder.article_organization_id' => $article_organization_id,
                                        'ArticlesOrder.article_id' => $article_id];
            $options['recursive'] = 1;
            $this->unbindModel(['belongsTo' => ['Article', 'Cart']]);
            $results = $this->find('first', $options);
          
            if($results['Order']['des_order_id']>0) {
                
                self::d("E' un ordine DES", $debug);
                
                App::import('Model', 'DesOrdersOrganization');
                $DesOrdersOrganization = new DesOrdersOrganization();

                $DesOrdersOrganization->unbindModel(['belongsTo' => ['Order', 'Organization', 'De']]);
                
                $options = [];
                $options['conditions'] = ['DesOrdersOrganization.des_order_id' => $results['Order']['des_order_id']];
                $options['fields'] = ['DesOrder.des_id','DesOrder.des_supplier_id','DesOrdersOrganization.organization_id','DesOrdersOrganization.order_id'];
                $options['recursive'] = 1;
                $desOrdersOrganizationsResults = $DesOrdersOrganization->find('all', $options);

                self::d("Trovati ".count($desOrdersOrganizationsResults)." ordini associati all'ordine DES", $debug);
                self::d($desOrdersOrganizationsResults, $debug);
                
                if(!empty($desOrdersOrganizationsResults)) {
                    
                    /*
                     * calcolo la Somma di Cart.qta per tutti i GAS dell'ordine DES
                     */
                    $qta_cart_new = 0; 
                    foreach ($desOrdersOrganizationsResults as $desOrdersOrganizationsResult) {
                        $organization_id = $desOrdersOrganizationsResult['DesOrdersOrganization']['organization_id'];
                        $order_id = $desOrdersOrganizationsResult['DesOrdersOrganization']['order_id'];

                        $qta_cart_new += $this->_getSumCartQta($organization_id, $order_id, $article_organization_id, $article_id, $debug);
                    }

                    self::d("Per tutti i GAS dell'ordine DES aggiornero' ArticlesOrder.qta_cart con la somma di tutti gli acquisti dei GAS ".$qta_cart_new, $debug);
                                    
                    /* 
                     * aggiorno QtaCart e StatoQtaMax per tutti gli ordini del DES
                     */
                    foreach ($desOrdersOrganizationsResults as $desOrdersOrganizationsResult) {
                   
            /*
             * estraggo l'articlesOrders del GAS
             */           
            $options = [];
            $options['conditions'] = ['ArticlesOrder.organization_id' => $desOrdersOrganizationsResult['DesOrdersOrganization']['organization_id'],
                          'ArticlesOrder.order_id' => $desOrdersOrganizationsResult['DesOrdersOrganization']['order_id'],
                          'ArticlesOrder.article_organization_id' => $article_organization_id,
                          'ArticlesOrder.article_id' => $article_id];
            $options['recursive'] = -1;
            $ArticlesOrderResults = $this->find('first', $options);
                              
                        $ArticlesOrderResults['ArticlesOrder']['qta_cart'] = $qta_cart_new;
                        $this->_updateArticlesOrderQtaCart_StatoQtaMax($ArticlesOrderResults, $debug);
                    }
                    self::d($desSupplierResults, $debug);
                  
                } // end if(!empty($desOrdersOrganizationsResults))

            }
            else {                
                self::d("NON e' un ordine DES", $debug);
                
                $qta_cart_new = $this->_getSumCartQta($organization_id, $order_id, $article_organization_id, $article_id, $debug);
                $results['ArticlesOrder']['qta_cart'] = $qta_cart_new;
                $this->_updateArticlesOrderQtaCart_StatoQtaMax($results, $debug);
                
           } // non e' ordine DES
            
        } catch (Exception $e) {
            self::d('UtilsCrons::articlesOrdersQtaCart()'.$e, $debug);
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }

        //if($debug) exit;
    }

    private function _updateArticlesOrderQtaCart_StatoQtaMax($results, $debug=false) {            
        
        if($debug) {
            echo "ArticlesOrder.qta_massima_order ".$results['ArticlesOrder']['qta_massima_order']." ArticlesOrder.qta_cart ".$results['ArticlesOrder']['qta_cart']."\n";
        }
        
        $qta_massima_order = intval($results['ArticlesOrder']['qta_massima_order']);
        $qta_cart = intval($results['ArticlesOrder']['qta_cart']);

        /*
         * ctrl se ArticlesOrder.qta_massima_order > 0, se SI controllo lo ArticlesOrder.stato
         */
        if ($qta_massima_order > 0) {
            if ($qta_cart >= $qta_massima_order) {
                if ($results['ArticlesOrder']['stato'] != 'QTAMAXORDER') { // ho gia' settato a QTAMAXORDER e eventualmente inviato la mail
                    $results['ArticlesOrder']['stato'] = 'QTAMAXORDER';
                    $results['ArticlesOrder']['send_mail'] = 'N';  // invia mail da Cron::mailReferentiQtaMax
                }
            }
            else
            if ($qta_cart < $qta_massima_order && $results['ArticlesOrder']['stato'] == 'QTAMAXORDER') {
                $results['ArticlesOrder']['stato'] = 'Y';
                $results['ArticlesOrder']['send_mail'] = 'N'; // invia mail da Cron::mailReferentiQtaMax
            }
        } 
        else
        if ($qta_massima_order == 0) {
            if ($results['ArticlesOrder']['stato'] == 'QTAMAXORDER')
                $results['ArticlesOrder']['stato'] = 'Y';
            $results['ArticlesOrder']['send_mail'] = 'N';
        }

        unset($results['Order']);
        self::d($results, $debug);
        
        if ($this->save($results)) 
            if ($debug)
                echo "ArticleOrder::aggiornaQtaCart_StatoQtaMax() - OK aggiorno l'ArticlesOrder con order_id " . $results['ArticlesOrder']['order_id'] . " article_organization_id " . $results['ArticlesOrder']['article_organization_id'] . " article_id " . $results['ArticlesOrder']['article_id'] . " a qta_cart = " . $qta_cart . " stato " . $results['ArticlesOrder']['stato'] . "  \n";
            else
            if ($debug)
                echo "ArticleOrder::aggiornaQtaCart_StatoQtaMax() - NO aggiorno l'ArticlesOrder con order_id " . $results['ArticlesOrder']['order_id'] . " article_organization_id " . $results['ArticlesOrder']['article_organization_id'] . " article_id " . $results['ArticlesOrder']['article_id'] . " a qta_cart = " . $qta_cart . " stato " . $results['ArticlesOrder']['stato'] . "  \n";
    }
    
    private function _getSumCartQta($organization_id, $order_id, $article_organization_id, $article_id, $debug) {
            $sql = "SELECT
                        sum(Cart.qta) as totale,
                        Cart.order_id as order_id, Cart.article_organization_id as article_organization_id, Cart.article_id as article_id
                   FROM " . Configure::read('DB.prefix') . "carts as Cart
                    WHERE
                        Cart.organization_id = $organization_id
                        AND Cart.order_id = $order_id
                        AND Cart.article_organization_id = $article_organization_id
                        AND Cart.article_id = $article_id
                        AND Cart.qta_forzato = 0
                        AND Cart.deleteToReferent = 'N' ";
            //if ($debug)
            //    echo '<br />' . $sql;
            $results = $this->query($sql);

            if ($debug)
                echo "Estraggo i totali sum(cart.qta_forzato) impostati dal REFERENTE per ogni articolo associato ad un ordine <br />\n";
            $sql = "SELECT
                        sum(Cart.qta_forzato) as totale_forzato,
                        Cart.order_id as order_id, Cart.article_organization_id as article_organization_id, Cart.article_id as article_id
                   FROM " . Configure::read('DB.prefix') . "carts as Cart
                   WHERE
                        Cart.organization_id = $organization_id
                        AND Cart.order_id = $order_id
                        AND Cart.article_organization_id = $article_organization_id
                        AND Cart.article_id = $article_id
                        AND Cart.qta_forzato > 0
                        AND Cart.deleteToReferent = 'N' ";
            //if ($debug)
            //    echo '<br />' . $sql;
            $forzatoResults = $this->query($sql);

            /*
             * merge tra i 2 result
             */
            $qta_cart_new = 0;
            if (!isset($results[0][0]['totale']))
                $totale = 0;
            else
                $totale = $results[0][0]['totale'];
            if (!isset($forzatoResults[0][0]['totale_forzato']))
                $totale_forzato = 0;
            else
                $totale_forzato = $forzatoResults[0][0]['totale_forzato'];
            $qta_cart_new = ($totale + $totale_forzato);

            if ($debug)
                echo "Per il GAS $organization_id e l'ordine $order_id, articolo $article_organization_id $article_id la somma di tutti gli acquisti (Cart.qta/qta_forzato) e' $qta_cart_new <br />\r";
                
            return $qta_cart_new;
    }
        
    /*
     * cancella articlesOrders 
     * eventuali Cart
     * DES se isTitolareDesSupplier => deleteArticlesOrderAllOrganizations
     */
    public function delete_and_carts($user, $order_id, $article_organization_id, $article_id, $des_order_id=0, $isTitolareDesSupplier, $debug=false) {
        
        $msg = '';

        self::d('ArticlesOrder::delete_and_carts', $debug);
        self::d(' - organization.id - '.$user->organization['Organization']['id'], $debug);
        self::d(' - order_id - '.$order_id, $debug);
        self::d(' - article_organization_id - '.$article_organization_id, $debug);
        self::d(' - article_id - '.$article_id, $debug);
        
        App::import('Model', 'Cart');
        $Cart = new Cart;
        
        App::import('Model', 'SummaryOrder');

        /*
         * cancello ArticlesOrders
         */     
        if (!$this->exists($user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id)) {
            return __('msg_error_params');
        }

        if (!$this->delete($user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id)) {
            return "<br />Articolo associato all'ordine ($order_id $article_id) non cancellato!";
        }

        /*
         * cancello tutti gli eventuali acquisti (Carts), lo esegue gia' articles_orders_Trigger
         */
        $Cart->delete($user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id);

        /*
         *  D E S, se isTitolareDesSupplier cancello i ArticlesOrder di tutti i GAS
         */
        if ($user->organization['Organization']['hasDes'] == 'Y' && $isTitolareDesSupplier) {

            App::import('Model', 'DesOrder');
            $DesOrder = new DesOrder;
            
            $articles_orders_key = ['organization_id' => $user->organization['Organization']['id'],
                                    'order_id' => $order_id,
                                    'article_organization_id' => $article_organization_id,
                                    'article_id' => $article_id];
            $DesOrder->deleteArticlesOrderAllOrganizations($user, $des_order_id, $articles_orders_key, $debug);
        }
        
        return $msg;        
    }   
    
    public $validate = array(
        'organization_id' => array(
            'numeric' => array(
                'rule' => ['numeric'],
            ),
        ),
        'article_organization_id' => array(
            'numeric' => array(
                'rule' => ['numeric'],
            ),
        ),
        'article_id' => array(
            'numeric' => array(
                'rule' => ['numeric'],
            ),
        ),
        'order_id' => array(
            'numeric' => array(
                'rule' => ['numeric'],
            ),
        ),
        'prezzo' => array(
            'rule' => array('decimal', 2),
            'message' => "Indica il prezzo dell'articolo con un valore numerico con 2 decimali (1,00)",
        ),
        'pezzi_confezione' => array(
            'notempty' => array(
                'rule' => array('naturalNumber', false),
            ),
        ),
        'qta_minima' => array(
            'notempty' => array(
                'rule' => array('notBlank', false),
                'message' => 'Indica la quantità minima che un gasista può acquistare',
            ),
            'numeric' => array(
                'rule' => array('naturalNumber', false),
                'message' => "La quantità minima che un gasista può acquistare dev'essere indicata con un valore numerico maggiore di zero",
                'allowEmpty' => false,
            ),
        ),
        'qta_massima' => array(
            'notempty' => array(
                'rule' => array('notBlank', false),
                'message' => 'Indica la quantità massima che un gasista può acquistare',
            ),
            'numeric' => array(
                'rule' => array('numeric', false),
                'message' => "La quantità massima che un gasista può acquistare dev'essere indicata con un valore numerico",
                'allowEmpty' => true,
            ),
        ),
        'qta_minima_order' => array(
            'notempty' => array(
                'rule' => array('notBlank', false),
                'message' => "Indica la quantità minima rispetto a tutti gli acquisti dell'ordine",
            ),
            'numeric' => array(
                'rule' => array('numeric', false),
                'message' => "La quantità minima rispetto a tutti gli acquisti dell'ordine dev'essere indicata con un valore numerico",
                'allowEmpty' => true,
            ),
        ),
        'qta_massima_order' => array(
            'notempty' => array(
                'rule' => array('notBlank', false),
                'message' => "Indica la quantità massima rispetto a tutti gli acquisti dell'ordine",
            ),
            'numeric' => array(
                'rule' => array('numeric', false),
                'message' => "La quantità massima rispetto a tutti gli acquisti dell'ordine dev'essere indicata con un valore numerico",
                'allowEmpty' => true,
            ),
        ),
        'qta_multipli' => array(
            'notempty' => array(
                'rule' => array('naturalNumber', false),
            ),
        ),
    );
    
    public $belongsTo = [
        'Article' => [
            'className' => 'Article',
            'foreignKey' => 'article_id',
            'conditions' => 'Article.organization_id = ArticlesOrder.article_organization_id',
            'fields' => '',
            'order' => ''
        ],
        'Order' => [
            'className' => 'Order',
            'foreignKey' => 'order_id',
            'conditions' => 'Order.organization_id = ArticlesOrder.organization_id',
            'fields' => '',
            'order' => ''
        ],
        'Cart' => [
            'className' => 'Cart',
            'foreignKey' => '',
            'conditions' => 'Cart.organization_id = ArticlesOrder.organization_id AND Cart.order_id = ArticlesOrder.order_id AND Cart.article_organization_id = ArticlesOrder.article_organization_id AND Cart.article_id = ArticlesOrder.article_id',
            'fields' => '',
            'order' => '',
        ],
    ];

    public function afterFind($results, $primary = true) {

        foreach ($results as $key => $val) {
            if (!empty($val)) {
                if (isset($val['ArticlesOrder']['prezzo'])) {
                    $results[$key]['ArticlesOrder']['prezzo_'] = number_format($val['ArticlesOrder']['prezzo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                    $results[$key]['ArticlesOrder']['prezzo_e'] = $results[$key]['ArticlesOrder']['prezzo_'] . ' &euro;';
                } else
                /*
                 * se il find() arriva da $hasAndBelongsToMany
                 */
                if (isset($val['prezzo'])) {
                    $results[$key]['prezzo_'] = number_format($val['prezzo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                    $results[$key]['prezzo_e'] = $results[$key]['prezzo_'] . ' &euro;';
                }
            }
        }
        return $results;
    }
    
    public function beforeSave($options = []) {
        if (!empty($this->data['ArticlesOrder']['prezzo']))
            $this->data['ArticlesOrder']['prezzo'] = $this->importoToDatabase($this->data['ArticlesOrder']['prezzo']);

        return true;
    }
}