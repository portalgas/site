<?php 
if($this->layout=='pdf') {
	App::import('Vendor','xtcpdf');
	
	$output = new XTCPDF($organization, PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$output->headerText = $fileData['fileTitle'];
			
	// add a page
	$output->AddPage();
	$css = $output->getCss();
}
else 
if($this->layout=='ajax') {
	App::import('Vendor','xtcpreview');
	$output = new XTCPREVIEW();
	$css = $output->getCss();
}

/*
 * escludo le consegne senza ordini con acquisti
 */
if(isset($results['RequestPaymentsOrder']))
foreach($results['RequestPaymentsOrder'] as $numRequestPaymentsOrder => $results2)
foreach($results2['Delivery'] as $numDelivery => $result) {

	$found_order=false;
	foreach($result['Order'] as $numOrder => $order) {
		if(!empty($order['ExportRows'])) 
			$found_order=true;
	}
	
	if(!$found_order)
		unset($results['RequestPaymentsOrder'][$numRequestPaymentsOrder]['Delivery'][$numDelivery]);
}	


/*
 * R E Q U E S T P A Y M E N T S - O R D E R 
 */
$delivery_id_old = 0;
if(isset($results['RequestPaymentsOrder']))
foreach($results['RequestPaymentsOrder'] as $numRequestPaymentsOrder => $requestPaymentsOrderResults) {
foreach($requestPaymentsOrderResults['Delivery'] as $numDelivery => $result) {
	
	if($delivery_id_old==0 || $delivery_id_old!=$result['id']) {
		$html = $this->ExportDocs->delivery($result);
		$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}
	
	
	/*
	 * lo commento se no mi escludo gli eventuali dati inseriti ex-novo da SummaryOrder
	 * if($result['totOrders']>0 && $result['totArticlesOrder']>0) {
	 */
		foreach($result['Order'] as $numOrder => $order) {

			if(!empty($order['ExportRows'])) {

				$html = $this->ExportDocs->suppliersOrganization($order['SuppliersOrganization']);
				$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
				
				$html = '';
				$html .= '	<table cellpadding="0" cellspacing="0">';
				$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
				$html .= '		<tr>';
				if(($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
					($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
					($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) {
					//$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
					$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()).'">'.__('Name').'</th>';
					$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('PrezzoUnita').'</th>';
					$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('Prezzo/UM').'</th>';
					$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH50().'">'.__('qta').'</th>';
					$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">'.__('Importo').'</th>';
					$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">';
					if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00')
						$html .= __('Trasport').'<br />';
					if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') 
						$html .= __('CostMore').'<br />';
					if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')
						$html .= __('CostLess');
					$html .= '</th>';
					$html .= '			<th width="'.$output->getCELLWIDTH70().'" style="text-align:right;">Totale</th>';
				}
				else {
				//	$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
					$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()+$output->getCELLWIDTH20()).'">'.__('Name').'</th>';
					$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('PrezzoUnita').'</th>';
					$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Prezzo/UM').'</th>';
					$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH50().'">'.__('qta').'</th>';
					$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH30()).'" style="text-align:right;">'.__('Importo').'</th>';
				}
				$html .= '	    </tr>';	
				$html .= '	</thead><tbody>';

	
				foreach ($order['ExportRows'] as $rows) {
				
					$user_id = current(array_keys($rows));
					$rows = current(array_values($rows));
						
					$html .= '<tr>';
				
					foreach ($rows as $typeRow => $cols) {
				
						switch ($typeRow) {
							case 'TRGROUP':
								if(($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
									($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
									($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) 
									$colspan = '7';
								else
									$colspan = '5';
								$html .= '<td colspan="'.$colspan.'" >'.$cols['LABEL'].'</td>';
							break;
							case 'TRSUBTOT':
								$html .= '<td colspan="3" style="text-align:right;">Totale&nbsp;dell\'utente&nbsp;</td>';
								$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH50().'">&nbsp;'.$cols['QTA'].'</td>';
								$html .= '<td style="text-align:right;">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
								if(($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
									($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
									($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) {
										
									$html .= '<td style="text-align:right;">';
									if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') $html .= __('TrasportShort').' '.$cols['IMPORTO_TRASPORTO_E'].'<br />';
									if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') $html .= __('CostMoreShort').' '.$cols['IMPORTO_COST_MORE_E'].'<br />';
									if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00') $html .= __('CostLessShort').' '.$cols['IMPORTO_COST_LESS_E'];
									$html .= '</td>';
										
									$html .= '<td style="text-align:right;">';
									$html .= $cols['IMPORTO_COMPLETO_E'];
									$html .= '</td>';
								}								
							break;
							case 'TRTOT':
							/*
								$html .= '<td colspan="3" style="text-align:right;">'.__('qta_tot').'&nbsp;</td>';
								$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH50().'">'.$cols['QTA'].'</td>';
								$html .= '<td style="text-align:right;">&nbsp;'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
								if(($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
									($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
									($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) {
									
									$html .= '<td style="text-align:right;">';
									if($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') $html .= __('TrasportShort').' '.$cols['IMPORTO_TRASPORTO_E'].'<br />';
									if($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') $html .= __('CostMoreShort').' '.$cols['IMPORTO_COST_MORE_E'].'<br />';
									if($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00') $html .= __('CostLessShort').' '.$cols['IMPORTO_COST_LESS_E'];
									$html .= '</td>';
										
									$html .= '<td style="text-align:right;">';
									$html .= $cols['IMPORTO_COMPLETO_E'];
									$html .= '</td>';
								}
							*/
							break;								
							case 'TRDATA':
							
								$name = $cols['NAME'].' '.$this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']);
							
								if(($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
									($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
									($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) {
										
									//$html .= '<td width="'.$output->getCELLWIDTH20().'">'.$cols['NUM'].'</td>';
									$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()).'">'.$name.'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$cols['PREZZO_E'].'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$cols['PREZZO_UMRIF'].'</td>';
									$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH50().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
									$html .= '<td style="text-align:right;" width="'.$output->getCELLWIDTH70().'">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
									$html .= '<td width="'.($output->getCELLWIDTH70()+$output->getCELLWIDTH70()).'"  colspan="2" style="text-align:right;">&nbsp;</td>';
								}
								else {
									//$html .= '<td width="'.$output->getCELLWIDTH20().'">'.$cols['NUM'].'</td>';
									$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH20()+$output->getCELLWIDTH20()).'">'.$name.'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$cols['PREZZO_E'].'</td>';
									$html .= '<td width="'.$output->getCELLWIDTH100().'">'.$cols['PREZZO_UMRIF'].'</td>';
									$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH50().'">'.$cols['QTA'].$this->App->traslateQtaImportoModificati($cols['ISQTAMOD']).'</td>';
									$html .= '<td style="text-align:right;" width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH30()).'">'.$cols['IMPORTO_E'].$this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD']).'</td>';
								}
							break;
							case 'TRDATABIS':
							if(($order['Order']['hasTrasport']=='Y' && $order['Order']['trasport']!='0.00') ||
									($order['Order']['hasCostMore']=='Y' && $order['Order']['cost_more']!='0.00') ||
									($order['Order']['hasCostLess']=='Y' && $order['Order']['cost_less']!='0.00')) 
									$colspan = '6';
								else
									$colspan = '4';
																
								//$html .= '<td width="'.$output->getCELLWIDTH20().'"></td>';
								$html .= '<td colspan="'.$colspan.'">NOTA: '.$cols['NOTA'].'</td>';
							break;						
						}
					} // end foreach ($rows as $typeRow => $cols)
						
					$html .= '</tr>';
						
				} // end foreach ($exportRows as $rows)
				
				$html .= '</tbody></table>';			
				$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
				$html = '';
				$html = $this->ExportDocs->suppliersOrganizationsReferent($order['SuppliersOrganizationsReferent']);
				$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
			} // end if(!empty($order['ExportRows'])) 
		}  // end foreach($result['Order'] as $numOrder => $order)
			
		$html = '';
		$html = $output->getLegenda();
		$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		
	/*}
	else {
		$html = '<div class="h4PdfNotFound">'.__('export_docs_not_found').'</div>';
		$output->writeHTMLCell(0,0,15,40, $css.$html, $border=0, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}*/	
	
	$delivery_id_old = $result['id'];

	}  // end foreach($results['Delivery'] as $numDelivery => $result) 
}


/*
 * R E Q U E S T P A Y M E N T S - S T O R E R O O M
 */
if(!empty($results['RequestPaymentsStoreroom'])) {
	/*
	echo "<pre>";
	print_r($results['RequestPaymentsStoreroom']);
	echo "</pre>";
	*/
	$html  = '';
	$html .= '<div class="h1Pdf">';
	$html .= __('RequestPaymentsStoreroom');
	$html .= '</div>';
	$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
	$html = '';
	$html .= '	<table cellpadding="0" cellspacing="0">';
	$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
	$html .= '		<tr>';
	$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
	$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'">'.__('Name').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('Conf').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('PrezzoUnita').'</th>'; 
	$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('Prezzo/UM').'</th>';
	$html .= '			<th style="text-align:center;" width="'.$output->getCELLWIDTH70().'">'.__('qta').'</th>'; 
	$html .= '			<th width="'.$output->getCELLWIDTH70().'">'.__('Importo').'</th>';
	$html .= '		</tr>';
	$html .= '	</thead><tbody>'; 
	
	foreach($results['RequestPaymentsStoreroom'] as $numResult => $result) {

		$html .= '<tr>';
		
		$html .= '<td width="'.$output->getCELLWIDTH20().'">'.((int)$numResult+1).'</td>';
		$html .= '<td width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'">'.$result['Storeroom']['name'].'</td>';
		$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
		$html .= '<td width="'.$output->getCELLWIDTH70().'">'.number_format($result['Article']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
		$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$this->App->getArticlePrezzoUM($result['Storeroom']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
		$html .= '<td style="text-align:center;" width="'.$output->getCELLWIDTH70().'">'.$result['Storeroom']['qta'].'</td>';
		$html .= '<td width="'.$output->getCELLWIDTH70().'">'.$this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']).'</td>';
		$html .= '</tr>';
		
	}
	
	$html .= '</tbody></table>';
	$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
} // if(!empty($results['RequestPaymentsStoreroom']))


/*
 * R E Q U E S T P A Y M E N T S - G E N E R I C 
 */
if(!empty($results['RequestPaymentsGeneric'])) {		

	$html  = '';
	$html .= '<div class="h1Pdf">';
	$html .= __('RequestPaymentsGeneric');
	$html .= '</div>';
	$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
	$html = '';
	$html .= '	<table cellpadding="0" cellspacing="0">';
	$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
	$html .= '		<tr>';
	$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH300().'">'.__('Name').'</th>';
	$html .= '			<th width="'.$output->getCELLWIDTH300().'">'.__('Importo').'</th>';
	$html .= '		</tr>';
	$html .= '	</thead><tbody>'; 

	foreach($results['RequestPaymentsGeneric'] as $numResult => $result) {
		$importo = number_format($result['RequestPaymentsGeneric']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
		$html .= '<tr>';
		
		$html .= '<td width="'.$output->getCELLWIDTH20().'">'.((int)$numResult+1).'</td>';
		$html .= '<td width="'.$output->getCELLWIDTH300().'">'.$result['RequestPaymentsGeneric']['name'].'</td>';
		$html .= '<td width="'.$output->getCELLWIDTH300().'">'.$importo.'&nbsp;&euro;</td>';
		
		$html .= '</tr>';
		
	}
	
	$html .= '</tbody></table>';
	$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	
} // if(!empty($results['RequestPaymentsGeneric']))



/*
 * S U M M A R Y  P A Y M E N T S  +  C A S H
 *
 *   importo_dovuto
 *        +/- cassa
 *	 importo_richiesto
 */
$html = '';
$html .= '	<table cellpadding="0" cellspacing="0">';
$html .= '	<tbody>'; 

$delta_cassa = (-1 * (floatval($results['SummaryPayment']['importo_dovuto']) - floatval($results['SummaryPayment']['importo_richiesto'])));
// echo "<br />delta_cassa (importo_dovuto - importo_richiesto) => ".$results['SummaryPayment']['importo_dovuto']." - ".$results['SummaryPayment']['importo_richiesto']." = ".$delta_cassa;

$delta_cassa = number_format($delta_cassa,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
$importo_dovuto = number_format($results['SummaryPayment']['importo_dovuto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
$importo_richiesto = number_format($results['SummaryPayment']['importo_richiesto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
$html .= '<tr>';
$html .= '<td width="'.$output->getCELLWIDTH20().'" class="h1Pdf"></td>';
$html .= '<td width="'.$output->getCELLWIDTH300().'" style="text-align:right;" class="h1Pdf">';
$html .= __('Importo_dovuto');
$html .= '</td>';
$html .= '<td width="'.$output->getCELLWIDTH300().'" style="text-align:left;" class="h1Pdf">';
$html .= '&nbsp;&nbsp;<b>'.$importo_dovuto.'</b>&nbsp;&euro;';
$html .= '</td>';
$html .= '</tr>';

if($delta_cassa > 0) {

	/*
	 *    user ha un DEBITO con la cassa
	 */
	$html .= '<tr>';
	$html .= '<td width="'.$output->getCELLWIDTH20().'" class="h1Pdf"></td>';
	$html .= '<td width="'.$output->getCELLWIDTH300().'" style="text-align:right;" class="h1Pdf">';
	$html .= 'Debito&nbsp;verso&nbsp;cassa';
	$html .= '</td>';
	$html .= '<td width="'.$output->getCELLWIDTH300().'" style="text-align:left;" class="h1Pdf">';
	$html .= '&nbsp;<b>&nbsp;'.$delta_cassa.'</b>&nbsp;&euro;';
	$html .= '</td>';
	$html .= '</tr>';
}
else 
if($delta_cassa < 0) {

	/*
	 *    user ha un CREDITO con la cassa
	 */
	$html .= '<tr>';
	$html .= '<td width="'.$output->getCELLWIDTH20().'" class="h1Pdf"></td>';
	$html .= '<td width="'.$output->getCELLWIDTH300().'" style="text-align:right;" class="h1Pdf">';
	$html .= 'Credito&nbsp;verso&nbsp;la&nbsp;cassa';
	$html .= '</td>';
	$html .= '<td width="'.$output->getCELLWIDTH300().'" style="text-align:left;" class="h1Pdf">';
	$html .= '&nbsp;<b>'.$delta_cassa.'</b>&nbsp;&euro;';
	$html .= '</td>';
	$html .= '</tr>';
}
		
$html .= '<tr>';
$html .= '<td width="'.$output->getCELLWIDTH20().'" class="h1Pdf"></td>';
$html .= '<td width="'.$output->getCELLWIDTH300().'" style="text-align:right;" class="h1Pdf">';
if($importo_richiesto!='0,00')
	$html .= 'Effettuare il pagamento dell\'importo di';
$html .= '</td>';
$html .= '<td width="'.$output->getCELLWIDTH300().'" style="text-align:left;" class="h1Pdf">';
$html .= '&nbsp;&nbsp;<b>'.$importo_richiesto.'</b>&nbsp;&euro;';
$html .= '</td>';
$html .= '</tr>';

$html .= '</tbody></table>';
$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

if($importo_richiesto!='0,00') {
	/*
	 * dati pagamento
	*/
	$html = '';
	$html .= '<br />';
	$html .= '<div width="'.($output->getCELLWIDTH20()+$output->getCELLWIDTH300()+$output->getCELLWIDTH300()).'" class="h3Pdf">'.__('Dati pagamento').':<br />';

	$html .= 'Causale:&nbsp;Richiesta num '.$request_payment_num.'&nbsp;di&nbsp;'.$userResults['User']['name'].'<br />';
	$html .= 'Importo:&nbsp;'.$importo_richiesto.'&nbsp;&euro;<br />';
	if(!empty($organizationResults['Organization']['banca_iban']))
		$html .= 'IBAN:&nbsp;'.$organizationResults['Organization']['banca_iban'].'<br />';

	if(!empty($organizationResults['Organization']['banca']))
		$html .= 'Banca:&nbsp;'.$organizationResults['Organization']['banca'].'<br />';

	$html .= '<br />';

	$html .= $organizationResults['Organization']['name'].'<br />';
	if(!empty($organizationResults['Organization']['cf']))
		$html .= 'Codice Fiscale:&nbsp;'.$organizationResults['Organization']['cf'].'&nbsp;-&nbsp;';
	if(!empty($organizationResults['Organization']['piva']))
		$html .= 'Partita IVA:&nbsp;'.$organizationResults['Organization']['piva'].'<br />';
	$html .= '<br />';
	$html .= '</div>';
	$output->writeHTML($css.$html, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
}


// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>