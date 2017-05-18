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

if(count($results['Order'])>0) { 

		$table = array();
		$table[] =	array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
		$table[] =	array('label' =>  __('Delivery'), 'width' => 'auto');
		$table[] = array('label' => __('Supplier'), 'width' => 'auto', 'filter' => false);
	
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

		$delivery_id_old=0;
		foreach($results['Order'] as $i => $result) {
			
			$rows = array();
			$rows[] = ($i+1);
			if($result['Delivery']['id']!=$delivery_id_old)
				$rows[] = $result['Delivery']['luogo'].', del '.$this->Time->i18nFormat($result['Delivery']['data'],"%A %e %B %Y");
			else
				$rows[] = '';
			$rows[] = $result['SuppliersOrganization']['name'];
		
			$this->PhpExcel->addTableRow($rows);
		
			$delivery_id_old = $result['Delivery']['id'];
		} // end foreach($results['Order'] as $i => $result)
		
		$rows = array();
		$rows[] = '';
		$this->PhpExcel->addTableRow($rows);		 
} 

/*
 *  storeroom
 */
if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
	if(!empty($results['Storeroom'])) { 
	
		$table = array();
		$table[] = array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
		$table[] = array('label' =>  __('Delivery'), 'width' => 'auto');

		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));
	
		foreach($results['Storeroom'] as $i => $storeroom) {

			$rows = array();
			$rows[] = ($i+1);
			$rows[] = $storeroom['Delivery']['luogo'].', di '.$this->Time->i18nFormat($storeroom['Delivery']['data'],"%A %e %B %Y");
			$rows[] = $this->App->formatDateCreatedModifier($storeroom['Delivery']['created']);
		
			$this->PhpExcel->addTableRow($rows);
		
		} // end foreach($results['Order'] as $i => $result) 
		
		$rows = array();
		$rows[] = '';
		$this->PhpExcel->addTableRow($rows);
				
	 } // end if(!empty($results['Storeroom'])) 
} // end if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y')


if(!empty($results['PaymentsGeneric'])) { 

	$table = array();
	$table[] = array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
	$table[] = array('label' =>  'Voce di spesa', 'width' => 'auto');
	$table[] = array('label' =>  __('User'), 'width' => 'auto');
	$table[] = array('label' =>  __('Importo'), 'width' => 'auto');
	$table[] = array('label' =>  _('Created'), 'width' => 'auto');

	$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

	foreach($results['PaymentsGeneric'] as $i => $requestPaymentsGeneric) {
	
			$rows = array();
			$rows[] = ($i+1);
			$rows[] = $requestPaymentsGeneric['RequestPaymentsGeneric']['name'];
			$rows[] = $requestPaymentsGeneric['User']['name'];
			$rows[] = $requestPaymentsGeneric['RequestPaymentsGeneric']['importo'];
			$rows[] = $this->Time->i18nFormat($requestPaymentsGeneric['RequestPaymentsGeneric']['created'],"%A %e %B %Y");
			
			$this->PhpExcel->addTableRow($rows);
	} 
	
	$rows = array();
	$rows[] = '';
	$this->PhpExcel->addTableRow($rows);
			
} // end if(!empty($results['PaymentsGeneric'])) 


$table = array();
$table[] =	array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' =>  'Utente', 'width' => 'auto');
$table[] = array('label' => 'Mail', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => 'Importo dovuto', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => 'Importo richiesto', 'width' => 'auto', 'filter' => false);
//$table[] = array('label' => 'Cassa', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => 'Importo pagato', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => 'Stato', 'width' => 'auto', 'filter' => false);
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
	$rows[] = $summaryPayment['User']['name'];
	$rows[] = $summaryPayment['User']['email'];
	$rows[] = number_format($summaryPayment['SummaryPayment']['importo_dovuto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$rows[] = number_format($summaryPayment['SummaryPayment']['importo_richiesto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	//$rows[] = number_format($summaryPayment['Cash']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$rows[] = number_format($summaryPayment['SummaryPayment']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$rows[] = $this->App->traslateEnum($summaryPayment['SummaryPayment']['stato']);
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
$rows[] = number_format($tot_importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
$rows[] = number_format($tot_importo_richiesto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
//$rows[] = number_format($tot_importo_cash,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
$rows[] = number_format($tot_importo_pagato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

$this->PhpExcel->addTableRow($rows);
		    
$this->PhpExcel->addTableFooter();

$this->PhpExcel->output($fileData['fileName'].'.xlsx'); // anche x pdf
?>