<?php
App::uses('AppController', 'Controller');

class PdfCartsController extends AppController {

    public function beforeFilter() {
        //$this->ctrlHttpReferer();

        parent::beforeFilter();
    }

    public function index() {

		$debug = false;
	
        $results = [];
        $user_id = $this->user->get('id');
        
        /*
         * filtri di ricerca
         */
        $years = $this->PdfCart->getListYears($this->user, $user_id); 
        $supplier_organizations = $this->PdfCart->getListSuppliers($this->user, $user_id); 
        $this->set('years', $years);
        $this->set('supplier_organizations', $supplier_organizations);
        
        $supplier_organization_id = $this->request->params['pass']['supplier_organization_id'];
        $year_id = $this->request->params['pass']['year_id'];
        if(empty($year_id))
            $year_id = date('Y');
        $this->set('supplier_organization_id', $supplier_organization_id);
        $this->set('year_id', $year_id);

		if(!empty($supplier_organization_id) && !empty($year_id)) {
			
			App::import('Model', 'StatDelivery');
			$StatDelivery = new StatDelivery;

			App::import('Model', 'StatCart');
			$StatCart = new StatCart;
		
			$StatCart->unbindModel(['belongsTo' => ['User']]);
		
			$options = [];
			$options['conditions'] = ['StatDelivery.organization_id' => $this->user->organization['Organization']['id'],
									  'YEAR(StatDelivery.data)' => $year_id];
			$options['recursive'] = 1;
			$options['order'] = ['StatDelivery.data'];
			$statDeliveryResults = $StatDelivery->find('all', $options);

			self::d($options['conditions'], $debug);
			// self::d($statDeliveryResults, $debug);
			
			if($statDeliveryResults)
			foreach($statDeliveryResults as $numResult => $statOrderResults) {
			
				self::d($statOrderResults, $debug);
			
				if(empty($statOrderResults['StatOrder'])) {
					unset($statDeliveryResults[$numResult]);
				}
				else
				foreach($statOrderResults['StatOrder'] as $numResult2 => $statOrderResult) {
					
					$options = [];
					$options['conditions'] = ['StatCart.organization_id' => $this->user->organization['Organization']['id'],
											  'StatCart.user_id' => $user_id,
											  'StatOrder.id' =>  $statOrderResult['id']];
					if(!empty($supplier_organization_id))
						$options['conditions'] += ['StatOrder.supplier_organization_id' => $supplier_organization_id];
											  
					$options['recursive'] = 1;
					$options['order'] = ['StatOrder.data_inizio'];
					$statCartResults = $StatCart->find('all', $options);
					
					self::d($options, $debug);
					// self::d($statCartResults, $debug);
					if(empty($statCartResults)) {
						/*
						 * lo user non ha acquisti per l'ordine => elimino ordine
						 */
						unset($statDeliveryResults[$numResult]['StatOrder'][$numResult2]);
						
						/*
						 * cancellati tutti gli ordini della consegna => elimino consegna
						 */	
						if(!isset($statDeliveryResults[$numResult]['StatOrder']) || empty($statDeliveryResults[$numResult]['StatOrder']))
							unset($statDeliveryResults[$numResult]);
					}
					else {
						$statDeliveryResults[$numResult]['StatOrder'][$numResult2]['StatCart'] = $statCartResults;
					}
				} // end foreach($statDeliveryResult as $statOrderResult)
			} // end foreach($statDeliveryResults as $statDeliveryResult)
		
		} // end  if(!empty($supplier_organization_id) && !empty($year_id)) 
			
		self::d($statDeliveryResults, $debug);
		
        $this->set('results', $statDeliveryResults);
        $this->layout = 'default_front_end';
        $this->layout = 'ajax';
    }
	
	/*
	 * versione che leggeva i dati da pdfCarts 
	 * ora non creo + il pdf perche' gli ordini vanno in statistiche indipendentemente se la cosegna ha tutti gli ordni chiusi
	*/
    public function index_pdf() {

        $results = [];
        $user_id = $this->user->get('id');
        
        /*
         * filtri di ricerca
         */
        
        $years = $this->PdfCart->getListYears($this->user, $user_id); 
        $supplier_organizations = $this->PdfCart->getListSuppliers($this->user, $user_id); 
        $this->set('years', $years);
        $this->set('supplier_organizations', $supplier_organizations);
        
        $supplier_organization_id = $this->request->params['pass']['supplier_organization_id'];
        $year_id = $this->request->params['pass']['year_id'];
        if(empty($year_id) && !empty($years))
            $year_id = key($years);
        $this->set('supplier_organization_id', $supplier_organization_id);
        $this->set('year_id', $year_id);

        $options = [];
        $options['conditions'] = ['PdfCart.organization_id' => (int) $this->user->organization['Organization']['id'],
                                  'PdfCart.user_id' => (int) $user_id];
        if(!empty($year_id))
            $options['conditions'] +=  ["DATE_FORMAT(PdfCart.delivery_data, '%Y')" => $year_id];
        
        $options['order'] = ['PdfCart.delivery_data' => 'desc'];
        $options['recursive'] = 0;
        $this->PdfCart->unbindModel(['belongsTo' => ['User']]);
        $results = $this->PdfCart->find('all', $options);
          
        App::import('Model', 'PdfCartsOrder');
        foreach ($results as $numResult => $result) {

            $PdfCartsOrder = new PdfCartsOrder;

            $options = [];
            $options['conditions'] = ['PdfCartsOrder.organization_id' => (int) $this->user->organization['Organization']['id'],
                                           'PdfCartsOrder.user_id' => (int) $user_id,
                                           'PdfCartsOrder.pdf_cart_id' => $result['PdfCart']['id']];
            if(!empty($supplier_organization_id))
                $options['conditions'] += ['PdfCartsOrder.supplier_organizations_id' => $supplier_organization_id];
            
            $options['recursive'] = -1;
            $pdfCartsOrderResults = $PdfCartsOrder->find('all', $options);
            
            if (!empty($pdfCartsOrderResults))
                $results[$numResult]['PdfCartsOrder'] = $pdfCartsOrderResults;
            else 
                unset($results[$numResult]);
        }
		
		self::d($results,false);
       
        $this->set('results', $results);
        $this->layout = 'default_front_end';
        $this->layout = 'ajax';
    }	
}