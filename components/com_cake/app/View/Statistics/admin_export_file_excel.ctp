<?php 
    $this->PhpExcel->createWorksheet();
    $this->PhpExcel->setDefaultFont('Calibri', 12);
				  
    // define table cells
	$table[] = array('label' => __('Delivery'), 'width' => 'auto');
	$table[] = array('label' => __('Delivery').' '.__('Data'), 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => __('Order').' '.__('DataInizio'), 'width' => 'auto');
	$table[] = array('label' => __('Order').' '.__('DataFine'), 'width' => 'auto');
	$table[] = array('label' => __('SuppliersOrganization'), 'width' => 'auto', 'filter' => true);
	// user
	$table[] = array('label' => __('Username'), 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => __('Email'), 'width' => 'auto');
    if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
    	$table[] = array('label' => __('Codice'), 'width' => 'auto');
	$table[] =	array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => true);
	$table[] =	array('label' => __('Conf'), 'width' => 'auto', 'filter' => true);
    $table[] = array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => true);
    $table[] = array('label' => __('UM'), 'width' => 'auto', 'filter' => true);
    $table[] = array('label' => __('Prezzo/UM'), 'width' => 'auto', 'filter' => true);    		
    // cart
    $table[] = array('label' => __('qta'), 'width' => 'auto', 'filter' => true);  
    $table[] = array('label' => __('importo'), 'width' => 'auto', 'filter' => true);    		
    	
    // heading
    $this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));
    
    // data
	if(isset($results) && !empty($results))
    foreach($results as $result) {

    	$rows = [];
   	    		
		$rows[] = $result['StatDelivery']['luogo'];
		$rows[] = $result[0]['StatDelivery_data'];
		$rows[] = $result[0]['StatOrder_data_inizio'];
		$rows[] = $result[0]['StatOrder_data_fine'];
		$rows[] = $result['StatOrder']['supplier_organization_name'];
		$rows[] = $result['User']['username'];	
		$rows[] = $result['User']['email'];	
		$rows[] = $result['User']['User_name'];		
    	if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
	    	$rows[] = $result['StatArticlesOrder']['codice'];
    	$rows[] = $result['StatArticlesOrder']['StatArticlesOrder_name'];
	    $rows[] = $this->ExportDocs->prepareCsv($this->App->getArticleConf($result['StatArticlesOrder']['statArticlesOrder_qta'], $result['StatArticlesOrder']['um']));
		$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($result['StatArticlesOrder']['prezzo']));
    	$rows[] = $result['StatArticlesOrder']['um_riferimento'];
    	$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzoUM($result['StatArticlesOrder']['prezzo'], $result['StatArticlesOrder']['statArticlesOrder_qta'], $result['StatArticlesOrder']['um'], $result['StatArticlesOrder']['um_riferimento']));
		$rows[] = $result['StatCart']['StatCart_qta'];
		$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($result['StatCart']['StatCart_importo']));
		
		$this->PhpExcel->addTableRow($rows);		    
    }
    $this->PhpExcel->addTableFooter();
    $this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>