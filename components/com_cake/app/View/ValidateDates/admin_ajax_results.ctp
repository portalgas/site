<?php
if(!empty($results)) {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '	<th>order_id<br />delivery_id</th>';
	echo '	<th>'.__('SuppliersOrganization').'</th>';
	echo '  <th>'.__('StatoElaborazione').'</th>';
	echo '  <th>'.__('totImportoUserAcquisti').'</th>';
	echo '  <th>'.__('importo_fattura').'</th>';
	echo '  <th>Totale importo dell\'ordine</th>';
	echo '  <th>Totale importo dovuto<br />(somma degli importi degli utenti)</th>';
	echo '  <th>Delta</th>';
	if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') 
		echo '  <th colspan="3">'.__('Request Payment').'</th>';
	echo '</tr>';
	
	$tot_tesoriere_fattura_importo = 0;
	$tot_importo = 0;
	$tot_importo_rimborsate = 0;
	$tot_delta = 0;
	foreach ($results as $numResult => $result) {
	
			$tesoriere_fattura_importo_e = number_format($result['Order']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			$tot_importo_rimborsate_e = number_format($result['Order']['tot_importo_rimborsate'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			$delta_e =  number_format($result['Order']['delta'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
	 
			echo '<tr>';
			echo '	<td>'.$result['Order']['id'].'<br />'.$result['Order']['delivery_id'].'</td>';
			/*
			echo '	<td>';
			if($result['Delivery']['sys']=='N') 
				echo __('Delivery').': '.$result['Delivery']['luogoData'];
			else 
				echo __('Delivery').': '.h($result['Delivery']['luogo']);
			echo '	</td>';
			*/
			echo '	<td>';
			echo $result['SuppliersOrganization']['name'];
			echo '	</td>';

			echo '<td>';		 
			echo $this->App->drawOrdersStateDiv($result);
			echo '&nbsp;';
			echo __($result['Order']['state_code'].'-label');
			echo '</td>';			

			echo '<td>';		 
			echo $result['Cart']['totImporto_e'];
			echo '</td>';

			echo '<td>';		 
			echo $tesoriere_fattura_importo_e;
			echo '</td>';
			
			echo '<td>';		 
			echo $result['Order']['tot_importo_e'];
			echo '</td>';
			
			echo '<td>';		 
			echo $tot_importo_rimborsate_e;
			echo '</td>';
			
			if($result['Order']['delta'] == 0)
					$style = 'background-color:green;';
				else
					$style = 'background-color:red;';			
			echo '<td style="color:#fff;'.$style.'">';
			echo $delta_e;
			echo '</td>';
			
			if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
				
				if(!empty($result['RequestPayment']))
					$requestPayment = $result['RequestPayment'];
				
				echo '<td>';		 
				if(!empty($requestPayment['RequestPayment']))
					echo 'N. '.$requestPayment['RequestPayment']['num'];
				echo '</td>';	
				
				if(!empty($requestPayment['RequestPayment'])) {
					echo '<td title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPayment['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPayment['RequestPayment']['stato_elaborazione']).'"></td>';					
				}
				else
					echo '<td></td>';	
				
				echo '<td>';		 
				if(!empty($requestPayment['RequestPayment']))
					echo $this->App->formatDateCreatedModifier($requestPayment['RequestPayment']['stato_elaborazione_date']);
				echo '</td>';				
			}
				
		echo '</tr>';
			
		/*
         * SummaryOrder
		 */
		if(!empty($result['SummaryOrder'])) {
			echo '<tr>';
			echo '	<td></td>';
			echo '	<td></td>';
			if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') 
				echo '  <td colspan="10">';
			else
				echo '  <td colspan="7">';
			
			echo '<div class="table-responsive"><table class="table table-hover">';
			echo '<tr>';
			echo '	<th>'.__('Username').'</th>';
			echo '  <th>'.__('Importo').'</th>';
			echo '  <th>'.__('Tesoriere Importo Pay').'</th>';
			echo '  <th>'.__('Payment').'</th>';
			echo '  <th style="width:1px;"></th>';
			echo '</tr>';	
			

			foreach($result['SummaryOrder'] as $summaryOrder) {
				echo '<tr>';
				echo '<td tilte="'.$summaryOrder['User']['id'].'">';		 
				echo $summaryOrder['User']['username'];
				echo '</td>';
				echo '<td>';		 
				echo $summaryOrder['SummaryOrder']['importo_e'];
				echo '</td>';
				echo '<td>';		 
				echo $summaryOrder['SummaryOrder']['importo_pagato_e'];
				echo '</td>';				
				echo '<td>';		 
				echo $this->App->traslateEnum($summaryOrder['SummaryOrder']['modalita']);
				echo '</td>';			
				
				if($summaryOrder['SummaryOrder']['importo'] == $summaryOrder['SummaryOrder']['importo_pagato'])
					$style = 'background-color:green;';
				else
					$style = 'background-color:red;';			
				echo '<td style="color:#fff;'.$style.'"></td>';
				echo '</tr>';	
				
			} // end loop 
			echo '</table></div>';
			
			
			echo '</td>';
			echo '</tr>';
		}  // end if(!empty($result['SummaryOrder'])) 
			
		$tot_tesoriere_fattura_importo += $result['Order']['tesoriere_fattura_importo'];
		$tot_importo += $result['Order']['tot_importo'];
		$tot_importo_rimborsate += $result['Order']['tot_importo_rimborsate'];
		$tot_delta += $result['Order']['delta'];
	}

	$tot_importo_fattura = number_format($tot_importo_fattura,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_rimborsate = number_format($tot_importo_rimborsate,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_delta = number_format($tot_delta,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	echo '<tr>';
	echo '	<th colspan="4" style="text-align:right;">Totali</th>'; 
	echo '	<th>'.$tot_tesoriere_fattura_importo.'&nbsp;&euro;</th>';
	echo '	<th>'.$tot_importo.'&nbsp;&euro;</th>';
	echo '	<th>'.$tot_importo_rimborsate.'&nbsp;&euro;</th>';
	echo '	<th>'.$tot_delta.'&nbsp;&euro;</th>';
	if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') 
		echo '  <th colspan="3"></th>';	
	echo '</tr>';
		
	echo '</table></div>';	
	
	if($result['Delivery']['sys']=='N') 
		$label = __('Statistics').': '.__('Delivery').': '.$result['Delivery']['luogoData'];
	else 
		$label = __('Statistics').': '.__('Delivery').': '.h($result['Delivery']['luogo']);

	echo '<div class="statistic">';
	echo '<h2 class="ico-statistic">';		
	echo '<div class="actions-img">';			
	echo '<ul>';
	echo '<li>'.$this->Html->link($label, array('controller' => 'Statistics', 'action' => 'add', $result['Delivery']['id']),array('class' => 'action actionAdd','title' => __('Statistics'))).'</li>';
	echo '</ul>';
	echo '</div>';
	echo '</div>';
	echo '</h2>';

} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora ordini registrati"));

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
});
</script>