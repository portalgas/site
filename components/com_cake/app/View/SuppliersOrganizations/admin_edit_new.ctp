<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'SuppliersOrganizations', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Supplier Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';
echo $this->Form->create('SuppliersOrganization',array('id' => 'formGas'));
echo '	<fieldset>';
echo '		<legend>'.__('Edit Supplier Organization').': '.$results['Supplier']['name'].'</legend>';
echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('Dati modificabili').'</a></li>';
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('User profile').'</a></li>';	
echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Contacts').'</a></li>';	
echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Others').'</a></li>';	
echo '<li><a href="#tabs-4" data-toggle="tab">'.__('Img').'</a></li>';	
if(isset($j_content_text) && $j_content_text!==false)	 
	echo '<li><a href="#tabs-5" data-toggle="tab">'.__('SupplierForm').'</a></li>';		 
echo '<li><a href="#tabs-6" data-toggle="tab">'.__('List Articles').'</a></li>';
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0">';

		/*
		 * dati owner_articles listino REFERENT / DES / SUPPLIER 
		 */	
		if(!empty($desSupplierResults)) {
			echo '<div class="input text ">';
			if($isGasTitolare) {
				echo '<div class="input text"><label>'.__('Des Supplier').'</label> '.__('OwnOrganizationId');
				echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$user->organization['Organization']['img1'].'" alt="'.$user->organization['Organization']['name'].'" /> ';	
				echo $user->organization['Organization']['name']; 				
				echo '</div>';
			}
			else {
				echo '<div class="input text"><label>'.__('Des Supplier').'</label> '.__('OwnOrganizationId');
				echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$organizationResults['Organization']['img1'].'" alt="'.$organizationResults['Organization']['name'].'" /> ';	
				echo $organizationResults['Organization']['name']; 				
				echo '</div>';
			}
			echo '</div>';
		}
		
        echo $this->Form->input('frequenza', array('value' => $results['SuppliersOrganization']['frequenza'], 'before'=>$this->App->drawTooltip(null,__('toolTipSupplierOrganizationFrequenza'),$type='HELP')));

        echo '<div class="clearfix"></div>';
        echo $this->App->drawFormRadio('SuppliersOrganization','mail_order_open',array('options' => $mail_order_open, 'value' => $results['SuppliersOrganization']['mail_order_open'], 'label'=>__('MailOrderOpen'), 'required'=>'required',
                                                                                        'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderOpen'),$type='HELP')));

        echo '<div class="clearfix"></div>';
        echo $this->App->drawFormRadio('SuppliersOrganization','mail_order_close',array('options' => $mail_order_close, 'value' => $results['SuppliersOrganization']['mail_order_close'], 'label'=>__('MailOrderClose'), 'required'=>'required',
                                                                                        'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderClose'),$type='HELP')));

        echo '<div class="clearfix"></div>';
        echo $this->App->drawFormRadio('SuppliersOrganization','stato',array('options' => $stato, 'value' => $results['SuppliersOrganization']['stato'], 'label'=>__('Stato'), 'required'=>'required',
                                                                                                            'after'=>$this->App->drawTooltip(null,__('toolTipStato'),$type='HELP')));

		echo $this->App->drawFormRadioOwnerArticles($user, 'SuppliersOrganization', 'prod_gas_supplier_owner_articles', ['options' => $prod_gas_supplier_owner_articles, 'value' => $results['SuppliersOrganization']['owner_articles'], 'label'=>__('prod_gas_supplier_owner_articles')]);
																						
		echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_can_view_orders',array('options' => $prod_gas_supplier_can_view_orders, 'value' => $results['SuppliersOrganization']['can_view_orders'], 'label'=>__('prod_gas_supplier_can_view_orders'), 'required'=>'required',
																						'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrders'),$type='HELP')));
																						
		echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_can_view_orders_users',array('options' => $prod_gas_supplier_can_view_orders_users, 'value' => $results['SuppliersOrganization']['can_view_orders_users'], 'label'=>__('prod_gas_supplier_can_view_orders_users'), 'required'=>'required',
																						'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrdersUsers'),$type='HELP')));

		echo $this->App->drawFormRadio('SuppliersOrganization','prod_gas_supplier_can_promotions',array('options' => $prod_gas_supplier_can_promotions, 'value' => $results['SuppliersOrganization']['can_promotions'], 'label'=>__('prod_gas_supplier_can_promotions'), 'required'=>'required',
																						'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanPromotions'),$type='HELP')));

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1">';
				if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') echo '<div class="input text"><label for="">Categoria</label> '.$results['CategoriesSupplier']['name'].'</div>';
				?>
                <div class="input text"><label for="">Ragione sociale</label> <?php echo $results['Supplier']['name'];?></div>
                <div class="input text"><label for="">Nome</label> <?php echo $results['Supplier']['nome'];?></div>
                <div class="input text"><label for="">Cognome</label> <?php echo $results['Supplier']['cognome'];?></div>
                <div class="input text"><label for="">Descrizione</label> <?php echo $results['Supplier']['descrizione'];?></div>
