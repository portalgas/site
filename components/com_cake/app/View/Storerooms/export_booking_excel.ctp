<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

$rowsExcel = array();

$rowsExcel[] = __('Delivery');
if($delivery['sys']=='N')
	$rowsExcel[] = $delivery['Delivery']['luogoData'];
else
	$rowsExcel[] = $delivery['Delivery']['luogo'];
$this->PhpExcel->addTableRow($rowsExcel);

if (!empty($results)) {

		// define table cells
		$table[] = array('label' => __('N.'), 'width' => 'auto');
		$table[] = array('label' => __('User'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Supplier'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Conf'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Qta'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => true);
	
		// heading
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

		$tot_importo=0;
		foreach ($results as $numResult => $result) {
			
			$User_name = '';
			$Storeroom_qta = '';
			$Storeroom_prezzo = '';
			$Storeroom_importo = '';	
			
			if(isset($result['Storeroom']['articlesJustBookeds']))
				foreach($result['Storeroom']['articlesJustBookeds'] as $user) {
					$User_name = $user['User']['name'];
					$Storeroom_qta = $user['Storeroom']['qta'];
					$Storeroom_prezzo = $user['Storeroom']['prezzo'];
					$Storeroom_importo = $user['Storeroom']['importo'];
				}
					
			$rowsExcel = array();
			
			$rowsExcel[] = ($numResult + 1);
			$rowsExcel[] = $User_name;
			$rowsExcel[] = $result['SuppliersOrganization']['name'];
			$rowsExcel[] = $this->ExportDocs->prepareCsv($result['Storeroom']['name']);
			$rowsExcel[] = $this->ExportDocs->prepareCsv($this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']));	
			$rowsExcel[] = $this->ExportDocs->prepareCsv($Storeroom_qta);
			$rowsExcel[] = number_format($Storeroom_prezzo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$rowsExcel[] = $this->ExportDocs->prepareCsv($this->App->getArticleImporto($Storeroom_prezzo, $Storeroom_qta));
			
			$this->PhpExcel->addTableRow($rowsExcel);
		}		
				
}	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>