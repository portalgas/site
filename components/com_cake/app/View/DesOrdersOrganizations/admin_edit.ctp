<?php
/*
echo "<pre>";
print_r($this->request['data']);
echo "</pre>";
*/
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
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

		echo $this->Form->input('data',array('type' => 'text','size'=>'30','value' => $this->Time->i18nFormat($this->Form->value('DesOrdersOrganization.data'),"%A, %e %B %Y")));
		echo $this->Ajax->datepicker('DesOrdersOrganizationData',array('dateFormat' => 'DD, d MM yy','altField' => '#DesOrdersOrganizationDataDb', 'altFormat' => 'yy-mm-dd'));
		echo '<input type="hidden" id="DesOrdersOrganizationDataDb" name="data[DesOrdersOrganization][data_db]" value="'.$this->Form->value('DesOrdersOrganization.data').'" />';
		
		echo $this->Form->input('orario', array('type' => 'time','selected' => $this->Form->value('DesOrdersOrganization.orario'),'timeFormat'=>'24','interval' => 15));
		
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
jQuery(document).ready(function() {

	jQuery('#formGas').submit(function() {		

		var luogo = jQuery('#DesOrdersOrganizationLuogo').val();
		if(luogo=='' || luogo==undefined) {
			alert("<?php echo __('jsAlertDeliveryToSupplierRequired');?>");
			jQuery('#DesOrdersOrganizationLuogo').focus();
			return false;
		}	    

		var contattoMail = jQuery('#DesOrdersOrganizationContattoMail').val();
		if(contattoMail!='') {
			if(!validateEmail(contattoMail)) {
				alert("<?php echo __('jsAlertMailInvalid');?>");
				jQuery('#DesOrdersOrganizationContattoMail').focus();
				return false;
			}	
		}
		return true;	
	});
	
});
</script>