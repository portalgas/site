<?php 
if($results['Order']['state_code']=='PROCESSED-POST-DELIVERY' ||
   $results['Order']['state_code']=='INCOMING-ORDER' ||   
   $results['Order']['state_code']=='PROCESSED-ON-DELIVERY') {
	if(!empty($resultsSummaryOrder)) {
	
		$msg = "I dati sottostanti sono stati precedentemente creati aggregando gli importi degli acquisti degli utenti.";
		echo $this->element('boxMsg',array('class_msg' => 'notice nomargin','msg' => $msg));
	?>
		<div class="summary_orders-options-label left label"></div>
		<div class="summary_orders-options left radio">
			<span class="tooltip-box">
				<a href="#" class="mytooltip  tooltip-help-img">
					<span class="tooltip-custom tooltip-help">
						<img width="48" height="48" alt="Informazione per aiutarti" src="/images/cake/tooltips/48x48/help.png">
						<em>Informazione per aiutarti</em>
						<?php echo __('toolTipSummaryOrdersOptions');?></span></a>
			</span>
			<input type="radio" checked="checked" name="summary_orders-options" id="options-summary_orders-delete-no" value="options-delete-no">
				<label for="options-summary_orders-delete-no">Mantengo i dati sottostanti</label>
			<input type="radio" name="summary_orders-options" id="options-summary_orders-delete-yes" value="options-delete-yes">
				<label for="options-summary_orders-delete-yes">Rigenero i dati sottostanti perch&egrave; ho modificato gli acquisti degli utenti</label>
		</div>	
		
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("input[name='summary_orders-options']").change(function() {
				choiceSummaryOrdersOptions();
			});
		
			choiceSummaryOrdersOptions();
		});
		</script>
	<?php 
	} 
	else {
	?>
		<script type="text/javascript">
			choiceSummaryOrdersOptions();
		</script>
	<?php 
	} // end if 
}
else { 
?>
<script type="text/javascript">
	choiceSummaryOrdersOptionsReadOnly();
</script>
<?php 
} 
?>