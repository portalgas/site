<?php
App::uses('AppController', 'Controller');
/**
 * ProdDeliveriesArticles Controller
 *
 * @property ProdDeliveriesArticle $ProdDeliveriesArticle
 * @property PaginatorComponent $Paginator
 */
class ProdDeliveriesArticlesController extends AppController {

	private $supplier_organization_id;	
	public $components = array('Paginator');
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		if(empty( $this->user->organization['Organization']['prodSupplierOrganizationId'])) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		else
			$this->supplier_organization_id = $this->user->organization['Organization']['prodSupplierOrganizationId'];
	}
	
	public function admin_index($tmp, $prod_delivery_id=1) {
		
		/*
		 * estraggo tutti gli articoli acquistati dall'utente
		 * 		ho bindModel di ProdCart
		*/
		$this->ProdDeliveriesArticle->unbindModel(array('belongsTo' => array('ProdDelivery')));
		$options['conditions'] = array('ProdDeliveriesArticle.organization_id' => $this->user->organization['Organization']['id'],
										'ProdDeliveriesArticle.stato != ' => 'N',
										'Article.stato' => 'Y'
		);
		
		$options['conditions'] += array('ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id);
		
		if(isset($conditions['Cart.user_id']))
			$options['conditions'] += array('Cart.user_id' => $conditions['Cart.user_id']);
		
		$options['recursive'] = 0;
		//$options['order'] = $order;
		$results = $this->ProdDeliveriesArticle->find('all', $options);

		/*
		 *  estraggo gli article_id per escluderli dopo
		 */
		$article_ids = "";
		if(!empty($results)) {
			foreach ($results as $result) 
				$article_ids .= $result['ProdDeliveriesArticle']['article_id'].',';
			$article_ids = substr($article_ids, 0, (strlen($article_ids)-1));
		}		
		
		/*
		 * estraggo tutti gli articoli dell'ordine
		 * 		faccio unbindModel di Cart
		 */		
		$this->ProdDeliveriesArticle->unbindModel(array('belongsTo' => array('ProdDelivery', 'ProdCart')));
		$options['conditions'] = array('ProdDeliveriesArticle.organization_id' => $this->user->organization['Organization']['id'],
										'ProdDeliveriesArticle.stato != ' => 'N',
										'Article.stato' => 'Y');	
		if(!empty($article_ids)) $options['conditions'] += array('Article.id not IN ('.$article_ids.')');
		
		$options['conditions'] += array('ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id);
				
		$options['recursive'] = 0;
		$options['order'] = $order;		
		$results2 = $this->ProdDeliveriesArticle->find('all', $options);
		
				
		$results = array_merge($results, $results2);
		
		if($order == 'Article.name asc')
		 	$results =	Set::sort($results, '{n}.Article.name', 'asc');
		$this->set('results',$results);
	
		//$this->layout = 'ajax';
	}


	public function admin_add($prod_delivery_id=0) {

		if ($this->request->is('post') || $this->request->is('put')) {
		
			$prod_delivery_id = $this->request->data['ProdDeliveriesArticle']['prod_delivery_id'];
			
			$msg = "";
			$article_id_selected = $this->request->data['ProdDeliveriesArticle']['article_id_selected'];
			$arr_article_id_selected = explode(',',$article_id_selected);
		
			/*
			 * cancello eventuali doppioni
			* */
			// $this->ProdDeliveriesArticle->delete($this->user->organization['Organization']['id'], $prod_delivery_id);

			foreach($this->request->data['Article'] as $key => $data) {
				$article_id = $key;

				if(isset($article_id) && in_array($article_id, $arr_article_id_selected)) {
					$row['ProdDeliveriesArticle']['organization_id'] = $this->user->organization['Organization']['id'];
					$row['ProdDeliveriesArticle']['article_organization_id'] = $this->user->organization['Organization']['id'];
					$row['ProdDeliveriesArticle']['article_id'] = $article_id;
					$row['ProdDeliveriesArticle']['prod_delivery_id'] = $prod_delivery_id;
					$row['ProdDeliveriesArticle']['prezzo'] = $data['ArticlesOrderPrezzo'];
					$row['ProdDeliveriesArticle']['qta_cart'] = 0;
					$row['ProdDeliveriesArticle']['pezzi_confezione'] = $data['ArticlesOrderPezziConfezione'];
					$row['ProdDeliveriesArticle']['qta_minima'] = $data['ArticlesOrderQtaMinima'];
					$row['ProdDeliveriesArticle']['qta_massima'] = $data['ArticlesOrderQtaMassima'];
					$row['ProdDeliveriesArticle']['qta_minima_order'] = $data['ArticlesOrderQtaMinimaOrder'];
					$row['ProdDeliveriesArticle']['qta_massima_order'] = $data['ArticlesOrderQtaMassimaOrder'];
					$row['ProdDeliveriesArticle']['qta_multipli'] = $data['ArticlesOrderQtaMultipli'];
					if($this->user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
						$row['ProdDeliveriesArticle']['alert_to_qta'] = 0;
					else
						$row['ProdDeliveriesArticle']['alert_to_qta'] = $data['ArticlesOrderAlertToQta'];
					$row['ProdDeliveriesArticle']['stato'] = 'Y';
		
					/*
					 * richiamo la validazione
					*/
					$this->ProdDeliveriesArticle->set($row);
					if(!$this->ProdDeliveriesArticle->validates()) {
						$errors = $this->ProdDeliveriesArticle->validationErrors;
						break;
					}
		
					$this->ProdDeliveriesArticle->create();
					if (!$this->ProdDeliveriesArticle->save($row)) {
						$msg .= "<br />articolo ".$article_id." in errore!";
					}
				}
			} // end foreach
				
			if(!empty($msg))
				$this->Session->setFlash(__('The articles order could not be saved. Please, try again.'));
			else {
				/*
				 * aggiorno lo stato della consegna
				* */
				$utilsCrons = new UtilsCrons(new View(null));
				if(Configure::read('developer.mode')) echo "<pre>";
				$utilsCrons->prodDeliveriesStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $prod_delivery_id);
				if(Configure::read('developer.mode')) echo "</pre>";
									
				$this->Session->setFlash(__('The articles order has been saved'));
				$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdDeliveries&action=index&delivery_id='.$prod_delivery_id);
			}
		} // end if ($this->request->is('post'))

		/*
		 * dettaglio ProdDelivery
		 */
		App::import('Model', 'ProdDelivery');
		$ProdDelivery = new ProdDelivery;

		$options = [];
		$options['conditions'] = array('ProdDelivery.organization_id' => $this->user->organization['Organization']['id'],
									   'ProdDelivery.id' => $prod_delivery_id);
		$options['recursive'] = 0;
		$prodDelivery = $ProdDelivery->find('first', $options);
		$this->set('prodDelivery', $prodDelivery);

		/*
		 * estraggo gli articoli associati al fornitore
		*/
		App::import('Model', 'Article');
		$Article = new Article;
		$results = $Article->getBySupplierOrganization($this->user, $this->supplier_organization_id);
		$this->set('results', $results);
		
		$this->set('prod_delivery_id', $prod_delivery_id);
	}

	public function admin_edit($id = null) {
		if (!$this->ProdDeliveriesArticle->exists($id)) {
			throw new NotFoundException(__('Invalid prod deliveries article'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProdDeliveriesArticle->save($this->request->data)) {
				$this->Session->setFlash(__('The prod deliveries article has been saved.'));
				return $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The prod deliveries article could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ProdDeliveriesArticle.' . $this->ProdDeliveriesArticle->primaryKey => $id));
			$this->request->data = $this->ProdDeliveriesArticle->find('first', $options);
		}
		$prodDeliveries = $this->ProdDeliveriesArticle->ProdDelivery->find('list');
		$articles = $this->ProdDeliveriesArticle->Article->find('list');
		$this->set(compact('prodDeliveries', 'articles'));
	}

	public function admin_delete($id = null) {
		$this->ProdDeliveriesArticle->id = $id;
		if (!$this->ProdDeliveriesArticle->exists()) {
			throw new NotFoundException(__('Invalid prod deliveries article'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->ProdDeliveriesArticle->delete($this->user->organization['Organization']['id'], $id)) {
			$this->Session->setFlash(__('The prod deliveries article has been deleted.'));
		} else {
			$this->Session->setFlash(__('The prod deliveries article could not be deleted. Please, try again.'));
		}
		return $this->myRedirect(['action' => 'index']);
	}}
