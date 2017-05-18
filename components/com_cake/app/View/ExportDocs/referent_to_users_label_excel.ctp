<?php 
/*
 * T O - U S E R S - L A B E L 
 *   Documento con elenco diviso per utente in formato etichetta (per la consegna) 
 */
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);


$totale = 0;
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {

							
				
				$user_id = current(array_keys($rows));
				$rows = current(array_values($rows));

						
					switch ($typeRow) {
						case 'TRGROUP':
						
							$rows = array();
							$rows[] = $cols['LABEL'];
							
							/*
							 * estraggo il totale di un utente 
							 */
							foreach ($order['ExportRows'] as $rows2) {
								$user_id2 = current(array_keys($rows2));
									if($typeRow2 == 'TRSUBTOT' && $user_id2 == $user_id) {
										if($trasportAndCost=='Y') {
											$totale += $cols2['IMPORTO_COMPLETO_DOUBLE'];
											$rows[] = $this->ExportDocs->prepareCsv($cols2['IMPORTO_COMPLETO']);
										}
										else {
											$totale += $cols2['IMPORTO_DOUBLE'];
											$rows[] = $this->ExportDocs->prepareCsv($cols2['IMPORTO']);
										}
									}
							if($user_phone=='Y')
								$rows[] = $cols['LABEL_PHONE'];
							if($user_email=='Y')
								$rows[] = $cols['LABEL_EMAIL'];
							if($user_address=='Y')
								$rows[] = $cols['LABEL_ADDRESS'];
		
							$this->PhpExcel->addTableRow($rows);
			
		

							// define table cells
							$table = array();
							$table[] = array('label' => __('qta'), 'width' => 'auto', 'filter' => false);
							$table[] = array('label' => __('Name'), 'width' => 'auto', 'filter' => false);
							$table[] =	array('label' => __('PrezzoUnita'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
							$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => false);
							
							if($trasportAndCost=='Y') {
								$table[] =	array('label' => __('TrasportAndCost'), 'width' => 'auto', 'filter' => false);
								$table[] = array('label' => __('Totale'), 'width' => 'auto', 'filter' => false);
							}
							
							// heading
							$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));
	
						break;
						case 'TRSUBTOT':
						
							$rows = array();
							$rows[] = $cols['QTA'];
							$rows[] = '';
							$rows[] = '';
							$rows[] = $this->ExportDocs->prepareCsv($cols['IMPORTO']);
							if($trasportAndCost=='Y') {
								
								if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') $rows[] = __('TrasportShort').' '.$cols['IMPORTO_TRASPORTO'].'<br />';
								if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') $rows[] = __('CostMoreShort').' '.$cols['IMPORTO_COST_MORE'].'<br />';
								if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00') $rows[] = __('CostLessShort').' '.$cols['IMPORTO_COST_LESS'];

								$rows[] = $cols['IMPORTO_COMPLETO'];
							}

							$this->PhpExcel->addTableRow($rows);
							
							
							
							$rows = array();
							$rows[] = '';
							$rows[] = '';
							$rows[] = '';
							$rows[] = '';
							if($trasportAndCost=='Y') {
								$rows[] = '';
							}
							$this->PhpExcel->addTableRow($rows);
							
						break;
						case 'TRTOT':
						break;								
						case 'TRDATA':
							
							$name = $this->ExportDocs->prepareCsv($cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']));
							if($cols['DELETE_TO_REFERENT']=='Y') $name .= " (CANCELLATO)";
							
							$rows = array();
							$rows[] = $cols['QTA'];
							$rows[] = $name;
							if($cols['DELETE_TO_REFERENT']=='Y')
								$rows[] = '0,00';
							else
								$rows[] = '';
							
							$this->PhpExcel->addTableRow($rows);
						case 'TRDATABIS':
							$rows = array();
							$rows[] = $cols['NOTA'];
							
							$this->PhpExcel->addTableRow($rows);
						break;
					}
					
					/*
					 *  lo devo mettere ad ogni case perche' ho la label con lo user
					 *  $this->PhpExcel->addTableRow($rows);
					 */
					
					
				} // end foreach ($rows as $typeRow => $cols)
							
			} // end foreach ($exportRows as $rows)
			
		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
	
		/*
		 *  totale
		 */		
		$rows = array();
		$rows[] = 'Totale dell\'ordine';
		$rows[] = '';
		$rows[] = '';
		$rows[] = number_format($totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$this->PhpExcel->addTableRow($rows);
	}
	else {
		$rows[] = __('export_docs_not_found');
		$this->PhpExcel->addTableRow($rows);
	}	
}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 


$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>