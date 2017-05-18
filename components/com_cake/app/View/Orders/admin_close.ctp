<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home',$this->Form->value('Order.id')));
$this->Html->addCrumb(__('Close Order'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

if(!empty($des_order_id))
	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));	

echo $this->Form->create('Order', array('type' => 'post'));?>
	<fieldset>
		<legend><?php echo __('Close Order'); ?></legend>

		<div class="input text"><label for=""><?php echo __('Supplier')?></label><?php echo $results['SuppliersOrganization']['name'];?></div>

		<div class="input text"><label for=""><?php echo __('Delivery')?></label>
		<?php 
		if($results['Delivery']['sys']=='N')
			echo $results['Delivery']['luogoData'];
		else 
			echo $results['Delivery']['luogo'];		
		?></div>

		<div class="input text"><label for="">Decorrenza</label><?php echo $results['Order']['name'];?></div>

		<div class="input text"><label for=""><?php echo __('StateOrder');?></label><?php echo $this->App->drawOrdersStateDiv($results);?><?php echo __($results['Order']['state_code'].'-label');?></div>

		<?php 
		echo $this->Element('boxMsg',array('msg' => $msg)); 
		
		echo $this->App->drawFormRadio('Order','order_just_pay', array('options' => $order_just_pay, 'value' => 'N', 'name' => 'order_just_pay', 'label'=>__('order_just_pay')));
		
		
	echo '</fieldset>';
	echo $this->Form->hidden('id',array('value' => $results['Order']['id']));
		
	if($results['Delivery']['sys']=='N')
		echo $this->Form->end(__('Submit'));
	else
		echo $this->Form->end();

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($results['Order']['id'], $options);
?>