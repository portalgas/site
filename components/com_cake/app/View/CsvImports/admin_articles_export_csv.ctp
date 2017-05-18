<?php 
/*
 * header
 */
$csv = array(); 
foreach($struttura_file as $field) {
	$csv[$field['INPUT_NAME']] = $this->ExportDocs->prepareCsvAccenti($field['LABEL']);
} 			
$headers = array('csv' => $csv);

/*
 * body
 */
$data = array();
if(isset($results) && !empty($results)) {
	foreach($results as $numResult => $result) {
		foreach($struttura_file as $numField => $field) {
			if($numField==0)
				$data[$numResult]['csv'] = array();
			
			$data[$numResult]['csv'] += array($field['INPUT_NAME'] => $result['Article'][$field['INPUT_NAME']]);
		}
	}
}
array_unshift($data,$headers);


foreach ($data as $row)
{
	foreach ($row['csv'] as &$value) {
		// Apply opening and closing text delimiters to every value
		$value = "\"".$value."\"";
	}
	// Echo all values in a row comma separated
	echo implode(",",$row['csv'])."\n";
}
?>