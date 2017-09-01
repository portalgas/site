<?php 
echo '<label for="order_id">Ordini</label>';
echo '<div>';

if (!empty($results['Order'])):
?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th></th>
		<th><?php echo __('stato_elaborazione'); ?></th>
		<th colspan="2"><?php echo __('Supplier'); ?></th>
		<th><?php echo __('Importo totale ordine'); ?></th>
		<th><?php echo __('Tesoriere fattura importo'); ?></th>
		<th><?php echo __('Tesoriere Importo Pay'); ?></th>
		<th><?php echo __('Tesoriere Data Pay'); ?></th>
	</tr>
	<?php
		foreach ($results['Order'] as $numResult => $order):
		
			echo '<tr class="OrderTesoriereStatoPay'.$order['tesoriere_stato_pay'].'">';

			echo '<td>';
			echo '<a action="orders_tesoriere-'.$order['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
			echo '</td>';
			echo '<td>';
			echo __($order['state_code'].'-label');
			echo '&nbsp;';
			echo $this->App->drawOrdersStateDiv($order);
			echo '</td>';
			echo '<td>';
			if(!empty($order['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$order['Supplier']['img1']))
				echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$order['Supplier']['img1'].'" alt="'.$order['SupplierOrganization']['name'].'" /> ';
			echo '</td>';
			echo '<td>'.$order['SuppliersOrganization']['name'].'</td>';
			echo '<td>';
			echo number_format($order['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
			echo '</td>';
			echo '<td>';
			echo number_format($order['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
			echo '</td>';

			echo '<td>';
			echo $order['tesoriere_importo_pay_e'];
			echo '</td>';
			
			echo '<td>';
			if($order['tesoriere_data_pay']=='000-00-00')
				$tesoriere_data_pay = '';
			else
				$tesoriere_data_pay = $this->Time->i18nFormat($order['tesoriere_data_pay'],"%A, %e %B %Y");
							
			echo $tesoriere_data_pay;
				
			echo '</td>';
			echo '</tr>';
			
			echo '<tr class="trView" id="trViewId-'.$order['id'].'">';
			echo '	<td></td>'; 
			echo '	<td colspan="7" id="tdViewId-'.$order['id'].'"></td>';
			echo '</tr>';
		
	endforeach;

	echo '</table>';
else: 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ordini associati"));
endif; 
?>
<script type="text/javascript">
$(document).ready(function() {

	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});
	
});
</script>
</div>