<?php
App::uses('UtilsCommons', 'Lib');

/*
 * aggiungere action in  core.php
 * Configure::write('urlFrontEndToRewriteCakeRequest', ...)
 */
class RestsController extends AppController {
	
	public $components = ['CryptDecrypt'];

	public function beforeFilter() {
		parent::beforeFilter();

		// in .htaccess $this->response->header('Access-Control-Allow-Origin', '*');
	
		/*
		debug($_REQUEST);
		*/
	}

    // servizio chiamato da neo per mantenere la sessione 
    public function ping_session() {

		$response = ['code' => 200];
	    $json = json_encode($response);
		$this->set('json', $json); 
		
		$this->layout = 'json';
		$this->render('/Rests/index');	   
    }

    // servizio chiamato da neo per mantenere la sessione 
    public function admin_ping_session() {

		$response = ['code' => 200];
	    $json = json_encode($response);
		$this->set('json', $json); 
		
		$this->layout = 'json';
		$this->render('/Rests/index');	   
    }

   /*
    * da NEO => OLD
    *	(OLD => NEO Connects::index())
    *
    * da neo.joomla25Salts::index()
    *	creo u (user_salt) e passo scope (FE / BO) c_to (controller destinazione) / a_to (action destinazione)
	* richiamo https://www.portalgas.it/api/connect?u={salt}=&c_to=Pages&a_to=home
	* rimappa in Rests::connect()
	*	unserialize(user_salt), crea Session e redirect pg destinazione
    *
    * localhost nginx non gestisce .htaccess  
	* 	non passa da api/connect ma direttamente Rests::connect
    */
    public function connect() {

   		$debug = false;
		$continua = true;
   		
   		if($debug) debug($this->request->params);
 
 		$q = '';
 		$queries = $this->request->params['pass'];

   		if(!isset($queries['u']) || empty($queries['u'])) 
   			$continua = false;

   		if($continua) {
   			if(isset($queries['scope']))
	   			$scope = $queries['scope'];
	   		else
	   			$scope = 'FE';
   			if(isset($queries['c_to']))
	   			$c_to = $queries['c_to'];
	   		else
	   			$c_to = 'Pages';
   			if(isset($queries['a_to']))
	   			$a_to = $queries['a_to'];
	   		else
	   			$a_to = 'home';
   			if(isset($queries['u']))
	   			$user_salt = $queries['u'];
	   		else
	   			$continua = false;
   		}

		if($continua) {
			/*
			 * recupero valori queryString
			 */
			unset($queries['u']);
			unset($queries['scope']);
			unset($queries['c_to']);
			unset($queries['a_to']);
			if(!empty($queries)) {
				foreach($queries as $key => $value) {
	                $q .= $key.'='.$value.'&';
	            }
	            if(!empty($q))  
	               $q = substr($q, 0, (strlen($q)-1));	            
			}
			if($debug) debug($q);

			$user = $this->CryptDecrypt->decrypt($user_salt);
			$user = unserialize($user);
			if($debug) debug($user); 

		    $db = JFactory::getDbo();
		    $app = JFactory::getApplication();
			$jUser = JFactory::getUser($user['user_id']);
			if($debug) debug($jUser);	

            $instance = $jUser;     
            $instance->set('guest', 0);

            $session = JFactory::getSession();
            $session->set('user', $jUser);

            // Check to see the the session already exists.                        
            $app->checkSession();
            $app->setUserState('users.login.form.data', array());
            
            // Update the user related fields for the Joomla sessions table.
            $sql = 'UPDATE '.$db->quoteName('#__session') .
                    ' SET '.$db->quoteName('guest').' = '.$db->quote($instance->get('guest')).',' .
                    '   '.$db->quoteName('username').' = '.$db->quote($instance->get('username')).',' .
                    '   '.$db->quoteName('userid').' = '.(int) $instance->get('id') .
                    ' WHERE '.$db->quoteName('session_id').' = '.$db->quote($session->getId());
            if($debug) debug($sql);    
            $db->setQuery($sql);
            $db->query();
            $instance->setLastVisit();  

            switch ($scope) {
            	case 'FE':
            		if(!$this->utilsCommons->string_starts_with($c_to, '/')) 
            			$c_to = '/'.$c_to;
            		$url = Configure::read('App.server').$c_to;
            		break;
            	case 'BO':
            		$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller='.$c_to.'&action='.$a_to.'&'.$q;
            		break;
            	default:
            		die("Resta::connect scpoe [$scope] invalid!");
            		break;
            }
            
            if($debug) debug($url);

            if(!$debug)
            	$app->redirect($url);

   		} // end if($continua) 

   		exit;
    }

