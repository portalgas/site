<?php

$csv = array(
            'N' => 'N',
            'bio' => __('Bio'));

if($showCodice=='Y') 
	$csv += array('codice' => __('Codice'));

$csv += array('name' => __('Name'));

if($isToValidate) {
        $csv += array('colli' => ('Colli completati'));
        $csv += array('colli_delta' => ('Mancano per il collo'));
        $csv += array('colli_da' => ('Collo da'));
}


$csv += array('qta' => __('qta'));
$csv += array('prezzoUnita' => __('PrezzoUnita'));
$csv += array('importo' => __('Importo'));

$headers = array('csv' => $csv);



$i=0;
$tot_qta = 0;
$tot_importo = 0;
if(isset($results))
foreach($results as $result) {

        $name = $result['ArticlesOrder']['name'].' '.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);

        $data[$i]['csv'] = array('N' => ($i+1));

        if($result['Article']['bio']=='Y') $data[$i]['csv'] += array('bio' => 'Bio');
        else $data[$i]['csv'] += array('bio' => '');
        if($showCodice=='Y')
            $data[$i]['csv'] += array('codice' => $result['Article']['codice']);

        $data[$i]['csv'] += array('name' => $this->ExportDocs->prepareCsv($name));

  
        if($isToValidate) {
            if($result['ArticlesOrder']['pezzi_confezione']>1) {
                /*
                 * colli_completi / differenza_da_ordinare
                 */
                $colli_completi = intval($result['Cart']['qta'] / $result['ArticlesOrder']['pezzi_confezione']);
                if($colli_completi>0) {
                    $differenza_da_ordinare = (($result['ArticlesOrder']['pezzi_confezione'] * $colli_completi) - $result['Cart']['qta']);
                    if($differenza_da_ordinare<0) $differenza_da_ordinare = -1 * $differenza_da_ordinare;
                }
                else {
                        $differenza_da_ordinare = ($result['ArticlesOrder']['pezzi_confezione'] - $result['Cart']['qta']);
                        $colli_completi = '-';
                }        	
        }
                
            
        if($result['ArticlesOrder']['pezzi_confezione']>1)  $data[$i]['csv'] += array('colli' => $colli_completi);
        else $data[$i]['csv'] += array('colli' => '');
            
        if($result['ArticlesOrder']['pezzi_confezione']>1) {
            	
                if($differenza_da_ordinare != $result['ArticlesOrder']['pezzi_confezione'])  
                    $data[$i]['csv'] += array('colli_delta' => $differenza_da_ordinare);
                else
                    $data[$i]['csv'] += array('colli_delta' => '0');
                
                $data[$i]['csv'] += array('colli_da' => $result['ArticlesOrder']['pezzi_confezione']);
            }
            else {
                $data[$i]['csv'] += array('colli_delta' => '');
                $data[$i]['csv'] += array('colli_da' => '');
            }
        }
 
        $data[$i]['csv'] += array('qta' =>  $result['Cart']['qta']);				
        $data[$i]['csv'] += array('prezzoUnita' =>  $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($result['ArticlesOrder']['prezzo'])));
        $data[$i]['csv'] += array('importo' => number_format($result['Cart']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));

	$tot_importo += $result['Cart']['importo'];
	$tot_qta += $prezzo = $result['Cart']['qta'];
	
	$i++;
        
}  // loop articoli
	
$numResult++;	

/*
 * totali
 */
$data[$i]['csv'] = array('N' => '');
$data[$i]['csv'] += array('bio' => '');
if($showCodice=='Y') 
    $data[$i]['csv'] += array('codice' => '');

$data[$i]['csv'] += array('name' => '');

if($isToValidate) {
    $data[$i]['csv'] += array('colli' => '');
    $data[$i]['csv'] += array('colli_delta' => '');
    $data[$i]['csv'] += array('colli_da' => '');
}
$data[$i]['csv'] += array('qta' => $tot_qta);
$data[$i]['csv'] += array('prezzoUnita' => '');
$data[$i]['csv'] += array('importo' => $this->ExportDocs->prepareCsv(number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'))));			

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