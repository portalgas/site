<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$resultsOrder['Order']['id']));
$this->Html->addCrumb(__('Request Payment').': '.__('Supplier').' <b>'.$resultsOrder['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>'.$resultsOrder['Delivery']['luogo'].'</b>');
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="requestPayment form">';

/*
echo '<table cellpadding = "0" cellspacing = "0">';
echo '<tr>';
echo '	<th style="border-radius:5px;">'.$this->App->drawOrdersStateDiv($resultsOrder).'&nbsp;'.__($resultsOrder['Order']['state_code'].'-label').'</th>';
echo '</tr>';
echo '</table>';
*/
if(!empty($results)) {
?>
	<h2 class="ico-pay">
		<?php 
			echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$this->Time->i18nFormat($results['RequestPayment']['data_send'],"%A %e %B %Y");
			echo '<span style="float:right;">'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$results['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$results['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($results['RequestPayment']['stato_elaborazione']).'"></span></span>';
		?>
	</h2>

	<?php
	 if(count($results['Order'])>0) { ?>
		<h2>Richiesta di pagamento di ordini</h2>
		<table cellpadding = "0" cellspacing = "0">
			<tr>
				<th><?php echo __('Delivery');?></th>
				<th><?php echo __('Supplier');?></th>
				<th><?php echo __('Referenti'); ?></th>
			</tr>
			<?php
			$delivery_id_old=0;
			foreach($results['Order'] as $i => $requestPaymentsOrder) {
			
				echo '<tr ';
				if($requestPaymentsOrder['Order']['id']==$resultsOrder['Order']['id'])
					echo "style='background-color:yellow;'";
				echo '>';
				
				// echo '<td>'.($i+1).'</td>';
				if($requestPaymentsOrder['Delivery']['id']!=$delivery_id_old)
					echo '<td>'.$requestPaymentsOrder['Delivery']['luogo'].', del '.$this->Time->i18nFormat($requestPaymentsOrder['Delivery']['data'],"%A %e %B %Y").'</td>';
				else
					echo '<td></td>';
				
				echo '<td>'.$requestPaymentsOrder['SuppliersOrganization']['name'].'</td>';
				echo '<td>';
			
				if(!empty($requestPaymentsOrder['Referenti'])) {
					foreach ($requestPaymentsOrder['Referenti'] as $ref) {
						$referente = $ref['User'];
						echo $referente['name'];
						if(!empty($referente['email']))	echo ' <a class="fa fa-envelope-o fa-lg" title="'.__('Email send').'" target="_blank" href="mailto:'.$this->App->getPublicMail($user,$referente['email']).'"></a>';
						echo '<br />';
					}
				}
				echo '</td>';
			echo '</tr>';
			
				$delivery_id_old = $requestPaymentsOrder['Delivery']['id'];
			} // end foreach($results['Order'] as $i => $requestPaymentsOrder) 
			?>
		</table>
	<?php } ?>
	
	<?php 
	/*
	 * faccio solo vedere le richieste d'ordine perche' accede il referente
	 * 
	if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
		if(!empty($results['Storeroom'])) { ?>
		<h2>Richiesta di pagamento di articoli in dispensa</h2>			
		<table cellpadding = "0" cellspacing = "0">
			<tr>
				<th><?php echo __('N');?></th>
				<th><?php echo __('Delivery');?></th>
				<th>Creata</th>
			</tr>
			<?php
			foreach($results['Storeroom'] as $i => $requestPaymentsStoreroomResult) {
			
				echo '<tr>';
				echo '<td>'.($i+1).'</td>';
				echo '<td>'.$requestPaymentsStoreroomResult['Delivery']['luogo'].', di '.$this->Time->i18nFormat($requestPaymentsStoreroomResult['Delivery']['data'],"%A %e %B %Y").'</td>';
				echo '<td>'.$this->App->formatDateCreatedModifier($requestPaymentsStoreroomResult['Delivery']['created']).'</td>';
				echo '</tr>';
			} // end foreach($results['Order'] as $i => $requestPaymentsOrder) 
			
			echo '</table>';
		 } // end if(!empty($results['Storeroom'])) 
	} // end if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y')
	?>
	
	<?php 
	 if(!empty($results['PaymentsGeneric'])) { ?>
	<h2>Richiesta di pagamento di nuove voci di spesa</h2>
	<table cellpadding = "0" cellspacing = "0">
		<tr>
			<th><?php echo __('N');?></th>
			<th>Voce di spesa</th>
			<th>Importo</th>
			<th>Creata</th>
		</tr>
		<?php
		foreach($results['PaymentsGeneric'] as $i => $requestPaymentsGenericResult) {
		?>
		<tr>
			<td><?php echo ($i+1);?></td>
			<td><?php echo $requestPaymentsGenericResult['RequestPaymentsGeneric']['name']; ?></td>
			<td><?php echo $requestPaymentsGenericResult['RequestPaymentsGeneric']['importo']; ?>&nbsp;&euro;</td>
			<td><?php echo $this->App->formatDateCreatedModifier($requestPaymentsGenericResult['RequestPaymentsGeneric']['created']); ?></td>
		</tr>
		<?php 
		} 
		?>
	</table>
	<?php
	 } // end if(!empty($results['PaymentsGeneric'])) 		
	*/
}
else
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non è stata trovata la richiesta di pagamento associata!"));
	?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>

	<div id="order-sotto-menu-<?php echo $resultsOrder['Order']['id'];?>" style="clear: both;"></div>
	
</div>

<script type="text/javascript">
viewOrderSottoMenu(<?php echo $resultsOrder['Order']['id'];?>, "bgLeft");
</script>

<style type="text/css">
.cakeContainer div.form, .cakeContainer div.index, .cakeContainer div.view {
    width: 74%;
}
.cakeContainer div.actions {
    width: 25%;
}
</style>