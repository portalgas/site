<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
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
?>
<div class="RequestPaymentsGeneric form">

	<h2 class="ico-pay">
		<?php echo __('Add Request Payments Generic')." alla richiesta numero ".$requestPaymentResults['RequestPayment']['num'];?>
	</h2>

	<?php	
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
			echo '<td>';
			echo $this->Form->input('importo_diff',array('name' => 'data[RequestPaymentsGeneric][Importo]['.$key.']', 'label' => false, 'type' => 'text', 'after' => '&nbsp;&euro;', 'size' => '15', 'default' => '0,00', 'class' => 'noWidth double', 'tabindex'=>($i+1)));
			echo '</td>';
			echo '</tr>';		
		}
		echo '</table>';
		echo '</div>';
		
		echo $this->Form->input('name',array('type' => 'text', 'label' => 'Nota di spesa', 'tabindex'=>($i+1)));

		echo '<div id="importo_singolo" style="display:none;">';
		echo $this->Form->input('importo',array('label' => 'Importo', 'type' => 'text', 'after' => '&nbsp;&euro;', 'size' => '15', 'class' => 'noWidth double', 'tabindex'=>($i+1)));
		echo '</div>';
		
		echo $this->element('legendaRequestPaymentsGeneric');
		?>
	</div>

	</fieldset>
	<?php 
	echo $this->Form->hidden('request_payment_id',array('value' => $requestPaymentResults['RequestPayment']['id']));
	echo $this->Form->end(__('Add Request Payments Generic')." alla richiesta numero ".$requestPaymentResults['RequestPayment']['num']);
	?>
</div>

<div class="actions">
	<?php include(Configure::read('App.root').Configure::read('App.component.base').'/View/RequestPayments/admin_sotto_menu.ctp');?>		
</div>

<script type="text/javascript">
<?php
if($isReferenteTesoriere) 
	echo 'viewReferenteTesoriereSottoMenu("0", "bgLeft");';
else
	echo 'viewTesoriereSottoMenu("0", "bgLeft");';
?>
</script>


<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.double').focusout(function() {validateNumberField(this,'importo');});
	
	jQuery("input[name='data[RequestPaymentsGeneric][dest_options_qta]']").change(function() {
		choiceDestOptions();
	});
	
	choiceDestOptions();

	jQuery('#formGas').submit(function() {

		var dest_options_qta = jQuery("input[name='data[RequestPaymentsGeneric][dest_options_qta]']:checked").val();
		if(dest_options_qta=='SOME') {
			destinatariScelti = jQuery("#RequestPaymentsGenericUsers").val();

			if(destinatariScelti==null) {
				alert("Devi scegliere almeno un destinatario");
				return false;
			}
		}

		var name = jQuery('#RequestPaymentsGenericName').val();
		if(name=="") {
			alert("Devi indicare la motivazione della voce di spesa");
			jQuery('#RequestPaymentsGenericName').focus();
			return false;
		}

		if(dest_options_qta!='SOME_DIFF') {
			var importo = jQuery('#RequestPaymentsGenericImporto').val();
			if(importo=='' || importo==null || importo=='0,00' || importo=='0.00' || importo=='0') {
				alert("Devi indicare l'importo");
				return false;
			}
		}
				
		return true;
	});	
});

function choiceDestOptions() {
	var dest_options_qta = jQuery("input[name='data[RequestPaymentsGeneric][dest_options_qta]']:checked").val();

	if(dest_options_qta=='ALL') {
		jQuery('#users').hide();
		jQuery('#some_diff').hide();
		jQuery('#importo_singolo').css('display','block');
	}
	else 
	if(dest_options_qta=='SOME') {
		jQuery('#users').show();
		jQuery('#some_diff').hide();
		jQuery('#importo_singolo').css('display','block');
	}
	else 
	if(dest_options_qta=='SOME_DIFF') 	 {	
		jQuery('#users').hide();
		jQuery('#some_diff').show();
		jQuery('#importo_singolo').css('display','none');
	}
}
</script>

<style type="text/css">
.cakeContainer div.form, .cakeContainer div.index, .cakeContainer div.view {
    width: 74%;
    padding-left: 5px;    
}
.cakeContainer div.actions {
    width: 25%;
}
</style>