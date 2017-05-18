<?php
if($user->organization['Organization']['hasStoreroom'] == 'Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y' && $user->organization['Organization']['hasVisibility'] == 'Y')
	$colspan = '10';
else
if(($user->organization['Organization']['hasStoreroom'] == 'N' || $user->organization['Organization']['hasStoreroomFrontEnd']=='N') && $user->organization['Organization']['hasVisibility'] == 'N')
	$colspan = '8';
else
	$colspan = '9';
?>
<div class="deliveries">
	<h2 class="ico-deliveries">
		<?php echo __('Deliveries history');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Deliveries current'), array('action' => 'index'),array('class' => 'action actionList','title' => __('Deliveries current'))); ?></li>
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
			<th><?php echo $this->Paginator->sort('data');?></th>
			<th><?php echo __('Aperto/Chiuso');?></th>
			<th><?php echo $this->Paginator->sort('nota');?></th>
			<th></th>
			<?php 
			if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') 
				echo '<th>'.$this->Paginator->sort('isToStoreroom','Dispensa').'</th>';
				
			if($user->organization['Organization']['hasVisibility']=='Y') 
				echo '<th>'.$this->Paginator->sort('isVisibleBackOffice',__('isVisibleBackOffice')).'</th>';
			?>	
			<th></th>		
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
		<td><?php echo $result['Delivery']['luogo']; ?></td>
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
			?>
		</td>
		<td><div class="small"><?php echo $result['Delivery']['nota']; ?></div></td>
		<td style="padding:20px" class="nota_evidenza_<?php echo strtolower($result['Delivery']['nota_evidenza']); ?>">&nbsp;</td>
		<?php
		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') 
			echo '<td title="'.__('toolTipIsToStoreroom').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isToStoreroom']).'"></td>';

		if($user->organization['Organization']['hasVisibility']=='Y') 
			echo '<td title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['Delivery']['isVisibleBackOffice']).'"></td>';		
		echo '<td>';
		if(!empty($result['Delivery']['gcalendar_event_id']))
			echo '<img src="'.Configure::read('App.server').Configure::read('App.img.cake').'/gcalendar.png" title="Inserito in GCalendar con evento num '.$result['Delivery']['gcalendar_event_id'].'" />';
		echo '</td>';
		?>			
		<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Delivery']['created']); ?></td>
		<td class="actions-table-img-3">
			<?php 
			echo $this->Html->link(null, array('action' => 'copy', null, 'delivery_id='.$result['Delivery']['id']),array('class' => 'action actionCopy','title' => __('Copy')));
			echo $this->Html->link(null, array('controller' => 'Pages', 'action' => 'export_docs_delivery', null, 'delivery_id='.$result['Delivery']['id']),array('class' => 'action actionPrinter','title' => __('Print Delivery')));
			echo $this->Html->link(null, array('action' => 'delete', null, 'delivery_id='.$result['Delivery']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 
			?>
		</td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['Delivery']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo $colspan;?>" id="tdViewId-<?php echo $result['Delivery']['id'];?>"></td>
	</tr>
<?php endforeach; ?>
	</table>
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
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora consegne chiuse"));
		
echo '</div>';
?>