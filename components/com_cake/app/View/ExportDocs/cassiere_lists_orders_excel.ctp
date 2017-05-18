<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

// define table cells
$table[] =	array('label' => __('N.'), 'width' => 'auto');
$table[] =	array('label' => __('User'), 'width' => 'auto');
$table[] = array('label' => __('Importo Dovuto'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Importo Pagato'), 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('Differenza'), 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('Cassa'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Modalità'), 'width' => 'auto', 'filter' => true);
if($user->organization['Organization']['hasFieldPaymentPos']=='Y')
	$table[] = array('label' => __('Importo POS'), 'width' => 'auto', 'filter' => true);

if (!empty($results['Order'])) {

		$tot_importo_delivery = 0;
		$tot_importo_pagato_delivery = 0;
		$tot_differenza_delivery = 0;
		foreach ($results['Order'] as $numResult => $order) {

			$rowsExcel = array();
			$rowsExcel[] =  '';
			$this->PhpExcel->addTableRow($rowsExcel);
			
			$rowsExcel = array();
			$rowsExcel[] = '';
			$rowsExcel[] = __('Supplier').' '.$order['SuppliersOrganization']['name'];
			$rowsExcel[] = 'dal '.$this->Time->i18nFormat($order['data_inizio'], "%e %b %Y").' al '.$this->Time->i18nFormat($order['data_fine'], "%e %b %Y");
			if($order['tesoriere_fattura_importo']>0)
				$rowsExcel[] = __('Tesoriere fattura importo Short').' '.number_format($order['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			if($order['tot_importo']>0)
				$rowsExcel[] = __('Importo totale ordine Short').' '.number_format($order['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			$rowsExcel[] = 'Utenti che devono pagare '.$order['totUserToTesoriere'];
			$this->PhpExcel->addTableRow($rowsExcel);


			// heading
			$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));
			
			
			$tot_importo = 0;
			$tot_importo_pagato = 0;
			$tot_differenza = 0;
			$tot_importo_cash = 0;
			$tot_importo_pos = 0;			
			foreach ($order['SummaryOrder'] as $numResult2 => $result) {

					$importo = number_format($result['SummaryOrder']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$importo_pagato = number_format($result['SummaryOrder']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
					
					$differenza = (-1 * ($result['SummaryOrder']['importo'] - $result['SummaryOrder']['importo_pagato']));
					$differenza = number_format($differenza,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
											
					if(isset($result['Cash']['importo']))
						$importo_cash = number_format($result['Cash']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));	
					else
						$importo_cash = '0,00';
						
					$rowsExcel = array();
					$rowsExcel[] = ($numResult2+1);
					$rowsExcel[] = $result['User']['name'];
					$rowsExcel[] = $importo;
					$rowsExcel[] = $importo_pagato;
					$rowsExcel[] = $differenza;
					$rowsExcel[] = $importo_cash;
					if($result['SummaryOrder']['modalita']!='DEFINED')
						$rowsExcel[] = $this->App->traslateEnum($result['SummaryOrder']['modalita']);
					if($user->organization['Organization']['hasFieldPaymentPos']=='Y' && $result['SummaryOrder']['modalita']=='BANCOMAT')
						$rowsExcel[] = $result['SummaryOrdersPos']['importo'];

					
					$this->PhpExcel->addTableRow($rowsExcel);
					
					$tot_importo += $result['SummaryOrder']['importo'];
					$tot_importo_pagato += $result['SummaryOrder']['importo_pagato'];
					$tot_differenza += (-1 * ($result['SummaryOrder']['importo'] - $result['SummaryOrder']['importo_pagato']));
					
					$tot_importo_delivery += $result['SummaryOrder']['importo'];
					$tot_importo_pagato_delivery += $result['SummaryOrder']['importo_pagato'];
					$tot_differenza_delivery += (-1 * ($result['SummaryOrder']['importo'] - $result['SummaryOrder']['importo_pagato']));
					
					$tot_importo_cash += $result['Cash']['importo'];
					
					$tot_importo_pos += $result['SummaryOrdersPos']['importo'];			
			} // loop summaryOrders

			/*
			 * totale ordine
			 */
			$tot_importo = number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_importo_pagato = number_format($tot_importo_pagato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$tot_differenza = number_format($tot_differenza,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			$rowsExcel = array();
			$rowsExcel[] =  '';
			$rowsExcel[] =  '';
			$rowsExcel[] = $tot_importo;
			$rowsExcel[] = $tot_importo_pagato;
			$rowsExcel[] = $tot_differenza;
			$rowsExcel[] =  '';
			$this->PhpExcel->addTableRow($rowsExcel);
			
		} // loop orders
			
	/*
	 * totale consegna
	 */
	$tot_importo_delivery = number_format($tot_importo_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_importo_pagato_delivery = number_format($tot_importo_pagato_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$tot_differenza_delivery = number_format($tot_differenza_delivery,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	$rowsExcel = array();
	$rowsExcel[] =  '';
	$rowsExcel[] =  '';
	$rowsExcel[] =  $tot_importo_delivery;
	$rowsExcel[] =  $tot_importo_pagato_delivery;
	$rowsExcel[] =  $tot_differenza_delivery;
	$rowsExcel[] =  $tot_importo_cash;
	$rowsExcel[] =  '';
	if($user->organization['Organization']['hasFieldPaymentPos']=='Y')
		$rowsExcel[] = $tot_importo_pos;
	$this->PhpExcel->addTableRow($rowsExcel);
}


$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>