<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Deliveries'), array('controller' => 'Deliveries', 'action' => 'index'));
$this->Html->addCrumb(__('Add Delivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="deliveries form">
<?php echo $this->Form->create('Delivery');?>
	<fieldset>
		<legend><?php echo __('Add Delivery'); ?></legend>
	<?php
		echo $this->Ajax->autoComplete('luogo',
									  Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteDeliveries_luogo&format=notmpl',
									  array('label' => 'Luogo Consegna','div' => 'required'));
									  
		echo $this->Form->input('data', ['type' => 'text', 'size' => '30', 'value' => $data, 'autocomplete' => 'off']);
		echo $this->Ajax->datepicker('DeliveryData',array('dateFormat' => 'DD, d MM yy','altField' => '#DeliveryDataDb', 'altFormat' => 'yy-mm-dd'));
		echo '<input type="hidden" id="DeliveryDataDb" name="data[Delivery][data_db]" value="'.$data_db.'" />';
		
		echo $this->Html->div('clearfix','');
		
		echo '<div class="row">';
		$options['required'] = 'required';
		echo $this->App->drawHour('Delivery', 'orario_da', __('orario_da'), date("Y-mm-dd").' '.$orario_da, $options);	
		echo $this->App->drawHour('Delivery', 'orario_a', __('orario_a'), date("Y-mm-dd").' '.$orario_a, $options);		
		echo '</div>';
		
		echo $this->Html->div('clearfix','');
		echo '<br />';
		
		echo '<div class="row">';
		echo '<div class="col-md-12">';
		echo $this->Form->input('nota');
		echo $this->Html->div('clearfix','');
		echo $this->Form->input('nota_evidenza', array('options' => $nota_evidenza,
													   'label' => 'Nota evidenza',
													   'after'=>'<div id="DeliveryNotaEvidenzaImg" style="float:right;height:18px;width:400px;" class=""></div>'));
		echo '</div>';
		echo '</div>';

		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			echo $this->App->drawFormRadio('Delivery','isToStoreroom',array('options' => $isToStoreroom, 'value'=> $isToStoreroomDefault, 'label'=>__('isToStoreroom'), 'required'=>'required',
																	'after'=>$this->App->drawTooltip(null,__('toolTipIsToStoreroom'),$type='HELP')));
		} 
		
		if($user->organization['Organization']['hasVisibility']=='Y') {
			echo $this->App->drawFormRadio('Delivery','isVisibleFrontEnd',array('options' => $isVisibleFrontEnd, 'value'=> $isVisibleFrontEndDefault, 'label'=>__('isVisibleFrontEnd'), 'required'=>'required',
														   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleFrontEndDelivery'),$type='HELP')));
	
			echo $this->App->drawFormRadio('Delivery','isVisibleBackOffice',array('options' => $isVisibleBackOffice, 'value'=> $isVisibleBackOfficeDefault, 'label'=>__('isVisibleBackOffice'), 'required'=>'required',
														   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleBackOfficeDelivery'),$type='HELP')));
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Deliveries'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
$(document).ready(function() {

	$('#DeliveryNotaEvidenzaImg').addClass("nota_evidenza_no");
	
	$('#DeliveryNotaEvidenza').change(function() {
		var deliveryNotaEvidenza = $(this).val();
		setNotaEvidenza(deliveryNotaEvidenza);
	});
	
	<?php
	if(!empty($nota_evidenzaDefault)) 
		echo 'setNotaEvidenza(\''.$nota_evidenzaDefault.'\');';
	?>
	
});

function setNotaEvidenza(deliveryNotaEvidenza) {
	$('#DeliveryNotaEvidenzaImg').removeClass();
	$('#DeliveryNotaEvidenzaImg').addClass("nota_evidenza nota_evidenza_"+deliveryNotaEvidenza.toLowerCase());
}
</script>