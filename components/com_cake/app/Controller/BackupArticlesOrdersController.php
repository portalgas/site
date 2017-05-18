<?php
App::uses('AppController', 'Controller');

class BackupArticlesOrdersController extends AppController {
			
    public function beforeFilter() {
    	
    	parent::beforeFilter();
    	
		App::import('Model', 'Order');
		$Order = new Order;
		
		/* ctrl ACL */
		if($this->isSuperReferente()) {
				 
		}
		else {
			if(empty($this->order_id) || !$this->isReferentGeneric() || !$Order->aclReferenteSupplierOrganization($this->user, $this->order_id)) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}    	
    }

	public function admin_index() {
		
		$debug = false;
		
		if(empty($this->order_id)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		$Order->id = $this->order_id;
		if (!$Order->exists($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $this->user->organization['Organization']['id'],
										'Order.id' => $this->order_id);
		$options['recursive'] = 0;
		$order = $Order->find('first', $options);
		$this->set('order', $order);
	
		if ($this->request->is('post') || $this->request->is('put')) {

			try {
				$sql = "INSERT INTO ".Configure::read('DB.prefix')."articles_orders
						(SELECT * 
						 FROM ".Configure::read('DB.prefix')."backup_articles_orders as BackupArticlesOrder 
						 WHERE
						 	BackupArticlesOrder.organization_id = ".(int)$this->user->organization['Organization']['id']." 
						 	AND BackupArticlesOrder.order_id = ".$this->order_id." )";		 	
				if($debug) echo '<br />'.$sql;
				$results = $this->BackupArticlesOrder->query($sql);
			
				$sql = "INSERT INTO ".Configure::read('DB.prefix')."carts
						(SELECT * 
						 FROM ".Configure::read('DB.prefix')."backup_carts as BackupCart 
						 WHERE
						 	BackupCart.organization_id = ".(int)$this->user->organization['Organization']['id']." 
						 	AND BackupCart.order_id = ".$this->order_id." )";		 	
				if($debug) echo '<br />'.$sql;
				$results = $this->BackupArticlesOrder->query($sql);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}
			
							
			/*
			 * aggiorno lo stato dell'ordine
			 * 		OPEN-NEXT o OPEN o ...
			 * */
			$utilsCrons = new UtilsCrons(new View(null));
			if($debug) echo "<pre>";
			$utilsCrons->ordersStatoElaborazione($this->user->organization['Organization']['id'], (Configure::read('developer.mode')) ? true : false, $this->order_id);
			if($debug) echo "</pre>";
			
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Orders&action=home&order_id='.$this->order_id;
			
			if(!$debug) $this->myRedirect($url);

		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		/* 
		 * articoli gia' associati all'ordine
		 */
		$this->BackupArticlesOrder->unbindModel(array('belongsTo' => array('Cart', 'Order')));
		$options = array();
		$options['conditions'] = array('BackupArticlesOrder.organization_id' => $this->user->organization['Organization']['id'],
									   'BackupArticlesOrder.order_id' => $this->order_id,
									   'Article.stato' => 'Y');
		$options['order'] = 'Article.name'; 
		$options['recursive'] = 0;
		$results = $this->BackupArticlesOrder->find('all',$options);
	
		/*
		 *  ctrl eventuali acquisti gia' fatti
		 */
		App::import('Model', 'BackupCart');
		foreach ($results as $numResult => $result) {
			$BackupCart = new BackupCart();
			$options = array();
			$options['conditions'] = array('BackupCart.organization_id' => $this->user->organization['Organization']['id'],
											'BackupCart.order_id' => $result['BackupArticlesOrder']['order_id'],
											'BackupCart.article_organization_id' => $result['BackupArticlesOrder']['article_organization_id'],
											'BackupCart.article_id' => $result['BackupArticlesOrder']['article_id'],
											'BackupCart.deleteToReferent' => 'N',
									);
			$options['order'] = '';
			$options['recursive'] = -1;
			$cartResults = $BackupCart->find('all', $options);
			
			$results[$numResult]['BackupCart'] = $cartResults;
			
		}
		$this->set('results', $results);
	}
}