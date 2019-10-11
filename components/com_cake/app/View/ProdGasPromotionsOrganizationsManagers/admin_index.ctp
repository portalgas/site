<?php
$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdGasPromotions New'), array('controller' => 'ProdGasPromotionsOrganizationsManagers', 'action' => 'index_new'));
$this->Html->addCrumb(__('List ProdGasPromotionsOrders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="prodgaspromotion">';
echo '<h2 class="ico-orders">';		
echo __('List ProdGasPromotionsOrders');
echo '</h2>';

/*
 * promozioni gia' associate ad un ordine di GAS
 */
if(!empty($results)) {

	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '	<th></th>';
	echo '	<th colspan="2">'.__('Supplier').'</th>';
	echo '	<th>';
	echo 		__('DataInizio');
	echo '		<br />';
	echo 		__('DataFine');
	echo '	</th>';
	echo '	<th>'.__('OpenClose').'</th>';
	echo '	<th>'.__('Nota').'</th>';
	echo '<th>'.__('StatoElaborazione').'</th>';
		
	echo '	<th>'.__('Created').'</th>';
	echo '	<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';
	
	$delivery_id_old = 0;
	foreach ($results as $numResult => $result):

		if($delivery_id_old==0 || $delivery_id_old!=$result['Delivery']['id']) {
			
			echo '<tr><td class="trGroup" colspan="11">';
			
			if($result['Delivery']['isVisibleFrontEnd']=='N') echo '<span style="padding-left: 16px;padding-left: 16px;" class="stato_no" title="'.__('DeliveryIsVisibleFrontEndN').'"></span>';
			if($result['Delivery']['isVisibleBackOffice']=='N') echo '<span style="padding-left: 16px;padding-left: 16px;" class="stato_no" title="'.__('DeliveryIsVisibleBackOfficeN').'"></span>';
			
			echo $this->Html->link(null, array('controller' => 'Deliveries', 'action' => 'calendar_view', null, 'delivery_id='.$result['Order']['delivery_id']), array('class' => 'action actionDeliveryCalendar','title' => __('View Calendar Delivery')));
			
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
					echo '<span style="color:green;">Aperta ';
					if($result['Delivery']['daysToEndConsegna']==0) echo '(scade oggi)';
					else echo '(mancano ancora '.$result['Delivery']['daysToEndConsegna'].'&nbsp;gg&nbsp;alla&nbsp;consegna)';
					echo '</span>';
				}
			}
			else {
				echo __('Delivery').': '.h($result['Delivery']['luogo']);
			}
			echo '</td></tr>';
		}
		

	echo '<tr class="view">';
	echo '	<td><a action="orders-'.$result['Order']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	echo '	<td>';
	if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
		echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
	echo '	</td>';
	echo '	<td>';
	
	// echo $this->Html->link($result['SuppliersOrganization']['name'], array('controller' => 'suppliersOrganizations', 'action' => 'edit', $result['SuppliersOrganization']['id']),array('title' => "Clicca per modificare i dati anagrafici del produttore ".$result['SuppliersOrganization']['name'])); 
			
	echo $result['SuppliersOrganization']['name'];
	
	
	if($user->organization['Organization']['hasDes']=='Y' && !empty($result['DesOrdersOrganization'])) {
		if($result['DesOrdersOrganization']['des_id']==$user->des_id) {
			echo '<a title="" href="/administrator/index.php?option=com_cake&amp;controller=DesOrdersOrganizations&amp;action=index&amp;des_order_id='.$result['DesOrdersOrganization']['des_order_id'].'">';
			echo '<span class="ico-order-is-des" title="'.__('OrderIsDes').'"></span></a>';	
		}
		else
			echo '<span class="ico-order-is-des" title="'.__('OrderIsDes').'"></span>';
	}
	
	echo '	</td>';
					
	echo '	<td style="white-space:nowrap;">';
	echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y").'<br />';
	echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");
	if($result['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
		echo '<br />Riaperto fino a '.$this->Time->i18nFormat($result['Order']['data_fine_validation'],"%A %e %B %Y");
	echo '	</td>';
	echo '	<td style="white-space:nowrap;">';
	echo $this->App->utilsCommons->getOrderTime($result['Order']);
	echo '	</td>';
	
	/*
	 *  campo nota
	 */
	echo '<td>';
	if(!empty($result['Order']['nota'])) {
		
		echo '<button type="button" class="btn btn-info" data-toggle="modal" data-target="#order_nota_'.$result['Order']['id'].'"><i class="fa fa-2x fa-info-circle" aria-hidden="true"></i></button>';
		echo '<div id="order_nota_'.$result['Order']['id'].'" class="modal fade" role="dialog">';
		echo '<div class="modal-dialog">';
		echo '<div class="modal-content">';
		echo '<div class="modal-header">';
		echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
		echo '<h4 class="modal-title">Nota del referente</h4>';
		echo '</div>';
		echo '<div class="modal-body"><p>'.$result['Order']['nota'].'</p>';
		echo '</div>';
		echo '<div class="modal-footer">';
		echo '<button type="button" class="btn btn-primary" data-dismiss="modal">'.__('Close').'</button>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';	
		
	} // end if(!empty($result['Order']['nota']))	
	echo '</td>'; 
		
	echo '<td>';		 
	if($result['Order']['state_code']=='PROCESSED-POST-DELIVERY') {
		if($isReferenteTesoriere)
			echo $this->Html->link(null, array('controller' => 'Referente', 'action' => 'order_state_in_TO_PAYMENT', null, 'delivery_id='.$result['Order']['delivery_id'], 'order_id='.$result['Order']['id']),array('class' => 'action orderStato'.$result['Order']['state_code'],'title' => "Gestisci il pagamento dell'ordine"));
		else
			echo $this->Html->link(null, array('controller' => 'Referente', 'action' => 'order_state_in_WAIT_PROCESSED_TESORIERE', null, 'delivery_id='.$result['Order']['delivery_id'], 'order_id='.$result['Order']['id']),array('class' => 'action orderStato'.$result['Order']['state_code'],'title' => "Passa l'ordine al tesoriere"));
	}
	else 
	if($result['Order']['state_code']=='WAIT-PROCESSED-TESORIERE')
		echo $this->Html->link(null, array('controller' => 'Referente', 'action' => 'order_state_in_PROCESSED_POST_DELIVERY', null, 'delivery_id='.$result['Order']['delivery_id'], 'order_id='.$result['Order']['id']),array('class' => 'action orderStato'.$result['Order']['state_code'],'title' => "Riporta l'ordine allo stato 'in carico al referente'"));
	else 
		echo $this->App->drawOrdersStateDiv($result);

	echo '&nbsp;';
    echo __($result['Order']['state_code'].'-label');
	echo '</td>';
	if($user->organization['Organization']['hasVisibility']=='Y') {
		echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleFrontEnd']).'"></td>';
		echo '<td title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleBackOffice']).'"></td>';		
	}
	
	echo '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Order']['created']).'</td>';
	
	echo '<td class="actions-table-img-3">';
	
		//if($result['Order']['permissionToEditReferente']) {
		echo $this->Html->link(null, ['action' => 'contact', null, 'prod_gas_promotion_id='.$result['ProdGasPromotion']['id']], ['class' => 'action actionPhone','title' => __('Contact PromotionOrganizationManager')]);
		echo $this->Html->link(null, array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionWorkflow','title' => __('Order home')));
			
		echo '<a id="actionMenu-'.$result['Order']['id'].'" class="action actionMenu" title="'.__('Expand menu').'"></a>';
		echo '<div class="menuDetails" id="menuDetails-'.$result['Order']['id'].'" style="display:none;">';
		echo '	<a class="menuDetailsClose" id="menuDetailsClose-'.$result['Order']['id'].'"></a>';
		echo '<div id="order-sotto-menu-'.$result['Order']['id'].'"></div>';
		echo '</div>';

		echo '</td>';

	echo '</tr>';
	
	echo '<tr class="trView" id="trViewId-'.$result['Order']['id'].'">';
	echo '	<td colspan="2"></td>'; 
	echo '	<td colspan="11" id="tdViewId-'.$result['Order']['id'].'"></td>';
	echo '</tr>';

	$delivery_id_old=$result['Delivery']['id'];
endforeach; 

echo '</table>';

} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora promozioni associate ad un ordine"));

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

	$('.referente_nota').click(function() {
		var id = $(this).attr('id');
		$("#dialog-msg-"+id ).modal("open");
	});
});
</script>