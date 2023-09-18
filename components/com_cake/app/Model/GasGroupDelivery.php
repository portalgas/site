<?php
App::uses('AppModel', 'Model');
App::uses('CakeTime', 'Utility');

class GasGroupDelivery extends AppModel {	
	
	public $tablePrefix = false;
	public $table = 'gas_group_deliveries';

	public $belongsTo = [
		'GasGroup' => [
				'className' => 'GasGroup',
				'foreignKey' => 'gas_group_id',
				'conditions' => 'GasGroup.organization_id = GasGroupDelivery.organization_id',
		],
		'Delivery' => [
				'className' => 'Delivery',
				'foreignKey' => 'delivery_id',
				'conditions' => 'Delivery.organization_id = GasGroupDelivery.organization_id',
		],
	];

	/*
	 * alla consegna aggiungo il prefisso del nome del gruppo
	 */
	public function getLabel($user, $organization_id, $delivery_id) {

		$options = [];
		$options['conditions'] = ['GasGroupDelivery.organization_id' => $organization_id,
								  'GasGroupDelivery.delivery_id' => $delivery_id,
								  'GasGroup.is_active' => true,
								  'Delivery.isVisibleBackOffice' => 'Y'];
		$options['recursive'] = 0;

		$gas_group_delivery = $this->find('first', $options);
		if(empty($gas_group_delivery))
			return false;

		$group_name = $gas_group_delivery['GasGroup']['name'];
		// $DeliveryData = CakeTime::format($gas_group_delivery['Delivery']['data'], "%A %e %B %Y");
		if($gas_group_delivery['Delivery']['sys']=='N')
			$delivery_label = $gas_group_delivery['Delivery']['luogoData'];
		else
			$delivery_label = $gas_group_delivery['Delivery']['luogo'];		
		
		$result = $group_name.' - '.$delivery_label;
	    
		return $result;		
	}
}