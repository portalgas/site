<?php 
/*
 * utenti ancora da associare
 */
echo '<div class="row">';
echo '<div class="col-md-2">';
echo '<label class="control-label" for="User">'.$this->App->traslateEnum($SuppliersOrganizationsReferentType).'</label> ';
echo '</div>';
echo '<div class="col-md-5">';
echo $this->Form->select('master_user_id-'.$SuppliersOrganizationsReferentType, $users, array('label' => $label, 'multiple' => true, 'size' =>10));
echo '</div>';
echo '<div class="col-md-5">';
echo $this->Form->select('referent_user_id-'.$SuppliersOrganizationsReferentType, $referents, array('multiple' => true, 'size' => 10, 'style' => 'min-width:300px'));
echo $this->Form->hidden('referent_user_ids-'.$SuppliersOrganizationsReferentType, array('id' => 'referent_user_ids-'.$SuppliersOrganizationsReferentType,'value' => ''));
echo '</div>';
echo '</div>';
?>

<script type="text/javascript">
$(document).ready(function() {

	$('#master_user_id-<?php echo $SuppliersOrganizationsReferentType;?>').click(function() {
		$("#master_user_id-<?php echo $SuppliersOrganizationsReferentType;?> option:selected" ).each(function (){			
			$('#referent_user_id-<?php echo $SuppliersOrganizationsReferentType;?>').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
	
	$('#referent_user_id-<?php echo $SuppliersOrganizationsReferentType;?>').click(function() {
		$("#referent_user_id-<?php echo $SuppliersOrganizationsReferentType;?> option:selected" ).each(function (){			
			$('#master_user_id-<?php echo $SuppliersOrganizationsReferentType;?>').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
});
</script>