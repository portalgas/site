<?php
/*
 * Controllers/EventTypesController.php
 * CakePHP Full Calendar Plugin
 *
 * Copyright (c) 2010 Silas Montgomery
 * http://silasmontgomery.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
 
class EventTypesController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
	
        /* ctrl ACL */
		if (!$this->isManager() && !$this->isManagerEvents()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
        /* ctrl ACL */			
	}
	
	var $components = array('Session');
	var $helpers = array('Html', 'Form', 'Session', 'Js'=>array('Jquery'));

	var $name = 'EventTypes';

	function admin_index() {
	    $this->paginate = array('conditions' => array('EventType.organization_id' => $this->user->organization['Organization']['id']),
					    		'recursive' => 1,
								'limit' => 25,
								'order' => array('EventType.name' => 'asc'));
	    $results = $this->paginate('EventType');
		$this->set('results', $results);
	}

	function admin_add() {
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['EventType']['organization_id'] = $this->user->organization['Organization']['id'];
			
			$this->EventType->create();
			if ($this->EventType->save($this->request->data)) {
				$this->Session->setFlash(__('The event type has been saved', true));
				$this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The event type could not be saved. Please, try again.', true));
			}
		}
	}

	function admin_edit($id = null) {

			
		$debug = false;

		if (empty($id)) {
			$id = $this->request->data['EventType']['id'];
		}
		
		if (empty($id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['EventType']['organization_id'] = $this->user->organization['Organization']['id'];
			
			self::d($this->request->data,false);
		
			if ($this->EventType->save($this->request->data)) {
				$this->Session->setFlash(__('The event type has been saved', true));
				$this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The event type could not be saved. Please, try again.', true));
			}
		} // if ($this->request->is('post') || $this->request->is('put')) 		
	
		$options = [];
		$options['conditions'] = array('EventType.id' => $id);
		$this->request->data = $this->EventType->find('first', $options);
	}

	function admin_delete($id = null) {
		if (!$id) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		if ($this->EventType->delete($id)) {
			$this->Session->setFlash(__('Delete Event Type', true));
			$this->myRedirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Event type was not deleted', true));
		$this->myRedirect(['action' => 'index']);
	}
}
?>
