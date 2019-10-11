<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'));
$this->Html->addCrumb(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => 'edit', $results['ProdGasPromotion']['id']));
$this->Html->addCrumb(__('Title Delete ProdGasPromotion'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotion', array('type' => 'post'));
echo '<fieldset>';
echo '<legend>'.__('Title Delete ProdGasPromotion').'</legend>';

echo $this->Element('boxProdGasPromotionOrganizations', array('results' => $results));

$msg = "Cancello definitivamente la promozione ".$results['ProdGasPromotion']['name'];
echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => $msg));

echo '</fieldset>';
echo $this->Form->hidden('id',array('value' => $results['ProdGasPromotion']['prod_gas_promotion_id']));
echo $this->Form->end(__('Submit Delete'));
echo '</div>';
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit ProdGasPromotion'), array('controller' => 'ProdGasPromotions', 'action' => 'edit', $results['ProdGasPromotion']['id']),array('class'=>'action actionEdit'));?></li>
	</ul>	
</div>