<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/

/*
 * aggrego i dati
 */
$newResults = [];
foreach($results as $numResult => $resultOrg) {

	$newResults[$resultOrg['Organization']['id']]['Organization'] = $resultOrg['Organization'];
	$newResults[$resultOrg['Organization']['id']]['Order'] = $resultOrg['Order'];
		
	foreach($resultOrg['Cart'] as $numResult => $result) {
		$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['Article'] = $result['Article'];

		$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['ArticlesOrder']['article_organzation__id'] = $result['ArticlesOrder']['article_organzation__id'];
		$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['ArticlesOrder']['article_id'] = $result['Article']['ArticlesOrder'];
		$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['ArticlesOrder']['pezzi_confezione'] = $result['ArticlesOrder']['pezzi_confezione'];
		$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['ArticlesOrder']['prezzo'] = $result['ArticlesOrder']['prezzo'];
		
		/*
		 * gestione qta e importi
		 * */
		if($result['Cart']['qta_forzato']>0) 
			$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['Cart']['qta'] += $result['Cart']['qta_forzato'];
		else 
			$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['Cart']['qta'] += $result['Cart']['qta'];
		
		if($result['Cart']['importo_forzato']==0) {
			if($result['Cart']['qta_forzato']>0) 
				$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['Cart']['importo'] += ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
			else 
				$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['Cart']['importo'] += ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);	
		}	
		else 
			$newResults[$resultOrg['Organization']['id']]['Cart'][$result['Article']['id']]['Cart']['importo'] += $result['Cart']['importo_forzato'];		
	}
} 
		
/*
echo "<pre>";
print_r($newResults);
echo "</pre>";
*/

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

$table = array();
$table[] =	array('label' => __('N'), 'width' => 'auto');
$table[] =	array('label' => __('Bio'), 'width' => 'auto', 'filter' => true);
if($showCodice=='Y')
    $table[] =	array('label' => __('Codice'), 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
if($isToValidate) {
        $table[] = array('label' => ('Colli completati'), 'width' => 'auto');
        $table[] = array('label' => ('Mancano per il collo'), 'width' => 'auto');
        $table[] = array('label' => ('Colla da'), 'width' => 'auto');
}
$table[] =	array('label' => __('qta'), 'width' => 'auto', 'filter' => false);
$table[] =	array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => false);
$table[] =	array('label' => __('Importo'), 'width' => 'auto', 'filter' => false);

// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));


$qta_totale = 0;
$importo_totale = 0;
$html = '';
foreach($newResults as $resultOrg) {

        /*
         * intestazione
         */
        $rows = array();
        $rows[] = '';
        $rows[] = '';
        if($showCodice=='Y')
            $rows[] = '';
        $rows[] = $resultOrg['Organization']['name'];
        $this->PhpExcel->addTableRow($rows);	




	$i=0;
	$tot_qta = 0;
	$tot_importo = 0;
	if(isset($resultOrg['Cart']))
	foreach($resultOrg['Cart'] as $numResult => $result) {

    $name = $result['Article']['name'].' '.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
    
    $rows = array();

    $rows[] = ($i+1);

    $data[$numResult]['csv'] = array('N' => ($numArticle+1));

        if($result['Article']['bio']=='Y') $rows[] = 'Bio';
        else $rows[] = '';
        if($showCodice=='Y')
            $rows[] = $result['Article']['codice'];

        $rows[] = $this->ExportDocs->prepareCsv($name);

        if($isToValidate) {
            if($result['ArticlesOrder']['pezzi_confezione']>1) {
                /*
                 * colli_completi / differenza_da_ordinare
                 */
                $colli_completi = intval($result['Cart']['qta'] / $result['ArticlesOrder']['pezzi_confezione']);
                if($colli_completi>0)
                        $differenza_da_ordinare = (($result['ArticlesOrder']['pezzi_confezione'] * ($colli_completi +1)) - $result['Cart']['qta']);
                else {
                        $differenza_da_ordinare = ($result['ArticlesOrder']['pezzi_confezione'] - $result['Cart']['qta']);
                        $colli_completi = '-';
                }        	
        }
                
            
        if($result['ArticlesOrder']['pezzi_confezione']>1)  $rows[] = $colli_completi;
        else $rows[] = '';
            
        if($result['ArticlesOrder']['pezzi_confezione']>1) {
            		
                if($differenza_da_ordinare != $result['ArticlesOrder']['pezzi_confezione'])  
                    $rows[] = $differenza_da_ordinare;
                else
                    $rows[] = '0';
                
                $rows[] = $result['ArticlesOrder']['pezzi_confezione'];
            }
            else {
                $rows[] = '';
                $rows[] = '';
            }
        }
 
        $rows[] = $result['Cart']['qta'];				
        $rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($result['ArticlesOrder']['prezzo']));
        $rows[] = number_format($result['Cart']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

		$tot_importo += $result['Cart']['importo'];
		$tot_qta += $prezzo = $result['Cart']['qta'];
		
		$i++;
			
        $this->PhpExcel->addTableRow($rows);	

	}  // loop articoli
 	




        $rows = array();
        $rows[] = '';
		if($showCodice=='Y') 
            $rows[] = '';
        $rows[] = '';
        if($isToValidate) {
                $rows[] = '';
                $rows[] = '';
                $rows[] = '';
        }  
        $rows[] = '';
	/*
	 * totali singolo GAS
	 */
	$rows[] = $tot_qta;
	$rows[] = '';
	$rows[] = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

	$this->PhpExcel->addTableRow($rows);	

	$qta_totale += $tot_qta;
	$importo_totale += $tot_importo;

	$tot_qta=0;
	$tot_qta_single_article = 0;
	$tot_importo_single_article = 0;	
	
} // loop Organizations


/* 
 * totali
 */
$rows = array();
$rows[] = '';
if($showCodice=='Y') 
    $rows[] = '';
$rows[] = '';
if($isToValidate) {
        $rows[] = '';
        $rows[] = '';
        $rows[] = '';
}  
$rows[] = '';
$rows[] = $qta_totale;
$rows[] = '';
$rows[] = number_format($importo_totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

$this->PhpExcel->addTableRow($rows);	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>