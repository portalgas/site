<?php
if(isset($results['Order']) && $results['Order']['order_type_id']==Configure::read('Order.type.gas_groups')) {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'), Configure::read('Neo.portalgas.url').'admin/orders/index/'.$results['Order']['order_type_id']);
	$this->Html->addCrumb(__('Order home'), Configure::read('Neo.portalgas.url').'admin/orders/home/'.$results['Order']['order_type_id'].'/'.$results['Order']['id']);
	$this->Html->addCrumb(__('OrdersReferenteInProcessedOnDelivery'));
}
else {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home',$this->Form->value('Order.id')));
	$this->Html->addCrumb(__('OrdersReferenteInProcessedOnDelivery'));
}
echo $this->Html->getCrumbList(['class'=>'crumbs']);

echo '<div class="contentMenuLaterale">';

echo '<div class="table-responsive"><table class="table">';
echo '<tr>';
echo '<td>'.__('Supplier').' <b>'.$results['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>';
echo $results['Delivery']['luogo'];
echo '</b>';
echo '<br/>';
echo '<br/>';
echo $this->App->drawOrdersStateDiv($this->request->data).'&nbsp;'.__($this->request->data['Order']['state_code'].'-label').'</td>';
echo '</tr>';
echo '</table></div>';

echo $this->element('boxUploadDoc', array('label' => __('OrdersReferenteInProcessedOnDelivery'), 'msg' => $msg, 'type' => 'CASSIERE'));

echo '</div>';

$options = [];
$options['openCloseClassCss'] = 'open';
echo $this->MenuOrders->drawWrapper($this->Form->value('Order.id'), $options);
?>