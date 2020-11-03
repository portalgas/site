<?php 
/*
 * T O - A R T I C L E S 
 * Documento con articoli aggregati (per il produttore)
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

foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	$html = $this->ExportDocs->delivery($result['Delivery']);
	$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {

			$html = $this->ExportDocs->suppliersOrganization($order['SuppliersOrganization']);
			$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


			$html = '';
			$html .= '	<table cellpadding="0" cellspacing="0">';
			$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
			$html .= '		<tr>';
			$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
			
			$width = ($output->getCELLWIDTH100()+$output->getCELLWIDTH20());
			if(!$orderToQtaMinimaOrder) 
				$width = ((int)$width + $output->getCELLWIDTH50()+$output->getCELLWIDTH70());
			else
			if(!$orderToQtaMassima)
				$width = ((int)$width + $output->getCELLWIDTH50());
			else
			if($orderToValidate)
				$width = ((int)$width + $output->getCELLWIDTH70()+$output->getCELLWIDTH80());
			
			$html .= '			<th width="'.$width.'">'.__('Name').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.__('qta').'</th>';

			if($orderToQtaMinimaOrder) {
				$html .= '			<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.__('qta_minima_order_short').'</th>';	
				$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">ancora</th>';	
			}
			
			if($orderToQtaMassima)
				$html .= '			<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.__('qta_massima_order_short').'</th>';

			if($orderToValidate) {
				$html .= '<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">Colli<br />completati</th>';
				$html .= '<th width="'.$output->getCELLWIDTH80().'" style="text-align:center;">Mancano<br />per il collo</th>';
			}
						
			$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.__('PrezzoUnita').'</th>';
			$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';
			$html .= '	</tr>';
			$html .= '	</thead><tbody>';

			$colli_completi = 0;
			$differenza_da_ordinare = 0;
			$pezzi_confezione = 0;
			$qta_cart = 0;
			$qta_minima_order = 0;
			$qta_massima_order = 0;
			$tot_qta = 0;
			$tot_importo = 0;
			$order_id_old = 0;
			$article_organization_id_old=0;
			$article_id_old = 0;
			$i=0;

			foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {

				if(!empty($order_id_old) && // salto la prima volta
				   ($order_id_old != $order['ArticlesOrder'][$numArticlesOrder]['order_id'] ||
				    $article_organization_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'] ||
				    $article_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_id'])) {

					$bio = $order['Article'][$numArticlesOrder]['bio'];
					
					$html .= '<tr>';
					$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($i+1).'</td>';
					
					$html .= '<td width="'.$width.'">'.$name.'</td>';
					$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$tot_qta_single_article.'</td>';
		
					if($orderToQtaMinimaOrder) {
						$differenza = (intval($qta_minima_order) - intval($qta_cart));
						$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">';
						if($differenza > 0)
							$html .= '<span class="box_evidenza"> '.$qta_minima_order.' </span>';
						else
							$html .= $qta_minima_order;
						$html .= '</td>';
						$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">';
						if($differenza > 0)
							$html .= $differenza;
						else
							$html .= '-';						
						$html .= '</td>';
					} 
					
					if($orderToQtaMassima)
						$html .= '			<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$qta_massima_order.'</td>';
					
					if($orderToValidate) {
						$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">';

						if($colli1=='Y')
							$html .= $colli_completi;
						else {
							if($pezzi_confezione>1)  $html .= $colli_completi;
							else $html .= '';							
						}

						$html .= '</td>';
						$html .= '<td width="'.$output->getCELLWIDTH80().'" style="text-align:center;">';

						if($colli1=='Y') {
							if($differenza_da_ordinare!=$pezzi_confezione)  
								$html .= '<span class="box_evidenza"> '.$differenza_da_ordinare.' </span> (collo da '.$pezzi_confezione.')';
							else {
								if($pezzi_confezione==1)
									$html .= '(collo da '.$pezzi_confezione.')';
								else
									$html .= '0 (collo da '.$pezzi_confezione.')';
							}
						}
						else {
							if($pezzi_confezione>1) {
								if($differenza_da_ordinare!=$pezzi_confezione)  
									$html .= '<span class="box_evidenza"> '.$differenza_da_ordinare.' </span> (collo da '.$pezzi_confezione.')';
								else
									$html .= '0 (collo da '.$pezzi_confezione.')';
							}
							else 
								$html .= '';

						}
						$html .= '</td>';
					}
										
					$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.$this->App->getArticlePrezzo($prezzo).'</td>';
					$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
					$html .= '</tr>';	
					
					$i++;

					$bio = '';
					$name = '';
					$prezzo = '';
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
				$name = $order['ArticlesOrder'][$numArticlesOrder]['name'].' '.$this->App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']);
				
				$prezzo = $order['ArticlesOrder'][$numArticlesOrder]['prezzo'];
				$tot_qta_single_article += $qta;
				$tot_importo_single_article += $importo;
				$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
				/*
				 * colli_completi / differenza_da_ordinare
				 */
				$colli_completi = intval($order['ArticlesOrder'][$numArticlesOrder]['qta_cart'] / $order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione']);
				if($colli_completi>0)
					$differenza_da_ordinare = (($order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione'] * ($colli_completi +1)) - $order['ArticlesOrder'][$numArticlesOrder]['qta_cart']);
				else {
					$differenza_da_ordinare = ($order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione'] - $order['ArticlesOrder'][$numArticlesOrder]['qta_cart']);
					$colli_completi = '-';
				}
				
				$pezzi_confezione = $order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione'];
				$qta_cart = $order['ArticlesOrder'][$numArticlesOrder]['qta_cart'];
				$qta_minima_order = $order['ArticlesOrder'][$numArticlesOrder]['qta_minima_order'];
				if($qta_minima_order==0) $qta_minima_order = ' ';
				$qta_massima_order = $order['ArticlesOrder'][$numArticlesOrder]['qta_massima_order'];
				if($qta_massima_order==0) $qta_massima_order = ' ';
				
				$order_id_old = $order['ArticlesOrder'][$numArticlesOrder]['order_id'];
				$article_organization_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'];
				$article_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_id'];
								
			}  // end foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
			 	
			$html .= '<tr>';
			$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($i+1).'</td>';
			$html .= '<td width="'.$width.'">'.$name.'</td>';
			$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$tot_qta_single_article.'</td>';

			if($orderToQtaMinimaOrder) {
				$differenza = (intval($qta_minima_order) - intval($qta_cart));
				$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">';
				if($differenza > 0)
					$html .= '<span class="box_evidenza"> '.$qta_minima_order.' </span>';
				else
					$html .= $qta_minima_order;
				$html .= '</td>';
				$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">';
				if($differenza > 0)
					$html .= $differenza;
				else 
					$html .= '-';
				$html .= '</td>';
			} 
			
			if($orderToQtaMassima)
				$html .= '			<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$qta_massima_order.'</td>';
			
			if($orderToValidate) {
				$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">';
				if($pezzi_confezione>1)  $html .= $colli_completi;
				else $html .= '';
				$html .= '</td>';
				$html .= '<td width="'.$output->getCELLWIDTH80().'" style="text-align:center;">';
				if($pezzi_confezione>1) {
					if($differenza_da_ordinare!=$pezzi_confezione)  
						$html .= '<span class="box_evidenza"> '.$differenza_da_ordinare.' </span> (collo da '.$pezzi_confezione.')';
					else
						$html .= '0 (collo da '.$pezzi_confezione.')';					
				}
				else 
					$html .= '';
				$html .= '</td>';
			}
			
			$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:center;">'.$this->App->getArticlePrezzo($prezzo).'</td>';
			$html .= '<td width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
			$html .= '</tr>';	
						
			/*
			 * ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
			*/
			if(!empty($resultsSummaryOrder[0]['totale_importo']) && 
		       $resultsSummaryOrder[0]['totale_importo']!=$tot_importo) { 
				$tmp_importo = $resultsSummaryOrder[0]['totale_importo'];
				$importo_modificato = true;
			}
			else {
				$tmp_importo = $tot_importo;
				$importo_modificato = false;
			}
			
			$html .= '<tr>';
			$html .= '	<th width="'.$output->getCELLWIDTH20().'"></th>';
			$html .= '	<th width="'.$width.'" style="text-align:right;">'.__('qta_tot').'</th>';
			$html .= '	<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;">&nbsp;'.$tot_qta.'</th>';

			if($orderToQtaMinimaOrder) {
				$html .= '			<th width="'.$output->getCELLWIDTH50().'"></th>';
				$html .= '			<th width="'.$output->getCELLWIDTH70().'"></th>';
			}
				
			if($orderToQtaMassima)
				$html .= '			<th width="'.$output->getCELLWIDTH50().'"></th>';
			
			if($orderToValidate) {
				$html .= '			<th width="'.$output->getCELLWIDTH70().'"></th>';
				$html .= '			<th width="'.$output->getCELLWIDTH80().'"></th>';
			}
				
			$html .= '	<th width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'" colspan="2" style="text-align:right;">'.__('Importo_totale').'&nbsp;'.number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;'.$this->App->traslateQtaImportoModificati($importo_modificato).'</th>';
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