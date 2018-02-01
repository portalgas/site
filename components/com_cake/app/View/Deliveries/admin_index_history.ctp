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
	foreach ($results as $i => $result) {
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);
		 
		echo '<tr class="view">';
		echo '<td>';
		echo '<a data-toggle="collapse" href="#ajax_details-'.$result['Delivery']['id'].'" title="'.__('Href_title_expand').'"><i class="fa fa-3x fa-search-plus" aria-hidden="true"></i></a>';
		echo '</td>';
		?>		
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
			echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.img.cake').'/gcalendar.png" title="Inserito in GCalendar con evento num '.$result['Delivery']['gcalendar_event_id'].'" />';
		echo '</td>';
		echo '<td style="white-space: nowrap;">';
		echo $this->App->formatDateCreatedModifier($result['Delivery']['created']);
		echo '</td>';

		echo '<td>';
		$modal_url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Menus&action=delivery_history&id='.$result['Delivery']['id'].'&format=notmpl';
		$modal_size = 'md'; 
		$modal_header = __('Delivery');
		echo '<button type="button" class="btn btn-primary btn-menu" data-attr-url="'.$modal_url.'" data-attr-size="'.$modal_size.'" data-attr-header="'.$modal_header.'" ><i class="fa fa-2x fa-navicon"></i></button>';
		echo '</td>';
		echo '</tr>';
		
	echo '<tr data-attr-action="deliveries-'.$result['Delivery']['id'].'" class="collapse ajax_details" id="ajax_details-'.$result['Delivery']['id'].'">';
	echo '	<td colspan="2"></td>'; 
	echo '	<td colspan="'.$colspan.'" id="ajax_details_content-'.$result['Delivery']['id'].'"></td>';
	echo '</tr>';
	
}
?>
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