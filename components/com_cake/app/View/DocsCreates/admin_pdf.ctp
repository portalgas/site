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
/*
 * user
 */ 
$html .= '<tr>';
$html .= '<td style="font-size:14px; border-bottom: none !important;" width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH100()).'">';
$html .= $userResults['User']['name'].'<br />';
if(!empty($userResults['Profile']['address'])) $html .= $userResults['Profile']['address'].'<br />';
if(!empty($userResults['Profile']['city'])) $html .= $userResults['Profile']['city'].'&nbsp;';
if(!empty($userResults['Profile']['postal_code'])) $html .= $userResults['Profile']['postal_code'].'&nbsp;';
$html .= '</td>';
$html .= '<td style="font-size:14px;border-bottom: none !important;" width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH10()).'">&nbsp;</td>';
$html .= '</tr>';


$html .= '<tr>';
$html .= '<td style="border-bottom: none !important;" width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH100()).'"></td>';
$html .= '<td style="font-size:14px;border-bottom: none !important;" width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH10()).'">&nbsp;</td>';
$html .= '</tr>';

/*
 * organization
 */ 
$html .= '<tr>';
$html .= '<td style="border-bottom: none !important;" width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH100()).'">&nbsp;</td>';
$html .= '<td style="font-size:14px;border-bottom: none !important;" width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH10()).'">';
$html .= 'Gruppo d\'acquisto solidale '.$organizationResults['Organization']['name'].'<br />';
if(!empty($organizationResults['Organization']['indirizzo'])) $html .= $organizationResults['Organization']['indirizzo'].'<br />';
if(!empty($organizationResults['Organization']['cap'])) $html .= $organizationResults['Organization']['cap'].'&nbsp;';
if(!empty($organizationResults['Organization']['localita'])) $html .= $organizationResults['Organization']['localita'].'&nbsp;';
if(!empty($organizationResults['Organization']['provincia'])) $html .= '('.h($organizationResults['Organization']['provincia']).')';
if(!empty($organizationResults['Organization']['cf'])) $html .= '<br />C.F. '.h($organizationResults['Organization']['cf']);
if(!empty($organizationResults['Organization']['piva'])) $html .= '<br />P.iva '.h($organizationResults['Organization']['piva']);
$html .= '</td>';
$html .= '</tr>';
$html .= '</tbody>';
$html .= '</table>';

$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<div class="h1Pdf" style="text-align:center">'.$num.'/'.$year.' '.$name.'</div>';
$html .= '<p style="font-size:12px">'.$txt_testo.'</p>';
$html .= '<p>&nbsp;</p>';
$html .= '<p>&nbsp;</p>';

$html .= '<table cellpadding="0" cellspacing="0">';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<td style="border-bottom: none !important;" width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH100()).'">';
$html .= $organizationResults['Organization']['localita'].', '.$txt_data;
$html .= '</td>';
$html .= '<td style="border-bottom: none !important;" width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH10()).'">';
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
exit;
?>