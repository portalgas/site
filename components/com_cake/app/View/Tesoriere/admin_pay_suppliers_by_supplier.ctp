<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
if(!isset($supplier_organization_id)) $supplier_organization_id = 0;
$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $supplier_organization_id));
$this->Html->addCrumb(__('Pay Suppliers'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<h2 class="ico-users">
	<?php echo __('Pays Supplier');?>
</h2>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.submit').css('display','none');
		
	jQuery('#supplier_organization_id').change(function() {
		caricaSupplierOrganizations();
	});
	
	var supplier_organization_id = jQuery('#supplier_organization_id').val();
	if(supplier_organization_id!="" && supplier_organization_id!=undefined) caricaSupplierOrganizations();
});
	
function caricaSupplierOrganizations() {
	var supplier_organization_id = jQuery('#supplier_organization_id').val();
	if(supplier_organization_id=="" || supplier_organization_id==undefined) {
		jQuery('#orders-result').html('');
		jQuery('#orders-result').css('display', 'none');
		return;
	}

	jQuery('#orders-result').html('');
	jQuery('#orders-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
	jQuery('#orders-result').css('display', 'block');	
	jQuery.ajax({
		type: "get",
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_to_pay_index_by_supplier&supplier_organization_id="+supplier_organization_id+"&format=notmpl",
		data: "", 
		success: function(response) {
			jQuery('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#orders-result').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			jQuery('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#orders-result').html(textStatus);
		}
	});	
}
</script>

<div class="tesoriere"  style="min-height:450px;">
<?php echo $this->Form->create('Tesoriere', array('id'=>'formGas'));?>
	<fieldset>
	<?php
	$options = array('id' => 'supplier_organization_id', 'options' => $suppliersOrganizations, 'empty' => Configure::read('option.empty'), 'default'=> $supplier_organization_id, 'escape' => false);
	if(count($suppliersOrganizations) > Configure::read('HtmlSelectWithSearchNum'))
		$options += array('class'=> 'selectpicker', 'data-live-search' => true);
	echo $this->Form->input('supplier_organization_id',$options);
	?>	
	
	<div id="orders-result" style="display:block;min-height:50px;"></div>

	<?php
		echo $this->Form->end();
	?>
	</fieldset>
</div>

<?php 
echo $this->element('menuTesoriereLaterale');
?>