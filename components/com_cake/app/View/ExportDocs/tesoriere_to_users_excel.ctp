<?php 
/*
 * T O - U S E R S
 * Documento con elenco diviso per utente (per pagamento dell'utente)
 */

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);


// define table cells


$table[] =	array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' =>  'Utente', 'width' => 'auto');
$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => false);
		
// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

$tot_importo = 0;
foreach($results as $numResult => $result) {

	$rows = array();
	$rows[] = ($numResult+1);
	$rows[] = $result['User']['name'];
	$rows[] = number_format($result['SummaryOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	$this->PhpExcel->addTableRow($rows);
			
	$tot_importo = ($tot_importo + $result['SummaryOrder']['importo']);
}

$rows = array();
$rows[] = '';
$rows[] = '';
$rows[] = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

$this->PhpExcel->addTableRow($rows);
		    
$this->PhpExcel->addTableFooter();

$this->PhpExcel->output($fileData['fileName'].'.xlsx'); // anche x pdf
?>