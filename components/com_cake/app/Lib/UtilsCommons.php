<?php
/*
 * class inclusa in AppController ($this->utilsCommons)
 * 				 in DataBehavior  ($this->utilsCommons)
 * 				 in Model         ($this->utilsCommons)
 * 				 in AppHelper	  ($this->utilsCommons) utilizzato in view $this->App->utilsCommons->...
 * */
App::uses('TimeHelper', 'View/Helper');

class UtilsCommons {

    private $time;

    public function __construct($time = null) {
        $this->time = $time;
    }

    // genera una stringa casuale della lunghezza desiderata
    public function random_string($length) {
        $string = "";

        // genera una stringa casuale che ha lunghezza
        // uguale al multiplo di 32 successivo a $length
        for ($i = 0; $i <= ($length / 32); $i++)
            $string .= md5(time() + rand(0, 99));

        // indice di partenza limite
        $max_start_index = (32 * $i) - $length;

        // seleziona la stringa, utilizzando come indice iniziale
        // un valore tra 0 e $max_start_point
        $random_string = substr($string, rand(0, $max_start_index), $length);

        return $random_string;
    }

    public function string_starts_with($string, $search) {
        return (strncmp($string, $search, strlen($search)) == 0);
    }

    /*
     * se l'anno di date2 e' > 2039 bugs
     */
    public function dayDiffToDate($date1, $date2 = null) {
        // 86400 seconds = day
        date_default_timezone_set('Europe/London');

        if (empty($date2))
            $date2 = date("Y-m-d");

        $dateToTime1 = strtotime($date1);
        $dateToTime2 = strtotime($date2);

        $interval = round(($dateToTime1 - $dateToTime2) / 86400);

        $dayDiffToDate = (-1 * $interval);
        return $dayDiffToDate;
    }

