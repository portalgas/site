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


if(!empty($results)) {
			
	$html = '';
	$html .= '	<table cellpadding="0" cellspacing="0">';	$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella	$html .= '		<tr>';				
	$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()+$output->getCELLWIDTH70()).'" colspan="2">'.__('Name').'</th>';	$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('PrezzoUnita').'</th>';	$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Prezzo/UM').'</th>';	$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('qta').'</th>';	$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';				
	$html .= '		</tr>';				
	$html .= '	</thead><tbody>';


	foreach ($results as $rows) {
	
		$user_id = current(array_keys($rows));		$rows = current(array_values($rows));		
		$html .= '<tr>';
			
		foreach ($rows as $typeRow => $cols) {
	
			switch ($typeRow) {
				case 'TRGROUP':
					
					$colspan = '6';
					$html .= '<td colspan="'.$colspan.'" >'.$cols['LABEL'];
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
						$html .= '<td colspan="4" style="text-align:right;">Totale&nbsp;dell\'utente&nbsp;</td>';
						$html .= '<td style="text-align:center;">&nbsp;'.$cols['QTA'].'</td>';
						$html .= '<td style="text-align:right;">'.$cols['IMPORTO'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
						if($trasportAndCost=='Y') {
							$html .= '<td style="text-align:right;">';
							if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') $html .= __('TrasportShort').' '.$cols['IMPORTO_TRASPORTO'].'<br />';
							if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') $html .= __('CostMoreShort').' '.$cols['IMPORTO_COST_MORE'].'<br />';
							if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00') $html .= __('CostLessShort').' '.$cols['IMPORTO_COST_LESS'];
							$html .= '</td>';
							
							$html .= '<td style="text-align:right;">';
							$html .= $cols['IMPORTO_COMPLETO'];
							$html .= '</td>';
						}
					}
				break;
				case 'TRTOT':
					$html .= '<td colspan="4" style="text-align:right;">Totale&nbsp;&nbsp;</td>';
					$html .= '<td style="text-align:center;">&nbsp;'.$cols['QTA'].'</td>';
					$html .= '<td style="text-align:right;">&nbsp;'.$cols['IMPORTO'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
				break;								
				case 'TRDATA':
					
					$name = $cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']);
																	$html .= '<td width="'.$output->getCELLWIDTH20().'"></td>';					$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH70()).'">'.$name.'</td>';					$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$cols['PREZZO_E'].'</td>';					$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$cols['PREZZO_UMRIF'].' al '.$this->App->traslateEnum($cols['UMRIF']).'</td>';					$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH70().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';					$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$cols['IMPORTO'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';				break;
				case 'TRDATABIS':
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
	$html = $output->getLegenda();
	$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
}
else {
	$html = '<div class="h4PdfNotFound">'.__('export_docs_not_found').'</div>';
	$output->writeHTMLCell(0,0,15,40, $css.$html, $border=0, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}  // end if(!empty($results))


// reset pointer to the last page
$output->lastPage();

echo $output->Output($fileData['fileName'].'.pdf', 'D');
?>