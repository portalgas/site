<?php 
/*
 * Elenco utenti
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
	
$html = '';
$html .= '	<table cellpadding="0" cellspacing="0">';
$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
$html .= '		<tr>';
$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Suppliers').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH80().'">'.__('Frequenza').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH50().'">'.__('Code').'</th>';$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Name').'</th>';$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Mail').'</th>';$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Phone').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH80().'">'.__('Type').'</th>';
$html .= '	</tr>';
$html .= '	</thead><tbody>';

foreach($results as $numUser => $result) {

	$html .= '<tr>';
	$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($numUser+1).'</td>';
	$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$result['SuppliersOrganization']['name'].'</td>';
	$html .= '<td width="'.$output->getCELLWIDTH80().'">'.$result['SuppliersOrganization']['frequenza'].'</td>';
	$html .= '<td width="'.$output->getCELLWIDTH50().'">'.$result['Profile']['codice'].'</td>';	$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$result['User']['name'].'</td>';
	$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$result['User']['email'].'</td>';	
	$html .= '<td width="'.$output->getCELLWIDTH100().'">';
	if(!empty($result['Profile']['phone'])) $html .= $result['Profile']['phone'].'<br />';
	if(!empty($result['Profile']['phone2'])) $html .= $result['Profile']['phone2'];
	$html .= '</td>';
	$html .= '<td width="'.$output->getCELLWIDTH80().'">'.$this->App->traslateEnum($result['SuppliersOrganizationsReferent']['type']).'</td>';
	
	$html .= '</tr>';	
}
$html .= '</tbody></table>';

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>