<?php
App::uses('AppModel', 'Model');

class StatOrder extends AppModel {

	public $belongsTo = array(
			'SuppliersOrganization' => array(
					'className' => 'SuppliersOrganization',
					'foreignKey' => 'supplier_organization_id',
					'conditions' => 'SuppliersOrganization.organization_id = StatOrder.organization_id',
					'fields' => '',
					'order' => ''
			),
			'StatDelivery' => array(
					'className' => 'StatDelivery',
					'foreignKey' => 'stat_delivery_id',
					'conditions' => 'StatDelivery.organization_id = StatOrder.organization_id',
					'fields' => '',
					'order' => ''
			),
	);
	
	public function afterFind($results, $primary = true) {		foreach ($results as $key => $val) {				if(!empty($val)) {				if (isset($val['StatOrder']['data_inizio'])) {					$results[$key]['StatOrder']['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['StatOrder']['data_inizio']);					$results[$key]['StatOrder']['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['StatOrder']['data_fine']);											$results[$key]['StatOrder']['data_inizio_'] = date('d',strtotime($val['StatOrder']['data_inizio'])).'/'.date('n',strtotime($val['StatOrder']['data_inizio'])).'/'.date('Y',strtotime($val['StatOrder']['data_inizio']));					$results[$key]['StatOrder']['data_fine_'] = date('d',strtotime($val['StatOrder']['data_fine'])).'/'.date('n',strtotime($val['StatOrder']['data_fine'])).'/'.date('Y',strtotime($val['StatOrder']['data_fine']));				}				else					/*					 * se il find() arriva da $hasAndBelongsToMany				*/					if (isset($val['data_inizio'])) {					$results[$key]['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['data_inizio']);					$results[$key]['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['data_fine']);						$results[$key]['data_inizio_'] = date('d',strtotime($val['data_inizio'])).'/'.date('n',strtotime($val['data_inizio'])).'/'.date('Y',strtotime($val['data_inizio']));					$results[$key]['data_fine_'] = date('d',strtotime($val['data_fine'])).'/'.date('n',strtotime($val['data_fine'])).'/'.date('Y',strtotime($val['data_fine']));				}			}		}			return $results;	}
}