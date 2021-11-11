<div class="categories">
	<h2 class="ico-categories">
		<?php echo __('Categories');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Category'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Category'))); ?></li>
			</ul>
		</div>
	</h2>

	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Name');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($results as $key => $value):?>
	<tr>
		<td><?php echo $value; ?></td>
		<td class="actions-table-img">
			<?php echo $this->Html->link(null, array('action' => 'edit', $key),array('class' => 'action actionEdit','title' => __('Edit'))); ?>
			<?php echo $this->Html->link(null, array('action' => 'delete', $key),array('class' => 'action actionDelete','title' => __('Delete'))); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
