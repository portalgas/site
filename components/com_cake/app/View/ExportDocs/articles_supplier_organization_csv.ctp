<?php 
$csv = array(
		'N' => 'N',
		'bio' => __('Bio'));

if($filterType=='Y') 
	$csv += array('type' => __('Type'));
if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y' && $filterCategory=='Y')
	$csv += array('category' => __('Category'));

if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
	$csv += array('codice' => __('codice'));

$csv += array('name' => __('Name'));

	
$csv += array(
		'qta' => __('Conf'),
		'Prezzo' => $this->ExportDocs->prepareCsvAccenti(__('PrezzoUnita')),
		'um' => 'UM',
		'prezzo_um' => __('Prezzo/UM')
		);

if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y' && $filterIngredienti=='Y')
	$csv += array('ingredienti' => __('ingredienti'));

if($filterNota=='Y')
	$csv += array('nota' => __('nota'));

$csv += array(
		'pezzi_confezione' => __('pezzi_confezione'),
		'qta_minima' => $this->ExportDocs->prepareCsvAccenti(__('qta_minima')),
		'qta_massima' => $this->ExportDocs->prepareCsvAccenti(__('qta_massima')),
		'qta_multipli' => $this->ExportDocs->prepareCsvAccenti(__('qta_multipli')),
		'qta_minima_order' => $this->ExportDocs->prepareCsvAccenti(__('qta_minima_order')),
		'qta_massima_order' => $this->ExportDocs->prepareCsvAccenti(__('qta_massima_order'))		
		);
		
if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')	
	$csv += array('alert_to_qta' => __('alert_to_qta'));

if($isReferenteSupplierOrganization) // ho il campo STATO in +
	$csv += array('visibile' => 'Visibile');
	
$csv += array('flag_presente_articlesorders' => "Presente nell'elenco degli articoli che si possono associare ad un ordine");

$headers = array('csv' => $csv); 


$data = array();
if(isset($results) && !empty($results))
foreach($results as $numArticle => $result) {

	if($result['Article']['bio']=='Y') 
		$bio = 'Si';
	else 
		$bio = 'No';
	
	$data[$numArticle]['csv'] = array(
			'N' => ($numArticle+1),
			'bio' => $bio);
	
	if($filterType=='Y') {
		$tmp = "";
		if(!empty($result['ArticlesType'])) {				foreach($result['ArticlesType'] as $articlesType)
				$tmp .= $articlesType['descrizione']." ";			}		$data[$numArticle]['csv'] += array('type' => $tmp);	}
			if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y' && $filterCategory=='Y') 
		$data[$numArticle]['csv'] += array('category' => $result['CategoriesArticle']['name']);
	
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
		$data[$numArticle]['csv'] += array('codice' => $result['Article']['codice']);
	
		if(isset($result['ArticlesOrder']['name']))
			$data[$numArticle]['csv'] += array('name' => $result['ArticlesOrder']['name']);
		else
			$data[$numArticle]['csv'] += array('name' => $result['Article']['name']);
			
	
			
	$data[$numArticle]['csv'] += array(
			'qta' => $this->ExportDocs->prepareCsv($this->App->getArticleConf($result['Article']['qta'], $result['Article']['um'])),
		 	'Prezzo' => $result['Article']['prezzo_'].' ',
			'um' => $result['Article']['um'],
			'prezzo_um' => $this->ExportDocs->prepareCsv($this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']))
			);
			
	if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y' && $filterIngredienti=='Y')
		$data[$numArticle]['csv'] += array('ingredienti' => strip_tags($result['Article']['ingredienti']));
		
	if($filterNota=='Y')
		$data[$numArticle]['csv'] += array('nota' => strip_tags($result['Article']['nota']));
	
	/*
	 * per gli articoli associati all'ordine
	*/
	if(isset($result['ArticlesOrder'])) {
		if($result['ArticlesOrder']['qta_massima_order']==0)
			$qta_massima_order = "Nessuna";
		else
			$qta_massima_order = $result['ArticlesOrder']['qta_massima_order'];
		
		if($result['ArticlesOrder']['qta_minima_order']==0)
			$qta_minima_order = "Nessuna";
		else
			$qta_minima_order = $result['ArticlesOrder']['qta_minima_order'];
				
		$data[$numArticle]['csv'] += array(
				'pezzi_confezione' => $result['ArticlesOrder']['pezzi_confezione'],
				'qta_minima' => $result['ArticlesOrder']['qta_minima'],
				'qta_massima' => $result['ArticlesOrder']['qta_massima'],
				'qta_multipli' => $result['ArticlesOrder']['qta_multipli'],
				'qta_minima_order' => $qta_minima_order,
				'qta_massima_order' => $qta_massima_order
		);
		
		if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
			$data[$numArticle]['csv'] += array('alert_to_qta' => $result['Article']['alert_to_qta']);		
	}
	else {
		if($result['Article']['qta_massima_order']==0)
			$qta_massima_order = "Nessuna";
		else
			$qta_massima_order = $result['Article']['qta_massima_order'];
		
		if($result['Article']['qta_minima_order']==0)
			$qta_minima_order = "Nessuna";
		else
			$qta_minima_order = $result['Article']['qta_minima_order'];
				
		$data[$numArticle]['csv'] += array(
				'pezzi_confezione' => $result['Article']['pezzi_confezione'],
				'qta_minima' => $result['Article']['qta_minima'],
				'qta_massima' => $result['Article']['qta_massima'],
				'qta_multipli' => $result['Article']['qta_multipli'],			
				'qta_minima_order' => $qta_minima_order,
				'qta_massima_order' => $qta_massima_order
				);
		
		if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
			$data[$numArticle]['csv'] += array('alert_to_qta' => $result['Article']['alert_to_qta']);
	}
					
	if($isReferenteSupplierOrganization) // ho il campo STATO in +
		$data[$numArticle]['csv'] += array('visibile' => $this->App->traslateEnum($result['Article']['stato']));
		
	$data[$numArticle]['csv'] += array('flag_presente_articlesorders' => $this->App->traslateEnum($result['Article']['flag_presente_articlesorders']));
}

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