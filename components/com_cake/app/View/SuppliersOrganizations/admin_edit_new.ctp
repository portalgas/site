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
                <li><a href="#tabs-0"><span><?php echo __('Dati modificabili'); ?></span></a></li>
				<li><a href="#tabs-6"><span><?php echo __('Permissions'); ?></span></a></li>
                <li><a href="#tabs-1"><span><?php echo __('Dati anagrafici'); ?></span></a></li>
                <li><a href="#tabs-2"><span><?php echo __('Contatti'); ?></span></a></li>
                <li><a href="#tabs-3"><span><?php echo __('Altro'); ?></span></a></li>
                <li><a href="#tabs-4"><span><?php echo __('Immagine'); ?></span></a></li>
                <li><a href="#tabs-5"><span><?php echo __('Articles'); ?></span></a></li>
            </ul>

            <div id="tabs-0">
                        <?php
                            echo $this->Form->input('frequenza', array('value' => $results['SuppliersOrganization']['frequenza'], 'before'=>$this->App->drawTooltip(null,__('toolTipSupplierOrganizationFrequenza'),$type='HELP')));

                            echo $this->App->drawFormRadio('SuppliersOrganization','mail_order_open',array('options' => $mail_order_open, 'value' => $results['SuppliersOrganization']['mail_order_open'], 'label'=>__('MailOrderOpen'), 'required'=>'required',
                                                                                                            'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderOpen'),$type='HELP')));

                            echo $this->App->drawFormRadio('SuppliersOrganization','mail_order_close',array('options' => $mail_order_close, 'value' => $results['SuppliersOrganization']['mail_order_close'], 'label'=>__('MailOrderClose'), 'required'=>'required',
                                                                                                            'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderClose'),$type='HELP')));

                            echo $this->App->drawFormRadio('SuppliersOrganization','stato',array('options' => $stato, 'value' => $results['SuppliersOrganization']['stato'], 'label'=>__('Stato'), 'required'=>'required',
                                                                                                            'after'=>$this->App->drawTooltip(null,__('toolTipStato'),$type='HELP')));
                        ?>
            </div>

            <div id="tabs-6">
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
			
            <div id="tabs-1">
				<?php if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') echo '<div class="input text"><label for="">Categoria</label>'.$results['CategoriesSupplier']['name'].'</div>';?>
                <div class="input text"><label for="">Ragione sociale</label><?php echo $results['Supplier']['name'];?></div>
                <div class="input text"><label for="">Nome</label><?php echo $results['Supplier']['nome'];?></div>
                <div class="input text"><label for="">Cognome</label><?php echo $results['Supplier']['cognome'];?></div>
                <div class="input text"><label for="">Descrizione</label><?php echo $results['Supplier']['descrizione'];?></div>

            </div>
            <div id="tabs-2">

                <div class="input text"><label for="">Indirizzo</label><?php echo $results['Supplier']['indirizzo'];?></div>
                <div class="input text"><label for="">Localit&agrave;</label><?php echo $results['Supplier']['localita'];?></div>
                <div class="input text"><label for="">Cap</label><?php echo $results['Supplier']['cap'];?></div>
                <div class="input text"><label for="">Provincia</label><?php echo $results['Supplier']['provincia'];?></div>
                <div class="input text"><label for="">Telefono</label><?php echo $results['Supplier']['telefono'];?></div>
                <div class="input text"><label for=""><?php echo __('Telefono2');?></label><?php echo $results['Supplier']['telefono2'];?></div>
                <div class="input text"><label for="">Fax</label><?php echo $results['Supplier']['fax'];?></div>
					<?php
					echo '<div class="input text"><label for="">'.__('Mail').'</label>';
					if(!empty($results['Supplier']['mail'])) 
						echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$results['Supplier']['mail'].'">'.$results['Supplier']['mail'].'</a>';
					echo '</div>';
					
					echo '<div class="input text"><label for="">'.__('Www').'</label>';
					if(!empty($results['Supplier']['www'])) 
						echo '<a title="'.$results['Supplier']['www'].'" target="_blank" href="'.$this->App->traslateWww($results['Supplier']['www']).'">'.$results['Supplier']['www'].'</a>';
					echo '</div>';
					?>
            </div>
            <div id="tabs-3">

                <div class="input text"><label for=""><?php echo __('Cf');?></label><?php echo $results['Supplier']['cf'];?></div>
                <div class="input text"><label for=""><?php echo __('Piva');?></label><?php echo $results['Supplier']['piva'];?></div>
                <div class="input text"><label for=""><?php echo __('Conto');?></label><?php echo $results['Supplier']['conto'];?></div>

            </div>
            <div id="tabs-4">
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
       <?php 
            echo '<div id="tabs-5">';
            echo $this->Html->link(__('List Articles').' ('.$totArticlesAttivi.')', array('controller' => 'Articles', 'action' => 'context_articles_index', null,'FilterArticleSupplierId='.$results['SuppliersOrganization']['id']),array());
            echo '</div>';				

echo $this->element('legendaSuppliersOrganizationsStatoYEdit', array('organization_id' => $user->organization['Organization']['id'], 'supplier_id' => $results['SuppliersOrganization']['supplier_id']));
echo '</div>';
echo '</fieldset>';

echo $this->Form->input('id', array('value' => $results['SuppliersOrganization']['id']));
if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
	echo $this->Form->input('category_supplier_id', array('type' => 'hidden','value' => $results['SuppliersOrganization']['category_supplier_id']));
echo $this->Form->end(__('Submit'));

echo $this->element('send_mail_popup');
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
    jQuery(document).ready(function () {
        jQuery(function () {
            jQuery(".tabs").tabs({
                event: "click"
            });
        });
    });
</script>