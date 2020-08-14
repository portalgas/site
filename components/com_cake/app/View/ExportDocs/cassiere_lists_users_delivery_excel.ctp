<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

// define table cells
$table[] =	array('label' => __('N.'), 'width' => 'auto');
$table[] =	array('label' => __('User'), 'width' => 'auto',  'filter' => true);
$table[] = array('label' => __('Importo_dovuto'), 'width' => 'auto', 'filter' => false);
$table[] = array('label' => __('Importo_pagato'), 'width' => 'auto', 'filter' => false);
$table[] = array('label' => __('Delta'), 'width' => 'auto', 'filter' => false);
if($user->organization['Organization']['hasFieldPaymentPos']=='Y') 
	$table[] = array('label' => __('Importo POS'), 'width' => 'auto', 'filter' => false);
$table[] =	array('label' => __('Cassa'), 'width' => 'auto', 'filter' => false);
$table[] = array('label' => __('Nota'), 'width' => 'auto', 'filter' => false);

// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));


if (!empty($results)) {

		$tot_importo_delivery = 0;
		$tot_importo_pagato_delivery = 0;
		$tot_importo_pos = 0;
		$tot_differenza_delivery = 0;
		foreach ($results as $numResult => $result) {


			$tot_importo = number_format($result[0]['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_importo_pagato = number_format($result[0]['tot_importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
			
			$differenza = (-1 * ($result[0]['tot_importo'] - $result[0]['tot_importo_pagato']));
			$differenza = number_format($differenza,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
			
			if(isset($result['Cash']['importo']))
				$importo_cash = number_format($result['Cash']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
			else
				$importo_cash = '0,00';
				
			$rowsExcel = [];
			$rowsExcel[] = ((int)$numResult+1);
			$rowsExcel[] = $result['User']['name'];
			$rowsExcel[] = $tot_importo;
			$rowsExcel[] = $tot_importo_pagato;
			$rowsExcel[] = $differenza;
			if($user->organization['Organization']['hasFieldPaymentPos']=='Y') 
				$rowsExcel[] = $result['SummaryDeliveriesPos']['importo'];
			$rowsExcel[] = $importo_cash;
			$rowsExcel[] = $result['Cash']['nota'];
			
			$this->PhpExcel->addTableRow($rowsExcel);
						
			$tot_importo_delivery += $result[0]['tot_importo'];
			$tot_importo_pagato_delivery += $result[0]['tot_importo_pagato'];
			$tot_importo_pos += $result['SummaryDeliveriesPos']['importo'];
			$tot_differenza_delivery += (-1 * ($result[0]['tot_importo'] - $result[0]['tot_importo_pagato']));
			
			$tot_importo_cash += $result['Cash']['importo'];				
		} // loop 

	/*
	 * totale consegna
	 */
	$tot_importo_delivery = number_format($tot_importo_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_pagato_delivery = number_format($tot_importo_pagato_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_pos = number_format($tot_importo_pos,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_differenza_delivery = number_format($tot_differenza_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

	$rowsExcel = [];
	$rowsExcel[] =  '';
			$rowsExcel[] =  '';
	$rowsExcel[] =  $tot_importo_delivery;
	$rowsExcel[] =  $tot_importo_pagato_delivery;
	$rowsExcel[] =  $tot_importo_pagato_delivery;
	if($user->organization['Organization']['hasFieldPaymentPos']=='Y') 
		$rowsExcel[] =  $tot_importo_pos;
	$rowsExcel[] =  $tot_importo_cash;
	$this->PhpExcel->addTableRow($rowsExcel);
}


$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>