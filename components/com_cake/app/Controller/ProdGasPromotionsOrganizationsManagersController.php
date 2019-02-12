<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

/*
 * moduli utilizzati dai Manager dei GAS per gestire le promozioni a loro offerte
 */
class ProdGasPromotionsOrganizationsManagersController extends AppController {
    
   public function beforeFilter() {
   		parent::beforeFilter();
   		
		if (!$this->isManager()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}		
   }
   
   /*
    * elenco promozioni nuove (da associate ad un ordine del GAS)
	*/
   public function admin_index_new() {
	
		/*
		 * promozioni da associare ad un ordine di GAS
		 */
		$options = [];
		$options['conditions'] = ['ProdGasPromotionsOrganizationsManager.organization_id' => $this->user->organization['Organization']['id'],
								   'ProdGasPromotionsOrganizationsManager.order_id' => 0,
								   'ProdGasPromotion.state_code' => 'TRASMISSION-TO-GAS',
								   'DATE(ProdGasPromotion.data_inizio) <= CURDATE() AND DATE(ProdGasPromotion.data_fine) >= CURDATE()'];

		$this->ProdGasPromotionsOrganizationsManager->unbindModel(['belongsTo' => ['Organization']]);
		$options['recursive'] = 1;	
		$results = $this->ProdGasPromotionsOrganizationsManager->find('all', $options);
		self::d([$options, $results], $debug);	
		
		/*
		 * per ogni promozione estraggo ProdGasArticle
 		 */
		App::import('Model', 'ProdGasArticlesPromotion');
		foreach($results as $numResult => $result) {
				
					$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
					
					$ProdGasArticlesPromotion->unbindModel(['belongsTo' => ['ProdGasPromotion']]);
					
					$options = [];
					$options['conditions'] = ['ProdGasArticlesPromotion.prod_gas_promotion_id' => $result['ProdGasPromotion']['id']];
					$options['recursive'] = 1;
					$prodGasArticlesPromotionResults = $ProdGasArticlesPromotion->find('all', $options);
					if(!empty($prodGasArticlesPromotionResults)) {
						$results[$numResult]['Article'] = $prodGasArticlesPromotionResults;
					}			
		}
		
		self::d([$options, $results], $debug);	

		$this->set('results', $results);
	}
	
   /*
    * elenco promozioni gia' associate ad un ordine del GAS
	*/
   public function admin_index() {
		
		/*
		 * promozioni gia' associate ad un ordine di GAS
		 */
		$options = [];
		$options['conditions'] = array('ProdGasPromotionsOrganizationsManager.organization_id' => $this->user->organization['Organization']['id'],
									   'ProdGasPromotionsOrganizationsManager.order_id !=' => 0,
									   'ProdGasPromotion.state_code != ' => 'WORKING');

		$this->ProdGasPromotionsOrganizationsManager->unbindModel(array('belongsTo' => array('Organization')));
		$options['recursive'] = 1;
		$results = $this->ProdGasPromotionsOrganizationsManager->find('all', $options);
		
		/*
		 * per ogni ordine estraggo Delivery , SuppliersOrganization
 		 */
		App::import('Model', 'Delivery');
		App::import('Model', 'SuppliersOrganization'); 
		foreach($results as $numResult => $result) {
			if(isset($result['Order'])) {
					
				$order_id = $result['Order']['id'];
				$delivery_id = $result['Order']['delivery_id'];
				$supplier_organization_id = $result['Order']['supplier_organization_id'];
				
				$Delivery = new Delivery;
				
				$options = [];
				$options['conditions'] = array('Delivery.organization_id' => $this->user->organization['Organization']['id'],
											   'Delivery.id' => $delivery_id);
				$options['recursive'] = -1;
				$deliveryResults = $Delivery->find('first', $options);
				if(!empty($deliveryResults)) {
					$results[$numResult]['Delivery'] = $deliveryResults['Delivery'];
				}
				
				
				$SuppliersOrganization = new SuppliersOrganization;
				
				$options = [];
				$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
											   'SuppliersOrganization.id' => $supplier_organization_id);
				$options['recursive'] = -1;
				$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
				if(!empty($suppliersOrganizationResults)) {
					$results[$numResult]['SuppliersOrganization'] = $suppliersOrganizationResults['SuppliersOrganization'];
				}
			}
		} 
		
		self::d($results, $debug);		

