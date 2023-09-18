<?php
App::uses('AppModel', 'Model');
App::uses('CakeTime', 'Utility');

class GasGroupOrder extends AppModel {	
	
	public $useTable = 'orders';

	public $belongsTo = [
		'GasGroup' => [
				'className' => 'GasGroup',
				'foreignKey' => 'gas_group_id',
				'conditions' => 'GasGroup.organization_id = GasGroupOrder.organization_id',
		],
		'Delivery' => [
				'className' => 'Delivery',
				'foreignKey' => 'delivery_id',
				'conditions' => 'Delivery.organization_id = GasGroupOrder.organization_id',
		],
		'SuppliersOrganization' => [
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = GasGroupOrder.organization_id'
	],
	];

	/*
	 * all'ordine aggiungo il prefisso del nome del gruppo
	 */
	public function getLabel($user, $organization_id, $order_id) {

		$options = [];
		$options['conditions'] = ['GasGroupOrder.organization_id' => $organization_id,
								  'GasGroupOrder.id' => $order_id,
								  'GasGroupOrder.isVisibleBackOffice' => 'Y',
								  'GasGroup.is_active' => true,
								  'SuppliersOrganization.stato != ' => 'N'];
		$options['recursive'] = 0;
		$gas_group_order = $this->find('first', $options);
		if(empty($gas_group_order))
			return false;

		$group_name = $gas_group_order['GasGroup']['name'];
		if ($gas_group_order['GasGroupOrder']['data_fine_validation'] != Configure::read('DB.field.date.empty'))
			$data_fine = $gas_group_order['GasGroupOrder']['data_fine_validation_'];
		else
			$data_fine = $gas_group_order['GasGroupOrder']['data_fine_'];
			
		$result = $group_name . ' - ' . $gas_group_order['SuppliersOrganization']['name'] . ' - dal ' . $gas_group_order['GasGroupOrder']['data_inizio_'] . ' al ' . $data_fine;
		return $result;		
	}


