<?php 
/*
 * T O - U S E R S 
 *   Documento con elenco diviso per utente (per pagamento dell'utente) 
 */

$this->PhpExcel->createWorksheet();$this->PhpExcel->setDefaultFont('Calibri', 12);
// data
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {


			// define table cells
			$table[] =	array('label' => '', 'width' => 'auto');
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
				$table[] =	array('label' => __('Codice'), 'width' => 'auto', 'wrap' => true, 'filter' => false);			$table[] =	array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false);			$table[] = array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => true);
			$table[] = array('label' => __('Prezzo/UM'), 'width' => 'auto', 'filter' => true);
			$table[] =	array('label' => __('qta'), 'width' => 'auto', 'filter' => true);
			$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => true);			
			if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
				$table[] = array('label' => __('Trasport'), 'width' => auto, 'wrap' => true, 'filter' => false);
				
			if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
				$table[] = array('label' => __('CostMore'), 'width' => auto, 'wrap' => true, 'filter' => false);
			
			if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
				$table[] = array('label' => __('CostLess'), 'width' => auto, 'wrap' => true, 'filter' => false);
				
			if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
				($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
				($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
				$table[] = array('label' => __('Totale'), 'width' => auto, 'wrap' => true, 'filter' => false);
						// heading			$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));				
			foreach ($order['ExportRows'] as $rows) {		
			
				$user_id = current(array_keys($rows));				$rows = current(array_values($rows));				
				foreach ($rows as $typeRow => $cols) {
			
					switch ($typeRow) {
						case 'TRGROUP':
							$label = $cols['LABEL'];
							
							if($user_phone=='Y')
								$label .=  ' '.$cols['LABEL_PHONE'];
							if($user_email=='Y')
								$label .=  ' '.$cols['LABEL_EMAIL'];
							if($user_address=='Y')
								$label .=  ' '.$cols['LABEL_ADDRESS'];
															
							$rows = array();
							$rows[] = $this->ExportDocs->prepareCsv($label);
						break;
						case 'TRSUBTOT':
							if($totale_per_utente=='Y') {
								$rows = array();
								$rows[] = '';
								if($user->organization['Organization']['hasFieldArticleCodice']=='Y')									$rows[] = '';
								$rows[] = "Totale dell'utente";
								$rows[] = '';
								$rows[] = '';
								$rows[] = $cols['QTA'];
								$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO']);	

								if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
									$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_TRASPORTO']);
								
								if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
									$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_MORE']);
								
								if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
									$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_LESS']);
								
								if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
									($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
									($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
									$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_COMPLETO']);
							}
						break;
						case 'TRTOT':
							$rows = array();							$rows[] = "Totale";
							if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
								$rows[] = '';														$rows[] = '';							$rows[] = '';
							$rows[] = '';
							$rows[] = $cols['QTA'];							$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO']);
							
							if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
								$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_TRASPORTO']);
							
							if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
								$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_MORE']);
							
							if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
								$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_LESS']);
							
							if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
								($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
								($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
								$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_COMPLETO']);						break;								
						case 'TRDATA':
							$name = $this->ExportDocs->prepareCsv($cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UMRIF']));
							if($cols['DELETE_TO_REFERENT']=='Y') $name .= " (CANCELLATO)";
							$codice = $this->ExportDocs->prepareCsv($cols['CODICE']);
									
							$rows = array();
							$rows[] = '';							if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
								$rows[] = $codice;							$rows[] = $name;							$rows[] = $cols['PREZZO_'];							$rows[] = $this->ExportDocs->prepareCsv($cols['PREZZO_UMRIF']);							$rows[] = $cols['QTA']; // $this->App->traslateQtaImportoModificati($cols['ISQTAMOD'])
							if($cols['DELETE_TO_REFERENT']=='Y') 
								$rows[] = '0,00';
							else								$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO']); // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])
							if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
								$rows[] = '';
								
							if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
								$rows[] = '';
								
							if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
								$rows[] = '';
								
							if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
								($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
								($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
								$rows[] = '';		
						break;
						case 'TRDATABIS':
							case 'TRDATA':								$rows = array();								$rows[] = $cols['NOTA'];
						break;
					}
					
					if($typeRow=='TRSUBTOT' && $totale_per_utente=='N') {}
					else
						$this->PhpExcel->addTableRow($rows);
						
				} // end foreach ($rows as $typeRow => $cols)
							
			} // end foreach ($exportRows as $rows)
			
		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
					
	}
}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 

		    
$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>