	/*
	 *  i valori arrivano in GET per RewriteRule in api/.htaccess 
	 */
	public function autentication() {

		if(isset($this->request->data['username']))
			$username = $this->request->data['username'];
		if(isset($this->request->data['password']))
			$password = $this->request->data['password'];

	   /*
	    * curl_setopt(): CURLOPT_FOLLOWLOCATION cannot be activated when an open_basedir is set [/plugins/authentication/gmail/gmail.php, line 84]
		*/
	   ini_set('safe_mode', false);
		
       if (($username == '') || ($password == ''))
       {
           header('HTTP/1.1 400 Bad Request', true, 400);
           jexit();
       }

       jimport( 'joomla.user.authentication');
       $auth = & JAuthentication::getInstance();
       $credentials = ['username' => $username, 'password' => $password];
       $options = [];
       $response = $auth->authenticate($credentials, $options);

	   if(isset($response->password))
			$response->password = '*****';
		
		$response->token = JSession::getFormToken();
			
		$app = JFactory::getApplication('administrator');
		$response = $app->login($credentials);
				 
	   /*
		JAuthenticationResponse Object
		(
			[status] => 1
			[type] => GMail Joomla
			[error_message] => 
			[username] => francesco.actis@gmail.com
			[email] => francesco.actis@gmail.com
			[fullname] => francesco.actis@gmail.com
			[birthdate] => 
			[gender] => 
			[postcode] => 
			[country] => 
			[language] => 
			[timezone] => 
			[_errors:protected] => Array
				(
				)

		)
		
		echo "<pre>";
		print_r($response);
		echo "</pre>";	
		*/			

       if ($response->status == JAUTHENTICATE_STATUS_SUCCESS)
       {
			$user = JFactory::getUser();
			/*
			echo "<pre>";
			print_r($user);
			echo "</pre>";			
			*/
       }
       
	    $json = json_encode($response);
		$this->set('json', $json); 
		
		$this->layout = 'json';
		$this->render('/Rests/index');	   
	}
	
	/*
	 * /?option=com_cake&controller=Rests&action=organizations&format=notmpl
	 * /api/organizations?format=notmpl
	 */
	public function organizations() {
	
		App::import('Model', 'Organization');
		$Organization = new Organization;		
		
		$options = [];
        $options['conditions'] = ['Organization.stato' => 'Y', 'Organization.type' => 'GAS'];
		$options['fields'] = ['Organization.id', 'Organization.name', 'Organization.descrizione', 'Organization.indirizzo', 'Organization.localita', 'Organization.cap', 'Organization.provincia',
							   'Organization.mail', 'Organization.www', 'Organization.www2'];
        $options['order'] = ['Organization.name'];
		$options['recursive'] = -1;
		$results = $Organization->find('all', $options);
		
		$json = json_encode($results);
		$this->set('json', $json);
				
		$this->layout = 'json';
		$this->render('/Rests/index');			
	}
			
	 /*
	  *  /api/organization?id=1&format=notmpl
	  */
	public function organization($organization_id) {
	
		App::import('Model', 'Organization');
		$Organization = new Organization;		
		
		$options = [];
        $options['conditions'] = ['Organization.id' => $organization_id,  'Organization.stato' => 'Y', 'Organization.type' => 'GAS'];
		$options['fields'] = ['Organization.id', 'Organization.name', 'Organization.indirizzo', 'Organization.localita', 'Organization.cap', 'Organization.provincia',
								    'Organization.mail', 'Organization.www', 'Organization.www2',
									'Organization.img1'];
        $options['order'] = ['Organization.name'];			
		$options['recursive'] = -1;
		$results = $Organization->find('all', $options);
		
		$json = json_encode($results);
		$this->set('json', $json);
				
		$this->layout = 'json';
		$this->render('/Rests/index');			
	}
	
