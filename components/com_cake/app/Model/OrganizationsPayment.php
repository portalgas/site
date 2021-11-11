<?php
App::uses('AppModel', 'Model');

class OrganizationsPayment extends AppModel {

    public $useTable = 'organizations';

    /*
     * verifica se i dati di pagamento del GAS sono completi
     */
    public function isPaymentComplete($user) {
        
        $options = [];
        $options['conditions'] = ['OrganizationsPayment.id' => $user->organization['Organization']['id'],
								  'DATE(OrganizationsPayment.created) <= CURDATE() - INTERVAL '.Configure::read('GGOrganizationsPayment').' DAY'];
        $options['fields'] = ['OrganizationsPayment.paramsPay'];
        $options['recursive'] = -1;

        $results = $this->find('first', $options);
		if(empty($results))
			return true;
		
		$paramsPay = json_decode($results['OrganizationsPayment']['paramsPay'], true);
		
		self::d($options);
		self::d($results);
		self::d($paramsPay);
	
        if(empty($paramsPay['payMail']) || 
            empty($paramsPay['payContatto']) ||
            empty($paramsPay['payIntestatario']) ||
            empty($paramsPay['payIndirizzo']) ||
            empty($paramsPay['payCap']) ||
            empty($paramsPay['payCitta']) ||
            empty($paramsPay['payProv']) ||
            empty($paramsPay['payCf']))
               return false;
        else
            return true;
    }
    
    public function hasMgs($user) {
        
		$msg = '';
		
        $options = [];
        $options['conditions'] = ['OrganizationsPayment.id' => $user->organization['Organization']['id'],
								  'OrganizationsPayment.hasMsg' => 'Y'];
        $options['fields'] = ['OrganizationsPayment.msgText'];
        $options['recursive'] = -1;

        $results = $this->find('first', $options);
		if(!empty($results)) {
			$msg = trim($results['OrganizationsPayment']['msgText']);
		}
		return $msg;
    }
	
    public $hasMany = [
            'User' => [
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
            ]
    ];
}
