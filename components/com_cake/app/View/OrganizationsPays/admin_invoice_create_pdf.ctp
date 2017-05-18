<?php
App::import('Vendor','xtcpdf');
			
if($this->layout=='pdf') {
	App::import('Vendor','xtcpdf');
	
	$output = new XTCPDF($organization, PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$output->headerText = ''; // non lo valorizzo $fileData['fileTitle'];  
	$output->footerText = '';

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
$html .= '<p>&nbsp;</p>';
$html .= '<table cellpadding="0" cellspacing="0">';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<td style="border-bottom: none !important;" width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH100()).'">&nbsp;</td>';
$html .= '<td style="font-size:14px;border-bottom: none !important;" width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH10()).'">';
if(!empty($organizationResults['Organization']['payIntestatario'])) 
	$html .= $organizationResults['Organization']['payIntestatario'].'<br />';
else
	$html .= 'Gruppo d\'acquisto solidale '.$organizationResults['Organization']['name'].'<br />';
if(!empty($organizationResults['Organization']['payIndirizzo'])) $html .= $organizationResults['Organization']['payIndirizzo'].'<br />';
if(!empty($organizationResults['Organization']['payCap'])) $html .= $organizationResults['Organization']['payCap'].'&nbsp;';
if(!empty($organizationResults['Organization']['payCitta'])) $html .= $organizationResults['Organization']['payCitta'].'&nbsp;';
if(!empty($organizationResults['Organization']['payProv'])) $html .= '('.h($organizationResults['Organization']['payProv']).')';
if(!empty($organizationResults['Organization']['payCf'])) $html .= '<br />C.F. '.h($organizationResults['Organization']['payCf']);
if(!empty($organizationResults['Organization']['payPiva'])) $html .= '<br />P.iva '.h($organizationResults['Organization']['payPiva']);
$html .= '</td>';
$html .= '</tr>';
$html .= '</tbody>';
$html .= '</table>';

$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<div class="h1Pdf" style="text-align:center">'.$title.'</div>';
$html .= '<p style="font-size:12px">'.$intro.'</p>';
$html .= '<p style="font-size:12px">'.$text.'</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>'.$nota.'</p>';
$html .= '<p>'.$nota2.'</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';

$html .= '<table cellpadding="0" cellspacing="0">';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<td style="border-bottom: none !important;" width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH100()).'">';
$html .= 'Torino, '.date('d/m/Y');
$html .= '</td>';
$html .= '<td style="border-bottom: none !important;" width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH10()).'">';
$html .= '<div class="h3Pdf">In fede</div>';
$html .= '</td>';
$html .= '</tr>';
$html .= '</tbody>';
$html .= '</table>';

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');



// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
?>