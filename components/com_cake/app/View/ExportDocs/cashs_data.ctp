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
		$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Name').'</th>';
		$html .= '			<th width="'.$output->getCELLWIDTH200().'">'.__('Mail').'</th>';
		$html .= '			<th width="'.$output->getCELLWIDTH100().'" style="text-align:right;" colspan"2">'.__('Importo').'</th>';				
		$html .= '			<th width="'.$output->getCELLWIDTH200().'">'.__('Nota').'</th>';

		$html .= '		</tr>';				
		$html .= '	</thead><tbody>';
			
		$i=0;
		$tot_importo=0;
		foreach ($results as $numResult => $result) {
			
			$html .= '<tr>';
			$html .= '			<td width="'.$output->getCELLWIDTH30().'">'.($numResult + 1).'</td>';
			$html .= '			<td width="'.$output->getCELLWIDTH100().'">'.$result['User']['name'].'</td>';
			$html .= '			<td width="'.$output->getCELLWIDTH200().'">'.$result['User']['email'].'</td>';
			$html .= '			<td width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.$result['Cash']['importo_e'].'</td>';	
			$html .= '			<td width="'.$output->getCELLWIDTH10().'" style="text-align:right;';
			$html .= 'background-color:';
			if($result['Cash']['importo']=='0.00') $html .= '#fff';
			else
			if($result['Cash']['importo']<0) $html .= 'red';
			else
			if($result['Cash']['importo']>0) $html .= 'green';
			$html .= '">';			
			$html .= '</td>';				
			$html .= '			<td width="'.$output->getCELLWIDTH200().'">'.$result['Cash']['nota'].'</td>';
			$html .= '</tr>';
			
			$tot_importo += $result['Cash']['importo'];
			$i++;
		}		
		
		/*
		 * totale cassa
		 */
		$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
		$html .= '		<tr>';
		$html .= '			<th width="'.$output->getCELLWIDTH30().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH200().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH100().'" style="text-align:right;" colspan"2">'.$tot_importo.' &euro;</th>';				
		$html .= '			<th width="'.$output->getCELLWIDTH200().'"></th>';
		$html .= '		</tr>';				
		$html .= '</tbody></table>';
				
}	

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
?>