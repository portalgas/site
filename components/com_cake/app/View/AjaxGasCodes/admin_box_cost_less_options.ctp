<div class="row">
	<span class="tooltip-box">
		<a href="#" class="mytooltip  tooltip-help-img">
			<span class="tooltip-custom tooltip-help">
				<img width="48" height="48" alt="Informazione per aiutarti" src="/images/cake/tooltips/48x48/help.png">
				<em>Informazione per aiutarti</em>
	<?php 
	echo __('toolTipCostLessOptions');
	echo '</span></a>';
	echo '</span>';

	echo '<label class="radio-inline">';
	echo '<input ';
	if($results['Order']['cost_less_type']=='QTA') echo 'checked="checked"';
	echo ' type="radio" name="summay-order-plus-options" id="options-qta" value="options-qta"/>';	
	echo __('options_cost_less_qta').'</label> ';
	
	if($options_weight=='Y') {
		echo '<label class="radio-inline">';
		echo '<input ';
		if($results['Order']['cost_less_type']=='WEIGHT') echo 'checked="checked"';
		echo ' type="radio" name="summay-order-plus-options" id="options-weight" value="options-weight" />';
		echo __('options_cost_less_weight').'</label> ';
	}
	
	echo '<label class="radio-inline">';
	echo '<input ';
	if($results['Order']['cost_less_type']=='USERS') echo 'checked="checked"'; 
	echo ' type="radio" name="summay-order-plus-options" id="options-users" value="options-users" />';
	echo __('options_cost_less_users').'</label> ';
echo '</div>';	
?>
<script type="text/javascript">
$(document).ready(function() {
	$("input[name='summay-order-plus-options']").change(function() {
		choiceOptions();		
	});

	<?php if($results['Order']['cost_less_type']!=null) {
		echo "choiceOptions();";
	}?>
});
</script>