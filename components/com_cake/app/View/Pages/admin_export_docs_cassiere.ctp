<div class="docs">
	
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th>Tipologia di documento</th>
		<th></th>
		<th>Formato pdf</th>
		<th>Formato excel</th>
	</tr>
	<tr>
		<td>Stampa della <b>Cassa</b></td>
		<td></td>
		<td><a class="cashsData" id="cashsData-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la cassa <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="cashsData" id="cashsData-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa la cassa '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<tr>
		<td>Stampa della <b>Cassa</b> con storico</td>
		<td></td>
		<td><a class="cashsHistoryData" id="cashsHistoryData-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la cassa con lo storico <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="cashsHistoryData" id="cashsHistoryData-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa la cassa con lo storico '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	
	<?php
	if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
	?>
	<tr>
		<td><?php echo __('to_cassiere_pos');?></td>
		<td>
			<?php
				echo $this->Form->input('years_pos',array('label' => false, 'id' => 'years_pos', 'options' => $years_pos,
															'empty' => 'Scegli l\'anno','escape' => false));
			?>		
		</td>
		<td><a class="exportToCassiereImportoPos" id="exportToCassiereImportoPos-PDF" style="cursor:pointer;" rel="nofollow" title="<?php echo __('to_cassiere_pos');?> <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportToCassiereImportoPos" id="exportToCassiereImportoPos-EXCEL" style="cursor:pointer;" rel="nofollow" title="'.__('to_cassiere_pos').' '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<?php
	}
	
	if($user->organization['Organization']['payToDelivery']=='ON' || $user->organization['Organization']['payToDelivery']=='ON-POST') {
	?>
	<tr>
		<td><?php echo __('to_lists_suppliers_cassiere');?></td>
		<td rowspan="3" style="vertical-align: middle;">
			<?php
				echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'delivery_id',
															'empty' => 'Scegli la consegna','escape' => false));
			?>		
		</td>
		<td><a class="exportToCassiereListSuppliersAll" id="exportToCassiereListOrders-PDF" style="cursor:pointer;" rel="nofollow" title="<?php echo __('to_lists_suppliers_cassiere');?> <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportToCassiereListSuppliersAll" id="exportToCassiereListOrders-EXCEL" style="cursor:pointer;" rel="nofollow" title="'.__('to_lists_suppliers_cassiere').' '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<tr>
		<td><?php echo __('to_lists_orders_cassiere');?></td>
		<td><a class="exportToCassiereListOrdersAll" id="exportToCassiereListOrdersAll-PDF" style="cursor:pointer;" rel="nofollow" title="<?php echo __('to_lists_orders_cassiere');?> <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportToCassiereListOrdersAll" id="exportToCassiereListOrdersAll-EXCEL" style="cursor:pointer;" rel="nofollow" title="'.__('to_lists_orders_cassiere').' '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>		
	<tr>
		<td><?php echo __('to_list_users_delivery_cassiere');?></td>
		<td><a class="exportToCassiereListUsersDeliveryAll" id="exportToCassiereListUsersDeliveryAll-PDF" style="cursor:pointer;" rel="nofollow" title="<?php echo __('to_list_users_delivery_cassiere');?> <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportToCassiereListUsersDeliveryAll" id="exportToCassiereListUsersDeliveryAll-EXCEL" style="cursor:pointer;" rel="nofollow" title="'.__('to_list_users_delivery_cassiere').' '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<?php
	}
	?>				
	</table>
</div>


<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.cashsData').click(function() {	
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];
		
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});	
	
	jQuery('.cashsHistoryData').click(function() {	
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];
		
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});	
	
	jQuery('.exportToCassiereImportoPos').click(function() {	
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		var years_pos = jQuery('#years_pos').val();
		if(years_pos=="") {
			alert("Devi scegliere l'anno");
			return false;
		}
																													
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&years_pos='+years_pos+'&doc_options=to-cassiere-pos&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});

	
	jQuery('.exportToCassiereListSuppliersAll').click(function() {	
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		var delivery_id = jQuery('#delivery_id').val();
		if(delivery_id=="") {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
																													
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=to-lists-suppliers-cassiere&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});
	
	jQuery('.exportToCassiereListOrdersAll').click(function() {	
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		var delivery_id = jQuery('#delivery_id').val();
		if(delivery_id=="") {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
			
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=to-lists-orders-cassiere&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});		
	
	jQuery('.exportToCassiereListUsersDeliveryAll').click(function() {	
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		var delivery_id = jQuery('#delivery_id').val();
		if(delivery_id=="") {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
			
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=to-list-users-delivery-cassiere&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});	
	
});
</script>