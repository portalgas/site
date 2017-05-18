<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Users'), array('controller' => 'Users', 'action' => 'index'));
$this->Html->addCrumb(__('Csv Import'), array('controller' => 'CsvImports', 'action' => 'users'));
$this->Html->addCrumb(__('Csv Import Prepare'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo $this->Form->create('CsvImport',array('id'=>'formGas', 'action' => 'todefined'));
echo '<fieldset class="filter">';
echo '<legend>'.__('Csv Import Prepare').'</legend>';

echo $this->Form->hidden('deliminatore',array('value' => $deliminatore));
echo $this->Form->hidden('password_default',array('value' => $password_default));

echo '</fieldset>';

echo $this->element('csv_import_prepare', array('struttura_file' => $struttura_file, 'results' => $results, 'totRowsConsentiti' => Configure::read('CsvImportRowsMaxUsers')));
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#esito_ok').click(function() {	

		var action = jQuery('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'users_insert';

		jQuery('#formGas').attr('action',action);
	});
	jQuery('#esito_ko').click(function() {	
		
		var action = jQuery('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'users';
		
		jQuery('#formGas').attr('action',action);
	});
});
</script>		