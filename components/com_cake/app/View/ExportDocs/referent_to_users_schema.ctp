<?php 
/*
 * T O - U S E R S 
 *   Documento con elenco diviso per utente (per pagamento dell'utente) 
 *   senza il dettaglio dei singoli utenti 
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

	$html = $this->ExportDocs->delivery($user, $result['Delivery']);
	$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {

			$html = $this->ExportDocs->suppliersOrganization($order['SuppliersOrganization']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


			
			$html = '';
			$html .= '	<table class="table table-hover" cellpadding="0" cellspacing="0" border="0">';
			$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
			$html .= '		<tr>';
				

            $html .= '			<th colspan="2" width="'.$output->getCELLWIDTH100().'">'.__('Name').'</th>';
            $html .= '			<th width="'.($output->getCELLWIDTH90()+$output->getCELLWIDTH200()+$output->getCELLWIDTH100()).'" >'.__('Note').'</th>';
            $html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.__('qta').' totale</th>';
            $html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').' totale</th>';

			$html .= '		</tr>';				
			$html .= '	</thead><tbody>';


			foreach ($order['ExportRows'] as $rows) {
			
				$user_id = current(array_keys($rows));
				$rows = current(array_values($rows));
				
				$html .= '<tr>';
			
				foreach ($rows as $typeRow => $cols) {
			
					switch ($typeRow) {
						case 'TRGROUP':

							$html .= '<td colspan="2" width="'.$output->getCELLWIDTH100().'">';
							if($user_avatar=='Y')
								$html .=  ' '.$this->App->drawUserAvatar($user, $cols['LABEL_ID']).' ';
							
							/*
							 * tolgo Utente:
							*/
							$label = str_replace("Utente: ", "", $cols['LABEL']);
							$html .= $label;
							
							if($user_phone=='Y')
								$html .=  ' '.$cols['LABEL_PHONE'];
							if($user_email=='Y')
								$html .=  ' '.$cols['LABEL_EMAIL'];
							if($user_address=='Y')
								$html .=  ' '.$cols['LABEL_ADDRESS'];							
							$html .= '</td>';

                            // note
                            $html .= '<td width="'.($output->getCELLWIDTH90()+$output->getCELLWIDTH200()+$output->getCELLWIDTH100()).'"></td>';

                            /*
                             * estraggo il totale di un utente
                            */
                            foreach ($order['ExportRows'] as $rows2) {
                                $user_id2 = current(array_keys($rows2));
                                $rows2 = current(array_values($rows2));
                                foreach ($rows2 as $typeRow2 => $cols2)
                                if($typeRow2 == 'TRSUBTOT' && $user_id2 == $user_id) {
                                    $qta_totale_dell_utente = $cols2['QTA'];
                                    $importo_totale_dell_utente = $cols2['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols2['ISIMPORTOMOD']);
                                    $importo_trasporto = $cols2['IMPORTO_TRASPORTO_E'];
                                    $importo_cost_more = $cols2['IMPORTO_COST_MORE_E'];
                                    $importo_cost_less = $cols2['IMPORTO_COST_LESS_E'];
                                    $importo_completo = $cols2['IMPORTO_COMPLETO_E'];
                                }
                            }

                            $html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.$qta_totale_dell_utente.'</td>';
                            $html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$importo_totale_dell_utente.'</td>';

						break;
						case 'TRSUBTOT':

						break;
						case 'TRTOT':
								
							$html .= '<td colspan="2" width="'.$output->getCELLWIDTH100().'"></td>';
                            $html .= '<td width="'.($output->getCELLWIDTH90()+$output->getCELLWIDTH200()+$output->getCELLWIDTH100()).'" style="text-align:right;">Totale&nbsp;&nbsp;</td>';
                            $html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">&nbsp;'.$cols['QTA'].'</td>';
							$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">&nbsp;'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';

						break;								
						case 'TRDATA':

						break;
						case 'TRDATABIS':
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