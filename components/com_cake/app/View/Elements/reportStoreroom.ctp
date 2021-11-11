<?php
if($type=='FE') {
?>
	<tr>
		<td></td>
		<td>Articoli in <b>Dispensa</b></td>
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
	<tr>
		<td></td>
		<td>Articoli in <b>Dispensa</b> e consegne non chiuse</td>
		<td></td>
		<td><a class="exportStoreroom" id="export_current_deliveries-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa <?php echo __('formatFilePdf');?>"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>
		<td></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportStoreroom" id="export_current_deliveries-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa '.__('formatFileExcel').'"><i class="fa fa-file-excel-o fa-2x"></i></a>';
			?>
		</td>		
	</tr>	
	<?php
	if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y' && ($isUserCurrentStoreroom || $isManager)) {

		echo '<tr>';
		echo '	<td></td>';
		echo '	<td>Dispensa, articoli <b>da prenotare</b> e <b>prenotati</b></td>';
		echo '<td></td>';
		echo '<td><a class="exportStoreroomAll" id="exportAll-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli da prenotare e prenotati '.__('formatFilePdf').'"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>';
		echo '<td></td>';
		echo '<td>';
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="exportStoreroomAll" id="exportAll-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli da prenotare e prenotati  '.__('formatFileExcel').'"><i class="fa fa-file-excel-o fa-2x"></i></a>';		
		echo '</td>';
		echo '</tr>';



		echo '<tr>';
		echo '	<td></td>';
		echo '	<td>Dispensa, articoli <b>prenotati</b></td>';
		if(empty($deliveriesStorerooms)) {
			echo '<td colspan="4">';
			echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono consegne per la dispensa"));
			echo '</td>';
		}
		else {
			echo '<td>';
			echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'storeroom_delivery_id', 'options' => $deliveriesStorerooms,
														'empty' => 'Scegli la consegna','escape' => false));
			echo '</td>';
			echo '<td><a class="exportStoreroomBooking" id="exportBooking-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli della dispensa prenotati '.__('formatFilePdf').'"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>';
			echo '<td></td>';
			echo '<td>';
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportStoreroomBooking" id="exportBooking-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli della dispensa prenotati  '.__('formatFileExcel').'"><i class="fa fa-file-excel-o fa-2x"></i></a>';		
			echo '</td>';
		}
		echo '</tr>';	
	}
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		$('.exportStoreroom').click(function() {
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var id =  $(this).attr('id');
			var action      = idArray[0];
			var doc_formato = idArray[1];
					
			window.open('/?option=com_cake&controller=Storerooms&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
		});

		$('.exportStoreroomAll').click(function() {
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var id =  $(this).attr('id');
			var action      = idArray[0];
			var doc_formato = idArray[1];
					
			window.open('/?option=com_cake&controller=Storerooms&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
		});
				
		$('.exportStoreroomBooking').click(function() {
			var delivery_id = $('#storeroom_delivery_id').val();
			if(delivery_id=="") {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
						
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var id =  $(this).attr('id');
			var action      = idArray[0];
			var doc_formato = idArray[1];
					
			window.open('/?option=com_cake&controller=Storerooms&action='+action+'&delivery_id='+delivery_id+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
		});
	});
	</script>	
	<?php
}
else {
 
	echo '<tr>';
	echo '	<td></td>';
	echo '<td>Articoli in <b>Dispensa</b></td>';
	echo '<td></td>';
	if(isset($preview) && $preview)
		echo '<td><a class="exportStoreroom" id="export-PREVIEW" style="cursor:pointer;" rel="nofollow" title="anteprima della stampa della dispensa"><img alt="PREVIEW" src="'.Configure::read('App.img.cake').'/minetypes/32x32/document.png"></a></td>';
	echo '<td><a class="exportStoreroom" id="export-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa '.__('formatFilePdf').'"><img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png"></a></td>';
	echo '<td></td>';
	echo '<td>';
	if(Configure::read('developer.mode'))
		echo 'No in developer mode';
	else
		echo '<a class="exportStoreroom" id="export-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
	echo '</td>';		
	echo '</tr>';
	
	echo '<tr>';
	echo '	<td></td>';
	echo '<td>Articoli in <b>Dispensa</b> e consegne non chiuse</td>';
	echo '<td></td>';
	if(isset($preview) && $preview)
		echo '<td><a class="exportStoreroom" id="export_current_deliveries-PREVIEW" style="cursor:pointer;" rel="nofollow" title="anteprima della stampa della dispensa"><img alt="PREVIEW" src="'.Configure::read('App.img.cake').'/minetypes/32x32/document.png"></a></td>';
	echo '<td><a class="exportStoreroom" id="export_current_deliveries-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa '.__('formatFilePdf').'"><img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png"></a></td>';
	echo '<td></td>';
	echo '<td>';
	if(Configure::read('developer.mode'))
		echo 'No in developer mode';
	else
		echo '<a class="exportStoreroom" id="export_current_deliveries-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa la dispensa '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
	echo '</td>';		
	echo '</tr>';
	
	if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y' && ($isUserCurrentStoreroom || $isManager)) {

		echo '<tr>';
		echo '	<td></td>';
		echo '	<td>Dispensa</b>, articoli <b>da prenotare</b> e <b>prenotati</td>';
		echo '<td></td>';
		if(isset($preview) && $preview)
			echo '<td><a class="exportStoreroomAll" id="exportAll-PREVIEW" style="cursor:pointer;" rel="nofollow" title="anteprima della stampa degli articoli da prenotare e prenotati"><img alt="PREVIEW" src="'.Configure::read('App.img.cake').'/minetypes/32x32/document.png"></a></td>';		
		echo '<td><a class="exportStoreroomAll" id="exportAll-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli da prenotare e prenotati '.__('formatFilePdf').'"><img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png"></a></td>';
		echo '<td></td>';
		echo '<td>';
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="exportStoreroomAll" id="exportAll-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli da prenotare e prenotati  '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';		
		echo '</td>';
		echo '</tr>';	



		echo '<tr>';
		echo '	<td></td>';
		echo '	<td>Dispensa, articoli <b>prenotati</b></td>';
		if(empty($deliveries)) {
			echo '<td colspan="4">';
			echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono consegne per la dispensa"));
			echo '</td>';
		}
		else {
			echo '<td>';
			echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'storeroom_delivery_id', 'options' => $deliveriesStorerooms,
														'empty' => 'Scegli la consegna','escape' => false));
			echo '</td>';
			if(isset($preview) && $preview)
				echo '<td><a class="exportStoreroomBooking" id="exportBooking-PREVIEW" style="cursor:pointer;" rel="nofollow" title="anteprima della stampa degli articoli prenotati"><img alt="PREVIEW" src="'.Configure::read('App.img.cake').'/minetypes/32x32/document.png"></a></td>';		
			echo '<td><a class="exportStoreroomBooking" id="exportBooking-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli della dispensa prenotati '.__('formatFilePdf').'"><img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png"></a></td>';
			echo '<td></td>';
			echo '<td>';
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportStoreroomBooking" id="exportBooking-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli della dispensa prenotati  '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';		
			echo '</td>';
		}
		echo '</tr>';	
	}
	
	/* 
	 * carrello dell'user dispensa
	 */ 
	if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y' && $isUserCurrentStoreroom) {
		echo '<tr>';
		echo '	<td></td>';
		echo '	<td>Cosa <b>arriverà</b> in dispensa (articoli <b>inseriti</b> dai referenti)</td>';
		echo '	<td>';
		echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'cart_delivery_id', 'options' => $cartsDeliveries,
													'empty' => 'Scegli la consegna','escape' => false));
		echo '</td>';
		if(isset($preview) && $preview)
			echo '<td><a class="exportCartDelivery" id="userCart-PREVIEW" style="cursor:pointer;" rel="nofollow" title="anteprima della stampa gli articoli che arriveranno in dispensa alla consegna"><img alt="PREVIEW" src="'.Configure::read('App.img.cake').'/minetypes/32x32/document.png"></a></td>';
		echo '<td><a class="exportCartDelivery" id="userCart-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli che arriveranno in dispensa alla consegna '.__('formatFilePdf').'"><img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png"></a></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '</tr>';	
	}
	?>
	<script type="text/javascript">
	var idDivTarget = 'doc-preview';
	var url = "";
	
	$(document).ready(function() {
		$('.exportStoreroom').click(function() {
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var id =  $(this).attr('id');
			var action      = idArray[0];
			var doc_formato = idArray[1];
	
			if(doc_formato=='PREVIEW') {
				$('#doc-preview').html("");
				$('#doc-preview').show();
				url = "/administrator/index.php?option=com_cake&controller=Storerooms&action="+action+"&doc_formato="+doc_formato+"&format=notmpl";
				ajaxCallBox(url, idDivTarget);	
			}
			else {
				url = "/administrator/index.php?option=com_cake&controller=Storerooms&action="+action+"&doc_formato="+doc_formato+"&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no";
				window.open(url);
			}	
		});
		
		$('.exportStoreroomAll').click(function() {
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var id =  $(this).attr('id');
			var action      = idArray[0];
			var doc_formato = idArray[1];
					
			var url = "";
			
			if(doc_formato=='PREVIEW') {
				$('#doc-preview').html("");
				$('#doc-preview').show();
				url = "/administrator/index.php?option=com_cake&controller=Storerooms&action="+action+"&doc_formato="+doc_formato+"&format=notmpl";
				ajaxCallBox(url, idDivTarget);	
			}
			else {
				url = "/administrator/index.php?option=com_cake&controller=Storerooms&action="+action+"&doc_formato="+doc_formato+"&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no";
				window.open(url);
			}	
		});
		
		$('.exportStoreroomBooking').click(function() {
			var delivery_id = $('#storeroom_delivery_id').val();
			if(delivery_id=="") {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			var url = "";
			
			if(doc_formato=='PREVIEW') {
				$('#doc-preview').html("");
				$('#doc-preview').show();
				url = "/administrator/index.php?option=com_cake&controller=Storerooms&action="+action+"&delivery_id="+delivery_id+"&doc_formato="+doc_formato+"&format=notmpl";
				ajaxCallBox(url, idDivTarget);	
			}
			else {
				url = "/administrator/index.php?option=com_cake&controller=Storerooms&action="+action+"&delivery_id="+delivery_id+"&doc_formato="+doc_formato+"&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no";
				window.open(url);
			}
		});	
		
		$('.exportCartDelivery').click(function() {
			var cart_delivery_id = $('#cart_delivery_id').val();
			if(cart_delivery_id=="") {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			var url = "";
			
			if(doc_formato=='PREVIEW') {
				$('#doc-preview').html("");
				$('#doc-preview').show();
				url = "/administrator/index.php?option=com_cake&controller=ExportDocs&action="+action+"&delivery_id="+cart_delivery_id+"&doc_formato="+doc_formato+"&format=notmpl";
				ajaxCallBox(url, idDivTarget);	
			}
			else {
				url = "/administrator/index.php?option=com_cake&controller=ExportDocs&action="+action+"&delivery_id="+cart_delivery_id+"&doc_formato="+doc_formato+"&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no";
				window.open(url);
			}
		});
			
	});
	</script>	
<?php
}
?>