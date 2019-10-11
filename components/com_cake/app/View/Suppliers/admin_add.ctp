<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'Suppliers', 'action' => 'index'));
$this->Html->addCrumb(__('Add Supplier'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="suppliers form">
<?php echo $this->Form->create('Supplier',array('id'=>'formGas','enctype' => 'multipart/form-data'));?>
	<fieldset>
		<legend><?php echo __('Add Supplier'); ?></legend>
		
		<?php
			echo '<div class="tabs">';
			echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
			echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('User profile').'</a></li>';
			echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Contatti').'</a></li>';
			echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Altro').'</a></li>';
			echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Joomla').'</a></li>';			
			echo '</ul>';

			echo '<div class="tab-content">';
			echo '<div class="tab-pane fade active in" id="tabs-0">';
			
						// if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
						echo $this->Form->input('category_supplier_id', array('options' => $categories, 'empty' => Configure::read('option.empty'),'escape' => false));
							
						echo $this->Form->input('name',array('label'=>'Ragione sociale'));
						echo $this->Form->input('nome');
						echo $this->Form->input('cognome');
						echo $this->Form->input('descrizione', array('label'=>'Descrizione breve',  'type' => 'text', 'after' => '<br /><img width="150" class="print_screen" id="print_screen_supplier_nota" src="'.Configure::read('App.img.cake').'/print_screen_supplier_nota.jpg" title="" border="0" />'));
						
						echo $this->App->drawFormRadio('Supplier','can_promotions',array('options' => $can_promotions, 'value' => 'N', 'label'=>__('SupplierCanPromotions'), 'required'=>'required'));						
						echo $this->App->drawFormRadio('Supplier','stato',array('options' => $stato, 'value' => 'Y', 'label'=> __('Stato'), 'required'=>'required'));						
					
						echo $this->element('legendaSupplier');
		
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-1">';
			
						echo $this->Form->input('indirizzo');
						echo $this->Form->input('localita');
						echo $this->Form->input('cap',array('style'=>'width:100px;'));
						echo $this->Form->input('provincia',array('style'=>'width:75px;'));
						echo $this->Form->input('lat',array('style'=>'width:200px;'));
						echo $this->Form->input('lng',array('style'=>'width:200px;'));
						echo '<p><a href="'.Configure::read('UrlApiGpsCoordinate').'" target="_blank">geocode</a></p>';
						echo $this->Form->input('telefono');
						echo $this->Form->input('telefono2');
						echo $this->Form->input('fax');
						echo $this->Form->input('mail');
						echo $this->Form->input('www');
		
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-2">';
			
						echo $this->Form->input('nota');
						echo $this->Form->input('cf');
						echo $this->Form->input('piva');
						echo $this->Form->input('conto');
						echo $this->Form->input('delivery_type_id', array('options' => $suppliersDeliveriesType, 'default' => 1, 'label' => __('SuppliersDeliveriesTypes'), 'escape' => false));																							
		
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-3">';
			
						echo '<div class="input text">';
						echo $this->App->drawTooltip(null,__('toolJoomlaContent'),$type='INFO');
						echo '<label for="">Articolo</label> ';
						echo $modalArticle;
						echo '</div>';
						echo '<p style="float:right;">Aggiungere al fondo del testo {flike}</p>';
						
						echo $this->Form->input('Document.img1', array(
											    'between' => '<br />',
											    'type' => 'file',
											     'label' => 'Carica una nuova immagine'
											));
											
						echo $this->element('legendaSupplierImg');											
			echo '</div>';
			echo '</div>'; // tab-content
			echo '</div>';
			echo '</fieldset>';
			
echo $this->Form->end(__('Submit'));

echo $this->element('print_screen_supplier');?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Suppliers'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>