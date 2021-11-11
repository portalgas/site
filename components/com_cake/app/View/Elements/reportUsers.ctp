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
		<td rowspan="3" style="vertical-align: middle;">
			<?php
			echo $this->Form->input('organization_id', ['label' => false, 'id' => 'organization_id',
														'options' => $organizationsResults, 'default' => $user->organization['Organization']['id'],
														'empty' => false,'escape' => false]);				
			?>
		</td>
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
			
			<p>Opzioni stampa</p>
			
			<div class="input ">
				<?php 
				foreach ($filterUserGroups as $id => $label) {
					echo '<label class="checkbox-inline"><input type="checkbox" id="filterUserGroups'.$id.'" name="filterUserGroups" value="'.$id.'" checked />'.$label.'</label> ';				
				}
				
				/*
				echo '<div style="clear:both;float:left;margin-right:10px;">';
				echo '<input type="checkbox" id="filterUsersImg" name="filterUsersImg" value="Y" />';
				echo '<label for="filterUsersImg">';
				echo "Immagine degli utenti";
				echo '</label> ';
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

			<p>Opzioni stampa</p>

			<div class="input ">
				<label class="control-label" for="filterType">Ordina per </label>
				<label class="radio-inline" for="filterOrderDateName">
					<input checked="checked" value="NAME" id="filterOrderDateName" name="filterOrderUsersDate" type="radio"> Nome</label>
				<label class="radio-inline" for="filterOrderDateRegistrer">
					<input value="REGISTERDATA" id="filterOrderDateRegistrer" name="filterOrderUsersDate" type="radio"> Data di registrazione</label>
			</div>
						
		</td>
	</tr>
	
	
	
	
	
	
	<tr>
		<td><a action="referentsData" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
		<td>Stampa dei <b>referenti</b></td>
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
			
			<p>Opzioni stampa</p>

			<div class="input ">
				<label class="control-label" for="filterType">Ordina per </label>
				<label class="radio-inline" for="filterOrderSuppliers">
					<input checked="checked" value="SUPPLIERS" id="filterOrderSuppliers" name="filterOrder" type="radio"> Produttore</label>
				<label class="radio-inline" for="filterOrderUsers">
					<input value="USERS" id="filterOrderUsers" name="filterOrder" type="radio"> Utente</label>
			</div>
						
		</td>
	</tr>
	
	<script type="text/javascript">
	$(document).ready(function() {
	
		$('.usersData').click(function() {	
		
			var organization_id = $('#organization_id').val();
			if(organization_id=="")  {
				alert("Devi selezionare un GAS");
				return false;
			}
			 
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action = idArray[0];
			var doc_formato = idArray[1];
	
			var checked = $("input[name='filterUserGroups']:checked").val();
			var userGroupIds = "";
			$("input[name='filterUserGroups']").each(function() {
			  if($(this).is(":checked")) {
			     userGroupId = $(this).val();
			     userGroupIds += userGroupId+",";
			  } 
			});
			
			if(userGroupIds=="")  {
				alert("Devi selezionare almeno un gruppo");
				return false;
			}
			else
				userGroupIds = userGroupIds.substring(0,(userGroupIds.length-1));
	
			/*var filterUsersImg = $("input[name='filterUsersImg']:checked").val();
			if(filterUsersImg!='Y') */ filterUsersImg = 'N';
			
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&organization_id='+organization_id+'&userGroupIds='+userGroupIds+'&filterUsersImg='+filterUsersImg+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
		});	
		
		$('.referentsData').click(function() {
		
			var organization_id = $('#organization_id').val();
			if(organization_id=="")  {
				alert("Devi selezionare un GAS");
				return false;
			}
			 		
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action      = idArray[0];
			var doc_formato = idArray[1];
	
			/*
			 * filtri
			 */
			var filterOrder = $("input[name='filterOrder']:checked").val();
			
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&organization_id='+organization_id+'&filterOrder='+filterOrder+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
		});	
		
		$('.usersDateData').click(function() {
		
			var organization_id = $('#organization_id').val();
			if(organization_id=="")  {
				alert("Devi selezionare un GAS");
				return false;
			}
			 			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action      = idArray[0];
			var doc_formato = idArray[1];

			/*
			 * filtri
			 */
			var filterOrder = $("input[name='filterOrderUsersDate']:checked").val();
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&organization_id='+organization_id+'&filterOrder='+filterOrder+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
		});	
	});
	</script>	
<?php
}
?>