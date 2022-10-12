<?php 
/*
 * r e q u e s t - p a y m e n t 
 */

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);


$rows = [];
$rows[] = '';
$rows[] = "Rich. pagamento num.".$results['RequestPayment']['num'];
$rows[] = $this->Time->i18nFormat($results['RequestPayment']['stato_elaborazione_date'],"%A %e %B %Y");

$this->PhpExcel->addTableRow($rows);

if(count($results['Order'])>0) { 

		$table = [];
		$table[] =	['label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false];
		$table[] =	['label' =>  __('Delivery'), 'width' => 'auto'];
		$table[] = ['label' => __('Supplier'), 'width' => 'auto', 'filter' => false];
		$table[] = ['label' => __('Importo totale ordine'), 'width' => 'auto', 'filter' => false];
		$table[] = ['label' => __('Tesoriere fattura importo'), 'width' => 'auto', 'filter' => false];
	
		$this->PhpExcel->addTableHeader($table, ['name' => 'Cambria', 'bold' => true]);

		$delivery_id_old=0;
		foreach($results['Order'] as $i => $result) {
			
			$rows = [];
			$rows[] = ($i+1);
			if($result['Delivery']['id']!=$delivery_id_old)
				$rows[] = $result['Delivery']['luogo'].', del '.$this->Time->i18nFormat($result['Delivery']['data'],"%A %e %B %Y");
			else
				$rows[] = '';
			$rows[] = $result['SuppliersOrganization']['name'];
			$rows[] = ''.$result['Order']['tot_importo'];
			$rows[] = ''.$result['Order']['tesoriere_fattura_importo'];
		
			$this->PhpExcel->addTableRow($rows);
		
			$delivery_id_old = $result['Delivery']['id'];
		} // end foreach($results['Order'] as $i => $result)
		
		$rows = [];
		$rows[] = '';
		$this->PhpExcel->addTableRow($rows);		 
} 

/*
 *  storeroom
 */
if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
	if(!empty($results['Storeroom'])) { 
	
		$table = [];
		$table[] = array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
		$table[] = array('label' =>  __('Delivery'), 'width' => 'auto');

		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));
	
		foreach($results['Storeroom'] as $i => $storeroom) {

			$rows = [];
			$rows[] = ($i+1);
			$rows[] = $storeroom['Delivery']['luogo'].', di '.$this->Time->i18nFormat($storeroom['Delivery']['data'],"%A %e %B %Y");
			$rows[] = $this->App->formatDateCreatedModifier($storeroom['Delivery']['created']);
		
			$this->PhpExcel->addTableRow($rows);
		
		} // end foreach($results['Order'] as $i => $result) 
		
		$rows = [];
		$rows[] = '';
		$this->PhpExcel->addTableRow($rows);
				
	 } // end if(!empty($results['Storeroom'])) 
} // end if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y')


if(!empty($results['PaymentsGeneric'])) { 

	$table = [];
	$table[] = array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
	$table[] = array('label' =>  'Voce di spesa', 'width' => 'auto');
	$table[] = array('label' =>  __('User'), 'width' => 'auto');
	$table[] = array('label' =>  __('Importo'), 'width' => 'auto');
	$table[] = array('label' =>  _('Created'), 'width' => 'auto');

	$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

	foreach($results['PaymentsGeneric'] as $i => $requestPaymentsGeneric) {
	
			$rows = [];
			$rows[] = ($i+1);
			$rows[] = $requestPaymentsGeneric['RequestPaymentsGeneric']['name'];
			$rows[] = $requestPaymentsGeneric['User']['name'];
			$rows[] = $requestPaymentsGeneric['RequestPaymentsGeneric']['importo'];
			$rows[] = $this->Time->i18nFormat($requestPaymentsGeneric['RequestPaymentsGeneric']['created'],"%A %e %B %Y");
			
			$this->PhpExcel->addTableRow($rows);
	} 
	
	$rows = [];
	$rows[] = '';
	$this->PhpExcel->addTableRow($rows);
			
} // end if(!empty($results['PaymentsGeneric'])) 


