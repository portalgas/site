<?php
App::uses('AppModel', 'Model');


class DesDelivery extends AppModel {

	public $actsAs = array('Data');
	public $virtualFields = array('luogoData' => "CONCAT_WS(' - ', DesDelivery.luogo,DATE_FORMAT(DesDelivery.data, '%W, %e %M %Y'))"); 
	
	/*
	 * se il referente ha creato la DesDelivery (DesDelivery.organization_id = $this->user->organization['Organization']['id'])
	 * e' amministratore dell'ordine
	 */
	public function isReferenteIntraGas($user, $des_id, $des_delivery_id) {
	
		$options = [];
		$options['conditions'] = array('DesDelivery.des_id' =>  $des_id, 
									   'DesDelivery.des_delivery_id' =>  $des_delivery_id,
									   'DesDelivery.organization_id' => $user->organization['Organization']['id']);
		$options['recursive'] = -1;
		$options['fields'] = array('des_id');
		$results = $this->find('first', $options);		
		if(empty($results))
			return false;
		else
			return true;
	
	}
	
	public $validate = array(
		'luogo' => array(
			'rule' => array('notempty'),
			'message' => 'Indica il luogo della consegna',
			'allowEmpty' => false
		),
		'data' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'Indica la data della consegna',
				'allowEmpty' => false
			),
			 'dateCompareDataOggi' => array(
					'rule' => array('date_compare_data_oggi'),
					'message' => 'La data della consegna è antecedente o uguale rispetto alla data di odierna',
			),
		),
		'orario_da' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => "Indica a che ora ha inizio la consegna",
			),
			'orarioCtrl' => array(
				'rule'       =>  array('orario_crtl', '>=', 'orario_a'),
				'message'    => "L'orario DA non può essere posteriore o uguale dell'orario A",
			),				
		),
		'orario_a' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => "Indica a che ora si conclude la consegna",
			),
			'orarioCtrl' => array(
				'rule'       =>  array('orario_crtl', '<=', 'orario_da'),
				'message'    => "L'orario A non può essere precedente o uguale dell'orario DA",
			),
		),
	);

	/*
	 * ctrl che la data della consegna sia maggiore alla data odierna
	 * */
	function date_compare_data_oggi($field=[]) {

		$continue = true;
 		$operator = '>';
		
		$data_delivery = $field['data'];
		$data_oggi = (date("Y-m-d"));
	
		if (!Validation::comparison($data_delivery, $operator, $data_oggi))
			$continue = false;

		return $continue;
	}
	
	function orario_crtl($field=[], $operator, $field2) {
		foreach( $field as $key => $value1 ){
			$value2 = $this->data[$this->alias][$field2];
			
			$value1 = str_replace(':','',$value1);
			$value2 = str_replace(':','',$value2);
			
			if (!Validation::comparison($value1, $operator, $value2))
				return true;
			else
				return false;
		}
		return true;
	}
	
	public function afterFind($results, $primary = true) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if (isset($val['DesDelivery']['data'])) {
					$results[$key]['DesDelivery']['daysToEndConsegna'] = $this->utilsCommons->dayDiffToDate(date("Y-m-d"),$val['DesDelivery']['data']);
				}
			}
		}
		return $results;
	}	
		
	public $belongsTo = array(
			'De' => array(
					'className' => 'De',
					'foreignKey' => 'des_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'Organization' => array(
					'className' => 'Organization',
					'foreignKey' => 'organization_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
	);	
	
	public $hasMany = array(
		'DesOrdersOrganization' => array(
			'className' => 'DesOrdersOrganization',
			'foreignKey' => 'des_delivery_id',
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
}