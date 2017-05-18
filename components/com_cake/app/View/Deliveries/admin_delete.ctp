<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Deliveries'), array('controller' => 'Deliveries', 'action' => 'index'));
$this->Html->addCrumb(__('Title Delete Delivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="suppliers form">
<?php echo $this->Form->create('Delivery', array('type' => 'post'));?>
	<fieldset>
		<legend><?php echo __('Title Delete Delivery'); ?></legend>

		<div class="input text"><label for=""><?php echo __('Delivery');?></label>
		<?php 
		if($results['Delivery']['sys']=='N')
			echo $results['Delivery']['luogoData'];
		else
			echo $results['Delivery']['luogo'];
		?></div>

		<?php echo $this->Element('boxMsg',array('msg' => "Elementi associati che verranno cancellati definitivamente")); ?>

		<?php (count($results['Order']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label for="">Totale ordini associati alla consegna</label><span class="<?php echo $class;?>"><?php echo count($results['Order']);?></span></div>

		<?php ($results['totStorerooms'] > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label for="">Eventuali acquisti prodotti in dispensa</label><span class="qtaZero"><?php echo $results['totStorerooms'];?></span></div>

	</fieldset>
<?php
echo $this->Form->hidden('id',array('value' => $results['Delivery']['id']));
echo $this->Form->end(__('Submit Delete'));
?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Deliveries'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit'), array('action' => 'edit', null, 'delivery_id='.$results['Delivery']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); ?></li>
	</ul>
</div>