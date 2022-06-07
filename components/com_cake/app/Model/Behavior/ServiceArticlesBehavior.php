<?php 
App::uses('SuperServiceArticlesBehavior', 'Model/Behavior');
App::uses('UtilsCommons', 'Lib');

class ServiceArticlesBehavior extends SuperServiceArticlesBehavior {

	private $utilsCommons;
	
	public function setup(Model $Model, $settings = []) {	
		parent::setup($Model, $settings);
		$this->utilsCommons = new UtilsCommons();
	}

    /*
     * elenco articoli validi e non ancora associati all'ordine partendo dal produttore dell'ordine
     *
     * in add con ArticlesOrder::add
     * in index con ArticlesOrder::index per l'elenco degli articoli ancora da associare
     */
    public function getArticlesBySupplierOrganization_Ordinabili(Model $Model, $user, $orderResult, $opts=[], $debug=false) {

        if(!is_array($orderResult))
            $orderResult = $Model->_getOrderById($user, $orderResult, $debug);

        /*
         * articoli da associare, ricerco partendo da SupplierOrganization => Articles
         * SuppliersOrganizationOwnerArticles creato in AppModel::_getOrderById() chi gestisce il listino
         */
        $suppliersOrganization['SuppliersOrganization'] = $orderResult['SuppliersOrganizationOwnerArticles'];
        // debug($suppliersOrganization['SuppliersOrganization']);
        $results = $this->getArticlesBySuppliersOrganization($Model, $user, $suppliersOrganization, [], $debug);	// articoli da associare

        return $this->_excludeJustAssociate($Model, $user, $results, $orderResult, $opts);
    }

    /*
     * elenco articoli validi e non ancora associati, ricerco partendo da Order
     */
	public function getArticlesByOrder_Ordinabili(Model $Model, $user, $orderResult, $opts=[], $debug=false) {

		if(!is_array($orderResult))
			$orderResult = $Model->_getOrderById($user, $orderResult, $debug);

        $results = $this->getArticlesByOrder($Model, $user, $orderResult, [], $debug);	// articoli da associare

        return $this->_excludeJustAssociate($Model, $user, $results, $orderResult, $opts);
	}

    /*
     * elenco articoli validi e non ancora associati all'ordine DES partendo da quelli gia' scelti dal titolare
     *
     * in index con ArticlesOrder::index per l'elenco degli articoli ancora da associare
     *
     * articoli ordinabili del GAS titolare
     * $desResults = $this->ActionsDesOrder->getDesOrderData($user, $order_id, $debug);
     */
    public function getArticlesByDesOrder_Ordinabili(Model $Model, $user, $orderTitolareResult, $orderResult, $opts=[], $debug=false)
    {

        if (!is_array($orderResult))
            $orderResult = $Model->_getOrderById($user, $orderResult, $debug);

        /*
         * articoli da associare, ricerco partendo da quelli gia' scelti da titolare
         */
        $results = $this->getArticlesByOrder($Model, $user, $orderTitolareResult, [], $debug);    // articoli da associare

        return $this->_excludeJustAssociate($Model, $user, $results, $orderResult, $opts);
    }

	public function getArticlesByOrder_ConAcquisti(Model $Model, $user, $orderResult, $opts=[], $debug=false) {

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
        $results = $this->getArticlesByOrder($Model, $user, $orderResult, $options, $debug);

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
     * escludo gli articoli gia' associati all'ordine
     */
    private function _excludeJustAssociate($Model, $user, $results, $orderResult, $opts, $debug=false) {

        if(!empty($results)) {

            /*
             * non li escludo se estraggo gli articoli associati all'ordine precedente ArticlesOrder::add()
             */
            if(isset($opts['force']) && $opts['force'] == 'NOT_EXCLUDE_ARTICLESORDERS')
                return $results;

            if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
                $results = $this->_arrayConvertingToFindAll($results);

            App::import('Model', 'ArticlesOrder');
            $ArticlesOrder = new ArticlesOrder;

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
}
?>