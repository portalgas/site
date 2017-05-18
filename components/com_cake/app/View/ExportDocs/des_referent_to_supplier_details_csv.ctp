<?php 
$csv = array(
		'N' => 'N',
		'bio' => __('Bio'));

if($showCodice=='Y') 
	$csv += array('codice' => __('Codice'));

$csv += array('name' => __('Name'));
$csv += array('GAS' => __('GAS'));
$csv += array('qta' => __('qta'));
$csv += array('prezzoUnita' => __('PrezzoUnita'));
$csv += array('importo' => __('Importo'));

$headers = array('csv' => $csv);



$tot_qta = 0;
$tot_importo = 0;
$i=0;
$data = array();
$row_csv = 0;
foreach($results as $numResult => $result) {

		/*
		 *  ARTICOLO
		 */
                $data[$row_csv]['csv'] = array('N' => $numArticle+1);
                if($result['Article']['bio']=='Y')
                    $data[$row_csv]['csv'] += array('bio' => 'Bio');
                else
                    $data[$row_csv]['csv'] += array('bio' => '');

		if($showCodice=='Y') 
                    $data[$row_csv]['csv'] += array('codice' => $result['Article']['codice']);
                        
                $data[$row_csv]['csv'] += array('name' => $this->ExportDocs->prepareCsv($result['Article']['name']));

                $row_csv++;
                
		/*
		 *  GAS 
		 */		
		 $tot_qta_article = 0;
		 $tot_importo_article = 0;
		foreach($result['Article']['Organization'] as $numResult2 => $organizationResult) {
		
				$data[$row_csv]['csv'] = array('N' => '');
				$data[$row_csv]['csv'] += array('bio' => '');
				if($showCodice=='Y')
                                    $data[$row_csv]['csv'] += array('codice' => '');

                                $data[$row_csv]['csv'] += array('name' => '');
				$data[$row_csv]['csv'] += array('GAS' => $organizationResult['Organization']['name']);				
					
				$data[$row_csv]['csv'] += array('qta' => $organizationResult['tot_qta']);				
				$data[$row_csv]['csv'] += array('prezzoUnita' => $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($organizationResult['ArticlesOrder']['prezzo'])));
				$data[$row_csv]['csv'] += array('importo' => number_format($organizationResult['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
				
				
				$tot_qta_article += $organizationResult['tot_qta'];
				$tot_importo_article += $organizationResult['tot_importo'];
                                
                                $row_csv++;
		}
		           
		/*
		 * sub totale
		 */
                $data[$row_csv]['csv'] = array('N' => '');
                $data[$row_csv]['csv'] += array('bio' => '');
                if($showCodice=='Y')
                    $data[$row_csv]['csv'] += array('codice' => '');
                $data[$row_csv]['csv'] += array('name' => '');
                $data[$row_csv]['csv'] += array('GAS' => '');
                $data[$row_csv]['csv'] += array('qta' => $tot_qta_article);
                $data[$row_csv]['csv'] += array('prezzoUnita' => '');
		$data[$row_csv]['csv'] += array('importo' => number_format($tot_importo_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
		
                $row_csv++;
		
		$tot_qta += $tot_qta_article;
		$tot_importo += $tot_importo_article;
		
		$i++;
} // loop Articles
 
// totale
$data[$row_csv]['csv'] = array('N' => '');
$data[$row_csv]['csv'] += array('bio' => '');
if($showCodice=='Y')
    $data[$row_csv]['csv'] += array('codice' => '');
$data[$row_csv]['csv'] += array('name' => '');
$data[$row_csv]['csv'] += array('GAS' => '');
$data[$row_csv]['csv'] += array('qta' => $tot_qta);
$data[$row_csv]['csv'] += array('prezzoUnita' => '');
$data[$row_csv]['csv'] += array('importo' => number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));			
/*
echo "<pre>";
print_r($data);
echo "</pre>";exit;
*/
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