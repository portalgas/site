<?php
/*
 * T O - U S E R S
* Documento con elenco diviso per utente (per pagamento dell'utente)
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

$html = $this->ExportDocs->delivery($resultDelivery['Delivery']);
$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');



$html = '';
$html .= '	<table>';
$html .= '	<tr>';
$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('N').'</th>';
$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH200()).'">'.__('User').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH200().'" style="text-align:right;">'.__('Importo').'</th>';
$html .= '	</tr>';		

$tot_importo = 0;
foreach($results as $numResult => $result) {
	
	$html .= '<tr>';
	$html .= '	<td width="'.$output->getCELLWIDTH30().'">'.($numResult+1).'</td>';
	$html .= '	<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH200()).'">'.$result['User']['name'].'</td>';
	$html .= '	<td width="'.$output->getCELLWIDTH200().'" style="text-align:right;">'.number_format($result['SummaryOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
	$html .= '</tr>';

	$tot_importo = ($tot_importo + $result['SummaryOrder']['importo']);
}
$html .= '<tr>';
$html .= '	<td></td>';
$html .= '	<td style="text-align:right;">Totale&nbsp;&nbsp;</td>';
$html .= '	<td style="text-align:right;">'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
$html .= '</tr>';
$html .= '</table>';
$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData.'.pdf', 'D');
// echo $output->Output($fileData['fileName'].'.pdf', 'D');
?>