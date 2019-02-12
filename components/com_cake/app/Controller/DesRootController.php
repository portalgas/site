<?php

App::uses('AppController', 'Controller');

class DesRootController extends AppController {

    public $components = array('Paginator');

    public function beforeFilter() {
        parent::beforeFilter();

		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
    }

    public function admin_index() {
		
        App::import('Model', 'Organization');
        $Organization = new Organization;
		
        $options = [];
        $options['order'] = array('DesRoot.name');
        $options['recursive'] = 1;
        $results = $this->DesRoot->find('all', $options);

		foreach ($results as $numResult => $result) {
	
			foreach ($result['DesOrganization'] as $numResult2 => $org) {
				$options = [];
				$options['conditions'] = array('Organization.id' => $org['organization_id']);
				$options['recursive'] = -1;
				$organizationResults = $Organization->find('first', $options);

				$results[$numResult]['Organization'][$numResult2] = $organizationResults['Organization'];			
			}
		
		}
		
		self::d($results,false);
		
        $this->set(compact('results'));
    }

    public function admin_add() {
      if ($this->request->is('post')) {
		  $this->DesRoot->create();
		  if ($this->DesRoot->save($this->request->data)) {
			$this->Session->setFlash(__('The de has been saved.'));
			return $this->redirect(array('action' => 'index'));
		  } else {
			$this->Session->setFlash(__('The de could not be saved. Please, try again.'));
		  }
      }
    }

    public function admin_edit($id = null) {
      if (!$this->DesRoot->exists($id)) {
		throw new NotFoundException(__('Invalid de'));
      }
      if ($this->request->is(array('post', 'put'))) {
		if ($this->DesRoot->save($this->request->data)) {
			$this->Session->setFlash(__('The de has been saved.'));
			return $this->redirect(array('action' => 'index'));
		} else {
			$this->Session->setFlash(__('The de could not be saved. Please, try again.'));
		}
      } 
	  else {
		$options = array('conditions' => array('DesRoot.' . $this->DesRoot->primaryKey => $id));
		$this->request->data = $this->DesRoot->find('first', $options);
      }
    }

    public function admin_delete($id = null) {
		$this->DesRoot->id = $id;
		if (!$this->DesRoot->exists()) {
			throw new NotFoundException(__('Invalid de'));
		}
		$this->request->onlyAllow('post', 'delete');
			if ($this->DesRoot->delete()) {
				$this->Session->setFlash(__('The de has been deleted.'));
			} else {
				$this->Session->setFlash(__('The de could not be deleted. Please, try again.'));
		}
      return $this->redirect(array('action' => 'index'));
    }
}
