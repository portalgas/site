<?php 
/*
 * C A S S I E R E - T O - U S E R S 
 *   Documento della consegna completa diviso per utente (per pagamento dell'utente) 
 */
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

// define table cells
$table[] =	array('label' => __('qta'), 'width' => 'auto');
$table[] =	array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('PrezzoUnita'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Importo'), 'width' => 'auto', 'filter' => true);
if($trasportAndCost=='Y') {
	$table[] = array('label' => __('TrasportShort'), 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => __('CostMoreShort'), 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => __('CostLessShort'), 'width' => 'auto', 'filter' => true);
	$table[] = array('label' => __('Totale'), 'width' => 'auto', 'filter' => true);
}

$importo_completo_all_orders = 0;
$user_label = '';
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

	$rowsExcel = [];
	
	$rowsExcel[] = __('Delivery');
	if($delivery['sys']=='N')
		$rowsExcel[] = $result['Delivery']['luogoData'];
	else
		$rowsExcel[] = $result['Delivery']['luogo'];
	$this->PhpExcel->addTableRow($rowsExcel);

	
	if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

		foreach($result['Delivery']['Order'] as $numOrder => $order) {

			if(!empty($order['ExportRows'])) { // lo user non ha effettuato acquisti sull'ordine legato alla consegna
				$rowsExcel = [];
				$rowsExcel[] = __('Supplier').' '.$order['SuppliersOrganization']['name'].', '.$order['SuppliersOrganization']['descrizione'];
				$this->PhpExcel->addTableRow($rowsExcel);
			}


			
			$html = '';							
			foreach ($order['ExportRows'] as $rows) {
				
				$user_id_local = current(array_keys($rows));
				$rows = current(array_values($rows));
				
				foreach ($rows as $typeRow => $cols) {
						
					$rowsExcel = [];
						
					switch ($typeRow) {
						case 'TRGROUP':
						
							$user_label = $cols['LABEL'];
													
							$rowsExcel[] = $cols['LABEL'];
							
							if($user_phone=='Y')
								$rowsExcel[] =  ' '.$cols['LABEL_PHONE'];
							if($user_email=='Y')
								$rowsExcel[] =  ' '.$cols['LABEL_EMAIL'];
							if($user_address=='Y')
								$rowsExcel[] =  ' '.$cols['LABEL_ADDRESS'];
								
							/*
							 * estraggo il totale di un utente 
							 */
							foreach ($order['ExportRows'] as $rows2) {
								$user_id2 = current(array_keys($rows2));
								$rows2 = current(array_values($rows2));
								foreach ($rows2 as $typeRow2 => $cols2) 
									if($typeRow2 == 'TRSUBTOT' && $user_id2 == $user_id_local) 
										if($trasportAndCost=='Y')  
											$rowsExcel[] = 'Totale: '.$cols2['IMPORTO_COMPLETO'];
										else
											$rowsExcel[] = 'Totale: '.$cols2['IMPORTO'];
							}
							$this->PhpExcel->addTableRow($rowsExcel);							

							// heading
							$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));
															
						break;
						case 'TRSUBTOT':
							$rowsExcel[] = $cols['QTA'];
							$rowsExcel[] = 'Totale dell\'utente';
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
							
							$importo_completo_all_orders += $cols['IMPORTO_COMPLETO'];
							
							$this->PhpExcel->addTableRow($rowsExcel);
						break;
						case 'TRTOT':
						break;								
						case 'TRDATA':
							
							$name = $this->ExportDocs->prepareCsv($cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']));
							
							$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['QTA']);
							$rowsExcel[] = $name;
							$rowsExcel[] = $this->ExportDocs->prepareCsv($cols['PREZZO']);
							$rowsExcel[] = $cols['IMPORTO'];
					
							$this->PhpExcel->addTableRow($rowsExcel);
						break;
						case 'TRDATABIS':
							$rowsExcel[] = 'NOTA: '.$cols['NOTA'];
							$this->PhpExcel->addTableRow($rowsExcel);
						break;
					}
				} // end foreach ($rows as $typeRow => $cols)
							
			} // end foreach ($exportRows as $rows)
						
		}  // end foreach($result['Delivery']['Order'] as $numOrder => $order)
		
	}
	else {
			$rowsExcel = [];
			$rowsExcel[] = __('Supplier').' '.$order['SuppliersOrganization']['name'].', '.$order['SuppliersOrganization']['descrizione'];
			$this->PhpExcel->addTableRow($rowsExcel);
			
			$rowsExcel[] = __('export_docs_not_found');
			$this->PhpExcel->addTableRow($rowsExcel);
	}	
	
	
        /*
         * D I S P E N S A
         */
        $i = 0;
        if (isset($storeroomResults['Delivery'][$numDelivery])) {

            $delivery = $storeroomResults['Delivery'][$numDelivery];

            if ($delivery['totStorerooms']) {

				$rowsExcel = [];
				$rowsExcel[] = '';
				$this->PhpExcel->addTableRow($rowsExcel);
				
				$rowsExcel = [];
				$rowsExcel[] = __('Storeroom');
				$this->PhpExcel->addTableRow($rowsExcel);


				$rowsExcel = [];
				$rowsExcel[] = __('Bio');
				$rowsExcel[] = __('Name');
				$rowsExcel[] = __('Conf');
				$rowsExcel[] = __('PrezzoUnita');
				$rowsExcel[] = __('PrezzoUM');
				$rowsExcel[] = __('Acquistato');
				$rowsExcel[] = __('Importo');
				$this->PhpExcel->addTableRow($rowsExcel);


				$tot_qta = 0;
				$tot_importo = 0;
                $supplier_organization_id_old = 0;
                foreach ($delivery['Storeroom'] as $numStoreroom => $storeroom) {

                    if ($storeroom['SuppliersOrganization']['id'] != $supplier_organization_id_old) {

						$rowsExcel = [];
						$rowsExcel[] = __('Supplier') . ': ' . $storeroom['SuppliersOrganization']['name'];
						$this->PhpExcel->addTableRow($rowsExcel);						
                    }

                    if ($storeroom['Article']['bio'] == 'Y')
                        $bio = 'Bio';
					else
						$bio = '';
					
					$rowsExcel = [];
					$rowsExcel[] = $bio;
					$rowsExcel[] = $this->ExportDocs->prepareCsv($storeroom['name']);
					$rowsExcel[] = $this->ExportDocs->prepareCsv($this->App->getArticleConf($storeroom['Article']['qta'], $storeroom['Article']['um']));
					$rowsExcel[] = $storeroom['prezzo'];
					$rowsExcel[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzoUM($storeroom['prezzo'], $storeroom['Article']['qta'], $storeroom['Article']['um'], $storeroom['Article']['um_riferimento']));
					$rowsExcel[] = $this->ExportDocs->prepareCsv($storeroom['qta']);
					$rowsExcel[] = $this->ExportDocs->prepareCsv($this->App->getArticleImporto($storeroom['prezzo'], $storeroom['qta']));
					$this->PhpExcel->addTableRow($rowsExcel);
					
					$tot_qta = ($tot_qta + $storeroom['qta']);
					$tot_importo = ($tot_importo + ($storeroom['prezzo'] * $storeroom['qta']));
					$importo_completo_all_orders += $tot_importo;
					
                    $supplier_organization_id_old = $storeroom['SuppliersOrganization']['id'];
                }
                $html .= '</tbody>';
                
                $tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
                
                
				$rowsExcel = [];
				$rowsExcel[] = '';
				$rowsExcel[] = '';
				$rowsExcel[] = '';
				$rowsExcel[] = '';
				$rowsExcel[] = '';
				$rowsExcel[] = $tot_qta;
				$rowsExcel[] = $tot_importo;
				$this->PhpExcel->addTableRow($rowsExcel);
				
            } // end if($delivery['totStorerooms'])			
        } // end if(isset($storeroomResults['Delivery'][$numDelivery])) 
			
}  // end foreach($results['Delivery'] as $numDelivery => $result['Delivery']) 



