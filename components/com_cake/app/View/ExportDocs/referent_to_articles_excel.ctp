<?php 
/*
 * T O - A R T I C L E S 
 * Documento con articoli aggregati (per il produttore)
 */
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

if(isset($results['Delivery']) && !empty($results['Delivery']))
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {

			// define table cells
			$table[] =	array('label' => __('N'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
			$table[] =	array('label' => __('Bio'), 'width' => 'auto');
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$table[] = array('label' => __('Codice'), 'width' => 'auto', 'filter' => true);
			$table[] = array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
			$table[] = array('label' => __('pezzi_confezione_short'), 'width' => 'auto', 'filter' => true);
			$table[] = array('label' => __('qta'), 'width' => 'auto', 'filter' => true);
			if($pezzi_confezione1=='Y')
				$table[] = array('label' => __('Colli'), 'width' => 'auto', 'filter' => true);
			$table[] =	array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => true);
			$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => true);

			// heading
			$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));


			$tot_qta = 0;
			$tot_importo = 0;
			$pezzi_confezione = 1;
			$order_id_old = 0;
			$article_organization_id_old = 0;
			$article_id_old = 0;	
			$i=0;
			foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {

				if(!empty($order_id_old) && // salto la prima volta
				   ($order_id_old != $order['ArticlesOrder'][$numArticlesOrder]['order_id'] ||
				    $article_organization_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'] ||
				    $article_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_id'])) {
					
					$rows = [];
					$rows[] = ($i+1);
					$rows[] = $bio;
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
						$rows[] = $codiceArticle;
					$rows[] = $name;
					$rows[] = $pezzi_confezione;
					$rows[] = $tot_qta_single_article;
					$tmp = '';
					if($pezzi_confezione1=='Y') {
						/*
						 * colli_completi
						*/
						$tmp .= $this->App->getColli($tot_qta_single_article, $pezzi_confezione);
						
						$rows[] = $tmp;
					}
					$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo));
					$rows[] = number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
							
					$this->PhpExcel->addTableRow($rows);
										
					$i++;

					$bio = '';
					$name = '';
					$pezzi_confezione = 1;
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
				
				if($order['Article'][$numArticlesOrder]['bio']=='Y')
					$bio = 'Si';
				else
					$bio = 'No';
				
				$codiceArticle = $order['Article'][$numArticlesOrder]['codice'];
				$codiceArticle = $this->ExportDocs->prepareCsv($codiceArticle);
				
				/*
				 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
				 */					
				 if(!empty($order['ArticlesOrder'][$numArticlesOrder]['name']))
					 $name = $order['ArticlesOrder'][$numArticlesOrder]['name'];
				 else
					 $name = $order['Article'][$numArticlesOrder]['name'];
				$name = $name.' '.$this->App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']);
				$name = $this->ExportDocs->prepareCsv($name);
				
				$prezzo = $order['ArticlesOrder'][$numArticlesOrder]['prezzo'];
				$tot_qta_single_article += $qta;
				$pezzi_confezione = $order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione'];
				$tot_importo_single_article += $importo;
				$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
				$order_id_old = $order['ArticlesOrder'][$numArticlesOrder]['order_id'];
				$article_organization_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'];
				$article_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_id'];
						
			}  // end foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
			 	
			$rows = [];
			$rows[] = ($i+1);
			$rows[] = $bio;
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$rows[] = $codiceArticle;			
			$rows[] = $name;
			$rows[] = $pezzi_confezione;
			$rows[] = $tot_qta_single_article;
			$tmp = '';
			if($pezzi_confezione1=='Y') {
				/*
				 * colli_completi
				*/
				$tmp .= $this->App->getColli($tot_qta_single_article, $pezzi_confezione);
				
				$rows[] = $tmp;
			}
			$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo));
			$rows[] = number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));						
			
			$this->PhpExcel->addTableRow($rows);
						
			/*
			 * ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
			*/
			if(!empty($resultsSummaryOrder[0]['totale_importo']) &&
			$resultsSummaryOrder[0]['totale_importo'] != ($tot_importo + $order['Order']['trasport'] + $order['Order']['cost_more'] + ($order['Order']['cost_less'])).'' ) {
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
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$rows[] = '';			
			$rows[] = '';		
			$rows[] = '';
			$rows[] = $tot_qta;
			if($pezzi_confezione1=='Y')
				$rows[] = '';
			$rows[] = '';
			$rows[] = number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

			$this->PhpExcel->addTableRow($rows);
			
			/*
			 * TRASPORTO
			*/			
			if($order['Order']['hasTrasport']=='Y' && $trasportAndCost=='Y' && $order['Order']['trasport']!='0.00') {
			
				$trasport = $order['Order']['trasport'];

				$rows = [];
				$rows[] = '';
				$rows[] = '';
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = '';		
				$rows[] = '';
				if($pezzi_confezione1=='Y')
					$rows[] = '';
				$rows[] = __('TrasportShort');
				$rows[] = number_format($trasport,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$rows[] = '';
				
				$this->PhpExcel->addTableRow($rows);
			}

			/*
			 * COSTO AGGIUNTIVO
			*/
			if($order['Order']['hasCostMore']=='Y' && $trasportAndCost=='Y' && $order['Order']['cost_more']!='0.00') {
					
				$cost_more = $order['Order']['cost_more'];
					
				$rows = [];
				$rows[] = '';
				$rows[] = '';
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = '';		
				$rows[] = '';
				if($pezzi_confezione1=='Y')
					$rows[] = '';
				$rows[] = __('CostMoreShort');
				$rows[] = number_format($cost_more,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$rows[] = '';
				
				$this->PhpExcel->addTableRow($rows);
			}
			
			/*
			 * SCONTO
			*/
			if($order['Order']['hasCostLess']=='Y' && $trasportAndCost=='Y' && $order['Order']['cost_less']!='0.00') {
					
				$cost_less = $order['Order']['cost_less'];
					
				$rows = [];
				$rows[] = '';
				$rows[] = '';
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = '';		
				$rows[] = '';
				if($pezzi_confezione1=='Y')
					$rows[] = '';
				$rows[] = __('CostLessShort');
				$rows[] = number_format($cost_less,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$rows[] = '';
				
				$this->PhpExcel->addTableRow($rows);
			}
						
			/*
			 * TOTALE
			*/
			if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') || 
				 ($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') || 
				 ($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y') {
			
				/*
				 * se modificato, quindi in SummaryOrder e' gia' compreso l'importo del trasporto
				*/
				if($importo_modificato)
					$importo_completo = ($tmp_importo);
				else
					$importo_completo = ($tmp_importo + $order['Order']['trasport'] + $order['Order']['cost_more'] + (-1 * $order['Order']['cost_less']));
				
				$rows = [];
				$rows[] = '';
				$rows[] = '';
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = '';		
				$rows[] = '';
				if($pezzi_confezione1=='Y')
					$rows[] = '';
				$rows[] = __('Totale');
				$rows[] = number_format($importo_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
				$this->PhpExcel->addTableRow($rows);
			}
			
		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
					
	}

}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 

		    
$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>