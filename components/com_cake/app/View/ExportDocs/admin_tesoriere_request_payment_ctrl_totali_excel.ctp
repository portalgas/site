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
        $table[] = ['label' => __('Importo totale ordine').' calcolato quando l\'ordine passa al tesoriere', 'width' => 'auto', 'filter' => false];
        $table[] = ['label' => __('Importo totale ordine').' calcolato ora', 'width' => 'auto', 'filter' => false];
        $table[] = ['label' => __('Importo totale calcolato su tutti gli acquisti calcolato ora'), 'width' => 'auto', 'filter' => false];
        // $table[] = ['label' => __('Differenza'), 'width' => 'auto', 'filter' => false];
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

            foreach ($acquisti_totali as $order_id => $acquisto_totali) {
                if($order_id==$result['Order']['id']) {
                    $totale = $acquisto_totali['totale'];
                    $rows[] = ''.$acquisto_totali['tot_importo'];
                    $rows[] = ''.$totale;
                    unset($acquisti_totali[$order_id]);
                    break;
                }
            }
            // $rows[] = ''.($result['Order']['tot_importo'] - $totale);

            $rows[] = ''.$result['Order']['tesoriere_fattura_importo'];

			$this->PhpExcel->addTableRow($rows);
		
			$delivery_id_old = $result['Delivery']['id'];
		} // end foreach($results['Order'] as $i => $result)
		
		$rows = [];
		$rows[] = '';
		$this->PhpExcel->addTableRow($rows);		 
} 

$this->PhpExcel->addTableFooter();

$this->PhpExcel->output($fileData['fileName'].'.xlsx'); // anche x pdf
?>