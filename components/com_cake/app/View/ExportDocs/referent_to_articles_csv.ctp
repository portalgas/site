<?php 
/*
 * T O - A R T I C L E S 
 * Documento con articoli aggregati (per il produttore)
 */

$data = "";

if(isset($results['Delivery']) && !empty($results['Delivery']))
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {

			$csv = array('N' => 'N',
							'bio' => __('Bio'));
			
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$csv += array('codice' => __('Codice'));
			
			$csv += array('name' => __('Name'),
						'pezzi_confezioni' => __('pezzi_confezione_short'),
						'qta' => __('qta'));

			if($pezzi_confezione1=='Y')
				$csv += array('pezzi_confezione' => __('Colli'));
					
			$csv += array('prezzo_unita' => $this->ExportDocs->prepareCsvAccenti(__('PrezzoUnita')),
						  'importo' => __('Importo'));
			
			$headers = array('csv' => $csv); 
		

			$tot_qta = 0;
			$tot_importo = 0;
			$pezzi_confezione = 1;
			$order_id_old = 0;
			$article_organization_id_old = 0;			$article_id_old = 0;	
			$i=0;
			foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
			 								
				if(!empty($order_id_old) && // salto la prima volta
				   ($order_id_old != $order['ArticlesOrder'][$numArticlesOrder]['order_id'] ||
				    $article_organization_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'] ||
				    $article_id_old != $order['ArticlesOrder'][$numArticlesOrder]['article_id'])) {
					
					$data[$numArticlesOrder]['csv'] = array(							'N' => ($i+1),							'bio' => $bio);
					
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
						$data[$numArticlesOrder]['csv'] += array('codice' => $codiceArticle);
											
					$data[$numArticlesOrder]['csv'] += array(							'name' => $name,							'qta' => $tot_qta_single_article,
							'pezzi_confezione' => $pezzi_confezione);
					
					$tmp = '';
					if($pezzi_confezione1=='Y') {
						/*
						 * colli_completi
						*/
						$tmp .= $this->App->getColli($tot_qta_single_article, $pezzi_confezione);
					
						$data[$numArticlesOrder]['csv'] += array('colli' => $tmp);
					}
										$data[$numArticlesOrder]['csv'] += array(
							'prezzo_unita' => $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo)),
							'importo' => number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
										
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
				
				/*
				 * per gli articoli DES che prendono gli articoli da un'altro GAS il pregresso potrebbe non avere ArticlesOrder.name
				 */					
				 if(!empty($order['ArticlesOrder'][$numArticlesOrder]['name']))
					 $name = $order['ArticlesOrder'][$numArticlesOrder]['name'];
				 else
					 $name = $order['Article'][$numArticlesOrder]['name'];
				$name = $name.' '.$this->App->getArticleConf($order['Article'][$numArticlesOrder]['qta'], $order['Article'][$numArticlesOrder]['um']);
				$name = $this->ExportDocs->prepareCsv($name);
				
				$codiceArticle = $order['Article'][$numArticlesOrder]['codice'];
				$codiceArticle = $this->ExportDocs->prepareCsv($codiceArticle);
					
				$prezzo = $order['ArticlesOrder'][$numArticlesOrder]['prezzo'];
				$tot_qta_single_article += $qta;
				$pezzi_confezione = $order['ArticlesOrder'][$numArticlesOrder]['pezzi_confezione'];
				$tot_importo_single_article += $importo;
				$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				
				$order_id_old = $order['ArticlesOrder'][$numArticlesOrder]['order_id'];
				$article_organization_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_organization_id'];
				$article_id_old = $order['ArticlesOrder'][$numArticlesOrder]['article_id'];
				
			}  // end foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
			 	
			$data[$numArticlesOrder]['csv'] = array(					'N' => ($i+1),					'bio' => $bio);
			
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$data[$numArticlesOrder]['csv'] += array('codice' => $codiceArticle);
			
			$data[$numArticlesOrder]['csv'] += array(
					'name' => $name,					'qta' => $tot_qta_single_article,
					'pezzi_confezione' => $pezzi_confezione);					
			$tmp = '';
			if($pezzi_confezione1=='Y') {
				/*
				 * colli_completi
				*/
				$tmp .= $this->App->getColli($tot_qta_single_article, $pezzi_confezione);
			
				$data[$numArticlesOrder]['csv'] += array('colli' => $tmp);
			}	

			$data[$numArticlesOrder]['csv'] += array(
				'prezzo_unita' => $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($prezzo)),				'importo' => number_format($tot_importo_single_article,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));			
						
			/*
			 * ctrl se il totale e' stato modificato in Carts::managementCartsGroupByUsers
			*/
			if(!empty($resultsSummaryOrder[0]['totale_importo']) && 
		       $resultsSummaryOrder[0]['totale_importo'] != ($tot_importo+$order['Order']['trasport']).'') { 
				$tmp_importo = $resultsSummaryOrder[0]['totale_importo'];
				$importo_modificato = true;
			}
			else {
				$tmp_importo = $tot_importo;
				$importo_modificato = false;
			}

			$importo_trasporto = $order['Order']['trasport'];
			$importo_cost_more = $order['Order']['cost_more'];
			$importo_cost_less = $order['Order']['cost_less'];
			
			/*
			 * se modificato, quindi in SummaryOrder e' gia' compreso l'importo del trasporto
			*/
			if($importo_modificato)
				$importo_completo = ($tmp_importo);
			else
				$importo_completo = ($tmp_importo + $importo_trasporto + $importo_cost_more + (-1 * $importo_cost_less));
		
			/*
			 * IMPORTO
			*/		
			$data[$numArticlesOrder]['csv'] = array(
					'N' => '',
					'bio' => '');
			
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$data[$numArticlesOrder]['csv'] += array('codice' => '');

			$data[$numArticlesOrder]['csv'] += array(
					'name' => '',
					'qta' => $tot_qta,
					'pezzi_confezione' => $pezzi_confezione);
			
			if($pezzi_confezione1=='Y') $data[$numArticlesOrder]['csv'] += array('pezzi_confezione' => '');
				
			$data[$numArticlesOrder]['csv'] += array(
					'prezzo_unita' => '',
					'importo' => number_format($tmp_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
					
		
		/*
		 * TRASPORTO
		*/
		if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y') {
			$numArticlesOrder++;
			
			$data[$numArticlesOrder]['csv'] = array(
					'N' => '',
					'bio' => '');
				
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$data[$numArticlesOrder]['csv'] += array('codice' => '');
			
			$data[$numArticlesOrder]['csv'] += array(
					'name' => '',
					'qta' => '',
					'pezzi_confezione' => '');
				
			if($pezzi_confezione1=='Y') $data[$numArticlesOrder]['csv'] += array('pezzi_confezione' => '');
			
			$data[$numArticlesOrder]['csv'] += array(
					'prezzo_unita' => __('Trasport'),
					'importo' => number_format($importo_trasporto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
		}
		
		/*
		 * COSTO AGGIUNTIVO
		*/		
		if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y') {
			$numArticlesOrder++;
				
			$data[$numArticlesOrder]['csv'] = array(
					'N' => '',
					'bio' => '');
			
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$data[$numArticlesOrder]['csv'] += array('codice' => '');
				
			$data[$numArticlesOrder]['csv'] += array(
					'name' => '',
					'qta' => '',
					'pezzi_confezione' => '');
			
			if($pezzi_confezione1=='Y') $data[$numArticlesOrder]['csv'] += array('pezzi_confezione' => '');
				
			$data[$numArticlesOrder]['csv'] += array(
					'prezzo_unita' => __('CostMore'),
					'importo' => number_format($importo_cost_more,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));	
		}
			
		/*
		 * SCONTO
		*/		
		if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y') {
			$numArticlesOrder++;
				
			$data[$numArticlesOrder]['csv'] = array(
					'N' => '',
					'bio' => '');
			
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$data[$numArticlesOrder]['csv'] += array('codice' => '');
				
			$data[$numArticlesOrder]['csv'] += array(
					'name' => '',
					'qta' => '',
					'pezzi_confezione' => '');
			
			if($pezzi_confezione1=='Y') $data[$numArticlesOrder]['csv'] += array('pezzi_confezione' => '');
				
			$data[$numArticlesOrder]['csv'] += array(
					'prezzo_unita' => __('CostLess'),
					'importo' => number_format($importo_cost_less,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));
		}
			
		/*
		 * TOTALE
		*/		
		if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
			($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
			($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y') {
			$numArticlesOrder++;
			
			$data[$numArticlesOrder]['csv'] = array(
					'N' => '',
					'bio' => '');
				
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
				$data[$numArticlesOrder]['csv'] += array('codice' => '');
			
			$data[$numArticlesOrder]['csv'] += array(
					'name' => '',
					'qta' => '',
					'pezzi_confezione' => '');
				
			if($pezzi_confezione1=='Y') $data[$numArticlesOrder]['csv'] += array('pezzi_confezione' => '');
			
			$data[$numArticlesOrder]['csv'] += array(
					'prezzo_unita' => __('Totale'),
					'importo' => number_format($importo_completo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));			
		}		
		
	}

}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 
			

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