<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if($isReferenteTesoriere)  {
	$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
}
else {
	if(!isset($delivery_id)) $delivery_id = 0;
		$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
}
$this->Html->addCrumb(__('List Request Payments'),array('controller' => 'RequestPayments', 'action' => 'index'));
$this->Html->addCrumb(__('Add Request Payments'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="request_payment form">
<?php echo $this->Form->create('RequestPayment');?>
	<fieldset>
		<legend><?php echo __('Add Request Payments'); ?></legend>
	<?php
		echo $this->Form->input('request_payment_num', array('label' => __('request_payment_num'), 'size' => 4, 'value' => $request_payment_num, 'disabled' => true));
		echo $this->Form->input('nota', array('type' => 'textarea', 'class' => "noeditor", 'before' => $this->App->drawTooltip(null,__('toolRequestPaymentNota'),$type='INFO')));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Request Payments'), array('controller' => 'RequestPayments', 'action' => 'index'), array('class'=>'action actionReload'));?></li>
	</ul>
</div>