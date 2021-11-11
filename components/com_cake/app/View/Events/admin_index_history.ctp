<div class="events">
	<h2 class="ico-wait">
		<?php echo __('EventsHistory');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('List Events'), array('action' => 'index'),array('class' => 'action actionList','title' => __('List Events'))); ?></li>
			</ul>
		</div>
	</h2>
	
	
	<?php
	if(!empty($results)) {
	?>
	<div class="table-responsive"><table class="table table-hover table-striped">
	<tr>
			<th><?php echo $this->Paginator->sort('event_type_id', __('EventsTypesShort'));?></th>
			<th><?php echo $this->Paginator->sort('title');?></th>
			<th><?php echo $this->Paginator->sort('EventUser');?></th>
			<th><?php echo $this->Paginator->sort('start', __('EventStartShort'));?></th>
            <th><?php echo $this->Paginator->sort('end', __('EventEndShort'));?></th>
            <th><?php echo __('List Users');?></th>
			<th class="actions"></th>
	</tr>
	<?php
	$month_start_old = '';
	foreach ($results as $result):
	
		$month = substr($result['Event']['start'],5,2);
		
		if(empty($month_start_old) || $month!=$month_start_old) {
			echo '<tr>';
			echo '<td class="trGroup" colspan="7">'.$this->Time->i18nFormat($result['Event']['start'],"%B %Y").'</td>';
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
		<td>
		<?php 	
		if(!empty($result['Event']['EventsUser']))
			foreach($result['Event']['EventsUser'] as $eventsUser) {
				echo $eventsUser['User']['name'].'<br />';
			} 
		?>
		</td>
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
	
	} 
	else  
		echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora attività registrate"));
	?>
</div>