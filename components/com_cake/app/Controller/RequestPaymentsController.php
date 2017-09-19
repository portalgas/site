<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
/**
 * RequestPayments Controller
 *
 * @property RequestPayment $RequestPayment
 */
class RequestPaymentsController extends AppController {

	private $requestPaymentResults = array();
	
	public function beforeFilter() {
		
		parent::beforeFilter();
		
		/* ctrl ACL */
		if($this->action != 'admin_view') {
			if(!$this->isTesoriereGeneric()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}
		
		/*
		 * ctrl referentTesoriere
		*/		$this->set('isReferenteTesoriere', $this->isReferentTesoriere());		
		/*		 * REQUEST_PAYMENT		*/
		if(isset($this->request->pass['id'])) {
			$id = $this->request->pass['id'];
			$conditions = array('RequestPayment.organization_id' => $this->user->organization['Organization']['id'],								'RequestPayment.'.$this->RequestPayment->primaryKey => $id);			$results = $this->RequestPayment->find('first', array('conditions' => $conditions, 'recursive' => -1));
			
			
			$actionWithPermission = array('admin_add_generic', 'admin_add_orders', 'admin_add_storeroom');			if (in_array($this->action, $actionWithPermission)) {				if($results['RequestPayment']['stato_elaborazione']!='WAIT') {
					$this->Session->setFlash(__('msg_not_request_payment_state'));					$this->myRedirect(array('action' => 'index'));					
				}
			}	
			$this->requestPaymentResults = $results;
			$this->set('requestPaymentResults', $this->requestPaymentResults);
			
			$tot_importo = $this->RequestPayment->getTotImporto($this->user, $id);
			$this->set('tot_importo', $tot_importo);		}
		
		
		/*		 * ctrl configurazione Organization		*/		if($this->user->organization['Organization']['hasStoreroom']=='Y' && $this->user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {			
			/*
			 * se non ci sono consegne valide (quelle per la dispensa) non fa comparire la voce di menu "Aggiungi una richiesta pagamento di dispensa"
			 */
			App::import('Model', 'Storeroom');
			$Storeroom = new Storeroom;
		
			$deliveries = $Storeroom->deliveriesToRequestPayment($this->user);			if(empty($deliveries)) 
				$deliveriesValideToStoreroom = 'N';
			else 	
				$deliveriesValideToStoreroom = 'Y';
		}
		else 
			$deliveriesValideToStoreroom = 'N';
	
		$this->set('deliveriesValideToStoreroom', $deliveriesValideToStoreroom);		
		/*		 * $pageCurrent = array('controller' => '', 'action' => '');		* mi serve per non rendere cliccabile il link corrente nel menu laterale		*/		$pageCurrent = $this->getToUrlControllerAction($_SERVER['REQUEST_URI']);		$this->set('pageCurrent',$pageCurrent);	
	}
	
	public function admin_index() {
		
		$debug = false;
		
		/*		 * aggiorno lo stato degli ordini		 */		$utilsCrons = new UtilsCrons(new View(null));
		if(Configure::read('developer.mode')) echo "<pre>";		$utilsCrons->requestPaymentStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);		if(Configure::read('developer.mode')) echo "</pre>";
		
		/*		 * cancello le richieste di pagamento chiuse		 */		
		// $utilsCrons->archiveStatistics($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);		
		
		$this->RequestPayment->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id']);
		$this->RequestPayment->hasMany['SummaryPayment']['conditions'] = array('SummaryPayment.organization_id' => $this->user->organization['Organization']['id']);
		$this->RequestPayment->recursive = 1;
		$conditions = array('RequestPayment.organization_id' => $this->user->organization['Organization']['id']);
		/*
		 * se referente-tesoriere o super-referente-tesoriere prendo solo le richieste creata da lui
		 * solo il tesoriere puo' gestirli tutti
		 */
		if($this->isReferentTesoriere()) 
			$conditions = array('user_id' => $this->user->get('id'));		
		$this->paginate = array('conditions' => $conditions, 'order' => 'RequestPayment.created DESC');
		$results = $this->paginate();
		
		foreach ($results as $i => $result) {
			$request_payment_id = $result['RequestPayment']['id'];

			if($this->user->organizationHasStoreroom=='Y') {
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
				// echo '<br />'.$sql;
				$tot = current($this->RequestPayment->query($sql));
				$results[$i]['RequestPaymentsStoreroom'] = $tot[0]; 
			} // end if($this->user->organizationHasStoreroom=='Y') 
			
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
			// echo '<br />'.$sql;
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
			$data['RequestPayment']['num'] = $this->__getNumMaxAndUpdate($this->user);	
			$data['RequestPayment']['nota'] = $this->request->data['RequestPayment']['nota'];		
			$this->RequestPayment->create();
			if($this->RequestPayment->save($data)) {
				$request_payment_id = $this->RequestPayment->getLastInsertId();
				$this->myRedirect(array('controller' => 'RequestPayments', 'action' => 'edit', 'id' => $request_payment_id));
			}	
			else {
				$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
				$this->myRedirect(array('controller' => 'RequestPayments', 'action' => 'index'));
			}				
		}
		
		$request_payment_num = $this->__getNumMax($this->user);
		$this->set('request_payment_num', $request_payment_num);
	}
		
	public function admin_edit($id = null) {

		$debug = false;
	
		/*
		 * lo recupero dal GET, perche' ho effettuato filtro
 		 */		 
		if(isset($this->request->params['pass']['id']))
			$id = $this->request->params['pass']['id'];
	
		$this->RequestPayment->id = $id;		if (!$this->RequestPayment->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));
			echo "<pre>";
			print_r($this->request);
			exit;			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}
		$msg = "";
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			if($this->request->data['RequestPayment']['stato_elaborazione']=='WAIT')
				$msg = $this->__edit_wait($this->request->data, $debug);
			if($this->request->data['RequestPayment']['stato_elaborazione']=='OPEN')
				$msg = $this->__edit_open($this->request->data, $debug);
							
			if(empty($msg))
				$this->Session->setFlash(__('The summary payments has been saved'));
			else
				$this->Session->setFlash($msg);
			
