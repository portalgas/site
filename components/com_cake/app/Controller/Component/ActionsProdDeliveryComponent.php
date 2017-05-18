<?php 
/*
 * setting possibili action in base allo stato (prod_delivery_id) di 
 * 		Organization.type = PROD
 */
class ActionsProdDeliveryComponent extends Component {

	private $debug = false;
	
	public function writeCache() {

		$actionsProdDelivery = array();
		$actionsProdDelivery['ProdDeliveryView']['id'] = 'ProdDeliveryView';  // consegna in solo lettura
		$actionsProdDelivery['ProdDeliveryView']['label'] = "Visualizza la consegna";
		$actionsProdDelivery['ProdDeliveryView']['controller'] = 'ProdDeliveries';
		$actionsProdDelivery['ProdDeliveryView']['action'] = 'view';
		$actionsProdDelivery['ProdDeliveryView']['css_class'] = 'actionView';

		$actionsProdDelivery['ProdDeliveryEdit']['id'] = 'ProdDeliveryEdit';
		$actionsProdDelivery['ProdDeliveryEdit']['label'] = __('Edit ProdDelivery');
		$actionsProdDelivery['ProdDeliveryEdit']['controller'] = 'ProdDeliveries';
		$actionsProdDelivery['ProdDeliveryEdit']['action'] = 'edit';
		$actionsProdDelivery['ProdDeliveryEdit']['css_class'] = 'actionEdit';
		
		$actionsProdDelivery['ProdDeliveryDelete']['label'] = __('Delete');
		/*
		 * ProdDeliveriesArticle
		 */
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['id'] = 'ProdDeliveriesArticleEdit';
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['label'] = __('Edit ProdDeliveriesArticle Short');
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['controller'] = 'ProdDeliveriesArticles';
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['action'] = 'index';
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['css_class'] = 'actionEditCart';
		$actionsProdDelivery['ProdCartsManagementOne']['label'] = __('Management Carts One Short');

		$actionsProdDelivery['ProdCartsManagementSplit']['id'] = 'ProdCartsManagementSplit';
		$actionsProdDelivery['ProdCartsManagementSplit']['label'] = __('Management Carts Split Short');  // suddivide la qta di ogni acquisto
		$actionsProdDelivery['ProdCartsManagementSplit']['controller'] = 'ProdCarts';
		$actionsProdDelivery['ProdCartsManagementSplit']['action'] = 'managementCartsSplit';
		$actionsProdDelivery['ProdCartsManagementSplit']['css_class'] = 'actionEditDbSplit';
		
		$actionsProdDeliveries[Configure::read('OPEN-NEXT')][$i] = $actionsProdDelivery['ProdDeliveriesArticleEdit'];
		$i++;
		$actionsProdDeliveries[Configure::read('OPEN')][$i] = $actionsProdDelivery['ProdDeliveriesArticleEdit'];
		$i++;
		/*
		 * PROCESSED-BEFORE-DELIVERY
		 */
		$i++;
		$actionsProdDeliveries[Configure::read('PROCESSED-BEFORE-DELIVERY')][$i] = $actionsProdDelivery['ProdCartsManagementSplit'];
		/*
		 * PROCESSED-POST-DELIVERY
		 */
		$i=0;
		$i++;
		$actionsProdDeliveries[Configure::read('PROCESSED-POST-DELIVERY')][$i] = $actionsProdDelivery['ProdCartsManagementOne'];
		$i++;
		$actionsProdDeliveries[Configure::read('PROCESSED-POST-DELIVERY')][$i] = $actionsProdDelivery['ProdCartsManagementSplit'];
		$i++;
		$actionsProdDeliveries[Configure::read('PROCESSED-POST-DELIVERY')][$i] = $actionsProdDelivery['ProdExportDoc'];
				
		$i=0;
		$actionsProdDeliveries[Configure::read('TO-PAYMENT')][$i] = $actionsProdDelivery['ProdDeliveryView'];
		$i++;
		$actionsProdDeliveries[Configure::read('TO-PAYMENT')][$i] = $actionsProdDelivery['ProdExportDoc'];
		$i=0;
		$actionsProdDeliveries[Configure::read('CLOSE')][$i] = $actionsProdDelivery['ProdDeliveryView'];
		$i++;
		$actionsProdDeliveries[Configure::read('CLOSE')][$i] = $actionsProdDelivery['ProdExportDocHistory'];
		$actionsProdDeliveries[Configure::read('CLOSE')][$i] = $actionsProdDelivery['ProdRequestPayment'];
	}

	public function draw_sotto_menu($actionsProdDeliveries, $prod_delivery_id, $pageCurrent=null) {
		$RoutingPrefixes = Configure::read('Routing.prefixes');
		for($i=0; $i < count($actionsProdDeliveries); $i++) {
			 * pagina corrente, la rendo non cliccabile
			 */
			if(!empty($pageCurrent) && $pageCurrent['controller'] == strtolower($actionsProdDeliveries[$i]['controller']) && $pageCurrent['action'] == strtolower($actionsProdDeliveries[$i]['action'])) {
				$actionsProdDeliveries[$i]['url'] = ''; 
				$actionsProdDeliveriesNew[] = $actionsProdDeliveries[$i];
			else {
		return $actionsProdDeliveriesNew;
	}
}
?>