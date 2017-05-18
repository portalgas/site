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
jQuery(document).ready(function() {

	jQuery('#master_user_id').click(function() {
		jQuery("#master_user_id option:selected" ).each(function (){			
			jQuery('#referent_user_id').append(jQuery("<option></option>")
	         .attr("value",jQuery(this).val())
	         .text(jQuery(this).text()));
	         
	         jQuery(this).remove();
		});
	});
	
	jQuery('#referent_user_id').click(function() {
		jQuery("#referent_user_id option:selected" ).each(function (){			
			jQuery('#master_user_id').append(jQuery("<option></option>")
	         .attr("value",jQuery(this).val())
	         .text(jQuery(this).text()));
	         
	         jQuery(this).remove();
		});
	});
});
</script>