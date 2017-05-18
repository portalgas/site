<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

if (!empty($results)) {

		// define table cells
		$table[] =	array('label' => __('N.'), 'width' => 'auto');
		$table[] =	array('label' => __('Stato'), 'width' => 'auto', 'filter' => true);
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
			$table[] =	array('label' => __('Categoria'), 'width' => 'auto', 'filter' => true);
		$table[] =	array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Descrizione'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Frequenza'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Indirizzo'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Località'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Postal_code'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Provincia'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Telefono'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Telefono2'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Fax'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Email'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Www'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Cf'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Piva'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Conto'), 'width' => 'auto', 'filter' => true);;
		$table[] = array('label' => __('Totale articoli'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Referenti'), 'width' => 'auto', 'filter' => false);
		
		// heading
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

			
		$tot_totArticles=0;
		foreach ($results as $numResult => $result) {
			
			$rowsExcel = array();
			
			$rowsExcel[] = ($numResult + 1);
			if($result['Supplier']['stato']=='N' || $result['SuppliersOrganization']['stato']=='N')
				$rowsExcel[] = 'Non attivo';
			else
			if($result['SuppliersOrganization']['stato']=='T')
				$rowsExcel[] = 'Temporaneo';
			else
				$rowsExcel[] = 'Attivo';
			if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
				$rowsExcel[] = $result['CategoriesSupplier']['name'];
			$rowsExcel[] = $result['SuppliersOrganization']['name'];
			$rowsExcel[] = $result['Supplier']['descrizione'];
			$rowsExcel[] = $result['SuppliersOrganization']['frequenza'];
			$rowsExcel[] = $result['Supplier']['indirizzo'];
			$rowsExcel[] = $result['Supplier']['localita'];	
			$rowsExcel[] = $result['Supplier']['cap'];	
			$rowsExcel[] = $result['Supplier']['provincia'];	
			$rowsExcel[] = $result['Supplier']['telefono'];	
			$rowsExcel[] = $result['Supplier']['telefono2'];	
			$rowsExcel[] = $result['Supplier']['fax'];	
			$rowsExcel[] = $result['Supplier']['mail'];	
			$rowsExcel[] = $result['Supplier']['www'];
			$rowsExcel[] = $result['Supplier']['cf'];
			$rowsExcel[] = $result['Supplier']['piva'];
			$rowsExcel[] = $result['Supplier']['conto'];			
			$rowsExcel[] = $result['Articles']['totArticles'];	
			if(!empty($result['SuppliersOrganizationsReferent']))
			foreach($result['SuppliersOrganizationsReferent'] as $referent) {
				$rowsExcel[] = $referent['User']['name'];
			}	

			$this->PhpExcel->addTableRow($rowsExcel);
			
			$tot_totArticles += $result['Articles']['totArticles'];
		}		
	
		$rowsExcel = array();
		
		$rowsExcel[] = '';
		if($user->organization['Organization']['hasFieldSupplierCategoryId']=='Y')
			$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = '';
		$rowsExcel[] = $tot_totArticles;	
		
		$this->PhpExcel->addTableRow($rowsExcel);				
}	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>