<?php
if(!empty($results)) {

	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '	<th></th>';
	echo '	<th>'.__('N').'</th>';
	echo '	<th>'.__('Delivery').'</th>';
	echo '	<th>'.__('SuppliersOrganization').'</th>';
	echo '  <th>'.__('stato_elaborazione').'</th>';
	echo '  <th>Importo fattura</th>';
	echo '  <th>Totale importo dell\'ordine</th>';
	echo '  <th>Totale importo dovuto<br />(somma degli importi degli utenti)</th>';
	echo '  <th>Differenza</th>';
	echo '	<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';
	
	$tot_tesoriere_fattura_importo = 0;
	$tot_importo = 0;
	$tot_importo_rimborsate = 0;
	$tot_delta = 0;
	foreach ($results as $numResult => $result) {
	
			$tesoriere_fattura_importo_e = number_format($result['Order']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
			$tot_importo_rimborsate_e = number_format($result['Order']['tot_importo_rimborsate'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
			$delta_e =  number_format($result['Order']['delta'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
	 
			echo '<tr class="view">';
			echo '	<td><a action="orders-'.$result['Order']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
			echo '	<td>'.($numResult+1).'</td>';
			
			echo '	<td>';
			if($result['Delivery']['sys']=='N') 
				echo __('Delivery').': '.$result['Delivery']['luogoData'];
			else 
				echo __('Delivery').': '.h($result['Delivery']['luogo']);
			echo '	</td>';
			
			echo '	<td>';
			echo $result['SuppliersOrganization']['name'];
			echo '	</td>';

			echo '<td>';		 
			echo $this->App->drawOrdersStateDiv($result);
			echo '&nbsp;';
			echo __($result['Order']['state_code'].'-label');
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
			
			echo '<td class="actions-table-img-3">';

			if($isManager) {
				echo $this->Html->link(null, array('action' => 'home', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionWorkflow','title' => __('Order home')));

				echo '<a id="actionMenu-'.$result['Order']['id'].'" class="action actionMenu" title="'.__('Expand menu').'"></a>';
				echo '<div class="menuDetails" id="menuDetails-'.$result['Order']['id'].'" style="display:none;">';
				echo '	<a class="menuDetailsClose" id="menuDetailsClose-'.$result['Order']['id'].'"></a>';
				echo '<div id="order-sotto-menu-'.$result['Order']['id'].'"></div>';
				echo '</div>';
			}
			else 
			if($isRoot) 
				echo "Nessuna azione perchè root";
		
			echo '</td>';
	
		echo '</tr>';
		
		echo '<tr class="trView" id="trViewId-'.$result['Order']['id'].'">';
		echo '	<td colspan="2"></td>'; 
		echo '	<td colspan="9" id="tdViewId-'.$result['Order']['id'].'"></td>';
		echo '</tr>';
		
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
	echo '	<td colspan="5" style="text-align:right;">Totali</td>'; 
	echo '	<td>'.$tot_tesoriere_fattura_importo.' &euro;</td>';
	echo '	<td>'.$tot_importo.' &euro;</td>';
	echo '	<td>'.$tot_importo_rimborsate.' &euro;</td>';
	echo '	<td>'.$tot_delta.' &euro;</td>';
	echo '	<td></td>'; 
	echo '</tr>';
		
	echo '</table>';	
} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFonud', 'msg' => "Non ci sono ancora ordini registrati"));

echo '</div>';
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".actionMenu").each(function () {
		jQuery(this).click(function() {

			jQuery('.menuDetails').css('display','none');
			
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).show();

			viewOrderSottoMenu(numRow,"bgLeft");

			var offset = jQuery(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			jQuery('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	jQuery(".menuDetailsClose").each(function () {
		jQuery(this).click(function() {
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).hide('slow');
		});
	});	
});
</script>