    /*
     *
     * per rigeneralo (se no lo prende dalla cache)
     *  - impostarlo a if(1==1) o
     *  - cancellare apc
     */
    public function getLegendaProdDeliveriesState() {

        $debug = false;

        if ($debug)
            echo '<br />getLegendaProdDeliveriesState ';

        $htmlLegenda = '';

        if (Cache::read('legendaProdDeliveriesState') === false)
            $creaHtmlLegenda = true;
        else {
            $htmlLegenda = Cache::read('legendaProdDeliveriesState');
            $creaHtmlLegenda = false;
        }

        /*
         * per rigeneralo (se no lo prende dalla cache) $debug = true
         *  - cancellare apc
         */
        if ($creaHtmlLegenda || $debug) {
            App::import('Model', 'ProdDeliveriesState');
            $ProdDeliveriesState = new ProdDeliveriesState;

            $options = [];
            $options['conditions'] = ['ProdDeliveriesState.flag_produttore' => 'Y'];
            $options['order'] = ['ProdDeliveriesState.sort'];
            $options['recursive'] = -1;
            $prodDeliveriesStates = $ProdDeliveriesState->find('all', $options);

            if ($debug) {
                echo "<pre>";
                print_r($options);
                print_r($prodDeliveriesStates);
                echo "</pre>";
            }

            $colsWidth = floor(100 / count($prodDeliveriesStates));

            $htmlLegenda = '';
            $htmlLegenda .= '<table cellpadding="0" cellspacing="0" border="0">';
            $htmlLegenda .= "\r\n";

            $htmlLegenda .= '<tr>';
            foreach ($prodDeliveriesStates as $prodDeliveriesState) {
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '<td id="icoOrder' . $prodDeliveriesState['ProdDeliveriesState']['code'] . '" class="tdLegendaOrdersStateIco">';
                $htmlLegenda .= '<div style="padding-left:45px;width: 80%;cursor: pointer;" class="action orderStato' . $prodDeliveriesState['ProdDeliveriesState']['code'] . '" title="' . $prodDeliveriesState['ProdDeliveriesState']['intro'] . '">' . $prodDeliveriesState['ProdDeliveriesState']['label'] . '</div>&nbsp;';
                $htmlLegenda .= '</td>';
            }
            $htmlLegenda .= '</tr>';

            $htmlLegenda .= '<tr>';
            $htmlLegenda .= '<td id="tdLegendaOrdersStateTesto" colspan="' . count($prodDeliveriesStates) . '" style="border-bottom:none;background-color:#FFFFFF;height:50px;">';

            $htmlLegenda .= "\r\n";
            foreach ($prodDeliveriesStates as $prodDeliveriesState) {
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '<div class="testoLegendaTesoriereStato" id="testoOrder' . $prodDeliveriesState['ProdDeliveriesState']['code'] . '" style="display:none;">';
                $htmlLegenda .= $prodDeliveriesState['ProdDeliveriesState']['descrizione'];
                $htmlLegenda .= '</div>';
            }
            $htmlLegenda .= '</td>';
            $htmlLegenda .= '</tr>';

            $htmlLegenda .= '</table>';


            $htmlLegenda .= "\r\n";
            $htmlLegenda .= '<script type="text/javascript">';
            $htmlLegenda .= "\r\n";
            $htmlLegenda .= 'function bindLegendaProd() {';
            $htmlLegenda .= "\r\n";
            foreach ($prodDeliveriesStates as $prodDeliveriesState) {
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= 'jQuery( ".orderStato' . $prodDeliveriesState['ProdDeliveriesState']['code'] . '" ).mouseenter(function () {';
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '	jQuery(".tdLegendaOrdersStateIco").css("background-color","#ffffff").css("border-radius","0px");';
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '	jQuery(".testoLegendaTesoriereStato").hide();';
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '	jQuery("#icoOrder' . $prodDeliveriesState['ProdDeliveriesState']['code'] . '").css("background-color","yellow").css("border-radius","15px 15px 15px 15px");';
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '	jQuery(".tdLegendaOrdersStateTesto").css("background-color","#F0F0F0");';
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '	jQuery("#testoOrder' . $prodDeliveriesState['ProdDeliveriesState']['code'] . '").show();';
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '});';

                $htmlLegenda .= "\r\n";
                $htmlLegenda .= 'jQuery( ".orderStato' . $prodDeliveriesState['ProdDeliveriesState']['code'] . '" ).mouseleave(function () {';
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '	jQuery(".tdLegendaOrdersStateIco").css("background-color","#ffffff");';
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '	jQuery(".testoLegendaTesoriereStato").hide();';
                $htmlLegenda .= "\r\n";
                $htmlLegenda .= '});';
            }
            $htmlLegenda .= "\r\n";
            $htmlLegenda .= '}</script>';

            Cache::write('legendaProdDeliveriesState', $htmlLegenda);
        }

        return $htmlLegenda;
    }

    public function getOrderTime($order) {
		/*
         echo "<pre>";
         print_r($order);
         echo "</pre>";
        */

        $str = '';

        if ($order['state_code'] != 'CREATE-INCOMPLETE') {

			switch($order['state_code']) {
				case 'OPEN-NEXT':
					$str .= '<span style="color:#000000;">Aprira&grave; ' . $this->time->i18nFormat($order['data_inizio'], "%A %e %B") . '</span>';
				break;
				case 'OPEN':
				case 'RI-OPEN-VALIDATE':
					if ($order['dayDiffToDateFine'] >= Configure::read('GGOrderCloseNext')) {
						$str .= '<span style="background-color:#999999;color:yellow;">Si sta chiudendo! ';
						if ($order['dayDiffToDateFine'] == 0)
							$str .= 'oggi';
						else
							$str .= 'Tra&nbsp;' . (-1 * $order['dayDiffToDateFine']) . '&nbsp;gg';
						$str .= '</span>';
					} else
						$str .= '<span style="color:green;font-weight:bold;">Aperto</span>';
				break;
				// case ProdGasPromotion
				case 'TRASMISSION-TO-GAS': 
				case 'WORKING':          
					if ($order['dayDiffToDateInizio'] < 0) 
						$str .= '<span style="color:#000000;">Aprira&grave; ' . $this->time->i18nFormat($order['data_inizio'], "%A %e %B") . '</span>';
					else
					if ($order['dayDiffToDateFine'] >= Configure::read('GGOrderCloseNext')) {
						$str .= '<span style="background-color:#999999;color:yellow;">Si sta chiudendo! ';
						if ($order['dayDiffToDateFine'] == 0)
							$str .= 'oggi';
						else
							$str .= 'Tra&nbsp;' . (-1 * $order['dayDiffToDateFine']) . '&nbsp;gg';
						$str .= '</span>';
					} else
						$str .= '<span style="color:green;font-weight:bold;">Aperto</span>';				
				break;
				default:
					$str .= '<span style="color:red;font-weight: bold;">Chiuso</span>';
				break;
			}
        }

        return $str;
    }

