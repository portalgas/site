<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Categories'), array('controller' => 'Categories', 'action' => 'index'));
$this->Html->addCrumb(__('Add Category'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="categories form">
<?php echo $this->Form->create('Category');?>
	<fieldset>
		<legend><?php echo __('Add Category'); ?></legend>
	<?php
		echo $this->Form->input('parent_id', array('options' => $parents,'empty' => Configure::read('option.empty'),'escape' => false));
		echo $this->Form->input('Name');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Categories'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>
