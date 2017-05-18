<style type="text/css">
.esito_no {
	background-color:red;
}
.esito_ok {
	background-color:green;
}
span.esito_no {
	margin-left:25px;
	padding:2px;
	color:#fff;
}
</style>
<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/

if(!empty($results)) {
	echo '<table>';
	echo '<tr>';
	echo '<th></th>';
	for ($i=0; $i < count($struttura_file); $i++)
		echo '<th>'.$struttura_file[$i]['LABEL'].'</th>';
	echo '</tr>';
	
	$tot_error=0;
	foreach($results as $numResult => $result) {
		echo '<tr>';
		echo '<td ';
		if($result['ESITO']=='KO') {
			echo ' class="esito_no"';
			$tot_error++;
		}
		else echo ' class="esito_ok"';
		echo '></td>';
		
		foreach($result['Row'] as $row) {
			echo '<td>';
			
			echo '<input type="hidden" name="data[CsvImport]['.$numResult.']['.$row['INPUT_NAME'].']" value="'.$row['VALUE'].'" />';
	
			echo $row['VALUE'];
			if($row['ESITO']!='OK') echo '<span class="esito_no">'.$this->App->traslateEnum($row['ESITO']).'</span>';
			echo '</td>';
		}
		echo '</tr>';	
	}
	echo '</table>';
} // end if(!empty($results))

if($tot_error==0 && !empty($results)) {

	echo '<br />';
	$msg = "Il dati del file sono corretti: procedere con l'inserimento";
	echo $this->element('boxMsg', array('class_msg' => 'success', 'msg' => $msg));
	
	echo $this->Form->submit(__('Invia'),array('id' => 'esito_ok'));
}
else {
	if($totRows > $totRowsConsentiti) {
		$msg = sprintf(__('cvs_import_error_rows_max'), $totRows, $totRowsConsentiti);
		echo $this->element('boxMsg', array('class_msg' => 'notice nomargin', 'msg' => $msg));
	}	
	else {
		if($tot_error==1)
			$msg = sprintf(__('cvs_import_error_1'), $tot_error);
		else
			$msg = sprintf(__('cvs_import_error_n'), $tot_error);
		
		echo $this->element('legendaCsvImport', array('msg' => $msg, 'array_um' => $array_um, 'rowsMax' => Configure::read('CsvImportRowsMaxArticles')));
	}
	
	echo $this->Form->submit(__('Back'),array('id' => 'esito_ko'));
}
echo $this->Form->end();
?>