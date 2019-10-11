<?php 
    $this->PhpExcel->createWorksheet();
    $this->PhpExcel->setDefaultFont('Calibri', 12);

    // define table cells
    $table[] =	array('label' => __('N'), 'width' => 'auto');
    $table[] =	array('label' => __('Bio'), 'width' => 'auto', 'filter' => true);
    if($filterType=='Y')
    	$table[] =	array('label' => __('Type'), 'width' => 'auto', 'filter' => false);

    if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y' && $filterCategory=='Y')
    	$table[] =	array('label' => __('Category'), 'width' => 'auto', 'filter' => true);
     
    if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
    	$table[] = array('label' => __('Codice'), 'width' => 'auto');
        
    $table[] =	array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
    	
    $table[] =	array('label' => __('Conf'), 'width' => 'auto', 'filter' => true);

    $table[] = array('label' => 'Prezzo unità', 'width' => 'auto', 'filter' => true);
    $table[] = array('label' => __('UM'), 'width' => 'auto', 'filter' => true);
    $table[] = array('label' => __('Prezzo/UM'), 'width' => 'auto', 'filter' => true);
    		
    if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y' && $filterIngredienti=='Y')	
    	$table[] = array('label' => __('ingredienti'), 'width' => 50, 'wrap' => true, 'filter' => false);
    	
    if($filterNota=='Y')
    	$table[] =	array('label' => __('Nota'), 'width' => 50, 'wrap' => true);
    
    $table[] =	array('label' => __('pezzi_confezione'), 'width' => 'auto', 'filter' => true);
    $table[] =	array('label' => __('qta_minima'), 'width' => 'auto', 'filter' => true);
    $table[] =	array('label' => __('qta_massima'), 'width' => 'auto', 'filter' => true);
    $table[] =	array('label' => __('qta_multipli'), 'width' => 'auto', 'filter' => true);

    $table[] =	array('label' => __('qta_minima_order'), 'width' => 'auto', 'filter' => true);
    $table[] =	array('label' => __('qta_massima_order'), 'width' => 'auto', 'filter' => true);

    if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
    	$table[] =	array('label' => __('alert_to_qta'), 'width' => 'auto', 'filter' => true);

    if($isReferenteSupplierOrganization) // ho il campo STATO in +		$table[] =	array('label' => 'Visibile', 'width' => 'auto', 'filter' => true);
    
	$table[] =	array('label' => "Presente nell'elenco degli articoli che si possono associare ad un ordine", 'width' => 'auto', 'filter' => true);

    // heading
    $this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));
    
    // data
	if(isset($results) && !empty($results))
    foreach($results as $numArticle => $result) {
    
    	if($result['Article']['bio']=='Y')
    		$bio = 'Si';    	else    		$bio = 'No';
    	
    	$rows = [];
    	
    	$rows[] = ($numArticle+1);
    	$rows[] = $bio;
    	
    	if($filterType=='Y') {
	    	$tmp = "";	    	if(!empty($result['ArticlesType'])) {	    		foreach($result['ArticlesType'] as $articlesType)	    			$tmp .= $articlesType['ArticlesType']['descrizione']." ";	    	}	    	$rows[] = $tmp;
    	}
    	    	
    	if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y' && $filterCategory=='Y')
    		$rows[] = $result['CategoriesArticle']['name'];    		
    	if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
	    	$rows[] = $result['Article']['codice'];

		if(isset($result['ArticlesOrder']['name']))	
			$rows[] = $result['ArticlesOrder']['name'];
		else
			$rows[] = $result['Article']['name'];
		    	
	    $rows[] = $this->ExportDocs->prepareCsv($this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']));
    	$rows[] = $result['Article']['prezzo'];
    	$rows[] = $result['Article']['um'];
    	$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']));
    			
      	if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y' && $filterIngredienti=='Y')	
      		$rows[] = strip_tags($result['Article']['ingredienti']);
      	
	    if($filterNota=='Y')
	       $rows[] = strip_tags($result['Article']['nota']);

	    /*
	     * per gli articoli associati all'ordine
	    */
	    if(isset($result['ArticlesOrder'])) {
	    	$rows[] = $result['ArticlesOrder']['pezzi_confezione'];
	    	$rows[] = $result['ArticlesOrder']['qta_minima'];
	    	$rows[] = $result['ArticlesOrder']['qta_massima'];
	    	$rows[] = $result['ArticlesOrder']['qta_multipli'];
	    	 
	    	if($result['ArticlesOrder']['qta_minima_order']==0)
	    		$rows[] = "Nessuna";
	    	else
	    		$rows[] = $result['ArticlesOrder']['qta_minima_order'];
	    	if($result['Article']['qta_massima_order']==0)
	    		$rows[] = "Nessuna";
	    	else
	    		$rows[] = $result['ArticlesOrder']['qta_massima_order'];
	    	 
	    	if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
	    		$rows[] = $result['ArticlesOrder']['alert_to_qta'];	    	
	    }
	    else {
	    	$rows[] = $result['Article']['pezzi_confezione'];
	    	$rows[] = $result['Article']['qta_minima'];
	    	$rows[] = $result['Article']['qta_massima'];
	    	$rows[] = $result['Article']['qta_multipli'];
	    	 
	    	if($result['Article']['qta_minima_order']==0)
	    		$rows[] = "Nessuna";
	    	else
	    		$rows[] = $result['Article']['qta_minima_order'];
	    	if($result['Article']['qta_massima_order']==0)
	    		$rows[] = "Nessuna";
	    	else
	    		$rows[] = $result['Article']['qta_massima_order'];
	    	 
	    	if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
	    		$rows[] = $result['Article']['alert_to_qta'];
	    }
				    
    	if($isReferenteSupplierOrganization) // ho il campo STATO in +    		$rows[] = $this->App->traslateEnum($result['Article']['stato']);    	
		$rows[] = $this->App->traslateEnum($result['Article']['flag_presente_articlesorders']);
		
		$this->PhpExcel->addTableRow($rows);		    
    }
    $this->PhpExcel->addTableFooter();
    $this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>