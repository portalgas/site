<?php
App::uses('AppController', 'Controller');

class ProdGasArticlesOrdersController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		
		$debug = false;
		
		if($this->user->organization['Organization']['type']!='PRODGAS') {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	

		self::d($this->user->organization['Supplier'], $debug);
	}

	/*
	 * elenco degli articoli associati all'ordine
	 */
	public function admin_index($organization_id, $delivery_id, $order_id) {
		
		$debug = false;
		
		App::import('Model', 'ProdGasSuppliersImport');
		$ProdGasSuppliersImport = new ProdGasSuppliersImport;
				
		$gasResults = $ProdGasSuppliersImport->getGas($this->user, $organization_id, $debug);

		/* 
		 * dati ordine
		 */
		App::import('Model', 'Order');
		$Order = new Order;
		 
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $organization_id,
								  'Order.id' => $order_id];
		$options['recursive'] = 0;
		$orderResults = $Order->find('first',$options);
		self::d($orderResults, $debug);
		$this->set('orderResults', $orderResults);
		
		/* 
		 * articoli associati all'ordine
		 */
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		$ArticlesOrder->unbindModel(['belongsTo' => ['Order']]);
		 
		$options = [];
		$options['conditions'] = ['ArticlesOrder.organization_id' => $organization_id,
								  'ArticlesOrder.order_id' => $order_id,
								  'Article.stato' => 'Y'];
		$options['order'] = ['Article.name']; 
		$options['recursive'] = 0;
		$articlesOrderResults = $ArticlesOrder->find('all',$options);	
		
		self::d($options['conditions'], $debug);
		self::d($articlesOrderResults, $debug);	
		
		$this->set('results', $articlesOrderResults);
		
		$this->set('organization_id', $organization_id);
	}
	
	public function admin_add() {

		if ($this->request->is('post') || $this->request->is('put')) {

			$msg = "";
			$article_id_selected = $this->request->data['ProdGasArticlesOrder']['article_id_selected'];
			$arr_article_id_selected = explode(',',$article_id_selected);

			/*			 * cancello eventuali doppioni			* */			$this->ProdGasArticlesOrder->delete($this->user->organization['Organization']['id'], $this->order_id);				
			foreach($this->request->data['Article'] as $key => $data) {
				$article_id = $key;			

				if(isset($article_id) && in_array($article_id, $arr_article_id_selected)) {
					$row['ProdGasArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
					$row['ProdGasArticlesOrder']['article_organization_id'] = $this->user->organization['Organization']['id'];
					$row['ProdGasArticlesOrder']['article_id'] = $article_id;
					$row['ProdGasArticlesOrder']['order_id'] = $this->order_id;
					$row['ProdGasArticlesOrder']['prezzo'] = $data['ArticlesOrderPrezzo'];
					$row['ProdGasArticlesOrder']['qta_cart'] = 0;
					$row['ProdGasArticlesOrder']['pezzi_confezione'] = $data['ArticlesOrderPezziConfezione'];
					$row['ProdGasArticlesOrder']['qta_minima'] = $data['ArticlesOrderQtaMinima'];
					$row['ProdGasArticlesOrder']['qta_massima'] = $data['ArticlesOrderQtaMassima'];
					$row['ProdGasArticlesOrder']['qta_minima_order'] = $data['ArticlesOrderQtaMinimaOrder'];
					$row['ProdGasArticlesOrder']['qta_massima_order'] = $data['ArticlesOrderQtaMassimaOrder'];
					$row['ProdGasArticlesOrder']['qta_multipli'] = $data['ArticlesOrderQtaMultipli'];
					$row['ProdGasArticlesOrder']['flag_bookmarks'] = 'N';
					if($this->user->organization['Organization']['hasFieldArticleAlertToQta']=='N')						$row['ProdGasArticlesOrder']['alert_to_qta'] = 0;
					else
						$row['ProdGasArticlesOrder']['alert_to_qta'] = $data['ArticlesOrderAlertToQta'];
					$row['ProdGasArticlesOrder']['stato'] = 'Y';
					
					/*					 * richiamo la validazione					*/
					$this->ProdGasArticlesOrder->set($row);
					if(!$this->ProdGasArticlesOrder->validates()) {
			
								$errors = $this->ProdGasArticlesOrder->validationErrors;
								$tmp = '';
								$flatErrors = Set::flatten($errors);
								if(count($errors) > 0) { 
									$tmp = '';
									foreach($flatErrors as $key => $value) 
										$tmp .= $value.' - ';
								}
								$msg .= "Articolo non inserito: dati non validi, $tmp<br />";
								$this->Session->setFlash($msg);
					}
					else {										
						$this->ProdGasArticlesOrder->create();
						if (!$this->ProdGasArticlesOrder->save($row)) {
							$msg .= "<br />articolo ".$article_id." in errore!";
						}
					}
				} // end if(isset($article_id) && in_array($article_id, $arr_article_id_selected))
			} // end foreach 
			
			if(!empty($msg)) 
				$this->Session->setFlash(__('The articles order could not be saved. Please, try again.'));
			else {
				/*
				 * aggiorno lo stato dell'ordine 
				 * 	da OPEN-NEXT o OPEN
				 * */
				$utilsCrons = new UtilsCrons(new View(null));
				$utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $this->order_id);
					
				/*
				 * setto la tipologia Draw (SIMPLE o COMPLETE)
				*/
				App::import('Model', 'Order');
				$Order = new Order;
				$Order->updateTypeDraw($this->user, $this->order_id);
				
				
				$this->Session->setFlash(__('The articles order has been saved'));
				$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&delivery_id='.$this->delivery_id.'&order_id='.$this->order_id);
			}
		} // end if ($this->request->is('post')) 

		/*		 * estraggo gli articoli associati al fornitore		*/		App::import('Model', 'Article');		$Article = new Article;
		$results = $Article->getBySupplierOrganization($this->user, $this->order['Order']['supplier_organization_id']);		$this->set('results', $results);
	}
		
	/*
	 * modifico i dati di un articolo associato all'ordine
	 */
	public function admin_edit($order_id=0, $article_organization_id=0, $article_id=0) {

		$debug = false;
		
		if(empty($order_id) || empty($article_organization_id) || empty($article_id)) {			/*
			 * dopo il submit passano come campi hidden
			 */
			$order_id   = $this->request->data['ProdGasArticlesOrder']['order_id'];
			$article_organization_id = $this->request->data['ProdGasArticlesOrder']['article_organization_id'];
			$article_id = $this->request->data['ProdGasArticlesOrder']['article_id'];
		}
		
		if(empty($order_id) || empty($article_organization_id) || empty($article_id)) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}		
		if (!$this->ProdGasArticlesOrder->exists($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {

			$this->request->data['ProdGasArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
	
			/*			 * richiamo la validazione			*/			$this->ProdGasArticlesOrder->set($this->request->data);			if(!$this->ProdGasArticlesOrder->validates()) {
			
						$errors = $this->ProdGasArticlesOrder->validationErrors;
						$tmp = '';
						$flatErrors = Set::flatten($errors);
						if(count($errors) > 0) { 
							$tmp = '';
							foreach($flatErrors as $key => $value) 
								$tmp .= $value.' - ';
						}
						$msg .= "Articolo non aggiornato: dati non validi, $tmp<br />";
						$this->Session->setFlash($msg);
			}
			else {
				
				$this->ProdGasArticlesOrder->create();
				if ($this->ProdGasArticlesOrder->save($this->request->data)) {
					$this->Session->setFlash(__('The articles order edit single has been saved'));
				
					/*
				 	 * aggiorno ArticlesOrder.qta_cart e ArticlesOrder.qta_massima_order
					 */
					$this->ProdGasArticlesOrder->aggiornaQtaCart_StatoQtaMax($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id);
				
					$this->myRedirect(['action' => 'index']);
				} else 
					$this->Session->setFlash(__('The article could not be saved. Please, try again.'));
			} // end if(!$this->ProdGasArticlesOrder->validates()) 
		} // if ($this->request->is('post') || $this->request->is('put')) 
		else {
			/*
			 * aggiorno ArticlesOrder.qta_cart e ArticlesOrder.qta_massima_order
			*/
			$this->ProdGasArticlesOrder->aggiornaQtaCart_StatoQtaMax($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id);
		}
		
		$options = [];
		$options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],									   'ArticlesOrder.order_id' => $order_id,
										'ArticlesOrder.article_organization_id' => $article_organization_id,
										'ArticlesOrder.article_id' => $article_id);		$options['recursive'] = -1;		$this->request->data = $this->ProdGasArticlesOrder->find('first', $options);
		
		/*
		 * Order
		* */
		App::import('Model', 'Order');
		$Order = new Order;
		
		$options = [];
		$conditions = array('Order.organization_id' => (int)$this->user->organization['Organization']['id'],
							'Order.isVisibleBackOffice' => 'Y',
							'Order.id' => $order_id);
		$order = $Order->find('first', array('conditions' => $conditions));

		$stato = ClassRegistry::init('ProdGasArticlesOrder')->enumOptions('stato');
		unset($stato['QTAMAXORDER']);
		$this->set(compact('stato'));
		
		$this->set(compact('order'));

		/*
		 * dettaglio articolo
		 */
		App::import('Model', 'Article');
		$Article = new Article;
		if (!$Article->exists($this->user->organization['Organization']['id'], $article_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		$options = [];
		$options['conditions'] = ['Article.id' => $article_id];
		$article = $Article->getArticlesDataAnagr($this->user, $options);
		$this->set('article', $article);	
		
		/*
		 * ctrl referentTesoriere
		*/
		$this->set('isReferenteTesoriere', $this->isReferentTesoriere());		
	}
}