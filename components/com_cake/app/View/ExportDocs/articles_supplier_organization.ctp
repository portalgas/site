<?php 
/*
 * Elenco articoli di un produttore
*/

if($this->layout=='pdf') {
	App::import('Vendor','xtcpdf');
	
	$output = new XTCPDF($organization, PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$output->headerText = $fileData['fileTitle'];
			
	// add a page
	$output->AddPage();
	$css = $output->getCss();
}
else 
if($this->layout=='ajax') {
	App::import('Vendor','xtcpreview');
	$output = new XTCPREVIEW();
	$css = $output->getCss();
}



/*
 * per gli articoli associati all'ordine
 */
if(isset($DeliveryLabel)) 
	$html = $this->ExportDocs->suppliersOrganizationDelivery($supplier['Supplier'], $DeliveryLabel);
else 
	$html = $this->ExportDocs->suppliersOrganization($supplier['Supplier']);


$output->writeHTML($css.$html , $ln=false, $fill=false, $reseth=true, $cell=true, $align='');
	
if(!empty($results)) {
	
$html = '';
$html .= '	<table class="table table-hover" cellpadding="0" cellspacing="0">';
$html .= '<thead>'; // con questo TAG mi ripete l'intestazione della tabella
$html .= '<tr>';
$html .= '<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
$html .= '<th width="'.$output->getCELLWIDTH30().'">'.__('Bio').'</th>';

if($filterType=='Y')
	$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Type').'</th>';

if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y' && $filterCategory=='Y') {
	$html .= '<th width="'.$output->getCELLWIDTH80().'">'.__('Category').'</th>';
	
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
		$html .= '<th width="'.$output->getCELLWIDTH60().'">'.__('Codice').'</th>';
		
		if($isReferenteSupplierOrganization) {// ho il campo STATO in +
			if($filterType=='Y')
				$html .= '<th width="'.$output->getCELLWIDTH100().'">'.__('Name').'</th>';
			else 
				$html .= '<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'">'.__('Name').'</th>';
		} 
		else {
			if($filterType=='Y')
				$html .= '<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH40()).'">'.__('Name').'</th>';
			else
				$html .= '<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH40()+$output->getCELLWIDTH100()).'">'.__('Name').'</th>';
		}
	}
	else {
		if($isReferenteSupplierOrganization) { // ho il campo STATO in +
			if($filterType=='Y')
				$html .= '<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH60()).'">'.__('Name').'</th>';
			else 
				$html .= '<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH60()+$output->getCELLWIDTH100()).'">'.__('Name').'</th>';
		}
		else {
			if($filterType=='Y')
				$html .= '<th width="'.$output->getCELLWIDTH200().'">'.__('Name').'</th>';
			else
				$html .= '<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH100()).'">'.__('Name').'</th>';
		}
	}
}
else  {
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
		$html .= '			<th width="'.$output->getCELLWIDTH60().'">'.__('Codice').'</th>';
		if($isReferenteSupplierOrganization) { // ho il campo STATO in +
			if($filterType=='Y')
				$html .= '<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH80()).'">'.__('Name').'</th>';
			else
				$html .= '<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH80()+$output->getCELLWIDTH100()).'">'.__('Name').'</th>';
		}
		else {
			if($filterType=='Y')
				$html .= '<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()).'">'.__('Name').'</th>';
			else
				$html .= '<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()+$output->getCELLWIDTH100()).'">'.__('Name').'</th>';
		}
	}
	else {
		if($isReferenteSupplierOrganization) { // ho il campo STATO in +
			if($filterType=='Y')
				$html .= '<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH40()).'">'.__('Name').'</th>';
			else
				$html .= '<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH40()+$output->getCELLWIDTH100()).'">'.__('Name').'</th>';
		}
		else {
			if($filterType=='Y')
				$html .= '<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH80()).'">'.__('Name').'</th>';
			else
				$html .= '<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH80()+$output->getCELLWIDTH100()).'">'.__('Name').'</th>';
		}
	}
}
$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH50().'">'.__('Conf').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('PrezzoUnita').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('Prezzo/UM').'</th>';

if($isReferenteSupplierOrganization)
	$html .= '			<th width="'.$output->getCELLWIDTH40().'">Visibile</th>';

$html .= '	</tr>';
$html .= '	</thead><tbody>';

