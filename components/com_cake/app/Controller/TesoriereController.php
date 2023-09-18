<?php
App::uses('AppController', 'Controller');

class TesoriereController extends AppController {

	private $isReferenteTesoriere = false;
	public $helpers = ['Html', 'Javascript', 'Ajax', 'Tabs', 'RowEcomm'];
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		$isReferenteTesoriere = false;
		if(!empty($this->order_id)) {
			/*
			 * ctrl referentTesoriere
			*/
			if($this->isReferentTesoriere())
				$isReferenteTesoriere = true;
			else
				$isReferenteTesoriere = false;
		}
				
		/* ctrl ACL */
		$actionWithPermission = ['admin_index', 'admin_edit', 'admin_delete'];
		if (in_array($this->action, $actionWithPermission)) {
	
			/*
			 * gestione dell'anagrafica dei tesorieri
			 */
			if(!$this->isTesoriereGeneric()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}
		else {
			/*
			 * gestione delle funzioni dei tesorieri
			*/
			if(!$this->isTesoriereGeneric() && !$isReferenteTesoriere) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}			
		}
		$this->set('isReferenteTesoriere', $isReferenteTesoriere);
	}
	
	public function admin_home() {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
								   'Delivery.isVisibleBackOffice' => 'Y',
								   'Delivery.stato_elaborazione' => 'OPEN',
								   'Delivery.sys'=> 'N',
								   'Delivery.type'=> 'GAS', // GAS-GROUP
								   'DATE(Delivery.data) <= CURDATE()'];
		$options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$options['order'] = ['Delivery.data' => 'asc'];
		$options['recursive'] = -1;
		$deliveries = $Delivery->find('list', $options);
		$this->set(compact('deliveries'));
	}
	
	/*
	 * estrai ordini WAIT-PROCESSED-TESORIERE 
	 * azione da WAIT-PROCESSED-TESORIERE (referente) in PROCESSED-TESORIERE (tesoriere)
	 * 		  copia i dati in summary_orders se non ci sono gia' perche' inseriti dal referente
	 * 
	 * se lo richiamo dal menu laterale delivery_id e' valorizzato
	 */ 
	public function admin_orders_get_WAIT_PROCESSED_TESORIERE() {

		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
				
			$order_id_selected = $this->request->data['Tesoriere']['order_id_selected'];				
			if(!empty($order_id_selected)) {

				if(strpos($order_id_selected,',')===false)
					$order_ids[] = $order_id_selected;
				else 
					$order_ids = explode(',',$order_id_selected);
				
				foreach ($order_ids as $order_id) {

					/*
					 * riporto ordinie a 'PROCESSED-TESORIERE' (In carico al tesoriere) => popolo summary_orders
					 * $SummaryPayment->delete_order() aggiorno il totale in SummaryPayment, se il gasista aveva solo quell'ordine SummaryPayment.stato = DAPAGARE
					 * $SummaryOrderLifeCycle->changeRequestPayment($this->user, $order_id, $operation='DELETE', $opts); cancello i pagamenti gia' fatti del tesoriere SummaryOrder.saldato_a = TESORIERE
					 */
					App::import('Model', 'OrderLifeCycle');
					$OrderLifeCycle = new OrderLifeCycle();
					
					$esito = $OrderLifeCycle->stateCodeUpdate($this->user, $order_id, 'PROCESSED-TESORIERE');					
				} // end foreach ($order_ids as $order_id)  ciclo ordini

				/*
				 * invio mail a referenti
				*/
				App::import('Model', 'Order');
				App::import('Model', 'SuppliersOrganizationsReferent');
				App::import('Model', 'Mail');
				
				foreach ($order_ids as $order_id) {
				
					/*
					 * estraggo i referenti
					*/
					$Order = new Order;
						
					$options = [];
					$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'], 'Order.id' => $order_id];
					$options['recursive'] = 0;
					$results = $Order->find('first', $options);
						
					$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
					$conditions = ['User.block' => 0,
									'SuppliersOrganization.id' => $results['Order']['supplier_organization_id']];
					$userResults = $SuppliersOrganizationsReferent->getReferentsCompact($this->user, $conditions);
						
					/*
					 * invio mail a referenti
					*/
					$Mail = new Mail;
						
					$Email = $Mail->getMailSystem($this->user);
				
					$subject_mail = "Ordine di ".$results['SuppliersOrganization']['name']." preso in carico dal tesoriere.";
					$body_mail  = "L'ordine del produttore <b>".$results['SuppliersOrganization']['name']."</b> per la consegna <b>".$results['Delivery']['luogoData']."</b> è stato preso in carico dal <b>tesoriere</b>.";
						
					$Email->subject($subject_mail);
							if(!empty($this->user->organization['Organization']['www']))
								$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
							else
								$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);
				
					foreach ($userResults as $userResult)  {
						$name = $userResult['User']['name'];
						$mail = $userResult['User']['email'];
							
						if(!empty($mail)) {
							$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
							$Email->to($mail);
								
							$Mail->send($Email, $mail, $body_mail, $debug);
						} // end if(!empty($mail))
					} // end foreach ($userResults as $userResult)
				} // end foreach ($order_ids as $order_id)  ciclo ordini
				
				$this->Session->setFlash(__('Orders State Processed Tesoriere'));
				/*
				 * non cambio + la pagina $this->myRedirect(['action' => 'orders_get_PROCESSED_TESORIERE', null, 'delivery_id='.$this->delivery_id]);
				 */
			}
		} // end if ($this->request->is('post') || $this->request->is('put')) 
	
		$this->_getListDeliveries($this->user); 
		
		$this->set('order_state_code_checked','WAIT-PROCESSED-TESORIERE');
		
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToTesoriere($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);
		
		$this->render('admin_orders_get_wait_processed_tesoriere');
	}

	/*
	 * PROCESSED-TESORIERE (In carico al tesoriere) e li porta a TO-REQUEST-PAYMENT (Possibilità di richiederne il pagamento)
	*/	
	public function admin_orders_get_PROCESSED_TESORIERE() {
			
		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$action_submit = $this->request->data['Tesoriere']['action_submit'];
			
			$order_id_selected = $this->request->data['Tesoriere']['order_id_selected'];
			
			if(!empty($order_id_selected)) {
				
				App::import('Model', 'Order');
				$Order = new Order;
				
				$order_ids = explode(',',$order_id_selected);
				
				switch ($action_submit) {
					/*
					 * riporto l'ordine al referente
					 */
					case 'OrdersToPROCESSED_REFERENTE_POST_DELIVERY':			
							foreach ($order_ids as $order_id) {
								
								if($debug) echo '<br />order_id '.$order_id;
								
								$Order->id = $order_id;
								if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
									$this->Session->setFlash(__('msg_error_params'));
									$this->myRedirect(Configure::read('routes_msg_exclamation'));
								}
								$order = $Order->read($order_id, $this->user->organization['Organization']['id']);
								
								self::d($order,$debug);
							
								if($order['Order']['state_code']=='PROCESSED-TESORIERE') {
									
									$state_code_next = '';
									
									/*
									 * ulteriore ctrl
									 * il Organization.payToDelivery puo' essere POST o ON-POST (mai ON)
									 */
									switch($this->user->organization['Template']['payToDelivery']) {
										case 'POST':
											$state_code_next = 'PROCESSED-POST-DELIVERY';
										break;
										case 'ON-POST':
											if($order['Order']['inviato_al_tesoriere_da']=='REFERENTE')
												$state_code_next = 'INCOMING-ORDER';  // merce arrivata
											else
											if($order['Order']['inviato_al_tesoriere_da']=='CASSIERE')	
												$state_code_next = 'PROCESSED-ON-DELIVERY';  // in carico al cassiere durante la consegna			
										break;
									}
									
									if(!empty($state_code_next)) {
										
										/*
										 * aggiorno stato ORDER => pulisco / popolo SummaryOrderLifeCycle
										*/
										App::import('Model', 'OrderLifeCycle');
										$OrderLifeCycle = new OrderLifeCycle();
										
										$esito = $OrderLifeCycle->stateCodeUpdate($this->user, $order, $state_code_next);
									}
								} // end if($order['Order']['state_code']=='PROCESSED-TESORIERE')
							}  // end foreach ($order_ids as $order_id)
							$this->Session->setFlash(__('Orders State Processed Referente Post Delivery'));
					break;
					/*
					 * passo allo stato per richiedere il pagamento dell'ordine
					*/
					case 'OrdersToTO_REQUEST_PAYMENT': 
						App::import('Model', 'Order');
						$Order = new Order;
						
						App::import('Model', 'OrderLifeCycle');
						$OrderLifeCycle = new OrderLifeCycle;
						
						foreach ($order_ids as $order_id) {
			
								$Order->id = $order_id;
								if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
									$this->Session->setFlash(__('msg_error_params'));
									$this->myRedirect(Configure::read('routes_msg_exclamation'));
								}
								$order = $Order->read($order_id, $this->user->organization['Organization']['id']);
								
								if($order['Order']['state_code']=='PROCESSED-TESORIERE') {
									
									$OrderLifeCycle->stateCodeUpdate($this->user, $order_id, 'TO-REQUEST-PAYMENT');
								}
						} // end foreach ($order_ids as $order_id)
						
						$this->Session->setFlash(__('OrderStateCodeUpdateNowRequestPayment'));
						/*
						 * non cambio + la pagina $this->myRedirect(['controller' => 'RequestPayments', 'action' => 'index', null, 'delivery_id='.$this->delivery_id]);
						 */ 
					break;					
				}
			} // end if(!empty($order_id_selected))

		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		$this->_getListDeliveries($this->user); 
		
		$this->set('order_state_code_checked','PROCESSED-TESORIERE');
		
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToTesoriere($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);

		$this->render('admin_orders_get_processed_tesoriere');
	}

	/*
	 * TO-REQUEST-PAYMENT (Possibilità di richiederne il pagamento) per riportarli al referente
	*/	
	public function admin_orders_get_TO_REQUEST_PAYMENT() {
			
		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$action_submit = $this->request->data['Tesoriere']['action_submit'];
			
			$order_id_selected = $this->request->data['Tesoriere']['order_id_selected'];
			
			if(!empty($order_id_selected)) {
				
				App::import('Model', 'Order');
				$Order = new Order;
				
				$order_ids = explode(',',$order_id_selected);
				
				switch ($action_submit) {
					/*
					 * riporto l'ordine al referente
					 */
					case 'OrdersToPROCESSED_REFERENTE_POST_DELIVERY':			
							foreach ($order_ids as $order_id) {
								
								self::d('order_id '.$order_id, $debug);
								
								$Order->id = $order_id;
								if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
									$this->Session->setFlash(__('msg_error_params'));
									$this->myRedirect(Configure::read('routes_msg_exclamation'));
								}
								$order = $Order->read($order_id, $this->user->organization['Organization']['id']);
								
								self::d($order,$debug);
							
								if($order['Order']['state_code']=='TO-REQUEST-PAYMENT') {
									
									$state_code_next = '';
									
									/*
									 * ulteriore ctrl
									 * il Organization.payToDelivery puo' essere POST o ON-POST (mai ON)
									 */
									switch($this->user->organization['Template']['payToDelivery']) {
										case 'POST':
											$state_code_next = 'PROCESSED-POST-DELIVERY';
										break;
										case 'ON-POST':
											if($order['Order']['inviato_al_tesoriere_da']=='REFERENTE')
												$state_code_next = 'INCOMING-ORDER';  // merce arrivata
											else
											if($order['Order']['inviato_al_tesoriere_da']=='CASSIERE')	
												$state_code_next = 'PROCESSED-ON-DELIVERY';  // in carico al cassiere durante la consegna			
										break;
									}
									
									if(!empty($state_code_next)) {
										
										/*
										 * aggiorno stato ORDER => pulisco / popolo SummaryOrderLifeCycle
										*/
										App::import('Model', 'OrderLifeCycle');
										$OrderLifeCycle = new OrderLifeCycle();
										
										$esito = $OrderLifeCycle->stateCodeUpdate($this->user, $order, $state_code_next);
									}
								} // end if($order['Order']['state_code']=='PROCESSED-TESORIERE')
							}  // end foreach ($order_ids as $order_id)
							$this->Session->setFlash(__('Orders State Processed Referente Post Delivery'));
					break;				}
			} // end if(!empty($order_id_selected))

		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		$this->_getListDeliveries($this->user); 
		
		$this->set('order_state_code_checked','TO-REQUEST-PAYMENT');
		
		/*
		 * legenda profilata
		 */
		$group_id = $this->ActionsOrder->getGroupIdToTesoriere($this->user);
		$orderStatesToLegenda = $this->ActionsOrder->getOrderStatesToLegenda($this->user, $group_id);
		$this->set('orderStatesToLegenda', $orderStatesToLegenda);

		$this->render('admin_orders_get_to_request_payment');
	}
	
	/*
	 * elenco degli ordini con lo order_state_code cliccabile (in base a $order_state_code_checked)
	 * */
	public function admin_orders_index($delivery_id=0, $order_state_code_checked) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		App::import('Model', 'SuppliersOrganization');
		
		$Delivery->id = $this->delivery_id;
		if (!$Delivery->exists($Delivery->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		
		$newResults = [];
		/*
		 * metto in testa gli ordini con l'ordine filtrato $order_state_code_checked
		*/
		$Delivery->hasMany['Order']['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
													  'Order.isVisibleBackOffice != ' => 'N',
													  'Order.state_code != ' => 'CREATE-INCOMPLETE',
													  'Order.state_code' => $order_state_code_checked];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		
		$options = [];
		$options['conditions'] = ['Delivery.id' => $this->delivery_id,
								   'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
								   'Delivery.sys'=> 'N',
								   'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$numOrderNewResults = 0;
		$newResults['Delivery'] = $results['Delivery'];
		
		foreach ($results['Order'] as $numOrder => $order) {

			/*
			 * Suppliers, se e' in stato N lo escludo
			* */
			$SuppliersOrganization = new SuppliersOrganization;
			$SuppliersOrganization->unbindModel(['belongsTo' => ['Organization', 'CategoriesSupplier']]);
			$SuppliersOrganization->unbindModel(['hasMany' => ['Article', 'Order', 'SuppliersOrganizationsReferent']]);
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
									  'SuppliersOrganization.id' => $order['supplier_organization_id']];
			$options['recursive'] = 1;
			$SuppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
			if(!empty($SuppliersOrganizationResults)) {
				
				$newResults['Order'][$numOrderNewResults] = $results['Order'][$numOrder];
				
				$newResults['Order'][$numOrderNewResults]['Supplier'] = $SuppliersOrganizationResults['Supplier'];
				$newResults['Order'][$numOrderNewResults]['SuppliersOrganization'] = $SuppliersOrganizationResults['SuppliersOrganization'];
				
				/*
				 * Referents
				*/
				App::import('Model', 'SuppliersOrganizationsReferent');
				$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
							
				$conditions = ['User.block' => 0,
								'SuppliersOrganization.id' => $order['supplier_organization_id']];
				$suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($this->user, $conditions);
				
				if(!empty($suppliersOrganizationsReferent))
					$newResults['Order'][$numOrderNewResults]['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent;
				
				$numOrderNewResults++;
			}
		} // end  foreach ($results['Order'] as $numOrder => $order)
	
		
		/*
		 * metto dopo gli ordini diversi dallo stato filtrato $order_state_code_checked
		*/
		$Delivery->hasMany['Order']['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
													  'Order.isVisibleBackOffice != ' => 'N',
													  'Order.state_code != ' => 'CREATE-INCOMPLETE',
													  'Order.state_code !=' => $order_state_code_checked];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		$options = [];
		$options['conditions'] = ['Delivery.id' => $this->delivery_id,
									'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									'Delivery.sys'=> 'N',
									'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		foreach ($results['Order'] as $numOrder => $order) {

			/*
			 * Supplier, se e' in stato N lo escludo
			*/
			$sql = "SELECT *
					FROM
						".Configure::read('DB.prefix')."suppliers_organizations as SuppliersOrganization
					WHERE
						 stato = 'Y'
						 and organization_id = ".(int)$this->user->organization['Organization']['id']."
						 and id = ".(int)$order['supplier_organization_id'];
			$suppliersOrganization = current($Delivery->query($sql));
			if(!empty($suppliersOrganization)) {
				
				$newResults['Order'][$numOrderNewResults] = $results['Order'][$numOrder];
				
				$newResults['Order'][$numOrderNewResults]['SuppliersOrganization'] = $suppliersOrganization['SuppliersOrganization'];
			
				/*
				 * Referents
				*/
				App::import('Model', 'SuppliersOrganizationsReferent');
				$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
							
				$conditions = ['User.block' => 0,
								'SuppliersOrganization.id' => $order['supplier_organization_id']];
				$suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($this->user, $conditions);
				
				if(!empty($suppliersOrganizationsReferent))
					$newResults['Order'][$numOrderNewResults]['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent;
				
				$numOrderNewResults++;
			}
			
		} // end  foreach ($results['Order'] as $numOrder => $order)
		
		
		/*
		 *  elenco Order.state_code presenti nella lista per legenda
		 */
		App::import('Model', 'Order');
		$Order = new Order;

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
								  'Order.isVisibleBackOffice != ' => 'N',
								  'Order.state_code != ' => 'CREATE-INCOMPLETE',
								  'Order.delivery_id' => $this->delivery_id];
		$options['order'] = ['Order.state_code'];
		$options['group'] = ['Order.state_code'];
		$options['fields'] = ['Order.state_code'];
		$options['recursive'] = -1;
		
		$orderStateResults = $Order->find('all', $options);
		
		$this->set('orderStateResults', $orderStateResults);
		$this->set('results', $newResults);
		$this->set('order_state_code_checked', $order_state_code_checked);
		
		$this->layout = 'ajax';
	}

	/*
	 *  consegne per richiamare elenco ordini per gestire il pagamento
	 */
	public function admin_pay_suppliers() {
		
		$debug = false;
	
		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;
			
		/* 
		 * se arrivo da Order::index passo la consegna
		 */
		if(isset($this->request->params['pass']['delivery_id']))	
			$delivery_id = $this->request->params['pass']['delivery_id'];
		else
		if(isset($this->request->data['Order']['delivery_id']))
			$delivery_id = $this->request->data['Order']['delivery_id'];
		else 
			$delivery_id = 0;
		$this->set(compact('delivery_id'));		
		
		if ($this->request->is('post') || $this->request->is('put')) {
							
			unset($this->request->data['Order']['delivery_id']);
			
			foreach($this->request->data['Order'] as $order_id => $data) {

				$this->Tesoriere->updateFromModulo($this->user, $order_id, $data, $debug);
		
				/*
				 * se Ordine saldato (Order.tesoriere_stato_pay = Y) passo a Order.state_code succssivo
				 */
				if($OrderLifeCycle->isPaidSupplier($this->user, $order_id, $debug)) {
				 
					$state_code_next = $OrderLifeCycle->stateCodeAfter($this->user, $order_id, 'SUPPLIER-PAID', $debug);
			
					$OrderLifeCycle->stateCodeUpdate($this->user, $order_id, $state_code_next);
				}		
			} // end foreach($this->request->data['Order'] as $order_id => $data)
		
			
		} // end if ($this->request->is('post') || $this->request->is('put')) 
	
		$this->_getListDeliveries($this->user); 	
	}

	/*
	 *  ajax, elenco ordini per gestire il pagamento
	 */	
	public function admin_orders_to_pay_index($delivery_id=0) {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'SuppliersOrganization');
			
		$Delivery->id = $this->delivery_id;
		if (!$Delivery->exists($Delivery->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		$newResults = [];
		/*
		 * metto in testa gli ordini con l'ordine tesoriere_stato_pay = N
		*/
		$Delivery->hasMany['Order']['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
													  'Order.isVisibleBackOffice != ' => 'N',
													  'Order.state_code != ' => 'CREATE-INCOMPLETE',
													  'Order.tesoriere_stato_pay' => 'N'];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		
		$options = [];
		$options['conditions'] = ['Delivery.id' => $this->delivery_id,
								   'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
								   'Delivery.sys'=> 'N',
								   'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$numOrderNewResults = 0;
		$newResults['Delivery'] = $results['Delivery'];
		
		foreach ($results['Order'] as $numOrder => $order) {

			$newResults['Order'][$numOrderNewResults] = $results['Order'][$numOrder];
				
			/*
			 * Suppliers
			* */
			$SuppliersOrganization = new SuppliersOrganization;
			$SuppliersOrganization->unbindModel(['belongsTo' => ['Organization', 'CategoriesSupplier']]);
			$SuppliersOrganization->unbindModel(['hasMany' => ['Article', 'Order', 'SuppliersOrganizationsReferent']]);
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.id' => $order['supplier_organization_id']];
			$options['recursive'] = 1;
			$SuppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
			if(!empty($SuppliersOrganizationResults)) {
				$newResults['Order'][$numOrderNewResults]['Supplier'] = $SuppliersOrganizationResults['Supplier'];
				$newResults['Order'][$numOrderNewResults]['SuppliersOrganization'] = $SuppliersOrganizationResults['SuppliersOrganization'];
			}
			
			$numOrderNewResults++;
			
		} // end  foreach ($results['Order'] as $numOrder => $order)
	
		
		/*
		 * metto dopo gli ordini diversi dallo stato tesoriere_stato_pay = 'Y'
		*/
		$Delivery->hasMany['Order']['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
													  'Order.isVisibleBackOffice != ' => 'N',
													  'Order.state_code != ' => 'CREATE-INCOMPLETE',
													  'Order.tesoriere_stato_pay' => 'Y'];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		$options = [];
		$options['conditions'] =  ['Delivery.id' => $this->delivery_id,
									'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									'Delivery.sys'=> 'N',
									'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		foreach ($results['Order'] as $numOrder => $order) {

			$newResults['Order'][$numOrderNewResults] = $results['Order'][$numOrder];
			
			/*
			 * Suppliers
			* */
			$SuppliersOrganization = new SuppliersOrganization;
			$SuppliersOrganization->unbindModel(['belongsTo' => ['Organization', 'CategoriesSupplier']]);
			$SuppliersOrganization->unbindModel(['hasMany' => ['Article', 'Order', 'SuppliersOrganizationsReferent']]);
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.id' => $order['supplier_organization_id']];
			$options['recursive'] = 1;
			$SuppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
			if(!empty($SuppliersOrganizationResults)) {
				$newResults['Order'][$numOrderNewResults]['Supplier'] = $SuppliersOrganizationResults['Supplier'];
				$newResults['Order'][$numOrderNewResults]['SuppliersOrganization'] = $SuppliersOrganizationResults['SuppliersOrganization'];
			}
		
			$numOrderNewResults++;
			
		} // end  foreach ($results['Order'] as $numOrder => $order)
		
		$this->set('results', $newResults);
		
		/*
		 *  elenco order.tesoriere_stato_pay presenti nella lista per legenda
		 */
		$orderTesoriereStatoPayResults = ['N' => "Ordini da saldare al produttore", 'Y' => "Ordini saldati al produttore"];
		
		$this->set('orderTesoriereStatoPayResults', $orderTesoriereStatoPayResults);
		
		$this->layout = 'ajax';	
	}
	
	/*
	 *  consegne per richiamare elenco ordini con consegne CLOSEper gestire il pagamento
	*/
	public function admin_pay_suppliers_history() {
	
		$debug = false;
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									'Delivery.isVisibleBackOffice' => 'Y',
									'Delivery.sys'=> 'N',
									'Delivery.type'=> 'GAS', // GAS-GROUP
									'Delivery.stato_elaborazione' => 'CLOSE'];
		$options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$options['order'] = ['Delivery.data' => 'asc'];
		$options['recursive'] = -1;
		$deliveries = $Delivery->find('list', $options);
		if(empty($deliveries)) {
			$this->Session->setFlash(__('NotFoundDeliveries'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$this->set(compact('deliveries'));
	}
	
	/*
	 *  ajax, elenco ordini per visualizzare il pagamento
	*/
	public function admin_orders_to_pay_index_history($delivery_id=0) {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		App::import('Model', 'SuppliersOrganization');
			
		$Delivery->id = $this->delivery_id;
		if (!$Delivery->exists($Delivery->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		$newResults = [];
		
		$Delivery->hasMany['Order']['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
													'Order.isVisibleBackOffice != ' => 'N',
													'Order.state_code != ' => 'CREATE-INCOMPLETE'];
		$Delivery->hasMany['Order']['order'] = ['Order.data_inizio', 'Order.data_fine'];
		$options = [];
		$options['conditions'] = ['Delivery.id' => $this->delivery_id,
								'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
								'Delivery.sys'=> 'N',
								'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		foreach ($results['Order'] as $numOrder => $order) {
	
			$newResults['Order'][$numOrderNewResults] = $results['Order'][$numOrder];
				
			/*
			 * Supplier
			*/
			$sql = "SELECT *
					FROM
						".Configure::read('DB.prefix')."suppliers_organizations as SuppliersOrganization
					WHERE
						 stato = 'Y'
						 and organization_id = ".(int)$this->user->organization['Organization']['id']."
						 and id = ".(int)$order['supplier_organization_id'];
			$suppliersOrganization = current($Delivery->query($sql));
			if(!empty($suppliersOrganization))
				$newResults['Order'][$numOrderNewResults]['SuppliersOrganization'] = $suppliersOrganization['SuppliersOrganization'];
	
			$numOrderNewResults++;
				
		} // end  foreach ($results['Order'] as $numOrder => $order)
	
		$this->set('results', $newResults);
	
		$this->layout = 'ajax';
	}
	
	/*
	 *  produttori per richiamare elenco ordini per visualizzare il pagamento
	 */
	public function admin_pay_suppliers_by_supplier() {
		
		$debug = false;
		
		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;		
	
		if ($this->request->is('post') || $this->request->is('put')) {
										
			foreach($this->request->data['Order'] as $order_id => $data) {
			
				$this->Tesoriere->updateFromModulo($this->user, $order_id, $data, $debug);
				
				/*
				 * se Ordine saldato (Order.tesoriere_stato_pay = Y) passo a Order.state_code succssivo
				 */
				if($OrderLifeCycle->isPaidSupplier($this->user, $order_id, $debug)) {
				 
					$state_code_next = $OrderLifeCycle->stateCodeAfter($this->user, $order_id, 'SUPPLIER-PAID');
					
					$OrderLifeCycle->stateCodeUpdate($this->user, $order_id, $state_code_next);
				}
			} // end foreach($this->request->data['Order'] as $order_id => $data)
		
			
		} // end if ($this->request->is('post') || $this->request->is('put')) 
	
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
			
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
								   'SuppliersOrganization.stato' => 'Y'];
		$options['recursive'] = -1;
		$options['order'] = ['SuppliersOrganization.name'];
		$results = $SuppliersOrganization->find('list', $options);
		$this->set('suppliersOrganizations',$results);
	}

	/*
	 *  ajax, elenco ordini di u produttore per visualizzare il pagamento
	*/
	public function admin_orders_to_pay_index_by_supplier($supplier_organization_id=0) {
	 
	    $debug = false;
	    
		App::import('Model', 'Order');
		$Order = new Order;
	
		$Order->unbindModel(['belongsTo' => ['SuppliersOrganization']]); 
		
		$options = [];
		$options['conditions'] =['Order.organization_id' => $this->user->organization['Organization']['id'],
								'Order.isVisibleBackOffice != ' => 'N',
								'Order.state_code != ' => 'CREATE-INCOMPLETE',
								'Order.supplier_organization_id' => $supplier_organization_id];
		$options['order'] = ['Order.data_inizio', 'Order.data_fine'];
		$options['recursive'] = 1;
		$results = $Order->find('all', $options);
		
		if(isset($this->user->organization['Organization']['hasGasGroups']) && $this->user->organization['Organization']['hasGasGroups']=='Y') {
			App::import('Model', 'GasGroupDelivery');
			$GasGroupDelivery = new GasGroupDelivery;

			foreach($results as $numResult => $result) {

				$gasGroupDeliveryLabel = $GasGroupDelivery->getLabel($this->user, $this->user->organization['Organization']['id'], $result['Delivery']['id']);
				if($gasGroupDeliveryLabel!==false)
					$results[$numResult]['Delivery']['luogoData'] = $gasGroupDeliveryLabel;
			}
		} // end if(isset($this->user->organization['Organization']['hasGasGroups']) && $this->user->organization['Organization']['hasGasGroups']=='Y') 

		self::d([$options,$results], $debug);
		
		$this->set('results', $results);
	
		/*
		 *  elenco order.tesoriere_stato_pay presenti nella lista per legenda
		 */
		$orderTesoriereStatoPayResults = ['N' => "Ordini da saldare al produttore", 'Y' => "Ordini saldati al produttore"];
		
		$this->set('orderTesoriereStatoPayResults', $orderTesoriereStatoPayResults);
			
		$this->layout = 'ajax';
	}
	
	public function admin_sotto_menu_tesoriere($delivery_id, $position_img) {
	
		$this->ctrlHttpReferer();
		
		$results = [];
		
		if(!empty($delivery_id)) {
			App::import('Model', 'Delivery');
			$Delivery = new Delivery;
		
			$Delivery->id = $delivery_id;
			if ($Delivery->exists($Delivery->id, $this->user->organization['Organization']['id'])) {
				/*
				$this->Session->setFlash(__('msg_error_params'));
			    $this->myRedirect(Configure::read('routes_msg_exclamation'));
				*/
				$results = $Delivery->read($delivery_id, $this->user->organization['Organization']['id']);
			}
			else
				$results['Delivery']['id'] = 0;
		}
		else 
			$results['Delivery']['id'] = 0;
		
		$this->set('results',$results);
		$this->set('position_img',$position_img);
	
		$this->layout = 'ajax';
	}
	
	public function admin_sotto_menu_referentetesoriere($delivery_id, $position_img) {
	
		$this->ctrlHttpReferer();
		
		if(!empty($this->delivery_id)) {
			App::import('Model', 'Delivery');
			$Delivery = new Delivery;
		
			$Delivery->id = $this->delivery_id;
			if (!$Delivery->exists($Delivery->id, $this->user->organization['Organization']['id'])) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
		
			$results = $Delivery->read($this->delivery_id, $this->user->organization['Organization']['id']);
		}
		else 
			$results['Delivery']['id'] = 0;
		
		$this->set('results',$results);
		$this->set('position_img',$position_img);
	
		$this->layout = 'ajax';
	}
	public function admin_sotto_menu_tesoriere_request_payment_bootstrap($request_payment_id) {
		$this->_sotto_menu_tesoriere_request_payment($request_payment_id);
	}
	
	public function admin_sotto_menu_tesoriere_request_payment($request_payment_id, $position_img) {
		$this->_sotto_menu_tesoriere_request_payment($request_payment_id);
		
		$this->set('position_img', $position_img);
	}
	
	private function _sotto_menu_tesoriere_request_payment($request_payment_id) { 

	   /*
		* $pageCurrent = ['controller' => '', 'action' => ''];
		* mi serve per non rendere cliccabile il link corrente nel menu laterale
		*/
		$pageCurrent = $this->getToUrlControllerAction($_SERVER['HTTP_REFERER']);
		$this->set('pageCurrent',$pageCurrent);
				
		App::import('Model', 'RequestPayment');
		$RequestPayment = new RequestPayment;
		
		$RequestPayment->id = $request_payment_id;
		if (!$RequestPayment->exists($RequestPayment->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$conditions = ['RequestPayment.organization_id' => $this->user->organization['Organization']['id'], 'RequestPayment.id' => $request_payment_id];
		$requestPaymentResults = $RequestPayment->find('first', ['conditions' => $conditions, 'recursive' => -1]);
		$this->set('requestPaymentResults', $requestPaymentResults);	

		$tot_importo = $RequestPayment->getTotImporto($this->user, $request_payment_id);
		$this->set('tot_importo',$tot_importo);
		
		/*
		 * dispensa
		*/
		if($this->user->organization['Organization']['hasStoreroom']=='Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {

			App::import('Model', 'Storeroom');
			$Storeroom = new Storeroom;
		
			$deliveries = $Storeroom->deliveriesToRequestPayment($this->user);
			if(empty($deliveries)) 
				$deliveriesValideToStoreroom = 'N';
			else 	
				$deliveriesValideToStoreroom = 'Y';
		}
		else 
			$deliveriesValideToStoreroom = 'N';
	
		$this->set('deliveriesValideToStoreroom', $deliveriesValideToStoreroom);	
		
		$this->layout = 'ajax';	
	}
		
	public function admin_sotto_menu_referentetesoriere_request_payment($request_payment_id) {
			
		App::import('Model', 'RequestPayment');
		$RequestPayment = new RequestPayment;
		
		$RequestPayment->id = $request_payment_id;
		if (!$RequestPayment->exists($RequestPayment->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$conditions = ['RequestPayment.organization_id' => $this->user->organization['Organization']['id'],
						'RequestPayment.id' => $request_payment_id];
		$requestPaymentResults = $RequestPayment->find('first', ['conditions' => $conditions, 'recursive' => -1]);
		$this->set('requestPaymentResults',$requestPaymentResults);
	}	
	
	/*
	 * devo cercare per all e creare la lista perche' dopo il submit il campo luogoData era vuoto!!
	 */
	private function _getListDeliveries($user, $debug=false) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$results = [];
		$deliveries = [];
		
		$options = [];
		$options['conditions'] = ['Delivery.organization_id' => (int)$user->organization['Organization']['id'],
								   'Delivery.isVisibleBackOffice' => 'Y',
								   'Delivery.sys'=> 'N',
								   'Delivery.stato_elaborazione' => 'OPEN'];
		if(isset($user->organization['Organization']['hasGasGroups']) && $user->organization['Organization']['hasGasGroups']=='Y') {
			$options['conditions'] += ['Delivery.type' => 'GAS-GROUP'];

			App::import('Model', 'GasGroupDelivery');
			$GasGroupDelivery = new GasGroupDelivery;			
		}	
		else 
			$options['conditions'] += ['Delivery.type' => 'GAS'];
		
		// $options['fields'] = ['Delivery.id', 'Delivery.luogoData'];
		$options['order'] = ['Delivery.data' => 'asc'];
		$options['recursive'] = -1;
		$results = $Delivery->find('all', $options);
		self::d($results, $debug);
		if(empty($results)) {
			$this->Session->setFlash(__('NotFoundDeliveries'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		else {
			foreach($results as $result) {

				if(isset($user->organization['Organization']['hasGasGroups']) && $user->organization['Organization']['hasGasGroups']=='Y') {	
					$gasGroupDeliveryLabel = $GasGroupDelivery->getLabel($user, $user->organization['Organization']['id'], $result['Delivery']['id']);
					if($gasGroupDeliveryLabel!==false)
						$deliveries[$result['Delivery']['id']] = $gasGroupDeliveryLabel;
					else {
						if(isset($result['Delivery']['luogoData']))	
							$deliveries[$result['Delivery']['id']] = $result['Delivery']['luogoData'];
						else
							$deliveries[$result['Delivery']['id']] = $result[0]['Delivery__luogoData'];						
					}
				}
				else {
					if(isset($result['Delivery']['luogoData']))	
						$deliveries[$result['Delivery']['id']] = $result['Delivery']['luogoData'];
					else
						$deliveries[$result['Delivery']['id']] = $result[0]['Delivery__luogoData'];
				}
			}
			
		}
		$this->set(compact('deliveries'));
	}
}