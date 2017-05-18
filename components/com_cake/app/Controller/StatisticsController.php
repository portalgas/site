<?php

App::uses('AppController', 'Controller');

class StatisticsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();

        /* ctrl ACL */
        if (!$this->isManager()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */
    }

    public function admin_index() {

        $stat_delivery_id = 0;
        $supplier_organization_id = 0;
        $stat_order_id = 0;

        $mese_da = '01';
        $anno_da = date('Y');
        $mese_a = date('m');
        $anno_a = date('Y');

        $date_range = 'DATE_FORMAT(StatDelivery.data, "%Y-%m") BETWEEN \'' . $anno_da . '-' . $mese_da . '\' AND \'' . $anno_a . '-' . $mese_a . '\'';

        App::import('Model', 'StatDelivery');
        $StatDelivery = new StatDelivery;

        $options = array();
        $options['conditions'] = array('StatDelivery.organization_id' => (int) $this->user->organization['Organization']['id'],
            $date_range);
        $options['fields'] = array('id', 'luogoData');
        $options['order'] = array('data ASC');
        $options['recursive'] = -1;
        $statDeliveries = $StatDelivery->find('list', $options);
        if (empty($statDeliveries)) {
            /*
             * anno precedente
             */
            $mese_da = '01';
            $anno_da--;
            $mese_a = '12';
            $anno_a--;

            $date_range = 'DATE_FORMAT(StatDelivery.data, "%Y-%m") BETWEEN \'' . $anno_da . '-' . $mese_da . '\' AND \'' . $anno_a . '-' . $mese_a . '\'';

            $options = array();
            $options['conditions'] = array('StatDelivery.organization_id' => (int) $this->user->organization['Organization']['id'],
                $date_range);
            $options['fields'] = array('id', 'luogoData');
            $options['order'] = array('data ASC');
            $options['recursive'] = -1;
            $statDeliveries = $StatDelivery->find('list', $options);
        }

        if (empty($statDeliveries)) {
            $this->Session->setFlash(__('NotFoundDeliveries'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $this->set(compact('statDeliveries'));

        $this->set('mese_da', $mese_da);
        $this->set('anno_da', $anno_da);
        $this->set('mese_a', $mese_a);
        $this->set('anno_a', $anno_a);

        $this->set('stat_delivery_id', $stat_delivery_id);
        $this->set('supplier_organization_id', $supplier_organization_id);
        $this->set('stat_order_id', $stat_order_id);

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $options = array();
        $options['conditions'] = array('SuppliersOrganization.organization_id = ' . (int) $this->user->organization['Organization']['id'],
            'SuppliersOrganization.stato' => 'Y');
        $options['order'] = array('SuppliersOrganization.name');
        $options['recursive'] = -1;
        $suppliersOrganization = $SuppliersOrganization->find('list', $options);
        $this->set(compact('suppliersOrganization'));
    }

    public function admin_box_stat_deliveries($mese_da, $anno_da, $mese_a, $anno_a) {

        App::import('Model', 'StatDelivery');
        $StatDelivery = new StatDelivery;

        $date_range = 'DATE_FORMAT(StatDelivery.data, "%Y-%m") BETWEEN \'' . $anno_da . '-' . $mese_da . '\' AND \'' . $anno_a . '-' . $mese_a . '\'';
        $options = array();
        $options['conditions'] = array('StatDelivery.organization_id' => (int) $this->user->organization['Organization']['id'],
            $date_range);
        $options['fields'] = array('id', 'luogoData');
        $options['order'] = array('data ASC');
        $options['recursive'] = -1;
        $statDeliveries = $StatDelivery->find('list', $options);
        $this->set(compact('statDeliveries'));

        $this->layout = 'ajax';
    }

    public function admin_box_stat_orders($stat_delivery_id = 0, $supplier_organization_id = 0) {
        if (empty($stat_delivery_id) && empty($supplier_organization_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'StatOrder');
        $StatOrder = new StatOrder;

        $options['conditions'] = array('StatOrder.organization_id' => (int) $this->user->organization['Organization']['id']);
        if (!empty($stat_delivery_id))
            $options['conditions'] += array('StatOrder.stat_delivery_id' => $stat_delivery_id);
        if (!empty($supplier_organization_id))
            $options['conditions'] += array('StatOrder.supplier_organization_id' => $supplier_organization_id);
        $options['order'] = 'data_inizio ASC';
        $options['recursive'] = 1;
        $results = $StatOrder->find('all', $options);
        $statOrders = array();
        if (!empty($results)) {
            foreach ($results as $result)
                $statOrders[$result['StatOrder']['id']] = $result['SuppliersOrganization']['name'] . ' - dal ' . $result['StatOrder']['data_inizio_'] . ' al ' . $result['StatOrder']['data_fine_'];
        }

        $this->set(compact('statOrders'));

        $this->set('stat_delivery_id', $stat_delivery_id);
        $this->set('supplier_organization_id', $supplier_organization_id);

        $this->layout = 'ajax';
    }

    /*
     * tab 1
     */

    public function admin_tab_deliveries($mese_da, $anno_da, $mese_a, $anno_a, $stat_delivery_id = 0, $supplier_organization_id = 0, $stat_order_id = 0) {

        $data_da = $anno_da . '-' . $mese_da . '-01';
        $data_a = $anno_a . '-' . $mese_a . '-31';

        $sql = "
					SELECT
						sum(StatOrder.importo) as tot_importo, 
						StatDelivery.data, StatDelivery.luogo 
					FROM
						" . Configure::read('DB.prefix') . "stat_deliveries AS StatDelivery,
						" . Configure::read('DB.prefix') . "stat_orders AS StatOrder
					WHERE
						StatDelivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatDelivery.id = StatOrder.stat_delivery_id
						AND (StatDelivery.data BETWEEN '$data_da' AND '$data_a') ";
        if (!empty($stat_delivery_id))
            $sql .= " AND StatDelivery.id = " . $stat_delivery_id;
        if (!empty($supplier_organization_id))
            $sql .= " AND StatOrder.supplier_organization_id = " . $supplier_organization_id;
        if (!empty($stat_order_id))
            $sql .= " AND StatOrder.id = " . $stat_order_id;

        $sql .= " GROUP BY 
						StatDelivery.data, StatDelivery.luogo   
					ORDER BY tot_importo desc";
        // echo '<br />'.$sql;
        $results = $this->Statistic->query($sql);
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

    /*
     * tab 2
     */

    public function admin_tab_orders($mese_da, $anno_da, $mese_a, $anno_a, $stat_delivery_id = 0, $supplier_organization_id = 0, $stat_order_id = 0) {

        $data_da = $anno_da . '-' . $mese_da . '-01';
        $data_a = $anno_a . '-' . $mese_a . '-31';

        $sql = "
					SELECT 
						StatDelivery.id, StatDelivery.luogo, StatDelivery.data, 
						StatOrder.data_inizio, StatOrder.data_fine, StatOrder.importo, 
						StatOrder.tesoriere_doc1, StatOrder.tesoriere_fattura_importo, StatOrder.tesoriere_importo_pay, StatOrder.tesoriere_data_pay, 
						StatOrder.supplier_organization_id, StatOrder.supplier_organization_name, StatOrder.supplier_img1 
					FROM 
						" . Configure::read('DB.prefix') . "stat_deliveries AS StatDelivery, 
						" . Configure::read('DB.prefix') . "stat_orders AS StatOrder 
					WHERE 
						StatDelivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatDelivery.id = StatOrder.stat_delivery_id
						AND (StatDelivery.data BETWEEN '$data_da' AND '$data_a') ";
        if (!empty($stat_delivery_id))
            $sql .= " AND StatDelivery.id = " . $stat_delivery_id;
        if (!empty($supplier_organization_id))
            $sql .= " AND StatOrder.supplier_organization_id = " . $supplier_organization_id;
        if (!empty($stat_order_id))
            $sql .= " AND StatOrder.id = " . $stat_order_id;
        $sql .= " ORDER BY 
						StatDelivery.id, StatOrder.supplier_organization_name, StatOrder.data_inizio, StatOrder.data_fine, StatOrder.importo				
					";
        // echo '<br />'.$sql;
        $results = $this->Statistic->query($sql);
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

    /*
     * tab 2
     */

    public function admin_tab_suppliers($mese_da, $anno_da, $mese_a, $anno_a, $stat_delivery_id = 0, $supplier_organization_id = 0, $stat_order_id = 0) {

        $data_da = $anno_da . '-' . $mese_da . '-01';
        $data_a = $anno_a . '-' . $mese_a . '-31';

        $sql = "
					SELECT 
						sum( StatOrder.importo) as tot_importo, 
						StatOrder.supplier_organization_id, StatOrder.supplier_organization_name, StatOrder.supplier_img1 
					FROM 
						" . Configure::read('DB.prefix') . "stat_deliveries AS StatDelivery, 
						" . Configure::read('DB.prefix') . "stat_orders AS StatOrder
					WHERE 
						StatDelivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatDelivery.id = StatOrder.stat_delivery_id
						AND (StatDelivery.data BETWEEN '$data_da' AND '$data_a')";
        if (!empty($stat_delivery_id))
            $sql .= " AND StatDelivery.id = " . $stat_delivery_id;
        if (!empty($supplier_organization_id))
            $sql .= " AND StatOrder.supplier_organization_id = " . $supplier_organization_id;
        if (!empty($stat_order_id))
            $sql .= " AND StatOrder.id = " . $stat_order_id;
        $sql .= " GROUP BY 
						StatOrder.supplier_organization_name
					ORDER BY tot_importo desc";
        // echo '<br />'.$sql;
        $results = $this->Statistic->query($sql);
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

    /*
     * tab 3
     */

    public function admin_tab_users($mese_da, $anno_da, $mese_a, $anno_a, $stat_delivery_id = 0, $supplier_organization_id = 0, $stat_order_id = 0) {

        $data_da = $anno_da . '-' . $mese_da . '-01';
        $data_a = $anno_a . '-' . $mese_a . '-31';

        $sql = "
					SELECT
						StatOrder.supplier_organization_id, StatOrder.supplier_organization_name, StatOrder.supplier_img1, 
						StatArticlesOrder.name,
						User.username, User.name, User.email,
						StatCart.importo, StatCart.qta
					FROM 
						" . Configure::read('DB.prefix') . "stat_deliveries AS StatDelivery, 
						" . Configure::read('DB.prefix') . "stat_orders AS StatOrder, 
						" . Configure::read('DB.prefix') . "stat_articles_orders as StatArticlesOrder,
						" . Configure::read('DB.prefix') . "stat_carts as StatCart, 
						" . Configure::read('DB.portalPrefix') . "users as User 
					WHERE 
						StatDelivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatArticlesOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatCart.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
						AND StatDelivery.id = StatOrder.stat_delivery_id
						AND StatArticlesOrder.stat_order_id = StatOrder.id
						AND StatArticlesOrder.stat_order_id = StatCart.stat_order_id
						AND StatArticlesOrder.article_id = StatCart.article_id
						AND StatCart.user_id = User.id	
						AND (StatDelivery.data BETWEEN '$data_da' AND '$data_a') ";
        if (!empty($stat_delivery_id))
            $sql .= " AND StatDelivery.id = " . $stat_delivery_id;
        if (!empty($supplier_organization_id))
            $sql .= " AND StatOrder.supplier_organization_id = " . $supplier_organization_id;
        if (!empty($stat_order_id))
            $sql .= " AND StatOrder.id = " . $stat_order_id;
        $sql .= "  ORDER BY StatCart.importo desc, StatOrder.supplier_organization_name, StatOrder.data_inizio, StatOrder.data_fine
					";
        // echo '<br />'.$sql;
        $results = $this->Statistic->query($sql);
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

    /*
     * tab 4
     */

    public function admin_tab_users_details($mese_da, $anno_da, $mese_a, $anno_a, $stat_delivery_id = 0, $supplier_organization_id = 0, $stat_order_id = 0) {

        $data_da = $anno_da . '-' . $mese_da . '-01';
        $data_a = $anno_a . '-' . $mese_a . '-31';

        $sql = "
            SELECT
                    sum(StatCart.importo) as tot_importo, sum(StatCart.qta) as tot_qta, 
                    User.username, User.name, User.email
            FROM
                    " . Configure::read('DB.prefix') . "stat_deliveries AS StatDelivery,
                    " . Configure::read('DB.prefix') . "stat_orders AS StatOrder, 
                    " . Configure::read('DB.prefix') . "stat_articles_orders as StatArticlesOrder,
                    " . Configure::read('DB.prefix') . "stat_carts as StatCart, 
                    " . Configure::read('DB.portalPrefix') . "users as User 		
            WHERE
                    StatDelivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                    AND StatOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                    AND StatArticlesOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                    AND StatCart.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                    AND User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                    AND StatDelivery.id = StatOrder.stat_delivery_id 
                    AND StatArticlesOrder.stat_order_id = StatOrder.id
                    AND StatArticlesOrder.stat_order_id = StatCart.stat_order_id
                    AND StatArticlesOrder.article_id = StatCart.article_id
                    AND StatCart.user_id = User.id
                    AND (StatDelivery.data BETWEEN '$data_da' AND '$data_a') ";
        if (!empty($stat_delivery_id))
            $sql .= " AND StatDelivery.id = " . $stat_delivery_id;
        if (!empty($supplier_organization_id))
            $sql .= " AND StatOrder.supplier_organization_id = " . $supplier_organization_id;
        if (!empty($stat_order_id))
            $sql .= " AND StatOrder.id = " . $stat_order_id;
        $sql .= " GROUP BY 
						User.username, User.name, User.email 
					ORDER BY tot_importo desc, tot_qta desc";
        // echo '<br />'.$sql;
        $results = $this->Statistic->query($sql);
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

    /*
     * tab 5, totale importo degli articoli
     */

    public function admin_tab_articles_orders($mese_da, $anno_da, $mese_a, $anno_a, $stat_delivery_id = 0, $supplier_organization_id = 0, $stat_order_id = 0) {

        $data_da = $anno_da . '-' . $mese_da . '-01';
        $data_a = $anno_a . '-' . $mese_a . '-31';

        $sql = "
                SELECT 
                        sum(StatCart.importo) as tot_importo, sum(StatCart.qta) as tot_qta,
                        StatArticlesOrder.um, 
                        StatOrder.supplier_organization_id, StatOrder.supplier_organization_name, StatOrder.supplier_img1, 
                        StatArticlesOrder.name   
                FROM 
                        " . Configure::read('DB.prefix') . "stat_deliveries AS StatDelivery, 
                        " . Configure::read('DB.prefix') . "stat_orders AS StatOrder,
                        " . Configure::read('DB.prefix') . "stat_articles_orders as StatArticlesOrder,
                        " . Configure::read('DB.prefix') . "stat_carts as StatCart 
                WHERE 
                        StatDelivery.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND StatOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND StatArticlesOrder.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND StatCart.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND StatDelivery.id = StatOrder.stat_delivery_id 
                        AND StatArticlesOrder.stat_order_id = StatOrder.id
                        AND StatArticlesOrder.stat_order_id = StatCart.stat_order_id
                        AND StatArticlesOrder.article_id = StatCart.article_id						
                        AND (StatDelivery.data BETWEEN '$data_da' AND '$data_a')  ";
        if (!empty($stat_delivery_id))
            $sql .= " AND StatDelivery.id = " . $stat_delivery_id;
        if (!empty($supplier_organization_id))
            $sql .= " AND StatOrder.supplier_organization_id = " . $supplier_organization_id;
        if (!empty($stat_order_id))
            $sql .= " AND StatOrder.id = " . $stat_order_id;
        $sql .= " GROUP BY 
						StatOrder.supplier_organization_name, StatArticlesOrder.name, StatArticlesOrder.um  
					ORDER BY tot_importo desc, tot_qta desc";
        // echo '<br />'.$sql;
        $results = $this->Statistic->query($sql);
        $this->set('results', $results);

        $this->layout = 'ajax';
    }

    public function admin_export() {

        App::import('Model', 'StatDelivery');
        $StatDelivery = new StatDelivery;

        $anno_da = date("Y");

        $options = array();
        $options['conditions'] = array('StatDelivery.organization_id' => (int) $this->user->organization['Organization']['id'],
                                        'DATE_FORMAT(StatDelivery.data, "%Y")' => $anno_da);

            
        $options['fields'] = array('id', 'luogoData');
        $options['order'] = array('data ASC');
        $options['recursive'] = -1;
        $statDeliveries = $StatDelivery->find('list', $options);
        if (empty($statDeliveries)) {
            /*
             * anno precedente
             */
            $anno_da--;
            
            $options = array();
            $options['conditions'] = array('StatDelivery.organization_id' => (int) $this->user->organization['Organization']['id'],
                                            'DATE_FORMAT(StatDelivery.data, "%Y")' => $anno_da);
            $options['fields'] = array('id', 'luogoData');
            $options['order'] = array('data ASC');
            $options['recursive'] = -1;
            $statDeliveries = $StatDelivery->find('list', $options);
        }

        if (empty($statDeliveries)) {
            $this->Session->setFlash(__('NotFoundDeliveries'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        $this->set(compact('statDeliveries'));

        $this->set('anno_da', $anno_da); 

        $options = array('EXCEL' => 'Excel');
        $this->set('options', $options);
    }

    /*
     * esportazione completa
     */
    public function admin_export_file($year = '2012', $doc_options = null, $doc_formato = null) {

        if (empty($year) || $doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        Configure::write('debug', 0);

        $sql = "SELECT
                    StatDelivery.luogo, DATE_FORMAT(StatDelivery.data, '%d-%m-%Y') as StatDelivery_data,
                    DATE_FORMAT(StatOrder.data_inizio, '%d-%m-%Y') as StatOrder_data_inizio, DATE_FORMAT(StatOrder.data_fine, '%d-%m-%Y') as StatOrder_data_fine,
                    StatOrder.supplier_organization_id, StatOrder.supplier_organization_name, StatOrder.supplier_img1,
                    User.id as user_id,User.username,User.email,User.name as User_name,
                    StatArticlesOrder.codice, StatArticlesOrder.name as StatArticlesOrder_name,
                    StatArticlesOrder.prezzo, StatArticlesOrder.qta as statArticlesOrder_qta, StatArticlesOrder.um, StatArticlesOrder.um_riferimento,
                    StatCart.qta as StatCart_qta, StatCart.importo as StatCart_importo,
                    StatOrder.importo as StatOrder_importo, StatOrder.tesoriere_fattura_importo, StatOrder.tesoriere_doc1, StatOrder.tesoriere_data_pay, StatOrder.tesoriere_importo_pay 
                  FROM " . Configure::read('DB.portalPrefix') . "users User, 
                           " . Configure::read('DB.prefix') . "stat_articles_orders StatArticlesOrder,
                           " . Configure::read('DB.prefix') . "stat_carts StatCart, 
                           " . Configure::read('DB.prefix') . "stat_deliveries StatDelivery, 
                           " . Configure::read('DB.prefix') . "stat_orders StatOrder
                  WHERE
                    User.organization_id = " . $this->user->organization['Organization']['id'] . " and
                    StatArticlesOrder.organization_id = " . $this->user->organization['Organization']['id'] . " and
                    StatCart.organization_id = " . $this->user->organization['Organization']['id'] . " and
                    StatDelivery.organization_id = " . $this->user->organization['Organization']['id'] . " and
                    StatOrder.organization_id = " . $this->user->organization['Organization']['id'] . " and
                    StatArticlesOrder.stat_order_id = StatOrder.id and
                    StatCart.stat_order_id = StatOrder.id and
                    StatCart.user_id = User.id and
                    StatOrder.stat_delivery_id = StatDelivery.id and
                    StatArticlesOrder.article_organization_id = StatCart.article_organization_id and
                    StatArticlesOrder.article_id = StatCart.article_id and
                    DATE_FORMAT(StatDelivery.data, '%Y') = '" . $year . "'
                  ORDER BY
                    StatDelivery.data,
                    StatOrder.data_inizio,
                    StatOrder.data_fine,
                    StatOrder.supplier_organization_name,
                    User.id,User.username,User.email,User.name,
                    StatArticlesOrder.name,
                    StatArticlesOrder.codice;";
        // echo '<br />'.$sql;
        $results = $this->Statistic->query($sql);
        $this->set('results', $results);
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */

        $fileData['fileName'] = "statistiche_anno_" . $year;
        $fileData['fileTitle'] = "Statistiche anno " . $year;
        $this->set('fileData', $fileData);

        switch ($doc_formato) {
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'export_file_excel')
                    $this->render('admin_export_file_excel');
                break;
        }
    }
    
    /*
     * esportazione per orders
     */
    public function admin_export_orders_file($year = '2012', $doc_options = null, $doc_formato = null) {

        if (empty($year) || $doc_options == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        Configure::write('debug', 0);

        $sql = "SELECT
                    StatDelivery.luogo, DATE_FORMAT(StatDelivery.data, '%d-%m-%Y') as StatDelivery_data,
                    DATE_FORMAT(StatOrder.data_inizio, '%d-%m-%Y') as StatOrder_data_inizio, DATE_FORMAT(StatOrder.data_fine, '%d-%m-%Y') as StatOrder_data_fine,
                    CategoriesSupplier.name, 
                    StatOrder.supplier_organization_id, StatOrder.supplier_organization_name, StatOrder.supplier_img1,
                    StatOrder.importo as StatOrder_importo, StatOrder.tesoriere_fattura_importo, StatOrder.tesoriere_doc1, StatOrder.tesoriere_data_pay, StatOrder.tesoriere_importo_pay 
                  FROM 
                           " . Configure::read('DB.prefix') . "stat_deliveries StatDelivery, 
                           " . Configure::read('DB.prefix') . "stat_orders StatOrder,
                           " . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,
                           " . Configure::read('DB.prefix') . "suppliers Supplier,
                           " . Configure::read('DB.prefix') . "categories_suppliers CategoriesSupplier
                  WHERE
                    StatDelivery.organization_id = " . $this->user->organization['Organization']['id'] . " and
                    StatOrder.organization_id = " . $this->user->organization['Organization']['id'] . " and
                    StatOrder.stat_delivery_id = StatDelivery.id and
                    DATE_FORMAT(StatDelivery.data, '%Y') = '" . $year . "' and 
                    StatOrder.supplier_organization_id = SuppliersOrganization.id and 
                    SuppliersOrganization.organization_id = " . $this->user->organization['Organization']['id'] . "  and  
                    Supplier.id = SuppliersOrganization.supplier_id and  
                    Supplier.category_supplier_id = CategoriesSupplier.id                          
                  ORDER BY
                    StatDelivery.data,
                    StatOrder.data_inizio,
                    StatOrder.data_fine,
                    StatOrder.supplier_organization_name;";
        // echo '<br />'.$sql;
        $results = $this->Statistic->query($sql);
        $this->set('results', $results);
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
        */
        $fileData['fileName'] = "statistiche_per_ordine_anno_" . $year;
        $fileData['fileTitle'] = "Statistiche per ordine anno " . $year;
        $this->set('fileData', $fileData);

        switch ($doc_formato) {
            case 'EXCEL':
                $this->layout = 'excel';
                if ($doc_options == 'export_file_excel')
                    $this->render('admin_export_file_orders_excel');
                break;
        }
    }

}
