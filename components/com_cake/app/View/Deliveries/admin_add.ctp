<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Deliveries'), array('controller' => 'Deliveries', 'action' => 'index'));
$this->Html->addCrumb(__('Add Delivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';
echo $this->Form->create('Delivery', ['class' => 'form-group']);
echo '<fieldset>';
echo '<legend>'.__('Add Delivery').'</legend>';
	
echo $this->Ajax->autoComplete('luogo',
							  Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteDeliveries_luogo&format=notmpl',
							  array('label' => 'Luogo Consegna','div' => 'required','class' => 'form-control'));
							  
echo $this->Form->input('data',array('type' => 'text','size'=>'30', 'value' => $data,'class' => 'form-control'));
echo $this->Ajax->datepicker('DeliveryData',array('dateFormat' => 'DD, d MM yy','altField' => '#DeliveryDataDb', 'altFormat' => 'yy-mm-dd'));
echo '<input type="hidden" id="DeliveryDataDb" name="data[Delivery][data_db]" value="'.$data_db.'" />';

echo '<div class="row">';
echo '<div class="col-sm-4">';
echo $this->Form->input('orario_da', array('type' => 'time','selected' => $orario_da,'timeFormat'=>'24','interval' => 15,'class' => 'form-control'));
echo '</div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-sm-4">';
echo $this->Form->input('orario_a',  array('type' => 'time','selected' => $orario_a, 'timeFormat'=>'24','interval' => 15,'class' => 'form-control'));
echo '</div>';
echo '</div>';

echo $this->Form->input('nota',['class' => 'form-control', 'width' => '100%']);
echo $this->Html->div('clearfix','');
echo $this->Form->input('nota_evidenza', array('options' => $nota_evidenza,
											   'label' => 'Nota evidenza',
											   'class' => 'form-control',
											   'after'=>'<div id="DeliveryNotaEvidenzaImg" style="float:right;height:18px;width:400px;" class=""></div>'));

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
		
echo '</fieldset>';
echo $this->Form->end(__('Submit'));
echo '</div>';

$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Deliveries').' </span><span class="fa fa-reply"></span>', array('controller' => 'Deliveries', 'action' => 'index'), ['class' => 'animate', 'escape' => false]);
echo $this->Menu->draw($links);
?>
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#DeliveryNotaEvidenzaImg').addClass("nota_evidenza_no");
	
	jQuery('#DeliveryNotaEvidenza').change(function() {
		var deliveryNotaEvidenza = jQuery(this).val();
		setNotaEvidenza(deliveryNotaEvidenza);
	});
	
	<?php
	if(!empty($nota_evidenzaDefault)) 
		echo 'setNotaEvidenza(\''.$nota_evidenzaDefault.'\');';
	?>
	
});

function setNotaEvidenza(deliveryNotaEvidenza) {
	jQuery('#DeliveryNotaEvidenzaImg').removeClass();
	jQuery('#DeliveryNotaEvidenzaImg').addClass("nota_evidenza nota_evidenza_"+deliveryNotaEvidenza.toLowerCase());
}
</script>