<?php 
if($results['Order']['state_code']=='PROCESSED-POST-DELIVERY' ||
   $results['Order']['state_code']=='INCOMING-ORDER' ||   
   $results['Order']['state_code']=='PROCESSED-ON-DELIVERY') {
	if(!empty($resultsCartsSplit)) {
	
		$msg = "I dati sottostanti sono stati precedentemente creati suddividendo le quantità degli acquisti degli utenti.";
		echo $this->element('boxMsg',array('class_msg' => 'notice nomargin','msg' => $msg));
	
		echo '<div class="row">';		
		echo '	<div class="col-md-10">';
		
		echo $this->App->drawTooltip(__('Informazione per aiutarti'),__('toolTipCartsSplitsOptions'), $type='INFO');
		
		echo '<label class="radio-inline"><input type="radio" checked="checked" name="cart-splits-options" id="options-cart-splits-delete-no" value="options-delete-no" />Mantengo i dati sottostanti</label>';
		echo '<label class="radio-inline"><input type="radio" name="cart-splits-options" id="options-cart-splits-delete-yes" value="options-delete-yes" />Rigenero i dati sottostanti perch&egrave; ho modificato gli acquisti degli utenti</label>';
		echo '	</div>';	
		echo '</div>';
	?>		
	<script type="text/javascript">
	$(document).ready(function() {
		$("input[name='cart-splits-options']").change(function() {
			choiceCartsSplitsOptions();
		});
	
		choiceCartsSplitsOptions();
	});
	</script>
	<?php 
	} 
	else {
	?>
		<script type="text/javascript">
			choiceCartsSplitsOptions();
		</script>
	<?php 
	} // end if 
}
else {
?>
<script type="text/javascript">
	choiceCartsSplitsOptionsReadOnly();
</script>
<?php 
} 
?>