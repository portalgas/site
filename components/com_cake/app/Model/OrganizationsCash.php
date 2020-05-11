<?php
App::uses('AppModel', 'Model');


class OrganizationsCash extends AppModel {
    
    public $useTable = 'organizations';
    
    public $hasMany = [
            'CashesUser' => [
                    'className' => 'CashesUser',
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
            ],
            'User' => [
                    'className' => 'User',
                    'foreignKey' => 'organization_id',
                    'dependent' => false,
                    'conditions' => "User.block=0", // and User.email not like '%portalgas.it'
                    'fields' => '',
                    'order' => 'User.name',
                    'limit' => '',
                    'offset' => '',
                    'exclusive' => '',
                    'finderQuery' => '',
                    'counterQuery' => ''
            ]
    ];

    public function popolaCashesUser($user, $user_id, $limit_after, $limit_type, $debug=false) {

        $data = [];

        App::import('Model', 'CashesUser');
        $CashesUser = new CashesUser;
        
        $options = [];
        $options['conditions'] = ['CashesUser.organization_id' => $user->organization['Organization']['id'],
                                  'CashesUser.user_id' => $user_id];
        $options['recursive'] = -1;
        $cashesUserResults = $CashesUser->find('first', $options);
        if(!empty($cashesUserResults))
            $data['CashesUser']['id'] = $cashesUserResults['CashesUser']['id'];
            
        $data['CashesUser']['organization_id'] = $user->organization['Organization']['id'];
        $data['CashesUser']['user_id'] = $user_id;
        $data['CashesUser']['limit_type'] = $limit_type;
        $data['CashesUser']['limit_after'] = $limit_after;
        
        self::d("OrganizationsCashsController", $debug);
        self::d($data, $debug);
        
        $CashesUser->create();
        if ($CashesUser->save($data)) {
            $esito = true;
        }
        else {
            $esito = false;
        }

        return $esito;
    }
}