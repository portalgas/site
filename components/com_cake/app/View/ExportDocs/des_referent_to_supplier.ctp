<?php 
$debug = false;

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
if(isset($desOrdersResults['Supplier']))
	$html = $this->ExportDocs->desSupplier($desOrdersResults['Supplier']);

$html .= '	<table cellpadding="0" cellspacing="0">';
$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
$html .= '		<tr>';
$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('Bio').'</th>';
	
if($showCodice=='Y') {
	$html .= '			<th width="'.$output->getCELLWIDTH50().'">'.__('Codice').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH300().'">'.__('Name').'</th>';
}
else
	$html .= '			<th width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH50()).'">'.__('Name').'</th>';
	
$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.__('qta').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH80().'" style="text-align:center;">'.__('PrezzoUnita').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH80().'" style="text-align:right;">'.__('Importo').'</th>';
$html .= '	</tr>';
$html .= '	</thead><tbody>';

$i=0;
$tot_qta = 0;
$tot_importo = 0;
$article_organization_id_old=0;
$article_id_old = 0;
foreach($results as $numResult => $result) {

	if($article_id_old > 0 && // salto la prima volta
	   ($article_organization_id_old != $result['ArticlesOrder']['article_organization_id'] ||
	    $article_id_old != $result['ArticlesOrder']['article_id'])) {
		
		$html .= '<tr>';
		
		$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($i+1).'</td>';
		$html .= '	<td width="'.$output->getCELLWIDTH30().'">';
		if($bio=='Y') $html .= 'Bio';
		$html .= '</td>';

		
		if($showCodice=='Y') {
			$html .= '			<td width="'.$output->getCELLWIDTH50().'">'.$codiceArticle.'</td>';
			$html .= '			<td width="'.$output->getCELLWIDTH300().'">'.$name.'</td>';
		}
		else
			$html .= '			<td width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH50()).'">'.$name.'</td>';
			
		$html .= '			<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.$tot_qta_single_article.'</td>';				
		$html .= '			<td width="'.$output->getCELLWIDTH80().'" style="text-align:center;">'.$this->App->getArticlePrezzo($prezzo).'</td>';
		$html .= '			<td width="'.$output->getCELLWIDTH80().'" style="text-align:right;">'.number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
		
			
		$html .= '</tr>';	
		
		$i++;
		
		$bio = '';
		$codiceArticle = '';
		$name = '';
		$prezzo = '';
		$tot_qta_single_article = 0;
		$tot_importo_single_article = 0;
			
	}  
	
	/*
	 * gestione qta e importi
	 * */
	if($result['Cart']['qta_forzato']>0) {
		$qta = $result['Cart']['qta_forzato'];
		$qta_modificata = true;
	}	
	else {
		$qta = $result['Cart']['qta'];
		$qta_modificata = false;
	}
	$importo_modificato = false;
	if($result['Cart']['importo_forzato']==0) {
		if($result['Cart']['qta_forzato']>0) 
			$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
		else {
			$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
		}	
	}	
	else {
		$importo = $result['Cart']['importo_forzato'];
		$importo_modificato = true;
	}
					
	$tot_importo += $importo;
	$tot_qta += $qta;
					
	$bio = $result['Article']['bio'];
	$codiceArticle = $result['Article']['codice'];
	$name = $result['Article']['name'].' '.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
	$prezzo = $result['ArticlesOrder']['prezzo'];
	$tot_qta_single_article += $qta;
	$tot_importo_single_article += $importo;
	$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	$article_organization_id_old = $result['ArticlesOrder']['article_organization_id'];
	$article_id_old = $result['ArticlesOrder']['article_id'];
					
}  // loop articoli
 	

$html .= '<tr>';		
$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($i+1).'</td>';
$html .= '	<td width="'.$output->getCELLWIDTH30().'">';
if($bio=='Y') $html .= 'Bio';
$html .= '</td>';
				
if($showCodice=='Y') {
	$html .= '			<td width="'.$output->getCELLWIDTH50().'">'.$codiceArticle.'</td>';
	$html .= '			<td width="'.$output->getCELLWIDTH300().'">'.$name.'</td>';
}
else
	$html .= '			<td width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH50()).'">'.$name.'</td>';
	
