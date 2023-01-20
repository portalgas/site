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

		$html .= '<table class="table table-hover" cellpadding="0" cellspacing="0">';
		$html .= '<thead>'; // con questo TAG mi ripete l'intestazione della tabella
		$html .= '<tr>';		
		$html .= '<th width="'.$output->getCELLWIDTH30().'">N.</th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('User').'</th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Importo_dovuto').'</th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Importo_pagato').'</th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Delta').'</th>';
			if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
			$html .= '<th width="'.$output->getCELLWIDTH50().'">'.__('Importo POS').'</th>';
			$html .= '<th width="'.$output->getCELLWIDTH50().'">'.__('Cassa').'</th>';
		}
		else {
			$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Cassa').'</th>';	
		} 
		
		$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Nota').'</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>'; 

		$tot_importo_delivery = 0;
		$tot_importo_pagato_delivery = 0;
		$tot_importo_pos = 0;
		$tot_differenza_delivery = 0;
		foreach ($results as $numResult => $result) {

	
			$tot_importo = number_format($result[0]['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_importo_pagato = number_format($result[0]['tot_importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
			
			$differenza = (-1 * ($result[0]['tot_importo'] - $result[0]['tot_importo_pagato']));
			$differenza = number_format($differenza,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
			
			if(isset($result['Cash']['importo']))
				$importo_cash = number_format($result['Cash']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
			else
				$importo_cash = '0,00';
								
			$html .= '<tr>';
			$html .= '	<td width="'.$output->getCELLWIDTH30().'">'.((int)$numResult+1).'</td>';
			$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$result['User']['name'].'</td>';
			$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$tot_importo.'&nbsp;&euro;</td>';
			$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$tot_importo_pagato.'&nbsp;&euro;</td>';
			$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$differenza.'&nbsp;&euro;</td>';
			if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
				$html .= '	<td width="'.$output->getCELLWIDTH50().'">'.$result['SummaryDeliveriesPos']['importo_e'].'</td>';
				$html .= '	<td width="'.$output->getCELLWIDTH50().'">'.$importo_cash.'</td>';
			}
			else {
				$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$importo_cash.'</td>';
			}	
			$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$result['Cash']['nota'].'</td>';
			$html .= '</tr>';
			
			$tot_importo_delivery += $result[0]['tot_importo'];
			$tot_importo_pagato_delivery += $result[0]['tot_importo_pagato'];
			$tot_importo_pos += $result['SummaryDeliveriesPos']['importo'];
			$tot_differenza_delivery += (-1 * ($result[0]['tot_importo'] - $result[0]['tot_importo_pagato']));
			
			$tot_importo_cash += $result['Cash']['importo'];		
		} // loop 
			
	/*
	 * totale consegna
	 */
	$tot_importo_delivery = number_format($tot_importo_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_pagato_delivery = number_format($tot_importo_pagato_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_pos = number_format($tot_importo_pos,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_differenza_delivery = number_format($tot_differenza_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	 
	$html .= '<tr>';
	$html .= '	<th width="'.$output->getCELLWIDTH30().'"></th>';
	$html .= '	<th style="text-align:right" width="'.$output->getCELLWIDTH100().'"><b>Totali</b>&nbsp;&nbsp;&nbsp;</th>';
	$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_importo_delivery.'&nbsp;&euro;</th>';
	$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_importo_pagato_delivery.'&nbsp;&euro;</th>';
	$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_differenza_delivery.'&nbsp;&euro;</th>';
	if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
		$html .= '	<th width="'.$output->getCELLWIDTH50().'">'.$tot_importo_pos.'&nbsp;&euro;</th>';
		$html .= '	<th width="'.$output->getCELLWIDTH50().'">'.$tot_importo_cash.'&nbsp;&euro;</th>';
	}
	else {
		$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_importo_cash.'&nbsp;&euro;</th>';
	}		
	$html .= '	<th width="'.$output->getCELLWIDTH100().'"></th>';
	$html .= '</tr>';
			
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