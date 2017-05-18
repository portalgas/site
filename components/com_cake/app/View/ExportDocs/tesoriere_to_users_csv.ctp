<?php 
/*
 * T O - U S E R S
 * Documento con elenco diviso per utente (per pagamento dell'utente)
 */


$headers = array('csv' => array(
		'N' => 'N',
		'utente' => 'Utente',
		'importo' => __('Importo')
	)
);


$data = "";

$tot_importo = 0;
foreach($results as $numResult => $result) {
	
	$tot_importo_user = $result[0]['totImporto'];
	
	$data[]['csv'] = array(
			'N' => ($numResult+1),
			'utente' => $result['User']['name'],
			'importo' => number_format($result['SummaryOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'))
	);
	
	$tot_importo = ($tot_importo + $result['SummaryOrder']['importo']);
}

$data[]['csv'] = array(
		'N' => '',
		'utente' => '',
		'importo' => number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'))
);


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