<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

if (!empty($results)) {

		// define table cells
		$table[] =	array('label' => __('N.'), 'width' => 'auto');
		$table[] =	array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Delivery'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Importo POS'), 'width' => 'auto', 'filter' => true);
		
		// heading
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

			
		$tot_importo_pos=0;
		foreach ($results as $numResult => $result) {
			
			$rowsExcel = [];
			
			$rowsExcel[] = ($numResult + 1);
			$rowsExcel[] = $result['User']['name'];
			$rowsExcel[] = $result['Delivery']['luogoData'];
			$rowsExcel[] = $result['SummaryDeliveriesPos']['importo'];
			
			$this->PhpExcel->addTableRow($rowsExcel);
			
			$tot_importo_pos += $result['SummaryDeliveriesPos']['importo'];
		}		
	
		/*
		 * totali
		 */
		$tot_importo_pos = number_format($tot_importo_pos,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

			
		$rowsExcel = [];
		
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = $tot_importo_pos;	
		
		$this->PhpExcel->addTableRow($rowsExcel);				
}	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>