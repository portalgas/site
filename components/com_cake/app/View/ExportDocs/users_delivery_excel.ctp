<?php 
/*
 * Elenco utenti presenti ad una data consegna
*/
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

// define table cells
$table = array(
		array('label' => __('N'), 'width' => 'auto'),
		array('label' => __('Code'), 'width' => 'auto', 'wrap' => true, 'filter' => false),
		array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false),
		array('label' => __('Mail'), 'width' => 'auto', 'wrap' => true, 'filter' => false),
		array('label' => __('Telephone'), 'width' => 'auto', 'wrap' => true, 'filter' => false),
		array('label' => __('Address'), 'width' => 'auto', 'wrap' => true, 'filter' => false),
		array('label' => 'Qta totale', 'width' => 'auto', 'wrap' => true, 'filter' => true),
		array('label' => __('Suppliers Organizations Referents'), 'width' => 100, 'filter' => false),		
);

// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

foreach($results as $numResult => $result) {

	$data = [];
	
	$telephone = "";
	if(!empty($result['Profile']['phone'])) $telephone .= $result['Profile']['phone'].' ';
	if(!empty($result['Profile']['phone2'])) $telephone .= $result['Profile']['phone2'];
	
	$address = "";
	if(!empty($result['Profile']['address'])) $address = $result['Profile']['address'];	
	
	$data = array(			($numResult+1),			$result['Profile']['codice'],			$result['User']['name'],			$result['User']['email'],			$telephone,			$address,
			$result['User']['cart_qta_tot']	);
	
	if(isset($result['SuppliersOrganization'])) 		foreach($result['SuppliersOrganization'] as $numSuppliersOrganization => $suppliersOrganization) 			$data[] = $suppliersOrganization['name'].' '.$result['SuppliersOrganizationsReferent'][$numSuppliersOrganization]['type'];
	
	$this->PhpExcel->addTableRow($data);
}

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>