<?php

App::uses('AppController', 'Controller');

class PdfCartsController extends AppController {

    public function beforeFilter() {
        //$this->ctrlHttpReferer();

        parent::beforeFilter();
    }

    public function index() {

        $results = array();
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

        $options = array();
        $options['conditions'] = array('PdfCart.organization_id' => (int) $this->user->organization['Organization']['id'],
                                       'PdfCart.user_id' => (int) $user_id);
        if(!empty($year_id))
            $options['conditions'] += array("DATE_FORMAT(PdfCart.delivery_data, '%Y')" => $year_id);
        
        $options['order'] = array('PdfCart.delivery_data' => 'desc');
        $options['recursive'] = 0;
        $this->PdfCart->unbindModel(array('belongsTo' => array('User')));
        $results = $this->PdfCart->find('all', $options);
          
        App::import('Model', 'PdfCartsOrder');
        foreach ($results as $numResult => $result) {

            $PdfCartsOrder = new PdfCartsOrder;

            $options = array();
            $options['conditions'] = array('PdfCartsOrder.organization_id' => (int) $this->user->organization['Organization']['id'],
                                           'PdfCartsOrder.user_id' => (int) $user_id,
                                           'PdfCartsOrder.pdf_cart_id' => $result['PdfCart']['id']);
            if(!empty($supplier_organization_id))
                $options['conditions'] += array('PdfCartsOrder.supplier_organizations_id' => $supplier_organization_id);
            
            $options['recursive'] = -1;
            $pdfCartsOrderResults = $PdfCartsOrder->find('all', $options);
            
            if (!empty($pdfCartsOrderResults))
                $results[$numResult]['PdfCartsOrder'] = $pdfCartsOrderResults;
            else 
                unset($results[$numResult]);
        }

        
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
        $this->set('results', $results);
        $this->layout = 'default_front_end';
        $this->layout = 'ajax';
    }

}
