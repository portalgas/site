<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Csv Import'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo $this->Form->create('CsvImport',array('action' => 'articles_prepare', 'id'=>'formGas','enctype' => 'multipart/form-data'));
echo '<fieldset class="filter">';
echo '<legend>'.__('CsvImport').'</legend>';	

$options = array('id'=>'supplier_organization_id', 'options' => $ACLsuppliersOrganization,'empty' => 'Filtra per produttore', 'default' => $supplier_organization_id, 'escape' => false);
if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum'))
    $options += array('class'=> 'selectpicker', 'data-live-search' => true);
echo $this->Form->input('supplier_organization_id', $options); 

if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y')
	echo $this->Form->input('category_article_id', array('id' => 'category_article_id', 'options' => $categories, 'empty' => 'Filtra per categoria','default'=>$category_article_id,'escape' => false));
		
echo $this->Form->input('deliminatore', array('label' => 'Delimitatore', 'id' => 'deliminatore', 'value' => $deliminatore, 'style' => 'width:50px;'));
		
echo $this->Form->input('Document.file1', array(
	'between' => '<br />',
	'type' => 'file',
	'label' => 'Carica il file CSV da importare'
));

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
		if(jQuery('#category_article_id').length>0) {
			var category_article_id = jQuery('#category_article_id').val();
			if(category_article_id=='' || category_article_id==undefined) {
				alert("Devi scegliere la categoria da associare");
				jQuery('#category_article_id').focus();
				return false;
			}
		}
		var deliminatore = jQuery('#deliminatore').val();
		if(deliminatore=='' || deliminatore==undefined) {
			alert("Devi scegliere il deliminatore dei valori nel file .csv");
			jQuery('#deliminatore').focus();
			return false;
		}		
		return true;
	});
});
</script>	