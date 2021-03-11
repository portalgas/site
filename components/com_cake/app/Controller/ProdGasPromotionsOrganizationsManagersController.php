<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

/*
 * moduli utilizzati dai Referenti / SuperReferenti dei GAS per gestire le promozioni a loro offerte
 */
class ProdGasPromotionsOrganizationsManagersController extends AppController {
    
   public $components = ['Connects'];

   public function beforeFilter() {
   		parent::beforeFilter();
   		
		if (!$this->isReferente() && !$this->isSuperReferente()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}		
   }
   
   /*
    * elenco promozioni nuove (da associate ad un ordine del GAS)
	*/
   public function admin_index_new() {

		$rules = [];
		$rules['isSuperReferente'] = $this->isSuperReferente();
		$rules['isReferente'] = $this->isReferente();

		$results = $this->ProdGasPromotionsOrganizationsManager->getWaitingPromotions($this->user, $rules, $debug);
		$this->set(compact('results'));

		$params = ['order_type_id' => Configure::read('Order.type.promotion'), 
				   'prod_gas_promotion_id' => $result['ProdGasPromotion']['id']];
		$url_query = $this->Connects->createQueryParams('admin/orders', 'add', $params);
		$this->set(compact('url_query'));
	}
	
   /*
    * elenco promozioni gia' associate ad un ordine del GAS
	*/
   public function admin_index() {
		
		$results = $this->ProdGasPromotionsOrganizationsManager->getOpenPromotions($this->user, $debug);
		$this->set(compact('results'));
	}
	
	public function admin_add($prod_gas_promotion_id=0) { 

		$debug = false;
		$continua = true;
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
				 * richiamo la validazione per $useTable = 'prod_gas_promotions_organizations';
				 */
				if($debug) debug($this->request->data);	
				$msg_errors = $this->ProdGasPromotionsOrganizationsManager->getMessageErrorsToValidate($this->ProdGasPromotionsOrganizationsManager, $this->request->data);
				if(!empty($msg_errors)) {
					if($debug) debug($msg_errors);
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
					if($debug) debug($msg_errors);
					$continua=false;
				}
			}
			
			/*
			 * Associo gli articoli della promozione a ArticlesOrders
			 */					
			if($continua) {	
				if(!$this->ProdGasPromotionsOrganizationsManager->importProdGasArticlesPromotions($this->user, $prod_gas_promotion_id, $order_id, $debug)) {
					$continua=false;
				}
			}
		
			/*
			 * Associo l'ordine per il GAS con la promozione (ProdGasPromotionsOrganization)
			 */	
			if($continua) {
				App::import('Model', 'ProdGasPromotionsOrganization');
				$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;
				
				$options = [];
				$options['conditions'] = [
					'ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id,
					'ProdGasPromotionsOrganization.organization_id' => $this->user->organization['Organization']['id'] // e' quello del gas
										   ];
				$options['recursive'] = -1;
				// debug($options);
				
				$data = []; 
				$data = $ProdGasPromotionsOrganization->find('first', $options);
				// debug($data);
				if(empty($data)) {
					debug($options);
					$msg_errors .= "Error ProdGasPromotionsOrganization not found!";
					$continua=false;					
				}
				else {
					$data['ProdGasPromotionsOrganization']['order_id'] = $order_id;
					$data['ProdGasPromotionsOrganization']['state_code'] = 'OPEN';
					$data['ProdGasPromotionsOrganization']['nota_user'] = '';
					if(!empty($data['ProdGasPromotionsOrganization']['nota_user']))
						$data['ProdGasPromotionsOrganization']['user_id'] = $this->user->get('id');
					else
						$data['ProdGasPromotionsOrganization']['user_id'] = 0;
					$ProdGasPromotionsOrganization->create();
					if(!$ProdGasPromotionsOrganization->save($data)) {
						$msg_errors .= "Error ProdGasPromotionsOrganization SAVE";
						$continua=false;
					} 
				} // end if(empty($data))
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
		 * prendo solo le consegne scelte dal produttore
		 */
		App::import('Model', 'ProdGasPromotionsOrganizationsDelivery');
		$ProdGasPromotionsOrganizationsDelivery = new ProdGasPromotionsOrganizationsDelivery;
		
		$deliveries = $ProdGasPromotionsOrganizationsDelivery->getOrganizationsDeliveryList($this->user, $prod_gas_promotion_id, $debug);
		$this->set(compact('deliveries'));

		$isVisibleFrontEnd = ClassRegistry::init('Order')->enumOptions('isVisibleFrontEnd');
		$isVisibleBackOffice = ClassRegistry::init('Order')->enumOptions('isVisibleBackOffice');
		$this->set(compact('isVisibleFrontEnd','isVisibleBackOffice'));
		
		/*
		 * se sono ManagerDelivery ho il link per creare una nuova consenge, se no invio una mail
		 */
		$this->set('isManagerDelivery', $this->isManagerDelivery());
	}

