<?php 
$csv = array();
$headers = array('csv' => $csv);


$qta_totale = 0;
$importo_totale = 0;
$html = '';
$num_rows_csv = 0;
foreach($results as $numResult => $resultOrg) {
	
        // define table cells
        $data[$num_rows_csv]['csv'] = array('N' => '');
        $data[$num_rows_csv]['csv'] += array('bio' => '');
        if($showCodice=='Y')
            $data[$num_rows_csv]['csv'] += array('codice' => '');        
        $data[$num_rows_csv]['csv'] += array('name' => $resultOrg['Organization']['name']);
        // heading
        $num_rows_csv++;


        $table = array();
        $data[$num_rows_csv]['csv'] = array('N' => __('N'));
        $data[$num_rows_csv]['csv'] += array('bio' => __('Bio'));
        if($showCodice=='Y')
            $data[$num_rows_csv]['csv'] += array('codice' => __('Codice'));
        $data[$num_rows_csv]['csv'] += array('name' => __('Name'));
        $data[$num_rows_csv]['csv'] += array('qta' => __('qta'));
        $data[$num_rows_csv]['csv'] += array('prezzoUnita' => __('PrezzoUnita'));
        $data[$num_rows_csv]['csv'] += array('importo' => __('Importo'));
        
        // heading
        $num_rows_csv++;



	$i=0;
	$tot_qta = 0;
	$tot_importo = 0;
	$article_organization_id_old=0;
	$article_id_old = 0;
	foreach($resultOrg['Cart'] as $numResult => $result) {

		if($article_id_old > 0 && // salto la prima volta
		   ($article_organization_id_old != $result['ArticlesOrder']['article_organization_id'] ||
			$article_id_old != $result['ArticlesOrder']['article_id'])) {
		
			$bio = $result['Article']['bio'];
			

                        $data[$num_rows_csv]['csv'] = array('N' => $i+1);
                        if($bio=='Y') 
                            $data[$num_rows_csv]['csv'] += array('bio' => 'Bio');
			else 
                            $data[$num_rows_csv]['csv'] += array('bio' => '');
                      
			if($showCodice=='Y') 
                            $data[$num_rows_csv]['csv'] += array('codice' => $codiceArticle);
			
                        $data[$num_rows_csv]['csv'] += array('name' => $this->ExportDocs->prepareCsv($name));
				
			$data[$num_rows_csv]['csv'] += array('qta' => $tot_qta_single_article);				
			$data[$num_rows_csv]['csv'] += array('prezzoUnita' => $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo)));
			$data[$num_rows_csv]['csv'] += array('importo' => number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
			
				
			$num_rows_csv++;	
			
			$i++;
			
			$bio = '';
			$codiceArticle = '';
			$name = '';
			$prezzo = '';
			$tot_qta_single_article = 0;
			$tot_importo_single_article = 0;
				
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
 	


        $num_rows_csv++;

        $data[$num_rows_csv]['csv'] = array('N' => $i+1);
        if($bio=='Y') 
            $data[$num_rows_csv]['csv'] += array('bio' => 'Bio');
        else 
            $data[$num_rows_csv]['csv'] += array('bio' => '');

        if($showCodice=='Y') 
            $data[$num_rows_csv]['csv'] += array('codice' => $codiceArticle);

        $data[$num_rows_csv]['csv'] += array('name' => $this->ExportDocs->prepareCsv($name));
        $data[$num_rows_csv]['csv'] += array('qta' => $tot_qta_single_article);
        $data[$num_rows_csv]['csv'] += array('prezzoUnita' => $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo)));
        $data[$num_rows_csv]['csv'] += array('importo' => number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));

        $num_rows_csv++;	


        $data[$num_rows_csv]['csv'] = array('N' => '');
        $data[$num_rows_csv]['csv'] += array('bio' => '');
	if($showCodice=='Y') 
            $data[$num_rows_csv]['csv'] += array('codice' => '');
        $data[$num_rows_csv]['csv'] += array('name' => '');
        
	/*
	 * totali singolo GAS
	 */
	$data[$num_rows_csv]['csv'] += array('qta' => $tot_qta);
	$data[$num_rows_csv]['csv'] += array('prezzoUnita' => '');
	$data[$num_rows_csv]['csv'] += array('importo' => number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));

	$num_rows_csv++;	

	$qta_totale += $tot_qta;
	$importo_totale += $tot_importo;

	$tot_qta=0;
	$tot_qta_single_article = 0;
	$tot_importo_single_article = 0;	
	
} // loop Organizations


/* 
 * totali
 */
$num_rows_csv++;
$data[$num_rows_csv]['csv'] = array('N' => '');
$data[$num_rows_csv]['csv'] += array('bio' => '');
if($showCodice=='Y') 
    $data[$num_rows_csv]['csv'] += array('codice' => '');
$data[$num_rows_csv]['csv'] += array('name' => '');
$data[$num_rows_csv]['csv'] += array('qta' => $qta_totale);
$data[$num_rows_csv]['csv'] += array('prezzoUnita' => '');
$data[$num_rows_csv]['csv'] += array('importo' => number_format($importo_totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));

	
array_unshift($data,$headers);

foreach ($data as $row)
{
	foreach ($row['csv'] as &$value) {
		// Apply opening and closing text delimiters to every value
		$value = "\"".$value."\"";
	}
	// Echo all values in a row comma separated
	echo implode(",",$row['csv'])."\n";
}
?>