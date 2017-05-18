<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

// define table cells
$table[] =	array('label' => __('N'), 'width' => 'auto');
$table[] =	array('label' => __('Bio'), 'width' => 'auto', 'filter' => true);
if($showCodice=='Y')
    $table[] =	array('label' => __('Codice'), 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('qta'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('PrezzoUnita'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('Importo'), 'width' => 'auto', 'wrap' => true, 'filter' => false);

// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

$i=0;
$tot_qta = 0;
$tot_importo = 0;
$article_organization_id_old=0;
$article_id_old = 0;
foreach($results as $numResult => $result) {

	if($article_id_old > 0 && // salto la prima volta
	   ($article_organization_id_old != $result['ArticlesOrder']['article_organization_id'] ||
	    $article_id_old != $result['ArticlesOrder']['article_id'])) {
	
		$bio = $result['Article']['bio'];
		

                $rows = array();
    	
                $rows[] = ($numArticle+1);
                $rows[] = $bio;

		
		if($showCodice=='Y')
                    $rows[] = $codiceArticle;
		
                $rows[] = $this->ExportDocs->prepareCsv($name);
			
		$rows[] = $tot_qta_single_article;				
		$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo));
		$rows[] = number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
		$i++;
		
		$bio = '';
		$codiceArticle = '';
		$name = '';
		$prezzo = '';
		$tot_qta_single_article = 0;
		$tot_importo_single_article = 0;
		
                
                $this->PhpExcel->addTableRow($rows);	
	}  
	
	/*
	 * gestione qta e importi
	 * */
	if($result['Cart']['qta_forzato']>0) {
		$qta = $result['Cart']['qta_forzato'];
		$qta_modificata = true;
	}	
	else {
		$qta = $result['Cart']['qta'];
		$qta_modificata = false;
	}
	$importo_modificato = false;
	if($result['Cart']['importo_forzato']==0) {
		if($result['Cart']['qta_forzato']>0) 
			$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
		else {
			$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
		}	
	}	
	else {
		$importo = $result['Cart']['importo_forzato'];
		$importo_modificato = true;
	}
					
	$tot_importo += $importo;
	$tot_qta += $qta;
					
	$bio = $result['Article']['bio'];
	$codiceArticle = $result['Article']['codice'];
	$name = $result['Article']['name'].' '.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
	$prezzo = $result['ArticlesOrder']['prezzo'];
	$tot_qta_single_article += $qta;
	$tot_importo_single_article += $importo;
	$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	$article_organization_id_old = $result['ArticlesOrder']['article_organization_id'];
	$article_id_old = $result['ArticlesOrder']['article_id'];
					
}  // loop articoli
 	
$rows = array();

$rows[] = ($numArticle+1);
if($bio=='Y') $rows[] = 'Bio';
else $rows[] = '';
				
if($showCodice=='Y') 
    $rows[] = $codiceArticle;

$rows[] = $this->ExportDocs->prepareCsv($name);
$rows[] = $this->ExportDocs->prepareCsv($tot_qta_single_article);
$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo));
$rows[] = number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));		
	
$this->PhpExcel->addTableRow($rows);	

/*
 * totali
 */
$rows = array();
$rows[] = '';
if($showCodice=='Y') 
    $rows[] = '';

$rows[] = '';
$rows[] = '';
$rows[] = $tot_qta;
$rows[] = '';
$rows[] = $this->ExportDocs->prepareCsv(number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));			

$this->PhpExcel->addTableRow($rows);


$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>