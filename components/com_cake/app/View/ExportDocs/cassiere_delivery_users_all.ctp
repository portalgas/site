<?php 
/*
 * C A S S I E R E - T O - U S E R S 
 *   Documento della consegna completa diviso per utente (per pagamento dell'utente) 
 */


if($this->layout=='pdf') {
	App::import('Vendor','xtcpdf');
	
	$output = new XTCPDF($organization, PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$output->headerText = $fileData['fileTitle'];
			
	// add a page
	$output->AddPage();
	$css = $output->getCss();
}
else 
if($this->layout=='ajax') {
	App::import('Vendor','xtcpreview');
	$output = new XTCPREVIEW();
	$css = $output->getCss();
}

$importo_completo_all_orders = 0;
$importo_pos = 0;
$user_label = '';
$print_delivery = false;


foreach($results as $deliveryResults) {
	$deliveries = $deliveryResults['Delivery'];

	foreach($deliveries as $numDelivery => $delivery) {

		if(!$print_delivery) {
			$html = '';
			$html = $this->ExportDocs->delivery($delivery);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
			$print_delivery = true;
		}
		
		if($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0) {

			foreach($delivery['Order'] as $numOrder => $order) {

				if(!empty($order['ExportRows'])) { // lo user non ha effettuato acquisti sull'ordine legato alla consegna
					$html = '<br />';
					$html .= $this->ExportDocs->suppliersOrganization($order['SuppliersOrganization']);
					$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
				}


				
				$html = '';	
				$html = '	<table class="table table-hover" cellpadding="0" cellspacing="0">';						
				foreach ($order['ExportRows'] as $rows) {
					
					$user_id_local = current(array_keys($rows));
					$rows = current(array_values($rows));

					foreach ($rows as $typeRow => $cols) {
							
						switch ($typeRow) {
							case 'TRGROUP':
							
								$user_label = $cols['LABEL'];
							
								if($trasportAndCost=='Y') 
									$colspan = '6'; 
								else
									$colspan = '4';
								
								$html .= '<tr>';
								$html .= '<td colspan="'.$colspan.'" style="text-align:center;">';
								$html .= '<h3>';

								if($user_avatar=='Y')
									$html .=  ' '.$this->App->drawUserAvatar($user, $cols['LABEL_ID']).' ';
								
								$html .= $cols['LABEL'];
								
								if($user_phone=='Y')
									$html .=  ' '.$cols['LABEL_PHONE'];
								if($user_email=='Y')
									$html .=  ' '.$cols['LABEL_EMAIL'];
								if($user_address=='Y')
									$html .=  ' '.$cols['LABEL_ADDRESS'];
									
								/*
								 * estraggo il totale di un utente 
								 */
								foreach ($order['ExportRows'] as $rows2) {
									$user_id2 = current(array_keys($rows2));
									$rows2 = current(array_values($rows2));
									foreach ($rows2 as $typeRow2 => $cols2) 
										if($typeRow2 == 'TRSUBTOT' && $user_id2 == $user_id_local) 
											if($trasportAndCost=='Y')  
												$html .= ' - Totale: '.$cols2['IMPORTO_COMPLETO_E'];
											else
												$html .= ' - Totale: '.$cols2['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols2['ISIMPORTOMOD']);
								}
								$html .= '</h3></td>';
								$html .= '</tr>';
									
								$html .= '		<tr>';
								if($trasportAndCost=='Y') {
									$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.__('qta').'</th>';
									$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH90()).'">'.__('Name').'</th>';
									$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('PrezzoUnita').'</th>';
									$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';
									$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('TrasportAndCost').'</th>';
									$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Totale').'</th>';
								}
								else {
									$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.__('qta').'</th>';
									$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH100()+$output->getCELLWIDTH90()).'">'.__('Name').'</th>';
									$html .= '			<th width="'.$output->getCELLWIDTH90().'">'.__('PrezzoUnita').'</th>';
									$html .= '			<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.__('Importo').'</th>';
								} // end if($trasportAndCost=='Y')
								$html .= '		</tr>';
																
							break;
							case 'TRSUBTOT':
								$html .= '<tr>';
								$html .= '<td style="text-align:center;">'.$cols['QTA'].'</td>';
								$html .= '<td colspan="2" style="text-align:right;">Totale&nbsp;dell\'utente&nbsp;</td>';
								$html .= '<td style="text-align:right;">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
								if($trasportAndCost=='Y') {
									$html .= '<td style="text-align:right;">';
									if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') $html .= __('TrasportShort').' '.$cols['IMPORTO_TRASPORTO_E'].'<br />';
									if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') $html .= __('CostMoreShort').' '.$cols['IMPORTO_COST_MORE_E'].'<br />';
									if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00') $html .= __('CostLessShort').' '.$cols['IMPORTO_COST_LESS_E'];
									$html .= '</td>';

									$html .= '<td style="text-align:right;">';
									$html .= $cols['IMPORTO_COMPLETO_E'];
									$html .= '</td>';
								}
								$html .= '</tr>';
								
								$importo_completo_all_orders += $cols['IMPORTO_COMPLETO'];
								
								$importo_pos = 0;
								if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
									if(isset($deliveries[$numDelivery]['SummaryDeliveriesPos']))
										$importo_pos = $deliveries[$numDelivery]['SummaryDeliveriesPos'][$user_id_local]['importo'];
								}
																
							break;
							case 'TRTOT':
							break;								
							case 'TRDATA':
								
								$name = $cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']);
								
								$html .= '<tr>';
								if($trasportAndCost=='Y') {
									$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
									$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH90()).'">'.$name.'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$cols['PREZZO_E'].'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
									$html .= '<td width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'"  colspan="2" style="text-align:right;">&nbsp;</td>';
								}
								else {								
									$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
									$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH100()+$output->getCELLWIDTH90()).'">'.$name.'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH90().'">'.$cols['PREZZO_E'].'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
								} // end if($trasportAndCost=='Y')
								$html .= '</tr>';
							break;
							case 'TRDATABIS':
								$html .= '<tr>';
								$html .= '<td colspan="'.$colspan.'">NOTA: '.$cols['NOTA'].'</td>';
								$html .= '</tr>';
							break;
						}
					} // end foreach ($rows as $typeRow => $cols)
								
				} // end foreach ($exportRows as $rows)
				
				$html .= '</tbody></table>';
						
				$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

				if(!empty($order['ExportRows'])) { // lo user non ha effettuato acquisti sull'ordine legato alla consegna
					$html = '';
					$html = $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
					$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
				}
				
			}  // end foreach($delivery['Order'] as $numOrder => $order)
							
		}
		else {
			$html = $this->ExportDocs->suppliersOrganization($delivery['Order'][0]['SuppliersOrganization']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
			
			$html = '<div class="h4PdfNotFound">'.__('export_docs_not_found').'</div>';
			$output->writeHTMLCell(0,0,15,40, $css.$html, $border=0, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		}	
		
		
		
        /*
         * D I S P E N S A
         */
        $i = 0;
        if (isset($storeroomResults['Delivery'][$numDelivery])) {

            $delivery = $storeroomResults['Delivery'][$numDelivery];

            if ($delivery['totStorerooms']) {

                $html = '';
                $html .= '<br /><div class="h1Pdf">'.__('Storeroom').'</div>';
                $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');



                $html = '';
                $html .= '	<table class="table table-hover" cellpadding="0" cellspacing="0">';
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

				$tot_qta = 0;
				$tot_importo = 0;
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

					$tot_qta = ($tot_qta + $storeroom['qta']);
					$tot_importo = ($tot_importo + ($storeroom['prezzo'] * $storeroom['qta']));
					$importo_completo_all_orders += $tot_importo;
					
                    $supplier_organization_id_old = $storeroom['SuppliersOrganization']['id'];
                }
                $html .= '</tbody>';
                
                $tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
                
                
                $html .= '<tr>';
                $html .= '<tfooter>';
                $html .= '	<th></th>';
                $html .= '	<th></th>';
                $html .= '	<th></th>';
                $html .= '	<th></th>';
                $html .= '	<th></th>';
                $html .= '	<th style="text-align:center;">'.$tot_qta.'</th>';
                $html .= '	<th style="text-align:right;">'.$tot_importo.'&nbsp;&euro;</th>';
                $html .= '</tr>';
                $html .= '</tfooter>';
                                
                $html .= '</table>';
                $output->writeHTML($css . $html, $ln = true, $fill = false, $reseth = true, $cell = true, $align = '');
            } // end if($delivery['totStorerooms'])			
        } // end if(isset($storeroomResults['Delivery'][$numDelivery])) 



		
	}  // loop($results['Delivery'] as $numDelivery => $delivery) 
	
	/*
	 * totale utente
	*/
	if($importo_completo_all_orders>0) {

		$importo_completo_all_orders = number_format($importo_completo_all_orders,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

		$html = '';
		$html .= '<div style="text-align:center;background-color:#c3d2e5;padding: 5px 0;" class="h3Pdf">';
		$html .= __('Importo').' per l\'utente '.$user_label.':';
		$html .= '&nbsp;'.$importo_completo_all_orders.'&nbsp;&euro;';
		
		/*
		 *  eventuale importo POS
		 */
		if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
			if($importo_pos>0) {
				$importo_pos = number_format($importo_pos,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$html .= ' '.sprintf(Configure::read('label_payment_pos'), $importo_pos);
			}
		}
				
		$html .= '</div>';
		$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		
		$importo_completo_all_orders = 0;
	}

	
} // loop users


$html = '';
$html = $output->getLegenda();
$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');



// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>