    public function getProdDeliveryTime($prod_delivery) {

        /* echo "<pre>";
          print_r($prod_delivery);
          echo "</pre>";
         */

        $str = '';

        if ($prod_delivery['prod_delivery_state_id'] != Configure::read('CREATE-INCOMPLETE')) {

            if ($prod_delivery['prod_delivery_state_id'] == Configure::read('OPEN-NEXT'))
                $str .= '<span style="color:#000000;">Aprira&grave; ' . $this->time->i18nFormat($prod_delivery['data_inizio'], "%A %e %B") . '</span>';
            else
            if ($prod_delivery['prod_delivery_state_id'] == Configure::read('OPEN')) {
                if ($prod_delivery['dayDiffToDateFine'] >= Configure::read('GGOrderCloseNext')) {
                    $str .= '<span style="background-color:#999999;color:yellow;">Si sta chiudendo! ';
                    if ($prod_delivery['dayDiffToDateFine'] == 0)
                        $str .= 'oggi';
                    else
                        $str .= 'Tra&nbsp;' . (-1 * $prod_delivery['dayDiffToDateFine']) . '&nbsp;gg';
                    $str .= '</span>';
                } else
                    $str .= '<span style="color:green;font-weight:bold;">Aperto</span>';
            } else
                $str .= '<span style="color:red;font-weight: bold;">Chiuso</span>';
        }

        return $str;
    }

    /*
     * $user_target = USER, REFERENTE, TESORIERE
     * $doc_options 
     * 		to_users_all_modify  "Documento con elenco diviso per utente con tutte le modifiche (per confrontare i dati dell'utente con le modifiche del referente)"
     * 		to_users			 "Documento con elenco diviso per utente (per pagamento dell'utente)"
     * 		to_supplier			 "Documento con elenco diviso per produttore (per fattura al produttore)"
     * 		to_articles			 "Documento con articoli aggregati (per il produttore)"
     * 		to_articles_details  "Documento con articoli aggregati con il dettaglio degli utenti"
     *
     * 		user_request_payment  			richieste di pagamento dell'utente
     * 		user_cart			  			carrello dell'utente
     * 		articles_supplier_organization  lista degli articoli di un produttore
     * 		admin_articles_supplier_organization  lista degli articoli di un produttore (anche con stato = N)
     * 		users_data						elenco utenti
     * 		users_data_delivery				elenco utenti associati alla consegna
     */

