<div class="trasport-options-label left label"></div>
<div class="trasport-options left radio">
	<span class="tooltip-box">
		<a href="#" class="mytooltip  tooltip-help-img">
			<span class="tooltip-custom tooltip-help">
				<img width="48" height="48" alt="Informazione per aiutarti" src="/images/cake/tooltips/48x48/help.png">
				<em>Informazione per aiutarti</em>
	<?php 
	echo __('toolTipTrasportOptions');
	echo '</span></a>';
	echo '</span>';
	echo '<input ';
	if($results['Order']['trasport_type']=='QTA') echo 'checked="checked"';
	echo ' type="radio" name="trasport-options" id="options-trasport-qta" value="options-qta">';
	echo '<label for="options-trasport-qta">'.__('options_trasport_qta').'</label>';
	
	if($options_weight=='Y') {
		echo '<input ';
		if($results['Order']['trasport_type']=='WEIGHT') echo 'checked="checked"';
		echo ' type="radio" name="trasport-options" id="options-trasport-weight" value="options-weight">';
		echo '<label for="options-trasport-weight">'.__('options_trasport_weight').'</label>';
	}
	
	echo '<input ';
	if($results['Order']['trasport_type']=='USERS') echo 'checked="checked"'; 
	echo ' type="radio" name="trasport-options" id="options-trasport-users" value="options-users">';
	echo '<label for="options-trasport-users">'.__('options_trasport_users').'</label>';
echo '</div>';	
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("input[name='trasport-options']").change(function() {
		choiceTrasportOptions();		
	});

	<?php if($results['Order']['trasport_type']!=null) {
		echo "choiceTrasportOptions();";
	}?>
});
</script>