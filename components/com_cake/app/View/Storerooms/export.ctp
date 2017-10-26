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
		
		if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Supplier').'</th>';		 
			$html .= '			<th width="'.($output->getCELLWIDTH150()+$output->getCELLWIDTH20()).'">'.__('Name').'</th>';
		}
		else {
			$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('N.').'</th>';
			$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.__('Supplier').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH200().'">'.__('Name').'</th>';
		}
		$html .= '			<th width="'.$output->getCELLWIDTH50().'">'.__('Conf').'</th>';
		$html .= '			<th width="'.$output->getCELLWIDTH60().'">'.__('PrezzoUnita').'</th>';
		$html .= '			<th width="'.$output->getCELLWIDTH50().'">'.__('StoreroomQta').'</th>';
		if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			$html .= '	<th style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.__('StoreroomArticleToBooked').'</th>';
			$html .= '	<th style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.__('StoreroomArticleJustBooked').'</th>';			
		}		
		$html .= '			<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.__('Importo').'</th>';	

		$html .= '		</tr>';				
		$html .= '	</thead><tbody>';
			
		foreach ($results as $numResult => $result) {
			
			$html .= '<tr>';
			if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
				$html .= '			<td width="'.$output->getCELLWIDTH100().'">'.$result['SuppliersOrganization']['name'].'</td>';
				$html .= '			<td width="'.($output->getCELLWIDTH150()+$output->getCELLWIDTH20()).'">'.$result['Storeroom']['name'].'</td>';			
			}
			else { 
				$html .= '			<td width="'.$output->getCELLWIDTH30().'">'.($numResult + 1).'</td>';
				$html .= '			<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.$result['SuppliersOrganization']['name'].'</td>';
				$html .= '			<td width="'.$output->getCELLWIDTH200().'">'.$result['Storeroom']['name'].'</td>';
			}
			$html .= '			<td width="'.$output->getCELLWIDTH50().'">'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
			$html .= '			<td width="'.$output->getCELLWIDTH60().'">'.number_format($result['Storeroom']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
			$html .= '			<td  width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$result['Storeroom']['qtaTot'].'</td>';
			if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
				$html .= '			<td width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.$result['Storeroom']['qtaToBooked'].'</td>';
				$html .= '			<td width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.$result['Storeroom']['qtaJustBooked'].'</td>';
			}
			$html .= '			<td width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.$this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']).'</td>';	
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