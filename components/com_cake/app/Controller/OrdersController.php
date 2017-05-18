<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class OrdersController extends AppController {

   public $components = array('RequestHandler', 'Routings', 'ActionsDesOrder', 'Documents'); 
   
   public $helpers = array('OrderHome', 'Text');
   
   public function beforeFilter() {
   		parent::beforeFilter();
   		
   		/* ctrl ACL */
	   	$actionWithPermission = array('admin_home', 'admin_edit', 'admin_delete', 'admin_close', 'admin_sotto_menu', 'admin_sotto_menu_bootstrap', 'admin_edit_validation_cart', 'admin_delivery_change', 'admin_mail_supplier');
	   	if (in_array($this->action, $actionWithPermission)) {
	   		 
	   		if($this->isSuperReferente()) {
	   				
	   		}
	   		else {
		 		if(empty($this->order_id) || !$this->isReferentGeneric() || !$this->Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) { 
		 			$this->Session->setFlash(__('msg_not_permission'));
					$this->myRedirect(Configure::read('routes_msg_stop'));
		  		}
	   		}
   		}   	
   		/* ctrl ACL */  

   		 
   		$actionWithPermission = array('admin_home');
   		if(in_array($this->action, $actionWithPermission)) {
   			/*
   			 * ctrl che la consegna sia visibile in backoffice
   			*/
   			if(!empty($this->delivery_id)) {
   				 
   				App::import('Model', 'Delivery');
   				$Delivery = new Delivery;
   				$results = $Delivery->read($this->user->organization['Organization']['id'], null, $this->delivery_id);
   				if(!empty($results) && $results['Delivery']['isVisibleBackOffice']=='N') {
   					$this->Session->setFlash(__('msg_delivery_not_visible_backoffice'));
   					$this->myRedirect(Configure::read('routes_msg_stop'));
   				}
   			}
   		
   			/*
   			 * ctrl che l'ordine sia visibile in backoffice
   			*/
   			$results = $this->Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
   			if($results['Order']['isVisibleBackOffice']=='N') {
   				$this->Session->setFlash(__('msg_order_not_visible_backoffice'));
   				$this->myRedirect(Configure::read('routes_msg_stop'));
   			}
   		} // end if (in_array($this->action, $actionWithPermission))
   			   		   		
		/*
		 * ctrl referentTesoriere
		*/
   		$this->set('isReferenteTesoriere',$this->isReferenteTesoriere); 
   }
       
   /*
    * tutti gli ordini con 'Delivery.stato_elaborazione' => 'OPEN' 
    */
   public function admin_index() {

	   	App::import('Model', 'Supplier');
	   	
	   	// App::import('Model', 'SuppliersOrganizationsReferent');
	   	
	   	App::import('Model', 'DesOrdersOrganization');
			
	   	/*
	   	 * aggiorno lo stato degli ordini
	   	 * */
   		$utilsCrons = new UtilsCrons(new View(null));
   		if(Configure::read('developer.mode')) echo "<pre>";
   		$utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
   		if(Configure::read('developer.mode')) echo "</pre>";
   		
   		$this->__ctrl_data_visibility();
   		$this->__ctrl_state_code_error(); 
   		
   				
		$SqlLimit = 75;
		$conditions[] = array('Delivery.organization_id'=>$this->user->organization['Organization']['id'],
							  'Order.organization_id'=>$this->user->organization['Organization']['id'],
							  'Delivery.isVisibleBackOffice'=>'Y',
							  'Delivery.stato_elaborazione'=>'OPEN',
							  'SuppliersOrganization.stato' => 'Y');
						
		if(!$this->isSuperReferente()) {
			$conditions[] = array('Order.supplier_organization_id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');
		}

		$this->Order->recursive = 0; 
	    $this->paginate = array('conditions' => $conditions,'order'=>'Delivery.data asc, Delivery.id, Order.data_inizio asc','limit' => $SqlLimit);
		$results = $this->paginate('Order');

		foreach($results as $numResult => $result) {
	
			/*
			 * Suppliers per l'immagine
			 * */
			$Supplier = new Supplier;
			
			$options = array();
			$options['conditions'] = array('Supplier.id' => $result['SuppliersOrganization']['supplier_id']);
			$options['fields'] = array('Supplier.img1');
			$options['recursive'] = -1;
			$SupplierResults = $Supplier->find('first', $options);
			if(!empty($SupplierResults))
				$results[$numResult]['Supplier']['img1'] = $SupplierResults['Supplier']['img1'];

			/*
			 * SuppliersOrganizationsReferent per la tipologia REFERENTE COREFERENTE TESORIERE
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
			
			$options = array();
			$options['conditions'] = array('SuppliersOrganizationsReferent.organization_id' => $this->user->organization['Organization']['id'],
					'SuppliersOrganizationsReferent.supplier_organization_id' => $result['Order']['supplier_organization_id'],
					'SuppliersOrganizationsReferent.user_id' => $this->user->get('id'));
			$options['fields'] = array('type');
			$options['recursive'] = -1;
			$SuppliersOrganizationsReferentResults = $SuppliersOrganizationsReferent->find('first', $options);
			if(!empty($SuppliersOrganizationsReferentResults))
				$results[$numResult]['SuppliersOrganizationsReferent']['type'] = $SuppliersOrganizationsReferentResults['SuppliersOrganizationsReferent']['type'];
			* */
			
			/*
			 * DES
			 */		
			if($this->user->organization['Organization']['hasDes']=='Y') {		
	
				$DesOrdersOrganization = new DesOrdersOrganization();
				
				$options = array();
				$options['conditions'] = array('DesOrdersOrganization.order_id' => $result['Order']['id'],
											   'DesOrdersOrganization.organization_id' => $this->user->organization['Organization']['id']);
				$options['recursive'] = -1;
				$desOrdersOrganization = $DesOrdersOrganization->find('first', $options);
				$results[$numResult]['DesOrdersOrganization'] = $desOrdersOrganization['DesOrdersOrganization'];
			} // DES
			 				
		} // loop Orders

		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
		
		/*
		 * ctrl se ho i permessi per modificare le consegne
		 */
		if(!$this->isManager() && !$this->isManagerDelivery())
			$delivery_link_permission = false;
		else
			$delivery_link_permission = true;
		$this->set('delivery_link_permission', $delivery_link_permission);
		
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
	}

	/*
	 * ordini con consegne 'Delivery.stato_elaborazione' => 'CLOSE'
	 */
	public function admin_index_history() {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;

		App::import('Model', 'SuppliersOrganizationsReferent');
		$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;
		
		App::import('Model', 'Supplier');
		
		$SqlLimit = 20;
		$conditions[] = array('Delivery.organization_id'=>$this->user->organization['Organization']['id'],
							  'Order.organization_id'=>$this->user->organization['Organization']['id'],
							  'Delivery.sys'=>'N',
							  'Delivery.stato_elaborazione'=>'CLOSE');
		
		if(!$this->isSuperReferente()) {
			$conditions[] = array('Order.supplier_organization_id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');
		}
	
		$this->Order->unbindModel(array('belongsTo' => array('ArticlesOrder')));
		$this->Order->recursive = 0;
		$this->paginate = array('conditions' => $conditions,'order'=>'Delivery.data desc,Order.data_inizio','limit' => $SqlLimit);
		$results = $this->paginate('Order');

		foreach($results as $numResult => $result) {

			/*
			 * Suppliers per l'immagine
			* */
			$Supplier = new Supplier;
				
			$options = array();
			$options['conditions'] = array('Supplier.id' => $result['SuppliersOrganization']['supplier_id']);
			$options['fields'] = array('Supplier.img1');
			$options['recursive'] = -1;
			$SupplierResults = $Supplier->find('first', $options);
			if(!empty($SupplierResults))
				$results[$numResult]['Supplier']['img1'] = $SupplierResults['Supplier']['img1'];
			
			/*
			 * SuppliersOrganizationsReferent per la tipologia REFERENTE COREFERENTE TESORIERE
			* */
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
				
			$options = array();
			$options['conditions'] = array('SuppliersOrganizationsReferent.organization_id' => $this->user->organization['Organization']['id'],
										'SuppliersOrganizationsReferent.supplier_organization_id' => $result['Order']['supplier_organization_id'],
										'SuppliersOrganizationsReferent.user_id' => $this->user->get('id'));
			$options['fields'] = array('type');
			$options['recursive'] = -1;
			$SuppliersOrganizationsReferentResults = $SuppliersOrganizationsReferent->find('first', $options);
			if(!empty($SuppliersOrganizationsReferentResults))
				$results[$numResult]['SuppliersOrganizationsReferent']['type'] = $SuppliersOrganizationsReferentResults['SuppliersOrganizationsReferent']['type'];
					
			/*
			 * richieste di pagamento
			 */
			$options = array();
			$options['conditions'] = array('RequestPaymentsOrder.organization_id' => $this->user->organization['Organization']['id'],
											'RequestPaymentsOrder.order_id' => $result['Order']['id']);
			$options['recursive'] = 0;
			$RequestPaymentsOrder->unbindModel(array('belongsTo' => array('Order')));
			$resultsRequestPaymentsOrder = $RequestPaymentsOrder->find('first', $options);
			
			if(!empty($resultsRequestPaymentsOrder)) {
				$results[$numResult]['RequestPayment'] = $resultsRequestPaymentsOrder['RequestPayment'];
				$results[$numResult]['RequestPaymentsOrder'] = $resultsRequestPaymentsOrder['RequestPaymentsOrder'];
			}
		}

		$this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
	}
	
	/*
	 * delivery_id valorizzato se lo richiamo da Ajax/view_deliveries.ctp
	 * supplier_organization_id se lo richiamo da Ajax/view_suppliers_organizations.ctp
	 * des_order_id se arrivo da un ordine condiviso
	 */
	public function admin_add($delivery_id=0, $order_id=0, $supplier_organization_id=0, $des_order_id=0) {
	
		$debug=false;

		/*
		 * se ritorno per validationError recupero eventuali dati
		 */
		if(empty($des_order_id))
			$des_order_id = $this->request->data['Order']['des_order_id'];
		if(empty($supplier_organization_id))
			$supplier_organization_id = $this->request->data['Order']['supplier_organization_id'];
	
		$msg = "";

		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			$data_inizio = $this->request->data['Order']['data_inizio'];
			$data_inizio_db = $this->request->data['Order']['data_inizio_db'];
			$data_fine = $this->request->data['Order']['data_fine'];
			$data_fine_db = $this->request->data['Order']['data_fine_db'];
			
			$qta_massima = $this->request->data['Order']['qta_massima'];
			$qta_massima_um = $this->request->data['Order']['qta_massima_um'];
			if($qta_massima==0)
				$qta_massima_um = '';
			$importo_massimo = $this->request->data['Order']['importo_massimo'];
				
			if(isset($this->request->data['Order']['hasTrasport']))
				$hasTrasport = $this->request->data['Order']['hasTrasport'];
			else
				$hasTrasport = 'N';

			if(isset($this->request->data['Order']['hasCostMore']))
				$hasCostMore = $this->request->data['Order']['hasCostMore'];
			else
				$hasCostMore = 'N';

			if(isset($this->request->data['Order']['hasCostLess']))
				$hasCostLess = $this->request->data['Order']['hasCostLess'];
			else
				$hasCostLess = 'N';
				
			if(isset($this->request->data['Order']['isVisibleFrontEnd']))
				$isVisibleFrontEnd = $this->request->data['Order']['isVisibleFrontEnd'];
			else
				$isVisibleFrontEnd = 'Y';
			if(isset($this->request->data['Order']['isVisibleBackOffice']))
				$isVisibleBackOffice = $this->request->data['Order']['isVisibleBackOffice'];
			else
				$isVisibleBackOffice = 'Y';
				
			if(isset($this->request->data['Order']['des_order_id']))
				$des_order_id = $this->request->data['Order']['des_order_id'];
			else
				$des_order_id = 0;	
		}
		else {
			$data_inizio = '';
			$data_inizio_db = '';
			$data_fine = '';
			$data_fine_db = '';
			$qta_massima = 0;
			$qta_massima_um = '';
			$importo_massimo = 0; 			
			$hasTrasport = 'N';
			$hasCostMore = 'N';
			$hasCostLess = 'N';
			$isVisibleFrontEnd = 'Y';
			$isVisibleBackOffice = 'Y';
		}
		
		$this->set('des_order_id', $des_order_id);
		$this->set('data_inizio', $data_inizio);
		$this->set('data_inizio_db', $data_inizio_db);
		$this->set('data_fine', $data_fine);
		$this->set('data_fine_db', $data_fine_db);
		$this->set('qta_massima', $qta_massima);
		$this->set('qta_massima_um', $qta_massima_um);
		$this->set('importo_massimo', $importo_massimo);
		$this->set('hasTrasportDefault', $hasTrasport);
		$this->set('hasCostMoreDefault', $hasCostMore);
		$this->set('hasCostLessDefault', $hasCostLess);
		$this->set('isVisibleFrontEndDefault', $isVisibleFrontEnd);
		$this->set('isVisibleBackOfficeDefault', $isVisibleBackOffice);
		
		if ($this->request->is('post') || $this->request->is('put')) {

			$this->order_id = $this->__add($this->user, $this->request->data, $debug);	

			if($this->order_id > 0) {
				
				/*
				 * associo gli articoli all'ordine
				 */
				 if($this->user->organization['Organization']['hasDes']=='Y' && !empty($des_order_id)) {
				 
					App::import('Model', 'DesOrdersOrganization');
					$DesOrdersOrganization = new DesOrdersOrganization();
					
					$data = array();
					$data['DesOrdersOrganization']['des_id'] = $this->user->des_id;
					$data['DesOrdersOrganization']['des_order_id'] = $des_order_id;
					$data['DesOrdersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
					$data['DesOrdersOrganization']['order_id'] = $this->order_id;
					
					$data['DesOrdersOrganization']['luogo'] = '';
					$data['DesOrdersOrganization']['data'] = '0000-00-00';
					$data['DesOrdersOrganization']['orario'] = '00:00:00';
					$data['DesOrdersOrganization']['contatto_nominativo'] = '';
					$data['DesOrdersOrganization']['contatto_telefono'] = '';
					$data['DesOrdersOrganization']['contatto_mail'] = '';
					$data['DesOrdersOrganization']['nota'] = '';
										
					$DesOrdersOrganization->create();
					$DesOrdersOrganization->save($data);
				 } // end DES
				 
				 $this->Routings->fromOrderAddToArticlesOrderAdd($this->user, $this->order_id, null, $debug);
			}
			else {
				$msg = __('The order could not be saved. Please, try again.');	
				$this->Session->setFlash($msg);			
			}
		} // end if ($this->request->is('post') || $this->request->is('put'))
					
		/*
		 * prendo solo le consegne aperte
		 */
		$options =  array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'Delivery.stato_elaborazione' => 'OPEN',
									    'Delivery.sys'=>'N',
										'DATE(Delivery.data) >= CURDATE()');
		$options['fields'] = array('Delivery.id', 'luogoData');
		$options['order'] = array('Delivery.data ASC');
		$deliveries = $this->Order->Delivery->find('list', $options); 
		
		/*
		* non piu' perche' ho Delivery.sys = Y
		if(empty($deliveries)) {
			$this->Session->setFlash(__('OrderNotFoundDeliveries'));
			$this->myRedirect(array('action' => 'index'));
		}
		*/
		$this->set(compact('deliveries'));

		$isVisibleFrontEnd = ClassRegistry::init('Order')->enumOptions('isVisibleFrontEnd');
		$isVisibleBackOffice = ClassRegistry::init('Order')->enumOptions('isVisibleBackOffice');
		$hasTrasport = ClassRegistry::init('Order')->enumOptions('hasTrasport');
		$hasCostMore = ClassRegistry::init('Order')->enumOptions('hasCostMore');
		$hasCostLess = ClassRegistry::init('Order')->enumOptions('hasCostLess');
		$this->set(compact('isVisibleFrontEnd','isVisibleBackOffice','hasTrasport','hasCostMore','hasCostLess'));
		
		/*
		 * get elenco produttori filtrati
		*/
		if($this->user->organization['Organization']['hasDes']=='Y' && !empty($des_order_id) && !empty($supplier_organization_id)) {

			/* 
			 * per il DesOrder glielo passato da DesOrder::prepare_order_add 
			 */
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.stato' => 'Y',
										   'SuppliersOrganization.id' => $supplier_organization_id);
			$options['order'] = array('SuppliersOrganization.name');
			$options['recursive'] = -1;
			$ACLsuppliersOrganizationResults = $SuppliersOrganization->find('list', $options);

			$this->set('ACLsuppliersOrganization',$ACLsuppliersOrganizationResults);				
		}
		else {
			if($this->isSuperReferente()) {
				App::import('Model', 'SuppliersOrganization');
				$SuppliersOrganization = new SuppliersOrganization;
				
				$options = array();
				$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
											   'SuppliersOrganization.stato' => 'Y');
				$options['order'] = array('SuppliersOrganization.name');
				$options['recursive'] = -1;
				$ACLsuppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
				$this->set('ACLsuppliersOrganization',$ACLsuppliersOrganizationResults);
			}
			else 
				$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());		
		}
				
		$this->set('delivery_id',$delivery_id);  
		
		$qta_massima_um_options = array('KG' => 'Kg (prenderà in considerazione anche i Hg, Gr)', 'LT' => 'Lt (prenderà in considerazione anche i Hl, Ml)', 'PZ' => 'Pezzi');
		$this->set(compact('qta_massima_um_options'));
		
		/*
		 * se sono ManagerDelivery ho il link per cerare una nuova consenge, se no invio una mail
		 */
		$this->set('isManagerDelivery', $this->isManagerDelivery());
		
		/*
		 * dati DesOrder
		 */
		 if($this->user->organization['Organization']['hasDes']=='Y' && !empty($des_order_id)) {
		 
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

	public function admin_easy_add() {
	
		$debug=false;
		
		$msg = "";
		
		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			$data_inizio = $this->request->data['Order']['data_inizio'];
			$data_inizio_db = $this->request->data['Order']['data_inizio_db'];
			$data_fine = $this->request->data['Order']['data_fine'];
			$data_fine_db = $this->request->data['Order']['data_fine_db'];
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

		
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->order_id = $this->__add($this->user, $this->request->data, $debug);	
			
			if($this->order_id > 0) {				
				$options = [];
				$options['force_articlesorders_add_hidden'] = true;
				$this->Routings->fromOrderAddToArticlesOrderAdd($this->user, $this->order_id, $options, $debug);
			}
			else {
				$msg = __('The order could not be saved. Please, try again.');	
				$this->Session->setFlash($msg);			
			}
		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		/*
		 * prendo solo le consegne aperte
		 */
		$options =  array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'Delivery.stato_elaborazione' => 'OPEN',
									    'Delivery.sys'=>'N',
										'DATE(Delivery.data) >= CURDATE()');
		$options['fields'] = array('Delivery.id', 'luogoData');
		$options['order'] = array('Delivery.data ASC');
		$deliveries = $this->Order->Delivery->find('list', $options); 
		
		/*
		* non piu' perche' ho Delivery.sys = Y
		if(empty($deliveries)) {
			$this->Session->setFlash(__('OrderNotFoundDeliveries'));
			$this->myRedirect(array('action' => 'index'));
		}
		*/
		$this->set(compact('deliveries'));

		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.stato' => 'Y');
			$options['order'] = array('SuppliersOrganization.name');
			$options['recursive'] = -1;
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else 
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
				
		/*
		 * se sono ManagerDelivery ho il link per cerare una nuova consenge, se no invio una mail
		 */
		$this->set('isManagerDelivery', $this->isManagerDelivery());		
	}
	
	private function __add($user, $requestData, $debug) {
	
		$requestData['Order']['organization_id'] = $user->organization['Organization']['id'];
		if(!empty($requestData['Order']['des_order_id']))
			$des_order_id = $requestData['Order']['des_order_id'];
		else
			$requestData['Order']['des_order_id'] = 0;
		
		/*
		 * rimane allo stato CREATE-INCOMPLETE finche' non crea qualche associazione con gli articoli
		 */
		$requestData['Order']['state_code'] = 'CREATE-INCOMPLETE';

		if($requestData['Order']['qta_massima']==0) {
			$requestData['Order']['qta_massima_um']='';
			$requestData['Order']['send_mail_qta_massima'] = 'N';
		}
		else
			$requestData['Order']['send_mail_qta_massima'] = 'Y';
		
		if($requestData['Order']['importo_massimo']==0) 
			$requestData['Order']['send_mail_importo_massimo'] = 'N';
		else
			$requestData['Order']['send_mail_importo_massimo'] = 'Y';
		
		if($user->organization['Organization']['hasTrasport']=='N')
			$requestData['Order']['hasTrasport']='N';
		if($user->organization['Organization']['hasVisibility']=='N') {
			$requestData['Order']['isVisibleFrontEnd']='Y';
			$requestData['Order']['isVisibleBackOffice']='Y';
		}
		
		/*
		 * consegna da definire
		 */
		if($requestData['typeDelivery']=='to_defined') {
			App::import('Model', 'Delivery');
			$Delivery = new Delivery;
			
			$deliverySysResults = $Delivery->getDeliverySys($user);
			$requestData['Order']['delivery_id'] = $deliverySysResults ['Delivery']['id'];
		}
		
		$requestData['Order']['mail_open_send'] = $this->Order->setOrderMailOpenSend($user, $requestData);
		
		/*
		 *  bugs: il campo mail_open_testo a volta arriva con un <br /> 
		 */
		if($requestData['Order']['mail_open_testo']=='<br>' || $requestData['Order']['mail_open_testo']=='<br/>' || $requestData['Order']['mail_open_testo']=='<br />')
			$requestData['Order']['mail_open_testo'] = '';
		if($requestData['Order']['nota']=='<br>' || $requestData['Order']['nota']=='<br/>' || $requestData['Order']['nota']=='<br />')
			$requestData['Order']['nota'] = '';			
		
		$requestData['Order']['tesoriere_importo_pay'] = '0.00';
		$requestData['Order']['tesoriere_stato_pay'] = 'N';

		unset($requestData['typeDelivery']);
		unset($requestData['option']);
		
		if($debug) {
			echo '<br />oggi '.$data_oggi.' = '.$data_inizio_db;
			echo "<pre>";
			print_r($requestData);
			echo "</pre>";
		}

		$this->Order->set($requestData);
		if(!$this->Order->validates()) {
			$errors = $this->Order->validationErrors;
			$order_id = 0;
			if($debug) {
				echo "<pre>";
				print_r($errors);
				echo "</pre>";			
			}
		}
		else {
			$this->Order->create();
			if($this->Order->save($requestData)) 
				$order_id = $this->Order->getLastInsertId();
			else 
				$order_id = 0;
		}
		
		if($debug) echo '<br />__add() order_id '.$order_id;
		
		return $order_id;
	} 
			
	/*
	 * versione obsoleta (e di conseguenza anche OrderHomeHelper)
	 * 
	 * se $this->request->params['pass']['popup'] = Y 
	 * 		versione SIMPLE con le immagini (help on line)
	 */
	public function admin_home_disabled() {
		
		exit;
		
		$debug = false;
		
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.id' => $this->order_id);
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$this->set('results', $results);
		
		/*
		 * gestione colli (pezzi_confezione)
		*/
		$isToValidate = $this->Order->isOrderToValidate($this->user, $this->order_id);
		$this->set('isToValidate', $isToValidate);
		
		/*
		 *  Storeroom, cerco eventuali articoli nel carrello dell'utente Dispensa,
		*
		*  se Order.state_code == PROCESSED-POST-DELIVERY
		*  li copio dal carrello alla dispensa
		*/
		$isCartToStoreroom = false;
		if($this->user->organization['Organization']['hasStoreroom']=='Y' && $results['Order']['state_code']=='PROCESSED-POST-DELIVERY') {
			App::import('Model', 'Storeroom');
			$Storeroom = new Storeroom;
			$storeroomResults = $Storeroom->getCartsToStoreroom($this->user, $order_id);
			if(count($storeroomResults)>0) $isCartToStoreroom = true;
		}
		$this->set('isCartToStoreroom', $isCartToStoreroom);

		
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);		
		$orderActions = $this->ActionsOrder->getOrderActionsToMenu($this->user, $group_id, $results['Order']['id'], $debug);
		$this->set('orderActions', $orderActions);
	
		/*
		 * creo degli OrderActions di raggruppamento per controller (Orders, Carts, etc)
		*/
		$raggruppamentoOrderActions = $this->ActionsOrder->getRaggruppamentoOrderActions($orderActions, $debug);
		$this->set('raggruppamentoOrderActions', $raggruppamentoOrderActions);
		
		/*
		 * popup = Y quando e' richiamato dall'help on line
		 */
		if(!isset($this->request->params['pass']['popup']))
			$popup = 'N';
		else
			$popup = $this->request->params['pass']['popup'];
			
		if($popup=='Y') 
			$this->render('admin_home_simple');
		else
			$this->render('admin_home');
	}

	public function admin_home() {
	
		$debug = false;
	
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.id' => $this->order_id);
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$this->set('results', $results);

		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);	
		$orderActions = $this->ActionsOrder->getOrderActionsToMenu($this->user, $group_id, $results['Order']['id'], $debug);
		$this->set('orderActions', $orderActions);
	
		/*
		 * creo degli OrderActions di raggruppamento per controller (Orders, Carts, etc)
		*/
		$raggruppamentoOrderActions = array();
		/*
		 * bugs, per questi stati non raggruppo perche' ho 2 OrderController con conseguenziali
		 */
		if($results['Order']['state_code']!='PROCESSED-ON-DELIVERY' && $results['Order']['prod_gas_promotion_id']==0)
			$raggruppamentoOrderActions = $this->ActionsOrder->getRaggruppamentoOrderActions($orderActions, $debug);
		$this->set('raggruppamentoOrderActions', $raggruppamentoOrderActions);
		
		/*
		 * DES
		 */
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
								
				App::import('Model', 'DesOrder');
				$DesOrder = new DesOrder();
				
				$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
				/*
				echo "<pre>";
				print_r($desOrdersResults);
				echo "</pre>";	
				*/
				
				/*
				 * ctrl eventuali occorrenze di SummaryDesOrder
				*/
				App::import('Model', 'SummaryDesOrder');
				$SummaryDesOrder = new SummaryDesOrder;
				$summaryDesOrderResults = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id, $this->user->organization['Organization']['id']);
				
				$this->set(compact('desOrdersResults', 'summaryDesOrderResults'));			
			}
		} // DES
		$this->set(compact('des_order_id'));

		/*
		 * dati promozione del GAS (gli passo $this->user->organization['Organization']['id'])
		 */
		$supplier_id = 0;
		$prod_gas_promotion_id = $results['Order']['prod_gas_promotion_id']; 
		$this->set(compact('supplier_id', 'prod_gas_promotion_id'));	
		 
		if($prod_gas_promotion_id!=0) { 
			App::import('Model', 'ProdGasPromotion');
			$ProdGasPromotion = new ProdGasPromotion;
			 
			$promotionResults = $ProdGasPromotion->getProdGasPromotion($this->user, $supplier_id, $prod_gas_promotion_id, $this->user->organization['Organization']['id']);
			$this->set('promotionResults', $promotionResults);
		}
	}
	
	public function admin_edit() {
	
		$debug=false;
		

		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		/*
		 * setting fields
		
		if ($this->request->is('post') || $this->request->is('put')) {
			$data_inizio = $this->request->data['Order']['data_inizio'];
			$data_inizio_db = $this->request->data['Order']['data_inizio'];
			$data_fine = $this->request->data['Order']['data_fine'];
			$data_fine_db = $this->request->data['Order']['data_fine'];
			$qta_massima = $this->request->data['Order']['qta_massima_um'];
			$qta_massima_um = $this->request->data['Order']['qta_massima_um'];
			if($qta_massima==0)
				$qta_massima_um = '';
			$importo_massimo = $this->request->data['Order']['importo_massimo'];
			$hasTrasport = $this->request->data['Order']['hasTrasport'];
			$hasCostMore = $this->request->data['Order']['hasCostMore'];
			$hasCostLess = $this->request->data['Order']['hasCostLess'];
			$isVisibleFrontEnd = $this->request->data['Order']['isVisibleFrontEnd'];
			$isVisibleBackOffice = $this->request->data['Order']['isVisibleBackOffice'];
		}
		else {
			$data_inizio = $this->request->data['Order']['data_inizio'];
			$data_inizio_db = $this->request->data['Order']['data_inizio'];
			$data_fine = $this->request->data['Order']['data_fine'];
			$data_fine_db = $this->request->data['Order']['data_fine'];
			$qta_massima = $this->request->data['Order']['qta_massima_um'];
			$qta_massima_um = $this->request->data['Order']['qta_massima_um'];
			if($qta_massima==0)
				$qta_massima_um = '';
			$importo_massimo = $this->request->data['Order']['importo_massimo'];			
			$hasTrasport = $this->request->data['Order']['hasTrasport'];
			$hasCostMore = $this->request->data['Order']['hasCostMore'];
			$hasCostLess = $this->request->data['Order']['hasCostLess'];
			$isVisibleFrontEnd = $this->request->data['Order']['isVisibleFrontEnd'];
			$isVisibleBackOffice = $this->request->data['Order']['isVisibleBackOffice'];
		}
	
		$this->set('data_inizio', $data_inizio);
		$this->set('data_inizio_db', $data_inizio_db);
		$this->set('data_fine', $data_fine);
		$this->set('data_fine_db', $data_fine_db);
		$this->set('qta_massima', $qta_massima);
		$this->set('qta_massima_um', $qta_massima_um);
		$this->set('importo_massimo', $importo_massimo);
		$this->set('hasTrasportDefault', $hasTrasport);
		$this->set('hasCostMoreDefault', $hasCostMore);
		$this->set('hasCostLessDefault', $hasCostLess);
		$this->set('isVisibleFrontEndDefault', $isVisibleFrontEnd);
		$this->set('isVisibleBackOfficeDefault', $isVisibleBackOffice);
		*/

		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
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
		} // DES
		$this->set(compact('des_order_id'));
		
		if ($this->request->is('post') || $this->request->is('put')) {
				
			/*
			 * ordine prima del salvataggio, ctrl successivi
			 */
			$options = array();
			$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
											'Order.id' => $this->order_id);
			$options['recursive'] = -1;
			$OrderOldresults = $this->Order->find('first', $options);
			if($debug) {
				echo "<pre>ordine precedente ";
				print_r($OrderOldresults);
				echo "</pre>";				
			}
			
			$this->request->data['Order']['data_inizio'] = $this->request->data['Order']['data_inizio_db'];
			$this->request->data['Order']['data_fine'] = $this->request->data['Order']['data_fine_db'];
			$this->request->data['Order']['data_fine_validation'] = $this->request->data['Order']['data_fine_validation_db'];
			
			if($this->request->data['Order']['qta_massima']==0) {
				$this->request->data['Order']['qta_massima_um']='';
			}
			
			/*
			 *  se ho modificato Order.qta_massima => send_mail_qta_massima = Y
			 *  se ho modificato Order.importo_massimo => send_mail_importo_massimo = Y
			 */
			if($OrderOldresults['Order']['qta_massima']!=$this->request->data['Order']['qta_massima'])
				$this->request->data['Order']['send_mail_qta_massima'] = 'Y';
			else
				$this->request->data['Order']['send_mail_qta_massima'] = $OrderOldresults['Order']['send_mail_qta_massima'];
			
			if($OrderOldresults['Order']['importo_massimo']!=$this->request->data['Order']['importo_massimo'])
				$this->request->data['Order']['send_mail_importo_massimo'] = 'Y';
			else
				$this->request->data['Order']['send_mail_importo_massimo'] = $OrderOldresults['Order']['send_mail_importo_massimo'];
			
			if($this->user->organization['Organization']['hasTrasport']=='N') {
				$this->request->data['Order']['hasTrasport']='N';
				$this->request->data['Order']['trasport_type']= null;
				$this->request->data['Order']['trasport']= 0;
			}
			if($this->user->organization['Organization']['hasVisibility']=='N') {
				$this->request->data['Order']['isVisibleFrontEnd']='Y';
				$this->request->data['Order']['isVisibleBackOffice']='Y';
			}
			if($this->request['Order']['hasTrasport']=='N') {
				$this->request->data['Order']['trasport_type']= null;
				$this->request->data['Order']['trasport']= 0;
			}
			if($this->request['Order']['hasCostMore']=='N') {
				$this->request->data['Order']['cost_more_type']= null;
				$this->request->data['Order']['cost_more']= 0;
			}
			if($this->request['Order']['hasCostLess']=='N') {
				$this->request->data['Order']['cost_less_type']= null;
				$this->request->data['Order']['cost_less']= 0;
			}
				
			$this->request->data['Order']['mail_open_send'] = $this->Order->setOrderMailOpenSend($this->user, $this->request->data);
			
			/*
			 *  bugs: il campo mail_open_testo a volta arriva con un <br />
			*/
			if($this->request->data['Order']['mail_open_testo']=='<br>' || $this->request->data['Order']['mail_open_testo']=='<br/>' || $this->request->data['Order']['mail_open_testo']=='<br />')
				$this->request->data['Order']['mail_open_testo'] = '';
			if($this->request->data['Order']['nota']=='<br>' || $this->request->data['Order']['nota']=='<br/>' || $this->request->data['Order']['nota']=='<br />')
				$this->request->data['Order']['nota'] = '';
						
			if($debug) {
				echo '<br />oggi '.$data_oggi.' = '.$data_inizio_db;
				echo '<br />mail_open_send '.$this->request->data['Order']['mail_open_send'];
			}

			/*
			 * consegna da definire
			*/
			if($this->request->data['typeDelivery']=='to_defined') {			
				$deliverySysResults = $Delivery->getDeliverySys($this->user);
				$this->request->data['Order']['delivery_id'] = $deliverySysResults ['Delivery']['id'];
			}
			
			$this->Order->create();
			if ($this->Order->save($this->request->data)) {
				$msg .= __('The order has been saved').'<br/>';
				 
				/*
				 * elimina il trasporto da SummaryOrders e Orders
				*/
				if($this->request['Order']['hasTrasport']=='N') {
				
					if($debug) echo "<br />Order.hasTrasport == N, cancello il trasporto";
					
					App::import('Model', 'SummaryOrderTrasport');
					$SummaryOrderTrasport = new SummaryOrderTrasport;
				
					$SummaryOrderTrasport->delete_trasport_to_order($this->user, $this->order_id, $debug);
				}

				/*
				 * elimina il costo aggiuntivo da SummaryOrders e Orders
				*/
				if($this->request['Order']['hasCostMore']=='N') {
				
					if($debug) echo "<br />Order.hasCostMore == N, cancello il costo aggiuntivo";
						
					App::import('Model', 'SummaryOrderCostMore');
					$SummaryOrderCostMore = new SummaryOrderCostMore;
				
					$SummaryOrderCostMore->delete_cost_more_to_order($this->user, $this->order_id, $debug);
				}
				
				/*
				 * elimina lo sconto da SummaryOrders e Orders
				*/
				if($this->request['Order']['hasCostLess']=='N') {
				
					if($debug) echo "<br />Order.hasCostLess == N, cancello lo sconto";
						
					App::import('Model', 'SummaryOrderCostLess');
					$SummaryOrderCostLess = new SummaryOrderCostLess;
				
					$SummaryOrderCostLess->delete_cost_less_to_order($this->user, $this->order_id, $debug);
				}
				
				/*
				 * gestType 'AGGREGATE', 'SPLIT'
				 */
				$this->Order->gestTypeGest($this->user, $this->request->data, $OrderOldresults, $debug);
				
				/*
				 * aggiorno lo stato dell'ordine
				* 		OPEN-NEXT o OPEN o ...
				* */
				$utilsCrons = new UtilsCrons(new View(null));
				if($debug) echo "<pre>";
				$utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $this->order_id);
				if($debug) echo "</pre>";
				
				/*
				 * ordine prima del salvataggio
				* se riapro un ordine cancello a dati importo_forzato, summary_orders, summary_order_trasport, Order.trasport ...
				* 
				* 	si puo' fare solo Order.state_code = PROCESSED-BEFORE-DELIVERY => tutti i dati vuoti!
				*  in PROCESSED-POST-DELIVERY se setto Order.data_fine > Delivery.data non mi viene permesso
				*/
				$options = array();
				$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
												'Order.id' => $this->order_id);
				$options['recursive'] = -1;
				$results = $this->Order->find('first', $options);
				if($debug) {
					echo "<pre>ordine dopo la modifica ";
					print_r($results);
					echo "</pre>";
				}
				
				if($OrderOldresults['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY' && 
					($results['Order']['state_code']=='OPEN-NEXT' || $results['Order']['state_code']=='OPEN' || $results['Order']['state_code']=='RI-OPEN-VALIDATE')) {
					if($debug) echo "<br />riapro l'ordine ";
					$this->Order->riapriOrdine($this->user, $this->order_id, $debug);
				}
				
				$this->Session->setFlash($msg);
				
				/*
				 * se e' cambiata la consegna vado sulla pagina per inviare la mail agli utenti
				 */
				if($OrderOldresults['Order']['delivery_id']!=$this->request->data['Order']['delivery_id']) {
					
					/*
					 * aggiorno con il nuovo delivery_id le tabelle
					 *
					 * k_summary_orders 					 
					 * k_request_payments_orders
					 */
					$this->Order->updateTablesToChangeDeliverId($this->user, $this->order_id, $this->request->data['Order']['delivery_id'], $debug);
					
					App::import('Model', 'User');
					$User = new User;
					
					$conditions = array();
					$conditions = array('ArticlesOrder.order_id' => $this->order_id);
					$results = $User->getUserWithCartByOrder($this->user ,$conditions);
					
					if(count($results)>0)
						$redirect = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=delivery_change&delivery_id_old='.$OrderOldresults['Order']['delivery_id'];
					else
						$redirect = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id;
				}
				else
					$redirect = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id;
					
				if(!$debug) 
					$this->myRedirect($redirect);
				else  {
					echo "<pre> ";
					print_r($redirect);
					echo "</pre>";					
				}
						
			} else 
				$this->Session->setFlash(__('The order could not be saved. Please, try again.'));
		} // else if ($this->request->is('post') || $this->request->is('put'))

		$options = array();
		$options['conditions'] = array('Order.id' => $this->order_id, 
									   'Order.organization_id' => $this->user->organization['Organization']['id']);
		$options['recursive'] = 1;
		$this->request->data = $this->Order->find('first', $options);
		if (empty($this->request->data)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		if($this->request->data['Delivery']['isVisibleBackOffice']=='N') {
			$msgDeliveryNotValid = "La consegna '".$this->request->data['Delivery']['luogoData']."' associata all'ordine non &egrave; disponibile";
			$this->set('msgDeliveryNotValid' , $msgDeliveryNotValid);
		}
		
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'DATE(Delivery.data) >= CURDATE()',
										'Delivery.sys'=>'N',
										'Delivery.stato_elaborazione' => 'OPEN');
		$options['fields'] = array('Delivery.id', 'luogoData');
		$options['order'] = array('Delivery.data ASC');
		$deliveries = $this->Order->Delivery->find('list', $options);
		/*
		 * non piu' perche' ho Delivery.sys = Y
		if(empty($deliveries)) {
			$this->Session->setFlash(__('OrderNotFoundDeliveries'));
			$this->myRedirect(array('action' => 'index'));
		}*/

		/* 
		 * ctrl che tra l'elenco delle consegne ci sia la consegna gia' associata all'ordine 
		 * se non c'e' (per esempio consegna chiusa e qui prendo solo DATE(Delivery.data) >= CURDATE() ) 
		 * l'aggiungo 
		 * */
		if($this->request->data['Delivery']['sys']=='N') {
			if(!array_key_exists($this->request->data['Delivery']['id'], $deliveries))
				$deliveries[$this->request->data['Delivery']['id']] = $this->request->data['Delivery']['luogoData'];
		}
		
		$this->set(compact('deliveries'));
			
		$isVisibleFrontEnd = ClassRegistry::init('Order')->enumOptions('isVisibleFrontEnd');
		$isVisibleBackOffice = ClassRegistry::init('Order')->enumOptions('isVisibleBackOffice');
		$hasTrasport = ClassRegistry::init('Order')->enumOptions('hasTrasport');
		$hasCostMore = ClassRegistry::init('Order')->enumOptions('hasCostMore');		
		$hasCostLess = ClassRegistry::init('Order')->enumOptions('hasCostLess');
		$type_draw = ClassRegistry::init('Order')->enumOptions('type_draw');

		$this->set(compact('isVisibleFrontEnd','isVisibleBackOffice','hasTrasport','type_draw','hasCostMore','hasCostLess'));
		
		/*
		 * estraggo i referenti
		*/
		$this->__getReferenti($this->user, $this->request->data['Order']['supplier_organization_id']);
		
		if($isDesOrder) 
			$this->__getDesReferenti($this->user, $this->des_order_id);
		
		$qta_massima_um_options = array('KG' => 'Kg (prenderà in considerazione anche i Hg, Gr)', 'LT' => 'Lt (prenderà in considerazione anche i Hl, Ml)', 'PZ' => 'Pezzi');
		$this->set(compact('qta_massima_um_options'));

		/*
		 * se sono ManagerDelivery ho il link per cerare una nuova consenge, se no invio una mail
		*/
		$this->set('isManagerDelivery', $this->isManagerDelivery());
		
		/*
		 * ctrl se ci sono consegne scadute, per far vedere il tasto "associa a consegna scaduta"
		 */		
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.isVisibleBackOffice' => 'Y',
									   'Delivery.stato_elaborazione' => 'OPEN',
									   'Delivery.sys' => 'N', 
									   'DATE(Delivery.data) < CURDATE()');
		$options['order'] = array('Delivery.data ASC');
		$options['recursive'] = -1;
		$tot_delivery_old = $Delivery->find('count', $options); 
		$this->set('tot_delivery_old', $tot_delivery_old);	
		
	}

	public function admin_view() {
		$this->__view();
	}
	
	/*
	 * ho l'ordine in solo lettura e senza 
	 * 		la home dell'ordine 
	 * 		il menu dell'ordine
	 * perche' non sono referente
	 */
	public function admin_view_public() {
	
		$this->__view();
	}
	
	private function __view() {
		$debug=false;
		
		$msg = '';
		$this->Order->id = $this->order_id;
		if (!$this->Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$options = array();
		$options['conditions'] = array('Order.id' => $this->order_id,
				'Order.organization_id' => $this->user->organization['Organization']['id']);
		$options['recursive'] = 1;
		$this->request->data = $this->Order->find('first', $options);
		if (empty($this->request->data)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$isVisibleFrontEnd = ClassRegistry::init('Order')->enumOptions('isVisibleFrontEnd');
		$isVisibleBackOffice = ClassRegistry::init('Order')->enumOptions('isVisibleBackOffice');
		$hasTrasport = ClassRegistry::init('Order')->enumOptions('hasTrasport');
		$hasCostMore = ClassRegistry::init('Order')->enumOptions('hasCostMore');
		$hasCostLess = ClassRegistry::init('Order')->enumOptions('hasCostLess');
		$type_draw = ClassRegistry::init('Order')->enumOptions('type_draw');
		
		$this->set(compact('isVisibleFrontEnd','isVisibleBackOffice','hasTrasport','type_draw','hasCostMore','hasCostLess'));
		
		/*
		 * estraggo i referenti
		*/
		$this->__getReferenti($this->user, $this->request->data['Order']['supplier_organization_id']);
		
		$this->set('userGroups', $this->userGroups);		
	}
	
	/*
	 * se ci sono degli articoli ordinati che non hanno completato il collo (pezzi_confezione) 
	 * permetto di riaprire l'ordine
	 * 
	 *  call Carts::admin_validationCarts()
	 */
	public function admin_edit_validation_cart() {

		$debug=false;
		
		$msg = '';
		$this->Order->id = $this->order_id;
		if (!$this->Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$options = array();
		$options['conditions'] = array('Order.id' => $this->order_id,
									   'Order.organization_id' => $this->user->organization['Organization']['id']);
		$options['recursive'] = 1;
		$results = $this->Order->find('first', $options);
	
		/*
		 * qui arrivo gia' in if ($this->request->is('post') || $this->request->is('put'))
		 */
		if (!empty($this->request->data['Order']['data_fine_validation_db'])) {
		
			$continue = true;
			$msg = '';
						
			$data_fine_validation = $this->request->data['Order']['data_fine_validation'];
			$data_fine_validation_db = $this->request->data['Order']['data_fine_validation_db'];

			/*
			 * aggiorno stato ORDER
			*/			
	        App::import('Model', 'OrderLifeCycle');
	        $OrderLifeCycle = new OrderLifeCycle();
	        
	        $options = array();
	        $options['data_fine_validation'] = $data_fine_validation_db;
	        $esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'RI-OPEN-VALIDATE', $options, $debug);
	        if($esito['CODE']!=200) {
	        	$msg = $esito['MSG'];
	        	$continue = false;
	        } 
			
			if($continue) {
				if($debug) echo "<br />riapro l'ordine ";
				$this->Order->riapriOrdine($this->user, $this->order_id, $debug);
			}
						
			/*
			 * invio mail
			 */
			if($continue) {
				if($this->request->data['Order']['invio_mail']=='Y') {
					
					$mail_open_testo = $this->request->data['Order']['mail_open_testo'];
					
					App::import('Model', 'Mail');
					$Mail = new Mail;
					
					$Email = $Mail->getMailSystem($this->user);
					
					/*
					 * estraggo gli utenti ai quali inviare la mail
					*/
					App::import('Model', 'User');
					$User = new User;
					
					$options = array();
					$options['conditions'] = array('User.organization_id'=>(int)$this->user->organization['Organization']['id'],
													'User.block'=> 0);
					$options['fields'] = array('id','name','email');
					$options['order'] = Configure::read('orderUser');
					$options['recursive'] = -1;
					$users = $User->find('all', $options);
					
					if(!empty($users)) {
		
						$subject_mail = 'Riapertura ordine per completare i colli';
						$Email->subject($subject_mail);
						if(!empty($this->user->organization['Organization']['www']))
							$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))));
						else
							$Email->viewVars(array('body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))));
							
						$msg .= '<br />Inviata la mail a<br />';
						/*
					 	* ciclo UTENTI
					 	*/
						foreach($users as $numResult => $user) {
			
							$mail = $user['User']['email'];
							$name = $user['User']['name'];
								
							if(!empty($mail)) {
								$mail_open_testo = str_replace('gg/mm/yyy', $data_fine_validation, $mail_open_testo);
								$body_mail = $mail_open_testo;
	
								$Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
								$Email->to($mail);
									
								$Mail->send($Email, $mail, $body_mail, $debug);
							}
							else
								$msg .= $name.' senza indirizzo mail!<br />';
						} // end foreach($results as $numResult => $result)
			
					} // end if(!empty($users))
					
				} // if($this->request->data['Order']['invio_mail']=='Y')
			} // $continue

			if($continue) {
				if($this->request->data['Order']['invio_mail']=='Y') 
					$msg = __('The OrderDataFineValidation has been saved and Send Mail');
				else
					$msg = __('The OrderDataFineValidation has been saved');
				
				$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);	
			}
			
			$this->Session->setFlash($msg);
		}

		/*
		 * prima volta
		 */
		if($results['Order']['data_fine_validation']==Configure::read('DB.field.date.empty')) {
			$data_fine_validation = '';
			$data_fine_validation_db = '';	
			$invio_mail_default = 'Y';
		}
		else {
			$data_fine_validation = $results['Order']['data_fine_validation'];
			$data_fine_validation_db = $results['Order']['data_fine_validation'];
			$invio_mail_default = 'N';
		}				

		$this->set('data_fine_validation', $data_fine_validation);
		$this->set('data_fine_validation_db', $data_fine_validation_db);
		
		$invio_mail = array('Y' => 'Si', 'N' => 'No');
		$this->set(compact('invio_mail'));
		$this->set(compact('invio_mail_default'));
		
		$this->request->data = $results;
		
		/*
		 * testo della mail
		 * * estraggo gli acquisti da validate (ArticlesOrder.pezzi_confezione > 1)
		 */
		App::import('Model', 'Cart');
		$Cart = new Cart;
		$cartToValidateResults = $Cart->getCartToValidate($this->user, $this->delivery_id, $this->order_id);
		
		$this->set('body_header', sprintf(Configure::read('Mail.body_header'), 'Mario Rossi'));
		$this->set('body_footer_no_reply', sprintf(Configure::read('Mail.body_footer_no_reply'), $this->traslateWww($this->user->organization['Organization']['www'])));
		
		$testo_mail = "";
		$testo_mail .= '<ul>';
		foreach($cartToValidateResults as $numResult => $cartToValidateResult) {
		
			if($debug) echo '<br />Articolo acquistato '.$cartToValidateResult['ArticlesOrder']['qta_cart'].' volte';
			if($debug) echo '<br />Collo da '.$cartToValidateResult['ArticlesOrder']['pezzi_confezione'];
			
			/*
			 * colli_completi / differenza_da_ordinare
			*/
			$colli_completi = intval($cartToValidateResult['ArticlesOrder']['qta_cart'] / $cartToValidateResult['ArticlesOrder']['pezzi_confezione']);
			if($debug) echo '<br />Colli completati '.$colli_completi;
			
			if($colli_completi>0)
				$differenza_da_ordinare = (($cartToValidateResult['ArticlesOrder']['pezzi_confezione'] * ($colli_completi +1)) - $cartToValidateResult['ArticlesOrder']['qta_cart']);
			else {
				$differenza_da_ordinare = ($cartToValidateResult['ArticlesOrder']['pezzi_confezione'] - $cartToValidateResult['ArticlesOrder']['qta_cart']);
				$colli_completi = '-';
			}
			
			if($debug) echo '<br />Differenza da ordinare '.$differenza_da_ordinare;
			
			$testo_mail .= '<li>'.$cartToValidateResult['ArticlesOrder']['name'].' prezzo '.$cartToValidateResult['ArticlesOrder']['prezzo_e'].' ancora da ordinarne '.$differenza_da_ordinare.' per completare il collo da '.$cartToValidateResult['ArticlesOrder']['pezzi_confezione']."</li>";
		} // foreach($newResults['ArticlesOrder'] as $numResult => $cartToValidateResult) 	
		$testo_mail .= '</ul>';
		$testo_mail .= "Vai su http://".Configure::read('SOC.site')." e completa l'ordine ";
		
		$testo_mail = sprintf(Configure::read('Mail.body_carts_validation'), $results['Delivery']['luogoData'], $results['SuppliersOrganization']['name'], 'gg/mm/yyy', $testo_mail);
		
		$this->set('testo_mail',$testo_mail);
	}
	
	/*
	*  se e' cambiata la consegna avviso gli eventuali acquirenti
	*/
	public function admin_delivery_change() {
	
		$debug = false;
		if($debug) {
			echo "<pre>";
			print_r($this->request->data);
			echo "</pre>";
		}
			
		$msg = '';
		$this->Order->id = $this->order_id;
		if (!$this->Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$options = array();
		$options['conditions'] = array('Order.id' => $this->order_id,
										'Order.organization_id' => $this->user->organization['Organization']['id']);
		$options['recursive'] = 1;
		$results = $this->Order->find('first', $options);
		
		/*
		 * consegna precedente
		 */
		 $delivery_id_old = '';
		if(isset($this->request->pass['delivery_id_old']))	
			$delivery_id_old = $this->request->pass['delivery_id_old'];
		else
		if(isset($this->request->data['Order']['delivery_id_old']))	
			$delivery_id_old = $this->request->data['Order']['delivery_id_old'];
		
		if($debug) echo '<br />delivery_id_old '.$delivery_id_old;
		$this->set(compact('delivery_id_old'));
		
		if(!empty($delivery_id_old)) {
			App::import('Model', 'Delivery');
			$Delivery = new Delivery;
					
			$options = array();
			$options['conditions'] = array('Delivery.organization_id' => $this->user->organization['Organization']['id'],
										   'Delivery.id' => $delivery_id_old);
			$options['recursive'] = -1;
			$options['fields'] = array('sys', 'data', 'luogo', 'luogoData');
			
			$OrderOldresults = $Delivery->find('first', $options);
			$this->set(compact('OrderOldresults'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			/*
			 * invio mail
			*/
			$msg = '';

			$mail_open_testo = $this->request->data['Order']['mail_open_testo'];
	
			App::import('Model', 'Mail');
			$Mail = new Mail;

			$Email = $Mail->getMailSystem($this->user);

			/*
			 * estraggo gli utenti ai quali inviare la mail
			*/
			App::import('Model', 'User');
			$User = new User;

			$conditions = array();
			$conditions = array('ArticlesOrder.order_id' => $this->order_id);
			$users = $User->getUserWithCartByOrder($this->user ,$conditions);
			
			if(!empty($users)) {

				$subject_mail = "L'ordine di ".$results['SuppliersOrganization']['name']." ha cambiato consegna";
				$Email->subject($subject_mail);
				if(!empty($this->user->organization['Organization']['www']))
					$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))));
				else
					$Email->viewVars(array('body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))));

				$msg .= '<br />Inviata la mail a<br />';
				/*
				 * ciclo UTENTI
				*/
				foreach($users as $numResult => $user) {

					$mail = $user['User']['email'];
					$name = $user['User']['name'];
						
					if($debug) echo '<br />Mail a '.$mail;
					
					if(!empty($mail)) {
						$body_mail = $mail_open_testo;

						$Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
						$Email->to($mail);

						$Mail->send($Email, $mail, $body_mail, $debug);
					}
					else
						$msg .= $name.' senza indirizzo mail!<br />';
				} // end foreach($results as $numResult => $result)
					
				$this->Session->setFlash(__('The OrderDeliveryChange has been saved and Send Mail'));
			} // end if(!empty($users))
	
			if(!$debug) $this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
		}  // end if ($this->request->is('post') || $this->request->is('put')) 
	

		$this->request->data = $results;
	
		/*
		 * testo della mail
		*/
		$this->set('body_header', sprintf(Configure::read('Mail.body_header'), 'Mario Rossi'));
		$this->set('body_footer_no_reply', sprintf(Configure::read('Mail.body_footer_no_reply'), $this->traslateWww($this->user->organization['Organization']['www'])));
	
		$testo_mail = "L'ordine di ".$results['SuppliersOrganization']['name']." ha cambiato consegna:";
		$testo_mail .= "<br />";
		$testo_mail .= "non sar&agrave; pi&ugrave; ";
		if($OrderOldresults['Delivery']['sys']=='Y')
			$testo_mail .= '<b>'.$OrderOldresults['Delivery']['luogo'].'</b>';
		else
			$testo_mail .= ' presso '.$OrderOldresults['Delivery']['luogoData'];		
		
		$testo_mail .= "<br /><br />";
		$testo_mail .= "La <b>nuova consegna</b> sar&agrave; ";
		if($results['Delivery']['sys']=='Y')
			$testo_mail .= '<b>'.$results['Delivery']['luogo'].'</b>';
		else {
			$testo_mail .= ' presso '.$results['Delivery']['luogoData'];
			$testo_mail .= ', dalle ore '.substr($results['Delivery']['orario_da'], 0, 5).' alle '.substr($results['Delivery']['orario_a'], 0, 5);	
		}

		$testo_mail .= "<br /><br />";
		$testo_mail .= "Gli <b>acquisti</b> che hai gi&agrave; effettuato li potrai ritirare durante la nuova consegna.";
		$testo_mail .= "<br /><br />";
		$testo_mail .= "Se hai problemi contatta i referenti";
		
		
		/*
		 * estraggo i referenti
		*/
		$suppliersOrganizationsReferent = $this->__getReferenti($this->user, $this->request->data['Order']['supplier_organization_id']);
		if(!empty($suppliersOrganizationsReferent)) {
			foreach ($suppliersOrganizationsReferent as $referent) {

				$testo_mail .= "<br />";
				$testo_mail .= ' - '.$referent['User']['name'].' ';
		
				if(!empty($referent['User']['email']))
					$testo_mail .= $referent['User']['email'].' ';
				
				if(!empty($referent['Profile']['phone']))  $testo_mail .= $referent['Profile']['phone'].' ';
				if(!empty($referent['Profile']['phone2'])) $testo_mail .= $referent['Profile']['phone2'];
			}
		} // end if(!empty($suppliersOrganizationsReferent))
		
		$this->set('testo_mail',$testo_mail);
	}
	
	/*
 	* orders_Trigger
	*   orders
	*   summary_orders
	*   articles_orders
	*   carts
	*/
	public function admin_delete() {

		$debug = false;
		if($debug) {
			echo "<pre>";
			print_r($this->request->data);
			echo "</pre>";
		}
	
		$this->Order->id = $this->order_id;
		if (!$this->Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.id' => $this->order_id);
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		$this->set('results', $results);
		
		/*
		 * desOrder
		 */
		$isTitolareDesSupplier = false; 
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
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
                        
		        $isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);
			
		} // DES
		$this->set(compact('isTitolareDesSupplier'));		
		$this->set(compact('des_order_id'));
	
		if ($this->request->is('post') || $this->request->is('put')) {

				/*
				 * copia di backup di Order / ArticlesOrder / Cart
				 */
				App::import('Model', 'BackupOrder');
				$BackupOrder = new BackupOrder();				
				$BackupOrder->copyData($this->user, $this->order_id, $debug);
				
				
				$msg = '';

				/*
				 * invio mail
				*/				
				if($this->request->data['send_mail']=='Y') {
			
					$mail_open_testo = $this->request->data['Order']['mail_open_testo'];
	
					App::import('Model', 'Mail');
					$Mail = new Mail;

					$Email = $Mail->getMailSystem($this->user);

					/*
			 		* estraggo gli utenti ai quali inviare la mail
					*/
					App::import('Model', 'User');
					$User = new User;

					$conditions = array();
					$conditions = array('ArticlesOrder.order_id' => $this->order_id);
					$users = $User->getUserWithCartByOrder($this->user ,$conditions);
			
					if(!empty($users)) {

						$subject_mail = "L'ordine di ".$results['SuppliersOrganization']['name']." cancellato";
						$Email->subject($subject_mail);
						if(!empty($this->user->organization['Organization']['www']))
							$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))));
						else
							$Email->viewVars(array('body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))));

						$msg .= '<br />Inviata la mail a<br />';
						/*
					 	* ciclo UTENTI
						*/
						foreach($users as $numResult => $user) {

							$mail = $user['User']['email'];
							$name = $user['User']['name'];
							
							if($debug) echo '<br />Mail a '.$mail;
							
							if(!empty($mail)) {
								$body_mail = $mail_open_testo;
	
								$Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
								$Email->to($mail);
	
								$Mail->send($Email, $mail, $body_mail, $debug);

								$msg .= $name.'<br />';
							}
							else
								$msg .= $name.' senza indirizzo mail!<br />';
						} // end foreach($results as $numResult => $result)
						
					} // end if(!empty($users))
	
				} // if($this->request->data['send_mail']=='Y')
			
				if ($this->Order->delete()) {
                                    

                                        /*
                                         * D.E.S.
                                         * se titolare cancello DesOrder, trigger cancellera' DesOrderOrganization => Order
                                         */
                                        if($this->user->organization['Organization']['hasDes']=='Y') {
                                            if($isTitolareDesSupplier) {
                                            	if($debug) echo "<br />OrganizationHasDes == Y - isTitolareDesSupplier - des_order_id ".$des_order_id; 
                                            	
                                                $DesOrder->id = $des_order_id;
                                                $DesOrder->delete();
                                            }
                                            else {
                                            	if($debug) echo "<br />OrganizationHasDes == Y - !isTitolareDesSupplier - des_order_id ".$des_order_id;

                                                /*
                                                 * tolto trigger che da Order cancellava DesOrderOrganization perche' andava in conflitto
                                                 */ 
                                                try {
                                                    $sql = "DELETE FROM ".Configure::read('DB.prefix')."des_orders_organizations
                                                            WHERE
                                                                organization_id = ".(int)$this->user->organization['Organization']['id']."
                                                                AND order_id = ".(int)$this->order_id;
                                                    if($debug) echo '<br />'.$sql;
                                                    $resultDelete = $this->Order->query($sql);
                                                }
                                                catch (Exception $e) {
                                                    CakeLog::write('error',$sql);
                                                }                                                                
                                            }
                                        } //  end if($this->user->organization['Organization']['hasDes']=='Y') 


					$message = __('Delete Order');
					$message .= $msg;
					$this->Session->setFlash($message);
				}
				else
					$this->Session->setFlash(__('Order was not deleted'));
				
			setcookie('order_id', '', time() - 42000, Configure::read('App.server'));
			$this->Session->delete('order_id');
			
			$this->myRedirect(array('action' => 'index'));
		} // end POST
			
		App::import('Model', 'ArticlesOrder');
		/*
		 * articoli acquistati
		 * 		ho bindModel di Cart
		*/
		$ArticlesOrder = new ArticlesOrder;
		
		$ArticlesOrder->unbindModel(array('belongsTo' => array('Order')));
		$options = array();
		$options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
									   'ArticlesOrder.order_id' => $this->order_id,
									   'Article.stato' => 'Y',
									   'Cart.order_id' => $this->order_id
		);
		$totCart = $ArticlesOrder->find('count', $options);
		$this->set(compact('totCart'));
		
		if($totCart > 0) {
			/*
			 * testo della mail
			*/
			$this->set('body_header', sprintf(Configure::read('Mail.body_header'), 'Mario Rossi'));
			$this->set('body_footer_no_reply', sprintf(Configure::read('Mail.body_footer_no_reply'), $this->traslateWww($this->user->organization['Organization']['www'])));
	
			$testo_mail = "L'ordine di \"".$results['SuppliersOrganization']['name']."\" della consegna ";
			if($results['Delivery']['sys']=='Y')
				$testo_mail .= '<b>'.$results['Delivery']['luogo'].'</b>';
			else
				$testo_mail .= ' presso '.$results['Delivery']['luogoData'];		
		
			$testo_mail .= " &egrave; stato <b>cancellato</b> dal referente.";

			$testo_mail .= "<br /><br />";
			$testo_mail .= "Anche gli <b>acquisti</b> che avevi effettuato sono stati cancellati.";
			$testo_mail .= "<br /><br />";
			$testo_mail .= "Se hai necessit&agrave;, puoi contattare i referenti";
		
			
			/*
			 * estraggo i referenti
			*/
			$suppliersOrganizationsReferent = $this->__getReferenti($this->user, $results['Order']['supplier_organization_id']);
			if(!empty($suppliersOrganizationsReferent)) {
				foreach ($suppliersOrganizationsReferent as $referent) {
	
					$testo_mail .= "<br />";
					$testo_mail .= ' - '.$referent['User']['name'].' ';
		
					if(!empty($referent['User']['email']))
						$testo_mail .= $referent['User']['email'].' ';
					
					if(!empty($referent['Profile']['phone']))  $testo_mail .= $referent['Profile']['phone'].' ';
					if(!empty($referent['Profile']['phone2'])) $testo_mail .= $referent['Profile']['phone2'];
				}
			} // end if(!empty($suppliersOrganizationsReferent))
		
			$this->set('testo_mail',$testo_mail);		
		}
		
		/*
		 * articoli associati all'ordine
		 * 		faccio unbindModel di Cart
		 */
		$ArticlesOrder = new ArticlesOrder;
		$ArticlesOrder->unbindModel(array('belongsTo' => array('Cart', 'Order')));
		$options = array();
		$options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
										'ArticlesOrder.order_id' => $this->order_id,
										'Article.stato' => 'Y'
									);
		$totArticlesOrder = $ArticlesOrder->find('count', $options);
		$this->set(compact('totArticlesOrder'));
	}
	
	/*
	 * se se l'order.state_code INCOMING-ORDER / PROCESSED-ON-DELIVERY
	 * se i GAS che non gestiscono il cassiere
	 * devono cmq associato ad una consegna valida
	 */
	public function admin_close() {

		$debug = false;
		if($debug) {
			echo "<pre>";
			print_r($this->request->data);
			echo "</pre>";
		}
	
		$this->Order->id = $this->order_id;
		if (!$this->Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.id' => $this->order_id);
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		$this->set('results', $results);
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		/*
		 * msg 
		 */
		if($results['Delivery']['sys']=='Y')  {
			$msg = "Per poter chiudere l'ordine dovrai prima associarlo ad una consegna valida";
		}
		else {
			if($results['Order']['state_code']=='INCOMING-ORDER') {
				if($this->user->organization['Organization']['payToDelivery']=='POST')
					$msg = "Se chiudi l'ordine non potrai passarlo al TESORIERE per gestire i pagamenti";
				else
				if($this->user->organization['Organization']['payToDelivery']=='ON')
					$msg = "Se chiudi l'ordine non potrai passarlo al CASSIERE per gestire i pagamenti";
				else
				if($this->user->organization['Organization']['payToDelivery']=='ON-POST')
					$msg = "Se chiudi l'ordine non potrai passarlo al CASSIERE o al TESORIERE per gestire i pagamenti";
				
			}
			else
			if($results['Order']['state_code']=='PROCESSED-ON-DELIVERY') {	
				if($this->user->organization['Organization']['payToDelivery']=='POST')
					$msg = "Se chiudi l'ordine il TESORIERE non potr&agrave; più gestire i pagamenti";
				else
				if($this->user->organization['Organization']['payToDelivery']=='ON')
					$msg = "Se chiudi l'ordine il CASSIERE non potr&agrave; più gestire i pagamenti";
				else
				if($this->user->organization['Organization']['payToDelivery']=='ON-POST')
					$msg = "Se chiudi l'ordine il CASSIERE o il TESORIERE non potr&agrave; più gestire i pagamenti";
				
			}			
		}

		$this->set('msg', $msg);
		
		$order_just_pay = array('Y' => 'Si', 'N' => 'No');
		$this->set(compact('order_just_pay'));
		
		
		/*
		 * desOrder
		 */
		$isTitolareDesSupplier = false; 
		$des_order_id = 0;
		if($this->user->organization['Organization']['hasDes']=='Y') {		
	
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();

			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($this->user, $this->order_id, $debug);
			if(!empty($desOrdersOrganizationResults)) {
			
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
                        
		        $isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);
			
		} // DES
		$this->set(compact('isTitolareDesSupplier'));		
		$this->set(compact('des_order_id'));
		
		if ($this->request->is('post') || $this->request->is('put')) {


			$continue = true;
			$msg = '';
						
			if($results['Delivery']['sys']=='Y') {
				$msg = __("Lo stato dell'ordine non è stato aggiornato perchè non associato ad una consegna valida.");  
				$continue = false;
			}
			
			if($continue) {
		
				$order_just_pay = $this->request->data['Order']['order_just_pay'];
				
				/*
				 * calcolo il totale degli importi degli acquisti dell'ordine
				*/
				$importo_totale = $this->Order->getTotImporto($this->user, $this->order_id);
				/* 
				 *  bugs float: i float li converte gia' con la virgola!  li riporto flaot
				 */
				if(strpos($importo_totale,',')!==false)  $importo_totale = str_replace(',','.',$importo_totale);
				
				/*
				 * aggiorno stato ORDER
				*/
		        App::import('Model', 'OrderLifeCycle');
		        $OrderLifeCycle = new OrderLifeCycle();
		        
		        $options = array();
		        $options['tot_importo'] = $importo_totale;
		        $options['tesoriere_sorce'] = 'REFERENTE';
		        if($order_just_pay=='Y') {
		        	$options['tesoriere_data_pay'] = date('Y-m-d H:i:s');
		        	$options['tesoriere_importo_pay'] = $importo_totale;
		        	$options['tesoriere_fattura_importo'] = $importo_totale;
		        	$options['tesoriere_stato_pay'] = 'Y';
		        }
		        $esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'CLOSE', $options, $debug);
		        if($esito['CODE']!=200) {
		        	$msg = $esito['MSG'];
		        	$continue = false;
		        } 
			}
				
			if($continue) {
				/*
				 * popolo cmq SummaryOrder 
				 */
				App::import('Model', 'SummaryOrder');
				$SummaryOrder = new SummaryOrder;
				$resultsSummaryOrder = $SummaryOrder->select_to_order($this->user, $this->order_id);
					
				/*
				 * se summaryOrder non e' gia' stato popolato dal referente da Cart::admin_managementCartsGroupByUsers
				 */
				if(empty($resultsSummaryOrder))
					$SummaryOrder->populate_to_order($this->user, $this->order_id, 0);
			}					

			$this->Session->setFlash($msg);
			
			if($continue) {
				$msg = __("Lo stato dell'ordine è stato aggiornato.");
				$this->Session->setFlash($msg);
				$this->myRedirect(array('action' => 'home'));
			}				
		    
		} // end POST
	}
	
	public function admin_sotto_menu_bootstrap($order_id=0) {
		$this->_sotto_menu($this->user, $order_id);
	}
	
	/*  creo sotto menu degli ordini profilato
	 * 	in ArticlesOrdersController::beforeFilter() ctrl lato server
	 * 
	 *  position_img, le backgroung-img e' a Dx o Sn
	 */
	public function admin_sotto_menu($order_id=0, $position_img) {

		$this->_sotto_menu($this->user, $order_id);
		
		$this->set('position_img', $position_img);
	}
	
	private function _sotto_menu($user, $order_id) {

		$this->ctrlHttpReferer();
		
		$debug = false;
		
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
									   'Order.id' => $order_id);
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
				
		$this->set('results', $results);
		
		$group_id = $this->ActionsOrder->getGroupIdToReferente($user);
		$orderActions = $this->ActionsOrder->getOrderActionsToMenu($user, $group_id, $results['Order']['id'], $debug);
		$this->set('orderActions', $orderActions);
		
		$orderStates = $this->ActionsOrder->getOrderStatesToLegenda($user, $group_id, $debug);
		$this->set('orderStates', $orderStates);
		
		/*
		 * ctrl se e' un ordine condiviso DES
		 */
		$des_order_id = 0; 
		if($user->organization['Organization']['hasDes']=='Y') {
		
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();
			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($user, $order_id, $debug);
		 
			if(!empty($desOrdersOrganizationResults)) {
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
				$desOrder = $desOrdersOrganizationResults['DesOrder'];
			}	
		}
		$this->set(compact('des_order_id', 'desOrder'));
		 
		/*
         *  ctrl se e' una promozione
		 */
		if($results['Order']['prod_gas_promotion_id']>0) {
			
		}
			
		/*
		 * $pageCurrent = array('controller' => '', 'action' => '');
		 * mi serve per non rendere cliccabile il link corrente nel menu laterale
		*/
		$pageCurrent = $this->getToUrlControllerAction($_SERVER['HTTP_REFERER']);
		$this->set('pageCurrent', $pageCurrent);
		
		$this->layout = 'ajax';
	}

	/*
	 * associo ordine a consegna scaduta
	 */
	public function admin_edit_delivery_old() {

	   	if(empty($this->order_id) || empty($this->delivery_id)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	   	    	   
	   	$this->Order->id = $this->order_id;
	   	if (!$this->Order->exists($this->user->organization['Organization']['id'])) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	
		if ($this->request->is('post') || $this->request->is('put')) {

			/*
			 * cambio lo stato degli ORDERS
			*/
			$sql = "UPDATE
						".Configure::read('DB.prefix')."orders
					SET
						delivery_id = ".$this->request->data['Order']['delivery_id'].",
						modified = '".date('Y-m-d H:i:s')."'
					WHERE
						organization_id = ".(int)$this->user->organization['Organization']['id']."
						and id = ".(int)$this->order_id;
			// echo '<br />'.$sql;
			$result = $this->Order->query($sql);
			
		   	/*
		   	 * aggiorno lo stato degli ordini
		   	 * */
	   		$utilsCrons = new UtilsCrons(new View(null));
	   		if(Configure::read('developer.mode')) echo "<pre>";
	   		$utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
	   		if(Configure::read('developer.mode')) echo "</pre>";
			
			$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
		}
			
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.isVisibleBackOffice' => 'Y',
									   'Delivery.stato_elaborazione' => 'OPEN',
									   'Delivery.sys' => 'N', 
									   'DATE(Delivery.data) < CURDATE()');
		$options['order'] = array('Delivery.data ASC');
		$options['recursive'] = -1;
		$options['fields'] = array('Delivery.id', 'luogoData');
		$deliveries = $this->Order->Delivery->find('list', $options); 
		
		$this->set(compact('deliveries'));

	   	$options =array();
	   	$options['conditions'] = array ('Delivery.stato_elaborazione' => 'OPEN');
	   	$this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	   	 
	}
	
	public function admin_mail_supplier() {

		$debug = false;
	
	   	if(empty($this->order_id) || empty($this->delivery_id)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	   	   
	   	$this->Order->id = $this->order_id;
	   	if (!$this->Order->exists($this->user->organization['Organization']['id'])) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}

		$options = array();
		$options['conditions'] = array('Order.id' => $this->order_id,
										'Order.organization_id' => $this->user->organization['Organization']['id']);
		$options['recursive'] = 1;
		$results = $this->Order->find('first', $options);
	
		/*
		 *  dati produttore, mail
		 */
		App::import('Model', 'Supplier');
		$Supplier = new Supplier;	
		
		$options = array();
		$options['conditions'] = array('Supplier.id' => $results['SuppliersOrganization']['supplier_id']);
		$options['recursive'] = -1;
		$supplierResults = $Supplier->find('first', $options);
		$this->set('supplierResults',$supplierResults);
		
		$arr_extensions = array_merge(Configure::read('App.web.pdf.upload.extension'));
		$arr_extensions = array_merge(Configure::read('App.web.zip.upload.extension'),$arr_extensions);
		$arr_contentTypes = array_merge(Configure::read('ContentType.pdf'),Configure::read('ContentType.img'));
		$arr_contentTypes = array_merge(Configure::read('ContentType.zip'),$arr_contentTypes);
		$this->set('arr_extensions',$arr_extensions);
		
		if ($this->request->is('post') || $this->request->is('put')) {

			$msg = '';
			
			App::import('Model', 'Mail');
			$Mail = new Mail;
			
			/*
			echo "<pre>this->request->data \n ";
			print_r($this->request->data);
			echo "</pre>";
			*/
		
			$destinatatio_mail = $supplierResults['Supplier']['mail'];
			$subject = $this->request->data['Order']['subject'];
			$intestazione = $this->request->data['Order']['intestazione'];
			$mail_open_testo = $this->request->data['Order']['mail_open_testo'];
			if(!empty($intestazione)) 
				$body_mail = $intestazione.'<br /><br />'.$mail_open_testo;
			else
				$body_mail = $mail_open_testo;
						
			$Email = new CakeEmail(Configure::read('EmailConfig'));
			$Email->helpers(array('Html', 'Text'));
			$Email->template('default');
			$Email->emailFormat('html');
				
			$Email->replyTo(array($this->user->email => $this->user->email));
			$Email->from(array(Configure::read('SOC.mail') => Configure::read('SOC.name')));
			$Email->sender(Configure::read('SOC.mail'), Configure::read('SOC.name'));
			$Email->subject($subject);
			$Email->viewVars(array('header' => $Mail->drawLogo($this->user->organization)));						
			$Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
			$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))));
			$Email->to($destinatatio_mail);
			if(!Configure::read('mail.send')) $Email->transport('Debug');

			/*
			 * attachments
			 */
			$attachments = array();
			$path_upload = Configure::read('App.root').Configure::read('App.img.upload.tmp').DS;
			
			if(!empty($this->request->data['Document']['img1']['name'])){
				$esito = $this->Documents->genericUpload($this->user, $this->request->data['Document']['img1'], $path_upload, 'UPLOAD', '', $arr_extensions, $arr_contentTypes, '', $debug);
				if(empty($esito['msg']))
				    array_push($attachments, $path_upload.$esito['fileNewName']);
				else
					$msg = $esito['msg'];					
			}		
			if(empty($msg) && !empty($this->request->data['Document']['img2']['name'])) {		
				$esito = $this->Documents->genericUpload($this->user, $this->request->data['Document']['img2'], $path_upload, 'UPLOAD', '', $arr_extensions, $arr_contentTypes, '', $debug);
				if(empty($esito['msg'])) 
					array_push($attachments, $path_upload.$esito['fileNewName']);
				else
					$msg = $esito['msg'];					
			}	
			if($debug) {
				echo "<pre>attachments \n ";
				print_r($attachments);
				echo "</pre>";				
			}
			if(empty($msg) && !empty($attachments))
				$Email->attachments($attachments);
			
			if(empty($msg)) {
				try {
				// if($debug) echo '<br />body_mail '.$body_mail;
					$Email->send($body_mail);

					$msg = "Mail inviata al produttore $destinatatio_mail";

					/*
					 * save Mail
					 */	
					$data = array();
					$data['Mail']['organization_id'] = $this->user->organization['Organization']['id'];
					$data['Mail']['user_id'] = $this->user->id;
					$data['Mail']['mittente'] = $this->user->email;
					$data['Mail']['dest_options'] = 'SUPPLIERS';
					$data['Mail']['dest_options_qta'] = 'SOME';
					$data['Mail']['dest_ids'] = $destinatatio_mail;
					$data['Mail']['subject'] = $subject;
					$data['Mail']['body'] = $body_mail;
					if(isset($esito['fileNewName'])) // inserisco solo 1 allegato
						$data['Mail']['allegato'] = $esito['fileNewName'];
		
					if($debug) {
						echo "<pre>Mail \n ";
						print_r($data);
						echo "</pre>";					
					}
					
					$Mail->create();
					$Mail->save($data);
			
					if(!Configure::read('mail.send')) {
						$msg .= ' (modalita DEBUG)';
					}
				} catch (Exception $e) {
					$msg = "Errore nell'invio della mail al produttore $destinatatio_mail";
					CakeLog::write("error", $e, array("mails"));
				}
			} // if(empty($msg))
			
			$this->Session->setFlash($msg);			
			if($debug) echo '<br />'.$msg;
	
			if(!$debug) $this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
		}
			
		if(empty($supplierResults['Supplier']['mail'])) {
			$this->Session->setFlash("Il produttore \"".$results['SuppliersOrganization']['name']."\" non ha un indirizzo mail!");
			$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
		}
		else {
			
			$this->request->data = $results;
					
			/*
			 * testo della mail
			*/
			if(!empty($supplierResults['Supplier']['cognome']) || !empty($supplierResults['Supplier']['nome'])) {
				$intestatario = $supplierResults['Supplier']['nome']." ".$supplierResults['Supplier']['cognome'];
				$this->set('body_header', sprintf(Configure::read('Mail.body_header'), $intestatario));
			}
			else
				$this->set('body_header', 'Salve,');
			
			$this->set('body_footer', sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www'])));
		
			$testo_mail = '';			
			
			$testo_mail .= "per la <b>Consegna</b>  ";
			if($results['Delivery']['sys']=='Y')
				$testo_mail .= '<b>'.$results['Delivery']['luogo'].'</b>';
			else {
				$testo_mail .= ' presso '.$results['Delivery']['luogoData'];
				$testo_mail .= ', dalle ore '.substr($results['Delivery']['orario_da'], 0, 5).' alle '.substr($results['Delivery']['orario_a'], 0, 5);	
			}
			$testo_mail .= " ...";
			
			$testo_mail .= "<br /><br />";
			$testo_mail .= "allego i file";
			$testo_mail .= " ...";
			
			$testo_mail .= "<br /><br />";
			$testo_mail .= "<b>G</b>ruppo d'<b>a</b>cquisto <b>S</b>olidale  ".$this->user->organization['Organization']['name'];			
			$testo_mail .= " ...";
			
			
			$testo_mail .= "<br /><br />";
			$testo_mail .= "Se hai problemi contatta i <b>referenti</b>";	
			
			/*
			 * estraggo i referenti
			*/
			$suppliersOrganizationsReferent = $this->__getReferenti($this->user, $this->request->data['Order']['supplier_organization_id']);
			if(!empty($suppliersOrganizationsReferent)) {
				foreach ($suppliersOrganizationsReferent as $referent) {

					$testo_mail .= "<br />";
					$testo_mail .= ' - '.$referent['User']['name'].' ';
			
					if(!empty($referent['User']['email']))
						$testo_mail .= $referent['User']['email'].' ';
					
					if(!empty($referent['Profile']['phone']))  $testo_mail .= $referent['Profile']['phone'].' ';
					if(!empty($referent['Profile']['phone2'])) $testo_mail .= $referent['Profile']['phone2'];
				}
			} // end if(!empty($suppliersOrganizationsReferent))
			
			$this->set('testo_mail',$testo_mail);			
		}
		
	   	$options =array();
	   	$options['conditions'] = array ('Delivery.stato_elaborazione' => 'OPEN');
	   	$this->__boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	   	 
	}
	
	/*
	 * setto la visibility degli ordini a Y se l'organizzazione ha organizationHasVisibility = 'N'
	*/
	private function __ctrl_data_visibility() {
	
		if($this->user->organization['Organization']['hasVisibility']=='N') {
			$sql = "UPDATE ".Configure::read('DB.prefix')."orders
   						SET isVisibleFrontEnd = 'Y', isVisibleBackOffice = 'Y'
						WHERE organization_id = ".(int)$this->user->organization['Organization']['id'];
			$result = $this->Order->query($sql);
	
		}
	}
	
	/*
	 * cron: orders da OPEN a PROCESSED-POST-DELIVERY per gli ordini con le consegne chiuse
	*/
	private function __ctrl_state_code_error() {
	
		if($this->user->organization['Organization']['payToDelivery']=='ON' || $this->user->organization['Organization']['payToDelivery']=='ON-POST')
			$state_code_next = 'PROCESSED-ON-DELIVERY';
		else
			$state_code_next = 'PROCESSED-POST-DELIVERY';
		
		$sql = "SELECT `Order`.id
				FROM
					".Configure::read('DB.prefix')."deliveries Delivery,
					`".Configure::read('DB.prefix')."orders` `Order`
				WHERE
					Delivery.organization_id = ".(int)$this->user->organization['Organization']['id']."
					and `Order`.organization_id = ".(int)$this->user->organization['Organization']['id']."
					and Delivery.stato_elaborazione = 'OPEN'
					and `Order`.delivery_id = Delivery.id
					and `Order`.state_code = 'OPEN' 
					and DATE(Delivery.data) < CURDATE()
					and `Order`.data_fine < CURDATE()";
		//echo $sql."\n";
		$results = $this->Order->query($sql);
		foreach($results as $result) {
			$sql ="UPDATE `".Configure::read('DB.prefix')."orders`
				   SET
						state_code = '$state_code_next',
						modified = '".date('Y-m-d H:i:s')."'
				   WHERE
			   			organization_id = ".(int)$this->user->organization['Organization']['id']."
			   			and id = ".$result['Order']['id'];
			$this->Order->query($sql);
		}
	}	
	
	private function __getReferenti($user, $supplier_organization_id) {
	
		App::import('Model', 'SuppliersOrganizationsReferent');
		$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
		
		$conditions = array('User.block' => 0,
							'SuppliersOrganization.id' => $supplier_organization_id);
		$suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions);
		$this->set('suppliersOrganizationsReferent', $suppliersOrganizationsReferent);
		
		$this->set('userGroups', $this->userGroups);
		
		return $suppliersOrganizationsReferent;
	}	
	
	private function __getDesReferenti($user, $des_order_id) {
		App::import('Model', 'DesSuppliersReferent');
		$DesSuppliersReferent = new DesSuppliersReferent();			
		
		$conditions = array();
		$conditions = array('DesSuppliersReferent.organization_id' => $user->organization['Organization']['id'],
							'DesSupplier.des_id' => $user->des_id,
							'DesSupplier.supplier_id', $des_order_id);
		$desSuppliersReferents = $DesSuppliersReferent->getReferentsDesCompact($conditions);
		/*
		echo "<pre>";
		print_r($conditions);
		print_r($desSuppliersReferents);
		echo "</pre>";
		*/
		$this->set(compact('desSuppliersReferents'));

		return $desSuppliersReferents;	
   }
}