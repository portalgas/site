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
		$table[] = array('label' => __('StoreroomQta'), 'width' => 'auto', 'filter' => true);
		if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
			$table[] = array('label' => __('StoreroomArticleToBooked'), 'width' => 'auto', 'filter' => true);
			$table[] = array('label' => __('StoreroomArticleJustBooked'), 'width' => 'auto', 'filter' => true);			
		}
		$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => true);
		
		// heading
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

			
		$i=0;
		$tot_importo=0;
		foreach ($results as $numResult => $result) {
			
			$rowsExcel = [];
			
			$rowsExcel[] = ($numResult + 1);
			$rowsExcel[] = $result['SuppliersOrganization']['name'];
			$rowsExcel[] = $this->ExportDocs->prepareCsv($result['Storeroom']['name']);
			$rowsExcel[] = $this->ExportDocs->prepareCsv($this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']));	
			$rowsExcel[] = number_format($result['Storeroom']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$rowsExcel[] = $this->ExportDocs->prepareCsv($result['MyStoreroom']['qtaTot']);
			if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
				$rowsExcel[] = $this->ExportDocs->prepareCsv($result['MyStoreroom']['qtaToBooked']);
				$rowsExcel[] = $this->ExportDocs->prepareCsv($result['MyStoreroom']['qtaJustBooked']);
			}
			$rowsExcel[] = $this->ExportDocs->prepareCsv($this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']));
			//$rowsExcel[] = $this->App->formatDateCreatedModifier($result['Storeroom']['created']);
			
			$this->PhpExcel->addTableRow($rowsExcel);
			
			$tot_importo += $result['Cash']['importo'];
			$i++;
			
			/*
			 * dettaglio prenotazioni
			 */
			if($user->organization['Organization']['hasStoreroomFrontEnd']=='Y' &&
			   isset($result['MyStoreroom']['articlesJustBookeds']))
				foreach($result['MyStoreroom']['articlesJustBookeds'] as $articlesJustBookeds) {
					$User_name = $articlesJustBookeds['User']['name'];
					$Storeroom_qta = $articlesJustBookeds['Storeroom']['qta'];
					$Storeroom_prezzo = $articlesJustBookeds['Storeroom']['prezzo'];
					$Storeroom_importo = $articlesJustBookeds['Storeroom']['importo'];

					$rowsExcel = [];

					$rowsExcel[] = '';
					$rowsExcel[] = __('Delivery');
					$rowsExcel[] = $articlesJustBookeds['Delivery']['luogo'];
					$rowsExcel[] = $this->Time->i18nFormat($articlesJustBookeds['Delivery']['data'],"%A %e %B %Y");			
					$rowsExcel[] = $User_name;
					$rowsExcel[] = '';
					$rowsExcel[] = '';
					$rowsExcel[] = $Storeroom_qta;
						
					$this->PhpExcel->addTableRow($rowsExcel);
				}				
		}		
				
}	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>