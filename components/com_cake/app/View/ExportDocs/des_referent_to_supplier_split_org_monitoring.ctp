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

$qta_totale = 0;
$importo_totale = 0;
$html = '';
foreach($newResults as $resultOrg) {
	
	$html .= '<div class="h1Pdf">';
	$html .= $resultOrg['Organization']['name'];
	$html .= '</div>';
	
	$html .= '	<table cellpadding="0" cellspacing="0">';
	$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
	$html .= '		<tr>';
	$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('Bio').'</th>';
		
	if($showCodice=='Y') {
		$html .= '			<th width="'.$output->getCELLWIDTH50().'">'.__('Codice').'</th>';
		$html .= '			<th width="'.$output->getCELLWIDTH180().'">'.__('Name').'</th>';
	}
	else
		$html .= '			<th width="'.($output->getCELLWIDTH180()+$output->getCELLWIDTH70()).'">'.__('Name').'</th>';
	
	if($isToValidate) {
	        $html .= '<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;">Colli<br />completati</th>';
	        $html .= '<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">Mancano<br />per il collo</th>';
	}
	else {
	        $html .= '<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;"></th>';
	        $html .= '<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;"></th>';    
	}
	 
	 		
	$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.__('qta').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH80().'" style="text-align:center;">'.__('PrezzoUnita').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH80().'" style="text-align:right;">'.__('Importo').'</th>';
	$html .= '	</tr>';
	$html .= '	</thead><tbody>';


	$i=0;
	$tot_qta = 0;
	$tot_importo = 0;
	if(isset($resultOrg['Cart']))
	foreach($resultOrg['Cart'] as $result) {

			$name = $result['Article']['name'].' '.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);		
			$bio = $result['Article']['bio'];
			
			$html .= '<tr>';
			
			$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($i+1).'</td>';
			$html .= '	<td width="'.$output->getCELLWIDTH30().'">';
			if($bio=='Y') $html .= 'Bio';
			$html .= '</td>';

			
			if($showCodice=='Y') {
				$html .= '			<td width="'.$output->getCELLWIDTH50().'">'.$result['Article']['codice'].'</td>';
				$html .= '			<td width="'.$output->getCELLWIDTH180().'">'.$name.'</td>';
			}
			else
				$html .= '			<td width="'.($output->getCELLWIDTH180()+$output->getCELLWIDTH70()).'">'.$name.'</td>';
				
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
                
            $html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">';
            if($result['ArticlesOrder']['pezzi_confezione']>1)  $html .= $colli_completi;
            else $html .= '';
            $html .= '</td>';
            $html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">';
            if($result['ArticlesOrder']['pezzi_confezione']>1) {
            		
                if($differenza_da_ordinare != $result['ArticlesOrder']['pezzi_confezione'])  
                    $html .= '<span style="background-color: #FF0000;padding: 0 5px;"> '.$differenza_da_ordinare.' </span> (collo da '.$result['ArticlesOrder']['pezzi_confezione'].')';
                else
                    $html .= '0 (collo da '.$result['ArticlesOrder']['pezzi_confezione'].')';
            }
            else 
                    $html .= '';
            $html .= '</td>';
        }
        else {
                $html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;"></td>';
                $html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;"></td>';    
        }
				
				
        $html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.$result['Cart']['qta'].'</td>';
        $html .= '<td width="'.$output->getCELLWIDTH80().'" style="text-align:center;">'.$this->App->getArticlePrezzo($result['ArticlesOrder']['prezzo']).'</td>';
        $html .= '<td width="'.$output->getCELLWIDTH80().'" style="text-align:right;">'.number_format($result['Cart']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';

        $html .= '</tr>';	
		
	$tot_importo += $result['Cart']['importo'];
	$tot_qta += $prezzo = $result['Cart']['qta'];
	
	$i++;

						
	}  // loop articoli
 	

	$html .= '<tr>';

	if($showCodice=='Y') 
		$colspan = '5';
	else
		$colspan = '4';

	/*
	 * totali singolo GAS
	 */
	$html .= '	<th width="'.$output->getCELLWIDTH20().'"></th>';
	$html .= '	<th colspan="'.$colspan.'" style="text-align:right;">Quantit&agrave;&nbsp;totale&nbsp;</th>';
	$html .= '	<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">&nbsp;'.$tot_qta.'</th>';
	$html .= '	<th width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH80()).'" colspan="2" style="text-align:right;">Importo totale&nbsp;'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</th>';			

	$html .= '</tr>';

	$html .= '</tbody></table><br />';

	$qta_totale += $tot_qta;
	$importo_totale += $tot_importo;

	$tot_qta=0;
	$tot_qta_single_article = 0;
	$tot_importo_single_article = 0;	
	
} // loop Organizations


/* 
 * totali
 */
$html .= '<div class="h1Pdf" style="text-align:center">';
$html .= 'Importo totale&nbsp;'.number_format($importo_totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato);
$html .= '</div>';

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
?>