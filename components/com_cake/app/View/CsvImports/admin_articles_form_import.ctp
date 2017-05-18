<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
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

echo $this->Form->create('CsvImport',array('action' => 'articles_prepare_import', 'id'=>'formGas','enctype' => 'multipart/form-data'));
echo '<fieldset class="filter">';
echo '<legend>'.__('Csv Import-Export').'</legend>';	

$options = array('id'=>'supplier_organization_id', 'options' => $ACLsuppliersOrganization,'empty' => 'Filtra per produttore', 'default' => $supplier_organization_id, 'escape' => false);
if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum'))
    $options += array('class'=> 'selectpicker', 'data-live-search' => true);
echo $this->Form->input('supplier_organization_id', $options); 

echo $this->Form->input('Document.file1', array(
	'between' => '<br />',
	'type' => 'file',
	'label' => 'Carica il file CSV da importare'
));

echo $this->element('legendaCsvExportImport');
echo $this->element('legendaCsvImport', array('array_um' => $array_um, 'rowsMax' => Configure::read('CsvImportRowsMaxArticles')));

echo '</fieldset>';

echo $this->Form->end(array('label' => __('Invia')));
?>
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#formGas').submit(function() {
		var supplier_organization_id = jQuery('#supplier_organization_id').val();
		if(supplier_organization_id=='' || supplier_organization_id==undefined) {
			alert("<?php echo __('jsAlertSupplierRequired');?>");
			jQuery('#supplier_organization_id').focus();
			return false;
		}
		return true;
	});
});
</script>	