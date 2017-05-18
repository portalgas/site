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
			echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$this->Time->i18nFormat($results['RequestPayment']['data_send'],"%A %e %B %Y");
			echo '<span style="float:right;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$results['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$results['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($results['RequestPayment']['stato_elaborazione']).'"></span></span>';
		?>
	</h2>

	<?php include('box_detail.ctp');?>
		
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th><?php echo __('Name');?></th>
			<th><?php echo __('mail');?></th>
			<th>Importo dovuto</th>
			<th>Importo pagato</th>
			<th>Modalit&agrave;</th>
	</tr>			
	<?php 
		$tabindex = 1;
		foreach($results['SummaryPayment'] as $num => $summaryPayment) {
	
			echo '<tr class="view">';
			echo '<td><a action="request_payment_referent_to_users-'.$requestPaymentResults['RequestPayment']['id'].'_'.$summaryPayment['User']['id'].'" class="actionTrView openTrView" href="#"  title="'.__('Href_title_expand').'"></a></td>';
			echo '<td>'.($num+1).'</td>';
			echo '<td>'.$summaryPayment['User']['name'].'</td>';
			echo '<td>'.$summaryPayment['User']['email'].'</td>';
			echo '<td>'.$summaryPayment['SummaryPayment']['importo_dovuto_e'].'</td>';
		
			echo '<td ';
			if($summaryPayment['SummaryPayment']['importo_dovuto']==$summaryPayment['SummaryPayment']['importo_pagato']) 
				echo 'style="background-color:green;"';
			else
				echo 'style="background-color:red;"';
			echo '>';
			
			echo $summaryPayment['SummaryPayment']['importo_pagato_e'];
			echo '</td>';
		?>
		<td>
			<?php echo $this->App->traslateEnum($summaryPayment['SummaryPayment']['modalita']); ?>
		</td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $requestPaymentResults['RequestPayment']['id'];?>_<?php echo $summaryPayment['User']['id']; ?>">
		<td colspan="2"></td>
		<td colspan="5" id="tdViewId-<?php echo $requestPaymentResults['RequestPayment']['id'];?>_<?php echo $summaryPayment['User']['id']; ?>"></td>
	</tr>		
	<?php
		}
	?>
	</table>
	
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