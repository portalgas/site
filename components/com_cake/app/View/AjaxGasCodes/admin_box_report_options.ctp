<p class="control-group">
	<label class="control-label">Opzioni report</label>
	<label class="radio-inline"><input <?php if($report_options=='report-users-cart') echo 'checked=checked';?> type="radio" name="report-options" id="report-users-cart" value="report-users-cart">Solo utenti con acquisti</label>
	<label class="radio-inline"><input <?php if($report_options=='report-users-all') echo 'checked=checked';?> type="radio" name="report-options" id="report-users-all" value="report-users-all">Tutti gli utenti</label>
	<label class="radio-inline"><input <?php if($report_options=='report-articles-details') echo 'checked=checked';?> type="radio" name="report-options" id="report-articles-details" value="report-articles-details">Articoli aggregati con il dettaglio degli utenti</label>
</p>	
<script type="text/javascript">
$(document).ready(function() {
	$("input[name='report-options']").change(function() {
		choiceReportOptions();
	});

	<?php 
	if(!empty($report_options)) {
	?>
		choiceReportOptions();
	<?php 
	}
	?>	
});
</script>