			/*
			 * se tutti i summary_payments.stato = SOSPESO o PAGATO porto RequestPayment.stato_elaborazione = CLOSE
			 */
			$utilsCrons = new UtilsCrons(new View(null));			$utilsCrons->requestPaymentStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $id);
		} // end if ($this->request->is('post') || $this->request->is('put'))

		/*		 * filtri		*/		$FilterRequestPaymentName = null;		$FilterSummaryPaymentStato = null;		$conditions = array();
				if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'Name')) {			$FilterRequestPaymentName = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'Name');			$conditions += array('User.name'=> $FilterRequestPaymentName);		}		if($this->Session->check(Configure::read('Filter.prefix').'SummaryPaymentStato')) {			$FilterSummaryPaymentStato = $this->Session->read(Configure::read('Filter.prefix').'SummaryPaymentStato');			$conditions += array('SummaryPayment.stato' => $FilterSummaryPaymentStato);		}
		/*		echo "<pre>";
		print_r($conditions);
		echo "</pre>";
		*/		$this->set('FilterRequestPaymentName', $FilterRequestPaymentName);		$this->set('FilterSummaryPaymentStato', $FilterSummaryPaymentStato);				$summaryPaymentStato = array('DAPAGARE' => __('DAPAGARE'), 'SOLLECITO1' => __('SOLLECITO1'), 'SOLLECITO2' => __('SOLLECITO2'), 'SOSPESO' => __('SOSPESO'), 'PAGATO' => __('PAGATO'));  
		$this->set(compact('summaryPaymentStato'));
		
		/*		 * estraggo i dettagli di una richiesta di pagamento		 * 	- ordini associati		 *  - voci di spesa generica		 *  - dispensa		 */
		$results = $this->RequestPayment->getAllDetails($this->user, $id, $conditions);
		
		/*
		 * dati cassa per l'utente
		 */
		App::import('Model', 'Cash');
		foreach($results['SummaryPayment'] as $numResult => $result) {
			$Cash = new Cash;
			
			$options = array();
			$options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
											'Cash.user_id' => $result['User']['id']);
			$options['recursive'] = -1;
			$cashResults = $Cash->find('first', $options);
			if(empty($cashResults))	{
				$cashResults['Cash']['importo'] = '0.00';
				$cashResults['Cash']['importo_'] = '0,00';
				$cashResults['Cash']['importo_e'] = '0,00 &euro;';								
			}
			/*
			echo "<pre>";
			print_r($options);
			print_r($cashResults);
			echo "</pre>";
			*/
			$results['SummaryPayment'][$numResult]['Cash'] = $cashResults['Cash'];
		}
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
		$this->set('results', $results);		$this->set('request_payment_empty', $this->__ctrl_request_payment_empty($this->user, $results));
		
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
	
		
		if(count($results['Order'])==0 && empty($requestPaymentsGenericResults) && 
		  (($this->user->organizationHasStoreroom=='Y' && empty($requestPaymentsStoreroomResults)) || $this->user->organizationHasStoreroom=='N'))
			$this->render('admin_edit_no');
		else {	

			if(Configure::read('developer.mode')) echo '<br />RequestPayment.stato_elaborazione '.$results['RequestPayment']['stato_elaborazione'];
			
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

	public function admin_edit_stato_elaborazione($id=null) {	
		if ($this->request->is('post') || $this->request->is('put')) {
			$action_submit = $this->request->data['RequestPayment']['action_submit'];
			
			switch ($action_submit) {				case 'toWait':
					$stato_elaborazione = 'WAIT';
				break;
				case 'toClose':
					$stato_elaborazione = 'CLOSE';
				break;
			}

			$id = $this->request->data['RequestPayment']['request_payment_id'];						$this->RequestPayment->id = $id;			if (!$this->RequestPayment->exists($this->user->organization['Organization']['id'])) {				$this->Session->setFlash(__('msg_error_params'));				$this->myRedirect(Configure::read('routes_msg_exclamation'));			}				
			/*
			 * le richieste 
			 * - stato.elaborazione = CLOSE 
			 * - stato_elaborazione_date <= Configure::read('GGArchiveStatics');
			 * vengono richiamate dal Cron::archiveStatistics() 
			 */			$sql = "UPDATE						".Configure::read('DB.prefix')."request_payments 					SET
						stato_elaborazione = '".$stato_elaborazione."',
						stato_elaborazione_date = '".date('Y-m-d')."',						modified = '".date('Y-m-d H:i:s')."'					WHERE						organization_id = ".(int)$this->user->organization['Organization']['id']."						and id = ".(int)$id;			// echo '<br />'.$sql;			$result = $this->RequestPayment->query($sql);							$this->myRedirect(array('controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id));						} // end if ($this->request->is('post') || $this->request->is('put')) 
				
		$this->RequestPayment->id = $id;		if (!$this->RequestPayment->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}			
		/*		 * estraggo i dettagli di una richiesta di pagamento		* 	- ordini associati		*  - voci di spesa generica		*  - dispensa		*/		$results = $this->RequestPayment->getAllDetails($this->user, $id, null);		// qui non serve $this->set('results', $results);		$this->set('request_payment_empty', $this->__ctrl_request_payment_empty($this->user, $results));
				$invio_mail = array('Y' => 'Si', 'N' => 'No');		$this->set(compact('invio_mail'));		
		switch ($this->requestPaymentResults['RequestPayment']['stato_elaborazione']) {			case 'WAIT':				$this->render('admin_edit_stato_elaborazione_to_open_from_wait');				break;			case 'OPEN':				$this->render('admin_edit_stato_elaborazione_open');				break;			case 'CLOSE':				$this->render('admin_edit_stato_elaborazione_to_open_from_close');				break;		}	}
	
	/*
	 *  confermo rich pagamento e invio mail
	 */
	public function admin_edit_stato_elaborazione_to_open_from_wait() {		
		if ($this->request->is('post') || $this->request->is('put') ) {
		
			$id = $this->request->data['RequestPayment']['request_payment_id'];
			
			$this->RequestPayment->id = $id;
			if (!$this->RequestPayment->exists($this->user->organization['Organization']['id'])) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}		
								
			if($this->request->data['RequestPayment']['invio_mail']=='Y') {

				App::import('Model', 'Mail');				$Mail = new Mail;				
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
				//echo '<br />'.$sql;
				$summaryPaymentResults = $this->RequestPayment->query($sql);
				
				if(!empty($summaryPaymentResults)) {

					/*
					 * num della richiesta pagamento
					*/
					$options = array();
					$options['conditions'] = array('RequestPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
												   'RequestPayment.id' => $id);
					$options['recursive'] = -1;
					$options['fields'] = array('num', 'nota');
					$numRequestPaymentResults = $this->RequestPayment->find('first', $options);
					$request_payment_num = $numRequestPaymentResults['RequestPayment']['num'];
					
					/*
					 * prepare mail
					 */					$subject_mail = 'Nuova richiesta di pagamento (numero '.$request_payment_num.')';					$Email->subject($subject_mail);
					if(!empty($this->user->organization['Organization']['www']))
						$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))));
					else
						$Email->viewVars(array('body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))));					
					$msg = '';					$msg .= 'Inviata la mail a<br />';
					foreach ($summaryPaymentResults as $summaryPaymentResult) {
						
							$mail = $summaryPaymentResult['User']['email'];							$name = $summaryPaymentResult['User']['name'];							$request_payment_id = $summaryPaymentResult['SummaryPayment']['request_payment_id'];
							$importo_dovuto = $summaryPaymentResult['SummaryPayment']['importo_dovuto'];
							$importo_dovuto = number_format($importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
							$importo_richiesto = $summaryPaymentResult['SummaryPayment']['importo_richiesto'];
							$importo_richiesto = number_format($importo_richiesto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
														if(!empty($mail)) {																	$body_mail = "c'è una nuova <b>richiesta di pagamento</b> (la numero $request_payment_num) di ".$importo_richiesto.'&nbsp;&euro;.';
								
								$body_mail .= "<br /><br />Collegati ";
								$body_mail .= "al sito ".$this->traslateWww(Configure::read('SOC.site'));									
								$body_mail .= " e, dopo aver fatto la login, scarica il documento per effettuare il pagamento.";
								
								$body_mail .= '<br /><br />Se effettui il pagamento tramite bonifico indica come <b>causale</b>: Richiesta num '.$request_payment_num.'&nbsp;di&nbsp;'.$name;
										
								if(!empty($numRequestPaymentResults['RequestPayment']['nota'])) 
									$body_mail .= '<br /><br />'.$numRequestPaymentResults['RequestPayment']['nota'];																	//echo $body_mail; exit;
								
								if(!Configure::read('mail.send'))  $Email->transport('Debug');
								
								$Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
								$Email->to($mail);
									
								try {
									$Mail->send($Email, $mail, $body_mail);
								} catch (Exception $e) {
									CakeLog::write("error", $e, array("mails"));
								}																}							else								$msg .= $name.' senza indirizzo mail!<br />';
					}
					
					$this->Session->setFlash($msg);
				} // end if(!empty($summaryPaymentResults))
			} // if($this->request->data['RequestPayment']['invio_mail']=='Y') 
										$sql = "UPDATE						`".Configure::read('DB.prefix')."request_payments`					SET						stato_elaborazione = 'OPEN',
						stato_elaborazione_date = '".date('Y-m-d')."',
						data_send = '".date('Y-m-d')."',
						modified = '".date('Y-m-d H:i:s')."'					WHERE						organization_id = ".(int)$this->user->organization['Organization']['id']."						and id = ".(int)$id;			// echo '<br />'.$sql;			$result = $this->RequestPayment->query($sql);				$this->myRedirect(array('controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id));		} // if ($this->request->is('post') || $this->request->is('put') )	}

	public function admin_edit_stato_elaborazione_to_open_from_close() {	
		if ($this->request->is('post') || $this->request->is('put') ) {
		
			$id = $this->request->data['RequestPayment']['request_payment_id'];
			
			$this->RequestPayment->id = $id;			if (!$this->RequestPayment->exists($this->user->organization['Organization']['id'])) {				$this->Session->setFlash(__('msg_error_params'));				$this->myRedirect(Configure::read('routes_msg_exclamation'));			}
										$sql = "UPDATE						`".Configure::read('DB.prefix')."request_payments`					SET						stato_elaborazione = 'OPEN',
						stato_elaborazione_date = '".date('Y-m-d')."',
						modified = '".date('Y-m-d H:i:s')."'					WHERE						organization_id = ".(int)$this->user->organization['Organization']['id']."						and id = ".(int)$id;			// echo '<br />'.$sql;			$result = $this->RequestPayment->query($sql);				$this->myRedirect(array('controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id));		} // if ($this->request->is('post') || $this->request->is('put') )	}
		
	/*
	 * estrai ordini TO-PAYMENT per portarle a CLOSE
	*
	*  $RequestPaymentsOrders, $SummaryPayments
	*/
	public function admin_add_orders($id=null) {
		
		$debug = false;
		
		$this->RequestPayment->id = $id;		if (!$this->RequestPayment->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}	
		
		if ($this->request->is('post') || $this->request->is('put')) {
				
			$order_id_selected = $this->request->data['RequestPayment']['order_id_selected'];
				
			if($debug) echo '<br />order_id_selected '.$order_id_selected;
			
			/*
			 * summary_order dovrebbe gia' essere popolato 
			 * dal referente da Cart::admin_managementCartsGroupByUsers
			 * dal tesoriere da Tesoriere::admin_orders_get_WAIT_PROCESSED_TESORIERE (quando li prende in carico)
			 */
			App::import('Model', 'SummaryOrder');
			$SummaryOrder = new SummaryOrder;
				
			if(strpos($order_id_selected,',')===false)
				$order_id_arr[] = $order_id_selected;
			else
				$order_id_arr = explode(',',$order_id_selected);
			foreach ($order_id_arr as $order_id) {
				
				if($debug) echo '<br />order_id '.$order_id;
				
				$resultsSummaryOrder = $SummaryOrder->select_to_order($this->user, $order_id);
				
				if($debug) echo '<br />occorrenze in SummaryOrder '.count($resultsSummaryOrder);
				
				if(empty($resultsSummaryOrder))
					$SummaryOrder->populate_to_order($this->user, $order_id, 0, $debug);
			}	
				
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
						WHERE
							organization_id = ".(int)$this->user->organization['Organization']['id']."
							AND order_id in (".$order_id_selected.")
							AND modalita = 'DEFINED'
							AND importo_pagato = '0.00' 
						GROUP BY user_id
						ORDER BY user_id ";
				if($debug) echo '<br />'.$sql;
				$results = $SummaryPayment->query($sql);
				foreach ($results as $result) {
					
					$tot_importo = $result[0]['tot_importo'];
					
					$data = null;
					
					/*
					 * ctrl se esiste gia' un'occorrenza in SummaryPayment, se SI => update  
					 * */
					$conditions = array('SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
										'SummaryPayment.user_id' => (int)$result['SummaryOrder']['user_id'],
										'SummaryPayment.request_payment_id' => (int)$id);
					$resultCtrl = $SummaryPayment->find('first',array('fields' => array('id','importo_dovuto','importo_richiesto'), 'conditions' => $conditions,'recursive' => -1));
					if(!empty($resultCtrl)) {
						// UPDATE
						$data['SummaryPayment']['id'] = $resultCtrl['SummaryPayment']['id'];
						$data['SummaryPayment']['importo_dovuto'] = ($resultCtrl['SummaryPayment']['importo_dovuto'] + $tot_importo);
						$data['SummaryPayment']['importo_richiesto'] = $data['SummaryPayment']['importo_dovuto'];
						
						if($debug) echo '<br />UPDATE importo totale in SummaryPayment - importo_dovuto '.$data['SummaryPayment']['importo_dovuto'].' - importo_richiesto '.$data['SummaryPayment']['importo_richiesto'].' per user_id '.$result['SummaryOrder']['user_id'];
					}
					else {
						// INSERT
						$data['SummaryPayment']['importo_dovuto'] = $tot_importo;
						$data['SummaryPayment']['importo_richiesto'] = $data['SummaryPayment']['importo_dovuto'];

						if($debug) echo '<br />INSERT importo totale in SummaryPayment - importo_dovuto '.$data['SummaryPayment']['importo_dovuto'].' - importo_richiesto '.$data['SummaryPayment']['importo_richiesto'].' per user_id '.$result['SummaryOrder']['user_id'];
					}
					$data['SummaryPayment']['organization_id'] = $this->user->organization['Organization']['id'];
					$data['SummaryPayment']['request_payment_id'] = $id;
					$data['SummaryPayment']['user_id'] = $result['SummaryOrder']['user_id'];

					if($debug) {
						echo "<pre>";
						print_r($data);
						echo "</pre>";						
					}
					
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

				if($debug) echo '<br />order_id '.$order_id;
				
				if(isset($order_id) && in_array($order_id, $arr_order_id_selected)) {
						
					/*
					 * ottengo ORDER per avere delivery_id
					* */
					App::import('Model', 'Order');
					$Order = new Order;
						
					$Order->id = $order_id;
					if (!$Order->exists($this->user->organization['Organization']['id'])) {
						$msg .= "<br />ordine ".$order_id." non esiste!";
					}
					else {
						$conditions = array('Order.organization_id' => (int)$this->user->organization['Organization']['id'],
											'Order.isVisibleBackOffice' => 'Y',
											'Order.id' => (int)$order_id);
						$Order->unbindModel(array('belongsTo' => array('SuppliersOrganization','Delivery')));
						$resultOrder = $Order->find('first',array('conditions' => $conditions,'recursive' => 0));
		
						/*
						 * creo per ogni ORDINE un occorrenza RequestPaymentsOrders
						*/
						$data = null;
						$data['RequestPaymentsOrder']['organization_id'] = $this->user->organization['Organization']['id'];
						$data['RequestPaymentsOrder']['request_payment_id'] = $id;
						$data['RequestPaymentsOrder']['order_id'] = $order_id;
						$data['RequestPaymentsOrder']['delivery_id'] = $resultOrder['Order']['delivery_id'];
		
						if($debug) {
							echo "<pre>";
							print_r($data);
							echo "</pre>";
						}
						
						$RequestPaymentsOrder->create();
						if(!$RequestPaymentsOrder->save($data)) {
							$msg .= "<br />ordine ".$order_id." in errore!";
						}
		
						/*
						 * aggiorno stato ORDER
						 * 	da TO-PAYMENT a CLOSE
						 */
						$sql = "UPDATE
							`".Configure::read('DB.prefix')."orders`
							SET
								state_code = 'CLOSE',
								modified = '".date('Y-m-d H:i:s')."'
							WHERE
								organization_id = ".(int)$this->user->organization['Organization']['id']."
								and id = ".(int)$order_id;
						//if($debug) echo '<br />'.$sql;
						$result = $RequestPaymentsOrder->query($sql);

						/*
						 * Order is CLOSE => cancello eventuali occorrenze di MonitoringOrder
						 * 	anche in Cassiere::admin_edit_stato_elaborazione();
						*/						
						App::import('Model', 'MonitoringOrder');
						$MonitoringOrder = new MonitoringOrder;
		
						$MonitoringOrder->delete_to_order($this->user, $order_id);
			
					} // end if (!$Order->exists($this->user->organization['Organization']['id']))
				}
			} // end foreach
		
			if(!empty($msg))
				$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
			else
				$this->Session->setFlash(__('The request payments orders has been saved'));
		
			if($debug) echo '<br />msg '.$msg;
				 
			/*
			 * aggiorno lo stato delle consegne, se tutti gli ordini sono a CLOSE setto lo stato_elaborazione CLOSE
			 * */
			$utilsCrons = new UtilsCrons(new View(null));
			$utilsCrons->deliveriesStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
			
			if(!$debug) $this->myRedirect(array('controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id));
			
		} // end if ($this->request->is('post'))
		
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$options = array('orders' => true, 'storerooms' => false, 'summaryOrders' => false,
						 'suppliers' => true,'referents' => true, 
						 'articlesOrdersInOrder'=>false);  // NON estraggo gli articoli dell'ordine
			
		$conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
												'Delivery.sys'=> 'N',
												'Delivery.stato_elaborazione'=> 'OPEN'),
							'Order' => array('Order.isVisibleBackOffice' => 'Y',
											 'Order.state_code' => 'TO-PAYMENT'));
		
		$results = $Delivery->getDataTabs($this->user,$conditions,$options);
		$this->set('results',$results);
	}
	
	public function admin_add_storeroom() {
		
		$id = $this->request->pass['id']; // lo ricavo cosi' perche' nella queryString ho FilterStoreroomDeliveryId
		
		$this->RequestPayment->id = $id;		if (!$this->RequestPayment->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}		
		

		/*		 * ctrl configurazione Organization		*/
		if($this->user->organizationHasStoreroom=='N') {			$this->Session->setFlash(__('msg_not_organization_config'));			$this->myRedirect(Configure::read('routes_msg_stop'));		}
		
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
			// echo '<br />'.$sql;
			$results = $Storeroom->query($sql);
			
			App::import('Model', 'SummaryPayment');
			$SummaryPayment = new SummaryPayment;
			
			foreach ($results as $i => $result) {
				$data = null;
				
				/*
				 * ctrl se esiste gia' un'occorrenza in SummaryPayment, se SI => update
				 * */
				$conditions = array('SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
									'SummaryPayment.user_id' => (int)$result['Storeroom']['user_id'],
									'SummaryPayment.request_payment_id' => (int)$id);
				$resultCtrl = $SummaryPayment->find('first',array('conditions' => $conditions,'recursive' => -1));
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
				
				$this->myRedirect(array('controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id));
			}
			else		
				$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
		} // end if ($this->request->is('post') || $this->request->is('put')) 
		
		$FilterRequestPaymentDeliveryId = null;
		
		$deliveries = $Storeroom->deliveriesToRequestPayment($this->user);	
		if(empty($deliveries)) {			$this->Session->setFlash(__('NotFoundDeliveries'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}		
		$this->set(compact('deliveries'));
		
		$resultsFound = '';
		$results = array();
		
		/* recupero dati dalla Session gestita in appController::beforeFilter */		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'DeliveryId')) {			$FilterRequestPaymentDeliveryId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'DeliveryId');		
			$conditions = array('Storeroom.organization_id' => (int)$this->user->organization['Organization']['id'],
								'Storeroom.user_id != ' => $this->storeroomUser['User']['id'],
								'Storeroom.delivery_id > ' => 0,
								'Storeroom.stato' => 'Y',
								'Storeroom.delivery_id'=>$FilterRequestPaymentDeliveryId);
					
			$orderBy = array('Storeroom.delivery_id, '.Configure::read('orderUser').', Storeroom.name');

			$Storeroom->Delivery->unbindModel(array('hasMany' => array('Order')));			
			$Storeroom->Article->unbindModel(array('hasOne' => array('ArticlesOrder')));			$Storeroom->Article->unbindModel(array('hasMany' => array('ArticlesOrder')));			$Storeroom->Article->unbindModel(array('hasAndBelongsToMany' => array('Order')));			$Storeroom->User->unbindModel(array('hasMany' => array('Cart')));
			$results = $Storeroom->find('all',array('conditions' => $conditions,'order' => $orderBy,'recursive' => 1));
			
			/*
			 * aggiungo informazione sul produttore
			 */
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			if(!empty($results)) {
				foreach ($results as $i => $result) {
				
					$conditions = array('SuppliersOrganization.id' => $result['Article']['supplier_organization_id']);
					$suppliersOrganization = $SuppliersOrganization->getSuppliersOrganization($this->user, $conditions);
					$results[$i]['SuppliersOrganization'] = current($suppliersOrganization);
				}
			}
			else 
				$resultsFound = 'N';		}
		
		/* filtro */
		$this->set('FilterRequestPaymentDeliveryId', $FilterRequestPaymentDeliveryId);
		$this->set('resultsFound', $resultsFound);
		$this->set('results',$results);
	}	
	
	public function admin_delete_generic($id = null) {
	
		$debug = false;
		
		App::import('Model', 'RequestPaymentsGeneric');
		$RequestPaymentsGeneric = new RequestPaymentsGeneric;
		
		$RequestPaymentsGeneric->id = $id;
		if (!$RequestPaymentsGeneric->exists()) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * estraggo RequestPaymentsGeneric da cancellare
		 */
		$options =  array();
		$options['conditions'] = array('RequestPaymentsGeneric.organization_id'=>(int)$this->user->organization['Organization']['id'],
										'RequestPaymentsGeneric.id'=> $id);
		$options['recursive'] = -1;
		$results = $RequestPaymentsGeneric->find('first', $options);

		if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";
		}
		
		if ($RequestPaymentsGeneric->delete()) {
			$this->Session->setFlash(__('Delete RequestPaymentsGeneric'));
		
			/*
			 * aggiorno il totale in SummaryPayment
			*/
				
			App::import('Model', 'SummaryPayment');
			$SummaryPayment = new SummaryPayment;
			
			$options = array();
			$options['conditions'] = array('SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
											'SummaryPayment.user_id' => $results['RequestPaymentsGeneric']['user_id'],
											'SummaryPayment.request_payment_id' => $results['RequestPaymentsGeneric']['request_payment_id']);
			$options['recursive'] = -1;
			$summaryPaymentResult= $SummaryPayment->find('first', $options);
			
			/*
			 * se gli importi RequestPaymentsGeneric.importo = SummaryPayment.importo_richiesto
			 * cancello SummaryPayment
			 */
			if($results['RequestPaymentsGeneric']['importo']==$summaryPaymentResult['SummaryPayment']['importo_richiesto']) {
				
				if($debug)
					echo '<br />RequestPaymentsGeneric.importo ('.$results['RequestPaymentsGeneric']['importo'].') = SummaryPayment.importo_richiesto '.$summaryPaymentResult['SummaryPayment']['importo_richiesto'].' => cancello SummaryPayment ';
				
				$SummaryPayment->id = $summaryPaymentResult['SummaryPayment']['id'];
				$SummaryPayment->delete();
			}
			else {
				$importo_dovuto = ($summaryPaymentResult['SummaryPayment']['importo_dovuto'] - ($results['RequestPaymentsGeneric']['importo']));
				$importo_richiesto = ($summaryPaymentResult['SummaryPayment']['importo_richiesto'] - ($results['RequestPaymentsGeneric']['importo']));
				
				if($debug)
					echo '<br />RequestPaymentsGeneric.importo ('.$results['RequestPaymentsGeneric']['importo'].') != SummaryPayment.importo_richiesto '.$summaryPaymentResult['SummaryPayment']['importo_richiesto'].' => aggiorno SummaryPayment: '.$importo_richiesto;
				
				/*
				 * sottraggo da SummaryPayment.importo_richiesto 
				 * 	RequestPaymentsGeneric.importo che viene eliminato
				 */
				
				$data = array();
				$data['SummaryPayment'] =  $summaryPaymentResult['SummaryPayment'];
				$data['SummaryPayment']['importo_dovuto'] = $importo_dovuto;
				$data['SummaryPayment']['importo_richiesto'] = $importo_richiesto;
					
				if($debug) {
					echo '<h2>Aggiorno SummaryPayment con il nuovo importo</h2>';
					echo "<pre>";
					print_r($data);
					echo "</pre>";
				}
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
			echo '<br />url redirect '.$url;
			exit;
		}
		$this->myRedirect($url);
   }  
	
	public function admin_add_generic($id=null) {
		
		$debug = false;
		
		$this->RequestPayment->id = $id;		if (!$this->RequestPayment->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}		
		App::import('Model', 'RequestPaymentsGeneric');
		$RequestPaymentsGeneric = new RequestPaymentsGeneric;
				
		if ($this->request->is('post') || $this->request->is('put')) {

			App::import('Model', 'RequestPaymentsGeneric');
			$RequestPaymentsGeneric = new RequestPaymentsGeneric;
				
			if($debug) echo '<br />dest_options_qta '.$this->request->data['RequestPaymentsGeneric']['dest_options_qta'];
		
			// if(isset($this->request->data['RequestPaymentsGeneric']['users']) && !empty($this->request->data['RequestPaymentsGeneric']['users']))
				
				/*
				 * per ogni user con importo valorizzato creo un occorrenza
				 */
				if($this->request->data['RequestPaymentsGeneric']['dest_options_qta']=='SOME_DIFF') {
					
					$users = $this->request->data['RequestPaymentsGeneric']['Importo'];
					foreach ($users as $user_id => $importo) {
						if($importo!='0,00') {
							
							$importo = $this->importoToDatabase($importo);
							if($debug) echo "<br />Tratto lo user ".$user_id." con importo ".$importo;
								
							/*
							 * creo occorrenza in RequestPaymentsGeneric
							*/
							$data = null;
							$data['RequestPaymentsGeneric']['organization_id'] = (int)$this->user->organization['Organization']['id'];
							$data['RequestPaymentsGeneric']['request_payment_id'] = $id;
							$data['RequestPaymentsGeneric']['user_id'] = $user_id;
							$data['RequestPaymentsGeneric']['name'] = $this->request['data']['RequestPaymentsGeneric']['name'];
							$data['RequestPaymentsGeneric']['importo'] = $importo;			

							if($debug) {
								echo "<pre>";
								print_r($data);
								echo "</pre>";
							}
							
							$RequestPaymentsGeneric->create();
							if($RequestPaymentsGeneric->save($data))
								$this->Session->setFlash(__('The request payments generic has been saved'));
							else
								$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
							
							/*
							 * creo occorrenza in SummaryPayment
							*/
							
							App::import('Model', 'SummaryPayment');
							$SummaryPayment = new SummaryPayment;

							$data = null;
							/*
							 * ctrl se esiste gia' un'occorrenza in SummaryPayment, se SI => update
							* */
							$options = array();
							$options['conditions'] = array('SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
														'SummaryPayment.user_id' => (int)$user_id,
														'SummaryPayment.request_payment_id' => (int)$id);
							$options['fields'] = array('id','importo_dovuto','importo_richiesto');
							$options['recursive'] = -1;
							$resultCtrl = $SummaryPayment->find('first', $options);
							if(!empty($resultCtrl)) {
								
								if($debug) echo '<br />Trovata gia un occorrenza per lo user '.$user_id.' in SummaryPayment => UPDATE di importo '.$resultCtrl['SummaryPayment']['importo_dovuto'].' + '.$importo;
								// UPDATE
								$data['SummaryPayment']['id'] = $resultCtrl['SummaryPayment']['id'];
								$data['SummaryPayment']['importo_dovuto'] = ($resultCtrl['SummaryPayment']['importo_dovuto'] + $importo);
								$data['SummaryPayment']['importo_richiesto'] = $data['SummaryPayment']['importo_dovuto'];
							}
							else {
								
								if($debug) echo '<br />NON Trovata un occorrenza per lo user '.$user_id.' in SummaryPayment => INSERT ';
								// INSERT
								$data['SummaryPayment']['importo_dovuto'] = $importo;  // $this->importoToDatabase($importo);
								$data['SummaryPayment']['importo_richiesto'] = $importo;  // $this->importoToDatabase($importo);
							}
							$data['SummaryPayment']['organization_id'] = $this->user->organization['Organization']['id'];
							$data['SummaryPayment']['request_payment_id'] = $id;
							$data['SummaryPayment']['user_id'] = $user_id;
						
							if($debug) {
								echo "<pre>";
								print_r($data);
								echo "</pre>";
							}
							$SummaryPayment->create();
							$SummaryPayment->save($data);
															
						} // end if($importo!='0,00') 
					} // foreach ($users as $user_id => $importo) 
				}
				else  {
					
					if($debug) {
						echo "<pre>";
						print_r($this->request->data['RequestPaymentsGeneric']);
						echo "</pre>";
					}
					
					/*
					 * creo occorrenza in SummaryPayment
					*/
					if($this->request->data['RequestPaymentsGeneric']['dest_options_qta']=='SOME')
						$users = $this->request->data['RequestPaymentsGeneric']['users'];
					else 
					if($this->request->data['RequestPaymentsGeneric']['dest_options_qta']=='ALL') {
						App::import('Model', 'User');
						$User = new User;
						
						$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
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
							$this->Session->setFlash(__('The request payments generic has been saved'));
						else
							$this->Session->setFlash(__('The request payments could not be saved. Please, try again.'));
						
						$data = null;
						/*
						 * ctrl se esiste gia' un'occorrenza in SummaryPayment, se SI => update
						* */
						$options = array();
						$options['conditions'] = array('SummaryPayment.organization_id' => (int)$this->user->organization['Organization']['id'],
														'SummaryPayment.user_id' => (int)$user_id,
														'SummaryPayment.request_payment_id' => (int)$id);
						$options['fields'] = array('id','importo_dovuto','importo_richiesto');
						$options['recursive'] = -1;
						$resultCtrl = $SummaryPayment->find('first', $options);
						if(!empty($resultCtrl)) {
							// UPDATE
							$data['SummaryPayment']['id'] = $resultCtrl['SummaryPayment']['id'];
							$data['SummaryPayment']['importo_dovuto'] = ($resultCtrl['SummaryPayment']['importo_dovuto'] + $importo);
							$data['SummaryPayment']['importo_richiesto'] = $data['SummaryPayment']['importo_dovuto'];
						}
						else {
							// INSERT
							$data['SummaryPayment']['importo_dovuto'] = $importo;  // $this->importoToDatabase($importo);
							$data['SummaryPayment']['importo_richiesto'] = $importo;  // $this->importoToDatabase($importo);
						}
						$data['SummaryPayment']['organization_id'] = $this->user->organization['Organization']['id'];
						$data['SummaryPayment']['request_payment_id'] = $id;
						$data['SummaryPayment']['user_id'] = $user_id;
		
						$SummaryPayment->create();
						$SummaryPayment->save($data);
						
					} // end if($this->request->data['RequestPaymentsGeneric']['dest_options_qta']=='SOME_DIFF')
				} // end foreach ($this->request->data['RequestPayment']['users'] as $user)
			
				if(!$debug) $this->myRedirect(array('controller' => 'RequestPayments', 'action' => 'edit', 'id' => $id));
		} // if ($this->request->is('post') || $this->request->is('put'))
		
		$dest_options_qta = array('ALL' => 'A tutti',
								  'SOME' => 'Ad alcuni',
								  'SOME_DIFF' => 'Ad alcuni con importi diversi');
		
		$this->set(compact('dest_options_qta'));
		
		App::import('Model', 'User');
		$User = new User;
		
		$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
		$users = $User->getUsersList($this->user, $conditions);
		$this->set('users',$users);	
	}	
	
	public function admin_delete($id = null) {
		
		$debug = false;
				$this->RequestPayment->id = $id;		if (!$this->RequestPayment->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}				if ($this->request->is('post') || $this->request->is('put') ) {
			
			/*
			 * riporto le consegne da CLOSE e OPEN
			 * riporto gli ordini da 'CLOSE' a 'TO-PAYMENT'
			 */
			$sql = "SELECT 
						`Order`.id, `Order`.delivery_id    
				FROM 
					".Configure::read('DB.prefix')."request_payments_orders as RequestPaymentsOrder, 
					".Configure::read('DB.prefix')."orders as `Order`  
				WHERE 
					RequestPaymentsOrder.organization_id = ".(int)$this->user->organization['Organization']['id']." 
				    and `Order`.organization_id = ".(int)$this->user->organization['Organization']['id']." 
				    and RequestPaymentsOrder.order_id = `Order`.id  
				    and RequestPaymentsOrder.request_payment_id = ".$id."
				ORDER BY 
					  `Order`.delivery_id, `Order`.id ";
			if($debug) echo '<br />'.$sql;
			$results = $this->RequestPayment->query($sql);
			foreach($results as $result) {
				
				/*
				 * riporto gli ordini da 'CLOSE' a 'TO-PAYMENT'
				 */
				$sql = "UPDATE
							`".Configure::read('DB.prefix')."orders`
						SET
							state_code = 'TO-PAYMENT',
							modified = '".date('Y-m-d H:i:s')."'
						WHERE
							organization_id = ".(int)$this->user->organization['Organization']['id']."
							and id = ".(int)$result['Order']['id'];
				if($debug) echo '<br />'.$sql;
				$resultUpdate = $this->RequestPayment->query($sql);
				
				 /*
				 * riporto le consegne da CLOSE e OPEN
				 */
				 $sql = "UPDATE
							`".Configure::read('DB.prefix')."deliveries`
						SET
							stato_elaborazione = 'OPEN',
							modified = '".date('Y-m-d H:i:s')."'
						WHERE
							organization_id = ".(int)$this->user->organization['Organization']['id']."
							and id = ".(int)$result['Order']['delivery_id'];
				if($debug)echo '<br />'.$sql;
				$resultUpdate = $this->RequestPayment->query($sql);				
			} // foreach($results as $result)

			if($this->RequestPayment->delete()) 
				$this->Session->setFlash(__('Delete Request Payment'));
			else				$this->Session->setFlash(__('Request payment was not deleted'));
			if(!$debug) $this->myRedirect(array('action' => 'index'));		}
		
		/*		 * estraggo gli utenti che hanno gia' concluso il pagamento 
		 * SummaryPayment.stato != 'DAPAGARE' cosi' prendo tutti i 'SOLLECITO1','SOLLECITO2','PAGATO','SOSPESO'		*/		$sql = "SELECT					User.id, User.name, User.username, User.email,				 	SummaryPayment.* 				FROM					".Configure::read('DB.portalPrefix')."users as User,					".Configure::read('DB.prefix')."summary_payments as SummaryPayment				WHERE					SummaryPayment.organization_id = ".(int)$this->user->organization['Organization']['id']."					and User.organization_id = ".(int)$this->user->organization['Organization']['id']."					and User.id = SummaryPayment.user_id
					and SummaryPayment.request_payment_id = $id
					and SummaryPayment.stato != 'DAPAGARE'				ORDER BY ".Configure::read('orderUser');		//echo '<br />'.$sql;
		try {			$summaryPaymentResults = $this->RequestPayment->query($sql);			$this->set('summaryPaymentResults',$summaryPaymentResults);		}
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
		$this->set('results', $results);
		$this->set('request_payment_empty', $this->__ctrl_request_payment_empty($this->user, $results));
		
	}

	public function admin_view() {

		/*
		 * permission
		 */
		App::import('Model', 'Order');		$Order = new Order;
				
		/*
		 * dati ordine
		 */		
		$Order->id = $this->order_id;		if (!$Order->exists($this->user->organization['Organization']['id'])) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}
		$options = array('conditions' => array('Order.' . $Order->primaryKey => $this->order_id));		$resultsOrder = $Order->find('first', $options);		$this->set(compact('resultsOrder'));		
		/*
		 * dati richiesta di pagamento
		 * ricavo il request_payment_id dall'order_id 
		 */		
		App::import('Model', 'RequestPaymentsOrder');		$RequestPaymentsOrder = new RequestPaymentsOrder;				$conditions = array('RequestPaymentsOrder.organization_id' => $this->user->organization['Organization']['id'],
							'RequestPaymentsOrder.order_id' => $resultsOrder['Order']['id']);
		$RequestPaymentsOrder->unbindModel(array('belongsTo' => array('Order')));
		$requestPaymentResults = $RequestPaymentsOrder->find('first', array('conditions' => $conditions, 'recursive' => 2));		$this->set('requestPaymentResults', $requestPaymentResults);
	
		$results = $this->RequestPayment->getAllDetails($this->user, $requestPaymentResults['RequestPaymentsOrder']['request_payment_id'], $conditions=array());		$this->set(compact('results'));
	}
	
	/*
	 * gli passo il contenuto di getAllDetails() e	 * ctrl che sia stata associata almeno una richesta (orders, storeroom, genereric)	*/
	private function __ctrl_request_payment_empty($user, $results) {
		
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
	
	private function __getNumMax($user) { 

		App::import('Model', 'Counter');
		$Counter = new Counter;
			
		$results = $Counter->getCounter($user, 'request_payments');
		
		return $results;
	}
	
	private function __getNumMaxAndUpdate($user) { 

		App::import('Model', 'Counter');
		$Counter = new Counter;
			
		$results = $Counter->getCounterAndUpdate($user, 'request_payments');
		
		return $results;
	}

	/*
	 * gestisco importo_richiesto
	 */			
	private function __edit_wait($data, $debug) {
	
		$msg = '';
		
		if($debug) {
			echo "<pre>";
			print_r($data['RequestPayment']);
			echo "</pre>";
		}
		
		if(isset($data['RequestPayment']['importo_richiesto']))
		foreach ($data['RequestPayment']['importo_richiesto'] as $summary_payment_id => $importo) {
				$sql = "UPDATE
							".Configure::read('DB.prefix')."summary_payments 
						SET
							importo_richiesto = ".$this->importoToDatabase($importo)."
						WHERE
							organization_id = ".(int)$this->user->organization['Organization']['id']."
							and id = ".(int)$summary_payment_id;
				if($debug) echo '<br />'.$sql;
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
	private function __edit_open($data, $debug) {
	
		$debug=false;
	
		$msg = '';

		if($debug) {
			echo "<pre>";
			print_r($data['RequestPayment']);
			echo "</pre>";
		}
		
		App::import('Model', 'SummaryPayment');
		$SummaryPayment = new SummaryPayment;
			
		App::import('Model', 'Cash');
				
		if(isset($data['RequestPayment']['importo_pagato']))		
		foreach ($data['RequestPayment']['importo_pagato'] as $summary_payment_id => $importo) {
				
			$stato = $data['RequestPayment']['stato'][$summary_payment_id];
			$stato_orig = $data['RequestPayment']['stato_orig'][$summary_payment_id];

			if($debug) {
				echo "<br />Per summary_payment_id ".$summary_payment_id." Da stato_orig ".$stato_orig." a stato ".$stato;
				if($stato == $stato_orig) echo " => Nessun cambiamento ";
				else {
					if($stato=='PAGATO') echo " => Aggiorno STATO, importo, Cash";
					else echo " => Aggiorno solo STATO";
				}
				
			}
				
			if($stato != $stato_orig) {
					
				if($stato=='PAGATO') 
					$this->__edit_open_stato_PAGATO($summary_payment_id, $data, $debug);
				else
					$this->__edit_open_stato_NON_PAGATO($summary_payment_id, $data, $debug);
			}
				
		} // end foreach ($data['RequestPayment']['stato'] as $summary_payment_id => $stato)
			
		return $msg; 				
	}	
	
	/*
	 *  memorizzo il nuovo SummaryPayment.stato
	 *		l'importo_pagato (anche 0,00 perche' puo' prendere tutto dalla cassa)
	 *		Casch
	 */
	private function __edit_open_stato_PAGATO($summary_payment_id, $data, $debug) {

		$msg = '';
		
		$user_id = $data['RequestPayment']['user_id'][$summary_payment_id];
		$importo_dovuto = $data['RequestPayment']['importo_dovuto'][$summary_payment_id];
		$importo_richiesto = $data['RequestPayment']['importo_richiesto'][$summary_payment_id];
		$importo_pagato = $data['RequestPayment']['importo_pagato'][$summary_payment_id];
		$stato = $data['RequestPayment']['stato'][$summary_payment_id];
			
		if($debug) {
			echo "<br />importo_dovuto ".$importo_dovuto;
			echo "<br />importo_richiesto ".$importo_richiesto;
			echo "<br />importo_pagato ".$importo_pagato;
		}
				
		/*
		 * modalita':  summary_payments 'DEFINED', 'CONTANTI', 'BANCOMAT', 'BONIFICO'
		 */
		if(isset($data['RequestPayment']['modalita'][$summary_payment_id]))
			$modalita = $data['RequestPayment']['modalita'][$summary_payment_id];
		else
			$modalita = 'DEFINED';
				
		$row = array();
		$row['SummaryPayment']['id'] = $summary_payment_id;
		$row['SummaryPayment']['user_id'] = $user_id;
		$row['SummaryPayment']['request_payment_id'] = $data['RequestPayment']['request_payment_id'];
		$row['SummaryPayment']['organization_id'] = $this->user->organization['Organization']['id'];
		$row['SummaryPayment']['importo_pagato'] = $importo_pagato;
		$row['SummaryPayment']['modalita'] = $modalita;
		$row['SummaryPayment']['stato'] = $stato;
				
		App::import('Model', 'SummaryPayment');
		$SummaryPayment = new SummaryPayment;	
								
		$SummaryPayment->create();
		if($debug) {
			echo "<pre>";
			print_r($row);
			echo "</pre>";
		}

		if (!$SummaryPayment->save($row)) 
			$msg .= "<br />SummaryPayment per lo user ".$summary_payment_id." in errore!";
					
		/*
		 * C A S H
		 */
		App::import('Model', 'Cash');
		$Cash = new Cash;
				 
		$importo_dovuto = $this->importoToDatabase($importo_dovuto);
		$importo_richiesto = $this->importoToDatabase($importo_richiesto);
		$delta_cassa = (-1 * (floatval($importo_dovuto) - floatval($importo_richiesto)));
		if($debug) 
			echo "<br />delta_cassa (importo_dovuto - importo_richiesto) => $importo_dovuto - $importo_richiesto = ".$delta_cassa;

		$options = array();
		$options['conditions'] = array('Cash.organization_id' => $this->user->organization['Organization']['id'],
										'Cash.user_id' => $user_id);
		$options['recursive'] = -1;
		$cashResults = $Cash->find('first', $options);
		if($debug) {
			echo "<pre>";
			print_r($cashResults);
			echo "</pre>";
		}
		
		if(empty($cashResults)) {
			if($delta_cassa != 0) {
				
				if($debug) echo "<br />INSERT CASH with user_id $user_id import ".$delta_cassa;
					
						/*
						 * INSERT CASH
						 */
						$data_cash = array();
						$data_cash['Cash']['user_id'] = $user_id;
						$data_cash['Cash']['organization_id'] = $this->user->organization['Organization']['id'];
						$data_cash['Cash']['importo'] = $delta_cassa;
					
						if($debug) {
							echo "<pre>";
							print_r($data_cash);
							echo "</pre>";
						}										   	
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
				if($debug) echo "<br />UPDATE CASH with user_id $user_id importo da ".$cashResults['Cash']['importo']." a ".$new_importo_cash;
						
				/*
				 * UPDATE CASH
				 */
				$cashResults['Cash']['importo'] = $new_importo_cash;	
						
				if($debug) {
					echo "<pre>";
					print_r($data_cash);
					echo "</pre>";
				}										   	
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
					
		if($debug) echo "<br />-----------------------------------------------------";
		
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
			// echo $sql;
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
	 */
	private function __edit_open_stato_NON_PAGATO($summary_payment_id, $data, $debug) {

		$msg = '';

		$row = array();
		$row['SummaryPayment']['id'] = $summary_payment_id;
		$row['SummaryPayment']['user_id'] = $data['RequestPayment']['user_id'][$summary_payment_id];
		$row['SummaryPayment']['request_payment_id'] = $data['RequestPayment']['request_payment_id'];
		$row['SummaryPayment']['organization_id'] = $this->user->organization['Organization']['id'];
		$row['SummaryPayment']['importo_pagato'] = '0.00';
		$row['SummaryPayment']['modalita'] = 'DEFINED';
		$row['SummaryPayment']['stato'] = $data['RequestPayment']['stato'][$summary_payment_id];
				
		App::import('Model', 'SummaryPayment');
		$SummaryPayment = new SummaryPayment;
					
		$SummaryPayment->create();
		if($debug) {
			echo "<pre>";
			print_r($row);
			echo "</pre>";
		}
		if (!$SummaryPayment->save($row)) 
			$msg .= "<br />SummaryPayment per lo user ".$summary_payment_id." in errore!";

		return $msg;
	}					
}