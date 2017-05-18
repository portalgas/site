<?php 
if($results['Order']['trasport']>0) {
	/*
	 * update 
	 */
	$trasport = number_format($results['Order']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	?>
	   <div class="input text">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
				   		<label for="trasporto"><?php echo __('Trasport');?></label>
		   				<input style="width:200px;" type="text" value="<?php echo $trasport;?>" name="trasport" id="trasport" size="5" class="importo double" />&nbsp;<span>&euro;</span>
					</td>
					<td>
						<div class="submit"><input id="submitImportoDelete" type="submit" class="buttonBlu" value="<?php echo __('Submit Delete Trasport');?>" /></div>
					</td>
					<td>
						<div class="submit"><input id="submitImportoUpdate" type="submit" value="<?php echo __('Submit Update Trasport');?>" /></div>
					</td>
				</tr>
			</table>
		</div>
		
		<script type="text/javascript">
		jQuery(document).ready(function() {
		
			jQuery('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			jQuery('#trasport').focusout(function() {
				validateNumberPositiveField(this,'importo trasporto');});
				jQuery('.double').focusout(function() {validateNumberPositiveField(this,'importo trasporto');
			});
		
			jQuery('#submitImportoUpdate').click(function() {
		
				var delivery_id = jQuery('#delivery_id').val();
				var order_id = jQuery('#order_id').val();
				var trasport = jQuery('#trasport').val();
				
				if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
					alert("Devi indicare l'importo del trasporto");
					return false;
				}
				
				jQuery('#actionSubmit').val('submitImportoUpdate');
		
				return true;			
			});
		
			jQuery('#submitImportoDelete').click(function() {
		
				jQuery('#actionSubmit').val('submitImportoDelete');
		
				return true;
			});	

			choiceTrasportImporto();
		});					
		</script>			
	<?php 
}
else {
	/*
	 * insert 
	 */
	$trasport = '0,00'; 
   ?>
	   <div class="input text">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
				   		<label for="trasporto"><?php echo __('Trasport');?></label>
		   				<input style="width:200px;" type="text" value="<?php echo $trasport;?>" name="trasport" id="trasport" size="5" class="importo double" />&nbsp;<span>&euro;</span>
					</td>
					<td>
						<div class="submit"><input id="submitImportoInsert" type="submit" value="<?php echo __('Submit Trasport');?>" /></div>
					</td>
				</tr>
			</table>
		</div>	
		
		
		<script type="text/javascript">
		jQuery(document).ready(function() {
		
			jQuery('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			jQuery('#trasport').focusout(function() {
				validateNumberField(this,'importo trasporto');});
				jQuery('.double').focusout(function() {validateNumberField(this,'importo trasporto');
			});
		
			var order_id = jQuery("#order_id").val();
			if(order_id>0)	choiceTrasportOptions();
		
			jQuery('#submitImportoInsert').click(function() {
		
				var delivery_id = jQuery('#delivery_id').val();
				var order_id = jQuery('#order_id').val();
				var trasport = jQuery('#trasport').val();
				
				if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
					alert("Devi indicare l'importo del trasporto");
					return false;
				}
				
				jQuery('#actionSubmit').val('submitImportoInsert');
		
				return true;		
			});
		});					
		</script>			   
   <?php 
} // end if($results['Order']['trasport']>0) 
?>