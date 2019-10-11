<?php 
App::uses('SuperServiceArticlesBehavior', 'Model/Behavior');

class ServiceArticlesBehavior extends SuperServiceArticlesBehavior {

	public function setup(Model $Model, $settings = []) {	
		parent::setup($Model, $settings);
	}

	/*
	 * elenco articoli validi e non ancora associati all'ordine partendo dal produttore (quando sono in add con ArticlesOrder::add)
	 */
	public function getArticlesBySupplierOrganizationId_Ordinabili(Model $Model, $user, $orderResult, $opts=[], $debug=false) {
	
		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$options = [];
		$options['conditions'] = ['Article.stato' => 'Y',
			   					  'Article.flag_presente_articlesorders' => 'Y'];	
			   					  
		if(isset($opts['conditions']))
			$options['conditions'] = array_merge($options['conditions'], $opts['conditions']);	

		$results = $this->getArticlesBySupplierOrganizationId($Model, $user, $orderResult['Order']['supplier_organization_id'], $options, $debug);

    	/*
    	 * escludo gli articoli gia' associati all'ordine 
		 */
		if(!empty($results)) {
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions'])) 
				$results = $this->_arrayConvertingToFindAll($results);
			
			foreach ($results as $numResult => $result) {
	
				$articlesOrderOptions = [];
				$articlesOrderOptions['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
													  'ArticlesOrder.article_organization_id' => $result['Article']['organization_id'],
													  'ArticlesOrder.article_id' => $result['Article']['id'],
													  'ArticlesOrder.order_id' => $orderResult['Order']['id']];
				$articlesOrderOptions['recursive'] = -1;
				$articlesOrderResults = $ArticlesOrder->find('first', $articlesOrderOptions);
				$Model::d($articlesOrderOptions, $debug);
				if(!empty($articlesOrderResults)) {
					unset($results[$numResult]);
					$Model::d($articlesOrderResults, $debug);
				}
					
			} // loops
			
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions'])) 
				$results = $this->_arrayConvertingToFindFirst($results);			
   	 	}
		
    	return $results;					   					  
	}

	/*
	 * articoli ordinabili del GAS titolare 
	 * $desResults = $this->ActionsDesOrder->getDesOrderData($user, $order_id, $debug);
	 */
	public function getArticlesDesTitolareByOrderId_Ordinabili(Model $Model, $user, $orderResult, $titolareOrderResult, $opts=[], $debug=false) {

		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$options = [];
		$options['conditions'] = ['Article.stato' => 'Y',
			   					  'Article.flag_presente_articlesorders' => 'Y'];	
			   					  
		if(isset($opts['conditions']))
			$options['conditions'] = array_merge($options['conditions'], $opts['conditions']);	
		
		$results = $this->getArticlesByOrderId($Model, $user, $titolareOrderResult, $options, $debug);
		
    	/*
    	 * escludo gli articoli gia' associati all'ordine 
		 */
		if(!empty($results)) {
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
				$results = $this->_arrayConvertingToFindAll($results);
			
			foreach ($results as $numResult => $result) {
				if(isset($result['Article'])) {
					$articlesOrderOptions = [];
					$articlesOrderOptions['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
														  'ArticlesOrder.article_organization_id' => $result['Article']['organization_id'],
														  'ArticlesOrder.article_id' => $result['Article']['id'],
														  'ArticlesOrder.order_id' => $orderResult['Order']['id']];
					$articlesOrderOptions['recursive'] = -1;
					$articlesOrderResults = $ArticlesOrder->find('first', $articlesOrderOptions);
					$Model::d($articlesOrderOptions, $debug);
					if(!empty($articlesOrderResults)) {
						unset($results[$numResult]);
						$Model::d($articlesOrderResults, $debug);
					}					
				} // end if(isset($result['Article']))					
			} // loops
			
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions'])) 
				$results = $this->_arrayConvertingToFindFirst($results);			
 	 	}
		
    	return $results;
	}
	
