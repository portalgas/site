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

    public function __construct(View $view, array $settings = array()) {

        date_default_timezone_set('Europe/Rome');

        Configure::write('debug', 0);
        $this->timeHelper = new TimeHelper($view, $settings);
        $this->appHelper = new AppHelper($view, $settings);
        $this->exportDocsHelper = new ExportDocsHelper($view, $settings);
    }

    /*
     *  invio mail x notificare la consegna
     */

    public function mailUsersDelivery($organization_id) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " Mail agli utenti con dettaglio consegna \n";
        $user = $this->__getObjUserLocal($organization_id);

        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        App::import('Model', 'Order');

        App::import('Model', 'Supplier');

        App::import('Model', 'Mail');
        $Mail = new Mail;

        App::import('Model', 'User');

        $j_seo = $user->organization['Organization']['j_seo'];

        $options = array();
        /*
         * estraggo le consegne che si apriranno domani
         */
        $options['conditions'] = array('Delivery.organization_id' => (int) $user->organization['Organization']['id'],
            'Delivery.isVisibleFrontEnd' => 'Y',
            'Delivery.stato_elaborazione' => 'OPEN',
            'DATE(Delivery.data) = CURDATE() + INTERVAL ' . Configure::read('GGMailToAlertDeliveryOn') . ' DAY '
        );
        $options['recursive'] = -1;
        $deliveryResults = $Delivery->find('all', $options);

        if (!empty($deliveryResults)) {
            foreach ($deliveryResults as $deliveryResult) {

                echo "Elaboro consegna di " . $this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'], "%A %e %B %Y") . " a " . $deliveryResult['Delivery']['luogo'] . " \n";

                /*
                 * estraggo gli ordini associati alla consegna
                 */

                $Order = new Order;

                $Order->unbindModel(array('belongsTo' => array('Delivery')));
                $options = array();
                $options['conditions'] = array('Order.delivery_id' => $deliveryResult['Delivery']['id'],
                    'Order.organization_id' => (int) $user->organization['Organization']['id'],
                    'Order.isVisibleBackOffice' => 'Y',
                    'Order.state_code !=' => 'CREATE-INCOMPLETE');
                $options['recursive'] = 0;
                $options['fields'] = array('SuppliersOrganization.name', 'SuppliersOrganization.frequenza', 'SuppliersOrganization.supplier_id');
                $options['order'] = array('SuppliersOrganization.name');
                $orderResults = $Order->find('all', $options);

                $tmpProduttori = "";
                foreach ($orderResults as $numResult => $orderResult) {

                    echo "Elaboro ordine del produttore " . $orderResult['SuppliersOrganization']['name'] . " \n";

                    /*
                     * Suppliers per l'immagine
                     * */
                    $Supplier = new Supplier;

                    $options = array();
                    $options['conditions'] = array('Supplier.id' => $orderResult['SuppliersOrganization']['supplier_id']);
                    $options['fields'] = array('Supplier.descrizione', 'Supplier.img1');
                    $options['recursive'] = -1;
                    $SupplierResults = $Supplier->find('first', $options);

                    $tmpProduttori .= '<br />' . ($numResult + 1) . ') ';

                    if (!empty($SupplierResults['Supplier']['img1']) && file_exists($this->AppRoot . Configure::read('App.img.upload.content') . DS . $SupplierResults['Supplier']['img1']))
                        $tmpProduttori .= ' <img width="50" src="http://www.portalgas.it' . Configure::read('App.web.img.upload.content') . '/' . $SupplierResults['Supplier']['img1'] . '" alt="' . $orderResult['SuppliersOrganization']['name'] . '" /> ';

                    $tmpProduttori .= $orderResult['SuppliersOrganization']['name'];
                    if (!empty($SupplierResults['Supplier']['descrizione']))
                        $tmpProduttori .= ' (' . $SupplierResults['Supplier']['descrizione'] . ')';
                    if (!empty($SupplierResults['SuppliersOrganization']['frequenza']))
                        $tmpProduttori .= ' Frequenza ' . $orderResult['SuppliersOrganization']['frequenza'];
                } // end foreach ($orderResults as $orderResult)


                $User = new User;

                $conditions = array('Delivery.id' => $deliveryResult['Delivery']['id']);
                $results = $User->getUserWithCartByDelivery($user, $conditions, null, $modalita = 'CRON');
                if (!empty($results)) {
                    foreach ($results as $numResult => $result) {
                        $name = $result['User']['name'];
                        $mail = $result['User']['email'];
                        $username = $result['User']['username'];

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

                            $url = 'http://www.portalgas.it/home-' . $j_seo . '/preview-carrello-' . $j_seo . '?' . $User->getUrlCartPreviewNoUsername($user, $deliveryResult['Delivery']['id']);

                            $body_mail .= '<div style="clear: both; float: none; margin: 5px 0 15px;">';
                            $body_mail .= '<img src="http://www.portalgas.it' . Configure::read('App.img.cake') . '/cesta-piena.png" title="" border="0" />';
                            $body_mail .= ' <a target="_blank" href="' . $url . '">Clicca qui per visualizzare i tuoi <b>acquisti</b> che dovrai ritirare durante la consegna</a>';
                            $body_mail .= '</div>';

                            $body_mail .= '<h3>Elenco dei produttori presenti alla consegna</h3>';
                            $body_mail .= $tmpProduttori;

                            /*
                             * all'url per il CartPreview aggiungo lo username crittografato
                             */
                            $body_mail_final = str_replace("{u}", urlencode($User->getUsernameCrypted($username)), $body_mail);
                            if ($debug) {
                                echo '<h1>' . $username . ' ' . urlencode($User->getUsernameCrypted($username)) . '</h1>';
                                if ($numResult == 5)
                                    exit;
                            }

                            if ($numResult == 1)
                                echo $body_mail_final;

                            $Email = $this->__getMail();
                            $subject_mail = $this->appHelper->organizationNameError($user->organization) . ", consegna di " . $this->timeHelper->i18nFormat($deliveryResult['Delivery']['data'], "%A %e %B %Y") . " a " . $deliveryResult['Delivery']['luogo'];
                            $Email->subject($subject_mail);

                            $Email->viewVars(array('header' => $Mail->drawLogo($user->organization)));
                            $Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
                            $Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))));

                            $Email->to($mail);
                            if (!Configure::read('mail.send'))
                                $Email->transport('Debug');

                            $Email->viewVars(array('content_info' => $this->__getContentInfo()));

                            try {
                                if (!$debug) {
                                    $Email->send($body_mail_final);

                                    if (!Configure::read('mail.send'))
                                        echo ": inviata a " . $mail . " (modalita DEBUG)\n";
                                    else
                                        echo ": inviata a " . $mail . " \n";
                                }
                            } catch (Exception $e) {
                                echo ": NON inviata $e \n";
                                CakeLog::write("error", $e, array("mails"));
                            }
                        } else
                            echo ": NON inviata, mail empty \n";
                    } // end foreach ($results as $result)				
                }
            } // end foreach ($deliveryResults as $deliveryResult) 
        } else
            echo "non ci sono consegne che apriranno tra " . (Configure::read('GGMailToAlertDeliveryOn') + 1) . " giorni \n";
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     */

    public function mailEvents($organization_id, $debug = true) {
        App::import('Model', 'Event');
        $Event = new Event;

        $Event->sendNotificationMail($this->timeHelper, $this->appHelper, $organization_id, $debug);
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     *  event gcalendar x notificare le consegne OPEN e non elaborate (Delivery.gcalendar_event_id null)
     *  senza dettaglio produttori perche' non li ho ancora
     */

    public function gcalendarUsersDeliveryInsert($organization_id, $debug = true) {

        App::import('Model', 'Google');
        $Google = new Google;

        $Google->usersDeliveryInsert($this->timeHelper, $organization_id, $debug);
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     *  event gcalendar x aggiornare la consegna (prima di Configure::read('GGEventGCalendarToAlertDeliveryOn') gg dall'apertura)
     *  con dettaglio produttori
     */

    public function gcalendarUsersDeliveryUpdate($organization_id, $debug = true) {

        App::import('Model', 'Google');
        $Google = new Google;

        $Google->usersDeliveryUpdate($this->timeHelper, $organization_id, $debug);
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * invio mail 
     * 		ordini che si aprono oggi
     * 		ctrl data_inizio con data_oggi
     * 		mail_open_send = Y (perche' in Order::add data_inizio = data_oggi)
     */

    public function mailUsersOrdersOpen($organization_id, $debug = false) {

        $user = $this->__getObjUserLocal($organization_id);

        App::import('Model', 'MailsSend');
        $MailsSend = new MailsSend;

        $MailsSend->mailUsersOrdersOpen($organization_id, $user, $debug);
    }

    public function mailMonitoringSuppliersOrganizationsOrdersDataFine($organization_id, $debug = false) {

        App::import('Model', 'MonitoringSuppliersOrganization');
        $MonitoringSuppliersOrganization = new MonitoringSuppliersOrganization;

        $MonitoringSuppliersOrganization->mail_order_data_fine($organization_id, $debug);
    }
    
    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     */

    public function mailUsersOrdersClose($organization_id, $debug = false) {

        $user = $this->__getObjUserLocal($organization_id);

        App::import('Model', 'MailsSend');
        $MailsSend = new MailsSend;

        $MailsSend->mailUsersOrdersClose($organization_id, $user, $debug);
    }

    /*
     * se ordine NON DES
     *    se un ArticleOrder ha raggiunto la quantita' massimo (stato = QTAMAXORDER)
     *      => se ArticleOrder.send_mail = 'N'  mail gia' inviata, salto
     *      => se ArticleOrder.send_mail = 'Y'  invio mail e update send_mail = 'N'
     * 
     *      prima porto tutti gli ArticleOrder.stato = Y con send_mail = Y a send_mail = N
     * se ordine DES
     *    faccio le medesime operazioni solo se il GAS e' il titolare dell'ordine, invio mail ai referenti (non ai titolari perche' loro non posso accedere all'ordine) 
     */

    public function mailReferentiQtaMax($organization_id) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " Mail ai referenti per i prodotti che hanno raggiunto il limite \n";
        $user = $this->__getObjUserLocal($organization_id);

        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;

        App::import('Model', 'SuppliersOrganizationsReferent');        
        
        /*
         * prima porto tutti gli ArticleOrder.stato = Y con send_mail = Y a send_mail = N
         * 		perche' solo quelli con ArticleOrder.stato = QTAMAXORDER possono avere send_mail = Y 
         *  	quindi era un QTAMAXORDER abbassata e totata a stato = Y 
         */
        echo "Estraggo gli articoli con ArticlesOrder.stato = Y e ArticlesOrder.send_mail = Y e li porto a send_mail = N perche solo con ArticlesOrder.QTAMAXORDER posso avere send_mail = Y \n";
        $ArticlesOrder->unbindModel(array('belongsTo' => array('Cart')));

        $options = array();
        $options['conditions'] = array('ArticlesOrder.organization_id' => (int) $user->organization['Organization']['id'],
            'ArticlesOrder.stato' => 'Y',
            'ArticlesOrder.send_mail' => 'Y',
            'Article.stato' => 'Y',
            'Order.state_code' => 'OPEN');
        $options['recursive'] = 1;
        $articlesOrderResults = $ArticlesOrder->find('all', $options);

        foreach ($articlesOrderResults as $articlesOrderResult) {

            $sql = "UPDATE " . Configure::read('DB.prefix') . "articles_orders 
                    SET
                        send_mail = 'N',
                        modified = '" . date('Y-m-d H:i:s') . "'
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
        $options = array();
        $options['conditions'] = array('ArticlesOrder.organization_id' => (int) $user->organization['Organization']['id'],
                                        'ArticlesOrder.stato' => 'QTAMAXORDER',
                                        'ArticlesOrder.send_mail' => 'N',
                                        'Article.stato' => 'Y',
                                        'Order.state_code' => 'OPEN'
        );
        $options['recursive'] = 1;

        $ArticlesOrder->unbindModel(array('belongsTo' => array('Cart')));

        $articlesOrderResults = $ArticlesOrder->find('all', $options);

        foreach ($articlesOrderResults as $articlesOrderResult) {
         
            $send_mail = false;
            
            if($articlesOrderResult['Order']['des_order_id']>0) {
                /*
                 * ordine DES => ctrl se sono TITOLARE, invio mail ai referenti (non ai titolari perche' loro non posso accedere all'ordine)
                 */
                App::import('Model', 'DesOrder');
                $DesOrder = new DesOrder();

                $DesOrder->unbindModel(array('belongsTo' => array('De')));
                
                $options = array();
                $options['conditions'] = array('DesOrder.id' => $articlesOrderResult['Order']['des_order_id']);
                $options['fields'] = array('DesSupplier.id', 'DesSupplier.des_id', 'DesSupplier.own_organization_id');
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
             
                    $DesSuppliersReferent->unbindModel(array('belongsTo' => array('De', 'DesSupplier')));
                    
                    $options = array();
                    $options['conditions'] = array('DesSuppliersReferent.des_id' => $desOrdersResults['DesSupplier']['des_id'],
                                                    'DesSuppliersReferent.des_supplier_id' => $desOrdersResults['DesSupplier']['id'],
                                                    'DesSuppliersReferent.organization_id' => $user->organization['Organization']['id'],
                                                    'DesSuppliersReferent.group_id' => Configure::read('group_id_titolare_des_supplier'));
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
                   $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;              
                   $conditions = array('User.block' => 0,
                                        'SuppliersOrganization.id' => $articlesOrderResult['Order']['supplier_organization_id']);
                   $mail_destinatati = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions, $orderBy = null, $modalita = 'CRON');

                   $this->_mailReferentiQtaMaxSendMail($user, $articlesOrderResult, $mail_destinatati, $debug);            
            }
         } // end foreach($articlesOrderResults as $numResult => $articlesOrderResult)
    }

   private function _mailReferentiQtaMaxSendMail($user, $articlesOrderResult, $mail_destinatati, $debug) {
        
        App::import('Model', 'Mail');
        $Mail = new Mail;
    
        $body_mail_final = "";
        $body_mail_final .= "<br />";
        if (!empty($articlesOrderResult['Article']['img1']) && file_exists($this->AppRoot . Configure::read('App.img.upload.article') . DS . $articlesOrderResult['Article']['organization_id'] . DS . $articlesOrderResult['Article']['img1'])) {
            $body_mail_final .= '<img width="50" class="userAvatar" src="http://www.portalgas.it' . Configure::read('App.web.img.upload.article') . '/' . $articlesOrderResult['Article']['organization_id'] . '/' . $articlesOrderResult['Article']['img1'] . '" /> ';
        }
        $body_mail_final .= "L'articolo ";
        $body_mail_final .= "<b>" . $articlesOrderResult['ArticlesOrder']['name'] . '</b> ';
        $body_mail_final .= 'ha raggiunto la quantit&agrave; massima (' . $articlesOrderResult['ArticlesOrder']['qta_massima_order'] . ') ';
        $body_mail_final .= 'che hai settato quando l\'hai associato all\'ordine';

        echo '<h2>tratto L\'articolo ' . $articlesOrderResult['ArticlesOrder']['name'] . ' (' . $articlesOrderResult['Article']['id'] . ')</h2>';
        echo $body_mail_final;

        $Email = $this->__getMail();
        $subject_mail = $this->appHelper->organizationNameError($user->organization) . ", articolo " . $articlesOrderResult['ArticlesOrder']['name'] . " ha raggiunto la quantita' massima";
        $Email->subject($subject_mail);


        foreach ($mail_destinatati as $numResult => $result) {

            $mail = $result['User']['email'];
            $name = $result['User']['name'];

            $username = $result['User']['username'];

            echo $numResult . ") tratto l'utente " . $name . ', username ' . $username;

            if (!empty($mail)) {

                $Email->viewVars(array('header' => $Mail->drawLogo($user->organization)));
                $Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
                $Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))));

                $Email->to($mail);
                if (!Configure::read('mail.send'))
                    $Email->transport('Debug');

                if ($numResult == 0)
                    echo $body_mail_final;

                try {
                    $Email->send($body_mail_final);

                    if (!Configure::read('mail.send'))
                        echo ": inviata a " . $mail . " (modalita DEBUG)\n";
                    else
                        echo ": inviata a " . $mail . " \n";
                } catch (Exception $e) {
                    echo ": NON inviata $e \n";
                    CakeLog::write("error", $e, array("mails"));
                }
            } else
                echo ": NON inviata, mail empty \n";
        } // end loop users

        /*
         * ho inviato la mail, update send_mail = 'Y' cosi' non invia + la mail 
         * 		a meno che ArticlesOrder.stato non torna a Y
         */
        echo "Ho inviato la MAIL, porto send_mail = Y \n";
        $sql = "UPDATE " . Configure::read('DB.prefix') . "articles_orders
                SET
                    send_mail = 'Y',
                    modified = '" . date('Y-m-d H:i:s') . "'
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
     * 	=> invio mail e update send_mail_qta_massima = 'N'
     */
    public function mailReferentiOrderQtaMax($organization_id, $debug = true) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " Mail ai referenti se la quantit&agrave; massima dell'ordine ha raggiunto il limite \n";
        $user = $this->__getObjUserLocal($organization_id);

        App::import('Model', 'Order');
        $Order = new Order;

        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

        App::import('Model', 'Mail');
        $Mail = new Mail;

        /*
         * estraggo gli ordini OPEN con un limite sull'importo
         */

        $Order = new Order;

        $Order->unbindModel(array('belongsTo' => array('Delivery')));

        $options = array();
        $options['conditions'] = array('Order.organization_id' => (int) $user->organization['Organization']['id'],
            'Order.isVisibleBackOffice' => 'Y',
            'Order.state_code' => 'OPEN',
            'Order.qta_massima != ' => 0,
            'Order.send_mail_qta_massima' => 'Y');
        $options['recursive'] = 0;
        $options['fields'] = array('Order.id', 'Order.qta_massima', 'Order.qta_massima_um', 'Order.supplier_organization_id', 'SuppliersOrganization.name');
        $options['order'] = array('Order.id');
        $orderResults = $Order->find('all', $options);

        foreach ($orderResults as $numResult => $orderResult) {

            $totQuantita = $Order->getTotQuantitaArticlesOrder($user, $orderResult, $debug);

            if ($debug)
                echo "\n" . 'Ordine ' . $orderResult['SuppliersOrganization']['name'] . ' (' . $orderResult['Order']['id'] . ') ha un limite quantita impostata ' . $orderResult['Order']['qta_massima'] . " (" . $orderResult['Order']['qta_massima_um'] . ") => quantita totale acquistata " . $totQuantita . " (in GR o ML o PZ)\n";

            /*
             *  per il confronto riporto il totale in KG, LT
             */
            if ($orderResult['Order']['qta_massima_um'] != 'PZ')
                $totQuantita = ($totQuantita / 1000);

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

                if ($debug)
                    echo "\n" . '<h2>tratto L\'ordine ' . $orderResult['SuppliersOrganization']['name'] . '</h2>';
                if ($debug)
                    echo "\n" . $body_mail_final;

                $Email = $this->__getMail();
                $subject_mail = $this->appHelper->organizationNameError($user->organization) . ", ordine " . $orderResult['SuppliersOrganization']['name'] . " ha raggiunto la quantita' di ";
                if ($orderResult['Order']['qta_massima_um'] == 'PZ')
                    $subject_mail .= $totQuantita . ' pezzi';
                else
                    $subject_mail .= $totQuantita . $orderResult['Order']['qta_massima_um'];

                $Email->subject($subject_mail);

                /*
                 * estraggo i referenti
                 */
                $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

                $conditions = array('User.block' => 0,
                    'SuppliersOrganization.id' => $orderResult['Order']['supplier_organization_id']);
                $results = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions, $orderBy = null, $modalita = 'CRON');

                foreach ($results as $numResult => $result) {

                    $mail = $result['User']['email'];
                    $name = $result['User']['name'];

                    $username = $result['User']['username'];

                    echo "\n" . $numResult . ") tratto l'utente " . $name . ', username ' . $username;

                    if (!empty($mail)) {

                        $Email->viewVars(array('header' => $Mail->drawLogo($user->organization)));
                        $Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
                        $Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))));

                        $Email->to($mail);
                        if (!Configure::read('mail.send'))
                            $Email->transport('Debug');

                        if ($numResult == 0)
                            echo $body_mail_final;

                        try {
                            $Email->send($body_mail_final);

                            if (!Configure::read('mail.send'))
                                echo ": inviata a " . $mail . " (modalita DEBUG)\n";
                            else
                                echo ": inviata a " . $mail . " \n";
                        } catch (Exception $e) {
                            echo ": NON inviata $e \n";
                            CakeLog::write("error", $e, array("mails"));
                        }
                    } else
                        echo ": NON inviata, mail empty \n";
                } // end loop users

                /*
                 * ho inviato la mail, update send_mail_importo_massimo = 'N' cosi' non invia + la mail 
                 */
                $sql = "UPDATE " . Configure::read('DB.prefix') . "orders
					   SET
						send_mail_qta_massima = 'N',
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
						organization_id = " . (int) $user->organization['Organization']['id'] . "
						and id = " . $orderResult['Order']['id'];
                if ($debug)
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
     * 	=> invio mail e update send_mail_importo_massimo = 'N'
     */

    public function mailReferentiOrderImportoMax($organization_id, $debug = true) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " Mail ai referenti se l'importo massimo dell'ordine ha raggiunto il limite \n";
        $user = $this->__getObjUserLocal($organization_id);

        App::import('Model', 'Order');
        $Order = new Order;

        App::import('Model', 'SuppliersOrganizationsReferent');
        $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

        App::import('Model', 'Mail');
        $Mail = new Mail;

        /*
         * estraggo gli ordini OPEN con un limite sull'importo
         */

        $Order = new Order;

        $Order->unbindModel(array('belongsTo' => array('Delivery')));

        $options = array();
        $options['conditions'] = array('Order.organization_id' => (int) $user->organization['Organization']['id'],
            'Order.isVisibleBackOffice' => 'Y',
            'Order.state_code' => 'OPEN',
            'Order.importo_massimo != ' => 0,
            'Order.send_mail_importo_massimo' => 'Y');
        $options['recursive'] = 0;
        $options['fields'] = array('Order.id', 'Order.importo_massimo', 'Order.supplier_organization_id', 'SuppliersOrganization.name');
        $options['order'] = array('Order.id');
        $orderResults = $Order->find('all', $options);

        foreach ($orderResults as $numResult => $orderResult) {

            $totImporto = $Order->getTotImportoArticlesOrder($user, $orderResult['Order']['id'], $debug);

            if ($debug)
                echo "\n" . 'Ordine ' . $orderResult['SuppliersOrganization']['name'] . ' (' . $orderResult['Order']['id'] . ') ha un limite a ' . $orderResult['Order']['importo_massimo'] . ' &euro; => raggiunto ' . $totImporto . '&euro;' . "\n";

            if ($totImporto >= $orderResult['Order']['importo_massimo']) {

                $body_mail_final = "";
                $body_mail_final .= "<br />";
                $body_mail_final .= "L'ordine ";
                $body_mail_final .= "<b>" . $orderResult['SuppliersOrganization']['name'] . '</b> ';
                $body_mail_final .= 'ha raggiunto l\'importo ' . $totImporto . '&euro;:';
                $body_mail_final .= ' quando hai creato l\'ordine hai settato un limite di ' . $orderResult['Order']['importo_massimo'] . '&euro;';

                if ($debug)
                    echo "\n" . '<h2>tratto L\'ordine ' . $orderResult['SuppliersOrganization']['name'] . '</h2>';
                if ($debug)
                    echo "\n" . $body_mail_final;

                $Email = $this->__getMail();
                $subject_mail = $this->appHelper->organizationNameError($user->organization) . ", ordine " . $orderResult['SuppliersOrganization']['name'] . " ha raggiunto l'importo di " . $totImporto . "€";
                $Email->subject($subject_mail);

                /*
                 * estraggo i referenti
                 */
                $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;

                $conditions = array('User.block' => 0,
                    'SuppliersOrganization.id' => $orderResult['Order']['supplier_organization_id']);
                $results = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions, $orderBy = null, $modalita = 'CRON');

                foreach ($results as $numResult => $result) {

                    $mail = $result['User']['email'];
                    $name = $result['User']['name'];

                    $username = $result['User']['username'];

                    echo "\n" . $numResult . ") tratto l'utente " . $name . ', username ' . $username;

                    if (!empty($mail)) {

                        $Email->viewVars(array('header' => $Mail->drawLogo($user->organization)));
                        $Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
                        $Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))));

                        $Email->to($mail);
                        if (!Configure::read('mail.send'))
                            $Email->transport('Debug');

                        if ($numResult == 0)
                            echo $body_mail_final;

                        try {
                            $Email->send($body_mail_final);

                            if (!Configure::read('mail.send'))
                                echo ": inviata a " . $mail . " (modalita DEBUG)\n";
                            else
                                echo ": inviata a " . $mail . " \n";
                        } catch (Exception $e) {
                            echo ": NON inviata $e \n";
                            CakeLog::write("error", $e, array("mails"));
                        }
                    } else
                        echo ": NON inviata, mail empty \n";
                } // end loop users

                /*
                 * ho inviato la mail, update send_mail_importo_massimo = 'N' cosi' non invia + la mail 
                 */
                $sql = "UPDATE " . Configure::read('DB.prefix') . "orders
					   SET
						send_mail_importo_massimo = 'N',
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
						organization_id = " . (int) $user->organization['Organization']['id'] . "
						and id = " . $orderResult['Order']['id'];
                if ($debug)
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
     * 		estraggo per data_master_reale 
     * 		ricalcolo la ricorsione partendo da data_master 
     * 			data_master 	  => data_copy
     * 			data_master_reale => data_copy_reale
     * 			data_copy 		  => calcolo nuova ricorsione
     * 			data_copy_reale   => calcolo nuova ricorsione
     * 			nuova consegna con data_copy_reale
     */

    public function loopsDeliveries($organization_id, $debug = true) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " Consegne: creo le consegne ricorsive \n";
        $user = $this->__getObjUserLocal($organization_id);

        App::import('Model', 'LoopsDelivery');
        $LoopsDelivery = new LoopsDelivery;

        App::import('Model', 'Delivery');

        /*
         * faccio CURDATE() - INTERVAL 1 DAY cosi aspetto che sia chiusa la master e prendo quelle del giorno precedente (il cron parte alle 0.35)
         */
        $options = array();
        $options['conditions'] = array('LoopsDelivery.organization_id' => (int) $user->organization['Organization']['id'],
            'DATE(LoopsDelivery.data_master_reale) = CURDATE() - INTERVAL 1 DAY');
        $options['recursive'] = -1;
        $loopsDeliveryResults = $LoopsDelivery->find('all', $options);

        if ($debug) {
            echo '<h2>Consegne ricorsive</h2>';
            echo "<pre>";
            print_r($loopsDeliveryResults);
            echo "</pre>";
        }

        if (!empty($loopsDeliveryResults)) {

            foreach ($loopsDeliveryResults as $numResult => $loopsDeliveryResult) {

                /*
                 * non faccio + il ctrl se esiste una consegna: si possono creare + consegne per la stessa data
                 * $delivery_just_exist = false;
                 */
                $rules = json_decode($loopsDeliveryResult['LoopsDelivery']['rules'], true);
                $loopsDeliveryResult['LoopsDelivery'] += $rules;

                $data = $loopsDeliveryResult['LoopsDelivery']['data_master_reale'];


                /*
                 * ctrl che non esisti gia' una consegna in quella data => NON +
                 *

                  $Delivery = new Delivery;

                  $options = array();
                  $options['conditions'] = array('Delivery.organization_id' => (int)$user->organization['Organization']['id'],
                  'DATE(Delivery.data)' => $loopsDeliveryResult['LoopsDelivery']['data_copy_reale']);
                  $options['recursive'] = -1;
                  $deliveryResults = $Delivery->find('first', $options);

                  if(empty($deliveryResults)) {
                 */
                // $delivery_just_exist = false;

                $row = array();
                $row['Delivery']['organization_id'] = $user->organization['Organization']['id'];
                $row['Delivery']['luogo'] = $loopsDeliveryResult['LoopsDelivery']['luogo'];
                $row['Delivery']['data'] = $loopsDeliveryResult['LoopsDelivery']['data_copy_reale'];
                $row['Delivery']['orario_da'] = $loopsDeliveryResult['LoopsDelivery']['orario_da'];
                $row['Delivery']['orario_a'] = $loopsDeliveryResult['LoopsDelivery']['orario_a'];
                $row['Delivery']['nota'] = $loopsDeliveryResult['LoopsDelivery']['nota'];
                $row['Delivery']['nota_evidenza'] = $loopsDeliveryResult['LoopsDelivery']['nota_evidenza'];
                $row['Delivery']['isToStoreroom'] = 'N';
                $row['Delivery']['isToStoreroomPay'] = 'N';
                $row['Delivery']['stato_elaborazione'] = 'OPEN';
                $row['Delivery']['isVisibleFrontEnd'] = 'Y';
                $row['Delivery']['isVisibleBackOffice'] = 'Y';
                $row['Delivery']['sys'] = 'N';

                if ($debug) {
                    echo '<h2>Nuova consegna</h2>';
                    echo "<pre>";
                    print_r($row);
                    echo "</pre>";
                }

                $Delivery = new Delivery;
                $Delivery->create();
                if ($Delivery->save($row)) {
                    echo "\r\n consegna per il " . $row['Delivery']['data'] . " a " . $row['Delivery']['luogo'] . " creata";
                } else {
                    echo "\r\n consegna per il " . $row['Delivery']['data'] . " a " . $row['Delivery']['luogo'] . " NON creata";
                }

                /* } // if(empty($deliveryResults)) 
                  else {
                  if($debug)
                  echo '<br />Consegne gia esistente';

                  $delivery_just_exist = true;
                  }
                 */

                /*
                 * creo nuova ricorsione
                 */
                $row1 = array();
                $row1['LoopsDelivery']['id'] = $loopsDeliveryResult['LoopsDelivery']['id'];
                $row1['LoopsDelivery']['organization_id'] = $user->organization['Organization']['id'];
                $row1['LoopsDelivery']['data_master'] = $loopsDeliveryResult['LoopsDelivery']['data_copy'];
                $row1['LoopsDelivery']['data_master_reale'] = $loopsDeliveryResult['LoopsDelivery']['data_copy_reale'];

                $data_copy = $LoopsDelivery->get_data_copy($loopsDeliveryResult['LoopsDelivery']['data_copy'], $loopsDeliveryResult, $debug);

                $row1['LoopsDelivery']['data_copy'] = $data_copy;
                $row1['LoopsDelivery']['data_copy_reale'] = $data_copy;

                if ($debug) {
                    echo '<h2>Aggiorno ricorsione</h2>';
                    echo "<pre>";
                    print_r($row1);
                    echo "</pre>";
                }

                $LoopsDelivery->create();
                if ($LoopsDelivery->save($row1)) {
                    echo "\r\n consegna ricorsiva creata con data $data_copy";
                } else {
                    echo "\r\n consegna ricorsiva NON creata con data $data_copy";
                }

                /*
                 * invio mail di notifica a chi ha creato la ricorsione
                 */
                if ($loopsDeliveryResult['LoopsDelivery']['flag_send_mail'] == 'Y') {

                    App::import('Model', 'User');
                    $User = new User;

                    App::import('Model', 'Mail');
                    $Mail = new Mail;

                    $options = array();
                    $options['conditions'] = array('User.organization_id' => (int) $user->organization['Organization']['id'],
                        'User.id' => $loopsDeliveryResult['LoopsDelivery']['user_id']);
                    $options['recursive'] = -1;
                    $result = $User->find('first', $options);
                    if (!empty($result)) {
                        $name = $result['User']['name'];
                        $mail = $result['User']['email'];
                        $username = $result['User']['username'];

                        echo "\r\n tratto l'utente " . $name . ', username ' . $username;

                        if (!empty($mail)) {
                            $body_mail = "";
                            if ($delivery_just_exist)
                                $body_mail .= 'Tentativo di creare la consegna ricorsiva ' . $this->timeHelper->i18nFormat($row['Delivery']['data'], "%A %e %B %Y") . " ma esisteva gi&agrave;.";
                            else
                                $body_mail .= 'Creata la consegna ricorsiva ' . $this->timeHelper->i18nFormat($row['Delivery']['data'], "%A %e %B %Y") . " a " . $row['Delivery']['luogo'] . '.';

                            $body_mail .= '<br />Prossima consegna sar&agrave; ' . $this->timeHelper->i18nFormat($row1['LoopsDelivery']['data_copy_reale']);

                            $body_mail_final = $body_mail;
                            echo $body_mail_final;

                            $Email = $this->__getMail();
                            $subject_mail = 'Creata la consegna ricorsiva ' . $this->timeHelper->i18nFormat($row['Delivery']['data'], "%A %e %B %Y") . " a " . $row['Delivery']['luogo'];
                            $Email->subject($subject_mail);

                            $Email->viewVars(array('header' => $Mail->drawLogo($user->organization)));
                            $Email->viewVars(array('body_header' => sprintf(Configure::read('Mail.body_header'), $name)));
                            $Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->appHelper->traslateWww($user->organization['Organization']['www']))));

                            $Email->to($mail);
                            if (!Configure::read('mail.send'))
                                $Email->transport('Debug');
                            try {
                                if (!$debug) {
                                    $Email->send($body_mail_final);

                                    if (!Configure::read('mail.send'))
                                        echo ": inviata a " . $mail . " (modalita DEBUG)\n";
                                    else
                                        echo ": inviata a " . $mail . " \n";
                                }
                            } catch (Exception $e) {
                                echo ": NON inviata $e \n";
                                CakeLog::write("error", $e, array("mails"));
                            }
                        } else
                            echo ": NON inviata, mail empty \n";
                    } // if(!empty($results))				
                } // if($row['LoopsDelivery']['flag_send_mail']=='Y')
            } // end foreach ($loopsDeliveryResults as $loopsDeliveryResult)
        } // end if(!empty($loopsDeliveryResults)) 
    }

    public function loopsOrders($organization_id) {
        echo date("d/m/Y") . " - " . date("H:i:s") . " Ordini: duplica gli ordini ricorsivi \n";
        $user = $this->__getObjUserLocal($organization_id);
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     *  prendo gli ordini in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna)
     *  		se tutti gli utenti hanno pagato SummaryOrder.importo = SummaryOrder.importo_pagato li chiudo
     */

    public function ordersIncomingOnDeliveryToClose($organization_id, $debug = true, $order_id = 0) {

        $user = $this->__getObjUserLocal($organization_id);

        /*
         * cron: estraggo i summary_payments associati alla richiesta di pagamento con importo_richiesto = importo_pagato
         */
        if ($debug)
            echo date("d/m/Y") . " - " . date("H:i:s") . " Porto gli ordini in PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna), pagati (con SummaryOrder.importo = SummaryOrder.importo_pagato) a Order.state_code = CLOSE \n";

        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;

        $SummaryOrder->unbindModel(array('belongsTo' => array('User', 'Delivery')));

        $options = array();
        $options['conditions'] = array('SummaryOrder.organization_id' => $organization_id,
            'SummaryOrder.modalita' => 'DEFINED',
            'SummaryOrder.importo' => 'SummaryOrder.importo_pagato',
            'Order.organization_id' => $organization_id,
            'Order.state_code' => 'PROCESSED-ON-DELIVERY');

        if ($order_id != 0) {
            $options['conditions'] += array('SummaryOrder.order_id' => $order_id,
                'Order.id' => $order_id);
        }
        $options['group'] = array('Order.id');
        $options['recursive'] = 1;
        $results = $SummaryOrder->find('all', $options);
        if (!empty($results)) {
            foreach ($results as $result) {

                $sql = "UPDATE " . Configure::read('DB.prefix') . "orders
						       SET
								state_code = 'CLOSE',
								modified = '" . date('Y-m-d H:i:s') . "'
						   WHERE
						   		organization_id = " . (int) $organization_id . "
						   		and id = " . $result['Order']['id'];
                if ($debug)
                    echo $sql . "\n";
                $SummaryOrder->query($sql);

                if ($debug)
                    echo "Porto l'ordine " . $result['Order']['id'] . " da PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) a CLOSE\n";
            }
        }
        else {
            if ($debug)
                echo "Nessun ordine in stato PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna) con tutti gli utenti che hanno pagato (SummaryOrder.importo = SummaryOrder.importo_pagato) da portare allo stato CLOSE\n";
        }
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * $request_payment_id  se valorizzato setta lo stato_elaborazione della richiesta di pagamento 
     * solo per pagamento POST-DELIVERY
     */

    public function requestPaymentStatoElaborazione($organization_id, $debug = true, $request_payment_id = 0) {

        try {
            $user = $this->__getObjUserLocal($organization_id);

            $paramsConfig = json_decode($user->organization['Organization']['paramsConfig'], true);
            $paramsFields = json_decode($user->organization['Organization']['paramsFields'], true);
            $organization_payToDelivery = $paramsConfig['payToDelivery'];

            if ($organization_payToDelivery != 'POST')
                return;

            App::import('Model', 'RequestPayment');
            $RequestPayment = new RequestPayment;

            /*
             * cron: estraggo i summary_payments associati alla richiesta di pagamento con importo_richiesto = importo_pagato
             */
            if ($debug)
                echo date("d/m/Y") . " - " . date("H:i:s") . " Porto le richiesta di pagamento con tutti i summary_payments.stato = SOSPESO o PAGATO a RequestPayment.stato_elaborazione = CLOSE \n";

            /*
             * estraggo tutti gli summary_payments di request_payment
             */
            $sql = "SELECT
						RequestPayment.id, 
						SummaryPayment.id, SummaryPayment.importo_dovuto, SummaryPayment.importo_richiesto, SummaryPayment.importo_pagato, SummaryPayment.stato   
				   FROM
						 " . Configure::read('DB.prefix') . "request_payments RequestPayment,
						 " . Configure::read('DB.prefix') . "summary_payments SummaryPayment   
				   WHERE
						RequestPayment.organization_id = " . (int) $organization_id . "
						AND SummaryPayment.organization_id = " . (int) $organization_id . "
						AND RequestPayment.stato_elaborazione = 'OPEN'
						and SummaryPayment.request_payment_id = RequestPayment.id ";
            if (!empty($delivery_id))
                $sql .= " AND RequestPayment.id = " . (int) $request_payment_id;
            $sql .= " ORDER BY RequestPayment.id, SummaryPayment.id";
            if ($debug)
                echo $sql . "\n";
            $results = $RequestPayment->query($sql);
            if ($debug)
                echo "Trattero " . count($results) . " SummaryPayment aggregati per RequestPayment per estrarre la RequestPayment con tutti i SummaryPayment con importo_richiesto = importo_pagato \n";

            /*
             * ciclo tutti gli SummaryPayment di un RequestPayment per vedere se tutti i SummaryPayment sono con importo_richiesto = importo_pagato 
             */
            $request_payment_id_old = 0;
            $all_summary_order_importi_uguali = true;
            foreach ($results as $result) {

                // if($debug) echo "<br />request_payment_id_old ".$request_payment_id_old.' - '.$result['RequestPayment']['id'].' stato '.$result['SummaryPayment']['stato'].' - all_summary_order_importi_uguali '.$all_summary_order_importi_uguali;

                if ($request_payment_id_old == 0 || $result['RequestPayment']['id'] == $request_payment_id_old) {
                    if ($result['SummaryPayment']['stato'] != Configure::read('SOSPESO') &&
                            $result['SummaryPayment']['stato'] != Configure::read('PAGATO'))
                        $all_summary_order_importi_uguali = false;
                }
                else {

                    // if($debug) echo "<br />Cambio request_payment_id ".$result['RequestPayment']['id'].' all_summary_order_importi_uguali '.$all_summary_order_importi_uguali;

                    if ($all_summary_order_importi_uguali) {
                        $sql = "UPDATE " . Configure::read('DB.prefix') . "request_payments 
						       SET
								stato_elaborazione = 'CLOSE',
								modified = '" . date('Y-m-d H:i:s') . "'
						   WHERE
						   		organization_id = " . (int) $organization_id . "
						   		and id = " . $request_payment_id_old;
                        if ($debug)
                            echo $sql . "\n";
                        $RequestPayment->query($sql);
                    }
                    $all_summary_order_importi_uguali = true;
                }

                $request_payment_id_old = $result['RequestPayment']['id'];
            }
        } catch (Exception $e) {
            if ($debug)
                echo '<br />UtilsCrons::requestPaymentStatoElaborazione()<br />' . $e;
        }
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     *  se $organization_payToDelivery=='POST'
     * 		tratto le RequestPayment.stato_elaborazione = CLOSE (portate a CLOSE da Cron::requestPaymentStatoElaborazione)
     *  se $organization_payToDelivery=='ON'
     * 		Delivery.stato_elaborazione = CLOSE (portate a CLOSE dal Cassiere) 
     *  se $organization_payToDelivery=='ON-POST'
     * 		tratto le RequestPayment.stato_elaborazione = CLOSE (portate a CLOSE da Cron::requestPaymentStatoElaborazione)
     * 		Delivery.stato_elaborazione = CLOSE (portate a CLOSE dal Cassiere) 
     */

    public function archiveStatistics($organization_id, $debug = true) {

        App::import('Model', 'Statistic');
        $Statistic = new Statistic;

        $user = $this->__getObjUserLocal($organization_id);
        $paramsConfig = json_decode($user->organization['Organization']['paramsConfig'], true);
        $paramsFields = json_decode($user->organization['Organization']['paramsFields'], true);
        $organization_payToDelivery = $paramsConfig['payToDelivery'];

        echo "\rTratto l'organization (" . $organization_id . ") con pagamento $organization_payToDelivery \r";
        switch ($organization_payToDelivery) {
            case 'ON':
                $Statistic->archivePayToDeliveryOn($user, $debug);
                break;
            case 'POST':
                $Statistic->archivePayToDeliveryPost($user, $debug);
                break;
            case 'ON-POST':
                /*
                 * prima della cancellazione Delivery, in __deleteDelivery() ctrl se non ha + ordini associati
                 */
                $Statistic->archivePayToDeliveryPost($user, $debug);
                $Statistic->archivePayToDeliveryOn($user, $debug);
                break;
            default:
                echo "\rPagamento non valido!";
                return;
                break;
        }
    }

    public function createPdfSingleUser($organization_id, $delivery_id, $user_id, $debug = true) {

        App::import('Model', 'Statistic');
        $Statistic = new Statistic;

        $user = $this->__getObjUserLocal($organization_id);

        $Statistic->create_pdf_single_user($user, $delivery_id, $user_id, $debug);
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * $delivery_id  se valorizzato setta lo stato_elaborazione della consegna
     * 
     * Porto le consegne con 
     * tutti gli ordini in stato_elaborazione = CLOSE 
     * 
     * if($user->organization['Organization']['hasUserGroupsTesoriere']=='Y')
     * 		e Order.tesoriere_stato_pay = Y 
     *
     * if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
     * 	e isToStoreroomPay = Y 
     *
     * allo Delivery.stato_elaborazione = CLOSE
     */

    public function deliveriesStatoElaborazione($organization_id, $debug = true, $delivery_id = 0) {
        $user = $this->__getObjUserLocal($organization_id);

        App::import('Model', 'DeliveryLifeCycle');
        $DeliveryLifeCycle = new DeliveryLifeCycle;

		$DeliveryLifeCycle->deliveriesExpiredWithoutOrdersDelete($user, 0, $debug);
		$DeliveryLifeCycle->deliveriesToClose($user, 0, $debug);
    }

    /*
     * le consegne che dovrebbero essere chiuse dal Cassiere con stato_elaborazione => OPEN vecchie di enne giorni, vengono chiuse
     */

    public function deliveriesCassiereClose($organization_id, $debug = true) {

        $user = $this->__getObjUserLocal($organization_id);

        $paramsConfig = json_decode($user->organization['Organization']['paramsConfig'], true);
        $paramsFields = json_decode($user->organization['Organization']['paramsFields'], true);
        $organization_payToDelivery = $paramsConfig['payToDelivery'];

        App::import('Model', 'Cassiere');
        $Cassiere = new Cassiere;

        $results = $Cassiere->getDeliveriesToClose($user, true, $debug);

        if (!empty($results)) {

            if ($debug) {
                echo date("d/m/Y") . " - " . date("H:i:s");
                echo " Porto le consegne non chiuse dal Cassiere, di " . Configure::read('GGDeliveryCassiereClose') . " gg a CLOSE";
                echo " e tutti gli ordini associati a stato_elaborazione = CLOSE \n";
            }

            foreach ($results as $result)
                $Cassiere->deliveryStatoClose($user, $result['Delivery']['id']);
        }
    }

    /*
     * Validazione degli ordini (articoli con colli): gli articoli messi nel carrello per l'utente Dispensa
     * vengono messi in Dispensa quando si chiude la consegna
     * 
     * gli articoli dal Carrello alla Dispensa vengono copiati perche' in Cart servono per conteggi
     * eseguire il Cron prima di mezzanotte!
     */

    public function articlesFromCartToStoreroom($organization_id, $debug = true, $delivery_id = 0) {

        try {
            $user = $this->__getObjUserLocal($organization_id);

            if ($debug)
                echo "Validazione degli ordini (articoli con colli): gli articoli messi nel carrello per l'utente Dispensa \n";
            if ($debug)
                echo "vengono messi in Dispensa quando si chiude la consegna \n";
            if ($debug)
                echo "gli articoli dal Carrello alla Dispensa vengono copiati perche' in Cart servono per conteggi \n";

            if ($user->organization['Organization']['hasStoreroom'] == 'N') {
                if ($debug)
                    echo "Organizzazione non abilitata a gestire la dispensa (hasStoreroom = N) \n";
                return;
            }

            App::import('Model', 'Storeroom');
            $Storeroom = new Storeroom;

            $storeroomUser = $Storeroom->getStoreroomUser($user);
            if (empty($storeroomUser)) {
                if ($debug)
                    echo "Non esiste lo user dispensa \n";
                return;
            }


            /*
             * estraggo tutti gli ordini delle consegne
             */
            $sql = "SELECT
						Delivery.id, 
						`Order`.id, 
						Cart.*,
						Article.*,
						ArticlesOrder.*  
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
            if ($debug)
                echo $sql . "\n";
            $results = $Storeroom->query($sql);
            if ($debug)
                echo "Trattero " . count($results) . " articoli acquistati dall'utente Dispensa per le consegna che si chiudono oggi \n";

            foreach ($results as $result) {

                if ($result['Cart']['qta'] == 0)
                    $qta = $result['Cart']['qta_forzato'];
                else
                    $qta = $result['Cart']['qta'];

                /*
                 * ctrl che non ci sia gia' un articolo in dispensa 
                 */
                $conditions = array('User.id' => $storeroomUser['User']['id'],
                    'Storeroom.delivery_id' => 0,
                    'Article.id' => $result['Article']['id']);
                $ctrlResults = $Storeroom->getArticlesToStoreroom($user, $conditions);

                $storeroom = array();
                if (!empty($ctrlResults)) {
                    $storeroom['Storeroom']['id'] = $ctrlResults['Storeroom']['id'];
                    $storeroom['Storeroom']['qta'] = ($ctrlResults['Storeroom']['qta'] + $qta);
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
                if ($Storeroom->save($storeroom))
                    if ($debug)
                        echo "OK, Inserito l'articolo (" . $storeroom['Storeroom']['article_id'] . ") " . $storeroom['ArticlesOrder']['name'] . " in dispensa con qta " . $storeroom['Storeroom']['qta'] . "\n";
                    else
                    if ($debug)
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

                if ($debug)
                    echo "Update Cart con inStoreroom = Y \n";
            } // foreach($results as $result)
        } catch (Exception $e) {
            if ($debug)
                echo '<br />UtilsCrons::articlesFromCartToStoreroom()<br />' . $e;
        }
    }

    /*
     * $debug = true perche' quando e' richiamato dal Cron deve scrivere sul file di log
     * $order_id  se valorizzato setta lo stato_elaborazione di quell'ordine
     */

    public function ordersStatoElaborazione($organization_id, $debug = true, $order_id = 0) {

        $debugSql = false;

        try {
            $user = $this->__getObjUserLocal($organization_id);

            $paramsConfig = json_decode($user->organization['Organization']['paramsConfig'], true);
            $paramsFields = json_decode($user->organization['Organization']['paramsFields'], true);
            $organization_payToDelivery = $paramsConfig['payToDelivery'];

            App::import('Model', 'Order');
            $Order = new Order;

            /*
             * cron: orders senza articoli associati (ArticlesOrder) in CREATE-INCOMPLETE
             */
            if ($debug)
                echo date("d/m/Y") . " - " . date("H:i:s") . " Porto gli ordini senza articoli associati (ArticlesOrder) in CREATE-INCOMPLETE \n";
            $sql = "SELECT 
						`Order`.id 
				   FROM 
						`" . Configure::read('DB.prefix') . "orders` `Order` LEFT JOIN 
						 " . Configure::read('DB.prefix') . "articles_orders ArticlesOrder ON 
						 		(ArticlesOrder.order_id = `Order`.id  
						 		and `ArticlesOrder`.organization_id = " . (int) $organization_id . ") 
				   WHERE 
						`Order`.organization_id = " . (int) $organization_id . "
						AND (`Order`.state_code != 'CREATE-INCOMPLETE' and `Order`.state_code != 'CLOSE')
						AND ArticlesOrder.article_id IS NULL AND ArticlesOrder.order_id IS NULL ";
            if (!empty($order_id))
                $sql .= " AND `Order`.id = " . (int) $order_id;
            $sql .= " GROUP BY `Order`.id";
            if ($debugSql)
                echo $sql . "\n";
            $results = $Order->query($sql);
            if ($debug)
                echo "Aggiornati: " . count($results) . "\n";
            foreach ($results as $result) {
                $sql = "UPDATE `" . Configure::read('DB.prefix') . "orders`
					   SET
							state_code = 'CREATE-INCOMPLETE',
							modified = '" . date('Y-m-d H:i:s') . "'
					   WHERE
					   		organization_id = " . (int) $organization_id . "
					   		and id = " . $result['Order']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $Order->query($sql);
            }

            /*
             * cron: orders con articoli associati (ArticlesOrder) da CREATE-INCOMPLETE a OPEN-NEXT o OPEN
             * 		ArtciclesOrders::add
             */
            if ($debug)
                echo "Porto gli ordini con articoli associati (ArticlesOrder) da CREATE-INCOMPLETE a OPEN-NEXT o OPEN (ArtciclesOrders::add) \n";
            $sql = "SELECT
						`Order`.id
				   FROM
						`" . Configure::read('DB.prefix') . "orders` `Order`, 
						 " . Configure::read('DB.prefix') . "articles_orders ArticlesOrder 
				   WHERE
						`Order`.organization_id = " . (int) $organization_id . "
						AND `ArticlesOrder`.organization_id = " . (int) $organization_id . " 
						AND ArticlesOrder.order_id = `Order`.id 
						AND `Order`.state_code = 'CREATE-INCOMPLETE' ";
            if (!empty($order_id))
                $sql .= " AND `Order`.id = " . (int) $order_id;
            $sql .= " group by `Order`.id";
            if ($debugSql)
                echo $sql . "\n";
            $results = $Order->query($sql);
            if ($debug)
                echo "Aggiornati: " . count($results) . "\n";
            foreach ($results as $result) {
                /*
                 * calcolo se OPEN-NEXT o OPEN
                 */
                $data_oggi = date("Y-m-d");
                if ($results['Order']['data_inizio'] > $data_oggi)
                    $state_code = 'OPEN-NEXT';
                else
                    $state_code = 'OPEN';

                $sql = "UPDATE `" . Configure::read('DB.prefix') . "orders`
					   SET
							state_code = '$state_code',
							modified = '" . date('Y-m-d H:i:s') . "'
					   WHERE
					   		organization_id = " . (int) $organization_id . "
					   		and id = " . $result['Order']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $Order->query($sql);
            }

            /*
             * cron: orders in OPEN-NEXT
             * 	estraggo gli ordini che si aprono successivamente
             */
            if ($debug)
                echo "Porto gli ordini a OPEN-NEXT per quelli che devono ancora aprirsi \n";
            $sql = "SELECT 
						count(`Order`.id) as totale
				   FROM 
						`" . Configure::read('DB.prefix') . "orders` as `Order` 
				   WHERE 
				   		`Order`.organization_id = " . (int) $organization_id . "
				   		AND (`Order`.state_code != 'CREATE-INCOMPLETE' 
				   			AND `Order`.state_code != 'OPEN-NEXT'
				   			AND `Order`.state_code != 'CLOSE'
				   			) 
				   		AND `Order`.data_inizio > CURDATE() ";  // data_inizio successiva ad oggi
            if (!empty($order_id))
                $sql .= " AND id = " . (int) $order_id;
            if ($debugSql)
                echo $sql . "\n";
            $results = current($Order->query($sql));
            $sql = "UPDATE 
						`" . Configure::read('DB.prefix') . "orders`
				   SET
						state_code = 'OPEN-NEXT',
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
						organization_id = " . (int) $organization_id . "
				   		AND (state_code != 'CREATE-INCOMPLETE'
				   			AND state_code != 'OPEN-NEXT' 
				   			AND state_code != 'CLOSE'
				   			) 
				   		AND data_inizio > CURDATE() ";  // data_inizio successiva ad oggi 
            if (!empty($order_id))
                $sql .= " AND id = " . (int) $order_id;
            if ($debugSql)
                echo $sql . "\n";
            if ($debug)
                echo "Aggiornati: " . $results[0]['totale'] . "\n";
            $Order->query($sql);


            /*
             * cron: orders da OPEN-NEXT a OPEN
             * 	estraggo gli ordini che si aprono oggi (o dovrebbero essere gia' aperti!)
             */
            if ($debug)
                echo "Porto gli ordini da OPEN-NEXT a OPEN: estraggo gli ordini che si aprono oggi (o dovrebbero essere gia' aperti!)\n";
            $sql = "SELECT count(id) as totale
				   FROM `" . Configure::read('DB.prefix') . "orders` as `Order` 
				   WHERE 
				   		organization_id = " . (int) $organization_id . "
				   		and state_code = 'OPEN-NEXT'
				   		and data_inizio <= CURDATE()"; // data_inizio precedente o uguale ad oggi 
            if ($debugSql)
                echo $sql . "\n";
            $results = current($Order->query($sql));
            $sql = "UPDATE `" . Configure::read('DB.prefix') . "orders`
				   SET
						state_code = 'OPEN',
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
				   		organization_id = " . (int) $organization_id . "
				   		and state_code = 'OPEN-NEXT'
				   		and data_inizio <= CURDATE() ";  // data_inizio precedente o uguale ad oggi 
            if (!empty($order_id))
                $sql .= " AND id = " . (int) $order_id;
            if ($debugSql)
                echo $sql . "\n";
            if ($debug)
                echo "Aggiornati: " . $results[0]['totale'] . "\n";
            $Order->query($sql);

            /*
             * cron: orders da OPEN a PROCESSED-BEFORE-DELIVERY
             * 	estraggo gli ordini chiusi con le consegne ancora aperte
             */
            if ($debug)
                echo "Porto gli ordini da OPEN a PROCESSED-BEFORE-DELIVERY: estraggo gli ordini chiusi con le consegne ancora aperte \n";
            $sql = "SELECT `Order`.id
					FROM
						" . Configure::read('DB.prefix') . "deliveries Delivery,
						`" . Configure::read('DB.prefix') . "orders` `Order`
					WHERE
						Delivery.organization_id = " . (int) $organization_id . "
						and `Order`.organization_id = " . (int) $organization_id . "
						and Delivery.stato_elaborazione = 'OPEN'
						and `Order`.delivery_id = Delivery.id
						and `Order`.state_code = 'OPEN' 
						and DATE(Delivery.data) >= CURDATE()
						and `Order`.data_fine < CURDATE()";
            if (!empty($order_id))
                $sql .= " AND `Order`.id = " . (int) $order_id;
            if ($debugSql)
                echo $sql . "\n";
            $results = $Order->query($sql);
            if ($debug)
                echo "Aggiornati: " . count($results) . "\n";
            foreach ($results as $result) {
                $sql = "UPDATE `" . Configure::read('DB.prefix') . "orders`
					   SET
							state_code = 'PROCESSED-BEFORE-DELIVERY',
							modified = '" . date('Y-m-d H:i:s') . "'
					   WHERE
					   		organization_id = " . (int) $organization_id . "
					   		and id = " . $result['Order']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $Order->query($sql);
            }


            /*
             * cron: orders da RI-OPEN-VALIDATE a PROCESSED-BEFORE-DELIVERY
             * 	estraggo gli ordini chiusi con le consegne ancora aperte
             */
            if ($debug)
                echo "Porto gli ordini da RI-OPEN-VALIDATE a PROCESSED-BEFORE-DELIVERY: estraggo gli ordini chiusi con le consegne ancora aperte \n";
            $sql = "SELECT `Order`.id
					FROM
						" . Configure::read('DB.prefix') . "deliveries Delivery,
						`" . Configure::read('DB.prefix') . "orders` `Order`
					WHERE
						Delivery.organization_id = " . (int) $organization_id . "
						and `Order`.organization_id = " . (int) $organization_id . "
						and Delivery.stato_elaborazione = 'OPEN'
						and `Order`.delivery_id = Delivery.id
						and `Order`.state_code = 'RI-OPEN-VALIDATE'
						and DATE(Delivery.data) >= CURDATE()
						and `Order`.data_fine_validation < CURDATE()";
            if (!empty($order_id))
                $sql .= " AND `Order`.id = " . (int) $order_id;
            if ($debugSql)
                echo $sql . "\n";
            $results = $Order->query($sql);
            if ($debug)
                echo "Aggiornati: " . count($results) . "\n";
            foreach ($results as $result) {
                $sql = "UPDATE `" . Configure::read('DB.prefix') . "orders`
					   SET
							state_code = 'PROCESSED-BEFORE-DELIVERY',
							modified = '" . date('Y-m-d H:i:s') . "'
					   WHERE
					   		organization_id = " . (int) $organization_id . "
					   		and id = " . $result['Order']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $Order->query($sql);
            }

            /*
             * cron: orders da PROCESSED-BEFORE-DELIVERY a OPEN
             * 	estraggo gli ordini in carico al referente prima della consegna che devono riaprirsi
             */
            if ($debug)
                echo "Porto gli ordini da PROCESSED-BEFORE-DELIVERY a OPEN: estraggo gli ordini in carico al referente prima delle consegne che devono riaprirsi \n";
            $sql = "SELECT `Order`.id
					FROM
						" . Configure::read('DB.prefix') . "deliveries Delivery,
						`" . Configure::read('DB.prefix') . "orders` `Order`
					WHERE
						Delivery.organization_id = " . (int) $organization_id . "
						and `Order`.organization_id = " . (int) $organization_id . "
						and Delivery.stato_elaborazione = 'OPEN'
						and `Order`.delivery_id = Delivery.id
						and `Order`.state_code = 'PROCESSED-BEFORE-DELIVERY' 
						and DATE(Delivery.data) >= CURDATE()
						and `Order`.data_fine >= CURDATE()";
            if (!empty($order_id))
                $sql .= " AND `Order`.id = " . (int) $order_id;
            if ($debugSql)
                echo $sql . "\n";
            $results = $Order->query($sql);
            if ($debug)
                echo "Aggiornati: " . count($results) . "\n";
            foreach ($results as $result) {
                $sql = "UPDATE `" . Configure::read('DB.prefix') . "orders`
					   SET
							state_code = 'OPEN',
							modified = '" . date('Y-m-d H:i:s') . "'
					   WHERE
					   		organization_id = " . (int) $organization_id . "
					   		and id = " . $result['Order']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $Order->query($sql);
            }

            /*
             * cron: 
             *  if($user->organization['Organization']['payToDelivery']=='POST')
             *  	orders da PROCESSED-BEFORE-DELIVERY a PROCESSED-POST-DELIVERY
             *  
             *  per ON o ON-POST e' un azione del referente 
             *  	orders da PROCESSED-BEFORE-DELIVERY => INCOMING-ORDER (merce arrivata) => PROCESSED-ON-DELIVERY (in carico al cassiere durante la consegna)
             *  
             *  
             * 	estraggo gli ordini con le consegne chiuse
             */
            if ($organization_payToDelivery == 'POST') {
                if ($debug)
                    echo "Porto gli ordini da PROCESSED-BEFORE-DELIVERY a PROCESSED-POST-DELIVERY: estraggo gli ordini con le consegne chiuse \n";

                $sql = "SELECT `Order`.id
						FROM
							" . Configure::read('DB.prefix') . "deliveries Delivery,
							`" . Configure::read('DB.prefix') . "orders` `Order`
						WHERE
							Delivery.organization_id = " . (int) $organization_id . "
							and `Order`.organization_id = " . (int) $organization_id . "
							and Delivery.stato_elaborazione = 'OPEN'
							and `Order`.delivery_id = Delivery.id
							and `Order`.state_code = 'PROCESSED-BEFORE-DELIVERY'
							and DATE(Delivery.data) <= CURDATE()
							and `Order`.data_fine < CURDATE()";
                if (!empty($order_id))
                    $sql .= " AND `Order`.id = " . (int) $order_id;
                if ($debugSql)
                    echo $sql . "\n";
                $results = $Order->query($sql);
                if ($debug)
                    echo "Aggiornati: " . count($results) . "\n";

                $state_code_next = 'PROCESSED-POST-DELIVERY';

                foreach ($results as $result) {
                    $sql = "UPDATE `" . Configure::read('DB.prefix') . "orders`
						   SET
								state_code = '$state_code_next',
								modified = '" . date('Y-m-d H:i:s') . "'
						   WHERE
					   			organization_id = " . (int) $organization_id . "
					   			and id = " . $result['Order']['id'];
                    if ($debugSql)
                        echo $sql . "\n";
                    $Order->query($sql);
                }
            } //end  if($organization_payToDelivery=='POST')

            /*
             * cron:
             * 
             * non + => e' un qazinoe del referente
             * 
             * 	orders da INCOMING-ORDER a PROCESSED-ON-DELIVERY 
             * 	estraggo gli ordini con le consegne chiuse

              if($organization_payToDelivery=='ON' || $organization_payToDelivery=='ON-POST') {
              if($debug)
              echo "Porto gli ordini da INCOMING-ORDER a PROCESSED-ON-DELIVERY: estraggo gli ordini con le consegne chiuse \n";

              $sql = "SELECT `Order`.id
              FROM
              ".Configure::read('DB.prefix')."deliveries Delivery,
              `".Configure::read('DB.prefix')."orders` `Order`
              WHERE
              Delivery.organization_id = ".(int)$organization_id."
              and `Order`.organization_id = ".(int)$organization_id."
              and Delivery.stato_elaborazione = 'OPEN'
              and `Order`.delivery_id = Delivery.id
              and `Order`.state_code = 'INCOMING-ORDER'
              and DATE(Delivery.data) <= CURDATE()
              and `Order`.data_fine < CURDATE()";
              if(!empty($order_id)) $sql .= " AND `Order`.id = ".(int)$order_id;
              if($debugSql) echo $sql."\n";
              $results = $Order->query($sql);
              if($debug) echo "Aggiornati: ".count($results)."\n";

              foreach($results as $result) {
              $sql ="UPDATE `".Configure::read('DB.prefix')."orders`
              SET
              state_code = 'PROCESSED-ON-DELIVERY',
              modified = '".date('Y-m-d H:i:s')."'
              WHERE
              organization_id = ".(int)$organization_id."
              and id = ".$result['Order']['id'];
              if($debugSql) echo $sql."\n";
              $Order->query($sql);
              }
              } // end if($organization_payToDelivery=='ON' || $organization_payToDelivery=='ON-POST')
             */

            /*
             * cron: orders da PROCESSED-POST-DELIVERY a PROCESSED-BEFORE-DELIVERY
             * 	se la data della consegna da CHUISA e' stata modificata a OPEN
             */
            if ($organization_payToDelivery == 'POST') {
                if ($debug)
                    echo "Porto gli ordini da PROCESSED-POST-DELIVERY a PROCESSED-BEFORE-DELIVERY: la consegna da Chiusa e' stata riaperta (OPEN) \n";

                $sql = "SELECT `Order`.id
						FROM
							" . Configure::read('DB.prefix') . "deliveries Delivery,
							`" . Configure::read('DB.prefix') . "orders` `Order`
						WHERE
							Delivery.organization_id = " . (int) $organization_id . "
							and `Order`.organization_id = " . (int) $organization_id . "
							and Delivery.stato_elaborazione = 'OPEN'
							and `Order`.delivery_id = Delivery.id
							and `Order`.state_code = 'PROCESSED-POST-DELIVERY' 
							and DATE(Delivery.data) > CURDATE() ";
                if (!empty($order_id))
                    $sql .= " AND `Order`.id = " . (int) $order_id;
                if ($debugSql)
                    echo $sql . "\n";
                $results = $Order->query($sql);
                if ($debug)
                    echo "Aggiornati: " . count($results) . "\n";
                foreach ($results as $result) {

                    /*
                     * calcolo se PROCESSED-BEFORE-DELIVERY o OPEN
                     */
                    $data_oggi = date("Y-m-d");
                    if ($results['Order']['data_fine'] > $data_oggi)
                        $state_code = 'PROCESSED-BEFORE-DELIVERY';
                    else
                        $state_code = 'OPEN';

                    $sql = "UPDATE `" . Configure::read('DB.prefix') . "orders`
						   SET
								state_code = '$state_code',
								modified = '" . date('Y-m-d H:i:s') . "'
						   WHERE
					   			organization_id = " . (int) $organization_id . "
					   			and id = " . $result['Order']['id'];
                    if ($debugSql)
                        echo $sql . "\n";
                    $Order->query($sql);
                }
            } // end if($organization_payToDelivery=='POST')
        } catch (Exception $e) {
            if ($debug)
                echo '<br />UtilsCrons::ordersStatoElaborazione()<br />' . $sql . '<br />' . $e;
        }
    }

    /*
     * DES
     *  se DesOrder.data_fine_max scaduta: DesOrdes.stato da OPEN => BEFORE-TRASMISSION
     */

    public function desOrdersStatoElaborazione($des_id, $des_order_id = 0, $debug = true) {

        if ($debug)
            echo date("d/m/Y") . " - " . date("H:i:s") . " Aggiorna lo stato dei DesOrder  \n";
        if ($debug)
            echo " Tratto il DES $des_id \n";

        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder();
        $DesOrder->statoElaborazione($des_id, $des_order_id, $debug);
    }

    /*
     * DES
     *  cancella DesOrder con 
     *  	data_fine_max scaduta / DesOrder.state_code = 'CLOSE'
     * 		ordini non + associati perche' portati in Statistiche
     */

    public function desOrdersDelete($des_id, $des_order_id = 0, $debug = true) {

        if ($debug)
            echo date("d/m/Y") . " - " . date("H:i:s") . " Controllo se cancellare i DesOrder  \n";
        if ($debug)
            echo " Tratto il DES $des_id \n";

        App::import('Model', 'DesOrder');
        $DesOrder = new DesOrder();
        $DesOrder->deleteScaduti($des_id, $des_order_id, $debug);
    }

    /*
     * key ArticlesOrder $organization_id $order_id, $article_organization_id, $article_id
     */

    public function articlesOrdersQtaCart($organization_id, $debug = true) {

        /*
         * Aggiorna il totale della quantita' acquistata per ogni articolo
         */
        if ($debug)
            echo date("d/m/Y") . " - " . date("H:i:s") . " Aggiorna il totale della quantita' acquistata per ogni articolo (ArticlesOrder.qta_cart) e se ArticlesOrder.qta_massima_order > 0 anche ArticlesOrder.stato \n";
        //$user = $this->__getObjUserLocal($organization_id);

        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;

        $options = array();
        $options['conditions'] = array('ArticlesOrder.organization_id' => $organization_id);
        $options['recursive'] = -1;
        $results = $ArticlesOrder->find('all', $options);

        foreach ($results as $result) {

            $organization_id = $result['ArticlesOrder']['organization_id'];
            $order_id = $result['ArticlesOrder']['order_id'];
            $article_organization_id = $result['ArticlesOrder']['article_organization_id'];
            $article_id = $result['ArticlesOrder']['article_id'];

            $ArticlesOrder->aggiornaQtaCart_StatoQtaMax($organization_id, $order_id, $article_organization_id, $article_id, $debug);
        }
    }

    public function articlesBio($organization_id) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " articlesBio() ";

        try {
            App::import('Model', 'Article');
            $Article = new Article;

            $user = $this->__getObjUserLocal($organization_id);
			echo "<pre>";
			print_r($user);
			echo "</pre>";			
            $Article->syncronizeArticleTypeBio($user, 0, false);
        } catch (Exception $e) {
            echo '<br />UtilsCrons::articlesBio()<br />' . $e;
        }
    }

    public function deleteCart($organization_id) {
        
    }

    private function __getUsers($organization_id) {
        App::import('Model', 'User');
        $User = new User;

        $options = array();
        $options['conditions'] = array('User.organization_id' => (int) $organization_id,
            'User.block' => 0);
        $options['fields'] = array('id', 'name', 'email', 'username');
        $options['order'] = Configure::read('orderUser');
        $options['recursive'] = -1;

        $users = $User->find('all', $options);

        /*
          echo "<pre>";
          print_r($users;
          echo "</pre>";
         */

        $conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
        //$users = $User->getUsersList($user, $conditions);

        echo "getUsers(): trovati " . count($users) . " utenti\n";

        return $users;
    }

    private function __getMail() {
        $Email = new CakeEmail(Configure::read('EmailConfig'));
        $Email->helpers(array('Html', 'Text'));
        $Email->template('default');
        $Email->emailFormat('html');

        $Email->replyTo(Configure::read('Mail.no_reply_mail'), Configure::read('Mail.no_reply_name'));
        $Email->from(array(Configure::read('SOC.mail') => Configure::read('SOC.name')));
        $Email->sender(Configure::read('SOC.mail'), Configure::read('SOC.name'));

        return $Email;
    }

    /*
     * $user = new UserLocal() e non new User() se no override App::import('Model', 'User');
     */

    private function __getObjUserLocal($organization_id) {

        App::import('Model', 'Organization');
        $Organization = new Organization;

        $options = array();
        $options['conditions'] = array('Organization.id' => (int) $organization_id);
        $options['recursive'] = -1;
        $organization = $Organization->find('first', $options);

        $user = new UserLocal();
        $user->organization = $organization;

        $paramsConfig = json_decode($organization['Organization']['paramsConfig'], true);
        $paramsFields = json_decode($organization['Organization']['paramsFields'], true);

        $user->organization['Organization'] += $paramsConfig;
        $user->organization['Organization'] += $paramsFields;

        return $user;
    }

    public function usersGmaps($organization_id, $debug = true) {

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

        $options = array();
        $options['conditions'] = array('User.organization_id' => $organization_id);
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

            $userProfile = $this->__getProfileUser($result['User']['id']);

            $lat = $this->__getProfileUserValue($userProfile, 'profile.lat');
            $lng = $this->__getProfileUserValue($userProfile, 'profile.lng');
            $address = $this->__getProfileUserValue($userProfile, 'profile.address');
            $city = $this->__getProfileUserValue($userProfile, 'profile.city');
            $cap = $this->__getProfileUserValue($userProfile, 'profile.postal_code');

            // echo "\n Tratto lo user ".$result['User']['id'].' '.$result['User']['username'].' coordinate '.$lat.' '.$lng.' - address '.$address.' '.$city;

            if ($tot_user_elaborati <= 10 && $lat == '' && $lng == '') {

                if ($address != '' && $city != '') {

                    if ($debug)
                        echo "\n tot_user_elaborati " . $tot_user_elaborati;

                    /* if($debug) {
                      echo "<pre>";
                      print_r($userProfile);
                      echo "</pre>";
                      }
                     */

                    $address = $results[$numResult]['Profile']['gmaps'] = $address . ' ' . $city . ' ' . $cap;

                    $tot_user_elaborati++;
                    $coordinate = $this->__gmap($address, $debug);

                    if ($debug) {
                        echo "<pre>";
                        print_r($coordinate);
                        echo "</pre>";
                    }

                    if (!empty($coordinate)) {
                        $lat = str_replace(",", ".", $coordinate['lat']);
                        $lng = str_replace(",", ".", $coordinate['lng']);

                        $sql = 'INSERT INTO ' . Configure::read('DB.portalPrefix') . 'user_profiles VALUES (' . $result['User']['id'] . ', "profile.lat", "\"' . $lat . '\"" , 10 )';
                        echo "\n " . $sql;
                        $executeInsert = $User->query($sql);

                        $sql = 'INSERT INTO ' . Configure::read('DB.portalPrefix') . 'user_profiles VALUES (' . $result['User']['id'] . ', "profile.lng", "\"' . $lng . '\"" , 11 )';
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

    public function suppliersGmaps($organization_id, $debug = true) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " Dall'indirizzo cerca lng e lat \n";

        App::import('Model', 'Supplier');
        $Supplier = new Supplier;

        $options = array();
        $options['order'] = array('Supplier.id');
        $options['recursive'] = -1;
        $results = $Supplier->find('all', $options);


        $tot_supplier_elaborati = 0;
        foreach ($results as $numResult => $result) {

            /* if($debug) {
              echo "<pre>";
              print_r($result);
              echo "</pre>";
              }
             */

            if ($tot_supplier_elaborati <= 10 && empty($result['Supplier']['lat']) && empty($result['Supplier']['lng'])) {

                if (!empty($result['Supplier']['localita'])) {

                    if ($debug)
                        echo "\n tot_supplier_elaborati " . $tot_supplier_elaborati;

                    if ($debug) {
                        echo "<pre>";
                        print_r($result);
                        echo "</pre>";
                    }

                    $address = $result['Supplier']['gmaps'] = $result['Supplier']['indirizzo'] . ' ' . $result['Supplier']['localita'] . ' ' . $result['Supplier']['cap'];

                    $tot_supplier_elaborati++;
                    $coordinate = $this->__gmap($address, $debug);

                    if ($debug) {
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
                }  // if(!empty($result['Supplier']['localita']))
            }    // if($tot_supplier_elaborati<=10 && empty($result['Supplier']['lat']) && empty($result['Supplier']['lng'])) 
        } // foreach ($results as $numResult => $result) 
    }

    private function __gmap($address, $debug = false) {

        $esito = "";

        $url = "https://maps.google.com/maps/api/geocode/json?sensor=false&address=";

        $url = $url . urlencode($address);

        $resp_json = $this->__curl_file_get_contents($url);
        $resp = json_decode($resp_json, true);

        if ($debug)
            echo "\n " . $url . ' ' . $resp['status'];


        if ($resp['status'] == 'OK') {
            if (isset($resp['results'][0]))
                $esito = $resp['results'][0]['geometry']['location'];
        } else {
            if ($debug) {
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

    private function __curl_file_get_contents($URL) {
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

    public function usersSuppliersOrganizationsReferents($organization_id, $debug = true) {

        echo date("d/m/Y") . " - " . date("H:i:s") . " Controllo se l'utente e' un referente ed appartiene o no al gruppo \n";

        App::import('Model', 'User');
        $User = new User;

        App::import('Model', 'SuppliersOrganizationsReferent');

        $options = array();
        $options['conditions'] = array('User.organization_id' => (int) $organization_id,
            'User.block' => 0);
        $options['fields'] = array('id', 'name', 'email');
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
            $options = array();
            $options['conditions'] = array('SuppliersOrganizationsReferent.organization_id' => $organization_id,
                'SuppliersOrganizationsReferent.user_id' => $user['User']['id']);
            $totRows = $SuppliersOrganizationsReferent->find('count', $options);
            if ($totRows == 0) {
                echo "	non ha produttori associati: lo <span style=color:red>cancello</span> dal gruppo referenti \n";
                $User->joomlaBatchUser(Configure::read('group_id_referent'), $user['User']['id'], 'del', false);
            } else {
                echo "	ha produttori associati ($totRows): lo <span style=color:green>inserisco</span> dal gruppo referenti \n";
                $User->joomlaBatchUser(Configure::read('group_id_referent'), $user['User']['id'], 'add', false);
            }
        }
    }

    public function filesystemLogDelete($organization_id, $debug = true) {

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
               // 	echo 'file '.$file.' con data '.$fileNameDate.' maggiore di '.$data_oggi_diminuita_logs."\n";
            }
        }
    }

    public function prodDeliveriesStatoElaborazione($organization_id, $debug = true, $prod_delivery_id = 0) {
        $debugSql = false;

        try {
            $user = $this->__getObjUserLocal($organization_id);

            $paramsConfig = json_decode($user->organization['Organization']['paramsConfig'], true);
            $paramsFields = json_decode($user->organization['Organization']['paramsFields'], true);
            $organization_payToDelivery = $paramsConfig['payToDelivery'];

            App::import('Model', 'ProdDelivery');
            $ProdDelivery = new ProdDelivery;

            /*
             * cron: consegne senza articoli associati (ProdDeliveriesArticle) in CREATE-INCOMPLETE
             */
            if ($debug)
                echo date("d/m/Y") . " - " . date("H:i:s") . " Porto le consegne senza articoli associati (ProdDeliveriesArticle) in CREATE-INCOMPLETE \n";
            $sql = "SELECT
						ProdDelivery.id
				   FROM
						" . Configure::read('DB.prefix') . "prod_deliveries ProdDelivery LEFT JOIN
						 " . Configure::read('DB.prefix') . "prod_deliveries_articles ProdDeliveriesArticle ON
						 		(ProdDeliveriesArticle.prod_delivery_id = ProdDelivery.id
						 		and ProdDeliveriesArticle.organization_id = " . (int) $organization_id . ")
				   WHERE
						ProdDelivery.organization_id = " . (int) $organization_id . "
						AND (ProdDelivery.prod_delivery_state_id != " . Configure::read('CREATE-INCOMPLETE') . " and ProdDelivery.prod_delivery_state_id != " . Configure::read('CLOSE') . ")
						AND ProdDeliveriesArticle.article_id IS NULL AND ProdDeliveriesArticle.prod_delivery_id IS NULL ";
            if (!empty($prod_delivery_id))
                $sql .= " AND ProdDelivery.id = " . (int) $prod_delivery_id;
            $sql .= " GROUP BY ProdDelivery.id";
            if ($debugSql)
                echo $sql . "\n";
            $results = $ProdDelivery->query($sql);
            if ($debug)
                echo "Aggiornati: " . count($results) . "\n";
            foreach ($results as $result) {
                $sql = "UPDATE " . Configure::read('DB.prefix') . "prod_deliveries 
					   SET
							prod_delivery_state_id = " . Configure::read('CREATE-INCOMPLETE') . ",
							modified = '" . date('Y-m-d H:i:s') . "'
					   WHERE
					   		organization_id = " . (int) $organization_id . "
					   		and id = " . $result['ProdDelivery']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $ProdDelivery->query($sql);
            }

            /*
             * cron: consegne con articoli associati (ProdDeliveriesArticle) da CREATE-INCOMPLETE a OPEN-NEXT o OPEN
             * 		ProdDeliveriesArticle::add
             */
            if ($debug)
                echo "Porto le consegne con articoli associati (ProdDeliveriesArticle) da CREATE-INCOMPLETE a OPEN-NEXT o OPEN (ProdDeliveriesArticle::add) \n";
            $sql = "SELECT
						ProdDelivery.id
				   FROM
						" . Configure::read('DB.prefix') . "prod_deliveries ProdDelivery,
						 " . Configure::read('DB.prefix') . "prod_deliveries_articles ProdDeliveriesArticle
				   WHERE
						ProdDelivery.organization_id = " . (int) $organization_id . "
						AND ProdDeliveriesArticle.organization_id = " . (int) $organization_id . "
						AND ProdDeliveriesArticle.prod_delivery_id = ProdDelivery.id
						AND ProdDelivery.prod_delivery_state_id = " . Configure::read('CREATE-INCOMPLETE');
            if (!empty($prod_delivery_id))
                $sql .= " AND ProdDelivery.id = " . (int) $prod_delivery_id;
            $sql .= " group by ProdDelivery.id";
            if ($debugSql)
                echo $sql . "\n";
            $results = $ProdDelivery->query($sql);
            if ($debug)
                echo "Aggiornati: " . count($results) . "\n";
            foreach ($results as $result) {
                /*
                 * calcolo se OPEN-NEXT o OPEN
                 */
                $data_oggi = date("Y-m-d");
                if ($results['ProdDelivery']['data_inizio'] > $data_oggi)
                    $prod_delivery_state_id = Configure::read('OPEN-NEXT');
                else
                    $prod_delivery_state_id = Configure::read('OPEN');

                $sql = "UPDATE " . Configure::read('DB.prefix') . "prod_deliveries
						SET
							prod_delivery_state_id = $prod_delivery_state_id,
							modified = '" . date('Y-m-d H:i:s') . "'
						WHERE
					   		organization_id = " . (int) $organization_id . "
							and id = " . $result['ProdDelivery']['id'];
                if ($debugSql)
                    echo $sql . "\n";
                $ProdDelivery->query($sql);
            }

            /*
             * cron: orders in OPEN-NEXT
             * 	estraggo gli ordini che si aprono successivamente
             */
            if ($debug)
                echo "Porto gli ordini a OPEN-NEXT per quelli che devono ancora aprirsi \n";
            $sql = "SELECT
						count(ProdDelivery.id) as totale
				   FROM
						" . Configure::read('DB.prefix') . "prod_deliveries as ProdDelivery
				   WHERE
				   		ProdDelivery.organization_id = " . (int) $organization_id . "
				   		AND (ProdDelivery.prod_delivery_state_id != " . Configure::read('CREATE-INCOMPLETE') . "
				   			AND ProdDelivery.prod_delivery_state_id != " . Configure::read('OPEN-NEXT') . "
				   			AND ProdDelivery.prod_delivery_state_id != " . Configure::read('CLOSE') . "
				   			)
				   		AND ProdDelivery.data_inizio > CURDATE() ";  // data_inizio successiva ad oggi
            if (!empty($prod_delivery_id))
                $sql .= " AND id = " . (int) $prod_delivery_id;
            if ($debugSql)
                echo $sql . "\n";
            $results = current($ProdDelivery->query($sql));
            $sql = "UPDATE
						" . Configure::read('DB.prefix') . "prod_deliveries
				   SET
						prod_delivery_state_id = " . Configure::read('OPEN-NEXT') . ",
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
						organization_id = " . (int) $organization_id . "
				   		AND (prod_delivery_state_id != " . Configure::read('CREATE-INCOMPLETE') . "
				   			AND prod_delivery_state_id != " . Configure::read('OPEN-NEXT') . "
				   			AND prod_delivery_state_id != " . Configure::read('CLOSE') . "
				   			)
				   		AND data_inizio > CURDATE() ";  // data_inizio successiva ad oggi
            if (!empty($prod_delivery_id))
                $sql .= " AND id = " . (int) $prod_delivery_id;
            if ($debugSql)
                echo $sql . "\n";
            if ($debug)
                echo "Aggiornati: " . $results[0]['totale'] . "\n";
            $ProdDelivery->query($sql);


            /*
             * cron: consegne da OPEN-NEXT a OPEN
             * 	estraggo le consegne che si aprono oggi (o dovrebbero essere gia' aperti!)
             */
            if ($debug)
                echo "Porto gli ordini da OPEN-NEXT a OPEN: estraggo gli ordini che si aprono oggi (o dovrebbero essere gia' aperti!)\n";
            $sql = "SELECT count(id) as totale
				   FROM " . Configure::read('DB.prefix') . "prod_deliveries as ProdDelivery
				   WHERE
				   		organization_id = " . (int) $organization_id . "
				   		and prod_delivery_state_id = " . Configure::read('OPEN-NEXT') . "
				   		and data_inizio <= CURDATE()"; // data_inizio precedente o uguale ad oggi
            if ($debugSql)
                echo $sql . "\n";
            $results = current($ProdDelivery->query($sql));
            $sql = "UPDATE " . Configure::read('DB.prefix') . "prod_deliveries
				   SET
						prod_delivery_state_id = " . Configure::read('OPEN') . ",
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
				   		organization_id = " . (int) $organization_id . "
				   		and prod_delivery_state_id = " . Configure::read('OPEN-NEXT') . "
				   		and data_inizio <= CURDATE() ";  // data_inizio precedente o uguale ad oggi
            if (!empty($prod_delivery_id))
                $sql .= " AND id = " . (int) $prod_delivery_id;
            if ($debugSql)
                echo $sql . "\n";
            if ($debug)
                echo "Aggiornati: " . $results[0]['totale'] . "\n";
            $ProdDelivery->query($sql);

            /*
             * cron: consegne da OPEN a PROCESSED-POST-DELIVERY
             * 	estraggo le consegne aperte che devono chiudersi
             */
            if ($debug)
                echo "Porto le consegne da OPEN a PROCESSED-POST-DELIVERY: estraggo le consegne aperte che devono chiudersi \n";

            $sql = "UPDATE
						" . Configure::read('DB.prefix') . "prod_deliveries as ProdDelivery
				   SET
						prod_delivery_state_id = " . Configure::read('PROCESSED-POST-DELIVERY') . ",
						modified = '" . date('Y-m-d H:i:s') . "'
				   WHERE
				   		organization_id = " . (int) $organization_id . "
				   		and ProdDelivery.stato_elaborazione = 'OPEN'
				   		and ProdDelivery.prod_delivery_state_id = " . Configure::read('OPEN') . "
				   		and ProdDelivery.data_fine < CURDATE()";
            if (!empty($prod_delivery_id))
                $sql .= " AND ProdDelivery.id = " . (int) $prod_delivery_id;
            if ($debugSql)
                echo $sql . "\n";
            $ProdDelivery->query($sql);
        } catch (Exception $e) {
            if ($debug)
                echo '<br />UtilsCrons::prodDeliveriesStatoElaborazione()<br />' . $sql . '<br />' . $e;
        }
    }

    /*
     * per ogni organization scrive un file seo.rss in /rss/
     */

    public function rss($organization_id, $debug = false) {

        /*
         * DATE_RFC850 Thursday, 30-Apr-15 13:03:33 GMT
         * DATE_RFC822 Thu, 30 Apr 15 13:04:48 +0000
         */
        $formatDate = DATE_RFC822;

        $user = $this->__getObjUserLocal($organization_id);

        $j_seo = $user->organization['Organization']['j_seo'];
        $fileName1 = $j_seo . '.rss';
        $fileName2 = $j_seo . '2.rss';
        $fileName3 = $j_seo . '-gcalendar.rss';
        $link = 'http://www.portalgas.it/home-' . $j_seo . '/consegne-' . $j_seo;

        /*
         * data nel formato Wed, 02 Oct 2002 08:00:00 EST
         */
        $d = date('Y-m-d H:i:s T', time());
        $date = gmdate($formatDate, strtotime($d));

        $rssHeader = '';
        $rssHeader .= '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
        $rssHeader .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $rssHeader .= '<channel>' . "\n";
        $rssHeader .= '<atom:link href="http://www.portalgas.it/rss/' . $j_seo . '.rss" type="application/rss+xml" />' . "\n";
        $rssHeader .= '<title>Ordini del G.A.S. ' . $this->appHelper->organizationNameError($user->organization) . '</title>' . "\n";
        $rssHeader .= '<link>http://www.portalgas.it</link>' . "\n";
        $rssHeader .= '<description>Gestionale web per G.A.S. (GAS gruppo d\'acquisto solidale)</description>' . "\n";
        $rssHeader .= '<pubDate>' . $date . '</pubDate>' . "\n";
        $rssHeader .= '<lastBuildDate>' . $date . '</lastBuildDate>' . "\n";
        $rssHeader .= '<copyright>Copyright 2012 - ' . date('Y') . ' - portalgas.it</copyright>' . "\n";

        if (!empty($user->organization['Organization']['img1'])) {
            $rssHeader .= '<image>' . "\n";
            $rssHeader .= '<url>http://www.portalgas.it' . Configure::read('App.web.img.upload.content') . '/' . $user->organization['Organization']['img1'] . '</url>' . "\n";
            $rssHeader .= '<link>http://www.portalgas.it</link>' . "\n";
            $rssHeader .= '<title>Ordini del G.A.S. ' . $this->appHelper->organizationNameError($user->organization) . '</title>' . "\n";
            $rssHeader .= '</image>' . "\n";
        }

        App::import('Model', 'Order');
        $Order = new Order;

        App::import('Model', 'Supplier');

        $options = array();
        $options['conditions'] = array('Delivery.organization_id' => $user->organization['Organization']['id'],
            'Order.organization_id' => $user->organization['Organization']['id'],
            'Delivery.isVisibleBackOffice' => 'Y',
            'Delivery.isVisibleFrontEnd' => 'Y',
            'DATE(Delivery.data) >= CURDATE()',
            'Delivery.stato_elaborazione' => 'OPEN',
            'Order.state_code != ' => 'CREATE-INCOMPLETE');
        $options['recursive'] = 0;
        $options['order'] = array('Delivery.data asc', 'Order.data_inizio');
        $results = $Order->find('all', $options);
        /*
          if($debug) {
          echo "<pre>";
          print_r ($results);
          echo "</pre>";
          }
         */
        $rssItems1 = '';
        $rssItems2 = '';
        $rssItems3 = '';
        foreach ($results as $numResult => $result) {

            /*
             * data nel formato Wed, 02 Oct 2002 08:00:00 GMT
             */
            $d = date($result['Delivery']['data'], time());
            $date = gmdate($formatDate, strtotime($d));


            $guid = 'http://www.portalgas.it/' . $j_seo . '-' . $result['Order']['id'];

            /*
             * titolo uno: Il Girasole - Ordine aperto dal 1 febbraio al 7 febbraio - Consegna 10 febbraio
             */
            $delivery = '';
            $title1 = '';
            if ($result['Delivery']['sys'] == 'Y')
                $delivery .= "Consegna " . $result['Delivery']['luogo'];
            else
                $delivery .= $this->timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");

            $title1 = $result['SuppliersOrganization']['name'] . ', ordine aperto fino a ' . $this->timeHelper->i18nFormat($result['Order']['data_fine'], "%A %e %B") . ' - Consegna ';
            if ($result['Delivery']['sys'] == 'Y')
                $title1 .= $result['Delivery']['luogo'];
            else
                $title1 .= $this->timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");

            $rssItems1 .= '<item>' . "\n";
            $rssItems1 .= '<guid>' . $guid . '</guid>' . "\n";
            $rssItems1 .= '<category ><![CDATA[' . $this->__pulisciStringaRss($delivery) . ']]></category >' . "\n";
            $rssItems1 .= '<title><![CDATA[' . $this->__pulisciStringaRss($title1) . ']]></title>' . "\n";
            $rssItems1 .= '<link>' . $link . '</link>' . "\n";
            $rssItems1 .= '<pubDate>' . $date . '</pubDate>' . "\n";
            if (!empty($result['Order']['nota']))
                $rssItems1 .= '<description><![CDATA[' . $this->__pulisciStringaRss($result['Order']['nota']) . ']]></description>' . "\n";

            $rssItems1 .= '</item>' . "\n";


            /*
             * titolo due
             */
            $delivery = '';
            $title2 = '';
            if ($result['Delivery']['sys'] == 'Y')
                $delivery .= "Consegna " . $result['Delivery']['luogo'];
            else
                $delivery .= ucfirst($this->timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B"));

            $title2 .= $delivery . ' - ' . $result['SuppliersOrganization']['name'];

            $title2 .= ", ordine aperto fino a " . $this->timeHelper->i18nFormat($result['Order']['data_fine'], "%A %e %B");
            if ($result['Delivery']['sys'] == 'N')
                $title2 .= ", " . $result['Delivery']['luogo'];

            $rssItems2 .= '<item>' . "\n";
            $rssItems2 .= '<guid>' . $guid . '</guid>' . "\n";
            $rssItems2 .= '<category ><![CDATA[' . $this->__pulisciStringaRss($delivery) . ']]></category >' . "\n";
            $rssItems2 .= '<title><![CDATA[' . $this->__pulisciStringaRss($title2) . ']]></title>' . "\n";
            $rssItems2 .= '<link>' . $link . '</link>' . "\n";
            $rssItems2 .= '<pubDate>' . $date . '</pubDate>' . "\n";
            if (!empty($result['Order']['nota']))
                $rssItems2 .= '<description><![CDATA[' . $this->__pulisciStringaRss($result['Order']['nota']) . ']]></description>' . "\n";

            $rssItems2 .= '</item>' . "\n";


            /*
             * titolo tre: 
             * 		una riga con Order.data_inizio
             * 		una riga con Order.data_fine
             */
            $data_inizio = date($result['Order']['data_inizio'], time());
            $data_inizio = gmdate($formatDate, strtotime($data_inizio));

            $data_fine = date($result['Order']['data_fine'], time());
            $data_fine = gmdate($formatDate, strtotime($data_fine));

            $delivery = '';
            $title_inizio = '';
            if ($result['Delivery']['sys'] == 'Y')
                $delivery .= "Consegna " . $result['Delivery']['luogo'];
            else
                $delivery .= $this->timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");


            /*
             * Order.data inizio
             */
            $guid = $guid . '-inizio';

            $title_inizio = "Apertura ordine " . $result['SuppliersOrganization']['name'] . ' - Consegna ';
            if ($result['Delivery']['sys'] == 'Y')
                $title_inizio .= $result['Delivery']['luogo'];
            else
                $title_inizio .= $this->timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");

            $rssItems3 .= '<item>' . "\n";
            $rssItems3 .= '<guid>' . $guid . '</guid>' . "\n";
            $rssItems3 .= '<category><![CDATA[' . $this->__pulisciStringaRss($delivery) . ']]></category >' . "\n";
            $rssItems3 .= '<title><![CDATA[' . $this->__pulisciStringaRss($title_inizio) . ']]></title>' . "\n";
            $rssItems3 .= '<link>' . $link . '</link>' . "\n";
            $rssItems3 .= '<pubDate>' . $data_inizio . '</pubDate>' . "\n";
            $rssItems3 .= '<description><![CDATA[Ordine aperto fino a ' . $this->timeHelper->i18nFormat($result['Order']['data_fine'], "%A %e %B") . ']]></description>' . "\n";
            $rssItems3 .= '</item>' . "\n";
            /*
             * Order.data fine
             */
            $guid = $guid . '-fine';

            $title_fine = "Chiusura ordine " . $result['SuppliersOrganization']['name'] . ' - Consegna ';
            if ($result['Delivery']['sys'] == 'Y')
                $title_fine .= $result['Delivery']['luogo'];
            else
                $title_fine .= $this->timeHelper->i18nFormat($result['Delivery']['data'], "%A %e %B");

            $rssItems3 .= '<item>' . "\n";
            $rssItems3 .= '<guid>' . $guid . '</guid>' . "\n";
            $rssItems3 .= '<category><![CDATA[' . $this->__pulisciStringaRss($delivery) . ']]></category >' . "\n";
            $rssItems3 .= '<title><![CDATA[' . $this->__pulisciStringaRss($title_fine) . ']]></title>' . "\n";
            $rssItems3 .= '<link>' . $link . '</link>' . "\n";
            $rssItems3 .= '<pubDate>' . $data_fine . '</pubDate>' . "\n";
            $rssItems3 .= '</item>' . "\n";
        } // end loop items

        $rssFooter .= '</channel>' . "\n";
        $rssFooter .= '</rss>' . "\n";

        echo date("d/m/Y") . " - " . date("H:i:s") . " " . $this->AppRoot . DS . 'rss' . DS . $fileName1 . "\n";
        echo date("d/m/Y") . " - " . date("H:i:s") . " " . $this->AppRoot . DS . 'rss' . DS . $fileName2 . "\n";
        echo date("d/m/Y") . " - " . date("H:i:s") . " " . $this->AppRoot . DS . 'rss' . DS . $fileName3 . "\n";

        $rss1 = $rssHeader . $rssItems1 . $rssFooter;
        $rss2 = $rssHeader . $rssItems2 . $rssFooter;
        $rss3 = $rssHeader . $rssItems3 . $rssFooter;

        if ($debug) {
            echo "<code>";
            echo "<pre>";
            print_r($rss1);
            echo "</pre>";
            echo '<hr>';
            echo "<pre>";
            print_r($rss2);
            echo "</pre>";
            /*
              echo '<hr>';
              echo "<pre>";
              print_r ($rss3);
              echo "</pre>";
             */
            echo "</code>";
        }

        $file1 = new File($this->AppRoot . DS . 'rss' . DS . $fileName1, true);
        $file1->write($rss1);

        $file2 = new File($this->AppRoot . DS . 'rss' . DS . $fileName2, true);
        $file2->write($rss2);

        $file3 = new File($this->AppRoot . DS . 'rss' . DS . $fileName3, true);
        $file3->write($rss3);
    }

    private function __getProfileUser($user_id = 0) {

        App::import('Model', 'User');
        $User = new User;

        $sql = "SELECT profile_key, profile_value 
					FROM " . Configure::read('DB.portalPrefix') . "user_profiles 
					WHERE user_id = " . $user_id;
        $userProfile = $User->query($sql);

        return $userProfile;
    }

    /*
      [0] => Array
      (
      [j_user_profiles] => Array
      (
      [profile_key] => profile.region
      [profile_value] => "MI"
      )

      )
     */

    private function __getProfileUserValue($userProfile, $key) {

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

                if ($debug)
                    echo '<br />' . $profile['j_user_profiles']['profile_key'] . ' ' . $key . ' => ' . $profile['j_user_profiles']['profile_value'] . ' ' . $value;

                break;
            }
        }

        return $value;
    }

    private function __getContentInfo() {
        App::import('Model', 'Msg');
        $Msg = new Msg;

        $results = $Msg->getRandomMsg();
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
        if (!empty($results))
            $content_info = $results['Msg']['testo'];
        else
            $content_info = '';

        return $content_info;
    }

    private function __pulisciStringaRss($str) {

        $str = strip_tags($str);
        //$str = utf8_encode(htmlentities($str,ENT_COMPAT,'utf-8'));
        //$str = htmlspecialchars($str, ENT_QUOTES);
        $str = html_entity_decode($str);  // to &agrave; to ...
        $str = str_replace("&amp;", "", $str);

        return $str;
    }

}

class UserLocal {

    public $organization;

}

?>
