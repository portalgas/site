<?php
App::uses('AppModel', 'Model');
App::import('Model', 'CartMultiKey');

/*
 * 
 * DROP TRIGGER IF EXISTS `k_carts_Trigger`;
 * DELIMITER |
 * CREATE TRIGGER `k_carts_Trigger` AFTER DELETE ON `k_carts`
 * FOR EACH ROW BEGIN
 * delete from k_carts_splits where organization_id = old.organization_id and user_id = old.user_id and order_id = old.order_id and article_organization_id = old.article_organization_id and article_id = old.article_id ;
 * END
 * |
 * DELIMITER ;
 */
class Cart extends CartMultiKey {
		
	/*
	 * carrello dell'utente in base alla consegna 
	 */
	public function getUserCart($user, $user_id, $delivery_id, $debug=false) {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$conditions = ['Delivery' => ['Delivery.isVisibleBackOffice' => 'Y',
									  'Delivery.id' => (int)$delivery_id],
				       'Cart' => ['Cart.user_id' => (int)$user_id,
								'Cart.deleteToReferent' => 'N']];
	
		$options = ['orders'=> true, 'storerooms' => false, 'summaryOrders' => false,
					'articoliDellUtenteInOrdine' => true,  // estraggo SOLO gli articoli acquistati da un utente in base all'ordine
					'suppliers'=>true, 'referents'=>true];
		
		$results = [];
		try {
			self::d('Cart::getUserCart()', $debug);
			self::d($options, $debug);
			$results = $Delivery->getDataWithoutTabs($user, $conditions, $options);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
				
		return $results;
	}
		
	/*
	 * dispensa nel carrello dell'utente in base alla consegna
	*/
	public function getUserCartStoreroom($user, $user_id, $delivery_id) {
	
		App::import('Model', 'Delivery');
		$Delivery = new Delivery;
	
		$results = [];
		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
	
			/*
			 * D I S P E N S A
			*/
			$options = ['orders' => false, 'storerooms' => true, 'summaryOrders' => false,
						 'suppliers'=>true, 'referents'=>false];
				
			$conditions = ['Delivery' => ['Delivery.isVisibleBackOffice' => 'Y',
										  'Delivery.id' => (int)$delivery_id],
							'Storeroom' => ['Storeroom.user_id' => (int)$user_id]];
			$orderBy = null;
			try {
				$results = $Delivery->getDataWithoutTabs($user, $conditions, $options, $orderBy);
			}
			catch (Exception $e) {
				CakeLog::write('error',$sql);
				CakeLog::write('error',$e);
			}	
		}
		
		return $results;
	}

	/*
	 * Ajax::admin_box_validation_carts()  estraggo gli acquisti da validate (ArticlesOrder.pezzi_confezione > 1)
	*
	* gestione colli
	* se valorizzato $article_id cerco un determinato articolo,
	* se no tutti gli articoli di un ordine
	*/
	public function getCartToValidate($user, $delivery_id, $order_id, $article_organization_id=0, $article_id=0) {
	
		$debug = false;
	
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'Article');
		$Article = new Article;
		            		 
		$conditions = ['Order.id' => $order_id,
						'ArticlesOrder.pezzi_confezione' => '1'];
		if(!empty($article_organization_id) && !empty($article_id)) $conditions += ['Article.organization_id' => $article_organization_id, 'Article.id' => $article_id];
		$orderBy = ['Article' => 'Article.name'];
	
		$results = $ArticlesOrder->getArticlesOrdersInOrder($user, $conditions, $orderBy);

