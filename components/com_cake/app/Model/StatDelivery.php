<?php
App::uses('AppModel', 'Model');

class StatDelivery extends AppModel {

	public $virtualFields = ['luogoData' => "CONCAT_WS(' - ',StatDelivery.luogo,DATE_FORMAT(StatDelivery.data, '%W, %e %M %Y'))"];	
	public $hasMany = [
			'StatOrder' => [
					'className' => 'StatOrder',
					'foreignKey' => 'stat_delivery_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			]
	];
}