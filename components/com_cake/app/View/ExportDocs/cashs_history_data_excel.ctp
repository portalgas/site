<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	



if (!empty($results)) {

		// define table cells
		$table[] = array('label' => __('N.'), 'width' => 'auto');
		$table[] = array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Mail'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Movimenti'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Nota'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Data'), 'width' => 'auto', 'filter' => true);
		
		// heading
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

			
		$i=0;
		$tot_importo=0;
		foreach ($results as $numResult => $result) {
			
			$rowsExcel = array();
			
			$rowsExcel[] = ($numResult + 1);
			$rowsExcel[] = $result['User']['name'];
			$rowsExcel[] = $result['User']['email'];
			$rowsExcel[] = $result['Cash']['importo'];
			$rowsExcel[] = '';	
			$rowsExcel[] = strip_tags($this->ExportDocs->prepareCsv($result['Cash']['nota']));	
			$rowsExcel[] = CakeTime::format($result['Cash']['created'], "%A %e %B %Y");
			
			$this->PhpExcel->addTableRow($rowsExcel);
			
			$tot_importo += $result['Cash']['importo'];
			$i++;
                        
                        /*
                         * storico cassa
                         */
                        if(isset($result['CashesHistory'])) {
                            $importo_history_old = 0;
                            foreach ($result['CashesHistory'] as $numResult2 => $historyResult) {
			
                                if($numResult2==0) 
                                    $movimento = ($result['Cash']['importo'] - ($historyResult['CashesHistory']['importo']));
                                else 
                                    $movimento = ($importo_history_old - ($historyResult['CashesHistory']['importo']));
                                
                                $rowsExcel = array();

                                $rowsExcel[] = '';
                                $rowsExcel[] = '';
                                $rowsExcel[] = '';
                                $rowsExcel[] = $historyResult['CashesHistory']['importo'];
                                $rowsExcel[] = $movimento;	
                                $rowsExcel[] = strip_tags($this->ExportDocs->prepareCsv($historyResult['CashesHistory']['nota']));	
                                $rowsExcel[] = CakeTime::format($historyResult['CashesHistory']['created'], "%A %e %B %Y");

                                $this->PhpExcel->addTableRow($rowsExcel);  
                                
                                $importo_history_old = $historyResult['CashesHistory']['importo'];
                            }
                        }
		}		
		
		/*
		 * totale cassa
		 */
		$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

			
		$rowsExcel = array();
		
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = $tot_importo;	
		
		$this->PhpExcel->addTableRow($rowsExcel);				
}	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>