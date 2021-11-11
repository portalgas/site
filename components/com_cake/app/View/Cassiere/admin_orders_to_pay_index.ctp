<?php 
echo '<label for="order_id">Ordini</label> ';
echo '<div>';

if (!empty($results['Order'])):

	/*
	 * legenda con gli Order.OrderTesoriereStatoPay
	 */
	echo '<div style="float:right;">';
	echo '	<a id="legendaOrderState" href="#" title="'.__('Href_title_expand').'"><img src="/images/cake/actions/32x32/viewmag+.png" /> Visualizza/Nascondi gli ordini</a>';
	echo '<div id="legendaOrderStateContent" class="legenda">';
	echo '<div id="box-account-close"></div>';
	foreach ($orderTesoriereStatoPayResults as $key => $label) {
	
		echo '<div>';
		echo '<span class="action orderStato'.$key.' ';
		
		if($key=='Y') echo 'orderStatoCLOSE';
		else
		if($key=='N') echo 'orderStatoOPEN';
		
		echo '" title="'.$label.'"></span>';
		echo '&nbsp;';
		echo '<input style="clear: none;float: none;" type="checkbox" name="order_tesoriere_stato_pay_selected" value="'.$key.'" checked="checked" />';
		echo '&nbsp;';		
		echo $label;
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
?>
<style>
#legendaOrderStateContent {
	display:none;
	z-index:15;
	width:300px;
	position:fixed;
	right:50px;
	background-color: #fff;
}
#box-account-close {
   background: url("/images/cake/close-popup-red.png") no-repeat scroll 0 0 rgba(0, 0, 0, 0);
    cursor: pointer;
    float: right;
    height: 32px;
    position: absolute;
    right: -15px;
    top: -15px;
    width: 32px;	
}
</style>

	<div class="table-responsive"><table class="table table-hover">
	<tr>
		<th></th>
		<th><?php echo __('StatoElaborazione'); ?></th>
		<th colspan="2"><?php echo __('Supplier'); ?></th>
		<th><?php echo __('Tesoriere Importo Pay'); ?></th>
		<th><?php echo __('Tesoriere Data Pay'); ?></th>
		<th><?php echo __('Saldato'); ?></th>
	</tr>
	<?php
		foreach ($results['Order'] as $numResult => $order):
		
		if($order['tesoriere_stato_pay']=='N') 
			echo '<tr class="OrderTesoriereStatoPay'.$order['tesoriere_stato_pay'].'">';
		else
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
				echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$order['Supplier']['img1'].'" alt="'.$order['SupplierOrganization']['name'].'" /> ';
			echo '</td>';
			echo '<td>'.$order['SuppliersOrganization']['name'].'</td>';

			echo '<td>';
			echo '<input class="double" type="text" name="data[Order]['.$order['id'].'][tesoriere_importo_pay]" value="'.$order['tesoriere_importo_pay_'].'" />';
			echo '</td>';
			
			echo '<td>';
			if($order['tesoriere_data_pay']==Configure::read('DB.field.date.empty'))
				$tesoriere_data_pay = '';
			else
				$tesoriere_data_pay = $this->Time->i18nFormat($order['tesoriere_data_pay'],"%A, %e %B %Y");
							
			echo $this->Form->input('tesoriere_data_pay',array('type' => 'text','size'=>'25','label' => false, 'value' => $tesoriere_data_pay, 'name' => 'data[Order]['.$order['id'].'][tesoriere_data_pay]', 'id' => 'OrderTesoriereDataPay'.$order['id'], 'required' => 'false'));
			echo $this->Ajax->datepicker('OrderTesoriereDataPay'.$order['id'],array('dateFormat' => 'DD, d MM yy','altField' => '#OrderTesoriereDataPayDb'.$order['id'], 'altFormat' => 'yy-mm-dd'));
			echo '<input type="hidden" id="OrderTesoriereDataPayDb'.$order['id'].'" name="data[Order]['.$order['id'].'][tesoriere_data_pay_db]" value="'.$order['tesoriere_data_pay'].'" />';
				
			echo '</td>';
			echo '<td>';
			echo '<input type="checkbox" name="data[Order]['.$order['id'].'][tesoriere_stato_pay]" value="Y" ';
			if($order['tesoriere_stato_pay']=='Y')
				echo 'checked=checked ';
			echo '/>';
			
			/*
			 * campi hidden per confronto se cambiato per update
			 */
			 echo $this->Form->hidden('tesoriere_importo_pay_old',array('name' => 'data[Order]['.$order['id'].'][tesoriere_importo_pay_old]', 'value' => $order['tesoriere_importo_pay']));
			 echo $this->Form->hidden('tesoriere_data_pay_old',array('name' => 'data[Order]['.$order['id'].'][tesoriere_data_pay_old]', 'value' => $order['tesoriere_data_pay']));
			 echo $this->Form->hidden('tesoriere_stato_pay_old',array('name' => 'data[Order]['.$order['id'].'][tesoriere_stato_pay_old]', 'value' => $order['tesoriere_stato_pay']));
			 
			echo '</td>';
			echo '</tr>';
				
			echo '<tr class="trView" id="trViewId-'.$order['id'].'">';
			echo '	<td></td>'; 
			echo '	<td colspan="7" id="tdViewId-'.$order['id'].'"></td>';
			echo '</tr>';					
	endforeach;

	echo '</table></div>';
else: 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ordini associati"));
endif; 
?>
<script type="text/javascript">
$(document).ready(function() {

	$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
	<?php if (!empty($results['Order'])) { ?>
		$('.submit').css('display','block');
	<?php
	} else { ?>
		$('.submit').css('display','none');
	<?php
	}
	?>	

	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	$('#box-account-close').click(function() {
		if($('#legendaOrderStateContent').css('display')=='block')  {
			$('#legendaOrderStateContent').hide();
		}
		else 
			$('#legendaOrderStateContent').show();

		return false;
	});

	$('#legendaOrderState').click(function() {
		if($('#legendaOrderStateContent').css('display')=='block')  {
			$('#legendaOrderStateContent').hide();
		}
		else 
			$('#legendaOrderStateContent').show();

		return false;
	});
	
	$("input[name='order_tesoriere_stato_pay_selected']").click(function() {
		var order_tesoriere_stato_pay = $(this).val();
		if($(this).is(':checked')) 
			$('.OrderTesoriereStatoPay'+order_tesoriere_stato_pay).css('display','table-row');
		else
			$('.OrderTesoriereStatoPay'+order_tesoriere_stato_pay).css('display','none');
	});	
});
</script>
</div>