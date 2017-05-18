<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'Suppliers', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Supplier'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="suppliers form">
<?php 
echo '<div class="box-details">';
if(!empty($this->request->data['SuppliersOrganization'])) {?>
	
	<h2>Il produttore è associato alle seguenti organizzazioni</h2>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('N');?></th>
		<th>Nome</th>
		<th>Descrizione</th>
		<th>Contatti</th>
	</tr>
	<?php		
	foreach($this->request->data['SuppliersOrganization'] as $i => $SuppliersOrganization) { ?>	
			<tr>
				<td><?php echo ($i+1);?></td>
				<td style="white-space:nowrap;"><?php echo $SuppliersOrganization['Organization']['name'];?></td>
				<td style="white-space:nowrap;"><?php echo $SuppliersOrganization['Organization']['descrizione'];?></td>
				<td style="white-space:nowrap;">
				<?php 
					if(!empty($SuppliersOrganization['Organization']['mail']))  echo '<a class="link_mailto" title="'.__('Email send').'" target="_blank" href="mailto:'.$SuppliersOrganization['Organization']['mail'].'"></a>&nbsp;';
					if(!empty($SuppliersOrganization['Organization']['www']))  echo '<a class="link_www" target="_blank" href="'.$this->App->traslateWww($SuppliersOrganization['Organization']['www']).'"><img alt="Vai al sito del produttore" src="'.Configure::read('App.img.cake').'/icons/16x16/world_link.png" /></a>&nbsp;';
					?>
				</td>
			</tr>
		<?php 
	}
	echo '</table>';
	echo "\r\n";
	echo "<p>Salvando il produttore saranno aggiornati, dei produttori associati alle organizzazioni, i seguenti campi:";
	echo "<ul>";
	echo "	<li>Nome</li>";
	if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
		echo "	<li>Categoria del produttore</li>";
	echo "</ul>";
	echo "</p>";
}
else
{
	echo '<h2>Il produttore non è ancora associato ad alcuna organizzazione</h2>';
}
echo '</div>';

echo $this->Form->create('Supplier',array('id'=>'formGas','enctype' => 'multipart/form-data'));?>
	<fieldset>
		<legend><?php echo __('Edit Supplier'); ?></legend>

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
							echo $this->Form->input('category_supplier_id', array('options' => $categories, 'default' => 1, 'empty' => Configure::read('option.empty'),'escape' => false));
						echo $this->Form->input('name',array('label'=>'Ragione sociale'));
						echo $this->Form->input('nome');
						echo $this->Form->input('cognome');
						echo $this->Form->input('descrizione', array('label'=>'Descrizione breve',  'type' => 'text', 'after' => '<br /><img width="150" class="print_screen" id="print_screen_supplier_nota" src="'.Configure::read('App.img.cake').'/print_screen_supplier_nota.jpg" title="" border="0" />'));
						
						echo $this->App->drawFormRadio('Supplier','stato',array('options' => $stato, 'value'=>$this->Form->value('Supplier.stato'), 'label'=>__('Stato'), 'required'=>'required'));						
					
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
						//echo $this->Form->input('nota');
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
											
						if(!empty($this->request->data['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$this->request->data['Supplier']['img1'])) {	
							echo '<img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$this->request->data['Supplier']['img1'].'" /> <br />';
							echo '<span>Nome file '.$this->request->data['Supplier']['img1'].'</span>';
							
							if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$this->request->data['Supplier']['j_content_id'].'a.jpg'))
								echo '<p><img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$this->request->data['Supplier']['j_content_id'].'a.jpg" /></p>';
						
							if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$this->request->data['Supplier']['j_content_id'].'b.jpg'))
								echo '<p><img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$this->request->data['Supplier']['j_content_id'].'b.jpg" /></p>';
						
							if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$this->request->data['Supplier']['j_content_id'].'c.jpg'))
								echo '<p><img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$this->request->data['Supplier']['j_content_id'].'c.jpg" /></p>';
						
							if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$this->request->data['Supplier']['j_content_id'].'d.jpg'))
								echo '<p><img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$this->request->data['Supplier']['j_content_id'].'d.jpg" /></p>';
						
						}
						else
							echo 'Immagine non presente';
						
						
						echo $this->element('legendaSupplierImg');					
						?>
				</div>
			</div>

	</fieldset>
<?php 
if(!empty($sort)) echo $this->Form->hidden('sort',array('value'=>$sort));
if(!empty($direction)) echo $this->Form->hidden('direction',array('value'=>$direction));
if(!empty($page)) echo $this->Form->hidden('page',array('value'=>$page));

echo $this->Form->hidden('id',array('value' => $this->Form->value('Supplier.id')));
echo $this->Form->end(__('Submit'));

echo $this->element('print_screen_supplier');
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Suppliers'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('Supplier.id')),array('class' => 'action actionDelete','title' => __('Delete'))); ?></li>				
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