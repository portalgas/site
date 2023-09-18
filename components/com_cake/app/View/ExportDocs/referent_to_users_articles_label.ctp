<?php 
$this->App->d($results);
/*
 * T O - U S E R S - L A B E L 
 *   Documento con elenco diviso per utente in formato etichetta (per la consegna) 
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



$totale = 0;
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	$html = $this->ExportDocs->delivery($user, $result['Delivery']);
	$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {

			$html = $this->ExportDocs->suppliersOrganization($order['SuppliersOrganization']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');



			
			$html = '';
			$html_header = '';
							
			foreach ($order['ExportRows'] as $rows) {
				
				$user_id = current(array_keys($rows));
				$rows = current(array_values($rows));

				foreach ($rows as $typeRow => $cols) {
						
					switch ($typeRow) {
						case 'TRGROUP':
							if($trasportAndCost=='Y') 
								$colspan = '6'; 
							else
								$colspan = '4';
							
																						   
							$html_header .= '	<br /><br />';
							$html_header .= '	<table class="table table-hover" cellpadding="0" cellspacing="0">';
							$html_header .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
							$html_header .= '<tr>';
							$html_header .= '<td colspan="'.$colspan.'" style="text-align:center;">';
							$html_header .= '<h3>';

							if($user_avatar=='Y')
								$html_header .=  ' '.$this->App->drawUserAvatar($user, $cols['LABEL_ID']).' ';
							
							$html_header .= $cols['LABEL'];
							
							if($user_phone=='Y')
								$html_header .=  ' '.$cols['LABEL_PHONE'];
							if($user_email=='Y')
								$html_header .=  ' '.$cols['LABEL_EMAIL'];
							if($user_address=='Y')
								$html_header .=  ' '.$cols['LABEL_ADDRESS'];
								
							/*
							 * estraggo il totale di un utente 
							 */
							foreach ($order['ExportRows'] as $rows2) {
								$user_id2 = current(array_keys($rows2));
								$rows2 = current(array_values($rows2));
								foreach ($rows2 as $typeRow2 => $cols2) 
									if($typeRow2 == 'TRSUBTOT' && $user_id2 == $user_id) {
										if($trasportAndCost=='Y') {
											$totale += $cols2['IMPORTO_COMPLETO'];
											$html_header .= ' - Totale: '.$cols2['IMPORTO_COMPLETO_E'];
										}
										else {
											$totale += $cols2['IMPORTO'];
											$html_header .= ' - Totale: '.$cols2['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols2['ISIMPORTOMOD']);
										}
									}
							}
							$html_header .= '</h3></td>';
							$html_header .= '</tr>';
								
							$html_header .= '		<tr>';
							if($trasportAndCost=='Y') {
								$html_header .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.__('qta').'</th>';
								$html_header .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH90()).'">'.__('Name').'</th>';
								$html_header .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('PrezzoUnita').'</th>';
								$html_header .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';
								$html_header .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('TrasportAndCost').'</th>';
								$html_header .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Totale').'</th>';
							}
							else {
								$html_header .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.__('qta').'</th>';
								$html_header .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH100()+$output->getCELLWIDTH90()).'">'.__('Name').'</th>';
								$html_header .= '			<th width="'.$output->getCELLWIDTH90().'">'.__('PrezzoUnita').'</th>';
								$html_header .= '			<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.__('Importo').'</th>';
							} // end if($trasportAndCost=='Y')
							$html_header .= '		</tr>';
							$html_header .= '	</thead><tbody>';
															
						break;
						case 'TRSUBTOT':
							/*
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
							*/
							$html .= '</tbody></table>';
							
							$html_header = '';
						break;
						case 'TRTOT':
						break;								
						case 'TRDATA':
							
							$html .= $html_header.'</table>';
							
							if($codice=='Y' && $user->organization['Organization']['hasFieldArticleCodice']=='Y')		
								$name = $cols['CODICE'].' '.$cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']);
							else
								$name = $cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']);
							
							$html .= '<tr>';
							if($trasportAndCost=='Y') {
								$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
								$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH90()).'" ';
								if($cols['DELETE_TO_REFERENT']=='Y') $html .= ' style="text-decoration: line-through;"';
								$html .= '>'.$name.'</td>';
								$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$cols['PREZZO_E'].'</td>';
								$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">';
								if($cols['DELETE_TO_REFERENT']=='Y') 
									$html .= '0,00&nbsp;&euro;';
								else
									$html .= $cols['IMPORTO_E'];
								$html .= $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']);
								$html .= '</td>';
								$html .= '<td width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'"  colspan="2" style="text-align:right;">&nbsp;</td>';
							}
							else {								
								$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
								$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH100()+$output->getCELLWIDTH90()).'" ';
								if($cols['DELETE_TO_REFERENT']=='Y') $html .= ' style="text-decoration: line-through;"';
								$html .= '>'.$name.'</td>';
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
			
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

			$html = '';
			$html = $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
	
		/*
		 *  totale
		 */		
		$html = '';
		$html .= '	<br />';
		$html .= '	<table class="table table-hover" cellpadding="0" cellspacing="0">';
		$html .= '	<tbody>';
		$html .= '<tr>';
		$html .= '<td colspan="'.$colspan.'" style="text-align:center;">';
		$html .= '<h3>Totale dell\'ordine '.number_format($totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</h3>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</tbody></table>';		
		$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
	
					
		$html = '';
		$html = $output->getLegenda();
		$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		
	}
	else {
		$html = $this->ExportDocs->suppliersOrganization($result['Delivery']['Order'][0]['SuppliersOrganization']);
		$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		
		$html = '<div class="h4PdfNotFound">'.__('export_docs_not_found').'</div>';
		$output->writeHTMLCell(0,0,15,40, $css.$html, $border=0, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}	
}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 


// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>