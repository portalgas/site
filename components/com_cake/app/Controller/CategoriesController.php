<?php
App::uses('AppController', 'Controller');

class CategoriesController extends AppController {

   public $name = 'Categories';

	public function beforeFilter() {
		 parent::beforeFilter();
		 
		 /* ctrl ACL */
		 if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		 }
		 /* ctrl ACL */
		 		
	}

    public function admin_index() {
        $results = $this->Category->generateTreeList(null, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set('results', $results);
    }

	public function admin_view($id = null) {
		$results = $this->Category->read(0, null, $id);
		if (empty($results)) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}
		$this->set('category', $results);
	}

	public function admin_add() {
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->Category->create();
			if ($this->Category->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
			}
		}
		$parents = $this->Category->generateTreeList(null, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('parents'));
	}

	public function admin_edit($id = null) {
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->Category->create();
			if ($this->Category->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Category->read(0, null, $id);
			if (empty($this->request->data)) {				$this->Session->setFlash(__('msg_error_params'));				$this->myRedirect(Configure::read('routes_msg_exclamation'));			}			
		}
		$parents = $this->Category->generateTreeList(null, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('parents'));
	}

	/*
	 * categories_Trigger
	 * 		update suppliers a zero
	 * */
	public function admin_delete($id = null) {
	
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Category->delete())
				$this->Session->setFlash(__('Delete Category'));
			else
				$this->Session->setFlash(__('Category was not deleted'));
			$this->myRedirect(['action' => 'index']);
		}
	
		$options = [];
		$options['conditions'] = array('Category.id' => $id);		$options['recursive'] = 1;		$results = $this->Category->find('first', $options);		$this->set(compact('results'));
	}		
}