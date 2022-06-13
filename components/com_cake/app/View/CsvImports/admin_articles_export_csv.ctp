<?php 
/*
 * header
 */
$csv = []; 
foreach($struttura_file as $field) {
	$csv[$field['INPUT_NAME']] = $this->ExportDocs->prepareCsvAccenti($field['LABEL']);
} 			
$headers = ['csv' => $csv];

$this->App->d($results);

/*
 * body
 */
$data = [];
if(isset($results) && !empty($results)) {
	foreach($results as $numResult => $result) {
		foreach($struttura_file as $numField => $field) {
			if($numField==0)
				$data[$numResult]['csv'] = [];
			
			/*
			 * se esiste {prezzo}_ considero quello perche' ha il decimale 
			 */
			if(isset($result['Article'][$field['INPUT_NAME'].'_'])) {
				$value = $result['Article'][$field['INPUT_NAME'].'_'];
				$value = str_replace('.', '', $value);
			}
			else
				$value = $result['Article'][$field['INPUT_NAME']];
			
			$value = str_replace('"', "'", $value);
			$value = str_replace(["\n","\r"], "", $value);

            $value = htmlentities($value); // converto eventuale HTML (ex ingredienti) se no in mimetype e' text\html

            $data[$numResult]['csv'] += [$field['INPUT_NAME'] => $value];
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