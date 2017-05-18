<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Prod Groups'), array('controller' => 'ProdGroups', 'action' => 'index'));
$this->Html->addCrumb(__('Add Prod Group'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="prod_groups form">
<?php echo $this->Form->create('ProdGroup');?>
	<fieldset>
		<legend><?php echo __('Add Prod Group'); ?></legend>
	<?php
		echo $this->Form->input('name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Prod Groups'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>
