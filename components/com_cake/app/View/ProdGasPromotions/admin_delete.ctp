<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'));
$this->Html->addCrumb(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => 'edit', $prod_gas_promotion_id));
$this->Html->addCrumb(__('Title Delete ProdGasPromotion'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotion', array('type' => 'post'));?>
	<fieldset>
		<legend><?php echo __('Title Delete ProdGasPromotion'); ?></legend>



<?php
echo '</fieldset>';
echo $this->Form->hidden('id',array('value' => $results['ProdGasPromotion']['prod_gas_promotion_id']));
echo $this->Form->end(__('Submit Delete'));
?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => 'edit', $prod_gas_promotion_id),array('class'=>'action actionEdit'));?></li>
	</ul>
	
</div>


<style type="text/css">
.cakeContainer div.form, .cakeContainer div.index, .cakeContainer div.view {
    width: 74%;
}
.cakeContainer div.actions {
    width: 25%;
}
</style>