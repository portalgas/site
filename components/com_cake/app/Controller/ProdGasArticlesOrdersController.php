<?php
App::uses('AppController', 'Controller');
/**
 * ProdGasArticlesOrders Controller
 *
 * @property ProdGasArticlesOrder $ProdGasArticlesOrder
 */
class ProdGasArticlesOrdersController extends AppController {

	private $supplierResults = array();
	private $suppliersOrganizationResults = array();

	public function beforeFilter() {
		parent::beforeFilter();
		
		if($this->user->organization['Organization']['type']!='PRODGAS') {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	

		/*
		 *  ottengo il supplier_id del organization
		 */		
		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
		
		$options = array();
		$options['conditions'] = array('Supplier.organization_id' => (int)$this->user->organization['Organization']['id']);
		$options['recursive'] = -1;
		$this->supplierResults = $Supplier->find('first', $options);
		
		/*
		echo "<pre>";
		print_r($this->supplierResults);
		echo "</pre>";
		*/
		echo '<p>'.$this->supplierResults['Supplier']['name'].'</p>';
		
		/*
		 *  ottengo i GAS associati
		 */		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$SuppliersOrganization->unbindModel(array('belongsTo' => array('Supplier')));
		
		$options = array();
		$options['conditions'] = array('SuppliersOrganization.supplier_id' => (int)$this->supplierResults['Supplier']['id']);
		$options['recursive'] = 0;
		$this->suppliersOrganizationResults = $SuppliersOrganization->find('all', $options);
		
		/*
		echo "<pre>";
		print_r($options);	
		print_r($this->suppliersOrganizationResults);
		echo "</pre>";
		*/
		echo '<br />Organizzazioni associate ';
		foreach($this->suppliersOrganizationResults as $numResults => $suppliersOrganizationResult) {
			echo '<br />'.($numResults+1).') '.$suppliersOrganizationResult['Organization']['name'];
		}			

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
	 * elenco degli articoli associati all'ordine
	 */
	public function admin_index() {
		
		if ($this->request->is('post') || $this->request->is('put')) {

			$msg = "";

			/*
			 * articoli da aggiungere in ArticlesOrder
			* */
			$article_id_selected = $this->request->data['ProdGasArticlesOrder']['article_id_selected'];
			if(!empty($article_id_selected)) {
				$arr_article_id_selected = explode(',',$article_id_selected);
	
				foreach($this->request->data['Article'] as $key => $data) {
					$article_id = $key;
					
					if(isset($article_id) && in_array($article_id, $arr_article_id_selected)) {
						$row['ProdGasArticlesOrder']['organization_id'] = $this->user->organization['Organization']['id'];
						$row['ProdGasArticlesOrder']['article_organization_id'] = $this->user->organization['Organization']['id'];
						$row['ProdGasArticlesOrder']['article_id'] = $article_id;
						$row['ProdGasArticlesOrder']['order_id'] = $this->order_id;
						$row['ProdGasArticlesOrder']['qta_cart'] = "0";
						$row['ProdGasArticlesOrder']['prezzo'] = $data['ArticlesOrderPrezzo'];
						$row['ProdGasArticlesOrder']['pezzi_confezione'] = $data['ArticlesOrderPezziConfezione'];
						$row['ProdGasArticlesOrder']['qta_minima'] = $data['ArticlesOrderQtaMinima'];
						$row['ProdGasArticlesOrder']['qta_massima'] = $data['ArticlesOrderQtaMassima'];
						$row['ProdGasArticlesOrder']['qta_minima_order'] = $data['ArticlesOrderQtaMinimaOrder'];
						$row['ProdGasArticlesOrder']['qta_massima_order'] = $data['ArticlesOrderQtaMassimaOrder'];
						$row['ProdGasArticlesOrder']['qta_multipli'] = $data['ArticlesOrderQtaMultipli'];
						$row['ProdGasArticlesOrder']['flag_bookmarks'] = 'N';
						if($this->user->organization['Organization']['hasFieldArticleAlertToQta']=='N')							$row['ProdGasArticlesOrder']['alert_to_qta'] = 0;						else							$row['ProdGasArticlesOrder']['alert_to_qta'] = $data['ArticlesOrderAlertToQta'];
						$row['ProdGasArticlesOrder']['stato'] = 'Y';
						/*						 * richiamo la validazione						*/						$this->ProdGasArticlesOrder->set($row);						if(!$this->ProdGasArticlesOrder->validates()) {							$errors = $this->ProdGasArticlesOrder->validationsError;							break;						}
						
						$this->ProdGasArticlesOrder->create();
						if (!$this->ProdGasArticlesOrder->save($row)) {
							$msg .= "<br />Articolo (".$article_id.") non associato all'ordine!";
						}
					}
				} // end foreach
			} // if(!empty($article_id_selected))
			
			
			/*
			 * ArticlesOrder da cancellare
			 * 	=> cancello tutti gli eventuali acquisti (Carts)
			* */
			$article_order_key_selected = $this->request->data['ProdGasArticlesOrder']['article_order_key_selected'];
			if(!empty($article_order_key_selected)) {
				$arr_article_order_key_selected = explode(',',$article_order_key_selected);

				App::import('Model', 'Cart');				
				foreach($arr_article_order_key_selected as $article_order_key) {
						
					list($order_id, $article_id) = explode('_', $article_order_key);
					if (!$this->ProdGasArticlesOrder->exists($this->user->organization['Organization']['id'], $order_id, $this->user->organization['Organization']['id'], $article_id)) {
						$this->Session->setFlash(__('msg_error_params'));
						$this->myRedirect(Configure::read('routes_msg_exclamation'));
					}
					
					if (!$this->ProdGasArticlesOrder->delete($this->user->organization['Organization']['id'], $order_id, $this->user->organization['Organization']['id'], $article_id)) { 
						$msg .= "<br />Articolo associato all'ordine ($order_id $article_id) non cancellato!";
					}	
					
					/*
					 * cancello tutti gli eventuali acquisti (Carts), lo esegue gia' articles_orders_Trigger
					* */
					$Cart = new Cart;					$Cart->delete($this->user->organization['Organization']['id'], $order_id, $this->user->organization['Organization']['id'], $article_id);
					
				} // end foreach
			}  // end if(!empty($article_order_key_selected))
									
			if(!empty($msg)) 
				$this->Session->setFlash($msg);
			else
				$this->Session->setFlash(__('The articles order has been saved'));
			
			/*
			 * aggiorno lo stato dell'ordine
			 * 	da OPEN-NEXT o OPEN o CLOSE a eventualmente CREATE-INCOMPLETE
			 * */
			$utilsCrons = new UtilsCrons(new View(null));
			$utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $this->order_id);
			
		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		/* 
		 * articoli gia' associati all'ordine
		 */
		$this->ProdGasArticlesOrder->unbindModel(array('belongsTo' => array('Supplier')));
		$options = array();		$options['conditions'] = array('ProdGasArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],									   'Article.stato' => 'Y');
		$options['order'] = 'Article.name'; 
		$options['recursive'] = 0;
		$results = $this->ProdGasArticlesOrder->find('all',$options);	
		$this->set('results', $results);
		
		echo "<pre>";
		print_r($options);	
		print_r($results);
		echo "</pre>";		
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
				
					$this->myRedirect(array('action' => 'index'));
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
		
		$options = array();
		$options['conditions'] = array('ArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],									   'ArticlesOrder.order_id' => $order_id,
										'ArticlesOrder.article_organization_id' => $article_organization_id,
										'ArticlesOrder.article_id' => $article_id);		$options['recursive'] = -1;		$this->request->data = $this->ProdGasArticlesOrder->find('first', $options);
		
		/*
		 * Order
		* */
		App::import('Model', 'Order');
		$Order = new Order;
		
		$options = array();
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
		
		$options = array();
		$options['conditions'] = array('Article.id' => $article_id);
		$article = $Article->getArticlesDataAnagr($this->user, $options);
		$this->set('article', $article);	
		
		/*
		 * ctrl referentTesoriere
		*/
		$this->set('isReferenteTesoriere', $this->isReferentTesoriere());		
	}
}