    public function getFileData($user, $doc_options, $params = null, $user_target = null) {

		$debug = false;
		
        $fileName = '';
        $fileTitle = '';

		if($debug) {
			echo "<pre>";
			print_r($doc_options);
			echo "</pre>";
		}
		
        switch ($doc_options) {
            /*
             * stampe dei referenti e tesoriere
             */
            case 'to-users':
            case 'to-users-label':
            case 'to-users-all-modify':
            case 'to-supplier':
            case 'to-articles-monitoring':
            case 'to-articles':
            case 'to-articles-details':

                if (isset($params['delivery_id']) && isset($params['order_id'])) {
                    $order_id = $params['order_id'];

                    App::import('Model', 'Order');
                    $Order = new Order;

                    $conditions = ['Order.organization_id' => (int) $user->organization['Organization']['id'], 'Order.id' => $order_id];
                    $results = $Order->find('first', ['conditions' => $conditions, 'recursive' => 0]);
                    if ($results['Delivery']['sys'] == 'N')
                        $delivery_data = $this->dateFormat($results['Delivery']['data']);
                    else
                        $delivery_data = Configure::read('DeliveryToDefinedLabel');

//					$fileName .= 'report_'.strtolower($user_target).'_'.strtolower(__('Delivery')).'_'.$results['Delivery']['luogoData'].'_'.$results['SupplierOrganization']['name'];
//					$fileTitle .= 'Report '.strtolower($user_target).' '.strtolower(__('Delivery')).' '.$results['Delivery']['luogoData'].' '.$results['SupplierOrganization']['name'];
                    $fileName .= strtolower(__('Delivery')) . '_' . $delivery_data . '_' . $results['SupplierOrganization']['name'];
                    $fileTitle .= strtolower(__('Delivery')) . ' del ' . $delivery_data . ' ' . $results['SupplierOrganization']['name'];
                }
                else
                if (isset($params['delivery_id'])) {
                    $delivery_id = $params['delivery_id'];

                    App::import('Model', 'Delivery');
                    $Delivery = new Delivery;

                    $conditions = ['Delivery.organization_id' => (int) $user->organization['Organization']['id'], 'Delivery.id' => $delivery_id];
                    $results = $Delivery->find('first', ['conditions' => $conditions, 'recursive' => -1]);

                    if ($results['Delivery']['sys'] == 'N')
                        $delivery_data = $this->dateFormat($results['Delivery']['data']);
                    else
                        $delivery_data = Configure::read('DeliveryToDefinedLabel');

//					$fileName .= 'report_'.strtolower($user_target).'_'.strtolower(__('Delivery')).'_'.$results['Delivery']['luogoData'];
//					$fileTitle .= 'Report '.strtolower($user_target).' '.strtolower(__('Delivery')).' '.$results['Delivery']['luogoData'];
                    $fileName .= strtolower(__('Delivery')) . '_' . $delivery_data;
                    $fileTitle .= strtolower(__('Delivery')) . ' del ' . $delivery_data;
                }

                switch ($doc_options) {
                    case 'to-users':
                        $fileName .= '_diviso_per_utente_';
                        $fileTitle .= ' diviso per utente ';
                        break;
                    case 'to-users-label':
                        $fileName .= '_per_la_consegna_';
                        $fileTitle .= ' per la consegna ';
                        break;
                    case 'to-users-all-modify':
                        $fileName .= '_diviso_per_utente_con_tutte_le_modifiche_';
                        $fileTitle .= ' diviso per utente con tutte le modifiche ';
                        break;
                    case 'to-supplier':
                        $fileName .= '_diviso_per_produttore_';
                        $fileTitle .= ' diviso per produttore ';
                        break;
                    case 'to-articles-monitoring':
                        $fileName .= '_con_articoli_aggregati_per_monitorare_';
                        $fileTitle .= ' con articoli aggregati per monitorare ';
                        break;
                    case 'to-articles':
                        $fileName .= '_con_articoli_aggregati_';
                        $fileTitle .= ' con articoli aggregati ';
                        break;
                    case 'to-articles-details':
                        $fileName .= '_con_articoli_aggregati_con_dettaglio_utenti_';
                        $fileTitle .= ' con articoli aggregati con dettaglio utenti ';
                        break;
                }
                break;
            /*
             * stampa carrello utente o
             * tutti gli acquisti di un utente per una consegna (cassiere) 
             */ 
            case 'user_cart':
            case 'to-delivery-cassiere-users-all-split':
            case 'to-delivery-cassiere-user-one':
                if (isset($params['delivery_id']) && isset($params['user_id'])) {
                    $delivery_id = $params['delivery_id'];
                    $user_id = $params['user_id'];

                    App::import('Model', 'Delivery');
                    $Delivery = new Delivery;
                    $conditions = ['Delivery.organization_id' => (int) $user->organization['Organization']['id'], 'Delivery.id' => $delivery_id];
                    $resultsDelivery = $Delivery->find('first', ['conditions' => $conditions, 'recursive' => -1]);
                    if ($resultsDelivery['Delivery']['sys'] == 'N')
                        $delivery_data = $this->dateFormat($resultsDelivery['Delivery']['data']);
                    else
                        $delivery_data = Configure::read('DeliveryToDefinedLabel');

                    App::import('Model', 'User');
                    $User = new User;
                    $conditions = array('User.organization_id' => (int) $user->organization['Organization']['id'],
                        'User.id' => $user_id);
                    $resultsUser = $User->find('first', array('conditions' => $conditions, 'recursive' => -1));

                    $fileName .= 'carrello_' . strtolower(__('Delivery')) . '_' . $delivery_data . '_';
                    if (!empty($resultsUser))
                        $fileName .= 'di_' . $resultsUser['User']['name'];

                    $fileTitle .= 'Carrello ' . strtolower(__('Delivery')) . ' del ' . $delivery_data . ' ';
                    if (!empty($resultsUser))
                        $fileTitle .= 'di ' . $resultsUser['User']['name'];
                }
                break;
            case 'storeroom_cart':
                if (isset($params['delivery_id'])) {
                    $delivery_id = $params['delivery_id'];
 
                    App::import('Model', 'Delivery');
                    $Delivery = new Delivery;
                    $conditions = ['Delivery.organization_id' => (int) $user->organization['Organization']['id'], 'Delivery.id' => $delivery_id];
                    $resultsDelivery = $Delivery->find('first', ['conditions' => $conditions, 'recursive' => -1]);
                    if ($resultsDelivery['Delivery']['sys'] == 'N')
                        $delivery_data = $this->dateFormat($resultsDelivery['Delivery']['data']);
                    else
                        $delivery_data = Configure::read('DeliveryToDefinedLabel');

                    $fileName .= 'cosa_arrivera_dispensa_' . strtolower(__('Delivery')) . '_' . $delivery_data . '_';

                    $fileTitle .= 'Cosa arrivera in dispensa ' . strtolower(__('Delivery')) . ' del ' . $delivery_data . ' '; 
                }
                break;
                
            /*
             * stampa richieste di pagamento dell'utente
             */
            case 'user_request_payment':
                if (isset($params['request_payment_num']) && isset($params['user_id'])) {
                    $request_payment_num = $params['request_payment_num'];
                    $user_id = $params['user_id'];

                    App::import('Model', 'User');
                    $User = new User;
                    $conditions = ['User.organization_id' => (int) $user->organization['Organization']['id'], 'User.id' => $user_id];
                    $resultsUser = $User->find('first', ['conditions' => $conditions, 'recursive' => -1]);

                    $fileName .= 'richiesta_pagamento_n_' . $request_payment_num . '_';
                    if (!empty($resultsUser))
                        $fileName .= 'di_' . $resultsUser['User']['name'] . '_';

                    $fileTitle .= 'Richiesta di pagamento num ' . $request_payment_num . ' ';
                    if (!empty($resultsUser))
                        $fileTitle .= 'di ' . $resultsUser['User']['name'] . ' ';
                }
                break;
            case 'to-tesoriere-request-payment':
                $request_payment_num = $params['request_payment_num'];

                $fileName .= 'richiesta_pagamento_n_' . $request_payment_num . '_';
                $fileTitle .= 'Richiesta di pagamento num ' . $request_payment_num . ' ';
                break;
            /*
             * stampe richieste di pagamento del tesoriere
             */
            case 'request_payment';
                if (isset($params['request_payment_num'])) {
                    $request_payment_num = $params['request_payment_num'];

                    $fileName .= 'report_' . strtolower($user_target) . '_richiesta_pagamento_n_' . $request_payment_num . '_';
                    $fileTitle .= 'Report ' . strtolower($user_target) . ' richiesta pagamento num ' . $request_payment_num . ' ';
                }
                break;
            /*
             * stampa elenco articoli
             */
            case 'articles_supplier_organization':

                if (isset($params['supplier_organization_id'])) {
                    $supplier_organization_id = $params['supplier_organization_id'];

                    App::import('Model', 'SuppliersOrganization');
                    $SuppliersOrganization = new SuppliersOrganization;

                    $conditions = ['SuppliersOrganization.id' => $supplier_organization_id];
                    $supplier = current($SuppliersOrganization->getSuppliersOrganization($user, $conditions));

                    $fileName .= 'elenco_articoli_' . __('Supplier') . '_' . $supplier['SuppliersOrganization']['name'] . '_';
                    $fileTitle .= 'Elenco articoli ' . __('Supplier') . ' ' . $supplier['SuppliersOrganization']['name'] . ' ';
                }
                break;
            /*
             * stampa elenco articoli associati all'ordine
             */
            case 'articles_orders':
                if (isset($params['SuppliersOrganizationName'])) {
                    $SuppliersOrganizationName = $params['SuppliersOrganizationName'];

                    $fileName .= 'elenco_articoli_' . __('Order') . '_' . $SuppliersOrganizationName . '_';
                    $fileTitle .= 'Elenco articoli ' . __('Order') . ' ' . $SuppliersOrganizationName . ' ';

                    if (isset($params['DeliveryLabel'])) {
                        $DeliveryLabel = $params['DeliveryLabel'];

                        $fileName .= '_' . __('Delivery') . '_' . $DeliveryLabel;
                        //$fileTitle .= ' '.__('Delivery').' '.$DeliveryLabel;
                    }
                }
                break;
            case 'users_data':
                $fileName .= 'elenco_utenti_';
                $fileTitle .= 'Elenco utenti ';
                break;
            case 'referents_data':
                $fileName .= 'elenco_referenti_';
                $fileTitle .= 'Elenco referenti ';
                break;
            case 'users_data_delivery':
			case 'users_data_delivery_sum_orders':
                if (isset($params['delivery_id'])) {
                    $delivery_id = $params['delivery_id'];

                    App::import('Model', 'Delivery');
                    $Delivery = new Delivery;
                    $conditions = ['Delivery.organization_id' => (int) $user->organization['Organization']['id'], 'Delivery.id' => $delivery_id];
                    $resultsDelivery = $Delivery->find('first', ['conditions' => $conditions, 'recursive' => -1]);
                    if ($resultsDelivery['Delivery']['sys'] == 'N')
                        $delivery_data = $this->dateFormat($resultsDelivery['Delivery']['data']);
                    else
                        $delivery_data = Configure::read('DeliveryToDefinedLabel');

					if ($doc_options == 'users_data_delivery') {
						$fileName = 'elenco_utenti_presenti_alla_' . strtolower(__('Delivery')) . '_' . $delivery_data . '_';
						$fileTitle = 'Elenco utenti presenti alla ' . strtolower(__('Delivery')) . ' del ' . $delivery_data . ' ';
					}
					if ($doc_options == 'users_data_delivery_sum_orders') {
						$fileName = 'elenco_acquisti_utenti_presenti_alla_' . strtolower(__('Delivery')) . '_' . $delivery_data . '_';
						$fileTitle = 'Elenco acquisti utenti presenti alla ' . strtolower(__('Delivery')) . ' del ' . $delivery_data . ' ';						
					}
					
                }
                break;
            case 'des-referent-to-supplier':
            case 'des-referent-to-supplier-monitoring':
            case 'des-referent-to-supplier-details':
            case 'des-referent-to-supplier-split-org':
            case 'des-referent-to-supplier-split-org-monitoring':
                if (isset($params['des_order_id'])) {
                    $des_order_id = $params['des_order_id'];

                    App::import('Model', 'DesOrder');
                    $DesOrder = new DesOrder;

                    $options = [];
                    $options['conditions'] = ['DesOrder.id' => $des_order_id,
                                              'DesOrder.des_id' => $user->des_id];
                    $options['fields'] = ['DesOrder.luogo'];
                    $options['recursive'] = -1;
                    $desOrderResults = $DesOrder->find('first', $options);

                    if ($doc_options == 'des-referent-to-supplier') {
                        $fileName .= 'elenco_articoli_' . __('Supplier') . '_' . $desOrderResults['DesOrder']['luogo'] . '_';
                        $fileTitle .= 'Elenco articoli ' . __('Supplier') . ' ' . $desOrderResults['DesOrder']['luogo'] . ' ';
                    } else
                    if ($doc_options == 'des-referent-to-supplier-monitoring') {
                        $fileName .= 'elenco_articoli_da_monitorate_' . __('Supplier') . '_' . $desOrderResults['DesOrder']['luogo'] . '_';
                        $fileTitle .= 'Elenco articoli da monitorare ' . __('Supplier') . ' ' . $desOrderResults['DesOrder']['luogo'] . ' ';
                    } else
                    if ($doc_options == 'des-referent-to-supplier-details') {
                        $fileName .= 'elenco_articoli_' . __('Supplier') . '_' . $desOrderResults['DesOrder']['luogo'] . '_con_dettaglio_GAS_';
                        $fileTitle .= 'Elenco articoli ' . __('Supplier') . ' ' . $desOrderResults['DesOrder']['luogo'] . ' con dettaglio GAS ';
                    } else
                    if ($doc_options == 'des-referent-to-supplier-split-org') {
                        $fileName .= 'elenco_articoli_' . __('Supplier') . '_' . $desOrderResults['DesOrder']['luogo'] . '_divisi_per_GAS_';
                        $fileTitle .= 'Elenco articoli ' . __('Supplier') . ' ' . $desOrderResults['DesOrder']['luogo'] . ' divisi per GAS ';
                    } else
                    if ($doc_options == 'des-referent-to-supplier-split-org-monitoring') {
                        $fileName .= 'elenco_articoli_da_monitorate_' . __('Supplier') . '_' . $desOrderResults['DesOrder']['luogo'] . '_divisi_per_GAS_';
                        $fileTitle .= 'Elenco articoli da monitorare ' . __('Supplier') . ' ' . $desOrderResults['DesOrder']['luogo'] . ' divisi per GAS ';
                    }
                }
                break;
            case 'to-delivery-cassiere-users-all':
            case 'to-delivery-cassiere-users-compact-all':
            case 'to-lists-suppliers-cassiere':
            case 'to-lists-orders-cassiere':
            case 'to-list-users-delivery-cassiere':
                if (isset($params['delivery_id'])) {
                    $delivery_id = $params['delivery_id'];

                    App::import('Model', 'Delivery');
                    $Delivery = new Delivery;
                    $conditions = ['Delivery.organization_id' => (int) $user->organization['Organization']['id'], 'Delivery.id' => $delivery_id];
                    $resultsDelivery = $Delivery->find('first', ['conditions' => $conditions, 'recursive' => -1]);
                    if ($resultsDelivery['Delivery']['sys'] == 'N')
                        $delivery_data = $this->dateFormat($resultsDelivery['Delivery']['data']);
                    else
                        $delivery_data = Configure::read('DeliveryToDefinedLabel');

                    if ($doc_options == 'to-lists-orders-cassiere') {
                        $fileName = 'stampa_cassiere_' . strtolower(__('Orders')) . '_della_' . strtolower(__('Delivery')) . '_' . $delivery_data . '_';
                        $fileTitle = 'Stampa cassiere ' . strtolower(__('Orders')) . ' della ' . strtolower(__('Delivery')) . ' del ' . $delivery_data . ' ';
                    } else {
                        $fileName = 'stampa_cassiere_' . strtolower(__('Delivery')) . '_' . $delivery_data . '_';
                        $fileTitle = 'Stampa cassiere ' . strtolower(__('Delivery')) . ' del ' . $delivery_data . ' ';
                    }
                }
                break;
            case 'to-cassiere-pos':
                $year_pos = $params['year_pos'];
                $fileName = 'stampa_cassiere_importi_pos_anno_' . $year_pos . '_';
                $fileTitle = 'Stampa cassiere importi POS anno ' . $year_pos . ' ';
                break;
            case 'to_suppliers_organizations':
                $fileName = 'stampa_produttori_';
                $fileTitle = 'Stampa produttori ';
                break;
        }

        $fileName .= date("Ymd");

        $fileName = $this->fileNamePilisci($fileName);

        //echo '<br />fileName '.$fileName;
        //echo '<br />fileTitle '.$fileTitle;

        return array('fileName' => $fileName, 'fileTitle' => $fileTitle);
    }

