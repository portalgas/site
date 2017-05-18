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


			// define table cells			$table[] =	array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false);			$table[] =	array('label' => __('qta').' totale', 'width' => 'auto', 'filter' => true);
			$table[] = array('label' => __('Importo').' totale', 'width' => 'auto', 'filter' => true);						if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')  				$table[] = array('label' => __('Trasport'), 'width' => auto, 'wrap' => true, 'filter' => false);
			
			if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y') 
				$table[] = array('label' => __('CostMore'), 'width' => auto, 'wrap' => true, 'filter' => false);
		
			if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y') 
				$table[] = array('label' => __('CostLess'), 'width' => auto, 'wrap' => true, 'filter' => false);		
			
			if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') || 
				($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
				($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y') 
				$table[] = array('label' => __('Totale'), 'width' => auto, 'wrap' => true, 'filter' => false);
			
						// heading			$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));				
			foreach ($order['ExportRows'] as $rows) {		
			
				$user_id = current(array_keys($rows));				$rows = current(array_values($rows));				
				foreach ($rows as $typeRow => $cols) {
			
					switch ($typeRow) {
						case 'TRGROUP':
							$label = $cols['LABEL'];
							/*
							 * tolgo Utente: 
							 */
							$label = str_replace("Utente: ", "", $label);
							
							if($user_phone=='Y')
								$label .=  ' '.$cols['LABEL_PHONE'];
							if($user_email=='Y')
								$label .=  ' '.$cols['LABEL_EMAIL'];
							if($user_address=='Y')
								$label .=  ' '.$cols['LABEL_ADDRESS'];
															
							$rows = array();
							$rows[] = $this->ExportDocs->prepareCsv($label);
							
							if($totale_per_utente=='Y') {
							
								/*
								 * estraggo il totale di un utente
								*/
								foreach ($order['ExportRows'] as $rows2) {
									$user_id2 = current(array_keys($rows2));
									$rows2 = current(array_values($rows2));
									foreach ($rows2 as $typeRow2 => $cols2)
									if($typeRow2 == 'TRSUBTOT' && $user_id2 == $user_id) {
										$qta_totale_dell_utente = $cols2['QTA'];
										$importo_totale_dell_utente = $cols2['IMPORTO'];
										$importo_trasporto = $cols2['IMPORTO_TRASPORTO'];
										$importo_cost_more = $cols2['IMPORTO_COST_MORE'];
										$importo_cost_less = $cols2['IMPORTO_COST_LESS'];
										$importo_completo = $cols2['IMPORTO_COMPLETO'];
									}
								}
							
								$rows[] = (int)$this->ExportDocs->prepareCsv($qta_totale_dell_utente);
								$rows[] = $this->ExportDocs->prepareCsv($importo_totale_dell_utente);
								
								if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y') 
									$rows[] = $this->ExportDocs->prepareCsv($importo_trasporto);
								
								if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y') 
									$rows[] = $this->ExportDocs->prepareCsv($importo_cost_more);
								
								if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y') 
									$rows[] = $this->ExportDocs->prepareCsv($importo_cost_less);
								
								if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
									($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
									($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y') 
									$rows[] = $this->ExportDocs->prepareCsv($importo_completo);
							}
							
						break;
						case 'TRSUBTOT':

						break;
						case 'TRTOT':
							$rows = array();
							$rows[] = '';
							$rows[] = (int)$cols['QTA'];							$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO']);
							

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
						break;								
						case 'TRDATA':

						break;
						case 'TRDATABIS':
							case 'TRDATA':								$rows = array();								$rows[] = $cols['NOTA'];
						break;
					}
					
					if(($typeRow=='TRSUBTOT' && $totale_per_utente=='N') || $typeRow=='TRDATA' || $typeRow=='TRSUBTOT') {}
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