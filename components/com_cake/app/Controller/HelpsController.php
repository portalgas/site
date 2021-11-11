<?php
class HelpsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();

		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		/*
		 * nella view e' profilato anche per gli utenti ma non e' + accessibile
		 */		$this->set('isRoot',$this->isRoot());
	}

	public function admin_index() {
		

	}
}