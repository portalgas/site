<?php
App::uses('AppController', 'Controller');

class LoopsDeliveriesController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		/* ctrl ACL */
		if(!$this->isRoot() && !$this->isManagerDelivery()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		/* ctrl ACL */
	}
	
	public function admin_index() {
		
		$options =  [];
		$options['conditions'] = array('LoopsDelivery.organization_id' => (int)$this->user->organization['Organization']['id']);
		$options['order'] = array('LoopsDelivery.data_master ASC');
		$options['recursive'] = 1;
		$results = $this->LoopsDelivery->find('all', $options);
		
		foreach ($results as $numResult => $result) {
			$rules = json_decode($result['LoopsDelivery']['rules'], true);
		
			$results[$numResult]['LoopsDelivery'] += $rules;
			 
			unset($results[$numResult]['LoopsDelivery']['rules']);
		}
		
		$this->set('results', $results);
		
		$this->set('isRoot', $this->isRoot());
	}
		
	public function admin_add() {
		
		$debug = false;
		
		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			$orario_da = $this->request->data['LoopsDelivery']['orario_da']['hour'].':'.$this->request->data['LoopsDelivery']['orario_da']['min'];
			$orario_a = $this->request->data['LoopsDelivery']['orario_a']['hour'].':'.$this->request->data['LoopsDelivery']['orario_a']['min'];
			$data_master = $this->request->data['LoopsDelivery']['data_master'];	
			$data_master_db = $this->request->data['LoopsDelivery']['data_master_db'];	
			$nota_evidenza = $this->request->data['LoopsDelivery']['nota_evidenza'];
			$flag_send_mailDefault = $this->request->data['LoopsDelivery']['flag_send_mail'];
		}
		else {
			$orario_da = '10:30';
			$orario_a  = '11:30';
			$data_master = '';
			$data_master_db = '';
			$nota_evidenza = '';
			$flag_send_mailDefault = 'N';
		
		}
		$this->set('orario_da',$orario_da);
		$this->set('orario_a', $orario_a);
		$this->set('data_master', $data_master);
		$this->set('data_master_db', $data_master_db);
		$this->set('nota_evidenzaDefault', $nota_evidenza);
		$this->set('flag_send_mailDefault', $flag_send_mailDefault);
		
		if ($this->request->is('post')) {
			
			self::d($this->request->data,$debug);
			
			$data_copy = $this->LoopsDelivery->getDataCopy($data_master_db, $this->request->data, $debug);
				
			if(empty($data_copy)) {
					$this->Session->setFlash(__('I dati per creare la consegna ricorsiva non sono corretti'));
			}
			else {	
				/*
				 * faccio PREVIEW della consegna ricorsiva
				 */
				if($this->request->data['LoopsDelivery']['action_post']=='action_preview') {
					/*
					 * variabili per calendar
					 */
					$data_master_label = $data_master_db;
					$data_copy_label = $data_copy;
					
					$data_master = explode("-", $data_master_db);
					$data_copy = explode("-", $data_copy);
						
					if($data_master[1][0]=='0') $data_master_mm = $data_master[1][1]; // elimino l'eventuale 0 iniziale
					else $data_master_mm = $data_master[1];
					$data_master_mm = ($data_master_mm -1);
						
					if($data_master[2][0]=='0') $data_master_gg = $data_master[2][1];
					else $data_master_gg = $data_master[2];
					$data_master_gg = ($data_master_gg);
						
					
					if($data_copy[1][0]=='0') $data_copy_mm = $data_copy[1][1];
					else $data_copy_mm = $data_copy[1];
					$data_copy_mm = ($data_copy_mm -1);
						
					if($data_copy[2][0]=='0') $data_copy_gg = $data_copy[2][1];
					else $data_copy_gg = $data_copy[2];
					$data_copy_gg = ($data_copy_gg);
						
					$data_master_value .= $data_master[0].",".$data_master_mm.",".$data_master_gg;
					$data_copy_value .= $data_copy[0].",".$data_copy_mm.",".$data_copy_gg;
		
					$this->set('data_master', $data_master_label);
					$this->set('data_copy', $data_copy_label);
					
					$this->set('data_master_value', $data_master_value);
					$this->set('data_copy_value', $data_copy_value);			
				}
				else
				/*
				 * Salvo la nuova consegna
				 */
				if($this->request->data['LoopsDelivery']['action_post']=='action_submit') {
	
						$type = $this->request->data['LoopsDelivery']['type'];  /* WEEK MONTH */
						 
						$rules = [];
						$rules += array('type' => $type);
						
						switch ($type) {
							case 'WEEK':
								$rules += array('week_every_week' => $this->request->data['LoopsDelivery']['week_every_week']);						
							break;
							case "MONTH":
						
								$rules += array('type_month' => $this->request->data['LoopsDelivery']['type_month']);
								
								switch ($this->request->data['LoopsDelivery']['type_month']) {
									case 'MONTH1':
										if(!empty($this->request->data['LoopsDelivery']['month1_day']))
											$rules += array('month1_day' => $this->request->data['LoopsDelivery']['month1_day']);
										if(!empty($this->request->data['LoopsDelivery']['month1_every_month']))
											$rules += array('month1_every_month' => $this->request->data['LoopsDelivery']['month1_every_month']);	
									break;
									case 'MONTH2':
										if(!empty($this->request->data['LoopsDelivery']['month2_every_type'])) /* FIRST... LAST */
											$rules += array('month2_every_type' => $this->request->data['LoopsDelivery']['month2_every_type']);
										if(!empty($this->request->data['LoopsDelivery']['month2_day_week'])) /* MON TUE WED... */
											$rules += array('month2_day_week' => $this->request->data['LoopsDelivery']['month2_day_week']);
										if(!empty($this->request->data['LoopsDelivery']['month2_every_month']))
											$rules += array('month2_every_month' => $this->request->data['LoopsDelivery']['month2_every_month']);								
									break;
								}
							break;
						}
												
						$data = [];
						$data['LoopsDelivery']['organization_id'] = $this->user->organization['Organization']['id'];
						$data['LoopsDelivery']['luogo'] = $this->request->data['LoopsDelivery']['luogo'];
						$data['LoopsDelivery']['orario_da'] = $this->request->data['LoopsDelivery']['orario_da'];
						$data['LoopsDelivery']['orario_a'] = $this->request->data['LoopsDelivery']['orario_a'];
						$data['LoopsDelivery']['nota'] = $this->request->data['LoopsDelivery']['nota'];
						$data['LoopsDelivery']['nota_evidenza'] = $this->request->data['LoopsDelivery']['nota_evidenza'];
						$data['LoopsDelivery']['user_id'] = $this->user->id;
						$data['LoopsDelivery']['data_master'] = $data_master_db;
						$data['LoopsDelivery']['data_master_reale'] = $data_master_db;
						$data['LoopsDelivery']['data_copy'] = $data_copy;
						$data['LoopsDelivery']['data_copy_reale'] = $data_copy;
						$data['LoopsDelivery']['type'] = $type;
						$data['LoopsDelivery']['rules'] = json_encode($rules);
						
						self::d($data,$debug);
						
						$this->LoopsDelivery->create();
						if ($this->LoopsDelivery->save($data)) {
							$this->Session->setFlash(__('The loops delivery has been saved'));
							$this->myRedirect(['action' => 'index']);
						} else {
							$this->Session->setFlash(__('The loops delivery could not be saved. Please, try again.'));
						}	
						
						/*
						 * creo l'evento su gcalendar solo se sono su portalgas.it (no test / next)
						* */
						if(Configure::read('App.root') == '/var/www/portalgas') {
							$utilsCrons = new UtilsCrons(new View(null));
							if(Configure::read('developer.mode')) echo "<pre>";
							$utilsCrons->gcalendarUsersDeliveryInsert($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
							if(Configure::read('developer.mode')) echo "</pre>";					
						}
					} 						
			} // end if(!empty($data_copy) 
		}  // end if ($this->request->is('post'))
		
		/*
		 *  gestisco i 2 tasti di submit, 
		 */
		if(!isset($this->request->data['LoopsDelivery']['action_post']))
			$action_submit = 'hidden'; /* e' la prima volta, solo tasto di preview */
		else 
			$action_submit = 'show';
		$this->set('action_submit', $action_submit);

		$nota_evidenza = ClassRegistry::init('LoopsDelivery')->enumOptions('nota_evidenza');
		$flag_send_mail = ClassRegistry::init('LoopsDelivery')->enumOptions('flag_send_mail');
		
		$this->set(compact('nota_evidenza', 'flag_send_mail'));
	}

	public function admin_edit($id = null) {
	
		$this->LoopsDelivery->id = $id;
		if (!$this->LoopsDelivery->exists($this->LoopsDelivery->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * setting fields
		*/
		if ($this->request->is(array('post', 'put'))) {
			$nota_evidenza = $this->request->data['LoopsDelivery']['nota_evidenza'];
		}
		else {
			$nota_evidenza = '';
		}
		$this->set('nota_evidenzaDefault', $nota_evidenza);
		
		if ($this->request->is(array('post', 'put'))) {
				
			$this->request->data['LoopsDelivery']['organization_id'] = $this->user->organization['Organization']['id'];
			$this->request->data['LoopsDelivery']['data_copy_reale'] = $this->request->data['LoopsDelivery']['data_copy_reale_db'];
				
			if ($this->LoopsDelivery->save($this->request->data)) {
				$this->Session->setFlash(__('The loops delivery has been saved'));
				$this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The loops delivery could not be saved. Please, try again.'));
			}
		} else {
			$options = [];
			$options['conditions'] = ['LoopsDelivery.organization_id' => $this->user->organization['Organization']['id'],
									        'LoopsDelivery.id' => $id];
			$this->request->data = $this->LoopsDelivery->find('first', $options);
		}
		

		$nota_evidenza = ClassRegistry::init('LoopsDelivery')->enumOptions('nota_evidenza');
		$flag_send_mail = ClassRegistry::init('LoopsDelivery')->enumOptions('flag_send_mail');

		$this->set(compact('nota_evidenza', 'flag_send_mail'));
	}

	public function admin_delete($id = null) {
		$this->LoopsDelivery->id = $id;
		if (!$this->LoopsDelivery->exists($this->LoopsDelivery->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		if ($this->LoopsDelivery->delete()) {
			$this->Session->setFlash(__('Delete LoopsDelivery'));
		} else {
			$this->Session->setFlash(__('LoopDelivery was not deleted'));
		}
		$this->myRedirect(['action' => 'index']);
	}
	
	public function admin_testing($id = null) {
	
		$debug = true;	

		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
	
		$options = [];
		$options['conditions'] = ['LoopsDelivery.organization_id' => $this->user->organization['Organization']['id'],
						          'LoopsDelivery.id' => $id];
		$loopsDeliveryResults = $this->LoopsDelivery->find('first', $options);
		self::d($loopsDeliveryResults, $debug);
		if (empty($loopsDeliveryResults)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$create = false; // in LoopsDeliveries::testing simulo
		$this->LoopsDelivery->creating($this->user, $loopsDeliveryResults, $create, $debug);
	}
}