<?php 
if($results['Order']['cost_more']>0) {
	/*
	 * update 
	 */
	$cost_more = number_format($results['Order']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	?>
	 <div class="row">
		<div class="col-md-4 form-inline">
			<label class="control-label" for="tot_importo"><?php echo __('Importo totale ordine');?></label>
			<input type="number" value="<?php echo $results['Order']['tot_importo'];?>" name="tot_importo" disabled class="form-control" />&nbsp;<span>&euro;</span>	
		</div>		 	
		<div class="col-md-4 form-inline">
			<label class="control-label" for="cost_more"><?php echo __('CostMore');?></label>
			<input type="text" value="<?php echo $cost_more;?>" name="cost_more" id="cost_more" class="importo double form-control" />&nbsp;<span>&euro;</span>
		</div>
		<div class="col-md-2">
			<div class="submit"><input id="submitImportoDelete" type="submit" class="buttonBlu" value="<?php echo __('Submit Delete CostMore');?>" /></div>
		</div>
		<div class="col-md-2">
			<div class="submit"><input id="submitImportoUpdate" type="submit" value="<?php echo __('Submit Update CostMore');?>" /></div>
		</div>
 	</div>

		<script type="text/javascript">
		$(document).ready(function() {
		
			$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			$('#cost_more').focusout(function() {
				validateNumberField(this,'importo costo aggiuntivo');});
				$('.double').focusout(function() {validateNumberField(this,'importo costo aggiuntivo');
			});

			$('#submitImportoUpdate').click(function() {
		
				var delivery_id = $('#delivery_id').val();
				var order_id = $('#order_id').val();
				var cost_more = $('#cost_more').val();
				
				if(cost_more=='' || cost_more==null || cost_more=='0,00' || cost_more=='0.00' || cost_more=='0') {
					alert("Devi indicare l'importo del costo aggiuntivo");
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
	$cost_more = '0,00'; 
   ?>
	  <div class="row">
		<div class="col-md-3 form-inline">
			<label class="control-label" for="tot_importo"><?php echo __('Importo totale ordine');?></label>
			<input type="text" value="<?php echo $results['Order']['tot_importo'];?>" name="tot_importo" disabled class="form-control" />&nbsp;<span>&euro;</span>		
		</div>
          <div class="col-md-3 form-inline">
              <label for="trasporto"><?php echo __('Percentuale');?></label>
              <input type="number" min="0" value="" name="perc" id="perc" class="perc form-control" />
          </div>
		<div class="col-md-3 form-inline">
			<label for="cost_more"><?php echo __('CostMore');?></label>
			<input type="text" value="<?php echo $cost_more;?>" name="cost_more" id="cost_more" class="importo double form-control" />&nbsp;<span>&euro;</span>
		</div>
		<div class="col-md-3">
			<div class="submit"><input id="submitImportoInsert" type="submit" value="<?php echo __('Submit CostMore');?>" /></div>
		</div>
	  </div>
	  		
		<script type="text/javascript">
		$(document).ready(function() {
		
			$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
		
			$('#cost_more').focusout(function() {
				validateNumberField(this,'importo costo aggiuntivo');});
				$('.double').focusout(function() {validateNumberField(this,'importo costo aggiuntivo');
			});

            $('.perc').change(function() {
                let perc = Number($(this).val());
                let tot_importo = numberToJs($('input[name="tot_importo"').val());
                tot_importo = Number(tot_importo);
                let tot_importo_perc = (tot_importo / 100 * perc);
                $('#cost_more').val(number_format(tot_importo_perc,2,',','.'));
            });

			var order_id = $("#order_id").val();
			if(order_id>0)	choiceOptions();
		
			$('#submitImportoInsert').click(function() {
		
				var delivery_id = $('#delivery_id').val();
				var order_id = $('#order_id').val();
				var cost_more = $('#cost_more').val();
				
				if(cost_more=='' || cost_more==null || cost_more=='0,00' || cost_more=='0.00' || cost_more=='0') {
					alert("Devi indicare l'importo del costo aggiuntivo");
					return false;
				}
				
				$('#actionSubmit').val('submitImportoInsert');
		
				return true;		
			});
		});					
		</script>			   
   <?php 
} // end if($results['Order']['cost_more']>0) 
?>