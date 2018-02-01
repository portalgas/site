<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));
$this->Html->addCrumb(__('Edit Order Delivery Old'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';
echo $this->Form->create('Order',array('id' => 'formGas'));
echo '<fieldset>';

echo '<legend>'.__('Edit Order Delivery Old').'</legend>';

echo $this->element('boxOrder',array('results' => $results));
	
if(!empty($deliveries)) {

			
	$options = array('id' => 'delivery_id', 'options' => $deliveries, 'empty' => Configure::read('option.empty'), 'required' => 'false');
	echo $this->Form->input('delivery_id', $options);		
	
	echo '</fieldset>';
	
	echo '<input type="hidden" name="data[Order][order_id]" value="'.$this->Form->value('Order.id').'" />'; 
	echo $this->Form->end(__('Submit'));
}
else {
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono consegne scaduta da associare all'ordine"));
	echo '</fieldset>';
	
	echo '<input type="hidden" name="data[Order][order_id]" value="'.$this->Form->value('Order.id').'" />'; 
	echo $this->Form->end();
}
echo '</div>';

echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'),array('class'=>'action actionReload')).'</li>';
echo '<li>'.$this->Html->link(__('Edit Order'), array('controller' => 'Orders', 'action' => 'edit', null, 'delivery_id='.$this->Form->value('Order.delivery_id').'&order_id='.$this->Form->value('Order.id')),array('class'=>'action actionEdit')).'</li>';
echo '</ul>';
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {

	$('#formGas').submit(function() {

		var delivery_id = $('#delivery_id').val();
		
		if(delivery_id=='') {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
		
		return true;
	});
})
</script>