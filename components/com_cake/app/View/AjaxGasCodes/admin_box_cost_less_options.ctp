<div class="cost-less-options-label left label"></div>
<div class="cost-less-options left radio">
	<span class="tooltip-box">
		<a href="#" class="mytooltip  tooltip-help-img">
			<span class="tooltip-custom tooltip-help">
				<img width="48" height="48" alt="Informazione per aiutarti" src="/images/cake/tooltips/48x48/help.png">
				<em>Informazione per aiutarti</em>
	<?php 
	echo __('toolTipCostLessOptions');
	echo '</span></a>';
	echo '</span>';
	echo '<input ';
	if($results['Order']['cost_less_type']=='QTA') echo 'checked="checked"';
	echo ' type="radio" name="cost-less-options" id="options-cost_less-qta" value="options-qta">';
	echo '<label for="options-cost_less-qta">'.__('options_cost_less_qta').'</label>';
	
	if($options_weight=='Y') {
		echo '<input ';
		if($results['Order']['cost_less_type']=='WEIGHT') echo 'checked="checked"';
		echo ' type="radio" name="cost-less-options" id="options-cost_less-weight" value="options-weight">';
		echo '<label for="options-cost_less-weight">'.__('options_cost_less_weight').'</label>';
	}
	
	echo '<input ';
	if($results['Order']['cost_less_type']=='USERS') echo 'checked="checked"'; 
	echo ' type="radio" name="cost-less-options" id="options-cost_less-users" value="options-users">';
	echo '<label for="options-cost_less-users">'.__('options_cost_less_users').'</label>';
echo '</div>';	
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("input[name='cost-less-options']").change(function() {
		choiceCostLessOptions();		
	});

	<?php if($results['Order']['cost_less_type']!=null) {
		echo "choiceCostLessOptions();";
	}?>
});
</script>