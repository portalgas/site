<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List').' '.$userGroups[$group_id]['name'], array('controller' => 'SuppliersOrganizationsReferents', 'action' => 'index', null, 'group_id='.$group_id));
$this->Html->addCrumb(__('Gest').' '.$userGroups[$group_id]['name']);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="supplierOrganizationReferents form">';
echo $this->Form->create('SuppliersOrganizationsReferent', array('id' => 'formGas'));

	echo '<fieldset>';
	echo '<legend>';
	echo __('Gest').' '.$userGroups[$group_id]['name'];
	echo '</legend>';

		$options =  array('id' => 'supplier_organization_id', 'onChange' => 'javascript:choiceSupplierOrganization(this);');
		if(!empty($supplier_organization_id) && $supplier_organization_id>0)
			$options += array('default' => $supplier_organization_id);
		else
			$options += array('empty' => Configure::read('option.empty'));
		$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
			
		echo $this->Form->input('supplier_organization_id',$options);
		
		// echo $this->App->drawFormRadio('SuppliersOrganizationsReferent','type',array('options' => $types, 'value'=>'REFERENTE', 'label'=>__('Type'), 'required'=>'required'));
		
		foreach ($types as $type => $value)
			echo '<div id="users-'.$type.'" style="min-height:50px"></div>';
		
	echo '</fieldset>';

echo $this->Form->hidden('group_id',array('id' => 'group_id','value' => $group_id));
echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List').' '.$userGroups[$group_id]['name'], array('action' => 'index', null, 'group_id='.$group_id),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
function choiceSupplierOrganization() {
	var supplier_organization_id = jQuery("#supplier_organization_id").val();	
	var group_id = jQuery("#group_id").val();	
	
	if(supplier_organization_id=='') {
		<?php
		foreach ($types as $type => $value) {
			echo "jQuery('#users-".$type."').hide();";
			echo "\r\n";
			echo "jQuery('#users-".$type."').html();";
			echo "\r\n";
		}
		?>
		return;
	}

	<?php
	foreach ($types as $type => $value) {
		echo "AjaxCallToReferents(supplier_organization_id, group_id, '".$type."');";
		echo "\r\n";
	}
	?>
}

function AjaxCallToReferents(supplier_organization_id, group_id, type) {	

	var url = "/administrator/index.php?option=com_cake&controller=SuppliersOrganizationsReferents&action=ajax_box_users&supplier_organization_id="+supplier_organization_id+"&group_id="+group_id+"&type="+type+"&format=notmpl";
	var urlAjax = 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent'; 
	
	jQuery('#users-'+type).show();
	jQuery('#users-'+type).html('');
	jQuery('#users-'+type).css('background', urlAjax);	
	
	jQuery.ajax({
		type: "get", 
		url: url,
		data: "", 
		success: function(response) {
			jQuery('#users-'+type).css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#users-'+type).html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			jQuery('#users-'+type).css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#users-'+type).html(textStatus);
		}
	});	
}	
<?php
if(!empty($supplier_organization_id)) echo 'choiceSupplierOrganization();';
?>

jQuery(document).ready(function() {
	jQuery('#formGas').submit(function() {

		<?php
		foreach ($types as $type => $value) {
		?>
			var referent_user_ids_<?php echo $type;?> = '';
			jQuery("#referent_user_id-<?php echo $type;?> option" ).each(function (){	
				referent_user_ids_<?php echo $type;?> +=  jQuery(this).val()+',';
			});
			referent_user_ids_<?php echo $type;?> = referent_user_ids_<?php echo $type;?>.substring(0,referent_user_ids_<?php echo $type;?>.length-1);
		
		<?php
		}
		?>

		if(
			<?php
			$tmp = '';
			foreach ($types as $type => $value) 
				$tmp .= "referent_user_ids_".$type."=='' && ";
			
			$tmp = substr($tmp, 0, strlen($tmp)-3);
			
			echo $tmp;
			?>
		) {
			alert("Devi selezionare almeno un utente da associare al gruppo <?php echo $userGroups[$group_id]['name'];?>");
			return false;
		}

		<?php
		foreach ($types as $type => $value) {
			echo "jQuery('#referent_user_ids-".$type."').val();";
			echo "\r\n";
			echo "jQuery('#referent_user_ids-".$type."').val(referent_user_ids_".$type.");";
			echo "\r\n";
		}
		?>

		return true;
	});	
});
</script>