    public function fileNamePilisci($fileName) {

        $caratteri_da_eliminare = array('.',
            ',',
            '!',
            '?',
            '/',
            '\\',
            "'",
            '\"');

        foreach ($caratteri_da_eliminare as $carattere)
            $fileName = str_replace($carattere, "", $fileName);

        $fileName = str_replace(" ", "_", $fileName);

        return $fileName;
    }

    /*
     *  calcola l'um di riferimento
     * richiamato da AppHelper e ExportDocModel
     */

    public function getArticlePrezzoUM($prezzo, $qta, $um, $um_riferimento, $debug = false) {

        if ($debug) {
            echo '<br />prezzo ' . $prezzo;
            echo '<br />qta ' . $qta;
            echo '<br />um ' . $um;
            echo '<br />um_riferimento ' . $um_riferimento;
        }

        if (empty($prezzo) || empty($qta) || $qta == '0,00' || $qta == '0.00')
            $prezzo_um_riferimento = '0,00';
        else {
            $prezzo_um_riferimento = ($prezzo / $qta);
            if ($debug)
                echo '<br />prezzo_um_riferimento (prezzo/qta) ' . $prezzo_um_riferimento;

            if ($um == 'GR' && $um_riferimento == 'HG')
                $prezzo_um_riferimento = ($prezzo_um_riferimento * 100);
            else
            if ($um == 'GR' && $um_riferimento == 'KG')
                $prezzo_um_riferimento = ($prezzo_um_riferimento * 1000);
            else
            if ($um == 'HG' && $um_riferimento == 'GR')
                $prezzo_um_riferimento = ($prezzo_um_riferimento / 100);
            else
            if ($um == 'HG' && $um_riferimento == 'KG')
                $prezzo_um_riferimento = ($prezzo_um_riferimento * 10);
            else
            if ($um == 'KG' && $um_riferimento == 'GR')
                $prezzo_um_riferimento = ($prezzo_um_riferimento / 1000);
            else
            if ($um == 'KG' && $um_riferimento == 'HG')
                $prezzo_um_riferimento = ($prezzo_um_riferimento / 100);
            else
            if ($um == 'ML' && $um_riferimento == 'DL')
                $prezzo_um_riferimento = ($prezzo_um_riferimento * 10);
            else
            if ($um == 'ML' && $um_riferimento == 'LT')
                $prezzo_um_riferimento = ($prezzo_um_riferimento * 1000);
            else
            if ($um == 'DL' && $um_riferimento == 'ML')
                $prezzo_um_riferimento = ($prezzo_um_riferimento / 100);
            else
            if ($um == 'DL' && $um_riferimento == 'LT')
                $prezzo_um_riferimento = ($prezzo_um_riferimento * 10);
            else
            if ($um == 'LT' && $um_riferimento == 'ML')
                $prezzo_um_riferimento = ($prezzo_um_riferimento / 1000);
            else
            if ($um == 'LT' && $um_riferimento == 'DL')
                $prezzo_um_riferimento = ($prezzo_um_riferimento / 100);

            $prezzo_um_riferimento = number_format($prezzo_um_riferimento, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));
        }

