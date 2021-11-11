<div class="report-options-label left label">Opzioni report</div>
<div class="report-options left radio">
	<input <?php if($report_options=='report-users-cart') echo 'checked=checked';?> type="radio" name="report-options" id="report-users-cart" value="report-users-cart"><label for="report-users-cart">Solo utenti con acquisti</label>
	<input <?php if($report_options=='report-users-all') echo 'checked=checked';?> type="radio" name="report-options" id="report-users-all" value="report-users-all"><label for="report-users-all">Tutti gli utenti</label>
	<input <?php if($report_options=='report-articles-details') echo 'checked=checked';?> type="radio" name="report-options" id="report-articles-details" value="report-articles-details"><label for="report-articles-details">Articoli aggregati con il dettaglio degli utenti</label>
</div>	
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