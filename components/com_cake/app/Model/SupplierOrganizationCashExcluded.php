<?php
App::uses('AppModel', 'Model');
/**
 * SupplierOrganizationCashExcluded Model
 *
 * @property Organization $Organization
 * @property SupplierOrganization $SupplierOrganization
 */
class SupplierOrganizationCashExcluded extends AppModel {

	public $displayField = 'id';
	public $tablePrefix = '';  

	public function isSupplierOrganizationCashExcluded($user, $supplier_organization_id, $debug=false) {

		$options = [];
		$options['conditions'] = ['SupplierOrganizationCashExcluded.organization_id' => $user->organization['Organization']['id'],
								'SupplierOrganizationCashExcluded.supplier_organization_id' => $supplier_organization_id];
		$options['recursive'] = -1;
		$results = $this->find('count', $options);
		// debug($options);
		// debug($results);
		if($results==0)
			return false;
		else
			return true;
	}
}