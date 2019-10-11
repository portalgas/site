<?php
/*
 * T O - S U P L I E R S
* Documento con elenco diviso per produttore (per fattura al produttore)
*/

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

// define table cells
$table[] =	array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' =>  __('Supplier'), 'width' => 'auto');
$table[] =	array('label' =>  "Data di inizio dell'ordine", 'width' => 'auto');
$table[] =	array('label' =>  "Data di chiusura dell'ordine", 'width' => 'auto');
$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => false);
		
// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));


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
	
	$rows = [];
	$rows[] = ($numResult+1);
	$rows[] = $result['SupplierOrganization']['name'];
	$rows[] = $data_inizio;
	$rows[] = $data_fine;
	$rows[] = number_format($result[0]['totImporto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	$this->PhpExcel->addTableRow($rows);
	
	$totImporto = ($totImporto + $result[0]['totImporto']);
}

$rows = [];
$rows[] = '';
$rows[] = '';
$rows[] = '';
$rows[] = '';
$rows[] = number_format($totImporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

$this->PhpExcel->addTableRow($rows);
	    
$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>