$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.$tot_qta_single_article.'</td>';
$html .= '<td width="'.$output->getCELLWIDTH80().'" style="text-align:center;">'.$this->App->getArticlePrezzo($prezzo).'</td>';
$html .= '<td width="'.$output->getCELLWIDTH80().'" style="text-align:right;">'.number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';			
		
$html .= '</tr>';	

$html .= '<tr>';

if($showCodice=='Y') 
	$colspan = '3';
else
	$colspan = '2';

$html .= '	<th width="'.$output->getCELLWIDTH20().'"></th>';
$html .= '	<th colspan="'.$colspan.'" style="text-align:right;">'.__('qta_tot').'</th>';
$html .= '	<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">&nbsp;'.$tot_qta.'</th>';
$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH80()).'" colspan="2" style="text-align:right;">'.__('Importo_totale').'&nbsp;'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</th>';			

$html .= '</tr>';


$html .= '</tbody></table>';
$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');




	
/* 
 *  dati da comunicare al produttore
 */
 $html = '';
 $draw_header_table = false;
 foreach($desOrdersResults['DesOrdersOrganizations'] as $desOrdersOrganization) {
 	
 	if(!empty($desOrdersOrganization['DesOrdersOrganization']['luogo'])) {
		 	
		 	if(!$draw_header_table) {

				$html = '';
				$html .= '<h2>Indicazioni per la consegna</h2>';
				
				$html .= '<table cellpadding="0" cellspacing="0">';
				$html .= '<thead><tr>';
				$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('G.A.S.').'</th>';
				$html .= '<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH30()).'">'.__('Delivery').'</th>';
				$html .= '<th width="'.$output->getCELLWIDTH300().'">'.__('Riferimenti').'</th>';
				$html .= '</tr></thead><tbody>';
		 	
		 		$draw_header_table = true;
		 		
		 	} // end (!$draw_header_table)
			
				 	
			$html .= '<tr class="view-2">';							
			$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$desOrdersOrganization['Organization']['name'].'</td>';
			$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH30()).'">';
			$html .= $desOrdersOrganization['DesOrdersOrganization']['luogo'];
			if($desOrdersOrganization['DesOrdersOrganization']['data']!=Configure::read('DB.field.date.empty'))
				$html .= '<br />'.$this->Time->i18nFormat($desOrdersOrganization['DesOrdersOrganization']['data'],"%A, %e %B %Y");
			if($desOrdersOrganization['DesOrdersOrganization']['orario']!='00:00:00')
				$html .= '<br />'.$this->App->formatOrario($desOrdersOrganization['DesOrdersOrganization']['orario']);
			$html .= '</td>';
				
			$html .= '<td width="'.$output->getCELLWIDTH300().'">';
			if(!empty($desOrdersOrganization['DesOrdersOrganization']['contatto_nominativo']))
				$html .= '<br />'.$desOrdersOrganization['DesOrdersOrganization']['contatto_nominativo'];
			if(!empty($desOrdersOrganization['DesOrdersOrganization']['contatto_telefono']))
				$html .= '<br />'.$desOrdersOrganization['DesOrdersOrganization']['contatto_telefono'];
			if(!empty($desOrdersOrganization['DesOrdersOrganization']['contatto_mail']))
				$html .= '<br />'.$desOrdersOrganization['DesOrdersOrganization']['contatto_mail'];
			$html .= '</td>';		
			$html .= '</tr>';	
			 	
			if(!empty($desOrdersOrganization['DesOrdersOrganization']['nota'])) {
				$html .= '<tr>';
				$html .= '<td width="'.$output->getCELLWIDTH100().'"></td>';		
				$html .= '<td colspan="2" width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH200()+$output->getCELLWIDTH100()+$output->getCELLWIDTH30()).'">'.$desOrdersOrganization['DesOrdersOrganization']['nota'].'</td>';
				$html .= '</tr>';			
			}
			
 	} // if(!empty($desOrdersOrganization['DesOrdersOrganization']['luogo'])) 
 }
 
 if($draw_header_table)
 	$html .= '</tbody></table>';	

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align=''); 	 
 	 
 	 


// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>