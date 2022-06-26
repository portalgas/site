<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class OrdersController extends AppController {

   public $components = ['RequestHandler', 'Routings', 'ActionsDesOrder', 'Documents', 'Connects']; 
   
   public $helpers = ['OrderHome', 'Text'];
   
   public function beforeFilter() {
   		parent::beforeFilter();
   		
		if(isset($this->request->data['Order']['id']))
			$this->order_id = $this->request->data['Order']['id'];
		else	
		if(isset($this->request->params['pass']['order_id']))
			$this->order_id = $this->request->params['pass']['order_id'];
		
   		/* ctrl ACL */
	   	$actionWithPermission = ['admin_home', 'admin_edit', 'admin_delete', 'admin_close', 'admin_sotto_menu', 'admin_sotto_menu_bootstrap', 'admin_edit_validation_cart', 'admin_delivery_change', 'admin_mail_supplier'];
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
   		
		if($this->user->organization['Organization']['type']!='GAS' &&
            $this->user->organization['Organization']['type']!='SOCIALMARKET') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	   		   	
   		/* ctrl ACL */  


   		$actionWithPermission = ['admin_home'];
   		if(in_array($this->action, $actionWithPermission)) {
   			/*
   			 * ctrl che la consegna sia visibile in backoffice
   			*/
   			if(!empty($this->delivery_id)) {
   				 
   				App::import('Model', 'Delivery');
   				$Delivery = new Delivery;
   				$results = $Delivery->read($this->delivery_id, $this->user->organization['Organization']['id']);
   				if(!empty($results) && $results['Delivery']['isVisibleBackOffice']=='N') {
   					$this->Session->setFlash(__('msg_delivery_not_visible_backoffice'));
   					$this->myRedirect(Configure::read('routes_msg_stop'));
   				}
   			}
   		
   			/*
   			 * ctrl che l'ordine sia visibile in backoffice
   			*/
   			$results = $this->Order->read($this->order_id, $this->user->organization['Organization']['id']);
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

		$debug = false;
		
	   	App::import('Model', 'Supplier');
	   	
	   	// App::import('Model', 'SuppliersOrganizationsReferent');
	   	
	   	App::import('Model', 'DesOrdersOrganization');
		
		App::import('Model', 'OrderLifeCycle');
		
		App::import('Model', 'RequestPayment');
		$RequestPayment = new RequestPayment;
		
	   	/*
	   	 * aggiorno lo stato degli ordini
	   	 * */
   		$utilsCrons = new UtilsCrons(new View(null));
   		if(Configure::read('developer.mode')) echo "<pre>";
   		$utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
   		if(Configure::read('developer.mode')) echo "</pre>";
   		
   		$this->_ctrl_data_visibility();
   			
		/*
		 * filters
		 */
		$conditions = [];
  		$FilterOrderOrderBy = 'Delivery.data asc';
		$FilterOrderSuppliersOrganizationId = null;

		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'SuppliersOrganizationId')) {
			$FilterOrderSuppliersOrganizationId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'SuppliersOrganizationId');
			$conditions += ['SuppliersOrganization.id' => $FilterOrderSuppliersOrganizationId];
		}
			
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'OrderBy')) {
			$FilterOrderOrderBy = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'OrderBy');
			$order = $FilterOrderOrderBy;
		}
		else
			$order = 'Delivery.data asc';
		$order .= ', Delivery.id, Order.data_inizio asc';
		
		$this->set('FilterOrderSuppliersOrganizationId', $FilterOrderSuppliersOrganizationId);
		$this->set('FilterOrderOrderBy', $FilterOrderOrderBy);
		
		/*
		 * filtri modulo
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
				
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									  'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = ['SuppliersOrganization.name'];
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
			
		$orders = ['Delivery.data asc' => 'Prime consegne', 'Delivery.data desc' => 'Ultime consegne'];
		$this->set('orders',$orders);
		   
		$SqlLimit = 75;
		$conditions += ['Delivery.organization_id'=>$this->user->organization['Organization']['id'],
						  'Order.organization_id'=>$this->user->organization['Organization']['id'],
						  'Delivery.isVisibleBackOffice'=>'Y',
						  'Delivery.stato_elaborazione'=>'OPEN',
						  'SuppliersOrganization.stato' => 'Y'];
					
		if(!$this->isSuperReferente()) {
			$conditions += ['Order.supplier_organization_id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')'];
		}

		$this->Order->recursive = 0;

		self::d($conditions, $debug);
		self::d($order, $debug);
		
	    $this->paginate = ['conditions' => $conditions, 'order' => $order, 'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];
		$results = $this->paginate('Order');

		foreach($results as $numResult => $result) {
	
			/*
			 * Suppliers per l'immagine
			 * */
			$Supplier = new Supplier;
			
			$options = [];
			$options['conditions'] = ['Supplier.id' => $result['SuppliersOrganization']['supplier_id']];
			$options['fields'] = ['Supplier.img1'];
			$options['recursive'] = -1;
			$SupplierResults = $Supplier->find('first', $options);
			if(!empty($SupplierResults))
				$results[$numResult]['Supplier']['img1'] = $SupplierResults['Supplier']['img1'];

			/*
			 * SuppliersOrganizationsReferent per la tipologia REFERENTE COREFERENTE TESORIERE
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganizationsReferent.organization_id' => $this->user->organization['Organization']['id'],
									'SuppliersOrganizationsReferent.supplier_organization_id' => $result['Order']['supplier_organization_id'],
									'SuppliersOrganizationsReferent.user_id' => $this->user->get('id')];
			$options['fields'] = ['type'];
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
				
				$options = [];
				$options['conditions'] = ['DesOrdersOrganization.order_id' => $result['Order']['id'],
										'DesOrdersOrganization.organization_id' => $this->user->organization['Organization']['id']];
				$options['recursive'] = -1;
				$desOrdersOrganization = $DesOrdersOrganization->find('first', $options);
				$results[$numResult]['DesOrdersOrganization'] = $desOrdersOrganization['DesOrdersOrganization'];
			} // DES
			 
			/*
			 * ordine saldato dai gasisti
			 * ordine pagato al produttore
			 */ 
			 $OrderLifeCycle = new OrderLifeCycle;
			
 		 	 $results[$numResult]['orderStateNext'] = $OrderLifeCycle->getOrderStateNext($this->user, $result, $this->isReferenteTesoriere, $debug);

 		 	 $results[$numResult]['PaidUsers'] = $OrderLifeCycle->getPaidUsers($this->user, $result, $debug);
		 
			 $results[$numResult]['PaidSupplier'] = $OrderLifeCycle->getPaidSupplier($this->user, $result, $debug);
			 
			 $results[$numResult]['Order']['can_state_code_to_close'] = $OrderLifeCycle->canStateCodeToClose($this->user, $result, $debug);
			 
			 $results[$numResult]['Order']['msgGgArchiveStatics'] = $OrderLifeCycle->msgGgArchiveStatics($this->user, $result, $debug);
			 
			 /*
			  * recupero richiesta di pagamento 
			  */ 
			$results[$numResult]['Order']['request_payment_num'] = '';
			$results[$numResult]['Order']['request_payment_id'] = '';
			if($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery']=='ON-POST') {
				$results[$numResult]['Order']['request_payment_num'] = $RequestPayment->getRequestPaymentNumByOrderId($this->user, $result['Order']['id']);
				$results[$numResult]['Order']['request_payment_id'] = $RequestPayment->getRequestPaymentIdByOrderId($this->user, $result['Order']['id']);
			} 
			  
		} // loop Orders
 
		$this->set(compact('results'));
		$this->set(compact('SqlLimit'));
		
		/*
		 * ctrl se ho i permessi per modificare le consegne
		 */
		if(!$this->isManager() && !$this->isManagerDelivery())
			$delivery_link_permission = false;
		else
			$delivery_link_permission = true;
		$this->set('delivery_link_permission', $delivery_link_permission);
		
		/*
		 * per ogni ordine CLOSE ctrl se richiesto il pagamento
		 */
		 $this->set('isRoot', $this->isRoot());
		 $this->set('isTesoriereGeneric', $this->isTesoriereGeneric());
		
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
	}

	/*
	 * ordini con consegne 'Delivery.stato_elaborazione' => 'CLOSE'
	 * DeliveryLifeCycle::deliveriesToClose()
	 */
	public function admin_index_history() {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;

		App::import('Model', 'SuppliersOrganizationsReferent');
		$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;
		
		App::import('Model', 'Supplier');
		
		App::import('Model', 'OrderLifeCycle');
			
		$SqlLimit = 20;
		$conditions[] = ['Delivery.organization_id' => $this->user->organization['Organization']['id'],
							  'Order.organization_id' => $this->user->organization['Organization']['id'],
							  'Delivery.sys' => 'N',
							  'Delivery.stato_elaborazione' => 'CLOSE'];
		
		if(!$this->isSuperReferente()) {
			$conditions[] = ['Order.supplier_organization_id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')'];
		}
	
		$this->Order->unbindModel(['belongsTo' => ['ArticlesOrder']]);
		$this->Order->recursive = 0;
		$this->paginate = ['conditions' => $conditions, 'order' => ['Delivery.data desc,Order.data_inizio'], 'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];
		$results = $this->paginate('Order');

		foreach($results as $numResult => $result) {

			/*
			 * Suppliers per l'immagine
			* */
			$Supplier = new Supplier;
				
			$options = [];
			$options['conditions'] = ['Supplier.id' => $result['SuppliersOrganization']['supplier_id']];
			$options['fields'] = ['Supplier.img1'];
			$options['recursive'] = -1;
			$SupplierResults = $Supplier->find('first', $options);
			if(!empty($SupplierResults))
				$results[$numResult]['Supplier']['img1'] = $SupplierResults['Supplier']['img1'];
			
			/*
			 * SuppliersOrganizationsReferent per la tipologia REFERENTE COREFERENTE TESORIERE
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
				
			$options = [];
			$options['conditions'] = ['SuppliersOrganizationsReferent.organization_id' => $this->user->organization['Organization']['id'],
										'SuppliersOrganizationsReferent.supplier_organization_id' => $result['Order']['supplier_organization_id'],
										'SuppliersOrganizationsReferent.user_id' => $this->user->get('id')];
			$options['fields'] = ['type'];
			$options['recursive'] = -1;
			$SuppliersOrganizationsReferentResults = $SuppliersOrganizationsReferent->find('first', $options);
			if(!empty($SuppliersOrganizationsReferentResults))
				$results[$numResult]['SuppliersOrganizationsReferent']['type'] = $SuppliersOrganizationsReferentResults['SuppliersOrganizationsReferent']['type'];
			* */
								
			/*
			 * richieste di pagamento
			 */
			if($this->user->organization['Template']['payToDelivery']=='POST' || $this->user->organization['Template']['payToDelivery']=='ON-POST') {
				$options = [];
				$options['conditions'] = ['RequestPaymentsOrder.organization_id' => $this->user->organization['Organization']['id'],
										  'RequestPaymentsOrder.order_id' => $result['Order']['id']];
				$options['recursive'] = 0;
				$RequestPaymentsOrder->unbindModel(['belongsTo' => ['Order']]);
				$resultsRequestPaymentsOrder = $RequestPaymentsOrder->find('first', $options);
				
				if(!empty($resultsRequestPaymentsOrder)) {
					$results[$numResult]['RequestPayment'] = $resultsRequestPaymentsOrder['RequestPayment'];
					$results[$numResult]['RequestPaymentsOrder'] = $resultsRequestPaymentsOrder['RequestPaymentsOrder'];
				}
			}
			
			/*
			 * ordine saldato dai gasisti
			 * ordine pagato al produttore
			 */ 
			 $OrderLifeCycle = new OrderLifeCycle;
			
 		 	 $results[$numResult]['orderStateNext'] = $OrderLifeCycle->getOrderStateNext($this->user, $result, $this->isReferenteTesoriere, $debug);

 		 	 $results[$numResult]['PaidUsers'] = $OrderLifeCycle->getPaidUsers($this->user, $result, $debug);
		 
			 $results[$numResult]['PaidSupplier'] = $OrderLifeCycle->getPaidSupplier($this->user, $result, $debug);
			 
			 $results[$numResult]['Order']['can_state_code_to_close'] = $OrderLifeCycle->canStateCodeToClose($this->user, $result, $debug);
			 
			 $results[$numResult]['Order']['msgGgArchiveStatics'] = $OrderLifeCycle->msgGgArchiveStatics($this->user, $result, $debug);
			 
		}
		
		$this->set('isRoot', $this->isRoot());

		$this->set(compact('results'));
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
		if(empty($supplier_organization_id))
			$supplier_organization_id = $this->request->data['Order']['supplier_organization_id'];
		self::d($supplier_organization_id, $debug);
		$this->set(compact('supplier_organization_id'));

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

			$this->order_id = $this->_add($this->user, $this->request->data, $debug);	

			if($this->order_id > 0) {
				
				/*
				 * associo ordine all'ordine DES
				 */
				 if($this->user->organization['Organization']['hasDes']=='Y' && !empty($des_order_id)) {
				 
					App::import('Model', 'DesOrdersOrganization');
					$DesOrdersOrganization = new DesOrdersOrganization();
					
					$data = [];
					$data['DesOrdersOrganization']['des_id'] = $this->user->des_id;
					$data['DesOrdersOrganization']['des_order_id'] = $des_order_id;
					$data['DesOrdersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
					$data['DesOrdersOrganization']['order_id'] = $this->order_id;
					
					$data['DesOrdersOrganization']['luogo'] = '';
					$data['DesOrdersOrganization']['data'] = Configure::read('DB.field.date.empty');
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
		$options =  [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
								'Delivery.isVisibleBackOffice' => 'Y',
								'Delivery.stato_elaborazione' => 'OPEN',
								'Delivery.sys'=>'N',
								'DATE(Delivery.data) >= CURDATE()'];
		$options['fields'] = ['Delivery.id', 'luogoData'];
		$options['order'] = ['Delivery.data ASC'];
		$deliveries = $this->Order->Delivery->find('list', $options); 
		
		/*
		* non piu' perche' ho Delivery.sys = Y
		if(empty($deliveries)) {
			$this->Session->setFlash(__('OrderNotFoundDeliveries'));
			$this->myRedirect(['action' => 'index']);
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
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
					'SuppliersOrganization.stato' => 'Y',
					'SuppliersOrganization.id' => $supplier_organization_id];
			$options['order'] = ['SuppliersOrganization.name'];
			$options['recursive'] = -1;
			self::d($options['conditions'], $debug);
			$ACLsuppliersOrganizationResults = $SuppliersOrganization->find('list', $options);

			$this->set('ACLsuppliersOrganization',$ACLsuppliersOrganizationResults);				
		}
		else {
			if($this->isSuperReferente()) {
				App::import('Model', 'SuppliersOrganization');
				$SuppliersOrganization = new SuppliersOrganization;
				
				$options = [];
				$options['conditions'] = [
						'SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
						'SuppliersOrganization.stato' => 'Y'];
				$options['order'] = ['SuppliersOrganization.name'];
				$options['recursive'] = -1;
				$ACLsuppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
				$this->set('ACLsuppliersOrganization',$ACLsuppliersOrganizationResults);
			}
			else 
				$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());		
		}
				
		$this->set('delivery_id',$delivery_id);  
		
		$qta_massima_um_options = ['KG' => 'Kg (prenderà in considerazione anche i Hg, Gr)', 'LT' => 'Lt (prenderà in considerazione anche i Hl, Ml)', 'PZ' => 'Pezzi'];
		$this->set(compact('qta_massima_um_options'));
		
		/*
		 * se sono ManagerDelivery ho il link per cerare una nuova consenge, se no invio una mail
		 */
		$this->set('isManagerDelivery', $this->isManagerDelivery());
		
		/*
		 * dati DesOrder, diverso dalle altre chiamate $this->ActionsDesOrder->getDesOrderData() perche' non ho ancora order_id
		 */
		 if($this->user->organization['Organization']['hasDes']=='Y' && !empty($des_order_id)) {
		 
			App::import('Model', 'DesOrder');
			$DesOrder = new DesOrder();
			
			$desOrdersResults = $DesOrder->getDesOrder($this->user, $des_order_id);
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
			$this->order_id = $this->_add($this->user, $this->request->data, $debug);	
			
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
		$options =  [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'Delivery.stato_elaborazione' => 'OPEN',
									    'Delivery.sys'=>'N',
										'DATE(Delivery.data) >= CURDATE()'];
		$options['fields'] = ['Delivery.id', 'luogoData'];
		$options['order'] = ['Delivery.data ASC'];
		$deliveries = $this->Order->Delivery->find('list', $options); 
		
		/*
		* non piu' perche' ho Delivery.sys = Y
		if(empty($deliveries)) {
			$this->Session->setFlash(__('OrderNotFoundDeliveries'));
			$this->myRedirect(['action' => 'index']);
		}
		*/
		$this->set(compact('deliveries'));

		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.stato' => 'Y'];
			$options['order'] = ['SuppliersOrganization.name'];
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
	
	private function _add($user, $requestData, $debug) {
	
		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;

		$requestData['Order']['organization_id'] = $user->organization['Organization']['id'];
		if(!empty($requestData['Order']['des_order_id'])) 
			$des_order_id = $requestData['Order']['des_order_id'];
		else
			$requestData['Order']['des_order_id'] = 0;
		
		$requestData['Order']['order_type_id'] = $OrderLifeCycle->getType($user, $requestData);

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
		
		/*
		 * riporto SuppliersOrganization owner_articles / owner_organization_id / owner_supplier_organization_id 
		 * cosi' se vengono cambiati rimangono legati all'ordine
		 */
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $requestData['Order']['organization_id'],
								  'SuppliersOrganization.id' => $requestData['Order']['supplier_organization_id']];
		$options['fields'] = ['SuppliersOrganization.owner_articles', 'SuppliersOrganization.owner_organization_id', 'SuppliersOrganization.owner_supplier_organization_id'];
		$options['recursive'] = -1;
		self::d($options, $debug);
		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		if(!empty($suppliersOrganizationResults)) {
			$requestData['Order']['owner_articles'] = $suppliersOrganizationResults['SuppliersOrganization']['owner_articles'];
			$requestData['Order']['owner_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['owner_organization_id'];
			$requestData['Order']['owner_supplier_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['owner_supplier_organization_id'];
		}
				
		self::d('OrderController::oggi '.$data_oggi.' = '.$data_inizio_db, $debug);
		self::d($requestData, $debug);

		/*
		 * richiamo la validazione 
		 */
		$msg_errors = $this->Order->getMessageErrorsToValidate($this->Order, $requestData);
		if(!empty($msg_errors)) {
			self::d($requestData, $debug);
			self::d($msg_errors, $debug);
			$order_id = 0;
		}
		else {
			$this->Order->create();
			if($this->Order->save($requestData)) 
				$order_id = $this->Order->getLastInsertId();
			else 
				$order_id = 0;
		}
		
		self::d('OrderController::_add() order_id '.$order_id, $debug);
		
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
		
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$this->set(compact('results'));
		
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
	
        /*
         * aggiorno lo stato dell'ordine 
         * se ordine della promozione creato in neo, qui aggiorno lo stato
         */
        $utilsCrons = new UtilsCrons(new View(null));
        $utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $this->order_id);

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$this->set(compact('results'));

		$group_id = $this->ActionsOrder->getGroupIdToReferente($this->user);	
		$orderActions = $this->ActionsOrder->getOrderActionsToMenu($this->user, $group_id, $results['Order']['id'], $debug);
		
		/* 
		 * elimino l'item Order home (id=0) perche' sono già in home
		 */
		if($orderActions[0]['OrdersAction']['id']==0) 
			unset($orderActions[0]);
		$this->set('orderActions', $orderActions);
	
		/*
		 * creo degli OrderActions di raggruppamento per controller (Orders, Carts, etc)
		*/
		$raggruppamentoOrderActions = [];
		/*
		 * bugs, per questi stati non raggruppo perche' ho 2 OrderController con conseguenziali
		 */
		if($results['Order']['state_code']!='PROCESSED-ON-DELIVERY' && $results['Order']['prod_gas_promotion_id']==0)
			$raggruppamentoOrderActions = $this->ActionsOrder->getRaggruppamentoOrderActions($orderActions, $debug);
		$this->set('raggruppamentoOrderActions', $raggruppamentoOrderActions);
		
        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);
		$des_order_id = $desResults['des_order_id'];
		$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
		$this->set('des_order_id',$des_order_id);
		$this->set('isTitolareDesSupplier', $isTitolareDesSupplier);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		
		/*
		 * dati promozione del GAS (gli passo $this->user->organization['Organization']['id'])
		 */
		$supplier_id = 0;
		$prod_gas_promotion_id = $results['Order']['prod_gas_promotion_id']; 
		$this->set(compact('supplier_id', 'prod_gas_promotion_id'));	
		 
		if($prod_gas_promotion_id!=0) { 
			App::import('Model', 'ProdGasPromotion');
			$ProdGasPromotion = new ProdGasPromotion;
			 
			$promotionResults = $ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id, $this->user->organization['Organization']['id']);
			$this->set('promotionResults', $promotionResults);
		}
	}

	public function admin_edit() {
	
		$debug = false;
		$continua = true;
		
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

        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);
		$des_order_id = $desResults['des_order_id'];
		$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
		$this->set('des_order_id',$des_order_id);
		$this->set('isTitolareDesSupplier', $isTitolareDesSupplier);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		
		if ($this->request->is('post') || $this->request->is('put')) {
		
			/*
			 * ordine prima del salvataggio, ctrl successivi
			 */
			$options = [];
			$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
			$options['recursive'] = -1;
			$OrderOldresults = $this->Order->find('first', $options);
			self::d($OrderOldresults, $debug);
			$this->request->data['Order']['owner_articles'] = $OrderOldresults['Order']['owner_articles'];
			$this->request->data['Order']['owner_organization_id'] = $OrderOldresults['Order']['owner_organization_id'];
			$this->request->data['Order']['owner_supplier_organization_id'] = $OrderOldresults['Order']['owner_supplier_organization_id'];
			$this->request->data['Order']['order_type_id'] = $OrderOldresults['Order']['order_type_id'];
						
			$this->request->data['Order']['data_inizio'] = $this->request->data['Order']['data_inizio_db'];
			$this->request->data['Order']['data_fine'] = $this->request->data['Order']['data_fine_db'];
			/*
			 * ho ripulito il campo di riapertura, se non voglio piu' l'ordine riaperto
			 */
			if(!empty($this->request->data['Order']['data_fine_validation'])) {
				$this->request->data['Order']['data_fine_validation'] = $this->request->data['Order']['data_fine_validation_db'];
			}
			else {
				$this->request->data['Order']['data_fine_validation_db'] = '';
			}
			
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
				debug ('oggi '.$data_oggi.' = '.$data_inizio_db);
				debug ('mail_open_send '.$this->request->data['Order']['mail_open_send']);
			}

			/*
			 * consegna da definire
			*/
			if($this->request->data['typeDelivery']=='to_defined') {			
				$deliverySysResults = $Delivery->getDeliverySys($this->user);
				$this->request->data['Order']['delivery_id'] = $deliverySysResults ['Delivery']['id'];
			}
			
			/*
			 * richiamo la validazione 
			 */
			$msg_errors = $this->Order->getMessageErrorsToValidate($this->Order, $this->request->data);
			if(!empty($msg_errors)) {
				self::d($requestData, $debug);
				self::d($msg_errors, $debug);
				$continua=false;
			}
			// debug($this->request->data); 
			if($continua) {	
				$this->Order->create();
				if (!$this->Order->save($this->request->data))
					$continua=false;
			}
			
			if($continua) {
				$msg .= __('The order has been saved').'<br/>';
				 
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
				
				$options = [];
				$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
				$options['recursive'] = -1;
				$results = $this->Order->find('first', $options);
				
				self::d($results, false);

				App::import('Model', 'OrderLifeCycle');
				$OrderLifeCycle = new OrderLifeCycle;
				
				$OrderLifeCycle->changeOrder($this->user, $results, 'EDIT');
				
				$this->Session->setFlash($msg);
				
				/*
				 * se e' cambiata la consegna vado sulla pagina per inviare la mail agli utenti
				 */
				if($OrderOldresults['Order']['delivery_id']!=$this->request->data['Order']['delivery_id']) {
					
					$OrderLifeCycle->changeOrder($this->user, $results, 'CHANGE_DELIVERY');
				
					App::import('Model', 'User');
					$User = new User;
					
					$conditions = [];
					$conditions = ['ArticlesOrder.order_id' => $this->order_id];
					$results = $User->getUserWithCartByOrder($this->user ,$conditions);
					
					if(count($results)>0)
						$redirect = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=delivery_change&delivery_id_old='.$OrderOldresults['Order']['delivery_id'];
					else
						$redirect = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id;
				}
				else
					$redirect = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id;
					
				self::d($redirect, $debug);	
				if(!$debug) 
					$this->myRedirect($redirect);
						
			} else 
				$this->Session->setFlash(__('The order could not be saved. Please, try again.').$msg_errors);
			
		} // else if ($this->request->is('post') || $this->request->is('put'))

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
		$options['recursive'] = 1;
		$this->request->data = $this->Order->find('first', $options);

		/*
		 * REDIRECT PACT
		 */
		switch ($this->request->data['Order']['order_type_id']) {
			case Configure::read('Order.type.pact'):
				$params = ['order_id' => $this->order_id];
				$url = $this->Connects->createUrlBo('admin/orders', 'edit', $params);
				$this->myRedirect($url);
			break;
		}

		if (empty($this->request->data)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		if($this->request->data['Delivery']['isVisibleBackOffice']=='N') {
			$msgDeliveryNotValid = "La consegna '".$this->request->data['Delivery']['luogoData']."' associata all'ordine non &egrave; disponibile";
			$this->set('msgDeliveryNotValid' , $msgDeliveryNotValid);
		}
		
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									'Delivery.isVisibleBackOffice' => 'Y',
									'DATE(Delivery.data) >= CURDATE()',
									'Delivery.sys'=>'N',
									'Delivery.stato_elaborazione' => 'OPEN'];
		$options['fields'] = ['Delivery.id', 'luogoData'];
		$options['order'] = ['Delivery.data ASC'];
		$deliveries = $this->Order->Delivery->find('list', $options);
		/*
		 * non piu' perche' ho Delivery.sys = Y
		if(empty($deliveries)) {
			$this->Session->setFlash(__('OrderNotFoundDeliveries'));
			$this->myRedirect(['action' => 'index']);
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
		$this->_getReferenti($this->user, $this->request->data['Order']['supplier_organization_id']);
		
		if($isDesOrder) 
			$this->_getDesReferenti($this->user, $this->des_order_id);
		
		$qta_massima_um_options = ['KG' => 'Kg (prenderà in considerazione anche i Hg, Gr)', 'LT' => 'Lt (prenderà in considerazione anche i Hl, Ml)', 'PZ' => 'Pezzi'];
		$this->set(compact('qta_massima_um_options'));

		/*
		 * se sono ManagerDelivery ho il link per cerare una nuova consenge, se no invio una mail
		*/
		$this->set('isManagerDelivery', $this->isManagerDelivery());
		
		/*
		 * ctrl se ci sono consegne scadute, per far vedere il tasto "associa a consegna scaduta"
		 */		
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.isVisibleBackOffice' => 'Y',
									   'Delivery.stato_elaborazione' => 'OPEN',
									   'Delivery.sys' => 'N', 
									   'DATE(Delivery.data) < CURDATE()'];
		$options['order'] = ['Delivery.data ASC'];
		$options['recursive'] = -1;
		$tot_delivery_old = $Delivery->find('count', $options); 
		$this->set('tot_delivery_old', $tot_delivery_old);	
		
	}

	public function admin_view() {
		$this->_view();
	}
	
	/*
	 * ho l'ordine in solo lettura e senza 
	 * 		la home dell'ordine 
	 * 		il menu dell'ordine
	 * perche' non sono referente
	 */
	public function admin_view_public() {
	
		$this->_view();
	}
	
	private function _view() {
		$debug=false;
		
		$msg = '';
		$this->Order->id = $this->order_id;
		if (!$this->Order->exists($this->Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);
		$des_order_id = $desResults['des_order_id'];
		$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
		$this->set('des_order_id',$des_order_id);
		$this->set('isTitolareDesSupplier', $isTitolareDesSupplier);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
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
		$this->_getReferenti($this->user, $this->request->data['Order']['supplier_organization_id']);
		
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
		if (!$this->Order->exists($this->Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
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
			// debug($this->request->data['Order']);

			/*
			 * aggiorno stato ORDER
			*/			
	        App::import('Model', 'OrderLifeCycle');
	        $OrderLifeCycle = new OrderLifeCycle();
	        
	        $options = [];
	        $options['data_fine_validation'] = $data_fine_validation_db;
	        $esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'RI-OPEN-VALIDATE', $options, $debug);
	        if($esito['CODE']!=200) {
	        	$msg = $esito['MSG'];
	        	$continue = false;
	        } 
			
			if($continue) {
				if($debug) debug("riapro l'ordine");
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
					
					$usersResults = $User->getUsersToMail($this->user->organization['Organization']['id']);
					
					if(!empty($usersResults)) {
		
						$subject_mail = 'Riapertura ordine per completare i colli';
						$Email->subject($subject_mail);
						if(!empty($this->user->organization['Organization']['www']))
							$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
						else
							$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);
							
						$msg .= '<br />Inviata la mail a<br />';
						/*
					 	* ciclo UTENTI
					 	*/
						foreach($usersResults as $numResult => $user) {
			
							$mail = $user['User']['email'];
							$mail2 = $user['UserProfile']['email'];
							$name = $user['User']['name'];
								
							if(!empty($mail)) {
								$mail_open_testo = str_replace('gg/mm/yyy', $data_fine_validation, $mail_open_testo);
								$body_mail = $mail_open_testo;
	
								$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
								$Email->to($mail);
									
								$mailResults = $Mail->send($Email, [$mail2, $mail], $body_mail, $debug);
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
		
		$invio_mail = ['Y' => 'Si', 'N' => 'No'];
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
		$testo_mail .= "Vai su https://".Configure::read('SOC.site')." e completa l'ordine ";
		
		if ($result['Delivery']['sys'] == 'Y')
        	$delivery = $result['Delivery']['luogo'];
        else
            $delivery = $result['Delivery']['luogoData'];
                		
		$testo_mail = sprintf(Configure::read('Mail.body_carts_validation'), $delivery, $results['SuppliersOrganization']['name'], 'gg/mm/yyy', $testo_mail);
		
		$this->set('testo_mail',$testo_mail);
	}
	
	/*
	*  se e' cambiata la consegna avviso gli eventuali acquirenti
	*/
	public function admin_delivery_change() {
	
		$debug = false;
		self::d($this->request->data, $debug);	
			
		$msg = '';
		$this->Order->id = $this->order_id;
		if (!$this->Order->exists($this->Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
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
		
		self::d('delivery_id_old '.$delivery_id_old, $debug);
		
		$this->set(compact('delivery_id_old'));
		
		if(!empty($delivery_id_old)) {
			App::import('Model', 'Delivery');
			$Delivery = new Delivery;
					
			$options = [];
			$options['conditions'] = ['Delivery.organization_id' => $this->user->organization['Organization']['id'],
										   'Delivery.id' => $delivery_id_old];
			$options['fields'] = ['Delivery.sys', 'Delivery.data', 'Delivery.luogo', 'Delivery.luogoData'];
			$options['recursive'] = -1;
			
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

			$conditions = [];
			$conditions = ['ArticlesOrder.order_id' => $this->order_id];
			$users = $User->getUserWithCartByOrder($this->user ,$conditions);
			
			if(!empty($users)) {

				$subject_mail = "L'ordine di ".$results['SuppliersOrganization']['name']." ha cambiato consegna";
				$Email->subject($subject_mail);
				if(!empty($this->user->organization['Organization']['www']))
					$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
				else
					$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);

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

						$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
						$Email->to($mail);

						$mailResults = $Mail->send($Email, $mail, $body_mail, $debug);
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
		$suppliersOrganizationsReferent = $this->_getReferenti($this->user, $this->request->data['Order']['supplier_organization_id']);
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
		
		if(isset($this->request->data['Order']['id']))
			$order_id = $this->request->data['Order']['id'];
		else	
		if(isset($this->request->params['pass']['order_id']))
			$order_id = $this->request->params['pass']['order_id'];
		
		$this->Order->id = $order_id;
		if (!$this->Order->exists($this->Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		$canOrdersDelete = true;

		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle();
		if(!$OrderLifeCycle->canOrdersDelete($this->user, $debug)) 
			$canOrdersDelete = false;
		$this->set(compact('canOrdersDelete'));
			
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $order_id];
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		$this->set(compact('results'));
		
        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $order_id, $debug);
		$des_order_id = $desResults['des_order_id'];
		$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
		$this->set('des_order_id',$des_order_id);
		$this->set('isTitolareDesSupplier', $isTitolareDesSupplier);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
			
		if ($this->request->is('post') || $this->request->is('put')) {

				if(!$canOrdersDelete) {
					$this->Session->setFlash(__('msg_not_permission'));
					$this->myRedirect(Configure::read('routes_msg_exclamation'));				
				}
				
				/*
				 * copia di backup di Order / ArticlesOrder / Cart
				 */
				App::import('Model', 'BackupOrder');
				$BackupOrder = new BackupOrder();				
				$BackupOrder->copyData($this->user, $order_id, $debug);
				
				$msg_ok = '';
				$msg_no = '';
				$tot_ok=0;
				$tot_no=0;

				/*
				 * invio mail
				*/				
				if($this->request->data['send_mail']=='Y') {
			
					$body_mail = $this->request->data['Order']['mail_open_testo'];
	
					App::import('Model', 'Mail');
					$Mail = new Mail;

					$Email = $Mail->getMailSystem($this->user);

					/*
			 		* estraggo gli utenti ai quali inviare la mail
					*/
					App::import('Model', 'User');
					$User = new User;

					$conditions = [];
					$conditions = ['ArticlesOrder.order_id' => $order_id];
					$users = $User->getUserWithCartByOrder($this->user ,$conditions);
			
					if(!empty($users)) {

						$subject_mail = "L'ordine di ".$results['SuppliersOrganization']['name']." cancellato";
						$Email->subject($subject_mail);
						if(!empty($this->user->organization['Organization']['www']))
							$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
						else
							$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);

						/*
					 	* ciclo UTENTI
						*/
						foreach($users as $numResult => $user) {

							$mail = $user['User']['email'];
							$name = $user['User']['name'];
							
							self::d('Mail a '.$mail, $debug);

							$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);

							$mailResults = $Mail->send($Email, $mail, $body_mail, $debug);
							if(isset($mailResults['OK'])) {
								$tot_ok++;
								$msg_ok .= $mailResults['OK'].'<br />';							
							}
							else 
							if(isset($mailResults['KO'])) {
								$tot_no++;
								$msg_ok .= $mailResults['KO'].'<br />';	
							}

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
	
							App::import('Model', 'DesOrder');
							$DesOrder = new DesOrder();				
										
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
											AND order_id = ".(int)$order_id;
								self::d($sql, $debug);
								$resultDelete = $this->Order->query($sql);
							}
							catch (Exception $e) {
								CakeLog::write('error',$sql);
							}                                                                
						}
					} //  end if($this->user->organization['Organization']['hasDes']=='Y') 


					$msg = __('Delete Order').'<br />';
					/*
					 * messaggio
					 */
					if(!empty($msg_ok)) $msg_ok = 'La mail è stata inviata a<br />'.$msg_ok.'<br/>Totale: '.$tot_ok; 
					if(!empty($msg_no)) $msg_no = '<hr />La mail NON è stata inviata a<br />'.$msg_no.'<br/>Totale: '.$tot_no; 
					$msg .= $msg_ok.$msg_no;
					$this->Session->setFlash($msg);
				}
				else
					$this->Session->setFlash(__('Order was not deleted'));
				
			setcookie('order_id', '', time() - 42000, Configure::read('App.server'));
			$this->Session->delete('order_id');
			
			$this->myRedirect(['action' => 'index']);
		} // end POST
			
		App::import('Model', 'ArticlesOrder');
		/*
		 * articoli acquistati
		 * 		ho bindModel di Cart
		*/
		$ArticlesOrder = new ArticlesOrder;
		
		$ArticlesOrder->unbindModel(['belongsTo' => ['Order']]);
		$options = [];
		$options['conditions'] = ['ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
								   'ArticlesOrder.order_id' => $order_id,
								   'Article.stato' => 'Y',
								   'Cart.order_id' => $order_id];
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
			$suppliersOrganizationsReferent = $this->_getReferenti($this->user, $results['Order']['supplier_organization_id']);
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
		$ArticlesOrder->unbindModel(['belongsTo' => ['Cart', 'Order']]);
		$options = [];
		$options['conditions'] = ['ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
									'ArticlesOrder.order_id' => $order_id,
									'Article.stato' => 'Y'];
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
		
		self::d($this->request->data, $debug);
	
		$this->Order->id = $this->order_id;
		if (!$this->Order->exists($this->Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		$this->set(compact('results'));
		self::d($results, $debug);

		$canOrdersClose = true;

		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle();
		if(!$OrderLifeCycle->canOrdersClose($this->user, $results, $debug)) 
			$canOrdersClose = false;
		else {
			// rileggo Order con totImport aggiornato
			$options = [];
			$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
			$options['recursive'] = 0;
			$results = $this->Order->find('first', $options);
			$this->set(compact('results'));
			self::d($results, $debug);			
		}
		$this->set(compact('canOrdersClose'));
		
		/*
		 * msg 
		 */	 
		$msg = $OrderLifeCycle->beforeRendering($this->user, $results, $this->request->params['controller'], $this->action);
		if(!empty($msg['msgOrderToClose'])) 
			$msg = $msg['msgOrderToClose'];
		else
			$msg = '';
		self::d($msg, $debug);
		$this->set(compact('msg'));
		
		/*
		 * se order_just_pay = Y forzo il pagamento di un produttore
		 */		
		if($this->user->organization['Template']['orderSupplierPaid']=='Y') {
			$order_just_pay = ['Y' => 'Si', 'N' => 'No'];
			$this->set(compact('order_just_pay'));
		}
		
        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);
		$des_order_id = $desResults['des_order_id'];
		$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
		$this->set('des_order_id',$des_order_id);
		$this->set('isTitolareDesSupplier', $isTitolareDesSupplier);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			if(!$canOrdersClose) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));				
			}
						
			if($this->user->organization['Template']['orderSupplierPaid']=='Y')
				$order_just_pay = $this->request->data['Order']['order_just_pay'];
			if($order_just_pay=='Y')
				$order_just_pay = true;
			else
				$order_just_pay = false;
			
			$options = [];
			$options['order_just_pay'] = $order_just_pay; // setta il pagamento del produttore
			$esito = $OrderLifeCycle->stateCodeUpdate($this->user, $results, 'CLOSE', $options, $debug);
			if($esito['CODE']!=200) {
				$msg = $esito['MSG'];
				$this->Session->setFlash($msg);
			}
            else {
				$msg = __('OrderStateCodeUpdate');
				$this->Session->setFlash($msg);
				if(!$debug)
					$this->myRedirect(['controller' => 'Orders', 'action' => 'index']);
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
		
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
		$options['recursive'] = 0;
		$results = $this->Order->find('first', $options);
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
				
		$this->set(compact('results'));
		
		$group_id = $this->ActionsOrder->getGroupIdToReferente($user);
		$orderActions = $this->ActionsOrder->getOrderActionsToMenu($user, $group_id, $results['Order']['id'], $debug);
		$this->set('orderActions', $orderActions);
		
		$orderStates = $this->ActionsOrder->getOrderStatesToLegenda($user, $group_id, $debug);
		$this->set('orderStates', $orderStates);
		
        /*
         * D.E.S.
         */
		$desResults = $this->ActionsDesOrder->getDesOrderData($this->user, $this->order_id, $debug);
		$des_order_id = $desResults['des_order_id'];
		$isTitolareDesSupplier = $desResults['isTitolareDesSupplier'];
		$this->set('des_order_id',$des_order_id);
		$this->set('isTitolareDesSupplier', $isTitolareDesSupplier);
		$this->set('desOrdersResults', $desResults['desOrdersResults']);
		$this->set('summaryDesOrderResults', $desResults['summaryDesOrderResults']);
		 
		/*
         *  ctrl se e' una promozione
		 */
		if($results['Order']['prod_gas_promotion_id']>0) {
			
		}
			
		/*
		 * $pageCurrent = ['controller' => '', 'action' => ''];
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
	   	if (!$this->Order->exists($this->Order->id, $this->user->organization['Organization']['id'])) {
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
			self::d($sql, false);
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
		
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
								   'Delivery.isVisibleBackOffice' => 'Y',
								   'Delivery.stato_elaborazione' => 'OPEN',
								   'Delivery.sys' => 'N', 
								   'DATE(Delivery.data) < CURDATE()'];
		$options['order'] = ['Delivery.data' => 'asc'];
		$options['recursive'] = -1;
		$options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$deliveries = $this->Order->Delivery->find('list', $options); 
		
		$this->set(compact('deliveries'));

	   	$options =[];
	   	$options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
	   	$this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	   	 
	}
	
	public function admin_mail_supplier() {

		$debug = false;

	   	if(empty($this->order_id) || empty($this->delivery_id)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	   	   
	   	$this->Order->id = $this->order_id;
	   	if (!$this->Order->exists($this->Order->id, $this->user->organization['Organization']['id'])) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $this->order_id];
		$options['recursive'] = 1;
		$results = $this->Order->find('first', $options);
	
		/*
		 *  dati produttore, mail
		 */
		App::import('Model', 'Supplier');
		$Supplier = new Supplier;	
		
		$options = [];
		$options['conditions'] = ['Supplier.id' => $results['SuppliersOrganization']['supplier_id']];
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

			$Email = $Mail->getMailSystem($this->user);
			
			self::d($this->request->data, $debug);	
		
			$destinatatio_mail = $supplierResults['Supplier']['mail'];
			$subject = $this->request->data['Order']['subject'];
			if(empty($subject))
				$subject = '';
			$intestazione = $this->request->data['Order']['intestazione'];
			$mail_open_testo = $this->request->data['Order']['mail_open_testo'];
			if(!empty($intestazione)) 
				$body_mail = $intestazione.'<br /><br />'.$mail_open_testo;
			else
				$body_mail = $mail_open_testo;
				
			$Email->replyTo([$this->user->email => $this->user->email]);
			$Email->subject($subject);
			$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
			$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);

			/*
			 * attachments
			 */
			$attachments = [];
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
			
			self::d($attachments, $debug);	
			
			if(empty($msg) && !empty($attachments))
				$Email->attachments($attachments);
			
			if(empty($msg)) {
				try {
				// if($debug) echo '<br />body_mail '.$body_mail;

					$mailResults = $Mail->send($Email, $destinatatio_mail, $body_mail, $debug);
					if(isset($mailResults['OK'])) {
						$tot_ok++;
						$msg_ok .= $mailResults['OK'].'<br />';							
					}
					else 
					if(isset($mailResults['KO'])) {
						$tot_no++;
						$msg_ok .= $mailResults['KO'].'<br />';	
					}
					
					$msg = "Mail inviata al produttore $destinatatio_mail";

					/*
					 * save Mail
					 */	
					$data = [];
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
		
					self::d($data, $debug);	
					
					$Mail->create();
					$Mail->save($data);
			
				} catch (Exception $e) {
					$msg = "Errore nell'invio della mail al produttore $destinatatio_mail";
					CakeLog::write("error", $e, ['mails']);
				}
			} // if(empty($msg))
			
			$this->Session->setFlash($msg);			
			self::d($msg, $debug);
	
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
			$suppliersOrganizationsReferent = $this->_getReferenti($this->user, $this->request->data['Order']['supplier_organization_id']);
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
		
	   	$options =[];
	   	$options['conditions'] = ['Delivery.stato_elaborazione' => 'OPEN'];
	   	$this->_boxOrder($this->user, $this->delivery_id, $this->order_id, $options);
	   	 
	}
	
	/*
	 * setto la visibility degli ordini a Y se l'organizzazione ha organizationHasVisibility = 'N'
	*/
	private function _ctrl_data_visibility() {
	
		if($this->user->organization['Organization']['hasVisibility']=='N') {
			$sql = "UPDATE ".Configure::read('DB.prefix')."orders
   						SET isVisibleFrontEnd = 'Y', isVisibleBackOffice = 'Y'
						WHERE organization_id = ".(int)$this->user->organization['Organization']['id'];
			$result = $this->Order->query($sql);
		}
	}
	
	private function _getReferenti($user, $supplier_organization_id) {
	
		App::import('Model', 'SuppliersOrganizationsReferent');
		$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
		
		$conditions = ['User.block' => 0,
						'SuppliersOrganization.id' => $supplier_organization_id];
		$suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions);
		$this->set('suppliersOrganizationsReferent', $suppliersOrganizationsReferent);
		
		$this->set('userGroups', $this->userGroups);
		
		return $suppliersOrganizationsReferent;
	}	
	
	private function _getDesReferenti($user, $des_order_id) {
		App::import('Model', 'DesSuppliersReferent');
		$DesSuppliersReferent = new DesSuppliersReferent();			
		
		$conditions = [];
		$conditions = ['DesSuppliersReferent.organization_id' => $user->organization['Organization']['id'],
						'DesSupplier.des_id' => $user->des_id,
						'DesSupplier.supplier_id', $des_order_id];
		$desSuppliersReferents = $DesSuppliersReferent->getReferentsDesCompact($conditions);
		
		self::d($conditions, $debug);
		self::d($desSuppliersReferents, $debug);
		
		$this->set(compact('desSuppliersReferents'));

		return $desSuppliersReferents;	
   }

   /*
    * cambia stato da CLOSE a PROCESSED-BEFORE-DELIVERY / PROCESSED-POST-DELIVERY / INCOMING-ORDER
	*/
   public function admin_state_code_change($order_id, $url_bck) {

		$debug = false;

   		/* ctrl ACL */	   		 
		   if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
					'Order.id' => $order_id,
					'Order.state_code' => 'CLOSE'
			];
		$options['recursive'] = 0;
		$orderResult = $this->Order->find('first', $options);
		// if($debug) debug($orderResult); 
		if (empty($orderResult)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle();	

		App::import('Model', 'DeliveryLifeCycle');
		$DeliveryLifeCycle = new DeliveryLifeCycle();			
		
		if($debug) debug($this->user->organization['Template']); 
		switch ($this->user->organization['Template']['payToDelivery']) {
			case 'ON':
			case 'ON-POST':
				$state_code_next = 'INCOMING-ORDER';
			break;
			case 'POST':
				if ($orderResult['Delivery']['data'] > date("Y-m-d"))
					$state_code_next = 'PROCESSED-BEFORE-DELIVERY';
				$state_code_next = 'PROCESSED-POST-DELIVERY';				
			break;
		}				
		if($debug) debug('state_code_next '.$state_code_next); 

		$results = $OrderLifeCycle->stateCodeUpdate($this->user, $orderResult, $state_code_next, $opts=[], $debug);
		if(isset($results['CODE']) && $results['CODE']!='200') {
			if($debug) debug($results);
		}
		else {
			$results = $DeliveryLifeCycle->deliveriesToOpen($this->user, $orderResult['Delivery']['id'], $debug);
			if($debug) debug($results);

			$this->Session->setFlash("Ripristinato l'ordine allo stato ".__($state_code_next.'-label'));
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$orderResult['Delivery']['id'].'&order_id='.$order_id;
			if($debug) debug($url);	
		}
		
		if(!$debug) $this->myRedirect($url);

		exit;
   }
}