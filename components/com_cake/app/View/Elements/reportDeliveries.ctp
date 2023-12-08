<?php
if($type=='FE') {
?>

<?php
}
else {
?>
	<tr>
		<td></td>
		<td>Tutti gli <b>acquisti</b> della consegna</td>
		<td rowspan="2" style="vertical-align: middle;">
			<?php
				echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'delivery_id',
															'empty' => 'Scegli la consegna','escape' => false));
			?>
		</td>
		<td><a class="exportDelivery" id="userCart-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli acquisti associati alla consegna <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td>Tutti gli <b>utenti</b> che saranno presenti alla <b>consegna</b></td>
		<td><a class="exportDelivery" id="usersDelivery-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli utenti che saranno presenti alla consegna  <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td><a class="exportDelivery" id="usersDelivery-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli utenti che saranno presenti alla consegna <?php echo __('formatFileCsv');?>"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportDelivery" id="usersDelivery-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli utenti che saranno presenti alla consegna '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>

	<script type="text/javascript">
	$(document).ready(function() {
		
		$('.exportDelivery').click(function() {
			var delivery_id = $('#delivery_id').val();
			if(delivery_id=="") {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action = idArray[0];
			var doc_formato = idArray[1];

			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		});

		
	});
	</script>
<?php
}
?>
