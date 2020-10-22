<?php
App::uses('AppModel', 'Model');

class DesOrder extends AppModel {
    
    /*
     * dati utilizzati  $this->element('boxDesOrderToOrder', array('results' => $desOrdersResults));    
     */
    public function getDesOrder($user, $des_order_id, $debug = false) {

        App::import('Model', 'Supplier');

        App::import('Model', 'DesOrdersOrganization');

        App::import('Model', 'Organization');

        $options = [];
        $options['recursive'] = 1;
        $options['conditions'] = ['DesOrder.id' => $des_order_id];
        /*
         * se arrivo da FO o sono ROOT e' vuoto! 
         */
        if(!empty($user->des_id)) {
            $options['conditions'] += ['DesOrder.des_id' => $user->des_id];
        } 
        $results = $this->find('first', $options);

        self::d('DesOrder::getDesOrder()', $debug);
        self::d($options, $debug);
        // self::d($user, $debug);
        
        if (empty($results))
            return;

        /*
         * estraggo OwnOrganization
         */
        $Organization = new Organization;

        $options = [];
        $options['conditions'] = ['Organization.id' => $results['DesSupplier']['own_organization_id']];
        $options['recursive'] = -1;
        $ownOrganizationResults = $Organization->find('first', $options);


        /*
         * estraggo produttore
         */
        $Supplier = new Supplier;

        $options = [];
        $options['conditions'] = ['Supplier.id' => $results['DesSupplier']['supplier_id']];
        $options['recursive'] = -1;
        $supplierResults = $Supplier->find('first', $options);

        /*
         * estraggo ordini dei GAS (DesOrdersOrganizations) associati al DesOrders
         */
        $DesOrdersOrganization = new DesOrdersOrganization;

        $options = [];
        $options['conditions'] = ['DesOrdersOrganization.des_order_id' => $results['DesOrder']['id']];
        /*
         * se arrivo da FO o sono ROOT e' vuoto! 
         */
        if(!empty($user->des_id)) {
            $options['conditions'] += ['DesOrdersOrganization.des_id' => $user->des_id];
        }       

        $options['recursive'] = 0;
        $DesOrdersOrganization->unbindModel(['belongsTo' => ['DesOrder', 'De']]);
        $desOrdersOrganizationsResults = $DesOrdersOrganization->find('all', $options);

        $results['OwnOrganization'] = $ownOrganizationResults['Organization'];
        $results['Supplier'] = $supplierResults['Supplier'];
        $results['DesOrdersOrganizations'] = $desOrdersOrganizationsResults;

        self::d('DesOrder::getDesOrder()', $debug);
        self::d($options, $debug);
        self::d($results, $debug);

        return $results;
    }

    /*
     * ctrl se l'utente e' referente dell'ordine
     */

    public function aclReferenteDesSupplier($user, $des_order_id, $debug = false) {
        
        $ACLsuppliersIdsDes = $user->get('ACLsuppliersIdsDes');
        if(empty($ACLsuppliersIdsDes))
            return false;
    
        $options = [];
        $options['conditions'] = ['DesOrder.des_id' => $user->des_id,
                                  'DesOrder.id' => $des_order_id,
                                  'DesOrder.des_supplier_id IN ('.$user->get('ACLsuppliersIdsDes').')'];
        $options['recursive'] = -1;
        $totali = $this->find('count', $options);

        self::d('DesOrder::aclReferenteDesSupplier()', $debug);
        self::d($options, $debug);
        self::d($totali, $debug);

        if ($totali == 0)
            return false;
        else
            return true;
    }

    /*
     * Se titolareDes -> nuovo ordine
     *      => copio tutti articleOrders ai desGAS
     *
     * Se titolareDes -> modifica articleOrders
     *      => aggiunge -> articleOrders ai desGAS
     *      => elimina -> articleOrders ai desGAS
     *
     * Se referente -> nuovo ordine
     *      => copio tutti articleOrders dal titolareDes
     *  
     *  $articles_orders_key =  ArticlesOrder.organization_id, 
     *                          ArticlesOrder.article_organization_id, 
     *                          ArticlesOrder.article_id, 
     *                          ArticlesOrder.order_id
     *  se valorizzato aggiorno solo quell'articles_orders
     */

