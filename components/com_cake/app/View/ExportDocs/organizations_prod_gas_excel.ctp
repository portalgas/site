<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	

if (!empty($results)) {

		// define table cells
		$table[] =	array('label' => __('N.'), 'width' => 'auto');
		$table[] =	array('label' => __('Id'), 'width' => 'auto', 'filter' => false);
		$table[] =	array('label' => __('Name'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('GasOrganizations'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('www'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Address'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Users').' '.__('LastvisitDate'), 'width' => 'auto', 'filter' => false);
		
		// heading
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

		foreach ($results as $numResult => $result) {
			
			$rowsExcel = [];
		
			$rowsExcel[] = ($numResult + 1);
			$rowsExcel[] = $result['Organization']['id'];
			$rowsExcel[] = $result['Organization']['name'];
			// $rowsExcel[] = $result['Supplier']['Supplier']['name'];
			$rowsExcel[] = '';
			$rowsExcel[] = $result['Supplier']['Supplier']['www'];
			$rowsExcel[] = $result['Supplier']['Supplier']['localita'].' '.$result['Supplier']['Supplier']['provincia'];

			/*
			 * Users associati al gruppo Configure::read('prod_gas_supplier_manager');  // 62
			 * ciclo per escludere Assistente PortAlGas 
			 */
			$users = [];
			if(isset($result['User']) && !empty($result['User'])) {
				foreach($result['User'] as $numResult => $user) {

					if($user['User']['lastVisitDate']=='0000-00-00 00:00:00')
						$lastVisitDate = 'Mai';
					else
						$lastVisitDate = $this->Time->i18nFormat($user['User']['lastVisitDate'],"%e %B %Y");

					// escludo Assistente PortAlGas
					if(strpos($user['User']['email'], '.portalgas.it')===false) {
						$users[$user['User']['id']] = $user['User']['name'].': '.$lastVisitDate;
					}
				}

				foreach($users as $user) {
					$rowsExcel[] = $user;
				}	
			}
			$this->PhpExcel->addTableRow($rowsExcel);
					
			/*
			 * GAS associati
			 */

			if(isset($result['Supplier']['Organization'])) {
				foreach($result['Supplier']['Organization'] as $organization) {
					$rowsExcel = [];
					$rowsExcel[] = '';
					$rowsExcel[] = '';	
					$rowsExcel[] = '';					
					$rowsExcel[] = $organization['Organization']['name'];
					$rowsExcel[] = $this->App->traslateEnum('ProdGasSupplier'.$organization['SuppliersOrganization']['owner_articles']);

					$this->PhpExcel->addTableRow($rowsExcel);
				}
			}
			else {
				$rowsExcel = [];
				$rowsExcel[] = '';

				$this->PhpExcel->addTableRow($rowsExcel);				
			}

			/*
			 * salto linea
			 */
			$rowsExcel = [];
			$rowsExcel[] = '';

			$this->PhpExcel->addTableRow($rowsExcel);
						
		} // end foreach ($results as $numResult => $result)
}	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>