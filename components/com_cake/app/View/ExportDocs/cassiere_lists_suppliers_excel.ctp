<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

$rowsExcel[] = __('Delivery');
if($resultDelivery['Delivery']['sys']=='N')
	$rowsExcel[] = $resultDelivery['Delivery']['luogoData'];
else
	$rowsExcel[] = $resultDelivery['Delivery']['luogo'];
$this->PhpExcel->addTableRow($rowsExcel);

// define table cells
$table[] =	array('label' => __('N.'), 'width' => 'auto');
$table[] =	array('label' => __('Supplier'), 'width' => 'auto');
$table[] = array('label' => __('Importo_dovuto'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Importo_pagato'), 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('Delta'), 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('Utenti che devono pagare'), 'width' => 'auto', 'filter' => true);

// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

$this->App->d($results);

if(!empty($results)) {

	foreach ($results as $numResult => $result) {

		$rowsExcel = [];
		$rowsExcel[] = ($numResult+1);
		$rowsExcel[] = $result['SuppliersOrganization']['name'];
		$rowsExcel[] = $result['Order']['tot_importo'];
		$rowsExcel[] = $result['Order']['tot_importo_pagato'];
		$rowsExcel[] = $result['Order']['tot_differenza'];
		$rowsExcel[] = $result['Order']['totUserToTesoriere'];

		$this->PhpExcel->addTableRow($rowsExcel);
			
	} // loop orders
			
	/*
	 * totale consegna
	 */
	
	if(!empty($deliveryResults)) {
		
		$rowsExcel = [];
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = $deliveryResults['tot_importo_delivery'];
		$rowsExcel[] = $deliveryResults['tot_importo_pagato_delivery'];
		$rowsExcel[] = $deliveryResults['tot_differenza_delivery'];

		$this->PhpExcel->addTableRow($rowsExcel);		
	}
}

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>