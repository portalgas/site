<?php
App::uses('AppController', 'Controller');

class CassiereController extends AppController {
	
	public $components = array('Documents');
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		if(!$this->isCassiereGeneric()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
	}
	
	public function admin_home() {
		
		$debug = false;
		if($this->user->organization['Template']['payToDelivery']!='ON' && $this->user->organization['Template']['payToDelivery']!='ON-POST') {			
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$this->set('isCassiere', $this->isCassiere());
		$this->set('isReferentCassiere', $this->isReferentCassiere()); 
	}
	
	/*
	 * chiude la consegna e tutti gli ordini associati
	 */
	public function admin_edit_stato_elaborazione($delivery_id=null) {
	
		if(empty($delivery_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
		
		$Delivery->id = $delivery_id;
		if (!$Delivery->exists($Delivery->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		$this->Cassiere->deliveryStatoClose($this->user, $delivery_id);
			
		$this->myRedirect(array('controller' => 'Cassiere', 'action' => 'home'));
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
									".Configure::read('DB.prefix')."orders
								SET
									".$sqlTmp."
									modified = '".date('Y-m-d H:i:s')."'
								WHERE
									organization_id = ".(int)$this->user->organization['Organization']['id']."
									and id = ".(int)$order_id;
						self::d($sql, $debug);
						$resultUpdate = $this->Cassiere->query($sql);
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
		
		$options = [];
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
		if (!$Delivery->exists($Delivery->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		$newResults = [];
		/*
		 * metto in testa gli ordini con l'ordine tesoriere_stato_pay = N
		*/
		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
														  'Order.isVisibleBackOffice != ' => 'N',
														  'Order.state_code != ' => 'CREATE-INCOMPLETE',
														  'Order.tesoriere_stato_pay' => 'N');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		
		$options = [];
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
			
			$options = [];
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
		$options = [];
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
	 *  consegne CLOSE per richiamare elenco ordini per visualizzare il pagamento
	*/
	public function admin_pay_suppliers_history() {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$options = [];
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
		if (!$Delivery->exists($Delivery->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		$newResults = [];

		$Delivery->hasMany['Order']['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
														'Order.isVisibleBackOffice != ' => 'N',
														'Order.state_code != ' => 'CREATE-INCOMPLETE');
		$Delivery->hasMany['Order']['order'] = array('Order.data_inizio', 'Order.data_fine');
		$options = [];
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
	 *  elenco consegne con ordini PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) da passare al Tesoriere
	*/
	public function admin_orders_to_wait_processed_tesoriere() {
		
		if($this->user->organization['Template']['payToDelivery']!='ON-POST' ||
		   $this->user->organization['Organization']['hasUserGroupsTesoriere']=='N'
		   /* || !$this->isCassiere() */ ) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$deliveries = $this->Cassiere->get_cassiere_deliveries($this->user, $this->isCassiere(), $this->isReferentCassiere());	
		$this->set(compact('deliveries'));
	}	

	/*
	 *  elenco ordini PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) da passare al Tesoriere
	*/
	public function admin_ajax_orders_to_wait_processed_tesoriere($delivery_id=0) {
		
		if($this->user->organization['Template']['payToDelivery']!='ON-POST' ||
		   $this->user->organization['Organization']['hasUserGroupsTesoriere']=='N' ||
		   !$this->isCassiere()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$results = $this->Cassiere->lists_orders_processed_on_delivery($this->user, $delivery_id);
		$this->set('results', $results);
		
		$this->layout = 'ajax';
	}	
	
	/*
	 *  form per passare l'ordine al tesoriere
	 * 	da 'PROCESSED-ON-DELIVERY' a 'WAIT-PROCESSED-TESORIERE'
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
			
			self::d($this->request->data['Order'], $debug);			
					
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
				$Tesoriere->updateAfterUpload($this->user, $this->request->data, $esito, 'CASSIERE', $debug);				
			
				/*
				 * delete fattura
				 */		
				if(isset($this->request->data['Order']['file1_delete']) && $this->request->data['Order']['file1_delete']=='Y') {
				
					$esito = $this->Documents->genericUpload($this->user, $results['Order']['tesoriere_doc1'], $path_upload, 'DELETE', '', '', '', '', $debug);
					$msg = $esito['msg'];
					self::d("msg UPLOAD ".$msg, $debug);
				}

				if($this->user->organization['Organization']['hasFieldFatturaRequired']=='Y' && $esito['msg']==4)
					$msg = __('upload_file_required');
				
				/*
				 * aggiorno stato ORDER => pulisco SummaryOrders
				*/
				$esito = $OrderLifeCycle->stateCodeUpdate($this->user, $this->order_id, 'WAIT-PROCESSED-TESORIERE');			
				if($debug) {
					echo "<br />msg ".$msg;
					exit;
				}

				$Tesoriere->sendMailToUpload($this->user, $this->request->data, $results, 'CASSIERE', $debug);	
			} // end if($continua)
				
			if(empty($msg)) {
				$this->Session->setFlash(__('Order State in Wait Processed Tesoriere'));
				$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Cassiere&action=orders_to_wait_processed_tesoriere&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
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
			$this->render('/Cassiere/admin_no_trasmit');
		}
		else
			$this->render('/Cassiere/admin_order_state_in_wait_processed_tesoriere');		
	}	
}