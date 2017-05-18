<h3>
	<?php echo __('request_payment_num_short');?> <?php echo $requestPaymentResults['RequestPayment']['num'];
	if(!empty($tot_importo)) echo ' di '.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';?>
</h3>
<ul class="menuLateraleItems">
	<li style="font-size:14px;padding:5px;"><?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']);?> <span style="padding-left: 20px;" title="<?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']);?>" class="stato_<?php echo strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']);?>"></span></li>
	<li><?php echo $this->Html->link(__('Edit Stato Elaborazione'), array('controller' => 'RequestPayments', 'action' => 'edit_stato_elaborazione', $requestPaymentResults['RequestPayment']['id']),array('class' => $position_img.' actionOpen','title' => __('Edit Stato Elaborazione'))); ?></li>
	<li><?php echo $this->Html->link(__('Edit RequestPayment'), array('controller' => 'RequestPayments', 'action' => 'edit', $requestPaymentResults['RequestPayment']['id']),array('class' => $position_img.' actionEdit','title' => __('Edit RequestPayment'))); ?></li>
	<li><?php echo $this->Html->link(__('Delete'), array('controller' => 'RequestPayments', 'action' => 'delete', $requestPaymentResults['RequestPayment']['id']),array('class' => $position_img.' actionDelete','title' => __('Delete'))); ?></li>
	<?php
	if($requestPaymentResults['RequestPayment']['stato_elaborazione']=='WAIT') {
	?>
		<li><?php echo $this->Html->link(__('Add Request Payments Orders'), array('controller' => 'RequestPayments', 'action' => 'add_orders',$requestPaymentResults['RequestPayment']['id']),array('class' => $position_img.' actionAdd'));?></li>
		<?php 
		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y' && $deliveriesValideToStoreroom=='Y') 
			echo '<li>'.$this->Html->link(__('Add Request Payments Storeroom'), array('controller' => 'RequestPayments', 'action' => 'add_storeroom',$requestPaymentResults['RequestPayment']['id']),array('class' => $position_img.' actionAdd')).'</li>';
		?>
		<li><?php echo $this->Html->link(__('Add Request Payments Generic'), array('controller' => 'RequestPayments', 'action' => 'add_generic',$requestPaymentResults['RequestPayment']['id']),array('class' => $position_img.' actionAdd'));?></li>
	<?php
	}
	?>
</ul>

<div class="clearfix"></div>

<h3><?php echo __('Actions'); ?></h3>
<?php 
echo "\r\n"; 
echo '<ul class="menuLateraleItems">';
echo '<li>'.$this->Html->link(__('OrdersWaitProcessedTesoriere'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_get_WAIT_PROCESSED_TESORIERE&delivery_id='.$results['Delivery']['id'],array('class' => $position_img.' actionReloadFoward','title' => '')).'</li>';
echo "\r\n";
echo  '<li>'.$this->Html->link(__('OrdersProcessedTesoriere'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_get_PROCESSED_TESORIERE&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionConfig')).'</li>';
echo "\r\n";
echo  '<li>'.$this->Html->link(__('OrdersToPayment'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=RequestPayments&action=index&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionPay')).'</li>';
echo "\r\n";
/*
	echo  '<li>'.$this->Html->link(__('Export Docs to delivery'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Docs&action=tesoriereDocsExport&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionPrinter')).'</li>';
*/
echo '</ul>';
echo '</ul>';
?>