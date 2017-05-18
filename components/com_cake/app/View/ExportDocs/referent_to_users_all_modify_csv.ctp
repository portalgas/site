<?php 
$headers = array('csv' => array(
		'qta' => $this->ExportDocs->prepareCsvAccenti(__('qta')),
		'importo_utente' => "Importo dell'utente",
		'qta_referente' => $this->ExportDocs->prepareCsvAccenti("Quantità totale modificata dal referente"),
		'importo_referente' => "Importo totale modificato dal referente",
		'importo_forzato' => "Importo forzato"
	)
$csv = array(

$totRows=0;
$data = array();
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {
			
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

					switch ($typeRow) {
						case 'TRGROUP':
							
							$totRows++;
							
							$data[$totRows]['csv'] = array(
									'N' => 'Totale dell\'utente',
									'name' => '',
									'prezzo_unita' => '',
									'prezzo_um' => '',
									'qta' => $cols['QTA'],
									'importo' => $this->ExportDocs->prepareCsv($cols['IMPORTO']), // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])
									'qta_utente' => $cols['QTA'],
									'importo_utente' => '',
									'qta_referente' => '',
									'importo_referente' => '',
									'importo_forzato' => '');

							if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('trasporto' => $this->ExportDocs->prepareCsv($cols['IMPORTO_TRASPORTO']));

							if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('cost_more' => $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_MORE']));

							if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('cost_less' => $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_LESS']));
							
							if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') || 
								($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
								($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('importo_completo' => $this->ExportDocs->prepareCsv($cols['IMPORTO_COMPLETO']));
							
							$totRows++;
							break;
							
							$data[$totRows]['csv'] = array(
								'N' => 'Totale',
								'name' => '',
								'prezzo_unita' => '',
								'prezzo_um' => '',
								'qta' => $cols['QTA'],
								'importo' => $this->ExportDocs->prepareCsv($cols['IMPORTO']), // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])
								'qta_utente' => $cols['QTA'],
								'importo_utente' => '',
								'qta_referente' => '',
								'importo_referente' => '',
								'importo_forzato' => '');
					
							if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('trasporto' => $this->ExportDocs->prepareCsv($cols['IMPORTO_TRASPORTO']));
							
							if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('cost_more' => $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_MORE']));
							
							if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('cost_less' => $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_LESS']));
								
							if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
								($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
								($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
								$data[$totRows]['csv'] += array('importo_completo' => $this->ExportDocs->prepareCsv($cols['IMPORTO_COMPLETO']));
							
							$totRows++;
							break;
							$name = $this->ExportDocs->prepareCsv($cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UMRIF']));
							
							$cols['PREZZO_UM'] = $cols['PREZZO_UMRIF'];
							$cols['IMPORTO'] = $this->ExportDocs->prepareCsv($cols['IMPORTO']);
							$cols['IMPORTOUSER'] = $this->ExportDocs->prepareCsv($cols['IMPORTOUSER']);
							$cols['IMPORTOFORZATO'] = $this->ExportDocs->prepareCsv($cols['IMPORTOFORZATO']);
							
									'importo_utente' => $this->ExportDocs->prepareCsv($cols['IMPORTOUSER']),
									'qta_referente' => $cols['QTAREF'],
									'importo_referente' => $cols['IMPORTOREF'],
									'importo_forzato' => $cols['IMPORTOFORZATO']
							
							$totRows++;
									'N' => '',
									'name' => $cols['NOTA']
							);	

							$totRows++;
						break;
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