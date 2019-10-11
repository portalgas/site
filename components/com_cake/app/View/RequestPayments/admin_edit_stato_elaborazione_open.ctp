<?php$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);if($isReferenteTesoriere)  {	$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));	if(isset($order_id))		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));}else {	if(!isset($delivery_id)) $delivery_id = 0;		$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));}$this->Html->addCrumb(__('List Request Payments'), array('controller' => 'RequestPayments', 'action' => 'index'));$this->Html->addCrumb(__('Edit Request Payments'));echo $this->Html->getCrumbList(array('class'=>'crumbs'));echo '<div class="contentMenuLaterale form">';	echo '<h2 class="ico-pay">';	echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$tot_importo.' &euro; ('.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['created'],"%A %e %B %Y").')';	echo '<span style="float:right;">';	echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']).'"></span>';	echo '</span>';	echo '</h2>';			echo $this->Form->create('RequestPayment',array('id' => 'formGas'));	echo '<fieldset>';			echo '<div class="submit">';	echo '<label for="order_id">Azioni</label>';	echo '<div style="margin-left: 210px;">';		echo '<div class="table-responsive"><table class="table">';	echo '<tr>';	echo '<td>'; 	echo '<div class="submit"><input id="sumbitToWait" type="submit" class="buttonBlu" value="Riporta \'in lavorazione\'" /></div>';	echo '</td>';	/*	 * non lo permetto +, la rich si chiudo in automatico se tutti gli ordini sono CLOSE	echo '<td>';	echo '<div class="submit"><input id="sumbitToClose" type="submit" value="Chiudi la richiesta" /></div>';	echo '</td>';	*/	echo '</tr>';	echo '</table></div>';		echo '</fieldset>';				echo $this->Form->hidden('request_payment_id',array('value' => $requestPaymentResults['RequestPayment']['id']));	echo $this->Form->hidden('action_submit', array('id' => 'action_submit', 'value' => ''));	echo $this->Form->end();	echo '<div class="clearfix"></div>';		echo $this->element('legendaRequestPaymentStato');echo '</div>'; // end contentMenuLaterale$options = [];echo $this->MenuRequestPayment->drawWrapper($requestPaymentResults['RequestPayment']['id'], $options);?><script type="text/javascript">$(document).ready(function() {	$('#sumbitToWait').click(function() {		$('#action_submit').val('toWait');		return true;	});		$('#sumbitToClose').click(function() {		$('#action_submit').val('toClose');		return true;	});	});	</script>