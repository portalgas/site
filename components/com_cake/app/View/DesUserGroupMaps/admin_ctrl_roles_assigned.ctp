<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('List DesUserGroupMaps'),array('controller' => 'DesUserGroupMaps', 'action' => 'intro'));
$this->Html->addCrumb(__('Ctrl DesUserGroupMaps'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));


echo '<div class="users">';
	
echo '<h2 class="ico-users">';
echo __('Ctrl DesUserGroupMaps');
echo '</h2>';

/* 
 *   loop DesSupplier
 */
if(!empty($results)) {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th></th>';
	echo '<th colspan="2">'.__('DesSuppliers').'</th>';
	echo '</tr>';

	foreach ($results as $numResult => $result) {

		echo '<tr class="view-2">';
		echo '<td>'.($numResult+1).'</td>';
		echo '<td><input type="radio" name="data[DesSupplier][id]" value="'.$result['DesSupplier']['id'].'" /></td>';
		
		echo '<td>';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo '<img width="50" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
		echo '</td>';			
		echo '<td>'.$result['Supplier']['name'];
		if(!empty($result['Supplier']['descrizione']))
			echo ' - '.$result['Supplier']['descrizione'];
		echo '</td>';

		echo '</tr>';
		
		echo '<tr>';
		echo '<td></td>';
		echo '<td colspan="3" class="details_users" id="details_users-'.$result['DesSupplier']['id'].'"></td>';
		echo '</tr>';
		
	} // loop DesSupplier
	echo '</table></div>';
} 
else
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non sei abilitato su alcun produttore!"));

echo '</div>';
?>

<script type="text/javascript">
$(document).ready(function() {
	$("input[name='data[DesSupplier][id]']").change(function() {	
		
		var des_supplier_id = $(this).val();

		$('.details_users').html("");
		$('.details_users').css('display', 'none');
				
		$('#details_users-'+des_supplier_id).css('min-height', '50px');
		$('#details_users-'+des_supplier_id).css('display', 'table-cell');
		$('#details_users-'+des_supplier_id).css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
		
		$.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=DesUserGroupMaps&action=ctrl_roles_assigned_details_users&des_supplier_id="+des_supplier_id+"&format=notmpl",
			data: "",
			success: function(response) {
				$('#details_users-'+des_supplier_id).css('background', 'none repeat scroll 0 0 transparent');
				 $('#details_users-'+des_supplier_id).html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$('#details_users-'+des_supplier_id).css('background', 'none repeat scroll 0 0 transparent');
				$('#details_users-'+des_supplier_id).html(textStatus);
			}
		});
			
		return false;
	});
	
});
</script>