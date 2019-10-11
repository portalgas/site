<?php
App::uses('AppModel', 'Model');


class MonitoringOrder extends AppModel {

	public function delete_to_order($user, $order_id, $debug = false) {
		
		$sql = "DELETE
				FROM
					".Configure::read('DB.prefix')."monitoring_orders
				WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
					and order_id = ".(int)$order_id;
		self::d($sql, $debug);
		try {
			$result = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}
	}
	
	public $validate = array(
			'organization_id' => array(
					'numeric' => array(
							'rule' => ['numeric'],
					),
			),
			'order_id' => array(
					'numeric' => array(
							'rule' => ['numeric'],
					),
			),
			'user_id' => array(
					'numeric' => array(
							'rule' => ['numeric'],
					),
			),
	);
	
	public $belongsTo = array(
			'User' => array(
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => 'User.organization_id = MonitoringOrder.organization_id',
					'fields' => '',
					'order' => ''
			),
			'Order' => array(
					'className' => 'Order',
					'foreignKey' => 'order_id',
					'conditions' => 'Order.organization_id = MonitoringOrder.organization_id',
					'fields' => '',
					'order' => ''
			),
	);
}