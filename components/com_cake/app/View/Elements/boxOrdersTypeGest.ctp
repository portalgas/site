<div class="input required">

	<label for="">Gestione degli acquisti</label>
		
	<div style="width:75%;float: right;">
	 
		<table>
			<?php
			/* ********************************************************************************************************
			<tr>
				<td style="width: 32px;">
					<input type="radio" checked="checked" value="" id="" name="data[Order][typeGestDisabled]" />
				</td>		
				<td style="width: 32px;">
					<div style="height: 32px;" title="<?php echo __('Management Carts One');?>" class="actionEditDbOne"></div>
				</td>
				<td>
					<?php echo __('Management Carts One');?>
				</td>
				<td></td>
			</tr>
			<tr>
				<td colspan="4">ed inoltre</td>
			</tr>
			******************************************************************************************************** */ ?>	
			<tr>
				<td>
					<?php 		
					if($modalita=='VIEW') {
						if(empty($value)) echo '<input type="radio" checked="checked" value="" id="" name="data[Order][typeGest]" />';
					}
					else {
						echo '<input type="radio"'; 
						if(empty($value)) echo 'checked="checked" ';			
						echo 'value="" id="" name="data[Order][typeGest]" />';
					}
					?>
				</td>
				<td></td>
				<td>
					Nessuno di questi
				</td>
				<td></td>
			</tr>			
			<tr>
				<td>
					<?php 		
					if($modalita=='VIEW') {
						if($value=='AGGREGATE') echo '<input type="radio" checked="checked" value="AGGREGATE" id="" name="data[Order][typeGest]" />';
					}
					else {
						echo '<input type="radio"'; 
						if($value=='AGGREGATE') echo 'checked="checked" ';			
						echo 'value="AGGREGATE" id="" name="data[Order][typeGest]" />';
					}
					?>
				</td>
				<td>
					<div style="height: 32px;width: 32px;" title="<?php echo __('Management Carts Group By Users');?>" class="actionEditDbGroupByUsers"></div>
				</td>
				<td>
					<?php echo __('Management Carts Group By Users');?>
				</td>
				<td style="width:50px;">
					<div class="tooltip-help-img" id="active_example_aggregate" style="cursor:pointer;" title="Clicca per visualizzare l'esempio"></div>
				</td>
			</tr>
			<tr class="trView" style="display:none;" id="example_aggregate">
				<td></td>
				<td colspan="3">
				
					<h2>Esempio</h2>
					<table>
						<tr>
							<th>Gasista</th>
							<th>ha ordinato</th>
							<th>con l'importo</th>
							<th>Gestito la somma degli importi</th>
						</tr>
						<tr>
							<td rowspan="2">Rossi Mario</th>
							<td>2 orate</th>
							<td>10,00 &euro;</th>
							<td rowspan="2">10,00 &euro; + 5,00 &euro; = <b>15,00</b> &euro;</td>
						</tr>
						<tr>
							<td>1 branzino</th>
							<td>5,00 &euro;</th>
						</tr>
					</table>
				
				</td>
			</tr>		
			<tr>
				<td>
					<?php 		
					if($modalita=='VIEW') {
						if($value=='SPLIT') echo '<input type="radio" checked="checked" value="AGGREGATE" id="" name="data[Order][typeGest]" />';
					}
					else {
						echo '<input type="radio"'; 
						if($value=='SPLIT') echo 'checked="checked" ';			
						echo 'value="SPLIT" id="" name="data[Order][typeGest]" />';
					}
					?>
				</td>
				<td>
					<div style="height: 32px;width: 32px;" title="<?php echo __('Management Carts Split');?>" class="actionEditDbSplit"></div>
				</td>
				<td>
					<?php echo __('Management Carts Split');?>
				</td>
				<td style="width:50px;">
					<div class="tooltip-help-img" id="active_example_split" style="cursor:pointer;" title="Clicca per visualizzare l'esempio"></div>
				</td>
			</tr>
			<tr class="trView" style="display:none;" id="example_split">
				<td></td>
				<td colspan="3">
				
					<h2>Esempio</h2>
					<table>
						<tr>
							<th>Gasista</th>
							<th>ha ordinato</th>
							<th>con l'importo</th>
							<th>Gestito ogni singola quantità</th>
						</tr>
						<tr>
							<td rowspan="2">Rossi Mario</th>
							<td rowspan="2"><b>2</b> orate</th>
							<td rowspan="2">10,00 &euro;</th>
							<td><b>1</b> orate&nbsp;&nbsp;.... &euro;</th>
						</tr>
						<tr>
							<td><b>1</b> orata&nbsp;&nbsp;.... &euro;</th>
						</tr>
					</table>
								
				</td>
			</tr>
		</table>
	
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function() { 
	jQuery('#active_example_aggregate').click(function() {
		if(jQuery('#example_aggregate').css('display')=='none')
			jQuery('#example_aggregate').show();
		else
			jQuery('#example_aggregate').hide();
	});
	
	jQuery('#active_example_split').click(function() {
		if(jQuery('#example_split').css('display')=='none')
			jQuery('#example_split').show();
		else
			jQuery('#example_split').hide();
	});
});
</script>