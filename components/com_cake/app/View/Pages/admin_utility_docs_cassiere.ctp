<div class="docs">
	
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th>Tipologia di utility</th>
		<th></th>
		<th>Formato .xls</th>
		<th>Formato .xlsx</th>
	</tr>	
	<tr>
		<td>Quadratura Cassa Contanti</td>
		<td></td>
		<td>
			<a target="_blank" href="/images/utilities/quadratura_cassa_contanti.xls" style="cursor:pointer;" rel="nofollow" title="Quadratura Cassa Contanti formato .xls">
			<img alt=".xls" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/vcalendar.png"></a>
		</td>
		<td>
			<a target="_blank" href="/images/utilities/quadratura_cassa_contanti.xlsx" style="cursor:pointer;" rel="nofollow" title="Quadratura Cassa Contanti formato .xlsx">
			<img alt=".xlsx" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/vcalendar.png"></a>
		</td>		
	</tr>
	<tr>
		<td>Quadratura Contanti e POS</td>
		<td></td>
		<td>
			<a target="_blank" href="/images/utilities/quadratura_cassa_contanti_e_POS.xls" style="cursor:pointer;" rel="nofollow" title="Quadratura Cassa Contanti e POS formato .xls">
			<img alt=".xls" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/vcalendar.png"></a>
		</td>
		<td>
			<a target="_blank" href="/images/utilities/quadratura_cassa_contanti_e_POS.xlsx" style="cursor:pointer;" rel="nofollow" title="Quadratura Cassa Contanti e POS formato .xlsx">
			<img alt=".xlsx" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/vcalendar.png"></a>
		</td>
	</tr>
	</table>
</div>


<script type="text/javascript">
$(document).ready(function() {

	$('.cashsData').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];
		
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});	
	
	$('.exportToCassiereListSuppliersAll').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		var delivery_id = $('#delivery_id').val();
		if(delivery_id=="") {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
																													
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=to-lists-suppliers-cassiere&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});
	
	$('.exportToCassiereListOrdersAll').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		var delivery_id = $('#delivery_id').val();
		if(delivery_id=="") {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
			
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=to-lists-orders-cassiere&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});		
	
	$('.exportToCassiereListUsersDeliveryAll').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		var delivery_id = $('#delivery_id').val();
		if(delivery_id=="") {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
			
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=to-list-users-delivery-cassiere&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});	
	
});
</script>