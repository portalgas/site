<?php
$tmp = '';

$tmp .= $this->ExportDocs->delivery($results['Delivery'][0]);

$tmp .= '<div class="clearfix"></div>';
	
if($results['Delivery']['totOrders'] > 0) {
	$tot_importo=0;
	foreach($results['Delivery'][0]['Order'] as $numOrder => $order) {	
	
		$tmp .= '	<table>';
		$tmp .= '	<tr>';
		
		/*
		 * S U P P L I E R S _ O R G A N I Z A T I O N
		 */
		$tmp .= $this->ExportDocs->suppliersOrganizationShort($order['SuppliersOrganization']);
		
		if(isset($order['SummaryOrder'])) {
			$tmp .= '	<table>';
			$tmp .= '		<tr>';
			$tmp .= '			<th>'.__('N').'</th>';
			$tmp .= '			<th>'.__('User').'</th>';
			$tmp .= '			<th>'.__('Importo').'</th>';
			$tmp .= '			<th>'.__('Created').'</th>';;
			$tmp .= '			<th>'.__('Modified').'</th>';
			$tmp .= '	</tr>';		
		
			foreach($order['SummaryOrder'] as $numSummaryOrder => $summaryOrder) {
				$tmp .= '<tr>';
				$tmp .= '	<td>'.($numSummaryOrder+1).'</td>';
				$tmp .= '	<td>'.$summaryOrder['User']['name'].'</td>';
				$tmp .= '	<td>';

				$tmp .= $summaryOrder['SummaryOrder']['importo'].'&nbsp;&euro;';
				
				$tmp .= '</td>';
				$tmp .= '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($summaryOrder['SummaryOrder']['created']).'</td>';
				$tmp .= '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($summaryOrder['SummaryOrder']['modified']).'</td>';
				$tmp .= '</tr>';	
				
				$tot_importo += $summaryOrder['SummaryOrder']['importo'];
			}

			/*
			 * totali
			*/
			$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			$tmp .= "\r\n";
			$tmp .= '<tr>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td style="text-align:right;">Totale</td>';
			$tmp .= '	<td>'.$tot_importo.'&nbsp;&euro;</td>';
			$tmp .= '	<td></td>';
			$tmp .= '	<td></td>';
			$tmp .= '</tr>';
		
			$tmp .= '</table>';
		
		} // if(isset($order['SummaryOrder']))
		
		/*
		 * S U P P L I E R S _ O R G A N I Z A T I O N S _ R E F E R E N T S
		*/
		$tmp .= $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
		
	} // end ciclo orders
}

$tmp .= '<div class="clearfix"></div>';

echo $tmp;
?>