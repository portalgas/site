<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdGroups'), array('controller' => 'ProdGroups', 'action' => 'index'));
$this->Html->addCrumb(__('Edit ProdGroup'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="prod_groups form">
<?php echo $this->Form->create('ProdGroup');?>
	<fieldset>
		<legend><?php echo __('Edit ProdGroup'); ?></legend>
		
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
	?>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Prod Groups'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('ProdGroups.id')),array('class' => 'action actionDelete','title' => __('Delete'))); ?></li>
	</ul>
</div>