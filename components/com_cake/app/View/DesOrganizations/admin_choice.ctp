<div class="desOrganizations form">
<?php echo $this->Form->create('DesOrganization',array( 'id' => 'formGas'));?>
	<fieldset>
		<legend><?php echo __('Scegli il D.E.S.'); ?></legend>
	<?php
		$options =  array('id' => 'des_id',
						  'empty' => Configure::read('option.empty'),
						  'options' => $desOrganizations,
				  		  'onChange' => 'javascript:choiceDesOrganization(this);');
		echo $this->Form->input('des_id', $options);
	?>
	</fieldset>
</div>

<script type="text/javascript">
function choiceDesOrganization() {
	var des_id = jQuery("#des_id").val();
	if(des_id!='') {
		jQuery('#formGas').submit();
	}
}

$( document ).ready(function() {
	if(jQuery('#des_id > option').length==2) {
		var options = jQuery('#des_id').children(); 
		var des_id = jQuery(options[1]).val();
		jQuery(options[1]).attr('selected','selected');
		jQuery('#formGas').submit();		
	}
});
</script>