<?php
App::uses('AppModel', 'Model');

class StatDelivery extends AppModel {

	public $virtualFields = array('luogoData' => "CONCAT_WS(' - ',StatDelivery.luogo,DATE_FORMAT(StatDelivery.data, '%W, %e %M %Y'))");	
	public $hasMany = array(
			'StatOrder' => array(
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
			)
	);

	public function afterFind($results, $primary = true) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if (isset($val['StatDelivery']['data'])) {
					$results[$key]['StatDelivery']['daysToEndConsegna'] = $this->utilsCommons->dayDiffToDate(date("Y-m-d"),$val['StatDelivery']['data']);
				}
			}
		}
		return $results;
	}
}