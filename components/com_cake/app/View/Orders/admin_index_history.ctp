<?php
if($user->organization['Organization']['hasVisibility'] == 'Y')
	$colspan = '10';
else
	$colspan = '9';
	
echo '<div class="orders">';
echo '<h2 class="ico-orders-history">';		
echo __('Orders history');
echo '<div class="actions-img">';			
echo '	<ul>';
echo '		<li>'.$this->Html->link(__('Orders current'), array('action' => 'index'),array('class' => 'action actionList','title' => __('Orders current'))).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';


// echo $this->element('legendaOrdersHistory');

if(!empty($results)) {

		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<tr>';
		echo '<th></th>';
		echo '<th>'.__('N').'</th>';
		echo '<th colspan="2">'.$this->Paginator->sort('supplier_organization_id').'</th>';
		echo '<th>';
		echo __('DataInizioPast').'<br />';
		echo __('DataFinePast');
		echo '</th>';
		if($user->organization['Organization']['hasVisibility']=='Y') 
			echo '<th>'.$this->Paginator->sort('isVisibleBackOffice',__('isVisibleBackOffice')).'</th>';
		echo '<th>'.__('StatoElaborazione').'</th>';
		echo '<th>Rich. pagamento</th>';			
		echo '<th></th>';
		echo '<th class="actions">'.__('Actions').'</th>';
		echo '</tr>';
		
		$delivery_id_old = 0;
		foreach ($results as $i => $result) {
		
			$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
			
			if($delivery_id_old==0 || $delivery_id_old!=$result['Delivery']['id']) {
				
				echo '<tr><td class="trGroup" colspan="11">';
				
				if($result['Delivery']['isVisibleBackOffice']=='N') echo '<span style="padding-left: 16px;padding-left: 16px;" class="stato_no" title="'.__('DeliveryIsVisibleBackOfficeN').'"></span>';
				
				if($result['Delivery']['sys']=='N')
					$label = $result['Delivery']['luogoData'];
				else 
					$label = $result['Delivery']['luogo'];
				echo __('Delivery').': '.$this->Html->link($label, array('controller' => 'deliveries', 'action' => 'edit', null, 'delivery_id='.$result['Delivery']['id']));
				
				echo '</td></tr>';
			}
			
			echo '<tr class="view">';
			echo '<td><a action="orders-'.$result['Order']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
			echo '<td>'.$numRow.'</td>';
			echo '<td>';
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
			echo '</td>';
			echo '<td>';
			echo $result['SuppliersOrganization']['name'];
			echo '</td>';
			echo '<td style="white-space:nowrap;">';
			echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y");
			echo '<br />';
			echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");
			echo '</td>';
				
			if($user->organization['Organization']['hasVisibility']=='Y') 
				echo '<td title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleBackOffice']).'"></td>';		
	
			echo '<td>';
			echo $this->App->drawOrdersStateDiv($result);
			echo '&nbsp;';
			echo __($result['Order']['state_code'].'-label');
			echo '</td>';
			
			echo '<td>';
			if(isset($result['RequestPayment'])) {
				echo '<span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$result['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($result['RequestPayment']['stato_elaborazione']).'"></span>';
				echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$result['RequestPayment']['stato_elaborazione']);
				echo ' (n.'.$result['RequestPayment']['num'].')';
			}
			echo '</td>';			
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
			echo '</td>';

			echo '<td>';

			if($result['Order']['can_state_code_to_close'])
				echo '<a title="'.__('Close Order').'" class="hidden-xs" href="'.Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=close&delivery_id='.$result['Order']['delivery_id'].'&order_id='.$result['Order']['id'].'"><button type="button" class="btn btn-danger"><i class="fa fa-2x fa-power-off" aria-hidden="true"></i></button></a>';
			
			echo '<a title="'.__('Order home').'" class="hidden-xs" href="'.Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$result['Order']['delivery_id'].'&order_id='.$result['Order']['id'].'"><button type="button" class="btn btn-primary"><i class="fa fa-2x fa-home" aria-hidden="true"></i></button></a>';
				
			$modal_url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=sotto_menu&order_id='.$result['Order']['id'].'&position_img=bgLeft&format=notmpl';
			$modal_size = 'md'; // sm md lg
			$modal_header = __('Order').' '.$result['SuppliersOrganization']['name'];
			echo '<button type="button" class="btn btn-primary btn-menu" data-attr-url="'.$modal_url.'" data-attr-size="'.$modal_size.'" data-attr-header="'.$modal_header.'" ><i class="fa fa-2x fa-navicon"></i></button>';
			
			/*
			 * gestione menu' precedente			
			echo '<td class="actions-table-img-3">';
				if($result['Delivery']['isVisibleBackOffice']=='Y' && $result['Order']['isVisibleBackOffice']=='Y') { 
					echo $this->Html->link(null, array('action' => 'home', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionWorkflow','title' => __('Order home')));
					echo $this->Html->link(null, array('action' => 'view', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionView','title' => __('View Order')));
					//echo $this->Html->link(null, array('action' => 'delete', null, 'order_id='.$result['Order']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 
					echo $this->Html->link(null, array('controller' => 'Docs', 'action' => 'referentDocsExportHistory', null, 'delivery_id='.$result['Delivery']['id'], 'order_id='.$result['Order']['id']),array('class' => 'action actionPrinter','title' => __('Export Docs to order')));
				}
			*/

			if($isRoot)
				echo $this->Html->link(null, ['action' => 'state_code_change', null, 'order_id='.$result['Order']['id'].'&url_bck=index_history'], ['class' => 'action action actionSyncronize', 'title' => __('Orders state_code change')]);

			echo '</td>';
			echo '</tr>';
			echo '<tr class="trView" id="trViewId-'.$result['Order']['id'].'">';
			echo '<td colspan="2"></td>';
			echo '<td colspan="'.$colspan.'" id="tdViewId-'.$result['Order']['id'].'"></td>';
			echo '</tr>';
		 
			$delivery_id_old=$result['Delivery']['id'];
		} // foreach ($results as $i => $result)
	
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
	 	
	 	echo $this->element('legendaRequestPaymentStato');
	 	
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora ordini associati a consegne chiuse"));
	
echo '</div>';
?>