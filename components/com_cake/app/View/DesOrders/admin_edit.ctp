<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List DesOrders'), array('controller' => 'DesOrders', 'action' => 'index'));
if(isset($des_order_id) && !empty($des_order_id))
	$this->Html->addCrumb(__('Order home DES'),array('controller'=>'DesOrdersOrganizations','action'=>'index', null, 'des_order_id='.$des_order_id));
$this->Html->addCrumb(__('Edit DesOrder'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';
echo $this->Form->create('DesOrder',array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('Edit DesOrder').'</legend>';
	
	echo $this->Form->input('id');
	
	echo '<div class="row">';
	echo '<div class="col-md-10">';
	echo '<label for="DesOrderSupplier">'.__('DesSupplier').'</label> ';
	echo $this->Form->value('Supplier.name');
	echo '</div>';
	echo '<div class="col-md-2" id="des_supplier_details">';
	echo '</div>';
	echo '</div>';
	echo '<input type="hidden" name="data[DesOrder][des_supplier_id]" value="'.$this->Form->value('DesOrder.des_supplier_id').'" />';
		
	echo $this->Form->input('luogo', array('label' => __('DesDelivery')));

	echo $this->Form->input('data_fine_max',array('type' => 'text','size'=>'30','label' => __('DataFineMax'), 'value' => $this->Time->i18nFormat($this->Form->value('DesOrder.data_fine_max'),"%A, %e %B %Y"), 'required' => 'false'));	
	echo $this->Ajax->datepicker('DesOrderDataFineMax',array('dateFormat' => 'DD, d MM yy','altField' => '#DesOrderDataFineMaxDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="DesOrderDataFineMaxDb" name="data[DesOrder][data_fine_max_db]" value="'.$this->Form->value('DesOrder.data_fine_max').'" />';
	
	echo $this->Form->input('nota');
	
	echo $this->Html->div('clearfix','');
	echo $this->Form->input('nota_evidenza',array('options' => $nota_evidenza,
												  'value' => $this->Form->value('DesOrder.nota_evidenza'),
												  'label' => 'Nota evidenza',
												  'after'=>'<div id="DesOrderNotaEvidenzaImg" style="float:right;height:18px;width:400px;" class=""></div>'));
										
	echo $this->Html->div('clearfix','');
	echo $this->App->drawFormRadio('DesOrder','hasTrasport',array('options' => $hasTrasport, 'value'=>$this->Form->value('DesOrder.hasTrasport'), 'label'=>__('HasTrasport'), 'required'=>'false',
													 'after' => '<div class="action actionTrasport"></div>'.$this->App->drawTooltip(null,__('toolTipHasTrasportDes'),$type='HELP')));

	echo $this->Html->div('clearfix','');
	echo $this->App->drawFormRadio('DesOrder','hasCostMore',array('options' => $hasCostMore, 'value'=>$this->Form->value('DesOrder.hasCostMore'), 'label'=>__('HasCostMore'), 'required'=>'false',
													'after' => '<div class="action actionCostMore"></div>'.$this->App->drawTooltip(null,__('toolTipHasCostMoreDes'),$type='HELP')));

	echo $this->Html->div('clearfix','');
	echo $this->App->drawFormRadio('DesOrder','hasCostLess',array('options' => $hasCostLess, 'value'=>$this->Form->value('DesOrder.hasCostLess'), 'label'=>__('HasCostLess'), 'required'=>'false',
													'after' => '<div class="action actionCostLess"></div>'.$this->App->drawTooltip(null,__('toolTipHasCostLess'),$type='HELP')));
		
	echo '</fieldset>';

echo '<input type="hidden" name="data[DesOrder][des_order_id]" value="'.$this->Form->value('DesOrder.id').'" />';	
echo $this->Form->end(__('Submit'));

echo '</div>';

$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List DesOrders').' </span><span class="fa fa-reply"></span>', array('controller' => 'DesOrders', 'action' => 'index'), ['class' => 'animate', 'escape' => false]);
$links[] = $this->Html->link('<span class="desc animate"> '.__('Order home DES').' </span><span class="fa fa-home"></span>', array('controller' => 'DesOrdersOrganizations', 'action' => 'index', null, 'des_order_id='.$des_order_id), ['class' => 'animate', 'escape' => false]);
$links[] = $this->Html->link('<span class="desc animate"> '.__('Delete').' </span><span class="fa fa-trash"></span>', array('controller' => 'DesOrders', 'action' => 'delete', null, 'des_order_id='.$this->Form->value('DesOrder.id')), array('title' => __('Delete'), 'class' => 'animate', 'escape' => false));
echo $this->Menu->draw($links);
?>
<script type="text/javascript">
function desSuppliersDetails(des_supplier_id) {
	if(des_supplier_id!=0 && des_supplier_id!='') {
		var url = "/administrator/index.php?option=com_cake&controller=Ajax&action=desSupplierDetails&des_supplier_id="+des_supplier_id+"&format=notmpl";
		var idDivTarget = 'des_supplier_details';
		ajaxCallBox(url, idDivTarget);		
	}
}	

$(document).ready(function() {

	desSuppliersDetails(<?php echo $this->Form->value('DesOrder.des_supplier_id');?>);

	$('#DesOrderNotaEvidenzaImg').addClass("nota_evidenza_<?php echo strtolower($this->Form->value('DesOrder.nota_evidenza'));?>");
	
	$('#DesOrderNotaEvidenza').change(function() {
		var deliveryNotaEvidenza = $(this).val();
		setNotaEvidenza(deliveryNotaEvidenza);
	});
	
	<?php
	if(!empty($nota_evidenzaDefault)) 
		echo 'setNotaEvidenza(\''.$nota_evidenzaDefault.'\');';
	?>

	$('#formGas').submit(function() {

		var desOrderDataFineMaxDb = $('#DesOrderDataFineMaxDb').val();
		if(desOrderDataFineMaxDb=='' || desOrderDataFineMaxDb==undefined) {
			alert("Devi indicare la data massima di chiusura dell'ordine");
			return false;
		}	
		
		return true;
	});
	
});

function setNotaEvidenza(deliveryNotaEvidenza) {
	$('#DesOrderNotaEvidenzaImg').removeClass();
	$('#DesOrderNotaEvidenzaImg').addClass("nota_evidenza_"+deliveryNotaEvidenza.toLowerCase());
}
</script>