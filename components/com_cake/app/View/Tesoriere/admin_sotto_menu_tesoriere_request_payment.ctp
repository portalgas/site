<ul class="menuLateraleItems">
	<li><?php echo $this->Html->link(__('Edit RequestPayment'), array('controller' => 'RequestPayments', 'action' => 'edit', $requestPaymentResults['RequestPayment']['id']),array('class' => $position_img.' actionWorkflow','title' => __('Edit RequestPayment'))); ?></li>
	<li><?php echo $this->Html->link(__('Edit Stato Elaborazione'), array('controller' => 'RequestPayments', 'action' => 'edit_stato_elaborazione', $requestPaymentResults['RequestPayment']['id']),array('class' => $position_img.' actionOpen','title' => __('Edit Stato Elaborazione'))); ?></li>
	<li><?php echo $this->Html->link(__('Export RequestPayment'), array('controller' => 'Pages', 'action' => 'export_docs_request_payment', $requestPaymentResults['RequestPayment']['id']),array('class' => $position_img.' actionPrinter','title' => __('Export RequestPayment'))); ?></li>
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
    else {
        echo '<li>'.$this->Html->link(__('RequestPaymentMailSend'), ['controller' => 'Connects', 'action' => 'index', 'c_to' => 'admin/request-payments&a_to=mails&request_payment_id='.$requestPaymentResults['RequestPayment']['id']], ['target' => '_blank', 'class' => $position_img.' actionMail', 'title' => __('RequestPaymentMailSend')]).'</li>';
    }
	?>
</ul>

<div class="clearfix"></div>
<?php
/*
 * gestione  - S T A T E S
 */	
echo '<div class="clearfix"></div>';
echo '<h3>Ciclo della richiesta</h3>';

echo '<ul class="menuLateraleItems">';
echo '<li class="';
echo ($requestPaymentResults['RequestPayment']['stato_elaborazione']=='WAIT') ? 'statoCurrent': 'statoNotCurrent';
echo '">';
echo '<a title="" class="bgLeft stato_wait" style="text-decoration:none;font-weight:normal;cursor:default;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_WAIT').'</a>';
echo '</li>';
echo '<li class="';
echo ($requestPaymentResults['RequestPayment']['stato_elaborazione']=='OPEN') ? 'statoCurrent': 'statoNotCurrent';
echo '">';
echo '<a title="" class="bgLeft stato_open" style="text-decoration:none;font-weight:normal;cursor:default;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_OPEN').'</a>';
echo '</li>';
echo '<li class="';
echo ($requestPaymentResults['RequestPayment']['stato_elaborazione']=='CLOSE') ? 'statoCurrent': 'statoNotCurrent';
echo '">';
echo '<a title="" class="bgLeft stato_close" style="text-decoration:none;font-weight:normal;cursor:default;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_CLOSE').'</a>';
echo '</li>';
echo '</ul>';

/*
echo '<h3>'.__('Actions').'</h3>';
echo "\r\n"; 
echo '<ul class="menuLateraleItems">';
echo '<li>'.$this->Html->link(__('OrdersWaitProcessedTesoriereShort'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_get_WAIT_PROCESSED_TESORIERE&delivery_id='.$results['Delivery']['id'],array('class' => $position_img.' actionReloadFoward','title' => '')).'</li>';
echo "\r\n";
echo  '<li>'.$this->Html->link(__('OrdersProcessedTesoriereShort'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_get_PROCESSED_TESORIERE&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionConfig')).'</li>';
echo "\r\n";
echo  '<li>'.$this->Html->link(__('OrdersToRequestPaymentShort'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_get_TO_REQUEST_PAYMENT&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionConfig')).'</li>';
echo "\r\n";
echo  '<li>'.$this->Html->link(__('OrdersToPaymentShort'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=RequestPayments&action=index&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionPay')).'</li>';
echo "\r\n";
// echo  '<li>'.$this->Html->link(__('Export Docs to delivery'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Docs&action=tesoriereDocsExport&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionPrinter')).'</li>';
echo '</ul>';
*/
?>