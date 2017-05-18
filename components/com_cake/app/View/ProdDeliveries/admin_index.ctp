<?php
if($user->organization['Organization']['hasVisibility'] == 'Y')
	$colspan = '10';
else
	$colspan = '8';	
?>

<div class="prod_deliveries">
	<h2 class="ico-deliveries">
		<?php echo __('Deliveries');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Delivery'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Delivery'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php
	if(!empty($results)) {
	
		echo '<table cellpadding="0" cellspacing="0">';
		echo '<tr>';
		echo '	<th></th>';
		echo '	<th>'.__('N').'</th>';
		echo '	<th>'.$this->Paginator->sort('name').'</th>';
		echo '	<th>'.$this->Paginator->sort(__('Data inizio'), 'data_inizio').'</th>';
		echo '	<th>'.$this->Paginator->sort(__('Data fine'), 'data_fine').'</th>';
		echo '	<th>'. __('Prod Groups').'</th>';
		echo '	<th>'. __('Ricorrenza').'</th>';
		echo '	<th></th>';
		if($user->organization['Organization']['hasVisibility']=='Y') {			
			echo '<th>'.$this->Paginator->sort('isVisibleFrontEnd',__('isVisibleFrontEnd')).'</th>';
			echo '<th>'.$this->Paginator->sort('isVisibleBackOffice',__('isVisibleBackOffice')).'</th>';
		}		
		echo '	<th>'.$this->Paginator->sort('created').'</th>';
		echo '	<th class="actions">'.__('Actions').'</th>';	
		echo '</tr>';

		foreach ($results as $i => $result):
	
			$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1); 
		
			echo '<tr class="view">';
			echo '	<td><a action="prod_deliveries-'.$result['ProdDelivery']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
			echo '	<td>'.$numRow.'</td>';
		?>
		<td><?php echo $result['ProdDelivery']['name']; ?>&nbsp;</td>
		<td><?php echo $this->Time->i18nFormat($result['ProdDelivery']['data_inizio'],"%A %e %B %Y"); ?>&nbsp;</td>
		<td><?php echo $this->Time->i18nFormat($result['ProdDelivery']['data_fine'],"%A %e %B %Y"); ?>&nbsp;</td>
		<td><?php echo $result['ProdGroup']['name']; ?></td>
		<?php
		echo '<td>';
		if(!empty($result['ProdDelivery']['ricorrenza_num']))
			echo $result['ProdDelivery']['ricorrenza_num'].' '.$this->App->traslateEnum($result['ProdDelivery']['ricorrenza_type']);
		echo '</td>';
		echo '<td>'.$this->App->drawProdDeliveriesStateDiv($result).'&nbsp;</td>';
		if($user->organization['Organization']['hasVisibility']=='Y') {
			echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['ProdDelivery']['isVisibleFrontEnd']).'"></td>';
			echo '<td title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['ProdDelivery']['isVisibleBackOffice']).'"></td>';		
		}
		
		echo '	<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['ProdDelivery']['created']).'</td>';				
		

		echo '<td class="actions-table-img-3">';
		

			if($result['ProdDelivery']['prod_delivery_state_id']<=Configure::read('PROCESSED-BEFORE-DELIVERY')) 
				echo $this->Html->link(null, array('action' => 'edit', null, 'prod_delivery_id='.$result['ProdDelivery']['id']),array('class' => 'action actionEdit','title' => __('Edit ProdDelivery')));
			
			if($result['ProdDelivery']['prod_delivery_state_id']==Configure::read('CREATE-INCOMPLETE')) {
				echo $this->Html->link(null, array('controller' => 'ProdDeliveriesArticle', 'action' => 'add', null, 'prod_delivery_id='.$result['ProdDelivery']['id']), array('class' => 'action actionEditCart','title' => __('Add ProdDeliveriesArticle Error')));
				echo $this->Html->link(null, array('action' => 'delete', null, 'prod_delivery_id='.$result['ProdDelivery']['id']), array('class' => 'action actionDelete','title' => __('Delete')));
			}
			else {	
				echo $this->Html->link(null, array('action' => 'home', null, 'prod_delivery_id='.$result['ProdDelivery']['id']), array('class' => 'action actionWorkflow','title' => __('ProdDelivery home')));

				echo '<a id="actionMenu-'.$result['ProdDelivery']['id'].'" class="action actionMenu" title="'.__('Expand menu').'"></a>';
				echo '<div class="menuDetails" id="menuDetails-'.$result['ProdDelivery']['id'].'" style="display:none;">';
				echo '	<a class="menuDetailsClose" id="menuDetailsClose-'.$result['ProdDelivery']['id'].'"></a>';
				echo '<div id="prod-delivery-sotto-menu-'.$result['ProdDelivery']['id'].'"></div>';
				echo '</div>';
			}
		echo '</td>';
		
	echo '</tr>';
				
		echo '<tr class="trView" id="trViewId-'.$result['ProdDelivery']['id'].'">';
		echo '	<td colspan="2"></td>'; 
		echo '	<td colspan="'.$colspan.'" id="tdViewId-'.$result['ProdDelivery']['id'].'"></td>';
		echo '</tr>';
		
	endforeach; 

		echo '</table>';
		
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

		echo $this->element('legendaProdDeliveriesState',array('htmlLegenda' => $htmlLegenda));
	} 
	else  
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora consegne registrate"));
	
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

			viewProdDeliverySottoMenu(numRow,"bgLeft");

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
});
</script>