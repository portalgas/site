<?php 
/*
 * T O - U S E R S - A L L - M O D I F Y
*  Documento con elenco diviso per utente con tutte le modifiche (per confrontare i dati dell'utente con le modifiche del referente) 
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
			$html .= '	<table class="table table-hover" cellpadding="0" cellspacing="0">';
			$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
			$html .= '		<tr>';
			$html .= '			<th rowspan="2" width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
			$html .= '			<th rowspan="2" width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH40()).'">'.__('Name').'</th>';
			$html .= '			<th rowspan="2" width="'.$output->getCELLWIDTH40().'">'.__('PrezzoUnita').'</th>';
			// $html .= '			<th rowspan="2" width="'.$output->getCELLWIDTH40().'">'.__('Prezzo/UM').'</th>';
			$html .= '			<th rowspan="2" width="'.$output->getCELLWIDTH50().'">'.__('qta').'</th>';
			$html .= '			<th rowspan="2" width="'.$output->getCELLWIDTH70().'">'.__('Importo').'</th>';
			
			/*
			 * qui indicate tutte le eventuali modifiche 
			 */
			$html .= '			<th width="'.$output->getCELLWIDTH50().'">'.__('qta').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('Importo').'</th>';
			$html .= '			<th colspan="2" width="'.($output->getCELLWIDTH50()+$output->getCELLWIDTH70()).'">Quantità e importo totali</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">Imp. forzato</th>';
			$html .= '	</tr>';
			
			$html .= '	<tr>';
			$html .= '			<th width="'.($output->getCELLWIDTH50() + $output->getCELLWIDTH70()).'" colspan="2" style="text-align:center;">dell\'utente</th>';
			$html .= '			<th width="'.($output->getCELLWIDTH50() + $output->getCELLWIDTH70()).'" colspan="2" style="text-align:center;">modificati dal referente</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'"></th>';
			$html .= '	</tr>';
			$html .= '	</thead><tbody>';


			foreach ($order['ExportRows'] as $rows) {
			
				$user_id = current(array_keys($rows));
				$rows = current(array_values($rows));
				
				$html .= '<tr>';
			
				foreach ($rows as $typeRow => $cols) {
			
					switch ($typeRow) {
						case 'TRGROUP':
							$html .= '<td colspan="5" >'.$cols['LABEL'].'</td>';
							$html .= '<td style="background-color:#F5F5F5;" colspan="5"></td>';
						break;
						case 'TRSUBTOT':
							$html .= '<td colspan="3" style="text-align:right;">Totale&nbsp;dell\'utente&nbsp;</td>';
							$html .= '<td>&nbsp;'.$cols['QTA'].'</td>';
							$html .= '<td>'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
							$html .= '<td style="background-color:#F5F5F5;" colspan="5">';
							
							if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
								($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
								($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y') {

								if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00')
									$html .= '+&nbsp;'.$cols['IMPORTO_TRASPORTO_E'].'&nbsp;di&nbsp;'.__('TrasportShort');

								if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00')
									$html .= '+&nbsp;'.$cols['IMPORTO_COST_MORE_E'].'&nbsp;di&nbsp;'.__('CostMoreShort');
								
								if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')
									$html .= '&nbsp;'.$cols['IMPORTO_COST_LESS_E'].'&nbsp;di&nbsp;'.__('CostLessShort');
								
								$html .= '&nbsp;=&nbsp;'.$cols['IMPORTO_COMPLETO_E'];
							}
								
							$html .= '</td>';
						break;
						case 'TRTOT':
							$html .= '<td colspan="3" style="text-align:right;">Totale&nbsp;</td>';
							$html .= '<td>&nbsp;'.$cols['QTA'].'</td>';
							$html .= '<td>&nbsp;'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
							$html .= '<td style="background-color:#F5F5F5;" colspan="5">';

							if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
								($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
								($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y') {

								if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00')
									$html .= '+&nbsp;'.$cols['IMPORTO_TRASPORTO_E'].'&nbsp;di&nbsp;'.__('TrasportShort');

								if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00')
									$html .= '+&nbsp;'.$cols['IMPORTO_COST_MORE_E'].'&nbsp;di&nbsp;'.__('CostMoreShort');
								
								if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')
									$html .= '-&nbsp;'.$cols['IMPORTO_COST_LESS_E'].'&nbsp;di&nbsp;'.__('CostLessShort');
								
								$html .= '&nbsp;=&nbsp;'.$cols['IMPORTO_COMPLETO_E'];
							}
							
							$html .= '</td>';
						break;								
						case 'TRDATA':
							$name = $cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']);
							
							if($cols['DELETE_TO_REFERENT']=='Y')
								$deleteToReferent = 'text-decoration:line-through;';
							else
								$deleteToReferent = '';
							
							$html .= '<td width="'.$output->getCELLWIDTH20().'">'.$cols['NUM'].'</td>';
							$html .= '<td style="'.$deleteToReferent.'" width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH40()).'">'.$name.'</td>';
							$html .= '<td width="'.$output->getCELLWIDTH40().'">'.$cols['PREZZO_E'].'</td>';
							// $html .= '<td width="'.$output->getCELLWIDTH40().'">'.$cols['PREZZO_UMRIF'].'</td>';
							$html .= '<td width="'.$output->getCELLWIDTH50().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
							$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
							
							$html .= '<td style="background-color:#F5F5F5;'.$deleteToReferent.'" width="'.$output->getCELLWIDTH50().'">'.$cols['QTAUSER'].'</td>';
							$html .= '<td style="background-color:#F5F5F5;'.$deleteToReferent.'"  width="'.$output->getCELLWIDTH70().'">'.$cols['IMPORTOUSER_E'].'</td>';
							$html .= '<td style="background-color:#F5F5F5;" width="'.$output->getCELLWIDTH50().'">'.$cols['QTAREF'].'</td>';
							$html .= '<td style="background-color:#F5F5F5;"  width="'.$output->getCELLWIDTH70().'">'.$cols['IMPORTOREF'].'</td>';
							$html .= '<td style="background-color:#F5F5F5;text-align:right;" width="'.$output->getCELLWIDTH70().'">'.$cols['IMPORTOFORZATO_E'].'</td>';
						break;							
						case 'TRDATABIS':
							$html .= '<td width="'.$output->getCELLWIDTH20().'"></td>';
							$html .= '<td colspan="9">NOTA: '.$cols['NOTA'].'</td>';
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