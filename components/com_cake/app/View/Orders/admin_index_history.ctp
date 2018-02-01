<?php
if($user->organization['Organization']['hasVisibility'] == 'Y')
	$colspan = '11';
else
	$colspan = '10';
?>
<div class="orders">
	<h2 class="ico-orders-history">
		<?php echo __('Orders history');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Orders current'), array('action' => 'index'),array('class' => 'action actionList','title' => __('Orders current'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php
	echo $this->element('legendaOrdersHistory');
	
	if(!empty($results)) {
	?>
	<div class="table-responsive"><table class="table table-hover">
	<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th colspan="2"><?php echo $this->Paginator->sort('supplier_organization_id');?></th>
			<th>
				<?php echo __('Data inizio');?><br />
				<?php echo __('Data fine');?>
			</th>
			<th><?php echo $this->Paginator->sort('nota');?></th>
			<?php 
			if($user->organization['Organization']['hasVisibility']=='Y') 
				echo '<th>'.$this->Paginator->sort('isVisibleBackOffice',__('isVisibleBackOffice')).'</th>';
			echo '<th>'.__('stato_elaborazione').'</th>';
			echo '<th>Rich. pagamento</th>';
			?>				
			<th><?php echo $this->Paginator->sort('Created');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$delivery_id_old = 0;
	foreach ($results as $i => $result):
	
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
		
		if($delivery_id_old==0 || $delivery_id_old!=$result['Delivery']['id']) {
			
			echo '<tr><td class="trGroup" colspan="11">';
			
			if($result['Delivery']['isVisibleBackOffice']=='N') echo '<span style="padding-left: 16px;padding-left: 16px;" class="stato_no" title="'.__('DeliveryIsVisibleBackOfficeN').'"></span>';
			
			if($result['Delivery']['sys']=='N')
				$label = $result['Delivery']['luogoData'];
			else 
				$label = $result['Delivery']['luogo'];
			echo __('Delivery').': '.$this->Html->link($label, array('controller' => 'deliveries', 'action' => 'edit', null, 'delivery_id='.$result['Delivery']['id']));
			
			/*
			 * il Cron::deliveriesStatoElaborazione() lo setta a CLOSE
			 */
			if($result['Delivery']['stato_elaborazione']=='CLOSE')
				echo '<span style="margin-left:20px;">[Stato elaborazione: chiuso, prossima alla cancellazione]</span><span style="padding-left: 16px;padding-left: 16px;" class="stato_no"></span>';
			else
				echo '<span style="margin-left:20px;">[Stato elaborazione: aperto]</span><span style="margin-left:20px;padding-left: 16px;padding-left: 16px;" class="stato_si"></span>';
			
			echo '</td></tr>';
		}
		?>
	<tr class="view">
		<td><a action="orders-<?php echo $result['Order']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo $numRow;?></td>
		<td>
			<?php if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
				echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
			?>
		</td>
		<td>
			<?php echo $result['SuppliersOrganization']['name']; ?>
		</td>
		<td style="white-space:nowrap;">
			<?php echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y"); ?><br />
			<?php echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y"); ?>
		</td>
		<?php
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

		if($user->organization['Organization']['hasVisibility']=='Y') 
			echo '<td title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleBackOffice']).'"></td>';		
		echo '<td>';		echo $this->App->drawOrdersStateDiv($result);		echo '&nbsp;';		echo __($result['Order']['state_code'].'-label');
		echo '</td>';
		
		echo '<td>';
		if(isset($result['RequestPayment'])) {
			echo '<span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$result['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($result['RequestPayment']['stato_elaborazione']).'"></span>';
			echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$result['RequestPayment']['stato_elaborazione']);
			echo ' (n.'.$result['RequestPayment']['num'].')';
		}
		echo '</td>';
		?>			
		<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Order']['created']); ?></td>
		<td class="actions-table-img-3">
			<?php 
			if($result['Delivery']['isVisibleBackOffice']=='Y' && $result['Order']['isVisibleBackOffice']=='Y') { 
				echo $this->Html->link(null, array('action' => 'home', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionWorkflow','title' => __('Order home')));				echo $this->Html->link(null, array('action' => 'view', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionView','title' => __('View Order')));
				//echo $this->Html->link(null, array('action' => 'delete', null, 'order_id='.$result['Order']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 
				echo $this->Html->link(null, array('controller' => 'Docs', 'action' => 'referentDocsExportHistory', null, 'delivery_id='.$result['Delivery']['id'], 'order_id='.$result['Order']['id']),array('class' => 'action actionPrinter','title' => __('Export Docs to order')));
			}
			?>
		</td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['Order']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo $colspan;?>" id="tdViewId-<?php echo $result['Order']['id'];?>"></td>
	</tr>
<?php 
	$delivery_id_old=$result['Delivery']['id'];
	endforeach; ?>
	</table></div>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	
	 	echo '</div>';
	 	
	 	echo $this->element('legendaRequestPaymentStato');	 	
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora ordini associati a consegne chiuse"));
	
echo '</div>';
?>