if(isset($results) && !empty($results))
foreach($results as $numArticle => $result) {

	if(isset($result['ArticlesOrder']['name']))	
		$name = $result['ArticlesOrder']['name'];
	else
		$name = $result['Article']['name'];
	
	$html .= '<tr>';
	$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($numArticle+1).'</td>';
	$html .= '	<td width="'.$output->getCELLWIDTH30().'">';
	if($result['Article']['bio']=='Y') $html .= 'Bio';
	$html .= '</td>';

	if($filterType=='Y') {
		$html .= ' <td width="'.$output->getCELLWIDTH100().'">';
		if(!empty($result['ArticlesType'])) {
			foreach($result['ArticlesType'] as $articlesType)
				$html .= $articlesType['ArticlesType']['descrizione'].'<br />';
		}
		$html .= '</td>';
	}
		
	if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y' && $filterCategory=='Y') {
		
		$html .= '<td width="'.$output->getCELLWIDTH80().'">'.$result['CategoriesArticle']['name'].'</td>';
		
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
			
			$html .= '<td width="'.$output->getCELLWIDTH60().'">'.$result['Article']['codice'].'</td>';
					
			if($isReferenteSupplierOrganization) {// ho il campo STATO in +
				if($filterType=='Y')
					$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$name.'</td>';
				else 
					$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH100()).'">'.$name.'</td>';
			} 
			else {
				if($filterType=='Y')
					$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH40()).'">'.$name.'</td>';
				else
					$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH40()+$output->getCELLWIDTH100()).'">'.$name.'</td>';
			}
						
		}
		else {
			if($isReferenteSupplierOrganization) { // ho il campo STATO in +
				if($filterType=='Y')
					$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH60()).'">'.$name.'</td>';
				else 
					$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH60()+$output->getCELLWIDTH100()).'">'.$name.'</td>';
			}
			else {
				if($filterType=='Y')
					$html .= '<td width="'.$output->getCELLWIDTH200().'">'.$name.'</td>';
				else
					$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH100()).'">'.$name.'</td>';
			}			
		}
		

	}
	else  {
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
			$html .= '<td width="'.$output->getCELLWIDTH60().'">'.$result['Article']['codice'].'</td>';
			if($isReferenteSupplierOrganization) { // ho il campo STATO in +
				if($filterType=='Y')
					$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH80()).'">'.$name.'</td>';
				else
					$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH80()+$output->getCELLWIDTH100()).'">'.$name.'</td>';
			}
			else {
				if($filterType=='Y')
					$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()).'">'.$name.'</td>';
				else
					$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()+$output->getCELLWIDTH100()).'">'.$name.'</td>';
			}
		}
		else {
			if($isReferenteSupplierOrganization) { // ho il campo STATO in +
				if($filterType=='Y')
					$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH40()).'">'.$name.'</td>';
				else
					$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH40()+$output->getCELLWIDTH100()).'">'.$name.'</td>';
			}
			else {
				if($filterType=='Y')
					$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH80()).'">'.$name.'</td>';
				else
					$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH80()+$output->getCELLWIDTH100()).'">'.$name.'</td>';
			}
		}
	}
		
	$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH50().'">'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
	$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH70().'">'.$result['Article']['prezzo_e'].'</td>';
	$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH70().'">'.$this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';

	if($isReferenteSupplierOrganization)
		$html .= '			<td style="text-align:center;" width="'.$output->getCELLWIDTH40().'">'.$this->App->traslateEnum($result['Article']['stato']).'</td>';
	
	$html .= '</tr>';	

	if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y' && !empty($result['Article']['ingredienti']) && $filterIngredienti=='Y') {
		$html .= '<tr>';
		$html .= '	<td width="'.$output->getCELLWIDTH20().'"></td>';
		$html .= '	<td width="'.$output->getCELLWIDTH30().'"></td>';
		$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH80()+$output->getCELLWIDTH200()+$output->getCELLWIDTH50()+$output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'">';
		$html .= '<b>'.__('Ingredienti').'</b>:&nbsp;'.strip_tags($result['Article']['ingredienti']).'</td>';
		$html .= '</tr>';
	}
	if(!empty($result['Article']['nota']) && $filterNota=='Y') {
		$html .= '<tr>';
		$html .= '	<td width="'.$output->getCELLWIDTH20().'"></td>';
		$html .= '	<td width="'.$output->getCELLWIDTH30().'"></td>';
		$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH80()+$output->getCELLWIDTH200()+$output->getCELLWIDTH50()+$output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'">';
		$html .= '<b>'.__('Nota').'</b>:&nbsp;'.strip_tags($result['Article']['nota']).'</td>';
		$html .= '</tr>';
	}	
	
}
$html .= '</tbody></table>';

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
} // end if(!empty($results)) 
	
// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>