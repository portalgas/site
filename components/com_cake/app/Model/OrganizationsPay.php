<?php
App::uses('AppModel', 'Model');

class OrganizationsPay extends AppModel {

	public function getImporto($organization_id, $year, $tot_users) {
	
		$results = [];
		
		if($year < Configure::read('OrganizationPayFasceYearStart')) {
			/*
			 * calcolo a persona
			 */
			$results['importo'] = (Configure::read('costToUser') * (float)$tot_users);
			
			if($results['importo'] > Configure::read('OrganizationPayImportMax')) {
				$results['importo'] = Configure::read('OrganizationPayImportMax');
				$results['importo_nota'] = ' <span>(max)</span>';
			}
			else
				$results['importo_nota'] = '';
			
			$results['importo_e'] = number_format($results['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		}
		else {	
			/*
			 * calcolo a fasce
			 */	
			 if($tot_users<=25) 
				$imp = 25;
			 else
			 if($tot_users>25 && $tot_users<=50) 
				$imp = 50;
			 else
			 if($tot_users>50 && $tot_users<=75) 
				$imp = 75;
			 else
			 if($tot_users>75) 
				$imp = 100;
			
			$results['importo'] = $imp;	
			$results['importo_e'] = number_format($imp,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			$results['importo_nota'] = '';
		}
		
		return $results;
	}
	
	/*
	 * calcola il totale di utenti attivi di un organization
	 */
	public function totUsers($organization_id) {
		
		App::import('Model', 'User');
        $User = new User;
		
		$options = [];
		$options['conditions'] = ['User.organization_id' => $organization_id,
								  'User.block' => 0];
		$options['recursive'] = -1;
        $totale = $User->find('count', $options);
		
		return $totale;
	}

	/*
	 * calcola il totale di produttori di un organization
	 */
	public function totSuppliersOrganizations($organization_id) {
		
		App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;
		
		$options = [];
		$options['conditions'] = ['SuppliersOrganization.organization_id' => $organization_id];
		$options['recursive'] = -1;
	
        $totale = $SuppliersOrganization->find('count', $options);
		
		return $totale;
	}
	
	/*
	 * calcola il totale di articles di un organization
	 */
	public function totArticlesOrganizations($organization_id) {
		
		App::import('Model', 'Article');
        $Article = new Article;
		
		$options = [];
		$options['conditions'] =  ['Article.organization_id' => $organization_id];
		$options['recursive'] = -1;
	
        $totale = $Article->find('count', $options);
		
		return $totale;
	}

	/*
	 * calcola il totale di ordine eseguiti di un organization
	 */
	public function totOrders($organization_id, $year='') {
		
		App::import('Model', 'Order');
        $Order = new Order;
		
		$Order->unbindModel(['belongsTo' => ['SuppliersOrganization']]);
		
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $organization_id,
								   'Delivery.organization_id' => $organization_id];
		if(!empty($year)) {
			/*
			 * Se e' l'anno corrente prendo anche le consegne "da definire" 
			 */
			if($year==date('Y'))
				$options['conditions'] += ["(substr(Delivery.data,1,4) = ".$year." OR Delivery.sys = 'Y')"];
			else
				$options['conditions'] += ['substr(Delivery.data,1,4)' => $year];
		}
		
		$options['recursive'] = 1;
	
        $totaleOrders = $Order->find('count', $options);

		/*
	 	 *  storico
		 */
		App::import('Model', 'StatOrder');
        $StatOrder = new StatOrder;
		
		$StatOrder->unbindModel(['belongsTo' => ['SuppliersOrganization']]);
		
		$options = [];
		$options['conditions'] = ['StatOrder.organization_id' => $organization_id,
								   'StatDelivery.organization_id' => $organization_id];
		if(!empty($year)) 
			$options['conditions'] += ['substr(StatDelivery.data,1,4)' => $year];
		
		$options['recursive'] = 1;
	
        $totaleStatOrders = $StatOrder->find('count', $options);

		/* 
		 * Backup
		 */
		App::import('Model', 'BackupOrdersOrder');
        $BackupOrdersOrder = new BackupOrdersOrder;
				
		$options = [];
		$options['conditions'] = ['BackupOrdersOrder.organization_id' => $organization_id,
								 'substr(BackupOrdersOrder.data_inizio,1,4)' => $year];
		$options['recursive'] = -1;
	
        $totaleBackupOrdersOrders = $BackupOrdersOrder->find('count', $options);

		return ($totaleOrders + $totaleStatOrders + $totaleBackupOrdersOrder);
	}
	
	public $belongsTo = [
		'Organization' => [
			'className' => 'Organization',
			'foreignKey' => 'organization_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
	    ]
	];
		
	public function afterFind($results, $primary = false) {

		foreach ($results as $key => $val) {
			if(!empty($val)) {

				if(isset($val['OrganizationsPay']['importo'])) {
					$results[$key]['OrganizationsPay']['importo_'] = number_format($val['OrganizationsPay']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['OrganizationsPay']['importo_e'] = $results[$key]['OrganizationsPay']['importo_'].' &euro;';
				}
				else
					/*
					 * se il find() arriva da $hasAndBelongsToMany
					*/
				 if(isset($val['importo'])) {
					$results[$key]['importo_'] = number_format($val['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['importo_e'] = $results[$key]['importo_'].' &euro;';
				}
				
			}
		}
		
		return $results;
	}		
}