    public function insertOrUpdateArticlesOrderAllOrganizations($user, $des_order_id, $order_id, $articles_orders_key = [], $isTitolareDesSupplier, $debug = false) {


        $debug = true;


        /*
         * array di tutti i GAS dove copiare gli articlesOrders del GAS titolare
         */
        $desOrdersOrganizationsToCopyResults = [];

        App::import('Model', 'DesOrdersOrganization');
        $DesOrdersOrganization = new DesOrdersOrganization();

        if ($debug) {
            if ($isTitolareDesSupplier)
                echo '<h2>referente is Titolare DesSupplier</h2>';
            else
                echo '<h2>referente NOT is Titolare DesSupplier</h2>';
        }

        $desOrdersResults = [];


        if ($isTitolareDesSupplier) {

            $titolare_organization_id = $user->organization['Organization']['id'];
            $titolare_order_id = $order_id;

            /*
             * estraggo gli altri ordini per allinearni con quelli del titolare 
             */
            $options = [];
            $options['conditions'] = ['DesOrdersOrganization.des_order_id' => $des_order_id,
                                      'DesOrdersOrganization.organization_id != ' => $user->organization['Organization']['id']];  // escludo quello del GAS 
            if (!empty($user->des_id))
                $options['conditions'] += ['DesOrdersOrganization.des_id' => $user->des_id];
            $options['recursive'] = -1;

            /*
             * array di tutti i GAS dove copiare gli articlesOrders del GAS titolare
             */
            $desOrdersOrganizationsToCopyResults = $DesOrdersOrganization->find('all', $options);
            if ($debug) {
                echo '<br />DesOrder->insertOrUpdateArticlesOrderAllOrganizations() - tutti gli ordini dei DES';
                echo "<pre>";
                print_r($options['conditions']);
                print_r($desOrdersOrganizationsToCopyResults);
                echo "</pre>";
            }
        } else {
            /*
             * titolare_organization_id:
             *       organization_id del GAS titolare per copiare gli articlesOrders
             */
            App::import('Model', 'DesSupplier');
            $DesSupplier = new DesSupplier();
            $titolare_organization_id = $DesSupplier->getOrganizationIdTitolare($user, $des_order_id, $debug);

            if (!empty($titolare_organization_id)) {

                /*
                 * titolare_order_id:
                 *      order_id del GAS titolare per copiare gli articlesOrders
                 */
                $options = [];
                $options['conditions'] = array('DesOrdersOrganization.organization_id' => $titolare_organization_id,
                    'DesOrdersOrganization.des_order_id' => (int) $des_order_id);
                if (!empty($user->des_id))
                    $options['conditions'] += array('DesOrdersOrganization.des_id' => $user->des_id);
                $options['recursive'] = -1;
                $options['fields'] = array('DesOrdersOrganization.order_id');
                $desOrdersOrganizationsResults = $DesOrdersOrganization->find('first', $options);

                $titolare_order_id = $desOrdersOrganizationsResults['DesOrdersOrganization']['order_id'];
            }

            /*
             * array di tutti i GAS dove copiare gli articlesOrders del GAS titolare
             */
            $desOrdersOrganizationsToCopyResults[0]['DesOrdersOrganization']['organization_id'] = $user->organization['Organization']['id'];
            $desOrdersOrganizationsToCopyResults[0]['DesOrdersOrganization']['order_id'] = $order_id;
        }

        if ($debug) {
            echo '<br />DesOrder->insertOrUpdateArticlesOrderAllOrganizations() - titolare_organization_id ' . $titolare_organization_id;
            echo '<br />DesOrder->insertOrUpdateArticlesOrderAllOrganizations() - titolare_order_id ' . $titolare_order_id;
        }

        /*
         * estraggo gli ordini del GAS titolare (il referente che ha creato l'ordine in condivisione)
         * se $articles_orders_key valorizzato gestisco il singolo articlesOrder
         */
        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;

        $options = [];
        $options['conditions'] = array('ArticlesOrder.organization_id' => $titolare_organization_id,
            'ArticlesOrder.order_id' => $titolare_order_id,
            'ArticlesOrder.stato != ' => 'N');
        if (!empty($articles_orders_key)) {
            $options['conditions'] += array('ArticlesOrder.article_organization_id' => $articles_orders_key['article_organization_id'],
                'ArticlesOrder.article_id' => $articles_orders_key['article_id']);
        }
        $options['order'] = 'ArticlesOrder.article_organization_id, ArticlesOrder.article_id';
        $options['recursive'] = -1;
        $results = $ArticlesOrder->find('all', $options);
        if ($debug) {
            echo '<br />DesOrder->insertOrUpdateArticlesOrderAllOrganizations() - totale Articoli trovati del GAS TITOLARE ' . count($results);
            /*
              echo "<pre>DesOrder->insertOrUpdateArticlesOrderAllOrganizations() - des_order_id \n";
              print_r($options);
              print_r($results);
              echo "</pre>";
             */
        }


        /*
         * ciclo per DesOrders
         */
        if (empty($desOrdersOrganizationsToCopyResults)) {
            if ($debug)
                echo '<br />Non ci sono altri ordini DES da aggiornare';
        } else
            foreach ($desOrdersOrganizationsToCopyResults as $desOrdersOrganizationsToCopyResult) {

                $order_id = $desOrdersOrganizationsToCopyResult['DesOrdersOrganization']['order_id'];
                $organization_id = $desOrdersOrganizationsToCopyResult['DesOrdersOrganization']['organization_id'];

                /*
                 * ciclo per ArticlesOrders
                 */
                foreach ($results as $result) {

                    $data = [];
                    $data['ArticlesOrder']['organization_id'] = $organization_id;
                    $data['ArticlesOrder']['order_id'] = $order_id;
                    $data['ArticlesOrder']['article_organization_id'] = $result['ArticlesOrder']['article_organization_id'];
                    $data['ArticlesOrder']['article_id'] = $result['ArticlesOrder']['article_id'];
                    $data['ArticlesOrder']['qta_cart'] = 0;
                    $data['ArticlesOrder']['name'] = $result['ArticlesOrder']['name'];
                    $data['ArticlesOrder']['prezzo'] = $result['ArticlesOrder']['prezzo_'];
                    $data['ArticlesOrder']['pezzi_confezione'] = $result['ArticlesOrder']['pezzi_confezione'];
                    $data['ArticlesOrder']['qta_minima'] = $result['ArticlesOrder']['qta_minima'];
                    $data['ArticlesOrder']['qta_massima'] = $result['ArticlesOrder']['qta_massima'];
                    $data['ArticlesOrder']['qta_minima_order'] = $result['ArticlesOrder']['qta_minima_order'];
                    $data['ArticlesOrder']['qta_massima_order'] = $result['ArticlesOrder']['qta_massima_order'];
                    $data['ArticlesOrder']['qta_multipli'] = $result['ArticlesOrder']['qta_multipli'];
                    $data['ArticlesOrder']['alert_to_qta'] = $result['ArticlesOrder']['alert_to_qta'];
                    $data['ArticlesOrder']['stato'] = $result['ArticlesOrder']['stato'];

                    if ($debug) {
                        echo "<pre>Articoli da copiare ";
                        print_r($data);
                        echo "</pre>";
                    }

                    $ArticlesOrder->create();
                    try {
                        $ArticlesOrder->save($data);
                    } catch (Exception $e) {
                        echo "<pre> ";
                        print_r($e);
                        echo "</pre>";
                        //  return false;
                    }
                } // end foreach ArticlesOrders
            } // end foreach DesOrders  


        if ($debug)
            exit;

        return true;
    }

