<?php

App::uses('AppModel', 'Model');
App::import('Model', 'ArticlesOrderMultiKey');

/*
 * DROP TRIGGER IF EXISTS `k_articles_orders_Trigger`;
 * DELIMITER |
 * CREATE TRIGGER `k_articles_orders_Trigger` AFTER DELETE ON `k_articles_orders`
 * FOR EACH ROW BEGIN
 * delete from k_carts where order_id = old.order_id and article_id = old.article_id  and article_organization_id = old.article_organization_id and organization_id = old.organization_id;
 * END
 * |
 * DELIMITER ;
 */

/**
 * public function getArticlesOrdersInOrder($user, $conditions, $orderBy=null) 
 * 		  estraggo tutti gli articoli in base all'ordine
 * 
 * public function getArticoliEventualiAcquistiNoFilterInOrdine($user, $conditions, $orderBy=null)
 * 		  estraggo tutti gli articoli acquistati in base all'ordine ed EVENTUALI Cart di UN utente 
 * 		  $conditions['Cart.user_id']  obbligatorio
 * 
 * public function getArticoliDellUtenteInOrdine($user, $conditions, $orderBy=null)
 * 		  estraggo SOLO gli articoli acquistati da UN utente in base all'ordine
 * 		  $conditions['Cart.user_id'] || $conditions['User.id'] obbligatorio
 *
 * public function getArticoliAcquistatiDaUtenteInOrdine($user, $conditions, $orderBy=null)
 * 		  estraggo SOLO gli articoli acquistati da TUTTI gli utenti in base all'ordine 
 */
class ArticlesOrder extends ArticlesOrderMultiKey {
    /*
     * estraggo tutti gli articoli in base all'ordine
     * Ajax::admin_box_validation_carts() estraggo tutti ArticlesOrder con pezzi_confezione > 1 
     */

