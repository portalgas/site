<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Request Payments'), array('controller' => 'RequestPayments', 'action' => 'index'));
$this->Html->addCrumb('Coerenza dati');
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
echo '<div class="requestPayment">';


echo '<h2 class="ico-pay">';
echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['created'],"%A %e %B %Y");
echo '</h2>';

if(!empty($results)) {

	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '	<th></th>';
	echo '	<th>'.__('N').'</th>';
	echo '	<th>'.__('Delivery').'</th>';
	echo '	<th>'.__('SupplierOrganizations').'</th>';
	echo '  <th>'.__('StatoElaborazione').'</th>';
	echo '  <th>Totale importo dell\'ordine</th>';
	echo '  <th>Totale importo dovuto<br />(somma degli importi degli utenti)</th>';
	echo '  <th>Delta</th>';
	echo '	<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';
	
	$tot_importo = 0;
	$tot_importo_rimborsate = 0;
	$tot_delta = 0;
	foreach ($results as $numResult => $result) {
	
			$tot_importo_rimborsate_e =  number_format($result['Order']['tot_importo_rimborsate'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			$delta_e =  number_format($result['Order']['delta'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
	 
			echo '<tr class="view">';
			echo '	<td><a action="orders-'.$result['Order']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
			echo '	<td>'.((int)$numResult+1).'</td>';
			
			echo '	<td>';
			if($result['Delivery']['sys']=='N') {
				if($delivery_link_permission)
					echo __('Delivery').': '.$this->Html->link($result['Delivery']['luogoData'], array('controller' => 'deliveries', 'action' => 'edit', null, 'delivery_id='.$result['Delivery']['id']),array('title'=>__('Edit Delivery')));
				else
					echo __('Delivery').': '.$result['Delivery']['luogoData'];
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				if($result['Delivery']['daysToEndConsegna']<0) {
					echo '<span style="color:red;">Chiusa</span>';
				}
				else {
					echo '<span style="color:green;">Aperta';
					if($result['Delivery']['daysToEndConsegna']==0) echo '(scade oggi)';
					else echo '(mancano ancora '.$result['Delivery']['daysToEndConsegna'].'&nbsp;gg&nbsp;alla&nbsp;consegna)';
					echo '</span>';
				}
			}
			else 
				echo __('Delivery').': '.h($result['Delivery']['luogo']);
				
			echo '	</td>';
			
			echo '	<td>';
			echo $result['SuppliersOrganization']['name'];
			echo '	</td>';

			echo '<td>';		 
			echo $this->App->drawOrdersStateDiv($result);
			echo '&nbsp;';
			echo __($result['Order']['state_code'].'-label');
			echo '</td>';			



			echo '<td>';		 
			echo $result['Order']['tot_importo_e'];
			echo '</td>';
			echo '<td>';		 
			echo $tot_importo_rimborsate_e;
			echo '</td>';
			
			if($result['Order']['delta'] == 0)
					$style = 'background-color:green;';
				else
					$style = 'background-color:red;';			
			echo '<td style="color:#fff;'.$style.'">';
			echo $delta_e;
			echo '</td>';
			
			echo '<td class="actions-table-img-3">';
			
			if($result['Order']['isVisibleBackOffice']=='N') 
				echo $this->Html->link(null, array('action' => 'edit', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionEdit','title' => __('Edit Order')));
			else {
				if($result['Order']['state_code']=='CREATE-INCOMPLETE' || $result['Order']['state_code']=='OPEN-NEXT' || $result['Order']['state_code']=='OPEN' || $result['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY') 
					echo $this->Html->link(null, array('action' => 'edit', null, 'order_id='.$result['Order']['id']),array('class' => 'action actionEdit','title' => __('Edit Order')));
				
				//if($result['Order']['permissionToEditReferente']) {
				echo $this->Html->link(null, array('action' => 'home', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionWorkflow','title' => __('Order home')));

				echo '<a id="actionMenu-'.$result['Order']['id'].'" class="action actionMenu" title="'.__('Expand menu').'"></a>';
				echo '<div class="menuDetails" id="menuDetails-'.$result['Order']['id'].'" style="display:none;">';
				echo '	<a class="menuDetailsClose" id="menuDetailsClose-'.$result['Order']['id'].'"></a>';
				echo '<div id="order-sotto-menu-'.$result['Order']['id'].'"></div>';
				echo '</div>';
			} // end if($results['Order']['isVisibleBackOffice']=='N')
			echo '</td>';
	
		echo '</tr>';
		
		echo '<tr class="trView" id="trViewId-'.$result['Order']['id'].'">';
		echo '	<td colspan="2"></td>'; 
		echo '	<td colspan="8" id="tdViewId-'.$result['Order']['id'].'"></td>';
		echo '</tr>';
		
		$tot_importo += $result['Order']['tot_importo'];
		$tot_importo_rimborsate += $result['Order']['tot_importo_rimborsate'];
		$tot_delta += $result['Order']['delta'];
	}

	$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_rimborsate = number_format($tot_importo_rimborsate,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_delta = number_format($tot_delta,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	echo '<tr>';
	echo '	<td colspan="5" style="text-align:right;">Totali</td>'; 
	echo '	<td>'.$tot_importo.'&nbsp;&euro;</td>';
	echo '	<td>'.$tot_importo_rimborsate.'&nbsp;&euro;</td>';
	echo '	<td>'.$tot_delta.'&nbsp;&euro;</td>';
	echo '	<td></td>'; 
	echo '</tr>';
		
	echo '</table>';	
} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora ordini registrati"));

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
	$(".actionMenu").each(function () {
		$(this).click(function() {

			$('.menuDetails').css('display','none');
			
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).show();

			viewOrderSottoMenu(numRow,"bgLeft");

			var offset = $(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			$('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	$(".menuDetailsClose").each(function () {
		$(this).click(function() {
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).hide('slow');
		});
	});	
});
</script>