<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Csv Import-Export'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="csv">';
echo '<h2 class="ico-db-add">';		
echo __('Csv Import-Export');
echo '<div class="actions-img">';			
echo '	<ul>';
echo '		<li>'.$this->Html->link(__('Csv Export-Import'), array('action' => 'articles_form_export'),array('class' => 'action actionRemove','title' => __('Csv Export-Import'))).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';

echo $this->Form->create('CsvImport', ['action' => 'articles_prepare_import', 'id'=>'formGas','enctype' => 'multipart/form-data']);
echo '<fieldset class="filter">';
echo '<legend>'.__('Csv Import-Export').'</legend>';	

$options = ['id' => 'supplier_organization_id',
 			'options' => $ACLsuppliersOrganization, 
 			'default' => $supplier_organization_id, 'escape' => false];
if(count($ACLsuppliersOrganization) > 1) 
	$options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];
if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum'))
    $options += ['class'=> 'selectpicker', 'data-live-search' => true];
echo $this->Form->input('supplier_organization_id', $options); 

echo $this->Form->input('Document.file1', [
	'between' => '<br />',
	'type' => 'file',
	'accept' => '.csv',
	'label' => 'Carica il file CSV da importare']);

echo $this->element('legendaCsvExportImport');
echo $this->element('legendaCsvImport', ['array_um' => $array_um, 'rowsMax' => Configure::read('CsvImportRowsMaxArticles'), 'viewVersionSimple' => false]);

echo '</fieldset>';

echo '<div style="padding-right:75px;">';
echo $this->Form->end(array('label' => __('Invia')));
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {

	$('#formGas').submit(function() {
		var supplier_organization_id = $('#supplier_organization_id').val();
		if(supplier_organization_id=='' || supplier_organization_id==undefined) {
			alert("<?php echo __('jsAlertSupplierRequired');?>");
			$('#supplier_organization_id').focus();
			return false;
		}
		return true;
	});
});
</script>	