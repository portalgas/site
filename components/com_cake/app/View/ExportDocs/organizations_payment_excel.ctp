<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);	


if (!empty($results)) {

		// define table cells
		$table[] =	array('label' => __('Id'), 'width' => 'auto');
		$table[] =	array('label' => __('Name'), 'width' => 'auto', 'filter' => true);
				
		$table[] = array('label' => __('payMail'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('payContatto'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('payIntestatario'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('payIndirizzo'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('payCap'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('payCitta'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('payProv'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('payCf'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('payPiva'), 'width' => 'auto', 'filter' => false);
			
		$table[] = array('label' => 'Produttori', 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => 'Articoli', 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => 'Ordini effettuati', 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => 'Utenti attivi', 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Importo_dovuto'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Importo_pagato'), 'width' => 'auto', 'filter' => false);
		$table[] = array('label' => __('Data pagamento'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Beneficiario'), 'width' => 'auto', 'filter' => true);
		$table[] = array('label' => __('Tipologia pagamento'), 'width' => 'auto', 'filter' => true);
		
		// heading
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));

				
		$tot_users = 0;
		$tot_suppliers_organizations = 0;
		$tot_articles = 0;
		$tot_orders = 0;
		$tot_importo_dovuto = 0;
		$tot_importo_pagato = 0;
		$year_old=0;
		foreach ($results as $numResult => $result) {
			
			if($result['OrganizationsPay']['importo_dovuto']==0) 
				$importo_dovuto = (Configure::read('costToUser') * (float)$result['OrganizationsPay']['tot_users']);
			else
				$importo_dovuto = 0;
			
			if($importo_dovuto > Configure::read('OrganizationPayImportMax'))
				$importo_dovuto = Configure::read('OrganizationPayImportMax');
			
			$rowsExcel = [];
			
			$rowsExcel[] = $result['Organization']['id'];
			$rowsExcel[] = $result['Organization']['name'];
			$rowsExcel[] = $result['Organization']['payMail'];
			$rowsExcel[] = $result['Organization']['payContatto'];
			$rowsExcel[] = $result['Organization']['payIntestatario'];	
			$rowsExcel[] = $result['Organization']['payIndirizzo'];
			$rowsExcel[] = $result['Organization']['payCap'];
			$rowsExcel[] = $result['Organization']['payCitta'];
			$rowsExcel[] = $result['Organization']['payProv'];
			$rowsExcel[] = $result['Organization']['payCf'];
			$rowsExcel[] = $result['Organization']['payPiva'];
			$rowsExcel[] = $result['OrganizationsPay']['tot_suppliers_organizations'];
			$rowsExcel[] = $result['OrganizationsPay']['tot_articles'];
			$rowsExcel[] = $result['OrganizationsPay']['tot_orders'];
			$rowsExcel[] = $result['OrganizationsPay']['tot_users'];
			$rowsExcel[] = number_format($importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			$rowsExcel[] = number_format($result['OrganizationsPay']['importo_pagato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			
			if($result['OrganizationsPay']['importo_pagato']!=Configure::read('DB.field.date.empty'))
				$rowsExcel[] = $result['OrganizationsPay']['data_pay'];
			else
				$rowsExcel[] = '';
			$rowsExcel[] = $result['OrganizationsPay']['beneficiario_pay'];
			$rowsExcel[] = $result['OrganizationsPay']['type_pay'];
			
			$this->PhpExcel->addTableRow($rowsExcel);
			
			$tot_importo_dovuto += $importo_dovuto;
			$tot_users += $result['OrganizationsPay']['tot_users'];
			$tot_suppliers_organizations += $result['OrganizationsPay']['tot_suppliers_organizations'];
			$tot_articles += $result['OrganizationsPay']['tot_articles'];
			$tot_orders += $result['OrganizationsPay']['tot_orders'];
			$tot_importo_dovuto += $result['OrganizationsPay']['importo_dovuto'];
			$tot_importo_pagato += $result['OrganizationsPay']['importo_pagato'];
			
			$i++;
		}		
	
	
		/*
		 * totale
		 */
		$tot_importo_dovuto = number_format($tot_importo_dovuto,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		$tot_importo_pagato = number_format($tot_importo_pagato,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));

			
		$rowsExcel = [];
		
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
		$rowsExcel[] = $tot_suppliers_organizations;
		$rowsExcel[] = $tot_articles;
		$rowsExcel[] = $tot_orders;
		$rowsExcel[] = $tot_users;
		$rowsExcel[] = $tot_importo_dovuto;
		$rowsExcel[] = $tot_importo_pagato;
		
		$this->PhpExcel->addTableRow($rowsExcel);				
}	

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>