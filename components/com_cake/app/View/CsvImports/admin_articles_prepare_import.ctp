<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Csv Import Prepare'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="csv">';
echo '<h2 class="ico-db-remove">';		
echo __('Csv Export-Import');
echo '<div class="actions-img">';			
echo '	<ul>';
echo '		<li>'.$this->Html->link(__('Csv Import-Export'), array('action' => 'articles_form_import'),array('class' => 'action actionAdd','title' => __('Csv Import-Export'))).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';

echo $this->Form->create('CsvImport',array('id'=>'formGas', 'action' => 'todefined'));
echo '<fieldset class="filter">';
echo '<legend>'.__('Csv Import Prepare').'</legend>';

echo $this->Form->hidden('supplier_organization_id',array('value' => $supplier_organization_id));
echo '</fieldset>';

echo $this->element('csv_import_prepare', array('struttura_file' => $struttura_file, 'results' => $results, 'totRowsConsentiti' => Configure::read('CsvImportRowsMaxArticles')));
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#esito_ok').click(function() {	

		var action = jQuery('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'articles_import';

		jQuery('#formGas').attr('action',action);
	});
	jQuery('#esito_ko').click(function() {	
		
		var action = jQuery('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'articles_form_import';
		
		jQuery('#formGas').attr('action',action);
	});
});
</script>		