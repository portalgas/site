<?php 
/*
 * utenti ancora da associare
 */
echo '<label for="User">'.$this->App->traslateEnum($SuppliersOrganizationsReferentType).'</label>';

echo $this->Form->select('master_user_id-'.$SuppliersOrganizationsReferentType, $users, array('label' => $label, 'multiple' => true, 'size' =>10));
echo $this->Form->select('referent_user_id-'.$SuppliersOrganizationsReferentType, $referents, array('multiple' => true, 'size' => 10, 'style' => 'min-width:300px'));
echo $this->Form->hidden('referent_user_ids-'.$SuppliersOrganizationsReferentType, array('id' => 'referent_user_ids-'.$SuppliersOrganizationsReferentType,'value' => ''));
echo '</div>';
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#master_user_id-<?php echo $SuppliersOrganizationsReferentType;?>').click(function() {
		jQuery("#master_user_id-<?php echo $SuppliersOrganizationsReferentType;?> option:selected" ).each(function (){			
			jQuery('#referent_user_id-<?php echo $SuppliersOrganizationsReferentType;?>').append(jQuery("<option></option>")
	         .attr("value",jQuery(this).val())
	         .text(jQuery(this).text()));
	         
	         jQuery(this).remove();
		});
	});
	
	jQuery('#referent_user_id-<?php echo $SuppliersOrganizationsReferentType;?>').click(function() {
		jQuery("#referent_user_id-<?php echo $SuppliersOrganizationsReferentType;?> option:selected" ).each(function (){			
			jQuery('#master_user_id-<?php echo $SuppliersOrganizationsReferentType;?>').append(jQuery("<option></option>")
	         .attr("value",jQuery(this).val())
	         .text(jQuery(this).text()));
	         
	         jQuery(this).remove();
		});
	});
});
</script>