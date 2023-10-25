<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
// $this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Connects', 'action' => 'index', 'c_to' => 'admin/articles&a_to=index-quick'));
$this->Html->addCrumb(__('Csv Import'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo $this->Form->create('CsvImport',array('action' => 'articles_prepare', 'id'=>'formGas','enctype' => 'multipart/form-data'));
echo '<fieldset class="filter">';
echo '<legend>'.__('CsvImport').'</legend>';	

$options = ['id' => 'supplier_organization_id', 
			'options' => $ACLsuppliersOrganization, 
			'default' => $supplier_organization_id, 'escape' => false];
if(count($ACLsuppliersOrganization) > 1) 
	$options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];
if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum'))
    $options += ['class'=> 'selectpicker', 'data-live-search' => true];
echo $this->Form->input('supplier_organization_id', $options); 

if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y')
	echo $this->Form->input('category_article_id', ['id' => 'category_article_id', 'options' => $categories, 'empty' => 'Filtra per categoria','default'=>$category_article_id,'escape' => false]);
		
echo $this->Form->input('delimitatore', ['label' => 'Delimitatore', 'id' => 'delimitatore', 'value' => $delimitatore, 'style' => 'width:50px;']);

echo $this->Form->input('version', ['label' => 'Versione', 'id' => 'version', 'options' => $versions, 'value' => $version]);
		
echo $this->Form->input('Document.file1', [
	'between' => '<br />',
	'type' => 'file',
	'accept' => '.csv',
	'label' => 'Carica il file CSV da importare'
]);

echo $this->element('legendaCsvImport', ['array_um' => $array_um, 'rowsMax' => Configure::read('CsvImportRowsMaxArticles')]);

echo '</fieldset>';

echo '<div style="padding-right:75px;">';
echo $this->Form->end(['label' => __('Invia')]);
echo '</div>';
?>
<script type="text/javascript">
function settingVersion(version) {
	if(version=='COMPLETE') {
		$('#version_complete').collapse('show');
		$('#version_simple').collapse('hide');
	}
	else {
		$('#version_complete').collapse('hide');
		$('#version_simple').collapse('show');
	}
}
$(document).ready(function() {

	settingVersion('<?php echo $version;?>');
	
	$('#version').change(function() {
		settingVersion($('#version').val());
	});
	
	$('#formGas').submit(function() {
		var supplier_organization_id = $('#supplier_organization_id').val();
		if(supplier_organization_id=='' || supplier_organization_id==undefined) {
			alert("<?php echo __('jsAlertSupplierRequired');?>");
			$('#supplier_organization_id').focus();
			return false;
		}
		if($('#category_article_id').length>0) {
			var category_article_id = $('#category_article_id').val();
			if(category_article_id=='' || category_article_id==undefined) {
				alert("Devi scegliere la categoria da associare");
				$('#category_article_id').focus();
				return false;
			}
		}
		var delimitatore = $('#delimitatore').val();
		if(delimitatore=='' || delimitatore==undefined) {
			alert("Devi scegliere il delimitatore dei valori nel file .csv");
			$('#delimitatore').focus();
			return false;
		}		
		return true;
	});
});
</script>	