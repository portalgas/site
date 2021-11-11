<?php
$this->App->d($this->request->data);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));

if($this->request->data['Order']['state_code']=='PROCESSED-POST-DELIVERY')
	$this->Html->addCrumb(__('OrdersReferenteInProcessedPostDelivery'));  /* tesoriere */
else
if($this->request->data['Order']['state_code']=='INCOMING-ORDER')  /* cassiere */
	$this->Html->addCrumb(__('OrdersReferenteInProcessedOnDelivery'));

echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo '<div class="table-responsive"><table class="table">';
echo '<tr>';
echo '<td>'.__('Supplier').' <b>'.$results['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>';
if($results['Delivery']['sys']=='N')
	echo $results['Delivery']['luogoData'];
else 
	echo $results['Delivery']['luogo'];
echo '</b>';
echo '<br/>';
echo '<br/>';
echo $this->App->drawOrdersStateDiv($this->request->data).'&nbsp;'.__($this->request->data['Order']['state_code'].'-label').'</td>';
echo '</tr>';
echo '</table></div>';

echo $this->Form->create('Order',array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend></legend>';

echo $this->element('boxMsg', array('msg' => $msg['msg']));

if(isset($msg['actions'])) {

	echo '<div class="legenda">';
	echo '<h2>Cosa puoi fare:</h2>';
	echo '<div class="table-responsive"><table class="table table-hover table-striped">';
	
	foreach($msg['actions'] as $action){

		echo '<tr>';
		echo '<td style="width:50px"><div title="'.$action['action_label'].'" class="action '.$action['action_class'].'"></div></td>';
		echo '<td><a href="'.$action['url'].'">'.$action['msg'].'</a></td>';
		echo '</tr>';
	}
	echo '</table></div>';
	
	echo '<br/>';
	echo '</div>';
}

echo '</fieldset>';

echo $this->Form->end();
echo '</div>';

$options = [];
$options['openCloseClassCss'] = 'open';
echo $this->MenuOrders->drawWrapper($this->Form->value('Order.id'), $options);
?>