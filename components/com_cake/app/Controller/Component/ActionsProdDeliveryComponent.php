<?php 
App::uses('Component', 'Controller');

/*
 * setting possibili action in base allo stato (prod_delivery_id) di 
 * 		Organization.type = PROD
 */
class ActionsProdDeliveryComponent extends Component {

	private $debug = false;
    private $Controller = null;

    public function initialize(Controller $controller) 
    {
		$this->Controller = $controller;
    }
	
	public function writeCache() {

		$actionsProdDelivery = [];
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
				$actionsProdDelivery['ProdDeliveryDelete']['id'] = 'ProdDeliveryDelete';
		$actionsProdDelivery['ProdDeliveryDelete']['label'] = __('Delete');		$actionsProdDelivery['ProdDeliveryDelete']['controller'] = 'ProdDeliveries';		$actionsProdDelivery['ProdDeliveryDelete']['action'] = 'delete';		$actionsProdDelivery['ProdDeliveryDelete']['css_class'] = 'actionDelete';		
		/*
		 * ProdDeliveriesArticle
		 */
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['id'] = 'ProdDeliveriesArticleEdit';
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['label'] = __('Edit ProdDeliveriesArticle Short');
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['controller'] = 'ProdDeliveriesArticles';
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['action'] = 'index';
		$actionsProdDelivery['ProdDeliveriesArticleEdit']['css_class'] = 'actionEditCart';				$actionsProdDelivery['ProdCartsManagementOne']['id'] = 'ProdCartsManagementOne';
		$actionsProdDelivery['ProdCartsManagementOne']['label'] = __('Management Carts One Short');		$actionsProdDelivery['ProdCartsManagementOne']['controller'] = 'ProdCarts';		$actionsProdDelivery['ProdCartsManagementOne']['action'] = 'managementCartsOne';		$actionsProdDelivery['ProdCartsManagementOne']['css_class'] = 'actionEditDbOne';

		$actionsProdDelivery['ProdCartsManagementSplit']['id'] = 'ProdCartsManagementSplit';
		$actionsProdDelivery['ProdCartsManagementSplit']['label'] = __('Management Carts Split Short');  // suddivide la qta di ogni acquisto
		$actionsProdDelivery['ProdCartsManagementSplit']['controller'] = 'ProdCarts';
		$actionsProdDelivery['ProdCartsManagementSplit']['action'] = 'managementCartsSplit';
		$actionsProdDelivery['ProdCartsManagementSplit']['css_class'] = 'actionEditDbSplit';		$actionsProdDelivery['ProdExportDoc']['id'] = 'ProdDoc';		$actionsProdDelivery['ProdExportDoc']['label'] = __('Print ProdDoc');		$actionsProdDelivery['ProdExportDoc']['controller'] = 'ProdDocs';		$actionsProdDelivery['ProdExportDoc']['action'] = 'produttoreDocsExport';		$actionsProdDelivery['ProdExportDoc']['css_class'] = 'actionPrinter';		$actionsProdDelivery['ProdExportDocHistory']['id'] = 'ProdDocHistory';		$actionsProdDelivery['ProdExportDocHistory']['label'] = __('Print ProdDoc');		$actionsProdDelivery['ProdExportDocHistory']['controller'] = 'ProdExportDocs';		$actionsProdDelivery['ProdExportDocHistory']['action'] = 'produttoreDocsExportHistory';		$actionsProdDelivery['ProdExportDocHistory']['css_class'] = 'actionPrinter';		 		$actionsProdDelivery['ProdRequestPayment']['id'] = 'ProdRequestPayment';		$actionsProdDelivery['ProdRequestPayment']['label'] = "Richiesta di pagamento";		$actionsProdDelivery['ProdRequestPayment']['controller'] = 'ProdRequestPayments';		$actionsProdDelivery['ProdRequestPayment']['action'] = 'view';		$actionsProdDelivery['ProdRequestPayment']['css_class'] = 'actionPay';							$actionsProdDeliveries = [];
				$i=0;		$actionsProdDeliveries[Configure::read('CREATE-INCOMPLETE')][$i]['label'] = __('Add ProdDeliveriesArticle Error');		$actionsProdDeliveries[Configure::read('CREATE-INCOMPLETE')][$i]['controller'] = 'ProdDeliveriesArticles';		$actionsProdDeliveries[Configure::read('CREATE-INCOMPLETE')][$i]['action'] = 'add';		$actionsProdDeliveries[Configure::read('CREATE-INCOMPLETE')][$i]['css_class'] = 'actionEditCart';		$i++;		$actionsProdDeliveries[Configure::read('CREATE-INCOMPLETE')][$i] = $actionsProdDelivery['ProdDeliveryEdit'];		$i++;		$actionsProdDeliveries[Configure::read('CREATE-INCOMPLETE')][$i] = $actionsProdDelivery['ProdDeliveryDelete'];		 		$i=0;
		$actionsProdDeliveries[Configure::read('OPEN-NEXT')][$i] = $actionsProdDelivery['ProdDeliveriesArticleEdit'];
		$i++;		$actionsProdDeliveries[Configure::read('OPEN-NEXT')][$i] = $actionsProdDelivery['ProdDeliveryEdit'];		$i++;		$actionsProdDeliveries[Configure::read('OPEN-NEXT')][$i] = $actionsProdDelivery['ProdDeliveryDelete'];		 		$i=0;		$actionsProdDeliveries[Configure::read('OPEN')][$i] = $actionsProdDelivery['ProdDeliveryEdit'];		$i++;
		$actionsProdDeliveries[Configure::read('OPEN')][$i] = $actionsProdDelivery['ProdDeliveriesArticleEdit'];
		$i++;		$actionsProdDeliveries[Configure::read('OPEN')][$i] = $actionsProdDelivery['ProdDeliveryDelete'];		$i++;		$actionsProdDeliveries[Configure::read('OPEN')][$i] = $actionsProdDelivery['ProdCartsManagementOne'];		$actionsProdDeliveries[Configure::read('OPEN')][$i]['label'] = __('Management Carts One Short');		$i++;		$actionsProdDeliveries[Configure::read('OPEN')][$i] = $actionsProdDelivery['ProdExportDoc'];		$actionsProdDeliveries[Configure::read('OPEN')][$i]['label'] = __('Print ProdDelivery');		
		/*
		 * PROCESSED-BEFORE-DELIVERY
		 */		$i=0;		$actionsProdDeliveries[Configure::read('PROCESSED-BEFORE-DELIVERY')][$i] = $actionsProdDelivery['ProdDeliveryEdit'];		$i++;		$actionsProdDeliveries[Configure::read('PROCESSED-BEFORE-DELIVERY')][$i] = $actionsProdDelivery['ProdDeliveriesArticleEdit'];		$i++;		$actionsProdDeliveries[Configure::read('PROCESSED-BEFORE-DELIVERY')][$i] = $actionsProdDelivery['ProdDeliveryDelete'];		$i++;		$actionsProdDeliveries[Configure::read('PROCESSED-BEFORE-DELIVERY')][$i] = $actionsProdDelivery['ProdCartsManagementOne'];
		$i++;
		$actionsProdDeliveries[Configure::read('PROCESSED-BEFORE-DELIVERY')][$i] = $actionsProdDelivery['ProdCartsManagementSplit'];		$i++;		$actionsProdDeliveries[Configure::read('PROCESSED-BEFORE-DELIVERY')][$i] = $actionsProdDelivery['ProdExportDoc'];		
		/*
		 * PROCESSED-POST-DELIVERY
		 */
		$i=0;		$actionsProdDeliveries[Configure::read('PROCESSED-POST-DELIVERY')][$i] = $actionsProdDelivery['ProdDeliveryEdit'];		$i++;		$actionsProdDeliveries[Configure::read('PROCESSED-POST-DELIVERY')][$i] = $actionsProdDelivery['ProdDeliveriesArticleEdit'];		$i++;		$actionsProdDeliveries[Configure::read('PROCESSED-POST-DELIVERY')][$i] = $actionsProdDelivery['ProdDeliveryDelete'];
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
		$actionsProdDeliveries[Configure::read('CLOSE')][$i] = $actionsProdDelivery['ProdExportDocHistory'];		$i++;
		$actionsProdDeliveries[Configure::read('CLOSE')][$i] = $actionsProdDelivery['ProdRequestPayment'];				Cache::write('actionsProdDeliveries', $actionsProdDeliveries);
	}

	public function draw_sotto_menu($actionsProdDeliveries, $prod_delivery_id, $pageCurrent=null) {			$actionsProdDeliveriesNew = [];		$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';
		$RoutingPrefixes = Configure::read('Routing.prefixes');		
		for($i=0; $i < count($actionsProdDeliveries); $i++) {					/*
			 * pagina corrente, la rendo non cliccabile
			 */
			if(!empty($pageCurrent) && $pageCurrent['controller'] == strtolower($actionsProdDeliveries[$i]['controller']) && $pageCurrent['action'] == strtolower($actionsProdDeliveries[$i]['action'])) {
				$actionsProdDeliveries[$i]['url'] = ''; 
				$actionsProdDeliveriesNew[] = $actionsProdDeliveries[$i];			}
			else {				$actionsProdDeliveries[$i]['url'] = $urlBase.'controller='.$actionsProdDeliveries[$i]['controller'].'&action='.$actionsProdDeliveries[$i]['action'].'&prod_delivery_id='.$prod_delivery_id;				$actionsProdDeliveriesNew[] = $actionsProdDeliveries[$i];									}		} // end for		
		return $actionsProdDeliveriesNew;
	}
}
?>