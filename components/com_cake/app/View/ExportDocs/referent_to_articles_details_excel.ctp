<?php 
/*
 * T O - A R T I C L E S - D E T A I L S 
 * Documento con articoli aggregati con il dettaglio degli utenti
 */

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

// define table cells
$table[] =	array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('Bio'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
	$table[] =	array('label' => __('Codice'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' =>  'Utente', 'width' => 'auto');
if($acquistato_il=='Y') 
	$table[] = array('label' => 'Acquistato il', 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('qta'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => false);
		
// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

if(isset($results['Delivery']))
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		if(isset($result['Delivery']['Order']))
		foreach($result['Delivery']['Order'] as $numOrder => $order) {
			
			$hasTrasport = $order['Order']['hasTrasport'];
			$importo_trasporto = $order['Order']['trasport'];
			
			$hasCostMore = $order['Order']['hasCostMore'];
			$importo_cost_more = $order['Order']['cost_more']; 
			
			$hasCostLess = $order['Order']['hasCostLess'];
			$importo_cost_less = $order['Order']['cost_less'];
			
			$tot_qta = 0;
			$tot_importo = 0;
			$tot_qta_singolo_articolo = 0;
			$tot_importo_singolo_articolo = 0;			
			$order_id_old = 0;
			$article_organization_id_old=0;
			$article_id_old = 0;
			$name = '';
			$qta = 0;
			$prezzo = '';
			$totale = 0;
			$i=0;
			if(isset($order['ArticlesOrder']))
			foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {

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
								
				
				$tot_qta += $qta;
				$tot_importo += $importo;
				
				if($order_id_old != $order['ArticlesOrder'][$numArticlesOrder]['order_id'] ||
					$article_organization_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'] ||
					$article_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_id'] ) {
					
					/*
					 * totale per ogni articolo
					*/
					if($numArticlesOrder>0 && $totale_per_articolo=='Y')  {
						$rows = array();
						$rows[] = '';
						$rows[] = '';
						if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
							$rows[] = '';
						$rows[] = '';
						$rows[] = '';
						if($acquistato_il=='Y')
							$rows[] = '';
						$rows[] = $tot_qta_singolo_articolo;
						$rows[] = '';
						$rows[] = number_format($tot_importo_singolo_articolo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
							
						$this->PhpExcel->addTableRow($rows);

						$tot_qta_singolo_articolo = 0;
						$tot_importo_singolo_articolo = 0;
					}
					
					$num = ($i+1);
					if($order['Article'][$numArticlesOrder]['bio']=='Y')
						$bio = 'Bio';
					else 
						$bio = '';
					
					$codiceArticle = $this->ExportDocs->prepareCsv($order['Article'][$numArticlesOrder]['codice']);
					/*
					 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
					 */	
					 if(!empty($order['ArticlesOrder'][$numArticlesOrder]['name']))
						 $name = $order['ArticlesOrder'][$numArticlesOrder]['name'];
					 else
						 $name = $order['Article'][$numArticlesOrder]['name'];	
					$name = $this->ExportDocs->prepareCsv($name).' '.$this->App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']);
						
					$i++;
				}
				else {
					$num = '';
					$bio = '';
					$codiceArticle = '';
					$name = '';
				}
				
				$rows = array();
				$rows[] = $num;
				$rows[] = $bio;
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = $codiceArticle;
				$rows[] = $name;
				$rows[] = $order['User'][$numArticlesOrder]['name'];
				if($acquistato_il=='Y')	
					$rows[] = $this->time->i18nFormat($order['Cart'][$numArticlesOrder]['created'],"%e %B %R");
				$rows[] = $qta; // $this->App->traslateQtaImportoModificati($qta_modificata)
				$rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($order['ArticlesOrder'][$numArticlesOrder]['prezzo']));
				$rows[] = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
				
				$this->PhpExcel->addTableRow($rows);
				
				$userName = $order['User'][$numArticlesOrder]['name'];
				$codiceArticle = $this->ExportDocs->prepareCsv($order['Article'][$numArticlesOrder]['codice']);
				/*
				 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
				 */	
				 if(!empty($order['ArticlesOrder'][$numArticlesOrder]['name']))
					 $name = $order['ArticlesOrder'][$numArticlesOrder]['name'];
				 else
					 $name = $order['Article'][$numArticlesOrder]['name'];
				$name = $this->ExportDocs->prepareCsv($name).' '.$this->App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']);
				
				if($order['Article'][$numArticlesOrder]['bio']=='Y')
					$bio = 'Si';
				else
					$bio = 'No';
				$created = $order['Cart'][$numArticlesOrder]['created'];
				$prezzo = $order['ArticlesOrder'][$numArticlesOrder]['prezzo'];
				
				$tot_qta_singolo_articolo += $qta;
				$tot_importo_singolo_articolo += $importo;
				
				$order_id_old = $order['ArticlesOrder'][$numArticlesOrder]['order_id'];
				$article_organization_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'];
				$article_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_id'];
				
			}  // end foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) 

			
			/*
			 * totale per ogni articolo
			*/
			if($totale_per_articolo=='Y')  {
				$rows = array();
				$rows[] = '';
				$rows[] = '';
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = '';
				if($acquistato_il=='Y')
					$rows[] = '';
				$rows[] = $tot_qta_singolo_articolo;
				$rows[] = '';
				$rows[] = number_format($tot_importo_singolo_articolo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					
				$this->PhpExcel->addTableRow($rows);
			}
			
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

			$rows = array();
			$rows[] = '';
			$rows[] = '';
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$rows[] = '';
			$rows[] = '';
			$rows[] = '';
			if($acquistato_il=='Y')	
				$rows[] = '';
			$rows[] = $tot_qta;
			$rows[] = '';
			$rows[] = number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
			
			$this->PhpExcel->addTableRow($rows);
			
			/*
			 * TRASPORTO
			 */
			if($hasTrasport=='Y' && $trasportAndCost=='Y') {
				
				$rows = array();
				$rows[] = '';
				$rows[] = '';
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = '';
				if($acquistato_il=='Y')	
					$rows[] = '';
				$rows[] = '';
				$rows[] = __('TrasportShort');
				$rows[] = number_format($importo_trasporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
				
				$this->PhpExcel->addTableRow($rows);
			}

			/*
			 * COSTO AGGIUNTIVO
			*/
			if($hasCostMore=='Y' && $trasportAndCost=='Y') {
					
				$rows = array();
				$rows[] = '';
				$rows[] = '';
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = '';
				if($acquistato_il=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = __('CostMoreShort');
				$rows[] = number_format($importo_cost_more,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					
				$this->PhpExcel->addTableRow($rows);
			}
			
			/*
			 * SCONTO
			*/
			if($hasCostLess=='Y' && $trasportAndCost=='Y') {
			
				$rows = array();
				$rows[] = '';
				$rows[] = '';
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = '';
				if($acquistato_il=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = __('CostLessShort');
				$rows[] = number_format($importo_costless,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
				$this->PhpExcel->addTableRow($rows);
			}
			
				
			
			/*
			 * TOTALE
			*/
			if(($hasTrasport=='Y' || $hasCostMore=='Y' || $hasCostLess=='Y') && $trasportAndCost=='Y') {
			
				/*
				 * se modificato, quindi in SummaryOrder e' gia' compreso l'importo del trasporto
				*/
				if($importo_modificato)
					$importo_completo = ($tmp_importo);
				else
					$importo_completo = ($tmp_importo + $importo_trasporto + $importo_cost_more + (-1 * $importo_cost_less));

				$rows = array();
				$rows[] = '';
				$rows[] = '';
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$rows[] = '';
				$rows[] = '';
				$rows[] = '';
				if($acquistato_il=='Y')
					$rows[] = '';
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