<?php 
/*
 * T O - A R T I C L E S - M O N I T O R I N G
 * Documento con articoli aggregati (per il produttore)
 */

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

// define table cells
$table[] =	array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('Bio'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('qta'), 'width' => 'auto');
if($orderToQtaMinimaOrder)
	$table[] =	array('label' => __('qta_minima_order'), 'width' => 'auto', 'filter' => true);
if($orderToQtaMassima)
	$table[] =	array('label' => __('qta_massima_order'), 'width' => 'auto', 'filter' => true);
if($orderToValidate) {
	$table[] = array('label' => 'Colli completati', 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => 'Mancano per il collo', 'width' => 'auto', 'filter' => true);
}
$table[] =	array('label' => __('PrezzoUnita'), 'width' => 'auto');
$table[] =	array('label' => __('Importo'), 'width' => 'auto');
	
// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {

			$colli_completi = 0;
			$differenza_da_ordinare = 0;
			$pezzi_confezione = 0;
			$qta_minima_order = 0;
			$qta_massima_order = 0;
			$tot_qta = 0;
			$tot_qta_colli = 0;
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
					if($bio=='Y') $bio = 'Bio';
					
					$rows = [];
					$rows[] = ($i+1);
					$rows[] = $bio;
					$rows[] = $name;
					$rows[] = $tot_qta_single_article;
					if($orderToQtaMinimaOrder)
						$rows[] = $qta_minima_order;
					if($orderToQtaMassima)
						$rows[] = $qta_massima_order;
					if($orderToValidate)  {
							$tmp = "";

							$tot_qta_colli = ((int)$tot_qta_colli + (int)$colli_completi);

							if($colli1=='N') {
								if($pezzi_confezione>1)  $tmp .= $colli_completi;
								else $tmp .= '';
								$rows[] = $tmp;
								
								$tmp = "";
								if($pezzi_confezione>1) {
									if($differenza_da_ordinare!=$pezzi_confezione)  
										$tmp .= $differenza_da_ordinare.' (collo da '.$pezzi_confezione.')';
									else
										$tmp .= '(collo da '.$pezzi_confezione.')';
								}
								else 
									$tmp .= '';

								$rows[] = $tmp;
							}
							else {
								$tmp .= $colli_completi;
								$rows[] = $tmp;
								
								$tmp = "";
								if($differenza_da_ordinare!=$pezzi_confezione)  
									$tmp .= $differenza_da_ordinare.' (collo da '.$pezzi_confezione.')';
								else
									$tmp .= '(collo da '.$pezzi_confezione.')';

								$rows[] = $tmp;							
							}	
					}
										
					$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo));
					$rows[] = number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
					$this->PhpExcel->addTableRow($rows);

					
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
				$name = $this->ExportDocs->prepareCsv($order['ArticlesOrder'][$numArticlesOrder]['name'].' '.$this->App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']));
				
				$prezzo = $order['ArticlesOrder'][$numArticlesOrder]['prezzo'];
				$tot_qta_single_article += $qta;
				$tot_importo_single_article += $importo;
				$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
				/*
				 * colli_completi / differenza_da_ordinare
				 */
				$colli_completi = intval($order['ArticlesOrder'][$numArticlesOrder]['qta_cart'] / $order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione']);
				if($colli_completi>0) {
                    $ordinati_che_completano_i_colli = ($order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione'] * $colli_completi);
                    $differenza_da_ordinare = ($order['ArticlesOrder'][$numArticlesOrder]['qta_cart'] - $ordinati_che_completano_i_colli - $order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione']);
                    //  $differenza_da_ordinare = (($order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione'] * $colli_completi) - $order['ArticlesOrder'][$numArticlesOrder]['qta_cart']);
                }
				else {
					$differenza_da_ordinare = ($order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione'] - $order['ArticlesOrder'][$numArticlesOrder]['qta_cart']);

					if($colli1!='Y') 
						$colli_completi = '0';
					else 
						$colli_completi = $order['ArticlesOrder'][$numArticlesOrder]['qta_cart'];
				}
				
				$pezzi_confezione = $order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione'];
				$qta_minima_order = $order['ArticlesOrder'][$numArticlesOrder]['qta_minima_order'];
				if($qta_minima_order==0) $qta_minima_order = ' ';
				$qta_massima_order = $order['ArticlesOrder'][$numArticlesOrder]['qta_massima_order'];
				if($qta_massima_order==0) $qta_massima_order = ' ';
				
				$order_id_old = $order['ArticlesOrder'][$numArticlesOrder]['order_id'];
				$article_organization_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'];
				$article_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_id'];
								
			}  // end foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
			 	
			 	
			if($bio=='Y') $bio = 'Bio'; 	
			$rows = [];
			$rows[] = ($i+1);
			$rows[] = $bio;
			$rows[] = $name;
			$rows[] = $tot_qta_single_article;
			if($orderToQtaMinimaOrder)
				$rows[] = $qta_minima_order;			
			if($orderToQtaMassima)
				$rows[] = $qta_massima_order;
			if($orderToValidate)  {
				$tmp = "";

				$tot_qta_colli = ((int)$tot_qta_colli + (int)$colli_completi);
				
				if($colli1=='N') {
					if($pezzi_confezione>1)  $tmp .= $colli_completi;
					else $tmp .= '';
					$rows[] = $tmp;
					
					$tmp = "";
					if($pezzi_confezione>1) {
						if($differenza_da_ordinare!=$pezzi_confezione)  
							$tmp .= $differenza_da_ordinare.' (collo da '.$pezzi_confezione.')';
						else
							$tmp .= '(collo da '.$pezzi_confezione.')';
					}
					else 
						$tmp .= '';

					$rows[] = $tmp;
				}
				else {
					$tmp .= $colli_completi;
					$rows[] = $tmp;
					
					$tmp = "";
					if($differenza_da_ordinare!=$pezzi_confezione)  
						$tmp .= $differenza_da_ordinare.' (collo da '.$pezzi_confezione.')';
					else
						$tmp .= '(collo da '.$pezzi_confezione.')';

					$rows[] = $tmp;							
				}	
			}
			
			$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo));
			$rows[] = number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
			$this->PhpExcel->addTableRow($rows);
					


						
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
			
			$rows = [];
			$rows[] = '';
			$rows[] = '';
			$rows[] = '';	
			$rows[] = $tot_qta;	
			if($orderToQtaMinimaOrder)
				$rows[] = '';			
			if($orderToQtaMassima)
				$rows[] = '';
			else
			if($orderToValidate) {
				if($colli1=='Y') 
					$rows[] = $tot_qta_colli;
				else				
					$rows[] = '';
				$rows[] = '';
			}
			$rows[] = '';
			$rows[] = number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')); // $this->App->traslateQtaImportoModificati($importo_modificato);
			$this->PhpExcel->addTableRow($rows);
				
				
		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
		
	}

	
}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 

		    
$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>