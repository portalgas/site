<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if($isReferenteTesoriere)  {
	$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
}
else {
	if(!isset($delivery_id)) $delivery_id = 0;
		$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
}
$this->Html->addCrumb(__('List Request Payments'), array('controller' => 'RequestPayments', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Request Payments'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

    echo '<h2 class="ico-pay">';
    echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$tot_importo.' &euro;';
    echo '(';
    echo 'creata '.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['created'],"%A %e %B %Y");
    if($requestPaymentResults['RequestPayment']['data_send']!==Configure::read('DB.field.date.empty', '1970-01-01'))
        echo ' - inviata '.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['data_send'],"%A %e %B %Y");
    echo ')';
    echo '<span style="float:right;">';
	echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']).'"></span>';
	echo '</span>';
	echo '</h2>';

	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "La richiesta di pagamento non ha ancora delle voci associate"));

echo '</div>'; // end contentMenuLaterale

$options = [];
echo $this->MenuRequestPayment->drawWrapper($requestPaymentResults['RequestPayment']['id'], $options);
?>