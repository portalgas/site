<?php
App::uses('AppModel', 'Model');


class OrganizationsCash extends AppModel {
    
    public $useTable = 'organizations';
	
    public $hasMany = array(
            'CashesUser' => array(
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
            ),
            'User' => array(
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
            )
    );
}