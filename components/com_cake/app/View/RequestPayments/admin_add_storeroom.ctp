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
$this->Html->addCrumb(__('Add Request Payments Storeroom'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale form">';

	echo '<h2 class="ico-pay">';
	echo __('Add Request Payments Storeroom')." alla ".__('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$tot_importo.' &euro; ('.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['created'],"%A %e %B %Y").')';
	echo '<span style="float:right;">';
	echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']).'"></span>';
	echo '</span>';
	echo '</h2>';
	

if(empty($deliveries))
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono consegne alle quali si può richiedere il pagamento degli articoli in dispensa."));
else {

	echo $this->Form->create('FilterRequestPayment',array('id'=>'formGasFilter','type'=>'get'));	
	echo '<fieldset class="filter">';
	echo '<legend>'.__('Filter RequestPayment').'</legend>';
	echo '<div class="row">';
	echo '<div class="col-md-8">';
	echo $this->Form->input('delivery_id',array('label' => '&nbsp;', 'options' => $deliveries, 'empty' => 'Filtra per consegne', 'name'=>'FilterRequestPaymentDeliveryId','default'=>$FilterRequestPaymentDeliveryId,'escape' => false));
	echo '</div>';	
	echo '<div class="col-md-2">';	
	echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset'));
	echo '</div>';	
	echo '<div class="col-md-2">';
	echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none')));
	echo '</div>';	
	echo '</div>';
	echo '</fieldset>';
		
if(!empty($results)) {
	echo $this->Form->create('RequestPayment',array('id' => 'formGas'));
	
	echo '<fieldset>';
	
	echo '<div class="requestPayment">';
	
		$delivery_id_old = 0;
		$user_id_old = 0;
		$count=0;
		$tot_importo_storeroom=0;
		foreach ($results as $i => $result) { 
	
			if($result['Storeroom']['delivery_id']!=$delivery_id_old) {
				$count=1;
				if($i>0) echo '</table>';
				
				echo '<table cellpadding="0" cellspacing="0">';
				echo '<tr>';
				echo '<th>'.__('N').'</th>';
				echo '<th>'.__('Supplier').'</th>';
				echo '<th>'.__('Name').'</th>';
				echo '<th>'.__('Conf').'</th>';
				echo '<th>'.__('PrezzoUnita').'</th>';
				echo '<th>'.__('Prezzo/UM').'</th>';
				echo '<th>'.__('qta').'</th>';
				echo '<th>'.__('Importo').'</th>';
				echo '<th>'.__('Created').'</th>';
				echo '</tr>';
			}
			else 
				$count++;
	
		
			if($result['Storeroom']['user_id']!=$user_id_old) {
				echo '<tr>';
				echo '<td colspan="9" class="trGroup">Utente: '.$result['User']['name'];
				echo '</td>';
				echo '</tr>';
			}
			
			echo '<tr>';
			echo '<td>'.($count).'</td>';
			echo '<td>';
			echo $result['SuppliersOrganization']['SuppliersOrganization']['name'];
			echo '</td>';
			echo '<td>'; 
			if($result['Storeroom']['stato']=='LOCK') echo '<span class="stato_lock"></span> ';
				echo $result['Storeroom']['name'];
			echo '</td>';
			echo '<td>'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
			echo '<td>'.$result['Storeroom']['prezzo_e'].'</td>';
			echo '<td>'.$this->App->getArticlePrezzoUM($result['Storeroom']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
			echo '<td>'.$result['Storeroom']['qta'].'</td>';
			echo '<td>'.$this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']).'</td>';
			echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Storeroom']['created']).'</td>';
			echo '</tr>';
	
			$delivery_id_old=$result['Storeroom']['delivery_id'];
			$user_id_old=$result['Storeroom']['user_id'];
			
			$tot_importo_storeroom += ($result['Storeroom']['prezzo'] * $result['Storeroom']['qta']);
		} // end loop Storeroom
		
		/* 
		 * TOTALE STOREROOM
		 */
		 $tot_importo_storeroom = number_format($tot_importo_storeroom,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		 
		echo '<tr>';
		echo '<th colspan="7"></th>';
		echo '<th>'.$tot_importo_storeroom.'&nbsp;&euro;</th>';
		echo '<th></th>';
		echo '</tr>';		 		
		echo '</table>';	
	echo '</div>';

	echo '</fieldset>';
	
	echo $this->Form->hidden('delivery_id',array('value' => $result['Storeroom']['delivery_id']));
	echo $this->Form->hidden('request_payment_id',array('value' => $requestPaymentResults['RequestPayment']['id']));
	echo $this->Form->end(__('Add Request Payments Storeroom')." alla richiesta numero ".$requestPaymentResults['RequestPayment']['num']);
} // end if(!empty($results))
else 
if($resultsFound=='N') 
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));

} // end if(empty($deliveries))	
	
echo '</div>'; // end contentMenuLaterale

$options = [];
echo $this->MenuRequestPayment->drawWrapper($requestPaymentResults['RequestPayment']['id'], $options);
?>