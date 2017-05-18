<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

if (!empty($results)) {

		// define table cells
		$table[] =	array('label' => __('N.'), 'width' => 'auto');
		$table[] =	array('label' => __('Supplier'), 'width' => 'auto', 'filter' => true);
		$table[] =	array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Conf'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('qta'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Created'), 'width' => 'auto', 'filter' => true);
		
		// heading
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

			
		$i=0;
		$tot_importo=0;
		foreach ($results as $numResult => $result) {
			
			$rowsExcel = array();
			
			$rowsExcel[] = ($numResult + 1);
			$rowsExcel[] = $result['SuppliersOrganization']['name'];
			$rowsExcel[] = $this->ExportDocs->prepareCsv($result['Storeroom']['name']);
			$rowsExcel[] = $this->ExportDocs->prepareCsv($this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']));	
			$rowsExcel[] = number_format($result['Storeroom']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$rowsExcel[] = $this->ExportDocs->prepareCsv($result['Storeroom']['qta']);
			$rowsExcel[] = $this->ExportDocs->prepareCsv($this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']));
			$rowsExcel[] = $this->App->formatDateCreatedModifier($result['Storeroom']['created']);
			
			$this->PhpExcel->addTableRow($rowsExcel);
			
			$tot_importo += $result['Cash']['importo'];
			$i++;
		}		
				
}	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>