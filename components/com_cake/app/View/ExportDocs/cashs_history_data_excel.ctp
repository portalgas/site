<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

if (!empty($results)) {

		// define table cells
		$table[] = array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Mail'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('CashSaldo'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => '', 'width' => 'auto', 'filter' => false);		
		$table[] = array('label' => __('CashOperazione'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('nota'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Created'), 'width' => 'auto', 'filter' => true);
		
		// heading
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

		$tot_importo=0;
		foreach ($results as $numResult => $user) {
			
			foreach ($user['Cash'] as $numResult2 => $result) {
			
				$rowsExcel = [];

				if($numResult2==0) {
					$rowsExcel[] = $user['User']['name'];
					$rowsExcel[] = $user['User']['email'];
				}
				else {
					$rowsExcel[] = '';
					$rowsExcel[] = '';
				}

				$rowsExcel[] = $result['CashesHistory']['importo'];
				if($result['CashesHistory']['operazione']>0)
					$rowsExcel[] = '+';		
				else
					$rowsExcel[] = '-';	
				$rowsExcel[] = $result['CashesHistory']['operazione'];	
				$rowsExcel[] = strip_tags($this->ExportDocs->prepareCsv($result['CashesHistory']['nota']));	
				$rowsExcel[] = CakeTime::format($result['CashesHistory']['modified'], "%A, %e %B %Y");
				
				$this->PhpExcel->addTableRow($rowsExcel);
				
				// $tot_importo += $result['CashesHistory']['importo'];
				
			} // loop Cashs

		} // loop Users	
		
		/*
		 * totale cassa

		$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

		$rowsExcel = [];
		
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = $tot_importo;	
		
		$this->PhpExcel->addTableRow($rowsExcel);	
		 */					
}	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>