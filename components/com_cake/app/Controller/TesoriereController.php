<?php
App::uses('AppController', 'Controller');

class TesoriereController extends AppController {

	private $isReferenteTesoriere = false;	public $helpers = array('Html', 'Javascript', 'Ajax', 'Tabs', 'RowEcomm');
	
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
		$actionWithPermission = array('admin_index', 'admin_edit', 'admin_delete');
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
	
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.isVisibleBackOffice' => 'Y',
									   'Delivery.stato_elaborazione' => 'OPEN',
									   'Delivery.sys'=> 'N',
									   'DATE(Delivery.data) <= CURDATE()');
		$options['fields'] = array('id', 'luogoData');
		$options['order'] = 'data ASC';
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

				App::import('Model', 'SummaryOrder');
				$SummaryOrder = new SummaryOrder;

				if(strpos($order_id_selected,',')===false)
					$order_id_arr[] = $order_id_selected;
				else 
					$order_id_arr = explode(',',$order_id_selected);
				foreach ($order_id_arr as $order_id) {
					$resultsSummaryOrder = $SummaryOrder->select_to_order($this->user, $order_id);
					
					/*
					 * se summaryOrder non e' gia' stato popolato dal referente da Cart::admin_managementCartsGroupByUsers
					 */
					if(empty($resultsSummaryOrder))
						$this->__populate_summary_orders($order_id);
					else {
						/*
						 * aggiorno stato ORDER
						*/
						$sql = "UPDATE
								`".Configure::read('DB.prefix')."orders`
								SET
									state_code = 'PROCESSED-TESORIERE',
									modified = '".date('Y-m-d H:i:s')."'
								WHERE
									organization_id = ".(int)$this->user->organization['Organization']['id']."
									and id = ".(int)$order_id;
						// echo '<br />'.$sql;
						$result = $SummaryOrder->query($sql);						
					}
				} // end foreach ($order_id_arr as $order_id)  ciclo ordini

				/*
				 * invio mail a referenti
				*/
				App::import('Model', 'Order');
				App::import('Model', 'SuppliersOrganizationsReferent');
				App::import('Model', 'Mail');
				
				foreach ($order_id_arr as $order_id) {
				
					/*
					 * estraggo i referenti
					*/
					$Order = new Order;
						
					$options = array();
					$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
												   'Order.id' => $order_id);
					$options['recursive'] = 0;
					$results = $Order->find('first', $options);
						
					$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
					$conditions = array('User.block' => 0,
										'SuppliersOrganization.id' => $results['Order']['supplier_organization_id']);
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
								$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))));
							else
								$Email->viewVars(array('body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))));
				
					foreach ($userResults as $userResult)  {
						$name = $userResult['User']['name'];
						$mail = $userResult['User']['email'];
							
						if(!empty($mail)) {
							$Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
							$Email->to($mail);
								
							$Mail->send($Email, $mail, $body_mail, $debug);
						} // end if(!empty($mail))
					} // end foreach ($userResults as $userResult)
				} // end foreach ($order_id_arr as $order_id)  ciclo ordini
				
				$this->Session->setFlash(__('Orders State Processed Tesoriere'));
				/*
				 * non cambio + la pagina $this->myRedirect(array('action' => 'orders_get_PROCESSED_TESORIERE', null, 'delivery_id='.$this->delivery_id));
				 */
			}
		} // end if ($this->request->is('post') || $this->request->is('put')) 
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.isVisibleBackOffice' => 'Y',
									   'Delivery.sys'=> 'N',
									   'Delivery.stato_elaborazione' => 'OPEN');
		$options['fields'] = array('id', 'luogoData');
		$options['order'] = 'data ASC';
		$options['recursive'] = -1;
		$deliveries = $Delivery->find('list', $options);
		if(empty($deliveries)) {			$this->Session->setFlash(__('NotFoundDeliveries'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}
		$this->set(compact('deliveries'));
		
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
	 * estrai ordini PROCESSED-TESORIERE
	*  azione
	*  1) elenco SummaryOrders
	*  2) da PROCESSED-TESORIERE (tesoriere) in PROCESSED-REFERENTE (referente)
	* 		  se Order.tesoriere_sorce = REFERENTE cancella i dati in summary_orders
	* 		  se Order.tesoriere_sorce = CASSIERE  NON cancella i dati in summary_orders
	*  3) da PROCESSED-TESORIERE (tesoriere) in TO-PAYMENT (tesoriere)
	*
	* se lo richiamo dal menu laterale delivery_id e' valorizzato
	* 
	* $order_id e' valorizzato se richiamato dal referente/tesoriere
	*/	
	public function admin_orders_get_PROCESSED_TESORIERE() {
			
		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$action_submit = $this->request->data['Tesoriere']['action_submit'];
			
			$order_id_selected = $this->request->data['Tesoriere']['order_id_selected'];
			
			if(!empty($order_id_selected)) {
				
				App::import('Model', 'Order');
				$Order = new Order;
				
				$order_id_arr = explode(',',$order_id_selected);
				
				switch ($action_submit) {
					/*
					 * riporto l'ordine al referente
					 */
					case 'OrdersToPROCESSED_REFERENTE_POST_DELIVERY':			
							foreach ($order_id_arr as $order_id) {
								
								if($debug) echo '<br />order_id '.$order_id;
								
								$Order->id = $order_id;
								if (!$Order->exists($this->user->organization['Organization']['id'])) {
									$this->Session->setFlash(__('msg_error_params'));
									$this->myRedirect(Configure::read('routes_msg_exclamation'));
								}
								$order = $Order->read($this->user->organization['Organization']['id'], null, $order_id);
								
								if($debug) {
									echo "<pre>";
									print_r($order);
									echo "</pre>";
								}
								
								/*
								 * 	se Order.tesoriere_sorce = REFERENTE cancella i dati in summary_orders
								 * 	se Order.tesoriere_sorce = CASSIERE non lo permetto
								*/
								if($order['Order']['state_code']=='PROCESSED-TESORIERE') {
									
									$state_code_next = '';
									
									/*
									 * ulteriore ctrl
									 * il Organization.payToDelivery puo' essere POST o ON-POST (mai ON)
									 */
									 if($this->user->organization['Organization']['payToDelivery']=='POST')
									 	$state_code_next = 'PROCESSED-POST-DELIVERY';
									 else
									 if($this->user->organization['Organization']['payToDelivery']=='ON-POST') {
											if($order['Order']['tesoriere_sorce']=='REFERENTE')
												$state_code_next = 'INCOMING-ORDER';
											else
											if($order['Order']['tesoriere_sorce']=='CASSIERE')	
												$state_code_next = 'PROCESSED-ON-DELIVERY';  // (in carico al cassiere durante la consegna)							 
									 }
									 	
									/*
									 * aggiorno stato ORDER
									*/
									if(!empty($state_code_next)) {
										$sql = "UPDATE
											`".Configure::read('DB.prefix')."orders`
										SET
											state_code = '".$state_code_next."',
											modified = '".date('Y-m-d H:i:s')."'
										WHERE
											organization_id = ".(int)$this->user->organization['Organization']['id']."
											and id = ".(int)$order_id;
										if($debug) echo '<br />sql '.$sql;
										$result = $Order->query($sql);
										
										/*
										 * se l'ordine e' AGGREGATE i dati in SummaryOrder sono stati caricati dal referente
										 */
										if($order['Order']['tesoriere_sorce']=='REFERENTE' && $order['Order']['typeGest']!='AGGREGATE')
											$this->__delete_summary_orders($order_id, $debug);
										
									}
								}
							}  // end foreach ($order_id_arr as $order_id)
							$this->Session->setFlash(__('Orders State Processed Referente Post Delivery'));
					break;
					/*
					 * passo allo stato per richiedere il pagamento dell'ordine
					*/
					case 'OrdersToTO_PAYMENT':
						App::import('Model', 'Order');
						$Order = new Order;
						
						foreach ($order_id_arr as $order_id) {
			
								$Order->id = $order_id;
								if (!$Order->exists($this->user->organization['Organization']['id'])) {
									$this->Session->setFlash(__('msg_error_params'));
									$this->myRedirect(Configure::read('routes_msg_exclamation'));
								}
								$order = $Order->read($this->user->organization['Organization']['id'], null, $order_id);
								
								if($order['Order']['state_code']=='PROCESSED-TESORIERE') {
									/*
									 * aggiorno stato ORDER
									*/
									$sql = "UPDATE
										`".Configure::read('DB.prefix')."orders`
									SET
										state_code = 'TO-PAYMENT',
										modified = '".date('Y-m-d H:i:s')."'
									WHERE
										organization_id = ".(int)$this->user->organization['Organization']['id']."
										and id = ".(int)$order_id;
									$result = $Order->query($sql);
								}
						} // end foreach ($order_id_arr as $order_id)
						$this->Session->setFlash(__('Lo stato dell\'ordine è stato aggiornato: ora si potrà richiederne il pagamento.'));						/*
						 * non cambio + la pagina $this->myRedirect(array('controller' => 'RequestPayments', 'action' => 'index', null, 'delivery_id='.$this->delivery_id));
						 */ 
					break;					
				}
			} // end if(!empty($order_id_selected))

		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.isVisibleBackOffice' => 'Y',
									   'Delivery.sys'=> 'N',
									   'Delivery.stato_elaborazione' => 'OPEN');
		$options['fields'] = array('id', 'luogoData');
		$options['order'] = 'data ASC';
		$options['recursive'] = -1;
		$deliveries = $Delivery->find('list', $options);
		if(empty($deliveries)) {			$this->Session->setFlash(__('NotFoundDeliveries'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}
		$this->set(compact('deliveries'));
		
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
	 * elenco degli ordini con lo order_state_code cliccabile (in base a $order_state_code_checked)
	 * */
	public function admin_orders_index($delivery_id=0, $order_state_code_checked) {
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		App::import('Model', 'SuppliersOrganization');
		
		$Delivery->id = $this->delivery_id;
		if (!$Delivery->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		
		$newResults = array();
		/*
		 * metto in testa gli ordini con l'ordine filtrato $order_state_code_checked
		*/
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
														  'Order.isVisibleBackOffice != ' => 'N',
														  'Order.state_code != ' => 'CREATE-INCOMPLETE',
														  'Order.state_code' => $order_state_code_checked);
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		
		$options = array();
		$options['conditions'] = array('Delivery.id' => $this->delivery_id,
									   'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.sys'=> 'N',
							           'Delivery.isVisibleBackOffice' => 'Y');
		$options['recursive'] = 1;
		$results = $Delivery->find('first', $options);
		
		$numOrderNewResults = 0;
		$newResults['Delivery'] = $results['Delivery'];
		
		foreach ($results['Order'] as $numOrder => $order) {

			/*
			 * Suppliers, se e' in stato N lo escludo
			* */
			$SuppliersOrganization = new SuppliersOrganization;
			$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization', 'CategoriesSupplier')));
			$SuppliersOrganization->unbindModel(array('hasMany' => array('Article', 'Order', 'SuppliersOrganizationsReferent')));
			
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.id' => $order['supplier_organization_id']);
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
							
				$conditions = array('User.block' => 0,
									'SuppliersOrganization.id' => $order['supplier_organization_id']);
				$suppliersOrganizationsReferent = $SuppliersOrganizationsReferent->getReferentsCompact($this->user, $conditions);
				
				if(!empty($suppliersOrganizationsReferent))
					$newResults['Order'][$numOrderNewResults]['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent;
				
				$numOrderNewResults++;
			}
		} // end  foreach ($results['Order'] as $numOrder => $order)
	
		
		/*
		 * metto dopo gli ordini diversi dallo stato filtrato $order_state_code_checked
		*/
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
													  	  'Order.isVisibleBackOffice != ' => 'N',
														  'Order.state_code != ' => 'CREATE-INCOMPLETE',
														  'Order.state_code !=' => $order_state_code_checked);
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options = array();
		$options['conditions'] = array('Delivery.id' => $this->delivery_id,
										'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.sys'=> 'N',
										'Delivery.isVisibleBackOffice' => 'Y');
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
							
				$conditions = array('User.block' => 0,
									'SuppliersOrganization.id' => $order['supplier_organization_id']);
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

		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
														  'Order.isVisibleBackOffice != ' => 'N',
														  'Order.state_code != ' => 'CREATE-INCOMPLETE',
														  'Order.delivery_id' => $this->delivery_id);
		$options['order'] = array('Order.state_code');
		$options['group'] = array('Order.state_code');
		$options['fields'] = array('Order.state_code');
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
	
		if ($this->request->is('post') || $this->request->is('put')) {
							
			unset($this->request->data['Order']['delivery_id']);
			
			foreach($this->request->data['Order'] as $order_id => $data) {

				/*
				 *   ctrl che siano cambiati i dati
				 */
				 $sqlTmp = "";
				 if($this->importoToDatabase($data['tesoriere_importo_pay']) != $data['tesoriere_importo_pay_old'])
				 	$sqlTmp .= " tesoriere_importo_pay = ".$this->importoToDatabase($data['tesoriere_importo_pay']).',';
				 	
				 if($data['tesoriere_data_pay_db'] != $data['tesoriere_data_pay_old'])
				 	$sqlTmp .= " tesoriere_data_pay = '".$data['tesoriere_data_pay_db']."',";
				 
				 if(empty($data['tesoriere_stato_pay']))
				 	$data['tesoriere_stato_pay'] = 'N';
				 	
				 if($data['tesoriere_stato_pay'] != $data['tesoriere_stato_pay_old'])
				 	$sqlTmp .= " tesoriere_stato_pay = '".$data['tesoriere_stato_pay']."',";
				 	
				if(!empty($sqlTmp)) {
				
					try {
						$sql = "UPDATE
									`".Configure::read('DB.prefix')."orders`
								SET
									".$sqlTmp."
									modified = '".date('Y-m-d H:i:s')."'
								WHERE
									organization_id = ".(int)$this->user->organization['Organization']['id']."
									and id = ".(int)$order_id;
						if($debug) echo '<br />'.$sql;
						$resultUpdate = $this->Tesoriere->query($sql);
					}
					catch (Exception $e) {
						CakeLog::write('error',$sql);
						CakeLog::write('error',$e);
					}
				} // if(!empty($sqlTmp))
			} // end foreach($this->request->data['Order'] as $order_id => $data)
		
			
		} // end if ($this->request->is('post') || $this->request->is('put')) 
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.isVisibleBackOffice' => 'Y',
									   'Delivery.sys'=> 'N',
									   'Delivery.stato_elaborazione' => 'OPEN');
		$options['fields'] = array('id', 'luogoData');
		$options['order'] = 'data ASC';
		$options['recursive'] = -1;
		$deliveries = $Delivery->find('list', $options);
		if(empty($deliveries)) {
			$this->Session->setFlash(__('NotFoundDeliveries'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$this->set(compact('deliveries'));	
	}

	/*
	 *  ajax, elenco ordini per gestire il pagamento
	 */	
	public function admin_orders_to_pay_index($delivery_id=0) {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		App::import('Model', 'SuppliersOrganization');
			
		$Delivery->id = $this->delivery_id;
		if (!$Delivery->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		$newResults = array();
		/*
		 * metto in testa gli ordini con l'ordine tesoriere_stato_pay = N
		*/
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
														  'Order.isVisibleBackOffice != ' => 'N',
														  'Order.state_code != ' => 'CREATE-INCOMPLETE',
														  'Order.tesoriere_stato_pay' => 'N');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		
		$options = array();
		$options['conditions'] = array('Delivery.id' => $this->delivery_id,
									   'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.sys'=> 'N',
							           'Delivery.isVisibleBackOffice' => 'Y');
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
			$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization', 'CategoriesSupplier')));
			$SuppliersOrganization->unbindModel(array('hasMany' => array('Article', 'Order', 'SuppliersOrganizationsReferent')));
			
			$options = array();
			$options['conditions'] = array('SuppliersOrganization.id' => $order['supplier_organization_id']);
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
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
													  	  'Order.isVisibleBackOffice != ' => 'N',
														  'Order.state_code != ' => 'CREATE-INCOMPLETE',
														  'Order.tesoriere_stato_pay' => 'Y');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options = array();
		$options['conditions'] = array('Delivery.id' => $this->delivery_id,
										'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.sys'=> 'N',
										'Delivery.isVisibleBackOffice' => 'Y');
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
		
		/*
		 *  elenco order.tesoriere_stato_pay presenti nella lista per legenda
		 */
		$orderTesoriereStatoPayResults = array('N' => "Ordini da saldare al produttore", 'Y' => "Ordini saldati al produttore");
		
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
	
		$options = array();
		$options['conditions'] = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Delivery.isVisibleBackOffice' => 'Y',
										'Delivery.sys'=> 'N',
										'Delivery.stato_elaborazione' => 'CLOSE');
		$options['fields'] = array('id', 'luogoData');
		$options['order'] = 'data ASC';
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
		if (!$Delivery->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		$newResults = array();
		
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
														'Order.isVisibleBackOffice != ' => 'N',
														'Order.state_code != ' => 'CREATE-INCOMPLETE');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options = array();
		$options['conditions'] = array('Delivery.id' => $this->delivery_id,
				'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
				'Delivery.sys'=> 'N',
				'Delivery.isVisibleBackOffice' => 'Y');
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
	
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
			
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										'SuppliersOrganization.stato' => 'Y');
		$options['recursive'] = -1;
		$options['order'] = array('SuppliersOrganization.name');
		$results = $SuppliersOrganization->find('list', $options);
		$this->set('suppliersOrganizations',$results);
	}

	/*
	 *  ajax, elenco ordini di u produttore per visualizzare il pagamento
	*/
	public function admin_orders_to_pay_index_by_supplier($supplier_organization_id=0) {
	
		App::import('Model', 'Order');
		$Order = new Order;
	
		$Order->unbindModel(array('belongsTo' => array('SuppliersOrganization'))); 
		
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.isVisibleBackOffice != ' => 'N',
										'Order.state_code != ' => 'CREATE-INCOMPLETE',
										'Order.supplier_organization_id' => $supplier_organization_id);
		$options['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options['recursive'] = 1;
		$results = $Order->find('all', $options);
		/*
		echo "<pre>";
		print_r($options);
		print_r($results);
		echo "</pre>";
		*/
		$this->set('results', $results);
	
		$this->layout = 'ajax';
	}
	
	public function admin_sotto_menu_tesoriere($delivery_id, $position_img) {
	
		$this->ctrlHttpReferer();
		
		$results = array();
		
		if(!empty($delivery_id)) {
			App::import('Model', 'Delivery');
			$Delivery = new Delivery;
		
			$Delivery->id = $delivery_id;
			if ($Delivery->exists($this->user->organization['Organization']['id'])) {
				/*
				$this->Session->setFlash(__('msg_error_params'));
			    $this->myRedirect(Configure::read('routes_msg_exclamation'));
				*/
				$results = $Delivery->read($this->user->organization['Organization']['id'], null, $delivery_id);
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
			if (!$Delivery->exists($this->user->organization['Organization']['id'])) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
		
			$results = $Delivery->read($this->user->organization['Organization']['id'], null, $this->delivery_id);
		}
		else 
			$results['Delivery']['id'] = 0;
		
		$this->set('results',$results);
		$this->set('position_img',$position_img);
	
		$this->layout = 'ajax';
	}

	public function admin_sotto_menu_tesoriere_request_payment($delivery_id, $request_payment_id, $position_img) {	
	   /*
		* $pageCurrent = array('controller' => '', 'action' => '');		* mi serve per non rendere cliccabile il link corrente nel menu laterale		*/		$pageCurrent = $this->getToUrlControllerAction($_SERVER['HTTP_REFERER']);		$this->set('pageCurrent',$pageCurrent);
				$this->admin_sotto_menu_tesoriere($delivery_id, $position_img);
		
		App::import('Model', 'RequestPayment');		$RequestPayment = new RequestPayment;				$RequestPayment->id = $request_payment_id;		if (!$RequestPayment->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}				$conditions = array('RequestPayment.organization_id' => $this->user->organization['Organization']['id'],				'RequestPayment.id' => $request_payment_id);		$requestPaymentResults = $RequestPayment->find('first', array('conditions' => $conditions, 'recursive' => -1));		$this->set('requestPaymentResults', $requestPaymentResults);	

		$tot_importo = $RequestPayment->getTotImporto($this->user, $request_payment_id);
		$this->set('tot_importo',$tot_importo);	}		public function admin_sotto_menu_referentetesoriere_request_payment($delivery_id, $request_payment_id, $position_img) {			$this->admin_sotto_menu_referentetesoriere($delivery_id, $position_img);
		
		App::import('Model', 'RequestPayment');
		$RequestPayment = new RequestPayment;
		
		$RequestPayment->id = $request_payment_id;		if (!$RequestPayment->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}		
		$conditions = array('RequestPayment.organization_id' => $this->user->organization['Organization']['id'],							'RequestPayment.id' => $request_payment_id);		$requestPaymentResults = $RequestPayment->find('first', array('conditions' => $conditions, 'recursive' => -1));		$this->set('requestPaymentResults',$requestPaymentResults);	}	
	private function __populate_summary_orders($order_id) {
	
		App::import('Model', 'Order');
		$Order = new Order;
		
		$msg = '';
		$Order->id = $order_id;
		if (!$Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$order = $Order->read($this->user->organization['Organization']['id'], null, $order_id);
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
		$SummaryOrder->populate_to_order($this->user, $order_id, 0);
		
		
		/*
		* aggiorno stato ORDER
		*/
		$sql = "UPDATE
					`".Configure::read('DB.prefix')."orders`
				SET
					state_code = 'PROCESSED-TESORIERE',
					modified = '".date('Y-m-d H:i:s')."'
				WHERE
					organization_id = ".(int)$this->user->organization['Organization']['id']."
					and id = ".(int)$order_id;
		// echo '<br />'.$sql;
		$result = $Order->query($sql);
	}
	
	private function __delete_summary_orders($order_id=0, $debug) {
	
		if(empty($order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		/*
		 * cancello eventuali occorrenze di SummaryOrder
		 */
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
		$SummaryOrder->delete_to_order($this->user, $order_id, $debug);
	}	
}