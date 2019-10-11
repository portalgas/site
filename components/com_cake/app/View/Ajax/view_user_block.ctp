<div class="related">

<?php
if($user->organization['Template']['payToDelivery']=='POST') {

	echo '<h3 class="title_details">'.__('Related Requests Payments').'</h3>';

	if (!empty($results)):?>

	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo __('Request Payment');?></th>
			<th><?php echo __('importo_required');?></th>
			<th><?php echo __('Importo_pagato');?></th>
			<th>Stato</th>
			<th>Pagamento</th>
	</tr>			
	<?php 
		foreach($results as $num => $result) {
	?>
	<tr class="view">
		<td><?php echo $result['RequestPayment']['num']; ?> di <?php echo $this->Time->i18nFormat($result['RequestPayment']['data_send'],"%A %e %B %Y");?></td>
		<td><?php echo $result['SummaryPayment']['importo_dovuto_e']; ?></td>
		 <?php
		 	echo '<td id="color-'.$result['SummaryPayment']['id'].'" ';
			switch ($result['SummaryPayment']['stato']) {
				case 'DAPAGARE':
					echo 'style="background-color:red;"';
				break;
				case 'SOLLECITO1':
					echo 'style="background-color:yellow;"';
				break;
				case 'SOLLECITO2':
					echo 'style="background-color:yellow;"';
				break;
				case 'SOSPESO':
					echo 'style="background-color:gray;"';
				break;
				case 'PAGATO':
					echo 'style="background-color:green;"';
				break;
			}
			
			echo $result['SummaryPayment']['stato'].'>';
			
			echo $result['SummaryPayment']['importo_pagato_e'];
			?>
		</td>
		<td>
			<?php echo $this->App->traslateEnum($result['SummaryPayment']['stato']);?>
		</td>
		<td>
			<?php echo $this->App->traslateEnum($result['SummaryPayment']['modalita']);?>
		</td>
	</tr>	
	<?php
	}

	echo '</table>';
	
else: 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "L'utente non ha richieste di pagamento associate"));
endif; 

} // end if($user->organization['Template']['payToDelivery']=='POST') 



echo '<h3 class="title_details">'.__('Related Cash').'</h3>';

echo $this->element('boxCashUserTotaleImporto', array('cashResults' => $cashResults));

echo '</div>';
