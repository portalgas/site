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

$importo_completo_user = 0;
$importo_completo_all_orders = 0;
$tot_importo_pos = 0;
$importo_pos = 0;
$user_label = '';
$id_user_old = 0;

$html = '';
$html = $this->ExportDocs->delivery($resultDelivery['Delivery']);
$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$html = '';		
$html = '<table class="table table-hover" cellpadding="0" cellspacing="0">';	
$html .= '<thead><tr>';
if($trasportAndCost=='Y') {
	$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH30().'">'.__('qta').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Name').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('User').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('PrezzoUnita').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.__('TrasportAndCost').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Totale').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Supplier').'</th>';
}
else {
	$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH30().'">'.__('qta').'</th>';
	$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'">'.__('Name').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('User').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH90().'">'.__('PrezzoUnita').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.__('Importo').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Supplier').'</th>';
} // end if($trasportAndCost=='Y')
$html .= '</tr></thead><tbody>';


foreach($results as $deliveryResults) {
	$deliveries = $deliveryResults['Delivery'];

	foreach($deliveries as $numDelivery => $delivery) {

		if($delivery['totOrders']>0 /* && $delivery['totArticlesOrder']>0 */ ) {

			foreach($delivery['Order'] as $numOrder => $order) {

				foreach ($order['ExportRows'] as $rows) {
				
					$user_id_local = current(array_keys($rows));
					$rows = current(array_values($rows));

					foreach ($rows as $typeRow => $cols) {
							
						switch ($typeRow) {
							case 'TRGROUP':								

								if($user_id_local!=$id_user_old)
									$importo_completo_user = 0;						

								$user_label = substr($cols['LABEL'], strlen("Utente: "), strlen($cols['LABEL']));
							break;
							case 'TRSUBTOT':
								$html .= '<tr>';
								$html .= '<td style="text-align:center;"></td>'; // $cols['QTA']
								$html .= '<td colspan="3" style="text-align:right;"></td>';
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
								$html .= '<td></td>';
								$html .= '</tr>';
								
								$importo_completo_user += $cols['IMPORTO_COMPLETO']; 
								$importo_completo_all_orders += $cols['IMPORTO_COMPLETO'];

								$importo_pos = 0;
								if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
									if(isset($deliveries[$numDelivery]['SummaryDeliveriesPos']))
										$importo_pos = $deliveries[$numDelivery]['SummaryDeliveriesPos'][$user_id_local]['importo'];
								}
								
								$tot_importo_pos += $importo_pos;
								
							break;
							case 'TRTOT':
							break;								
							case 'TRDATA':
								
								$name = $cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']);
				 
								$html .= '<tr>';
								if($trasportAndCost=='Y') {
									$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH30().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$name.'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$user_label.'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$cols['PREZZO_E'].'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
									$html .= '<td width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH90()).'"  colspan="2" style="text-align:right;">&nbsp;</td>';
									$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$order['SuppliersOrganization']['name'].'</td>';
								}
								else {								
									$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH30().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
									$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'">'.$name.'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$user_label.'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH90().'">'.$cols['PREZZO_E'].'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$order['SuppliersOrganization']['name'].'</td>';
								} // end if($trasportAndCost=='Y')
								$html .= '</tr>';
							break;
						}
						
					} // end foreach ($rows as $typeRow => $cols)

					if($user_id_local>0) // quando $typeRow == TRTOT
						$id_user_old = $user_id_local;
				
				} // end foreach ($exportRows as $rows)
								
			}  // end foreach($delivery['Order'] as $numOrder => $order)
				
		}
		else {
			$html = $this->ExportDocs->suppliersOrganization($delivery['Order'][0]['SuppliersOrganization']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
			
			$html = '<div class="h4PdfNotFound">'.__('export_docs_not_found').'</div>';
			$output->writeHTMLCell(0,0,15,40, $css.$html, $border=0, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		}	
	}  // loop($results['Delivery'] as $numDelivery => $delivery) 
	
	/*
	 * totale utente
	*/
	if($importo_completo_user>0) {

		$importo_completo_user = number_format($importo_completo_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

		$html .= '<tr>';
		if($trasportAndCost=='Y') {
			$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH30().'"></th>';
			$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
			$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'"></th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;"></th>';
			$html .= '			<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;"></th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$importo_completo_user.'&nbsp;&euro;</th>';
		}
		else {
			$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH30().'"></th>';
			$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'"></th>';
			$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
			$html .= '			<th width="'.$output->getCELLWIDTH90().'"></th>';
			$html .= '			<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.$importo_completo_user.'&nbsp;&euro;</th>';
		} // end if($trasportAndCost=='Y')
		
		$html .= '			<th width="'.$output->getCELLWIDTH100().'">';
		/*
		 *  eventuale importo POS
		 */
		if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
			if($importo_pos>0) { 
				$importo_pos = number_format($importo_pos,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$html .=  sprintf(Configure::read('label_payment_pos'), $importo_pos);
			}
		}		
		$html .= '</th>';
		$html .= '</tr>';
	} // end if($importo_completo_user>0)
	
	
} // loop users

/*
 * totale orders
*/
if($importo_completo_all_orders>0) {

	$importo_completo_all_orders = number_format($importo_completo_all_orders,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

	$html .= '<tr>';
	if($trasportAndCost=='Y') {
		$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH30().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH70().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$importo_completo_all_orders.'&nbsp;&euro;</th>';
	}
	else {
		$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH30().'"></th>';
		$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH90().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.$importo_completo_all_orders.'&nbsp;&euro;</th>';
	} // end if($trasportAndCost=='Y')
	
	$html .= '			<th width="'.$output->getCELLWIDTH100().'">';
	/*
	 *  eventuale importo POS
	 */
	if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
		if($tot_importo_pos>0) { 
			$tot_importo_pos = number_format($tot_importo_pos,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$html .=  sprintf(Configure::read('label_payment_pos'), $tot_importo_pos);
		}
	}		
	$html .= '</th>';
	$html .= '</tr>';
} // end if($importo_completo_all_orders>0)


$html .= '</tbody></table>';
$html .= $output->getLegenda();
$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');



// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>