$table = [];
$table[] =	array('label' => 'N', 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' =>  'Utente', 'width' => 'auto');
$table[] = array('label' => 'Mail', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => __('Importo_dovuto'), 'width' => 'auto', 'filter' => false);
$table[] = array('label' => __('Importo_richiesto'), 'width' => 'auto', 'filter' => false);
//$table[] = array('label' => __('Cash'), 'width' => 'auto', 'filter' => false);
$table[] = array('label' => __('Importo_pagato'), 'width' => 'auto', 'filter' => false);
$table[] = array('label' => 'Stato', 'width' => 'auto', 'filter' => false);
$table[] = array('label' => __('Modality'), 'width' => 'auto', 'filter' => false);
	
					
// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

$tot_importo_dovuto = 0;
$tot_importo_richiesto = 0;
$tot_importo_cash = 0;
$tot_importo_pagato = 0;
foreach($results['SummaryPayment'] as $num => $summaryPayment) {

	$rows = [];
	$rows[] = ($num+1);
	$rows[] = $summaryPayment['User']['name'];
	$rows[] = $summaryPayment['User']['email'];
	$rows[] = number_format($summaryPayment['SummaryPayment']['importo_dovuto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$rows[] = number_format($summaryPayment['SummaryPayment']['importo_richiesto'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	//$rows[] = number_format($summaryPayment['Cash']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$rows[] = number_format($summaryPayment['SummaryPayment']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	$rows[] = $this->App->traslateEnum($summaryPayment['SummaryPayment']['stato']);
	$rows[] = $this->App->traslateEnum($summaryPayment['SummaryPayment']['modalita']);
	
	$this->PhpExcel->addTableRow($rows);
			
	$tot_importo_dovuto = ($tot_importo_dovuto + $summaryPayment['SummaryPayment']['importo_dovuto']);
	$tot_importo_richiesto = ($tot_importo_richiesto + $summaryPayment['SummaryPayment']['importo_richiesto']);
	//$tot_importo_cash = ($tot_importo_cash + $summaryPayment['Cash']['importo']);
	$tot_importo_pagato = ($tot_importo_dovuto + $summaryPayment['SummaryPayment']['importo_pagato']);

    /*
     * dettaglio ordini dello user
     *
      * R E Q U E S T P A Y M E N T S - O R D E R
      */
    $delivery_id_old = 0;
    if(isset($summaryPayment['RequestPaymentsOrder']))
        foreach($summaryPayment['RequestPaymentsOrder'] as $numRequestPaymentsOrder => $requestPaymentsOrderResults) {
            foreach ($requestPaymentsOrderResults['Delivery'] as $numDelivery => $result) {

                if($result['sys']=='N')
                    $delivery = $result['luogoData'];
                else
                    $delivery = $result['luogo'];

                /*
                 * lo commento se no mi escludo gli eventuali dati inseriti ex-novo da SummaryOrder
                 * if($result['totOrders']>0 && $result['totArticlesOrder']>0) {
                 */
                foreach ($result['Order'] as $numOrder => $order) {

                    if (!empty($order['ExportRows'])) {

                        $suppliers_organization = $order['SuppliersOrganization']['name'];

                        $rows = [];
                        $this->PhpExcel->addTableRow($rows);

                        $rows = [];
                        $rows[] = '';
                        $rows[] = $delivery;
                        $rows[] = $suppliers_organization;
                        $this->PhpExcel->addTableRow($rows);

                        foreach ($order['ExportRows'] as $export_row) {

                            $user_id = current(array_keys($export_row));
                            $export_row = current(array_values($export_row));

                            foreach ($export_row as $typeRow => $cols) {

                                $rows = [];
                                switch ($typeRow) {
                                    case 'TRSUBTOT':
                                        $rows[] = '';
                                        $rows[] = '';
                                        $rows[] = 'Totale dell\'utente';
                                        $rows[] = $cols['IMPORTO_COMPLETO_'];
                                        $rows[] = $this->App->traslateQtaImportoModificatiDescri($cols['ISIMPORTOMOD']);

                                        if (($order['Order']['hasTrasport'] == 'Y' && $order['Order']['trasport'] != '0.00') ||
                                            ($order['Order']['hasCostMore'] == 'Y' && $order['Order']['cost_more'] != '0.00') ||
                                            ($order['Order']['hasCostLess'] == 'Y' && $order['Order']['cost_less'] != '0.00')) {

                                            $tmp = '';
                                            if ($order['Order']['hasTrasport'] == 'Y' && $order['Order']['trasport'] != '0.00')  $tmp .= __('TrasportShort') . ' ' . $cols['IMPORTO_TRASPORTO_'] . "\r\n";
                                            if ($order['Order']['hasCostMore'] == 'Y' && $order['Order']['cost_more'] != '0.00') $tmp .= __('CostMoreShort') . ' ' . $cols['IMPORTO_COST_MORE_'] . "\r\n";
                                            if ($order['Order']['hasCostLess'] == 'Y' && $order['Order']['cost_less'] != '0.00') $tmp .= __('CostLessShort') . ' ' . $cols['IMPORTO_COST_LESS_'];
                                            $rows[] = $tmp;
                                        }
                                        break;
                                    case 'TRDATA':

                                        $name = $cols['NAME']; //  . $this->App->getArticleConf($cols['ARTICLEQTA'], $cols['UM']);

                                        $rows[] = '';
                                        $rows[] = '';
                                        $rows[] = $name;
                                        $rows[] = $cols['IMPORTO_'];
                                        $rows[] = $this->App->traslateQtaImportoModificatiDescri($cols['ISIMPORTOMOD']);
                                        $rows[] = '';
                                        $rows[] = 'Qta '.$cols['QTA'];
                                        $rows[] = 'Prezzo unità '.$cols['PREZZO_'];

                                        break;
                                }

                                if(!empty($rows))
                                    $this->PhpExcel->addTableRow($rows);

                            } // end foreach ($rows as $typeRow => $cols)
                        } // end foreach ($order['ExportRows'] as $export_row)
                    }
                }
            }
        } // end loop dettaglio ordini dello user
} // foreach($results['SummaryPayment'] as $num => $summaryPayment)


$rows = [];
$rows[] = '';
$rows[] = '';
$rows[] = '';
$rows[] = number_format($tot_importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
$rows[] = number_format($tot_importo_richiesto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
//$rows[] = number_format($tot_importo_cash,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
$rows[] = number_format($tot_importo_pagato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

$this->PhpExcel->addTableRow($rows);

$this->PhpExcel->addTableFooter();

$this->PhpExcel->output($fileData['fileName'].'.xlsx'); // anche x pdf
?>