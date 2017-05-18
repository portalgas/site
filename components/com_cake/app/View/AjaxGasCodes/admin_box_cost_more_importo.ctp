<?php 
if($results['Order']['cost_more']>0) {
	/*
	 * update 
	 */
	$cost_more = number_format($results['Order']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	?>
	   <div class="input text">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
				   		<label for="cost_more"><?php echo __('CostMore');?></label>
		   				<input style="width:200px;" type="text" value="<?php echo $cost_more;?>" name="cost_more" id="cost_more" size="5" class="importo double" />&nbsp;<span>&euro;</span>
					</td>
					<td>
						<div class="submit"><input id="submitImportoDelete" type="submit" class="buttonBlu" value="<?php echo __('Submit Delete CostMore');?>" /></div>
					</td>
					<td>
						<div class="submit"><input id="submitImportoUpdate" type="submit" value="<?php echo __('Submit Update CostMore');?>" /></div>
					</td>
				</tr>
			</table>
		</div>
		
		<script type="text/javascript">
		jQuery(document).ready(function() {
		
			jQuery('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			jQuery('#cost_more').focusout(function() {
				validateNumberField(this,'importo costo aggiuntivo');});
				jQuery('.double').focusout(function() {validateNumberField(this,'importo costo aggiuntivo');
			});
		
			jQuery('#submitImportoUpdate').click(function() {
		
				var delivery_id = jQuery('#delivery_id').val();
				var order_id = jQuery('#order_id').val();
				var cost_more = jQuery('#cost_more').val();
				
				if(cost_more=='' || cost_more==null || cost_more=='0,00' || cost_more=='0.00' || cost_more=='0') {
					alert("Devi indicare l'importo del costo aggiuntivo");
					return false;
				}
				
				jQuery('#actionSubmit').val('submitImportoUpdate');
		
				return true;			
			});
		
			jQuery('#submitImportoDelete').click(function() {
		
				jQuery('#actionSubmit').val('submitImportoDelete');
		
				return true;
			});	

			choiceCostMoreImporto();
		});					
		</script>			
	<?php 
}
else {
	/*
	 * insert 
	 */
	$cost_more = '0,00'; 
   ?>
	   <div class="input text">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
				   		<label for="cost_more"><?php echo __('CostMore');?></label>
		   				<input style="width:200px;" type="text" value="<?php echo $cost_more;?>" name="cost_more" id="cost_more" size="5" class="importo double" />&nbsp;<span>&euro;</span>
					</td>
					<td>
						<div class="submit"><input id="submitImportoInsert" type="submit" value="<?php echo __('Submit CostMore');?>" /></div>
					</td>
				</tr>
			</table>
		</div>	
		
		
		<script type="text/javascript">
		jQuery(document).ready(function() {
		
			jQuery('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			jQuery('#cost_more').focusout(function() {
				validateNumberField(this,'importo costo aggiuntivo');});
				jQuery('.double').focusout(function() {validateNumberField(this,'importo costo aggiuntivo');
			});
		
			var order_id = jQuery("#order_id").val();
			if(order_id>0)	choiceCostMoreOptions();
		
			jQuery('#submitImportoInsert').click(function() {
		
				var delivery_id = jQuery('#delivery_id').val();
				var order_id = jQuery('#order_id').val();
				var cost_more = jQuery('#cost_more').val();
				
				if(cost_more=='' || cost_more==null || cost_more=='0,00' || cost_more=='0.00' || cost_more=='0') {
					alert("Devi indicare l'importo del costo aggiuntivo");
					return false;
				}
				
				jQuery('#actionSubmit').val('submitImportoInsert');
		
				return true;		
			});
		});					
		</script>			   
   <?php 
} // end if($results['Order']['cost_more']>0) 
?>