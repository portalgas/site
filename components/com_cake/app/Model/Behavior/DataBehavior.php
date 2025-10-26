<?php 
/* 
 * Model: Delivery
 * 
 * options = array(
 *				  'orders'=>true, 		 ORDINI
 *				  'storerooms' => true, DISPENSA
 *				  'summaryOrders' => true, per Tesoriere
 *
 *				  'articoliEventualiAcquistiNoFilterInOrdine'=>true   estraggo tutti gli articoli acquistati in base all'ordine ed EVENTUALI Cart di un utente
 * 				  'articlesOrdersInOrder'=>true                      estraggo tutti gli articoli in base all'ordine
 * 				  'articoliDellUtenteInOrdine'=>true,     estraggo SOLO gli articoli acquistati da UN utente in base all'ordine
 *                'articlesOrdersInOrderAndCartsAllUsers'=>true,     estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine 
 * 
 *				  'suppliers'=>true, 'referents'=>true);
 */
App::uses('UtilsCommons', 'Lib');

class DataBehavior extends ModelBehavior {

	var $debug = false;
	var $result = [];
	private $utilsCommons;
	
	public function setup(Model $Model, $settings = []) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = [
					'option1_key' => 'option1_default_value',
					'option2_key' => 'option2_default_value',
					'option3_key' => 'option3_default_value',
			];
		}
		$this->settings[$Model->alias] = array_merge(
				$this->settings[$Model->alias], (array)$settings);
		
		$this->utilsCommons = new UtilsCommons();
	}
	
   /* 
    * Deliveries::tabs()
    * Deliveries::tabsEcomm()
    * Deliveries::tabsUserCart('orders'=>true, 'storerooms' => false) 
    * Deliveries::tabsUserCart('orders'=>false, 'storerooms' => true) come dispensa 
    * ExportDocs::userCart('orders'=>true, 'storerooms' => false)
    * ExportDocs::userCart('orders'=>false, 'storerooms' => true) come dispensa
    */
	public function getDataTabs(Model $Model, $user, $conditions, $options, $orderBy = []) {
				
		$tabResults = $this->getTabsToDeliveriesData($Model, $user, $conditions['Delivery'], $orderBy);
		
		foreach($tabResults as $numTab => $tabResult) {

			$this->result['Tab'][$numTab] = $tabResult['Delivery'];
			
			if(isset($conditions['Delivery']['Delivery.id'])) $conditions['Delivery'] = array('Delivery.id' => $conditions['Delivery']['Delivery.id']);
			else $conditions['Delivery'] = [];
			$conditions['Delivery'] += array('Delivery.data' => $tabResult['Delivery']['data']);
			$deliveryResults = $this->_getDeliveries($Model, $user, $conditions, $orderBy);	
			foreach($deliveryResults as $numDelivery => $deliveryResult) {
				
				$this->result['Tab'][$numTab]['Delivery'][$numDelivery] = $deliveryResult['Delivery'];
				
				if($options['orders']) {
					// estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
					if(isset($options['articlesOrdersInOrderAndCartsAllUsers']) && $options['articlesOrdersInOrderAndCartsAllUsers'])
						$this->_getOrdersAndArticlesOrdersAllUsers($Model, $numTab, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
					else
						$this->_getOrdersAndArticlesOrdersByUserId($Model, $numTab, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
				}
				else
				if($options['storerooms'])
					$this->_getStorerooms($Model, $numTab, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
				else
				if($options['summaryOrderAggregates'])   // referente in "Gestisci gli acquisti aggregati"  (Carts::managementCartsGroupByUsers)
					$this->_getSummaryOrderAggregates($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);				
				else 
				if($options['summaryOrders'])   // per Tesoriere
					$this->_getSummaryOrders($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);				
				else 
				if($options['summaryOrderTrasports'])   // Ajax::admin_box_trasport()
					$this->_getSummaryOrderTrasports($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);				
				else
				if($options['summaryOrderCostMores'])   // Ajax::admin_box_cost_more()
					$this->_getSummaryOrderCostMores($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);			
				else
				if($options['summaryOrderCostLess'])   // Ajax::admin_box_cost_less()
					$this->_getSummaryOrderCostLess($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
				
			} // ciclo deliveries
		} // ciclo tabs

		return $this->result;
	}	
	
	/* 
	 * estraggo tutti i dati senza Tabs
	 * AjaxController::admin_management_cart() non ho i tabs
	 */
	public function getDataWithoutTabs(Model $Model, $user, $conditions, $options, $orderBy = []) {
	
		$numTab=-1; // setto a -1 cosi' escludo Tabs
		
		$deliveryResults = $this->_getDeliveries($Model, $user, $conditions, $orderBy);		
		foreach($deliveryResults as $numDelivery => $deliveryResult) {
				
			$this->result['Delivery'][$numDelivery] = $deliveryResult['Delivery'];

			if($options['orders']) {
				// estraggo SOLO gli articoli acquistati da TUTTI gli utente in base all'ordine
				if(isset($options['articlesOrdersInOrderAndCartsAllUsers']) && $options['articlesOrdersInOrderAndCartsAllUsers']) 
					$this->_getOrdersAndArticlesOrdersAllUsers($Model, $numTab, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
				else 
					$this->_getOrdersAndArticlesOrdersByUserId($Model, $numTab, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
			}
			else
			if($options['storerooms']) 
				$this->_getStorerooms($Model, $numTab, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
			else
			if($options['summaryOrderAggregates'])   // referente in "Gestisci gli acquisti aggregati"  (Carts::managementCartsGroupByUsers)
				$this->_getSummaryOrderAggregates($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);				
			else
			if($options['summaryOrders'])  // per Tesoriere 
				$this->_getSummaryOrders($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
			else
			if($options['summaryOrderTrasports'])  // Ajax::admin_box_trasport()
				$this->_getSummaryOrderTrasports($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
			else
			if($options['summaryOrderCostMores'])   // Ajax::admin_box_cost_more()
				$this->_getSummaryOrderCostMores($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
			else
			if($options['summaryOrderCostLess'])   // Ajax::admin_box_cost_less()
				$this->_getSummaryOrderCostLess($Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy);
			
		} // ciclo deliveries
	
		return $this->result;
	}
			
	private function _getOrdersAndArticlesOrdersByUserId(Model $Model, $numTab, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy = []) {
				
		$numOrder=-1;
		$numArticlesOrder=-1;
		
		App::import('Model', 'Order');
		$Order = new Order;
		
		if(isset($orderBy['Order'])) $orderLocal = $orderBy['Order'];
		else $orderLocal = 'Order.data_inizio ASC';
		
		$conditionsLocal = ['Order.organization_id' => $user->organization['Organization']['id'],
							 'Order.delivery_id' => $deliveryResult['Delivery']['id'],
							 'Order.isVisibleBackOffice' => 'Y'];
		if(isset($options['orders_attivi'])) // per tabsEcomm
			$conditionsLocal += array('DATE(Order.data_fine) >= CURDATE()'); 
		if(isset($conditions['Order'])) $conditionsLocal += $conditions['Order'];
		if(isset($conditions['SuppliersOrganization'])) $conditionsLocal += $conditions['SuppliersOrganization']; 		
		
		/*
		 * prendo solo OrderState
		 */
		$Order->unbindModel(array('belongsTo' => array('Delivery')));
		$orderResults = $Order->find('all', ['conditions' => $conditionsLocal,
												 'order' => $orderLocal,
												 'recursive' => 0]);
	
		foreach ($orderResults as $numOrder => $order) {

			// order
			$this->_setOrder($Model, $numTab, $numDelivery, $numOrder, $order);
				
			// se Delivery::tabs() non loggato non mi servono gli articoli 
			if(isset($options['articlesOrdersInOrder']) && $options['articlesOrdersInOrder']==false) { 

			}
			else {	
				/*
				 * ottienti dati Article, ArticlesOrder, Cart, ArticleType
				* */
				App::import('Model', 'ArticlesArticlesType');
				$ArticlesArticlesType = new ArticlesArticlesType;
				
				App::import('Model', 'ArticlesOrder');
				$ArticlesOrder = new ArticlesOrder;
				
				$conditionsLocal = ['Order.id' => $order['Order']['id']];
				if(isset($conditions['Cart']))          $conditionsLocal += $conditions['Cart'];
				if(isset($conditions['ArticlesOrder'])) $conditionsLocal += $conditions['ArticlesOrder'];
				if(isset($conditions['Article']))       $conditionsLocal += $conditions['Article'];
				if(isset($conditions['FilterArticleName'])) $conditionsLocal += $conditions['FilterArticleName'];
				if(isset($conditions['FilterArticleArticleTypeIds'])) $conditionsLocal += $conditions['FilterArticleArticleTypeIds'];
				
				if(isset($options['articlesOrdersInOrder']) && $options['articlesOrdersInOrder'])  // Ajax::admin_box_validation_carts, Ajax::admin_box_summary_orders
					$articlesOrders = $ArticlesOrder->getArticlesOrdersInOrder($user, $conditionsLocal, $orderBy);
				else
				if(isset($options['articoliEventualiAcquistiNoFilterInOrdine']) && $options['articoliEventualiAcquistiNoFilterInOrdine'])  { // Deliveries::tabsEcomm(), Deliveries::tabs() se loggati
					$articlesOrders = $ArticlesOrder->getArticoliEventualiAcquistiNoFilterInOrdine($user, $conditionsLocal, $orderBy);
				}
				else
				if(isset($options['articoliDellUtenteInOrdine']) && $options['articoliDellUtenteInOrdine'])  // Deliveries::tabsUserCart()
					$articlesOrders = $ArticlesOrder->getArticoliDellUtenteInOrdine($user, $conditionsLocal, $orderBy);
												
				$user_ids = [];
				if(!empty($articlesOrders)) {
					/*
					 * ottengo user_id che hanno gli acquisti gia' saldati SummaryOrder.saldato_a = CASSIERE / TESORIERE
					 */
					if($options['summaryOrders']) 
						$user_ids = $this->_getUserIdsJustSaldato($user, $order['Order']['id'], $articlesOrders);

					/*
					 * qui tratto un solo user, se ha gia' saldato l'ordine non potra' fare nuovi acquisti
					 */
					if(!empty($user_ids))
						$totSummaryOrderSaldato = 1;
					else
						$totSummaryOrderSaldato = 0;
					 
					foreach($articlesOrders as $numArticlesOrder => $articlesOrder) {
								
						if($numTab==-1) {
							$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['Article'][$numArticlesOrder] = $articlesOrder['Article'];
							$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['ArticlesOrder'][$numArticlesOrder] = $articlesOrder['ArticlesOrder'];
							if(isset($articlesOrder['Cart'])) $this->result['Delivery'][$numDelivery]['Order'][$numOrder]['Cart'][$numArticlesOrder] = $articlesOrder['Cart'];
							if(isset($articlesOrder['User'])) $this->result['Delivery'][$numDelivery]['Order'][$numOrder]['User'][$numArticlesOrder] = $articlesOrder['User'];
							$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrder'][$numArticlesOrder] = $totSummaryOrderSaldato;		
						}
						else {
							$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['Article'][$numArticlesOrder] = $articlesOrder['Article'];
							$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['ArticlesOrder'][$numArticlesOrder] = $articlesOrder['ArticlesOrder'];
							if(isset($articlesOrder['Cart'])) $this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['Cart'][$numArticlesOrder] = $articlesOrder['Cart'];
							if(isset($articlesOrder['User'])) $this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['User'][$numArticlesOrder] = $articlesOrder['User'];
							$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrder'][$numArticlesOrder] = $totSummaryOrderSaldato;
						}
						
					} // loop foreach($articlesOrders as $numArticlesOrder => $articlesOrder)
				} // end if(!empty($articlesOrders))
			}  // end if(isset($options['articlesOrdersInOrder']) && $options['articlesOrdersInOrder']==false) 
						
			// suppliersOrganization
			if($options['suppliers']) 
				$this->_setSuppliersOrganizations($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
		
			// suppliersOrganizationsReferents 
			 if($options['referents'])
				$this->_setSuppliersOrganizationsReferent($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);

		} // ciclo orders

		if($numTab==-1) {
			$this->result['Delivery'][$numDelivery]['totOrders'] = $numOrder+1;
			$this->result['Delivery'][$numDelivery]['totArticlesOrder'] = $numArticlesOrder+1;
		}
		else {
			$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['totOrders'] = $numOrder+1;
			$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['totArticlesOrder'] = $numArticlesOrder+1;
		}
	}

	private function _getOrdersAndArticlesOrdersAllUsers(Model $Model, $numTab, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy = []) {
	
		$numOrder=-1;
		$numArticlesOrder=-1;
	
		App::import('Model', 'Order');
		$Order = new Order;
	
		if(isset($orderBy['Order'])) $orderLocal = $orderBy['Order'];
		else $orderLocal = 'Order.data_inizio ASC';
	
		$conditionsLocal = ['Order.organization_id' => $user->organization['Organization']['id'],
							'Order.delivery_id' => $deliveryResult['Delivery']['id'],
							'Order.isVisibleBackOffice' => 'Y'];
		if(isset($options['orders_attivi'])) // per tabsEcomm
			$conditionsLocal += ['DATE(Order.data_fine) >= CURDATE()'];
		if(isset($conditions['Order'])) $conditionsLocal += $conditions['Order'];
		if(isset($conditions['SuppliersOrganization'])) $conditionsLocal += $conditions['SuppliersOrganization'];

		/*
		 * prendo solo OrderState
		*/
		$Order->unbindModel(['belongsTo' => ['Delivery']]);
		$orderResults = $Order->find('all',['conditions' => $conditionsLocal,
											'order' => $orderLocal,
											'recursive' => 0]);

		foreach ($orderResults as $numOrder => $order) {

			// order
			$this->_setOrder($Model, $numTab, $numDelivery, $numOrder, $order);
			
			// se Delivery::tabs() non loggato non mi servono gli articoli
			if(isset($options['articlesOrdersInOrder']) && $options['articlesOrdersInOrder']==false) {
	
			}
			else {
				/*
				 * ottienti dati Article, ArticlesOrder, Cart
				* */
				App::import('Model', 'ArticlesOrder');
				$ArticlesOrder = new ArticlesOrder;
				
				$conditionsLocal = ['Order.id' => $order['Order']['id'], 'ArticlesOrder.order_id' => $order['Order']['id']];
				if(isset($conditions['Cart']))          $conditionsLocal += $conditions['Cart'];
				if(isset($conditions['ArticlesOrder'])) $conditionsLocal += $conditions['ArticlesOrder'];
				if(isset($conditions['Article']))       $conditionsLocal += $conditions['Article'];
				if(isset($conditions['User']))          $conditionsLocal += $conditions['User'];

				if(isset($options['articlesOrdersInOrderAndCartsAllUsers']) && $options['articlesOrdersInOrderAndCartsAllUsers'])
					$articlesOrders = $ArticlesOrder->getArticoliAcquistatiDaUtenteInOrdine($user, $conditionsLocal, $orderBy);
		
				$user_ids = [];
				if(!empty($articlesOrders)) {
					/*
					 * ottengo user_id che hanno gli acquisti gia' saldati SummaryOrder.saldato_a = CASSIERE / TESORIERE
					 */
					if($options['summaryOrders']) 
						$user_ids = $this->_getUserIdsJustSaldato($user, $order['Order']['id'], $articlesOrders);

					foreach($articlesOrders as $numArticlesOrder => $articlesOrder) {

						$totSummaryOrderSaldato = 0;
						if(isset($articlesOrder['Cart']['user_id']))
							if (array_key_exists($articlesOrder['Cart']['user_id'], $user_ids))
								$totSummaryOrderSaldato = 1;
					
						if($numTab==-1) {
							$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['Article'][$numArticlesOrder] = $articlesOrder['Article'];
							$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['ArticlesOrder'][$numArticlesOrder] = $articlesOrder['ArticlesOrder'];
							$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['Cart'][$numArticlesOrder] = $articlesOrder['Cart'];
							$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['User'][$numArticlesOrder] = $articlesOrder['User'];
							$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrder'][$numArticlesOrder] = $totSummaryOrderSaldato;
							
							/*
							 * userprofile
							*/
							$userTmp = JFactory::getUser($articlesOrder['Cart']['user_id']);
							$userProfile = JUserHelper::getProfile($userTmp->id);
							$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['User'][$numArticlesOrder]['Profile'] = $userProfile->profile;
						}
						else {
							$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['Article'][$numArticlesOrder] = $articlesOrder['Article'];
							$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['ArticlesOrder'][$numArticlesOrder] = $articlesOrder['ArticlesOrder'];
							$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['Cart'][$numArticlesOrder] = $articlesOrder['Cart'];
							$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['User'][$numArticlesOrder] = $articlesOrder['User'];
							$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrder'][$numArticlesOrder] = $totSummaryOrderSaldato;
							
							/*
							 * userprofile
							*/
							$userTmp = JFactory::getUser($articlesOrder['Cart']['user_id']);
							$userProfile = JUserHelper::getProfile($userTmp->id);
							$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['User'][$numArticlesOrder]['Profile'] = $userProfile->profile;						
						}
					
					} // loop foreach($articlesOrders as $numArticlesOrder => $articlesOrder)
				} // end if(!empty($articlesOrders)) 
			}  // end if(isset($options['articlesOrdersInOrder']) && $options['articlesOrdersInOrder']==false)

			// suppliersOrganization
			if($options['suppliers']) 
				$this->_setSuppliersOrganizations($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
			
			// suppliersOrganizationsReferents 
			if($options['referents'])
				$this->_setSuppliersOrganizationsReferent($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
			
		} // ciclo orders
	
		if($numTab==-1) {
			$this->result['Delivery'][$numDelivery]['totOrders'] = $numOrder+1;
			$this->result['Delivery'][$numDelivery]['totArticlesOrder'] = $numArticlesOrder+1;
		}
		else {
			$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['totOrders'] = $numOrder+1;
			$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['totArticlesOrder'] = $numArticlesOrder+1;
		}
	}
	
	/* 
	 * referente in "Gestisci gli acquisti aggregati"  (Carts::managementCartsGroupByUsers)
	 */
	private function _getSummaryOrderAggregates(Model $Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy = []) {
	
		$numTab=-1;
		$numOrder=-1;
		$numSummaryOrder=-1;

		App::import('Model', 'Order');
		$Order = new Order;
		
		if(isset($orderBy['Order'])) $orderLocal = $orderBy['Order'];
		else $orderLocal = 'Order.data_inizio ASC';

		$conditionsLocal = ['Order.organization_id' => $user->organization['Organization']['id'],
							 'Order.delivery_id' => $deliveryResult['Delivery']['id'],
							 'Order.isVisibleBackOffice' => 'Y'];
		if(isset($conditions['Order'])) $conditionsLocal += $conditions['Order'];

		/*
		 * prendo solo OrderState
		*/
		$Order->unbindModel(array('belongsTo' => array('Delivery','SuppliersOrganization')));
		$orderResults = $Order->find('all', ['conditions' => $conditionsLocal,
										'order' => $orderLocal,
										'recursive' => 0]);
		foreach ($orderResults as $numOrder => $order) {
	
			// order
			$this->_setOrder($Model, $numTab, $numDelivery, $numOrder, $order);

			App::import('Model', 'SummaryOrderAggregate');
			$SummaryOrderAggregate = new SummaryOrderAggregate;
				
			if(isset($orderBy['User'])) $orderLocal = $orderBy['User'];
			else $orderLocal = 'User.name ASC';
				
			$SummaryOrderAggregate->unbindModel(array('belongsTo' => array('Delivery','Order')));
			$conditionsLocal = array('SummaryOrderAggregate.organization_id' => $user->organization['Organization']['id'],
								     'SummaryOrderAggregate.order_id' => $order['Order']['id']);
			$results = $SummaryOrderAggregate->find('all',array('conditions' => $conditionsLocal,
													   'order' => $orderLocal,
													   'recursive' => 1));			
			foreach ($results as $numResult => $result) {
	
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderAggregate'][$numResult]['SummaryOrderAggregate'] = $result['SummaryOrderAggregate'];
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderAggregate'][$numResult]['User'] = $result['User'];
				
				/*
				 * userprofile
				*/
				$userTmp = JFactory::getUser($result['User']['id']);
				$userProfile = JUserHelper::getProfile($userTmp->id);
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrder'][$numResult]['User']['Profile'] = $userProfile->profile;
				
			}
				
			// suppliersOrganization
			if($options['suppliers']) 
				$this->_setSuppliersOrganizations($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
			
			// suppliersOrganizationsReferents 
			 if($options['referents'])
				$this->_setSuppliersOrganizationsReferent($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
				
		} // ciclo orders
	
		$this->result['Delivery']['totOrders'] = $numOrder+1;
		$this->result['Delivery']['totSummaryOrderAggregate'] = ((int)$numResult+1);
	}	
	
	/* 
	 * ExportDocs::admin_exportToTesoriere() 
	 * SummaryOrders::admin_index_details() 
	 */
	private function _getSummaryOrders(Model $Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy = []) {
	
		$numTab=-1;
		$numOrder=-1;
		$numSummaryOrder=-1;

		App::import('Model', 'Order');
		$Order = new Order;
		
		if(isset($orderBy['Order'])) $orderLocal = $orderBy['Order'];
		else $orderLocal = 'Order.data_inizio ASC';

		$conditionsLocal = array('Order.organization_id' => $user->organization['Organization']['id'],
								 'Order.delivery_id' => $deliveryResult['Delivery']['id'],
								 'Order.isVisibleBackOffice' => 'Y');
		if(isset($conditions['Order'])) $conditionsLocal += $conditions['Order'];

		/*
		 * prendo solo OrderState
		*/
		$Order->unbindModel(array('belongsTo' => array('Delivery','SuppliersOrganization')));
		$orderResults = $Order->find('all',array('conditions' => $conditionsLocal,
									'order' => $orderLocal,
									'recursive' => 0));
		foreach ($orderResults as $numOrder => $order) {
	
			// order
			$this->_setOrder($Model, $numTab, $numDelivery, $numOrder, $order);

			App::import('Model', 'SummaryOrder');
			$SummaryOrder = new SummaryOrder;
				
			if(isset($orderBy['User'])) $orderLocal = $orderBy['User'];
			else $orderLocal = 'User.name ASC';
				
			$SummaryOrder->unbindModel(array('belongsTo' => array('Delivery','Order')));
			$conditionsLocal = array('SummaryOrder.organization_id' => $user->organization['Organization']['id'],
								     'SummaryOrder.order_id' => $order['Order']['id']);
			$results = $SummaryOrder->find('all',array('conditions' => $conditionsLocal,
													   'order' => $orderLocal,
													   'recursive' => 1));
			
			foreach ($results as $numSummaryOrder => $result) {
	
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrder'][$numSummaryOrder]['SummaryOrder'] = $result['SummaryOrder'];
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrder'][$numSummaryOrder]['User'] = $result['User'];
				
				/*
				 * userprofile
				*/
				$userTmp = JFactory::getUser($result['User']['id']);
				$userProfile = JUserHelper::getProfile($userTmp->id);
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrder'][$numSummaryOrder]['User']['Profile'] = $userProfile->profile;
				
			}
				
			// suppliersOrganization
			if($options['suppliers']) 
				$this->_setSuppliersOrganizations($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
			
			// suppliersOrganizationsReferents 
			 if($options['referents'])
				$this->_setSuppliersOrganizationsReferent($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
				
		} // ciclo orders
	
		$this->result['Delivery']['totOrders'] = $numOrder+1;
		$this->result['Delivery']['totSummaryOrder'] = $numSummaryOrder+1;
	}
	
	/* 
	 * Ajax::admin_box_trasport() 
	 */
	private function _getSummaryOrderTrasports(Model $Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy = []) {
	
		$numTab=-1;
		$numOrder=-1;
		$numSummaryOrder=-1;

		App::import('Model', 'Order');
		$Order = new Order;
		
		if(isset($orderBy['Order'])) $orderLocal = $orderBy['Order'];
		else $orderLocal = 'Order.data_inizio ASC';

		$conditionsLocal = array('Order.organization_id' => $user->organization['Organization']['id'],
								 'Order.delivery_id' => $deliveryResult['Delivery']['id'],
								 'Order.isVisibleBackOffice' => 'Y');
		if(isset($conditions['Order'])) $conditionsLocal += $conditions['Order'];

		/*
		 * prendo solo OrderState
		*/
		$Order->unbindModel(array('belongsTo' => array('Delivery','SuppliersOrganization')));
		$orderResults = $Order->find('all',array('conditions' => $conditionsLocal,
									'order' => $orderLocal,
									'recursive' => 0));
		
		foreach ($orderResults as $numOrder => $order) {
	
			// order
			$this->_setOrder($Model, $numTab, $numDelivery, $numOrder, $order);

			App::import('Model', 'SummaryOrderTrasport');
			$SummaryOrderTrasport = new SummaryOrderTrasport;
				
			if(isset($orderBy['User'])) $orderLocal = $orderBy['User'];
			else $orderLocal = 'User.name ASC';
				
			$SummaryOrderTrasport->unbindModel(array('belongsTo' => array('Delivery','Order')));
			$conditionsLocal = array('SummaryOrderTrasport.organization_id' => $user->organization['Organization']['id'],
								     'SummaryOrderTrasport.order_id' => $order['Order']['id']);
			$results = $SummaryOrderTrasport->find('all',array('conditions' => $conditionsLocal,
													   'order' => $orderLocal,
													   'recursive' => 1));
			
			foreach ($results as $numSummaryOrderTrasport => $result) {
	
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderTrasport'][$numSummaryOrderTrasport]['SummaryOrderTrasport'] = $result['SummaryOrderTrasport'];
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderTrasport'][$numSummaryOrderTrasport]['User'] = $result['User'];
				
				/*
				 * userprofile
				*/
				$userTmp = JFactory::getUser($result['User']['id']);
				$userProfile = JUserHelper::getProfile($userTmp->id);
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderTrasport'][$numSummaryOrderTrasport]['User']['Profile'] = $userProfile->profile;
				
			}
				
			// suppliersOrganization
			if($options['suppliers']) 
				$this->_setSuppliersOrganizations($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
			
			// suppliersOrganizationsReferents 
			 if($options['referents'])
				$this->_setSuppliersOrganizationsReferent($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
				
		} // ciclo orders
	
		$this->result['Delivery']['totOrders'] = $numOrder+1;
		$this->result['Delivery']['totSummaryOrderTrasport'] = $numSummaryOrderTrasport+1;
	}
	
	/*
	 * Ajax::admin_box_cost_more()
	*/
	private function _getSummaryOrderCostMores(Model $Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy = []) {
	
		$numTab=-1;
		$numOrder=-1;
		$numSummaryOrder=-1;
	
		App::import('Model', 'Order');
		$Order = new Order;
	
		if(isset($orderBy['Order'])) $orderLocal = $orderBy['Order'];
		else $orderLocal = 'Order.data_inizio ASC';
	
		$conditionsLocal = array('Order.organization_id' => $user->organization['Organization']['id'],
								'Order.delivery_id' => $deliveryResult['Delivery']['id'],
								'Order.isVisibleBackOffice' => 'Y');
		if(isset($conditions['Order'])) $conditionsLocal += $conditions['Order'];
	
		/*
		 * prendo solo OrderState
		*/
		$Order->unbindModel(array('belongsTo' => array('Delivery','SuppliersOrganization')));
		$orderResults = $Order->find('all',array('conditions' => $conditionsLocal,
												'order' => $orderLocal,
												'recursive' => 0));
	
		foreach ($orderResults as $numOrder => $order) {
	
			// order
			$this->_setOrder($Model, $numTab, $numDelivery, $numOrder, $order);
	
			App::import('Model', 'SummaryOrderCostMore');
			$SummaryOrderCostMore = new SummaryOrderCostMore;
	
			if(isset($orderBy['User'])) $orderLocal = $orderBy['User'];
			else $orderLocal = 'User.name ASC';
	
			$SummaryOrderCostMore->unbindModel(array('belongsTo' => array('Delivery','Order')));
			$conditionsLocal = array('SummaryOrderCostMore.organization_id' => $user->organization['Organization']['id'],
					'SummaryOrderCostMore.order_id' => $order['Order']['id']);
			$results = $SummaryOrderCostMore->find('all',array('conditions' => $conditionsLocal,
																'order' => $orderLocal,
																'recursive' => 1));
				
			foreach ($results as $numSummaryOrderCostMore => $result) {
	
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderCostMore'][$numSummaryOrderCostMore]['SummaryOrderCostMore'] = $result['SummaryOrderCostMore'];
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderCostMore'][$numSummaryOrderCostMore]['User'] = $result['User'];
	
				/*
				 * userprofile
				*/
				$userTmp = JFactory::getUser($result['User']['id']);
				$userProfile = JUserHelper::getProfile($userTmp->id);
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderCostMore'][$numSummaryOrderCostMore]['User']['Profile'] = $userProfile->profile;
	
			}
	
			// suppliersOrganization
			if($options['suppliers'])
				$this->_setSuppliersOrganizations($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
				
			// suppliersOrganizationsReferents
			if($options['referents'])
				$this->_setSuppliersOrganizationsReferent($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
	
		} // ciclo orders
	
		$this->result['Delivery']['totOrders'] = $numOrder+1;
		$this->result['Delivery']['totSummaryOrderCostMore'] = $numSummaryOrderCostMore+1;
	}
	
	/*
	 * Ajax::admin_box_cost_less()
	*/
	private function _getSummaryOrderCostLess(Model $Model, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy = []) {
	
		$numTab=-1;
		$numOrder=-1;
		$numSummaryOrder=-1;
	
		App::import('Model', 'Order');
		$Order = new Order;
	
		if(isset($orderBy['Order'])) $orderLocal = $orderBy['Order'];
		else $orderLocal = 'Order.data_inizio ASC';
	
		$conditionsLocal = array('Order.organization_id' => $user->organization['Organization']['id'],
				'Order.delivery_id' => $deliveryResult['Delivery']['id'],
				'Order.isVisibleBackOffice' => 'Y');
		if(isset($conditions['Order'])) $conditionsLocal += $conditions['Order'];
	
		/*
		 * prendo solo OrderState
		*/
		$Order->unbindModel(array('belongsTo' => array('Delivery','SuppliersOrganization')));
		$orderResults = $Order->find('all',array('conditions' => $conditionsLocal,
				'order' => $orderLocal,
				'recursive' => 0));
	
		foreach ($orderResults as $numOrder => $order) {
	
			// order
			$this->_setOrder($Model, $numTab, $numDelivery, $numOrder, $order);
	
			App::import('Model', 'SummaryOrderCostLess');
			$SummaryOrderCostLess = new SummaryOrderCostLess;
	
			if(isset($orderBy['User'])) $orderLocal = $orderBy['User'];
			else $orderLocal = 'User.name ASC';
	
			$SummaryOrderCostLess->unbindModel(array('belongsTo' => array('Delivery','Order')));
			$conditionsLocal = array('SummaryOrderCostLess.organization_id' => $user->organization['Organization']['id'],
					'SummaryOrderCostLess.order_id' => $order['Order']['id']);
			$results = $SummaryOrderCostLess->find('all',array('conditions' => $conditionsLocal,
					'order' => $orderLocal,
					'recursive' => 1));
	
			foreach ($results as $numSummaryOrderCostLess => $result) {
	
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderCostLess'][$numSummaryOrderCostLess]['SummaryOrderCostLess'] = $result['SummaryOrderCostLess'];
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderCostLess'][$numSummaryOrderCostLess]['User'] = $result['User'];
	
				/*
				 * userprofile
				*/
				$userTmp = JFactory::getUser($result['User']['id']);
				$userProfile = JUserHelper::getProfile($userTmp->id);
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SummaryOrderCostLess'][$numSummaryOrderCostLess]['User']['Profile'] = $userProfile->profile;
	
			}
	
			// suppliersOrganization
			if($options['suppliers'])
				$this->_setSuppliersOrganizations($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
	
			// suppliersOrganizationsReferents
			if($options['referents'])
				$this->_setSuppliersOrganizationsReferent($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy);
	
		} // ciclo orders
	
		$this->result['Delivery']['totOrders'] = $numOrder+1;
		$this->result['Delivery']['totSummaryOrderCostLess'] = $numSummaryOrderCostLess+1;
	}
	
	/* 
	 * di un delivery, prendo tutti gli articoli in dispensa
	 */
	private function _getStorerooms(Model $Model, $numTab, $numDelivery, $deliveryResult, $user, $conditions, $options, $orderBy = []) {
		
		$numStoreroom=-1; 
		
		App::import('Model', 'Storeroom');
		$Storeroom = new Storeroom;

		if(isset($orderBy['Storeroom'])) $orderLocal = $orderBy['Storeroom'];
		else $orderLocal = 'Article.name ASC';

		$conditionsLocal = array('Storeroom.organization_id' => $user->organization['Organization']['id'],
								 'Storeroom.delivery_id' => $deliveryResult['Delivery']['id']);
		$conditionsLocal += $conditions['Storeroom'];
		$Storeroom->unbindModel(array('belongsTo' => array('User','Delivery')));
		$storeroomResults = $Storeroom->find('all', array('conditions' => $conditionsLocal,
														  'order' => $orderLocal,
														  'recursive' => 0));

		foreach ($storeroomResults as $numStoreroom => $storeroom) {
			// storeroom
			if($numTab==-1)
				$this->result['Delivery'][$numDelivery]['Storeroom'][$numStoreroom] = $storeroom['Storeroom'];
			else
				$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Storeroom'][$numStoreroom] = $storeroom['Storeroom'];
			
			// articles
			if($numTab==-1) 
				$this->result['Delivery'][$numDelivery]['Storeroom'][$numStoreroom]['Article'] = $storeroom['Article'];
			else 
				$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Storeroom'][$numStoreroom]['Article'] = $storeroom['Article'];
			
			// suppliersOrganization
			if($options['suppliers']) {
				$conditionsLocal = array('SuppliersOrganization.id' => $storeroom['Article']['supplier_organization_id']);
				$suppliersOrganizationResults = $this->_getSuppliersOrganizations($user, $conditionsLocal, $orderBy);
				if($numTab==-1)
					$this->result['Delivery'][$numDelivery]['Storeroom'][$numStoreroom]['SuppliersOrganization'] = $suppliersOrganizationResults; 
				else 
					$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Storeroom'][$numStoreroom]['SuppliersOrganization'] = $suppliersOrganizationResults;
			}
			
			// suppliersOrganizationsReferents
			if($options['referents']) {
				$conditionsLocal = array('SuppliersOrganization.id' => $storeroom['Article']['supplier_organization_id']);
				$suppliersOrganizationsReferents = $this->_getSuppliersOrganizationsReferents($user, $conditionsLocal, $orderBy);
				foreach ($suppliersOrganizationsReferents as $numReferent => $suppliersOrganizationsReferent) {
					if($numTab==-1)
						$this->result['Delivery'][$numDelivery]['Storeroom'][$numStoreroom]['SuppliersOrganizationsReferent'][$numReferent] = $suppliersOrganizationsReferent;
					else 
						$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Storeroom'][$numStoreroom]['SuppliersOrganizationsReferent'][$numReferent] = $suppliersOrganizationsReferent;
				}
			}			
		}

		if($numTab==-1)
			$this->result['Delivery'][$numDelivery]['totStorerooms'] = $numStoreroom+1;
		else
			$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['totStorerooms'] = $numStoreroom+1;
	}
	
	/*
	 * estrae le date delle consegne per i Tabs per le promozioni
	 * */
	public function getTabsToDeliveriesGasProdPromotionsData(Model $Model, $user, $conditions=null, $orderBy = []) {
	
		$conditions += ['Delivery.organization_id' => $user->organization['Organization']['id']];
		if(isset($orderBy['Delivery'])) $orderLocal = $orderBy['Delivery'];
		else $orderLocal = 'data ASC';
		
		//$conditions += [];
		
		$options = [];
		$options['fields'] = ['Delivery.data'];
		$options['conditions'] = $conditions;
		$options['order'] = $orderLocal;
		$options['group'] = ['Delivery.data'];
		$options['recursive'] = -1;
		
		$results = $Model->find('all', $options);
		
		/*
		 * estragggo solo le consegne con ordini in promozione
		 */
		if(!empty($results)) {

			App::import('Model', 'Order');
			$Order = new Order;
					
			foreach($results as $numResult => $result) {
				$options = [];
				$options['conditions'] = ['Delivery.organization_id' => $user->organization['Organization']['id'],
										  'Delivery.data' => $result['Delivery']['data'],
										  'Order.prod_gas_promotion_id != ' => 0,
										  'Order.organization_id' => $user->organization['Organization']['id']];
				$options['recursive'] = 0;
				$ordersResults = $Order->find('all', $options);
				if(empty($ordersResults))	
					unset($results[$numResult]);
			}
		}
		
		return $results;
	}
	
		/*
	 * estrae le date delle consegne per i Tabs
	 * */
	public function getTabsToDeliveriesData(Model $Model, $user, $conditions=null, $orderBy = []) {
	
		$conditions += ['Delivery.organization_id' => $user->organization['Organization']['id']];
		if(isset($orderBy['Delivery'])) $orderLocal = $orderBy['Delivery'];
		else $orderLocal = 'data ASC';
		
		$options = [];
		$options['fields'] = ['data'];
		$options['conditions'] = $conditions;
		$options['order'] = $orderLocal;
		$options['group'] = ['data'];
		$options['recursive'] = -1;
		
		$results = $Model->find('all', $options);
		return $results;
	}
	
	private function _getDeliveries(Model $Model, $user, $conditions, $orderBy = []) {

		$conditions = $conditions['Delivery'];
		$conditions += array('Delivery.organization_id' => $user->organization['Organization']['id']);		
		if(isset($orderBy['Delivery'])) $orderLocal = $orderBy['Delivery'];
		else $orderLocal = 'data ASC'; 
		$results = $Model->find('all',array('conditions' => $conditions,
											'order' => $orderLocal,
											'recursive' => -1));			
		return $results;
	}	
	
	private function _getSuppliersOrganizations($user, $conditions, $orderBy = []) {

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;

		$results = $SuppliersOrganization->getSuppliersOrganization($user, $conditions, $orderBy);

		if(count($results)==1) {
			$newResults = [];
			$results = current($results);
			$newResults = $results['Supplier'];
			$newResults['id'] = $results['SuppliersOrganization']['id'];
			$newResults['name'] = $results['SuppliersOrganization']['name'];
			$newResults['frequenza'] = $results['SuppliersOrganization']['frequenza'];
			$newResults['img1'] = $results['Supplier']['img1'];
			$newResults['supplier_id'] = $results['Supplier']['id'];
			$newResults['j_content_id'] = $results['Supplier']['j_content_id'];
			$newResults['j_catid'] = $results['Content']['catid'];
			$newResults['isSupplierOrganizationCashExcluded'] = $results['SuppliersOrganization']['isSupplierOrganizationCashExcluded'];

			$results = $newResults;
		}
			
		return $results;
	}

	private function _getSuppliersOrganizationsReferents($user, $conditions, $orderBy = []) {
		
		if(isset($orderBy['SuppliersOrganizationsReferent'])) $orderLocal = $orderBy['SuppliersOrganizationsReferent'];
		else $orderLocal = Configure::read('orderUser');
		
		App::import('Model', 'SuppliersOrganizationsReferent');
		$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
		
		$results = $SuppliersOrganizationsReferent->getReferentsCompact($user, $conditions, $orderLocal);
		
		return $results;
	}
	
	private function _setOrder(Model $Model, $numTab, $numDelivery, $numOrder, $order) {
		if($numTab==-1) 
			$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['Order'] = $order['Order'];
		else 
			$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['Order'] = $order['Order'];
	}
	
	private function _setSuppliersOrganizations($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy) {
		$conditionsLocal = array('SuppliersOrganization.id' => $order['Order']['supplier_organization_id']);
		if(isset($conditions['SuppliersOrganization']))
			$conditionsLocal += $conditions['SuppliersOrganization'];
		$suppliersOrganizationResuls = $this->_getSuppliersOrganizations($user, $conditionsLocal, $orderBy);
	
		if($numTab==-1)
			$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SuppliersOrganization'] = $suppliersOrganizationResuls;
		else
			$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['SuppliersOrganization'] = $suppliersOrganizationResuls;
	}
	
	private function _setSuppliersOrganizationsReferent($numTab, $numDelivery, $numOrder, $order, $user, $conditions, $options, $orderBy)	{
		$suppliersOrganizationsReferents = null;
		$conditionsLocal = array('SuppliersOrganization.id' => $order['Order']['supplier_organization_id']);
		$suppliersOrganizationsReferents = $this->_getSuppliersOrganizationsReferents($user, $conditionsLocal, $orderBy);
		if(!empty($suppliersOrganizationsReferents)) {
			foreach ($suppliersOrganizationsReferents as $numReferent => $suppliersOrganizationsReferent) {
				if($numTab==-1)
					$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SuppliersOrganizationsReferent'][$numReferent] = $suppliersOrganizationsReferent;
				else
					$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['SuppliersOrganizationsReferent'][$numReferent] = $suppliersOrganizationsReferent;
			}
		}
	
		if(empty($suppliersOrganizationsReferents)) {
			if($numTab==-1)
				$this->result['Delivery'][$numDelivery]['Order'][$numOrder]['SuppliersOrganizationsReferent'] = null;
			else
				$this->result['Tab'][$numTab]['Delivery'][$numDelivery]['Order'][$numOrder]['SuppliersOrganizationsReferent'] = null;
		}
	}
	
	/*
	 * ottengo user_id che hanno gli acquisti gia' saldati SummaryOrder.saldato_a = CASSIERE / TESORIERE
	 */
	private function _getUserIdsJustSaldato($user, $order_id, $articlesOrders) {
		
		$user_ids = [];
		
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
				
		/*
		 * estraggo gli user_id che hanno effettuato acquisti su almeno un articolo dell'ordine
		 */
		foreach($articlesOrders as $numArticlesOrder => $articlesOrder) {
			if(isset($articlesOrder['Cart']['user_id'])) // se l'articolo e' stato acquistato
				$user_ids[$articlesOrder['Cart']['user_id']] = $articlesOrder['Cart']['user_id'];
		}	
		/*
		echo "<pre>_getUserIdsJustSaldato \n";
		print_r($user_ids);
		echo "</pre>";	
		*/		
		if(!empty($user_ids)) {
			foreach($user_ids as $user_id) {
				$ops = [];
				$opts['conditions'] = ['SummaryOrder.organization_id' => $user->organization['Organization']['id'],
										 'SummaryOrder.order_id' => $order_id,
										 'SummaryOrder.user_id' => $user_id,
										 'SummaryOrder.saldato_a !=' => null];
				$totSummaryOrderSaldato = $SummaryOrder->find('count', $opts);
				/*
				echo "<pre>";
				print_r($options);
				print_r($totSummaryOrderSaldato);
				echo "</pre>";
				*/	
	
				if($totSummaryOrderSaldato==0)
					unset($user_ids[$user_id]);
			}
			
		}
		
		return $user_ids;
	}	
}	
?>