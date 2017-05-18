<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'Suppliers', 'action' => 'index'));
$this->Html->addCrumb(__('Add Supplier'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="suppliers form">
<?php echo $this->Form->create('Supplier',array('id'=>'formGas','enctype' => 'multipart/form-data'));?>
	<fieldset>
		<legend><?php echo __('Add Supplier'); ?></legend>
         <div class="tabs">
             <ul>
                 <li><a href="#tabs-0"><span><?php echo __('Dati anagrafici'); ?></span></a></li>
                 <li><a href="#tabs-1"><span><?php echo __('Contatti'); ?></span></a></li>
                 <li><a href="#tabs-2"><span><?php echo __('Altro'); ?></span></a></li>
                 <li><a href="#tabs-3"><span><?php echo __('Joomla'); ?></span></a></li>
             </ul>

             <div id="tabs-0">
					<?php
						// if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
						echo $this->Form->input('category_supplier_id', array('options' => $categories, 'empty' => Configure::read('option.empty'),'escape' => false));
							
						echo $this->Form->input('name',array('label'=>'Ragione sociale'));
						echo $this->Form->input('nome');
						echo $this->Form->input('cognome');
						echo $this->Form->input('descrizione', array('label'=>'Descrizione breve',  'type' => 'text', 'after' => '<br /><img width="150" class="print_screen" id="print_screen_supplier_nota" src="'.Configure::read('App.img.cake').'/print_screen_supplier_nota.jpg" title="" border="0" />'));
						
						echo $this->App->drawFormRadio('Supplier','stato',array('options' => $stato, 'value'=>'Y', 'label'=>__('Stato'), 'required'=>'required'));						
					
						echo $this->element('legendaSupplier');
						?>
				</div>
            <div id="tabs-1">
					<?php
						echo $this->Form->input('indirizzo');
						echo $this->Form->input('localita');
						echo $this->Form->input('cap',array('style'=>'width:50px;'));
						echo $this->Form->input('provincia',array('style'=>'width:25px;'));
						echo $this->Form->input('lat',array('style'=>'width:200px;'));
						echo $this->Form->input('lng',array('style'=>'width:200px;'));
						echo '<p><a href="http://maps.google.com/maps/api/geocode/json?sensor=false&address=" target="_blank">geocode</a></p>';
						echo $this->Form->input('telefono');
						echo $this->Form->input('telefono2');
						echo $this->Form->input('fax');
						echo $this->Form->input('mail');
						echo $this->Form->input('www');
					?>
				</div>
           		<div id="tabs-2">
					<?php
						echo $this->Form->input('nota');
						echo $this->Form->input('cf');
						echo $this->Form->input('piva');
						echo $this->Form->input('conto');
					?>
				</div>
			    <div id="tabs-3">
					<?php
						echo '<div class="input text">';
						echo $this->App->drawTooltip(null,__('toolJoomlaContent'),$type='INFO');
						echo '<label for="">Articolo</label>';
						echo $modalArticle;
						echo '</div>';
						echo '<p style="float:right;">Aggiungere al fondo del testo {flike}</p>';
						
						echo $this->Form->input('Document.img1', array(
											    'between' => '<br />',
											    'type' => 'file',
											     'label' => 'Carica una nuova immagine'
											));
											
						echo $this->element('legendaSupplierImg');											
					?>
				</div>
			</div>

	</fieldset>
<?php 
echo $this->Form->end(__('Submit'));

echo $this->element('print_screen_supplier');?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Suppliers'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});	
});
</script>