<?php
App::uses('AppModel', 'Model');

class Organization extends AppModel {

	public function getOrganization($organization_id, $debug=false) {

       	$options = [];
        $options['conditions'] = ['Organization.id' => $organization_id];
        $options['recursive'] = -1;
        $organizationResults = $this->find('first', $options);
	
        $paramsConfig = json_decode($organizationResults['Organization']['paramsConfig'], true);
        $paramsFields = json_decode($organizationResults['Organization']['paramsFields'], true);

        $organizationResults['Organization'] += $paramsConfig;
        $organizationResults['Organization'] += $paramsFields;
	
		return $organizationResults;
	}
	
	public function getCashLimit($cashLimit = '') {

		$results = ['LIMIT-NO' => __('LIMIT-NO'), // 'Nessun limite', 
					'LIMIT-CASH' => __('LIMIT-CASH'), // 'Limite credito in cassa', 
					'LIMIT-CASH-AFTER' => __('LIMIT-CASH-AFTER'), // 'Limite superato credito in cassa di', 
					'LIMIT-CASH-USER' => __('LIMIT-CASH-USER')]; // 'Limite per ogni gasista'
			
		if(!empty($cashLimit) && isset($results[$cashLimit]))
			$results = $results[$cashLimit];
					 
		return $results;	
	}
		
	/*
	 * ctrl se il template scelto ha i ruoli corretti
	 */
    public function validateData($data, $debug=false) {
   
		App::import('Model', 'Template');
		$Template = new Template;
		
		$options = [];
		$options['conditions'] = ['Template.id' => $data['Organization']['template_id']];
		$options['recursive'] = -1;
		$templateResults = $Template->find('first', $options);
		if(empty($templateResults))
			self::x("Organization::validateData() templateResults empty!");
			
        if ($data['Organization']['j_group_registred'] == null)
            $data['Organization']['j_group_registred'] = 0;

		if ($data['Organization']['j_page_category_id'] == null)
            $data['Organization']['j_page_category_id'] = 0;
				
		switch ($data['Organization']['type']) {
            case 'GAS':
					$data['Organization']['prodSupplierOrganizationId'] = 0;
					
					/*
					 * cassiere
					 */ 
					if($templateResults['Template']['hasCassiere'] == 'Y') {
						$data['Organization']['hasUserGroupsCassiere'] = 'Y';
					}
					else {
						$data['Organization']['hasUserGroupsCassiere'] = 'N';
					}

					/*
					 * tesoriere
					 */ 					
					if($templateResults['Template']['hasTesoriere'] == 'Y') {
						$data['Organization']['hasUserGroupsReferentTesoriere'] = 'Y';
						$data['Organization']['hasUserGroupsTesoriere'] = 'Y';				
					}
					else {
						$data['Organization']['hasUserGroupsReferentTesoriere'] = 'N';
						$data['Organization']['hasUserGroupsTesoriere'] = 'N';				
					}					
                break;
            case 'PROD':
				/*
				 * configurazioni
				 */
				$data['Organization']['hasBookmarsArticles'] = 'Y';
				$data['Organization']['hasArticlesOrder'] = 'Y';

				$data['Organization']['hasTrasport'] = 'N';
				$data['Organization']['hasCostMore'] = 'N';
				$data['Organization']['hasCostLess'] = 'N';
				$data['Organization']['hasValidate'] = 'N';
				$data['Organization']['hasStoreroom'] = 'N';
				$data['Organization']['hasStoreroomFrontEnd'] = 'N';
				$data['Organization']['hasDes'] = 'N';
				$data['Organization']['hasDesReferentAllGas'] = 'N';
				$data['Organization']['hasUsersRegistrationFE'] = 'N';
				
				/*
				 * ruoli
				 */
				$data['Organization']['hasUserGroupsCassiere'] = 'Y';
				$data['Organization']['hasUserGroupsReferentTesoriere'] = 'N';
				$data['Organization']['hasUserGroupsStoreroom'] = 'Y';

				$data['Organization']['hasUserGroupsTesoriere'] = 'Y';

                break;
            default:
				if(!isset($data['Organization']['type']))
					$type = '';
				else 
					$type = $data['Organization']['type'];
                self::d("Organization::validateData() valore type non valido ".$type);			
                return;
                break;			
		}

        self::d($data['Organization'], $debug);
        
        return $data;
    }
	
//	update  k_organizations set paramsConfig = CONCAT(SUBSTRING(paramsConfig, 1, LENGTH(paramsConfig)-1), ',"cashLimit":"LIMIT-NO","limitCashAfter":"0,00"}');
	
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'localita' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'cap' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'provincia' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'mail' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'www' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $belongsTo = [
		'Template' => [
			'className' => 'Template',
			'foreignKey' => 'template_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		]		
	];
	
	public $hasMany = array(
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'organization_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'organization_id',
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
