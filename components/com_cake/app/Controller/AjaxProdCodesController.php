<?php
App::uses('AppController', 'Controller');

class AjaxProdCodesController extends AppController {
			
	public function beforeFilter() {
    	$this->ctrlHttpReferer();
    	
    	parent::beforeFilter();
    }

	public function admin_box_report_options() {
	
		// di default l'opzione (Solo utenti con acquisti)
		$this->set('report_options','report-users-cart');
		
		$this->layout = 'ajax';
	}
	
	public function admin_box_articles_options($user_id) {
		
		// di default l'opzione (Solo articoli acquistati)
		$this->set('articles_options','options-articles-cart');
		$this->set('user_id',$user_id);
		
		$this->layout = 'ajax';
	}

	/*
	 * list users
	* $reportOptions = 'report-users-all', tutti
	* 					'report-users-cart', che hanno effettuato acquisti in un ordine
	*/
	public function admin_box_users($prod_delivery_id=0, $reportOptions) {
	
		if(empty($prod_delivery_id) || $reportOptions==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		/*
		 * dati consegna
		*/
		App::import('Model', 'ProdDelivery');
		$ProdDelivery = new ProdDelivery;
	
		$options = [];
		$options['conditions'] = array('ProdDelivery.organization_id' => (int)$this->user->organization['Organization']['id'],
				'ProdDelivery.id' => $prod_delivery_id);
		$options['fields'] = array('ProdDelivery.prod_group_id');
		$options['recursive'] = -1;
		$prodDeliveryResults = $ProdDelivery->find('first', $options);
		$prod_group_id = $prodDeliveryResults['ProdDelivery']['prod_group_id'];
	
		App::import('Model', 'User');
		$User = new User;
	
		if($reportOptions=='report-users-all') {
				
			/*
			 * estraggo gli utenti della consegna
			*/
			App::import('Model', 'ProdUsersGroup');
			$ProdUsersGroup = new ProdUsersGroup;
	
			$options = [];
			$options['conditions'] = array('ProdUsersGroup.organization_id' => (int)$this->user->organization['Organization']['id'],
					'ProdUsersGroup.prod_group_id' => $prod_group_id,
					'User.block' => 0);
			$options['order'] = Configure::read('orderUser');
			$options['fields'] = array('User.id', 'User.name');
			$options['recursive'] = 0;
	
			$users = $ProdUsersGroup->find('list', $options);
		}
		else
		if($reportOptions=='report-users-cart') {
			$conditions = [];
			$conditions = array('ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id);
			$results = $User->getUserWithCartByProdDelivery($this->user ,$conditions);
	
			$users = [];
			$users += array('ALL' => 'Tutti gli utenti che hanno effettuato acquisti');
			foreach($results as $key => $results2)
				$users[$results2['User']['id']] = $results2['User']['name'];
		}
	
		$this->set(compact('users'));
	
		$this->layout = 'ajax';
	}
	
	/*
	 * solo se $results['ProdDelivery']['prod_delivery_state_id']==Configure::read('PROCESSED-POST-DELIVERY') posso procedere
	*/
	public function admin_box_carts_splits_options() {
	
		App::import('Model', 'ProdDelivery');
		$ProdDelivery = new ProdDelivery;
	
		$ProdDelivery->id = $this->prod_delivery_id;
		if (!$ProdDelivery->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		$results = $ProdDelivery->read($this->user->organization['Organization']['id'], null, $this->prod_delivery_id);
		if($results['ProdDelivery']['prod_delivery_state_id']==Configure::read('PROCESSED-POST-DELIVERY')) {
			/*
			 * ctrl eventuali occorrenze di ProdCartsSplit
			*/
			App::import('Model', 'ProdCartsSplit');
			$ProdCartsSplit = new ProdCartsSplit;
			$resultsCartsSplit = $ProdCartsSplit->select_to_delivery($this->user, $this->prod_delivery_id);
			$this->set('resultsCartsSplit',$resultsCartsSplit);
		}
	
		$this->set(compact('results',$results));
	
		$this->layout = 'ajax';
	}
	
	/*
	 * richiamata da
	* ProdCarts::managementCartsSplits , $cartsSplitsOptions='options-delete-...'
	*/
	public function admin_box_carts_splits($prod_delivery_id, $cartsSplitsOptions='options-delete-no') {
	
		if(empty($this->prod_delivery_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		App::import('Model', 'ProdCartsSplit');
		$ProdCartsSplit = new ProdCartsSplit;
	
		/*
		 * cancello occorrenze di ProdCartsSplit, se il referente vuole rigenerarle
		*/
		if($cartsSplitsOptions=='options-delete-yes') {
			$ProdCartsSplit->delete_to_delivery($this->user, $prod_delivery_id);
	
			$ProdCartsSplit->populate_to_delivery($this->user, $prod_delivery_id);
	
			$this->set('carts_splits_regenerated', true);
		}
		else {
			/*
			 * ctrl eventuali occorrenze di ProdCartsSplit, se non ci sono lo popolo
			*/
			$results = $ProdCartsSplit->select_to_delivery($this->user, $prod_delivery_id);
			if(empty($results))
				$ProdCartsSplit->populate_to_delivery($this->user, $prod_delivery_id);
		}
	
		$ProdCartsSplit->unbindModel(array('belongsTo' => array('ProdDelivery')));
		$options = [];
		$options['conditions'] = array('ProdCartsSplit.organization_id' => $this->user->organization['Organization']['id'],
									'ProdCartsSplit.prod_delivery_id' => $prod_delivery_id);
		$options['recursive'] = 1;
		$options['order'] = array(Configure::read('orderUser').',ProdCartsSplit.user_id, ProdCartsSplit.article_organization_id, ProdCartsSplit.article_id, ProdCartsSplit.num_split');
		$results = $ProdCartsSplit->find('all', $options);
	
		$this->set('results',$results);
	
		$this->layout = 'ajax';
	}
	
	public function admin_box_doc_options() {
	
		$this->layout = 'ajax';
	}
	
	/*
	 * $doc_options = to-prod-users-group, to-users, to-users-label, to-users-all-modify, to-articles-monitoring, to-articles, to-articles-details
	*/
	public function admin_box_doc_print($doc_options=null) {
	
		if($doc_options=='to-users-all-modify')
			$options = array('PDF' => 'Pdf','CSV' => 'Csv');
		else
		if($doc_options=='to-users-label')
			$options = array('PDF' => 'Pdf');
		else
			$options = array('PDF' => 'Pdf','CSV' => 'Csv','EXCEL' => 'Excel');
			
		$this->set('options',$options);
	
		$this->layout = 'ajax';
	}
	
	/*
	 * $articlesOptions
	* 		'options-users-cart' Solo articoli acquisti
	*		'options-users-all'  Tutti gli articoli
	*/
	public function admin_box_management_carts_users($prod_delivery_id, $user_id, $articlesOptions, $order_by) {
	
		if(empty($this->prod_delivery_id) || $user_id==null || $articlesOptions==null) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		/*
		 * dati consegna
		*/
		App::import('Model', 'ProdDelivery');
		$ProdDelivery = new ProdDelivery;
		
		$options = [];
		$options['conditions'] = array('ProdDelivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'ProdDelivery.id' => $prod_delivery_id);
		$options['recursive'] = -1;
		$prodDeliveryResults = $ProdDelivery->find('first', $options);
		
		/*
		 * dati articoli
		*/
		App::import('Model', 'ProdDeliveriesArticle');
		$ProdDeliveriesArticle = new ProdDeliveriesArticle;
		
		$conditions = array('ProdCart.user_id' => $user_id,
							'ProdCart.prod_delivery_id' => $prod_delivery_id);
		$results = $ProdDeliveriesArticle->getArticoliDellUtenteInConsegna($this->user ,$conditions);
	
		$this->set(compact('prodDeliveryResults', 'results','user_id'));
	
		$this->layout = 'ajax';
	}
	
	/*
	 * $articlesOptions
	* 		'options-users-cart' Solo articoli acquisti
	*		'options-users-all'  Tutti gli articoli
	*
	*  $order_by
	*  		users_asc     (tutti gli utenti con acquisti)
	*  		articles_asc  (Articoli aggregati con il dettaglio degli utenti)
	*/
	public function admin_box_management_carts_articles_details($prod_delivery_id,$order_by) {
		
		if(empty($this->prod_delivery_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		/*
		 * dati consegna
		 */
		App::import('Model', 'ProdDelivery');
		$ProdDelivery = new ProdDelivery;
		
		$options = [];
		$options['conditions'] = array('ProdDelivery.organization_id' => (int)$this->user->organization['Organization']['id'],
										'ProdDelivery.id' => $prod_delivery_id);
		$options['recursive'] = -1;
		$prodDeliveryResults = $ProdDelivery->find('first', $options);

		/*
		 * dati articoli
		 */
		App::import('Model', 'ProdDeliveriesArticle');
		$ProdDeliveriesArticle = new ProdDeliveriesArticle;
		
		$conditions = array('ProdCart.user_id' => $user_id,
							'ProdCart.prod_delivery_id' => $prod_delivery_id);
		
		if($order_by=='articles_asc')
			$orderBy = array('Article' => 'Article.name asc, Article.id, Cart.created');
		else
		if($order_by=='articles_desc')
			$orderBy = array('Article' => 'Article.name desc, Article.id, Cart.created');
		else
		if($order_by=='users_asc')
			$orderBy = array('User' => Configure::read('orderUser').' asc, Cart.created');
		else
		if($order_by=='users_desc')
			$orderBy = array('User' => Configure::read('orderUser').' desc, Cart.created');
		
		// estraggo SOLO gli articoli acquistati da TUTTI gli utente in base alla consegna
		$results = $ProdDeliveriesArticle->getArticoliAcquistatiDaUtenteInConsegna($this->user ,$conditions);
		
		$this->set(compact('prodDeliveryResults', 'results', 'user_id'));
	
		$this->layout = 'ajax';
	}
	
	/*
	 * key = $prod_delivery_id_$article_organization_id_$article_id_$user_id
	*/
	public function admin_setImportoForzato($row_id, $key, $importo_forzato=0) {
	
		if(empty($row_id) || (empty($key) && strpos($key,'_') !== false)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		list($prod_delivery_id,$article_organization_id,$article_id,$user_id) = explode('_',$key);
	
		App::import('Model', 'ProdCart');
		$ProdCart = new ProdCart();
		if(!$ProdCart->exists($this->user->organization['Organization']['id'], $prod_delivery_id, $article_organization_id, $article_id, $user_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$sql = "UPDATE
					".Configure::read('DB.prefix')."prod_carts
				SET
					importo_forzato = ".$this->importoToDatabase($importo_forzato)."
				WHERE
					organization_id = ".$this->user->organization['Organization']['id']."
					AND prod_delivery_id = ".$prod_delivery_id."
					AND article_organization_id = ".$article_organization_id."
					AND article_id = ".$article_id."
					AND user_id = ".$user_id;
		//echo '<br/>'.$sql;
		try {
			$ProdCart->query($sql);
			$esito = true;
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			$esito = false;
		}
	
		if ($esito)
			$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'OKIMPORTO\',null);</script>';
		else
			$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'NO\',null);</script>';
			
		$this->set('content_for_layout',$content_for_layout);
	
		$this->layout = 'ajax';
		$this->render('/Layouts/ajax');
	}
	
	/*
	 * key = $prod_delivery_id_$article_organization_id_$article_id_$user_id
	*/
	function admin_setNotaForzato($key) {
			
		if(empty($key) && strpos($key,'_') !== false) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		list($prod_delivery_id,$article_organization_id,$article_id,$user_id) = explode('_',$key);
	
		App::import('Model', 'ProdCart');
		$ProdCart = new ProdCart();
		if(!$ProdCart->exists($this->user->organization['Organization']['id'], $prod_delivery_id, $article_organization_id, $article_id, $user_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
		if ($this->request->is('post') || $this->request->is('put')) {
	
			$sql = "UPDATE
					".Configure::read('DB.prefix')."prod_carts
				SET
					nota = '".addslashes($this->request->data['notaTextEcomm'])."'
				WHERE
					organization_id = ".$this->user->organization['Organization']['id']."
					AND prod_delivery_id = ".$prod_delivery_id."
					AND article_organization_id = ".$article_organization_id."
					AND article_id = ".$article_id."
					AND user_id = ".$user_id;
			try {
				$ProdCart->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}
		}
	
		$content_for_layout = '';
		$this->set('content_for_layout',$content_for_layout );
	
		$this->layout = 'ajax';
		$this->render('/Layouts/ajax');
	}
	
	/*
	 * Event ('#dialogmodal')open()
	* 	View/ProdDocs/admin_management_cart.ctp
	*		/Layouts/ajax.ctp
	*		modal in View/ProdDocs/admin_management_cart.ctp
	*
	*  key = $prod_delivery_id_$article_organization_id_$article_id_$user_id
	*/
	function admin_getNotaForzato($key) {
	
		if(empty($key) && strpos($key,'_') !== false) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		list($prod_delivery_i,$article_organization_id,$article_id,$user_id) = explode('_',$key);
	
		App::import('Model', 'ProdCart');
		$ProdCart = new ProdCart();
		if(!$ProdCart->exists($this->user->organization['Organization']['id'], $prod_delivery_i, $article_organization_id, $article_id, $user_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$options = [];
		$options['conditions'] = array('ProdCart.organization_id' => $this->user->organization['Organization']['id'],
										'ProdCart.prod_delivery_i' => $prod_delivery_i,
										'ProdCart.article_organization_id' => $article_organization_id,
										'ProdCart.article_id' => $article_id,
										'ProdCart.user_id' => $user_id);
		$options['recursive'] = -1;
		$options['fields'] = array('nota');
		$results = $ProdCart->find('first', $options);
			
		$nota = $results['ProdCart']['nota'];
	
		if(!empty($nota))
			$this->set('content_for_layout',$nota);
		else
			$this->set('content_for_layout','');
			
		$this->layout = 'ajax';
		$this->render('/Layouts/ajax');
	}	
}