	/*
	 * articoli ordinabili del GAS titolare ma non presi dal un ordine DES 
	 */
	public function getArticlesDesTitolareBySupplierOrganizationId_Ordinabili(Model $Model, $user, $orderResult, $opts=[], $debug=false) {

		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);
								 
		/*
		 * dati del titolare 
		 */
		$owner_articles = $orderResult['Order']['owner_articles'];  // e' DES
		$owner_organization_id = $orderResult['Order']['owner_organization_id'];
		$owner_supplier_organization_id = $orderResult['Order']['owner_supplier_organization_id'];

		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$options = [];
		$options['conditions'] = ['Article.stato' => 'Y',
			   					  'Article.flag_presente_articlesorders' => 'Y'];	
			   					  
		if(isset($opts['conditions']))
			$options['conditions'] = array_merge($options['conditions'], $opts['conditions']);	
		
		$titolareUser->organization['Organization']['id'] = $owner_organization_id;
		$results = $this->getArticlesBySupplierOrganizationId($Model, $titolareUser, $owner_supplier_organization_id, $options, $debug);
		
    	/*
    	 * escludo gli articoli gia' associati all'ordine 
		 */
		if(!empty($results)) {
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
				$results = $this->_arrayConvertingToFindAll($results);
			
			foreach ($results as $numResult => $result) {
				
				$articlesOrderOptions = [];
				$articlesOrderOptions['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
													  'ArticlesOrder.article_organization_id' => $result['Article']['organization_id'],
													  'ArticlesOrder.article_id' => $result['Article']['id'],
													  'ArticlesOrder.order_id' => $orderResult['Order']['id']];
				$articlesOrderOptions['recursive'] = -1;
				$articlesOrderResults = $ArticlesOrder->find('first', $articlesOrderOptions);
				$Model::d($articlesOrderOptions, $debug);
				if(!empty($articlesOrderResults)) {
					unset($results[$numResult]);
					$Model::d($articlesOrderResults, $debug);
				}
					
			} // loops
			
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions'])) 
				$results = $this->_arrayConvertingToFindFirst($results);			
 	 	}
		