	public function afterFind($results, $primary = true) {
		
		App::import('Model', 'DesOrder');

		foreach ($results as $key => $val) {

			if(!empty($val)) {
				if (isset($val['GasGroupOrder']['data_inizio'])) {
					$results[$key]['GasGroupOrder']['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['GasGroupOrder']['data_inizio']);
					if(!empty($val['GasGroupOrder']['data_fine_validation']) && $val['GasGroupOrder']['data_fine_validation']!=Configure::read('DB.field.date.empty')) 
						$results[$key]['GasGroupOrder']['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['GasGroupOrder']['data_fine_validation']);
					else
						$results[$key]['GasGroupOrder']['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['GasGroupOrder']['data_fine']);

					/* 
					 * estraggo stato dell'ordine DES
					 * */
					$desOrderStateCode = '';
					if(!empty($results[$key]['GasGroupOrder']['des_order_id'])) {
						$DesOrder = new DesOrder;
						$options = [];
						$options['fields'] = ['state_code'];
						$options['conditions'] = ['id' => $results[$key]['GasGroupOrder']['des_order_id']];
						$options['recursive'] = -1;
						$desOrderStateCode = $DesOrder->find('first', $options);
						if(!empty($desOrderStateCode))
							$desOrderStateCode = $desOrderStateCode['DesOrder']['state_code'];
					}
											
					$results[$key]['GasGroupOrder']['data_inizio_'] = date('d',strtotime($val['GasGroupOrder']['data_inizio'])).'/'.date('n',strtotime($val['GasGroupOrder']['data_inizio'])).'/'.date('Y',strtotime($val['GasGroupOrder']['data_inizio']));
					$results[$key]['GasGroupOrder']['data_fine_'] = date('d',strtotime($val['GasGroupOrder']['data_fine'])).'/'.date('n',strtotime($val['GasGroupOrder']['data_fine'])).'/'.date('Y',strtotime($val['GasGroupOrder']['data_fine']));
					$results[$key]['GasGroupOrder']['data_fine_validation_'] = date('d',strtotime($val['GasGroupOrder']['data_fine_validation'])).'/'.date('n',strtotime($val['GasGroupOrder']['data_fine_validation'])).'/'.date('Y',strtotime($val['GasGroupOrder']['data_fine_validation']));
					$results[$key]['GasGroupOrder']['tesoriere_data_pay_'] = date('d',strtotime($val['GasGroupOrder']['tesoriere_data_pay'])).'/'.date('n',strtotime($val['GasGroupOrder']['tesoriere_data_pay'])).'/'.date('Y',strtotime($val['GasGroupOrder']['tesoriere_data_pay']));

					$results[$key]['GasGroupOrder']['trasport_'] = number_format($val['GasGroupOrder']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['trasport_e'] = $results[$key]['GasGroupOrder']['trasport_'].' &euro;';				

					$results[$key]['GasGroupOrder']['cost_more_'] = number_format($val['GasGroupOrder']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['cost_more_e'] = $results[$key]['GasGroupOrder']['cost_more_'].' &euro;';

					$results[$key]['GasGroupOrder']['cost_less_'] = number_format($val['GasGroupOrder']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['cost_less_e'] = $results[$key]['GasGroupOrder']['cost_less_'].' &euro;';

					$results[$key]['GasGroupOrder']['tesoriere_importo_pay_'] = number_format($val['GasGroupOrder']['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['tesoriere_importo_pay_e'] = $results[$key]['GasGroupOrder']['tesoriere_importo_pay_'].' &euro;';

					$results[$key]['GasGroupOrder']['tesoriere_fattura_importo_'] = number_format($val['GasGroupOrder']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['tesoriere_fattura_importo_e'] = $results[$key]['GasGroupOrder']['tesoriere_fattura_importo_'].' &euro;';

					$results[$key]['GasGroupOrder']['importo_massimo_'] = number_format($val['GasGroupOrder']['importo_massimo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['importo_massimo_e'] = $results[$key]['GasGroupOrder']['importo_massimo_'].' &euro;';

					$results[$key]['GasGroupOrder']['tot_importo_'] = number_format($val['GasGroupOrder']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['tot_importo_e'] = $results[$key]['GasGroupOrder']['tot_importo_'].' &euro;';					
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
						
					/* 
					 * estraggo stato dell'ordine DES
					 * */
					$desOrderStateCode = '';
					if(!empty($val['des_order_id'])) {
						$DesOrder = new DesOrder;
						$options = [];
						$options['fields'] = ['state_code'];
						$options['conditions'] = ['id' => $val['des_order_id']];
						$options['recursive'] = -1;
						$desOrderStateCode = $DesOrder->find('first', $options);
						if(!empty($desOrderStateCode))
							$desOrderStateCode = $desOrderStateCode['DesOrder']['state_code'];
					}
						
					$results[$key]['data_inizio_'] = date('d',strtotime($val['data_inizio'])).'/'.date('n',strtotime($val['data_inizio'])).'/'.date('Y',strtotime($val['data_inizio']));
					$results[$key]['data_fine_'] = date('d',strtotime($val['data_fine'])).'/'.date('n',strtotime($val['data_fine'])).'/'.date('Y',strtotime($val['data_fine']));
					$results[$key]['data_fine_validation_'] = date('d',strtotime($val['data_fine_validation'])).'/'.date('n',strtotime($val['data_fine_validation'])).'/'.date('Y',strtotime($val['data_fine_validation']));
					$results[$key]['tesoriere_data_pay_'] = date('d',strtotime($val['tesoriere_data_pay'])).'/'.date('n',strtotime($val['tesoriere_data_pay'])).'/'.date('Y',strtotime($val['tesoriere_data_pay']));
				}	
				
				if(isset($val['GasGroupOrder']['trasport'])) {
					$results[$key]['GasGroupOrder']['trasport_'] = number_format($val['GasGroupOrder']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['trasport_e'] = $results[$key]['GasGroupOrder']['trasport_'].' &euro;';
				}
				else 
				if(isset($val['trasport'])) {
					$results[$key]['trasport_'] = number_format($val['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['trasport_e'] = $results['GasGroupOrder']['trasport_'].' &euro;';
				}		

				if(isset($val['GasGroupOrder']['cost_more'])) {
					$results[$key]['GasGroupOrder']['cost_more_'] = number_format($val['GasGroupOrder']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['cost_more_e'] = $results[$key]['GasGroupOrder']['cost_more_'].' &euro;';
				}
				else
				if(isset($val['cost_more'])) {
					$results[$key]['cost_more_'] = number_format($val['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['cost_more_e'] = $results['GasGroupOrder']['cost_more_'].' &euro;';
				}

				if(isset($val['GasGroupOrder']['cost_less'])) {
					$results[$key]['GasGroupOrder']['cost_less_'] = number_format($val['GasGroupOrder']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['cost_less_e'] = $results[$key]['GasGroupOrder']['cost_less_'].' &euro;';
				}
				else
				if(isset($val['cost_less'])) {
					$results[$key]['cost_less_'] = number_format($val['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['cost_less_e'] = $results['GasGroupOrder']['cost_less_'].' &euro;';
				}
				
				if(isset($val['GasGroupOrder']['tesoriere_importo_pay'])) {
					$results[$key]['GasGroupOrder']['tesoriere_importo_pay_'] = number_format($val['GasGroupOrder']['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['tesoriere_importo_pay_e'] = $results[$key]['GasGroupOrder']['tesoriere_importo_pay_'].' &euro;';
				}
				else
				if(isset($val['tesoriere_importo_pay'])) {
					$results[$key]['tesoriere_importo_pay_'] = number_format($val['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['tesoriere_importo_pay_e'] = $results['GasGroupOrder']['tesoriere_importo_pay_'].' &euro;';
				}
				
				if(isset($val['GasGroupOrder']['tesoriere_fattura_importo'])) {
					$results[$key]['GasGroupOrder']['tesoriere_fattura_importo_'] = number_format($val['GasGroupOrder']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['tesoriere_fattura_importo_e'] = $results[$key]['GasGroupOrder']['tesoriere_fattura_importo_'].' &euro;';
				}
				else
				if(isset($val['tesoriere_fattura_importo'])) {
					$results[$key]['tesoriere_fattura_importo_'] = number_format($val['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['tesoriere_fattura_importo_e'] = $results['GasGroupOrder']['tesoriere_fattura_importo_'].' &euro;';
				}
	
				if(isset($val['GasGroupOrder']['importo_massimo'])) {
					$results[$key]['GasGroupOrder']['importo_massimo_'] = number_format($val['GasGroupOrder']['importo_massimo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['importo_massimo_e'] = $results[$key]['GasGroupOrder']['importo_massimo_'].' &euro;';
				}
				else
				if(isset($val['importo_massimo'])) {
					$results[$key]['importo_massimo_'] = number_format($val['importo_massimo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['importo_massimo_e'] = $results['GasGroupOrder']['importo_massimo_'].' &euro;';
				}

				if(isset($val['GasGroupOrder']['tot_importo'])) {
					$results[$key]['GasGroupOrder']['tot_importo_'] = number_format($val['GasGroupOrder']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['GasGroupOrder']['tot_importo_e'] = $results[$key]['GasGroupOrder']['tot_importo_'].' &euro;';
				}
				else
				if(isset($val['tot_importo'])) {
					$results[$key]['tot_importo_'] = number_format($val['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['tot_importo_e'] = $results['GasGroupOrder']['tot_importo_'].' &euro;';
				}
			}				
		}
	
		return $results;
	}	
}