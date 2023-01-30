<?php
$this->App->d($results);

if($user->organization['Organization']['hasVisibility'] == 'Y')
	$colspan = '12';
else
	$colspan = '10';


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
		$options = ['label' => '&nbsp;', 
					'options' => $ACLsuppliersOrganization,
					'name' => 'FilterOrderSuppliersOrganizationId', 
					'default' => $FilterOrderSuppliersOrganizationId,'escape' => false];
		if(count($ACLsuppliersOrganization) > 1) 
			$options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];					
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
	echo '	<th scope="col" class="hidden-xs hidden-sm"></th>';
	echo '	<th scope="col" class="hidden-xs hidden-sm">'.__('N').'</th>';
	echo '	<th scope="col" colspan="2">'.$this->Paginator->sort('supplier_organization_id').'</th>';
	echo '	<th scope="col" class="hidden-xs hidden-sm">';
	echo 		__('DataInizio');
	echo '		<br />';
	echo 		__('DataFine');
	echo '	</th>';
	echo '	<th scope="col" class="hidden-xs">'.__('OpenClose').'</th>';
	echo '	<th scope="col" class="hidden-xs">'.$this->Paginator->sort('nota').'</th>';
	echo '  <th scope="col" class="hidden-xs">'.__('StatoElaborazione').'</th>';
		
	if($user->organization['Organization']['hasVisibility']=='Y') {			
		echo '<th scope="col" class="hidden-xs">'.$this->Paginator->sort('isVisibleFrontEnd',__('isVisibleFrontEnd')).'</th>';
		echo '<th scope="col" class="hidden-xs">'.$this->Paginator->sort('isVisibleBackOffice',__('isVisibleBackOffice')).'</th>';
	}

	echo '	<th scope="col" class="hidden-xs hidden-sm">';
	// $this->Paginator->sort('Created');
	echo '  </th>';
	echo '	<th scope="col" class="actions" style="min-width: 125px;">'.__('Actions').'</th>';
	echo '</tr>';
	
	$delivery_id_old = 0;
	foreach ($results as $i => $result):
		
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
		
		if($delivery_id_old==0 || $delivery_id_old!=$result['Delivery']['id']) {
			
			echo '<tr><td class="trGroup" colspan="'.$colspan.'">';
			
			if($result['Delivery']['isVisibleFrontEnd']=='N') echo '<span style="padding-left: 16px;padding-left: 16px;" class="stato_no" title="'.__('DeliveryIsVisibleFrontEndN').'"></span>';
			if($result['Delivery']['isVisibleBackOffice']=='N') echo '<span style="padding-left: 16px;padding-left: 16px;" class="stato_no" title="'.__('DeliveryIsVisibleBackOfficeN').'"></span>';
			
			echo $this->Html->link(null, array('controller' => 'Deliveries', 'action' => 'calendar_view', null, 'delivery_id='.$result['Order']['delivery_id']), array('class' => 'action actionDeliveryCalendar','title' => __('View Calendar Delivery')));
			
			if($result['Delivery']['sys']=='N') {
				if($delivery_link_permission)
					echo '<span class="hidden-xs">'.__('Delivery').': </span>'.$this->Html->link($result['Delivery']['luogoData'], array('controller' => 'deliveries', 'action' => 'edit', null, 'delivery_id='.$result['Delivery']['id']),array('title'=>__('Edit Delivery')));
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
	echo '<td class="hidden-xs hidden-sm">';
	echo '<a data-toggle="collapse" href="#ajax_details-'.$result['Order']['id'].'" title="'.__('Href_title_expand').'"><i class="fa fa-3x fa-search-plus" aria-hidden="true"></i></a>';
	echo '</td>';
	
	echo '	<td class="hidden-xs hidden-sm">'.$numRow.'</td>';
	echo '	<td style="width:50px;">';
	if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
		echo ' <img style="width:50px;" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
	echo '	</td>';
	echo '	<td>';
	
	// echo $this->Html->link($result['SuppliersOrganization']['name'], array('controller' => 'suppliersOrganizations', 'action' => 'edit', $result['SuppliersOrganization']['id']),array('title' => "Clicca per modificare i dati anagrafici del produttore ".$result['SuppliersOrganization']['name'])); 

    echo $result['SuppliersOrganization']['name'];
    echo '<br /><small>'.__('Importo_totale').' '.number_format($result['Order']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</small>';

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
	 *  campo nota / pagamento
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
	echo $this->App->drawOrdersStateDiv($result);
	echo '&nbsp;';
    echo __($result['Order']['state_code'].'-label');

	 /*
	  * richiesta di pagamento 
	  */ 
	if($user->organization['Template']['payToDelivery'] == 'POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
		if(!empty($result['Order']['request_payment_num'])) {
			echo "<br />";
			if($isTesoriereGeneric)
				echo $this->Html->link('Rich. pagamento n. '.$result['Order']['request_payment_num'], ['controller' => 'RequestPayments', 'action' => 'edit', $result['Order']['request_payment_id']], ['title' => __('Edit RequestPayment')]);
			else
				echo "<br />Rich. pagamento n. ".$result['Order']['request_payment_num'];
		}
	} 
	echo '</td>';
	
	if($user->organization['Organization']['hasVisibility']=='Y') {
		echo '<td class="hidden-xs" title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleFrontEnd']).'"></td>';
		echo '<td class="hidden-xs" title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleBackOffice']).'"></td>';		
	}
	
	/*
	 * btns / msg
	 */
	echo '<td style="white-space: nowrap;" class="hidden-xs hidden-sm">';
	// $this->App->formatDateCreatedModifier($result['Order']['created']);
	$btns = $this->App->drawOrderBtnPaid($result, $isRoot, $isTesoriereGeneric);
	if(!empty($btns))
		echo $btns;	
	else if(!empty($result['Order']['msgGgArchiveStatics']))
		echo $this->App->drawOrderMsgGgArchiveStatics($result);		
	echo $this->App->drawOrderStateNext($result);
	echo '</td>';
	
	echo '<td>';

	if($result['Order']['can_state_code_to_close'])
		echo '<a title="'.__('Close Order').'" class="hidden-xs" href="'.Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=close&delivery_id='.$result['Order']['delivery_id'].'&order_id='.$result['Order']['id'].'"><button type="button" class="btn btn-danger"><i class="fa fa-2x fa-power-off" aria-hidden="true"></i></button></a>';
	
	echo '<a title="'.__('Order home').'" class="hidden-xs" href="'.Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$result['Order']['delivery_id'].'&order_id='.$result['Order']['id'].'"><button type="button" class="btn btn-primary"><i class="fa fa-2x fa-home" aria-hidden="true"></i></button></a>';
		
	$modal_url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=sotto_menu&order_id='.$result['Order']['id'].'&position_img=bgLeft&format=notmpl';
	$modal_size = 'md'; // sm md lg
	$modal_header = __('Order').' '.$result['SuppliersOrganization']['name'];
	echo '<button type="button" class="btn btn-primary btn-menu" data-attr-url="'.$modal_url.'" data-attr-size="'.$modal_size.'" data-attr-header="'.$modal_header.'" ><i class="fa fa-2x fa-navicon"></i></button>';
	
	if($isRoot && $result['Order']['state_code']=='CLOSE')
		echo $this->Html->link(null, ['action' => 'state_code_change', null, 'order_id='.$result['Order']['id'].'&url_bck=index_history'], ['class' => 'action action actionSyncronize', 'title' => __('Orders state_code change')]);


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
	
	echo '<tr data-attr-action="order_details-'.$result['Order']['id'].'" class="hidden-xs hidden-sm collapse ajax_details" id="ajax_details-'.$result['Order']['id'].'">';
	echo '	<td class="hidden-xs hidden-sm" colspan="2"></td>'; 
	echo '	<td class="hidden-xs hidden-sm" colspan="'.($colspan-2).'" id="ajax_details_content-'.$result['Order']['id'].'"></td>';
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
echo '<span class="hidden-xs">';
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
echo '</span>';
} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora ordini registrati"));

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
});
</script>