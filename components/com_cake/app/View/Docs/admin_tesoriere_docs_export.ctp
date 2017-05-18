<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
if(!isset($delivery_id)) $delivery_id = 0; 
$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('Export Docs to delivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	var delivery_id = jQuery('#delivery_id').val();
	if(delivery_id!="" && delivery_id!=undefined) caricaOrdini();
	
	jQuery('#delivery_id').change(function() {
		caricaOrdini();
	});
	
	jQuery("input[name='doc_options']").change(function() {
		var doc_options = jQuery("input[name='doc_options']:checked").val();
		var order_id_selected = '';
		for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
		}
		if(order_id_selected!='') {
			order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);

			jQuery('#articles-result').html('');
			
			tesoriereDocsExportAnteprima(order_id_selected,doc_options); 
		}	
		else {
			alert("Seleziona almeno un ordine");
			jQuery("input[name='doc_options']").prop('checked',false);
		}	
	});

	jQuery('#actionExportDoc').click(function() {
		var delivery_id = jQuery('#delivery_id').val();
		var order_id_selected = '';
		for(i = 0; i < jQuery("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += jQuery("input[name='order_id_selected']:checked").eq(i).val()+',';
		}
		var doc_options = jQuery("input[name='doc_options']:checked").val();
		var doc_formato = jQuery("input[name='doc_formato']:checked").val();

		if(delivery_id=='') {
			order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
		if(order_id_selected=='') {
			alert("<?php echo __('jsAlertOrderAtLeastRequired');?>");
			return false;
		}
		if(doc_options==null) {
			alert("<?php echo __('jsAlertPrintFormatRequired');?>");
			return false;
		}
		if(doc_formato==null) {
			alert("<?php echo __('jsAlertPrintTypeRequired');?>");
			return false;
		}

		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
		jQuery('#actionExportDoc').attr('href','/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToTesoriere&delivery_id='+delivery_id+'&order_id_selected='+order_id_selected+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl');
		return true;
	});
});

function caricaOrdini() {
	
	var delivery_id = jQuery('#delivery_id').val();

	if(delivery_id=="") {
		jQuery('#orders-result').css('display', 'none');	
		jQuery('#type-render-data').css('display', 'none');	
		jQuery('#print-doc').css('display', 'none');	
		jQuery('#articles-results').css('display', 'none');	
		return;
	}

	jQuery('#orders-result').html('');
	jQuery('#type-render-data').css('display', 'none');	
	jQuery('#print-doc').css('display', 'none');	
	jQuery('#articles-result').css('display', 'none');	
	
	jQuery('#orders-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
	jQuery('#orders-result').css('display', 'block');	
	
	jQuery.ajax({
		type: "get",
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_index&delivery_id="+delivery_id+"&order_state_code_checked=<?php echo $order_state_code_checked;?>&format=notmpl",
		data: "", 
		success: function(response) {
			jQuery('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#orders-result').html(response);

			jQuery('#type-render-data').css('display', 'block');	
			jQuery("input[name='doc_options']").removeAttr("checked");
			jQuery('#print-doc').css('display', 'block');
			jQuery('#articles-results').css('display', 'block');	
					
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			jQuery('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#orders-result').html(textStatus);
		}
	});	
}
</script>

<h2 class="ico-export-docs">
	<?php echo __('Export Docs to delivery');?>
</h2>


<div class="docs">
<?php echo $this->Form->create();?>
	<fieldset>
	
	<?php
	$options = array('id'=>'delivery_id');
	if(!empty($delivery_id) && $delivery_id>0)
		$options += array('default' => $delivery_id);
	else
		$options += array('empty' => Configure::read('option.empty'));
	
	echo $this->Form->input('delivery_id',$options);
	?>	
	
	<div id="orders-result" style="display:none;min-height:50px;"></div>
	
	<div id="type-render-data" style="display:none;">
		<div class="left label">Opzioni formato</div>
		<div class="left radio">
			<p>
				<input type="radio" name="doc_options" id="to-users" value="to-users" /><label for="to-users"><?php echo __('to_users');?></label>
			</p>
		
			<p>
				<input type="radio" name="doc_options" id="to-supplier" value="to-supplier" /><label for="to-supplier"><?php echo __('to_supplier');?></label>
			</p>
		</div>
	</div>

	<div id="print-doc" style="display:none;">
	
		<h2 class="ico-export-docs">
			<?php echo __('Print Doc');?>
			<div class="actions-img">
				<ul>
					<li><?php echo $this->Form->input('typeDoc', array(
										 'id' => 'typeDoc',
								         'type' => 'radio',
								         'name' => 'doc_formato',
								         'fieldset' => false,
								         'legend' => false,
								         'div' => array('class' => ''),
								         'options' => array('PDF'=>'Pdf','CSV'=>'Csv','EXCEL'=>'Excel'),
								         'default' => 'PDF',
								   ));
						?>
					</li>
					<li><?php echo $this->Html->link(__('Print Doc'), '' ,array('id' => 'actionExportDoc', 'class' => 'action actionPrinter blank', 'title' => __('Print Doc'))); ?></li>
				</ul>
			</div>
		</h2>	
	<div/>

	<div id="articles-result" style="display:none;min-height:50px;"></div>
	
	</fieldset>
</div>