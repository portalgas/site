<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Events'), array('controller' => 'Events', 'action' => 'index'));
$this->Html->addCrumb(__('List Event Types'), array('controller' => 'EventTypes', 'action' => 'index'));
$this->Html->addCrumb(__('Add Event Type'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="eventTypes form">
<?php echo $this->Form->create('EventType', array('id' => 'formGas'));?>
	<fieldset>
 		<legend><?php __('Add Event Type'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('color', 
					array('options' => array(
						'Blue' => 'Blue',
						'Red' => 'Red',
						'Pink' => 'Pink',
						'Purple' => 'Purple',
						'Orange' => 'Orange',
						'Green' => 'Green',
						'Gray' => 'Gray',
						'Black' => 'Black',
						'Brown' => 'Brown'
					)));

	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Event Types'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

