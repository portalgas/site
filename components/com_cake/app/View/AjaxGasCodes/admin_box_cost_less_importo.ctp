<?php 
if($results['Order']['cost_less']>0) {
	/*
	 * update 
	 */
	$cost_less = number_format($results['Order']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	?>
	 <div class="row">
		<div class="col-md-4 form-inline">
			<label class="control-label" for="tot_importo"><?php echo __('Importo totale ordine');?></label>
			<input type="text" value="<?php echo $results['Order']['tot_importo'];?>" name="tot_importo" disabled class="form-control" />&nbsp;<span>&euro;</span>	
		</div>		 	
		<div class="col-md-4 form-inline">
			<label class="control-label" for="cost_less"><?php echo __('CostLess');?></label>
			<input type="text" value="<?php echo $cost_less;?>" name="cost_less" id="cost_less" class="importo double form-control" />&nbsp;<span>&euro;</span>			
		</div>
		<div class="col-md-2">
			<div class="submit"><input id="submitImportoDelete" type="submit" class="buttonBlu" value="<?php echo __('Submit Delete CostLess');?>" /></div>
		</div>
		<div class="col-md-2">
			<div class="submit"><input id="submitImportoUpdate" type="submit" value="<?php echo __('Submit Update CostLess');?>" /></div>
		</div>
 	</div>
		
		<script type="text/javascript">
		$(document).ready(function() {
		
			$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			$('#cost_less').focusout(function() {
				validateNumberField(this,'importo sconto');});
				$('.double').focusout(function() {validateNumberField(this,'importo sconto');
			});
		
			$('#submitImportoUpdate').click(function() {
		
				var delivery_id = $('#delivery_id').val();
				var order_id = $('#order_id').val();
				var cost_less = $('#cost_less').val();
				
				if(cost_less=='' || cost_less==null || cost_less=='0,00' || cost_less=='0.00' || cost_less=='0') {
					alert("Devi indicare l'importo dello sconto");
					return false;
				}
				
				$('#actionSubmit').val('submitImportoUpdate');
		
				return true;			
			});
		
			$('#submitImportoDelete').click(function() {
		
				$('#actionSubmit').val('submitImportoDelete');
		
				return true;
			});	

			choiceImporto();
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
	  <div class="row">
		<div class="col-md-4 form-inline">
			<label class="control-label" for="tot_importo"><?php echo __('Importo totale ordine');?></label>
			<input type="text" value="<?php echo $results['Order']['tot_importo'];?>" name="tot_importo" disabled class="form-control" />&nbsp;<span>&euro;</span>		
		</div>		  	
		<div class="col-md-4 form-inline">
			<label for="cost_less"><?php echo __('CostLess');?></label>
			<input type="text" value="<?php echo $cost_less;?>" name="cost_less" id="cost_less" class="importo double form-control" />&nbsp;<span>&euro;</span>
		</div>
		<div class="col-md-4">
			<div class="submit"><input id="submitImportoInsert" type="submit" value="<?php echo __('Submit CostLess');?>" /></div>
		</div>
	  </div>
	  		
		<script type="text/javascript">
		$(document).ready(function() {
		
			$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			$('#cost_less').focusout(function() {
				validateNumberField(this,'importo sconto');});
				$('.double').focusout(function() {validateNumberField(this,'importo sconto');
			});
		
			var order_id = $("#order_id").val();
			if(order_id>0)	choiceOptions();
		
			$('#submitImportoInsert').click(function() {
		
				var delivery_id = $('#delivery_id').val();
				var order_id = $('#order_id').val();
				var cost_less = $('#cost_less').val();
				
				if(cost_less=='' || cost_less==null || cost_less=='0,00' || cost_less=='0.00' || cost_less=='0') {
					alert("Devi indicare l'importo dello sconto");
					return false;
				}
				
				$('#actionSubmit').val('submitImportoInsert');
		
				return true;		
			});
		});					
		</script>			   
   <?php 
} // end if($results['Order']['cost_less']>0) 
?>