<?php
App::uses('AppModel', 'Model');

class SummaryPayment extends AppModel {
  	
	/*
	 * condizione se un pagamento per un utente e' saldato
	*/
	public function isPaid($user, $results, $debug=false) {

		$request_payment_id = '';
		$user_label = '';
		
		if(isset($results['User']))
			$request_payment_id = $results['RequestPayment']['id'];
		if(isset($results['User'])) 		
			$user_label = '['.$results['User']['id'].'] '.$results['User']['name'];
		
		if(!isset($results['SummaryPayment']) && !isset($results['SummaryPayment']['stato']))
			return false;
		
		if($results['SummaryPayment']['stato'] != Configure::read('SOSPESO') && $results['SummaryPayment']['stato'] != Configure::read('PAGATO'))  {
			self::d('SummaryPayment::isPaid - requestPayment.id '.$request_payment_id.' SummaryPayment.stato '.$results['SummaryPayment']['stato'].' => Non saldato da gasista '.$user_label, $debug);
			return false;
		}
		else {
			self::d('SummaryPayment::isPaid - requestPayment.id '.$request_payment_id.' SummaryPayment.stato '.$results['SummaryPayment']['stato'].' => Saldato da gasista '.$user_label, $debug);
			return true;
		}
	}
	
	/*
	 *  memorizzo il nuovo SummaryPayment.stato
	 *		l'importo_pagato (anche 0,00 perche' puo' prendere tutto dalla cassa)
	 *      per ogni user aggiorno SummaryOrder.saldato_a = 'TESORIERE' cosi' l'ordine andra' allo stato successivo (dipende dal template) se tutti hanno saldato
	 */	
	public function paid($user, $data, $debug=false) {
		
		$this->set($data);
		if(!$this->validates()) {
			$errors = $this->validationErrors;
			self::l($errors, $debug);

			return false;
		}
		else {
			$this->create();
			self::d(['SummaryPayment::paid', $data], $debug);
			if (!$this->save($data))  {
				return false;
			}
		}
		
		$request_payment_id = $data['SummaryPayment']['request_payment_id'];
		$organization_id = $data['SummaryPayment']['organization_id'];		
		$user_id = $data['SummaryPayment']['user_id'];
		
		/*
		 * per ogni user aggiorno SummaryOrder.saldato_a = 'TESORIERE' cosi' l'ordine andra' allo stato successivo
		 * con il ctrl $SummaryOrderLifeCycle->isSummaryOrderAllSaldato in UtilsCron::ordersStatoElaborazione() con Order.state_code TO-PAYMENT
		 */
		try {
			 $sql = "UPDATE ".Configure::read('DB.prefix')."summary_orders s
					INNER JOIN ".Configure::read('DB.prefix')."request_payments_orders o 
					ON (s.order_id = o.order_id and o.organization_id = $organization_id and o.request_payment_id = $request_payment_id)
					SET s.saldato_a = 'TESORIERE', s.importo_pagato = s.importo  
					WHERE s.importo_pagato = '0.00' 
					and s.organization_id = $organization_id
					and s.user_id = $user_id";
				self::l('SummaryPayment::paid '.$sql, $debug);
				$results = $this->query($sql);
        } catch (Exception $e) {
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }

		return true;
	}
	
