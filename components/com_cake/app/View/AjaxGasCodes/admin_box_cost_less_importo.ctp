<?php 
if($results['Order']['cost_less']>0) {
	/*
	 * update 
	 */
	$cost_less = number_format($results['Order']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	?>
	   <div class="input text">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
				   		<label for="cost_less"><?php echo __('CostLess');?></label>
		   				<input style="width:200px;" type="text" value="<?php echo $cost_less;?>" name="cost_less" id="cost_less" size="5" class="importo double" />&nbsp;<span>&euro;</span>
					</td>
					<td>
						<div class="submit"><input id="submitImportoDelete" type="submit" class="buttonBlu" value="<?php echo __('Submit Delete CostLess');?>" /></div>
					</td>
					<td>
						<div class="submit"><input id="submitImportoUpdate" type="submit" value="<?php echo __('Submit Update CostLess');?>" /></div>
					</td>
				</tr>
			</table>
		</div>
		
		<script type="text/javascript">
		jQuery(document).ready(function() {
		
			jQuery('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			jQuery('#cost_less').focusout(function() {
				validateNumberField(this,'importo sconto');});
				jQuery('.double').focusout(function() {validateNumberField(this,'importo sconto');
			});
		
			jQuery('#submitImportoUpdate').click(function() {
		
				var delivery_id = jQuery('#delivery_id').val();
				var order_id = jQuery('#order_id').val();
				var cost_less = jQuery('#cost_less').val();
				
				if(cost_less=='' || cost_less==null || cost_less=='0,00' || cost_less=='0.00' || cost_less=='0') {
					alert("Devi indicare l'importo dello sconto");
					return false;
				}
				
				jQuery('#actionSubmit').val('submitImportoUpdate');
		
				return true;			
			});
		
			jQuery('#submitImportoDelete').click(function() {
		
				jQuery('#actionSubmit').val('submitImportoDelete');
		
				return true;
			});	

			choiceCostLessImporto();
		});					
		</script>			
	<?php 
}
else {
	/*
	 * insert 
	 */
	$cost_less = '0,00'; 
   ?>
	   <div class="input text">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
				   		<label for="cost_less"><?php echo __('CostLess');?></label>
		   				<input style="width:200px;" type="text" value="<?php echo $cost_less;?>" name="cost_less" id="cost_less" size="5" class="importo double" />&nbsp;<span>&euro;</span>
					</td>
					<td>
						<div class="submit"><input id="submitImportoInsert" type="submit" value="<?php echo __('Submit CostLess');?>" /></div>
					</td>
				</tr>
			</table>
		</div>	
		
		
		<script type="text/javascript">
		jQuery(document).ready(function() {
		
			jQuery('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			jQuery('#cost_less').focusout(function() {
				validateNumberField(this,'importo sconto');});
				jQuery('.double').focusout(function() {validateNumberField(this,'importo sconto');
			});
		
			var order_id = jQuery("#order_id").val();
			if(order_id>0)	choiceCostLessOptions();
		
			jQuery('#submitImportoInsert').click(function() {
		
				var delivery_id = jQuery('#delivery_id').val();
				var order_id = jQuery('#order_id').val();
				var cost_less = jQuery('#cost_less').val();
				
				if(cost_less=='' || cost_less==null || cost_less=='0,00' || cost_less=='0.00' || cost_less=='0') {
					alert("Devi indicare l'importo dello sconto");
					return false;
				}
				
				jQuery('#actionSubmit').val('submitImportoInsert');
		
				return true;		
			});
		});					
		</script>			   
   <?php 
} // end if($results['Order']['cost_less']>0) 
?>