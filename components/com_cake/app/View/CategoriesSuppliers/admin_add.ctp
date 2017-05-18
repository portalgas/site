<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Categories Supplier'), array('controller' => 'CategoriesSuppliers', 'action' => 'index'));
$this->Html->addCrumb(__('Add Category Supplier'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="categories form">
<?php echo $this->Form->create('CategoriesSupplier');?>
	<fieldset>
		<legend><?php echo __('Add Category Supplier'); ?></legend>
	<?php
		echo $this->Form->input('parent_id', array('options' => $parents,'empty' => Configure::read('option.empty'),'escape' => false));
		echo $this->Form->input('name');
		echo $this->Form->input('description');
		echo $this->Form->input('j_category_id', array('label'=>__('JCategory'), 'options' => $j_categories, 'empty' => Configure::read('option.empty'),'escape' => false, 
			'after'=>$this->App->drawTooltip(null,__('toolJoomlaCategory'),$type='INFO')));		
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
