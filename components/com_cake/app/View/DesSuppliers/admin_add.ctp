<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesSuppliers'),array('controller' => 'DesSuppliers', 'action' => 'index'));
$this->Html->addCrumb(__('Add DesSupplier'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<h2 class="ico-organizations">';
echo __('List DesSuppliers');
echo '<div class="actions-img">';

echo '</div>';
echo '</h2>';
?>

<div class="desSuppliers form">
<?php echo $this->Form->create('DesSupplier',array( 'id' => 'formGas'));?>
	<fieldset style="min-height:600px;">
		<legend><?php echo __('Scegli Produttore'); ?></legend>
	<?php
		$options =  array('id' => 'supplier_id',
						  'empty' => __('FilterToSuppliers'),
						  'options' => $desOrganizations);
		$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
						  
		echo $this->Form->input('supplier_id', $options);
	?>
	</fieldset>
<?php 
echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List DesSuppliers'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>
