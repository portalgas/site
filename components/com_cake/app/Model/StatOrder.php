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
	
	public function afterFind($results, $primary = true) {
}