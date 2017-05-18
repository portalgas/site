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
						         'default' => 'EXCEL',
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
		var user_id = jQuery('#user_id').val();
		
		var doc_options = jQuery("input[name='doc_options']:checked").val();
		var doc_formato = jQuery("input[name='doc_formato']:checked").val();

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
			
					jQuery("#user_id > option").each(function() {
		    			/* console.log(this.text + ' ' + this.value); */ 
					
						var user_id = this.value;
		    			if(user_id!='' && user_id!='ALL') {
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
    				jQuery('#actionExportDoc').attr('href', url);
    		}
    		else
    		if(doc_options=='to-delivery-cassiere-users-compact-all') {
			
					alert("<?php echo __('jsAlertPrintWaiting');?>");
				
    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereAllDelivery&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    				jQuery('#actionExportDoc').attr('href', url);
    		}
			else
    		if(doc_options=='to-lists-suppliers-cassiere') {
    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereListOrders&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    				jQuery('#actionExportDoc').attr('href', url);
    		}
    		else
    		if(doc_options=='to-lists-orders-cassiere') {
    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereListOrders&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    				jQuery('#actionExportDoc').attr('href', url);
    		}
    		else
    		if(doc_options=='to-list-users-delivery-cassiere') {
    				url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiereListUsersDelivery&delivery_id='+delivery_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    				jQuery('#actionExportDoc').attr('href', url);
    		}
		}
		else {
			/* to-delivery-cassiere-user-one */
			url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToCassiere&delivery_id='+delivery_id+'&user_id='+user_id+'&doc_options='+doc_options+'&doc_formato='+doc_formato+'&format=notmpl';
    		jQuery('#actionExportDoc').attr('href', url);
    	}
		
		return true;
	});	
});
</script>