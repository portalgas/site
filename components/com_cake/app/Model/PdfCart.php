<?php
App::uses('AppModel', 'Model');

class PdfCart extends AppModel {

    public function getListYears($user, $user_id, $debug=false) {

		App::import('Model', 'StatCart');
        $StatCart = new StatCart;
	
		$StatCart->unbindModel(['belongsTo' => ['User', 'Article', 'StatArticlesOrder']]);
			
		$options = [];
		$options['conditions'] = ['StatCart.organization_id' => $user->organization['Organization']['id'],
							      'StatCart.user_id' => $user_id];	
		$options['fields'] = ['StatOrder.stat_delivery_year'];
		$options['group'] = ['StatOrder.stat_delivery_year'];
		$options['recursive'] = 1;
		$statCartResults = $StatCart->find('all', $options);								  

		self::d($options['conditions'], $debug);
		self::d($statCartResults, $debug);
		
        $newResults = [];
        foreach($statCartResults as $statCartResult) {
            $newResults[$statCartResult['StatOrder']['stat_delivery_year']] = $statCartResult['StatOrder']['stat_delivery_year'];
        }
		self::d($newResults, $debug);
        
        return $newResults;
    }
    
    public function getListSuppliers($user, $user_id, $debug=false) {

		App::import('Model', 'StatOrder');
        $StatOrder = new StatOrder;
		
		$options = [];
		$options['conditions'] = ['StatOrder.organization_id' => $user->organization['Organization']['id']];	
		$options['fields'] = ['StatOrder.supplier_organization_id', 'StatOrder.supplier_organization_name'];
		$options['group'] = ['StatOrder.supplier_organization_id'];
		$options['order'] = ['StatOrder.supplier_organization_name'];
		$options['recursive'] = -1;
		$statOrderResults = $StatOrder->find('all', $options);								  

		self::d($statOrderResults, $debug);
		
        $newResults = [];
        foreach($statOrderResults as $statOrderResult) {
            $newResults[$statOrderResult['StatOrder']['supplier_organization_id']] = $statOrderResult['StatOrder']['supplier_organization_name'];
        }
		self::d($newResults, $debug);

        return $newResults;
    }
	
	/*
	 * versione che leggeva i dati da pdfCarts 
	 * ora non creo + il pdf perche' gli ordini vanno in statistiche indipendentemente se la cosegna ha tutti gli ordni chiusi
	*/	
    public function getListYears_pdf($user, $user_id, $debug=false) {

        $sql = "SELECT DATE_FORMAT(PdfCart.delivery_data, '%Y') as delivery_data 
                FROM ".Configure::read('DB.prefix')."pdf_carts PdfCart
                WHERE PdfCart.organization_id = ".$user->organization['Organization']['id']." 
                    and PdfCart.user_id = ".$user_id."
                group by DATE_FORMAT(PdfCart.delivery_data, '%Y') order by PdfCart.delivery_data";
        self::d($sql, $debug);
        $results = $this->query($sql);
        
        $newResults = [];
        foreach($results as $result) {
            $newResults[$result[0]['delivery_data']] = $result[0]['delivery_data'];
        }
        
        return $newResults;
    }
    
	/*
	 * versione che leggeva i dati da pdfCarts 
	 * ora non creo + il pdf perche' gli ordini vanno in statistiche indipendentemente se la cosegna ha tutti gli ordni chiusi
	*/	
    public function getListSuppliers_pdf($user, $user_id, $debug=false) {

        $sql = "SELECT PdfOrder.supplier_organizations_name as supplier_organizations_name, 
                       PdfOrder.supplier_organizations_id as supplier_organizations_id 
                FROM ".Configure::read('DB.prefix')."pdf_carts_orders PdfOrder 
                WHERE PdfOrder.organization_id = ".$user->organization['Organization']['id']." 
                    and PdfOrder.user_id = ".$user_id."
                group by PdfOrder.supplier_organizations_name order by PdfOrder.supplier_organizations_name";
        self::d($sql, $debug);
        $results = $this->query($sql);
        
        $newResults = [];
        foreach($results as $result) {
            $newResults[$result['PdfOrder']['supplier_organizations_id']] = $result['PdfOrder']['supplier_organizations_name'];
        } 
        
        return $newResults;
    }
    
    var $belongsTo = [
        'User' => [
            'className' => 'User',
            'foreignKey' => 'user_id'
        ]
    ];
	
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

    public function beforeSave($options = []) {
        if (!empty($this->data['PdfCart']['delivery_importo']))
            $this->data['PdfCart']['delivery_importo'] = $this->importoToDatabase($this->data['PdfCart']['delivery_importo']);

        return true;
    }
}
