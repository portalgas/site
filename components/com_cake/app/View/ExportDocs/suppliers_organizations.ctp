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

			
$html = '';

if (!empty($results)) {

		$html = '';
		$html .= '	<table class="table table-hover" cellpadding="0" cellspacing="0">';
		$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
		$html .= '		<tr>';
		
		$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('N.').'</th>';
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
			$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Categoria').'</th>';
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
			$html .= '			<th colspan="2" width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'">'.__('Name').'</th>';
		else
			$html .= '			<th colspan="2" width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'">'.__('Name').'</th>';
		$html .= '			<th width="'.$output->getCELLWIDTH80().'">'.__('Frequenza').'</th>';
		$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Indirizzo').'</th>';				
		$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH20()).'">'.__('Contatti').'</th>';			
		//$html .= '			<th width="'.$output->getCELLWIDTH100().'">'.__('Dati fiscali').'</th>';		
		$html .= '			<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.__('Totale<br />articoli').'</th>';	
		$html .= '		</tr>';				
		$html .= '	</thead><tbody>';
	
			
		$tot_totArticles=0;
		foreach ($results as $numResult => $result) {
			
			$html .= '<tr>';
			$html .= '			<td width="'.$output->getCELLWIDTH30().'">'.($numResult + 1).'</td>';
			if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y') 
				$html .= '			<td width="'.$output->getCELLWIDTH100().'">'.$result['CategoriesSupplier']['name'].'</td>';
			$html .= '<td width="'.$output->getCELLWIDTH50().'">';
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			$html .= '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" /> ';	
			$html .= '</td>';
			
			if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
				$html .= '<td width="'.$output->getCELLWIDTH100().'">';
			else
				$html .= '<td width="'.$output->getCELLWIDTH200().'">';
			$html .= $result['SuppliersOrganization']['name'];
			if(!empty($result['Supplier']['descrizione']))
				$html .= '<br /><small>'.$result['Supplier']['descrizione'].'</small>';
			$html .= '</td>';
						
			$html .= '<td width="'.$output->getCELLWIDTH80().'">'.$result['SuppliersOrganization']['frequenza'].'</td>';
			$html .= '<td width="'.$output->getCELLWIDTH100().'">';
			if(!empty($result['Supplier']['indirizzo']))
				$html .= $result['Supplier']['indirizzo'].'<br />';
			if(!empty($result['Supplier']['localita']))
				$html .= $result['Supplier']['localita'];
			if(!empty($result['Supplier']['cap']))
				$html .= ' '.$result['Supplier']['cap'];
			if(!empty($result['Supplier']['provincia']))
				$html .= ' ('.$result['Supplier']['provincia'].')';			
			$html .= '</td>';
			$html .= '<td width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH20()).'" >';
			if(!empty($result['Supplier']['telefono']))
				$html .= $result['Supplier']['telefono'].'<br />';
			if(!empty($result['Supplier']['telefono2']))
				$html .= $result['Supplier']['telefono2'].'<br />';
			if(!empty($result['Supplier']['fax']))
				$html .= $result['Supplier']['fax'].'<br />';
			if(!empty($result['Supplier']['mail']))
				$html .= $result['Supplier']['mail'].'<br />';
			if(!empty($result['Supplier']['www']))
				$html .= $result['Supplier']['www'];
			$html .= '</td>';
			// $html .= '<td width="'.$output->getCELLWIDTH100().'" >'.$result['Supplier']['cf'].' '.$result['Supplier']['piva'].' '.$result['Supplier']['conto'].'</td>';
			$html .= '<td width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.number_format($result['Articles']['totArticles'],0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</td>';
			$html .= '</tr>';
			
			/*
			 * referenti
			 */
			if(!empty($result['SuppliersOrganizationsReferent'])) {
				$html .= '<tr>';
				$html .= '<td width="'.$output->getCELLWIDTH30().'"></td>';
				$html .= '<td colspan="6"><b>'.__('Referenti').'</b>: ';
				
				$str = "";
				foreach($result['SuppliersOrganizationsReferent'] as $referent) 
					$str .= $referent['User']['name'].' - ';
				
				if(!empty($str))
					$str = substr($str, 0, strlen($str)-3);
				
				$html .= $str.'</td>';
				$html .= '</tr>';				
			} 
		
			$totArticles += $result['Articles']['totArticles'];
		}		
		
		$totArticles = number_format($totArticles,0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
		$html .= '		<tr>';
		$html .= '			<th width="'.$output->getCELLWIDTH30().'"></th>';
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
			$html .= '<th width="'.$output->getCELLWIDTH100().'"></th>';
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
			$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH50()).'"></th>';
		else
			$html .= '			<th width="'.($output->getCELLWIDTH200()+$output->getCELLWIDTH50()).'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH80().'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH100().'"></th>';
		$html .= '			<th width="'.($output->getCELLWIDTH100()+$output->getCELLWIDTH20()).'"></th>';
		$html .= '			<th width="'.$output->getCELLWIDTH50().'" style="text-align:center;">'.$totArticles.'</th>';		
		
		$html .= '		</tr>';				
		$html .= '</tbody></table>';
				
}	

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');


// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>