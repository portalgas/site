<?php 
$csv = array(
		'N' => 'N',
		'bio' => __('Bio'));

if($showCodice=='Y') 
	$csv += array('codice' => __('Codice'));

$csv += array('name' => __('Name'));
$csv += array('qta' => __('qta'));
$csv += array('prezzoUnita' => __('PrezzoUnita'));
$csv += array('importo' => __('Importo'));

$headers = array('csv' => $csv);



$i=0;
$tot_qta = 0;
$tot_importo = 0;
$article_organization_id_old=0;
$article_id_old = 0;
foreach($results as $numResult => $result) {

	if($article_id_old > 0 && // salto la prima volta
	   ($article_organization_id_old != $result['ArticlesOrder']['article_organization_id'] ||
	    $article_id_old != $result['ArticlesOrder']['article_id'])) {
	
		$data[$numResult]['csv'] = array('N' => ($numArticle+1));
		if($bio=='Y') $data[$numResult]['csv'] += array('bio' => 'Bio');
		else $data[$numResult]['csv'] += array('bio' => '');
		if($showCodice=='Y')
			$data[$numResult]['csv'] += array('codice' => $codiceArticle);

		$data[$numResult]['csv'] += array('name' => $this->ExportDocs->prepareCsv($name));
			
		$data[$numResult]['csv'] += array('qta' =>  $tot_qta_single_article);				
		$data[$numResult]['csv'] += array('prezzoUnita' =>  $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo)));
		$data[$numResult]['csv'] += array('importo' => number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
		
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
	$name = $result['ArticlesOrder']['name'].' '.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
	$prezzo = $result['ArticlesOrder']['prezzo'];
	$tot_qta_single_article += $qta;
	$tot_importo_single_article += $importo;
	$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	$article_organization_id_old = $result['ArticlesOrder']['article_organization_id'];
	$article_id_old = $result['ArticlesOrder']['article_id'];
					
}  // loop articoli
 	
$numResult++;

$data[$numResult]['csv'] = array('N' => ($numArticle+1));
if($bio=='Y') $data[$numResult]['csv'] += array('bio' => 'Bio');
else $data[$numResult]['csv'] += array('bio' => '');
				
if($showCodice=='Y') 
    $data[$numResult]['csv'] += array('codice' => $codiceArticle);

$data[$numResult]['csv'] += array('name' => $this->ExportDocs->prepareCsv($name));
$data[$numResult]['csv'] += array('qta' => $this->ExportDocs->prepareCsv($tot_qta_single_article));
$data[$numResult]['csv'] += array('prezzoUnita' => $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo)));
$data[$numResult]['csv'] += array('importo' => number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));		
	
$numResult++;	

/*
 * totali
 */
$data[$numResult]['csv'] = array('N' => '');
$data[$numResult]['csv'] += array('bio' => '');
if($showCodice=='Y') 
    $data[$numResult]['csv'] += array('codice' => '');

$data[$numResult]['csv'] += array('name' => '');
$data[$numResult]['csv'] += array('qta' => $tot_qta);
$data[$numResult]['csv'] += array('prezzoUnita' => '');
$data[$numResult]['csv'] += array('importo' => $this->ExportDocs->prepareCsv(number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'))));			

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