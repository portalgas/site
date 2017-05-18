<div class="organizations form" style="min-height:450px;">
<?php echo $this->Form->create('Organization',array( 'id' => 'formGas'));?>
	<fieldset>
		<legend><?php echo __('Scegli l\'organizzazione'); ?></legend>
	<?php
		$options =  array('id' => 'organization_id',
						  'empty' => Configure::read('option.empty'),
				  		  'onChange' => 'javascript:choiceOrganization(this);',
				  		  'class'=> 'selectpicker', 'data-live-search' => true);
		echo $this->Form->input('organization_id',$options);
	?>
	</fieldset>
</div>

<script type="text/javascript">
function choiceOrganization() {
	var organization_id = jQuery("#organization_id").val();	
	if(organization_id!='') {
		jQuery('#formGas').submit();
	}
}
</script>