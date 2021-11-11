<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$this->Form->value('Order.id')));

if($this->request->data['Order']['state_code']=='PROCESSED-POST-DELIVERY')
	$this->Html->addCrumb(__('OrdersReferenteInWaitProcessedTesoriere'));  /* tesoriere */
else
if($this->request->data['Order']['state_code']=='INCOMING-ORDER')  /* cassiere */
	$this->Html->addCrumb(__('OrdersReferenteInProcessedOnDelivery'));
else
if($this->request->data['Order']['state_code']=='PROCESSED-ON-DELIVERY')  /* cassiere al tesoriere */
	$this->Html->addCrumb(__('OrdersReferenteInWaitProcessedTesoriere'));

echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

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
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>

	<div id="order-sotto-menu-<?php echo $this->Form->value('Order.id');?>" style="clear: both;"></div>
	
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