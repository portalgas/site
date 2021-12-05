<?php 
if($results['Order']['trasport']>0) {
	/*
	 * update 
	 */
	$trasport = number_format($results['Order']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	?>	
	 <div class="row">
		<div class="col-md-4 form-inline">
			<label class="control-label" for="tot_importo"><?php echo __('Importo totale ordine');?></label>
			<input type="text" value="<?php echo $results['Order']['tot_importo'];?>" name="tot_importo" disabled class="form-control" />&nbsp;<span>&euro;</span>	
		</div>	
		<div class="col-md-4 form-inline">
			<label class="control-label" for="trasporto"><?php echo __('Trasport');?></label>
			<input type="text" value="<?php echo $trasport;?>" name="trasport" id="trasport" class="importo double form-control" />&nbsp;<span>&euro;</span>			
		</div>
		<div class="col-md-2">
			<div class="submit"><input id="submitImportoDelete" type="submit" class="buttonBlu" value="<?php echo __('Submit Delete Trasport');?>" /></div>
		</div>
		<div class="col-md-2">
			<div class="submit"><input id="submitImportoUpdate" type="submit" value="<?php echo __('Submit Update Trasport');?>" /></div>
		</div>
 	</div>
		
		<script type="text/javascript">
		$(document).ready(function() {
		
			$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			$('#trasport').focusout(function() {
				validateNumberPositiveField(this,'importo trasporto');});
				$('.double').focusout(function() {validateNumberPositiveField(this,'importo trasporto');
			});
		
			$('#submitImportoUpdate').click(function() {
		
				var delivery_id = $('#delivery_id').val();
				var order_id = $('#order_id').val();
				var trasport = $('#trasport').val();
				
				if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
					alert("Devi indicare l'importo del trasporto");
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
	$trasport = '0,00'; 
   ?>

	  <div class="row">
		<div class="col-md-4 form-inline">
			<label class="control-label" for="tot_importo"><?php echo __('Importo totale ordine');?></label>
			<input type="text" value="<?php echo $results['Order']['tot_importo'];?>" name="tot_importo" disabled class="form-control" />&nbsp;<span>&euro;</span>		
		</div>	  	
		<div class="col-md-4 form-inline">
			<label for="trasporto"><?php echo __('Trasport');?></label>
			<input type="text" value="<?php echo $trasport;?>" name="trasport" id="trasport" class="importo double form-control" />&nbsp;<span>&euro;</span>		
		</div>
		<div class="col-md-4">
			<div class="submit"><input id="submitImportoInsert" type="submit" value="<?php echo __('Submit Trasport');?>" /></div>
		</div>
	  </div>
				
			
		<script type="text/javascript">
		$(document).ready(function() {
		
			$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			$('#trasport').focusout(function() {
				validateNumberField(this,'importo trasporto');});
				$('.double').focusout(function() {validateNumberField(this,'importo trasporto');
			});
		
			var order_id = $("#order_id").val();
			if(order_id>0)	choiceOptions();
		
			$('#submitImportoInsert').click(function() {
		
				var delivery_id = $('#delivery_id').val();
				var order_id = $('#order_id').val();
				var trasport = $('#trasport').val();
				
				if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
					alert("Devi indicare l'importo del trasporto");
					return false;
				}
				
				$('#actionSubmit').val('submitImportoInsert');
		
				return true;		
			});
		});					
		</script>			   
   <?php 
} // end if($results['Order']['trasport']>0) 
?>