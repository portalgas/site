<?php 
echo "\r\n"; 
echo '<ul class="menuLateraleItems">';
echo '<li>'.$this->Html->link(__('Tesoriere home'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=home&delivery_id='.$results['Delivery']['id'],array('class' => $position_img.' actionWorkflow','title' => '')).'</li>';
echo "\r\n";
echo '<li>'.$this->Html->link(__('OrdersWaitProcessedTesoriereShort'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_get_WAIT_PROCESSED_TESORIERE&delivery_id='.$results['Delivery']['id'],array('class' => $position_img.' actionReloadFoward','title' => '')).'</li>';
echo "\r\n";
echo  '<li>'.$this->Html->link(__('OrdersProcessedTesoriereShort'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_get_PROCESSED_TESORIERE&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionConfig')).'</li>';
echo "\r\n";
echo  '<li>'.$this->Html->link(__('OrdersToRequestPaymentShort'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_get_TO_REQUEST_PAYMENT&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionConfig')).'</li>';
echo "\r\n";
echo  '<li>'.$this->Html->link(__('OrdersToPaymentShort'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=RequestPayments&action=index&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionPay')).'</li>';
echo "\r\n";
/*
 echo  '<li>'.$this->Html->link(__('Export Docs to delivery'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Docs&action=tesoriereDocsExport&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionPrinter')).'</li>';
 echo "\r\n";
*/
if($user->organization['Template']['orderSupplierPaid']=='Y') {
	echo  '<li>'.$this->Html->link(__('Pay Suppliers'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionPrice')).'</li>';
	echo  '<li>'.$this->Html->link(__('Pays Supplier'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers_by_supplier&delivery_id='.$results['Delivery']['id'], array('class' => $position_img.' actionPrice')).'</li>';
}
echo '</ul>';
echo '</ul>';
?>