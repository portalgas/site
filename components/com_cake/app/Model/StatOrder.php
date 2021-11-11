<?php
App::uses('AppModel', 'Model');

class StatOrder extends AppModel {
	
	public $virtualFields = ['name' => "CONCAT_WS(' - ',DATE_FORMAT(StatOrder.data_inizio, '%W, %e %M %Y'),DATE_FORMAT(StatOrder.data_fine, '%W, %e %M %Y'))"]; 
	
	public $belongsTo = [
		'SuppliersOrganization' => [
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = StatOrder.organization_id',
			'fields' => '',
			'StatOrder' => ''
		],
		'StatDelivery' => [
			'className' => 'StatDelivery',
			'foreignKey' => 'stat_delivery_id',
			'conditions' => 'StatDelivery.organization_id = StatOrder.organization_id',
			'fields' => '',
			'StatOrder' => ''
		]						
	];
	
	public function afterFind($results, $primary = true) {
		foreach ($results as $key => $val) {
	
			if(!empty($val)) {
				if (isset($val['StatOrder']['data_inizio'])) {						
					$results[$key]['StatOrder']['data_inizio_'] = date('d',strtotime($val['StatOrder']['data_inizio'])).'/'.date('n',strtotime($val['StatOrder']['data_inizio'])).'/'.date('Y',strtotime($val['StatOrder']['data_inizio']));
					$results[$key]['StatOrder']['data_fine_'] = date('d',strtotime($val['StatOrder']['data_fine'])).'/'.date('n',strtotime($val['StatOrder']['data_fine'])).'/'.date('Y',strtotime($val['StatOrder']['data_fine']));
				}
				else
					/*
					 * se il find() arriva da $hasAndBelongsToMany
					*/
				if (isset($val['data_inizio'])) {
	
					$results[$key]['data_inizio_'] = date('d',strtotime($val['data_inizio'])).'/'.date('n',strtotime($val['data_inizio'])).'/'.date('Y',strtotime($val['data_inizio']));
					$results[$key]['data_fine_'] = date('d',strtotime($val['data_fine'])).'/'.date('n',strtotime($val['data_fine'])).'/'.date('Y',strtotime($val['data_fine']));
				}
			}
		}
	
		return $results;
	}	
}