    	return $results;
	}
	
	/*
	 * elenco articoli validi e non ancora associati all'ordine partendo dall'ordine (quando sono in edit con ArticlesOrder::index)
	 */
	public function getArticlesByOrderId_Ordinabili(Model $Model, $user, $orderResult, $opts=[], $debug=false) {

		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);
	
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$options = [];
		$options['conditions'] = ['Article.stato' => 'Y',
			   					  'Article.flag_presente_articlesorders' => 'Y'];	
			   					  
		if(isset($opts['conditions']))
			$options['conditions'] = array_merge($options['conditions'], $opts['conditions']);	

		$results = $this->getArticlesByOrderId($Model, $user, $orderResult, $options, $debug);

    	/*
    	 * escludo gli articoli gia' associati all'ordine 
		 */
		if(!empty($results)) {
			
			/*
			 * non li escludo se estraggo gli articoli associati all'ordine precedente ArticlesOrder::add()
			 */
			if(isset($opts['force']) && $opts['force'] == 'NOT_EXCLUDE_ARTICLESORDERS')
				return $results;
			
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
				$results = $this->_arrayConvertingToFindAll($results);
	
			foreach ($results as $numResult => $result) {
				
				$articlesOrderOptions = [];
				$articlesOrderOptions['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
													  'ArticlesOrder.article_organization_id' => $result['Article']['organization_id'],
													  'ArticlesOrder.article_id' => $result['Article']['id'],
													  'ArticlesOrder.order_id' => $orderResult['Order']['id']];
				$articlesOrderOptions['recursive'] = -1;
				$articlesOrderResults = $ArticlesOrder->find('first', $articlesOrderOptions);
				$Model::d($articlesOrderOptions, $debug);
				
				if(!empty($articlesOrderResults)) {
					unset($results[$numResult]);
					$Model::d($articlesOrderResults, $debug);
				}
					
			} // loops
		
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions'])) 
				$results = $this->_arrayConvertingToFindFirst($results);			
 	 	}
		
    	return $results;					   					  
	}
	
	/*
	 * elenco articoli validi e non ancora associati all'ordine partendo Produttore
	 */
	public function getArticlesSupplierByOrderId_Ordinabili(Model $Model, $user, $orderResult, $opts=[], $debug=false) {

		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;

		$options = [];
		$options['conditions'] = ['Article.stato' => 'Y',
			   					  'Article.flag_presente_articlesorders' => 'Y'];	
			   					  
		if(isset($opts['conditions']))
			$options['conditions'] = array_merge($options['conditions'], $opts['conditions']);	
		
		$results = $this->getArticlesBySupplierOrganizationId($Model, $user, $orderResult, $options, $debug);		

    	/*
    	 * escludo gli articoli gia' associati all'ordine 
		 */
		if(!empty($results)) {
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
				$results = $this->_arrayConvertingToFindAll($results);
			
			foreach ($results as $numResult => $result) {

				$articlesOrderOptions = [];
				$articlesOrderOptions['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
													  'ArticlesOrder.article_organization_id' => $result['Article']['organization_id'],
													  'ArticlesOrder.article_id' => $result['Article']['id'],
													  'ArticlesOrder.order_id' => $orderResult['Order']['id']];
				$articlesOrderOptions['recursive'] = -1;
				$articlesOrderResults = $ArticlesOrder->find('first', $articlesOrderOptions);
				$Model::d($articlesOrderOptions, $debug);
				if(!empty($articlesOrderResults)) {
					unset($results[$numResult]);
					$Model::d($articlesOrderResults, $debug);
				}
			} // loops
			
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions'])) 
				$results = $this->_arrayConvertingToFindFirst($results);			
		}
		
		return $results;
	}
	
	/*
	 * estraggo SOLO articoli acquisti associati ad un ordine
	 * Mails::admin_send()  
	 */	
	public function getArticlesByOrderId_ConSoloAcquisti(Model $Model, $user, $orderResult, $opts=[], $debug=false) {
		
		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);
		
		$results = $this->getArticlesByOrderId_ConAcquisti($Model, $user, $orderResult, $opts, $debug);
		
		if(!empty($results)) {
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
				$results = $this->_arrayConvertingToFindAll($results);	
			
			foreach($results as $numResult => $result) {
				if(!isset($result['Cart']) || empty($result['Cart']))
					unset($results[$numResult]);
			} // loops 

			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions'])) 
				$results = $this->_arrayConvertingToFindFirst($results);			
		}
		
		return $results; 
	}

	public function getArticlesByOrderId_ConAcquisti(Model $Model, $user, $orderResult, $opts=[], $debug=false) {

		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'Cart');		   					  
		$Cart = new Cart();

		$options = [];
		$options['conditions'] = ['Article.stato' => 'Y'];	
			   					  
		if(isset($opts['conditions']))
			$options['conditions'] = array_merge($options['conditions'], $opts['conditions']);	
			
		$results = [];
		$results = $this->getArticlesByOrderId($Model, $user, $orderResult, $options, $debug);
		
		if(!empty($results)) {
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
				$results = $this->_arrayConvertingToFindAll($results);	
			
			foreach ($results as $numResult => $result) {
			
				$articlesOrderOptions = [];
				$articlesOrderOptions['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
										  'ArticlesOrder.article_organization_id' => $result['Article']['organization_id'],
										  'ArticlesOrder.article_id' => $result['Article']['id'],
										  'ArticlesOrder.order_id' => $orderResult['Order']['id']];
				$articlesOrderOptions['recursive'] = -1;
				$articlesOrderResults = $ArticlesOrder->find('first', $articlesOrderOptions);
				
				/*
				 * escludo gli articoli non associati all'ordine 
				 */
				if(empty($articlesOrderResults)) 
					unset($results[$numResult]);
				else { 
					/*
					 * estraggo eventuali acquisti
					 */
					
					$cartOptions = [];
					$cartOptions['conditions'] =  ['Cart.organization_id' => $user->organization['Organization']['id'],
												'Cart.order_id' => $result['ArticlesOrder']['order_id'],
												'Cart.article_organization_id' => $result['ArticlesOrder']['article_organization_id'],
												'Cart.article_id' => $result['ArticlesOrder']['article_id'],
												'Cart.deleteToReferent' => 'N'];
					$cartOptions['recursive'] = -1;
					$cartResults = $Cart->find('all', $cartOptions);
					$Model::d($cartOptions, $debug);
					$Model::d($cartResults, $debug);

					$results[$numResult]['Cart'] = $cartResults;
				}
			} // end loops 	
			
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions'])) 
				$results = $this->_arrayConvertingToFindFirst($results);			
		}
		
		return $results;	
	}

    /*
     * se titolare DES controllo gli acquisti di tutti
     */
	public function getArticlesByOrderId_ConAcquisti_TitolareDES(Model $Model, $user, $orderResult, $opts=[], $debug=false) {

		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);
		
		if ($user->organization['Organization']['hasDes']!='Y' || empty($orderResult['Order']['des_order_id']))
			return [];
		
		App::import('Model', 'Cart');		   					  
		$Cart = new Cart();

		/*
		 * dati degli ordini DES (altri GAS)
		 */	
		$desOrdersOrganizationResults = []; 

		App::import('Model', 'DesOrdersOrganization');
		$DesOrdersOrganization = new DesOrdersOrganization();

		$DesOrdersOrganizationOptions = [];
		$DesOrdersOrganizationOptions['conditions'] = ['DesOrdersOrganization.des_id' => $user->des_id,
													   'DesOrdersOrganization.des_order_id' => $orderResult['Order']['des_order_id']];
		$DesOrdersOrganizationOptions['fields'] = ['DesOrdersOrganization.organization_id', 'DesOrdersOrganization.order_id'];	
		$DesOrdersOrganizationOptions['recursive'] = -1;	
		$desOrdersOrganizationResults = $DesOrdersOrganization->find('all', $DesOrdersOrganizationOptions);
		$Model::d('DesOrdersOrganization::getDesOrdersOrganization()', $debug);
		$Model::d($DesOrdersOrganizationOptions, $debug);
		$Model::d($desOrdersOrganizationResults, $debug);

		$options = [];
		$options['conditions'] = ['Article.stato' => 'Y',
								  'ArticlesOrder.organization_id' => $orderResult['Order']['organization_id'],
								  'ArticlesOrder.order_id' => $orderResult['Order']['id']];	
			   					  
		if(isset($opts['conditions']))
			$options['conditions'] = array_merge($options['conditions'], $opts['conditions']);	
		
		$results = $this->getArticlesByOrderId($Model, $user, $orderResult, $options, $debug);
		if(!empty($results)) {
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
				$results = $this->_arrayConvertingToFindAll($results);	
				
			foreach ($results as $numResult => $result) {
		  
				/*
				 * per ogni articolo cerco gli acquisti del proprio GAS e degli altri del DES
				 */
				foreach($desOrdersOrganizationResults as $desOrdersOrganizationResult) {
				
					$cartOptions = [];
					$cartOptions['conditions'] = ['Cart.organization_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['organization_id'],
												  'Cart.order_id' => $desOrdersOrganizationResult['DesOrdersOrganization']['order_id'],
												  'Cart.article_organization_id' => $result['Article']['organization_id'],
												  'Cart.article_id' => $result['Article']['id'],
												  'Cart.deleteToReferent' => 'N'];
					$options['recursive'] = -1;
					$Model::d($cartOptions, $debug); 
					$cartResults = $Cart->find('all', $cartOptions);
					$Model::d($cartResults, $debug);
					
					if(!empty($cartResults)) {
						$results[$numResult]['Cart'] = $cartResults;
						if($cartResults['Cart']['organization_id']==$user->organization['Organization']['id'])
							$results[$numResult]['Cart']['canEdit'] = true;
						else
							$results[$numResult]['Cart']['canEdit'] = true;
					}
				} // loops DesOrdersOrganizationResult
			}  // loops Cart
			
			if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions'])) 
				$results = $this->_arrayConvertingToFindFirst($results);			
		}
		
		return $results;	
	}	
}	
?>