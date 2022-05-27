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
$this->Html->addCrumb(__('Add Request Payments Orders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

	echo '<h2 class="ico-pay">';
	echo __('Add Request Payments Orders')." alla ".__('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$tot_importo.' &euro; ('.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['created'],"%A %e %B %Y").')';
	echo '<span style="float:right;">';
	echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']).'"></span>';
	echo '</span>';
	echo '</h2>';
			
	/*
	 * ctrl se c'e' almeno un acquisto
	* */
	$numOrder=0;
	if(isset($results['Tab']))
	foreach($results['Tab'] as $numTabs => $tab) 
		foreach($tab['Delivery'] as $delivery)
			if($delivery['totOrders']>0) 
				foreach($delivery['Order'] as $order)  
					$numOrder++;
	
	
	if($numOrder>0) {
		
		echo $this->Form->create('RequestPayment',array('id' => 'formGas'));
		
		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<tbody>';
	
		foreach($results['Tab'] as $numTabs => $tab) {
		
			foreach($tab['Delivery'] as $numDelivery => $delivery) {
		
				if($delivery['totOrders']>0) {
		
					echo '<tr><td colspan="8" class="trGroup">'.__('Delivery').': ';
					if($delivery['sys']=='N')
						echo $delivery['luogoData'];
					else 
						echo $delivery['luogo'];
					echo '</td></tr>';
					
					echo '<thead>';
					echo '<tr>';
					echo '<th>'.__('N').'</th>';
					echo '<th><input type="checkbox" checked id="order_id_selected_all'.$delivery['id'].'" name="order_id_selected_all'.$delivery['id'].'" value="ALL" /></th>';
					echo '<th colspan="2">'.__('Supplier').'</th>';
					echo '<th>'.__('OrderDate').'</th>';
					echo '<th>'.__('Stato').'</th>';
					echo '<th  style="text-align:center;">'.__('Importo totale ordine').'</th>';
					echo '<th>'.__('Suppliers Organizations Referents').'</th>';
					echo '</tr>';
					echo '</thead>';
					
					foreach($delivery['Order'] as $numOrder => $order)  {
		
						if($order['Order']['data_inizio']!=Configure::read('DB.field.date.empty'))
							$data_inizio = $this->Time->i18nFormat($order['Order']['data_inizio'],"%A %e %B");
						else
							$data_inizio = "";
						
						if($order['Order']['data_fine']!=Configure::read('DB.field.date.empty'))
							$data_fine = $this->Time->i18nFormat($order['Order']['data_fine'],"%A %e %B");
						else
							$data_fine = "";

						if($order['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
							$data_fine = $this->Time->i18nFormat($order['Order']['data_fine_validation'],"%A %e %B");
						
						echo "\n";
						echo '<tr>';
						echo "\n";
						echo '<td>'.($numOrder+1).'</td>';
						echo "\n";
						echo '<td><input type="checkbox" checked id="'.$order['Order']['id'].'[order_id_selected]" name="order_id_selected" class="order_id_selected'.$delivery['id'].'" value="'.$order['Order']['id'].'" />';
						echo $this->Form->hidden('order_id',array('name'=>'data[RequestPayment]['.$order['Order']['id'].'][order_id]','value'=>$order['Order']['id']));
						echo '</td>';
						echo '	<td>';
						if(!empty($order['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$order['Supplier']['img1']))
							echo ' <img width="50" class="img-responsive-disabled" src="'.Configure::read('App.web.img.upload.content').'/'.$order['Supplier']['img1'].'" alt="'.$order['SupplierOrganization']['name'].'" /> ';
						echo '	</td>';						
						echo "\n";
						echo '<td>';
						echo $order['SuppliersOrganization']['name'];
						if(!empty($order['SuppliersOrganization']['descrizione'])) echo '/'.$order['SuppliersOrganization']['descrizione'];
						echo '</td>';
						echo "\n";
						echo '<td>Da '.$data_inizio;
						echo '<br />a '.$data_fine;
						echo '</td>';
						
						echo "\n";
						echo '<td>';
						echo $this->App->drawOrdersStateDiv($order);
						echo '&nbsp;';					
						echo __($order['Order']['state_code'].'-label');
						echo '</td>';
						
						echo '<td style="text-align:center;">';
						echo $order['Order']['tot_importo_e'];
						echo '</td>';
						echo '<td>';
						echo $this->app->drawListSuppliersOrganizationsReferents($user,$order['SuppliersOrganizationsReferent']);
						echo '</td>';
		
						echo "\n";
						echo '</tr>';
					}  // end ciclo Orders
				}
		
				?>
				<script type="text/javascript">
				$(document).ready(function() {
					$('#order_id_selected_all<?php echo $delivery['id'];?>').click(function () {
						var checked = $("input[name='order_id_selected_all<?php echo $delivery['id'];?>']:checked").val();
						if(checked=='ALL')
							$('.order_id_selected<?php echo $delivery['id'];?>').prop('checked',true);
						else
							$('.order_id_selected<?php echo $delivery['id'];?>').prop('checked',false);
					});
				});
				</script>	
				<?php 
			} // end ciclo Deliveries
		
		} // end ciclo Tabs
		echo '</tbody>';
		echo '</table></div>';
		
		echo $this->Form->hidden('order_id_selected',array('id' =>'order_id_selected', 'value'=>''));
		echo $this->Form->end(__('Add Request Payments Orders')." alla richiesta numero ".$requestPaymentResults['RequestPayment']['num']);
		
				?>
				<script type="text/javascript">
				$(document).ready(function() {
					$('#formGas').submit(function() {
				
						var order_id_selected = '';
						for(i = 0; i < $("input[name='order_id_selected']:checked").length; i++) {
							order_id_selected += $("input[name='order_id_selected']:checked").eq(i).val()+',';
						}
				
						if(order_id_selected=='') {
							alert("<?php echo __('jsAlertOrderAtLeastToRequestPaymentRequired');?>");
							return false;
						}	    
						order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
						
						$('#order_id_selected').val(order_id_selected);
				
						$("input[type=submit]").attr('disabled', 'disabled');
						$("input[type=submit]").css('background-image', '-moz-linear-gradient(center top , #ccc, #dedede)');
						$("input[type=submit]").css('box-shadow', 'none');
		
						return true;
					});
				});
				</script>	
				<?php 		
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non sono stati trovati ordini che possono richiedere il pagamento"));

echo '</div>'; // end contentMenuLaterale

$options = [];
echo $this->MenuRequestPayment->drawWrapper($requestPaymentResults['RequestPayment']['id'], $options);
?>