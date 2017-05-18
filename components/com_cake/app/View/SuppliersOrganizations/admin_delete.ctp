<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'SuppliersOrganizations', 'action' => 'index'));
$this->Html->addCrumb(__('Title Delete Supplier Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="suppliers form">
<?php echo $this->Form->create('SuppliersOrganization', array('type' => 'post'));?>
	<fieldset>
		<legend><?php echo __('Title Delete Supplier Organization'); ?></legend>

		<div class="input text"><label for="">Categoria</label><?php echo $results['CategoriesSupplier']['name'];?></div>
		<div class="input text"><label for="">Ragione sociale</label><?php echo $results['SuppliersOrganization']['name'];?></div>
		<div class="input text"><label for="">Descrizione</label><?php echo $results['Supplier']['descrizione'];?></div>

		<?php echo $this->Element('boxMsg',array('msg' => "Elementi associati che verranno cancellati definitivamente")); ?>

		<?php (count($results['Article']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label for="">Totale articoli</label><span class="<?php echo $class;?>"><?php echo count($results['Article']);?></span></div>

		<?php (count($results['Order']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label for="">Totale ordini</label><span class="<?php echo $class;?>"><?php echo count($results['Order']);?></span></div>

		<?php (count($results['SuppliersOrganizationsReferent']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label for="">Totale referenti</label><span class="<?php echo $class;?>"><?php echo count($results['SuppliersOrganizationsReferent']);?></span></div>

	</fieldset>
<?php
echo $this->Form->hidden('id',array('value' => $results['SuppliersOrganization']['id']));
echo $this->Form->end(__('Submit Delete'));
?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Suppliers Organization'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $results['SuppliersOrganization']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); ?></li>
	</ul>
</div>
