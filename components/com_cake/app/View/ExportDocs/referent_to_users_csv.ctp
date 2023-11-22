<?php 
$data = "";

$totRows=0;
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {
			
			$csv = [];
			$csv += array('N' => '');
			
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
				$csv += array('codice' => __('Codice'));
			
			$csv += array('name' => __('Name'),
					'prezzo_unita' => $this->ExportDocs->prepareCsvAccenti($this->ExportDocs->prepareCsvAccenti(__('PrezzoUnita'))),
					'prezzo_um' => __('Prezzo/UM'),
					'qta' => $this->ExportDocs->prepareCsvAccenti(__('qta')),
					'importo' => __('Importo'));
			
			if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
				$csv += array('trasporto' => __('Trasport'));
			
			if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
				$csv += array('cost_more' => __('CostMore'));
			
			if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
				$csv += array('cost_less' => __('CostLess'));
			
			if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
					($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
					($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
				$csv += array('importo_completo' => __('Totale'));
			
			$headers = array('csv' => $csv);
			
			
			foreach ($order['ExportRows'] as $rows) {
					
				$user_id = current(array_keys($rows));
				$rows = current(array_values($rows));
				
				foreach ($rows as $typeRow => $cols) {

					switch ($typeRow) {
						case 'TRGROUP':
							$label = $cols['LABEL'];
							
							if(!empty($cols['LABEL_CODICE']))
								$label .= ' ('.$cols['LABEL_CODICE'].')';

							if($user_phone=='Y')
								$label .=  ' '.$cols['LABEL_PHONE'];
							if($user_email=='Y')
								$label .=  ' '.$cols['LABEL_EMAIL'];
							if($user_address=='Y')
								$label .=  ' '.$cols['LABEL_ADDRESS'];
								
							$data[$totRows]['csv'] = array('N' => $this->ExportDocs->prepareCsv($label));	
							
							$totRows++;							
						break;
						case 'TRSUBTOT':
							if($totale_per_utente=='Y') {
								
								$data[$totRows]['csv'] = [];
								$data[$totRows]['csv'] += array('N' => '');
								
								if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
									$data[$totRows]['csv'] += array('codice' => '');
								
								$data[$totRows]['csv'] += array('name' => 'Totale dell\'utente',
														'prezzo_unita' => '',
														'prezzo_um' => '',
														'qta' => $cols['QTA'],
														'importo' => $cols['IMPORTO']); // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])
										
								if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
									$data[$totRows]['csv'] += array('trasporto' => $cols['IMPORTO_TRASPORTO']);
								
								if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
									$data[$totRows]['csv'] += array('cost_more' => $cols['IMPORTO_COST_MORE']);
								
								if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
									$data[$totRows]['csv'] += array('cost_less' => $cols['IMPORTO_COST_LESS']);
								
								if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
									($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
									($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
									$data[$totRows]['csv'] += array('importo_completo' => $cols['IMPORTO_COMPLETO']);
																		
								$totRows++;
							}
						break;
						case 'TRTOT':
							$data[$totRows]['csv'] = [];
							$data[$totRows]['csv'] += array('N' => '');
							
							if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
								$data[$totRows]['csv'] += array('codice' => '');
						
							$data[$totRows]['csv'] += array('name' => 'Totale',
															'prezzo_unita' => '',
															'prezzo_um' => '',
															'qta' => $cols['QTA'],
															'importo' => $cols['IMPORTO']); // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']);

							if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('trasporto' => $cols['IMPORTO_TRASPORTO']);
							
							if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('cost_more' => $cols['IMPORTO_COST_MORE']);
							
							if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('cost_less' => $cols['IMPORTO_COST_LESS']);
							
							if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
								($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
								($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('importo_completo' => $cols['IMPORTO_COMPLETO']);
							
							$totRows++;				
						break;
						case 'TRDATA':
							$name = $this->ExportDocs->prepareCsv($cols['NAME'].' '.$this->ExportDocs->prepareCsv($this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM'])));
							if($cols['DELETE_TO_REFERENT']=='Y') $name .= " (CANCELLATO)";
							$cols['PREZZO_UM'] = $cols['PREZZO_UMRIF'];
							if($cols['DELETE_TO_REFERENT']=='Y') 
								$cols['IMPORTO'] = '0.00';
							else
								$cols['IMPORTO'] = $cols['IMPORTO'];
														
							$data[$totRows]['csv'] = [];
							$data[$totRows]['csv'] += array('N' => '');
							
							if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
								$data[$totRows]['csv'] += array('codice' => $this->ExportDocs->prepareCsv($cols['CODICE']));
							
							$data[$totRows]['csv'] += array('name' => $name,
													'prezzo_unita' => $cols['PREZZO'], 
													'prezzo_um' => $this->ExportDocs->prepareCsv($cols['PREZZO_UM']), 
													'qta' => $cols['QTA'], // $this->App->traslateQtaImportoModificati($cols['ISQTAMOD']),
													'importo' => $cols['IMPORTO']); // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])
								

							if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('trasporto' => '');
							
							if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('cost_more' => '');
							
							if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('cost_less' => '');
							
							if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
								($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
								($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('importo_completo' => '');
							
							$totRows++;									
						break;
						case 'TRDATABIS':
							$data[$totRows]['csv'] = array(
									'N' => '',
									'codice' => $cols['NOTA']
							);	
							$totRows++;							
						break;
					}
						
				}
			}					
		}
	}
}


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