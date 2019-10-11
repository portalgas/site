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
$this->Html->addCrumb(__('Delete Request Payments Order'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if (!empty($user_ids_just_saldato_summary_payments)) {

	echo $this->Element('boxMsg',array('msg' => "I seguenti gasisti hanno già saldato la richiesta di pagamento: non si può eliminare l'ordine"));

	echo '<div class="table-responsive"><table class="table table-hover">';
	
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th colspan="2">'.__('Name').'</th>';
	echo '<th>'.__('Mail').'</th>';
	//echo '<th>'.__('importo').'</th>';
	echo '<th>'.__('Importo_pagato').'</th>';
	//echo '<th>'.__('modalita').'</th>';
	//echo '<th>'.__('saldato a').'</th>';
	echo '</tr>';
	
	foreach ($user_ids_just_saldato_summary_payments as $numResult => $user_ids_just_saldato_summary_payment) {

		echo '<tr>';
		echo '<td>'.($numResult+1).'</td>';
		echo '<td>'.$this->App->drawUserAvatar($user, $user_ids_just_saldato_summary_payment['User']['id'], $user_ids_just_saldato_summary_payment['User']).'</td>';
		echo '<td>'.$user_ids_just_saldato_summary_payment['User']['name'].'</td>';
		echo '<td>';  	
			if(!empty($user_ids_just_saldato_summary_payment['User']['email'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$user_ids_just_saldato_summary_payment['User']['email'].'">'.$user_ids_just_saldato_summary_payment['User']['email'].'</a>';
		echo '</td>';
		//echo '<td>'.$user_ids_just_saldato_summary_payment['SummaryPayment']['importo_e'].'</td>';
		echo '<td>'.$user_ids_just_saldato_summary_payment['SummaryPayment']['importo_pagato_e'].'</td>';
		//echo '<td>'.$this->App->traslateEnum($user_ids_just_saldato_summary_payment['SummaryPayment']['modalita']).'</td>';
		//echo '<td>'.$user_ids_just_saldato_summary_payment['SummaryPayment']['saldato_a'].'</td>';
		echo '</tr>';
	} // loops  
		
	
	echo '</table></div>';	
	
	
} // end if (!empty($user_ids_just_saldato_summary_payments)) 
	


if (!empty($user_ids_just_saldato_summary_orders)) {

	echo $this->Element('boxMsg',array('msg' => "I seguenti pagamenti dell'ordine saranno eliminati "));

	echo '<div class="table-responsive"><table class="table table-hover">';
	
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th colspan="2">'.__('Name').'</th>';
	echo '<th>'.__('Mail').'</th>';
	//echo '<th>'.__('importo').'</th>';
	echo '<th>'.__('Importo_pagato').'</th>';
	//echo '<th>'.__('modalita').'</th>';
	//echo '<th>'.__('saldato a').'</th>';
	echo '</tr>';
	
	foreach ($user_ids_just_saldato_summary_orders as $numResult => $user_ids_just_saldato_summary_order) {

		echo '<tr>';
		echo '<td>'.($numResult+1).'</td>';
		echo '<td>'.$this->App->drawUserAvatar($user, $user_ids_just_saldato_summary_order['User']['id'], $user_ids_just_saldato_summary_order['User']).'</td>';
		echo '<td>'.$user_ids_just_saldato_summary_order['User']['name'].'</td>';
		echo '<td>';  	
			if(!empty($user_ids_just_saldato_summary_order['User']['email'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$user_ids_just_saldato_summary_order['User']['email'].'">'.$user_ids_just_saldato_summary_order['User']['email'].'</a>';
		echo '</td>';
		//echo '<td>'.$user_ids_just_saldato_summary_order['SummaryOrder']['importo_e'].'</td>';
		echo '<td>'.$user_ids_just_saldato_summary_order['SummaryOrder']['importo_pagato_e'].'</td>';
		//echo '<td>'.$this->App->traslateEnum($user_ids_just_saldato_summary_order['SummaryOrder']['modalita']).'</td>';
		//echo '<td>'.$user_ids_just_saldato_summary_order['SummaryOrder']['saldato_a'].'</td>';
		echo '</tr>';
	} // loops  
		
	
	echo '<tr>';
	echo '<td colspan="5">';
	echo '<p class="pull-right">';
	echo __('Submit Delete').' '.$this->Html->link(null, ['controller' => 'RequestPayments' ,'action' => 'delete_order', $id], ['class' => 'action actionDelete', 'title' => __('Submit Delete')]);	
	echo '</p>';
	echo '</td>';	
	echo '</tr>';
	
	echo '</table></div>';	
	
} // end if (!empty($user_ids_just_saldato_summary_orders)) 
?>