<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'ImageTool');
//require_once ('/var/www/portalgas' . DS . 'google' . DS . 'autoload.php');

class TestsController extends AppController {

    public $helpers = ['App',
        'Html',
        'Form',
        'Time',
        'Ajax',
        'Tabs'];

    public function beforeFilter() {

        parent::beforeFilter();

        /* ctrl ACL */
        if (!$this->isRoot()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
    }

    public function admin_index() {
        /*
          App::import('Model', 'Order');
          $Order = new Order();

          $order_id = '4781';
          $debug = true;
          $importo_totale = $Order->getTotImporto($this->user, $this->order_id, $debug);
         */ 
			
		/*
		 * aggiorno le SummaryOrder.importo_pagato con le richieste di pagamento SALDATE
		$sql = "SELECT r.organization_id,p.importo_dovuto,p.importo_richiesto,p.importo_pagato,p.stato,p.user_id,o.order_id 
			FROM 
				k_request_payments r, 
				k_request_payments_orders o, 
				k_summary_payments p 
			WHERE 
				r.stato_elaborazione = 'OPEN' 
				and p.stato = 'PAGATO'
				and r.organization_id = o.organization_id
				and r.organization_id = p.organization_id
				and r.id = o.request_payment_id
				and r.id = p.request_payment_id 
			ORDER BY r.organization_id,o.order_id,p.user_id";
		$results = $this->Test->query($sql);
		foreach($results as $result) {
			*/
			/*	
			 echo "<pre>";
			 print_r($result);
			 echo "</pre>";
			*/
			/*
			$organization_id = $result['r']['organization_id'];
			$user_id = $result['p']['user_id'];
			$order_id = $result['o']['order_id'];

			$sql = "UPDATE k_summary_orders SET importo_pagato = importo WHERE importo_pagato = '0.00' and organization_id = $organization_id and user_id = $user_id and order_id = $order_id";
			echo "<br />".$sql;
			$exeResults = $this->Test->query($sql);
		}
		*/
		 
    }

	/*
	 * aggiorna i summaryOrder di ordini saldati
	 */
    public function admin_v2_allinea_summary_order_saldate() {
      
        App::import('Model', 'Organization');
        $Organization = new Organization();

		$options = [];
		$options['conditions'] = ['Template.payToDelivery IN' => ['POST','ON-POST']];
		$options['recursive'] = 0;
		$organizationResults = $Organization->find('all', $options);
       
		foreach($organizationResults as $organizationResult) {
			
			$sql = "SELECT RequestPaymentsOrder.order_id, SummaryPayment.user_id, SummaryPayment.request_payment_id 
				    FROM ".Configure::read('DB.prefix')."summary_payments SummaryPayment, ".Configure::read('DB.prefix')."request_payments_orders RequestPaymentsOrder 
					WHERE SummaryPayment.organization_id = ".$organizationResult['Organization']['id']." and SummaryPayment.organization_id = RequestPaymentsOrder.organization_id 
					and SummaryPayment.request_payment_id = RequestPaymentsOrder.request_payment_id and SummaryPayment.stato = 'PAGATO'";
			$results = $this->Test->query($sql);
			self::d($results);
			foreach($results as $result) {
				$sql = "update ".Configure::read('DB.prefix')."summary_orders 
						SET importo_pagato = importo, saldato_a = 'TESORIERE', modalita = 'CONTANTI' 
						WHERE organization_id = ".$organizationResult['Organization']['id']." 
						and order_id = ".$result['RequestPaymentsOrder']['order_id']." 
						and user_id = ".$result['SummaryPayment']['user_id'].";";
				self::dd($sql);
				$exeResults = $this->Test->query($sql);
			}		
		} // loops Organization
		
		$this->render('admin_index');
    }
	
	/*
	 * aggiorna i Order.state_code di ordini legati a richieste di pagamento
	 */
    public function admin_v2_allinea_orders_state_code_request_payment() {
      
        App::import('Model', 'RequestPayment');
        $RequestPayment = new RequestPayment();    
		
		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;	
				
        App::import('Model', 'Organization');
        $Organization = new Organization();

		$options = [];
		$options['conditions'] = ['Template.payToDelivery IN' => ['POST','ON-POST']];
		$options['recursive'] = 0;
		$organizationResults = $Organization->find('all', $options);
       
		foreach($organizationResults as $organizationResult) {
			
			$options = [];
			$options['conditions'] = ['RequestPayment.organization_id' => $organizationResult['Organization']['id']];
			$options['recursive'] = -1;
			$requestPaymentResults = $RequestPayment->find('all', $options);
		
			foreach($requestPaymentResults as $requestPaymentResult) {
		 
        $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organizationResult['Organization']['id']]);

				$RequestPaymentsOrder->setOrdersStateCodeByRequestPaymentId($tmp_user, $requestPaymentResult['RequestPayment']['id']);
			
			}	// loops requestPaymentResults	
		} // loops Organization
		
