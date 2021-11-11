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
$this->App->d($results);

$html = '';
$html = $this->ExportDocs->delivery($delivery);
$html .= '<hr />';

if (!empty($results)) {

		$html = '';
		$html .= '	<table cellpadding="0" cellspacing="0">';
		$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
		$html .= '		<tr>';
		
		$html .= '	<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH10()).'">'.__('User').'</th>';
		$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.__('Supplier').'</th>';		 
		$html .= '	<th width="'.($output->getCELLWIDTH150()+$output->getCELLWIDTH20()).'">'.__('Name').'</th>';
		$html .= '	<th width="'.$output->getCELLWIDTH50().'">'.__('Conf').'</th>';
		$html .= '	<th style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.__('Qta').'</th>';
		$html .= '	<th style="text-align:center;" width="'.$output->getCELLWIDTH60().'">'.__('PrezzoUnita').'</th>';			
		$html .= '	<th width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.__('Importo').'</th>';	

		$html .= '		</tr>';				
		$html .= '	</thead><tbody>';
			
		foreach ($results as $numResult => $result) {

			$User_name = '';
			$Storeroom_qta = '';
			$Storeroom_prezzo = '';
			$Storeroom_importo = '';	
			
			if(isset($result['Storeroom']['articlesJustBookeds']))
				foreach($result['Storeroom']['articlesJustBookeds'] as $user) {
					$User_name = $user['User']['name'];
					$Storeroom_qta = $user['Storeroom']['qta'];
					$Storeroom_prezzo = $user['Storeroom']['prezzo'];
					$Storeroom_importo = $user['Storeroom']['importo'];
				
					$html .= '<tr>';
					$html .= '			<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH10()).'">'.$User_name.'</td>';
					$html .= '			<td width="'.$output->getCELLWIDTH100().'">'.$result['SuppliersOrganization']['name'].'</td>';
					$html .= '			<td width="'.($output->getCELLWIDTH150()+$output->getCELLWIDTH20()).'">'.$result['Storeroom']['name'].'</td>';			
					$html .= '			<td width="'.$output->getCELLWIDTH50().'">'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
					$html .= '			<td width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.$Storeroom_qta.'</td>';
					$html .= '			<td width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.number_format($Storeroom_prezzo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
					$html .= '			<td width="'.$output->getCELLWIDTH90().'" style="text-align:right;">'.$this->App->getArticleImporto($Storeroom_prezzo, $Storeroom_qta).'</td>';	
					$html .= '</tr>';
				} // loop 					
		}		

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