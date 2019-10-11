<?php

$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

// define table cells
$table[] = array('label' => __('Delivery'), 'width' => 'auto');
$table[] = array('label' => __('Delivery') . ' ' . __('Data'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Category'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Order') . ' ' . __('DataInizio'), 'width' => 'auto');
$table[] = array('label' => __('Order') . ' ' . __('DataFine'), 'width' => 'auto');
$table[] = array('label' => __('SuppliersOrganization'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Importo totale ordine'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Tesoriere fattura importo'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Tesoriere Importo Pay'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Tesoriere Data Pay'), 'width' => 'auto', 'filter' => true);
$table[] = array('label' => __('Fattura'), 'width' => 'auto', 'filter' => false);


// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

// data
if (isset($results) && !empty($results))
    foreach ($results as $result) {

        $rows = [];

        $rows[] = $result['StatDelivery']['luogo'];
        $rows[] = $result[0]['StatDelivery_data'];
        $rows[] = $result['CategoriesSupplier']['name'];
        $rows[] = $result[0]['StatOrder_data_inizio'];
        $rows[] = $result[0]['StatOrder_data_fine'];
        $rows[] = $result['StatOrder']['supplier_organization_name'];
        $rows[] = $result['StatOrder']['StatOrder_importo'];
        $rows[] = $result['StatOrder']['tesoriere_fattura_importo'];
        $rows[] = $result['StatOrder']['tesoriere_importo_pay'];
        $rows[] = $result['StatOrder']['tesoriere_data_pay'];
        
        if(!empty($result['StatOrder']['tesoriere_doc1']) && file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$user->organization['Organization']['id'].DS.$result['StatOrder']['tesoriere_doc1'])) {
            $rows[] = Configure::read('App.server').Configure::read('App.web.doc.upload.tesoriere').'/'.$user->organization['Organization']['id'].'/'.$result['StatOrder']['tesoriere_doc1'];
        }            
  
        $this->PhpExcel->addTableRow($rows);
    }
$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'] . '.xlsx');
?>