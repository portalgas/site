<?php
echo '<li>';
echo $this->Html->link('<span class="desc animate"> '.__('Edit RequestPayment').' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="action actionWorkflow"></span>', array('controller' => 'RequestPayments', 'action' => 'edit', $requestPaymentResults['RequestPayment']['id']),array('class' => 'animate', 'escape' => false, 'title' => __('Edit RequestPayment')));
echo '</li>';
echo '<li>';
echo $this->Html->link('<span class="desc animate"> '.__('Edit Stato Elaborazione').' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="action actionOpen"></span>', array('controller' => 'RequestPayments', 'action' => 'edit_stato_elaborazione', $requestPaymentResults['RequestPayment']['id']),array('class' => 'animate', 'escape' => false, 'title' => __('Edit Stato Elaborazione')));
echo '</li>';
echo '<li>';
echo $this->Html->link('<span class="desc animate"> '.__('Export RequestPayment').' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="action actionPrinter"></span>', array('controller' => 'Pages', 'action' => 'export_docs_request_payment', $requestPaymentResults['RequestPayment']['id']), array('class' => 'animate', 'escape' => false, 'title' => __('Export RequestPayment')));
echo '</li>';
echo '<li>';
echo $this->Html->link('<span class="desc animate"> '.__('Delete').' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="action actionDelete"></span>', array('controller' => 'RequestPayments', 'action' => 'delete', $requestPaymentResults['RequestPayment']['id']),array('class' => 'animate', 'escape' => false, 'title' => __('Delete')));
echo '</li>';

if($requestPaymentResults['RequestPayment']['stato_elaborazione']=='WAIT') {
	echo '<li>';
	echo $this->Html->link('<span class="desc animate"> '.__('Add Request Payments Orders').' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="action actionAdd"></span>', array('controller' => 'RequestPayments', 'action' => 'add_orders',$requestPaymentResults['RequestPayment']['id']),array('class' => 'animate', 'title' => __('Add Request Payments Orders'),'escape' => false));
	echo '</li>';
	if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y' && $deliveriesValideToStoreroom=='Y') {
		echo '<li>'; 
		echo $this->Html->link('<span class="desc animate"> '.__('Add Request Payments Storeroom').' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="action actionAdd"></span>', array('controller' => 'RequestPayments', 'action' => 'add_storeroom',$requestPaymentResults['RequestPayment']['id']),array('class' => 'animate', 'title' => __('Add Request Payments Storeroom'), 'escape' => false));
		echo '</li>';
	}
	echo '<li>';
	echo $this->Html->link('<span class="desc animate"> '.__('Add Request Payments Generic').' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="action actionAdd"></span>', array('controller' => 'RequestPayments', 'action' => 'add_generic',$requestPaymentResults['RequestPayment']['id']),array('class' => 'animate', 'title' => __('Add Request Payments Generic'), 'escape' => false));
	echo '</li>';
}
else {
    echo '<li>';
    echo $this->Html->link('<span class="desc animate"> '.__('RequestPaymentMailSend').' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="action actionMail"></span>', ['controller' => 'Connects', 'action' => 'index', 'c_to' => 'admin/request-payments&a_to=mails&request_payment_id='.$requestPaymentResults['RequestPayment']['id']], ['target' => '_blank', 'class' => 'animate', 'title' => __('RequestPaymentMailSend'), 'escape' => false]);
    echo '</li>';
}

echo '<div class="clearfix"></div>';
/*
 * gestione  - S T A T E S
 */	
echo '<div class="clearfix"></div>';
echo '<div class="menuOrderStatoTitle">Ciclo della richiesta</div>';

echo '<ul class="menuLateraleItems">';
echo '<li class="';
echo ($requestPaymentResults['RequestPayment']['stato_elaborazione']=='WAIT') ? 'statoCurrent': 'statoNotCurrent';
echo '">';
echo '<a title="" class="bgRight stato_wait" style="text-decoration:none;font-weight:normal;cursor:default;color:#fff;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_WAIT').'</a>';
echo '</li>';
echo '<li class="';
echo ($requestPaymentResults['RequestPayment']['stato_elaborazione']=='OPEN') ? 'statoCurrent': 'statoNotCurrent';
echo '">';
echo '<a title="" class="bgRight stato_open" style="text-decoration:none;font-weight:normal;cursor:default;color:#fff;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_OPEN').'</a>';
echo '</li>';
echo '<li class="';
echo ($requestPaymentResults['RequestPayment']['stato_elaborazione']=='CLOSE') ? 'statoCurrent': 'statoNotCurrent';
echo '">';
echo '<a title="" class="bgRight stato_close" style="text-decoration:none;font-weight:normal;cursor:default;color:#fff;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_CLOSE').'</a>';
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