<?php
/*
 * T O - S U P L I E R S
* Documento con elenco diviso per produttore (per fattura al produttore)
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

$html = $this->ExportDocs->delivery($user, $resultDelivery['Delivery']);
$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

	
$html = '';
$html .= '	<table>';
$html .= '	<tr>';
$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('N').'</th>';
$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH30()).'">'.__('Supplier').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH150().'">Data di inizio dell\'ordine</th>';
$html .= '			<th width="'.$output->getCELLWIDTH150().'">Data di chiusura dell\'ordine</th>';
$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';
$html .= '	</tr>';		

$totImporto = 0;
foreach($results as $numResult => $result) {

	if($result['Order']['data_inizio']!=Configure::read('DB.field.date.empty'))
		$data_inizio = $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y");
	else 
		$data_inizio = "";

	if($result['Order']['data_fine']!=Configure::read('DB.field.date.empty'))
		$data_fine = $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");
	else
		$data_fine = "";
	
	if($result['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
		$data_fine = $this->Time->i18nFormat($result['Order']['data_fine_validation'],"%A %e %B %Y");
	
	$html .= '<tr>';
	$html .= '	<td>'.((int)$numResult+1).'</td>';
	$html .= '	<td>'.$result['SupplierOrganization']['name'].'</td>';
	$html .= '	<td>'.$data_inizio.'</td>';
	$html .= '	<td>'.$data_fine.'</td>';
	$html .= '	<td style="text-align:right;">'.number_format($result[0]['totImporto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
	$html .= '</tr>';	
	
	$totImporto = ($totImporto + $result[0]['totImporto']);
}
$html .= '<tr>';
$html .= '	<td></td>';
$html .= '	<td></td>';
$html .= '	<td></td>';
$html .= '	<td style="text-align:right;">Totale&nbsp;&nbsp;</td>';
$html .= '	<td style="text-align:right;">'.number_format($totImporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
$html .= '</tr>';
$html .= '</table>';
$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData.'.pdf', 'D');
//echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>