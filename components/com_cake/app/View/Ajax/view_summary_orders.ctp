<?php
echo '<div class="related">';
echo '<h3 class="title_details">';
echo __('Related SummaryOrders');
echo '</h3>';

echo '<p>';
echo '<ul>';
echo '<li><b>k_summary_order</b>: dati aggregati per gasista per un ordine</li>';
echo '<li><b>k_summary_payments</b>: dati aggregati per gasista per richiesta di pagamento (può includere ordini + dispensa  + voci di spesa)</li>';
echo '</ul>';
echo '</p>';

if (!empty($results)) {
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th></th>';

	if($orderResults['Order']['hasTrasport']=='Y')
		echo '<th colspan="2"></th>';
	if($orderResults['Order']['hasCostMore']=='Y')
		echo '<th colspan="2"></th>';
	if($orderResults['Order']['hasCostLess']=='Y')
		echo '<th colspan="2"></th>';
	
	echo '<th colspan="4" style="text-align:center;background-color:#ccc;">k_summary_orders</th>';
	echo '<th style="width:1px;background-color:#999;"></th>';
	echo '<th colspan="6" style="text-align:center;background-color:#ccc;">k_summary_payments</th>';
	echo '</tr>';
	
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th>'.__('Name').'</th>';
	
	if($orderResults['Order']['hasTrasport']=='Y')
		echo '<th colspan="2">'.__('Trasport').'</th>';
	if($orderResults['Order']['hasCostMore']=='Y')
		echo '<th colspan="2">'.__('CostMore').'</th>';
	if($orderResults['Order']['hasCostLess']=='Y')
		echo '<th colspan="2">'.__('CostLess').'</th>';
		
	echo '<th>'.__('importo').'</th>';
	echo '<th>'.__('Importo_pagato').'</th>';
	echo '<th>'.__('modalita').'</th>';
	echo '<th>'.__('saldato a').'</th>';
	echo '<th style="width:1px;background-color:#999;"></th>';
	echo '<th>'.__('Importo_dovuto').'</th>';
	echo '<th>'.__('Importo_richiesto').'</th>';
	echo '<th>'.__('Importo_pagato').'</th>';
	echo '<th>'.__('modalita').'</th>';
	echo '<th>'.__('stato a').'</th>';
	echo '<th style="width:1px;"></th>';
	echo '</tr>';
	
	foreach ($results as $numResult => $result) {

		echo '<tr>';
		echo '<td>'.($numResult+1).'</td>';
		echo '<td>'.$result['User']['name'].' ('.$result['User']['id'].')</td>';

		if($orderResults['Order']['hasTrasport']=='Y') {
			echo '<td>'.$result['SummaryOrderTrasport']['importo_e'].'</td>';
			echo '<td>'.$result['SummaryOrderTrasport']['importo_trasport_e'].'</td>';
		}
		if($orderResults['Order']['hasCostMore']=='Y') {
			echo '<td>'.$result['SummaryOrderCostMore']['importo_e'].'</td>';
			echo '<td>'.$result['SummaryOrderCostMore']['importo_cost_more_e'].'</td>';
		}
		if($orderResults['Order']['hasCostLess']=='Y') {
			echo '<td>'.$result['SummaryOrderCostLess']['importo_e'].'</td>';
			echo '<td>'.$result['SummaryOrderCostLess']['importo_cost_less_e'].'</td>';
		}
	
		echo '<td style="text-align:center;">'.$result['SummaryOrder']['importo_e'].'</td>';
		echo '<td style="text-align:center;">'.$result['SummaryOrder']['importo_pagato_e'].'</td>';
		echo '<td>'.$this->App->traslateEnum($result['SummaryOrder']['modalita']).'</td>';
		echo '<td>'.$result['SummaryOrder']['saldato_a'].'</td>';
		
		echo '<td style="background-color:#999;"></td>';
		if(isset($result['SummaryPayment'])) {
			echo '<td style="text-align:center;">'.$result['SummaryPayment']['SummaryPayment']['importo_dovuto_e'].'</td>';
			echo '<td style="text-align:center;">'.$result['SummaryPayment']['SummaryPayment']['importo_richiesto_e'].'</td>';
			echo '<td style="text-align:center;">'.$result['SummaryPayment']['SummaryPayment']['importo_pagato_e'].'</td>';
			echo '<td>'.$this->App->traslateEnum($result['SummaryPayment']['SummaryPayment']['modalita']).'</td>';
			echo '<td>'.$this->App->traslateEnum($result['SummaryPayment']['SummaryPayment']['stato']).'</td>';

			if($result['SummaryPayment']['SummaryPayment']['stato']=='PAGATO')
				echo '<td style="background-color:green;"></td>';
			else
				echo '<td style="background-color:red;"></td>';
		}
		else
			echo '<td colspan="6"></td>';
		echo '</tr>';
	} // loops  
	
	/*
	 * eventuali gasisti della RequestPayment
	 */	
	if(isset($result['SummaryPaymentNotSummaryOrderResults'])) {
			
		echo '<tr>';
		echo '<th></th>';
		echo '<th colspan="5">Altri gasisti legati alla richiesta di pagamento ma non all\'ordine</th>';
		
		if($orderResults['Order']['hasTrasport']=='Y')
			echo '<th colspan="2"></th>';
		if($orderResults['Order']['hasCostMore']=='Y')
			echo '<th colspan="2"></th>';
		if($orderResults['Order']['hasCostLess']=='Y')
			echo '<th colspan="2"></th>';
		
		echo '<th style="width:1px;background-color:#999;"></th>';
		echo '<th colspan="5"></th>';
		echo '<th style="width:1px;"></th>';
		echo '</tr>';
		echo '<tr>';
		echo '<th>'.__('N').'</th>';
		echo '<th>'.__('Name').'</th>';
		
		if($orderResults['Order']['hasTrasport']=='Y')
			echo '<th colspan="2">'.__('Trasport').'</th>';
		if($orderResults['Order']['hasCostMore']=='Y')
			echo '<th colspan="2">'.__('CostMore').'</th>';
		if($orderResults['Order']['hasCostLess']=='Y')
			echo '<th colspan="2">'.__('CostLess').'</th>';
	
		echo '<th>'.__('importo').'</th>';
		echo '<th>'.__('Importo_pagato').'</th>';
		echo '<th>'.__('modalita').'</th>';
		echo '<th>'.__('saldato a').'</th>';
		echo '<th style="width:1px;background-color:#999;"></th>';
		echo '<th>'.__('Importo_dovuto').'</th>';
		echo '<th>'.__('Importo_richiesto').'</th>';
		echo '<th>'.__('Importo_pagato').'</th>';
		echo '<th>'.__('modalita').'</th>';
		echo '<th>'.__('stato a').'</th>';
		echo '<th style="width:1px;"></th>';
		echo '</tr>';
	
		foreach($result['SummaryPaymentNotSummaryOrderResults'] as $summaryPaymentNotSummaryOrderResult) {
			echo '<tr>';
			echo '<td>'.($numResult+1).'</td>';
			echo '<td>'.$summaryPaymentNotSummaryOrderResult['User']['name'].' ('.$summaryPaymentNotSummaryOrderResult['User']['id'].')</td>';
			
			if($orderResults['Order']['hasTrasport']=='Y')
				echo '<td colspan="2"></td>';
			if($orderResults['Order']['hasCostMore']=='Y')
				echo '<td colspan="2"></td>';
			if($orderResults['Order']['hasCostLess']=='Y')
				echo '<td colspan="2"></td>';
			
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			
			echo '<td style="background-color:#999;"></td>';
			echo '<td>'.$summaryPaymentNotSummaryOrderResult['SummaryPayment']['importo_dovuto_e'].'</td>';
			echo '<td>'.$summaryPaymentNotSummaryOrderResult['SummaryPayment']['importo_richiesto_e'].'</td>';
			echo '<td>'.$summaryPaymentNotSummaryOrderResult['SummaryPayment']['importo_pagato_e'].'</td>';
			echo '<td>'.$this->App->traslateEnum($summaryPaymentNotSummaryOrderResult['SummaryPayment']['modalita']).'</td>';
			echo '<td>'.$this->App->traslateEnum($summaryPaymentNotSummaryOrderResult['SummaryPayment']['stato']).'</td>';	
			if($summaryPaymentNotSummaryOrderResult['SummaryPayment']['stato']=='PAGATO')
				echo '<td style="background-color:green;"></td>';
			else
				echo '<td style="background-color:red;"></td>';			
			echo '</tr>';
			
			$numResult++;
		}
	} // end if(isset($result['SummaryPaymentNotSummaryOrderResults'])) 
	
	echo '</table></div>';
	
} // end if (!empty($results)) 
else
	echo $this->element('boxMsg',['class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora voci di pagamento"]);




if($user->organization['Template'] == 'POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
	if(!empty($requestPaymentResults)) { 
		echo '<div class="related">';
		echo '<h3 class="title_details">';
		echo __('Related RequestPayment Orders');
		echo '</h3>';

		$tot_orders = count($requestPaymentResults['Order']);
		$tot_generics = count($requestPaymentResults['PaymentsGeneric']);
		$tot_storeroom = count($requestPaymentResults['Storeroom']);

		if($tot_orders==0 && $tot_generics==0 && $tot_storeroom==0)
			echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono elementi associati"));

		if(count($requestPaymentResults['Order'])>0) { 
			
			echo '<h2>Richiesta di pagamento di ordini ('.$tot_orders.')</h2>';
			echo '<div class="table-responsive"><table class="table">';
			echo '<tr>';
			echo '<th>'.__('N').'</th>';
			echo '<th>'.__('Delivery').'</th>';
			echo '<th colspan="2">'.__('Supplier').'</th>';
			echo '</tr>';

				$delivery_id_old=0;
				$tot_importo = 0;
				foreach($requestPaymentResults['Order'] as $i => $result) {

					if($orderResults['Order']['id']==$result['Order']['id'])
						$css = 'background-color:yellow';
					else
						$css = '';
						
					echo '<tr style="'.$css.'">';
					echo '<td>'.($i+1).'</td>';
					if($result['Delivery']['id']!=$delivery_id_old)
						echo '<td>'.$result['Delivery']['luogo'].', del '.$this->Time->i18nFormat($result['Delivery']['data'],"%A %e %B %Y").'</td>';
					else
						echo '<td></td>';

					echo '<td>';
					if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';
					echo '</td>';
					echo '<td>'.$result['SuppliersOrganization']['name'].'</td>';
					echo '</tr>';
				
					$delivery_id_old = $result['Delivery']['id'];
				} // end foreach($requestPaymentResults['Order'] as $i => $result)

				echo '<tr>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th></th>';
				echo '</tr>';		
				
			echo '</table></div>';
		} 

		if(!empty($requestPaymentResults['PaymentsGeneric'])) { 

			echo '<h2>Richiesta di pagamento di nuove voci di spesa ('.$tot_generics.')</h2>';
			echo '<div class="table-responsive"><table class="table">';
			echo '<tr>';
			echo '<th>'.__('N').'</th>';
			echo '<th>Voce di spesa</th>';
			echo '<th colspan="2">'.__('User').'</th>';
			echo '<th>'.__('Importo').'</th>';
			echo '</tr>';

				$tot_importo = 0;
				foreach($requestPaymentResults['PaymentsGeneric'] as $i => $requestPaymentsGeneric) {
					echo '<tr>';
					echo '<td>'.($i+1).'</td>';
					echo '<td>'.$requestPaymentsGeneric['RequestPaymentsGeneric']['name'].'</td>';
					echo '<td>'.$requestPaymentsGeneric['User']['name'].'</td>';
					echo '<td>';
					if(!empty($requestPaymentsGeneric['User']['email']))
						echo '<a title="'.__('Email send').'" href="mailto:'.$requestPaymentsGeneric['User']['email'].'">'.$requestPaymentsGeneric['User']['email'].'</a>';
					echo '</td>';
					echo '<td>'.$requestPaymentsGeneric['RequestPaymentsGeneric']['importo'].'&nbsp;&euro;</td>';
					echo '</tr>';
				
					$tot_importo = ($tot_importo + $requestPaymentsGeneric['RequestPaymentsGeneric']['importo']);
				} 
				echo '<tr>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th></th>';
				echo '<th style="text-align:right;">'.('Totale').'</th>';
				echo '<th>';
				echo $tot_importo.'&nbsp;&euro;';
				echo '</th>';
				echo '</tr>';
				
				echo '</table></div>';
		} // end if(!empty($requestPaymentResults['PaymentsGeneric'])) 

	} // if (!empty($requestPaymentResults))
} // end if($user->organization['Template'] == 'POST' || $user->organization['Template']['payToDelivery']=='ON-POST') 
?>