<div class="events">
	<h2 class="ico-wait">
		<?php echo __('Events');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Event'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Event'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php
	if(!empty($results)) {
	?>
		<table cellpadding="0" cellspacing="0">
		<tr>
				<th><?php echo $this->Paginator->sort('event_type_id', __('EventsTypesShort'));?></th>
				<th><?php echo $this->Paginator->sort('title');?></th>
				<th><?php echo $this->Paginator->sort('EventUser');?></th>
				<th><?php echo $this->Paginator->sort('start', __('EventStartShort'));?></th>
				<th><?php echo $this->Paginator->sort('end', __('EventEndShort'));?></th>
				<th><?php echo $this->Paginator->sort('date_alert_mail', __('EventDateAlertMailShort'));?></th>
				<th><?php echo $this->Paginator->sort('date_alert_fe', __('EventDateAlertFEShort'));?></th>
				<th><?php echo $this->Paginator->sort('isVisibleFrontEnd',__('isVisibleFrontEnd'));?></th>
				<th><?php echo __('List Users');?></th>
				<th class="actions"></th>
		</tr>
		<?php
		$month_start_old = '';
		foreach ($results as $result):
		
			$month = substr($result['Event']['start'],5,2);
			
			if(empty($month_start_old) || $month!=$month_start_old) {
				echo '<tr>';
				echo '<td class="trGroup" colspan="10">'.$this->Time->i18nFormat($result['Event']['start'],"%B %Y").'</td>';
				echo '</tr>';
			}
		?>
		<tr>
			<td>
				<?php echo $this->Html->link($result['EventType']['name'], array('controller' => 'event_types', 'action' => 'edit', $result['EventType']['id'])); ?>
			</td>
			<td><?php echo $result['Event']['title']; ?></td>
			<td><?php echo $result['User']['name']; ?></td>
			<td style="white-space:nowrap;"><?php echo $this->Time->i18nFormat($result['Event']['start'],"%A %e %B %Y alle %H:%M"); ?></td>
			<td style="white-space:nowrap;"><?php echo $this->Time->i18nFormat($result['Event']['end'],"%A %e %B %Y alle %H:%M"); ?></td>
			<td style="white-space:nowrap;"><?php echo $this->Time->i18nFormat($result['Event']['date_alert_mail'],"%A %e %B %Y"); ?></td>
			<td style="white-space:nowrap;"><?php echo $this->Time->i18nFormat($result['Event']['date_alert_fe'],"%A %e %B %Y"); ?></td>
			
			<?php
			echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Event']['isVisibleFrontEnd']).'"></td>';
			echo '<td>';
			if(!empty($result['Event']['EventsUser']))
				foreach($result['Event']['EventsUser'] as $eventsUser) {
					echo $eventsUser['User']['name'].'<br />';
				} 
			echo '</td>';
			?>
			<td class="actions">
				<?php
				echo $this->Html->link(null, array('action' => 'edit', null, 'event_id='.$result['Event']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); 
				echo $this->Html->link(null, array('action' => 'delete', null, 'event_id='.$result['Event']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 			
				?>
			</td>
		</tr>
	<?php 
			$month_start_old = $month;
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
	
	} 
	else  
		echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFonud', 'msg' => "Non ci sono ancora attività registrate"));
	?>
</div>
