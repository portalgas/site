<?php
App::uses('AppModel', 'Model');


class Delivery extends AppModel {

	public $actsAs = array('Data');
	public $virtualFields = array('luogoData' => "CONCAT_WS(' - ',Delivery.luogo,DATE_FORMAT(Delivery.data, '%W, %e %M %Y'))"); 
	
	public $validate = array(
		'luogo' => array(
			'rule' => ['notBlank'],
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
					'message' => 'La data della consegna è antecedente rispetto alla data di odierna',
			),
			'dateCompareOrdersData' => array(
				'rule' => array('date_compare_orders_data'),
				'message' => 'La data della consegna è antecedente rispetto alla data di chiusura di un ordine associato',
			),
		),
		'orario_da' => array(
			'notempty' => array(
				'rule' => ['notBlank'],
				'message' => "Indica a che ora ha inizio la consegna",
			),
			'orarioCtrl' => array(
				'rule'       =>  array('orario_crtl', '>=', 'orario_a'),
				'message'    => "L'orario DA non può essere posteriore o uguale dell'orario A",
			),				
		),
		'orario_a' => array(
			'notempty' => array(
				'rule' => ['notBlank'],
				'message' => "Indica a che ora si conclude la consegna",
			),
			'orarioCtrl' => array(
				'rule'       =>  array('orario_crtl', '<=', 'orario_da'),
				'message'    => "L'orario A non può essere precedente o uguale dell'orario DA",
			),
		),
	);
	/*
	 * ctrl che la data della consegna sia maggiore della data di chiusura dei suoi ordini
	* */
	function date_compare_orders_data($field=[]) {
	
		$continue = true;
		$operator = '>';
	
		$data_delivery = $field['data'];
		$id = $this->data[$this->alias]['id'];
		
		$conditions = array('Delivery.id' => $id);
		$results = $this->find('first',array('conditions'=>$conditions,'recursive'=>1));
	
		if(isset($results['Order']) && !empty($results['Order']))
			foreach ($results['Order'] as $order) {
			$data_fine = $order['data_fine'];
	
			if (!Validation::comparison($data_delivery, $operator, $data_fine))
				$continue = false;
		}
	
		return $continue;
	}
	
	/*
	 * ctrl che la data della consegna sia maggiore alla data odierna
	 * */
	function date_compare_data_oggi($field=[]) {

		$continue = true;
 		$operator = '>';
		
		$data_delivery = $field['data'];
		$data_oggi = (date("Y-m-d"));
	
		/*
		 * tolgo un giorno cosi' rendo valido la data odierna
		 */
		$data_oggi = date('Y-m-d', strtotime($data_oggi. ' - 1 days'));
		
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
	
	public $hasMany = array(
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'delivery_id',
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
				if (isset($val['Delivery']['data'])) {
					$results[$key]['Delivery']['daysToEndConsegna'] = $this->utilsCommons->dayDiffToDate(date("Y-m-d"),$val['Delivery']['data']);
				}
			}
		}
		return $results;
	}	
	
	public function getDeliverySys($user) {

		$options = [];
		$options['conditions'] = array('Delivery.organization_id' => $user->organization['Organization']['id'],
									   'Delivery.sys' => 'Y');
		$options['recursive'] = -1;
		$results = $this->find('first', $options);
		
		return $results;	
	}	
}