<?php
App::uses('UtilsCommons', 'Lib');

class RestsController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();

		$this->response->header('Access-Control-Allow-Origin', '*');
		
		/*
		echo "<pre>";
		print_r($_REQUEST);
		echo "</pre>";
		*/
	}

	/*
	 *  i valori arrivano in GET per RewriteRule in api/.htaccess 
	 */
	public function autentication() {
	
		exit;
		
	   /*
	    * curl_setopt(): CURLOPT_FOLLOWLOCATION cannot be activated when an open_basedir is set [/plugins/authentication/gmail/gmail.php, line 84]
		*/
	   ini_set('safe_mode', false);
		
       $jinput = JFactory::getApplication()->input;
       $username = $jinput->get('username', '', 'STRING');
       $password = $jinput->get('password', '', 'STRING');
		
       if (($username == '') ||    ($password == ''))
       {
           header('HTTP/1.1 400 Bad Request', true, 400);
           jexit();
       }

       jimport( 'joomla.user.authentication');
       $auth = & JAuthentication::getInstance();
       $credentials = array( 'username' => $username, 'password' => $password );
       $options = array();
       $response = $auth->authenticate($credentials, $options);

	   if(isset($response->password))
			$response->password = '*****';
		
		$response->token = JSession::getFormToken();
					
	   /*
		JAuthenticationResponse Object
		(
			[status] => 1
			[type] => GMail Joomla
			[error_message] => 
			[username] => francesco.actis@gmail.com
			[password] => Barrett
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
	 *  next.portalgas.it/api/organizations?format=notmpl
	 */
	public function organizations() {
	
		App::import('Model', 'Organization');
		$Organization = new Organization;		
		
		$options = array();
		$options['conditions'] = array('Organization.stato' => 'Y');
		
		$options['recursive'] = -1;
		$options['fields'] = array('Organization.id', 'Organization.name', 'Organization.descrizione', 'Organization.indirizzo', 'Organization.localita', 'Organization.cap', 'Organization.provincia',
								    'Organization.mail', 'Organization.www', 'Organization.www2');
		$options['order'] = array('Organization.name');
		$results = $Organization->find('all', $options);
		
		$json = json_encode($results);
		$this->set('json', $json);
				
		$this->layout = 'json';
		$this->render('/Rests/index');			
	}
			
	 /*
	 *  next.portalgas.it/api/organization?id=1&format=notmpl
	 */
	public function organization($organization_id) {
	
		App::import('Model', 'Organization');
		$Organization = new Organization;		
		
		$options = array();
		$options['conditions'] = array('Organization.id' => $organization_id,
									   'Organization.stato' => 'Y');
		
		$options['recursive'] = -1;
		$options['fields'] = array('Organization.id', 'Organization.name', 'Organization.indirizzo', 'Organization.localita', 'Organization.cap', 'Organization.provincia',
								    'Organization.mail', 'Organization.www', 'Organization.www2',
									'Organization.img1', 
									);
		$results = $Organization->find('all', $options);
		
		$json = json_encode($results);
		$this->set('json', $json);
				
		$this->layout = 'json';
		$this->render('/Rests/index');			
	}
	
	/*
	 *  http://next.portalgas.it/api/deliveries?id=1&format=notmpl
	 */
	public function deliveries($organization_id) {

		$json = array();
		
		if(empty($organization_id)) {
			$this->set('json', $json);
			$this->layout = 'json';
			$this->render('/Rests/admin_index');		
		}
		
		$user->organization['Organization']['id'] = $organization_id; 
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => $user->organization['Organization']['id'],
									   'Delivery.isVisibleFrontEnd' => 'Y',
									   'Delivery.stato_elaborazione'=> 'OPEN',
										'Delivery.sys'=> 'N',
										'DATE(Delivery.data) >= CURDATE() - INTERVAL '.Configure::read('GGinMenoPerEstrarreDeliveriesInTabs').' DAY');
		$options['recursive'] = -1;
		$options['order'] = array('Delivery.data');
		$results = $Delivery->find('all', $options);

		$newResults = array();
		foreach($results as $numResults => $result) {
		
			$newResults[$numResults] = $result;
			$newResults[$numResults]['Delivery']['nota'] = $this->__pulisciCampo($result['Delivery']['nota']);
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
	 *  next.portalgas.it/api/orders?id=1&delivery_id=71&format=notmpl
	 */
	public function orders($organization_id, $delivery_id) {

		$json = array();
				
		if(empty($organization_id) || empty($delivery_id)) {
			$this->set('json', $json);
			$this->layout = 'json';
			$this->render('/Rests/admin_index');		
		}
		
		$user->organization['Organization']['id'] = $organization_id; 
		
		App::import('Model', 'Supplier');
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$options = array();
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

		$newResults = array();
		foreach($results as $numResults => $result) {
		
			$newResults[$numResults] = $result;
			$newResults[$numResults]['Order']['nota'] = $this->__pulisciCampo($result['Order']['nota']);
					
			/*
			 * produttore
			 */
			$Supplier = new Supplier;

			$options = array();
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
	 *  next.portalgas.it/api/articles_orders?id=1&order_id=192&format=notmpl
	 */
	public function articles_orders($organization_id, $order_id) {
	
		$user->organization['Organization']['id'] = $organization_id;
	
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;		
		
		$options = array();
		$options['conditions'] = array(// 'Cart.user_id' => $this->user->id,
										'Cart.deleteToReferent' => 'N', 
										'ArticlesOrder.order_id' => $order_id);
		
		$options['order'] = 'Article.name';
		$results = $ArticlesOrder->getArticoliEventualiAcquistiInOrdine($user, $options);

		$newResults = array();
		foreach($results as $numResults => $result) {
		
			$newResults[$numResults] = $result;
			$newResults[$numResults]['Article']['nota'] = ''; // $this->__pulisciCampo($result['Article']['nota']);
			$newResults[$numResults]['Article']['ingredienti'] = ''; // $this->__pulisciCampo($result['Article']['ingredienti']);
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
	
	private function __pulisciCampo($str) {
	
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