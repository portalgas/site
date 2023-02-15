<?php
App::uses('AppController', 'Controller');

class ReferenteController extends AppController {

    public $components = ['Documents'];
							   
	public function beforeFilter() {
		parent::beforeFilter();
		
		/* ctrl ACL */
		if($this->isSuperReferente()) {
			
		}
		else {
	   			
			App::import('Model', 'Order');
		   	$Order = new Order;
		   	if(empty($this->order_id) || !$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
		   		$this->Session->setFlash(__('msg_not_permission'));
		   		$this->myRedirect(Configure::read('routes_msg_stop'));
		   	}
		}
	}

	/*
	 * richiamato solo dal referente per passare l'ordine al TESORIERE
	 * 	da 'PROCESSED-POST-DELIVERY' a 'WAIT-PROCESSED-TESORIERE'
	 *
	 * invio messaggio al tesoriere
	 * 		 documento della fattura
	*/
	public function admin_order_state_in_WAIT_PROCESSED_TESORIERE() {
	
		$debug=false;

		App::import('Model', 'Order');
		$Order = new Order;

        App::import('Model', 'OrderLifeCycle');
        $OrderLifeCycle = new OrderLifeCycle();
		
		$msg = '';
		$Order->id = $this->order_id;
		if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * dati dell'ordine
		*/
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
								  'Order.id' => $this->order_id];
		$options['recursive'] = 0;
		$results = $Order->find('first', $options);
		$this->set(compact('results'));
		
		if ($this->request->is('post') || $this->request->is('put')) {
		
		    $this->request->data['Order']['id'] = $this->order_id;
			self::d($this->request->data);
			
			App::import('Model', 'Tesoriere');
			$Tesoriere = new Tesoriere;

			$path_upload = Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS;
			/*
			 * upload fattura
			 */		
			$continua=true;
			if(!empty($this->request->data['Order']['tesoriere_doc1']['name'])){
			
				$arr_extensions = array_merge(Configure::read('App.web.pdf.upload.extension'), Configure::read('App.web.img.upload.extension'));
				$arr_extensions = array_merge($arr_extensions, Configure::read('App.web.zip.upload.extension'));
				$arr_contentTypes = array_merge(Configure::read('ContentType.pdf'),Configure::read('ContentType.img'));
				$arr_contentTypes = array_merge($arr_contentTypes,Configure::read('ContentType.zip'));
							
				$newName = $results['Order']['delivery_id'].'-'.$results['Order']['supplier_organization_id'].'-'.$this->request->data['Order']['tesoriere_doc1']['name'];	
				$esito = $this->Documents->genericUpload($this->user, $this->request->data['Order']['tesoriere_doc1'], $path_upload, 'UPLOAD', $newName, $arr_extensions, $arr_contentTypes, '', $debug);
				if(!empty($esito['msg'])) {
					$msg = $esito['msg'];
					$continua=false;
				}
				self::d("msg UPLOAD ".$msg, $debug);
			}
			
			if($continua) {
				/*
				 * aggiorno tesoriere_fattura_importo / tesoriere_nota / tesoriere_doc1 ...
				 */
				$Tesoriere->updateAfterUpload($this->user, $this->request->data, $esito, 'REFERENTE', $debug);
				
				/*
				 * delete fattura
				 */		
				if(isset($this->request->data['Order']['file1_delete']) && $this->request->data['Order']['file1_delete']=='Y') {
											
					$esito = $this->Documents->genericUpload($this->user, $results['Order']['tesoriere_doc1'], $path_upload, 'DELETE', '', '', '', '', $debug);
						$msg = $esito['msg'];
						self::d("msg UPLOAD ".$msg, $debug);
				}
			

				/*
				 * aggiorno stato ORDER
				*/					        
		        $options = [];
		        if(isset($this->request->data['Order']['file1_delete']) && $this->request->data['Order']['file1_delete']=='Y') 
		        	$options['tesoriere_doc1'] = '';
		        $esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'WAIT-PROCESSED-TESORIERE', $options, $debug);
		        if($esito['CODE']!=200) {
		        	$msg = $esito['MSG'];
		        	$continue = false;
		        } 
			}
			
