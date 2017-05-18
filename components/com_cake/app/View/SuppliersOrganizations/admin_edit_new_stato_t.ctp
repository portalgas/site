<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'SuppliersOrganizations', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Supplier Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="suppliers form">
<?php echo $this->Form->create('SuppliersOrganization');?>
	<fieldset>
		<legend><?php echo __('Edit Supplier Organization'); ?></legend>

         <div class="tabs">
             <ul>
                 <li><a href="#tabs-0"><span><?php echo __('Dati anagrafici'); ?></span></a></li>
                 <li><a href="#tabs-1"><span><?php echo __('Contatti'); ?></span></a></li>
                 <li><a href="#tabs-2"><span><?php echo __('Altro'); ?></span></a></li>
                 <li><a href="#tabs-3"><span><?php echo __('Immagine'); ?></span></a></li>
                 <li><a href="#tabs-4"><span><?php echo __('Articles'); ?></span></a></li>
				 <li><a href="#tabs-5"><span><?php echo __('Permissions'); ?></span></a></li>
             </ul>

             <div id="tabs-0">
                    <?php
                            if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
                                    echo $this->Form->input('category_supplier_id',array('options' => $categories, 'default' => $results['SuppliersOrganization']['category_supplier_id'], 'empty' => 'Filtra per categoria','escape' => false, 'required' => false));

                            echo $this->Form->input('name',array('value' => $results['Supplier']['name'], 'label'=>'Ragione sociale', 'required' => false));
                            echo $this->Form->input('nome', array('value' => $results['Supplier']['nome']));
                            echo $this->Form->input('cognome', array('value' => $results['Supplier']['cognome']));
                            echo $this->Form->input('frequenza', array('value' => $results['SuppliersOrganization']['frequenza'], 'after'=>$this->App->drawTooltip(null,__('toolTipSupplierOrganizationFrequenza'),$type='HELP')));
						
                            echo $this->App->drawFormRadio('SuppliersOrganization','mail_order_open',array('options' => $mail_order_open, 'value' => $results['SuppliersOrganization']['mail_order_open'], 'label'=>__('MailOrderOpen'), 'required'=>'required',
                                                                                                            'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderOpen'),$type='HELP')));

                            echo $this->App->drawFormRadio('SuppliersOrganization','mail_order_close',array('options' => $mail_order_close, 'value' => $results['SuppliersOrganization']['mail_order_close'], 'label'=>__('MailOrderClose'), 'required'=>'required',
                                                                                                            'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderClose'),$type='HELP')));

                            echo $this->Form->input('descrizione',  array('value' => $results['Supplier']['descrizione'], 'label'=>'Descrizione breve', 'type' => 'text', 'after' => '<br /><img width="150" class="print_screen" id="print_screen_supplier_nota" src="'.Configure::read('App.img.cake').'/print_screen_supplier_nota.jpg" title="" border="0" />'));
                            ?>
			</div>
            <div id="tabs-1">
                            <?php
                                    echo $this->Form->input('indirizzo', array('required' => false, 'value' => $results['Supplier']['indirizzo']));
                                    echo $this->Form->input('localita', array('value' => $results['Supplier']['localita']));
                                    echo $this->Form->input('cap',array('style'=>'width:50px;', 'value' => $results['Supplier']['cap']));
                                    echo $this->Form->input('provincia',array('style'=>'width:25px;', 'value' => $results['Supplier']['provincia']));
                                    echo $this->Form->input('telefono', array('value' => $results['Supplier']['telefono']));
                                    echo $this->Form->input('telefono2', array('value' => $results['Supplier']['telefono2']));
                                    echo $this->Form->input('fax', array('value' => $results['Supplier']['fax']));
                                    echo $this->Form->input('mail', array('value' => $results['Supplier']['mail']));
                                    echo $this->Form->input('www', array('value' => $results['Supplier']['www']));
                            ?>
			</div>
            <div id="tabs-2">
                            <?php
                                    //echo $this->Form->input('nota', array('value' => $results['Supplier']['nota']));
                                    echo $this->Form->input('cf', array('value' => $results['Supplier']['cf']));
                                    echo $this->Form->input('piva', array('value' => $results['Supplier']['piva']));
                                    echo $this->Form->input('conto', array('value' => $results['Supplier']['conto']));
                            ?>
			</div>
             <div id="tabs-3">

                        <?php
                        if(!empty($results['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['img1'])) {
                                echo '<img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['img1'].'" /> <br />';

                                if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['j_content_id'].'a.jpg')) 
                                        echo '<p><img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['j_content_id'].'a.jpg" /></p>';

                                if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['j_content_id'].'b.jpg')) 
                                        echo '<p><img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['j_content_id'].'b.jpg" /></p>';

                                if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['j_content_id'].'c.jpg')) 
                                        echo '<p><img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['j_content_id'].'c.jpg" /></p>';

                                if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['j_content_id'].'d.jpg')) 
                                        echo '<p><img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['j_content_id'].'d.jpg" /></p>';

                        }
                        else
                                echo 'Immagine non presente';
                        ?>		

			</div>
            <div id="tabs-4">
             	<?php echo $this->Html->link(__('List Articles').' ('.$totArticlesAttivi.')', array('controller' => 'Articles', 'action' => 'context_articles_index', null,'FilterArticleSupplierId='.$results['SuppliersOrganization']['id']),array());?>		
			</div>
            <div id="tabs-5">
					<?php
						foreach($prod_gas_supplier_owner_articles as $key => $value)
							$prod_gas_supplier_owner_articles[$key] = $this->App->traslateEnum('ProdGasSupplier'.$value);
						echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_owner_articles',array('options' => $prod_gas_supplier_owner_articles, 'value' => $results['SuppliersOrganization']['owner_articles'], 'label'=>__('prod_gas_supplier_owner_articles'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierOwnerArticles'),$type='HELP')));
																										
						echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_can_view_orders',array('options' => $prod_gas_supplier_can_view_orders, 'value' => $results['SuppliersOrganization']['can_view_orders'], 'label'=>__('prod_gas_supplier_can_view_orders'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrders'),$type='HELP')));
																										
						echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_can_view_orders_users',array('options' => $prod_gas_supplier_can_view_orders_users, 'value' => $results['SuppliersOrganization']['can_view_orders_users'], 'label'=>__('prod_gas_supplier_can_view_orders_users'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrdersUsers'),$type='HELP')));
					?>
			</div>			
		</div>

	</fieldset>
<?php 
echo $this->Form->input('id', array('value' => $results['SuppliersOrganization']['id']));
echo $this->Form->end(__('Submit'));

echo $this->element('print_screen_supplier');
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Suppliers Organization'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('List Articles').' ('.$totArticlesAttivi.')', array('controller' => 'Articles', 'action' => 'context_articles_index', null,'FilterArticleSupplierId='.$results['SuppliersOrganization']['id']),array('class'=>'action actionList'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});	
	
	var battute = 75;
	
	jQuery("input[name='data[SuppliersOrganization][descrizione]']").after("<p style='float:right' class='avviso'>Hai ancora <strong>"+ (battute - jQuery("input[name='data[SuppliersOrganization][descrizione]']").val().length)+"</strong> caratteri disponibili</p>");

	jQuery("input[name='data[SuppliersOrganization][descrizione]']").keyup(function() {
		if(jQuery(this).val().length > battute) {
			$(this).val($(this).val().substr(0, battute));
		}
		$(this).parent().find('p.avviso').html("Hai ancora <strong>"+ (battute - $(this).val().length)+"</strong> caratteri disponibili");
	});
});
</script>