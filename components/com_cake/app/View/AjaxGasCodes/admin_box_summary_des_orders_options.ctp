<?php 
if($desOrdersResults['DesOrder']['state_code']=='PROCESSED-POST-DELIVERY' ||
   $desOrdersResults['DesOrder']['state_code']=='INCOMING-ORDER' ||   
   $desOrdersResults['DesOrder']['state_code']=='PROCESSED-ON-DELIVERY' ||
   
   
   1==1)

   {
	if(!empty($summaryDesOrderResults)) {
	
		$msg = "I dati sottostanti sono stati precedentemente creati aggregando gli importi degli acquisti degli utenti.";
		echo $this->element('boxMsg',array('class_msg' => 'notice nomargin','msg' => $msg));
	?>
		<div class="summary_des_orders-options-label left label"></div>
		<div class="summary_des_orders-options left radio">
			<span class="tooltip-box">
				<a href="#" class="mytooltip  tooltip-help-img">
					<span class="tooltip-custom tooltip-help">
						<img width="48" height="48" alt="Informazione per aiutarti" src="/images/cake/tooltips/48x48/help.png">
						<em>Informazione per aiutarti</em>
						<?php echo __('toolTipSummaryOrdersOptions');?></span></a>
			</span>
			<input type="radio" checked="checked" name="summary_des_orders-options" id="options-summary_des_orders-delete-no" value="options-delete-no">
				<label for="options-summary_des_orders-delete-no">Mantengo i dati sottostanti</label>
			<input type="radio" name="summary_des_orders-options" id="options-summary_des_orders-delete-yes" value="options-delete-yes">
				<label for="options-summary_des_orders-delete-yes">Rigenero i dati sottostanti perch&egrave; ho modificato gli acquisti degli utenti</label>
		</div>	
		
		<script type="text/javascript">
		$(document).ready(function() {
			$("input[name='summary_des_orders-options']").change(function() {
				choiceSummaryDesOrdersOptions();
			});
		
			choiceSummaryDesOrdersOptions();
		});
		</script>
	<?php 
	} 
	else {
	?>
		<script type="text/javascript">
			choiceSummaryDesOrdersOptions();
		</script>
	<?php 
	} // end if 
}
else { 
?>
<script type="text/javascript">
	choiceSummaryDesOrdersOptionsReadOnly();
</script>
<?php 
} 
?>