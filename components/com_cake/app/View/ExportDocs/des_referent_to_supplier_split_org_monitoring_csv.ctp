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
foreach($results as $resultOrg) {

	$newResults[$resultOrg['Organization']['id']]['Organization'] = $resultOrg['Organization'];
	$newResults[$resultOrg['Organization']['id']]['Order'] = $resultOrg['Order'];
		
	foreach($resultOrg['Cart'] as $result) {
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

$csv = array();
$headers = array('csv' => $csv);


$qta_totale = 0;
$importo_totale = 0;
$html = '';
$num_rows_csv = 0;
foreach($newResults as $resultOrg) {
	
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
        
        if($isToValidate) {
                $data[$num_rows_csv]['csv'] += array('colli' => ('Colli completati'));
                $data[$num_rows_csv]['csv'] += array('colli_delta' => ('Mancano per il collo'));
                $data[$num_rows_csv]['csv'] += array('colli_da' => ('Collo da'));
        }

        $data[$num_rows_csv]['csv'] += array('qta' => __('qta'));
        $data[$num_rows_csv]['csv'] += array('prezzoUnita' => __('PrezzoUnita'));
        $data[$num_rows_csv]['csv'] += array('importo' => __('Importo'));
        
        // heading
        $num_rows_csv++;



	$i=0;
	$tot_qta = 0;
	$tot_importo = 0;
	if(isset($resultOrg['Cart']))
	foreach($resultOrg['Cart'] as $result) {

                $name = $result['Article']['name'].' '.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);		
                $bio = $result['Article']['bio'];

                $data[$num_rows_csv]['csv'] = array('N' => $i+1);

                if($bio=='Y') 
                    $data[$num_rows_csv]['csv'] += array('bio' => 'Bio');
                else 
                    $data[$num_rows_csv]['csv'] += array('bio' => '');

                if($showCodice=='Y') 
                    $data[$num_rows_csv]['csv'] += array('codice' => $codiceArticle);

                $data[$num_rows_csv]['csv'] += array('name' => $this->ExportDocs->prepareCsv($name));

				
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
                
                if($result['ArticlesOrder']['pezzi_confezione']>1)  $data[$num_rows_csv]['csv'] += array('colli' => $colli_completi);
                else $data[$num_rows_csv]['csv'] += array('colli' => '');

            if($result['ArticlesOrder']['pezzi_confezione']>1) {
            	
                if($differenza_da_ordinare != $result['ArticlesOrder']['pezzi_confezione'])  
                    $data[$num_rows_csv]['csv'] += array('colli_delta' => $differenza_da_ordinare);
                else
                    $data[$num_rows_csv]['csv'] += array('colli_delta' => '0');
                
                $data[$num_rows_csv]['csv'] += array('colli_da' => $result['ArticlesOrder']['pezzi_confezione']);
            }
            else 
            {
                $data[$num_rows_csv]['csv'] += array('colli_delta' => '');
                $data[$num_rows_csv]['csv'] += array('colli_da' => '');
            }

        }

        $data[$num_rows_csv]['csv'] += array('qta' =>  $result['Cart']['qta']);				
        $data[$num_rows_csv]['csv'] += array('prezzoUnita' =>  $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($result['ArticlesOrder']['prezzo'])));
        $data[$num_rows_csv]['csv'] += array('importo' => number_format($result['Cart']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));

	$tot_importo += $result['Cart']['importo'];
	$tot_qta += $prezzo = $result['Cart']['qta'];
	
	$i++;
        $num_rows_csv++;
						
	}  // loop articoli


        $num_rows_csv++;	


        $data[$num_rows_csv]['csv'] = array('N' => '');
        $data[$num_rows_csv]['csv'] += array('bio' => '');
	if($showCodice=='Y') 
            $data[$num_rows_csv]['csv'] += array('codice' => '');
        $data[$num_rows_csv]['csv'] += array('name' => '');
        
        if($isToValidate) {
           $data[$num_rows_csv]['csv'] += array('colli' => '');
           $data[$num_rows_csv]['csv'] += array('colli_delta' => '');
           $data[$num_rows_csv]['csv'] += array('colli_da' => '');
        }

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

if($isToValidate) {
   $data[$num_rows_csv]['csv'] += array('colli' => '');
   $data[$num_rows_csv]['csv'] += array('colli_delta' => '');
   $data[$num_rows_csv]['csv'] += array('colli_da' => '');
}

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