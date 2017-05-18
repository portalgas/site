<?php  
/*
 * C A S S I E R E - T O - U S E R S 
 *   Documento della consegna completa diviso per utente (per pagamento dell'utente) 
 */

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

$rowsExcel[] = __('Delivery');
if($resultDelivery['Delivery']['sys']=='N')
	$rowsExcel[] = $resultDelivery['Delivery']['luogoData'];
else
	$rowsExcel[] = $resultDelivery['Delivery']['luogo'];
$this->PhpExcel->addTableRow($rowsExcel);
			
// define table cells
$table[] =	array('label' => __('qta'), 'width' => 'auto');
$table[] =	array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('User'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => true);
if($trasportAndCost=='Y') {
	$table[] = array('label' => __('TrasportShort'), 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => __('CostMoreShort'), 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => __('CostLessShort'), 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => __('Totale'), 'width' => 'auto', 'filter' => true);
}
$table[] = array('label' => __('Supplier'), 'width' => 'auto', 'filter' => true);

// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

$importo_completo_all_orders = 0;
$importo_pos = 0;
$user_label = '';

foreach($results as $deliveryResults) {
	$deliveries = $deliveryResults['Delivery'];

	foreach($deliveries as $numDelivery => $delivery) {

		$rowsExcel = array();
	
		if($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0) {

			foreach($delivery['Order'] as $numOrder => $order) {

				foreach ($order['ExportRows'] as $rows) {
					
					$user_id_local = current(array_keys($rows));
					$rows = current(array_values($rows));
					foreach ($rows as $typeRow => $cols) {

						$rowsExcel = array();
					
						switch ($typeRow) {
							case 'TRGROUP':
								$user_label = substr($cols['LABEL'], strlen("Utente: "), strlen($cols['LABEL']));
							break;
							case 'TRSUBTOT':							
								$rowsExcel[] = '';
								$rowsExcel[] = '';
								$rowsExcel[] = '';
								$rowsExcel[] = '';
								$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['IMPORTO']);
								if($trasportAndCost=='Y') {
									if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00')  
										$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_TRASPORTO']);
									else 
										$rowsExcel[] = '';
									if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') 
										$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_MORE']);
									else 
										$rowsExcel[] = '';
									if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00') 
										$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_COST_LESS']);
									else 
										$rowsExcel[] = '';

									$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['IMPORTO_COMPLETO']);
								}

								$importo_completo_all_orders += $this->ExportDocs->prepareCsv($cols['IMPORTO_COMPLETO_DOUBLE']);
								
								$importo_pos = 0;
								if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
									if(isset($deliveries[$numDelivery]['SummaryDeliveriesPos']))
										$importo_pos = $deliveries[$numDelivery]['SummaryDeliveriesPos'][$user_id_local]['importo'];
								}
																
								$this->PhpExcel->addTableRow($rowsExcel);
							break;
							case 'TRTOT':
							break;								
							case 'TRDATA':								
								$name = $this->ExportDocs->prepareCsv($cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']));
								
								$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['QTA']);
								$rowsExcel[] = $name;
								$rowsExcel[] = $user_label;
								$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['PREZZO']);
								$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['IMPORTO']);
								if($trasportAndCost=='Y') {
									$rowsExcel[] = '';
									$rowsExcel[] = '';
									$rowsExcel[] = '';
									$rowsExcel[] = '';
								}								
								$rowsExcel[] = $order['SuppliersOrganization']['name'];
								
								$this->PhpExcel->addTableRow($rowsExcel);
							break;
							case 'TRDATABIS':
							break;
						}
						
					} // end foreach ($rows as $typeRow => $cols)
								
				} // end foreach ($exportRows as $rows)
								
			}  // end foreach($delivery['Order'] as $numOrder => $order)
				
		}
		else {
			$rowsExcel = array();
			$rowsExcel[] = __('Supplier').' '.$order['SuppliersOrganization']['name'].', '.$order['SuppliersOrganization']['descrizione'];
			$this->PhpExcel->addTableRow($rowsExcel);
			
			$rowsExcel[] = __('export_docs_not_found');
			$this->PhpExcel->addTableRow($rowsExcel);
		}	
	}  // loop($results['Delivery'] as $numDelivery => $delivery) 
	
	/*
	 * totale utente
	*/
	if($importo_completo_all_orders>0) {

		$importo_completo_all_orders = number_format($importo_completo_all_orders,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

		$rowsExcel = array();
		$rowsExcel[] =  '';
		$rowsExcel[] =  '';
		$rowsExcel[] =  '';
		$rowsExcel[] =  '';
		
		if($trasportAndCost=='Y') {
			$rowsExcel[] =  '';
			$rowsExcel[] =  '';
			$rowsExcel[] =  '';
			$rowsExcel[] =  '';
			$rowsExcel[] = ' '.$importo_completo_all_orders;
		}
		else
			$rowsExcel[] = ' '.$importo_completo_all_orders;

		/*
		 *  eventuale importo POS
		 */
		if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
			if($importo_pos>0) {
				$rowsExcel[] =  ' '.$importo_pos;
				$rowsExcel[] =  'Importo POS';
			}
		}

		
		$this->PhpExcel->addTableRow($rowsExcel);
		
		$importo_completo_all_orders = 0;
	}
	
} // loop users

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>