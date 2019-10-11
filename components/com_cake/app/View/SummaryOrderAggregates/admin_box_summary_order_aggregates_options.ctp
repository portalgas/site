<?php 
if(!empty($summaryOrderAggregateResults)) {

	$msg = "I dati sottostanti sono stati precedentemente creati aggregando gli importi degli acquisti degli utenti.";
	echo $this->element('boxMsg',array('class_msg' => 'notice nomargin','msg' => $msg));
?>
	<div class="summary-order-aggregates-options-label left label"></div>
	<div class="summary-order-aggregates-options left radio">
		<span class="tooltip-box">
			<a href="#" class="mytooltip  tooltip-help-img">
				<span class="tooltip-custom tooltip-help">
					<img width="48" height="48" alt="Informazione per aiutarti" src="/images/cake/tooltips/48x48/help.png">
					<em>Informazione per aiutarti</em>
					<?php echo __('toolTipSummaryOrdersOptions');?></span></a>
		</span>
		<label class="radio-inline"><input type="radio" checked="checked" name="summary-order-aggregates-options" id="options-summary_orders-delete-no" value="options-delete-no">Mantengo i dati sottostanti</label>
		<label class="radio-inline"><input type="radio" name="summary-order-aggregates-options" id="options-summary_orders-delete-yes" value="options-delete-yes">Rigenero i dati sottostanti perch&egrave; ho modificato gli acquisti degli utenti</label>
	</div>	
	
	<script type="text/javascript">
	$(document).ready(function() {
		$("input[name='summary-order-aggregates-options']").change(function() {
			choiceSummaryOrderAggregatesOptions();
		});
	
		choiceSummaryOrderAggregatesOptions();
	});
	</script>
<?php 
} 
else {
?>
	<script type="text/javascript">
		choiceSummaryOrderAggregatesOptions();
	</script>
<?php 
} // end if 
?>