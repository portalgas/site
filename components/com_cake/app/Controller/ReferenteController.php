<?php
App::uses('AppController', 'Controller');

class ReferenteController extends AppController {

    public $components = array('Documents');
							   
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
		
		$msg = '';
		$Order->id = $this->order_id;
		if (!$Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * dati dell'ordine
		*/
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
									   'Order.id' => $this->order_id);
		$options['recursive'] = 0;
		$results = $Order->find('first', $options);
		$this->set(compact('results'));
		
		if ($this->request->is('post') || $this->request->is('put')) {
		
		    $this->request->data['Order']['id'] = $this->order_id;
			if($debug) {
				echo "<pre>this->request->data \n";
				print_r($this->request->data['Order']);
				echo "</pre>";
			}
			
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
				if($debug)
					echo "<br  />msg UPLOAD ".$msg;
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
						if($debug)
							echo "<br  />msg UPLOAD ".$msg;
				}
			

				/*
				 * aggiorno stato ORDER
				*/			
		        App::import('Model', 'OrderLifeCycle');
		        $OrderLifeCycle = new OrderLifeCycle();
		        
		        $options = array();
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
			
		$options = array();
		$options['conditions'] = array('Order.id' => $this->order_id,
									   'Order.organization_id' => $this->user->organization['Organization']['id']);
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
		/* 
		 *  bugs float: i float li converte gia' con la virgola!  li riporto flaot
		 */
		if(strpos($importo_totale,',')!==false)  $importo_totale = str_replace(',','.',$importo_totale);
		$this->set('importo_totale', $importo_totale);
		
