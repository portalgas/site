<?php
echo "\r\n"; 
echo '<ul class="menuLateraleItems">';
echo "\r\n";
echo  '<li>'.$this->Html->link(__('OrdersToPayment'), Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=RequestPayments&action=index&delivery_id='.$results['Delivery']['id'].'&order_id='.$order_id, array('class' => $position_img.' actionPay')).'</li>';
echo '</ul>';
echo '</ul>';
?>