<?php

App::uses('AppController', 'Controller');

class MonitoringSuppliersOrganizationsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();

        if (!$this->isSuperReferente()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
    }

    public function admin_home() {

        $debug = false;

        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $options = [];
        $options['conditions'] = array('MonitoringSuppliersOrganization.organization_id' => $this->user->organization['Organization']['id']);
        $options['order'] = array('SuppliersOrganization.name');
        $options['recursive'] = 0;

        $results = $this->MonitoringSuppliersOrganization->find('all', $options);
        /*
        echo "<pre>";
        print_r($options);
        print_r($results);
        echo "<pre>";
        */        
        foreach ($results as $numResult => $result) {

            /*
             * Suppliers per l'immagine
             * */
            $Supplier = new Supplier;

            $options = [];
            $options['conditions'] = array('Supplier.id' => $result['SuppliersOrganization']['supplier_id']);
            $options['fields'] = array('Supplier.img1');
            $options['recursive'] = -1;
            $SupplierResults = $Supplier->find('first', $options);
            if (!empty($SupplierResults))
                $results[$numResult]['Supplier']['img1'] = $SupplierResults['Supplier']['img1'];

            /*
             * SuppliersOrganizationsReferent 
             */
            $conditions = array('SuppliersOrganizationsReferent.supplier_organization_id' => $result['SuppliersOrganization']['id'],
                'SuppliersOrganizationsReferent.organization_id' => (int) $this->user->organization['Organization']['id']);
            $suppliersOrganizationsReferents = $SuppliersOrganization->SuppliersOrganizationsReferent->find('all', array('conditions' => $conditions));
            if (!empty($suppliersOrganizationsReferents)) {
                foreach ($suppliersOrganizationsReferents as $ii => $suppliersOrganizationsReferent) {
                    $results[$numResult]['SuppliersOrganizationsReferent'][$ii]['User'] = $suppliersOrganizationsReferent['User'];
                    $results[$numResult]['SuppliersOrganizationsReferent'][$ii]['SuppliersOrganizationsReferent'] = $suppliersOrganizationsReferent['SuppliersOrganizationsReferent'];
                }
            } else
                $results[$numResult]['SuppliersOrganizationsReferent'] = null;
        }
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */

        $this->set(compact('results'));
    }

    public function admin_index() {

        $debug = false;

        if ($this->request->is('post') || $this->request->is('put')) {
            /*
              echo "<pre>";
              print_r($this->request->data['MonitoringSuppliersOrganization']);
              echo "</pre>";
             */

            $supplier_organization_id = $this->request->data['MonitoringSuppliersOrganization']['supplier_organization_id'];
            $mail_order_close = 'N';
            $mail_order_data_fine = $this->request->data['MonitoringSuppliersOrganization']['mail_order_data_fine'];
            
            $options = [];
            $options['conditions'] = array('MonitoringSuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
                'MonitoringSuppliersOrganization.supplier_organization_id' => $supplier_organization_id);
            $options['recursive'] = 0;
            $results = $this->MonitoringSuppliersOrganization->find('first', $options);
            if (empty($results)) {
                $results['MonitoringSuppliersOrganization']['organization_id'] = $this->user->organization['Organization']['id'];
                $results['MonitoringSuppliersOrganization']['supplier_organization_id'] = $supplier_organization_id;
            }

            $results['MonitoringSuppliersOrganization']['mail_order_close'] = $mail_order_close;
            $results['MonitoringSuppliersOrganization']['mail_order_data_fine'] = $mail_order_data_fine;
            $results['MonitoringSuppliersOrganization']['user_id'] = $this->user->id;

            if ($debug) {
                echo "<pre>";
                print_r($options['conditions']);
                print_r($results);
                echo "</pre>";
            }

            $this->MonitoringSuppliersOrganization->create();
            $this->MonitoringSuppliersOrganization->save($results);

            $this->Session->setFlash(__('MonitoringSuppliersOrganizations has been saved'));
        } // end if ($this->request->is('post') || $this->request->is('put')) 

        /*
         * get elenco produttori filtrati
         * e' sempre SuperReferente
         */
        if ($this->isSuperReferente()) {
            App::import('Model', 'SuppliersOrganization');
            $SuppliersOrganization = new SuppliersOrganization;

            $options = [];
            $options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
                'SuppliersOrganization.stato' => 'Y');
            $options['recursive'] = -1;
            $options['order'] = array('SuppliersOrganization.name');
            $ACLsuppliersOrganizationResults = $SuppliersOrganization->find('list', $options);
            $this->set('ACLsuppliersOrganization', $ACLsuppliersOrganizationResults);
        } else
            $this->set('ACLsuppliersOrganization', $this->getACLsuppliersOrganization());
    }

    /*
     * estraggo il produttore e le eventuali associazioni con MonitoringSuppliersOrganizations
     */

    public function admin_suppliers_organizations_index($supplier_organization_id = 0) {

        $options = [];
        $options['conditions'] = array('MonitoringSuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
            'MonitoringSuppliersOrganization.supplier_organization_id' => $supplier_organization_id);
        $options['recursive'] = -1;
        $results = $this->MonitoringSuppliersOrganization->find('first', $options);
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

}
