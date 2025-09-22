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

    $tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

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
		
	include('box_detail.ctp');
	?>
		
	<div class="table-responsive"><table class="table table-hover">
		<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th><?php echo __('Name');?></th>
			<th><?php echo __('mail');?></th>
			<th style="text-align:center;"><?php echo __('Importo_dovuto');?></th>
			<th style="text-align:center;"><?php echo __('Importo_pagato');?></th>
			<th style="text-align:center;"><?php echo __('Stato');?></th>
			<th><?php echo __('Modality');?></th>
	</tr>			
	<?php 
		$tabindex = 1;
		foreach($results['SummaryPayment'] as $num => $summaryPayment) {

            // if($summaryPayment['SummaryPayment']['id']== 44713) debug($summaryPayment);

			echo '<tr class="view">';
			echo '<td><a action="request_payment_referent_to_users-'.$requestPaymentResults['RequestPayment']['id'].'_'.$summaryPayment['User']['id'].'" class="actionTrView openTrView" href="#"  title="'.__('Href_title_expand').'"></a></td>';
			echo '<td>'.($num+1).'</td>';
			echo '<td>'.$summaryPayment['User']['name'].'</td>';
			echo '<td>'.$summaryPayment['User']['email'].'</td>';
			echo '<td style="text-align:center;">'.$summaryPayment['SummaryPayment']['importo_dovuto_e'].'</td>';
		
			echo '<td style="text-align:center;color:#fff;';
			/*
			 * posso pagare + di quello che devo se ho un debito di cassa
			 */
			if($summaryPayment['SummaryPayment']['importo_dovuto']<=$summaryPayment['SummaryPayment']['importo_pagato']) 
				echo 'background-color:green;"';
			else
				echo 'background-color:red;"';
			echo '>';
			
			echo $summaryPayment['SummaryPayment']['importo_pagato_e'];
			echo '</td>';

		 	echo '<td style="text-align:center;color:#fff;';
			switch ($summaryPayment['SummaryPayment']['stato']) {
				case 'DAPAGARE':
					echo 'background-color:red;"';
				break;
				case 'SOLLECITO1':
					echo 'background-color:yellow;"';
				break;
				case 'SOLLECITO2':
					echo 'background-color:yellow;"';
				break;
				case 'SOSPESO':
					echo 'background-color:gray;"';
				break;
				case 'PAGATO':
					echo 'background-color:green;"';
				break;
			}
            echo '>';
            echo $this->App->traslateEnum($summaryPayment['SummaryPayment']['stato']);
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
	echo '</table></div>';
	
echo '</div>'; // end contentMenuLaterale

$options = [];
echo $this->MenuRequestPayment->drawWrapper($requestPaymentResults['RequestPayment']['id'], $options);
?>