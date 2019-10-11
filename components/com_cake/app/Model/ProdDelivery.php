<?php
App::uses('AppModel', 'Model');


class ProdDelivery extends AppModel {

	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'supplier_organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'prod_group_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'luogo' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'data_inizio' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data valida',
				'allowEmpty' => false
			),
		),
		'data_fine' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data valida',
				'allowEmpty' => false
			),
		),
	);

	public $belongsTo = array(
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = SuppliersOrganization.organization_id',
			'fields' => '',
			'order' => ''
		),
		'ProdGroup' => array(
			'className' => 'ProdGroup',
			'foreignKey' => 'prod_group_id',
			'conditions' => 'ProdGroup.organization_id = ProdDelivery.organization_id',
			'fields' => '',
			'order' => ''
		),
		'ProdDeliveriesState' => array(
				'className' => 'ProdDeliveriesState',
				'foreignKey' => 'prod_delivery_state_id',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		)
	);	
	
	public function getProdDeliveryPermissionToEditUtente($prod_delivery) {
		if($prod_delivery['prod_delivery_state_id']==Configure::read('OPEN'))
			return true;
		else
			return false;
	}
	
	public function getProdDeliveryPermissionToEditProduttore($prod_delivery_state_id) {
	
		return true;
	}
	
	public function afterFind($results, $primary = true) {
	
		foreach ($results as $key => $val) {
	
			if(!empty($val)) {
				if (isset($val['ProdDelivery']['data_inizio'])) {
					$results[$key]['ProdDelivery']['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['ProdDelivery']['data_inizio']);
					$results[$key]['ProdDelivery']['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['ProdDelivery']['data_fine']);
						
					$results[$key]['ProdDelivery']['permissionToEditUtente']    = $this->getProdDeliveryPermissionToEditUtente($val['ProdDelivery']);
					$results[$key]['ProdDelivery']['permissionToEditProduttore'] = $this->getProdDeliveryPermissionToEditProduttore($val['ProdDelivery']);
						
					$results[$key]['ProdDelivery']['data_inizio_'] = date('d',strtotime($val['ProdDelivery']['data_inizio'])).'/'.date('n',strtotime($val['ProdDelivery']['data_inizio'])).'/'.date('Y',strtotime($val['ProdDelivery']['data_inizio']));
					$results[$key]['ProdDelivery']['data_fine_'] = date('d',strtotime($val['ProdDelivery']['data_fine'])).'/'.date('n',strtotime($val['ProdDelivery']['data_fine'])).'/'.date('Y',strtotime($val['ProdDelivery']['data_fine']));
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if (isset($val['data_inizio'])) {
					$results[$key]['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['data_inizio']);
					$results[$key]['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['data_fine']);
			
					$results[$key]['permissionToEditUtente']    = $this->getProdDeliveryPermissionToEditUtente($val);
					$results[$key]['permissionToEditProduttore'] = $this->getProdDeliveryPermissionToEditProduttore($val);
			
					$results[$key]['data_inizio_'] = date('d',strtotime($val['data_inizio'])).'/'.date('n',strtotime($val['data_inizio'])).'/'.date('Y',strtotime($val['data_inizio']));
					$results[$key]['data_fine_'] = date('d',strtotime($val['data_fine'])).'/'.date('n',strtotime($val['data_fine'])).'/'.date('Y',strtotime($val['data_fine']));
				}
			}
		}
	
		return $results;
	}

	public function beforeValidate($options = []) {
			
		if (!empty($this->data['ProdDelivery']['data_inizio']))
			$this->data['ProdDelivery']['data_inizio'] = $this->data['ProdDelivery']['data_inizio_db'];
			
		if (!empty($this->data['ProdDelivery']['data_fine']))
			$this->data['ProdDelivery']['data_fine'] = $this->data['ProdDelivery']['data_fine_db'];
	
		return true;
	}
	
	public function beforeSave($options = []) {
		if (!empty($this->data['ProdDelivery']['data_inizio']))
			$this->data['ProdDelivery']['data_inizio'] = $this->data['ProdDelivery']['data_inizio_db'];
		 
		if (!empty($this->data['ProdDelivery']['data_fine']))
			$this->data['ProdDelivery']['data_fine'] = $this->data['ProdDelivery']['data_fine_db'];
	
		return true;
	}
}