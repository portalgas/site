<?php
App::uses('AppModel', 'Model');


class OrganizationsPayment extends AppModel {

    /*
     * verifica se i dati di pagamento del GAS sono completi
     */
    public function isPaymentComplete($user) {
        
        $options = [];
        $options['conditions'] = array('OrganizationsPayment.id' => $user->organization['Organization']['id'],
										'DATE(OrganizationsPayment.created) <= CURDATE() - INTERVAL '.Configure::read('GGOrganizationsPayment').' DAY');
        $options['fields'] = array('OrganizationsPayment.paramsPay');
        $options['recursive'] = -1;

        $results = $this->find('first', $options);
		if(empty($results))
			return true;
		
	$paramsPay = json_decode($results['OrganizationsPayment']['paramsPay'], true);
 	/*
        echo "<pre>";
        print_r($options);
	print_r($results);
	print_r($paramsPay);
        echo "</pre>";
	*/
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
    
    public $useTable = 'organizations';

    public $hasMany = array(
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
