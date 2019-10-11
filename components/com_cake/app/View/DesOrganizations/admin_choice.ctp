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
	var des_id = $("#des_id").val();
	if(des_id!='') {
		$('#formGas').submit();
	}
}

$( document ).ready(function() {
	if($('#des_id > option').length==2) {
		var options = $('#des_id').children(); 
		var des_id = $(options[1]).val();
		$(options[1]).attr('selected','selected');
		$('#formGas').submit();		
	}
});
</script>