	public function admin_edit($delivery_id=0, $order_id=0) {

		$debug = false;
		$continua=true;
		$msg_errors = "";

		if (empty($delivery_id))	
			$delivery_id = $this->request->data['ProdGasPromotionsOrganizationsManager']['delivery_id'];
			
		if (empty($order_id))	
			$order_id = $this->request->data['ProdGasPromotionsOrganizationsManager']['order_id'];

		if (empty($delivery_id) || empty($order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if($debug) debug($this->request->data);
		
		/*
		 * da order_id recupero dati promozione
		 */
		App::import('Model', 'Order');
		$Order = new Order;

		$options =  [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
									'Order.delivery_id' => $delivery_id,
									'Order.id' => $order_id];
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
		 * prendo solo le consegne scelte dal produttore
		 */
		App::import('Model', 'ProdGasPromotionsOrganizationsDelivery');
		$ProdGasPromotionsOrganizationsDelivery = new ProdGasPromotionsOrganizationsDelivery;
		
		$deliveries = $ProdGasPromotionsOrganizationsDelivery->getOrganizationsDeliveryList($this->user, $prod_gas_promotion_id, $debug);
		$this->set(compact('deliveries'));

		$isVisibleFrontEnd = ClassRegistry::init('Order')->enumOptions('isVisibleFrontEnd');
		$isVisibleBackOffice = ClassRegistry::init('Order')->enumOptions('isVisibleBackOffice');
		$this->set(compact('isVisibleFrontEnd','isVisibleBackOffice'));
		
		/*
		 * se sono ManagerDelivery ho il link per creare una nuova consenge, se no invio una mail
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
		$data['Order']['order_type_id'] = Configure::read('Order.type.promotion');
		$data['Order']['supplier_organization_id'] = $supplier_organization_id;
		$data['Order']['owner_organization_id'] = $user->organization['Organization']['id'];
		$data['Order']['owner_supplier_organization_id'] = $supplier_organization_id;
		$data['Order']['prod_gas_promotion_id'] = $prod_gas_promotion_id;
		$data['Order']['delivery_id'] = $requestData['ProdGasPromotionsOrganizationsManager']['delivery_id'];
		
		$data['Order']['data_inizio_db'] = $requestData['ProdGasPromotionsOrganizationsManager']['data_inizio_db'];
		$data['Order']['data_inizio'] = $requestData['ProdGasPromotionsOrganizationsManager']['data_inizio'];
		$data['Order']['data_fine_db'] = $requestData['ProdGasPromotionsOrganizationsManager']['data_fine_db'];
		$data['Order']['data_fine'] = $requestData['ProdGasPromotionsOrganizationsManager']['data_fine'];
		
		$data['Order']['qta_massima'] = 0;
		$data['Order']['qta_massima_um'] = '';
		$data['Order']['send_mail_qta_massima'] = 'N';
		$data['Order']['importo_massimo'] = 0;
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
	
	public function admin_reject($prod_gas_promotion_id=0) { 

		$debug = false;
		
		self::dd($this->request->data, $debug);		
		if(isset($this->request->data['ProdGasPromotionsOrganizationsManager']['prod_gas_promotion_id']))
			$prod_gas_promotion_id = $this->request->data['ProdGasPromotionsOrganizationsManager']['prod_gas_promotion_id'];
			
		if (empty($prod_gas_promotion_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		if ($this->request->is('post') || $this->request->is('put')) {

			App::import('Model', 'ProdGasPromotionsOrganization');
			$ProdGasPromotionsOrganization = new ProdGasPromotionsOrganization;
			
			if(isset($this->request->data['ProdGasPromotionsOrganizationsManager']['nota_user'])) {
				
				$nota_user = $this->request->data['ProdGasPromotionsOrganizationsManager']['nota_user'];
				
				$options = [];
				$options['conditions'] = ['ProdGasPromotionsOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'ProdGasPromotionsOrganization.prod_gas_promotion_id' => $prod_gas_promotion_id];
				$options['recursive'] = -1;
				$prodGasPromotionsOrganizationResults = $ProdGasPromotionsOrganization->find('first', $options);
					
				$prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['state_code'] = 'REJECT';
				$prodGasPromotionsOrganizationResults['ProdGasPromotionsOrganization']['nota_user'] = $nota_user;

				self::d($options, $debug);
				self::d($prodGasPromotionsOrganizationResults, $debug);

				$ProdGasPromotionsOrganization->create();
				if(!$ProdGasPromotionsOrganization->save($prodGasPromotionsOrganizationResults)) {
					$this->Session->setFlash(__('The prodGasPromotionsOrganizations could not be saved. Please, try again.'));
				}
				else
					$this->Session->setFlash(__('ProdGasPromotionsOrganization REJECT'));
			}

			if(!$debug) $this->myRedirect(['action' => 'index_new']);
			
		} // end post

		/*
		 * dati promozione del GAS (gli passo $this->user->organization['Organization']['id'])
		 */	
		App::import('Model', 'ProdGasPromotion');
		$ProdGasPromotion = new ProdGasPromotion;
				 	 
		$promotionResults = $ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id, $this->user->organization['Organization']['id'], $debug);
		$this->set(compact('promotionResults'));	
		
		$rejectsNotes = $this->ProdGasPromotionsOrganizationsManager->getRejectNotes($this->user);
		$this->set(compact('rejectsNotes', 'prod_gas_promotion_id'));	
	}
	
	public function admin_contact($prod_gas_promotion_id=0) { 

		$debug = false;
							
		if (empty($prod_gas_promotion_id)) {
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
	}	
}