	/*
	 * aggiorno il totale in SummaryPayment
	 * se il gasista aveva solo quell'ordine SummaryPayment.stato = DAPAGARE
	*/	
	public function delete_order($user, $order_id, $request_payment_id, $debug=false) {
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
		
		$options = [];
		$options['conditions'] = ['SummaryPayment.organization_id' => (int)$user->organization['Organization']['id'],
								  'SummaryPayment.request_payment_id' => $request_payment_id];
		$options['recursive'] = -1;
		$summaryPaymentResults = $this->find('all', $options);
		foreach($summaryPaymentResults as $summaryPaymentResult) {
			
			self::l($summaryPaymentResult, $debug);
			
			$user_id = $summaryPaymentResult['SummaryPayment']['user_id'];
			$summaryOrderUserResults = $SummaryOrder->select_to_order($user, $order_id, $user_id);

			if(!empty($summaryOrderUserResults)) {
				/*
				 * se gli importi RequestPaymentsOrder.importo = SummaryPayment.importo_richiesto
				 * cancello SummaryPayment
				 */
				if($summaryOrderUserResults['SummaryOrder']['importo']==$summaryPaymentResult['SummaryPayment']['importo_richiesto']) {
					
					self::l('user_id '.$user_id.' SummaryOrder.importo ('.$summaryOrderUserResults['SummaryOrder']['importo'].') = SummaryPayment.importo_richiesto '.$summaryPaymentResult['SummaryPayment']['importo_richiesto'].' => cancello SummaryPayment.id ('.$summaryPaymentResult['SummaryPayment']['id'].')', $debug);
					
					$this->id = $summaryPaymentResult['SummaryPayment']['id'];
					if(!$this->delete()) {
						self::l('ERROR cancellazione SummaryPayment.id ('.$summaryPaymentResult['SummaryPayment']['id'].')', $debug);						
					}
					else 
						self::l('OK cancellazione SummaryPayment.id ('.$summaryPaymentResult['SummaryPayment']['id'].')', $debug);	
				}
				else {
					$importo_dovuto = ($summaryPaymentResult['SummaryPayment']['importo_dovuto'] - ($summaryOrderUserResults['SummaryOrder']['importo']));
					$importo_richiesto = ($summaryPaymentResult['SummaryPayment']['importo_richiesto'] - ($summaryOrderUserResults['SummaryOrder']['importo']));
					
					self::l('user_id '.$user_id.' SummaryOrder.importo ('.$summaryOrderUserResults['SummaryOrder']['importo'].') != SummaryPayment.importo_richiesto '.$summaryPaymentResult['SummaryPayment']['importo_richiesto'].' => aggiorno SummaryPayment: '.$importo_richiesto, $debug);
					
					/*
					 * sottraggo da SummaryPayment.importo_richiesto 
					 * 	SummaryOrder.importo che viene eliminato
					 */
					
					$data = [];
					$data['SummaryPayment'] = $summaryPaymentResult['SummaryPayment'];
					$data['SummaryPayment']['importo_dovuto'] = $importo_dovuto;
					$data['SummaryPayment']['importo_richiesto'] = $importo_richiesto;
						
					self::l($data, $debug);
					
					$this->create();
					$this->save($data);
				}
			} 
			else {
				self::l('user_id '.$user_id.' non ha acquisti per order_id '.$order_id.' => SummaryOrder empty', $debug);
			} // end if(!empty($summaryOrderUserResults)) 
		} // end foreach($summaryPaymentResults as $summaryPaymentResult)
				
		return true;
	}
	
	public $belongsTo = [
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = SummaryPayment.organization_id',
			'fields' => '',
			'order' => ''
		],
		'RequestPayment' => [
			'className' => 'RequestPayment',
			'foreignKey' => 'request_payment_id',
			'conditions' => 'RequestPayment.organization_id = SummaryPayment.organization_id',
			'fields' => '',
			'order' => ''
		]
	];	
	
	public function afterFind($results, $primary = false) {			foreach ($results as $key => $val) {			if(!empty($val)) {			
				if(isset($val['SummaryPayment']['importo_dovuto'])) {
					$results[$key]['SummaryPayment']['importo_dovuto_'] = number_format($val['SummaryPayment']['importo_dovuto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));					$results[$key]['SummaryPayment']['importo_dovuto_e'] = $results[$key]['SummaryPayment']['importo_dovuto_'].' &euro;';				}
				if(isset($val['SummaryPayment']['importo_richiesto'])) {
					$results[$key]['SummaryPayment']['importo_richiesto_'] = number_format($val['SummaryPayment']['importo_richiesto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryPayment']['importo_richiesto_e'] = $results[$key]['SummaryPayment']['importo_richiesto_'].' &euro;';
				}
				if(isset($val['SummaryPayment']['importo_pagato'])) {
					$results[$key]['SummaryPayment']['importo_pagato_'] = number_format($val['SummaryPayment']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['SummaryPayment']['importo_pagato_e'] = $results[$key]['SummaryPayment']['importo_pagato_'].' &euro;';
				}			}				
		}		return $results;	}
	
	public function beforeSave($options = []) {		
		if(!empty($this->data['SummaryPayment']['importo_dovuto'])) {
			$this->data['SummaryPayment']['importo_dovuto'] =  $this->importoToDatabase($this->data['SummaryPayment']['importo_dovuto']);
		}	
		if(!empty($this->data['SummaryPayment']['importo_richiesto'])) {
			$this->data['SummaryPayment']['importo_richiesto'] =  $this->importoToDatabase($this->data['SummaryPayment']['importo_richiesto']);
		}
		if(!empty($this->data['SummaryPayment']['importo_pagato'])) {
			$this->data['SummaryPayment']['importo_pagato'] =  $this->importoToDatabase($this->data['SummaryPayment']['importo_pagato']);
		}		return true;	}	
}