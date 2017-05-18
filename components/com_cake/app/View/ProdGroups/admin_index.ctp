<div class="prod_groups">
	<h2 class="ico-users">
		<?php echo __('Prod Groups');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Prod Groups'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Prod Groups'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php foreach ($prodGroups as $prodGroup): ?>
	<tr>
		<td><?php echo h($prodGroup['ProdGroup']['id']); ?>&nbsp;</td>
		<td><?php echo h($prodGroup['ProdGroup']['name']); ?>&nbsp;</td>
		<td class="actions-table-img-3">
			<?php echo $this->Html->link(null, array('controller' => 'ProdUsersGroups', 'action' => 'index', $prodGroup['ProdGroup']['id']),array('class' => 'action actionList', 'title' => __('List ProdUsersGroup'))); ?>
			<?php echo $this->Html->link(null, array('action' => 'edit', $prodGroup['ProdGroup']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); ?>
			<?php echo $this->Html->link(null, array('action' => 'delete', $prodGroup['ProdGroup']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); ?>			
		</td>
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
	?>
	</div>
</div>
