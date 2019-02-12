<?php
App::uses('AppController', 'Controller');

class ProdDeliveriesController extends AppController {

	public $components = array('Paginator');
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		if(empty($this->user->organization['Organization']['prodSupplierOrganizationId'])) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
	}

	public function ecomm() {

		$debug = false;
		
		/* prod-forzato
		if($this->user->id == 0 || $this->user->organization_id != $this->user->organization['Organization']['id']) {
			$this->Session->setFlash(__('msg_not_permission_guest'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		*/
	
		/*
		 * estraggo la consegna (ProdDelivery) associato al gruppo dell'utente (ProdUserGroup)
		* 			ProdDelivery.prod_group_id = ProdUserGroup.prod_group_id
		*/
		App::import('Model', 'ProdUsersGroup');
		$ProdUsersGroup = new ProdUsersGroup;
	
		$options = [];
		$options['conditions'] = array('ProdUsersGroup.organization_id' => $this->user->organization['Organization']['id'],
										'ProdUsersGroup.user_id' => $this->user->id);
		$options['recursive'] = -1;
		$options['fields'] = array('ProdUsersGroup.prod_group_id');
		$results = $ProdUsersGroup->find('first', $options);
		$prod_group_id = $results['ProdUsersGroup']['prod_group_id'];
	
		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}
		
		/*
		 * l'utente non e' associato ad alcun gruppo associato ad una consegna 
		 */
		if(empty($prod_group_id)) 
			$this->myRedirect(Configure::read('routes_msg_frontend_prod_user_group_not'));			
		
		
		/*
		 * dati della consegna
		*/
		$options = [];
		$options['conditions'] = array('ProdDelivery.organization_id' => $this->user->organization['Organization']['id'],
				'ProdDelivery.prod_group_id' => $prod_group_id,
				'ProdDelivery.prod_delivery_state_id' => Configure::read('OPEN'),
		        'ProdDelivery.supplier_organization_id' => $this->user->organization['Organization']['prodSupplierOrganizationId']);
		$options['recursive'] = -1;
		$prodDelivery = $this->ProdDelivery->find('first', $options);

		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($prodDelivery);
			echo "</pre>";
		}
		
		if(empty($prodDelivery)) 
			$this->myRedirect(Configure::read('routes_msg_frontend_prod_delivery_not'));
		
		$this->set('prodDelivery', $prodDelivery);
		
		/*
		 * articoli associati alla consegna
		 */
		$results = [];
		$prod_delivery_id = $prodDelivery['ProdDelivery']['id'];
		
		App::import('Model', 'ProdDeliveriesArticle');
		$ProdDeliveriesArticle = new ProdDeliveriesArticle;
			
		$options = [];
		$options['conditions'] = array('ProdCart.user_id' => $this->user->id,
										'ProdCart.deleteToReferent' => 'N',
										'ProdCart.prod_delivery_id' => $prod_delivery_id,
										'ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id);
		if(!empty($filterArticleName)) $options['conditions'] += array('Article.name' => $filterArticleName);
		if(!empty($filterArticleArticleTypeIds)) $options['conditions'] += array('ArticleArticleTypeId.article_type_id' => $filterArticleArticleTypeIds);
			
		$options['order'] = 'Article.name';
		$results = $ProdDeliveriesArticle->getArticoliEventualiAcquistiInConsegna($this->user, $options);
		
		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($results);
			echo "</pre>";
		}
		
		$this->set('results',$results);

		$this->set('FilterArticleName', $filterArticleName);
		$this->set('FilterArticleArticleTypeIds', $filterArticleArticleTypeIds);
	
		if(Cache::read('articlesTypes')===false) {
			App::import('Model', 'ArticlesType');
			$ArticlesType = new ArticlesType;
			$ArticlesTypeResults = $ArticlesType->prepareArray($ArticlesType->getArticlesTypes());
			Cache::write('articlesTypes',$ArticlesTypeResults);
		}
		else
			$ArticlesTypeResults = Cache::read('articlesTypes');
		$this->set('ArticlesTypeResults',$ArticlesTypeResults);
		
		$this->layout = 'default_front_end';
	}
	
	public function admin_index() {
		
		/*
		 * aggiorno lo stato della consegna
		* */
		$utilsCrons = new UtilsCrons(new View(null));
		if(Configure::read('developer.mode')) echo "<pre>";
		$utilsCrons->prodDeliveriesStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false);
		if(Configure::read('developer.mode')) echo "</pre>";

		$conditions = array('ProdDelivery.organization_id' => $this->user->organization['Organization']['id'],
							'ProdDelivery.supplier_organization_id' => $this->user->organization['Organization']['prodSupplierOrganizationId']);
		$this->paginate = array('conditions' => $conditions,
								'order' => 'ProdDelivery.name',
								'recursive' => 1);
		$results = $this->paginate('ProdDelivery');
		$this->set('results', $results);
		
		$htmlLegenda = $this->utilsCommons->getLegendaProdDeliveriesState();	
		$this->set('htmlLegenda',$htmlLegenda);
	}
	
	public function admin_add() {
		
		/*
		 * setting fields
		*/
		if ($this->request->is('post') || $this->request->is('put')) {
			$data_db = $this->request->data['ProdDelivery']['data_db'];
			$isVisibleFrontEnd = $this->request->data['ProdDelivery']['isVisibleFrontEnd'];
			$isVisibleBackOffice = $this->request->data['ProdDelivery']['isVisibleBackOffice'];
		}
		else {
			$data_inizio = '';
			$data_inizio_db = '';
			$data_fine = '';
			$data_fine_db = '';
			$isVisibleFrontEnd = 'Y';
			$isVisibleBackOffice = 'Y';
			$ricorrenza_num = 0;
		}
		$this->set('data_inizio',$data_inizio);
		$this->set('data_inizio_db', $data_inizio_db);
		$this->set('data_fine',$data_fine);
		$this->set('data_fine_db', $data_fine_db);
		$this->set('isVisibleFrontEndDefault', $isVisibleFrontEnd);
		$this->set('isVisibleBackOfficeDefault', $isVisibleBackOffice);
		$this->set('ricorrenza_num', $ricorrenza_num);
		
		if ($this->request->is('post')) {
			
			$this->request->data['ProdDelivery']['organization_id'] = $this->user->organization['Organization']['id'];
			$this->request->data['ProdDelivery']['data_inizio'] = $this->request->data['ProdDelivery']['data_inizio_db'];
			$this->request->data['ProdDelivery']['data_fine'] = $this->request->data['ProdDelivery']['data_fine_db'];
			
			$this->request->data['ProdDelivery']['supplier_organization_id'] = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			
			/*
			 * rimane allo stato CREATE-INCOMPLETE finche' non crea qualche associazione con gli articoli
			*/
			$this->request->data['ProdDelivery']['prod_delivery_state_id'] = Configure::read('CREATE-INCOMPLETE');
			
			if($this->user->organization['Organization']['hasVisibility']=='N') {
				$this->request->data['ProdDelivery']['isVisibleFrontEnd']='Y';
				$this->request->data['ProdDelivery']['isVisibleBackOffice']='Y';
			}
			
			if($this->request->data['ProdDelivery']['ricorrenza_num']==0)
				$this->request->data['ProdDelivery']['ricorrenza_type'] = '';
				
			$this->ProdDelivery->create();
			if ($this->ProdDelivery->save($this->request->data)) {
				$this->Session->setFlash(__('The prod delivery has been saved.'));
				
				$url = "";
				$prod_delivery_id = $this->ProdDelivery->getLastInsertId();
				
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdDeliveriesArticles&action=add&prod_delivery_id='.$prod_delivery_id;
				$this->myRedirect($url);
			} else {
				$this->Session->setFlash(__('The prod delivery could not be saved. Please, try again.'));
			}
		}
		
		$ricorrenza_type = ClassRegistry::init('ProdDelivery')->enumOptions('ricorrenza_type');
		$this->set(compact('ricorrenza_type'));
		
		$options = [];
		$options['conditions'] = array('ProdGroup.organization_id' => $this->user->organization['Organization']['id']);
		$options['fields'] = array('ProdGroup.id', 'ProdGroup.name');
		$options['order'] = 'ProdGroup.name ASC';
		$prodGroups = $this->ProdDelivery->ProdGroup->find('list', $options);
		$this->set(compact('prodGroups'));
	}

	public function admin_edit($id = null) {
		$this->ProdDelivery->id = $id;
		if (!$this->ProdDelivery->exists($this->user->organization['Organization']['id'])) {
			throw new NotFoundException(__('Invalid prod delivery'));
		}
		if ($this->request->is(array('post', 'put'))) {
			
			$this->request->data['ProdDelivery']['organization_id'] = $this->user->organization['Organization']['id'];
			$this->request->data['ProdDelivery']['data_inizio'] = $this->request->data['ProdDelivery']['data_inizio_db'];
			$this->request->data['ProdDelivery']['data_fine'] = $this->request->data['ProdDelivery']['data_fine_db'];
			
			$this->request->data['ProdDelivery']['supplier_organization_id'] = $this->user->organization['Organization']['prodSupplierOrganizationId'];
			
			if($this->user->organization['Organization']['hasVisibility']=='N') {
				$this->request->data['ProdDelivery']['isVisibleFrontEnd']='Y';
				$this->request->data['ProdDelivery']['isVisibleBackOffice']='Y';
			}
			
			if ($this->ProdDelivery->save($this->request->data)) {
				$this->Session->setFlash(__('The prod delivery has been saved.'));
				return $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The prod delivery could not be saved. Please, try again.'));
			}
		} else {
			$options['conditions'] = array('ProdDelivery.organization_id' => $this->user->organization['Organization']['id'],
										   'ProdDelivery.id' => $id);
			$this->request->data = $this->ProdDelivery->find('first', $options);
		}
		
		$ricorrenza_type = ClassRegistry::init('ProdDelivery')->enumOptions('ricorrenza_type');
		$this->set(compact('ricorrenza_type'));
		
		$prodGroups = $this->ProdDelivery->ProdGroup->find('list');
		$this->set(compact('prodGroups'));
	}

	public function admin_delete($id = null) {
		$this->ProdDelivery->id = $id;
		if (!$this->ProdDelivery->exists($this->user->organization['Organization']['id'])) {
			throw new NotFoundException(__('Invalid prod delivery'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->ProdDelivery->delete()) {
			$this->Session->setFlash(__('The prod delivery has been deleted.'));
		} else {
			$this->Session->setFlash(__('The prod delivery could not be deleted. Please, try again.'));
		}
		return $this->myRedirect(['action' => 'index']);
	}
	
	/*  creo sotto menu degli ordini profilato
	 * 	in ArticlesOrdersController::beforeFilter() ctrl lato server
	*
	*  position_img, le backgroung-img e' a Dx o Sn
	*/
	public function admin_sotto_menu($prod_delivery_id=0, $position_img) {
	
		$this->ctrlHttpReferer();
	
		$this->ProdDelivery->id = $prod_delivery_id;
		if (!$this->ProdDelivery->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$results = $this->ProdDelivery->read($this->user->organization['Organization']['id'], null, $prod_delivery_id);
		$this->set('results',$results);
	
		$this->set('position_img',$position_img);
	
		/*
		 * $pageCurrent = array('controller' => '', 'action' => '');
		* mi serve per non rendere cliccabile il link corrente nel menu laterale
		*/
		$pageCurrent = $this->getToUrlControllerAction($_SERVER['HTTP_REFERER']);
	
		/*
		 * possibili action della consegna
		*/
		$actionsProdDeliveries = $this->actionsProdDeliveries[$results['ProdDelivery']['prod_delivery_state_id']];
		$actionsProdDeliveries = $this->ActionsProdDelivery->draw_sotto_menu($actionsProdDeliveries, $results['ProdDelivery']['id'], $pageCurrent);
		$this->set('actionsProdDeliveries', $actionsProdDeliveries);
	
		/*
		 * stato elaborazione
		*/				
		App::import('Model', 'ProdDeliveriesState');
		$ProdDeliveriesState = new ProdDeliveriesState;
		$prodDeliveriesStates = $ProdDeliveriesState->getProdDeliveriesState($type);
		$this->set('prodDeliveriesStates',$prodDeliveriesStates);
	
		$this->layout = 'ajax';
		$this->render('admin_sotto_menu');
	}	
}
