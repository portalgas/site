<?php
App::uses('AppController', 'Controller');

class ProdCartsController extends AppController {

	public $components = array('Paginator');
	
	public function beforeFilter() {
		parent::beforeFilter();
	}
	
	public function admin_index() {
		$this->ProdCart->recursive = 0;
		$this->set('prodCarts', $this->Paginator->paginate());
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->ProdCart->create();
			if ($this->ProdCart->save($this->request->data)) {
				$this->Session->setFlash(__('The prod cart has been saved.'));
				return $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The prod cart could not be saved. Please, try again.'));
			}
		}
		$organizations = $this->ProdCart->Organization->find('list');
		$users = $this->ProdCart->User->find('list');
		$articleOrganizations = $this->ProdCart->ArticleOrganization->find('list');
		$articles = $this->ProdCart->Article->find('list');
		$this->set(compact('organizations', 'users', 'articleOrganizations', 'articles'));
	}

	public function edit($id = null) {
		if (!$this->ProdCart->exists($id)) {
			throw new NotFoundException(__('Invalid prod cart'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProdCart->save($this->request->data)) {
				$this->Session->setFlash(__('The prod cart has been saved.'));
				return $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The prod cart could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ProdCart.' . $this->ProdCart->primaryKey => $id));
			$this->request->data = $this->ProdCart->find('first', $options);
		}
		$organizations = $this->ProdCart->Organization->find('list');
		$users = $this->ProdCart->User->find('list');
		$articleOrganizations = $this->ProdCart->ArticleOrganization->find('list');
		$articles = $this->ProdCart->Article->find('list');
		$this->set(compact('organizations', 'users', 'articleOrganizations', 'articles'));
	}

	public function delete($id = null) {
		$this->ProdCart->id = $id;
		if (!$this->ProdCart->exists()) {
			throw new NotFoundException(__('Invalid prod cart'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->ProdCart->delete()) {
			$this->Session->setFlash(__('The prod cart has been deleted.'));
		} else {
			$this->Session->setFlash(__('The prod cart could not be deleted. Please, try again.'));
		}
		return $this->myRedirect(['action' => 'index']);
	}

	/*
	 * il campo ProdCart.date si aggiorna in automatico ON UPDATE CURRENT_TIMESTAMP
	*/
	public function cart_to_user_preview() {
	
		$this->ctrlHttpReferer();
	
		$user_id = $this->user->get('id');
		if(empty($user_id)) {
			$this->Session->setFlash(__('msg_not_permission_guest'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
	
		App::import('Model', 'ProdDeliveriesArticle');
		$ProdDeliveriesArticle = new ProdDeliveriesArticle;
	
		$conditions = array('ProdCart.user_id' => (int)$user_id);
	
		// in AjaxProdCart setto con = valore created e modified
		$orderBy = array('ProdCartPreview' => 'ProdCart.date DESC');
		 
		$results = $ProdDeliveriesArticle->getArticoliDellUtenteInConsegna($this->user, $conditions, $orderBy, Configure::read('ProdCartLimitPreview'));
		 
		$this->set('results', $results);
	
		$this->layout = 'ajax';
	}
	 
	public function admin_managementCartsOne() {
		 
		if(empty($this->prod_delivery_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		App::import('Model', 'ProdDelivery');
		$ProdDelivery = new ProdDelivery;
				
		$options = [];
		$options['conditions'] = array ('ProdDelivery.stato_elaborazione' => 'OPEN',);
		$this->_boxProdDelivery($this->user, $this->prod_delivery_id, $options);
		
		$htmlLegenda = $this->utilsCommons->getLegendaProdDeliveriesState();
		$this->set('htmlLegenda',$htmlLegenda);
		
		$this->set('prod_delivery_id', $this->prod_delivery_id);
	}
	
	public function admin_managementCartsSplit() {
		 
		if(empty($this->prod_delivery_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		App::import('Model', 'ProdDelivery');
		$ProdDelivery = new ProdDelivery;
		
		$options = [];
		$options['conditions'] = array ('ProdDelivery.stato_elaborazione' => 'OPEN',);
		$this->_boxProdDelivery($this->user, $this->prod_delivery_id, $options);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			 
			App::import('Model', 'ProdCartsSplit');
			$ProdCartsSplit = new ProdCartsSplit;
	
			$options['fields'] = array('SUM(ProdCartsSplit.importo_forzato) AS totImportoForzato', 'ProdCartsSplit.organization_id', 'ProdCartsSplit.prod_delivery_id', 'ProdCartsSplit.article_id', 'ProdCartsSplit.article_organization_id', 'ProdCartsSplit.user_id');
			$options['conditions'] = array('ProdCartsSplit.organization_id' => $this->user->organization['Organization']['id'],
										   'ProdCartsSplit.prod_delivery_id' => $this->prod_delivery_id);
			$options['group'] = array('ProdCartsSplit.organization_id', 'ProdCartsSplit.prod_delivery_id', 'ProdCartsSplit.article_id', 'ProdCartsSplit.article_organization_id', 'ProdCartsSplit.user_id');
			$options['recursive'] = -1;
			$results = $ProdCartsSplit->find('all', $options);
			foreach ($results as $result) {
	
				$importo_forzato = $result[0]['totImportoForzato'];
	
				$sql = "UPDATE
					".Configure::read('DB.prefix')."prod_carts
				SET
					importo_forzato = ".$importo_forzato."
				WHERE
					organization_id = ".$this->user->organization['Organization']['id']."
					AND prod_delivery_id = ".$result['ProdCartsSplit']['prod_delivery_id']."
					AND article_organization_id = ".$result['ProdCartsSplit']['article_organization_id']."
					AND article_id = ".$result['ProdCartsSplit']['article_id']."
					AND user_id = ".$result['ProdCartsSplit']['user_id'];
				//echo '<br/>'.$sql;
				try {
					$ProdCartsSplit->query($sql);
					$esito = true;
				}
				catch (Exception $e) {
					CakeLog::write('error',$sql);
					CakeLog::write('error',$e);
					$esito = false;
				}
			}
			 
			$this->Session->setFlash(__('The carts split has been saved'));
			$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdDelivery&action=home&prod_delivery_id='.$this->prod_delivery_id);
	
		}  // end if ($this->request->is('post') || $this->request->is('put'))W
		
		$htmlLegenda = $this->utilsCommons->getLegendaProdDeliveriesState();
		$this->set('htmlLegenda',$htmlLegenda);
		
		$this->set('prod_delivery_id', $this->prod_delivery_id);
	}	
}
