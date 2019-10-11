<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Organizations'), array('controller' => 'Organizations', 'action' => 'index'));
$this->Html->addCrumb(__('Title Delete Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="suppliers form">
<?php echo $this->Form->create('Organization', array('type' => 'post'));?>
	<fieldset>
		<legend><?php echo __('Title Delete Organization'); ?></legend>

		<div class="input text"><label for="">Organizzazione</label> <?php echo $results['Organization']['name'];?></div>
		<div class="input text"><label for="">Descrizione</label> <?php echo $results['Organization']['descrizione'];?></div>
		<div class="input text"><label for="">Mail</label> <?php echo $results['Organization']['mail'];?></div>
            
		<?php echo $this->Element('boxMsg',array('msg' => "Elementi associati che verranno cancellati definitivamente")); ?>

		<?php (count($results['Delivery']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label for="">Totale consegne associate</label><span class="<?php echo $class;?>"><?php echo count($results['Delivery']);?></span></div>

		<?php (count($results['User']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label for="">Totale utenti associati</label><span class="<?php echo $class;?>"><?php echo count($results['User']);?></span></div>

	</fieldset>
<?php
echo $this->Form->hidden('id',array('value' => $results['Organization']['id']));
echo $this->Form->end(__('Submit Delete'));
?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Organizations'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $results['Organization']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); ?></li>
	</ul>
</div>