		foreach($results as $numResults => $result) {
	
			$isArticleInCart = true;
			
			/*
			 * ctrl che l'articolo sia acquistato perche' se ordine DES la ArticlesOrder.qta_cart ha il medesimo valore per tutti i GAS 
			 */
			$isArticleInCart = $Article->isArticleInCart($user, $result['ArticlesOrder']['article_organization_id'], $result['ArticlesOrder']['article_id']);
			
			if($isArticleInCart) {
			    
				// se DES non prendo ArticlesOrder.qta_cart perche' e' la somma di tutti i GAS
				$qta_cart = $this->getOrderDesQtaCart($user, $order_id, $result['ArticlesOrder']['article_organization_id'], $result['ArticlesOrder']['article_id']);
				if($qta_cart!==false) {
					$results[$numResults]['ArticlesOrder']['qta_cart'] = $qta_cart;
					$result['ArticlesOrder']['qta_cart'] = $qta_cart;
				}
							    
				$differenza_da_ordinare = ($result['ArticlesOrder']['qta_cart'] % $result['ArticlesOrder']['pezzi_confezione']);
					
				self::d('pezzi_confezione '.$result['ArticlesOrder']['pezzi_confezione'], $debug);
				self::d('qta_cart '.$result['ArticlesOrder']['qta_cart'], $debug);
				self::d('differenza_da_ordinare '.$differenza_da_ordinare, $debug);
					
				if($differenza_da_ordinare>0) {
					$differenza_da_ordinare = ($result['ArticlesOrder']['pezzi_confezione'] - $differenza_da_ordinare);
					$differenza_importo = ($differenza_da_ordinare * $result['ArticlesOrder']['prezzo']);
		
					self::d('differenza_importo '.$differenza_importo, $debug);
						
					$results[$numResults]['ArticlesOrder']['differenza_da_ordinare'] = $differenza_da_ordinare;
					$results[$numResults]['ArticlesOrder']['differenza_importo'] = $differenza_importo;
		
				}
				else
					unset($results[$numResults]);
			}
			else
				unset($results[$numResults]);
			
		} // foreach($results['ArticlesOrder'] as $numResults => $articlesOrder)
	
