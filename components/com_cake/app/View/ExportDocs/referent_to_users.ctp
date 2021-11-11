<?php 
/*
 * T O - U S E R S 
 *   Documento con elenco diviso per utente (per pagamento dell'utente) 
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





foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	$html = $this->ExportDocs->delivery($result['Delivery']);
	$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {
			
			$html = $this->ExportDocs->suppliersOrganization($order['SuppliersOrganization']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
			
			$html = '';
			$html .= '	<table cellpadding="0" cellspacing="0">';
			$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
			$html .= '		<tr>';
				
			if($trasportAndCost=='Y') {
				$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()).'" colspan="2">'.__('Name').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH60().'">'.__('PrezzoUnita').'</th>';
				if($note=='N') $html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('Prezzo/UM').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('qta').'</th>';
				if($note=='Y') $html .= '			<th width="'.$output->getCELLWIDTH70().'">Nota</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';				
				$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('TrasportAndCost').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Totale').'</th>';
			} 
			else {
				$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()+$output->getCELLWIDTH70()).'" colspan="2">'.__('Name').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('PrezzoUnita').'</th>';
				if($note=='N') $html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Prezzo/UM').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('qta').'</th>';
				if($note=='Y') $html .= '			<th width="'.$output->getCELLWIDTH100().'">Nota</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';				
			} // end if($trasportAndCost=='Y')
			$html .= '		</tr>';				
			$html .= '	</thead><tbody>';


			foreach ($order['ExportRows'] as $rows) {
			
				$user_id = current(array_keys($rows));
				$rows = current(array_values($rows));
				
				$html .= '<tr>';
			
				foreach ($rows as $typeRow => $cols) {
			
					switch ($typeRow) {
						case 'TRGROUP':
							if($trasportAndCost=='Y') 
								$colspan = '8'; 
							else
								$colspan = '6';
							$html .= '<td colspan="'.$colspan.'" >';

							if($user_avatar=='Y')
								$html .=  ' '.$this->App->drawUserAvatar($user, $cols['LABEL_ID']).' ';
							
							$html .= $cols['LABEL'];
							
							if($user_phone=='Y')
								$html .=  ' '.$cols['LABEL_PHONE'];
							if($user_email=='Y')
								$html .=  ' '.$cols['LABEL_EMAIL'];
							if($user_address=='Y')
								$html .=  ' '.$cols['LABEL_ADDRESS'];							
							$html .= '</td>';
						break;
						case 'TRSUBTOT':
							if($totale_per_utente=='Y') {
								if($note=='N') $colspan = '4';
								else $colspan = '3';
								$html .= '<td colspan="'.$colspan.'" style="text-align:right;">Totale&nbsp;dell\'utente&nbsp;</td>';
								$html .= '<td style="text-align:center;">&nbsp;'.$cols['QTA'].'</td>';
								if($note=='Y')
									$html .= '<td></td>';
								if($trasportAndCost=='Y') {
									$html .= '<td style="text-align:right;">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
									$html .= '<td style="text-align:right;">';
									if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') $html .= __('TrasportShort').' '.$cols['IMPORTO_TRASPORTO_E'].'<br />';
									if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') $html .= __('CostMoreShort').' '.$cols['IMPORTO_COST_MORE_E'].'<br />';
									if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00') $html .= __('CostLessShort').' '.$cols['IMPORTO_COST_LESS_E'];
									$html .= '</td>';									

									$html .= '<td style="text-align:right;">'.$cols['IMPORTO_COMPLETO_E'].'</td>';
								}
								else 
									$html .= '<td style="text-align:right;">'.$cols['IMPORTO_COMPLETO_E'].'</td>';
							}
						break;
						case 'TRTOT':
							if($note=='N') $colspan = '4';
							else $colspan = '3';
							$html .= '<td colspan="'.$colspan.'" style="text-align:right;">Totale&nbsp;&nbsp;</td>';
							$html .= '<td style="text-align:center;">&nbsp;'.$cols['QTA'].'</td>';
							if($note=='Y')
								$html .= '<td></td>';
							$html .= '<td style="text-align:right;">&nbsp;'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
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
						break;								
						case 'TRDATA':
							$name = $cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']);
							if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
								if(!empty($cols['CODICE']))
									$name = $cols['CODICE'].' '.$name;
							}

							if($cols['DELETE_TO_REFERENT']=='Y') 
								$cols['IMPORTO_E'] = '0,00&nbsp;&euro;';
							
								
							if($trasportAndCost=='Y') {
								$html .= '<td width="'.$output->getCELLWIDTH20().'"></td>';
								$html .= '<td width="'.$output->getCELLWIDTH200().'" ';
								if($cols['DELETE_TO_REFERENT']=='Y') $html .= ' style="text-decoration: line-through;"';
								$html .= '>'.$name.'</td>';
								$html .= '<td width="'.$output->getCELLWIDTH60().'">'.$cols['PREZZO_E'].'</td>';
								if($note=='N') 
									$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$cols['PREZZO_UMRIF'].'</td>';
								$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH70().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
								if($note=='Y') 
									$html .= '<td width="'.$output->getCELLWIDTH70().'" style="border-bottom:1px solid #999;"></td>';
								$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
								$html .= '<td width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'"  colspan="2" style="text-align:right;">&nbsp;</td>';
							}
							else {								
								$html .= '<td width="'.$output->getCELLWIDTH20().'"></td>';
								$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH70()).'" ';
								if($cols['DELETE_TO_REFERENT']=='Y') $html .= ' style="text-decoration: line-through;"';
								$html .= '>'.$name.'</td>';
								$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$cols['PREZZO_E'].'</td>';
								if($note=='N') 
									$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$cols['PREZZO_UMRIF'].'</td>';
								$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH70().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
								if($note=='Y') 
									$html .= '<td width="'.$output->getCELLWIDTH100().'" style="border-bottom:1px solid #999;"></td>';
								$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
							} // end if($trasportAndCost=='Y')
						break;
						case 'TRDATABIS':
							if($trasportAndCost=='Y') 
								$colspan = '7';
							else
								$colspan = '5';
							$html .= '<td width="'.$output->getCELLWIDTH20().'"></td>';
							$html .= '<td colspan="'.$colspan.'">NOTA: '.$cols['NOTA'].'</td>';
						break;
					}
				} // end foreach ($rows as $typeRow => $cols)
			
				$html .= '</tr>';
				
			} // end foreach ($exportRows as $rows)
			
			$html .= '</tbody></table>';			
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

			$html = '';
			$html = $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
			
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