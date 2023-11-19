<?php 
echo '<label for="order_id">Ordini</label> ';
echo '<div>';

if (!empty($results)):

	/*
	 * legenda con gli Order.OrderTesoriereStatoPay
	 */
	echo '<div style="float:right;">';
	echo '	<a id="legendaOrderState" href="#" title="'.__('Href_title_expand').'"><img class="img-responsive-disabled" src="/images/cake/actions/32x32/viewmag+.png" /> Visualizza/Nascondi gli ordini</a>';
	echo '<div id="legendaOrderStateContent" class="legenda">';
	echo '<div id="box-account-close"></div>';
	foreach ($orderTesoriereStatoPayResults as $key => $label) {
	
		echo '<div class="checkbox"><label>';
		echo '<span style="float: right;" class="action orderStato'.$key.' ';
		
		if($key=='Y') echo 'orderStatoCLOSE';
		else
		if($key=='N') echo 'orderStatoOPEN';
		
		echo '" title="'.$label.'"></span>';
		echo ' ';
		echo '<input style="clear: none;float: none;" type="checkbox" name="order_tesoriere_stato_pay_selected" value="'.$key.'" checked="checked" />';
		echo '&nbsp;';		
		echo $label;
		echo '</label></div>';
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


	<?php

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th>'.__('StatoElaborazione').'</th>';
	echo '<th>'.__('Delivery').'</th>';
	echo '<th>'.__('Importo totale ordine').'</th>';
	echo '<th></th>';
	echo '<th colspan="2">'.__('Tesoriere fattura importo').'</th>';
	echo '<th style="min-width:100px;">'.__('Tesoriere Importo Pay').'</th>';
	echo '<th style="min-width:200px;">'.__('Tesoriere Data Pay').'</th>';
	echo '<th>'.__('Saldato').'</th>';
	echo '</tr>';
	
	foreach ($results as $numResult => $result) {
		
		if($result['Order']['tesoriere_stato_pay']=='N') 
			echo '<tr class="OrderTesoriereStatoPay'.$result['Order']['tesoriere_stato_pay'].'">';
		else
			echo '<tr class="OrderTesoriereStatoPay'.$result['Order']['tesoriere_stato_pay'].'">';

			echo '<td>';
			echo '<a action="orders_tesoriere-'.$result['Order']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
			echo '</td>';
			echo '<td>';
			echo __($result['Order']['state_code'].'-label');
			echo '&nbsp;';
			echo $this->App->drawOrdersStateDiv($result);
			echo '</td>';
						
			echo '<td>';
			echo $result['Delivery']['luogoData'];
			echo '</td>';

			/*
			echo '	<td style="white-space:nowrap;">';
			echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y").'<br />';
			echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");	
			echo '</td>';
			*/
			
			echo '<td>';
			echo number_format($result['Order']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			echo '</td>';
			echo '<td>';
			if(!empty($result['Order']['tesoriere_doc1']) && file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$user->organization['Organization']['id'].DS.$result['Order']['tesoriere_doc1'])) {
				$ico = $this->App->drawDocumentIco($result['Order']['tesoriere_doc1']);
				echo '<a alt="Scarica il documento" title="Scarica il documento" href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.tesoriere').'/'.$user->organization['Organization']['id'].'/'.$result['Order']['tesoriere_doc1'].'" target="_blank"><img src="'.$ico.'" /></a>';
			}
			echo '</td>';			
			echo '<td>';
			echo $result['Order']['tesoriere_fattura_importo_e'];
			echo '</td>';

			echo '<td>';
			echo $this->Html->link(null, [] ,['class' => 'action actionCopy', 'title' => __('Copy'), 'id' => $result['Order']['id'], 'importo' => number_format($result['Order']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'))]);
			echo '</td>';

			echo '<td>';
			echo '<input class="form-control double" type="text" name="data[Order]['.$result['Order']['id'].'][tesoriere_importo_pay]" value="'.$result['Order']['tesoriere_importo_pay_'].'" />';
			echo '</td>';
			
			echo '<td>';
			if($result['Order']['tesoriere_data_pay']==Configure::read('DB.field.date.empty'))
				$tesoriere_data_pay = '';
			else
				$tesoriere_data_pay = $this->Time->i18nFormat($result['Order']['tesoriere_data_pay'],"%A, %e %B %Y");
							
			echo $this->Form->input('tesoriere_data_pay',array('type' => 'text','size'=>'20','label' => false,  'value' => $tesoriere_data_pay, 'name' => 'data[Order]['.$result['Order']['id'].'][tesoriere_data_pay]', 'id' => 'OrderTesoriereDataPay'.$result['Order']['id'], 'required' => 'false'));
			echo $this->Ajax->datepicker('OrderTesoriereDataPay'.$result['Order']['id'],array('dateFormat' => 'DD, d MM yy','altField' => '#OrderTesoriereDataPayDb'.$result['Order']['id'], 'altFormat' => 'yy-mm-dd'));
			echo '<input type="hidden" id="OrderTesoriereDataPayDb'.$result['Order']['id'].'" name="data[Order]['.$result['Order']['id'].'][tesoriere_data_pay_db]" value="'.$result['Order']['tesoriere_data_pay'].'" />';
				
			echo '</td>';
			echo '<td>';
			echo '<input type="checkbox" class="form-control" name="data[Order]['.$result['Order']['id'].'][tesoriere_stato_pay]" value="Y" ';
			if($result['Order']['tesoriere_stato_pay']=='Y')
				echo 'checked=checked '; // non + disabled se no non passa il valore
			echo '/>';
			
			/*
			 * campi hidden per confronto se cambiato per update
			 */
			echo $this->Form->hidden('tesoriere_importo_pay_old', ['name' => 'data[Order]['.$result['id'].'][tesoriere_importo_pay_old]', 'value' => $result['tesoriere_importo_pay']]);
			echo $this->Form->hidden('tesoriere_data_pay_old', ['name' => 'data[Order]['.$result['id'].'][tesoriere_data_pay_old]', 'value' => $result['tesoriere_data_pay']]);
			echo $this->Form->hidden('tesoriere_stato_pay_old', ['name' => 'data[Order]['.$result['id'].'][tesoriere_stato_pay_old]', 'value' => $result['tesoriere_stato_pay']]);
			 
			echo '</td>';
			echo '</tr>';

			echo '<tr class="trView" id="trViewId-'.$result['Order']['id'].'">';
			echo '	<td></td>'; 
			echo '	<td colspan="8" id="tdViewId-'.$result['Order']['id'].'"></td>';
			echo '</tr>';
		
	} // end foreach

	echo '</table></div>';
else: 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ordini associati"));
endif; 

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {

	$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
	<?php if (!empty($results)) { ?>
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
	
	$('.actionCopy').click(function() {

		var id = $(this).attr('id');
		var importo = $(this).attr('importo');
		
		if(!$("input[name='data[Order]["+id+"][tesoriere_stato_pay]']").prop('checked')) {
			$("input[name='data[Order]["+id+"][tesoriere_importo_pay]']").val(importo);
			$("input[name='data[Order]["+id+"][tesoriere_stato_pay]']").prop('checked', true);
		}
		else {
			$("input[name='data[Order]["+id+"][tesoriere_importo_pay]']").val("0,00");
			$("input[name='data[Order]["+id+"][tesoriere_stato_pay]']").prop('checked', false);			
		}
				
		return false;
	});
});
</script>