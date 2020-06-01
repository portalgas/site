<?php

if ($this->layout == 'pdf') {
    App::import('Vendor', 'xtcpdf');

    $output = new XTCPDF($organization, PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $output->headerText = $fileData['fileTitle'];

    // add a page
    $output->AddPage();
    $css = $output->getCss();
} else
if ($this->layout == 'ajax') {   // mai utilizzato
    App::import('Vendor', 'xtcpreview');
    $output = new XTCPREVIEW();
    $css = $output->getCss();
}


if (isset($results['Delivery']))
    foreach ($results['Delivery'] as $numDelivery => $result['Delivery']) {


        /*
         * D I S P E N S A
         */
		$tot_qta_storeroom = 0;
		$tot_importo_storeroom = 0;		 
        $i = 0;
        if (isset($storeroomResults['Delivery'][$numDelivery])) {

            $delivery = $storeroomResults['Delivery'][$numDelivery];

            if ($delivery['totStorerooms']) {

                $html = '';
                $html .= '<div class="h1Pdf">Dispensa</div>';
                $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');



                $html = '';
                $html .= '	<table cellpadding="0" cellspacing="0">';
                $html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
                $html .= '		<tr>';
               // $html .= '			<th width="' . $output->getCELLWIDTH20() . '">' . __('N') . '</th>';
                $html .= '			<th width="' . $output->getCELLWIDTH30() . '">' . __('Bio') . '</th>';
                $html .= '			<th width="' . ($output->getCELLWIDTH200() + $output->getCELLWIDTH30()) . '">' . __('Name') . '</th>';
                $html .= '			<th width="' . $output->getCELLWIDTH70() . '">' . __('Conf') . '</th>';
                $html .= '			<th width="' . $output->getCELLWIDTH70() . '">' . __('PrezzoUnita') . '</th>';
                $html .= '			<th width="' . ($output->getCELLWIDTH70()+$output->getCELLWIDTH20()) . '">' . __('PrezzoUM') . '</th>';
                $html .= '			<th width="' . $output->getCELLWIDTH70() . '">' . __('Acquistato') . '</th>';
                $html .= '			<th width="' . $output->getCELLWIDTH70() . '" style="text-align:right;">' . __('Importo') . '</th>';
                $html .= '	</tr>';
                $html .= '	</thead><tbody>';

                $supplier_organization_id_old = 0;
                foreach ($delivery['Storeroom'] as $numStoreroom => $storeroom) {

                    if ($storeroom['SuppliersOrganization']['id'] != $supplier_organization_id_old) {
                        $html .= '<tr style="height:30px;">';
                        $html .= '<td colspan="7" class="trGroup">' . __('Supplier') . ': ' . $storeroom['SuppliersOrganization']['name'];
                        if (!empty($storeroom['SuppliersOrganization']['descrizione']))
                            $html .= '/' . $storeroom['SuppliersOrganization']['descrizione'];
                        $html .= '</td>';
                        $html .= '</tr>';
                    }


                    $html .= '<tr>';
                   // $html .= '	<td width="' . $output->getCELLWIDTH20() . '">' . ($i + 1) . '</td>';
                    $html .= '	<td width="' . $output->getCELLWIDTH30() . '">';
                    if ($storeroom['Article']['bio'] == 'Y')
                        $html .= 'Bio';
                    $html .= '</td>';
                    $html .= '<td width="' . ($output->getCELLWIDTH200() + $output->getCELLWIDTH30()) . '">' . h($storeroom['name']) . '</td>';
                    $html .= '<td width="' . $output->getCELLWIDTH70() . '">' . $this->App->getArticleConf($storeroom['Article']['qta'], $storeroom['Article']['um']) . '</td>';
                    $html .= '<td width="' . $output->getCELLWIDTH70() . '">' . $storeroom['prezzo_e'] . '</td>';
                    $html .= '<td width="' . ($output->getCELLWIDTH70()+$output->getCELLWIDTH20()) . '">' . $this->App->getArticlePrezzoUM($storeroom['prezzo'], $storeroom['Article']['qta'], $storeroom['Article']['um'], $storeroom['Article']['um_riferimento']) . '</td>';
                    $html .= '<td style="text-align:center;" width="' . $output->getCELLWIDTH70() . '">' . $storeroom['qta'] . '</td>';
                    $html .= '<td width="' . $output->getCELLWIDTH70() . '" style="text-align:right;">' . $this->App->getArticleImporto($storeroom['prezzo'], $storeroom['qta']) . '</td>';
                    $html .= '</tr>';

					$tot_qta_storeroom = ($tot_qta_storeroom + $storeroom['qta']);
					$tot_importo_storeroom = ($tot_importo_storeroom + ($storeroom['prezzo'] * $storeroom['qta']));

                    $supplier_organization_id_old = $storeroom['SuppliersOrganization']['id'];
                }
                $html .= '</tbody>';
                
                $tot_importo_storeroom = number_format($tot_importo_storeroom,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
                
                
                $html .= '<tr>';
                $html .= '<tfooter>';
                $html .= '	<th></th>';
                $html .= '	<th></th>';
                $html .= '	<th></th>';
                $html .= '	<th></th>';
                $html .= '	<th></th>';
                $html .= '	<th style="text-align:center;">'.$tot_qta_storeroom.'</th>';
                $html .= '	<th style="text-align:right;">'.$tot_importo_storeroom.'&nbsp;&euro;</th>';
                $html .= '</tr>';
                $html .= '</tfooter>';
                                
                $html .= '</table>';
                $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
            } // end if($delivery['totStorerooms'])			
        } // end if(isset($storeroomResults['Delivery'][$numDelivery])) 
			
		
		$html = '';
		$html .= '<br />';
        $html .= $this->ExportDocs->delivery($result['Delivery']);
        $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

        if ($result['Delivery']['totOrders'] > 0 && $result['Delivery']['totArticlesOrder'] > 0) {

            $tot_importo = 0;  /* il totale di tutta la consegna */
            $tot_importo_cost_less = 0;
            $tot_importo_trasport = 0;
            $tot_importo_cost_more = 0;

            foreach ($result['Delivery']['Order'] as $numOrder => $order) {

                if (isset($order['ArticlesOrder'])) { // cosi' escludo gli ordini senza acquisti
                    
                    if(isset($user->organization['Organization']['hasCashFilterSupplier']) && $user->organization['Organization']['hasCashFilterSupplier']=='Y') 
                        $html = $this->ExportDocs->suppliersOrganizationPrepaidShort($order['SuppliersOrganization']);
                    else
                        $html = $this->ExportDocs->suppliersOrganizationShort($order['SuppliersOrganization']);

                    $output->writeHTML($css . $html, $ln = false, $fill = false, $reseth = true, $cell = true, $align = '');

                    $html = '';
                    $html .= '	<table cellpadding="0" cellspacing="0">';
                    $html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
                    $html .= '		<tr>';
                    //$html .= '			<th width="' . $output->getCELLWIDTH20() . '">' . __('N') . '</th>';
                    $html .= '			<th width="' . $output->getCELLWIDTH30() . '">' . __('Bio') . '</th>';
                    $html .= '			<th width="' . ($output->getCELLWIDTH300() + $output->getCELLWIDTH20()) . '">' . __('Name') . '</th>';
                    $html .= '			<th width="' . $output->getCELLWIDTH50() . '" style="text-align:center;">' . __('qta') . '</th>';
                    $html .= '			<th width="' . $output->getCELLWIDTH70() . '" style="text-align:center;">&nbsp;' . __('PrezzoUnita') . '</th>';
                    $html .= '			<th width="' . ($output->getCELLWIDTH70()+$output->getCELLWIDTH20()) . '">' . __('Prezzo/UM') . '</th>';
                    $html .= '			<th width="' . $output->getCELLWIDTH70() . '" style="text-align:right;">' . __('Importo') . '</th>';
                    $html .= '	</tr>';
                    $html .= '	</thead><tbody>';



                    $tot_qta = 0;
                    $tot_importo_sub = 0;  /* il totale di un ordine */
                    foreach ($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {

                        $name = $order['ArticlesOrder'][$numArticlesOrder]['name'] . ' ' . $this->App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']);

                        /*
                         * gestione qta e importi
                         * */
                        if ($order['Cart'][$numArticlesOrder]['qta_forzato'] > 0) {
                            $qta = $order['Cart'][$numArticlesOrder]['qta_forzato'];
                            $qta_modificata = true;
                        } else {
                            $qta = $order['Cart'][$numArticlesOrder]['qta'];
                            $qta_modificata = false;
                        }
                        $importo_modificato = false;
                        if ($order['Cart'][$numArticlesOrder]['importo_forzato'] == 0) {
                            if ($order['Cart'][$numArticlesOrder]['qta_forzato'] > 0)
                                $importo = ($order['Cart'][$numArticlesOrder]['qta_forzato'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
                            else {
                                $importo = ($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
                            }
                        } else {
                            $importo = $order['Cart'][$numArticlesOrder]['importo_forzato'];
                            $importo_modificato = true;
                        }

                        $tot_qta += $qta;
                        $tot_importo_sub += $importo;

                        $importo = number_format($importo, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));

                        $html .= '<tr>';
                       // $html .= '	<td width="' . $output->getCELLWIDTH20() . '">' . ($numArticlesOrder + 1) . '</td>';
                        $html .= '	<td width="' . $output->getCELLWIDTH30() . '">';
                        if ($order['Article'][$numArticlesOrder]['bio'] == 'Y')
                            $html .= 'Bio';
                        $html .= '</td>';
                        $html .= '<td width="' . ($output->getCELLWIDTH300() + $output->getCELLWIDTH20()) . '">' . $name . '</td>';
                        $html .= '<td width="' . $output->getCELLWIDTH50() . '" style="text-align:center;">' . $qta . $this->App->traslateQtaImportoModificati($qta_modificata) . '</td>';
                        $html .= '<td width="' . $output->getCELLWIDTH70() . '" style="text-align:center;">' . $order['ArticlesOrder'][$numArticlesOrder]['prezzo_e'] . '</td>';
                        $html .= '<td width="' . ($output->getCELLWIDTH70()+$output->getCELLWIDTH20()) . '">' . $this->App->getArticlePrezzoUM($order['ArticlesOrder'][$numArticlesOrder]['prezzo'], $order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um'], $order['Article'][$numArticlesOrder]['um_riferimento']) . '</td>';
                        $html .= '<td width="' . $output->getCELLWIDTH70() . '" style="text-align:right;">';
                        $html .= $importo . '&nbsp;&euro;' . $this->App->traslateQtaImportoModificati($importo_modificato);
                        $html .= '</td>';
                        $html .= '</tr>';
                    }  // end ciclo ArticlesOrder

                    $tot_importo += $tot_importo_sub;

                    $html .= '<tr>';
                    $html .= '	<th></th>';
                    $html .= '	<th colspan="1" style="text-align:right;">'.__('qta_tot').'</th>';
                    $html .= '	<th style="text-align:center;">&nbsp;' . $tot_qta . '</th>';
                    $html .= '	<th colspan="3" style="text-align:right;">Importo totale&nbsp;' . number_format($tot_importo_sub, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                    $html .= '</tr>';

                    /*
                     * only Order.state_code = PROCESSED-ON-DELIVERY (in carico al cassiere) 
                     * ctrl se ci sono variazioni in
                     *
                     * SummaryOrder
                     * SummaryOrderTrasport
                     * SummaryOrderCostMore
                     * SummaryOrderCostLess
                     *
                     * $resultsWithModifies[order_id][SummaryOrder][0][SummaryOrder][importo] e' la somma di SummaryOrderTrasport + SummaryOrderCostMore + SummaryOrderCostLess
                     */
                    if (array_key_exists($order['Order']['id'], $resultsWithModifies)) {

                        $resultsWithModifiesOrder = $resultsWithModifies[$order['Order']['id']];
                        /*
                          echo "<pre>";
                          print_r($resultsWithModifiesOrder);
                          echo "</pre>";
                         */
                        if (isset($resultsWithModifiesOrder['SummaryOrderTrasport'][0])) {

                            $importo_trasport = $resultsWithModifiesOrder['SummaryOrderTrasport'][0]['SummaryOrderTrasport']['importo_trasport'];
                            // echo '<br />importo_trasport '.$importo_trasport;

                            if ($importo_trasport > 0) {
                                $html .= '<tr>';
                                $html .= '	<th></th>';
                                $html .= '	<th colspan="1" style="text-align:right;"></th>';
                                $html .= '	<th style="text-align:center;"></th>';
                                $html .= '	<th colspan="3" style="text-align:right;">Trasporto&nbsp;&nbsp;' . number_format($importo_trasport, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                                $html .= '</tr>';
                            }

                            $tot_importo_trasport += $importo_trasport;
                            // echo '<br />tot_importo_trasport '.$tot_importo_trasport;
                        }
                        if (isset($resultsWithModifiesOrder['SummaryOrderCostMore'][0])) {

                            $importo_cost_more = $resultsWithModifiesOrder['SummaryOrderCostMore'][0]['SummaryOrderCostMore']['importo_cost_more'];

                            if ($importo_cost_more > 0) {
                                $html .= '<tr>';
                                $html .= '	<th></th>';
                                $html .= '	<th colspan="1" style="text-align:right;"></th>';
                                $html .= '	<th style="text-align:center;"></th>';
                                $html .= '	<th colspan="3" style="text-align:right;">Costo aggiuntivo&nbsp;&nbsp;' . number_format($importo_cost_more, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                                $html .= '</tr>';
                            }

                            $tot_importo_cost_more += $importo_cost_more;
                        }
                        if (isset($resultsWithModifiesOrder['SummaryOrderCostLess'][0])) {

                            $importo_cost_less = $resultsWithModifiesOrder['SummaryOrderCostLess'][0]['SummaryOrderCostLess']['importo_cost_less'];

                            if ($importo_cost_less != 0) {
                                $html .= '<tr>';
                                $html .= '	<th></th>';
                                $html .= '	<th colspan="1" style="text-align:right;"></th>';
                                $html .= '	<th style="text-align:center;"></th>';
                                $html .= '	<th colspan="3" style="text-align:right;">Sconto&nbsp;&nbsp;' . number_format($importo_cost_less, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                                $html .= '</tr>';
                            }

                            $tot_importo_cost_less += $importo_cost_less;
                        }

                        if (isset($resultsWithModifiesOrder['SummaryOrder'][0])) {

                            $importo = $resultsWithModifiesOrder['SummaryOrder'][0]['SummaryOrder']['importo'];
                            // echo '<br />importo '.$importo;

                            if ($importo > 0) {
                                $html .= '<tr>';
                                $html .= '	<th></th>';
                                $html .= '	<th colspan="5" style="text-align:right;">';

                                if ($importo_trasport == 0 && $importo_cost_less == 0 && $importo_cost_more == 0) {
                                    $html .= 'Totale dell\'ordine&nbsp;modificato&nbsp;dal&nbsp;referente&nbsp;';
                                    /*
                                     * l'importo dell'ordine e' stato modificato con l'aggregazione dei dai, tolgo il vecchio e agginugo il nuovo
                                     */
                                    $tot_importo = ($tot_importo - $tot_importo_sub + $importo);
                                } else
                                    $html .= 'Totale dell\'ordine&nbsp;&nbsp;';
                                $html .= number_format($importo, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;</th>';
                                $html .= '</tr>';
                            }
                        }
                    }

                    $html .= '</tbody></table>';
                    $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');

                    if (isset($order['SuppliersOrganizationsReferent'])) {
                        $html = '';
                        $html = $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
                        $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
                    }
                }  // end if(isset($order['ArticlesOrder']))  	
            }  // end foreach($result['Delivery']['Order'] as $numOrder => $order)


            /*
             * totale importo della consegna
             */
            $tot_importo = ($tot_importo_storeroom + $tot_importo + $tot_importo_trasport + ($tot_importo_cost_less) + $tot_importo_cost_more);

            if ($user->organization['Template']['payToDelivery'] == 'POST')
                $msg = sprintf(__('TotaleConfirmTesoriere'), number_format($tot_importo, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;');
            if ($user->organization['Template']['payToDelivery'] == 'ON' || $user->organization['Template']['payToDelivery'] == 'ON-POST')
                $msg = sprintf(__('TotaleConfirmCassiere'), number_format($tot_importo, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia')) . '&nbsp;&euro;');

            $html = '';
            $html .= '	<table cellpadding="0" cellspacing="0">';
            $html .= '	<tbody>';
            $html .= '<tr>';
            $html .= '	<td colspan="6"></td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '	<th colspan="6" style="text-align:right;">';
            $html .= $msg;
            $html .= '</th>';
            $html .= '</tr>';

            $html .= '</tbody></table>';
            $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
        }
        else {

            if ($storeroomResults['Delivery'][$numDelivery]['totStorerooms'] == 0) {
                $html = '<div class="h4PdfNotFound">' . __('export_docs_not_found') . '</div>';
                $output->writeHTMLCell(0, 0, 15, 40, $css . $html, $border = 0, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
            }
        }

    } // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 

$html = '';
$html = $output->getLegenda();
$output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');



/*
 * D I S T A N C E
 */
if (!empty($arrayDistances)) {
    $html = '';
    $html .= '<br /><div class="h2Pdf">Quanta strada hanno fatto i tuoi acquisti?</div>';
    $tot_distance = 0;
    foreach ($arrayDistances as $arrayDistance) {

        $percentuale = $arrayDistance['percentuale'];
        if ($percentuale == 0)
            $percentuale = 1;

        $html .= '	<table cellpadding="0" cellspacing="0">';
        $html .= '		<tr>';
        $html .= '			<td style="border-bottom:0px solid #fff;">' . $arrayDistance['supplierName'] . ' da ' . $arrayDistance['supplierLocalita'] . ' ha percorso ' . $arrayDistance['distance'] . ' Km</td>';
        $html .= '		</tr>';
        $html .= '		<tr>';
        $html .= '			<td class="progressBar" width="' . $percentuale . '%">&nbsp;</td>';
        $html .= '		</tr>';
        $html .= '</table>';
        $tot_distance += $arrayDistance['distance'];
    }

    $tot_distance = number_format($tot_distance, 2, Configure::read('separatoreDecimali'), Configure::read('separatoreMigliaia'));

    $html .= '<div class="h3Pdf">per un totale di ' . $tot_distance . ' Km</div>';
    $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
} // if(!empty($arrayDistances))
// reset pointer to the last page
$output->lastPage();

if ($this->layout == 'pdf')
    ob_end_clean();
echo $output->Output($fileData['fileName'] . '.pdf', 'D');
exit;
?>