<?php 
/*
 * T O - A R T I C L E S - D E T A I L S 
 * Documento con articoli aggregati con il dettaglio degli utenti
 */

$csv = array('N' => 'N',			  'bio' => __('Bio'));

if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
	$csv += array('codice' => __('Codice'));

$csv += array('name' => __('Name'),
				'utente' => 'Utente');

if($acquistato_il=='Y')
	$csv += array('acquistato_il' => 'Acquistato il');

$csv += array('qta' => $this->ExportDocs->prepareCsvAccenti(__('qta')),				'prezzo_unita' => $this->ExportDocs->prepareCsvAccenti(__('PrezzoUnita')),				'importo' => __('Importo')			);

$headers = array('csv' => $csv);

$data = "";

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
			$totRows=0;
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
							$data[$totRows]['csv'] = array(
									'N' => '',
									'bio' => '');
						
							if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
								$data[$totRows]['csv'] += array('codice' => '');
							
								$data[$totRows]['csv'] += array('name' => '',
																		'utente' => '');

								if($acquistato_il=='Y')
									$data[$totRows]['csv'] += array('acquistato_il' => '');
								
								$data[$totRows]['csv'] += array('qta' => $tot_qta_singolo_articolo,
																		'prezzo_unita' => '',
																		'importo' => number_format($tot_importo_singolo_articolo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
								
							$tot_qta_singolo_articolo = 0;
							$tot_importo_singolo_articolo = 0;
							$totRows++;
					} // end if($numArticlesOrder>0 && $totale_per_articolo=='Y') 
					
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
					$name = '';
				}
				
								$data[$totRows]['csv'] = array('N' => $num,										'bio' => $bio);
					
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$data[$totRows]['csv'] += array('codice' => $codiceArticle);
											$data[$totRows]['csv'] += array('name' => $name,
														'utente' => $order['User'][$numArticlesOrder]['name']);
				
				if($acquistato_il=='Y')		
					$data[$totRows]['csv'] += array('acquistato_il' => $this->time->i18nFormat($order['Cart'][$numArticlesOrder]['created'],"%e %B %R"));
								$data[$totRows]['csv'] += array('qta' => $qta, // $this->App->traslateQtaImportoModificati($qta_modificata)														'prezzo_unita' => $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($order['ArticlesOrder'][$numArticlesOrder]['prezzo'])),														'importo' => number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'))				);
				
				$userName = $order['User'][$numArticlesOrder]['name'];
				$codiceArticle = $order['Article'][$numArticlesOrder]['codice'];
				$name = $order['Article'][$numArticlesOrder]['name'];
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
				
				$totRows++;
			}  // end foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) 

			
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

			/*
			 * totale per ogni articolo
			*/
			if($totale_per_articolo=='Y')  {
				$data[$totRows]['csv'] = array(
						'N' => '',
						'bio' => '');

				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$data[$totRows]['csv'] += array('codice' => '');
				
				$data[$totRows]['csv'] += array('name' => '',
														 'utente' => '');
				
				if($acquistato_il=='Y')
					$data[$totRows]['csv'] += array('acquistato_il' => '');
				
				$data[$totRows]['csv'] += array('qta' => $tot_qta_singolo_articolo,
														'prezzo_unita' => '',
														'importo' => number_format($tot_importo_singolo_articolo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
			
				$totRows++;
			} // end if($totale_per_articolo=='Y')
			
			/*
			 * TOTALE
			 */	
						$data[$totRows]['csv'] = array(					'N' => '',					'bio' => '');
		
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$data[$totRows]['csv'] += array('codice' => '');			
			$data[$totRows]['csv'] += array('name' => '',													 'utente' => '');			if($acquistato_il=='Y')	
				$data[$totRows]['csv'] += array('acquistato_il' => '');
						$data[$totRows]['csv'] += array('qta' => $tot_qta,												'prezzo_unita' => '',												'importo' => number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));

			$totRows++;
			
			/*
			 * TRASPORTO
			 */	
			if($hasTrasport=='Y' && $trasportAndCost=='Y') {
				
				$data[$totRows]['csv'] = array(
						'N' => '',
						'bio' => '');

				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$data[$totRows]['csv'] += array('codice' => '');
				
				$data[$totRows]['csv'] += array('name' => '',
														'utente' => '');
				if($acquistato_il=='Y')	
					$data[$totRows]['csv'] += array('acquistato_il' => '');
				
				$data[$totRows]['csv'] += array('qta' => '',
													'prezzo_unita' => __('TrasportShort'),
													'importo' => number_format($importo_trasporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
				
				$totRows++;
			}

			/*
			 * COSTO AGGIUNTIVO
			*/
			if($hasCostMore=='Y' && $trasportAndCost=='Y') {
					
				$data[$totRows]['csv'] = array(
						'N' => '',
						'bio' => '');
					
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$data[$totRows]['csv'] += array('codice' => '');
					
				$data[$totRows]['csv'] += array('name' => '',
						'utente' => '');
				if($acquistato_il=='Y')
					$data[$totRows]['csv'] += array('acquistato_il' => '');
					
				$data[$totRows]['csv'] += array('qta' => '',
						'prezzo_unita' => __('CostMoreShort'),
						'importo' => number_format($importo_cost_more,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
					
				$totRows++;
			}
			
			/*
			 * SCONTO
			*/
			if($hasCostMore=='Y' && $trasportAndCost=='Y') {
			
				$data[$totRows]['csv'] = array(
						'N' => '',
						'bio' => '');
			
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$data[$totRows]['csv'] += array('codice' => '');
			
				$data[$totRows]['csv'] += array('name' => '',
						'utente' => '');
				if($acquistato_il=='Y')
					$data[$totRows]['csv'] += array('acquistato_il' => '');
			
				$data[$totRows]['csv'] += array('qta' => '',
						'prezzo_unita' => __('CostLessShort'),
						'importo' => number_format($importo_cost_less,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
			
				$totRows++;
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
								
				$data[$totRows]['csv'] = array(
						'N' => '',
						'bio' => '');

				if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
					$data[$totRows]['csv'] += array('codice' => '');
				
				$data[$totRows]['csv'] += array('name' => '',
										'utente' => '');

				if($acquistato_il=='Y')	
					$data[$totRows]['csv'] += array('acquistato_il' => '');
				
				$data[$totRows]['csv'] += array('qta' => '',
										'prezzo_unita' => __('Totale'),
										'importo' => number_format($importo_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
				
				$totRows++;
			}	// end TOTALE
			
		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
			
	}

}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 

array_unshift($data,$headers);foreach ($data as $row){	foreach ($row['csv'] as &$value) {		// Apply opening and closing text delimiters to every value		$value = "\"".$value."\"";	}	// Echo all values in a row comma separated	echo implode(",",$row['csv'])."\n";}?>