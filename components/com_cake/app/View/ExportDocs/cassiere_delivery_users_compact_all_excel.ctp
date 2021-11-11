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

$importo_completo_user = 0;
$importo_completo_all_orders = 0;
$tot_importo_pos = 0;
$importo_pos = 0;
$user_label = '';
$id_user_old = 0;

foreach($results as $deliveryResults) {
	$deliveries = $deliveryResults['Delivery'];

	foreach($deliveries as $numDelivery => $delivery) {

		$rowsExcel = [];
	
		if($delivery['totOrders']>0 /* && $delivery['totArticlesOrder']>0 */) {

			foreach($delivery['Order'] as $numOrder => $order) {

				foreach ($order['ExportRows'] as $rows) {
					
					$user_id_local = current(array_keys($rows));
					$rows = current(array_values($rows));
					foreach ($rows as $typeRow => $cols) {

						$rowsExcel = [];
					
						switch ($typeRow) {
							case 'TRGROUP':
							
								if($user_id_local!=$id_user_old)
									$importo_completo_user = 0;						
							
								$user_label = substr($cols['LABEL'], strlen("Utente: "), strlen($cols['LABEL']));
							break;
							case 'TRSUBTOT':							
								$rowsExcel[] = '';
								$rowsExcel[] = '';
								$rowsExcel[] = '';
								$rowsExcel[] = '';
								$rowsExcel[] = $cols['IMPORTO'];
								if($trasportAndCost=='Y') {
									if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00')  
										$rowsExcel[] = $cols['IMPORTO_TRASPORTO'];
									else 
										$rowsExcel[] = '';
									if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') 
										$rowsExcel[] = $cols['IMPORTO_COST_MORE'];
									else 
										$rowsExcel[] = '';
									if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00') 
										$rowsExcel[] = $cols['IMPORTO_COST_LESS'];
									else 
										$rowsExcel[] = '';

									$rowsExcel[] = $cols['IMPORTO_COMPLETO'];
								}

								$importo_completo_user += $cols['IMPORTO_COMPLETO']; 
								$importo_completo_all_orders += $cols['IMPORTO_COMPLETO'];
								
								$importo_pos = 0;
								if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
									if(isset($deliveries[$numDelivery]['SummaryDeliveriesPos']))
										$importo_pos = $deliveries[$numDelivery]['SummaryDeliveriesPos'][$user_id_local]['importo'];
								}
																
								$tot_importo_pos += $importo_pos;
																
								$this->PhpExcel->addTableRow($rowsExcel);
							break;
							case 'TRTOT':
							break;								
							case 'TRDATA':								
								$name = $this->ExportDocs->prepareCsv($cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']));
								
								$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['QTA']);
								$rowsExcel[] = $name;
								$rowsExcel[] = $user_label;
								$rowsExcel[] = $cols['PREZZO'];
								$rowsExcel[] = $cols['IMPORTO'];
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
								
					if($user_id_local>0) // quando $typeRow == TRTOT
						$id_user_old = $user_id_local;
								
				} // end foreach ($exportRows as $rows)
								
			}  // end foreach($delivery['Order'] as $numOrder => $order)
				
		}
		else {
			$rowsExcel = [];
			$rowsExcel[] = __('Supplier').' '.$order['SuppliersOrganization']['name'].', '.$order['SuppliersOrganization']['descrizione'];
			$this->PhpExcel->addTableRow($rowsExcel);
			
			$rowsExcel[] = __('export_docs_not_found');
			$this->PhpExcel->addTableRow($rowsExcel);
		}	
	}  // loop($results['Delivery'] as $numDelivery => $delivery) 
	
	/*
	 * totale utente
	*/
	if($importo_completo_user>0) {

		$rowsExcel = [];
		$rowsExcel[] =  '';
		$rowsExcel[] =  '';
		$rowsExcel[] =  '';
		$rowsExcel[] =  '';
		
		if($trasportAndCost=='Y') {
			$rowsExcel[] =  '';
			$rowsExcel[] =  '';
			$rowsExcel[] =  '';
			$rowsExcel[] =  '';
			$rowsExcel[] = $importo_completo_user;
		}
		else
			$rowsExcel[] = $importo_completo_user;

		/*
		 *  eventuale importo POS
		 */
		if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
			if($importo_pos>0) {
				$rowsExcel[] =  $importo_pos;
				$rowsExcel[] =  'Importo POS';
			}
		}
		
		$this->PhpExcel->addTableRow($rowsExcel);		
	}
	
} // loop users

/*
 * totale orders
*/
if($importo_completo_all_orders>0) {

	$rowsExcel = [];
	$rowsExcel[] =  '';
	$rowsExcel[] =  '';
	$rowsExcel[] =  '';
	$rowsExcel[] =  '';
	
	if($trasportAndCost=='Y') {
		$rowsExcel[] =  '';
		$rowsExcel[] =  '';
		$rowsExcel[] =  '';
		$rowsExcel[] =  '';
		$rowsExcel[] = $importo_completo_all_orders;
	}
	else
		$rowsExcel[] = $importo_completo_all_orders;

	/*
	 *  eventuale importo POS
	 */
	if($user->organization['Organization']['hasFieldPaymentPos']=='Y') {
		if($tot_importo_pos>0) {
			$rowsExcel[] =  $tot_importo_pos;
			$rowsExcel[] =  'Importo POS';
		}
	}
	
	$this->PhpExcel->addTableRow($rowsExcel);		
}

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>