		$this->render('admin_index');
    }
	
    /*
	 * DES 
	 *	1 	Rete D.E.S. Ecoredia
	 *		15 	Gas Ivrea
	 *		19 	Gas Dora Baltea
	 *		20 	Gas Valchiusella 	
	 *	2 	RES.TO
	 *		 	1 	La Cavagnetta
	 *			3 	Arcoiris
	 *			4 	Villargas
	 *			5 	GassePiossasco 
	 *			8 	Gas Buttigliera		
	 *			9 	Gas Almese
	 *			12 	Gas Gastaldi
	 *			14 	Gas Trana
	 *			18 	Gas ValDellaTorre	 
	 */
     public function admin_setSuppliersOrganizationOwnerArticles() {
	
		$debug = true;
	
		App::import('Model', 'DesSupplier');
		$DesSupplier = new DesSupplier;
		
		$supplier_id=0; // 306; // Cooperativa Agricola Valli Unite (gas 15, 19)
		
		$DesSupplier->setSupplierOrganizationOwnerArticles($this->user, $supplier_id, $debug);

		exit;		
	 }
	 
    /*
     * $state_code = PROCESSED-ON-DELIVERY / TO-PAYMENT
     * simulo $state_code_next = $OrderLifeCycle->stateCodeAfter($user, 0, $state_code);
     */
    public function admin_TemplatesOrdersState($state_code='PROCESSED-ON-DELIVERY') {
		
		$template_id = $this->user->organization['Organization']['template_id'];
	
		App::import('Model', 'TemplatesOrdersState');
		$TemplatesOrdersState = new TemplatesOrdersState;

		$options = [];
		$options['conditions'] = ['TemplatesOrdersState.template_id' => $template_id,
								  'TemplatesOrdersState.state_code' => $state_code,
								  'TemplatesOrdersState.group_id' => Configure::read('group_id_super_referent')]; // prendo quello di un gruppo tanto solo = 
		$options['fields'] = ['TemplatesOrdersState.sort'];
		$options['recursive'] = -1;
		$results = $TemplatesOrdersState->find('first', $options);
		
		/*
		 * ottengo i successivi e restituisco il primo
		 */
		$options = [];
		$options['conditions'] = ['TemplatesOrdersState.template_id' => $template_id,
								  'TemplatesOrdersState.sort > ' => $results['TemplatesOrdersState']['sort'],
								  'TemplatesOrdersState.group_id' => Configure::read('group_id_super_referent')]; // prendo quello di un gruppo tanto solo = 
		$options['order'] = ['TemplatesOrdersState.sort asc'];
		$options['recursive'] = -1;
		$results = $TemplatesOrdersState->find('all', $options);
		
		$state_code_next = $results[0]['TemplatesOrdersState']['state_code'];		 
		
         echo "<pre>";
         print_r($state_code_next);
         echo "</pre>";	         		

		exit;
		return $state_code_next;
		
	}
	    
    public function admin_sistema_dati_db() {
                  
          App::import('Model', 'Order');
          $Order = new Order();
           $sql = "SELECT a.name, a.organization_id, a.id FROM k_articles a 
	        LEFT JOIN k_stat_articles_orders o ON (
	            o.article_organization_id = a.organization_id and	
	            o. article_id = a.id) WHERE o.name is null";
         self::d($sql, false);
            
             $results = $Order->query($sql);
         echo '<br />'.count($results);
         
  /*
         echo "<pre>";
         print_r($result);
         echo "</pre>";
   */       
         foreach($results as $numResult => $result) {
         	$name = $result['a']['name'];
         	$sql = "update k_stat_articles_orders set name = '".addslashes($name)."' WHERE organization_id = ".$result['a']['organization_id']." and article_id = ".$result['a']['id']." and name is null";
			echo "<br />".$numResult.')  '.$sql;
			
			$updateResults = $Order->query($sql);
         }
         
    }
    
    public function admin_aggiornaQtaCart_StatoQtaMax() {
        
          App::import('Model', 'ArticlesOrder');
          $ArticlesOrder = new ArticlesOrder(); 
          
          // des_order_id = 75
          $organization_id = '3';
          $order_id = '2990';
          $article_organization_id = '3';
          $article_id = '1582';
          $debug = true;
          
          $ArticlesOrder->aggiornaQtaCart_StatoQtaMax($organization_id, $order_id, $article_organization_id, $article_id, $debug);
          
          /*
            $utilsCrons = new UtilsCrons(new View(null));
            if(Configure::read('developer.mode')) echo "<pre>";
            $utilsCrons->mailReferentiQtaMax($organization_id, $debug);
            if(Configure::read('developer.mode')) echo "</pre>";
          */
          exit;
    }

    // https://console.developers.google.com/
    public function admin_gcaelndar() {
        $debug = true;

        $api_key = 'uphuu5m4egkslr5k4ibubkn13s@group.calendar.google.com'; // key cal cavagnetta 
        // GoogleClient_id 317847689931-ltnq3244cit3mojunmtee3cqtacc4tdh.apps.googleusercontent.com
        // GoogleService_client_id 317847689931-95cgtaogot4bnmt70audi0d30mq76fp4.apps.googleusercontent.com
        // exception  'Google_Auth_Exception' with message 'Error refreshing the OAuth2 token, message: '{ "error" : "unauthorized_client", "error_description" : "Unauthorized client or scope in request." }'' in /var/www/portalgas/google/src/Google/Auth/OAuth2.php:
        // GoogleService_client_idApplicationWEB 317847689931-kpjiopds89k7mu7v3kdikjl9uoggmlmd.apps.googleusercontent.com 
        // exception 'Google_Auth_Exception' with message 'Error refreshing the OAuth2 token, message: '{ "error" : "invalid_grant", "error_description" : "Invalid JWT Signature." }'' in /var/www/portalgas/google/src/Google/Auth/OAuth2.php
        // GooglePrivateKeyLocation cert/portalgas-5b553a227069.p12

        $client = new Google_Client();
        $client->setApplicationName("PortAlGas");
        $client->setClientId(Configure::read('GoogleClient_id'));  // GoogleClient_id  GoogleService_client_id
        /*
          echo "<pre>__createClientGoogle() \n";
          print_r($client);
          echo "<pre>";
         */

        // function __createServiceCalendarGoogle($client, $debug) ... 

        $service = null;

        try {
            $service_account_name = Configure::read('GoogleService_client_id'); // Configure::read('GoogleService_client_id');  // GoogleService_email  
            $key_file_location = Configure::read('App.root') . DS . Configure::read('GooglePrivateKeyLocation');
            $privateKey = file_get_contents($key_file_location);

            if ($debug)
                echo '<br />__createServiceCalendarGoogle() - GooglePrivateKeyLocation ' . $key_file_location;
            if ($debug)
                echo '<br />__createServiceCalendarGoogle() - GoogleClient_id ' . Configure::read('GoogleClient_id');
            if ($debug)
                echo '<br />__createServiceCalendarGoogle() - GoogleService_client_id ' . $service_account_name;

            $scopes = array(
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.readonly'
            );

            $auth_credentials = new Google_Auth_AssertionCredentials($service_account_name, $scopes, $privateKey);
            //	$auth_credentials->sub = Configure::read('GoogleEmailGmail');
            $auth_credentials->create_delegated = Configure::read('GoogleEmailGmail');
            /*
              echo "<pre>auth_credentials \n";
              print_r($auth_credentials);
              echo "<pre>";
             */
            $client->setAssertionCredentials($auth_credentials);
            if ($client->getAuth()->isAccessTokenExpired()) {
                /*
                  echo "<pre>client->getAuth() \n";
                  print_r($client->getAuth());
                  echo "<pre>";
                 */
                $client->getAuth()->refreshTokenWithAssertion($auth_credentials);
            }

            $_SESSION['access_token'] = $client->getAccessToken();

            $service = new Google_Service_Calendar($client);
            echo "<pre>service() \n";
            print_r($service);
            echo "<pre>";
        } catch (Exception $e) {
            if ($debug)
                echo "<br />__createServiceCalendarGoogle() " . $e;
            else {
                /* CakeLog::write("error", $e); */
            }
        }

        exit;
    }

    public function admin_users_roles() {
        $roles = [Configure::read('group_id_super_referent_des'), // 38
            Configure::read('group_id_referent_des'), // 37
            Configure::read('group_id_titolare_des_supplier'), // 39
            Configure::read('group_id_des_supplier_all_gas')];  // 51

        App::import('Model', 'DesSuppliersReferent');
        $DesSuppliersReferent = new DesSuppliersReferent;

        $organization_id = 15;  // ivrea
        $des_supplier_id = 28;  // produttore Lolmaia
        $this->user->des_id = 1;
        echo "<br />organization_id " . $organization_id;
        echo "<br />des_supplier_id " . $des_supplier_id;
        echo "<br />user->des_id " . $this->user->des_id;

        $results = $DesSuppliersReferent->getUsersRoles($this->user, $organization_id, $roles, $des_supplier_id, true);

        foreach ($results as $result) {
            echo "<br />" . $result['User']['email'] . " - " . $result['User']['username'];
            foreach ($result['User']['Group'] as $numResult => $group_id) {
                echo "<br />--- group_id " . $group_id;
            }
        }
        exit;
    }

    public function admin_bookmarks_articles() {
        App::import('Model', 'BookmarksArticle');
        $BookmarksArticle = new BookmarksArticle;

        $order_id = 76;
        $supplier_organization_id = 253;

        $debug = true;

        $BookmarksArticle->popolaCarts($this->user, $order_id, $supplier_organization_id, $debug);

        $this->render('admin_index');
    }

    public function admin_mail_logo() {
        App::import('Model', 'Mail');
        $Mail = new Mail;

        $this->set('content', $Mail->drawLogo($this->user->organization));

        $this->render('admin_index');
    }

    /*
     * in Model::Article ho relazione con 
     * 		ArticlesOrder -> Article
     * 		ArticlesArticleType -> ArticleType
     *  ma devo passargli i parametri organization_id nel Controller
     */

    public function admin_model_article() {
        App::import('Model', 'Article');
        $Model = new Article();

        $Model->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = ' . $this->user->organization['Organization']['id'];
        $Model->hasMany['ArticlesArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
        $Model->hasAndBelongsToMany['ArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);

        if (empty($order_id)) {
            $Model->hasOne['ArticlesOrder']['conditions'] = 'ArticlesOrder.organization_id = Article.organization_id AND Article.organization_id = ' . $this->user->organization['Organization']['id'];
            $Model->hasMany['ArticlesOrder']['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id']);
            $Model->hasAndBelongsToMany['Order']['conditions'] = 'Order.organization_id = ArticlesOrder.organization_id AND Order.organization_id = ' . $this->user->organization['Organization']['id'];
        } else {
            $Model->hasOne['ArticlesOrder']['conditions'] = 'ArticlesOrder.organization_id = Article.organization_id and Article.organization_id = ' . $this->user->organization['Organization']['id'] . ' and ArticlesOrder.order_id =' . $order_id;
            $Model->hasMany['ArticlesOrder']['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
                'ArticlesOrder.order_id' => $order_id);
            $Model->hasAndBelongsToMany['Order']['conditions'] = array('Order.organization_id' => 'ArticlesOrder.organization_id',
                'Order.organization_id' => $this->user->organization['Organization']['id'],
                'ArticlesOrder.order_id' => $order_id);
        }

        /* 	
          echo "<pre>belongsTo ";
          print_r($Model->belongsTo);
          echo "</pre>";
          echo "<pre>hasOne ";
          print_r($Model->hasOne);
          echo "</pre>";
          echo "<pre>hasMany ";
          print_r($Model->hasMany);
          echo "</pre>";
          echo "<pre>hasAndBelongsToMany ";
          print_r($Model->hasAndBelongsToMany);
          echo "</pre>";
         */

        //$Model->bindModel(array('hasOne' => array('ArticlesArticlesType' => array('conditions' => array('ArticlesArticlesType.organization_id = Article.organization_id')))));
        //$Model->bindModel(array('hasOne' => array('ArticlesOrder' => array('conditions' => array('ArticlesOrder.organization_id = Article.organization_id')))));

        $conditions = array(//'ArticlesArticlesType.article_type_id in (4)',
            //'ArticlesOrder.order_id' => 36,
            'Article.id' => 3,
            'Article.organization_id' => $this->user->organization['Organization']['id']);

        echo "<pre> ";
        print_r($conditions);
        echo "</pre>";
        try {
            $SqlLimit = 1000;

            $this->paginate = array('fields' => array('Article.*, SuppliersOrganization.name, ArticlesArticlesType.article_type_id'),
                //'fields' => array('Article.name'),
                'conditions' => $conditions,
                'order' => ['SuppliersOrganization.name', 'Article.name'],
                'recursive' => 1,
                'group' => array('Article.id,Article.organization_id,Article.supplier_organization_id,Article.category_article_id,Article.name,Article.codice,Article.nota,Article.ingredienti,Article.prezzo,Article.qta,Article.um,Article.um_riferimento,Article.pezzi_confezione,Article.qta_minima,Article.qta_massima,Article.qta_minima_order,Article.qta_massima_order,Article.qta_multipli,Article.alert_to_qta,Article.bio,Article.stato,Article.created,Article.modified,SuppliersOrganization.name'),
                'maxLimit' => $SqlLimit, 'limit' => $SqlLimit);

            $results = $this->paginate($Model);
            foreach ($results as $result) {
                echo '<br />' . $result['Article']['id'] . ' ' . $result['Article']['name'];
            }
            echo "<pre> ";
//			print_r($results);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            echo $e;
            echo "</pre>";
        }

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_article_unbind_articles_order() {
        App::import('Model', 'Article');
        $Model = new Article();

        $Model->hasOne['ArticlesArticlesType']['conditions'] = 'ArticlesArticlesType.organization_id = Article.organization_id AND Article.organization_id = ' . $this->user->organization['Organization']['id'];
        $Model->hasMany['ArticlesArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);
        $Model->hasAndBelongsToMany['ArticlesType']['conditions'] = array('ArticlesArticlesType.organization_id' => $this->user->organization['Organization']['id']);

        /*
          $Model->hasOne['ArticlesOrder']['conditions'] = 'ArticlesOrder.organization_id = Article.organization_id AND Article.organization_id = '.$this->user->organization['Organization']['id'];
          $Model->hasMany['ArticlesOrder']['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id']);
          $Model->hasAndBelongsToMany['Order']['conditions'] = 'Order.organization_id = ArticlesOrder.organization_id AND Order.organization_id = '. $this->user->organization['Organization']['id'];
         */
        $Model->unbindModel(['hasOne' => ['ArticlesOrder']]);
        $Model->unbindModel(['hasMany' => ['ArticlesOrder']]);
        $Model->unbindModel(['hasAndBelongsToMany' => ['Order']]);

        $options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
            'Article.id' => 3);
        $options['recursive'] = 1;
        try {
            $results = $Model->find('all', $options);
            foreach ($results as $result) {
                echo '<br />' . $result['Article']['id'] . ' ' . $result['Article']['name'];
            }
            echo "<pre> ";
            print_r($results);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            echo $e;
            echo "</pre>";
        }

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_articles_suppliers_organization() {

        $supplier_organization_id = 30;

        App::import('Model', 'Article');
        $Model = new Article;

        $Model->unbindModel(['hasOne' => ['ArticlesOrder']]);
        $Model->unbindModel(['hasMany' => ['ArticlesOrder']]);
        $Model->unbindModel(['hasAndBelongsToMany' => ['Order']]);

        $Model->unbindModel(['hasOne' => ['ArticlesArticlesType']]);
        $Model->unbindModel(['hasMany' => ['ArticlesArticlesType']]);
        $Model->unbindModel(['hasAndBelongsToMany' => ['ArticlesType']]);

        $options['conditions'] = array('Article.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Article.supplier_organization_id' => $supplier_organization_id,
            'Article.stato' => 'Y');
        if (!$this->isSuperReferente())
            $options['conditions'] += array('SuppliersOrganization.id IN (' . $this->user->get('ACLsuppliersIdsOrganization') . ')');

        $options['recursive'] = 0;
        $options['order'] = 'Article.id';
        $results = $Model->find('all', $options);
        foreach ($results as $result) {
            echo '<br />' . $result['Article']['id'] . ' ' . $result['Article']['name'];
        }
        echo "<pre> ";
        print_r($results);
        echo "</pre>";

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_suppliers_organization_articles() {

        $supplier_organization_id = 30;

        App::import('Model', 'SuppliersOrganization');
        $Model = new SuppliersOrganization;

        $Model->hasMany['Article']['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
            'Article.stato' => 'Y');

        $options['conditions'] = array('SuppliersOrganization.organization_id' => (int) $this->user->organization['Organization']['id'],
            'SuppliersOrganization.id' => $supplier_organization_id);
        if (!$this->isSuperReferente())
            $options['conditions'] += array('SuppliersOrganization.id IN (' . $this->user->get('ACLsuppliersIdsOrganization') . ')');

        $options['recursive'] = 1;
        $results = $Model->find('first', $options);

        /*
         * odinamento
         */
        Set::sort($results, 'Article.{n}.id', 'asc');

        foreach ($results['Article'] as $result) {
            echo '<br />' . $result['id'] . ' ' . $result['name'];
        }
        echo "<pre> ";
        print_r($results);
        echo "</pre>";

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_articles_type() {
        App::import('Model', 'ArticlesType');
        $Model = new ArticlesType;

        echo "<pre>belongsTo ";
        print_r($Model->belongsTo);
        echo "</pre>";
        echo "<pre>hasMany ";
        print_r($Model->hasMany);
        echo "</pre>";
        echo "<pre>hasAndBelongsToMany ";
        print_r($Model->hasAndBelongsToMany);
        echo "</pre>";

        $conditions = array('ArticlesType.id' => 1);
        $results = $Model->find('first', array('conditions' => $conditions, 'recursive' => 2));
        echo "<pre> ";
        print_r($results);
        echo "</pre>";

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_articles_articles_type() {
        App::import('Model', 'ArticlesArticlesType');
        $Model = new ArticlesArticlesType;

        //$Model->unbindModel(array('belongsTo' => array('Article')));

        echo "<pre>belongsTo ";
        print_r($Model->belongsTo);
        echo "</pre>";
        echo "<pre>hasMany ";
        print_r($Model->hasMany);
        echo "</pre>";
        echo "<pre>hasAndBelongsToMany ";
        print_r($Model->hasAndBelongsToMany);
        echo "</pre>";

        $conditions = array('ArticlesArticlesType.article_id' => 1);
        $results = $Model->find('first', array('conditions' => $conditions, 'recursive' => 2));
        echo "<pre> ";
        print_r($results);
        echo "</pre>";

        $this->set('content', "");
        $this->render('admin_index');
    }

    /*
     * in Model::Order creo la relazione con ArticlesOrder -> Article quando mi sever
     * ma devo passargli i parametri organization_id nel Controller
     */

    public function admin_model_order() {
        App::import('Model', 'Order');
        $Model = new Order;

        /*
         * questa prima parte non serve
         */
        /* $Model->hasOne['ArticlesOrder'] = array(
          'className' => 'ArticlesOrder',
          'foreignKey' => 'order_id',
          'dependent' => false,
          'conditions' => 'ArticlesOrder.organization_id = Order.organization_id AND Order.organization_id = '.$this->user->organization['Organization']['id']);
         */
        $Model->hasMany['ArticlesOrder'] = array(
            'className' => 'ArticlesOrder',
            'foreignKey' => 'order_id',
            'dependent' => false,
            'conditions' => 'ArticlesOrder.organization_id = ' . $this->user->organization['Organization']['id']);

        /*
         * questa estrae gli articoli associati all'ordine
         */
        $Model->hasAndBelongsToMany['Article'] = array(
            'className' => 'Article',
            'joinTable' => 'articles_orders',
            'foreignKey' => 'order_id',
            'associationForeignKey' => 'article_id',
            'unique' => 'keepExisting',
            'conditions' => 'Article.organization_id = ArticlesOrder.organization_id AND Article.organization_id = ' . $this->user->organization['Organization']['id'],
            'with' => 'ArticlesOrder');

        echo "<pre>belongsTo ";
        print_r($Model->belongsTo);
        echo "</pre>";
        echo "<pre>hasOne ";
        print_r($Model->hasOne);
        echo "</pre>";
        echo "<pre>hasMany ";
        print_r($Model->hasMany);
        echo "</pre>";
        echo "<pre>hasAndBelongsToMany ";
        print_r($Model->hasAndBelongsToMany);
        echo "</pre>";

        $conditions = array('Order.id' => 47);

        try {
            $results = $Model->find('all', array('conditions' => $conditions, 'recursive' => 1, 'maxLimit' => 1000, 'limit' => 1000));
            echo "<pre> ";
            print_r($results);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            echo $e;
            echo "</pre>";
        }

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_summary_payment() {
        App::import('Model', 'SummaryPayment');
        $Model = new SummaryPayment;

        echo "<pre>belongsTo ";
        print_r($Model->belongsTo);
        echo "</pre>";
        echo "<pre>hasOne ";
        print_r($Model->hasOne);
        echo "</pre>";
        echo "<pre>hasMany ";
        print_r($Model->hasMany);
        echo "</pre>";
        echo "<pre>hasAndBelongsToMany ";
        print_r($Model->hasAndBelongsToMany);
        echo "</pre>";

        $conditions = array('SummaryPayment.organization_id' => $this->user->organization['Organization']['id'],
            'SummaryPayment.request_payment_id' => 9);

        try {
            $results = $Model->find('all', array('conditions' => $conditions, 'recursive' => 1, 'limit' => 1000));
            echo "<pre> ";
            print_r($results);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            echo $e;
            echo "</pre>";
        }

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_delivery() {
        App::import('Model', 'Delivery');
        $Model = new Delivery;

        $Model->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id']);

        //$Model->unbindModel(array('belongsTo' => array('Article')));

        $conditions = array('Delivery.id' => 16,);

        $joins = array(
            array('table' => 'orders',
                'alias' => 'Order',
                'type' => 'LEFT',
                'conditions' => array(
                    'Order.delivery_id = Delivery.id',
                    'Order.organization_id = Delivery.organization_id'
                )
            )
        );

        try {
            $results = $Model->find('all', array('conditions' => $conditions, 'recursive' => 1));

            /*
             * in model beforeFind creo 
             * 		$this->hasMany = array(
             * 				'Order' => array(
             * 						'conditions' => array('Order.organization_id' => $organization_id),
             */
            echo "<pre>belongsTo ";
            print_r($Model->belongsTo);
            echo "</pre>";
            echo "<pre>hasMany ";
            print_r($Model->hasMany);
            echo "</pre>";
            echo "<pre>hasAndBelongsToMany ";
            print_r($Model->hasAndBelongsToMany);
            echo "</pre>";

            echo "<pre> ";
            print_r($results);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            echo $e;
            echo "</pre>";
        }

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_suppliers_organizations_referent() {

        App::import('Model', 'SuppliersOrganizationsReferent');
        $Model = new SuppliersOrganizationsReferent;

        echo "<pre>belongsTo ";
        print_r($Model->belongsTo);
        echo "</pre>";
        echo "<pre>hasMany ";
        print_r($Model->hasMany);
        echo "</pre>";
        echo "<pre>hasAndBelongsToMany ";
        print_r($Model->hasAndBelongsToMany);
        echo "</pre>";

        $conditions = array('SuppliersOrganizationsReferent.user_id' => 4);
        try {
            $results = $Model->find('all', array('conditions' => $conditions, 'recursive' => 1));

            echo "<pre>hasAndBelongsToMany ";
            print_r($results);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            echo $e;
            echo "</pre>";
        }

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_cart() {

        $order_id = 44;
        $article_id = 1402;
        $user_id = 11;

        App::import('Model', 'Cart');
        $Model = new Cart();

        /*
         * exists
         */
        if (!$Model->exists($this->user->organization['Organization']['id'], $order_id, $article_id, $user_id))
            echo '<h1>exists NOT FOUND</h1>';
        else
            echo '<h1>exists FOUND</h1>';


        $options['conditions'] = array('Cart.organization_id' => $this->user->organization['Organization']['id'],
            'Cart.order_id' => $order_id,
            //'Cart.article_id' => $article_id,
            'Cart.user_id' => $user_id,
        );
        /*
          echo "<pre>belongsTo ";
          print_r($Model->belongsTo);
          echo "</pre>";
          echo "<pre>hasMany ";
          print_r($Model->hasMany);
          echo "</pre>";
          echo "<pre>hasAndBelongsToMany ";
          print_r($Model->hasAndBelongsToMany);
          echo "</pre>";
         */
        $options['recursive'] = 1;
        $options['order'] = array(Configure::read('orderUser'));
        try {
            $results = $Model->find('all', $options);
            foreach ($results as $result) {
                echo '<br />' . $result['User']['name'] . ' ' . $result['Article']['name'] . ' ' . $result['ArticlesOrder']['order_id'];
            }
            echo "<pre> ";
            print_r($results);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            echo $e;
            echo "</pre>";
        }

        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_articles_order() {

        $order_id = 47;
        //$article_id = 1402;
        $user_id = 11;

        App::import('Model', 'ArticlesOrder');
        $Model = new ArticlesOrder();

        $Model->unbindModel(['belongsTo' => ['Cart', 'Order']]);
        $options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
            'ArticlesOrder.order_id' => $order_id,
            'Article.stato' => 'Y'
                //'ArticlesOrder.article_id' => $article_id,
//										'Cart.user_id' => $user_id,
        );

        $options['recursive'] = 1;
        //$options['order'] = array(Configure::read('orderUser'));
        try {
            $results = $Model->find('all', $options);
            foreach ($results as $result) {
                //	echo '<br />'.$result['Article']['name'].' '.$result['ArticlesOrder']['order_id'];
            }
            echo "<pre> ";
            print_r($results);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            echo $e;
            echo "</pre>";
        }



        $Model = new ArticlesOrder();

        //$Model->unbindModel(array('belongsTo' => array('Cart')));
        $options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
            'ArticlesOrder.order_id' => $order_id,
            //'ArticlesOrder.article_id' => $article_id,
            'Cart.user_id' => $user_id,
        );

        $options['recursive'] = 1;
        //$options['order'] = array(Configure::read('orderUser'));
        try {
            $results2 = $Model->find('all', $options);
            foreach ($results2 as $result) {
                echo '<br />' . $result['Article']['name'] . ' ' . $result['ArticlesOrder']['order_id'];
            }

            $results3 = array_merge($results, $results2);
            echo "<pre> ";
            print_r($results3);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            echo $e;
            echo "</pre>";
        }


        $this->set('content', "");
        $this->render('admin_index');
    }

    public function admin_model_get_max_id_organization_id() {
        App::import('Model', 'Delivery');
        App::import('Model', 'Article');
        $Model = new Article;

        $maxId = $Model->getMaxIdOrganizationId($this->user->organization['Organization']['id']);

        $this->set('content', $maxId);
        $this->render('admin_index');
    }

    public function admin_model_get_counter() {

        App::import('Model', 'Counter');
        $Model = new Counter;

        $results = $Model->getCounter($this->user, 'request_payments');

        $this->set('content', $results);
        $this->render('admin_index');
    }

    public function admin_get_users() {
        App::import('Model', 'User');
        $User = new User;

        $conditions = array('User.organization_id' => $this->user->organization['Organization']['id'],
            'User.block' => 0);
        $users = $User->find('all', array('conditions' => $conditions,
            'fields' => array('id', 'name', 'email'),
            'order' => Configure::read('orderUser'),
            'recursive' => -1));

        echo "<pre> ";
        print_r($users);
        echo "</pre>";

        $conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
        //$users = $User->getUsersList($user, $conditions);

        echo "getUsers(): trovati " . count($users) . " utenti\n";

        $this->set('content', $results);
        $this->render('admin_index');
    }

    public function admin_orders_type_draw($order_id = 1) {

        echo "<h2>il GAS " . $this->user->organization['Organization']['id'] . " partecipa con un ordine order_id $order_id</h2>";

        App::import('Model', 'Order');
        $Model = new Order;

        $Model->getTypeDraw($this->user, $order_id, $debug = true);

        $this->set('content', $results);
        $this->render('admin_index');
    }

    // App::import('Vendor', 'ImageTool');
    public function admin_image_resize() {

        /*
          Configure::write('App.log_joomla', DS.'logs');
          Configure::write('App.img.upload.article', DS.'images'.DS.'articles');
          Configure::write('App.img.upload.content', DS.'images'.DS.'organizations'.DS.'contents');
          Configure::write('App.web.img.upload.article', '/images/articles');
          Configure::write('App.web.img.upload.content', '/images/organizations/contents');
          Configure::write('App.img.upload.tmp', DS.'tmp'); // utilizzato per gli allegati delle mail
         */

        $input_path = Configure::read('App.root') . Configure::read('App.img.upload.tmp') . DS;
        $output_path = Configure::read('App.root') . Configure::read('App.img.upload.tmp') . DS;
        $watermark_file = Configure::read('App.root') . Configure::read('App.img.upload.tmp') . DS . 'watermark.gif';

        $input_file = '01.jpg';
        $output_fileA = '01a.jpg';
        $output_fileB = '01b.jpg';
        $output_fileC = '01c.jpg';
        $output_fileD = '01d.jpg';

        echo '<h2>img prese dalla cartella ' . $input_path . $input_file . '</h2>';
        echo '<p><img src="' . Configure::read('App.server') . '/tmp/' . $input_file . '" title="' . $input_path . $input_file . '" /></p>';

        $status = ImageTool::resize(array(
                    'input' => $input_path . $input_file,
                    'output' => $output_path . $output_fileA,
                    'width' => 100,
                    'height' => 100
        ));
        echo '<p><img src="' . Configure::read('App.server') . '/tmp/' . $output_fileA . '" title="' . $output_path . $output_fileA . '" /></p>';

        $status = ImageTool::resize(array(
                    'input' => $input_path . $input_file,
                    'output' => $output_path . $output_fileB,
                    'width' => 100,
                    'height' => ''
        ));
        echo '<p><img src="' . Configure::read('App.server') . '/tmp/' . $output_fileB . '" title="' . $output_path . $output_fileB . '" /></p>';

        /* 		
          $image = ImageTool::autorotate(array(
          'input' =>$input_path.$input_file,
          'output' => null
          ));

          if ($image) {
          $status = ImageTool::grayscale(array(
          'input' => $image,
          'output' => $output_path.$output_fileC
          ));
          } else {
          $status = false;
          }
          echo '<p><img src="'.Configure::read('App.server').'/tmp/'.$output_fileC.'" title="'.$output_path.$output_fileC.'" /></p>';


          $status = ImageTool::resize(array(
          'input' => $input_path.$input_file,
          'output' => $output_path.$output_fileD,
          'width' => 600,
          'height' => 600,
          'keepRatio' => true,
          'paddings' => false,
          'afterCallbacks' => array(
          array('watermark', array('watermark' => $watermark_file, 'position' => 'bottom-right')),
          array('unsharpMask'),
          )
          ));

          echo '<p><img src="'.Configure::read('App.server').'/tmp/'.$output_fileD.'" title="'.$output_path.$output_fileD.'" /></p>';
         */

        $this->render('admin_index');
    }

    public function admin_file() {
        $file_name = '3418.jpg';
        $file_path = Configure::read('App.root') . Configure::read('App.img.upload.article') . DS . $this->user->organization['Organization']['id'] . DS;
        echo "<br />file_name $file_name";
        echo "<br />file_path $file_path";

        $file1 = new File($file_path . $file_name, false, 0777);
        echo "<pre>";
        print_r($file1);
        echo "</pre>";

        if (!$file1->delete())
            echo "<br />File $file_name non cancellato";
        else
            echo "<br />File $file_name cancellato";

        $this->render('admin_index');
    }

    function admin_ricorsivo() {

        setlocale('LC_ALL', 'it_IT');
        $format = "d D/m/Y";

        echo '<h1>' . date($format) . '</h1>';

        echo '<br/>ieri ' . date($format, mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        echo '<br/>domani ' . date($format, mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
        echo '<br/>mese prossimo ' . date($format, mktime(0, 0, 0, date("m") + 1, date("d"), date("Y")));

        $giorni_mese = date('t');  //  numero di giorni del mese dato; i.e. "28" a "31" 
        $ultimodelmese = date('Y-m') . "-" . $giorni_mese;
        $giorno = date('w', strtotime($ultimodelmese)); // giorno della settimana, numerico, i.e. "0" (Domenica) a "6" (Sabato) 

        echo '<br/>giorni_mese ' . $giorni_mese . ' numero di giorni del mese dato; i.e. "28" a "31"';
        echo '<br/>ultimodelmese ' . $ultimodelmese;
        echo '<br/>giorno ' . $giorno . ' giorno della settimana, numerico, i.e. "0" (Domenica) a "6" (Sabato) ';

        switch ($giorno) {
            case '6':
                $giorni_mese = $giorni_mese - 1;
                break;
            case '0':
                $giorni_mese = $giorni_mese - 2;
                break;
            default:
                break;
        }
        $ultimo_lavorativo_del_mese = $giorni_mese . "/" . date('m/Y');
        echo '<br/>ultimo_lavorativo_del_mese ' . $ultimo_lavorativo_del_mese;

        $this->render('admin_index');
    }

    /*
     * francesco.actis@gmail.com
     * robi_luca@alice.it
     * malandrel2@gmail.com
     */

    function admin_login_da_mail($username = 'malandrel2@gmail.com', $delivery_id = 5627) {

        App::import('Model', 'User');
        $User = new User;

        $url = $User->getUrlCartPreview($this->user, $username, $delivery_id);

        echo '<a target="_blank" href="' . Configure::read('App.server') . '/home-gas-cavagnetta/preview-carrello-gas-cavagnetta?' . $url . '">' . $url . '</a>';

        $usernameCrypted = $User->getUsernameCrypted($username);

        $usernameCrypted = 'SXp1S2JpQ1VmMGM3S1lzcFB4RXhseVRxT1FwVXhBLzcrS2Q1eE0xODF3WT06Oh/YNiABYPuDmXevoJFQwQ0=';
        // Warning (2): openssl_decrypt(): IV passed is only 12 bytes long, cipher expects an IV of precisely 16 bytes, padding with \0 [APP/Model/AppModel.php, line 406]
        $usernameCrypted = 'dReFbBk4SEfDWFSnfNAKdedqDERdS6UBZuuEPQA6C6rYGoFFDnC8L4PhHc57QwQ8Ojr3q8 rPbTGX6xcN7K=';
        
        $username = $User->getUsernameToUsernameCrypted($usernameCrypted);
        debug('usernameCrypted ' . $usernameCrypted);
        debug('usernameDeCrypted ' . $username);

        $options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
            'User.username' => $username,
            'User.block' => 0];
        $options['fields'] = ['id'];
        $options['recursive'] = -1;
        $results = $User->find('first', $options);

        debug($results);

        $this->render('admin_index');
    }

    function admin_payToDelivery($delivery_id = 24) {

        App::import('Model', 'User');

        App::import('Model', 'Order');

        App::import('Model', 'ArticlesOrder');

        /*
         * estraggo tutti gli users di una consegna
         */
        $User = new User;

        $conditions = [];
        $conditions = ['Delivery.id' => $delivery_id];
        $userResults = $User->getUserWithCartByOrder($this->user, $conditions);

        /*
         * estraggo tutto gli ordini di una consegna
         */
        $Order = new Order;
        $Order->unbindModel(['belongsTo' => ['Delivery']]);

        $options = [];
        $options['conditions'] = ['Order.organization_id' => (int) $this->user->organization['Organization']['id'],
            'Order.delivery_id' => $delivery_id,
            'Order.isVisibleBackOffice' => 'Y'];
        $options['order'] = 'Order.data_inizio ASC';
        $options['recursive'] = 0;

        $orderResults = $Order->find('all', $options);

        $permissions = [];

        foreach ($userResults as $userResults) {
            debug('User (' . $userResults['User']['id'] . ') ' . $userResults['User']['name']);

            $user_id = $userResults['User']['id'];

            foreach ($orderResults as $numResult => $orderResult) {

                $order_id = $orderResult['Order']['id'];

                $conditions = [];
                $conditions = ['Cart.user_id' => $user_id,
                               'Cart.order_id' => $order_id];

                $ArticlesOrder = new ArticlesOrder;
                $articlesOrderResults = $ArticlesOrder->getArticoliDellUtenteInOrdine($this->user, $conditions);
                if (!empty($articlesOrderResults)) {

                    debug('----- ' . ($numResult + 1) . ' Ordine (' . $orderResult['Order']['id'] . ' ' . $orderResult['Order']['state_code'] . ') ' . $orderResult['SuppliersOrganization']['name']);

                    foreach ($articlesOrderResults as $numResult2 => $articlesOrderResults) {

                        debug('---------- ' . ($numResult2 + 1) . ' Acquisto ' . $articlesOrderResults['Article']['name'] . ' ' . $articlesOrderResults['ArticlesOrder']['prezzo_e'] . ' qta ' . $articlesOrderResults['Cart']['qta']);
                    }
                }
            } // end foreach ($orderResults as $orderResult)
        } // end foreach ($userResults as $userResults) 
    }

    function admin_order_actions() {

        $debug = true;

        $group_id = 19;
        $orderResults['Order']['state_code'] = 'OPEN';
        $user->organization['Organization']['template_id'] = 1;

        App::import('Model', 'TemplatesOrdersStatesOrdersAction');
        $TemplatesOrdersStatesOrdersAction = new TemplatesOrdersStatesOrdersAction;

        $options = [];
        $options['conditions'] = [
            'TemplatesOrdersStatesOrdersAction.template_id' => $user->organization['Organization']['template_id'],
            'TemplatesOrdersStatesOrdersAction.state_code' => $orderResults['Order']['state_code'],
            'TemplatesOrdersStatesOrdersAction.group_id' => $group_id,
            'OrdersAction.flag_menu' => 'Y',
        ];

        $options['order'] = ['TemplatesOrdersStatesOrdersAction.sort'];
        $options['recursive'] = 0;
        $results = $TemplatesOrdersStatesOrdersAction->find('all', $options);

        debug($results);
        exit;
    }

    function admin_gmaps() {

        $debug = false;

        App::import('Model', 'User');
        $User = new User;

        $options = [];
        $options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
            'User.block' => 0];
        $options['order'] = Configure::read('orderUser');
        $options['recursive'] = -1;
        $results = $User->find('all', $options);

        /*
         * userprofile
         */
        jimport('joomla.user.helper');
        $i = 0;
        $newResults = [];
        foreach ($results as $numResult => $result) {

            $userTmp = JFactory::getUser($result['User']['id']);
            $userProfile = JUserHelper::getProfile($userTmp->id);

            // add Configure::read('LatLngNotFound')
            if (!empty($userProfile->profile['lat']) && !empty($userProfile->profile['lng'])) {
                $newResults[$i] = $result;
                $newResults[$i]['Profile'] = $userProfile->profile;

                $i++;
            }
        } // foreach ($results as $numResult => $result)

        // debug($newResults);
        $this->set('results', $newResults);
    }

    function admin_data_validate() {

        $id = 12;

        App::import('Model', 'RequestPayment');
        $RequestPayment = new RequestPayment;

        App::import('Model', 'RequestPaymentsOrder');
        $RequestPaymentsOrder = new RequestPaymentsOrder;

        App::import('Model', 'Order');
        $Order = new Order;

        $options = [];
        $options['conditions'] = ['RequestPayment.organization_id' => $this->user->organization['Organization']['id'],
            'RequestPayment.id' => $id];
        $options['recursive'] = -1;
        $requestPaymentResults = $RequestPayment->find('first', $options);
        $this->set('requestPaymentResults', $requestPaymentResults);

        $options = [];
        $options['conditions'] = ['RequestPaymentsOrder.organization_id' => $this->user->organization['Organization']['id'],
            'RequestPaymentsOrder.request_payment_id' => $id];
        $options['recursive'] = -1;
        $results = $RequestPaymentsOrder->find('all', $options);
        $tot_delta = 0;
        foreach ($results as $numResult => $result) {

            $order_id = $result['RequestPaymentsOrder']['order_id'];

            $options = [];
            $options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
                'Order.id' => $order_id];
            //$options['fields'] = ['id', 'state_code', 'tot_importo'];
            //$options['recursive'] = -1;
            $orderResults[$numResult] = $Order->find('first', $options);

            $sql = "SELECT
						sum(importo) as tot_importo_rimborsate
					FROM
						" . Configure::read('DB.prefix') . "summary_orders as SummaryOrder
					WHERE
						SummaryOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						and SummaryOrder.order_id = " . (int) $order_id;
            self::d($sql, false);
            $tot_importo_rimborsate = current($RequestPaymentsOrder->query($sql));
            $orderResults[$numResult]['Order']['tot_importo_rimborsate'] = $tot_importo_rimborsate[0]['tot_importo_rimborsate'];

            $delta = ($orderResults[$numResult]['Order']['tot_importo'] - $orderResults[$numResult]['Order']['tot_importo_rimborsate']);
            $orderResults[$numResult]['Order']['delta'] = $delta;

            $tot_delta += $delta;
        } // foreach ($results as $numResult => $result)

        // debug($orderResults);
        $this->set('results', $orderResults);
    }

    function admin_des() {

        $debug = true;
        $des_id = 1;
        $des_supplier_id = 1;  // Coop. Agricola IRIS - Astra bio (supplier_id = 5)

        $tmp_user = (object) ['des_id' => $des_id];
        // debug($tmp_user);
        
        App::import('Model', 'SummaryDesOrder');
        $SummaryDesOrder = new SummaryDesOrder();
        $SummaryDesOrder->populate_to_des_order($tmp_user, $des_supplier_id, $debug);

        $options = [];
        $options['conditions'] = ['SummaryDesOrder.des_id' => $des_id,
            'SummaryDesOrder.des_supplier_id' => $des_supplier_id
        ];
        $options['recursive'] = 1;
        $desSummaryDesOrderResults = $SummaryDesOrder->find('all', $options);
        debug($desSummaryDesOrderResults);
        
        App::import('Model', 'Organization');

        App::import('Model', 'De');
        $De = new De();

        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder();

        // $DesOrder->unbindModel(array('belongsTo' => array('Organization', 'De')));

        $options = [];
        $options['conditions'] = ['DesOrder.des_id' => $des_id,
            'DesOrder.des_supplier_id' => $des_supplier_id
        ];
        $options['recursive'] = -1;
        $desOrdersResults = $DesOrder->find('all', $options);

        $results = [];
        foreach ($desOrdersResults as $numResult => $desOrdersResult) {

            $order_ids = '';
            foreach ($desOrdersResults as $desOrdersResult)
                $order_ids .= $desOrdersResult['DesOrder']['order_id'] . ',';

            $order_ids = substr($order_ids, 0, strlen($order_ids) - 1);

            $conditions = array('Cart.order_id' => $order_ids);
            $orderBy = array('Article.name ASC', 'Article.id ASC', 'Organization.name');

            $results = $De->getArticoliAcquistatiDaUtenteInDesOrdine($this->user, $conditions, $orderBy);
        }

        $results = $De->getAggregateArticoliOrganization($this->user, $results);
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
        $this->set('results', $results);
    }
}
