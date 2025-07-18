<?php
/*
 * class inclusa in cake/components/com_cake/app/Cron/index.php
 *
 * in /var/cakephp/cron creo {method}.sh
 * in Lib/UtilsCrons.php creo public function articlesOrdersQtaCart($organization_id)
 * per eseguirle da shell /var/cakephp/cron/...........sh
 */
App::uses('CakeEmail', 'Network/Email');
App::uses('TimeHelper', 'View/Helper');
App::uses('View', 'View');
App::uses('AppHelper', 'View/Helper');
App::uses('ExportDocsHelper', 'View/Helper');
App::uses('File', 'Utility');

class UtilsCrons {

    private $AppRoot = '/var/www/portalgas';
    private $timeHelper;
    private $appHelper;
    private $exportDocsHelper;

    public function __construct(View $view, array $settings = []) {

        date_default_timezone_set('Europe/Rome');

        Configure::write('debug', 0);
        $this->timeHelper = new TimeHelper($view, $settings);
        $this->appHelper = new AppHelper($view, $settings);
        $this->exportDocsHelper = new ExportDocsHelper($view, $settings);
    }

    /*
     *  invio mail x notificare la consegna
     */
    public function mailUsersDelivery($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return;

        // escludo i GasGroup
        if(isset($user->organization['Organization']['hasGasGroups']) && $user->organization['Organization']['hasGasGroups']=='Y')
            return;

        echo date("d/m/Y") . " - " . date("H:i:s") . " Mail agli utenti con dettaglio consegna \n";
        
        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        App::import('Model', 'Order');

        App::import('Model', 'Supplier');

        App::import('Model', 'Mail');
        $Mail = new Mail;

        $Email = $Mail->getMailSystem($user);
        
        App::import('Model', 'User');

        $j_seo = $user->organization['Organization']['j_seo'];

        $options = [];
        /*
         * estraggo le consegne che si apriranno domani
         */
        $options['conditions'] = ['Delivery.organization_id' => (int) $user->organization['Organization']['id'],
                                'Delivery.isVisibleFrontEnd' => 'Y',
                                'Delivery.stato_elaborazione' => 'OPEN',
                                'Delivery.type' => 'GAS',  // GAS-GROUP
                                'DATE(Delivery.data) = CURDATE() + INTERVAL ' . Configure::read('GGMailToAlertDeliveryOn') . ' DAY '];
        $options['recursive'] = -1;
        $deliveryResults = $Delivery->find('all', $options);

        if (!empty($deliveryResults)) {
            foreach ($deliveryResults as $deliveryResult) {

                if($debug)
                    echo "Elaboro consegna di " . $this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'], "%A %e %B %Y") . " a " . $deliveryResult['Delivery']['luogo'] . " \n";

                /*
                 * estraggo gli ordini associati alla consegna
                 */

                $Order = new Order;

                $Order->unbindModel(['belongsTo' => ['Delivery']]);
                $options = [];
                $options['conditions'] = ['Order.delivery_id' => $deliveryResult['Delivery']['id'],
                                            'Order.organization_id' => (int) $user->organization['Organization']['id'],
                                            'Order.isVisibleBackOffice' => 'Y',
                                            'Order.state_code !=' => 'CREATE-INCOMPLETE'];
                $options['recursive'] = 0;
                $options['fields'] = ['SuppliersOrganization.name', 'SuppliersOrganization.frequenza', 'SuppliersOrganization.supplier_id'];
                $options['order'] = ['SuppliersOrganization.name'];
                $orderResults = $Order->find('all', $options);

                $tmpProduttori = "";
                foreach ($orderResults as $numResult => $orderResult) {

                    if($debug)
                        echo "Elaboro ordine del produttore " . $orderResult['SuppliersOrganization']['name'] . " \n";

                    /*
                     * Suppliers per l'immagine
                     * */
                    $Supplier = new Supplier;

                    $options = [];
                    $options['conditions'] = ['Supplier.id' => $orderResult['SuppliersOrganization']['supplier_id']];
                    $options['fields'] = ['Supplier.descrizione', 'Supplier.img1'];
                    $options['recursive'] = -1;
                    $SupplierResults = $Supplier->find('first', $options);

                    $tmpProduttori .= '<br />' . ($numResult + 1) . ') ';

                    if (!empty($SupplierResults['Supplier']['img1']) && file_exists($this->AppRoot . Configure::read('App.img.upload.content') . DS . $SupplierResults['Supplier']['img1']))
                        $tmpProduttori .= ' <img width="50" src="https://www.portalgas.it' . Configure::read('App.web.img.upload.content') . '/' . $SupplierResults['Supplier']['img1'] . '" alt="' . $orderResult['SuppliersOrganization']['name'] . '" /> ';
                    else
                        $tmpProduttori .= ' <img width="50" src="https://www.portalgas.it' . Configure::read('App.web.img.upload.content') . '/empty.png" alt="' . $orderResult['SuppliersOrganization']['name'] . '" /> ';

                    $tmpProduttori .= $orderResult['SuppliersOrganization']['name'];
                    if (!empty($SupplierResults['Supplier']['descrizione']))
                        $tmpProduttori .= ' (' . $SupplierResults['Supplier']['descrizione'] . ')';
                    if (!empty($SupplierResults['SuppliersOrganization']['frequenza']))
                        $tmpProduttori .= ' Frequenza ' . $orderResult['SuppliersOrganization']['frequenza'];
                } // end foreach ($orderResults as $orderResult)


                $User = new User;

                $conditions = ['Delivery.id' => $deliveryResult['Delivery']['id']];
                $results = $User->getUserWithCartByDelivery($user, $conditions, null, $modalita = 'CRON');
                if (!empty($results)) {
                    foreach ($results as $numResult => $result) {
                        $name = $result['User']['name'];
                        $mail = $result['User']['email'];
                        $username = $result['User']['username'];

                        if($debug)
                            echo '<br />' . $numResult . ") tratto l'utente " . $name . ', username ' . $username;

                        if (!empty($mail)) {
                            $body_mail = "";
                            $body_mail .= 'Il giorno ' . $this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'], "%A %e %B %Y") . " ci sar&agrave; la consegna a " . $deliveryResult['Delivery']['luogo'] . ", dalle ore " . substr($deliveryResult['Delivery']['orario_da'], 0, 5) . " alle " . substr($deliveryResult['Delivery']['orario_a'], 0, 5);
                            if (!empty($deliveryResult['Delivery']['nota'])) {
                                $body_mail .= '<div style="float:right;width:75%;margin-top:5px;">';
                                $body_mail .= '<span style="color:red;">Nota</span> ';
                                $body_mail .= $deliveryResult['Delivery']['nota'];
                                $body_mail .= '</div>';
                            }

                            $url = 'https://www.portalgas.it/home-' . $j_seo . '/preview-carrello-' . $j_seo . '?' . $User->getUrlCartPreviewNoUsername($user, $deliveryResult['Delivery']['id']);

                            $body_mail .= '<div style="clear: both; float: none; margin: 5px 0 15px;">';
                            $body_mail .= '<img src="https://www.portalgas.it' . Configure::read('App.img.cake') . '/cesta-piena.png" title="" border="0" />';
                            $body_mail .= ' <a target="_blank" href="' . $url . '">Clicca qui per visualizzare i tuoi <b>acquisti</b> che dovrai ritirare durante la consegna</a>';
                            $body_mail .= '</div>';

                            if(count($orderResults)==1)
                                $body_mail .= '<h3>Produttore presente alla consegna</h3>';
                            else
                                $body_mail .= '<h3>Elenco dei produttori presenti alla consegna</h3>';
                            $body_mail .= $tmpProduttori;

                            /*
                             * all'url per il CartPreview aggiungo lo username crittografato
                             */
                            $body_mail_final = str_replace("{u}", urlencode($User->getUsernameCrypted($username)), $body_mail);
                            if ($debug && $numResult == 1)
                                echo $body_mail_final;

                            $subject_mail = $this->appHelper->_organizationNameError($user->organization) . ", consegna di " . $this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'], "%A %e %B %Y") . " a " . $deliveryResult['Delivery']['luogo'];
                            $Email->subject($subject_mail);

                            $Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
                            $Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))]);

                            $mailResults = $Mail->send($Email, $mail, $body_mail_final, $debug);
                            
                        } else {
                            if($debug) echo ": NON inviata, mail empty \n";
                        }
                    } // end foreach ($results as $result)              
                }
            } // end foreach ($deliveryResults as $deliveryResult) 
        } else {
            if($debug) echo "non ci sono consegne che apriranno tra " . (Configure::read('GGMailToAlertDeliveryOn') + 1) . " giorni \n";
        }
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     */
    public function mailEvents($organization_id, $debug=true) {
        
        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return;
        
        App::import('Model', 'Event');
        $Event = new Event;

        $Event->sendNotificationMail($this->timeHelper, $this->appHelper, $user, $debug);
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     *  event gcalendar x notificare le consegne OPEN e non elaborate (Delivery.gcalendar_event_id null)
     *  senza dettaglio produttori perche' non li ho ancora
     */

    public function gcalendarUsersDeliveryInsert($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return;
            
        App::import('Model', 'Google');
        $Google = new Google;

        $Google->usersDeliveryInsert($user, $this->timeHelper, $debug);
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     *  event gcalendar x aggiornare la consegna (prima di Configure::read('GGEventGCalendarToAlertDeliveryOn') gg dall'apertura)
     *  con dettaglio produttori
     */

    public function gcalendarUsersDeliveryUpdate($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return;
        
        App::import('Model', 'Google');
        $Google = new Google;

        $Google->usersDeliveryUpdate($user, $this->timeHelper, $debug);
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * invio mail 
     *      ordini che si aprono oggi
     *      ctrl data_inizio con data_oggi
     *      mail_open_send = Y (perche' in Order::add data_inizio = data_oggi)
     */
    public function mailUsersOrdersOpen($organization_id, $debug = false) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        // escludo i GasGroup
        if(isset($user->organization['Organization']['hasGasGroups']) && $user->organization['Organization']['hasGasGroups']=='Y')
            return;
        
        App::import('Model', 'MailsSend');
        $MailsSend = new MailsSend;

        $MailsSend->mailUsersOrdersOpen($organization_id, $user, $debug);
    }

    public function mailMonitoringSuppliersOrganizationsOrdersDataFine($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        App::import('Model', 'MonitoringSuppliersOrganization');
        $MonitoringSuppliersOrganization = new MonitoringSuppliersOrganization;

        $MonitoringSuppliersOrganization->mail_order_data_fine($organization_id, $debug);
    }
    
    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     */
    public function mailUsersOrdersClose($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 

        // escludo i GasGroup
        if(isset($user->organization['Organization']['hasGasGroups']) && $user->organization['Organization']['hasGasGroups']=='Y')
            return;

        App::import('Model', 'MailsSend');
        $MailsSend = new MailsSend;

        $MailsSend->mailUsersOrdersClose($organization_id, $user, $debug);
    }

    /*
     * se ordine NON DES
     *    se un ArticleOrder ha raggiunto la quantita' massimo (stato = QTAMAXORDER)
     *      => se ArticleOrder.send_mail = 'Y'  mail gia' inviata, salto
     *      => se ArticleOrder.send_mail = 'N'  invio mail e update send_mail = 'Y'
     * 
     *      prima porto tutti gli ArticleOrder.stato = Y con send_mail = Y a send_mail = N
     * se ordine DES
     *    faccio le medesime operazioni solo se il GAS e' il titolare dell'ordine, invio mail ai referenti (non ai titolari perche' loro non posso accedere all'ordine) 
     *
     * cron 10 8,13,20 * * * /var/portalgas/cron/mailReferentiQtaMax.sh
     */
    public function mailReferentiQtaMax($organization_id) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS', 'PRODGAS']);
        if(empty($user)) 
            return; 
        
        echo date("d/m/Y") . " - " . date("H:i:s") . " Mail ai referenti per i prodotti che hanno raggiunto il limite  (QTAMAXORDER) \n";

        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;

        /*
         * prima porto tutti gli ArticleOrder.stato = Y con send_mail = Y a send_mail = N
         *      perche' solo quelli con ArticleOrder.stato = QTAMAXORDER possono avere send_mail = Y 
         *      quindi era un QTAMAXORDER abbassata e totata a stato = Y 
         */
        echo "Estraggo gli articoli con ArticlesOrder.stato = Y e ArticlesOrder.send_mail = Y => li porto a send_mail = N perche solo con ArticlesOrder.QTAMAXORDER posso avere send_mail = Y \n";
        $ArticlesOrder->unbindModel(['belongsTo' => ['Cart']]);

        $options = [];
        $options['conditions'] = ['ArticlesOrder.organization_id' => (int) $user->organization['Organization']['id'],
                                'ArticlesOrder.stato' => 'Y',
                                'ArticlesOrder.send_mail' => 'Y',
                                'Article.stato' => 'Y'];
        /*
         * PRODGAS e' una promozione e l'ordine e' vuoto (order_id => prod_gas_promotion_id)
         */
        if($user->organization['Organization']['type']=='GAS')  
            $options['conditions'] += ['Order.state_code' => 'OPEN'];

        $options['recursive'] = 1;
        $articlesOrderResults = $ArticlesOrder->find('all', $options);
        echo "trovati ".count($articlesOrderResults)." articoli da aggiornare \n";

        foreach ($articlesOrderResults as $articlesOrderResult) {

            $sql = "UPDATE " . Configure::read('DB.prefix') . "articles_orders 
                    SET
                        send_mail = 'N', modified = '" . date('Y-m-d H:i:s') . "'
                    WHERE
                        organization_id = " . (int) $user->organization['Organization']['id'] . "
                        and order_id = " . $articlesOrderResult['ArticlesOrder']['order_id'] . " 
                        and article_organization_id = " . $articlesOrderResult['ArticlesOrder']['article_organization_id'] . " 
                        and article_id = " . $articlesOrderResult['ArticlesOrder']['article_id'];
            echo $sql . "\n";
            try {
                $ArticlesOrder->query($sql);
            } catch (Exception $e) {
                CakeLog::write('error', $sql);
                CakeLog::write('error', $e);
            }
        }

        /*
         * estraggo gli articoli che hanno raggiunto la QTAMAXORDER e non gli e' stata ancora inviata la mail
         */
        echo "Estraggo gli articoli con ArticlesOrder.stato = QTAMAXORDER e ArticlesOrder.send_mail = N per inviare MAIL di notifica \n";
        $options = [];
        $options['conditions'] = ['ArticlesOrder.organization_id' => (int) $user->organization['Organization']['id'],
                                    'ArticlesOrder.stato' => 'QTAMAXORDER',
                                    'ArticlesOrder.send_mail' => 'N',
                                    'Article.stato' => 'Y'];
        /*
         * PRODGAS e' una promozione e l'ordine e' vuoto (order_id => prod_gas_promotion_id)
         */
        if($user->organization['Organization']['type']=='GAS')  
            $options['conditions'] += ['Order.state_code' => 'OPEN'];
        $options['recursive'] = 1;     
        $ArticlesOrder->unbindModel(['belongsTo' => ['Cart']]);

        $articlesOrderResults = $ArticlesOrder->find('all', $options);
        
        echo "trovati ".count($articlesOrderResults)." articoli da inviare mail notifica \n";
        foreach ($articlesOrderResults as $articlesOrderResult) {
         
            $send_mail = false;
            
            if($articlesOrderResult['Order']['des_order_id']>0) {
                /*
                 * ordine DES => ctrl se sono TITOLARE, invio mail ai referenti (non ai titolari perche' loro non posso accedere all'ordine)
                 */
                App::import('Model', 'DesOrder');
                $DesOrder = new DesOrder();

                $DesOrder->unbindModel(['belongsTo' => ['De']]);
                
                $options = [];
                $options['conditions'] = ['DesOrder.id' => $articlesOrderResult['Order']['des_order_id']];
                $options['fields'] = ['DesSupplier.id', 'DesSupplier.des_id', 'DesSupplier.own_organization_id'];
                $options['recursive'] = 1;
                $desOrdersResults = $DesOrder->find('first', $options);
                /*
                echo "<pre>";
                print_r($desOrdersResults);   
                echo "</pre>";                 
                */
                if($desOrdersResults['DesSupplier']['own_organization_id']==$user->organization['Organization']['id']) {    
                    
                    /*    
                     * sono il titolare 
                     * 
                     * ricerco i titolari, non + perche' loro non posso accedere all'ordine
                    App::import('Model', 'DesSuppliersReferent');
                    $DesSuppliersReferent = new DesSuppliersReferent();                  
             
                    $DesSuppliersReferent->unbindModel(['belongsTo' => ['De', 'DesSupplier']]);
                    
                    $options = [];
                    $options['conditions'] = ['DesSuppliersReferent.des_id' => $desOrdersResults['DesSupplier']['des_id'],
                                                    'DesSuppliersReferent.des_supplier_id' => $desOrdersResults['DesSupplier']['id'],
                                                    'DesSuppliersReferent.organization_id' => $user->organization['Organization']['id'],
                                                    'DesSuppliersReferent.group_id' => Configure::read('group_id_titolare_des_supplier')];
                    $options['recursive'] = 1;
                    $desSuppliersReferentResults = $DesSuppliersReferent->find('all', $options);                  
                     */ 
                    
                    $send_mail = true;
                }
                else
                    $send_mail = false;
            }
            else 
                $send_mail = true;
            
            if($send_mail) {
                    /*
                     * estraggo i referenti
                     */
                    $mail_destinatati = [];
                    switch ($user->organization['Organization']['type']) {
                        case 'GAS':
                                App::import('Model', 'SuppliersOrganizationsReferent'); 
                                $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
                                $conditions = ['User.block' => 0,
                                              'SuppliersOrganization.id' => $articlesOrderResult['Order']['supplier_organization_id']];
                                $mail_destinatati = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions, $orderBy = null, $modalita = 'CRON');

                            break;
                        /*
                         * PRODGAS e' una promozione e l'ordine e' vuoto (order_id => prod_gas_promotion_id)
                         */
                        case 'PRODGAS':
                                App::import('Model', 'User');
                                $User = new User;
                                $mail_destinatati = $User->getUsersComplete($user);       
                            break;
                    }

                    if(!empty($mail_destinatati)) {
                       $this->_mailReferentiQtaMaxSendMail($user, $articlesOrderResult, $mail_destinatati, $debug);
                    }
            }
         } // end foreach($articlesOrderResults as $numResult => $articlesOrderResult)
    }

   private function _mailReferentiQtaMaxSendMail($user, $articlesOrderResult, $mail_destinatati, $debug) {
        
        App::import('Model', 'Mail');
        $Mail = new Mail;
    
        $Email = $Mail->getMailSystem($user);
    
        $body_mail_final = "";
        $body_mail_final .= "<br />";
        if (!empty($articlesOrderResult['Article']['img1']) && file_exists($this->AppRoot . Configure::read('App.img.upload.article') . DS . $articlesOrderResult['Article']['organization_id'] . DS . $articlesOrderResult['Article']['img1'])) {
            $body_mail_final .= '<img width="50" class="userAvatar" src="https://www.portalgas.it' . Configure::read('App.web.img.upload.article') . '/' . $articlesOrderResult['Article']['organization_id'] . '/' . $articlesOrderResult['Article']['img1'] . '" /> ';
        }
        $body_mail_final .= "L'articolo ";
        $body_mail_final .= "<b>" . $articlesOrderResult['ArticlesOrder']['name'] . '</b> ';
        $body_mail_final .= 'ha raggiunto la quantit&agrave; massima (' . $articlesOrderResult['ArticlesOrder']['qta_massima_order'] . ') ';
        $body_mail_final .= 'che hai settato quando l\'hai associato all\'ordine';

        echo '<h2>tratto L\'articolo ' . $articlesOrderResult['ArticlesOrder']['name'] . ' (' . $articlesOrderResult['Article']['id'] . ')</h2>';
        echo $body_mail_final;

        $subject_mail = $this->appHelper->_organizationNameError($user->organization) . ", articolo " . $articlesOrderResult['ArticlesOrder']['name'] . " ha raggiunto la quantita' massima";
        $Email->subject($subject_mail);

        foreach ($mail_destinatati as $numResult => $result) {

            $name = $result['User']['name'];
            $mail = $result['User']['email'];
            $mail2 = $result['UserProfile']['email'];

            $username = $result['User']['username'];

            echo "\n\n".$numResult . ") tratto l'utente " . $name . ', username ' . $username;

            $Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
            $Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))]);

            if ($numResult == 0)
                echo $body_mail_final;

            $mailResults = $Mail->send($Email, [$mail2, $mail], $body_mail_final, $debug);
            
        } // end loop users

        /*
         * ho inviato la mail, update send_mail = 'Y' cosi' non invia + la mail 
         *      a meno che ArticlesOrder.stato non torna a Y
         */
        echo "\n\nHo inviato la MAIL, porto send_mail = Y cosi' non invia + la mail a meno che ArticlesOrder.stato non torna a Y \n";
        $sql = "UPDATE " . Configure::read('DB.prefix') . "articles_orders
                SET
                    send_mail = 'Y', modified = '" . date('Y-m-d H:i:s') . "'
                WHERE
                    organization_id = " . (int) $user->organization['Organization']['id'] . "
                    and order_id = " . $articlesOrderResult['ArticlesOrder']['order_id'] . "
                    and article_organization_id = " . $articlesOrderResult['ArticlesOrder']['article_organization_id'] . " 
                    and article_id = " . $articlesOrderResult['ArticlesOrder']['article_id'];
        echo $sql . "\n";
        try {
            $Mail->query($sql);
        } catch (Exception $e) {
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }
   }
   
    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * se un Order.qta_massima di Order.qta_massima_um ha raggiunto la quantita' massimo
     *  => invio mail e update send_mail_qta_massima = 'N'
     *
     * => chiudo l'ordine 
     */
    public function mailReferentiOrderQtaMax($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        echo date("d/m/Y") . " - " . date("H:i:s") . " Mail ai referenti se la quantit&agrave; massima dell'ordine ha raggiunto il limite \n";

        App::import('Model', 'Order');
        $Order = new Order;

        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

        App::import('Model', 'Mail');
        $Mail = new Mail;

        $Email = $Mail->getMailSystem($user);
        
        /*
         * estraggo gli ordini OPEN con un limite sull'importo
         */

        $Order = new Order;

        $Order->unbindModel(['belongsTo' => ['Delivery']]);

        $options = [];
        $options['conditions'] = ['Order.organization_id' => (int) $user->organization['Organization']['id'],
                                'Order.isVisibleBackOffice' => 'Y',
                                'Order.state_code' => 'OPEN',
                                'Order.qta_massima != ' => 0,
                                'Order.send_mail_qta_massima' => 'Y'];
        $options['recursive'] = 0;
        $options['fields'] = ['Order.id', 'Order.qta_massima', 'Order.qta_massima_um', 'Order.supplier_organization_id', 'SuppliersOrganization.name'];
        $options['order'] = ['Order.id'];
        $orderResults = $Order->find('all', $options);
        
        foreach ($orderResults as $numResult => $orderResult) {

            $totQuantita = $Order->getTotQuantitaArticlesOrder($user, $orderResult, $debug);

            if($debug)
                echo "\n" . 'Ordine ' . $orderResult['SuppliersOrganization']['name'] . ' (' . $orderResult['Order']['id'] . ') ha un limite quantita impostata ' . $orderResult['Order']['qta_massima'] . " (" . $orderResult['Order']['qta_massima_um'] . ") => quantita totale acquistata " . $totQuantita . " (in GR o ML o PZ)\n";

            /*
             *  per il confronto riporto il totale in KG, LT
             */
            if ($orderResult['Order']['qta_massima_um'] != 'PZ')
                $totQuantita = ($totQuantita / 1000);

            if($debug)
                echo "\n" . 'CTRL totQuantita ' . $totQuantita . ' >= Order.qta_massima ' . $orderResult['Order']['qta_massima'] . " =>  INVIO MAIL \n";


            if ($totQuantita >= $orderResult['Order']['qta_massima']) {

                $body_mail_final = "";
                $body_mail_final .= "<br />";
                $body_mail_final .= "L'ordine ";
                $body_mail_final .= "<b>" . $orderResult['SuppliersOrganization']['name'] . '</b> ';
                if ($orderResult['Order']['qta_massima_um'] == 'PZ')
                    $body_mail_final .= 'ha raggiunto la quantit&agrave; di ' . $totQuantita . ' pezzi:';
                else
                    $body_mail_final .= 'ha raggiunto la quantit&agrave; ' . $totQuantita . $orderResult['Order']['qta_massima_um'] . ':';

                $body_mail_final .= ' quando hai creato l\'ordine hai settato un limite di ' . $orderResult['Order']['qta_massima'] . $orderResult['Order']['qta_massima_um'];
                $body_mail_final .= '<p>L\'ordine egrave; stato chiuso e i gasisti non possono piugrave; effettuare acquisti';
                $body_mail_final .= '<br /><a target="_blank" href="http://manuali.portalgas.it/gestione_degli_ordini.php#il-tab-gestione-durante-l-ordine">http://manuali.portalgas.it/gestione_degli_ordini.php#il-tab-gestione-durante-l-ordine</a>';

                if($debug)
                    echo "\n" . '<h2>tratto L\'ordine ' . $orderResult['SuppliersOrganization']['name'] . '</h2>';
                if($debug)
                    echo "\n" . $body_mail_final;

                $subject_mail = $this->appHelper->_organizationNameError($user->organization) . ", ordine " . $orderResult['SuppliersOrganization']['name'] . " ha raggiunto la quantita' di ";
                if ($orderResult['Order']['qta_massima_um'] == 'PZ')
                    $subject_mail .= $totQuantita . ' pezzi';
                else
                    $subject_mail .= $totQuantita . $orderResult['Order']['qta_massima_um'];

                $Email->subject($subject_mail);

                /*
                 * estraggo i referenti
                 */
                $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

                $conditions = ['User.block' => 0,
                               'SuppliersOrganization.id' => $orderResult['Order']['supplier_organization_id']];
                $results = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions, $orderBy = null, $modalita = 'CRON');

                foreach ($results as $numResult => $result) {

                    $name = $result['User']['name'];
                    $mail = $result['User']['email'];
                    $mail2 = $result['UserProfile']['email'];
                    
                    $username = $result['User']['username'];

                    echo "\n" . $numResult . ") tratto l'utente " . $name . ', username ' . $username;

                    $Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
                    $Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))]);

                    if ($numResult == 0)
                        echo $body_mail_final;

                    $mailResults = $Mail->send($Email, [$mail2, $mail], $body_mail_final, $debug);
                } // end loop users

                /*
                 * ho inviato la mail, update send_mail_importo_massimo = 'N' cosi' non invia + la mail 
                 * chiudo l'ordine
                 */
                $ieri = date('Y-m-d', mktime(0,0,0,date('m'),date('d')-1,date('Y')));
                $sql = "UPDATE " . Configure::read('DB.prefix') . "orders
                       SET
                        send_mail_qta_massima = 'N',
                        state_code = 'PROCESSED-BEFORE-DELIVERY',
                        data_fine = '" . $ieri . "',
                        modified = '" . date('Y-m-d H:i:s') . "'
                   WHERE
                        organization_id = " . (int) $user->organization['Organization']['id'] . "
                        and id = " . $orderResult['Order']['id'];
                if($debug)
                    echo $sql . "\n";
                try {
                    $Order->query($sql);
                } catch (Exception $e) {
                    CakeLog::write('error', $sql);
                    CakeLog::write('error', $e);
                }
            } // end if($totImporto >= $orderResult['Order']['importo_massimo']) 
                
        } // loop foreach ($orderResults as $numResult => $orderResult) 
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * se un Order.importo_massimo l'importo massimo
     *  => invio mail e update send_mail_importo_massimo = 'N'
     *
     * => chiudo l'ordine
     */
    public function mailReferentiOrderImportoMax($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        echo date("d/m/Y") . " - " . date("H:i:s") . " Mail ai referenti se l'importo massimo dell'ordine ha raggiunto il limite \n";

        App::import('Model', 'Order');
        $Order = new Order;

        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

        App::import('Model', 'Mail');
        $Mail = new Mail;

        $Email = $Mail->getMailSystem($user);
        
        /*
         * estraggo gli ordini OPEN con un limite sull'importo
         */

        $Order = new Order;

        $Order->unbindModel(['belongsTo' => ['Delivery']]);

        $options = [];
        $options['conditions'] = ['Order.organization_id' => (int) $user->organization['Organization']['id'],
                                'Order.isVisibleBackOffice' => 'Y',
                                'Order.state_code' => 'OPEN',
                                'Order.importo_massimo != ' => 0,
                                'Order.send_mail_importo_massimo' => 'Y'];
        $options['recursive'] = 0;
        $options['fields'] = ['Order.id', 'Order.importo_massimo', 'Order.supplier_organization_id', 'SuppliersOrganization.name'];
        $options['order'] = ['Order.id'];
        $orderResults = $Order->find('all', $options);

        foreach ($orderResults as $numResult => $orderResult) {

            $totImporto = $Order->getTotImportoArticlesOrder($user, $orderResult['Order']['id'], $debug);

            if($debug)
                echo "\n" . 'Ordine ' . $orderResult['SuppliersOrganization']['name'] . ' (' . $orderResult['Order']['id'] . ') ha un limite a ' . $orderResult['Order']['importo_massimo'] . ' &euro; => raggiunto ' . $totImporto . '&euro;' . "\n";

            if ($totImporto >= $orderResult['Order']['importo_massimo']) {

                $body_mail_final = "";
                $body_mail_final .= "<br />";
                $body_mail_final .= "L'ordine ";
                $body_mail_final .= "<b>" . $orderResult['SuppliersOrganization']['name'] . '</b> ';
                $body_mail_final .= 'ha raggiunto l\'importo ' . $totImporto . '&euro;:';
                $body_mail_final .= ' quando hai creato l\'ordine hai settato un limite di ' . $orderResult['Order']['importo_massimo'] . '&euro;';
                $body_mail_final .= '<p>L\'ordine egrave; stato chiuso e i gasisti non possono piugrave; effettuare acquisti';
                $body_mail_final .= '<br /><a target="_blank" href="http://manuali.portalgas.it/gestione_degli_ordini.php#il-tab-gestione-durante-l-ordine">http://manuali.portalgas.it/gestione_degli_ordini.php#il-tab-gestione-durante-l-ordine</a>';

                if($debug)
                    echo "\n" . '<h2>tratto L\'ordine ' . $orderResult['SuppliersOrganization']['name'] . '</h2>';
                if($debug)
                    echo "\n" . $body_mail_final;

                $subject_mail = $this->appHelper->_organizationNameError($user->organization) . ", ordine " . $orderResult['SuppliersOrganization']['name'] . " ha raggiunto l'importo di " . $totImporto . "€";
                $Email->subject($subject_mail);

                /*
                 * estraggo i referenti
                 */
                $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

                $conditions = ['User.block' => 0,
                               'SuppliersOrganization.id' => $orderResult['Order']['supplier_organization_id']];
                $results = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions, $orderBy = null, $modalita = 'CRON');

                foreach ($results as $numResult => $result) {

                    $name = $result['User']['name'];
                    $mail = $result['User']['email'];
                    $mail2 = $result['UserProfile']['email'];
                    
                    $username = $result['User']['username'];

                    echo "\n" . $numResult . ") tratto l'utente " . $name . ', username ' . $username;

                    $Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
                    $Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))]);

                    if ($numResult == 0)
                        echo $body_mail_final;

                    $mailResults = $Mail->send($Email, [$mail2, $mail], $body_mail_final, $debug);
                    
                } // end loop users

                /*
                 * ho inviato la mail, update send_mail_importo_massimo = 'N' cosi' non invia + la mail 
                 * chiudo l'ordine
                 */
                $ieri = date('Y-m-d', mktime(0,0,0,date('m'),date('d')-1,date('Y')));
                $sql = "UPDATE " . Configure::read('DB.prefix') . "orders
                       SET
                        send_mail_importo_massimo = 'N',
                        state_code = 'PROCESSED-BEFORE-DELIVERY',
                        data_fine = '" . $ieri . "',                        
                        modified = '" . date('Y-m-d H:i:s') . "'
                   WHERE
                        organization_id = " . (int) $user->organization['Organization']['id'] . "
                        and id = " . $orderResult['Order']['id'];
                if($debug)
                    echo $sql . "\n";
                try {
                    $Order->query($sql);
                } catch (Exception $e) {
                    CakeLog::write('error', $sql);
                    CakeLog::write('error', $e);
                }
            } // end if($totImporto >= $orderResult['Order']['importo_massimo']) 
        } // loop foreach ($orderResults as $numResult => $orderResult)     
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * estraggo le consegne ricorsive di oggi
     *      estraggo per data_master_reale 
     *      ricalcolo la ricorsione partendo da data_master 
     *          data_master       => data_copy
     *          data_master_reale => data_copy_reale
     *          data_copy         => calcolo nuova ricorsione
     *          data_copy_reale   => calcolo nuova ricorsione
     *          nuova consegna con data_copy_reale
     */

    public function loopsDeliveries($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        echo date("d/m/Y") . " - " . date("H:i:s") . " Consegne: creo le consegne ricorsive \n";

        App::import('Model', 'LoopsDelivery');
        $LoopsDelivery = new LoopsDelivery;

        /*
         * faccio CURDATE() - INTERVAL 1 DAY cosi aspetto che sia chiusa la master e prendo quelle del giorno precedente (il cron parte alle 0.35)
         */
        $options = [];
        $options['conditions'] = ['LoopsDelivery.organization_id' => (int) $user->organization['Organization']['id'],
                                  'DATE(LoopsDelivery.data_master_reale) = CURDATE() - INTERVAL 1 DAY'];
        $options['recursive'] = -1;
        $loopsDeliveryResults = $LoopsDelivery->find('all', $options);

        if($debug) {
            echo '<h2>Consegne ricorsive</h2>';
            echo "<pre>";
            print_r($loopsDeliveryResults);
            echo "</pre>";
        }

        if (!empty($loopsDeliveryResults)) 
        foreach ($loopsDeliveryResults as $numResult => $loopsDeliveryResult) {
                
            $create = true; // in LoopsDeliveries::testing simulo
            $LoopsDelivery->creating($user, $loopsDeliveryResult, $create);
            
        } // end foreach ($loopsDeliveryResults as $loopsDeliveryResult)
    }

    public function loopsOrders($organization_id) {

    $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        echo date("d/m/Y") . " - " . date("H:i:s") . " Ordini: duplica gli ordini ricorsivi \n";
    }
 
    public function archiveStatistics($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 

        echo "\rTratto l'organization (" . $organization_id . ") con pagamento ".$user->organization['Template']['payToDelivery']." \r";
        
        App::import('Model', 'Statistic');
        $Statistic = new Statistic;
        
        $Statistic->archive($user, $debug);
    }
    
    public function createPdfSingleUser($organization_id, $delivery_id, $user_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        App::import('Model', 'Statistic');
        $Statistic = new Statistic;

        $Statistic->create_pdf_single_user($user, $delivery_id, $user_id, $debug);
    }

    /*
     * gli articoli messi nel carrello per l'utente Dispensa (con il modulo gestione degli acquisti nel dettaglio o gestione colli)
     * vengono messi in Dispensa quando si chiude la consegna
     * 
     * gli articoli dal Carrello alla Dispensa vengono copiati perche' in Cart servono per conteggi
     * eseguire il Cron prima di mezzanotte!
     */

    public function articlesFromCartToStoreroom($organization_id, $debug = true, $delivery_id = 0) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        try {
            if($debug)
                echo "Gli articoli messi nel carrello per l'utente Dispensa (con il modulo gestione degli acquisti nel dettaglio o gestione colli) \n";
            if($debug)
                echo "vengono messi in Dispensa quando si chiude la consegna \n";
            if($debug)
                echo "gli articoli dal Carrello alla Dispensa vengono copiati perche' in Cart servono per conteggi \n";

            if ($user->organization['Organization']['hasStoreroom'] == 'N' || $user->organization['Organization']['hasStoreroomFrontEnd'] == 'N') {
                if($debug)
                    echo "Organizzazione non abilitata a gestire la dispensa (hasStoreroom = N || hasStoreroomFrontEnd = N) \n";
                return;
            }

            App::import('Model', 'Storeroom');
            $Storeroom = new Storeroom;

            $storeroomUser = $Storeroom->getStoreroomUser($user);
            if (empty($storeroomUser)) {
                if($debug)
                    echo "Non esiste lo user dispensa \n";
                return;
            }


            /*
             * estraggo tutti gli ordini delle consegne
             */
            $sql = "SELECT Delivery.id, `Order`.id, Cart.*, Article.*, ArticlesOrder.*  
                   FROM
                         " . Configure::read('DB.prefix') . "deliveries Delivery,
                         `" . Configure::read('DB.prefix') . "orders` `Order`,
                         " . Configure::read('DB.prefix') . "articles_orders ArticlesOrder,
                         " . Configure::read('DB.prefix') . "articles Article,
                         " . Configure::read('DB.prefix') . "carts Cart 
                   WHERE
                        Delivery.organization_id = " . (int) $organization_id . "
                        AND `Order`.organization_id = " . (int) $organization_id . "
                        AND ArticlesOrder.organization_id = " . (int) $organization_id . "
                        AND Cart.organization_id = " . (int) $organization_id . "
                        AND Delivery.stato_elaborazione = 'OPEN'
                        AND `Order`.delivery_id = Delivery.id
                        AND ArticlesOrder.order_id = `Order`.id 
                        AND ArticlesOrder.article_id = Article.id 
                        AND ArticlesOrder.article_organization_id = Article.organization_id 
                        AND Cart.order_id = ArticlesOrder.order_id 
                        AND Cart.article_id = ArticlesOrder.article_id 
                        AND Cart.article_organization_id = ArticlesOrder.article_organization_id 
                        AND Cart.inStoreroom = 'N' 
                        AND `Order`.isVisibleFrontEnd = 'Y'  and `Order`.isVisibleFrontEnd = 'Y'
                        AND Delivery.isVisibleFrontEnd = 'Y' and Delivery.isVisibleFrontEnd = 'Y' 
                        AND Cart.user_id = " . $storeroomUser['User']['id'];
            if (!empty($delivery_id))
                $sql .= " AND Delivery.id = " . (int) $delivery_id;
            else
                $sql .= " AND Delivery.data = CURDATE() ";
            $sql .= " ORDER BY Delivery.id, Cart.order_id, Cart.article_id";
            if($debug)
                echo $sql . "\n";
            $results = $Storeroom->query($sql);
            if($debug)
                echo "Trattero " . count($results) . " articoli acquistati dall'utente Dispensa per le consegna che si chiudono oggi \n";

            foreach ($results as $result) {

                if ($result['Cart']['qta'] == 0)
                    $qta = $result['Cart']['qta_forzato'];
                else
                    $qta = $result['Cart']['qta'];

                /*
                 * ctrl che non ci sia gia' un articolo in dispensa 
                 */
                $conditions = ['User.id' => $storeroomUser['User']['id'],
                            'Storeroom.delivery_id' => 0,
                            'Article.id' => $result['Article']['id']];
                $ctrlResults = $Storeroom->getArticlesToStoreroom($user, $conditions);
                /*
                if ($debug) {
                    echo "<pre>Articolo gia presente in Storeroom \n ";
                    print_r($ctrlResults);
                    echo "</pre>";
                }
                */
                $storeroom = [];
                if (!empty($ctrlResults)) {
                    $storeroom['Storeroom']['id'] = $ctrlResults[0]['Storeroom']['id'];
                    $storeroom['Storeroom']['qta'] = ($ctrlResults[0]['Storeroom']['qta'] + $qta);
                } else
                    $storeroom['Storeroom']['qta'] = $qta;

                $storeroom['Storeroom']['organization_id'] = $organization_id;
                $storeroom['Storeroom']['delivery_id'] = 0; // $result['Delivery']['id']; se valorizzato e' perche' e' stato acquistato
                $storeroom['Storeroom']['user_id'] = $storeroomUser['User']['id']; // dispensa
                $storeroom['Storeroom']['article_organization_id'] = $result['Article']['organization_id'];
                $storeroom['Storeroom']['article_id'] = $result['Article']['id'];
                $storeroom['Storeroom']['name'] = $result['ArticlesOrder']['name'];
                $storeroom['Storeroom']['prezzo'] = $result['ArticlesOrder']['prezzo'];
                $storeroom['Storeroom']['stato'] = 'Y';
                $Storeroom->create();
                /*
                if ($debug) {
                    print_r($storeroom);
                }
                */
                if ($Storeroom->save($storeroom))
                    if($debug)
                        echo "OK, Inserito l'articolo (" . $storeroom['Storeroom']['article_id'] . ") " . $storeroom['ArticlesOrder']['name'] . " in dispensa con qta " . $storeroom['Storeroom']['qta'] . "\n";
                    else
                    if($debug)
                        echo "ERRORE, inserendo l'articolo (" . $storeroom['Storeroom']['article_id'] . ") " . $storeroom['ArticlesOrder']['name'] . " in dispensa \n";

                /*
                 * setto Cart.inStoreroom a Y cosi' non lo elaboro +
                 */
                $sql = "UPDATE " . Configure::read('DB.prefix') . "carts as Cart
                         SET Cart.inStoreroom = 'Y'
                       WHERE
                            Cart.organization_id = " . (int) $organization_id . "
                           AND Cart.order_id = " . $result['Order']['id'] . " 
                            AND Cart.article_organization_id = " . $result['Article']['organization_id'] . " 
                            AND Cart.article_id = " . $result['Article']['id'] . " 
                            AND Cart.user_id = " . $storeroomUser['User']['id'];
                //if($debug) echo $sql."\n";
                $results = $Storeroom->query($sql);

                if($debug)
                    echo "Update Cart con inStoreroom = Y \n";
            } // foreach($results as $result)
        } catch (Exception $e) {
            if($debug)
                echo '<br />UtilsCrons::articlesFromCartToStoreroom()<br />' . $e;
        }
    }

    /*
     * DES
     *  cancella DesOrder con 
     *      data_fine_max scaduta / DesOrder.state_code = 'CLOSE'
     *      ordini non + associati perche' portati in Statistiche
     */
    public function desOrdersDelete($des_id, $des_order_id = 0, $debug=true) {

        if($debug)
            echo date("d/m/Y") . " - " . date("H:i:s") . " Controllo se cancellare i DesOrder  \n";
        if($debug)
            echo " Tratto il DES $des_id \n";

        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder();
        $DesOrder->deleteScaduti($des_id, $des_order_id, $debug);
    }

    /*
     * per ogni GAS setto SupplierOrganization.owner_articles = DES o REFERENT
     *
     * cron disabilitato, metodo setSupplierOrganizationOwnerArticles richiamato solo quando cambia il titolare ordine DES
     */
    public function desSetSupplierOrganizationOwnerArticles($organization_id, $debug=true) {
        
        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        App::import('Model', 'DesSupplier');
        $DesSupplier = new DesSupplier;
        
        $supplier_id=0;
                
        if($user->organization['Organization']['hasDes']=='Y') {
            
            if(empty($user->des_id)) {
                App::import('Model', 'DesOrganization');
                $DesOrganization = new DesOrganization;
            
                $options = [];
                $options['conditions'] = ['DesOrganization.organization_id' => $user->organization['Organization']['id']];
                $options['recursive'] = -1;
                $desOrganizationsResults = $DesOrganization->find('all', $options);
                foreach($desOrganizationsResults as $desOrganizationsResult) {
                    $user->des_id = $desOrganizationsResult['DesOrganization']['des_id'];

                    if ($debug) {
                        echo date("d/m/Y") . " - " . date("H:i:s") . " per il GAS [$organization_id] del DES [".$user->des_id."] setto SupplierOrganization.owner_articles = DES o REFERENT  \n";
                        if(!empty($supplier_id))
                            echo date("d/m/Y") . " - " . date("H:i:s") . " per il produttore [$supplier_id] \n";
                        else
                            echo date("d/m/Y") . " - " . date("H:i:s") . " per TUTTI i produttori \n";
                    }
                    
                    $DesSupplier->setSupplierOrganizationOwnerArticles($user, $supplier_id, $debug);
                    
                } // foreach($desOrganizationsResults as $desOrganizationsResult)
            } // if(empty($user->des_id)) 
            else {
                if ($debug) {
                    echo date("d/m/Y") . " - " . date("H:i:s") . " per il GAS [$organization_id] del DES [$des_id] setto SupplierOrganization.owner_articles = DES o REFERENT  \n";
                    if(!empty($supplier_id))
                        echo date("d/m/Y") . " - " . date("H:i:s") . " per il produttore [$supplier_id] \n";
                    else
                        echo date("d/m/Y") . " - " . date("H:i:s") . " per TUTTI i produttori \n";
                }

                $DesSupplier->setSupplierOrganizationOwnerArticles($user, $supplier_id, $debug);                
            }
        } // end if($user->organization['Organization']['hasDes']=='Y')
    }
    
    /*
     * key ArticlesOrder $organization_id $order_id, $article_organization_id, $article_id
     */
    public function articlesOrdersQtaCart($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        /*
         * Aggiorna il totale della quantita' acquistata per ogni articolo
         */
        if($debug)
            echo date("d/m/Y") . " - " . date("H:i:s") . " Aggiorna il totale della quantita' acquistata per ogni articolo (ArticlesOrder.qta_cart) e se ArticlesOrder.qta_massima_order > 0 anche ArticlesOrder.stato \n";

        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;

        $options = [];
        $options['conditions'] = ['ArticlesOrder.organization_id' => $organization_id,
                        // per debug    'ArticlesOrder.order_id' => 20563
                        ];
        $options['recursive'] = -1;
        $results = $ArticlesOrder->find('all', $options);

        if($debug)
            echo "Trovati ".count($results)." ArticlesOrder \n";

        $debug = false; // log troppo verbosi

        foreach ($results as $result) {

            $organization_id = $result['ArticlesOrder']['organization_id'];
            $order_id = $result['ArticlesOrder']['order_id'];
            $article_organization_id = $result['ArticlesOrder']['article_organization_id'];
            $article_id = $result['ArticlesOrder']['article_id'];

            $ArticlesOrder->aggiornaQtaCart_StatoQtaMax($organization_id, $order_id, $article_organization_id, $article_id, $debug);
        }
    }

    public function articlesBio($organization_id) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS', 'PRODGAS', 'SOCIALMARKET']);
        if(empty($user)) 
            return; 
        
        echo date("d/m/Y") . " - " . date("H:i:s") . " articlesBio() ";

        try {
            App::import('Model', 'Article');
            $Article = new Article;

            $Article->syncronizeArticleTypeBio($user, 0, false);
        } catch (Exception $e) {
            echo '<br />UtilsCrons::articlesBio()<br />' . $e;
        }
    }

    public function deleteCart($organization_id) {
        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return;         
    }

    public function usersGmaps($organization_id, $debug=true) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return;         
        
        echo date("d/m/Y") . " - " . date("H:i:s") . " Dall'indirizzo cerca lng e lat per organization_id $organization_id \n";

        /*
         * userprofile
         *
         * PHP Fatal error:  Call to undefined function jimport()
         * jimport( 'joomla.user.helper' );
         * define('JPATH_PLATFORM', dirname(__FILE__));
         * require($this->AppRoot.'/libraries/joomla/user/helper.php');
         */

        App::import('Model', 'User');
        $User = new User;

        $options = [];
        $options['conditions'] = ['User.organization_id' => $organization_id];
        $options['order'] = Configure::read('orderUser');
        $options['recursive'] = -1;
        $results = $User->find('all', $options);

        /*
          echo "<pre>";
          print_r($options);
          print_r($results);
          echo "</pre>";
         */

        $tot_user_elaborati = 0;
        foreach ($results as $numResult => $result) {

            $userProfile = $this->_getProfileUser($result['User']['id']);
            /*
            echo "<pre>";
            print_r($userProfile);
            echo "</pre>";
            */ 
            $lat = $this->_getProfileUserValue($userProfile, 'profile.lat');
            $lng = $this->_getProfileUserValue($userProfile, 'profile.lng');
            $address = $this->_getProfileUserValue($userProfile, 'profile.address');
            $city = $this->_getProfileUserValue($userProfile, 'profile.city');
            $cap = $this->_getProfileUserValue($userProfile, 'profile.postal_code');

            // echo "\n Tratto lo user ".$result['User']['id'].' '.$result['User']['username'].' coordinate '.$lat.' '.$lng.' - address '.$address.' '.$city;
            if ($tot_user_elaborati <= 10 && $lat == '' && $lng == '') {

                if ($address != '' && $city != '') {

                    if($debug)
                        echo "\n tot_user_elaborati " . $tot_user_elaborati;

                    /* if($debug) {
                      echo "<pre>";
                      print_r($userProfile);
                      echo "</pre>";
                      }
                     */

                    $address = $results[$numResult]['Profile']['gmaps'] = $address . ' ' . $city . ' ' . $cap;

                    $tot_user_elaborati++;
                    $coordinate = $this->_gmap($address, $debug);

                    if($debug) {
                        echo "<pre>";
                        print_r($coordinate);
                        echo "</pre>";
                    }

                    if (!empty($coordinate)) {
                        $lat = str_replace(",", ".", $coordinate['lat']);
                        $lng = str_replace(",", ".", $coordinate['lng']);

                        /*
                         * ctrl se esiste gia' lat
                         * */
                        $sql = 'select user_id from ' . Configure::read('DB.portalPrefix') . 'user_profiles where user_id = ' . $result['User']['id'] . ' and profile_key = "profile.lat"';
                        $ctrl = $User->query($sql);
                        if(empty($ctrl))
                            $sql = 'INSERT INTO ' . Configure::read('DB.portalPrefix') . 'user_profiles VALUES (' . $result['User']['id'] . ', "profile.lat", "\"' . $lat . '\"" , 10 )';
                        else
                            $sql = 'UPDATE '.Configure::read('DB.portalPrefix').'user_profiles set profile_value = "\"'.$lat.'\"" WHERE user_id =  '.$result['User']['id'].' and profile_key ="profile.lat"';
                        echo "\n " . $sql;
                        $executeInsert = $User->query($sql);

                        /*
                         * ctrl se esiste gia' lng
                         * */
                        $sql = 'select user_id from ' . Configure::read('DB.portalPrefix') . 'user_profiles where user_id = ' . $result['User']['id'] . ' and profile_key = "profile.lng"';
                        $ctrl = $User->query($sql);
                        if(empty($ctrl))
                            $sql = 'INSERT INTO ' . Configure::read('DB.portalPrefix') . 'user_profiles VALUES (' . $result['User']['id'] . ', "profile.lng", "\"' . $lng . '\"" , 11 )';
                        else
                            $sql = 'UPDATE '.Configure::read('DB.portalPrefix').'user_profiles set profile_value = "\"'.$lng.'\"" WHERE user_id =  '.$result['User']['id'].' and profile_key ="profile.lng"';                        
                        echo "\n " . $sql;
                        $executeInsert = $User->query($sql);
                    }
                }  // if($tot_user_elaborati<=10 && $lat!='' && $lng!='')   
            }    // if($address!='' && $city!='')  
        } // foreach ($results as $numResult => $result) 
    }

    /*
     *  anche se $organization_id non mi serve viene passato
     */

    public function suppliersGmaps($organization_id, $debug=true) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " Dall'indirizzo cerca lng e lat \n";

        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

        $options = [];
        $options['conditions'] = ['Supplier.stato' => ['Y','T'], 
                                  'Supplier.lat' => '', 
                                  'Supplier.localita !=' => ''];
        $options['order'] = ['Supplier.id'];
        $options['limit'] = 10;
        $options['recursive'] = -1;
        $results = $Supplier->find('all', $options);
        if($debug) {
            echo "<pre>conditions \n";
            print_r($options['conditions']);
            echo "</pre>";
            echo "<pre>founds \n";
            print_r(count($results));
            echo "</pre>";
        }   
        
        foreach ($results as $numResult => $result) {

            if($debug) {
                echo "<pre>";
                print_r($result);
                echo "</pre>";
            }

            $address = $result['Supplier']['gmaps'] = $result['Supplier']['indirizzo'] . ' ' . $result['Supplier']['localita'] . ' ' . $result['Supplier']['cap'];

            $coordinate = $this->_gmap($address, $debug);

            if($debug) {
                echo "<pre>";
                print_r($coordinate);
                echo "</pre>";
            }

            if (!empty($coordinate)) {
                $lat = str_replace(",", ".", $coordinate['lat']);
                $lng = str_replace(",", ".", $coordinate['lng']);

                $sql = 'UPDATE ' . Configure::read('DB.prefix') . 'suppliers set lat = "' . $lat . '", lng = "' . $lng . '" WHERE id = ' . $result['Supplier']['id'];
                echo "\n " . $sql;
                $executeUpdate = $Supplier->query($sql);
            }

        } // foreach ($results as $numResult => $result) 
    }

    private function _gmap($address, $debug = false) {

        $esito = [];

        $url = "https://maps.google.com/maps/api/geocode/json?sensor=false&key=".Configure::read('GoogleApiKeyServer')."&address=";

        $url = $url . urlencode($address);

        $resp_json = $this->_curl_file_get_contents($url);
        $resp = json_decode($resp_json, true);

        if($debug)
            echo "\n " . $url . ' ' . $resp['status'];


        if ($resp['status'] == 'OK') {
            if (isset($resp['results'][0]))
                $esito = $resp['results'][0]['geometry']['location'];
        } else {
            if($debug) {
                echo "<pre>";
                print_r($resp);
                echo "</pre>";
                echo '<br/>' . $url;
            }
        }

        if (empty($esito)) {
            $esito['lat'] = Configure::read('LatLngNotFound');
            $esito['lng'] = Configure::read('LatLngNotFound');
        }

        return $esito;
    }

    public function usersSuppliersOrganizationsReferents($organization_id, $debug=true) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " Controllo se l'utente e' un referente ed appartiene o no al gruppo \n";

        App::import('Model', 'User');
        $User = new User;

        App::import('Model', 'SuppliersOrganizationsReferent');

        $options = [];
        $options['conditions'] = ['User.organization_id' => (int) $organization_id,
                                  'User.block' => 0];
        $options['fields'] = ['User.id', 'User.name', 'User.email'];
        $options['recursive'] = -1;

        $users = $User->find('all', $options);
        echo "  Estratti " . count($users) . " utenti \n";
        //print_r($users);
        foreach ($users as $user) {

            echo "\nTratto l'utente " . $user['User']['name'] . " " . $user['User']['email'] . " (" . $user['User']['id'] . ") \n";

            /*
             *  ctrl se e' gia' referent,
             *  se NO lo e' associo lo joomla.users al gruppo referenti in joomla.user_usergroup_map
             */
            $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
            $options = [];
            $options['conditions'] = ['SuppliersOrganizationsReferent.organization_id' => $organization_id,
                                      'SuppliersOrganizationsReferent.user_id' => $user['User']['id']];
            $totRows = $SuppliersOrganizationsReferent->find('count', $options);
            if ($totRows == 0) {
                echo "  non ha produttori associati: lo <span style=color:red>cancello</span> dal gruppo referenti \n";
                $User->joomlaBatchUser(Configure::read('group_id_referent'), $user['User']['id'], 'del', false);
            } else {
                echo "  ha produttori associati ($totRows): lo <span style=color:green>inserisco</span> dal gruppo referenti \n";
                $User->joomlaBatchUser(Configure::read('group_id_referent'), $user['User']['id'], 'add', false);
            }
        }
    }

    public function filesystemLogDelete($organization_id, $debug=true) {

        date_default_timezone_set('Europe/Rome');

        $data_oggi = date('Ymd');
        $data_oggi_diminuita_logs = date('Ymd', strtotime('-' . Configure::read('GGDeleteLogs') . ' day', strtotime($data_oggi)));
        $data_oggi_diminuita_bk = date('Ymd', strtotime('-' . Configure::read('GGDeleteBackup') . ' day', strtotime($data_oggi)));
        $data_oggi_diminuita_db = date('Ymd', strtotime('-' . Configure::read('GGDeleteDump') . ' day', strtotime($data_oggi)));

        echo date("d/m/Y") . " - " . date("H:i:s") . " Cancello tutti i logs dei cron precedenti al " . $data_oggi_diminuita_logs . " (" . Configure::read('GGDeleteLogs') . " gg rispetto a " . $data_oggi . ") \n";
        echo date("d/m/Y") . " - " . date("H:i:s") . " Cancello tutti i backup del codice precedenti al " . $data_oggi_diminuita_bk . " (" . Configure::read('GGDeleteBackup') . " gg rispetto a " . $data_oggi . ") \n";
        echo date("d/m/Y") . " - " . date("H:i:s") . " Cancello tutti i dump del database precedenti al " . $data_oggi_diminuita_db . " (" . Configure::read('GGDeleteDump') . " gg rispetto a " . $data_oggi . ") \n";

        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');

        $dir_backup = new Folder(Configure::read('App.cron.backup'));
        $dir_dump = new Folder(Configure::read('App.cron.dump'));
        $dir_log = new Folder(Configure::read('App.cron.log'));

        /*
         * file di backup
         */
        echo " \n";
        echo "Tratto la directory " . Configure::read('App.cron.backup') . " \n";
        $files = $dir_backup->find('.*\.tar.gz', true);
        foreach ($files as $file) {
            $pos = strpos($file, ".");
            // echo "file " . $file . " \n";
            if ($pos !== false) {
                $fileNameDate = substr($file, 0, $pos);

                if ($fileNameDate < $data_oggi_diminuita_bk) {
                    echo 'DELETE file ' . $file . ' con data ' . $fileNameDate . ' inferiore di ' . $data_oggi_diminuita_bk . "\n";
                    $fl = new File(Configure::read('App.cron.backup') . DS . $file);
                    $fl->delete();
                    $fl->close();
                }
                else
                    echo 'file '.$file.' con data '.$fileNameDate.' maggiore di '.$data_oggi_diminuita_bk." => non lo cancello \n";
            }
        }

        /*
         * file di dump
         */
        echo " \n";
        echo "Tratto la directory " . Configure::read('App.cron.dump') . " \n";
        $files = $dir_dump->find('.*\.tar.gz', true);
        foreach ($files as $file) {
            $pos = strpos($file, ".");
            // echo "file " . $file . " \n";         
            if ($pos !== false) {
                $fileNameDate = substr($file, 0, $pos);

                if ($fileNameDate < $data_oggi_diminuita_db) {
                    echo 'DELETE file ' . $file . ' con data ' . $fileNameDate . ' inferiore di ' . $data_oggi_diminuita_db . "\n";
                    $fl = new File(Configure::read('App.cron.dump') . DS . $file);
                    $fl->delete();
                    $fl->close();
                }
                else
                    echo 'file '.$file.' con data '.$fileNameDate.' maggiore di '.$data_oggi_diminuita_db." => non lo cancello \n";
            }
        }

        /*
         * file di log
         */
        echo " \n";
        echo "Tratto la directory " . Configure::read('App.cron.log') . " \n";
        $files = $dir_log->find('.*\.log', true);
        foreach ($files as $file) {
            $pos = strpos($file, "_");

            if ($pos !== false) {
                $fileNameDate = substr($file, 0, $pos);

                if ($fileNameDate < $data_oggi_diminuita_logs) {
                    echo 'DELETE file ' . $file . ' con data ' . $fileNameDate . ' inferiore di ' . $data_oggi_diminuita_logs . "\n";
                    $fl = new File(Configure::read('App.cron.log') . DS . $file);
                    $fl->delete();
                    $fl->close();
                }
               // else
               //   echo 'file '.$file.' con data '.$fileNameDate.' maggiore di '.$data_oggi_diminuita_logs."\n";
            }
        }
    }

    /*
     * per ogni organization scrive un file seo.rss in /rss/
     */
    public function rss($organization_id, $debug = false) {

        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        App::import('Model', 'Rss');
        $Rss = new Rss();
        
        $Rss->cronElabora($user, $this->timeHelper, $debug);
    }

    /* 
     *  S T A T O - E L A B O R A Z I O N E
     *
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * $order_id  se valorizzato setta lo stato_elaborazione di quell'ordine
     */
    public function ordersStatoElaborazione($organization_id, $debug = true, $order_id = 0) {

        if($organization_id==Configure::read('social_market_organization_id'))
            $type = ['SOCIALMARKET'];
        else
            $type = ['GAS'];
        $user = $this->_getObjUserLocal($organization_id, $type);
        if(empty($user))
            return; 
        
        App::import('Model', 'StatoElaborazione');
        $StatoElaborazione = new StatoElaborazione();
        
        $StatoElaborazione->orders($user, $debug, $order_id);
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * $delivery_id  se valorizzato setta lo stato_elaborazione della consegna
     * 
     * Porto le consegne con 
     * tutti gli ordini in stato_elaborazione = CLOSE a Delivery.stato_elaborazione = CLOSE
     */
    public function deliveriesStatoElaborazione($organization_id, $debug = true, $delivery_id = 0) {
        
        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 

        App::import('Model', 'DeliveryLifeCycle');
        $DeliveryLifeCycle = new DeliveryLifeCycle;

        $DeliveryLifeCycle->deleteExpiredWithoutAssociations($user, 0, $debug);
        $DeliveryLifeCycle->deliveriesToClose($user, 0, $debug);
    }
    
    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * $request_payment_id  se valorizzato setta lo stato_elaborazione della richiesta di pagamento 
     * solo per pagamento POST e ON-POST
     */
    public function requestPaymentStatoElaborazione($organization_id, $debug = true, $request_payment_id = 0) {
        
        $user = $this->_getObjUserLocal($organization_id, ['GAS']);
        if(empty($user)) 
            return; 
        
        App::import('Model', 'StatoElaborazione');
        $StatoElaborazione = new StatoElaborazione();
        
        $StatoElaborazione->requestPayment($user, $debug, $request_payment_id);        
    }

    /*
     * DES
     *  se DesOrder.data_fine_max scaduta: DesOrdes.stato da OPEN => BEFORE-TRASMISSION
     */
    public function desOrdersStatoElaborazione($des_id, $des_order_id = 0, $debug=true) {

        if($debug)
            echo date("d/m/Y") . " - " . date("H:i:s") . " Aggiorna lo stato dei DesOrder  \n";
        if($debug)
            echo " Tratto il DES $des_id \n";

        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder();
        
        $DesOrder->statoElaborazione($des_id, $des_order_id, $debug);
    }
     
    public function prodDeliveriesStatoElaborazione($organization_id, $debug = true, $prod_delivery_id = 0) {
    
        $user = $this->_getObjUserLocal($organization_id, ['PROD']);
        if(empty($user)) 
            return; 
        
        App::import('Model', 'StatoElaborazioneProd');
        $StatoElaborazioneProd = new StatoElaborazioneProd();
        
        $StatoElaborazioneProd->prodDeliveries($user, $debug, $prod_delivery_id); 
    }
     
    public function prodGasPromotionsStatoElaborazione($organization_id, $prod_gas_promotion_id = 0, $debug = true) {
    
        $user = $this->_getObjUserLocal($organization_id, ['PRODGAS']);
        if(empty($user)) 
            return; 
        
        App::import('Model', 'StatoElaborazioneProdGas');
        $StatoElaborazioneProdGas = new StatoElaborazioneProdGas();
        
        $StatoElaborazioneProdGas->promotions($user, $prod_gas_promotion_id, $debug); 
    }

    private function _getProfileUser($user_id = 0) {

        App::import('Model', 'User');
        $User = new User;

        $sql = "SELECT profile_key, profile_value 
                    FROM " . Configure::read('DB.portalPrefix') . "user_profiles 
                    WHERE user_id = " . $user_id;
        $userProfile = $User->query($sql);

        return $userProfile;
    }

    /*
      [0] => [[j_user_profiles] => [
          [profile_key] => profile.region
          [profile_value] => "MI"  ]]
     */
    private function _getProfileUserValue($userProfile, $key) {

        $debug = false;

        /* if($debug) {
          echo "<pre>";
          print_r($userProfile);
          echo "</pre>";
          }
         */

        $value = '';
        foreach ($userProfile as $profile) {
            if ($profile['j_user_profiles']['profile_key'] == $key) {
                $value = $profile['j_user_profiles']['profile_value'];
                if (!empty($value))
                    $value = substr($value, 1, strlen($value) - 2);

                if($debug)
                    echo '<br />' . $profile['j_user_profiles']['profile_key'] . ' ' . $key . ' => ' . $profile['j_user_profiles']['profile_value'] . ' ' . $value;

                break;
            }
        }

        return $value;
    }

    private function _curl_file_get_contents($URL) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents)
            return $contents;
        else
            return FALSE;
    }
    
    /*
     * $user = new UserLocal() e non new User() se no override App::import('Model', 'User');
     * type ENUM('GAS', 'PRODGAS', 'PROD', 'PACT')
     */
    private function _getObjUserLocal($organization_id, $type=['GAS']) {

        App::import('Model', 'Organization');
        $Organization = new Organization;

        $Organization->unbindModel(['hasMany' => ['Delivery', 'User']]);

        $options = [];
        $options['conditions'] = ['Organization.id' => (int) $organization_id,
                                  'Organization.type' => $type];
        $options['recursive'] = 0;

        $results = $Organization->find('first', $options);
        if(!empty($results)) {
            $user = new UserLocal();
            $user->organization = $results;

            $paramsConfig = json_decode($results['Organization']['paramsConfig'], true);
            $paramsFields = json_decode($results['Organization']['paramsFields'], true);

            /*
             * configurazione preso dal template
             */
            $paramsConfig['payToDelivery'] = $results['Template']['payToDelivery'];
            $paramsConfig['orderForceClose'] = $results['Template']['orderForceClose'];
            $paramsConfig['orderUserPaid'] = $results['Template']['orderUserPaid'];
            $paramsConfig['orderSupplierPaid'] = $results['Template']['orderSupplierPaid'];
            // non + ora dall'organization $paramsConfig['ggArchiveStatics'] = $results['Template']['ggArchiveStatics'];
            if(!isset($paramsConfig['ggArchiveStatics']))
                $paramsConfig['ggArchiveStatics'] = $results['Template']['ggArchiveStatics'];

            $user->organization['Organization'] += $paramsConfig;
            $user->organization['Organization'] += $paramsFields;
        }
        
        return $user;
    }
}

class UserLocal {

    public $organization;

}
?>