<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Categories Articles'), array('controller' => 'Categories', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Category Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="categories form">
<?php echo $this->Form->create('CategoriesArticle');?>
	<fieldset>
		<legend><?php echo __('Edit Category Article'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('parent_id', array('options' => $parents,'empty' => Configure::read('option.empty'),'escape' => false));
		echo $this->Form->input('name');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Categories Articles'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('Category.id')),array('class' => 'action actionDelete','title' => __('Delete'))); ?></li>
	</ul>
</div>