		$this->set('results', $results);
	}
	
	public function admin_add($prod_gas_promotion_id=0) { 

		$debug = false;
		$continua=true;
		$msg_errors = "";
		
		self::d($this->request->data, $debug);		
	
		if ($this->request->is('post') || $this->request->is('put')) {
			$prod_gas_promotion_id = $this->request->data['ProdGasPromotionsOrganizationsManager']['prod_gas_promotion_id'];
		}	
			
		if (empty($prod_gas_promotion_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			

		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {

			/*
			 * import Produttore
			 */
			$supplier_organization_id = $this->ProdGasPromotionsOrganizationsManager->importProdGasSupplier($this->user, $prod_gas_promotion_id, $debug);
			if($supplier_organization_id===false) {
				$msg_errors .= __('The prodGasPromotionsOrganizationsMangers import supplier could not be saved. Please, try again.');
				$continua=false;
			}

			if($continua) {
				/*
				 * consegna da definire
				 */
				if($this->request->data['typeDelivery']=='to_defined') {
					App::import('Model', 'Delivery');
					$Delivery = new Delivery;
					
					$deliverySysResults = $Delivery->getDeliverySys($this->user);
					$this->request->data['ProdGasPromotionsOrganizationsManager']['delivery_id'] = $deliverySysResults ['Delivery']['id'];
				}
	
		
				/*
				 * richiamo la validazione 
				 */
				$msg_errors = $this->ProdGasPromotionsOrganizationsManager->getMessageErrorsToValidate($this->ProdGasPromotionsOrganizationsManager, $this->request->data);
				if(!empty($msg_errors)) {
					$continua=false;
				}
			}
				
			/*
			 * creo l'ordine per il GAS
			 */			
			if($continua) {
				$order_id = $this->_add_order($this->user, $prod_gas_promotion_id, $supplier_organization_id, $this->request->data, $debug);
				if(!is_numeric($order_id)) {
					$msg_errors .= $order_id;
					$continua=false;
				}
			}
			
			/*
			 * Associo l'ordine per il GAS con la promozione (ProdGasPromotionsOrganization)
			 */			
			if($continua) {	
				$this->ProdGasPromotionsOrganizationsManager->importProdGasArticlesPromotions($this->user, $prod_gas_promotion_id, $order_id, $debug); 
				
				App::import('Model', 'ProdGasPromotionsOrganization');
				$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;
				
				$options = [];
				$options['conditions'] = ['ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id,
										   'ProdGasPromotionsOrganization.organization_id' => $this->user->organization['Organization']['id'] // e' quello del gas
										   ];
				$options['recursive'] = -1;
				$data = [];
				$data = $ProdGasPromotionsOrganization->find('first', $options);
				$data['ProdGasPromotionsOrganization']['order_id'] = $order_id;
				$data['ProdGasPromotionsOrganization']['nota'] = $nota;
				$ProdGasPromotionsOrganization->create();
				if(!$ProdGasPromotionsOrganization->save($data)) {
					$msg_errors .= "Error ProdGasPromotionsOrganization SAVE";
					$continua=false;
				} 
			}
			
			
			if($continua) {			
				/*
				 * aggiorno lo stato dell'ordine
				 * 	da CREATE-INCOMPLETE a OPEN-NEXT o OPEN
				 * */
				$utilsCrons = new UtilsCrons(new View(null));
				$utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $order_id);
				
				$msg_errors .= __('The ProdGasPromotionOrganizationsManager has been saved');
			} 
				
			$this->Session->setFlash($msg_errors);
			
			if($continua) {	
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasPromotionsOrganizationsManagers&action=index';  // redirect Elenco promozioni associate ad un ordine 
				if(!$debug) $this->myRedirect($url);
			}
		}
		else {
			$data_inizio = '';
			$data_inizio_db = '';
			$data_fine = '';
			$data_fine_db = '';
		}
		
		$this->set('data_inizio', $data_inizio);
		$this->set('data_inizio_db', $data_inizio_db);
		$this->set('data_fine', $data_fine);
		$this->set('data_fine_db', $data_fine_db);

		/*
		 * dati promozione del GAS (gli passo $this->user->organization['Organization']['id'])
		 */
		App::import('Model', 'ProdGasPromotion');
		$ProdGasPromotion = new ProdGasPromotion;
		 
		$promotionResults = $ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id, $this->user->organization['Organization']['id'], $debug);
		$this->set('promotionResults', $promotionResults);
		
		/*
		 * prendo solo le consegne aperte
		 */
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options =  [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									'Delivery.isVisibleBackOffice' => 'Y',
									'Delivery.stato_elaborazione' => 'OPEN',
									'Delivery.sys'=>'N',
									'DATE(Delivery.data) >= CURDATE()'];
		$options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$options['order'] = ['Delivery.data' => 'asc'];
		$deliveries = $Delivery->find('list', $options); 
		$this->set(compact('deliveries'));

		$isVisibleFrontEnd = ClassRegistry::init('Order')->enumOptions('isVisibleFrontEnd');
		$isVisibleBackOffice = ClassRegistry::init('Order')->enumOptions('isVisibleBackOffice');
		$this->set(compact('isVisibleFrontEnd','isVisibleBackOffice'));
		
		/*
		 * se sono ManagerDelivery ho il link per cerare una nuova consenge, se no invio una mail
		 */
		$this->set('isManagerDelivery', $this->isManagerDelivery());
	}

	public function admin_edit($delivery_id, $order_id) {

		$debug = false;
		$continua=true;
		$msg_errors = "";

		if (empty($delivery_id) || empty($order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if($debug) {
			echo "<pre>request->data \n";
			print_r($this->request->data);
			echo "</pre>";			
		}
		
		/*
		 * da order_id recupero dati promozione
		 */
		App::import('Model', 'Order');
		$Order = new Order;

		$options =  [];
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.delivery_id' => $delivery_id,
									    'Order.id' => $order_id);
		$options['recursive'] = 0;
		$this->request->data = $Order->find('first', $options);
		
		$prod_gas_promotion_id = $this->request->data['Order']['prod_gas_promotion_id'];
		$supplier_id = $this->request->data['SuppliersOrganization']['supplier_id'];
		if (empty($prod_gas_promotion_id) || empty($supplier_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * dati promozione del GAS (gli passo $this->user->organization['Organization']['id'])
		 */
		App::import('Model', 'ProdGasPromotion');
		$ProdGasPromotion = new ProdGasPromotion;
		 
		$promotionResults = $ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id, $this->user->organization['Organization']['id'], $debug);
		$this->set(compact('promotionResults'));
		
		/*
		 * prendo solo le consegne aperte
		 */
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options =  [];
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'Delivery.stato_elaborazione' => 'OPEN',
									    'Delivery.sys'=>'N',
										'DATE(Delivery.data) >= CURDATE()');
		$options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$options['order'] = ['Delivery.data' => 'asc'];
		$deliveries = $Delivery->find('list', $options); 
		$this->set(compact('deliveries'));

		$isVisibleFrontEnd = ClassRegistry::init('Order')->enumOptions('isVisibleFrontEnd');
		$isVisibleBackOffice = ClassRegistry::init('Order')->enumOptions('isVisibleBackOffice');
		$this->set(compact('isVisibleFrontEnd','isVisibleBackOffice'));
		
		/*
		 * se sono ManagerDelivery ho il link per cerare una nuova consenge, se no invio una mail
		 */
		$this->set('isManagerDelivery', $this->isManagerDelivery());
		
		$this->set(compact('delivery_id','order_id','supplier_id','prod_gas_promotion_id'));
	}
	
	public function admin_articles_orders_index($delivery_id, $order_id) {

		$debug = false;
		$continua=true;
		$msg_errors = "";

		if (empty($delivery_id) || empty($order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * da order_id recupero dati promozione
		 */
		App::import('Model', 'Order');
		$Order = new Order;

		$options =  [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
									'Order.delivery_id' => $delivery_id,
								    'Order.id' => $order_id];
		$options['fields'] = ['Order.prod_gas_promotion_id', 'SuppliersOrganization.supplier_id'];
		$options['recursive'] = 0;
		$orderResults = $Order->find('first', $options);
		
		$prod_gas_promotion_id = $orderResults['Order']['prod_gas_promotion_id'];
		$supplier_id = $orderResults['SuppliersOrganization']['supplier_id'];
		if (empty($prod_gas_promotion_id) || empty($supplier_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * dati promozione del GAS (gli passo $this->user->organization['Organization']['id'])
		 */
		App::import('Model', 'ProdGasPromotion');
		$ProdGasPromotion = new ProdGasPromotion;
		 
		$results = $ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id, $this->user->organization['Organization']['id']);
		$this->set('results', $results);
		
		$this->set(compact('delivery_id','order_id','supplier_id','prod_gas_promotion_id'));	
	}
	
	private function _add_order($user, $prod_gas_promotion_id, $supplier_organization_id, $requestData, $debug=false) {

		$msg_errors = '';
	
		App::import('Model', 'Order');
		$Order = new Order;
		
		$data = []; // array con i dati dell'ordine
		$data['Order']['organization_id'] = $user->organization['Organization']['id'];
		$data['Order']['prod_gas_promotion_id'] = $prod_gas_promotion_id;
		$data['Order']['supplier_organization_id'] = $supplier_organization_id;
		$data['Order']['delivery_id'] = $requestData['ProdGasPromotionsOrganizationsManager']['delivery_id'];
		
		$data['Order']['data_inizio_db'] = $requestData['ProdGasPromotionsOrganizationsManager']['data_inizio_db'];
		$data['Order']['data_inizio'] = $requestData['ProdGasPromotionsOrganizationsManager']['data_inizio'];
		$data['Order']['data_fine_db'] = $requestData['ProdGasPromotionsOrganizationsManager']['data_fine_db'];
		$data['Order']['data_fine'] = $requestData['ProdGasPromotionsOrganizationsManager']['data_fine'];
		
		$data['Order']['qta_massima'] = 0;
		$data['Order']['qta_massima_um'] = '';
		$data['Order']['send_mail_qta_massima'] = 'N';
		$data['Order']['importo_massimo']=0;
		$data['Order']['send_mail_importo_massimo'] = 'N';
		
		if(isset($requestData['ProdGasPromotionsOrganizationsManager']['hasTrasport']))
			$data['Order']['hasTrasport'] = $requestData['ProdGasPromotionsOrganizationsManager']['hasTrasport'];
		else
			$data['Order']['hasTrasport'] = 'N';

		if(isset($requestData['ProdGasPromotionsOrganizationsManager']['hasCostMore']))
			$data['Order']['hasCostMore'] = $requestData['ProdGasPromotionsOrganizationsManager']['hasCostMore'];
		else
			$data['Order']['hasCostMore'] = 'N';
			
		if(isset($requestData['ProdGasPromotionsOrganizationsManager']['isVisibleFrontEnd']))
			$data['Order']['isVisibleFrontEnd'] = $requestData['ProdGasPromotionsOrganizationsManager']['isVisibleFrontEnd'];
		else
			$data['Order']['isVisibleFrontEnd'] = 'Y';
		if(isset($requestData['ProdGasPromotionsOrganizationsManager']['isVisibleBackOffice']))
			$data['Order']['isVisibleBackOffice'] = $requestData['ProdGasPromotionsOrganizationsManager']['isVisibleBackOffice'];
		else
			$data['Order']['isVisibleBackOffice'] = 'Y';
			
		/*
		 * rimane allo stato CREATE-INCOMPLETE finche' non crea qualche associazione con gli articoli
		 */
		$data['Order']['state_code'] = 'CREATE-INCOMPLETE';
		$data['Order']['type_draw'] = 'PROMOTION';
				
		$data['Order']['mail_open_send'] = $Order->setOrderMailOpenSend($user, $requestData);
		
		/*
		 *  bugs: il campo mail_open_testo a volta arriva con un <br /> 
		 */
		$data['Order']['mail_open_testo'] = $requestData['ProdGasPromotionsOrganizationsManager']['mail_open_testo']; 
		if($data['Order']['mail_open_testo']=='<br>' || $data['Order']['mail_open_testo']=='<br/>' || $data['Order']['mail_open_testo']=='<br />')
			$data['Order']['mail_open_testo'] = '';
		
		$data['Order']['mail_open_testo'] = $requestData['ProdGasPromotionsOrganizationsManager']['nota'];
		if($data['Order']['nota']=='<br>' || $data['Order']['nota']=='<br/>' || $data['Order']['nota']=='<br />')
			$data['Order']['nota'] = '';			
		
		$data['Order']['tesoriere_importo_pay'] = '0.00';
		$data['Order']['tesoriere_stato_pay'] = 'N';
	
		self::d($data, $debug);
		
		/*
		 * richiamo la validazione 
		 */
		$msg_errors = $Order->getMessageErrorsToValidate($Order, $data);
		if(!empty($msg_errors)) {
			return $msg_errors;	
		}
			
		$Order->create();
		if($Order->save($data)) {
			$order_id = $Order->getLastInsertId();
			$msg_errors = $order_id;
		} 
		else {
			$msg_errors .= "Ordine non creato!";
			
			return $msg_errors;				
		}

		if($debug) {
			echo '<br />Ordine CREATO: id '.$order_id;
		}
		
		return $msg_errors;
	}
}