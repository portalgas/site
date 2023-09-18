<?php 
/*
 * T O - A R T I C L E S 
 * Documento con articoli aggregati (per il produttore)
 */
$debug = false;

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

$html = $this->ExportDocs->organization($user);
$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

if(isset($results['Delivery']) && !empty($results['Delivery']))
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	$html = $this->ExportDocs->delivery($user, $result['Delivery']);
	$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {

			$html = $this->ExportDocs->suppliersOrganization($order['SuppliersOrganization']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


			$html = '';
			$html .= '	<table class="table table-hover" cellpadding="0" cellspacing="0">';
			$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
			$html .= '		<tr>';
			if(($order['Order']['hasTrasport']=='Y' || $order['Order']['hasCostMore']=='Y' || $order['Order']['hasCostLess']=='Y') && $trasportAndCost=='Y') {
				$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('Bio').'</th>';
				
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y') {
					$html .= '			<th width="'.$output->getCELLWIDTH50().'">'.__('Codice').'</th>';
					$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.__('Name').'</th>';
				}
				else 
					$html .= '			<th width="'.$output->getCELLWIDTH200().'">'.__('Name').'</th>';
				
				$html .= '			<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.__('pezzi_confezione_short').'</th>';				
				$html .= '			<th width="'.$output->getCELLWIDTH80().'" style="text-align:center;">'.__('qta').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.__('PrezzoUnita').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH50().'" style="text-align:right;">'.__('Importo').'</th>';

				$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('TrasportAndCost').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Totale').'</th>';
			}
			else {
				$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('Bio').'</th>';
				
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y') {
					$html .= '			<th width="'.$output->getCELLWIDTH50().'">'.__('Codice').'</th>';
					$html .= '			<th width="'.$output->getCELLWIDTH300().'">'.__('Name').'</th>';
				}
				else
					$html .= '			<th width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH70()).'">'.__('Name').'</th>';
				
				$html .= '			<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.__('pezzi_confezione_short').'</th>';				
				$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.__('qta').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.__('PrezzoUnita').'</th>';
				$html .= '			<th width="'.$output->getCELLWIDTH50().'" style="text-align:right;">'.__('Importo').'</th>';
			}
			$html .= '	</tr>';
			$html .= '	</thead><tbody>';

			$tot_qta = 0;
			$tot_importo = 0;
			$pezzi_confezione = 1;
			$order_id_old = 0;
			$article_organization_id_old=0;
			$article_id_old = 0;
			$i=0;
			foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
			
				if(!empty($order_id_old) && // salto la prima volta
				   ($order_id_old != $order['ArticlesOrder'][$numArticlesOrder]['order_id'] ||
				    $article_organization_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'] ||
				    $article_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_id'])) {
				
					// $bio = $order['Article'][$numArticlesOrder]['bio'];
					
					$html .= '<tr>';
					
					$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($i+1).'</td>';
					$html .= '	<td width="'.$output->getCELLWIDTH30().'">';
					if($bio=='Y') $html .= 'Bio';
					$html .= '</td>';

					
					if(($order['Order']['hasTrasport']=='Y' || $order['Order']['hasCostMore']=='Y' || $order['Order']['hasCostLess']=='Y') && $trasportAndCost=='Y') {

						if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y') {
							$html .= '			<td width="'.$output->getCELLWIDTH50().'">'.$codiceArticle.'</td>';
							$html .= '			<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.$name.'</td>';
						}
						else
							$html .= '			<td width="'.$output->getCELLWIDTH200().'">'.$name.'</td>';
							
						$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$pezzi_confezione.'</td>';
						$html .= '<td width="'.$output->getCELLWIDTH80().'" style="text-align:center;">'.$tot_qta_single_article;
						
						if($pezzi_confezione1=='Y') {
							/*
							 * colli_completi  	
							*/
							$html .= $this->App->getColli($tot_qta_single_article, $pezzi_confezione);
						}
						$html .= '</td>';
						
						$html .= '<td width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.$this->App->getArticlePrezzo($prezzo).'</td>';
						$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:right;">'.number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
	
						$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;"></td>';
						$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;"></td>';
					}
					else {
						
						if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y') {
							$html .= '			<td width="'.$output->getCELLWIDTH50().'">'.$codiceArticle.'</td>';
							$html .= '			<td width="'.$output->getCELLWIDTH300().'">'.$name.'</td>';
						}
						else
							$html .= '			<td width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH70()).'">'.$name.'</td>';
						
						$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$pezzi_confezione.'</td>';
						$html .= '			<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.$tot_qta_single_article;
						
						if($pezzi_confezione1=='Y') {
							/*
							 * colli_completi 
							*/
							$html .= $this->App->getColli($tot_qta_single_article, $pezzi_confezione);
							
						}
						$html .= '</td>';
						
						$html .= '<td width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.$this->App->getArticlePrezzo($prezzo).'</td>';
						$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:right;">'.number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
					}
					$html .= '</tr>';	
					
					$i++;

					$bio = '';
					$codiceArticle = '';
					$name = '';
					$prezzo = '';
					$pezzi_confezione = 1;
					$tot_qta_single_article = 0;
					$tot_importo_single_article = 0;
						
				}  
				
				/*
				 * gestione qta e importi
				 * */
				if($order['Cart'][$numArticlesOrder]['qta_forzato']>0) {
					$qta = $order['Cart'][$numArticlesOrder]['qta_forzato'];
					$qta_modificata = true;
				}	
				else {
					$qta = $order['Cart'][$numArticlesOrder]['qta'];
					$qta_modificata = false;
				}
				$importo_modificato = false;
				if($order['Cart'][$numArticlesOrder]['importo_forzato']==0) {
					if($order['Cart'][$numArticlesOrder]['qta_forzato']>0) 
						$importo = ($order['Cart'][$numArticlesOrder]['qta_forzato'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
					else {
						$importo = ($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']);
					}	
				}	
				else {
					$importo = $order['Cart'][$numArticlesOrder]['importo_forzato'];
					$importo_modificato = true;
				}
								
				$tot_importo += $importo;
				$tot_qta += $qta;
								
				$bio = $order['Article'][$numArticlesOrder]['bio'];
				$codiceArticle = $order['Article'][$numArticlesOrder]['codice'];
				/*
				 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
				 */					
				 if(!empty($order['ArticlesOrder'][$numArticlesOrder]['name']))
					 $name = $order['ArticlesOrder'][$numArticlesOrder]['name'];
				 else
					 $name = $order['Article'][$numArticlesOrder]['name'];
				$name = $name.' '.$this->App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']);
				$pezzi_confezione = $order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione']; 
				$prezzo = $order['ArticlesOrder'][$numArticlesOrder]['prezzo'];
				$tot_qta_single_article += $qta;
				$tot_importo_single_article += $importo;
				$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
				$order_id_old = $order['ArticlesOrder'][$numArticlesOrder]['order_id'];
				$article_organization_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'];
				$article_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_id'];
								
			}  // end foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
			 	
			$html .= '<tr>';

			
			$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($i+1).'</td>';
			$html .= '	<td width="'.$output->getCELLWIDTH30().'">';
			if($bio=='Y') $html .= 'Bio';
			$html .= '</td>';
						
			if(($order['Order']['hasTrasport']=='Y' || $order['Order']['hasCostMore']=='Y' || $order['Order']['hasCostLess']=='Y') && $trasportAndCost=='Y') {
				
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y') {
					$html .= '			<td width="'.$output->getCELLWIDTH50().'">'.$codiceArticle.'</td>';
					$html .= '			<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.$name.'</td>';
				}
				else
					$html .= '			<td width="'.$output->getCELLWIDTH200().'">'.$name.'</td>';
				
				$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$pezzi_confezione.'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH80().'" style="text-align:center;">'.$tot_qta_single_article;
				
				if($pezzi_confezione1=='Y') {
					/*
					 * colli_completi 
					*/
					$html .= $this->App->getColli($tot_qta_single_article, $pezzi_confezione);
												
				}
				$html .= '</td>';
				$html .= '<td width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.$this->App->getArticlePrezzo($prezzo).'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:right;">'.number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
			
				$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;"></td>';
				$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;"></td>';
			}
			else {
	
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y') {
					$html .= '			<td width="'.$output->getCELLWIDTH50().'">'.$codiceArticle.'</td>';
					$html .= '			<td width="'.$output->getCELLWIDTH300().'">'.$name.'</td>';
				}
				else
					$html .= '			<td width="'.($output->getCELLWIDTH300()+$output->getCELLWIDTH70()).'">'.$name.'</td>';
				
				$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$pezzi_confezione.'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.$tot_qta_single_article;

				if($pezzi_confezione1=='Y') {
					/*
					 * colli_completi  	
					*/
					$html .= $this->App->getColli($tot_qta_single_article, $pezzi_confezione);
				}
				$html .= '</td>';
				$html .= '<td width="'.$output->getCELLWIDTH60().'" style="text-align:center;">'.$this->App->getArticlePrezzo($prezzo).'</td>';
				$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:right;">'.number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';			
			}
			
					
			$html .= '</tr>';	
			
			
			/*
			 * ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
			*/
			if($debug) {
				echo '<h2>Totali</h2>';	
				echo '<br />tot_importo (ciclo degli importi) '.$tot_importo;
				echo '<br />summaryOrder.totale_importo (somma degli importi aggragati + importo trasporto + costo aggiuntivo - sconto) '.$resultsSummaryOrder[0]['totale_importo'];
				echo '<br />importo del trasporto '.$order['Order']['trasport'];
				echo '<br />importo del costo aggiuntivo '.$order['Order']['cost_more'];
				echo '<br />importo dello sconto '.$order['Order']['cost_less'];
				echo '<br />tot_importo + importo del trasporto + costo aggiuntivo - sconto '.($tot_importo + $order['Order']['trasport'] + $order['Order']['cost_more'] + ($order['Order']['cost_less']));
			}
			
			if(!empty($resultsSummaryOrder[0]['totale_importo']) && 
		       $resultsSummaryOrder[0]['totale_importo'] != ($tot_importo + $order['Order']['trasport'] + $order['Order']['cost_more'] + ($order['Order']['cost_less'])).'') { 
				$tmp_importo = $resultsSummaryOrder[0]['totale_importo'];
				$importo_modificato = true;
			}
			else {
				$tmp_importo = $tot_importo;
				$importo_modificato = false;
			}
			
			if(($order['Order']['hasTrasport']=='Y' || $order['Order']['hasCostMore']=='Y' || $order['Order']['hasCostLess']=='Y') && $trasportAndCost=='Y') {
				
				/*
				 * se modificato, quindi in SummaryOrder e' gia' compreso l'importo del trasporto
				 */
				if($importo_modificato)
					$importo_completo = ($tmp_importo);
				else
					$importo_completo = ($tmp_importo + $order['Order']['trasport'] + $order['Order']['cost_more'] + (-1 * $order['Order']['cost_less']));
			}
			
			if($debug) {
				echo '<h2>Totali</h2>';
				echo '<br />importo_completo (totale che stampo) '.$importo_completo;
			}

			$html .= '<tr>';

			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y') 
				$colspan = '4';
			else
				$colspan = '3';
			
			
			if(($order['Order']['hasTrasport']=='Y' || $order['Order']['hasCostMore']=='Y' || $order['Order']['hasCostLess']=='Y') && $trasportAndCost=='Y') {
				$html .= '	<th width="'.$output->getCELLWIDTH20().'"></th>';
				$html .= '	<th colspan="'.$colspan.'" style="text-align:right;">'.__('qta_tot').'</th>';
				$html .= '	<th width="'.$output->getCELLWIDTH80().'" style="text-align:center;">&nbsp;'.$tot_qta.'</th>';
				$html .= '	<th width="'.($output->getCELLWIDTH60()+$output->getCELLWIDTH50()).'" colspan="2" style="text-align:right;">'.__('Importo_totale').'&nbsp;'.number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</th>';
			
				$html .= '<th style="text-align:right;">';
				if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') $html .= __('TrasportShort').' '.$order['Order']['trasport_e'].'<br />';
				if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') $html .= __('CostMoreShort').' '.$order['Order']['cost_more_e'].'<br />';
				if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00') $html .= __('CostLessShort').' '.$order['Order']['cost_less_e'];
				$html .= '</th>';

				$html .= '<th style="text-align:right;">'.number_format($importo_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
			}
			else {
				$html .= '	<th width="'.$output->getCELLWIDTH20().'"></th>';
				$html .= '	<th colspan="'.$colspan.'" style="text-align:right;">'.__('qta_tot').'</th>';
				$html .= '	<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">&nbsp;'.$tot_qta.'</th>';
				$html .= '	<th width="'.($output->getCELLWIDTH60()+$output->getCELLWIDTH50()).'" colspan="2" style="text-align:right;">'.__('Importo_totale').'&nbsp;'.number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</th>';			
			}
			$html .= '</tr>';
	
			
			$html .= '</tbody></table>';
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

			$html = '';
			$html = $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
				
		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
			
		$html = '';
		$html = $output->getLegenda();
		$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		
	}
	else {
		$html = $this->ExportDocs->suppliersOrganization($result['Delivery']['Order'][0]['SuppliersOrganization']);
		$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
			
		$html = '<div class="h4PdfNotFound">'.__('export_docs_not_found').'</div>';
		$output->writeHTMLCell(0,0,15,40, $css.$html, $border=0, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}	
}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 
			
// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>