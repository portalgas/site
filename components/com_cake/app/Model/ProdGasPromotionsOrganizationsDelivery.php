<?php
App::uses('AppModel', 'Model');

class ProdGasPromotionsOrganizationsDelivery extends AppModel {

	public function getOrganizationsDeliveryList($user, $prod_gas_promotion_id, $debug=false) {
		
		$deliveries = [];
		
	
		$options =  [];
		$options['conditions'] = ['ProdGasPromotionsOrganizationsDelivery.organization_id' => (int)$user->organization['Organization']['id'],
								  'ProdGasPromotionsOrganizationsDelivery.prod_gas_promotion_id' => $prod_gas_promotion_id,
								  'Delivery.isVisibleBackOffice' => 'Y',
								  'Delivery.stato_elaborazione' => 'OPEN',
								  'Delivery.sys'=>'N',
								  'DATE(Delivery.data) >= CURDATE()'];
		$options['order'] = ['Delivery.data' => 'asc'];
		$this->unbindModel(['belongsTo' => ['ProdGasPromotion', 'Organization']]);
		$options['recursive'] = 0;
		$prodGasPromotionsOrganizationsDeliveryResults = $this->find('all', $options);

		if(!empty($prodGasPromotionsOrganizationsDeliveryResults))
		foreach($prodGasPromotionsOrganizationsDeliveryResults as $prodGasPromotionsOrganizationsDeliveryResult) {
			$deliveries[$prodGasPromotionsOrganizationsDeliveryResult['Delivery']['id']] = $prodGasPromotionsOrganizationsDeliveryResult['Delivery']['luogoData'];
		}
		self::d($options, $debug);
		self::d($deliveries, $debug);
		
		return $deliveries;
	}
		
	public $validate = array(
		'supplier_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'delivery_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $belongsTo = [
		'ProdGasPromotion' => [
			'className' => 'ProdGasPromotion',
			'foreignKey' => 'prod_gas_promotion_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Organization' => [
			'className' => 'Organization',
			'foreignKey' => 'organization_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Delivery' => [
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = ProdGasPromotionsOrganizationsDelivery.organization_id',
			'fields' => '',
			'order' => ''
		],
	];	
}