    /*
     * solo referenteTitolareDes puo' cancellare e propagare agli altri GAS
     *
     *  $articles_orders_key =  ArticlesOrder.organization_id, 
     *                          ArticlesOrder.article_organization_id, 
     *                          ArticlesOrder.article_id, 
     *                          ArticlesOrder.order_id
     */
    public function deleteArticlesOrderAllOrganizations($user, $des_order_id, $articles_orders_key = [], $debug = false) {

        // $debug=true;

        /*
         * array di tutti i GAS dove cancellare gli articlesOrders del GAS titolare
         */
        $desOrdersOrganizationsToDeleteResults = [];

        $titolare_organization_id = $user->organization['Organization']['id'];
        $titolare_order_id = $order_id;

        /*
         * estraggo gli altri ordini per allinearni con quelli del titolare 
         */
        App::import('Model', 'DesOrdersOrganization');
        $DesOrdersOrganization = new DesOrdersOrganization();

        $options = [];
        $options['conditions'] = ['DesOrdersOrganization.des_order_id' => $des_order_id,
                                  'DesOrdersOrganization.organization_id != ' => $user->organization['Organization']['id']];  // escludo quello del GAS 
        if (!empty($user->des_id))
            $options['conditions'] += ['DesOrdersOrganization.des_id' => $user->des_id];
        $options['recursive'] = -1;

        /*
         * array di tutti i GAS dove cancellare gli articlesOrders del GAS titolare
         */
        $desOrdersOrganizationsToDeleteResults = $DesOrdersOrganization->find('all', $options);

        self::d('DesOrder::deleteArticlesOrderAllOrganizations()', $debug);
        self::d($options['conditions'], $debug);
        self::d($desOrdersOrganizationsToDeleteResults, $debug);

        self::d('DesOrder->deleteArticlesOrderAllOrganizations() - titolare_organization_id ' . $titolare_organization_id, $debug);
        self::d('DesOrder->deleteArticlesOrderAllOrganizations() - totale ordine del DES ' . count($desOrdersOrganizationsToDeleteResults), $debug);
        self::d('DesOrder->deleteArticlesOrderAllOrganizations() - articles_orders_key', $debug);
        self::d($articles_orders_key, $debug);

        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;

        /*
         * ciclo per DesOrders
         */
        if (empty($desOrdersOrganizationsToDeleteResults)) {
            self::d('Non ci sono altri ordini DES da aggiornare', $debug);
        } else
            foreach ($desOrdersOrganizationsToDeleteResults as $desOrdersOrganizationsToDeleteResult) {

                $organization_id = $desOrdersOrganizationsToDeleteResult['DesOrdersOrganization']['organization_id'];
                $order_id = $desOrdersOrganizationsToDeleteResult['DesOrdersOrganization']['order_id'];
                $article_organization_id = $articles_orders_key['article_organization_id'];
                $article_id = $articles_orders_key['article_id'];

                if (!$ArticlesOrder->delete($organization_id, $order_id, $article_organization_id, $article_id)) {
                    self::d('Articolo ' . $article_id . ' del GAS ' . $organization_id . ' associato all ordine ' . $order_id . ' non cancellato!', $debug);
                }
            }

        if($debug)
            exit;

        return true;
    }

