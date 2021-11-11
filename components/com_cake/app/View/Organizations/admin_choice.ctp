<?php 
echo '<div class="organizations form" style="min-height:350px;">';
echo $this->Form->create('Organization',array( 'id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('Scegli l\'organizzazione').'</legend>';
	
$options =  ['id' => 'organization_id',
				  'empty' => Configure::read('option.empty'),
				  'onChange' => 'javascript:choiceOrganization(this);',
				  'class'=> 'selectpicker', 'data-live-search' => true];
echo $this->Form->input('organization_id',$options);
echo '</fieldset>';
echo '</div>';

echo '<div class="users form">';
echo $this->Form->create('User', ['id' => 'formGasFilter']);
echo '<fieldset>';


echo '<div class="row">';
echo '<div class="col-md-12">';
echo $this->Form->input('mail', ['type' => 'text', 'label' => 'Username / email', 'id' => 'mail', 'size'=>'30', 'value' => '']);
echo '</div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-12" id="user_details"></div>';
echo '</div>';
	
echo '</fieldset>';
echo $this->Form->end();
echo '</div>';
?>
<script type="text/javascript">
function choiceOrganization() {
	var organization_id = $("#organization_id").val();	
	if(organization_id!='') {
		$('#formGas').submit();
	}
}

$('#mail').change(function() {
	var mail = $(this).val();

	if(mail!=undefined && mail!='') {
		var url = "/administrator/index.php?option=com_cake&controller=Organizations&action=get_user_details&q="+mail+"&format=notmpl";
		var idDivTarget = 'user_details';
		ajaxCallBox(url, idDivTarget);		
	}	
	else {
		$('#user_details').htlm("");
	}
});
</script>