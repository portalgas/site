<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
if($user->organization['Organization']['hasVisibility'] == 'Y')
	$colspan = '12';
else
	$colspan = '11';


echo '<div class="orders">';
echo '<h2 class="ico-orders">';		
echo __('Orders current');
echo '<div class="actions-img">';			
echo '	<ul>';
echo '		<li>'.$this->Html->link(__('New Order'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Order'))).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';
if(!empty($results)) {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '	<th></th>';
	echo '	<th>'.__('N').'</th>';
	echo '	<th colspan="2">'.$this->Paginator->sort('supplier_organization_id').'</th>';
	echo '	<th class="hidden-xs hidden-sm">';
	echo 		__('Data inizio');
	echo '		<br />';
	echo 		__('Data fine');
	echo '	</th>';
	echo '	<th>'.__('Aperto/Chiuso').'</th>';
	echo '	<th>'.$this->Paginator->sort('nota').'</th>';
	echo '<th>'.__('stato_elaborazione').'</th>';
		
	if($user->organization['Organization']['hasVisibility']=='Y') {			
		echo '<th>'.$this->Paginator->sort('isVisibleFrontEnd',__('isVisibleFrontEnd')).'</th>';
		echo '<th>'.$this->Paginator->sort('isVisibleBackOffice',__('isVisibleBackOffice')).'</th>';
	}

	echo '	<th class="hidden-xs hidden-sm">'.$this->Paginator->sort('Created').'</th>';
	echo '	<th class="actions">'.__('Actions').'</th>';
	echo '</tr>';
	
	$delivery_id_old = 0;
	foreach ($results as $i => $result):

		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
		
		if($delivery_id_old==0 || $delivery_id_old!=$result['Delivery']['id']) {
			
			echo '<tr><td class="trGroup" colspan="12">';
			
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
		

	echo '<tr>';
	if(Configure::read('LayoutBootstrap')) {
		echo '<td>';
		echo '<a data-toggle="collapse" href="#ajax_details-'.$result['Order']['id'].'" title="'.__('Href_title_expand').'"><i class="fa fa-3x fa-search-plus" aria-hidden="true"></i></a>';
		echo '</td>';
	}
	else
		echo '	<td><a action="orders-'.$result['Order']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
	echo '	<td>'.$numRow.'</td>';
	echo '	<td>';
	if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
		echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
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
	else {
		if($result['Order']['prod_gas_promotion_id']>0)
			echo '<span class="ico-order-is-prod_gas_promotion" title="'.__('OrderIsProdGasPromotion').'"></span>';
	}
	
	echo '	</td>';
					
	echo '	<td style="white-space:nowrap;" class="hidden-xs hidden-sm">';
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
		
		if(Configure::read('LayoutBootstrap')) {
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
		}
		else {		
			echo '<img style="cursor:pointer;" class="referente_nota" id="'.$result['Order']['id'].'" src="'.Configure::read('App.img.cake').'/icon-28-info.png" title="Leggi la nota del referente" border="0" />';
			
			echo '<div id="dialog-msg-'.$result['Order']['id'].'" title="Nota del referente">';
			echo '<p>';
			echo $result['Order']['nota'];
			echo '</p>';
			echo '</div>';
			
			echo '<script type="text/javascript">';
			echo 'jQuery("#dialog-msg-'.$result['Order']['id'].'" ).dialog({';
			echo "\r\n";
			echo '	autoOpen: false,';
			echo "\r\n";
			echo '	height: 450,';
			echo "\r\n";
			echo '	width: 600,';
			echo "\r\n";
			echo '	modal: true,';
			echo "\r\n";
			echo '	buttons: {';
			echo "\r\n";
			echo '		"Chiudi": function() {';
			echo "\r\n";
			echo '			jQuery( this ).dialog( "close" );';
			echo "\r\n";
			echo '		},';
			echo "\r\n";
			echo '	}';
			echo '});';
			echo '</script>';
		}
		
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
	
	echo '	<td style="white-space: nowrap;" class="hidden-xs hidden-sm">'.$this->App->formatDateCreatedModifier($result['Order']['created']).'</td>';
	
	echo '<td class="actions-table-img-3">';
	
	if($result['Order']['isVisibleBackOffice']=='N') 
		echo $this->Html->link(null, array('action' => 'edit', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionEdit','title' => __('Edit Order')));
	else {
		if($result['Order']['prod_gas_promotion_id']==0 && ($result['Order']['state_code']=='CREATE-INCOMPLETE' || $result['Order']['state_code']=='OPEN-NEXT' || $result['Order']['state_code']=='OPEN' || $result['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY')) 
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
	
	if(Configure::read('LayoutBootstrap')) {
		echo '<tr data-attr-action="orders-'.$result['Order']['id'].'" class="collapse ajax_details" id="ajax_details-'.$result['Order']['id'].'">';
		echo '	<td colspan="2"></td>'; 
		echo '	<td colspan="'.$colspan.'" id="ajax_details_content-'.$result['Order']['id'].'"></td>';
		echo '</tr>';		
	}
	else {	
		echo '<tr class="trView" id="trViewId-'.$result['Order']['id'].'">';
		echo '	<td colspan="2"></td>'; 
		echo '	<td colspan="'.$colspan.'" id="tdViewId-'.$result['Order']['id'].'"></td>';
		echo '</tr>';
	}

	$delivery_id_old=$result['Delivery']['id'];
endforeach; 

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
	
/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFonud', 'msg' => "Non ci sono ancora ordini registrati"));

echo '</div>';
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".actionMenu").each(function () {
		jQuery(this).click(function() {

			jQuery('.menuDetails').css('display','none');
			
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).show();

			viewOrderSottoMenu(numRow,"bgLeft");

			var offset = jQuery(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			jQuery('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	jQuery(".menuDetailsClose").each(function () {
		jQuery(this).click(function() {
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).hide('slow');
		});
	});	

	jQuery('.referente_nota').click(function() {
		var id = jQuery(this).attr('id');
		jQuery("#dialog-msg-"+id ).dialog("open");
	});
});
</script>