    /*
     * richiamato anche da Cron::desOrderStatoElaborazione()
     *
     * DesOrder.state_code
     *
     */
    public function statoElaborazione($des_id, $des_order_id = 0, $debug = false) {

        $data_oggi = date("Y-m-d");

        /*
         * da OPEN a BEFORE-TRASMISSION
         */
        self::d("Porto i DesOrders da OPEN a BEFORE-TRASMISSION se la data_fine_max e' scaduta", $debug);

        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder;
        $DesOrder->unbindModel(['belongsTo' => ['DesOrganization', 'De', 'Organization']]);

        $options = [];
        $options['conditions'] = ['DesOrder.des_id' => $des_id,
                                    'DesOrder.state_code' => 'OPEN',
                                    '0' => 'DesOrder.data_fine_max < CURDATE()'];
        if (!empty($des_order_id))
            $options['conditions'] += ['DesOrder.id' => $des_order_id];
        $options['fields'] = ['DesOrder.id'];
        $options['recursive'] = -1;
        $results = $DesOrder->find('all', $options);
        // self::d($results, $debug);

        if (empty($results)) {
            self::d("nessun DesOrder trovato con data_fine_max scaduta", $debug);
        } else
            foreach ($results as $numResult => $result) {

                self::d($result['DesOrder']['id'], $debug);

                try {
                    $sql = "UPDATE " . Configure::read('DB.prefix') . "des_orders
                            SET
                                state_code = 'BEFORE-TRASMISSION'
                            WHERE
                                des_id = ".(int) $des_id." and id = ".$result['DesOrder']['id'];
                    self::d($sql, $debug);
                    $this->query($sql);
                } catch (Exception $e) {
                    self::d('DesOrder::statoElaborazione()' . $e, $debug);
                }
            } // loop DesOrder da OPEN a BEFORE-TRASMISSION 

            /*
             * da POST-TRASMISSION / REFERENT-WORKING a CLOSE
             */
        self::d("Porto i DesOrders POST-TRASMISSION / REFERENT-WORKING a CLOSE se tutti i suoi Orders.state_code = CLOSE", $debug);

        App::import('Model', 'DesOrdersOrganization');

        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder;
        $DesOrder->unbindModel(['belongsTo' => ['DesOrganization', 'De', 'Organization']]);

        $options = [];
        $options['conditions'] = ['DesOrder.des_id' => $des_id,
                                  "(DesOrder.state_code = 'POST-TRASMISSION' or DesOrder.state_code = 'REFERENT-WORKING')"];
        if (!empty($des_order_id))
            $options['conditions'] += ['DesOrder.id' => $des_order_id];
        $options['fields'] = ['DesOrder.id'];
        $options['recursive'] = -1;
        $results = $DesOrder->find('all', $options);
        
        self::d("DesOrder::statoElaborazione() - estraggo DesOrder", $debug);
        self::d($options, $debug);
        self::d($results, $debug);
        if (empty($results)) {
            self::d("nessun DesOrder trovato da portare a CLOSE", $debug);
        } else
            foreach ($results as $numResult => $result) {

                $DesOrdersOrganization = new DesOrdersOrganization;
                $DesOrdersOrganization->unbindModel(['belongsTo' => ['DesOrganization', 'De', 'Organization', 'DesOrder']]);

                $options = [];
                $options['conditions'] = ['DesOrdersOrganization.des_id' => $des_id,
                                           'DesOrdersOrganization.des_order_id' => $result['DesOrder']['id']];
                $options['fields'] = ['Order.state_code'];
                $options['recursive'] = 1;
                $ordersResults = $DesOrdersOrganization->find('all', $options);
                /*
                  echo "<pre>DesOrder::statoElaborazione() - estraggo DesOrdersOrganization \r ";
                  print_r($options);
                  print_r($ordersResults);
                  echo "</pre>".count($ordersResults);
                */

                /*
                 * ctrl se tutti gli ordini di un DesOrderOrganization sono CLOSE
                 */
                $all_close = 0;
                foreach ($ordersResults as $ordersResult) {
                    if ($ordersResult['Order']['state_code'] == 'CLOSE')
                        $all_close++;
                }

                self::d(" DesOrderId " . $result['DesOrder']['id'] . " ha " . ($all_close) . " ordini chiusi su " . (count($ordersResults)), $debug);
                
                if ($all_close == count($ordersResults)) {
                    
                    self::d("Porto il DesOrder a CLOSE ", $debug);
                    
                    try {
                        $sql = "UPDATE " . Configure::read('DB.prefix') . "des_orders
                                SET
                                    state_code = 'CLOSE'
                                WHERE
                                    des_id = " . (int) $des_id . "
                                    and id = " . $result['DesOrder']['id'];
                        self::d($sql, $debug);
                        $this->query($sql);
                    } catch (Exception $e) {
                        self::d('DesOrder::statoElaborazione()' . $e, $debug);
                    }
                } else {
                    self::d(" NON porto il DesOrder a CLOSE", $debug);
                }
            } // loop DesOrder da BEFORE-TRASMISSION a CLOSE 
    }

