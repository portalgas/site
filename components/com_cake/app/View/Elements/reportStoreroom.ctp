<?php
if($type=='FE') {
?>
	<tr>
		<td></td>
		<td><b>Dispensa</b> del G.A.S.</td>
		<td></td>
		<td><a class="exportStoreroom" id="export-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa <?php echo __('formatFilePdf');?>"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>
		<td></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportStoreroom" id="export-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa '.__('formatFileExcel').'"><i class="fa fa-file-excel-o fa-2x"></i></a>';
			?>
		</td>		
	</tr>
<?php
}
else {
?>
	<tr>
		<td></td>
		<td><b>Dispensa</b> del G.A.S.</td>
		<td>
		</td>
		<td><a class="exportStoreroom" id="export-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportStoreroom" id="export-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('.exportStoreroom').click(function() {
			var id =  jQuery(this).attr('id');
			idArray = id.split('-');
			var id =  jQuery(this).attr('id');
			var action      = idArray[0];
			var doc_formato = idArray[1];
					
			window.open('/administrator/index.php?option=com_cake&controller=Storerooms&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
		});
	});
	</script>	
<?php
}
?>
