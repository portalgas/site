<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));
$this->Html->addCrumb(__('ValidationCartRiOpen'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo '<table cellpadding = "0" cellspacing = "0">';
echo '<tr>';
echo '	<th>'.$this->App->drawOrdersStateDiv($this->request->data).'&nbsp;'.__($this->request->data['Order']['state_code'].'-label').'</th>';
echo '</tr>';
echo '</table>';

echo $this->Form->create('Order',array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('ValidationCartRiOpen').'</legend>';

		echo $this->Form->input('id');
		echo '<div class="input text required">';
		echo '<label for="OrderSuppliersOrganizationId">'.__('SuppliersOrganization').'</label> ';
		echo $this->Form->value('SuppliersOrganization.name');
		echo '</div>';
		
		echo '<div class="input text required">';
		echo '<label for="OrderDeliveryId">'.__('Delivery').'</label> ';
		if($this->request->data['Delivery']['sys']=='N')
			echo $this->Form->value('Delivery.luogoData');
		else 
			echo $this->Form->value('Delivery.luogo');
		echo '</div>';
		echo '<input type="hidden" id="DeliveryDataDb" name="data[Delivery][data_db]" value="'.$this->Form->value('Delivery.data').'" />';
		
		echo '<div class="input text required">';
		echo '<label for="OrderDataInizio">'.__('DataInizio').'</label> ';
		echo $this->Time->i18nFormat($this->Form->value('Order.data_inizio'),"%A, %e %B %Y");
		echo '</div>';
		
		echo '<div class="input text required">';
		echo '<label for="OrderDataFine">'.__('DataFine').'</label> ';
		echo $this->Time->i18nFormat($this->Form->value('Order.data_fine'),"%A, %e %B %Y");
		echo '</div>';
		echo '<input type="hidden" id="OrderDataFineDb" name="data[Order][data_fine_db]" value="'.$this->Form->value('Order.data_fine').'" />';
		
		if($data_fine_validation==Configure::read('DB.field.date.empty'))
			$value = '';
		else
			$value = $this->Time->i18nFormat($data_fine_validation,"%A, %e %B %Y");
		echo $this->Form->input('data_fine_validation',array('type' => 'text','size'=>'30','label' => __('DataFineValidation'), 'value' => $value));
		echo $this->Ajax->datepicker('OrderDataFineValidation',array('dateFormat' => 'DD, d MM yy','altField' => '#OrderDataFineValidationDb', 'altFormat' => 'yy-mm-dd'));
		echo '<input type="hidden" id="OrderDataFineValidationDb" name="data[Order][data_fine_validation_db]" value="'.$data_fine_validation_db.'" />';

		echo $this->App->drawFormRadio('Order','invio_mail',array('options' => $invio_mail, 'value' => $invio_mail_default, 'name' => 'invio_mail', 'label'=>__('Invio la mail agli utenti')));
		
		echo '<div id="box_mail_open_testo" ';
		if($invio_mail_default=='N') echo 'style="display:none;"';
		echo '>';
			echo $this->Form->input('name',array('label' => 'Intestazione', 'value' => str_replace('<br />', '', $body_header), 'disabled' => 'true'));
			echo $this->Form->input('mail_open_testo', array('value' => $testo_mail));
			// echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer_no_reply" style="display:inline;">'.str_replace('<br />', '', $body_footer_no_reply).'</textarea>';
		echo '</div>';
		
	echo '</fieldset>';

echo $this->Form->end(__('Submit'));
echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($this->Form->value('Order.id'), $options);
?>
<script type="text/javascript">
$(document).ready(function() {

	$("input[name='data[Order][invio_mail]']").change(function() {	
		var invio_mail = $("input[name='data[Order][invio_mail]']:checked").val();
		if(invio_mail=='N')
			$('#box_mail_open_testo').hide();
		else
			$('#box_mail_open_testo').show();
	});
			
	$('#formGas').submit(function() {

		var deliveryDataDb = $('#DeliveryDataDb').val();
		if(deliveryDataDb=='' || deliveryDataDb==undefined || deliveryDataDb=='<?php echo Configure::read('DB.field.date.empty');?>')  {
	 		alert("Non è indicata la data di chiusura della consegna");
 			return false;
		}
		
		var orderDataFineDb = $('#OrderDataFineDb').val();
		if(orderDataFineDb=='' || orderDataFineDb==undefined || orderDataFineDb=='<?php echo Configure::read('DB.field.date.empty');?>')  {
	 		alert("Non è indicata la data di chiusura dell'ordine");
 			return false;
		}

		var orderDataFineValidationDb = $('#OrderDataFineValidationDb').val();
 		if(orderDataFineValidationDb=='' || orderDataFineValidationDb==undefined || orderDataFineValidationDb=='<?php echo Configure::read('DB.field.date.empty');?>')  {
 	 		alert("Devi indicare nuova la data di chiusura dell'ordine");
	 		return false;
		}

		var resultCompare = compare_date(orderDataFineValidationDb, deliveryDataDb);
		if(resultCompare==">") {
 	 		alert("La nuova data di chiusura dell'ordine non può essere posteriore alla data di chiusura della consegna");
	 		return false;
		}
		else
		if(resultCompare=="=") {
 	 		alert("La nuova data di chiusura dell'ordine non può essere uguale alla data di chiusura della consegna");
	 		return false;
		}	

		resultCompare = compare_date(orderDataFineValidationDb, orderDataFineDb);
		if(resultCompare=="<") {
 	 		alert("La nuova data di chiusura dell'ordine non può essere antecedente alla data di chiusura dell'ordine");
	 		return false;
		}
		else
		if(resultCompare=="=") {
 	 		alert("La nuova data di chiusura dell'ordine non può essere uguale alla data di chiusura dell'ordine");
	 		return false;
		}	

		resultCompare = compare_date_today(orderDataFineValidationDb);
		if(resultCompare=="<") {
 	 		alert("La nuova data di chiusura dell'ordine non può essere antecedente alla data odierna");
	 		return false;
		}
		
		return true;
	});
});
</script>

<style type="text/css">
.cakeContainer div.form, .cakeContainer div.index, .cakeContainer div.view {
    width: 74%;
}
.cakeContainer div.actions {
    width: 25%;
}
</style>