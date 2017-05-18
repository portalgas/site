<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'SuppliersOrganizations', 'action' => 'index'));
$this->Html->addCrumb(__('Add Supplier Organization'),'add');
$this->Html->addCrumb(__('New Supplier Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="suppliers form">
<?php echo $this->Form->create('Supplier',array('id' => 'formGas'));?>
	<fieldset>
		<legend><?php echo __('New Supplier Organization'); ?></legend>
         <div class="tabs">
             <ul>
                 <li><a href="#tabs-0"><span><?php echo __('Dati anagrafici'); ?></span></a></li>
                 <li><a href="#tabs-1"><span><?php echo __('Contatti'); ?></span></a></li>
                 <li><a href="#tabs-2"><span><?php echo __('Altro'); ?></span></a></li>
                 <li><a href="#tabs-3"><span><?php echo __('Referenti'); ?></span></a></li>
                 <li><a href="#tabs-4"><span><?php echo __('ArticlesAndLogo');?></span></a></li>
                 <li><a href="#tabs-5"><span><?php echo __('Permissions'); ?></span></a></li>
             </ul>

             <div id="tabs-0">
					<?php
						if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
							echo $this->Form->input('category_supplier_id',array('options' => $categories,'empty' => 'Filtra per categoria','escape' => false, 'required' => false));
							
						echo $this->Form->input('name', array('label'=>'Ragione sociale', 'required' => false, 'class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('nome');
						echo $this->Form->input('cognome');
						echo $this->Form->input('frequenza', array('after'=>$this->App->drawTooltip(null,__('toolTipSupplierOrganizationFrequenza'),$type='HELP')));
						echo $this->App->drawFormRadio('Supplier','mail_order_open',array('options' => $mail_order_open, 'value' => 'Y', 'label'=>__('MailOrderOpen'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderOpen'),$type='HELP')));

						echo $this->App->drawFormRadio('Supplier','mail_order_close',array('options' => $mail_order_close, 'value' => 'Y', 'label'=>__('MailOrderClose'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderClose'),$type='HELP')));

																											
						echo $this->Form->input('descrizione',  array('label'=>'Descrizione breve', 'type' => 'text', 'after' => '<br /><img width="150" class="print_screen" id="print_screen_supplier_nota" src="'.Configure::read('App.img.cake').'/print_screen_supplier_nota.jpg" title="" border="0" />'));						
					?>
			</div>
            <div id="tabs-1">
					<?php
						echo $this->Form->input('indirizzo', array('required' => false));
						echo $this->Form->input('localita');
						echo $this->Form->input('cap',array('style'=>'width:50px;'));
						echo $this->Form->input('provincia',array('style'=>'width:25px;'));
						echo $this->Form->input('telefono', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('telefono2', array('label' => __('Telephone2'),'class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('fax', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('mail', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('www', array('class' => 'ctrlSupplierDuplicate'));
					?>
			</div>
            <div id="tabs-2">
					<?php
						// echo $this->Form->input('nota');
						echo $this->Form->input('cf', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('piva', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('conto');
					?>
			</div>
            <div id="tabs-3">
					<?php
					foreach ($types as $type => $value)
						echo '<div id="users-'.$type.'" style="min-height:50px"></div>';
					?>
			</div>
            <div id="tabs-4">
					<div class="legenda legenda-ico-info" style="float:none;">
						Dopo il salvataggio si potrà inserire
						<ul>
							<li>Un <b>articolo</b> per completare una scheda informativa del produttore</li>
							<li>Il logo del produttore</li>
						</ul>
						
						<label>
						<img width="150" class="print_screen" id="print_screen_supplier_article" src="<?php echo Configure::read('App.img.cake');?>/print_screen_supplier_article.jpg" title="" border="0" />
						</label>						
					</div>
			</div>
            <div id="tabs-5">
					<?php
						foreach($prod_gas_supplier_owner_articles as $key => $value)
							$prod_gas_supplier_owner_articles[$key] = $this->App->traslateEnum('ProdGasSupplier'.$value);							
						echo $this->App->drawFormRadio('Supplier','prod_gas_supplier_owner_articles',array('options' => $prod_gas_supplier_owner_articles, 'value' => 'REFERENT', 'label'=>__('prod_gas_supplier_owner_articles'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierOwnerArticles'),$type='HELP')));
																										
						echo $this->App->drawFormRadio('Supplier','prod_gas_supplier_can_view_orders',array('options' => $prod_gas_supplier_can_view_orders, 'value' => 'N', 'label'=>__('prod_gas_supplier_can_view_orders'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrders'),$type='HELP')));
																										
						echo $this->App->drawFormRadio('Supplier','prod_gas_supplier_can_view_orders_users',array('options' => $prod_gas_supplier_can_view_orders_users, 'value' => 'N', 'label'=>__('prod_gas_supplier_can_view_orders_users'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrdersUsers'),$type='HELP')));
					?>
			</div>			
		</div>

	</fieldset>
<?php 
echo $this->Form->hidden('sort',array('value' => $sort));
echo $this->Form->hidden('direction',array('value' => $direction));
echo $this->Form->hidden('page',array('value' => $page));

echo $this->Form->end(__('Submit'));

echo $this->element('print_screen_supplier');
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Suppliers Organization'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<div id="dialog" title="Il produttore potrebbe già esistere">
  <p id="dialogText"></p>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});	

	var battute = 75;
	
	jQuery("input[name='data[Supplier][descrizione]']").after("<p style='float:right' class='avviso'>Hai ancora <strong>"+ (battute - jQuery("input[name='data[Supplier][descrizione]']").val().length)+"</strong> caratteri disponibili</p>");

	jQuery("input[name='data[Supplier][descrizione]']").keyup(function() {
		if(jQuery(this).val().length > battute) {
			$(this).val($(this).val().substr(0, battute));
		}
		$(this).parent().find('p.avviso').html("Hai ancora <strong>"+ (battute - $(this).val().length)+"</strong> caratteri disponibili");
	});
	
	jQuery('#SupplierProvincia').change(function() {
		jQuery('#SupplierProvincia').val(jQuery('#SupplierProvincia').val().toUpperCase());
	});

    jQuery("#dialog").dialog({
		autoOpen: false,	
		resizable: true,
		height: "auto",
		width: 500,
		modal: true,
		show: {
		effect: "blind",
		duration: 300
		},	  
		buttons: {
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
    });
  
	jQuery('.ctrlSupplierDuplicate').blur(function() {
		
		jQuery('#dialog').dialog("close");
		
		var value = jQuery(this).val(); 
		var field = jQuery(this).attr('name'); 
		field = field.replace("data[Supplier][","").replace("]","")
		var url = "/administrator/index.php?option=com_cake&controller=Ajax&action=ctrl_supplier_duplicate&field="+field+"&value="+value+"&format=notmpl";
		
		jQuery.ajax({
			type: "get", 
			url: url,
			data: "", 
			success: function(response) {
				/* console.log(response); */
				if(response!='') {
					jQuery('#dialogText').html(response);
					jQuery('#dialog').dialog("open");					
				}	
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				console.log(textStatus);
			}
		});	
	});
	
	jQuery('#formGas').submit(function() {

		<?php
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')  {
		?>
			var SupplierCategorySupplierId = jQuery('#SupplierCategorySupplierId').val();
			if(SupplierCategorySupplierId=='' || SupplierCategorySupplierId==undefined) {
				alert("Devi scegliere la categoria da associare al produttore");
				jQuery('.tabs').tabs('option', 'active',0);
				jQuery('#SupplierCategorySupplierId').focus();
				return false;
			}
		<?php 
		}
		?>			

		var SupplierName = jQuery('#SupplierName').val();
		if(SupplierName=='' || SupplierName==undefined) {
			alert("Indica la ragione sociale del produttore");
			jQuery('.tabs').tabs('option', 'active',0);
			jQuery('#SupplierName').focus();
			return false;
		}	    

		var SupplierMail = jQuery('#SupplierMail').val();
		if(SupplierMail!='') {
			if(!validateEmail(SupplierMail)) {
				alert("<?php echo __('jsAlertMailInvalid');?>");
				jQuery('.tabs').tabs('option', 'active',1);
				jQuery('#SupplierName').focus();
				return false;
			}	
		}

		<?php
		foreach ($types as $type => $value) {
		?>
			var referent_user_ids_<?php echo $type;?> = '';
			jQuery("#referent_user_id-<?php echo $type;?> option" ).each(function (){	
				referent_user_ids_<?php echo $type;?> +=  jQuery(this).val()+',';
			});
			referent_user_ids_<?php echo $type;?> = referent_user_ids_<?php echo $type;?>.substring(0,referent_user_ids_<?php echo $type;?>.length-1);
		
		<?php
		}
		?>

		if(
			<?php
			$tmp = '';
			foreach ($types as $type => $value) 
				$tmp .= "referent_user_ids_".$type."=='' && ";
			
			$tmp = substr($tmp, 0, strlen($tmp)-3);
			
			echo $tmp;
			?>
		) {
			if(!confirm("Sei sicuro di NON voler associare un referente al produttore?"))
			return false;
		}

		<?php
		foreach ($types as $type => $value) {
			echo "jQuery('#referent_user_ids-".$type."').val();";
			echo "\r\n";
			echo "jQuery('#referent_user_ids-".$type."').val(referent_user_ids_".$type.");";
			echo "\r\n";
		}
		?>

		return true;	
	});
});

<?php
foreach ($types as $type => $value) {
	echo "AjaxCallToReferents(0, '".$type."');";
	echo "\r\n";
}
?>

function AjaxCallToReferents(supplier_organization_id, type) {	

	var group_id = '<?php echo Configure::read('group_id_referent');?>';
	var url = "/administrator/index.php?option=com_cake&controller=SuppliersOrganizationsReferents&action=ajax_box_users&supplier_organization_id="+supplier_organization_id+"&group_id="+group_id+"&type="+type+"&format=notmpl";
	var urlAjax = 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent'; 
	
	jQuery('#users-'+type).show();
	jQuery('#users-'+type).html('');
	jQuery('#users-'+type).css('background', urlAjax);	
	
	jQuery.ajax({
		type: "get", 
		url: url,
		data: "", 
		success: function(response) {
			jQuery('#users-'+type).css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#users-'+type).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			jQuery('#users-'+type).css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#users-'+type).html(textStatus);
		}
	});	
}	
</script>