    public function getArticlesOrdersInOrder($user, $conditions, $orderBy = null) {

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
        $this->unbindModel(array('belongsTo' => array('Cart', 'Order')));
        $options['conditions'] = array('ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
            'ArticlesOrder.stato != ' => 'N',
            'Article.stato' => 'Y'
        );

        if (isset($conditions['ArticlesOrder.pezzi_confezione']))
            $options['conditions'] += array('ArticlesOrder.pezzi_confezione > ' => $conditions['ArticlesOrder.pezzi_confezione']);
        if (isset($conditions['ArticlesOrder.qta_massima']))
            $options['conditions'] += array('ArticlesOrder.qta_massima > ' => $conditions['ArticlesOrder.qta_massima']);
        if (isset($conditions['ArticlesOrder.qta_minima']))
            $options['conditions'] += array('ArticlesOrder.qta_minima > ' => $conditions['ArticlesOrder.qta_minima']);
        if (isset($conditions['ArticlesOrder.qta_massima_order']))
            $options['conditions'] += array('ArticlesOrder.qta_massima_order > ' => $conditions['ArticlesOrder.qta_massima_order']);
        if (isset($conditions['ArticlesOrder.qta_minima_order']))
            $options['conditions'] += array('ArticlesOrder.qta_minima_order > ' => $conditions['ArticlesOrder.qta_minima_order']);
        if (isset($conditions['Order.id']))
            $options['conditions'] += array('ArticlesOrder.order_id' => $conditions['Order.id']);
        if (isset($conditions['Article.id']))
            $options['conditions'] += array('ArticlesOrder.article_id' => $conditions['Article.id']);
        $options['recursive'] = 1;
        $options['order'] = $order;
        $results = $this->find('all', $options);

        /*
          echo "<pre>";
          print_r($options);
          print_r($results);
          echo "</pre>";
         */

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
            die("Errore getArticlesOrdersInOrderAndAnyCartsByUserId conditions['Cart.user_id'] obbligatorio");

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
         * 		ho bindModel di Cart
         */
        $this->unbindModel(array('belongsTo' => array('Order')));
        $options['conditions'] = array('ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
            'ArticlesOrder.stato != ' => 'N',
            'Article.stato' => 'Y'
        );
        if (isset($conditions['Order.id']))
            $options['conditions'] += array('ArticlesOrder.order_id' => $conditions['Order.id']);
        if (isset($conditions['Cart.user_id']))
            $options['conditions'] += array('Cart.user_id' => $conditions['Cart.user_id']);
        if (isset($conditions['Cart.deleteToReferent']))
            $options['conditions'] += array('Cart.deleteToReferent' => $conditions['Cart.deleteToReferent']);

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
         * 		faccio unbindModel di Cart
         */
        $this->unbindModel(array('belongsTo' => array('Order', 'Cart')));
        $options['conditions'] = array('ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
            'ArticlesOrder.stato != ' => 'N',
            'Article.stato' => 'Y');
        if (!empty($article_ids))
            $options['conditions'] += array('Article.id not IN (' . $article_ids . ')');

        if (isset($conditions['Order.id']))
            $options['conditions'] += array('ArticlesOrder.order_id' => $conditions['Order.id']);

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
     * 	Deliveries::tabsEcomm() in front-end per l'ecommerce 
     * 
     * stesso risultato di getArticoliEventualiAcquistiNoFilterInOrdine ma gestisco i filtri (non gestivo ArticleType)
     * 
     * Delivery::tabs_ajax_ecomm_carts_validation() estraggo tutti ArticlesOrder con pezzi_confezione > 1 
     */

    public function getArticoliEventualiAcquistiInOrdine($user, $options, $debug = false) {

        $results = array();

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
                        ArticlesOrder.organization_id = " . $user->organization['Organization']['id'] . " 
                        AND Article.organization_id = ArticlesOrder.article_organization_id 
                        AND ArticlesOrder.article_id = Article.id 
                        AND ArticlesOrder.stato != 'N' 
                        AND Article.stato = 'Y' 
                        AND ArticlesOrder.order_id = " . $options['conditions']['ArticlesOrder.order_id'];

            if (isset($conditions['ArticlesOrder.pezzi_confezione']))
                $options['conditions'] += array('ArticlesOrder.pezzi_confezione > ' => $conditions['ArticlesOrder.pezzi_confezione']);

            if (isset($options['conditions']['ArticleArticleTypeId.article_type_id']))
                $sql .= " AND ArticlesArticlesType.organization_id = " . $user->organization['Organization']['id'] . "
                        AND ArticlesArticlesType.article_type_id IN (" . $options['conditions']['ArticleArticleTypeId.article_type_id'] . ")
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
            // echo '<br />getArticoliEventualiAcquistiInOrdine '.$sql;
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

            /*
              echo "<pre>";
              print_r($results);
              echo "</pre>";
             */
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
     * 	Deliveries::tabsEcomm() in front-end per l'ecommerce 
     * 
     * stesso risultato di getArticoliEventualiAcquistiNoFilterInOrdine ma gestisco i filtri (non gestivo ArticleType) 
     */

    public function getArticoliEventualiAcquistiInOrdinePromotion($user, $prod_gas_promotion_id, $options, $debug = false) {

        $results = array();

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
                        ProdGasArticlesPromotion.supplier_id = Article.supplier_id
                        AND ProdGasArticlesPromotion.prod_gas_article_id = Article.prod_gas_article_id
                        AND ArticlesOrder.organization_id = " . $user->organization['Organization']['id'] . " 
                        AND Article.organization_id = ArticlesOrder.article_organization_id 
                        AND ArticlesOrder.article_id = Article.id 
                        AND ArticlesOrder.stato != 'N' 
                        AND Article.stato = 'Y' 
                        AND ArticlesOrder.order_id = " . $options['conditions']['ArticlesOrder.order_id'] . " 
                        AND ProdGasArticlesPromotion.prod_gas_promotion_id = " . $prod_gas_promotion_id;

            if (isset($options['conditions']['ArticleArticleTypeId.article_type_id']))
                $sql .= " AND ArticlesArticlesType.organization_id = " . $user->organization['Organization']['id'] . "
                        AND ArticlesArticlesType.article_type_id IN (" . $options['conditions']['ArticleArticleTypeId.article_type_id'] . ")
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
            // echo '<br />getArticoliEventualiAcquistiInOrdinePromotion '.$sql;
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

            /*
              echo "<pre>";
              print_r($results);
              echo "</pre>";
             */
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

    public function getArticoliDellUtenteInOrdine($user, $conditions, $orderBy = null, $limit = null) {

        if ((!isset($conditions['Cart.user_id']) || empty($conditions['Cart.user_id'])) &&
                (!isset($conditions['User.id']) || empty($conditions['User.id'])))
            die("Errore getArticlesOrdersInOrderAndCartsByUserId conditions['Cart.user_id'] o conditions['User.id'] obbligatori");

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

        $options['conditions'] = array('Cart.organization_id' => $user->organization['Organization']['id'],
            'ArticlesOrder.stato != ' => 'N',
            'Article.stato' => 'Y',
        );
        /*
         * solo per il CartPreview (box in front-end che compare dopo un acquisto)
         * 		filtro per lo stato dell'ordine
         * per il Carrello mi filtra il Tab della Consegna 
         */
        if (isset($orderBy['CartPreview'])) {
            $options['conditions'] += array('(Order.state_code = \'OPEN\' OR Order.state_code = \'RI-OPEN-VALIDATE\' OR Order.state_code = \'PROCESSED-BEFORE-DELIVERY\')');
        }

        if (isset($conditions['Order.id']))
            $options['conditions'] += array('Cart.order_id' => $conditions['Order.id']);
        if (isset($conditions['Cart.order_id']))
            $options['conditions'] += array('Cart.order_id' => $conditions['Cart.order_id']);
        if (isset($conditions['Cart.article_id']))
            $options['conditions'] += array('Cart.article_id' => $conditions['Cart.article_id']);
        if (isset($conditions['Cart.user_id']))
            $options['conditions'] += array('Cart.user_id' => $conditions['Cart.user_id']);
        if (isset($conditions['Cart.deleteToReferent']))
            $options['conditions'] += array('Cart.deleteToReferent' => $conditions['Cart.deleteToReferent']);
        if (isset($conditions['Cart.inStoreroom']))
            $options['conditions'] += array('Cart.inStoreroom' => $conditions['Cart.inStoreroom']);

        $options['recursive'] = 0;
        $options['order'] = $order;
        if (!empty($limit))
            $options['limit'] = $limit;

        $results = $Cart->find('all', $options);
        /*
          echo "<pre>";
          print_r($options);
          print_r($results);
          echo "</pre>";
         */
        return $results;
    }

    /*
     * estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
     *
     *  Ajax::admin_view_articles() quando e chi ha acquistato un articolo
     *  ExportDocs::admin_exportToReferent() tutti gli articoli di un ordine aggregati per produttore
     *  										tutti gli articoli di un ordine aggregati per utenti 
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

        $Cart->unbindModel(array('belongsTo' => array('Order')));
        $options['conditions'] = array('Cart.organization_id' => $user->organization['Organization']['id'],
            'ArticlesOrder.stato != ' => 'N',
            'Article.stato' => 'Y'
        );
        if (isset($conditions['ArticlesOrder.order_id']))
            $options['conditions'] += array('Cart.order_id' => $conditions['ArticlesOrder.order_id']);
        if (isset($conditions['Order.id']))
            $options['conditions'] += array('Cart.order_id' => $conditions['Order.id']);
        if (isset($conditions['ArticlesOrder.article_id']))
            $options['conditions'] += array('Cart.article_id' => $conditions['ArticlesOrder.article_id']);
        if (isset($conditions['Article.id']))
            $options['conditions'] += array('Cart.article_id' => $conditions['Article.id']);
        if (isset($conditions['Cart.user_id']))
            $options['conditions'] += array('Cart.user_id' => $conditions['Cart.user_id']);
        if (isset($conditions['Cart.deleteToReferent']))
            $options['conditions'] += array('Cart.deleteToReferent' => $conditions['Cart.deleteToReferent']);
        if (isset($conditions['User.id']))
            $options['conditions'] += array('Cart.user_id' => $conditions['User.id']);

        $options['recursive'] = 0;
        $options['order'] = $order;

        $results = $Cart->find('all', $options);
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
            if ($debug)
                echo "Estraggo i totali sum(cart.qta) acquisti dall'UTENTE per ogni articolo associato ad un ordine <br />\n";

            $options = array();
            $options['conditions'] = array('ArticlesOrder.organization_id' => $organization_id,
                                            'ArticlesOrder.order_id' => $order_id,
                                            'ArticlesOrder.article_organization_id' => $article_organization_id,
                                            'ArticlesOrder.article_id' => $article_id);
            $options['recursive'] = 1;
            $this->unbindModel(array('belongsTo' => array('Article', 'Cart')));
            $results = $this->find('first', $options);
          
            if($results['Order']['des_order_id']>0) {
                
                if ($debug)
                    echo "E' un ordine DES <br />\n";
                
                App::import('Model', 'DesOrdersOrganization');
                $DesOrdersOrganization = new DesOrdersOrganization();

                $DesOrdersOrganization->unbindModel(array('belongsTo' => array('Order', 'Organization', 'De')));
                
                $options = array();
                $options['conditions'] = array('DesOrdersOrganization.des_order_id' => $results['Order']['des_order_id']);
                $options['fields'] = array('DesOrder.des_id','DesOrder.des_supplier_id','DesOrdersOrganization.organization_id','DesOrdersOrganization.order_id');
                $options['recursive'] = 1;
                $desOrdersOrganizationsResults = $DesOrdersOrganization->find('all', $options);

                if ($debug)
                    echo "Trovati ".count($desOrdersOrganizationsResults)." ordini associati all'ordine DES <br />\n";                
                /*
                echo "<pre>";
                print_r($desOrdersOrganizationsResults);
                echo "</pre>";                
                 */
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

                    if ($debug)
                        echo "Per tutti i GAS dell'ordine DES aggiornero' ArticlesOrder.qta_cart con la somma di tutti gli acquisti dei GAS ".$qta_cart_new."<br />\n"; 
                                    
                    /* 
                     * aggiorno tutti gli ordini del DES
                     */
                    foreach ($desOrdersOrganizationsResults as $desOrdersOrganizationsResult) {
                        
                        $results['ArticlesOrder']['organization_id'] = $desOrdersOrganizationsResult['DesOrdersOrganization']['organization_id'];
                        $results['ArticlesOrder']['order_id'] = $desOrdersOrganizationsResult['DesOrdersOrganization']['order_id'];
                        
                        $results['ArticlesOrder']['qta_cart'] = $qta_cart_new;
                        $this->_updateArticlesOrderQtaCart_StatoQtaMax($results, $debug);
                    }
                    /*
                    echo "<pre>";
                    print_r($desSupplierResults);
                    echo "</pre>";                    
                     */
                } // end if(!empty($desOrdersOrganizationsResults))

            }
            else {                
                if ($debug)
                    echo "NON e' un ordine DES <br />\n";
                
                $qta_cart_new = $this->_getSumCartQta($organization_id, $order_id, $article_organization_id, $article_id, $debug);
                $results['ArticlesOrder']['qta_cart'] = $qta_cart_new;
                $this->_updateArticlesOrderQtaCart_StatoQtaMax($results, $debug);
                
           } // non e' ordine DES
            
        } catch (Exception $e) {
            if ($debug)
                echo '<br />UtilsCrons::articlesOrdersQtaCart()<br />' . $e;
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }

