<?php

App::uses('AppModel', 'Model');

class PdfCart extends AppModel {

    var $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id'
        )
    );

    public function getListYears($user, $user_id, $debug=false) {

        $sql = "SELECT DATE_FORMAT(PdfCart.delivery_data, '%Y') as delivery_data 
                FROM ".Configure::read('DB.prefix')."pdf_carts PdfCart
                WHERE PdfCart.organization_id = ".$user->organization['Organization']['id']." 
                    and PdfCart.user_id = ".$user_id."
                group by DATE_FORMAT(PdfCart.delivery_data, '%Y') order by PdfCart.delivery_data";
        if($debug)
            echo '<br />'.$sql;
        $results = $this->query($sql);
        
        $newResults = array();
        foreach($results as $result) {
            $newResults[$result[0]['delivery_data']] = $result[0]['delivery_data'];
        }
        
        return $newResults;
    }
    
    public function getListSuppliers($user, $user_id, $debug=false) {

        $sql = "SELECT PdfOrder.supplier_organizations_name as supplier_organizations_name, 
                       PdfOrder.supplier_organizations_id as supplier_organizations_id 
                FROM ".Configure::read('DB.prefix')."pdf_carts_orders PdfOrder 
                WHERE PdfOrder.organization_id = ".$user->organization['Organization']['id']." 
                    and PdfOrder.user_id = ".$user_id."
                group by PdfOrder.supplier_organizations_name order by PdfOrder.supplier_organizations_name";
        if($debug)
            echo '<br />'.$sql;      
        $results = $this->query($sql);
        
        $newResults = array();
        foreach($results as $result) {
            $newResults[$result['PdfOrder']['supplier_organizations_id']] = $result['PdfOrder']['supplier_organizations_name'];
        } 
        
        return $newResults;
    }
    
    public function afterFind($results, $primary = false) {
        foreach ($results as $key => $val) {
            if (!empty($val)) {
                if (isset($val['PdfCart']['delivery_importo'])) {
                    $results[$key]['PdfCart']['delivery_importo_'] = number_format($val['PdfCart']['delivery_importo'], 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
                    $results[$key]['PdfCart']['delivery_importo_e'] = $results[$key]['PdfCart']['delivery_importo_'] . ' &euro;';
                }
            }
        }
        return $results;
    }

    public function beforeSave($options = array()) {
        if (!empty($this->data['PdfCart']['delivery_importo']))
            $this->data['PdfCart']['delivery_importo'] = $this->importoToDatabase($this->data['PdfCart']['delivery_importo']);

        return true;
    }
}
