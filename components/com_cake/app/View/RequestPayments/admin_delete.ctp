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
echo $this->Form->create('RequestPayment',array('id' => 'formGas'));
?>
	<fieldset>
		<legend><?php echo __('Title Delete Request Payment'); ?></legend>

		<div class="input text"><label for=""><?php echo __('request_payment_num');?> </label><?php echo $requestPaymentResults['RequestPayment']['num'];?></div>

		<div class="input text"><label for=""><?php echo __('stato_elaborazione');?></label><?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']);?> <span style="padding-left: 20px;" title="<?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']);?>" class="stato_<?php echo strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']);?>"></span></li></div>

		<?php include('box_detail.ctp');?>

		<?php 
		if(empty($summaryPaymentResults))
			echo $this->Element('boxMsg',array('msg' => "Nessun utente ha ancora effettuato il pagamento")); 
		else {
			echo $this->Element('boxMsg',array('msg' => "Utenti che hanno già effettuato il pagamento")); 
			
			?>
			<table cellpadding="0" cellspacing="0">
			<tr>
					<th>Utente</th>
					<th>Importo dovuto</th>
					<th>Importo pagato</th>
					<th>Stato</th>
					<th>Modalità</th>
					<th>Data del pagamento</th>
			</tr>			
			<?php
			foreach($summaryPaymentResults as $summaryPaymentResult) {
			
				$importo_dovuto = number_format($summaryPaymentResult['SummaryPayment']['importo_dovuto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$importo_pagato = number_format($summaryPaymentResult['SummaryPayment']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
				if(!empty($summaryPaymentResult['SummaryPayment']['created']) && $summaryPaymentResult['SummaryPayment']['created']!='0000-00-00 00:00:00') 
					$created = $this->Time->i18nFormat($summaryPaymentResult['SummaryPayment']['created'],"%A %e %B");
				else
					$created = "";
						
				echo "\r\n";
				echo '<tr>';
				echo '<td>'.$summaryPaymentResult['User']['name'].'</td>';
				echo '<td>'.$importo_dovuto.'&nbsp;&euro;</td>';
				echo '<td>'.$importo_pagato.'&nbsp;&euro;</td>';
				echo '<td>'.$this->App->traslateEnum($summaryPaymentResult['SummaryPayment']['stato']).'</td>';
				echo '<td>'.$this->App->traslateEnum($summaryPaymentResult['SummaryPayment']['modalita']).'</td>';
				echo '<td>'.$created.'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}		
		?>

</fieldset>
<?php 
echo $this->Form->hidden('request_payment_id',array('value' => $requestPaymentResults['RequestPayment']['id']));

echo $this->Form->end(__('Submit Delete'));
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