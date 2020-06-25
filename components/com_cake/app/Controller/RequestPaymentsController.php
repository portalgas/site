<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
/**
 * RequestPayments Controller
 *
 * @property RequestPayment $RequestPayment
 */
class RequestPaymentsController extends AppController {

	private $requestPaymentResults = [];
	
	public function beforeFilter() {
		
		parent::beforeFilter();
		
		/* ctrl ACL */
		if($this->action != 'admin_view') {
			if(!$this->isTesoriereGeneric()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}
		
		if($this->user->organization['Organization']['type']!='GAS') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	
				
		/*
		 * ctrl referentTesoriere
		*/
		$this->set('isReferenteTesoriere', $this->isReferentTesoriere());
		
		/*
		 * REQUEST_PAYMENT
		*/
		if(isset($this->request->pass['id'])) {
			$id = $this->request->pass['id'];
			$conditions = ['RequestPayment.organization_id' => $this->user->organization['Organization']['id'],
							'RequestPayment.'.$this->RequestPayment->primaryKey => $id];
			$results = $this->RequestPayment->find('first', ['conditions' => $conditions, 'recursive' => -1]);
			
			
			$actionWithPermission = ['admin_add_generic', 'admin_add_orders', 'admin_add_storeroom'];
			if (in_array($this->action, $actionWithPermission)) {
				if($results['RequestPayment']['stato_elaborazione']!='WAIT') {
					$this->Session->setFlash(__('msg_not_request_payment_state'));
					$this->myRedirect(['action' => 'index']);					
				}
			}	
			$this->requestPaymentResults = $results;
			$this->set('requestPaymentResults', $this->requestPaymentResults);
			
			$tot_importo = $this->RequestPayment->getTotImporto($this->user, $id);
			$this->set('tot_importo', $tot_importo);
		}
		
		
		/*
		 * ctrl configurazione Organization
		*/
		if($this->user->organization['Organization']['hasStoreroom']=='Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			
			/*
			 * se non ci sono consegne valide (quelle per la dispensa) non fa comparire la voce di menu "Aggiungi una richiesta pagamento di dispensa"
			 */
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
		
		/*
		 * $pageCurrent = ['controller' => '', 'action' => ''];
		* mi serve per non rendere cliccabile il link corrente nel menu laterale
		*/
		$pageCurrent = $this->getToUrlControllerAction($_SERVER['REQUEST_URI']);
		$this->set('pageCurrent',$pageCurrent);	
	}
	
	public function admin_index() {
		
		$debug = false;
		
		/*
		 * aggiorno lo stato degli ordini
		 */
		$utilsCrons = new UtilsCrons(new View(null));
		if(Configure::read('developer.mode')) echo "<pre>";
		$utilsCrons->requestPaymentStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
		if(Configure::read('developer.mode')) echo "</pre>";
		
		/*
		 * cancello le richieste di pagamento chiuse
		 */		
		// $utilsCrons->archiveStatistics($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
		
		
		$this->RequestPayment->hasMany['Order']['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id']];
		$this->RequestPayment->hasMany['SummaryPayment']['conditions'] = ['SummaryPayment.organization_id' => $this->user->organization['Organization']['id']];
		$this->RequestPayment->recursive = 1;
		$conditions = ['RequestPayment.organization_id' => $this->user->organization['Organization']['id']];
		/*
		 * se referente-tesoriere o super-referente-tesoriere prendo solo le richieste creata da lui
		 * solo il tesoriere puo' gestirli tutti
		 */
		if($this->isReferentTesoriere()) 
			$conditions = ['RequestPayment.user_id' => $this->user->get('id')];		
		$this->paginate = ['conditions' => $conditions, 'order' => 'RequestPayment.created DESC'];
		$results = $this->paginate();
		
		foreach ($results as $i => $result) {
			$request_payment_id = $result['RequestPayment']['id'];

			if($this->user->organization['Organization']['hasStoreroom']=='Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
				/*
				 * totale RequestPaymentsStoreroom
				 */
				$sql = "SELECT
							count(id) as totRequestPaymentsStoreroom 
						FROM
							".Configure::read('DB.prefix')."request_payments_storerooms as RequestPaymentsStoreroom
						WHERE
							RequestPaymentsStoreroom.organization_id = ".(int)$this->user->organization['Organization']['id']."
							and RequestPaymentsStoreroom.request_payment_id = ".(int)$request_payment_id;
				self::d($sql, false);
				$tot = current($this->RequestPayment->query($sql));
				$results[$i]['RequestPaymentsStoreroom'] = $tot[0]; 
			} // end if($this->user->organization['Organization']['hasStoreroom']=='Y') 
	
			/*
			 * totale RequestPaymentsGeneric
			*/
			$sql = "SELECT
						count(id) as totRequestPaymentsGeneric
					FROM
						".Configure::read('DB.prefix')."request_payments_generics as RequestPaymentsGeneric
					WHERE
						RequestPaymentsGeneric.organization_id = ".(int)$this->user->organization['Organization']['id']."
						and RequestPaymentsGeneric.request_payment_id = ".(int)$request_payment_id;
			self::d($sql, false);
			$tot = current($this->RequestPayment->query($sql));
			$results[$i]['RequestPaymentsGeneric'] = $tot[0];
			
			$results[$i]['RequestPayment']['tot_importo'] = $this->RequestPayment->getTotImporto($this->user, $request_payment_id, $debug);
		}
		
		$this->set('requestPayments', $results);
	}

	/*
	 * creo un occorrenza di richiesta di pagamento RequestPayment
	*/
	public function admin_add() {

		if ($this->request->is('post') || $this->request->is('put')) {
			$data = null;
			$data['RequestPayment']['organization_id'] = $this->user->organization['Organization']['id'];
			$data['RequestPayment']['user_id'] = $this->user->get('id');
			$data['RequestPayment']['stato_elaborazione'] = 'WAIT';
			$data['RequestPayment']['stato_elaborazione_date'] = Configure::read('DB.field.date.empty');
			$data['RequestPayment']['data_send'] = Configure::read('DB.field.date.empty');
			$data['RequestPayment']['num'] = $this->_getNumMaxAndUpdate($this->user);	
			$data['RequestPayment']['nota'] = $this->request->data['RequestPayment']['nota'];
			
			self::d($data);
			$this->RequestPayment->create();
			if($this->RequestPayment->save($data)) {
				$request_payment_id = $this->RequestPayment->getLastInsertId();
				$this->myRedirect(['controller' => 'RequestPayments', 'action' => 'edit', 'id' => $request_payment_id]);
			}	
			else {
				$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
				$this->myRedirect(['controller' => 'RequestPayments', 'action' => 'index']);
			}				
		}
		
		$request_payment_num = $this->_getNumMax($this->user);
		$this->set('request_payment_num', $request_payment_num);
	}
		
	public function admin_edit($id = null) {
	
		$debug = false;
	
		/*
		 * lo recupero dal GET, perche' ho effettuato filtro
 		 */		 
		if(isset($this->request->params['pass']['id']))
			$id = $this->request->params['pass']['id'];
		else
			$id = $this->request->data['RequestPayment']['request_payment_id'];

		$this->RequestPayment->id = $id;
		if (!$this->RequestPayment->exists($this->RequestPayment->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			self::d($this->request, $debug);
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$msg = "";
	
		if ($this->request->is('post') || $this->request->is('put')) {
			
			if($this->request->data['RequestPayment']['stato_elaborazione']=='WAIT')
				$msg = $this->_edit_wait($this->request->data, $debug);
			if($this->request->data['RequestPayment']['stato_elaborazione']=='OPEN')
				$msg = $this->_edit_open($this->request->data, $debug);
							
			if(empty($msg))
				$this->Session->setFlash(__('The summary payments has been saved'));
			else
				$this->Session->setFlash($msg);
			
			/* 
			 * aggiorno gli stati Order.state_code con successivo
			 */
			App::import('Model', 'RequestPaymentsOrder');
			$RequestPaymentsOrder = new RequestPaymentsOrder;
						 
			$RequestPaymentsOrder->setOrdersStateCodeByRequestPaymentId($this->user, $id);

			/*
			 * se tutti i summary_payments.stato = SOSPESO o PAGATO porto RequestPayment.stato_elaborazione = CLOSE
			 * porta Order=CLOSE se tutti sono WAIT-REQUEST-PAYMENT-CLOSE
			 */
			$utilsCrons = new UtilsCrons(new View(null));
			$utilsCrons->requestPaymentStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $id);
			
			
		} // end if ($this->request->is('post') || $this->request->is('put'))
		/*
		 * filtri
		*/
		$FilterRequestPaymentName = null;
		$FilterSummaryPaymentStato = null;
		$conditions = [];
		
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Name')) {
			$FilterRequestPaymentName = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Name');
			$conditions += ['User.name'=> $FilterRequestPaymentName];
		}
		if($this->Session->check(Configure::read('Filter.prefix').'SummaryPaymentStato')) {
			$FilterSummaryPaymentStato = $this->Session->read(Configure::read('Filter.prefix').'SummaryPaymentStato');
			$conditions += ['SummaryPayment.stato' => $FilterSummaryPaymentStato];
		}
		
		$this->set('FilterRequestPaymentName', $FilterRequestPaymentName);
		$this->set('FilterSummaryPaymentStato', $FilterSummaryPaymentStato);
		
		$summaryPaymentStato = ['DAPAGARE' => __('DAPAGARE'), 'SOLLECITO1' => __('SOLLECITO1'), 'SOLLECITO2' => __('SOLLECITO2'), 'SOSPESO' => __('SOSPESO'), 'PAGATO' => __('PAGATO')];  
		$this->set(compact('summaryPaymentStato'));
		
		/*
		 * estraggo i dettagli di una richiesta di pagamento
		 * 	- ordini associati
		 *  - voci di spesa generica
		 *  - dispensa
		 */
		$results = $this->RequestPayment->getAllDetails($this->user, $id, $conditions, $debug);

		/*
		 * dati cassa per l'utente
		 */
		App::import('Model', 'Cash');
		foreach($results['SummaryPayment'] as $numResult => $result) {
			$Cash = new Cash;
			
			$options = [];
			$options['conditions'] = ['Cash.organization_id' => $this->user->organization['Organization']['id'],
										'Cash.user_id' => $result['User']['id']];
			$options['recursive'] = -1;
			$cashResults = $Cash->find('first', $options);
			if(empty($cashResults))	{
				$cashResults['Cash']['importo'] = '0.00';
				$cashResults['Cash']['importo_'] = '0,00';
				$cashResults['Cash']['importo_e'] = '0,00 &euro;';								
			}
			self::d($options, $debug);
			self::d($cashResults, $debug);

			$results['SummaryPayment'][$numResult]['Cash'] = $cashResults['Cash'];
		}
		
		self::d($results, false);
		
		$this->set(compact('results'));
		$this->set('request_payment_empty', $this->_ctrl_request_payment_empty($this->user, $results));
		
		$modalita = ClassRegistry::init('SummaryPayment')->enumOptions('modalita');
		if($this->user->organization['Organization']['hasFieldPaymentPos']=='N')
			unset($modalita['BANCOMAT']);
		unset($modalita['DEFINED']);
		$this->set(compact('modalita'));
		
		/*
		 * se arrivo da $requestPaymentsGenerics::delete() gli passo open_details cosi apro il box con il dettaglio ordini / voci di spesa
		 */
		if(isset($this->request->pass['open_details']) && $this->request->pass['open_details']=='Y')
			$this->set('open_details', true);
		else 
			$this->set('open_details', false);

		if(
		  (!empty($results['Order']) && count($results['Order'])==0 && !empty($results['PaymentsGeneric']) && count($results['PaymentsGeneric'])==0 && $this->user->organization['Organization']['hasStoreroomFrontEnd']=='N') || 
		  (!empty($results['Order']) && count($results['Order'])==0 && !empty($results['PaymentsGeneric']) && count($results['PaymentsGeneric'])==0 && !empty($results['Storeroom']) && count($results['Storeroom'])==0 && $this->user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
		  )
			$this->render('admin_edit_no');
		else {	

			self::d('RequestPayment.stato_elaborazione '.$results['RequestPayment']['stato_elaborazione']);
			
			switch ($results['RequestPayment']['stato_elaborazione']) {
				case 'WAIT':
					$this->render('admin_edit_wait');  // In lavorazione
				break;
				case 'OPEN':
					$this->render('admin_edit_open');
				break;
				case 'CLOSE':
					$this->render('admin_edit_close');
				break;
			}
		}
	}

	/*
	 * cambio lo stato della richiesta di pagmento
	 * da WAIT "in lavorazione" => OPEN "Aperta per richiedere il pagamento" => Order state_code = TO-PAYMENT
	 * da OPEN => WAIT => Order state_code = USER-PAID
	 * da OPEN => CLOSE "Chiusa" => Order state_code = ...   
 	 */
	public function admin_edit_stato_elaborazione($id=null) {
	
		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			$action_submit = $this->request->data['RequestPayment']['action_submit'];
			
			switch ($action_submit) {
				case 'toWait':
					$stato_elaborazione = 'WAIT';
				break;
				case 'toClose':
					/*
					 * non lo permetto +, la rich si chiudo in automatico se tutti gli ordini sono CLOSE
					 */
					$stato_elaborazione = 'CLOSE';
				break;
			}

			$id = $this->request->data['RequestPayment']['request_payment_id'];
					
			$this->RequestPayment->id = $id;
			if (!$this->RequestPayment->exists($this->RequestPayment->id, $this->user->organization['Organization']['id'])) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
				
			/*
			 * le richieste 
			 * - stato_elaborazione = CLOSE 
			 * - stato_elaborazione_date <= Configure::read('GGArchiveStatics');
			 * vengono richiamate dal Cron::archiveStatistics() 
			 */
			$sql = "UPDATE
						".Configure::read('DB.prefix')."request_payments 
					SET
						stato_elaborazione = '".$stato_elaborazione."',
						stato_elaborazione_date = '".date('Y-m-d')."',
						modified = '".date('Y-m-d H:i:s')."'
					WHERE
						organization_id = ".(int)$this->user->organization['Organization']['id']."
						and id = ".(int)$id;
			self::d($sql, false);
			$result = $this->RequestPayment->query($sql);
			
			/*
			 * estraggo gli eventuali Order e Order state_code da TO-PAYMENT a USER-PAID
			 */	
			App::import('Model', 'RequestPaymentsOrder');
			$RequestPaymentsOrder = new RequestPaymentsOrder;
						 
			switch ($action_submit) {
				case 'toWait': 
					$RequestPaymentsOrder->setOrdersStateCodeByRequestPaymentId($this->user, $id, '', $debug);  // Order.state_code_next TO-PAYMENT
				break;
				case 'toClose':
					/*
					 * non lo permetto +, la rich si chiudo in automatico se tutti gli ordini sono CLOSE
					 */
					$options = [];
					$RequestPaymentsOrder->setOrdersStateCodeByRequestPaymentId($this->user, $id, '', $debug); // calcolo Order.state_code_next 
				break;
			}		
				 				
			$this->myRedirect(['controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id]);
				
		} // end if ($this->request->is('post') || $this->request->is('put')) 
				
		$this->RequestPayment->id = $id;
		if (!$this->RequestPayment->exists($this->RequestPayment->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		/*
		 * estraggo i dettagli di una richiesta di pagamento
		* 	- ordini associati
		*  - voci di spesa generica
		*  - dispensa
		*/
		$results = $this->RequestPayment->getAllDetails($this->user, $id, null);
		// qui non serve $this->set(compact('results'));
		$this->set('request_payment_empty', $this->_ctrl_request_payment_empty($this->user, $results));
		

		self::d($this->requestPaymentResults['RequestPayment']['stato_elaborazione'], $debug);
		
		switch ($this->requestPaymentResults['RequestPayment']['stato_elaborazione']) {
			case 'WAIT':
				$invio_mail = ['Y' => 'Si', 'N' => 'No'];
				$this->set(compact('invio_mail'));
				
				$this->render('admin_edit_stato_elaborazione_to_open_from_wait');
				break;
			case 'OPEN':							
				$this->render('admin_edit_stato_elaborazione_open');
				break;
			case 'CLOSE':
				$this->render('admin_edit_stato_elaborazione_to_open_from_close');
				break;
		}
	}
	
	/*
	 * confermo rich pagamento e invio mail
	 * Order.state_code da TO-PAYMENT (Associato ad una richiesta di pagamento) => USER-PAID (Da saldare da parte dei gasisti)
	 */
	public function admin_edit_stato_elaborazione_to_open_from_wait() {
		
		if ($this->request->is('post') || $this->request->is('put') ) {
		
			$id = $this->request->data['RequestPayment']['request_payment_id'];
			
			$this->RequestPayment->id = $id;
			if (!$this->RequestPayment->exists($this->RequestPayment->id, $this->user->organization['Organization']['id'])) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}		
												
			if($this->request->data['RequestPayment']['invio_mail']=='Y') {

				App::import('Model', 'Mail');
				$Mail = new Mail;
				
				$Email = $Mail->getMailSystem($this->user);
				
				/*
				 * estraggo gli utenti ai quali inviare la mail
				 */
				$sql = "SELECT
					User.id, User.name, User.username, User.email,
				 	SummaryPayment.request_payment_id, SummaryPayment.importo_dovuto, SummaryPayment.importo_richiesto, SummaryPayment.created 	
				FROM
					".Configure::read('DB.portalPrefix')."users as User,
					".Configure::read('DB.prefix')."summary_payments as SummaryPayment 
				WHERE
					SummaryPayment.organization_id = ".(int)$this->user->organization['Organization']['id']."
					and User.organization_id = ".(int)$this->user->organization['Organization']['id']."
					and User.id = SummaryPayment.user_id
					and SummaryPayment.request_payment_id =$id
				ORDER BY ".Configure::read('orderUser');
				self::d($sql, false);
				$summaryPaymentResults = $this->RequestPayment->query($sql);
				
				if(!empty($summaryPaymentResults)) {

					/*
					 * num della richiesta pagamento
					*/
					$options = [];
					$options['conditions'] = ['RequestPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
											  'RequestPayment.id' => $id];
					$options['recursive'] = -1;
					$options['fields'] = ['RequestPayment.num', 'RequestPayment.nota'];
					$numRequestPaymentResults = $this->RequestPayment->find('first', $options);
					$request_payment_num = $numRequestPaymentResults['RequestPayment']['num'];
					
					/*
					 * prepare mail
					 */
					$subject_mail = 'Nuova richiesta di pagamento (numero '.$request_payment_num.')';
					$Email->subject($subject_mail);
					if(!empty($this->user->organization['Organization']['www']))
						$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
					else
						$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);					
					$msg = '';
					$msg .= 'Inviata la mail a<br />';
					foreach ($summaryPaymentResults as $summaryPaymentResult) {
						
							$mail = $summaryPaymentResult['User']['email'];
							$name = $summaryPaymentResult['User']['name'];
							$request_payment_id = $summaryPaymentResult['SummaryPayment']['request_payment_id'];
							$importo_dovuto = $summaryPaymentResult['SummaryPayment']['importo_dovuto'];
							$importo_dovuto = number_format($importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
							$importo_richiesto = $summaryPaymentResult['SummaryPayment']['importo_richiesto'];
							$importo_richiesto = number_format($importo_richiesto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
							
							if(!empty($mail)) {
									
								$body_mail = "c'è una nuova <b>richiesta di pagamento</b> (la numero $request_payment_num) di ".$importo_richiesto.'&nbsp;&euro;.';
								
								$body_mail .= "<br /><br />Collegati ";
								$body_mail .= "al sito ".$this->traslateWww(Configure::read('SOC.site'));
									
								$body_mail .= " e, dopo aver fatto la login, scarica il documento per effettuare il pagamento.";
								
								$body_mail .= '<br /><br />Se effettui il pagamento tramite bonifico indica come <b>causale</b>: Richiesta num '.$request_payment_num.' di '.$name;
										
								if(!empty($numRequestPaymentResults['RequestPayment']['nota'])) 
									$body_mail .= '<br /><br />'.$numRequestPaymentResults['RequestPayment']['nota'];									
								//echo $body_mail; exit;
								
								if(!Configure::read('mail.send'))  $Email->transport('Debug');
								
								$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
								$Email->to($mail);
									
								try {
									$Mail->send($Email, [$mail], $body_mail);
								} catch (Exception $e) {
									CakeLog::write("error", $e, ['mails']);
								}									
							}
							else
								$msg .= $name.' senza indirizzo mail!<br />';
					}
					
					$this->Session->setFlash($msg);
				} // end if(!empty($summaryPaymentResults))
			} // if($this->request->data['RequestPayment']['invio_mail']=='Y') 
							
			$sql = "UPDATE
						`".Configure::read('DB.prefix')."request_payments`
					SET
						stato_elaborazione = 'OPEN',
						stato_elaborazione_date = '".date('Y-m-d')."',
						data_send = '".date('Y-m-d')."',
						modified = '".date('Y-m-d H:i:s')."'
					WHERE
						organization_id = ".(int)$this->user->organization['Organization']['id']."
						and id = ".(int)$id;
			self::d($sql, false);
			$result = $this->RequestPayment->query($sql);
	
			App::import('Model', 'RequestPaymentsOrder');
			$RequestPaymentsOrder = new RequestPaymentsOrder;

			$RequestPaymentsOrder->setOrdersStateCodeByRequestPaymentId($this->user, $id, '');
	
			$this->myRedirect(['controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id]);
		} // if ($this->request->is('post') || $this->request->is('put') )
	}
	
	/*
	 * da richiesta CLOSE => OPEN 
	 * Order.state_code da CLOSE => calcolo
	 */	
	public function admin_edit_stato_elaborazione_to_open_from_close() {
	
		if ($this->request->is('post') || $this->request->is('put') ) {
		
			$id = $this->request->data['RequestPayment']['request_payment_id'];
			
			$this->RequestPayment->id = $id;
			if (!$this->RequestPayment->exists($this->RequestPayment->id, $this->user->organization['Organization']['id'])) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
							
			$sql = "UPDATE
						`".Configure::read('DB.prefix')."request_payments`
					SET
						stato_elaborazione = 'OPEN',
						stato_elaborazione_date = '".date('Y-m-d')."',
						modified = '".date('Y-m-d H:i:s')."'
					WHERE
						organization_id = ".(int)$this->user->organization['Organization']['id']."
						and id = ".(int)$id;
			self::d($sql, false);
			$result = $this->RequestPayment->query($sql);
	
			App::import('Model', 'RequestPaymentsOrder');
			$RequestPaymentsOrder = new RequestPaymentsOrder;
			
			$RequestPaymentsOrder->setOrdersStateCodeByRequestPaymentId($this->user, $id, '');
	
			$this->myRedirect(['controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id]);
		} // if ($this->request->is('post') || $this->request->is('put') )
	}
		
	/*
	 * estrai ordini TO-REQUEST-PAYMENT per portarle a TO-PAYMENT
	*
	*  $RequestPaymentsOrders, $SummaryPayments
	*/
	public function admin_add_orders($id=null) {
		
		$debug = false;
		
		$this->RequestPayment->id = $id;
		if (!$this->RequestPayment->exists($this->RequestPayment->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	
		
		if ($this->request->is('post') || $this->request->is('put')) {
				
			$order_id_selected = $this->request->data['RequestPayment']['order_id_selected'];
				
			self::l('order_id_selected '.$order_id_selected, $debug);	
			
			/*
			 * per ogni USER 
			 * 		INSERT un occorrenza RequestPaymentsOrders con il totale da pagare
			 * 		UPDATE aggiorno l'occorrenza RequestPaymentsOrders con il totale da pagare
			 * 
			 * se SummaryOrder.modalita != 'DEFINED' user ha gia' pagato l'ordine
			 * 	magari dal Cassiere
			*/
			App::import('Model', 'SummaryPayment');
			$SummaryPayment = new SummaryPayment;
				
			if(!empty($order_id_selected)) {
				$sql = "SELECT sum(importo) as tot_importo, user_id
						FROM ".Configure::read('DB.prefix')."summary_orders as SummaryOrder
						WHERE organization_id = ".(int)$this->user->organization['Organization']['id']."
							AND order_id in (".$order_id_selected.")
							AND saldato_a is null
						GROUP BY user_id ORDER BY user_id ";
				self::l($sql, $debug);
				$results = $SummaryPayment->query($sql);
				if(empty($results))
					self::l('Nessun records trovato ', $debug);
				foreach ($results as $result) {
					
					$tot_importo = $result[0]['tot_importo'];
					
					$data = null;
					
					/*
					 * ctrl se esiste gia' un'occorrenza in SummaryPayment, se SI => update  
					 * */
					$conditions = ['SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
									'SummaryPayment.user_id' => (int)$result['SummaryOrder']['user_id'],
									'SummaryPayment.request_payment_id' => (int)$id];
					$resultCtrl = $SummaryPayment->find('first', ['fields' => ['id','importo_dovuto','importo_richiesto'], 'conditions' => $conditions, 'recursive' => -1]);
					if(!empty($resultCtrl)) {
						// UPDATE
						$data['SummaryPayment']['id'] = $resultCtrl['SummaryPayment']['id'];
						$data['SummaryPayment']['importo_dovuto'] = ($resultCtrl['SummaryPayment']['importo_dovuto'] + $tot_importo);
						$data['SummaryPayment']['importo_richiesto'] = $data['SummaryPayment']['importo_dovuto'];
						
						self::l('UPDATE importo totale in SummaryPayment - importo_dovuto '.$data['SummaryPayment']['importo_dovuto'].' - importo_richiesto '.$data['SummaryPayment']['importo_richiesto'].' per user_id '.$result['SummaryOrder']['user_id'], $debug);
					}
					else {
						// INSERT
						$data['SummaryPayment']['importo_dovuto'] = $tot_importo;
						$data['SummaryPayment']['importo_richiesto'] = $data['SummaryPayment']['importo_dovuto'];

						self::l('INSERT importo totale in SummaryPayment - importo_dovuto '.$data['SummaryPayment']['importo_dovuto'].' - importo_richiesto '.$data['SummaryPayment']['importo_richiesto'].' per user_id '.$result['SummaryOrder']['user_id'], $debug);

						$data['SummaryPayment']['importo_pagato'] = Configure::read('DB.field.double.empty');
					}
					$data['SummaryPayment']['organization_id'] = $this->user->organization['Organization']['id'];
					$data['SummaryPayment']['request_payment_id'] = $id;
					$data['SummaryPayment']['user_id'] = $result['SummaryOrder']['user_id'];

					self::l($data, $debug);
	
					$SummaryPayment->create();
					if(!$SummaryPayment->save($data))
						$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
				}
			} // end if(!empty($order_id_selected))
		
			App::import('Model', 'RequestPaymentsOrder');
			$RequestPaymentsOrder = new RequestPaymentsOrder;
				
			$msg = "";
			$arr_order_id_selected = explode(',',$order_id_selected);
			foreach($this->request->data['RequestPayment'] as $key => $data) {
				$order_id = $key;

				self::l('order_id '.$order_id, $debug);
				
				if(isset($order_id) && in_array($order_id, $arr_order_id_selected)) {
						
					/*
					 * ottengo ORDER per avere delivery_id
					* */
					App::import('Model', 'Order');
					$Order = new Order;
						
					$Order->id = $order_id;
					if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
						$msg .= "<br />ordine ".$order_id." non esiste!";
					}
					else {
						$conditions = ['Order.organization_id' => (int)$this->user->organization['Organization']['id'],
										'Order.isVisibleBackOffice' => 'Y',
										'Order.id' => (int)$order_id];
						$Order->unbindModel(['belongsTo' => ['SuppliersOrganization','Delivery']]);
						$resultOrder = $Order->find('first', ['conditions' => $conditions,'recursive' => 0]);
		
						/*
						 * creo per ogni ORDINE un occorrenza RequestPaymentsOrders
						*/
						$data = null;
						$data['RequestPaymentsOrder']['organization_id'] = $this->user->organization['Organization']['id'];
						$data['RequestPaymentsOrder']['request_payment_id'] = $id;
						$data['RequestPaymentsOrder']['order_id'] = $order_id;
						$data['RequestPaymentsOrder']['delivery_id'] = $resultOrder['Order']['delivery_id'];
		
						self::l($data, $debug);
						
						$RequestPaymentsOrder->create();
						if(!$RequestPaymentsOrder->save($data)) {
							$msg .= "<br />ordine ".$order_id." in errore!";
						}
		
						/*
						 * aggiorno stato ORDER
						 * 	da TO-REQUEST-PAYMENT (Possibilità di richiederne il pagamento) a TO-PAYMENT (Associato ad una richiesta di pagamento)
						 */
						App::import('Model', 'OrderLifeCycle');
						$OrderLifeCycle = new OrderLifeCycle;
						
						$state_code_next = 'TO-PAYMENT';						
						$OrderLifeCycle->stateCodeUpdate($this->user, $order_id, $state_code_next, null, $debug);
						
					} // end if (!$Order->exists($Order->id, $this->user->organization['Organization']['id']))
				}
			} // end foreach
		
			if(!empty($msg))
				$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
			else
				$this->Session->setFlash(__('The request payments orders has been saved'));
		
			self::d($msg, false);
				 
			/*
			 * aggiorno lo stato delle consegne, se tutti gli ordini sono a CLOSE setto lo stato_elaborazione CLOSE
			 * */
			$utilsCrons = new UtilsCrons(new View(null));
			$utilsCrons->deliveriesStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
			
			if(!$debug) $this->myRedirect(['controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id]);
			
		} // end if ($this->request->is('post'))
		
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options = ['orders' => true, 'storerooms' => false, 'summaryOrders' => false,
						 'suppliers' => true,'referents' => true, 
						 'articlesOrdersInOrder'=>false];  // NON estraggo gli articoli dell'ordine
			
		$conditions = ['Delivery' => ['Delivery.isVisibleBackOffice' => 'Y',
												'Delivery.sys'=> 'N',
												'Delivery.stato_elaborazione'=> 'OPEN'],
							'Order' => ['Order.isVisibleBackOffice' => 'Y',
											 'Order.state_code' => 'TO-REQUEST-PAYMENT']];
		
		$results = $Delivery->getDataTabs($this->user,$conditions,$options);
		$this->set(compact('results'));
	}
	
	public function admin_add_storeroom() {
		
		$id = $this->request->pass['id']; // lo ricavo cosi' perche' nella queryString ho FilterStoreroomDeliveryId
		
		$this->RequestPayment->id = $id;
		if (!$this->RequestPayment->exists($this->RequestPayment->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}		
		

		/*
		 * ctrl configurazione Organization
		*/
		if($this->user->organization['Organization']['hasStoreroom']=='N' || $this->user->organization['Organization']['hasStoreroomFrontEnd']=='N') {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		/*
		 * ctrl se esiste l'utente dispensa
		 */
		App::import('Model', 'Storeroom');
		$Storeroom = new Storeroom;
		
		$this->storeroomUser = $Storeroom->getStoreroomUser($this->user);
		if(empty($this->storeroomUser)) {
			$this->Session->setFlash(__('StoreroomNotFound'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {		

			$sql = "SELECT
						sum(qta * prezzo) as importo, user_id
					FROM 
						".Configure::read('DB.prefix')."storerooms as Storeroom 
					WHERE
						Storeroom.organization_id = ".(int)$this->user->organization['Organization']['id']."
						and Storeroom.user_id != ".$this->storeroomUser['User']['id']."
						and Storeroom.stato = 'Y'
						and Storeroom.delivery_id = ".$this->request['data']['RequestPayment']['delivery_id']." 
					GROUP BY user_id
					ORDER BY user_id ";
			self::d($sql, false);
			$results = $Storeroom->query($sql);
			
			App::import('Model', 'SummaryPayment');
			$SummaryPayment = new SummaryPayment;
			
			foreach ($results as $i => $result) {
				$data = null;
				
				/*
				 * ctrl se esiste gia' un'occorrenza in SummaryPayment, se SI => update
				 * */
				$conditions = ['SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
									'SummaryPayment.user_id' => (int)$result['Storeroom']['user_id'],
									'SummaryPayment.request_payment_id' => (int)$id];
				$resultCtrl = $SummaryPayment->find('first', ['conditions' => $conditions,'recursive' => -1]);
				if(!empty($resultCtrl)) {
					// UPDATE
					$data['SummaryPayment']['id'] = $resultCtrl['SummaryPayment']['id'];
					$data['SummaryPayment']['importo_pagato'] = $resultCtrl['SummaryPayment']['importo_pagato'];
					$data['SummaryPayment']['modalita'] = $resultCtrl['SummaryPayment']['modalita'];
					$importo = ($resultCtrl['SummaryPayment']['importo_dovuto'] + $result['0']['importo']);
				}
				else {
					// INSERT
					$importo = $result['0']['importo'];
					$data['SummaryPayment']['importo_pagato'] = 0;
					$data['SummaryPayment']['modalita'] = 'DEFINED';
				}
				$data['SummaryPayment']['importo_dovuto'] = $importo; // $this->importoToDatabase($importo);
				$data['SummaryPayment']['importo_richiesto'] = $importo; // $this->importoToDatabase($importo);
				
				$data['SummaryPayment']['organization_id'] = $this->user->organization['Organization']['id'];
				$data['SummaryPayment']['request_payment_id'] = $id;
				$data['SummaryPayment']['user_id'] = $result['Storeroom']['user_id'];
				
				$SummaryPayment->create();
				if(!$SummaryPayment->save($data))
					$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));	
			} // end foreach ($results as $i => $result)

			App::import('Model', 'RequestPaymentsStoreroom');
			$RequestPaymentsStoreroom = new RequestPaymentsStoreroom;
			$data = null;
			$data['RequestPaymentsStoreroom']['organization_id'] = (int)$this->user->organization['Organization']['id'];
			$data['RequestPaymentsStoreroom']['request_payment_id'] = $id;
			$data['RequestPaymentsStoreroom']['delivery_id'] = $this->request['data']['RequestPayment']['delivery_id'];
				
			$RequestPaymentsStoreroom->create();
			if($RequestPaymentsStoreroom->save($data)) {
				/*
				 * aggiorno la consegna con isToStoreroomPay = Y
				 */
				App::import('Model', 'Delivery');
				$Delivery = new Delivery;
				
				$data = null;
				$data['Delivery']['id'] = $this->request['data']['RequestPayment']['delivery_id'];
				$data['Delivery']['organization_id'] = (int)$this->user->organization['Organization']['id'];
				$data['Delivery']['isToStoreroomPay'] = 'Y';
				$data['Delivery']['isToStoreroom'] = 'Y';
				$data['Delivery']['isVisibleBackOffice'] = 'Y';
				$data['Delivery']['isVisibleFrontEnd'] = 'Y';
				$data['Delivery']['Delivery.sys'] = 'N';

				$Delivery->create();
				if($Delivery->save($data)) 
					$this->Session->setFlash(__('The request payments storeroom has been saved'));				
				else
					$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
				
				$this->myRedirect(['controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id]);
			}
			else		
				$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
		} // end if ($this->request->is('post') || $this->request->is('put')) 
		
		$FilterRequestPaymentDeliveryId = null;
		
		$deliveries = $Storeroom->deliveriesToRequestPayment($this->user);	
		if(empty($deliveries)) {
			$this->Session->setFlash(__('NotFoundDeliveries'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}		
		$this->set(compact('deliveries'));
		
		$resultsFound = '';
		$results = [];
		
		/* recupero dati dalla Session gestita in appController::beforeFilter */
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'DeliveryId')) {
			$FilterRequestPaymentDeliveryId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'DeliveryId');
		
			$conditions = ['Storeroom.organization_id' => (int)$this->user->organization['Organization']['id'],
							'Storeroom.user_id != ' => $this->storeroomUser['User']['id'],
							'Storeroom.delivery_id > ' => 0,
							'Storeroom.stato' => 'Y',
							'Storeroom.delivery_id'=>$FilterRequestPaymentDeliveryId];
				
			$orderBy = ['Storeroom.delivery_id, '.Configure::read('orderUser').', Storeroom.name'];

			$Storeroom->Delivery->unbindModel(['hasMany' => ['Order']]);			
			$Storeroom->Article->unbindModel(['hasOne' => ['ArticlesOrder']]);
			$Storeroom->Article->unbindModel(['hasMany' => ['ArticlesOrder']]);
			$Storeroom->Article->unbindModel(['hasAndBelongsToMany' => ['Order']]);
			$Storeroom->User->unbindModel(['hasMany' => ['Cart']]);
			$results = $Storeroom->find('all', ['conditions' => $conditions,'order' => $orderBy,'recursive' => 1]);
			
			/*
			 * aggiungo informazione sul produttore
			 */
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			if(!empty($results)) {
				foreach ($results as $i => $result) {
					$conditions = ['SuppliersOrganization.id' => $result['Article']['supplier_organization_id']];
					$userTmp->organization['Organization']['id'] =  $result['Article']['organization_id'];
					$suppliersOrganization = $SuppliersOrganization->getSuppliersOrganization($userTmp, $conditions);
					$results[$i]['SuppliersOrganization'] = current($suppliersOrganization);
				}
			}
			else 
				$resultsFound = 'N';
		}
		
		/* filtro */
		$this->set('FilterRequestPaymentDeliveryId', $FilterRequestPaymentDeliveryId);
		$this->set('resultsFound', $resultsFound);
		$this->set(compact('results'));
	}	
	
	public function admin_delete_generic($id = null) {
	
		$debug = false;
		
		App::import('Model', 'RequestPaymentsGeneric');
		$RequestPaymentsGeneric = new RequestPaymentsGeneric;
		
		$RequestPaymentsGeneric->id = $id;
		if (!$RequestPaymentsGeneric->exists($RequestPaymentsGeneric->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * estraggo RequestPaymentsGeneric da cancellare
		 */
		$options =  [];
		$options['conditions'] = ['RequestPaymentsGeneric.organization_id'=>(int)$this->user->organization['Organization']['id'],
									'RequestPaymentsGeneric.id'=> $id];
		$options['recursive'] = -1;
		$results = $RequestPaymentsGeneric->find('first', $options);

		self::d($results, $debug);
		
		if ($RequestPaymentsGeneric->delete()) {
			$this->Session->setFlash(__('Delete RequestPaymentsGeneric'));
		
			/*
			 * aggiorno il totale in SummaryPayment
			*/
				
			App::import('Model', 'SummaryPayment');
			$SummaryPayment = new SummaryPayment;
			
			$options = [];
			$options['conditions'] = ['SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
										'SummaryPayment.user_id' => $results['RequestPaymentsGeneric']['user_id'],
										'SummaryPayment.request_payment_id' => $results['RequestPaymentsGeneric']['request_payment_id']];
			$options['recursive'] = -1;
			$summaryPaymentResult= $SummaryPayment->find('first', $options);
			
			/*
			 * se gli importi RequestPaymentsGeneric.importo = SummaryPayment.importo_richiesto
			 * cancello SummaryPayment
			 */
			if($results['RequestPaymentsGeneric']['importo']==$summaryPaymentResult['SummaryPayment']['importo_richiesto']) {
				
				self::d('RequestPaymentsGeneric.importo ('.$results['RequestPaymentsGeneric']['importo'].') = SummaryPayment.importo_richiesto '.$summaryPaymentResult['SummaryPayment']['importo_richiesto'].' => cancello SummaryPayment', $debug);
				
				$SummaryPayment->id = $summaryPaymentResult['SummaryPayment']['id'];
				$SummaryPayment->delete();
			}
			else {
				$importo_dovuto = ($summaryPaymentResult['SummaryPayment']['importo_dovuto'] - ($results['RequestPaymentsGeneric']['importo']));
				$importo_richiesto = ($summaryPaymentResult['SummaryPayment']['importo_richiesto'] - ($results['RequestPaymentsGeneric']['importo']));
				
				self::d('RequestPaymentsGeneric.importo ('.$results['RequestPaymentsGeneric']['importo'].') != SummaryPayment.importo_richiesto '.$summaryPaymentResult['SummaryPayment']['importo_richiesto'].' => aggiorno SummaryPayment: '.$importo_richiesto, $debug);
				
				/*
				 * sottraggo da SummaryPayment.importo_richiesto 
				 * 	RequestPaymentsGeneric.importo che viene eliminato
				 */
				
				$data = [];
				$data['SummaryPayment'] = $summaryPaymentResult['SummaryPayment'];
				$data['SummaryPayment']['importo_dovuto'] = $importo_dovuto;
				$data['SummaryPayment']['importo_richiesto'] = $importo_richiesto;
					
				self::d($data, $debug);
				
				$SummaryPayment->create();
				$SummaryPayment->save($data);
			}
		
		}
		else
			$this->Session->setFlash(__('RequestPaymentsGeneric was not deleted'));
		
		/*
		 * gli passo open_details cosi apro il box con il dettaglio ordini / voci di spesa
		*/
		$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=RequestPayments&action=edit&id='.$results['RequestPaymentsGeneric']['request_payment_id'].'&open_details=Y';
		
		if($debug) {
			self::d($url, $debug);
			exit;
		}
		$this->myRedirect($url);
   }  

	/*
	 * estraggo SummaryPayment users che hanno gia' saldato per quella richiesta di pagamento
	 * estraggo SummaryOrder   users legati all'ordine
	 * ctrl se ci sono users dell'ordine che hanno pagato la richiesta di pagamento => se SI li blocco
	 * estraggo SummaryOrder users che hanno gia' saldato per quell'ordine => se si msg che SummaryOrders.saldato_a = TESORIERE verranno eliminati
	 */
	public function admin_delete_order_pre($id = null) {
		
		$debug = false;
		
		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;
		
		$RequestPaymentsOrder->id = $id;
		if (!$RequestPaymentsOrder->exists($RequestPaymentsOrder->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	
		
		$results = $RequestPaymentsOrder->ctrlDeleteOrders($this->user, $id, $debug);
		
		$user_ids_just_saldato_summary_payments = $results['user_ids_just_saldato_summary_payments'];
		$user_ids_just_saldato_summary_orders = $results['user_ids_just_saldato_summary_orders'];

		
		if(empty($user_ids_just_saldato_summary_payments) && empty($user_ids_just_saldato_summary_orders)) {
			$url = Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=RequestPayments&action=delete_order&id='.$id;
			if(!$debug)
				$this->myRedirect($url);
			else
				echo $url;
		}
		else {
			$this->set('id', $id);
			$this->set('user_ids_just_saldato_summary_payments', $user_ids_just_saldato_summary_payments);
			$this->set('user_ids_just_saldato_summary_orders', $user_ids_just_saldato_summary_orders);
		}
	}
	
	public function admin_delete_order($id = null) {
	
		$debug = false;
		
		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;
		
		$RequestPaymentsOrder->id = $id;
		if (!$RequestPaymentsOrder->exists($RequestPaymentsOrder->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * estraggo RequestPaymentsOrder da cancellare
		 */
		$options =  [];
		$options['conditions'] = ['RequestPaymentsOrder.organization_id'=>(int)$this->user->organization['Organization']['id'],
								  'RequestPaymentsOrder.id'=> $id];
		$options['recursive'] = -1;
		$results = $RequestPaymentsOrder->find('first', $options);
		self::d($results, $debug);
		
		if(!empty($results)) { 
		
			$order_id = $results['RequestPaymentsOrder']['order_id'];
			
			
			/*
			 * riporto ordinie a 'PROCESSED-TESORIERE' (In carico al tesoriere) => popolo summary_orders
			 * $SummaryPayment->delete_order() aggiorno il totale in SummaryPayment, se il gasista aveva solo quell'ordine SummaryPayment.stato = DAPAGARE
			 * $SummaryOrderLifeCycle->changeRequestPayment($this->user, $order_id, $operation='DELETE', $opts); cancello i pagamenti gia' fatti del tesoriere SummaryOrder.saldato_a = TESORIERE
			 */
			App::import('Model', 'OrderLifeCycle');
			$OrderLifeCycle = new OrderLifeCycle;
	
			$OrderLifeCycle->stateCodeUpdate($this->user, $order_id, 'PROCESSED-TESORIERE');
							
			/*
	         * aggiorno lo stato delle consegne
	         * ctrl se quelle chiuse hanno tutti gli ordini CLOSE
	         * gli ordini riportati indietro se erano CLOSE potevano avere la consegna aperta
	         * */
			App::import('Model', 'DeliveryLifeCycle');
			$DeliveryLifeCycle = new DeliveryLifeCycle;
			
			$DeliveryLifeCycle->deliveriesToOpen($this->user);	
					
			if ($RequestPaymentsOrder->delete()) {
				$this->Session->setFlash(__('Delete RequestPaymentsOrder'));
			}
			else
				$this->Session->setFlash(__('RequestPaymentsOrder was not deleted'));
				
			/*
			 * gli passo open_details cosi apro il box con il dettaglio ordini / voci di spesa
			*/
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=RequestPayments&action=edit&id='.$results['RequestPaymentsOrder']['request_payment_id'].'&open_details=Y';
			
			if($debug) 
				self::xx($url, $debug);
			
			$this->myRedirect($url);
				
		} // end if(!empty($results))
		else
			$this->Session->setFlash(__('RequestPaymentsOrder was not deleted'));		
		
   }  
   
	public function admin_add_generic($id=null) {
		
		$debug = false;
		
		$this->RequestPayment->id = $id;
		if (!$this->RequestPayment->exists($this->RequestPayment->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		App::import('Model', 'RequestPaymentsGeneric');
		$RequestPaymentsGeneric = new RequestPaymentsGeneric;
				
		if ($this->request->is('post') || $this->request->is('put')) {

			$continua=true;
			App::import('Model', 'RequestPaymentsGeneric');
			$RequestPaymentsGeneric = new RequestPaymentsGeneric;
				
			self::d('dest_options_qta '.$this->request->data['RequestPaymentsGeneric']['dest_options_qta'], $debug);
		
			// if(isset($this->request->data['RequestPaymentsGeneric']['users']) && !empty($this->request->data['RequestPaymentsGeneric']['users']))
				
				/*
				 * per ogni user con importo valorizzato creo un occorrenza
				 */
				switch ($this->request->data['RequestPaymentsGeneric']['dest_options_qta']) {
					case 'SOME_DIFF':
						$users = $this->request->data['RequestPaymentsGeneric']['Importo'];
						foreach ($users as $user_id => $importo) {
							if($importo!='0,00') {
								
								$importo = $this->importoToDatabase($importo);
								self::d("Tratto lo user ".$user_id." con importo ".$importo, $debug);
									
								/*
								 * creo occorrenza in RequestPaymentsGeneric
								*/
								$data = null;
								$data['RequestPaymentsGeneric']['organization_id'] = (int)$this->user->organization['Organization']['id'];
								$data['RequestPaymentsGeneric']['request_payment_id'] = $id;
								$data['RequestPaymentsGeneric']['user_id'] = $user_id;
								$data['RequestPaymentsGeneric']['name'] = $this->request['data']['RequestPaymentsGeneric']['name'];
								$data['RequestPaymentsGeneric']['importo'] = $importo;			
								
								self::d($data, $debug);
								
								$RequestPaymentsGeneric->create();
								if($RequestPaymentsGeneric->save($data))
									$continua=true;
								else
									$continua=false;
								
								/*
								 * creo occorrenza in SummaryPayment
								*/
								
								App::import('Model', 'SummaryPayment');
								$SummaryPayment = new SummaryPayment;

								$data = null;
								/*
								 * ctrl se esiste gia' un'occorrenza in SummaryPayment, se SI => update
								* */
								$options = [];
								$options['conditions'] = ['SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
														  'SummaryPayment.user_id' => (int)$user_id,
														  'SummaryPayment.request_payment_id' => (int)$id];
								$options['fields'] = ['SummaryPayment.id','SummaryPayment.importo_dovuto','SummaryPayment.importo_richiesto','SummaryPayment.importo_pagato'];
								$options['recursive'] = -1;
								$resultCtrl = $SummaryPayment->find('first', $options);
								if(!empty($resultCtrl)) {
									
									self::d('Trovata gia un occorrenza per lo user '.$user_id.' in SummaryPayment => UPDATE di importo '.$resultCtrl['SummaryPayment']['importo_dovuto'].' + '.$importo, $debug);
									
									// UPDATE
									$data['SummaryPayment']['id'] = $resultCtrl['SummaryPayment']['id'];
									$data['SummaryPayment']['importo_dovuto'] = ($resultCtrl['SummaryPayment']['importo_dovuto'] + $importo);
									$data['SummaryPayment']['importo_richiesto'] = $data['SummaryPayment']['importo_dovuto'];	
									$data['SummaryPayment']['importo_pagato'] = $resultCtrl['SummaryPayment']['importo_pagato'];				
								}
								else {
									
									self::d('NON Trovata un occorrenza per lo user '.$user_id.' in SummaryPayment => INSERT ', $debug);

									// INSERT
									$data['SummaryPayment']['importo_dovuto'] = $importo;  // $this->importoToDatabase($importo);
									$data['SummaryPayment']['importo_richiesto'] = $importo;  // $this->importoToDatabase($importo);
									$data['SummaryPayment']['importo_pagato'] = Configure::read('DB.field.double.empty');

								}
								$data['SummaryPayment']['organization_id'] = $this->user->organization['Organization']['id'];
								$data['SummaryPayment']['request_payment_id'] = $id;
								$data['SummaryPayment']['user_id'] = $user_id;
							
								self::d($data, $debug);
								$SummaryPayment->create();
								$SummaryPayment->save($data);
																
							} // end if($importo!='0,00') 
						} // foreach ($users as $user_id => $importo) 
					break;
					// ALL / SOME
					default:
					
						self::d($this->request->data['RequestPaymentsGeneric'], $debug);
						
						/*
						 * creo occorrenza in SummaryPayment
						*/
						if($this->request->data['RequestPaymentsGeneric']['dest_options_qta']=='SOME')
							$users = $this->request->data['RequestPaymentsGeneric']['users'];
						else 
						if($this->request->data['RequestPaymentsGeneric']['dest_options_qta']=='ALL') {
							App::import('Model', 'User');
							$User = new User;
							
							$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user')];
							$users = $User->getUsersList($this->user, $conditions);
						}

						$importo = $this->importoToDatabase($this->request['data']['RequestPaymentsGeneric']['importo']);
						
						App::import('Model', 'SummaryPayment');
						$SummaryPayment = new SummaryPayment;
						foreach ($users as $key => $value) {
							
							if($this->request->data['RequestPaymentsGeneric']['dest_options_qta']=='SOME')
								$user_id = $value;
							else
								$user_id = $key;
								
							/*
							 * creo occorrenza in RequestPaymentsGeneric
							*/
							$data = null;
							$data['RequestPaymentsGeneric']['organization_id'] = (int)$this->user->organization['Organization']['id'];
							$data['RequestPaymentsGeneric']['request_payment_id'] = $id;
							$data['RequestPaymentsGeneric']['user_id'] = $user_id;
							$data['RequestPaymentsGeneric']['name'] = $this->request['data']['RequestPaymentsGeneric']['name'];
							$data['RequestPaymentsGeneric']['importo'] = $this->request['data']['RequestPaymentsGeneric']['importo'];
							
							$RequestPaymentsGeneric->create();
							if($RequestPaymentsGeneric->save($data))
								$continua=true;
							else
								$continua=false;
							
							$data = null;
							/*
							 * ctrl se esiste gia' un'occorrenza in SummaryPayment, se SI => update
							* */
							$options = [];
							$options['conditions'] = ['SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
														'SummaryPayment.user_id' => (int)$user_id,
														'SummaryPayment.request_payment_id' => (int)$id];
							$options['fields'] = ['SummaryPayment.id','SummaryPayment.importo_dovuto','SummaryPayment.importo_richiesto','SummaryPayment.importo_pagato'];
							$options['recursive'] = -1;
							$resultCtrl = $SummaryPayment->find('first', $options);
							if(!empty($resultCtrl)) {
								// UPDATE
								$data['SummaryPayment']['id'] = $resultCtrl['SummaryPayment']['id'];
								$data['SummaryPayment']['importo_dovuto'] = ($resultCtrl['SummaryPayment']['importo_dovuto'] + $importo);
								$data['SummaryPayment']['importo_richiesto'] = $data['SummaryPayment']['importo_dovuto'];
								$data['SummaryPayment']['importo_pagato'] = $resultCtrl['SummaryPayment']['importo_pagato'];
							}
							else {
								// INSERT
								$data['SummaryPayment']['importo_dovuto'] = $importo;  // $this->importoToDatabase($importo);
								$data['SummaryPayment']['importo_richiesto'] = $importo;  // $this->importoToDatabase($importo);
								$data['SummaryPayment']['importo_pagato'] = Configure::read('DB.field.double.empty');
							}
							$data['SummaryPayment']['organization_id'] = $this->user->organization['Organization']['id'];
							$data['SummaryPayment']['request_payment_id'] = $id;
							$data['SummaryPayment']['user_id'] = $user_id;
			
							self::d($data, $debug);
							$SummaryPayment->create();
							$SummaryPayment->save($data);
						} // end foreach ($users as $key => $value)
					break;							
				} // switch($this->request->data['RequestPaymentsGeneric']['dest_options_qta']

				if($continua)
					$this->Session->setFlash(__('The request payments generic has been saved'));
				else
					$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));

				if(!$debug) $this->myRedirect(['controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id]);
		} // if ($this->request->is('post') || $this->request->is('put'))
		
		$dest_options_qta = ['ALL' => 'A tutti',
							  'SOME' => 'Ad alcuni',
							  'SOME_DIFF' => 'Ad alcuni con importi diversi'];
		
		$this->set(compact('dest_options_qta'));
		
		App::import('Model', 'User');
		$User = new User;
		
		$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user')];
		$users = $User->getUsersList($this->user, $conditions);
		$this->set('users',$users);	
	}	

	/*
	 * riporto le consegne da CLOSE e OPEN 
	 * riporto gli ordini da 'USER-PAID'  (Da saldare da parte dei gasisti)         a PROCESSED-TESORIERE (In carico al tesoriere)
	 * riporto gli ordini da 'TO-PAYMENT' (Associato ad una richiesta di pagamento) a PROCESSED-TESORIERE (In carico al tesoriere)
	 *	pilisco k_summary_orders con saldato_a = TESORIERE
	 */			
	public function admin_delete($id = null) {
		
		$debug = false;
		
		$this->RequestPayment->id = $id;
		if (!$this->RequestPayment->exists($this->RequestPayment->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if ($this->request->is('post') || $this->request->is('put') ) {
	
			App::import('Model', 'RequestPaymentsOrder');
			$RequestPaymentsOrder = new RequestPaymentsOrder;

			/*
			 * riporto ordinie a 'PROCESSED-TESORIERE' (In carico al tesoriere) => popolo summary_orders
			 * $SummaryOrderLifeCycle->changeRequestPayment($this->user, $order_id, $operation='DELETE', $opts); cancello i pagamenti gia' fatti del tesoriere SummaryOrder.saldato_a = TESORIERE
			 */
			$RequestPaymentsOrder->setOrdersDeleteByRequestPaymentId($this->user, $id);
			
			/*
			 * riporto le consegne a isToStoreroomPay = N
			 */
			if($this->user->organization['Organization']['hasStoreroom']=='Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {

				App::import('Model', 'Delivery');
				
				App::import('Model', 'RequestPaymentsStoreroom');
				$RequestPaymentsStoreroom = new RequestPaymentsStoreroom;
			
				$options = [];
				$options['conditions'] = ['RequestPaymentsStoreroom.organization_id' => $this->user->organization['Organization']['id'],
										  'RequestPaymentsStoreroom.request_payment_id' => $id];
				$options['recursive'] = -1;
				$requestPaymentsStoreroomResults = $RequestPaymentsStoreroom->find('all', $options);
				foreach($requestPaymentsStoreroomResults as $requestPaymentsStoreroomResult) {

					$sql = "UPDATE
								".Configure::read('DB.prefix')."deliveries 
							SET
								isToStoreroomPay = 'N' , 
								modified = '".date('Y-m-d H:i:s')."'
							WHERE
								organization_id = ".(int)$this->user->organization['Organization']['id']."
								and id = ".$requestPaymentsStoreroomResult['RequestPaymentsStoreroom']['delivery_id'];
					self::d($sql, $debug);
					$resultUpdate = $this->RequestPayment->query($sql);
				} // loop requestPaymentsStoreroomResults
			} // end Storeroom
			
			/*
	         * aggiorno lo stato delle consegne
	         * ctrl se quelle chiuse hanno tutti gli ordini CLOSE
	         * gli ordini riportati indietro se erano CLOSE potevano avere la consegna aperta
	         * */
			App::import('Model', 'DeliveryLifeCycle');
			$DeliveryLifeCycle = new DeliveryLifeCycle;
			
			$DeliveryLifeCycle->deliveriesToOpen($this->user);	
									
			if($this->RequestPayment->delete()) 
				$this->Session->setFlash(__('Delete Request Payment'));
			else
				$this->Session->setFlash(__('Request payment was not deleted'));
			if(!$debug) $this->myRedirect(['action' => 'index']);
		} // end POST
		
		/*
		 * estraggo gli utenti che hanno gia' concluso il pagamento 
		 * SummaryPayment.stato != 'DAPAGARE' cosi' prendo tutti i 'SOLLECITO1','SOLLECITO2','PAGATO','SOSPESO'
		*/
		$sql = "SELECT
					User.id, User.name, User.username, User.email,
				 	SummaryPayment.* 
				FROM
					".Configure::read('DB.portalPrefix')."users as User,
					".Configure::read('DB.prefix')."summary_payments as SummaryPayment
				WHERE
					SummaryPayment.organization_id = ".(int)$this->user->organization['Organization']['id']."
					and User.organization_id = ".(int)$this->user->organization['Organization']['id']."
					and User.id = SummaryPayment.user_id
					and SummaryPayment.request_payment_id = $id
					and SummaryPayment.stato != 'DAPAGARE'
				ORDER BY ".Configure::read('orderUser');
		self::d($sql, false);
		try {
			$summaryPaymentResults = $this->RequestPayment->query($sql);
			$this->set('summaryPaymentResults',$summaryPaymentResults);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}	

		/*
		 * estraggo i dettagli di una richiesta di pagamento
		* 	- ordini associati
		*  - voci di spesa generica
		*  - dispensa
		*/
		$results = $this->RequestPayment->getAllDetails($this->user, $id, null);
		$this->set(compact('results'));
		$this->set('request_payment_empty', $this->_ctrl_request_payment_empty($this->user, $results));
		
	}

	public function admin_view() {

		/*
		 * permission
		 */
		App::import('Model', 'Order');
		$Order = new Order;
				
		/*
		 * dati ordine
		 */		
		$Order->id = $this->order_id;
		if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$options = ['conditions' => ['Order.' . $Order->primaryKey => $this->order_id]];
		$resultsOrder = $Order->find('first', $options);
		$this->set(compact('resultsOrder'));
		
		/*
		 * dati richiesta di pagamento
		 * ricavo il request_payment_id dall'order_id 
		 */		
		App::import('Model', 'RequestPaymentsOrder');
		$RequestPaymentsOrder = new RequestPaymentsOrder;
		
		$conditions = ['RequestPaymentsOrder.organization_id' => $this->user->organization['Organization']['id'],
						'RequestPaymentsOrder.order_id' => $resultsOrder['Order']['id']];
		$RequestPaymentsOrder->unbindModel(['belongsTo' => ['Order']]);
		$requestPaymentResults = $RequestPaymentsOrder->find('first', ['conditions' => $conditions, 'recursive' => 2]);
		$this->set('requestPaymentResults', $requestPaymentResults);
		
		$results = [];
		if(!empty($requestPaymentResults)) {
			$results = $this->RequestPayment->getAllDetails($this->user, $requestPaymentResults['RequestPaymentsOrder']['request_payment_id'], $conditions=[]);
		}
		$this->set(compact('results'));
	}
	
	/*
	 * gli passo il contenuto di getAllDetails() e
	 * ctrl che sia stata associata almeno una richesta (orders, storeroom, genereric)
	*/
	private function _ctrl_request_payment_empty($user, $results) {
		
		$request_payment_empty = false;
		
		if($user->organization['Organization']['hasStoreroom']=='Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			if(empty($results['Order']) && empty($results['SummaryPayment']) && empty($results['PaymentsGeneric']) && empty($results['Storeroom']))
				$request_payment_empty = true;
			else
				$request_payment_empty = false;
		}
		else {
			if(empty($results['Order']) && empty($results['SummaryPayment']) && empty($results['PaymentsGeneric']))
				$request_payment_empty = true;
			else
				$request_payment_empty = false;
		}

		return $request_payment_empty;	
	}
	
	private function _getNumMax($user) { 

		App::import('Model', 'Counter');
		$Counter = new Counter;
			
		$results = $Counter->getCounter($user, 'request_payments');
		
		return $results;
	}
	
	private function _getNumMaxAndUpdate($user) { 

		App::import('Model', 'Counter');
		$Counter = new Counter;
			
		$results = $Counter->getCounterAndUpdate($user, 'request_payments');
		
		return $results;
	}

	/*
	 * gestisco importo_richiesto
	 */			
	private function _edit_wait($data, $debug) {
	
		$msg = '';
		
		self::d($data['RequestPayment'], $debug);
		
		if(isset($data['RequestPayment']['importo_richiesto']))
		foreach ($data['RequestPayment']['importo_richiesto'] as $summary_payment_id => $importo) {
				$sql = "UPDATE
							".Configure::read('DB.prefix')."summary_payments 
						SET
							importo_richiesto = ".$this->importoToDatabase($importo)."
						WHERE
							organization_id = ".(int)$this->user->organization['Organization']['id']."
							and id = ".(int)$summary_payment_id;
				self::d($sql, $debug);
				$resultUpdate = $this->RequestPayment->query($sql);
		}	
		
		return $msg;
	}	
	
	/*
	 * gestisco stato SOLLECITO1, SOLLECITO2, SOSPESO, DAPAGARE
	 * 		con stato PAGATO gestisco 
	 *				importo_pagato  
	 * 				Cashs
	 */			
	private function _edit_open($data, $debug) {
	
		$debug = false;
	
		$msg = '';

		self::d($data['RequestPayment'], $debug);
		
		App::import('Model', 'SummaryPayment');
		$SummaryPayment = new SummaryPayment;
			
		App::import('Model', 'Cash');
				
		if(isset($data['RequestPayment']['importo_pagato']))		
		foreach ($data['RequestPayment']['importo_pagato'] as $summary_payment_id => $importo) {
				
			$stato = $data['RequestPayment']['stato'][$summary_payment_id];
			$stato_orig = $data['RequestPayment']['stato_orig'][$summary_payment_id];

			self::d("Per summary_payment_id ".$summary_payment_id." Da stato_orig ".$stato_orig." a stato ".$stato, $debug);
			if($stato == $stato_orig) self::d(" => Nessun cambiamento ", $debug);
			else {
				if($stato=='PAGATO') self::d(" => Aggiorno STATO, importo, Cash", $debug);
				else self::d(" => Aggiorno solo STATO", $debug);
			}
				
			if($stato != $stato_orig) {
					
				switch($stato) {
					case 'PAGATO':
						$this->_edit_open_stato_PAGATO($summary_payment_id, $data, $debug);
					break;
					case 'SOSPESO':
						$this->_edit_open_stato_SOSPESO($summary_payment_id, $data, $debug);
					break;
					default:
						$this->_edit_open_stato_NON_PAGATO($summary_payment_id, $data, $debug);
					break;
				}
			}
				
		} // end foreach ($data['RequestPayment']['stato'] as $summary_payment_id => $stato)
			
		return $msg; 				
	}	
	
	/*
	 *  memorizzo il nuovo SummaryPayment.stato
	 *		l'importo_pagato (anche 0,00 perche' puo' prendere tutto dalla cassa)
	 *      per ogni user aggiorno SummaryOrder.saldata_a = TESORIERE cosi' l'ordine andra' allo stato successivo (dipende dal template) 
	 *		Cash
	 */
	private function _edit_open_stato_PAGATO($summary_payment_id, $data, $debug) {
 
		$msg = '';

		$user_id = $data['RequestPayment']['user_id'][$summary_payment_id];
		$importo_dovuto = $data['RequestPayment']['importo_dovuto'][$summary_payment_id];
		$importo_richiesto = $data['RequestPayment']['importo_richiesto'][$summary_payment_id];
		$importo_pagato = $data['RequestPayment']['importo_pagato'][$summary_payment_id];
		$stato = $data['RequestPayment']['stato'][$summary_payment_id];

		App::import('Model', 'SummaryPayment');
		$SummaryPayment = new SummaryPayment;
		
		$options = [];
		$options['conditions'] = ['SummaryPayment.organization_id' => $this->user->organization['Organization']['id'],
								  'SummaryPayment.id' => $summary_payment_id,
								  'SummaryPayment.user_id' => $data['RequestPayment']['user_id'][$summary_payment_id],
								  'SummaryPayment.request_payment_id' => $data['RequestPayment']['request_payment_id']];
		$options['recursive'] = -1;
		$summaryPaymentResults = $SummaryPayment->find('first', $options);
		self::d($options, $debug);
		self::d($summaryPaymentResults, $debug);
		if(!empty($summaryPaymentResults)) {
			$summaryPaymentResults['SummaryPayment']['importo_dovuto'] = $importo_dovuto;
			$summaryPaymentResults['SummaryPayment']['importo_richiesto'] = $importo_richiesto;
			$summaryPaymentResults['SummaryPayment']['importo_pagato'] = $importo_pagato;
			$summaryPaymentResults['SummaryPayment']['stato'] = $stato;
				
			self::d("importo_dovuto ".$row['SummaryPayment']['importo_dovuto'], $debug);
			self::d("importo_richiesto ".$row['SummaryPayment']['importo_richiesto'], $debug);
			self::d("importo_pagato ".$row['SummaryPayment']['importo_pagato'], $debug);
		
			/*
			 * modalita':  summary_payments 'DEFINED', 'CONTANTI', 'BANCOMAT', 'BONIFICO'
			 */
			if(isset($data['RequestPayment']['modalita'][$summary_payment_id]))
				$summaryPaymentResults['SummaryPayment']['modalita'] = $data['RequestPayment']['modalita'][$summary_payment_id];
			else
				$summaryPaymentResults['SummaryPayment']['modalita'] = 'DEFINED';
			
			$SummaryPayment->create();
			self::d($summaryPaymentResults, $debug);
			if (!$SummaryPayment->save($summaryPaymentResults)) 
				$msg .= "<br />SummaryPayment ".$summary_payment_id." per lo user ".$data['RequestPayment']['user_id'][$summary_payment_id]." in errore!";

			/*
			 * salvo SummaryPayment
			 * per ogni user aggiorno SummaryOrder.saldato_a = 'TESORIERE' cosi' l'ordine andra' allo stato successivo
			 */
			if (!$SummaryPayment->paid($this->user, $summaryPaymentResults, $debug)) 
				$msg .= "<br />SummaryPayment per lo user ".$summaryPaymentResults['SummaryPayment']['user_id']." in errore!";
						
		} // end if(!empty($summaryPaymentResults))
		else
			$msg .= "<br />SummaryPayment ".$summary_payment_id." per lo user ".$data['RequestPayment']['user_id'][$summary_payment_id]." non trovato!";

		/*
		 * salvo SummaryPayment
		 * per ogni user aggiorno SummaryOrder.saldato_a = 'TESORIERE' cosi' l'ordine andra' allo stato successivo
		 */
		if (!$SummaryPayment->paid($this->user, $summaryPaymentResults, $debug)) 
			$msg .= "<br />SummaryPayment per lo user ".$summaryPaymentResults['SummaryPayment']['user_id']." in errore!";
					
		/*
		 * C A S H
		 */
		App::import('Model', 'Cash');
		$Cash = new Cash;
				 
		$importo_dovuto = $this->importoToDatabase($importo_dovuto);
		$importo_richiesto = $this->importoToDatabase($importo_richiesto);
		$delta_cassa = (-1 * (floatval($importo_dovuto) - floatval($importo_richiesto)));
		self::d("delta_cassa (importo_dovuto - importo_richiesto) => $importo_dovuto - $importo_richiesto = ".$delta_cassa, $debug);
			
		$options = [];
		$options['conditions'] = ['Cash.organization_id' => $this->user->organization['Organization']['id'],
								  'Cash.user_id' => $user_id];
		$options['recursive'] = -1;
		$cashResults = $Cash->find('first', $options);
		self::d($cashResults, $debug);
		
		if(empty($cashResults)) {
			if($delta_cassa != 0) {
				
				self::d("INSERT CASH with user_id $user_id import ".$delta_cassa, $debug);
				
				/*
				 * INSERT CASH
				 */
				$data_cash = [];
				$data_cash['Cash']['user_id'] = $user_id;
				$data_cash['Cash']['organization_id'] = $this->user->organization['Organization']['id'];
				$data_cash['Cash']['importo'] = $delta_cassa;
			
				self::d($data_cash, $debug);									   	
				$Cash->create();
				if(!$Cash->save($data_cash)) 
					$msg .= "<br />INSERT CASH with user_id $user_id import ".$delta_cassa." ERROR";
			}
		}
		else {
			$new_importo_cash = ($cashResults['Cash']['importo'] + ($delta_cassa));
				
			/*
			 * non cancello + perche' ho CashesHistory
			 * 
			 if($new_importo_cash==0) {
					
				if($debug) echo "<br />DELETE CASH with user_id $user_id, cash_id ".$cashResults['Cash']['id'];
					
				// DELETE CASH
				$Cash->id = $cashResults['Cash']['id'];	
				if(!$Cash->delete()) 	
					$msg .= "<br />DELETE CASH with user_id $user_id, cash_id ".$cashResults['Cash']['id']." ERROR";
			}
			else {
			*
			*/
				self::d("UPDATE CASH with user_id $user_id importo da ".$cashResults['Cash']['importo']." a ".$new_importo_cash, $debug);
															
				/*
				 * UPDATE CASH
				 */
				$cashResults['Cash']['importo'] = $new_importo_cash;	
						
				self::d($data_cash, $debug);
				$Cash->create();
				if(!$Cash->save($cashResults)) 
					$msg .= "<br />UPDATE CASH with user_id $user_id importo da ".$cashResults['Cash']['importo']." a ".$new_importo_cash." ERROR";
					
				/*
				 * dati Cash precedenti in CashesHistory
				 */
				App::import('Model', 'CashesHistory');
		        $CashesHistory = new CashesHistory;
				
				$CashesHistory->previousCashSave($this->user, $cashResults['Cash']['id']);
		        									 		
			// } // end if(empty($cashResults))
				
		} // if($delta_cassa != 0) 
					
		self::d("-----------------------------------------------------", $debug);
		
		return $msg;
	}

    function admin_setNota($request_payment_id) {

        if (empty($request_payment_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {

            $sql = "UPDATE
					" . Configure::read('DB.prefix') . "request_payments
				SET
					nota = '" . addslashes($this->request->data['notaText']) . "' 
				WHERE
					organization_id = " . $this->user->organization['Organization']['id'] . "
					AND id = " . $request_payment_id;
			self::d($sql);
            try {
                $this->RequestPayment->query($sql);
            } catch (Exception $e) {
                CakeLog::write('error', $sql);
                CakeLog::write('error', $e);
            }
        }

        $content_for_layout = '';
        $this->set('content_for_layout', $content_for_layout);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }
	
	/*
	 *  memorizzo solo il nuovo SummaryPayment.stato
	 *		e non l'importo
	 *      SummaryOrder.saldato_a = 'TESORIERE' cosi' l'ordine andra' allo stato successivo
	 */
	private function _edit_open_stato_SOSPESO($summary_payment_id, $data, $debug) {

		$msg = '';

		App::import('Model', 'SummaryPayment');
		$SummaryPayment = new SummaryPayment;
		
		$options = [];
		$options['conditions'] = ['SummaryPayment.organization_id' => $this->user->organization['Organization']['id'],
								  'SummaryPayment.id' => $summary_payment_id,
								  'SummaryPayment.user_id' => $data['RequestPayment']['user_id'][$summary_payment_id],
								  'SummaryPayment.request_payment_id' => $data['RequestPayment']['request_payment_id']];
		$options['recursive'] = -1;
		$summaryPaymentResults = $SummaryPayment->find('first', $options);
		self::d($options, $debug);
		self::d($summaryPaymentResults, $debug);
		if(!empty($summaryPaymentResults)) {
			$summaryPaymentResults['SummaryPayment']['importo_pagato'] = '0.00';
			$summaryPaymentResults['SummaryPayment']['modalita'] = 'DEFINED';
			$summaryPaymentResults['SummaryPayment']['stato'] = $data['RequestPayment']['stato'][$summary_payment_id];
						
			$SummaryPayment->create();
			self::d($summaryPaymentResults, $debug);
			if (!$SummaryPayment->save($summaryPaymentResults)) 
				$msg .= "<br />SummaryPayment ".$summary_payment_id." per lo user ".$data['RequestPayment']['user_id'][$summary_payment_id]." in errore!";

			/*
			 * salvo SummaryPayment
			 * per ogni user aggiorno SummaryOrder.saldato_a = 'TESORIERE' cosi' l'ordine andra' allo stato successivo
			 */
			if (!$SummaryPayment->paid($this->user, $summaryPaymentResults, $debug)) 
				$msg .= "<br />SummaryPayment per lo user ".$summaryPaymentResults['SummaryPayment']['user_id']." in errore!";
										
		} // end if(!empty($summaryPaymentResults))
		else
			$msg .= "<br />SummaryPayment ".$summary_payment_id." per lo user ".$data['RequestPayment']['user_id'][$summary_payment_id]." non trovato!";
		
		return $msg;
	}
		
	/*
	 *  memorizzo solo il nuovo SummaryPayment.stato
	 *		e non l'importo
	 */
	private function _edit_open_stato_NON_PAGATO($summary_payment_id, $data, $debug) {

		$msg = '';

		App::import('Model', 'SummaryPayment');
		$SummaryPayment = new SummaryPayment;
		
		$options = [];
		$options['conditions'] = ['SummaryPayment.organization_id' => $this->user->organization['Organization']['id'],
								  'SummaryPayment.id' => $summary_payment_id,
								  'SummaryPayment.user_id' => $data['RequestPayment']['user_id'][$summary_payment_id],
								  'SummaryPayment.request_payment_id' => $data['RequestPayment']['request_payment_id']];
		$options['recursive'] = -1;
		$summaryPaymentResults = $SummaryPayment->find('first', $options);
		self::d($options, $debug);
		self::d($summaryPaymentResults, $debug);
		if(!empty($summaryPaymentResults)) {
			$summaryPaymentResults['SummaryPayment']['importo_pagato'] = '0.00';
			$summaryPaymentResults['SummaryPayment']['modalita'] = 'DEFINED';
			$summaryPaymentResults['SummaryPayment']['stato'] = $data['RequestPayment']['stato'][$summary_payment_id];
						
			$SummaryPayment->create();
			self::d($summaryPaymentResults, $debug);
			if (!$SummaryPayment->save($summaryPaymentResults)) 
				$msg .= "<br />SummaryPayment ".$summary_payment_id." per lo user ".$data['RequestPayment']['user_id'][$summary_payment_id]." in errore!";

			/*
			 * salvo SummaryPayment
			 * per ogni user aggiorno SummaryOrder.saldato_a = 'TESORIERE' cosi' l'ordine andra' allo stato successivo
			 */
			if (!$SummaryPayment->paid($this->user, $summaryPaymentResults, $debug)) 
				$msg .= "<br />SummaryPayment per lo user ".$summaryPaymentResults['SummaryPayment']['user_id']." in errore!";
						
		} // end if(!empty($summaryPaymentResults))
		else
			$msg .= "<br />SummaryPayment ".$summary_payment_id." per lo user ".$data['RequestPayment']['user_id'][$summary_payment_id]." non trovato!";

		return $msg;
	}					
}