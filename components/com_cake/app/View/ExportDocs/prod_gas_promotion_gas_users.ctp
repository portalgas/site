<?php echo $this->element('sql_dump');?>
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

	/*
	 * P R O M O T I O N S 
	 */
	$html .= $this->ExportDocs->promotion($results);

	foreach($results['ProdGasPromotionsOrganization'] as $numResult => $prodGasPromotionsOrganization) {
		
		/*
		 * G A S 
		 */
		$html .= $this->ExportDocs->title($prodGasPromotionsOrganization['Organization']['name']);

        if(!empty($prodGasPromotionsOrganization['Cart'])) {

			$html .= '	<table cellpadding="0" cellspacing="0">';
			$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
			$html .= '		<tr>';
			
			$html .= '			<th width="'.$output->getCELLWIDTH120().'">'.__('User').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH130().'">'.__('Name').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH80().'">'.__('Conf').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH80().'">'.__('Prezzo/UM').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('PrezzoUnita').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Qta').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH80().'" style="text-align:right;">'.__('Importo').'</th>';				
			$html .= '		</tr>';				
			$html .= '	</thead><tbody>';
				
			$tot_importo_user = 0;
			$tot_qta_user = 0;
            $user_id_old = 0;
            foreach($prodGasPromotionsOrganization['Cart'] as $numResult => $cart) {
				
				$html .= '<tr>';
				$html .= '<td width="'.$output->getCELLWIDTH120().'">'.$cart['User']['name'].'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH130().'">'.$cart['ArticlesOrder']['name'].'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH80().'">'.$this->App->getArticleConf($cart['Article']['qta'], $cart['Article']['um']).'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH80().'">'.$this->App->getArticlePrezzoUM($cart['ArticlesOrder']['prezzo'], $cart['Article']['qta'], $cart['Article']['um'], $cart['Article']['um_riferimento']).'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$this->App->getArticlePrezzo($cart['ArticlesOrder']['prezzo']).'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$cart['Cart']['qta'].'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH80().'" style="text-align:right;">'.$this->App->getArticleImporto($cart['ArticlesOrder']['prezzo'], $cart['Cart']['qta']).'</td>';
				$html .= '</tr>';
				
				$tot_importo_user += ($cart['ArticlesOrder']['prezzo'] * $cart['Cart']['qta']);
				$tot_qta_user += $cart['Cart']['qta'];
				
				/*
				 * totale per utente
				 */				
				if($user_id_old>0 && $user_id_old != $cart['User']['id']) {
					$tot_importo_user = number_format($tot_importo_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					
					$html .= '<tr>';
					$html .= '	<th width="'.$output->getCELLWIDTH120().'"></th>';
					$html .= '  <th width="'.$output->getCELLWIDTH130().'"></th>';
					$html .= '	<th width="'.$output->getCELLWIDTH80().'"></th>';
					$html .= '	<th width="'.$output->getCELLWIDTH80().'"></th>';
					$html .= '	<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Totale').'</th>';
					$html .= '	<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$tot_qta_user.'</th>';
					$html .= '	<th width="'.$output->getCELLWIDTH80().'" style="text-align:right;">'.$tot_importo_user.'&nbsp;&euro;</th>';
					$html .= '</tr>';				

					$tot_importo_user = 0;
				}

				$user_id_old = $cart['User']['id'];
			} // end foreach($prodGasPromotionsOrganization['Cart'] as $numResult => $cart)

			/*
			 * totale per utente
			 */				
			$tot_importo_user = number_format($tot_importo_user,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			$html .= '<tr>';
			$html .= '	<th width="'.$output->getCELLWIDTH120().'"></th>';
			$html .= '	<th width="'.$output->getCELLWIDTH130().'"></th>';
			$html .= '	<th width="'.$output->getCELLWIDTH80().'"></th>';
			$html .= '	<th width="'.$output->getCELLWIDTH80().'"></th>';
			$html .= '	<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Totale').'</th>';
			$html .= '	<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.$tot_qta_user.'</th>';
			$html .= '	<th width="'.$output->getCELLWIDTH80().'" style="text-align:right;">'.$tot_importo_user.'&nbsp;&euro;</th>';
			$html .= '</tr>';				


			$html .= '</tbody></table>';
			
			$html .= '<br />';

        } // end if(!empty($prodGasPromotionsOrganization['Cart']))
        else {
			$html .= '<div class="h3Pdf">Nessun acquisto da parte dei gasisti del G.A.S.</div>';
        }			
	} // end foreach($results['ProdGasPromotionsOrganization'] as $numResult => $prodGasPromotionsOrganization)
}	

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>