        if (!empty($um_riferimento)) {
            $um_riferimento = $this->traslateEnum($um_riferimento);

            $tmp = "";
            $tmp .= $prezzo_um_riferimento;
            $tmp .= ' &euro;';
            $tmp .= ' al ' . $um_riferimento;
        } else {
            $tmp = "";
            $tmp .= $prezzo_um_riferimento;
        }

        return $tmp;
    }

    public function traslateEnum($str) {

        $traslateEnum = Configure::read('traslateEnum');
        foreach ($traslateEnum as $key => $value) {
            //echo '<br />'.$key.' - '.$value;
            if ($str == $key)
                $str = $value;
        }

        return $str;
    }

	function formatSizeUnits($bytes)
	{
		if ($bytes >= 1073741824)
		{
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		}
		elseif ($bytes >= 1048576)
		{
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		}
		elseif ($bytes >= 1024)
		{
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		}
		elseif ($bytes > 1)
		{
			$bytes = $bytes . ' bytes';
		}
		elseif ($bytes == 1)
		{
			$bytes = $bytes . ' byte';
		}
		else
		{
			$bytes = '0 bytes';
		}
	
		return $bytes;
	}
	
    private function dateFormat($dateEN) {

        $dateIT = "";
        if (!empty($dateEN)) {
            list($aaaa, $mm, $gg) = explode("-", $dateEN);
            $dateIT = $gg . '-' . $mm . '-' . $aaaa;
        }

        return $dateIT;
    }
}
?>