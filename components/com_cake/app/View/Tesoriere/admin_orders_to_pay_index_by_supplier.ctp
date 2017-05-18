<?php 
echo '<label for="order_id">Ordini</label>';
echo '<div>';

if (!empty($results)):
?>

	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th></th>
		<th><?php echo __('Delivery'); ?></th>
		<th><?php echo __('Order'); ?></th>
		<th><?php echo __('Importo totale ordine'); ?></th>
		<th><?php echo __('Tesoriere fattura importo'); ?></th>
		<th><?php echo __('Tesoriere Importo Pay'); ?></th>
		<th><?php echo __('Tesoriere Data Pay'); ?></th>
	</tr>
	<?php
		foreach ($results as $numResult => $result):
		
		if($result['Order']['tesoriere_stato_pay']=='N') 
			echo '<tr class="OrderTesoriereStatoPay'.$result['Order']['tesoriere_stato_pay'].'">';
		else
			echo '<tr class="OrderTesoriereStatoPay'.$result['Order']['tesoriere_stato_pay'].'">';

			echo '<td>';
			echo '<a action="orders_tesoriere-'.$result['Order']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
			echo '</td>';
			echo '<td>';
			echo $result['Delivery']['luogoData'];
			echo '</td>';

			echo '<td>';
			echo __($result['Order']['state_code'].'-label');
			echo '&nbsp;';
			echo $this->App->drawOrdersStateDiv($result['Order']);
			echo '</td>';
			/*
			echo '	<td style="white-space:nowrap;">';
			echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y").'<br />';
			echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");	
			echo '</td>';
			*/
			echo '<td>';
			echo number_format($result['Order']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
			echo '</td>';
			echo '<td>';
			echo number_format($result['Order']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
			echo '</td>';

			echo '<td>';
			echo $result['Order']['tesoriere_importo_pay_e'];
			echo '</td>';
			
			echo '<td>';
			if($result['Order']['tesoriere_data_pay']=='000-00-00')
				$tesoriere_data_pay = '';
			else
				$tesoriere_data_pay = $this->Time->i18nFormat($result['Order']['tesoriere_data_pay'],"%A, %e %B %Y");
							
			echo $tesoriere_data_pay;
				
			echo '</td>';
			echo '</tr>';
			
			echo '<tr class="trView" id="trViewId-'.$result['Order']['id'].'">';
			echo '	<td></td>'; 
			echo '	<td colspan="7" id="tdViewId-'.$result['Order']['id'].'"></td>';
			echo '</tr>';
		
	endforeach;

	echo '</table>';
else: 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ordini associati"));
endif; 
?>
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery('.actionTrView').each(function () {
		actionTrView(this);
	});
});
</script>
</div>