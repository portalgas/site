<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'Suppliers', 'action' => 'index'));
$this->Html->addCrumb(__('Title Delete Supplier'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="suppliers form">
<?php echo $this->Form->create('Supplier', array('type' => 'post'));?>
	<fieldset>
		<legend><?php echo __('Title Delete Supplier'); ?></legend>

		<div class="input text"><label for="">Nome</label> <?php echo $results['Supplier']['name'];?></div>
		<div class="input text"><label for="">Descrizione</label> <?php echo $results['Supplier']['descrizione'];?></div>
		<div class="input text"><label for="">Indirizzo</label> <?php echo $results['Supplier']['indirizzo'];?></div>
		<div class="input text"><label for="">Localita</label> <?php echo $results['Supplier']['localita'];?></div>

		<?php echo $this->Element('boxMsg',array('msg' => "Elementi associati che verranno cancellati definitivamente")); ?>

		<?php (count($results['SuppliersOrganization']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');?>
		<div class="input text"><label class="big" for="">Totale G.A.S. associati al produttore</label><span class="<?php echo $class;?>"><?php echo count($results['SuppliersOrganization']);?></span></div>

		<?php
			if(!empty($totArticlesResults)) {
				foreach($totArticlesResults as $totArticlesResult) {
					echo '<div class="input text"><label class="big" for="">Totale articoli associati a <b>'.$totArticlesResult['Organization']['name'].'</b></label> ';
					if($totArticlesResult['Articles']['totArticles']==0)
						$class = 'qtaZero';
					else
						$class = 'qtaUno';
					echo '<span class="'.$class.'">'.$totArticlesResult['Articles']['totArticles'].'</span></div>';
				}
			}
		?>
	</fieldset>
<?php
echo $this->Form->hidden('id',array('value' => $results['Supplier']['id']));
echo $this->Form->end(__('Submit Delete'));
?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Suppliers'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $results['Supplier']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); ?></li>
	</ul>
</div>