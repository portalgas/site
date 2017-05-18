<?php 
if($results['Order']['state_code']=='PROCESSED-POST-DELIVERY' ||
   $results['Order']['state_code']=='INCOMING-ORDER' ||   
   $results['Order']['state_code']=='PROCESSED-ON-DELIVERY') {
	if(!empty($resultsCartsSplit)) {
	
		$msg = "I dati sottostanti sono stati precedentemente creati suddividendo le quantità degli acquisti degli utenti.";
		echo $this->element('boxMsg',array('class_msg' => 'notice nomargin','msg' => $msg));
	?>
		<div class="cart-splits-options-label left label"></div>
		<div class="cart-splits-options left radio">
			<span class="tooltip-box">
				<a href="#" class="mytooltip  tooltip-help-img">
					<span class="tooltip-custom tooltip-help">
						<img width="48" height="48" alt="Informazione per aiutarti" src="/images/cake/tooltips/48x48/help.png">
						<em>Informazione per aiutarti</em>
						<?php echo __('toolTipCartsSplitsOptions');?></span></a>
			</span>
			<input type="radio" checked="checked" name="cart-splits-options" id="options-cart-splits-delete-no" value="options-delete-no">
				<label for="options-cart-splits-delete-no">Mantengo i dati sottostanti</label>
			<input type="radio" name="cart-splits-options" id="options-cart-splits-delete-yes" value="options-delete-yes">
				<label for="options-cart-splits-delete-yes">Rigenero i dati sottostanti perch&egrave; ho modificato gli acquisti degli utenti</label>
		</div>	
		
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("input[name='cart-splits-options']").change(function() {
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