<?php
echo '<h2 class="ico-export-docs">';
echo __('Print Doc');
echo '<div class="actions-img">';
echo '<ul>';
echo '<li>';					   
echo $this->App->drawFormRadio('typeDoc','doc_formato',array('options' => $options, 
										'value'=> 'EXCEL', 'label' => false));										
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
		var user_id = $('#user_id').val();
		
		var doc_options = $("input[name='doc_options']:checked").val();
		var doc_formato = $("input[name='data[typeDoc][doc_formato]']:checked").val();

		if(delivery_id=='') {
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

		/* console.log('user_id '+user_id); */

		if(user_id=='ALL') {
			console.log('doc_options '+doc_options); 
			if(doc_options=='to-delivery-cassiere-users-all-split') {
			
					$("#user_id > option").each(function() {
		    			/* console.log(this.text + ' ' + this.value); */ 
					
						var user_id = this.value;
		    			if(user_id!='' && user_id!='ALL') {
						// if(user_id==827) {		
		    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiere&delivery_id='+delivery_id+'&user_id='+user_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
		    				/* console.log(url); */ 
		    				window.open(url,'win'+user_id,'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=300,height=200,directories=no,location=no'); 
						} 
					});	
					
					return false;		
			}
    		else
    		if(doc_options=='to-delivery-cassiere-users-all') {
			
					alert("<?php echo __('jsAlertPrintWaiting');?>");
				
    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereAllDelivery&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    				$('#actionExportDoc').attr('href', url);
    		}
    		else
    		if(doc_options=='to-delivery-cassiere-users-compact-all') {
			
					alert("<?php echo __('jsAlertPrintWaiting');?>");
				
    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereAllDelivery&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    				$('#actionExportDoc').attr('href', url);
    		}
			else
    		if(doc_options=='to-lists-suppliers-cassiere') {
    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereListOrders&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    				$('#actionExportDoc').attr('href', url);
    		}
    		else
    		if(doc_options=='to-lists-orders-cassiere') {
    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereListOrders&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    				$('#actionExportDoc').attr('href', url);
    		}
    		else
    		if(doc_options=='to-list-users-delivery-cassiere') {
    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereListUsersDelivery&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    				$('#actionExportDoc').attr('href', url);
    		}
		}
		else {
			/* to-delivery-cassiere-user-one */
			url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiere&delivery_id='+delivery_id+'&user_id='+user_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    		$('#actionExportDoc').attr('href', url);
    	}
		
		return true;
	});	
});
</script>