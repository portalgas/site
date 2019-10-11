<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesUserGroupMaps'),array('controller' => 'DesUserGroupMaps', 'action' => 'intro'));
$this->Html->addCrumb(__('List').' '.$userGroups[$group_id]['name'], array('controller' => 'DesSuppliersReferents', 'action' => 'index', null, 'group_id='.$group_id));
$this->Html->addCrumb(__('Gest').' '.$userGroups[$group_id]['name']);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="desSupplierReferents form">';
echo $this->Form->create('DesSuppliersReferent', array('id' => 'formGas'));

	echo '<fieldset style="min-height:600px;">';
	echo '<legend>';
	echo __('Gest').' '.$userGroups[$group_id]['name'];
	echo '</legend>';

		$options =  array('id' => 'des_supplier_id', 'label' => __('DesSupplier'), 'onChange' => 'javascript:choiceDesSupplier(this);');
		if(!empty($des_supplier_id) && $des_supplier_id>0)
			$options += array('default' => $des_supplier_id);
		else
			$options += array('empty' => Configure::read('option.empty'));
			
		$options += array('options' => $ACLdesSuppliers);
		if(count($ACLdesSuppliers) > Configure::read('HtmlSelectWithSearchNum')) 
			$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
			
		echo $this->Form->input('des_supplier_id',$options);
		
		echo '<div id="users" style="min-height:50px"></div>';
		
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
function choiceDesSupplier() {
	var des_supplier_id = $("#des_supplier_id").val();	
	var group_id = $("#group_id").val();	
	
	if(des_supplier_id=='') {
		<?php
			echo "$('#users').hide();";
			echo "\r\n";
			echo "$('#users').html();";
			echo "\r\n";
		?>
		return;
	}

	<?php
	echo "AjaxCallToReferents(des_supplier_id, group_id);";
	echo "\r\n";
	?>
}

function AjaxCallToReferents(des_supplier_id, group_id) {	

	var url = "/administrator/index.php?option=com_cake&controller=DesSuppliersReferents&action=ajax_box_users&des_supplier_id="+des_supplier_id+"&group_id="+group_id+"&format=notmpl";
	var urlAjax = 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent'; 
	
	$('#users').show();
	$('#users').html('');
	$('#users').css('background', urlAjax);	
	
	$.ajax({
		type: "get", 
		url: url,
		data: "", 
		success: function(response) {
			$('#users').css('background', 'none repeat scroll 0 0 transparent');
			$('#users').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$('#users').css('background', 'none repeat scroll 0 0 transparent');
			$('#users').html(textStatus);
		}
	});	
}	
<?php
if(!empty($des_supplier_id)) echo 'choiceDesSupplier();';
?>

$(document).ready(function() {
	$('#formGas').submit(function() {

		var referent_user_ids = '';
		$("#referent_user_id option" ).each(function (){	
			referent_user_ids +=  $(this).val()+',';
		});
		referent_user_ids = referent_user_ids.substring(0,referent_user_ids.length-1);


		if(
			<?php
			$tmp = '';
			$tmp .= "referent_user_ids=='' && ";
			
			$tmp = substr($tmp, 0, strlen($tmp)-3);
			
			echo $tmp;
			?>
		) {
			alert("Devi selezionare almeno un utente da associare al gruppo <?php echo $userGroups[$group_id]['name'];?>");
			return false;
		}

		<?php
		echo "$('#referent_user_ids').val();";
			echo "\r\n";
			echo "$('#referent_user_ids').val(referent_user_ids);";
			echo "\r\n";
		?>

		return true;
	});	
});
</script>