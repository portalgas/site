<?php 
$csv = [];

if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
	$csv += array('codice' => __('Codice'));

$csv += array('name' => __('Name'),
			  'qta' => $this->ExportDocs->prepareCsvAccenti(__('qta')).' totale',
			  'importo' => __('Importo').' totale');

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


$data = "";

$totRows=0;
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {		foreach($result['Delivery']['Order'] as $numOrder => $order) {
			foreach ($order['ExportRows'] as $rows) {					
				$user_id = current(array_keys($rows));				$rows = current(array_values($rows));								foreach ($rows as $typeRow => $cols) {

					switch ($typeRow) {						case 'TRGROUP':
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
									
								$data[$totRows]['csv'] = array(
										'name' => $this->ExportDocs->prepareCsv($label),
										'qta' => $qta_totale_dell_utente,
										'importo' => $importo_totale_dell_utente, // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])
								);
								
								if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
									$data[$totRows]['csv'] = array('trasporto' => $importo_trasporto);
								
								if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
									$data[$totRows]['csv'] = array('cost_more' => $importo_cost_more);
								
								if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
									$data[$totRows]['csv'] = array('cost_less' => $importo_cost_less);
								
								if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
									($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
									($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
									$data[$totRows]['csv'] = array('importo_completo' => $importo_completo);
							}
							else 
								$data[$totRows]['csv'] = array('name' => $this->ExportDocs->prepareCsv($label));
							
							$totRows++;						break;						case 'TRSUBTOT':
							$totRows++;
						break;						case 'TRTOT':
							$data[$totRows]['csv'] = array(									'name' => '',									'qta' => $cols['QTA'],									'importo' => $cols['IMPORTO'], // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])							);
							
							if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] = array('trasporto' => $cols['IMPORTO_TRASPORTO']);
							
							if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] = array('cost_more' => $cols['IMPORTO_COST_MORE']);
							
							if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00' && $trasportAndCost=='Y')
								$data[$totRows]['csv'] = array('cost_less' => $cols['IMPORTO_COST_LESS']);
							
							if((($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
								($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
								($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) && $trasportAndCost=='Y')
								$data[$totRows]['csv'] = array('importo_completo' => $cols['IMPORTO_COMPLETO']);
							
							$totRows++;						break;						case 'TRDATA':
							$totRows++;						break;						case 'TRDATABIS':
							$data[$totRows]['csv'] = array(									'name' => $cols['NOTA']							);	
							$totRows++;						break;					}										}
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