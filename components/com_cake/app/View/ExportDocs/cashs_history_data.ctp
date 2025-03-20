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
		$html .= '	<table class="table table-hover" cellpadding="0" cellspacing="0">';
		$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
		$html .= '		<tr>';
		
		$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Name').'</th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'" style="text-align:left;" colspan="2">'.__('CashSaldo').'</th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'" style="text-align:left;">'.__('CashOperazione').'</th>';
        $html .= '<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH30()).'" style="text-align:right;">'.__('nota').'</th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'" style="text-align:right;">'.__('Created').'</th>';

		$html .= '		</tr>';
		$html .= '	</thead><tbody>';

		$tot_importo=0;
		foreach ($results as $numResult => $user) {
			
			foreach ($user['Cash'] as $numResult2 => $result) {
				$html .= '<tr>';

				if($numResult2==0) {
					$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$user['User']['name'].'</td>';
				}
				else {
					$html .= '<td width="'.$output->getCELLWIDTH100().'"></td>';
				}

                $html .= '<td width="'.$output->getCELLWIDTH10().'" style="text-align:right;';
                $html .= 'background-color:';
                if($result['CashesHistory']['importo']=='0.00') $html .= '#fff';
                else
                    if($result['CashesHistory']['importo']<0) $html .= 'red';
                    else
                        if($result['CashesHistory']['importo']>0) $html .= 'green';
                $html .= '">';
                $html .= '</td>';
				$html .= '<td width="'.$output->getCELLWIDTH90().'" style="text-align:left;">'.$result['CashesHistory']['importo_e'].'</td>';

	            $html .= '<td width="'.$output->getCELLWIDTH100().'">';
				if($result['CashesHistory']['operazione']>0)
					$html .=  '+';		
				$html .=  $result['CashesHistory']['operazione_e'];
	            $html .= '</td>';
				$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH30()).'" style="text-align:right;">'.$result['CashesHistory']['nota'].'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH100().'" style="text-align:right;">';
				if((count($user['Cash'])-1) > $numResult2)
					$html .= CakeTime::format($result['CashesHistory']['created'], "%A, %e %B %Y");
				$html .= '</td>'; 
				$html .= '</tr>';
				
				$tot_importo += $result['CashesHistory']['importo'];
  
			} // loop Cashs

		} // loop Users
		
		/*
		 * totale cassa
		$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
		$html .= '<tr>';
		$html .= '<th width="'.$output->getCELLWIDTH30().'"></th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'" style="text-align:right;" colspan"2">'.$tot_importo.'&nbsp;&euro;</th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '<th width="'.$output->getCELLWIDTH200().'"></th>';
		$html .= '<th width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '</tr>';
		*/
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