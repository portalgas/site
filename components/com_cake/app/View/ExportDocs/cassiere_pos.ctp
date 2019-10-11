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

if (!empty($results)) {

		$html = '';
		$html .= '	<table cellpadding="0" cellspacing="0">';
		$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
		$html .= '		<tr>';
		
		$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('N.').'</th>';
		$html .= '			<th width="'.$output->getCELLWIDTH200().'">'.__('Name').'</th>';
		$html .= '			<th width="'.$output->getCELLWIDTH300().'">'.__('Delivery').'</th>';	
		$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Importo POS').'</th>';

		$html .= '		</tr>';				
		$html .= '	</thead><tbody>';
	
			
		$tot_importo_pos=0;
		foreach ($results as $numResult => $result) {
			
			$html .= '<tr>';
			$html .= '			<td width="'.$output->getCELLWIDTH30().'">'.($numResult + 1).'</td>';
			$html .= '			<td width="'.$output->getCELLWIDTH200().'">'.$result['User']['name'].'</td>';
			$html .= '			<td width="'.$output->getCELLWIDTH300().'">'.$result['Delivery']['luogoData'].'</td>';
			$html .= '			<td width="'.$output->getCELLWIDTH100().'" style="text-align:right;">'.$result['SummaryDeliveriesPos']['importo_e'].'</td>';
			$html .= '</tr>';
			
			$tot_importo_pos += $result['SummaryDeliveriesPos']['importo'];
		}		
		
		/*
		 * totali
		 */
		$tot_importo_pos = number_format($tot_importo_pos,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
		$html .= '		<tr>';
		$html .= '			<th width="'.$output->getCELLWIDTH30().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH200().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH300().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH100().'" style="text-align:right;">'.$tot_importo_pos.'&nbsp;&euro;</th>';		
		$html .= '		</tr>';				
		$html .= '</tbody></table>';
				
}	

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>