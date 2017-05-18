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
$this->Html->addCrumb(__('Add Request Payments Orders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="requestPayment form">

	<h2 class="ico-pay">
		<?php echo __('Add Request Payments Orders')." alla richiesta numero ".$requestPaymentResults['RequestPayment']['num'];?>
	</h2>	
		
	<?php 
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
		
		echo '<table>';
		echo '<tbody>';
	
		foreach($results['Tab'] as $numTabs => $tab) {
		
			foreach($tab['Delivery'] as $numDelivery => $delivery) {
		
				if($delivery['totOrders']>0) {
		
					echo '<tr><td colspan="7" class="trGroup">'.__('Delivery').': ';
					if($delivery['sys']=='N')
						echo $delivery['luogoData'];
					else 
						echo $delivery['luogo'];
					echo '</td></tr>';
					
					echo '<thead>';
					echo '<tr>';
					echo '<th>'.__('N').'</th>';
					echo '<th><input type="checkbox" checked id="order_id_selected_all'.$delivery['id'].'" name="order_id_selected_all'.$delivery['id'].'" value="ALL" /></th>';
					echo '<th>'.__('Supplier').'</th>';
					echo '<th>Data dell\'ordine</th>';
					echo '<th>'.__('Stato').'</th>';
					echo '<th>'.__('Importo totale ordine').'</th>';
					echo '<th>Referenti</th>';
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
				jQuery(document).ready(function() {
					jQuery('#order_id_selected_all<?php echo $delivery['id'];?>').click(function () {
						var checked = jQuery("input[name='order_id_selected_all<?php echo $delivery['id'];?>']:checked").val();
						if(checked=='ALL')
							jQuery('.order_id_selected<?php echo $delivery['id'];?>').prop('checked',true);
						else
							jQuery('.order_id_selected<?php echo $delivery['id'];?>').prop('checked',false);
					});
				});
				</script>	
				<?php 
			} // end ciclo Deliveries
		
		} // end ciclo Tabs
		echo '</tbody>';
		echo '</table>';
		
		echo $this->Form->hidden('order_id_selected',array('id' =>'order_id_selected', 'value'=>''));
		echo $this->Form->end(__('Add Request Payments Orders')." alla richiesta numero ".$requestPaymentResults['RequestPayment']['num']);
		
				?>
				<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('#formGas').submit(function() {
				
						var order_id_selected = '';
						for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
							order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
						}
				
						if(order_id_selected=='') {
							alert("<?php echo __('jsAlertOrderAtLeastToRequestPaymentRequired');?>");
							return false;
						}	    
						order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
						
						jQuery('#order_id_selected').val(order_id_selected);
				
						return true;
					});
				});
				</script>	
				<?php 		
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non sono stati trovati ordini che possono richiedere il pagamento"));
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
	padding-left: 5px;
    width: 74%;
}
.cakeContainer div.actions {
    width: 25%;
}
</style>