<h2 class="ico-export-docs">
	<?php echo __('Print Doc');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Form->input('typeDoc', array(
						         'type' => 'radio',
						         'name' => 'doc_formato',
						         'fieldset' => false,
						         'legend' => false,
						         'div' => array('class' => ''),
						         'options' => $options,
						         'default' => 'PDF',
						   ));
				?>
			</li>
			<li><?php echo $this->Html->link(__('Print Doc'), '' ,array('id' => 'actionExportDoc', 'class' => 'action actionPrinter blank', 'title' => __('Print Doc'))); ?></li>
		</ul>
	</div>
</h2>

<script type="text/javascript">
$(document).ready(function() {
	choiceDocPrint();
	
	$(function() {
		$(".blank").attr("target","_blank");
	});
	
	$('#actionExportDoc').click(function() {
		var prod_delivery_id = $('#prod_delivery_id').val();
		
		var doc_options = $("input[name='doc_options']:checked").val();
		var doc_formato = $("input[name='doc_formato']:checked").val();

		if(doc_formato=='EXCEL' && doc_options=='to-users-all-modify') {
			alert("<?php echo Configure::read('sys_report_not_implement');?>");
			return false;		
		}
		else
		if(doc_formato=='CSV' && doc_options=='to-articles-monitoring') {
			alert("<?php echo Configure::read('sys_report_not_implement');?>");
			return false;		
		}



		if(prod_delivery_id=='') {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
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

		/*
		 * setting, uguale a 
		 *              Ajax::admin_view_tesoriere_export_docs.ctp 	 
		 *				AjaxGasCode::admin_box_doc_print_referente.ctp, 
		 *				Doc::admin_referent_docs_export.ctp, 
		 *				Doc::admin_cassiere_docs_export.ctp, 
		 *              Pages:admin_home.ctp
		 */
		var a = '';
		var b = '';
		var c = '';
		var d = '';
		var e = '';
		if(doc_options=='to-users-all-modify') {
			if($("input[name='trasport1']").length > 0)
				a = $("input[name='trasport1']:checked").val();
		}
		else	
		if(doc_options=='to-users') {
			a = $("input[name='user_phone1']:checked").val();
			b = $("input[name='user_email1']:checked").val();
			c = $("input[name='user_address1']:checked").val();
			d = $("input[name='totale_per_utente']:checked").val();
			if($("input[name='trasport2']").length > 0)
				e = $("input[name='trasport2']:checked").val();
		}
		else
		if(doc_options=='to-users-label') {
			a = $("input[name='user_phone']:checked").val();
			b = $("input[name='user_email']:checked").val();
			c = $("input[name='user_address']:checked").val();
			if($("input[name='trasport3']").length > 0)
				d = $("input[name='trasport3']:checked").val();
		}
		else
		if(doc_options=='to-articles') {
			if($("input[name='trasport4']").length > 0)
				a = $("input[name='trasport4']:checked").val();
		}
		else
		if(doc_options=='to-articles-details') {
			a = $("input[name='acquistato_il']:checked").val();
			if($("input[name='article_img']").length > 0)
				b = $("input[name='article_img']:checked").val();
			if($("input[name='trasport5']").length > 0)
				c = $("input[name='trasport5']:checked").val();
		}	
		 	
		$('#actionExportDoc').attr('href','/administrator/index.php?option=com_cake&controller=ProdExportDocs&action=exportToProduttore&prod_delivery_id='+prod_delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&a='+a+'&b='+b+'&c='+c+'&d='+d+'&format=notmpl');
		return true;
	});	
});
</script>