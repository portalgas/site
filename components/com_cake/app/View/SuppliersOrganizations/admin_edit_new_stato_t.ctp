<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'SuppliersOrganizations', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Supplier Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="suppliers form">';
echo $this->Form->create('SuppliersOrganization',array('id' => 'formGas'));
echo '	<fieldset>';
echo '		<legend>'.__('Edit Supplier Organization').'</legend>';
echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('User profile').'</a></li>';	
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Contacts').'</a></li>';	
echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Others').'</a></li>';	
echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Img').'</a></li>';	
echo '<li><a href="#tabs-4" data-toggle="tab">'.__('SupplierForm').'</a></li>';	
echo '<li><a href="#tabs-5" data-toggle="tab">'.__('Permissions').'</a></li>';		 
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0">';

                            if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
                                    echo $this->Form->input('category_supplier_id',array('options' => $categories, 'default' => $results['SuppliersOrganization']['category_supplier_id'], 'empty' => 'Filtra per categoria','escape' => false, 'required' => false));

                            echo $this->Form->input('name',array('value' => $results['Supplier']['name'], 'label'=>'Ragione sociale', 'required' => false));
                            echo $this->Form->input('nome', array('value' => $results['Supplier']['nome']));
                            echo $this->Form->input('cognome', array('value' => $results['Supplier']['cognome']));
                            echo $this->Form->input('frequenza', array('value' => $results['SuppliersOrganization']['frequenza'], 'after'=>$this->App->drawTooltip(null,__('toolTipSupplierOrganizationFrequenza'),$type='HELP')));
							
                            echo $this->Form->input('descrizione',  array('value' => $results['Supplier']['descrizione'], 'label'=>'Descrizione breve', 'type' => 'text', 'after' => '<br /><img width="150" class="print_screen" id="print_screen_supplier_nota" src="'.Configure::read('App.img.cake').'/print_screen_supplier_nota.jpg" title="" border="0" />'));
 echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1">';
                                    echo $this->Form->input('indirizzo', array('required' => false, 'value' => $results['Supplier']['indirizzo']));
                                    echo $this->Form->input('localita', array('value' => $results['Supplier']['localita']));
									
									echo '<div class="row">';
									echo '<div class="col-md-4">';
									echo $this->Form->input('cap',array('value' => $results['Supplier']['cap']));
									echo '</div>';
									echo '<div class="col-md-2">';
									echo $this->Form->input('provincia',array('value' => $results['Supplier']['provincia']));
									echo '</div>';
									echo '</div>';
						
                                    echo $this->Form->input('telefono', array('value' => $results['Supplier']['telefono']));
                                    echo $this->Form->input('telefono2', array('value' => $results['Supplier']['telefono2']));
                                    echo $this->Form->input('fax', array('value' => $results['Supplier']['fax']));
                                    echo $this->Form->input('mail', array('value' => $results['Supplier']['mail']));
                                    echo $this->Form->input('www', array('value' => $results['Supplier']['www']));
 echo '</div>';
echo '<div class="tab-pane fade" id="tabs-2">';
                                    //echo $this->Form->input('nota', array('value' => $results['Supplier']['nota']));
                                    echo $this->Form->input('cf', array('value' => $results['Supplier']['cf']));
                                    echo $this->Form->input('piva', array('value' => $results['Supplier']['piva']));
                                    echo $this->Form->input('conto', array('value' => $results['Supplier']['conto']));
                                    echo $this->Form->input('delivery_type_id', array('value' => $results['Supplier']['delivery_type_id'], 'options' => $suppliersDeliveriesType, 'default' => 1, 'label' => __('SuppliersDeliveriesTypes'), 'escape' => false));
 echo '</div>';
echo '<div class="tab-pane fade" id="tabs-3">';
                        if(!empty($results['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['img1'])) {
                                echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['img1'].'" /> <br />';

                                if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['j_content_id'].'a.jpg')) 
                                        echo '<p><img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['j_content_id'].'a.jpg" /></p>';

                                if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['j_content_id'].'b.jpg')) 
                                        echo '<p><img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['j_content_id'].'b.jpg" /></p>';

                                if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['j_content_id'].'c.jpg')) 
                                        echo '<p><img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['j_content_id'].'c.jpg" /></p>';

                                if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['j_content_id'].'d.jpg')) 
                                        echo '<p><img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['j_content_id'].'d.jpg" /></p>';

                        }
                        else
                                echo 'Immagine non presente';
 echo '</div>';
