<?php
App::uses('AppModel', 'Model');

class OrganizationsPay extends AppModel {

	/*
	 * calcola il totale di utenti attivi di un organization
	 */
	public function totUsers($organization_id) {
		
		App::import('Model', 'User');
        $User = new User;
		
		$options = array();
		$options['conditions'] = array('User.organization_id' => $organization_id,
									   'User.block' => 0);
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
		
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $organization_id);
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
		
		$options = array();
		$options['conditions'] = array('Article.organization_id' => $organization_id);
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
		
		$Order->unbindModel(array('belongsTo' => array('SuppliersOrganization')));
		
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $organization_id,
									   'Delivery.organization_id' => $organization_id);
		if(!empty($year)) {
			/*
			 * Se e' l'anno corrente prendo anche le consegne "da definire" 
			 */
			if($year==date('Y'))
				$options['conditions'] += array("(substr(Delivery.data,1,4) = ".$year." OR Delivery.sys = 'Y')");
			else
				$options['conditions'] += array('substr(Delivery.data,1,4)' => $year);
		}
		
		$options['recursive'] = 1;
	
        $totaleOrders = $Order->find('count', $options);

		/*
	 	 *  storico
		 */
		App::import('Model', 'StatOrder');
        $StatOrder = new StatOrder;
		
		$StatOrder->unbindModel(array('belongsTo' => array('SuppliersOrganization')));
		
		$options = array();
		$options['conditions'] = array('StatOrder.organization_id' => $organization_id,
									   'StatDelivery.organization_id' => $organization_id);
		if(!empty($year)) 
			$options['conditions'] += array('substr(StatDelivery.data,1,4)' => $year);
		
		$options['recursive'] = 1;
	
        $totaleStatOrders = $StatOrder->find('count', $options);


		return ($totaleOrders + $totaleStatOrders);
	}
	
	public $belongsTo = array(
		'Organization' => array(
			'className' => 'Organization',
			'foreignKey' => 'organization_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)	
	);
		
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
