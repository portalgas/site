<?php
App::uses('AppController', 'Controller');

class LoopsOrdersController extends AppController {
	
    public function beforeFilter() {
    	$this->ctrlHttpReferer();
    	 
    	parent::beforeFilter();
    }
    
	public function admin_index() {
	}
}