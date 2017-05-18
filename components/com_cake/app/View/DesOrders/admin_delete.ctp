<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List DesOrders'), array('controller' => 'DesOrders', 'action' => 'index'));
if(isset($des_order_id) && !empty($des_order_id))
	$this->Html->addCrumb(__('Order home DES'),array('controller'=>'DesOrdersOrganizations','action'=>'index', null, 'des_order_id='.$des_order_id));
$this->Html->addCrumb(__('Title Delete DesOrder'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="des_orders form">';
echo $this->Form->create('DesOrder',array('id' => 'formGas', 'method' => 'post'));
echo '<fieldset>';
echo '<legend>'.__('Title Delete DesOrder').'</legend>';
	
	echo $this->Form->input('id');
	
	echo '<div class="input text">';
	echo '<label for="DesSupplier">'.__('DesSupplier').'</label>';
	echo $this->Form->value('Supplier.name');
	echo '</div>';
	
	echo '<div class="input text">';
	echo '<label for="DesDelivery">'.__('DesDelivery').'</label>';
	echo $this->Form->value('DesOrder.luogo');
	echo '</div>';
		
	echo '</fieldset>';
	
echo $this->Form->end(__('Submit Delete'));
echo '</div>';
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List DesOrders'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Order home DES'), array('controller' => 'DesOrdersOrganizations', 'action' => 'index', $des_order_id),array('class'=>'action actionWorkflow'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#formGas').submit(function() {
		if(confirm("<?php echo __('jsAlertConfirmDelete');?>")) {
			return true;
		}	
		
		return false;
	});
});
</script>
