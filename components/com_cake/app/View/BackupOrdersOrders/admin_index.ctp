<?php
$this->App->d($results);
		
if(!empty($results)) {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '	<th class="hidden-xs hidden-sm">'.__('N').'</th>';
	echo '	<th colspan="2">'.$this->Paginator->sort('supplier_organization_id').'</th>';
	echo '	<th class="hidden-xs hidden-sm">';
	echo 		__('DataInizio');
	echo '		<br />';
	echo 		__('DataFine');
	echo '	</th>';
	echo '  <th class="hidden-xs">'.__('StatoElaborazione').'</th>';
	echo '	<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';
	
	$delivery_id_old = 0;
	foreach ($results as $i => $result) {
		
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
		
		if($delivery_id_old==0 || $delivery_id_old!=$result['Delivery']['id']) {
			
			echo '<tr><td class="trGroup" colspan="6">';
			
			if($result['Delivery']['isVisibleFrontEnd']=='N') echo '<span style="padding-left: 16px;padding-left: 16px;" class="stato_no" title="'.__('DeliveryIsVisibleFrontEndN').'"></span>';
			if($result['Delivery']['isVisibleBackOffice']=='N') echo '<span style="padding-left: 16px;padding-left: 16px;" class="stato_no" title="'.__('DeliveryIsVisibleBackOfficeN').'"></span>';
			
			echo $this->Html->link(null, array('controller' => 'Deliveries', 'action' => 'calendar_view', null, 'delivery_id='.$result['BackupOrdersOrder']['delivery_id']), array('class' => 'action actionDeliveryCalendar','title' => __('View Calendar Delivery')));
			
			if($result['Delivery']['sys']=='N') {
				if($delivery_link_permission)
					echo '<span class="hidden-xs">'.__('Delivery').': </span>'.$this->Html->link($result['Delivery']['luogoData'], ['controller' => 'deliveries', 'action' => 'edit', null, 'delivery_id='.$result['Delivery']['id']], ['title'=>__('Edit Delivery')]);
				else
					echo '<span class="hidden-xs">'.__('Delivery').': </span>'.$result['Delivery']['luogoData'];
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				if($result['Delivery']['daysToEndConsegna']<0) {
					echo '<span style="color:red;">Chiusa</span>';
				}
				else {
					echo '<span style="color:green;">Aperta ';
					if($result['Delivery']['daysToEndConsegna']==0) echo '(scade oggi)';
					else echo '(mancano ancora '.$result['Delivery']['daysToEndConsegna'].'&nbsp;gg&nbsp;alla&nbsp;consegna)';
					echo '</span>';
				}
			}
			else {
				echo '<span class="hidden-xs">'.__('Delivery').': </span>'.h($result['Delivery']['luogo']);
			}
			echo '</td></tr>';
		}
		

		echo '<tr>';
		echo '	<td class="hidden-xs hidden-sm">'.$numRow.'</td>';
		echo '	<td style="width:50px;">';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo ' <img style="width:50px;" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
		echo '	</td>';
		echo '	<td>';
		echo $result['SuppliersOrganization']['name'];
		
		if($user->organization['Organization']['hasDes']=='Y' && !empty($result['BackupOrdersDesOrdersOrganization'])) {
			if($result['BackupOrdersDesOrdersOrganization']['des_id']==$user->des_id) {
				echo '<a title="" href="/administrator/index.php?option=com_cake&amp;controller=DesOrdersOrganizations&amp;action=index&amp;des_order_id='.$result['BackupOrdersDesOrdersOrganization']['des_order_id'].'">';
				echo '<span class="ico-order-is-des" title="'.__('OrderIsDes').'"></span></a>';	
			}
			else
				echo '<span class="ico-order-is-des" title="'.__('OrderIsDes').'"></span>';
		}
		else {
			if($result['BackupOrdersOrder']['prod_gas_promotion_id']>0)
				echo '<span class="ico-order-is-prod_gas_promotion" title="'.__('OrderIsProdGasPromotion').'"></span>';
		}
		
		echo '	</td>';
						
		echo '	<td style="white-space:nowrap;" class="hidden-xs hidden-sm">';
		echo $this->Time->i18nFormat($result['BackupOrdersOrder']['data_inizio'],"%A %e %B %Y").'<br />';
		echo $this->Time->i18nFormat($result['BackupOrdersOrder']['data_fine'],"%A %e %B %Y");
		if($result['BackupOrdersOrder']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
			echo '<br />Riaperto fino a '.$this->Time->i18nFormat($result['BackupOrdersOrder']['data_fine_validation'],"%A %e %B %Y");
		echo '	</td>';
		
			
		echo '<td class="hidden-xs">';
		echo __($result['BackupOrdersOrder']['state_code'].'-label');

		 /*
		  * richiesta di pagamento 
		  */ 
		if($user->organization['Template']['payToDelivery'] == 'POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
			if(!empty($result['BackupOrdersOrder']['request_payment_num'])) {
				echo "<br />";
				if($isTesoriereGeneric)
					echo $this->Html->link('Rich. pagamento n. '.$result['BackupOrdersOrder']['request_payment_num'], ['controller' => 'RequestPayments', 'action' => 'edit', $result['BackupOrdersOrder']['request_payment_id']], ['title' => __('Edit RequestPayment')]);
				else
					echo "<br />Rich. pagamento n. ".$result['BackupOrdersOrder']['request_payment_num'];
			}
		} 
		echo '</td>';
		
		echo '<td>';
		echo $this->Html->link(null, ['action' => 'resume', null, 'order_id='.$result['BackupOrdersOrder']['id']], ['class' => 'action action actionSyncronize', 'title' => __('Order resume')]);
		echo '</td>';
		echo '</tr>';

		$delivery_id_old=$result['Delivery']['id'];
	}

echo '</table></div>';


 
echo '<p>';
echo $this->Paginator->counter(array(
'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
));
echo '</p>';

echo '<div class="paging">';
echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
echo $this->Paginator->numbers(array('separator' => ''));
echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
echo '</div>';

} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora ordini registrati"));

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
});
</script>