		return $results;
	}
	
	/*
	 * Delivery::tabs_ajax_ecomm_carts_validation() estraggo tutti ArticlesOrder con pezzi_confezione > 1
	 * 
	 * se valorizzato $article_id cerco un determinato articolo, 
	 * se no tutti gli articoli di un ordine
	 */
	public function getCartToValidateFrontEnd($user, $delivery_id, $order_id, $article_organization_id, $article_id=0) {
		
		$debug = false;
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$options['conditions'] = ['Cart.user_id' => $user->id,
								  'ArticlesOrder.pezzi_confezione' => '1'];
		if(!empty($article_id)) 
			$options['conditions'] += ['Article.id' => $article_id];
		
		$results = $ArticlesOrder->getArticoliEventualiAcquistiInOrdine($user, $order_id, $options);
		foreach($results as $numResults => $result) {

			// se DES non prendo ArticlesOrder.qta_cart perche' e' la somma di tutti i GAS
			$qta_cart = $this->getOrderDesQtaCart($user, $order_id, $result['ArticlesOrder']['article_organization_id'], $result['ArticlesOrder']['article_id']);
			if($qta_cart!==false) {
				$results[$numResults]['ArticlesOrder']['qta_cart'] = $qta_cart;
				$result['ArticlesOrder']['qta_cart'] = $qta_cart;
			}
			
			$differenza_da_ordinare = ($result['ArticlesOrder']['qta_cart'] % $result['ArticlesOrder']['pezzi_confezione']);
			
			self::d('Article '.$result['Article']['id'].' - code '.$result['Article']['codice'], $debug);
			self::d('pezzi_confezione '.$result['ArticlesOrder']['pezzi_confezione'], $debug);
			self::d('qta_cart '.$result['ArticlesOrder']['qta_cart'], $debug);
			self::d('differenza_da_ordinare '.$differenza_da_ordinare, $debug);
			
			if($differenza_da_ordinare>0) {
				$differenza_da_ordinare = ($result['ArticlesOrder']['pezzi_confezione'] - $differenza_da_ordinare);
				$differenza_importo = ($differenza_da_ordinare * $result['ArticlesOrder']['prezzo']);
				
				self::d('differenza_importo '.$differenza_importo, $debug);
					
				$results[$numResults]['ArticlesOrder']['differenza_da_ordinare'] = $differenza_da_ordinare;
				$results[$numResults]['ArticlesOrder']['differenza_importo'] = $differenza_importo;
				
			}
			else 
				unset($results[$numResults]);
			
		} // foreach($results['ArticlesOrder'] as $numResults => $articlesOrder)

		return $results;
	}	
	
	/*
	 * estrae l'importo totale degli acquisti (qta e qta_forzato, importo_forzato) di un ordine o di una consegna
	 * NON ctrl eventuali
	 * 		- totali impostati dal referente (SummaryOrder) in Carts::managementCartsGroupByUsers 
	 * 		- spese di trasporto  (SummaryOrderTrasport)
	 *
	 *  return $importo_totale gia' formattato 1.000,00
	*/
	public function getTotImporto($user, $conditions, $debug=false) {
				
		try {		
			$sql = "SELECT
						ArticlesOrder.prezzo, 
						Cart.qta, Cart.qta_forzato, Cart.importo_forzato, Cart.user_id 
					FROM
						".Configure::read('DB.prefix')."deliveries as Delivery,
						".Configure::read('DB.prefix')."orders as `Order`,
						".Configure::read('DB.prefix')."articles_orders as ArticlesOrder,
						".Configure::read('DB.prefix')."carts as Cart
					WHERE
					    Delivery.organization_id = ".(int)$user->organization['Organization']['id']."
					    and `Order`.organization_id = ".(int)$user->organization['Organization']['id']."
					    and ArticlesOrder.organization_id = ".(int)$user->organization['Organization']['id']."
					    and Cart.organization_id = ".(int)$user->organization['Organization']['id']."
						and Delivery.id = `Order`.delivery_id
						and `Order`.id = ArticlesOrder.order_id
						and ArticlesOrder.order_id = Cart.order_id
						and ArticlesOrder.article_id = Cart.article_id
					    and Cart.deleteToReferent = 'N'
					    and ArticlesOrder.stato != 'N' ";
			if(isset($conditions['Cart.user_id'])) $sql .= " and Cart.user_id = ".$conditions['Cart.user_id'];  // filtro per un utente 
			if(isset($conditions['Order.id'])) $sql .= " and `Order`.id = ".$conditions['Order.id'];  // filtro per ordine
			if(isset($conditions['Delivery.id'])) $sql .= " and Delivery.id = ".$conditions['Delivery.id']." ";
			$sql .= " ORDER BY Delivery.id, `Order`.id";
			self::d($sql, $debug);
			$results = [];
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
				
		$importo_totale = 0;
		if(!empty($results)) {
			foreach ($results as $numResult => $result) {

				/*
				 * importo
				 */
				if($result['Cart']['importo_forzato']==0) {
					if($result['Cart']['qta_forzato']>0)
						$importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
					else
						$importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
				}
				else
					$importo = $result['Cart']['importo_forzato'];
				
				$importo_totale += $importo;
				
				self::d($numResult, $debug);
				self::d('Cart.qta '.$result['Cart']['qta'].' - ', $debug);
				self::d('Cart.qta_forzato '.$result['Cart']['qta_forzato'].' - ', $debug);
				self::d('Cart.importo_forzato '.$result['Cart']['importo_forzato'].' - ', $debug);
				self::d('ArticlesOrder.prezzo '.$result['ArticlesOrder']['prezzo'], $debug);
				self::d(' => importo '.$importo, $debug);
				self::d(' => importo_totale '.$importo_totale, $debug);
			}
		}
		
		return $importo_totale;
	}
	
	public function getLastCartDateByUser($user, $user_id, $debug) {
		
		$options = [];
		$options['conditions'] = ['Cart.organization_id' => $user->organization['Organization']['id'], 
								  'Cart.user_id' => $user_id, 
								  'Cart.stato' => 'Y'];
		$options['fields'] = ['Cart.date'];
		$options['order'] = ['Cart.date' => 'desc'];
		$options['recursive'] = -1;
		$results = $this->find('first', $options);
		self::d($options, $debug);
		self::d($results, $debug);
		
		if(empty($results)) 
			$results['Cart']['date'] = Configure::read('DB.field.datetime.empty');
		
		return $results; 
	}

	public function getOrderDesQtaCart($user, $order_id, $article_organization_id, $article_id, $debug=false) {
			
		$results = false;
		
		/* 
		 * ctrl se l'ordine e' DES
		 */
		$des_order_id = 0;
		if($user->organization['Organization']['hasDes']=='Y') {
			App::import('Model', 'DesOrdersOrganization');
			$DesOrdersOrganization = new DesOrdersOrganization();
	
			$desOrdersOrganizationResults = $DesOrdersOrganization->getDesOrdersOrganization($user, $order_id, $debug);
			if (!empty($desOrdersOrganizationResults)) {
				$des_order_id = $desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'];
			}
			
			self::d('Order '.$order_id.' DES '.$des_order_id, $debug);
			
		} // end if($user->organization['Organization']['hasDes']=='Y') 
		self::d('Order '.$order_id.' NON DES ', $debug);	
	
		if(!empty($des_order_id)) {
			$options = [];
			$options['conditions'] = ['Cart.organization_id' => $user->organization['Organization']['id'],
									'Cart.order_id' => $order_id,
									'Cart.article_organization_id' => $article_organization_id,
									'Cart.article_id' => $article_id,
									'Cart.deleteToReferent' => 'N'];
			$options['recursive'] = -1;
			$options['fields'] = ['Cart.qta','Cart.qta_forzato'];
			self::d($options, $debug);
			$cartResults = $this->find('all', $options);
			$qta_cart = 0;	
			if(!empty($cartResults)) {
				foreach($cartResults as $cartResult) {
					
					self::d('Cart.qta '.$cartResult['Cart']['qta'].' Cart.qta_forzato'.$cartResult['Cart']['qta_forzato'], $debug);
					
					if(!empty($cartResult['Cart']['qta_forzato']))
						$qta_cart += $cartResult['Cart']['qta_forzato'];
					else
						$qta_cart += $cartResult['Cart']['qta'];
				}
			}
			
			$results = $qta_cart;
		}
		
		self::d('result '.$results, $debug);
		
		return $results;
	}
				
	public $validate = [
		'organization_id' => [
				'numeric' => [
						'rule' => ['numeric'],
				],
		],
		'article_organization_id' => [
				'numeric' => [
						'rule' => ['numeric'],
				],
		],
		'article_id' => [
				'numeric' => [
						'rule' => ['numeric'],
				],
		],
		'order_id' => [
				'numeric' => [
						'rule' => ['numeric'],
				],
		],			
		'user_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
	];

	/* 
	 * se arriva da Storeroom 
	 * 		delivery_id valorizzato
	 *  	order_id    non valorizzato
	 * se arriva da Cart
	 * 		delivery_id non valorizzato
	 *  	order_id    valorizzato
	 */
	public $belongsTo = [
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = Cart.organization_id',
			'fields' => '',
			'order' => ''
		],
		'Article' => [
				'className' => 'Article',
				'foreignKey' => 'article_id',
				'conditions' => 'Article.organization_id = Cart.article_organization_id',
				'fields' => '',
				'order' => ''
		],
		'Order' => [
				'className' => 'Order',
				'foreignKey' => 'order_id',
				'conditions' => 'Order.organization_id = Cart.organization_id',
				'fields' => '',
				'order' => ''
		],
		'ArticlesOrder' => [
				'className' => 'ArticlesOrder',
				'foreignKey' => '', 
				'conditions' => 'ArticlesOrder.organization_id = Cart.organization_id AND ArticlesOrder.order_id = Cart.order_id AND ArticlesOrder.article_organization_id = Cart.article_organization_id AND ArticlesOrder.article_id = Cart.article_id',
				'fields' => '',
				'order' => ''
		],
	];	
}