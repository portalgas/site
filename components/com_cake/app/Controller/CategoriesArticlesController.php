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
		 
		 /*		  * ctrl configurazione Organization		 */		 if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='N') {		 	$this->Session->setFlash(__('msg_not_organization_config'));		 	$this->myRedirect(Configure::read('routes_msg_stop'));		 }		 
		 		
	}

    public function admin_index() {
    	$conditions = ['organization_id' => $this->user->organization['Organization']['id']];
        $results = $this->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
        
        $resultsTotArticle = [];        foreach ($results as $key => $value) {        	         	/*        	 * ottengo il totale degli articoli associati        	*/        	$sql = "SELECT        				count(Article.id) as totArticle         			FROM        				".Configure::read('DB.prefix')."categories_articles CategoriesArticle,        				".Configure::read('DB.prefix')."articles Article         			WHERE
        				Article.organization_id = ".$this->user->organization['Organization']['id']." 
        				AND CategoriesArticle.organization_id = ".$this->user->organization['Organization']['id']."		        				AND Article.category_article_id = CategoriesArticle.id        				AND CategoriesArticle.id = ".$key;        	self::d($sql, false);        	$totResults = $this->CategoriesArticle->query($sql);        	if(!empty($totResults)) {        		$totResults = current($totResults);        		$resultsTotArticle[$key]['totArticle'] = $totResults[0]['totArticle'];        	}        	else {        		$resultsTotArticle[$key]['totArticle'] = 0;        	}        }        
        /*         * ottengo il totale degli articoli         */        $sql = "SELECT        				count(Article.id) as totArticle        			FROM        				".Configure::read('DB.prefix')."articles Article        			WHERE        				Article.organization_id = ".$this->user->organization['Organization']['id'];        self::d($sql, false);        $totResults = $this->CategoriesArticle->query($sql);        if(!empty($totResults)) {        	$totResults = current($totResults);        	$totArticles = $totResults[0]['totArticle'];        }
        else 
        	$totArticles = 0;
        	        $this->set('totArticles', $totArticles);
        $this->set('resultsTotArticle', $resultsTotArticle);
        $this->set('results', $results);
    }

	public function admin_view($id = null) {
		$this->CategoriesArticle->id = $id;
		if (!$this->CategoriesArticle->exists($this->CategoriesArticle->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$this->set('category', $this->CategoriesArticle->read($id, $this->user->organization['Organization']['id']));
	}

	public function admin_add() {
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['CategoriesArticle']['organization_id'] = $this->user->organization['Organization']['id'];
			
			$this->CategoriesArticle->create();
			if ($this->CategoriesArticle->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(['action' => 'index']);
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
		if (!$this->CategoriesArticle->exists($this->CategoriesArticle->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['CategoriesArticle']['organization_id'] = $this->user->organization['Organization']['id'];				
			$this->CategoriesArticle->create();
			if ($this->CategoriesArticle->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->CategoriesArticle->read($id, $this->user->organization['Organization']['id']);
		}
		$conditions = ['organization_id' => $this->user->organization['Organization']['id']];
		$parents = $this->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('parents'));
	}

	/*
	 * categories_Trigger
	 * 		update suppliers a zero
	 * */
	public function admin_delete($id=0) {
	
		if ($this->request->is('post') || $this->request->is('put'))			$id = $this->request->data['CategoryArticle']['id'];				$this->CategoriesArticle->id = $id;
		if (!$this->CategoriesArticle->exists($this->CategoriesArticle->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->CategoriesArticle->delete())
				$this->Session->setFlash(__('Delete Category'));
			else
				$this->Session->setFlash(__('Category was not deleted'));
			$this->myRedirect(['action' => 'index']);
		}
	
		$options['conditions'] = ['CategoriesArticle.organization_id' => $this->user->organization['Organization']['id'],
								  'CategoriesArticle.id' => $id];		$options['recursive'] = 1;		$results = $this->CategoriesArticle->find('first', $options);		$this->set(compact('results'));
	}		
}