		/*
		 * fattura per il tesoriere
		 */
		if(!empty($this->request->data['Order']['tesoriere_doc1']) &&
		file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS.$this->request->data['Order']['tesoriere_doc1'])) {
		
			$file1 = new File(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS.$this->request->data['Order']['tesoriere_doc1']);
			$this->set('file1', $file1);
		}

		$msg = $Order->isOrderValidateToTrasmit($this->user, $this->order_id);
		if(!empty($msg)) {
			$this->set(compact('msg'));
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
		if (!$Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$order = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
	
	
		/*
		 * aggiorno stato ORDER
		*/
        App::import('Model', 'OrderLifeCycle');
        $OrderLifeCycle = new OrderLifeCycle();
        
        $options = array();
        $esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'PROCESSED-POST-DELIVERY', $options, $debug);
        if($esito['CODE']!=200) {
        	$msg = $esito['MSG'];
        	$continue = false;
		}        	
		        	
		if($continue) 
			$msg = __("Lo stato dell'ordine è stato aggiornato: ora il referente potrà modificarlo.");
		
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
		if (!$Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$order = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
	
		/*
		 * aggiorno stato ORDER
		*/			
        App::import('Model', 'OrderLifeCycle');
        $OrderLifeCycle = new OrderLifeCycle();
        
        $options = array();
        $options['data_incoming_order'] = date('Y-m-d');
        $esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'INCOMING-ORDER', $options, $debug);
        if($esito['CODE']!=200) {
        	$msg = $esito['MSG'];
        	$continue = false;
        } 

		if($continue) 
			$msg = __("Lo stato dell'ordine è stato aggiornato.");
		
		$this->Session->setFlash($msg);

		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
	}

	/*
	 * payToDelivery = ON o payToDelivery = ON-POST
	 * richiamato solo dal referente per riportare l'ordine 
	 * da 'INCOMING-ORDER' a 'PROCESSED-BEFORE-DELIVERY'   la merce NON e' arrivata
	 * 
	 * NON cancello i dati modificati dal referente perche' l'ordine e' cmq chiuso e quelle modifiche sono forse valide
	*/
	public function admin_order_state_in_PROCESSED_BEFORE_DELIVERY() {
	
		$debug = false;
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$Order->id = $this->order_id;
		if (!$Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$order = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
	
		/*
		 * aggiorno stato ORDER
		*/
		$sql = "UPDATE
					`".Configure::read('DB.prefix')."orders`
				SET
					state_code = 'PROCESSED-BEFORE-DELIVERY',
					data_incoming_order = '0000-00-00', 
					modified = '".date('Y-m-d H:i:s')."'
				WHERE
					organization_id = ".(int)$this->user->organization['Organization']['id']."
					and id = ".(int)$this->order_id;
	
		$result = $Order->query($sql);
		$this->Session->setFlash(__('Lo stato dell\'ordine è stato aggiornato.'));
		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
	}
		
	/*
	 * payToDelivery = ON o payToDelivery = ON-POST
	 * richiamato solo dal referente per portare l'ordine al CASSIERE
	 * da 'INCOMING-ORDER' a 'PROCESSED-ON-DELIVERY'    ho controllato la merce arrivata e confermo gli importi
	 * 
	 * se non e' popolato creo SummaryOrder per eventuale pagamento cassa (importo_pagato, modalita)
	*/
	public function admin_order_state_in_PROCESSED_ON_DELIVERY() {
	
		$debug = false;
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$Order->id = $this->order_id;
		if (!$Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		/*
		 * dati dell'ordine
		*/
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
									   'Order.id' => $this->order_id);
		$options['recursive'] = 0;
		$results = $Order->find('first', $options);
		$this->set(compact('results'));
					
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['Order']['id'] = $this->order_id;
			if($debug) {
				echo "<pre>this->request->data \n";
				print_r($this->request->data['Order']);
				echo "</pre>";
			}			
			
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
				if($debug)
					echo "<br  />msg UPLOAD ".$msg;
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
					if($debug)
						echo "<br  />msg UPLOAD ".$msg;
				}
				 
				/*
				 * ctrl eventuali occorrenze di SummaryOrder, se non ci sono lo popolo
				 * mi servira' per gestire la cassa
				*/
				if($results['Delivery']['sys']=='N')  {
					App::import('Model', 'SummaryOrder');
					$SummaryOrder = new SummaryOrder;				
					$resultsSummaryOrder = $SummaryOrder->select_to_order($this->user, $this->order_id);
					if(empty($resultsSummaryOrder))
						$SummaryOrder->populate_to_order($this->user, $this->order_id, 0);
						
					if($results['Delivery']['sys']=='N') 
						$state_code_next = 'PROCESSED-ON-DELIVERY'; // lo passo al cassiere
					else
						$state_code_next = 'CLOSE'; // lo chiudo NON + 
				
					/*
					 * aggiorno stato ORDER
					*/
					$sql = "UPDATE
								`".Configure::read('DB.prefix')."orders`
							SET
								state_code = '".$state_code_next."',
								modified = '".date('Y-m-d H:i:s')."'
							WHERE
								organization_id = ".(int)$this->user->organization['Organization']['id']."
								and id = ".(int)$this->order_id;
					// echo $sql;
					$result = $Order->query($sql);
				} // if($results['Delivery']['sys']=='N')
			} // end if($continua)
				
			if(empty($msg)) {
				$this->Session->setFlash(__('Lo stato dell\'ordine è stato aggiornato.'));
				$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
			}
			else {
				$this->Session->setFlash($msg);
			}					
		} // end POST

		$options = array();
		$options['conditions'] = array('Order.id' => $this->order_id,
									   'Order.organization_id' => $this->user->organization['Organization']['id']);
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
		/* 
		 *  bugs float: i float li converte gia' con la virgola!  li riporto flaot
		 */
		if(strpos($importo_totale,',')!==false)  $importo_totale = str_replace(',','.',$importo_totale);
		$this->set('importo_totale', $importo_totale);
		
		/*
		 * fattura per il tesoriere
		 */
		if(!empty($this->request->data['Order']['tesoriere_doc1']) &&
		file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS.$this->request->data['Order']['tesoriere_doc1'])) {
		
			$file1 = new File(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$this->user->organization['Organization']['id'].DS.$this->request->data['Order']['tesoriere_doc1']);
			$this->set('file1', $file1);
		}
		
		$msg = $Order->isOrderValidateToTrasmit($this->user, $this->order_id);
		if(!empty($msg)) {
			$this->set(compact('msg'));
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
	 * 	da 'PROCESSED-POST-DELIVERY' in 'TO-PAYMENT'
	 */
	public function admin_order_state_in_TO_PAYMENT() {

		/*
		 * ctrl referentTesoriere
		*/
		if($this->isReferentTesoriere())
			$isReferenteTesoriere = true;
		else
			$isReferenteTesoriere = false;		if(!$isReferenteTesoriere) {			$this->Session->setFlash(__('msg_not_permission'));			$this->myRedirect(Configure::read('routes_msg_stop'));		}
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$Order->id = $this->order_id;
		if (!$Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$order = $Order->read($this->user->organization['Organization']['id'], null, $this->order_id);
		
		/*		 * ctrl eventuali occorrenze di SummaryOrder		* 		se il referenteTesoriere non e' mai passato da Carts::managementCartsGroupByUsers e' vuoto		*/
		App::import('Model', 'SummaryOrder');		$SummaryOrder = new SummaryOrder;				$results = $SummaryOrder->select_to_order($this->user, $this->order_id);		if(empty($results))			$SummaryOrder->populate_to_order($this->user, $this->order_id, 0);				App::import('Model', 'Delivery');		$Delivery = new Delivery;				$conditions = array('Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],							'Delivery.isVisibleBackOffice' => 'Y',
							'Delivery.sys' => 'N',							'Delivery.stato_elaborazione' => 'OPEN');					$deliveries = $Delivery->find('list',array('fields'=>array('id', 'luogoData'),'conditions'=>$conditions,'order'=>'data ASC','recursive'=>-1));		if(empty($deliveries)) {			$this->Session->setFlash(__('NotFoundDeliveries'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}		$this->set(compact('deliveries'));		
		
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
					and id = ".(int)$this->order_id;
		
		$result = $Order->query($sql);
		$this->Session->setFlash("Lo stato dell'ordine è stato aggiornato: ora si potrà richiedere il pagamento.");
		
		$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
	}
}