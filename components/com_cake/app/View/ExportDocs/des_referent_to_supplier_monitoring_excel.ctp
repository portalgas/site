<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

// define table cells
$table[] =	array('label' => __('N'), 'width' => 'auto');
$table[] =	array('label' => __('Bio'), 'width' => 'auto', 'filter' => true);
if($showCodice=='Y')
    $table[] =	array('label' => __('Codice'), 'width' => 'auto', 'filter' => true);
$table[] =	array('label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
if($isToValidate) {
        $table[] = array('label' => ('Colli completati'), 'width' => 'auto');
        $table[] = array('label' => ('Mancano per il collo'), 'width' => 'auto');
        $table[] = array('label' => ('Colla da'), 'width' => 'auto');
}
$table[] =	array('label' => __('qta'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('PrezzoUnita'), 'width' => 'auto', 'wrap' => true, 'filter' => false);
$table[] =	array('label' => __('Importo'), 'width' => 'auto', 'wrap' => true, 'filter' => false);

// heading
$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

$i=0;
$tot_qta = 0;
$tot_importo = 0;
if(isset($results))
foreach($results as $numResult => $result) {

    $name = $result['Article']['name'].' '.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
    
    $rows = array();

    $rows[] = ($i+1);

    $data[$numResult]['csv'] = array('N' => ($numArticle+1));

        if($result['Article']['bio']=='Y') $rows[] = 'Bio';
        else $rows[] = '';
        if($showCodice=='Y')
            $rows[] = $result['Article']['codice'];

        $rows[] = $this->ExportDocs->prepareCsv($name);

        if($isToValidate) {
            if($result['ArticlesOrder']['pezzi_confezione']>1) {
                /*
                 * colli_completi / differenza_da_ordinare
                 */
                $colli_completi = intval($result['Cart']['qta'] / $result['ArticlesOrder']['pezzi_confezione']);
                if($colli_completi>0)
                        $differenza_da_ordinare = (($result['ArticlesOrder']['pezzi_confezione'] * ($colli_completi +1)) - $result['Cart']['qta']);
                else {
                        $differenza_da_ordinare = ($result['ArticlesOrder']['pezzi_confezione'] - $result['Cart']['qta']);
                        $colli_completi = '-';
                }        	
        }
                
            
        if($result['ArticlesOrder']['pezzi_confezione']>1)  $rows[] = $colli_completi;
        else $rows[] = '';
            
        if($result['ArticlesOrder']['pezzi_confezione']>1) {
            		
                if($differenza_da_ordinare != $result['ArticlesOrder']['pezzi_confezione'])  
                    $rows[] = $differenza_da_ordinare;
                else
                    $rows[] = '0';
                
                $rows[] = $result['ArticlesOrder']['pezzi_confezione'];
            }
            else {
                $rows[] = '';
                $rows[] = '';
            }
        }
 
        $rows[] = $result['Cart']['qta'];				
        $rows[] = $this->ExportDocs->prepareCsv($this->App->getArticlePrezzo($result['ArticlesOrder']['prezzo']));
        $rows[] = number_format($result['Cart']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

	$tot_importo += $result['Cart']['importo'];
	$tot_qta += $prezzo = $result['Cart']['qta'];
	
	$i++;
        
    $this->PhpExcel->addTableRow($rows);	
					
}  // loop articoli

/*
 * totali
 */
$rows = array();
$rows[] = '';
$rows[] = '';
if($showCodice=='Y') 
    $rows[] = '';

$rows[] = '';
if($isToValidate) {
        $rows[] = '';
        $rows[] = '';
        $rows[] = '';
}
$rows[] = $tot_qta;
$rows[] = '';
$rows[] = $this->ExportDocs->prepareCsv(number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')));			

$this->PhpExcel->addTableRow($rows);


$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>