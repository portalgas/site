<?php
//$this->App::d($results); 
/*
 * Elenco utenti presenti ad una data consegna
*/
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

// define table cells
$table = [
		['label' => __('N'), 'width' => 'auto'],
		['label' => __('Code'), 'width' => 'auto', 'wrap' => true, 'filter' => false],
		['label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false],
		['label' => __('Telephone'), 'width' => 'auto', 'wrap' => true, 'filter' => false]
];

foreach($supplier_organizations as $supplier_organization_name) {
	array_push($table, ['label' => $supplier_organization_name, 'width' => 'auto', 'wrap' => true, 'filter' => true]);
}


// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

foreach($results as $numResult => $result) {

	$data = [];
	
	$telephone = "";
	if(!empty($result['Profile']['phone'])) $telephone .= $result['Profile']['phone'].' ';
	if(!empty($result['Profile']['phone2'])) $telephone .= $result['Profile']['phone2'];
	
	$data = [
			((int)$numResult+1),
			$result['Profile']['codice'],
			$result['User']['name'],
			$telephone
	];

	foreach($supplier_organizations as $supplier_organization_id => $supplier_organization_name) {
		if(isset($result['Order'][$supplier_organization_id]))
			array_push($data, $result['Order'][$supplier_organization_id]['tot_user_importo']);
		else 
			array_push($data, '');	
	}
	
	$this->PhpExcel->addTableRow($data);
}

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>