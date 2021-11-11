<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if($isReferenteTesoriere)  {
	$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
}
else {
	if(!isset($delivery_id)) $delivery_id = 0;
		$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
}
$this->Html->addCrumb(__('List Request Payments'), array('controller' => 'RequestPayments', 'action' => 'index'));
$this->Html->addCrumb(__('Add Request Payments Generic'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale form">';

	echo '<h2 class="ico-pay">';
	echo __('Add Request Payments Generic')." alla ".__('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$tot_importo.' &euro; ('.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['created'],"%A %e %B %Y").')';
	echo '<span style="float:right;">';
	echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']).'"></span>';
	echo '</span>';
	echo '</h2>';

	echo $this->Form->create('RequestPaymentsGeneric',array('id' => 'formGas'));
	?>
	<fieldset>

	<div class="requestPayment">
		<?php
		$i=0;
		
		echo $this->App->drawFormRadio('RequestPaymentsGeneric','dest_options_qta',array('options' => $dest_options_qta, 'value'=>'ALL', 'name' => 'dest-options-qta', 'label'=>__('A quali utenti associ la nuova voce di spesa')));
		
		echo '<div id="users" style="display:none;">';
		$label = __('Users').'&nbsp;('.count($users).')';
		echo $this->Form->input('users',array('label' => $label,'options' => $users,'escape' => false,'multiple' => true));
		echo '</div>';
		
		/*
		 * elenco utenti per impostare ad ognuno il suo importo
		 */
		echo '<div id="some_diff" style="display:none;">'; 
		echo '<table>';
		echo '<tr><th>Utente</th><th>Importo</th></tr>';
		
		foreach ($users as $key => $user) {
			echo '<tr>';
			echo '<td>'.$user.'</td>';
			echo '<td style="white-space: nowrap;">';
			echo $this->Form->input('importo_diff',array('name' => 'data[RequestPaymentsGeneric][Importo]['.$key.']', 'label' => false, 'type' => 'text', 'after' => '&nbsp;&euro;','style' => 'display:inline', 'default' => '0,00', 'class' => 'double', 'tabindex'=>($i+1)));
			echo '</td>';
			echo '</tr>';		
		}
		echo '</table>';
		echo '</div>';
		
		echo $this->Form->input('name',array('type' => 'text', 'label' => 'Nota di spesa', 'style' => 'display:inline', 'tabindex'=>($i+1)));

		echo '<div id="importo_singolo" style="display:none;white-space: nowrap;">';
		echo $this->Form->input('importo',array('label' => 'Importo', 'type' => 'text', 'after' => '&nbsp;&euro;', 'style' => 'display:inline', 'class' => 'double', 'tabindex'=>($i+1)));
		echo '</div>';
		
		echo $this->element('legendaRequestPaymentsGeneric');
		?>
	</div>

	</fieldset>
	<?php 
	echo $this->Form->hidden('request_payment_id',array('value' => $requestPaymentResults['RequestPayment']['id']));
	echo $this->Form->end(__('Add Request Payments Generic')." alla richiesta numero ".$requestPaymentResults['RequestPayment']['num']);

echo '</div>'; // end contentMenuLaterale

$options = [];
echo $this->MenuRequestPayment->drawWrapper($requestPaymentResults['RequestPayment']['id'], $options);
?>

<script type="text/javascript">
$(document).ready(function() {

	$('.double').focusout(function() {validateNumberField(this,'importo');});
	
	$("input[name='data[RequestPaymentsGeneric][dest_options_qta]']").change(function() {
		choiceDestOptions();
	});
	
	choiceDestOptions();

	$('#formGas').submit(function() {

		var dest_options_qta = $("input[name='data[RequestPaymentsGeneric][dest_options_qta]']:checked").val();
		if(dest_options_qta=='SOME') {
			destinatariScelti = $("#RequestPaymentsGenericUsers").val();

			if(destinatariScelti==null) {
				alert("Devi scegliere almeno un destinatario");
				return false;
			}
		}

		var name = $('#RequestPaymentsGenericName').val();
		if(name=="") {
			alert("Devi indicare la motivazione della voce di spesa");
			$('#RequestPaymentsGenericName').focus();
			return false;
		}

		if(dest_options_qta!='SOME_DIFF') {
			var importo = $('#RequestPaymentsGenericImporto').val();
			if(importo=='' || importo==null || importo=='0,00' || importo=='0.00' || importo=='0') {
				alert("Devi indicare l'importo");
				return false;
			}
		}
				
		return true;
	});	
});

function choiceDestOptions() {
	var dest_options_qta = $("input[name='data[RequestPaymentsGeneric][dest_options_qta]']:checked").val();

	if(dest_options_qta=='ALL') {
		$('#users').hide();
		$('#some_diff').hide();
		$('#importo_singolo').css('display','block');
	}
	else 
	if(dest_options_qta=='SOME') {
		$('#users').show();
		$('#some_diff').hide();
		$('#importo_singolo').css('display','block');
	}
	else 
	if(dest_options_qta=='SOME_DIFF') 	 {	
		$('#users').hide();
		$('#some_diff').show();
		$('#importo_singolo').css('display','none');
	}
}
</script>