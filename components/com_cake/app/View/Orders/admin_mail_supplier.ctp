<?php
if(isset($results['Order']) && $results['Order']['order_type_id']==Configure::read('Order.type.gas_groups')) {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'), Configure::read('Neo.portalgas.url').'admin/orders/index/'.$results['Order']['order_type_id']);
	$this->Html->addCrumb(__('Order home'), Configure::read('Neo.portalgas.url').'admin/orders/home/'.$results['Order']['order_type_id'].'/'.$results['Order']['id']);
	$this->Html->addCrumb(__('OrderMailSupplier'));
}
else {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));
	$this->Html->addCrumb(__('OrderMailSupplier'));	
}
echo $this->Html->getCrumbList(['class'=>'crumbs']);

echo '<div class="contentMenuLaterale">';

echo '<table cellpadding = "0" cellspacing = "0">';
echo '<tr>';
echo '	<th>'.$this->App->drawOrdersStateDiv($this->request->data).'&nbsp;'.__($this->request->data['Order']['state_code'].'-label').'</th>';
echo '</tr>';
echo '</table>';

echo $this->Form->create('Order',array('id'=>'formGas','enctype' => 'multipart/form-data'));

echo '<fieldset>';
echo '<legend>'.__('OrderMailSupplier').'</legend>';

		echo $this->Form->input('id');
		echo '<div class="input text">';
		echo '<label for="OrderSuppliersOrganizationId">'.__('SuppliersOrganization').'</label> ';
		if(!empty($supplierResults['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$supplierResults['Supplier']['img1']))
			echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$supplierResults['Supplier']['img1'].'" alt="'.$supplierResults['SupplierOrganization']['name'].'" /> ';
		
		echo $this->Form->value('SuppliersOrganization.name');
		echo '</div>';

		echo '<div class="input text">';
		echo '<label for="OrderDeliveryId">'.__('Email').'</label> ';
		echo '<a href="mailto:'.$supplierResults['Supplier']['mail'].'" target="_blank">'.$supplierResults['Supplier']['mail'].'</a>';
		echo '</div>';
		
		echo '<div class="input text">';
		echo '<label for="OrderDeliveryId">'.__('Contatti').'</label> ';
		if(!empty($supplierResults['Supplier']['indirizzo'])) echo $supplierResults['Supplier']['indirizzo'];
		if(!empty($supplierResults['Supplier']['localita'])) echo ", ".$supplierResults['Supplier']['localita'];
		if(!empty($supplierResults['Supplier']['telefono2'])) echo " - ".$supplierResults['Supplier']['telefono2'];
		if(!empty($supplierResults['Supplier']['telefono2'])) echo " - ".$supplierResults['Supplier']['telefono2'];
		echo '</div>';
		
		echo $this->Form->input('subject');
		
		echo $this->Form->input('intestazione',array('label' => 'Intestazione', 'value' => str_replace('<br />', '', $body_header), 'disabled' => 'false'));
		echo '<div class="clearfix"></div>';
		echo $this->Form->input('mail_open_testo', array('label' => "Testo della mail", 'value' => $testo_mail));
		// echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer_no_reply" style="display:inline;">'.str_replace('<br />', '', $body_footer_no_reply).'</textarea>';
		
		echo '<div class="clearfix"></div>';
		echo '<div class="input text"><label>Piè di pagina</label> ';
		echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer">'.str_replace('<br />', '', $body_footer).'</textarea>';
		
		echo '<div class="clearfix"></div>';
		echo $this->Form->input('Document.img1', array(
													'label' => 'Allegato',
												    'between' => '<br />',
												    'type' => 'file'
												));	

		echo '<div class="clearfix"></div>';
		echo $this->Form->input('Document.img2', array(
													'label' => 'Allegato',
												    'between' => '<br />',
												    'type' => 'file'
												));	
												
		$msg = 'Estensioni consentite dei file uplodati ';
		foreach($arr_extensions as $extension)
			$msg .= '.'.$extension.'&nbsp;';
		echo $this->element('boxMsg',array('class_msg' => 'notice nomargin','msg' => $msg));
		
	echo '</fieldset>';

echo $this->Form->submit(__('Send'), array('div'=> 'submitMultiple'));

echo $this->Form->end();

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($this->Form->value('Order.id'), $options);
?>
<script type="text/javascript">
$(document).ready(function() {
		
	$('#formGas').submit(function() {

		var subject = $('#OrderSubject').val();
		if(subject=="") {
			alert("Devi indicare l'oggetto della mail");
			return false;
		}
		/*
		var intestazione = $('#OrderIntestazione').val();
		if(intestazione=="") {
			alert("Devi indicare l'intestazione della mail");
			return false;
		}
		*/
		var body = $('#OrderMailOpenTesto').val();
		if(body=="") {
			alert("Devi indicare il testo della mail");
			return false;
		}
	
		alert("Verrà inviata la mail, attendere che venga terminata l'esecuzione");
	
		$("input[type=submit]").attr('disabled', 'disabled');
		$("input[type=submit]").css('background-image', '-moz-linear-gradient(center top , #ccc, #dedede)');
		$("input[type=submit]").css('box-shadow', 'none');

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