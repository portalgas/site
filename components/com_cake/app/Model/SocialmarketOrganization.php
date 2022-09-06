<?php
App::uses('AppModel', 'Model');

class SocialmarketOrganization extends AppModel {

    public $useTable = 'suppliers_organizations';

    public $belongsTo = array(
        'Supplier' => array(
            'className' => 'Supplier',
            'foreignKey' => 'supplier_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'CategoriesSupplier' => array(
            'className' => 'CategoriesSupplier',
            'foreignKey' => 'category_supplier_id',
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
        $options['conditions'] = ['SocialmarketOrganization.organization_id' => Configure::read('social_market_organization_id')];
        $options['order'] = ['SocialmarketOrganization.name'];
        $options['recursive'] = 1;
        // debug($options);
        $socialmarketOrganizationResults = $this->find('all', $options);

        foreach($socialmarketOrganizationResults as $numResult => $socialmarketOrganizationResult) {

            /*
             * dati Organization associato al produttore, il produttore ha un account da produttore
             */
            $owner_organization_id = $socialmarketOrganizationResult['Supplier']['owner_organization_id'];

            $options = [];
            $options['conditions'] = ['Organization.id' => $owner_organization_id];
            $options['recursive'] = -1;

            $Organization = new Organization;
            $organizationResults = $Organization->find('first', $options);

            $socialmarketOrganizationResults[$numResult]['Supplier']['Organization'] = $organizationResults['Organization'];

            /*
             * GAS assocati al produttore
             */
            $options = [];
            $options['conditions'] = ['SuppliersOrganization.supplier_id' => $socialmarketOrganizationResult['Supplier']['id'],
               'NOT' => ['SuppliersOrganization.organization_id' => [Configure::read('social_market_organization_id'), $organizationResults['Organization']['id']]]
                                    ];
            $options['recursive'] = 0;

            $SuppliersOrganization = new SuppliersOrganization;
            $SuppliersOrganization->unbindModel(['belongsTo' => ['Supplier', 'CategoriesSupplier']]);
            $suppliersOrganizationResults = $SuppliersOrganization->find('all', $options);

            $socialmarketOrganizationResults[$numResult]['Organization'] = $suppliersOrganizationResults;

        } // end foreach($socialmarketOrganizationResults as $numResult => $socialmarketOrganizationResult)

        return $socialmarketOrganizationResults;
    }
}