/*
 * dati pagamento
*/
if($importo_completo_all_orders>0) {

	$rowsExcel = [];
	
	/* 
	 * user_id puo' essere ALL => importo totale della consenga
	 */ 
	if($user_id > 0)
		$rowsExcel[] = __('Importo').' per l\'utente '.$user_label.':';
	else
		$rowsExcel[] = __('Importo').' della consegna:';
	
	$rowsExcel[] = $importo_completo_all_orders;
	if($summaryDeliveriesPosResults['SummaryDeliveriesPos']['importo']!='0.00') {
		$rowsExcel[] = $summaryDeliveriesPosResults['SummaryDeliveriesPos']['importo'];	
		$rowsExcel[] = 'Importo POS';	
	}
	
	if(!empty($organizationResults['Organization']['banca_iban']))
		$rowsExcel[] = 'IBAN: '.$organizationResults['Organization']['banca_iban'];
	
	if(!empty($organizationResults['Organization']['banca']))
		$rowsExcel[] = 'Banca: '.$organizationResults['Organization']['banca'];
	
	// $rowsExcel[] = $organizationResults['Organization']['name'];
	if(!empty($organizationResults['Organization']['cf']))
		$rowsExcel[] = 'Codice Fiscale: '.$organizationResults['Organization']['cf'].' - ';
	if(!empty($organizationResults['Organization']['piva']))
		$rowsExcel[] = 'Partita IVA: '.$organizationResults['Organization']['piva'] ;
	$this->PhpExcel->addTableRow($rowsExcel);
}




$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>