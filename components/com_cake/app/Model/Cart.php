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
	
		$conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
				'Delivery.id' => (int)$delivery_id),
				'Cart' => array('Cart.user_id' => (int)$user_id,
								'Cart.deleteToReferent' => 'N'));
	
		$options = array('orders'=> true, 'storerooms' => false, 'summaryOrders' => false,
				'articoliDellUtenteInOrdine' => true,  // estraggo SOLO gli articoli acquistati da un utente in base all'ordine
				'suppliers'=>true, 'referents'=>true);
		
		$results = array();
		try {
			if($debug) {
				echo "<pre>Cart::getUserCart() \n";
				print_r($options);
				echo "</pre>";
			}
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
	
		$results = array();
		if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
	
			/*
			 * D I S P E N S A
			*/
			$options = array('orders' => false, 'storerooms' => true, 'summaryOrders' => false,
							'suppliers'=>true, 'referents'=>false);
				
			$conditions = array('Delivery' => array('Delivery.isVisibleBackOffice' => 'Y',
													'Delivery.id' => (int)$delivery_id),
								'Storeroom' => array('Storeroom.user_id' => (int)$user_id));
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
	* se valorizzato $article_id cerco un determinato articolo,
	* se no tutti gli articoli di un ordine
	*/
	public function getCartToValidate($user, $delivery_id, $order_id, $article_organization_id=0, $article_id=0) {
	
		$debug = false;
	
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
	
		$conditions = array('Order.id' => $order_id,
				'ArticlesOrder.pezzi_confezione' => '1');
		if(!empty($article_organization_id) && !empty($article_id)) $conditions += array('Article.organization_id' => $article_organization_id, 'Article.id' => $article_id);
		$orderBy = array('Article' => 'Article.name');
	
		$results = $ArticlesOrder->getArticlesOrdersInOrder($user, $conditions, $orderBy);
		foreach($results as $numResults => $result) {
	
			$differenza_da_ordinare = ($result['ArticlesOrder']['qta_cart'] % $result['ArticlesOrder']['pezzi_confezione']);
				
			if($debug) {
				echo '<br />';
				echo '<br />pezzi_confezione '.$result['ArticlesOrder']['pezzi_confezione'];
				echo '<br />qta_cart '.$result['ArticlesOrder']['qta_cart'];
				echo '<br />differenza_da_ordinare '.$differenza_da_ordinare;
			}
				
			if($differenza_da_ordinare>0) {
				$differenza_da_ordinare = ($result['ArticlesOrder']['pezzi_confezione'] - $differenza_da_ordinare);
				$differenza_importo = ($differenza_da_ordinare * $result['ArticlesOrder']['prezzo']);
	
				if($debug) echo '<br />differenza_importo '.$differenza_importo;
					
				$results[$numResults]['ArticlesOrder']['differenza_da_ordinare'] = $differenza_da_ordinare;
				$results[$numResults]['ArticlesOrder']['differenza_importo'] = $differenza_importo;
	
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
	public function getCartToValidateFrontEnd($user, $delivery_id, $order_id, $article_organization_id=0, $article_id=0) {
		
		$debug = false;
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$options['conditions'] = array('Cart.user_id' => $user->id,
										'Order.id' => $order_id,
										'ArticlesOrder.order_id' => $order_id,
										'ArticlesOrder.pezzi_confezione' => '1');
		if(!empty($article_organization_id) && !empty($article_id)) $options['conditions'] += array('Article.organization_id' => $article_organization_id, 'Article.id' => $article_id);
		
		$results = $ArticlesOrder->getArticoliEventualiAcquistiInOrdine($user, $options);
		foreach($results as $numResults => $result) {

			$differenza_da_ordinare = ($result['ArticlesOrder']['qta_cart'] % $result['ArticlesOrder']['pezzi_confezione']);
			
			if($debug) {
				echo '<br />';
				echo '<br />pezzi_confezione '.$result['ArticlesOrder']['pezzi_confezione'];
				echo '<br />qta_cart '.$result['ArticlesOrder']['qta_cart'];
				echo '<br />differenza_da_ordinare '.$differenza_da_ordinare;
			}
			
			if($differenza_da_ordinare>0) {
				$differenza_da_ordinare = ($result['ArticlesOrder']['pezzi_confezione'] - $differenza_da_ordinare);
				$differenza_importo = ($differenza_da_ordinare * $result['ArticlesOrder']['prezzo']);
				
				if($debug) echo '<br />differenza_importo '.$differenza_importo;
					
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
				
		$sql = "SELECT
					ArticlesOrder.prezzo, 
					Cart.qta, Cart.qta_forzato, Cart.importo_forzato,
					Cart.user_id 
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
		if(isset($conditions['Order.id'])) $sql .= " and `Order`.id = ".$conditions['Order.id'];  // filtro per ordine
		if(isset($conditions['Delivery.id'])) $sql .= " and Delivery.id = ".$conditions['Delivery.id']." ";
		$sql .= " ORDER BY Delivery.id, `Order`.id";
		if($debug) echo '<br />'.$sql;
		$results = array();
		try {
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
				
				if($debug) {
					echo '<br />'.$numResult.') ';
					echo 'Cart.qta '.$result['Cart']['qta'].' - ';
					echo 'Cart.qta_forzato '.$result['Cart']['qta_forzato'].' - ';
					echo 'Cart.importo_forzato '.$result['Cart']['importo_forzato'].' - ';
					echo 'ArticlesOrder.prezzo '.$result['ArticlesOrder']['prezzo'];
					echo ' => importo '.$importo;
					echo ' => importo_totale '.$importo_totale;
				}
			}
		}
		
		return $importo_totale;
	}
	
    /*
     * aggiunge ad un ordine le eventuali 
     *  SummaryOrder 
     *  SummaryOrderTrapsort spese di trasporto
     *  SummaryOrderMore spese generiche
     *  SummaryOrderLess sconti
     *
     *  call 
     *      ExportDocs::userCart
     *      Delivery::tabsAjaxUserCartDeliveries 
     *
     */
    public function addSummaryOrder($user, $user_id, $order) {
        
        $order_id = $order['Order']['id'];		

        /*
        * dati dell'ordine
        */
        $hasTrasport = $order['Order']['hasTrasport']; /* trasporto */
        $trasport = $order['Order']['trasport'];
        $hasCostMore = $order['Order']['hasCostMore']; /* spesa aggiuntiva */
        $cost_more = $order['Order']['cost_more'];
        $hasCostLess = $order['Order']['hasCostLess'];  /* sconto */
        $cost_less = $order['Order']['cost_less'];
        $typeGest = $order['Order']['typeGest'];   /* AGGREGATE / SPLIT */

        $resultsSummaryOrder = array();
        $resultsSummaryOrderTrasport = array();
        $resultsSummaryOrderCostMore = array();
        $resultsSummaryOrderCostLess = array();

        if($hasTrasport=='Y') {
            App::import('Model', 'SummaryOrderTrasport');
            $SummaryOrderTrasport = new SummaryOrderTrasport;

            $resultsSummaryOrderTrasport = $SummaryOrderTrasport->select_to_order($user, $order_id, $user_id);
        }
        if($hasCostMore=='Y') {
            App::import('Model', 'SummaryOrderCostMore');
            $SummaryOrderCostMore = new SummaryOrderCostMore;

            $resultsSummaryOrderCostMore = $SummaryOrderCostMore->select_to_order($user, $order_id, $user_id);
        }
        if($hasCostLess=='Y') {
            App::import('Model', 'SummaryOrderCostLess');
            $SummaryOrderCostLess = new SummaryOrderCostLess;

            $resultsSummaryOrderCostLess = $SummaryOrderCostLess->select_to_order($user, $order_id, $user_id);
        }

        App::import('Model', 'SummaryOrder');
        $SummaryOrder = new SummaryOrder;
        $resultsSummaryOrder = $SummaryOrder->select_to_order($user, $order_id, $user_id); // se l'ordine e' ancora aperto e' vuoto

        $results = array();
        $results['SummaryOrder'] = $resultsSummaryOrder;
        $results['SummaryOrderTrasport'] = $resultsSummaryOrderTrasport;
        $results['SummaryOrderCostMore'] = $resultsSummaryOrderCostMore;
        $results['SummaryOrderCostLess'] = $resultsSummaryOrderCostLess;

        /*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/

        return $results;
    }
    
	public $validate = array(
		'organization_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'article_organization_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'article_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'order_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),			
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

	/* 
	 * se arriva da Storeroom 
	 * 		delivery_id valorizzato
	 *  	order_id    non valorizzato
	 * se arriva da Cart
	 * 		delivery_id non valorizzato
	 *  	order_id    valorizzato
	 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = Cart.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Article' => array(
				'className' => 'Article',
				'foreignKey' => 'article_id',
				'conditions' => 'Article.organization_id = Cart.article_organization_id',
				'fields' => '',
				'order' => ''
		),
		'Order' => array(
				'className' => 'Order',
				'foreignKey' => 'order_id',
				'conditions' => 'Order.organization_id = Cart.organization_id',
				'fields' => '',
				'order' => ''
		),
		'ArticlesOrder' => array(
				'className' => 'ArticlesOrder',
				'foreignKey' => '', 
				'conditions' => 'ArticlesOrder.organization_id = Cart.organization_id AND ArticlesOrder.order_id = Cart.order_id AND ArticlesOrder.article_organization_id = Cart.article_organization_id AND ArticlesOrder.article_id = Cart.article_id',
				'fields' => '',
				'order' => ''
		),			
	);	
}