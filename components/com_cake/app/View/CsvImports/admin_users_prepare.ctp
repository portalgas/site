<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Users'), array('controller' => 'Users', 'action' => 'index'));
$this->Html->addCrumb(__('Csv Import'), array('controller' => 'CsvImports', 'action' => 'users'));
$this->Html->addCrumb(__('Csv Import Prepare'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo $this->Form->create('CsvImport',array('id'=>'formGas', 'action' => 'todefined'));
echo '<fieldset class="filter">';
echo '<legend>'.__('Csv Import Prepare').'</legend>';

echo $this->Form->hidden('delimitatore',array('value' => $delimitatore));
echo $this->Form->hidden('password_default',array('value' => $password_default));

echo '</fieldset>';

echo $this->element('csv_import_prepare', array('struttura_file' => $struttura_file, 'results' => $results, 'totRowsConsentiti' => Configure::read('CsvImportRowsMaxUsers')));
?>
<script type="text/javascript">
$(document).ready(function() {
	$('#esito_ok').click(function() {	

		var action = $('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'users_insert';

		$('#formGas').attr('action',action);
	});
	$('#esito_ko').click(function() {	
		
		var action = $('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'users';
		
		$('#formGas').attr('action',action);
	});
});
</script>		