<div class="cost-more-options-label left label"></div>
<div class="cost-more-options left radio">
	<span class="tooltip-box">
		<a href="#" class="mytooltip  tooltip-help-img">
			<span class="tooltip-custom tooltip-help">
				<img width="48" height="48" alt="Informazione per aiutarti" src="/images/cake/tooltips/48x48/help.png">
				<em>Informazione per aiutarti</em>
	<?php 
	echo __('toolTipCostMoreOptions');
	echo '</span></a>';
	echo '</span>';
	echo '<input ';
	if($results['Order']['cost_more_type']=='QTA') echo 'checked="checked"';
	echo ' type="radio" name="cost-more-options" id="options-cost_more-qta" value="options-qta">';
	echo '<label for="options-cost_more-qta">'.__('options_cost_more_qta').'</label>';
	
	if($options_weight=='Y') {
		echo '<input ';
		if($results['Order']['cost_more_type']=='WEIGHT') echo 'checked="checked"';
		echo ' type="radio" name="cost-more-options" id="options-cost_more-weight" value="options-weight">';
		echo '<label for="options-cost_more-weight">'.__('options_cost_more_weight').'</label>';
	}
	
	echo '<input ';
	if($results['Order']['cost_more_type']=='USERS') echo 'checked="checked"'; 
	echo ' type="radio" name="cost-more-options" id="options-cost_more-users" value="options-users">';
	echo '<label for="options-cost_more-users">'.__('options_cost_more_users').'</label>';
echo '</div>';	
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("input[name='cost-more-options']").change(function() {
		choiceCostMoreOptions();		
	});

	<?php if($results['Order']['cost_more_type']!=null) {
		echo "choiceCostMoreOptions();";
	}?>
});
</script>