<?php
App::uses('AppModel', 'Model');
App::import('Model', 'ProdCartsSplitMultiKey');

class ProdCartsSplit extends ProdCartsSplitMultiKey {
  
	/* 
	 *  estrae tutti i ProdCartsSplit di una consegna
	 */
	public function select_to_delivery($user, $prod_delivery_id, $user_id=0) {
		
		$options = [];
		$options['conditions'] = array('ProdCartsSplit.organization_id' => $user->organization['Organization']['id'],
									    'ProdCartsSplit.prod_delivery_id' => $prod_delivery_id);
		if(!empty($user_id)) $options['conditions'] += array('ProdCartsSplit.user_id' => $user_id); 
		$options['recursive'] = -1;
		$options['order'] = array('ProdCartsSplit.user_id');
		$results = $this->find('all', $options);
		
		return $results;
	}

	public function delete_to_delivery($user, $prod_delivery_id, $debug = false) {
		try {
			$sql = "DELETE
					FROM
						".Configure::read('DB.prefix')."carts_splits
					WHERE
						organization_id = ".(int)$user->organization['Organization']['id']."
						and prod_delivery_id = ".(int)$prod_delivery_id;
				self::d($sql, $debug);
				$result = $this->query($sql);
				
				$sql = "UPDATE ".Configure::read('DB.prefix')."prod_carts SET importo_forzato = 0  
					WHERE
						organization_id = ".(int)$user->organization['Organization']['id']."
						and prod_delivery_id = ".(int)$prod_delivery_id;
				self::d($sql, $debug);
				$result = $this->query($sql);				
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}
	}
	
	public function populate_to_delivery($user, $prod_delivery_id, $debug = false) { 
	
		try {
			/*
			 * ctrl che l'ordine sia in stato valido per creare i dati aggragati
			 */
			App::import('Model', 'ProdDelivery');
			$ProdDelivery = new ProdDelivery;
			
			$options = [];
			$options['conditions'] = array('ProdDelivery.organization_id' => $user->organization['Organization']['id'],
											'ProdDelivery.id' => $prod_delivery_id);
			$options['recursive'] = -1;
			$options['fields'] = array('prod_delivery_state_id');
			$results = $ProdDelivery->find('first', $options);
			if($results['ProdDelivery']['prod_delivery_state_id']!=Configure::read('PROCESSED-POST-DELIVERY')) {
				if($debug) echo "<br />la consegna ha uno stato (".$results['ProdDelivery']['prod_delivery_state_id'].") non valido per l'operazione di populate_to_delivery()";
				return;
			}
				
			/*
			 * estraggo tutti gli acquisti in base all'ordine
			* */
			App::import('Model', 'ProdCart');
			$ProdCart = new ProdCart;
			
			$options = [];
			$options['conditions'] = array('ProdCart.organization_id' => $user->organization['Organization']['id'],
											'ProdCart.prod_delivery_id' => $prod_delivery_id,
											'ProdCart.stato' => 'Y',
											'ProdCart.deleteToReferent' => 'N');
			$options['recursive'] = -1;
			$options['order'] = array('ProdCart.user_id');
			$results = $ProdCart->find('all', $options);
			foreach($results as $result) {
				
				if($result['ProdCart']['qta_forzato'] > 0)
					$qta = $result['ProdCart']['qta_forzato'];
				else
					$qta = $result['ProdCart']['qta'];
				
				for ($i = 1; $i <= $qta; $i++) {
					$data = [];
					
					$data['ProdCartsSplit']['organization_id'] = $result['ProdCart']['organization_id'];
					$data['ProdCartsSplit']['user_id'] = $result['ProdCart']['user_id'];
					$data['ProdCartsSplit']['prod_delivery_id'] = $result['ProdCart']['prod_delivery_id'];
					$data['ProdCartsSplit']['article_organization_id'] = $result['ProdCart']['article_organization_id'];
					$data['ProdCartsSplit']['article_id'] = $result['ProdCart']['article_id'];
					$data['ProdCartsSplit']['importo_forzato'] = 0;
					$data['ProdCartsSplit']['num_split'] = $i;
					
					$this->save($data);
				}
			} // end foreach($results as $result)	

		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}			
	}
	
