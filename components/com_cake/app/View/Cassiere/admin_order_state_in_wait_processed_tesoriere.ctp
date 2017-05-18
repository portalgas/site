<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));
$this->Html->addCrumb(__('OrdersReferenteInWaitProcessedTesoriere'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo '<table cellpadding = "0" cellspacing = "0">';
echo '<tr>';
echo '<td>'.__('Supplier').' <b>'.$results['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>';
echo $results['Delivery']['luogo'];
echo '</b>';
echo '<br/>';
echo '<br/>';
echo $this->App->drawOrdersStateDiv($this->request->data).'&nbsp;'.__($this->request->data['Order']['state_code'].'-label').'</td>';
echo '</tr>';
echo '</table>';

echo $this->element('boxUploadDoc', array('msg' => $msg));

echo '</div>';
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('ListOrdersToWaitProcessedTesoriere'), array('controller' => 'Cassiere', 'action' => 'orders_to_wait_processed_tesoriere'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
viewOrderSottoMenu(<?php echo $this->Form->value('Order.id');?>, "bgLeft");
</script>

<style type="text/css">
.cakeContainer div.form, .cakeContainer div.index, .cakeContainer div.view {
    width: 74%;
}
.cakeContainer div.actions {
    width: 25%;
}
</style>