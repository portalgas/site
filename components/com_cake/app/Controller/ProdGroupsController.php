<?php
App::uses('AppController', 'Controller');
/**
 * ProdGroups Controller
 *
 * @property ProdGroup $ProdGroup
 * @property PaginatorComponent $Paginator
 */
class ProdGroupsController extends AppController {

	public $components = array('Paginator');
	
	public function beforeFilter() {
		parent::beforeFilter();
	}
	
	public function admin_index() {
		$conditions = array('ProdGroup.organization_id' => $this->user->organization['Organization']['id']);
		$this->ProdGroup->recursive = 0;
		$this->Paginator->settings = array('conditions' => $conditions);
		$this->set('prodGroups', $this->Paginator->paginate());
	}

	public function admin_add() {
		if ($this->request->is('post')) {
			
			$this->request->data['ProdGroup']['organization_id'] = $this->user->organization['Organization']['id'];
			
			$this->ProdGroup->create();
			if ($this->ProdGroup->save($this->request->data)) {
				$this->Session->setFlash(__('The prod group has been saved.'));
				return $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The prod group could not be saved. Please, try again.'));
			}
		}
	}

	public function admin_edit($id = null) {
		
		$this->ProdGroup->id = $id;
		if (!$this->ProdGroup->exists($this->user->organization['Organization']['id'])) {
			throw new NotFoundException(__('Invalid prod group'));
		}
		if ($this->request->is(array('post', 'put'))) {
			
			$this->request->data['ProdGroup']['organization_id'] = $this->user->organization['Organization']['id'];
			
			if ($this->ProdGroup->save($this->request->data)) {
				$this->Session->setFlash(__('The prod group has been saved.'));
				echo "eee";
				return $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The prod group could not be saved. Please, try again.'));
			}
		} else {
			$options['conditions'] = array('ProdGroup.organization_id' => $this->user->organization['Organization']['id'],
										   'ProdGroup.id' => $id);
			$this->request->data = $this->ProdGroup->find('first', $options);
		}
	}

	public function admin_delete($id = null) {
		$this->ProdGroup->id = $id;
		if (!$this->ProdGroup->exists($this->user->organization['Organization']['id'])) {
			throw new NotFoundException(__('Invalid prod group'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->ProdGroup->delete()) {
			$this->Session->setFlash(__('The prod group has been deleted.'));
		} else {
			$this->Session->setFlash(__('The prod group could not be deleted. Please, try again.'));
		}
		return $this->myRedirect(['action' => 'index']);
	}}