	/*
	 *  /api/deliveries?id=1&format=notmpl
	 */
	public function deliveries($organization_id) {

		$json = [];
		
		if(empty($organization_id)) {
			$this->set('json', $json);
			$this->layout = 'json';
			$this->render('/Rests/admin_index');		
		}
		
		$user->organization['Organization']['id'] = $organization_id; 
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => $user->organization['Organization']['id'],
								   'Delivery.isVisibleFrontEnd' => 'Y',
								   'Delivery.stato_elaborazione'=> 'OPEN',
									'Delivery.sys'=> 'N',
									'DATE(Delivery.data) >= CURDATE() - INTERVAL '.Configure::read('GGinMenoPerEstrarreDeliveriesInTabs').' DAY'];
		$options['recursive'] = -1;
		$options['order'] = ['Delivery.data'];
		$results = $Delivery->find('all', $options);

		$newResults = [];
		foreach($results as $numResults => $result) {
		
			$newResults[$numResults] = $result;
			$newResults[$numResults]['Delivery']['nota'] = $this->_pulisciCampo($result['Delivery']['nota']);
		}

		/*
		 * ctrl se ci sono ordini con la consegna ancora da definire (Delivery.sys = Y)
		 */
		App::import('Model', 'Order');
		$Order = new Order;
		
		$ordersResults = $Order->getOrdersDeliverySys($user);

		if(count($ordersResults)>0) {
		
			$sysResults = $Delivery->getDeliverySys($user);
			
			$tmp['Delivery']['id'] = $sysResults['Delivery']['id'];
			$tmp['Delivery']['luogo'] = $sysResults['Delivery']['luogo'];
			$tmp['Delivery']['nota'] = $sysResults['Delivery']['nota'];
			$tmp['Delivery']['nota_evidenza'] = $sysResults['Delivery']['nota_evidenza'];
			$tmp['Delivery']['data'] = Configure::read('DeliveryToDefinedDate');
			$newResults[count($newResults)] = $tmp;
		}
		
		/*
		echo "<pre>";
		print_r($newResults);
		echo "</pre>";	
		*/
		
		$json = json_encode($newResults);
		$this->set('json', $json);
		
