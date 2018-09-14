<?php
App::uses('AppController', 'Controller');

class OrdersActionsController extends AppController {

	public $components = array('Paginator');

	public $paginate = array(
			'limit' => 500,
			'order' => array('id' => 'asc')
	);
	
	public function admin_index() {
		
		$this->Paginator->settings = $this->paginate;
		
		$this->OrdersAction->recursive = 0;
		$this->set('results', $this->Paginator->paginate());
	}
}