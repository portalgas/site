<?php
/* 
 * Storerooms: dispensa 
 */

App::uses('AppController', 'Controller');

class StoreroomsController extends AppController {

	public $helpers = ['Html', 'Javascript', 'Ajax', 'Tabs'];
	public $storeroomUser = null;
	public $isUserCurrentStoreroom = false;
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		/*
		 * ctrl configurazione Organization
		 */
		if($this->user->organization['Organization']['hasStoreroom']=='N') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$this->storeroomUser = $this->Storeroom->getStoreroomUser($this->user);
		if(empty($this->storeroomUser)) {
			$this->Session->setFlash(__('StoreroomNotFound'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	
		
		/*
		echo "<pre>";
		print_r($this->storeroomUser);
		echo "</pre>";
		*/
		
		/*
		 *  ctrl se lo user corrente e' la dispensa
		 */	
		if($this->storeroomUser['User']['id']==$this->user->get('id') && 
			$this->storeroomUser['User']['organization_id']==$this->user->organization['Organization']['id']) {
				$this->isUserCurrentStoreroom = true;	
		}		
		$this->set('isUserCurrentStoreroom',$this->isUserCurrentStoreroom);	

		/* ctrl ACL */
		$actionWithPermission = ['admin_storeroomToUser','admin_edit'];
		if (in_array($this->action, $actionWithPermission)) {
			
			if($this->isSuperReferente() || $this->isUserCurrentStoreroom) {
					
			}
			else {
				$storeroom_id = 0;
				if(isset($this->request->pass['id']))
					$storeroom_id = $this->request->pass['id'];
				 
				/*
				 * ottengo il produttore
				 */
				$conditions = ['Storeroom.id' => $storeroom_id];
				$results = current($this->Storeroom->getArticlesToStoreroom($this->user, $conditions));

				/*
				 * ctrl che l'utente sia referente del produttore
				*/				
				$arrayACLsuppliersIdsOrganization = explode(",", $this->user->get('ACLsuppliersIdsOrganization'));
				if(!in_array($results['SuppliersOrganization']['id'],$arrayACLsuppliersIdsOrganization)) {
					$this->Session->setFlash(__('msg_not_permission'));
					$this->myRedirect(Configure::read('routes_msg_stop'));
				}
			}
		}
		/* ctrl ACL */		
	}

	/* 
	 * cosa e' stato acquistato	
	 * id, da DELETE articolo da togliere a gasista e rimettere in dispensa
	 */
	public function admin_index_to_users($id=0) {
		
		$debug = false;
		
		if(!empty($this->request->params['pass']['id'])) {
			/* 
			 * Dati articolo in Storeroom
			 */
			$this->Storeroom->id = $this->request->params['pass']['id'];
			if (!$this->Storeroom->exists($this->user->organization['Organization']['id'])) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
	
			$options = [];
			$options['conditions'] = ['Storeroom.organization_id' => $this->user->organization['Organization']['id'],
									  'Storeroom.id' => $id];
			$options['order'] = ['Storeroom.data ASC'];
			$options['recursive'] = 1;
	
			$this->Storeroom->unbindModel(['belongsTo' => ['Article', 'SuppliersOrganization', 'User']]);
			
			$storeroomOld = $this->Storeroom->find('first', $options);
			if($storeroomOld['Delivery']['isToStoreroomPay']=='Y') {
				$this->Session->setFlash(__('StoreroomArticleInRequestPayment'));
				$this->myRedirect(['action' => 'index_to_users']);
			}
			 					
			$storeroomOrigine['Storeroom'] = $storeroomOld['Storeroom'];
			$storeroomOrigine['Storeroom']['qta'] = 0;
				
			$storeroomDestinazione['Storeroom'] = $storeroomOld['Storeroom'];
			$storeroomDestinazione['Storeroom']['id'] = 0; // verifica dopo se insert o update
			$storeroomDestinazione['Storeroom']['user_id'] = $this->storeroomUser['User']['id']; // dispensa
			$storeroomDestinazione['Storeroom']['delivery_id'] = 0;
			$storeroomDestinazione['Storeroom']['qta'] = $storeroomOld['Storeroom']['qta'];			
			
			$this->_storeroom_management($id,$storeroomOrigine,$storeroomDestinazione, $debug);

			$this->Session->setFlash(__('UserToStoreroom has been saved'));
			$this->myRedirect(['action' => 'index_to_users', 'id' => 0]);
		
		} // end if(!empty($this->request->data['id']))
		
		$FilterStoreroomDeliveryId = null;
		$FilterStoreroomGroupBy = null; 
		
		/*
		 * creo elenco consegne per filtro
		 */
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$conditionsDeliveries= [];
 		$conditionsDeliveries['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
								  'Delivery.isVisibleBackOffice' => 'Y',
								  'Delivery.isToStoreroom' => 'Y',
								  'Delivery.sys'=> 'N',
								  'Delivery.stato_elaborazione' => 'OPEN'];
		$conditionsDeliveries['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$conditionsDeliveries['order'] = ['Delivery.data ASC'];
		$conditionsDeliveries['recursive'] = -1;
		$deliveries = $Delivery->find('list', $conditionsDeliveries);
		if(empty($deliveries)) {
			$this->Session->setFlash(__('NotFoundDeliveries'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$this->set(compact('deliveries'));
		
		$conditions = [];
		$conditions[] = ['Storeroom.organization_id' => (int)$this->user->organization['Organization']['id'],
						  'Storeroom.user_id != ' => $this->storeroomUser['User']['id'],
						  'Storeroom.delivery_id > ' => 0,
						  'Storeroom.stato' => 'Y'];
	
		/* recupero dati dalla Session gestita in appController::beforeFilter */ 
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'DeliveryId')) {
			$FilterStoreroomDeliveryId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'DeliveryId');
			$conditions[] = ['Storeroom.delivery_id'=>$FilterStoreroomDeliveryId];
		}
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'GroupBy')) 
			$FilterStoreroomGroupBy = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'GroupBy');
		else
			$FilterStoreroomGroupBy = 'SUPPLIERS';
		
		/* filtro */
		$this->set('FilterStoreroomDeliveryId', $FilterStoreroomDeliveryId);
		$this->set('FilterStoreroomGroupBy', $FilterStoreroomGroupBy);
		$this->set('ArrayFilterStoreroomGroupBy', ['SUPPLIERS' => 'Produttori', 'USERS' => 'Utenti']);
				
		if($FilterStoreroomGroupBy=='SUPPLIERS') 
			$orderBy = ['Storeroom.delivery_id, Article.supplier_organization_id, Storeroom.name'];
		else
		if($FilterStoreroomGroupBy=='USERS') 
			$orderBy = ['Storeroom.delivery_id, '.Configure::read('orderUser').', Storeroom.name'];

		$this->Storeroom->Delivery->unbindModel(['hasMany' => ['Order']]);
		$this->Storeroom->Article->unbindModel(['hasMany' => ['ArticlesOrder']]);
		$this->Storeroom->User->unbindModel(['hasMany' => ['Cart']]);
		$results = $this->Storeroom->find('all', ['conditions' => $conditions, 'order' => $orderBy, 'recursive' => 1]);
		
		self::d([$conditions, $results], $debug);
		
		/*
		* posso modificare l'associazione con l'utente solo i produttori di cui sono referente
		*/
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$arrayACLsuppliersIdsOrganization = explode(",",$this->user->get('ACLsuppliersIdsOrganization'));
		$ii=0;
		$resultsNew = [];
		if(!empty($results))
		foreach ($results as $i => $result) {
		
			if($result['Delivery']['isVisibleBackOffice']=='Y') {
				
				$resultsNew[$ii] = $result;
				
				$conditions = ['SuppliersOrganization.id' => $result['Article']['supplier_organization_id']];
				$suppliersOrganization = $SuppliersOrganization->getSuppliersOrganization($this->user, $conditions);
				$resultsNew[$ii]['SuppliersOrganization'] = current($suppliersOrganization);
					
				if($this->isSuperReferente() ||
					in_array($result['Article']['supplier_organization_id'],$arrayACLsuppliersIdsOrganization))
					$resultsNew[$ii]['SuppliersOrganization']['IsReferente'] = 'Y';
				else
					$resultsNew[$ii]['SuppliersOrganization']['IsReferente'] = 'N';
				
				$ii++;
			}
		}
	
		$this->set('results',$resultsNew);	
	}
	/*
	 * cosa c'e' in dispensa
	 */
	public function admin_index() {

		$debug = false;
	
		App::import('Model', 'Supplier');
		
		$this->_ctrl_data_delete_qta_zero(); 
		
		$user_id = $this->storeroomUser['User']['id'];
		
		$FilterStoreroomSupplierId = null;
		$SqlLimit = 50;
	
		$conditions = [];

		/* recupero dati dalla Session gestita in appController::beforeFilter */
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'SupplierId')) {
			$FilterStoreroomSupplierId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'SupplierId');
			$conditions[] = ['Article.organization_id' => (int)$this->user->organization['Organization']['id'],
							  'SuppliersOrganization.id' => $FilterStoreroomSupplierId,
							  'Article.stato' => 'Y'];
		}
		
		/*
		 * ctrl se non e' ancora stata effettuata una ricerca
		* */
		if(empty($conditions))
			$this->set('iniCallPage', true);
		else
			$this->set('iniCallPage', false);
		
		
		/* filtro */
		$this->set('FilterStoreroomSupplierId', $FilterStoreroomSupplierId);
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => (int)$this->user->organization['Organization']['id'],
						  		 'SuppliersOrganization.stato' => 'Y'];
		if(!$this->isSuperReferente() && !$this->isUserCurrentStoreroom)
			$options['conditions'] += ['SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')'];
		$options['order'] = ['SuppliersOrganization.name'];
		$options['recursive'] = -1;
		$suppliersOrganization = $SuppliersOrganization->find('list', $options);
		$this->set(compact('suppliersOrganization','suppliersOrganization'));

		/* 
		 * Articoli in dispensa
		 */
		$conditions = ['Delivery.id' => 0,  // articoli in dispensa non ancora acquistati
						'User.id' => $user_id];
		if(!empty($FilterStoreroomSupplierId))
			$conditions += ['SuppliersOrganization.id' => $FilterStoreroomSupplierId];
		$orderBy = ['SuppliersOrganization' => 'SuppliersOrganization.name, Article.name'];
		$results = $this->Storeroom->getArticlesToStoreroom($this->user, $conditions, $orderBy, $debug);

		/*
		 * posso associare all'utente solo i produttori di cui sono referente
		 */
		$arrayACLsuppliersIdsOrganization = explode(",",$this->user->get('ACLsuppliersIdsOrganization'));
		foreach ($results as $numResult => $result) {
			if($this->isSuperReferente() || 
			  in_array($result['SuppliersOrganization']['id'],$arrayACLsuppliersIdsOrganization))
				$results[$numResult]['SuppliersOrganization']['IsReferente'] = 'Y';
			else
				$results[$numResult]['SuppliersOrganization']['IsReferente'] = 'N';
			
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
			 * per ogni articolo in dispensa, ctrl cos'e' stato gia' acquistato
			*/ 
			$results[$numResult]['Storeroom']['articlesJustBookeds'] = $this->Storeroom->getArticlesJustBooked($this->user, $this->storeroomUser, $result['Article']['organization_id'], $result['Article']['id']);
				
			// articoli in dispensa da prenotare
			$results[$numResult]['Storeroom']['qtaToBooked'] = $result['Storeroom']['qta'];

			// articoli gia' prenotati
			$qtaJustBooked = 0;
			if(!empty($results[$numResult]['Storeroom']['articlesJustBookeds'])) 
				foreach($results[$numResult]['Storeroom']['articlesJustBookeds'] as $articlesJustBooked)  {
					$qtaJustBooked += $articlesJustBooked['Storeroom']['qta'];
			}
			$results[$numResult]['Storeroom']['qtaJustBooked'] = $qtaJustBooked;

			//articoli totali
			$results[$numResult]['Storeroom']['qtaTot'] = ($result['Storeroom']['qta'] + $qtaJustBooked);
		}
		
		$this->set(compact('results',$results));
	}	
	
	public function index() {
		
		App::import('Model', 'Supplier');

		$user_id = $this->user->get('id');
		if($user_id==0) {
			$this->Session->setFlash(__('msg_not_permission_guest'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		// parametro passato da storeroom_to_user.ctp 
		if(isset($_REQUEST['esito']) && $_REQUEST['esito']=='OK')
			$this->Session->setFlash(__('storeroomToUser has been saved'));

		$FilterStoreroomSupplierId = null;
		$SqlLimit = 20;

		/* filtro */
		$this->set('FilterStoreroomSupplierId', $FilterStoreroomSupplierId);
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => (int)$this->user->organization['Organization']['id'],
								  'SuppliersOrganization.stato' => 'Y'];
		$options['order'] = ['SuppliersOrganization.name'];
		$options['recursive'] = -1;
		$suppliersOrganizations = $SuppliersOrganization->find('list', $options);
				
		$this->set(compact('suppliersOrganizations','suppliersOrganizations'));

		/* 
		 * Articoli in dispensa
		 */
		$conditions = ['Delivery.id' => 0,  // articoli in dispensa non ancora acquistati
						'User.id' => $this->storeroomUser['User']['id'],
						'SuppliersOrganization.id' => $FilterStoreroomSupplierId];
		$orderBy = ['SuppliersOrganization' => 'SuppliersOrganization.name, Article.name'];
		$results = $this->Storeroom->getArticlesToStoreroom($this->user, $conditions, $orderBy);

		foreach ($results as $numResult => $result) {
			
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
		}
		
		$this->set(compact('results',$results));

		$this->layout = 'default_front_end';
	}

	/* 
	 * dal carrello modifico la dispensa
	 *
	 * associa un articolo nel carrello della dispensa all'utente
	 * storeroom.delivery_id  = 0
	 * storeroom.delivery_id valorizzato
	 * 
	 * per DEBUG, in Storerooms::storeroom_to_user.ctp commentare jQuery('#ajaxContent').load(url);
	 */
	public function storeroomToUser($id) {
		            
		if($this->user->get('id')==0) {
			$this->Session->setFlash(__('msg_not_permission_guest'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$this->Storeroom->id = $id;
		if (!$this->Storeroom->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		/* 
		 * qui  ho $_REQUEST e non $this->request->params['pass']
		 * perche' la chiamata viene fatta da ajax e non con il submit del form
		 * */
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$conditions = ['Storeroom.id' => $id];
			$storeroomOld = current($this->Storeroom->getArticlesToStoreroom($this->user, $conditions));

			$storeroomOrigine['Storeroom'] = $storeroomOld['Storeroom'];
			$storeroomOrigine['Storeroom']['user_id'] = $this->storeroomUser['User']['id']; // dispensa
			$storeroomOrigine['Storeroom']['qta'] = ($storeroomOld['Storeroom']['qta'] - $_REQUEST['qta']);
			
			$storeroomDestinazione['Storeroom'] = $storeroomOld['Storeroom'];
			$storeroomDestinazione['Storeroom']['id'] = 0; // verifica dopo se insert o update 
			$storeroomDestinazione['Storeroom']['user_id'] = $this->user->get('id'); // utente da sessione
			$storeroomDestinazione['Storeroom']['delivery_id'] = $_REQUEST['delivery_id']; // delivery scelta dal menu tendina
			$storeroomDestinazione['Storeroom']['qta'] = $_REQUEST['qta'];
				
			$this->_storeroom_management($id,$storeroomOrigine,$storeroomDestinazione);

			/*
			 * il redirect su Storeroom::index c'e' come callback nella view
			 */ 			
		}

		$user_id = $this->storeroomUser['User']['id'];
		$this->_populate_to_view($id, $user_id, 'FE');
		
		$this->layout = 'ajax';
	}
	
	/* 
	 * Associa gli articoli della dispensa all'utente
	 */
	public function admin_storeroomToUser($id) {
		
		$debug = false;
		
		$this->Storeroom->id = $id;
		if (!$this->Storeroom->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {

			if($this->request->data['Storeroom']['user_id']==$this->storeroomUser['User']['id']) {
				$this->Session->setFlash(__('StoreroomErrorUsedStoreroomUser'));
				$this->myRedirect(['action' => 'index']);
			}
			
			$conditions = ['User.id' => $this->storeroomUser['User']['id'],
							'Storeroom.id' => $id];
			$storeroomOld = current($this->Storeroom->getArticlesToStoreroom($this->user, $conditions));
			unset($storeroomOld['User']);
			unset($storeroomOld['SuppliersOrganization']);
				
			$storeroomOrigine = $storeroomOld;
			$storeroomOrigine['Storeroom']['user_id'] = $this->storeroomUser['User']['id']; // dispensa
			$storeroomOrigine['Storeroom']['delivery_id'] = 0;
			$storeroomOrigine['Storeroom']['qta'] = ($storeroomOld['Storeroom']['qta'] - $this->request->data['Storeroom']['qta']);
				
			$storeroomDestinazione = $storeroomOld;
			$storeroomDestinazione['Storeroom']['id'] = 0; // verifica dopo se insert o update
			$storeroomDestinazione['Storeroom']['user_id'] = $this->request->data['Storeroom']['user_id']; // utente da scelto dal menu tendina
			$storeroomDestinazione['Storeroom']['delivery_id'] = $this->request->data['Storeroom']['delivery_id']; // delivery scelta dal menu tendina
			$storeroomDestinazione['Storeroom']['qta'] = $this->request->data['Storeroom']['qta'];
			
			$this->_storeroom_management($id,$storeroomOrigine,$storeroomDestinazione,$debug); 
			
			$this->Session->setFlash(__('The storeroom has been saved'));
			$this->myRedirect(['action' => 'index']);
		}
		
		App::import('Model', 'User');
		$User = new User;

		$conditions = [];
		$conditions['UserGroupMap.group_id'] = Configure::read('group_id_user');
		$conditions['UserGroupMap.group_id NOT IN'] = "(".Configure::read('group_id_storeroom').")";
		$users = $User->getUsersList($this->user, $conditions);
		$this->set(compact('users'));

		$user_id = $this->storeroomUser['User']['id'];
		$this->_populate_to_view($id, $user_id, 'BO');
	}
	
	/*
	 * dal carrello dell'utente si effettua modifica => una qta torna in dispensa 
	 * storeroom.order_id = 0
	 * storeroom.delivery_id valorizzato
	 * 
 	 * per DEBUG, in Storerooms::user_to_storeroom.ctp commentare jQuery('#ajaxContent').load(url);
	 */
	public function userToStoreroom($id) {
		
		if($this->user->get('id')==0)  {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		
		$this->Storeroom->id = $id;
		if (!$this->Storeroom->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	
			
		/*
		 * qui ho $_REQUEST e non $this->request->params['pass']
		* perche' la chiamata viene fatta da ajax e non con il submit del form
		* */
		if ($this->request->is('post') || $this->request->is('put')) {			

			$this->Storeroom->unbindModel(['belongsTo' => ['Article','Delivery','User']]);
			$storeroomOld = $this->Storeroom->read($this->user->organization['Organization']['id'], null, $id);
				
			$storeroomOrigine['Storeroom'] = $storeroomOld['Storeroom'];
			$storeroomOrigine['Storeroom']['user_id'] = $this->user->get('id'); // sessione
			$storeroomOrigine['Storeroom']['delivery_id'] = $_REQUEST['delivery_id'];
			$storeroomOrigine['Storeroom']['qta'] = $_REQUEST['qta'];
			
			$storeroomDestinazione['Storeroom'] = $storeroomOld['Storeroom'];
			$storeroomDestinazione['Storeroom']['id'] = 0; // verifica dopo se insert o update
			$storeroomDestinazione['Storeroom']['user_id'] = $this->storeroomUser['User']['id']; // dispensa
			$storeroomDestinazione['Storeroom']['delivery_id'] = 0;
			$storeroomDestinazione['Storeroom']['qta'] = ($storeroomOld['Storeroom']['qta'] - $_REQUEST['qta']);
			
			$this->_storeroom_management($id,$storeroomOrigine,$storeroomDestinazione);
			
			$this->Session->setFlash(__('The storeroom has been saved'));
			$this->myRedirect(['action' => 'index']);
		}
		
		$user_id = $this->user->get('id');
		$this->_populate_to_view($id, $user_id, 'BO');
		
		$this->layout = 'ajax';
	}
	
	/*
	 * call da userToStoreroom()
	 *		   admin_storeroomToUser($id) 
	 * popola ctp con 
	 * 				Deliveries
	 * 				Articoli in dispensa (storeroom)
	 *	$type BO / FE
	 */
	private function _populate_to_view($id, $user_id, $type='BO') {
		
		/*
		 * Consegne per la dispensa
		* */
		if($type=='FE')
			$deliveries = $this->Storeroom->getDeliveriesFE($this->user);
		else
			$deliveries = $this->Storeroom->getDeliveries($this->user);
		if(empty($deliveries)) {
			$this->Session->setFlash(__('NotFoundDeliveries'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	
		$this->set('deliveries', $deliveries);

		/*
		 * Articoli in dispensa
		* */
		$conditions = ['User.id' => $user_id,
						'Storeroom.id' => $id,
						'Storeroom.delivery_id' => 0];
		$this->request->data = current($this->Storeroom->getArticlesToStoreroom($this->user, $conditions));
	}
	
	/*
	 * se da storeroom (storeroomOriginale) a utente (storeroomDestinazione)
	 * 			aggiorno (update o delete) con request storeroom
	 * 			aggiorno (insert o update) a nuovo utente
	 * se da utente (storeroomOriginale) a storeroom (storeroomDestinazione) 
	 * 			aggiorno (update o delete) con request utente
	 * 			aggiorno (insert o update) a storeroom 
	 * 
	 * $storeroomDestinazione lo ricerco: potrebbe esserci (UPDATE/DELETE) o crealo (INSERT)
	 * 
	 * BACK-OFFICE
	 * admin_index
	 *	 	cosa c'e' in Dispensa    -> storeroom TO user con elenco user   $storeroomUser, $user_id
	 * admin_deliveries_index
	 *      cosa e' stato acquistato -> user TO storeroom con elenco user -> $user_id  $storeroomUser
	 * admin_add
	 * 		aggiungi in dispensa -> $storeroomUser
	 * 
	 * FRONT-END 
	 * 		cosa c'e' in Dispensa -> storeroom TO user con Session(user)  $storeroomUser, $user_id
     * carello utente
	 * 		user TO storeroom -> $user_id  $storeroomUser
	 * */
	private function _storeroom_management($id, $storeroomOrigine, $storeroomDestinazione, $debug = false) {
	
		if($debug) {
			echo "<pre>storeroomOriginale ";
			print_r($storeroomOrigine);
			echo "</pre>";
			echo "<pre>storeroomDestinazione ";
			print_r($storeroomDestinazione);
			echo "</pre>";
		}

		/* 
		 * UPDATE/DELETE storeroomOriginale (qta / delivery_id /user_id (o session o select))
		 */
		if($storeroomOrigine['Storeroom']['qta']>0) {
			if($debug) echo "<br />UPDATE/INSERT storeroomOriginale ";
			if(!$debug) {
				$this->Storeroom->create();
				if (!$this->Storeroom->save($storeroomOrigine))
				$this->Session->setFlash(__('The Storeroom could not be saved. Please, try again.'));
			}
		}
		else {
			$this->Storeroom->id = $id;
			if($debug) echo "<br />DELETE storeroomOld prima ctrl exists()";
			if (!$this->Storeroom->exists($this->user->organization['Organization']['id'])) {
				$this->Session->setFlash(__('msg_error_params'));
				if(!$debug) $this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
			if($debug) echo "<br />DELETE storeroomOld ";
			if(!$debug) {
				if (!$this->Storeroom->delete())
				$this->Session->setFlash(__('The Storeroom not deleted'));
			}
		}

		if($debug) echo '<h2>------------------------------------------</h2>';
		/* 
		 * UPDATE/INSERT/DELETE storeroomDestinazione (qta / delivery_id)
		 */
		if($storeroomDestinazione!=null) {  // se arrivo da admin_edit $storeroomDestinazione=null
			$conditions = ['Storeroom.organization_id'=>$storeroomDestinazione['Storeroom']['organization_id'],
							'Storeroom.user_id'=>$storeroomDestinazione['Storeroom']['user_id'],
							'Storeroom.delivery_id'=>$storeroomDestinazione['Storeroom']['delivery_id'],
							'Storeroom.article_id'=>$storeroomDestinazione['Storeroom']['article_id'],
							'Storeroom.stato'=>'Y'];
			$this->Storeroom->unbindModel(['belongsTo' => ['Article','Delivery','User']]);
			$storeroomDestinazioneCtrl = $this->Storeroom->find('first', ['conditions' => $conditions]);
			if($debug) {
				echo "<br />cerco se esiste gia' storeroomDestinazione con conditions ";
				echo "<pre>";
				print_r($conditions);
				echo "</pre>";
			}
			// esiste => update
			if(!empty($storeroomDestinazioneCtrl)) {
				$storeroomDestinazione['Storeroom']['id'] = $storeroomDestinazioneCtrl['Storeroom']['id'];
				$storeroomDestinazione['Storeroom']['qta'] = ($storeroomDestinazione['Storeroom']['qta'] + $storeroomDestinazioneCtrl['Storeroom']['qta']);
				if($debug) echo "<br />storeroomDestinazioneCtrl esiste => UPDATE con nuova QTA ".$storeroomDestinazione['Storeroom']['qta'];
			}
			else {
				if($debug) echo "<br />storeroomDestinazioneCtrl NON esiste => INSERT ";
			}
			
			if($debug) {
				echo "<pre>storeroomDestinazione B ";
				print_r($storeroomDestinazione);
				echo "</pre>";
			}
			if($storeroomDestinazione['Storeroom']['qta']>0) {
				if($debug) echo "<br />UPDATE o INSERT storeroomDestinazione ";
				if(!$debug) {
					$this->Storeroom->create();
					if (!$this->Storeroom->save($storeroomDestinazione))				
						$this->Session->setFlash(__('The Storeroom could not be saved. Please, try again.'));
				}
			}
			else {
				$this->Storeroom->id = $storeroomDestinazione['Storeroom']['id'];
				if($debug) echo "<br />DELETE storeroomDestinazione prima ctrl exists()";
				if ($this->Storeroom->exists($this->user->organization['Organization']['id'])) {
					if($debug) echo "<br />DELETE storeroomDestinazione ";
					else {
						if(!$this->Storeroom->delete())
						$this->Session->setFlash(__('The Storeroom not deleted'));
					}
			    }
			}	
		}	
		
		if($debug) exit();
	}
	
	public function admin_edit($id) {
		
		$debug=false;
		
		$deliveries = $this->Storeroom->getDeliveries($this->user);
		if(empty($deliveries)) {
			$this->Session->setFlash(__('NotFoundDeliveries'));
			$this->myRedirect(['action' => 'index_to_users']);
		}	
		$this->set('deliveries', $deliveries);
		
		/* 
		 * Dati articolo in Storeroom
		 */
		$this->Storeroom->id = $id;
		if (!$this->Storeroom->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$options = [];
		$options['conditions'] = ['Storeroom.organization_id' => $this->user->organization['Organization']['id'],
								   'Storeroom.id' => $id];
		$options['order'] = ['Storeroom.data ASC'];
		$options['recursive'] = 1;

		$results = $this->Storeroom->find('first', $options);
		if($results['Delivery']['isToStoreroomPay']=='Y') {
			$this->Session->setFlash(__('StoreroomArticleInRequestPayment'));
			$this->myRedirect(['action' => 'index_to_users']);
		}
			 
		if ($this->request->is('post') || $this->request->is('put')) {

			if($debug) {
				echo "<pre>Storerooom::edit request->data \n ";
				print_r($this->request->data);
				echo "</pre>";
			}
			
			$this->Storeroom->unbindModel(['belongsTo' => ['Article','Delivery','User']]);
			$storeroomOld = $this->Storeroom->read($this->user->organization['Organization']['id'], null, $id);
			unset($storeroomOld['User']);
			unset($storeroomOld['SuppliersOrganization']);
			
			$storeroomOrigine['Storeroom'] = $storeroomOld['Storeroom'];
			$storeroomOrigine['Storeroom']['user_id'] = $this->request->data['Storeroom']['user_id']; // utente da scelto dal menu tendina
			$storeroomOrigine['Storeroom']['delivery_id'] = $this->request->data['Storeroom']['delivery_id']; // delivery scelta dal menu tendina
			$storeroomOrigine['Storeroom']['qta'] = $this->request->data['Storeroom']['qta'];
				
			$storeroomDestinazione['Storeroom'] = $storeroomOld['Storeroom'];
			$storeroomDestinazione['Storeroom']['id'] = 0; // verifica dopo se insert o update
			$storeroomDestinazione['Storeroom']['user_id'] = $this->storeroomUser['User']['id']; // dispensa
			$storeroomDestinazione['Storeroom']['delivery_id'] = 0;
			$storeroomDestinazione['Storeroom']['qta'] = ($storeroomOld['Storeroom']['qta'] - $this->request->data['Storeroom']['qta']);			
			
			$this->_storeroom_management($id,$storeroomOrigine,$storeroomDestinazione, $debug);

			$this->Session->setFlash(__('The storeroom has been saved'));
			$this->myRedirect(['action' => 'index_to_users']);
		}
		
		$this->request->data = $results;
	}
	
	/* 
	 * aggiunge articoli di un produttore alla dispensa
	 * storerooms.id_order = 0 perche' la dispensa non e' legata all'ordine
	 *
	 * $storeroom_id / $supplier_organization_id valorizzati se chiamato da admin_index per modifica
	 */
	public function admin_add($storeroom_id=0, $supplier_organization_id=0) {
		
		$debug = false;
		
		if ($this->request->is('post')) {

			$msg = "";
			/*
			 * tratto gli articoli da inserire
			 * */
			if(isset($this->request->data['Article'])) {
				
				App::import('Model', 'Article');
				self::d($this->request->data, $debug);
				$supplier_organization_id = $this->request->data['supplier_organization_id'];
				$storeroom_id = $this->request->data['storeroom_id'];
				 
				foreach($this->request->data['Article'] as $key => $data) {
					
					if(!empty($data['Qta']) && is_numeric($data['Qta'])) {
						
						self::d('Tratto article_id '.$key.' del supplier_organization_id '.$supplier_organization_id.' con quantita '.$data['Qta'], $debug);
						
						$article_id = $key;
						
						/*
						 * ctrl se l'articolo associato all'utente non esiste gia' in dispensa
						 * 		se SI possibile refresh della pagina
						 * */
						$conditions = ['User.id' => $this->storeroomUser['User']['id'],
										'Storeroom.delivery_id' => 0,
										'SuppliersOrganization.id' => $supplier_organization_id,
										'Article.id' => $article_id]; 
						$results = $this->Storeroom->getArticlesToStoreroom($this->user, $conditions, null, $debug);
						
						/*
						 * lo inserisco in dispensa se non estiste gia'
						 * */
						if(empty($results))  {		
							$Article = new Article;
							
							$Article->unbindModel(['belongsTo' => ['CategoriesArticle']]);
							$Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
							$Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
							$Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesArticlesType']]);			
							
							if (!$Article->exists($this->user->organization['Organization']['id'], $article_id)) {
								$this->Session->setFlash(__('msg_error_params'));
								$this->myRedirect(Configure::read('routes_msg_exclamation'));
							}
							$options = [];
							$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
													  'SuppliersOrganization.id' => $supplier_organization_id,
													  'Article.id' => $article_id];
							$options['recursive'] = 0;
							$article = $Article->find('first', $options);
							self::d($options, $debug);
							self::d($article, $debug);
							
							$storeroom['Storeroom']['user_id'] = $this->storeroomUser['User']['id'];
							$storeroom['Storeroom']['delivery_id'] = 0;
							$storeroom['Storeroom']['article_id'] = $article_id;
							$storeroom['Storeroom']['article_organization_id'] = $article['Article']['organization_id'];
							$storeroom['Storeroom']['name'] = $article['Article']['name'];
							$storeroom['Storeroom']['qta'] = $data['Qta'];
							$storeroom['Storeroom']['prezzo'] = $article['Article']['prezzo'];
							$storeroom['Storeroom']['organization_id'] = (int)$this->user->organization['Organization']['id'];
							$storeroom['Storeroom']['stato'] = 'Y';
								
							self::d($storeroom, $debug);
				
							$this->Storeroom->create();
							if (!$this->Storeroom->save($storeroom)) {
								$msg .= "<br />Articolo ".$article['Article']['name']." ($article_id) non inserito in dispensa!";
							}
						} // if(empty($results))  	
					} // enf if(!empty($data['Qta']))
					else
						self::d('Non tratto article_id '.$key.' del supplier_organization_id '.$supplier_organization_id.' perche quantita '.$data['Qta'], $debug);
				} // end foreach
			} // end if(isset($this->request->data['Article'])) 
		
			
			
			/*
			 * tratto gli articoli gia' inseriti in dispensa
			* */
			if(isset($this->request->data['Storeroom'])) {
				foreach($this->request->data['Storeroom'] as $key => $data) {
					$storeroom_id = $key;

					// DELETE da dispensa
					if($data['Qta']=='0' && is_numeric($data['Qta'])) {
						$this->Storeroom->id = $storeroom_id;
						if (!$this->Storeroom->exists($this->user->organization['Organization']['id'])) {
							$this->Session->setFlash(__('msg_error_params'));
							$this->myRedirect(Configure::read('routes_msg_exclamation'));
						}
						if (!$this->Storeroom->delete()) 
							$msg .= "<br />Articolo in dispensa ($storeroom_id) non cancellato!";
					}
					else // UDPATE (solo se modificato)
					if(!empty($data['Qta']) && is_numeric($data['Qta'])) { 
						$storeroom['Storeroom']['id'] = $storeroom_id;
						$storeroom['Storeroom']['qta'] = $data['Qta'];
						$this->Storeroom->create();
						if (!$this->Storeroom->save($storeroom)) 
							$msg .= "<br />Articolo ".$storeroom['Storeroom']['name']." in dispensa ($storeroom_id) non salvato!";
					}
				}
			} // end if(isset($this->request->data['Storeroom']))

			if(!empty($msg)) 
				$this->Session->setFlash($msg);
			else		
				$this->Session->setFlash(__('The articles order has been saved'));
			
			if(!$debug) $this->myRedirect(['action' => 'index']);
		}
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => (int)$this->user->organization['Organization']['id'],
								   'SuppliersOrganization.stato' => 'Y'];
		if(!$this->isSuperReferente() && !$this->isUserCurrentStoreroom)
			$options['conditions'] += ['SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')'];
		
		$options['order'] = ['SuppliersOrganization.name'];
		$options['recursive'] = -1;
		$suppliersOrganization = $SuppliersOrganization->find('list', $options);
		
		$this->set(compact('suppliersOrganization'));
		
		$this->set(compact('storeroom_id', 'supplier_organization_id'));
	}
	
	/*
	* $storeroom_id se chiamato da admin_index per modifica
	*/		
	public function admin_add_list_articles($supplier_organization_id, $storeroom_id=0) {
			
		$debug = false;
		
		$this->ctrlHttpReferer();
		
		if($supplier_organization_id==null)  {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		/*
		 * Articoli gia' in dispensa
		* */
		$conditions = ['User.id' => $this->storeroomUser['User']['id'],
						'Storeroom.delivery_id' => 0,
						'SuppliersOrganization.id' => $supplier_organization_id];
		$results = $this->Storeroom->getArticlesToStoreroom($this->user, $conditions, null, $debug);
		$this->set('results', $results);
		
		/*
		 * Articles, solo quelli non in Dispensa
		* */
		$article_id_da_escludere = [];
		if(!empty($results)) {
			foreach($results as $result)
				array_push($article_id_da_escludere, $result['Article']['id']);
		}
		
		$this->Storeroom->Article->unbindModel(['belongsTo' => ['CategoriesArticle']]);
		$this->Storeroom->Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$this->Storeroom->Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
		$this->Storeroom->Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesArticlesType']]);			
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								  'SuppliersOrganization.id'=> $supplier_organization_id,
								  'Article.stato' => 'Y']; 
		if(!empty($article_id_da_escludere))							   
			$options['conditions'] += ["NOT" => ["Article.id" => $article_id_da_escludere]];
		$options['order'] = ['Article.name'];
		$options['recursive'] = 0;
		$articles = $this->Storeroom->Article->find('all', $options);
		self::d([$options, $articles], $debug);
		$this->set('articles', $articles);

		$this->set('storeroom_id', $storeroom_id);
		 
	    /*
	     * se non trovo articoli ctrl se non e' un produttore DES e 
	     *  cerco eventuali articoli del Gas titolare 
	     */
	     $isSupplierOrganizationDesTitolare = false;
	     $ownOrganizationResults= [];
	    if(empty($results) && empty($articles) && $this->user->organization['Organization']['hasDes']=='Y') {
	    
			App::import('Model', 'DesSupplier');
	  		$DesSupplier = new DesSupplier();
	    	
	    	$desSupplierResults = $DesSupplier->getDesSupplierTitolare($this->user, $supplier_organization_id, $debug);

	    	$own_organization_id = $desSupplierResults['DesSupplier']['own_organization_id'];
	    	$ownOrganizationResults= $desSupplierResults;
		    	
		    if(!empty($own_organization_id)) { 
			    $this->set('isSupplierOrganizationDesTitolare', $isSupplierOrganizationDesTitolare);
			    $this->set('ownOrganizationResults', $ownOrganizationResults);
			}
		}	 
		
		$this->layout = 'ajax';		
	}	
	
	/*
	 * elenco degli articoli acquistati da portare in dispensa UtilsCron::articlesFromCartToStoreroom
	 * se Order.state_code = PROCESSED-POST-DELIVERY / INCOMING-ORDER / PROCESSED-ON-DELIVERY
	 */
	public function admin_carts_to_storeroom() {
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$conditions = ['Cart.user_id' => $this->storeroomUser['User']['id'],
						'Cart.order_id' => $this->order_id,
						// 'Cart.inStoreroom' => 'N'
						];		 
		$results = $ArticlesOrder->getArticoliDellUtenteInOrdine($this->user, $conditions);
		$this->set('results', $results);
		
        /*
         * Order
         * */
        App::import('Model', 'Order');
        $Order = new Order;

        $options = [];
        $options['conditions'] = ['Order.organization_id' => (int) $this->user->organization['Organization']['id'],
									'Order.isVisibleBackOffice' => 'Y',
									'Order.id' => $this->order_id];
        $order = $Order->find('first', $options);
		$this->set('order', $order);
	}
	
	
	/*
	 * report stampe 
	 */
	public function export($doc_formato) {
		$this->_export($doc_formato);
	}
	
	public function admin_export($doc_formato) {
		$this->_export($doc_formato);
	}

	public function export_current_deliveries($doc_formato) { // solo con prenotazioni di consegne correnti o future
		$this->_export($doc_formato, 'CURRENT_DELIVERIES');
	}
	
	public function admin_export_current_deliveries($doc_formato) { // solo con prenotazioni di consegne correnti o future
		$this->_export($doc_formato, 'CURRENT_DELIVERIES');
	}
	
	public function exportAll($doc_formato) {
		$this->_export($doc_formato, 'ALL');
	}
	
	public function admin_exportAll($doc_formato) {
		$this->_export($doc_formato, 'ALL');
	}

	public function exportBooking($delivery_id=0, $doc_formato) {
		$this->_exportBooking($delivery_id, $doc_formato);
	}
	
	public function admin_exportBooking($delivery_id=0, $doc_formato) {
		$this->_exportBooking($delivery_id, $doc_formato);
	}
	
	/*
	 * type ALL, 		    tutti gli articoli, in dispensa e prenotati
	 *      ONLY_STOREROOM, solo in dispensa
	 *		CURRENT_DELIVERIES, escludo le consegne chiuse
	 */
	private function _export($doc_formato, $type='ONLY_STOREROOM') {
		
		$debug = false; // sotto filtro per article.id
		
        if ($doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		App::import('Model', 'Supplier');

		/* 
		 * Articoli in dispensa, nessun filtro, dopo filtro quelli della sola consegna
		 */
		 $conditions = []; 
		if($debug)
			$conditions += ['Article.id' => 863];		 
		
		$orderBy = ['SuppliersOrganization' => 'SuppliersOrganization.name, Article.name'];
		$results = $this->Storeroom->getArticlesToStoreroom($this->user, $conditions, $orderBy, $debug);
		
		$newResults = [];
		$i=0;
		foreach($results as $numResult => $result) {

			$newResults[$result['Article']['id']]['Article'] = $result['Article'];
			$newResults[$result['Article']['id']]['SuppliersOrganization'] = $result['SuppliersOrganization'];
			$newResults[$result['Article']['id']]['Delivery'] = $result['Delivery'];
			$newResults[$result['Article']['id']]['User'] = $result['User'];
			$newResults[$result['Article']['id']]['Storeroom']['id'] = $result['Storeroom']['id'];
			$newResults[$result['Article']['id']]['Storeroom']['organization_id'] = $result['Storeroom']['organization_id'];
			$newResults[$result['Article']['id']]['Storeroom']['delivery_id'] = $result['Storeroom']['delivery_id'];
			$newResults[$result['Article']['id']]['Storeroom']['user_id'] = $result['Storeroom']['user_id'];
			$newResults[$result['Article']['id']]['Storeroom']['article_id'] = $result['Storeroom']['article_id'];
			$newResults[$result['Article']['id']]['Storeroom']['article_organization_id'] = $result['Storeroom']['article_organization_id'];
			$newResults[$result['Article']['id']]['Storeroom']['name'] = $result['Storeroom']['name'];
			$newResults[$result['Article']['id']]['Storeroom']['qta'] = $result['Storeroom']['qta'];
			$newResults[$result['Article']['id']]['Storeroom']['prezzo'] = $result['Storeroom']['prezzo'];
			$newResults[$result['Article']['id']]['Storeroom']['stato'] = $result['Storeroom']['stato'];
			$newResults[$result['Article']['id']]['Storeroom']['prezzo_db'] = $result['Storeroom']['prezzo_db'];
			$newResults[$result['Article']['id']]['Storeroom']['importo'] = $result['Storeroom']['importo'];
			$newResults[$result['Article']['id']]['Storeroom']['created'] = $result['Storeroom']['created'];
            
			/*
			 * per ogni articolo in dispensa, ctrl cos'e' stato gia' acquistato
			*/ 
			$newResults[$result['Article']['id']]['MyStoreroom']['articlesJustBookeds'] = $this->Storeroom->getArticlesJustBooked($this->user, $this->storeroomUser, $result['Article']['organization_id'], $result['Article']['id']);
			if($debug) {
				echo "<pre>Storeroom::articlesJustBookeds \n";
				print_r($newResults[$result['Article']['id']]['MyStoreroom']['articlesJustBookeds']);
				echo "</pre>";
			}
				
			// articoli in dispensa da prenotare
			if($result['Storeroom']['user_id']==$this->storeroomUser['User']['id'])
				$newResults[$result['Article']['id']]['MyStoreroom']['qtaToBooked'] += $result['Storeroom']['qta'];
			else {
				if(!isset($newResults[$result['Article']['id']]['MyStoreroom']['qtaToBooked']))
					$newResults[$result['Article']['id']]['MyStoreroom']['qtaToBooked'] = 0;
			}
			if($debug)
				echo '<br />Article.id '.$result['Article']['id'].' - qtaToBooked '.$newResults[$result['Article']['id']]['MyStoreroom']['qtaToBooked'];
			
			// articoli gia' prenotati
			/*
			$qtaJustBooked = 0;
			if(!empty($newResults[$result['Article']['id']]['MyStoreroom']['articlesJustBookeds'])) 
				foreach($newResults[$result['Article']['id']]['MyStoreroom']['articlesJustBookeds'] as $articlesJustBooked)  {
					if($articlesJustBooked['Storeroom']['user_id']!=$this->storeroomUser['User']['id'])
						$qtaJustBooked += $articlesJustBooked['Storeroom']['qta'];
			}
			*/
			if($result['Storeroom']['user_id']!=$this->storeroomUser['User']['id'])
				$newResults[$result['Article']['id']]['MyStoreroom']['qtaJustBooked'] += $result['Storeroom']['qta'];
			else{
				if(!isset($newResults[$result['Article']['id']]['MyStoreroom']['qtaJustBooked']))
					$newResults[$result['Article']['id']]['MyStoreroom']['qtaJustBooked'] = 0;
			}
			if($debug)
				echo '<br />Article.id '.$result['Article']['id'].' - qtaJustBooked '.$newResults[$result['Article']['id']]['MyStoreroom']['qtaJustBooked'];
							
			//articoli totali
			$newResults[$result['Article']['id']]['MyStoreroom']['qtaTot'] += $result['Storeroom']['qta'];
			if($debug)
				echo '<br />Article.id '.$result['Article']['id'].' - qtaTot '.$newResults[$result['Article']['id']]['MyStoreroom']['qtaTot'];
		}
		
		/*
		 * prendo solo gli articolo din dispensa, quelli che non sono stati tutti prenotati
		 */
		if($type=='ONLY_STOREROOM' || $type=='CURRENT_DELIVERIES') {
			foreach($newResults as $article_id => $newResult) {
				if($newResult['MyStoreroom']['qtaTot']==$newResult['MyStoreroom']['qtaJustBooked'])
					unset($newResults[$article_id]);
			}
		}
			
		if($debug) {
			echo "<pre>Storeroom::_export type $type \n";
			print_r($newResults);
			echo "</pre>";
		}
		
		$this->set('results', $newResults);
		$this->set('type' ,$type);
		
		if($type=='ALL') {
			$fileData['fileTitle'] = "Dispensa, articoli da prenotare e prenotati";
			$fileData['fileName'] = "dispensa_articoli_da_prenotare_e_prenotati";
		}
		else {
			$fileData['fileTitle'] = "Articoli in dispensa";
			$fileData['fileName'] = "articoli_in_dispensa";
		}
		
		$this->set('fileData', $fileData);
		$this->set('organization', $this->user->organization);
		
		if($type=='CURRENT_DELIVERIES') {
			switch ($doc_formato) {
				case 'PREVIEW':
					$this->layout = 'ajax';
					$this->render('export_current_deliveries');
					break;
				case 'PDF':
					$this->layout = 'pdf';
					$this->render('export_current_deliveries');
					break;
				case 'CSV':
				case 'EXCEL':
					$this->layout = 'excel';
					$this->render('export_current_deliveries_excel');
				break;
			}			
		}
		else {
			switch ($doc_formato) {
				case 'PREVIEW':
					$this->layout = 'ajax';
					$this->render('export');
					break;
				case 'PDF':
					$this->layout = 'pdf';
					$this->render('export');
					break;
				case 'CSV':
				case 'EXCEL':
					$this->layout = 'excel';
					$this->render('export_excel');
				break;
			}
		}
	}

	private function _exportBooking($delivery_id, $doc_formato) {
		
        if ($doc_formato == null || empty($delivery_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		App::import('Model', 'Supplier');

		App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        $options = [];
        $options['conditions'] = ['Delivery.organization_id' => (int) $this->user->organization['Organization']['id'],
									'Delivery.isVisibleBackOffice' => 'Y',
									'Delivery.id' => $delivery_id];
        $delivery = $Delivery->find('first', $options);
		$this->set('delivery', $delivery);
		
		/* 
		 * Articoli in dispensa
		 */
		$conditions = ['Delivery.id' => $delivery_id,  // articoli in dispensa non ancora acquistati
		                // 'SuppliersOrganization.id' => $FilterStoreroomSupplierId
						];
		$orderBy = ['SuppliersOrganization' => 'SuppliersOrganization.name, Article.name'];
		$results = $this->Storeroom->getArticlesToStoreroom($this->user, $conditions, $orderBy);
		
		foreach($results as $numResult => $result) {

			/*
			 * per ogni articolo in dispensa, ctrl cos'e' stato gia' acquistato
			*/ 
			$results[$numResult]['Storeroom']['articlesJustBookeds'] = $this->Storeroom->getArticlesJustBooked($this->user, $this->storeroomUser, $result['Article']['organization_id'], $result['Article']['id'], $result['User']['id']);
				
			// articoli in dispensa da prenotare
			$results[$numResult]['Storeroom']['qtaToBooked'] = $result['Storeroom']['qta'];

			// articoli gia' prenotati
			$qtaJustBooked = 0;
			if(!empty($results[$numResult]['Storeroom']['articlesJustBookeds'])) 
				foreach($results[$numResult]['Storeroom']['articlesJustBookeds'] as $articlesJustBooked)  {
					$qtaJustBooked += $articlesJustBooked['Storeroom']['qta'];
			}
			$results[$numResult]['Storeroom']['qtaJustBooked'] = $qtaJustBooked;

			//articoli totali
			$results[$numResult]['Storeroom']['qtaTot'] = ($result['Storeroom']['qta'] + $qtaJustBooked);
		}
		
		$this->set(compact('results',$results));
		
		$fileData['fileTitle'] = "Dispensa, articoli prenotati";
		$fileData['fileName'] = "dispensa_articoli_prenotati";
		$this->set('fileData', $fileData);
		$this->set('organization', $this->user->organization);

        switch ($doc_formato) {
            case 'PREVIEW':
                $this->layout = 'ajax';
				$this->render('export_booking');
                break;
            case 'PDF':
                $this->layout = 'pdf';
                $this->render('export_booking');
                break;
            case 'CSV':
            case 'EXCEL':
                $this->layout = 'excel';
				$this->render('export_booking_excel');
			break;
        }
		
	}
	
	/*
	 * cancella articoli in dispensa non dovrebbe mai capitare, se zero vengono cancellati
	 */
	private function _ctrl_data_delete_qta_zero() {
		$sql = "DELETE FROM ".Configure::read('DB.prefix')."storerooms 
				WHERE organization_id = ".(int)$this->user->organization['Organization']['id']." 
				and qta = 0 ";
		$result = $this->Storeroom->query($sql);
	}
}
