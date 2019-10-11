<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('List MonitoringSuppliersOrganizations'), array('controller' => 'MonitoringSuppliersOrganizations', 'action' => 'home'));
$this->Html->addCrumb(__('Gest MonitoringSuppliersOrganizations'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="monitoring-orders form">';
?>

<h2 class="ico-monitoring-orders">
	<?php echo __('Monitoring Suppliers Organizations');?>
</h2>

<script type="text/javascript">
$(document).ready(function() {

	$('.submit').css('display','none');
		
	$('#supplier_organization_id').change(function() {
		caricaSuppliersOrganizations();
	});
	
	var supplier_organization_id = $('#supplier_organization_id').val();
	if(supplier_organization_id!="" && supplier_organization_id!=undefined) caricaSuppliersOrganizations();
});
	
function caricaSuppliersOrganizations() {
	var supplier_organization_id = $('#supplier_organization_id').val();
	if(supplier_organization_id=="" || supplier_organization_id==undefined) {
		$('#supplier-organizations-results').html('');
		$('#supplier-organizations-results').css('display', 'none');
		
		$('.submit').css('display','none');
		
		return;
	}

	$('#supplier-organizations-results').html('');
	$('#supplier-organizations-results').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
	$('#supplier-organizations-results').css('display', 'block');	
	$.ajax({
		type: "get",
		url: "/administrator/index.php?option=com_cake&controller=MonitoringSuppliersOrganizations&action=suppliers_organizations_index&supplier_organization_id="+supplier_organization_id+"&format=notmpl",
		data: "", 
		success: function(response) {
			$('#supplier-organizations-results').css('background', 'none repeat scroll 0 0 transparent');
			$('#supplier-organizations-results').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$('#supplier-organizations-results').css('background', 'none repeat scroll 0 0 transparent');
			$('#supplier-organizations-results').html(textStatus);
		}
	});	
}
</script>

<div class="legenda  legenda-ico-info">
Scegli per quale produttori monitorare gli ordini
</div>

<?php echo $this->Form->create('MonitoringSuppliersOrganization', array('id'=>'formGas'));?>
	<fieldset style="min-height:300px;">
	<?php
	$options = ['label' => false, 
				'options' => $ACLsuppliersOrganization,
				'id' => 'supplier_organization_id',
				'default'=>$FilterArticleSupplierId,'escape' => false];
	if(count($ACLsuppliersOrganization) > 1) 
		$options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];				
	if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
		$options += ['class'=> 'selectpicker', 'data-live-search' => true];
	echo $this->Form->input('supplier_organization_id',$options);						
	?>	
	
	<div id="supplier-organizations-results" style="display:block;min-height:50px;"></div>

	<?php
		echo $this->Form->end(__('Submit'));
	?>
	</fieldset>


<?php
echo '</div>';

echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List MonitoringSuppliersOrganizations'), array('controller' => 'MonitoringSuppliersOrganizations', 'action' => 'home'),array('class'=>'action actionReload')).'</li>';
echo '</ul>';
echo '</div>';
?>