<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Events'), array('controller' => 'Events', 'action' => 'index'));
$this->Html->addCrumb(__('List Event Types'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="events form">
	<h2 class="ico-orders-history">
		<?php echo __('EventsTypes');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Event Type'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Event Type'))); ?></li>
			</ul>
		</div>
	</h2>

		
	<?php
	if(!empty($results)) {
	?>
		<div class="table-responsive"><table class="table table-hover table-striped">
		<tr>
				<th><?php echo $this->Paginator->sort('name');?></th>
				<th><?php echo $this->Paginator->sort('color');?></th>
				<th class="actions"></th>
		</tr>
		<?php
		$i = 0;
		foreach ($results as $result):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $result['EventType']['name']; ?>&nbsp;</td>
			<td><?php echo $result['EventType']['color']; ?>&nbsp;</td>
			<td class="actions">
				<?php
				echo $this->Html->link(null, array('action' => 'edit', null, 'id='.$result['EventType']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); 
				echo $this->Html->link(null, array('action' => 'delete', null, 'id='.$result['EventType']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 			
				?>
			</td>
		</tr>
	<?php 
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
		echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Non ci sono ancora tipologie di attività registrate"));
	?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Events'), array('controller' => 'Events', 'action' => 'index'),array('class'=>'action actionList'));?></li>
	</ul>
</div>
