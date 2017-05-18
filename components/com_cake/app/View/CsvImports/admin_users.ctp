<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Users'), array('controller' => 'Users', 'action' => 'index'));
$this->Html->addCrumb(__('Csv Import'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo $this->Form->create('CsvImport',array('action' => 'users_prepare', 'id'=>'formGas','enctype' => 'multipart/form-data'));
echo '<fieldset class="filter">';
echo '<legend>'.__('CsvImport').'</legend>';	

echo $this->Form->input('password_default', array('id' => 'password_default', 'value' => '', 'style' => 'width:150px;'));

echo $this->Form->input('deliminatore', array('label' => 'Delimitatore', 'id' => 'deliminatore', 'value' => $deliminatore, 'style' => 'width:50px;'));
		
echo $this->Form->input('Document.file1', array(
	'between' => '<br />',
	'type' => 'file',
	'label' => 'Carica il file CSV da importare'
));

echo $this->element('legendaCsvImport', array('rowsMax' => Configure::read('CsvImportRowsMaxUsers')));

echo '</fieldset>';

echo $this->Form->end(array('label' => __('Invia')));
?>
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#formGas').submit(function() {
		
		var password_default = jQuery('#password_default').val();
		if(password_default=='' || password_default==undefined) {
			alert("Devi scegliere la password di default da impostare per tutti gli utenti");
			jQuery('#password_default').focus();
			return false;
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