<?php
App::uses('AppModel', 'Model');

class BackupOrdersOrder extends AppModel {
				
	public $name = 'BackupOrdersOrder';
	
	public $belongsTo = array(
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = BackupOrdersOrder.organization_id',
			'fields' => '',
			'BackupOrdersOrder' => ''
		),
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = BackupOrdersOrder.organization_id',
			'fields' => '',
			'BackupOrdersOrder' => ''
		)						
	);

	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {

			if(!empty($val)) {
				if (isset($val['BackupOrdersOrder']['data_inizio'])) {
					$results[$key]['BackupOrdersOrder']['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['BackupOrdersOrder']['data_inizio']);
					if(!empty($val['BackupOrdersOrder']['data_fine_validation']) && $val['BackupOrdersOrder']['data_fine_validation']!=Configure::read('DB.field.date.empty')) 
						$results[$key]['BackupOrdersOrder']['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['BackupOrdersOrder']['data_fine_validation']);
					else
						$results[$key]['BackupOrdersOrder']['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['BackupOrdersOrder']['data_fine']);
					
					$results[$key]['BackupOrdersOrder']['data_inizio_'] = date('d',strtotime($val['BackupOrdersOrder']['data_inizio'])).'/'.date('n',strtotime($val['BackupOrdersOrder']['data_inizio'])).'/'.date('Y',strtotime($val['BackupOrdersOrder']['data_inizio']));
					$results[$key]['BackupOrdersOrder']['data_fine_'] = date('d',strtotime($val['BackupOrdersOrder']['data_fine'])).'/'.date('n',strtotime($val['BackupOrdersOrder']['data_fine'])).'/'.date('Y',strtotime($val['BackupOrdersOrder']['data_fine']));
					$results[$key]['BackupOrdersOrder']['data_fine_validation_'] = date('d',strtotime($val['BackupOrdersOrder']['data_fine_validation'])).'/'.date('n',strtotime($val['BackupOrdersOrder']['data_fine_validation'])).'/'.date('Y',strtotime($val['BackupOrdersOrder']['data_fine_validation']));
					$results[$key]['BackupOrdersOrder']['tesoriere_data_pay_'] = date('d',strtotime($val['BackupOrdersOrder']['tesoriere_data_pay'])).'/'.date('n',strtotime($val['BackupOrdersOrder']['tesoriere_data_pay'])).'/'.date('Y',strtotime($val['BackupOrdersOrder']['tesoriere_data_pay']));

					$results[$key]['BackupOrdersOrder']['trasport_'] = number_format($val['BackupOrdersOrder']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['trasport_e'] = $results[$key]['BackupOrdersOrder']['trasport_'].' &euro;';				

					$results[$key]['BackupOrdersOrder']['cost_more_'] = number_format($val['BackupOrdersOrder']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['cost_more_e'] = $results[$key]['BackupOrdersOrder']['cost_more_'].' &euro;';

					$results[$key]['BackupOrdersOrder']['cost_less_'] = number_format($val['BackupOrdersOrder']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['cost_less_e'] = $results[$key]['BackupOrdersOrder']['cost_less_'].' &euro;';

					$results[$key]['BackupOrdersOrder']['tesoriere_importo_pay_'] = number_format($val['BackupOrdersOrder']['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['tesoriere_importo_pay_e'] = $results[$key]['BackupOrdersOrder']['tesoriere_importo_pay_'].' &euro;';

					$results[$key]['BackupOrdersOrder']['tesoriere_fattura_importo_'] = number_format($val['BackupOrdersOrder']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['tesoriere_fattura_importo_e'] = $results[$key]['BackupOrdersOrder']['tesoriere_fattura_importo_'].' &euro;';

					$results[$key]['BackupOrdersOrder']['importo_massimo_'] = number_format($val['BackupOrdersOrder']['importo_massimo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['importo_massimo_e'] = $results[$key]['BackupOrdersOrder']['importo_massimo_'].' &euro;';

					$results[$key]['BackupOrdersOrder']['tot_importo_'] = number_format($val['BackupOrdersOrder']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['tot_importo_e'] = $results[$key]['BackupOrdersOrder']['tot_importo_'].' &euro;';					
				}
				else 
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				 */
				if (isset($val['data_inizio'])) {
					$results[$key]['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['data_inizio']);
					if(!empty($val['data_fine_validation']) && $val['data_fine_validation']!=Configure::read('DB.field.date.empty'))
						$results[$key]['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['data_fine']);
					else
						$results[$key]['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['data_fine']);
						
					$results[$key]['data_inizio_'] = date('d',strtotime($val['data_inizio'])).'/'.date('n',strtotime($val['data_inizio'])).'/'.date('Y',strtotime($val['data_inizio']));
					$results[$key]['data_fine_'] = date('d',strtotime($val['data_fine'])).'/'.date('n',strtotime($val['data_fine'])).'/'.date('Y',strtotime($val['data_fine']));
					$results[$key]['data_fine_validation_'] = date('d',strtotime($val['data_fine_validation'])).'/'.date('n',strtotime($val['data_fine_validation'])).'/'.date('Y',strtotime($val['data_fine_validation']));
					$results[$key]['tesoriere_data_pay_'] = date('d',strtotime($val['tesoriere_data_pay'])).'/'.date('n',strtotime($val['tesoriere_data_pay'])).'/'.date('Y',strtotime($val['tesoriere_data_pay']));
				}	
				
				if(isset($val['BackupOrdersOrder']['trasport'])) {
					$results[$key]['BackupOrdersOrder']['trasport_'] = number_format($val['BackupOrdersOrder']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['trasport_e'] = $results[$key]['BackupOrdersOrder']['trasport_'].' &euro;';
				}
				else 
				if(isset($val['trasport'])) {
					$results[$key]['trasport_'] = number_format($val['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['trasport_e'] = $results['BackupOrdersOrder']['trasport_'].' &euro;';
				}		

				if(isset($val['BackupOrdersOrder']['cost_more'])) {
					$results[$key]['BackupOrdersOrder']['cost_more_'] = number_format($val['BackupOrdersOrder']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['cost_more_e'] = $results[$key]['BackupOrdersOrder']['cost_more_'].' &euro;';
				}
				else
				if(isset($val['cost_more'])) {
					$results[$key]['cost_more_'] = number_format($val['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['cost_more_e'] = $results['BackupOrdersOrder']['cost_more_'].' &euro;';
				}

				if(isset($val['BackupOrdersOrder']['cost_less'])) {
					$results[$key]['BackupOrdersOrder']['cost_less_'] = number_format($val['BackupOrdersOrder']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['cost_less_e'] = $results[$key]['BackupOrdersOrder']['cost_less_'].' &euro;';
				}
				else
				if(isset($val['cost_less'])) {
					$results[$key]['cost_less_'] = number_format($val['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['cost_less_e'] = $results['BackupOrdersOrder']['cost_less_'].' &euro;';
				}
				
				if(isset($val['BackupOrdersOrder']['tesoriere_importo_pay'])) {
					$results[$key]['BackupOrdersOrder']['tesoriere_importo_pay_'] = number_format($val['BackupOrdersOrder']['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['tesoriere_importo_pay_e'] = $results[$key]['BackupOrdersOrder']['tesoriere_importo_pay_'].' &euro;';
				}
				else
				if(isset($val['tesoriere_importo_pay'])) {
					$results[$key]['tesoriere_importo_pay_'] = number_format($val['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['tesoriere_importo_pay_e'] = $results['BackupOrdersOrder']['tesoriere_importo_pay_'].' &euro;';
				}
				
				if(isset($val['BackupOrdersOrder']['tesoriere_fattura_importo'])) {
					$results[$key]['BackupOrdersOrder']['tesoriere_fattura_importo_'] = number_format($val['BackupOrdersOrder']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['tesoriere_fattura_importo_e'] = $results[$key]['BackupOrdersOrder']['tesoriere_fattura_importo_'].' &euro;';
				}
				else
				if(isset($val['tesoriere_fattura_importo'])) {
					$results[$key]['tesoriere_fattura_importo_'] = number_format($val['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['tesoriere_fattura_importo_e'] = $results['BackupOrdersOrder']['tesoriere_fattura_importo_'].' &euro;';
				}
	
				if(isset($val['BackupOrdersOrder']['importo_massimo'])) {
					$results[$key]['BackupOrdersOrder']['importo_massimo_'] = number_format($val['BackupOrdersOrder']['importo_massimo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['importo_massimo_e'] = $results[$key]['BackupOrdersOrder']['importo_massimo_'].' &euro;';
				}
				else
				if(isset($val['importo_massimo'])) {
					$results[$key]['importo_massimo_'] = number_format($val['importo_massimo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['importo_massimo_e'] = $results['BackupOrdersOrder']['importo_massimo_'].' &euro;';
				}

				if(isset($val['BackupOrdersOrder']['tot_importo'])) {
					$results[$key]['BackupOrdersOrder']['tot_importo_'] = number_format($val['BackupOrdersOrder']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['BackupOrdersOrder']['tot_importo_e'] = $results[$key]['BackupOrdersOrder']['tot_importo_'].' &euro;';
				}
				else
				if(isset($val['tot_importo'])) {
					$results[$key]['tot_importo_'] = number_format($val['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['tot_importo_e'] = $results['BackupOrdersOrder']['tot_importo_'].' &euro;';
				}
			}				
		}
	
		return $results;
	}
	
	public function beforeValidate($options = []) {
		 
		if (!empty($this->data['BackupOrdersOrder']['data_inizio']))
			$this->data['BackupOrdersOrder']['data_inizio'] = $this->data['BackupOrdersOrder']['data_inizio_db'];

		if (!empty($this->data['BackupOrdersOrder']['data_fine']))
			$this->data['BackupOrdersOrder']['data_fine'] = $this->data['BackupOrdersOrder']['data_fine_db'];

		if (!empty($this->data['BackupOrdersOrder']['data_fine_validation_db']))
			$this->data['BackupOrdersOrder']['data_fine_validation'] = $this->data['BackupOrdersOrder']['data_fine_validation_db'];
		
		if (!empty($this->data['BackupOrdersOrder']['tesoriere_data_pay_db']))
			$this->data['BackupOrdersOrder']['tesoriere_data_pay'] = $this->data['BackupOrdersOrder']['tesoriere_data_pay_db'];
			
		return true;
	}
		
	public function beforeSave($options = []) {
		if (!empty($this->data['BackupOrdersOrder']['data_inizio_db'])) 
	    	$this->data['BackupOrdersOrder']['data_inizio'] = $this->data['BackupOrdersOrder']['data_inizio_db'];

		if (!empty($this->data['BackupOrdersOrder']['data_fine_db']))
			$this->data['BackupOrdersOrder']['data_fine'] = $this->data['BackupOrdersOrder']['data_fine_db'];
				
	    if (!empty($this->data['BackupOrdersOrder']['data_fine_validation_db']))
	    	$this->data['BackupOrdersOrder']['data_fine_validation'] = $this->data['BackupOrdersOrder']['data_fine_validation_db'];
			
	    if (!empty($this->data['BackupOrdersOrder']['tesoriere_data_pay_db']))
	    	$this->data['BackupOrdersOrder']['tesoriere_data_pay'] = $this->data['BackupOrdersOrder']['tesoriere_data_pay_db'];

		if (empty($this->data['BackupOrdersOrder']['data_fine_validation']) || $this->data['BackupOrdersOrder']['data_fine_validation']==Configure::read('DB.field.date.error'))
			$this->data['BackupOrdersOrder']['data_fine_validation'] = Configure::read('DB.field.date.empty');
		
		if (empty($this->data['BackupOrdersOrder']['tesoriere_data_pay']) || $this->data['BackupOrdersOrder']['tesoriere_data_pay']==Configure::read('DB.field.date.error'))
			$this->data['BackupOrdersOrder']['tesoriere_data_pay'] = Configure::read('DB.field.date.empty');
		
		if (empty($this->data['BackupOrdersOrder']['data_incoming_order']) || $this->data['BackupOrdersOrder']['data_incoming_order']==Configure::read('DB.field.date.error'))
			$this->data['BackupOrdersOrder']['data_incoming_order'] = Configure::read('DB.field.date.empty');
				
		if (empty($this->data['BackupOrdersOrder']['data_state_code_close']) || $this->data['BackupOrdersOrder']['data_state_code_close']==Configure::read('DB.field.date.error'))
			$this->data['BackupOrdersOrder']['data_state_code_close'] = Configure::read('DB.field.date.empty');
		
		if (empty($this->data['BackupOrdersOrder']['trasport']))
			$this->data['BackupOrdersOrder']['trasport'] = Configure::read('DB.field.double.empty');
		
		if (empty($this->data['BackupOrdersOrder']['cost_more']))
			$this->data['BackupOrdersOrder']['cost_more'] = Configure::read('DB.field.double.empty');
		
		if (empty($this->data['BackupOrdersOrder']['cost_less']))
			$this->data['BackupOrdersOrder']['cost_less'] = Configure::read('DB.field.double.empty');
	
		if (empty($this->data['BackupOrdersOrder']['tot_importo']))
			$this->data['BackupOrdersOrder']['tot_importo'] = Configure::read('DB.field.double.empty');

		if (empty($this->data['BackupOrdersOrder']['importo_massimo']))
			$this->data['BackupOrdersOrder']['importo_massimo'] = Configure::read('DB.field.double.empty');
		else
			$this->data['BackupOrdersOrder']['importo_massimo'] = $this->importoToDatabase($this->data['BackupOrdersOrder']['importo_massimo']);

		if (empty($this->data['BackupOrdersOrder']['tesoriere_fattura_importo']))
			$this->data['BackupOrdersOrder']['tesoriere_fattura_importo'] = Configure::read('DB.field.double.empty');
	
		if (empty($this->data['BackupOrdersOrder']['mail_open_data']))
			$this->data['BackupOrdersOrder']['mail_open_data'] = Configure::read('DB.field.datetime.empty');
		
		if (empty($this->data['BackupOrdersOrder']['mail_close_data']))
			$this->data['BackupOrdersOrder']['mail_close_data'] = Configure::read('DB.field.datetime.empty');
				
	    return true;
	}
}