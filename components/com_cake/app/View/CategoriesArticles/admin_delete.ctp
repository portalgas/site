<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Categories Articles'), array('controller' => 'Categories', 'action' => 'index'));
$this->Html->addCrumb(__('Title Delete Category Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="suppliers form">
<?php echo $this->Form->create('CategoryArticle', array('type' => 'post'));?>
	<fieldset>
		<legend><?php echo __('Title Delete Category Article'); ?></legend>

		<div class="input text"><label for="">Decorrenza</label> <?php echo $results['CategoriesArticle']['name'];?></div>

		<?php echo $this->Element('boxMsg',array('msg' => "Elementi associati che verranno cancellati definitivamente")); ?>

		<div class="input text"><label for="">Eventuali associazioni con fornitori saranno annullate</label><span class="qtaZero">...</span></div>

	</fieldset>
<?php
echo $this->Form->hidden('id',array('value' => $results['CategoriesArticle']['id']));
echo $this->Form->end(__('Submit Delete'));
?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Categories Articles'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $results['CategoriesArticle']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); ?></li>
	</ul>
</div>