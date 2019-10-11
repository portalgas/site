<?php
/*
echo "<pre>";
print_r($this->request['data']);
echo "</pre>";
*/
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesOrders'),array('controller' => 'DesOrders', 'action' => 'index'));
if(isset($des_order_id) && !empty($des_order_id))
	$this->Html->addCrumb(__('Order home DES'),array('controller'=>'DesOrdersOrganizations','action'=>'index', null, 'des_order_id='.$des_order_id));
$this->Html->addCrumb(__('Edit DesOrdersOrganizations'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="deliveries form">';

echo $this->Form->create('DesOrdersOrganization',array('id' => 'formGas'));?>
	<fieldset>
		<legend><?php echo __('Edit DesOrdersOrganizations'); ?></legend>
	<?php
		echo $this->element('boxDesOrder', array('results' => $desOrdersResults));	

		echo $this->element('legendaDesOrdersOrganizationEdit');

		echo $this->Form->input('luogo',array('required'=>'required'));

		echo $this->App->drawDate('DesOrdersOrganization','data', __('Data'),$this->Form->value('DesOrdersOrganization.data'));
		
		echo $this->Html->div('clearfix','');
		
		echo '<div class="row">';
		// $options['required'] = 'required';
		echo $this->App->drawHour('DesOrdersOrganization', 'orario', __('orario'), $this->Form->value('DesOrdersOrganization.orario'), $options);		
		echo '</div>';		
		
		echo $this->Html->div('clearfix','');
		echo '<br />';
		
		echo $this->Form->input('contatto_nominativo',array('type' => 'text', 'value' => $this->Form->value('DesOrdersOrganization.contatto_nominativo')));
		
		echo $this->Form->input('contatto_telefono',array('type' => 'text', 'value' => $this->Form->value('DesOrdersOrganization.contatto_telefono')));
		
		echo $this->Form->input('contatto_mail',array('type' => 'text', 'value' => $this->Form->value('DesOrdersOrganization.contatto_mail')));
		
		echo $this->Form->input('nota');
		
		echo $this->Html->div('clearfix','');
		
	echo '</fieldset>';

echo $this->Form->hidden('id',array('value' => $this->request['data']['DesOrdersOrganization']['id']));
echo $this->Form->hidden('des_order_id',array('value' => $this->request['data']['DesOrdersOrganization']['des_order_id']));	
echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List DesOrders'), array('controller' => 'DesOrders', 'action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
$(document).ready(function() {

	$('#formGas').submit(function() {		

		var luogo = $('#DesOrdersOrganizationLuogo').val();
		if(luogo=='' || luogo==undefined) {
			alert("<?php echo __('jsAlertDeliveryToSupplierRequired');?>");
			$('#DesOrdersOrganizationLuogo').focus();
			return false;
		}	    

		var contattoMail = $('#DesOrdersOrganizationContattoMail').val();
		if(contattoMail!='') {
			if(!validateEmail(contattoMail)) {
				alert("<?php echo __('jsAlertMailInvalid');?>");
				$('#DesOrdersOrganizationContattoMail').focus();
				return false;
			}	
		}
		return true;	
	});
	
});
</script>