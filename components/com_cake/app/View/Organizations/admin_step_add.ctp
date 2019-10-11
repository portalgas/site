<div class="organizations">
	<h2 class="ico-organizations">
		<?php echo __('GasOrganizations');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Organization'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Organization'))); ?></li>
			</ul>
		</div>
	</h2>
	
<?php 
	echo $this->element('legendaOrganization', ['max_id' => $max_id]);
?>