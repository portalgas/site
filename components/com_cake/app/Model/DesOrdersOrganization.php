<?php
App::uses('AppModel', 'Model');

class DesOrdersOrganization extends AppModel {
 
	public $actsAs = array('Data');
	public $virtualFields = array('luogoData' => "CONCAT_WS(' - ',DesOrdersOrganization.luogo,DATE_FORMAT(DesOrdersOrganization.data, '%W, %e %M %Y'))"); 
  
  	/*
  	 * dato un ordine estraggo l'eventuale DesOrder 
  	 */
  	function getDesOrdersOrganization($user, $order_id, $debug = false) {

		$this->unbindModel(array('belongsTo' => array('Organization', 'De', 'Order')));

		$options = array();
		$options['conditions'] = array('DesOrdersOrganization.organization_id' => $user->organization['Organization']['id'],
									   'DesOrdersOrganization.order_id' => $order_id);
		if(!empty($user->des_id))
			$options['conditions'] += array('DesOrdersOrganization.des_id' => $user->des_id);	    								   	   

		$options['recursive'] = 1;								
		$desOrdersOrganizationResults = $this->find('first', $options);
		
		if($debug) {
			echo "<pre>DesOrdersOrganization::getDesOrdersOrganization() \n";
			print_r($options);
			print_r($desOrdersOrganizationResults);
			echo "</pre>";
		}
				
		return $desOrdersOrganizationResults;
		
	}

	public function afterFind($results, $primary = true) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if (isset($val['DesOrdersOrganization']['data'])) {
					if(!empty($val['DesOrdersOrganization']['data']) && $val['DesOrdersOrganization']['data']!='0000-00-00') 
						$results[$key]['DesOrdersOrganization']['daysToEndConsegna'] = $this->utilsCommons->dayDiffToDate(date("Y-m-d"),$val['DesOrdersOrganization']['data']);
					else
						$results[$key]['DesOrdersOrganization']['daysToEndConsegna'] = '';
				}
			}
		}
		return $results;
	}	
	  
	public $validate = array(
		'des_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'des_supplier_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'order_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
	
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
			'Order' => array(
					'className' => 'Order',
					'foreignKey' => 'order_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'DesOrder' => array(
					'className' => 'DesOrder',
					'foreignKey' => 'des_order_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
	);		
}