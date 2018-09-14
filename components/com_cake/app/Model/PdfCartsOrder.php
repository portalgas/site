<?php
App::uses('AppModel', 'Model');


class PdfCartsOrder extends AppModel {

	public $validate = array(
		'supplier_organzations_name	' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'organization_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'user_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'delivery_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'supplier_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'supplier_organzations_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
	);
	
	public $belongsTo = array(
		/*
		'Organization' => array(
			'className' => 'Organization',
			'foreignKey' => 'organization_id',
			'conditions' => '',
			'fields' => '',
			'PdfCartsOrder' => ''
		),
		*/
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'PdfCartsOrder' => ''
		),
		'Supplier' => array(
			'className' => 'Supplier',
			'foreignKey' => 'supplier_id',
			'conditions' => '',
			'fields' => '',
			'PdfCartsOrder' => ''
		),
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organzations_id',
			'conditions' => '',
			'fields' => '',
			'PdfCartsOrder' => ''
		),
		'PdfCart' => array(
			'className' => 'PdfCart',
			'foreignKey' => 'pdf_cart_id',
			'conditions' => '',
			'fields' => '',
			'PdfCartsOrder' => ''
		)
	);
	
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if(isset($val['PdfCartsOrder']['order_importo'])) {
					$results[$key]['PdfCartsOrder']['order_importo_'] = number_format($val['PdfCartsOrder']['order_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['PdfCartsOrder']['order_importo_e'] = $results[$key]['PdfCartsOrder']['order_importo_'].' &euro;';
				}
			}
		}
		return $results;
	}
	
	public function beforeSave($options = []) {
		if (!empty($this->data['PdfCartsOrder']['order_importo']))
			$this->data['PdfCartsOrder']['order_importo'] = $this->importoToDatabase($this->data['PdfCartsOrder']['order_importo']);
		
	    return true;
	}	
	
}