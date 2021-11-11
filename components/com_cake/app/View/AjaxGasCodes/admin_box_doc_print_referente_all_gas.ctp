<?php
echo '<h2 class="ico-export-docs">';
echo __('Print Doc');
echo '<div class="actions-img">';
echo '<ul>';
echo '<li>';
echo $this->App->drawFormRadio('Doc','doc_formato',array('options' => $options, 
										'value'=> 'PDF', 'label' => false));			
echo '</li>';
echo '<li style="padding-left:25px;">';
echo $this->Html->link(__('Print Doc'), '' ,array('id' => 'actionExportDoc', 'class' => 'action actionPrinter blank', 'title' => __('Print Doc')));
echo '</li>';
echo '</ul>';
echo '</div>';
echo '</h2>';
?>

<script type="text/javascript">
$(document).ready(function() {
	choiceDocPrint();
	
	$(function() {
		$(".blank").attr("target","_blank");
	});
	
	$('#actionExportDoc').click(function() {
		var delivery_id = $('#delivery_id').val();
		var order_id = $('#order_id').val();
		
		var doc_options = $("input[name='doc_options']:checked").val();
		var doc_formato = $("input[name='data[Doc][doc_formato]']:checked").val();

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
			
		$('#actionExportDoc').attr('href', url);
		return true;
	});	
});
</script>