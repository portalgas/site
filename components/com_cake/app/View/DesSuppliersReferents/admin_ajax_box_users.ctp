<?php 
if($isOrganizationTitolare)
	echo $this->element('boxMsg',array('msg' => $msg, 'class_msg' => 'message'));
	
/*
 * utenti ancora da associare
 */
echo $this->Form->select('master_user_id', $users, array('label' => $label, 'multiple' => true, 'size' =>10));
echo $this->Form->select('referent_user_id', $referents, array('multiple' => true, 'size' => 10, 'style' => 'min-width:300px'));
echo $this->Form->hidden('referent_user_ids', array('id' => 'referent_user_ids','value' => ''));
?>

<script type="text/javascript">
$(document).ready(function() {

	$('#master_user_id').click(function() {
		$("#master_user_id option:selected" ).each(function (){			
			$('#referent_user_id').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
	
	$('#referent_user_id').click(function() {
		$("#referent_user_id option:selected" ).each(function (){			
			$('#master_user_id').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
});
</script>