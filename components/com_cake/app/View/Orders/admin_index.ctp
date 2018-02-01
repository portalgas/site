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

echo $this->Form->create('FilterOrder',array('id'=>'formGasFilter','type'=>'get'));
echo '<fieldset class="filter">';
	echo '<legend>'.__('Filter Order').'</legend>';
	
		echo '<div class="row">';
		echo '<div class="col-md-6">';
		$options = array('label' => '&nbsp;', 'options' => $ACLsuppliersOrganization,
								'empty' => 'Filtra per produttore',
								'name'=>'FilterOrderSuppliersOrganizationId','default' => $FilterOrderSuppliersOrganizationId,'escape' => false);
		if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
			$options += array('class'=> 'selectpicker', 'data-live-search' => true);
		echo $this->Form->input('supplier_organization_id',$options);					
		echo '</div>';
		
		echo '<div class="col-md-4">';
		echo $this->Form->input('orders',array('label' => __('Ordinamento'), 'class' => 'form-control', 'options' => $orders, 'name' => 'FilterOrderOrderBy', 'default' => $FilterOrderOrderBy, 'escape' => false)); 
		echo '</div>';	
		
		echo '<div class="col-md-2">';	
		echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
		echo '</div>';		
		echo '</div>';
		
echo '</fieldset>';
		
		
if(!empty($results)) {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '	<th class="hidden-xs hidden-sm"></th>';
	echo '	<th class="hidden-xs hidden-sm">'.__('N').'</th>';
	echo '	<th colspan="2">'.$this->Paginator->sort('supplier_organization_id').'</th>';
	echo '	<th class="hidden-xs hidden-sm">';
	echo 		__('Data inizio');
	echo '		<br />';
	echo 		__('Data fine');
	echo '	</th>';
	echo '	<th class="hidden-xs">'.__('Aperto/Chiuso').'</th>';
	echo '	<th class="hidden-xs">'.$this->Paginator->sort('nota').'</th>';
	echo '<th class="hidden-xs">'.__('stato_elaborazione').'</th>';
		
	if($user->organization['Organization']['hasVisibility']=='Y') {			
		echo '<th class="hidden-xs">'.$this->Paginator->sort('isVisibleFrontEnd',__('isVisibleFrontEnd')).'</th>';
		echo '<th class="hidden-xs">'.$this->Paginator->sort('isVisibleBackOffice',__('isVisibleBackOffice')).'</th>';
	}

	echo '	<th class="hidden-xs hidden-sm">'.$this->Paginator->sort('Created').'</th>';
	echo '	<th class="actions" style="min-width: 125px;">'.__('Actions').'</th>';
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
	echo '<td class="hidden-xs hidden-sm">';
	echo '<a data-toggle="collapse" href="#ajax_details-'.$result['Order']['id'].'" title="'.__('Href_title_expand').'"><i class="fa fa-3x fa-search-plus" aria-hidden="true"></i></a>';
	echo '</td>';
	
	echo '	<td class="hidden-xs hidden-sm">'.$numRow.'</td>';
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
	echo '	<td style="white-space:nowrap;" class="hidden-xs">';
	echo $this->App->utilsCommons->getOrderTime($result['Order']);
	echo '	</td>';
	
	/*
	 *  campo nota
	 */
	echo '<td class="hidden-xs">';
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
		
	echo '<td class="hidden-xs">';		 
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
		echo '<td class="hidden-xs" title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleFrontEnd']).'"></td>';
		echo '<td class="hidden-xs" title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleBackOffice']).'"></td>';		
	}
	
	echo '	<td style="white-space: nowrap;" class="hidden-xs hidden-sm">'.$this->App->formatDateCreatedModifier($result['Order']['created']).'</td>';
	
	echo '<td>';
	
	echo '<a href="'.Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$result['Order']['delivery_id'].'&order_id='.$result['Order']['id'].'"><button type="button" class="btn btn-primary"><i class="fa fa-2x fa-home" aria-hidden="true"></i></button></a>';
	
	$modal_url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=sotto_menu&order_id='.$result['Order']['id'].'&position_img=bgLeft&format=notmpl';
	$modal_size = 'md'; // sm md lg
	$modal_header = __('Order').' '.$result['SuppliersOrganization']['name'];
	echo '<button type="button" class="btn btn-primary btn-menu" data-attr-url="'.$modal_url.'" data-attr-size="'.$modal_size.'" data-attr-header="'.$modal_header.'" ><i class="fa fa-2x fa-navicon"></i></button>';
	
	/*
	 * gestione menu' precedente
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
	*/
	
	echo '</td>';

	echo '</tr>';
	
	echo '<tr data-attr-action="orders-'.$result['Order']['id'].'" class="collapse ajax_details" id="ajax_details-'.$result['Order']['id'].'">';
	echo '	<td colspan="2"></td>'; 
	echo '	<td colspan="'.$colspan.'" id="ajax_details_content-'.$result['Order']['id'].'"></td>';
	echo '</tr>';		

	$delivery_id_old=$result['Delivery']['id'];
endforeach; 

echo '</table></div>';
		
/*
 * modal order menu
 */		
echo '<div class="modal menu fade" role="dialog">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
echo '<h4 class="modal-title">'.__('Order').'</h4>';
echo '</div>';
echo '<div class="modal-body"><p></p>';
echo '</div>';
echo '<div class="modal-footer">';
echo '<button type="button" class="btn btn-primary" data-dismiss="modal">'.__('Close').'</button>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';	

 
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
$(document).ready(function() {
});
</script>