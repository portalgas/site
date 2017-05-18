<?php 
/*
 * T O - A R T I C L E S - W E I G H T
 * Documento con articoli per peso
 */
 
 /*
echo "<pre>";
print_r($results);
echo "</pre>";
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

$html = '';	
if(!empty($results)) {

	$peso_gr_totale = 0;
	$peso_ml_totale = 0;
	$peso_pz_totale = 0;
	
	$html .= '	<table cellpadding="0" cellspacing="0">';
	$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
	$html .= '		<tr>';
	$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH40().'">'.__('Bio').'</th>';
	
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
		$html .= '<th width="'.$output->getCELLWIDTH50().'">'.__('Codice').'</th>';
		$html .= '<th colspan="2" width="'.$output->getCELLWIDTH200().'">'.__('Name').'</th>';
	}
	else
		$html .= '<th colspan="2" width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'">'.__('Name').'</th>';
	
	$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH80().'">Kg</th>';
	$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH80().'">Lt</th>';
	$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH80().'">Pz</th>';
	$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH80().'">Quantit&agrave acquitata</th>';
	$html .= '	</thead><tbody>';
	$html .= '</tr>';
	
	$i=0;
	foreach($results as $article_id => $result) {
	
		$i++;
		
		if($result['Article']['bio']=='Y')
			$bio = 'Si';
		else
			$bio = 'No';
		
		$html .= '<tr>';
		$html .= '<td width="'.$output->getCELLWIDTH20().'">'.$i.'</td>';
		$html .= '<td width="'.$output->getCELLWIDTH40().'">'.$bio.'</td>';
	
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
			$html .= '<td width="'.$output->getCELLWIDTH50().'">'.$result['Article']['codice'].'</td>';
			$html .= '<td width="'.$output->getCELLWIDTH50().'">';
			if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
				$html .= '<img width="40" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
			}				
			$html .= '</td>';		
			$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.$result['ArticlesOrder']['name'].'</td>';
		}
		else {
			$html .= '<td width="'.$output->getCELLWIDTH50().'">';
			if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
				$html .= '<img width="40" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
			}				
			$html .= '</td>';
			

			/*
			 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
			 */			
			if(!empty($result['ArticlesOrder']['name']))
				$name = $result['ArticlesOrder']['name'];
			else
				$name = $result['Article']['name'];			
			$html .= '<td width="'.$output->getCELLWIDTH200().'">'.$name.'</td>';
	
		}
			
		$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH80().'">'.($result['Peso']['kg'] / 1000).'</td>';
		$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH80().'">'.($result['Peso']['lt'] / 1000).'</td>';
		$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH80().'">'.$result['Peso']['pz'].'</td>';
		$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH80().'">'.$result['ArticlesOrder']['qta_cart'].'</td>';
		$html .= '	</tr>';
		$html .= '	</thead><tbody>';
		
		$peso_gr_totale += $result['Peso']['kg'];
		$peso_ml_totale += $result['Peso']['lt'];
		$peso_pz_totale += $result['Peso']['pz'];
	}
	$html .= '<tr>';
	$html .= '<th></th>';
	$html .= '<th></th>';
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
		$html .= '<th></th>'; 
	$html .= '<th colspan="2" style="text-align:right;">Totali</th>';
	$html .= '<th style="text-align:center;">'.($peso_gr_totale / 1000).' kg</th>';
	$html .= '<th style="text-align:center;" >'.($peso_ml_totale / 1000).' lt</th>';
	$html .= '<th style="text-align:center;" >'.$peso_pz_totale.' pz</th>';
	$html .= '<th></th>';
	$html .= '</tr>';
	$html .= '</tbody></table>';
}
else
	$html .= '<div class="h4PdfNotFound">Non ci sono acquisti.</div>';

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

			
// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
$html .= $output->Output($fileData['fileName'].'.pdf', 'D');
?>