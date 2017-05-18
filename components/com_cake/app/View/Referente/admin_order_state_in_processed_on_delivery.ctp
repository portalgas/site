<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));
$this->Html->addCrumb(__('OrdersReferenteInProcessedOnDelivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

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

echo $this->element('boxMsg', array('msg' => $msg));

echo $this->element('boxUploadDoc', array('label' => __('OrdersReferenteInProcessedOnDelivery'), 'msg' => $msg));

echo '</div>';

$options = [];
$options['openCloseClassCss'] = 'open';
echo $this->MenuOrders->drawWrapper($this->Form->value('Order.id'), $options);
?>