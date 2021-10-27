<?php 
/*
 * T O - A R T I C L E S - W E I G H T
 */

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

if(!empty($results)) {

	$peso_gr_totale = 0;
	$peso_ml_totale = 0;
	$peso_pz_totale = 0;
	
	// define table cells
	$table[] =	array('label' => __('N'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
	$table[] =	array('label' => __('Bio'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y') 
		$table[] =	array('label' => __('Codice'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
	$table[] =	array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
	$table[] =	array('label' => __('Kg'), 'width' => 'auto');
	$table[] =	array('label' => __('Lt'), 'width' => 'auto');
	$table[] =	array('label' => __('Pz'), 'width' => 'auto');
	$table[] =	array('label' => __('Quantità acquistata'), 'width' => 'auto');	
	// heading
	$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

	$i=0;
	foreach($results as $article_id => $result) {
	
		$i++;
		
		if($result['Article']['bio']=='Y')
			$bio = 'Bio';
		else
			$bio = '';

		$rows = [];
		$rows[] = $i;
		$rows[] = $bio;
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') 
			$rows[] = $result['Article']['codice'];

		/*
		 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
		 */			
		if(!empty($result['ArticlesOrder']['name']))
			$rows[] = $this->ExportDocs->prepareCsv($result['ArticlesOrder']['name']);
		else
			$rows[] = $this->ExportDocs->prepareCsv($result['Article']['name']);
		$rows[] = ($result['Peso']['kg'] / 1000);
		$rows[] = ($result['Peso']['lt'] / 1000);
		$rows[] = $result['Peso']['pz'];
		$rows[] = $result['ArticlesOrder']['qta_cart'];

		$this->PhpExcel->addTableRow($rows);

		$peso_gr_totale += $result['Peso']['kg'];
		$peso_ml_totale += $result['Peso']['lt'];
		$peso_pz_totale += $result['Peso']['pz'];
	}

	$rows = [];
	$rows[] = '';
	$rows[] = '';
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y') 
		$rows[] = '';
		
	$rows[] = '';
	$rows[] = ($peso_gr_totale / 1000);
	$rows[] = ($peso_ml_totale / 1000);
	$rows[] = $peso_pz_totale;

	$this->PhpExcel->addTableRow($rows);	
}
		    
$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>