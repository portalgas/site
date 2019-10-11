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

if (!empty($results['Order'])) {

		$html .= '<table cellpadding="0" cellspacing="0">';
		$html .= '<tbody>'; 

		$tot_importo_delivery = 0;
		$tot_importo_pagato_delivery = 0;
		$tot_differenza_delivery = 0;
		foreach ($results['Order'] as $numResult => $order) {

			$html .= '<tr>';
			
			$html .= '	<td colspan="2" width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH30()).'">';
			$html .= '<b>'.__('Supplier').'</b>: '.$order['SuppliersOrganization']['name'].'</td>';
			$html .= '	<td colspan="2" width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'">';
			$html .= 'dal '.$this->Time->i18nFormat($order['data_inizio'], "%e %b %Y").' al '.$this->Time->i18nFormat($order['data_fine'], "%e %b %Y");	
			$html .= '	</td>';

			$html .= '	<td width="'.$output->getCELLWIDTH100().'">';
			if($order['tesoriere_fattura_importo']>0)
				$html .= '<b>'.__('Tesoriere fattura importo Short').'</b> '.number_format($order['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			$html .= '</td>';

			$html .= '	<td width="'.$output->getCELLWIDTH100().'">';
			if($order['tot_importo']>0)
				$html .=  '<b>'.__('Importo totale ordine Short').'</b> '.number_format($order['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			$html .= '</td>';
			
			$html .= '<td width="'.$output->getCELLWIDTH100().'">Utenti che devono pagare <b>'.$order['totUserToTesoriere'].'</b></td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<th width="'.$output->getCELLWIDTH30().'">N.</th>';
			$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('User').'</th>';
			$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Importo_dovuto').'</th>';
			$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Importo_pagato').'</th>';
			$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Delta').'</th>';
			$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Cash').'</th>';
			$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Modality').'</th>';
			$html .= '</tr>';
	
			$tot_importo = 0;
			$tot_importo_pagato = 0;
			$tot_differenza = 0;
			$tot_importo_cash = 0;			
			foreach ($order['SummaryOrder'] as $numResult2 => $result) {

					$importo = number_format($result['SummaryOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$importo_pagato = number_format($result['SummaryOrder']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
					
					$differenza = (-1 * ($result['SummaryOrder']['importo'] - $result['SummaryOrder']['importo_pagato']));
					$differenza = number_format($differenza,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
										
					if(isset($result['Cash']['importo']))
						$importo_cash = number_format($result['Cash']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
					else
						$importo_cash = '0,00';
						
					$html .= '<tr>';
					$html .= '	<td width="'.$output->getCELLWIDTH30().'">'.($numResult2+1).'</td>';
					$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$result['User']['name'].'</td>';
					$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$importo.'&nbsp;&euro;</td>';
					$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$importo_pagato.'&nbsp;&euro;</td>';
					$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$differenza.'&nbsp;&euro;</td>';
					$html .= '	<td width="'.$output->getCELLWIDTH100().'">'.$importo_cash.'&nbsp;&euro;</td>';
					$html .= '	<td width="'.$output->getCELLWIDTH100().'">';
					if($result['SummaryOrder']['modalita']!='DEFINED')
						$html .= $this->App->traslateEnum($result['SummaryOrder']['modalita']);
					$html .= '</td>';
					$html .= '</tr>';
					
					$tot_importo += $result['SummaryOrder']['importo'];
					$tot_importo_pagato += $result['SummaryOrder']['importo_pagato'];
					$tot_differenza += (-1 * ($result['SummaryOrder']['importo'] - $result['SummaryOrder']['importo_pagato']));
					
					$tot_importo_delivery += $result['SummaryOrder']['importo'];
					$tot_importo_pagato_delivery += $result['SummaryOrder']['importo_pagato'];
					$tot_differenza_delivery += (-1 * ($result['SummaryOrder']['importo'] - $result['SummaryOrder']['importo_pagato']));
					
					$tot_importo_cash += $result['Cash']['importo'];				
			} // loop summaryOrders

			/*
			 * totale ordine
			 */
			$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_importo_pagato = number_format($tot_importo_pagato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_differenza = number_format($tot_differenza,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			 
			$html .= '<tr>';
			$html .= '	<th width="'.$output->getCELLWIDTH30().'"></th>';
			$html .= '	<th style="text-align:right" width="'.$output->getCELLWIDTH100().'"><b>Totali</b>&nbsp;&nbsp;&nbsp;</th>';
			$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_importo.'&nbsp;&euro;</th>';
			$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_importo_pagato.'&nbsp;&euro;</th>';
			$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_differenza.'&nbsp;&euro;</th>';
			$html .= '	<th width="'.$output->getCELLWIDTH100().'"></th>';
			$html .= '	<th width="'.$output->getCELLWIDTH100().'"></th>';
			$html .= '</tr>';
			
		} // loop orders
			
	/*
	 * totale consegna
	 */
	$tot_importo_delivery = number_format($tot_importo_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_pagato_delivery = number_format($tot_importo_pagato_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_differenza_delivery = number_format($tot_differenza_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	 
	$html .= '<tr>';
	$html .= '	<th width="'.$output->getCELLWIDTH30().'"></th>';
	$html .= '	<th style="text-align:right" width="'.$output->getCELLWIDTH100().'"><b>Totali</b>&nbsp;&nbsp;&nbsp;</th>';
	$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_importo_delivery.'&nbsp;&euro;</th>';
	$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_importo_pagato_delivery.'&nbsp;&euro;</th>';
	$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_differenza_delivery.'&nbsp;&euro;</th>';
	$html .= '	<th width="'.$output->getCELLWIDTH100().'">'.$tot_importo_cash.'&nbsp;&euro;</th>';
	$html .= '	<th width="'.$output->getCELLWIDTH100().'"></th>';
	$html .= '</tr>';
			
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