<?php 
App::uses('Controller', 'Controller');

class RoutingsComponent extends Component {
	
	public $components = array('ActionsDesOrder', 'Session'); 
	
    private $Controller = null;

    public function initialize(Controller $controller) 
    {
		$this->Controller = $controller;
    }
	
	/*
	 * ordersActions.id = 4  => ArticlesOrders::add  - articlesOwner.REFERENT or isTitolareDesSupplier = Y => canEdit
	 * ordersActions.id = 7  => ArticlesOrders::add  - articlesOwner.DES 
	 * ordersActions.id = 36 => ArticlesOrders::add  - articlesOwner.SUPPLIER 
	 * ordersActions.id = 6  => ArticlesOrders::index - elenco articoli
	 */
	public function fromOrderAddToArticlesOrderAdd($user, $order_id, $opt=[], $debug = false) {
	
		$controllerLog = $this->Controller;
	
		$esito = true;
		$msg = "";
		$go = "";
		$url = Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=";
        $App = new AppController();
		
		$controllerLog::l("RoutingsComponent::fromOrderToArticlesOrder() - order_id ".$order_id, $debug);
			
		App::import('Model', 'Order');
		$Order = new Order();
		
		$Order->unbindModel(['belongsTo' => ['Delivery']]);
		
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'], 'Order.id' => $order_id];
		$options['fields'] = ['Order.id, Order.delivery_id, Order.des_order_id', 'SuppliersOrganization.owner_articles'];
		$options['recursive'] = 0;
		$results = $Order->find('first', $options);

		$controllerLog::l($results, $debug);
		
		if(empty($results))
			return false;
			
		$des_order_id = $results['Order']['des_order_id'];
		$delivery_id = $results['Order']['delivery_id'];
				
		if($user->organization['Organization']['hasDes']=='Y' && !empty($des_order_id)) {
			/*
			 * DES
			 */
			$isTitolareDesSupplier = $this->ActionsDesOrder->isTitolareDesSupplier($user, $des_order_id);

			if($results['SuppliersOrganization']['owner_articles']=='SUPPLIER') {
				$msg = __('The order has been saved. ProdGas association Articles');
				$go = "articlesorders_add_prod_gas";	
				$go = "articlesorders_add";			
			} 
			else				
			if($isTitolareDesSupplier) {
				$msg = __('The order has been saved. DES association Articles Titolare');
				$go = "articlesorders_add";
			}
			else {
				$msg = __('The order has been saved. DES association Articles NOT Titolare');
				$go = "articlesorders_index_only_read_des";
				$go = "articlesorders_add";
			}
		}	
		else {
			/*
			 * Not DES
			 */
			if(isset($opt['force_articlesorders_add_hidden']) || !$App->isUserPermissionArticlesOrder($user)) {
				$msg = __('The order has been saved. Hidden association Articles');		
				$go = 'articlesorders_add_hidden';
			}
			else
			if($results['SuppliersOrganization']['owner_articles']=='SUPPLIER') {
				/* 
				 * articoli gestiti dal produttore
				 */
				$msg = __('The order has been saved. ProdGas association Articles');
				$go = "articlesorders_add_prod_gas";	
				$go = "articlesorders_add";	
			} 
			else
			if($results['SuppliersOrganization']['owner_articles']=='DES') {
				/* 
				 * articoli gestiti dal DES, ma non e' un ordine DES, il GAS prende il suo listino per un proprio ordine
				 */
				$msg = __('The order has been saved. DES association Articles');
				$go = "articlesorders_add";	
			} 
			else {		 
				$msg = __('The order has been saved. Now association Articles');
				$go = "articlesorders_add";
			}
		}

		switch ($go) {
			case "articlesorders_add";
				$url .= "ArticlesOrders&action=add&delivery_id=$delivery_id&order_id=$order_id&des_order_id=$des_order_id";			
			break;
			case "articlesorders_add_hidden";
				$url .= "ArticlesOrders&action=add_hidden&order_id=$order_id";			
			break;
			case "articlesorders_add_prod_gas"; // dismesso
				$url .= "ArticlesOrders&action=add_prod_gas&delivery_id=$delivery_id&order_id=$order_id";			
			break;
			case "articlesorders_index_only_read_des";
				$url .= "ArticlesOrders&action=index_only_read_des&delivery_id=$delivery_id&order_id=$order_id";			
			break;
			default:
				$esito = false;
			break;
		}
		
		$controllerLog::l([$msg,$url],$debug);
				
		if($esito) {
			$this->Session->setFlash($msg);
			if(!$debug) $App->myRedirect($url);
		}
	}		
}
?>