    /*
     * richiamato
     * da DesOrdersController::admin_index() => Cron::desOrdersDelete()
     *  cancella DesOrder con 
     *      data_fine_max scaduta / DesOrder.state_code = 'CLOSE'
     *      ordini non + associati perche' portati in Statistiche
     */
    public function deleteScaduti($des_id, $des_order_id = 0, $debug = false) {

        $data_oggi = date("Y-m-d");

        /*
         * Estraggo DesOrder con data_fine_max scaduta
         */
        if ($debug)
            echo "Estraggo DesOrder con data_fine_max scaduta e state_code' = 'CLOSE' \n";

        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder;
        $DesOrder->unbindModel(['belongsTo' => ['DesOrganization', 'De', 'Organization']]);

        $options = [];
        $options['conditions'] = ['DesOrder.des_id' => $des_id,
                                   'DesOrder.state_code' => 'CLOSE',
                                   'DesOrder.data_fine_max < CURDATE()'];
        if (!empty($des_order_id))
            $options['conditions'] += array('DesOrder.id' => $des_order_id);
        $options['fields'] = ['DesOrder.id', 'DesOrder.data_fine_max'];
        $options['order'] = ['DesOrder.id'];
        $options['recursive'] = -1;
        $results = $DesOrder->find('all', $options);
        /*
        echo "<pre>"; 
        print_r($options);
        print_r($results);
        echo "<pre>";
        */
        if (empty($results)) {
            if ($debug)
                echo " nessun DesOrder trovato con data_fine_max scaduta e state_code' = 'CLOSE' \n";
        } else {
            /*
             * controllo se quelli trovati hanno TUTTI gli ordini non + associati perche' portati in Statistiche
             */
            if ($debug)
                echo "<br />Controllo se quelli trovati hanno TUTTI gli ordini non + associati perche' portati in Statistiche \n";
             
            foreach ($results as $numResult => $result) {

                if ($debug)
                    echo '<hr />Tratto DesOrder.id '.$result['DesOrder']['id'].' con data_fine_max '.$result['DesOrder']['data_fine_max']." e estraggo tutti gli ordini associati \n";

                App::import('Model', 'DesOrdersOrganization');
                $DesOrdersOrganization = new DesOrdersOrganization;
                $DesOrdersOrganization->unbindModel(array('belongsTo' => array('Organization', 'De', 'DesOrder')));
        
                $options = [];
                $options['conditions'] = ['DesOrdersOrganization.des_id' => $des_id,
                          'DesOrdersOrganization.des_order_id' => $result['DesOrder']['id']];
                $options['fields'] = ['Order.id'];
                $options['recursive'] = 1;              
                $desOrdersOrganizationResults = $DesOrdersOrganization->find('all', $options);
                
                if ($debug) debug($desOrdersOrganizationResults);
                     
                if (!empty($desOrdersOrganizationResults)) {
                    if ($debug)
                        echo '<br />Per DesOrder.id '.$result['DesOrder']['id']." ctrl se TUTTI gli ordini non + associati perche' portati in Statistiche \n";
                    
                    $empty_order_id = 0;
                    foreach ($desOrdersOrganizationResults as $desOrdersOrganizationResult) {
                        if(empty($desOrdersOrganizationResult['Order']['id'])) {
                            /*
                             * quindi e' stato cancellato perche' in statistiche
                             */
                            $empty_order_id ++;
                        }
                    }    // loop $desOrdersOrganizationResults

                    if ($debug)
                        echo "<br />Per DesOrder.id ".$result['DesOrder']['id']." trovati ".$empty_order_id."  su ".(count($desOrdersOrganizationResult)+1)." \n";
        
                    $tot_orders = count($desOrdersOrganizationResults);
                    if($empty_order_id == $tot_orders) {
                        
                        try {
                            $sql = "DELETE from " . Configure::read('DB.prefix') . "des_orders
                                    WHERE
                                        des_id = " . (int) $des_id . "
                                        and id = " . $result['DesOrder']['id'];
                            if ($debug)
                                echo '<br />' . $sql . "\n";
                            $this->query($sql);
                        } catch (Exception $e) {
                            echo '<br />DesOrder::deleteScaduti()<br />' . $e;
                        }
                    }
                    else {
                        if ($debug)
                            echo "<br />NON cancello DesOrder.id ".$result['DesOrder']['id']." \n";
                    }
                }
                else {
                    /*
                     * non ho piu' ordini associati all'ordine DES
                     */
                    if ($debug)
                        echo "<br />non ho piu' ordini associati all'ordine DES => cancello DesOrder.id ".$result['DesOrder']['id']." \n";                    
                    try {
                        $sql = "DELETE from " . Configure::read('DB.prefix') . "des_orders
                                WHERE
                                    des_id = " . (int) $des_id . "
                                    and id = " . $result['DesOrder']['id'];
                        if ($debug)
                            echo '<br />' . $sql . "\n";
                        $this->query($sql);
                    } catch (Exception $e) {
                        echo '<br />DesOrder::deleteScaduti()<br />' . $e;
                    }
                }

            } // loop DesOrder 
        } // end (empty($results)) 
    }

    public $validate = array(
        'des_id' => array(
            'numeric' => array(
                'rule' => ['numeric'],
            //'message' => 'Your custom message here',
            //'allowEmpty' => false,
            //'required' => false,
            //'last' => false, // Stop validation after this rule
            //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'des_supplier_id' => array(
            'numeric' => array(
                'rule' => ['numeric'],
            //'message' => 'Your custom message here',
            //'allowEmpty' => false,
            //'required' => false,
            //'last' => false, // Stop validation after this rule
            //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
    );
    public $belongsTo = array(
        'De' => array(
            'className' => 'De',
            'foreignKey' => 'des_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'DesSupplier' => array(
            'className' => 'DesSupplier',
            'foreignKey' => 'des_supplier_id',
            'conditions' => 'DesSupplier.des_id = DesOrder.des_id',
            'fields' => '',
            'order' => ''
        )
    );

}