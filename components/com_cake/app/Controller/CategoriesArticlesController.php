<?php
App::uses('AppController', 'Controller');

class CategoriesArticlesController extends AppController {

   public $name = 'CategoriesArticles';

	public function beforeFilter() {
		 parent::beforeFilter();
		 
		 /* ctrl ACL */
		 if(!$this->isManager()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		 }
		 /* ctrl ACL */
		 
		 /*
		 		
	}

    public function admin_index() {
    	$conditions = array('organization_id' => $this->user->organization['Organization']['id']);
        $results = $this->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
        
        $resultsTotArticle = array();
        				Article.organization_id = ".$this->user->organization['Organization']['id']." 
        				AND CategoriesArticle.organization_id = ".$this->user->organization['Organization']['id']."		
        /*
        else 
        	$totArticles = 0;
        	
        $this->set('resultsTotArticle', $resultsTotArticle);
        $this->set('results', $results);
    }

	public function admin_view($id = null) {
		$this->CategoriesArticle->id = $id;
		if (!$this->CategoriesArticle->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$this->set('category', $this->CategoriesArticle->read($this->user->organization['Organization']['id'], null, $id));
	}

	public function admin_add() {
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['CategoriesArticle']['organization_id'] = $this->user->organization['Organization']['id'];
			
			$this->CategoriesArticle->create();
			if ($this->CategoriesArticle->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
			}
		}
		
		$conditions = 'organization_id = '.$this->user->organization['Organization']['id'];
		$parents = $this->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('parents'));
	}

	public function admin_edit($id = null) {
		$this->CategoriesArticle->id = $id;
		if (!$this->CategoriesArticle->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['CategoriesArticle']['organization_id'] = $this->user->organization['Organization']['id'];
			$this->CategoriesArticle->create();
			if ($this->CategoriesArticle->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->CategoriesArticle->read($this->user->organization['Organization']['id'], null, $id);
		}
		$conditions = array('organization_id' => $this->user->organization['Organization']['id']);
		$parents = $this->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('parents'));
	}

	/*
	 * categories_Trigger
	 * 		update suppliers a zero
	 * */
	public function admin_delete($id=0) {
	
		if ($this->request->is('post') || $this->request->is('put'))
		if (!$this->CategoriesArticle->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->CategoriesArticle->delete())
				$this->Session->setFlash(__('Delete Category'));
			else
				$this->Session->setFlash(__('Category was not deleted'));
			$this->myRedirect(array('action' => 'index'));
		}
	
		$options['conditions'] = array('Categories.organization_id' => $this->user->organization['Organization']['id'],
									   'CategoriesArticle.id' => $id);
	}		
}