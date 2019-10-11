<?php
App::uses('AppController', 'Controller');
App::uses('CakeTime', 'Utility');

class DesOrdersController extends AppController {
   
   public $components = array('ActionsDesOrder');
   
   public function beforeFilter() {
   		parent::beforeFilter();
   		
		/* ctrl ACL */
   		if($this->user->organization['Organization']['hasDes']=='N') {
   			$this->Session->setFlash(__('msg_not_organization_config'));
   			$this->myRedirect(Configure::read('routes_msg_stop'));
   		}
   		
		if(empty($this->user->des_id)) {
            $this->Session->setFlash(__('msg_des_choice'));
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Des&action=index';
			$this->myRedirect($url);
        }
		
   		/*
   	   	$actionWithPermission = array('admin_index');
	   	if (in_array($this->action, $actionWithPermission)) {
   			 // possono tutti perche' e' la pg di atterraggio		 
   		}
   		else {
	   		if(!$this->isTitolareDesSupplier() && $this->isSuperReferenteDes() && !$this->isReferenteDes()) {
	   			$this->Session->setFlash(__('msg_not_organization_config'));
	   			$this->myRedirect(Configure::read('routes_msg_stop'));
	   		}
	   	}
	   	*/
		/* ctrl ACL */
		
  		$this->set('isManagerDes', $this->isManagerDes());
   		$this->set('isReferenteDes', $this->isReferenteDes());
   		$this->set('isSuperReferenteDes', $this->isSuperReferenteDes());
   		$this->set('isTitolareDesSupplier', $this->isTitolareDesSupplier());		
   }
   
	/*
	 *  estraggo tutti gli ordini delle organization del DES
	 *  con 'DATE(DesOrder.data_fine_max) >= CURDATE() - INTERVAL ' . Configure::read('GGDesOrdersOld') . ' DAY'));	 
	 */
    public function admin_index() {
   	
   		$debug = false;
		
		$ACLsuppliersIdsDes = $this->user->get('ACLsuppliersIdsDes');
		if(empty($ACLsuppliersIdsDes)) 
			$ACLsuppliersIdsDes = 0;
		
  		/*
		 * aggiorno lo stato del desOrders
		* */
		$utilsCrons = new UtilsCrons(new View(null));
		if(Configure::read('developer.mode')) echo "<pre>";
		$utilsCrons->desOrdersStatoElaborazione($this->user->des_id, 0, (Configure::read('developer.mode')) ? true : false);
		if(Configure::read('developer.mode')) echo "</pre>";		   			

		/*
		 * cancello desOrders vecchio
		 */
		if(Configure::read('developer.mode')) echo "<pre>";
		$utilsCrons->desOrdersDelete($this->user->des_id, 0, (Configure::read('developer.mode')) ? true : false);
		if(Configure::read('developer.mode')) echo "</pre>";		   			

				
		$options = [];
		$options['recursive'] = -1;
 		$options['conditions'] = ['DesOrder.des_id' => $this->user->des_id, 
									   'DATE(DesOrder.data_fine_max) >= CURDATE() - INTERVAL ' . Configure::read('GGDesOrdersOld') . ' DAY'];
 		if(!$this->isSuperReferenteDes())
 			$options['conditions'] += ['DesOrder.des_supplier_id IN ('.$ACLsuppliersIdsDes.')'];
		$options['order'] = ['DesOrder.data_fine_max desc', 'DesOrder.id'];
		$results = $this->DesOrder->find('all', $options);
	
		$newResults = [];
		foreach($results as $numResult => $result) {
			$newResults[$numResult] = $this->DesOrder->getDesOrder($this->user, $result['DesOrder']['id']);
			
			/*
			 * per action Edit, Delete
			 */
			$isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $result['DesOrder']['id']);		
			$newResults[$numResult]['DesOrder']['isTitolareDesSupplier'] = $isTitolareDesSupplier;
		}
		
		self::d($newResults, $debug);
		
		$this->set('results', $newResults);
		
