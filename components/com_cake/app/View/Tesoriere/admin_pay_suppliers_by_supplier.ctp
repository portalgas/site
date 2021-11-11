<?php
echo '<div class="old-menu" style="min-height:450px;">';

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if(!isset($supplier_organization_id)) $supplier_organization_id = 0;
$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $supplier_organization_id));
$this->Html->addCrumb(__('Pays Supplier'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<h2 class="ico-users">
	<?php echo __('Pays Supplier');?>
</h2>

<script type="text/javascript">
$(document).ready(function() {

	$('.submit').css('display','none');
		
	$('#supplier_organization_id').change(function() {
		caricaSupplierOrganizations();
	});
	
	var supplier_organization_id = $('#supplier_organization_id').val();
	if(supplier_organization_id!="" && supplier_organization_id!=undefined) caricaSupplierOrganizations();
});
	
function caricaSupplierOrganizations() {
	var supplier_organization_id = $('#supplier_organization_id').val();
	if(supplier_organization_id=="" || supplier_organization_id==undefined) {
		$('#orders-result').html('');
		$('#orders-result').css('display', 'none');
		return;
	}

	$('#orders-result').html('');
	$('#orders-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
	$('#orders-result').css('display', 'block');	
	$.ajax({
		type: "get",
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_to_pay_index_by_supplier&supplier_organization_id="+supplier_organization_id+"&format=notmpl",
		data: "", 
		success: function(response) {
			$('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			$('#orders-result').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			$('#orders-result').html(textStatus);
		}
	});	
}
</script>

<?php 
echo $this->Form->create('Tesoriere', array('id'=>'formGas'));
echo '<fieldset>';

$options = array('id' => 'supplier_organization_id', 'options' => $suppliersOrganizations, 'empty' => Configure::read('option.empty'), 'default'=> $supplier_organization_id, 'escape' => false);
if(count($suppliersOrganizations) > Configure::read('HtmlSelectWithSearchNum'))
	$options += array('class'=> 'selectpicker', 'data-live-search' => true);
echo $this->Form->input('supplier_organization_id',$options);
	
echo '<div id="orders-result" style="display:block;min-height:50px;"></div>';

echo $this->Form->end(__('Submit'));

echo '</fieldset>';
echo '</div>';

echo $this->element('menuTesoriereLaterale');
?>