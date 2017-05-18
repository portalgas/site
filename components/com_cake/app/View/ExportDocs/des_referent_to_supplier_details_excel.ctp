<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

// define table cells
$table[] =	array('label' => __('N'), 'width' => 'auto');
$table[] =	array('label' => __('Bio'), 'width' => 'auto', 'filter' => true);
if($showCodice=='Y')
    $table[] =	array('label' => __('Codice'), 'width' => 'auto', 'filter' => false);
$table[] =	array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('GAS'), 'width' => 'auto', 'filter' => false);
$table[] =	array('label' => __('qta'), 'width' => 'auto', 'filter' => false);
$table[] =	array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => false);
$table[] =	array('label' => __('Importo'), 'width' => 'auto', 'filter' => false);

// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

$tot_qta = 0;
$tot_importo = 0;
$i=0;
foreach($results as $numResult => $result) {

		/*
		 *  ARTICOLO
		 */
                $rows = array();
                $rows[] = ($numArticle+1);
                if($result['Article']['bio']=='Y')
                    $rows[] = 'Bio';
                else
                    $rows[] = '';

		if($showCodice=='Y') 
                    $rows[] = $result['Article']['codice'];
                        
                $rows[] = $this->ExportDocs->prepareCsv($result['Article']['name']);

                $this->PhpExcel->addTableRow($rows);
                
		/*
		 *  GAS 
		 */		
		 $tot_qta_article = 0;
		 $tot_importo_article = 0;
		foreach($result['Article']['Organization'] as $numResult2 => $organizationResult) {
		
				$rows = array();
				$rows[] = '';
				if($showCodice=='Y')
                                    $rows[] = '';

                                $rows[] = '';
				$rows[] = '';
				$rows[] = $organizationResult['Organization']['name'];				
					
				$rows[] = $organizationResult['tot_qta'];				
				$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($organizationResult['ArticlesOrder']['prezzo']));
				$rows[] = number_format($organizationResult['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
				
				$tot_qta_article += $organizationResult['tot_qta'];
				$tot_importo_article += $organizationResult['tot_importo'];
                                
                                $this->PhpExcel->addTableRow($rows);
		}
		           
		/*
		 * sub totale
		 */
		$rows = array();
                $rows[] = '';
		if($showCodice=='Y') 
                    $rows[] = '';
		
                $rows[] = '';
                $rows[] = '';
                $rows[] = '';
		$rows[] = $tot_qta_article;
		$rows[] = '';
		$rows[] = number_format($tot_importo_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
                $this->PhpExcel->addTableRow($rows);
		
		$tot_qta += $tot_qta_article;
		$tot_importo += $tot_importo_article;
		
		$i++;
} // loop Articles
 
// totale
$rows = array();
$rows[] = '';
if($showCodice=='Y') 
    $rows[] = '';

$rows[] = '';
$rows[] = '';
$rows[] = '';
$rows[] = $tot_qta;
$rows[] = '';
$rows[] = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));			
$this->PhpExcel->addTableRow($rows);

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>