<?php
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-2">';
?>
                <div class="input text"><label for="">Indirizzo</label> <?php echo $results['Supplier']['indirizzo'];?></div>
                <div class="input text"><label for="">Localit&agrave;</label> <?php echo $results['Supplier']['localita'];?></div>
                <div class="input text"><label for="">Cap</label> <?php echo $results['Supplier']['cap'];?></div>
                <div class="input text"><label for="">Provincia</label> <?php echo $results['Supplier']['provincia'];?></div>
                <div class="input text"><label for="">Telefono</label> <?php echo $results['Supplier']['telefono'];?></div>
                <div class="input text"><label for=""><?php echo __('Telefono2');?></label> <?php echo $results['Supplier']['telefono2'];?></div>
                <div class="input text"><label for="">Fax</label> <?php echo $results['Supplier']['fax'];?></div>
					<?php
					echo '<div class="input text"><label for="">'.__('Mail').'</label> ';
					if(!empty($results['Supplier']['mail'])) 
						echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$results['Supplier']['mail'].'">'.$results['Supplier']['mail'].'</a>';
					echo '</div>';
					
					echo '<div class="input text"><label for="">'.__('Www').'</label> ';
					if(!empty($results['Supplier']['www'])) 
						echo '<a title="'.$results['Supplier']['www'].'" target="_blank" href="'.$this->App->traslateWww($results['Supplier']['www']).'">'.$results['Supplier']['www'].'</a>';
					echo '</div>';
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-3">';
?>
                <div class="input text"><label for=""><?php echo __('Cf');?></label> <?php echo $results['Supplier']['cf'];?></div>
                <div class="input text"><label for=""><?php echo __('Piva');?></label> <?php echo $results['Supplier']['piva'];?></div>
                <div class="input text"><label for=""><?php echo __('Conto');?></label> <?php echo $results['Supplier']['conto'];?></div>
                <div class="input text"><label for=""><?php echo __('SuppliersDeliveriesTypes');?></label> <?php echo $results['SuppliersDeliveriesType']['name'];?></div>
<?php
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-4">';
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
if(isset($j_content_text) && $j_content_text!==false) {
	echo '<div class="tab-pane fade" id="tabs-5">';
	echo $j_content_text->introtext;
	echo '</div>';		
}
echo '<div class="tab-pane fade" id="tabs-6">';
            echo $this->Html->link(__('List Articles').' ('.$totArticlesAttivi.')', array('controller' => 'Articles', 'action' => 'context_articles_index', null,'FilterArticleSupplierId='.$results['SuppliersOrganization']['id']),array());
echo '</div>';	
		

echo $this->element('legendaSuppliersOrganizationsStatoYEdit', array('organization_id' => $user->organization['Organization']['id'], 'supplier_id' => $results['SuppliersOrganization']['supplier_id']));


echo '</div>'; // tab-content
echo '</div>'; // tabs 

			
echo $this->Form->input('id', array('value' => $results['SuppliersOrganization']['id']));
if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
	echo $this->Form->input('category_supplier_id', array('type' => 'hidden','value' => $results['SuppliersOrganization']['category_supplier_id']));

echo '</fieldset>';
echo $this->Form->end(__('Submit'));
echo '</div>'; // contentMenuLaterale

$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Suppliers Organization').' </span><span class="fa fa-reply"></span>', array('controller' => 'SuppliersOrganizations', 'action' => 'index'), ['class' => 'animate', 'escape' => false]);
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Articles').' ('.$totArticlesAttivi.') </span><span class="fa fa-cubes"></span>', array('controller' => 'Articles', 'action' => 'context_articles_index', null,'FilterArticleSupplierId='.$results['SuppliersOrganization']['id']), ['class' => 'animate', 'escape' => false]);
echo $this->Menu->draw($links);

echo $this->element('send_mail_popup');
?>