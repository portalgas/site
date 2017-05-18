<?php 
App::uses('Controller', 'Controller');

class RoutingsComponent extends Component {
	
	public $components = array('ActionsDesOrder', 'Session'); 
	
	public function fromOrderAddToArticlesOrderAdd($user, $order_id, $opt=[], $debug = false) {
	
		$esito = true;
		$msg = "";
		$go = "";
		$url = Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=";
        $App = new AppController();
		
		if($debug) echo "<br />RoutingsComponent::fromOrderToArticlesOrder() - order_id ".$order_id;
			
		App::import('Model', 'Order');
		$Order = new Order();
		
		$Order->unbindModel(array('belongsTo' => array('Delivery')));
		
		$options = array();
		$options['conditions'] = array('Order.organization_id' => $user->organization['Organization']['id'],
										'Order.id' => $order_id);
		$options['fields'] = ['Order.id, Order.delivery_id, Order.des_order_id', 'SuppliersOrganization.owner_articles'];
		$options['recursive'] = 0;
		$results = $Order->find('first', $options);

		if($debug) {
			echo "<pre>RoutingsComponent::fromOrderToArticlesOrder() \n";
			print_r($results);
			echo "</pre>";		
		}
		
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
			} 
			else				
			if($isTitolareDesSupplier) {
				$msg = __('The order has been saved. DES association Articles Titolare');
				$go = "articlesorders_add";
			}
			else {
				$msg = __('The order has been saved. DES association Articles NOT Titolare');
				$go = "articlesorders_index_only_read_des";
			}
		}	
		else {
			/*
			 * Not DES
			 */
			if($results['SuppliersOrganization']['owner_articles']=='SUPPLIER') {
				$msg = __('The order has been saved. ProdGas association Articles');
				$go = "articlesorders_add_prod_gas";	
			} 
			else
			if(isset($opt['force_articlesorders_add_hidden']) || !$App->isUserPermissionArticlesOrder($user)) {
				$msg = __('The order has been saved. Hidden association Articles');		
				$go = 'articlesorders_add_hidden';
			}
			else {		 
				$msg = __('The order has been saved. Now association Articles');
				$go = "articlesorders_add";
			}
		}

		switch ($go) {
			case "articlesorders_add";
				$url .= "ArticlesOrders&action=add&order_id=$order_id&des_order_id=$des_order_id";			
			break;
			case "articlesorders_add_hidden";
				$url .= "ArticlesOrders&action=add_hidden&order_id=$order_id";			
			break;
			case "articlesorders_add_prod_gas";
				$url .= "ArticlesOrders&action=add_prod_gas&order_id=$order_id&des_order_id=$des_order_id";			
			break;
			case "articlesorders_index_only_read_des";
				$url .= "ArticlesOrders&action=index_only_read_des&delivery_id=$delivery_id&order_id=$order_id";			
			break;
			default:
				$esito = false;
			break;
		}
		
		if($debug) {
			echo "<pre>";
			print_r($msg);
			echo "</pre>";
			echo "<pre>";
			print_r($url);
			echo "</pre>";
			exit;
		}
		
		if($esito) {
			$this->Session->setFlash($msg);
			if(!$debug) $App->myRedirect($url);
		}
	}		
}
?>