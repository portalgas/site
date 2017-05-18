<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
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

echo '<div class="requestPayment form">';
?>
		
	<h2 class="ico-pay">
		<?php 
			echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$this->Time->i18nFormat($results['RequestPayment']['created'],"%A %e %B %Y");
			echo '<span style="float:right;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$results['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$results['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($results['RequestPayment']['stato_elaborazione']).'"></span></span>';
		?>
	</h2>

	<?php
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "La richiesta di pagamento non ha ancora delle voci associate"));
	?>
	
</div>

<div class="actions">
	<?php include(Configure::read('App.root').Configure::read('App.component.base').'/View/RequestPayments/admin_sotto_menu.ctp');?>		
</div>


<script type="text/javascript">
<?php
if($isReferenteTesoriere) 
	echo 'viewReferenteTesoriereSottoMenu("0", "bgLeft");';
else
	echo 'viewTesoriereSottoMenu("0", "bgLeft");';
?>
</script>



<style type="text/css">
.cakeContainer div.form, .cakeContainer div.index, .cakeContainer div.view {
    width: 74%;
    padding-left: 5px;    
}
.cakeContainer div.actions {
    width: 25%;
}
</style>