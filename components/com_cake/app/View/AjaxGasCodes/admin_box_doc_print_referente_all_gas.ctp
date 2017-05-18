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
jQuery(document).ready(function() {
	choiceDocPrint();
	
	jQuery(function() {
		jQuery(".blank").attr("target","_blank");
	});
	
	jQuery('#actionExportDoc').click(function() {
		var delivery_id = jQuery('#delivery_id').val();
		var order_id = jQuery('#order_id').val();
		
		var doc_options = jQuery("input[name='doc_options']:checked").val();
		var doc_formato = jQuery("input[name='doc_formato']:checked").val();

		if(doc_formato=='EXCEL' && doc_options=='to-users-all-modify') {
			alert("<?php echo Configure::read('sys_report_not_implement');?>");
			return false;		
		}
		else
		if(doc_formato=='CSV' && doc_options=='to-articles-monitoring') {
			alert("<?php echo Configure::read('sys_report_not_implement');?>");
			return false;		
		}



		if(delivery_id=='') {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
		if(order_id=='') {
			alert("<?php echo __('jsAlertOrderRequired');?>");
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

		var parametersFilter = setExportDocsParameters(doc_options);
		
		if(doc_options=='to-articles-weight')
			var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToArticlesWeight&delivery_id='+delivery_id+'&order_id='+order_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
		else
			var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToReferent&delivery_id='+delivery_id+'&order_id='+order_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&'+parametersFilter+'&format=notmpl';
			
		jQuery('#actionExportDoc').attr('href', url);
		return true;
	});	
});
</script>