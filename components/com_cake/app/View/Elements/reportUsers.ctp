<?php
if($type=='FE') {
?>

<?php
}
else {
?>
	<tr>
		<td><a action="usersData" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
		<td>Anagrafica di tutti gli <b>utenti</b></td>
		<td></td>
		<td><a class="usersData" id="usersData-PDF" style="cursor:pointer;" rel="nofollow" title="stampa l'anagrafica degli utenti <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td><a class="usersData" id="usersData-CSV" style="cursor:pointer;" rel="nofollow" title="stampa l'anagrafica degli utenti <?php echo __('formatFileCsv');?>"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="usersData" id="usersData-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa l\'anagrafica degli utenti '.__('formatFilePdf').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<tr class="trConfig" id="trConfigId-usersData">
		<td></td>
		<td colspan="5" id="tdConfigId-userData">
			
			<div class="left label" style="width:125px !important;">Opzioni stampa</div>
			<div class="left radio">
				<?php 
				foreach ($filterUserGroups as $id => $label) {
					echo '<div style="float:left;margin-right:10px;">';
					echo '<input type="checkbox" id="filterUserGroups'.$id.'" name="filterUserGroups" value="'.$id.'" checked />';
					echo '<label for="filterUserGroups'.$id.'">';
					echo $label;
					echo '</label>';
					echo '</div>';					
				}
				
				/*
				echo '<div style="clear:both;float:left;margin-right:10px;">';
				echo '<input type="checkbox" id="filterUsersImg" name="filterUsersImg" value="Y" />';
				echo '<label for="filterUsersImg">';
				echo "Immagine degli utenti";
				echo '</label>';
				echo '</div>';	
				*/			
				?>
			</div>	
						
		</td>
	</tr>
	
	
	
	
	<tr>
		<td><a action="usersDate" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
		<td>Stampa <b>entrata/uscita</b> degli utenti</td>
		<td></td>
		<td></td>
		<td></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="usersDateData" id="usersDateData-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa delle entrata/uscita degli utenti  '.__('formatFilePdf').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<tr class="trConfig" id="trConfigId-usersDate">
		<td></td>
		<td colspan="5" id="tdConfigId-usersDate">
			
			<div class="left label" style="width:125px !important;">Opzioni stampa</div>
			<div class="left radio">
				<p>
					<label for="filterOrder">Ordina per</label>
					<input type="radio" id="filterOrderDateName" name="filterOrderUsersDate" value="NAME" checked /><label for="filterOrderDateName">Nome</label>
					<input type="radio" id="filterOrderDateRegistrer" name="filterOrderUsersDate" value="REGISTERDATA" /><label for="filterOrderDateRegistrer">Data di registrazione</label>
				</p>
			</div>	
						
		</td>
	</tr>
	
	
	
	
	
	
	<tr>
		<td><a action="referentsData" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
		<td>Stampa dei <b>referenti</b></td>
		<td></td>
		<td><a class="referentsData" id="referentsData-PDF" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td><a class="referentsData" id="referentsData-CSV" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti <?php echo __('formatFilePdf');?>"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="referentsData" id="referentsData-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti '.__('formatFilePdf').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<tr class="trConfig" id="trConfigId-referentsData">
		<td></td>
		<td colspan="5" id="tdConfigId-referentsData">
			
			<div class="left label" style="width:125px !important;">Opzioni stampa</div>
			<div class="left radio">
				<p>
					<label for="filterOrder">Ordina per</label>
					<input type="radio" id="filterOrderSuppliers" name="filterOrder" value="SUPPLIERS" checked /><label for="filterOrderSupplier">Produttore</label>
					<input type="radio" id="filterOrderUsers" name="filterOrder" value="USERS" /><label for="filterOrderUsers">Utente</label>
				</p>
			</div>	
						
		</td>
	</tr>
	
	<script type="text/javascript">
	jQuery(document).ready(function() {
	
		jQuery('.usersData').click(function() {	
			var id =  jQuery(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			var checked = jQuery("input[name='filterUserGroups']:checked").val();
			var userGroupIds = "";
			jQuery("input[name='filterUserGroups']").each(function() {
			  if(jQuery(this).is(":checked")) {
			     userGroupId = jQuery(this).val();
			     userGroupIds += userGroupId+",";
			  } 
			});
			
			if(userGroupIds=="")  {
				alert("Devi selezionare almeno un gruppo");
				return false;
			}
			else
				userGroupIds = userGroupIds.substring(0,(userGroupIds.length-1));
	
			/*var filterUsersImg = jQuery("input[name='filterUsersImg']:checked").val();
			if(filterUsersImg!='Y') */ filterUsersImg = 'N';
			
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&userGroupIds='+userGroupIds+'&filterUsersImg='+filterUsersImg+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
		});	
		
		jQuery('.referentsData').click(function() {	
			var id =  jQuery(this).attr('id');
			idArray = id.split('-');
			var action      = idArray[0];
			var doc_formato = idArray[1];
	
			/*
			 * filtri
			 */
			var filterOrder = jQuery("input[name='filterOrder']:checked").val();
			
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&filterOrder='+filterOrder+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
		});	
		
		jQuery('.usersDateData').click(function() {	
			var id =  jQuery(this).attr('id');
			idArray = id.split('-');
			var action      = idArray[0];
			var doc_formato = idArray[1];

			/*
			 * filtri
			 */
			var filterOrder = jQuery("input[name='filterOrderUsersDate']:checked").val();
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&filterOrder='+filterOrder+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
		});	
	});
	</script>	
<?php
}
?>
