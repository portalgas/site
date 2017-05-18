<?php 
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

$html = '';
$html = $this->ExportDocs->delivery($resultDelivery['Delivery']);
$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
if(!empty($results)) {
	
	$html = '';
	$html .= '<table cellpadding="0" cellspacing="0">';
	$html .= '<tbody>'; 

	$html .= '<tr>';
	$html .= '<th width="'.$output->getCELLWIDTH30().'">N.</th>';
	$html .= '<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'">'.__('Supplier').'</th>';
	// $html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Data inizio').'<br />'.__('Data fine').'</th>';
	$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Importo Dovuto').'</th>';
	$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Importo Pagato').'</th>';
	$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Differenza').'</th>';
	$html .= '<th width="'.$output->getCELLWIDTH100().'">Utenti che devono pagare</th>';
	$html .= '</tr>';

	foreach ($results as $numResult => $result) {
		
		$html .= '<tr>';
		
		$html .= '	<td width="'.$output->getCELLWIDTH30().'">'.($numResult+1).'</td>';
		$html .= '	<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'">'.$result['SuppliersOrganization']['name'].'</td>';
		// $html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$this->Time->i18nFormat($result['Order']['data_inizio'], "%e %b %Y").' a '.$this->Time->i18nFormat($result['Order']['data_fine'], "%e %b %Y").'	</td>';
		$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$result['Order']['tot_importo'].' &euro;</td>';
		$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$result['Order']['tot_importo_pagato'].' &euro;</td>';
		$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$result['Order']['tot_differenza'].' &euro;</td>';	
		$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$result['Order']['totUserToTesoriere'].'</td>';
		$html .= '</tr>';			
	} // loop orders
			
	/*
	 * totale consegna
	 */
	
	if(!empty($deliveryResults)) {
		$html .= '<tr>';
		$html .= '	<th width="'.$output->getCELLWIDTH30().'"></th>';
		$html .= '	<th style="text-align:right" width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'"><b>Totali</b>&nbsp;&nbsp;&nbsp;</th>';
		// $html .= '	<th  width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$deliveryResults['tot_importo_delivery'].' &euro;</th>';
		$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$deliveryResults['tot_importo_pagato_delivery'].' &euro;</th>';
		$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$deliveryResults['tot_differenza_delivery'].' &euro;</th>';
		$html .= '	<th width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '</tr>';		
	}
			
	$html .= '</tbody></table>';
}


$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
?>