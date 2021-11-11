<?php
App::uses('AppModel', 'Model');

class ProdGasPromotionsUserGasManager extends AppModel {

    public $useTable = 'prod_gas_promotion';

    /*
     * $user->organization['Organization']['id']   organization_id del produttore
     * $prod_gas_promotion_id filtro per promozione 
     * $organization_id       filtro per GAS
     * $user_id				  filtro per utente
     */
    public function getCartOrderUsers($user, $prod_gas_promotion_id, $organization_id=0, $user_id=0, $where=[], $debug=false) {
    
        $debug=false;
    
        App::import('Model', 'ProdGasPromotion');
        $ProdGasPromotion = new ProdGasPromotion;

        App::import('Model', 'Cart');
      
        $organization_id=0; // filtra per la promozione per il GAS passato
        $results = $ProdGasPromotion->getProdGasPromotion($this->user, $prod_gas_promotion_id, $organization_id, $debug);
        // debug($results); 
        foreach($results['ProdGasPromotionsOrganization'] as $numResult => $prodGasPromotionsOrganization) {
            
            // debug($prodGasPromotionsOrganization);

            $organization_id = $results['ProdGasPromotion']['organization_id'];
            $gas_organization_id = $prodGasPromotionsOrganization['ProdGasPromotionsOrganization']['organization_id'];
            $prod_gas_promotion_id = $prodGasPromotionsOrganization['ProdGasPromotionsOrganization']['prod_gas_promotion_id'];
            $options = [];
            $options['conditions'] = ['Cart.organization_id' => $organization_id, 
                                      'Cart.order_id' => $prod_gas_promotion_id,
                                      'User.organization_id' => $gas_organization_id
                                  ];
            $options['recursive'] = 1;
            $options['order'] = ['User.name' => 'asc'];

            $Cart = new Cart;
            $Cart->unbindModel(['belongsTo' => ['Order', 'User']]);
            $belongsTo = ['className' => 'User',
                          'foreignKey' => 'user_id',
                          'conditions' => 'User.block = 0',
                          'fields' => '',
                          'order' => 'User.name'];
            $Cart->bindModel(['belongsTo' => ['User' => $belongsTo]]);  
            $cartResults = $Cart->find('all', $options);  
            // debug($Cart);  
            // debug($options);  
            // debug($cartResults);// exit; 
            $results['ProdGasPromotionsOrganization'][$numResult]['Cart'] = $cartResults;

            /*
             * recupero i dati dell'organization
             */
            foreach($results['Organization'] as $numResult2 => $organization) {
                if($gas_organization_id==$organization['Organization']['id']) {
                  $results['ProdGasPromotionsOrganization'][$numResult]['Organization'] = $organization['Organization'];
                  break;
                }
            }

        }

        return $results;
	   }
}