        //if($debug) exit;
    }

    private function _updateArticlesOrderQtaCart_StatoQtaMax($results, $debug) {            
        /*
         * ctrl se ArticlesOrder.qta_massima_order > 0, se SI controllo lo ArticlesOrder.stato
         */
        if ($results['ArticlesOrder']['qta_massima_order'] > 0) {
            if ($results['ArticlesOrder']['qta_cart'] >= $results['ArticlesOrder']['qta_massima_order']) {
                $results['ArticlesOrder']['stato'] = 'QTAMAXORDER';
                $results['ArticlesOrder']['send_mail'] = 'N';
            }
            else
            if ($results['ArticlesOrder']['qta_cart'] < $results['ArticlesOrder']['qta_massima_order'] && $results['ArticlesOrder']['stato'] == 'QTAMAXORDER') {
                $results['ArticlesOrder']['stato'] = 'Y';
                $results['ArticlesOrder']['send_mail'] = 'N';
            }
        } else
        if ($results['ArticlesOrder']['qta_massima_order'] == 0) {
            if ($results['ArticlesOrder']['stato'] == 'QTAMAXORDER')
                $results['ArticlesOrder']['stato'] = 'Y';
            $results['ArticlesOrder']['send_mail'] = 'N';
        }

        unset($results['Order']);
        /*
        if($debug) {
            echo "<pre>ArticleOrder::aggiornaQtaCart_StatoQtaMax() <br />\n";
            print_r($results);
            echo "</pre>";
        } 
        */
        if ($this->save($results)) 
            if ($debug)
                echo "ArticleOrder::aggiornaQtaCart_StatoQtaMax() -	OK aggiorno l'ArticlesOrder con order_id " . $results['ArticlesOrder']['order_id'] . " article_organization_id " . $results['ArticlesOrder']['article_organization_id'] . " article_id " . $results['ArticlesOrder']['article_id'] . " a qta_cart = " . $results['ArticlesOrder']['qta_cart'] . " stato " . $results['ArticlesOrder']['stato'] . "  \n";
            else
            if ($debug)
                echo "ArticleOrder::aggiornaQtaCart_StatoQtaMax() -	NO aggiorno l'ArticlesOrder con order_id " . $results['ArticlesOrder']['order_id'] . " article_organization_id " . $results['ArticlesOrder']['article_organization_id'] . " article_id " . $results['ArticlesOrder']['article_id'] . " a qta_cart = " . $results['ArticlesOrder']['qta_cart'] . " stato " . $results['ArticlesOrder']['stato'] . "  \n";
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
        
    public $validate = array(
        'organization_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
        'article_organization_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
        'article_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
        'order_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
            ),
        ),
        'prezzo' => array(
            'rule' => array('decimal', 2),
            'message' => "Indica il prezzo dell'articolo con un valore numerico",
        ),
        'pezzi_confezione' => array(
            'notempty' => array(
                'rule' => array('naturalNumber', false),
            ),
        ),
        'qta_minima' => array(
            'notempty' => array(
                'rule' => array('notempty', false),
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
                'rule' => array('notempty', false),
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
                'rule' => array('notempty', false),
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
                'rule' => array('notempty', false),
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
    public $belongsTo = array(
        'Article' => array(
            'className' => 'Article',
            'foreignKey' => 'article_id',
            'conditions' => 'Article.organization_id = ArticlesOrder.article_organization_id',
            'fields' => '',
            'order' => ''
        ),
        'Order' => array(
            'className' => 'Order',
            'foreignKey' => 'order_id',
            'conditions' => 'Order.organization_id = ArticlesOrder.organization_id',
            'fields' => '',
            'order' => ''
        ),
        'Cart' => array(
            'className' => 'Cart',
            'foreignKey' => '',
            'conditions' => 'Cart.organization_id = ArticlesOrder.organization_id AND Cart.order_id = ArticlesOrder.order_id AND Cart.article_organization_id = ArticlesOrder.article_organization_id AND Cart.article_id = ArticlesOrder.article_id',
            'fields' => '',
            'order' => '',
        ),
    );

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
}