		$this->layout = 'json';
		$this->render('/Rests/index');
	}

	/*
	 *  /api/orders?id=1&delivery_id=71&format=notmpl
	 */
	public function orders($organization_id, $delivery_id) {

		$json = [];
				
		if(empty($organization_id) || empty($delivery_id)) {
			$this->set('json', $json);
			$this->layout = 'json';
			$this->render('/Rests/admin_index');		
		}
		
		$user->organization['Organization']['id'] = $organization_id; 
		
		App::import('Model', 'Supplier');
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$options = [];
		$options['conditions'] = array('Delivery.organization_id' => $user->organization['Organization']['id'],
									   'Delivery.isVisibleFrontEnd' => 'Y',
									   'Delivery.stato_elaborazione'=> 'OPEN',
										'Order.organization_id' => $user->organization['Organization']['id'],
										'Order.delivery_id' => $delivery_id,
										);
		$options['recursive'] = 0;
		$options['fields'] = array('Order.id', 'Order.supplier_organization_id', 'Order.data_inizio', 'Order.data_fine', 'Order.data_fine_validation', 'Order.nota', 'SuppliersOrganization.supplier_id');
		$options['order'] = array('Order.data_fine');
		$results = $Order->find('all', $options);

		$newResults = [];
		foreach($results as $numResults => $result) {
		
			$newResults[$numResults] = $result;
			$newResults[$numResults]['Order']['nota'] = $this->_pulisciCampo($result['Order']['nota']);
					
			/*
			 * produttore
			 */
			$Supplier = new Supplier;

			$options = [];
			$options['conditions'] = array('Supplier.id' => $result['SuppliersOrganization']['supplier_id']);
			$options['recursive'] = -1;
			$options['fields'] = array('Supplier.name', 'Supplier.img1');
			$supplierResults = $Supplier->find('first', $options);
			
			$newResults[$numResults]['Supplier'] = $supplierResults['Supplier'];
		} // foreach($results as $numResults => $result)

		/*
		echo "<pre>";
		print_r($newResults);
		echo "</pre>";	
		*/
		
		$json = json_encode($newResults);
		$this->set('json', $json);
		
		$this->layout = 'json';
		$this->render('/Rests/index');		
	}
	
	 /*
	 *  /api/articles_orders?id=1&order_id=192&format=notmpl
	 */
	public function articles_orders($organization_id, $order_id) {
	
		$user->organization['Organization']['id'] = $organization_id;
	
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;		
		
		$options = [];
		$options['conditions'] = array(// 'Cart.user_id' => $this->user->id,
										'Cart.deleteToReferent' => 'N', 
										'ArticlesOrder.order_id' => $order_id);
		
		$options['order'] = 'Article.name';
		$results = $ArticlesOrder->getArticoliEventualiAcquistiInOrdine($user, $order_id, $organization_id, $options);

		$newResults = [];
		foreach($results as $numResults => $result) {
		
			$newResults[$numResults] = $result;
			$newResults[$numResults]['Article']['nota'] = ''; // $this->_pulisciCampo($result['Article']['nota']);
			$newResults[$numResults]['Article']['ingredienti'] = ''; // $this->_pulisciCampo($result['Article']['ingredienti']);
			$newResults[$numResults]['Article']['prezzoUm'] = $this->utilsCommons->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
			
		}
		
		/*
		echo "<pre>";
		print_r($newResults);
		echo "</pre>";	
		*/
		
		$json = json_encode($newResults);
		$this->set('json', $json);
				
		$this->layout = 'json';
		$this->render('/Rests/index');			
	}
	
	 /*
	  *  /api/cash_ctrl_limit?format=notmpl
	 */
	public function cash_ctrl_limit() {
	
		$debug = false;
		$results = [];
		$continua = true;
		
		/*
		echo "<pre>";
		print_r($this->user);
		echo "</pre>";
		*/
		
		if(!isset($this->user) || empty($this->user->id)) {
			$results['code'] = 500;
			$results['msg'] = __('msg_rest_not_permission'); // msg_not_permission msg_rest_error_params
			$continua = false;
		}
		
		if($continua) {
				
			App::import('Model', 'CashesUser');
			$CashesUser = new CashesUser;
				
			$results = $CashesUser->getUserData($this->user);
			
			 /*
			  * totale importo acquisti
			  */
			$results['user_tot_importo_acquistato'] = $CashesUser->getTotImportoAcquistato($this->user, $this->user->id);
			
			$cashesUserResults = [];
			$cashesUserResults['limit_type'] = $results['user_limit_type'];
			$cashesUserResults['limit_after'] = $results['user_limit_after'];
			
			$results['ctrl_limit'] = $CashesUser->ctrlLimit($this->user, $results['organization_cash_limit'], $results['organization_limit_cash_after'], $cashesUserResults, $results['user_cash'], $results['user_tot_importo_acquistato'], $debug);
			  
			self::d($results, false);
		}
		
		$json = json_encode($results);
		$this->set('json', $json);
				
		$this->layout = 'json';
		$this->render('/Rests/index');			
	}
	
	private function _pulisciCampo($str) {
	
		$str = str_replace("\r\n", "", $str);
		$str = str_replace("\r", "", $str);
		$str = str_replace("\n", "", $str);
		$str = str_replace("\t", "", $str);
		$str = preg_replace("'\r\n'", "", $str);
		$str = str_replace("\"", "'", $str);
		$str = trim($str);
		$str = strip_tags($str);
		return $str;
	}
}