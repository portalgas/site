<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Csv Import'), array('controller' => 'CsvImports', 'action' => 'articles'));
$this->Html->addCrumb(__('Csv Import Prepare'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo $this->Form->create('CsvImport', ['id'=>'formGas', 'action' => 'todefined']);
echo '<fieldset class="filter">';
echo '<legend>'.__('Csv Import Prepare').'</legend>';

echo $this->Form->hidden('supplier_organization_id',  ['value' => $supplier_organization_id]);
echo $this->Form->hidden('category_article_id', ['value' => $category_article_id]);
echo $this->Form->hidden('deliminatore', ['value' => $deliminatore]);
echo $this->Form->hidden('version', ['value' => $version]);
echo '</fieldset>';

echo $this->element('csv_import_prepare', ['struttura_file' => $struttura_file, 'results' => $results, 'version' => $version, 'totRowsConsentiti' => Configure::read('CsvImportRowsMaxArticles')]);
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
	
	$('#esito_ok').click(function() {	

		var action = $('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'articles_insert';

		$('#formGas').attr('action',action);
	});
	$('#esito_ko').click(function() {	
		
		var action = $('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'articles';
		
		$('#formGas').attr('action',action);
	});
});
</script>		