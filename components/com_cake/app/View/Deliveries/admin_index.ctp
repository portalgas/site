<?php
if($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y' && $user->organization['Organization']['hasVisibility'] == 'Y')
	$colspan = '12';
else
if(($user->organization['Organization']['hasStoreroom'] == 'N' || $user->organization['Organization']['hasStoreroomFrontEnd']=='N') && $user->organization['Organization']['hasVisibility'] == 'N')
	$colspan = '8';
else
	$colspan = '10';
?>
<div class="deliveries">
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
	?>	
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th><?php echo $this->Paginator->sort('luogo');?></th>
			<th></th>
			<th><?php echo $this->Paginator->sort('data');?></th>
			<th><?php echo __('Aperto/Chiuso');?></th>
			<?php 
			if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
				echo '<th>'.$this->Paginator->sort('isToStoreroom','Dispensa').'</th>';
				echo '<th>Dispensa<br />rich. pagam.</th>';
			}
			
			if($user->organization['Organization']['hasVisibility']=='Y') {
				echo '<th>'.$this->Paginator->sort('isVisibleFrontEnd',__('isVisibleFrontEnd')).'</th>';
				echo '<th>'.$this->Paginator->sort('isVisibleBackOffice',__('isVisibleBackOffice')).'</th>';
			}
			?>
			<th style="width50px;"></th>
			<th><?php echo $this->Paginator->sort('stato_elaborazione',__('stato_elaborazione'));?></th>
			<th><?php echo $this->Paginator->sort('Created');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($results as $i => $result):
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);
		 ?>
	<tr class="view">
		<td><a action="deliveries-<?php echo $result['Delivery']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo $numRow;?></td>
		<td>
			<?php echo $result['Delivery']['luogo']; 
			if(!empty($result['Delivery']['nota']))  echo '<div class="small">'.$result['Delivery']['nota'].'</div>';
			?>		
		</td>
		<td class="nota_evidenza_<?php echo strtolower($result['Delivery']['nota_evidenza']); ?>">&nbsp;</td>
		<td style="white-space:nowrap;"><?php echo $this->Time->i18nFormat($result['Delivery']['data'],"%A %e %B %Y"); ?></td>
		<td>
			<?php 
			if($result['Delivery']['daysToEndConsegna']<0) 
				echo '<span style="color:red;">Chiuso</span>';
			else {
					echo '<span style="color:green;">Aperto ';
					if($result['Delivery']['daysToEndConsegna']==0) echo '(scade oggi)';
					else echo '(per ancora '.$result['Delivery']['daysToEndConsegna'].'&nbsp;gg)';
					echo '</span>';
			}
			
		echo '</td>';
			
		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			echo '<td title="'.__('toolTipIsToStoreroom').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isToStoreroom']).'"></td>';
			echo '<td title="'.__('toolTipIsToStoreroomPay').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isToStoreroomPay']).'"></td>';
		}
		
		if($user->organization['Organization']['hasVisibility']=='Y') {
			echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isVisibleFrontEnd']).'"></td>';
			echo '<td title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isVisibleBackOffice']).'"></td>';		
		}
		echo '<td>';
		if(!empty($result['Delivery']['gcalendar_event_id']))
			echo '<img src="'.Configure::read('App.server').Configure::read('App.img.cake').'/gcalendar.png" title="Inserito in GCalendar con evento num '.$result['Delivery']['gcalendar_event_id'].'" />';
		echo '</td>';
		?>	
		<td title="<?php echo __('toolTipStatoElaborazione');?>" class="stato_<?php echo strtolower($result['Delivery']['stato_elaborazione']); ?>"></td>
		<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Delivery']['created']); ?></td>
		<td class="actions-table-img-5">
			<?php echo $this->Html->link(null, array('action' => 'calendar_view', null, 'delivery_id='.$result['Delivery']['id']), array('class' => 'action actionDeliveryCalendar','title' => __('View Calendar Delivery'))); 
			if($result['Delivery']['sys']=='N') {
				echo $this->Html->link(null, array('action' => 'copy', null, 'delivery_id='.$result['Delivery']['id']),array('class' => 'action actionCopy','title' => __('Copy')));
				echo $this->Html->link(null, array('action' => 'edit', null, 'delivery_id='.$result['Delivery']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); 
				echo $this->Html->link(null, array('controller' => 'Pages', 'action' => 'export_docs_delivery', null, 'delivery_id='.$result['Delivery']['id']),array('class' => 'action actionPrinter','title' => __('Print Delivery')));
				echo $this->Html->link(null, array('action' => 'delete', null, 'delivery_id='.$result['Delivery']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 
			}
			?>
		</td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['Delivery']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo $colspan;?>" id="tdViewId-<?php echo $result['Delivery']['id'];?>"></td>
	</tr>
<?php endforeach; 
	
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

	} 
	else  
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora consegne registrate"));
	
echo '</div>';
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.actionCopy').click(function() {

		if(!confirm("Sei sicuro di voler copiare la consegna selezionata?")) {
			return false;
		}		
		return true;
	});		
});
</script>