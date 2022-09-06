<?php
App::uses('AppModel', 'Model');

class SocialmarketOrganization extends AppModel {

    public $tablePrefix = false;

    public $belongsTo = array(
        'Organization' => array(
            'className' => 'Organization',
            'foreignKey' => 'organization_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'SuppliersOrganization' => array(
            'className' => 'SuppliersOrganization',
            'foreignKey' => 'supplier_organization_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

    /*
     * estrae tutti i produttori associati all'organization SocialMarket
     */
    public function getSuppliers($user, $debug=false) {

        App::import('Model', 'Organization');

        App::import('Model', 'SuppliersOrganization');

        /*
         * tutti i produttori associati all'organization SocialMarket
         */
        $options = [];
        $options['conditions'] = ['SuppliersOrganization.organization_id' => Configure::read('social_market_organization_id')];
        $options['order'] = ['SuppliersOrganization.name'];
        $options['recursive'] = 1;
        // debug($options);
        $SuppliersOrganization = new SuppliersOrganization;
        $suppliersOrganizationResults = $SuppliersOrganization->find('all', $options);

        foreach($suppliersOrganizationResults as $numResult => $suppliersOrganizationResult) {

            /*
             * dati Organization associato al produttore, il produttore ha un account da produttore
             */
            $owner_organization_id = $suppliersOrganizationResult['Supplier']['owner_organization_id'];

            $options = [];
            $options['conditions'] = ['Organization.id' => $owner_organization_id];
            $options['recursive'] = -1;

            $Organization = new Organization;
            $organizationResults = $Organization->find('first', $options);

            $suppliersOrganizationResults[$numResult]['Supplier']['Organization'] = $organizationResults['Organization'];

            /*
             * GAS gia' assocati al produttore => non saranno in SocialMarket per conflitti d'interesse
             */
            $options = [];
            $options['conditions'] = ['SuppliersOrganization.supplier_id' => $suppliersOrganizationResult['Supplier']['id'],
               'NOT' => ['SuppliersOrganization.organization_id' => [Configure::read('social_market_organization_id'), $organizationResults['Organization']['id']]]
                                    ];
            $options['recursive'] = 0;

            $SuppliersOrganization = new SuppliersOrganization;
            $SuppliersOrganization->unbindModel(['belongsTo' => ['Supplier', 'CategoriesSupplier']]);
            $suppliersOrganizationResults = $SuppliersOrganization->find('all', $options);

            $suppliersOrganizationResults[$numResult]['Organization'] = $suppliersOrganizationResults;

            /*
             * GAS assocati al SocialMarket (in base alla modalita' di consegna)
             */
            $options = [];
            $options['conditions'] = ['supplier_organization_id' => $suppliersOrganizationResult['SuppliersOrganization']['id']];
            $options['recursive'] = 0;

            $socialmarketOrganizationResults = $this->find('all', $options);

            $suppliersOrganizationResults[$numResult]['SocialmarketOrganization'] = $socialmarketOrganizationResults;

        } // end foreach($socialmarketOrganizationResults as $numResult => $socialmarketOrganizationResult)

        return $suppliersOrganizationResults;
    }
}