	public $validate = array(
			'organization_id' => array(
					'numeric' => array(
							'rule' => array('numeric'),
							//'message' => 'Your custom message here',
							//'allowEmpty' => false,
							//'required' => false,
							//'last' => false, // Stop validation after this rule
							//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
			),
			'user_id' => array(
					'numeric' => array(
							'rule' => array('numeric'),
							//'message' => 'Your custom message here',
							//'allowEmpty' => false,
							//'required' => false,
							//'last' => false, // Stop validation after this rule
							//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
			),
			'prod_delivery_id' => array(
					'numeric' => array(
							'rule' => array('numeric'),
							//'message' => 'Your custom message here',
							//'allowEmpty' => false,
							//'required' => false,
							//'last' => false, // Stop validation after this rule
							//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
			),
			'article_organization_id' => array(
					'numeric' => array(
							'rule' => array('numeric'),
							//'message' => 'Your custom message here',
							//'allowEmpty' => false,
							//'required' => false,
							//'last' => false, // Stop validation after this rule
							//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
			),
			'article_id' => array(
					'numeric' => array(
							'rule' => array('numeric'),
							//'message' => 'Your custom message here',
							//'allowEmpty' => false,
							//'required' => false,
							//'last' => false, // Stop validation after this rule
							//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
			),
			'num_split' => array(
					'numeric' => array(
							'rule' => array('numeric'),
							//'message' => 'Your custom message here',
							//'allowEmpty' => false,
							//'required' => false,
							//'last' => false, // Stop validation after this rule
							//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
			),
			'importo_forzato' => array(
					'numeric' => array(
							'rule' => array('numeric'),
							//'message' => 'Your custom message here',
							//'allowEmpty' => false,
							//'required' => false,
							//'last' => false, // Stop validation after this rule
							//'on' => 'create', // Limit validation to 'create' or 'update' operations
					),
			),
	);
	

	public function afterFind($results, $primary = true) {
	
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if (isset($val['ProdCartsSplit']['importo_forzato'])) {
					$results[$key]['ProdCartsSplit']['importo_forzato_'] = number_format($val['ProdCartsSplit']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdCartsSplit']['importo_forzato_e'] = $results[$key]['ProdCartsSplit']['importo_forzato_'].' &euro;';
				}
			}
		}
		return $results;
	}
	
	public $belongsTo = array(
			'User' => array(
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => 'User.organization_id = ProdCartsSplit.organization_id',
					'fields' => '',
					'order' => ''
			),
			'ProdDelivery' => array(
					'className' => 'ProdDelivery',
					'foreignKey' => 'prod_delivery_id',
					'conditions' => 'ProdDelivery.organization_id = ProdCartsSplit.organization_id',
					'fields' => '',
					'order' => ''
			),
			'Article' => array(
					'className' => 'Article',
					'foreignKey' => 'article_id',
					'conditions' => 'Article.organization_id = ProdCartsSplit.article_organization_id',
					'fields' => '',
					'order' => ''
			),
			'ProdDeliveriesArticle' => array(
					'className' => 'ProdDeliveriesArticle',
					'foreignKey' => '',
					'conditions' => 'ProdDeliveriesArticle.organization_id = ProdCartsSplit.organization_id AND ProdDeliveriesArticle.prod_delivery_id = ProdCartsSplit.prod_delivery_id AND ProdDeliveriesArticle.article_organization_id = ProdCartsSplit.article_organization_id AND ProdDeliveriesArticle.article_id = ProdCartsSplit.article_id',
					'fields' => '',
					'order' => ''
			)
			/*,
			'ProdCart' => array(
					'className' => 'ProdCart',
					'foreignKey' => '',
					'conditions' => 'ProdCart.organization_id = ProdCartsSplit.organization_id AND ProdCart.prod_delivery_id = ProdCartsSplit.prod_delivery_id AND ProdCart.article_organization_id = ProdCartsSplit.article_organization_id AND ProdCart.article_id = ProdCartsSplit.article_id',
					'fields' => '',
					'order' => ''
			)*/			
	);
}