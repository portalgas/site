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
$this->App->d($results);
$this->App->d($struttura_file);

if(!empty($results)) {
	echo '<div class="table-responsive"><table class="table">';
	echo '<tr>';
	echo '<th></th>';
	for ($i=0; $i < count($struttura_file); $i++) {
		if($version=='COMPLETE' || $struttura_file[$i]['VERSION_SIMPLE']) 
			echo '<th>'.$struttura_file[$i]['LABEL'].'</th>';
	}
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
            if(isset($row['MSG']) && !empty($row['MSG'])) echo '<span class="esito_no">'.$row['MSG'].'</span>';
			echo '</td>';
		}
		echo '</tr>';	
	}
	echo '</table></div>';
} // end if(!empty($results))

if($tot_error==0 && !empty($results)) {

	echo '<br />';
	$msg = "Il dati del file sono corretti: procedere con l'inserimento";
	echo $this->element('boxMsg', ['class_msg' => 'success', 'msg' => $msg]);
	
	echo $this->Form->submit(__('Invia'), ['id' => 'esito_ok', 'style' => 'clear: both;']);
}
else {
	if($totRows > $totRowsConsentiti) {
		$msg = sprintf(__('cvs_import_error_rows_max'), $totRows, $totRowsConsentiti);
		echo $this->element('boxMsg', ['class_msg' => 'notice nomargin', 'msg' => $msg]);
	}	
	else {
		if($tot_error==1)
			$msg = sprintf(__('cvs_import_error_1'), $tot_error);
		else
			$msg = sprintf(__('cvs_import_error_n'), $tot_error);
		
		echo $this->element('legendaCsvImport', ['msg' => $msg, 'array_um' => $array_um, 'rowsMax' => Configure::read('CsvImportRowsMaxArticles')]);
	}
	
	echo $this->Form->submit(__('Back'), ['id' => 'esito_ko', 'style' => 'clear: both;']);
}
echo $this->Form->end();
?>