		$group_id = Configure::read('group_id_titolare_des_supplier');
		$desOrderStatesToLegenda = $this->ActionsDesOrder->getDesOrderStatesToLegenda($this->user, $group_id);
		$this->set('desOrderStatesToLegenda', $desOrderStatesToLegenda);				
	}
	
	/*
	 *  estraggo tutti gli ordini delle organization del DES 
	 *  con 'DATE(DesOrder.data_fine_max) < CURDATE() - INTERVAL ' . Configure::read('GGDesOrdersOld') . ' DAY'));
	 */
    public function admin_index_history() {
   	
   		$debug = false;
		
		$ACLsuppliersIdsDes = $this->user->get('ACLsuppliersIdsDes');
		if(empty($ACLsuppliersIdsDes)) 
			$ACLsuppliersIdsDes = 0;
				
		$options = [];
		$options['recursive'] = -1;
 		$options['conditions'] = ['DesOrder.des_id' => $this->user->des_id, 
								   'DATE(DesOrder.data_fine_max) < CURDATE() - INTERVAL ' . Configure::read('GGDesOrdersOld') . ' DAY'];
 		if(!$this->isSuperReferenteDes())
 			$options['conditions'] += ['DesOrder.des_supplier_id IN ('.$ACLsuppliersIdsDes.')'];
		$options['order'] = ['DesOrder.data_fine_max desc', 'DesOrder.id'];
		$results = $this->DesOrder->find('all', $options);
		
		$newResults = [];
		foreach($results as $numResult => $result) {
			$newResults[$numResult] = $this->DesOrder->getDesOrder($this->user, $result['DesOrder']['id']);
			
			/*
			 * per action Edit, Delete
			 */
			$isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $result['DesOrder']['id']);		
			$newResults[$numResult]['DesOrder']['isTitolareDesSupplier'] = $isTitolareDesSupplier;
		}

		self::d($newResults, $debug);		
		
		$this->set('results', $newResults);
		
		$group_id = Configure::read('group_id_titolare_des_supplier');
		$desOrderStatesToLegenda = $this->ActionsDesOrder->getDesOrderStatesToLegenda($this->user, $group_id);
		$this->set('desOrderStatesToLegenda', $desOrderStatesToLegenda);				
	}
		
	public function admin_add() {		

		$debug = false;
		
		if(!$this->isTitolareDesSupplier()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}		

		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			$data_fine_max = $this->request->data['DesOrder']['data_fine_max'];
			$data_fine_max_db = $this->request->data['DesOrder']['data_fine_max_db'];
			
			if(isset($this->request->data['DesOrder']['hasTrasport']))
				$hasTrasport = $this->request->data['DesOrder']['hasTrasport'];
			else
				$hasTrasport = 'N';

			if(isset($this->request->data['DesOrder']['hasCostMore']))
				$hasCostMore = $this->request->data['DesOrder']['hasCostMore'];
			else
				$hasCostMore = 'N';

			if(isset($this->request->data['DesOrder']['hasCostLess']))
				$hasCostLess = $this->request->data['DesOrder']['hasCostLess'];
			else
				$hasCostLess = 'N';
		}
		else {
			$data_fine_max = '';
			$data_fine_max_db = '';			
			$hasTrasport = 'N';
			$hasCostMore = 'N';
			$hasCostLess = 'N';
		}
		
		$this->set('orario_da',$orario_da);
		$this->set('orario_a', $orario_a);
		$this->set('data', $data);
		$this->set('data_db', $data_db);
		$this->set('nota_evidenzaDefault', $nota_evidenza);
		
		$this->set('data_fine_max', $data_fine_max);
		$this->set('data_fine_max_db', $data_fine_max_db);
		$this->set('hasTrasportDefault', $hasTrasport);
		$this->set('hasCostMoreDefault', $hasCostMore);
		$this->set('hasCostLessDefault', $hasCostLess);
				
		if ($this->request->is('post') || $this->request->is('put')) {
			$nota_evidenza = $this->request->data['DesOrder']['nota_evidenza'];
		}
		else {
			$nota_evidenza = '';
		}
		$this->set('nota_evidenzaDefault', $nota_evidenza);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['DesOrder']['des_id'] = $this->user->des_id;
			$this->request->data['DesOrder']['state_code'] = 'OPEN';
			$this->request->data['DesOrder']['data_fine_max'] = $this->request->data['DesOrder']['data_fine_max_db'];
			
			self::d(["DesOrder::admid_add() this->request->data", $this->request->data], $debug);		
				
			$this->DesOrder->create();
			if ($this->DesOrder->save($this->request->data)) {
				
					/*
					 * invio mail ai referenti di tutti i DES
					 * escludo i Configure::read('group_id_manager_des')
					 */
					$msg = "";
					if($this->request->data['DesOrder']['sendMail']=='Y' && !empty($this->request->data['DesOrder']['sendMailTarget_hidden'])) {
						
						
						/*
						 * gestione ruoli per destinatari MAIL
						 *
						$roles = [Configure::read('group_id_super_referent_des'),
								  Configure::read('group_id_referent_des'),
								  Configure::read('group_id_titolare_des_supplier'),
								  Configure::read('group_id_des_supplier_all_gas')
						];
						*/
						$arr_users_groups = explode(',', $this->request->data['DesOrder']['sendMailTarget_hidden']); 
						
						$roles = [];
						foreach($arr_users_groups as $numResult => $arr_users_group)
							$roles[$numResult] = $arr_users_group;						

						/*
						 * dati produttore
						 */
						App::import('Model', 'DesSupplier'); 
						$DesSupplier = new DesSupplier;
						$DesSupplier->unbindModel(array('belongsTo' => array('De', 'OwnOrganization')));
						
						$options = [];
						$options['conditions'] = array('DesSupplier.id' => $this->request->data['DesOrder']['des_supplier_id']);
						$options['recursive'] = 0;
						$desSupplierResults = $DesSupplier->find('first', $options);			
						self::d(["DesOrder::admid_add() - dati produttore", $desSupplierResults], $debug);	
					
						App::import('Model', 'DesSuppliersReferent');

						App::import('Model', 'DesOrganization');
						$DesOrganization = new DesOrganization;
					
						$DesOrganization->unbindModel(array('belongsTo' => array('De', 'Organization')));

						$options = [];
						$options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id,
													   // escludo il proprio 'DesOrganization.organization_id != ' => $this->user->organization['Organization']['id']
												];
						$options['recursive'] = 0;
						$options['order_by'] = array('Organization.name');
						$desOrganizationsResults = $DesOrganization->find('all', $options);	
						self::d(["DesOrder::admid_add() - Elenco DesOrganizations", $options['conditions'], $desOrganizationsResults], $debug);		

						$userMailResults = [];
						/*
						 * per ogni GAS estraggo gli utenti
						 */			
						foreach($desOrganizationsResults as $numResult => $desOrganizationsResult) {
							$organization_id = $desOrganizationsResult['DesOrganization']['organization_id'];
							
							$DesSuppliersReferent = new DesSuppliersReferent;
							$userMailResults += $DesSuppliersReferent->getUsersRoles($this->user, $organization_id, $roles, $this->request->data['DesOrder']['des_supplier_id']);			
						}
						
						self::d($userMailResults, $debug);	
						
						App::import('Model', 'Mail');
						$Mail = new Mail;
						
						$Email = $Mail->getMailSystem($this->user);

						$subject_mail = "Creato nuovo Ordine Condiviso";
						$body_mail  = "E' stato creato un nuovo ordine condiviso per il produttore <b>".$desSupplierResults['Supplier']['name']."</b>";
						if(!empty($desSupplierResults['Supplier']['descrizione']))
							$body_mail .= " - ".$desSupplierResults['Supplier']['descrizione']."<br />";				
						$body_mail .= " da parte del titolare <b>".$this->user->name."</b> - <a href=mailto:".$this->user->email.">".$this->user->email."</a> del GAS ".$this->user->organization['Organization']['name']."<br />";
						$body_mail .= "Consegna: ".$this->request->data['DesOrder']['luogo']."<br />";
						$body_mail .= "L'ordine del tuo GAS da associare non potrà terminare oltre il ".CakeTime::format($this->request->data['DesOrder']['data_fine_max_db'], "%A %e %B %Y")."<br />";
						if(!empty($this->request->data['DesOrder']['nota']))
							$body_mail .= "<b>Nota</b>: ".$this->request->data['DesOrder']['nota']."<br />";
										
						$Email->subject($subject_mail);
						$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);
							
						$str_log = "";
						foreach ($userMailResults as $userResult)  {
							$name = $userResult['User']['name'];
							$mail = $userResult['User']['email'];
								
							if(!empty($mail)) {
								$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
								$Email->to($mail);
								if(!Configure::read('mail.send')) $Email->transport('Debug');
								
								
								if($debug) {
									echo "<br />mail to ".$mail." - body_mail ".$body_mail;
								}
								
								$Mail->send($Email, $mail, $body_mail, $debug);	
							
								/*
								 * log dell'invio mail
								 */
								$str_log = 'GAS '.$userResult['User']['organization_id'].' '.$name.' '.$mail;
								$msg .= $name.' '.$mail.'<br />';
								/* CakeLog::write('debug', $str_log, array('desorder')); */
								
							} // end if(!empty($mail))
						} // end foreach ($userResults as $userResult)

						
					} // end if($this->request->data['DesOrder']['sendMail']=='Y')
					
				if(empty($msg))
					$msg = __('The DesOrder has been saved');
				else	
					$msg = __('The DesOrder has been saved').'<br /><br />Inviata la mail a<br />'.$msg;
				$this->Session->setFlash($msg);
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesOrders&action=index';
				if(!$debug) $this->myRedirect($url);
			} 
			else 
				$this->Session->setFlash(__('The DesOrder could not be saved. Please, try again.'));
		} // END POST
		
		$nota_evidenza = ClassRegistry::init('DesOrder')->enumOptions('nota_evidenza');
		unset($nota_evidenza['NO']);
		$this->set(compact('nota_evidenza'));	

		 /*
		  * sendMailTarget
		  */
		$sendMailTarget = array($this->userGroups[Configure::read('group_id_super_referent_des')]['id'] => $this->userGroups[Configure::read('group_id_super_referent_des')]['name'],
								$this->userGroups[Configure::read('group_id_referent_des')]['id'] => $this->userGroups[Configure::read('group_id_referent_des')]['name'],
								$this->userGroups[Configure::read('group_id_titolare_des_supplier')]['id'] => $this->userGroups[Configure::read('group_id_titolare_des_supplier')]['name'],
								$this->userGroups[Configure::read('group_id_des_supplier_all_gas')]['id'] => $this->userGroups[Configure::read('group_id_des_supplier_all_gas')]['name']);
		$sendMailTargetDefault = array(0 => array('id' => $this->userGroups[Configure::read('group_id_referent_des')]['id']));
		$this->set(compact('sendMailTarget', 'sendMailTargetDefault'));
		
		$sendMail = array('N' => 'No', 'Y' => 'Si');
		$hasTrasport = ClassRegistry::init('DesOrder')->enumOptions('hasTrasport');
		$hasCostMore = ClassRegistry::init('DesOrder')->enumOptions('hasCostMore');
		$hasCostLess = ClassRegistry::init('DesOrder')->enumOptions('hasCostLess');
		$this->set(compact('hasTrasport','hasCostMore','hasCostLess','sendMail'));	
		
		/*
		 * legenda
		 */
		foreach ($this->userGroups as $group_id => $data) {
			if($data['type']!='DES')	
				unset($this->userGroups[$group_id]);
		}
		$this->set('usersGroups', $this->userGroups);	
		
		/*
		 * estrae tutti i produttori del titolare del produttore
		 *	 solo lui puo' aprire un DesOder
		 */
		App::import('Model', 'DesSuppliersReferent');
		$DesSuppliersReferent = new DesSuppliersReferent;

		$ACLDesSuppliersResults = $DesSuppliersReferent->getDesSuppliersTitolare($this->user); 
		$newResults = [];
		if(!empty($ACLDesSuppliersResults)) {
			foreach($ACLDesSuppliersResults as $ACLDesSuppliersResult) 
				$newResults[$ACLDesSuppliersResult['DesSupplier']['id']] = $ACLDesSuppliersResult['Supplier']['name']; 
		}
		$this->set('ACLDesSuppliersResults',$newResults);
	}	
	
	public function admin_edit($des_order_id=0) {		

		$debug = false;
		$msg = "";
		
		if(!$this->isTitolareDesSupplier()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}		

		/*
		 * dopo il submit li ho in $this->request->data 
		 */
		if(empty($des_order_id)) {
			$des_order_id = $this->request->data['DesOrder']['des_order_id'];
		}

		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			$data_fine_max = $this->request->data['DesOrder']['data_fine_max'];
			$data_fine_max_db = $this->request->data['DesOrder']['data_fine_max_db'];
			
			if(isset($this->request->data['DesOrder']['hasTrasport']))
				$hasTrasport = $this->request->data['DesOrder']['hasTrasport'];
			else
				$hasTrasport = 'N';

			if(isset($this->request->data['DesOrder']['hasCostMore']))
				$hasCostMore = $this->request->data['DesOrder']['hasCostMore'];
			else
				$hasCostMore = 'N';

			if(isset($this->request->data['DesOrder']['hasCostLess']))
				$hasCostLess = $this->request->data['DesOrder']['hasCostLess'];
			else
				$hasCostLess = 'N';
		}
		else {
			$data_fine_max = '';
			$data_fine_max_db = '';			
			$hasTrasport = 'N';
			$hasCostMore = 'N';
			$hasCostLess = 'N';
		}

		$this->set('des_order_id', $des_order_id);
		$this->set('data_fine_max', $data_fine_max);
		$this->set('data_fine_max_db', $data_fine_max_db);
		$this->set('hasTrasportDefault', $hasTrasport);
		$this->set('hasCostMoreDefault', $hasCostMore);
		$this->set('hasCostLessDefault', $hasCostLess);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			$nota_evidenza = $this->request->data['DesOrder']['nota_evidenza'];
		}
		else {
			$nota_evidenza = '';
		}
		$this->set('nota_evidenzaDefault', $nota_evidenza);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['DesOrder']['des_id'] = $this->user->des_id;
			$this->request->data['DesOrder']['data_fine_max'] = $this->request->data['DesOrder']['data_fine_max_db'];
			
			/*
			 * aggiorno i Order.data_fine con DesOrder.DATA_FINE_MAX
			 */
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();
			$DesOrdersOrganization->unbindModel(array('belongsTo' => array('De','DesOrder')));
			
			$options = [];
			$options['conditions'] = ['DesOrdersOrganization.des_order_id' => $des_order_id,
									  'DesOrdersOrganization.des_id' => $this->user->des_id];
			$options['recursive'] = 1;
			$options['fields'] = array('Organization.id','Organization.name',
										'Order.organization_id', 'Order.id', 'Order.data_inizio', 'Order.data_fine');
			$desOrdersOrganizationsResults = $DesOrdersOrganization->find('all', $options);
			self::d(["DesOrders::edit ORDINI di tutti i GAS associati", $options['conditions'], $desOrdersOrganizationsResults], $debug);
			
			foreach($desOrdersOrganizationsResults as $desOrdersOrganizationsResult) {
				
				$organization_id = $desOrdersOrganizationsResult['Order']['organization_id'];
				$order_id = $desOrdersOrganizationsResult['Order']['id'];
				$data_inizio = $desOrdersOrganizationsResult['Order']['data_inizio'];
				$data_fine = $desOrdersOrganizationsResult['Order']['data_fine'];
				
				if($debug) echo '<br />OrderID '.$order_id.' - data_inizio '.$data_inizio.' - data_fine '.$data_fine.' - data_fine_max '.$this->request->data['DesOrder']['data_fine_max'];

				$order_data_fine_maggiore = Validation::comparison($data_fine, '>', $this->request->data['DesOrder']['data_fine_max']);
					
				if($order_data_fine_maggiore) {
					
					$order_data_inizio_maggiore = Validation::comparison($data_inizio, '>', $this->request->data['DesOrder']['data_fine_max']);
					
					$sql = "UPDATE
								".Configure::read('DB.prefix')."orders
							SET ";
					if($order_data_inizio_maggiore)
						$sql .= " data_inizio = '".$this->request->data['DesOrder']['data_fine_max']."', ";
					$sql .= " data_fine = '".$this->request->data['DesOrder']['data_fine_max']."' ";
					$sql .= " WHERE
								organization_id = ".$organization_id."
								AND id = ".$order_id;
					self::d($sql, $debug);
					try {
						$this->DesOrder->query($sql);
					}
					catch (Exception $e) {
						CakeLog::write('error',$sql);
						CakeLog::write('error',$e);
					}
				
					$msg .= "Per il G.A.S. ".$desOrdersOrganizationsResult['Organization']['name'].' &egrave; stata reimpostata la <b>data di chiusura dell\'ordine</b> perch&egrave; era superiore della <b>data di chiusura dell\'ordine condiviso</b>.<br />'; 
				}
				
			} // loop desOrdersOrganizations

			$this->DesOrder->create();
			if ($this->DesOrder->save($this->request->data)) {
				$msg .= __('The DesOrder has been saved');
				$this->Session->setFlash($msg);
				
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesOrders&action=index';
				$this->myRedirect($url);
			} 
			else {
				$msg .= __('The DesOrder could not be saved. Please, try again.');
				$this->Session->setFlash($msg);
								
			} 
		} // post
		
		/*
		 * dati DesOrder
		 */		
		$options = [];
		$options['conditions'] = array('DesOrder.id' => $des_order_id,
									   'DesOrder.des_id' => $this->user->des_id);
		$options['recursive'] = 0;
		$this->request->data = $this->DesOrder->find('first', $options);

		/*
		 * estraggo i PRODUTTORI
		 */
		App::import('Model', 'DesSupplier'); 
		$DesSupplier = new DesSupplier;
		$DesSupplier->unbindModel(array('belongsTo' => array('De', 'OwnOrganization')));
		
		$options = [];
		$options['conditions'] = array('DesSupplier.id' => $this->request->data['DesSupplier']['id']);
		$options['recursive'] = 0;
		$desSupplierResults = $DesSupplier->find('first', $options);			
		
		$this->request->data['Supplier'] = $desSupplierResults['Supplier'];
		
		$nota_evidenza = ClassRegistry::init('DesOrder')->enumOptions('nota_evidenza');
		unset($nota_evidenza['NO']);
		$this->set(compact('nota_evidenza'));	
		 
		$hasTrasport = ClassRegistry::init('DesOrder')->enumOptions('hasTrasport');
		$hasCostMore = ClassRegistry::init('DesOrder')->enumOptions('hasCostMore');
		$hasCostLess = ClassRegistry::init('DesOrder')->enumOptions('hasCostLess');
		$this->set(compact('hasTrasport','hasCostMore','hasCostLess'));		
	}
	
   public function admin_delete($des_order_id=0) {
	   
		if(empty($des_order_id)) 
			$des_order_id = $this->request->data['DesOrder']['id'];
		
		   
		if (empty($des_order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}	   

		if(!$this->isTitolareDesSupplier()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$this->DesOrder->id = $des_order_id;
		if (!$this->DesOrder->exists($this->DesOrder->id)) {
			throw new NotFoundException(__('Invalid DesOrder'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->DesOrder->delete()) {
				$this->Session->setFlash(__('Delete DesOrder'));
			} else {
				$this->Session->setFlash(__('The DesOrder could not be deleted. Please, try again.'));
			}

			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=DesOrders&action=index';
			$this->myRedirect($url);
		}
		
		/*
		 * dati DesOrder
		 */		
		$options = [];
		$options['conditions'] = ['DesOrder.id' => $des_order_id,
								   'DesOrder.des_id' => $this->user->des_id];
		$options['recursive'] = 0;
		$this->request->data = $this->DesOrder->find('first', $options);
		
		/*
		 * estraggo i PRODUTTORI
		 */
		App::import('Model', 'DesSupplier'); 
		$DesSupplier = new DesSupplier;
		$DesSupplier->unbindModel(['belongsTo' => ['De', 'OwnOrganization']]);
		
		$options = [];
		$options['conditions'] = ['DesSupplier.id' => $this->request->data['DesSupplier']['id']];
		$options['recursive'] = 0;
		$desSupplierResults = $DesSupplier->find('first', $options);			
		
		$this->request->data['Supplier'] = $desSupplierResults['Supplier'];
		
		$this->set(compact('des_order_id'));
   }
   
	/*
	 * dal link del menu' passo des_order_id
	 *	redirect a ORder::add con altri parametri (supplier_organization_id)
	 */
	public function admin_prepare_order_add($des_order_id) {

		/*
		 * recupero $supplier_organization_id
		 */
		App::import('Model', 'DesOrder');
		$DesOrder = new DesOrder();
		$DesOrder->unbindModel(array('belongsTo' => array('De', 'DesOrder')));
		
		$options = [];
		$options['conditions'] = ['DesOrder.des_id' => $this->user->des_id,
									   'DesOrder.id' => $des_order_id];
		$options['fields'] = ['DesSupplier.supplier_id'];
		$options['recursive'] = 1;
		$results = $DesOrder->find('first', $options);
				
		$supplier_id = $results['DesSupplier']['supplier_id'];
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization();

		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'], 'SuppliersOrganization.supplier_id' => $supplier_id];
		$options['fields'] = ['SuppliersOrganization.id', 'SuppliersOrganization.stato'];
		$options['recursive'] = -1;
		
		$results = $SuppliersOrganization->find('first', $options);
		 
		if(empty($results)) {
			$this->Session->setFlash("Il produttore non compare nella lista dei produttori associati al tuo GAS!");

			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
			$url .= '&controller=DesOrdersOrganizations&action=index&id='.$des_order_id;				
		}
		else 
		if($results['SuppliersOrganization']['stato']=='N') {
			$this->Session->setFlash("Il produttore ha lo stato disabilitato!");

			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
			$url .= '&controller=DesOrdersOrganizations&action=index&id='.$des_order_id;			
		}
		else {
			$supplier_organization_id = $results['SuppliersOrganization']['id'];
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
			$url .= '&controller=Orders&action=add';
			$url .= '&delivery_id=0&order_id=0';
			$url .= '&supplier_organization_id='.$supplier_organization_id.'&des_order_id='.$des_order_id;			
		}
	
		// echo '<br />'.$url;

		$this->myRedirect($url);
	}
	
	public function admin_prepare_order_edit($des_order_id) {
		$url = $this->_prepare_order($this->user, $des_order_id, $this->action);
		$this->myRedirect($url);
	}
	
	public function admin_prepare_order_home($des_order_id) {
		$url = $this->_prepare_order($this->user, $des_order_id, $this->action);
		$this->myRedirect($url);
	}

	public function admin_prepare_order_print($des_order_id) {	
		$url = $this->_prepare_order($this->user, $des_order_id, $this->action);
		$this->myRedirect($url);
	}

	public function admin_prepare_articles_orders_index($des_order_id) {
		$url = $this->_prepare_order($this->user, $des_order_id, $this->action);
		$this->myRedirect($url);
	}

	public function admin_prepare_articles_orders_index_only_read_des($des_order_id) {
		$url = $this->_prepare_order($this->user, $des_order_id, $this->action);
		$this->myRedirect($url);	
	}
	
	/*
	 * dal link passo des_order_id
	 *	redirect a ArticlesOrder::index_only_read_des con altri parametri (order_id)
	 */	
	public function admin_prepare_print_all_gas($des_order_id, $organization_id) {
	
		/*
		 * recupero $delivery_id, $order_id
		 */
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();
		
		$options = [];
		$options['conditions'] = array('DesOrdersOrganization.des_id' => $this->user->des_id,
									   'DesOrdersOrganization.des_order_id' => $des_order_id,
									   'DesOrdersOrganization.organization_id' => $organization_id);
		$options['fields'] = array('DesOrdersOrganization.order_id'); 
		$options['recursive'] = -1;
		$results = $DesOrdersOrganization->find('first', $options);

		$order_id = $results['DesOrdersOrganization']['order_id']; 
		if(empty($order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		App::import('Model', 'Order');
		$Order = new Order();

		$options = [];
		$options['conditions'] = array('Order.id' => $order_id,
									   'Order.organization_id' => $organization_id);
		$options['fields'] = array('Order.organization_id', 'Order.delivery_id'); 
		$options['recursive'] = -1;
		$results = $Order->find('first', $options);

		$delivery_id = $results['Order']['delivery_id'];
				
		$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
		$url .= '&controller=Docs&action=referentDesAllGasDocsExport';
		$url .= '&des_order_id='.$des_order_id.'&organization_id='.$organization_id.'&delivery_id='.$delivery_id.'&order_id='.$order_id;
		
		// echo $url; exit;
		
		$this->myRedirect($url);
	}

	private function _prepare_order($user, $des_order_id, $action) {
		
		$url = "";
		
		/*
		 * recupero $order_id
		 */
		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();
		
		$options = [];
		$options['conditions'] = ['DesOrdersOrganization.des_id' => $user->des_id,
								   'DesOrdersOrganization.des_order_id' => $des_order_id,
								   'DesOrdersOrganization.organization_id' => $user->organization['Organization']['id']];
		$options['fields'] = ['DesOrdersOrganization.order_id']; 
		$options['recursive'] = -1;
		$results = $DesOrdersOrganization->find('first', $options);
		
		$order_id = $results['DesOrdersOrganization']['order_id']; 
		if(empty($order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			return Configure::read('routes_msg_exclamation');
		}
		

		/*
		 * CTRL se esiste come ORDER, se non trovo l'ordine potrebbe essere stato cancellato
		 */
		App::import('Model', 'Order');
		$Order = new Order();
		
		$options = [];
		$options['conditions'] = ['Order.id' => $order_id,
							      'Order.organization_id' => $user->organization['Organization']['id']];
		$options['recursive'] = -1;
		$orderResults = $Order->find('first', $options);
		if(empty($orderResults)) {
			$this->Session->setFlash(__('msg_des_order_not_found'));
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
			$url .= '&controller=DesOrdersOrganizations&action=index';
			$url .= '&id='.$des_order_id;
			return $url;
		}
			
		/* ctrl ACL */
	   	if($this->isSuperReferente()) {
	   				
		}
		else {
			if(empty($order_id) || !$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($user, $order_id)) { 
				$this->Session->setFlash(__('msg_des_order_not_referent'));
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
				$url .= '&controller=DesOrdersOrganizations&action=index';
				$url .= '&id='.$des_order_id;
				return $url;
			}
		}
   		/* ctrl ACL */ 
		
		switch($action) {
			case "admin_prepare_order_print":
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
				$url .= '&controller=Docs&action=referentDocsExport';
				$url .= '&delivery_id='.$orderResults['Order']['delivery_id'].'&order_id='.$order_id.'&des_order_id='.$des_order_id;			
			break;
			case "admin_prepare_order_home":
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
				$url .= '&controller=Orders&action=home';
				$url .= '&order_id='.$order_id.'&des_order_id='.$des_order_id;		
			break;
			case "admin_prepare_order_edit":
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
				$url .= '&controller=Orders&action=edit';
				$url .= '&order_id='.$order_id.'&des_order_id='.$des_order_id;
			break;
			case "admin_prepare_articles_orders_index":
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
				$url .= '&controller=Orders&action=edit';
				$url .= '&order_id='.$order_id.'&des_order_id='.$des_order_id;
			break;
			case "admin_prepare_articles_orders_index":
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
				$url .= '&controller=ArticlesOrders&action=index';
				$url .= '&order_id='.$order_id;
			break;
			case "admin_prepare_articles_orders_index_only_read_des":
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake';
				$url .= '&controller=ArticlesOrders&action=index';
				$url .= '&order_id='.$order_id;
			break;
			default:
				$url = Configure::read('routes_msg_exclamation');
			break;			
		}
		
		return $url;
	}
		
	/*  creo sotto menu degli ordini profilato
	 * 
	 *  position_img, le backgroung-img e' a Dx o Sn
	 */
	public function admin_sotto_menu($des_order_id=0, $position_img) {

		$debug = false;

		$this->ctrlHttpReferer();
				
		$options = [];
		$options['conditions'] = ['DesOrder.des_id' => $this->user->des_id,
								  'DesOrder.id' => $des_order_id];
		$options['recursive'] = 0;
		$results = $this->DesOrder->find('first', $options);
		
		self::d([$options, $results], $debug);
				
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
				
		$this->set('results', $results);
		
		$group_id = $this->ActionsDesOrder->getGroupIdToReferente($this->user);
		$desOrderActions = $this->ActionsDesOrder->getDesOrderActionsToMenu($this->user, $group_id, $results['DesOrder']['id'], $debug);
		$this->set('desOrderActions', $desOrderActions);
		
		$desOrderStates = $this->ActionsDesOrder->getDesOrderStatesToLegenda($this->user, $group_id, $debug);
		$this->set('desOrderStates', $desOrderStates);
		
		/*
		 * $pageCurrent = array('controller' => '', 'action' => '');
		 * mi serve per non rendere cliccabile il link corrente nel menu laterale
		*/
		$pageCurrent = $this->getToUrlControllerAction($_SERVER['HTTP_REFERER']);
		$this->set('pageCurrent', $pageCurrent);
		$this->set('position_img', $position_img);
		
		$this->layout = 'ajax';
	}
	
	/*
	 * DesOrdes.state_code to BEFORE-TRASMISSION => POST-TRASMISSION
	 */
	public function admin_des_orders_state_in_POST_TRASMISSION($des_order_id) {

		$state_code_next = 'POST-TRASMISSION';

		/*
		 * ctrl ACL
		 */
		$isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);
		if(!$isTitolareDesSupplier) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));				
		}
		
		/*
		 * ctrl eventuali occorrenze di DesSummaryOrder, se non ci sono lo popolo
		*/
	    App::import('Model', 'SummaryDesOrder');
	    $SummaryDesOrder = new SummaryDesOrder;		
		$results = $SummaryDesOrder->select_to_des_order($this->user, $des_order_id);
		if(empty($results))
			$SummaryDesOrder->populate_to_des_order($this->user, $des_order_id);
		
		try {
			$sql ="UPDATE `".Configure::read('DB.prefix')."des_orders`
				   SET
						state_code = '$state_code_next',
						modified = '".date('Y-m-d H:i:s')."'
				   WHERE
			   			des_id = ".(int)$this->user->des_id."
			   			and id = ".$des_order_id;
			$this->DesOrder->query($sql);			
		}
		catch (Exception $e) {
			echo '<br />DesOrder::des_orders_state_in_POST_TRASMISSION()<br />'.$e;
		}
		
		$this->Session->setFlash(__('DesOrder State in POST-TRASMISSION'));
		$this->myRedirect(array('controller' => 'DesOrdersOrganizations', 'action' => 'index', 'id' => $des_order_id));
	}		
	
	/*
	 * DesOrdes.state_code to POST-TRASMISSION => BEFORE-TRASMISSION
	 */
	public function admin_des_orders_state_in_BEFORE_TRASMISSION($des_order_id) {

		$state_code_next = 'BEFORE-TRASMISSION';
		
		/*
		 * ctrl ACL
		 */
		$isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);
		if(!$isTitolareDesSupplier) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));				
		}
		
		try {
			$sql ="UPDATE `".Configure::read('DB.prefix')."des_orders`
				   SET
						state_code = '$state_code_next',
						modified = '".date('Y-m-d H:i:s')."'
				   WHERE
			   			des_id = ".(int)$this->user->des_id."
			   			and id = ".$des_order_id;
			$this->DesOrder->query($sql);			
		}
		catch (Exception $e) {
			echo '<br />DesOrder::des_orders_state_in_BEFORE_TRASMISSION()<br />'.$e;
		}
		
		$this->Session->setFlash(__('DesOrder State in BEFORE-TRASMISSION'));
		$this->myRedirect(array('controller' => 'DesOrdersOrganizations', 'action' => 'index', 'id' => $des_order_id));
	}	
	
	
	/*
	 * DesOrdes.state_code to POST-TRASMISSION => REFERENT-WORKING
	 */
	public function admin_des_orders_state_in_REFERENT_WORKING($des_order_id) {

		$state_code_next = 'REFERENT-WORKING';
		
		/*
		 * ctrl ACL
		 */
		$isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($this->user, $des_order_id);
		if(!$isTitolareDesSupplier) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));				
		}
		
		try {
			$sql ="UPDATE `".Configure::read('DB.prefix')."des_orders`
				   SET
						state_code = '$state_code_next',
						modified = '".date('Y-m-d H:i:s')."'
				   WHERE
			   			des_id = ".(int)$this->user->des_id."
			   			and id = ".$des_order_id;
			$this->DesOrder->query($sql);			
		}
		catch (Exception $e) {
			echo '<br />DesOrder::des_orders_state_in_REFERENT-WORKING()<br />'.$e;
		}
		
		$this->Session->setFlash(__('DesOrder State in REFERENT-WORKING'));
		$this->myRedirect(array('controller' => 'DesOrdersOrganizations', 'action' => 'index', 'id' => $des_order_id));
	}		
}