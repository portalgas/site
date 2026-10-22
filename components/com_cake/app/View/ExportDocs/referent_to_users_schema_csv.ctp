<?php
$csv = [];

if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $codice=='Y')
    $csv += array('codice' => __('Codice'));

if($qta_article=='Y') 
    $csv += array('name' => __('Name'),
        'note' => __('Note'),
        'qta' => $this->ExportDocs->prepareCsvAccenti(__('qta')).' totale',
        'importo' => __('Importo').' totale');
else 
    $csv += array('name' => __('Name'),
        'note' => __('Note'),
        'importo' => __('Importo').' totale');

$headers = ['csv' => $csv];


$data = "";

$totRows=0;
foreach($results['Delivery'] as $numDelivery => $result['Delivery']) {

    if($result['Delivery']['totOrders']>0 && $result['Delivery']['totArticlesOrder']>0) {

        foreach($result['Delivery']['Order'] as $numOrder => $order) {
            foreach ($order['ExportRows'] as $rows) {

                $user_id = current(array_keys($rows));
                $rows = current(array_values($rows));

                foreach ($rows as $typeRow => $cols) {

                    $data[$totRows] = [];
                    $data[$totRows]['csv'] = [];

                    switch ($typeRow) {
                        case 'TRGROUP':
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

                            if($qta_article=='Y') 
                                $data[$totRows]['csv'] = [ 
                                    'name' => $this->ExportDocs->prepareCsv($label),
                                    'note' => '',
                                    'qta' => $qta_totale_dell_utente,
                                    'importo' => $importo_totale_dell_utente, // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])
                                ];
                            else 
                                $data[$totRows]['csv'] = [
                                    'name' => $this->ExportDocs->prepareCsv($label),
                                    'note' => '',
                                    'importo' => $importo_totale_dell_utente, // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])
                                ];
                            $totRows++;
                            break;
                        case 'TRSUBTOT':
                            $totRows++;
                            break;
                        case 'TRTOT':
                            if($qta_article=='Y') 
                                $data[$totRows]['csv'] = [
                                    'name' => '',
                                    'note' => '',
                                    'qta' => $cols['QTA'],
                                    'importo' => $cols['IMPORTO'], // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])
                                ];
                            else  
                                $data[$totRows]['csv'] = [
                                    'name' => '',
                                    'note' => '',
                                    'importo' => $cols['IMPORTO'], // $this->App->traslateQtaImportoModificati($cols['ISIMPORTOMOD'])                                    
                                ];
                            $totRows++;
                            break;
                        case 'TRDATA':
                            $totRows++;
                            break;
                        case 'TRDATABIS':
                            $totRows++;
                            break;
                    } // end switch ($typeRow)

                } // end foreach ($rows as $typeRow => $cols)
            } // foreach ($order['ExportRows'] as $rows)
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