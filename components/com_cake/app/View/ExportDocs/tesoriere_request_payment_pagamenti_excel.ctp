<?php 
/*
 * r e q u e s t - p a y m e n t 
 */

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);


$rows = array();
$rows[] = '';
$rows[] = "Rich. pagamento num.".$results['RequestPayment']['num'];
$rows[] = $this->Time->i18nFormat($results['RequestPayment']['stato_elaborazione_date'],"%A %e %B %Y");

$this->PhpExcel->addTableRow($rows);

$table = array();
$table[] =	array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] = array('label' => 'Stato', 'width' => 'auto', 'filter' => false);
$table[] =	array('label' =>  'Utente', 'width' => 'auto');
$table[] = array('label' => 'Mail', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => 'Importo dovuto', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => 'Importo richiesto', 'width' => 'auto', 'filter' => false);
//$table[] = array('label' => 'Cassa', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => 'Importo pagato', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => 'Modalità', 'width' => 'auto', 'filter' => false);
	
					
// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

$tot_importo_dovuto = 0;
$tot_importo_richiesto = 0;
$tot_importo_cash = 0;
$tot_importo_pagato = 0;
foreach($results['SummaryPayment'] as $num => $summaryPayment) {

	$rows = array();
	$rows[] = ($num+1);
	$rows[] = $this->App->traslateEnum($summaryPayment['SummaryPayment']['stato']);
	$rows[] = $summaryPayment['User']['name'];
	$rows[] = $summaryPayment['User']['email'];
	$rows[] = number_format($summaryPayment['SummaryPayment']['importo_dovuto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$rows[] = number_format($summaryPayment['SummaryPayment']['importo_richiesto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	//$rows[] = number_format($summaryPayment['Cash']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$rows[] = number_format($summaryPayment['SummaryPayment']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$rows[] = $this->App->traslateEnum($summaryPayment['SummaryPayment']['modalita']);
	
	$this->PhpExcel->addTableRow($rows);
			
	$tot_importo_dovuto = ($tot_importo_dovuto + $summaryPayment['SummaryPayment']['importo_dovuto']);
	$tot_importo_richiesto = ($tot_importo_richiesto + $summaryPayment['SummaryPayment']['importo_richiesto']);
	//$tot_importo_cash = ($tot_importo_cash + $summaryPayment['Cash']['importo']);
	$tot_importo_pagato = ($tot_importo_dovuto + $summaryPayment['SummaryPayment']['importo_pagato']);
}


$rows = array();
$rows[] = '';
$rows[] = '';
$rows[] = '';
$rows[] = '';
$rows[] = number_format($tot_importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
$rows[] = number_format($tot_importo_richiesto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
//$rows[] = number_format($tot_importo_cash,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
$rows[] = number_format($tot_importo_pagato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

$this->PhpExcel->addTableRow($rows);
		    
$this->PhpExcel->addTableFooter();

$this->PhpExcel->output($fileData['fileName'].'.xlsx'); // anche x pdf
?>