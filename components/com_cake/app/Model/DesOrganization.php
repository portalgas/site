<?php
App::uses('AppModel', 'Model');


class DesOrganization extends AppModel {

	/*
	 * estraggo i GAS del DES
	 * 		non li filtro per $user->des_id perche' potrei non aver selezionato il DES ma operare su un ordine condiviso
	 */
	public function getOrganizations($user, $conditions, $debug = false) {
		
		/*
		 * estraggo des_id
		 */
   		App::import('Model', 'DesSupplier');
  		$DesSupplier = new DesSupplier();
   		
   		$options = [];
   		$options['conditions'] = [];
		if(isset($conditions['DesSupplier.id']))
			$options['conditions'] += array('DesSupplier.id' => $conditions['DesSupplier.id']);
   		if(!empty($user->des_id))
	   		$options['conditions'] += array('DesSupplier.des_id' => $user->des_id);

   		$options['fields'] = array('DesSupplier.des_id');
   		$options['recursive'] = -1;
   		
   		$results = $DesSupplier->find('first', $options);
   		if(empty($results))
   			return $results;
   			
   		$des_id = $results['DesSupplier']['des_id'];
   		if($debug)  echo '<br />DesOrganization->getOrganizations - des_id '.$des_id; 
   		
		/*
		 * estraggo Organizations
		 */
   		App::import('Model', 'DesOrganization');
  		$DesOrganization = new DesOrganization();
   		
   		$options = [];
   		$options['conditions'] = array('DesOrganization.des_id' => $des_id);
   		$options['recursive'] = 0;
   		
   		$results = $DesOrganization->find('all', $options);
   		 
		if($debug) {
			echo "<pre>DesOrganization->getOrganizations ";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}   		

		return $results;
	}

	/*
	 * tutti i DES di cui fa parte un GAS
	 * gestisco che non abbia ancora scelto quale utilizza ($user->des_id) ma li estraggo tutti
	 */
	public function getAllDes($user, $debug = false) {
		
   		$options = [];
   		$options['conditions'] = array('DesOrganization.organization_id' => $user->organization['Organization']['id']);
   		$options['recursive'] = -1;
   		
   		$results = $this->find('all', $options);
   		 
		if($debug) {
			echo "<pre>DesOrganization->getAllDes \n ";
			print_r($results);
			echo "</pre>";
		}   		

		return $results;
	}

	
	public $validate = array(
		'des_id' => array(
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
	);

	public $belongsTo = array(
			'Organization' => array(
					'className' => 'Organization',
					'foreignKey' => 'organization_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'De' => array(
					'className' => 'De',
					'foreignKey' => 'des_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			)
	);	
}