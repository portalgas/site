<?php
App::uses('AppController', 'Controller');

class ProdDocsController extends AppController {

	public $helpers = array('ProdTabs');
	
	public function beforeFilter() {
		parent::beforeFilter();
	}
	
	public function admin_produttoreDocsExport() {

		if(empty($this->prod_delivery_id)) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}
			
		$options = array();
		$options['conditions'] = array ('ProdDelivery.stato_elaborazione' => 'OPEN');
		$this->__boxProdDelivery($this->user, $this->prod_delivery_id, $options);
		
		$this->set('prod_delivery_id', $this->prod_delivery_id);
	}

	/*
	 * se arrivo da ProdDelivery/admin_index_history.ctp $prod_delivery_id e' valorizzato
	 * 'Doc.stato_elaborazione' => 'CLOSE'
	* */
	public function admin_produttoreDocsExportHistory() {
	
		if(empty($this->prod_delivery_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		$options = array();
		$options['conditions'] = array ('ProdDelivery.stato_elaborazione' => 'CLOSE');
		$this->__boxProdDelivery($this->user, $this->prod_delivery_id, $options);
		
		$this->set('prod_delivery_id', $this->prod_delivery_id);
	}
}