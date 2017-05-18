<?php
App::uses('AppModel', 'Model');
App::import('Model', 'CartsSplitMultiKey');

class CartsSplit extends CartsSplitMultiKey {
  
	/* 
	 *  estrae tutti i CartsSplit di un ordine
	 */
	public function select_to_order($user, $order_id, $user_id=0) {
		
		$options = array();
		$options['conditions'] = array('CartsSplit.organization_id' => $user->organization['Organization']['id'],
									    'CartsSplit.order_id' => $order_id);
		if(!empty($user_id)) $options['conditions'] += array('CartsSplit.user_id' => $user_id); 
		$options['recursive'] = -1;
		$options['order'] = array('CartsSplit.user_id');
		$results = $this->find('all', $options);
		
		return $results;
	}

	public function delete_to_order($user, $order_id, $debug = false) {
		try {
			$sql = "DELETE
					FROM
						".Configure::read('DB.prefix')."carts_splits
					WHERE
						organization_id = ".(int)$user->organization['Organization']['id']."
						and order_id = ".(int)$order_id;
				if($debug) echo '<br />'.$sql;
				$result = $this->query($sql);
				
				$sql = "UPDATE ".Configure::read('DB.prefix')."carts SET importo_forzato = 0  
					WHERE
						organization_id = ".(int)$user->organization['Organization']['id']."
						and order_id = ".(int)$order_id;
				if($debug) echo '<br />'.$sql;
				$result = $this->query($sql);				
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}
	}
	
	public function populate_to_order($user, $order_id, $debug = false) { 
	
		try {
			/*
			 * estraggo tutti gli acquisti in base all'ordine
			* */
			App::import('Model', 'Cart');
			$Cart = new Cart;
			
			$options = array();
			$options['conditions'] = array('Cart.organization_id' => $user->organization['Organization']['id'],
											'Cart.order_id' => $order_id,
											'Cart.stato' => 'Y',
											'Cart.deleteToReferent' => 'N');
			$options['recursive'] = -1;
			$options['order'] = array('Cart.user_id');
			$results = $Cart->find('all', $options);
			foreach($results as $result) {
				
				if($result['Cart']['qta_forzato'] > 0)
					$qta = $result['Cart']['qta_forzato'];
				else
					$qta = $result['Cart']['qta'];
				
				for ($i = 1; $i <= $qta; $i++) {
					$data = array();
					
					$data['CartsSplit']['organization_id'] = $result['Cart']['organization_id'];
					$data['CartsSplit']['user_id'] = $result['Cart']['user_id'];
					$data['CartsSplit']['order_id'] = $result['Cart']['order_id'];
					$data['CartsSplit']['article_organization_id'] = $result['Cart']['article_organization_id'];
					$data['CartsSplit']['article_id'] = $result['Cart']['article_id'];
					$data['CartsSplit']['importo_forzato'] = 0;
					$data['CartsSplit']['num_split'] = $i;
					
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
			'order_id' => array(
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
				if (isset($val['CartsSplit']['importo_forzato'])) {
					$results[$key]['CartsSplit']['importo_forzato_'] = number_format($val['CartsSplit']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['CartsSplit']['importo_forzato_e'] = $results[$key]['CartsSplit']['importo_forzato_'].' &euro;';
				}
			}
		}
		return $results;
	}
	
	public $belongsTo = array(
			'User' => array(
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => 'User.organization_id = CartsSplit.organization_id',
					'fields' => '',
					'order' => ''
			),
			'Order' => array(
					'className' => 'Order',
					'foreignKey' => 'order_id',
					'conditions' => 'Order.organization_id = CartsSplit.organization_id',
					'fields' => '',
					'order' => ''
			),
			'Article' => array(
					'className' => 'Article',
					'foreignKey' => 'article_id',
					'conditions' => 'Article.organization_id = CartsSplit.article_organization_id',
					'fields' => '',
					'order' => ''
			),
			'ArticlesOrder' => array(
					'className' => 'ArticlesOrder',
					'foreignKey' => '',
					'conditions' => 'ArticlesOrder.organization_id = CartsSplit.organization_id AND ArticlesOrder.order_id = CartsSplit.order_id AND ArticlesOrder.article_organization_id = CartsSplit.article_organization_id AND ArticlesOrder.article_id = CartsSplit.article_id',
					'fields' => '',
					'order' => ''
			)
			/*,
			'Cart' => array(
					'className' => 'Cart',
					'foreignKey' => '',
					'conditions' => 'Cart.organization_id = CartsSplit.organization_id AND Cart.order_id = CartsSplit.order_id AND Cart.article_organization_id = CartsSplit.article_organization_id AND Cart.article_id = CartsSplit.article_id',
					'fields' => '',
					'order' => ''
			)*/			
	);
}