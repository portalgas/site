<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'SuppliersOrganizations', 'action' => 'index'));
$this->Html->addCrumb(__('Add Supplier Organization'),'add');
$this->Html->addCrumb(__('New Supplier Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';
echo $this->Form->create('Supplier',array('id' => 'formGas'));
echo '	<fieldset>';
echo '		<legend>'.__('New Supplier Organization').'</legend>';
echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('User profile').'</a></li>';	
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Contacts').'</a></li>';	
echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Others').'</a></li>';	
echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Suppliers Organizations Referents').'</a></li>';	
echo '<li><a href="#tabs-4" data-toggle="tab">'.__('SupplierFormAndLogo').'</a></li>';	
echo '<li><a href="#tabs-5" data-toggle="tab">'.__('Permissions').'</a></li>';		 
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0">';		
						if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
							echo $this->Form->input('category_supplier_id',array('options' => $categories,'empty' => 'Filtra per categoria','escape' => false, 'required' => false));
							
						echo $this->Form->input('name', array('label'=>'Ragione sociale', 'required' => false, 'class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('nome');
						echo $this->Form->input('cognome');
						echo $this->Form->input('frequenza', array('after' => $this->App->drawTooltip(null,__('toolTipSupplierOrganizationFrequenza'), $type='HELP')));
																											
						echo $this->Form->input('descrizione',  array('label'=>'Descrizione breve', 'type' => 'text', 'after' => '<br /><img width="150" class="print_screen" id="print_screen_supplier_nota" src="'.Configure::read('App.img.cake').'/print_screen_supplier_nota.jpg" title="" border="0" />'));						
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1">';
						echo $this->Form->input('indirizzo', array('required' => false));
						echo $this->Form->input('localita');
						
						echo '<div class="row">';
						echo '<div class="col-md-4">';
						echo $this->Form->input('cap');
						echo '</div>';
						echo '<div class="col-md-2">';
						echo $this->Form->input('provincia');
						echo '</div>';
						echo '</div>';
						
						echo $this->Form->input('telefono', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('telefono2', array('label' => __('Telephone2'),'class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('fax', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('mail', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('www', array('class' => 'ctrlSupplierDuplicate'));
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-2">';
						// echo $this->Form->input('nota');
						echo $this->Form->input('cf', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('piva', array('class' => 'ctrlSupplierDuplicate'));
						echo $this->Form->input('conto');
						echo $this->Form->input('delivery_type_id', array('options' => $suppliersDeliveriesType, 'default' => 1, 'label' => __('SuppliersDeliveriesTypes'), 'escape' => false));																							
						
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-3">';
					foreach ($types as $type => $value)
						echo '<div id="users-'.$type.'" style="min-height:50px"></div>';
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-4">';
			?>
					<div class="legenda legenda-ico-info" style="float:none;">
						Dopo il salvataggio si potrà inserire
						<ul>
							<li>- Una <b>scheda informativa</b> per descrivere l'attività del produttore</li>
							<li>- Il logo del produttore</li>
						</ul>
						
						<label>
						<img width="150" class="print_screen" id="print_screen_supplier_article" src="<?php echo Configure::read('App.img.cake');?>/print_screen_supplier_article.jpg" title="" border="0" />
						</label>						
					</div>
					<?php
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-5">';				
						echo $this->App->drawFormRadio('Supplier','mail_order_open',array('options' => $mail_order_open, 'value' => 'Y', 'label'=>__('MailOrderOpen'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderOpen'),$type='HELP')));


						echo $this->App->drawFormRadio('Supplier','mail_order_close',array('options' => $mail_order_close, 'value' => 'Y', 'label'=>__('MailOrderClose'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipSupplierMailOrderClose'),$type='HELP')));

						foreach($prod_gas_supplier_owner_articles as $key => $value)
							$prod_gas_supplier_owner_articles[$key] = $this->App->traslateEnum('ProdGasSupplier'.$value);							
						echo $this->App->drawFormRadio('Supplier','prod_gas_supplier_owner_articles',array('options' => $prod_gas_supplier_owner_articles, 'value' => 'REFERENT', 'label'=>__('prod_gas_supplier_owner_articles'), 'disabled' => 'disabled',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierOwnerArticles'),$type='HELP')));
																										
						echo $this->App->drawFormRadio('Supplier','prod_gas_supplier_can_view_orders',array('options' => $prod_gas_supplier_can_view_orders, 'value' => 'N', 'label'=>__('prod_gas_supplier_can_view_orders'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrders'),$type='HELP')));
																										
						echo $this->App->drawFormRadio('Supplier','prod_gas_supplier_can_view_orders_users',array('options' => $prod_gas_supplier_can_view_orders_users, 'value' => 'N', 'label'=>__('prod_gas_supplier_can_view_orders_users'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanViewOrdersUsers'),$type='HELP')));

						echo $this->App->drawFormRadio('Supplier','prod_gas_supplier_can_promotions',array('options' => $prod_gas_supplier_can_promotions, 'value' => 'N', 'label'=>__('prod_gas_supplier_can_promotions'), 'required'=>'required',
																										'after'=>$this->App->drawTooltip(null,__('toolTipProdGasSupplierCanPromotions'),$type='HELP')));

			echo '</div>';
			echo '</div>'; // tab-content
			echo '</div>';
echo $this->Form->hidden('sort',array('value' => $sort));
echo $this->Form->hidden('direction',array('value' => $direction));
echo $this->Form->hidden('page',array('value' => $page));


echo '</fieldset>';
echo $this->Form->end(__('Submit'));
echo '</div>';

$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Suppliers Organization').' </span><span class="fa fa-reply"></span>', array('controller' => 'SuppliersOrganizations', 'action' => 'index'), ['class' => 'animate', 'escape' => false]);
echo $this->Menu->draw($links);

echo $this->element('print_screen_supplier');
?>

<!-- modal -->
<div class="modal fade" id="dialog-ctrl-supplier" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Il produttore potrebbe già esistere</h4>
			</div>
			<div class="modal-body">		
			</div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
		  </div>
		</div>
	</div>		
</div>	

<script type="text/javascript">
$(document).ready(function() {
	var battute = 75;
	
	$("input[name='data[Supplier][descrizione]']").after("<p style='float:right' class='avviso'>Hai ancora <strong>"+ (battute - $("input[name='data[Supplier][descrizione]']").val().length)+"</strong> caratteri disponibili</p>");

	$("input[name='data[Supplier][descrizione]']").keyup(function() {
		if($(this).val().length > battute) {
			$(this).val($(this).val().substr(0, battute));
		}
		$(this).parent().find('p.avviso').html("Hai ancora <strong>"+ (battute - $(this).val().length)+"</strong> caratteri disponibili");
	});
	
	$('#SupplierProvincia').change(function() {
		$('#SupplierProvincia').val($('#SupplierProvincia').val().toUpperCase());
	});
  
	$('.ctrlSupplierDuplicate').blur(function() {
		
		$('#dialog-ctrl-supplier').modal("hide");
		
		var value = $(this).val(); 
		var field = $(this).attr('name'); 
		field = field.replace("data[Supplier][","").replace("]","")
		var url = "/administrator/index.php?option=com_cake&controller=Ajax&action=ctrl_supplier_duplicate&field="+field+"&value="+value+"&format=notmpl";
		
		$.ajax({
			type: "get", 
			url: url,
			data: "", 
			success: function(response) {
				/* console.log(response); */
				if(response!='') {
					$('#dialog-ctrl-supplier .modal-body').html(response);
					$('#dialog-ctrl-supplier').modal("show");					
				}	
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				console.log(textStatus);
			}
		});	
	});
	
	$('#formGas').submit(function() {

		<?php
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')  {
		?>
			var SupplierCategorySupplierId = $('#SupplierCategorySupplierId').val();
			if(SupplierCategorySupplierId=='' || SupplierCategorySupplierId==undefined) {
				alert("Devi scegliere la categoria da associare al produttore");
				$('.tabs li:eq(0) a').tab('show');
				$('#SupplierCategorySupplierId').focus();
				return false;
			}
		<?php 
		}
		?>			

		var SupplierName = $('#SupplierName').val();
		if(SupplierName=='' || SupplierName==undefined) {
			alert("Indica la ragione sociale del produttore");
			$('.tabs li:eq(0) a').tab('show');
			$('#SupplierName').focus();
			return false;
		}	    

		var SupplierMail = $('#SupplierMail').val();
		if(SupplierMail!='') {
			if(!validateEmail(SupplierMail)) {
				alert("<?php echo __('jsAlertMailInvalid');?>");
				$('.tabs li:eq(1) a').tab('show');
				$('#SupplierName').focus();
				return false;
			}	
		}

		<?php
		foreach ($types as $type => $value) {
		?>
			var referent_user_ids_<?php echo $type;?> = '';
			$("#referent_user_id-<?php echo $type;?> option" ).each(function (){	
				referent_user_ids_<?php echo $type;?> +=  $(this).val()+',';
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
			echo "$('#referent_user_ids-".$type."').val();";
			echo "\r\n";
			echo "$('#referent_user_ids-".$type."').val(referent_user_ids_".$type.");";
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
	
	$('#users-'+type).show();
	$('#users-'+type).html('');
	$('#users-'+type).css('background', urlAjax);	
	
	$.ajax({
		type: "get", 
		url: url,
		data: "", 
		success: function(response) {
			$('#users-'+type).css('background', 'none repeat scroll 0 0 transparent');
			$('#users-'+type).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$('#users-'+type).css('background', 'none repeat scroll 0 0 transparent');
			$('#users-'+type).html(textStatus);
		}
	});	
}	
</script>