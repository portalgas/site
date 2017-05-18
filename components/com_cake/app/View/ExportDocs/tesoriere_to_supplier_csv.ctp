<?php
/*
 * T O - S U P L I E R S
* Documento con elenco diviso per produttore (per fattura al produttore)
*/

$headers = array('csv' => array(
		'N' => 'N',
		'supplier' => __('Supplier'),
		'data_inizio' => "Data di inizio dell'ordine",
		'data_fine' => "Data di chiusura dell'ordine",
		'importo' => __('Importo')
	)
);


$data = "";

$totImporto = 0;
foreach($results as $numResult => $result) {

	if($result['Order']['data_inizio']!=Configure::read('DB.field.date.empty'))
		$data_inizio = $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y");
	else 
		$data_inizio = "";

	if($result['Order']['data_fine']!=Configure::read('DB.field.date.empty'))
		$data_fine = $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");
	else
		$data_fine = "";
	
	if($result['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
		$data_fine = $this->Time->i18nFormat($result['Order']['data_fine_validation'],"%A %e %B %Y");
	
	$data[]['csv'] = array(
		'N' => ($numResult+1),
		'supplier' => $result['SupplierOrganization']['name'],
		'data_inizio' => $data_inizio,
		'data_fine' => $data_fine,
		'importo' => number_format($result[0]['totImporto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'))
	);
		
	$totImporto = ($totImporto + $result[0]['totImporto']);
}

$data[]['csv'] = array(
	'N' => '',
	'supplier' => '',
	'data_inizio' => '',
	'data_fine' => '',
	'importo' => number_format($totImporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'))
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