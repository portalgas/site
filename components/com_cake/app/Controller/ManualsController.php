<?php
class ManualsController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();

	}

	public function admin_index() {
		$this->set('isRoot',$this->isRoot());
		$this->set('isManager',$this->isManager());
		$this->set('isManagerDelivery',$this->isManagerDelivery());
		$this->set('isReferentGeneric',$this->isReferentGeneric());
		$this->set('isSuperReferente',$this->isSuperReferente());
		$this->set('isTesoriere',$this->isTesoriere());
		$this->set('isTesoriereGeneric',$this->isTesoriereGeneric());
		$this->set('isCassiere',$this->isCassiereGeneric());
		$this->set('isStoreroom',$this->isStoreroom());
		
		if(empty($this->user->supplier['Supplier'])) 
			$this->render('admin_index');
		else
			$this->render('admin_prod_gas_index');
	}	
}