			if($continua) {					
				if($this->user->organization['Organization']['hasFieldFatturaRequired']=='Y' && $esito['msg']==4)
					$msg = __('upload_file_required');
				
				if($debug) {
					echo "<br />msg ".$msg;
					exit;
				}

				$Tesoriere->sendMailToUpload($this->user, $this->request->data, $results, 'REFERENTE', $debug);	
			} // end if($continua)
			
			if(empty($msg)) {
				$this->Session->setFlash(__('Order State in Wait Processed Tesoriere'));
				$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
			}
			else {
				$this->Session->setFlash($msg);
			}
		} // if ($this->request->is('post') || $this->request->is('put'))
			
		$options = [];
		$options['conditions'] = ['Order.id' => $this->order_id,
								  'Order.organization_id' => $this->user->organization['Organization']['id']];
		$options['recursive'] = 1;
		$this->request->data = $Order->find('first', $options);
		if (empty($this->request->data)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
						
		/*
		 * calcolo il totale degli importi degli acquisti dell'ordine
		*/
		$importo_totale = $Order->getTotImporto($this->user, $this->order_id, $debug);
		$this->set('importo_totale', $importo_totale);
		
		/*
		 * fattura per il tesoriere
		 */
		if(!empty($this->request->data['Order']['tesoriere_doc1']) &&
		file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS.$this->request->data['Order']['tesoriere_doc1'])) {
		
			$file1 = new File(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS.$this->request->data['Order']['tesoriere_doc1']);
			$this->set('file1', $file1);
		}

		$msg = $OrderLifeCycle->beforeRendering($this->user, $this->request->data, $this->request->params['controller'], $this->action);
		if(!empty($msg['isOrderValidateToTrasmit'])) {
			$this->set('msg', $msg['isOrderValidateToTrasmit']);
			$this->render('/Referente/admin_no_trasmit');
		}
		else
			$this->render('/Referente/admin_order_state_in_wait_processed_tesoriere');
	}
	
	/*
	 * richiamato solo dal referente per riportare l'ordine 
	 * 	da 'WAIT-PROCESSED-TESORIERE' in 'PROCESSED-POST-DELIVERY'
	 *  o da 
	*/
	public function admin_order_state_in_PROCESSED_POST_DELIVERY() {

    	$msg = '';
    	$continue = true;
	
		App::import('Model', 'Order');
		$Order = new Order;
		
		$Order->id = $this->order_id;
		if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$order = $Order->read($this->order_id, $this->user->organization['Organization']['id']);
	
	
		/*
		 * aggiorno stato ORDER
		*/
        App::import('Model', 'OrderLifeCycle');
        $OrderLifeCycle = new OrderLifeCycle();
        
        $options = [];
        $esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'PROCESSED-POST-DELIVERY', $options, $debug);
        if($esito['CODE']!=200) {
        	$msg = $esito['MSG'];
        	$continue = false;
		}        	
		        	
		if($continue) 
			$msg = __('OrderStateCodeUpdateNowReferentWorking');
		
		$this->Session->setFlash($msg);
		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
	}
	
	/*
	 * payToDelivery = ON o payToDelivery = ON-POST
	 * richiamato solo dal referente per riportare l'ordine 
	 * 	da 'PROCESSED-BEFORE-DELIVERY' a 'INCOMING-ORDER'   da "prima della consegna" a la merce e' arrivata
	 * oppure
	 * 	da 'PROCESSED-ON-DELIVERY' a 'INCOMING-ORDER'  da "ordine confermato" a  la merce e' arrivata
	*/
	public function admin_order_state_in_INCOMING_ORDER() {
	
		$msg = '';
		$continue = true;
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$Order->id = $this->order_id;
		if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$order = $Order->read($this->order_id, $this->user->organization['Organization']['id']);
	
		/*
		 * aggiorno stato ORDER =>  pulisco SummaryOrders
		*/			
        App::import('Model', 'OrderLifeCycle');
        $OrderLifeCycle = new OrderLifeCycle();
        
        $options = [];
        $options['data_incoming_order'] = date('Y-m-d');
        $esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'INCOMING-ORDER', $options, $debug);
        if($esito['CODE']!=200) {
        	$msg = $esito['MSG'];
        	$continue = false;
        } 
		else
			$msg = __('OrderStateCodeUpdate');
		
		$this->Session->setFlash($msg);
		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
	}

	/*
	 * payToDelivery = ON o payToDelivery = ON-POST
	 * richiamato solo dal referente per riportare l'ordine 
	 * da 'INCOMING-ORDER' a 'PROCESSED-BEFORE-DELIVERY'   la merce NON e' arrivata
	 * 
	 * cancello eventuali dati aggregati / trasporto ..., la merce non e' arrivata e il referente 
	 *		puo' modificare acquisti
	 *		dati aggregato / trasporto ... gia' calcolati possono essere errati
	 */
	public function admin_order_state_in_PROCESSED_BEFORE_DELIVERY() {
	
		$debug = false;
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$Order->id = $this->order_id;
		if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$order = $Order->read($this->order_id, $this->user->organization['Organization']['id']);
	
		/*
		 * aggiorno stato ORDER
		*/
		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;
		
		$esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'PROCESSED-BEFORE-DELIVERY');
        if($esito['CODE']!=200) {
        	$msg = $esito['MSG'];
        	$continue = false;
        } 
		else
			$msg = __('OrderStateCodeUpdate');
			
		$this->Session->setFlash($msg);
		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
	}
		
	/*
	 * payToDelivery = ON o payToDelivery = ON-POST
	 * richiamato solo dal referente per portare l'ordine al CASSIERE
	 * da 'INCOMING-ORDER' a 'PROCESSED-ON-DELIVERY' ho controllato la merce arrivata e confermo gli importi
	 * 
	 * se non e' popolato creo SummaryOrder per eventuale pagamento cassa (importo_pagato, modalita)
	*/
	public function admin_order_state_in_PROCESSED_ON_DELIVERY() {
	
		$debug = false;
		
		App::import('Model', 'Order');
		$Order = new Order;

		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;
		
		$Order->id = $this->order_id;
		if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		/*
		 * dati dell'ordine
		*/
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $this->user->organization['Organization']['id'],
								   'Order.id' => $this->order_id];
		$options['recursive'] = 0;
		$results = $Order->find('first', $options);
		$this->set(compact('results'));
					
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['Order']['id'] = $this->order_id;
			self::dd($this->request->data, $debug);
			
			App::import('Model', 'Tesoriere');
			$Tesoriere = new Tesoriere;

			$path_upload = Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS;
			/*
			 * upload fattura
			 */		
			$continua=true; 
			if(!empty($this->request->data['Order']['tesoriere_doc1']['name'])){
			
				$arr_extensions = array_merge(Configure::read('App.web.pdf.upload.extension'), Configure::read('App.web.img.upload.extension'));
				$arr_extensions = array_merge($arr_extensions, Configure::read('App.web.zip.upload.extension'));
				$arr_contentTypes = array_merge(Configure::read('ContentType.pdf'),Configure::read('ContentType.img'));
				$arr_contentTypes = array_merge($arr_contentTypes,Configure::read('ContentType.zip'));
						
				$newName = $results['Order']['delivery_id'].'-'.$results['Order']['supplier_organization_id'].'-'.$this->request->data['Order']['tesoriere_doc1']['name'];	
				$esito = $this->Documents->genericUpload($this->user, $this->request->data['Order']['tesoriere_doc1'], $path_upload, 'UPLOAD', $newName, $arr_extensions, $arr_contentTypes, '', $debug);
				if(!empty($esito['msg'])) {	
					$msg = $esito['msg'];
					$continua=false;
				}	
				self::d("msg UPLOAD ".$msg, $debug);
			}
			
			if($continua) {
				/*
				 * aggiorno tesoriere_fattura_importo / tesoriere_nota / tesoriere_doc1 ...
				 */
				$Tesoriere->updateAfterUpload($this->user, $this->request->data, $esito, 'REFERENTE', $debug);
				
				/*
				 * delete fattura
				 */		
				if(isset($this->request->data['Order']['file1_delete']) && $this->request->data['Order']['file1_delete']=='Y') {
								
					$esito = $this->Documents->genericUpload($this->user, $results['Order']['tesoriere_doc1'], $path_upload, 'DELETE', '', '', '', '', $debug);
					$msg = $esito['msg'];
					self::d("msg UPLOAD ".$msg, $debug);
				}
				 
				if($results['Delivery']['sys']=='N') 
					$state_code_next = 'PROCESSED-ON-DELIVERY'; // lo passo al cassiere
				else
					$state_code_next = 'CLOSE'; // lo chiudo NON + 
			
				/*
				 * aggiorno stato ORDER, gli passo order_id cosi' prende le modifiche effettuate in $Tesoriere->updateAfterUpload()
				*/				
				$OrderLifeCycle->stateCodeUpdate($this->user, $this->request->data['Order']['id'], $state_code_next);
			} // end if($continua)
				
			if(empty($msg)) {
				$this->Session->setFlash(__('OrderStateCodeUpdate'));
				$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
			}
			else {
				$this->Session->setFlash($msg);
			}					
		} // end POST

		$options = [];
		$options['conditions'] = ['Order.id' => $this->order_id,
								   'Order.organization_id' => $this->user->organization['Organization']['id']];
		$options['recursive'] = 1;
		$this->request->data = $Order->find('first', $options);
		if (empty($this->request->data)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * calcolo il totale degli importi degli acquisti dell'ordine
		*/
		$importo_totale = $Order->getTotImporto($this->user, $this->order_id, $debug);
		$this->set('importo_totale', $importo_totale);
		
		/*
		 * fattura per il tesoriere
		 */
		if(!empty($this->request->data['Order']['tesoriere_doc1']) &&
		file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS.$this->request->data['Order']['tesoriere_doc1'])) {
		
			$file1 = new File(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS.$this->request->data['Order']['tesoriere_doc1']);
			$this->set('file1', $file1);
		}
		
		$msg = $OrderLifeCycle->beforeRendering($this->user, $this->request->data, $this->request->params['controller'], $this->action);
		if(!empty($msg['isOrderValidateToTrasmit'])) {
			$this->set('msg', $msg['isOrderValidateToTrasmit']);
			$this->render('/Referente/admin_no_trasmit');
		}
		else {
			$msg = "Se hai effettuato tutte le modifiche potrai passarlo al cassiere: <br /><br /><ul><li>il cassiere potrà gestire il pagamento durante la consegna.</li><li>i gasisti potranno scaricarsi il PDF con gli importi corretti.</li></ul><br />";
			$this->set(compact('msg'));
			$this->render('/Referente/admin_order_state_in_processed_on_delivery');
		}
	}
	
	/*
	 * richiamato solo dal referenteTesoriere per portare l'ordine 
	 * 	da 'PROCESSED-POST-DELIVERY' in 'TO-REQUEST-PAYMENT'
	 */
	public function admin_order_state_in_TO_PAYMENT() {

		/*
		 * ctrl referentTesoriere
		*/
		if($this->isReferentTesoriere())
			$isReferenteTesoriere = true;
		else
			$isReferenteTesoriere = false;
		if(!$isReferenteTesoriere) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$Order->id = $this->order_id;
		if (!$Order->exists($Order->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$order = $Order->read($this->order_id, $this->user->organization['Organization']['id']);
				
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$conditions = ['Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
						'Delivery.isVisibleBackOffice' => 'Y',
						'Delivery.sys' => 'N',
						'Delivery.type'=> 'GAS', // GAS-GROUP
						'Delivery.stato_elaborazione' => 'OPEN'];
			
		$deliveries = $Delivery->find('list', ['fields' => ['id', 'luogoData'], 'conditions' => $conditions, 'order' => 'data ASC', 'recursive' => -1]);
		if(empty($deliveries)) {
			$this->Session->setFlash(__('NotFoundDeliveries'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$this->set(compact('deliveries'));
		
		
		/*
		 * aggiorno stato ORDER
		*/
		App::import('Model', 'OrderLifeCycle');
		$OrderLifeCycle = new OrderLifeCycle;
		
		$OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'TO-REQUEST-PAYMENT');
					
		$this->Session->setFlash('OrderStateCodeUpdateNowRequestPayment');
		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
	}
}