echo '<div class="tab-pane fade" id="tabs-4">';
if($results['SuppliersOrganization']['owner_articles']=='REFERENT') 
	echo $this->Html->link(__('List Articles').' ('.$totArticlesAttivi.')', array('controller' => 'Articles', 'action' => 'context_articles_index', null,'FilterArticleSupplierId='.$results['SuppliersOrganization']['id']),array());		
else
if($results['SuppliersOrganization']['owner_articles']=='SUPPLIER') 
	echo $this->element('boxMsg',array('class_msg' => 'notice resultsNotFound', 'msg' => "Gli articoli sono gestiti dal produttore"));
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-5">';
            echo $this->App->drawFormRadio('SuppliersOrganization','mail_order_open',array('options' => $mail_order_open, 'value' => $results['SuppliersOrganization']['mail_order_open'], 'label'=>__('MailOrderOpen'), 'required'=>'required',
                                                                                            'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderOpen'),$type='HELP')));

			echo $this->App->drawFormRadio('SuppliersOrganization','mail_order_close',array('options' => $mail_order_close, 'value' => $results['SuppliersOrganization']['mail_order_close'], 'label'=>__('MailOrderClose'), 'required'=>'required',
                                                                                                'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderClose'),$type='HELP')));
			foreach($prod_gas_supplier_owner_articles as $key => $value)
				$prod_gas_supplier_owner_articles[$key] = $this->App->traslateEnum('ProdGasSupplier'.$value);
			echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_owner_articles',array('options' => $prod_gas_supplier_owner_articles, 'value' => $results['SuppliersOrganization']['owner_articles'], 'label'=>__('prod_gas_supplier_owner_articles'), 'disabled' => 'disabled',
																							'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierOwnerArticles'),$type='HELP')));
																							
			echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_can_view_orders',array('options' => $prod_gas_supplier_can_view_orders, 'value' => $results['SuppliersOrganization']['can_view_orders'], 'label'=>__('prod_gas_supplier_can_view_orders'), 'required'=>'required',
																							'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrders'),$type='HELP')));
																							
			echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_can_view_orders_users',array('options' => $prod_gas_supplier_can_view_orders_users, 'value' => $results['SuppliersOrganization']['can_view_orders_users'], 'label'=>__('prod_gas_supplier_can_view_orders_users'), 'required'=>'required',
																							'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrdersUsers'),$type='HELP')));

			echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_can_promotions',array('options' => $prod_gas_supplier_can_view_orders_users, 'value' => $results['SuppliersOrganization']['can_promotions'], 'label'=>__('prod_gas_supplier_can_promotions'), 'required'=>'required',
																							'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanPromotions'),$type='HELP')));

echo '</div>';
echo '</div>'; // tab-content
echo '</div>';
echo '</fieldset>';
echo $this->Form->input('id', array('value' => $results['SuppliersOrganization']['id']));
echo $this->Form->end(__('Submit'));

echo $this->element('print_screen_supplier');

echo '</div>';
echo '<div class="actions">';
echo '	<h3>'.__('Actions').'</h3>';
echo '	<ul>';
echo '		<li>';
echo $this->Html->link(__('List Suppliers Organization'), array('action' => 'index'),array('class'=>'action actionReload'));
echo '</li>';
if($results['SuppliersOrganization']['owner_articles']=='REFERENT') {
	echo '		<li>';
	echo $this->Html->link(__('List Articles').' ('.$totArticlesAttivi.')', array('controller' => 'Articles', 'action' => 'context_articles_index', null,'FilterArticleSupplierId='.$results['SuppliersOrganization']['id']),array('class'=>'action actionList'));
	echo '</li>';
}	
echo '	</ul>';
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
	
	var battute = 75;
	
	$("input[name='data[SuppliersOrganization][descrizione]']").after("<p style='float:right' class='avviso'>Hai ancora <strong>"+ (battute - $("input[name='data[SuppliersOrganization][descrizione]']").val().length)+"</strong> caratteri disponibili</p>");

	$("input[name='data[SuppliersOrganization][descrizione]']").keyup(function() {
		if($(this).val().length > battute) {
			$(this).val($(this).val().substr(0, battute));
		}
		$(this).parent().find('p.avviso').html("Hai ancora <strong>"+ (battute - $(this).val().length)+"</strong> caratteri disponibili");
	});
});
</script>