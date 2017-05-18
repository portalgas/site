<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List DesOrders'), array('controller' => 'DesOrders', 'action' => 'index'));
$this->Html->addCrumb(__('Add DesOrder'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="deliveries form">';

if(!empty($ACLDesSuppliersResults)) {
	echo $this->Form->create('DesOrder',array('id' => 'formGas'));
	echo '<fieldset>';
	echo '<legend>'.__('Add DesOrder').'</legend>';
		
	$options = array('id' => 'des_supplier_id', 
					 'data-placeholder' => 'Scegli un produttore',
					 'options' => $ACLDesSuppliersResults, 
					 'default' => $des_supplier_id, 
					 'required' => 'false', 
					 'after' => '<div style="float:right;" id="des_supplier_details"></div>');
	if(count($ACLDesSuppliersResults) > Configure::read('HtmlSelectWithSearchNum')) 
		$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
	else
		$options += array('empty' => Configure::read('option.empty')); 
	echo $this->Form->input('des_supplier_id', $options);
	
	echo $this->Form->input('luogo', array('label' => __('DesDelivery')));

	echo $this->Form->input('data_fine_max',array('type' => 'text','size'=>'30','label' => __('Data fine max'), 'value' => $data_fine_max, 'required'=>'false'));
	echo $this->Ajax->datepicker('DesOrderDataFineMax',array('dateFormat' => 'DD, d MM yy','altField' => '#DesOrderDataFineMaxDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="DesOrderDataFineMaxDb" name="data[DesOrder][data_fine_max_db]" value="'.$data_fine_max_db.'" />';
	
	echo $this->Form->input('nota');
	
	echo $this->Html->div('clearfix','');
	echo $this->Form->input('nota_evidenza',array('options' => $nota_evidenza,
												  'value' => $this->Form->value('DesOrder.nota_evidenza'),
												  'label' => 'Nota evidenza',
												  'after'=>'<div id="DesOrderNotaEvidenzaImg" style="float:right;height:18px;width:400px;" class=""></div>'));
										
	echo $this->Html->div('clearfix','');
	echo $this->App->drawFormRadio('DesOrder','hasTrasport',array('options' => $hasTrasport, 'value'=>$hasTrasportDefault, 'label'=>__('HasTrasport'), 'required'=>'false',
													 'after' => '<div class="action actionTrasport"></div>'.$this->App->drawTooltip(null,__('toolTipHasTrasportDes'),$type='HELP')));
	
	echo $this->Html->div('clearfix','');
	echo $this->App->drawFormRadio('DesOrder','hasCostMore',array('options' => $hasCostMore, 'value'=>$hasCostMoreDefault, 'label'=>__('HasCostMore'), 'required'=>'false',
													'after' => '<div class="action actionCostMore"></div>'.$this->App->drawTooltip(null,__('toolTipHasCostMoreDes'),$type='HELP')));

	echo $this->Html->div('clearfix','');
	echo $this->App->drawFormRadio('DesOrder','hasCostLess',array('options' => $hasCostLess, 'value'=>$hasCostLessDefault, 'label'=>__('HasCostLess'), 'required'=>'false',
													'after' => '<div class="action actionCostLess"></div>'.$this->App->drawTooltip(null,__('toolTipHasCostLess'),$type='HELP')));

	echo $this->Html->div('clearfix','');
	echo $this->App->drawFormRadio('DesOrder','sendMail',array('options' => $sendMail, 'value' => 'Y', 'label'=>__('DESsendMail'), 'required'=>'false'));
			
	echo $this->Html->div('clearfix','');
	echo $this->Form->drawFormCheckbox('DesOrder', 'sendMailTarget', array('options' => $sendMailTarget, 'selected'=> $sendMailTargetDefault, 'label'=>__('DESsendMailTarget'), 'required'=>'false'));			
	
	echo $this->element('legendaUsersGroups', array('usersGroups' => $usersGroups));
	
	echo '</fieldset>';
	
	echo $this->Form->end(__('Submit'));
}
else 
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non hai produttori associati"));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List DesOrders'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
function desSuppliersDetails(des_supplier_id) {
	if(des_supplier_id!=0 && des_supplier_id!='') {
		var url = "/administrator/index.php?option=com_cake&controller=Ajax&action=desSupplierDetails&des_supplier_id="+des_supplier_id+"&format=notmpl";
		var idDivTarget = 'des_supplier_details';
		ajaxCallBox(url, idDivTarget);		
	}
}	
 
jQuery(document).ready(function() {

	jQuery('#des_supplier_id').change(function() {
		var des_supplier_id = jQuery(this).val();
		desSuppliersDetails(des_supplier_id);
	});
	 
	jQuery('#DesOrderNotaEvidenzaImg').addClass("nota_evidenza_<?php echo strtolower($this->Form->value('DesOrder.nota_evidenza'));?>");
	
	jQuery('#DesOrderNotaEvidenza').change(function() {
		var deliveryNotaEvidenza = jQuery(this).val();
		setNotaEvidenza(deliveryNotaEvidenza);
	});
	
	<?php
	if(!empty($nota_evidenzaDefault)) 
		echo 'setNotaEvidenza(\''.$nota_evidenzaDefault.'\');';
	?>
	
	jQuery('#formGas').submit(function() {
	
		var des_supplier_id = jQuery('#des_supplier_id').val();
		if(des_supplier_id=='' || des_supplier_id==undefined) {
			alert("<?php echo __('jsAlertSupplierRequired');?>");
			jQuery('#des_supplier_id').focus();
			return false;
		}
		
		var desOrderDataFineMaxDb = jQuery('#DesOrderDataFineMaxDb').val();
		if(desOrderDataFineMaxDb=='' || desOrderDataFineMaxDb==undefined) {
			alert("Devi indicare la data massima di chiusura dell'ordine");
			return false;
		}	
		
		return true;
	});
	
});

function setNotaEvidenza(deliveryNotaEvidenza) {
	jQuery('#DesOrderNotaEvidenzaImg').removeClass();
	jQuery('#DesOrderNotaEvidenzaImg').addClass("nota_evidenza_"+deliveryNotaEvidenza.toLowerCase());
}
</script>
